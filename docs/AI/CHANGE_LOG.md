# CHANGE LOG — AlfarezMart

> File ini mencatat semua perubahan yang dilakukan pada project AlfarezMart secara kronologis.
> **Jangan hapus entri lama.** Tambahkan entri baru di paling atas (format terbaru di atas).
> AI membaca file ini untuk memahami histori perubahan jika diperlukan konteks mendalam.

---

## [2026-06-06] — Fix Saldo Keuangan Harian (0 Rupiah) & Update Version

**Tipe:** Hotfix
**Modul:** Finance, Core
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Fix Saldo Keuangan Harian**: Memperbaiki issue dimana saldo (Saldo Utama, Saldo Pulsa, Saldo Rokok) pada halaman Keuangan Harian menunjukkan `Rp 0`. Akar penyebabnya adalah ketidakcocokan antara alias SQL di query database (`current_balance`) dengan array key PHP yang dipanggil (`accumulative_net`) pada `FinanceModel.php`. Alias SQL telah disesuaikan menjadi `accumulative_net`.
- **Update Versi Aplikasi**: Meningkatkan `APP_VERSION` dan `version` JavaScript menjadi `10.0` pada `app.php`. Memperbarui query string aset (css/js) menjadi `?v=10.6` pada `app.php`. Meningkatkan versi cache service worker `sw.js` menjadi `alfarezmart-v7.7`.

### File yang Diubah
- `app/models/FinanceModel.php`
- `app/views/layouts/app.php`
- `sw.js`

---

## [2026-06-06] — Revamp Total Algoritma AI Chat & Context Injection

**Tipe:** Mayor (Feature Improvement)
**Modul:** AI Chat
**Dikerjakan oleh:** AI Agent (Antigravity)

### Feature Improvement

1. **Refactor `AiContextBuilder.php`**
   - **Root Cause:** Data `katalog_semua_produk` yang dimasukkan ke dalam System Prompt sangat besar sehingga memicu *Context Overload* pada model LLM, menyebabkannya "berhalusinasi" atau tidak mengetahui konteks. Format JSON mentah juga sulit dipahami oleh LLM.
   - **Fix:** Menghapus autoinject `katalog_semua_produk` kecuali ada trigger keywords (contoh: "katalog", "semua produk"). Mengubah format `json_encode` menjadi Markdown yang lebih bersih dan human-readable.
   - **Penyederhanaan Skema:** Mengganti fungsi `getDatabaseSchema` (yang sebelumnya menggunakan `SHOW COLUMNS` dan tidak jelas bagi AI) menjadi struktur string manual yang memuat daftar tabel kunci beserta tipe data, status, dan contoh cara melakukan `JOIN` antar tabel.
   - **File:** `app/services/AiContextBuilder.php`

2. **Peningkatan Agentic SQL Loop di `AiChatController.php`**
   - **Root Cause:** Prompt perintah SQL Agentic lemah, dan bila model AI mengembalikan format SQL yang salah, server gagal memberikan pesan kesalahan kepada AI untuk memperbaikinya, sehingga loop terputus begitu saja.
   - **Fix:** Menaikkan batas *max passes* iterasi dari 2 menjadi 3. Memodifikasi parsing feedback; apabila server gagal menjalankan query yang dibentuk AI, kembalikan response dalam block `[SQL_ERROR]` disertai pesan perbaikan.
   - **File:** `app/controllers/AiChatController.php`

---

## [2026-06-06] — Fix Keuangan Harian, Fix Produk Offline/Online, Fix Mode Seleksi

**Tipe:** Minor (Bug Fix)
**Modul:** Finance, Products
**Dikerjakan oleh:** AI Agent (Antigravity)

### Bug Fix

1. **Fix Saldo Keuangan Harian = 0**
   - **Root Cause Offline:** Di `loadFinanceData` offline fallback (`finance/index.php`), kode hanya mengambil log hari ini (`log.log_date === date`) untuk semua perhitungan, padahal `net` seharusnya akumulatif dari semua hari sebelumnya.
   - **Fix Offline:** Tambahkan `pastLogs` (semua log `<= date`) untuk menghitung `net` akumulatif. `income` dan `expense` tetap hanya dari hari yang dipilih.
   - **Root Cause Online:** `loadMasterData()` memicu refresh API di background yang memanggil ulang `renderPosGrid()` *setelah* data summary di-load. Karena `renderPosGrid()` men-hardcode `Rp 0` di elemen HTML-nya, UI me-reset nilai summary yang sudah didapat dari server kembali ke 0.
   - **Fix Online:** Update `renderPosGrid()` agar menggunakan `currentBreakdown` (jika tersedia) saat merender HTML agar tidak me-reset nilai yang sudah ada.
   - **File:** `app/views/finance/index.php`

2. **Fix Halaman Produk Loading Data Offline Saat Online**
   - **Root Cause:** `DOMContentLoaded` listener di `products/index.php` selalu memanggil `doOfflineSearch(q)` tanpa cek `navigator.onLine`, dengan komentar lama "Always render from Dexie".
   - **Fix:** Bungkus `doOfflineSearch(q)` dengan `if (!navigator.onLine)`. Ketika online, biarkan server-rendered HTML dari PHP yang tampil.
   - **File:** `app/views/products/index.php`

3. **Fix Live Search Dropdown Mencari Offline Padahal Online**
   - **Root Cause:** Input live search selalu mencari OfflineDB dulu, API server jadi fallback sekunder.
   - **Fix:** Balikkan prioritas: jika `navigator.onLine`, langsung hit API server. OfflineDB digunakan sebagai fallback jika offline atau hasil kosong.
   - **File:** `app/views/products/index.php`

4. **Fix Mode Seleksi Produk (Long Press Tidak Berfungsi)**
   - **Root Cause:** Fungsi `toggleSelectMode`, `updateSelectionState`, dan listener long-press dikurung dalam closure `DOMContentLoaded` sehingga tidak diekspos secara global. `doOfflineSearch` tidak bisa memanggil re-attachment listener untuk card baru.
   - **Fix:** Refactor semua fungsi seleksi menjadi global. Buat `attachProductCardListeners()` sebagai fungsi global yang dipanggil oleh `DOMContentLoaded` (server-rendered cards) maupun oleh `doOfflineSearch` (offline-rendered cards). Gunakan clone trick (replaceChild) untuk mencegah double-binding event.
   - **File:** `app/views/products/index.php`

5. **Fix Urutan Produk di Search API**
   - **Root Cause:** `searchProducts` di `ProductModel.php` masih menggunakan `ORDER BY p.full_name ASC`.
   - **Fix:** Ubah ke `ORDER BY COALESCE(p.updated_at, p.created_at) DESC, p.full_name ASC` agar konsisten dengan tampilan daftar produk utama (terbaru di atas).
   - **File:** `app/models/ProductModel.php`

### File yang Diubah
- `app/views/finance/index.php`
- `app/views/products/index.php`
- `app/models/ProductModel.php`

---


**Tipe:** Mayor
**Modul:** Finance (FinanceModel, ApiController, finance/index.php)
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Database Skema Baru** — Menambahkan tabel `finance_accounts` (untuk daftar POS) dan `finance_categories` (untuk daftar Jenis Transaksi). Migrasi data lama dari `finance_logs` berhasil dilakukan sehingga data historis aman.
- **UI Grid POS Dinamis** — Kotak ringkasan POS (Uang Laci, dll) di halaman Keuangan Harian kini di-render secara dinamis dari database, mendukung warna/ikon acak untuk POS baru.
- **Manajemen POS & Konversi Otomatis** — Ditambahkan opsi *Dependent / Independent* eksplisit pada UI Tambah POS. Jika "Dependent" dipilih, akan muncul dropdown untuk memilih tujuan konversi (misal: "Saldo Utama"). Jika diset dependent, pengeluaran di POS asal otomatis ter-redirect sebagai Pemasukan & Pengeluaran di POS tujuan.
- **Manajemen Kategori Transaksi** — Ditambahkan tombol ⚙️ Kelola di sebelah input Jenis Transaksi. Form kini memunculkan modal khusus untuk **Menambah, Mengedit, dan Menghapus** list dropdown kategori transaksi secara manual. Kategori baru tetap bisa ditambahkan otomatis jika langsung diketik, dan kini Anda memegang kendali penuh atas daftarnya.

### File yang Diubah/Dibuat
- `database/migrate_finance_dynamic.php` — Script migrasi skema tabel baru & seeder data.
- `app/models/FinanceModel.php` — Refactor logic konversi otomatis di `addLog` & tambah method CRUD untuk accounts/categories.
- `app/controllers/ApiController.php` — Penambahan 8 endpoint REST API baru untuk CRUD accounts/categories.
- `app/config/Routes.php` — Routing endpoint finance accounts & categories.
- `app/views/finance/index.php` — Rewrite total JavaScript logic dan UI Grid untuk menggunakan data dinamis (AJAX ke API master data).
- Menghapus file test `test_db.php`.

---

## [2026-06-01] — Filter Harga Jual di Halaman Produk & Pembersihan File

**Tipe:** Minor
**Modul:** Products (ProductModel, ProductController, products/index.php), docs/AI
**Dikerjakan oleh:** AI Agent (Antigravity)

### Perubahan
- **Fix Bug Pagination (Service Worker)** — Memperbaiki bug di mana pagination (seperti pada Riwayat Barang Masuk) tidak berfungsi dan selalu kembali ke halaman pertama. Ini disebabkan oleh konfigurasi Service Worker (`sw.js`) yang mengabaikan parameter URL (`ignoreSearch: true`) saat memuat dari *cache*. Versi SW juga telah ditingkatkan agar *browser* memuat ulang *cache* yang benar.
- **Update Timestamp Produk via Pembelian** — Saat user menyimpan form Input Barang Masuk (setelah mendistribusikan diskon/PPN ke harga modal), sistem kini akan otomatis memperbarui nilai `updated_at` pada produk-produk yang bersangkutan. Hal ini membuat produk yang baru saja diinput/didistribusikan harganya otomatis naik ke posisi teratas pada daftar halaman Produk.
- **Filter Range Harga Jual** — Halaman Produk kini memiliki filter harga jual di bawah filter kategori. Filter bekerja pada harga jual ecer kemasan terkecil (level 1). User dapat mengisi min/max harga dan menekan tombol 🔫 (atau Enter) untuk menerapkan. Tombol ✕ muncul jika filter aktif untuk reset. Semua parameter (kategori, pencarian, harga) dipertahankan saat navigasi halaman dan pagination.
- **Fix Bug Filter Harga** — Memperbaiki bug dimana angka `0` pada input Max Price tertolak/diabaikan oleh sistem.
- **Update ai-instructions.md** — Memperkuat aturan wajib file cleanup setelah setiap task: diperluas dengan daftar pola file yang harus dicari dan dihapus (test, backup, debug, scratch, diff, extract, .md di luar docs). Format ringkasan task juga diperbarui agar lebih eksplisit.
- **Pembersihan File** — Menghapus 16 file tidak berguna:
  - Root: `test_search.php`, `test_search2.php`, `implementation_plan.md`, `ESC_POS_SPECIFICATION.md`, `PRINTER_SETUP_GUIDE.md`
  - Scratch: `check_db.php`, `delete_ai_prompt.php`, `extract.js`, `extract.php`, `extract2.php`, `find_fn.php`, `migrate_supplier_fields.php`, `revert_diff.diff`, `test.js`, `test2.js`, `update_invoice_column.php`

### File yang Diubah/Dibuat
- `app/models/ProductModel.php` — Tambah param `$minPrice`, `$maxPrice` di `getProductsWithPrices()` dengan subquery filter ke `product_packagings`
- `app/controllers/ProductController.php` — Parse `min_price`/`max_price` dari `$_GET`, teruskan ke model dan view
- `app/views/products/index.php` — UI filter harga (2 input + tombol terapkan + tombol reset), update `filterByCategory()` + tambah `applyPriceFilter()`, update `$buildUrl` pagination
- `docs/AI/ai-instructions.md` — Perkuat aturan file cleanup di Workflow Rules & Gitignore Rules

### Catatan
- Filter harga menggunakan subquery `(SELECT sell_price_retail FROM product_packagings WHERE product_id = p.id ORDER BY level ASC LIMIT 1)` — aman untuk count query dan data query tanpa JOIN tambahan.
- Folder `scratch/` kini kosong sepenuhnya.
- File .md yang dihapus (`ESC_POS_SPECIFICATION.md`, `PRINTER_SETUP_GUIDE.md`, `implementation_plan.md`) tidak direferensikan oleh kode manapun.

---

## [2026-05-30] — Hitung Orderan, Dashboard Summary, Offline Auto-Login, Staff Role Limits, Geofencing, Printer Modal

**Tipe:** Mayor
**Modul:** Multi-modul (Auth, Products, Sales, Dashboard, Geofencing, Printer)
**Dikerjakan oleh:** AI Agent

### Perubahan
- **Fix SQL constraint `full_name`** pada Tambah Produk: komposisi nama dengan fallback brand+type+variant → single_name → short_label (FE + BE).
- **Fitur Baru — Hitung Orderan** (`/hitung-orderan`): cari produk dengan algoritma yang sama seperti POS (multi-kata), pilih level kemasan, hitung total estimasi belanja real-time, copy daftar ke clipboard dalam format WhatsApp.
- **Fix barcode reset** di Edit Produk: `rebuildPackagingsFromReference()` kini mempertahankan barcode existing dari DOM ketika user mengganti produk referensi.
- **Offline Auto-Login superadmin**: login.php cache credentials hash di localStorage saat superadmin login online sukses; tryOfflineLogin saat `!navigator.onLine`; `app.js` checkAutoLogin skip fetch saat offline.
- **Modal Pilih Printer Bluetooth**: `window.openPrinterChooser(tp)` bottom-sheet bertemakan aplikasi (saved printer + cari baru), terintegrasi di POS dan Detail Penjualan (reprint invoice). Auto-reconnect silent pada page load.
- **Limit Role Staff**: hide finance/reports/debts di dashboard & controller `requireSuperadmin()`; block product edit/delete (UI + ApiController.blockStaffMutations); staff masih bisa create product (untuk POS custom item).
- **Geofencing Strict**: `geofencing.js` blocking alert app-styled (bukan native `alert()`) + auto-clear `alfarezmart_logged_in`/`alfarezmart_user` localStorage sebelum logout + safety auto-logout 4 detik.
- **Fitur Baru — Dashboard Summary** (`/dashboard/summary`, superadmin-only): omzet/belanja/profit/markup rata-rata bulanan, chart omzet harian (CSS bar), top 10 produk laris, outstanding debt snapshot, month picker.
- **Cache Buster**: SW `alfarezmart-v7.0`, `APP_VERSION = '7.0'`, asset `?v=7.0` untuk force PWA refresh.

### File yang Diubah/Dibuat
- `app/core/Controller.php` — helper `userLevel()`, `isStaff()`, `isSuperadmin()`, `requireSuperadmin()`, `blockStaffMutations()`
- `app/controllers/ApiController.php` — fullName fallback di createProduct; role guards di ~18 endpoint
- `app/controllers/FinanceController.php`, `DebtController.php`, `ReportController.php` — requireSuperadmin() di index
- `app/controllers/ProductController.php` — blockStaffMutations di edit()
- `app/controllers/OrderEstimateController.php` — **BARU**, fitur Hitung Orderan
- `app/controllers/DashboardController.php` — method `summary()` baru
- `app/views/products/create.php`, `edit.php` — fullName fallback chain + preserve barcode
- `app/views/products/index.php`, `show.php` — gating tombol untuk staff
- `app/views/auth/login.php` — offline login + credentials cache
- `app/views/orders/hitung.php` — **BARU**, halaman Hitung Orderan
- `app/views/dashboard/index.php` — menu Hitung Orderan & Summary
- `app/views/dashboard/summary.php` — **BARU**, halaman Summary & Statistik
- `app/views/sales/pos.php`, `detail.php` — integrasi `openPrinterChooser` + auto-reconnect
- `app/config/Routes.php` — route `/hitung-orderan` dan `/dashboard/summary`
- `public/js/app.js` — checkAutoLogin offline-aware
- `public/js/geofencing.js` — blocking alert + auto-logout
- `public/js/printer.js` — `window.openPrinterChooser(tp)` global
- `public/sw.js` — CACHE_NAME → `alfarezmart-v7.0`
- `app/views/layouts/app.php` — APP_VERSION `7.0`, asset `?v=7.0`

### Catatan
- Service Worker baru akan trigger auto-purge cache lama → semua user perlu reload pertama kali setelah deploy.
- Offline login HANYA untuk superadmin (sesuai spesifikasi). Admin/staff offline tetap diblokir.
- Web Bluetooth `requestDevice()` native popup tetap dipakai untuk pairing baru (tidak bisa diganti); modal kustom hanya membungkus alur saved/new.

---

## [2026-05-29] — Fitur Draft, Edit, dan Hapus Riwayat Pembelian

**Tipe:** Mayor
**Modul:** Pembelian (Purchases)
**Dikerjakan oleh:** AI Agent

### Perubahan
- Menambahkan fungsionalitas Draft pada halaman Input Barang Masuk untuk menyimpan isian ke localStorage.
- Menambahkan fitur "Kosongkan Semua" dan seleksi jamak "Hapus Terpilih" untuk item dalam cart di halaman Input Barang.
- Mengimplementasikan fungsionalitas Hapus Pembelian pada Riwayat Pembelian, beserta kemampuan menghapus secara massal dengan update/rollback stok (deleteWithRevert).
- Menambahkan fitur Edit Pembelian yang memungkinkan pengguna memuat ulang form input dengan nota yang ada dan mengubah harganya.
- Menambahkan tombol pratinjau foto invoice untuk menampilkan gambar invoice lama pada halaman riwayat.

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

## [2026-05-28] — Fix: Purchases Submit "Unauthorized" pada Klik Pertama

**Tipe:** Hotfix
**Modul:** Session, Service Worker (sw.js)
**Dikerjakan oleh:** AI Agent

### Masalah
Saat user menyimpan inputan Barang Masuk (purchases/create), muncul error "Unauthorized. Please login." pada klik pertama. Baru berhasil tersimpan saat klik kedua.

### Root Cause
Terdapat dua bug yang berkontribusi:
1. **Race Condition di Session.php**: `session_regenerate_id(true)` dipanggil setiap 300 detik saat session start. Jika ada request paralel (misalnya background sync dari Service Worker + page request), satu request bisa mengganti session ID sementara request lain masih menggunakan session ID lama → session dianggap tidak valid → `user_id` tidak ditemukan → 401 Unauthorized.
2. **Service Worker meng-cache response error**: SW menyimpan semua response API ke cache termasuk response 401/403, yang berpotensi mengembalikan response error lama pada request offline berikutnya.

### Perubahan
- **Session.php**: Hapus logika `session_regenerate_id(true)` periodik. Session ID hanya di-regenerate saat login (sudah di `AuthController::login()`).
- **sw.js**: Tambahkan pengecekan `response.ok` sebelum menyimpan response ke cache — hanya cache response 2xx yang sukses.
- **sw.js**: Bump cache version ke `alfarezmart-v6.8`.

### File yang Diubah
- `app/core/Session.php` — hapus periodic session regeneration
- `sw.js` — conditional caching (only 2xx), bump version v6.8

---

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

## 29 Mei 2026 - Perbaikan Barcode Scanner & Penambahan Tier Pricing di Tambah Produk & Input Barang
**Tipe:** Feature/Fix  
**Modul:** Scanner, Product, Purchase  
**Dikerjakan oleh:** AI Agent

### Perubahan
- Modifikasi ProductModel::findByBarcode agar pencarian scanner mencakup products.code selain product_packagings.barcode.
- Menambahkan fungsionalitas Tier Pricing (Harga Spesial per Kuantitas) di halaman Tambah Produk Baru (pp/views/products/create.php) agar selaras dengan halaman Edit.
- Menyempurnakan form Tier Pricing di halaman Input Barang (pp/views/purchases/create.php) dengan mengubah opsi dropdown Mode agar sesuai dengan tampilan di Edit Produk (Ecer & Grosir, Ecer saja, Grosir saja).
- Memperbarui payload JSON di Input Barang untuk menyertakan qty_prices saat input reguler maupun massal.
- Mengubah PurchaseModel::createWithDetails agar menyimpan qty_prices melalui ProductModel::saveQtyPricesForPackaging saat menyimpan pembelian baru.

### File yang Diubah
- pp/models/ProductModel.php`n- pp/views/products/create.php`n- pp/views/purchases/create.php`n- pp/models/PurchaseModel.php

- Menyelesaikan bug hilang nya harga tier ketika menekan tombol Distribusikan ke Harga Modal Barang di form Input Barang (menambahkan collectDrawerDataForItem sebelum re-render).
- Menambahkan input field Label (opsional) di form Harga Tier pada Input Barang untuk sinkronisasi dengan fitur Edit Produk.
