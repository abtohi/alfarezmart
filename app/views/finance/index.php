<?php
/**
 * Daily Finance Index View
 * 
 * @var string $csrfToken
 */
$combinedMarkup = isset($combinedMarkup) ? (float)$combinedMarkup : 27.82;
$storeName      = isset($storeName)      ? $storeName      : 'AlfarezMart';
$storeAddress   = isset($storeAddress)   ? $storeAddress   : '';
$storePhone     = isset($storePhone)     ? $storePhone     : '';
$logoBase64     = isset($logoBase64)     ? $logoBase64     : '';
$markupEcer     = isset($markupStats['level1']['avg_ecer'])   ? (float)$markupStats['level1']['avg_ecer']   : 0;
$markupGrosir   = isset($markupStats['level1']['avg_grosir']) ? (float)$markupStats['level1']['avg_grosir'] : 0;
?>
<!-- Chart.js & html2pdf -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASE_URL ?>public/js/html2pdf.bundle.min.js"></script>

<div class="page-section" style="padding-bottom: 80px;">
    <!-- Date Navigation Header -->
    <div style="background: var(--gradient-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h4 style="font-weight: 700; font-size: var(--font-size-md); margin: 0;">Keuangan Harian</h4>
                <p style="font-size: var(--font-size-xs); color: var(--text-muted); margin: 4px 0 0 0;">Catat & bandingkan pendapatan/pengeluaran</p>
            </div>
            <button class="btn-primary-custom" style="padding: 10px 14px; cursor: pointer; border-radius: var(--radius-md);" onclick="showAddLogModal()">
                <i class="bi bi-plus-lg"></i> Transaksi
            </button>
        </div>
        
        <!-- Date Selector -->
        <div style="background: var(--bg-primary); padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-calendar3" style="color: var(--text-muted); font-size: 14px;"></i>
            <span style="font-size: var(--font-size-xs); color: var(--text-muted); font-weight: 600;">Tanggal:</span>
            <input type="date" id="selectedDate" value="<?= date('Y-m-d') ?>" style="flex: 1; border: none; background: transparent; color: var(--text-primary); font-size: var(--font-size-sm); font-weight: 700; outline: none; padding: 2px 4px; color-scheme: dark;">
        </div>
    </div>

    <!-- Hidden CSRF Token -->
    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- ============================================================
         OMZET ANALYTICS SECTION
    ============================================================= -->
    <div id="omzetAnalyticsSection" style="margin-bottom:24px;">

        <!-- Section Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
                <div class="section-title" style="margin-bottom:2px;">📊 Grafik Omzet &amp; Estimasi Profit</div>
                <div style="font-size:10px; color:var(--text-muted);">
                    Markup Gabungan: <strong>+<?= number_format($combinedMarkup,1) ?>%</strong>
                    &nbsp;|&nbsp; Ecer: +<?= number_format($markupEcer,1) ?>%
                    &nbsp;|&nbsp; Grosir: +<?= number_format($markupGrosir,1) ?>%
                </div>
            </div>
            <button id="btnDownloadLaporanPDF" onclick="downloadLaporanPDF()"
                style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:10px;padding:9px 14px;font-size:11px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(99,102,241,.35);">
                <i class="bi bi-file-earmark-pdf-fill"></i> Unduh PDF
            </button>
        </div>

        <!-- Date Range Presets -->
        <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:12px;">
            <button class="omzet-preset active" data-preset="7" onclick="setOmzetPreset(7,this)">7 Hari</button>
            <button class="omzet-preset" data-preset="14" onclick="setOmzetPreset(14,this)">14 Hari</button>
            <button class="omzet-preset" data-preset="30" onclick="setOmzetPreset(30,this)">30 Hari</button>
            <button class="omzet-preset" data-preset="bulan" onclick="setOmzetPreset('bulan',this)">Bulan Ini</button>
            <button class="omzet-preset" data-preset="custom" onclick="setOmzetPreset('custom',this)">Custom</button>
        </div>

        <!-- Custom Date Range -->
        <div id="omzetCustomRange" style="display:none; background:var(--bg-primary); border:1px solid var(--border-color); border-radius:10px; padding:12px; margin-bottom:12px;">
            <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
                <div>
                    <label style="font-size:10px;color:var(--text-muted);font-weight:600;display:block;margin-bottom:3px;">Dari Tanggal</label>
                    <input type="date" id="omzetStartDate" style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:8px;padding:7px 10px;color:var(--text-primary);font-size:13px;color-scheme:dark;">
                </div>
                <div>
                    <label style="font-size:10px;color:var(--text-muted);font-weight:600;display:block;margin-bottom:3px;">Sampai Tanggal</label>
                    <input type="date" id="omzetEndDate" style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:8px;padding:7px 10px;color:var(--text-primary);font-size:13px;color-scheme:dark;">
                </div>
                <button onclick="applyOmzetCustomRange()" style="background:var(--info);color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:700;cursor:pointer;">Terapkan</button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div id="omzetKpiGrid" style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:14px;">
            <div class="omzet-kpi-card">
                <div class="okpi-icon" style="background:rgba(99,102,241,.15);color:#6366f1;"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="okpi-label">Total Omzet</div>
                <div class="okpi-value" id="kpiOmzetVal">—</div>
                <div class="okpi-badge" id="kpiOmzetBadge"></div>
            </div>
            <div class="omzet-kpi-card">
                <div class="okpi-icon" style="background:rgba(16,185,129,.15);color:#10b981;"><i class="bi bi-cash-coin"></i></div>
                <div class="okpi-label">Est. Profit (+<?= number_format($combinedMarkup,1) ?>%)</div>
                <div class="okpi-value" id="kpiEstProfitVal">—</div>
                <div class="okpi-badge" id="kpiEstProfitBadge"></div>
            </div>
            <div class="omzet-kpi-card">
                <div class="okpi-icon" style="background:rgba(245,158,11,.15);color:#f59e0b;"><i class="bi bi-calendar-day"></i></div>
                <div class="okpi-label">Rata-rata / Hari</div>
                <div class="okpi-value" id="kpiAvgDailyVal">—</div>
                <div class="okpi-badge" id="kpiAvgDailyBadge"></div>
            </div>
            <div class="omzet-kpi-card">
                <div class="okpi-icon" style="background:rgba(239,68,68,.15);color:#ef4444;"><i class="bi bi-receipt"></i></div>
                <div class="okpi-label">Total Transaksi</div>
                <div class="okpi-value" id="kpiTrxVal">—</div>
                <div class="okpi-badge" id="kpiTrxBadge"></div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div style="background:var(--gradient-card);border:1px solid var(--border-color);border-radius:12px;padding:14px;margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="font-size:11px;font-weight:700;color:var(--text-primary);">Progress Omzet vs Estimasi Profit</span>
                <span id="omzetProgressPct" style="font-size:11px;font-weight:800;color:#6366f1;">—</span>
            </div>
            <div style="height:10px;background:var(--surface-2);border-radius:5px;overflow:hidden;position:relative;margin-bottom:6px;">
                <div id="omzetBar" style="position:absolute;top:0;left:0;height:100%;width:0%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:5px;transition:width .6s ease;"></div>
                <div id="profitBarOverlay" style="position:absolute;top:0;left:0;height:100%;width:0%;background:rgba(16,185,129,.55);border-radius:5px;transition:width .6s ease;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:9px;color:var(--text-muted);">
                <span><span style="display:inline-block;width:8px;height:8px;background:#6366f1;border-radius:50%;margin-right:3px;"></span>Omzet</span>
                <span><span style="display:inline-block;width:8px;height:8px;background:rgba(16,185,129,.7);border-radius:50%;margin-right:3px;"></span>Est. Profit</span>
                <span id="omzetProgressLabel" style="font-weight:700;color:var(--text-primary);">Rp 0</span>
            </div>
        </div>

        <!-- Chart Canvas -->
        <div style="background:var(--gradient-card);border:1px solid var(--border-color);border-radius:16px;padding:16px;margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <div style="font-size:12px;font-weight:700;color:var(--text-primary);">Tren Omzet &amp; Estimasi Profit</div>
                <div style="display:flex;gap:10px;font-size:10px;color:var(--text-muted);">
                    <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:3px;background:#6366f1;display:inline-block;border-radius:2px;"></span>Omzet</span>
                    <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:3px;background:#10b981;display:inline-block;border-radius:2px;"></span>Est.Profit</span>
                </div>
            </div>
            <div id="omzetChartLoader" style="text-align:center;padding:28px 0;">
                <div class="elegant-loader" style="margin:auto;"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
            </div>
            <canvas id="omzetChart" style="display:none;max-height:230px;"></canvas>
        </div>

        <!-- Insights -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
            <div class="omzet-insight-card">
                <div class="oi-icon"><i class="bi bi-trophy-fill" style="color:#f59e0b;"></i></div>
                <div class="oi-label">Hari Puncak</div>
                <div class="oi-val" id="insightPeakDay">—</div>
                <div class="oi-sub" id="insightPeakRev"></div>
            </div>
            <div class="omzet-insight-card">
                <div class="oi-icon"><i class="bi bi-basket2-fill" style="color:#6366f1;"></i></div>
                <div class="oi-label">Nilai / Transaksi</div>
                <div class="oi-val" id="insightAOV">—</div>
                <div class="oi-sub" id="insightAOVSub"></div>
            </div>
            <div class="omzet-insight-card">
                <div class="oi-icon"><i class="bi bi-arrow-up-circle-fill" style="color:#10b981;"></i></div>
                <div class="oi-label">Pertumbuhan Omzet</div>
                <div class="oi-val" id="insightGrowth">—</div>
                <div class="oi-sub" id="insightGrowthSub"></div>
            </div>
            <div class="omzet-insight-card">
                <div class="oi-icon"><i class="bi bi-percent" style="color:#ec4899;"></i></div>
                <div class="oi-label">Margin Est. Profit</div>
                <div class="oi-val" id="insightMargin">—</div>
                <div class="oi-sub" id="insightMarginSub"></div>
            </div>
        </div>

    </div><!-- /omzetAnalyticsSection -->

    <!-- Visual Comparison Card (Net Balance & Bar) -->
    <div class="app-card" style="padding: 20px; margin-bottom: 20px;">
        <div style="text-align: center; margin-bottom: 16px;">
            <span style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Selisih Hari Ini (Net)</span>
            <h2 id="netBalanceValue" style="font-size: 1.8rem; font-weight: 800; margin: 4px 0; color: var(--text-primary);">Rp 0</h2>
            <div id="netStatusBadge" style="display: inline-block; font-size: 9px; padding: 2px 8px; border-radius: 20px; font-weight: 700; text-transform: uppercase;">SEIMBANG</div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; border-top: 1px solid var(--border-color); padding-top: 16px;">
            <div>
                <span style="font-size: 10px; color: var(--text-muted); display: block; margin-bottom: 2px;">Total Pemasukan</span>
                <span id="totalIncomeValue" style="font-weight: 800; font-size: var(--font-size-md); color: var(--success);">Rp 0</span>
            </div>
            <div style="text-align: right; border-left: 1px solid var(--border-color); padding-left: 12px;">
                <span style="font-size: 10px; color: var(--text-muted); display: block; margin-bottom: 2px;">Total Pengeluaran</span>
                <span id="totalExpenseValue" style="font-weight: 800; font-size: var(--font-size-md); color: var(--primary);">Rp 0</span>
            </div>
        </div>

        <!-- Progress Bar Comparison -->
        <div style="position: relative; height: 8px; background: var(--surface-2); border-radius: 4px; overflow: hidden; display: flex;">
            <div id="incomeBar" style="height: 100%; width: 50%; background: var(--success); transition: width 0.3s ease;"></div>
            <div id="expenseBar" style="height: 100%; width: 50%; background: var(--primary); transition: width 0.3s ease;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 9px; color: var(--text-muted); margin-top: 6px;">
            <span id="incomePercentage">Pemasukan: 0%</span>
            <span id="expensePercentage">Pengeluaran: 0%</span>
        </div>
    </div>

    <!-- Grid POS Keuangan Dinamis -->
    <div style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div class="section-title" style="margin-bottom: 0;">Sumber Keuangan (Pos)</div>
            <button onclick="manageAccounts()" style="background: transparent; border: none; color: var(--info); cursor: pointer; font-size: var(--font-size-xs); font-weight: 600;">
                <i class="bi bi-gear-fill"></i> Kelola POS
            </button>
        </div>
        <div id="posGridContainer" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <!-- POS Cards akan digenerate disini oleh JS -->
            <div class="elegant-loader" style="margin: 20px auto; grid-column: span 2;">
                <div class="dot"></div><div class="dot"></div><div class="dot"></div>
            </div>
        </div>
    </div>

    <!-- Filter and Transaction List -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div class="section-title" style="margin-bottom: 0;">Daftar Transaksi</div>
            <div class="dropdown" style="width:auto; min-width:140px;">
                <button id="filterPostBtn" class="btn-dropdown-modern dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:6px 12px; font-size:11.5px;">
                    <span><i class="bi bi-wallet2 me-1 text-primary"></i>Semua Pos</span>
                </button>
                <ul id="filterPostMenu" class="dropdown-menu dropdown-menu-dark shadow" style="font-size:12px; min-width:100%;">
                    <!-- Options akan digenerate disini -->
                </ul>
                <input type="hidden" id="filterPost" value="">
            </div>
        </div>
        
        <div id="bulkActionBar" style="display: none; background: var(--surface-2); padding: 10px 14px; border-radius: var(--radius-md); margin-bottom: 12px; align-items: center; justify-content: space-between; border: 1px solid var(--primary);">
            <div style="font-size: var(--font-size-sm); font-weight: 700; color: var(--primary);">
                <span id="selectedCountText">0</span> transaksi terpilih
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="btn-primary-custom" onclick="bulkDeleteSelected()" style="background: var(--primary); padding: 6px 12px; border-radius: var(--radius-sm); font-size: var(--font-size-xs);">
                    <i class="bi bi-trash-fill"></i> Hapus Terpilih
                </button>
                <button class="btn-primary-custom" onclick="clearSelection()" style="background: var(--surface-1); color: var(--text-primary); border: 1px solid var(--border-color); padding: 6px 12px; border-radius: var(--radius-sm); font-size: var(--font-size-xs);">
                    Batal
                </button>
            </div>
        </div>

        <div id="transactionsList">
            <div class="elegant-loader" style="margin: 20px auto;">
                <div class="dot"></div><div class="dot"></div><div class="dot"></div>
            </div>
        </div>
    </div>
</div>

<style>
.post-card.active {
    border-color: var(--info) !important;
    background: var(--bg-primary) !important;
}
.modal-form-group {
    margin-bottom: 12px;
    text-align: left;
}
.modal-form-group label {
    font-size: var(--font-size-xs);
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 4px;
    display: block;
}
</style>

<!-- Datalist untuk Autocomplete Kategori/Jenis Transaksi -->
<datalist id="categoryDatalist"></datalist>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const csrfVal = document.getElementById('csrfToken').value;
    const dateInput = document.getElementById('selectedDate');
    const filterPost = document.getElementById('filterPost');
    
    let activePostFilter = '';
    let currentLogs = [];
    let accountsData = [];
    let currentBreakdown = {};
    let categoriesData = [];
    let masterDataRefreshPromise = null; // Tracks in-progress server refresh
    let selectedLogs = new Set();

    // Helper: Colors for dynamically generated POS cards
    const posColors = [
        { bg: 'rgba(76, 201, 240, 0.1)', icon: '#4cc9f0', bi: 'bi-inbox' },
        { bg: 'rgba(255, 183, 3, 0.1)', icon: '#ffb703', bi: 'bi-phone' },
        { bg: 'rgba(46, 196, 182, 0.1)', icon: '#2ec4b6', bi: 'bi-cup-hot' },
        { bg: 'rgba(230, 57, 70, 0.1)', icon: '#e63946', bi: 'bi-fire' },
        { bg: 'rgba(114, 9, 183, 0.1)', icon: '#7209b7', bi: 'bi-wallet' },
        { bg: 'rgba(67, 97, 238, 0.1)', icon: '#4361ee', bi: 'bi-safe' },
        { bg: 'rgba(247, 37, 133, 0.1)', icon: '#f72585', bi: 'bi-bank' },
        { bg: 'rgba(181, 228, 140, 0.1)', icon: '#b5e48c', bi: 'bi-cash-coin' }
    ];

    function getPosStyle(index) {
        return posColors[index % posColors.length];
    }

    // Load Master Data (Accounts & Categories)
    async function loadMasterData() {
        try {
            // Try Dexie first for speed
            if (typeof OfflineDB !== 'undefined') {
                const financeData = await OfflineDB.getAllFinance();
                if (financeData && financeData.length > 0) {
                    accountsData = financeData.filter(x => x._type === 'account');
                    categoriesData = financeData.filter(x => x._type === 'finance_cat');
                    renderPosGrid();
                    updateFilterOptions();
                    updateCategoryDatalist();
                    // Refresh from server in background if online
                    // Store the promise so modal can await it before opening
                    if (navigator.onLine) {
                        masterDataRefreshPromise = refreshMasterDataFromServer();
                        masterDataRefreshPromise.catch(e => console.error("Background refresh failed:", e));
                    }
                    return;
                }
            }
            await refreshMasterDataFromServer();
        } catch (e) {
            console.error("Gagal memuat data master keuangan:", e);
            // Ensure accountsData has at least default entries to prevent empty dropdown
            if (!accountsData || accountsData.length === 0) {
                accountsData = [
                    { id: 1, name: 'Saldo Utama', is_active: 1 },
                    { id: 2, name: 'Saldo Rokok', is_active: 1 },
                    { id: 3, name: 'Saldo Pulsa', is_active: 1 }
                ];
            }
            renderPosGrid();
            updateFilterOptions();
        }
    }

    async function refreshMasterDataFromServer() {
        // Don't attempt server refresh when offline — prevents false-positive error log spam
        if (!navigator.onLine) return;
        try {
            const accRes = await api(`${BASE_URL}api/finance/accounts`, { silent: true, noOfflineQueue: true });
            if (accRes && accRes.success) accountsData = accRes.data;

            const catRes = await api(`${BASE_URL}api/finance/categories`, { silent: true, noOfflineQueue: true });
            if (catRes && catRes.success) categoriesData = catRes.data;

            renderPosGrid();
            updateFilterOptions();
            updateCategoryDatalist();
        } catch (e) {
            // Silently fail — stale local data is used as fallback
            if (navigator.onLine) {
                console.warn("Gagal refresh data master dari server:", e.message || e);
            }
        }
    }

    function renderPosGrid() {
        const container = document.getElementById('posGridContainer');
        if (!accountsData || accountsData.length === 0) {
            container.innerHTML = `<div style="grid-column: span 2; text-align:center; font-size:12px; color:var(--text-muted);">Belum ada POS Keuangan</div>`;
            return;
        }

        const hiddenPos = ['Uang Laci', 'Uang Pulsa', 'Uang Rokok', 'Uang Pinjaman'];
        let visibleAccounts = accountsData.filter(acc => !hiddenPos.includes(acc.name));

        let html = '';
        visibleAccounts.forEach((acc, index) => {
            const style = getPosStyle(index);
            const safeName = escapeHtml(acc.name);
            const shortId = `pos_${acc.id}`;
            const postData = (typeof currentBreakdown !== 'undefined' && currentBreakdown[acc.name]) ? currentBreakdown[acc.name] : { income: 0, expense: 0, net: 0 };
            
            html += `
            <div class="app-card post-card ${activePostFilter === acc.name ? 'active' : ''}" data-post="${safeName}" data-index="${index}" style="padding: 14px; position: relative; cursor: pointer; transition: transform 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: var(--font-size-xs); font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%;">${safeName}</span>
                    <div style="width: 24px; height: 24px; background: ${style.bg}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: ${style.icon}; flex-shrink: 0;">
                        <i class="bi ${style.bi}" style="font-size: 11px;"></i>
                    </div>
                </div>
                <div id="net_${shortId}" style="font-size: var(--font-size-sm); font-weight: 800; color: var(--text-primary);">${formatRupiah(postData.net)}</div>
                <div style="font-size: 8px; color: var(--text-muted); margin-top: 4px; display: flex; justify-content: space-between;">
                    <span>Masuk: <span id="inc_${shortId}" style="color: var(--success);">${formatRupiah(postData.income)}</span></span>
                    <span>Keluar: <span id="exp_${shortId}" style="color: var(--primary);">${formatRupiah(postData.expense)}</span></span>
                </div>
                ${acc.dependency_name ? `<div style="font-size: 7px; color: var(--info); margin-top: 4px; text-align: right;"><i class="bi bi-link"></i> ${escapeHtml(acc.dependency_name)}</div>` : ''}
            </div>
            `;
        });
        container.innerHTML = html;

        // Re-attach listeners
        document.querySelectorAll('.post-card').forEach(card => {
            card.addEventListener('click', function() {
                const clickedPost = this.dataset.post;
                if (activePostFilter === clickedPost) {
                    activePostFilter = '';
                    filterPost.value = '';
                    this.classList.remove('active');
                } else {
                    activePostFilter = clickedPost;
                    filterPost.value = clickedPost;
                    document.querySelectorAll('.post-card').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                }
                renderTransactions();
            });
        });
    }

    function updateFilterOptions() {
        let html = `<li><a class="dropdown-item ${activePostFilter === '' ? 'active' : ''}" href="#" onclick="event.preventDefault(); document.getElementById('filterPost').value=''; document.getElementById('filterPostBtn').querySelector('span').textContent='Semua Pos'; document.querySelectorAll('#filterPostMenu .dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); document.getElementById('filterPost').dispatchEvent(new Event('change'));">Semua Pos</a></li>`;
        if (activePostFilter === '') document.getElementById('filterPostBtn').querySelector('span').textContent = 'Semua Pos';
        accountsData.forEach(acc => {
            const isSelected = activePostFilter === acc.name;
            if (isSelected) {
                document.getElementById('filterPostBtn').querySelector('span').textContent = escapeHtml(acc.name);
            }
            html += `<li><a class="dropdown-item ${isSelected ? 'active' : ''}" href="#" onclick="event.preventDefault(); document.getElementById('filterPost').value='${escapeHtml(acc.name)}'; document.getElementById('filterPostBtn').querySelector('span').textContent='${escapeHtml(acc.name)}'; document.querySelectorAll('#filterPostMenu .dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); document.getElementById('filterPost').dispatchEvent(new Event('change'));">${escapeHtml(acc.name)}</a></li>`;
        });
        document.getElementById('filterPostMenu').innerHTML = html;
    }

    function updateCategoryDatalist() {
        let html = '';
        categoriesData.forEach(cat => {
            html += `<option value="${escapeHtml(cat.name)}">[${cat.type}] ${escapeHtml(cat.name)}</option>`;
        });
        document.getElementById('categoryDatalist').innerHTML = html;
    }

    // Date changes trigger reload
    dateInput.addEventListener('change', function() {
        loadFinanceData();
    });

    // Post filter dropdown changes
    filterPost.addEventListener('change', function() {
        activePostFilter = this.value;
        document.querySelectorAll('.post-card').forEach(card => {
            if (card.dataset.post === activePostFilter) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });
        renderTransactions();
    });

    async function loadFinanceData() {
        const date = dateInput.value;
        const listContainer = document.getElementById('transactionsList');
        listContainer.innerHTML = `<div class="elegant-loader" style="margin:20px auto;"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>`;

        try {
            if (!navigator.onLine && typeof OfflineDB !== 'undefined') {
                const allLogs = await OfflineDB.getAllFinanceLogs();
                const pastLogs = allLogs.filter(log => log.log_date <= date);
                const todayLogs = pastLogs.filter(log => log.log_date === date);
                currentLogs = todayLogs;

                const summary = { income: 0, expense: 0, net: 0 };
                const breakdown = {};
                accountsData.forEach(a => breakdown[a.name] = { income: 0, expense: 0, net: 0 });

                pastLogs.forEach(log => {
                    const amt = parseFloat(log.amount) || 0;
                    const pos = log.balance_type;
                    if (!breakdown[pos]) breakdown[pos] = { income: 0, expense: 0, net: 0 };
                    
                    if (log.category === 'Pemasukan') {
                        summary.net += amt;
                        breakdown[pos].net += amt;
                        if (log.log_date === date) {
                            summary.income += amt;
                            breakdown[pos].income += amt;
                        }
                    } else if (log.category === 'Pengeluaran') {
                        summary.net -= amt;
                        breakdown[pos].net -= amt;
                        if (log.log_date === date) {
                            summary.expense += amt;
                            breakdown[pos].expense += amt;
                        }
                    }
                });
                
                currentBreakdown = breakdown;
                updateSummaryUI(summary, breakdown);
                renderTransactions();
            } else {
                // 1. Fetch Summary
                const summaryRes = await api(`${BASE_URL}api/finance/summary?date=${date}`);
                if (summaryRes.success) {
                    currentBreakdown = summaryRes.breakdown;
                    updateSummaryUI(summaryRes.summary, summaryRes.breakdown);
                }

                // 2. Fetch Logs
                const logsRes = await api(`${BASE_URL}api/finance/logs?date=${date}`);
                if (logsRes.success) {
                    currentLogs = logsRes.logs;
                    renderTransactions();
                } else {
                    listContainer.innerHTML = `<div class="empty-state" style="padding:30px 10px;"><i class="bi bi-wallet2" style="font-size:2rem;color:var(--text-muted);opacity:0.5;"></i><h3>Gagal Memuat Data</h3><p>Coba refresh halaman ini.</p></div>`;
                }
            }
        } catch (e) {
            listContainer.innerHTML = `<div class="empty-state" style="padding:30px 10px;"><i class="bi bi-exclamation-circle" style="font-size:2rem;color:var(--primary);opacity:0.7;"></i><h3>Gagal Memuat Data</h3><p>${e.message || 'Periksa koneksi internet Anda.'}</p></div>`;
            showToast(e.message, 'error');
        }
    }

    function updateSummaryUI(summary, breakdown) {
        const net = summary.net;
        document.getElementById('netBalanceValue').innerText = formatRupiah(net);
        document.getElementById('totalIncomeValue').innerText = formatRupiah(summary.income);
        document.getElementById('totalExpenseValue').innerText = formatRupiah(summary.expense);

        // Net Badge Status
        const badge = document.getElementById('netStatusBadge');
        if (net > 0) {
            badge.innerText = 'SURPLUS';
            badge.className = 'badge-custom badge-success';
            badge.style.backgroundColor = 'rgba(46, 196, 182, 0.15)';
            badge.style.color = 'var(--success)';
        } else if (net < 0) {
            badge.innerText = 'DEFISIT';
            badge.className = 'badge-custom badge-danger';
            badge.style.backgroundColor = 'rgba(230, 57, 70, 0.15)';
            badge.style.color = 'var(--primary)';
        } else {
            badge.innerText = 'SEIMBANG';
            badge.className = 'badge-custom';
            badge.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
            badge.style.color = 'var(--text-muted)';
        }

        // Progress Bar
        const total = summary.income + summary.expense;
        let incPct = 50, expPct = 50;
        if (total > 0) {
            incPct = (summary.income / total) * 100;
            expPct = (summary.expense / total) * 100;
        } else {
            incPct = 0; expPct = 0;
        }

        document.getElementById('incomeBar').style.width = `${incPct}%`;
        document.getElementById('expenseBar').style.width = `${expPct}%`;
        document.getElementById('incomePercentage').innerText = `Pemasukan: ${Math.round(incPct)}%`;
        document.getElementById('expensePercentage').innerText = `Pengeluaran: ${Math.round(expPct)}%`;

        // Update Dynamic Pos Cards
        accountsData.forEach((acc, index) => {
            const shortId = `pos_${acc.id}`;
            const postData = breakdown[acc.name] || { income: 0, expense: 0, net: 0 };
            
            const netEl = document.getElementById(`net_${shortId}`);
            const incEl = document.getElementById(`inc_${shortId}`);
            const expEl = document.getElementById(`exp_${shortId}`);
            
            if(netEl) netEl.innerText = formatRupiah(postData.net);
            if(incEl) incEl.innerText = formatRupiah(postData.income);
            if(expEl) expEl.innerText = formatRupiah(postData.expense);
        });
    }

    function renderTransactions() {
        const container = document.getElementById('transactionsList');
        let filtered = currentLogs;
        
        if (activePostFilter) {
            filtered = currentLogs.filter(log => log.balance_type === activePostFilter);
        }

        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="empty-state" style="padding: 30px 10px;">
                    <i class="bi bi-wallet2" style="font-size: 2rem; color: var(--text-muted); opacity: 0.5;"></i>
                    <h3>Belum Ada Transaksi</h3>
                    <p>${activePostFilter ? `Tidak ada catatan di pos ${activePostFilter}` : 'Mulai catat pemasukan atau pengeluaran'}</p>
                </div>
            `;
            updateBulkActionBar();
            return;
        }

        // Grouping: Date -> Category (Pemasukan/Pengeluaran) -> Balance Type (POS Keuangan)
        let grouped = {};
        filtered.forEach(log => {
            const dateStr = log.log_date || 'Tanggal Tidak Diketahui';
            const typeStr = log.category || 'Lainnya';
            const posStr = log.balance_type || 'Lainnya';
            
            if(!grouped[dateStr]) grouped[dateStr] = {};
            if(!grouped[dateStr][typeStr]) grouped[dateStr][typeStr] = {};
            if(!grouped[dateStr][typeStr][posStr]) grouped[dateStr][typeStr][posStr] = [];
            
            grouped[dateStr][typeStr][posStr].push(log);
        });

        let html = '';
        let dateIndex = 0;

        // Iterate through Date
        for (const [dateStr, types] of Object.entries(grouped)) {
            dateIndex++;
            html += `
                <div style="margin-bottom: 20px;">
                    <div onclick="document.getElementById('group_date_${dateIndex}').style.display = document.getElementById('group_date_${dateIndex}').style.display === 'none' ? 'block' : 'none';" style="background: var(--surface-2); padding: 8px 14px; border-radius: var(--radius-md); font-weight: 800; font-size: var(--font-size-sm); color: var(--text-primary); margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                        <div style="display:flex; gap:8px; align-items:center;"><i class="bi bi-calendar-event"></i> ${dateStr}</div>
                        <i class="bi bi-chevron-expand"></i>
                    </div>
                    <div id="group_date_${dateIndex}">
            `;

            let typeIndex = 0;
            // Iterate through Type (Pemasukan / Pengeluaran)
            for (const [typeStr, poses] of Object.entries(types)) {
                typeIndex++;
                const isIncome = typeStr === 'Pemasukan';
                const typeColor = isIncome ? 'var(--success)' : 'var(--primary)';
                const typeIcon = isIncome ? 'bi-arrow-down-circle-fill' : 'bi-arrow-up-circle-fill';
                
                html += `
                    <div style="margin-left: 10px; border-left: 2px solid ${typeColor}; padding-left: 12px; margin-bottom: 16px;">
                        <div onclick="document.getElementById('group_type_${dateIndex}_${typeIndex}').style.display = document.getElementById('group_type_${dateIndex}_${typeIndex}').style.display === 'none' ? 'block' : 'none';" style="font-weight: 700; font-size: var(--font-size-xs); color: ${typeColor}; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <div style="display:flex; gap:6px; align-items:center;"><i class="bi ${typeIcon}"></i> ${typeStr}</div>
                            <i class="bi bi-chevron-expand"></i>
                        </div>
                        <div id="group_type_${dateIndex}_${typeIndex}">
                `;

                let posIndex = 0;
                // Iterate through POS
                for (const [posStr, logs] of Object.entries(poses)) {
                    posIndex++;
                    let accIndex = accountsData.findIndex(a => a.name === posStr);
                    if(accIndex < 0) accIndex = 0;
                    const style = getPosStyle(accIndex);

                    html += `
                        <div style="margin-bottom: 12px;">
                            <div onclick="document.getElementById('group_pos_${dateIndex}_${typeIndex}_${posIndex}').style.display = document.getElementById('group_pos_${dateIndex}_${typeIndex}_${posIndex}').style.display === 'none' ? 'block' : 'none';" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                                <div style="display:flex; gap:6px; align-items:center;"><span style="display: inline-block; width: 6px; height: 6px; background: ${style.icon}; border-radius: 50%;"></span> ${posStr}</div>
                                <i class="bi bi-chevron-expand"></i>
                            </div>
                            <div id="group_pos_${dateIndex}_${typeIndex}_${posIndex}">
                    `;

                    // Render Logs
                    logs.forEach(log => {
                        const amount = parseFloat(log.amount);
                        const isSelectable = true; // FORCE UNLOCK: allow selecting system logs
                        const isChecked = selectedLogs.has(log.id);

                        html += `
                            <div class="product-card" style="margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; ${isChecked ? 'border-color: var(--primary);' : ''}">
                                <div style="display: flex; align-items: center; min-width: 0; flex: 1; gap: 12px;">
                                    <!-- Checkbox for selection -->
                                    <div style="flex-shrink: 0;">
                                        <input type="checkbox" class="form-check-input" ${isSelectable ? '' : 'disabled'} ${isChecked ? 'checked' : ''} onchange="toggleLogSelection(${log.id}, this)" style="width: 1.2em; height: 1.2em; cursor: ${isSelectable ? 'pointer' : 'not-allowed'};">
                                    </div>

                                    <div style="min-width: 0; flex: 1;">
                                        <div style="font-weight: 700; font-size: var(--font-size-sm); color: var(--text-primary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                            ${escapeHtml(log.detail)}
                                        </div>
                                        ${log.description ? `<div style="font-size: 10px; color: var(--text-muted); font-style: italic; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; margin-top: 2px;" title="${escapeHtml(log.description)}">${escapeHtml(log.description)}</div>` : ''}
                                    </div>
                                </div>
                                
                                <div style="text-align: right; margin-left: 12px; flex-shrink: 0; display: flex; align-items: center; gap: 10px;">
                                    <div>
                                        <div style="font-weight: 800; font-size: var(--font-size-sm); color: ${typeColor};">
                                            ${isIncome ? '+' : '-'} ${formatRupiah(amount)}
                                        </div>
                                        <div style="font-size: 9px; color: var(--text-muted); margin-top: 2px;">
                                            ${log.reference_type === 'auto_conversion' ? `<span style="color: var(--info); font-weight:600;"><i class="bi bi-arrow-repeat"></i> AUTO</span>` : (log.reference_type ? `<span style="color: var(--info); font-weight:600;"><i class="bi bi-link-45deg"></i> POS</span>` : 'Manual')}
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        ${!log.reference_type ? `
                                        <button onclick="editLog(${JSON.stringify(log).replace(/"/g, '&quot;')})" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 2px; font-size: 13px;" title="Ubah">
                                            <i class="bi bi-pencil-square" style="color: var(--info);"></i>
                                        </button>
                                        ` : ''}
                                        <button onclick="deleteLog(${log.id})" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 2px; font-size: 13px;" title="Hapus (Force)">
                                            <i class="bi bi-trash-fill" style="color: var(--primary);"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div></div>`; // Close POS group and POS wrapper
                }
                html += `</div></div>`; // Close Type group and Type wrapper
            }
            html += `</div></div>`; // Close Date group and Date wrapper
        }
        
        container.innerHTML = html;
        updateBulkActionBar();
    }

    // Bulk Selection Logic
    window.toggleLogSelection = function(id, el) {
        if (el.checked) {
            selectedLogs.add(id);
            el.closest('.product-card').style.borderColor = 'var(--primary)';
        } else {
            selectedLogs.delete(id);
            el.closest('.product-card').style.borderColor = '';
        }
        updateBulkActionBar();
    };

    window.updateBalanceInfo = function(val, containerId, category, amount) {
        const infoEl = document.getElementById(containerId);
        if (!infoEl) return;
        if (currentBreakdown[val]) {
            const currentNet = currentBreakdown[val].net;
            let html = `<span style="display:block;"><i class="bi bi-wallet2" style="margin-right:4px;"></i>Saldo Saat Ini: <strong>${formatRupiah(currentNet)}</strong></span>`;
            const amt = parseFloat(amount);
            if (!isNaN(amt) && amt > 0 && category) {
                const updated = category === 'Pemasukan' ? currentNet + amt : currentNet - amt;
                const color = updated >= 0 ? 'var(--success)' : 'var(--primary)';
                const icon = category === 'Pemasukan' ? 'bi-arrow-up-circle-fill' : 'bi-arrow-down-circle-fill';
                html += `<span style="display:block; margin-top:4px; color:${color};"><i class="bi ${icon}" style="margin-right:4px;"></i>Saldo Setelah Transaksi: <strong>${formatRupiah(updated)}</strong></span>`;
            }
            infoEl.innerHTML = html;
            infoEl.style.display = 'block';
        } else {
            infoEl.style.display = 'none';
        }
    };

    window.clearSelection = function() {
        selectedLogs.clear();
        renderTransactions();
    };

    window.updateBulkActionBar = function() {
        const bar = document.getElementById('bulkActionBar');
        const countText = document.getElementById('selectedCountText');
        if (selectedLogs.size > 0) {
            countText.innerText = selectedLogs.size;
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }
    };

    window.bulkDeleteSelected = async function() {
        if (selectedLogs.size === 0) return;
        
        const confirmed = await AppModal.confirm(
            'Konfirmasi Hapus',
            `Yakin ingin menghapus ${selectedLogs.size} transaksi terpilih?`,
            'Ya, Hapus'
        );
        if (!confirmed) return;

        try {
            const idsArray = Array.from(selectedLogs);
            const res = await api(`${BASE_URL}api/finance/logs/bulk-delete`, 'POST', {
                csrf_token: csrfVal,
                ids: idsArray
            });

            if (res.success) {
                showToast(`${selectedLogs.size} transaksi berhasil dihapus`, 'success');
                selectedLogs.clear();
                loadFinanceData();
            }
        } catch (e) {
            showToast(e.message, 'error');
        }
    };

    // Modal untuk Kelola POS Keuangan
    window.manageAccounts = async function() {
        const html = /* html */ `
            <style>
                .compact-searchbox .searchbox-trigger { min-height: 32px !important; padding: 6px 10px !important; }
                .compact-searchbox .sb-value { font-size: 11px !important; }
            </style>
            <div style="margin-bottom: 15px; background: var(--surface-2); padding: 12px; border-radius: var(--radius-md);">
                <div style="margin-bottom: 8px; font-weight: 600; font-size: 12px;">Tambah POS Baru</div>
                <div style="display:flex; flex-direction:column; gap: 8px; margin-bottom: 8px;">
                    <input type="text" id="newAccountName" class="form-control-dark" placeholder="Nama POS (misal: Uang Gas)" style="width: 100%;" />
                    <div style="display:flex; gap: 8px; align-items:center;">
                        <div id="newAccountDepTypeContainer" class="compact-searchbox" style="flex: 1;"></div>
                        <div id="newAccountDepTargetContainer" class="compact-searchbox" style="display: none; flex:1;"></div>
                        <button class="btn-primary-custom" onclick="saveNewAccount()" style="padding: 6px 12px; border-radius:var(--radius-md); font-size: 11px; white-space:nowrap;"><i class="bi bi-plus-lg"></i> Tambah</button>
                    </div>
                </div>
            </div>
            <div style="font-size: 10px; color: var(--info); margin-bottom: 15px;"><i class="bi bi-info-circle"></i> Jika "Dependent" dipilih, maka pengeluaran dari POS tersebut akan otomatis tercatat juga sebagai pemasukan+pengeluaran di pos tujuan tanpa memotong saldo aslinya.</div>
            <div style="max-height: 300px; overflow-y: auto; background: var(--surface-2); border-radius: var(--radius-md); padding: 10px;">
                ${accountsData.map(acc => `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid var(--border-color);">
                        <div style="flex: 1;">
                            <div style="display: flex; gap: 5px; align-items: center;">
                                <input type="text" id="editAccName_${acc.id}" value="${escapeHtml(acc.name)}" class="form-control-dark" style="font-size: 12px; padding: 4px; height: auto;" />
                            </div>
                            <div style="display: flex; gap: 5px; margin-top: 4px; align-items: center; width: 100%;">
                                <span style="font-size: 9px; color: var(--text-muted); width: 30px;">Sifat:</span>
                                <div id="editAccDepContainer_${acc.id}" class="compact-searchbox" style="flex: 1; max-width: 250px;"></div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 5px; margin-left: 10px;">
                            <button onclick="updateAccount(${acc.id})" style="background: transparent; border: none; color: var(--info); padding: 4px;" title="Simpan Perubahan"><i class="bi bi-save"></i></button>
                            <button onclick="deleteAccount(${acc.id})" style="background: transparent; border: none; color: var(--primary); padding: 4px;" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        const modalPromise = AppModal.show({
            title: 'Kelola POS Keuangan',
            subtitle: 'Tambah, ubah nama, hapus, atau atur konversi otomatis',
            icon: 'bi-gear-fill',
            bodyHTML: html,
            hideSubmit: true,
            cancelText: 'Tutup'
        });

        new SearchBox(document.getElementById('newAccountDepTypeContainer'), {
            options: [
                {value: 'independent', label: 'Independent'},
                {value: 'dependent', label: 'Dependent'}
            ],
            value: 'independent',
            name: 'newAccountDepType',
            onChange: (val) => {
                document.getElementById('newAccountDepTargetContainer').style.display = val === 'dependent' ? 'block' : 'none';
            }
        });
        new SearchBox(document.getElementById('newAccountDepTargetContainer'), {
            options: accountsData.map(acc => ({value: acc.id, label: acc.name})),
            placeholder: '-- Pilih Tujuan --',
            name: 'newAccountDepTarget'
        });
        
        accountsData.forEach(acc => {
            const options = [{value: '', label: 'Independent'}];
            accountsData.forEach(d => {
                if (d.id !== acc.id) {
                    options.push({value: d.id, label: 'Dependent ke: ' + d.name});
                }
            });
            new SearchBox(document.getElementById(`editAccDepContainer_${acc.id}`), {
                options: options,
                value: acc.dependency_account_id || '',
                name: `editAccDep_${acc.id}`,
                placeholder: 'Sifat'
            });
        });
        
        await modalPromise;
    };

    window.saveNewAccount = async function() {
        const name = document.getElementById('newAccountName').value.trim();
        const type = document.querySelector('input[name="newAccountDepType"]').value;
        let depId = null;
        if (type === 'dependent') {
            depId = document.querySelector('input[name="newAccountDepTarget"]').value;
        }
        
        if(!name) return;
        try {
            const res = await api(`${BASE_URL}api/finance/accounts`, 'POST', { csrf_token: csrfVal, name: name, dependency_account_id: depId });
            if(res.success) {
                showToast("Berhasil ditambahkan", "success");
                await loadMasterData();
                AppModal.close();
                setTimeout(() => manageAccounts(), 300);
            }
        } catch(e) { showToast(e.message, 'error'); }
    };

    window.updateAccount = async function(id) {
        try {
            const name = document.getElementById(`editAccName_${id}`).value.trim();
            const depInput = document.querySelector(`input[name="editAccDep_${id}"]`);
            const depId = depInput ? depInput.value : '';
            
            const res = await api(`${BASE_URL}api/finance/accounts/${id}/update`, 'POST', { 
                csrf_token: csrfVal, 
                name: name,
                dependency_account_id: depId || null
            });
            if(res.success) {
                showToast("Berhasil diperbarui", "success");
                await loadMasterData();
            }
        } catch(e) { showToast(e.message, 'error'); }
    };

    window.deleteAccount = async function(id) {
        const confirmed = await AppModal.confirm(
            'Hapus POS Keuangan',
            'Yakin ingin menghapus pos keuangan ini?',
            'Ya, Hapus'
        );
        if(!confirmed) return;
        
        try {
            const res = await api(`${BASE_URL}api/finance/accounts/${id}/delete`, 'POST', { csrf_token: csrfVal });
            if(res.success) {
                showToast("Dihapus", "success");
                await loadMasterData();
                AppModal.close();
                setTimeout(() => manageAccounts(), 300);
            }
        } catch(e) { showToast(e.message, 'error'); }
    };

    window.showAddLogModal = async function() {
        // Ensure master data is loaded before showing modal
        if (!accountsData || accountsData.length === 0) {
            showToast('Tunggu, data POS Keuangan masih dimuat...', 'info');
            await loadMasterData();
            if (!accountsData || accountsData.length === 0) {
                showToast('Gagal memuat data POS Keuangan', 'error');
                return;
            }
        }

        // Always wait for the latest server refresh (if in-progress) so dropdown has all accounts
        if (masterDataRefreshPromise) {
            try { await masterDataRefreshPromise; } catch (e) { /* ignore, cached data still usable */ }
            masterDataRefreshPromise = null;
        } else if (navigator.onLine) {
            // If no pending refresh, do a fresh one now to get latest accounts
            await refreshMasterDataFromServer().catch(e => console.error('Refresh failed:', e));
        }

        const currentDate = dateInput.value;
        
        // Build Select Options for POS
        let posOptions = '';
        accountsData.forEach(acc => {
            posOptions += `<option value="${escapeHtml(acc.name)}">${escapeHtml(acc.name)}</option>`;
        });

        const html = `
            <div class="modal-form-group">
                <label style="margin-bottom: 4px; display: block;">Jenis Transaksi *</label>
                <div id="logCategoryContainer"></div>
            </div>
            
            <div class="modal-form-group">
                <label style="margin-bottom: 4px; display: block;">Pos Keuangan *</label>
                <div id="logBalanceTypeContainer"></div>
                <div id="logBalanceInfo" style="font-size: 11px; color: var(--info); margin-top: 6px; font-weight: 600; display: none;"></div>
            </div>

            <div class="modal-form-group">
                <label style="margin-bottom: 4px; display: block;">Kategori Transaksi *</label>
                <div id="logDetailContainer"></div>
            </div>

            <div class="modal-form-group">
                <label>Nominal (Rp) *</label>
                <input type="number" id="logAmount" class="form-control-dark" placeholder="Cth: 20000" min="1">
            </div>

            <div class="modal-form-group">
                <label>Tanggal *</label>
                <input type="date" id="logDate" class="form-control-dark" value="${currentDate}">
            </div>

            <div class="modal-form-group">
                <label>Keterangan Tambahan (Opsional)</label>
                <textarea id="logDescription" class="form-control-dark" rows="2" placeholder="Detail tambahan..."></textarea>
            </div>
        `;

        const modalPromise = AppModal.show({
            title: 'Catat Transaksi Keuangan',
            subtitle: 'Tambahkan pemasukan atau pengeluaran harian',
            icon: 'bi-wallet2',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: html,
            submitText: 'Simpan Catatan',
            onSubmit: async () => {
                const cat = document.querySelector('input[name="logCategory"]').value;
                const pos = document.querySelector('input[name="logBalanceType"]').value;
                const amt = parseFloat(document.getElementById('logAmount').value);
                const date = document.getElementById('logDate').value;
                const detail = document.querySelector('input[name="logDetail"]').value.trim();
                const desc = document.getElementById('logDescription').value.trim();

                if (isNaN(amt) || amt <= 0) {
                    showToast('Nominal transaksi wajib diisi dan valid', 'warning');
                    return false;
                }
                if (!date) {
                    showToast('Tanggal transaksi wajib diisi', 'warning');
                    return false;
                }
                if (!detail) {
                    showToast('Detail kategori transaksi wajib diisi', 'warning');
                    return false;
                }

                try {
                    // Check if category exists, if not, create it on the fly
                    const existingCat = categoriesData.find(c => c.name.toLowerCase() === detail.toLowerCase());
                    if (!existingCat && navigator.onLine) {
                        await api(`${BASE_URL}api/finance/categories`, 'POST', { csrf_token: csrfVal, name: detail, type: cat });
                        loadMasterData(); 
                    } else if (!existingCat) {
                        categoriesData.push({ id: Date.now(), name: detail, type: cat, _type: 'finance_cat' });
                    }

                    if (!navigator.onLine && typeof OfflineDB !== 'undefined') {
                        const fakeId = Date.now();
                        const newLog = {
                            id: fakeId,
                            category: cat,
                            balance_type: pos,
                            amount: amt,
                            log_date: date,
                            detail: detail,
                            description: desc,
                            period_yyyymm: date.substring(0,7).replace('-',''),
                            reference_type: null,
                            created_at: new Date().toISOString().replace('T',' ').substring(0,19)
                        };

                        await OfflineDB.addPendingChange('finance/logs', 'POST', {
                            csrf_token: csrfVal, category: cat, balance_type: pos, amount: amt, log_date: date, detail: detail, description: desc
                        });
                        
                        await OfflineDB.saveFinanceLog(newLog);
                        showToast('Transaksi disimpan offline', 'success');
                        
                        if (typeof updateSyncBadge === 'function') updateSyncBadge();

                        if (dateInput.value !== date) {
                            dateInput.value = date;
                        }
                        loadFinanceData();
                        return true;
                    }

                    const res = await api(`${BASE_URL}api/finance/logs`, 'POST', {
                        csrf_token: csrfVal,
                        category: cat,
                        balance_type: pos,
                        amount: amt,
                        log_date: date,
                        detail: detail,
                        description: desc
                    });

                    if (res.success) {
                        showToast(res.message || 'Transaksi berhasil disimpan', 'success');
                        if (dateInput.value !== date) {
                            dateInput.value = date;
                        }
                        loadFinanceData();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });

        function updateCategoryOptions(type) {
            let filtered = categoriesData.filter(c => c.type === type).map(c => ({value: c.name, label: c.name}));
            if (type === 'Pemasukan' && !filtered.find(c => c.value.toLowerCase() === 'omzet')) {
                filtered.unshift({value: 'Omzet', label: 'Omzet'});
            }
            if (type === 'Pengeluaran' && !filtered.find(c => c.value.toLowerCase() === 'belanja toko')) {
                filtered.unshift({value: 'Belanja Toko', label: 'Belanja Toko'});
            }
            logDetailBox.setOptions(filtered);
            if (type === 'Pemasukan') logDetailBox.setValue('Omzet', 'Omzet');
            else if (type === 'Pengeluaran') logDetailBox.setValue('Belanja Toko', 'Belanja Toko');
        }

        const logDetailBox = new SearchBox(document.getElementById('logDetailContainer'), {
            options: [],
            placeholder: '-- Pilih Kategori --',
            name: 'logDetail',
            onAdd: () => { AppModal.close(); setTimeout(() => manageCategories(), 300); },
            addLabel: 'Kelola Kategori',
            icon: 'bi-tags'
        });

        new SearchBox(document.getElementById('logCategoryContainer'), {
            options: [
                {value: 'Pemasukan', label: 'Pemasukan (Uang Masuk)'},
                {value: 'Pengeluaran', label: 'Pengeluaran (Uang Keluar)'}
            ],
            value: 'Pengeluaran',
            name: 'logCategory',
            onChange: (val) => {
                updateCategoryOptions(val);
                const pos = document.querySelector('input[name="logBalanceType"]')?.value;
                const amt = document.getElementById('logAmount')?.value;
                if (pos) updateBalanceInfo(pos, 'logBalanceInfo', val, amt);
            }
        });

        updateCategoryOptions('Pengeluaran');

        new SearchBox(document.getElementById('logBalanceTypeContainer'), {
            options: accountsData.map(acc => ({value: acc.name, label: acc.name})),
            placeholder: '-- Pilih Pos Keuangan --',
            name: 'logBalanceType',
            onAdd: () => { AppModal.close(); setTimeout(() => manageAccounts(), 300); },
            addLabel: 'Kelola POS Keuangan',
            icon: 'bi-wallet2',
            onChange: (val) => {
                const cat = document.querySelector('input[name="logCategory"]')?.value;
                const amt = document.getElementById('logAmount')?.value;
                updateBalanceInfo(val, 'logBalanceInfo', cat, amt);
            }
        });

        // Realtime update on amount change
        document.getElementById('logAmount')?.addEventListener('input', function() {
            const pos = document.querySelector('input[name="logBalanceType"]')?.value;
            const cat = document.querySelector('input[name="logCategory"]')?.value;
            if (pos) updateBalanceInfo(pos, 'logBalanceInfo', cat, this.value);
        });

        await modalPromise;
    };

    window.editLog = async function(log) {
        // Ensure we have the latest accounts data from server
        if (masterDataRefreshPromise) {
            try { await masterDataRefreshPromise; } catch (e) { /* ignore */ }
            masterDataRefreshPromise = null;
        } else if (navigator.onLine && (!accountsData || accountsData.length <= 3)) {
            await refreshMasterDataFromServer().catch(e => console.error('Refresh failed:', e));
        }

        let posOptions = '';
        accountsData.forEach(acc => {
            posOptions += `<option value="${escapeHtml(acc.name)}" ${log.balance_type === acc.name ? 'selected' : ''}>${escapeHtml(acc.name)}</option>`;
        });

        const html = `
            <div class="modal-form-group">
                <label style="margin-bottom: 4px; display: block;">Jenis Transaksi *</label>
                <div id="editLogCategoryContainer"></div>
            </div>
            
            <div class="modal-form-group">
                <label style="margin-bottom: 4px; display: block;">Pos Keuangan *</label>
                <div id="editLogBalanceTypeContainer"></div>
                <div id="editLogBalanceInfo" style="font-size: 11px; color: var(--info); margin-top: 6px; font-weight: 600; display: none;"></div>
            </div>

            <div class="modal-form-group">
                <label style="margin-bottom: 4px; display: block;">Kategori Transaksi *</label>
                <div id="editLogDetailContainer"></div>
            </div>

            <div class="modal-form-group">
                <label>Nominal (Rp) *</label>
                <input type="number" id="editLogAmount" class="form-control-dark" placeholder="Cth: 20000" min="1" value="${log.amount}">
            </div>

            <div class="modal-form-group">
                <label>Tanggal *</label>
                <input type="date" id="editLogDate" class="form-control-dark" value="${log.log_date}">
            </div>

            <div class="modal-form-group">
                <label>Keterangan Tambahan (Opsional)</label>
                <textarea id="editLogDescription" class="form-control-dark" rows="2" placeholder="Detail tambahan...">${escapeHtml(log.description || '')}</textarea>
            </div>
        `;

        const modalPromise = AppModal.show({
            title: 'Ubah Transaksi Keuangan',
            subtitle: 'Perbarui pencatatan pemasukan/pengeluaran',
            icon: 'bi-pencil-square',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: html,
            submitText: 'Perbarui Catatan',
            onSubmit: async () => {
                const cat = document.querySelector('input[name="editLogCategory"]').value;
                const pos = document.querySelector('input[name="editLogBalanceType"]').value;
                const amt = parseFloat(document.getElementById('editLogAmount').value);
                const date = document.getElementById('editLogDate').value;
                const detail = document.querySelector('input[name="editLogDetail"]').value.trim();
                const desc = document.getElementById('editLogDescription').value.trim();

                if (isNaN(amt) || amt <= 0) {
                    showToast('Nominal transaksi wajib diisi dan valid', 'warning');
                    return false;
                }
                if (!date) {
                    showToast('Tanggal transaksi wajib diisi', 'warning');
                    return false;
                }
                if (!detail) {
                    showToast('Detail/Jenis transaksi wajib diisi', 'warning');
                    return false;
                }

                try {
                    const existingCat = categoriesData.find(c => c.name.toLowerCase() === detail.toLowerCase());
                    if (!existingCat) {
                        await api(`${BASE_URL}api/finance/categories`, 'POST', { csrf_token: csrfVal, name: detail, type: cat });
                        loadMasterData(); 
                    }

                    const res = await api(`${BASE_URL}api/finance/logs/${log.id}/update`, 'POST', {
                        csrf_token: csrfVal,
                        category: cat,
                        balance_type: pos,
                        amount: amt,
                        log_date: date,
                        detail: detail,
                        description: desc
                    });

                    if (res.success) {
                        showToast(res.message || 'Transaksi berhasil diperbarui', 'success');
                        if (dateInput.value !== date) {
                            dateInput.value = date;
                        }
                        loadFinanceData();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });

        function updateEditCategoryOptions(type, initialValue = null) {
            let filtered = categoriesData.filter(c => c.type === type).map(c => ({value: c.name, label: c.name}));
            if (type === 'Pemasukan' && !filtered.find(c => c.value.toLowerCase() === 'omzet')) {
                filtered.unshift({value: 'Omzet', label: 'Omzet'});
            }
            if (type === 'Pengeluaran' && !filtered.find(c => c.value.toLowerCase() === 'belanja toko')) {
                filtered.unshift({value: 'Belanja Toko', label: 'Belanja Toko'});
            }
            editLogDetailBox.setOptions(filtered);
            if (initialValue) {
                editLogDetailBox.setValue(initialValue, initialValue);
            } else {
                if (type === 'Pemasukan') editLogDetailBox.setValue('Omzet', 'Omzet');
                else if (type === 'Pengeluaran') editLogDetailBox.setValue('Belanja Toko', 'Belanja Toko');
            }
        }

        const editLogDetailBox = new SearchBox(document.getElementById('editLogDetailContainer'), {
            options: [],
            placeholder: '-- Pilih Kategori --',
            name: 'editLogDetail',
            onAdd: () => { AppModal.close(); setTimeout(() => manageCategories(), 300); },
            addLabel: 'Kelola Kategori',
            icon: 'bi-tags'
        });

        new SearchBox(document.getElementById('editLogCategoryContainer'), {
            options: [
                {value: 'Pemasukan', label: 'Pemasukan (Uang Masuk)'},
                {value: 'Pengeluaran', label: 'Pengeluaran (Uang Keluar)'}
            ],
            value: log.category,
            name: 'editLogCategory',
            onChange: (val) => {
                updateEditCategoryOptions(val);
                const pos = document.querySelector('input[name="editLogBalanceType"]')?.value;
                const amt = document.getElementById('editLogAmount')?.value;
                if (pos) updateBalanceInfo(pos, 'editLogBalanceInfo', val, amt);
            }
        });

        updateEditCategoryOptions(log.category, log.detail);

        new SearchBox(document.getElementById('editLogBalanceTypeContainer'), {
            options: accountsData.map(acc => ({value: acc.name, label: acc.name})),
            placeholder: '-- Pilih Pos Keuangan --',
            name: 'editLogBalanceType',
            value: log.balance_type,
            onAdd: () => { AppModal.close(); setTimeout(() => manageAccounts(), 300); },
            addLabel: 'Kelola POS Keuangan',
            icon: 'bi-wallet2',
            onChange: (val) => {
                const cat = document.querySelector('input[name="editLogCategory"]')?.value;
                const amt = document.getElementById('editLogAmount')?.value;
                updateBalanceInfo(val, 'editLogBalanceInfo', cat, amt);
            }
        });
        updateBalanceInfo(log.balance_type, 'editLogBalanceInfo', log.category, log.amount);

        // Realtime update on amount change
        document.getElementById('editLogAmount')?.addEventListener('input', function() {
            const pos = document.querySelector('input[name="editLogBalanceType"]')?.value;
            const cat = document.querySelector('input[name="editLogCategory"]')?.value;
            if (pos) updateBalanceInfo(pos, 'editLogBalanceInfo', cat, this.value);
        });

        await modalPromise;
    };

    window.deleteLog = async function(id) {
        const confirmDelete = await AppModal.show({
            title: 'Konfirmasi Hapus',
            subtitle: 'Apakah Anda yakin ingin menghapus catatan keuangan ini?',
            icon: 'bi-trash-fill',
            iconColor: 'rgba(230, 57, 70, 0.15)',
            iconAccent: 'var(--primary)',
            bodyHTML: '<p style="text-align: center; color: var(--text-muted); font-size: var(--font-size-xs); margin: 10px 0;">Tindakan ini tidak dapat dibatalkan.</p>',
            submitText: 'Ya, Hapus',
            onSubmit: async () => {
                try {
                    const res = await api(`${BASE_URL}api/finance/logs/${id}/delete`, 'POST', {
                        csrf_token: csrfVal
                    });

                    if (res.success) {
                        showToast(res.message || 'Transaksi berhasil dihapus', 'success');
                        loadFinanceData();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });
    };

    // Modal untuk Kelola Kategori Transaksi
    window.manageCategories = async function() {
        const html = `
            <div style="margin-bottom: 15px; background: var(--surface-2); padding: 12px; border-radius: var(--radius-md);">
                <div style="margin-bottom: 8px; font-weight: 600; font-size: 12px;">Tambah Kategori Baru</div>
                <div style="display:flex; flex-direction:column; gap: 8px;">
                    <input type="text" id="newCatName" class="form-control-dark" placeholder="Nama Kategori (Misal: Uang Makan)" style="width: 100%;">
                    <div style="display:flex; gap: 8px; align-items:center;">
                        <div class="dropdown" style="flex:1;">
                            <button class="btn-dropdown-modern dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:6px 10px; font-size:11px;">
                                <span><i class="bi bi-arrow-down-circle-fill me-1 text-danger"></i>Pengeluaran</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:11px; min-width:100%;">
                                <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='Pemasukan'; dp.querySelector('button span').innerHTML='<i class=\'bi bi-arrow-up-circle-fill me-1 text-success\'></i>Pemasukan'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');"><i class="bi bi-arrow-up-circle-fill me-1 text-success"></i>Pemasukan</a></li>
                                <li><a class="dropdown-item active" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='Pengeluaran'; dp.querySelector('button span').innerHTML='<i class=\'bi bi-arrow-down-circle-fill me-1 text-danger\'></i>Pengeluaran'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');"><i class="bi bi-arrow-down-circle-fill me-1 text-danger"></i>Pengeluaran</a></li>
                            </ul>
                            <input type="hidden" id="newCatType" value="Pengeluaran">
                        </div>
                        <button class="btn-primary-custom" onclick="saveNewCategory()" style="padding: 6px 12px; border-radius:var(--radius-md); font-size: 11px; white-space:nowrap;"><i class="bi bi-plus-lg"></i> Tambah</button>
                    </div>
                </div>
            </div>
            <div style="max-height: 300px; overflow-y: auto; background: var(--surface-2); border-radius: var(--radius-md); padding: 10px;">
                ${categoriesData.map(cat => `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid var(--border-color);">
                        <div style="flex: 1; display:flex; gap: 5px; align-items: center;">
                            <div class="dropdown" style="width:auto;">
                                <button class="btn-dropdown-modern dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding:4px 8px; font-size:11px;">
                                    <span><i class="bi ${cat.type === 'Pemasukan' ? 'bi-arrow-up-circle-fill text-success' : 'bi-arrow-down-circle-fill text-danger'} me-1"></i>${cat.type}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:11px; min-width:100%;">
                                    <li><a class="dropdown-item ${cat.type === 'Pemasukan' ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='Pemasukan'; dp.querySelector('button span').innerHTML='<i class=\'bi bi-arrow-up-circle-fill text-success me-1\'></i>Pemasukan'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');"><i class="bi bi-arrow-up-circle-fill text-success me-1"></i>Pemasukan</a></li>
                                    <li><a class="dropdown-item ${cat.type === 'Pengeluaran' ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='Pengeluaran'; dp.querySelector('button span').innerHTML='<i class=\'bi bi-arrow-down-circle-fill text-danger me-1\'></i>Pengeluaran'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');"><i class="bi bi-arrow-down-circle-fill text-danger me-1"></i>Pengeluaran</a></li>
                                </ul>
                                <input type="hidden" id="editCatType_${cat.id}" value="${cat.type}">
                            </div>
                            <input type="text" id="editCatName_${cat.id}" value="${escapeHtml(cat.name)}" class="form-control-dark" style="font-size: 12px; padding: 4px; height: auto; flex:1;">
                        </div>
                        <div style="display: flex; gap: 5px; margin-left: 10px;">
                            <button onclick="updateCategory(${cat.id})" style="background: transparent; border: none; color: var(--info); padding: 4px;" title="Simpan Perubahan"><i class="bi bi-save"></i></button>
                            <button onclick="deleteCategory(${cat.id})" style="background: transparent; border: none; color: var(--primary); padding: 4px;" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        await AppModal.show({
            title: 'Kelola Kategori Transaksi',
            subtitle: 'Tambah, ubah nama, atau hapus kategori untuk form autocomplete',
            icon: 'bi-tags-fill',
            bodyHTML: html,
            hideSubmit: true,
            cancelText: 'Tutup'
        });
    };

    window.saveNewCategory = async function() {
        const type = document.getElementById('newCatType').value;
        const name = document.getElementById('newCatName').value.trim();
        if(!name) return;
        try {
            const res = await api(`${BASE_URL}api/finance/categories`, 'POST', { csrf_token: csrfVal, name: name, type: type });
            if(res.success) {
                showToast("Kategori ditambahkan", "success");
                await loadMasterData();
                AppModal.close();
                setTimeout(() => manageCategories(), 300);
            }
        } catch(e) { showToast(e.message, 'error'); }
    };

    window.updateCategory = async function(id) {
        try {
            const type = document.getElementById(`editCatType_${id}`).value;
            const name = document.getElementById(`editCatName_${id}`).value.trim();
            const res = await api(`${BASE_URL}api/finance/categories/${id}/update`, 'POST', { 
                csrf_token: csrfVal, 
                name: name,
                type: type 
            });
            if(res.success) {
                showToast("Kategori diperbarui", "success");
                await loadMasterData();
            }
        } catch(e) { showToast(e.message, 'error'); }
    };

    window.deleteCategory = async function(id) {
        const confirmed = await AppModal.confirm(
            'Hapus Kategori',
            'Yakin ingin menghapus kategori ini? (Riwayat transaksi tidak akan terhapus)',
            'Ya, Hapus'
        );
        if(!confirmed) return;
        
        try {
            const res = await api(`${BASE_URL}api/finance/categories/${id}/delete`, 'POST', { csrf_token: csrfVal });
            if(res.success) {
                showToast("Kategori dihapus", "success");
                await loadMasterData();
                AppModal.close();
                setTimeout(() => manageCategories(), 300);
            }
        } catch(e) { showToast(e.message, 'error'); }
    };

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Initialize Page at the very end so all functions are registered first
    await loadMasterData();
    loadFinanceData();

});
</script>

<!-- ============================================================
     OMZET ANALYTICS: CSS
============================================================= -->
<style>
.omzet-preset {
    background: var(--surface-2);
    color: var(--text-muted);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.omzet-preset.active,
.omzet-preset:hover {
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 3px 10px rgba(99,102,241,.35);
}
.omzet-kpi-card {
    background: var(--gradient-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    transition: transform .2s;
}
.omzet-kpi-card:hover { transform: translateY(-2px); }
.okpi-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    margin-bottom: 2px;
}
.okpi-label { font-size: 10px; color: var(--text-muted); font-weight: 600; }
.okpi-value { font-size: 15px; font-weight: 800; color: var(--text-primary); }
.okpi-badge {
    font-size: 10px; font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
    display: inline-block;
    width: fit-content;
}
.okpi-badge.up { background: rgba(16,185,129,.15); color: #10b981; }
.okpi-badge.down { background: rgba(239,68,68,.15); color: #ef4444; }
.okpi-badge.neutral { background: var(--surface-2); color: var(--text-muted); }
.omzet-insight-card {
    background: var(--gradient-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.oi-icon { font-size: 18px; margin-bottom: 2px; }
.oi-label { font-size: 10px; color: var(--text-muted); font-weight: 600; }
.oi-val { font-size: 13px; font-weight: 800; color: var(--text-primary); }
.oi-sub { font-size: 9px; color: var(--text-muted); }
</style>

<!-- ============================================================
     OMZET ANALYTICS: JavaScript
============================================================= -->
<script>
(function() {
    /* ---- PHP-injected constants ---- */
    const COMBINED_MARKUP   = <?= json_encode((float)$combinedMarkup) ?>;
    const MARKUP_ECER       = <?= json_encode((float)$markupEcer) ?>;
    const MARKUP_GROSIR     = <?= json_encode((float)$markupGrosir) ?>;
    const STORE_NAME        = <?= json_encode($storeName) ?>;
    const STORE_ADDRESS     = <?= json_encode($storeAddress) ?>;
    const STORE_PHONE       = <?= json_encode($storePhone) ?>;
    const LOGO_BASE64       = <?= json_encode($logoBase64) ?>;
    const MARGIN_RATIO      = COMBINED_MARKUP / (100 + COMBINED_MARKUP); // ~21.76%

    /* ---- State ---- */
    let omzetChart      = null;
    let currentAnalytics = null;
    let currentPreset   = 7; // default: 7 days
    let activeRange     = { start: '', end: '' };

    /* ---- Utilities ---- */
    function fmtRp(v) {
        if (v >= 1_000_000_000) return 'Rp ' + (v/1_000_000_000).toFixed(1) + 'M';
        if (v >= 1_000_000)     return 'Rp ' + (v/1_000_000).toFixed(1) + ' Jt';
        if (v >= 1_000)         return 'Rp ' + (v/1_000).toFixed(0) + 'rb';
        return 'Rp ' + Math.round(v).toLocaleString('id-ID');
    }
    function fmtRpFull(v) {
        return 'Rp ' + Math.round(v).toLocaleString('id-ID');
    }
    function fmtDate(ymd) {
        if (!ymd) return '';
        const d = new Date(ymd + 'T00:00:00');
        const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }
    function fmtDateShort(ymd) {
        if (!ymd) return '';
        const d = new Date(ymd + 'T00:00:00');
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return d.getDate() + ' ' + months[d.getMonth()];
    }
    function isoDate(d) { return d.toISOString().slice(0,10); }
    function growthBadge(pct) {
        if (pct === null || pct === undefined || isNaN(pct)) return { text: '—', cls: 'neutral' };
        const arrow = pct >= 0 ? '▲' : '▼';
        return { text: arrow + ' ' + Math.abs(pct).toFixed(1) + '%', cls: pct >= 0 ? 'up' : 'down' };
    }

    /* ---- Date preset helpers ---- */
    function getDateRange(preset) {
        const today = new Date();
        today.setHours(0,0,0,0);
        let start, end = isoDate(today);
        if (preset === 'bulan') {
            start = isoDate(new Date(today.getFullYear(), today.getMonth(), 1));
        } else if (preset === 'custom') {
            const s = document.getElementById('omzetStartDate')?.value;
            const e = document.getElementById('omzetEndDate')?.value;
            if (!s || !e) return null;
            return { start: s, end: e };
        } else {
            const d = new Date(today);
            d.setDate(today.getDate() - (parseInt(preset) - 1));
            start = isoDate(d);
        }
        return { start, end };
    }

    window.setOmzetPreset = function(preset, btn) {
        currentPreset = preset;
        document.querySelectorAll('.omzet-preset').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        const customBox = document.getElementById('omzetCustomRange');
        if (preset === 'custom') {
            customBox.style.display = 'block';
            const today = new Date();
            const start = new Date(today);
            start.setDate(today.getDate() - 6);
            document.getElementById('omzetStartDate').value = isoDate(start);
            document.getElementById('omzetEndDate').value   = isoDate(today);
        } else {
            customBox.style.display = 'none';
            const range = getDateRange(preset);
            if (range) loadOmzetAnalytics(range.start, range.end);
        }
    };

    window.applyOmzetCustomRange = function() {
        const s = document.getElementById('omzetStartDate')?.value;
        const e = document.getElementById('omzetEndDate')?.value;
        if (!s || !e) { if (typeof showToast === 'function') showToast('Pilih rentang tanggal terlebih dahulu', 'error'); return; }
        loadOmzetAnalytics(s, e);
    };

    /* ---- Main data load ---- */
    async function loadOmzetAnalytics(startDate, endDate) {
        activeRange = { start: startDate, end: endDate };
        const loader = document.getElementById('omzetChartLoader');
        const canvas = document.getElementById('omzetChart');
        if (loader) loader.style.display = 'block';
        if (canvas) canvas.style.display = 'none';

        try {
            const url = `${BASE_URL}api/finance/omzet-analytics?start_date=${startDate}&end_date=${endDate}`;
            const res = await fetch(url, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Gagal memuat data');
            currentAnalytics = data;
            renderOmzetKPI(data);
            renderOmzetChart(data);
            renderOmzetInsights(data);
        } catch(e) {
            if (typeof showToast === 'function') showToast('Gagal memuat data omzet: ' + e.message, 'error');
            if (loader) loader.style.display = 'none';
        }
    }

    /* ---- KPI Cards ---- */
    function renderOmzetKPI(data) {
        const s = data.summary;
        // Total Omzet
        document.getElementById('kpiOmzetVal').textContent = fmtRp(s.total_revenue);
        const omzetBadge = growthBadge(s.growth_revenue_pct);
        const bEl = document.getElementById('kpiOmzetBadge');
        bEl.textContent = omzetBadge.text;
        bEl.className = 'okpi-badge ' + omzetBadge.cls;

        // Est. Profit
        document.getElementById('kpiEstProfitVal').textContent = fmtRp(s.total_estimated_profit);
        const profitPct = s.total_revenue > 0 ? ((s.total_estimated_profit / s.total_revenue) * 100).toFixed(1) : 0;
        const pEl = document.getElementById('kpiEstProfitBadge');
        pEl.textContent = profitPct + '% margin';
        pEl.className = 'okpi-badge up';

        // Avg Daily
        document.getElementById('kpiAvgDailyVal').textContent = fmtRp(s.avg_daily_revenue);
        const adEl = document.getElementById('kpiAvgDailyBadge');
        adEl.textContent = 'AOV: ' + fmtRp(s.avg_basket_size);
        adEl.className = 'okpi-badge neutral';

        // Transactions
        document.getElementById('kpiTrxVal').textContent = s.total_transactions + ' trx';
        const txBadge = growthBadge(s.growth_tx_pct);
        const txEl = document.getElementById('kpiTrxBadge');
        txEl.textContent = txBadge.text;
        txEl.className = 'okpi-badge ' + txBadge.cls;

        // Progress bar
        const pct = s.total_revenue > 0 ? (s.total_estimated_profit / s.total_revenue * 100) : 0;
        document.getElementById('omzetBar').style.width = '100%';
        document.getElementById('profitBarOverlay').style.width = pct.toFixed(1) + '%';
        document.getElementById('omzetProgressPct').textContent = pct.toFixed(1) + '%';
        document.getElementById('omzetProgressLabel').textContent = fmtRp(s.total_estimated_profit) + ' Est.Profit';
    }

    /* ---- Chart ---- */
    function renderOmzetChart(data) {
        const loader = document.getElementById('omzetChartLoader');
        const canvas = document.getElementById('omzetChart');
        if (!canvas) return;

        if (loader) loader.style.display = 'none';
        canvas.style.display = 'block';

        const labels     = data.series.map(d => d.series_label || d.short_label || d.label);
        const revenues   = data.series.map(d => d.revenue);
        const estProfits = data.series.map(d => d.estimated_profit);
        const realProf   = data.series.map(d => d.realized_profit);

        if (omzetChart) { omzetChart.destroy(); }

        const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
        const gridCol = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
        const textCol = isDark ? 'rgba(255,255,255,0.5)'  : 'rgba(0,0,0,0.45)';

        omzetChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Omzet',
                        data: revenues,
                        backgroundColor: 'rgba(99,102,241,0.75)',
                        borderRadius: 5,
                        order: 2
                    },
                    {
                        label: 'Est. Profit',
                        data: estProfits,
                        type: 'line',
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        borderWidth: 2.5,
                        pointRadius: data.series.length <= 14 ? 4 : 2,
                        pointBackgroundColor: '#10b981',
                        fill: true,
                        tension: 0.4,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.95)',
                        titleFont: { size: 11, weight: '700' },
                        bodyFont: { size: 11 },
                        padding: 10,
                        callbacks: {
                            label: ctx => ' ' + ctx.dataset.label + ': ' + fmtRpFull(ctx.raw)
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textCol, font: { size: 9 }, maxRotation: 45, minRotation: data.series.length > 14 ? 45 : 0 }
                    },
                    y: {
                        grid: { color: gridCol },
                        ticks: {
                            color: textCol,
                            font: { size: 9 },
                            callback: v => fmtRp(v)
                        }
                    }
                }
            }
        });
    }

    /* ---- Insights ---- */
    function renderOmzetInsights(data) {
        const s = data.summary;

        // Peak day
        if (s.peak_day && s.peak_day.revenue > 0) {
            document.getElementById('insightPeakDay').textContent = s.peak_day.label || '—';
            document.getElementById('insightPeakRev').textContent = fmtRpFull(s.peak_day.revenue) + ' (' + (s.peak_day.tx_count || 0) + ' trx)';
        } else {
            document.getElementById('insightPeakDay').textContent = 'Belum ada data';
            document.getElementById('insightPeakRev').textContent = '';
        }

        // AOV
        document.getElementById('insightAOV').textContent = fmtRp(s.avg_basket_size);
        document.getElementById('insightAOVSub').textContent = s.total_transactions + ' transaksi total';

        // Growth
        const g = growthBadge(s.growth_revenue_pct);
        const gEl = document.getElementById('insightGrowth');
        gEl.textContent = g.text;
        gEl.style.color = s.growth_revenue_pct >= 0 ? '#10b981' : '#ef4444';
        document.getElementById('insightGrowthSub').textContent = 'vs periode sebelumnya (' + fmtRp(s.prev_revenue) + ')';

        // Margin
        const margin = s.total_revenue > 0 ? (s.total_estimated_profit / s.total_revenue * 100).toFixed(2) : 0;
        document.getElementById('insightMargin').textContent = margin + '%';
        document.getElementById('insightMarginSub').textContent = 'Markup +' + COMBINED_MARKUP.toFixed(1) + '% (Gabungan Ecer+Grosir)';
    }

    /* ================================================================
       PDF REPORT GENERATOR
    ================================================================= */
    window.downloadLaporanPDF = async function() {
        if (!currentAnalytics) {
            if (typeof showToast === 'function') showToast('Data belum dimuat. Tunggu sebentar.', 'error');
            return;
        }

        const btn = document.getElementById('btnDownloadLaporanPDF');
        const origHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generating...';
        btn.disabled = true;

        try {
            const data = currentAnalytics;
            const s = data.summary;
            const series = data.series;

            // Capture chart as base64 image
            let chartImgSrc = '';
            const chartCanvas = document.getElementById('omzetChart');
            if (chartCanvas && chartCanvas.style.display !== 'none') {
                chartImgSrc = chartCanvas.toDataURL('image/png', 0.95);
            }

            // Date formatting
            const startFmt = fmtDateShort(data.start_date);
            const endFmt   = fmtDateShort(data.end_date) + ' ' + new Date(data.end_date + 'T00:00:00').getFullYear();
            const printedAt = fmtDate(new Date().toISOString().slice(0,10)) + ', ' + new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'});
            const fileName  = 'Laporan_Omzet_AlfarezMart_' + data.start_date + '_sd_' + data.end_date + '.pdf';

            // --- Build daily rows ---
            const margin = s.total_revenue > 0 ? (s.total_estimated_profit / s.total_revenue * 100).toFixed(1) : 0;
            const rowsHtml = series.map((d, i) => {
                const isZero = d.revenue === 0;
                const bg = i % 2 === 0 ? '#f9fafb' : '#ffffff';
                const revFmt   = fmtRpFull(d.revenue);
                const estFmt   = fmtRpFull(d.estimated_profit);
                const realFmt  = fmtRpFull(d.realized_profit);
                const marginD  = d.revenue > 0 ? (d.estimated_profit / d.revenue * 100).toFixed(1) + '%' : '—';
                const trxCount = d.transactions;
                const isPeak   = s.peak_day && s.peak_day.date === d.date;
                return `
                <tr style="background:${isPeak ? '#eff6ff' : bg};">
                    <td style="padding:7px 10px; font-size:10px; color:#374151; white-space:nowrap; border-bottom:1px solid #f3f4f6;">
                        ${isPeak ? '🏆 ' : ''}${d.full_label || d.label}
                    </td>
                    <td style="padding:7px 10px; font-size:10px; text-align:center; color:#6b7280; border-bottom:1px solid #f3f4f6;">${trxCount}</td>
                    <td style="padding:7px 10px; font-size:10px; text-align:right; font-weight:700; color:${isZero?'#9ca3af':'#111827'}; border-bottom:1px solid #f3f4f6;">${isZero ? '—' : revFmt}</td>
                    <td style="padding:7px 10px; font-size:10px; text-align:right; color:#059669; border-bottom:1px solid #f3f4f6;">${isZero ? '—' : estFmt}</td>
                    <td style="padding:7px 10px; font-size:10px; text-align:right; color:#7c3aed; border-bottom:1px solid #f3f4f6;">${isZero ? '—' : realFmt}</td>
                    <td style="padding:7px 10px; font-size:10px; text-align:center; color:#6b7280; border-bottom:1px solid #f3f4f6;">${isZero ? '—' : marginD}</td>
                </tr>`;
            }).join('');

            // --- Build PDF content ---
            const pdfContent = document.createElement('div');
            pdfContent.style.cssText = 'font-family: "Helvetica Neue", Arial, sans-serif; color: #111827; background: #fff; width: 794px; padding: 0;';

            pdfContent.innerHTML = `
            <!-- COVER HEADER -->
            <div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%); padding: 36px 40px 32px; color: #fff; border-radius: 0;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                    <div style="display:flex; align-items:center; gap:16px;">
                        ${LOGO_BASE64 ? `<img src="${LOGO_BASE64}" style="width:56px;height:56px;border-radius:12px;object-fit:contain;background:#fff;padding:4px;" />` : `<div style="width:56px;height:56px;border-radius:12px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:26px;">🛒</div>`}
                        <div>
                            <div style="font-size:22px; font-weight:800; letter-spacing:-.5px;">${STORE_NAME}</div>
                            <div style="font-size:11px; opacity:.75; margin-top:2px;">${STORE_ADDRESS || 'Toko Kelontong & Sembako'}</div>
                            ${STORE_PHONE ? `<div style="font-size:11px; opacity:.6;">${STORE_PHONE}</div>` : ''}
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:13px; font-weight:700; opacity:.85; text-transform:uppercase; letter-spacing:1px;">Laporan Omzet</div>
                        <div style="font-size:11px; opacity:.6; margin-top:4px;">Periode: ${startFmt} – ${endFmt}</div>
                        <div style="font-size:10px; opacity:.5; margin-top:2px;">Dicetak: ${printedAt}</div>
                    </div>
                </div>
                <!-- KPI Summary in Header -->
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">
                    <div style="background:rgba(255,255,255,.12); border-radius:10px; padding:12px 14px;">
                        <div style="font-size:9px; opacity:.7; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Total Omzet</div>
                        <div style="font-size:16px; font-weight:800;">${fmtRpFull(s.total_revenue)}</div>
                    </div>
                    <div style="background:rgba(255,255,255,.12); border-radius:10px; padding:12px 14px;">
                        <div style="font-size:9px; opacity:.7; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Est. Profit</div>
                        <div style="font-size:16px; font-weight:800; color:#6ee7b7;">${fmtRpFull(s.total_estimated_profit)}</div>
                        <div style="font-size:9px; opacity:.6;">Margin ${margin}%</div>
                    </div>
                    <div style="background:rgba(255,255,255,.12); border-radius:10px; padding:12px 14px;">
                        <div style="font-size:9px; opacity:.7; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Realisasi Profit</div>
                        <div style="font-size:16px; font-weight:800; color:#c4b5fd;">${fmtRpFull(s.total_realized_profit)}</div>
                    </div>
                    <div style="background:rgba(255,255,255,.12); border-radius:10px; padding:12px 14px;">
                        <div style="font-size:9px; opacity:.7; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Total Transaksi</div>
                        <div style="font-size:16px; font-weight:800;">${s.total_transactions}</div>
                        <div style="font-size:9px; opacity:.6;">AOV: ${fmtRpFull(s.avg_basket_size)}</div>
                    </div>
                </div>
            </div>

            <!-- BODY -->
            <div style="padding: 28px 40px;">

                <!-- Insights Row -->
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:28px;">
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:14px;">
                        <div style="font-size:9px; font-weight:700; color:#065f46; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px;">📈 Pertumbuhan Omzet</div>
                        <div style="font-size:18px; font-weight:800; color:${s.growth_revenue_pct >= 0 ? '#059669' : '#dc2626'};">${s.growth_revenue_pct >= 0 ? '▲' : '▼'} ${Math.abs(s.growth_revenue_pct).toFixed(1)}%</div>
                        <div style="font-size:9px; color:#6b7280; margin-top:3px;">vs periode sebelumnya (${fmtRpFull(s.prev_revenue)})</div>
                    </div>
                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px;">
                        <div style="font-size:9px; font-weight:700; color:#1e40af; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px;">🏆 Hari Puncak</div>
                        <div style="font-size:12px; font-weight:800; color:#1e3a8a;">${s.peak_day ? (s.peak_day.label || '—') : '—'}</div>
                        <div style="font-size:9px; color:#6b7280; margin-top:3px;">${s.peak_day ? fmtRpFull(s.peak_day.revenue) + ' (' + s.peak_day.tx_count + ' trx)' : '—'}</div>
                    </div>
                    <div style="background:#faf5ff; border:1px solid #e9d5ff; border-radius:10px; padding:14px;">
                        <div style="font-size:9px; font-weight:700; color:#6b21a8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px;">📊 Rata-rata Harian</div>
                        <div style="font-size:14px; font-weight:800; color:#4c1d95;">${fmtRpFull(s.avg_daily_revenue)}</div>
                        <div style="font-size:9px; color:#6b7280; margin-top:3px;">Est. Profit/hari: ${fmtRpFull(s.avg_daily_profit)}</div>
                    </div>
                </div>

                <!-- Markup Info -->
                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:12px 16px; margin-bottom:24px; display:flex; align-items:center; gap:14px;">
                    <div style="font-size:22px;">💡</div>
                    <div>
                        <div style="font-size:10px; font-weight:700; color:#92400e;">Dasar Perhitungan Estimasi Profit</div>
                        <div style="font-size:9px; color:#78350f; margin-top:2px;">
                            Markup Gabungan (Ecer+Grosir) = <strong>+${COMBINED_MARKUP.toFixed(2)}%</strong>
                            &nbsp;|&nbsp; Ecer: +${MARKUP_ECER.toFixed(1)}%
                            &nbsp;|&nbsp; Grosir: +${MARKUP_GROSIR.toFixed(1)}%
                            &nbsp;|&nbsp; Margin Revenue: ~${margin}%
                        </div>
                        <div style="font-size:9px; color:#a16207; margin-top:1px;">
                            Estimasi Profit = Omzet × (Markup / (100 + Markup)) — bukan termasuk biaya operasional
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                ${chartImgSrc ? `
                <div style="margin-bottom:24px;">
                    <div style="font-size:11px; font-weight:700; color:#374151; margin-bottom:8px; padding-bottom:6px; border-bottom:2px solid #e5e7eb;">
                        📊 Grafik Tren Omzet & Estimasi Profit
                    </div>
                    <img src="${chartImgSrc}" style="width:100%; border-radius:8px; border:1px solid #e5e7eb;" />
                </div>` : ''}

                <!-- Daily Breakdown Table -->
                <div style="margin-bottom:24px;">
                    <div style="font-size:11px; font-weight:700; color:#374151; margin-bottom:10px; padding-bottom:6px; border-bottom:2px solid #e5e7eb;">
                        📋 Rincian Harian
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:10px;">
                        <thead>
                            <tr style="background:linear-gradient(135deg,#1e1b4b,#4338ca); color:#fff;">
                                <th style="padding:9px 10px; text-align:left; font-weight:700; font-size:9px; border-radius:0;">Tanggal</th>
                                <th style="padding:9px 10px; text-align:center; font-weight:700; font-size:9px;">Trx</th>
                                <th style="padding:9px 10px; text-align:right; font-weight:700; font-size:9px;">Omzet</th>
                                <th style="padding:9px 10px; text-align:right; font-weight:700; font-size:9px;">Est. Profit</th>
                                <th style="padding:9px 10px; text-align:right; font-weight:700; font-size:9px;">Real. Profit</th>
                                <th style="padding:9px 10px; text-align:center; font-weight:700; font-size:9px;">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                        <tfoot>
                            <tr style="background:#f1f5f9; border-top:2px solid #cbd5e1;">
                                <td style="padding:9px 10px; font-weight:800; font-size:10px; color:#1e293b;">TOTAL (${data.days_count} hari)</td>
                                <td style="padding:9px 10px; text-align:center; font-weight:800; font-size:10px; color:#1e293b;">${s.total_transactions}</td>
                                <td style="padding:9px 10px; text-align:right; font-weight:800; font-size:10px; color:#1e293b;">${fmtRpFull(s.total_revenue)}</td>
                                <td style="padding:9px 10px; text-align:right; font-weight:800; font-size:10px; color:#059669;">${fmtRpFull(s.total_estimated_profit)}</td>
                                <td style="padding:9px 10px; text-align:right; font-weight:800; font-size:10px; color:#7c3aed;">${fmtRpFull(s.total_realized_profit)}</td>
                                <td style="padding:9px 10px; text-align:center; font-weight:700; font-size:10px; color:#1e293b;">${margin}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Footer -->
                <div style="border-top:1px solid #e5e7eb; padding-top:14px; display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:9px; color:#9ca3af;">
                        ${STORE_NAME} &nbsp;|&nbsp; Dicetak: ${printedAt}
                    </div>
                    <div style="font-size:9px; color:#9ca3af; text-align:right;">
                        Laporan ini dihasilkan otomatis oleh sistem AlfarezMart.<br>
                        Estimasi profit berdasarkan rata-rata markup katalog produk.
                    </div>
                </div>
            </div>
            `;

            document.body.appendChild(pdfContent);

            const opt = {
                margin:      [0, 0, 0, 0],
                filename:    fileName,
                image:       { type: 'jpeg', quality: 0.97 },
                html2canvas: { scale: 2, useCORS: true, logging: false, backgroundColor: '#ffffff' },
                jsPDF:       { unit: 'px', format: [794, 1123], orientation: 'portrait' },
                pagebreak:   { mode: ['css', 'legacy'] }
            };

            await html2pdf().set(opt).from(pdfContent).save();
            document.body.removeChild(pdfContent);

            if (typeof showToast === 'function') showToast('Laporan PDF berhasil diunduh!', 'success');
        } catch(e) {
            console.error('PDF error:', e);
            if (typeof showToast === 'function') showToast('Gagal generate PDF: ' + e.message, 'error');
        } finally {
            btn.innerHTML = origHtml;
            btn.disabled = false;
        }
    };

    /* ---- Init on page ready ---- */
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-load default 7 days
        const range = getDateRange(7);
        if (range) loadOmzetAnalytics(range.start, range.end);
    });

})();
</script>
