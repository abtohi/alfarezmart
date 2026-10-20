# 🛠️ Utility & Maintenance Scripts — AlfarezMart

Direktori ini berisi skrip pemeliharaan sistem, sinkronisasi offline, dan perkakas teknis.

## Daftar Skrip:

### 1. `sync_to_sqlite.php`
* **Tujuan**: Mengkloning dan menyinkronkan data master dari MySQL (server) ke database lokal SQLite (`storage/database/alfarezmart.sqlite`).
* **Kegunaan**: Memastikan kasir dapat mencari produk dan memproses penjualan offline meskipun koneksi internet terputus atau MySQL maintenance.
* **Cara Eksekusi**:
  ```bash
  php scripts/sync_to_sqlite.php
  ```
