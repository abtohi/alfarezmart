# 🔒 ATURAN WAJIB: AI Invoice Scanner & Pembacaan Faktur AlfarezMart (PERMANEN)

> **PERINGATAN KERAS UNTUK SELURUH AGENT & PENGEMBANG**:
> Aturan ini mengatur seluruh arsitektur pemindaian faktur/nota AI (`app/services/invoice/`, `app/views/purchases/create.php`, `app/views/purchases/edit.php`, `app/views/settings/app.php`, dan `app/controllers/ApiController.php`).
> **DILARANG KERAS MENGUBAH ATAU MENGURANGI ATURAN INI TANPA PERSETUJUAN EKSPLISIT DARI USER.**

---

## 1. Aturan Model AI Scanner & Optimasi Kecepatan (OpenRouter Vision)

1. **Model Utama (Default & Recommended)**:
   - **`openrouter/auto`**: Router multimodal otomatis cerdas OpenRouter yang mengarahkan ke model vision tercanggih dan tercepat (seperti Claude 3.5/3.7 Sonnet / Haiku / GPT-5 / Gemini).
2. **Fallback Model (100% Free Multimodal Vision)**:
   - `google/gemma-4-31b-it:free`
   - `google/gemma-4-26b-a4b-it:free`
   - `nvidia/nemotron-nano-12b-v2-vl:free`
   - `meta-llama/llama-3.2-11b-vision-instruct:free`
3. **Optimasi Kecepatan & Kompresi Payload**:
   - Gambar invoice dari kamera di-preprocess di browser via HTML5 Canvas menjadi format **JPEG murni** (Quality `0.82`, Max Dimension `1500px`).
   - Ukuran payload dijaga antara **150KB – 300KB** agar pengiriman cURL instan dan decoding OpenRouter vision selesai dalam waktu **8–15 detik**.
   - Timeout cURL per attempt disetel **45 detik** dengan *fast failover*.

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

## 3. ATURAN WAJIB 100% ITEM MASUK KE KERANJANG (Direct 1-to-1 Ingestion)

> **PRINSIP UTAMA**: Jumlah item yang berhasil diidentifikasi oleh AI harus **100% SAMA** dengan jumlah item yang tampil di keranjang/tabel pembelian. **TIDAK BOLEH ADA ITEM YANG HILANG, TERGABUNG, ATAU DIBUANG.**

1. **Direct 1-to-1 Cart Object Construction**:
   - Setiap baris hasil ekstraksi AI **langsung dibuatkan 1 baris objek kartu item tersendiri** di `purchaseItems` (tidak boleh memanggil fungsi helper yang melakukan *merging* atau *overwriting*).
2. **Item yang Cocok (`is_matched = true`)**:
   - Dimasukkan langsung ke keranjang dengan kemasan (`packaging_level`) yang sesuai.
3. **Item yang Belum Cocok (`is_matched = false` / Unmatched)**:
   - **WAJIB TETAP DIMASUKKAN KE KERANJANG** sebagai *Draft Item* (`is_unmatched: true`, `product_id: null`).
   - Kuantitas, satuan (`unit`), harga satuan (`unit_price`), dan total harga (`total_price`) hasil scan AI wajib dipertahankan utuh sehingga total rupiah faktur tetap 100% akurat.
   - Di antarmuka keranjang, item ditandai badge kuning `⚠️ Hasil Scan (Draft)` dan disediakan tombol **"Hubungkan Produk"**.
4. **Modal Hubungkan Produk (`openLinkProductModal`) & Pembelajaran Alias**:
   - Memungkinkan kasir/admin mencari dan memilih produk master dalam 1 klik.
   - Saat produk dipilih, sistem mengikat `product_id`, menyesuaikan kemasan, menutup modal via `AppModal.close()`, dan memanggil endpoint `/api/ai/learn-alias` untuk menyimpan alias nota tersebut ke memori AI secara otomatis.

---

## 4. Modal Pop-up Hasil Scan Modern & Elegan (`showScanResultModal`)

- Saat scan selesai, sistem menampilkan pop-up modal modern bergaya glassmorphism / dark-mode AlfarezMart.
- Modal menampilkan:
  1. Header ringkasan: Total baris terdeteksi, durasi scan dalam detik.
  2. Kartu statistik: Total Item, Item Cocok Otomatis (Hijau), Item Draft (Kuning).
  3. Total estimasi nilai rupiah nota.
  4. Rincian list interaktif setiap item beserta status pencocokannya.
  5. Tombol aksi cepat: "Lihat di Keranjang".

---

## 5. Ringkasan File Kritis yang Dilindungi

| File | Komponen Kritis |
|------|-----------------|
| [`app/config/Routes.php`](file:///c:/xampp/htdocs/AlfarezMart/app/config/Routes.php) | Route `/api/ai/scan-invoice` dan `/api/ai/learn-alias` |
| [`app/controllers/ApiController.php`](file:///c:/xampp/htdocs/AlfarezMart/app/controllers/ApiController.php) | `scanInvoiceAI()` dan `learnAliasAI()` |
| [`app/services/invoice/InvoiceScanService.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/InvoiceScanService.php) | Multi-model routing, prompt builder, timeout 45s, auto-reconnect MySQL |
| [`app/services/invoice/InvoiceLearningService.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/InvoiceLearningService.php) | Auto-learn alias, log pembacaan faktur, supplier product code linking |
| [`app/services/invoice/ProductMatcher.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/ProductMatcher.php) | Kamus FMCG, exact code matching, composite brand/variant boost |
| [`app/services/invoice/LearnedAliasLookup.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/LearnedAliasLookup.php) | In-memory indexing multi-baris alias, supplier codes, & product master |
| [`public/js/components.js`](file:///c:/xampp/htdocs/AlfarezMart/public/js/components.js) | `AppModal.show()`, `AppModal.close()`, dan `AppModal.hide()` alias |
| [`app/views/purchases/create.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/purchases/create.php) | `showScanResultModal`, 100% item insertion, `copyCartAsJson`, `openLinkProductModal` |
| [`app/views/purchases/edit.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/purchases/edit.php) | `showScanResultModal`, 100% item insertion, `copyCartAsJson`, `openLinkProductModal` |

---

*Aturan ini diperbarui permanen pada 21 Agustus 2026 dan wajib dipatuhi oleh seluruh agent dan sesi pengembangan di masa mendatang.*
