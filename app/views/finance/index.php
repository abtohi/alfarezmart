<?php
/**
 * Daily Finance Index View
 * 
 * @var string $csrfToken
 */
?>

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
            <select id="filterPost" class="form-select-dark-sm" style="width: auto; min-width: 120px;">
                <option value="">Semua Pos</option>
                <!-- Options akan digenerate disini -->
            </select>
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
            const accRes = await api(`${BASE_URL}api/finance/accounts`);
            if (accRes.success) accountsData = accRes.data;

            const catRes = await api(`${BASE_URL}api/finance/categories`);
            if (catRes.success) categoriesData = catRes.data;

            renderPosGrid();
            updateFilterOptions();
            updateCategoryDatalist();
        } catch (e) {
            console.error("Gagal memuat data master keuangan:", e);
        }
    }

    function renderPosGrid() {
        const container = document.getElementById('posGridContainer');
        if (!accountsData || accountsData.length === 0) {
            container.innerHTML = `<div style="grid-column: span 2; text-align:center; font-size:12px; color:var(--text-muted);">Belum ada POS Keuangan</div>`;
            return;
        }

        const allowedPos = ['Saldo Utama', 'Saldo Rokok', 'Saldo Pulsa'];
        let visibleAccounts = accountsData.filter(a => allowedPos.includes(a.name));

        let html = '';
        visibleAccounts.forEach((acc, index) => {
            const style = getPosStyle(index);
            const safeName = escapeHtml(acc.name);
            const shortId = `pos_${acc.id}`;
            
            html += `
            <div class="app-card post-card ${activePostFilter === acc.name ? 'active' : ''}" data-post="${safeName}" data-index="${index}" style="padding: 14px; position: relative; cursor: pointer; transition: transform 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: var(--font-size-xs); font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%;">${safeName}</span>
                    <div style="width: 24px; height: 24px; background: ${style.bg}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: ${style.icon}; flex-shrink: 0;">
                        <i class="bi ${style.bi}" style="font-size: 11px;"></i>
                    </div>
                </div>
                <div id="net_${shortId}" style="font-size: var(--font-size-sm); font-weight: 800; color: var(--text-primary);">Rp 0</div>
                <div style="font-size: 8px; color: var(--text-muted); margin-top: 4px; display: flex; justify-content: space-between;">
                    <span>Masuk: <span id="inc_${shortId}" style="color: var(--success);">Rp 0</span></span>
                    <span>Keluar: <span id="exp_${shortId}" style="color: var(--primary);">Rp 0</span></span>
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
        let html = '<option value="">Semua Pos</option>';
        accountsData.forEach(acc => {
            html += `<option value="${escapeHtml(acc.name)}" ${activePostFilter === acc.name ? 'selected' : ''}>${escapeHtml(acc.name)}</option>`;
        });
        filterPost.innerHTML = html;
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
                // Clear spinner if no success flag
                listContainer.innerHTML = `<div class="empty-state" style="padding:30px 10px;"><i class="bi bi-wallet2" style="font-size:2rem;color:var(--text-muted);opacity:0.5;"></i><h3>Gagal Memuat Data</h3><p>Coba refresh halaman ini.</p></div>`;
            }
        } catch (e) {
            // Always clear the spinner on error
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

    window.updateBalanceInfo = function(val, containerId) {
        const infoEl = document.getElementById(containerId);
        if (!infoEl) return;
        const allowed = ['Saldo Utama', 'Saldo Rokok', 'Saldo Pulsa'];
        if (allowed.includes(val) && currentBreakdown[val]) {
            infoEl.innerText = `Total Saldo Saat Ini: ${formatRupiah(currentBreakdown[val].net)}`;
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
                    if (!existingCat) {
                        await api(`${BASE_URL}api/finance/categories`, 'POST', { csrf_token: csrfVal, name: detail, type: cat });
                        // Reload categories silently in background
                        loadMasterData(); 
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
            onChange: (val) => { updateBalanceInfo(val, 'logBalanceInfo'); }
        });

        await modalPromise;
    };

    window.editLog = async function(log) {
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
            onChange: (val) => { updateBalanceInfo(val, 'editLogBalanceInfo'); }
        });
        updateBalanceInfo(log.balance_type, 'editLogBalanceInfo');

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
                        <select id="newCatType" class="form-select-dark" style="flex:1; font-size: 11px;">
                            <option value="Pemasukan">Pemasukan</option>
                            <option value="Pengeluaran" selected>Pengeluaran</option>
                        </select>
                        <button class="btn-primary-custom" onclick="saveNewCategory()" style="padding: 6px 12px; border-radius:var(--radius-md); font-size: 11px; white-space:nowrap;"><i class="bi bi-plus-lg"></i> Tambah</button>
                    </div>
                </div>
            </div>
            <div style="max-height: 300px; overflow-y: auto; background: var(--surface-2); border-radius: var(--radius-md); padding: 10px;">
                ${categoriesData.map(cat => `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid var(--border-color);">
                        <div style="flex: 1; display:flex; gap: 5px; align-items: center;">
                            <select id="editCatType_${cat.id}" class="form-select-dark" style="font-size: 11px; padding: 4px; width: auto; height: auto;">
                                <option value="Pemasukan" ${cat.type === 'Pemasukan' ? 'selected' : ''}>Pemasukan</option>
                                <option value="Pengeluaran" ${cat.type === 'Pengeluaran' ? 'selected' : ''}>Pengeluaran</option>
                            </select>
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
