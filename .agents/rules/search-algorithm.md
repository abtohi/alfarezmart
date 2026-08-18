# 🔒 ATURAN WAJIB: Algoritma Pencarian Produk (JANGAN DIUBAH)

> **PERINGATAN KERAS**: Algoritma pencarian produk di bawah ini adalah hasil iterasi yang sudah teruji dan disetujui. **DILARANG KERAS** mengubah, menyederhanakan, atau menghapus bagian apapun dari algoritma ini tanpa persetujuan eksplisit dari pemilik proyek. Setiap perubahan fitur atau upgrade WAJIB mempertahankan semua perilaku pencarian yang didokumentasikan di sini.

---

## 1. Multi-Keyword AND Matching

Setiap query pencarian dipecah menjadi kata-kata individual berdasarkan spasi. **SEMUA kata harus cocok** (AND logic) — sebuah produk hanya muncul jika setiap kata cocok dengan minimal satu atribut.

**Contoh**: `Royco 500` → kata `royco` harus cocok DAN kata `500` harus cocok.

## 2. Field yang Harus Di-Match (Per Kata)

Setiap kata di-match terhadap field berikut (OR logic antar field):

| # | Field | Keterangan |
|---|-------|-----------|
| 1 | `p.full_name` | Nama lengkap produk |
| 2 | `p.short_label` | Label thermal / nama singkat |
| 3 | `p.invoice_name` | Nama di invoice |
| 4 | `p.supplier_invoice_name` | Nama di faktur supplier |
| 5 | `p.code` | Kode/SKU produk |
| 6 | `p.supplier_product_code` | Kode produk supplier |
| 7 | `b.name` (brand) | Nama merek/brand |
| 8 | `c.name` (category) | Nama kategori |
| 9 | `pp.barcode` | Barcode di semua level kemasan |
| 10 | **Harga kemasan** | `sell_price_retail`, `sell_price_wholesale`, `buy_price` di semua level packaging |

### ⚠️ Aturan Khusus Harga (Price Matching)

- Jika kata kunci berupa angka (contoh: `500`, `3500`, `10000`), maka kata tersebut di-match terhadap:
  - `ROUND(pp.sell_price_retail)` — harga ecer per kemasan
  - `ROUND(pp.sell_price_wholesale)` — harga grosir per kemasan
  - `ROUND(pp.buy_price)` — harga beli per kemasan
  - `p.price_small_retail` — harga ecer satuan terkecil (fallback)
  - `p.price_small_wholesale` — harga grosir satuan terkecil (fallback)
- Angka yang diketik user bisa mengandung separator (`.` atau `,`) yang harus di-strip dulu sebelum matching.
- Matching harga menggunakan **substring match** (`LIKE '%500%'` atau `String(price).includes('500')`).

## 3. Scoring & Ranking (Pengurutan Hasil)

Hasil pencarian **WAJIB diurutkan berdasarkan relevansi (score)**, bukan hanya filter. Aturan skor:

| Kondisi | Skor |
|---------|------|
| Label/nama dimulai dengan query penuh | +60 |
| Label/nama mengandung query penuh | +40 |
| Kata cocok di nama produk (name/label/invoice) | +30 per kata |
| Kata cocok di brand/kategori | +20 per kata |
| Kata cocok di kode/barcode | +15 per kata |
| Kata cocok **eksak** di harga kemasan | **+50 per kata** |
| Kata cocok substring di harga kemasan | +15 per kata |

**KRITIS**: Produk yang harganya cocok **eksak** (contoh: search `500` dan harga pcs = `500`) HARUS muncul **di atas** produk yang harganya hanya cocok substring (contoh: harga `5000` mengandung `500`).

## 4. Lokasi Implementasi yang DILINDUNGI

Algoritma ini diimplementasikan di lokasi berikut. **JANGAN** mengubah logika search di file-file ini tanpa mempertahankan semua aturan di atas:

### Backend (Server-Side SQL)
- `app/models/ProductModel.php` → `searchProducts()` dan `getProductsWithPrices()`
- `app/models/SupplierProductModel.php` → `searchProductsBySupplier()`
- `app/controllers/ApiController.php` → `searchProducts()` endpoint

### Frontend (Client-Side JavaScript)
- `public/js/db.js` → `searchProducts()` dan `searchProductsBySupplier()`
- `public/js/offline-db.js` → `searchProducts()` dan `searchProductsBySupplier()`
- `app/views/sales/pos.php` → `performSearch()` in-memory filter
- `app/views/products/index.php` → `doOfflineSearch()` fallback filter

## 5. Aturan Teknis PDO (MySQL)

- **DILARANG** menggunakan named parameter PDO yang sama lebih dari sekali dalam satu query.
- Gunakan parameter terpisah untuk setiap penggunaan: `_price_r`, `_price_w`, `_price_b` untuk retail, wholesale, buy price.
- Contoh BENAR: `:kw_0_price_r`, `:kw_0_price_w`, `:kw_0_price_b` (masing-masing di-bind ke nilai yang sama).
- Contoh SALAH: `:kw_0_price` dipakai 3 kali → akan menyebabkan PDO error dan hasil pencarian kosong.

## 6. Konsistensi Antar Modul

Algoritma pencarian **HARUS konsisten** di semua modul:
- Halaman Produk (katalog + live search dropdown)
- Kasir POS (search bar + suggestions)
- Input Barang / Pembelian (search produk supplier)
- Scan / Cek Harga (search manual)
- Offline mode (IndexedDB / in-memory fallback)

Jika ada perubahan di satu modul, **WAJIB** diterapkan juga di semua modul lainnya.

## 7. Larangan Regresi

Berikut hal-hal yang **DILARANG** dilakukan saat upgrade fitur apapun:

1. ❌ Menghapus price matching dari algoritma search
2. ❌ Mengubah AND logic menjadi OR logic antar kata kunci
3. ❌ Menghilangkan scoring/ranking sehingga hasil tidak terurut relevansi
4. ❌ Menggunakan PDO named parameter yang duplikat
5. ❌ Mengubah search di satu modul tanpa menyesuaikan modul lainnya
6. ❌ Menghapus field matching (barcode, brand, category, supplier code, dll)
7. ❌ Mengubah logika exact price match boost (skor +50)
8. ❌ Menghilangkan fallback `price_small_retail` / `price_small_wholesale`

---

*Aturan ini dibuat pada 18 Agustus 2026 dan berlaku permanen hingga ada persetujuan eksplisit dari pemilik proyek untuk mengubahnya.*
