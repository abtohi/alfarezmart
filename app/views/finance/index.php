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

    <!-- Grid 4 Pos Keuangan -->
    <div style="margin-bottom: 24px;">
        <div class="section-title" style="margin-bottom: 12px;">Sumber Keuangan (Pos)</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <!-- Pos Uang Laci -->
            <div class="app-card post-card" data-post="Uang Laci" style="padding: 14px; position: relative; cursor: pointer; transition: transform 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: var(--font-size-xs); font-weight: 700; color: var(--text-primary);">Uang Laci</span>
                    <div style="width: 24px; height: 24px; background: rgba(76, 201, 240, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #4cc9f0;">
                        <i class="bi bi-inbox" style="font-size: 11px;"></i>
                    </div>
                </div>
                <div id="netLaci" style="font-size: var(--font-size-sm); font-weight: 800; color: var(--text-primary);">Rp 0</div>
                <div style="font-size: 8px; color: var(--text-muted); margin-top: 4px; display: flex; justify-content: space-between;">
                    <span>Masuk: <span id="incLaci" style="color: var(--success);">Rp 0</span></span>
                    <span>Keluar: <span id="expLaci" style="color: var(--primary);">Rp 0</span></span>
                </div>
            </div>

            <!-- Pos Uang Pulsa -->
            <div class="app-card post-card" data-post="Uang Pulsa" style="padding: 14px; position: relative; cursor: pointer; transition: transform 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: var(--font-size-xs); font-weight: 700; color: var(--text-primary);">Uang Pulsa</span>
                    <div style="width: 24px; height: 24px; background: rgba(255, 183, 3, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffb703;">
                        <i class="bi bi-phone" style="font-size: 11px;"></i>
                    </div>
                </div>
                <div id="netPulsa" style="font-size: var(--font-size-sm); font-weight: 800; color: var(--text-primary);">Rp 0</div>
                <div style="font-size: 8px; color: var(--text-muted); margin-top: 4px; display: flex; justify-content: space-between;">
                    <span>Masuk: <span id="incPulsa" style="color: var(--success);">Rp 0</span></span>
                    <span>Keluar: <span id="expPulsa" style="color: var(--primary);">Rp 0</span></span>
                </div>
            </div>

            <!-- Pos Uang Beras -->
            <div class="app-card post-card" data-post="Uang Beras" style="padding: 14px; position: relative; cursor: pointer; transition: transform 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: var(--font-size-xs); font-weight: 700; color: var(--text-primary);">Uang Beras</span>
                    <div style="width: 24px; height: 24px; background: rgba(46, 196, 182, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #2ec4b6;">
                        <i class="bi bi-cup-hot" style="font-size: 11px;"></i>
                    </div>
                </div>
                <div id="netBeras" style="font-size: var(--font-size-sm); font-weight: 800; color: var(--text-primary);">Rp 0</div>
                <div style="font-size: 8px; color: var(--text-muted); margin-top: 4px; display: flex; justify-content: space-between;">
                    <span>Masuk: <span id="incBeras" style="color: var(--success);">Rp 0</span></span>
                    <span>Keluar: <span id="expBeras" style="color: var(--primary);">Rp 0</span></span>
                </div>
            </div>

            <!-- Pos Uang Rokok -->
            <div class="app-card post-card" data-post="Uang Rokok" style="padding: 14px; position: relative; cursor: pointer; transition: transform 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: var(--font-size-xs); font-weight: 700; color: var(--text-primary);">Uang Rokok</span>
                    <div style="width: 24px; height: 24px; background: rgba(230, 57, 70, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #e63946;">
                        <i class="bi bi-fire" style="font-size: 11px;"></i>
                    </div>
                </div>
                <div id="netRokok" style="font-size: var(--font-size-sm); font-weight: 800; color: var(--text-primary);">Rp 0</div>
                <div style="font-size: 8px; color: var(--text-muted); margin-top: 4px; display: flex; justify-content: space-between;">
                    <span>Masuk: <span id="incRokok" style="color: var(--success);">Rp 0</span></span>
                    <span>Keluar: <span id="expRokok" style="color: var(--primary);">Rp 0</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Transaction List -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div class="section-title" style="margin-bottom: 0;">Daftar Transaksi</div>
            <select id="filterPost" style="border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary); font-size: 11px; padding: 4px 8px; border-radius: 4px; outline: none;">
                <option value="">Semua Pos</option>
                <option value="Uang Laci">Uang Laci</option>
                <option value="Uang Pulsa">Uang Pulsa</option>
                <option value="Uang Beras">Uang Beras</option>
                <option value="Uang Rokok">Uang Rokok</option>
            </select>
        </div>

        <div id="transactionsList">
            <div class="elegant-loader" style="margin: 20px auto;">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfVal = document.getElementById('csrfToken').value;
    const dateInput = document.getElementById('selectedDate');
    const filterPost = document.getElementById('filterPost');
    
    let activePostFilter = '';
    let currentLogs = [];

    // Initialize Page
    loadFinanceData();

    // Date changes trigger reload
    dateInput.addEventListener('change', function() {
        loadFinanceData();
    });

    // Post filter dropdown changes
    filterPost.addEventListener('change', function() {
        activePostFilter = this.value;
        // Visual indicator on cards
        document.querySelectorAll('.post-card').forEach(card => {
            if (card.dataset.post === activePostFilter) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });
        renderTransactions();
    });

    // Clicking post card filters the list
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

    async function loadFinanceData() {
        const date = dateInput.value;
        const listContainer = document.getElementById('transactionsList');
        listContainer.innerHTML = `<div class="elegant-loader" style="margin:20px auto;"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>`;

        try {
            // 1. Fetch Summary
            const summaryRes = await api(`${BASE_URL}api/finance/summary?date=${date}`);
            if (summaryRes.success) {
                updateSummaryUI(summaryRes.summary, summaryRes.breakdown);
            }

            // 2. Fetch Logs
            const logsRes = await api(`${BASE_URL}api/finance/logs?date=${date}`);
            if (logsRes.success) {
                currentLogs = logsRes.logs;
                renderTransactions();
            }
        } catch (e) {
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
        let incPct = 50;
        let expPct = 50;
        if (total > 0) {
            incPct = (summary.income / total) * 100;
            expPct = (summary.expense / total) * 100;
        } else {
            incPct = 0;
            expPct = 0;
        }

        document.getElementById('incomeBar').style.width = `${incPct}%`;
        document.getElementById('expenseBar').style.width = `${expPct}%`;
        document.getElementById('incomePercentage').innerText = `Pemasukan: ${Math.round(incPct)}%`;
        document.getElementById('expensePercentage').innerText = `Pengeluaran: ${Math.round(expPct)}%`;

        // Update Pos Cards
        const posts = {
            'Uang Laci': 'Laci',
            'Uang Pulsa': 'Pulsa',
            'Uang Beras': 'Beras',
            'Uang Rokok': 'Rokok'
        };

        for (const [fullName, shortName] of Object.entries(posts)) {
            const postData = breakdown[fullName] || { income: 0, expense: 0, net: 0 };
            document.getElementById(`net${shortName}`).innerText = formatRupiah(postData.net);
            document.getElementById(`inc${shortName}`).innerText = formatRupiah(postData.income);
            document.getElementById(`exp${shortName}`).innerText = formatRupiah(postData.expense);
        }
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
            return;
        }

        let html = '';
        filtered.forEach(log => {
            const isIncome = log.category === 'Pemasukan';
            const amount = parseFloat(log.amount);
            
            // Format post classes/icons
            let badgeBg = 'var(--info-bg)';
            let badgeText = 'var(--info)';
            if (log.balance_type === 'Uang Pulsa') {
                badgeBg = 'rgba(255, 183, 3, 0.15)';
                badgeText = '#ffb703';
            } else if (log.balance_type === 'Uang Beras') {
                badgeBg = 'rgba(46, 196, 182, 0.15)';
                badgeText = '#2ec4b6';
            } else if (log.balance_type === 'Uang Rokok') {
                badgeBg = 'rgba(230, 57, 70, 0.15)';
                badgeText = '#e63946';
            }

            html += `
                <div class="product-card" style="margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; padding: 12px 14px;">
                    <div style="display: flex; align-items: center; min-width: 0; flex: 1; gap: 12px;">
                        <div class="product-icon" style="background: ${isIncome ? 'rgba(46, 196, 182, 0.15)' : 'rgba(230, 57, 70, 0.15)'}; color: ${isIncome ? 'var(--success)' : 'var(--primary)'}; flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="bi ${isIncome ? 'bi-plus-lg' : 'bi-dash-lg'}" style="font-size: 14px; font-weight: 800;"></i>
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-weight: 700; font-size: var(--font-size-sm); color: var(--text-primary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                ${escapeHtml(log.detail)}
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px; flex-wrap: wrap;">
                                <span class="badge-custom" style="background: ${badgeBg}; color: ${badgeText}; font-size: 9px; padding: 1px 6px;">
                                    ${log.balance_type}
                                </span>
                                ${log.description ? `<span style="font-size: 10px; color: var(--text-muted); font-style: italic; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;" title="${escapeHtml(log.description)}">${escapeHtml(log.description)}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <div style="text-align: right; margin-left: 12px; flex-shrink: 0; display: flex; align-items: center; gap: 10px;">
                        <div>
                            <div style="font-weight: 800; font-size: var(--font-size-sm); color: ${isIncome ? 'var(--success)' : 'var(--text-primary)'};">
                                ${isIncome ? '+' : '-'} ${formatRupiah(amount)}
                            </div>
                            <div style="font-size: 9px; color: var(--text-muted); margin-top: 2px;">
                                ${log.reference_type ? `<span style="color: var(--info); font-weight:600;"><i class="bi bi-link-45deg"></i> POS</span>` : 'Manual'}
                            </div>
                        </div>
                        
                        <!-- Actions (Only show for manual logs) -->
                        ${!log.reference_type ? `
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <button onclick="editLog(${JSON.stringify(log).replace(/"/g, '&quot;')})" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 2px; font-size: 13px;" title="Ubah">
                                <i class="bi bi-pencil-square" style="color: var(--info);"></i>
                            </button>
                            <button onclick="deleteLog(${log.id})" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 2px; font-size: 13px;" title="Hapus">
                                <i class="bi bi-trash-fill" style="color: var(--primary);"></i>
                            </button>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    window.showAddLogModal = async function() {
        const currentDate = dateInput.value;
        const html = `
            <div class="modal-form-group">
                <label>Jenis Transaksi / Kategori *</label>
                <select id="logCategory" class="form-select-dark">
                    <option value="Pemasukan">Pemasukan (Uang Masuk)</option>
                    <option value="Pengeluaran" selected>Pengeluaran (Uang Keluar)</option>
                </select>
            </div>
            
            <div class="modal-form-group">
                <label>Pos Keuangan *</label>
                <select id="logBalanceType" class="form-select-dark">
                    <option value="Uang Laci">Uang Laci</option>
                    <option value="Uang Pulsa">Uang Pulsa</option>
                    <option value="Uang Beras">Uang Beras</option>
                    <option value="Uang Rokok">Uang Rokok</option>
                </select>
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
                <label>Detail / Jenis Transaksi *</label>
                <input type="text" id="logDetail" class="form-control-dark" placeholder="Cth: Beli sabun pel, Bayar tagihan wifi, Pulsa XL">
            </div>

            <div class="modal-form-group">
                <label>Keterangan Tambahan (Opsional)</label>
                <textarea id="logDescription" class="form-control-dark" rows="2" placeholder="Detail tambahan..."></textarea>
            </div>
        `;

        await AppModal.show({
            title: 'Catat Transaksi Keuangan',
            subtitle: 'Tambahkan pemasukan atau pengeluaran harian',
            icon: 'bi-wallet2',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: html,
            submitText: 'Simpan Catatan',
            onSubmit: async () => {
                const cat = document.getElementById('logCategory').value;
                const pos = document.getElementById('logBalanceType').value;
                const amt = parseFloat(document.getElementById('logAmount').value);
                const date = document.getElementById('logDate').value;
                const detail = document.getElementById('logDetail').value.trim();
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
                    showToast('Detail/Jenis transaksi wajib diisi', 'warning');
                    return false;
                }

                try {
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
                        
                        // If date is different from selected date, redirect selectedDate to new logDate
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
    };

    window.editLog = async function(log) {
        const html = `
            <div class="modal-form-group">
                <label>Jenis Transaksi / Kategori *</label>
                <select id="editLogCategory" class="form-select-dark">
                    <option value="Pemasukan" ${log.category === 'Pemasukan' ? 'selected' : ''}>Pemasukan (Uang Masuk)</option>
                    <option value="Pengeluaran" ${log.category === 'Pengeluaran' ? 'selected' : ''}>Pengeluaran (Uang Keluar)</option>
                </select>
            </div>
            
            <div class="modal-form-group">
                <label>Pos Keuangan *</label>
                <select id="editLogBalanceType" class="form-select-dark">
                    <option value="Uang Laci" ${log.balance_type === 'Uang Laci' ? 'selected' : ''}>Uang Laci</option>
                    <option value="Uang Pulsa" ${log.balance_type === 'Uang Pulsa' ? 'selected' : ''}>Uang Pulsa</option>
                    <option value="Uang Beras" ${log.balance_type === 'Uang Beras' ? 'selected' : ''}>Uang Beras</option>
                    <option value="Uang Rokok" ${log.balance_type === 'Uang Rokok' ? 'selected' : ''}>Uang Rokok</option>
                </select>
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
                <label>Detail / Jenis Transaksi *</label>
                <input type="text" id="editLogDetail" class="form-control-dark" placeholder="Cth: Beli sabun pel" value="${escapeHtml(log.detail)}">
            </div>

            <div class="modal-form-group">
                <label>Keterangan Tambahan (Opsional)</label>
                <textarea id="editLogDescription" class="form-control-dark" rows="2" placeholder="Detail tambahan...">${escapeHtml(log.description || '')}</textarea>
            </div>
        `;

        await AppModal.show({
            title: 'Ubah Transaksi Keuangan',
            subtitle: 'Perbarui pencatatan pemasukan/pengeluaran',
            icon: 'bi-pencil-square',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: html,
            submitText: 'Perbarui Catatan',
            onSubmit: async () => {
                const cat = document.getElementById('editLogCategory').value;
                const pos = document.getElementById('editLogBalanceType').value;
                const amt = parseFloat(document.getElementById('editLogAmount').value);
                const date = document.getElementById('editLogDate').value;
                const detail = document.getElementById('editLogDetail').value.trim();
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

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>
