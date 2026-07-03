

<div class="page-section" style="max-width:1200px; margin:0 auto;">
    
    <!-- Header Title & Badges -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div class="d-flex align-items-center">
            <a href="<?= BASE_URL ?>ppob" class="btn btn-icon me-3" style="background:var(--surface-3);color:var(--text-color); border:1px solid var(--border-color);">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="m-0 fw-bold" style="font-size:1.25rem;">Laporan Transaksi Prabayar</h4>
        </div>
        
        <div class="d-flex gap-2">
            <span class="badge" style="background-color:rgba(25, 135, 84, 0.15); color:#198754; font-size:13px; padding:8px 12px; font-weight:600;">
                Sukses <?= intval($stats['success_count'] ?? 0) ?>
            </span>
            <span class="badge" style="background-color:rgba(220, 53, 69, 0.15); color:#dc3545; font-size:13px; padding:8px 12px; font-weight:600;">
                Gagal <?= intval($stats['failed_count'] ?? 0) ?>
            </span>
            <span class="badge" style="background-color:rgba(13, 202, 240, 0.15); color:#0dcaf0; font-size:13px; padding:8px 12px; font-weight:600;">
                Pending <?= intval($stats['pending_count'] ?? 0) ?>
            </span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="card card-custom mb-4" style="border: 1px solid var(--border-color); box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-6 col-md-3 p-3 p-md-4 border-end border-bottom border-md-bottom-0" style="border-color:var(--border-color) !important;">
                    <div class="text-muted mb-1" style="font-size:12px; font-weight:600; text-transform:uppercase;">Transaksi</div>
                    <div class="fw-bold fs-4"><?= intval($stats['total_trx'] ?? 0) ?></div>
                </div>
                <div class="col-6 col-md-3 p-3 p-md-4 border-end border-bottom border-md-bottom-0" style="border-color:var(--border-color) !important;">
                    <div class="text-muted mb-1" style="font-size:12px; font-weight:600; text-transform:uppercase;">Penjualan</div>
                    <div class="fw-bold fs-5">Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?></div>
                </div>
                <div class="col-6 col-md-3 p-3 p-md-4 border-end" style="border-color:var(--border-color) !important;">
                    <div class="text-muted mb-1" style="font-size:12px; font-weight:600; text-transform:uppercase;">Biaya</div>
                    <div class="fw-bold fs-5">Rp <?= number_format($stats['total_cost'] ?? 0, 0, ',', '.') ?></div>
                </div>
                <div class="col-6 col-md-3 p-3 p-md-4">
                    <div class="text-muted mb-1" style="font-size:12px; font-weight:600; text-transform:uppercase;">Profit</div>
                    <div class="fw-bold fs-5 text-success">Rp <?= number_format($stats['total_profit'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card card-custom" style="border: 1px solid var(--border-color); box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div class="card-header border-bottom-0 p-0" style="background:var(--surface-2);">
            <ul class="nav nav-tabs px-3 pt-3 border-bottom" style="border-color:var(--border-color) !important;">
                <li class="nav-item">
                    <a class="nav-link active fw-bold" href="#" id="tab-all" onclick="loadTab('all', event)" style="border-bottom: 3px solid var(--primary); color:var(--text-color); border-top:none; border-left:none; border-right:none; background:transparent;">Semua</a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link fw-bold text-muted" href="#" id="tab-pending" onclick="loadTab('pending', event)" style="border-bottom: 3px solid transparent; border-top:none; border-left:none; border-right:none; background:transparent;">Pending</a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" style="font-size:13px;">
                    <thead style="background:var(--surface-3);">
                        <tr>
                            <th class="ps-4 py-3 text-nowrap">Tanggal</th>
                            <th class="py-3">Agen</th>
                            <th class="py-3">Supplier</th>
                            <th class="py-3 text-end">Modal</th>
                            <th class="py-3">SN</th>
                            <th class="py-3 text-end">Profit</th>
                            <th class="pe-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody">
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let currentStatus = 'all';

    document.addEventListener('DOMContentLoaded', () => {
        loadHistory(currentStatus);
    });

    function loadTab(status, event) {
        event.preventDefault();
        currentStatus = status;
        
        // Update tab styles
        document.getElementById('tab-all').className = status === 'all' ? 'nav-link active fw-bold' : 'nav-link fw-bold text-muted';
        document.getElementById('tab-all').style.borderBottomColor = status === 'all' ? 'var(--primary)' : 'transparent';
        
        document.getElementById('tab-pending').className = status === 'pending' ? 'nav-link active fw-bold' : 'nav-link fw-bold text-muted';
        document.getElementById('tab-pending').style.borderBottomColor = status === 'pending' ? 'var(--primary)' : 'transparent';
        
        document.getElementById('history-tbody').innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Memuat data...</td></tr>';
        
        loadHistory(status);
    }

    async function loadHistory(status) {
        try {
            const res = await fetch(`<?= BASE_URL ?>api/ppob/transactions?limit=100&status=${status}`);
            const data = await res.json();
            const tbody = document.getElementById('history-tbody');
            tbody.innerHTML = '';

            if (data.success && data.data.length > 0) {
                data.data.forEach(trx => {
                    let statusBadge = '';
                    if (trx.status === 'success') statusBadge = '<span class="badge bg-success" style="font-weight:500;">Sukses</span>';
                    else if (trx.status === 'pending' || trx.status === 'processing') statusBadge = '<span class="badge bg-info text-dark" style="font-weight:500;">Pending</span>';
                    else statusBadge = '<span class="badge bg-danger" style="font-weight:500;">Gagal</span>';

                    const dateObj = new Date(trx.created_at);
                    const formattedDate = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute:'2-digit' });

                    tbody.innerHTML += `
                        <tr>
                            <td class="ps-4 py-3 text-nowrap">${formattedDate}</td>
                            <td class="py-3 fw-bold">${trx.agent_name || 'Admin'}</td>
                            <td class="py-3"><span class="badge bg-light text-dark border">Digiflazz</span></td>
                            <td class="py-3 text-end">Rp ${parseInt(trx.modal_price).toLocaleString('id-ID')}</td>
                            <td class="py-3" style="font-family:monospace; font-size:12px;">${trx.sn || '-'}</td>
                            <td class="py-3 text-end text-success fw-bold">Rp ${parseInt(trx.profit).toLocaleString('id-ID')}</td>
                            <td class="pe-4 py-3 text-center">${statusBadge}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada transaksi ditemukan.</td></tr>';
            }
        } catch (e) {
            console.error(e);
            document.getElementById('history-tbody').innerHTML = '<tr><td colspan="7" class="text-center py-5 text-danger">Gagal memuat data.</td></tr>';
        }
    }
</script>


