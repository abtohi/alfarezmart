# MASTER PROMPT — OPTIMASI TOTAL INVOICE SCANNER ALFAREZMART

Kamu adalah AI Software Engineer/AI Agent yang bertugas mengaudit, merancang ulang, mengoptimalkan, mengimplementasikan, dan menguji sistem Invoice Scanner pada aplikasi AlfarezMart.

TUJUAN BESAR:

Membuat invoice scanner yang:

- sangat cepat
- sangat akurat
- mampu menangani banyak supplier
- tidak bergantung pada AI vision untuk setiap invoice
- meminimalkan penggunaan token/request AI
- tahan rate limit
- mendukung fallback provider/model
- mampu belajar dari koreksi user
- tetap kompatibel dengan sistem lama
- tidak menimbulkan regression

PRINSIP UTAMA:

> Deterministic/local processing FIRST. AI hanya FALLBACK.

Jangan mengubah kode secara membabi buta. Audit kode yang benar-benar ada di repository terlebih dahulu.

---

# PHASE 0 — BACA KONTEKS

Sebelum melakukan perubahan:

1. Baca seluruh file dan struktur module invoice scanner yang relevan.
2. Cari:
   - InvoiceSkillInterface
   - SupplierDetector
   - SkillManager
   - ProductMatcher
   - packaging/unit resolver
   - OCR integration
   - AI provider integration
   - invoice upload/controller/service
   - product master query
   - alias/learning mechanism
   - invoice save/purchase save
   - cache
   - queue/job bila ada
   - logging
   - database schema terkait invoice/product/alias
3. Baca semua supplier skill yang sudah ada.
4. Jangan menghapus supplier skill lama.
5. Identifikasi duplicate logic antar supplier skill.
6. Identifikasi titik yang paling banyak memakai AI, OCR, query database, atau fuzzy matching.

Jika repository memiliki dokumentasi invoice scanner, baca dan gunakan sebagai sumber aturan existing.

Gunakan `AI_INVOICE_SKILL_GUIDELINES.md` sebagai target architecture.

---

# PHASE 1 — AUDIT CURRENT SYSTEM

Buat audit internal yang mencakup:

A. Alur scan saat ini.
B. Semua titik request AI.
C. Semua titik OCR.
D. Semua query database per invoice dan per item.
E. Semua fuzzy matching.
F. Semua supplier-specific rule.
G. Semua cache yang sudah ada.
H. Semua retry.
I. Semua failure mode.
J. Potensi bottleneck.
K. Potensi rate-limit issue.
L. Potensi token/image-size issue.
M. Potensi regression.

Jangan hanya melihat nama file. Telusuri call graph dan hubungan antar class/service/function.

---

# PHASE 2 — BUAT BASELINE

Sebelum optimasi besar, tentukan baseline dari kode/test yang tersedia.

Ukur atau estimasikan secara terukur:

- latency scan
- OCR latency
- AI latency
- jumlah request AI
- token/image payload jika tersedia
- jumlah DB query
- rata-rata jumlah item
- match rate
- unmatched rate
- confidence
- failure rate

Jika automated benchmark belum tersedia, buat benchmark minimal menggunakan fixture invoice yang tersedia.

Jangan mengklaim performa lebih cepat tanpa pembanding.

---

# PHASE 3 — BANGUN FAST PATH

Refactor alur menjadi:

```text
Input
 -> validation
 -> image preprocessing
 -> OCR/text extraction
 -> OCR normalization
 -> supplier detection
 -> layout detection
 -> row parser
 -> product matcher
 -> packaging resolver
 -> confidence engine
 -> result
```

Fast path HARUS dapat menyelesaikan invoice yang sudah dikenal tanpa AI jika confidence mencukupi.

AI tidak boleh dipanggil secara default.

---

# PHASE 4 — REFACTOR SUPPLIER SKILLS

Pertahankan:

- InvoiceSkillInterface
- SkillManager
- SupplierDetector
- supplier skill lama

Tetapi pindahkan logic umum yang duplicate ke shared service/module.

Supplier skill hanya menyimpan aturan unik supplier.

Jangan membuat setiap supplier mempunyai implementasi OCR/parser/matcher sendiri jika logic tersebut sebenarnya generic.

---

# PHASE 5 — OCR LAYER

Pastikan OCR layer:

- menghasilkan text
- coordinates/bounding box jika engine mendukung
- confidence jika tersedia
- page/line metadata
- menyimpan raw OCR
- menyediakan normalized OCR

Tambahkan cache berdasarkan file/image hash bila aman.

Jangan melakukan OCR ulang untuk data yang identik.

---

# PHASE 6 — IMAGE OPTIMIZATION

Tambahkan preprocessing yang aman:

- orientation
- crop area relevan
- deskew ringan
- contrast adjustment bila diperlukan
- resize terukur

Untuk invoice panjang, proses region/crop bila memungkinkan.

Jangan mengorbankan akurasi karakter kecil hanya demi kompresi.

---

# PHASE 7 — GENERIC PARSER

Buat generic parser untuk supplier baru yang belum memiliki skill.

Gunakan:

- OCR coordinates
- header keywords
- numeric column detection
- line grouping
- arithmetic validation
- known units

Target:

Invoice baru tidak otomatis gagal hanya karena belum ada supplier skill.

---

# PHASE 8 — PRODUCT MATCHER OPTIMIZATION

Urutan matcher:

1. code exact
2. barcode/SKU exact
3. learned alias
4. exact normalized name
5. supplier alias
6. brand + variant + size
7. constrained fuzzy match
8. historical match
9. AI fallback

Jangan melakukan fuzzy matching ke seluruh product table.

Filter candidate set berdasarkan supplier/brand/token sebelum fuzzy matching.

Batch DB query jika memungkinkan.

Cache product lookup selama satu scan.

---

# PHASE 9 — PACKAGING RESOLVER

Pisahkan packaging resolution menjadi service tersendiri.

Dukung:

- PCS
- PACK/RENTENG
- BOX/KARTON
- kode supplier seperti BSR/TGH/KCL bila relevan
- conversion data
- database packaging
- price distance
- quantity
- historical data

Gunakan multi-signal scoring, bukan satu heuristik saja.

Jangan hard-code arti kode jika kode tersebut supplier-specific.

---

# PHASE 10 — CONFIDENCE ENGINE

Buat confidence engine yang menghasilkan:

- overall confidence
- field confidence
- reasons

Gunakan signal:

- OCR confidence
- supplier confidence
- parser confidence
- matcher confidence
- unit confidence
- price consistency
- line total consistency
- invoice total consistency
- historical consistency

Buat threshold configurable, misalnya:

```text
>= 0.95 -> auto accept
0.80-0.949 -> review/local correction
< 0.80 -> AI fallback
```

Jangan hard-code threshold jika configuration system memungkinkan.

---

# PHASE 11 — MATHEMATICAL VALIDATION

Validasi setiap line:

qty x unit_price
- discount
= line total

Kemudian:

sum(line total)
= invoice total ± tolerance

Jika tidak konsisten:

- turunkan confidence
- tandai field yang bermasalah
- gunakan AI fallback hanya jika diperlukan

---

# PHASE 12 — AI FALLBACK ARCHITECTURE

AI hanya untuk kasus confidence rendah atau kasus baru.

Jangan kirim seluruh gambar bila hanya satu bagian yang bermasalah.

Prioritas payload:

1. cropped problematic region
2. OCR text + coordinates
3. structured context dari parser
4. full image hanya jika benar-benar diperlukan

AI harus diminta mengeluarkan JSON terstruktur.

Validasi AI output menggunakan schema sebelum memasukkannya ke database.

Jangan pernah memasukkan output AI mentah ke database.

---

# PHASE 13 — PROVIDER ABSTRACTION

Jika AI provider saat ini di-hard-code, refactor menjadi interface.

Contoh:

```php
interface InvoiceAIProviderInterface
{
    public function analyzeInvoice(array $payload): array;
}
```

Buat provider adapter sesuai provider yang benar-benar sudah digunakan repository.

Jangan menambahkan provider fiktif jika tidak tersedia.

Tambahkan provider selection/failover hanya jika benar-benar dapat diimplementasikan dengan credential/config yang tersedia.

---

# PHASE 14 — RATE LIMIT & RESILIENCE

Implementasikan sesuai kebutuhan aplikasi:

- timeout
- bounded retry
- exponential backoff
- rate-limit detection
- request deduplication
- cache
- provider failover jika tersedia
- circuit breaker sederhana jika bermanfaat

Dilarang melakukan infinite retry.

Dilarang membuat retry storm.

---

# PHASE 15 — CACHE

Prioritaskan cache untuk:

- file hash -> OCR
- OCR fingerprint -> supplier detection/layout
- product alias -> product id
- invoice/request fingerprint -> AI result

Pastikan cache tidak menghasilkan data stale ketika master product atau supplier rule berubah.

Jika membutuhkan invalidation, implementasikan versioning/fingerprint.

---

# PHASE 16 — AUTO LEARNING

Pertahankan konsep auto-learning existing.

Learning event berasal dari:

- user-confirmed result
- saved purchase
- manual correction
- repeated high-confidence match

Simpan minimal:

- alias
- product_id
- supplier_key
- confidence
- usage count
- confirmed count
- created_at
- last_seen_at

Jangan belajar permanen dari AI low-confidence atau fuzzy match yang belum dikonfirmasi.

---

# PHASE 17 — DUPLICATE DETECTION

Tambahkan duplicate invoice protection jika belum ada.

Gunakan kombinasi yang realistis:

- file hash
- supplier
- invoice number
- invoice date
- total
- invoice fingerprint

Jangan memblokir invoice valid hanya karena dua field kebetulan sama.

---

# PHASE 18 — OBSERVABILITY

Buat/tingkatkan scan metrics:

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

Tujuan metrics adalah mengetahui bottleneck nyata dan mengontrol penggunaan AI.

---

# PHASE 19 — TESTING

Sebelum dan sesudah refactor, jalankan:

1. PHP syntax check.
2. Unit test bila tersedia.
3. Integration test invoice.
4. Supplier detection test.
5. OCR normalization test.
6. Parser test.
7. Product matcher test.
8. Packaging resolver test.
9. Arithmetic validation test.
10. Confidence test.
11. AI schema validation test.
12. Rate-limit retry test.
13. Cache test.
14. Learning test.
15. Duplicate invoice test.
16. Existing supplier regression tests.

Gunakan fixture invoice nyata yang ada di repository.

---

# PHASE 20 — PERFORMANCE TEST

Buat minimal dua benchmark:

### Benchmark A — Known invoice

Target:

```text
AI requests = 0 jika confidence tinggi
```

### Benchmark B — Unknown/ambiguous invoice

Target:

```text
AI hanya dipanggil pada bagian yang bermasalah
```

Catat sebelum/sesudah:

- total runtime
- OCR runtime
- AI runtime
- request count
- DB query count jika dapat diukur
- match rate

Jangan membuat angka benchmark palsu.

---

# PHASE 21 — DATABASE SAFETY

Sebelum migrasi:

1. inspect current schema
2. gunakan migration yang backward-compatible
3. jangan menghapus data existing
4. jangan mengubah product_id historical record
5. backup/rollback plan bila mekanisme repository mendukung

Semua schema change harus terdokumentasi.

---

# PHASE 22 — CODE QUALITY

Ikuti standar project existing.

Utamakan:

- single responsibility
- dependency injection jika project mendukung
- interface untuk provider yang dapat diganti
- reusable services
- clear naming
- typed data bila PHP version/project mendukung
- error handling jelas
- secure input handling
- prepared statements
- logging tanpa membocorkan credential/API key

Jangan melakukan rewrite besar hanya demi style.

Refactor harus bernilai nyata.

---

# PHASE 23 — SECURITY

Periksa minimal:

- upload validation
- path traversal
- malicious file upload
- unsafe shell call
- API key exposure
- log secret leakage
- SQL injection
- raw user input
- untrusted AI output

AI output dianggap untrusted input.

---

# PHASE 24 — IMPLEMENTATION RULES

Saat mengerjakan:

1. Jangan menghapus fitur lama tanpa alasan.
2. Jangan mengubah business logic purchase secara diam-diam.
3. Jangan mengubah hasil historical transaction.
4. Jangan mengganti provider AI tanpa mengecek dependency/config existing.
5. Jangan menambahkan dependency besar tanpa alasan.
6. Jangan membuat query N+1 jika bisa dibatch.
7. Jangan membuat infinite retry.
8. Jangan melakukan AI call untuk setiap item secara default.
9. Jangan memproses full-resolution image berulang kali.
10. Jangan hard-code supplier-specific behavior ke generic engine.

---

# PHASE 25 — DELIVERABLES

Pada akhir pekerjaan, hasilkan:

### A. Code

Implementasi nyata di repository.

### B. Guideline

Pastikan repository memiliki/merujuk:

`docs/AI_INVOICE_SKILL_GUIDELINES.md`

### C. Tests

Tambahkan/update tests dan fixtures yang relevan.

### D. Migration

Jika database berubah, buat migration yang aman.

### E. Documentation

Dokumentasikan:

- architecture
- flow
- provider selection
- caching
- confidence
- learning
- supplier skill development

### F. Performance report

Bandingkan baseline vs hasil optimasi berdasarkan angka yang benar-benar diukur.

### G. Final audit

Periksa kembali regression, error handling, security, dan rate-limit behavior.

---

# PHASE 26 — GIT

Jika repository dan credential/remote sudah tersedia:

1. lihat git status
2. inspect diff
3. commit perubahan dengan message yang jelas
4. push ke branch yang sedang digunakan atau branch kerja yang sesuai

Jangan melakukan force push.

Jangan menghapus commit orang lain.

Jika tidak memiliki akses push, berhenti pada perubahan lokal dan laporkan commit yang siap dibuat.

---

# OUTPUT WAJIB DARI AGENT

Setelah selesai, tampilkan ringkasan:

```text
INVOICE SCANNER OPTIMIZATION COMPLETE

1. Current architecture:
2. Main bottlenecks found:
3. Files changed:
4. New services/modules:
5. Supplier skills preserved:
6. AI calls before:
7. AI calls after:
8. Cache added:
9. Confidence system:
10. Auto-learning improvements:
11. Packaging improvements:
12. Tests executed:
13. Regression status:
14. Security checks:
15. Performance benchmark:
16. Database migration:
17. Git commit:
18. Push status:
19. Remaining risks:
```

Jangan mengatakan "100% accurate" atau "unlimited" jika belum dibuktikan melalui test. Gunakan angka hasil pengujian.

---

# PRIORITAS IMPLEMENTASI

Urutan prioritas:

P0 — Jangan rusak scanner yang ada.
P1 — Kurangi AI call.
P1 — Tingkatkan latency.
P1 — Tingkatkan matching accuracy.
P1 — Tambahkan confidence + validation.
P2 — Optimalkan query/cache.
P2 — Perkuat auto-learning.
P2 — Generic parser.
P3 — Provider failover.
P3 — Advanced observability.

Mulai dari audit repository nyata, bukan asumsi.
