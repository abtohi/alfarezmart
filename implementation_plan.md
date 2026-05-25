# Implementasi Geofencing (Akses Lokasi) untuk Staff

Fitur ini akan membatasi akses akun dengan level `staff` agar hanya bisa mengakses aplikasi jika berada di dalam radius tertentu dari titik pusat (toko) yang telah ditentukan oleh Admin, serta mewajibkan koneksi internet aktif.

## User Review Required

> [!WARNING]
> **Akurasi GPS Perangkat:** Radius **10 meter** sangat ketat. GPS pada smartphone biasa (terutama di dalam ruangan) sering kali memiliki margin error 15-20 meter. Jika diset 10 meter, Staff mungkin akan sering ter-logout meskipun mereka berada di dalam toko. Saya menyarankan radius *default* **20-30 meter**, namun Admin tetap bisa mengubahnya di halaman Pengaturan. Apakah Anda setuju dengan pendekatan ini?

## Open Questions

> [!IMPORTANT]
> 1. **Metode *Bypass*:** Apakah Admin atau Owner akan dikecualikan sepenuhnya dari pengecekan lokasi ini? (Saya berasumsi: Ya, hanya `staff` yang dicek).
> 2. **Perizinan Lokasi:** Jika Staff menolak memberikan izin akses lokasi (Location Permission) di browser mereka, apakah mereka akan langsung diblokir/logout? (Saya berasumsi: Ya, wajib diizinkan).

## Proposed Changes

---

### Database & Settings (Pengaturan Aplikasi)
Menambahkan konfigurasi baru di database `app_settings` untuk menyimpan koordinat toko dan radius.

#### [MODIFY] `app/controllers/SettingController.php`
- Menambahkan *logic* untuk mengambil dan menyimpan `store_latitude`, `store_longitude`, dan `store_radius_meters`.

#### [MODIFY] `app/views/settings/app.php`
- Menambahkan form input untuk **Latitude Toko**, **Longitude Toko**, dan **Radius Akses (meter)** di tab Pengaturan Aplikasi.
- Menambahkan tombol "Dapatkan Lokasi Saat Ini" agar Admin mudah mengatur titik pusat toko saat berada di lokasi.

---

### Geofencing Logic & Client-side Script
Membuat script yang akan berjalan di latar belakang untuk terus memantau lokasi Staff.

#### [NEW] `public/js/geofencing.js`
Script baru khusus untuk menangani:
1. Pengecekan `navigator.onLine` (Offline/Online). Jika *offline*, akan menampilkan layar blokir atau otomatis logout.
2. Meminta izin `navigator.geolocation`.
3. Menghitung jarak (Haversine Formula) antara posisi Staff dan titik koordinat Toko.
4. Jika jarak melebihi radius, secara otomatis mengalihkan pengguna ke halaman *logout* dengan pesan peringatan.

#### [MODIFY] `app/views/layouts/app.php`
- Memasukkan `geofencing.js` ke dalam layout utama.
- Menyisipkan variabel global ke JavaScript yang berisi `user_role`, `store_latitude`, `store_longitude`, dan `store_radius_meters` (hanya jika user yang login adalah `staff`).

---

### Backend Security
Mencegah *bypass* sederhana.

#### [MODIFY] `app/controllers/AuthController.php`
- Menambahkan penanganan pesan error spesifik saat *logout* paksa (misal: "Anda ter-logout karena berada di luar area toko").

## Verification Plan

### Automated/Manual Verification
1. **Test Pengaturan:** Login sebagai Admin, ubah koordinat lokasi toko dan atur radius menjadi 50m.
2. **Test Staff (Di dalam radius):** Login sebagai Staff menggunakan koordinat buatan (mock location) yang ada di dalam radius. Pastikan aplikasi dapat digunakan.
3. **Test Staff (Di luar radius):** Ubah *mock location* ke luar radius. Pastikan sistem mendeteksi dan melakukan auto-logout.
4. **Test Offline:** Matikan koneksi internet saat login sebagai Staff. Pastikan aplikasi langsung memblokir akses dan melakukan *logout* saat gagal mendeteksi lokasi atau internet.
5. **Test Permission Denied:** Tolak izin lokasi di browser, pastikan sistem menolak akses untuk Staff.
