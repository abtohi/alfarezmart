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
        $this->ensureTables();
        try {
            if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
                $check = $this->db->query("SHOW COLUMNS FROM digi_products LIKE 'is_custom_price'");
                if ($check && $check->rowCount() === 0) {
                    $this->db->exec("ALTER TABLE digi_products ADD COLUMN is_custom_price TINYINT(1) NOT NULL DEFAULT 0 AFTER sell_price");
                }
            }
            // Auto repair legacy/generic 'pascabayar' category to proper specific categories
            $this->db->exec("UPDATE digi_products SET category = 'multifinance' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) = 'multifinance' OR LOWER(product_name) LIKE '%finance%' OR LOWER(product_name) LIKE '%credit%' OR LOWER(product_name) LIKE '%angsuran%')");
            $this->db->exec("UPDATE digi_products SET category = 'pln' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) = 'pln' OR LOWER(product_name) LIKE '%pln%' OR LOWER(buyer_sku_code) LIKE 'pln%')");
            $this->db->exec("UPDATE digi_products SET category = 'bpjs' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) LIKE '%bpjs%' OR LOWER(product_name) LIKE '%bpjs%' OR LOWER(buyer_sku_code) LIKE 'bpjs%')");
            $this->db->exec("UPDATE digi_products SET category = 'pdam' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) = 'pdam' OR LOWER(product_name) LIKE '%pdam%' OR LOWER(product_name) LIKE '%air%')");
            $this->db->exec("UPDATE digi_products SET category = 'hp' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) IN ('hp', 'halo', 'matrix', 'xl prioritas') OR LOWER(product_name) LIKE '%pasca%')");
            $this->db->exec("UPDATE digi_products SET category = 'internet' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) IN ('tv', 'internet', 'telkom', 'indihome', 'biznet', 'cbn', 'first media', 'mnc') OR LOWER(product_name) LIKE '%indihome%' OR LOWER(product_name) LIKE '%telkom%' OR LOWER(product_name) LIKE '%internet%')");
            $this->db->exec("UPDATE digi_products SET category = 'ewallet' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) IN ('e-money', 'emoney', 'dana', 'gopay', 'ovo', 'shopeepay', 'linkaja') OR LOWER(product_name) LIKE '%bebas nominal%')");
            $this->db->exec("UPDATE digi_products SET category = 'samsat' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) LIKE '%samsat%' OR LOWER(product_name) LIKE '%samsat%' OR LOWER(buyer_sku_code) LIKE 'samsat%')");
            $this->db->exec("UPDATE digi_products SET category = 'pbb' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) LIKE '%pbb%' OR LOWER(product_name) LIKE '%pbb%' OR LOWER(buyer_sku_code) LIKE 'pbb%' OR LOWER(buyer_sku_code) = 'cimahi')");
            $this->db->exec("UPDATE digi_products SET category = 'gas' WHERE type = 'postpaid' AND (LOWER(category) = 'pascabayar' OR category IS NULL OR category = '') AND (LOWER(brand) IN ('gas', 'pgn', 'pertagas') OR LOWER(product_name) LIKE '%gas%')");
            
            // Ensure all prepaid products without custom prices have sell_price = 0
            $this->db->exec("UPDATE digi_products SET sell_price = 0 WHERE type = 'prepaid' AND is_custom_price = 0");
            
            // Fix legacy negative profits where sell_price = 0
            $this->db->exec("UPDATE digi_transactions SET profit = 0 WHERE (sell_price = 0 OR sell_price IS NULL) AND profit < 0");
            
            $this->seedDefaultPostpaidProducts();
        } catch (\Exception $e) {
            error_log("[DigiflazzModel] init migration error: " . $e->getMessage());
        }
    }

    /**
     * Auto-create required Digiflazz tables if missing
     */
    public function ensureTables(): void {
        try {
            $isSqlite = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
            if ($isSqlite) {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS digi_markup_rules (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        category TEXT UNIQUE,
                        brand TEXT,
                        markup_type TEXT DEFAULT 'fixed',
                        markup_value REAL DEFAULT 0,
                        min_price REAL DEFAULT 0,
                        max_price REAL DEFAULT 0,
                        is_active INTEGER DEFAULT 1,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    );
                    CREATE TABLE IF NOT EXISTS digi_deposits (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        amount REAL DEFAULT 0,
                        bank TEXT,
                        owner_name TEXT,
                        status TEXT DEFAULT 'pending',
                        notes TEXT,
                        raw_response TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            } else {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS digi_markup_rules (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        category VARCHAR(50) UNIQUE,
                        brand VARCHAR(100),
                        markup_type ENUM('fixed','percentage') DEFAULT 'fixed',
                        markup_value DECIMAL(15,2) DEFAULT 0,
                        min_price DECIMAL(15,2) DEFAULT 0,
                        max_price DECIMAL(15,2) DEFAULT 0,
                        is_active TINYINT(1) DEFAULT 1,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    );
                    CREATE TABLE IF NOT EXISTS digi_deposits (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        amount DECIMAL(15,2) DEFAULT 0,
                        bank VARCHAR(50),
                        owner_name VARCHAR(100),
                        status ENUM('pending','success','failed','expired') DEFAULT 'pending',
                        notes TEXT,
                        raw_response JSON,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_status (status),
                        INDEX idx_date (created_at)
                    );
                ");
            }

            // Seed default markup rules if table is empty
            $countStmt = $this->db->query("SELECT COUNT(*) as c FROM digi_markup_rules");
            $count = (int)($countStmt ? ($countStmt->fetchColumn() ?? 0) : 0);
            if ($count === 0) {
                $defaultCategories = [
                    'Pulsa' => ['fixed', 1500],
                    'Data' => ['fixed', 2000],
                    'PLN' => ['fixed', 2500],
                    'E-Money' => ['fixed', 1500],
                    'Games' => ['fixed', 2000],
                    'Voucher' => ['fixed', 2000],
                    'Streaming' => ['fixed', 2000],
                    'TV' => ['fixed', 2500],
                    'Pascabayar' => ['fixed', 2500],
                ];
                foreach ($defaultCategories as $cat => $val) {
                    $this->saveMarkupRule($cat, $val[0], (float)$val[1]);
                }
            }

            $this->seedDefaultPostpaidProducts();
        } catch (\Throwable $e) {
            error_log("[DigiflazzModel] ensureTables error: " . $e->getMessage());
        }
    }

    /**
     * Seed default standard postpaid products for Digiflazz if missing
     */
    public function seedDefaultPostpaidProducts(): void {
        try {
            $isSqlite = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
            $defaultPostpaid = [
                // PLN Pascabayar
                ['sku' => 'pln', 'name' => 'PLN Pascabayar (Tagihan Listrik)', 'category' => 'pln', 'brand' => 'PLN', 'price' => 2500, 'desc' => 'Pembayaran tagihan listrik PLN bulanan pascabayar'],
                ['sku' => 'plnnontaglis', 'name' => 'PLN Non-Taglis (Pasang Baru / Tambah Daya)', 'category' => 'pln', 'brand' => 'PLN', 'price' => 2500, 'desc' => 'Pembayaran non-tagihan listrik (pasang baru, rubah daya, migrasi meter)'],

                // BPJS
                ['sku' => 'BPJS', 'name' => 'BPJS Kesehatan', 'category' => 'bpjs', 'brand' => 'BPJS KESEHATAN', 'price' => 2500, 'desc' => 'Pembayaran iuran bulanan BPJS Kesehatan'],
                ['sku' => 'bpjstk', 'name' => 'BPJS Ketenagakerjaan (BPU)', 'category' => 'bpjs', 'brand' => 'BPJS KETENAGAKERJAAN', 'price' => 2500, 'desc' => 'Pembayaran iuran BPJS Ketenagakerjaan Bukan Penerima Upah'],
                ['sku' => 'bpjstkpu', 'name' => 'BPJS Ketenagakerjaan (PU)', 'category' => 'bpjs', 'brand' => 'BPJS KETENAGAKERJAAN', 'price' => 2500, 'desc' => 'Pembayaran iuran BPJS Ketenagakerjaan Penerima Upah'],

                // Internet & TV
                ['sku' => 'internet', 'name' => 'Telkom IndiHome / Speedy', 'category' => 'internet', 'brand' => 'INDIHOME', 'price' => 2500, 'desc' => 'Tagihan internet WiFi IndiHome Telkom'],
                ['sku' => 'telkom', 'name' => 'Telepon Rumah & Speedy (Telkom)', 'category' => 'internet', 'brand' => 'TELKOM', 'price' => 2500, 'desc' => 'Tagihan telepon rumah PSTN & Telkom'],
                ['sku' => 'biznet', 'name' => 'Biznet Home Internet', 'category' => 'internet', 'brand' => 'BIZNET', 'price' => 2500, 'desc' => 'Tagihan internet fiber Biznet Home'],
                ['sku' => 'cbn', 'name' => 'CBN Fiber Internet', 'category' => 'internet', 'brand' => 'CBN', 'price' => 2500, 'desc' => 'Tagihan internet CBN'],
                ['sku' => 'firstmedia', 'name' => 'First Media Cable & Internet', 'category' => 'internet', 'brand' => 'FIRST MEDIA', 'price' => 2500, 'desc' => 'Tagihan First Media Internet & TV Kabel'],
                ['sku' => 'mncplay', 'name' => 'MNC Play Media', 'category' => 'internet', 'brand' => 'MNC PLAY', 'price' => 2500, 'desc' => 'Tagihan internet & TV MNC Play'],
                ['sku' => 'transvision', 'name' => 'Transvision Pascabayar', 'category' => 'internet', 'brand' => 'TRANSVISION', 'price' => 2500, 'desc' => 'Tagihan TV Satelit Transvision'],
                ['sku' => 'kvision', 'name' => 'K-Vision Pascabayar', 'category' => 'internet', 'brand' => 'K-VISION', 'price' => 2500, 'desc' => 'Tagihan langganan K-Vision pascabayar'],

                // E-Money Pascabayar (Bebas Nominal)
                ['sku' => 'DANA', 'name' => 'DANA Bebas Nominal', 'category' => 'ewallet', 'brand' => 'DANA', 'price' => 1500, 'desc' => 'Top up saldo DANA bebas nominal pascabayar'],
                ['sku' => 'GOPAY', 'name' => 'GoPay Bebas Nominal', 'category' => 'ewallet', 'brand' => 'GO PAY', 'price' => 2000, 'desc' => 'Top up saldo GoPay bebas nominal pascabayar'],
                ['sku' => 'OVO', 'name' => 'OVO Bebas Nominal', 'category' => 'ewallet', 'brand' => 'OVO', 'price' => 2000, 'desc' => 'Top up saldo OVO bebas nominal pascabayar'],
                ['sku' => 'SHOPEEPAY', 'name' => 'ShopeePay Bebas Nominal', 'category' => 'ewallet', 'brand' => 'SHOPEE PAY', 'price' => 1500, 'desc' => 'Top up saldo ShopeePay bebas nominal pascabayar'],
                ['sku' => 'LINKAJA', 'name' => 'LinkAja Bebas Nominal', 'category' => 'ewallet', 'brand' => 'LINKAJA', 'price' => 1500, 'desc' => 'Top up saldo LinkAja bebas nominal pascabayar'],
                ['sku' => 'emoney', 'name' => 'E-Money Mandiri / Tapcash / Brizzi', 'category' => 'ewallet', 'brand' => 'E-MONEY', 'price' => 1500, 'desc' => 'Top up saldo E-Money / Uang Elektronik bebas nominal'],

                // SAMSAT (Pajak Kendaraan Bermotor)
                ['sku' => 'samsat', 'name' => 'SAMSAT Nasional (Signal / e-Samsat)', 'category' => 'samsat', 'brand' => 'SAMSAT', 'price' => 3000, 'desc' => 'Pembayaran PKB Pajak Kendaraan Bermotor Nasional'],
                ['sku' => 'samsatjabar', 'name' => 'SAMSAT Jawa Barat (Sambara)', 'category' => 'samsat', 'brand' => 'SAMSAT', 'price' => 3000, 'desc' => 'Pembayaran Pajak Kendaraan Bermotor Provinsi Jawa Barat'],
                ['sku' => 'samsatjatim', 'name' => 'SAMSAT Jawa Timur', 'category' => 'samsat', 'brand' => 'SAMSAT', 'price' => 3000, 'desc' => 'Pembayaran Pajak Kendaraan Bermotor Provinsi Jawa Timur'],
                ['sku' => 'samsatjateng', 'name' => 'SAMSAT Jawa Tengah (Sakpole)', 'category' => 'samsat', 'brand' => 'SAMSAT', 'price' => 3000, 'desc' => 'Pembayaran Pajak Kendaraan Bermotor Provinsi Jawa Tengah'],
                ['sku' => 'samsatdki', 'name' => 'SAMSAT DKI Jakarta', 'category' => 'samsat', 'brand' => 'SAMSAT', 'price' => 3000, 'desc' => 'Pembayaran Pajak Kendaraan Bermotor Provinsi DKI Jakarta'],

                // PBB (Pajak Bumi dan Bangunan)
                ['sku' => 'pbb', 'name' => 'PBB Nasional', 'category' => 'pbb', 'brand' => 'PBB', 'price' => 2500, 'desc' => 'Pembayaran Pajak Bumi dan Bangunan Nasional'],
                ['sku' => 'pbbdki', 'name' => 'PBB DKI Jakarta', 'category' => 'pbb', 'brand' => 'PBB', 'price' => 2500, 'desc' => 'Pembayaran PBB Provinsi DKI Jakarta'],
                ['sku' => 'cimahi', 'name' => 'PBB Kota Cimahi', 'category' => 'pbb', 'brand' => 'PBB', 'price' => 2500, 'desc' => 'Pembayaran PBB Kota Cimahi Jawa Barat'],
                ['sku' => 'pbbbandung', 'name' => 'PBB Kota / Kab Bandung', 'category' => 'pbb', 'brand' => 'PBB', 'price' => 2500, 'desc' => 'Pembayaran PBB Kota dan Kabupaten Bandung'],
                ['sku' => 'pbbsubang', 'name' => 'PBB Kab Subang', 'category' => 'pbb', 'brand' => 'PBB', 'price' => 2500, 'desc' => 'Pembayaran PBB Kabupaten Subang'],
                ['sku' => 'pbbsurabaya', 'name' => 'PBB Kota Surabaya', 'category' => 'pbb', 'brand' => 'PBB', 'price' => 2500, 'desc' => 'Pembayaran PBB Kota Surabaya Jawa Timur'],
                ['sku' => 'pbbsemarang', 'name' => 'PBB Kota Semarang', 'category' => 'pbb', 'brand' => 'PBB', 'price' => 2500, 'desc' => 'Pembayaran PBB Kota Semarang Jawa Tengah'],

                // PDAM (Air Bersih)
                ['sku' => 'pdam', 'name' => 'PDAM Nasional', 'category' => 'pdam', 'brand' => 'PDAM', 'price' => 2500, 'desc' => 'Pembayaran tagihan rekening air PDAM Nasional'],
                ['sku' => 'pdambandung', 'name' => 'PDAM Tirtawening Kota Bandung', 'category' => 'pdam', 'brand' => 'PDAM', 'price' => 2500, 'desc' => 'Tagihan PDAM Tirtawening Kota Bandung'],
                ['sku' => 'pdamkabbandung', 'name' => 'PDAM Tirta Raharja Kab Bandung', 'category' => 'pdam', 'brand' => 'PDAM', 'price' => 2500, 'desc' => 'Tagihan PDAM Tirta Raharja Kabupaten Bandung'],
                ['sku' => 'pdamjakarta', 'name' => 'PDAM PAM JAYA DKI Jakarta', 'category' => 'pdam', 'brand' => 'PDAM', 'price' => 2500, 'desc' => 'Tagihan PAM JAYA DKI Jakarta (Aetra / Palyja)'],
                ['sku' => 'pdamsurabaya', 'name' => 'PDAM Surya Sembada Surabaya', 'category' => 'pdam', 'brand' => 'PDAM', 'price' => 2500, 'desc' => 'Tagihan PDAM Surya Sembada Kota Surabaya'],
                ['sku' => 'pdamsemarang', 'name' => 'PDAM Tirta Moedal Semarang', 'category' => 'pdam', 'brand' => 'PDAM', 'price' => 2500, 'desc' => 'Tagihan PDAM Tirta Moedal Kota Semarang'],

                // Gas Negara
                ['sku' => 'pgas', 'name' => 'PGN (Perusahaan Gas Negara)', 'category' => 'gas', 'brand' => 'PGN', 'price' => 2500, 'desc' => 'Pembayaran tagihan gas rumah tangga & industri PGN'],
                ['sku' => 'pertagas', 'name' => 'Pertamina Gas (Pertagas)', 'category' => 'gas', 'brand' => 'PERTAGAS', 'price' => 2500, 'desc' => 'Pembayaran tagihan Pertamina Gas'],

                // HP Pascabayar
                ['sku' => 'kartuhalo', 'name' => 'Kartu Halo (Telkomsel Pasca)', 'category' => 'hp', 'brand' => 'TELKOMSEL', 'price' => 2500, 'desc' => 'Tagihan kartu Halo Telkomsel pascabayar'],
                ['sku' => 'matrix', 'name' => 'Indosat Matrix (Indosat Pasca)', 'category' => 'hp', 'brand' => 'INDOSAT', 'price' => 2500, 'desc' => 'Tagihan Indosat Matrix pascabayar'],
                ['sku' => 'xlprioritas', 'name' => 'XL Prioritas (XL Pasca)', 'category' => 'hp', 'brand' => 'XL', 'price' => 2500, 'desc' => 'Tagihan XL Prioritas pascabayar'],
                ['sku' => 'smartfrenpasca', 'name' => 'Smartfren Postpaid', 'category' => 'hp', 'brand' => 'SMARTFREN', 'price' => 2500, 'desc' => 'Tagihan Smartfren pascabayar'],
            ];

            if ($isSqlite) {
                $stmt = $this->db->prepare("
                    INSERT INTO digi_products (
                        buyer_sku_code, product_name, category, brand, type, 
                        seller_price, sell_price, markup, buyer_product_status, seller_product_status, 
                        description, seller_name, is_active, last_synced_at
                    ) VALUES (
                        :sku, :name, :category, :brand, 'postpaid', 
                        :price, :sell_price, 0, 1, 1, 
                        :desc, 'Digiflazz', 1, CURRENT_TIMESTAMP
                    ) ON CONFLICT(buyer_sku_code) DO UPDATE SET
                        category = excluded.category,
                        brand = excluded.brand,
                        type = 'postpaid',
                        buyer_product_status = 1,
                        seller_product_status = 1,
                        is_active = 1
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO digi_products (
                        buyer_sku_code, product_name, category, brand, type, 
                        seller_price, sell_price, markup, buyer_product_status, seller_product_status, 
                        description, seller_name, is_active, last_synced_at
                    ) VALUES (
                        :sku, :name, :category, :brand, 'postpaid', 
                        :price, :sell_price, 0, 1, 1, 
                        :desc, 'Digiflazz', 1, NOW()
                    ) ON DUPLICATE KEY UPDATE
                        category = VALUES(category),
                        brand = VALUES(brand),
                        type = 'postpaid',
                        buyer_product_status = 1,
                        seller_product_status = 1,
                        is_active = 1
                ");
            }

            foreach ($defaultPostpaid as $item) {
                $stmt->execute([
                    'sku' => $item['sku'],
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'brand' => $item['brand'],
                    'price' => $item['price'],
                    'sell_price' => $item['price'],
                    'desc' => $item['desc']
                ]);
            }
        } catch (\Throwable $e) {
            error_log("[DigiflazzModel] seedDefaultPostpaidProducts error: " . $e->getMessage());
        }
    }

    /**
     * Sync Price List from Digiflazz API response
     */
    public function syncPriceList(array $productsData, string $type = 'prepaid') {
        try {
            $isSqlite = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
            if (!$isSqlite) {
                $check = $this->db->query("SHOW COLUMNS FROM digi_products LIKE 'seller_name'");
                if ($check && $check->rowCount() === 0) {
                    $this->db->exec("ALTER TABLE digi_products ADD COLUMN seller_name VARCHAR(100) NULL AFTER brand");
                }
            }
        } catch (\Exception $e) {
            // Migration check silent fallback
        }

        $isSqlite = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');

        $this->db->beginTransaction();
        try {
            if ($isSqlite) {
                $stmt = $this->db->prepare("
                    INSERT INTO digi_products (
                        buyer_sku_code, product_name, category, sub_category, brand, type, 
                        seller_price, buyer_product_status, seller_product_status, 
                        description, start_cut_off, end_cut_off, last_synced_at, seller_name, is_active
                    ) VALUES (
                        :sku, :name, :category, :sub_cat, :brand, :type, 
                        :price, :buyer_status, :seller_status, 
                        :desc, :start_cut, :end_cut, CURRENT_TIMESTAMP, :seller_name, 1
                    ) ON CONFLICT(buyer_sku_code) DO UPDATE SET 
                        product_name = excluded.product_name,
                        category = excluded.category,
                        sub_category = excluded.sub_category,
                        brand = excluded.brand,
                        seller_price = excluded.seller_price,
                        buyer_product_status = excluded.buyer_product_status,
                        seller_product_status = excluded.seller_product_status,
                        description = excluded.description,
                        start_cut_off = excluded.start_cut_off,
                        end_cut_off = excluded.end_cut_off,
                        last_synced_at = CURRENT_TIMESTAMP,
                        seller_name = excluded.seller_name,
                        is_active = 1
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO digi_products (
                        buyer_sku_code, product_name, category, sub_category, brand, type, 
                        seller_price, buyer_product_status, seller_product_status, 
                        description, start_cut_off, end_cut_off, last_synced_at, seller_name, is_active
                    ) VALUES (
                        :sku, :name, :category, :sub_cat, :brand, :type, 
                        :price, :buyer_status, :seller_status, 
                        :desc, :start_cut, :end_cut, NOW(), :seller_name, 1
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
                        seller_name = VALUES(seller_name),
                        is_active = 1
                ");
            }

            foreach ($productsData as $item) {
                $sku = trim($item['buyer_sku_code'] ?? $item['sku_code'] ?? $item['pasca_code'] ?? '');
                if (empty($sku)) {
                    continue;
                }
                
                $itemBrand = $item['brand'] ?? '';
                $itemName  = $item['product_name'] ?? '';
                $itemCat   = $item['category'] ?? '';

                // Determine normalized category using category, brand, product_name, and type
                $category = $this->normalizeCategory($itemCat, $itemBrand, $itemName, $type);
                $subCat = $this->determineSubCategory($category, $itemName);
                $price = $item['price'] ?? $item['admin'] ?? 0;
                
                $buyerStatus = filter_var($item['buyer_product_status'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                $sellerStatus = filter_var($item['seller_product_status'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                
                $sellerName = trim($item['seller_name'] ?? '');
                if (empty($sellerName)) {
                    $sellerName = 'Digiflazz';
                }
                
                $stmt->execute([
                    'sku' => $sku,
                    'name' => $itemName,
                    'category' => $category,
                    'sub_cat' => $subCat,
                    'brand' => $itemBrand,
                    'type' => $type,
                    'price' => $price,
                    'buyer_status' => $buyerStatus,
                    'seller_status' => $sellerStatus,
                    'desc' => $item['desc'] ?? '',
                    'start_cut' => $item['start_cut_off'] ?? '',
                    'end_cut' => $item['end_cut_off'] ?? '',
                    'seller_name' => $sellerName
                ]);
            }

            // Deactivate products of this type that are no longer returned in Digiflazz API payload (e.g. deleted sellers/SKUs)
            $syncedSkus = array_filter(array_map(function($item) {
                return trim($item['buyer_sku_code'] ?? $item['sku_code'] ?? $item['pasca_code'] ?? '');
            }, $productsData));

            if (!empty($syncedSkus)) {
                $stmtExisting = $this->db->prepare("SELECT buyer_sku_code FROM digi_products WHERE type = :type AND is_active = 1");
                $stmtExisting->execute(['type' => $type]);
                $existingSkus = $stmtExisting->fetchAll(PDO::FETCH_COLUMN);

                $missingSkus = array_diff($existingSkus, $syncedSkus);

                if (!empty($missingSkus)) {
                    $nowFunc = $isSqlite ? 'CURRENT_TIMESTAMP' : 'NOW()';
                    foreach (array_chunk(array_values($missingSkus), 300) as $chunk) {
                        $inClause = str_repeat('?,', count($chunk) - 1) . '?';
                        $params = array_merge([$type], $chunk);
                        $stmtDeactivate = $this->db->prepare("
                            UPDATE digi_products 
                            SET is_active = 0, buyer_product_status = 0, seller_product_status = 0, last_synced_at = $nowFunc 
                            WHERE type = ? AND buyer_sku_code IN ($inClause)
                        ");
                        $stmtDeactivate->execute($params);
                    }
                }
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

    private function normalizeCategory(string $apiCategory, string $brand = '', string $productName = '', string $type = 'prepaid') {
        $cat   = strtolower(trim($apiCategory));
        $brd   = strtolower(trim($brand));
        $pName = strtolower(trim($productName));
        $combined = $cat . ' ' . $brd . ' ' . $pName;

        // ─── If Postpaid / Pascabayar, match by combined context ───
        if ($type === 'postpaid' || strpos($cat, 'pasca') !== false || strpos($cat, 'postpaid') !== false) {
            if (strpos($combined, 'multifinance') !== false || strpos($combined, 'finance') !== false || strpos($combined, 'leasing') !== false || strpos($combined, 'cicilan') !== false || strpos($combined, 'angsuran') !== false || strpos($combined, 'kredit') !== false || strpos($combined, 'fif') !== false || strpos($combined, 'baf') !== false || strpos($combined, 'wom') !== false || strpos($combined, 'oto') !== false || strpos($combined, 'adira') !== false || strpos($combined, 'columbia') !== false || strpos($combined, 'bima') !== false || strpos($combined, 'smart') !== false || strpos($combined, 'suzuki') !== false || strpos($combined, 'nsc') !== false) return 'multifinance';
            if (strpos($combined, 'pln') !== false || strpos($combined, 'listrik') !== false || strpos($combined, 'nontaglis') !== false) return 'pln';
            if (strpos($combined, 'bpjs') !== false || strpos($combined, 'kesehatan') !== false || strpos($combined, 'ketenagakerjaan') !== false || strpos($combined, 'bpjstk') !== false) return 'bpjs';
            if (strpos($combined, 'pdam') !== false || strpos($combined, 'air') !== false || strpos($combined, 'aetra') !== false || strpos($combined, 'palyja') !== false || strpos($combined, 'tirta') !== false) return 'pdam';
            if (strpos($combined, 'internet') !== false || strpos($combined, 'indihome') !== false || strpos($combined, 'telkom') !== false || strpos($combined, 'speedy') !== false || strpos($combined, 'cbn') !== false || strpos($combined, 'first media') !== false || strpos($combined, 'biznet') !== false || strpos($combined, 'mnc') !== false || strpos($combined, 'transvision') !== false || strpos($combined, 'k-vision') !== false || strpos($combined, 'tv') !== false) return 'tv';
            if (strpos($combined, 'hp') !== false || strpos($combined, 'halo') !== false || strpos($combined, 'matrix') !== false || strpos($combined, 'prioritas') !== false || strpos($combined, 'kartu halo') !== false) return 'hp';
            if (strpos($combined, 'samsat') !== false || strpos($combined, 'pkb') !== false) return 'samsat';
            if (strpos($combined, 'pbb') !== false || strpos($combined, 'pajak') !== false) return 'pbb';
            if (strpos($combined, 'gas') !== false || strpos($combined, 'pgn') !== false || strpos($combined, 'pertagas') !== false) return 'gas';
        }

        // ─── Pascabayar-specific categories from category name ───
        if (strpos($cat, 'hp pascabayar') !== false || strpos($cat, 'hp pasca') !== false) return 'hp';
        if (strpos($cat, 'internet pascabayar') !== false || strpos($cat, 'indihome') !== false || strpos($cat, 'telkom') !== false) return 'tv';
        if (strpos($cat, 'pdam') !== false) return 'pdam';
        if (strpos($cat, 'pln') !== false || strpos($cat, 'nontaglis') !== false) return 'pln';
        if (strpos($cat, 'bpjs') !== false) return 'bpjs';
        if (strpos($cat, 'multifinance') !== false || strpos($cat, 'finance') !== false) return 'multifinance';
        if (strpos($cat, 'samsat') !== false) return 'samsat';
        if (strpos($cat, 'pbb') !== false) return 'pbb';
        if (strpos($cat, 'gas negara') !== false || strpos($cat, 'pgn') !== false) return 'gas';

        // ─── Prepaid / general categories ───
        if (strpos($cat, 'pulsa') !== false) return 'pulsa';
        if (strpos($cat, 'data') !== false) return 'data';
        if (strpos($cat, 'sms') !== false || strpos($cat, 'nelpon') !== false || strpos($cat, 'telpon') !== false) return 'sms_nelpon';
        if (strpos($cat, 'e-money') !== false || strpos($cat, 'ewallet') !== false || strpos($cat, 'e-wallet') !== false || strpos($cat, 'emoney') !== false || strpos($cat, 'uang elektronik') !== false || strpos($cat, 'wallet') !== false) return 'ewallet';
        if (strpos($cat, 'game') !== false) return 'game';
        if (strpos($cat, 'tv') !== false || strpos($cat, 'televisi') !== false) return 'tv';
        if (strpos($cat, 'voucher') !== false) return 'voucher';
        if (strpos($cat, 'bank') !== false || strpos($cat, 'transfer') !== false) return 'bank';
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
        $isSqlite = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if (!$isSqlite) {
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
            // For postpaid products without custom price, apply default/admin markup
            $this->db->exec("UPDATE digi_products SET sell_price = CEIL((seller_price + markup) / 100) * 100 WHERE type = 'postpaid' AND is_custom_price = 0");
            // For prepaid products without custom price, sell_price remains 0
            $this->db->exec("UPDATE digi_products SET sell_price = 0 WHERE type = 'prepaid' AND is_custom_price = 0");
        } else {
            try {
                $rules = $this->getMarkupRules();
                foreach ($rules as $r) {
                    $cat = addslashes($r['category']);
                    $type = $r['markup_type'];
                    $val = (float)$r['markup_value'];
                    if ($type === 'percentage') {
                        $this->db->exec("UPDATE digi_products SET markup = (seller_price * $val / 100), sell_price = (seller_price + (seller_price * $val / 100)) WHERE category = '$cat' AND type = 'postpaid' AND is_custom_price = 0");
                    } else {
                        $this->db->exec("UPDATE digi_products SET markup = $val, sell_price = (seller_price + $val) WHERE category = '$cat' AND type = 'postpaid' AND is_custom_price = 0");
                    }
                }
                $this->db->exec("UPDATE digi_products SET sell_price = (seller_price + 2000) WHERE (sell_price IS NULL OR sell_price = 0) AND type = 'postpaid' AND is_custom_price = 0");
                $this->db->exec("UPDATE digi_products SET sell_price = 0 WHERE type = 'prepaid' AND is_custom_price = 0");
            } catch (\Throwable $e) {
                $this->db->exec("UPDATE digi_products SET sell_price = (seller_price + 2000) WHERE type = 'postpaid' AND is_custom_price = 0");
                $this->db->exec("UPDATE digi_products SET sell_price = 0 WHERE type = 'prepaid' AND is_custom_price = 0");
            }
        }
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
        $stmt = $this->db->prepare("UPDATE digi_products SET is_custom_price = 0, sell_price = CASE WHEN type = 'prepaid' THEN 0 ELSE seller_price END WHERE buyer_sku_code = :sku");
        $stmt->execute(['sku' => $sku]);
        $this->applyAllMarkups(); // Re-apply to update the sell_price of this specific product
        return true;
    }

    /**
     * Get all markup rules
     */
    public function getMarkupRules() {
        try {
            $stmt = $this->db->query("SELECT * FROM digi_markup_rules ORDER BY category");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            $this->ensureTables();
            try {
                $stmt = $this->db->query("SELECT * FROM digi_markup_rules ORDER BY category");
                return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            } catch (\Throwable $e2) {
                return [];
            }
        }
    }

    /**
     * Save (upsert) a markup rule
     */
    public function saveMarkupRule(string $category, string $markupType, float $markupValue) {
        $isSqlite = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
        if ($isSqlite) {
            $stmt = $this->db->prepare("
                INSERT INTO digi_markup_rules (category, brand, markup_type, markup_value)
                VALUES (:cat, NULL, :type, :val)
                ON CONFLICT(category) DO UPDATE SET markup_type = excluded.markup_type, markup_value = excluded.markup_value
            ");
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO digi_markup_rules (category, brand, markup_type, markup_value)
                VALUES (:cat, NULL, :type, :val)
                ON DUPLICATE KEY UPDATE markup_type = :type, markup_value = :val
            ");
        }
        return $stmt->execute(['cat' => $category, 'type' => $markupType, 'val' => $markupValue]);
    }

    /**
     * Get unique categories by type
     */
    public function getCategories(string $type = 'prepaid') {
        $stmt = $this->db->prepare("
            SELECT DISTINCT category 
            FROM digi_products 
            WHERE type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1
            ORDER BY category ASC
        ");
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get unique brands by category and type
     */
    public function getBrands(string $category, string $type = 'prepaid') {
        $stmt = $this->db->prepare("
            SELECT DISTINCT brand 
            FROM digi_products 
            WHERE category = :cat AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1 AND brand IS NOT NULL AND brand != ''
            ORDER BY brand ASC
        ");
        $stmt->execute(['cat' => $category, 'type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get a single product by buyer_sku_code
     */
    public function getProductBySku(string $sku) {
        $stmt = $this->db->prepare("SELECT * FROM digi_products WHERE buyer_sku_code = ? LIMIT 1");
        $stmt->execute([$sku]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        $catLower = strtolower(trim($category));
        
        if ($catLower === 'ewallet' || $catLower === 'e-money' || $catLower === 'e-wallet' || $catLower === 'emoney') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('ewallet', 'e-money', 'e-wallet', 'emoney', 'uang elektronik') 
                           OR LOWER(brand) IN ('dana', 'gopay', 'ovo', 'shopeepay', 'linkaja', 'shopee pay', 'go pay', 'e-money', 'emoney', 'maxim', 'isaku', 'i-saku', 'sakuku', 'tapcash', 'brizzi', 'e-toll', 'etoll', 'grab', 'gojek')
                           OR LOWER(product_name) LIKE '%bebas nominal%')
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'voucher') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('voucher', 'aktivasi voucher')) 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'game' || $catLower === 'games') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('game', 'games')) 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'tv' || $catLower === 'televisi' || $catLower === 'internet') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('tv', 'televisi', 'internet', 'internet pascabayar', 'tv pascabayar')
                           OR LOWER(brand) IN ('tv', 'internet', 'telkom', 'indihome', 'biznet', 'cbn', 'first media', 'mnc', 'mnc play', 'transvision', 'k-vision') 
                           OR LOWER(product_name) LIKE '%indihome%' OR LOWER(product_name) LIKE '%telkom%' OR LOWER(product_name) LIKE '%internet%' OR LOWER(product_name) LIKE '%biznet%' OR LOWER(product_name) LIKE '%first media%' OR LOWER(product_name) LIKE '%cbn%') 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'sms_nelpon') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('sms_nelpon', 'paket sms & telpon', 'sms', 'telepon', 'paket telepon', 'sms & telpon')) 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'pdam') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('pdam') 
                           OR LOWER(brand) = 'pdam' 
                           OR LOWER(buyer_sku_code) LIKE 'pdam%'
                           OR LOWER(product_name) LIKE '%pdam%' OR LOWER(product_name) LIKE '%air%') 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'hp') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('hp', 'hp pascabayar', 'hp pasca') 
                           OR LOWER(brand) IN ('hp', 'halo', 'matrix', 'xl prioritas', 'smartfren') 
                           OR LOWER(buyer_sku_code) IN ('kartuhalo', 'matrix', 'xlprioritas', 'smartfrenpasca')
                           OR LOWER(product_name) LIKE '%pasca%') 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'bpjs') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('bpjs', 'bpjs kesehatan', 'bpjs ketenagakerjaan', 'bpjstk')
                           OR LOWER(category) LIKE '%bpjs%' 
                           OR LOWER(brand) LIKE '%bpjs%' 
                           OR LOWER(buyer_sku_code) LIKE 'bpjs%'
                           OR LOWER(product_name) LIKE '%bpjs%') 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'multifinance') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('multifinance', 'finance', 'cicilan', 'angsuran') 
                           OR LOWER(brand) = 'multifinance' 
                           OR LOWER(product_name) LIKE '%finance%' OR LOWER(product_name) LIKE '%credit%' OR LOWER(product_name) LIKE '%angsuran%'
                           OR LOWER(buyer_sku_code) LIKE 'fn%' OR LOWER(buyer_sku_code) LIKE 'afn%') 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'pln') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('pln', 'pln pascabayar', 'pln nontaglis') 
                           OR LOWER(brand) = 'pln' 
                           OR LOWER(buyer_sku_code) IN ('pln', 'plnnontaglis', 'plnpascatagihan')
                           OR LOWER(product_name) LIKE '%pln%') 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'samsat') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('samsat', 'pkb') 
                           OR LOWER(brand) LIKE '%samsat%' 
                           OR LOWER(buyer_sku_code) LIKE 'samsat%'
                           OR LOWER(product_name) LIKE '%samsat%' OR LOWER(product_name) LIKE '%pajak kendaraan%') 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'pbb') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('pbb', 'pajak') 
                           OR LOWER(brand) LIKE '%pbb%' 
                           OR LOWER(buyer_sku_code) LIKE 'pbb%' OR LOWER(buyer_sku_code) = 'cimahi'
                           OR LOWER(product_name) LIKE '%pbb%' OR LOWER(product_name) LIKE '%pajak bumi%') 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } elseif ($catLower === 'gas') {
            $sql = "SELECT * FROM digi_products 
                    WHERE (LOWER(category) IN ('gas', 'gas negara') 
                           OR LOWER(brand) IN ('gas', 'pgn', 'pertagas') 
                           OR LOWER(buyer_sku_code) IN ('pgas', 'pertagas')
                           OR LOWER(product_name) LIKE '%gas%') 
                      AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['type' => $type];
        } else {
            $sql = "SELECT * FROM digi_products 
                    WHERE category = :cat AND type = :type AND is_active = 1 AND buyer_product_status = 1 AND seller_product_status = 1";
            $params = ['cat' => $category, 'type' => $type];
        }
        
        if ($brand) {
            $sql .= " AND brand = :brand";
            $params['brand'] = $brand;
        }
        
        if ($type === 'postpaid') {
            $sql .= " ORDER BY product_name ASC";
        } else {
            $sql .= " ORDER BY sell_price ASC, seller_price ASC";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback empty seller name to Digiflazz and deduplicate by buyer_sku_code
        $unique = [];
        $result = [];
        foreach ($products as $p) {
            if (empty(trim($p['seller_name'] ?? ''))) {
                $p['seller_name'] = 'Digiflazz';
            }
            
            $sku = trim($p['buyer_sku_code'] ?? '');
            if ($sku !== '' && !isset($unique[$sku])) {
                $unique[$sku] = true;
                $result[] = $p;
            }
        }
        return $result;
    }

    /**
     * Create a new transaction log
     */
    public function createTransaction(array $data) {
        $sellPrice = floatval($data['sell_price'] ?? 0);
        $modalPrice = floatval($data['modal_price'] ?? 0);

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
            'sell_price' => $sellPrice,
            'modal_price' => $modalPrice,
            'profit' => ($sellPrice > 0) ? ($sellPrice - $modalPrice) : 0,
            'status' => $data['status'] ?? 'pending',
            'user_id' => $data['user_id'] ?? null,
            'message' => $data['message'] ?? '',
            'raw_response' => isset($data['raw_response']) ? json_encode($data['raw_response']) : null,
            'seller_name' => $sellerName
        ]);
    }

    /**
     * Update transaction selling price and recalculate profit
     */
    public function updateTransactionSellPrice(string $refId, float $sellPrice): bool {
        $sellPrice = max(0, $sellPrice);
        $stmt = $this->db->prepare("
            UPDATE digi_transactions 
            SET sell_price = :sell_price, 
                profit = CASE WHEN :sell_price_check > 0 THEN :sell_price_calc - modal_price ELSE 0 END,
                updated_at = NOW() 
            WHERE ref_id = :ref_id
        ");
        return $stmt->execute([
            'sell_price' => $sellPrice,
            'sell_price_check' => $sellPrice,
            'sell_price_calc' => $sellPrice,
            'ref_id' => $refId
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
                SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN LOWER(status) IN ('pending', 'processing') THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN LOWER(status) IN ('failed', 'gagal') THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN sell_price ELSE 0 END) as total_revenue,
                SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN modal_price ELSE 0 END) as total_cost,
                SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') AND sell_price > 0 THEN profit ELSE 0 END) as total_profit,
                AVG(CASE WHEN LOWER(status) IN ('success', 'sukses', 'failed', 'gagal') 
                             AND TIMESTAMPDIFF(SECOND, created_at, updated_at) BETWEEN 0 AND 900
                        THEN TIMESTAMPDIFF(SECOND, created_at, updated_at) 
                        ELSE NULL END) as avg_speed
            FROM digi_transactions
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['avg_speed'] = $row['avg_speed'] !== null ? round((float)$row['avg_speed'], 1) : 0;
        }
        return $row;
    }

    /**
     * Get transaction data for Analytics Dashboard
     */
    public function getAnalyticsData(string $startDate, string $endDate) {
        $sql = "SELECT 
                    t.id, t.ref_id, t.digiflazz_trx_id, t.buyer_sku_code, t.category, t.product_name, t.type, t.customer_no, t.customer_name,
                    t.sell_price, t.modal_price, t.status, t.raw_response, t.created_at, t.updated_at,
                    t.seller_name AS trx_seller_name,
                    p.seller_name AS prod_seller_name,
                    p.brand
                FROM digi_transactions t
                LEFT JOIN digi_products p ON t.buyer_sku_code = p.buyer_sku_code
                WHERE t.created_at >= :start_date AND t.created_at <= :end_date
                ORDER BY t.created_at DESC";
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
        try {
            $stmt = $this->db->prepare("SELECT * FROM digi_deposits ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->ensureTables();
            try {
                $stmt = $this->db->prepare("SELECT * FROM digi_deposits ORDER BY created_at DESC LIMIT :limit");
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Throwable $e2) {
                return [];
            }
        }
    }

    /**
     * Get pending deposits
     */
    public function getPendingDeposits() {
        try {
            $stmt = $this->db->query("SELECT * FROM digi_deposits WHERE status = 'pending' ORDER BY created_at ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            $this->ensureTables();
            try {
                $stmt = $this->db->query("SELECT * FROM digi_deposits WHERE status = 'pending' ORDER BY created_at ASC");
                return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            } catch (\Throwable $e2) {
                return [];
            }
        }
    }

    /**
     * Update deposit status by record ID
     *
     * @param int|string $id
     * @param string $status
     * @param string $notes
     * @param array|string|null $rawResponse
     * @return bool
     */
    public function updateDepositStatusById(int|string $id, string $status, string $notes, array|string|null $rawResponse = null): bool {
        $sql = "UPDATE digi_deposits SET status = :status, notes = :notes, updated_at = NOW()";
        $params = [
            'status' => $status,
            'notes' => $notes,
            'id' => $id
        ];
        if ($rawResponse !== null) {
            $sql .= ", raw_response = :raw";
            $params['raw'] = is_array($rawResponse) ? json_encode($rawResponse) : $rawResponse;
        }
        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return (bool)$stmt->execute($params);
    }

    /**
     * Update deposit status by matching amount or notes
     *
     * @param float|int $amount
     * @param string $status
     * @param string $notes
     * @param array|string|null $rawResponse
     * @return bool
     */
    public function updateDepositStatus(float|int $amount, string $status, string $notes, array|string|null $rawResponse = null): bool {
        $pending = $this->getPendingDeposits();
        if (empty($pending)) return false;

        $targetId = null;
        $amountFloat = (float)$amount;

        foreach ($pending as $row) {
            $rowAmount = (float)$row['amount'];
            
            // 1. Check exact match with row amount
            if (abs($rowAmount - $amountFloat) < 1.0) {
                $targetId = $row['id'];
                break;
            }

            // 2. Parse raw response for unique amount or base amount
            if (!empty($row['raw_response'])) {
                $raw = json_decode($row['raw_response'], true);
                if (is_array($raw)) {
                    $rawAmt = (float)($raw['amount'] ?? $raw['deposit']['amount'] ?? 0);
                    if ($rawAmt > 0 && abs($rawAmt - $amountFloat) < 1.0) {
                        $targetId = $row['id'];
                        break;
                    }
                }
            }

            // 3. Parse notes for amount regex
            if (!empty($row['notes'])) {
                if (preg_match('/Rp\s*([0-9.,]+)/i', $row['notes'], $m)) {
                    $parsedRp = (float)str_replace(['.', ','], '', $m[1]);
                    if ($parsedRp > 0 && abs($parsedRp - $amountFloat) < 1.0) {
                        $targetId = $row['id'];
                        break;
                    }
                }
            }
        }

        // Fallback: If only 1 pending deposit exists and no exact amount match, match that 1 deposit
        if (!$targetId && count($pending) === 1) {
            $targetId = $pending[0]['id'];
        }

        if ($targetId) {
            return $this->updateDepositStatusById($targetId, $status, $notes, $rawResponse);
        }

        return false;
    }


    public function getSellerSuccessRates() {
        try {
            $stmt = $this->db->query("
                SELECT seller_name,
                       COUNT(id) as total,
                       SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN 1 ELSE 0 END) as success,
                        AVG(CASE WHEN LOWER(status) IN ('success', 'sukses', 'failed', 'gagal') 
                                      AND TIMESTAMPDIFF(SECOND, created_at, updated_at) BETWEEN 0 AND 900
                                 THEN TIMESTAMPDIFF(SECOND, created_at, updated_at) 
                                 ELSE NULL END) as avg_speed
                FROM digi_transactions
                WHERE seller_name IS NOT NULL AND seller_name != '' 
                  AND LOWER(status) IN ('success', 'failed', 'sukses', 'gagal')
                GROUP BY seller_name
            ");
            $rates = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rates[trim($row['seller_name'])] = [
                    'total' => (int)$row['total'],
                    'success' => (int)$row['success'],
                    'avg_speed' => $row['avg_speed'] !== null ? round((float)$row['avg_speed'], 1) : null
                ];
            }
            return $rates;
        } catch (\Exception $e) {
            error_log("[DigiflazzModel] getSellerSuccessRates error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get avg speed per SKU+Seller combination
     * Returns array keyed by "sku|seller_name"
     */
    public function getSkuSellerSpeedRates() {
        try {
            $stmt = $this->db->query("
                SELECT buyer_sku_code, seller_name,
                       COUNT(id) as total,
                       SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN 1 ELSE 0 END) as success,
                       AVG(CASE WHEN LOWER(status) IN ('success', 'sukses', 'failed', 'gagal')
                                    AND TIMESTAMPDIFF(SECOND, created_at, updated_at) BETWEEN 0 AND 900
                               THEN TIMESTAMPDIFF(SECOND, created_at, updated_at)
                               ELSE NULL END) as avg_speed
                FROM digi_transactions
                WHERE buyer_sku_code IS NOT NULL AND buyer_sku_code != ''
                  AND seller_name IS NOT NULL AND seller_name != ''
                  AND LOWER(status) IN ('success', 'failed', 'sukses', 'gagal')
                GROUP BY buyer_sku_code, seller_name
            ");
            $rates = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $key = trim($row['buyer_sku_code']) . '|' . trim($row['seller_name']);
                $rates[$key] = [
                    'total'     => (int)$row['total'],
                    'success'   => (int)$row['success'],
                    'avg_speed' => $row['avg_speed'] !== null ? round((float)$row['avg_speed'], 1) : null
                ];
            }
            return $rates;
        } catch (\Exception $e) {
            error_log("[DigiflazzModel] getSkuSellerSpeedRates error: " . $e->getMessage());
            return [];
        }
    }


    public function getProductSuccessRates() {
        try {
            $stmt = $this->db->query("
                SELECT buyer_sku_code,
                       COUNT(id) as total,
                       SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN 1 ELSE 0 END) as success,
                       AVG(CASE WHEN LOWER(status) IN ('success', 'sukses', 'failed', 'gagal') 
                                    AND TIMESTAMPDIFF(SECOND, created_at, updated_at) BETWEEN 0 AND 900
                               THEN TIMESTAMPDIFF(SECOND, created_at, updated_at) 
                               ELSE NULL END) as avg_speed
                FROM digi_transactions
                WHERE buyer_sku_code IS NOT NULL AND buyer_sku_code != '' 
                  AND LOWER(status) IN ('success', 'failed', 'sukses', 'gagal')
                GROUP BY buyer_sku_code
            ");
            $rates = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rates[trim($row['buyer_sku_code'])] = [
                    'total' => (int)$row['total'],
                    'success' => (int)$row['success'],
                    'avg_speed' => $row['avg_speed'] !== null ? round((float)$row['avg_speed'], 1) : null
                ];
            }
            return $rates;
        } catch (\Exception $e) {
            error_log("[DigiflazzModel] getProductSuccessRates error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent transactions for a specific seller
     */
    public function getSellerHistory(string $sellerName, int $page = 1, int $limit = 10) {
        $stmtStat = $this->db->prepare("
            SELECT category, status, COUNT(*) as count
            FROM digi_transactions
            WHERE seller_name = :seller AND status IN ('success', 'failed')
            GROUP BY category, status
        ");
        $stmtStat->execute(['seller' => $sellerName]);
        $rows = $stmtStat->fetchAll(PDO::FETCH_ASSOC);

        $totalTrx = 0;
        $totalSuccess = 0;
        $totalFailed = 0;
        $categories = [];

        foreach ($rows as $row) {
            $cat = strtoupper(str_replace('_', ' ', $row['category']));
            $count = (int)$row['count'];
            $totalTrx += $count;
            if ($row['status'] === 'success') $totalSuccess += $count;
            if ($row['status'] === 'failed') $totalFailed += $count;
            
            if (!isset($categories[$cat])) $categories[$cat] = 0;
            $categories[$cat] += $count;
        }

        // Sort categories by highest count
        arsort($categories);

        // Pagination
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("
            SELECT ref_id, digiflazz_trx_id, customer_no, customer_name, status, created_at, updated_at, product_name, seller_name, message, modal_price, sell_price, profit, category, raw_response
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
        
        foreach ($data as &$row) {
            $created = !empty($row['created_at']) ? strtotime($row['created_at']) : 0;
            $updated = !empty($row['updated_at']) ? strtotime($row['updated_at']) : 0;
            $diff = $updated - $created;
            $row['duration_seconds'] = ($diff >= 0 && $created > 0 && $updated > 0) ? $diff : null;

            if (empty($row['digiflazz_trx_id']) || $row['digiflazz_trx_id'] === $row['ref_id']) {
                if (!empty($row['raw_response'])) {
                    $raw = json_decode($row['raw_response'], true);
                    if (!empty($raw['tr_id'])) $row['digiflazz_trx_id'] = (string)$raw['tr_id'];
                    elseif (!empty($raw['trx_id'])) $row['digiflazz_trx_id'] = (string)$raw['trx_id'];
                    else $row['digiflazz_trx_id'] = null;
                } else {
                    $row['digiflazz_trx_id'] = null;
                }
            }
        }

        // Calculate average seller speed across all completed transactions
        $stmtSpeed = $this->db->prepare("
            SELECT AVG(CASE WHEN LOWER(status) IN ('success', 'sukses', 'failed', 'gagal') 
                                AND TIMESTAMPDIFF(SECOND, created_at, updated_at) BETWEEN 0 AND 900
                           THEN TIMESTAMPDIFF(SECOND, created_at, updated_at) 
                           ELSE NULL END) as avg_speed
            FROM digi_transactions
            WHERE seller_name = :seller
        ");
        $stmtSpeed->execute(['seller' => $sellerName]);
        $avgSpdRow = $stmtSpeed->fetch(PDO::FETCH_ASSOC);
        $avgSpeed = ($avgSpdRow && $avgSpdRow['avg_speed'] !== null) ? round((float)$avgSpdRow['avg_speed'], 1) : null;

        // Count total for pagination
        $stmtTotal = $this->db->prepare("SELECT COUNT(*) FROM digi_transactions WHERE seller_name = :seller");
        $stmtTotal->execute(['seller' => $sellerName]);
        $totalRecords = $stmtTotal->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);

        // Standard category definitions for Radar Chart
        $standardCats = ['PLN', 'Pulsa', 'Paket Data', 'E-Wallet', 'Games', 'TV', 'SMS & Telp'];
        $categoryMetrics = [];
        foreach ($standardCats as $sc) {
            $categoryMetrics[$sc] = [
                'name' => $sc,
                'total_trx' => 0,
                'success_trx' => 0,
                'failed_trx' => 0,
                'avg_speed' => null,
                'speed_text' => '-',
                'speed_score' => 0,
                'success_rate' => 0
            ];
        }

        // Detailed category query for speed & volume per product category
        $stmtCatDetail = $this->db->prepare("
            SELECT 
                category,
                COUNT(*) as total_trx,
                SUM(CASE WHEN LOWER(status) IN ('success', 'sukses') THEN 1 ELSE 0 END) as success_trx,
                SUM(CASE WHEN LOWER(status) IN ('failed', 'gagal') THEN 1 ELSE 0 END) as failed_trx,
                AVG(CASE WHEN LOWER(status) IN ('success', 'sukses', 'failed', 'gagal') 
                             AND TIMESTAMPDIFF(SECOND, created_at, updated_at) BETWEEN 0 AND 900
                        THEN TIMESTAMPDIFF(SECOND, created_at, updated_at) 
                        ELSE NULL END) as avg_speed
            FROM digi_transactions
            WHERE seller_name = :seller
            GROUP BY category
        ");
        $stmtCatDetail->execute(['seller' => $sellerName]);
        $catDetailRows = $stmtCatDetail->fetchAll(PDO::FETCH_ASSOC);

        foreach ($catDetailRows as $cdr) {
            $rawCat = strtoupper(trim(str_replace('_', ' ', $cdr['category'] ?? '')));
            
            // Map raw category to standard category
            $stdKey = 'Lainnya';
            if (strpos($rawCat, 'PLN') !== false || strpos($rawCat, 'LISTRIK') !== false) {
                $stdKey = 'PLN';
            } elseif (strpos($rawCat, 'PULSA') !== false) {
                $stdKey = 'Pulsa';
            } elseif (strpos($rawCat, 'DATA') !== false || strpos($rawCat, 'INTERNET') !== false || strpos($rawCat, 'PAKET') !== false) {
                $stdKey = 'Paket Data';
            } elseif (strpos($rawCat, 'WALLET') !== false || strpos($rawCat, 'MONEY') !== false || strpos($rawCat, 'E-') !== false || strpos($rawCat, 'E ') !== false) {
                $stdKey = 'E-Wallet';
            } elseif (strpos($rawCat, 'GAME') !== false) {
                $stdKey = 'Games';
            } elseif (strpos($rawCat, 'TV') !== false) {
                $stdKey = 'TV';
            } elseif (strpos($rawCat, 'SMS') !== false || strpos($rawCat, 'TELP') !== false || strpos($rawCat, 'NELEPON') !== false) {
                $stdKey = 'SMS & Telp';
            } else {
                $stdKey = !empty($rawCat) ? ucfirst(strtolower($rawCat)) : 'Lainnya';
            }

            if (!isset($categoryMetrics[$stdKey])) {
                $categoryMetrics[$stdKey] = [
                    'name' => $stdKey,
                    'total_trx' => 0,
                    'success_trx' => 0,
                    'failed_trx' => 0,
                    'avg_speed' => null,
                    'speed_text' => '-',
                    'speed_score' => 0,
                    'success_rate' => 0
                ];
            }

            $tTrx = (int)$cdr['total_trx'];
            $sTrx = (int)$cdr['success_trx'];
            $fTrx = (int)$cdr['failed_trx'];
            $spVal = ($cdr['avg_speed'] !== null) ? (float)$cdr['avg_speed'] : null;

            $categoryMetrics[$stdKey]['total_trx'] += $tTrx;
            $categoryMetrics[$stdKey]['success_trx'] += $sTrx;
            $categoryMetrics[$stdKey]['failed_trx'] += $fTrx;

            if ($spVal !== null) {
                // If multiple raw cats map to same stdKey, keep weighted average or simpler average
                if ($categoryMetrics[$stdKey]['avg_speed'] === null) {
                    $categoryMetrics[$stdKey]['avg_speed'] = $spVal;
                } else {
                    $categoryMetrics[$stdKey]['avg_speed'] = ($categoryMetrics[$stdKey]['avg_speed'] + $spVal) / 2;
                }
            }
        }

        // Format speed text, score, and success rate for category metrics
        foreach ($categoryMetrics as $key => &$m) {
            $tot = $m['total_trx'];
            $m['success_rate'] = $tot > 0 ? round(($m['success_trx'] / $tot) * 100) : 0;
            if ($m['avg_speed'] !== null) {
                $sp = round($m['avg_speed']);
                $m['speed_text'] = $sp > 900 ? '>15m' : ($sp <= 59 ? "{$sp} dtk" : floor($sp / 60) . "m " . ($sp % 60) . "d");
                
                // Speed score mapping (0-100) for Radar Chart visualization
                if ($sp <= 5) $m['speed_score'] = 100;
                elseif ($sp <= 15) $m['speed_score'] = 90;
                elseif ($sp <= 30) $m['speed_score'] = 75;
                elseif ($sp <= 60) $m['speed_score'] = 60;
                elseif ($sp <= 180) $m['speed_score'] = 45;
                else $m['speed_score'] = 25;
            } else {
                // No timing data available
                if ($tot > 0) {
                    // Has transactions but no speed data — derive partial score from success_rate
                    // Max 55 (below "good speed" threshold) to signal data gap to user
                    $m['speed_score'] = (int) round($m['success_rate'] * 0.55);
                    $m['speed_text']  = 'Data N/A';
                } else {
                    $m['speed_text']  = '-';
                    $m['speed_score'] = 0;
                }
            }
        }
        unset($m);

        // Compute Strength & Weakness Analysis
        $strengths = [];
        $weaknesses = [];

        // Overall SR Analysis
        $overallSr = $totalTrx > 0 ? round(($totalSuccess / $totalTrx) * 100) : 0;
        if ($totalTrx >= 5 && $overallSr >= 95) {
            $strengths[] = "Success Rate Keseluruhan Sangat Tinggi ({$overallSr}%)";
        } elseif ($totalTrx >= 5 && $overallSr < 80) {
            $weaknesses[] = "Tingkat Keberhasilan Global Perlu Perhatian ({$overallSr}%)";
        }

        // Category specific speed & volume strengths/weaknesses
        foreach ($categoryMetrics as $catName => $cm) {
            if ($cm['total_trx'] > 0) {
                // Strength by speed
                if ($cm['avg_speed'] !== null && $cm['avg_speed'] <= 15) {
                    $strengths[] = "Sangat Cepat pada transaksi {$catName} (Rata-rata {$cm['speed_text']}, {$cm['total_trx']} Trx)";
                }
                // Strength by volume
                if ($cm['total_trx'] >= 20 && $cm['success_rate'] >= 90) {
                    $strengths[] = "Volume & Keandalan tinggi pada {$catName} ({$cm['total_trx']} Trx, SR {$cm['success_rate']}%)";
                }
                // Weakness by speed
                if ($cm['avg_speed'] !== null && $cm['avg_speed'] > 45) {
                    $weaknesses[] = "Respon lebih lambat pada {$catName} (Rata-rata {$cm['speed_text']})";
                }
                // Weakness by failure rate
                if ($cm['total_trx'] >= 3 && $cm['success_rate'] < 80) {
                    $weaknesses[] = "Tingkat Gagal Cukup Tinggi pada {$catName} (Gagal {$cm['failed_trx']} dari {$cm['total_trx']} Trx)";
                }
            } else {
                $weaknesses[] = "Belum memiliki riwayat transaksi untuk kategori {$catName}";
            }
        }

        // Fallback default messages if empty
        if (empty($strengths)) {
            if ($avgSpeed !== null && $avgSpeed <= 20) {
                $strengths[] = "Kecepatan rata-rata respon seller tergolong cepat ({$avgSpeed} dtk)";
            } else {
                $strengths[] = "Seller aktif memproses pesanan PPOB";
            }
        }

        if (empty($weaknesses)) {
            $weaknesses[] = "Tidak ditemukan kendala signifikan pada riwayat seller ini";
        }

        // Limit strengths and weaknesses to max top 4 items each for clean UI layout
        $strengths = array_slice($strengths, 0, 4);
        $weaknesses = array_slice($weaknesses, 0, 4);

        return [
            'analytics' => [
                'total' => $totalTrx,
                'success' => $totalSuccess,
                'failed' => $totalFailed,
                'avg_speed' => $avgSpeed,
                'categories' => $categories,
                'category_metrics' => $categoryMetrics,
                'strengths' => $strengths,
                'weaknesses' => $weaknesses
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

