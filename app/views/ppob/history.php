<div class="page-section" style="max-width:1100px;margin:0 auto;">

    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div class="d-flex align-items-center">
            <a href="<?= BASE_URL ?>ppob" class="btn btn-icon me-3" style="background:var(--surface-2);color:var(--text-primary);border:1px solid var(--border-color);">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="m-0 fw-bold" style="font-family:var(--font-family);font-size:1.05rem;">Laporan Transaksi</h4>
                <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">Riwayat & statistik Digiflazz</div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div style="background:var(--success-bg);border:1px solid rgba(46,196,182,0.25);color:var(--success);font-size:10px;padding:4px 8px;border-radius:var(--radius-sm);font-weight:700;display:flex;align-items:center;gap:4px;">
                <i class="bi bi-check-circle-fill"></i> Sukses: <?= intval($stats['success_count'] ?? 0) ?>
            </div>
            <div style="background:var(--danger-bg);border:1px solid rgba(230,57,70,0.25);color:var(--danger);font-size:10px;padding:4px 8px;border-radius:var(--radius-sm);font-weight:700;display:flex;align-items:center;gap:4px;">
                <i class="bi bi-x-circle-fill"></i> Gagal: <?= intval($stats['failed_count'] ?? 0) ?>
            </div>
            <div style="background:var(--info-bg);border:1px solid rgba(76,201,240,0.25);color:var(--info);font-size:10px;padding:4px 8px;border-radius:var(--radius-sm);font-weight:700;display:flex;align-items:center;gap:4px;">
                <i class="bi bi-clock-fill"></i> Pending: <?= intval($stats['pending_count'] ?? 0) ?>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:10px;margin-bottom:20px;">
        <div class="stat-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:center;padding:12px;border-radius:12px;">
            <div class="stat-icon blue" style="width:28px;height:28px;font-size:0.9rem;margin-bottom:8px;"><i class="bi bi-receipt"></i></div>
            <div class="stat-value" style="font-size:1.1rem;font-weight:700;"><?= intval($stats['total_trx'] ?? 0) ?></div>
            <div class="stat-label" style="font-size:9px;margin-top:2px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Total Transaksi</div>
        </div>
        <div class="stat-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:center;padding:12px;border-radius:12px;">
            <div class="stat-icon green" style="width:28px;height:28px;font-size:0.9rem;margin-bottom:8px;"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value" style="font-size:0.95rem;font-weight:700;">Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label" style="font-size:9px;margin-top:2px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Total Penjualan</div>
        </div>
        <div class="stat-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:center;padding:12px;border-radius:12px;">
            <div class="stat-icon orange" style="width:28px;height:28px;font-size:0.9rem;margin-bottom:8px;"><i class="bi bi-cart-dash"></i></div>
            <div class="stat-value" style="font-size:0.95rem;font-weight:700;">Rp <?= number_format($stats['total_cost'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label" style="font-size:9px;margin-top:2px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Total Modal</div>
        </div>
        <div class="stat-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:center;padding:12px;border-radius:12px;">
            <div class="stat-icon purple" style="width:28px;height:28px;font-size:0.9rem;margin-bottom:8px;"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-value" style="font-size:0.95rem;font-weight:700;color:var(--success);">Rp <?= number_format($stats['total_profit'] ?? 0, 0, ',', '.') ?></div>
            <div class="stat-label" style="font-size:9px;margin-top:2px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Total Profit</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card card-custom" style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
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
        <div class="table-responsive" style="background:var(--surface-1); overflow-x:auto; -webkit-overflow-scrolling:touch; width:100%; display:block;">
            <table style="width:100%;background:var(--surface-1);border-collapse:collapse;font-size:var(--font-size-sm);font-family:var(--font-family);color:var(--text-primary);">
                <thead>
                    <tr style="background:var(--surface-3);">
                        <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);white-space:nowrap;min-width:120px;">Tanggal</th>
                        <th style="padding:12px 8px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:180px;">Produk / Pelanggan</th>
                        <th style="padding:12px 8px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:90px;">Agen</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:95px;">Modal</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:105px;">Jual</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:220px;">SN / Token</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:100px;">Seller</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:90px;">Status</th>
                        <th style="padding:12px 16px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:140px;white-space:nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="history-tbody">
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">
                            <span class="spinner-border spinner-border-sm me-2" style="color:var(--primary);"></span>Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Receipt Preview Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:24px; overflow:hidden; background:var(--surface-1);">
            <div class="modal-header border-0 pb-0" style="background:var(--surface-2); padding:20px 24px 10px;">
                <h5 class="modal-title fw-bold" style="font-family:var(--font-family); font-size:1.05rem;"><i class="bi bi-receipt me-2"></i>Detail Struk Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:var(--btn-close-filter, invert(1) grayscale(100%) brightness(200%));"></button>
            </div>
            <div class="modal-body p-0">
                <div class="text-center py-4 px-4">
                    <!-- Simulated Receipt Area -->
                    <div id="receipt-preview-area" class="p-3 mb-3 text-start bg-white text-dark mx-auto" style="border-radius:12px; max-width:320px; font-family:sans-serif; box-shadow:0 4px 12px rgba(0,0,0,0.08); border:1px solid #eee;">
                        <!-- Dynamically filled -->
                    </div>

                    <!-- Custom Price Input -->
                    <div class="mb-3 text-start mx-auto" style="max-width:320px;">
                        <label class="form-label small text-muted mb-1 fw-bold" style="color:var(--text-secondary) !important;">Harga Jual (Bisa Diubah Untuk Struk)</label>
                        <div class="d-flex align-items-stretch" style="border:1px solid var(--border-color); border-radius:12px; overflow:hidden; background:var(--surface-2);">
                            <div class="d-flex align-items-center justify-content-center px-3 fw-bold text-muted" style="background:rgba(0,0,0,0.03); border-right:1px solid var(--border-color); color:var(--text-muted) !important;">Rp</div>
                            <input type="number" class="form-control border-0 bg-transparent ps-2 fw-bold shadow-none" id="custom-print-price" placeholder="0" style="font-size:16px; color:var(--text-primary); padding:10px 12px;" oninput="updateReceiptTotal(this.value)">
                        </div>
                    </div>

                    <!-- Printer status badge if printer.js is loaded -->
                    <div class="d-flex justify-content-center mx-auto mb-3" style="max-width:320px;">
                        <span id="history-printer-status-badge" class="badge bg-secondary" style="font-size:10px; font-weight:500; display:none;"><i class="bi bi-printer me-1"></i>Printer: Belum Terhubung</span>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 justify-content-center mx-auto" style="max-width:320px;">
                        <button class="btn btn-primary flex-grow-1 rounded-pill fw-bold py-2" id="btn-print-receipt" onclick="executePrintReceipt()">
                            <i class="bi bi-printer me-1"></i>Cetak (BT)
                        </button>
                        <button class="btn btn-success flex-grow-1 rounded-pill fw-bold py-2" id="btn-share-receipt" onclick="executeShareReceipt()">
                            <i class="bi bi-share me-1"></i>Kirim/Share
                        </button>
                    </div>
                    <div class="mt-2 mx-auto" style="max-width:320px;">
                        <button class="btn btn-outline-primary w-100 rounded-pill py-2" onclick="executePreviewBrowser()">
                            <i class="bi bi-window me-1"></i>Cetak Web / PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentStatus = 'all';
    let transactionHistory = [];
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
        document.getElementById('history-tbody').innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);"><span class="spinner-border spinner-border-sm me-2" style="color:var(--primary);"></span>Memuat...</td></tr>`;
        loadHistory(status);
    }

    async function loadHistory(status) {
        try {
            const res = await fetch(`<?= BASE_URL ?>api/ppob/transactions?limit=200&status=${status}`);
            const data = await res.json();
            const tbody = document.getElementById('history-tbody');
            tbody.innerHTML = '';
            
            if (data.success && data.data.length > 0) {
                transactionHistory = data.data;
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
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    const hours = String(d.getHours()).padStart(2, '0');
                    const minutes = String(d.getMinutes()).padStart(2, '0');
                    const seconds = String(d.getSeconds()).padStart(2, '0');
                    const fullDateStr = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                    const profit = parseInt(trx.profit || 0);
                    const profitColor = profit > 0 ? 'color:var(--success);' : 'color:var(--text-muted);';

                    let sellerName = '-';
                    let complaintBtns = '';
                    if (trx.raw_response) {
                        try {
                            const raw = JSON.parse(trx.raw_response);
                            if (raw.tele) {
                                sellerName = raw.tele;
                            } else if (raw.wa) {
                                sellerName = raw.wa;
                            } else {
                                sellerName = 'Digiflazz';
                            }

                            if (trx.status === 'failed' && (raw.wa || raw.tele)) {
                                const trxIdText = trx.digiflazz_trx_id || trx.ref_id;
                                const msg = `S2.${trx.customer_no}, ${fullDateStr} trx Id: ${trxIdText}, gagal, bisa dibantu infokan alasan gagalnya?`;
                                const encodedMsg = encodeURIComponent(msg);
                                complaintBtns += '<div style="margin-top:6px;display:flex;gap:4px;justify-content:center;">';
                                if (raw.wa) {
                                    let waNum = raw.wa.replace(/[^0-9]/g, '');
                                    if (waNum.startsWith('0')) waNum = '62' + waNum.substring(1);
                                    complaintBtns += `<a href="https://wa.me/${waNum}?text=${encodedMsg}" target="_blank" style="background:#25D366;color:#fff;font-size:9px;padding:3px 6px;border-radius:4px;text-decoration:none;font-weight:600;"><i class="bi bi-whatsapp"></i> WA</a>`;
                                }
                                if (raw.tele) {
                                    let teleUsername = raw.tele.replace('@', '');
                                    complaintBtns += `<a href="https://t.me/${teleUsername}?text=${encodedMsg}" target="_blank" style="background:#0088cc;color:#fff;font-size:9px;padding:3px 6px;border-radius:4px;text-decoration:none;font-weight:600;"><i class="bi bi-telegram"></i> Tele</a>`;
                                }
                                complaintBtns += '</div>';
                            }
                        } catch(e) {}
                    }

                    // Action buttons
                    let actionBtns = '';
                    if (trx.status === 'success') {
                        actionBtns = `
                            <div style="display:flex;gap:6px;justify-content:center;align-items:center;">
                                <button type="button" class="btn btn-sm btn-icon" onclick="previewReceipt('${trx.ref_id}')" style="background:var(--primary-bg);color:var(--primary);border:none;padding:5px 8px;border-radius:6px;cursor:pointer;" title="Preview Struk">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon" onclick="printReceipt('${trx.ref_id}')" style="background:var(--success-bg);color:var(--success);border:none;padding:5px 8px;border-radius:6px;cursor:pointer;" title="Cetak Struk">
                                    <i class="bi bi-printer"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon" onclick="shareReceipt('${trx.ref_id}')" style="background:var(--info-bg);color:var(--info);border:none;padding:5px 8px;border-radius:6px;cursor:pointer;" title="Bagikan Struk">
                                    <i class="bi bi-share"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        actionBtns = `
                            <div style="display:flex;gap:6px;justify-content:center;align-items:center;opacity:0.4;">
                                <button type="button" class="btn btn-sm btn-icon" disabled style="background:var(--surface-3);color:var(--text-muted);border:none;padding:5px 8px;border-radius:6px;cursor:not-allowed;" title="Struk hanya tersedia untuk transaksi sukses">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon" disabled style="background:var(--surface-3);color:var(--text-muted);border:none;padding:5px 8px;border-radius:6px;cursor:not-allowed;" title="Struk hanya tersedia untuk transaksi sukses">
                                    <i class="bi bi-printer"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon" disabled style="background:var(--surface-3);color:var(--text-muted);border:none;padding:5px 8px;border-radius:6px;cursor:not-allowed;" title="Struk hanya tersedia untuk transaksi sukses">
                                    <i class="bi bi-share"></i>
                                </button>
                            </div>
                        `;
                    }

                    tbody.innerHTML += `
                        <tr style="border-bottom:1px solid var(--border-color);transition:background var(--transition-fast);${rowAccent}" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='${rowAccent ? rowAccent.replace('background:','') : 'var(--surface-1)'}'">
                            <td style="padding:12px 16px;white-space:nowrap;min-width:120px;">
                                <div style="font-weight:600;font-size:var(--font-size-xs);">${dateStr}</div>
                                <div style="font-size:10px;color:var(--text-muted);">${timeStr}</div>
                            </td>
                            <td style="padding:12px 8px;min-width:180px;">
                                <div style="font-weight:700;font-size:var(--font-size-xs);white-space:nowrap;" title="${trx.product_name}">${trx.product_name}</div>
                                <div style="font-size:10px;color:var(--text-muted);">${trx.customer_no || ''}</div>
                            </td>
                            <td style="padding:12px 8px;min-width:90px;">
                                <div style="font-size:var(--font-size-xs);font-weight:600;">${trx.agent_name || 'Admin'}</div>
                            </td>
                            <td style="padding:12px 8px;text-align:right;font-weight:700;font-size:var(--font-size-xs);white-space:nowrap;min-width:95px;">Rp ${parseInt(trx.modal_price||0).toLocaleString('id-ID')}</td>
                            <td style="padding:12px 8px;text-align:right;font-weight:700;font-size:var(--font-size-xs);white-space:nowrap;min-width:105px;">Rp ${parseInt(trx.sell_price||0).toLocaleString('id-ID')}<br><span style="font-size:9px;${profitColor}font-weight:700;">+${profit.toLocaleString('id-ID')}</span></td>
                            <td style="padding:12px 8px;text-align:left;min-width:220px;">
                                <div style="font-family:monospace;font-size:10px;color:var(--text-primary);white-space:nowrap;" title="SN">${trx.sn || '-'}</div>
                                <div style="font-family:monospace;font-size:9px;color:var(--text-muted);white-space:nowrap;margin-top:2px;" title="Trx ID">Trx: ${trx.digiflazz_trx_id || '-'}</div>
                            </td>
                            <td style="padding:12px 8px;text-align:center;min-width:100px;">
                                <span style="font-size:10px;font-weight:600;color:var(--text-secondary);">${sellerName}</span>
                            </td>
                            <td style="padding:12px 8px;text-align:center;min-width:90px;">
                                ${badge}
                                ${complaintBtns}
                            </td>
                            <td style="padding:12px 16px;text-align:center;min-width:140px;white-space:nowrap;">
                                ${actionBtns}
                            </td>
                        </tr>`;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:48px;color:var(--text-muted);font-size:var(--font-size-sm);"><i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:10px;opacity:0.4;"></i>Tidak ada transaksi</td></tr>`;
            }
        } catch (e) {
            console.error(e);
            document.getElementById('history-tbody').innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--danger);font-size:var(--font-size-sm);"><i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal memuat data.</td></tr>`;
        }
    }

    // Receipt printing, preview, and sharing functions
    let activeTrxData = null;

    function getTrxByRefId(refId) {
        return transactionHistory.find(t => t.ref_id === refId);
    }

    function triggerToast(msg, type = 'info') {
        if (typeof showToast === 'function') {
            showToast(msg, type);
        } else {
            alert(msg);
        }
    }

    function previewReceipt(refId) {
        const trx = getTrxByRefId(refId);
        if (!trx) {
            triggerToast('⚠️ Data transaksi tidak ditemukan', 'warning');
            return;
        }
        activeTrxData = { ...trx };
        
        // Render receipt preview
        document.getElementById('receipt-preview-area').innerHTML = getReceiptPreviewContent(activeTrxData, activeTrxData.sell_price);
        
        // Initialize custom price input
        document.getElementById('custom-print-price').value = parseInt(activeTrxData.sell_price || 0);

        // Check if ThermalPrinter class is loaded
        const printerBadge = document.getElementById('history-printer-status-badge');
        if (typeof ThermalPrinter !== 'undefined') {
            printerBadge.style.display = 'inline-block';
            const printer = window._ppobPrinter || (window._ppobPrinter = new ThermalPrinter());
            if (printer.isConnected()) {
                printerBadge.className = 'badge bg-success';
                printerBadge.innerHTML = '<i class="bi bi-bluetooth me-1"></i>Printer: Terhubung';
            } else {
                printerBadge.className = 'badge bg-secondary';
                printerBadge.innerHTML = '<i class="bi bi-printer me-1"></i>Printer: Belum Terhubung';
            }
        } else {
            printerBadge.style.display = 'none';
        }

        // Show modal
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('receiptModal'));
        modal.show();
    }

    function updateReceiptTotal(value) {
        if (!activeTrxData) return;
        const price = parseInt(value) || 0;
        const previewTotalVal = document.getElementById('preview-total-val');
        if (previewTotalVal) {
            previewTotalVal.innerText = 'Rp ' + price.toLocaleString('id-ID');
        }
    }

    function printReceipt(refId) {
        previewReceipt(refId);
        setTimeout(() => {
            executePrintReceipt();
        }, 300);
    }

    function shareReceipt(refId) {
        previewReceipt(refId);
        setTimeout(() => {
            executeShareReceipt();
        }, 300);
    }

    async function executePrintReceipt() {
        if (!activeTrxData) {
            triggerToast('⚠️ Data transaksi tidak ditemukan', 'warning');
            return;
        }

        const btn = document.getElementById('btn-print-receipt');
        const badge = document.getElementById('history-printer-status-badge');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';
        btn.disabled = true;

        try {
            const d = { ...activeTrxData };
            const customPriceInput = document.getElementById('custom-print-price');
            if (customPriceInput && customPriceInput.value) {
                d.sell_price = parseInt(customPriceInput.value) || d.sell_price;
            }

            if (typeof ThermalPrinter !== 'undefined') {
                const printer = window._ppobPrinter || (window._ppobPrinter = new ThermalPrinter());
                
                if (navigator.bluetooth && !printer.isConnected()) {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghubungkan...';
                    const reconnected = await printer.tryAutoReconnect();
                    if (!reconnected) {
                        await printer.connect();
                    }
                }
                
                if (badge) {
                    badge.className = 'badge bg-success';
                    badge.innerHTML = '<i class="bi bi-bluetooth me-1"></i>Printer: Terhubung';
                }
                
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mencetak...';
                await printer.printDigitalReceipt(d);
                triggerToast('✅ Struk berhasil dikirim ke printer', 'success');
            } else {
                executePreviewBrowser();
            }
        } catch (e) {
            console.error(e);
            triggerToast('❌ Gagal mencetak: ' + (e.message || 'Printer tidak ditemukan/dibatalkan'), 'danger');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    async function executeShareReceipt() {
        if (!activeTrxData) {
            triggerToast('⚠️ Data transaksi tidak ditemukan', 'warning');
            return;
        }

        const btn = document.getElementById('btn-share-receipt');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
        btn.disabled = true;

        try {
            // Load html2canvas if not loaded
            if (typeof html2canvas === 'undefined') {
                await new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = '<?= BASE_URL ?>public/js/html2canvas.min.js';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            const d = { ...activeTrxData };
            const customPriceInput = document.getElementById('custom-print-price');
            if (customPriceInput && customPriceInput.value) {
                d.sell_price = parseInt(customPriceInput.value) || d.sell_price;
            }

            const previewArea = document.getElementById('receipt-preview-area');
            
            // Render receipt image
            const canvas = await html2canvas(previewArea, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            });

            canvas.toBlob(async (blob) => {
                if (!blob) {
                    triggerToast('❌ Gagal membuat gambar struk', 'danger');
                    return;
                }
                const file = new File([blob], `Struk_PPOB_${d.ref_id}.png`, { type: 'image/png' });

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    try {
                        await navigator.share({
                            title: 'Struk Pembayaran AlfarezMart',
                            text: `Struk Pembayaran ${d.product_name}`,
                            files: [file]
                        });
                        triggerToast('✅ Berhasil membagikan struk!', 'success');
                    } catch (err) {
                        console.log('Share canceled or failed', err);
                    }
                } else {
                    // Download fallback
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = file.name;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    triggerToast('✅ Struk berhasil diunduh (perangkat tidak mendukung Web Share)', 'success');
                }
            }, 'image/png');

        } catch (error) {
            console.error('Error sharing receipt:', error);
            triggerToast('❌ Gagal membagikan struk', 'danger');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    function executePreviewBrowser() {
        if (!activeTrxData) return;
        
        const d = { ...activeTrxData };
        const customPriceInput = document.getElementById('custom-print-price');
        if (customPriceInput && customPriceInput.value) {
            d.sell_price = parseInt(customPriceInput.value) || d.sell_price;
        }

        let hasSN = d.sn && d.sn !== '-';
        const isPln = (d.product_name && d.product_name.toLowerCase().includes('pln')) || 
                      (d.buyer_sku_code && d.buyer_sku_code.toLowerCase().includes('pln')) || 
                      (hasSN && d.sn.split('/').length >= 4);
        
        let snTitle = "SN / TOKEN";
        let snValue = d.sn;
        let plnDetailsHtml = '';
        let otherSnHtml = '';
        
        if (isPln && d.sn.includes('/')) {
            const parts = d.sn.split('/');
            if (parts.length >= 4) {
                snTitle = "TOKEN PLN";
                snValue = parts[0];
                const plnName = parts[1] || '';
                const plnTarifPower = parts.length > 4 ? `${parts[2]}/${parts[3]}` : parts[2];
                const plnKwh = parts.length > 4 ? parts[4] : parts[3];
                
                plnDetailsHtml = `
                <div class="row"><div class="label">Nama Mtr</div><div class="value">${plnName}</div></div>
                <div class="row"><div class="label">Tarif/Daya</div><div class="value">${plnTarifPower}</div></div>
                <div class="row"><div class="label">Jml kWh</div><div class="value">${plnKwh}</div></div>
                `;
            }
        } else if (!isPln && hasSN) {
            if (d.sn.toUpperCase().includes('NAMA:') && d.sn.toUpperCase().includes('REFF:')) {
                const snStr = d.sn;
                const namaMatch = snStr.match(/NAMA:\s*([^,]+)/i);
                const reffMatch = snStr.match(/REFF:\s*([^,]+)/i);
                if (namaMatch && namaMatch[1]) otherSnHtml += `<div class="row"><div class="label">Nama Akun</div><div class="value">${namaMatch[1].trim()}</div></div>`;
                if (reffMatch && reffMatch[1]) otherSnHtml += `<div class="row"><div class="label">SN/Ref</div><div class="value">${reffMatch[1].trim()}</div></div>`;
            } else {
                otherSnHtml = `<div class="row"><div class="label">SN / Ref</div><div class="value">${d.sn}</div></div>`;
            }
            hasSN = false; // Hide big bold sn-box for non-PLN
        }
        
        const w = window.open('', '_blank', 'width=380,height=700');
        w.document.write(`<!DOCTYPE html><html><head><title>Struk PPOB</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
    body { font-family: 'Inter', sans-serif; font-size: 13px; width: 300px; margin: 0 auto; padding: 20px; color: #111; background: #fff; }
    * { box-sizing: border-box; }
    .center { text-align: center; }
    .bold { font-weight: 700; }
    .logo { width: 55px; height: 55px; object-fit: contain; margin-bottom: 12px; border-radius: 12px; }
    .header { margin-bottom: 15px; border-bottom: 2px dashed #ddd; padding-bottom: 15px; }
    .store-name { font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; color: #000; }
    .store-desc { font-size: 11px; color: #666; }
    .trx-success { font-size: 14px; font-weight: 800; color: #000; margin: 15px 0 10px; border: 1px solid #000; padding: 6px; border-radius: 6px; }
    .line { border-top: 1px dashed #ddd; margin: 12px 0; }
    .row { display: flex; justify-content: space-between; margin: 8px 0; align-items: flex-start; line-height: 1.4; }
    .label { color: #555; width: 35%; font-size: 12px; }
    .value { font-weight: 600; width: 65%; text-align: right; word-break: break-word; color: #000; }
    .sn-box { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 15px 12px; margin: 15px 0; text-align: center; }
    .sn-title { font-size: 11px; color: #666; margin-bottom: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
    .sn-value { font-size: ${snValue.length > 25 ? '13px' : '16px'}; font-weight: 900; color: #000; letter-spacing: 0.5px; word-break: break-all; font-family: monospace; }
    .total-row { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #000; font-size: 16px; font-weight: 800; color: #000; }
    .footer { text-align: center; margin-top: 30px; font-size: 11px; color: #666; line-height: 1.5; }
    .print-btn { display: block; width: 100%; padding: 14px; background: #0f0f1a; color: #fff; text-align: center; border: none; border-radius: 10px; font-weight: 600; font-size: 14px; margin-top: 25px; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
    .print-btn:active { background: #000; transform: scale(0.98); }
    @media print { body { width: 100%; padding: 0; } .no-print { display: none !important; } .header { border-bottom: 1px dashed #000; } .line { border-top: 1px dashed #000; } .sn-box { background: transparent; border: 1px dashed #000; } .trx-success { border: 1px dashed #000; } }
</style></head><body>
<div class="header center">
    <img src="<?= BASE_URL ?>public/images/mobile_icon.png" class="logo" alt="Logo">
    <div class="store-name">ALFAREZMART</div>
    <div class="store-desc">Struk Pembayaran Produk Digital</div>
</div>
<div class="row"><div class="label">No. Ref</div><div class="value">${d.ref_id}</div></div>
<div class="row"><div class="label">Trx ID</div><div class="value">${d.digiflazz_trx_id || d.ref_id}</div></div>
<div class="row"><div class="label">Tanggal</div><div class="value">${d.created_at}</div></div>
<div class="center trx-success">TRANSAKSI BERHASIL</div>
<div class="row"><div class="label">Produk</div><div class="value">${d.product_name}</div></div>
<div class="row"><div class="label">ID / No.</div><div class="value">${d.customer_no}</div></div>
${d.customer_name ? '<div class="row"><div class="label">Nama</div><div class="value">' + d.customer_name + '</div></div>' : ''}
${plnDetailsHtml}
${otherSnHtml}
<div class="line"></div>
${hasSN ? '<div class="sn-box"><div class="sn-title">' + snTitle + '</div><div class="sn-value">' + snValue + '</div></div>' : ''}
<div class="total-row"><span>TOTAL BAYAR</span><span>Rp ${parseInt(d.sell_price).toLocaleString('id-ID')}</span></div>
<div class="footer"><div class="bold" style="color:#000;margin-bottom:4px;">Terima kasih telah berbelanja</div><div>= Semoga Berkah =</div></div>
<button class="print-btn no-print" onclick="window.print()">🖨️ Cetak Struk Sekarang</button>
</body></html>`);
        w.document.close();
    }

    function getReceiptPreviewContent(d, sellPrice) {
        let hasSN = d.sn && d.sn !== '-';
        const isPln = (d.product_name && d.product_name.toLowerCase().includes('pln')) || 
                      (d.buyer_sku_code && d.buyer_sku_code.toLowerCase().includes('pln')) || 
                      (hasSN && d.sn.split('/').length >= 4);
        
        let snTitle = "SN / TOKEN";
        let snValue = d.sn;
        let plnDetailsHtml = '';
        let otherSnHtml = '';
        
        if (isPln && d.sn.includes('/')) {
            const parts = d.sn.split('/');
            if (parts.length >= 4) {
                snTitle = "TOKEN PLN";
                snValue = parts[0];
                const plnName = parts[1] || '';
                const plnTarifPower = parts.length > 4 ? `${parts[2]}/${parts[3]}` : parts[2];
                const plnKwh = parts.length > 4 ? parts[4] : parts[3];
                
                plnDetailsHtml = `
                <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;">
                    <span style="color:#555;">Nama Mtr</span>
                    <span style="font-weight: 600; text-align: right;">${plnName}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;">
                    <span style="color:#555;">Tarif/Daya</span>
                    <span style="font-weight: 600; text-align: right;">${plnTarifPower}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;">
                    <span style="color:#555;">Jml kWh</span>
                    <span style="font-weight: 600; text-align: right;">${plnKwh}</span>
                </div>
                `;
            }
        } else if (!isPln && hasSN) {
            if (d.sn.toUpperCase().includes('NAMA:') && d.sn.toUpperCase().includes('REFF:')) {
                const snStr = d.sn;
                const namaMatch = snStr.match(/NAMA:\s*([^,]+)/i);
                const reffMatch = snStr.match(/REFF:\s*([^,]+)/i);
                if (namaMatch && namaMatch[1]) otherSnHtml += '<div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;"><span style="color:#555;">Nama Akun</span><span style="font-weight: 600; text-align: right;">' + namaMatch[1].trim() + '</span></div>';
                if (reffMatch && reffMatch[1]) otherSnHtml += '<div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;"><span style="color:#555;">SN/Ref</span><span style="font-weight: 600; text-align: right;">' + reffMatch[1].trim() + '</span></div>';
            } else {
                otherSnHtml = '<div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;"><span style="color:#555;">SN / Ref</span><span style="font-weight: 600; text-align: right; word-break: break-all;">' + d.sn + '</span></div>';
            }
            hasSN = false; // Hide big bold sn-box for non-PLN
        }

        const priceText = parseInt(sellPrice || d.sell_price || 0).toLocaleString('id-ID');

        return `
            <div style="text-align:center; margin-bottom: 12px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; color: #000;">
                <div style="font-size: 16px; font-weight: 800; margin-bottom: 2px;">ALFAREZMART</div>
                <div style="font-size: 10px; color: #666;">Struk Pembayaran Produk Digital</div>
            </div>
            <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;">
                <span style="color:#555;">No. Ref</span>
                <span style="font-weight: 600; text-align: right;">${d.ref_id}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;">
                <span style="color:#555;">Trx ID</span>
                <span style="font-weight: 600; text-align: right;">${d.digiflazz_trx_id || d.ref_id}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;">
                <span style="color:#555;">Tanggal</span>
                <span style="font-weight: 600; text-align: right;">${d.created_at}</span>
            </div>
            <div style="text-align: center; margin: 10px 0; border: 1px solid #111; padding: 4px; border-radius: 4px; font-size: 12px; font-weight: 800; color: #000;">
                TRANSAKSI BERHASIL
            </div>
            <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;">
                <span style="color:#555;">Produk</span>
                <span style="font-weight: 600; text-align: right;">${d.product_name}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;">
                <span style="color:#555;">ID / No.</span>
                <span style="font-weight: 600; text-align: right;">${d.customer_no}</span>
            </div>
            ${d.customer_name ? '<div style="display: flex; justify-content: space-between; margin: 4px 0; font-size: 12px; color: #111;"><span style="color:#555;">Nama</span><span style="font-weight: 600; text-align: right;">' + d.customer_name + '</span></div>' : ''}
            ${plnDetailsHtml}
            ${otherSnHtml}
            <div style="border-top: 1px dashed #ccc; margin: 8px 0;"></div>
            ${hasSN ? '<div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 8px; margin: 8px 0; text-align: center;"><div style="font-size: 10px; color: #666; margin-bottom: 4px; font-weight: 800;">' + snTitle + '</div><div id="preview-sn-value" style="font-size: ' + (snValue.length > 25 ? '13px' : '16px') + '; font-weight: 900; color: #000; letter-spacing: 0.5px; word-break: break-all; font-family: monospace;">' + snValue + '</div></div>' : ''}
            <div style="display: flex; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #000; font-size: 14px; font-weight: 800; color: #000;">
                <span>TOTAL BAYAR</span>
                <span id="preview-total-val">Rp ${priceText}</span>
            </div>
            <div style="text-align: center; margin-top: 20px; font-size: 10px; color: #666; line-height: 1.4;">
                <div style="font-weight: 700; color:#000; margin-bottom:2px;">Terima kasih telah berbelanja</div>
                <div>= Semoga Berkah =</div>
            </div>
        `;
    }
</script>
