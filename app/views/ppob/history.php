<div class="page-section" style="max-width:1200px; margin:0 auto;">
    
    <!-- Header Title & Badges -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div class="d-flex align-items-center">
            <a href="<?= BASE_URL ?>ppob" class="btn btn-icon me-3" style="background:var(--surface-3);color:var(--text-color); border:1px solid var(--border-color);">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="m-0 fw-bold" style="font-size:1.4rem;">Laporan Transaksi Prabayar</h4>
                <div class="text-muted" style="font-size:0.85rem; margin-top:2px;">Ringkasan dan riwayat transaksi PPOB Digiflazz</div>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <div style="background:rgba(25, 135, 84, 0.1); border:1px solid rgba(25, 135, 84, 0.2); color:#198754; font-size:12px; padding:6px 12px; border-radius:var(--radius-sm); font-weight:700; display:flex; align-items:center; gap:6px;">
                <i class="bi bi-check-circle-fill"></i> Sukses: <?= intval($stats['success_count'] ?? 0) ?>
            </div>
            <div style="background:rgba(220, 53, 69, 0.1); border:1px solid rgba(220, 53, 69, 0.2); color:#dc3545; font-size:12px; padding:6px 12px; border-radius:var(--radius-sm); font-weight:700; display:flex; align-items:center; gap:6px;">
                <i class="bi bi-x-circle-fill"></i> Gagal: <?= intval($stats['failed_count'] ?? 0) ?>
            </div>
            <div style="background:rgba(13, 202, 240, 0.1); border:1px solid rgba(13, 202, 240, 0.2); color:#0dcaf0; font-size:12px; padding:6px 12px; border-radius:var(--radius-sm); font-weight:700; display:flex; align-items:center; gap:6px;">
                <i class="bi bi-clock-fill"></i> Pending: <?= intval($stats['pending_count'] ?? 0) ?>
            </div>
        </div>
    </div>

    <!-- Elegant Summary Cards (Matching Dashboard UI) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 24px;">
        <div class="stat-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: center; padding: 20px;">
            <div class="stat-icon blue" style="width: 40px; height: 40px; font-size: 1.2rem; margin-bottom: 12px;"><i class="bi bi-receipt"></i></div>
            <div class="stat-value" style="font-size: 1.5rem; font-weight: 800;"><?= intval($stats['total_trx'] ?? 0) ?></div>
            <div class="stat-label" style="font-size: 10px; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Total Transaksi</div>
        </div>
        <div class="stat-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: center; padding: 20px;">
            <div class="stat-icon green" style="width: 40px; height: 40px; font-size: 1.2rem; margin-bottom: 12px;"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value" style="font-size: 1.25rem; font-weight: 800;">Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label" style="font-size: 10px; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Total Penjualan</div>
        </div>
        <div class="stat-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: center; padding: 20px;">
            <div class="stat-icon orange" style="width: 40px; height: 40px; font-size: 1.2rem; margin-bottom: 12px;"><i class="bi bi-cart-dash"></i></div>
            <div class="stat-value" style="font-size: 1.25rem; font-weight: 800;">Rp <?= number_format($stats['total_cost'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label" style="font-size: 10px; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Total Biaya (Modal)</div>
        </div>
        <div class="stat-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: center; padding: 20px;">
            <div class="stat-icon purple" style="width: 40px; height: 40px; font-size: 1.2rem; margin-bottom: 12px;"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-value" style="font-size: 1.25rem; font-weight: 800; color:var(--success);">Rp <?= number_format($stats['total_profit'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label" style="font-size: 10px; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Total Profit</div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card card-custom" style="border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden;">
        <div class="card-header border-bottom-0 p-0" style="background:var(--surface-2);">
            <ul class="nav nav-tabs px-3 pt-3" style="border-bottom: 1px solid var(--border-color);">
                <li class="nav-item">
                    <a class="nav-link active fw-bold" href="#" id="tab-all" onclick="loadTab('all', event)" style="border-bottom: 3px solid var(--primary); color:var(--text-color); border-top:none; border-left:none; border-right:none; background:transparent;">Semua Transaksi</a>
                </li>
                <li class="nav-item ms-2">
                    <a class="nav-link fw-bold text-muted" href="#" id="tab-pending" onclick="loadTab('pending', event)" style="border-bottom: 3px solid transparent; border-top:none; border-left:none; border-right:none; background:transparent; color:var(--text-muted) !important;">Sedang Diproses</a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" style="font-size:13px; color:var(--text-color);">
                    <thead style="background:var(--surface-3);">
                        <tr>
                            <th class="ps-4 py-3 text-nowrap" style="border-bottom:1px solid var(--border-color);">Tanggal & Waktu</th>
                            <th class="py-3" style="border-bottom:1px solid var(--border-color);">Agen / User</th>
                            <th class="py-3" style="border-bottom:1px solid var(--border-color);">Supplier</th>
                            <th class="py-3 text-end" style="border-bottom:1px solid var(--border-color);">Modal</th>
                            <th class="py-3 text-center" style="border-bottom:1px solid var(--border-color);">SN / Ref</th>
                            <th class="py-3 text-end" style="border-bottom:1px solid var(--border-color);">Profit</th>
                            <th class="pe-4 py-3 text-center" style="border-bottom:1px solid var(--border-color);">Status</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody" style="border-top:none;">
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: var(--surface-3);
    }
</style>

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
        document.getElementById('tab-all').style.color = status === 'all' ? 'var(--text-color)' : 'var(--text-muted)';
        
        document.getElementById('tab-pending').className = status === 'pending' ? 'nav-link active fw-bold' : 'nav-link fw-bold text-muted';
        document.getElementById('tab-pending').style.borderBottomColor = status === 'pending' ? 'var(--primary)' : 'transparent';
        document.getElementById('tab-pending').style.color = status === 'pending' ? 'var(--text-color)' : 'var(--text-muted)';
        
        document.getElementById('history-tbody').innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2 text-primary"></div>Memuat data...</td></tr>';
        
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
                    if (trx.status === 'success') {
                        statusBadge = '<span style="background:rgba(25, 135, 84, 0.1); border:1px solid rgba(25, 135, 84, 0.2); color:#198754; font-size:11px; padding:4px 8px; border-radius:var(--radius-sm); font-weight:700;">SUKSES</span>';
                    } else if (trx.status === 'pending' || trx.status === 'processing') {
                        statusBadge = '<span style="background:rgba(13, 202, 240, 0.1); border:1px solid rgba(13, 202, 240, 0.2); color:#0dcaf0; font-size:11px; padding:4px 8px; border-radius:var(--radius-sm); font-weight:700;">PENDING</span>';
                    } else {
                        statusBadge = '<span style="background:rgba(220, 53, 69, 0.1); border:1px solid rgba(220, 53, 69, 0.2); color:#dc3545; font-size:11px; padding:4px 8px; border-radius:var(--radius-sm); font-weight:700;">GAGAL</span>';
                    }

                    const dateObj = new Date(trx.created_at);
                    const formattedDate = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + '<br><span class="text-muted" style="font-size:11px;">' + dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute:'2-digit' }) + '</span>';

                    tbody.innerHTML += `
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td class="ps-4 py-3 text-nowrap">${formattedDate}</td>
                            <td class="py-3">
                                <div class="fw-bold">${trx.agent_name || 'Admin'}</div>
                                <div class="text-muted" style="font-size:11px;">${trx.customer_no || ''}</div>
                            </td>
                            <td class="py-3"><span class="badge" style="background:var(--surface-3); color:var(--text-color); border:1px solid var(--border-color);">Digiflazz</span></td>
                            <td class="py-3 text-end fw-bold">Rp ${parseInt(trx.modal_price).toLocaleString('id-ID')}</td>
                            <td class="py-3 text-center" style="font-family:monospace; font-size:12px; color:var(--text-muted);">${trx.sn || '-'}</td>
                            <td class="py-3 text-end fw-bold text-success">+ Rp ${parseInt(trx.profit).toLocaleString('id-ID')}</td>
                            <td class="pe-4 py-3 text-center">${statusBadge}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada transaksi ditemukan.</td></tr>';
            }
        } catch (e) {
            console.error(e);
            document.getElementById('history-tbody').innerHTML = '<tr><td colspan="7" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Gagal memuat data.</td></tr>';
        }
    }
</script>


