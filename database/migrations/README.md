# 🗄️ Database Migrations — AlfarezMart

Direktori ini berisi seluruh skrip migrasi dan patch skema database MySQL AlfarezMart.

## Petunjuk Penggunaan
Skrip migrasi bersifat *one-off* (atau *idempotent*) yang dijalankan untuk memperbarui skema database:
```bash
php database/migrations/<nama_migrasi>.php
```

## Daftar Skrip Migrasi
* **`migrate_custom_price.php`**: Penambahan kolom `is_custom_price` pada tabel `digi_products`.
* **`migrate_markup.php`**: Pembuatan tabel `digi_markup_rules` untuk pengaturan markup PPOB Digiflazz.
* **`migrate_ppob.php`**: Migrasi skema transaksi dan layanan PPOB.
* **`migrate_ppob_customers.php`**: Penambahan tabel pelanggan rutin PPOB.
* **`migrate_seller.php`**: Konfigurasi tabel seller/mitra toko.
* **`migrate_transaction_seller.php`**: Integrasi transaksi penjualan dengan data seller.
* **`migration_deposit.php`**: Pencatatan histori deposit saldo Digiflazz.
* **`run_digi_migration.php`**: Runner kolektif modul PPOB.
* **`migrate_custom_items.php`**: Dukungan item kustom non-master pada kasir.
* **`migrate_debt_sources.php`**: Penambahan sumber pencatatan hutang toko & piutang.
* **`migrate_finance_dynamic.php`**: Kategori dinamis keuangan dan kas toko.
* **`migrate_order_estimates.php`**: Estimasi pemesanan & restok barang.
* **`patch_user_schedule.php`**: Jadwal kerja karyawan & absensi kasir.

*Catatan: Inisialisasi awal skema database utama dikelola di `database/setup.php`.*
