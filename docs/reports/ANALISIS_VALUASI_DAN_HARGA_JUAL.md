# 📊 Analisis Valuasi & Perkiraan Harga Jual Aplikasi AlfarezMart

Dokumen ini menyajikan analisis komprehensif mengenai estimasi nilai pasar (*valuation*), biaya rekonstruksi pengembangan (*cost-to-build*), dan strategi penetapan harga jual (*pricing strategy*) untuk aplikasi **AlfarezMart**, baik untuk target pasar **Perusahaan (B2B / Korporasi / Koperasi)** maupun **Perseorangan (Pemilik Toko Retail / UMKM)**.

---

## 📌 1. Ringkasan Eksekutif (Executive Summary)

| Target Pembeli | Skema Penjualan | Rentang Valuasi / Harga Wajar | Rekomendasi Harga Ideal |
| :--- | :--- | :--- | :--- |
| **Perusahaan / Investor** | **Full IP & Source Code Buyout** *(Jual Putus Kepemilikan Penuh)* | **Rp 85.000.000 – Rp 250.000.000** | **Rp 125.000.000 – Rp 175.000.000** |
| **Perusahaan / Jaringan Toko** | **Enterprise Multi-Branch License** *(Lisensi Server Mandiri 5–15 Cabang)* | **Rp 25.000.000 – Rp 60.000.000** *(+ Maintenance 15%/thn)* | **Rp 35.000.000** setup awal + **Rp 6.000.000**/thn |
| **Perseorangan / UMKM** | **One-Time Buyout / Lifetime License** *(Per Toko Standalone)* | **Rp 2.500.000 – Rp 7.500.000** *(Software Only)* | **Rp 3.500.000 – Rp 5.000.000** / toko |
| **Perseorangan / UMKM** | **Bundling Paket Siap Pakai** *(Software + Mini PC/Tablet + Printer Thermal)* | **Rp 6.500.000 – Rp 12.000.000** | **Rp 7.500.000 – Rp 8.900.000** / paket |
| **Perseorangan / UMKM** | **Model Langganan Cloud SaaS** *(Bulanan / Tahunan)* | **Rp 99.000 – Rp 250.000** / bulan | **Rp 149.000**/bln atau **Rp 1.500.000**/thn |

---

## 🛠️ 2. Parameter Teknis & Skala Aset Codebase

Berdasarkan audit langsung pada repositori sistem per September 2026:

- **Total Volume Kode**: **~95.299 baris kode (Lines of Code / LOC)** mencakup PHP, JavaScript, CSS, dan SQL.
- **Riwayat Pengembangan**: **> 1.050 commits**, mencerminkan tingkat iterasi, penyempurnaan bug, penyesuaian lapangan, dan stabilitas yang matang.
- **Struktur Sistem**:
  - **20 Controller** aktif (POS, Digiflazz PPOB, Savings/Reksa Dana, AI Chat, Keuangan, dsb.)
  - **19 Model** basis data relasional kompleks
  - **4 Service Engine** khusus (`AiSkillEngine`, `AiContextBuilder`, `MutualFundService`, `DigiflazzService`)
  - **22 Modul View** antarmuka responsif (Mobile-first & Desktop POS)
- **Teknologi & Dependensi**:
  - Arsitektur PHP MVC *pure native* (ultra-cepat, hemat RAM, tidak bergantung pada framework bloated).
  - PWA (*Progressive Web App*) & Service Worker untuk kapabilitas *offline-first*.
  - Integrasi hardware ESC/POS Thermal Bluetooth & Web AirPrint native tanpa perlu aplikasi driver pihak ketiga berbayar.
  - Integrasi API Payment & Produk Digital (*Digiflazz PPOB*).
  - Integrasi *Artificial Intelligence (AI)* context builder untuk asisten bisnis.

---

## 💰 3. Metode 1: Pendekatan Biaya Pengembangan Ulang (*Cost-to-Build / Replacement Value*)

Metode ini mengukur berapa biaya yang harus dikeluarkan sebuah perusahaan jika mereka menyewa agensi / software house profesional untuk membangun aplikasi dengan fitur dan stabilitas yang setara dari nol (*from scratch*).

### Rincian Alokasi Jam Kerja:
1. **Analisis Bisnis Retail & Database Architecture**: ~120 jam
2. **Pengembangan Core POS & Multi-satuan Kemasan (Dus/Pak/Pcs + Tier Pricing)**: ~240 jam
3. **Integrasi Hardware Thermal Bluetooth & Offline Caching**: ~100 jam
4. **Modul Pembelian, Supplier, Hutang/Piutang & Estimasi Order**: ~180 jam
5. **Integrasi PPOB Digiflazz (Webhook, Saldo, Margin Markup Otomatis)**: ~150 jam
6. **Modul Tabungan & Reksa Dana Portofolio**: ~130 jam
7. **Modul AI Assistant & Knowledge Context Engine**: ~110 jam
8. **Pengujian Stabilitas, UI/UX Refinement & Mobile Optimization**: ~180 jam
9. **Total Estimasi Jam Kerja**: **~1.210 jam** (setara 7–8 bulan kerja tim fullstack)

### Perhitungan Finansial:
- Standar honor Senior Fullstack Developer + UI/UX di Indonesia: **Rp 150.000 – Rp 250.000 / jam** (rata-rata konservatif: Rp 175.000/jam).
- Biaya Tenaga Kerja: `1.210 jam × Rp 175.000 = Rp 211.750.000`
- Biaya Manajemen Proyek, Testing & Server Testing: `Rp 25.000.000`
- **Total Biaya Rekonstruksi (*Replacement Value*)**: **Rp 236.750.000**

> 💡 **Kesimpulan Nilai Aset**: Dari kacamata *cost-to-build*, menjual kode sumber (*source code*) di harga **Rp 100jt – Rp 175jt** sudah sangat menarik bagi korporasi karena mereka menghemat waktu 8 bulan dan memotong biaya hingga 35–50%.

---

## 🏢 4. Penjualan ke Perusahaan / Korporasi (Skema B2B)

### A. Opsi Jual Putus Hak Cipta (*Full IP & Source Code Buyout*)
Target: Perusahaan ritel berjejaring, Software House yang ingin punya produk POS instan, Koperasi besar, atau Investor startup.
- **Perkiraan Nilai**: **Rp 85.000.000 – Rp 250.000.000**
- **Apa yang diserahkan**:
  - 100% kepemilikan kode sumber (Git repository).
  - Skema database lengkap + seeder.
  - Hak komersial bebas untuk dijual ulang, di-rebrand (*white-label*), atau dimodifikasi tanpa batas.
  - Dokumentasi teknis & transfer knowledge (1–2 minggu sesi handover).
- **Kondisi Valuasi Tertinggi (Bisa mencapai Rp 200jt+)**:
  - Jika penjualan menyertakan basis pengguna aktif (toko yang sudah berjalan riil).
  - Jika menyertakan kontrak dukungan teknis selama masa transisi.

### B. Opsi Lisensi Multi-Cabang Enterprise (*On-Premise / Private Cloud*)
Target: Pemilik usaha ritel yang memiliki 3 hingga 20 cabang toko/minimarket, atau distributor FMCG.
- **Harga Setup Awal**: **Rp 25.000.000 – Rp 50.000.000** (mencakup instalasi VPS private, konfigurasi jaringan, training karyawan).
- **Annual Maintenance / SLA (Dukungan & Update)**: **15% – 20% per tahun** (~Rp 5.000.000 – Rp 9.000.000 / tahun).
- **Keuntungan bagi Penjual**: Hak cipta tetap milik Anda, Anda memiliki *recurring income* tahunan yang pasti.

---

## 👤 5. Penjualan ke Perseorangan / Retailer UMKM

Untuk toko kelontong modern, minimarket mandiri, apotek, toko sembako, atau warung grosir:

### Skema 1: Jual Putus Per Toko (*Lifetime Single Store License*)
- **Harga Wajar Software**: **Rp 2.500.000 – Rp 5.000.000** (sekali bayar seumur hidup, offline/lokal di toko).
- **Layanan Tambahan**:
  - Biaya instalasi & pendampingan input produk awal: **Rp 500.000 – Rp 1.500.000**.
  - Garansi bug & support teknis gratis 3–6 bulan pertama.

### Skema 2: Paket Lengkap Bundling Hardware (*Turnkey POS Solution*)
Pemilik toko perseorangan umumnya tidak mau repot instalasi software sendiri. Mereka lebih suka paket siap pakai yang langsung colok listrik dan jalan.
- **Isi Paket**:
  1. Software AlfarezMart (terinstal permanen).
  2. Mini PC / Touchscreen POS Terminal / Tablet Android.
  3. Barcode Scanner Wireless / Omnidirectional (dudukan meja).
  4. Printer Thermal 58mm / 80mm Bluetooth + USB.
  5. Laci Kasir (*Cash Drawer*) otomatis RJ11.
- **Biaya Modal Hardware**: ~Rp 4.000.000 – Rp 5.200.000
- **Harga Jual Paket ke Pembeli**: **Rp 7.900.000 – Rp 11.500.000**
- **Margin Keuntungan Bersih Software**: **Rp 3.500.000 – Rp 5.500.000 per paket!**

### Skema 3: Model SaaS Berlangganan (*Subscription Cloud*)
Jika aplikasi di-deploy di cloud server terpusat dengan multi-tenancy:
- **Paket Starter**: **Rp 99.000 / bulan** (Maksimal 1 kasir, tanpa PPOB & AI).
- **Paket Pro (Komplit)**: **Rp 179.000 – Rp 249.000 / bulan** (Semua fitur: Multi-kemasan, Grosir/Ecer, PPOB Digiflazz, AI Chat, Cetak Struk Bluetooth).
- **Potensi Finansial**:
  - 30 Toko Pelanggan = `30 × Rp 179.000 = Rp 5.370.000 / bulan` (**Rp 64.440.000 / tahun**).
  - 100 Toko Pelanggan = `100 × Rp 179.000 = Rp 17.900.000 / bulan` (**Rp 214.800.000 / tahun**).

---

## 🌟 6. Keunggulan Kompetitif AlfarezMart (Faktor Penaik Valuasi)

Mengapa AlfarezMart bernilai lebih tinggi daripada template POS biasa yang beredar di internet?

| Fitur AlfarezMart | Aplikasi POS Pasaran Umum | Dampak terhadap Nilai Jual |
| :--- | :--- | :--- |
| **Sistem Multi-Kemasan Bertingkat (Level 1–4)** | Mayoritas hanya 1 satuan atau varian flat | **Sangat Tinggi**: Solusi mutlak untuk toko grosir & sembako (Dus ➔ Renceng ➔ Pcs). |
| **Sistem Harga Grosir, Ecer & Mix Mode Fleksibel** | Harga kaku atau butuh voucher manual | **Tinggi**: Kasir bisa langsung ganti mode harga per item saat transaksi berjalan. |
| **Modul PPOB Terintegrasi (Digiflazz)** | Tidak ada (harus pakai aplikasi agen terpisah) | **Tinggi**: Toko bisa dapat cuan tambahan dari jualan pulsa, token, PDAM dari 1 layar. |
| **Thermal Printer Bluetooth Native Web** | Butuh instalasi RawBT / aplikasi bridging rumit | **Sangat Tinggi**: Bisa langsung cetak dari browser Chrome HP/PC via Bluetooth. |
| **AI Assistant & Context Engine** | Hampir tidak ada di POS konvensional | **Nilai Jual Modern**: Daya tarik inovasi teknologi tinggi di mata investor/pembeli. |
| **Ringan & Bisa Dijalankan Offline (PWA)** | Wajib internet 100% (kalau internet mati, toko lumpuh) | **Krusial**: Toko tetap bisa transaksi meski koneksi internet terputus. |
| **Struk Berantai (*Invoice Continuation*)** | Tidak ada | **Sangat Solutif**: Mengatasi pembeli yang nambah belanjaan setelah struk tercetak. |

---

## 📈 7. Rekomendasi Strategi Penjualan & Negosiasi

1. **Jika ingin cepat mendapatkan uang tunai (*Cashflow Cepat*)**:
   - Tawarkan **Paket Bundling Hardware + Software ke 3–5 toko ritel terdekat**: Potensi penghasilan bersih **Rp 15.000.000 – Rp 25.000.000** dalam waktu 1–2 bulan.
2. **Jika ingin pendapatan pasif jangka panjang (*Recurring Revenue*)**:
   - Jangan jual putus source code. Terapkan sistem sewa lisensi tahunan (**Rp 1,5 jt – Rp 2 jt/tahun per toko**) atau skema bagi hasil fee transaksi PPOB.
3. **Jika ada Perusahaan / Investor yang tertarik membeli seluruh sistem (*Exit / Acquisition*)**:
   - Buka harga penawaran awal di **Rp 175.000.000 – Rp 200.000.000**.
   - Batas negosiasi bawah (*floor price*) yang disarankan: **Rp 95.000.000 – Rp 110.000.000**. Jangan lepas di bawah Rp 80.000.000 karena kompleksitas ~95.000 baris kode dan 1.050+ commit jauh melampaui nilai tersebut.
