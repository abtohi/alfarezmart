# BLUEPRINT — AlfarezMart

> Dokumen ini adalah referensi arsitektur dan gambaran besar website AlfarezMart.
> Dibaca oleh AI untuk memahami project tanpa harus mempelajari semua file kode.
> Update dokumen ini jika ada perubahan arsitektur, modul baru, atau perubahan pola signifikan.

---

## 1. Deskripsi & Tujuan

**AlfarezMart** adalah sistem manajemen toko berbasis web yang dibangun sebagai **PWA (Progressive Web App)** dengan prioritas tampilan dan penggunaan di **perangkat mobile Android**.

**Fungsi utama:**
- Manajemen stok & produk (harga modal, ecer, grosir, tier pricing)
- Kasir POS (Point of Sale) dengan barcode scanner
- Pencatatan pembelian barang masuk (purchase/barang masuk dari supplier)
- Pencatatan penjualan
- Manajemen supplier & sales representative
- Laporan produk & histori transaksi
- Pengaturan struk thermal printer
- Master data (brand, kategori, satuan/unit)

**Target pengguna:** Pemilik toko / kasir / admin toko AlfarezMart.

---

## 2. Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP Native (tanpa framework) |
| Pattern | MVC + OOP |
| Frontend | Bootstrap 5.3.3 |
| Icon | Bootstrap Icons 1.11.3 (utama) |
| Font | Google Fonts — Inter (300–800) |
| JavaScript | Vanilla JS (ES6+) |
| AJAX | Fetch API via `api()` helper (`utils.js`) |
| PWA | Service Worker + Web App Manifest |
| Database | MySQL (via XAMPP/MariaDB) + PDO |
| Server | XAMPP (Apache + PHP) |
| Timezone | Asia/Jakarta (GMT+7) |

---

## 3. Struktur Direktori

```
AlfarezMart/
├── app/
│   ├── config/
│   │   ├── App.php          # Load .env, definisi konstanta, BASE_URL, timezone
│   │   ├── Database.php     # Konfigurasi koneksi PDO
│   │   └── Routes.php       # Semua route web & API
│   ├── controllers/
│   │   ├── ApiController.php        # Semua JSON API endpoint (terbesar, ~57KB)
│   │   ├── AuthController.php       # Login, logout
│   │   ├── BarcodeController.php    # Halaman scanner
│   │   ├── DashboardController.php  # Dashboard & halaman help
│   │   ├── ProductController.php    # CRUD produk (web view)
│   │   ├── PurchaseController.php   # Barang masuk (web view)
│   │   ├── ReportController.php     # Laporan (web view)
│   │   ├── SaleController.php       # Penjualan & POS (web view)
│   │   ├── SettingController.php    # Pengaturan, master data
│   │   ├── SupplierController.php   # Supplier (web view)
│   │   └── UserController.php       # Manajemen user (web view)
│   ├── models/
│   │   ├── ProductModel.php         # Produk, packaging, tier pricing (~18KB)
│   │   ├── PurchaseModel.php        # Pembelian barang masuk (~17KB)
│   │   ├── SaleModel.php            # Penjualan (~10KB)
│   │   ├── SalesRepModel.php        # Sales representative
│   │   ├── SupplierModel.php        # Supplier
│   │   ├── SupplierProductModel.php # Relasi supplier-produk
│   │   ├── UserModel.php            # User & autentikasi
│   │   ├── SettingModel.php         # Pengaturan aplikasi
│   │   ├── BrandModel.php           # Master brand
│   │   ├── CategoryModel.php        # Master kategori
│   │   ├── SupplierTypeModel.php    # Tipe supplier
│   │   └── UnitModel.php            # Satuan produk
│   └── views/
│       ├── layouts/
│       │   └── app.php              # Layout utama (header, nav, footer, scripts)
│       ├── auth/                    # Halaman login
│       ├── dashboard/               # Halaman beranda
│       ├── products/                # Daftar, detail, buat, edit produk
│       ├── purchases/               # Daftar & form pembelian
│       ├── sales/                   # Daftar penjualan & POS kasir
│       ├── suppliers/               # Daftar & detail supplier
│       ├── scanner/                 # Scanner barcode (cek harga)
│       ├── reports/                 # Laporan produk & histori
│       ├── settings/                # Master data, pengaturan struk
│       ├── users/                   # Manajemen user
│       ├── help/                    # Dokumentasi/bantuan
│       └── errors/                  # Halaman error (404, dll)
├── public/
│   ├── css/
│   │   ├── variables.css    # Design tokens / CSS custom properties
│   │   ├── app.css          # Layout utama, body, header, bottom nav, toast
│   │   └── components.css   # Komponen UI: card, badge, form, modal, dll
│   ├── js/
│   │   ├── utils.js         # Utilitas global: formatRupiah, showToast, api(), debounce
│   │   ├── app.js           # PWA install prompt, global search, header scroll
│   │   ├── components.js    # Komponen UI JS reusable
│   │   ├── barcode.js       # Scanner barcode (ZXing-JS + html5-qrcode)
│   │   ├── printer.js       # Thermal printer ESC-POS via Web Serial API
│   │   ├── packaging-prices.js  # Logika harga & packaging produk
│   │   └── qty-pricing.js   # Logika tier pricing berbasis kuantitas
│   └── images/
│       ├── Icon.png         # Ikon header (32x32)
│       └── mobile_icon.png  # Ikon PWA maskable (512x512)
├── storage/
│   ├── logs/
│   ├── uploads/
│   │   ├── invoice_photos/  # Foto invoice pembelian
│   │   └── product_images/  # Foto produk
│   └── migrations/          # SQL migration files
├── docs/
│   └── AI/
│       ├── ai-instructions.md   # Instruksi AI (rules utama)
│       ├── BLUEPRINT.md         # File ini
│       ├── CURRENT_STATE.md     # Kondisi development terkini
│       └── CHANGE_LOG.md        # Log semua perubahan
├── database/                    # SQL schema & seed files
├── .env                         # Konfigurasi environment (tidak di-commit)
├── .htaccess                    # URL rewriting Apache
├── index.php                    # Entry point aplikasi
├── manifest.json                # PWA manifest
└── sw.js                        # Service Worker PWA
```

---

## 4. Routing System

**Entry point:** `index.php` → `app/config/Routes.php`

**Format route:** `$router->get('/path', 'ControllerName@method');`

**Pola URL:**
- Web views: `GET /products`, `GET /products/create`, `GET /products/{id}`, dll.
- API endpoints: `GET/POST /api/{resource}/...` → return JSON
- Auth: `GET /login`, `POST /api/auth/login`, `GET /logout`

**Semua route ada di** `app/config/Routes.php` — wajib daftarkan route baru di sini.

### Route Utama

| Route | Controller | Keterangan |
|-------|-----------|------------|
| `GET /` | `DashboardController@index` | Beranda / Dashboard |
| `GET /products` | `ProductController@index` | Daftar produk |
| `GET /products/create` | `ProductController@create` | Form tambah produk |
| `GET /products/{id}` | `ProductController@show` | Detail produk |
| `GET /products/{id}/edit` | `ProductController@edit` | Edit produk |
| `GET /purchases` | `PurchaseController@index` | Daftar pembelian |
| `GET /purchases/create` | `PurchaseController@create` | Form barang masuk |
| `GET /sales/pos` | `SaleController@pos` | Kasir POS |
| `GET /suppliers` | `SupplierController@index` | Daftar supplier |
| `GET /scanner` | `BarcodeController@scanner` | Scanner barcode |
| `GET /reports` | `ReportController@index` | Laporan |
| `GET /settings/master-data` | `SettingController@masterData` | Master data |
| `GET /settings/receipt` | `SettingController@receiptSettings` | Pengaturan struk |
| `GET /users` | `UserController@index` | Manajemen user |
| `GET /help` | `DashboardController@help` | Bantuan |

---

## 5. API Endpoints

Semua API dihandle oleh `ApiController.php`. Response format wajib:
```json
{ "success": true/false, "data": ..., "message": "..." }
```

### Produk
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | `/api/products` | Daftar semua produk |
| GET | `/api/products/search?q=` | Cari produk (global search) |
| GET | `/api/products/barcode/{code}` | Cari by barcode (scanner) |
| GET | `/api/products/{id}` | Detail produk |
| POST | `/api/products` | Buat produk baru |
| POST | `/api/products/update/{id}` | Update produk |
| POST | `/api/products/{id}/delete` | Hapus produk |
| POST | `/api/products/packaging/{id}` | Update packaging |
| POST | `/api/products/packaging/{id}/qty-prices` | Simpan tier pricing |
| POST | `/api/products/packaging/{id}/delete` | Hapus packaging |
| POST | `/api/products/{id}/packaging/add` | Tambah packaging |
| POST | `/api/products/{id}/photo` | Upload foto produk |
| POST | `/api/products/{id}/stock` | Update stok manual |

### Pembelian
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | `/api/purchases` | Daftar pembelian |
| POST | `/api/purchases` | Buat pembelian (barang masuk) |
| POST | `/api/purchases/{id}/photo` | Upload foto invoice |
| POST | `/api/purchases/{id}/delete` | Hapus pembelian |
| GET | `/api/purchases/search-products` | Cari produk untuk form pembelian |

### Penjualan
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | `/api/sales` | Buat transaksi penjualan |
| GET | `/api/sales/invoice/{id}` | Ambil data invoice |

### Supplier & Sales Rep
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | `/api/suppliers` | Daftar supplier |
| GET | `/api/suppliers/search?q=` | Cari supplier/sales |
| POST | `/api/suppliers` | Tambah supplier |
| POST | `/api/suppliers/{id}` | Update supplier |
| POST | `/api/suppliers/{id}/delete` | Hapus supplier |
| GET | `/api/suppliers/{id}/products` | Produk milik supplier |
| GET | `/api/suppliers/{id}/bulk-products` | Produk bulk purchase |
| GET | `/api/sales-reps` | Semua sales rep |
| POST | `/api/sales-reps` | Tambah sales rep |

### Master Data
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET/POST | `/api/brands` | CRUD brand |
| GET/POST | `/api/categories` | CRUD kategori |
| GET/POST | `/api/units` | CRUD satuan |

### Dashboard & User
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | `/api/dashboard/stats` | Statistik dashboard |
| GET/POST | `/api/users` | Manajemen user |
| GET | `/api/settings/receipt` | Ambil pengaturan struk |
| POST | `/api/settings/receipt` | Simpan pengaturan struk |

---

## 6. Database Schema (Ringkasan)

Tabel utama di database MySQL:

| Tabel | Keterangan |
|-------|-----------|
| `users` | User sistem (id, name, username, password, role, is_active) |
| `products` | Produk (id, short_label, full_name, brand_id, category_id, barcode, harga_modal, harga_ecer, harga_grosir, stok, is_active) |
| `product_packaging` | Packaging produk (id, product_id, unit_id, qty_per_pack, harga_modal, harga_ecer, harga_grosir, is_base) |
| `product_qty_prices` | Tier pricing (id, packaging_id, min_qty, harga_jual) |
| `purchases` | Pembelian/barang masuk header (id, supplier_id, sales_rep_id, tanggal, total, catatan) |
| `purchase_items` | Detail item pembelian (id, purchase_id, product_id, qty, harga_beli, subtotal) |
| `sales` | Transaksi penjualan header (id, tanggal, total, bayar, kembalian, kasir_id) |
| `sale_items` | Detail item penjualan (id, sale_id, product_id, qty, harga_jual, subtotal) |
| `suppliers` | Supplier (id, name, type_id, phone, address, notes) |
| `supplier_types` | Tipe supplier |
| `supplier_products` | Relasi supplier ↔ produk (harga_beli terakhir dari supplier) |
| `sales_reps` | Sales representative (id, supplier_id, name, phone) |
| `brands` | Master brand produk |
| `categories` | Master kategori produk |
| `units` | Satuan produk (pcs, dus, karton, dll) |
| `settings` | Pengaturan aplikasi (key-value store) |

---

## 7. Design System & UI Pattern

### Tema
**Dark modern** dengan aksen **merah (#e63946)** — seperti aplikasi Android premium.

### Layout Mobile-First
```
┌─────────────────────────────┐
│  [Status Bar Overlay]        │ ← pseudo status bar android
│  [App Header 56px]           │ ← fixed, hide on scroll
├─────────────────────────────┤
│                              │
│     [Main Content]           │ ← scroll area, padding top 56px + bottom 64px
│                              │
├─────────────────────────────┤
│  [Bottom Nav 64px - 5 item]  │ ← fixed, android-style
└─────────────────────────────┘
```

### Bottom Navigation (5 item)
| Icon | Label | Route | ID |
|------|-------|-------|-----|
| `bi-house-door` | Beranda | `/` | `navHome` |
| `bi-box-seam` | Produk | `/products` | `navProducts` |
| `bi-upc-scan` | Scan | `/scanner` | `navScan` (tombol tengah elevated) |
| `bi-cart-plus` | Masuk | `/purchases/create` | `navPurchase` |
| `bi-receipt` | Kasir POS | `/sales/pos` | `navPos` |

### CSS Files
| File | Isi |
|------|-----|
| `variables.css` | Semua CSS custom properties (token warna, spacing, typography, z-index) |
| `app.css` | Body, layout, header, bottom nav, search overlay, toast, loader |
| `components.css` | Card, badge, form, modal, table, button, empty state, dll |

### Warna Utama
| Peran | Variabel | Nilai |
|-------|----------|-------|
| Aksen utama | `--primary` | `#e63946` |
| Background utama | `--bg-primary` | `#0f0f1a` |
| Background kartu | `--bg-card` | `#16213e` |
| Sukses | `--success` | `#2ec4b6` |
| Peringatan | `--warning` | `#ffb703` |
| Info | `--info` | `#4cc9f0` |
| Bahaya | `--danger` | `#e63946` |

### Komponen Wajib
| Komponen | Cara Pakai |
|----------|-----------|
| Toast notification | `showToast(msg, type, duration, onClick)` — `utils.js` |
| Konfirmasi/Alert | Modal konfirmasi custom atau Bootstrap modal — **bukan** `alert()`/`confirm()` |
| Loading | `.elegant-loader` (3 dots) atau spinner existing |
| Empty state | `<div class="empty-state"><i class="bi ..."></i><p>...</p></div>` |
| Card | `<div class="app-card">...</div>` |

---

## 8. PWA Architecture

```
[Browser]
   │
   ├── manifest.json        ← installable, standalone, portrait, lang=id
   │
   └── sw.js (Service Worker)
         ├── Cache: alfarezmart-v1.93 (update versi jika asset berubah)
         ├── Static assets → Cache First
         ├── /api/* → Network First, fallback cache
         └── HTML pages → Network First, fallback cache/index
```

**Install flow:**
- `beforeinstallprompt` event → simpan di `deferredInstallPrompt`
- Tampilkan `showToast` dengan clickable install prompt
- `appinstalled` event → konfirmasi toast sukses

**Auto-login hint:**
- `localStorage.alfarezmart_logged_in = 'true'` setelah login sukses
- Dicek di `app.js` saat load halaman

**Cache version:** `alfarezmart-v1.93` di `sw.js` (root) — update saat ada asset baru penting.

---

## 9. Modul & Fitur Detail

### Dashboard
- Statistik ringkasan (total produk, stok, transaksi hari ini)
- Grid menu akses cepat ke semua fitur
- API: `GET /api/dashboard/stats`

### Produk
- Daftar produk dengan filter & pencarian
- Detail produk: info dasar, packaging, tier pricing, histori harga
- Packaging: 1 produk bisa punya banyak packaging (pcs, dus, karton, dll)
- Tier pricing: harga berbeda per minimal kuantitas
- Label produk: cetak label dengan barcode
- Upload foto produk
- Update stok manual

### Barang Masuk (Purchase)
- Form input barang masuk dari supplier (multi-item, bulk)
- Pilih supplier & sales rep
- Pilih produk dengan auto-suggest (search)
- Input qty, harga beli, total harga otomatis
- Hitung margin otomatis (modal vs harga jual existing)
- Upload foto invoice
- Riwayat harga dari supplier terakhir (last price analytics)

### Kasir POS (Sales)
- Interface POS mobile-friendly
- Scan barcode atau search produk
- Cart dengan qty dan harga otomatis
- Tier pricing otomatis (harga berubah sesuai kuantitas)
- Hitung total, bayar, kembalian
- Cetak struk ke thermal printer via Web Serial API

### Supplier
- Daftar supplier dengan tipe (distributor, agen, dll)
- Detail supplier: info kontak, daftar sales rep, daftar produk, harga terakhir
- CRUD supplier & sales rep
- Context-aware search (di halaman supplier, search mencari supplier/sales)

### Scanner (Cek Harga)
- Scan barcode via kamera (ZXing-JS + html5-qrcode)
- Tampil info produk: nama, harga ecer, stok
- Mode real-time untuk kasir/cek harga cepat

### Laporan
- Histori produk: riwayat pembelian & penjualan per produk
- Export histori produk

### Pengaturan
- **Master data**: brand, kategori, satuan (on-the-fly create dari form produk/pembelian)
- **Pengaturan struk**: nama toko, alamat, no HP, logo, header/footer struk termal

### Manajemen User
- Daftar user (hanya superadmin)
- Tambah, aktifkan/nonaktifkan, reset password, hapus user

### Help
- Dokumentasi penggunaan sistem
- Alur fitur utama, terminologi, troubleshooting

---

## 10. Pattern AJAX

**Standard pattern di semua modul:**

```javascript
// GET
const data = await api(`${BASE_URL}api/resource`);

// POST
const result = await api(`${BASE_URL}api/resource`, 'POST', { key: value });
// atau dengan FormData:
const result = await api(`${BASE_URL}api/resource`, { method: 'POST', body: formData });
```

**Response handling:**
```javascript
if (result.success) {
    showToast('Berhasil', 'success');
} else {
    showToast(result.message || 'Gagal', 'error');
}
```

**Error handling** otomatis oleh `api()` helper — akan tampil toast error jika network/server error.

---

## 11. Security Pattern

- Semua query: **PDO prepared statement**
- Input: validasi + sanitasi di Controller
- Output ke View: `htmlspecialchars()`
- CSRF: `CsrfHelper::tokenField()` di form POST, validasi `CsrfHelper::validate()` di Controller
- Auth: session PHP, dicek di Controller base class
- Role: `superadmin` (akses penuh) vs user biasa
- File upload: validasi extension, MIME, ukuran, disimpan di `storage/uploads/`

---

## 12. Area Sensitif & Catatan Penting

| Area | Catatan |
|------|---------|
| `ApiController.php` | File terbesar (~57KB), semua API ada di sini — hati-hati saat edit |
| `sw.js` | Update `CACHE_NAME` jika ada asset baru penting |
| `public/css/variables.css` | Semua token design — jangan ubah nilai tanpa instruksi |
| `app/views/layouts/app.php` | Layout master — perubahan berdampak ke semua halaman |
| `app/config/Routes.php` | Semua route — jangan ubah path existing |
| `product_packaging` & `product_qty_prices` | Relasi kompleks — hati-hati dengan foreign key |
| Barcode scanner | 2 engine: ZXing-JS (utama) + html5-qrcode (fallback) |
| Thermal printer | Web Serial API — hanya di browser Chromium |
| `localStorage.alfarezmart_logged_in` | Dipakai untuk PWA auto-login hint |
| Notification polling | Berjalan setiap interval — jangan ganggu |
