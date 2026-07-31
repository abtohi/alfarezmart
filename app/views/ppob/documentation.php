<style>
/* ===== Custom Documentation Design System ===== */
:root {
    --doc-sidebar-width: 280px;
}

.doc-wrapper {
    color: var(--text-primary);
    font-family: var(--font-family, 'Inter', sans-serif);
}

.doc-hero {
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.98)), url('<?= BASE_URL ?>public/images/mobile_icon.png') no-repeat right 5% center / 180px;
    border-radius: 20px;
    padding: 32px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.doc-hero-title {
    font-size: 26px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.5px;
}

.doc-hero-subtitle {
    color: #94a3b8;
    font-size: 14px;
    max-width: 650px;
}

.doc-search-box {
    position: relative;
    max-width: 480px;
    margin-top: 20px;
}

.doc-search-box input {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    padding: 12px 16px 12px 42px;
    border-radius: 12px;
    font-size: 14px;
    width: 100%;
    backdrop-filter: blur(10px);
    transition: all 0.25s ease;
}

.doc-search-box input:focus {
    background: rgba(255, 255, 255, 0.18);
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
    outline: none;
    color: #fff;
}

.doc-search-box input::placeholder {
    color: #94a3b8;
}

.doc-search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 16px;
}

/* Sidebar Nav */
.doc-nav-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 16px;
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
}

.doc-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
    margin-bottom: 2px;
}

.doc-nav-item:hover {
    background: var(--surface-2);
    color: var(--primary);
    transform: translateX(3px);
}

.doc-nav-item.active {
    background: rgba(59, 130, 246, 0.12);
    color: var(--primary);
    font-weight: 700;
    border-left: 3px solid var(--primary);
}

.doc-nav-item i {
    font-size: 16px;
    width: 20px;
    text-align: center;
}

/* Content Cards */
.doc-section-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 18px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    scroll-margin-top: 24px;
    transition: border-color 0.2s ease;
}

.doc-section-card:hover {
    border-color: rgba(59, 130, 246, 0.4);
}

.doc-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 14px;
    margin-bottom: 18px;
}

.doc-section-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(59, 130, 246, 0.12);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.doc-section-title {
    font-size: 18px;
    font-weight: 800;
    margin: 0;
    color: var(--text-primary);
}

/* Callout Box */
.doc-callout {
    padding: 16px;
    border-radius: 12px;
    margin: 16px 0;
    border-left: 4px solid;
    font-size: 13.5px;
    line-height: 1.6;
}

.doc-callout.tip {
    background: rgba(16, 185, 129, 0.08);
    border-color: #10b981;
    color: var(--text-primary);
}

.doc-callout.important {
    background: rgba(59, 130, 246, 0.08);
    border-color: #3b82f6;
    color: var(--text-primary);
}

.doc-callout.warning {
    background: rgba(245, 158, 11, 0.08);
    border-color: #f59e0b;
    color: var(--text-primary);
}

.doc-callout.danger {
    background: rgba(239, 68, 68, 0.08);
    border-color: #ef4444;
    color: var(--text-primary);
}

/* Code Snippet Box */
.doc-code-block {
    background: #0f172a;
    border-radius: 12px;
    padding: 16px;
    color: #e2e8f0;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12.5px;
    position: relative;
    margin: 14px 0;
    overflow-x: auto;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.doc-copy-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    border: none;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    cursor: pointer;
    transition: background 0.2s;
}

.doc-copy-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Data Table */
.doc-table {
    width: 100%;
    border-collapse: collapse;
    margin: 14px 0;
    font-size: 13px;
}

.doc-table th {
    background: var(--surface-2);
    color: var(--text-secondary);
    font-weight: 700;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.doc-table td {
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
}

.doc-badge {
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
}

.doc-badge.success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.doc-badge.warning { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.doc-badge.danger  { background: rgba(239, 68, 68, 0.15);  color: #ef4444; }
.doc-badge.info    { background: rgba(59, 130, 246, 0.15);  color: #3b82f6; }

@media (max-width: 768px) {
    .doc-hero { padding: 24px 18px; }
    .doc-hero-title { font-size: 20px; }
    .doc-nav-card { position: static; max-height: none; margin-bottom: 20px; }
}
</style>

<div class="container-fluid py-4 doc-wrapper">
    <!-- Header Navigation Back Button -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="<?= BASE_URL ?>ppob" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold px-3 py-2" style="background: var(--surface-1); border-color: var(--border-color); color: var(--text-primary);">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard PPOB
        </a>
        <div class="text-muted small">
            <i class="bi bi-book me-1"></i> Dokumentasi Resmi PPOB v15.58
        </div>
    </div>

    <!-- Hero Banner -->
    <div class="doc-hero">
        <div class="doc-hero-title">Dokumentasi & Panduan Lengkap PPOB AlfarezMart</div>
        <div class="doc-hero-subtitle mt-2">
            Panduan teknis dan operasional menyeluruh untuk seluruh fitur Produk Digital (PPOB), Manajemen Saldo, Auto-Markup, Webhook Callback, Thermal Printer, Audit Saldo, dan Sandbox Testing.
        </div>
        <div class="doc-search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="docSearchInput" placeholder="Cari topik, endpoint API, atau kode error (misal: webhook, barcode, RC 00)..." onkeyup="filterDocContent()">
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="doc-nav-card">
                <div class="text-uppercase fw-bold text-muted mb-2 px-2" style="font-size:10px; letter-spacing:1px;">Daftar Modul & Fitur</div>
                <a href="#overview" class="doc-nav-item active" onclick="setActiveNav(this)"><i class="bi bi-cpu"></i> 1. Pengenalan & Arsitektur</a>
                <a href="#balance" class="doc-nav-item" onclick="setActiveNav(this)"><i class="bi bi-wallet2"></i> 2. Saldo & Deposit Digiflazz</a>
                <a href="#catalog" class="doc-nav-item" onclick="setActiveNav(this)"><i class="bi bi-tags"></i> 3. Katalog & Auto-Markup</a>
                <a href="#transactions" class="doc-nav-item" onclick="setActiveNav(this)"><i class="bi bi-lightning-charge"></i> 4. Alur Transaksi PPOB</a>
                <a href="#receipt" class="doc-nav-item" onclick="setActiveNav(this)"><i class="bi bi-receipt"></i> 5. Struk Digital & Thermal Printer</a>
                <a href="#history" class="doc-nav-item" onclick="setActiveNav(this)"><i class="bi bi-journal-text"></i> 6. Riwayat & Audit Saldo</a>
                <a href="#analytics" class="doc-nav-item" onclick="setActiveNav(this)"><i class="bi bi-bar-chart-line"></i> 7. Analytics & Top 5 Seller</a>
                <a href="#webhook" class="doc-nav-item" onclick="setActiveNav(this)"><i class="bi bi-diagram-3"></i> 8. Webhook & Integrasi API</a>
                <a href="#response-codes" class="doc-nav-item" onclick="setActiveNav(this)"><i class="bi bi-shield-exclamation"></i> 9. Kode Respon (RC Matrix)</a>
                <a href="#test-cases" class="doc-nav-item" onclick="setActiveNav(this)"><i class="bi bi-bug"></i> 10. Panduan Sandbox Testing</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">

            <!-- 1. OVERVIEW -->
            <div class="doc-section-card" id="overview">
                <div class="doc-section-header">
                    <div class="doc-section-icon"><i class="bi bi-cpu"></i></div>
                    <div>
                        <h3 class="doc-section-title">1. Pengenalan & Arsitektur PPOB AlfarezMart</h3>
                        <span class="text-muted small">Gambaran umum arsitektur dan sistem integrasi produk digital.</span>
                    </div>
                </div>
                <p>Fitur PPOB (Payment Point Online Bank) pada AlfarezMart mengintegrasikan ekosistem kasir & toko Anda secara langsung dengan <strong>Digiflazz API v2</strong>. Sistem ini memungkinkan penjualan pulsa, paket data, PLN token, e-money, voucher game, serta pembayaran tagihan pascabayar secara otomatis dan aman.</p>

                <h6 class="fw-bold mt-4 mb-2"><i class="bi bi-shield-check text-primary me-1"></i> Fitur Keamanan & Proteksi Transaksi:</h6>
                <ul>
                    <li><strong>PIN Keamanan Agen:</strong> Setiap eksekusi transaksi memerlukan validasi 6-digit PIN Keamanan Kasir untuk mencegah penyalahgunaan.</li>
                    <li><strong>CSRF Token & Anti-Double Transaction:</strong> Sistem memblokir pengiriman ganda pada transaksi dengan parameter yang sama dalam interval singkat.</li>
                    <li><strong>Dual Execution Mode (Sandbox / Production):</strong> Mode Sandbox memungkinkan pengujian tanpa menggunakan saldo riil, sedangkan Production langsung terhubung ke jaringan Digiflazz live.</li>
                </ul>

                <div class="doc-callout important">
                    <strong><i class="bi bi-info-circle-fill me-1"></i> Catatan Arsitektur:</strong> Seluruh harga modal diambil secara otomatis dari Digiflazz secara real-time. Keuntungan toko ditentukan melalui aturan Auto-Markup Global maupun Override Per-Kategori/SKU.
                </div>
            </div>

            <!-- 2. BALANCE & DEPOSIT -->
            <div class="doc-section-card" id="balance">
                <div class="doc-section-header">
                    <div class="doc-section-icon" style="background:rgba(16,185,129,0.12); color:#10b981;"><i class="bi bi-wallet2"></i></div>
                    <div>
                        <h3 class="doc-section-title">2. Saldo & Manajemen Deposit</h3>
                        <span class="text-muted small">Cek saldo otomatis, notifikasi ambang batas, dan prosedur top up.</span>
                    </div>
                </div>
                <p>Saldo deposit PPOB disimpan di akun merchant Digiflazz Anda. Aplikasi AlfarezMart secara berkala menyinkronkan saldo tersebut dan memunculkannya pada header dashboard PPOB.</p>

                <h6 class="fw-bold mt-3"><i class="bi bi-bell-fill text-warning me-1"></i> Notifikasi Saldo Minim:</h6>
                <p class="small">Jika saldo deposit Digiflazz Anda berada di bawah ambang batas aman (default: Rp 100.000), sistem akan menampilkan badge peringatan kuning di header PPOB agar Anda segera melakukan Top Up.</p>

                <h6 class="fw-bold mt-3"><i class="bi bi-box-arrow-up-right me-1"></i> Cara Melakukan Top Up Deposit:</h6>
                <ol class="small">
                    <li>Login ke Dashboard Resmi Digiflazz (<code>https://member.digiflazz.com</code>).</li>
                    <li>Masuk ke menu <strong>Deposit &gt; Tiket Deposit</strong>.</li>
                    <li>Tentukan nominal deposit (minimal Rp 50.000) dan pilih metode pembayaran (Transfer Bank BCA/Mandiri/BRI atau QRIS).</li>
                    <li>Transfer sesuai nominal unik hingga digit terakhir agar saldo otomatis masuk dalam hitungan detik.</li>
                </ol>
            </div>

            <!-- 3. CATALOG & AUTO-MARKUP -->
            <div class="doc-section-card" id="catalog">
                <div class="doc-section-header">
                    <div class="doc-section-icon" style="background:rgba(245,158,11,0.12); color:#f59e0b;"><i class="bi bi-tags"></i></div>
                    <div>
                        <h3 class="doc-section-title">3. Katalog Produk & Aturan Auto-Markup</h3>
                        <span class="text-muted small">Pengaturan margin keuntungan, pencarian cepat, dan matriks supplier.</span>
                    </div>
                </div>
                <p>AlfarezMart menyediakan fitur kalkulasi harga jual otomatis (Auto-Markup) sehingga Anda tidak perlu mengubah harga produk secara manual satu per satu saat supplier menaikkan/menurunkan harga modal.</p>

                <h6 class="fw-bold mt-3">Skema Aturan Auto-Markup:</h6>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Jenis Markup</th>
                            <th>Aturan / Logika</th>
                            <th>Contoh Kasus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="doc-badge info">Global Fixed</span></td>
                            <td>Menambahkan nilai nominal rupiah tetap ke harga modal supplier.</td>
                            <td>Harga Modal: Rp 10.200 + Markup Rp 2.000 = Harga Jual <strong>Rp 12.200</strong>.</td>
                        </tr>
                        <tr>
                            <td><span class="doc-badge success">Global Percentage</span></td>
                            <td>Menambahkan persentase persen dari harga modal supplier.</td>
                            <td>Harga Modal: Rp 100.000 + 5% = Harga Jual <strong>Rp 105.000</strong>.</td>
                        </tr>
                        <tr>
                            <td><span class="doc-badge warning">Category Override</span></td>
                            <td>Menentukan markup khusus untuk kategori tertentu (misal: E-Money / PLN).</td>
                            <td>PLN Token diset markup khusus Rp 2.500 per transaksi.</td>
                        </tr>
                        <tr>
                            <td><span class="doc-badge danger">Custom SKU Pricing</span></td>
                            <td>Mengunci harga jual khusus pada satu SKU produk tertentu.</td>
                            <td>Indosat 5GB dikunci pada harga jual pas Rp 15.000.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="doc-callout tip">
                    <strong><i class="bi bi-lightning-fill me-1"></i> Performa Supplier & Kecepatan:</strong> Setiap kartu produk pada daftar pilihan PPOB menampilkan badge <strong>Seller SR (%)</strong>, <strong>Product SR (%)</strong>, serta <strong>Kecepatan Rata-rata (⚡ Detik)</strong> untuk membantu kasir memilih produk dari supplier tercepat.
                </div>
            </div>

            <!-- 4. TRANSACTIONS -->
            <div class="doc-section-card" id="transactions">
                <div class="doc-section-header">
                    <div class="doc-section-icon" style="background:rgba(99,102,241,0.12); color:#6366f1;"><i class="bi bi-lightning-charge"></i></div>
                    <div>
                        <h3 class="doc-section-title">4. Alur Transaksi Prabayar & Pascabayar</h3>
                        <span class="text-muted small">Tata cara pemrosesan pulsa, data, token PLN, e-money, dan tagihan bulanan.</span>
                    </div>
                </div>

                <h6 class="fw-bold"><i class="bi bi-phone me-1"></i> Transaksi Prabayar (Prepaid):</h6>
                <ol class="small">
                    <li>Pilih kategori produk (Pulsa, Paket Data, PLN Token, E-Money, Game, dsb).</li>
                    <li>Masukkan nomor tujuan pelanggan (Format otomatis didukung: <code>0812...</code> / <code>62812...</code>).</li>
                    <li>Aplikasi akan mendeteksi nama operator provider secara otomatis (misal: Telkomsel / Indosat / XL).</li>
                    <li>Pilih nominal / paket produk dari daftar kartu pilihan.</li>
                    <li>Masukkan PIN Keamanan Agen dan klik <strong>Beli Sekarang</strong>.</li>
                </ol>

                <h6 class="fw-bold mt-4"><i class="bi bi-file-earmark-text me-1"></i> Transaksi Pascabayar (Postpaid):</h6>
                <ol class="small">
                    <li>Pilih kategori Tagihan (PLN Pascabayar, PDAM, BPJS Kesehatan, Telkom / Indihome, Multifinance).</li>
                    <li>Masukkan Nomor Pelanggan / ID Pelanggan lalu klik <strong>Cek Tagihan</strong>.</li>
                    <li>Sistem akan menampilkan rincian tagihan (Nama Pelanggan, Jumlah Periode, Denda, Admin Bank, & Total Bayar).</li>
                    <li>Konfirmasi rincian dan klik <strong>Bayar Tagihan</strong>.</li>
                </ol>
            </div>

            <!-- 5. RECEIPT & PRINTER -->
            <div class="doc-section-card" id="receipt">
                <div class="doc-section-header">
                    <div class="doc-section-icon" style="background:rgba(236,72,153,0.12); color:#ec4899;"><i class="bi bi-receipt"></i></div>
                    <div>
                        <h3 class="doc-section-title">5. Struk Digital & Thermal Printer</h3>
                        <span class="text-muted small">Pratinjau struk modern, kustomisasi harga cetak, dan printer Bluetooth ESC/POS.</span>
                    </div>
                </div>

                <p>Setiap transaksi PPOB yang sukses dapat dicetak menjadi struk fisik maupun bagikan struk digital berupa link / WhatsApp PDF.</p>

                <h6 class="fw-bold mt-3"><i class="bi bi-sliders me-1"></i> Fitur Kustomisasi Harga Struk:</h6>
                <p class="small">Agen dapat menyesuaikan nominal harga jual yang tercetak pada struk untuk pelanggan (misal: menambahkan biaya administrasi lokal Rp 2.000) tanpa merubah catatan laporan margin asli di database kasir.</p>

                <h6 class="fw-bold mt-3"><i class="bi bi-printer me-1"></i> Integrasi Thermal Printer (ESC/POS):</h6>
                <p class="small">Aplikasi terhubung dengan modul JS <code>ThermalPrinter</code> yang mendukung printer kasir thermal Bluetooth 58mm / 80mm. Struk memuat No. Referensi internal, ID Transaksi Provider (Trx ID), Tanggal, Detail Produk, SN/Token PLN, dan Total Bayar.</p>
            </div>

            <!-- 6. HISTORY & BALANCE AUDIT -->
            <div class="doc-section-card" id="history">
                <div class="doc-section-header">
                    <div class="doc-section-icon" style="background:rgba(14,165,233,0.12); color:#0ea5e9;"><i class="bi bi-journal-text"></i></div>
                    <div>
                        <h3 class="doc-section-title">6. Laporan Riwayat & Audit Saldo Deposit</h3>
                        <span class="text-muted small">Pelacakan transaksi, kolom Saldo Sebelum & Sesudah, dan fitur komplain.</span>
                    </div>
                </div>

                <p>Halaman <code>/ppob/history</code> menyajikan rekaman transaksi lengkap beserta audit transparansi saldo deposit.</p>

                <h6 class="fw-bold mt-3"><i class="bi bi-table me-1"></i> Struktur Kolom Tabel Riwayat PPOB:</h6>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Kolom</th>
                            <th>Penjelasan Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Aksi</strong></td>
                            <td>Tombol Cetak Struk, Bagikan Struk, Cek Status Ulang, dan Komplain.</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal & Waktu</strong></td>
                            <td>Waktu pasti pembuatan transaksi (WIP / WIB +07:00).</td>
                        </tr>
                        <tr>
                            <td><strong>Produk / Pelanggan</strong></td>
                            <td>Nama produk digital dan Nomor ID Pelanggan.</td>
                        </tr>
                        <tr>
                            <td><strong>Modal & Jual</strong></td>
                            <td>Harga modal dari Digiflazz dan harga jual ke pelanggan (+ Estimasi Profit).</td>
                        </tr>
                        <tr>
                            <td><span class="doc-badge info">Saldo Sebelum</span></td>
                            <td>Saldo deposit Digiflazz toko <strong>sebelum</strong> transaksi ini terpotong.</td>
                        </tr>
                        <tr>
                            <td><span class="doc-badge success">Saldo Sesudah</span></td>
                            <td>Saldo deposit Digiflazz toko <strong>setelah</strong> transaksi terpotong.</td>
                        </tr>
                        <tr>
                            <td><strong>SN / Token</strong></td>
                            <td>Serial Number pengisian atau 20-digit Kode Token Listrik PLN.</td>
                        </tr>
                        <tr>
                            <td><strong>Seller</strong></td>
                            <td>Nama penyedia / supplier resmi produk di Digiflazz.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 7. ANALYTICS & SUPPLIER LEADERBOARD -->
            <div class="doc-section-card" id="analytics">
                <div class="doc-section-header">
                    <div class="doc-section-icon" style="background:rgba(168,85,247,0.12); color:#a855f7;"><i class="bi bi-bar-chart-line"></i></div>
                    <div>
                        <h3 class="doc-section-title">7. Analytics Dashboard & Top 5 Seller</h3>
                        <span class="text-muted small">Visualisasi performa bisnis PPOB dan pemeringkatan supplier terbaik.</span>
                    </div>
                </div>

                <p>Halaman <code>/ppob/summary</code> menyediakan insights mendalam mengenai performa penjualan produk digital toko Anda.</p>

                <h6 class="fw-bold mt-3">Komponen Visualisasi:</h6>
                <ul>
                    <li><strong>4 Cards KPI Financials:</strong> Total Transaksi, Omset (Revenue), Modal (Cost), & Profit Bersih.</li>
                    <li><strong>Top 5 Seller Leaderboard Widget:</strong> Pemeringkatan supplier terbaik menggunakan rumus Bobot Komposit:
                        <br><code>Score = (SuccessRate × 50%) + (KecepatanScore × 35%) + (ProfitMargin × 15%)</code>
                    </li>
                    <li><strong>Category Breakdown Chart:</strong> Diagram lingkaran (Doughnut) distribusi kategori terlaris.</li>
                    <li><strong>Daily Trend Chart:</strong> Grafik tren transaksi harian & pertumbuhan profit.</li>
                </ul>
            </div>

            <!-- 8. WEBHOOK & API -->
            <div class="doc-section-card" id="webhook">
                <div class="doc-section-header">
                    <div class="doc-section-icon" style="background:rgba(20,184,166,0.12); color:#14b8a6;"><i class="bi bi-diagram-3"></i></div>
                    <div>
                        <h3 class="doc-section-title">8. Webhook & Otomatisasi Status</h3>
                        <span class="text-muted small">Callback otomatis untuk pembaruan status transaksi pending secara real-time.</span>
                    </div>
                </div>

                <p>Webhook memastikan bahwa saat transaksi berstatus <strong>Pending</strong> selesai diproses oleh operator, status pada aplikasi kasir Anda langsung berubah menjadi <strong>Sukses</strong> atau <strong>Gagal</strong> tanpa reload manual.</p>

                <h6 class="fw-bold mt-3">Konfigurasi Webhook URL:</h6>
                <div class="doc-code-block">
                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
https://alfarezmart.com/api/ppob/webhook
                </div>

                <h6 class="fw-bold mt-3">Verifikasi HMAC MD5 Signature:</h6>
                <p class="small">Aplikasi memvalidasi Secret Key webhook dengan algoritma MD5 signature:</p>
                <div class="doc-code-block">
                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
Signature = md5(username + secret_key + post_body)
                </div>
            </div>

            <!-- 9. RESPONSE CODES -->
            <div class="doc-section-card" id="response-codes">
                <div class="doc-section-header">
                    <div class="doc-section-icon" style="background:rgba(239,68,68,0.12); color:#ef4444;"><i class="bi bi-shield-exclamation"></i></div>
                    <div>
                        <h3 class="doc-section-title">9. Kode Respon & Matriks Error (RC Reference)</h3>
                        <span class="text-muted small">Daftar kode status Digiflazz (Response Code) dan tindakan perbaikannya.</span>
                    </div>
                </div>

                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>RC</th>
                            <th>Status</th>
                            <th>Pesan / Keterangan</th>
                            <th>Tindakan Kasir / Agen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>00</strong></td>
                            <td><span class="doc-badge success">Sukses</span></td>
                            <td>Transaksi Berhasil Diproses.</td>
                            <td>Cetak atau kirimkan struk ke pelanggan.</td>
                        </tr>
                        <tr>
                            <td><strong>01</strong></td>
                            <td><span class="doc-badge warning">Pending</span></td>
                            <td>Transaksi Sedang Diproses Operator.</td>
                            <td>Tunggu 1-3 menit atau klik tombol 'Cek Status'.</td>
                        </tr>
                        <tr>
                            <td><strong>02</strong></td>
                            <td><span class="doc-badge danger">Gagal</span></td>
                            <td>Batal / Gagal dari Provider.</td>
                            <td>Pastikan nomor pelanggan aktif dan coba kembali.</td>
                        </tr>
                        <tr>
                            <td><strong>10</strong></td>
                            <td><span class="doc-badge danger">Gagal</span></td>
                            <td>Saldo Merchant Tidak Mencukupi.</td>
                            <td>Segera lakukan Top Up saldo Digiflazz toko.</td>
                        </tr>
                        <tr>
                            <td><strong>40</strong></td>
                            <td><span class="doc-badge danger">Gagal</span></td>
                            <td>Nomor Tujuan Salah / Format Tidak Sesuai.</td>
                            <td>Periksa kembali digit nomor HP / ID Pelanggan.</td>
                        </tr>
                        <tr>
                            <td><strong>41</strong></td>
                            <td><span class="doc-badge danger">Gagal</span></td>
                            <td>Produk Sedang Gangguan / Maintenance.</td>
                            <td>Pilih produk alternatif dari seller lain.</td>
                        </tr>
                        <tr>
                            <td><strong>42</strong></td>
                            <td><span class="doc-badge danger">Gagal</span></td>
                            <td>Produk Kadaluwarsa / SKU Tidak Aktif.</td>
                            <td>Lakukan Sinkronisasi Harga di Pengaturan PPOB.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 10. TEST CASES / SANDBOX -->
            <div class="doc-section-card" id="test-cases">
                <div class="doc-section-header">
                    <div class="doc-section-icon" style="background:rgba(168,85,247,0.12); color:#a855f7;"><i class="bi bi-bug"></i></div>
                    <div>
                        <h3 class="doc-section-title">10. Panduan Sandbox Testing (Development Mode)</h3>
                        <span class="text-muted small">Pengujian transaksi simulasi dengan Nomor Sakti tanpa memotong saldo riil.</span>
                    </div>
                </div>

                <p>Saat aplikasi diset pada mode <strong>Development (Sandbox)</strong> di menu Pengaturan PPOB, Anda dapat mensimulasikan berbagai skenario transaksi menggunakan Nomor Sakti pengujian berikut:</p>

                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <div class="doc-callout tip m-0">
                            <div class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> 087800001230</div>
                            <div class="small mt-1">Skenario Transaksi Langsung <strong>Sukses</strong> (RC 00 + SN Dummy).</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="doc-callout warning m-0">
                            <div class="fw-bold text-warning"><i class="bi bi-clock-history me-1"></i> 087800001231</div>
                            <div class="small mt-1">Skenario Transaksi <strong>Pending</strong> (Membutuhkan Webhook simulasi).</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="doc-callout danger m-0">
                            <div class="fw-bold text-danger"><i class="bi bi-x-circle-fill me-1"></i> 087800001232</div>
                            <div class="small mt-1">Skenario Transaksi <strong>Gagal</strong> (Otomatis Refund / Saldo kembali).</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="doc-callout important m-0">
                            <div class="fw-bold text-primary"><i class="bi bi-info-circle-fill me-1"></i> 087800001233</div>
                            <div class="small mt-1">Skenario Transaksi Sukses <strong>Tanpa SN</strong> (Serial Number kosong).</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function filterDocContent() {
    const input = document.getElementById('docSearchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.doc-section-card');

    cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        if (text.includes(input)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function setActiveNav(element) {
    document.querySelectorAll('.doc-nav-item').forEach(item => {
        item.classList.remove('active');
    });
    element.classList.add('active');
}

function copyCode(button) {
    const codeBlock = button.parentElement;
    const text = codeBlock.innerText.replace('Copy', '').trim();
    navigator.clipboard.writeText(text).then(() => {
        const originalText = button.innerText;
        button.innerText = 'Copied!';
        button.style.background = '#10b981';
        setTimeout(() => {
            button.innerText = originalText;
            button.style.background = 'rgba(255, 255, 255, 0.15)';
        }, 2000);
    });
}

// Highlight active section on scroll
window.addEventListener('scroll', () => {
    const sections = document.querySelectorAll('.doc-section-card');
    const navItems = document.querySelectorAll('.doc-nav-item');

    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        if (pageYOffset >= sectionTop - 100) {
            current = section.getAttribute('id');
        }
    });

    navItems.forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('href') === `#${current}`) {
            item.classList.add('active');
        }
    });
});
</script>
