# Rencana Implementasi: Perbaikan Form Produk, Pop-up Konfirmasi Form Kotor, & Pencocokan Kode Barang AI

Rencana ini dibuat untuk memperbaiki tiga kendala utama di aplikasi AlfarezMart.

## User Review Required

> [!IMPORTANT]
> 1. **Auto-check Toggles Harga Custom pada Mode Referensi (Edit Produk):** Saat menggunakan produk referensi di halaman Edit Produk, kita akan otomatis mencentang pilihan "Harga Modal Custom" dan "Harga Jual Custom". Hal ini untuk mencegah harga yang disalin dari produk referensi ter-overwrite secara tidak sengaja oleh perhitungan otomatis Level 1.
> 2. **Konfirmasi Form Kotor Global:** Fitur pendeteksi perubahan form akan mendeteksi event `input` pada seluruh elemen `<form>` di aplikasi (kecuali elemen dengan class `no-track`). Jika user mencoba keluar halaman (baik via klik link menu/back di web maupun browser refresh/close), modal konfirmasi dari `AppModal` (atau `confirm` bawaan browser sebagai fallback jika `AppModal` tidak terdefinisi) akan muncul untuk memperingatkan bahwa data yang diinput akan terhapus.

## Proposed Changes

---

### 1. Perbaikan Bug Duplikasi Kemasan di Edit Produk

Kita akan memastikan pengisian nilai input `contained_qty` untuk kemasan tambahan level 2 dan level 3 diset secara aman dan tidak memicu error HTML5 browser ("Isi Kemasan minimal 2"). Kita juga akan mengunci harga kustom agar tidak ter-overwrite oleh autokalkulasi.

#### [MODIFY] [edit.php](file:///c:/xampp/htdocs/AlfarezMart/app/views/products/edit.php)
- Pada fungsi `addPackagingLevel(prefill)`, kita akan secara eksplisit mengisi value element input `.contained-qty` dengan `prefill.contained_qty` jika `prefill` dikirimkan (sama seperti di `create.php`).
- Jika halaman dalam `referenceMode` (menggunakan produk referensi), kita akan secara otomatis mencentang checkbox `.chk-buy-custom` dan `.chk-sell-custom` agar harga referensi yang tersalin tidak ditimpa/diubah oleh sinkronisasi harga otomatis dari Level 1.

---

### 2. Penyelamatan Data & Konfirmasi Form Kotor (Unsaved Changes)

Mengembalikan dan merapikan mekanisme deteksi form kotor agar user tidak kehilangan inputan secara tidak sengaja saat menavigasi keluar halaman sebelum form disimpan.

#### [MODIFY] [app.js](file:///c:/xampp/htdocs/AlfarezMart/public/js/app.js)
- Menambahkan kembali kode pelacak perubahan form kotor (`window.hasUnsavedChanges`).
- Menambahkan global event listener `submit` pada document agar ketika form dikirimkan (baik via POST biasa maupun AJAX sukses), flag `window.hasUnsavedChanges` di-set menjadi `false` (mencegah popup muncul saat proses submit normal).
- Menambahkan intercept klik pada link navigasi (`a[href]`) untuk memunculkan popup konfirmasi jika form dalam kondisi kotor.
- Menambahkan beforeunload event listener untuk memperingatkan pengguna saat refresh/close/back browser.

---

### 3. Perbaikan Pencocokan Barang AI Invoice Scan

Mengatur urutan prioritas pencocokan produk agar memprioritaskan pencarian exact match menggunakan `supplier_product_code` (kode barang supplier) sebelum menggunakan pencocokan nama barang fuzzy.

#### [MODIFY] [ApiController.php](file:///c:/xampp/htdocs/AlfarezMart/app/controllers/ApiController.php)
- Melakukan `trim` pada `extractedCode` (`supplier_product_code` atau `supplier_code` dari AI) dan memastikan nilainya tidak kosong.
- Jika tidak kosong, lakukan pencarian loop pertama pada `$allProducts` untuk mencari kecocokan persis (`strcasecmp`) antara kode tersebut dengan `supplier_product_code` atau `code` produk di database.
- Jika ditemukan exact match, langsung pilih produk tersebut sebagai `bestMatch` (skor maksimum) dan lewati (skip) kalkulasi fuzzy nama produk.
- Jika tidak ditemukan exact match kode, lanjutkan dengan pencocokan nama fuzzy seperti biasa.

---

## Verification Plan

### Automated/Manual Verification
1. **Verifikasi Edit Produk (Mode Referensi):**
   - Buka halaman Edit Produk lama.
   - Centang "Tambah varian dari produk referensi".
   - Cari dan pilih produk referensi yang memiliki 3 level kemasan (Pcs, Renceng, Karton).
   - Pastikan isi kemasan terisi dengan benar (tidak kosong/error).
   - Simpan perubahan dan buka kembali produk tersebut. Pastikan kemasan tidak duplikat (tetap 3 kemasan: pcs, renceng, karton).
2. **Verifikasi Konfirmasi Form Kotor:**
   - Masuk ke form edit produk atau input barang.
   - Ketik sesuatu pada salah satu input form.
   - Coba klik menu lain (misal "Beranda" atau "POS"). Pastikan muncul modal konfirmasi "Konfirmasi Keluar".
   - Coba submit form secara normal. Pastikan form berhasil disimpan tanpa memicu modal konfirmasi.
3. **Verifikasi AI Invoice Scan:**
   - Coba lakukan scan invoice yang memiliki kode supplier (misalnya `[CMY-125]`).
   - Pastikan jika kode supplier tersebut cocok dengan `supplier_product_code` produk di database, produk tersebut langsung terpilih dengan akurasi 100% tanpa salah pencocokan nama.
