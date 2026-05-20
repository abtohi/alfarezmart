# CURRENT STATE — AlfarezMart

> File ini mencatat kondisi development terkini project AlfarezMart.
> Update file ini setiap kali selesai mengerjakan task, menemukan kendala baru, atau membuat keputusan teknis penting.
> AI membaca file ini untuk memahami konteks terakhir tanpa perlu membaca semua kode.

---

## Status Umum

| Item | Nilai |
|------|-------|
| **Status** | Aktif dikembangkan (Production-ready, fitur lanjutan sedang ditambah) |
| **Versi Cache SW** | `alfarezmart-v1.93` |
| **Versi Asset** | `?v=3.8` (di `app/views/layouts/app.php`) |
| **PHP Version** | XAMPP (cek `php -v`) |
| **Timezone** | Asia/Jakarta (GMT+7) |
| **Last Updated** | 2026-05-20 |

---

## Pekerjaan Terakhir

### Sesi: 2026-05-20 — Modul Keuangan Harian (Pendapatan & Pengeluaran)

**Yang dikerjakan:**
1. **Model & Controller** — Membuat `FinanceModel.php` untuk melayani operasi CRUD dan query harian, serta `FinanceController.php` untuk render web view utama `/finance`.
2. **API & Routing** — Mendaftarkan rute web `/finance` serta 5 API endpoint (`/api/finance/...`) untuk kelola transaksi dan dashboard di `Routes.php` dan `ApiController.php`.
3. **Dashboard Status Integration** — Menambahkan grid status 2 kolom "Status Hari Ini" di atas grid "Ringkasan Data" pada `dashboard/index.php`. Menampilkan stok terendah (stok <= 5), omset hari ini (POS), dan net balance harian (icon dompet).
4. **Finance Manager UI** — Membuat view `app/views/finance/index.php` dengan visual comparison progress bar, pemilih tanggal dinamis, breakdown per pos keuangan (`Uang Laci`, `Uang Pulsa`, `Uang Beras`, `Uang Rokok`), filter pencarian log, serta modal input transaksi CRUD.

### Sesi: 2026-05-20 — Modul Catatan Hutang (Piutang Pelanggan & Hutang Toko)

**Yang dikerjakan:**
1. **Database Schema** — Memperbarui `database/setup.php` untuk menambahkan kolom `notes` ke tabel `customers`, serta membuat tabel `customer_debts`, `customer_debt_payments`, `shop_debts`, dan `shop_debt_payments`. Berhasil mengeksekusi migrasi database.
2. **Model Implementasi** — Membuat `DebtModel.php` untuk melayani operasi CRUD dan pencatatan cicilan pelanggan serta hutang toko ke pihak ketiga/supplier. Memperbaiki error `s.phone` pada query `getShopDebts()` dan `getShopDebtById()` karena tabel `suppliers` tidak memiliki kolom `phone` (kolom telepon hanya ada di tabel `sales_reps`).
3. **Controller & Routing** — Membuat `DebtController.php` dan mendaftarkan rute web `/debts` serta 12 endpoint API di `app/config/Routes.php` dan `ApiController.php`.
4. **Unified Manager UI** — Membuat `app/views/debts/index.php` dengan dashboard, tab switcher (Piutang, Hutang Toko, Pelanggan), form modal modern (menggunakan `AppModal`), dan fitur penanganan pelanggan tanpa nama/ciri fisik secara dinamis.
5. **Dashboard Integration** — Mengaktifkan menu "Catatan Hutang" di dashboard (`app/views/dashboard/index.php`) dan memindahkannya dari kategori "Segera Hadir" ke "Laporan & Riwayat".

---

### Sesi: 2026-05-20 (Finalized) — PPN & Diskon Per Item di Input Barang Masuk

**Yang dikerjakan:**
1. **Penyimpanan Database** — Memperbarui `PurchaseModel@createWithDetails` untuk menyimpan `ppn_percent`, `discount_percent`, `discount_amount`, dan `nett_price` ke tabel `purchase_items`.
2. **Form Barang Masuk & Input Massal (`create.php`)** — Mengembalikan input PPN (%) dan Diskon (Rp/%) di form reguler, form modal level kemasan, serta form input massal (bulk). Perhitungan Harga Nett dan visualisasi margin berbasis Nett disinkronkan secara real-time.
3. **Detail Barang Masuk (`show.php`)** — Menampilkan informasi PPN, Diskon, dan Harga Nett per item yang tersimpan di dalam database pada halaman detail transaksi pembelian.

---

### Sesi: 2026-05-20 (Update) — Form Sales/Supplier Fix & Fallback Implementation

**Yang dikerjakan:**
1. **Diagnosa Form Sales/Supplier** — Ditemukan bahwa form sudah ada di kode tapi belum visible di halaman
   - **Root cause**: Kemungkinan SearchBox component gagal render atau ada error JavaScript
   - **Solusi**: Implementasi fallback dropdown yang selalu visible

2. **Implementasi Fallback Mechanism**:
   - Added fallback `<select>` dropdown untuk sales rep selection (visible by default)
   - SearchBox tetap menjadi primary component (menimpa fallback jika berhasil di-load)
   - Dropdown otomatis tersembunyi jika SearchBox berhasil render
   - Fallback dropdown tetap visible jika SearchBox gagal atau tidak tersedia
   
3. **Improved Error Handling & Debugging**:
   - Added console.log statements untuk tracking SearchBox initialization
   - Try-catch block untuk menangani SearchBox errors gracefully
   - User akan selalu bisa akses form baik melalui SearchBox atau fallback dropdown

4. **Commit**: `556a5c8` — Fix: Add fallback dropdown for sales rep selection
   - Ensures form visibility selalu ada untuk user

---

### Sesi: 2026-05-20 — Cleanup & Investigation
1. **Cleanup File Sampah** — Berhasil menghapus 15 file temporary/debug/migration:
   - Root level: `check_db.php`, `check_setup.php`, `test_barcode_scanner.php`, `test_create_unit.php`, `test_session.php`, `fix_unit_fk.php`, `cleanup_bulk_fast.php`, `reset_password.php`
   - Public folder: `public/fix_fk.php` (security risk — publicly accessible)
   - Database: `dedupe_sales_reps.php`, `migrate_qty_prices.php`, `fix_fk.php` (migration scripts)
   - Scratch folder: entire `scratch/` directory (test files)
   - Commit: `ffecac1` pushed to main branch

2. **Form Supplier Investigation** — Diteliti ulang fungsi form supplier di `purchases/create`:
   - **Status**: Kode sudah benar, fungsi `onSalesRepPicked()` seharusnya menampilkan supplier otomatis
   - **Kemungkinan penyebab masalah**: Browser cache lama, asset versi tidak reload
   - **Solusi**: Clear browser cache, atau update asset version di `app/views/layouts/app.php`
   - **Keterangan**: Jika masalah persisten setelah cache clear, debug via Chrome DevTools

---

### Sesi: 2026-05-20 — PPN & Diskon Per Barang di Input Barang Masuk

**Yang dikerjakan:**
1. **Daftar Barang** — Ditambahkan form PPN (%) dan Diskon (Rp/%) per item. Kalkulasi `Harga Nett = Modal + PPN - Diskon` tampil realtime dengan breakdown detail.
2. **Margin** di Daftar Barang kini berbasis `Harga Nett`, bukan raw `buy_price`.
3. **Info kemasan lain** — Setiap item di Daftar Barang kini menampilkan mini-summary harga kemasan lain (Modal Nett, Ecer, Grosir beserta margin vs Nett), seperti di Input Massal.
4. **Input Massal (Bulk)** — Ditambahkan form PPN & Diskon per produk. Margin di bulk modal sekarang berbasis `Harga Nett`. Tombol "Atur Harga Kemasan Lainnya" kini memunculkan panel kemasan dan di dalam panel tersebut kini terdapat form PPN & Diskon per kemasan.
5. **Modal "Atur Harga Kemasan Lainnya"** — Kini memiliki form input PPN (%) dan Diskon (Rp/%) di masing-masing level kemasan untuk menghitung harga nett per kemasan.
6. **Helper functions baru:** `calcItemNett()`, `buildNettInfo()`, `updateItemPpnDiskon()`, `buildPkgMiniSummaryHtml()`.

---

## Pekerjaan Terakhir

### Sesi Sebelumnya: 2026-05-19 — Real-time Supplier Search & Navigation Optimization

**Yang dikerjakan:**
1. **Real-time supplier search** — Implementasi search bar context-aware di halaman supplier yang trigger otomatis saat mengetik tanpa perlu Enter.
2. **Search hasil dropdown** → auto-scroll ke kartu supplier yang dipilih + highlight kuning sementara.
3. **Global search context** — `app.js` diupdate agar placeholder search berbeda jika sedang di halaman `/suppliers`.
4. **API endpoint** `GET /api/suppliers/search?q=` digunakan untuk supplier/sales rep search.
5. **PWA caching sync** — versi cache diperbarui setelah perubahan signifikan.

**Sesi sebelumnya: 2026-05-19 — Dashboard Grid Menu & Help Update**
1. Grid menu dashboard berbasis kategori (pengganti menu list lama).
2. Help module diupdate dengan dokumentasi alur sistem terbaru.
3. Cleanup & validasi kode system-wide.

**Sesi sebelumnya: 2026-05-18 — Bulk Purchase & POS Optimization**
1. Tampilan harga di kartu produk bulk purchase (modal, ecer, grosir, margin, selisih).
2. Barcode scanner dual-engine: ZXing-JS (utama) + html5-qrcode (fallback).
3. Tier pricing POS: harga otomatis berubah sesuai kuantitas di cart.
4. Thermal printer: header/footer persistence & logo management.
5. Timezone fix: standardisasi ke GMT+7 di PHP + JS.
6. "Total Harga" input logic di bulk purchase untuk kalkulasi unit price otomatis.

---

## Known Issues & Kendala

| # | Issue | Status | Catatan |
|---|-------|--------|---------|
| 1 | Thermal printer (Web Serial API) | 🔶 Browser-limited | Hanya berfungsi di Chromium-based browser (Chrome/Edge) |
| 2 | Service Worker cache | 🔶 Manual update | Saat asset berubah besar, `CACHE_NAME` di `sw.js` harus diupdate manual |
| 3 | ApiController.php sangat besar (~57KB) | 🔶 Tech debt | Pertimbangkan refactor ke sub-controller terpisah di masa depan |

---

## Modul & Status

| Modul | Status | Catatan |
|-------|--------|---------|
| Auth (Login/Logout) | ✅ Stabil | Session-based, CSRF protected |
| Dashboard | ✅ Stabil | Grid menu, statistik, API stats |
| Produk | ✅ Stabil | CRUD, packaging, tier pricing, foto, label, stok |
| Barang Masuk (Purchase) | ✅ Stabil | Bulk input, foto invoice, harga terakhir supplier |
| Kasir POS | ✅ Stabil | Barcode scan, cart, tier pricing, thermal print |
| Supplier | ✅ Stabil | CRUD supplier & sales rep, real-time search |
| Scanner (Cek Harga) | ✅ Stabil | Dual-engine barcode scanner |
| Laporan | ✅ Stabil | Histori produk, export |
| Pengaturan Master Data | ✅ Stabil | Brand, kategori, satuan — on-the-fly create |
| Pengaturan Struk | ✅ Stabil | Nama toko, alamat, logo, header/footer |
| Manajemen User | ✅ Stabil | CRUD user, toggle active, reset password |
| Catatan Hutang | ✅ Stabil | Kelola piutang pelanggan & hutang toko, pencatatan cicilan |
| Keuangan Harian | ✅ Stabil | Pemasukan/pengeluaran harian, 4 pos keuangan, visual perbandingan |
| Help | ✅ Stabil | Dokumentasi sistem terbaru |
| PWA | ✅ Aktif | SW v1.93, manifest, install prompt, auto-login |

---

## Pending Tasks / Next Development

| Prioritas | Task | Keterangan |
|-----------|------|-----------|
| � Selesai | Cleanup file test di root | ✅ Semua file temporary/debug sudah dihapus (commit: ffecac1) |
| 🟢 Selesai | Amankan `public/fix_fk.php` | ✅ File sudah dihapus (commit: ffecac1) |
| 🟢 Selesai | Form sales/supplier visibility | ✅ Fallback dropdown ditambahkan (commit: 556a5c8) |
| 🟡 Sedang | Refactor ApiController | Pertimbangkan split ke resource-based sub-controller |
| 🟡 Sedang | Laporan penjualan per periode | Filter tanggal, total omzet, top produk |
| 🟡 Sedang | Notifikasi stok minimum | Alert jika stok produk di bawah batas minimal |
| 🟢 Rendah | Dark/light mode toggle | Saat ini full dark mode |
| 🟢 Rendah | Export laporan ke PDF/Excel | Laporan produk & penjualan |

---

## Keputusan Teknis Penting

| Tanggal | Keputusan | Alasan |
|---------|-----------|--------|
| 2026-05 | Semua API terpusat di `ApiController.php` | Simplifikasi routing, konsistensi response format |
| 2026-05 | Dual-engine barcode: ZXing-JS + html5-qrcode | ZXing lebih sensitif, html5-qrcode sebagai fallback |
| 2026-05 | Tier pricing via `product_qty_prices` table | Fleksibel untuk harga berbeda per qty minimal |
| 2026-05 | Web Serial API untuk thermal printer | Browser-native, tidak perlu server-side print |
| 2026-05 | PWA mode standalone | Mobile UX optimal, bisa install di home screen Android |
| 2026-05 | Cache First untuk static asset | Performa offline/slow network |
| 2026-05 | `?v=X.X` versioning untuk cache busting | Sederhana, tidak perlu build tool |

---

## Catatan Risiko

- **Satu ApiController besar** — edit harus sangat hati-hati karena semua API endpoint ada di satu file.
- **File test di root** — jika aktif di production server, bisa jadi celah keamanan.
- **Web Serial API** — hanya Chrome/Edge, iOS tidak didukung.
- **localStorage untuk auth hint** — bukan sumber kebenaran auth (hanya UX hint), validasi tetap di session PHP.
- **Packaging & qty_prices FK** — relasi kompleks, pastikan ON DELETE behavior benar sebelum hapus data.

---

## Cara Update File Ini

Setelah setiap task selesai, update bagian:
1. **Pekerjaan Terakhir** — tambahkan sesi baru di atas, geser sesi lama ke bawah
2. **Known Issues** — tambah/update/hapus issue sesuai kondisi terbaru
3. **Modul & Status** — update status jika ada perubahan
4. **Pending Tasks** — centang/hapus task yang selesai, tambah task baru
5. **Keputusan Teknis** — catat keputusan baru yang signifikan
6. **Last Updated** di tabel Status Umum
