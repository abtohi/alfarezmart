# ⚡ PANDUAN & ATURAN BAKU PERFORMA APLIKASI (MOBILE & WEB)
**AlfarezMart High-Performance Architecture Guidelines**

> **TUJUAN UTAMA:**
> Aplikasi harus berjalan **sangat cepat, realtime, instan (<50ms navigasi)**, dan tangguh di segala kondisi jaringan (Offline, Sinyal Lemah 2G/3G, maupun Sinyal Kuat 4G/WiFi).

---

## 🛑 1. ATURAN SERVICE WORKER & NAVIGASI (`sw.js`)
Setiap kali ada pembaruan pada `sw.js` atau routing, **DILARANG KERAS** menggunakan strategi *Network-Only* atau *Network-First tanpa Fast-Race Timeout* untuk navigasi HTML/Menu.

1. **Fast Network Race (Maksimal 350ms)**:
   - Untuk setiap request navigasi halaman (HTML):
     Jika halaman sudah pernah dibuka dan tersimpan di cache, Service Worker **wajib** memberikan batas tunggu network maksimal **350ms**.
     Jika network tidak merespons dalam 350ms (akibat sinyal HP lemah / server sibuk), **LANGSUNG sajikan dari cache lokal (0-20ms instant response)**, lalu lakukan background revalidation.
2. **Static Assets (CSS, JS, Fonts, Images) = Cache-First Murni**:
   - Seluruh asset statis (Bootstrap, icons, fonts, stylesheet, script) harus dilayani dari cache lokal dalam 0ms.
3. **Versi Cache (Cache Buster)**:
   - Setiap perubahan besar pada JS/CSS/HTML, naikkan `APP_VERSION` di `app/views/layouts/app.php` dan `CACHE_NAME` di `sw.js` agar client melakukan self-healing update secara mulus.

---

## ⚡ 2. ATURAN PENCARIAN PRODUK REALTIME (INSTANT ZERO-LATENCY)
1. **In-Memory RAM Cache First**:
   - Pencarian produk di mobile dan POS wajib menggunakan In-Memory Cache (`_inMemoryProductsCache` di `public/js/db.js`).
   - Hasil pencarian lokal harus muncul **dalam 0–2 milidetik** saat pengguna mengetik huruf demi huruf.
2. **Non-Blocking Background Server Sync**:
   - Pencarian ke server (`/api/products/search`) hanya berfungsi sebagai pelengkap data baru dan **tidak boleh memblokir (freeze)** input atau hasil tampilan lokal.
   - Jika sinyal lemah atau offline, pencarian lokal langsung difinalkan tanpa menunggu fetch server.

---

## 🚀 3. ATURAN PREFETCHING & ELIMINASI TOUCH DELAY (`instant-nav.js`)
1. **Instant Touch Intent**:
   - Aplikasi menggunakan `instant-nav.js` yang otomatis mem-prefetch halaman tujuan saat jari menyentuh menu (`touchstart` / `pointerdown`).
   - Ketika jari diangkat (`click`), halaman sudah 100% siap di memori sehingga perpindahan antar menu terasa seperti aplikasi native tanpa jeda putih.
2. **Critical Bottom Nav Pre-caching**:
   - Link navigasi utama (POS, Produk, Pembelian, PPOB, Keuangan, Laporan) otomatis di-precache beberapa detik setelah aplikasi dibuka pertama kali.

---

## 🗄️ 4. ATURAN KONEKSI DATABASE & FAST FALLBACK (`Database.php`)
1. **Connection Timeout Ringan**:
   - `PDO::ATTR_TIMEOUT` pada MySQL remote tidak boleh lebih dari **2–3 detik**.
   - Jika koneksi MySQL remote mengalami gangguan, sistem harus langsung fallback ke SQLite offline lokal tanpa membuat pengguna menunggu lama.
2. **Index Kolom Database**:
   - Semua kolom yang sering di-filter atau di-join (`category`, `brand`, `buyer_sku_code`, `status`, `customer_no`, `created_at`) wajib memiliki database index.

---

## 📋 5. CHECKLIST SEBELUM MERILIS UPDATE
Sebelum melakukan commit / update fitur baru di AlfarezMart, pastikan:
- [ ] Navigasi antar menu di HP tetap instan (<50ms).
- [ ] Tidak ada synchronous network call yang memblokir render UI.
- [ ] File `public/js/instant-nav.js` tetap aktif dimuat di layout utama.
- [ ] Service Worker versi terbaru aktif dan melayani asset secara Cache-First.
- [ ] Pencarian produk bekerja instan baik online maupun offline.

---
*Dokumen ini adalah standar resmi optimasi kecepatan AlfarezMart.*
