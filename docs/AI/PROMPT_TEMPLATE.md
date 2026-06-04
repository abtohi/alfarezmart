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


[ ] Pada halaman Pengaturan Sistem & AI, saat user memilih model lain selain Google Gemini 2.5 Flash, saat digunakan untuk scan AI otomatis masih gagal dan muncul tulisan OpenRouter API Error (404): {"error":{"message":"No anthropic/claude-3.5-sonnet.","code":404},"user_id":"user_3EeAdlNQLhhblbd"}, tolong ini diperbaiki agar bisa digunakan semua modelnya
[ ] Pada halaman Pengaturan Sistem & AI, saat memilih model AI, tampilan UI modal nya masih jadul dan belum modern menyesuaikan tema aplikasi, tolong ini desainnya diperbaiki agar lebih elegan, modern dan sesuai dengan tema aplikasi. Samakan model panelnya seperti panel opsi lainnya di halaman ini agar selaras dengan layout aplikasi, tolong rapikan lagi tampilan dropdownlist di mobile app agar lebih elegan dan stylish mengikuti tema website, jangan polos.
[ ] Pada saat menggunakan mode offline, saat ini ketika di klik Keuangan Harian masih belum bisa membuka halaman tersebut, dan kembali ke Beranda. Pastikan halaman ini bisa dibuka dan juga bisa melihat keuangan. Hal yang sama juga terjadi ketika mengklik laporan penjualan, termasuk user juga bisa input pengeluaran dan pemasukan saat mode offline. Pastikan semua fitur input produk, keuangan tetap bisa digunakan meskipun dalam mode offline, hanya scan AI otomatis yang tidak dapat digunakan dalam mode offline. Untuk fitur input produk, keuangan, laporan penjualan, penjualan, dll yang terkait dengan database, saya ingin ketika dalam keadaan offline, data tetap bisa disimpan ke lokal, kemudian setelah aplikasi online, secara otomatis data yang tersimpan di lokal akan kesinkron otomatis ke server di backend, dan count badge akan berkurang satu demi satu seiring data sinkron. Saat offline, jika ada inputan atau edit, count badge akan muncul di icon sync. Pastikan saat user mengklik Unduh Data Offline, semuanya terdownload dan bisa digunakan, termasuk routingannya bisa berjalan semuanya.
[ ] Pada halaman Input Barang Masuk, saat ini setelah melalui proses scan AI, kemudian user mengubah Total Harga Pembelian, ketika user mengklik tombol Distribusikan ke Harga Modal Barang, kenapa Total Harga Pembelian berubah lagi menjadi harga yang sebelum saya edit (yang hasil scan AI), seharusnya tombol Distribusikan ke Harga Modal tidak akan mengubah data yang sudah di edit, tolong perbaiki ini
[ ] Pada halaman Edit Produk dan Tambah Produk, tolong untuk setiap harga modal di setiap kemasan, juga dipertimbangkan angka desimal, jangan langsung digenapkan ke bilangan bulat, baik hasil penggunaan kalkukator, harga perkalian atau pembagian ke jenis kemasan lain, jika memang ada desimal, tolong tampilkan desimalnya, maksimal 2 digit desimal, namun jika memang harga modalnya bilangan bulat, tolong desimalnya jangan ditampilkan.

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
