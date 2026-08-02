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
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:10px;margin-bottom:20px;">
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
        <div class="stat-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:center;padding:12px;border-radius:12px;">
            <div class="stat-icon cyan" style="width:28px;height:28px;font-size:0.9rem;margin-bottom:8px;background:rgba(6,182,212,0.12);color:#06b6d4;"><i class="bi bi-stopwatch-fill"></i></div>
            <div class="stat-value" style="font-size:0.95rem;font-weight:700;color:var(--text-primary);white-space:nowrap;">
                <?php
                $avgSpdVal = round((float)($stats['avg_speed'] ?? 0));
                if ($avgSpdVal <= 59) {
                    echo number_format($avgSpdVal, 0, ',', '.') . ' <span style="font-size:11px;font-weight:600;">dtk</span>';
                } else {
                    $mVal = floor($avgSpdVal / 60);
                    $dVal = $avgSpdVal % 60;
                    echo "{$mVal}m, {$dVal}d";
                }
                ?>
            </div>
            <div class="stat-label" style="font-size:9px;margin-top:2px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Kecepatan Rata-Rata</div>
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
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:90px;">Kecepatan</th>
                        <th style="padding:12px 8px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:180px;">Produk / Pelanggan</th>
                        <th style="padding:12px 8px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:90px;">Agen</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:95px;">Modal</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:105px;">Jual</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:110px;">Saldo Sebelum</th>
                        <th style="padding:12px 8px;text-align:right;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:110px;">Saldo Sesudah</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:140px;">SN / Token</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:100px;">Seller</th>
                        <th style="padding:12px 8px;text-align:center;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);min-width:90px;">Status</th>
                    </tr>
                </thead>
                <tbody id="history-tbody">
                    <tr>
                        <td colspan="12" style="text-align:center;padding:40px;color:var(--text-muted);">
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
        document.getElementById('history-tbody').innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:var(--text-muted);"><span class="spinner-border spinner-border-sm me-2" style="color:var(--primary);"></span>Memuat...</td></tr>`;
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
                tbody.innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:var(--text-muted);">Tidak ada transaksi ${status === 'all' ? '' : status}.</td></tr>`;
                document.getElementById('pagination-info').innerText = 'Menampilkan 0 dari 0 data';
                document.getElementById('pagination-controls').innerHTML = '';
            }
        } catch (e) {
            console.error('Failed to load history', e);
            document.getElementById('history-tbody').innerHTML = `<tr><td colspan="12" style="text-align:center;padding:40px;color:var(--danger);">Gagal memuat data transaksi.</td></tr>`;
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

            let balBeforeStr = '-';
            if (trx.balance_before !== null && trx.balance_before !== undefined) {
                balBeforeStr = `Rp ${parseInt(trx.balance_before).toLocaleString('id-ID')}`;
            }
            let balAfterStr = '-';
            if (trx.balance_after !== null && trx.balance_after !== undefined) {
                balAfterStr = `Rp ${parseInt(trx.balance_after).toLocaleString('id-ID')}`;
            }

            let speedBadge = '<span style="color:var(--text-muted);font-size:10px;">-</span>';
            if (trx.duration_seconds !== null && trx.duration_seconds !== undefined) {
                let speedVal = Math.round(parseFloat(trx.duration_seconds));
                if (speedVal > 900) {
                    speedBadge = `<span style="background:rgba(107,114,128,0.12);color:var(--text-secondary);font-size:9.5px;padding:3px 6px;border-radius:4px;font-weight:600;white-space:nowrap;" title="Status diperbarui melalui cek status berkala (>15m)"><i class="bi bi-clock-history me-1"></i>>15m (Sync)</span>`;
                } else {
                    let speedColor = speedVal <= 5 ? 'background:rgba(16,185,129,0.12);color:#10b981;' : (speedVal <= 20 ? 'background:rgba(59,130,246,0.12);color:#3b82f6;' : 'background:rgba(245,158,11,0.12);color:#f59e0b;');
                    let speedText = speedVal <= 59 ? `${speedVal} dtk` : `${Math.floor(speedVal / 60)}m, ${speedVal % 60}d`;
                    speedBadge = `<span style="${speedColor}font-size:10px;padding:3px 7px;border-radius:4px;font-weight:700;white-space:nowrap;"><i class="bi bi-stopwatch me-1"></i>${speedText}</span>`;
                }
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
                    <td style="padding:12px 8px;text-align:center;white-space:nowrap;">
                        ${speedBadge}
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
                    <td style="padding:12px 8px;text-align:right;">
                        <div style="font-weight:600;color:var(--text-secondary);font-size:11px;">${balBeforeStr}</div>
                    </td>
                    <td style="padding:12px 8px;text-align:right;">
                        <div style="font-weight:700;color:var(--info);font-size:11px;">${balAfterStr}</div>
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
</script>
<script src="<?= BASE_URL ?>public/js/ppob_receipt.js"></script>
<script>
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
</script>
