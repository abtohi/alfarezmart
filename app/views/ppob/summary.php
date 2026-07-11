<?php include BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .summary-header {
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--primary-rgb), 0.05) 100%);
        border: 1px solid rgba(var(--primary-rgb), 0.2);
        border-radius: var(--radius-lg);
        padding: 24px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .summary-header::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(var(--primary-rgb), 0.15) 0%, transparent 70%);
        border-radius: 50%;
    }
    .kpi-card {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-color: rgba(var(--primary-rgb), 0.4);
    }
    .kpi-icon {
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 80px;
        opacity: 0.05;
        color: var(--text-primary);
        z-index: 0;
        transform: rotate(-15deg);
    }
    .kpi-content {
        position: relative;
        z-index: 1;
    }
    .kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .kpi-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.2;
    }
    .kpi-card.success .kpi-icon { color: var(--success); opacity: 0.1; }
    .kpi-card.success .kpi-value { color: var(--success); }
    .kpi-card.warning .kpi-icon { color: var(--warning); opacity: 0.1; }
    .kpi-card.warning .kpi-value { color: var(--warning); }
    .kpi-card.danger .kpi-icon { color: var(--danger); opacity: 0.1; }
    .kpi-card.danger .kpi-value { color: var(--danger); }
    
    .filter-card {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 20px;
        margin-bottom: 24px;
    }
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin-bottom: 8px;
        display: block;
    }
    .form-select-custom {
        background-color: var(--surface-2);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 10px 35px 10px 15px;
        font-weight: 500;
        box-shadow: none;
        transition: border-color 0.2s;
    }
    .form-select-custom:focus {
        border-color: rgba(var(--primary-rgb), 0.5);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }
    .table-custom-wrapper {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }
    .table-custom-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background: transparent;
    }
    .table-custom-header h6 {
        margin: 0;
        font-weight: 700;
        font-size: 16px;
        color: var(--text-primary);
    }
    .table-elegant {
        width: 100%;
        border-collapse: collapse;
        color: var(--text-primary);
    }
    .table-elegant th {
        background: var(--surface-2);
        padding: 15px 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .table-elegant td {
        padding: 15px 20px;
        font-size: 14px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    .table-elegant tbody tr:last-child td {
        border-bottom: none;
    }
    .table-elegant tbody tr:hover {
        background: var(--surface-2);
    }
    .badge-soft {
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .badge-soft-success { background: rgba(var(--success-rgb), 0.1); color: var(--success); }
    .badge-soft-warning { background: rgba(var(--warning-rgb), 0.1); color: var(--warning); }
    .badge-soft-danger { background: rgba(var(--danger-rgb), 0.1); color: var(--danger); }
</style>

<div class="container py-4">
    <!-- Header Section -->
    <div class="summary-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-2 fw-bold text-primary"><i class="bi bi-bar-chart-line-fill me-2"></i>PPOB Analytics</h4>
            <p class="text-muted mb-0" style="font-size: 14px;">Tinjauan performa transaksi, kecepatan seller, dan estimasi keuntungan</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>ppob/history" class="btn btn-primary shadow-sm" style="border-radius: var(--radius-md); font-weight: 600; padding: 10px 20px;">
                <i class="bi bi-clock-history me-2"></i>Riwayat Transaksi
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-4 col-sm-6">
                <label class="filter-label">Rentang Waktu</label>
                <select id="filter-range" class="form-select form-select-custom">
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="7days">7 Hari Terakhir</option>
                    <option value="this_month" selected>Bulan Ini</option>
                    <option value="all">Semua Waktu</option>
                </select>
            </div>
            <div class="col-md-4 col-sm-6">
                <label class="filter-label">Kategori Produk</label>
                <select id="filter-category" class="form-select form-select-custom">
                    <option value="all">Semua Kategori</option>
                    <option value="Pulsa">Pulsa</option>
                    <option value="Data">Paket Data</option>
                    <option value="Games">Games</option>
                    <option value="E-Money">E-Money</option>
                    <option value="PLN">Token PLN</option>
                </select>
            </div>
            <div class="col-md-4 col-sm-12">
                <button onclick="loadAnalytics()" class="btn btn-primary w-100 h-100 d-flex align-items-center justify-content-center gap-2" style="border-radius: var(--radius-md); font-weight: 600; padding: 10px;">
                    <i class="bi bi-funnel-fill"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading-state" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-3 fw-medium">Mengambil data analitik...</p>
    </div>

    <!-- Content -->
    <div id="analytics-content">
        <!-- KPIs -->
        <div class="row g-4 mb-4" id="metrics-container">
            <!-- Rendered by JS -->
        </div>

        <div class="row g-4 mb-4">
            <!-- Chart -->
            <div class="col-lg-5">
                <div class="table-custom-wrapper h-100 d-flex flex-column">
                    <div class="table-custom-header">
                        <h6><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Proporsi Kategori Transaksi</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center flex-grow-1" style="padding: 30px; position: relative; min-height: 300px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Seller Table -->
            <div class="col-lg-7">
                <div class="table-custom-wrapper h-100 d-flex flex-column">
                    <div class="table-custom-header">
                        <h6><i class="bi bi-shop me-2 text-primary"></i>Performa Kecepatan Seller Digiflazz</h6>
                    </div>
                    <div class="table-responsive flex-grow-1" style="max-height: 400px; overflow-y: auto;">
                        <table class="table-elegant">
                            <thead style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th>Nama Seller</th>
                                    <th class="text-center">Jml Transaksi</th>
                                    <th class="text-center">Tingkat Sukses</th>
                                    <th class="text-start">Rata-rata Kecepatan</th>
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
    </div>
</div>

<script>
    let categoryChartInstance = null;
    
    // CSS Variables for Chart Colors matching theme
    const getThemeColor = (variable) => {
        return getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
    };

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
                renderChart(json.data.categories);
                renderSellerTable(json.data.sellers);
            } else {
                showAlert('Gagal memuat analitik: ' + json.message, 'danger');
            }
        } catch (e) {
            console.error(e);
            showAlert('Terjadi kesalahan jaringan saat memuat analitik.', 'danger');
        } finally {
            document.getElementById('loading-state').classList.add('d-none');
            document.getElementById('analytics-content').style.display = 'block';
        }
    }

    function showAlert(msg, type='info') {
        // Fallback to simple alert if toast container doesn't exist on this page
        if (!document.getElementById('toast-container-ppob')) {
            alert(msg);
            return;
        }
        // Basic toast implementation
        const container = document.getElementById('toast-container-ppob');
        const id = 'toast-' + Date.now();
        const colors = {info:'#0dcaf0',success:'#22c55e',danger:'#ef4444',warning:'#f59e0b'};
        const toast = document.createElement('div');
        toast.id = id;
        toast.style.cssText = `background:${colors[type] || '#0dcaf0'};color:white;padding:14px 20px;border-radius:12px;font-weight:600;box-shadow:0 5px 15px rgba(0,0,0,0.3);animation:slideInRight 0.3s ease;margin-bottom:10px;`;
        toast.innerText = msg;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    function renderMetrics(metrics) {
        const c = document.getElementById('metrics-container');
        
        // Determine success rate status
        let srClass = '';
        if (metrics.success_rate >= 80) srClass = 'success';
        else if (metrics.success_rate >= 50) srClass = 'warning';
        else srClass = 'danger';

        c.innerHTML = `
            <div class="col-md-4">
                <div class="kpi-card">
                    <i class="bi bi-receipt kpi-icon"></i>
                    <div class="kpi-content">
                        <div class="kpi-label">Total Transaksi</div>
                        <div class="kpi-value">${metrics.total.toLocaleString('id-ID')}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card ${srClass}">
                    <i class="bi bi-percent kpi-icon"></i>
                    <div class="kpi-content">
                        <div class="kpi-label">Tingkat Kesuksesan (Success Rate)</div>
                        <div class="kpi-value">${metrics.success_rate}%</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card">
                    <i class="bi bi-cash-coin kpi-icon"></i>
                    <div class="kpi-content">
                        <div class="kpi-label">Estimasi Keuntungan</div>
                        <div class="kpi-value text-primary">Rp ${metrics.profit.toLocaleString('id-ID')}</div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderChart(categories) {
        const labels = Object.keys(categories);
        const data = Object.values(categories);
        const ctx = document.getElementById('categoryChart').getContext('2d');

        if (categoryChartInstance) {
            categoryChartInstance.destroy();
        }

        if (labels.length === 0) {
            // Show empty state if no data
            return;
        }

        // Modern color palette
        const colors = [
            '#3b82f6', // blue-500
            '#8b5cf6', // violet-500
            '#ec4899', // pink-500
            '#10b981', // emerald-500
            '#f59e0b', // amber-500
            '#06b6d4', // cyan-500
            '#6366f1', // indigo-500
            '#14b8a6'  // teal-500
        ];

        categoryChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 6,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: getThemeColor('--text-secondary') || '#888',
                            font: { size: 12, family: "'Inter', sans-serif", weight: '500' },
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: getThemeColor('--surface-2') || '#1e293b',
                        titleColor: getThemeColor('--text-primary') || '#fff',
                        bodyColor: getThemeColor('--text-secondary') || '#cbd5e1',
                        borderColor: getThemeColor('--border-color') || '#334155',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        boxPadding: 6
                    }
                },
                cutout: '75%',
                layout: {
                    padding: { top: 10, bottom: 10 }
                }
            }
        });
    }

    function renderSellerTable(sellers) {
        const tbody = document.getElementById('seller-tbody');
        tbody.innerHTML = '';

        if (sellers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                Tidak ada data seller untuk rentang waktu ini
            </td></tr>`;
            return;
        }

        sellers.forEach(s => {
            // Determine success badge
            let successBadge = '';
            if (s.success_rate >= 90) {
                successBadge = `<span class="badge-soft badge-soft-success"><i class="bi bi-check-circle-fill"></i> ${s.success_rate}%</span>`;
            } else if (s.success_rate >= 70) {
                successBadge = `<span class="badge-soft badge-soft-warning"><i class="bi bi-exclamation-triangle-fill"></i> ${s.success_rate}%</span>`;
            } else {
                successBadge = `<span class="badge-soft badge-soft-danger"><i class="bi bi-x-circle-fill"></i> ${s.success_rate}%</span>`;
            }
            
            // Format time
            let timeStr = '-';
            if (s.avg_process_time > 0) {
                if (s.avg_process_time < 60) {
                    timeStr = `${Math.round(s.avg_process_time)} detik`;
                } else {
                    timeStr = `${(s.avg_process_time / 60).toFixed(1)} menit`;
                }
            }

            // Speed indicator
            let speedIndicator = '';
            if (s.avg_process_time > 0 && s.avg_process_time <= 10) {
                speedIndicator = `<div class="d-flex align-items-center gap-2">
                    <span class="text-success"><i class="bi bi-lightning-charge-fill"></i></span>
                    <span class="fw-medium">${timeStr}</span>
                </div>`;
            } else if (s.avg_process_time > 10 && s.avg_process_time <= 60) {
                speedIndicator = `<div class="d-flex align-items-center gap-2">
                    <span class="text-info"><i class="bi bi-stopwatch"></i></span>
                    <span class="fw-medium">${timeStr}</span>
                </div>`;
            } else if (s.avg_process_time > 60) {
                speedIndicator = `<div class="d-flex align-items-center gap-2">
                    <span class="text-danger"><i class="bi bi-hourglass-bottom"></i></span>
                    <span class="fw-medium">${timeStr}</span>
                </div>`;
            } else {
                speedIndicator = `<span class="text-muted">${timeStr}</span>`;
            }

            tbody.innerHTML += `
                <tr>
                    <td class="fw-semibold">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            ${s.name}
                        </div>
                    </td>
                    <td class="text-center fw-medium">${s.total.toLocaleString('id-ID')}</td>
                    <td class="text-center">${successBadge}</td>
                    <td>${speedIndicator}</td>
                </tr>
            `;
        });
    }
</script>

<?php include BASE_PATH . '/app/views/layouts/footer.php'; ?>
