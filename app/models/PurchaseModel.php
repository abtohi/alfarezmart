<?php
class PurchaseModel extends Model
{
    protected $table = 'purchases';

    public function createWithDetails(array $data, array $items)
    {
        try {
            $this->db->beginTransaction();

            // 1. Create Purchase Header (now includes sales_rep_id and invoice_photo)
            $stmt = $this->db->prepare("
                INSERT INTO purchases (purchase_code, supplier_id, sales_rep_id, purchase_date, total_amount, grand_total, total_items, invoice_photo, notes)
                VALUES (:code, :supplier, :sales_rep, :date, :total, :grand, :items, :photo, :notes)
            ");
            $stmt->execute([
                ':code' => $data['purchase_code'],
                ':supplier' => $data['supplier_id'],
                ':sales_rep' => $data['sales_rep_id'] ?? null,
                ':date' => $data['purchase_date'],
                ':total' => $data['total_amount'],
                ':grand' => $data['grand_total'],
                ':items' => count($items),
                ':photo' => $data['invoice_photo'] ?? null,
                ':notes' => $data['notes'] ?? ''
            ]);
            $purchaseId = $this->db->lastInsertId();

            // 2. Insert Items & Update Stock & Prices
            $stmtItem = $this->db->prepare("
                INSERT INTO purchase_items (purchase_id, product_id, packaging_id, quantity, buy_price, ppn_percent, discount_percent, discount_amount, nett_price, total_price, sell_price_retail, sell_price_wholesale)
                VALUES (:pid, :prod_id, :pkg_id, :qty, :buy, :ppn, :disc_pct, :disc_amt, :nett, :total, :retail, :wholesale)
            ");

            $stmtUpdatePrice = $this->db->prepare("
                UPDATE product_packagings 
                SET buy_price = :buy, sell_price_retail = :retail, sell_price_wholesale = :wholesale, margin_retail = :margin_r, margin_wholesale = :margin_w
                WHERE id = :pkg_id
            ");

            $stmtCheckStock = $this->db->prepare("SELECT id, current_qty_base FROM stock WHERE product_id = :id");
            
            $stmtInsertStock = $this->db->prepare("
                INSERT INTO stock (product_id, current_qty_base, last_restock_date, last_restock_qty) 
                VALUES (:id, :qty, CURRENT_DATE, :qty)
            ");
            
            $stmtUpdateStock = $this->db->prepare("
                UPDATE stock 
                SET current_qty_base = current_qty_base + :qty, last_restock_date = CURRENT_DATE, last_restock_qty = :restock
                WHERE product_id = :id
            ");

            $stmtMovement = $this->db->prepare("
                INSERT INTO stock_movements (product_id, movement_type, quantity, reference_type, reference_id, notes)
                VALUES (:prod_id, 'in', :qty, 'purchase', :ref, :notes)
            ");

            $stmtUpdateProductTimestamp = $this->db->prepare("
                UPDATE products SET updated_at = CURRENT_TIMESTAMP WHERE id = :id
            ");

            $productModel = new ProductModel();
            $spModel = new SupplierProductModel();

            foreach ($items as $item) {
                $packagings = $productModel->getPackagings($item['product_id']);
                
                // Find matching packaging by level
                $pkg = null;
                $multiplier = 1;
                foreach ($packagings as $p) {
                    if ($p['level'] == $item['level']) {
                        $pkg = $p;
                        $multiplier = $p['base_qty'];
                        break;
                    }
                }

                if (!$pkg) throw new Exception("Kemasan level {$item['level']} tidak ditemukan untuk produk {$item['product_id']}");
                
                $pkgId = $pkg['id'];

                // Calculate PPN and discount
                $ppn_pct = isset($item['ppn_pct']) ? (float)$item['ppn_pct'] : 0.0;
                $disc_pct = 0.0;
                $disc_amt = 0.0;
                
                if (isset($item['diskon_mode'])) {
                    if ($item['diskon_mode'] === 'pct') {
                        $disc_pct = (float)($item['diskon_value'] ?? 0);
                        $disc_amt = round(($item['buy_price'] * $disc_pct / 100), 2);
                    } else {
                        $disc_amt = (float)($item['diskon_value'] ?? 0);
                        if ($item['buy_price'] > 0) {
                            $disc_pct = round(($disc_amt / $item['buy_price'] * 100), 2);
                        }
                    }
                }
                
                $ppn_amt = $item['buy_price'] * ($ppn_pct / 100);
                $nett_price = max(0, $item['buy_price'] + $ppn_amt - $disc_amt);
                $total_price = $item['quantity'] * $nett_price;

                // Insert purchase item
                $stmtItem->execute([
                    ':pid' => $purchaseId,
                    ':prod_id' => $item['product_id'],
                    ':pkg_id' => $pkgId,
                    ':qty' => $item['quantity'],
                    ':buy' => $item['buy_price'],
                    ':ppn' => $ppn_pct,
                    ':disc_pct' => $disc_pct,
                    ':disc_amt' => $disc_amt,
                    ':nett' => $nett_price,
                    ':total' => $total_price,
                    ':retail' => $item['sell_price_retail'],
                    ':wholesale' => $item['sell_price_wholesale']
                ]);

                if (isset($item['packagings']) && is_array($item['packagings'])) {
                    foreach ($item['packagings'] as $pUpdate) {
                        // Match with existing packagings from DB to get ID
                        foreach ($packagings as $pDb) {
                            if ($pDb['level'] == $pUpdate['level']) {
                                $m_r = $pUpdate['sell_price_retail'] > 0 ? Helper::calculateMargin($pUpdate['buy_price'], $pUpdate['sell_price_retail']) : 0;
                                $m_w = $pUpdate['sell_price_wholesale'] > 0 ? Helper::calculateMargin($pUpdate['buy_price'], $pUpdate['sell_price_wholesale']) : 0;
                                $stmtUpdatePrice->execute([
                                    ':buy' => $pUpdate['buy_price'],
                                    ':retail' => $pUpdate['sell_price_retail'],
                                    ':wholesale' => $pUpdate['sell_price_wholesale'],
                                    ':margin_r' => $m_r,
                                    ':margin_w' => $m_w,
                                    ':pkg_id' => $pDb['id']
                                ]);

                                // Save tier prices if provided
                                if (isset($pUpdate['qty_prices'])) {
                                    $productModel->saveQtyPricesForPackaging($pDb['id'], $pUpdate['qty_prices']);
                                }
                            }
                        }
                    }
                } else {
                    // Fallback to old behavior
                    $margin_r = $item['sell_price_retail'] > 0 ? Helper::calculateMargin($item['buy_price'], $item['sell_price_retail']) : 0;
                    $margin_w = $item['sell_price_wholesale'] > 0 ? Helper::calculateMargin($item['buy_price'], $item['sell_price_wholesale']) : 0;
                    
                    $stmtUpdatePrice->execute([
                        ':buy' => $item['buy_price'],
                        ':retail' => $item['sell_price_retail'],
                        ':wholesale' => $item['sell_price_wholesale'],
                        ':margin_r' => $margin_r,
                        ':margin_w' => $margin_w,
                        ':pkg_id' => $pkgId
                    ]);
                }

                $baseQtyAdded = $item['quantity'] * $multiplier;

                // Update stock
                $stmtCheckStock->execute([':id' => $item['product_id']]);
                $stockRow = $stmtCheckStock->fetch();
                
                if ($stockRow) {
                    $stmtUpdateStock->execute([
                        ':qty' => $baseQtyAdded,
                        ':restock' => $baseQtyAdded,
                        ':id' => $item['product_id']
                    ]);
                } else {
                    $stmtInsertStock->execute([
                        ':id' => $item['product_id'],
                        ':qty' => $baseQtyAdded
                    ]);
                }

                // Insert movement log
                $stmtMovement->execute([
                    ':prod_id' => $item['product_id'],
                    ':qty' => $baseQtyAdded,
                    ':ref' => $purchaseId,
                    ':notes' => "Pembelian " . $data['purchase_code']
                ]);

                // 2.5 Update Product timestamp so it rises to the top of product list
                $stmtUpdateProductTimestamp->execute([':id' => $item['product_id']]);

                // 3. Auto-track supplier-product relationship (skip if supplier unknown)
                if (!empty($data['supplier_id'])) {
                    $spModel->trackSupplierProduct(
                        $data['supplier_id'],
                        $item['product_id'],
                        $data['sales_rep_id'] ?? null,
                        $item['buy_price']
                    );
                }
            }

            $this->db->commit();
            return $purchaseId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getDetails(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, s.name as supplier_name, sr.name as sales_rep_name
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN sales_reps sr ON p.sales_rep_id = sr.id
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $purchase = $stmt->fetch();

        if (!$purchase) return null;

        $stmtItems = $this->db->prepare("
            SELECT pi.*, pr.full_name as product_name, pr.short_label,
                   u.name as unit_name, pkg.level as level
            FROM purchase_items pi
            JOIN products pr ON pi.product_id = pr.id
            JOIN product_packagings pkg ON pi.packaging_id = pkg.id
            JOIN units u ON pkg.unit_id = u.id
            WHERE pi.purchase_id = :id
            ORDER BY pi.id ASC
        ");
        $stmtItems->execute([':id' => $id]);
        $purchase['items'] = $stmtItems->fetchAll();

        return $purchase;
    }

    public function getList($page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM purchases");
        $total = $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT p.*, s.name as supplier_name 
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            ORDER BY p.purchase_date DESC, p.id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Kelompokkan daftar pembelian per tanggal (terbaru dulu), lalu per supplier.
     *
     * @param array $purchases Baris dari getList()['data']
     * @return array<int, array{date:string,date_label:string,total:int,grand_total:float,suppliers:array}>
     */
    public function groupByDateAndSupplier(array $purchases): array
    {
        $byDate = [];
        foreach ($purchases as $p) {
            $dateKey = $p['purchase_date'] ?? '';
            if ($dateKey === '') {
                continue;
            }
            $supplierId = (int)($p['supplier_id'] ?? 0);
            $supplierName = trim($p['supplier_name'] ?? '') ?: 'Tanpa Supplier';

            if (!isset($byDate[$dateKey])) {
                $byDate[$dateKey] = [
                    'date' => $dateKey,
                    'date_label' => Helper::formatDate($dateKey, 'd M Y'),
                    'total' => 0,
                    'grand_total' => 0.0,
                    'suppliers' => [],
                ];
            }

            $supKey = $supplierId . '|' . mb_strtolower($supplierName);
            if (!isset($byDate[$dateKey]['suppliers'][$supKey])) {
                $byDate[$dateKey]['suppliers'][$supKey] = [
                    'supplier_id' => $supplierId,
                    'supplier_name' => $supplierName,
                    'total' => 0,
                    'grand_total' => 0.0,
                    'purchases' => [],
                ];
            }

            $byDate[$dateKey]['suppliers'][$supKey]['purchases'][] = $p;
            $byDate[$dateKey]['suppliers'][$supKey]['total']++;
            $byDate[$dateKey]['suppliers'][$supKey]['grand_total'] += (float)($p['grand_total'] ?? 0);
            $byDate[$dateKey]['total']++;
            $byDate[$dateKey]['grand_total'] += (float)($p['grand_total'] ?? 0);
        }

        $result = array_values($byDate);
        foreach ($result as &$day) {
            $day['suppliers'] = array_values($day['suppliers']);
        }
        unset($day);

        return $result;
    }

    public function getDailyStats(string $date)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(id) as purchase_count,
                SUM(grand_total) as total_expense
            FROM purchases
            WHERE purchase_date = :date
        ");
        $stmt->execute([':date' => $date]);
        $result = $stmt->fetch();
        
        return [
            'transactions' => $result['purchase_count'] ?? 0,
            'expense' => $result['total_expense'] ?? 0
        ];
    }

    public function deleteWithRevert(int $id)
    {
        try {
            $this->beginTransaction();

            // 1. Get purchase items
            $stmt = $this->db->prepare("SELECT * FROM purchase_items WHERE purchase_id = :id");
            $stmt->execute([':id' => $id]);
            $items = $stmt->fetchAll();

            // 2. Revert stock and add movement log
            $stmtStock = $this->db->prepare("
                UPDATE stock 
                SET current_qty_base = current_qty_base - :qty 
                WHERE product_id = :id
            ");
            
            $stmtMovement = $this->db->prepare("
                INSERT INTO stock_movements (product_id, movement_type, quantity, reference_type, reference_id, notes)
                VALUES (:prod_id, 'out', :qty, 'purchase_deleted', :ref, 'Pembelian Dihapus')
            ");

            foreach ($items as $item) {
                $pkgStmt = $this->db->prepare("SELECT base_qty FROM product_packagings WHERE id = :pid");
                $pkgStmt->execute([':pid' => $item['packaging_id']]);
                $pkg = $pkgStmt->fetch();
                $baseQty = $pkg ? $pkg['base_qty'] : 1;
                
                $totalBaseQtyRevert = $item['quantity'] * $baseQty;

                $stmtStock->execute([
                    ':qty' => $totalBaseQtyRevert,
                    ':id' => $item['product_id']
                ]);

                $stmtMovement->execute([
                    ':prod_id' => $item['product_id'],
                    ':qty' => $totalBaseQtyRevert,
                    ':ref' => $id
                ]);
            }

            // 3. Delete items
            $stmtDeleteItems = $this->db->prepare("DELETE FROM purchase_items WHERE purchase_id = :id");
            $stmtDeleteItems->execute([':id' => $id]);

            // 4. Delete purchase record
            $stmtDeletePurchase = $this->db->prepare("DELETE FROM purchases WHERE id = :id");
            $stmtDeletePurchase->execute([':id' => $id]);

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function updateWithDetails(int $id, array $data, array $items)
    {
        try {
            $this->beginTransaction();

            // 1. Get OLD purchase items & revert stock
            $stmt = $this->db->prepare("SELECT * FROM purchase_items WHERE purchase_id = :id");
            $stmt->execute([':id' => $id]);
            $oldItems = $stmt->fetchAll();

            $stmtStockOut = $this->db->prepare("
                UPDATE stock 
                SET current_qty_base = current_qty_base - :qty 
                WHERE product_id = :id
            ");
            
            $stmtMovementOut = $this->db->prepare("
                INSERT INTO stock_movements (product_id, movement_type, quantity, reference_type, reference_id, notes)
                VALUES (:prod_id, 'out', :qty, 'purchase_edited_out', :ref, 'Revisi Stok - Edit Pembelian')
            ");

            foreach ($oldItems as $item) {
                $pkgStmt = $this->db->prepare("SELECT base_qty FROM product_packagings WHERE id = :pid");
                $pkgStmt->execute([':pid' => $item['packaging_id']]);
                $pkg = $pkgStmt->fetch();
                $baseQty = $pkg ? $pkg['base_qty'] : 1;
                
                $totalBaseQtyRevert = $item['quantity'] * $baseQty;

                $stmtStockOut->execute([
                    ':qty' => $totalBaseQtyRevert,
                    ':id' => $item['product_id']
                ]);

                $stmtMovementOut->execute([
                    ':prod_id' => $item['product_id'],
                    ':qty' => $totalBaseQtyRevert,
                    ':ref' => $id
                ]);
            }

            // 2. Delete OLD items
            $stmtDeleteItems = $this->db->prepare("DELETE FROM purchase_items WHERE purchase_id = :id");
            $stmtDeleteItems->execute([':id' => $id]);

            // 3. Update Purchase Header
            $stmtUpdateHeader = $this->db->prepare("
                UPDATE purchases 
                SET supplier_id = :supplier, sales_rep_id = :sales_rep, purchase_date = :date, 
                    total_amount = :total, grand_total = :grand, total_items = :items, 
                    invoice_photo = COALESCE(:photo, invoice_photo), notes = :notes
                WHERE id = :id
            ");
            $stmtUpdateHeader->execute([
                ':id' => $id,
                ':supplier' => $data['supplier_id'],
                ':sales_rep' => $data['sales_rep_id'] ?? null,
                ':date' => $data['purchase_date'],
                ':total' => $data['total_amount'],
                ':grand' => $data['grand_total'],
                ':items' => count($items),
                ':photo' => $data['invoice_photo'] ?? null,
                ':notes' => $data['notes'] ?? ''
            ]);

            // 4. Insert NEW Items & Update Stock & Prices
            $stmtItem = $this->db->prepare("
                INSERT INTO purchase_items (purchase_id, product_id, packaging_id, quantity, buy_price, ppn_percent, discount_percent, discount_amount, nett_price, total_price, sell_price_retail, sell_price_wholesale)
                VALUES (:pid, :prod_id, :pkg_id, :qty, :buy, :ppn, :disc_pct, :disc_amt, :nett, :total, :retail, :wholesale)
            ");

            $stmtUpdatePrice = $this->db->prepare("
                UPDATE product_packagings 
                SET buy_price = :buy, sell_price_retail = :retail, sell_price_wholesale = :wholesale, margin_retail = :margin_r, margin_wholesale = :margin_w
                WHERE id = :pkg_id
            ");

            $stmtCheckStock = $this->db->prepare("SELECT id, current_qty_base FROM stock WHERE product_id = :id");
            
            $stmtInsertStock = $this->db->prepare("
                INSERT INTO stock (product_id, current_qty_base, last_restock_date, last_restock_qty) 
                VALUES (:id, :qty, CURRENT_DATE, :qty)
            ");
            
            $stmtUpdateStock = $this->db->prepare("
                UPDATE stock 
                SET current_qty_base = current_qty_base + :qty, last_restock_date = CURRENT_DATE, last_restock_qty = :restock
                WHERE product_id = :id
            ");

            $stmtMovementIn = $this->db->prepare("
                INSERT INTO stock_movements (product_id, movement_type, quantity, reference_type, reference_id, notes)
                VALUES (:prod_id, 'in', :qty, 'purchase', :ref, :notes)
            ");

            $stmtUpdateProductTimestamp = $this->db->prepare("
                UPDATE products SET updated_at = CURRENT_TIMESTAMP WHERE id = :id
            ");

            $productModel = new ProductModel();
            $spModel = new SupplierProductModel();

            foreach ($items as $item) {
                $packagings = $productModel->getPackagings($item['product_id']);
                
                $pkg = null;
                $multiplier = 1;
                foreach ($packagings as $p) {
                    if ($p['level'] == $item['level']) {
                        $pkg = $p;
                        $multiplier = $p['base_qty'];
                        break;
                    }
                }

                if (!$pkg) throw new Exception("Kemasan level {$item['level']} tidak ditemukan untuk produk {$item['product_id']}");

                $totalQtyBase = $item['quantity'] * $multiplier;

                // Hitung margin
                $marginR = $item['sell_price_retail'] > 0 ? (($item['sell_price_retail'] - $item['buy_price']) / $item['sell_price_retail'] * 100) : 0;
                $marginW = $item['sell_price_wholesale'] > 0 ? (($item['sell_price_wholesale'] - $item['buy_price']) / $item['sell_price_wholesale'] * 100) : 0;

                // PPN & Discount
                $ppn = $item['ppn_pct'] ?? 0;
                $discMode = $item['diskon_mode'] ?? 'rp';
                $discVal = $item['diskon_value'] ?? 0;

                $discAmt = 0;
                $discPct = 0;
                if ($discMode === 'pct') {
                    $discPct = $discVal;
                    $discAmt = $item['buy_price'] * ($discPct / 100);
                } else {
                    $discAmt = $discVal;
                    $discPct = $item['buy_price'] > 0 ? ($discAmt / $item['buy_price'] * 100) : 0;
                }

                $nettPrice = $item['buy_price'] + ($item['buy_price'] * ($ppn / 100)) - $discAmt;
                $totalPriceItem = $nettPrice * $item['quantity'];

                $stmtItem->execute([
                    ':pid' => $id,
                    ':prod_id' => $item['product_id'],
                    ':pkg_id' => $pkg['id'],
                    ':qty' => $item['quantity'],
                    ':buy' => $item['buy_price'],
                    ':ppn' => $ppn,
                    ':disc_pct' => $discPct,
                    ':disc_amt' => $discAmt,
                    ':nett' => $nettPrice,
                    ':total' => $totalPriceItem,
                    ':retail' => $item['sell_price_retail'],
                    ':wholesale' => $item['sell_price_wholesale']
                ]);

                if (isset($item['packagings']) && is_array($item['packagings'])) {
                    foreach ($item['packagings'] as $pUpdate) {
                        foreach ($packagings as $pDb) {
                            if ($pDb['level'] == $pUpdate['level']) {
                                $m_r = $pUpdate['sell_price_retail'] > 0 ? Helper::calculateMargin($pUpdate['buy_price'], $pUpdate['sell_price_retail']) : 0;
                                $m_w = $pUpdate['sell_price_wholesale'] > 0 ? Helper::calculateMargin($pUpdate['buy_price'], $pUpdate['sell_price_wholesale']) : 0;
                                $stmtUpdatePrice->execute([
                                    ':buy' => $pUpdate['buy_price'],
                                    ':retail' => $pUpdate['sell_price_retail'],
                                    ':wholesale' => $pUpdate['sell_price_wholesale'],
                                    ':margin_r' => $m_r,
                                    ':margin_w' => $m_w,
                                    ':pkg_id' => $pDb['id']
                                ]);

                                if (isset($pUpdate['qty_prices'])) {
                                    $productModel->saveQtyPricesForPackaging($pDb['id'], $pUpdate['qty_prices']);
                                }
                            }
                        }
                    }
                } else {
                    $stmtUpdatePrice->execute([
                        ':buy' => $item['buy_price'],
                        ':retail' => $item['sell_price_retail'],
                        ':wholesale' => $item['sell_price_wholesale'],
                        ':margin_r' => $marginR,
                        ':margin_w' => $marginW,
                        ':pkg_id' => $pkg['id']
                    ]);
                }

                $stmtCheckStock->execute([':id' => $item['product_id']]);
                $stock = $stmtCheckStock->fetch();

                if ($stock) {
                    $stmtUpdateStock->execute([
                        ':qty' => $totalQtyBase,
                        ':restock' => $totalQtyBase,
                        ':id' => $item['product_id']
                    ]);
                } else {
                    $stmtInsertStock->execute([
                        ':id' => $item['product_id'],
                        ':qty' => $totalQtyBase
                    ]);
                }

                $stmtMovementIn->execute([
                    ':prod_id' => $item['product_id'],
                    ':qty' => $totalQtyBase,
                    ':ref' => $id,
                    ':notes' => 'Pembelian Direvisi: ' . $data['purchase_code']
                ]);

                // Update Product timestamp so it rises to the top of product list
                $stmtUpdateProductTimestamp->execute([':id' => $item['product_id']]);

                if (!empty($data['supplier_id'])) {
                    $spModel->trackSupplierProduct($data['supplier_id'], $item['product_id']);
                }
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function getProductPurchaseHistory(int $productId)
    {
        $stmt = $this->db->prepare("
            SELECT pi.*, p.purchase_date, p.purchase_code, s.name as supplier_name,
                   pkg.level, u.name as unit_name
            FROM purchase_items pi
            JOIN purchases p ON pi.purchase_id = p.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            JOIN product_packagings pkg ON pi.packaging_id = pkg.id
            JOIN units u ON pkg.unit_id = u.id
            WHERE pi.product_id = :pid
            ORDER BY p.purchase_date DESC, p.id DESC
        ");
        $stmt->execute([':pid' => $productId]);
        return $stmt->fetchAll();
    }

    public function getProductSupplierComparison(int $productId)
    {
        // Get all purchase records for this product grouped by supplier
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(s.id, 0) as supplier_id, 
                COALESCE(s.name, 'Supplier Dihapus') as supplier_name,
                MAX(p.purchase_date) as last_purchase_date,
                AVG(pi.buy_price / pkg.base_qty) as avg_price,
                MIN(pi.buy_price / pkg.base_qty) as min_price,
                MAX(pi.buy_price / pkg.base_qty) as max_price
            FROM purchase_items pi
            JOIN purchases p ON pi.purchase_id = p.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            JOIN product_packagings pkg ON pi.packaging_id = pkg.id
            WHERE pi.product_id = :pid
            GROUP BY COALESCE(s.id, 0), COALESCE(s.name, 'Supplier Dihapus')
            ORDER BY avg_price ASC
        ");
        $stmt->execute([':pid' => $productId]);
        $results = $stmt->fetchAll();
        
        // Get latest price for each supplier separately to avoid subquery issues
        foreach ($results as &$row) {
            $latestStmt = $this->db->prepare("
                SELECT (pi2.buy_price / pkg2.base_qty) as latest_price
                FROM purchase_items pi2
                JOIN purchases p2 ON pi2.purchase_id = p2.id
                JOIN product_packagings pkg2 ON pi2.packaging_id = pkg2.id
                WHERE pi2.product_id = :pid AND p2.supplier_id = :sid
                ORDER BY p2.purchase_date DESC, p2.id DESC
                LIMIT 1
            ");
            $latestStmt->execute([':pid' => $productId, ':sid' => $row['supplier_id']]);
            $latest = $latestStmt->fetchColumn();
            $row['latest_price'] = $latest ? floatval($latest) : $row['avg_price'];
        }
        
        return $results;
    }
}
