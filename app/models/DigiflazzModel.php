<?php
/**
 * Digiflazz Model
 * Handles database operations for Digiflazz products and transactions
 */
require_once __DIR__ . '/../config/Database.php';

class DigiflazzModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Sync Price List from Digiflazz API response
     */
    public function syncPriceList($productsData, $type = 'prepaid') {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO digi_products (
                    buyer_sku_code, product_name, category, brand, type, 
                    seller_price, buyer_product_status, seller_product_status, 
                    description, start_cut_off, end_cut_off, last_synced_at
                ) VALUES (
                    :sku, :name, :category, :brand, :type, 
                    :price, :buyer_status, :seller_status, 
                    :desc, :start_cut, :end_cut, NOW()
                ) ON DUPLICATE KEY UPDATE 
                    product_name = VALUES(product_name),
                    category = VALUES(category),
                    brand = VALUES(brand),
                    seller_price = VALUES(seller_price),
                    buyer_product_status = VALUES(buyer_product_status),
                    seller_product_status = VALUES(seller_product_status),
                    description = VALUES(description),
                    start_cut_off = VALUES(start_cut_off),
                    end_cut_off = VALUES(end_cut_off),
                    last_synced_at = NOW()
            ");

            foreach ($productsData as $item) {
                // Determine normalized category
                $category = $this->normalizeCategory($item['category'] ?? '');
                $price = $item['price'] ?? $item['admin'] ?? 0;
                
                $stmt->execute([
                    'sku' => $item['buyer_sku_code'],
                    'name' => $item['product_name'],
                    'category' => $category,
                    'brand' => $item['brand'],
                    'type' => $type,
                    'price' => $price,
                    'buyer_status' => $item['buyer_product_status'] ? 1 : 0,
                    'seller_status' => $item['seller_product_status'] ? 1 : 0,
                    'desc' => $item['desc'] ?? '',
                    'start_cut' => $item['start_cut_off'] ?? '',
                    'end_cut' => $item['end_cut_off'] ?? ''
                ]);
            }

            // Apply markup rules to recalculate sell_price
            $this->applyAllMarkups();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to sync price list: " . $e->getMessage());
            return false;
        }
    }

    private function normalizeCategory($apiCategory) {
        $cat = strtolower(trim($apiCategory));
        if (strpos($cat, 'pulsa') !== false) return 'pulsa';
        if (strpos($cat, 'data') !== false) return 'data';
        if (strpos($cat, 'pln') !== false) return 'pln';
        if (strpos($cat, 'e-money') !== false || strpos($cat, 'ewallet') !== false) return 'ewallet';
        if (strpos($cat, 'game') !== false) return 'game';
        if (strpos($cat, 'bpjs') !== false) return 'bpjs';
        if (strpos($cat, 'multifinance') !== false || strpos($cat, 'finance') !== false) return 'multifinance';
        if (strpos($cat, 'bank') !== false || strpos($cat, 'transfer') !== false) return 'bank';
        return $cat;
    }

    /**
     * Apply markups and update sell_price in products table
     */
    public function applyAllMarkups() {
        // Simple logic for now: Default markup of 10% or Rp2000
        $stmt = $this->db->prepare("
            UPDATE digi_products p
            LEFT JOIN digi_markup_rules r 
                ON (r.category = p.category OR r.category IS NULL)
                AND (r.brand = p.brand OR r.brand IS NULL)
            SET p.markup = COALESCE(
                    CASE 
                        WHEN r.markup_type = 'percentage' THEN (p.seller_price * (r.markup_value / 100))
                        ELSE r.markup_value
                    END,
                    2000 -- default fallback markup
                )
        ");
        $stmt->execute();

        // Update sell_price = seller_price + markup
        $this->db->exec("UPDATE digi_products SET sell_price = CEIL((seller_price + markup) / 100) * 100");
    }

    /**
     * Get products for frontend by category and brand
     */
    public function getProducts($category, $brand = null) {
        $sql = "SELECT * FROM digi_products 
                WHERE category = :cat AND is_active = 1 AND buyer_product_status = 1";
        $params = ['cat' => $category];
        
        if ($brand) {
            $sql .= " AND brand = :brand";
            $params['brand'] = $brand;
        }
        
        $sql .= " ORDER BY seller_price ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get distinct brands for a category
     */
    public function getBrands($category) {
        $stmt = $this->db->prepare("SELECT DISTINCT brand FROM digi_products WHERE category = ? AND is_active = 1 AND buyer_product_status = 1 ORDER BY brand");
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get a specific product by SKU
     */
    public function getProductBySku($sku) {
        $stmt = $this->db->prepare("SELECT * FROM digi_products WHERE buyer_sku_code = ?");
        $stmt->execute([$sku]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new transaction log
     */
    public function createTransaction($data) {
        $stmt = $this->db->prepare("
            INSERT INTO digi_transactions (
                ref_id, buyer_sku_code, customer_no, customer_name, product_name, 
                category, brand, type, sell_price, modal_price, profit, 
                status, user_id, message, raw_response
            ) VALUES (
                :ref_id, :sku, :customer_no, :customer_name, :product_name,
                :category, :brand, :type, :sell_price, :modal_price, :profit,
                :status, :user_id, :message, :raw_response
            )
        ");
        
        return $stmt->execute([
            'ref_id' => $data['ref_id'],
            'sku' => $data['buyer_sku_code'],
            'customer_no' => $data['customer_no'],
            'customer_name' => $data['customer_name'] ?? null,
            'product_name' => $data['product_name'] ?? '',
            'category' => $data['category'] ?? '',
            'brand' => $data['brand'] ?? '',
            'type' => $data['type'] ?? 'prepaid',
            'sell_price' => $data['sell_price'] ?? 0,
            'modal_price' => $data['modal_price'] ?? 0,
            'profit' => ($data['sell_price'] ?? 0) - ($data['modal_price'] ?? 0),
            'status' => $data['status'] ?? 'pending',
            'user_id' => $data['user_id'] ?? null,
            'message' => $data['message'] ?? '',
            'raw_response' => isset($data['raw_response']) ? json_encode($data['raw_response']) : null
        ]);
    }

    /**
     * Update transaction status (usually called by webhook)
     */
    public function updateTransactionStatus($refId, $status, $message, $sn = null, $trxId = null, $rawResponse = null) {
        $sql = "UPDATE digi_transactions SET status = :status, message = :message";
        $params = [
            'ref_id' => $refId,
            'status' => $status,
            'message' => $message
        ];

        if ($sn) {
            $sql .= ", sn = :sn";
            $params['sn'] = $sn;
        }
        
        if ($trxId) {
            $sql .= ", digiflazz_trx_id = :trxId";
            $params['trxId'] = $trxId;
        }
        
        if ($rawResponse) {
            $sql .= ", raw_response = :raw";
            $params['raw'] = json_encode($rawResponse);
        }

        $sql .= " WHERE ref_id = :ref_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get transaction history
     */
    public function getTransactions($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as agent_name 
            FROM digi_transactions t
            LEFT JOIN users u ON t.user_id = u.id
            ORDER BY t.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get transaction stats for Laporan
     */
    public function getTransactionStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_trx,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status = 'pending' OR status = 'processing' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN status = 'success' THEN sell_price ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'success' THEN modal_price ELSE 0 END) as total_cost,
                SUM(CASE WHEN status = 'success' THEN profit ELSE 0 END) as total_profit
            FROM digi_transactions
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
