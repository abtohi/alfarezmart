# AlfarezMart AI Assistant - Panduan Integrasi & Update

Dokumen ini menjelaskan arsitektur AI Chat di AlfarezMart, cara kerjanya, dan bagaimana memastikan AI selalu mengetahui fitur terbaru ketika ada penambahan database atau modul baru.

## 🧠 Arsitektur AI (RAG - Retrieval Augmented Generation)

AI AlfarezMart tidak terhubung langsung ke internet secara bebas, melainkan menggunakan pola **"INTERNAL FIRST"**. Semua pengetahuan utamanya disuntikkan secara real-time dari database sebelum merespons pertanyaan pengguna.

File utama penggerak AI:
- `app/services/AiContextBuilder.php`: RAG Engine yang bertugas mengambil data dari seluruh database dan menyusunnya menjadi konteks (prompt) untuk AI.
- `app/controllers/AiChatController.php`: Menangani API, auto-correction, dan komunikasi dengan API OpenRouter.
- `app/models/AiChatModel.php`: Menyimpan histori chat dan **knowledge base** (hasil pembelajaran dari koreksi pengguna).

## 🛠 Aturan Update Saat Menambah Database/Tabel Baru

Agar AI selalu pintar dan tidak "ketinggalan zaman", **SETIAP** kali Anda menambahkan tabel atau relasi baru ke database, Anda **WAJIB** memperbarui `AiContextBuilder.php`.

Ikuti langkah-langkah berikut:

### 1. Buat Method Getter Baru
Jika ada fitur baru (misalnya "Sistem Absensi"), buat private method baru di `AiContextBuilder`:

```php
private function getAttendanceSummary(): array
{
    // Lakukan query JOIN yang diperlukan.
    // Selalu gunakan try-catch agar jika query gagal, tidak merusak chat AI.
    try {
        $stmt = $this->db->query("SELECT ...");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}
```

### 2. Panggil di `buildSystemPrompt()`
Masukkan method tersebut ke dalam array `$context` di bagian atas method `buildSystemPrompt()`, atau buat conditional check berdasarkan kata kunci.

```php
if ($this->contains($q, ['absen', 'kehadiran', 'karyawan'])) {
    $context['absensi_hari_ini'] = $this->getAttendanceSummary();
}
```

### 3. Update Panduan Fitur (`getFeatureGuide`)
AI AlfarezMart dilatih untuk bisa menjadi "Buku Panduan Berjalan" bagi pengguna. Jika Anda menambahkan modul baru, tambahkan penjelasannya ke method `getFeatureGuide()`.

```php
// Tambahkan di dalam method getFeatureGuide():
"14. ABSENSI (/attendance): Fitur untuk mencatat kehadiran karyawan. " .
"    - Masuk ke menu Absensi, klik tombol 'Check In'.\n" .
```

## 🔄 Sistem Pembelajaran Otomatis (Knowledge Base)

AI AlfarezMart terus belajar dari penggunanya. Terdapat tabel `ai_knowledge` yang menyimpan koreksi dan fakta baru.

1. **Auto-Detect Koreksi:** Jika user mengetik *"seharusnya omzet dihitung dari..."* atau *"yang benar adalah..."*, `AiChatController` mendeteksinya menggunakan Regex dan otomatis menyimpan koreksi tersebut ke database.
2. **Koreksi Manual:** Pengguna dapat menekan ikon ✏️ pada pesan AI untuk mengoreksi fakta secara eksplisit.
3. **Retrieval:** Setiap kali pengguna bertanya, AI akan mengekstrak kata kunci dan mencari di `ai_knowledge`. Jika ada fakta terkait, data tersebut diprioritaskan.

## 🕵️‍♂️ Agentic SQL (Text-to-SQL)

Untuk pertanyaan analitik historis yang mendalam (contoh: *"Berapa omzet tanggal 2 Juni 2024?"*), AI dilengkapi kemampuan **Agentic SQL**.

1. **Schema Injection:** `AiContextBuilder` membaca seluruh struktur tabel dan kolom database dan memberikannya kepada AI.
2. **Query Generation:** Jika AI mendeteksi data yang diminta tidak ada di konteks biasa, ia akan merespons dengan: `[SQL_QUERY] SELECT * FROM ... [/SQL_QUERY]`.
3. **Safe Execution:** `AiChatController` mendeteksi tag tersebut, memvalidasi bahwa itu murni query `SELECT` (menolak segala upaya `INSERT/UPDATE/DELETE`), dan menjalankannya via PDO dengan limit otomatis.
4. **Multi-Pass Loop:** Hasil query dilempar kembali ke AI untuk dirangkum menjadi jawaban akhir kepada pengguna.

## 📊 Kapasitas Analisis AI Saat Ini (v3.0)

Saat ini AI mampu memahami dan menganalisis 35+ tabel secara komprehensif:
1. **Keuangan & Laba:** Omzet harian/bulanan, pertumbuhan omzet, perbandingan laba, breakdown pengeluaran/pemasukan.
2. **Stok & Inventori:** Overviews stok kritis, pergerakan stok 7 hari terakhir, expired date alert, produk "dead stock" (tidak laku).
3. **Hutang & Piutang:** Cicilan supplier, piutang pelanggan, breakdown sumber hutang.
4. **Relasi Produk:** Detail produk komplit dengan multi-kemasan, margin ecer/grosir, dan supplier asal produk.
5. **Aktivitas:** Estimasi order ke supplier, jadwal kunjungan sales, rekap konsinyasi, top pelanggan.

## 🚀 Versioning
Saat memperbarui struktur `AiContextBuilder`, pastikan untuk mengupdate konstan `VERSION` dan `SCHEMA_DATE` di bagian atas file.
