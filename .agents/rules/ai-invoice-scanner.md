# 🔒 ATURAN WAJIB: AI Invoice Scanner & Pemilihan Model OpenRouter (JANGAN DIUBAH)

> **PERINGATAN KERAS**: Aturan ini mengatur integrasi AI Invoice Scanner dengan OpenRouter API (`app/services/invoice/InvoiceScanService.php` dan `app/views/settings/app.php`). **DILARANG KERAS** memasukkan model berbayar atau model usang tanpa endpoint ke dalam pilihan scanner.

---

## 1. Aturan Model AI Scanner (100% Full Free Tanpa Biaya)

Untuk menjamin fitur AI Scan Invoice selalu dapat digunakan tanpa saldo berbayar dan tanpa error limit/endpoint:

### ⚠️ Daftar Model Wajib (100% Free Multimodal Vision):
1. **`google/gemma-4-26b-a4b-it:free`** : Google Gemma 4 26B A4B Vision (Rekomendasi Utama — Cepat, Akurat & Cerdas OCR Faktur).
2. **`google/gemma-4-31b-it:free`** : Google Gemma 4 31B Vision (Kualitas Tinggi).
3. **`openrouter/auto`** : OpenRouter Auto Router (Gratis).
4. **`nvidia/nemotron-nano-12b-v2-vl:free`** : NVIDIA Nemotron Nano 12B VL (Multimodal Vision).
5. **`dots-studio/dots-3-note-preview:free`** : Dots Studio Dots3 Note Preview (Gratis).

### ❌ DILARANG KERAS:
- Dilarang memasukkan model berbayar (seperti `google/gemini-2.0-flash-001`, `google/gemini-2.0-flash-lite-001`, Claude, GPT-4o) ke dalam daftar model default AI Scanner jika akun pengguna menggunakan kunci gratis (0 balance).
- Dilarang memasukkan model yang sudah ditutup/deprecated di OpenRouter (seperti `meta-llama/llama-3.2-11b-vision-instruct:free`, `qwen/qwen-2.5-vl-72b-instruct:free`).

---

## 2. Aturan Tampilan di Pengaturan Sistem & AI (`app/views/settings/app.php`)

1. **Format Tampilan Model**:
   - Bagian *1. AI Invoice Scanner* **WAJIB** menggunakan **Dropdown List (`<select id="ai_model_select">`)** yang rapi dan mudah dipilih oleh pengguna.
   - Sediakan opsi model free di atas dan opsi `custom` ("Model Kustom (Ketik Nama Model Manual)") di paling bawah dropdown.
   - Jika opsi `custom` dipilih, tampilkan input box text `#ai_model_custom_wrap` untuk mengetik model kustom.
2. **Sinkronisasi Form**:
   - Nilai pilihan model harus otomatis terhubung ke input hidden `#ai_model` dan tersimpan ke tabel `app_settings` dengan kunci `ai_model`.

---

## 3. Aturan Failover & Timeout di Backend (`InvoiceScanService.php`)

1. **Daftar Failover Otomatis**:
   - Backend `InvoiceScanService.php` **WAJIB** memiliki `$DEFAULT_VISION_MODELS` berisi list model 100% free yang aktif.
   - Jika user memilih model tertentu, sistem mencoba model tersebut terlebih dahulu. Jika gagal (rate-limit / offline), sistem otomatis beralih ke model free berikutnya tanpa crash.
2. **Batas Waktu (Timeout)**:
   - cURL timeout per model diatur minimal **65-90 detik** (`CURLOPT_TIMEOUT => 90`) karena pemrosesan gambar OCR faktur di tier free membutuhkan waktu 15-45 detik.
   - `set_time_limit(180)` pada controller API (`ApiController@scanInvoiceAI`) untuk mencegah fatal timeout server.

---

## 4. Ringkasan File yang Dilindungi

| File | Komponen Kritis yang Dilindungi |
|------|---------------------------------|
| [`app/services/invoice/InvoiceScanService.php`](file:///c:/xampp/htdocs/AlfarezMart/app/services/invoice/InvoiceScanService.php) | `$DEFAULT_VISION_MODELS`, `callOpenRouter()`, prompt cleaner, timeout 90s |
| [`app/views/settings/app.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/settings/app.php) | Dropdown selector `ai_model_select`, `onModelSelectChange()`, custom model input |
| [`app/controllers/ApiController.php`](file:///c:/xampp/htdocs/AlfarezMart/app/controllers/ApiController.php) | `scanInvoiceAI()` time limit 180s |

---

*Aturan ini dibuat pada 18 Agustus 2026 dan berlaku permanen di seluruh sesi pengembangan.*
