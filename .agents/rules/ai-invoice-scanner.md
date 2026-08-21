# 🔒 ATURAN BAKU & ARSITEKTUR PERMANEN: AI Invoice Scanner AlfarezMart

> ⛔ **PERINGATAN KERAS UNTUK SELURUH AI AGENT & DEVELOPER**:
> Seluruh arsitektur, algoritma, dan alur kerja di bawah ini telah diverifikasi, disetujui, dan dinyatakan **SEMPURNA** oleh Pemilik Sistem pada 21 Agustus 2026.
> **DILARANG MENGUBAH, MENGURANGI, ATAU MEREFACTOR ALGORITMA INI.**
> Penambahan fitur baru di masa mendatang HANYA diperbolehkan secara aditif tanpa merusak atau mengubah prinsip dasar di bawah ini.

---

## 1. Algoritma Preprocessing Gambar & Optimasi Kecepatan Frontend

Setiap foto invoice dari kamera atau galeri wajib melalui fungsi `compressImageForAI` sebelum dikirim ke backend:
- **Format**: `image/jpeg` (Jangan gunakan WebP atau PNG mentah untuk pengiriman OCR vision).
- **Dimensi Maksimal**: `1500px` (skala proporsional w/h).
- **Kualitas Kompresi**: `0.82`.
- **Target Ukuran Payload**: `< 300KB` (umumnya 150KB – 250KB).
- **Tujuan**: Memastikan waktu upload cURL dan proses decoding vision AI pada server OpenRouter berlangsung sangat cepat (**8–15 detik**, tidak boleh menyentuh 1 menit).

```javascript
// FORMAT STANDAR WAJIB FRONTEND
async function compressImageForAI(dataUrl, maxDimension = 1500, quality = 0.82) {
    if (!dataUrl || !dataUrl.startsWith('data:image')) return dataUrl;
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            let w = img.width, h = img.height;
            if (w > maxDimension || h > maxDimension) {
                if (w > h) { h = Math.round((h * maxDimension) / w); w = maxDimension; }
                else { w = Math.round((w * maxDimension) / h); h = maxDimension; }
            }
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, w, h);
            try { resolve(canvas.toDataURL('image/jpeg', quality)); }
            catch(e) { resolve(dataUrl); }
        };
        img.onerror = () => resolve(dataUrl);
        img.src = dataUrl;
    });
}
```

---

## 2. Arsitektur Routing Model Vision Backend (`InvoiceScanService.php`)

1. **Model Utama (Default & Prioritas 1)**:
   - `openrouter/auto`: Router multimodal cerdas OpenRouter yang otomatis memanggil model vision tercanggih dan tercepat (seperti Claude 3.5/3.7 Sonnet / Haiku / GPT-5 / Gemini Flash).
2. **Fallback Vision Models (100% Free Multimodal Vision)**:
   - `google/gemma-4-31b-it:free`
   - `google/gemma-4-26b-a4b-it:free`
   - `nvidia/nemotron-nano-12b-v2-vl:free`
   - `meta-llama/llama-3.2-11b-vision-instruct:free`
3. **Ketahanan Koneksi & cURL**:
   - Timeout cURL per model attempt: **45 detik**.
   - Auto-reconnect PDO MySQL jika terjadi *connection gone away* selama inferensi AI berlangsung.
   - Cache hashing gambar pada tabel `ai_scan_cache` untuk menghindari biaya/waktu scan berulang pada gambar yang sama.

---

## 3. Algoritma Multi-Tiered Product Matching Engine (`ProductMatcher.php`)

Pencocokan teks invoice ke database produk master wajib mematuhi hierarki 3 tier:
1. **Tier 1 — Exact Kode Produk Supplier (`supplier_product_code`) (Skor 200 / Instant Match)**:
   - Sanitasi kode: hilangkan spasi, tanda hubung, titik, dan *leading zeros* (`03066` ↔ `3066`).
   - Pencocokan langsung dengan `supplier_product_code`, `products.code`, dan barcode kemasan.
2. **Tier 2 — Multi-Line Alias Invoice (`supplier_invoice_name`) (Skor 160–195)**:
   - Mendukung multi-baris alias per produk untuk multi-supplier.
   - Kamus singkatan FMCG Indonesia lengkap (`KCP` → Kecap, `MNS` → Manis, `SAM EP` → Sambal Extra Pedas, `TOM` → Tomat, `PET` → Botol, `PCH` → Pouch, `BSR/TGH/KCL`, dll.).
3. **Tier 3 — Multi-Keywords Overlap & Token Similarity Matching**:
   - **Algoritma Multi-Keywords**: Jika kode supplier tidak ditemukan dan exact alias belum ada, sistem membandingkan kata-kata pada teks invoice dengan nama produk di database (`full_name`, `short_label`, `invoice_name`, `brand_name`, `variant`, `packagings.unit_name`).
   - Kata terbanyak yang memiliki kesamaan (*highest token overlap count & ratio*) akan dianggap match dengan skor proporsional.
   - Pembobotan kata kunci relevan: Kata brand/varian/angka gramatur memiliki bobot lebih tinggi daripada kata umum (*generic category words*).
   - Toleransi *shrinkflation* (10–20% fluktuasi gramatur pabrik).
   - Pencocokan harga beli kemasan (*Price Proximity*) untuk menentukan level kemasan secara presisi.

---

## 4. ATURAN WAJIB: Direct 1-to-1 Cart Ingestion (100% Masuk Keranjang)

> **PRINSIP MUTLAK**: Jumlah baris yang diekstrak AI harus **100% SAMA** dengan jumlah baris yang tampil di keranjang pembelian. **DILARANG MENGGABUNGKAN ATAU MENIMPA ITEM (NO MERGE / NO OVERWRITE).**

1. **Direct Object Construction**:
   - Setiap baris hasil AI **dikonstruksi langsung sebagai 1 objek item keranjang tersendiri** di `purchaseItems`.
2. **Item yang Cocok (`is_matched = true`)**:
   - Dimasukkan langsung dengan status aktif dan level kemasan yang sesuai.
   - Tetap menampilkan tombol **"Ganti Produk"** (`openLinkProductModal`) agar kasir dapat mengoreksi jika ada salah pilih master produk.
3. **Item yang Belum Cocok (`is_matched = false` / Unmatched)**:
   - **WAJIB TETAP DIMASUKKAN KE KERANJANG** sebagai *Draft Item* (`is_unmatched: true`, `product_id: null`).
   - Kuantitas, satuan, harga satuan, dan total harga nota wajib terjaga 100% utuh sehingga total rupiah invoice fisik dan sistem selalu sama.
   - Ditandai badge kuning `⚠️ Hasil Scan (Draft)` dan tombol **"Hubungkan Produk"**.

---

## 5. Modal Hasil Scan Modern & Elegan (`showScanResultModal`)

- Saat scan selesai, sistem wajib menampilkan pop-up modal modern bergaya dark-mode/glassmorphism:
  1. **Statistik Ringkas**: Kartu Total Item, Cocok Otomatis (Hijau), Draft (Kuning).
  2. **Total Nilai Nota**: Tampilan rupiah total kalkulasi.
  3. **List Rincian Interaktif**: Menampilkan nama nota, nama master produk terhubung (atau draft), kuantitas, satuan, harga per unit, dan subtotal baris.
  4. **Tombol Aksi**: *Lihat di Keranjang* yang langsung mengarahkan tampilan ke tabel item.

---

## 6. Pembelajaran Alias Otomatis & Fitur "Ganti / Hubungkan Produk"

1. **Opsi Hubungkan / Ganti Produk di Setiap Kartu Item**:
   - Setiap kartu item di keranjang memiliki tombol **"Hubungkan Produk"** (untuk draft) atau **"Ganti Produk"** (untuk item yang sudah terhubung).
2. **Auto-Learning ke Kolom `supplier_invoice_name`**:
   - Saat produk dipilih di modal `openLinkProductModal`:
     - Data item di keranjang langsung terhubung ke produk master baru tersebut.
     - Nama asli hasil scan AI (`original_invoice_name`) **otomatis disimpan ke kolom `products.supplier_invoice_name`** via endpoint `/api/ai/learn-alias` dan `InvoiceLearningService`.
     - Jika kolom `products.invoice_name` masih kosong, sistem juga otomatis mengisinya.
   - **Hasil**: Pada pemindaian faktur berikutnya, AI akan 100% otomatis mengenali produk tersebut tanpa perlu intervensi manual lagi.

---

## 7. Fitur Ekspor JSON Keranjang (`copyCartAsJson`)

- Tombol mini `<i class="bi bi-clipboard-data"></i> Copy JSON` di toolbar Daftar Barang menyalin seluruh array keranjang ke clipboard dalam format JSON terstruktur lengkap untuk keperluan evaluasi atau audit data.

---

## 8. Ringkasan File Kunci yang Wajib Dijaga

| File | Komponen Kritis |
|------|-----------------|
| [`app/config/Routes.php`](file:///c:/xampp/htdocs/AlfarezMart/app/config/Routes.php) | Route `/api/ai/scan-invoice` dan `/api/ai/learn-alias` |
| [`app/controllers/ApiController.php`](file:///c:/xampp/htdocs/AlfarezMart/app/controllers/ApiController.php) | Method `scanInvoiceAI()` dan `learnAliasAI()` (auto-update `supplier_invoice_name`) |
| [`app/services/invoice/InvoiceScanService.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/InvoiceScanService.php) | Routing multi-model OpenRouter, cURL 45s, auto-reconnect |
| [`app/services/invoice/InvoiceLearningService.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/InvoiceLearningService.php) | `appendProductInvoiceAlias` & auto-learn mapping |
| [`app/services/invoice/ProductMatcher.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/ProductMatcher.php) | Multi-tier matcher + Multi-Keywords Overlap Matching |
| [`public/js/components.js`](file:///c:/xampp/htdocs/AlfarezMart/public/js/components.js) | `AppModal.show()`, `AppModal.close()`, `AppModal.hide()` |
| [`app/views/purchases/create.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/purchases/create.php) | `openLinkProductModal` (Link/Change), `selectProductToLink`, Ganti Produk button, `showScanResultModal`, direct cart ingestion |
| [`app/views/purchases/edit.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/purchases/edit.php) | `openLinkProductModal` (Link/Change), `selectProductToLink`, Ganti Produk button, `showScanResultModal`, direct cart ingestion |

---

*Dokumen aturan baku ini diperbarui pada 21 Agustus 2026 dan wajib dipatuhi oleh seluruh agent dan sesi pengembangan di masa mendatang.*
