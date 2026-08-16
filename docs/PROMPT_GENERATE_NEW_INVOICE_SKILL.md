# 📘 PANDUAN UTAMA, REGISTRY ANTI-REGRESI, & TEMPLATE SKILL INVOICE ALFAREZ本来 (PROMPT GENERATOR)

> **⚠️ PERINGATAN WAJIB UNTUK SELURUH AI AGENT / DEVELOPER:**  
> Sebelum melakukan perbaikan, modifikasi, atau penambahan kode terkait **AI Invoice Scanner**, Anda **WAJIB MEMBACA SELURUH DOKUMEN INI**.  
> Dokumen ini berisi **Aturan Emas Arsitektur (Golden Architecture Rules)**, **Daftar Kesalahan Masa Lalu (Past Mistakes Registry)** yang **DILARANG DIULANGI**, serta **Template Prompt Siap Pakai** untuk generate/update skill faktur supplier.

---

## 🏛️ 1. ATURAN EMAS ARSITEKTUR SCANNER (GOLDEN ARCHITECTURE RULES)

Berikut adalah batasan teknis mutlak yang **TIDAK BOLEH DILANGGAR**:

### ⚡ Kecepatan & Timeout
1. **Timeout cURL per Model Max 15 Detik**:  
   Jangan pernah menyetel `CURLOPT_TIMEOUT` lebih dari 15 detik per percobaan model. Model vision seperti Gemini normalnya merespons dalam **3–6 detik**. Jika dalam 15 detik tidak ada balasan, model sedang macet/antre di provider, sehingga sistem harus langsung beralih ke model fallback.
2. **Total Percobaan Model Maksimal 2 Model (Total Waktu < 30 Detik)**:  
   Dengan timeout 15s x max 2 percobaan = max 30 detik total eksekusi. Frontend browser (`utils.js`) memiliki timeout `AbortController` 120s, sehingga batas 30s menjamin tidak akan pernah terjadi `AbortError: signal is aborted without reason`.
3. **Prioritas Model Vision Khusus (Bukan Text Meta-Router)**:  
   - Prioritas 1: `google/gemini-2.0-flash-exp:free` (Sangat cepat, 3-6s, OCR tabel akurat).
   - Prioritas 2: `google/gemini-2.5-flash:free` (Kualitas tinggi, fallback stabil 5-10s).
   - **DILARANG** mencoba lebih dari 2 model atau memasukkan `openrouter/free` di urutan pertama karena meta-router ini sering merutekan ke text-only model yang akan hang/stuck saat dikirimi gambar base64 besar!


### 🖼️ Kompresi Gambar & Payload
1. **Frontend Compression**:  
   Gambar nota dari kamera/file wajib dikompresi di browser (`compressImageForAI`) ke max resolusi **1600px–1800px** dan kualitas **0.80–0.85** (ukuran file ~50KB–150KB). Jangan kirim gambar raw > 2MB ke server karena akan membuang bandwidth dan memperlambat upload.
2. **Prompt Context Ringkas**:  
   Jangan menyuntikkan ratusan produk database ke dalam prompt AI. Cukup berikan **20–30 produk supplier teratas** atau alias yang sudah dipelajari. Terlalu banyak token context membuat pemrosesan AI lambat dan rawan terpotong (max_tokens).

### 🗄️ Database & Backend Anti-Crash
1. **PDO Named Parameter Unik**:  
   Dalam query PDO MySQL/SQLite, jangan pernah mengulang nama named parameter (misal `:barcode` diulang 6x dalam `WHERE`). PDO akan melempar error fatal `SQLSTATE[HY093]: Invalid parameter number`. Selalu gunakan positional parameter `?` dengan array nilai yang sesuai, atau beri nama unik (`:b1`, `:b2`, dll).
2. **ScanCache (Image Hash Caching)**:  
   Invoice yang gambarnya sama persis (hash MD5 sama) wajib langsung disajikan dari `ScanCache` (0.01 detik / 0 AI cost).

### 🌐 Network & Offline Resilience
1. **Pengecekan Sinyal yang Akurat (Anti False-Positive)**:  
   Probe RTT tidak boleh mendownload asset besar (>5KB) dengan threshold < 1000ms karena koneksi mobile normal akan disalahartikan sebagai "Sinyal Lemah". Gunakan asset ultra-ringan (`splash_icon.svg` ~1.6KB) dengan threshold RTT realistis (>2500ms).
2. **Silent Sync saat Offline**:  
   Fungsi background refresh (seperti `refreshMasterDataFromServer` di Finance) wajib memiliki guard `if (!navigator.onLine) return;` dan flag `silent: true`, `noOfflineQueue: true` agar tidak mencemari Error Logger dengan `TypeError: Failed to fetch`.

---

## 🚫 2. DAFTAR KESALAHAN MASA LALU YANG DILARANG DIULANGI (PAST MISTAKES REGISTRY)

| No | Gejala / Pesan Error | Akar Masalah (Root Cause) | Solusi Permanen yang Harus Dijaga |
|:---|:---|:---|:---|
| **M-01** | `AbortError: signal is aborted without reason` (Elapsed ~120s) | Timeout cURL per model diset 50s dan `openrouter/free` berada di urutan pertama (hang/stuck pada base64 payload). 3 model x 50s = 150s, melewati batas browser 120s. | Set timeout cURL max 20s per model. Prioritaskan model Gemini Flash langsung. Batasi percobaan max 2-3 model agar total < 40s. |
| **M-02** | `No endpoints found for meta-llama/llama-3.2-11b-vision-instruct:free` | Hardcoded model ID yang sudah dihapus/dinonaktifkan oleh provider OpenRouter. | Gunakan model Google Gemini yang stabil. Tangani error "No endpoints found" secara silent failover ke model berikutnya tanpa melempar error langsung ke user. |
| **M-03** | `SyntaxError: Identifier 'ThermalPrinter' has already been declared` | File `printer_v3.js` di-include dua kali (di `layouts/app.php` dan di `views/sales/detail.php`). | `printer_v3.js` sudah di-load secara global di `layouts/app.php`. Jangan pernah menambahkan `<script src="printer_v3.js">` lagi di view individual. Gunakan guard `window.thermalPrinter \|\| (typeof ThermalPrinter !== 'undefined' ? new ThermalPrinter() : null)`. |
| **M-04** | `HTTP 500 - SQLSTATE[HY093]: Invalid parameter number` saat scan barcode POS | Named parameter `:barcode` ditulis 6 kali dalam klausa `WHERE (pp.barcode = :barcode OR p.code = :barcode ...)` pada `ProductModel::findByBarcode`. | Gunakan placeholder positional `?` dan kirimkan array `[$barcode, $barcode, $barcode, $barcode, $barcode, $barcode]` ke `$stmt->execute()`. |
| **M-05** | Spam `TypeError: Failed to fetch` di halaman Keuangan/Finance saat offline | `refreshMasterDataFromServer()` dipanggil tanpa memeriksa `navigator.onLine`, dan API call tidak diberi flag `silent: true`. | Selalu pasang `if (!navigator.onLine) return;` di awal pemanggilan API master data background, serta set `{ silent: true, noOfflineQueue: true }`. |
| **M-06** | Alert kuning "Sinyal Lemah" sering muncul padahal koneksi WiFi/4G kencang | Pengecekan sinyal mendownload `Icon.png` (85KB) setiap 30 detik dengan threshold RTT 800ms. Download 85KB di mobile seringkali >800ms sehingga salah memicu alert. | Ganti probe ke asset 1.6KB (`splash_icon.svg`) dan naikkan threshold RTT ke 2500ms dengan timeout 4000ms. |

---

## 📋 3. TEMPLATE PROMPT GENERATE SKILL SUPPLIER BARU

Gunakan template di bawah ini saat menambahkan dukungan format nota untuk supplier baru:

### 🚀 Template 1: Generate Skill Supplier Baru
```markdown
Tolong pelajari gambar contoh faktur supplier yang saya upload ini, dan buatkan file skill invoice baru untuk supplier ini sesuai dengan panduan arsitektur AlfarezMart.

Informasi Supplier:
- Nama Supplier: [Contoh: PT Indomarco Adi Prima / CV Sinar Mandiri / dll]
- Brand Produk Utama: [Contoh: Indofood, Indomie, Bimoli / Unilever / dll]
- Key Skill: [Contoh: indomarco / sinarmandiri (huruf kecil tanpa spasi)]

Tolong lakukan langkah-langkah berikut:
1. Analisis struktur tabel nota (posisi kolom Kode, Nama Barang, Qty, Satuan/Kemasan, Harga Satuan, Diskon, dan Total).
2. Buat file skill baru di `app/services/invoice/skills/[NamaSupplier]InvoiceSkill.php` yang mengimplementasikan `InvoiceSkillInterface`.
   - Cantumkan daftar singkatan umum (ABBREVIATIONS) dan daftar merk (KNOWN_BRANDS).
   - Buat parser cerdas untuk mendeteksi varian rasa, berat/gramatur, dan kemasan bertingkat (Pcs, Renceng, Dus).
   - Terapkan logika Price Distance Matching untuk menentukan level kemasan secara presisi.
3. Daftarkan class skill tersebut di `app/services/invoice/skills/SkillManager.php`.
4. Daftarkan kata kunci pengenal (nama PT, header faktur, nomor rekening) di `app/services/invoice/SupplierDetector.php`.
5. Uji sintaks PHP dan pastikan bebas error.
6. Commit dan push ke repository GitHub.
```

---

### 🔧 Template 2: Koreksi / Update Skill Supplier yang Sudah Ada
```markdown
Tolong evaluasi hasil scan invoice untuk supplier [Nama Supplier, misal: MDR / Wings Group] berdasarkan gambar nota yang saya upload ini.

Catatan Masalah yang Ditemukan:
- Total item di nota ada: [Contoh: 15 item]
- Masalah: [Contoh: Baris ke-5 dan ke-6 terlewat / Satuan Renceng terbaca Pcs / Diskon persen belum memotong harga beli]

Tolong lakukan:
1. Periksa `app/services/invoice/skills/[NamaSupplier]InvoiceSkill.php` dan `ProductMatcher.php`.
2. Sesuaikan pola regex atau logika parsing agar 100% item pada nota terbaca akurat.
3. Pastikan auto-learning tetap mencatat alias baru ke tabel `supplier_products`.
4. Uji sintaks PHP, commit, dan push perubahannya.
```

---

### 📦 Template 3: Multi-Supplier Batching
```markdown
Saya mengupload beberapa invoice dari supplier yang berbeda sekaligus.
Tolong pelajari invoice-invoice ini:
1. Deteksi identitas masing-masing supplier dari header, footer, atau stempel nota.
2. Buatkan file skill terpisah di `app/services/invoice/skills/` untuk masing-masing supplier.
3. Daftarkan seluruh skill di `SkillManager.php` dan `SupplierDetector.php`.
4. Pastikan pipeline scan tetap cepat (<10 detik) dan mematuhi batas timeout 20s.
5. Uji sintaks PHP, lalu commit dan push ke GitHub.
```

---

## 🎯 4. CHECKLIST PRA-COMMIT SETIAP KALI MEMPERBAIKI SCANNER

Sebelum melakukan `git commit` dan `git push` untuk perbaikan scanner invoice:
- [ ] Apakah timeout cURL per model **<= 20 detik**?
- [ ] Apakah model prioritas adalah model vision cepat (`gemini-2.0-flash-exp:free` / `gemini-2.5-flash:free`)?
- [ ] Apakah `openrouter/free` TIDAK ditaruh di urutan paling atas?
- [ ] Apakah query database bebas dari named parameter duplikat?
- [ ] Apakah tidak ada script duplikat di view?
- [ ] Apakah fitur auto-learning (`InvoiceLearningService` & `LearnedAliasLookup`) tetap berjalan?
- [ ] Apakah sintaks PHP valid (`php -l`)?
