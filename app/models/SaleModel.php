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
                INSERT INTO sale_items (transaction_id, product_id, packaging_id, quantity, unit_price, total_price, profit)
                VALUES (:tid, :prod_id, :pkg_id, :qty, :price, :total, :profit)
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
                    ':profit' => $profit
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

            // Insert Finance Log if it's Lunas
            if (($data['payment_status'] ?? 'Lunas') === 'Lunas') {
                $stmtFinance = $this->db->prepare("
                    INSERT INTO finance_logs (log_date, period_yyyymm, amount, balance_type, category, detail, description, reference_type, reference_id)
                    VALUES (CURRENT_DATE, :period, :amount, 'Saldo Utama', 'Pemasukan', 'Omzet Toko', :desc, 'sale', :ref)
                ");
                $stmtFinance->execute([
                    ':period' => date('Ym'),
                    ':amount' => $data['total_amount'],
                    ':desc' => 'Pembayaran ' . $data['invoice_number'],
                    ':ref' => $transactionId
                ]);
            }

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
                   (SELECT SUM(quantity) FROM sale_items WHERE transaction_id = t.id) as total_items
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
            SELECT si.*, p.full_name, p.short_label, p.invoice_name,
                   pp.level, u.name as unit_name
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
            'gross_profit' => $itemsResult['total_profit'] ?? 0
        ];
    }
}
