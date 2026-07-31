<?php include BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .summary-header {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 20px 24px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        border-left: 4px solid var(--primary);
    }
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (min-width: 576px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (min-width: 992px) {
        .kpi-grid { grid-template-columns: repeat(4, 1fr); }
    }
    .kpi-card {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 18px 20px;
        transition: transform 0.2s ease, border-color 0.2s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary);
    }
    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
    }
    .kpi-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .kpi-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
    }
    .kpi-sub {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Leaderboard Styles */
    .top-sellers-card {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }
    .top-seller-item {
        background: var(--surface-2);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 12px 16px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: transform 0.15s ease;
    }
    .top-seller-item:hover {
        transform: translateX(4px);
        border-color: var(--primary);
    }
    .rank-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        flex-shrink: 0;
    }
    .rank-1 { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4); }
    .rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); color: #fff; }
    .rank-3 { background: linear-gradient(135deg, #b45309, #78350f); color: #fff; }
    .rank-other { background: var(--surface-3); color: var(--text-muted); }

    /* Filter & Tables */
    .filter-card {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 16px 20px;
        margin-bottom: 24px;
    }
    .table-custom-wrapper {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }
    .table-custom-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .table-elegant {
        width: 100%;
        border-collapse: collapse;
    }
    .table-elegant th {
        background: var(--surface-2);
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .table-elegant td {
        padding: 12px 16px;
        font-size: 13px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .table-elegant tbody tr:last-child td { border-bottom: none; }
    .table-elegant tbody tr:hover { background: var(--surface-2); }

    /* Speed Badges */
    .speed-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }
    .speed-kilat { background: rgba(16, 185, 129, 0.12); color: #10b981; }
    .speed-cepat { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .speed-normal { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .speed-lambat { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
</style>

<div class="container-fluid py-4" style="max-width: 1400px;">
    <!-- Header Section -->
    <div class="summary-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>ppob" class="btn btn-outline-secondary rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" title="Kembali ke PPOB">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="mb-1 fw-bold text-primary"><i class="bi bi-graph-up-arrow me-2"></i>Analytics PPOB Digiflazz</h4>
                <p class="text-muted mb-0" style="font-size: 13px;">Analisis performa transaksi, kecepatan supplier (detik), dan ranking Top Seller</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>ppob/history" class="btn btn-primary px-3 py-2" style="border-radius: var(--radius-md); font-size: 13px; font-weight: 600;">
                <i class="bi bi-clock-history me-1"></i>Riwayat Transaksi
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-4 col-sm-6">
                <label class="form-label text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Rentang Waktu</label>
                <select id="filter-range" class="form-select form-control-dark">
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="7days">7 Hari Terakhir</option>
                    <option value="this_month" selected>Bulan Ini</option>
                    <option value="all">Semua Waktu</option>
                </select>
            </div>
            <div class="col-md-4 col-sm-6">
                <label class="form-label text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">Kategori Produk</label>
                <select id="filter-category" class="form-select form-control-dark">
                    <option value="all">Semua Kategori</option>
                    <option value="Pulsa">Pulsa</option>
                    <option value="Data">Paket Data</option>
                    <option value="Games">Games</option>
                    <option value="E-Money">E-Money</option>
                    <option value="PLN">Token PLN</option>
                </select>
            </div>
            <div class="col-md-4 col-sm-12">
                <button onclick="loadAnalytics()" class="btn btn-primary w-100 py-2" style="border-radius: var(--radius-md); font-weight: 600;">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading-state" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-3 fw-medium" style="font-size: 13px;">Memproses & menganalisis data PPOB...</p>
    </div>

    <!-- Analytics Dashboard Content -->
    <div id="analytics-content">
        <!-- 1. KPI Summary Cards -->
        <div class="kpi-grid" id="metrics-container">
            <!-- Rendered by JS -->
        </div>

        <!-- 2. Top 5 Sellers Leaderboard & Category Charts -->
        <div class="row g-4 mb-4">
            <!-- Top 5 Sellers Leaderboard -->
            <div class="col-lg-7">
                <div class="top-sellers-card h-100 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="bi bi-trophy-fill me-2 text-warning"></i>🏆 5 Top Seller Terbaik</h6>
                            <span class="text-muted" style="font-size: 11px;">Diranking berdasarkan Success Rate, Kecepatan (Detik), dan Keuntungan</span>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold" style="font-size: 10px;">Leaderboard</span>
                    </div>
                    <div id="top-sellers-list" class="flex-grow-1">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>

            <!-- Category Chart -->
            <div class="col-lg-5">
                <div class="table-custom-wrapper h-100 d-flex flex-column">
                    <div class="table-custom-header">
                        <h6 class="mb-0 fw-bold" style="font-size: 14px;"><i class="bi bi-pie-chart-fill me-2 text-info"></i>Proporsi Kategori Transaksi</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center flex-grow-1" style="padding: 20px; position: relative; min-height: 280px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Daily Trend Chart -->
        <div class="table-custom-wrapper mb-4">
            <div class="table-custom-header">
                <h6 class="mb-0 fw-bold" style="font-size: 14px;"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Tren Volume Transaksi & Keuntungan Harian</h6>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 10px;">Grafik Harian</span>
            </div>
            <div style="padding: 20px; position: relative; height: 260px; width: 100%; overflow: hidden;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- 4. Seller Performance Table -->
        <div class="table-custom-wrapper">
            <div class="table-custom-header">
                <div>
                    <h6 class="mb-1 fw-bold" style="font-size: 15px;"><i class="bi bi-shop me-2 text-primary"></i>Analisis Lengkap Performa Supplier Digiflazz</h6>
                    <span class="text-muted" style="font-size: 11px;">Rata-rata kecepatan dihitung akurat dalam detik (dtk)</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table-elegant">
                    <thead>
                        <tr>
                            <th>Rank &amp; Seller Name</th>
                            <th class="text-center">Total Trx</th>
                            <th class="text-center">Success Rate</th>
                            <th>Rata-rata Kecepatan</th>
                            <th class="text-end">Total Omset</th>
                            <th class="text-end">Total Profit</th>
                            <th class="text-end">Avg Profit / Trx</th>
                        </tr>
                    </thead>
                    <tbody id="seller-tbody">
                        <!-- Rendered by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let categoryChartInstance = null;
    let trendChartInstance = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadAnalytics();
    });

    async function loadAnalytics() {
        const range = document.getElementById('filter-range').value;
        const category = document.getElementById('filter-category').value;
        
        document.getElementById('analytics-content').style.display = 'none';
        document.getElementById('loading-state').classList.remove('d-none');

        try {
            const res = await fetch(`<?= BASE_URL ?>api/ppob/summary?range=${range}&category=${category}`);
            const json = await res.json();
            
            if (json.success) {
                renderMetrics(json.data.metrics);
                renderTopSellers(json.data.top_sellers);
                renderCategoryChart(json.data.categories);
                renderTrendChart(json.data.daily_trend);
                renderSellerTable(json.data.sellers);
            } else {
                alert('Gagal memuat analitik: ' + (json.message || 'Error'));
            }
        } catch (e) {
            console.error('Error loading analytics:', e);
            alert('Terjadi kesalahan koneksi saat memuat data analitik.');
        } finally {
            document.getElementById('loading-state').classList.add('d-none');
            document.getElementById('analytics-content').style.display = 'block';
        }
    }

    function renderMetrics(m) {
        const c = document.getElementById('metrics-container');
        
        // Speed Badge Status
        let speedText = `${m.avg_speed} dtk`;
        let speedClass = 'text-success';
        if (m.avg_speed <= 5) speedText += ' ⚡ (Kilat)';
        else if (m.avg_speed <= 20) speedText += ' ⏱️ (Cepat)';
        else { speedText += ' ⏳ (Normal)'; speedClass = 'text-warning'; }

        c.innerHTML = `
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-label">Total Transaksi</span>
                    <div class="kpi-icon-box" style="background: rgba(59,130,246,0.12); color: #3b82f6;">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <div class="kpi-value">${m.total.toLocaleString('id-ID')}</div>
                <div class="kpi-sub">
                    <span class="text-success fw-bold">${m.success_count} Sukses</span> &middot; 
                    <span class="text-danger fw-bold">${m.failed_count} Gagal</span>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-label">Tingkat Sukses (SR)</span>
                    <div class="kpi-icon-box" style="background: rgba(16,185,129,0.12); color: #10b981;">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
                <div class="kpi-value ${m.success_rate >= 80 ? 'text-success' : (m.success_rate >= 60 ? 'text-warning' : 'text-danger')}">
                    ${m.success_rate}%
                </div>
                <div class="kpi-sub">
                    Performa Keseluruhan
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-label">Avg Kecepatan Supplier</span>
                    <div class="kpi-icon-box" style="background: rgba(129,140,248,0.12); color: #818cf8;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                </div>
                <div class="kpi-value ${speedClass}">
                    ${m.avg_speed} <span style="font-size: 1rem; font-weight:600;">dtk</span>
                </div>
                <div class="kpi-sub">Rata-rata waktu proses</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-label">Estimasi Profit</span>
                    <div class="kpi-icon-box" style="background: rgba(245,158,11,0.12); color: #f59e0b;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
                <div class="kpi-value text-primary">
                    Rp ${m.profit.toLocaleString('id-ID')}
                </div>
                <div class="kpi-sub">Omzet: Rp ${m.revenue.toLocaleString('id-ID')}</div>
            </div>
        `;
    }

    function renderTopSellers(topSellers) {
        const container = document.getElementById('top-sellers-list');
        if (!topSellers || topSellers.length === 0) {
            container.innerHTML = `<div class="text-center py-4 text-muted" style="font-size:12px;">Belum ada data seller untuk diperingkatkan</div>`;
            return;
        }

        let html = '';
        topSellers.forEach((s, idx) => {
            const rankClass = idx === 0 ? 'rank-1' : (idx === 1 ? 'rank-2' : (idx === 2 ? 'rank-3' : 'rank-other'));
            const medalIcon = idx === 0 ? '🥇' : (idx === 1 ? '🥈' : (idx === 2 ? '🥉' : `#${idx + 1}`));
            
            // Format speed
            let speedBadge = '';
            if (s.avg_process_time <= 5) {
                speedBadge = `<span class="speed-badge speed-kilat"><i class="bi bi-lightning-charge-fill"></i> ${s.avg_process_time} dtk</span>`;
            } else if (s.avg_process_time <= 20) {
                speedBadge = `<span class="speed-badge speed-cepat"><i class="bi bi-stopwatch"></i> ${s.avg_process_time} dtk</span>`;
            } else {
                speedBadge = `<span class="speed-badge speed-normal"><i class="bi bi-clock"></i> ${s.avg_process_time} dtk</span>`;
            }

            html += `
                <div class="top-seller-item">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="rank-badge ${rankClass}">${medalIcon}</div>
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate" style="font-size: 13px; color: var(--text-primary);" title="${s.name}">
                                ${s.name}
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted);">
                                ${s.handle ? `<span class="text-info me-1">${s.handle}</span> &middot; ` : ''}
                                <span>${s.total} Trx</span> &middot; 
                                <strong style="color:var(--success);">Profit Rp ${s.profit.toLocaleString('id-ID')}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="text-end flex-shrink-0 d-flex flex-column align-items-end gap-1">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 11px;">
                            <i class="bi bi-check-circle-fill me-1"></i>${s.success_rate}% SR
                        </span>
                        ${speedBadge}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function renderCategoryChart(categories) {
        const labels = Object.keys(categories);
        const data = Object.values(categories);
        const ctx = document.getElementById('categoryChart').getContext('2d');

        if (categoryChartInstance) categoryChartInstance.destroy();
        if (labels.length === 0) return;

        const colors = ['#3b82f6', '#818cf8', '#ec4899', '#10b981', '#f59e0b', '#06b6d4'];

        categoryChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
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
                            font: { size: 11, family: "'Inter', sans-serif" },
                            padding: 14,
                            usePointStyle: true
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }

    function renderTrendChart(dailyTrend) {
        const ctx = document.getElementById('trendChart').getContext('2d');
        if (trendChartInstance) trendChartInstance.destroy();

        if (!dailyTrend || dailyTrend.length === 0) return;

        const labels = dailyTrend.map(d => d.label);
        const trxData = dailyTrend.map(d => d.total);
        const profitData = dailyTrend.map(d => d.profit);

        trendChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Transaksi',
                        data: trxData,
                        backgroundColor: 'rgba(59, 130, 246, 0.75)',
                        borderColor: '#3b82f6',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Profit (Rp)',
                        data: profitData,
                        type: 'line',
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: '#a0a8c0', font: { size: 10 } }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b7394', font: { size: 9 } }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#6b7394', font: { size: 9 } }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            color: '#10b981',
                            font: { size: 9 },
                            callback: v => 'Rp ' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v)
                        }
                    }
                }
            }
        });
    }

    function renderSellerTable(sellers) {
        const tbody = document.getElementById('seller-tbody');
        tbody.innerHTML = '';

        if (!sellers || sellers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                Tidak ada data seller untuk filter yang dipilih
            </td></tr>`;
            return;
        }

        sellers.forEach((s, idx) => {
            // SR Badge
            let srBadge = '';
            if (s.success_rate >= 90) {
                srBadge = `<span class="badge bg-success bg-opacity-10 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>${s.success_rate}%</span>`;
            } else if (s.success_rate >= 70) {
                srBadge = `<span class="badge bg-warning bg-opacity-10 text-warning fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>${s.success_rate}%</span>`;
            } else {
                srBadge = `<span class="badge bg-danger bg-opacity-10 text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i>${s.success_rate}%</span>`;
            }

            // Speed Indicator in SECONDS
            let speedBadge = '';
            if (s.avg_process_time <= 5) {
                speedBadge = `<span class="speed-badge speed-kilat"><i class="bi bi-lightning-charge-fill"></i> ${s.avg_process_time} detik</span>`;
            } else if (s.avg_process_time <= 20) {
                speedBadge = `<span class="speed-badge speed-cepat"><i class="bi bi-stopwatch"></i> ${s.avg_process_time} detik</span>`;
            } else if (s.avg_process_time <= 60) {
                speedBadge = `<span class="speed-badge speed-normal"><i class="bi bi-clock"></i> ${s.avg_process_time} detik</span>`;
            } else {
                speedBadge = `<span class="speed-badge speed-lambat"><i class="bi bi-hourglass-bottom"></i> ${s.avg_process_time} detik</span>`;
            }

            tbody.innerHTML += `
                <tr>
                    <td class="fw-semibold">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 11px; flex-shrink: 0;">
                                #${idx + 1}
                            </div>
                            <div>
                                <div class="fw-bold" style="color: var(--text-primary);">${s.name}</div>
                                ${s.handle ? `<div style="font-size: 10px; color: var(--text-muted);">${s.handle}</div>` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="text-center fw-semibold">
                        ${s.total.toLocaleString('id-ID')}
                        <div style="font-size: 10px; color: var(--text-muted);">${s.success} S / ${s.failed} G</div>
                    </td>
                    <td class="text-center">${srBadge}</td>
                    <td>${speedBadge}</td>
                    <td class="text-end fw-semibold">Rp ${s.revenue.toLocaleString('id-ID')}</td>
                    <td class="text-end fw-bold text-success">Rp ${s.profit.toLocaleString('id-ID')}</td>
                    <td class="text-end text-muted" style="font-size: 11px;">Rp ${s.avg_profit_per_trx.toLocaleString('id-ID')}</td>
                </tr>
            `;
        });
    }
</script>

<?php include BASE_PATH . '/app/views/layouts/footer.php'; ?>
