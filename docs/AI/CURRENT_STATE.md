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
| 1 | File test sementara di root (`test_barcode_scanner.php`, `test_create_unit.php`, `test_session.php`, `check_db.php`, `check_setup.php`, `fix_unit_fk.php`, `cleanup_bulk_fast.php`, `reset_password.php`) | ⚠️ Perlu review | Kemungkinan masih dipakai untuk debug/setup — belum aman dihapus |
| 2 | `public/fix_fk.php` | ⚠️ Perlu review | File fix FK di folder public — tidak boleh diakses publik di production |
| 3 | Thermal printer (Web Serial API) | 🔶 Browser-limited | Hanya berfungsi di Chromium-based browser (Chrome/Edge) |
| 4 | Service Worker cache | 🔶 Manual update | Saat asset berubah besar, `CACHE_NAME` di `sw.js` harus diupdate manual |
| 5 | ApiController.php sangat besar (~57KB) | 🔶 Tech debt | Pertimbangkan refactor ke sub-controller terpisah di masa depan |

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
| Help | ✅ Stabil | Dokumentasi sistem terbaru |
| PWA | ✅ Aktif | SW v1.93, manifest, install prompt, auto-login |

---

## Pending Tasks / Next Development

| Prioritas | Task | Keterangan |
|-----------|------|-----------|
| 🔴 Tinggi | Cleanup file test di root | Review dan hapus jika aman: `test_*.php`, `check_*.php`, `fix_*.php`, `cleanup_*.php`, `reset_password.php` |
| 🔴 Tinggi | Amankan `public/fix_fk.php` | Hapus atau pindah agar tidak bisa diakses publik |
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
