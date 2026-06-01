<!-- Dashboard View -->
<div class="page-section">
    <!-- User Profile & Welcome Banner -->
    <?php if (isset($currentUser)): ?>
    <div style="background:var(--gradient-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:20px;margin-bottom:20px;display:flex;align-items:center;gap:16px;">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;color:var(--primary);border:2px solid var(--primary);flex-shrink:0;">
            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-weight:700;font-size:var(--font-size-md);"><?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
            <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:2px;"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
            <span class="badge-custom <?= $currentUser['level'] === 'superadmin' ? 'badge-danger' : ($currentUser['level'] === 'admin' ? 'badge-success' : 'badge-info') ?>" style="margin-top:6px;display:inline-block;">
                <?= ucfirst($currentUser['level'] ?? 'user') ?>
            </span>
        </div>
        <a href="<?= BASE_URL ?>logout" onclick="localStorage.removeItem('alfarezmart_logged_in'); localStorage.removeItem('alfarezmart_user');" style="color:var(--danger);font-size:1.2rem;padding:8px;" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
    <?php else: ?>
    <div style="background: var(--gradient-primary); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -30px; right: 30px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        <h2 style="font-size: var(--font-size-lg); font-weight: 700; margin-bottom: 4px; position: relative;">Selamat Datang! 👋</h2>
        <p style="font-size: var(--font-size-sm); opacity: 0.85; margin: 0; position: relative;">AlfarezMart Inventory System</p>
    </div>
    <?php endif; ?>

    <!-- Status Hari Ini -->
    <div class="section-title">Status Hari Ini</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 24px; align-items: stretch;">
        <!-- Left Column -->
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <!-- Stok Terendah Card -->
            <div class="stat-card" style="margin-bottom: 0; flex: 1; display: flex; align-items: center; gap: 12px; padding: 12px 16px;">
                <div class="stat-icon red" style="margin-bottom: 0; width: 36px; height: 36px; font-size: 1.1rem; flex-shrink: 0;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div style="flex: 1; min-width: 0;">
                    <div class="stat-value" style="font-size: var(--font-size-md); font-weight: 800; line-height: 1.2;"><?= number_format($stats['low_stock_count'] ?? 0) ?></div>
                    <div class="stat-label" style="font-size: 9px; margin-top: 0; text-transform: uppercase; letter-spacing: 0.5px;">Stok Terendah</div>
                </div>
            </div>
            <?php if (($currentUser['level'] ?? '') !== 'staff'): ?>
            <!-- Keuangan/Dompet Card -->
            <a href="<?= BASE_URL ?>finance" class="stat-card" style="margin-bottom: 0; flex: 1; display: flex; align-items: center; gap: 12px; padding: 12px 16px; text-decoration: none; color: inherit; cursor: pointer; transition: background 0.2s;">
                <div class="stat-icon blue" style="margin-bottom: 0; width: 36px; height: 36px; font-size: 1.1rem; flex-shrink: 0;"><i class="bi bi-wallet2"></i></div>
                <div style="flex: 1; min-width: 0;">
                    <div class="stat-value" style="font-size: 11px; font-weight: 800; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Net: Rp <?= number_format(($stats['finance_today']['income'] ?? 0) - ($stats['finance_today']['expense'] ?? 0), 0, ',', '.') ?>">
                        Rp <?= number_format(($stats['finance_today']['income'] ?? 0) - ($stats['finance_today']['expense'] ?? 0), 0, ',', '.') ?>
                    </div>
                    <div class="stat-label" style="font-size: 9px; margin-top: 0; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 4px;">
                        Keuangan Harian <i class="bi bi-chevron-right" style="font-size: 8px;"></i>
                    </div>
                </div>
            </a>
            <?php endif; ?>
        </div>
        <!-- Right Column -->
        <div style="display: flex;">
            <?php if (($currentUser['level'] ?? '') !== 'staff'): ?>
            <!-- Omset Hari Ini Card -->
            <div class="stat-card" style="margin-bottom: 0; flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 16px;">
                <div class="stat-icon green" style="width: 44px; height: 44px; font-size: 1.3rem; margin-bottom: 8px; flex-shrink: 0;"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-value" style="font-size: var(--font-size-md); font-weight: 800;">Rp <?= number_format($stats['today_revenue'] ?? 0, 0, ',', '.') ?></div>
                <div class="stat-label" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Omset Hari Ini</div>
                <div style="font-size: 9px; color: var(--text-muted); margin-top: 4px;">
                    <?= number_format($stats['today_transactions'] ?? 0) ?> transaksi POS
                </div>
            </div>
            <?php else: ?>
            <div class="stat-card" style="margin-bottom: 0; flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 16px;">
                <div class="stat-icon green" style="width: 44px; height: 44px; font-size: 1.3rem; margin-bottom: 8px; flex-shrink: 0;"><i class="bi bi-cart-check"></i></div>
                <div class="stat-value" style="font-size: var(--font-size-md); font-weight: 800;"><?= number_format($stats['today_transactions'] ?? 0) ?></div>
                <div class="stat-label" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">Transaksi Hari Ini</div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="section-title">Ringkasan Data</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-box-seam-fill"></i></div>
            <div class="stat-value"><?= number_format($stats['total_products'] ?? 0) ?></div>
            <div class="stat-label">Total Produk</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-tags-fill"></i></div>
            <div class="stat-value"><?= number_format($stats['total_brands'] ?? 0) ?></div>
            <div class="stat-label">Brand</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-grid-fill"></i></div>
            <div class="stat-value"><?= $stats['total_categories'] ?? 0 ?></div>
            <div class="stat-label">Kategori</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="bi bi-truck"></i></div>
            <div class="stat-value"><?= $stats['total_suppliers'] ?? 0 ?></div>
            <div class="stat-label">Supplier</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="section-title">Aksi Cepat</div>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 24px; text-align: center;">
        <a href="<?= BASE_URL ?>scanner" class="quick-action">
            <div class="action-icon" style="background: var(--danger-bg); color: var(--primary);"><i class="bi bi-upc-scan"></i></div>
            <span class="action-label">Scan Harga</span>
        </a>
        <a href="<?= BASE_URL ?>purchases/create" class="quick-action">
            <div class="action-icon" style="background: var(--success-bg); color: var(--success);"><i class="bi bi-cart-plus"></i></div>
            <span class="action-label">Barang Masuk</span>
        </a>
        <a href="<?= BASE_URL ?>sales/pos" class="quick-action">
            <div class="action-icon" style="background: var(--warning-bg); color: var(--warning);"><i class="bi bi-receipt"></i></div>
            <span class="action-label">Kasir POS</span>
        </a>
        <a href="<?= BASE_URL ?>sales" class="quick-action">
            <div class="action-icon" style="background: var(--primary-bg); color: var(--primary);"><i class="bi bi-clock-history"></i></div>
            <span class="action-label">Riwayat</span>
        </a>
        <a href="javascript:void(0)" onclick="openExportModal()" class="quick-action">
            <div class="action-icon" style="background: rgba(46, 196, 182, 0.1); color: var(--success);"><i class="bi bi-file-earmark-excel"></i></div>
            <span class="action-label">Export Data</span>
        </a>
    </div>

    <!-- Quick Actions Row 2 -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-bottom: 24px;">
        <a href="<?= BASE_URL ?>hitung-orderan" class="quick-action">
            <div class="action-icon" style="background: var(--success-bg); color: var(--success);"><i class="bi bi-clipboard-check"></i></div>
            <span class="action-label">Hitung Orderan</span>
        </a>
        <?php if (($currentUser['level'] ?? '') === 'superadmin'): ?>
        <a href="<?= BASE_URL ?>dashboard/summary" class="quick-action">
            <div class="action-icon" style="background: var(--info-bg); color: var(--info);"><i class="bi bi-graph-up-arrow"></i></div>
            <span class="action-label">Summary</span>
        </a>
        <?php endif; ?>
    </div>

    <!-- Manajemen Data -->
    <div class="section-title">Manajemen Data</div>
    <ul class="menu-list" style="margin-bottom:24px;">
        <a href="<?= BASE_URL ?>products" class="menu-item">
            <div class="menu-icon" style="background: var(--success-bg); color: var(--success);"><i class="bi bi-box-seam"></i></div>
            <div class="menu-text"><h6>Daftar Produk</h6><small>Kelola data, kemasan, harga, dan stok</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>suppliers" class="menu-item">
            <div class="menu-icon" style="background: var(--info-bg); color: var(--info);"><i class="bi bi-building"></i></div>
            <div class="menu-text"><h6>Supplier &amp; Sales</h6><small>Database pemasok barang dan kontak agen</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>settings/master-data" class="menu-item">
            <div class="menu-icon" style="background: var(--primary-bg); color: var(--primary);"><i class="bi bi-database"></i></div>
            <div class="menu-text"><h6>Master Data Utama</h6><small>Atur kategori, merk, dan satuan kemasan</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
    </ul>

    <!-- Laporan & Riwayat -->
    <div class="section-title">Laporan &amp; Riwayat</div>
    <ul class="menu-list" style="margin-bottom:24px;">
        <?php if (($currentUser['level'] ?? '') !== 'staff'): ?>
        <a href="<?= BASE_URL ?>reports" class="menu-item">
            <div class="menu-icon" style="background: var(--warning-bg); color: var(--warning);"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="menu-text"><h6>Laporan Keuangan</h6><small>Ringkasan profit, aset, dan performa omzet</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>sales" class="menu-item">
            <div class="menu-icon" style="background: rgba(var(--bs-primary-rgb, 13,110,253), 0.1); color: #0d6efd;"><i class="bi bi-receipt"></i></div>
            <div class="menu-text"><h6>Riwayat Penjualan</h6><small>Daftar transaksi kasir POS &amp; cetak ulang</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>purchases" class="menu-item">
            <div class="menu-icon" style="background: rgba(var(--bs-success-rgb, 25,135,84), 0.1); color: #198754;"><i class="bi bi-cart-check"></i></div>
            <div class="menu-text"><h6>Riwayat Pembelian</h6><small>Faktur barang masuk &amp; audit stok</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>reports/product-history" class="menu-item">
            <div class="menu-icon" style="background: rgba(var(--bs-danger-rgb, 220,53,69), 0.1); color: #dc3545;"><i class="bi bi-tags"></i></div>
            <div class="menu-text"><h6>Analitik &amp; Histori Produk</h6><small>Rekomendasi harga termurah &amp; riwayat belanja</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php if (($currentUser['level'] ?? '') !== 'staff'): ?>
        <a href="<?= BASE_URL ?>debts" class="menu-item">
            <div class="menu-icon" style="background: var(--info-bg); color: var(--info);"><i class="bi bi-journal-text"></i></div>
            <div class="menu-text"><h6>Catatan Hutang &amp; Piutang</h6><small>Kelola piutang pelanggan &amp; hutang toko</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php endif; ?>
    </ul>

    <!-- Sistem & Dukungan -->
    <div class="section-title">Sistem &amp; Dukungan</div>
    <ul class="menu-list" style="margin-bottom:24px;">
        <?php if (($currentUser['level'] ?? '') !== 'staff'): ?>
        <a href="<?= BASE_URL ?>settings/app" class="menu-item">
            <div class="menu-icon" style="background: rgba(var(--bs-primary-rgb, 13,110,253), 0.1); color: #0d6efd;"><i class="bi bi-gear"></i></div>
            <div class="menu-text"><h6>Pengaturan Sistem &amp; AI</h6><small>Ganti password dan konfigurasi AI Agent</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>settings/app" class="menu-item">
            <div class="menu-icon" style="background: rgba(var(--bs-primary-rgb, 13,110,253), 0.1); color: #0d6efd;"><i class="bi bi-key"></i></div>
            <div class="menu-text"><h6>Ganti Password</h6><small>Ubah kata sandi akun Anda</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>settings/receipt" class="menu-item">
            <div class="menu-icon" style="background: rgba(var(--bs-secondary-rgb, 108,117,125), 0.1); color: #6c757d;"><i class="bi bi-printer"></i></div>
            <div class="menu-text"><h6>Pengaturan Struk</h6><small>Logo toko, header, footer, dan format thermal</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php if (($currentUser['level'] ?? '') === 'superadmin'): ?>
        <a href="<?= BASE_URL ?>users" class="menu-item">
            <div class="menu-icon" style="background:var(--danger-bg);color:var(--primary);"><i class="bi bi-people"></i></div>
            <div class="menu-text"><h6>Manajemen User</h6><small>Tambah & kelola akun pengguna</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>setup" class="menu-item">
            <div class="menu-icon" style="background:var(--success-bg);color:var(--success);"><i class="bi bi-database-gear"></i></div>
            <div class="menu-text"><h6>Setup Database</h6><small>Inisialisasi tabel dan data awal</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>help" class="menu-item">
            <div class="menu-icon" style="background: var(--danger-bg); color: var(--primary);"><i class="bi bi-question-circle"></i></div>
            <div class="menu-text"><h6>Pusat Bantuan &amp; Panduan</h6><small>Cara penggunaan &amp; solusi perbaikan masalah</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
    </ul>

    <!-- Fitur Segera Hadir -->
    <div class="section-title">Segera Hadir</div>
    <ul class="menu-list" style="margin-bottom:24px;">
        <div class="menu-item" onclick="showComingSoon('Laporan Keuangan','Analisa omzet, profit, dan pengeluaran','bi-graph-up')" style="cursor:pointer;">
            <div class="menu-icon" style="background:var(--success-bg);color:var(--success);"><i class="bi bi-graph-up"></i></div>
            <div class="menu-text"><h6>Laporan Keuangan</h6><small>Analisa omzet dan profit</small></div>
            <span class="badge-custom badge-warning">Soon</span>
        </div>
        <div class="menu-item" onclick="showComingSoon('Barang Titipan','Manajemen barang konsinyasi dari supplier','bi-box2-heart')" style="cursor:pointer;">
            <div class="menu-icon" style="background:var(--warning-bg);color:var(--warning);"><i class="bi bi-box2-heart"></i></div>
            <div class="menu-text"><h6>Barang Titipan</h6><small>Konsinyasi dari supplier</small></div>
            <span class="badge-custom badge-warning">Soon</span>
        </div>
        <div class="menu-item" onclick="showComingSoon('AI Invoice Scanner','Foto invoice, data otomatis masuk ke sistem','bi-camera')" style="cursor:pointer;">
            <div class="menu-icon" style="background:var(--primary-bg);color:var(--primary);"><i class="bi bi-camera"></i></div>
            <div class="menu-text"><h6>AI Invoice Scanner</h6><small>Foto invoice, data otomatis masuk</small></div>
            <span class="badge-custom badge-warning">Soon</span>
        </div>
        <div class="menu-item" onclick="showComingSoon('Akun Customer','Login & riwayat belanja untuk pelanggan','bi-person-badge')" style="cursor:pointer;">
            <div class="menu-icon" style="background:var(--danger-bg);color:var(--danger);"><i class="bi bi-person-badge"></i></div>
            <div class="menu-text"><h6>Portal Customer</h6><small>Riwayat belanja pelanggan</small></div>
            <span class="badge-custom badge-warning">Soon</span>
        </div>
    </ul>

    <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:var(--font-size-xs);">
        AlfarezMart v1.1.0 · PWA Inventory System<br>
        &copy; 2026 AlfarezMart
    </div>
</div>

<script>
function showComingSoon(title, desc, icon) {
    AppModal.show({
        title: title,
        subtitle: desc,
        icon: icon || 'bi-clock',
        iconColor: 'var(--warning-bg)',
        iconAccent: 'var(--warning)',
        bodyHTML: `
            <div style="text-align:center;padding:20px 0;">
                <div style="width:80px;height:80px;background:var(--warning-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="${icon || 'bi-clock'}" style="font-size:2rem;color:var(--warning);"></i>
                </div>
                <h3 style="font-size:var(--font-size-md);font-weight:700;margin-bottom:8px;">${title}</h3>
                <p style="color:var(--text-muted);font-size:var(--font-size-sm);margin-bottom:16px;">${desc}</p>
                <div style="background:var(--surface-2);border-radius:var(--radius-lg);padding:16px;">
                    <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin:0;">Fitur ini sedang dalam tahap pengembangan dan akan segera tersedia di pembaruan berikutnya. Nantikan! 🚀</p>
                </div>
            </div>
        `,
        submitText: 'Oke, Mengerti',
        hideCancel: true,
        onSubmit: async () => true
    });
}

// Modal Export JS Logic
let exportSupplierData = [];
async function openExportModal() {
    const html = `
        <style>
            .export-tab { padding: 8px; font-size: 11px; font-weight: 600; text-align: center; border-radius: var(--radius-md); cursor: pointer; flex: 1; transition: 0.2s; }
            .export-tab.active { background: var(--primary); color: white; }
            .export-tab.inactive { background: var(--surface-2); color: var(--text-muted); }
            .export-panel { display: none; margin-top: 15px; }
            .export-panel.active { display: block; }
        </style>
        <div style="display: flex; gap: 8px; margin-bottom: 10px;">
            <div id="tabExport1" class="export-tab active" onclick="switchExportTab(1)">By Supplier</div>
            <div id="tabExport2" class="export-tab inactive" onclick="switchExportTab(2)">By Produk</div>
        </div>
        
        <div id="panelExport1" class="export-panel active">
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Pilih Supplier *</label>
                <div id="exportSupplierSearchContainer1"></div>
            </div>
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Tgl Masuk Dari (Opsional)</label>
                <input type="date" id="exportDateFrom1" class="form-control-dark">
            </div>
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Tgl Masuk Sampai (Opsional)</label>
                <input type="date" id="exportDateTo1" class="form-control-dark">
            </div>
            <button class="btn-primary-custom" onclick="executeExport(1)" style="width: 100%; padding: 10px; border-radius: var(--radius-md);"><i class="bi bi-download"></i> Download .xlsx</button>
        </div>
        
        <div id="panelExport2" class="export-panel">
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Cari Nama Produk (Kosong = Semua)</label>
                <input type="text" id="exportProductName" class="form-control-dark" placeholder="Nama produk..." autocomplete="off">
            </div>
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Filter Supplier (Opsional)</label>
                <div id="exportSupplierSearchContainer2"></div>
            </div>
            <button class="btn-primary-custom" onclick="executeExport(2)" style="width: 100%; padding: 10px; border-radius: var(--radius-md);"><i class="bi bi-download"></i> Download .xlsx</button>
        </div>
    `;

    const modalPromise = AppModal.show({
        title: 'Export Data Produk',
        bodyHTML: html,
        hideFooter: true
    });

    // Fetch supplier data for searchbox
    try {
        const res = await api(`${BASE_URL}api/suppliers`);
        if (res.success) {
            exportSupplierData = res.data;
        }
    } catch (e) {
        console.error("Gagal load supplier", e);
    }

    const supOptions = exportSupplierData.map(s => ({ value: s.id.toString(), label: s.name }));
    
    window.exportSearchBox1 = new SearchBox(document.getElementById('exportSupplierSearchContainer1'), {
        options: supOptions,
        placeholder: '-- Ketik/Pilih Supplier --',
        name: 'exportSupplier1',
        icon: 'bi-truck'
    });

    window.exportSearchBox2 = new SearchBox(document.getElementById('exportSupplierSearchContainer2'), {
        options: supOptions,
        placeholder: '-- Semua Supplier --',
        name: 'exportSupplier2',
        icon: 'bi-truck'
    });
}

window.switchExportTab = function(tabIdx) {
    document.getElementById('tabExport1').className = (tabIdx === 1) ? 'export-tab active' : 'export-tab inactive';
    document.getElementById('tabExport2').className = (tabIdx === 2) ? 'export-tab active' : 'export-tab inactive';
    document.getElementById('panelExport1').className = (tabIdx === 1) ? 'export-panel active' : 'export-panel';
    document.getElementById('panelExport2').className = (tabIdx === 2) ? 'export-panel active' : 'export-panel';
};

window.executeExport = async function(mode) {
    let payload = { mode: mode };
    if (mode === 1) {
        const supId = document.querySelector('input[name="exportSupplier1"]').value;
        if (!supId) {
            showToast("Pilih supplier terlebih dahulu!", "warning");
            return;
        }
        payload.supplier_id = supId;
        payload.date_from = document.getElementById('exportDateFrom1').value;
        payload.date_to = document.getElementById('exportDateTo1').value;
    } else {
        payload.product_name = document.getElementById('exportProductName').value.trim();
        const supId2 = document.querySelector('input[name="exportSupplier2"]').value;
        if(supId2) payload.supplier_id = supId2;
    }

    try {
        const query = new URLSearchParams(payload).toString();
        showToast("Mempersiapkan data ekspor...", "info");
        const res = await api(`${BASE_URL}api/products/export?${query}`);
        
        if (res.success && res.data && res.data.length > 0) {
            if (typeof XLSX === 'undefined') {
                showToast("Library XLSX belum termuat. Pastikan koneksi internet aktif untuk mendownload library.", "error");
                return;
            }
            const ws = XLSX.utils.json_to_sheet(res.data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Data Produk");
            
            const filename = `Export_Produk_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, filename);
            showToast("Berhasil didownload!", "success");
            AppModal.close();
        } else {
            showToast(res.message || "Tidak ada data ditemukan untuk kriteria ini.", "warning");
        }
    } catch (e) {
        showToast(e.message, 'error');
    }
};
</script>
