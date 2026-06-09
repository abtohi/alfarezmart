# PROMPT TEMPLATE — AlfarezMart

> Salin template di bawah ini, isi bagian `[TASK]`, lalu kirim ke AI Agent.
> Template ini menghemat quota karena AI langsung tahu file mana yang perlu dibaca.

---

## Cara Penggunaan

1. Pilih template sesuai jenis task (**Minor** atau **Mayor**).
2. Isi bagian `## Task` dengan poin-poin pekerjaan.
3. Hapus bagian yang tidak perlu.
4. Kirim ke AI.

---

---

# ═══════════════════════════════════════
# TEMPLATE A — TASK MINOR
# (Bug fix, tweak UI, perubahan 1–2 file,
#  tambah field kecil, perbaikan teks, dll)
# ═══════════════════════════════════════

```
Baca terlebih dahulu:
- `docs/AI/ai-instructions.md` → sebagai rules pengerjaan
- `docs/AI/CURRENT_STATE.md` → sebagai konteks kondisi terkini

Klasifikasikan task ini sebelum mulai:
- Jika MINOR → kerjakan langsung sesuai rules.
- Jika ternyata MAYOR → baca juga `docs/AI/BLUEPRINT.md` dan `docs/AI/CHANGE_LOG.md` sebelum mulai.

## Task

* Jangan mengubah fitur yang sudah ada, kecuali ada permintaan khusus dari user
* Pada halaman produk, pada bagian informasi Modal dan Selisih, tolong jangan ditulis "Modal", tapi cukup M saja, dan selisih tolong diubah menjadi P saja yang artinya Profit, begitu juga pada halaman Scan atau Cek Harga, Modal cukup tulis M: dan Selisih cukup tulis P:
* Pada halaman Scan, tolong untuk harga tier juga ditampilkan, beli berapa dapat harga berapa. Saat ini hanya ditampilkan informasi Qty, tetapi belum ada informasi harganya.
* 
* Update versi, commit dan push

#PROMPT
You are an expert AI Assistant specialized in the [Insert Your App Name] platform. 
Your goal is to provide accurate, helpful, and context-aware guidance to users.

### CORE OPERATIONAL RULES:
1. SOURCE OF TRUTH: Answer questions based ONLY on the provided [Application Documentation] and [Database Schema]. 
2. SCOPE: Do not discuss topics outside of [Insert Your App Name] features, database structure, or usage instructions. If a user asks a question irrelevant to the app, politely redirect them to relevant app features.
3. DATABASE EXPERTISE: When users ask about data, use the provided [Database Schema] to explain how data is stored, related, and queried.
4. GUIDANCE: Act as an interactive user manual. If a user has a problem, explain the solution by referencing specific steps or features within the app.
5. TONE: Professional, helpful, concise, and encouraging.

### KNOWLEDGE BASE (SYSTEM CONTEXT):
- [Application Features]: {INSERT_YOUR_APP_GUIDANCE_TEXT_HERE}
- [Database Schema]: {INSERT_JSON_OR_SQL_SCHEMA_HERE}

### RESPONSE PROTOCOL:
- If you are unsure of the answer based on the context, state: "I'm sorry, I don't have information on that specific feature or database structure."
- If the user's issue relates to a database state, explain it using the tables/columns provided in the schema.

How to implement this in your PHP backend:
​Since your application is offline-first (PWA), you should handle this dynamically. Do not hard-code everything into the prompt string if it's too large, or you will hit token limits.
​Preparation (The "Context Injection"):
​Create a text file or a JSON file in your project containing your Help/Guidance content.
​Export your MySQL schema using mysqldump --no-data.
​Dynamic Assembly (PHP):
When the user sends a message, your PHP script should:
​Fetch the relevant snippets (or the full schema) from your local files.
​Inject them into the {INSERT_...} placeholders in the prompt above.
​Send the final constructed string to your AI API.
​A Pro-Tip for "Context Limits":
​If your application grows and the documentation becomes huge, do not send the whole thing every time. Use a "Retrieval" strategy:
​Use your PHP backend to search your documentation for the specific keywords the user asked about.
​Only send the AI the relevant paragraph from your documentation and the relevant table from your schema based on the user's query."

* Check semua error di terminal, di tab problems, perbaiki semua errornya, setelah selesai cari lagi errornya di problems, lalu perbaiki sampai tuntas, jangan berhenti sebelum semua error hilang

* Update ai-instructions agar ketika ada tambahan fitur, atau database, memastikan ai chat akan selalu update dengan informasi terbaru, dan update dokumentasi AI agar bisa sesuai.

* Commit dan push ke github


## Konteks Tambahan (opsional)

- Modul terkait: [nama modul]
- File yang kemungkinan perlu diubah: [daftar file jika diketahui]
- Catatan khusus: [jika ada hal yang perlu diperhatikan]
```

---

---

# ═══════════════════════════════════════
# TEMPLATE B — TASK MAYOR
# (Modul baru, refactor, fitur kompleks,
#  perubahan lintas modul, arsitektur baru)
# ═══════════════════════════════════════

```
Baca terlebih dahulu (WAJIB semua):
1. `docs/AI/ai-instructions.md` → sebagai rules pengerjaan
2. `docs/AI/BLUEPRINT.md` → untuk memahami arsitektur & pola yang sudah ada
3. `docs/AI/CURRENT_STATE.md` → untuk konteks kondisi terkini & pending issues
4. `docs/AI/CHANGE_LOG.md` → untuk memahami histori & menghindari regression

Klasifikasikan task ini terlebih dahulu sebagai MAYOR, lalu kerjakan sesuai rules.

## Task

- [isi task 1]
- [isi task 2]
- [isi task 3]

## Deskripsi Kebutuhan

[Jelaskan dengan singkat apa yang ingin dicapai, mengapa, dan bagaimana gambaran hasilnya]

## Konteks Tambahan (opsional)

- Modul terkait: [nama modul]
- Pattern referensi yang mirip: [nama modul/file yang bisa dijadikan acuan]
- Constraint/batasan: [hal yang tidak boleh diubah]
- Catatan khusus: [jika ada]
```

---

---

## Panduan Klasifikasi Task

AI wajib mengklasifikasikan task di awal sebelum mengerjakan:

### MINOR — cukup baca `ai-instructions.md` + `CURRENT_STATE.md`

| Ciri-ciri Task Minor |
|----------------------|
| Perubahan pada 1–2 file saja |
| Bug fix di satu fungsi/method |
| Tweak tampilan (warna, teks, spacing) |
| Menambah field kecil di form/tabel |
| Perbaikan validasi atau pesan error |
| Update teks/label/placeholder |
| Fix typo atau komentar |
| Perubahan logika kecil yang terisolasi |

### MAYOR — wajib baca semua 4 file MD

| Ciri-ciri Task Mayor |
|----------------------|
| Modul/halaman baru |
| Fitur baru yang menyentuh ≥ 3 file |
| Perubahan database schema |
| Refactor signifikan |
| Perubahan arsitektur atau routing |
| Integrasi sistem baru (API eksternal, library baru) |
| Perubahan yang berdampak ke banyak modul |
| Perubahan PWA (service worker, manifest) |
| Perubahan layout utama (`app.php`) |
| Perubahan system-wide (utility, helper core) |

---

## Contoh Penggunaan

### Contoh Task Minor

```
Baca terlebih dahulu:
- `docs/AI/ai-instructions.md` → sebagai rules pengerjaan
- `docs/AI/CURRENT_STATE.md` → sebagai konteks kondisi terkini

Klasifikasikan task ini sebelum mulai.

## Task

- Di halaman daftar produk, tambahkan kolom "Stok" di tabel yang sudah ada
- Pastikan kolom ini tampil di mobile dengan lebar yang proporsional
- Nilai stok yang <= 5 ditampilkan dengan warna merah (--danger)

## Konteks Tambahan

- Modul terkait: Products
- File kemungkinan: `app/views/products/index.php`
```

---

### Contoh Task Mayor

```
Baca terlebih dahulu (WAJIB semua):
1. `docs/AI/ai-instructions.md`
2. `docs/AI/BLUEPRINT.md`
3. `docs/AI/CURRENT_STATE.md`
4. `docs/AI/CHANGE_LOG.md`

Klasifikasikan task ini terlebih dahulu sebagai MAYOR, lalu kerjakan sesuai rules.

## Task

- Buat modul Retur/Pengembalian Barang (Return) lengkap
- User bisa memilih transaksi penjualan lama lalu pilih item yang dikembalikan
- Stok otomatis bertambah setelah retur diproses
- Tampil di laporan sebagai entri terpisah

## Deskripsi Kebutuhan

Toko butuh fitur retur untuk mencatat barang yang dikembalikan pelanggan.
Hasilnya: form retur, daftar retur, update stok otomatis, dan entri laporan.

## Konteks Tambahan

- Pattern referensi: modul Purchase (pola input item mirip)
- Constraint: jangan ubah tabel `sales` dan `sale_items` yang sudah ada
```

---

## Tips Hemat Quota

1. **Gunakan Template A untuk task kecil** — AI tidak perlu baca BLUEPRINT dan CHANGE_LOG.
2. **Tulis task dalam poin-poin singkat** — hindari narasi panjang yang berulang.
3. **Sebutkan file yang mungkin terlibat** jika sudah diketahui — menghemat waktu AI investigasi.
4. **Satu prompt = satu scope jelas** — jangan campur banyak modul berbeda dalam satu task jika tidak berkaitan.
5. **Percayakan klasifikasi ke AI** — AI akan memberitahu jika task ternyata lebih besar dari yang diperkirakan.
