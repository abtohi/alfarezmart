<?php
/**
 * Digiflazz Model
 * Handles database operations for Digiflazz products and transactions
 */
require_once __DIR__ . '/../config/Database.php';

class DigiflazzModel {
    public \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Sync Price List from Digiflazz API response
     */
    public function syncPriceList(array $productsData, string $type = 'prepaid') {
        // Auto-migrate: ensure seller_name column exists
        try {
            $check = $this->db->query("SHOW COLUMNS FROM digi_products LIKE 'seller_name'");
            if ($check->rowCount() === 0) {
                $this->db->exec("ALTER TABLE digi_products ADD COLUMN seller_name VARCHAR(100) NULL AFTER brand");
            }
        } catch (\Exception $e) {
            error_log("[DigiflazzModel] seller_name migration error: " . $e->getMessage());
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO digi_products (
                    buyer_sku_code, product_name, category, sub_category, brand, type, 
                    seller_price, buyer_product_status, seller_product_status, 
                    description, start_cut_off, end_cut_off, last_synced_at, seller_name
                ) VALUES (
                    :sku, :name, :category, :sub_cat, :brand, :type, 
                    :price, :buyer_status, :seller_status, 
                    :desc, :start_cut, :end_cut, NOW(), :seller_name
                ) ON DUPLICATE KEY UPDATE 
                    product_name = VALUES(product_name),
                    category = VALUES(category),
                    sub_category = VALUES(sub_category),
                    brand = VALUES(brand),
                    seller_price = VALUES(seller_price),
                    buyer_product_status = VALUES(buyer_product_status),
                    seller_product_status = VALUES(seller_product_status),
                    description = VALUES(description),
                    start_cut_off = VALUES(start_cut_off),
                    end_cut_off = VALUES(end_cut_off),
                    last_synced_at = NOW(),
                    seller_name = VALUES(seller_name)
            ");

            foreach ($productsData as $item) {
                // Determine normalized category
                $category = $this->normalizeCategory($item['category'] ?? '');
                $subCat = $this->determineSubCategory($category, $item['product_name'] ?? '');
                $price = $item['price'] ?? $item['admin'] ?? 0;
                
                $stmt->execute([
                    'sku' => $item['buyer_sku_code'],
                    'name' => $item['product_name'],
                    'category' => $category,
                    'sub_cat' => $subCat,
                    'brand' => $item['brand'],
                    'type' => $type,
                    'price' => $price,
                    'buyer_status' => $item['buyer_product_status'] ? 1 : 0,
                    'seller_status' => $item['seller_product_status'] ? 1 : 0,
                    'desc' => $item['desc'] ?? '',
                    'start_cut' => $item['start_cut_off'] ?? '',
                    'end_cut' => $item['end_cut_off'] ?? '',
                    'seller_name' => $item['seller_name'] ?? null
                ]);
            }

            // Apply markup rules to recalculate sell_price
            $this->applyAllMarkups();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            try {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
            } catch (Exception $e2) {
                // Ignore rollback errors
            }
            error_log("Failed to sync price list: " . $e->getMessage());
            return false;
        }
    }

    private function normalizeCategory(string $apiCategory) {
        $cat = strtolower(trim($apiCategory));
        if (strpos($cat, 'pulsa') !== false) return 'pulsa';
        if (strpos($cat, 'data') !== false) return 'data';
        if (strpos($cat, 'sms') !== false || strpos($cat, 'nelpon') !== false) return 'sms_nelpon';
        if (strpos($cat, 'pln') !== false) return 'pln';
        if (strpos($cat, 'e-money') !== false || strpos($cat, 'ewallet') !== false) return 'ewallet';
        if (strpos($cat, 'game') !== false) return 'game';
        if (strpos($cat, 'bpjs') !== false) return 'bpjs';
        if (strpos($cat, 'multifinance') !== false || strpos($cat, 'finance') !== false) return 'multifinance';
        if (strpos($cat, 'bank') !== false || strpos($cat, 'transfer') !== false) return 'bank';
        if (strpos($cat, 'tv') !== false) return 'tv';
        return $cat;
    }

    private function determineSubCategory(string $category, string $productName) {
        if ($category !== 'data' && $category !== 'sms_nelpon') return null;
        
        $name = strtoupper($productName);
        
        if ($category === 'data') {
            if (strpos($name, 'COMBO SAKTI') !== false) return 'Combo Sakti';
            if (strpos($name, 'SAKTI') !== false) return 'Sakti';
            if (strpos($name, 'OMG') !== false) return 'Data OMG!';
            if (strpos($name, 'FLASH') !== false) return 'Flash';
            if (strpos($name, 'BULK') !== false) return 'Bulk / Inject';
            if (strpos($name, 'GIGAMAX') !== false) return 'Gigamax';
            if (strpos($name, 'KETENGAN') !== false) return 'Ketengan';
            if (strpos($name, 'MAXSTREAM') !== false) return 'Maxstream';
            if (strpos($name, 'YELLOW') !== false) return 'Yellow';
            if (strpos($name, 'FREEDOM') !== false) return 'Freedom';
            if (strpos($name, 'XTRA') !== false) return 'Xtra';
            if (strpos($name, 'AIGO') !== false) return 'Aigo';
            if (strpos($name, 'BRONET') !== false) return 'Bronet';
            if (strpos($name, 'HAPPY') !== false) return 'Happy';
            if (strpos($name, 'UNLIMITED') !== false) return 'Unlimited';
            if (strpos($name, 'KUOTA') !== false || strpos($name, 'DATA') !== false) return 'Data Reguler';
            return 'Lainnya';
        }
        
        if ($category === 'sms_nelpon') {
            if (strpos($name, 'ALL OPERATOR') !== false || strpos($name, 'SEMUA OPERATOR') !== false || strpos($name, 'SEMUA OPR') !== false) return 'Nelpon Semua Operator';
            if (strpos($name, 'SESAMA') !== false) return 'Nelpon Sesama';
            if (strpos($name, 'SMS') !== false) return 'Paket SMS';
            return 'Lainnya';
        }
        
        return null;
    }

    /**
     * Apply markups and update sell_price in products table
     */
    public function applyAllMarkups() {
        // Apply category-specific markup from digi_markup_rules table
        $this->db->exec("
            UPDATE digi_products p
            LEFT JOIN digi_markup_rules r ON r.category = p.category AND r.brand IS NULL AND r.is_active = 1
            SET p.markup = COALESCE(
                CASE 
                    WHEN r.markup_type = 'percentage' THEN (p.seller_price * (r.markup_value / 100))
                    ELSE r.markup_value
                END,
                2000
            )
        ");

        // Update sell_price = seller_price + markup, rounded to nearest 100, ONLY for non-custom prices
        $this->db->exec("UPDATE digi_products SET sell_price = CEIL((seller_price + markup) / 100) * 100 WHERE is_custom_price = 0");
    }

    /**
     * Update specific product selling price (Custom Price)
     */
    public function updateCustomPrice(string $sku, float $sellPrice) {
        $stmt = $this->db->prepare("UPDATE digi_products SET sell_price = :sell_price, is_custom_price = 1 WHERE buyer_sku_code = :sku");
        return $stmt->execute([
            'sell_price' => $sellPrice,
            'sku' => $sku
        ]);
    }

    /**
     * Reset specific product selling price to auto markup
     */
    public function resetCustomPrice(string $sku) {
        $stmt = $this->db->prepare("UPDATE digi_products SET is_custom_price = 0 WHERE buyer_sku_code = :sku");
        $stmt->execute(['sku' => $sku]);
        $this->applyAllMarkups(); // Re-apply to update the sell_price of this specific product
        return true;
    }

    /**
     * Get all markup rules
     */
    public function getMarkupRules() {
        $stmt = $this->db->query("SELECT * FROM digi_markup_rules ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save (upsert) a markup rule
     */
    public function saveMarkupRule(string $category, string $markupType, float $markupValue) {
        $stmt = $this->db->prepare("
            INSERT INTO digi_markup_rules (category, brand, markup_type, markup_value)
            VALUES (:cat, NULL, :type, :val)
            ON DUPLICATE KEY UPDATE markup_type = :type, markup_value = :val
        ");
        return $stmt->execute(['cat' => $category, 'type' => $markupType, 'val' => $markupValue]);
    }

    /**
     * Get a single transaction by ref_id
     */
    public function getTransactionByRefId(string $refId) {
        $stmt = $this->db->prepare("SELECT * FROM digi_transactions WHERE ref_id = ? LIMIT 1");
        $stmt->execute([$refId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get products for frontend by category and brand
     */
    public function getProducts(string $category, ?string $brand = null, string $type = 'prepaid') {
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
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Deduplicate products by product_name and seller_price, and ignore empty sellers
        $unique = [];
        $result = [];
        foreach ($products as $p) {
            // Filter out products with no seller name
            if (empty(trim($p['seller_name'] ?? ''))) {
                continue;
            }
            
            $key = trim($p['product_name']) . '|' . $p['seller_price'];
            if (!isset($unique[$key])) {
                $unique[$key] = true;
                $result[] = $p;
            }
        }
        return $result;
    }

    /**
     * Get distinct brands for a category
     */
    public function getBrands(string $category) {
        $stmt = $this->db->prepare("SELECT DISTINCT brand FROM digi_products WHERE category = ? AND is_active = 1 AND buyer_product_status = 1 ORDER BY brand");
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get a specific product by SKU
     */
    public function getProductBySku(string $sku) {
        $stmt = $this->db->prepare("SELECT * FROM digi_products WHERE buyer_sku_code = ?");
        $stmt->execute([$sku]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new transaction log
     */
    public function createTransaction(array $data) {
        // Find seller_name from digi_products
        $sellerName = null;
        $sku = $data['buyer_sku_code'] ?? null;
        if ($sku) {
            $stmtProd = $this->db->prepare("SELECT seller_name FROM digi_products WHERE buyer_sku_code = ? LIMIT 1");
            $stmtProd->execute([$sku]);
            $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);
            if ($prod && !empty($prod['seller_name'])) {
                $sellerName = $prod['seller_name'];
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO digi_transactions (
                ref_id, buyer_sku_code, customer_no, customer_name, product_name, 
                category, brand, type, sell_price, modal_price, profit, 
                status, user_id, message, raw_response, seller_name
            ) VALUES (
                :ref_id, :sku, :customer_no, :customer_name, :product_name,
                :category, :brand, :type, :sell_price, :modal_price, :profit,
                :status, :user_id, :message, :raw_response, :seller_name
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
            'raw_response' => isset($data['raw_response']) ? json_encode($data['raw_response']) : null,
            'seller_name' => $sellerName
        ]);
    }

    /**
     * Update transaction status (usually called by webhook)
     */
    public function updateTransactionStatus(string $refId, string $status, string $message, ?string $sn = null, ?string $trxId = null, $rawResponse = null) {
        $sql = "UPDATE digi_transactions SET status = :status, message = :message, updated_at = NOW()";
        $params = [
            'ref_id'  => $refId,
            'status'  => $status,
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

        // Prevent race condition: If we are updating TO 'pending', only do so if the current status is STILL 'pending'.
        // If we are updating to 'success' or 'failed', we can overwrite whatever is there.
        if ($status === 'pending') {
            $sql .= " WHERE ref_id = :ref_id AND status = 'pending'";
        } else {
            $sql .= " WHERE ref_id = :ref_id";
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            error_log("[DigiflazzModel] updateTransactionStatus ref_id=$refId status=$status rows=" . $stmt->rowCount());
            return $result;
        } catch (PDOException $e) {
            error_log("[DigiflazzModel] SQL Error in updateTransactionStatus: " . $e->getMessage() . " SQL: " . $sql);
            return false;
        }
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

    /**
     * Get transaction data for Analytics Dashboard
     */
    public function getAnalyticsData(string $startDate, string $endDate) {
        $sql = "SELECT id, ref_id, buyer_sku_code, category, product_name, type, sell_price, modal_price, status, raw_response, created_at, updated_at 
                FROM digi_transactions 
                WHERE created_at >= :start_date AND created_at <= :end_date
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start_date', $startDate . ' 00:00:00');
        $stmt->bindValue(':end_date', $endDate . ' 23:59:59');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create deposit log
     */
    public function createDepositLog(array $data) {
        $stmt = $this->db->prepare("
            INSERT INTO digi_deposits (amount, bank, owner_name, status, notes, raw_response)
            VALUES (:amount, :bank, :owner_name, :status, :notes, :raw)
        ");
        return $stmt->execute([
            'amount' => $data['amount'],
            'bank' => $data['bank'],
            'owner_name' => $data['owner_name'],
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? '',
            'raw' => isset($data['raw']) ? json_encode($data['raw']) : null
        ]);
    }

    /**
     * Get recent deposits
     */
    public function getDeposits($limit = 20) {
        $stmt = $this->db->prepare("SELECT * FROM digi_deposits ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get success rates for all sellers
     */
    public function getSellerSuccessRates() {
        try {
            $stmt = $this->db->query("
                SELECT seller_name,
                       COUNT(id) as total,
                       SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success
                FROM digi_transactions
                WHERE seller_name IS NOT NULL AND seller_name != '' 
                  AND status IN ('success', 'failed')
                GROUP BY seller_name
            ");
            $rates = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rates[$row['seller_name']] = [
                    'total' => (int)$row['total'],
                    'success' => (int)$row['success']
                ];
            }
            return $rates;
        } catch (\Exception $e) {
            error_log("[DigiflazzModel] getSellerSuccessRates error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent transactions for a specific seller
     */
    public function getSellerHistory(string $sellerName, int $page = 1, int $limit = 10) {
        // Analytics
        $stmtStat = $this->db->prepare("
            SELECT 
                COUNT(*) as total_trx,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as total_success,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as total_failed,
                SUM(CASE WHEN category = 'pln' THEN 1 ELSE 0 END) as cat_pln,
                SUM(CASE WHEN category = 'data' THEN 1 ELSE 0 END) as cat_data,
                SUM(CASE WHEN category = 'pulsa' THEN 1 ELSE 0 END) as cat_pulsa,
                SUM(CASE WHEN category = 'game' THEN 1 ELSE 0 END) as cat_game,
                SUM(CASE WHEN category NOT IN ('pln','data','pulsa','game') THEN 1 ELSE 0 END) as cat_other
            FROM digi_transactions
            WHERE seller_name = :seller AND status IN ('success', 'failed')
        ");
        $stmtStat->execute(['seller' => $sellerName]);
        $analytics = $stmtStat->fetch(PDO::FETCH_ASSOC);

        // Pagination
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("
            SELECT customer_no, status, created_at, product_name, seller_name, message, modal_price, sell_price, profit, category
            FROM digi_transactions
            WHERE seller_name = :seller
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':seller', $sellerName);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count total for pagination
        $stmtTotal = $this->db->prepare("SELECT COUNT(*) FROM digi_transactions WHERE seller_name = :seller");
        $stmtTotal->execute(['seller' => $sellerName]);
        $totalRecords = $stmtTotal->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);

        return [
            'analytics' => [
                'total' => (int)$analytics['total_trx'],
                'success' => (int)$analytics['total_success'],
                'failed' => (int)$analytics['total_failed'],
                'categories' => [
                    'PLN' => (int)$analytics['cat_pln'],
                    'Data' => (int)$analytics['cat_data'],
                    'Pulsa' => (int)$analytics['cat_pulsa'],
                    'Game' => (int)$analytics['cat_game'],
                    'Lainnya' => (int)$analytics['cat_other'],
                ]
            ],
            'pagination' => [
                'total_records' => $totalRecords,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'limit' => $limit
            ],
            'data' => $data
        ];
    }
}
