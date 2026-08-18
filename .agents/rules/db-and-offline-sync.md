# 🔒 ATURAN WAJIB: Manajemen Koneksi Database, Offline Sync, dan Search Integrity (JANGAN DIUBAH)

> **PERINGATAN KERAS**: Aturan ini dibuat untuk mencegah masalah kritis seperti **"Sinkronisasi 0 produk"**, **"Pencarian hilang setelah selesai ketik"**, dan **"MySQL max_connections_per_hour exceeded"** di Hostinger. **DILARANG KERAS** mengubah atau melanggar aturan-aturan ini dalam upgrade atau refactoring fitur apapun.

---

## 1. Aturan Koneksi MySQL (Hostinger Resource Limit)

Database MySQL menggunakan shared hosting Hostinger dengan pembatasan ketat: **`max_connections_per_hour = 500`**.

### ⚠️ Larangan & Kewajiban:
1. **WAJIB Persistent Connection**:
   - Di `app/config/Database.php`, opsi `PDO::ATTR_PERSISTENT` **HARUS SELALU `true`**.
   - ❌ **DILARANG** mengubah menjadi `false`, karena setiap request HTTP akan membuka koneksi TCP baru yang menghabiskan kuota 500 koneksi dalam hitungan menit.
2. **Koneksi Tunggal per Request (Singleton)**:
   - Gunakan selalu `Database::getInstance()->getConnection()`.
   - ❌ **DILARANG** membuat instance `new PDO(...)` manual di controller atau model.
3. **Validasi Koneksi Stale**:
   - Karena koneksi persistent dapat menjadi stale/timeout oleh server MySQL, `Database.php` harus selalu memvalidasi dengan `SELECT 1` dan retry logic saat inisialisasi.

---

## 2. Aturan Service Worker (`sw.js`) & Anti Connection Storm

Service Worker berjalan di browser/PWA user dan dapat memicu beban koneksi server masif jika salah konfigurasi.

### ⚠️ Larangan & Kewajiban:
1. **DILARANG Pre-cache Dynamic PHP Routes pada `STATIC_ASSETS`**:
   - `STATIC_ASSETS` dalam `sw.js` **HANYA BOLEH** berisi file statis murni: `manifest.json`, icon/gambar, dan CDN Bootstrap.
   - ❌ **DILARANG** memasukkan rute controller dinamis (seperti `/sales`, `/sales/pos`, `/products`, `/purchases`, `/finance`, dll.) ke dalam `STATIC_ASSETS`.
   - Rute dinamis sudah di-cache secara natural saat user mengunjunginya (*runtime caching*). Pre-caching saat install akan menembak belasan koneksi MySQL secara paralel sekaligus.

---

## 3. Aturan Non-Destructive Update pada Live Search Frontend

Di halaman katalog produk (`app/views/products/index.php`), POS (`app/views/sales/pos.php`), dan halaman lainnya:

### ⚠️ Larangan & Kewajiban:
1. **Prinsip Non-Destructive Update**:
   - Pencarian lokal (IndexedDB / memory) berjalan instan (0ms) dan langsung merender hasil ke layar.
   - Request pencarian server berjalan di background secara asinkron.
   - **JIKA server mengembalikan `[]` (kosong) atau error/offline, tetapi pencarian lokal SUDAH menemukan produk:**
     - ❌ **DILARANG** menimpa tampilan dengan pesan "Tidak ditemukan" / array kosong!
     - ✅ **WAJIB** mempertahankan hasil pencarian lokal yang sudah tampil.
   - Hanya tampilkan "Tidak ditemukan" jika pencarian lokal DAN server sama-sama tidak menemukan hasil.
2. **Debounce Pencarian**:
   - Debounce minimal **300ms** pada input pencarian untuk mencegah spamming request API ke server pada setiap ketikan huruf.
   - Gunakan `AbortController` untuk membatalkan request sebelumnya yang belum selesai jika user terus mengetik.

---

## 4. Aturan Database SQLite Server (Offline Fallback Mirror)

Aplikasi memiliki mekanisme hybrid: jika MySQL Hostinger tidak dapat dijangkau (offline / koneksi habis / internet putus), sistem otomatis beralih (*fallback*) ke SQLite lokal di server (`storage/database/alfarezmart.sqlite`).

### ⚠️ Larangan & Kewajiban:
1. **SQLite Tidak Boleh Kosong**:
   - File `alfarezmart.sqlite` harus selalu memiliki tabel dan data cermin produk (`products`, `product_packagings`, `product_qty_prices`, `stock`, `brands`, `categories`, `units`).
   - Gunakan `sync_to_sqlite.php` jika perlu memperbarui data SQLite cermin dari MySQL.
2. **Endpoint `/api/products/sync`**:
   - Endpoint ini harus selalu dapat melayani permintaan baik saat MySQL aktif maupun saat SQLite aktif, dan tidak boleh mengembalikan 0 produk jika data tersedia di database manapun.
3. **Proteksi IndexedDB di Frontend**:
   - Di frontend (`layouts/app.php` dan `products/index.php`), jika server merespons dengan 0 produk, **DILARANG** menghapus (`clear()`) IndexedDB lokal yang sudah terisi.

---

## 5. Ringkasan File yang Dilindungi Terkait Aturan Ini

| File | Komponen Kritis yang Dilindungi |
|------|---------------------------------|
| [`app/config/Database.php`](file:///c:/xampp/htdocs/AlfarezMart/app/config/Database.php) | `PDO::ATTR_PERSISTENT => true`, validasi `SELECT 1`, hybrid fallback SQLite |
| [`sw.js`](file:///c:/xampp/htdocs/AlfarezMart/sw.js) | `STATIC_ASSETS` hanya file statis, runtime dynamic caching |
| [`app/views/products/index.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/products/index.php) | Live search non-destructive update, fallback IndexedDB |
| [`app/views/sales/pos.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/sales/pos.php) | Instant 0ms memory search, non-destructive background fetch |
| [`app/views/layouts/app.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/layouts/app.php) | `fixAndSyncProducts()`, proteksi data lokal jika server return 0 |
| [`sync_to_sqlite.php`](file:///c:/xampp/htdocs/AlfarezMart/sync_to_sqlite.php) | Script replikasi MySQL → SQLite fallback |

---

*Aturan ini dibuat pada 18 Agustus 2026 dan berlaku permanen di seluruh sesi pengembangan.*
