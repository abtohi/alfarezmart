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

- Pada halaman Edit Produk atau Tambah Produk baru, Input Barang, dan Input Barang massal, ubah semua konsep margin menjadi markup, yakni perhitungan berdasarkan harga modal. Karena saya bukan menggunakan margin, tapi markup. Tolong sesuaikan semuanya

- Tolong buat algoritma Input Barang dan Input Barang Massal, ketika oleh user diinput PPN dan Diskon, maka ketika harganya disimpan dan didistribusikan ke harga modal barang, maka harga modalnya akan berubah menjadi Harga Modal + nilai PPN - nilai Diskon dan harga modal inilah yang akan didistribusikan ke harga modal barang di outlet. Jangan sampai harga modal produk berubah menjadi harga modal tanpa PPN jika memang ada PPN yang ditambahkan oleh user. 

- Pada halaman Edit Produk dan Tambah Produk tolong ubah posisi checkbox Harga Jual Custom (harga spesial per kemasan ini), seharusnya bukan berada di atas form PPN dan Diskon, tetapi berada diatas form Jual Ecer/Retail. Tolong sesuaikan

- Tambahkan fitur agar user benar benar bisa full membuka mode offline, semua menu termasuk menu scan bisa offline, dan ketika dibuka offline, dan sudah memiliki riwayat login juga harus tetap bisa login dan mengakses semua fitur. Saat ini ketika offline user tidak bisa membuka detail produk, ketika search produk kemudian produknya di klik, masih kembali ke halaman beranda, fitur scan juga tidak berjalan ketika mode offline. Aku ingin ketika user membuka aplikasi dengan keadaan online, di balik layar ada sistem yang bekerja mendownload dan melakukan sinkronisasi data offline dengan online, termasuk ketika user mengedit atau menambahkan produk, ketika online produk yang ditambahkan bisa langsung sinkron ke server, tetapi ketika offline, data tetap bisa tersimpan di lokal, kemudian di icon sync akan muncul count notif, 1,2,3,4 yang mengindikasikan jumlah perubahan yang perlu di sinkron, ketika app online kembali maka count notif akan hilang dan semua data akan tersinkron ke server. Jika awalnya count notifnya 4, kemudian yang tersinkron 1, maka akan menjadi 3, dst. Saat proses sinkronisasi berjalan, maka icon sync akan berubah menjadi icon animasi sedang memuat, dan ketika selesai, icon akan kembali seperti semula dan count notifnya akan hilang (Jika tidak ada lagi perubahan yang perlu di sinkron). User bisa mengatur kapan proses sinkronisasi berjalan secara manual (dengan menekan tombol sync), atau bisa juga diatur secara otomatis (ketika app online). Jika user mengatur sinkronisasi secara otomatis, maka ketika app online maka akan terjadi sinkronisasi, dan ketika app offline, maka tidak akan terjadi sinkronisasi. Pastikan semua proses sinkronisasi ini bisa berjalan dengan cepat dan tidak mengganggu pengalaman pengguna, pastikan juga tidak ada data yang hilang atau rusak selama proses sinkronisasi. Jika user sudah login dan berada di dalam aplikasi, user tetap bisa menggunakan fitur-fitur yang tersedia tanpa harus login kembali. 

-Pastikan semua perubahan bisa terimplementasi di mobile app, jangan hanya di browser, pastikan versi, cache, cookies, dan semuanya bisa di refresh agar appnya update.

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
