<?php
/**
 * ApiController - JSON API endpoints for AJAX/PWA
 */
class ApiController extends Controller
{
    protected \PDO $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    public function exportProducts()
    {
        try {
            $mode = isset($_GET['mode']) ? (int)$_GET['mode'] : 1;
            
            $db = Database::getInstance()->getConnection();
            $data = [];
            
            if ($mode === 1) {
                // By Supplier
                $supplierId = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
                $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
                $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
                
                $sql = "SELECT p.full_name as 'Nama Produk', 
                        (SELECT COALESCE(SUM(pi.quantity), 0) FROM purchase_items pi JOIN purchases pu ON pi.purchase_id = pu.id WHERE pi.product_id = p.id AND pu.supplier_id = sp.supplier_id) as 'Qty Pembelian',
                        (SELECT u.name FROM purchase_items pi JOIN purchases pu ON pi.purchase_id = pu.id JOIN product_packagings pp ON pi.packaging_id = pp.id JOIN units u ON pp.unit_id = u.id WHERE pi.product_id = p.id AND pu.supplier_id = sp.supplier_id ORDER BY pu.purchase_date DESC LIMIT 1) as 'Jenis Kemasan',
                        sp.last_buy_price as 'Harga Beli Terakhir', 
                        sp.updated_at as 'Tanggal Update' 
                        FROM supplier_products sp 
                        JOIN products p ON sp.product_id = p.id 
                        WHERE sp.supplier_id = :sup_id";
                        
                $params = [':sup_id' => $supplierId];
                
                if (!empty($dateFrom)) {
                    $sql .= " AND DATE(sp.updated_at) >= :from";
                    $params[':from'] = $dateFrom;
                }
                if (!empty($dateTo)) {
                    $sql .= " AND DATE(sp.updated_at) <= :to";
                    $params[':to'] = $dateTo;
                }
                
                $sql .= " ORDER BY p.full_name ASC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } else {
                // By Product Name & Multi Supplier filter
                $productName = isset($_GET['product_name']) ? $_GET['product_name'] : '';
                $supplierId = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
                
                $sql = "SELECT p.full_name as 'Nama Produk',
                        pi.quantity as 'Qty Pembelian',
                        u.name as 'Satuan atau jenis kemasan',
                        pi.buy_price as 'Harga Beli (Satuan)',
                        pi.total_price as 'Harga Total',
                        s.name as 'Supplier',
                        pu.purchase_date as 'Tanggal Pembelian'
                        FROM purchase_items pi
                        JOIN purchases pu ON pi.purchase_id = pu.id
                        JOIN products p ON pi.product_id = p.id
                        JOIN product_packagings pp ON pi.packaging_id = pp.id
                        JOIN units u ON pp.unit_id = u.id
                        LEFT JOIN suppliers s ON pu.supplier_id = s.id
                        WHERE p.is_active = 1";
                
                $params = [];
                
                // MULTI KEYWORD SEARCH ALGORITHM
                if (!empty($productName)) {
                    $words = array_filter(explode(' ', trim($productName)));
                    foreach ($words as $i => $word) {
                        $p_name  = ":p_name_$i";
                        $p_label = ":p_label_$i";
                        $p_brand = ":p_brand_$i";
                        $p_bar   = ":p_bar_$i";
                        $sql .= " AND (p.full_name LIKE $p_name OR p.short_label LIKE $p_label OR p.id IN (SELECT product_id FROM product_packagings WHERE barcode LIKE $p_bar))";
                        $like = "%{$word}%";
                        $params[$p_name]  = $like;
                        $params[$p_label] = $like;
                        $params[$p_bar]   = $like;
                    }
                }
                
                if ($supplierId > 0) {
                    $sql .= " AND pu.supplier_id = :sup_id";
                    $params[':sup_id'] = $supplierId;
                }
                
                $sql .= " ORDER BY pu.purchase_date DESC, p.full_name ASC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $this->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getProducts()
    {
        $model = new ProductModel();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        $search = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
        $catId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $result = $model->getProductsWithPrices($page, $perPage, $search, $catId);
        $this->json($result);
    }

    public function getProductNames()
    {
        $model = new ProductModel();
        $this->json(['success' => true, 'data' => $model->getProductNames()]);
    }

    public function syncAllData()
    {
        $limit = 500; // Limit payload size
        
        // Products
        $productModel = new ProductModel();
        $productsResult = $productModel->getProductsWithPrices(1, 999999, '', null);
        $products = [];
        if (isset($productsResult['data'])) {
            foreach ($productsResult['data'] as $p) {
                $products[] = [
                    'id'                   => (int)$p['id'],
                    'short_label'          => $p['short_label'],
                    'full_name'            => $p['full_name'],
                    'invoice_name'         => $p['invoice_name'] ?? null,
                    'supplier_invoice_name'=> $p['supplier_invoice_name'] ?? null,
                    'brand_id'             => !empty($p['brand_id']) ? (int)$p['brand_id'] : null,
                    'brand_name'           => $p['brand_name'],
                    'category_id'          => !empty($p['category_id']) ? (int)$p['category_id'] : null,
                    'category_name'        => $p['category_name'],
                    'product_type'         => $p['product_type'] ?? null,
                    'weight_value'         => $p['weight_value'] ?? null,
                    'weight_unit'          => $p['weight_unit'] ?? null,
                    'variant'              => $p['variant'] ?? null,
                    'is_custom_label'      => (int)($p['is_custom_label'] ?? 0),
                    'code'                 => $p['code'],
                    'is_available'         => (int)($p['is_available'] ?? 1),
                    'photo'                => $p['photo'] ?? null,
                    'updated_at'           => $p['updated_at'] ?? null,
                    'created_at'           => $p['created_at'] ?? null,
                    'packagings'           => $p['packagings']
                ];
            }
        }

        // Sales
        require_once __DIR__ . '/../models/SaleModel.php';
        $saleModel = new SaleModel();
        $salesResult = $saleModel->getList(1, $limit);
        $sales = $salesResult['data'] ?? [];

        // Suppliers
        require_once __DIR__ . '/../models/SupplierModel.php';
        $supplierModel = new SupplierModel();
        $suppliers = $supplierModel->getAllWithType();

        // Purchases
        require_once __DIR__ . '/../models/PurchaseModel.php';
        $purchaseModel = new PurchaseModel();
        $purchasesResult = $purchaseModel->getList(1, $limit);
        $purchases = $purchasesResult['data'] ?? [];

        // Product Categories (brands & categories)
        require_once __DIR__ . '/../models/CategoryModel.php';
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->all() ?? [];

        require_once __DIR__ . '/../models/BrandModel.php';
        $brandModel = new BrandModel();
        $brands = $brandModel->all() ?? [];

        require_once __DIR__ . '/../models/UnitModel.php';
        $unitModel = new UnitModel();
        $units = $unitModel->all() ?? [];

        // Finance Accounts & Categories
        require_once __DIR__ . '/../models/FinanceModel.php';
        $financeModel = new FinanceModel();
        $financeAccounts    = $financeModel->getActiveAccounts() ?? [];
        $financeCategories  = $financeModel->getActiveCategories() ?? [];

        // Fetch recent finance logs (last 30 days) for offline use
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM finance_logs WHERE log_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY log_date DESC, id DESC");
        $stmt->execute();
        $financeLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sales Reps
        require_once __DIR__ . '/../models/SalesRepModel.php';
        $salesRepModel = new SalesRepModel();
        $salesReps = $salesRepModel->getAllWithSupplier();

        // Supplier-Product mappings for offline supplier-aware search
        require_once __DIR__ . '/../models/SupplierProductModel.php';
        $spModel = new SupplierProductModel();
        $spStmt = $db->prepare("
            SELECT id, supplier_id, product_id, sales_rep_id, last_buy_price, purchase_count
            FROM supplier_products
            ORDER BY supplier_id, product_id
        ");
        $spStmt->execute();
        $supplierProducts = $spStmt->fetchAll(PDO::FETCH_ASSOC);
        // Cast to int for compact JSON
        foreach ($supplierProducts as &$sp) {
            $sp['id'] = (int)$sp['id'];
            $sp['supplier_id'] = (int)$sp['supplier_id'];
            $sp['product_id'] = (int)$sp['product_id'];
            $sp['sales_rep_id'] = $sp['sales_rep_id'] ? (int)$sp['sales_rep_id'] : null;
            $sp['purchase_count'] = (int)($sp['purchase_count'] ?? 0);
        }
        unset($sp);

        $this->json([
            'success'   => true,
            'products'  => $products,
            'sales'     => $sales,
            'suppliers' => $suppliers,
            'sales_reps'=> $salesReps,
            'purchases' => $purchases,
            'categories' => $categories,
            'brands'    => $brands,
            'units'     => $units,
            'debts'     => [],
            'finance'   => [
                'accounts'   => $financeAccounts,
                'categories' => $financeCategories,
            ],
            'finance_logs' => $financeLogs,
            'supplier_products' => $supplierProducts
        ]);
    }


    public function syncProducts()
    {
        $model = new ProductModel();
        // Fetch all active products (up to a large limit)
        $result = $model->getProductsWithPrices(1, 999999, '', null);
        
        // Return only what is needed for OfflineDB to keep payload small
        $products = [];
        foreach ($result['data'] as $p) {
            $products[] = [
                'id'                    => (int)$p['id'],
                'short_label'           => $p['short_label'],
                'full_name'             => $p['full_name'],
                'invoice_name'          => $p['invoice_name'] ?? null,
                'supplier_invoice_name' => $p['supplier_invoice_name'] ?? null,
                'brand_id'              => !empty($p['brand_id']) ? (int)$p['brand_id'] : null,
                'brand_name'            => $p['brand_name'],
                'category_id'           => !empty($p['category_id']) ? (int)$p['category_id'] : null,
                'category_name'         => $p['category_name'],
                'product_type'          => $p['product_type'] ?? null,
                'weight_value'          => $p['weight_value'] ?? null,
                'weight_unit'           => $p['weight_unit'] ?? null,
                'variant'               => $p['variant'] ?? null,
                'is_custom_label'       => (int)($p['is_custom_label'] ?? 0),
                'code'                  => $p['code'],
                'is_available'          => (int)($p['is_available'] ?? 1),
                'photo'                 => $p['photo'] ?? null,
                'updated_at'            => $p['updated_at'] ?? null,
                'created_at'            => $p['created_at'] ?? null,
                'packagings'            => $p['packagings']
            ];
        }
        
        $this->json([
            'success' => true,
            'products' => $products,
            'count' => count($products)
        ]);
    }

    public function getProductHistory(int $id)
    {
        $purchaseModel = new PurchaseModel();
        $history = $purchaseModel->getProductPurchaseHistory($id);
        $comparison = $purchaseModel->getProductSupplierComparison($id);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'history' => $history,
                'comparison' => $comparison
            ]
        ]);
    }

    /**
     * Get products from a supplier compared against prices from other suppliers.
     * Only returns products that have been purchased from MORE than one supplier.
     */
    public function getSupplierPriceComparison(string $id)
    {
        try {
            $supplierId = (int)$id;
            $db = Database::getInstance()->getConnection();

            // Get all products purchased from this supplier that were also
            // purchased from at least one other supplier (so we can compare).
            $stmt = $db->prepare("
                SELECT
                    pi.product_id,
                    p.full_name AS product_name,
                    p.short_label,
                    cat.name AS category_name,
                    -- Latest purchase info for the SELECTED supplier
                    (SELECT pi2.buy_price
                     FROM purchase_items pi2
                     JOIN purchases pu2 ON pi2.purchase_id = pu2.id
                     WHERE pi2.product_id = pi.product_id AND pu2.supplier_id = :sid2
                     ORDER BY pu2.purchase_date DESC, pu2.id DESC LIMIT 1
                    ) AS selected_buy_price,
                    (SELECT pkg2.base_qty
                     FROM purchase_items pi2
                     JOIN purchases pu2 ON pi2.purchase_id = pu2.id
                     JOIN product_packagings pkg2 ON pi2.packaging_id = pkg2.id
                     WHERE pi2.product_id = pi.product_id AND pu2.supplier_id = :sid3
                     ORDER BY pu2.purchase_date DESC, pu2.id DESC LIMIT 1
                    ) AS selected_base_qty,
                    (SELECT u2.name
                     FROM purchase_items pi2
                     JOIN purchases pu2 ON pi2.purchase_id = pu2.id
                     JOIN product_packagings pkg2 ON pi2.packaging_id = pkg2.id
                     JOIN units u2 ON pkg2.unit_id = u2.id
                     WHERE pi2.product_id = pi.product_id AND pu2.supplier_id = :sid4
                     ORDER BY pu2.purchase_date DESC, pu2.id DESC LIMIT 1
                    ) AS selected_unit_name,
                    (SELECT pu2.purchase_date
                     FROM purchase_items pi2
                     JOIN purchases pu2 ON pi2.purchase_id = pu2.id
                     WHERE pi2.product_id = pi.product_id AND pu2.supplier_id = :sid5
                     ORDER BY pu2.purchase_date DESC, pu2.id DESC LIMIT 1
                    ) AS selected_last_date
                FROM purchase_items pi
                JOIN purchases pu ON pi.purchase_id = pu.id
                JOIN products p ON pi.product_id = p.id
                LEFT JOIN categories cat ON p.category_id = cat.id
                WHERE pu.supplier_id = :sid
                GROUP BY pi.product_id, p.full_name, p.short_label, cat.name
                ORDER BY p.full_name ASC
            ");
            $stmt->execute([
                ':sid'  => $supplierId,
                ':sid2' => $supplierId,
                ':sid3' => $supplierId,
                ':sid4' => $supplierId,
                ':sid5' => $supplierId,
            ]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($products)) {
                $this->json(['success' => true, 'data' => [], 'supplier_name' => '']);
                return;
            }

            // Fetch supplier name
            $stmtSup = $db->prepare("SELECT name FROM suppliers WHERE id = :id");
            $stmtSup->execute([':id' => $supplierId]);
            $supplierName = $stmtSup->fetchColumn() ?: 'Supplier';

            // For each product, get all competitor supplier prices
            $productIds = array_column($products, 'product_id');
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));

            $stmtOthers = $db->prepare("
                SELECT
                    pi.product_id,
                    COALESCE(s.id, 0) AS supplier_id,
                    COALESCE(s.name, 'Supplier Dihapus') AS supplier_name,
                    (SELECT pi2.buy_price
                     FROM purchase_items pi2
                     JOIN purchases pu2 ON pi2.purchase_id = pu2.id
                     WHERE pi2.product_id = pi.product_id AND COALESCE(pu2.supplier_id, 0) = COALESCE(s.id, 0)
                     ORDER BY pu2.purchase_date DESC LIMIT 1
                    ) AS last_buy_price,
                    (SELECT pkg2.base_qty
                     FROM purchase_items pi2
                     JOIN purchases pu2 ON pi2.purchase_id = pu2.id
                     JOIN product_packagings pkg2 ON pi2.packaging_id = pkg2.id
                     WHERE pi2.product_id = pi.product_id AND COALESCE(pu2.supplier_id, 0) = COALESCE(s.id, 0)
                     ORDER BY pu2.purchase_date DESC LIMIT 1
                    ) AS last_base_qty,
                    (SELECT u2.name
                     FROM purchase_items pi2
                     JOIN purchases pu2 ON pi2.purchase_id = pu2.id
                     JOIN product_packagings pkg2 ON pi2.packaging_id = pkg2.id
                     JOIN units u2 ON pkg2.unit_id = u2.id
                     WHERE pi2.product_id = pi.product_id AND COALESCE(pu2.supplier_id, 0) = COALESCE(s.id, 0)
                     ORDER BY pu2.purchase_date DESC LIMIT 1
                    ) AS last_unit_name,
                    MAX(pu.purchase_date) AS last_date
                FROM purchase_items pi
                JOIN purchases pu ON pi.purchase_id = pu.id
                LEFT JOIN suppliers s ON pu.supplier_id = s.id
                WHERE pi.product_id IN ($placeholders)
                  AND COALESCE(pu.supplier_id, 0) != ?
                GROUP BY pi.product_id, COALESCE(s.id, 0), COALESCE(s.name, 'Supplier Dihapus')
            ");
            $params = array_merge(array_values($productIds), [$supplierId]);
            $stmtOthers->execute($params);
            $otherRows = $stmtOthers->fetchAll(PDO::FETCH_ASSOC);

            // Group other suppliers by product_id
            $othersByProduct = [];
            foreach ($otherRows as $row) {
                $othersByProduct[$row['product_id']][] = $row;
            }
            
            // Sort each product's competitors by last_buy_price ASC
            foreach ($othersByProduct as $pid => &$others) {
                usort($others, function($a, $b) {
                    return floatval($a['last_buy_price'] ?? 0) <=> floatval($b['last_buy_price'] ?? 0);
                });
            }
            unset($others);

            // Get base unit for each product
            $stmtUnit = $db->prepare("
                SELECT pp.product_id, u.name AS base_unit
                FROM product_packagings pp
                JOIN units u ON pp.unit_id = u.id
                WHERE pp.product_id IN ($placeholders) AND pp.level = 1
            ");
            $stmtUnit->execute(array_values($productIds));
            $unitRows = $stmtUnit->fetchAll(PDO::FETCH_ASSOC);
            $baseUnits = [];
            foreach ($unitRows as $ur) {
                $baseUnits[$ur['product_id']] = $ur['base_unit'];
            }

            // Build final result
            $result = [];
            foreach ($products as $prod) {
                $pid = $prod['product_id'];
                $others = $othersByProduct[$pid] ?? [];

                // Calculate normalized price per base unit for selected supplier
                $selBuyPrice = floatval($prod['selected_buy_price'] ?? 0);
                $selBaseQty  = floatval($prod['selected_base_qty'] ?? 1) ?: 1;
                $selNormPrice = $selBuyPrice / $selBaseQty;

                // Find minimum normalized price across all other suppliers
                $minOtherNormPrice = PHP_INT_MAX;
                foreach ($others as &$oth) {
                    $othBuyPrice = floatval($oth['last_buy_price'] ?? 0);
                    $othBaseQty  = floatval($oth['last_base_qty'] ?? 1) ?: 1;
                    $oth['norm_price'] = $othBuyPrice / $othBaseQty;
                    if ($oth['norm_price'] < $minOtherNormPrice) {
                        $minOtherNormPrice = $oth['norm_price'];
                    }
                }
                unset($oth);

                // Determine if selected supplier is the cheapest
                $isCheapest = $selNormPrice <= $minOtherNormPrice;
                $cheapestNorm = min($selNormPrice, $minOtherNormPrice === PHP_INT_MAX ? $selNormPrice : $minOtherNormPrice);
                $savingsPct = $cheapestNorm > 0 && $minOtherNormPrice !== PHP_INT_MAX
                    ? round((($selNormPrice - $cheapestNorm) / $cheapestNorm) * 100, 1)
                    : 0;

                $result[] = [
                    'product_id'         => $pid,
                    'product_name'       => $prod['product_name'],
                    'short_label'        => $prod['short_label'],
                    'category_name'      => $prod['category_name'],
                    'base_unit'          => $baseUnits[$pid] ?? 'pcs',
                    'selected_buy_price' => $selBuyPrice,
                    'selected_unit_name' => $prod['selected_unit_name'],
                    'selected_last_date' => $prod['selected_last_date'],
                    'selected_norm_price'=> round($selNormPrice, 2),
                    'is_cheapest'        => $isCheapest,
                    'savings_pct'        => $isCheapest ? 0 : $savingsPct,
                    'other_suppliers'    => $others,
                ];
            }

            // Sort: cheapest selected supplier first, then non-cheapest
            usort($result, function($a, $b) {
                if ($a['is_cheapest'] !== $b['is_cheapest']) return $b['is_cheapest'] <=> $a['is_cheapest'];
                return $a['selected_norm_price'] <=> $b['selected_norm_price'];
            });

            // Only include products that have at least one other supplier (multi-supplier products)
            $result = array_values(array_filter($result, function($item) {
                return !empty($item['other_suppliers']);
            }));

            $this->json([
                'success'       => true,
                'supplier_name' => $supplierName,
                'data'          => $result,
            ]);

        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get suppliers that have at least one product purchased from multiple suppliers.
     */
    public function getSuppliersForComparison()
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT DISTINCT s.id, s.name
                FROM suppliers s
                JOIN purchases pu ON s.id = pu.supplier_id
                JOIN purchase_items pi ON pu.id = pi.purchase_id
                WHERE pi.product_id IN (
                    SELECT product_id
                    FROM purchase_items pi_inner
                    JOIN purchases pu_inner ON pi_inner.purchase_id = pu_inner.id
                    WHERE pu_inner.supplier_id IS NOT NULL
                    GROUP BY product_id
                    HAVING COUNT(DISTINCT pu_inner.supplier_id) >= 2
                )
                ORDER BY s.name ASC
            ");
            $stmt->execute();
            $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->json([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function searchProducts()
    {
        try {
            $model = new ProductModel();
            $q = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            
            $isPos = isset($_GET['pos']) && $_GET['pos'] == 1;

            // Validate input
            if (strlen($q) < 1) {
                // Return recent/all products (limited) for browsing
                $results = $model->searchProducts('', 30, $isPos);
                if (!is_array($results)) $results = [];
            } else {
                // Search products
                $results = $model->searchProducts($q, 15, $isPos);
            }
            
            // Ensure results is array
            if (!is_array($results)) {
                $results = [];
            }
            
            // Add stock info to each result
            if (count($results) > 0) {
                $db = Database::getInstance()->getConnection();
                foreach ($results as &$r) {
                    try {
                        $stmt = $db->prepare("SELECT current_qty_base FROM stock WHERE product_id = :id LIMIT 1");
                        $stmt->execute([':id' => $r['id']]);
                        $r['current_qty_base'] = (int)($stmt->fetchColumn() ?: 0);
                    } catch (Exception $e) {
                        // If stock lookup fails, default to 0
                        $r['current_qty_base'] = 0;
                    }
                }
                unset($r); // Break reference
            }
            
            // Add packagings so POS can read tier pricing (qty_prices)
            $model->attachPackagingsForProductList($results);
            
            $this->json($results);
        } catch (\Throwable $e) {
            // Log error and return empty array instead of error
            error_log('Search Products Error: ' . $e->getMessage());
            $this->json([]);
        }
    }


    public function getByBarcode(string $code)
    {
        $code = trim(urldecode($code));
        $model = new ProductModel();
        $isPos = isset($_GET['pos']) && $_GET['pos'] == 1;
        $product = $model->findByBarcode($code, $isPos);
        if (!$product) {
            $this->json(['error' => 'Produk tidak ditemukan'], 404);
            return;
        }
        $product = $this->enrichProductDetailData($product);
        $this->json($product);
    }

    public function getById(int $id)
    {
        $model = new ProductModel();
        $product = $model->findWithDetails($id);
        if (!$product) {
            $this->json(['error' => 'Produk tidak ditemukan'], 404);
            return;
        }
        $product = $this->enrichProductDetailData($product);
        $this->json($product);
    }

    private function enrichProductDetailData(array $product): array
    {
        $model = new ProductModel();
        $productId = (int)$product['id'];

        // 1. Packagings with Markup % calculation
        $packagings = $model->getPackagings($productId);
        foreach ($packagings as &$p) {
            $buyPrice = (float)($p['buy_price'] ?? 0);
            $retailPrice = (float)($p['sell_price_retail'] ?? 0);
            $wholesalePrice = (float)($p['sell_price_wholesale'] ?? 0);

            // Calculate Markup % = ((Sell - Buy) / Buy) * 100
            $p['markup_retail_percent'] = ($buyPrice > 0) ? round((($retailPrice - $buyPrice) / $buyPrice) * 100, 1) : 0;
            $p['profit_retail_nominal'] = $retailPrice - $buyPrice;

            $p['markup_wholesale_percent'] = ($buyPrice > 0 && $wholesalePrice > 0) ? round((($wholesalePrice - $buyPrice) / $buyPrice) * 100, 1) : 0;
            $p['profit_wholesale_nominal'] = ($wholesalePrice > 0) ? ($wholesalePrice - $buyPrice) : 0;
        }
        unset($p);
        $product['packagings'] = $packagings;

        // 2. Fetch Suppliers & Purchase History grouped by Supplier
        $suppliers = [];
        try {
            $stmtSup = $this->db->prepare("
                SELECT DISTINCT s.id as supplier_id, s.name as supplier_name, s.address, s.notes,
                       COALESCE(sp.last_buy_price, 0) as last_buy_price,
                       sp.last_purchase_date,
                       COALESCE(sp.purchase_count, 0) as purchase_count
                FROM suppliers s
                JOIN supplier_products sp ON sp.supplier_id = s.id
                WHERE sp.product_id = :pid1

                UNION

                SELECT DISTINCT s.id as supplier_id, s.name as supplier_name, s.address, s.notes,
                       0 as last_buy_price,
                       MAX(pu.purchase_date) as last_purchase_date,
                       COUNT(pu.id) as purchase_count
                FROM suppliers s
                JOIN purchases pu ON pu.supplier_id = s.id
                JOIN purchase_items pi ON pi.purchase_id = pu.id
                WHERE pi.product_id = :pid2
                GROUP BY s.id, s.name, s.address, s.notes
            ");
            $stmtSup->execute([':pid1' => $productId, ':pid2' => $productId]);
            $rawSupplierRows = $stmtSup->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Deduplicate suppliers cleanly by supplier_id
            $suppliersMap = [];
            foreach ($rawSupplierRows as $sup) {
                $sid = (int)$sup['supplier_id'];
                if (!isset($suppliersMap[$sid])) {
                    $suppliersMap[$sid] = $sup;
                } else {
                    if (empty($suppliersMap[$sid]['last_buy_price']) && !empty($sup['last_buy_price'])) {
                        $suppliersMap[$sid]['last_buy_price'] = $sup['last_buy_price'];
                    }
                    if (empty($suppliersMap[$sid]['last_purchase_date']) && !empty($sup['last_purchase_date'])) {
                        $suppliersMap[$sid]['last_purchase_date'] = $sup['last_purchase_date'];
                    }
                    $suppliersMap[$sid]['purchase_count'] = max((int)$suppliersMap[$sid]['purchase_count'], (int)$sup['purchase_count']);
                }
            }
            $supplierRows = array_values($suppliersMap);

            foreach ($supplierRows as $sup) {
                $sid = (int)$sup['supplier_id'];
                
                // Fetch purchase history for this product at this supplier (sorted by date DESC, latest on top)
                $stmtHist = $this->db->prepare("
                    SELECT pu.id as purchase_id, pu.purchase_code, pu.purchase_date, pu.notes,
                           pi.quantity, pi.buy_price as item_buy_price, pi.total_price as item_subtotal,
                           u.name as unit_name, pp.level as packaging_level, pp.base_qty
                    FROM purchase_items pi
                    JOIN purchases pu ON pi.purchase_id = pu.id
                    LEFT JOIN product_packagings pp ON pi.packaging_id = pp.id
                    LEFT JOIN units u ON pp.unit_id = u.id
                    WHERE pi.product_id = :pid AND pu.supplier_id = :sid
                    ORDER BY pu.purchase_date DESC, pu.id DESC
                    LIMIT 50
                ");
                $stmtHist->execute([':pid' => $productId, ':sid' => $sid]);
                $history = $stmtHist->fetchAll(PDO::FETCH_ASSOC) ?: [];

                $sup['purchases'] = $history;
                $suppliers[] = $sup;
            }
        } catch (\Throwable $e) {
            error_log("[enrichProductDetailData] Supplier history query error: " . $e->getMessage());
        }
        $product['suppliers'] = $suppliers;

        // 3. Last Purchase Insight
        $lastPurchase = null;
        try {
            $stmtLast = $this->db->prepare("
                SELECT pu.purchase_date, pu.purchase_code, s.name as supplier_name, pi.buy_price, u.name as unit_name
                FROM purchase_items pi
                JOIN purchases pu ON pi.purchase_id = pu.id
                LEFT JOIN suppliers s ON pu.supplier_id = s.id
                LEFT JOIN product_packagings pp ON pi.packaging_id = pp.id
                LEFT JOIN units u ON pp.unit_id = u.id
                WHERE pi.product_id = :pid
                ORDER BY pu.purchase_date DESC, pu.id DESC
                LIMIT 1
            ");
            $stmtLast->execute([':pid' => $productId]);
            $lastPurchase = $stmtLast->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log("[enrichProductDetailData] Last purchase query error: " . $e->getMessage());
        }
        $product['last_purchase'] = $lastPurchase;

        return $product;
    }

    public function getProductVariants(int $id)
    {
        $model = new ProductModel();
        $ref = $model->findWithDetails($id);
        if (!$ref) {
            $this->json(['success' => false, 'message' => 'Referensi tidak ditemukan'], 404);
            return;
        }

        $brandId     = $ref['brand_id'];
        $productType = trim((string)($ref['product_type'] ?? ''));
        $weightValue = trim((string)($ref['weight_value'] ?? ''));
        $weightUnit  = trim((string)($ref['weight_unit'] ?? ''));

        // Jika brand kosong, jangan tebak-tebak otomatis
        if (!$brandId) {
            $this->json(['success' => true, 'variants' => []]);
            return;
        }

        $whereSql = "p.id != :id AND p.is_active = 1 AND p.brand_id = :bid";
        $params   = [
            ':id'  => $id,
            ':bid' => $brandId
        ];

        // Filter Jenis Produk:
        // Jika referensi punya jenis, maka target harus berjenis SAMA atau BLANK.
        // Jika referensi TIDAK punya jenis (blank), maka kita cari target hanya berdasarkan Brand & Volume.
        if ($productType !== '') {
            $whereSql .= " AND (p.product_type = :ptype OR p.product_type IS NULL OR p.product_type = '')";
            $params[':ptype'] = $productType;
        }

        if ($weightValue !== '' && $weightUnit !== '') {
            $whereSql .= " AND p.weight_value = :wv AND p.weight_unit = :wu";
            $params[':wv'] = $weightValue;
            $params[':wu'] = $weightUnit;
        } else {
            $whereSql .= " AND (p.weight_value IS NULL OR p.weight_value = '')";
        }

        $sql = "SELECT p.id, p.full_name, p.short_label, p.code, p.photo,
                       b.name as brand_name, c.name as category_name
                FROM products p
                LEFT JOIN brands b ON p.brand_id  = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE $whereSql
                ORDER BY p.full_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($variants)) {
            $model->attachPackagingsForProductList($variants);
        }

        $this->json(['success' => true, 'variants' => array_values($variants)]);
    }

    public function applyMultivariantPricing()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $refId     = isset($_POST['reference_id']) ? (int)$_POST['reference_id'] : 0;
        $targetIds = isset($_POST['target_ids']) && is_array($_POST['target_ids']) ? $_POST['target_ids'] : [];

        if (!$refId || empty($targetIds)) {
            $this->json(['success' => false, 'message' => 'Data tidak lengkap: reference_id=' . $refId . ', target_ids count=' . count($targetIds)]);
            return;
        }

        $model = new ProductModel();
        $refPackagings = $model->getPackagings($refId);

        if (empty($refPackagings)) {
            $this->json(['success' => false, 'message' => 'Produk referensi tidak memiliki kemasan (id=' . $refId . ')']);
            return;
        }

        $errors  = [];
        $success = 0;

        try {
            $this->db->beginTransaction();

            foreach ($targetIds as $rawId) {
                $tId = (int)$rawId;
                if ($tId <= 0 || $tId === $refId) continue;

                // ── 1. Ambil kemasan lama dari produk target ──
                $stmtOld = $this->db->prepare("SELECT id, level FROM product_packagings WHERE product_id = ?");
                $stmtOld->execute([$tId]);
                $oldPkgs = [];
                $level1PkgId = null;
                foreach ($stmtOld->fetchAll() as $row) {
                    $lvl = (int)$row['level'];
                    $oldPkgs[$lvl] = (int)$row['id'];
                    if ($lvl === 1) {
                        $level1PkgId = (int)$row['id'];
                    }
                }

                // ── 2. Loop referensi, Update atau Insert kemasan target ──
                foreach ($refPackagings as $pkg) {
                    $lvl = (int)$pkg['level'];

                    if (isset($oldPkgs[$lvl])) {
                        // UPDATE kemasan lama yang ada di level ini
                        $pkgId = $oldPkgs[$lvl];
                        $stmtUpdatePkg = $this->db->prepare("
                            UPDATE product_packagings SET
                                unit_id = ?, contained_qty = ?, base_qty = ?,
                                buy_price = ?, sell_price_retail = ?, margin_retail = ?,
                                sell_price_wholesale = ?, margin_wholesale = ?
                            WHERE id = ?
                        ");
                        $stmtUpdatePkg->execute([
                            $pkg['unit_id'],
                            $pkg['contained_qty'] ?? 1,
                            $pkg['base_qty'] ?? 1,
                            $pkg['buy_price'] ?? 0,
                            $pkg['sell_price_retail'] ?? 0,
                            $pkg['margin_retail'] ?? 0,
                            $pkg['sell_price_wholesale'] ?? 0,
                            $pkg['margin_wholesale'] ?? 0,
                            $pkgId
                        ]);

                        // Hapus harga tier lama
                        $this->db->prepare("DELETE FROM product_qty_prices WHERE packaging_id = ?")->execute([$pkgId]);

                        unset($oldPkgs[$lvl]); // Hapus dari daftar sisa
                    } else {
                        // INSERT kemasan baru
                        $stmtInsertPkg = $this->db->prepare("
                            INSERT INTO product_packagings
                                (product_id, unit_id, level, contained_qty, base_qty,
                                 buy_price, sell_price_retail, margin_retail,
                                 sell_price_wholesale, margin_wholesale, barcode)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmtInsertPkg->execute([
                            $tId,
                            $pkg['unit_id'],
                            $lvl,
                            $pkg['contained_qty'] ?? 1,
                            $pkg['base_qty'] ?? 1,
                            $pkg['buy_price'] ?? 0,
                            $pkg['sell_price_retail'] ?? 0,
                            $pkg['margin_retail'] ?? 0,
                            $pkg['sell_price_wholesale'] ?? 0,
                            $pkg['margin_wholesale'] ?? 0,
                            null
                        ]);
                        $pkgId = (int)$this->db->lastInsertId();
                    }

                    if ($lvl === 1) {
                        $level1PkgId = $pkgId; // Simpan level 1 id untuk fallback
                    }

                    // Insert harga tier baru
                    if (!empty($pkg['qty_prices'])) {
                        $stmtT = $this->db->prepare("
                            INSERT INTO product_qty_prices
                                (packaging_id, min_qty, unit_price, sale_mode, label, sort_order)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        foreach ($pkg['qty_prices'] as $tier) {
                            $stmtT->execute([
                                $pkgId,
                                $tier['min_qty'] ?? 1,
                                $tier['unit_price'] ?? 0,
                                $tier['sale_mode'] ?? 'both',
                                $tier['label'] ?? null,
                                $tier['sort_order'] ?? 0,
                            ]);
                        }
                    }
                }

                // ── 3. Hapus sisa kemasan target yang levelnya tidak ada di referensi ──
                if (!empty($oldPkgs)) {
                    $in = implode(',', array_map('intval', $oldPkgs));

                    // Pindahkan referensi FK (purchase_items, sale_items) ke Level 1
                    // agar tidak error ON DELETE RESTRICT
                    if ($level1PkgId) {
                        $this->db->exec("UPDATE purchase_items SET packaging_id = {$level1PkgId} WHERE packaging_id IN ($in)");
                        $this->db->exec("UPDATE sale_items SET packaging_id = {$level1PkgId} WHERE packaging_id IN ($in)");
                    }

                    // Hapus tier prices sisa
                    $this->db->exec("DELETE FROM product_qty_prices WHERE packaging_id IN ($in)");

                    // Baru hapus kemasan sisanya
                    $this->db->exec("DELETE FROM product_packagings WHERE id IN ($in)");
                }

                // Bump updated_at so product rises to top of list
                $this->db->prepare("UPDATE products SET updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                         ->execute([$tId]);

                $success++;
            }

            $this->db->commit();
            $this->json([
                'success' => true,
                'message' => "Harga berhasil diaplikasikan ke {$success} produk" . (!empty($errors) ? '. Gagal: ' . implode(', ', $errors) : ''),
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('[AlfarezMart][applyMultivariantPricing] ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }


    /**
     * Simpan tier harga spesial per kuantitas untuk satu kemasan
     */
    public function savePackagingQtyPrices(int $id)
    {
        $this->validateCSRF();
        try {
            $packagingId = (int)$id;
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $tiers = $jsonBody['tiers'] ?? $this->input('tiers') ?? [];
            if (!is_array($tiers)) {
                throw new Exception('Data tier harga tidak valid');
            }
            $model = new ProductModel();
            $model->saveQtyPricesForPackaging($packagingId, $tiers);
            $this->json([
                'success' => true,
                'message' => 'Harga kuantitas berhasil disimpan',
                'qty_prices' => $model->getQtyPricesByPackaging($packagingId),
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate unique barcode (for manual input / print)
     */
    public function generateBarcode()
    {
        $barcode = Helper::generateBarcode();
        $this->json([
            'success' => true,
            'barcode' => $barcode,
            'prefix' => defined('BARCODE_PREFIX') ? BARCODE_PREFIX : 'AM',
        ]);
    }

    public function createProduct()
    {
        $this->validateCSRF();
        $model = new ProductModel();

        if (empty($_POST)) {
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            if (is_array($jsonBody)) {
                $_POST = array_merge($_POST, $jsonBody);
            }
        }

        try {
            // Compose fallback nama produk supaya constraint NOT NULL `full_name` tidak gagal.
            $fullName = trim((string)$this->input('full_name'));
            $shortLabel = trim((string)$this->input('short_label'));
            $invoiceName = trim((string)$this->input('invoice_name'));
            $singleName = trim((string)$this->input('single_name'));
            $variant = trim((string)$this->input('variant'));
            $productType = trim((string)$this->input('product_type'));
            // Resolve brand name from id (best effort)
            $brandName = '';
            $brandId = $this->input('brand_id');
            if (!empty($brandId)) {
                try {
                    $b = (new BrandModel())->find((int)$brandId);
                    if ($b && !empty($b['name'])) $brandName = trim((string)$b['name']);
                } catch (Exception $ex) { /* abaikan */ }
            }
            if ($fullName === '' || $fullName === '-') {
                $composed = trim($brandName . ' ' . $productType . ' ' . $variant);
                $fullName = $singleName !== '' ? $singleName : ($shortLabel !== '' ? $shortLabel : $composed);
            }
            if ($fullName === '') {
                throw new Exception('Nama produk wajib diisi (Brand/Jenis/Varian atau Nama Produk).');
            }
            if ($shortLabel === '') {
                $shortLabel = mb_substr($fullName, 0, 35);
            }
            if ($invoiceName === '') {
                $invoiceName = $shortLabel;
            }

            $productData = [
                'code' => $this->input('code'),
                'brand_id' => $brandId ?: null,
                'category_id' => $this->input('category_id') ?: null,
                'product_type' => $productType !== '' ? $productType : null,
                'variant' => $variant !== '' ? $variant : null,
                'full_name' => $fullName,
                'short_label' => $shortLabel,
                'invoice_name' => $invoiceName,
                'supplier_product_code' => $this->input('supplier_product_code') ?: null,
                'supplier_invoice_name' => $this->input('supplier_invoice_name') ?: null,
                'weight_value' => $this->input('weight_value') ?: null,
                'weight_unit' => $this->input('weight_unit'),
                'is_custom_label' => $this->input('is_custom_label') ? 1 : 0,
                'is_available' => isset($_POST['is_available']) ? (int)$_POST['is_available'] : 1,
            ];

            $packagings = [];
            $unitIds = $_POST['unit_id'] ?? [];
            if (empty($unitIds)) {
                throw new Exception("Minimal harus ada 1 satuan terkecil.");
            }

            foreach ($unitIds as $i => $unitId) {
                if (empty($unitId)) continue;
                
                $level = $i + 1;
                $cqty = isset($_POST['contained_qty'][$i]) ? (float)$_POST['contained_qty'][$i] : 1;
                $buy = (float)($_POST['buy_price'][$i] ?? 0);
                $retail = $_POST['sell_price_retail'][$i] ?? 0;
                $wholesale = $_POST['sell_price_wholesale'][$i] ?? 0;

                $barcode = trim($_POST['barcode'][$i] ?? '');
                if (empty($barcode) && $level == 1) {
                    $barcode = Helper::generateBarcode();
                }
                if (!empty($barcode) && Helper::barcodeExists($barcode)) {
                    throw new Exception("Barcode \"{$barcode}\" sudah digunakan produk lain.");
                }

                // Terapkan PPN & Diskon langsung ke harga modal
                $ppnPct       = (float)($_POST['ppn_pct'][$i] ?? 0);
                $discountMode  = $_POST['discount_mode'][$i] ?? 'rp';
                $discountValue = (float)($_POST['discount_value'][$i] ?? 0);
                $buyAfterPpn   = $buy * (1 + $ppnPct / 100);
                if ($discountMode === 'pct') {
                    $finalBuy = $buyAfterPpn * (1 - $discountValue / 100);
                } else {
                    $finalBuy = $buyAfterPpn - $discountValue;
                }
                $finalBuy = max(0, round($finalBuy, 2));

                $packagings[] = [
                    'level' => $level,
                    'unit_id' => $unitId,
                    'contained_qty' => $cqty,
                    'barcode' => $barcode,
                    'buy_price' => $finalBuy,
                    'sell_price_retail' => $retail,
                    'margin_retail' => $retail > 0 ? Helper::calculateMargin($finalBuy, $retail) : 0,
                    'sell_price_wholesale' => $wholesale,
                    'margin_wholesale' => $wholesale > 0 ? Helper::calculateMargin($finalBuy, $wholesale) : 0,
                    'qty_prices' => json_decode($_POST['qty_prices_json'][$i] ?? '[]', true) ?: [],
                ];
            }

            $id = $model->createWithDetails($productData, $packagings);
            $this->json(['success' => true, 'id' => $id, 'message' => 'Produk berhasil ditambahkan']);

        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateProduct(int $id)
    {
        $this->blockStaffMutations('mengedit');
        $this->validateCSRF();
        $model = new ProductModel();

        try {
            $data = [];
            $fields = ['full_name','short_label','invoice_name','product_type','variant','brand_id','category_id',
                       'weight_value','weight_unit', 'supplier_product_code', 'supplier_invoice_name', 'is_custom_label', 'is_available'];
            $nullableFields = ['brand_id','product_type','variant','weight_value','weight_unit', 'supplier_product_code', 'supplier_invoice_name'];
            // Fields that can legitimately be 0
            $integerFields = ['is_custom_label', 'is_available'];
            foreach ($fields as $f) {
                $val = $this->input($f);
                if (in_array($f, $integerFields)) {
                    // For integer fields, always save even if 0
                    if ($val !== null) {
                        $data[$f] = (int)$val;
                    }
                } elseif ($val !== null && $val !== '') {
                    $data[$f] = $val;
                } elseif (in_array($f, $nullableFields)) {
                    // Allow clearing nullable fields
                    $data[$f] = null;
                }
            }
            $data['updated_at'] = date('Y-m-d H:i:s');
            $model->update($id, $data);
            $this->json(['success' => true, 'message' => 'Produk berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateProductAvailability(int $id)
    {
        $this->blockStaffMutations('mengedit');
        $this->validateCSRF();
        $model = new ProductModel();
        try {
            $is_available = (int)$this->input('is_available');
            $model->update($id, ['is_available' => $is_available, 'updated_at' => date('Y-m-d H:i:s')]);
            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update print label (short_label) for thermal receipt.
     * Also accepts optional supplier_product_code and supplier_invoice_name
     * so the product show page can save them inline without going to the edit page.
     */
    public function updateProductLabel(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new ProductModel();
            $shortLabel = $this->input('short_label');
            $invoiceName = $this->input('invoice_name');

            // Supplier fields (optional — only update when explicitly sent)
            $supplierCode    = $this->input('supplier_product_code');
            $supplierInvName = $this->input('supplier_invoice_name');

            $model->updatePrintLabel((int) $id, $shortLabel, $invoiceName);

            // Save supplier info if provided
            if ($supplierCode !== null || $supplierInvName !== null) {
                $supplierData = ['updated_at' => date('Y-m-d H:i:s')];
                if ($supplierCode !== null) {
                    $supplierData['supplier_product_code'] = trim($supplierCode) !== '' ? trim($supplierCode) : null;
                }
                if ($supplierInvName !== null) {
                    $supplierData['supplier_invoice_name'] = trim($supplierInvName) !== '' ? trim($supplierInvName) : null;
                }
                $model->update($id, $supplierData);
            }

            $product = $model->findWithDetails($id);
            $this->json([
                'success'               => true,
                'message'               => 'Label cetak berhasil disimpan',
                'short_label'           => $product['short_label'],
                'invoice_name'          => $product['invoice_name'],
                'supplier_product_code' => $product['supplier_product_code'] ?? null,
                'supplier_invoice_name' => $product['supplier_invoice_name'] ?? null,
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update product photo (compressed base64 + bg removed)
     */
    public function updateProductPhoto(int $id)
    {
        $this->validateCSRF();
        try {
            $id = (int)$id;
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $deletePhoto = $jsonBody['delete_photo'] ?? $this->input('delete_photo');
            $model = new ProductModel();

            if (!empty($deletePhoto) && $deletePhoto == '1') {
                $oldProduct = $model->find($id);
                if (!empty($oldProduct['photo'])) {
                    $oldPath = strpos($oldProduct['photo'], 'storage/') === 0
                        ? dirname(BASE_PATH) . '/' . ltrim($oldProduct['photo'], '/')
                        : BASE_PATH . '/' . ltrim($oldProduct['photo'], '/');
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $model->update($id, ['photo' => null]);
                $this->json(['success' => true, 'photo' => null]);
                return;
            }

            $base64 = $jsonBody['photo_base64'] ?? $this->input('photo_base64');
            if (empty($base64)) throw new Exception("Foto tidak ditemukan");

            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
                $type = strtolower($type[1]);
                if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $base64 = base64_decode($base64);
                    if ($base64 !== false) {
                        $filename = 'prod_' . $id . '_' . time() . '.' . $type;
                        $dir = STORAGE_PATH . '/uploads/products';
                        if (!is_dir($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        $filepath = $dir . '/' . $filename;
                        if (file_put_contents($filepath, $base64)) {
                            $photoPath = 'storage/uploads/products/' . $filename;
                            $model = new ProductModel();
                            
                            // Delete old photo
                            $oldProduct = $model->find($id);
                            if (!empty($oldProduct['photo'])) {
                                $oldPath = strpos($oldProduct['photo'], 'storage/') === 0
                                    ? dirname(BASE_PATH) . '/' . ltrim($oldProduct['photo'], '/')
                                    : BASE_PATH . '/' . ltrim($oldProduct['photo'], '/');
                                if (file_exists($oldPath)) @unlink($oldPath);
                            }

                            $model->update($id, ['photo' => $photoPath]);
                            $this->json(['success' => true, 'photo' => $photoPath]);
                            return;
                        }
                    }
                }
            }
            throw new Exception("Gagal memproses format gambar");
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List variant siblings for label distribution preview
     */
    public function getProductLabelVariants(int $id)
    {
        $model = new ProductModel();
        $product = $model->findWithDetails($id);
        if (!$product) {
            $this->json(['error' => 'Produk tidak ditemukan'], 404);
            return;
        }
        $siblings = $model->findVariantSiblings($id);
        $this->json([
            'product' => $product,
            'siblings' => $siblings,
            'count' => count($siblings),
        ]);
    }

    /**
     * Distribute label base to all variants (same brand + product_type)
     */
    public function distributeProductLabel(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new ProductModel();
            $labelBase = $this->input('label_base') ?: $this->input('short_label');
            $result = $model->distributePrintLabel((int) $id, $labelBase);
            $this->json([
                'success' => true,
                'message' => "Label berhasil diterapkan ke {$result['updated']} produk varian",
                'updated' => $result['updated'],
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store info for receipt printing
     */
    public function getReceiptSettings()
    {
        $settings = new SettingModel();
        $this->json([
            'store_name' => $settings->get('store_name', 'AlfarezMart'),
            'store_address' => $settings->get('store_address', ''),
            'store_phone' => $settings->get('store_phone', ''),
            'thermal_printer_width' => (int) $settings->get('thermal_printer_width', 58),
            'receipt_header' => $settings->get('receipt_header', ''),
            'receipt_footer' => $settings->get('receipt_footer', ''),
            'store_logo' => $settings->get('store_logo', ''),
            'printer_driver' => $settings->get('printer_driver', 'web_bluetooth'),
            'auto_print_checkout' => $settings->get('auto_print_checkout', '1'),
        ]);
    }

    public function saveReceiptSettings()
    {
        $this->validateCSRF();
        try {
            $settings = new SettingModel();
            // Read all from JSON body
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $fields = ['store_name','store_address','store_phone','thermal_printer_width','receipt_header','receipt_footer','printer_driver','auto_print_checkout'];
            foreach ($fields as $f) {
                $val = $data[$f] ?? $this->input($f, '');
                $settings->set($f, $val);
            }
            if (!empty($data['store_logo'])) {
                $settings->set('store_logo', $data['store_logo']);
            }

            $this->json(['success' => true, 'message' => 'Pengaturan struk berhasil disimpan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }


    public function deleteProduct(int $id)
    {
        $this->blockStaffMutations('menghapus');
        $this->validateCSRF();
        $db = Database::getInstance()->getConnection();
        try {
            $id = (int) $id;
            $model = new ProductModel();
            $db->beginTransaction();
            
            // Use prepared statements to prevent SQL injection
            $tables = ['purchase_items', 'sale_items', 'stock_movements', 'stock', 'product_packagings', 'supplier_products'];
            foreach ($tables as $table) {
                $stmt = $db->prepare("DELETE FROM {$table} WHERE product_id = :id");
                $stmt->execute([':id' => $id]);
            }
            $model->delete($id);
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Produk berhasil dihapus']);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDeleteProducts()
    {
        $this->blockStaffMutations('menghapus');
        $this->validateCSRF();
        $db = Database::getInstance()->getConnection();
        try {
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $ids = $jsonBody['ids'] ?? [];
            if (!is_array($ids) || empty($ids)) {
                throw new Exception("Tidak ada produk yang dipilih.");
            }

            $model = new ProductModel();
            $db->beginTransaction();
            
            // Use prepared statements to prevent SQL injection
            $tables = ['purchase_items', 'sale_items', 'stock_movements', 'stock', 'product_packagings', 'supplier_products'];
            
            foreach ($ids as $id) {
                $id = (int)$id;
                foreach ($tables as $table) {
                    $stmt = $db->prepare("DELETE FROM {$table} WHERE product_id = :id");
                    $stmt->execute([':id' => $id]);
                }
                $model->delete($id);
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => count($ids) . ' produk berhasil dihapus']);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a single product packaging (price, barcode)
     */
    public function updatePackaging(int $id)
    {
        $this->validateCSRF();
        try {
            $id = (int) $id;
            $db = Database::getInstance()->getConnection();
            
            $buyPrice = (float) $this->input('buy_price', 0);
            $retailPrice = (float) $this->input('sell_price_retail', 0);
            $wholesalePrice = (float) $this->input('sell_price_wholesale', 0);
            $barcode = trim($this->input('barcode', ''));
            $unitId = (int)$this->input('unit_id', 0);
            $forceReplace = (bool)$this->input('force_replace', false);
            
            // Terapkan PPN & Diskon langsung ke harga modal
            $ppnPct        = (float) $this->input('ppn_pct', 0);
            $discountMode  = $this->input('discount_mode', 'rp');
            $discountValue = (float) $this->input('discount_value', 0);
            $buyAfterPpn   = $buyPrice * (1 + $ppnPct / 100);
            if ($discountMode === 'pct') {
                $finalBuyPrice = $buyAfterPpn * (1 - $discountValue / 100);
            } else {
                $finalBuyPrice = $buyAfterPpn - $discountValue;
            }
            $finalBuyPrice = max(0, round($finalBuyPrice, 2));

            if (!empty($barcode)) {
                $owner = Helper::barcodeOwner($barcode, $id);
                if ($owner) {
                    if (!$forceReplace) {
                        // Return conflict info for UI to show confirmation
                        $this->json([
                            'error' => 'barcode_conflict',
                            'message' => "Barcode \"{$barcode}\" sudah digunakan oleh produk lain.",
                            'conflict' => [
                                'packaging_id' => $owner['packaging_id'],
                                'product_id' => $owner['product_id'],
                                'product_name' => $owner['short_label'] ?: $owner['full_name'],
                                'unit_name' => $owner['unit_name'] ?? '',
                                'level' => $owner['level'],
                            ]
                        ], 409);
                        return;
                    }
                    // Force replace: clear barcode from old packaging
                    $stmtClear = $db->prepare("UPDATE product_packagings SET barcode = NULL WHERE id = :oid");
                    $stmtClear->execute([':oid' => $owner['packaging_id']]);
                }
            }
            
            // Calculate margins using final buy price (after PPN & discount)
            $marginRetail    = $retailPrice > 0    ? Helper::calculateMargin($finalBuyPrice, $retailPrice)    : 0;
            $marginWholesale = $wholesalePrice > 0 ? Helper::calculateMargin($finalBuyPrice, $wholesalePrice) : 0;
            
            $unitSql = $unitId ? 'unit_id = :uid,' : '';
            $containedQty = $this->input('contained_qty');
            $containedQtySql = ($containedQty !== null && $containedQty !== '') ? 'contained_qty = :cqty,' : '';
            $stmt = $db->prepare("
                UPDATE product_packagings 
                SET {$unitSql}
                    {$containedQtySql}
                    buy_price = :buy, 
                    sell_price_retail = :retail, 
                    sell_price_wholesale = :wholesale,
                    margin_retail = :mr, 
                    margin_wholesale = :mw,
                    barcode = :barcode,
                    ppn_pct = 0,
                    discount_mode = 'rp',
                    discount_value = 0
                WHERE id = :id
            ");
            $params = [
                ':buy'      => $finalBuyPrice,
                ':retail'   => $retailPrice,
                ':wholesale'=> $wholesalePrice,
                ':mr'       => $marginRetail,
                ':mw'       => $marginWholesale,
                ':barcode'  => $barcode ?: null,
                ':id'       => $id
            ];
            if ($unitId) $params[':uid'] = $unitId;
            if ($containedQty !== null && $containedQty !== '') $params[':cqty'] = (float)$containedQty;
            $stmt->execute($params);

            // Update product's updated_at timestamp
            $stmtPid = $db->prepare("SELECT product_id FROM product_packagings WHERE id = :id");
            $stmtPid->execute([':id' => $id]);
            $productId = $stmtPid->fetchColumn();

            // Recalculate base_qty for all levels of this product if contained_qty changed
            if ($productId && $containedQty !== null && $containedQty !== '') {
                $stmtLevels = $db->prepare("SELECT id, level, contained_qty FROM product_packagings WHERE product_id = :pid ORDER BY level ASC");
                $stmtLevels->execute([':pid' => $productId]);
                $levels = $stmtLevels->fetchAll(\PDO::FETCH_ASSOC);
                $runningBase = 1;
                foreach ($levels as $lv) {
                    if ((int)$lv['level'] === 1) {
                        $runningBase = 1;
                    } else {
                        $runningBase *= (float)$lv['contained_qty'];
                    }
                    $stmtBq = $db->prepare("UPDATE product_packagings SET base_qty = :bq WHERE id = :lid");
                    $stmtBq->execute([':bq' => $runningBase, ':lid' => $lv['id']]);
                }
            }

            if ($productId) {
                $db->prepare("UPDATE products SET updated_at = NOW() WHERE id = :pid")
                   ->execute([':pid' => $productId]);
            }

            $this->json(['success' => true, 'message' => 'Harga kemasan berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateBarcodes(int $id)
    {
        $this->validateCSRF();
        try {
            $id = (int)$id;
            $db = Database::getInstance()->getConnection();

            // Read JSON body or form inputs
            $rawInput = file_get_contents('php://input');
            $jsonData = json_decode($rawInput, true) ?: [];
            
            $barcodes = $jsonData['barcodes'] ?? $this->input('barcodes', []);
            if (!is_array($barcodes)) {
                $this->json(['error' => 'Data barcode tidak valid'], 400);
                return;
            }

            // Check for duplicate barcode conflicts
            foreach ($barcodes as $pkgId => $code) {
                $code = trim((string)$code);
                $pkgId = (int)$pkgId;
                if (!empty($code)) {
                    $owner = Helper::barcodeOwner($code, $pkgId);
                    if ($owner && (int)$owner['product_id'] !== $id) {
                        $this->json([
                            'error' => 'barcode_conflict',
                            'message' => "Barcode \"{$code}\" sudah digunakan oleh produk " . ($owner['short_label'] ?: $owner['full_name']) . " (" . ($owner['unit_name'] ?? ('Level ' . $owner['level'])) . ")"
                        ], 409);
                        return;
                    }
                }
            }

            // Execute barcode updates
            $stmt = $db->prepare("UPDATE product_packagings SET barcode = :barcode WHERE id = :pkg_id AND product_id = :pid");
            foreach ($barcodes as $pkgId => $code) {
                $code = trim((string)$code);
                $pkgId = (int)$pkgId;
                $stmt->execute([
                    ':barcode' => !empty($code) ? $code : null,
                    ':pkg_id'  => $pkgId,
                    ':pid'     => $id
                ]);
            }

            $db->prepare("UPDATE products SET updated_at = NOW() WHERE id = :pid")->execute([':pid' => $id]);

            $this->json([
                'success' => true,
                'message' => 'Barcode kemasan berhasil diperbarui'
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Gagal memperbarui barcode: ' . $e->getMessage()], 500);
        }
    }

    public function updateProductStock(int $id)
    {
        $this->validateCSRF();
        try {
            $id = (int)$id;
            $totalQty = (int)$this->input('total_qty', 0);
            $notes = $this->input('notes', 'Manual Update');
            
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            
            // Get old stock for movement log
            $stmt = $db->prepare("SELECT current_qty_base FROM stock WHERE product_id = :id");
            $stmt->execute([':id' => $id]);
            $oldStock = $stmt->fetchColumn() ?: 0;
            
            $diff = $totalQty - $oldStock;
            
            if ($diff != 0) {
                // Update stock table
                $stmtStock = $db->prepare("UPDATE stock SET current_qty_base = :qty WHERE product_id = :id");
                $stmtStock->execute([':qty' => $totalQty, ':id' => $id]);
                
                if ($stmtStock->rowCount() === 0) {
                    // It didn't exist, insert it
                    $stmtIns = $db->prepare("INSERT INTO stock (product_id, current_qty_base) VALUES (:id, :qty)");
                    $stmtIns->execute([':id' => $id, ':qty' => $totalQty]);
                }
                
                // Add movement log
                $stmtLog = $db->prepare("
                    INSERT INTO stock_movements (product_id, movement_type, quantity, reference_type, notes) 
                    VALUES (:id, :mtype, :qty, 'manual', :notes)
                ");
                $stmtLog->execute([
                    ':id' => $id,
                    ':mtype' => 'adjustment',
                    ':qty' => $diff,
                    ':notes' => $notes . " (Diff: " . ($diff > 0 ? "+$diff" : $diff) . ")"
                ]);
            }
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Stok fisik berhasil diupdate', 'new_qty' => $totalQty]);
        } catch (Exception $e) {
            $db = Database::getInstance()->getConnection();
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== PURCHASES =====
    public function deletePurchase(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new PurchaseModel();
            $model->deleteWithRevert($id);
            $this->json(['success' => true, 'message' => 'Pembelian berhasil dihapus dan stok dikembalikan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== BRANDS =====
    public function getBrands()
    {
        $model = new BrandModel();
        $this->json($model->all('name', 'ASC'));
    }

    public function createBrand()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama brand wajib diisi');
            
            // Check for duplicates (case-insensitive)
            $existingBrand = $this->db->prepare("SELECT id FROM brands WHERE LOWER(name) = LOWER(:name) LIMIT 1");
            $existingBrand->execute([':name' => $name]);
            $existing = $existingBrand->fetch();
            
            if ($existing) {
                throw new Exception('Brand "' . htmlspecialchars($name) . '" sudah ada. Gunakan fitur edit untuk mengubahnya.');
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $model = new BrandModel();
            $id = $model->create(['name' => $name, 'slug' => $slug]);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'message' => 'Brand berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function updateBrand(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama brand wajib diisi');
            
            // Check for duplicates (case-insensitive), excluding current record
            $existingBrand = $this->db->prepare("SELECT id FROM brands WHERE LOWER(name) = LOWER(:name) AND id != :id LIMIT 1");
            $existingBrand->execute([':name' => $name, ':id' => $id]);
            $existing = $existingBrand->fetch();
            
            if ($existing) {
                throw new Exception('Brand "' . htmlspecialchars($name) . '" sudah ada. Silakan gunakan nama lain.');
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $model = new BrandModel();
            $model->update($id, [
                'name' => $name, 
                'slug' => $slug,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $this->json(['success' => true, 'message' => 'Brand berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function deleteBrand(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new BrandModel();
            $model->delete($id);
            $this->json(['success' => true, 'message' => 'Brand berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== CATEGORIES =====
    public function getCategories()
    {
        $model = new CategoryModel();
        $this->json($model->all('name', 'ASC'));
    }

    public function createCategory()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama kategori wajib diisi');
            
            // Check for duplicates (case-insensitive)
            $existingCat = $this->db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(:name) LIMIT 1");
            $existingCat->execute([':name' => $name]);
            $existing = $existingCat->fetch();
            
            if ($existing) {
                throw new Exception('Kategori "' . htmlspecialchars($name) . '" sudah ada. Gunakan fitur edit untuk mengubahnya.');
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $model = new CategoryModel();
            $id = $model->create(['name' => $name, 'slug' => $slug]);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'message' => 'Kategori berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function updateCategory(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama kategori wajib diisi');
            
            // Check for duplicates (case-insensitive), excluding current record
            $existingCat = $this->db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(:name) AND id != :id LIMIT 1");
            $existingCat->execute([':name' => $name, ':id' => $id]);
            $existing = $existingCat->fetch();
            
            if ($existing) {
                throw new Exception('Kategori "' . htmlspecialchars($name) . '" sudah ada. Silakan gunakan nama lain.');
            }
            
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
            $model = new CategoryModel();
            $model->update($id, ['name' => $name, 'slug' => $slug]);
            $this->json(['success' => true, 'message' => 'Kategori berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function deleteCategory(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new CategoryModel();
            $model->delete($id);
            $this->json(['success' => true, 'message' => 'Kategori berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== UNITS =====
    public function getUnits()
    {
        $model = new UnitModel();
        $this->json($model->all('name', 'ASC'));
    }

    public function createUnit()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama satuan wajib diisi');
            $abbr = $this->input('abbreviation');
            if (empty($abbr)) $abbr = strtolower(substr($name, 0, 3));
            
            $model = new UnitModel();
            
            // Check for duplicates (case-insensitive)
            $existingUnit = $this->db->prepare("SELECT id FROM units WHERE LOWER(name) = LOWER(:name) LIMIT 1");
            $existingUnit->execute([':name' => $name]);
            $existing = $existingUnit->fetch();
            
            if ($existing) {
                throw new Exception('Satuan "' . htmlspecialchars($name) . '" sudah ada. Gunakan fitur edit untuk mengubahnya.');
            }
            
            $id = $model->create(['name' => $name, 'abbreviation' => $abbr]);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'abbreviation' => $abbr, 'message' => 'Satuan berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function updateUnit(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama satuan wajib diisi');
            $abbr = $this->input('abbreviation');
            if (empty($abbr)) $abbr = strtolower(substr($name, 0, 3));
            
            $model = new UnitModel();
            
            // Check for duplicates (case-insensitive), excluding current record
            $existingUnit = $this->db->prepare("SELECT id FROM units WHERE LOWER(name) = LOWER(:name) AND id != :id LIMIT 1");
            $existingUnit->execute([':name' => $name, ':id' => $id]);
            $existing = $existingUnit->fetch();
            
            if ($existing) {
                throw new Exception('Satuan "' . htmlspecialchars($name) . '" sudah ada. Silakan gunakan nama lain.');
            }
            
            $model->update($id, ['name' => $name, 'abbreviation' => $abbr]);
            $this->json(['success' => true, 'message' => 'Satuan berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 409);
        }
    }

    public function deleteUnit(int $id)
    {
        $this->validateCSRF();
        $db = Database::getInstance()->getConnection();
        try {
            $db->beginTransaction();
            
            // Set null on product_packagings to prevent FK constraint error
            $stmt = $db->prepare("UPDATE product_packagings SET unit_id = NULL WHERE unit_id = :id");
            $stmt->execute([':id' => $id]);

            $model = new UnitModel();
            $model->delete($id);
            
            $db->commit();
            $this->json(['success' => true, 'message' => 'Satuan berhasil dihapus']);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== SUPPLIERS =====
    public function getSuppliers()
    {
        $model = new SupplierModel();
        $this->json($model->getAllWithType());
    }

    public function searchSuppliers()
    {
        $model = new SupplierModel();
        $q = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
        if (strlen($q) < 1) {
            $this->json([]);
            return;
        }
        $results = $model->searchSuppliersAndSales($q);
        $this->json($results);
    }

    public function getSupplierTypes()
    {
        $model = new SupplierTypeModel();
        $this->json($model->all('name', 'ASC'));
    }

    public function createSupplier()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama supplier wajib diisi');
            $model = new SupplierModel();
            $id = $model->create([
                'name' => $name,
                'type_id' => $this->input('type_id') ?: null,
                'address' => $this->input('address'),
                'products_sold' => $this->input('products_sold'),
                'is_consignment' => $this->input('is_consignment') ? 1 : 0,
                'notes' => $this->input('notes'),
            ]);
            $supplier = $model->findWithType($id);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'supplier' => $supplier]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateSupplier(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama supplier wajib diisi');
            $model = new SupplierModel();
            $model->update($id, [
                'name' => $name,
                'type_id' => $this->input('type_id') ?: null,
                'address' => $this->input('address'),
                'products_sold' => $this->input('products_sold'),
                'is_consignment' => $this->input('is_consignment') ? 1 : 0,
                'notes' => $this->input('notes'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $supplier = $model->findWithType($id);
            $this->json(['success' => true, 'supplier' => $supplier, 'message' => 'Supplier berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteSupplier(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new SupplierModel();
            $model->delete($id);
            $this->json(['success' => true, 'message' => 'Supplier berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== DASHBOARD =====
    public function getDashboardStats()
    {
        $model = new ProductModel();
        $this->json($model->getStats());
    }

    // ===== PURCHASES =====
    public function getPurchases()
    {
        $model = new PurchaseModel();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $this->json($model->getList($page, 20));
    }

    public function createPurchase()
    {
        $this->validateCSRF();
        try {
            $model = new PurchaseModel();
            
            // Expect JSON body from fetch
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $items = $jsonBody['items'] ?? [];
            
            if (empty($items)) {
                throw new Exception("Daftar barang tidak boleh kosong.");
            }

            // Format data
            $date = date('ymd');
            $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $invoiceNumber = "PUR-{$date}-{$random}";
            
            $photoPath = null;
            if (!empty($jsonBody['invoice_photo_base64'])) {
                $base64 = $jsonBody['invoice_photo_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]);
                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $base64 = base64_decode($base64);
                        if ($base64 !== false) {
                            $dir = STORAGE_PATH . '/uploads/invoice_photos';
                            if (!is_dir($dir)) {
                                mkdir($dir, 0755, true);
                            }
                            $filename = 'inv_' . $invoiceNumber . '_' . time() . '.' . $type;
                            $filepath = $dir . '/' . $filename;
                            if (file_put_contents($filepath, $base64)) {
                                $photoPath = 'storage/uploads/invoice_photos/' . $filename;
                            }
                        }
                    }
                }
            }

            $headerData = [
                'purchase_code' => $invoiceNumber,
                'supplier_id' => $jsonBody['supplier_id'] ?? $this->input('supplier_id'),
                'sales_rep_id' => $jsonBody['sales_rep_id'] ?? $this->input('sales_rep_id'),
                'purchase_date' => $jsonBody['purchase_date'] ?? $this->input('purchase_date') ?: date('Y-m-d'),
                'total_amount' => $jsonBody['total_amount'] ?? $this->input('total_amount') ?: 0,
                'grand_total' => $jsonBody['grand_total'] ?? $this->input('grand_total') ?: 0,
                'invoice_photo' => $photoPath,
                'notes' => $jsonBody['notes'] ?? $this->input('notes'),
            ];

            $id = $model->createWithDetails($headerData, $items);
            $this->json(['success' => true, 'id' => $id, 'message' => 'Pembelian berhasil disimpan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePurchase(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new PurchaseModel();
            
            // Validate existing purchase
            $existing = $model->getDetails($id);
            if (!$existing) {
                throw new Exception("Data pembelian tidak ditemukan");
            }

            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $items = $jsonBody['items'] ?? [];
            
            if (empty($items)) {
                throw new Exception("Daftar barang tidak boleh kosong.");
            }

            $photoPath = null;
            if (!empty($jsonBody['invoice_photo_base64'])) {
                $base64 = $jsonBody['invoice_photo_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]);
                    if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $base64 = base64_decode($base64);
                        if ($base64 !== false) {
                            $dir = STORAGE_PATH . '/uploads/invoice_photos';
                            if (!is_dir($dir)) {
                                mkdir($dir, 0755, true);
                            }
                            $filename = 'inv_' . $existing['purchase_code'] . '_' . time() . '.' . $type;
                            $filepath = $dir . '/' . $filename;
                            if (file_put_contents($filepath, $base64)) {
                                $photoPath = 'storage/uploads/invoice_photos/' . $filename;
                            }
                        }
                    }
                }
            }

            $headerData = [
                'purchase_code' => $existing['purchase_code'], // Keep original code
                'supplier_id' => $jsonBody['supplier_id'] ?? $this->input('supplier_id'),
                'sales_rep_id' => $jsonBody['sales_rep_id'] ?? $this->input('sales_rep_id'),
                'purchase_date' => $jsonBody['purchase_date'] ?? $this->input('purchase_date') ?: date('Y-m-d'),
                'total_amount' => $jsonBody['total_amount'] ?? $this->input('total_amount') ?: 0,
                'grand_total' => $jsonBody['grand_total'] ?? $this->input('grand_total') ?: 0,
                'invoice_photo' => $photoPath, // Will be coalesced in model if null
                'notes' => $jsonBody['notes'] ?? $this->input('notes'),
            ];

            $model->updateWithDetails($id, $headerData, $items);
            $this->json(['success' => true, 'message' => 'Pembelian berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function uploadInvoicePhoto(int $id) { 
        $this->json(['message' => 'Coming soon']); 
    }

    /**
     * Serve invoice photo securely from STORAGE_PATH (outside public_html).
     * Usage: GET /api/storage/invoice-photo?file=inv_PUR-XXXXXX.jpg
     */
    public function serveInvoicePhoto()
    {
        // Get filename from query string (only the basename, no paths)
        $file = $_GET['file'] ?? '';

        // Security: strip any path traversal attempts, keep only basename
        $file = basename($file);

        if (empty($file)) {
            http_response_code(400);
            echo 'Missing file parameter';
            exit;
        }

        $filepath = STORAGE_PATH . '/uploads/invoice_photos/' . $file;

        if (!file_exists($filepath) || !is_file($filepath)) {
            http_response_code(404);
            echo 'File not found';
            exit;
        }

        // Detect MIME type and restrict to images only
        $mime = mime_content_type($filepath);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowedMimes)) {
            http_response_code(403);
            echo 'Forbidden file type';
            exit;
        }

        // Send image response with caching headers
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: private, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        readfile($filepath);
        exit;
    }

    // ===== SALES REPS =====
    public function getSalesRepsBySupplier(int $supplierId)
    {
        $model = new SalesRepModel();
        $this->json($model->getBySupplier($supplierId));
    }

    public function getAllSalesReps()
    {
        $model = new SalesRepModel();
        $this->json($model->getAllWithSupplier());
    }

    public function createSalesRep()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $supplierId = $this->input('supplier_id');
            if (empty($name)) throw new Exception('Nama sales wajib diisi');
            if (empty($supplierId)) throw new Exception('Supplier wajib dipilih');

            $model = new SalesRepModel();
            $id = $model->create([
                'name' => $name,
                'supplier_id' => $supplierId,
                'phone' => $this->input('phone', ''),
                'visit_day' => $this->input('visit_day', ''),
                'delivery_day' => $this->input('delivery_day', ''),
                'notes' => $this->input('notes', ''),
                'status' => $this->input('status', 'Aktif'),
            ]);
            $salesRep = $model->findWithSupplier($id);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'sales_rep' => $salesRep]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateSalesRep(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama sales wajib diisi');

            $model = new SalesRepModel();
            $model->update($id, [
                'name' => $name,
                'phone' => $this->input('phone', ''),
                'visit_day' => $this->input('visit_day', ''),
                'delivery_day' => $this->input('delivery_day', ''),
                'notes' => $this->input('notes', ''),
                'status' => $this->input('status', 'Aktif'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $salesRep = $model->findWithSupplier($id);
            $this->json(['success' => true, 'sales_rep' => $salesRep, 'message' => 'Sales berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteSalesRep(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new SalesRepModel();
            $model->delete($id);
            $this->json(['success' => true, 'message' => 'Sales berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== SUPPLIER PRODUCTS =====
    public function getProductsBySupplier(int $supplierId)
    {
        $model = new SupplierProductModel();
        $q = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
        $salesRepId = isset($_GET['sales_rep_id']) ? (int)$_GET['sales_rep_id'] : null;

        if (!empty($q)) {
            $results = $model->searchBySupplier($supplierId, $q, $salesRepId);
        } else {
            $results = $model->getProductsBySupplier($supplierId, $salesRepId);
        }
        $this->json($results);
    }

    public function getBulkSupplierProducts(int $supplierId)
    {
        $salesRepId = isset($_GET['sales_rep_id']) ? (int)$_GET['sales_rep_id'] : null;
        $model = new SupplierProductModel();
        $products = $model->getProductsBySupplier($supplierId, $salesRepId);
        
        $productModel = new ProductModel();
        foreach ($products as &$p) {
            $p['packagings'] = $productModel->getPackagings($p['id']);
        }
        
        $this->json($products);
    }

    public function addSupplierProduct(int $supplierId)
    {
        $this->validateCSRF();
        try {
            $productId = $this->input('product_id');
            if (!$productId) throw new Exception('Pilih produk terlebih dahulu');
            
            $model = new SupplierProductModel();
            // Using trackSupplierProduct to insert/upsert
            $model->trackSupplierProduct($supplierId, $productId);
            $this->json(['success' => true, 'message' => 'Produk berhasil ditambahkan ke supplier']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeSupplierProduct(int $supplierId, int $productId)
    {
        $this->validateCSRF();
        try {
            $model = new SupplierProductModel();
            $model->removeSupplierProduct($supplierId, $productId);
            $this->json(['success' => true, 'message' => 'Produk dihapus dari daftar supplier']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function searchProductsForPurchase()
    {
        try {
            $q = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            $supplierId = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : null;
            $salesRepId = isset($_GET['sales_rep_id']) ? (int)$_GET['sales_rep_id'] : null;

            if (strlen($q) < 2) {
                $this->json([]);
                return;
            }

            // First: search within supplier products
            $supplierResults = [];
            if ($supplierId) {
                $spModel = new SupplierProductModel();
                $supplierResults = $spModel->searchBySupplier($supplierId, $q, $salesRepId);
            }

            // Then: search all products (to allow adding new products to supplier)
            $productModel = new ProductModel();
            $allResults = $productModel->searchProducts($q, 15);

            // Mark which ones are already supplier products
            $supplierProductIds = array_column($supplierResults, 'id');
            foreach ($allResults as &$p) {
                $p['is_supplier_product'] = in_array($p['id'], $supplierProductIds) ? 1 : 0;
            }

            // Merge: supplier products first, then others
            $otherResults = array_filter($allResults, function($p) use ($supplierProductIds) {
                return !in_array($p['id'], $supplierProductIds);
            });

            $merged = array_merge($supplierResults, array_values($otherResults));
            $finalResults = array_slice($merged, 0, 20);
            
            // Attach packagings so frontend can show them in the suggestion list
            $productModel->attachPackagingsForProductList($finalResults);

            $this->json($finalResults);
        } catch (\Throwable $e) {
            error_log('Search Products For Purchase Error: ' . $e->getMessage());
            $this->json([]);
        }
    }
    public function getSalesAnalytics()
    {
        try {
            $model = new SaleModel();
            $filters = [
                'date_from'   => trim($_GET['date_from'] ?? ''),
                'date_to'     => trim($_GET['date_to'] ?? ''),
                'customer_id' => trim($_GET['customer_id'] ?? ''),
            ];

            $transactions     = $model->getFiltered($filters);
            $customerRanking  = $model->getCustomerProfitRanking($filters);

            $totalOmzet  = array_sum(array_column($transactions, 'total_amount'));
            $totalProfit = array_sum(array_column($transactions, 'total_profit'));

            $this->json([
                'success' => true,
                'data' => [
                    'transactions'       => $transactions,
                    'customer_ranking'   => $customerRanking,
                    'summary' => [
                        'total_transactions' => count($transactions),
                        'total_omzet'        => $totalOmzet,
                        'total_profit'       => $totalProfit,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function createSale()
    {
        $this->validateCSRF();
        try {
            $model = new SaleModel();
            
            // Format data
            $date = date('ymd');
            $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $invoiceNumber = "INV-{$date}-{$random}";
            
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $items = $jsonBody['items'] ?? [];
            
            if (empty($items)) {
                throw new Exception("Keranjang belanja kosong.");
            }

            $headerData = [
                'invoice_number' => $invoiceNumber,
                'customer_id' => $jsonBody['customer_id'] ?? null,
                'sale_mode' => $jsonBody['sale_mode'] ?? 'retail',
                'total_amount' => $jsonBody['total_amount'] ?? 0,
                'payment_method' => $jsonBody['payment_method'] ?? 'Cash',
                'payment_status' => $jsonBody['payment_status'] ?? 'Lunas',
                'notes' => $jsonBody['notes'] ?? '',
            ];

            $id = $model->createWithDetails($headerData, $items);
            $this->json(['success' => true, 'id' => $id, 'invoice' => $invoiceNumber, 'message' => 'Transaksi berhasil disimpan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getInvoice(int $id) {
        try {
            $model = new SaleModel();
            // Try by ID first, then by invoice number
            $transaction = is_numeric($id)
                ? $model->getTransactionDetails((int)$id)
                : $model->findByInvoice($id);

            if (!$transaction) {
                $this->json(['error' => 'Transaksi tidak ditemukan'], 404);
                return;
            }
            $this->json(['success' => true, 'transaction' => $transaction]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== SALES BULK DELETE & UPDATE =====

    public function bulkDeleteSales()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $ids = $jsonBody['ids'] ?? [];
            if (empty($ids) || !is_array($ids)) {
                throw new \Exception('Tidak ada transaksi yang dipilih');
            }

            $model = new SaleModel();
            $deleted = 0;
            foreach ($ids as $id) {
                $model->deleteSale((int)$id);
                $deleted++;
            }

            $this->json([
                'success' => true,
                'deleted' => $deleted,
                'message' => "$deleted transaksi berhasil dihapus"
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateSale(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new SaleModel();

            // 1. Verify the old transaction exists
            $old = $model->getTransactionDetails($id);
            if (!$old) {
                $this->json(['error' => 'Transaksi tidak ditemukan'], 404);
                return;
            }

            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $items = $jsonBody['items'] ?? [];
            if (empty($items)) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            // 2. Delete old transaction (reverses stock, finance, movements)
            $model->deleteSale($id);

            // 3. Re-create with same invoice number
            $headerData = [
                'invoice_number' => $old['invoice_number'],
                'customer_id'    => $jsonBody['customer_id'] ?? $old['customer_id'],
                'sale_mode'      => $jsonBody['sale_mode'] ?? $old['sale_mode'],
                'total_amount'   => $jsonBody['total_amount'] ?? 0,
                'payment_method' => $jsonBody['payment_method'] ?? $old['payment_method'],
                'payment_status' => $jsonBody['payment_status'] ?? $old['payment_status'],
                'notes'          => $jsonBody['notes'] ?? ($old['notes'] ?? ''),
            ];

            $newId = $model->createWithDetails($headerData, $items);

            $this->json([
                'success' => true,
                'id'      => $newId,
                'invoice' => $old['invoice_number'],
                'message' => 'Transaksi berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== PACKAGING ADD/DELETE =====
    public function addPackaging(int $productId)
    {
        $this->validateCSRF();
        try {
            $productId = (int)$productId;
            $db = Database::getInstance()->getConnection();

            // Get current max level
            $stmt = $db->prepare("SELECT MAX(level) FROM product_packagings WHERE product_id = :pid");
            $stmt->execute([':pid' => $productId]);
            $maxLevel = (int)$stmt->fetchColumn();
            $newLevel = $maxLevel + 1;

            $unitId = $this->input('unit_id');
            if (!$unitId) throw new Exception("Satuan wajib dipilih");

            $containedQty = (float)($this->input('contained_qty') ?: 1);
            if ($newLevel > 1 && $containedQty < 1) throw new Exception("Isi kemasan minimal 1");

            // Calculate base_qty
            $stmtBase = $db->prepare("SELECT base_qty FROM product_packagings WHERE product_id = :pid ORDER BY level DESC LIMIT 1");
            $stmtBase->execute([':pid' => $productId]);
            $lastBase = (float)($stmtBase->fetchColumn() ?: 1);
            $baseQty = ($newLevel === 1) ? 1 : $lastBase * $containedQty;

            $buyPrice  = (float)$this->input('buy_price', 0);
            $retail    = (float)$this->input('sell_price_retail', 0);
            $wholesale = (float)$this->input('sell_price_wholesale', 0);
            $barcode   = trim($this->input('barcode', ''));
            if (empty($barcode)) $barcode = Helper::generateBarcode();

            // Terapkan PPN & Diskon langsung ke harga modal
            $ppnPct        = (float) $this->input('ppn_pct', 0);
            $discountMode  = $this->input('discount_mode', 'rp');
            $discountValue = (float) $this->input('discount_value', 0);
            $buyAfterPpn   = $buyPrice * (1 + $ppnPct / 100);
            if ($discountMode === 'pct') {
                $finalBuyPrice = $buyAfterPpn * (1 - $discountValue / 100);
            } else {
                $finalBuyPrice = $buyAfterPpn - $discountValue;
            }
            $finalBuyPrice = max(0, round($finalBuyPrice, 2));

            $stmt = $db->prepare("
                INSERT INTO product_packagings
                  (product_id, level, unit_id, contained_qty, base_qty, buy_price,
                   sell_price_retail, sell_price_wholesale, margin_retail, margin_wholesale, barcode)
                VALUES (:pid,:level,:uid,:cqty,:bqty,:buy,:retail,:wholesale,:mr,:mw,:barcode)
            ");
            $stmt->execute([
                ':pid'       => $productId,
                ':level'     => $newLevel,
                ':uid'       => $unitId,
                ':cqty'      => $containedQty,
                ':bqty'      => $baseQty,
                ':buy'       => $finalBuyPrice,
                ':retail'    => $retail,
                ':wholesale' => $wholesale,
                ':mr'        => $retail > 0 ? Helper::calculateMargin($finalBuyPrice, $retail) : 0,
                ':mw'        => $wholesale > 0 ? Helper::calculateMargin($finalBuyPrice, $wholesale) : 0,
                ':barcode'   => $barcode ?: null,
            ]);
            $newId = $db->lastInsertId();
            $this->json(['success' => true, 'id' => $newId, 'level' => $newLevel, 'message' => 'Level kemasan berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deletePackaging(int $id)
    {
        $this->blockStaffMutations('menghapus kemasan');
        $this->validateCSRF();
        try {
            $id = (int)$id;
            $db = Database::getInstance()->getConnection();

            // Don't allow deleting level 1
            $stmt = $db->prepare("SELECT level FROM product_packagings WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            if (!$row) throw new Exception("Kemasan tidak ditemukan");
            if ($row['level'] == 1) throw new Exception("Satuan terkecil (Level 1) tidak bisa dihapus");

            $stmtPid = $db->prepare("SELECT product_id FROM product_packagings WHERE id = :id");
            $stmtPid->execute([':id' => $id]);
            $productId = $stmtPid->fetchColumn();

            $stmt = $db->prepare("DELETE FROM product_packagings WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Re-number levels
            if ($productId) {
                $stmtGet = $db->prepare("SELECT id FROM product_packagings WHERE product_id = :pid ORDER BY level ASC");
                $stmtGet->execute([':pid' => $productId]);
                $remainingPkgs = $stmtGet->fetchAll(PDO::FETCH_ASSOC);
                
                $newLevel = 1;
                foreach ($remainingPkgs as $pkg) {
                    $stmtUpdate = $db->prepare("UPDATE product_packagings SET level = :lvl WHERE id = :id");
                    $stmtUpdate->execute([':lvl' => $newLevel, ':id' => $pkg['id']]);
                    $newLevel++;
                }
            }

            $this->json(['success' => true, 'message' => 'Level kemasan berhasil dihapus']);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
                try {
                    $db = Database::getInstance()->getConnection();
                    
                    // Function to drop FK
                    $dropFk = function($table, $column) use ($db) {
                        $stmt = $db->prepare("
                            SELECT CONSTRAINT_NAME
                            FROM information_schema.KEY_COLUMN_USAGE
                            WHERE TABLE_SCHEMA = DATABASE()
                              AND TABLE_NAME = :table
                              AND COLUMN_NAME = :column
                              AND REFERENCED_TABLE_NAME IS NOT NULL
                        ");
                        $stmt->execute([':table' => $table, ':column' => $column]);
                        while($fkName = $stmt->fetchColumn()) {
                            $db->exec("ALTER TABLE {$table} DROP FOREIGN KEY {$fkName}");
                        }
                    };

                    // Fix purchase_items
                    $dropFk('purchase_items', 'packaging_id');
                    $db->exec("ALTER TABLE purchase_items MODIFY packaging_id INT NULL");
                    $db->exec("ALTER TABLE purchase_items ADD CONSTRAINT fk_pi_pkg FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE SET NULL");

                    // Fix sale_items
                    $dropFk('sale_items', 'packaging_id');
                    $db->exec("ALTER TABLE sale_items MODIFY packaging_id INT NULL");
                    $db->exec("ALTER TABLE sale_items ADD CONSTRAINT fk_si_pkg FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE SET NULL");

                    // Retry deletion
                    $stmt = $db->prepare("DELETE FROM product_packagings WHERE id = :id");
                    $stmt->execute([':id' => $id]);
                    
                    if ($productId) {
                        $stmtGet = $db->prepare("SELECT id FROM product_packagings WHERE product_id = :pid ORDER BY level ASC");
                        $stmtGet->execute([':pid' => $productId]);
                        $remainingPkgs = $stmtGet->fetchAll(PDO::FETCH_ASSOC);
                        
                        $newLevel = 1;
                        foreach ($remainingPkgs as $pkg) {
                            $stmtUpdate = $db->prepare("UPDATE product_packagings SET level = :lvl WHERE id = :id");
                            $stmtUpdate->execute([':lvl' => $newLevel, ':id' => $pkg['id']]);
                            $newLevel++;
                        }
                    }

                    $this->json(['success' => true, 'message' => 'Level kemasan berhasil dihapus (beserta penyesuaian riwayat)']);
                } catch (Exception $ex) {
                    $this->json(['error' => 'Gagal menyesuaikan riwayat transaksi: ' . $ex->getMessage()], 500);
                }
            } else {
                $this->json(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePackagingUnit(int $id)
    {
        $this->validateCSRF();
        try {
            $id = (int)$id;
            $db = Database::getInstance()->getConnection();
            $unitId = (int)$this->input('unit_id');
            if (!$unitId) throw new Exception("Satuan wajib dipilih");
            $stmt = $db->prepare("UPDATE product_packagings SET unit_id = :uid WHERE id = :id");
            $stmt->execute([':uid' => $unitId, ':id' => $id]);
            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== USER MANAGEMENT =====
    public function getUsers()
    {
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        $model = new UserModel();
        $this->json($model->getAllUsers(1, 100));
    }

    public function createUser()
    {
        $this->validateCSRF();
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $name      = $this->input('name');
            $email     = $this->input('email');
            $phone     = $this->input('phone');
            $password  = $this->input('password');
            $userLevel = $this->input('user_level', 'staff');

            $workDays  = $this->input('work_days');
            $workStart = $this->input('work_start');
            $workEnd   = $this->input('work_end');

            if (empty($name))     throw new Exception("Nama wajib diisi");
            if (empty($email) && empty($phone)) throw new Exception("Email atau No HP wajib diisi");
            if (empty($password)) throw new Exception("Password wajib diisi");
            if (!in_array($userLevel, ['superadmin','admin','staff','customer'])) throw new Exception("Level tidak valid");

            $model = new UserModel();
            $id = $model->createUser([
                'name'       => $name,
                'email'      => $email ?: null,
                'phone'      => $phone ?: null,
                'password'   => $password,
                'user_level' => $userLevel,
                'is_active'  => 1,
                'work_days'  => $userLevel === 'staff' ? $workDays : null,
                'work_start' => $userLevel === 'staff' && $workStart ? $workStart : null,
                'work_end'   => $userLevel === 'staff' && $workEnd ? $workEnd : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->json(['success' => true, 'id' => $id, 'message' => 'User berhasil dibuat']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateUser(int $id)
    {
        $this->validateCSRF();
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $name      = $this->input('name');
            $email     = $this->input('email');
            $phone     = $this->input('phone');
            $userLevel = $this->input('user_level');
            $workDays  = $this->input('work_days');
            $workStart = $this->input('work_start');
            $workEnd   = $this->input('work_end');

            if (empty($name)) throw new Exception("Nama wajib diisi");
            if (!in_array($userLevel, ['superadmin','admin','staff','customer'])) throw new Exception("Level tidak valid");

            $model = new UserModel();
            
            $updateData = [
                'name'       => $name,
                'email'      => $email ?: null,
                'phone'      => $phone ?: null,
                'user_level' => $userLevel,
                'work_days'  => $userLevel === 'staff' ? $workDays : null,
                'work_start' => $userLevel === 'staff' && $workStart ? $workStart : null,
                'work_end'   => $userLevel === 'staff' && $workEnd ? $workEnd : null,
            ];
            
            $model->update($id, $updateData);
            
            $this->json(['success' => true, 'message' => 'User berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleUserStatus(int $id)
    {
        $this->validateCSRF();
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = :id");
            $stmt->execute([':id' => (int)$id]);
            $stmt2 = $db->prepare("SELECT is_active FROM users WHERE id = :id");
            $stmt2->execute([':id' => (int)$id]);
            $isActive = (bool)$stmt2->fetchColumn();
            $this->json(['success' => true, 'is_active' => $isActive, 'message' => $isActive ? 'User diaktifkan' : 'User dinonaktifkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resetUserPassword(int $id)
    {
        $this->validateCSRF();
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $newPassword = $this->input('password');
            if (empty($newPassword)) throw new Exception("Password baru wajib diisi");
            $model = new UserModel();
            $model->changePassword((int)$id, $newPassword);
            $this->json(['success' => true, 'message' => 'Password berhasil direset']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteUser(int $id)
    {
        $this->validateCSRF();
        $level = $_SESSION['user_level'] ?? '';
        if (!in_array($level, ['superadmin'])) {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $id = (int)$id;
            if ($id === (int)($_SESSION['user_id'] ?? 0)) throw new Exception("Tidak bisa menghapus akun sendiri");
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $this->json(['success' => true, 'message' => 'User berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // DEBTS & CUSTOMERS API
    // ==========================================

    public function getCustomers()
    {
        try {
            $model = new DebtModel();
            $search = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            $customers = $model->getCustomers($search);
            $this->json(['success' => true, 'data' => $customers]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createCustomer()
    {
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $name = $this->input('name');
            $phone = $this->input('phone');
            $address = $this->input('address');
            $notes = $this->input('notes');
            $typeId = $this->input('type_id');

            if (empty($name)) {
                throw new Exception("Nama pelanggan wajib diisi");
            }

            $customerId = $model->createCustomer([
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'notes' => $notes,
                'type_id' => !empty($typeId) ? (int)$typeId : null
            ]);

            $this->json(['success' => true, 'message' => 'Pelanggan berhasil ditambahkan', 'customer_id' => $customerId]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCustomer(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $name = $this->input('name');
            $phone = $this->input('phone');
            $address = $this->input('address');
            $notes = $this->input('notes');
            $typeId = $this->input('type_id');

            if (empty($name)) {
                throw new Exception("Nama pelanggan wajib diisi");
            }

            $model->updateCustomer((int)$id, [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'notes' => $notes,
                'type_id' => !empty($typeId) ? (int)$typeId : null
            ]);

            $this->json(['success' => true, 'message' => 'Pelanggan berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteCustomer(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $model->deleteCustomer((int)$id);
            $this->json(['success' => true, 'message' => 'Pelanggan berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCustomerDebts()
    {
        $this->requireSuperadmin();
        try {
            $model = new DebtModel();
            $status = isset($_GET['status']) ? Security::sanitize($_GET['status']) : null;
            $search = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            $debts = $model->getCustomerDebts($status, $search);
            
            $debtIds = array_column($debts, 'id');
            $paymentsByDebtId = [];
            if (!empty($debtIds)) {
                $inQuery = implode(',', array_fill(0, count($debtIds), '?'));
                $stmt = $this->db->prepare("SELECT * FROM customer_debt_payments WHERE debt_id IN ($inQuery) ORDER BY payment_date DESC, id DESC");
                $stmt->execute($debtIds);
                $allPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allPayments as $p) {
                    $paymentsByDebtId[$p['debt_id']][] = $p;
                }
            }

            foreach ($debts as &$d) {
                $d['payments'] = $paymentsByDebtId[$d['id']] ?? [];
            }
            unset($d);

            $this->json(['success' => true, 'data' => $debts]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createCustomerDebt()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $customerId = $this->input('customer_id');
            $fallbackName = $this->input('customer_name_fallback');
            $amount = (float)$this->input('amount');
            $date = $this->input('debt_date');
            $dueDate = $this->input('due_date');
            $notes = $this->input('notes');
            $saleId = $this->input('sale_id');

            if (empty($customerId) && empty($fallbackName)) {
                throw new Exception("Pelanggan harus dipilih atau diisi nama manual");
            }
            if ($amount <= 0) {
                throw new Exception("Jumlah hutang harus lebih dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal hutang wajib diisi");
            }

            $debtId = $model->createCustomerDebt([
                'customer_id' => !empty($customerId) ? (int)$customerId : null,
                'customer_name_fallback' => !empty($fallbackName) ? $fallbackName : null,
                'amount' => $amount,
                'debt_date' => $date,
                'due_date' => !empty($dueDate) ? $dueDate : null,
                'notes' => $notes,
                'sale_id' => !empty($saleId) ? (int)$saleId : null
            ]);

            $this->json(['success' => true, 'message' => 'Hutang pelanggan berhasil dicatat', 'debt_id' => $debtId]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function payCustomerDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $amount = (float)$this->input('amount');
            $date = $this->input('payment_date');
            $notes = $this->input('notes');

            if ($amount <= 0) {
                throw new Exception("Nominal pembayaran harus lebih besar dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal pembayaran wajib diisi");
            }

            $debt = $model->getCustomerDebtById($id);
            if (!$debt) {
                throw new Exception("Data hutang tidak ditemukan");
            }
            if ($amount > $debt['remaining_amount']) {
                throw new Exception("Nominal pembayaran melebihi sisa hutang (Sisa: Rp " . number_format($debt['remaining_amount'], 0, ',', '.') . ")");
            }

            $model->addCustomerPayment((int)$id, $amount, $date, $notes);
            $this->json(['success' => true, 'message' => 'Cicilan pembayaran berhasil dicatat']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCustomerDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $customerId = $this->input('customer_id');
            $fallbackName = $this->input('customer_name_fallback');
            $amount = (float)$this->input('amount');
            $date = $this->input('debt_date');
            $dueDate = $this->input('due_date');
            $notes = $this->input('notes');

            if (empty($customerId) && empty($fallbackName)) {
                throw new Exception("Pelanggan harus dipilih atau diisi nama manual");
            }
            if ($amount <= 0) {
                throw new Exception("Jumlah hutang harus lebih dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal hutang wajib diisi");
            }

            $model->updateCustomerDebt((int)$id, [
                'customer_id' => !empty($customerId) ? (int)$customerId : null,
                'customer_name_fallback' => !empty($fallbackName) ? $fallbackName : null,
                'amount' => $amount,
                'debt_date' => $date,
                'due_date' => !empty($dueDate) ? $dueDate : null,
                'notes' => $notes
            ]);

            $this->json(['success' => true, 'message' => 'Catatan piutang berhasil diperbarui']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteCustomerDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $model->deleteCustomerDebt((int)$id);
            $this->json(['success' => true, 'message' => 'Catatan hutang berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getShopDebts()
    {
        $this->requireSuperadmin();
        try {
            $model = new DebtModel();
            $status = isset($_GET['status']) ? Security::sanitize($_GET['status']) : null;
            $search = isset($_GET['q']) ? Security::sanitize($_GET['q']) : '';
            $debts = $model->getShopDebts($status, $search);

            $debtIds = array_column($debts, 'id');
            $paymentsByDebtId = [];
            if (!empty($debtIds)) {
                $inQuery = implode(',', array_fill(0, count($debtIds), '?'));
                $stmt = $this->db->prepare("SELECT * FROM shop_debt_payments WHERE debt_id IN ($inQuery) ORDER BY payment_date DESC, id DESC");
                $stmt->execute($debtIds);
                $allPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allPayments as $p) {
                    $paymentsByDebtId[$p['debt_id']][] = $p;
                }
            }

            foreach ($debts as &$d) {
                $d['payments'] = $paymentsByDebtId[$d['id']] ?? [];
            }
            unset($d);

            $this->json(['success' => true, 'data' => $debts]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createShopDebt()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $supplierId = $this->input('supplier_id');
            $fallbackName = $this->input('supplier_name_fallback');
            $amount = (float)$this->input('amount');
            $date = $this->input('debt_date');
            $dueDate = $this->input('due_date');
            $notes = $this->input('notes');
            $purchaseId = $this->input('purchase_id');

            if (empty($supplierId) && empty($fallbackName)) {
                throw new Exception("Supplier harus dipilih atau diisi nama manual");
            }
            if ($amount <= 0) {
                throw new Exception("Jumlah hutang harus lebih dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal hutang wajib diisi");
            }

            $debtId = $model->createShopDebt([
                'supplier_id' => !empty($supplierId) ? (int)$supplierId : null,
                'supplier_name_fallback' => !empty($fallbackName) ? $fallbackName : null,
                'amount' => $amount,
                'debt_date' => $date,
                'due_date' => !empty($dueDate) ? $dueDate : null,
                'notes' => $notes,
                'purchase_id' => !empty($purchaseId) ? (int)$purchaseId : null
            ]);

            $this->json(['success' => true, 'message' => 'Hutang toko berhasil dicatat', 'debt_id' => $debtId]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function payShopDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $amount = (float)$this->input('amount');
            $date = $this->input('payment_date');
            $notes = $this->input('notes');

            if ($amount <= 0) {
                throw new Exception("Nominal pembayaran harus lebih besar dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal pembayaran wajib diisi");
            }

            $debt = $model->getShopDebtById($id);
            if (!$debt) {
                throw new Exception("Data hutang tidak ditemukan");
            }
            if ($amount > $debt['remaining_amount']) {
                throw new Exception("Nominal pembayaran melebihi sisa hutang (Sisa: Rp " . number_format($debt['remaining_amount'], 0, ',', '.') . ")");
            }

            $model->addShopPayment((int)$id, $amount, $date, $notes);
            $this->json(['success' => true, 'message' => 'Pembayaran berhasil dicatat']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateShopDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $supplierId = $this->input('supplier_id');
            $debtSourceId = $this->input('debt_source_id');
            $fallbackName = $this->input('supplier_name_fallback');
            $amount = (float)$this->input('amount');
            $date = $this->input('debt_date');
            $dueDate = $this->input('due_date');
            $notes = $this->input('notes');

            if (empty($supplierId) && empty($debtSourceId) && empty($fallbackName)) {
                throw new Exception("Supplier/Sumber harus dipilih atau diisi nama manual");
            }
            if ($amount <= 0) {
                throw new Exception("Jumlah hutang harus lebih dari 0");
            }
            if (empty($date)) {
                throw new Exception("Tanggal hutang wajib diisi");
            }

            $model->updateShopDebt((int)$id, [
                'supplier_id' => !empty($supplierId) ? (int)$supplierId : null,
                'debt_source_id' => !empty($debtSourceId) ? (int)$debtSourceId : null,
                'supplier_name_fallback' => !empty($fallbackName) ? $fallbackName : null,
                'amount' => $amount,
                'debt_date' => $date,
                'due_date' => !empty($dueDate) ? $dueDate : null,
                'notes' => $notes
            ]);

            $this->json(['success' => true, 'message' => 'Catatan hutang toko berhasil diperbarui']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteShopDebt(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $model->deleteShopDebt((int)$id);
            $this->json(['success' => true, 'message' => 'Catatan hutang berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== FINANCE LOGS API =====
    // ===== FINANCE ACCOUNTS & CATEGORIES API =====
    public function getFinanceAccounts()
    {
        $this->requireSuperadmin();
        try {
            $model = new FinanceModel();
            $accounts = $model->getActiveAccounts();
            $this->json(['success' => true, 'data' => $accounts]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createFinanceAccount()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $depId = $this->input('dependency_account_id');
            if (empty($name)) {
                throw new Exception("Nama POS Keuangan wajib diisi");
            }
            
            $db = Database::getInstance()->getConnection();
            
            $checkStmt = $db->prepare("SELECT id, is_active FROM finance_accounts WHERE name = :name");
            $checkStmt->execute([':name' => $name]);
            $existing = $checkStmt->fetch();
            
            if ($existing) {
                if ($existing['is_active'] == 1) {
                    throw new Exception("POS Keuangan dengan nama tersebut sudah ada");
                } else {
                    $updateStmt = $db->prepare("UPDATE finance_accounts SET is_active = 1, dependency_account_id = :dep WHERE id = :id");
                    $updateStmt->execute([
                        ':dep' => empty($depId) ? null : (int)$depId,
                        ':id' => $existing['id']
                    ]);
                }
            } else {
                $stmt = $db->prepare("INSERT INTO finance_accounts (name, dependency_account_id) VALUES (:name, :dep)");
                $stmt->execute([
                    ':name' => $name,
                    ':dep' => empty($depId) ? null : (int)$depId
                ]);
            }
            
            $this->json(['success' => true, 'message' => 'POS Keuangan berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFinanceAccount(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $depId = $this->input('dependency_account_id');
            if (empty($name)) {
                throw new Exception("Nama POS Keuangan wajib diisi");
            }
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE finance_accounts SET name = :name, dependency_account_id = :dep WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':dep' => empty($depId) ? null : (int)$depId,
                ':id' => $id
            ]);
            
            $this->json(['success' => true, 'message' => 'POS Keuangan berhasil diperbarui']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteFinanceAccount(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE finance_accounts SET is_active = 0 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $this->json(['success' => true, 'message' => 'POS Keuangan berhasil dihapus (soft delete)']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFinanceCategories()
    {
        $this->requireSuperadmin();
        try {
            $type = $this->input('type');
            $model = new FinanceModel();
            $categories = $model->getActiveCategories($type);
            $this->json(['success' => true, 'data' => $categories]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createFinanceCategory()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $type = $this->input('type');
            if (empty($name) || empty($type)) {
                throw new Exception("Nama dan Tipe wajib diisi");
            }
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO finance_categories (name, type) VALUES (:name, :type) ON DUPLICATE KEY UPDATE is_active = 1");
            $stmt->execute([
                ':name' => $name,
                ':type' => $type
            ]);
            
            $this->json(['success' => true, 'message' => 'Kategori berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFinanceCategory(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            $type = $this->input('type');
            if (empty($name) || empty($type)) {
                throw new Exception("Nama dan Tipe wajib diisi");
            }
            
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE finance_categories SET name = :name, type = :type WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':type' => $type,
                ':id' => $id
            ]);
            
            $this->json(['success' => true, 'message' => 'Kategori berhasil diperbarui']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteFinanceCategory(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE finance_categories SET is_active = 0 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $this->json(['success' => true, 'message' => 'Kategori berhasil dihapus (soft delete)']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFinanceSummary()
    {
        $this->requireSuperadmin();
        try {
            $date = isset($_GET['date']) ? $_GET['date'] : (isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'));
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $date = date('Y-m-d');
            }
            $model = new FinanceModel();
            $summary = $model->getDailySummary($date);
            $breakdown = $model->getDailySummaryByPost($date);
            $this->json([
                'success' => true,
                'date' => $date,
                'summary' => [
                    'income' => (float)($summary['income'] ?? 0),
                    'expense' => (float)($summary['expense'] ?? 0),
                    'net' => (float)($summary['accumulative_net'] ?? 0)
                ],
                'breakdown' => $breakdown
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getFinanceLogs()
    {
        $this->requireSuperadmin();
        try {
            $date = isset($_GET['date']) ? $_GET['date'] : (isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'));
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $date = date('Y-m-d');
            }
            $model = new FinanceModel();
            $logs = $model->getLogsByDate($date);
            $this->json([
                'success' => true,
                'date' => $date,
                'logs' => $logs
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createFinanceLog()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new FinanceModel();
            $logDate = $this->input('log_date') ?: date('Y-m-d');
            $amount = (float)$this->input('amount');
            $balanceType = $this->input('balance_type');
            $category = $this->input('category');
            $detail = $this->input('detail');
            $description = $this->input('description');

            if ($amount <= 0) {
                throw new Exception("Nominal harus lebih besar dari 0");
            }
            // Validasi balance_type dari DB (bukan whitelist hardcoded)
            $db = Database::getInstance()->getConnection();
            $chk = $db->prepare("SELECT id FROM finance_accounts WHERE name = :name AND is_active = 1");
            $chk->execute([':name' => $balanceType]);
            if (!$chk->fetch()) {
                throw new Exception("Pos keuangan tidak valid atau tidak aktif");
            }
            if (empty($category) || !in_array($category, ['Pemasukan', 'Pengeluaran'])) {
                throw new Exception("Kategori tidak valid");
            }
            if (empty($detail)) {
                throw new Exception("Detail / Jenis transaksi harus diisi");
            }

            $logId = $model->addLog([
                'log_date' => $logDate,
                'amount' => $amount,
                'balance_type' => $balanceType,
                'category' => $category,
                'detail' => $detail,
                'description' => $description
            ]);

            $this->json([
                'success' => true,
                'message' => 'Catatan keuangan berhasil disimpan',
                'log_id' => $logId
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFinanceLog(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new FinanceModel();
            $log = $model->findLog($id);
            if (!$log) {
                throw new Exception("Data pencatatan tidak ditemukan");
            }

            $logDate = $this->input('log_date') ?: date('Y-m-d');
            $amount = (float)$this->input('amount');
            $balanceType = $this->input('balance_type');
            $category = $this->input('category');
            $detail = $this->input('detail');
            $description = $this->input('description');

            if ($amount <= 0) {
                throw new Exception("Nominal harus lebih besar dari 0");
            }
            // Validasi balance_type dari DB (bukan whitelist hardcoded)
            $db = Database::getInstance()->getConnection();
            $chk = $db->prepare("SELECT id FROM finance_accounts WHERE name = :name AND is_active = 1");
            $chk->execute([':name' => $balanceType]);
            if (!$chk->fetch()) {
                throw new Exception("Pos keuangan tidak valid atau tidak aktif");
            }
            if (empty($category) || !in_array($category, ['Pemasukan', 'Pengeluaran'])) {
                throw new Exception("Kategori tidak valid");
            }
            if (empty($detail)) {
                throw new Exception("Detail / Jenis transaksi harus diisi");
            }

            $model->updateLog($id, [
                'log_date' => $logDate,
                'amount' => $amount,
                'balance_type' => $balanceType,
                'category' => $category,
                'detail' => $detail,
                'description' => $description
            ]);

            $this->json([
                'success' => true,
                'message' => 'Catatan keuangan berhasil diperbarui'
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteFinanceLog(int $id)
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $model = new FinanceModel();
            $log = $model->findLog($id);
            if (!$log) {
                throw new Exception("Data pencatatan tidak ditemukan");
            }

            $model->deleteLog($id);
            $this->json([
                'success' => true,
                'message' => 'Catatan keuangan berhasil dihapus'
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDeleteFinanceLogs()
    {
        $this->requireSuperadmin();
        $this->validateCSRF();
        try {
            $jsonBody = json_decode(file_get_contents('php://input'), true);
            $ids = $jsonBody['ids'] ?? [];
            if (empty($ids) || !is_array($ids)) {
                throw new Exception("Tidak ada data yang dipilih");
            }

            $model = new FinanceModel();
            $deletedCount = 0;
            foreach ($ids as $id) {
                $log = $model->findLog((int)$id);
                if ($log) {
                    $model->deleteLog((int)$id);
                    $deletedCount++;
                }
            }

            $this->json([
                'success' => true,
                'message' => "$deletedCount catatan keuangan berhasil dihapus"
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }


    // ==========================================
    // APP SETTINGS API
    // ==========================================

    public function saveAppSettings()
    {
        $this->validateCSRF();
        if (AuthController::currentUser()['level'] !== 'superadmin' && AuthController::currentUser()['level'] !== 'admin') {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }

        try {
            $settingModel = new SettingModel();
            $fields = ['ai_model', 'ai_api_key', 'ai_invoice_prompt', 'store_latitude', 'store_longitude', 'store_radius_meters'];
            
            foreach ($fields as $field) {
                $val = $this->input($field);
                if ($val !== null && $val !== '') {
                    $settingModel->set($field, $val);
                }
            }

            $this->json(['success' => true, 'message' => 'Pengaturan aplikasi berhasil disimpan']);
        } catch (\Exception $e) {
            $this->json(['error' => 'Gagal menyimpan pengaturan: ' . $e->getMessage()], 500);
        }
    }

    public function saveChatSettings()
    {
        $this->validateCSRF();
        if (AuthController::currentUser()['level'] !== 'superadmin' && AuthController::currentUser()['level'] !== 'admin') {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }

        try {
            $settingModel = new SettingModel();
            $fields = ['ai_chat_enabled', 'ai_chat_model', 'ai_chat_api_key', 'ai_chat_context_months', 'ai_chat_max_history'];
            
            foreach ($fields as $field) {
                $val = $this->input($field);
                if ($val !== null && $val !== '') {
                    $settingModel->set($field, $val);
                }
            }

            $this->json(['success' => true, 'message' => 'Pengaturan AI Chat berhasil disimpan']);
        } catch (\Exception $e) {
            $this->json(['error' => 'Gagal menyimpan pengaturan chat: ' . $e->getMessage()], 500);
        }
    }

    public function changePassword()
    {
        $this->validateCSRF();
        $user = AuthController::currentUser();
        if (!$user) {
            $this->json(['error' => 'Belum login'], 401);
            return;
        }

        try {
            $oldPassword = $this->input('old_password');
            $newPassword = $this->input('new_password');

            if (empty($oldPassword) || empty($newPassword)) {
                throw new \Exception("Password lama dan baru wajib diisi");
            }

            $userModel = new UserModel();
            $dbUser = $userModel->find($user['id']); // get fresh hash
            if (!$dbUser || !password_verify($oldPassword, $dbUser['password_hash'])) {
                throw new \Exception("Password lama tidak sesuai");
            }

            $userModel->changePassword($user['id'], $newPassword);

            $this->json(['success' => true, 'message' => 'Password berhasil diubah']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    // ==========================================
    // AI INTEGRATION API
    // ==========================================



    public function scanInvoiceAI()
    {
        $this->validateCSRF();
        set_time_limit(120); // Prevent timeout for long AI requests
        
        // Custom shutdown handler to catch silent crashes (OOM, Timeouts)
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING, E_PARSE])) {
                file_put_contents(__DIR__ . '/../../logs/ai_crash.log', date('Y-m-d H:i:s') . " FATAL CRASH: " . print_r($error, true) . "\n", FILE_APPEND);
            }
        });
        
        ob_start(); // Capture any stray output/warnings to prevent breaking JSON
        try {
            error_log("SCAN_AI_TRACE: Starting scanInvoiceAI with InvoiceScanService");
            file_put_contents(__DIR__ . '/../../logs/ai_crash.log', date('Y-m-d H:i:s') . " Trace: Starting scanInvoiceAI\n", FILE_APPEND);
            
            // Read image_base64 directly from raw JSON to bypass Security::sanitize()
            // which calls strip_tags() and could corrupt base64 data.
            $rawInput = file_get_contents('php://input');
            $rawJson = json_decode($rawInput, true);
            if (!is_array($rawJson)) {
                $rawJson = [];
            }
            $imageB64 = $rawJson['image_base64'] ?? '';
            if (empty($imageB64)) {
                throw new \Exception("Gambar invoice tidak ditemukan");
            }

            // Optional supplier context
            $supplierId = isset($rawJson['supplier_id']) ? (int)$rawJson['supplier_id'] : null;

            // Load and run the new service
            require_once __DIR__ . '/../services/invoice/ImagePreprocessor.php';
            require_once __DIR__ . '/../services/invoice/PromptBuilder.php';
            require_once __DIR__ . '/../services/invoice/LayoutAnalyzer.php';
            require_once __DIR__ . '/../services/invoice/TableParser.php';
            require_once __DIR__ . '/../services/invoice/InvoiceValidator.php';
            require_once __DIR__ . '/../services/invoice/ProductMatcher.php';
            require_once __DIR__ . '/../services/invoice/ConfidenceScorer.php';
            require_once __DIR__ . '/../services/invoice/SelfCorrectionEngine.php';
            require_once __DIR__ . '/../services/invoice/TemplateLearner.php';
            require_once __DIR__ . '/../services/invoice/InvoiceScanService.php';

            $service = new InvoiceScanService($this->db);
            $result = $service->scan($imageB64, $supplierId);

            ob_end_clean(); // Discard any stray output before sending JSON

            if ($result['success']) {
                $this->json([
                    'success'  => true,
                    'message'  => $result['message'],
                    'data'     => $result['data'],
                    'metadata' => $result['metadata']
                ]);
            } else {
                $this->json(['error' => $result['message']], 500);
            }

        } catch (\Exception $e) {
            error_log("SCAN_AI_TRACE: Exception caught: " . $e->getMessage());
            ob_end_clean(); // Discard any stray output before sending JSON
            $this->json(['error' => $e->getMessage()], 500);
        } catch (\Error $err) {
            error_log("SCAN_AI_TRACE: Fatal Error caught: " . $err->getMessage());
            ob_end_clean();
            $this->json(['error' => 'Fatal internal error: ' . $err->getMessage()], 500);
        }
    }

    // ===== DEBT SOURCES =====
    public function getDebtSources()
    {
        $model = new DebtModel();
        $this->json($model->getDebtSources());
    }

    public function createDebtSource()
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama sumber hutang wajib diisi');
            $model = new DebtModel();
            $id = $model->createDebtSource($name);
            $this->json(['success' => true, 'id' => $id, 'name' => $name, 'message' => 'Sumber hutang berhasil ditambahkan']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateDebtSource(int $id)
    {
        $this->validateCSRF();
        try {
            $name = $this->input('name');
            if (empty($name)) throw new Exception('Nama sumber hutang wajib diisi');
            $model = new DebtModel();
            $model->updateDebtSource($id, $name);
            $this->json(['success' => true, 'message' => 'Sumber hutang berhasil diupdate']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteDebtSource(int $id)
    {
        $this->validateCSRF();
        try {
            $model = new DebtModel();
            $model->deleteDebtSource($id);
            $this->json(['success' => true, 'message' => 'Sumber hutang berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // ===== ORDER ESTIMATES (Hitung Orderan) =====
    public function getOrderEstimates()
    {
        require_once __DIR__ . '/../models/OrderEstimateModel.php';
        $model = new OrderEstimateModel();
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $this->json(['success' => true, 'data' => $model->getAll($limit)]);
    }

    public function getOrderEstimateDetails(int $id)
    {
        require_once __DIR__ . '/../models/OrderEstimateModel.php';
        $model = new OrderEstimateModel();
        $estimate = $model->getDetails($id);
        if (!$estimate) {
            $this->json(['success' => false, 'error' => 'Draft tidak ditemukan'], 404);
            return;
        }
        $this->json(['success' => true, 'data' => $estimate]);
    }

    public function saveOrderEstimate()
    {
        $this->validateCSRF();
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) throw new Exception('Invalid payload');
            if (empty($data['title'])) throw new Exception('Judul draft wajib diisi');
            if (empty($data['items']) || !is_array($data['items'])) throw new Exception('Item kosong');

            require_once __DIR__ . '/../models/OrderEstimateModel.php';
            $model = new OrderEstimateModel();

            $id = null;
            if (!empty($data['id'])) {
                $id = (int)$data['id'];
                $model->updateWithItems($id, $data, $data['items']);
            } else {
                $id = $model->createWithItems($data, $data['items']);
            }

            $this->json(['success' => true, 'id' => $id, 'message' => 'Draft berhasil disimpan']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteOrderEstimate(int $id)
    {
        $this->validateCSRF();
        try {
            require_once __DIR__ . '/../models/OrderEstimateModel.php';
            $model = new OrderEstimateModel();
            $model->delete($id);
            $this->json(['success' => true, 'message' => 'Draft berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/activity/log
     * Called from frontend on every page load to record user activity.
     */
    public function logActivity()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthenticated'], 401);
            return;
        }
        try {
            $db = Database::getInstance()->getConnection();
            $userId   = (int)$_SESSION['user_id'];
            $pageUrl  = $this->input('page_url') ?? '';
            $pageTitle = $this->input('page_title') ?? '';
            $lat      = $this->input('lat');
            $lng      = $this->input('lng');
            $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $ua       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512);
            $sessId   = substr(session_id(), 0, 128);

            if (empty($pageUrl)) {
                $this->json(['error' => 'page_url required'], 400);
                return;
            }

            $stmt = $db->prepare("
                INSERT INTO user_activity_logs
                    (user_id, page_url, page_title, action_type, lat, lng, ip, user_agent, session_id, created_at)
                VALUES
                    (:user_id, :page_url, :page_title, 'page_view', :lat, :lng, :ip, :ua, :sess, NOW())
            ");
            $stmt->execute([
                ':user_id'    => $userId,
                ':page_url'   => substr($pageUrl, 0, 512),
                ':page_title' => substr($pageTitle, 0, 255),
                ':lat'        => ($lat !== null && $lat !== '') ? (float)$lat : null,
                ':lng'        => ($lng !== null && $lng !== '') ? (float)$lng : null,
                ':ip'         => $ip,
                ':ua'         => $ua,
                ':sess'       => $sessId,
            ]);

            $this->json(['success' => true]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/users/{id}/activity
     * Fetch activity logs for a specific user (superadmin only).
     */
    public function getUserActivity(int $id)
    {
        $level = $_SESSION['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $db = Database::getInstance()->getConnection();

            // Last 50 logs for today + yesterday
            $stmt = $db->prepare("
                SELECT id, page_url, page_title, action_type, lat, lng, ip, created_at
                FROM user_activity_logs
                WHERE user_id = :uid
                AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
                ORDER BY created_at DESC
                LIMIT 100
            ");
            $stmt->execute([':uid' => (int)$id]);
            $logs = $stmt->fetchAll();

            // Last seen
            $stmtLast = $db->prepare("
                SELECT created_at, page_url, page_title, lat, lng
                FROM user_activity_logs
                WHERE user_id = :uid
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmtLast->execute([':uid' => (int)$id]);
            $lastSeen = $stmtLast->fetch();

            // Online check: seen in last 3 minutes?
            $isOnline = false;
            if ($lastSeen) {
                $diff = time() - strtotime($lastSeen['created_at']);
                $isOnline = ($diff < 180);
            }

            $this->json([
                'success'   => true,
                'is_online' => $isOnline,
                'last_seen' => $lastSeen ?: null,
                'logs'      => $logs,
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/users/activity/all
     * Get latest activity snapshot for all users (superadmin only).
     */
    public function getAllUsersActivity()
    {
        $level = $_SESSION['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->json(['error' => 'Akses ditolak'], 403);
            return;
        }
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT u.id, u.name, u.user_level,
                    l.page_url, l.page_title, l.lat, l.lng, l.created_at,
                    TIMESTAMPDIFF(SECOND, l.created_at, NOW()) AS seconds_ago
                FROM users u
                LEFT JOIN user_activity_logs l ON l.id = (
                    SELECT id FROM user_activity_logs
                    WHERE user_id = u.id
                    ORDER BY created_at DESC LIMIT 1
                )
                WHERE u.is_active = 1
                ORDER BY l.created_at DESC
            ");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $this->json(['success' => true, 'users' => $rows]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}

