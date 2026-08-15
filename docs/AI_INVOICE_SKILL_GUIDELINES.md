# AI_INVOICE_SKILL_GUIDELINES.md

## 1. Tujuan Utama

Sistem invoice scanner AlfarezMart harus mengutamakan:

1. Kecepatan scan.
2. Akurasi pembacaan dan pemetaan produk.
3. Pemakaian AI seminimal mungkin.
4. Tidak bergantung pada satu model AI atau satu provider.
5. Tidak mudah terkena rate limit/token limit.
6. Tetap kompatibel dengan supplier baru dan format invoice baru.
7. Mampu belajar dari hasil invoice yang sudah dikonfirmasi user.
8. Tidak merusak fungsi invoice scanner yang sudah berjalan.

Prinsip utama:

> Gunakan deterministic/local processing terlebih dahulu. Gunakan AI hanya sebagai fallback ketika sistem tidak cukup yakin.

Dokumen lama sudah menggunakan konsep supplier-specific skill, SupplierDetector, SkillManager, ProductMatcher, packaging resolution, dan auto-learning alias. Guideline ini mempertahankan konsep tersebut tetapi memindahkan logika umum ke invoice engine sehingga supplier skill hanya menangani keunikan supplier.

---

## 2. Arsitektur Target

```text
Invoice Image/PDF
      |
      v
[1] Input Validator
      |
      v
[2] Image Preprocessor
      |
      v
[3] OCR / Text Extraction
      |
      v
[4] OCR Normalizer
      |
      v
[5] Supplier Detector
      |
      v
[6] Invoice Layout Detector
      |
      v
[7] Row/Column Parser
      |
      v
[8] Product Matcher
      |
      v
[9] Packaging Resolver
      |
      v
[10] Confidence Engine
      |
      +---- high confidence ----> RESULT
      |
      +---- medium confidence --> local correction / alias learning
      |
      +---- low confidence ------> AI Fallback
                                      |
                                      v
                               structured result
                                      |
                                      v
                              Alias / Learning Store
```

AI bukan parser utama. AI adalah fallback intelligence.

---

## 3. Input Validator

Sebelum OCR:

- Validasi MIME type.
- Validasi ukuran file.
- Validasi resolusi minimum.
- Deteksi image kosong/korup.
- Dukungan JPG, JPEG, PNG, WEBP, dan PDF jika aplikasi memang mendukung PDF.
- Tolak file yang jelas tidak berisi invoice.
- Hindari resize berlebihan yang merusak karakter kecil.

Simpan checksum/hash file untuk cache sehingga invoice yang sama tidak diproses dua kali tanpa alasan.

---

## 4. Image Preprocessing

Preprocessing harus ringan dan deterministik.

Urutan yang disarankan:

1. Auto-orientation.
2. Crop margin kosong yang jelas.
3. Deskew ringan.
4. Contrast normalization jika perlu.
5. Grayscale jika OCR engine membutuhkannya.
6. Upscale hanya ketika karakter terlalu kecil.
7. Jangan selalu mengirim gambar resolusi penuh ke AI.

Jika invoice sangat panjang:

```text
full image
   -> detect header/table/footer
   -> crop relevant regions
   -> process region independently
```

Tujuan preprocessing adalah mengurangi biaya OCR/AI tanpa kehilangan informasi.

---

## 5. OCR Layer

OCR harus menjadi sumber teks awal.

OCR output minimal harus menyimpan:

- text
- bounding box
- page number
- line number
- confidence jika tersedia
- word/segment coordinates jika tersedia

Contoh struktur internal:

```php
[
    [
        'text' => 'INDOMIE GORENG 40G',
        'confidence' => 0.97,
        'x' => 100,
        'y' => 420,
        'width' => 320,
        'height' => 28,
        'page' => 1,
        'line' => 8,
    ],
]
```

Simpan hasil OCR dalam cache yang dapat digunakan kembali oleh proses berikutnya.

---

## 6. OCR Normalizer

Normalisasi harus dilakukan tanpa mengubah raw OCR.

Simpan dua nilai:

- `raw_text`
- `normalized_text`

Normalisasi harus menangani antara lain:

- `0/O`
- `1/I/L`
- `5/S`
- `8/B`
- koma/titik sebagai pemisah angka
- spasi ganda
- karakter aneh hasil OCR
- pemisahan angka dan satuan
- singkatan umum supplier
- variasi huruf besar/kecil

Contoh:

```text
INDM GORENG 40 GR
INDOMIE GORENG 40G
INDM GORENG40G
```

dapat dinormalisasi menjadi token yang lebih mudah dicocokkan, tetapi raw text harus tetap tersedia untuk debugging.

---

## 7. SupplierDetector

Supplier detection wajib terjadi sebelum parsing khusus supplier.

Sumber sinyal, dari yang paling kuat:

1. Identitas legal supplier.
2. NPWP.
3. Nomor rekening.
4. Nomor invoice/pola invoice.
5. Header keyword.
6. Alamat/telepon.
7. Brand dominan.
8. Layout fingerprint.

SupplierDetector harus mengembalikan:

```php
[
    'supplier_key' => 'indomarco',
    'confidence' => 0.98,
    'matched_rules' => [...],
]
```

Jangan langsung memilih supplier jika skor berada di bawah threshold aman. Dalam kondisi ambigu, gunakan generic parser lalu AI fallback bila diperlukan.

---

## 8. Supplier Skill

Supplier skill hanya bertanggung jawab atas aturan unik supplier tersebut.

Contoh tanggung jawab:

- keyword supplier
- header mapping
- abbreviation
- known brands
- column hints
- unit code khas supplier
- promo format khas
- layout rules khas supplier
- parsing exception tertentu

Supplier skill TIDAK boleh menggandakan logika umum yang seharusnya berada di engine.

Target struktur:

```text
app/services/invoice/
├── InvoiceEngine.php
├── InputValidator.php
├── ImagePreprocessor.php
├── OCR/
├── Parser/
├── Matching/
├── Packaging/
├── Confidence/
├── Learning/
├── AI/
├── skills/
│   ├── SupplierAInvoiceSkill.php
│   ├── SupplierBInvoiceSkill.php
│   └── ...
├── SkillManager.php
└── SupplierDetector.php
```

---

## 9. Layout Detector

Invoice layout harus diperlakukan sebagai data.

Sistem harus mencoba menemukan:

- header tabel
- kode barang
- barcode bila ada
- nama barang
- qty
- unit
- harga satuan
- diskon
- subtotal
- total baris
- footer
- total invoice

Layout detector harus menggunakan kombinasi:

- OCR coordinates
- header keyword
- relative x-position
- relative y-position
- separator/line detection bila tersedia
- supplier-specific layout rules

Jangan mengasumsikan semua supplier memiliki urutan kolom yang sama.

---

## 10. Row Parser

Parser harus mengubah hasil OCR menjadi row terstruktur:

```php
[
    'line_index' => 1,
    'product_code' => null,
    'product_text' => 'INDOMIE GORENG 40G',
    'qty' => 10,
    'unit' => 'PCS',
    'unit_price' => 2500,
    'discount' => 0,
    'line_total' => 25000,
    'confidence' => 0.96,
]
```

Baris harus dapat digabung ketika OCR memecah nama produk menjadi beberapa line.

Parser harus dapat mendeteksi dan memisahkan:

- product code
- description
- quantity
- package/unit
- price
- discount
- total

Jangan menggunakan AI jika parser deterministik sudah cukup yakin.

---

## 11. ProductMatcher

Urutan matching:

1. exact product code
2. exact normalized SKU/barcode
3. learned alias
4. exact normalized product name
5. supplier-specific alias
6. brand + variant + size
7. fuzzy similarity
8. historical invoice pattern
9. AI fallback

ProductMatcher harus mengembalikan alasan matching.

Contoh:

```php
[
    'product_id' => 12345,
    'match_type' => 'learned_alias',
    'score' => 0.97,
]
```

Jangan menyimpan fuzzy match sebagai alias permanen jika confidence belum melewati threshold tinggi dan belum dikonfirmasi.

---

## 12. Product Identity Extraction

Dari nama produk, sistem harus berusaha memisahkan:

- brand
- product family
- variant/flavor
- size
- weight
- volume
- package type

Contoh:

```text
INDOMIE SOTO 40G 1 RENTENG
```

menjadi:

```text
brand       = INDOMIE
variant     = SOTO
weight      = 40G
package     = RENTENG/PACK
```

Jangan mengubah produk database hanya berdasarkan OCR.

---

## 13. Packaging Resolver

Packaging resolver harus menjadi modul terpisah.

Gunakan kombinasi:

- OCR unit
- supplier unit code
- database packaging
- price distance
- quantity
- product master
- historical invoice
- known conversion

Contoh conversion:

```text
1 BOX = 10 PACK
1 PACK = 10 PCS
```

Konversi harus disimpan sebagai data, bukan hard-coded di banyak tempat.

Kode internal seperti berikut harus bisa dikonfigurasi:

```text
BSR -> BOX/KARTON
TGH -> PACK/RENTENG
KCL -> PCS
```

Jangan menganggap seluruh supplier memakai arti kode yang sama.

---

## 14. Price Distance

Price Distance boleh digunakan sebagai signal, bukan sebagai satu-satunya keputusan.

Contoh:

```text
PCS  = 2,500
PACK = 25,000
BOX  = 250,000

OCR price = 25,000
```

Harga paling dekat adalah PACK.

Tetapi keputusan final harus mempertimbangkan unit OCR, conversion, dan product master.

---

## 15. Confidence Engine

Setiap item harus memiliki confidence score.

Confidence dapat dihitung dari kombinasi:

- OCR confidence
- supplier confidence
- parser confidence
- product match confidence
- unit confidence
- price consistency
- line total consistency
- invoice total consistency
- historical consistency

Contoh klasifikasi:

```text
>= 0.95  AUTO ACCEPT
0.80-0.949  REVIEW / LOCAL CORRECTION
< 0.80  AI FALLBACK
```

Threshold harus configurable.

Jangan memakai satu confidence score yang tidak bisa dijelaskan. Simpan breakdown score.

---

## 16. Mathematical Validation

Setiap baris harus divalidasi:

```text
qty × unit_price
```

lalu diperiksa terhadap:

- discount
- subtotal
- line_total

Setelah semua baris:

```text
sum(line_total)
```

harus dibandingkan dengan total invoice.

Jika selisih melewati tolerance, confidence harus turun.

Tolerance harus configurable dan mempertimbangkan pembulatan.

---

## 17. AI Fallback

AI hanya dipanggil jika:

- supplier tidak dikenal
- layout tidak dikenal
- row parser gagal
- produk tidak teridentifikasi
- packaging ambigu
- total tidak konsisten
- confidence di bawah threshold

Request AI harus sekecil mungkin.

Prioritas:

1. kirim cropped region bermasalah, bukan seluruh invoice
2. kirim OCR text + coordinates jika lebih efisien
3. minta structured JSON
4. jangan minta penjelasan panjang
5. gunakan timeout
6. retry terbatas
7. fallback ke provider/model lain jika tersedia

AI output wajib tervalidasi oleh schema.

---

## 18. AI Response Schema

Gunakan schema terstruktur seperti:

```json
{
  "supplier": {
    "key": "indomarco",
    "confidence": 0.99
  },
  "items": [
    {
      "line": 1,
      "product_code": "123456",
      "name": "INDOMIE GORENG 40G",
      "qty": 10,
      "unit": "PCS",
      "unit_price": 2500,
      "discount": 0,
      "line_total": 25000,
      "confidence": 0.98
    }
  ],
  "invoice_total": 25000,
  "confidence": 0.98
}
```

AI tidak boleh mengeluarkan format bebas untuk diproses aplikasi.

---

## 19. Provider/Model Abstraction

Jangan hard-code aplikasi ke satu provider.

Gunakan interface:

```php
interface InvoiceAIProviderInterface
{
    public function analyzeInvoice(array $payload): array;
}
```

Kemudian:

```text
GemmaProvider
NemotronProvider
OpenAIProvider
GeminiProvider
ClaudeProvider
...
```

Provider selection dapat didasarkan pada:

- availability
- cost
- rate limit
- latency
- task type
- confidence requirement

---

## 20. Rate Limit Protection

Sistem wajib memiliki:

- request queue
- exponential backoff
- retry limit
- timeout
- provider failover
- circuit breaker sederhana
- cache
- request deduplication
- token/image size control

Jangan retry tanpa batas.

Jika provider sedang rate-limited, jangan membuat request paralel tambahan secara agresif.

---

## 21. Caching

Cache minimal:

1. image hash -> OCR result
2. OCR hash -> supplier detection
3. invoice/layout fingerprint -> parser strategy
4. product alias -> product_id
5. AI request fingerprint -> AI structured result

Gunakan TTL yang sesuai dan invalidation bila master data berubah.

---

## 22. Alias Learning

Learning hanya terjadi dari sumber yang dipercaya:

- user confirmed item
- saved purchase
- manually corrected product
- high confidence repeated match

Contoh:

```text
INDM GORENG 40GR
INDOM GORENG 40G
INDM GRG 40
```

dapat dipetakan ke satu product_id setelah cukup banyak bukti.

Simpan:

- alias
- product_id
- supplier_key
- source
- confidence
- usage_count
- last_seen
- confirmed_count

Jangan mencampur alias supplier A dengan supplier B jika ambigu.

---

## 23. Incremental Learning

Setelah user menyimpan invoice:

```text
scan
 ↓
user correction
 ↓
confirmed result
 ↓
learning event
 ↓
alias/product/unit update
 ↓
next scan becomes faster
```

Learning harus asynchronous bila arsitektur memungkinkan agar penyimpanan invoice tidak menjadi lambat.

---

## 24. Performance Rules

Target utama:

- Hindari request AI jika tidak diperlukan.
- Hindari OCR berulang pada gambar yang sama.
- Hindari query DB berulang untuk produk yang sama.
- Batch product lookup jika memungkinkan.
- Gunakan in-memory map untuk data referensi selama satu scan.
- Gunakan prepared statement.
- Batasi fuzzy matching hanya pada kandidat yang masuk akal.
- Jangan fuzzy match terhadap seluruh database jika supplier/brand sudah diketahui.

---

## 25. Database Matching Optimization

Jika product master besar:

```text
supplier -> brand -> normalized token -> candidate products
```

baru lakukan fuzzy matching.

Jangan:

```text
OCR row -> query seluruh product table -> fuzzy match semuanya
```

Gunakan indexing sesuai database engine yang tersedia.

---

## 26. Generic Parser

Harus tersedia generic parser untuk supplier yang belum mempunyai skill.

Generic parser menggunakan:

- OCR coordinates
- header detection
- numeric column detection
- product description heuristics
- price/total arithmetic
- known units

Jika generic parser confidence tinggi, invoice dapat diproses tanpa membuat supplier skill baru.

---

## 27. Skill Generation

Supplier skill baru dibuat hanya ketika:

- supplier formatnya stabil
- generic parser sering gagal
- ada nilai bisnis untuk optimasi khusus supplier

Skill generation harus mempelajari:

- column layout
- keyword
- abbreviations
- known brands
- unit codes
- promo conventions
- invoice fingerprint

Jangan membuat skill yang meng-copy seluruh InvoiceEngine.

---

## 28. Observability

Setiap scan harus menyimpan metadata minimal:

```text
scan_id
supplier
file_hash
ocr_duration
parse_duration
matching_duration
ai_duration
ai_called
ai_provider
ai_request_count
item_count
matched_count
unmatched_count
review_count
confidence_avg
invoice_total
validation_status
```

Ini diperlukan untuk mengetahui bottleneck yang sebenarnya.

---

## 29. Error Handling

Error harus dibedakan:

- invalid file
- OCR failure
- supplier unknown
- parser failure
- matcher failure
- AI timeout
- AI rate limit
- schema validation failure
- database error
- arithmetic mismatch

Jangan menampilkan raw exception provider kepada user.

Simpan detail teknis di log.

---

## 30. No Regression Rule

Perbaikan scanner tidak boleh:

- merusak supplier skill lama
- menghilangkan alias lama
- mengubah product_id secara diam-diam
- mengubah harga pembelian yang sudah tersimpan
- mengubah hasil invoice lama
- menghapus data learning

Migrasi database wajib backward-compatible.

---

## 31. Testing Minimum

Wajib memiliki test untuk:

1. supplier detection
2. OCR normalization
3. row parsing
4. product matching
5. packaging resolution
6. price distance
7. arithmetic validation
8. confidence scoring
9. AI schema validation
10. rate-limit retry
11. provider failover
12. alias learning
13. duplicate invoice detection
14. long invoice
15. invoice multi-page

Gunakan fixture invoice nyata yang telah disetujui.

---

## 32. Acceptance Criteria

Sistem dianggap berhasil jika:

- invoice format lama tetap berjalan
- supplier lama tetap terdeteksi
- sebagian besar invoice dikenal selesai tanpa AI
- invoice baru dapat diproses generic parser sebelum membuat skill baru
- AI fallback hanya dipakai ketika diperlukan
- hasil AI selalu tervalidasi
- scan yang sama dapat menggunakan cache
- alias learning meningkatkan hasil scan berikutnya
- tidak ada retry tak terbatas
- provider dapat diganti
- semua hasil penting dapat diaudit

---

## 33. Prinsip Akhir

```text
FAST PATH FIRST
LOCAL/DATABASE FIRST
AI SECOND
LEARNING ALWAYS
VALIDATE EVERYTHING
CACHE EVERYTHING THAT IS SAFE TO CACHE
NEVER BREAK EXISTING SUPPLIER SKILLS
```
