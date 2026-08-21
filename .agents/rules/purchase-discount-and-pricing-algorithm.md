# 🔒 ATURAN WAJIB: Algoritma Diskon, PPN, dan Harga Beli Barang Masuk (Pembelian) — JANGAN DIUBAH

> **PERINGATAN KERAS**: Algoritma perhitungan harga modal/beli (nett price), PPN, dan diskon barang masuk di bawah ini adalah aturan bisnis kritis yang sudah teruji dan tervalidasi. **DILARANG KERAS** mengubah, menyederhanakan, atau menghapus logika perhitungan diskon dan harga beli tanpa persetujuan eksplisit dari pemilik proyek. Perubahan sembarangan dapat menyebabkan harga modal produk menjadi 0 atau kalkulasi margin keuntungan keliru.

---

## 1. Konsep Dasar Harga Beli & Kemasan di AlfarezMart

1. **Harga Beli Gross (`buy_price`)**:
   - Harga modal per kemasan sebelum PPN dan sebelum diskon.
2. **Harga Beli Nett (`harga_nett` / `buy_price_nett`)**:
   - Harga modal bersih per kemasan setelah ditambahkan PPN dan dikurangkan Diskon.
   - Nilai inilah yang **wajib disimpan ke `product_packagings.buy_price`** agar menjadi acuan modal riil untuk perhitungan margin keuntungan penjualan eceran dan grosir.
3. **Diskon Total per Produk (`diskon_value`)**:
   - Dalam form input barang masuk, jika mode diskon adalah **Rupiah (`rp`)**, nilai diskon yang dimasukkan user adalah **TOTAL DISKON untuk seluruh kuantitas yang dibeli pada baris tersebut** (label UI: `Diskon (Rp=Total)`).
   - Jika mode diskon adalah **Persen (`pct`)**, nilai diskon adalah persentase langsung (%).

---

## 2. Formula Perhitungan Diskon & Modal Nett

### A. Perhitungan Diskon per Unit:
- **Mode Persen (`pct`)**:
  $$\text{Diskon per Unit} = \text{buy\_price} \times \left(\frac{\text{diskon\_value}}{100}\right)$$
- **Mode Rupiah (`rp`)**:
  $$\text{Diskon per Unit} = \frac{\text{diskon\_value}}{\text{quantity}}$$

### B. Perhitungan PPN per Unit:
$$\text{PPN per Unit} = \text{buy\_price} \times \left(\frac{\text{ppn\_pct}}{100}\right)$$

### C. Perhitungan Harga Modal Bersih (Nett) per Satuan:
$$\text{Harga Nett} = \max\left(0, \text{buy\_price} + \text{PPN per Unit} - \text{Diskon per Unit}\right)$$

### D. Perhitungan Total Harga Pembelian Baris Item:
$$\text{Total Price} = \text{quantity} \times \text{Harga Nett}$$

---

## 3. Propagasi Diskon ke Multi-Kemasan (Packaging Levels)

Ketika produk memiliki beberapa level kemasan (contoh: Level 1 = PCS isi 1, Level 2 = DUS isi 40):
1. **Total Pcs yang Dibeli**:
   $$\text{Total Pcs Dibeli} = \text{quantity} \times \text{base\_qty kemasan yang dipilih}$$
2. **Diskon per Pcs Dasar**:
   - Untuk Mode `rp`:
     $$\text{Diskon per Pcs} = \frac{\text{diskon\_value}}{\text{Total Pcs Dibeli}}$$
   - Untuk Mode `pct`:
     $$\text{Diskon per Pcs} = \text{buy\_price per Pcs} \times \left(\frac{\text{diskon\_value}}{100}\right)$$
3. **Diskon & Harga Nett Kemasan Level Lain (`pkg`)**:
   - Diskon untuk kemasan `pkg`:
     $$\text{Diskon Kemasan} = \text{Diskon per Pcs} \times \text{pkg.base\_qty}$$
   - Harga modal nett kemasan `pkg`:
     $$\text{pkg.harga\_nett} = \max\left(0, \text{pkg.buy\_price} + \text{PPN} - \text{Diskon Kemasan}\right)$$

---

## 4. Aturan Wajib di Frontend (`create.php` & `edit.php`)

1. **Fungsi `calcItemNett`**:
   - Parameter `qty` **wajib valid (> 0)** dan tidak boleh mengasumsikan `qty = 1` jika quantity barang lebih dari 1.
2. **Payload `submitPurchase`**:
   - `items[].buy_price`: Mengirim harga beli gross per kemasan yang dipilih.
   - `items[].harga_nett`: Mengirim harga nett per kemasan yang dipilih.
   - `items[].packagings[].buy_price`: Mengirim harga **modal NETT** masing-masing level kemasan untuk diupdate ke database.

---

## 5. Aturan Wajib di Backend (`PurchaseModel.php`)

1. Di `createWithDetails` & `updateWithDetails`:
   - Diskon per unit untuk mode `'rp'` dihitung dari `diskon_value / quantity`.
   - Record di tabel `purchase_items`:
     - `buy_price`: Menyimpan harga beli gross per unit.
     - `discount_amount`: Menyimpan nominal diskon per unit.
     - `nett_price`: Menyimpan harga modal bersih per unit.
     - `total_price`: Menyimpan total tagihan baris (`quantity * nett_price`).
   - Update ke tabel `product_packagings`:
     - Kolom `buy_price` **harus diupdate dengan harga modal NETT** (setelah PPN dan Diskon), tidak boleh menjadi 0 selama harga modal nett > 0.

---

## 6. Format Tampilan Modal Nett & Rincian Diskon di UI (`buildMiniPricingTableHtml` & `buildNettInfo`)

1. **Dilarang keras menampilkan label generik tanpa nominal** (misal hanya `-Disc`).
2. **Format Rincian Diskon per Kemasan**:
   - Jika mode `pct`: Tampilkan `−[nilai]%` (contoh: `−10%`).
   - Jika mode `rp`: Tampilkan nominal rupiah diskon spesifik untuk kemasan tersebut: `−Rp[nominal_per_kemasan]` (contoh: `−Rp400` untuk pcs, `−Rp9.600` untuk karton).
3. **Format PPN**:
   - Hanya tampilkan PPN jika `ppn > 0` (contoh: `+11%PPN`). Jika `ppn == 0`, jangan tampilkan `+0%PPN`.
4. **Contoh Tampilan Kolom Modal Nett**:
   - Diskon saja: `Rp5.100` dengan sub-label `(−Rp400)`
   - PPN + Diskon: `Rp5.705` dengan sub-label `(+11%PPN −Rp400)`
   - Tanpa PPN & Diskon: `Rp5.500` (tanpa sub-label)
