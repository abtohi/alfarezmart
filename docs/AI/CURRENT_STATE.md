# CURRENT STATE — AlfarezMart

> File ini mencatat kondisi development terkini project AlfarezMart.
> Update file ini setiap kali selesai mengerjakan task, menemukan kendala baru, atau membuat keputusan teknis penting.
> AI membaca file ini untuk memahami konteks terakhir tanpa perlu membaca semua kode.

---

## Status Umum

| Item | Nilai |
|------|-------|
| **Status** | Aktif dikembangkan (Production-ready, fitur lanjutan sedang ditambah) |
| **Versi Cache SW** | `alfarezmart-v9.0` |
| **Versi Asset** | `?v=9.0` (di `app/views/layouts/app.php`) |
| **PHP Version** | XAMPP (cek `php -v`) |
| **Timezone** | Asia/Jakarta (GMT+7) |
| **Last Updated** | 2026-06-06 |

---

## Pekerjaan Terakhir

### Sesi: 2026-06-06 — Revamp Total Algoritma AI Chat & Context Injection

**Yang dikerjakan:**
1. **Refactor `AiContextBuilder.php`** — Mengubah format Context Injection dari JSON mentah ke format Markdown yang lebih bersih agar tidak terjadi *Context Overload*. Menghapus injeksi otomatis `katalog_semua_produk` (hanya disertakan jika keywords cocok) sehingga token prompt berkurang drastis.
2. **Penyederhanaan & Penjelasan Skema Database** — `getDatabaseSchema` kini mengembalikan daftar statis tabel-tabel penting beserta kolom dan tipe relasinya. Hal ini jauh lebih berguna bagi AI dibanding sekadar `SHOW COLUMNS`.
3. **Peningkatan Agentic SQL Loop di `AiChatController.php`** — Memisahkan feedback antara `[SQL_RESULT]` (sukses) dan `[SQL_ERROR]` (gagal) agar AI tahu saat query-nya salah dan bisa melakukan *self-correction*. Batas perulangan (max passes) dinaikkan dari 2 menjadi 3.

**File yang Diubah:**
- `app/services/AiContextBuilder.php` — Merombak format System Prompt.
- `app/controllers/AiChatController.php` — Modifikasi error handling dan loop passes untuk Agentic SQL.

---

### Sesi: 2026-06-06 — Fix Keuangan Harian + Fix Produk Offline/Online + Fix Mode Seleksi

**Yang dikerjakan:**
1. **Fix Saldo Keuangan Harian (Offline Mode)** — Saat buka tanggal baru, Saldo Pulsa/Rokok/Utama tampil 0. Root cause: di `loadFinanceData` offline fallback, hanya filter log hari ini (`log.log_date === date`), bukan akumulatif. **Fix** di `finance/index.php`: Iterasi `pastLogs` (`log_date <= date`) untuk hitung `net`, tapi hanya hari ini untuk hitung `income`/`expense`.

2. **Fix Halaman Produk Loading Offline Saat Online** — Root cause utama: `DOMContentLoaded` selalu memanggil `doOfflineSearch(q)` tanpa cek status koneksi (komentar lama: "Always render from Dexie"). **Fix** di `products/index.php`: Bungkus pemanggilan `doOfflineSearch` dengan `if (!navigator.onLine)`.

3. **Fix Live Search Dropdown Prioritaskan Offline** — Live search dropdown saat mengetik selalu mencari OfflineDB dulu, API jadi fallback. **Fix**: Balikkan logika — online → API server dulu, OfflineDB sebagai fallback.

4. **Fix Mode Seleksi Produk (Long Press)** — Fungsi `toggleSelectMode`, `updateSelectionState`, dan listener long-press terkurung dalam closure `DOMContentLoaded` sehingga tidak bisa dipanggil ulang oleh `doOfflineSearch`. **Fix**: Refactor semua fungsi seleksi menjadi global. Buat `attachProductCardListeners()` global yang bisa dipanggil ulang setelah `doOfflineSearch` render card baru. Clone trick (replaceChild) digunakan agar event tidak double-bind.

5. **Fix Urutan Produk di Search** — `searchProducts` di `ProductModel.php` masih `ORDER BY full_name ASC`. **Fix**: Ubah ke `ORDER BY COALESCE(updated_at, created_at) DESC, full_name ASC` agar konsisten dengan daftar produk utama.

**File yang Diubah:**
- `app/views/finance/index.php` — Fix kalkulasi saldo akumulatif di mode offline
- `app/views/products/index.php` — Fix online/offline logic, fix live search priority, refactor select mode ke global scope
- `app/models/ProductModel.php` — Fix sort order di `searchProducts`


**Yang dikerjakan:**
1. **Fix Nama Produk Undefined di Edit Barang Masuk** — Saat klik icon edit di halaman Riwayat Barang Masuk, nama produk tampil "undefined". Root cause: `addProductToCartExisting` di `purchases/edit.php` tidak meng-assign field `name` ke objek item. **Fix**: Tambahkan `product.name = itemInfo.name || itemInfo.full_name` sebelum item masuk ke `purchaseItems`.

2. **Optimasi UI Pencarian POS** — List produk di POS search menggunakan font terlalu besar dan nama terpotong. **Fix** di `pos.php`: font lebih kecil, layout flex, `word-break: break-word`, tampilkan semua harga kemasan (ecer/grosir) per item sebagai pilihan langsung.

3. **Estimasi Profit di POS** — Menambahkan estimasi profit total di checkout bar (`#cartProfit`) dan per-item di cart (samar di bawah info harga modal). `calculateTotal()` diupdate untuk menghitung `profit = total - (buy_price × qty)` per item. Info PPN disembunyikan dari `price_note` (sesuai task "Sembunyikan PPN di POS").

4. **Fix Error Model AI Non-Gemini** — `response_format: {type: "json_object"}` di payload API menyebabkan error pada model non-OpenAI (Claude, Llama, DeepSeek). **Fix**: Hapus key `response_format` dari payload di `ApiController.php`. Model tetap dipandu via system prompt untuk output JSON.

5. **Fix Unduh Data Offline (IDB Error)** — Error "Failed to execute 'transaction' on 'IDBDatabase'" terjadi karena `syncAllDataFromServer()` dipanggil sebelum `db` diinisialisasi. **Fix** di `offline-db.js`: Tambah `if (!db) await init()` di awal `syncAllDataFromServer()`. Juga bump `DB_VERSION` ke 5 untuk memastikan schema upgrade ter-trigger.

6. **Desimal di Harga Modal (Barang Masuk)** — Input "Total Harga Pembelian" di cart Edit Barang Masuk tidak accept desimal. **Fix**: Tambah `step="any"` ke input. Modal buy price di drawer sudah punya `step="0.01"`.

7. **Fix Distribusi Harga Modal** — Fungsi distribusi harga (adjust) menimpa input manual user. **Fix**: Tambah flag `is_manual_price` pada item. `distributeAdjustments` di `edit.php` dan `create.php` skip item yang flagged.

**File yang Diubah:**
- `app/views/purchases/edit.php` — Fix undefined name, flag is_manual_price, step="any" di total input
- `app/views/purchases/create.php` — Flag is_manual_price di distributeAdjustments
- `app/views/sales/pos.php` — UI search, estimasi profit, hide PPN dari price_note, cartProfit element
- `app/controllers/ApiController.php` — Hapus response_format dari AI API payload
- `public/js/offline-db.js` — Add await init() di syncAllDataFromServer, bump DB_VERSION ke 5

---

**Yang dikerjakan:**
1. **Root Cause Ditemukan** — Light theme sudah benar di `variables.css`, namun tidak tampil di mobile karena versi tidak sinkron: `APP_VERSION` di `app.php` masih `8.1`, sedangkan `sw.js` sudah `v8.3`, dan CSS `?v=8.5`. Karena `APP_VERSION` tidak berubah, mekanisme cache-clearing tidak terpicu di mobile sehingga Service Worker terus menyajikan `variables.css` lama dari cache.
2. **Fix** — Semua versi disinkronkan ke `9.0`:
   - `sw.js`: `CACHE_NAME = 'alfarezmart-v9.0'`
   - `app.php`: `APP_VERSION = '9.0'` dan `$v = '?v=9.0'`
3. **SW STATIC_ASSETS Diperbaiki** — Ditambahkan `offline-db.js` dan `components.js` ke daftar pre-cache agar semua JS utama ter-cache dengan benar.

---

### Sesi: 2026-06-02 — Tema Light Mode & PWA Theme Toggle

**Yang dikerjakan:**
1. **Light Mode Theme** — Menerapkan skema warna Light Mode di `variables.css`. Menggunakan kombinasi gradien merah (`#e63946`) dan biru (`#1e40af`) pada header, sesuai gaya visual logo `mobile_icon.png`. Latar belakang menggunakan warna putih (`#ffffff`) dengan default teks berwarna hitam (`#0b0b0d`).
2. **Header Kontras** — Memaksa `.header-title` dan `.header-btn` berwarna putih murni (`#ffffff`) di `app.css` agar kontras tetap optimal baik di Dark maupun Light mode.
3. **Theme Toggle UI** — Tombol icon Matahari/Bulan sudah terintegrasi di header (`app.php`) menggunakan LocalStorage (`alfarezmart_theme`) untuk persistent mode.

---

### Sesi: 2026-06-02 — POS Auto-Pricing Tier, Saldo Rokok Dashboard, Debts SearchBox, ExcelJS Formatting

**Yang dikerjakan:**
1. **POS Auto-Pricing Tier (Greedy Chunking)** — Merevisi algoritma di `qty-pricing.js` agar mendukung chunking harga (analogi "uang pas"). Jika qty = 16 (1 slop = 10, tier = 5), maka dihitung otomatis: `1 slop + 1 tier + 1 satuan` tanpa mengubah level kemasan (`unit_name`) yang terlihat di UI. Fungsi `autoSwitchPackagingLevel` di `pos.php` dihapus agar satuan tidak melompat.
2. **Dashboard Saldo Rokok (Finance)** — Mengembalikan filter `allowedPos = ['Saldo Utama', 'Saldo Rokok', 'Saldo Pulsa']` di `finance/index.php` sesuai permintaan agar POS lain (seperti Uang Laci, dll) tidak muncul di dashboard.
3. **Logika Dependent POS (Finance)** — Memperbaiki pencatatan transaksi akun yang saling dependen (contoh: Uang Laci -> Saldo Utama) di `FinanceModel.php`.
   - **Fix**: Jika Uang Laci mencatat *Pemasukan*, otomatis mencatat *Pemasukan* di Saldo Utama (1 record).
   - **Fix**: Jika Uang Laci mencatat *Pengeluaran*, otomatis mencatat **Pemasukan** sekaligus **Pengeluaran** di Saldo Utama (2 record).
4. **Akumulasi Saldo Keuangan Harian** — Mengubah perhitungan `net` di dashboard keuangan agar menampilkan saldo *akumulatif* (dari semua tanggal sebelum atau sama dengan hari terpilih), sementara Pemasukan/Pengeluaran harian tetap menunjukkan mutasi hari itu. Modifikasi dilakukan pada `getDailySummary` dan `getDailySummaryByPost` dengan menambahkan agregasi `log_date <= :date` untuk `accumulative_net`.
3. **Catatan Hutang & Piutang (Debts SearchBox)** — Memperbaiki instansiasi komponen `SearchBox` di halaman Hutang (`debts/index.php`).
   - **Root Cause**: Komponen `SearchBox` dipanggil menggunakan `SearchBox.init()` yang tidak eksis.
   - **Fix**: Menggunakan instansiasi class `new SearchBox()` dengan syntax yang benar dan dibungkus `setTimeout` untuk memastikan DOM Modal sudah dirender sepenuhnya sebelum mengikat events UI.
4. **Export Data Formatting** — Meningkatkan tampilan hasil export Data Produk (`dashboard/index.php`).
   - **Fix**: Mengganti library SheetJS yang terbatas dengan **ExcelJS**. Mengimplementasikan background header warna `#963634`, font *Arial Narrow* (bold & putih di header), formatting mata uang Rp (`"Rp "#,##0`), rename kolom 'Satuan atau jenis kemasan' menjadi 'Satuan', dan auto-resize lebar kolom (min 8, max 40). Versi Asset ditingkatkan dari `8.1` ke `8.2`.

---

### Sesi: 2026-06-02 — Fix AI Scan Harga Modal, Finance Routing Uang Rokok, Export Performance

**Yang dikerjakan:**
1. **Fix AI Scan Harga Modal tidak ter-update di Panel Kemasan** — Bug di `purchases/create.php`: setelah AI scan menambahkan item ke keranjang dan mengubah `bestPkg.buy_price = item.unit_price`, nilai baru tidak di-propagate ke semua level kemasan lain. Akibatnya saat user membuka drawer kemasan, semua level non-selected masih menampilkan harga lama dari DB.
   - **Root Cause**: Setelah `addProductToCart`, kode hanya mengupdate `addedItem.quantity` dan `addedItem.total`, tanpa memanggil `propagateFromMainInputs()`.
   - **Fix**: Tambahkan `addedItem.buy_price = item.unit_price` (eksplisit), lalu panggil `propagateFromMainInputs(addedItem)`, `syncSellPricesWhenBuyPriceChanges(addedItem)`, dan recalculate `harga_nett`. Kini semua packaging level (pcs, renceng, karton, dll.) otomatis ter-update dengan harga modal dari hasil scan AI.

2. **Fix Finance Uang Rokok masuk ke Saldo Utama** (sesi sebelumnya) — Diperbaiki routing dependency di tabel `finance_accounts`. Kolom `dependency_account_id` untuk akun "Uang Rokok" di-set ke ID akun "Saldo Rokok" via script PHP (`scratch/fix_db.php`). Logika `FinanceModel::addLog()` sudah benar — `dependency_account_id` menentukan saldo mana yang terpengaruh.

3. **Fix Export Modal Performance** (sesi sebelumnya) — Endpoint baru `/api/products/names` ditambahkan untuk menggantikan fetch full dataset (`?page=1&per_page=9999`) saat modal Export dibuka. Payload minimal (id, full_name, short_label, brand_name) sehingga loading jauh lebih cepat.

**File yang Diubah:**
- `app/views/purchases/create.php` — Tambah `propagateFromMainInputs`, `syncSellPricesWhenBuyPriceChanges`, dan recalc `harga_nett` setelah AI scan item masuk ke cart.
- `app/models/ProductModel.php` — Tambah `getProductNames()` untuk endpoint ringan.
- `app/controllers/ApiController.php` — Tambah handler endpoint `/api/products/names`.
- `app/config/Routes.php` — Daftarkan route `/api/products/names`.
- `app/views/dashboard/index.php` — Update `openExportModal()` untuk fetch dari endpoint baru.
- `scratch/fix_db.php` — Script one-shot fix `dependency_account_id` Uang Rokok (sudah dieksekusi).

**Catatan Teknis:**
- `propagateFromMainInputs(item)` menghitung `buyPerPcs = item.buy_price / selPkg.base_qty`, lalu menerapkan ke semua pkg. Benar untuk semua level (termasuk jika AI memilih level non-1 seperti Karton).
- Jika produk sudah ada di keranjang (duplicate), `addProductToCart` menambah qty +1 dan tidak memanggil propagate — untuk kasus AI scan ini tidak terjadi karena produk baru.
- `scratch/fix_db.php` dan `scratch/check_db.php` masih ada di folder scratch — **wajib dihapus** setelah session ini.

---

### Sesi: 2026-06-01 — Filter Harga Jual di Halaman Produk & Cleanup File
**Yang dikerjakan:**
1. **Filter Range Harga Jual** — Ditambahkan filter harga di halaman Produk, di bawah filter kategori. Filter bekerja berdasarkan harga jual ecer kemasan terkecil (level 1). Menggunakan subquery di `ProductModel::getProductsWithPrices()` sehingga berlaku di count query maupun data query. UI: dua input (min/max), tombol terapkan (🔫), tombol reset (✕) yang muncul jika filter aktif. Semua param dipertahankan saat search, ganti kategori, dan pagination.
2. **Update ai-instructions.md** — Ditambahkan instruksi wajib file cleanup yang lebih detail di bagian Workflow Rules (Setelah Implementasi) dan Gitignore & Cleanup Rules, mencakup pola file yang harus dicari: `test_*`, `debug_*`, `check_*`, `extract*`, `*.diff`, `*.patch`, `.md` di luar `docs/`, scratch scripts.
3. **Cleanup 16 File** — Dihapus semua file tidak berguna yang tidak direferensikan oleh kode aktif:
   - Root: `test_search.php`, `test_search2.php`, `implementation_plan.md`, `ESC_POS_SPECIFICATION.md`, `PRINTER_SETUP_GUIDE.md`
   - `scratch/`: `check_db.php`, `delete_ai_prompt.php`, `extract.js`, `extract.php`, `extract2.php`, `find_fn.php`, `migrate_supplier_fields.php`, `revert_diff.diff`, `test.js`, `test2.js`, `update_invoice_column.php`

**Catatan Teknis:**
- Filter harga subquery: `(SELECT sell_price_retail FROM product_packagings WHERE product_id = p.id ORDER BY level ASC LIMIT 1)` — tidak memerlukan JOIN tambahan.
- Folder `scratch/` kini kosong.
- `filterByCategory()` di view diupdate agar tidak menghapus filter harga saat user ganti kategori.

---

### Sesi: 2026-05-30 — Fix Dashboard Summary (Statistik Bulanan Superadmin)
**Yang dikerjakan:**
1. **Bug Identified & Fixed** — Fitur Dashboard Summary halaman statistik bulanan superadmin (`/dashboard/summary`) tidak dapat diakses karena error pada query Top 10 Produk Laris.
   - **Root Cause**: Query menggunakan kolom `si.invoice_name` yang tidak ada di tabel `sale_items`. Kolom yang seharusnya digunakan adalah `si.custom_name` (untuk produk custom).
   - **File Affected**: `app/controllers/DashboardController.php` line ~91
   - **Fix**: Ganti `COALESCE(NULLIF(si.invoice_name, ''), ...)` dengan `COALESCE(NULLIF(si.custom_name, ''), ...)`
2. **Verification** — Semua queries dan model methods di-verify:
   - ✅ `DashboardController@index()` — model methods `ProductModel::getStats()`, `SaleModel::getDailyStats()`, `FinanceModel::getDailySummary()` ada dan bekerja
   - ✅ `DashboardController@summary()` — semua 6 queries (omzet, belanja, markup, daily series, top products, hutang) verified benar
   - ✅ View template `dashboard/summary.php` — sudah lengkap dengan KPI cards, debt summary, daily chart, top products list
3. **Feature Status**:
   - ✅ Link "Summary" hanya tampil untuk superadmin (di `dashboard/index.php`)
   - ✅ Halaman summary menampilkan periode bulanan dengan month picker
   - ✅ Dashboard menampilkan KPI: Omzet, Total Belanja, Gross Profit, Net, Piutang, Hutang Toko
   - ✅ Chart omzet harian (CSS bar chart)
   - ✅ Top 10 produk laris dengan profit breakdown
   - ✅ Security: `requireSuperadmin()` guard aktif

**Catatan Teknis:**
- Database schema `sale_items` menggunakan `custom_name` untuk identifikasi produk custom (dari feature POS Custom Item di sesi sebelumnya)
- Query fallback logic: gunakan `custom_name` jika ada, else `p.full_name`, else generic 'Produk'
- Semua try-catch blocks untuk debt queries sudah ter-setup aman meski table optional

### Sesi: 2026-05-27 — Fix Kalkulasi Tier POS & Hapus Produk Massal
**Yang dikerjakan:**
1. Memperbaiki logika kalkulasi harga tier di Kasir POS (`app/views/sales/pos.php`) agar menggunakan kuantitas aktual item di cart, bukan statis 1 item, sehingga total kelipatan harga tier berfungsi dengan benar.
2. Memperbaiki fitur hapus produk secara massal di halaman Produk (`app/views/products/index.php`) dengan menambahkan metode utilitas yang hilang `AppModal.confirm()` ke `public/js/components.js`.
3. Memperbaiki render karakter `&` yang berubah menjadi `&amp;` di UI SearchBox form Kategori/Brand dengan mengubah `htmlspecialchars` menjadi `json_encode` di file `create.php` dan `edit.php` serta mengupdate `ai-instructions.md`.
4. PWA Cache Busting — Update ke versi `5.9` (asset) dan `6.7` (SW).

---

### Sesi: 2026-05-24 — Bugfix Tier Harga & Live Search
**Yang dikerjakan:**
1. Memperbaiki tombol "Tambah Harga Tier" pada halaman Edit Produk yang tidak berfungsi untuk produk lama yang belum punya tier sama sekali (masalah missing initialization `initQtyTiers`).
2. Memperbaiki fitur *Live Search* (rekomendasi pencarian) di halaman Produk (`products/index.php`) yang rusak akibat *Syntax Error* pada blok `try...catch` asinkron Javascript.
3. PWA Cache Busting — Update ke versi `4.9`.

---

### Sesi Sebelumnya: 2026-05-24 — AI Scan Kemasan Cerdas & Keamanan Harga Jual

**Yang dikerjakan:**
1. **AI Extract Unit Price** — Memperbarui instruksi prompt AI di `ApiController.php` & tabel `app_settings` agar mampu mengekstrak attribut `unit_price` (harga per satuan kemasan) terpisah dari `total_price` serta mempertegas deteksi kolom nama `unit` (kemasan) seperti Karton, Renceng, dll.
2. **Auto-Selection Kemasan** — Mengubah logika frontend di `purchases/create.php` saat memasukkan hasil scan AI ke dalam keranjang. Kini sistem tidak langsung mematok level 1 (Pcs), melainkan secara cerdas memilih level kemasan. Sistem mencocokkan kemasan melalui nama satuan terlebih dahulu, dan jika tidak jelas, mencari harga kemasan yang mendekati hasil hitungan AI.
3. **Keamanan Harga Jual** — AI tidak lagi menimpa data harga level dasar. Harga beli (modal) hanya di-update khusus untuk kemasan terpilih, tanpa menimpa/merusak harga jual ritel dan grosir di database.
4. **PWA Cache Busting** — Memperbarui cache service worker dan asset menjadi versi `4.8`.

### File yang Diubah:
- `app/controllers/ApiController.php` — Update default prompt AI & extraksi `unit_price`, `total_price`.
- `app/views/purchases/create.php` — Logika pemetaan kemasan otomatis.
- `app/views/layouts/app.php` & `sw.js` — PWA Cache Buster v4.8.
- `update_prompt.php` — Script one-shot penimpa config prompt di database (sudah dieksekusi).

---

### Sesi Sebelumnya: 2026-05-24 — PWA Fast Fallback & Sinkronisasi Harga Mode Referensi

**Yang dikerjakan:**
1. **PWA Fast Fallback (Timeout-based Network First)** — Mengatasi keluhan "load lama saat sinyal lemah". `sw.js` diperbarui untuk melayani request navigasi HTML menggunakan `Promise.race` dengan timeout 800ms. Jika dalam 0,8 detik server belum merespon (sinyal buruk), SW akan segera men-serve versi cache lokal sementara update data berjalan asinkron. Menu aplikasi akan "terasa" instan mirip AppSheet di kondisi sinyal apapun.
2. **Sinkronisasi Harga Mode Referensi** — Melengkapi fitur duplikasi produk referensi di halaman Edit dan Tambah Produk. Saat referensi dipilih, form tak hanya mengisi data level kemasan, tetapi juga meneruskan dan menyiapkan `qty_prices` (tier harga khusus kuantitas) ke produk baru. Opsi "Harga Custom" juga akan otomatis terkunci (`checked`) untuk memastikan harga ecer & modal tidak terekayasa oleh auto-calculation.
3. **Backend Propagation** — Mengupdate `ApiController::createProduct` untuk mem-parsing json harga tier dan meneruskannya ke `ProductModel::createWithDetails` yang kini dapat menangani insert tier harga.

### File yang Diubah:
- `sw.js` — Strategi fetch dengan 800ms fallback timeout.
- `app/views/products/create.php` & `app/views/products/edit.php` — Penyesuaian hidden input & auto-lock "Harga Custom" toggle.
- `app/controllers/ApiController.php` & `app/models/ProductModel.php` — Penyimpanan qty_prices backend.
- `app/views/layouts/app.php` — Bump APP_VERSION menjadi 4.7.

---

### Sesi Sebelumnya: 2026-05-24 — Fix Popup "Data Tidak Tersimpan" Saat Simpan Edit Produk

**Yang dikerjakan:**
1. **Fix Unsaved Changes Popup pada Save** — Di `app/views/products/edit.php`, saya menggunakan metode `Object.defineProperty(window, 'hasUnsavedChanges', ...)` untuk mengunci nilai flag menjadi `false` secara permanen. Hal ini penting karena device Android dengan fitur autofill atau Service Worker caching yang agresif terkadang memicu event `input` susulan saat delay 1000ms sebelum redirect, yang membuat flag ini kembali menjadi `true`. Dengan *property lock*, popup `beforeunload` di `app.js` 1000% ter-bypass.
2. **Redirect ke Daftar Produk** — Mengubah target redirect setelah simpan produk dari halaman detail (`products/{id}`) kembali ke halaman index produk (`products`), sesuai instruksi task.
3. **Support Update `contained_qty` pada Packaging Existing** — Diperbarui payload AJAX di frontend dan handler di `ApiController.php@updatePackaging` untuk mendukung pengiriman dan penyimpanan `contained_qty` untuk level kemasan yang sudah ada. Backend juga otomatis akan me-recalculate nilai `base_qty` untuk semua level di produk terkait jika `contained_qty` berubah.

### File yang Diubah:
- `app/views/products/edit.php` — Set `window.hasUnsavedChanges = false`, ubah target redirect, tambahkan `contained_qty` ke payload AJAX.
- `app/controllers/ApiController.php` — Modifikasi `updatePackaging()` untuk menyimpan parameter `contained_qty` dan otomatis me-recalculate `base_qty` beruntun.

---

### Sesi: 2026-05-21 — Revamp Purchase Input & Bulk Input Massal UI/UX and Code Cleanup

**Yang dikerjakan:**
1. **Pembersihan Kode Redundan & Duplikat** — Menghapus fragmen fungsi yatim piatu dan deklarasi duplikat `renderCart()`, `submitPurchase()`, dan `openBulkInputModal()` di file `purchases/create.php` (~850 baris kode terhapus). File dibersihkan total untuk mencegah conflict JavaScript pada runtime.
2. **Standardisasi Propagation Engine** — Memvalidasi bahwa alur propagasi harga, PPN, diskon, dan perhitungan margin berjalan selaras di halaman barang masuk (baik reguler cart maupun bulk input modal).
3. **Validasi Linter** — Memastikan file `purchases/create.php` bebas dari kesalahan sintaksis PHP dengan checker PHP CLI.
4. **Git Versioning** — Berhasil melakukan commit dan push revisi final ke repositori GitHub (`main` branch).

### Sesi: 2026-05-21 — Database AI Prompt Update & Level Akses Keseragaman

**Yang dikerjakan:**
1. **Database AI Invoice Prompt Update** — Berhasil menjalankan script database one-shot (`update_prompt.php`) untuk menyinkronkan data di tabel `app_settings` key `ai_invoice_prompt` dengan prompt AI versi terbaru yang memiliki pendeteksian `size`, abbreviating harga, dan multi-pack matching.
2. **Keseragaman Dropdown Level Akses** — Mengubah class input Level Akses di form tambah user (`users/index.php`) dari `form-control-dark` ke `form-select-dark` agar mengikuti custom SVG chevron style guide yang baru.

### Sesi: 2026-05-21 — POS Custom Product Quantity & Unit Price Calculation Enhancement

**Yang dikerjakan:**
1. **Custom Product Modal Quantity & Input Upgrades** — Mendukung penjualan barang custom dengan kuantitas desimal (seperti 0.5 kg, 1.5 meter):
   - Form input Qty (`customItemQty`) di modal mendukung desimal (`min="0.01"` dan `step="any"`).
   - Validasi dan parsing Qty di frontend menggunakan `parseFloat` alih-alive integer.
2. **Cart Presentation & Unit Price Display** — Perhitungan real-time dan presentasi transparan di keranjang POS (`renderCart` & `updateCartItemDom`):
   - Menampilkan detail `Harga Satuan / Satuan (Total Harga)` (contoh: `Rp2.000 / Pcs (Total Rp6.000)`).
   - Penambahan kuantitas via button `+` / `-` otomatis meng-update total harga secara proporsional dengan mempertahankan unit price.
3. **PWA Service Worker & Cache Busting** — Menghindari stale caching akibat perubahan script & style:
   - Bump query version asset di `app.php` menjadi `?v=3.9`.
   - Bump cache version Service Worker di `sw.js` menjadi `alfarezmart-v1.95`.

### File yang Diubah:
- `app/views/sales/pos.php` — modifikasi float qty modal, calculation logic, renderCart, & updateCartItemDom.
- `app/views/layouts/app.php` — bump static asset version ke ?v=3.9.
- `sw.js` — bump Service Worker cache version ke alfarezmart-v1.95.

---

### Sesi: 2026-05-21 — Restore Form Edit Produk & Fitur Barang Custom POS

**Yang dikerjakan:**
1. **Restore Product Edit Layout (`edit.php`)** — Form edit produk dipastikan kembali bersih dan fungsional:
   - Checkbox "Produk Multivarian" toggle brand/jenis/varian berjalan dengan benar.
   - Modal popup supplier info digantikan dengan **inline collapsible panel** (accordion dengan chevron) di dalam form.
   - Field `supplier_product_code` dan `supplier_invoice_name` langsung ikut di-submit bersama form utama tanpa modal terpisah.
   - Panel otomatis expand jika produk sudah memiliki data supplier tersimpan.
2. **Fitur Barang Custom di Kasir POS (`pos.php`)** — Kasir dapat input item tidak terdaftar:
   - Tombol `+ Barang Custom` ditambahkan di samping header "Scan/Cari Produk".
   - Modal `openCustomProductModal()` dengan field Nama Barang, Satuan, dan Total Harga (Rp).
   - `addCustomProductToCart()` inject item ke cart dengan flag `is_custom: true`.
   - Checkout payload dikirim dengan field `is_custom`, `custom_name`, `custom_unit`.
3. **Backend SaleModel (`SaleModel.php`)** — Support custom item end-to-end:
   - `getPlaceholderProductAndPackaging()`: self-healing resolver produk placeholder CUSTOM.
   - `createWithDetails()`: deteksi `is_custom`, skip stock update, simpan `custom_name`/`custom_unit`.
   - `getTransactionDetails()`: COALESCE query agar nama/satuan custom tampil di struk & laporan.
4. **Database Migration** — Kolom `custom_name` dan `custom_unit` ditambahkan ke `sale_items`. Produk placeholder CUSTOM (id=9381, packaging id=18924) terverifikasi di database.

### Catatan Teknis:
- Barang custom tidak mengurangi stok — menggunakan produk placeholder CUSTOM sebagai anchor FK.
- `$placeholderCache` di SaleModel mencegah query ulang dalam satu request transaksi.
- Detail transaksi dan struk menampilkan nama/satuan custom secara transparan tanpa perubahan di layer lain.

### File yang Diubah:
- `app/views/products/edit.php` — Restore layout, inline collapsible supplier info panel.
- `app/views/sales/pos.php` — Tombol Barang Custom, modal, cart injection, checkout payload.
- `app/models/SaleModel.php` — Backend custom item support.
- `database/migrate_custom_items.php` — Migrasi DB [NEW].

---

**Yang dikerjakan:**
1. **AI Invoice Scan Prompt Enhancement** — Meningkatkan prompt AI di `ApiController::scanInvoiceAI()` dengan instruksi lebih detail untuk ekstraksi atribut produk (brand, product_type, variant, weight, unit, supplier_code). Menambahkan contoh ekstraksi yang lebih comprehensive untuk beverage dan noodles category.
2. **AI Matching Logic Optimization** — Revamp scoring system di `scanInvoiceAI()`:
   - Tambah prioritas untuk exact `supplier_invoice_name` match (95 points — immediate match)
   - Naikkan direct code matching dari 70 → 80 points
   - Perbaiki weight distribution: name similarity 65% (turun dari 70%), brand 12 pts, product_type 8 pts, variant 8 pts, weight 10 pts
   - Threshold tetap 65 points untuk match success
3. **Supplier Info Modal di Product Edit** — Menambahkan button "Info Supplier" di form edit produk (`products/edit.php`) yang membuka modal untuk input:
   - `supplier_product_code`: Kode barang dari supplier
   - `supplier_invoice_name`: Nama barang di invoice supplier (sesuai invoice asli)
   - Button hidden by default, hanya tampil saat user klik, tidak mengganggu form utama
4. **Decimal Support for Prices** — Menambahkan `step="0.01"` ke semua input harga di:
   - `products/edit.php`: buy_price, retail, wholesale inputs
   - `purchases/create.php`: buy_price di berbagai section (modal, item level, bulk input), ppn_pct, dan diskon_value inputs
   - Update kalkulator harga dari `Math.round(t/q)` → `(t/q).toFixed(2)` untuk support decimal
5. **Enhanced Error Handling & Validation**:
   - Product form: Tambah pre-submit validation (kategori, brand, packaging, harga)
   - Purchase form: Tambah item-by-item validation sebelum submit (product_id, quantity > 0, buy_price > 0)
   - Improve error messages dengan icon emoji dan deskripsi lebih jelas ("❌ Kategori produk wajib dipilih")
   - Add try-catch lebih detail di packaging section dengan error message spesifik

### Catatan Teknis:
- Database schema sudah memiliki kolom `supplier_product_code` dan `supplier_invoice_name` (tidak perlu migration baru)
- ProductModel dan ApiController sudah support field baru (no changes needed)
- Modal supplier info menggunakan `AppModal.show()` yang sudah tersedia (consistent dengan existing patterns)
- Semua perubahan backward-compatible, tidak breaking existing functionality

### File yang Diubah:
- `app/controllers/ApiController.php` — Update prompt & matching logic di `scanInvoiceAI()`
- `app/views/products/edit.php` — Add supplier info button, modal, hidden inputs, decimal support, error handling
- `app/views/purchases/create.php` — Add decimal support & validation di `submitPurchase()`

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
| Produk | ✅ Stabil | CRUD, packaging, tier pricing, foto, label, stok, filter kategori & harga |
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
| 🟢 Selesai | Cleanup file test di root | ✅ Semua file temporary/debug sudah dihapus (commit: ffecac1) |
| 🟢 Selesai | Amankan `public/fix_fk.php` | ✅ File sudah dihapus (commit: ffecac1) |
| 🟢 Selesai | Form sales/supplier visibility | ✅ Fallback dropdown ditambahkan (commit: 556a5c8) |
| 🟢 Selesai | Restore form edit produk | ✅ Layout & multivarian checkbox diperbaiki, supplier info inline collapsible |
| 🟢 Selesai | Fitur Barang Custom di POS | ✅ Modal + backend + DB migration selesai |
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
