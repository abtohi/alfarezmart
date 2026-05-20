<?php
/**
 * Pembersihan duplikat — bulk cepat v2
 * - Tanpa referensi transaksi: hapus massal per chunk
 * - Ada referensi: update per-ID + retry
 */
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
define('BASE_PATH', __DIR__);
define('STORAGE_PATH', __DIR__ . '/public/storage');
require 'app/config/App.php';
require 'app/config/Database.php';

$start = microtime(true);
$db = Database::getInstance()->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $db->exec('SET SESSION innodb_lock_wait_timeout = 180');
    $db->exec('SET SESSION max_statement_time = 0');
} catch (PDOException $e) {
}

echo "=== Pembersihan Duplikat (Bulk Cepat v2) ===\n";
echo date('Y-m-d H:i:s') . "\n\n";

function productScore(array $p, array $pkgs, int $purchaseCount = 0, int $saleCount = 0): int
{
    $score = 0;
    $hasRichPkg = false;
    foreach ($pkgs as $pkg) {
        if (!empty($pkg['barcode'])) {
            $score += 10;
            $hasRichPkg = true;
        }
        if ((float)($pkg['buy_price'] ?? 0) > 0) {
            $score += 3;
            $hasRichPkg = true;
        }
        if ((float)($pkg['sell_price_retail'] ?? 0) > 0) {
            $score += 3;
            $hasRichPkg = true;
        }
    }
    if ($hasRichPkg || count($pkgs) > 0) {
        if (!empty($p['brand_id'])) $score += 2;
        if (!empty($p['category_id'])) $score += 2;
        if (!empty($p['photo'])) $score += 5;
        if (!empty($p['weight_value'])) $score += 1;
    }
    $score += min(5, $purchaseCount) * 3;
    $score += min(3, $saleCount) * 2;
    return $score;
}

function supplierScore(array $s): int
{
    return (!empty($s['phone']) ? 2 : 0)
        + (!empty($s['address']) ? 2 : 0)
        + (!empty($s['email']) ? 2 : 0)
        + (!empty($s['notes']) ? 1 : 0);
}

function pickBestId(array $ids, array $scores): ?int
{
    $best = null;
    $bestScore = -1;
    foreach ($ids as $id) {
        $sc = $scores[$id] ?? -1;
        if ($sc > $bestScore || ($sc === $bestScore && ($best === null || $id < $best))) {
            $bestScore = $sc;
            $best = $id;
        }
    }
    return $best;
}

function loadIdSet(PDO $db, string $table): array
{
    $set = [];
    foreach ($db->query("SELECT DISTINCT product_id FROM `$table`")->fetchAll(PDO::FETCH_COLUMN) as $id) {
        $set[(int)$id] = true;
    }
    return $set;
}

function execRetry(PDOStatement $stmt, array $params, int $max = 6): void
{
    for ($i = 1; $i <= $max; $i++) {
        try {
            $stmt->execute($params);
            return;
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            $retryable = strpos($msg, '1205') !== false || strpos($msg, '1969') !== false || strpos($msg, '1213') !== false;
            if (!$retryable || $i >= $max) {
                throw $e;
            }
            usleep(500000 * $i);
        }
    }
}

/** Alihkan sale/purchase yang masih mengacu packaging produk yang akan dihapus */
function repointPackagingRefs(PDO $db, int $del, int $kept): void
{
    $keptPkg = defaultPackagingId($db, $kept);
    if (!$keptPkg) return;

    $updSale = $db->prepare('
        UPDATE sale_items si
        INNER JOIN product_packagings pp ON si.packaging_id = pp.id
        SET si.product_id = ?, si.packaging_id = ?
        WHERE pp.product_id = ?
    ');
    execRetry($updSale, [$kept, $keptPkg, $del]);

    $updPurch = $db->prepare('
        UPDATE purchase_items pi
        INNER JOIN product_packagings pp ON pi.packaging_id = pp.id
        SET pi.product_id = ?, pi.packaging_id = ?
        WHERE pp.product_id = ?
    ');
    execRetry($updPurch, [$kept, $keptPkg, $del]);
}

/** Hapus massal produk tanpa referensi product_id di tabel transaksi */
function bulkDeleteOrphans(PDO $db, array $prodMap, int $chunkSize = 400): int
{
    if (empty($prodMap)) return 0;
    $deleted = 0;
    $ids = array_map('intval', array_keys($prodMap));
    foreach (array_chunk($prodMap, $chunkSize, true) as $chunk) {
        foreach ($chunk as $del => $kept) {
            ensureKeptHasPackaging($db, (int)$del, (int)$kept);
            repointPackagingRefs($db, (int)$del, (int)$kept);
        }
        $in = implode(',', array_map('intval', array_keys($chunk)));
        execRetry($db->prepare("DELETE FROM supplier_products WHERE product_id IN ($in)"), []);
        execRetry($db->prepare("DELETE FROM stock WHERE product_id IN ($in)"), []);
        execRetry($db->prepare("DELETE FROM product_packagings WHERE product_id IN ($in)"), []);
        execRetry($db->prepare("DELETE FROM products WHERE id IN ($in)"), []);
        $deleted += count($chunk);
        echo "   ... bulk hapus $deleted / " . count($ids) . "\n";
    }
    return $deleted;
}

function defaultPackagingId(PDO $db, int $productId): ?int
{
    $stmt = $db->prepare('SELECT id FROM product_packagings WHERE product_id = ? ORDER BY level ASC, id ASC LIMIT 1');
    $stmt->execute([$productId]);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int)$id : null;
}

/** Jika produk yang dipertahankan belum punya packaging, salin dari produk duplikat */
function ensureKeptHasPackaging(PDO $db, int $del, int $kept): void
{
    if (defaultPackagingId($db, $kept)) {
        return;
    }
    $rows = $db->prepare('SELECT * FROM product_packagings WHERE product_id = ? ORDER BY level ASC, id ASC');
    $rows->execute([$del]);
    $cols = null;
    foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
        unset($row['id']);
        $row['product_id'] = $kept;
        if ($cols === null) {
            $cols = array_keys($row);
        }
        $ph = implode(',', array_fill(0, count($cols), '?'));
        $sql = 'INSERT INTO product_packagings (`' . implode('`,`', $cols) . '`) VALUES (' . $ph . ')';
        $db->prepare($sql)->execute(array_values($row));
    }
}

/** Produk yang punya referensi transaksi — merge per baris */
function mergeWithRefs(PDO $db, array $prodMap, array $refSets): int
{
    if (empty($prodMap)) return 0;

    $stmts = [
        'purchase' => $db->prepare('UPDATE purchase_items SET product_id = ?, packaging_id = ? WHERE product_id = ?'),
        'sale' => $db->prepare('UPDATE sale_items SET product_id = ?, packaging_id = ? WHERE product_id = ?'),
        'movement' => $db->prepare('UPDATE stock_movements SET product_id = ? WHERE product_id = ?'),
        'sp_del_conflict' => $db->prepare('
            DELETE sp FROM supplier_products sp
            WHERE sp.product_id = ?
            AND EXISTS (
                SELECT 1 FROM supplier_products k
                WHERE k.supplier_id = sp.supplier_id AND k.product_id = ?
            )
        '),
        'sp_update' => $db->prepare('UPDATE supplier_products SET product_id = ? WHERE product_id = ?'),
        'sp_del' => $db->prepare('DELETE FROM supplier_products WHERE product_id = ?'),
        'stock_del' => $db->prepare('DELETE FROM stock WHERE product_id = ?'),
        'pkg_del' => $db->prepare('DELETE FROM product_packagings WHERE product_id = ?'),
        'prod_del' => $db->prepare('DELETE FROM products WHERE id = ?'),
    ];

    $total = count($prodMap);
    echo "   Merge dengan referensi: $total produk...\n";
    $done = 0;
    foreach ($prodMap as $del => $kept) {
        $del = (int)$del;
        $kept = (int)$kept;
        if ($del === $kept) continue;

        ensureKeptHasPackaging($db, $del, $kept);
        repointPackagingRefs($db, $del, $kept);
        $keptPkg = defaultPackagingId($db, $kept);

        if ($keptPkg) {
            execRetry($stmts['purchase'], [$kept, $keptPkg, $del]);
            execRetry($stmts['sale'], [$kept, $keptPkg, $del]);
        }
        if (!empty($refSets['stock_movements'][$del])) {
            execRetry($stmts['movement'], [$kept, $del]);
        }
        if (!empty($refSets['supplier_products'][$del])) {
            execRetry($stmts['sp_del_conflict'], [$del, $kept]);
            execRetry($stmts['sp_update'], [$kept, $del]);
        }

        execRetry($stmts['stock_del'], [$del]);
        execRetry($stmts['pkg_del'], [$del]);
        execRetry($stmts['prod_del'], [$del]);

        $done++;
        if ($done % 50 === 0 || $done === $total) {
            echo "   ... merge $done / $total\n";
        }
    }
    return $done;
}

function applyProductMerges(PDO $db, array $prodMap, array $refSets): int
{
    if (empty($prodMap)) return 0;

    $orphans = [];
    $withRefs = [];
    foreach ($prodMap as $del => $kept) {
        $del = (int)$del;
        $hasTxn = !empty($refSets['purchase_items'][$del])
            || !empty($refSets['sale_items'][$del])
            || !empty($refSets['stock_movements'][$del]);
        if ($hasTxn) {
            $withRefs[$del] = $kept;
        } else {
            $orphans[$del] = $kept;
        }
    }

    echo '   Tanpa referensi product_id (bulk): ' . count($orphans) . "\n";
    echo '   Dengan referensi product_id: ' . count($withRefs) . "\n";

    $n = bulkDeleteOrphans($db, $orphans);
    $n += mergeWithRefs($db, $withRefs, $refSets);
    return $n;
}

// --- 1. Supplier ---
echo "1. Supplier duplikat...\n";
$suppliers = $db->query('SELECT * FROM suppliers')->fetchAll(PDO::FETCH_ASSOC);
$supByName = [];
foreach ($suppliers as $s) {
    $supByName[mb_strtolower(trim($s['name']))][] = $s;
}
$supMap = [];
foreach ($supByName as $rows) {
    if (count($rows) < 2) continue;
    $scores = [];
    foreach ($rows as $r) $scores[$r['id']] = supplierScore($r);
    $kept = pickBestId(array_column($rows, 'id'), $scores);
    foreach ($rows as $r) {
        if ((int)$r['id'] !== (int)$kept) $supMap[$r['id']] = $kept;
    }
}
if ($supMap) {
    foreach ($supMap as $del => $kept) {
        execRetry($db->prepare('UPDATE purchases SET supplier_id = ? WHERE supplier_id = ?'), [$kept, $del]);
        execRetry($db->prepare('UPDATE sales_reps SET supplier_id = ? WHERE supplier_id = ?'), [$kept, $del]);
        execRetry($db->prepare('UPDATE supplier_products SET supplier_id = ? WHERE supplier_id = ?'), [$kept, $del]);
    }
    $in = implode(',', array_map('intval', array_keys($supMap)));
    execRetry($db->prepare("DELETE FROM suppliers WHERE id IN ($in)"), []);
}
echo '   Dihapus: ' . count($supMap) . " supplier\n\n";

// --- Data produk ---
echo "2. Muat data produk...\n";
$products = $db->query('SELECT * FROM products')->fetchAll(PDO::FETCH_ASSOC);
$productById = [];
foreach ($products as $p) {
    $productById[(int)$p['id']] = $p;
}

$pkgsByProduct = [];
foreach ($db->query('SELECT * FROM product_packagings')->fetchAll(PDO::FETCH_ASSOC) as $pkg) {
    $pkgsByProduct[(int)$pkg['product_id']][] = $pkg;
}

$scores = [];
foreach ($productById as $id => $p) {
    $scores[$id] = productScore(
        $p,
        $pkgsByProduct[$id] ?? [],
        $purchaseCounts[$id] ?? 0,
        $saleCounts[$id] ?? 0
    );
}

$refSets = [
    'purchase_items' => loadIdSet($db, 'purchase_items'),
    'sale_items' => loadIdSet($db, 'sale_items'),
    'stock_movements' => loadIdSet($db, 'stock_movements'),
    'supplier_products' => loadIdSet($db, 'supplier_products'),
];

$purchaseCounts = [];
foreach ($db->query('SELECT product_id, COUNT(*) c FROM purchase_items GROUP BY product_id')->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $purchaseCounts[(int)$r['product_id']] = (int)$r['c'];
}
$saleCounts = [];
foreach ($db->query('SELECT product_id, COUNT(*) c FROM sale_items GROUP BY product_id')->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $saleCounts[(int)$r['product_id']] = (int)$r['c'];
}

echo '   Total produk: ' . count($productById) . "\n\n";

// --- full_name ---
echo "3. Duplikat full_name...\n";
$byName = [];
foreach ($productById as $id => $p) {
    $key = mb_strtolower(trim($p['full_name'] ?? ''));
    if ($key === '') continue;
    $byName[$key][] = $id;
}
$prodMap = [];
foreach ($byName as $ids) {
    if (count($ids) < 2) continue;
    $kept = pickBestId($ids, $scores);
    foreach ($ids as $id) {
        if ($id != $kept && !isset($prodMap[$id])) {
            $prodMap[$id] = $kept;
        }
    }
}
$n1 = applyProductMerges($db, $prodMap, $refSets);
echo "   Total dihapus: $n1\n\n";

foreach (array_keys($prodMap) as $id) {
    unset($productById[$id], $scores[$id], $pkgsByProduct[$id]);
    foreach ($refSets as &$set) {
        unset($set[$id]);
    }
}
unset($set);

// --- barcode ---
echo "4. Duplikat barcode...\n";
$byBarcode = [];
foreach ($pkgsByProduct as $pid => $pkgs) {
    if (!isset($productById[$pid])) continue;
    foreach ($pkgs as $pkg) {
        $bc = trim($pkg['barcode'] ?? '');
        if ($bc === '') continue;
        $byBarcode[$bc][$pid] = true;
    }
}
$bcMap = [];
foreach ($byBarcode as $pids) {
    $ids = array_keys($pids);
    if (count($ids) < 2) continue;
    $kept = pickBestId($ids, $scores);
    foreach ($ids as $id) {
        if ($id != $kept && !isset($bcMap[$id])) {
            $bcMap[$id] = $kept;
        }
    }
}
$n2 = applyProductMerges($db, $bcMap, $refSets);
echo "   Total dihapus: $n2\n\n";

// --- Verifikasi ---
$remainName = $db->query("SELECT COUNT(*) FROM (SELECT LOWER(TRIM(full_name)) n FROM products GROUP BY n HAVING COUNT(*)>1) t")->fetchColumn();
$remainBc = $db->query("SELECT COUNT(*) FROM (SELECT barcode FROM product_packagings WHERE barcode IS NOT NULL AND barcode != '' GROUP BY barcode HAVING COUNT(DISTINCT product_id) > 1) t")->fetchColumn();
$remainSup = $db->query("SELECT COUNT(*) FROM (SELECT LOWER(TRIM(name)) n FROM suppliers GROUP BY n HAVING COUNT(*)>1) t")->fetchColumn();
$totalProd = $db->query('SELECT COUNT(*) FROM products')->fetchColumn();

$sec = round(microtime(true) - $start, 1);
echo "=== Selesai ($sec detik) ===\n";
echo "Total produk: $totalProd\n";
echo "Sisa duplikat full_name: $remainName\n";
echo "Sisa duplikat barcode: $remainBc\n";
echo "Sisa duplikat supplier: $remainSup\n";
