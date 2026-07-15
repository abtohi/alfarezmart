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
                        <th style="padding:12px 16px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:140px;white-space:nowrap;">Aksi</th>
                        <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);white-space:nowrap;min-width:120px;">Tanggal</th>
                        <th style="padding:12px 8px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:180px;">Produk / Pelanggan</th>
                        <th style="padding:12px 8px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:90px;">Agen</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:95px;">Modal</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:105px;">Jual</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:140px;">SN / Token</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:100px;">Seller</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:90px;">Status</th>
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
        <div id="pagination-container" class="d-flex flex-wrap justify-content-between align-items-center mt-3 px-3 pb-3 gap-2" style="font-size:12px;">
            <div id="pagination-info" style="color:var(--text-muted); font-weight:500;">Menampilkan 0 dari 0 data</div>
            <div id="pagination-controls" class="d-flex gap-1"></div>
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
    let currentPage = 1;
    const itemsPerPage = 10;
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
            
            if (data.success && data.data.length > 0) {
                transactionHistory = data.data;
                currentPage = 1;
                renderTable();
            } else {
                transactionHistory = [];
                const tbody = document.getElementById('history-tbody');
                tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">Tidak ada transaksi ${status === 'all' ? '' : status}.</td></tr>`;
                document.getElementById('pagination-info').innerText = 'Menampilkan 0 dari 0 data';
                document.getElementById('pagination-controls').innerHTML = '';
            }
        } catch (e) {
            console.error('Failed to load history', e);
            document.getElementById('history-tbody').innerHTML = `<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--danger);">Gagal memuat data transaksi.</td></tr>`;
        }
    }

    function renderTable() {
        const tbody = document.getElementById('history-tbody');
        tbody.innerHTML = '';
        
        const totalItems = transactionHistory.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        if (currentPage < 1) currentPage = 1;
        if (currentPage > totalPages) currentPage = totalPages;
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
        
        const pageData = transactionHistory.slice(startIndex, endIndex);
        
        pageData.forEach(trx => {
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

            let sellerName = trx.seller_name && trx.seller_name !== '' ? trx.seller_name : '-';
            let complaintBtns = '';
            if (trx.raw_response) {
                try {
                    const raw = JSON.parse(trx.raw_response);
                    if (sellerName === '-') {
                        if (raw.tele) {
                            sellerName = raw.tele;
                        } else if (raw.wa) {
                            sellerName = raw.wa;
                        } else {
                            sellerName = 'Digiflazz';
                        }
                    }

                    if (trx.status === 'failed' && (raw.wa || raw.tele)) {
                        const trxIdText = trx.digiflazz_trx_id || trx.ref_id;
                        const msg = `${trx.buyer_sku_code}.${trx.customer_no}, ${fullDateStr} trx Id: ${trxIdText}, gagal, bisa dibantu infokan alasan gagalnya?`;
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

            let copyBtn = '';
            if (trx.sn && trx.sn !== '-') {
                copyBtn = `
                    <button type="button" onclick="copyToken('${trx.sn.replace(/'/g, "\\'")}')" class="btn btn-sm" style="background:var(--surface-3);color:var(--text-secondary);border:none;padding:2px 4px;border-radius:4px;cursor:pointer;margin-left:4px;display:inline-flex;align-items:center;justify-content:center;" title="Salin Token">
                        <i class="bi bi-clipboard" style="font-size:10px;"></i>
                    </button>
                `;
            }

            tbody.innerHTML += `
                <tr style="border-bottom:1px solid var(--border-color);transition:background var(--transition-fast);${rowAccent}" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='${rowAccent ? rowAccent.replace('background:','') : 'var(--surface-1)'}'">
                    <td style="padding:12px 16px;text-align:center;min-width:140px;white-space:nowrap;">
                        ${actionBtns}
                    </td>
                    <td style="padding:12px 16px;white-space:nowrap;">
                        <div style="font-weight:700;color:var(--text-primary);margin-bottom:2px;">${dateStr}</div>
                        <div style="font-size:10px;color:var(--text-muted);"><i class="bi bi-clock me-1"></i>${timeStr}</div>
                    </td>
                    <td style="padding:12px 8px;">
                        <div style="font-weight:700;color:var(--primary);font-size:11px;margin-bottom:2px;display:flex;align-items:center;gap:6px;">
                            ${trx.product_name}
                            ${trx.is_postpaid === 1 ? '<span style="background:rgba(245,158,11,0.1);color:#f59e0b;padding:2px 4px;border-radius:4px;font-size:8px;">PASCABAYAR</span>' : ''}
                        </div>
                        <div style="font-family:monospace;font-size:11px;color:var(--text-secondary);background:var(--surface-2);padding:2px 6px;border-radius:4px;display:inline-block;">${trx.customer_no}</div>
                    </td>
                    <td style="padding:12px 8px;font-size:11px;font-weight:600;color:var(--text-secondary);">
                        ${trx.created_by_name || 'Agen'}
                    </td>
                    <td style="padding:12px 8px;text-align:right;">
                        <div style="font-weight:700;color:var(--text-primary);font-size:11px;">Rp ${parseInt(trx.modal_price).toLocaleString('id-ID')}</div>
                    </td>
                    <td style="padding:12px 8px;text-align:right;">
                        <div style="font-weight:700;color:var(--text-primary);font-size:11px;">Rp ${parseInt(trx.sell_price).toLocaleString('id-ID')}</div>
                        <div style="font-size:9px;margin-top:2px;${profitColor}font-weight:600;">+Rp ${profit.toLocaleString('id-ID')}</div>
                    </td>
                    <td style="padding:12px 8px;text-align:center;">
                        <div style="font-family:monospace;font-size:10px;color:var(--text-primary);word-break:break-all;max-width:140px;margin:0 auto;">${trx.sn || '-'}</div>
                        ${copyBtn}
                    </td>
                    <td style="padding:12px 8px;text-align:center;">
                        <span style="font-size:10px;font-weight:600;color:var(--text-secondary);background:var(--surface-2);padding:2px 6px;border-radius:4px;">${sellerName}</span>
                        ${complaintBtns}
                    </td>
                    <td style="padding:12px 8px;text-align:center;">
                        ${badge}
                        <div style="font-size:9px;color:var(--text-muted);margin-top:4px;">Ref: ${trx.ref_id}</div>
                    </td>
                </tr>
            `;
        });
        
        renderPagination();
    }
    
    function renderPagination() {
        const totalItems = transactionHistory.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        const info = document.getElementById('pagination-info');
        const controls = document.getElementById('pagination-controls');
        
        if (totalItems === 0) {
            info.innerText = 'Menampilkan 0 dari 0 data';
            controls.innerHTML = '';
            return;
        }
        
        const startIndex = (currentPage - 1) * itemsPerPage + 1;
        const endIndex = Math.min(currentPage * itemsPerPage, totalItems);
        info.innerText = `Menampilkan ${startIndex} - ${endIndex} dari ${totalItems} data`;
        
        let btns = '';
        
        // Prev
        btns += `<button class="btn btn-sm ${currentPage === 1 ? 'btn-light disabled' : 'btn-outline-primary'}" onclick="changePage(${currentPage - 1})" style="border-radius:6px; padding:4px 10px;">&laquo; Prev</button>`;
        
        // Page numbers (simple, max 5 pages visible around current)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            btns += `<button class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-primary'}" onclick="changePage(${i})" style="border-radius:6px; padding:4px 10px;">${i}</button>`;
        }
        
        // Next
        btns += `<button class="btn btn-sm ${currentPage === totalPages ? 'btn-light disabled' : 'btn-outline-primary'}" onclick="changePage(${currentPage + 1})" style="border-radius:6px; padding:4px 10px;">Next &raquo;</button>`;
        
        controls.innerHTML = btns;
    }
    
    function changePage(page) {
        const totalPages = Math.ceil(transactionHistory.length / itemsPerPage);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderTable();
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

    function copyToken(sn) {
        if (!sn || sn === '-') return;
        let token = sn.includes('/') ? sn.split('/')[0] : sn;
        token = token.trim();
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(token).then(() => {
                triggerToast('📋 Token disalin: ' + token, 'success');
            }).catch(err => {
                console.error('Failed to copy:', err);
                triggerToast('❌ Gagal menyalin token', 'danger');
            });
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = token;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                triggerToast('📋 Token disalin: ' + token, 'success');
            } catch (err) {
                console.error('Fallback copy failed:', err);
                triggerToast('❌ Gagal menyalin token', 'danger');
            }
            document.body.removeChild(textArea);
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

    // =====================================================================
    // RECEIPT DESIGN SYSTEM — Elegant, Product-Differentiated
    // =====================================================================

    /**
     * Detect product category from transaction data.
     * Returns one of: 'pln', 'dana', 'shopee', 'gopay', 'ovo', 'linkaja', 'pulsa', 'paket', 'bpjs', 'pdam', 'telkom', 'voucher', 'other'
     */
    function detectProductType(d) {
        const name  = (d.product_name  || '').toLowerCase();
        const sku   = (d.buyer_sku_code || '').toLowerCase();
        const sn    = (d.sn            || '');
        const hasSN = sn && sn !== '-';

        if (name.includes('pln') || sku.includes('pln') || (hasSN && sn.split('/').length >= 4)) return 'pln';
        if (name.includes('dana') || sku.includes('dana') || name.includes('dnid')) return 'dana';
        if (name.includes('shopeepay') || name.includes('shopee') || sku.includes('shopee') || sku.includes('spay')) return 'shopee';
        if (name.includes('gopay') || sku.includes('gopay') || sku.includes('gpay')) return 'gopay';
        if (name.includes('ovo') || sku.includes('ovo')) return 'ovo';
        if (name.includes('link aja') || name.includes('linkaja') || sku.includes('linkaja')) return 'linkaja';
        if (name.includes('bpjs') || sku.includes('bpjs')) return 'bpjs';
        if (name.includes('pdam') || sku.includes('pdam')) return 'pdam';
        if (name.includes('telkom') || name.includes('indihome') || sku.includes('telkom')) return 'telkom';
        if (name.includes('paket data') || name.includes('paket internet') || sku.includes('data')) return 'paket';
        if (name.includes('pulsa') || name.includes('prabayar') || /^(xl|tsel|isat|axis|tri|hnet|smartfren)/i.test(sku)) return 'pulsa';
        if (name.includes('voucher') || name.includes('game')) return 'voucher';
        return 'other';
    }

    /**
     * Get theme config for each product type:
     * { accent, accentLight, accentDark, label, icon, gradient, badgeBg, badgeColor }
     */
    function getProductTheme(type) {
        const themes = {
            pln:     { accent:'#0073C6', accentLight:'#e8f4ff', accentDark:'#004f8b', gradient:'linear-gradient(135deg,#0073C6,#00b4d8)', label:'PLN Prepaid', icon:'⚡' },
            dana:    { accent:'#118EEA', accentLight:'#e5f4ff', accentDark:'#0068c2', gradient:'linear-gradient(135deg,#118EEA,#42a5f5)', label:'DANA', icon:'💙' },
            shopee:  { accent:'#EE4D2D', accentLight:'#fff1ee', accentDark:'#c73c21', gradient:'linear-gradient(135deg,#EE4D2D,#ff7043)', label:'ShopeePay', icon:'🧡' },
            gopay:   { accent:'#00AED6', accentLight:'#e6f9ff', accentDark:'#0089ab', gradient:'linear-gradient(135deg,#00AED6,#00e5ff)', label:'GoPay', icon:'💚' },
            ovo:     { accent:'#4C2A86', accentLight:'#f2eeff', accentDark:'#3a1f6a', gradient:'linear-gradient(135deg,#4C2A86,#7b5ea7)', label:'OVO', icon:'💜' },
            linkaja: { accent:'#E8192C', accentLight:'#fff0f1', accentDark:'#b91422', gradient:'linear-gradient(135deg,#E8192C,#ff5252)', label:'LinkAja', icon:'❤️' },
            bpjs:    { accent:'#00873C', accentLight:'#e6f7ee', accentDark:'#005c28', gradient:'linear-gradient(135deg,#00873C,#4caf50)', label:'BPJS Kesehatan', icon:'🏥' },
            pdam:    { accent:'#1565C0', accentLight:'#e8eeff', accentDark:'#0d3d73', gradient:'linear-gradient(135deg,#1565C0,#1e88e5)', label:'PDAM Air', icon:'💧' },
            telkom:  { accent:'#E40427', accentLight:'#fff0f1', accentDark:'#b4001e', gradient:'linear-gradient(135deg,#E40427,#f44336)', label:'Telkom/IndiHome', icon:'📡' },
            paket:   { accent:'#0277BD', accentLight:'#e6f4ff', accentDark:'#01579b', gradient:'linear-gradient(135deg,#0277BD,#29b6f6)', label:'Paket Data', icon:'📶' },
            pulsa:   { accent:'#2E7D32', accentLight:'#e8f5e9', accentDark:'#1b5e20', gradient:'linear-gradient(135deg,#2E7D32,#66bb6a)', label:'Pulsa', icon:'📱' },
            voucher: { accent:'#6A1B9A', accentLight:'#f4e6ff', accentDark:'#4a0072', gradient:'linear-gradient(135deg,#6A1B9A,#ab47bc)', label:'Voucher / Game', icon:'🎮' },
            other:   { accent:'#263238', accentLight:'#eceff1', accentDark:'#1a2327', gradient:'linear-gradient(135deg,#263238,#546e7a)', label:'Produk Digital', icon:'🔷' },
        };
        return themes[type] || themes.other;
    }

    /**
     * Parse SN field into structured data based on product type.
     * Returns { snTitle, snValue, extraRows: [{label, value}], accountName }
     */
    function parseSN(d, type) {
        const sn = (d.sn || '').trim();
        const hasSN = sn && sn !== '-';
        let result = { snTitle: 'Referensi', snValue: sn, extraRows: [], accountName: null, hasSN };

        if (!hasSN) return result;

        // PLN: parts split by '/'
        if (type === 'pln' && sn.includes('/')) {
            const parts = sn.split('/');
            if (parts.length >= 4) {
                result.snTitle  = 'Token PLN';
                result.snValue  = parts[0].trim();
                const mtrName   = parts[1]?.trim() || '';
                const tarif     = parts.length > 4 ? `${parts[2]}/${parts[3]}` : (parts[2] || '');
                const kwh       = parts.length > 4 ? parts[4] : (parts[3] || '');
                if (mtrName) result.extraRows.push({ label: 'Nama Pelanggan', value: mtrName });
                if (tarif)   result.extraRows.push({ label: 'Tarif / Daya',   value: tarif   });
                if (kwh)     result.extraRows.push({ label: 'Jumlah kWh',     value: kwh     });
            }
            return result;
        }

        // E-wallet (DANA, GoPay, ShopeePay, OVO, LinkAja): parse NAMA: ... REFF: ...
        const ewallets = ['dana','gopay','shopee','ovo','linkaja'];
        if (ewallets.includes(type)) {
            result.snTitle = 'ID Referensi';
            if (sn.toUpperCase().includes('NAMA:') && sn.toUpperCase().includes('REFF:')) {
                const namaMatch = sn.match(/NAMA:\s*([^,\n]+)/i);
                const reffMatch = sn.match(/REFF:\s*([^,\n]+)/i);
                if (namaMatch?.[1]) {
                    result.accountName = namaMatch[1].trim();
                }
                result.snValue = reffMatch?.[1]?.trim() || sn;
            } else {
                // Try to detect if it's purely a ref code
                result.snValue = sn;
            }
            result.hasSN = true;
            return result;
        }

        // Pulsa / Paket Data: no structured SN usually; show as-is
        if (type === 'pulsa' || type === 'paket') {
            result.snTitle = 'Nomor SN';
            result.snValue = sn;
            return result;
        }

        // Generic NAMA: REFF: pattern
        if (sn.toUpperCase().includes('NAMA:') && sn.toUpperCase().includes('REFF:')) {
            const namaMatch = sn.match(/NAMA:\s*([^,\n]+)/i);
            const reffMatch = sn.match(/REFF:\s*([^,\n]+)/i);
            if (namaMatch?.[1]) result.accountName = namaMatch[1].trim();
            result.snValue = reffMatch?.[1]?.trim() || sn;
            return result;
        }

        return result;
    }

    /**
     * Build the elegant print-window HTML for a specific product type.
     */
    function buildPrintHTML(d, type, theme, snData) {
        const BASE = '<?= BASE_URL ?>';
        const logoSrc = BASE + 'public/images/mobile_icon.png';
        const price = parseInt(d.sell_price || 0).toLocaleString('id-ID');
        const dateStr = d.created_at || '';

        // Watermark tiles — repeated logo as base64 CSS bg is too complex; use img tags in a grid
        // We use a pseudo-element approach with CSS and a data-uri trick
        const wmCount = 9; // 3x3 grid
        let wmHtml = '';
        for (let i = 0; i < wmCount; i++) {
            wmHtml += `<img src="${logoSrc}" class="wm-tile" alt="">`;
        }

        // Extra info rows from SN parsing
        let extraRowsHtml = snData.extraRows.map(r => `
            <tr class="info-row">
                <td class="info-label">${r.label}</td>
                <td class="info-value">${r.value}</td>
            </tr>
        `).join('');

        // Account name row (e-wallet)
        let accountRowHtml = '';
        if (snData.accountName) {
            accountRowHtml = `
                <tr class="info-row">
                    <td class="info-label">Nama Akun</td>
                    <td class="info-value highlight-val">${snData.accountName}</td>
                </tr>
            `;
        }

        // SN / Token box
        let snBoxHtml = '';
        if (snData.hasSN && snData.snValue) {
            const snFontSize = snData.snValue.length > 20 ? '12px' : (snData.snValue.length > 14 ? '15px' : '19px');
            snBoxHtml = `
                <div class="sn-section">
                    <div class="sn-label">${snData.snTitle}</div>
                    <div class="sn-value" style="font-size:${snFontSize}">${snData.snValue}</div>
                </div>
            `;
        }

        // Customer name — only show if not e-wallet (e-wallet shows accountName instead)
        const ewallets = ['dana','gopay','shopee','ovo','linkaja'];
        const showCustomerName = d.customer_name && !ewallets.includes(type) && type !== 'pln';

        return `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk ${theme.label} — AlfarezMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 12px 40px;
            color: #1a1a2e;
        }

        .receipt-outer {
            width: 100%;
            max-width: 340px;
        }

        /* Main card */
        .receipt {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.07);
            position: relative;
        }

        /* Watermark layer */
        .watermark {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            align-items: center;
            justify-items: center;
            pointer-events: none;
            z-index: 0;
            padding: 20px;
            gap: 0;
        }
        .wm-tile {
            width: 62px;
            height: 62px;
            object-fit: contain;
            opacity: 0.045;
            transform: rotate(-18deg);
            filter: grayscale(100%);
            display: block;
        }

        /* Header strip */
        .receipt-header {
            background: ${theme.gradient};
            padding: 22px 20px 28px;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .header-logo-wrap {
            width: 62px;
            height: 62px;
            background: rgba(255,255,255,0.95);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
            overflow: hidden;
        }
        .header-logo { width: 52px; height: 52px; object-fit: contain; }
        .header-store  { font-size: 17px; font-weight: 900; color: #fff; letter-spacing: 0.8px; text-transform: uppercase; }
        .header-tagline { font-size: 10.5px; color: rgba(255,255,255,0.82); margin-top: 3px; font-weight: 500; letter-spacing: 0.3px; }
        .header-product-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            margin-top: 10px;
            letter-spacing: 0.4px;
        }

        /* Jagged edge cut */
        .jagged {
            height: 18px;
            background: #fff;
            position: relative;
            z-index: 2;
        }
        .jagged::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 0; right: 0;
            height: 18px;
            background: radial-gradient(circle at 50% 0%, #fff 12px, transparent 13px);
            background-size: 24px 18px;
            background-repeat: repeat-x;
        }
        .jagged-top {
            height: 18px;
            background: ${theme.accent};
            position: relative;
            z-index: 2;
        }
        .jagged-top::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0; right: 0;
            height: 18px;
            background: radial-gradient(circle at 50% 100%, #fff 12px, transparent 13px);
            background-size: 24px 18px;
            background-repeat: repeat-x;
        }

        /* Body */
        .receipt-body {
            padding: 6px 22px 20px;
            position: relative;
            z-index: 2;
        }

        /* Success badge */
        .success-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            background: ${theme.accentLight};
            border: 1.5px solid ${theme.accent};
            border-radius: 10px;
            padding: 8px 14px;
            margin-bottom: 16px;
            color: ${theme.accentDark};
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .success-dot { width: 8px; height: 8px; background: ${theme.accent}; border-radius: 50%; flex-shrink: 0; }

        /* Meta info (ref, trx, date) */
        .meta-grid {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 14px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            padding: 3px 0;
        }
        .meta-row + .meta-row { border-top: 1px solid #eee; }
        .meta-key { color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .meta-val { color: #222; font-weight: 700; font-family: 'JetBrains Mono', monospace; font-size: 9px; text-align: right; }

        /* Info table */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-row td { padding: 6px 0; vertical-align: top; }
        .info-row + .info-row td { border-top: 1px dashed #eee; }
        .info-label { color: #777; font-size: 11px; font-weight: 500; width: 42%; padding-right: 8px; }
        .info-value { color: #111; font-size: 11.5px; font-weight: 700; text-align: right; word-break: break-word; }
        .highlight-val { color: ${theme.accentDark}; }

        /* Divider */
        .divider {
            border: none;
            border-top: 1.5px dashed #d4d8dd;
            margin: 14px 0;
        }

        /* SN / Token section */
        .sn-section {
            background: ${theme.accentLight};
            border: 1.5px solid ${theme.accent}33;
            border-radius: 12px;
            padding: 14px 14px;
            text-align: center;
            margin: 14px 0;
        }
        .sn-label {
            font-size: 9.5px;
            font-weight: 800;
            color: ${theme.accentDark};
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 6px;
        }
        .sn-value {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: #111;
            word-break: break-all;
            letter-spacing: 0.5px;
            line-height: 1.4;
        }

        /* Total row */
        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: ${theme.gradient};
            border-radius: 12px;
            padding: 14px 16px;
            margin-top: 6px;
        }
        .total-label { font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.9); text-transform: uppercase; letter-spacing: 0.5px; }
        .total-amount { font-size: 18px; font-weight: 900; color: #fff; letter-spacing: -0.5px; }

        /* Footer */
        .receipt-footer {
            text-align: center;
            padding: 16px 22px 22px;
            border-top: 1.5px dashed #e0e0e0;
            margin-top: 14px;
            position: relative;
            z-index: 2;
        }
        .footer-tagline {
            font-size: 11px;
            font-weight: 700;
            color: #333;
            margin-bottom: 3px;
        }
        .footer-sub {
            font-size: 9.5px;
            color: #999;
            font-style: italic;
        }
        .validity-note {
            font-size: 9px;
            color: ${theme.accentDark};
            background: ${theme.accentLight};
            border-radius: 6px;
            padding: 5px 10px;
            margin-top: 10px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        /* Print button */
        .print-btn {
            display: block;
            width: 100%;
            max-width: 340px;
            margin: 18px auto 0;
            padding: 14px;
            background: ${theme.gradient};
            color: #fff;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 16px ${theme.accent}44;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.3px;
        }
        .print-btn:active { opacity: 0.85; transform: scale(0.98); }

        @media print {
            body { background: #fff; padding: 0; min-height: unset; }
            .receipt { box-shadow: none; border-radius: 0; }
            .print-btn, .no-print { display: none !important; }
            .receipt-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .success-badge, .meta-grid, .sn-section, .total-section { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="receipt-outer">
    <div class="receipt">
        <!-- Watermark -->
        <div class="watermark">${wmHtml}</div>

        <!-- Header -->
        <div class="receipt-header">
            <div class="header-logo-wrap">
                <img src="${logoSrc}" class="header-logo" alt="AlfarezMart" crossorigin="anonymous">
            </div>
            <div class="header-store">AlfarezMart</div>
            <div class="header-tagline">Pusat Pembayaran Produk Digital</div>
            <div class="header-product-badge">${theme.icon} ${theme.label}</div>
        </div>
        <div class="jagged-top"></div>

        <!-- Body -->
        <div class="receipt-body">
            <!-- Success badge -->
            <div class="success-badge">
                <span class="success-dot"></span>
                Transaksi Berhasil
                <span class="success-dot"></span>
            </div>

            <!-- Meta info -->
            <div class="meta-grid">
                <div class="meta-row">
                    <span class="meta-key">No. Referensi</span>
                    <span class="meta-val">${d.ref_id || '-'}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">ID Transaksi</span>
                    <span class="meta-val">${d.digiflazz_trx_id || d.ref_id || '-'}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Tanggal & Waktu</span>
                    <span class="meta-val">${dateStr}</span>
                </div>
            </div>

            <!-- Info rows -->
            <table class="info-table">
                <tbody>
                    <tr class="info-row">
                        <td class="info-label">Produk</td>
                        <td class="info-value">${d.product_name || '-'}</td>
                    </tr>
                    <tr class="info-row">
                        <td class="info-label">Nomor / ID</td>
                        <td class="info-value">${d.customer_no || '-'}</td>
                    </tr>
                    ${accountRowHtml}
                    ${showCustomerName ? `<tr class="info-row"><td class="info-label">Nama</td><td class="info-value">${d.customer_name}</td></tr>` : ''}
                    ${extraRowsHtml}
                </tbody>
            </table>

            <!-- SN / Token -->
            ${snBoxHtml}

            <hr class="divider">

            <!-- Total -->
            <div class="total-section">
                <span class="total-label">Total Bayar</span>
                <span class="total-amount">Rp ${price}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="footer-tagline">Terima kasih telah bertransaksi</div>
            <div class="footer-sub">Struk ini merupakan bukti transaksi yang sah</div>
            <div class="validity-note">✦ Dokumen resmi AlfarezMart · Harap simpan sebagai bukti ✦</div>
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">🖨️ &nbsp;Cetak Struk Sekarang</button>
</div>
</body></html>`;
    }

    function executePreviewBrowser() {
        if (!activeTrxData) return;

        const d = { ...activeTrxData };
        const customPriceInput = document.getElementById('custom-print-price');
        if (customPriceInput && customPriceInput.value) {
            d.sell_price = parseInt(customPriceInput.value) || d.sell_price;
        }

        const type  = detectProductType(d);
        const theme = getProductTheme(type);
        const snData = parseSN(d, type);

        const html = buildPrintHTML(d, type, theme, snData);
        const w = window.open('', '_blank', 'width=400,height=760');
        if (!w) { triggerToast('⚠️ Pop-up diblokir browser. Izinkan pop-up untuk halaman ini.', 'warning'); return; }
        w.document.write(html);
        w.document.close();
    }

    /**
     * Preview receipt in modal — also redesigned per product type.
     */
    function getReceiptPreviewContent(d, sellPrice) {
        const type   = detectProductType(d);
        const theme  = getProductTheme(type);
        const snData = parseSN(d, type);
        const price  = parseInt(sellPrice || d.sell_price || 0).toLocaleString('id-ID');
        const BASE   = '<?= BASE_URL ?>';
        const logoSrc = BASE + 'public/images/mobile_icon.png';

        // Watermark: 9 tiles
        let wmHtml = '';
        for (let i = 0; i < 9; i++) {
            wmHtml += `<img src="${logoSrc}" style="width:48px;height:48px;object-fit:contain;opacity:0.042;transform:rotate(-18deg);filter:grayscale(100%);display:block;" alt="">`;
        }

        // Extra rows from SN
        let extraRowsHtml = snData.extraRows.map(r => `
            <div style="display:flex;justify-content:space-between;padding:5px 0;border-top:1px dashed #eee;">
                <span style="color:#888;font-size:11px;font-weight:500;">${r.label}</span>
                <span style="color:#111;font-size:11.5px;font-weight:700;text-align:right;">${r.value}</span>
            </div>
        `).join('');

        // Account name row
        let accountRowHtml = '';
        if (snData.accountName) {
            accountRowHtml = `
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-top:1px dashed #eee;">
                    <span style="color:#888;font-size:11px;font-weight:500;">Nama Akun</span>
                    <span style="color:${theme.accentDark};font-size:11.5px;font-weight:700;text-align:right;">${snData.accountName}</span>
                </div>
            `;
        }

        // SN box
        let snBoxHtml = '';
        if (snData.hasSN && snData.snValue) {
            const snFs = snData.snValue.length > 20 ? '11px' : (snData.snValue.length > 14 ? '14px' : '17px');
            snBoxHtml = `
                <div style="background:${theme.accentLight};border:1.5px solid ${theme.accent}33;border-radius:10px;padding:11px;text-align:center;margin:10px 0;">
                    <div style="font-size:8.5px;font-weight:800;color:${theme.accentDark};text-transform:uppercase;letter-spacing:1.2px;margin-bottom:5px;">${snData.snTitle}</div>
                    <div id="preview-sn-value" style="font-size:${snFs};font-weight:700;color:#111;word-break:break-all;font-family:'JetBrains Mono',monospace;line-height:1.4;">${snData.snValue}</div>
                </div>
            `;
        }

        const ewallets = ['dana','gopay','shopee','ovo','linkaja'];
        const showCustomerName = d.customer_name && !ewallets.includes(type) && type !== 'pln';

        return `
            <div style="position:relative;background:#fff;border-radius:14px;overflow:hidden;font-family:'Inter',sans-serif;">
                <!-- Watermark -->
                <div style="position:absolute;inset:0;display:grid;grid-template-columns:repeat(3,1fr);align-items:center;justify-items:center;pointer-events:none;z-index:0;padding:16px;gap:0;">
                    ${wmHtml}
                </div>

                <!-- Header -->
                <div style="background:${theme.gradient};padding:18px 16px 22px;text-align:center;position:relative;z-index:2;">
                    <div style="width:54px;height:54px;background:rgba(255,255,255,0.95);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;box-shadow:0 4px 10px rgba(0,0,0,0.18);overflow:hidden;">
                        <img src="${logoSrc}" style="width:44px;height:44px;object-fit:contain;" alt="Logo" crossorigin="anonymous">
                    </div>
                    <div style="font-size:15px;font-weight:900;color:#fff;letter-spacing:0.8px;text-transform:uppercase;">AlfarezMart</div>
                    <div style="font-size:9.5px;color:rgba(255,255,255,0.8);margin-top:2px;font-weight:500;">Pusat Pembayaran Produk Digital</div>
                    <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.35);border-radius:20px;padding:4px 12px;font-size:10px;font-weight:700;color:#fff;margin-top:8px;">${theme.icon} ${theme.label}</div>
                </div>

                <!-- Jagged edge -->
                <div style="height:14px;background:#fff;position:relative;z-index:2;">
                    <div style="position:absolute;top:-1px;left:0;right:0;height:14px;background:radial-gradient(circle at 50% 0%,#fff 10px,transparent 11px);background-size:20px 14px;background-repeat:repeat-x;"></div>
                </div>

                <!-- Body -->
                <div style="padding:4px 16px 16px;position:relative;z-index:2;">
                    <!-- Success badge -->
                    <div style="display:flex;align-items:center;justify-content:center;gap:7px;background:${theme.accentLight};border:1.5px solid ${theme.accent};border-radius:9px;padding:7px;margin-bottom:12px;color:${theme.accentDark};font-size:10.5px;font-weight:800;letter-spacing:0.8px;text-transform:uppercase;">
                        <span style="width:7px;height:7px;background:${theme.accent};border-radius:50%;flex-shrink:0;"></span>
                        Transaksi Berhasil
                        <span style="width:7px;height:7px;background:${theme.accent};border-radius:50%;flex-shrink:0;"></span>
                    </div>

                    <!-- Meta -->
                    <div style="background:#f8f9fa;border-radius:9px;padding:9px 11px;margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:9px;padding:2px 0;">
                            <span style="color:#888;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;">No. Referensi</span>
                            <span style="color:#222;font-weight:700;font-family:monospace;font-size:8.5px;text-align:right;">${d.ref_id || '-'}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:9px;padding:2px 0;border-top:1px solid #eee;">
                            <span style="color:#888;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;">ID Transaksi</span>
                            <span style="color:#222;font-weight:700;font-family:monospace;font-size:8.5px;text-align:right;">${d.digiflazz_trx_id || d.ref_id || '-'}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:9px;padding:2px 0;border-top:1px solid #eee;">
                            <span style="color:#888;font-weight:600;text-transform:uppercase;letter-spacing:0.3px;">Tanggal</span>
                            <span style="color:#222;font-weight:700;font-family:monospace;font-size:8.5px;text-align:right;">${d.created_at || '-'}</span>
                        </div>
                    </div>

                    <!-- Info rows -->
                    <div style="display:flex;justify-content:space-between;padding:5px 0;">
                        <span style="color:#888;font-size:11px;font-weight:500;">Produk</span>
                        <span style="color:#111;font-size:11.5px;font-weight:700;text-align:right;max-width:58%;">${d.product_name || '-'}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:5px 0;border-top:1px dashed #eee;">
                        <span style="color:#888;font-size:11px;font-weight:500;">Nomor / ID</span>
                        <span style="color:#111;font-size:11.5px;font-weight:700;text-align:right;">${d.customer_no || '-'}</span>
                    </div>
                    ${accountRowHtml}
                    ${showCustomerName ? `<div style="display:flex;justify-content:space-between;padding:5px 0;border-top:1px dashed #eee;"><span style="color:#888;font-size:11px;font-weight:500;">Nama</span><span style="color:#111;font-size:11.5px;font-weight:700;text-align:right;">${d.customer_name}</span></div>` : ''}
                    ${extraRowsHtml}

                    <!-- SN Box -->
                    ${snBoxHtml}

                    <div style="border-top:1.5px dashed #d4d8dd;margin:12px 0;"></div>

                    <!-- Total -->
                    <div style="display:flex;justify-content:space-between;align-items:center;background:${theme.gradient};border-radius:11px;padding:12px 14px;">
                        <span style="font-size:10.5px;font-weight:700;color:rgba(255,255,255,0.9);text-transform:uppercase;letter-spacing:0.5px;">Total Bayar</span>
                        <span id="preview-total-val" style="font-size:17px;font-weight:900;color:#fff;letter-spacing:-0.5px;">Rp ${price}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div style="text-align:center;padding:12px 16px 18px;border-top:1.5px dashed #e0e0e0;position:relative;z-index:2;">
                    <div style="font-size:10.5px;font-weight:700;color:#333;margin-bottom:2px;">Terima kasih telah bertransaksi</div>
                    <div style="font-size:9px;color:#999;font-style:italic;">Struk ini merupakan bukti transaksi yang sah</div>
                    <div style="font-size:8.5px;color:${theme.accentDark};background:${theme.accentLight};border-radius:6px;padding:4px 10px;margin-top:8px;font-weight:600;">✦ Dokumen resmi AlfarezMart · Harap simpan sebagai bukti ✦</div>
                </div>
            </div>
        `;
    }
</script>
