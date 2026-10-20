# ⏰ Background Cron Jobs — AlfarezMart

Direktori ini berisi skrip background jobs dan cron otomatis untuk AlfarezMart.

## Daftar Cron Job:

### 1. `cron_digiflazz.php`
* **Tujuan**: Sinkronisasi harga beli dan daftar produk PPOB (Pulsa, Paket Data, Token PLN, E-Wallet, BPJS, Game, dll) dari Digiflazz API.
* **Jadwal Rekomendasi**: Setiap hari pukul 01:00 dini hari (atau setiap 6 jam).
* **Contoh Crontab Linux**:
  ```bash
  0 1 * * * /usr/bin/php /home/u573283697/public_html/app/cron/cron_digiflazz.php >> /home/u573283697/public_html/logs/cron_digiflazz.log 2>&1
  ```

### 2. `cron_reksadana.php`
* **Tujuan**: Memperbarui Nilai Aktiva Bersih (NAB) live reksadana, menghitung total aset toko/tabungan, dan merekam log histori portofolio.
* **Jadwal Rekomendasi**: Setiap hari pukul 22:00 WIB (setelah bursa reksadana merilis NAB harian).
* **Contoh Crontab Linux**:
  ```bash
  0 22 * * * /usr/bin/php /home/u573283697/public_html/app/cron/cron_reksadana.php >> /home/u573283697/public_html/logs/cron_reksadana.log 2>&1
  ```
