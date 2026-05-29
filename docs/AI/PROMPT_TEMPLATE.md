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

- Pada halaman Tambah Produk, saat saya mencoba menambah produk baru masih gagal ada error SQL constrain full_name, tolong cek lagi dan perbaiki.

- Aku ingin menambahkan fitur Hitung Orderan, fitur ini akan saya gunakan untuk menghitung estimasi orderan ke supplier, dengan cara memilih supplier, kemudian memilih barang yang akan di order, sediakan form pencarian nama barang, form pencariannya dilakukan dengan cara mencari keyword terbanyak misal ada tulisan Chocolatos Wafer Roll Chocolate 24g, maka ketika ditulis Chocolatos 24g langsung memunculkan semua list chocolatos dengan berat 24g, silahkan lihat referensi di halaman produk, gunakan algoritma yang sama untuk pencarian produk. Setelah produk dipilih, kemudian user bisa memilih satuan, qty dan akan terhitung otomatis total dari harga modal yang harus dibeli. Kemudian user bisa menambahkan produk lain yang akan di order dengan cara mencari produk, lalu menentukan qty dan satuan, lalu akan muncul total (qty*harga modal sesuai jenis kemasan yang dipilih), kemudian dipaling bawah akan ada informasi estimasi orderan totalnya habis berapa. Kemudian tambahkan tombol copas untuk mencopas semua data orderan, data ini akan digunakan untuk di copas ke whatsapp dengan format
Nama Label Struk (Qty Satuan Kemasan), contoh :
* Intermi Kaldu Ayam 60g (2 karton)
* Sedaap Mi Goreng 90g (3 karton)
* Bango Kecap Manis 265g (1 karton)
* SB Garam Kasar 9kg (2 Pack)
dan seterusnya, ketika di copas, pastikan tidak ada harga modal, cukup formatnya seperti yang saya tulis diatas. Tolong tambahkan menu Hitung Orderan ini diberanda, silahkan optimalkan fitur ini agar user friendly.

- Pada halaman Edit Produk, ketika user ingin mengubah produk ke produk Multivarian, kemudian menggunakan referensi produk lain, pastikan barcode untuk setiap kemasan jangan di reset, tetap pertahankan barcode sebelumnya, saat ini ketika saya mengedit produk, kemudian menceklist produk referensi, barcodenya jadi hilang, padahal sebelumnya ada. Tolong cek dan perbaiki

- Tambahkan fitur agar ketika offline, user level superadmin tetap bisa login, karena aplikasi ini sangat crusial, jadi harus bisa tetap pantau dan cek harga meskipun dalam keadaan offline. Ketika offline, jika di HP ada cache/cookies superadmin yang login, maka akan dibuat autologin saat offline tanpa harus login kembali.

- Pada halaman Kasir POS, saat user mau mencari dan menghubungkan ke printer bluetooth thermal, akan muncul pop up modal dialog yang menampung list printer yang bisa dihubungkan, saat ini desain modal dialognya masih tidak mengikuti standar desain aplikasi, tolong ini dirapikan dan di desain agar lebih elegan, modern, dan sesuai standar desain aplikasi.

- Pada halaman Kasir POS, tolong ketika sudah pernah ada printer thermal yang terhubung, saat aplikasi dibuka kembali akan otomatis terhubung, tanpa perlu pairing lagi. Saat ini setiap kali fitur POS digunakan, harus selalu menghubungkan ulang ke printer bluetooth, ini sangat tidak user friendly, tolong cek dan perbaiki. Demikian pula di fitur history penjualan, ketika invoicenya akan dicetak ulang, pastikan printer thermalnya auto connect.

- Tolong cek seluruh kodingan, dan tambahkan limitasi untuk user level staff, diantaranya : Staff tidak bisa melihat omzet, hutang, dan catatan keuangan lainnya, tolong hide semua fitur yang berkaitan dengan hal finansial, baik omzet, catatan hutang maupun catatan keuangan lainnya. Yang bisa melihat hanya superadmin. Selain itu staff juga tidak boleh menghapus produk, atau mengedit produk. Tetapi staff bisa menambahkan produk baru, barangkali staff menggunakan fitur Kasir POS lalu mau menambahkan produk custom, maka ini bisa dilakukan.

- Tolong pastikan geofencing aktif, staff tidak dapat membuka aplikasi jika berada diluar radius yang ditentukan oleh admin. Pastikan fitur ini benar benar bisa di tes dan benar benar bisa berjalan, jika ditentukan radius 5m, maka ketika lebih dari 5 meter dari titik lokasi yang ditentukan, maka akun staff akan auto logout dan muncul alert "Anda berada di luar radius yang ditentukan!", kemudian ketika staff mencoba login kembali, tetap akan gagal dan muncul alert yang sama.

- Tambahkan fitur Dashboard untuk melihat summary dan statistik produk, penjualan, margin/markup, omzet, hutang, dan lain sebagainya yang sangat insightful. SIlahkan tambahkan menu Dashboard di Beranda. Aku ingin tahu dalam sebulan rata rata belanja berapa, omzetnya berapa, rata rata markup berapa, dan informasi lainnya. SIlahkan sajikan dalam bentuk card, chart, graph, atau apapun yang bisa bermanfaat untuk superadmin. FItur ini hanya tersedia untuk superadmin, tidak untuk staff.

- Lakukan commit dan push ke github https://github.com/abtohi/alfarezmart.git untuk semua perubahannya



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
