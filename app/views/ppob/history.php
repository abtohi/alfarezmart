<div class="page-section" style="max-width:1100px;margin:0 auto;">

    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div class="d-flex align-items-center">
            <a href="<?= BASE_URL ?>ppob" class="btn btn-icon me-3" style="background:var(--surface-2);color:var(--text-primary);border:1px solid var(--border-color);">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="m-0 fw-bold" style="font-family:var(--font-family);font-size:var(--font-size-lg);">Laporan Transaksi PPOB</h4>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:2px;">Riwayat & statistik transaksi Digiflazz</div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div style="background:var(--success-bg);border:1px solid rgba(46,196,182,0.25);color:var(--success);font-size:var(--font-size-xs);padding:6px 12px;border-radius:var(--radius-sm);font-weight:700;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-check-circle-fill"></i> Sukses: <?= intval($stats['success_count'] ?? 0) ?>
            </div>
            <div style="background:var(--danger-bg);border:1px solid rgba(230,57,70,0.25);color:var(--danger);font-size:var(--font-size-xs);padding:6px 12px;border-radius:var(--radius-sm);font-weight:700;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-x-circle-fill"></i> Gagal: <?= intval($stats['failed_count'] ?? 0) ?>
            </div>
            <div style="background:var(--info-bg);border:1px solid rgba(76,201,240,0.25);color:var(--info);font-size:var(--font-size-xs);padding:6px 12px;border-radius:var(--radius-sm);font-weight:700;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-clock-fill"></i> Pending: <?= intval($stats['pending_count'] ?? 0) ?>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;margin-bottom:20px;">
        <div class="stat-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:center;padding:18px;">
            <div class="stat-icon blue" style="width:38px;height:38px;font-size:1.1rem;margin-bottom:10px;"><i class="bi bi-receipt"></i></div>
            <div class="stat-value" style="font-size:1.5rem;font-weight:800;"><?= intval($stats['total_trx'] ?? 0) ?></div>
            <div class="stat-label" style="font-size:9px;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Total Transaksi</div>
        </div>
        <div class="stat-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:center;padding:18px;">
            <div class="stat-icon green" style="width:38px;height:38px;font-size:1.1rem;margin-bottom:10px;"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value" style="font-size:1.15rem;font-weight:800;">Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label" style="font-size:9px;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Total Penjualan</div>
        </div>
        <div class="stat-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:center;padding:18px;">
            <div class="stat-icon orange" style="width:38px;height:38px;font-size:1.1rem;margin-bottom:10px;"><i class="bi bi-cart-dash"></i></div>
            <div class="stat-value" style="font-size:1.15rem;font-weight:800;">Rp <?= number_format($stats['total_cost'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label" style="font-size:9px;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Total Modal</div>
        </div>
        <div class="stat-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:center;padding:18px;">
            <div class="stat-icon purple" style="width:38px;height:38px;font-size:1.1rem;margin-bottom:10px;"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-value" style="font-size:1.15rem;font-weight:800;color:var(--success);">Rp <?= number_format($stats['total_profit'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label" style="font-size:9px;margin-top:4px;text-transform:uppercase;letter-spacing:0.5px;">Total Profit</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card card-custom" style="border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
        <!-- Tabs -->
        <div style="background:var(--surface-2);border-bottom:1px solid var(--border-color);padding:0 16px;display:flex;gap:4px;">
            <button id="tab-all" onclick="loadTab('all',event)"
                style="background:none;border:none;border-bottom:3px solid var(--primary);color:var(--text-primary);font-weight:700;font-size:var(--font-size-sm);font-family:var(--font-family);padding:14px 12px;cursor:pointer;transition:all var(--transition-fast);">
                Semua
            </button>
            <button id="tab-pending" onclick="loadTab('pending',event)"
                style="background:none;border:none;border-bottom:3px solid transparent;color:var(--text-muted);font-weight:600;font-size:var(--font-size-sm);font-family:var(--font-family);padding:14px 12px;cursor:pointer;transition:all var(--transition-fast);">
                Diproses
            </button>
            <button id="tab-success" onclick="loadTab('success',event)"
                style="background:none;border:none;border-bottom:3px solid transparent;color:var(--text-muted);font-weight:600;font-size:var(--font-size-sm);font-family:var(--font-family);padding:14px 12px;cursor:pointer;transition:all var(--transition-fast);">
                Sukses
            </button>
            <button id="tab-failed" onclick="loadTab('failed',event)"
                style="background:none;border:none;border-bottom:3px solid transparent;color:var(--text-muted);font-weight:600;font-size:var(--font-size-sm);font-family:var(--font-family);padding:14px 12px;cursor:pointer;transition:all var(--transition-fast);">
                Gagal
            </button>
        </div>
        <!-- Table -->
        <div class="table-responsive">
            <table style="width:100%;border-collapse:collapse;font-size:var(--font-size-sm);font-family:var(--font-family);color:var(--text-primary);">
                <thead>
                    <tr style="background:var(--surface-3);">
                        <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);white-space:nowrap;">Tanggal</th>
                        <th style="padding:12px 8px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);">Produk / Pelanggan</th>
                        <th style="padding:12px 8px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);">Agen</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);">Modal</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);">Jual</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);">SN / Token</th>
                        <th style="padding:12px 16px 12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);">Status</th>
                    </tr>
                </thead>
                <tbody id="history-tbody">
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                            <span class="spinner-border spinner-border-sm me-2" style="color:var(--primary);"></span>Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let currentStatus = 'all';
    const TABS = ['tab-all','tab-pending','tab-success','tab-failed'];

    document.addEventListener('DOMContentLoaded', () => loadHistory('all'));

    function loadTab(status, event) {
        event.preventDefault();
        currentStatus = status;
        TABS.forEach(id => {
            const el = document.getElementById(id);
            const isActive = id === 'tab-' + status;
            el.style.borderBottomColor = isActive ? 'var(--primary)' : 'transparent';
            el.style.color = isActive ? 'var(--text-primary)' : 'var(--text-muted)';
            el.style.fontWeight = isActive ? '700' : '600';
        });
        document.getElementById('history-tbody').innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);"><span class="spinner-border spinner-border-sm me-2" style="color:var(--primary);"></span>Memuat...</td></tr>`;
        loadHistory(status);
    }

    async function loadHistory(status) {
        try {
            const res = await fetch(`<?= BASE_URL ?>api/ppob/transactions?limit=200&status=${status}`);
            const data = await res.json();
            const tbody = document.getElementById('history-tbody');
            tbody.innerHTML = '';
            if (data.success && data.data.length > 0) {
                data.data.forEach(trx => {
                    let badge, rowAccent = '';
                    if (trx.status === 'success') {
                        badge = `<span style="background:var(--success-bg);border:1px solid rgba(46,196,182,0.3);color:var(--success);font-size:10px;padding:3px 8px;border-radius:var(--radius-sm);font-weight:700;white-space:nowrap;">✓ SUKSES</span>`;
                    } else if (trx.status === 'pending' || trx.status === 'processing') {
                        badge = `<span style="background:var(--info-bg);border:1px solid rgba(76,201,240,0.3);color:var(--info);font-size:10px;padding:3px 8px;border-radius:var(--radius-sm);font-weight:700;white-space:nowrap;">⏳ PROSES</span>`;
                        rowAccent = 'background:rgba(76,201,240,0.02);';
                    } else {
                        badge = `<span style="background:var(--danger-bg);border:1px solid rgba(230,57,70,0.3);color:var(--danger);font-size:10px;padding:3px 8px;border-radius:var(--radius-sm);font-weight:700;white-space:nowrap;">✗ GAGAL</span>`;
                        rowAccent = 'opacity:0.75;';
                    }
                    const d = new Date(trx.created_at);
                    const dateStr = d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
                    const timeStr = d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
                    const profit = parseInt(trx.profit || 0);
                    const profitColor = profit > 0 ? 'color:var(--success);' : 'color:var(--text-muted);';
                    tbody.innerHTML += `
                        <tr style="border-bottom:1px solid var(--border-color);transition:background var(--transition-fast);${rowAccent}" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='${rowAccent ? '' : 'transparent'}'">
                            <td style="padding:12px 16px;white-space:nowrap;">
                                <div style="font-weight:600;font-size:var(--font-size-xs);">${dateStr}</div>
                                <div style="font-size:10px;color:var(--text-muted);">${timeStr}</div>
                            </td>
                            <td style="padding:12px 8px;max-width:200px;">
                                <div style="font-weight:700;font-size:var(--font-size-xs);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${trx.product_name}">${trx.product_name}</div>
                                <div style="font-size:10px;color:var(--text-muted);">${trx.customer_no || ''}</div>
                            </td>
                            <td style="padding:12px 8px;">
                                <div style="font-size:var(--font-size-xs);font-weight:600;">${trx.agent_name || 'Admin'}</div>
                            </td>
                            <td style="padding:12px 8px;text-align:right;font-weight:700;font-size:var(--font-size-xs);white-space:nowrap;">Rp ${parseInt(trx.modal_price||0).toLocaleString('id-ID')}</td>
                            <td style="padding:12px 8px;text-align:right;font-weight:700;font-size:var(--font-size-xs);white-space:nowrap;">Rp ${parseInt(trx.sell_price||0).toLocaleString('id-ID')}<br><span style="font-size:9px;${profitColor}font-weight:700;">+${profit.toLocaleString('id-ID')}</span></td>
                            <td style="padding:12px 8px;text-align:center;">
                                <span style="font-family:monospace;font-size:10px;color:var(--text-secondary);word-break:break-all;">${trx.sn || '-'}</span>
                            </td>
                            <td style="padding:12px 16px 12px 8px;text-align:center;">${badge}</td>
                        </tr>`;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:48px;color:var(--text-muted);font-size:var(--font-size-sm);"><i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:10px;opacity:0.4;"></i>Tidak ada transaksi</td></tr>`;
            }
        } catch (e) {
            console.error(e);
            document.getElementById('history-tbody').innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--danger);font-size:var(--font-size-sm);"><i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal memuat data.</td></tr>`;
        }
    }
</script>
