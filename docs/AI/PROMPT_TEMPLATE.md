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

- Pada halaman Produk, form untuk search produk saat ini masih belum support search untuk barcode, hanya support untuk nama produk, aku ingin kamu membuat form search produk bisa support untuk search barcode juga, sehingga ketika user memasukan barcode pada form search produk, maka akan muncul produk yang sesuai dengan barcode tersebut. Selain itu, tolong juga modifikasi algoritma pencarian produk di form ini, Misal ada produk bernama "Chocolatos Wafer Roll Dark 7g", aku mau ketika user ketik Chocolatos Dark juga akan direkomendasikan produk "Chocolatos Wafer Roll Dark 7g" ini, dan jika user mengetik Chocolatos Wafer juga akan direkomendasikan produk "Chocolatos Wafer Roll Dark 7g" ini, dan jika user mengetik Chocolatos juga akan direkomendasikan produk "Chocolatos Wafer Roll Dark 7g" ini. Pastikan algoritma ini bisa bekerja dengan baik dan cepat, dan tidak mengganggu performa aplikasi. Dan pastikan juga algoritma ini bisa bekerja dengan baik di mobile app, dan tidak mengganggu pengalaman pengguna. Termasuk jika user mengetik Chocolatos 7g, juga akan direkomendasikan produk ini dan beberapa produk lain dari chocolatos yang 7g, intinya form search ini akan menampilkan setiap kata yang paling banyak mengandung huruf yang diketik oleh user. Tolong implementasikan ini, selain bisa mencari dengan barcode dan dengan nama produk.

- Pada halaman https://alfarezmart.com/scanner, aku juga mau kamu mengimplementasikan algoritma yang sama dengan halaman produk, dan juga support untuk search barcode dan nama produk, yakni ketika user ketik Chocolatos Dark juga akan direkomendasikan produk "Chocolatos Wafer Roll Dark 7g" ini, dan jika user mengetik Chocolatos Wafer juga akan direkomendasikan produk "Chocolatos Wafer Roll Dark 7g" ini, dan jika user mengetik Chocolatos juga akan direkomendasikan produk "Chocolatos Wafer Roll Dark 7g" ini. Pastikan algoritma ini bisa bekerja dengan baik dan cepat, dan tidak mengganggu performa aplikasi. Dan pastikan juga algoritma ini bisa bekerja dengan baik di mobile app, dan tidak mengganggu pengalaman pengguna. Termasuk jika user mengetik Chocolatos 7g, juga akan direkomendasikan produk ini dan beberapa produk lain dari chocolatos yang 7g, intinya form search ini akan menampilkan setiap kata yang paling banyak mengandung huruf yang diketik oleh user. Tolong implementasikan ini, selain bisa mencari dengan barcode dan dengan nama produk. Kemudian di halaman ini juga pastikan ketika produk ditemukan, juga disertakan informasi harga modal, harga jual ecer dan grosir setiap kemasan, tuliskan juga informasi kemasannya apa, misal pcs, kemudian diikuti harga ecer dan grosirnya berapa, kemudian kemasan berikutnya misal pack, diikuti harga ecer dan grosirnya berapa, dan seterusnya. Intinya halaman ini akan menjadi fitur pencarian harga paling lengkap informasinya mengenai produk yang dicari, dan memudahkan user untuk mengetahui detail informasi produk dengan tampilan yang tetap minimalis dan user friendly menyesuaikan tema website.


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
