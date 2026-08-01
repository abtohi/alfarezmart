<!-- Settings View -->
<div class="page-section">
    <!-- User Profile Section -->
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
    <?php endif; ?>

    <div class="section-title">Pengaturan Umum</div>
    <ul class="menu-list">
        <a href="<?= BASE_URL ?>settings/master-data" class="menu-item">
            <div class="menu-icon" style="background:var(--primary-bg);color:var(--primary);"><i class="bi bi-collection"></i></div>
            <div class="menu-text"><h6>Master Data</h6><small>Kelola brand, kategori, dan satuan</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>settings/app" class="menu-item">
            <div class="menu-icon" style="background: rgba(var(--bs-primary-rgb, 13,110,253), 0.1); color: #0d6efd;"><i class="bi bi-gear"></i></div>
            <div class="menu-text"><h6>Pengaturan Sistem &amp; AI</h6><small>Ganti password dan konfigurasi AI Agent</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>settings/receipt" class="menu-item">
            <div class="menu-icon" style="background:var(--success-bg);color:var(--success);"><i class="bi bi-receipt"></i></div>
            <div class="menu-text"><h6>Pengaturan Struk</h6><small>Logo, header, footer, dan lebar printer</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>suppliers" class="menu-item">
            <div class="menu-icon" style="background:var(--info-bg);color:var(--info);"><i class="bi bi-building"></i></div>
            <div class="menu-text"><h6>Supplier & Sales</h6><small>Kelola data supplier dan kontak</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php if (($currentUser['level'] ?? '') === 'superadmin'): ?>
        <a href="<?= BASE_URL ?>users" class="menu-item">
            <div class="menu-icon" style="background:var(--danger-bg);color:var(--primary);"><i class="bi bi-people"></i></div>
            <div class="menu-text"><h6>Manajemen User</h6><small>Tambah & kelola akun pengguna</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>help" class="menu-item">
            <div class="menu-icon" style="background:var(--warning-bg);color:var(--warning);"><i class="bi bi-question-circle"></i></div>
            <div class="menu-text"><h6>Bantuan</h6><small>Panduan penggunaan aplikasi</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php if (($currentUser['level'] ?? '') === 'superadmin'): ?>
        <a href="<?= BASE_URL ?>setup" class="menu-item">
            <div class="menu-icon" style="background:var(--success-bg);color:var(--success);"><i class="bi bi-database-gear"></i></div>
            <div class="menu-text"><h6>Setup Database</h6><small>Inisialisasi tabel dan data awal</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <?php endif; ?>
    </ul>

    <!-- Fitur Aktif -->
    <div class="section-title" style="margin-top:24px;">Fitur Tersedia</div>
    <ul class="menu-list">
        <a href="<?= BASE_URL ?>sales/pos" class="menu-item">
            <div class="menu-icon" style="background:var(--success-bg);color:var(--success);"><i class="bi bi-receipt"></i></div>
            <div class="menu-text"><h6>Kasir / POS</h6><small>Mode penjualan retail &amp; grosir</small></div>
            <span class="badge-custom badge-success">Aktif</span>
        </a>
        <a href="<?= BASE_URL ?>sales" class="menu-item">
            <div class="menu-icon" style="background:var(--primary-bg);color:var(--primary);"><i class="bi bi-clock-history"></i></div>
            <div class="menu-text"><h6>Riwayat Penjualan</h6><small>Lihat histori transaksi ke pelanggan</small></div>
            <span class="badge-custom badge-success">Aktif</span>
        </a>
        <a href="<?= BASE_URL ?>reports/product-history" class="menu-item">
            <div class="menu-icon" style="background:var(--info-bg);color:var(--info);"><i class="bi bi-arrow-left-right"></i></div>
            <div class="menu-text"><h6>Perbandingan Harga</h6><small>Bandingkan harga antar supplier</small></div>
            <span class="badge-custom badge-success">Aktif</span>
        </a>
        <a href="<?= BASE_URL ?>purchases" class="menu-item">
            <div class="menu-icon" style="background:var(--warning-bg);color:var(--warning);"><i class="bi bi-cart-plus"></i></div>
            <div class="menu-text"><h6>Riwayat Pembelian</h6><small>Histori barang masuk dari supplier</small></div>
            <span class="badge-custom badge-success">Aktif</span>
        </a>
        <a href="<?= BASE_URL ?>scanner" class="menu-item">
            <div class="menu-icon" style="background:var(--info-bg);color:var(--info);"><i class="bi bi-upc-scan"></i></div>
            <div class="menu-text"><h6>Scanner Harga</h6><small>Scan barcode untuk cek harga</small></div>
            <span class="badge-custom badge-success">Aktif</span>
        </a>
    </ul>

    <!-- Fitur Segera Hadir -->
    <div class="section-title" style="margin-top:24px;">Segera Hadir</div>
    <ul class="menu-list">
        <div class="menu-item" onclick="showComingSoon('Catatan Hutang','Pencatatan hutang pelanggan dan piutang','bi-journal-text')" style="cursor:pointer;">
            <div class="menu-icon" style="background:var(--info-bg);color:var(--info);"><i class="bi bi-journal-text"></i></div>
            <div class="menu-text"><h6>Catatan Hutang</h6><small>Pencatatan hutang pelanggan</small></div>
            <span class="badge-custom badge-warning">Soon</span>
        </div>
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
        AlfarezMart v1.1.5 &middot; PWA Inventory System<br>
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
</script>
