# 🗄️ AlfarezMart PWA - Database Schema

> Detail lengkap semua tabel database. Dirancang berdasarkan analisis 17,290 transaksi historis dan 4,058 produk.

---

## Konfigurasi Database (.env)

```env
DB_DRIVER=sqlite
DB_HOST=153.92.15.83
DB_PORT=3306
DB_DATABASE=alfarezmart
DB_USERNAME=root
DB_PASSWORD=
DB_SQLITE_PATH=storage/database/alfarezmart.sqlite
APP_NAME=AlfarezMart
APP_URL=http://153.92.15.83/AlfarezMart
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
```

---

## SQL Schema (SQLite Compatible, MySQL Migration-Ready)

### 1. brands
```sql
CREATE TABLE brands (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- Contoh: Indomie, Cimory, Nabati, Miranda, Sampoerna
```

### 2. categories
```sql
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100),
    icon VARCHAR(50),
    sort_order INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- 21 kategori dari data historis: Sembako, Makanan Ringan, Minuman Kemasan, Rokok, ATK, dll
```

### 3. units
```sql
CREATE TABLE units (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    abbreviation VARCHAR(10),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- 38 satuan: Pcs, Pack, Karton, Slop, Sachet, Renceng, Box, Kaleng, dll
```

### 4. products (Master Produk)
```sql
CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(50) UNIQUE,           -- Internal product code
    brand_id INTEGER,
    category_id INTEGER,
    product_type VARCHAR(100),         -- Jenis produk (UHT, Hair Color, Goreng, dll)
    variant VARCHAR(100),              -- Varian (Choco Malt, Violet Red, Original, dll)
    full_name VARCHAR(255) NOT NULL,   -- Nama gabungan lengkap: "Cimory UHT Choco Malt (24 x 250ml)"
    short_label VARCHAR(50),           -- Label thermal: "Cimory UHT Choco Malt 250ml"
    invoice_name VARCHAR(100),         -- Nama untuk invoice AI matching
    
    -- Satuan hierarchy (3 level: kecil, sedang, besar)
    unit_small_id INTEGER,             -- FK ke units (Pcs, Sachet, Bungkus)
    unit_medium_id INTEGER,            -- FK ke units (Pack, Renceng, Slop)
    unit_large_id INTEGER,             -- FK ke units (Karton, Box, Dus)
    
    -- Isi per satuan
    qty_small INTEGER DEFAULT 1,       -- Isi terkecil (selalu 1)
    qty_medium INTEGER,                -- Berapa kecil per sedang (misal 10 pcs per pack)
    qty_large INTEGER,                 -- Berapa kecil per besar (misal 40 pcs per karton)
    
    -- Ukuran/berat/volume per satuan kecil
    weight_value DECIMAL(10,2),        -- Angka berat/volume (250, 60, 1)
    weight_unit VARCHAR(10),           -- Satuan ukuran (g, ml, kg, L, cm)
    
    -- Info tambahan
    description TEXT,
    image_path VARCHAR(255),
    min_stock INTEGER DEFAULT 0,
    max_stock INTEGER,
    is_active INTEGER DEFAULT 1,
    is_multivariant INTEGER DEFAULT 0,
    ref_product_id INTEGER,            -- Referensi ke produk utama (untuk multivarian)
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (brand_id) REFERENCES brands(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (unit_small_id) REFERENCES units(id),
    FOREIGN KEY (unit_medium_id) REFERENCES units(id),
    FOREIGN KEY (unit_large_id) REFERENCES units(id)
);

CREATE INDEX idx_products_brand ON products(brand_id);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_full_name ON products(full_name);
CREATE INDEX idx_products_active ON products(is_active);
```

### 5. product_barcodes
```sql
CREATE TABLE product_barcodes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    barcode VARCHAR(50) NOT NULL,
    barcode_type VARCHAR(20) DEFAULT 'EAN13', -- EAN13, EAN8, CODE128, GENERATED
    unit_level VARCHAR(10) NOT NULL,           -- 'small', 'medium', 'large'
    is_generated INTEGER DEFAULT 0,            -- 1 jika di-generate oleh sistem
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX idx_barcode_unique ON product_barcodes(barcode);
CREATE INDEX idx_barcode_product ON product_barcodes(product_id);
```

### 6. product_prices
```sql
CREATE TABLE product_prices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    unit_level VARCHAR(10) NOT NULL,      -- 'small', 'medium', 'large'
    
    -- Harga Beli
    buy_price DECIMAL(12,2) DEFAULT 0,     -- Harga beli per satuan
    buy_price_nett DECIMAL(12,2) DEFAULT 0,-- Harga beli nett (setelah diskon/ppn)
    
    -- Harga Jual Retail (konsumen langsung)
    sell_price_retail DECIMAL(12,2) DEFAULT 0,
    margin_retail DECIMAL(5,4) DEFAULT 0,   -- Margin dalam desimal
    profit_retail DECIMAL(12,2) DEFAULT 0,  -- Selisih harga
    
    -- Harga Jual Grosir (toko lain)
    sell_price_wholesale DECIMAL(12,2) DEFAULT 0,
    margin_wholesale DECIMAL(5,4) DEFAULT 0,
    profit_wholesale DECIMAL(12,2) DEFAULT 0,
    
    -- PPN & Diskon terakhir
    ppn_percent DECIMAL(5,2) DEFAULT 0,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE INDEX idx_prices_product ON product_prices(product_id);
CREATE UNIQUE INDEX idx_prices_unique ON product_prices(product_id, unit_level);
```

### 7. supplier_types
```sql
CREATE TABLE supplier_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE
);
-- Data: Home Made, Distributor, Marketplace, Grosir, Minimarket, Supermarket, Toko, Gudang, Individu, UMKM, Principal
```

### 8. suppliers
```sql
CREATE TABLE suppliers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(150) NOT NULL,
    type_id INTEGER,
    address TEXT,
    products_sold TEXT,               -- Produk yang dijual
    is_consignment INTEGER DEFAULT 0, -- 1 jika supplier titipan
    notes TEXT,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (type_id) REFERENCES supplier_types(id)
);
```

### 9. sales_reps (Kontak Sales)
```sql
CREATE TABLE sales_reps (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    supplier_id INTEGER NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    sales_type VARCHAR(50),           -- Admin Toko, Sales TO, Sales Canvas, dll
    visit_day VARCHAR(50),            -- Hari kunjungan
    delivery_day VARCHAR(50),         -- Hari pengantaran
    visit_period VARCHAR(50),         -- Mingguan, 2 Minggu, Bulanan
    status VARCHAR(20) DEFAULT 'Aktif',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
);
```

### 10. purchases (Header Pembelian)
```sql
CREATE TABLE purchases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    purchase_code VARCHAR(30) UNIQUE,  -- Auto: PUR-YYMMDD-XXXX
    supplier_id INTEGER,
    sales_rep_id INTEGER,
    purchase_date DATE NOT NULL,
    total_amount DECIMAL(15,2) DEFAULT 0,
    total_items INTEGER DEFAULT 0,
    ppn_amount DECIMAL(12,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    shipping_cost DECIMAL(12,2) DEFAULT 0,
    grand_total DECIMAL(15,2) DEFAULT 0,
    payment_status VARCHAR(20) DEFAULT 'Lunas', -- Lunas, Belum Lunas, Cicilan
    invoice_photo VARCHAR(255),        -- Path foto faktur
    notes TEXT,
    synced_to_finance INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (sales_rep_id) REFERENCES sales_reps(id)
);

CREATE INDEX idx_purchases_date ON purchases(purchase_date);
CREATE INDEX idx_purchases_supplier ON purchases(supplier_id);
```

### 11. purchase_items (Detail Item Pembelian)
```sql
CREATE TABLE purchase_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    purchase_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_level VARCHAR(10) NOT NULL,   -- 'small', 'medium', 'large'
    buy_price DECIMAL(12,2) NOT NULL,  -- Harga beli satuan
    ppn_percent DECIMAL(5,2) DEFAULT 0,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    nett_price DECIMAL(12,2),          -- Harga nett per satuan
    total_price DECIMAL(15,2),         -- quantity * nett_price
    expiry_date DATE,
    
    -- Harga jual yang ditentukan saat input
    sell_price_retail DECIMAL(12,2),
    sell_price_wholesale DECIMAL(12,2),
    
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE INDEX idx_pitems_purchase ON purchase_items(purchase_id);
CREATE INDEX idx_pitems_product ON purchase_items(product_id);
```

### 12. stock
```sql
CREATE TABLE stock (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL UNIQUE,
    current_qty_small INTEGER DEFAULT 0,  -- Stok dalam satuan terkecil
    last_restock_date DATE,
    last_restock_qty INTEGER,
    nearest_expiry DATE,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE INDEX idx_stock_product ON stock(product_id);
```

### 13. stock_movements (Log Pergerakan Stok)
```sql
CREATE TABLE stock_movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    movement_type VARCHAR(20) NOT NULL,  -- 'in', 'out', 'adjustment', 'return'
    quantity INTEGER NOT NULL,            -- Dalam satuan terkecil (positif/negatif)
    reference_type VARCHAR(20),           -- 'purchase', 'sale', 'consignment', 'manual'
    reference_id INTEGER,                 -- ID dari purchase/sale
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE INDEX idx_movements_product ON stock_movements(product_id);
CREATE INDEX idx_movements_date ON stock_movements(created_at);
```

### 14. customer_types
```sql
CREATE TABLE customer_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    price_tier VARCHAR(20) NOT NULL     -- 'retail' atau 'wholesale'
);
-- Data: Individu (retail), Toko (wholesale), Warung (wholesale)
```

### 15. customers
```sql
CREATE TABLE customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    type_id INTEGER,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (type_id) REFERENCES customer_types(id)
);
```

### 16. sale_transactions
```sql
CREATE TABLE sale_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_number VARCHAR(30) UNIQUE,  -- Auto: INV-YYMMDDHHMMSS
    customer_id INTEGER,
    sale_mode VARCHAR(10) NOT NULL,     -- 'retail' atau 'wholesale'
    total_amount DECIMAL(15,2) DEFAULT 0,
    payment_method VARCHAR(20) DEFAULT 'Cash',
    payment_status VARCHAR(20) DEFAULT 'Lunas',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE INDEX idx_sales_date ON sale_transactions(created_at);
CREATE INDEX idx_sales_customer ON sale_transactions(customer_id);
```

### 17. sale_items
```sql
CREATE TABLE sale_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_level VARCHAR(10) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    profit DECIMAL(12,2) DEFAULT 0,
    
    FOREIGN KEY (transaction_id) REFERENCES sale_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

### 18. consignments (Penitipan Barang)
```sql
CREATE TABLE consignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    supplier_id INTEGER NOT NULL,
    consignment_date DATE NOT NULL,
    check_period VARCHAR(50),          -- "3 hari sekali", "1 minggu sekali"
    next_check_date DATE,
    payment_status VARCHAR(20) DEFAULT 'Belum Lunas',
    total_cost DECIMAL(15,2) DEFAULT 0,
    total_sold DECIMAL(15,2) DEFAULT 0,
    total_returned INTEGER DEFAULT 0,
    payment_amount DECIMAL(15,2) DEFAULT 0,
    payment_date DATE,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);
```

### 19. consignment_items
```sql
CREATE TABLE consignment_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    consignment_id INTEGER NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INTEGER NOT NULL,
    cost_price DECIMAL(12,2) NOT NULL,
    sell_price DECIMAL(12,2) NOT NULL,
    qty_sold INTEGER DEFAULT 0,
    qty_returned INTEGER DEFAULT 0,
    status VARCHAR(20) DEFAULT 'Aktif',
    
    FOREIGN KEY (consignment_id) REFERENCES consignments(id) ON DELETE CASCADE
);
```

### 20. finance_logs
```sql
CREATE TABLE finance_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    log_date DATE NOT NULL,
    period_yyyymm VARCHAR(6),
    amount DECIMAL(15,2) NOT NULL,
    balance_type VARCHAR(30),          -- Saldo Utama, Saldo Rokok, Saldo Beras, Saldo Pulsa
    category VARCHAR(20) NOT NULL,     -- Pemasukan, Pengeluaran
    detail VARCHAR(100),               -- Omzet Toko, Belanja Toko, Listrik, dll
    description TEXT,
    reference_type VARCHAR(20),        -- purchase, sale, operational
    reference_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_finance_date ON finance_logs(log_date);
CREATE INDEX idx_finance_period ON finance_logs(period_yyyymm);
CREATE INDEX idx_finance_category ON finance_logs(category);
```

### 21. debts (Hutang Pelanggan)
```sql
CREATE TABLE debts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER,
    customer_name VARCHAR(100),
    phone VARCHAR(20),
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'Belum Lunas',
    due_date DATE,
    paid_date DATE,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);
```

### 22. app_settings
```sql
CREATE TABLE app_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'string',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Default settings
INSERT INTO app_settings (setting_key, setting_value, setting_type) VALUES
('store_name', 'AlfarezMart', 'string'),
('store_address', 'Jl. Pulo Padang - Marbau, Dusun 6, Desa Sipare Pare Tengah, Kec. Marbau, Kab. Labuhanbatu Utara', 'string'),
('store_phone', '082112538367', 'string'),
('default_margin_retail', '0.15', 'float'),
('default_margin_wholesale', '0.08', 'float'),
('thermal_printer_width', '58', 'integer'),
('barcode_prefix', 'AM', 'string'),
('currency', 'IDR', 'string'),
('timezone', 'Asia/Jakarta', 'string');
```

---

## Seed Data dari Historis

### Categories (21 records)
```sql
INSERT INTO categories (name, slug) VALUES
('Alat Serbaguna', 'alat-serbaguna'),
('ATK', 'atk'),
('Bahan Bakar dan Perlengkapan Memasak', 'bahan-bakar'),
('Bahan Dapur & Bumbu Masak', 'bahan-dapur'),
('Listrik dan Elektronik', 'listrik-elektronik'),
('Makanan Ringan', 'makanan-ringan'),
('Mie Instan', 'mie-instan'),
('Minuman Kemasan', 'minuman-kemasan'),
('Minuman Serbuk', 'minuman-serbuk'),
('Pembasmi Hama Rumah Tangga', 'pembasmi-hama'),
('Perawatan Tubuh dan Kecantikan', 'perawatan-tubuh'),
('Produk Bayi', 'produk-bayi'),
('Produk Dingin & Beku', 'produk-dingin'),
('Produk Kebersihan dan Rumah Tangga', 'kebersihan'),
('Produk Kesehatan', 'kesehatan'),
('Rokok', 'rokok'),
('Roti, Kue dan Makanan Olahan', 'roti-kue'),
('Sembako dan Bahan Pokok', 'sembako'),
('Pemanis, Perasa dan Bahan Pembuat Kue', 'bahan-kue'),
('Permen', 'permen'),
('Kebutuhan dan Pakan Ternak', 'pakan-ternak');
```

### Units (38 records - abbreviated)
```sql
INSERT INTO units (name, abbreviation) VALUES
('Pcs', 'pcs'), ('Pack', 'pck'), ('Karton', 'krt'), ('Box', 'box'),
('Sachet', 'sct'), ('Renceng', 'rcg'), ('Slop', 'slp'), ('Bungkus', 'bks'),
('Kaleng', 'klg'), ('Botol', 'btl'), ('Cup', 'cup'), ('Ball', 'bll'),
('Kg', 'kg'), ('Gram', 'gr'), ('Ons', 'ons'), ('Galon', 'gln'),
('Batang', 'btg'), ('Roll', 'rll'), ('Lembar', 'lbr'), ('Lusin', 'lsn'),
('Pouch', 'pch'), ('Tin', 'tin'), ('Toples', 'tpl'), ('Buah', 'bh'),
('Ikat', 'ikt'), ('Krat', 'krt'), ('Papan', 'ppn'), ('Pasang', 'psg'),
('Sak', 'sak'), ('Buku', 'bku'), ('Butir', 'btr'), ('Kotak', 'ktk'),
('Double Sachet', 'dsc'), ('Dobel Renceng', 'drc'), ('Meter', 'mtr'),
('Zak', 'zak'), ('Tablet', 'tbl'), ('Bundle', 'bdl');
```

### Supplier Types (11 records)
```sql
INSERT INTO supplier_types (name) VALUES
('Home Made'), ('Distributor'), ('Marketplace'), ('Grosir'),
('Minimarket'), ('Supermarket'), ('Toko'), ('Gudang'),
('Individu'), ('UMKM'), ('Principal');
```

---

## NDJSON Daily Transaction Format

File disimpan di `storage/transactions/YYYY-MM-DD.ndjson`:

```json
{"ts":"2026-05-14T10:30:00","type":"sale","mode":"retail","invoice":"INV-260514103000","customer":"Walk-in","items":[{"product_id":1,"name":"Indomie Goreng","qty":5,"unit":"Pcs","price":3500,"total":17500}],"total":17500,"payment":"Cash"}
{"ts":"2026-05-14T11:00:00","type":"purchase","code":"PUR-260514-0001","supplier":"PT Everbright","total":500000}
```

---

## Migration Notes (SQLite → MySQL)

Saat migrasi ke MySQL, perhatikan:
1. `INTEGER PRIMARY KEY AUTOINCREMENT` → `INT AUTO_INCREMENT PRIMARY KEY`
2. `DATETIME DEFAULT CURRENT_TIMESTAMP` → tetap sama
3. `INTEGER` untuk boolean → `TINYINT(1)`
4. Tambahkan `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4`
5. Index syntax tetap kompatibel
