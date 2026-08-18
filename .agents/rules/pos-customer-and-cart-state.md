# 🔒 ATURAN WAJIB: Preservasi State Pelanggan & Keranjang Kasir POS (JANGAN DIUBAH)

> **PERINGATAN KERAS**: Aturan ini mengatur persistensi data di halaman Kasir POS (`app/views/sales/pos.php`). **DILARANG KERAS** mengubah atau menghilangkan mekanisme penyimpanan dan pemulihan state ini saat melakukan upgrade atau refactoring fitur.

---

## 1. Aturan Persistensi Pemilihan Pelanggan (Customer State)

Ketika kasir telah memilih seorang pelanggan (bukan "Pelanggan Umum"):

### ⚠️ Larangan & Kewajiban:
1. **Preservasi Lintas Halaman (Cross-Page Navigation)**:
   - Pelanggan yang telah dipilih **HARUS TETAP TERPILIH** meskipun kasir berpindah ke halaman lain (misal: Halaman Produk, Cek Harga/Scan, Riwayat Penjualan, Dashboard, dll.) lalu kembali ke halaman Kasir POS.
   - ❌ **DILARANG KERAS** mereset pelanggan kembali ke "Pelanggan Umum" saat berpindah halaman atau me-refresh halaman.
2. **Mekanisme Auto-Save State**:
   - Fungsi `autoSaveCart()` **WAJIB** menyimpan object `selectedCustomer` ke dalam `localStorage` (`pos_autosave`) bersama dengan `cart`, `saleMode`, dan `mixDefaultPrice`.
   - Fungsi `selectCustomer()` dan `clearCustomer()` **WAJIB** memanggil `autoSaveCart()` secara instan setiap kali terjadi perubahan pemilihan pelanggan.
3. **Mekanisme Auto-Restore State**:
   - Fungsi `autoRestoreCart()` saat inisialisasi halaman Kasir POS (`DOMContentLoaded`) **WAJIB** membaca `saved.customer` dan memulihkan `selectedCustomer` serta memperbarui label UI (`customerSelectorLabel`, icon, tombol clear).
   - Pemulihan pelanggan harus tetap berjalan meskipun keranjang belanja masih kosong (0 item).
4. **Draft Penjualan**:
   - Saat kasir menyimpan Draft (`saveDraft()`), object `selectedCustomer` **WAJIB** ikut disimpan ke dalam data draft.
   - Saat draft dimuat (`loadDraft()`), pelanggan dari draft tersebut **WAJIB** dipulihkan ke kasir.
   - Modal daftar draft (`openDrafts()`) harus menampilkan nama pelanggan jika draft memiliki pelanggan terkait.

---

## 2. Kapan Pelanggan Boleh Direset ke "Pelanggan Umum"?

Pelanggan **HANYA BOLEH** direset ke `null` ("Pelanggan Umum") pada 3 kondisi berikut:
1. **Transaksi Selesai (Checkout Berhasil)**: Setelah pembayaran berhasil diproses dan invoice dibuat.
2. **Kasir Mengklik Tombol Clear Pelanggan / Memilih "Pelanggan Umum"** di dropdown.
3. **Kasir Mengosongkan Keranjang Secara Eksplisit** via modal konfirmasi "Kosongkan Keranjang" (`clearCartConfirm()`).

---

## 3. Ringkasan File yang Dilindungi

| File | Komponen Kritis yang Dilindungi |
|------|---------------------------------|
| [`app/views/sales/pos.php`](file:///c:/xampp/htdocs/AlfarezMart/app/views/sales/pos.php) | `selectedCustomer`, `autoSaveCart()`, `autoRestoreCart()`, `selectCustomer()`, `clearCustomer()`, `saveDraft()`, `loadDraft()`, `clearCartConfirm()` |

---

*Aturan ini dibuat pada 18 Agustus 2026 dan berlaku permanen di seluruh sesi pengembangan.*
