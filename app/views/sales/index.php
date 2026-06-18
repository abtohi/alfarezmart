<!-- Sales Index View — with Filter, Date Grouping & Customer Analytics -->
<div class="page-section" style="padding-bottom:100px;">

    <!-- ===== PAGE HEADER ===== -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="font-size:var(--font-size-lg); font-weight:800; letter-spacing:-0.5px;">Riwayat Penjualan</h2>
        <div style="display:flex;gap:8px;align-items:center;">
            <button type="button" id="btnSalesPrinter" onclick="toggleSalesPrinter()" title="Hubungkan Printer Thermal"
                    style="background:var(--surface-1);color:var(--text-muted);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:8px 12px;cursor:pointer;font-size:1rem;display:flex;align-items:center;gap:6px;font-size:var(--font-size-xs);transition:all 0.3s;">
                <i class="bi bi-printer" id="salesPrinterIcon"></i>
                <span id="salesPrinterLabel" style="display:none;"></span>
            </button>
            <a href="<?= BASE_URL ?>sales/pos" class="btn-primary-custom" style="padding:8px 16px;font-size:var(--font-size-xs);text-decoration:none;color:white;">
                <i class="bi bi-cart"></i> Kasir POS
            </a>
        </div>
    </div>

    <!-- ===== SUMMARY CARD ===== -->
    <div id="summaryCard" style="background:var(--gradient-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:16px;margin-bottom:16px;display:none;">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;text-align:center;">
            <div>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:4px;">Transaksi</div>
                <div id="summaryTx" style="font-size:var(--font-size-md);font-weight:800;color:var(--text-primary);">—</div>
            </div>
            <div style="border-left:1px solid var(--border-color);">
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:4px;">Total Omzet</div>
                <div id="summaryOmzet" style="font-size:var(--font-size-sm);font-weight:800;color:var(--primary);">—</div>
            </div>
            <div style="border-left:1px solid var(--border-color);">
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:4px;">Total Profit</div>
                <div id="summaryProfit" style="font-size:var(--font-size-sm);font-weight:800;color:var(--success);">—</div>
            </div>
            <div style="border-left:1px solid var(--border-color);">
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:4px;">Avg Markup</div>
                <div id="summaryMarkup" style="font-size:var(--font-size-sm);font-weight:800;color:var(--warning);">—</div>
            </div>
        </div>
    </div>

    <!-- ===== FILTER PANEL ===== -->
    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:14px;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
            <i class="bi bi-funnel-fill" style="color:var(--primary);font-size:0.9rem;"></i>
            <span style="font-size:var(--font-size-xs);font-weight:700;color:var(--text-primary);">Filter Penjualan</span>
        </div>

        <!-- Date Range -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
            <div>
                <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;font-weight:600;">Dari Tanggal</label>
                <input type="date" id="filterDateFrom" class="form-control-dark" style="font-size:var(--font-size-xs);padding:8px 10px;">
            </div>
            <div>
                <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;font-weight:600;">Sampai Tanggal</label>
                <input type="date" id="filterDateTo" class="form-control-dark" style="font-size:var(--font-size-xs);padding:8px 10px;">
            </div>
        </div>

        <!-- Customer Filter -->
        <div style="margin-bottom:12px;">
            <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;font-weight:600;">Pelanggan</label>
            <div style="position:relative;">
                <i class="bi bi-person" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.85rem;pointer-events:none;"></i>
                <select id="filterCustomer" class="form-control-dark" style="font-size:var(--font-size-xs);padding:8px 10px 8px 32px;width:100%;-webkit-appearance:none;">
                    <option value="">Semua Pelanggan</option>
                    <option value="none">Pelanggan Umum (Tanpa Nama)</option>
                </select>
            </div>
        </div>

        <!-- Quick Date Buttons -->
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;">
            <button onclick="setQuickDate('today')" class="filter-quick-btn" data-key="today" style="padding:5px 10px;border-radius:30px;border:1px solid var(--border-color);background:var(--surface-2);font-size:10px;font-weight:600;color:var(--text-muted);cursor:pointer;transition:all 0.2s;">Hari Ini</button>
            <button onclick="setQuickDate('week')" class="filter-quick-btn" data-key="week" style="padding:5px 10px;border-radius:30px;border:1px solid var(--border-color);background:var(--surface-2);font-size:10px;font-weight:600;color:var(--text-muted);cursor:pointer;transition:all 0.2s;">7 Hari</button>
            <button onclick="setQuickDate('month')" class="filter-quick-btn" data-key="month" style="padding:5px 10px;border-radius:30px;border:1px solid var(--border-color);background:var(--surface-2);font-size:10px;font-weight:600;color:var(--text-muted);cursor:pointer;transition:all 0.2s;">Bulan Ini</button>
            <button onclick="setQuickDate('last30')" class="filter-quick-btn" data-key="last30" style="padding:5px 10px;border-radius:30px;border:1px solid var(--border-color);background:var(--surface-2);font-size:10px;font-weight:600;color:var(--text-muted);cursor:pointer;transition:all 0.2s;">30 Hari</button>
        </div>

        <div style="display:flex;gap:8px;">
            <button onclick="applyFilter()" class="btn-primary-custom" style="flex:1;padding:9px;font-size:var(--font-size-xs);display:flex;align-items:center;justify-content:center;gap:6px;cursor:pointer;">
                <i class="bi bi-search"></i> Tampilkan
            </button>
            <button onclick="resetFilter()" style="padding:9px 16px;border-radius:var(--radius-md);border:1px solid var(--border-color);background:var(--surface-2);color:var(--text-muted);font-size:var(--font-size-xs);cursor:pointer;font-weight:600;transition:all 0.2s;" onmouseover="this.style.background='var(--surface-1)'" onmouseout="this.style.background='var(--surface-2)'">
                <i class="bi bi-x-circle"></i> Reset
            </button>
        </div>
    </div>

    <!-- ===== TOP CUSTOMER RANKING (collapsible) ===== -->
    <div id="customerRankingSection" style="display:none;margin-bottom:16px;">
        <div onclick="toggleRanking()" style="display:flex;justify-content:space-between;align-items:center;background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:12px 16px;cursor:pointer;transition:all 0.2s;" id="rankingToggleBtn">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;background:var(--warning-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-trophy-fill" style="color:var(--warning);font-size:0.9rem;"></i>
                </div>
                <div>
                    <div style="font-size:var(--font-size-xs);font-weight:700;color:var(--text-primary);">Top Pelanggan Paling Profitable</div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;" id="rankingSubtitle">Klik untuk lihat peringkat</div>
                </div>
            </div>
            <i class="bi bi-chevron-down" id="rankingChevron" style="color:var(--text-muted);transition:transform 0.3s;"></i>
        </div>
        <div id="rankingBody" style="display:none;border:1px solid var(--border-color);border-top:none;border-radius:0 0 var(--radius-lg) var(--radius-lg);background:var(--surface-1);overflow:hidden;">
            <div id="rankingList" style="padding:8px 0;"></div>
        </div>
    </div>

    <!-- ===== SALES LIST (grouped by date) ===== -->
    <div id="salesListContainer">
        <div class="empty-state">
            <i class="bi bi-funnel"></i>
            <h3>Pilih Filter</h3>
            <p>Gunakan filter di atas untuk menampilkan riwayat penjualan</p>
        </div>
    </div>
</div>

<input type="hidden" id="csrfToken" value="<?= $csrfToken ?? '' ?>">

<!-- Hidden receipt canvas for PNG share generation -->
<div id="receiptShareCanvas" style="position:fixed;top:-9999px;left:-9999px;z-index:-1;width:320px;background:#fff;font-family:'Inter',sans-serif;color:#111;padding:0;"></div>

<!-- Bulk Action Bar (fixed bottom) -->
<div id="bulkActionBar" style="display:none;position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:480px;z-index:110;padding:0 12px 12px;">
    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 -4px 24px rgba(0,0,0,0.25);backdrop-filter:blur(12px);">
        <div style="display:flex;align-items:center;gap:10px;">
            <button type="button" onclick="exitSelectionMode()" style="background:none;border:none;color:var(--text-primary);cursor:pointer;padding:4px;font-size:1.2rem;"><i class="bi bi-x-lg"></i></button>
            <span id="bulkSelectedCount" style="font-weight:700;font-size:var(--font-size-sm);color:var(--text-primary);">0 dipilih</span>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" onclick="selectAllSales()" style="padding:8px 14px;border-radius:var(--radius-md);border:1px solid var(--border-color);background:var(--surface-2);color:var(--text-primary);cursor:pointer;font-size:var(--font-size-xs);display:flex;align-items:center;gap:4px;">
                <i class="bi bi-check-all"></i> Semua
            </button>
            <button type="button" id="btnBulkDelete" onclick="bulkDeleteSelected()" style="padding:8px 14px;border-radius:var(--radius-md);border:none;background:var(--danger);color:white;cursor:pointer;font-size:var(--font-size-xs);display:flex;align-items:center;gap:4px;font-weight:600;">
                <i class="bi bi-trash"></i> Hapus
            </button>
        </div>
    </div>
</div>

<script>
// ===== State =====
let selectionMode = false;
let selectedIds = new Set();
let longPressTimer = null;
const LONG_PRESS_MS = 500;
let _rankingOpen = false;
let _currentData = null;

// ===== Utils =====
function rupiah(n) {
    return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
}
function fmtDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}
function fmtDateTime(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const hh = String(d.getHours()).padStart(2,'0');
    const mm = String(d.getMinutes()).padStart(2,'0');
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}, ${hh}:${mm}`;
}
function isoDate(d) {
    return d.toISOString().split('T')[0];
}

// ===== Load Customers into Filter Select =====
async function loadCustomerOptions() {
    try {
        const res = await api(`${BASE_URL}api/customers`);
        if (res.success && res.data) {
            const sel = document.getElementById('filterCustomer');
            res.data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name + (c.phone ? ` (${c.phone})` : '');
                sel.appendChild(opt);
            });
        }
    } catch(e) {}
}

// ===== Quick Date Buttons =====
function setQuickDate(key) {
    const today = new Date();
    let from, to;
    to = isoDate(today);

    if (key === 'today') {
        from = to;
    } else if (key === 'week') {
        const d = new Date(today); d.setDate(d.getDate() - 6);
        from = isoDate(d);
    } else if (key === 'month') {
        from = isoDate(new Date(today.getFullYear(), today.getMonth(), 1));
    } else if (key === 'last30') {
        const d = new Date(today); d.setDate(d.getDate() - 29);
        from = isoDate(d);
    }

    document.getElementById('filterDateFrom').value = from;
    document.getElementById('filterDateTo').value = to;

    document.querySelectorAll('.filter-quick-btn').forEach(btn => {
        const isActive = btn.dataset.key === key;
        btn.style.background = isActive ? 'var(--primary)' : 'var(--surface-2)';
        btn.style.color = isActive ? 'white' : 'var(--text-muted)';
        btn.style.borderColor = isActive ? 'var(--primary)' : 'var(--border-color)';
    });

    applyFilter();
}

function resetFilter() {
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterCustomer').value = '';
    document.querySelectorAll('.filter-quick-btn').forEach(btn => {
        btn.style.background = 'var(--surface-2)';
        btn.style.color = 'var(--text-muted)';
        btn.style.borderColor = 'var(--border-color)';
    });
    document.getElementById('summaryCard').style.display = 'none';
    document.getElementById('customerRankingSection').style.display = 'none';
    document.getElementById('salesListContainer').innerHTML = `
        <div class="empty-state">
            <i class="bi bi-funnel"></i>
            <h3>Pilih Filter</h3>
            <p>Gunakan filter di atas untuk menampilkan riwayat penjualan</p>
        </div>`;
}

// ===== Main: Apply Filter & Fetch =====
async function applyFilter() {
    const dateFrom    = document.getElementById('filterDateFrom').value;
    const dateTo      = document.getElementById('filterDateTo').value;
    const customerId  = document.getElementById('filterCustomer').value;

    const container = document.getElementById('salesListContainer');
    container.innerHTML = `<div class="elegant-loader" style="margin:30px auto;"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>`;
    document.getElementById('summaryCard').style.display = 'none';
    document.getElementById('customerRankingSection').style.display = 'none';

    try {
        const params = new URLSearchParams();
        if (dateFrom)    params.set('date_from', dateFrom);
        if (dateTo)      params.set('date_to', dateTo);
        if (customerId)  params.set('customer_id', customerId);

        const res = await api(`${BASE_URL}api/sales/analytics?${params.toString()}`);
        if (!res.success) throw new Error(res.error || 'Gagal memuat data');

        _currentData = res.data;
        renderSummary(res.data.summary);
        renderCustomerRanking(res.data.customer_ranking);
        renderGroupedSales(res.data.transactions);
    } catch(e) {
        container.innerHTML = `<div class="empty-state"><i class="bi bi-exclamation-triangle"></i><h3>Error</h3><p>${e.message}</p></div>`;
    }
}

// ===== Render Summary =====
function renderSummary(summary) {
    document.getElementById('summaryTx').textContent = summary.total_transactions.toLocaleString('id-ID') + ' transaksi';
    document.getElementById('summaryOmzet').textContent = rupiah(summary.total_omzet);
    document.getElementById('summaryProfit').textContent = rupiah(summary.total_profit);
    
    // Hitung rata-rata markup
    const totalOmzet = summary.total_omzet || 0;
    const totalProfit = summary.total_profit || 0;
    const modal = totalOmzet - totalProfit;
    const markup = modal > 0 ? (totalProfit / modal * 100) : (totalProfit > 0 ? 100 : 0);
    document.getElementById('summaryMarkup').textContent = '+' + markup.toFixed(1).replace('.', ',') + '%';
    
    document.getElementById('summaryCard').style.display = 'block';
}

// ===== Render Customer Ranking =====
function renderCustomerRanking(ranking) {
    const section = document.getElementById('customerRankingSection');
    const list    = document.getElementById('rankingList');
    const subtitle = document.getElementById('rankingSubtitle');

    if (!ranking || ranking.length === 0) {
        section.style.display = 'none';
        return;
    }

    subtitle.textContent = `${ranking.length} pelanggan ditemukan`;

    const medals = ['🥇','🥈','🥉'];
    const colors  = ['var(--warning)','#adb5bd','#cd7f32'];

    list.innerHTML = ranking.map((c, i) => {
        const markup = c.total_omzet > 0 ? ((c.total_profit / c.total_omzet) * 100).toFixed(1) : 0;
        return `
        <div style="display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid var(--border-color);transition:background 0.15s;" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
            <div style="width:28px;text-align:center;font-size:${i < 3 ? '1.2rem' : '0.75rem'};font-weight:700;color:${colors[i] || 'var(--text-muted)'};">
                ${i < 3 ? medals[i] : '#' + (i+1)}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:var(--font-size-xs);font-weight:700;color:var(--text-primary);text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">${c.name}</div>
                <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">${c.transaction_count} transaksi · ${c.phone || 'Tanpa HP'}</div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:var(--font-size-xs);font-weight:800;color:var(--success);">${rupiah(c.total_profit)}</div>
                <div style="font-size:10px;color:var(--text-muted);">Omzet: ${rupiah(c.total_omzet)}</div>
                <div style="display:inline-block;background:var(--success-bg);color:var(--success);border-radius:4px;padding:1px 5px;font-size:9px;font-weight:700;">+${markup}%</div>
            </div>
        </div>`;
    }).join('');

    section.style.display = 'block';
}

function toggleRanking() {
    _rankingOpen = !_rankingOpen;
    document.getElementById('rankingBody').style.display = _rankingOpen ? 'block' : 'none';
    document.getElementById('rankingChevron').style.transform = _rankingOpen ? 'rotate(180deg)' : '';
}

// ===== Render Grouped Sales =====
function toggleDateGroup(dateKey) {
    const el = document.getElementById('date-group-' + dateKey);
    const chevron = document.querySelector('.date-chevron-' + dateKey);
    if (!el || !chevron) return;
    
    if (el.style.display === 'none') {
        el.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        el.style.display = 'none';
        chevron.style.transform = '';
    }
}

function renderGroupedSales(transactions) {
    const container = document.getElementById('salesListContainer');

    if (!transactions || transactions.length === 0) {
        container.innerHTML = `<div class="empty-state"><i class="bi bi-receipt"></i><h3>Tidak Ada Transaksi</h3><p>Tidak ada penjualan pada filter yang dipilih</p></div>`;
        return;
    }

    // Group by date
    const groups = {};
    transactions.forEach(t => {
        const dateKey = t.created_at ? t.created_at.split(' ')[0] : 'unknown';
        if (!groups[dateKey]) groups[dateKey] = [];
        groups[dateKey].push(t);
    });

    let html = '';
    const todayISO = isoDate(new Date());

    Object.keys(groups).sort().reverse().forEach(dateKey => {
        const dayTx = groups[dateKey];
        const dayOmzet  = dayTx.reduce((s, t) => s + parseFloat(t.total_amount || 0), 0);
        const dayProfit = dayTx.reduce((s, t) => s + parseFloat(t.total_profit || 0), 0);
        
        const isToday = dateKey === todayISO;
        const displayStyle = isToday ? 'block' : 'none';
        const chevronStyle = isToday ? 'transform:rotate(180deg);' : '';

        html += `
        <div style="margin-bottom:12px;">
            <!-- Date Group Header -->
            <div onclick="toggleDateGroup('${dateKey}')" style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);cursor:pointer;transition:background 0.2s;position:sticky;top:60px;z-index:5;backdrop-filter:blur(8px);">
                <div style="display:flex;align-items:center;gap:10px;">
                    <i class="bi bi-chevron-down date-chevron-${dateKey}" style="color:var(--text-muted);transition:transform 0.3s;${chevronStyle}"></i>
                    <div>
                        <div style="font-size:var(--font-size-xs);font-weight:800;color:var(--text-primary);">${fmtDate(dateKey)} ${isToday ? '<span class="badge-custom badge-primary" style="margin-left:6px;font-size:9px;">Hari Ini</span>' : ''}</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">${dayTx.length} transaksi</div>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:var(--font-size-xs);font-weight:700;color:var(--primary);">${rupiah(dayOmzet)}</div>
                    <div style="font-size:10px;color:var(--success);font-weight:600;">Profit: ${rupiah(dayProfit)}</div>
                </div>
            </div>
            <!-- Day Transactions -->
            <div id="date-group-${dateKey}" style="display:${displayStyle};padding-top:8px;">
                ${dayTx.map(t => renderSaleCard(t)).join('')}
            </div>
        </div>`;
    });

    container.innerHTML = html;
    initSelectionMode();
}

function renderSaleCard(t) {
    const profit = parseFloat(t.total_profit || 0);
    const modal  = parseFloat(t.total_amount || 0) - profit;
    const markup = modal > 0 ? (profit / modal * 100) : (profit > 0 ? 100 : 0);
    const custName = t.customer_name || 'Pelanggan Umum';

    return `
    <div class="product-card sale-card" data-sale-id="${t.id}" style="align-items:flex-start;cursor:pointer;position:relative;transition:all 0.2s;margin-bottom:8px;">
        <div class="sale-check" style="display:none;position:absolute;left:8px;top:50%;transform:translateY(-50%);z-index:2;">
            <div style="width:24px;height:24px;border-radius:var(--radius-full);border:2px solid var(--primary);display:flex;align-items:center;justify-content:center;background:var(--surface-1);transition:all 0.2s;">
                <i class="bi bi-check-lg" style="font-size:14px;color:var(--primary);display:none;"></i>
            </div>
        </div>
        <div class="product-icon" style="background:var(--primary-bg);color:var(--primary);">
            <i class="bi bi-receipt"></i>
        </div>
        <div class="product-info">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                <span style="font-weight:700;font-size:var(--font-size-sm);">${t.invoice_number}</span>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:var(--font-size-xs);color:var(--text-muted);">${fmtDateTime(t.created_at)}</span>
                    <button type="button"
                        onclick="event.stopPropagation(); shareInvoice(${t.id}, '${t.invoice_number}')"
                        title="Bagikan struk"
                        style="background:none;border:none;padding:2px 4px;cursor:pointer;color:var(--text-muted);font-size:1rem;border-radius:var(--radius-sm);transition:color 0.2s,background 0.2s;line-height:1;display:flex;align-items:center;"
                        onmouseover="this.style.color='var(--primary)';this.style.background='var(--primary-bg)'"
                        onmouseout="this.style.color='var(--text-muted)';this.style.background='none'"
                        data-share-btn="${t.id}">
                        <i class="bi bi-share"></i>
                    </button>
                </div>
            </div>
            <div style="font-size:var(--font-size-xs);color:var(--text-secondary);margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;">
                <span><i class="bi bi-person"></i> ${custName}</span>
                <span class="badge-custom badge-${t.sale_mode === 'retail' ? 'info' : 'warning'}">${t.sale_mode === 'retail' ? 'Retail' : 'Grosir'}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:8px;">
                <span style="font-size:var(--font-size-xs);color:var(--text-muted);">${t.total_items || 0} item</span>
                <span style="font-weight:700;color:var(--primary);font-size:var(--font-size-base);">${rupiah(t.total_amount)}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px dashed var(--border-color);padding-top:8px;">
                <div style="font-size:11px;color:var(--text-secondary);">
                    Modal: <span style="font-weight:600;color:var(--text-primary);">${rupiah(modal)}</span>
                </div>
                <div style="font-size:11px;color:var(--text-secondary);">
                    Profit: <span style="font-weight:600;color:var(--success);">${rupiah(profit)}</span>
                    <span style="background:var(--success-bg);color:var(--success);padding:2px 4px;border-radius:4px;font-size:10px;margin-left:2px;">+${markup.toFixed(1).replace('.',',')}%</span>
                </div>
            </div>
        </div>
    </div>`;
}

// ===== Selection Mode =====
function initSelectionMode() {
    document.querySelectorAll('.sale-card').forEach(card => {
        const saleId = card.dataset.saleId;

        card.addEventListener('touchstart', (e) => {
            longPressTimer = setTimeout(() => {
                e.preventDefault();
                if (!selectionMode) enterSelectionMode();
                toggleSelect(saleId);
                if (navigator.vibrate) navigator.vibrate(30);
            }, LONG_PRESS_MS);
        }, { passive: false });

        card.addEventListener('touchend',  () => clearTimeout(longPressTimer));
        card.addEventListener('touchmove', () => clearTimeout(longPressTimer));

        card.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            if (!selectionMode) enterSelectionMode();
            toggleSelect(saleId);
        });

        card.addEventListener('click', (e) => {
            if (selectionMode) {
                e.preventDefault();
                e.stopPropagation();
                toggleSelect(saleId);
            } else {
                window.location.href = `${BASE_URL}sales/${saleId}`;
            }
        });
    });
}

function enterSelectionMode() {
    selectionMode = true;
    document.querySelectorAll('.sale-check').forEach(el => el.style.display = 'flex');
    document.querySelectorAll('.sale-card .product-icon').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.sale-card').forEach(el => el.style.paddingLeft = '44px');
    document.getElementById('bulkActionBar').style.display = 'block';
}

function exitSelectionMode() {
    selectionMode = false;
    selectedIds.clear();
    document.querySelectorAll('.sale-check').forEach(el => {
        el.style.display = 'none';
        el.querySelector('div').style.background = 'var(--surface-1)';
        el.querySelector('i').style.display = 'none';
    });
    document.querySelectorAll('.sale-card .product-icon').forEach(el => el.style.display = '');
    document.querySelectorAll('.sale-card').forEach(el => {
        el.style.paddingLeft = '';
        el.style.background = '';
    });
    document.getElementById('bulkActionBar').style.display = 'none';
}

function toggleSelect(id) {
    const card = document.querySelector(`.sale-card[data-sale-id="${id}"]`);
    if (!card) return;
    const check = card.querySelector('.sale-check');
    const checkDiv = check.querySelector('div');
    const checkIcon = check.querySelector('i');

    if (selectedIds.has(id)) {
        selectedIds.delete(id);
        checkDiv.style.background = 'var(--surface-1)';
        checkIcon.style.display = 'none';
        card.style.background = '';
    } else {
        selectedIds.add(id);
        checkDiv.style.background = 'var(--primary)';
        checkIcon.style.display = 'block';
        checkIcon.style.color = 'white';
        card.style.background = 'var(--primary-bg)';
    }
    document.getElementById('bulkSelectedCount').textContent = `${selectedIds.size} dipilih`;
    if (selectedIds.size === 0) exitSelectionMode();
}

function selectAllSales() {
    document.querySelectorAll('.sale-card').forEach(card => {
        const id = card.dataset.saleId;
        if (!selectedIds.has(id)) toggleSelect(id);
    });
}

async function bulkDeleteSelected() {
    if (selectedIds.size === 0) return;
    const count = selectedIds.size;

    AppModal.show({
        title: 'Hapus Transaksi',
        subtitle: `${count} transaksi dipilih`,
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `
            <div style="text-align:center;padding:12px 0;">
                <p style="font-size:var(--font-size-md);font-weight:600;color:var(--text-primary);margin-bottom:8px;">Yakin ingin menghapus ${count} transaksi?</p>
                <p style="font-size:var(--font-size-sm);color:var(--text-muted);margin-bottom:16px;">Stok produk yang terjual akan dikembalikan. Tindakan ini tidak bisa dibatalkan.</p>
            </div>`,
        submitText: 'Ya, Hapus',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const csrf = document.getElementById('csrfToken')?.value || '';
                const res = await fetch(`${BASE_URL}api/sales/bulk-delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                    body: JSON.stringify({ csrf_token: csrf, ids: Array.from(selectedIds).map(Number) })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`✅ ${result.deleted} transaksi berhasil dihapus`, 'success');
                    exitSelectionMode();
                    applyFilter();
                    return true;
                } else {
                    throw new Error(result.error || 'Gagal menghapus');
                }
            } catch (err) {
                showToast('Error: ' + err.message, 'error');
                return false;
            }
        }
    });
}

// ===== Printer =====
function updatePrinterUI() {
    const btn = document.getElementById('btnSalesPrinter');
    const icon = document.getElementById('salesPrinterIcon');
    const label = document.getElementById('salesPrinterLabel');
    const tp = (typeof thermalPrinter !== 'undefined') ? thermalPrinter : null;
    if (!btn || !tp) return;

    if (tp.isConnected()) {
        btn.style.background = 'var(--success-bg)'; btn.style.color = 'var(--success)'; btn.style.borderColor = 'var(--success)';
        icon.className = 'bi bi-printer-fill'; label.textContent = tp.device?.name || 'Terhubung'; label.style.display = 'inline';
        btn.title = 'Printer terhubung - Klik untuk putuskan';
    } else if (tp.hasSavedDevice()) {
        btn.style.background = 'var(--warning-bg)'; btn.style.color = 'var(--warning)'; btn.style.borderColor = 'var(--warning)';
        icon.className = 'bi bi-printer'; label.textContent = 'Tersimpan'; label.style.display = 'inline';
        btn.title = 'Printer tersimpan - Klik untuk hubungkan';
    } else {
        btn.style.background = 'var(--surface-1)'; btn.style.color = 'var(--text-muted)'; btn.style.borderColor = 'var(--border-color)';
        icon.className = 'bi bi-printer'; label.style.display = 'none';
        btn.title = 'Hubungkan Printer Thermal Bluetooth';
    }
}

async function toggleSalesPrinter() {
    const tp = (typeof thermalPrinter !== 'undefined') ? thermalPrinter : null;
    if (!tp) { showToast('Module printer tidak tersedia', 'error'); return; }
    if (tp.isIOS || !tp.hasBluetoothAPI) {
        showToast('Perangkat ini menggunakan cetak via Browser/AirPrint. Hubungkan printer saat checkout di POS.', 'info'); return;
    }
    if (tp.isConnected()) { tp.disconnect(); tp.clearLastDevice(); showToast('Printer diputuskan', 'info'); updatePrinterUI(); return; }

    const btn = document.getElementById('btnSalesPrinter');
    const prevHTML = btn.innerHTML;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i>'; btn.disabled = true;
    try {
        if (tp.device || tp.hasSavedDevice()) {
            const ok = await tp.tryAutoReconnect();
            if (ok) { showToast(`Printer terhubung: ${tp.device?.name || 'Bluetooth'}`, 'success'); updatePrinterUI(); btn.disabled = false; return; }
        }
        await tp.connect();
        showToast(`Printer terhubung: ${tp.device?.name || 'Bluetooth'}`, 'success');
    } catch(e) { showToast(e.message || 'Gagal menghubungkan printer', 'error'); }
    btn.innerHTML = prevHTML; btn.disabled = false; updatePrinterUI();
}

// ===== Init =====
document.addEventListener('DOMContentLoaded', () => {
    loadCustomerOptions();
    updatePrinterUI();

    // Default: tampilkan 30 hari terakhir
    setQuickDate('last30');

    const tp = (typeof thermalPrinter !== 'undefined') ? thermalPrinter : null;
    if (tp && !tp.isConnected() && tp.hasSavedDevice() && tp.hasBluetoothAPI && !tp.isIOS) {
        tp.tryAutoReconnect().then(ok => { if (ok) showToast(`Printer auto-connected: ${tp.device?.name}`, 'success'); updatePrinterUI(); }).catch(() => updatePrinterUI());
    }
});
</script>

<!-- html2canvas for PNG receipt generation -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
function formatRupiah(amount) { return 'Rp ' + parseInt(amount || 0).toLocaleString('id-ID'); }
function formatDateShort(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
}
function renderReceiptHTML(data) {
    const tx = data.transaction;
    const items = tx.items || [];
    const storeName = (typeof STORE_SETTINGS !== 'undefined' && STORE_SETTINGS?.store_name) ? STORE_SETTINGS.store_name : 'AlfarezMart';
    const itemRows = items.map(item => `
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:8px 0;border-bottom:1px solid #f0f0f0;">
            <div style="flex:1;margin-right:12px;">
                <div style="font-weight:600;font-size:12px;color:#111;line-height:1.3;">${item.invoice_name || item.full_name || 'Item'}</div>
                <div style="font-size:11px;color:#666;margin-top:2px;">${parseInt(item.quantity)} ${item.unit_name || 'pcs'} &times; ${formatRupiah(item.unit_price)}</div>
            </div>
            <div style="font-weight:700;font-size:12px;color:#111;white-space:nowrap;">${formatRupiah(item.total_price)}</div>
        </div>`).join('');
    return `
        <div style="width:320px;background:#ffffff;font-family:'Inter',Arial,sans-serif;color:#111;padding:0;overflow:hidden;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.12);">
            <div style="background:linear-gradient(135deg,#6c47ff 0%,#3b82f6 100%);padding:20px 20px 16px;text-align:center;">
                <div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:0.5px;">${storeName}</div>
                <div style="font-size:11px;color:rgba(255,255,255,0.8);margin-top:4px;">Struk Penjualan</div>
            </div>
            <div style="padding:14px 16px 10px;background:#f8f9fc;border-bottom:2px dashed #e0e0e0;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;"><span style="font-size:11px;color:#666;">No. Invoice</span><span style="font-weight:700;font-size:12px;color:#111;">${tx.invoice_number}</span></div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;"><span style="font-size:11px;color:#666;">Tanggal</span><span style="font-size:11px;color:#333;">${formatDateShort(tx.created_at)}</span></div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;"><span style="font-size:11px;color:#666;">Pelanggan</span><span style="font-size:11px;color:#333;">${tx.customer_name || 'Pelanggan Umum'}</span></div>
                <div style="display:flex;justify-content:space-between;align-items:center;"><span style="font-size:11px;color:#666;">Pembayaran</span><span style="font-size:11px;color:#333;">${tx.payment_method || 'Cash'}</span></div>
            </div>
            <div style="padding:10px 16px 0;">
                <div style="font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Item Pembelian</div>
                ${itemRows}
            </div>
            <div style="margin:12px 16px;background:linear-gradient(135deg,#6c47ff 0%,#3b82f6 100%);border-radius:8px;padding:12px 14px;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.9);">Total Pembayaran</span>
                <span style="font-size:16px;font-weight:800;color:#fff;">${formatRupiah(tx.total_amount)}</span>
            </div>
            <div style="text-align:center;padding:10px 16px 16px;">
                <div style="font-size:10px;color:#aaa;">Terima kasih telah berbelanja di ${storeName}</div>
                <div style="font-size:10px;color:#aaa;margin-top:2px;">⭐ Simpan struk ini sebagai bukti pembelian</div>
            </div>
        </div>`;
}
async function shareInvoice(saleId, invoiceNumber) {
    const btn = document.querySelector(`[data-share-btn="${saleId}"]`);
    if (btn) { btn.innerHTML = '<i class="bi bi-hourglass-split" style="font-size:0.85rem;"></i>'; btn.disabled = true; }
    try {
        const res = await fetch(`${BASE_URL}api/sales/invoice/${saleId}`);
        if (!res.ok) throw new Error('Gagal mengambil data invoice');
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Data tidak ditemukan');
        const canvasEl = document.getElementById('receiptShareCanvas');
        canvasEl.innerHTML = renderReceiptHTML(data);
        canvasEl.style.top = '-9999px'; canvasEl.style.left = '-9999px'; canvasEl.style.display = 'block';
        const receiptNode = canvasEl.firstElementChild;
        const canvas = await html2canvas(receiptNode, { scale: 2.5, useCORS: true, backgroundColor: '#ffffff', logging: false });
        canvasEl.innerHTML = '';
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
        const fileName = `struk-${invoiceNumber.replace(/[^a-zA-Z0-9\-]/g,'_')}.png`;
        if (navigator.canShare && navigator.canShare({ files: [new File([blob], fileName, { type: 'image/png' })] })) {
            await navigator.share({ title: `Struk ${invoiceNumber}`, text: `Struk pembelian ${invoiceNumber}`, files: [new File([blob], fileName, { type: 'image/png' })] });
        } else if (navigator.share) {
            await navigator.share({ title: `Struk ${invoiceNumber}`, text: `Struk pembelian ${invoiceNumber}`, url: `${BASE_URL}sales/${saleId}` });
        } else {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a'); a.href = url; a.download = fileName;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(url), 1000);
            showToast('Struk diunduh sebagai PNG', 'success');
        }
    } catch(err) {
        if (err.name !== 'AbortError') showToast('Gagal membagikan struk: ' + (err.message || 'Error tidak diketahui'), 'error');
    } finally {
        if (btn) { btn.innerHTML = '<i class="bi bi-share"></i>'; btn.disabled = false; }
    }
}
</script>
