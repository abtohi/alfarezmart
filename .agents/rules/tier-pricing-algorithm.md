# 🔒 ATURAN WAJIB: Algoritma Harga Tier (Qty Pricing) — JANGAN DIUBAH

> **PERINGATAN KERAS**: Algoritma perhitungan harga tier (qty pricing) di bawah ini adalah aturan bisnis kritis yang sudah teruji. **DILARANG KERAS** mengubah, menyederhanakan, atau menghapus logika apapun tanpa persetujuan eksplisit dari pemilik proyek. Perubahan pada algoritma ini berpotensi menyebabkan kerugian finansial langsung.

---

## 1. Konsep Dasar Harga Tier

Setiap kemasan (packaging) produk bisa memiliki **harga tier** berdasarkan kuantitas minimum pembelian (`min_qty`). Tier disimpan di tabel `product_qty_prices` dan diekspos sebagai array `qty_prices` pada setiap packaging.

**Contoh Data Tier:**
| min_qty | unit_price | Keterangan |
|---------|-----------|------------|
| 1 (base) | 27.500 | Harga dasar per pcs |
| 2 | 27.250 | Beli ≥2 pcs, harga per pcs jadi 27.250 |
| 5 | 21.000 | Beli ≥5 pcs, harga per pcs jadi 21.000 |

## 2. Aturan Perhitungan: Greedy Recursive Tiered Pricing

### ⚠️ ATURAN KRITIS — DILARANG DIUBAH

Ketika user membeli **3 pcs**, perhitungannya adalah:

```
Tier tertinggi yang cocok: min_qty=2 (karena 3 ≥ 2)
→ 2 pcs × Rp27.250 = Rp54.500
→ Sisa: 3 - 2 = 1 pcs
→ 1 pcs tidak memenuhi tier apapun → pakai harga dasar
→ 1 pcs × Rp27.500 = Rp27.500
→ TOTAL = Rp54.500 + Rp27.500 = Rp82.000
```

### Algoritma Step-by-Step:

1. **Cari tier tertinggi** yang `min_qty ≤ kuantitas_saat_ini`.
2. **Hitung berapa kali** tier tersebut bisa diterapkan: `bundles = floor(qty / min_qty)`.
3. **Terapkan harga tier** untuk `bundles × min_qty` unit.
4. **Hitung sisa**: `remainder = qty - (bundles × min_qty)`.
5. **Rekursif**: Ulangi langkah 1-4 untuk sisa kuantitas, dengan tier yang lebih rendah.
6. Jika **tidak ada tier yang cocok** untuk sisa, gunakan **harga dasar** (`sell_price_retail` atau `sell_price_wholesale`).

### Contoh Lain:

**Beli 7 pcs** dengan tier [2 → Rp27.250, 5 → Rp21.000]:
```
Tier tertinggi: min_qty=5 → 1×5 pcs = 5 × Rp21.000 = Rp105.000
Sisa: 7 - 5 = 2 pcs
Tier tertinggi untuk sisa: min_qty=2 → 1×2 pcs = 2 × Rp27.250 = Rp54.500
Sisa: 0
TOTAL = Rp105.000 + Rp54.500 = Rp159.500
```

**Beli 12 pcs** dengan tier [2 → Rp27.250, 5 → Rp21.000]:
```
Tier tertinggi: min_qty=5 → 2×5 pcs = 10 × Rp21.000 = Rp210.000
Sisa: 12 - 10 = 2 pcs
Tier tertinggi untuk sisa: min_qty=2 → 1×2 pcs = 2 × Rp27.250 = Rp54.500
Sisa: 0
TOTAL = Rp210.000 + Rp54.500 = Rp264.500
```

### ❌ YANG SALAH (DILARANG):

- ❌ `3 pcs × Rp27.250 = Rp81.750` → **SALAH**, tidak boleh mengalikan semua qty dengan harga tier tertinggi yang cocok
- ❌ `3 pcs × Rp27.500 = Rp82.500` → **SALAH**, tidak boleh mengabaikan tier
- ❌ `3 pcs × Rp21.000 = Rp63.000` → **SALAH**, tier min_qty=5 tidak berlaku untuk qty=3

## 3. Aturan Pemilihan Harga Berdasarkan Mode Penjualan

- **Mode Ecer (`retail`)**: Gunakan `sell_price_retail` sebagai harga dasar.
- **Mode Grosir (`wholesale`)**: Gunakan `sell_price_wholesale` sebagai harga dasar.
- Tier pricing memiliki field `sale_mode`: `'retail'`, `'wholesale'`, atau `'both'`.
- Tier hanya berlaku jika `sale_mode === 'both'` ATAU `sale_mode === mode_penjualan_aktif`.

## 4. Aturan untuk Kemasan Multi-Level (Cross-Packaging Bundle)

Ketika user membeli dalam **kemasan level 1 (pcs)** dan ada kemasan yang lebih besar (Pack, Karton, dll), sistem menerapkan **optimasi otomatis cross-packaging**:

1. Konversi qty ke `base_qty` (satuan dasar).
2. Kumpulkan semua "chunk" yang tersedia dari semua level kemasan + tier-nya.
3. **Urutkan berdasarkan `price_per_base_unit` termurah** (ascending), lalu `chunk_size` terbesar.
4. Terapkan secara **greedy**: gunakan chunk termurah dulu, lalu berikutnya, dst.
5. Sisa pecahan (jika ada) dihitung proporsional dari chunk terkecil.

**PENTING**: Kemasan **level > 1** (Pack, Renceng, Karton, Sak) **SELALU** menggunakan harga eksplisitnya sendiri, tidak pernah dihitung ulang dari level 1. Hanya level 1 yang boleh di-optimasi cross-packaging.

## 5. Harga Custom

Jika user mengaktifkan **harga custom** (`useCustom = true`):
- Semua perhitungan tier dan cross-packaging **diabaikan**.
- Harga yang digunakan adalah `customLineTotal` atau `customUnitPrice` yang diinput user.
- Breakdown menampilkan "Harga custom".

## 6. Lokasi Implementasi yang DILINDUNGI

### File Utama Algoritma
- **`public/js/qty-pricing.js`** → `QtyPricing` object:
  - `getActiveTier()` — mencari tier tertinggi yang cocok
  - `_applyTieredPricing()` — rekursif greedy tier pricing
  - `getPricingBreakdown()` — perhitungan lengkap dengan cross-packaging
  - `calculateTotalPrice()` — wrapper untuk total harga
  - `resolveUnitPrice()` — harga per unit (untuk display)

### File yang Menggunakan Algoritma
- `app/views/sales/pos.php` — Kasir POS (kalkulasi keranjang)
- `app/views/scanner/index.php` — Scan/Cek Harga (display tier)
- `app/views/catalog/index.php` — Katalog publik (display tier)
- `app/views/products/show.php` — Detail produk (display tier)
- `app/views/products/edit.php` — Edit produk (kelola tier)
- `app/views/products/create.php` — Buat produk (kelola tier)
- `app/views/purchases/create.php` & `edit.php` — Input barang (kelola tier)

### Backend
- `app/models/ProductModel.php` — `saveQtyPricesForPackaging()`, `getPackagings()`
- `app/models/PurchaseModel.php` — Menyimpan tier saat input barang
- `app/models/SaleModel.php` — Membaca tier saat proses transaksi

## 7. Larangan Regresi

Berikut hal-hal yang **DILARANG** dilakukan:

1. ❌ Mengalikan semua qty dengan harga tier tertinggi yang cocok (harus greedy recursive)
2. ❌ Menghapus logika recursive `_applyTieredPricing()`
3. ❌ Mengubah logika cross-packaging bundle optimization
4. ❌ Menghapus filter `sale_mode` pada tier matching
5. ❌ Mengubah urutan prioritas: tier tertinggi yang cocok → rekursif ke bawah → harga dasar
6. ❌ Membuat kemasan level > 1 dihitung ulang dari harga level 1
7. ❌ Menghapus dukungan harga custom yang men-bypass tier
8. ❌ Mengubah pembulatan hasil (`Math.round`) menjadi pembulatan lain
9. ❌ Menghapus price breakdown yang menunjukkan rincian perhitungan tier ke user

---

*Aturan ini dibuat pada 18 Agustus 2026 dan berlaku permanen hingga ada persetujuan eksplisit dari pemilik proyek untuk mengubahnya.*
