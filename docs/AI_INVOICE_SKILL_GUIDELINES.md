# 🧠 AI Invoice Skill Guidelines & Architecture (AlfarezMart)

Panduan teknis dan standar arsitektur untuk menambahkan skill OCR & AI invoice scanner untuk supplier baru di **AlfarezMart**.

Dokumen ini dirancang agar AI di masa depan dapat membaca panduan ini dan langsung mengenerate file skill baru yang **100% akurat, cepat, hemat token, dan kompatibel dengan AI Vision model gratisan** (seperti Gemma 4, Nemotron, dll).

---

## 📑 Daftar Isi
1. [Arsitektur AI Invoice Scanner](#1-arsitektur-ai-invoice-scanner)
2. [Anatomi Invoice Skill](#2-anatomi-invoice-skill)
3. [Langkah Pembuatan Skill Baru (Step-by-Step)](#3-langkah-pembuatan-skill-baru-step-by-step)
4. [Sistem Dynamic Self-Learning (AI Belajar Otomatis)](#4-sistem-dynamic-self-learning-ai-belajar-otomatis)
5. [Contoh Template Lengkap Skill Baru](#5-contoh-template-lengkap-skill-baru)
6. [Best Practices & Tips Presisi 99%+](#6-best-practices--tips-presisi-99)

---

## 1. Arsitektur AI Invoice Scanner

Pipeline pemrosesan invoice di AlfarezMart berjalan secara modular dalam 8 tahapan:

```
[Foto Invoice] 
       │
       ▼
1. ImagePreprocessor (Resize, Exif, Optimasi Format)
       │
       ▼
2. SupplierDetector (Deteksi Supplier via Tanda Tangan / Dropdown)
       │
       ▼
3. SkillManager (Resolve Skill yang Sesuai: MdrInvoiceSkill, GeneralInvoiceSkill, dll)
       │
       ▼
4. Context & Learning Injection (Injeksi Alias Produk & Pola yang Sudah Dipelajari)
       │
       ▼
5. OpenRouter Vision API (Kirim Prompt & Image ke Model AI Pilihan)
       │
       ▼
6. parseItem() dari Skill (Pembersihan, Ekstraksi Brand, Varian, Berat, dan Harga)
       │
       ▼
7. ProductMatcher (Pencocokan Multi-Strategi: Exact Code, Alias, Token Overlap, Packaging Level)
       │
       ▼
8. Frontend Injection (Auto-Fill Form Pembelian Tanpa Roundtrip Tambahan)
       │
       ▼
9. InvoiceLearningService (Otomatis Mempelajari Alias & Pola Baru Saat Pembelian Disimpan)
```

---

## 2. Anatomi Invoice Skill

Setiap skill supplier mengimplementasikan interface `InvoiceSkillInterface` yang terletak di:
`app/services/invoice/skills/InvoiceSkillInterface.php`.

### 5 Method Wajib:

| Method | Return Type | Fungsi |
|---|---|---|
| `getSkillKey()` | `string` | Identifier unik (huruf kecil, misal: `'mdr'`, `'indomarco'`, `'unilever'`) |
| `getSupplierName()` | `string` | Nama resmi supplier (misal: `'PT Medan Distribusindo Raya'`) |
| `getDetectionSignatures()` | `array` | Kata kunci visual unik untuk auto-deteksi dari teks/header faktur |
| `getSystemPrompt(bool $isCorrectionPass)` | `string` | Prompt instruksi khusus tabel & kolom faktur supplier tersebut |
| `getUserPromptHints()` | `string` | Petunjuk ringkas format kolom untuk model |
| `parseItem(array $rawItem)` | `array` | Parser cerdas untuk membersihkan harga, mengekstrak merk, varian, berat, dan kemasan |
| `determinePackagingLevel(float $unitPrice, array $packagings, string $extractedUnit, ?float $lastBuyPrice)` | `array` | Penentu level kemasan (Karton, Renceng, Lusin, Pcs) berdasarkan harga satuan |

---

## 3. Langkah Pembuatan Skill Baru (Step-by-Step)

Ketika user meminta membuat skill untuk supplier baru (misal: **PT Indomarco Adi Prima / Indofood**):

### Langkah 1: Analisis Struktur Nota Fisik
Identifikasi kolom-kolom pada nota:
- Nomor kolom dari kiri ke kanan.
- Posisi `QUANTITY` & satuannya (Karton, Dus, Pcs).
- Posisi `KODE BARANG` supplier.
- Posisi `NAMA BARANG` dan pola singkatannya (misal: `INDOMIE GOR SP`, `BUMBU RACIK`).
- Posisi `HARGA SATUAN`, `DISKON`, dan `TOTAL JUMLAH (Rp)`.

### Langkah 2: Buat File Skill di `app/services/invoice/skills/`
Buat file bernama `[Supplier]InvoiceSkill.php` (contoh: `IndomarcoInvoiceSkill.php`).

### Langkah 3: Daftarkan Skill di `SkillManager.php`
Buka `app/services/invoice/skills/SkillManager.php`:
1. Tambahkan `require_once __DIR__ . '/[Supplier]InvoiceSkill.php';`
2. Daftarkan di constructor `$this->registerSkill(new [Supplier]InvoiceSkill());`

### Langkah 4: Daftarkan Tanda Tangan di `SupplierDetector.php`
Buka `app/services/invoice/SupplierDetector.php` dan tambahkan deteksi kata kunci supplier tersebut pada method `detect()`.

---

## 4. Sistem Dynamic Self-Learning (AI Belajar Otomatis)

AlfarezMart dilengkapi dengan sistem pembelajaran otomatis (`InvoiceLearningService.php`) yang membuat sistem semakin cerdas seiring penggunaan:

### Bagaimana AI Belajar?
1. **Auto-Learning Alias Nota (`supplier_invoice_name`)**:
   - Ketika user menyimpan form pembelian, sistem secara otomatis mengekstrak nama barang nota supplier dan menyimpannya sebagai alias baru di database produk.
   - Pada scan berikutnya, barang tersebut akan langsung ter-match **100% instan** dengan Score 200 (Exact Match).
2. **Auto-Learning Supplier Product Code (`supplier_product_code`)**:
   - Kode barang supplier yang diinput/dikonfirmasi otomatis dihubungkan ke `supplier_products`.
3. **Dynamic Prompt Context Injection**:
   - Sebelum mengirim gambar ke AI, sistem menyuntikkan daftar produk & alias yang sudah dipelajari ke dalam prompt.
   - Hasilnya, model AI gratisan sekalipun dapat mengenali barang dengan tingkat keberhasilan 99%+.

---

## 5. Contoh Template Lengkap Skill Baru

Berikut adalah template standar yang siap digunakan saat membuat skill supplier baru:

```php
<?php
require_once __DIR__ . '/InvoiceSkillInterface.php';

/**
 * [SupplierName]InvoiceSkill
 *
 * Dedicated AI scanning skill for [Nama PT Supplier].
 *
 * @package AlfarezMart\Services\Invoice\Skills
 */
class [SupplierName]InvoiceSkill implements InvoiceSkillInterface
{
    /** Singkatan umum nota supplier ini */
    const ABBREVIATIONS = [
        'sct'  => 'sachet',
        'btl'  => 'botol',
        'bks'  => 'bungkus',
        'ctn'  => 'karton',
        'dus'  => 'karton',
        'gor'  => 'goreng',
        'sp'   => 'spesial',
        'kcl'  => 'kecil',
        'bsr'  => 'besar',
    ];

    /** Brand-brand yang dinaungi supplier ini */
    const KNOWN_BRANDS = [
        'indomie', 'supermi', 'sarimi', 'pop mie', 'chitato', 'chiki', 'indomilk',
    ];

    public function getSkillKey(): string
    {
        return '[nama_pendek_supplier]'; // contoh: 'indomarco'
    }

    public function getSupplierName(): string
    {
        return '[Nama Lengkap Supplier]'; // contoh: 'PT Indomarco Adi Prima'
    }

    public function getDetectionSignatures(): array
    {
        return [
            'indomarco',
            'indomarco adi prima',
            'indofood',
            // nomor rekening atau ciri visual lainnya
        ];
    }

    public function getSystemPrompt(bool $isCorrectionPass = false): string
    {
        $lines = [];
        $lines[] = 'Kamu adalah AI OCR & data extractor spesialis faktur ' . $this->getSupplierName() . '.';
        $lines[] = 'Tugasmu adalah membaca seluruh baris produk pada tabel faktur secara lengkap 100% tanpa terlewat.';
        $lines[] = '';
        $lines[] = '## STRUKTUR TABEL FAKTUR:';
        $lines[] = '1. KODE BARANG';
        $lines[] = '2. NAMA BARANG LENGKAP';
        $lines[] = '3. QUANTITY (misal: "1 DUS", "10 PCS")';
        $lines[] = '4. HARGA SATUAN';
        $lines[] = '5. DISKON';
        $lines[] = '6. TOTAL JUMLAH (Rp) (Kolom paling kanan setelah diskon)';
        $lines[] = '';
        $lines[] = '## ATURAN EKSTRAKSI:';
        $lines[] = '1. BACA SEMUA BARIS PRODUK DARI PALING ATAS SAMPAI BARIS TERAKHIR TABEL.';
        $lines[] = '2. "supplier_code": Ambil kode barang supplier jika ada.';
        $lines[] = '3. "name": Ambil nama barang lengkap persis di nota.';
        $lines[] = '4. "qty": Jumlah beli (angka).';
        $lines[] = '5. "unit": Satuan (Karton, Dus, Pcs, Renceng).';
        $lines[] = '6. "total_price": Total harga akhir setelah diskon di kolom paling kanan.';
        $lines[] = '7. "unit_price": Biarkan null (dihitung backend otomatis).';
        $lines[] = '8. ABAIKAN baris header/footer, subtotal, dan nomor rekening.';
        $lines[] = '';
        $lines[] = '## FORMAT OUTPUT JSON (HANYA JSON ARRAY VALID):';
        $lines[] = '[';
        $lines[] = '  {';
        $lines[] = '    "supplier_code": "100234",';
        $lines[] = '    "name": "INDOMIE MI GORENG SPESIAL 85GR",';
        $lines[] = '    "qty": 5,';
        $lines[] = '    "unit": "DUS",';
        $lines[] = '    "total_price": 550000,';
        $lines[] = '    "unit_price": null';
        $lines[] = '  }';
        $lines[] = ']';

        return implode("\n", $lines);
    }

    public function getUserPromptHints(): string
    {
        return "Invoice " . $this->getSupplierName() . ". Ambil kode barang, nama produk, quantity, dan total harga di kolom paling kanan. Ekstrak SEMUA baris produk tanpa terkecuali.";
    }

    public function parseItem(array $rawItem): array
    {
        $name = trim($rawItem['name'] ?? $rawItem['product_name'] ?? '');
        $code = trim($rawItem['supplier_code'] ?? $rawItem['code'] ?? '');
        
        $rawQty = $rawItem['qty'] ?? 1;
        $qty = is_numeric($rawQty) ? max(1, (float)$rawQty) : 1;
        $unit = strtoupper(trim($rawItem['unit'] ?? ''));

        // Pembersihan total harga Indonesia format (titik pemisah ribuan)
        $rawTotal = $rawItem['total_price'] ?? $rawItem['total'] ?? 0;
        if (is_string($rawTotal)) {
            $cleaned = preg_replace('/[^0-9,]/', '', str_replace('.', '', $rawTotal));
            $cleaned = str_replace(',', '.', $cleaned);
            $totalPrice = (float)$cleaned;
        } else {
            $totalPrice = (float)$rawTotal;
        }

        $unitPrice = $qty > 0 ? round($totalPrice / $qty, 2) : $totalPrice;

        // Ekstraksi brand, varian, berat
        $parsed = $this->parseProductName($name);

        return [
            'name'                  => $name,
            'supplier_invoice_name' => $name,
            'expanded_name'         => $parsed['expanded_name'],
            'supplier_code'         => $code,
            'qty'                   => $qty,
            'unit'                  => $unit,
            'total_price'           => $totalPrice,
            'unit_price'            => $unitPrice,
            'brand'                 => $parsed['brand'],
            'variant'               => $parsed['variant'],
            'weight'                => $parsed['weight'],
            'weight_unit'           => $parsed['weight_unit'],
            'skill_used'            => $this->getSkillKey()
        ];
    }

    private function parseProductName(string $name): array
    {
        $result = [
            'brand'         => '',
            'variant'       => '',
            'weight'        => null,
            'weight_unit'   => '',
            'expanded_name' => '',
        ];

        if (empty($name)) return $result;
        $lower = mb_strtolower(trim($name));

        // Ekstrak berat (contoh: 85GR, 500ML, 1KG)
        if (preg_match('/(\d+(?:\.\d+)?)\s*(gr|g|ml|l|kg)\b/i', $name, $m)) {
            $result['weight'] = (float)$m[1];
            $result['weight_unit'] = strtolower($m[2]) === 'gr' ? 'g' : strtolower($m[2]);
        }

        // Deteksi Brand
        foreach (self::KNOWN_BRANDS as $b) {
            if (stripos($lower, $b) !== false) {
                $result['brand'] = $b;
                break;
            }
        }

        // Ekspansi singkatan
        $words = preg_split('/\s+/', $lower);
        $exp = [];
        foreach ($words as $w) {
            $clean = preg_replace('/[^a-z0-9]/', '', $w);
            $exp[] = self::ABBREVIATIONS[$clean] ?? $w;
        }
        $result['expanded_name'] = implode(' ', $exp);

        return $result;
    }

    public function determinePackagingLevel(
        float $unitPrice,
        array $packagings,
        string $extractedUnit = '',
        ?float $lastBuyPrice = null
    ): array {
        if (empty($packagings)) {
            return ['packaging' => null, 'level' => 1, 'strategy' => 'default_level_1'];
        }

        // Price Distance Matching
        if ($unitPrice > 0) {
            $bestPkg = null;
            $minDiff = PHP_FLOAT_MAX;
            $bestLevel = 1;

            foreach ($packagings as $pkg) {
                $buyPrice = (float)($pkg['buy_price'] ?? 0);
                if ($buyPrice > 0) {
                    $diff = abs($buyPrice - $unitPrice);
                    if ($diff < $minDiff && ($diff / $buyPrice) <= 0.45) {
                        $minDiff = $diff;
                        $bestPkg = $pkg;
                        $bestLevel = (int)$pkg['level'];
                    }
                }
            }

            if ($bestPkg !== null) {
                return ['packaging' => $bestPkg, 'level' => $bestLevel, 'strategy' => 'price_distance'];
            }
        }

        return ['packaging' => $packagings[0], 'level' => (int)($packagings[0]['level'] ?? 1), 'strategy' => 'fallback'];
    }
}
```

---

## 6. Best Practices & Tips Presisi 99%+

1. **Format Angka Indonesia**:
   - Selalu hapus titik (`.`) ribuan sebelum mengonversi ke float.
   - Contoh: `"474.000"` → `474000`.
2. **Kalkulasi Harga Satuan**:
   - Biarkan AI mengisi `total_price` kolom paling kanan, dan biarkan backend menghitung `unit_price = total_price / qty`. Ini mencegah kesalahan pembulatan OCR.
3. **Diskon Baris**:
   - Ambil nilai di kolom paling kanan `JUMLAH (Rp)` yang sudah merupakan nilai nett setelah promo/regular discount.
4. **Ekspansi Singkatan**:
   - Daftarkan singkatan khas supplier ke dalam konstanta `ABBREVIATIONS` agar pencocokan nama berbasis fuzzy dan token overlap bekerja maksimal.
5. **Self-Learning Verification**:
   - Pastikan setiap ada transaksi pembelian baru, `InvoiceLearningService::learnFromPurchase()` terpanggil agar memori alias nota otomatis terupdate.

---
*Dokumen ini dikelola secara otomatis oleh Sistem AI AlfarezMart.*
