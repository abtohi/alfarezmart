<?php
/**
 * Help & Guide View - Redesigned & Role-Filtered (v1.1.6)
 * Dynamically hides non-eligible topics for Admin/Staff or disabled services.
 */
$currentUser  = AuthController::currentUser();
$userLevel    = strtolower((string)($currentUser['level'] ?? ''));
$isSuperadmin = $userLevel === 'superadmin';
$isAdmin      = $userLevel === 'admin';
$isStaff      = $userLevel === 'staff';
?>

<div class="page-section" style="padding-bottom:100px;">
    <!-- Modern Hero Header -->
    <div style="background: linear-gradient(135deg, var(--surface-1), var(--surface-2)); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px 20px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                    <span class="badge bg-primary bg-opacity-15 text-primary fw-bold" style="font-size:11px; padding:4px 10px; border-radius:20px; text-transform:uppercase;">
                        <i class="bi bi-person-badge-fill me-1"></i> Mode Akses: <?= ucfirst($userLevel ?: 'User') ?>
                    </span>
                    <span class="badge bg-success bg-opacity-15 text-success fw-bold" style="font-size:10px; padding:3px 8px; border-radius:12px;">
                        <i class="bi bi-shield-check me-1"></i> Terverifikasi
                    </span>
                </div>
                <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 6px; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-journal-bookmark-fill text-primary"></i> Pusat Bantuan &amp; Panduan Toko
                </h2>
                <p style="font-size: 12px; color: var(--text-muted); margin: 0; max-width: 620px; line-height: 1.5;">
                    Panduan operasional lengkap AlfarezMart PWA. Dokumentasi yang Anda lihat disesuaikan secara otomatis berdasarkan wewenang dan izin layanan untuk role <strong><?= ucfirst($userLevel) ?></strong>.
                </p>
            </div>
            
            <a href="<?= BASE_URL ?>chat" class="btn-primary-custom text-decoration-none" style="padding:10px 18px; font-size:12px; font-weight:700; border-radius:20px; display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg, #6366f1, #8b5cf6); border:none; box-shadow:0 4px 14px rgba(99,102,241,0.35);">
                <i class="bi bi-stars"></i> Tanya AI Assistant
            </a>
        </div>

        <!-- Live Search Bar -->
        <div style="margin-top: 18px; position: relative;">
            <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
            <input type="text" id="helpSearchInput" onkeyup="filterHelpTopics()" placeholder="Cari bantuan... (contoh: PPOB, Kasir, Struk, AI Scan, Restok, Margin, Supplier)" style="width: 100%; padding: 10px 14px 10px 38px; font-size: 12px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: var(--surface-1); color: var(--text-primary); outline: none;">
        </div>
    </div>

    <!-- Category Filter Chips -->
    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 20px; scrollbar-width: none;">
        <button onclick="filterHelpCategory('all')" class="help-cat-chip active" id="chip-all">
            <i class="bi bi-grid-fill me-1"></i> Semua Topik
        </button>
        <button onclick="filterHelpCategory('alur')" class="help-cat-chip" id="chip-alur">
            <i class="bi bi-diagram-3-fill me-1"></i> Alur Kerja
        </button>
        <button onclick="filterHelpCategory('fitur')" class="help-cat-chip" id="chip-fitur">
            <i class="bi bi-box-seam-fill me-1"></i> Panduan Fitur
        </button>
        <button onclick="filterHelpCategory('troubleshoot')" class="help-cat-chip" id="chip-troubleshoot">
            <i class="bi bi-wrench-adjustable-circle-fill me-1"></i> Troubleshooting
        </button>
        <button onclick="filterHelpCategory('pembaruan')" class="help-cat-chip" id="chip-pembaruan">
            <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Fitur Terbaru
        </button>
        <button onclick="filterHelpCategory('istilah')" class="help-cat-chip" id="chip-istilah">
            <i class="bi bi-book-fill me-1"></i> Istilah
        </button>
    </div>

    <!-- ======================================================= -->
    <!-- SECTION 1: ALUR KERJA SISTEM                            -->
    <!-- ======================================================= -->
    <div id="sec-alur" class="help-group-section" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:20px;">
        <h3 style="font-size:15px; font-weight:800; margin-bottom:14px; color:var(--primary); display:flex; align-items:center; gap:8px;">
            <i class="bi bi-diagram-3-fill"></i> Alur Kerja Operasional Sistem
        </h3>
        
        <div style="background:var(--surface-2); padding:16px; border-radius:var(--radius-md); font-size:13px; line-height:1.8; border:1px solid var(--border-color);">
            <!-- Flow Step 1: POS -->
            <div style="margin-bottom:12px; display:flex; align-items:flex-start; gap:10px;">
                <div style="width:26px; height:26px; border-radius:50%; background:#3b82f6; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; flex-shrink:0;">1</div>
                <div>
                    <strong style="color:var(--text-primary);">Penjualan Kasir POS &amp; Cetak Struk</strong><br>
                    Menu <strong>Scan / POS</strong> → Scan barcode atau cari nama produk → Pilih kemasan (Ecer/Grosir) → Masukkan ke keranjang → Tekan <strong>Bayar</strong> → Cetak Struk Thermal Bluetooth (58mm/80mm) atau Web Print. <em style="color:var(--text-muted);">(Stok otomatis berkurang real-time)</em>.
                </div>
            </div>

            <?php if ($this->hasServiceAccess('purchases')): ?>
            <!-- Flow Step 2: Purchases -->
            <div style="margin-bottom:12px; display:flex; align-items:flex-start; gap:10px;">
                <div style="width:26px; height:26px; border-radius:50%; background:#10b981; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; flex-shrink:0;">2</div>
                <div>
                    <strong style="color:var(--text-primary);">Barang Masuk &amp; AI Scan Invoice Belanja</strong><br>
                    Menu <strong>Barang Masuk (`/purchases/create`)</strong> → Pilih Supplier/Sales → Scan produk atau gunakan <strong>Input Bulk / AI Scan Invoice</strong> (Foto faktur belanja supplier) → Isi Qty &amp; Harga Beli → Masukkan PPN &amp; Diskon Nota → Simpan. <em style="color:var(--text-muted);">(Stok otomatis bertambah)</em>.
                </div>
            </div>
            <?php endif; ?>

            <?php if ($this->hasServiceAccess('ppob')): ?>
            <!-- Flow Step 3: PPOB -->
            <div style="margin-bottom:12px; display:flex; align-items:flex-start; gap:10px;">
                <div style="width:26px; height:26px; border-radius:50%; background:#a855f7; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; flex-shrink:0;">3</div>
                <div>
                    <strong style="color:var(--text-primary);">Transaksi Produk Digital PPOB</strong><br>
                    Menu <strong>Produk Digital (`/ppob`)</strong> → Pilih Kategori (Pulsa, Data, PLN, E-Wallet, Game) → Input Nomor Tujuan / Inquiry PLN → Pilih Produk → Konfirmasi Pembayaran → Transaksi diproses otomatis oleh Digiflazz.
                </div>
            </div>
            <?php endif; ?>

            <?php if ($this->hasServiceAccess('finance')): ?>
            <!-- Flow Step 4: Finance -->
            <div style="margin-bottom:12px; display:flex; align-items:flex-start; gap:10px;">
                <div style="width:26px; height:26px; border-radius:50%; background:#818cf8; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; flex-shrink:0;">4</div>
                <div>
                    <strong style="color:var(--text-primary);">Pencatatan Keuangan &amp; Dompet Toko</strong><br>
                    Menu <strong>Keuangan (`/finance`)</strong> → Catat pemasukan/pengeluaran toko harian → Pantau akumulasi kas bersih &amp; saldo dompet toko secara transparan.
                </div>
            </div>
            <?php endif; ?>

            <?php if ($isSuperadmin): ?>
            <!-- Flow Step 5: Superadmin Control -->
            <div style="display:flex; align-items:flex-start; gap:10px;">
                <div style="width:26px; height:26px; border-radius:50%; background:#e63946; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; flex-shrink:0;">5</div>
                <div>
                    <strong style="color:var(--text-primary);">Kontrol Akses User &amp; Pengaturan Sistem (Superadmin)</strong><br>
                    Menu <strong>Manajemen User (`/users`)</strong> → Tab <em>Kontrol Akses Layanan</em> → Aktifkan/Matikan saklar akses fitur khusus untuk role Admin &amp; Staff → Pengaturan Geofencing lokasi toko.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- SECTION 2: PANDUAN FITUR DETAIL                         -->
    <!-- ======================================================= -->
    <div id="sec-fitur" class="help-group-section" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:20px;">
        <h3 style="font-size:15px; font-weight:800; margin-bottom:14px; color:var(--success); display:flex; align-items:center; gap:8px;">
            <i class="bi bi-grid-3x3-gap-fill"></i> Panduan Fitur &amp; Modul Aplikasi
        </h3>

        <!-- 1. Kasir POS & Struk -->
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-cart-check-fill text-success me-2"></i> Kasir POS &amp; Cetak Struk Thermal</span>
                <span class="badge bg-success bg-opacity-10 text-success ms-auto" style="font-size:10px;">POS</span>
            </summary>
            <div class="help-item-body">
                • <strong>Mode Ecer &amp; Grosir:</strong> Pilih mode Ecer atau Grosir pada bagian atas layar POS. Harga produk di keranjang otomatis menyesuaikan.<br>
                • <strong>Scan Barcode:</strong> Gunakan kamera HP/tablet atau scanner Bluetooth fisik. Barcode langsung mengenali level kemasan produk (Pcs/Pack/Karton).<br>
                • <strong>Cari Produk Auto-Suggest:</strong> Ketik minimal 2 karakter pada kolom cari, hasil pencarian otomatis muncul secara *real-time* tanpa perlu menekan Enter.<br>
                • <strong>Simpan Draft Keranjang:</strong> Tekan tombol Draft untuk menyimpan transaksi sementara jika pelanggan ingin mengambil barang tambahan.<br>
                • <strong>Harga Custom POS:</strong> Centang opsi "Harga Custom" di kasir jika ingin mengubah total harga jual per transaksi secara fleksibel.<br>
                • <strong>Cetak Struk Thermal Bluetooth:</strong> Hubungkan printer thermal Bluetooth (58mm/80mm) via Web Bluetooth API (Android Chrome/Edge). Sekali terhubung, printer diingat otomatis.<br>
                • <strong>Cetak AirPrint / Web Print:</strong> Dukungan penuh untuk perangkat iOS / AirPrint dan cetak dokumen PDF browser.
            </div>
        </details>

        <!-- 2. Produk & Multivariant -->
        <?php if ($this->hasServiceAccess('products')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-box-seam-fill text-primary me-2"></i> Manajemen Produk &amp; Harga Multivarian</span>
                <span class="badge bg-primary bg-opacity-10 text-primary ms-auto" style="font-size:10px;">Stok</span>
            </summary>
            <div class="help-item-body">
                • <strong>Kemasan Multi-Level:</strong> Pengaturan 3 level kemasan: Level 1 (Satuan Terkecil / Pcs), Level 2 (Menengah / Pack), Level 3 (Terbesar / Karton). Setiap level memiliki barcode dan harga jual sendiri.<br>
                • <strong>Harga Bertingkat (Qty Pricing):</strong> Diskon otomatis sesuai jumlah beli (contoh: beli 1-5 = Rp 5.000, beli 6+ = Rp 4.500).<br>
                • <strong>Margin otomatis (% &amp; Rp):</strong> Sistem menghitung margin keuntungan secara otomatis berdasarkan harga modal beli vs harga jual ecer/grosir.<br>
                • <strong>Harga Multivarian (`/products/multivariant`):</strong> Pengaturan tier harga grosir bertingkat per varian kemasan.
            </div>
        </details>
        <?php endif; ?>

        <!-- 3. Purchases & AI Scan Invoice -->
        <?php if ($this->hasServiceAccess('purchases')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-cart-plus-fill text-warning me-2"></i> Pembelian, Restok &amp; AI Scan Invoice Belanja</span>
                <span class="badge bg-warning bg-opacity-10 text-warning ms-auto" style="font-size:10px;">AI Restok</span>
            </summary>
            <div class="help-item-body">
                • <strong>Input Barang Masuk (`/purchases/create`):</strong> Catat faktur pembelian barang dari supplier. Stok produk otomatis bertambah setelah disimpan.<br>
                • <strong>Distribusi PPN &amp; Diskon Nota:</strong> PPN (%) dan Diskon Nota (Rp) otomatis didistribusikan secara proporsional ke harga modal produk.<br>
                • <strong>AI Scan Invoice Belanja:</strong> Tekan tombol *Scan Invoice AI* -> Foto/upload struk atau faktur belanja supplier -> AI OCR berbasis OpenRouter membaca item, qty, dan harga secara otomatis -> Data langsung terisi ke tabel tanpa perlu ketik manual.<br>
                • <strong>Shortcut Master Data:</strong> Terdapat shortcut cepat untuk membuka Master Data di form kemasan jika butuh menambah satuan baru *on-the-fly*.
            </div>
        </details>
        <?php endif; ?>

        <!-- 4. PPOB & Produk Digital -->
        <?php if ($this->hasServiceAccess('ppob')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-phone-fill text-purple me-2" style="color:#a855f7;"></i> Produk Digital &amp; Transaksi PPOB</span>
                <span class="badge bg-purple bg-opacity-10 text-purple ms-auto" style="font-size:10px; color:#a855f7; background:rgba(168,85,247,0.12);">Digiflazz PPOB</span>
            </summary>
            <div class="help-item-body">
                • <strong>Layanan Produk Digital (`/ppob`):</strong> Pembelian Pulsa, Paket Data, Token PLN, Top-Up E-Wallet (Dana, Ovo, Gopay, ShopeePay), Voucher Game, dan Tagihan Pascabayar.<br>
                • <strong>Inquiry Real-time:</strong> Cek nama pelanggan PLN &amp; nomor E-Wallet secara otomatis sebelum melakukan pembayaran.<br>
                • <strong>Audit Riwayat Transaksi (`/ppob/history`):</strong> Laporan status transaksi (Sukses, Pending, Gagal), SN (Serial Number) pulsa/token, dan audit mutasi saldo deposit.<br>
                • <strong>PPOB Analytics (`/ppob/summary`):</strong> Insights performa penjualan PPOB, produk terlaris, dan distribusi kategori 30 hari.<br>
                <?php if ($isSuperadmin): ?>
                • <strong>Pengaturan PPOB Superadmin (`/ppob/settings`):</strong> Atur Username &amp; API Key Digiflazz (Development/Production), Webhook Secret, PIN transaksi, dan aturan Margin Markup global/kategori.
                <?php endif; ?>
            </div>
        </details>
        <?php endif; ?>

        <!-- 5. Restock Calculator (Order Estimate) -->
        <?php if ($this->hasServiceAccess('order_estimate')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-clipboard-check-fill text-teal me-2" style="color:#14b8a6;"></i> Hitung Orderan Supplier (Kalkulator Restok)</span>
                <span class="badge bg-teal bg-opacity-10 text-teal ms-auto" style="font-size:10px; color:#14b8a6; background:rgba(20,184,166,0.12);">Estimasi</span>
            </summary>
            <div class="help-item-body">
                • <strong>Kalkulator Restok (`/hitung-orderan`):</strong> Hitung estimasi jumlah barang yang perlu dipesan ke supplier berdasarkan stok minimum dan kecepatan rata-rata penjualan.<br>
                • <strong>Estimasi Biaya Belanja:</strong> Sistem menghitung estimasi total anggaran modal yang dibutuhkan untuk membeli stok barang dari supplier.
            </div>
        </details>
        <?php endif; ?>

        <!-- 6. Supplier Price Analysis -->
        <?php if ($this->hasServiceAccess('supplier_analysis')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-bar-chart-line-fill text-indigo me-2" style="color:#6366f1;"></i> Analisis Harga Supplier</span>
                <span class="badge bg-indigo bg-opacity-10 text-indigo ms-auto" style="font-size:10px; color:#6366f1; background:rgba(99,102,241,0.12);">Komparasi</span>
            </summary>
            <div class="help-item-body">
                • <strong>Perbandingan Per Satuan Dasar:</strong> Mengonversi semua harga beli supplier menjadi *Harga Per Pcs* untuk perbandingan secara *fair* antara supplier grosir (Karton) vs ecer.<br>
                • <strong>Rekomendasi Supplier Termurah:</strong> Penentuan supplier termurah berdasarkan transaksi harga terakhir (*Latest Price*).<br>
                • <strong>Indikator Tren Harga:</strong> Menampilkan grafis tren kenaikan/penurunan harga beli barang dari waktu ke waktu.
            </div>
        </details>
        <?php endif; ?>

        <!-- 7. Debts -->
        <?php if ($this->hasServiceAccess('debts')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-journal-text text-danger me-2"></i> Catatan Hutang &amp; Piutang</span>
                <span class="badge bg-danger bg-opacity-10 text-danger ms-auto" style="font-size:10px;">Hutang</span>
            </summary>
            <div class="help-item-body">
                • <strong>Hutang Toko &amp; Piutang Pelanggan (`/debts`):</strong> Catat kewajiban bayar toko ke supplier dan piutang tagihan belanja pelanggan.<br>
                • <strong>Pembayaran Cicilan:</strong> Catat pembayaran bertahap/pelunasan beserta bukti tanggal bayar.<br>
                • <strong>Notifikasi Jatuh Tempo:</strong> Penandaan otomatis untuk hutang/piutang yang mendekati tanggal jatuh tempo.
            </div>
        </details>
        <?php endif; ?>

        <!-- 8. Finance -->
        <?php if ($this->hasServiceAccess('finance')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-wallet2 text-indigo me-2" style="color:#818cf8;"></i> Keuangan &amp; Dompet Toko</span>
                <span class="badge bg-indigo bg-opacity-10 text-indigo ms-auto" style="font-size:10px; color:#818cf8; background:rgba(129,140,248,0.12);">Keuangan</span>
            </summary>
            <div class="help-item-body">
                • <strong>Catatan Kas Masuk/Keluar (`/finance`):</strong> Pencatatan biaya operasional toko (listrik, gaji, sewa) dan arus kas masuk di luar penjualan.<br>
                • <strong>Akun Dompet Toko:</strong> Manajemen saldo rekening bank, e-wallet toko, dan kas tunai fisik.
            </div>
        </details>
        <?php endif; ?>

        <!-- 9. Reports -->
        <?php if ($this->hasServiceAccess('reports')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-graph-up-arrow text-warning me-2"></i> Laporan Keuangan &amp; Analitik</span>
                <span class="badge bg-warning bg-opacity-10 text-warning ms-auto" style="font-size:10px;">Laporan</span>
            </summary>
            <div class="help-item-body">
                • <strong>Laporan Penjualan &amp; Profit (`/reports`):</strong> Ringkasan omzet, laba kotor, laba bersih, dan statistik penjualan bulanan.<br>
                • <strong>Histori Produk (`/reports/product-history`):</strong> Rekam jejak perubahan harga dan histori transaksi suatu produk.<br>
                • <strong>Export Data Excel:</strong> Unduh laporan ke file `.xlsx` untuk audit atau pembukuan akuntansi.
            </div>
        </details>
        <?php endif; ?>

        <!-- 10. Catalog -->
        <?php if ($this->hasServiceAccess('catalog')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-journal-richtext text-pink me-2" style="color:#ec4899;"></i> Pembuatan Katalog Produk Promo</span>
                <span class="badge bg-pink bg-opacity-10 text-pink ms-auto" style="font-size:10px; color:#ec4899; background:rgba(236,72,153,0.12);">Katalog</span>
            </summary>
            <div class="help-item-body">
                • <strong>Katalog Produk (`/catalog`):</strong> Buat flyer daftar harga produk promo toko yang siap dicetak atau dibagikan dalam format digital ke pelanggan.
            </div>
        </details>
        <?php endif; ?>

        <!-- 11. Customers -->
        <?php if ($this->hasServiceAccess('customers')): ?>
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-people-fill text-info me-2"></i> Database Pelanggan Toko &amp; PPOB</span>
                <span class="badge bg-info bg-opacity-10 text-info ms-auto" style="font-size:10px;">Pelanggan</span>
            </summary>
            <div class="help-item-body">
                • <strong>Database Pelanggan (`/customers`):</strong> Catat kontak pelanggan langganan toko &amp; penentuan tier harga grosir khusus.<br>
                • <strong>Pelanggan PPOB (`/ppob/customers`):</strong> Simpan nomor meter PLN, ID pelanggan PLN, dan nomor E-Wallet langganan agar transaksi PPOB lebih cepat tanpa ketik ulang.
            </div>
        </details>
        <?php endif; ?>

        <!-- 12. Tanya AI Assistant -->
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-stars text-primary me-2"></i> Tanya AI Assistant (AlfarezMart AI)</span>
                <span class="badge bg-primary bg-opacity-10 text-primary ms-auto" style="font-size:10px;">AI Chat</span>
            </summary>
            <div class="help-item-body">
                • <strong>AlfarezMart AI (`/chat`):</strong> Asisten AI cerdas yang memahami seluruh data stok, transaksi, dan laporan toko Anda.<br>
                • <strong>Pertanyaan yang Bisa Diajukan:</strong> "Produk apa yang stoknya menipis hari ini?", "Berapa total omzet minggu ini?", "Buatkan rekomendasi promo untuk barang terlaris".
            </div>
        </details>

        <!-- 13. Offline PWA -->
        <details class="help-item-card" style="margin-bottom:10px;">
            <summary class="help-item-summary">
                <span><i class="bi bi-wifi-off text-success me-2"></i> Mode Offline PWA &amp; Background Sync</span>
                <span class="badge bg-success bg-opacity-10 text-success ms-auto" style="font-size:10px;">PWA</span>
            </summary>
            <div class="help-item-body">
                • <strong>IndexedDB Local Cache:</strong> Aplikasi dapat menyimpan stok dan transaksi secara lokal saat tidak ada koneksi internet.<br>
                • <strong>Background Synchronization:</strong> Begitu internet terhubung kembali, antrean transaksi offline otomatis terkirim ke server utama.
            </div>
        </details>

        <!-- 14. Superadmin Only: User Management & Access Control -->
        <?php if ($isSuperadmin): ?>
        <details class="help-item-card" style="margin-bottom:10px; border:1px solid rgba(230,57,70,0.3);">
            <summary class="help-item-summary" style="background:rgba(230,57,70,0.06);">
                <span><i class="bi bi-shield-lock-fill text-danger me-2"></i> Manajemen User &amp; Kontrol Akses Layanan (Superadmin)</span>
                <span class="badge bg-danger bg-opacity-10 text-danger ms-auto" style="font-size:10px;">Superadmin</span>
            </summary>
            <div class="help-item-body">
                • <strong>Kelola Pengguna (`/users`):</strong> Tambah, edit, nonaktifkan, atau reset password akun pengguna toko.<br>
                • <strong>Matriks Kontrol Akses Layanan:</strong> Di halaman Manajemen User -> Tab <em>Kontrol Akses Layanan</em>, Superadmin dapat mengaktifkan/mematikan akses ke 14 layanan toko khusus untuk role **Admin** dan **Staff**.<br>
                • <strong>Restriksi PPOB:</strong> Layanan PPOB secara default dibatasi hanya untuk Superadmin, kecuali diaktifkan secara eksplisit untuk Admin/Staff di Matriks Kontrol Akses.<br>
                • <strong>Geofencing Pembatasan Lokasi (`/settings/app`):</strong> Tentukan koordinat toko (Latitude/Longitude) dan radius (meter) untuk membatasi akses login Staff di lokasi toko.
            </div>
        </details>
        <?php endif; ?>
    </div>

    <!-- ======================================================= -->
    <!-- SECTION 3: TROUBLESHOOTING                              -->
    <!-- ======================================================= -->
    <div id="sec-troubleshoot" class="help-group-section" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:20px;">
        <h3 style="font-size:15px; font-weight:800; margin-bottom:14px; color:var(--danger); display:flex; align-items:center; gap:8px;">
            <i class="bi bi-wrench-adjustable-circle-fill"></i> Troubleshooting &amp; Solusi Kendala
        </h3>

        <details class="help-item-card" style="margin-bottom:8px;">
            <summary class="help-item-summary">Aplikasi tidak memuat / Tampilan kosong atau lambat</summary>
            <div class="help-item-body">
                1. Force Close / tutup aplikasi sepenuhnya dari recent apps HP.<br>
                2. Buka kembali aplikasi.<br>
                3. Jika masih terjadi: Buka Chrome -> Settings -> Site Settings -> Clear Data untuk situs AlfarezMart.<br>
                4. Klik tombol <strong>Reload</strong> di pojok kanan atas header untuk *hard refresh*.
            </div>
        </details>

        <details class="help-item-card" style="margin-bottom:8px;">
            <summary class="help-item-summary">Kamera Scanner Barcode tidak terbuka / Error Permission</summary>
            <div class="help-item-body">
                1. Pastikan telah memberikan izin akses Kamera pada browser.<br>
                2. Pastikan tidak ada aplikasi kamera lain yang sedang berjalan di latar belakang.<br>
                3. Akses aplikasi wajib menggunakan protokol HTTPS yang aman.<br>
                4. Jika menggunakan scanner Bluetooth fisik, cukup hubungkan ke HP dan scanner akan mengisi kolom pencarian secara otomatis.
            </div>
        </details>

        <details class="help-item-card" style="margin-bottom:8px;">
            <summary class="help-item-summary">Printer Thermal Bluetooth Terputus / Tidak Berfungsi</summary>
            <div class="help-item-body">
                1. Pastikan printer thermal sudah dinyalakan dan Bluetooth HP aktif.<br>
                2. Fitur Web Bluetooth membutuhkan browser Chrome / Edge di sistem operasi Android.<br>
                3. Jika dialog pairing tidak muncul: matikan &amp; nyalakan ulang printer, lalu muat ulang halaman.<br>
                4. Pastikan lebar kertas printer sudah sesuai di Pengaturan Struk (58mm = 32 Karakter, 80mm = 48 Karakter).
            </div>
        </details>

        <?php if ($this->hasServiceAccess('ppob')): ?>
        <details class="help-item-card" style="margin-bottom:8px;">
            <summary class="help-item-summary">Transaksi PPOB Pending atau Gagal</summary>
            <div class="help-item-body">
                1. Buka halaman <strong>Produk Digital -> Riwayat Transaksi (`/ppob/history`)</strong>.<br>
                2. Tekan tombol <strong>Cek Status</strong> pada transaksi pending untuk menyinkronkan status terbaru dari server Digiflazz.<br>
                3. Jika status Gagal: Saldo deposit tidak terpotong (atau akan di-refund otomatis oleh Digiflazz).
            </div>
        </details>
        <?php endif; ?>

        <?php if ($this->hasServiceAccess('purchases')): ?>
        <details class="help-item-card" style="margin-bottom:8px;">
            <summary class="help-item-summary">AI Scan Invoice mengembalikan respons kosong / error</summary>
            <div class="help-item-body">
                1. Pastikan foto faktur/nota supplier jelas, terang, dan tidak buram.<br>
                2. Sistem secara otomatis menggunakan model Vision gratis sebagai fallback jika terjadi kendala token.<br>
                3. Jika gagal, input produk dapat dilakukan secara manual menggunakan fitur <em>Input Bulk / Massal</em>.
            </div>
        </details>
        <?php endif; ?>

        <details class="help-item-card" style="margin-bottom:8px;">
            <summary class="help-item-summary">Akses Ditolak saat Membuka Halaman Tertentu</summary>
            <div class="help-item-body">
                1. Berarti layanan tersebut sedang dinonaktifkan oleh Superadmin untuk role akun Anda.<br>
                2. Hubungi Superadmin toko untuk mengaktifkan izin layanan tersebut pada halaman <em>Manajemen User -> Kontrol Akses Layanan</em>.
            </div>
        </details>
    </div>

    <!-- ======================================================= -->
    <!-- SECTION 4: FITUR TERBARU & CHANGELOG                    -->
    <!-- ======================================================= -->
    <div id="sec-pembaruan" class="help-group-section" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:20px;">
        <h3 style="font-size:15px; font-weight:800; margin-bottom:14px; color:var(--warning); display:flex; align-items:center; gap:8px;">
            <i class="bi bi-lightning-charge-fill text-warning"></i> Pembaruan &amp; Fitur Terbaru (v1.1.6)
        </h3>

        <div style="display:flex; flex-direction:column; gap:10px;">
            <div style="background:var(--surface-2); padding:12px 14px; border-radius:var(--radius-md); border-left:4px solid var(--primary);">
                <strong style="font-size:13px; color:var(--text-primary);"><i class="bi bi-sliders text-primary me-1"></i> Sistem Kontrol Akses Layanan (Role Permissions)</strong>
                <p style="font-size:12px; color:var(--text-muted); margin:4px 0 0 0; line-line:1.5;">
                    Superadmin sekarang memiliki kontrol penuh untuk mengaktifkan atau menonaktifkan 14 layanan utama aplikasi secara independen untuk role Admin dan Staff.
                </p>
            </div>

            <div style="background:var(--surface-2); padding:12px 14px; border-radius:var(--radius-md); border-left:4px solid #a855f7;">
                <strong style="font-size:13px; color:var(--text-primary);"><i class="bi bi-phone-fill me-1" style="color:#a855f7;"></i> Integrasi PPOB Digiflazz &amp; Dashboard Analytics</strong>
                <p style="font-size:12px; color:var(--text-muted); margin:4px 0 0 0; line-height:1.5;">
                    Penambahan modul Produk Digital PPOB lengkap beserta KPI card transaksi harian, widget top 5 produk, dan analitik kategori 30 hari.
                </p>
            </div>

            <div style="background:var(--surface-2); padding:12px 14px; border-radius:var(--radius-md); border-left:4px solid var(--warning);">
                <strong style="font-size:13px; color:var(--text-primary);"><i class="bi bi-robot text-warning me-1"></i> AI Invoice OCR Scanner 100% Gratis</strong>
                <p style="font-size:12px; color:var(--text-muted); margin:4px 0 0 0; line-height:1.5;">
                    Mekanisme fallback otomatis ke OpenRouter free vision models dan pengekstrakan JSON berbasis regex untuk memastikan scan faktur belanja 100% handal tanpa kendala token.
                </p>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- SECTION 5: DAFTAR ISTILAH                               -->
    <!-- ======================================================= -->
    <div id="sec-istilah" class="help-group-section" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:20px;">
        <h3 style="font-size:15px; font-weight:800; margin-bottom:14px; color:var(--text-muted); display:flex; align-items:center; gap:8px;">
            <i class="bi bi-book-fill"></i> Kamus Istilah Aplikasi
        </h3>
        
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:10px; font-size:12px;">
            <div style="background:var(--surface-2); padding:10px 12px; border-radius:var(--radius-md);">
                <strong style="color:var(--primary);">POS (Point of Sale)</strong><br>
                <span style="color:var(--text-muted);">Sistem kasir digital untuk mencatat transaksi penjualan.</span>
            </div>
            <div style="background:var(--surface-2); padding:10px 12px; border-radius:var(--radius-md);">
                <strong style="color:var(--purple);" style="color:#a855f7;">PPOB</strong><br>
                <span style="color:var(--text-muted);">Payment Point Online Bank — pembayaran pulsa, data, PLN, &amp; e-wallet.</span>
            </div>
            <div style="background:var(--surface-2); padding:10px 12px; border-radius:var(--radius-md);">
                <strong style="color:var(--success);">Harga Modal (Cost Price)</strong><br>
                <span style="color:var(--text-muted);">Harga beli bersih per satuan barang setelah memperhitungkan PPN &amp; diskon nota.</span>
            </div>
            <div style="background:var(--surface-2); padding:10px 12px; border-radius:var(--radius-md);">
                <strong style="color:var(--warning);">Margin Profit</strong><br>
                <span style="color:var(--text-muted);">Persentase selisih keuntungan antara harga jual dan harga beli modal.</span>
            </div>
            <div style="background:var(--surface-2); padding:10px 12px; border-radius:var(--radius-md);">
                <strong style="color:var(--info);">Base Qty</strong><br>
                <span style="color:var(--text-muted);">Jumlah rasio isi kemasan besar dihitung dalam satuan terkecil (Level 1).</span>
            </div>
            <div style="background:var(--surface-2); padding:10px 12px; border-radius:var(--radius-md);">
                <strong style="color:var(--danger);">Geofencing</strong><br>
                <span style="color:var(--text-muted);">Pembatasan akses login Staff kasir berdasarkan jangkauan lokasi GPS toko.</span>
            </div>
        </div>
    </div>

    <!-- Footer Banner -->
    <div style="text-align:center; padding:20px; font-size:11px; color:var(--text-muted);">
        <i class="bi bi-shield-check text-success me-1"></i> AlfarezMart v1.1.6 &middot; PWA Inventory &amp; POS System<br>
        <span style="font-size:10px;">&copy; 2026 AlfarezMart. Sistem Panduan Disesuaikan Hak Akses Role.</span>
    </div>
</div>

<style>
.help-cat-chip {
    padding: 6px 14px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
    border: 1px solid var(--border-color);
    background: var(--surface-1);
    color: var(--text-muted);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}
.help-cat-chip.active, .help-cat-chip:hover {
    background: var(--primary);
    color: #ffffff;
    border-color: var(--primary);
}
.help-item-card {
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    background: var(--surface-1);
    transition: border-color 0.2s;
}
.help-item-card:hover {
    border-color: var(--primary);
}
.help-item-summary {
    padding: 12px 16px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    background: var(--surface-2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    user-select: none;
    color: var(--text-primary);
}
.help-item-body {
    padding: 14px 16px;
    font-size: 12px;
    line-height: 1.8;
    color: var(--text-primary);
    border-top: 1px solid var(--border-color);
    background: var(--surface-1);
}
</style>

<script>
function filterHelpCategory(catName) {
    document.querySelectorAll('.help-cat-chip').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById('chip-' + catName);
    if (activeBtn) activeBtn.classList.add('active');

    const sections = document.querySelectorAll('.help-group-section');
    if (catName === 'all') {
        sections.forEach(sec => sec.style.display = 'block');
    } else {
        sections.forEach(sec => sec.style.display = 'none');
        const targetSec = document.getElementById('sec-' + catName);
        if (targetSec) targetSec.style.display = 'block';
    }
}

function filterHelpTopics() {
    const input = document.getElementById('helpSearchInput').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.help-item-card');

    if (input === '') {
        cards.forEach(card => card.style.display = 'block');
        document.querySelectorAll('.help-group-section').forEach(sec => sec.style.display = 'block');
        return;
    }

    // Show all sections during active search
    document.querySelectorAll('.help-group-section').forEach(sec => sec.style.display = 'block');

    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(input)) {
            card.style.display = 'block';
            card.open = true; // Auto expand matching details
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
