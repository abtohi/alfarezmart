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

- Pada halaman Beranda di bagian AKSI CEPAT tambahkan icon untuk mendownload aplikasi offline mode, sehingga ketika user mengklik download, maka aplikasi bisa sepenuhnya berjalan secara offline, database akan didownload semua secara offline dan apabila aplikasi online, maka data akan disinkronkan otomatis, baik sinkron dari offline ke online maupun dari online ke offline, dan semua proses sinkronasi akan terjadi di background tanpa mengganggu user, dan data yang belum sinkron akan muncul count badge nya di icon sync. Jika sudah sinkron semuanya, antara di server dan di local sudah sama, baik database, UI, layout, konten, dan lainnya, maka badge sync akan hilang atau menjadi nol, dan ketika user offline, semua fitur tetap berjalan normal, termasuk fitur update harga, upload foto, jika dalam mode offline, maka aplikasi tetap bisa melakukan update dan upload, hanya saja sementara akan tersimpan di local, setelah aplikasi online baru akan sinkron otomatis ke server.

- Pada halaman login tambahkan juga fitur offline mode, jika user offline maka akan otomatis masuk ke mode offline dan bisa login dengan user dan password yang sama seperti online, namun untuk loginnya tidak perlu melakukan request ke server, melainkan langsung login dengan data yang sudah tersimpan di local, dan ketika aplikasi online baru akan sinkron otomatis ke server.

- Pada halaman Keuangan Harian, saat user memilih Jenis Transaksi Pengeluaran, tolong pada bagian Kategori Transaksi dibuat Default terpilih "Belanja Toko", tetapi user tetap bisa menggantinya.

- pada halaman Keuangan Harian, opsi Kategori Transaksi tolong dibuat dependen terhadap Jenis Transaksi. Jika Pemasukan, maka opsi yang ditampilkan hanya yang Pemasukan, dan jika Pengeluaran, maka opsi yang ditampilkan hanya yang Pengeluaran, jangan ditampilkan semuanya.

- Pada halaman Input Barang Masuk, tampilan form Tanggal kalau di aplikasi Android sudah rapi dan presisi, namun di iphone kenapa masih berantakan ya, form tanggal masih terlalu tinggi ukuran formnya dan bertabrakan dengan icon Kamera, tolong ini diperbaiki.

- Pada halaman Input Barang Masuk, title "Input Barang Masuk" pada bagian atas sebetulnya tidak perlu ditampilkan lagi, karena sudah ada judul halaman di header aplikasi, jadi tolong dihapus saja title tersebut agar tampilan lebih rapi dan ringkas.

- Pada halaman Keuangan Harian, di bagian DAFTAR TRANSAKSI, saat user menyeleksi lebih dari satu transaksi, kemudian mencoba menghapus item yang terseleksi saat ini masih gagal, tolong diperbaiki agar bisa menghapus item yang terseleksi. Saat ini hanya berhasil menghapus 1 item saja tanpa proses seleksi. Saat di select, kemudian klik tombol Hapus Terpilih, muncul error Server mengembalikan respons kosong. Kemungkinan timeout atau error internal, tolong perbaiki.Dan tolong ketika di klik tombol Hapus Terpilih, kemudian muncul dialog konfirmasi hapus, dialog konfirmasinya di desain agar lebih menarik menyesuaikan tema website, saat ini desainnya polos tidak ada seni nya, tolong dibuat lebih elegan dan modern sesuai tema websitenya.

- Pada halaman Keuangan Harian, saat ini ketika user mengklik icon Edit, kemudian mengubah Transaksi Keuangan, ketika diperbaharui Catatan, muncul error pos keuangan tidak valid. Tolong perbaiki ini agar transaksi bisa di edit dan berhasil diperbaharui.

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
