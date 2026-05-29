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

- Pada halaman Scanner, saat ini belum bisa menggunakan fitur barcode, hanya bisa menggunakan fitur nama produk, tolong perbaiki agar ketika di scan dengan barcode juga bisa memunculkan produknya

- Pada halaman Tambah Produk Baru, saat ini belum ada form untuk menambahkan Harga Tier per Kuantitas, tolong tambahkan form tersebut dan dibuat sama persis modelnya seperti saat user Edit produk, silahkan merefer ke halaman Edit Produk untuk melihat Harga Tier.

- Pada halaman Input Barang, baik untuk Input Barang reguler maupun massal, saat ini form Harga Tier nya belum ada keterangan untuk Ecer Saja, Grosir Saja atau Ecer & Grosir seperti halnya yang ada di form Edit Produk, tolong tambahkan fitur ini agar ketika ada produk masuk dan ada harga tier, bisa langsung diinput oleh user. Dan apabila, di data sebelumnya, produk tersebut sudah ada harga tier yang ditentukan oleh user, tolong juga ditampilkan dan di load datanya di halaman Input Barang Reguler atau Input Barang Massal, sehingga user tidak perlu menginput kembali Harga Tier nya.

- Saat ini di halaman Input Barang Reguler atau Input Barang Massal, ketika user menginput harga tier, kemudian disimpan, harga tier nya tidak tersimpan, dan ketika saya cek detail produk, harga tier yang diinput di halaman Input Barang tidak masuk ke database produk, tolong cek dan perbaiki, pastikan lagi Harga Tiernya bisa tersimpan di data produk, selain itu pastikan Harga Tier juga bisa di load dan ditampilkan informasinya di halaman Input Barang, dan user bisa mengubahnya juga di halaman Input Barang.



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
