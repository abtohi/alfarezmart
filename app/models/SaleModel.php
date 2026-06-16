<?php
class SaleModel extends Model
{
    protected $table = 'sale_transactions';

    public function createWithDetails(array $data, array $items)
    {
        try {
            $this->db->beginTransaction();

            // 1. Create Sale Header
            $stmt = $this->db->prepare("
                INSERT INTO sale_transactions (invoice_number, customer_id, sale_mode, total_amount, payment_method, payment_status, notes)
                VALUES (:inv, :cust, :mode, :total, :pay_method, :status, :notes)
            ");
            $stmt->execute([
                ':inv' => $data['invoice_number'],
                ':cust' => $data['customer_id'] ?? null,
                ':mode' => $data['sale_mode'], // 'retail' or 'wholesale'
                ':total' => $data['total_amount'],
                ':pay_method' => $data['payment_method'] ?? 'Cash',
                ':status' => $data['payment_status'] ?? 'Lunas',
                ':notes' => $data['notes'] ?? ''
            ]);
            $transactionId = $this->db->lastInsertId();

            // 2. Insert Items & Update Stock
            $stmtItem = $this->db->prepare("
                INSERT INTO sale_items (transaction_id, product_id, packaging_id, quantity, unit_price, total_price, profit, custom_name, custom_unit)
                VALUES (:tid, :prod_id, :pkg_id, :qty, :price, :total, :profit, :custom_name, :custom_unit)
            ");

            $stmtUpdateStock = $this->db->prepare("
                UPDATE stock 
                SET current_qty_base = current_qty_base - :qty
                WHERE product_id = :id
            ");

            $stmtMovement = $this->db->prepare("
                INSERT INTO stock_movements (product_id, movement_type, quantity, reference_type, reference_id, notes)
                VALUES (:prod_id, 'out', :qty, 'sale', :ref, :notes)
            ");

            foreach ($items as $item) {
                if (!empty($item['is_custom'])) {
                    // Resolve placeholder product and packaging
                    $placeholder = $this->getPlaceholderProductAndPackaging();
                    $productId = $placeholder['product_id'];
                    $pkgId = $placeholder['packaging_id'];
                    $customName = $item['custom_name'] ?? 'Barang Custom';
                    $customUnit = $item['custom_unit'] ?? 'Pcs';
                    $profit = $item['quantity'] * $item['unit_price']; // since custom item buy price is 0

                    // Insert sale item
                    $stmtItem->execute([
                        ':tid' => $transactionId,
                        ':prod_id' => $productId,
                        ':pkg_id' => $pkgId,
                        ':qty' => $item['quantity'],
                        ':price' => $item['unit_price'],
                        ':total' => $item['quantity'] * $item['unit_price'],
                        ':profit' => $profit,
                        ':custom_name' => $customName,
                        ':custom_unit' => $customUnit
                    ]);

                    // Skip stock update for custom items
                    continue;
                }

                // Query langsung — jangan pakai getPackagings() (DDL qty_prices bisa commit transaksi MySQL)
                $pkg = $this->resolvePackagingForSale(
                    (int)$item['product_id'],
                    (int)$item['level']
                );

                if (!$pkg) {
                    throw new Exception("Kemasan level {$item['level']} tidak ditemukan untuk produk {$item['product_id']}");
                }
                $multiplier = (int)$pkg['base_qty'];
                
                $pkgId = $pkg['id'];
                
                // Calculate profit
                // profit = (unit_price - buy_price) * quantity
                $buyPrice = $pkg['buy_price'];
                $profit = ($item['unit_price'] - $buyPrice) * $item['quantity'];

                // Insert sale item
                $stmtItem->execute([
                    ':tid' => $transactionId,
                    ':prod_id' => $item['product_id'],
                    ':pkg_id' => $pkgId,
                    ':qty' => $item['quantity'],
                    ':price' => $item['unit_price'],
                    ':total' => $item['quantity'] * $item['unit_price'],
                    ':profit' => $profit,
                    ':custom_name' => null,
                    ':custom_unit' => null
                ]);

                $baseQtyDeducted = $item['quantity'] * $multiplier;

                // Update stock
                $stmtUpdateStock->execute([
                    ':qty' => $baseQtyDeducted,
                    ':id' => $item['product_id']
                ]);

                // Insert movement log
                $stmtMovement->execute([
                    ':prod_id' => $item['product_id'],
                    ':qty' => -$baseQtyDeducted,
                    ':ref' => $transactionId,
                    ':notes' => "Penjualan " . $data['invoice_number']
                ]);
            }

            // [DISABLED by request] Insert Finance Log if it's Lunas
            // if (($data['payment_status'] ?? 'Lunas') === 'Lunas') {
            //     $stmtFinance = $this->db->prepare("
            //         INSERT INTO finance_logs (log_date, period_yyyymm, amount, balance_type, category, detail, description, reference_type, reference_id)
            //         VALUES (CURRENT_DATE, :period, :amount, 'Saldo Utama', 'Pemasukan', 'Omzet Toko', :desc, 'sale', :ref)
            //     ");
            //     $stmtFinance->execute([
            //         ':period' => date('Ym'),
            //         ':amount' => $data['total_amount'],
            //         ':desc' => 'Pembayaran ' . $data['invoice_number'],
            //         ':ref' => $transactionId
            //     ]);
            // }

            $this->db->commit();
            return $transactionId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Hapus transaksi yang sudah lebih dari X hari untuk menghemat storage
     */
    public function cleanupOldTransactions($days = 30)
    {
        try {
            // Because of foreign keys with CASCADE or SET NULL, we only need to delete the parent table.
            // But wait, sale_items has transaction_id, which should be CASCADE. Let's just delete sale_transactions.
            // If they are not CASCADE, we should delete sale_items first. Let's delete sale_items explicitly to be safe.
            $stmt = $this->db->prepare("
                DELETE si FROM sale_items si
                JOIN sale_transactions st ON si.transaction_id = st.id
                WHERE st.created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
            ");
            $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
            $stmt->execute();

            $stmt2 = $this->db->prepare("
                DELETE FROM sale_transactions 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
            ");
            $stmt2->bindValue(':days', (int)$days, PDO::PARAM_INT);
            $stmt2->execute();
        } catch (Exception $e) {
            error_log("Cleanup error: " . $e->getMessage());
        }
    }

    /**
     * Ambil kemasan untuk penjualan tanpa migrasi/DDL (aman di dalam transaksi).
     */
    private function resolvePackagingForSale(int $productId, int $level): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, level, base_qty, buy_price
            FROM product_packagings
            WHERE product_id = :pid AND level = :level
            LIMIT 1
        ");
        $stmt->execute([':pid' => $productId, ':level' => $level]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getList($page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM sale_transactions");
        $total = $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT t.*, c.name as customer_name,
                   (SELECT SUM(quantity) FROM sale_items WHERE transaction_id = t.id) as total_items,
                   (SELECT SUM(profit) FROM sale_items WHERE transaction_id = t.id) as total_profit
            FROM sale_transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            ORDER BY t.created_at DESC, t.id DESC
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
     * @param int|string $id
     */
    public function getTransactionDetails($id)
    {
        $stmt = $this->db->prepare("
            SELECT t.*, c.name as customer_name
            FROM sale_transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            WHERE t.id = :id
        ");
        $stmt->execute([':id' => (int)$id]);
        $transaction = $stmt->fetch();
        if (!$transaction) return null;

        $stmtItems = $this->db->prepare("
            SELECT si.*, COALESCE(si.custom_name, p.full_name) AS full_name, 
                   COALESCE(si.custom_name, p.short_label) AS short_label, 
                   COALESCE(si.custom_name, p.invoice_name) AS invoice_name,
                   pp.level, pp.buy_price, COALESCE(si.custom_unit, u.name) AS unit_name
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            LEFT JOIN product_packagings pp ON si.packaging_id = pp.id
            LEFT JOIN units u ON pp.unit_id = u.id
            WHERE si.transaction_id = :tid
            ORDER BY si.id ASC
        ");
        $stmtItems->execute([':tid' => $transaction['id']]);
        $transaction['items'] = $stmtItems->fetchAll();

        return $transaction;
    }

    private ?array $placeholderCache = null;

    private function getPlaceholderProductAndPackaging(): array
    {
        if ($this->placeholderCache !== null) {
            return $this->placeholderCache;
        }

        // Search for placeholder product
        $stmt = $this->db->prepare("SELECT id FROM products WHERE code = 'CUSTOM' LIMIT 1");
        $stmt->execute();
        $prod = $stmt->fetch();
        
        if (!$prod) {
            // Self-healing: create the product if it doesn't exist
            $stmt = $this->db->prepare("INSERT INTO products (code, full_name, short_label, invoice_name, is_active) VALUES ('CUSTOM', 'Barang Custom', 'Custom', 'Custom', 1)");
            $stmt->execute();
            $productId = $this->db->lastInsertId();

            // Create stock row
            $stmt = $this->db->prepare("INSERT INTO stock (product_id, current_qty_base) VALUES (:pid, 999999)");
            $stmt->execute([':pid' => $productId]);

            // Get Pcs unit
            $stmt = $this->db->prepare("SELECT id FROM units WHERE name = 'Pcs' OR name = 'pcs' LIMIT 1");
            $stmt->execute();
            $unit = $stmt->fetch();
            $unitId = $unit ? $unit['id'] : 1; // Fallback to 1

            // Create packaging row
            $stmt = $this->db->prepare("INSERT INTO product_packagings (product_id, level, unit_id, contained_qty, base_qty, barcode, buy_price, sell_price_retail, sell_price_wholesale) VALUES (:pid, 1, :uid, 1, 1, 'CUSTOM', 0, 0, 0)");
            $stmt->execute([':pid' => $productId, ':uid' => $unitId]);
            $packagingId = $this->db->lastInsertId();
        } else {
            $productId = $prod['id'];
            // Find the packaging
            $stmt = $this->db->prepare("SELECT id FROM product_packagings WHERE product_id = :pid AND level = 1 LIMIT 1");
            $stmt->execute([':pid' => $productId]);
            $pkg = $stmt->fetch();
            if (!$pkg) {
                // Self-healing packaging
                $stmt = $this->db->prepare("SELECT id FROM units WHERE name = 'Pcs' OR name = 'pcs' LIMIT 1");
                $stmt->execute();
                $unit = $stmt->fetch();
                $unitId = $unit ? $unit['id'] : 1;

                $stmt = $this->db->prepare("INSERT INTO product_packagings (product_id, level, unit_id, contained_qty, base_qty, barcode, buy_price, sell_price_retail, sell_price_wholesale) VALUES (:pid, 1, :uid, 1, 1, 'CUSTOM', 0, 0, 0)");
                $stmt->execute([':pid' => $productId, ':uid' => $unitId]);
                $packagingId = $this->db->lastInsertId();
            } else {
                $packagingId = $pkg['id'];
            }
        }

        $this->placeholderCache = [
            'product_id' => (int)$productId,
            'packaging_id' => (int)$packagingId
        ];

        return $this->placeholderCache;
    }

    /**
     * Hapus satu transaksi secara aman:
     * 1. Kembalikan stok produk (bukan custom) yang terjual
     * 2. Hapus stock_movements terkait
     * 3. Hapus finance_logs terkait
     * 4. Hapus sale_items & sale_transactions
     */
    public function deleteSale(int $id): void
    {
        $this->db->beginTransaction();
        try {
            // 1. Ambil detail items untuk kembalikan stok
            $stmtItems = $this->db->prepare("
                SELECT si.product_id, si.packaging_id, si.quantity, si.custom_name,
                       pp.base_qty
                FROM sale_items si
                LEFT JOIN product_packagings pp ON si.packaging_id = pp.id
                WHERE si.transaction_id = :tid
            ");
            $stmtItems->execute([':tid' => $id]);
            $items = $stmtItems->fetchAll();

            // 2. Kembalikan stok untuk setiap item (bukan custom)
            $stmtStock = $this->db->prepare("
                UPDATE stock SET current_qty_base = current_qty_base + :qty
                WHERE product_id = :pid
            ");
            foreach ($items as $item) {
                // Skip item custom (custom_name tidak null) atau produk placeholder CUSTOM
                $stmtCheck = $this->db->prepare("SELECT code FROM products WHERE id = :pid LIMIT 1");
                $stmtCheck->execute([':pid' => $item['product_id']]);
                $prodCode = $stmtCheck->fetchColumn();
                if ($item['custom_name'] !== null || $prodCode === 'CUSTOM') continue;

                $baseQty = (int)($item['base_qty'] ?: 1);
                $qty = $item['quantity'] * $baseQty;
                $stmtStock->execute([':qty' => $qty, ':pid' => $item['product_id']]);
            }

            // 3. Hapus stock_movements terkait
            $this->db->prepare("DELETE FROM stock_movements WHERE reference_type = 'sale' AND reference_id = :id")
                     ->execute([':id' => $id]);

            // 4. Hapus finance_logs terkait
            $this->db->prepare("DELETE FROM finance_logs WHERE reference_type = 'sale' AND reference_id = :id")
                     ->execute([':id' => $id]);

            // 5. Hapus sale_items dulu (foreign key), lalu sale_transactions
            $this->db->prepare("DELETE FROM sale_items WHERE transaction_id = :id")->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM sale_transactions WHERE id = :id")->execute([':id' => $id]);

            $this->db->commit();
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function findByInvoice(string $invoiceNumber)
    {
        $stmt = $this->db->prepare("
            SELECT id FROM sale_transactions WHERE invoice_number = :inv LIMIT 1
        ");
        $stmt->execute([':inv' => $invoiceNumber]);
        $row = $stmt->fetch();
        return $row ? $this->getTransactionDetails($row['id']) : null;
    }

    public function getDailyStats(string $date)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(id) as transaction_count,
                SUM(total_amount) as total_revenue
            FROM sale_transactions
            WHERE DATE(created_at) = :date
        ");
        $stmt->execute([':date' => $date]);
        $result = $stmt->fetch();
        
        $stmtItems = $this->db->prepare("
            SELECT 
                SUM(quantity) as items_sold,
                SUM(total_price) as total_items_revenue,
                SUM(profit) as total_profit
            FROM sale_items
            JOIN sale_transactions ON sale_items.transaction_id = sale_transactions.id
            WHERE DATE(sale_transactions.created_at) = :date
        ");
        $stmtItems->execute([':date' => $date]);
        $itemsResult = $stmtItems->fetch();
        
        return [
            'transactions' => $result['transaction_count'] ?? 0,
            'revenue' => $result['total_revenue'] ?? 0,
            'items_sold' => $itemsResult['items_sold'] ?? 0,
            'items_revenue' => $itemsResult['total_items_revenue'] ?? 0,
            'gross_profit' => $itemsResult['total_profit'] ?? 0
        ];
    }
}
