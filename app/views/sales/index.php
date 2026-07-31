<!-- Sales Index View — with Filter, Date Grouping & Customer Analytics -->
<style>
/* ===== DESKTOP ELEGANT LAYOUT (min-width: 992px) ===== */
@media (min-width: 992px) {
    .sales-page-wrapper {
        max-width: 1400px;
        margin: 0 auto;
    }
    .sales-desktop-card {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }
    .sales-desktop-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }
    .sales-desktop-table th {
        background: var(--surface-2);
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .sales-desktop-table td {
        padding: 11px 16px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
        vertical-align: middle;
    }
    .sales-desktop-table tr.sale-row-desktop {
        transition: background 0.15s ease;
        cursor: pointer;
    }
    .sales-desktop-table tr.sale-row-desktop:hover {
        background: rgba(99, 102, 241, 0.08) !important;
    }
    .sales-desktop-date-hdr {
        background: var(--surface-2) !important;
        border-top: 2px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
    }
}
</style>
<div class="page-section sales-page-wrapper" style="padding-bottom:100px;">

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

        <!-- Customer Filter (Custom Searchbox) -->
        <div style="margin-bottom:12px;">
            <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;font-weight:600;">Pelanggan</label>
            <div style="position:relative;" id="custFilterWrapper">
                <input type="hidden" id="filterCustomerId" value="">
                <div id="custFilterBox"
                     style="display:flex;align-items:center;gap:8px;background:var(--bg-input);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:0 12px;cursor:text;transition:border-color 0.2s,box-shadow 0.2s;"
                     onclick="openCustFilter()">
                    <i class="bi bi-person" id="custFilterIcon" style="color:var(--text-muted);font-size:0.9rem;flex-shrink:0;"></i>
                    <input type="text" id="custFilterInput"
                           placeholder="Semua Pelanggan"
                           autocomplete="off" autocorrect="off" spellcheck="false"
                           style="flex:1;border:none;background:transparent;padding:9px 0;color:var(--text-primary);font-size:var(--font-size-xs);outline:none;font-family:var(--font-family);cursor:text;"
                           oninput="onCustFilterInput(this.value)"
                           onfocus="openCustFilter()">
                    <button type="button" id="custFilterClear"
                            onclick="event.stopPropagation();clearCustFilter()"
                            style="display:none;background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;font-size:0.8rem;line-height:1;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
                <div id="custFilterDropdown"
                     style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:300;
                            background:var(--surface-1);border:1px solid var(--primary);border-radius:var(--radius-md);
                            box-shadow:0 12px 40px rgba(0,0,0,0.35);overflow:hidden;max-height:240px;overflow-y:auto;">
                    <div id="custFilterList" style="padding:4px 0;"></div>
                </div>
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
// Parse datetime/date string dari DB (tanpa timezone) sebagai WIB GMT+7
// Mencegah browser salah mengartikan sebagai UTC sehingga tanggal mundur 1 hari
// Mendukung dua format: "2026-06-19 10:30:00" (datetime) dan "2026-06-19" (date-only)
function parseWIBDate(dateStr) {
    if (!dateStr) return new Date();
    if (dateStr.includes(' ')) {
        // Format datetime: "2026-06-19 10:30:00" → "2026-06-19T10:30:00+07:00"
        return new Date(dateStr.replace(' ', 'T') + '+07:00');
    } else if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
        // Format date-only: "2026-06-19" → "2026-06-19T00:00:00+07:00"
        return new Date(dateStr + 'T00:00:00+07:00');
    }
    // Fallback
    return new Date(dateStr);
}
function fmtDate(dateStr) {
    if (!dateStr) return '';
    const d = parseWIBDate(dateStr);
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}
function fmtDateTime(dateStr) {
    if (!dateStr) return '';
    const d = parseWIBDate(dateStr);
    const hh = String(d.getHours()).padStart(2,'0');
    const mm = String(d.getMinutes()).padStart(2,'0');
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}, ${hh}:${mm}`;
}
function isoDate(d) {
    return d.toISOString().split('T')[0];
}

// ===== Customer Filter Searchbox (Custom, Elegant) =====
let _allCustOptions = [];
let _custSearchTimeout = null;

async function loadCustomerOptions() {
    try {
        const res = await api(`${BASE_URL}api/customers`);
        if (res.success && res.data) {
            _allCustOptions = res.data.map(c => ({ id: c.id, name: c.name, phone: c.phone || '' }));
            renderCustFilterList('');
        }
    } catch(e) {}
}

function escSales(str) {
    const d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML;
}

function highlightQuery(text, q) {
    if (!q || !q.trim()) return text;
    const words = q.trim().split(/\s+/).filter(Boolean);
    let result = text;
    words.forEach(w => {
        const reg = new RegExp('(' + w.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')', 'gi');
        result = result.replace(reg, '<mark style="background:rgba(230,57,70,0.22);color:var(--primary);border-radius:2px;padding:0 2px;font-weight:700;">$1</mark>');
    });
    return result;
}

function multiKeywordMatch(text, query) {
    if (!query || !query.trim()) return true;
    const words = query.trim().toLowerCase().split(/\s+/);
    const t = text.toLowerCase();
    return words.every(w => t.includes(w));
}

function renderCustFilterList(q) {
    const list = document.getElementById('custFilterList');
    if (!list) return;
    const currentId = String(document.getElementById('filterCustomerId').value);
    const filtered = _allCustOptions.filter(c => multiKeywordMatch(c.name + ' ' + c.phone, q));

    const isAllActive = !currentId;
    const isNoneActive = currentId === 'none';

    let html = `
        <div onclick="selectCustFilter('','Semua Pelanggan')"
             style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background 0.15s;${isAllActive ? 'background:var(--primary-bg);' : ''}"
             onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='${isAllActive ? 'var(--primary-bg)' : 'transparent'}'">
            <div style="width:30px;height:30px;border-radius:50%;background:var(--surface-2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-people" style="font-size:0.8rem;color:var(--text-muted);"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:var(--font-size-xs);font-weight:600;color:var(--text-primary);">Semua Pelanggan</div>
                <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">Tampilkan semua transaksi</div>
            </div>
            ${isAllActive ? '<i class="bi bi-check-circle-fill" style="color:var(--primary);font-size:0.85rem;flex-shrink:0;"></i>' : ''}
        </div>
        <div onclick="selectCustFilter('none','Pelanggan Umum')"
             style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background 0.15s;border-top:1px solid var(--border-color);${isNoneActive ? 'background:var(--primary-bg);' : ''}"
             onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='${isNoneActive ? 'var(--primary-bg)' : 'transparent'}'">
            <div style="width:30px;height:30px;border-radius:50%;background:var(--surface-2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-person-slash" style="font-size:0.8rem;color:var(--text-muted);"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:var(--font-size-xs);font-weight:600;color:var(--text-primary);">Pelanggan Umum</div>
                <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">Transaksi tanpa nama pelanggan</div>
            </div>
            ${isNoneActive ? '<i class="bi bi-check-circle-fill" style="color:var(--primary);font-size:0.85rem;flex-shrink:0;"></i>' : ''}
        </div>`;

    if (filtered.length === 0 && q) {
        html += `<div style="padding:20px;text-align:center;color:var(--text-muted);font-size:var(--font-size-xs);border-top:1px solid var(--border-color);">
            <i class="bi bi-search" style="font-size:1.4rem;display:block;margin-bottom:8px;opacity:0.4;"></i>
            Tidak ada pelanggan "<strong>${escSales(q)}</strong>"
        </div>`;
    } else {
        filtered.slice(0, 30).forEach(c => {
            const isActive = currentId === String(c.id);
            const initials = c.name.trim().split(' ').map(w => w[0] || '').slice(0,2).join('').toUpperCase();
            const nameHL = highlightQuery(escSales(c.name), q);
            const phoneHL = c.phone ? highlightQuery(escSales(c.phone), q) : '';
            const label = escSales(c.name) + (c.phone ? ' · ' + escSales(c.phone) : '');
            html += `
                <div onclick="selectCustFilter(${c.id}, '${c.name.replace(/'/g,"\\'") + (c.phone ? ' · ' + c.phone : '')}')"
                     style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background 0.15s;border-top:1px solid var(--border-color);${isActive ? 'background:var(--primary-bg);' : ''}"
                     onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='${isActive ? 'var(--primary-bg)' : 'transparent'}'">
                    <div style="width:30px;height:30px;border-radius:50%;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:11px;font-weight:700;color:var(--primary);">${initials}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:var(--font-size-xs);font-weight:600;color:var(--text-primary);text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">${nameHL}</div>
                        ${c.phone ? `<div style="font-size:10px;color:var(--text-muted);margin-top:1px;"><i class="bi bi-telephone" style="font-size:9px;"></i> ${phoneHL}</div>` : '<div style="font-size:10px;color:var(--text-muted);margin-top:1px;">Tidak ada nomor HP</div>'}
                    </div>
                    ${isActive ? '<i class="bi bi-check-circle-fill" style="color:var(--primary);font-size:0.85rem;flex-shrink:0;"></i>' : ''}
                </div>`;
        });
    }
    list.innerHTML = html;
}

function openCustFilter() {
    const dd = document.getElementById('custFilterDropdown');
    const box = document.getElementById('custFilterBox');
    if (!dd || dd.style.display === 'block') return;
    dd.style.display = 'block';
    box.style.borderColor = 'var(--primary)';
    box.style.boxShadow = '0 0 0 3px rgba(230,57,70,0.15)';
    renderCustFilterList(document.getElementById('custFilterInput').value);
    setTimeout(() => document.addEventListener('click', _custOutsideHandler), 10);
}

function _custOutsideHandler(e) {
    const wrapper = document.getElementById('custFilterWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        closeCustFilter();
    } else {
        document.addEventListener('click', _custOutsideHandler, { once: true });
    }
}

function closeCustFilter() {
    const dd = document.getElementById('custFilterDropdown');
    const box = document.getElementById('custFilterBox');
    if (dd) dd.style.display = 'none';
    if (box) { box.style.borderColor = 'var(--border-color)'; box.style.boxShadow = 'none'; }
}

function onCustFilterInput(q) {
    clearTimeout(_custSearchTimeout);
    _custSearchTimeout = setTimeout(() => {
        renderCustFilterList(q);
        if (document.getElementById('custFilterDropdown').style.display !== 'block') openCustFilter();
    }, 120);
}

function selectCustFilter(id, label) {
    document.getElementById('filterCustomerId').value = id;
    document.getElementById('custFilterInput').value = label;
    const clearBtn = document.getElementById('custFilterClear');
    const icon = document.getElementById('custFilterIcon');
    if (clearBtn) clearBtn.style.display = id ? 'inline-flex' : 'none';
    if (icon) icon.style.color = id ? 'var(--primary)' : 'var(--text-muted)';
    closeCustFilter();
}

function clearCustFilter() {
    selectCustFilter('', '');
    document.getElementById('custFilterInput').placeholder = 'Semua Pelanggan';
    renderCustFilterList('');
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
    clearCustFilter();
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
    const customerId  = document.getElementById('filterCustomerId').value;

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
    document.getElementById('summaryTx').textContent = summary.total_transactions.toLocaleString('id-ID');
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

    const todayISO = isoDate(new Date());

    // 1. MOBILE VIEW (max-width: 991px) - 100% UNTOUCHED
    let mobileHtml = '<div class="sales-mobile-view d-lg-none">';
    Object.keys(groups).sort().reverse().forEach(dateKey => {
        const dayTx = groups[dateKey];
        const dayOmzet  = dayTx.reduce((s, t) => s + parseFloat(t.total_amount || 0), 0);
        const dayProfit = dayTx.reduce((s, t) => s + parseFloat(t.total_profit || 0), 0);
        
        const isToday = dateKey === todayISO;
        const displayStyle = isToday ? 'block' : 'none';
        const chevronStyle = isToday ? 'transform:rotate(180deg);' : '';

        mobileHtml += `
        <div style="margin-bottom:12px;">
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
            <div id="date-group-${dateKey}" style="display:${displayStyle};padding-top:8px;">
                ${dayTx.map(t => renderSaleCard(t)).join('')}
            </div>
        </div>`;
    });
    mobileHtml += '</div>';

    // 2. DESKTOP VIEW (min-width: 992px) - Modern, High-Density Data Table!
    let desktopHtml = `
    <div class="sales-desktop-view d-none d-lg-block">
        <div class="sales-desktop-card">
            <table class="sales-desktop-table">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">#</th>
                        <th style="width:170px;">No. Invoice</th>
                        <th style="width:100px;">Waktu</th>
                        <th>Pelanggan</th>
                        <th style="width:90px;text-align:center;">Mode</th>
                        <th style="width:100px;text-align:right;">Total Item</th>
                        <th style="width:120px;text-align:right;">Modal</th>
                        <th style="width:130px;text-align:right;">Total Omzet</th>
                        <th style="width:160px;text-align:right;">Profit & Margin</th>
                        <th style="width:130px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>`;

    Object.keys(groups).sort().reverse().forEach(dateKey => {
        const dayTx = groups[dateKey];
        const dayOmzet  = dayTx.reduce((s, t) => s + parseFloat(t.total_amount || 0), 0);
        const dayProfit = dayTx.reduce((s, t) => s + parseFloat(t.total_profit || 0), 0);
        const isToday = dateKey === todayISO;

        // Desktop Date Header Row
        desktopHtml += `
        <tr class="sales-desktop-date-hdr">
            <td colspan="10" style="padding:10px 16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="bi bi-calendar-check" style="color:var(--primary);font-size:1.1rem;"></i>
                        <span style="font-weight:800;font-size:13px;color:var(--text-primary);">${fmtDate(dateKey)}</span>
                        ${isToday ? '<span class="badge-custom badge-primary" style="font-size:10px;padding:2px 8px;">Hari Ini</span>' : ''}
                        <span style="font-size:11px;color:var(--text-muted);font-weight:500;">(${dayTx.length} transaksi)</span>
                    </div>
                    <div style="display:flex;gap:20px;align-items:center;font-size:12px;">
                        <div>Total Omzet: <strong style="color:var(--primary);font-size:13px;">${rupiah(dayOmzet)}</strong></div>
                        <div>Total Profit: <strong style="color:var(--success);font-size:13px;">${rupiah(dayProfit)}</strong></div>
                    </div>
                </div>
            </td>
        </tr>`;

        // Desktop Transaction Rows
        dayTx.forEach((t, idx) => {
            const profit = parseFloat(t.total_profit || 0);
            const amount = parseFloat(t.total_amount || 0);
            const modal  = amount - profit;
            const markup = modal > 0 ? (profit / modal * 100) : (profit > 0 ? 100 : 0);
            const custName = t.customer_name || 'Pelanggan Umum';
            const timeStr = t.created_at ? t.created_at.split(' ')[1] || '' : '';

            desktopHtml += `
            <tr class="sale-row-desktop" data-sale-id="${t.id}" onclick="window.location.href='${BASE_URL}sales/${t.id}'">
                <td style="text-align:center;color:var(--text-muted);font-size:11px;">${idx + 1}</td>
                <td>
                    <span style="font-weight:700;color:var(--text-primary);font-family:monospace;font-size:12px;">${t.invoice_number}</span>
                </td>
                <td style="color:var(--text-muted);font-size:12px;">
                    <i class="bi bi-clock" style="font-size:10px;margin-right:2px;"></i> ${fmtDateTime(t.created_at).split(', ')[1] || timeStr}
                </td>
                <td>
                    <div style="font-weight:600;color:var(--text-primary);">${custName}</div>
                </td>
                <td style="text-align:center;">
                    <span class="badge-custom badge-${t.sale_mode === 'retail' ? 'info' : 'warning'}" style="font-size:10px;padding:2px 6px;">${t.sale_mode === 'retail' ? 'Retail' : 'Grosir'}</span>
                </td>
                <td style="text-align:right;color:var(--text-secondary);font-size:12px;">
                    ${parseFloat(t.total_items || 0)} item
                </td>
                <td style="text-align:right;color:var(--text-muted);font-size:12px;">
                    ${rupiah(modal)}
                </td>
                <td style="text-align:right;font-weight:800;color:var(--primary);font-size:13px;">
                    ${rupiah(amount)}
                </td>
                <td style="text-align:right;font-size:12px;">
                    <span style="font-weight:700;color:var(--success);">${rupiah(profit)}</span>
                    <span style="background:var(--success-bg);color:var(--success);padding:1px 5px;border-radius:4px;font-size:10px;font-weight:700;margin-left:4px;">+${markup.toFixed(1).replace('.',',')}%</span>
                </td>
                <td style="text-align:center;" onclick="event.stopPropagation();">
                    <div style="display:flex;gap:6px;justify-content:center;align-items:center;">
                        <a href="${BASE_URL}sales/${t.id}" class="btn-outline-custom" title="Lihat Detail" style="padding:4px 10px;font-size:11px;border-radius:6px;text-decoration:none;color:var(--text-primary);">
                            <i class="bi bi-eye"></i> Detail
                        </a>
                        <button type="button" onclick="shareInvoice(${t.id}, '${t.invoice_number}')" data-share-btn="${t.id}" title="Bagikan / Unduh Struk" class="btn-outline-custom" style="padding:4px 8px;font-size:11px;border-radius:6px;color:var(--primary);border-color:var(--primary-bg);">
                            <i class="bi bi-share"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        });
    });

    desktopHtml += `
                </tbody>
            </table>
        </div>
    </div>`;

    container.innerHTML = mobileHtml + desktopHtml;
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
<script src="<?= BASE_URL ?>public/js/html2canvas.min.js"></script>
<script>
function formatRupiah(amount) { return 'Rp ' + parseInt(amount || 0).toLocaleString('id-ID'); }
function formatDateShort(dateStr) {
    if (!dateStr) return '';
    // Tambahkan +07:00 agar browser tidak memparsing sebagai UTC
    const normalized = dateStr.replace(' ', 'T') + '+07:00';
    const d = new Date(normalized);
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
    if (btn) { btn.innerHTML = '<i class="spinner-border spinner-border-sm" style="width:14px;height:14px;"></i>'; btn.disabled = true; }
    try {
        const res = await fetch(`${BASE_URL}api/sales/invoice/${saleId}`);
        if (!res.ok) throw new Error('Gagal mengambil data invoice');
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Data tidak ditemukan');

        const tx = data.transaction || {};
        const items = data.items || [];
        const storeName = data.store_name || 'AlfarezMart';
        const saleUrl = `${BASE_URL}sales/${saleId}`;

        // Construct formatted receipt text
        let itemsListText = items.map(item => `- ${item.invoice_name || item.full_name || 'Item'} (${parseInt(item.quantity)}x @${rupiah(item.unit_price)}) = ${rupiah(item.total_price)}`).join('\n');

        const shareText = `🧾 *STRUK PENJUALAN - ${storeName.toUpperCase()}*\n` +
            `-----------------------------------\n` +
            `No. Invoice: *${tx.invoice_number}*\n` +
            `Tanggal: ${fmtDateTime(tx.created_at)}\n` +
            `Pelanggan: ${tx.customer_name || 'Pelanggan Umum'}\n` +
            `Pembayaran: ${tx.payment_method || 'Cash'}\n` +
            `-----------------------------------\n` +
            `*Rincian Pembelian:*\n${itemsListText}\n` +
            `-----------------------------------\n` +
            `*TOTAL BAYAR: ${rupiah(tx.total_amount)}*\n` +
            `-----------------------------------\n` +
            `Lihat Struk Digital:\n${saleUrl}\n\n` +
            `Terima kasih telah berbelanja di ${storeName}!`;

        const waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText)}`;
        const tgUrl = `https://t.me/share/url?url=${encodeURIComponent(saleUrl)}&text=${encodeURIComponent(`Struk Penjualan ${tx.invoice_number} - ${storeName}`)}`;

        // Show Share Modal
        AppModal.show({
            title: 'Bagikan Struk Penjualan',
            subtitle: `Invoice #${tx.invoice_number}`,
            icon: 'bi-share-fill',
            iconColor: 'var(--primary-bg)',
            iconAccent: 'var(--primary)',
            bodyHTML: `
                <div style="text-align:center;padding:4px 0;">
                    <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">Pilih media sosial atau opsi untuk membagikan struk ini:</div>
                    
                    <!-- Direct Social Media Share Buttons -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <a href="${waUrl}" target="_blank" rel="noopener" style="display:flex;align-items:center;justify-content:center;gap:10px;padding:14px;background:#25D366;color:white;border-radius:12px;text-decoration:none;font-weight:700;font-size:13px;box-shadow:0 4px 14px rgba(37,211,102,0.35);transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            <i class="bi bi-whatsapp" style="font-size:1.4rem;"></i> WhatsApp
                        </a>

                        <a href="${tgUrl}" target="_blank" rel="noopener" style="display:flex;align-items:center;justify-content:center;gap:10px;padding:14px;background:#0088cc;color:white;border-radius:12px;text-decoration:none;font-weight:700;font-size:13px;box-shadow:0 4px 14px rgba(0,136,204,0.35);transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            <i class="bi bi-telegram" style="font-size:1.4rem;"></i> Telegram
                        </a>
                    </div>

                    <!-- Quick Tools Buttons -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                        <button type="button" onclick="copyReceiptText(\`${encodeURIComponent(shareText)}\`)" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:var(--surface-2);border:1px solid var(--border-color);color:var(--text-primary);border-radius:10px;font-weight:600;font-size:12px;cursor:pointer;">
                            <i class="bi bi-clipboard"></i> Salin Teks
                        </button>

                        <button type="button" onclick="downloadReceiptImage(${saleId}, '${tx.invoice_number}')" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:var(--surface-2);border:1px solid var(--border-color);color:var(--text-primary);border-radius:10px;font-weight:600;font-size:12px;cursor:pointer;">
                            <i class="bi bi-download"></i> Unduh Gambar
                        </button>
                    </div>

                    ${navigator.share ? `
                    <div style="margin-top:12px;padding-top:12px;border-top:1px dashed var(--border-color);">
                        <button type="button" onclick="triggerNativeShare(\`${encodeURIComponent(shareText)}\`, '${saleUrl}')" style="width:100%;padding:10px;background:var(--primary-bg);border:1px solid var(--primary);color:var(--primary);border-radius:10px;font-weight:700;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                            <i class="bi bi-share"></i> Bagikan via Aplikasi HP Lainnya
                        </button>
                    </div>` : ''}
                </div>`,
            submitText: null,
            cancelText: 'Tutup'
        });
    } catch(err) {
        showToast('Gagal memuat struk: ' + (err.message || 'Error tidak diketahui'), 'error');
    } finally {
        if (btn) { btn.innerHTML = '<i class="bi bi-share"></i>'; btn.disabled = false; }
    }
}

function copyReceiptText(encodedText) {
    const text = decodeURIComponent(encodedText);
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Teks struk berhasil disalin ke clipboard!', 'success');
        }).catch(() => {
            fallbackCopyText(text);
        });
    } else {
        fallbackCopyText(text);
    }
}

function fallbackCopyText(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    showToast('Teks struk berhasil disalin!', 'success');
}

async function triggerNativeShare(encodedText, url) {
    const text = decodeURIComponent(encodedText);
    if (navigator.share) {
        try {
            await navigator.share({ title: 'Struk Penjualan AlfarezMart', text: text, url: url });
            showToast('Struk berhasil dibagikan', 'success');
        } catch(e) {
            console.log('Native share cancelled:', e);
        }
    }
}

async function downloadReceiptImage(saleId, invoiceNumber) {
    try {
        showToast('Menyiapkan gambar struk...', 'info');
        const res = await fetch(`${BASE_URL}api/sales/invoice/${saleId}`);
        const data = await res.json();
        const canvasEl = document.getElementById('receiptShareCanvas');
        canvasEl.innerHTML = renderReceiptHTML(data);
        canvasEl.style.top = '-9999px'; canvasEl.style.left = '-9999px'; canvasEl.style.display = 'block';
        const receiptNode = canvasEl.firstElementChild;
        const canvas = await html2canvas(receiptNode, { scale: 2.5, useCORS: true, backgroundColor: '#ffffff', logging: false });
        canvasEl.innerHTML = '';
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
        const fileName = `struk-${invoiceNumber.replace(/[^a-zA-Z0-9\-]/g,'_')}.png`;
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a'); a.href = url; a.download = fileName;
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 2000);
        showToast('Gambar struk berhasil diunduh', 'success');
    } catch(e) {
        showToast('Gagal mengunduh gambar struk', 'error');
    }
}
</script>
