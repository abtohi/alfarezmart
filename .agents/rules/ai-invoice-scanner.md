# 🔒 ATURAN WAJIB: AI Invoice Scanner & Pembacaan Faktur AlfarezMart (PERMANEN)

> **PERINGATAN KERAS UNTUK SELURUH AGENT & PENGEMBANG**:
> Aturan ini mengatur seluruh arsitektur pemindaian faktur/nota AI (`app/services/invoice/`, `app/views/purchases/create.php`, `app/views/purchases/edit.php`, dan `app/views/settings/app.php`).
> **DILARANG KERAS MENGUBAH ATAU MENGURANGI ATURAN INI TANPA PERSETUJUAN EKSPLISIT DARI USER.**

---

## 1. Aturan Model AI Scanner (OpenRouter Vision)

1. **Model Utama (Default & Recommended)**:
   - **`openrouter/auto`**: Router multimodal otomatis cerdas OpenRouter yang mengarahkan ke model vision tercanggih dan tercepat (seperti Claude 3.5/3.7 Sonnet / Haiku / GPT-5 / Gemini).
2. **Fallback Model (100% Free Multimodal Vision)**:
   - `google/gemma-4-31b-it:free`
   - `google/gemma-4-26b-a4b-it:free`
   - `nvidia/nemotron-nano-12b-v2-vl:free`
   - `meta-llama/llama-3.2-11b-vision-instruct:free`
3. **Timeout & Failover**:
   - Batas waktu cURL per attempt disetel **45 detik** dengan *fast failover*.
   - Waktu scan invoice dijaga agar selesai dalam **10–25 detik** dan tidak boleh mengalami *timeout* atau *circuit breaker crash*.

---

## 2. Aturan Matching Produk Multi-Tiered (Kecerdasan Ekstraksi)

Setiap item yang dibaca dari invoice dicocokkan ke database produk dengan hierarki prioritas:
1. **Tier 1 — Exact Kode Produk Supplier (`supplier_product_code`) (Skor 200 / Instant Match)**:
   - Sanitasi string: hilangkan spasi, tanda hubung (`-`), titik, dan *leading zeros* (`03066` ↔ `3066`).
   - Pencocokan langsung dengan `supplier_product_code`, `products.code`, dan `product_packagings.barcode`.
2. **Tier 2 — Multi-Line Alias Invoice (`supplier_invoice_name`) (Skor 160–195)**:
   - Mendukung multi-baris alias per produk untuk multi-supplier.
   - Kamus singkatan FMCG Indonesia lengkap (`KCP` → Kecap, `MNS` → Manis, `SAM EP` → Sambal Extra Pedas, `TOM` → Tomat, `PET` → Botol, `PCH` → Pouch, `BSR/TGH/KCL`, dll.).
3. **Tier 3 — Komposit Brand + Varian + Ukuran/Gramatur + Estimasi Harga (Price Proximity)**:
   - Toleransi fluktuasi gramatur pabrik (*shrinkflation*) 10–20%.
   - Pencocokan harga satuan terhadap harga beli kemasan (`buy_price`) untuk menentukan level kemasan (Karton vs Pack/Renceng vs PCS) secara presisi.

---

## 3. ATURAN WAJIB 100% ITEM MASUK KE KERANJANG (Cart Ingestion)

> **PRINSIP UTAMA**: Jumlah item yang berhasil diidentifikasi oleh AI harus **100% SAMA** dengan jumlah item yang tampil di keranjang/tabel pembelian. **TIDAK BOLEH ADA ITEM YANG HILANG ATAU DIBUANG.**

1. **Item yang Cocok (`is_matched = true`)**:
   - Dimasukkan langsung ke keranjang dengan kemasan (`packaging_level`) yang sesuai.
   - Jika item dengan level tersebut sudah ada di keranjang, jumlah kuantitas dan totalnya diakumulasikan (`quantity += scanQty`, `total += scanTotal`).
2. **Item yang Belum Cocok (`is_matched = false` / Unmatched)**:
   - **WAJIB TETAP DIMASUKKAN KE KERANJANG** sebagai *Draft Item* (`is_unmatched: true`, `product_id: null`).
   - Kuantitas, satuan (`unit`), harga satuan (`unit_price`), dan total harga (`total_price`) hasil scan AI wajib dipertahankan utuh sehingga total rupiah faktur tetap 100% akurat.
   - Di antarmuka keranjang, item ditandai badge kuning `⚠️ Hasil Scan (Draft)` dan disediakan tombol **"Hubungkan Produk"**.
3. **Modal Hubungkan Produk (`openLinkProductModal`)**:
   - Memungkinkan kasir/admin mencari dan memilih produk master dalam 1 klik.
   - Saat produk dipilih, sistem mengikat `product_id`, menyesuaikan kemasan, dan otomatis menyimpan alias nota tersebut ke `learned_aliases` via endpoint `/api/ai/learn-alias` untuk pemindaian berikutnya.

---

## 4. Ringkasan File Kritis yang Dilindungi

| File | Komponen Kritis |
|------|-----------------|
| [`app/services/invoice/InvoiceScanService.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/InvoiceScanService.php) | Multi-model routing, prompt builder, timeout 45s, auto-reconnect MySQL |
| [`app/services/invoice/ProductMatcher.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/ProductMatcher.php) | Kamus FMCG, exact code matching, composite brand/variant boost |
| [`app/services/invoice/LearnedAliasLookup.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/LearnedAliasLookup.php) | In-memory indexing multi-baris alias, supplier codes, & product master |
| [`app/views/purchases/create.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/purchases/create.php) | 100% item insertion loop, unmatched card rendering, `openLinkProductModal` |
| [`app/views/purchases/edit.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/purchases/edit.php) | 100% item insertion loop, unmatched card rendering, `openLinkProductModal` |
| [`app/views/settings/app.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/settings/app.php) | Dropdown model AI Scanner vision yang aktif dan terverifikasi |

---

*Aturan ini diperbarui permanen pada 21 Agustus 2026 dan wajib dipatuhi oleh seluruh agent dan sesi pengembangan di masa mendatang.*
