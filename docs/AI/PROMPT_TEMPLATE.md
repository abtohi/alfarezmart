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

[ ] Pada halaman riwayat barang masuk, saat salah satu riwayat di edit (di klik icon pencil), kemudian masuk ke halaman Edit Barang Masuk, nama produk menjadi undefined, seharusnya tampil nama produk sesuai di riwayat, tolong ini diperbaiki agar riwayat barang masuk bisa di edit
[ ] Pada halaman Kasir POS, saat user search produk, produk list yang ditampilkan font nya terlalu besar, dan namanya terpotong, aku mau kamu mengubah ukuran font agar lebih kecil, dan ketika nama produknya terlalu panjang jangan dibuat titik titik ... tetapi tampilkan secara full dan boleh dibuat menyambung ke bawahnya jika length melebihi border panel options. Selain itu saat search juga tolong tampilkan informasi detail harga perkemasan, jika user user memilih ecer, maka harga yang ditampilkan adalah harga ecer, jika memilih grosir maka harga yang ditampilkan yang grosir, sehingga user langsung tahu produk yang di search yang mana sebelum user mengklik / memilih produk.
[ ] Pada halaman kasir POS juga tolong tampilkan informasi Estimasi Profit, dibawah Total Belanja, namun hanya muncul ditampilan UI, jangan ikut ke cetak ke struk. Selain itu, di setiap produk yang terpilih, selain informasi harga produk, di samping kanan harga produk juga tampilkan profit (selisih harga modal dan harga jualnya) dalam bentuk shadow juga seperti tampilan harga modal, sehingga nantinya akan begini ex : M: Rp15.600/bks P:Rp1.400, untuk profit langsung dihitung profit berdasarkan kuantitasnya, misal perbungkus profitnya 1400, jika user menulis qty nya ada 2, maka profit akan tertulis 2800.
[ ] Pada halaman Pengaturan Sistem & AI, saat user memilih model lain selain Google Gemini 2.5 Flash, saat digunakan untuk scan AI otomatis masih gagal dan muncul tulisan endpoint tidak ditemukan, tolong ini diperbaiki agar semua model yang ada bisa dipakai, dan endpointnya bisa menyesuaikan
[ ] Pada halaman Pengaturan Sistem & AI, saat memilih model AI, tampilan UI modal nya masih jadul dan belum modern menyesuaikan tema aplikasi, tolong ini desainnya diperbaiki agar lebih elegan, modern dan sesuai dengan tema aplikasi. Samakan model panelnya seperti panel opsi lainnya di halaman ini agar selaras dengan layout aplikasi
[ ] Pada halaman Unduh Data Offline saat ini masih gagal, sebelumnya aku memintamu agar ketika mode offline, semua fitur tetap bisa digunakan, termasuk input Pemasukan dan Pengeluaran, input barang masuk, edit produk, tambah produk, hanya saja bedanya ketika offline, data tidak akan langsung sinkron ke server, melainkan akan tersimpan di lokal terlebih dahulu, karena fitur Unduh Offline ini akan mengunduh semua layout, UI, aplikasi front end, back end, framework, database, semua tabel yang ada, alert, animasi, kecuali yang tidak bisa digunakan hanya scan AI otomatis, selain itu fitur search juga seharusnya bisa berjalan menampilkan list dengan multi keywords. Saat ini saya saya klik Unduh Offline muncul error "Gagal mengunduh data offline: Failed to execute 'transaction' on 'IDBDatabase': One of the specified object stores was not found", tolong periksa dan perbaiki ini sampai tuntas.
[ ] Pada halaman Kasir POS, jika ada produk yang ada PPN nya, tolong jangan tampilkan PPNnya, cukup harga finalnya saja
[ ] Pada halaman Input Barang Masuk, saat ini setelah melalui proses scan AI, kemudian user mengubah Total Harga Pembelian, ketika user mengklik tombol Distribusikan ke Harga Modal Barang, kenapa Total Harga Pembelian berubah lagi menjadi harga yang sebelum saya edit (yang hasil scan AI), seharusnya tombol Distribusikan ke Harga Modal tidak akan mengubah data yang sudah di edit, tolong perbaiki ini
[ ] Pada halaman Edit Produk dan Tambah Produk, tolong untuk setiap harga modal di setiap kemasan, juga dipertimbangkan angka desimal, jangan langsung digenapkan ke bilangan bulat, baik hasil penggunaan kalkukator, harga perkalian atau pembagian ke jenis kemasan lain, jika memang ada desimal, tolong tampilkan desimalnya, maksimal 2 digit desimal, namun jika memang harga modalnya bilangan bulat, tolong desimalnya jangan ditampilkan.
[ ] Pada halaman Input Barang Masuk, untuk setiap panel produk, pada bagian tabel informasi yang berisi Kemasan, Modal Nett, Jual Ecer, Jual Grosir, di bagian informasi harga Jual Ecer dan Jual Grosir, selain dibawahnya ada informasi markup nya berapa persen, tolong juga tambahkan selisih harganya dengan harga modal, agar lebih informatif.
[ ] Tolong perbaiki semua error ini
[{
 "resource": "/c:/xampp/htdocs/AlfarezMart/app/views/settings/app.php",
 "owner": "_generated_diagnostic_collection_name_#4",
 "code": "P1008",
 "severity": 8,
 "message": "Undefined variable '$csrfToken'.",
 "source": "intelephense",
 "startLineNumber": 25,
 "startColumn": 52,
 "endLineNumber": 25,
 "endColumn": 62,
 "origin": "extHost1"
},{
 "resource": "/c:/xampp/htdocs/AlfarezMart/app/views/settings/receipt.php",
 "owner": "_generated_diagnostic_collection_name_#4",
 "code": "P1009",
 "severity": 8,
 "message": "Undefined type 'App\\Models\\SettingModel'.",
 "source": "intelephense",
 "startLineNumber": 3,
 "startColumn": 21,
 "endLineNumber": 3,
 "endColumn": 45,
 "origin": "extHost1"
},{
 "resource": "/c:/xampp/htdocs/AlfarezMart/app/views/settings/receipt.php",
 "owner": "_generated_diagnostic_collection_name_#4",
 "code": "P1008",
 "severity": 8,
 "message": "Undefined variable '$csrfToken'.",
 "source": "intelephense",
 "startLineNumber": 18,
 "startColumn": 52,
 "endLineNumber": 18,
 "endColumn": 62,
 "origin": "extHost1"
},{
 "resource": "/c:/xampp/htdocs/AlfarezMart/app/views/settings/receipt.php",
 "owner": "_generated_diagnostic_collection_name_#6",
 "code": "PHP0413",
 "severity": 4,
 "message": "Use of unknown class: 'App\\Models\\SettingModel'",
 "source": "PHP",
 "startLineNumber": 3,
 "startColumn": 21,
 "endLineNumber": 3,
 "endColumn": 45,
 "origin": "extHost1"
},{
 "resource": "/c:/xampp/htdocs/AlfarezMart/app/views/products/index.php",
 "owner": "_generated_diagnostic_collection_name_#4",
 "code": "vendorPrefix",
 "severity": 4,
 "message": "Also define the standard property 'appearance' for compatibility",
 "source": "css",
 "startLineNumber": 53,
 "startColumn": 226,
 "endLineNumber": 53,
 "endColumn": 241,
 "origin": "extHost1"
},{
 "resource": "/c:/xampp/htdocs/AlfarezMart/app/views/products/index.php",
 "owner": "_generated_diagnostic_collection_name_#4",
 "code": "vendorPrefix",
 "severity": 4,
 "message": "Also define the standard property 'appearance' for compatibility",
 "source": "css",
 "startLineNumber": 58,
 "startColumn": 226,
 "endLineNumber": 58,
 "endColumn": 241,
 "origin": "extHost1"
}]
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
