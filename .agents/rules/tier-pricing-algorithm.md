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

## 4. Aturan Kemasan Multi-Level (Cross-Packaging Upgrade & Combined Tier)

Ketika user menginput kuantitas dalam **kemasan level 1 (Pcs)** dan kuantitasnya menyentuh atau melebihi kemasan yang lebih tinggi (Pack, Renceng, Karton, Sak), sistem menerapkan **optimasi otomatis cross-packaging**:

1. Konversi qty input ke `targetBaseQty = qty * (base_qty || contained_qty)`.
2. Kumpulkan semua "chunk" yang tersedia dari level kemasan $\ge$ level aktif beserta tier-nya masing-masing.
3. **Urutkan berdasarkan `price_per_base_unit` termurah** (ascending), lalu `chunk_size` terbesar jika harga per unit dasarnya sama.
4. Terapkan secara **greedy**: gunakan chunk termurah dulu (misal Karton), lalu chunk termurah berikutnya (misal Pack atau Tier), dan sisa dihitung dengan harga satuan dasar.
5. Sisa pecahan (jika ada) dihitung proporsional dari chunk terkecil.

### 💡 Contoh Kasus Wajib:

- **Kasus A (1 Pcs = 7.000, 1 Karton isi 24 Pcs = 140.000):**
  - Input `24 Pcs` &rarr; Otomatis = **Rp 140.000** (Harga 1 Karton).
  - Input `25 Pcs` &rarr; Otomatis = **Rp 147.000** (`1 Karton @140.000 + 1 Pcs @7.000`).

- **Kasus B (1 Pcs = 1.200 [ada Tier 20+ @1.200], 1 Karton isi 40 Pcs = 46.000):**
  - Input `40 Pcs` &rarr; Otomatis = **Rp 46.000** (`1 Karton @46.000` / @1.150 per pcs), **BUKAN Rp 48.000** (`Tier 20+`).
  - Input `20 Pcs` &rarr; Otomatis = **Rp 24.000** (`Tier 20+ @1.200`).

- **Kasus C (1 Pcs = 7.000 [Tier 3 @6.800], 1 Pack isi 6 Pcs = 40.000):**
  - Input `7 Pcs` &rarr; Otomatis = **Rp 47.000** (`1 Pack @40.000 + 1 Pcs @7.000`).
  - Input `9 Pcs` &rarr; Otomatis = **Rp 60.400** (`1 Pack @40.000 + 3 Pcs Tier @6.800`).

- **Kasus D (Level > 1 Dipilih Eksplisit):**
  - Jika kasir memilih **Level 2 (Pack)** &rarr; hanya boleh di-upgrade ke level yang sama atau lebih tinggi (Level $\ge 2$, misal Karton jika mencapai kelipatan Karton), **TIDAK PERNAH** didegradasi ke Level 1.

## 5. Harga Custom

Jika user mengaktifkan **harga custom** (`useCustom = true`):
- Semua perhitungan tier dan cross-packaging **diabaikan**.
- Harga yang digunakan adalah `customLineTotal` atau `customUnitPrice` yang diinput user.
- Breakdown menampilkan "Harga custom".

## 6. Cache Invalidation & Sinkronisasi Rasio Kemasan (`base_qty`)

- Mapping `slimCatalog` di `pos.php` **WAJIB** menyertakan `base_qty: pkg.base_qty || pkg.contained_qty || 1`.
- Setiap perubahan pada `qty-pricing.js` **WAJIB** menaikkan:
  1. Parameter versi `$v = '?v=XX.XX'` di `app/views/layouts/app.php`
  2. `CURRENT_POS_CACHE_VER = 'vXX.XX_...'` di `preloadPosCatalog()` pada `app/views/sales/pos.php` agar cache lokal browser ter-invaliasi secara otomatis.

## 7. Lokasi Implementasi yang DILINDUNGI

### File Utama Algoritma
- **`public/js/qty-pricing.js`** &rarr; `QtyPricing` object:
  - `getActiveTier()` — mencari tier tertinggi yang cocok
  - `_applyTieredPricing()` — rekursif greedy tier pricing untuk single packaging
  - `getPricingBreakdown()` — perhitungan lengkap dengan cross-packaging upgrade & tier combination
  - `calculateTotalPrice()` — wrapper untuk total harga
  - `getPriceNote()` — keterangan breakdown otomatis yang rapi
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
- `app/models/ProductModel.php` — `saveQtyPricesForPackaging()`, `getPackagings()`, `attachPackagingsForProductList()`
- `app/models/PurchaseModel.php` — Menyimpan tier saat input barang
- `app/models/SaleModel.php` — Membaca tier saat proses transaksi

## 8. Larangan Regresi

Berikut hal-hal yang **DILARANG** dilakukan:

1. ❌ Mengalikan semua qty dengan harga tier tertinggi yang cocok (harus greedy recursive).
2. ❌ Mengabaikan harga kemasan tingkat atas (Level 2/3/4) ketika kuantitas Level 1 menyentuh atau melebihi kelipatan kemasan atasnya.
3. ❌ Mengabaikan tier yang ada di tengah-tengah sisa kuantitas setelah dikurangi kemasan atas.
4. ❌ Menghapus filter `sale_mode` pada tier matching.
5. ❌ Mengubah urutan prioritas: chunk termurah per base unit &rarr; chunk terbesar &rarr; sisa dasar/tier.
6. ❌ Membuat kemasan level > 1 didegradasi ke harga level 1.
7. ❌ Menghapus dukungan harga custom yang men-bypass tier.
8. ❌ Menghapus properti `base_qty` / `contained_qty` dari cache POS.
9. ❌ Mengubah pembulatan hasil (`Math.round`) menjadi pembulatan lain.
10. ❌ Menghapus price breakdown yang menunjukkan rincian perhitungan tier ke user.

---

*Aturan ini diperbarui secara permanen dan wajib dipatuhi oleh seluruh agent dalam pengembangan AlfarezMart.*
