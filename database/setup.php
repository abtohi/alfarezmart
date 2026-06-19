<?php
/**
 * Database Setup - Create all tables and seed data
 * 
 * Compatible with MySQL (Production: 153.92.15.83).
 * Run via: GET /setup (development only)
 * Or via CLI: php database/setup.php
 */

// Allow CLI execution
if (php_sapi_name() === 'cli') {
    define('BASE_PATH', dirname(__DIR__));
    define('APP_PATH', BASE_PATH . '/app');
    define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');
    require_once APP_PATH . '/core/Autoloader.php';
    Autoloader::register();
    require_once APP_PATH . '/config/App.php';
}

function setupDatabase()
{
    $db = Database::getInstance()->getConnection();
    $messages = [];

    try {
        // ========== CREATE TABLES (MySQL Syntax) ==========
        // Note: MySQL auto-commits DDL, so tables are created outside transaction

        $db->exec("CREATE TABLE IF NOT EXISTS brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            slug VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: brands";

        $db->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            slug VARCHAR(100),
            icon VARCHAR(50),
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: categories";

        $db->exec("CREATE TABLE IF NOT EXISTS units (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            abbreviation VARCHAR(10),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: units";

        $db->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) UNIQUE,
            brand_id INT,
            category_id INT,
            product_type VARCHAR(100),
            variant VARCHAR(100),
            full_name VARCHAR(255) NOT NULL,
            short_label VARCHAR(50),
            invoice_name VARCHAR(100),
            supplier_product_code VARCHAR(100) NULL,
            supplier_invoice_name VARCHAR(255) NULL,
            weight_value DECIMAL(10,2),
            weight_unit VARCHAR(10),
            description TEXT,
            image_path VARCHAR(255),
            min_stock INT DEFAULT 0,
            max_stock INT,
            is_active TINYINT(1) DEFAULT 1,
            is_multivariant TINYINT(1) DEFAULT 0,
            is_custom_label TINYINT(1) DEFAULT 0,
            ref_product_id INT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: products";

        $db->exec("CREATE TABLE IF NOT EXISTS product_packagings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            level INT NOT NULL,
            unit_id INT NOT NULL,
            contained_qty INT DEFAULT 1,
            base_qty INT DEFAULT 1,
            barcode VARCHAR(50),
            buy_price DECIMAL(12,2) DEFAULT 0,
            sell_price_retail DECIMAL(12,2) DEFAULT 0,
            margin_retail DECIMAL(5,4) DEFAULT 0,
            sell_price_wholesale DECIMAL(12,2) DEFAULT 0,
            margin_wholesale DECIMAL(5,4) DEFAULT 0,
            is_default_scan TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: product_packagings";

        $db->exec("CREATE TABLE IF NOT EXISTS product_qty_prices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            packaging_id INT NOT NULL,
            min_qty DECIMAL(10,2) NOT NULL DEFAULT 1,
            unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
            sale_mode VARCHAR(10) NOT NULL DEFAULT 'both',
            label VARCHAR(100) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE CASCADE,
            INDEX idx_pqp_packaging (packaging_id),
            INDEX idx_pqp_min_qty (packaging_id, min_qty)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: product_qty_prices";

        $db->exec("CREATE TABLE IF NOT EXISTS supplier_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: supplier_types";

        $db->exec("CREATE TABLE IF NOT EXISTS suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            type_id INT,
            address TEXT,
            products_sold TEXT,
            is_consignment TINYINT(1) DEFAULT 0,
            notes TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (type_id) REFERENCES supplier_types(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: suppliers";

        $db->exec("CREATE TABLE IF NOT EXISTS sales_reps (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            sales_type VARCHAR(50),
            visit_day VARCHAR(50),
            delivery_day VARCHAR(50),
            visit_period VARCHAR(50),
            status VARCHAR(20) DEFAULT 'Aktif',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: sales_reps";

        $db->exec("CREATE TABLE IF NOT EXISTS purchases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            purchase_code VARCHAR(30) UNIQUE,
            supplier_id INT,
            sales_rep_id INT,
            purchase_date DATE NOT NULL,
            total_amount DECIMAL(15,2) DEFAULT 0,
            total_items INT DEFAULT 0,
            ppn_amount DECIMAL(12,2) DEFAULT 0,
            discount_amount DECIMAL(12,2) DEFAULT 0,
            shipping_cost DECIMAL(12,2) DEFAULT 0,
            grand_total DECIMAL(15,2) DEFAULT 0,
            payment_status VARCHAR(20) DEFAULT 'Lunas',
            invoice_photo VARCHAR(255),
            notes TEXT,
            synced_to_finance TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
            FOREIGN KEY (sales_rep_id) REFERENCES sales_reps(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: purchases";

        $db->exec("CREATE TABLE IF NOT EXISTS purchase_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            purchase_id INT NOT NULL,
            product_id INT NOT NULL,
            packaging_id INT NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            buy_price DECIMAL(12,2) NOT NULL,
            ppn_percent DECIMAL(5,2) DEFAULT 0,
            discount_percent DECIMAL(5,2) DEFAULT 0,
            discount_amount DECIMAL(12,2) DEFAULT 0,
            nett_price DECIMAL(12,2),
            total_price DECIMAL(15,2),
            expiry_date DATE,
            sell_price_retail DECIMAL(12,2),
            sell_price_wholesale DECIMAL(12,2),
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
            FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: purchase_items";

        $db->exec("CREATE TABLE IF NOT EXISTS stock (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL UNIQUE,
            current_qty_base INT DEFAULT 0,
            last_restock_date DATE,
            last_restock_qty INT,
            nearest_expiry DATE,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: stock";

        $db->exec("CREATE TABLE IF NOT EXISTS stock_movements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            movement_type VARCHAR(20) NOT NULL,
            quantity INT NOT NULL,
            reference_type VARCHAR(20),
            reference_id INT,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: stock_movements";

        $db->exec("CREATE TABLE IF NOT EXISTS customer_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            price_tier VARCHAR(20) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: customer_types";

        $db->exec("CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            type_id INT,
            notes TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (type_id) REFERENCES customer_types(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: customers";

        // Ensure notes column exists if table was already created
        try {
            $db->exec("ALTER TABLE customers ADD COLUMN notes TEXT NULL");
        } catch (Exception $e) {
            // Column might already exist
        }

        $db->exec("CREATE TABLE IF NOT EXISTS sale_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_number VARCHAR(30) UNIQUE,
            customer_id INT,
            sale_mode VARCHAR(10) NOT NULL,
            total_amount DECIMAL(15,2) DEFAULT 0,
            payment_method VARCHAR(20) DEFAULT 'Cash',
            payment_status VARCHAR(20) DEFAULT 'Lunas',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: sale_transactions";

        $db->exec("CREATE TABLE IF NOT EXISTS sale_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_id INT NOT NULL,
            product_id INT NOT NULL,
            packaging_id INT NOT NULL,
            custom_name VARCHAR(255) NULL,
            custom_unit VARCHAR(50) NULL,
            quantity DECIMAL(10,2) NOT NULL,
            unit_price DECIMAL(12,2) NOT NULL,
            total_price DECIMAL(15,2) NOT NULL,
            profit DECIMAL(12,2) DEFAULT 0,
            FOREIGN KEY (transaction_id) REFERENCES sale_transactions(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
            FOREIGN KEY (packaging_id) REFERENCES product_packagings(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: sale_items";

        $db->exec("CREATE TABLE IF NOT EXISTS consignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NOT NULL,
            consignment_date DATE NOT NULL,
            check_period VARCHAR(50),
            next_check_date DATE,
            payment_status VARCHAR(20) DEFAULT 'Belum Lunas',
            total_cost DECIMAL(15,2) DEFAULT 0,
            total_sold DECIMAL(15,2) DEFAULT 0,
            total_returned INT DEFAULT 0,
            payment_amount DECIMAL(15,2) DEFAULT 0,
            payment_date DATE,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: consignments";

        $db->exec("CREATE TABLE IF NOT EXISTS consignment_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            consignment_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            cost_price DECIMAL(12,2) NOT NULL,
            sell_price DECIMAL(12,2) NOT NULL,
            qty_sold INT DEFAULT 0,
            qty_returned INT DEFAULT 0,
            status VARCHAR(20) DEFAULT 'Aktif',
            FOREIGN KEY (consignment_id) REFERENCES consignments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: consignment_items";

        $db->exec("CREATE TABLE IF NOT EXISTS finance_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            log_date DATE NOT NULL,
            period_yyyymm VARCHAR(6),
            amount DECIMAL(15,2) NOT NULL,
            balance_type VARCHAR(30),
            category VARCHAR(20) NOT NULL,
            detail VARCHAR(100),
            description TEXT,
            reference_type VARCHAR(20),
            reference_id INT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: finance_logs";

        $db->exec("CREATE TABLE IF NOT EXISTS debts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            customer_name VARCHAR(100),
            phone VARCHAR(20),
            amount DECIMAL(15,2) NOT NULL,
            description TEXT,
            status VARCHAR(20) DEFAULT 'Belum Lunas',
            due_date DATE,
            paid_date DATE,
            paid_amount DECIMAL(15,2) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: debts";

        // Customer Debts (Piutang Pelanggan)
        $db->exec("CREATE TABLE IF NOT EXISTS customer_debts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NULL,
            customer_name_fallback VARCHAR(255) NULL,
            amount DECIMAL(15,2) NOT NULL,
            remaining_amount DECIMAL(15,2) NOT NULL,
            debt_date DATE NOT NULL,
            due_date DATE NULL,
            status ENUM('belum_lunas', 'lunas') DEFAULT 'belum_lunas',
            notes TEXT NULL,
            sale_id INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
            FOREIGN KEY (sale_id) REFERENCES sale_transactions(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: customer_debts";

        // Customer Debt Payments (Cicilan Piutang Pelanggan)
        $db->exec("CREATE TABLE IF NOT EXISTS customer_debt_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            debt_id INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            payment_date DATE NOT NULL,
            notes TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (debt_id) REFERENCES customer_debts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: customer_debt_payments";

        // Shop Debts (Hutang Toko)
        $db->exec("CREATE TABLE IF NOT EXISTS shop_debts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NULL,
            supplier_name_fallback VARCHAR(255) NULL,
            amount DECIMAL(15,2) NOT NULL,
            remaining_amount DECIMAL(15,2) NOT NULL,
            debt_date DATE NOT NULL,
            due_date DATE NULL,
            status ENUM('belum_lunas', 'lunas') DEFAULT 'belum_lunas',
            notes TEXT NULL,
            purchase_id INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
            FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: shop_debts";

        // Shop Debt Payments (Cicilan Hutang Toko)
        $db->exec("CREATE TABLE IF NOT EXISTS shop_debt_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            debt_id INT NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            payment_date DATE NOT NULL,
            notes TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (debt_id) REFERENCES shop_debts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: shop_debt_payments";

        $db->exec("CREATE TABLE IF NOT EXISTS app_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_type VARCHAR(20) DEFAULT 'string',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: app_settings";

        $db->exec("CREATE TABLE IF NOT EXISTS supplier_products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NOT NULL,
            product_id INT NOT NULL,
            sales_rep_id INT,
            last_purchase_date DATE,
            last_buy_price DECIMAL(12,2),
            purchase_count INT DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (sales_rep_id) REFERENCES sales_reps(id) ON DELETE SET NULL,
            UNIQUE KEY uk_supplier_product (supplier_id, product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: supplier_products";

        // ========== USERS TABLE (for authentication) ==========
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            phone VARCHAR(20),
            password_hash VARCHAR(255) NOT NULL,
            user_level ENUM('superadmin','admin','staff','customer') NOT NULL DEFAULT 'customer',
            avatar_path VARCHAR(255),
            is_active TINYINT(1) DEFAULT 1,
            work_days VARCHAR(255) NULL,
            work_start TIME NULL,
            work_end TIME NULL,
            last_login_at DATETIME,
            last_login_ip VARCHAR(45),
            login_count INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_email (email),
            INDEX idx_user_phone (phone),
            INDEX idx_user_level (user_level)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $messages[] = "✅ Table: users";
        $messages[] = "✅ All tables created successfully";

        // ========== CREATE INDEXES ==========
        $indexes = [
            "CREATE INDEX idx_products_brand ON products(brand_id)",
            "CREATE INDEX idx_products_category ON products(category_id)",
            "CREATE INDEX idx_products_name ON products(full_name)",
            "CREATE INDEX idx_pack_barcode ON product_packagings(barcode)",
            "CREATE INDEX idx_pack_product ON product_packagings(product_id)",
            "CREATE INDEX idx_purchases_date ON purchases(purchase_date)",
            "CREATE INDEX idx_purchases_supplier ON purchases(supplier_id)",
            "CREATE INDEX idx_purchases_salesrep ON purchases(sales_rep_id)",
            "CREATE INDEX idx_stock_product ON stock(product_id)",
            "CREATE INDEX idx_movements_product ON stock_movements(product_id)",
            "CREATE INDEX idx_sales_date ON sale_transactions(created_at)",
            "CREATE INDEX idx_finance_date ON finance_logs(log_date)",
            "CREATE INDEX idx_finance_period ON finance_logs(period_yyyymm)",
            "CREATE INDEX idx_sp_supplier ON supplier_products(supplier_id)",
            "CREATE INDEX idx_sp_product ON supplier_products(product_id)",
            "CREATE INDEX idx_salesreps_supplier ON sales_reps(supplier_id)",
        ];
        foreach ($indexes as $sql) {
            try {
                $db->exec($sql);
            } catch (PDOException $e) {
                // Index already exists — safe to ignore (MySQL error code 1061)
                if (strpos($e->getMessage(), '1061') === false && strpos($e->getMessage(), 'Duplicate') === false) {
                    $messages[] = "⚠️ Index warning: " . $e->getMessage();
                }
            }
        }
        $messages[] = "✅ Indexes created";

        // ========== SEED DATA (in transaction for atomicity) ==========
        $db->beginTransaction();

        // Categories
        $categories = [
            'Alat Serbaguna','ATK','Bahan Bakar dan Perlengkapan Memasak',
            'Bahan Dapur & Bumbu Masak','Listrik dan Elektronik','Makanan Ringan',
            'Mie Instan','Minuman Kemasan','Minuman Serbuk',
            'Pembasmi Hama Rumah Tangga','Perawatan Tubuh dan Kecantikan','Produk Bayi',
            'Produk Dingin & Beku','Produk Kebersihan dan Rumah Tangga','Produk Kesehatan',
            'Rokok','Roti, Kue dan Makanan Olahan','Sembako dan Bahan Pokok',
            'Pemanis, Perasa dan Bahan Pembuat Kue','Permen','Kebutuhan dan Pakan Ternak'
        ];
        $stmt = $db->prepare("INSERT IGNORE INTO categories (name, slug) VALUES (:name, :slug)");
        foreach ($categories as $cat) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $cat));
            $stmt->execute([':name' => $cat, ':slug' => $slug]);
        }
        $messages[] = "✅ Seeded: " . count($categories) . " categories";

        // Units
        $units = [
            ['Pcs','pcs'],['Pack','pck'],['Karton','krt'],['Box','box'],
            ['Sachet','sct'],['Renceng','rcg'],['Slop','slp'],['Bungkus','bks'],
            ['Kaleng','klg'],['Botol','btl'],['Cup','cup'],['Ball','bll'],
            ['Kg','kg'],['Gram','gr'],['Ons','ons'],['Galon','gln'],
            ['Batang','btg'],['Roll','rll'],['Lembar','lbr'],['Lusin','lsn'],
            ['Pouch','pch'],['Tin','tin'],['Toples','tpl'],['Buah','bh'],
            ['Ikat','ikt'],['Krat','krt2'],['Papan','ppn'],['Pasang','psg'],
            ['Sak','sak'],['Buku','bku'],['Butir','btr'],['Kotak','ktk'],
            ['Double Sachet','dsc'],['Dobel Renceng','drc'],['Meter','mtr'],
            ['Zak','zak'],['Tablet','tbl'],['Bundle','bdl']
        ];
        $stmt = $db->prepare("INSERT IGNORE INTO units (name, abbreviation) VALUES (:name, :abbr)");
        foreach ($units as $u) {
            $stmt->execute([':name' => $u[0], ':abbr' => $u[1]]);
        }
        $messages[] = "✅ Seeded: " . count($units) . " units";

        // Supplier Types
        $stypes = ['Home Made','Distributor','Marketplace','Grosir','Minimarket',
                    'Supermarket','Toko','Gudang','Individu','UMKM','Principal'];
        $stmt = $db->prepare("INSERT IGNORE INTO supplier_types (name) VALUES (:name)");
        foreach ($stypes as $st) {
            $stmt->execute([':name' => $st]);
        }
        $messages[] = "✅ Seeded: " . count($stypes) . " supplier types";

        // Customer Types
        $ctypes = [
            ['Individu', 'retail'],
            ['Toko', 'wholesale'],
            ['Warung', 'wholesale'],
        ];
        $stmt = $db->prepare("INSERT IGNORE INTO customer_types (name, price_tier) VALUES (:name, :tier)");
        foreach ($ctypes as $ct) {
            $stmt->execute([':name' => $ct[0], ':tier' => $ct[1]]);
        }
        $messages[] = "✅ Seeded: customer types";

        // App Settings
        $settings = [
            ['store_name', 'AlfarezMart', 'string'],
            ['store_address', 'Jl. Pulo Padang - Marbau, Dusun 6, Desa Sipare Pare Tengah, Kec. Marbau, Kab. Labuhanbatu Utara', 'string'],
            ['store_phone', '082112538367', 'string'],
            ['default_margin_retail', '0.15', 'float'],
            ['default_margin_wholesale', '0.08', 'float'],
            ['thermal_printer_width', '58', 'integer'],
            ['barcode_prefix', 'AM', 'string'],
        ];
        $stmt = $db->prepare("INSERT IGNORE INTO app_settings (setting_key, setting_value, setting_type) VALUES (:key, :val, :type)");
        foreach ($settings as $s) {
            $stmt->execute([':key' => $s[0], ':val' => $s[1], ':type' => $s[2]]);
        }
        $messages[] = "✅ Seeded: app settings";

        // Default Superadmin User
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => 'admin@alfarezmart.com']);
        if (!$stmt->fetch()) {
            $stmt = $db->prepare("
                INSERT INTO users (name, email, phone, password_hash, user_level, is_active)
                VALUES (:name, :email, :phone, :pass, :level, 1)
            ");
            $stmt->execute([
                ':name' => 'Super Admin',
                ':email' => 'admin@alfarezmart.com',
                ':phone' => '082112538367',
                ':pass' => password_hash('admin123', PASSWORD_DEFAULT),
                ':level' => 'superadmin'
            ]);
            $messages[] = "✅ Seeded: default superadmin user (admin@alfarezmart.com / admin123)";
        } else {
            $messages[] = "⏭️ Superadmin user already exists";
        }

        $db->commit();
        $messages[] = "\n🎉 Database setup completed successfully!";

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $messages[] = "❌ Error: " . $e->getMessage();
    }

    return $messages;
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $messages = setupDatabase();
    foreach ($messages as $msg) {
        echo $msg . "\n";
    }
}
