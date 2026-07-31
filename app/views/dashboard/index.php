<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===== DASHBOARD REDESIGN STYLES ===== */
.dash-container {
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 90px;
}
.dash-kpi-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.dash-kpi-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s, border-color 0.2s;
    text-decoration: none;
    color: inherit;
}
.dash-kpi-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
}
.dash-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.dash-charts-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-bottom: 20px;
}
.dash-chart-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}
.dash-chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.dash-chart-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 6px;
}
.dash-quick-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 24px;
}
.dash-reports-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 24px;
}
.dash-report-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: var(--text-primary);
    transition: transform 0.2s, border-color 0.2s, background 0.2s;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.dash-report-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    background: var(--surface-2);
}
.dash-report-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

/* Desktop Responsive Layout */
@media (min-width: 992px) {
    .dash-kpi-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    .dash-charts-grid {
        grid-template-columns: 1.6fr 1fr;
        gap: 20px;
    }
    .dash-bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .dash-quick-grid {
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
    }
    .dash-reports-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
}
</style>

<div class="page-section dash-container">
    <!-- User Profile & Welcome Banner -->
    <?php if (isset($currentUser)): ?>
    <div style="background:var(--gradient-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:18px 20px;margin-bottom:20px;display:flex;align-items:center;gap:16px;box-shadow:0 4px 20px rgba(0,0,0,0.12);">
        <div style="width:52px;height:52px;border-radius:50%;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;color:var(--primary);border:2px solid var(--primary);flex-shrink:0;">
            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-weight:800;font-size:var(--font-size-md);color:var(--text-primary);"><?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
            <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:2px;"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
            <span class="badge-custom <?= $currentUser['level'] === 'superadmin' ? 'badge-danger' : ($currentUser['level'] === 'admin' ? 'badge-success' : 'badge-info') ?>" style="margin-top:4px;display:inline-block;font-size:10px;">
                <?= ucfirst($currentUser['level'] ?? 'user') ?>
            </span>
        </div>
        <a href="<?= BASE_URL ?>logout" onclick="localStorage.removeItem('alfarezmart_logged_in'); localStorage.removeItem('alfarezmart_user');" style="color:var(--danger);font-size:1.2rem;padding:8px;background:var(--danger-bg);border-radius:10px;display:flex;align-items:center;justify-content:center;" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
    <?php endif; ?>

    <?php $userLevel = $currentUser['level'] ?? 'staff'; ?>

    <!-- 1. STATUS & RINGKASAN HARI INI -->
    <div class="section-title">Status &amp; Ringkasan Hari Ini</div>
    <div class="dash-kpi-grid">

        <?php if ($userLevel === 'superadmin'): ?>
            <!-- Superadmin: Financial & Operational Overview -->
            <div class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:rgba(16,185,129,0.12);color:var(--success);">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Omzet Hari Ini</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--success);margin-top:2px;">
                        Rp <?= number_format($stats['today_revenue'] ?? 0, 0, ',', '.') ?>
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">
                        Profit: <strong style="color:var(--success);">Rp <?= number_format($stats['today_profit'] ?? 0, 0, ',', '.') ?></strong>
                    </div>
                </div>
            </div>

            <a href="<?= BASE_URL ?>finance" class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:rgba(99,102,241,0.12);color:#818cf8;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Keuangan Harian</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        Rp <?= number_format($stats['finance_today']['accumulative_net'] ?? 0, 0, ',', '.') ?>
                    </div>
                    <div style="font-size:10px;color:var(--primary);font-weight:600;margin-top:2px;display:flex;align-items:center;gap:2px;">
                        Detail Dompet <i class="bi bi-chevron-right" style="font-size:9px;"></i>
                    </div>
                </div>
            </a>

            <a href="<?= BASE_URL ?>sales" class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;">
                    <i class="bi bi-receipt"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Transaksi POS</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);margin-top:2px;">
                        <?= number_format($stats['today_transactions'] ?? 0) ?> Struk
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">Penjualan Hari Ini</div>
                </div>
            </a>

            <a href="<?= BASE_URL ?>products?filter=low_stock" class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:var(--danger-bg);color:var(--danger);">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Stok Terendah</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--danger);margin-top:2px;">
                        <?= number_format($stats['low_stock_count'] ?? 0) ?> Produk
                    </div>
                    <div style="font-size:10px;color:var(--danger);font-weight:600;margin-top:2px;">Perlu Restok!</div>
                </div>
            </a>

        <?php elseif ($userLevel === 'admin'): ?>
            <!-- Admin: Operational Overview (NO MONEY INFO) -->
            <a href="<?= BASE_URL ?>sales" class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;">
                    <i class="bi bi-receipt"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Transaksi Hari Ini</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);margin-top:2px;">
                        <?= number_format($stats['today_transactions'] ?? 0) ?> Struk
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">Penjualan Kasir POS</div>
                </div>
            </a>

            <a href="<?= BASE_URL ?>products?filter=low_stock" class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:var(--danger-bg);color:var(--danger);">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Stok Terendah</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--danger);margin-top:2px;">
                        <?= number_format($stats['low_stock_count'] ?? 0) ?> Produk
                    </div>
                    <div style="font-size:10px;color:var(--danger);font-weight:600;margin-top:2px;">Perlu Restok!</div>
                </div>
            </a>

            <a href="<?= BASE_URL ?>products" class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:rgba(16,185,129,0.12);color:var(--success);">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Produk</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);margin-top:2px;">
                        <?= number_format($stats['total_products'] ?? 0) ?> Item
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">Database Produk</div>
                </div>
            </a>

            <a href="<?= BASE_URL ?>suppliers" class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;">
                    <i class="bi bi-truck"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Supplier</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);margin-top:2px;">
                        <?= number_format($stats['total_suppliers'] ?? 0) ?> Mitra
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">Database Supplier</div>
                </div>
            </a>

        <?php else: ?>
            <!-- Staff: Pure Operational Inventory (NO MONEY INFO) -->
            <a href="<?= BASE_URL ?>products" class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:var(--primary-bg);color:var(--primary);">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Produk</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);margin-top:2px;">
                        <?= number_format($stats['total_products'] ?? 0) ?> Item
                    </div>
                </div>
            </a>

            <div class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;">
                    <i class="bi bi-tags-fill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Brand</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);margin-top:2px;">
                        <?= number_format($stats['total_brands'] ?? 0) ?> Merk
                    </div>
                </div>
            </div>

            <div class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:rgba(16,185,129,0.12);color:var(--success);">
                    <i class="bi bi-grid-fill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Kategori</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);margin-top:2px;">
                        <?= number_format($stats['total_categories'] ?? 0) ?> Kategori
                    </div>
                </div>
            </div>

            <a href="<?= BASE_URL ?>suppliers" class="dash-kpi-card">
                <div class="dash-kpi-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;">
                    <i class="bi bi-truck"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Supplier</div>
                    <div style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);margin-top:2px;">
                        <?= number_format($stats['total_suppliers'] ?? 0) ?> Mitra
                    </div>
                </div>
            </a>
        <?php endif; ?>

    </div>

    <!-- 2. AKSI CEPAT MENU (Right below Status Hari Ini!) -->
    <div class="section-title">Aksi Cepat Menu</div>
    <div class="dash-quick-grid">
        <a href="<?= BASE_URL ?>ppob" class="quick-action">
            <div class="action-icon" style="background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(168,85,247,0.15)); color: #818cf8;">
                <i class="bi bi-phone-fill"></i>
            </div>
            <span class="action-label">Produk Digital</span>
        </a>
        <a href="<?= BASE_URL ?>customers" class="quick-action">
            <div class="action-icon" style="background: var(--info-bg); color: var(--info);"><i class="bi bi-people-fill"></i></div>
            <span class="action-label">Pelanggan</span>
        </a>
        <?php if ($userLevel !== 'staff'): ?>
        <a href="<?= BASE_URL ?>debts" class="quick-action">
            <div class="action-icon" style="background: var(--danger-bg); color: var(--primary);"><i class="bi bi-journal-text"></i></div>
            <span class="action-label">Catatan Hutang</span>
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>suppliers" class="quick-action">
            <div class="action-icon" style="background: var(--success-bg); color: var(--success);"><i class="bi bi-building"></i></div>
            <span class="action-label">Supplier &amp; Sales</span>
        </a>
        <a href="<?= BASE_URL ?>reports/product-history" class="quick-action">
            <div class="action-icon" style="background: var(--warning-bg); color: var(--warning);"><i class="bi bi-tags"></i></div>
            <span class="action-label">Histori Produk</span>
        </a>
        <a href="javascript:void(0)" onclick="openSupplierPriceAnalysis()" class="quick-action">
            <div class="action-icon" style="background: rgba(99,102,241,0.12); color: #818cf8;"><i class="bi bi-bar-chart-line-fill"></i></div>
            <span class="action-label">Analisis Harga</span>
        </a>
        <a href="<?= BASE_URL ?>sales" class="quick-action">
            <div class="action-icon" style="background: var(--primary-bg); color: var(--primary);"><i class="bi bi-clock-history"></i></div>
            <span class="action-label">Riwayat</span>
        </a>
        <a href="javascript:void(0)" onclick="openExportModal()" class="quick-action">
            <div class="action-icon" style="background: rgba(46, 196, 182, 0.1); color: var(--success);"><i class="bi bi-file-earmark-excel"></i></div>
            <span class="action-label">Export Data</span>
        </a>
        <a href="<?= BASE_URL ?>hitung-orderan" class="quick-action">
            <div class="action-icon" style="background: var(--success-bg); color: var(--success);"><i class="bi bi-clipboard-check"></i></div>
            <span class="action-label">Hitung Orderan</span>
        </a>
        <a href="<?= BASE_URL ?>catalog" class="quick-action">
            <div class="action-icon" style="background: rgba(233, 30, 99, 0.1); color: #e91e63;"><i class="bi bi-journal-richtext"></i></div>
            <span class="action-label">Buat Katalog</span>
        </a>
        <a href="<?= BASE_URL ?>products/multivariant" class="quick-action">
            <div class="action-icon" style="background: rgba(156, 39, 176, 0.1); color: #9c27b0;"><i class="bi bi-diagram-3-fill"></i></div>
            <span class="action-label">Harga Multivarian</span>
        </a>
        <?php if ($userLevel === 'superadmin'): ?>
        <a href="<?= BASE_URL ?>dashboard/summary" class="quick-action">
            <div class="action-icon" style="background: var(--info-bg); color: var(--info);"><i class="bi bi-graph-up-arrow"></i></div>
            <span class="action-label">Summary</span>
        </a>
        <a href="javascript:void(0)" onclick="openOfflineDownloadModal()" class="quick-action" id="quickActionOffline">
            <div class="action-icon" style="background: rgba(99,102,241,0.12); color: #818cf8; position:relative;" id="offlineIconWrapper">
                <i class="bi bi-cloud-arrow-down-fill" id="offlineQuickIcon"></i>
                <span id="offlineQuickBadge" style="display:none;position:absolute;top:-4px;right:-4px;background:var(--danger);color:#fff;border-radius:50%;width:16px;height:16px;font-size:9px;font-weight:700;display:none;align-items:center;justify-content:center;">!</span>
            </div>
            <span class="action-label">Unduh Offline</span>
        </a>
        <?php endif; ?>
    </div>

    <!-- 3. LAPORAN & RIWAYAT (Modern Grid Format like Aksi Cepat!) -->
    <div class="section-title">Laporan &amp; Riwayat</div>
    <div class="dash-reports-grid">
        <?php if ($userLevel !== 'staff'): ?>
        <a href="<?= BASE_URL ?>reports" class="dash-report-card">
            <div class="dash-report-icon" style="background:var(--warning-bg);color:var(--warning);">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:700;">Laporan Keuangan</div>
                <div style="font-size:10px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Omzet, profit &amp; aset</div>
            </div>
        </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>sales" class="dash-report-card">
            <div class="dash-report-icon" style="background:rgba(13,110,253,0.12);color:#0d6efd;">
                <i class="bi bi-receipt"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:700;">Riwayat Penjualan</div>
                <div style="font-size:10px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Transaksi Kasir POS</div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>purchases" class="dash-report-card">
            <div class="dash-report-icon" style="background:rgba(25,135,84,0.12);color:#198754;">
                <i class="bi bi-cart-check"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:700;">Riwayat Pembelian</div>
                <div style="font-size:10px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Faktur barang masuk</div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>reports/product-history" class="dash-report-card">
            <div class="dash-report-icon" style="background:rgba(220,53,69,0.12);color:#dc3545;">
                <i class="bi bi-tags"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:700;">Histori Produk</div>
                <div style="font-size:10px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Harga &amp; riwayat beli</div>
            </div>
        </a>
    </div>

    <!-- 4. ANALITIK & TREN PENJUALAN (Charts Section) -->
    <?php if ($userLevel !== 'staff'): ?>
    <div class="section-title">Analitik &amp; Tren Penjualan</div>
    <div class="dash-charts-grid">
        <!-- 1. Bar Chart: 7-Day Performance -->
        <div class="dash-chart-card">
            <div class="dash-chart-header">
                <div class="dash-chart-title">
                    <i class="bi bi-bar-chart-line-fill" style="color:var(--primary);"></i>
                    <span><?= $userLevel === 'superadmin' ? 'Omzet & Transaksi (7 Hari)' : 'Volume Transaksi (7 Hari)' ?></span>
                </div>
                <span class="badge-custom badge-primary" style="font-size:10px;">Minggu Ini</span>
            </div>
            <div style="position:relative;height:220px;width:100%;max-width:100%;overflow:hidden;">
                <canvas id="chartWeeklySales"></canvas>
            </div>
        </div>

        <!-- 2. Pie/Doughnut Chart: Top Categories -->
        <div class="dash-chart-card">
            <div class="dash-chart-header">
                <div class="dash-chart-title">
                    <i class="bi bi-pie-chart-fill" style="color:#818cf8;"></i>
                    <span>Kategori Terlaris (30 Hari)</span>
                </div>
            </div>
            <div style="position:relative;height:220px;width:100%;max-width:100%;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                <canvas id="chartTopCategories"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Top Produk Today Widget -->
    <div class="dash-chart-card" style="margin-bottom:24px;">
        <div class="dash-chart-header">
            <div class="dash-chart-title">
                <i class="bi bi-trophy-fill" style="color:#f59e0b;"></i>
                <span>Top 5 Produk Terlaris Hari Ini</span>
            </div>
            <span class="badge-custom badge-warning" style="font-size:10px;">Hari Ini</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <?php if (!empty($topProductsToday)): ?>
                <?php foreach ($topProductsToday as $idx => $prod): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--surface-2);border-radius:var(--radius-md);">
                    <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1;">
                        <div style="width:24px;height:24px;border-radius:50%;background:<?= $idx === 0 ? '#f59e0b' : ($idx === 1 ? '#94a3b8' : ($idx === 2 ? '#b45309' : 'var(--surface-3)')) ?>;color:white;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;">
                            <?= $idx + 1 ?>
                        </div>
                        <div style="font-size:12px;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= htmlspecialchars($prod['name']) ?>
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <span style="font-size:12px;font-weight:800;color:var(--primary);"><?= number_format($prod['qty']) ?> pcs</span>
                        <?php if ($userLevel === 'superadmin'): ?>
                            <div style="font-size:10px;color:var(--text-muted);">Rp <?= number_format($prod['revenue'], 0, ',', '.') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center;padding:24px 0;color:var(--text-muted);font-size:12px;">
                    <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:4px;"></i>
                    Belum ada transaksi penjualan hari ini
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5. MANAJEMEN DATA -->
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
        <a href="<?= BASE_URL ?>customers" class="menu-item">
            <div class="menu-icon" style="background: var(--primary-bg); color: var(--primary);"><i class="bi bi-people-fill"></i></div>
            <div class="menu-text"><h6>Database Pelanggan</h6><small>Kelola data pelanggan dan kategori tier harga</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>settings/master-data" class="menu-item">
            <div class="menu-icon" style="background: var(--primary-bg); color: var(--primary);"><i class="bi bi-database"></i></div>
            <div class="menu-text"><h6>Master Data Utama</h6><small>Atur kategori, merk, dan satuan kemasan</small></div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
    </ul>

    <!-- 6. SISTEM & DUKUNGAN -->
    <div class="section-title">Sistem &amp; Dukungan</div>
    <ul class="menu-list" style="margin-bottom:24px;">
        <?php if ($userLevel !== 'staff'): ?>
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
        <?php if ($userLevel === 'superadmin'): ?>
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

    <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:var(--font-size-xs);">
        AlfarezMart v1.1.4 &middot; PWA Inventory System<br>
        &copy; 2026 AlfarezMart
    </div>
</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($userLevel !== 'staff'): ?>
    const weeklyData = <?= json_encode($weeklySeries ?? []) ?>;
    const topCatData = <?= json_encode($topCategories ?? []) ?>;
    const isSuperadmin = <?= json_encode($userLevel === 'superadmin') ?>;

    // 1. Weekly Sales Bar Chart
    const ctxWeekly = document.getElementById('chartWeeklySales');
    if (ctxWeekly && typeof Chart !== 'undefined') {
        const labels = weeklyData.map(d => d.label);
        const dataValues = weeklyData.map(d => isSuperadmin ? d.revenue : d.transactions);
        
        new Chart(ctxWeekly.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: isSuperadmin ? 'Omzet (Rp)' : 'Transaksi',
                    data: dataValues,
                    backgroundColor: isSuperadmin ? 'rgba(230, 57, 70, 0.75)' : 'rgba(59, 130, 246, 0.75)',
                    borderColor: isSuperadmin ? '#e63946' : '#3b82f6',
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.raw || 0;
                                return isSuperadmin ? ' Omzet: Rp ' + val.toLocaleString('id-ID') : ' Transaksi: ' + val;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b7394', font: { size: 9 } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: {
                            color: '#6b7394',
                            font: { size: 9 },
                            callback: function(value) {
                                if (!isSuperadmin) return value;
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Top Categories Doughnut Chart
    const ctxCat = document.getElementById('chartTopCategories');
    if (ctxCat && typeof Chart !== 'undefined') {
        const catLabels = topCatData.map(c => c.category_name);
        const catValues = topCatData.map(c => isSuperadmin ? parseFloat(c.total_revenue) : parseInt(c.total_qty));
        
        const colors = [
            '#e63946', '#3b82f6', '#10b981', '#f59e0b', '#818cf8', '#ec4899'
        ];

        new Chart(ctxCat.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: catLabels.length > 0 ? catLabels : ['Belum Ada Data'],
                datasets: [{
                    data: catValues.length > 0 ? catValues : [1],
                    backgroundColor: catLabels.length > 0 ? colors.slice(0, catLabels.length) : ['#334155'],
                    borderWidth: 2,
                    borderColor: 'var(--surface-1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#a0a8c0',
                            font: { size: 9 },
                            padding: 6,
                            boxWidth: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.raw || 0;
                                return isSuperadmin ? ' ' + context.label + ': Rp ' + val.toLocaleString('id-ID') : ' ' + context.label + ': ' + val + ' pcs';
                            }
                        }
                    }
                },
                layout: {
                    padding: { top: 0, bottom: 4, left: 4, right: 4 }
                },
                cutout: '60%'
            }
        });
    }
    <?php endif; ?>
});
</script>

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
        <a href="<?= BASE_URL ?>customers" class="menu-item">
            <div class="menu-icon" style="background: var(--primary-bg); color: var(--primary);"><i class="bi bi-people-fill"></i></div>
            <div class="menu-text"><h6>Database Pelanggan</h6><small>Kelola data pelanggan dan kategori tier harga</small></div>
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
        <?php if ($userLevel !== 'staff'): ?>
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
        <?php if ($userLevel !== 'staff'): ?>
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
        <?php if ($userLevel !== 'staff'): ?>
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
        <?php if ($userLevel === 'superadmin'): ?>
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

    <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:var(--font-size-xs);">
        AlfarezMart v1.1.4 &middot; PWA Inventory System<br>
        &copy; 2026 AlfarezMart
    </div>
</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($userLevel !== 'staff'): ?>
    const weeklyData = <?= json_encode($weeklySeries ?? []) ?>;
    const topCatData = <?= json_encode($topCategories ?? []) ?>;
    const isSuperadmin = <?= json_encode($userLevel === 'superadmin') ?>;

    // 1. Weekly Sales Bar Chart
    const ctxWeekly = document.getElementById('chartWeeklySales');
    if (ctxWeekly && typeof Chart !== 'undefined') {
        const labels = weeklyData.map(d => d.label);
        const dataValues = weeklyData.map(d => isSuperadmin ? d.revenue : d.transactions);
        
        new Chart(ctxWeekly.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: isSuperadmin ? 'Omzet (Rp)' : 'Transaksi',
                    data: dataValues,
                    backgroundColor: isSuperadmin ? 'rgba(230, 57, 70, 0.75)' : 'rgba(59, 130, 246, 0.75)',
                    borderColor: isSuperadmin ? '#e63946' : '#3b82f6',
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.raw || 0;
                                return isSuperadmin ? ' Omzet: Rp ' + val.toLocaleString('id-ID') : ' Transaksi: ' + val;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b7394', font: { size: 10 } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: {
                            color: '#6b7394',
                            font: { size: 10 },
                            callback: function(value) {
                                if (!isSuperadmin) return value;
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Top Categories Doughnut Chart
    const ctxCat = document.getElementById('chartTopCategories');
    if (ctxCat && typeof Chart !== 'undefined') {
        const catLabels = topCatData.map(c => c.category_name);
        const catValues = topCatData.map(c => isSuperadmin ? parseFloat(c.total_revenue) : parseInt(c.total_qty));
        
        const colors = [
            '#e63946', '#3b82f6', '#10b981', '#f59e0b', '#818cf8', '#ec4899'
        ];

        new Chart(ctxCat.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: catLabels.length > 0 ? catLabels : ['Belum Ada Data'],
                datasets: [{
                    data: catValues.length > 0 ? catValues : [1],
                    backgroundColor: catLabels.length > 0 ? colors.slice(0, catLabels.length) : ['#334155'],
                    borderWidth: 2,
                    borderColor: 'var(--surface-1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#a0a8c0',
                            font: { size: 10 },
                            padding: 10,
                            boxWidth: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.raw || 0;
                                return isSuperadmin ? ' ' + context.label + ': Rp ' + val.toLocaleString('id-ID') : ' ' + context.label + ': ' + val + ' pcs';
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
    <?php endif; ?>
});
</script>

<script>
// Utility helpers
function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

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
let exportProductData = [];
async function openExportModal() {
    // Load data first, THEN show modal (fixes race condition)
    showToast('Memuat data...', 'info');
    
    try {
        const [supRes, prodRes] = await Promise.all([
            api(`${BASE_URL}api/suppliers`),
            api(`${BASE_URL}api/products/names`)
        ]);
        exportSupplierData = supRes.success ? supRes.data : (Array.isArray(supRes) ? supRes : []);
        const rawProd = prodRes.success ? (prodRes.data.data || prodRes.data) : (Array.isArray(prodRes) ? prodRes : []);
        exportProductData = Array.isArray(rawProd) ? rawProd : [];
    } catch (e) {
        console.error('Gagal load data untuk export', e);
        exportSupplierData = [];
        exportProductData = [];
    }

    const today = new Date().toISOString().split('T')[0];
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
            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                <div class="modal-form-group" style="flex: 1; text-align: left;">
                    <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Tgl Dari (Opsional)</label>
                    <input type="date" id="exportDateFrom1" class="form-control-dark" value="${today}">
                </div>
                <div class="modal-form-group" style="flex: 1; text-align: left;">
                    <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Tgl Sampai (Opsional)</label>
                    <input type="date" id="exportDateTo1" class="form-control-dark" value="${today}">
                </div>
            </div>
            <button class="btn-primary-custom" onclick="executeExport(1)" style="width: 100%; padding: 10px; border-radius: var(--radius-md);"><i class="bi bi-download"></i> Download .xlsx</button>
        </div>
        
        <div id="panelExport2" class="export-panel">
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Cari Nama Produk (Kosong = Semua Produk)</label>
                <div id="exportProductSearchContainer"></div>
            </div>
            <div class="modal-form-group" style="margin-bottom: 12px; text-align: left;">
                <label style="font-size: var(--font-size-xs); color: var(--text-muted);">Filter Supplier (Opsional)</label>
                <div id="exportSupplierSearchContainer2"></div>
            </div>
            <button class="btn-primary-custom" onclick="executeExport(2)" style="width: 100%; padding: 10px; border-radius: var(--radius-md);"><i class="bi bi-download"></i> Download .xlsx</button>
        </div>
    `;

    AppModal.show({
        title: 'Export Data Produk',
        bodyHTML: html,
        hideFooter: true,
        centered: true
    });

    // Now safely init SearchBoxes - DOM is ready
    const supOptions = exportSupplierData.map(s => ({ value: s.id.toString(), label: s.name }));
    const prodOptions = exportProductData
        .map(p => {
            const name = p.full_name || p.short_label || p.name || '';
            return {
                value: name,
                label: name,
                brand: p.brand_name ? ` (${p.brand_name})` : ''
            };
        })
        .filter(o => o.value);

    // Display data counts for debugging
    console.log(`Export Data Loaded: ${supOptions.length} suppliers, ${prodOptions.length} products`);
    
    if (prodOptions.length === 0) {
        showToast('Perhatian: Data produk kosong atau gagal dimuat', 'warning');
    }

    window.exportSearchBox1 = new SearchBox(document.getElementById('exportSupplierSearchContainer1'), {
        options: supOptions, placeholder: '-- Ketik/Pilih Supplier --', name: 'exportSupplier1', icon: 'bi-truck'
    });
    window.exportSearchBox2 = new SearchBox(document.getElementById('exportSupplierSearchContainer2'), {
        options: supOptions, placeholder: '-- Semua Supplier --', name: 'exportSupplier2', icon: 'bi-truck'
    });
    window.exportProductBox = new SearchBox(document.getElementById('exportProductSearchContainer'), {
        options: prodOptions, placeholder: `-- Ketik Nama Produk (${prodOptions.length} produk) --`, name: 'exportProductName', icon: 'bi-box'
    });
}

window.switchExportTab = function(tabIdx) {
    document.getElementById('tabExport1').className = (tabIdx === 1) ? 'export-tab active' : 'export-tab inactive';
    document.getElementById('tabExport2').className = (tabIdx === 2) ? 'export-tab active' : 'export-tab inactive';
    document.getElementById('panelExport1').className = (tabIdx === 1) ? 'export-panel active' : 'export-panel';
    document.getElementById('panelExport2').className = (tabIdx === 2) ? 'export-panel active' : 'export-panel';
};

// ==========================================
// OFFLINE DOWNLOAD MODAL
// ==========================================
window.openOfflineDownloadModal = async function() {
    // Check current offline status
    let cachedCount = 0;
    let pendingCount = 0;
    try {
        if (window.OfflineDB) {
            const products = await window.OfflineDB.getAllProducts();
            cachedCount = products.length;
            pendingCount = await window.OfflineDB.countPending();
        }
    } catch(e) {}

    const lastSync = localStorage.getItem('alfarezmart_last_full_sync');
    const lastSyncStr = lastSync ? new Date(parseInt(lastSync)).toLocaleString('id-ID') : 'Belum pernah';
    const isOnline = navigator.onLine;

    const html = `
        <div style="text-align:center;padding:8px 0 16px;">
            <div style="width:64px;height:64px;background:rgba(99,102,241,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <i class="bi bi-cloud-arrow-down-fill" style="font-size:2rem;color:#818cf8;"></i>
            </div>
            <h5 style="font-weight:700;margin-bottom:4px;">Mode Offline Penuh</h5>
            <p style="font-size:0.8rem;color:var(--text-muted);margin:0;">Unduh semua data ke perangkat ini agar aplikasi<br>bisa berjalan sepenuhnya tanpa internet.</p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
            <div style="background:var(--surface-1);border-radius:var(--radius-md);padding:12px;border:1px solid var(--border-color);text-align:center;">
                <div style="font-size:1.5rem;font-weight:800;color:var(--primary);">${cachedCount}</div>
                <div style="font-size:0.7rem;color:var(--text-muted);">Produk Tersimpan</div>
            </div>
            <div style="background:var(--surface-1);border-radius:var(--radius-md);padding:12px;border:1px solid var(--border-color);text-align:center;">
                <div style="font-size:1.5rem;font-weight:800;color:${pendingCount > 0 ? 'var(--warning)' : 'var(--success)'};">${pendingCount}</div>
                <div style="font-size:0.7rem;color:var(--text-muted);">Menunggu Sinkron</div>
            </div>
        </div>

        <div style="background:var(--surface-1);border-radius:var(--radius-md);padding:12px;border:1px solid var(--border-color);margin-bottom:16px;font-size:0.78rem;color:var(--text-muted);">
            <i class="bi bi-clock-history" style="margin-right:6px;"></i> Sinkron terakhir: <strong style="color:var(--text-primary);">${lastSyncStr}</strong>
        </div>

        ${!isOnline ? `
        <div style="background:var(--warning-bg);border:1px solid var(--warning);border-radius:var(--radius-md);padding:10px 14px;margin-bottom:14px;font-size:0.8rem;color:var(--warning);display:flex;align-items:center;gap:8px;">
            <i class="bi bi-wifi-off"></i> Tidak ada koneksi internet. Tidak dapat mengunduh data baru.
        </div>` : ''}

        <div id="offlineDlProgress" style="display:none;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--text-muted);margin-bottom:6px;">
                <span id="offlineDlStep">Menginisialisasi...</span>
                <span id="offlineDlPct">0%</span>
            </div>
            <div style="background:var(--surface-2);border-radius:99px;height:8px;overflow:hidden;">
                <div id="offlineDlBar" style="height:100%;width:0%;background:linear-gradient(90deg,#818cf8,#6366f1);border-radius:99px;transition:width 0.4s ease;"></div>
            </div>
        </div>

        <div id="offlineDlSteps" style="margin-bottom:16px;">
            <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:8px;">Yang akan diunduh:</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                ${['Produk & Harga', 'Supplier & Tipe', 'Kategori', 'Keuangan'].map((s,i) => `
                <div style="display:flex;align-items:center;gap:8px;font-size:0.8rem;" id="dlStep${i}">
                    <div style="width:20px;height:20px;border-radius:50%;background:var(--surface-2);display:flex;align-items:center;justify-content:center;font-size:10px;" id="dlStepIcon${i}">
                        <i class="bi bi-circle" style="color:var(--text-muted);"></i>
                    </div>
                    <span style="color:var(--text-secondary);">${s}</span>
                </div>`).join('')}
            </div>
        </div>
    `;

    AppModal.show({
        title: 'Unduh Data Offline',
        bodyHTML: html,
        submitText: isOnline ? '<i class="bi bi-cloud-arrow-down"></i> Mulai Download' : null,
        cancelText: 'Tutup',
        hideFooter: false,
        onSubmit: isOnline ? async () => {
            await _executeOfflineDownload();
        } : null,
        centered: true
    });
};

window._executeOfflineDownload = async function() {
    const STEP_KEYS = ['products', 'suppliers', 'categories', 'finance'];
    const STEP_LABELS = {
        products:   'Menyimpan Produk & Harga...',
        suppliers:  'Menyimpan Supplier...',
        categories: 'Menyimpan Kategori...',
        finance:    'Menyimpan Data Keuangan...',
    };
    const STEP_IDX = { products: 0, suppliers: 1, categories: 2, finance: 3 };

    const progressEl = document.getElementById('offlineDlProgress');
    const barEl      = document.getElementById('offlineDlBar');
    const stepEl     = document.getElementById('offlineDlStep');
    const pctEl      = document.getElementById('offlineDlPct');

    // Disable submit button to prevent double-click
    const submitBtn = document.querySelector('#appModalEl .btn-primary-custom');
    if (submitBtn) submitBtn.disabled = true;

    if (progressEl) progressEl.style.display = 'block';

    function setStep(idx, status) {
        const iconEl   = document.getElementById(`dlStepIcon${idx}`);
        const stepDiv  = document.getElementById(`dlStep${idx}`);
        if (!iconEl || !stepDiv) return;
        if (status === 'loading') {
            iconEl.innerHTML = '<div class="spinner-border" style="width:12px;height:12px;border-width:2px;color:#818cf8;"></div>';
            stepDiv.querySelector('span').style.color = '#818cf8';
        } else if (status === 'done') {
            iconEl.innerHTML = '<i class="bi bi-check-circle-fill" style="color:var(--success);font-size:14px;"></i>';
            stepDiv.querySelector('span').style.color = 'var(--text-primary)';
        } else {
            iconEl.innerHTML = '<i class="bi bi-x-circle-fill" style="color:var(--danger);font-size:14px;"></i>';
        }
    }

    function updateProgress(completedSteps) {
        // 10% reserved for fetch phase, 90% split among 4 stores
        const pct = Math.round(10 + (completedSteps / STEP_KEYS.length) * 90);
        if (barEl) barEl.style.width = pct + '%';
        if (pctEl) pctEl.textContent = pct + '%';
    }

    try {
        // PHASE 1: Fetch from server
        if (stepEl) stepEl.textContent = 'Menghubungi server...';
        if (barEl)  barEl.style.width  = '5%';
        if (pctEl)  pctEl.textContent  = '5%';

        const response = await fetch(`${BASE_URL}api/sync/all?_t=${Date.now()}`, { credentials: 'same-origin' });
        if (!response.ok) throw new Error(`Server error ${response.status}`);
        const data = await response.json();
        if (data.error) throw new Error(data.error);

        if (barEl) barEl.style.width = '10%';
        if (pctEl) pctEl.textContent = '10%';

        // PHASE 2: Save to IndexedDB per-store with visual callbacks
        if (window.OfflineDB) {
            await window.OfflineDB.init();

            let completedSteps = 0;

            await window.OfflineDB.saveFromPayload(data, (stepKey) => {
                // Called BEFORE each store is saved — update UI
                const idx = STEP_IDX[stepKey];
                if (idx !== undefined) {
                    // Mark previous as done
                    if (completedSteps > 0) setStep(STEP_IDX[STEP_KEYS[completedSteps - 1]], 'done');
                    setStep(idx, 'loading');
                    if (stepEl) stepEl.textContent = STEP_LABELS[stepKey] || stepKey;
                }
            });

            // Mark all remaining steps as done
            STEP_KEYS.forEach((key, idx) => {
                setStep(idx, 'done');
                updateProgress(idx + 1);
            });
        } else {
            // Fallback: just show steps as done with delays
            for (let i = 0; i < STEP_KEYS.length; i++) {
                setStep(i, 'loading');
                if (stepEl) stepEl.textContent = STEP_LABELS[STEP_KEYS[i]];
                await new Promise(r => setTimeout(r, 200));
                setStep(i, 'done');
                updateProgress(i + 1);
            }
        }

        // PHASE 3: Done!
        if (stepEl) stepEl.textContent = '✅ Download selesai!';
        if (barEl) barEl.style.width = '100%';
        if (pctEl) pctEl.textContent = '100%';
        if (barEl) barEl.style.background = 'linear-gradient(90deg,var(--success),#10b981)';

        localStorage.setItem('alfarezmart_last_full_sync', Date.now().toString());

        if (typeof updateSyncBadge === 'function') updateSyncBadge();

        const prodCount = data.products?.length || 0;
        showToast(`✅ Berhasil! ${prodCount} produk + supplier, kategori & keuangan tersimpan offline.`, 'success', 6000);

        setTimeout(() => AppModal.close(), 1800);

    } catch (err) {
        if (stepEl) stepEl.textContent = '❌ Gagal: ' + err.message;
        if (barEl) barEl.style.background = 'linear-gradient(90deg,var(--danger),#ef4444)';
        showToast('Gagal mengunduh data offline: ' + err.message, 'error');
        if (submitBtn) submitBtn.disabled = false;
    }
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
        const productNameInput = document.querySelector('input[name="exportProductName"]');
        payload.product_name = productNameInput ? productNameInput.value.trim() : '';
        const supInput2 = document.querySelector('input[name="exportSupplier2"]');
        if(supInput2 && supInput2.value) payload.supplier_id = supInput2.value;
    }

    try {
        const query = new URLSearchParams(payload).toString();
        showToast("Mempersiapkan data ekspor...", "info");
        const res = await api(`${BASE_URL}api/products/export?${query}`);
        
        if (res.success && res.data && res.data.length > 0) {
            if (typeof ExcelJS === 'undefined') {
                showToast("Library ExcelJS belum termuat. Pastikan koneksi internet aktif.", "error");
                return;
            }

            // ─── Rename kolom "Satuan atau jenis kemasan" → "Satuan" ───
            const renameMap = { 'Satuan atau jenis kemasan': 'Satuan' };
            const priceKeys = ['Harga Beli Terakhir', 'Harga Beli (Satuan)', 'Harga Total'];

            const rawData = res.data;
            const originalKeys = Object.keys(rawData[0]);
            const renamedKeys = originalKeys.map(k => renameMap[k] || k);

            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Data Produk', {
                views: [{ state: 'frozen', xSplit: 0, ySplit: 1 }]
            });

            // Set Headers
            worksheet.columns = renamedKeys.map((key, index) => {
                const origKey = originalKeys[index];
                
                // Hitung lebar kolom dinamis
                let maxLen = key.length;
                rawData.forEach(row => {
                    const val = row[origKey];
                    const len = val !== null && val !== undefined ? String(val).length : 0;
                    if (len > maxLen) maxLen = len;
                });
                
                return {
                    header: key,
                    key: key,
                    width: Math.min(Math.max(maxLen + 2, 8), 40)
                };
            });

            // Style Header Row
            const headerRow = worksheet.getRow(1);
            headerRow.eachCell((cell) => {
                cell.font = { name: 'Arial Narrow', size: 10, bold: true, color: { argb: 'FFFFFFFF' } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF963634' } };
                cell.alignment = { vertical: 'middle', horizontal: 'center' };
                cell.border = {
                    top: { style: 'thin', color: { argb: 'FFFFFFFF' } },
                    bottom: { style: 'thin', color: { argb: 'FFFFFFFF' } },
                    left: { style: 'thin', color: { argb: 'FFFFFFFF' } },
                    right: { style: 'thin', color: { argb: 'FFFFFFFF' } }
                };
            });

            // Add Data
            rawData.forEach(row => {
                const newRow = {};
                originalKeys.forEach((k, i) => { newRow[renamedKeys[i]] = row[k]; });
                const addedRow = worksheet.addRow(newRow);
                
                // Style Data Row
                addedRow.eachCell((cell, colNumber) => {
                    const colKey = renamedKeys[colNumber - 1];
                    const isPrice = priceKeys.includes(colKey);
                    
                    cell.font = { name: 'Arial Narrow', size: 10 };
                    cell.alignment = { vertical: 'top' };
                    
                    if (isPrice && typeof cell.value === 'number') {
                        cell.numFmt = '"Rp "#,##0';
                        cell.alignment = { vertical: 'top', horizontal: 'right' };
                    }
                });
            });

            // Trigger Download
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            
            const filename = `Export_Produk_${new Date().toISOString().slice(0,10)}.xlsx`;
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);
            
            showToast("Berhasil didownload!", "success");
            AppModal.close();
        } else {
            showToast(res.message || "Tidak ada data ditemukan untuk kriteria ini.", "warning");
        }
    } catch (e) {
        showToast(e.message, 'error');
    }
};

// ==========================================
// SUPPLIER PRICE ANALYSIS MODAL
// ==========================================
let _spaSupplierSB = null;
let _spaCurrentSupplierId = null;
let _spaSupplierData = [];

window.openSupplierPriceAnalysis = async function() {
    // Load suppliers list
    showToast('Memuat data supplier...', 'info');
    try {
        const res = await api(`${BASE_URL}api/reports/suppliers-for-comparison`);
        _spaSupplierData = (res.success ? (res.data || res) : (Array.isArray(res) ? res : []));
        if (!Array.isArray(_spaSupplierData)) _spaSupplierData = [];
    } catch(e) {
        _spaSupplierData = [];
    }

    const html = `
        <style>
            .spa-supplier-picker { margin-bottom: 16px; }
            .spa-results-wrap { min-height: 120px; }
            .spa-empty {
                text-align:center; padding: 40px 0; color: var(--text-muted);
            }
            .spa-empty i { font-size: 2.5rem; margin-bottom: 12px; display:block; opacity:0.4; }
            .spa-product-card {
                background: var(--surface-1);
                border: 1px solid var(--border-color);
                border-radius: var(--radius-lg);
                padding: 14px;
                margin-bottom: 10px;
                transition: border-color 0.2s;
            }
            .spa-product-card.spa-cheapest {
                border-color: var(--success);
                background: linear-gradient(135deg, var(--success-bg) 0%, var(--surface-1) 100%);
            }
            .spa-product-card.spa-expensive {
                border-color: var(--danger);
            }
            .spa-prod-header { display:flex; align-items:flex-start; gap:10px; margin-bottom:10px; }
            .spa-prod-icon {
                width:36px; height:36px; border-radius:var(--radius-md);
                display:flex; align-items:center; justify-content:center;
                font-size:1rem; flex-shrink:0;
            }
            .spa-prod-name { font-weight:700; font-size:var(--font-size-sm); line-height:1.3; }
            .spa-prod-cat { font-size:var(--font-size-xs); color:var(--text-muted); margin-top:2px; }
            .spa-badge {
                font-size:9px; font-weight:700; padding:3px 7px;
                border-radius:99px; white-space:nowrap; flex-shrink:0;
            }
            .spa-badge-best { background:var(--success-bg); color:var(--success); }
            .spa-badge-expensive { background:var(--danger-bg); color:var(--danger); }
            .spa-price-table { width:100%; border-collapse:collapse; font-size:var(--font-size-xs); }
            .spa-price-table th {
                color:var(--text-muted); font-weight:600; text-align:left;
                padding:4px 6px; border-bottom:1px solid var(--border-color);
                font-size:9px; text-transform:uppercase; letter-spacing:0.5px;
            }
            .spa-price-table td { padding:5px 6px; vertical-align:middle; }
            .spa-price-table tr.spa-row-selected { background: rgba(99,102,241,0.08); }
            .spa-price-table tr.spa-row-cheapest-other td:first-child::before {
                content: '★ ';
                color: var(--success);
            }
            .spa-norm-price { font-weight:700; font-size:11px; color:var(--text-primary); }
            .spa-date-label { font-size:9px; color:var(--text-muted); }
            .spa-loading-card {
                background:var(--surface-1); border:1px solid var(--border-color);
                border-radius:var(--radius-lg); padding:16px; margin-bottom:10px;
                animation: spa-pulse 1.2s ease-in-out infinite;
            }
            @keyframes spa-pulse {
                0%, 100% { opacity:1; }
                50% { opacity:0.4; }
            }
            .spa-skel { background:var(--surface-2); border-radius:4px; height:12px; }
            .spa-summary-bar {
                display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap;
            }
            .spa-summary-chip {
                background:var(--surface-2); border-radius:var(--radius-md);
                padding:8px 12px; font-size:var(--font-size-xs); text-align:center; flex:1; min-width:70px;
            }
            .spa-summary-chip strong { display:block; font-size:var(--font-size-sm); color:var(--text-primary); font-weight:800; }
        </style>

        <div class="spa-supplier-picker">
            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:6px;"><i class="bi bi-building" style="margin-right:5px;"></i>Pilih Supplier</label>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <div id="spaSupplierSearchboxWrap" style="flex:1;"></div>
                <button id="spaBtnCari" type="button" onclick="_spaTriggerSearch()" style="height:44px;padding:0 16px;display:flex;align-items:center;gap:6px;white-space:nowrap;flex-shrink:0;background:var(--surface-1);border:1px solid var(--border-color);color:var(--text-primary);border-radius:var(--radius-md);font-weight:600;font-size:var(--font-size-sm);cursor:pointer;transition:all 0.2s;">
                    <i class="bi bi-search" style="color:var(--primary);"></i> Cari
                </button>
            </div>
            <div id="spaNoSuppliersMsg" style="display:none;margin-top:8px;font-size:var(--font-size-xs);color:var(--warning);">
                <i class="bi bi-exclamation-triangle"></i> Belum ada data supplier yang bisa dibandingkan. Pastikan ada produk yang pernah dibeli dari lebih dari satu supplier.
            </div>
        </div>

        <div id="spaResultsWrap" class="spa-results-wrap">
            <div class="spa-empty">
                <i class="bi bi-bar-chart-line"></i>
                <div style="font-weight:600;margin-bottom:4px;">Pilih supplier lalu klik Cari</div>
                <div style="font-size:var(--font-size-xs);">Hanya produk yang dibeli dari lebih dari 1 supplier yang akan ditampilkan</div>
            </div>
        </div>
    `;


    AppModal.show({
        title: 'Analisis Harga Supplier',
        subtitle: 'Komparasi harga beli antar supplier',
        icon: 'bi-bar-chart-line-fill',
        iconColor: 'rgba(99,102,241,0.12)',
        iconAccent: '#818cf8',
        bodyHTML: html,
        hideFooter: true,
        centered: true,
    });

    // Init SearchBox after DOM is ready
    const supOpts = _spaSupplierData.map(s => ({ value: String(s.id), label: s.name }));

    if (supOpts.length === 0) {
        const noMsg = document.getElementById('spaNoSuppliersMsg');
        if (noMsg) noMsg.style.display = 'block';
    }

    _spaSupplierSB = new SearchBox(document.getElementById('spaSupplierSearchboxWrap'), {
        options: supOpts,
        placeholder: '-- Ketik atau pilih supplier --',
        name: 'spaSupplier',
        icon: 'bi-truck',
        onSelect: (val, label) => {
            _spaCurrentSupplierId = val;
            _spaLoadComparison(val, label);
        }
    });
};

window._spaTriggerSearch = function() {
    if (!_spaCurrentSupplierId) {
        showToast('Pilih supplier terlebih dahulu', 'warning');
        return;
    }
    const label = _spaSupplierData.find(s => String(s.id) === String(_spaCurrentSupplierId))?.name || '';
    _spaLoadComparison(_spaCurrentSupplierId, label);
};

async function _spaLoadComparison(supplierId, supplierLabel) {
    const wrap = document.getElementById('spaResultsWrap');
    if (!wrap) return;

    // Show loading skeletons
    wrap.innerHTML = [
        `<div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:10px;"><i class="bi bi-hourglass-split" style="margin-right:4px;"></i>Memuat data komparasi untuk <strong>${escapeHtml(supplierLabel)}</strong>...</div>`,
        ...Array(3).fill(0).map(() => `
            <div class="spa-loading-card">
                <div style="display:flex;gap:10px;margin-bottom:10px;">
                    <div class="spa-skel" style="width:36px;height:36px;border-radius:8px;"></div>
                    <div style="flex:1;">
                        <div class="spa-skel" style="width:60%;margin-bottom:6px;"></div>
                        <div class="spa-skel" style="width:35%;height:9px;"></div>
                    </div>
                </div>
                <div class="spa-skel" style="width:100%;height:60px;"></div>
            </div>
        `)
    ].join('');

    try {
        const res = await api(`${BASE_URL}api/reports/supplier-price-comparison/${supplierId}`);

        if (res.error) throw new Error(res.error);

        const data = res.data || [];
        const supName = res.supplier_name || supplierLabel;

        if (data.length === 0) {
            wrap.innerHTML = `
                <div class="spa-empty">
                    <i class="bi bi-inbox"></i>
                    <div style="font-weight:600;margin-bottom:4px;">Tidak ada data komparasi</div>
                    <div style="font-size:var(--font-size-xs);">
                        Tidak ada produk dari <strong>${escapeHtml(supName)}</strong> yang juga pernah dibeli dari supplier lain.
                    </div>
                </div>
            `;
            return;
        }

        const cheapestCount = data.filter(d => d.is_cheapest).length;
        const expensiveCount = data.length - cheapestCount;
        const avgSavings = data.reduce((a, b) => a + (b.savings_pct || 0), 0) / (expensiveCount || 1);

        let html = `
            <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:10px;">
                Menampilkan <strong style="color:var(--text-primary);">${data.length} produk</strong> multi-supplier untuk <strong style="color:var(--primary);">${escapeHtml(supName)}</strong>
            </div>
            <div class="spa-summary-bar">
                <div class="spa-summary-chip">
                    <strong style="color:var(--success);">${cheapestCount}</strong>
                    <span>Harga Terbaik</span>
                </div>
                <div class="spa-summary-chip">
                    <strong style="color:var(--danger);">${expensiveCount}</strong>
                    <span>Bisa Lebih Murah</span>
                </div>
                <div class="spa-summary-chip">
                    <strong style="color:var(--warning);">${expensiveCount > 0 ? avgSavings.toFixed(1) + '%' : '-'}</strong>
                    <span>Rata-rata Selisih</span>
                </div>
            </div>
        `;

        data.forEach(prod => {
            const others = prod.other_suppliers || [];
            const isBest = prod.is_cheapest;
            
            const iconBg = isBest ? 'var(--success-bg)' : 'var(--danger-bg)';
            const iconColor = isBest ? 'var(--success)' : 'var(--danger)';
            const iconClass = isBest ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
            const cardClass = isBest ? 'spa-cheapest' : 'spa-expensive';
            
            const badge = isBest
                ? `<span class="spa-badge spa-badge-best"><i class="bi bi-check-lg"></i> Harga Terbaik</span>`
                : `<span class="spa-badge spa-badge-expensive">+${prod.savings_pct}% lebih mahal</span>`;

            const selNorm = prod.selected_norm_price;
            const selPrice = prod.selected_buy_price;
            const selUnit = prod.selected_unit_name || prod.base_unit;
            const selDate = prod.selected_last_date ? prod.selected_last_date.split(' ')[0] : '-';

            // Build competitor rows
            let otherRows = '';
            let minOtherNorm = Infinity;
            others.forEach(o => { if (parseFloat(o.norm_price) < minOtherNorm) minOtherNorm = parseFloat(o.norm_price); });

            others.forEach(oth => {
                const othNorm = parseFloat(oth.norm_price) || 0;
                const isOthCheapest = othNorm === minOtherNorm && !isBest;
                const rowClass = isOthCheapest ? 'spa-row-cheapest-other' : '';
                const othDate = (oth.last_date || '').split(' ')[0] || '-';
                const othUnit = oth.last_unit_name || prod.base_unit;
                const diffPct = selNorm > 0 ? (((othNorm - selNorm) / selNorm) * 100).toFixed(1) : 0;
                const diffColor = othNorm < selNorm ? 'var(--success)' : (othNorm > selNorm ? 'var(--danger)' : 'var(--text-muted)');
                const diffSign = othNorm < selNorm ? '↓' : (othNorm > selNorm ? '↑' : '=');
                const diffLabel = othNorm !== selNorm ? `<span style="color:${diffColor};font-size:9px;font-weight:700;">${diffSign}${Math.abs(diffPct)}%</span>` : `<span style="color:var(--text-muted);font-size:9px;">sama</span>`;

                otherRows += `
                    <tr class="${rowClass}">
                        <td style="color:var(--text-secondary);">${escapeHtml(oth.supplier_name)}</td>
                        <td><span class="spa-norm-price">Rp ${fmtNumber(Math.round(othNorm))}</span><br><span style="font-size:9px;color:var(--text-muted);">/${prod.base_unit} • ${escapeHtml(othUnit)}</span></td>
                        <td>${diffLabel}</td>
                        <td class="spa-date-label">${othDate}</td>
                    </tr>
                `;
            });

            html += `
                <div class="spa-product-card ${cardClass}">
                    <div class="spa-prod-header">
                        <div class="spa-prod-icon" style="background:${iconBg};color:${iconColor};">
                            <i class="bi ${iconClass}"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="spa-prod-name">${escapeHtml(prod.product_name)}</div>
                            <div class="spa-prod-cat">${prod.category_name ? escapeHtml(prod.category_name) : 'Tanpa Kategori'}</div>
                        </div>
                        ${badge}
                    </div>

                    <table class="spa-price-table">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Harga Terakhir</th>
                                <th>Selisih</th>
                                <th>Tgl Beli</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="spa-row-selected">
                                <td style="color:var(--primary);font-weight:700;">
                                    <i class="bi bi-pin-fill" style="font-size:9px;margin-right:3px;"></i>
                                    ${escapeHtml(supName)}
                                </td>
                                <td>
                                    <span class="spa-norm-price">Rp ${fmtNumber(Math.round(selNorm))}</span>
                                    <br><span style="font-size:9px;color:var(--text-muted);">/${prod.base_unit} • ${escapeHtml(selUnit)}</span>
                                </td>
                                <td><span style="color:var(--text-muted);font-size:9px;">—</span></td>
                                <td class="spa-date-label">${selDate}</td>
                            </tr>
                            ${otherRows}
                        </tbody>
                    </table>
                </div>
            `;
        });

        wrap.innerHTML = html;

    } catch (err) {
        wrap.innerHTML = `
            <div class="spa-empty">
                <i class="bi bi-exclamation-circle"></i>
                <div style="font-weight:600;margin-bottom:4px;color:var(--danger);">Gagal memuat data</div>
                <div style="font-size:var(--font-size-xs);">${escapeHtml(err.message)}</div>
            </div>
        `;
    }
}

function fmtNumber(n) {
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
</script>
