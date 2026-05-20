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
