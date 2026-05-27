# CHANGE LOG — AlfarezMart

> File ini mencatat semua perubahan yang dilakukan pada project AlfarezMart secara kronologis.
> **Jangan hapus entri lama.** Tambahkan entri baru di paling atas (format terbaru di atas).
> AI membaca file ini untuk memahami histori perubahan jika diperlukan konteks mendalam.

---

## Format Entri

```
## [YYYY-MM-DD] — Judul Singkat Perubahan

**Tipe:** Minor | Mayor | Hotfix | Refactor | Dokumentasi
**Modul:** [nama modul/file terkait]
**Dikerjakan oleh:** AI Agent

### Perubahan
- Deskripsi perubahan 1
- Deskripsi perubahan 2

### File yang Diubah/Dibuat
- `path/to/file.php` — keterangan singkat
- `path/to/file.js` — keterangan singkat

### Catatan
- Catatan penting, risiko, atau hal yang perlu diperhatikan (jika ada)
```

## [2026-05-27] — Hotfix & UI Polish

**Tipe:** Minor / Hotfix
**Modul:** UI/UX, Sales, Products, Core
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Fix Entitas HTML (&amp;)**: Memperbaiki bug dimana saat user menginput karakter ampersand (`&`) di form (seperti Kategori/Brand/Satuan), karakter tersebut dirender secara literal sebagai `&amp;` di UI SearchBox. Perbaikan dilakukan dengan mengganti `htmlspecialchars()` menjadi `json_encode()` saat meng-inject string PHP ke dalam variabel/object literal JavaScript di `products/create.php`, `products/edit.php`, `suppliers/index.php`, dan `purchases/create.php`.
- **Update Aturan AI**: Menambahkan instruksi eksplisit ke dalam `docs/AI/ai-instructions.md` agar AI selalu menggunakan `json_encode()` untuk transfer string PHP -> JS guna menghindari masalah serupa di masa depan.
- **Fix Kalkulasi Harga Tier Kasir POS**: Memperbaiki masalah dimana jika user menginput kuantitas barang di POS yang memenuhi syarat tier kelipatan harga (contoh: beli 4 dengan harga Rp 5000), total harga yang dihitung tidak menggunakan harga tier total, melainkan harga ecer biasa. Perbaikan dilakukan di `recalcItemPrice` (`app/views/sales/pos.php`) dengan menghitung total tier raw price sebelum mendapatkan harga per-item rata-rata yang kemudian digabung dengan PPN dan Diskon per-item.
- **Fix Hapus Produk Massal (Bulk Delete)**: Menambahkan implementasi metode `AppModal.confirm()` pada pustaka UI komponen (`public/js/components.js`) yang sebelumnya belum tersedia, sehingga mengaktifkan fitur hapus multi-produk di halaman index produk.

### File yang Diubah
- `app/views/products/create.php` & `edit.php` — mengubah `$label` JS injection ke `json_encode`.
- `app/views/suppliers/index.php` — mengubah `$label` JS injection ke `json_encode`.
- `app/views/purchases/create.php` — mengubah `$label` JS injection ke `json_encode`.
- `docs/AI/ai-instructions.md` — menambahkan rule security baru terkait PHP to JS injection.
- `app/views/sales/pos.php` — logic `recalcItemPrice`.
- `public/js/components.js` — penambahan `AppModal.confirm()`.
- `app/views/layouts/app.php` & `sw.js` — PWA Cache Buster v5.9 & SW v6.7.


## [2026-05-24] — Bugfix Tier Harga & Live Search

**Tipe:** Minor / Hotfix
**Modul:** UI/UX, Products
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Fix Tombol Tambah Harga Tier**: Menyelesaikan isu dimana tombol tidak bisa diklik pada produk yang belum memiliki tier harga. Perbaikan dilakukan dengan memastikan `initQtyTiers` selalu terinisialisasi walaupun jumlah tier `0`.
- **Fix Live Search Produk**: Memperbaiki pencarian real-time (tanpa enter) di halaman Produk yang rusak diakibatkan *Syntax Error* pada blok `try...catch` (ada blok `catch` yatim piatu yang tertinggal dari modifikasi sebelumnya).
- **PWA Update**: Meningkatkan versi `CACHE_NAME` dan aset menjadi versi `4.9` agar langsung ber-efek di mobile app.

### File yang Diubah
- `app/views/products/edit.php`
- `app/views/products/index.php`
- `app/views/layouts/app.php` & `sw.js`

---

## [2026-05-24] — AI Scan Kemasan Cerdas & Keamanan Harga Jual

**Tipe:** Mayor
**Modul:** AI, Pembelian (Purchases)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **AI Extract Unit Price**: Memperbarui instruksi prompt AI (`ApiController.php` & tabel `app_settings`) agar mengeluarkan attribut `unit_price` (harga per satuan kemasan) dan `total_price` serta mempertegas deteksi kolom nama `unit` (kemasan) di invoice.
- **Auto-Selection Kemasan**: Mengubah logika pada `purchases/create.php` agar saat hasil scan AI dimasukkan ke keranjang, sistem akan otomatis memilih level kemasan yang tepat (Level 1, 2, 3, dst). 
  - *Prioritas 1*: Pencocokan nama satuan AI dengan satuan produk (Karton, Box, Renceng, dll).
  - *Prioritas 2*: Jika tidak jelas, sistem mencari harga level kemasan yang paling mendekati dengan hasil hitungan AI.
- **Keamanan Harga Jual**: AI tidak lagi menimpa harga level dasar secara default, tetapi merubah harga beli (modal) khusus untuk level kemasan yang terpilih secara spesifik, tanpa menyentuh harga jual ritel dan grosir.

### File yang Diubah
- `app/controllers/ApiController.php` — Update default prompt AI & extraksi `unit_price`, `total_price`.
- `app/views/purchases/create.php` — Logika pemetaan kemasan otomatis.
- `app/views/layouts/app.php` & `sw.js` — PWA Cache Buster v4.8.
- `update_prompt.php` — Script untuk menimpa config prompt di database (sudah dieksekusi).

---
## [2026-05-24] — PWA Fast Fallback & Mode Referensi Sinkronisasi Harga

**Tipe:** Mayor
**Modul:** PWA (sw.js), Products (create.php, edit.php, ApiController.php, ProductModel.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Timeout-based Network First Strategy**: Mengubah strategi PWA service worker (`sw.js`) untuk request navigasi HTML. Jika koneksi lambat dan server tidak merespon dalam 800ms, SW akan instan menampilkan halaman dari cache lokal sementara update data berjalan di background. Ini menghasilkan load yang "instan" ala AppSheet walaupun sinyal lemah.
- **Sinkronisasi Harga Tier (Mode Referensi)**: Ketika user menduplikasi varian produk referensi (`create.php` / `edit.php`), `qty_prices` (harga khusus kuantitas) kini juga disalin utuh ke produk baru.
- **Lock Harga Custom (Mode Referensi)**: Opsi "Harga Modal Custom" dan "Harga Jual Custom" kini dicentang otomatis saat menduplikasi dari referensi. Ini mengikat agar harga modal dan jual ecer yang disalin tidak ter-overwrite (berubah otomatis) oleh kalkulasi saat user memodifikasi harga base level 1.
- **PWA Cache Busting**: Meningkatkan `CACHE_NAME` ke `alfarezmart-v4.7` dan asset version ke `?v=4.7`.

### File yang Diubah/Dibuat
- `sw.js` — Implementasi timeout 800ms di strategi fetch request navigasi.
- `app/views/layouts/app.php` — Bump APP_VERSION & asset version.
- `app/views/products/create.php` — Penambahan hidden input untuk menyertakan `qty_prices` dalam payload serta auto-check custom price toggles saat prefill.
- `app/views/products/edit.php` — Menjalankan `initQtyTiers` pada object prefill dan auto-check custom price toggles.
- `app/controllers/ApiController.php` — Membaca json tier harga pada endpoint `createProduct`.
- `app/models/ProductModel.php` — Menyimpan `qty_prices` beruntun saat memanggil `createWithDetails`.

### Catatan
- Fitur ini sangat meningkatkan User Experience di lapangan dimana sinyal seluler tidak stabil.

---


## [2026-05-24] — Fix Popup "Data Tidak Tersimpan" Saat Simpan Edit Produk

**Tipe:** Hotfix
**Modul:** Products (edit.php), ApiController.php
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Fix Unsaved Changes Popup pada Save**: Menambahkan `window.hasUnsavedChanges = false` sebelum redirect setelah berhasil simpan di `submitProduct()`. Sebelumnya, flag unsaved changes tetap `true` saat redirect terjadi, menyebabkan popup `beforeunload` browser muncul dan memblokir navigasi, sehingga user mengira data tidak tersimpan.
- **Redirect ke Daftar Produk**: Mengubah redirect setelah simpan dari halaman detail produk (`products/{id}`) ke halaman daftar produk (`products`) agar user langsung kembali ke daftar produk setelah edit.
- **Support Update `contained_qty` pada Packaging Existing**: Menambahkan pengiriman field `contained_qty` di payload frontend saat update packaging yang sudah ada, dan menambahkan handler di backend `updatePackaging()` untuk menyimpan perubahan `contained_qty` serta merecalculate `base_qty` secara otomatis untuk semua level kemasan produk terkait.

### File yang Diubah/Dibuat
- `app/views/products/edit.php` — fix `window.hasUnsavedChanges`, redirect ke daftar produk, tambah `contained_qty` di payload update packaging
- `app/controllers/ApiController.php` — support `contained_qty` update dan recalculate `base_qty` di method `updatePackaging()`

### Catatan
- Root cause popup: `app.js` men-track semua input changes via global `beforeunload` listener. Saat AJAX save berhasil dan redirect via `setTimeout`, flag tidak di-reset sehingga browser menampilkan popup konfirmasi keluar.
- Data sebenarnya sudah tersimpan via AJAX, tetapi popup menyebabkan kebingungan user dan di beberapa device memblokir navigasi.

---

## [2026-05-21] — Revamp Purchase Input & Bulk Input Massal UI/UX and Code Cleanup

**Tipe:** Mayor
**Modul:** Purchases (create.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **UI/UX Revamp of Purchase Input**: Refactor and streamline the standard cart and modal bulk input forms to use the unified modern propagation engine.
- **Unified Price Propagation Engine**: Main item level modifications (buy price, PPN, and discounts) dynamically scale and propagate across all other packaging sizes automatically, with flat discount rupiah values scaling based on quantity ratios and percentage discounts applying uniformly.
- **Mini Pricing statistics Table**: Standardized interactive tables for each item rendering Modal Nett (after discount and PPN), Ecer, and Grosir prices with their margins dynamically computed in real-time.
- **Smart Price Trend Banner**: Integrated a visual price change comparison comparing the calculated unit cost of the purchase against the last purchased supplier price.
- **Code Cleanup**: Removed massive chunks of obsolete, duplicate, and redundant code (totaling ~850 lines) from `app/views/purchases/create.php` that were causing potential runtime and structural overlaps in the script block.

### File yang Diubah/Dibuat
- `app/views/purchases/create.php` — Complete UI overhaul, implementation of modern unified propagation, and deletion of duplicate/redundant code.

### Catatan
- Successfully ran PHP linter (`php -l`) and confirmed file is syntactically flawless.
- Changes are fully committed and pushed to GitHub main branch.

---

## [2026-05-21] — Database AI Invoice Prompt Sync & User Level Akses Style Consistency

**Tipe:** Minor
**Modul:** Settings, User Management, AI Invoice Scan
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Sinkronisasi AI Prompt Database**: Menyinkronkan nilai `ai_invoice_prompt` di tabel `app_settings` dengan script database agar default prompt versi terbaru yang mengenali atribut `size`, multi-pack, supplier_code, dan format abbreviating harga bisa langsung diakses oleh sistem AI maupun visualisasi halaman Pengaturan Sistem & AI.
- **Keseragaman Style Level Akses**: Refactor dropdown `<select>` di modal tambah user (`users/index.php`) untuk memakai class `.form-select-dark` (dari `.form-control-dark`) agar terintegrasi dengan style modern SVG chevron.

### File yang Diubah/Dibuat
- `app/views/users/index.php` — Ubah kelas `mu_level` select dari `form-control-dark` ke `form-select-dark`
- `docs/AI/CURRENT_STATE.md` — Tambah catatan sesi Pekerjaan Terakhir
- `docs/AI/CHANGE_LOG.md` — Tambah catatan log perubahan

---

## [2026-05-21] — POS Custom Product: Satuan Searchbox dari Master Data

**Tipe:** Minor
**Modul:** Sales POS (pos.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Satuan Field → Searchbox Master Data**:
  - Mengganti field input teks bebas `Satuan` di modal barang custom (`openCustomProductModal()`) dengan searchbox autocomplete yang data listnya diambil dari endpoint `/api/units` (master data satuan).
  - Implementasi menggunakan `<input type="hidden" id="customItemUnit">` sebagai value holder dan `<input type="text" id="customItemUnitSearch">` sebagai UI input yang dapat diketik untuk filter.
  - Dropdown muncul saat field difokus, menampilkan daftar satuan beserta singkatan (jika ada), dan menutup otomatis setelah pilihan dibuat atau field kehilangan fokus.
  - User **tidak bisa input teks bebas** — harus memilih dari daftar master data satuan.
  - Default otomatis ke "Pcs" jika tersedia di master data, atau satuan pertama dalam daftar.
  - Setelah pilih satuan, fokus otomatis pindah ke field Total Harga untuk mempercepat input.
  - Pesan validasi diperbarui: `'Satuan wajib dipilih dari daftar'` (sebelumnya `'Satuan wajib diisi'`).

### File yang Diubah/Dibuat
- `app/views/sales/pos.php` — refactor `openCustomProductModal`: ganti Satuan text input jadi searchbox dengan fetch `/api/units`, dropdown filter, hidden input, auto-default, auto-focus.

### Catatan
- Tidak ada perubahan backend. Endpoint `/api/units` sudah tersedia dan hanya perlu dikonsumsi dari frontend.
- Tidak breaking bagi flow `addCustomProductToCart` — nilai satuan tetap dibaca dari `customItemUnit` (hidden input).

---

## [2026-05-21] — POS Custom Product Quantity & Unit Price Calculation Enhancement

**Tipe:** Minor
**Modul:** Sales POS (pos.php), Layout (app.php), PWA Service Worker (sw.js)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Custom Product Modal Quantity & Input Upgrades**:
  - Mengubah form input Qty (`customItemQty`) di modal barang custom untuk mendukung kuantitas desimal (misalnya 0.5 kg atau 1.5 meter) dengan mengatur `min="0.01"` dan `step="any"`.
  - Mengubah parsing Qty di frontend `onSubmit` modal dari `parseInt` menjadi `parseFloat` untuk memfasilitasi transaksi dengan kuantitas pecahan, serta meningkatkan validasi agar lebih aman.
- **Cart Presentation & Unit Price Display**:
  - Memperbarui visual cart di Kasir POS (`renderCart` & `updateCartItemDom`): untuk barang custom, kini menampilkan detail harga per pcs/unit yang dihitung secara real-time dari `Total Harga / Qty` di samping total harganya (contoh: `Rp2.000 / Pcs (Total Rp6.000)` alih-alih hanya `Total Rp6.000 (3 Pcs)`). Hal ini memperjelas informasi harga per unit sebelum checkout.
- **Cache Busting & SW Cache Bump**:
  - Meningkatkan versi cache Service Worker (`sw.js`) dari `alfarezmart-v1.94` ke `alfarezmart-v1.95`.
  - Meningkatkan versi query parameter asset (`app.php`) dari `?v=3.8` ke `?v=3.9` untuk memastikan reload script and styles yang bersih bagi user.

### File yang Diubah/Dibuat
- `app/views/sales/pos.php` — dukung float Qty, parse as float, perbarui renderCart dan updateCartItemDom untuk menampilkan kalkulasi harga satuan.
- `app/views/layouts/app.php` — bump static asset version ke v3.9.
- `sw.js` — bump Service Worker cache version ke v1.95.

### Catatan
- Perubahan ini 100% backward-compatible, tidak mengubah data model ataupun struktur tabel, dan memastikan detail `unit_price` dan `total_price` tersimpan akurat di database `sale_items`.

## [2026-05-21] — Restore Product Edit Layout & POS Barang Custom

**Tipe:** Mayor
**Modul:** Products (edit.php), Sales POS (pos.php), SaleModel, Database Migration
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Restore & Perbaikan Form Edit Produk (`edit.php`)**:
  - Memastikan layout form kembali bersih dan fungsional — checkbox "Produk Multivarian" toggle brand/varian/jenis berjalan dengan benar.
  - Mengganti modal popup supplier info dengan inline collapsible card **"Informasi Supplier (Opsional)"** menggunakan accordion chevron di dalam form. Panel ditampilkan/disembunyikan via `toggleSupplierInfo()`.
  - Field `supplier_product_code` dan `supplier_invoice_name` kini berada langsung di dalam form — tidak perlu modal terpisah — sehingga ikut ter-submit otomatis saat form utama disimpan.
  - Panel otomatis expand jika produk sudah memiliki data supplier (self-reveal on load).
- **Fitur Barang Custom di Kasir POS (`pos.php`)**:
  - Menambahkan tombol `+ Barang Custom` di header area scan/cari produk.
  - Implementasi `openCustomProductModal()` — modal dengan field Nama Barang, Satuan, dan Total Harga (Rp).
  - Implementasi `addCustomProductToCart(name, unit, totalPrice)` — inject item ke cart dengan `product_id: 'CUSTOM'`, `is_custom: true`, dan `use_custom_price: true`.
  - Checkout payload diperluas dengan field `is_custom`, `custom_name`, `custom_unit` per item.
- **Backend Custom Item (`SaleModel.php`)**:
  - Menambah metode private `getPlaceholderProductAndPackaging()` — self-healing resolver untuk produk placeholder `CUSTOM` dan packagingnya, dengan in-request cache `$placeholderCache`.
  - `createWithDetails()` diperluas: deteksi `is_custom` per item, skip stock deduction, simpan `custom_name` dan `custom_unit` ke tabel `sale_items`.
  - `getTransactionDetails()` diupdate menggunakan `COALESCE(si.custom_name, p.full_name)` dll. agar struk dan laporan menampilkan nama custom secara transparan.
- **Database Migration (`database/migrate_custom_items.php`)**:
  - Kolom `custom_name VARCHAR(255) NULL` dan `custom_unit VARCHAR(50) NULL` ditambahkan ke tabel `sale_items`.
  - Produk placeholder `CUSTOM` (code='CUSTOM'), stock row, dan packaging level 1 berhasil dibuat/diverifikasi di database.

### File yang Diubah/Dibuat
- `app/views/products/edit.php` — restore layout, ganti modal supplier info jadi inline collapsible panel.
- `app/views/sales/pos.php` — tambah tombol + Barang Custom, modal input, fungsi addCustomProductToCart, perbarui checkout payload.
- `app/models/SaleModel.php` — tambah getPlaceholderProductAndPackaging(), perbarui createWithDetails() dan getTransactionDetails().
- `database/migrate_custom_items.php` — script migrasi one-shot [NEW].

### Catatan
- Barang custom tidak mengurangi stok produk manapun — menggunakan produk placeholder CUSTOM sebagai anchor FK.
- `getPlaceholderProductAndPackaging()` bersifat self-healing: jika placeholder belum ada, dibuat otomatis dalam transaksi yang sama.
- Struk, detail transaksi, dan laporan secara otomatis menampilkan nama/satuan custom tanpa perubahan di View/Controller lain.

---

## [2026-05-20] — Penyeragaman Desain Dropdown & Searchbox Elegant

**Tipe:** Refactor
**Modul:** UI Components (components.css, debts/index.php, finance/index.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Custom Select Dropdown Style**: Memodifikasi class `.form-select-dark` di `components.css` dengan menerapkan `appearance: none;` untuk menghilangkan default chevron browser, dan menggantinya dengan custom SVG chevron elegant berwarna `#8892b0` (berubah menjadi warna merah `#e63946` ketika fokus). Hal ini menjamin konsistensi visual di iOS, Android, dan desktop browser.
- **Small Select Dropdown**: Membuat class `.form-select-dark-sm` untuk inline select dropdown berukuran kecil yang tetap berpenampilan seragam dengan custom chevron SVG. Diterapkan pada filter pos di halaman Catatan Keuangan Harian.
- **Custom Search Box Wrapper**: Membuat class `.search-input-wrapper` di `components.css` untuk membungkus elemen input pencarian teks agar memiliki border glow berwarna merah (`var(--primary)`) dan soft shadow saat fokus, serta merapikan ikon kaca pembesar di dalamnya. Diterapkan pada seluruh input pencarian di halaman Catatan Hutang/Piutang.

### File yang Diubah/Dibuat
- `public/css/components.css` — penambahan rules CSS untuk select modern dan search wrapper.
- `app/views/debts/index.php` — mengubah filter search wrapper di 3 tab (Piutang, Hutang Toko, Pelanggan) menggunakan `search-input-wrapper`.
- `app/views/finance/index.php` — mengubah filter pos menggunakan `form-select-dark-sm`.

### Catatan
- Perubahan ini memberikan UX yang jauh lebih stylish, premium, dan konsisten di seluruh browser modern.

---

## [2026-05-20] — Modul Catatan Keuangan Harian (Pendapatan & Pengeluaran)

**Tipe:** Mayor
**Modul:** Finance (FinanceModel, FinanceController, ApiController, Routes, dashboard/index.php, finance/index.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Model & Controller**: Membuat `FinanceModel.php` untuk melayani operasi CRUD dan query ringkasan harian serta breakdown saldo per pos keuangan. Membuat `FinanceController.php` untuk melayani rute web utama `/finance`.
- **API & Routing**: Mendaftarkan rute `/finance` serta 5 API endpoint (`/api/finance/summary`, `/api/finance/logs`, CRUD log transaksi) di `Routes.php` dan `ApiController.php`.
- **Dashboard Status Integration**: Menambahkan visualisasi grid 2 kolom "Status Hari Ini" di atas grid "Ringkasan Data" pada `dashboard/index.php`:
  - Kolom Kiri: Card **Stok Terendah** (jumlah produk dengan stok <= 5) & Card **Keuangan Harian** (menampilkan Net Balance harian, total pemasukan, total pengeluaran dengan icon dompet `bi-wallet2` yang link ke `/finance`).
  - Kolom Kanan: Card **Omset Hari Ini** (menampilkan omset harian dari POS secara realtime).
- **Finance Manager UI**: Membuat tampilan antarmuka keuangan harian yang mobile-first di `app/views/finance/index.php` dengan visual comparison progress bar (realtime ratio pemasukan/pengeluaran), pemilih tanggal dinamis, breakdown per pos keuangan (`Uang Laci`, `Uang Pulsa`, `Uang Beras`, `Uang Rokok`), filter pencarian log, serta modal input transaksi CRUD.

### File yang Diubah/Dibuat
- `app/models/FinanceModel.php` — model data dan operasi database [NEW].
- `app/controllers/FinanceController.php` — controller rute halaman utama [NEW].
- `app/controllers/DashboardController.php` — query & pasing statistik harian (omset POS, stok rendah, ringkasan kas).
- `app/controllers/ApiController.php` — penambahan 5 endpoint API CRUD & Summary.
- `app/config/Routes.php` — pendaftaran rute web dan API baru.
- `app/views/dashboard/index.php` — integrasi grid visual "Status Hari Ini" (dompet, stok terendah, omset).
- `app/views/finance/index.php` — halaman kelola kas harian per pos [NEW].
- `app/models/ProductModel.php` — query statistik hitung low stock count.

### Catatan
- PWA caching tetap aman. Pengguna disarankan untuk memuat ulang halaman dashboard untuk melihat widget status keuangan baru yang dinamis.

---

## [2026-05-20] — Modul Catatan Hutang (Piutang Pelanggan & Hutang Toko)

**Tipe:** Mayor
**Modul:** Debts & Customers (DebtModel, DebtController, ApiController, Routes, debts/index.php, dashboard/index.php, setup.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Database Schema Expansion**: Menambahkan kolom `notes` ke tabel `customers` dan menginisialisasi 4 tabel baru (`customer_debts`, `customer_debt_payments`, `shop_debts`, `shop_debt_payments`) di `database/setup.php`.
- **Model & Controller**: Membuat `DebtModel.php` (business logic transaksi & cicilan) dan `DebtController.php` (page entry). Menghapus referensi kolom `s.phone` yang tidak valid pada query `getShopDebts()` dan `getShopDebtById()`.
- **API & Routing**: Mendaftarkan rute `/debts` serta 12 API endpoint penunjang CRUD pelanggan & hutang/piutang serta pencatatan cicilan di `Routes.php` dan `ApiController.php`.
- **Unified Manager UI**: Membuat dashboard hutang/piutang modern di `app/views/debts/index.php` lengkap dengan switcher tab, visualisasi progress pelunasan, pencatatan cicilan real-time, dan fitur identifikasi pelanggan tanpa nama (ciri fisik).
- **Dashboard Menu Integration**: Memindahkan dan mengaktifkan menu "Catatan Hutang" dari section "Segera Hadir" ke section "Laporan & Riwayat" pada dashboard.

### File yang Diubah/Dibuat
- `database/setup.php` — modifikasi skema tabel.
- `app/models/DebtModel.php` — model data dan operasi database [NEW].
- `app/controllers/DebtController.php` — controller rute halaman utama [NEW].
- `app/controllers/ApiController.php` — penambahan 12 endpoint API CRUD & transaksi.
- `app/config/Routes.php` — pendaftaran rute web dan API baru.
- `app/views/debts/index.php` — tampilan manager hutang-piutang & pelanggan [NEW].
- `app/views/dashboard/index.php` — pemindahan link menu dashboard ke halaman aktif.

### Catatan
- PWA caching tetap aman karena perubahan halaman dinamis didukung oleh asset-level reload. Pengguna disarankan untuk reload aplikasi sekali untuk memperbarui layout dashboard.

---

## [2026-05-20] — Fitur PPN & Diskon Per Item Terintegrasi Database & Detail View

**Tipe:** Mayor
**Modul:** Purchase (PurchaseModel, create.php, show.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Database Persistence**: Menambahkan penyimpanan kolom `ppn_percent`, `discount_percent`, `discount_amount`, dan `nett_price` di tabel `purchase_items` saat proses penyimpanan barang masuk via `PurchaseModel@createWithDetails`.
- **UI PPN & Diskon**: Mengembalikan input PPN (%) dan Diskon (Rp/%) di form reguler, modal harga kemasan, serta form input massal (bulk) pada file `create.php`.
- **Real-time Nett Price & Margin Calculation**: Perhitungan `Harga Nett = Beli + PPN - Diskon` dihitung real-time. Margin retail dan grosir dihitung berdasarkan harga nett tersebut (bukan lagi harga beli kotor).
- **Detail View Update**: Memperbarui `show.php` untuk menampilkan informasi PPN, Diskon, dan Harga Nett per item yang telah disimpan dalam database di halaman detail barang masuk.

### File yang Diubah/Dibuat
- `app/models/PurchaseModel.php` — query penyimpanan detail PPN, Diskon, dan Nett.
- `app/views/purchases/create.php` — form input PPN/Diskon, modal detail, bulk input, logic JS sinkronisasi margin & nett.
- `app/views/purchases/show.php` — penampilan rincian harga beli kotor, diskon, PPN, dan harga nett per item.

### Catatan
- Margin ecer/grosir kini terhitung secara akurat dan fair berdasarkan modal riil setelah PPN & Diskon per item, yang disimpan dan bisa diaudit kapan saja lewat halaman detail barang masuk.

---

## [2026-05-20] — Restorasi Layout & Penyempurnaan Alur Input Barang Masuk

**Tipe:** Mayor
**Modul:** Purchase (create.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Reposisi Form**: Mengubah tata letak Step 1 agar input **Sales** (SearchBox) berada di posisi paling atas, diikuti oleh input **Supplier** di bawahnya.
- **Z-Index Optimization**: Menyesuaikan `z-index` agar popup dropdown dari Sales SearchBox (`z-index: 20`) menimpa area Supplier SearchBox (`z-index: 10`) secara estetik.
- **Client-Side Filtering & Warning Badge**: 
  - Mengubah fungsi `performProductSearch()` agar selalu mengambil data produk dari endpoint `/api/purchases/search-products` jika supplier terpilih. Hal ini memastikan status `is_supplier_product` selalu didapatkan.
  - Melakukan penyaringan produk di sisi client jika checkbox filter dicentang.
  - Menampilkan badge **"Milik Supplier Lain"** (warna merah) untuk memperingatkan pengguna apabila mencari produk non-supplier.
- **Penyelamatan Data PPN & Diskon Bulk**: Memperbaiki logika `onSubmit` pada modal bulk input agar data PPN, Diskon, dan harga nett tersimpan ke dalam objek keranjang belanja (`purchaseItems`) serta list kemasannya (`updatedPkgs`).
- **Fix Syntax Error**: Menghapus redeklarasi variabel `const bulkItem` pada fungsi `openBulkPkgPanel` yang memicu error Javascript.

### File yang Diubah/Dibuat
- `app/views/purchases/create.php` — perbaikan tata letak form, logika penyaringan pencarian produk, debugging syntax JS, dan integrasi data bulk input.

### Catatan
- Perubahan ini menyelesaikan masalah rusaknya tampilan awal dan alur "Sales -> Auto Supplier" di halaman input barang masuk.

---

## [2026-05-20] — PPN & Diskon Per Barang di Input Barang Masuk

**Tipe:** Mayor
**Modul:** Purchase (create.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Daftar Barang**: Tambah form PPN (%) dan Diskon (Rp/%) per item. Harga Nett = Modal + PPN - Diskon dihitung realtime dengan breakdown visual.
- **Margin** di Daftar Barang kini berbasis Harga Nett, bukan raw buy_price.
- **Info kemasan lain** di Daftar Barang: mini-summary Modal Nett, Ecer, Grosir + margin vs Nett untuk kemasan selain kemasan aktif (mengikuti pola bulk modal).
- **Input Massal (Bulk)**: Tambah form PPN & Diskon per produk. Margin(Nett) dihitung dari harga nett setelah PPN/diskon. Tombol "Atur Harga Kemasan Lainnya" kini memunculkan panel kemasan dengan benar.
- **Modal "Atur Harga Kemasan Lainnya"**: Kini setiap level kemasan memiliki form PPN dan Diskon mandiri. Harga Nett per kemasan digunakan sebagai acuan margin saat mengatur harga ecer & grosir.
- **Helper functions baru**: `calcItemNett()`, `buildNettInfo()`, `updateItemPpnDiskon()`, `buildPkgMiniSummaryHtml()`.

### File yang Diubah/Dibuat
- `app/views/purchases/create.php` — seluruh perubahan ada di file ini

### Catatan
- PPN dan Diskon bersifat per-item (bukan di level total invoice) sehingga setiap barang bisa punya PPN/diskon berbeda.
- Harga Nett tidak mempengaruhi `buy_price` yang disimpan ke DB — hanya dipakai sebagai acuan margin di UI.
- Untuk menyimpan harga modal yang sudah ter-adjusted PPN/diskon ke DB, user perlu input manual ke field Harga Modal/Beli.

---

## [2026-05-20] — Inisialisasi Dokumentasi AI

**Tipe:** Dokumentasi  
**Modul:** docs/AI  
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- Revisi `docs/AI/ai-instructions.md` — ditambahkan rules PWA, mobile-first design, tech stack table, design system lengkap, tabel komponen UI, dan format output ringkasan yang lebih terstruktur.
- Buat `docs/AI/BLUEPRINT.md` — dokumentasi arsitektur lengkap: struktur direktori, routing, API endpoints, database schema ringkasan, design system, PWA architecture, modul detail, AJAX pattern, security pattern, area sensitif.
- Buat `docs/AI/CURRENT_STATE.md` — state development terkini: status modul, known issues, pending tasks, keputusan teknis, risiko.
- Buat `docs/AI/CHANGE_LOG.md` — file ini, log perubahan awal dengan rekonstruksi histori dari conversation logs.
- Buat `docs/AI/PROMPT_TEMPLATE.md` — template prompt standar untuk task baru.

### File yang Diubah/Dibuat
- `docs/AI/ai-instructions.md` — direvisi lengkap
- `docs/AI/BLUEPRINT.md` — dibuat baru
- `docs/AI/CURRENT_STATE.md` — dibuat baru
- `docs/AI/CHANGE_LOG.md` — dibuat baru (file ini)
- `docs/AI/PROMPT_TEMPLATE.md` — dibuat baru

### Catatan
- Rekonstruksi histori di bawah berdasarkan conversation logs — mungkin tidak 100% lengkap untuk detail teknis per sesi.

---

## [2026-05-19] — Real-time Supplier Search & Navigation Optimization

**Tipe:** Mayor  
**Modul:** Supplier, Global Search, app.js  
**Dikerjakan oleh:** AI Agent

### Perubahan
- Implementasi real-time search supplier/sales rep tanpa perlu tekan Enter (auto-trigger on input).
- Search results ditampilkan sebagai dropdown, klik → scroll otomatis ke kartu supplier + highlight sementara.
- `app.js` diupdate: placeholder search overlay berubah jadi "Cari Nama Supplier atau Sales..." saat di halaman `/suppliers`.
- Supplier search menggunakan API `GET /api/suppliers/search?q=`.
- PWA cache diperbarui setelah perubahan JS.

### File yang Diubah/Dibuat
- `public/js/app.js` — context-aware search placeholder + supplier search handler
- `app/views/suppliers/index.php` — integrasi search hasil dropdown
- `app/controllers/ApiController.php` — endpoint `searchSuppliers`
- `sw.js` — update CACHE_NAME

---

## [2026-05-19] — Dashboard Grid Menu & Help Module Update

**Tipe:** Mayor  
**Modul:** Dashboard, Help  
**Dikerjakan oleh:** AI Agent

### Perubahan
- Dashboard diubah dari menu list menjadi grid berbasis kategori untuk navigasi yang lebih mudah.
- Help module diupdate dengan dokumentasi alur sistem terbaru, terminologi terbaru, dan troubleshooting fitur baru (real-time search, last-price analytics supplier).
- System-wide cleanup dan validasi kode.

### File yang Diubah/Dibuat
- `app/views/dashboard/index.php` — grid menu kategorisasi
- `app/views/help/index.php` — dokumentasi diperbarui lengkap

---

## [2026-05-18] — Master Data API Fix

**Tipe:** Hotfix  
**Modul:** Master Data, ApiController  
**Dikerjakan oleh:** AI Agent

### Perubahan
- Fix "Unexpected token" JSON parsing error saat menambah satuan (unit) baru dari Master Data.
- Root cause: response tidak berisi JSON bersih — ada HTML/whitespace sebelum JSON output.
- Fix: memastikan header `Content-Type: application/json` di-set sebelum output, tidak ada echo/HTML sebelum JSON.
- Validasi response header di frontend untuk handle status 200 dengan body JSON yang benar.

### File yang Diubah/Dibuat
- `app/controllers/ApiController.php` — fix JSON response untuk `createUnit`
- `public/js/components.js` — perbaikan error handling AJAX create unit

---

## [2026-05-18] — Timezone Standardization

**Tipe:** Hotfix  
**Modul:** Config, Utils  
**Dikerjakan oleh:** AI Agent

### Perubahan
- Standardisasi timezone ke `Asia/Jakarta` (GMT+7) di seluruh aplikasi.
- Fix relative time formatting di JS ("baru saja" menjadi akurat, bukan "7 jam lalu").
- Validasi `date_default_timezone_set` di `app/config/App.php`.

### File yang Diubah/Dibuat
- `app/config/App.php` — `date_default_timezone_set('Asia/Jakarta')`
- `public/js/utils.js` — penyesuaian relative time formatting

---

## [2026-05-18] — Bulk Purchase & POS Enhancement

**Tipe:** Mayor  
**Modul:** Purchase, POS, Barcode, Printer  
**Dikerjakan oleh:** AI Agent

### Perubahan
- Tampilan kartu produk di bulk purchase: info harga (modal, ecer, grosir, margin, selisih harga).
- Dual-engine barcode scanner: ZXing-JS (utama) + html5-qrcode (fallback) untuk sensitivitas tinggi.
- Tier pricing POS: harga otomatis berubah sesuai qty di cart (qty-pricing.js).
- Thermal printer: persistensi header/footer struk, manajemen logo.
- "Total Harga" input di bulk purchase: kalkulasi unit price otomatis dari total, menghindari desimal tidak akurat.

### File yang Diubah/Dibuat
- `app/views/purchases/create.php` — tampilan harga di kartu produk + total harga input
- `public/js/qty-pricing.js` — logika tier pricing POS
- `public/js/barcode.js` — dual-engine scanner (ZXing + html5-qrcode)
- `public/js/printer.js` — persistensi header/footer, manajemen logo

---

## [2026-05-18] — Pricing Logic Refinement

**Tipe:** Mayor  
**Modul:** Product, POS, Purchase  
**Dikerjakan oleh:** AI Agent

### Perubahan
- Fix margin calculation dan state sync di purchase modal (harga terkunci vs bebas).
- Search API produk dilengkapi dengan data packaging dan tier pricing lengkap.
- Frontend POS: ingesti dan aplikasi tier pricing berbasis kuantitas di cart.

### File yang Diubah/Dibuat
- `app/controllers/ApiController.php` — extend search product response dengan packaging + tier pricing
- `app/views/purchases/create.php` — fix margin state
- `public/js/qty-pricing.js` — implementasi awal tier pricing cart logic

---

## [2026-05-17] — Product Packaging & Pricing Management

**Tipe:** Mayor  
**Modul:** Product  
**Dikerjakan oleh:** AI Agent

### Perubahan
- Implementasi "ON DELETE SET NULL" untuk FK packaging agar histori tidak error saat modifikasi.
- Product editor: deteksi otomatis custom pricing, state management harga manual.
- Tier pricing: "Total Harga" input logic untuk mencegah inakurasi desimal.
- Tampilan profit (Rp) dan persentase margin di interface produk secara real-time.
- Fix IDE diagnostic warnings di views produk dan POS.

### File yang Diubah/Dibuat
- `app/models/ProductModel.php` — ON DELETE SET NULL, query packaging
- `app/views/products/show.php` — tampilan margin & profit
- `app/views/products/edit.php` — custom pricing detection & state
- `public/js/packaging-prices.js` — total harga input logic

---

## [2026-05-16] — Cleanup Duplicate Records

**Tipe:** Hotfix  
**Modul:** Database, Supplier, Product  
**Dikerjakan oleh:** AI Agent

### Perubahan
- Identifikasi dan penghapusan data duplikat supplier dan produk di database.
- Migrasi referensi (pembelian, penjualan, stok) ke record primer sebelum hapus duplikat.
- Prioritas retensi: record dengan data paling lengkap.

### File yang Diubah/Dibuat
- `cleanup_bulk_fast.php` — script cleanup (review sebelum hapus)
- Database: data duplikat dibersihkan

### Catatan
- File `cleanup_bulk_fast.php` di root masih ada — perlu review apakah sudah aman dihapus.
