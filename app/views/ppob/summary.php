<?php include BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i> PPOB Analytics</h4>
            <p class="text-muted mb-0" style="font-size:var(--font-size-sm);">Analisis kecepatan seller dan performa transaksi</p>
        </div>
        <a href="<?= BASE_URL ?>ppob/history" class="btn btn-outline-secondary btn-sm" style="border-radius:var(--radius-md);">
            <i class="bi bi-clock-history me-1"></i> History
        </a>
    </div>

    <!-- Slicers / Filters -->
    <div class="card card-custom mb-4" style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);">
        <div class="card-body p-3 d-flex gap-3 flex-wrap align-items-end">
            <div style="flex:1;min-width:200px;">
                <label class="form-label text-muted fw-bold" style="font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">Rentang Waktu</label>
                <select id="filter-range" class="form-select form-select-sm" style="border-radius:var(--radius-md);background:var(--surface-2);color:var(--text-primary);border-color:var(--border-color);">
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="7days">7 Hari Terakhir</option>
                    <option value="this_month" selected>Bulan Ini</option>
                    <option value="all">Semua Waktu</option>
                </select>
            </div>
            <div style="flex:1;min-width:200px;">
                <label class="form-label text-muted fw-bold" style="font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">Kategori</label>
                <select id="filter-category" class="form-select form-select-sm" style="border-radius:var(--radius-md);background:var(--surface-2);color:var(--text-primary);border-color:var(--border-color);">
                    <option value="all">Semua Kategori</option>
                    <option value="Pulsa">Pulsa</option>
                    <option value="Data">Paket Data</option>
                    <option value="Games">Games</option>
                    <option value="E-Money">E-Money</option>
                    <option value="PLN">Token PLN</option>
                </select>
            </div>
            <div>
                <button onclick="loadAnalytics()" class="btn btn-primary btn-sm" style="border-radius:var(--radius-md);padding:0.25rem 1rem;">
                    <i class="bi bi-filter me-1"></i> Terapkan
                </button>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4" id="metrics-container">
        <!-- Rendered by JS -->
    </div>

    <div class="row g-4 mb-4">
        <!-- Chart -->
        <div class="col-md-5">
            <div class="card card-custom h-100" style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);">
                <div class="card-header" style="background:transparent;border-bottom:1px solid var(--border-color);padding:15px;">
                    <h6 class="mb-0 fw-bold">Proporsi Kategori</h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="position: relative; height:250px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Seller Table -->
        <div class="col-md-7">
            <div class="card card-custom h-100" style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
                <div class="card-header" style="background:transparent;border-bottom:1px solid var(--border-color);padding:15px;">
                    <h6 class="mb-0 fw-bold">Performa Seller (Digiflazz)</h6>
                </div>
                <div class="table-responsive" style="max-height:250px;">
                    <table class="table mb-0" style="font-size:var(--font-size-sm);color:var(--text-primary);">
                        <thead style="background:var(--surface-2);position:sticky;top:0;z-index:1;">
                            <tr>
                                <th style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:none;">Seller</th>
                                <th style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:none;text-align:center;">Jml Transaksi</th>
                                <th style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:none;text-align:center;">Success Rate</th>
                                <th style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:none;text-align:center;">Avg Kecepatan</th>
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

<script>
    let categoryChartInstance = null;

    document.addEventListener('DOMContentLoaded', () => {
        loadAnalytics();
    });

    async function loadAnalytics() {
        const range = document.getElementById('filter-range').value;
        const category = document.getElementById('filter-category').value;
        
        document.getElementById('metrics-container').innerHTML = `
            <div class="col-12 text-center py-4 text-muted">
                <span class="spinner-border spinner-border-sm me-2 text-primary"></span> Memuat Data...
            </div>
        `;
        document.getElementById('seller-tbody').innerHTML = '';

        try {
            const res = await fetch(`<?= BASE_URL ?>api/ppob/summary?range=${range}&category=${category}`);
            const json = await res.json();
            
            if (json.success) {
                renderMetrics(json.data.metrics);
                renderChart(json.data.categories);
                renderSellerTable(json.data.sellers);
            }
        } catch (e) {
            console.error(e);
            alert('Gagal memuat analitik');
        }
    }

    function renderMetrics(metrics) {
        const c = document.getElementById('metrics-container');
        c.innerHTML = `
            <div class="col-md-4">
                <div class="card card-custom" style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:20px;">
                    <div class="text-muted fw-bold mb-1" style="font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">Total Transaksi</div>
                    <div class="fw-bold" style="font-size:24px;color:var(--text-primary);">${metrics.total}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom" style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:20px;">
                    <div class="text-muted fw-bold mb-1" style="font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">Success Rate</div>
                    <div class="fw-bold" style="font-size:24px;color:${metrics.success_rate >= 80 ? 'var(--success)' : (metrics.success_rate >= 50 ? 'var(--warning)' : 'var(--danger)')};">${metrics.success_rate}%</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom" style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:20px;">
                    <div class="text-muted fw-bold mb-1" style="font-size:10px;text-transform:uppercase;letter-spacing:0.5px;">Estimasi Keuntungan</div>
                    <div class="fw-bold" style="font-size:24px;color:var(--text-primary);">Rp ${metrics.profit.toLocaleString('id-ID')}</div>
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
            // No data
            return;
        }

        categoryChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        '#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4cc9f0', '#00f5d4'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim() || '#a0a0a0',
                            font: { size: 10, family: 'Inter' }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    function renderSellerTable(sellers) {
        const tbody = document.getElementById('seller-tbody');
        tbody.innerHTML = '';

        if (sellers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Belum ada data seller</td></tr>`;
            return;
        }

        sellers.forEach(s => {
            const successColor = s.success_rate >= 90 ? 'var(--success)' : (s.success_rate >= 70 ? 'var(--warning)' : 'var(--danger)');
            
            // Format time
            let timeStr = '-';
            if (s.avg_process_time > 0) {
                if (s.avg_process_time < 60) {
                    timeStr = `${Math.round(s.avg_process_time)} detik`;
                } else {
                    timeStr = `${(s.avg_process_time / 60).toFixed(1)} menit`;
                }
            }

            let badge = '';
            if (s.avg_process_time > 0 && s.avg_process_time < 5) badge = '<i class="bi bi-lightning-charge-fill text-warning ms-1" title="Sangat Cepat"></i>';
            else if (s.avg_process_time > 60) badge = '<i class="bi bi-turtle text-danger ms-1" title="Lambat"></i>';

            tbody.innerHTML += `
                <tr style="border-bottom:1px solid var(--border-color);">
                    <td style="padding:12px;font-weight:600;">${s.name}</td>
                    <td style="padding:12px;text-align:center;">${s.total}</td>
                    <td style="padding:12px;text-align:center;color:${successColor};font-weight:700;">${s.success_rate}%</td>
                    <td style="padding:12px;text-align:center;">${timeStr} ${badge}</td>
                </tr>
            `;
        });
    }
</script>

<?php include BASE_PATH . '/app/views/layouts/footer.php'; ?>
