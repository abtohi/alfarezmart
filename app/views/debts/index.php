<?php
/**
 * Catatan Hutang Index View
 * 
 * @var array $suppliers
 * @var array $customerTypes
 * @var string $csrfToken
 */
?>

<div class="page-section" style="padding-bottom: 80px;">
    <!-- Header Summary & Stats Card -->
    <div style="background:var(--gradient-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
                <h4 style="font-weight:700; font-size:var(--font-size-md); margin:0;">Catatan Hutang & Piutang</h4>
                <p style="font-size:var(--font-size-xs); color:var(--text-muted); margin:4px 0 0 0;">Kelola piutang pelanggan dan hutang toko</p>
            </div>
            <div style="width:40px; height:40px; background:var(--primary-bg); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--primary);">
                <i class="bi bi-journal-text" style="font-size:1.2rem;"></i>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:16px;">
            <div style="background:var(--bg-primary); padding:12px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px;">Total Piutang Pelanggan</div>
                <div id="summaryCustomerDebt" style="font-weight:800; font-size:var(--font-size-sm); color:var(--info);">Rp 0</div>
            </div>
            <div style="background:var(--bg-primary); padding:12px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px;">Total Hutang Toko</div>
                <div id="summaryShopDebt" style="font-weight:800; font-size:var(--font-size-sm); color:var(--warning);">Rp 0</div>
            </div>
        </div>
    </div>

    <!-- Tabbed Navigation Menu -->
    <div style="display:flex; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:4px; margin-bottom:16px;">
        <button class="debt-tab active" data-tab="customer-debts" style="flex:1; border:none; padding:10px; border-radius:var(--radius-sm); font-size:var(--font-size-xs); font-weight:700; color:var(--text-primary); cursor:pointer; text-align:center; transition:all 0.2s;">
            <i class="bi bi-person-fill-exclamation" style="margin-right:4px;"></i> Piutang
        </button>
        <button class="debt-tab" data-tab="shop-debts" style="flex:1; border:none; padding:10px; border-radius:var(--radius-sm); font-size:var(--font-size-xs); font-weight:700; color:var(--text-muted); cursor:pointer; text-align:center; transition:all 0.2s;">
            <i class="bi bi-shop-window" style="margin-right:4px;"></i> Hutang Toko
        </button>
        <button class="debt-tab" data-tab="customers" style="flex:1; border:none; padding:10px; border-radius:var(--radius-sm); font-size:var(--font-size-xs); font-weight:700; color:var(--text-muted); cursor:pointer; text-align:center; transition:all 0.2s;">
            <i class="bi bi-people-fill" style="margin-right:4px;"></i> Pelanggan
        </button>
    </div>

    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- Tab 1: Piutang Pelanggan (Customer Debts) Content -->
    <div id="tabContent-customer-debts" class="tab-panel">
        <div style="display:flex; gap:8px; margin-bottom:16px;">
            <div class="search-input-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" id="searchCustomerDebts" placeholder="Cari piutang...">
            </div>
            <button class="btn-primary-custom" style="padding:10px 14px; cursor:pointer;" onclick="showAddCustomerDebtModal()">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>

        <div style="display:flex; gap:6px; margin-bottom:16px; overflow-x:auto; padding-bottom:4px;">
            <button class="filter-status active" data-status="" style="border:1px solid var(--border-color); padding:6px 12px; border-radius:30px; font-size:var(--font-size-xs); font-weight:600; cursor:pointer;">Semua</button>
            <button class="filter-status" data-status="belum_lunas" style="border:1px solid var(--border-color); padding:6px 12px; border-radius:30px; font-size:var(--font-size-xs); font-weight:600; cursor:pointer; color:var(--warning);">Belum Lunas</button>
            <button class="filter-status" data-status="lunas" style="border:1px solid var(--border-color); padding:6px 12px; border-radius:30px; font-size:var(--font-size-xs); font-weight:600; cursor:pointer; color:var(--success);">Lunas</button>
        </div>

        <div id="customerDebtsList">
            <div class="elegant-loader" style="margin:20px auto;">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Hutang Toko (Shop Debts) Content -->
    <div id="tabContent-shop-debts" class="tab-panel" style="display:none;">
        <div style="display:flex; gap:8px; margin-bottom:16px;">
            <div class="search-input-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" id="searchShopDebts" placeholder="Cari hutang toko...">
            </div>
            <button class="btn-primary-custom" style="padding:10px 14px; cursor:pointer;" onclick="showAddShopDebtModal()">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>

        <div style="display:flex; gap:6px; margin-bottom:16px; overflow-x:auto; padding-bottom:4px;">
            <button class="filter-status-shop active" data-status="" style="border:1px solid var(--border-color); padding:6px 12px; border-radius:30px; font-size:var(--font-size-xs); font-weight:600; cursor:pointer;">Semua</button>
            <button class="filter-status-shop" data-status="belum_lunas" style="border:1px solid var(--border-color); padding:6px 12px; border-radius:30px; font-size:var(--font-size-xs); font-weight:600; cursor:pointer; color:var(--warning);">Belum Lunas</button>
            <button class="filter-status-shop" data-status="lunas" style="border:1px solid var(--border-color); padding:6px 12px; border-radius:30px; font-size:var(--font-size-xs); font-weight:600; cursor:pointer; color:var(--success);">Lunas</button>
        </div>

        <div id="shopDebtsList">
            <div class="elegant-loader" style="margin:20px auto;">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Pelanggan (Customers) Content -->
    <div id="tabContent-customers" class="tab-panel" style="display:none;">
        <div style="display:flex; gap:8px; margin-bottom:16px;">
            <div class="search-input-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" id="searchCustomers" placeholder="Cari data pelanggan...">
            </div>
            <button class="btn-primary-custom" style="padding:10px 14px; cursor:pointer;" onclick="showAddCustomerModal()">
                <i class="bi bi-person-plus-fill"></i> Pelanggan
            </button>
        </div>

        <div id="customersList">
            <div class="elegant-loader" style="margin:20px auto;">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
    </div>
</div>

<style>
.debt-tab {
    background: transparent;
    border: none;
    outline: none;
}
.debt-tab.active {
    background: var(--bg-card) !important;
    color: var(--primary) !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.filter-status, .filter-status-shop {
    background: transparent;
    color: var(--text-muted);
}
.filter-status.active, .filter-status-shop.active {
    background: var(--primary-bg) !important;
    color: var(--primary) !important;
    border-color: var(--primary) !important;
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

    // Static variables from PHP variables
    const suppliers = <?= json_encode($suppliers) ?>;
    const customerTypes = <?= json_encode($customerTypes) ?>;
    const debtSources = <?= json_encode($debtSources ?? []) ?>;

    // State Variables
    let currentTab = 'customer-debts';
    let customerDebtsFilter = '';
    let shopDebtsFilter = '';

    // Init Page Load
    loadTabContent();

    // Tab Switches
    document.querySelectorAll('.debt-tab').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.debt-tab').forEach(btn => {
                btn.classList.remove('active');
                btn.style.color = 'var(--text-muted)';
            });
            this.classList.add('active');
            this.style.color = 'var(--primary)';
            
            const tabId = this.dataset.tab;
            currentTab = tabId;
            
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.style.display = 'none';
            });
            document.getElementById('tabContent-' + tabId).style.display = 'block';
            
            loadTabContent();
        });
    });

    // Filters & Search Event Listeners
    document.querySelectorAll('.filter-status').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-status').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            customerDebtsFilter = this.dataset.status;
            loadCustomerDebts();
        });
    });

    document.querySelectorAll('.filter-status-shop').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-status-shop').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            shopDebtsFilter = this.dataset.status;
            loadShopDebts();
        });
    });

    document.getElementById('searchCustomerDebts').addEventListener('input', debounce(loadCustomerDebts, 350));
    document.getElementById('searchShopDebts').addEventListener('input', debounce(loadShopDebts, 350));
    document.getElementById('searchCustomers').addEventListener('input', debounce(loadCustomers, 350));

    // Load active tab data
    function loadTabContent() {
        if (currentTab === 'customer-debts') {
            loadCustomerDebts();
        } else if (currentTab === 'shop-debts') {
            loadShopDebts();
        } else if (currentTab === 'customers') {
            loadCustomers();
        }
    }

    // ==========================================
    // 1. PIUTANG PELANGGAN (CUSTOMER DEBTS)
    // ==========================================

    async function loadCustomerDebts() {
        const container = document.getElementById('customerDebtsList');
        container.innerHTML = `<div class="elegant-loader" style="margin:20px auto;"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>`;
        
        try {
            const q = document.getElementById('searchCustomerDebts').value;
            const res = await api(`${BASE_URL}api/debts/customer?status=${customerDebtsFilter}&q=${encodeURIComponent(q)}`);
            if (res.success) {
                // Update stats card summary
                let totalPiutang = 0;
                res.data.forEach(d => {
                    if (d.status !== 'lunas') {
                        totalPiutang += parseFloat(d.remaining_amount);
                    }
                });
                document.getElementById('summaryCustomerDebt').innerHTML = formatRupiah(totalPiutang);

                if (res.data.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-person-fill-exclamation"></i>
                            <h3>Tidak Ada Catatan Piutang</h3>
                            <p>Cari atau tambahkan piutang baru</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                res.data.forEach(d => {
                    const isLunas = d.status === 'lunas';
                    const amount = parseFloat(d.amount);
                    const remaining = parseFloat(d.remaining_amount);
                    const paid = amount - remaining;
                    const pct = amount > 0 ? (paid / amount) * 100 : 0;
                    
                    const name = d.customer_name || d.customer_name_fallback || 'Pelanggan';
                    const ciri = d.customer_notes || d.notes || '';
                    const isAnonymous = !d.customer_name && ciri;

                    html += `
                        <div class="product-card" style="margin-bottom:12px; cursor:pointer;" onclick="viewCustomerDebtDetail(${JSON.stringify(d).replace(/"/g, '&quot;')})">
                            <div class="product-icon" style="background:${isLunas ? 'var(--success-bg)' : 'var(--info-bg)'}; color:${isLunas ? 'var(--success)' : 'var(--info)'}; flex-shrink:0;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div style="flex:1; min-width:0; margin-left:12px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                                    <div style="font-weight:700; font-size:var(--font-size-sm); color:var(--text-primary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
                                        ${name}
                                        ${isAnonymous ? '<span class="badge-custom badge-danger" style="font-size:10px; margin-left:4px; padding:2px 6px;">Tanpa Nama</span>' : ''}
                                    </div>
                                    <span class="badge-custom ${isLunas ? 'badge-success' : 'badge-warning'}" style="font-size:10px; padding:2px 6px;">
                                        ${isLunas ? 'Lunas' : 'Belum Lunas'}
                                    </span>
                                </div>
                                
                                ${ciri ? `<div style="font-size:11px; color:var(--text-muted); margin-top:2px; font-style:italic;">Ciri: ${ciri}</div>` : ''}
                                
                                <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:var(--font-size-xs);">
                                    <span style="color:var(--text-muted);">Sisa: <strong style="color:var(--info);">${formatRupiah(remaining)}</strong></span>
                                    <span style="color:var(--text-muted);">Total: ${formatRupiah(amount)}</span>
                                </div>

                                <!-- Progress Bar -->
                                <div style="height:4px; background:var(--surface-2); border-radius:2px; margin-top:6px; overflow:hidden;">
                                    <div style="height:100%; width:${pct}%; background:${isLunas ? 'var(--success)' : 'var(--info)'}; border-radius:2px;"></div>
                                </div>

                                <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:10px; color:var(--text-muted);">
                                    <span>Tgl: ${d.debt_date}</span>
                                    ${d.due_date ? `<span style="color:${new Date(d.due_date) < new Date() && !isLunas ? 'var(--primary)' : 'var(--text-muted)'}">Jatuh Tempo: ${d.due_date}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }
        } catch (e) {
            showToast(e.message, 'error');
        }
    }

    window.viewCustomerDebtDetail = async function(d) {
        let historyHTML = '';
        if (d.payments && d.payments.length > 0) {
            d.payments.forEach(p => {
                historyHTML += `
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); padding:8px 0; font-size:12px;">
                        <div>
                            <div style="font-weight:600; color:var(--text-primary);">${formatRupiah(parseFloat(p.amount))}</div>
                            <div style="font-size:10px; color:var(--text-muted);">${p.payment_date} ${p.notes ? `· ${p.notes}` : ''}</div>
                        </div>
                        <i class="bi bi-check2-circle" style="color:var(--success); font-size:1.1rem;"></i>
                    </div>
                `;
            });
        } else {
            historyHTML = `<div style="text-align:center; padding:12px; color:var(--text-muted); font-size:var(--font-size-xs);">Belum ada cicilan tercatat</div>`;
        }

        const isLunas = d.status === 'lunas';
        const modalBody = `
            <div style="text-align:left;">
                <div style="background:var(--bg-primary); border-radius:var(--radius-md); padding:16px; margin-bottom:16px; border:1px solid var(--border-color);">
                    <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Detail Piutang</div>
                    <h3 style="font-size:16px; font-weight:700; margin:0 0 8px 0;">${d.customer_name || d.customer_name_fallback || 'Pelanggan'}</h3>
                    ${d.invoice_number ? `<p style="font-size:12px; color:var(--text-muted); margin:0 0 8px 0;"><i class="bi bi-receipt"></i> Nota POS: <strong>${d.invoice_number}</strong></p>` : ''}
                    <div style="display:flex; justify-content:space-between; margin-top:12px; font-size:13px;">
                        <span style="color:var(--text-muted);">Total Hutang:</span>
                        <span style="font-weight:700; color:var(--text-primary);">${formatRupiah(parseFloat(d.amount))}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:13px;">
                        <span style="color:var(--text-muted);">Sisa Saldo:</span>
                        <span style="font-weight:800; color:var(--info);">${formatRupiah(parseFloat(d.remaining_amount))}</span>
                    </div>
                </div>

                ${!isLunas ? `
                <div style="background:var(--bg-primary); border-radius:var(--radius-md); padding:16px; margin-bottom:16px; border:1px solid var(--border-color);">
                    <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Input Cicilan Baru</div>
                    <div class="modal-form-group">
                        <label>Nominal Pembayaran (Rp) *</label>
                        <input type="number" id="payAmount" class="form-control-dark" placeholder="Cth: 50000" min="1" max="${d.remaining_amount}">
                    </div>
                    <div class="modal-form-group">
                        <label>Tanggal Bayar *</label>
                        <input type="date" id="payDate" class="form-control-dark" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="modal-form-group">
                        <label>Catatan Pembayaran</label>
                        <input type="text" id="payNotes" class="form-control-dark" placeholder="Cth: Titip cash, dll">
                    </div>
                </div>
                ` : ''}

                <div style="background:var(--bg-primary); border-radius:var(--radius-md); padding:16px; border:1px solid var(--border-color);">
                    <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Riwayat Cicilan</div>
                    <div style="max-height:150px; overflow-y:auto;">
                        ${historyHTML}
                    </div>
                </div>
            </div>
        `;

        await AppModal.show({
            title: 'Detail & Pembayaran Piutang',
            subtitle: 'Catat cicilan pembayaran pelanggan',
            icon: 'bi-wallet2',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: modalBody,
            submitText: isLunas ? 'Oke' : 'Simpan Pembayaran',
            hideCancel: isLunas,
            onSubmit: async () => {
                if (isLunas) return true;
                const amt = parseFloat(document.getElementById('payAmount').value);
                const date = document.getElementById('payDate').value;
                const notes = document.getElementById('payNotes').value.trim();

                if (isNaN(amt) || amt <= 0) {
                    showToast('Nominal pembayaran wajib diisi dan valid', 'warning');
                    return false;
                }
                if (amt > parseFloat(d.remaining_amount)) {
                    showToast('Nominal pembayaran melebihi sisa hutang', 'warning');
                    return false;
                }
                if (!date) {
                    showToast('Tanggal pembayaran wajib diisi', 'warning');
                    return false;
                }

                try {
                    const res = await api(`${BASE_URL}api/debts/customer/${d.id}/pay`, 'POST', {
                        csrf_token: csrfVal,
                        amount: amt,
                        payment_date: date,
                        notes: notes
                    });

                    if (res.success) {
                        showToast(res.message || 'Pembayaran berhasil disimpan', 'success');
                        loadCustomerDebts();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });
    };

    window.showAddCustomerDebtModal = async function() {
        let customerOptionsData = [];
        try {
            const custRes = await api(`${BASE_URL}api/customers`);
            if (custRes.success) {
                custRes.data.forEach(c => {
                    const ciri = c.notes ? ` (${c.notes})` : '';
                    customerOptionsData.push({ value: String(c.id), label: `${c.name}${ciri}` });
                });
            }
        } catch (e) {
            console.error(e);
        }
        customerOptionsData.push({ value: 'NEW_ANON', label: '-- input manual / tanpa nama --' });

        const html = `
            <div class="modal-form-group">
                <label>Pelanggan *</label>
                <div id="newDebtCustomerIdContainer"></div>
                <input type="hidden" id="newDebtCustomerId">
            </div>
            
            <div class="modal-form-group" id="manualCustomerGroup" style="display:none;">
                <label>Keterangan/Nama Pelanggan Manual *</label>
                <input type="text" id="newDebtCustomerFallback" class="form-control-dark" placeholder="Cth: Ibu jilbab merah bawa anak">
            </div>

            <div class="modal-form-group">
                <label>Nominal Hutang (Rp) *</label>
                <input type="number" id="newDebtAmount" class="form-control-dark" placeholder="Cth: 75000">
            </div>

            <div class="modal-form-group">
                <label>Tanggal Hutang *</label>
                <input type="date" id="newDebtDate" class="form-control-dark" value="${new Date().toISOString().split('T')[0]}">
            </div>

            <div class="modal-form-group">
                <label>Tanggal Jatuh Tempo (Opsional)</label>
                <input type="date" id="newDebtDueDate" class="form-control-dark">
            </div>

            <div class="modal-form-group">
                <label>Catatan / Keterangan</label>
                <input type="text" id="newDebtNotes" class="form-control-dark" placeholder="Cth: Bon belanja bahan pokok">
            </div>

            <div class="modal-form-group">
                <label>ID Invoice POS Terkait (Opsional)</label>
                <input type="number" id="newDebtSaleId" class="form-control-dark" placeholder="Cth: 12">
            </div>
        `;

        await AppModal.show({
            title: 'Tambah Piutang Pelanggan',
            subtitle: 'Catat hutang baru untuk pelanggan',
            icon: 'bi-person-fill-exclamation',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: html,
            submitText: 'Catat Piutang',
            onShown: () => {
                new SearchBox(document.getElementById('newDebtCustomerIdContainer'), {
                    options: customerOptionsData,
                    placeholder: 'Cari pelanggan...',
                    icon: 'bi-person',
                    name: 'newDebtCustomerIdDummy',
                    clearable: true,
                    onChange: (val) => {
                        document.getElementById('newDebtCustomerId').value = val;
                        toggleCustomerFallback(val);
                    },
                    onClear: () => {
                        document.getElementById('newDebtCustomerId').value = '';
                        toggleCustomerFallback('');
                    }
                });
            },
            onSubmit: async () => {
                const custId = document.getElementById('newDebtCustomerId').value;
                const fallback = document.getElementById('newDebtCustomerFallback').value.trim();
                const amt = parseFloat(document.getElementById('newDebtAmount').value);
                const date = document.getElementById('newDebtDate').value;
                const dueDate = document.getElementById('newDebtDueDate').value;
                const notes = document.getElementById('newDebtNotes').value.trim();
                const saleId = document.getElementById('newDebtSaleId').value;

                if (!custId) {
                    showToast('Harap pilih pelanggan atau input manual', 'warning');
                    return false;
                }
                if (custId === 'NEW_ANON' && !fallback) {
                    showToast('Harap isi nama manual atau ciri ciri pelanggan', 'warning');
                    return false;
                }
                if (isNaN(amt) || amt <= 0) {
                    showToast('Nominal hutang wajib diisi dan valid', 'warning');
                    return false;
                }
                if (!date) {
                    showToast('Tanggal hutang wajib diisi', 'warning');
                    return false;
                }

                try {
                    const res = await api(`${BASE_URL}api/debts/customer`, 'POST', {
                        csrf_token: csrfVal,
                        customer_id: custId !== 'NEW_ANON' ? custId : '',
                        customer_name_fallback: custId === 'NEW_ANON' ? fallback : '',
                        amount: amt,
                        debt_date: date,
                        due_date: dueDate,
                        notes: notes,
                        sale_id: saleId
                    });

                    if (res.success) {
                        showToast(res.message || 'Hutang berhasil dicatat', 'success');
                        loadCustomerDebts();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });

        // Toggle Manual Input Helper
        window.toggleCustomerFallback = function(val) {
            const manualGroup = document.getElementById('manualCustomerGroup');
            if (val === 'NEW_ANON') {
                manualGroup.style.display = 'block';
                document.getElementById('newDebtCustomerFallback').focus();
            } else {
                manualGroup.style.display = 'none';
            }
        };
    };


    // ==========================================
    // 2. HUTANG TOKO (SHOP DEBTS)
    // ==========================================

    async function loadShopDebts() {
        const container = document.getElementById('shopDebtsList');
        container.innerHTML = `<div class="elegant-loader" style="margin:20px auto;"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>`;
        
        try {
            const q = document.getElementById('searchShopDebts').value;
            const res = await api(`${BASE_URL}api/debts/shop?status=${shopDebtsFilter}&q=${encodeURIComponent(q)}`);
            if (res.success) {
                // Update stats card summary
                let totalHutang = 0;
                res.data.forEach(d => {
                    if (d.status !== 'lunas') {
                        totalHutang += parseFloat(d.remaining_amount);
                    }
                });
                document.getElementById('summaryShopDebt').innerHTML = formatRupiah(totalHutang);

                if (res.data.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-shop-window"></i>
                            <h3>Tidak Ada Catatan Hutang Toko</h3>
                            <p>Cari atau tambahkan hutang baru</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                res.data.forEach(d => {
                    const isLunas = d.status === 'lunas';
                    const amount = parseFloat(d.amount);
                    const remaining = parseFloat(d.remaining_amount);
                    const paid = amount - remaining;
                    const pct = amount > 0 ? (paid / amount) * 100 : 0;
                    
                    const name = d.supplier_name || d.supplier_name_fallback || 'Pihak Lain';

                    html += `
                        <div class="product-card" style="margin-bottom:12px; cursor:pointer;" onclick="viewShopDebtDetail(${JSON.stringify(d).replace(/"/g, '&quot;')})">
                            <div class="product-icon" style="background:${isLunas ? 'var(--success-bg)' : 'var(--warning-bg)'}; color:${isLunas ? 'var(--success)' : 'var(--warning)'}; flex-shrink:0;">
                                <i class="bi bi-shop-window"></i>
                            </div>
                            <div style="flex:1; min-width:0; margin-left:12px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                                    <div style="font-weight:700; font-size:var(--font-size-sm); color:var(--text-primary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
                                        ${name}
                                        ${d.source_name ? `<span class="badge-custom badge-info" style="font-size:9px; margin-left:4px;">${d.source_name}</span>` : ''}
                                    </div>
                                    <span class="badge-custom ${isLunas ? 'badge-success' : 'badge-warning'}" style="font-size:10px; padding:2px 6px;">
                                        ${isLunas ? 'Lunas' : 'Belum Lunas'}
                                    </span>
                                </div>
                                
                                ${d.notes ? `<div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Catatan: ${d.notes}</div>` : ''}
                                
                                <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:var(--font-size-xs);">
                                    <span style="color:var(--text-muted);">Sisa: <strong style="color:var(--warning);">${formatRupiah(remaining)}</strong></span>
                                    <span style="color:var(--text-muted);">Total: ${formatRupiah(amount)}</span>
                                </div>

                                <!-- Progress Bar -->
                                <div style="height:4px; background:var(--surface-2); border-radius:2px; margin-top:6px; overflow:hidden;">
                                    <div style="height:100%; width:${pct}%; background:${isLunas ? 'var(--success)' : 'var(--warning)'}; border-radius:2px;"></div>
                                </div>

                                <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:10px; color:var(--text-muted);">
                                    <span>Tgl: ${d.debt_date}</span>
                                    ${d.due_date ? `<span style="color:${new Date(d.due_date) < new Date() && !isLunas ? 'var(--primary)' : 'var(--text-muted)'}">Jatuh Tempo: ${d.due_date}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }
        } catch (e) {
            showToast(e.message, 'error');
        }
    }

    window.viewShopDebtDetail = async function(d) {
        let historyHTML = '';
        if (d.payments && d.payments.length > 0) {
            d.payments.forEach(p => {
                historyHTML += `
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); padding:8px 0; font-size:12px;">
                        <div>
                            <div style="font-weight:600; color:var(--text-primary);">${formatRupiah(parseFloat(p.amount))}</div>
                            <div style="font-size:10px; color:var(--text-muted);">${p.payment_date} ${p.notes ? `· ${p.notes}` : ''}</div>
                        </div>
                        <i class="bi bi-check2-circle" style="color:var(--success); font-size:1.1rem;"></i>
                    </div>
                `;
            });
        } else {
            historyHTML = `<div style="text-align:center; padding:12px; color:var(--text-muted); font-size:var(--font-size-xs);">Belum ada pembayaran tercatat</div>`;
        }

        const isLunas = d.status === 'lunas';
        const modalBody = `
            <div style="text-align:left;">
                <div style="background:var(--bg-primary); border-radius:var(--radius-md); padding:16px; margin-bottom:16px; border:1px solid var(--border-color);">
                    <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Detail Hutang Toko</div>
                    <h3 style="font-size:16px; font-weight:700; margin:0 0 8px 0;">${d.supplier_name || d.supplier_name_fallback || 'Supplier'}</h3>
                    ${d.purchase_code ? `<p style="font-size:12px; color:var(--text-muted); margin:0 0 8px 0;"><i class="bi bi-cart-check"></i> Faktur Masuk: <strong>${d.purchase_code}</strong></p>` : ''}
                    <div style="display:flex; justify-content:space-between; margin-top:12px; font-size:13px;">
                        <span style="color:var(--text-muted);">Total Hutang:</span>
                        <span style="font-weight:700; color:var(--text-primary);">${formatRupiah(parseFloat(d.amount))}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:13px;">
                        <span style="color:var(--text-muted);">Sisa Hutang:</span>
                        <span style="font-weight:800; color:var(--warning);">${formatRupiah(parseFloat(d.remaining_amount))}</span>
                    </div>
                </div>

                ${!isLunas ? `
                <div style="background:var(--bg-primary); border-radius:var(--radius-md); padding:16px; margin-bottom:16px; border:1px solid var(--border-color);">
                    <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Input Cicilan Bayar</div>
                    <div class="modal-form-group">
                        <label>Nominal Bayar (Rp) *</label>
                        <input type="number" id="payShopAmount" class="form-control-dark" placeholder="Cth: 100000" min="1" max="${d.remaining_amount}">
                    </div>
                    <div class="modal-form-group">
                        <label>Tanggal Bayar *</label>
                        <input type="date" id="payShopDate" class="form-control-dark" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="modal-form-group">
                        <label>Catatan</label>
                        <input type="text" id="payShopNotes" class="form-control-dark" placeholder="Cth: Bayar cash via kurir">
                    </div>
                </div>
                ` : ''}

                <div style="background:var(--bg-primary); border-radius:var(--radius-md); padding:16px; border:1px solid var(--border-color);">
                    <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Riwayat Pembayaran</div>
                    <div style="max-height:150px; overflow-y:auto;">
                        ${historyHTML}
                    </div>
                </div>
            </div>
        `;

        await AppModal.show({
            title: 'Detail & Pembayaran Hutang Toko',
            subtitle: 'Kelola pembayaran hutang ke supplier',
            icon: 'bi-shop-window',
            iconColor: 'var(--warning-bg)',
            iconAccent: 'var(--warning)',
            bodyHTML: modalBody,
            submitText: isLunas ? 'Oke' : 'Simpan Pembayaran',
            hideCancel: isLunas,
            onSubmit: async () => {
                if (isLunas) return true;
                const amt = parseFloat(document.getElementById('payShopAmount').value);
                const date = document.getElementById('payShopDate').value;
                const notes = document.getElementById('payShopNotes').value.trim();

                if (isNaN(amt) || amt <= 0) {
                    showToast('Nominal pembayaran wajib diisi dan valid', 'warning');
                    return false;
                }
                if (amt > parseFloat(d.remaining_amount)) {
                    showToast('Nominal pembayaran melebihi sisa hutang', 'warning');
                    return false;
                }
                if (!date) {
                    showToast('Tanggal pembayaran wajib diisi', 'warning');
                    return false;
                }

                try {
                    const res = await api(`${BASE_URL}api/debts/shop/${d.id}/pay`, 'POST', {
                        csrf_token: csrfVal,
                        amount: amt,
                        payment_date: date,
                        notes: notes
                    });

                    if (res.success) {
                        showToast(res.message || 'Pembayaran berhasil disimpan', 'success');
                        loadShopDebts();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });
    };

    window.showAddShopDebtModal = async function() {
        let supplierOptions = '<optgroup label="Supplier">';
        suppliers.forEach(s => {
            supplierOptions += `<option value="SUP_${s.id}">${s.name}</option>`;
        });
        supplierOptions += '</optgroup><optgroup label="Sumber Lain">';
        debtSources.forEach(ds => {
            supplierOptions += `<option value="SRC_${ds.id}">${ds.name}</option>`;
        });
        supplierOptions += '</optgroup>';

        const html = `
            <div class="modal-form-group">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <label style="margin:0;">Sumber Hutang / Kreditur *</label>
                    <button onclick="manageDebtSources()" class="btn-primary-custom" style="padding:4px 8px; font-size:10px; border-radius:4px;"><i class="bi bi-gear"></i> Kelola Opsi</button>
                </div>
                <!-- Using component SearchBox for elegance -->
                <div id="shopDebtSourceSearch" class="search-box-component"></div>
                <input type="hidden" id="newShopSupplierId">
                <input type="hidden" id="newShopDebtSourceId">
            </div>
            
            <div class="modal-form-group" id="manualSupplierGroup" style="display:none;">
                <label>Nama Kreditur/Pihak Lain *</label>
                <input type="text" id="newShopSupplierFallback" class="form-control-dark" placeholder="Cth: Bank Mandiri, Teman, dll">
            </div>

            <div class="modal-form-group">
                <label>Nominal Hutang Toko (Rp) *</label>
                <input type="number" id="newShopAmount" class="form-control-dark" placeholder="Cth: 1500000">
            </div>

            <div class="modal-form-group">
                <label>Tanggal Hutang *</label>
                <input type="date" id="newShopDate" class="form-control-dark" value="${new Date().toISOString().split('T')[0]}">
            </div>

            <div class="modal-form-group">
                <label>Tanggal Jatuh Tempo (Opsional)</label>
                <input type="date" id="newShopDueDate" class="form-control-dark">
            </div>

            <div class="modal-form-group">
                <label>Catatan / Keterangan</label>
                <input type="text" id="newShopNotes" class="form-control-dark" placeholder="Cth: Pinjaman modal barang masuk">
            </div>

            <div class="modal-form-group">
                <label>ID Transaksi Pembelian Terkait (Opsional)</label>
                <input type="number" id="newShopPurchaseId" class="form-control-dark" placeholder="Cth: 15">
            </div>
        `;

        AppModal.show({
            title: 'Tambah Hutang Toko',
            subtitle: 'Catat hutang toko ke pihak ketiga/supplier',
            icon: 'bi-shop-window',
            iconColor: 'var(--warning-bg)',
            iconAccent: 'var(--warning)',
            bodyHTML: html,
            submitText: 'Catat Hutang',
            onSubmit: async () => {
                const supId = document.getElementById('newShopSupplierId').value;
                const sourceId = document.getElementById('newShopDebtSourceId').value;
                const fallback = document.getElementById('newShopSupplierFallback').value.trim();
                const amt = parseFloat(document.getElementById('newShopAmount').value);
                const date = document.getElementById('newShopDate').value;
                const dueDate = document.getElementById('newShopDueDate').value;
                const notes = document.getElementById('newShopNotes').value.trim();
                const purchaseId = document.getElementById('newShopPurchaseId').value;

                if (!supId && !sourceId && !fallback) {
                    showToast('Harap pilih sumber hutang atau input manual', 'warning');
                    return false;
                }
                if (isNaN(amt) || amt <= 0) {
                    showToast('Nominal hutang wajib diisi dan valid', 'warning');
                    return false;
                }
                if (!date) {
                    showToast('Tanggal hutang wajib diisi', 'warning');
                    return false;
                }

                try {
                    const payload = {
                        csrf_token: csrfVal,
                        amount: amt,
                        debt_date: date,
                        due_date: dueDate,
                        notes: notes,
                        purchase_id: purchaseId
                    };
                    
                    if (supId) payload.supplier_id = supId;
                    if (sourceId) payload.debt_source_id = sourceId;
                    if (fallback) payload.supplier_name_fallback = fallback;

                    const res = await api(`${BASE_URL}api/debts/shop`, 'POST', payload);

                    if (res.success) {
                        showToast(res.message || 'Hutang toko berhasil dicatat', 'success');
                        loadShopDebts();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });

        // Initialize SearchBox Component for Debt Sources (using correct new SearchBox() API)
        setTimeout(() => {
            const searchContainer = document.getElementById('shopDebtSourceSearch');
            if (!searchContainer) return;
            
            const optionsList = [];
            suppliers.forEach(s => optionsList.push({ value: 'SUP_' + s.id, label: s.name }));
            debtSources.forEach(ds => optionsList.push({ value: 'SRC_' + ds.id, label: ds.name }));
            optionsList.push({ value: 'NEW_MANUAL', label: '+ Input Manual' });
            
            new SearchBox(searchContainer, {
                options: optionsList,
                placeholder: 'Ketik / pilih sumber hutang...',
                icon: 'bi-building',
                clearable: true,
                onSelect: (value, label) => {
                    const supEl = document.getElementById('newShopSupplierId');
                    const srcEl = document.getElementById('newShopDebtSourceId');
                    const fallbackGrp = document.getElementById('manualSupplierGroup');
                    const fallbackInput = document.getElementById('newShopSupplierFallback');
                    
                    if (!supEl || !srcEl) return;
                    supEl.value = '';
                    srcEl.value = '';
                    fallbackGrp.style.display = 'none';
                    fallbackInput.value = '';
                    
                    if (!value) return;
                    
                    if (value === 'NEW_MANUAL') {
                        fallbackGrp.style.display = 'block';
                        fallbackInput.focus();
                    } else if (value.startsWith('SUP_')) {
                        supEl.value = value.replace('SUP_', '');
                    } else if (value.startsWith('SRC_')) {
                        srcEl.value = value.replace('SRC_', '');
                    }
                }
            });
        }, 100);
    };

    // Manage Debt Sources
    window.manageDebtSources = async function() {
        AppModal.close(); // Hide current modal
        
        let dsListHtml = '';
        debtSources.forEach(ds => {
            dsListHtml += `
                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--surface-2); padding:10px; border-radius:var(--radius-sm); margin-bottom:8px;">
                    <div style="font-size:13px; font-weight:600; color:var(--text-primary);">${ds.name}</div>
                    <div style="display:flex; gap:6px;">
                        <button onclick="editDebtSource(${ds.id}, '${ds.name.replace(/'/g, "\\'")}')" class="btn-icon" style="color:var(--info); padding:4px;"><i class="bi bi-pencil-square"></i></button>
                        <button onclick="deleteDebtSource(${ds.id}, '${ds.name.replace(/'/g, "\\'")}')" class="btn-icon" style="color:var(--danger); padding:4px;"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
        });
        
        if (debtSources.length === 0) {
            dsListHtml = `<div style="text-align:center; padding:16px; color:var(--text-muted); font-size:12px;">Belum ada opsi tambahan</div>`;
        }
        
        const html = `
            <div style="margin-bottom:16px;">
                <div style="display:flex; gap:8px;">
                    <input type="text" id="newDsName" class="form-control-dark" placeholder="Nama sumber hutang baru..." style="flex:1;">
                    <button onclick="addDebtSource()" class="btn-primary-custom" style="padding:0 16px;"><i class="bi bi-plus-lg"></i></button>
                </div>
            </div>
            <div style="max-height:300px; overflow-y:auto;" id="dsListContainer">
                ${dsListHtml}
            </div>
        `;
        
        await AppModal.show({
            title: 'Kelola Opsi Sumber Hutang',
            subtitle: 'Tambah opsi sumber hutang selain supplier',
            icon: 'bi-list-ul',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: html,
            submitText: 'Tutup',
            onSubmit: () => {
                location.reload(); // Reload to reflect changes in variables safely
                return true;
            }
        });
    };
    
    window.addDebtSource = async function() {
        const name = document.getElementById('newDsName').value.trim();
        if (!name) return showToast('Nama tidak boleh kosong', 'warning');
        try {
            const res = await api(BASE_URL + 'api/debts/sources', 'POST', { csrf_token: csrfVal, name });
            if (res.success) {
                showToast(res.message, 'success');
                debtSources.push({id: res.id, name: res.name});
                manageDebtSources();
            }
        } catch (e) { showToast(e.message, 'error'); }
    };
    
    window.editDebtSource = async function(id, oldName) {
        const newName = prompt('Ubah Nama Sumber Hutang:', oldName);
        if (!newName || newName.trim() === '' || newName === oldName) return;
        try {
            const res = await api(`${BASE_URL}api/debts/sources/${id}`, 'POST', { csrf_token: csrfVal, name: newName.trim() });
            if (res.success) {
                showToast(res.message, 'success');
                const idx = debtSources.findIndex(d => d.id == id);
                if (idx > -1) debtSources[idx].name = newName.trim();
                manageDebtSources();
            }
        } catch (e) { showToast(e.message, 'error'); }
    };
    
    window.deleteDebtSource = async function(id, name) {
        if (!confirm(`Hapus sumber hutang '${name}'?`)) return;
        try {
            const res = await api(`${BASE_URL}api/debts/sources/${id}/delete`, 'POST', { csrf_token: csrfVal });
            if (res.success) {
                showToast(res.message, 'success');
                const idx = debtSources.findIndex(d => d.id == id);
                if (idx > -1) debtSources.splice(idx, 1);
                manageDebtSources();
            }
        } catch (e) { showToast(e.message, 'error'); }
    };


    // ==========================================
    // 3. DATABASE PELANGGAN (CUSTOMERS)
    // ==========================================

    async function loadCustomers() {
        const container = document.getElementById('customersList');
        container.innerHTML = `<div class="elegant-loader" style="margin:20px auto;"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>`;
        
        try {
            const q = document.getElementById('searchCustomers').value;
            const res = await api(`${BASE_URL}api/customers?q=${encodeURIComponent(q)}`);
            if (res.success) {
                if (res.data.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-people-fill"></i>
                            <h3>Belum Ada Pelanggan</h3>
                            <p>Tambahkan pelanggan toko Anda</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                res.data.forEach(c => {
                    const phone = c.phone || 'Tidak ada HP';
                    const addr = c.address || 'Tidak ada alamat';
                    const notes = c.notes || ''; // ciri fisik
                    const isAnon = c.name.toLowerCase().includes('tanpa nama');

                    html += `
                        <div class="product-card" style="margin-bottom:12px; cursor:default;">
                            <div class="product-icon" style="background:var(--success-bg); color:var(--success); flex-shrink:0;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div style="flex:1; min-width:0; margin-left:12px;">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div style="font-weight:700; font-size:var(--font-size-sm); color:var(--text-primary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
                                        ${c.name}
                                        ${isAnon ? '<span class="badge-custom badge-danger" style="font-size:9px; margin-left:4px;">Tanpa Nama</span>' : ''}
                                    </div>
                                    <div style="display:flex; gap:6px;">
                                        ${c.phone ? `<a href="https://wa.me/${c.phone.replace(/^0/, '62').replace(/\D/g, '')}" target="_blank" class="btn-icon" style="color:#25D366; text-decoration:none; display:flex; align-items:center; justify-content:center;" title="Hubungi via WhatsApp"><i class="bi bi-whatsapp"></i></a>` : ''}
                                        <button onclick="showEditCustomerModal(${JSON.stringify(c).replace(/"/g, '&quot;')})" class="btn-icon" style="color:var(--text-primary);" title="Edit Pelanggan"><i class="bi bi-pencil-square"></i></button>
                                        <button onclick="deleteCustomer(${c.id}, '${c.name.replace(/'/g, "\\'")}')" class="btn-icon" style="color:var(--danger);" title="Hapus Pelanggan"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:4px;">
                                    <i class="bi bi-telephone"></i> ${phone} · <i class="bi bi-geo-alt"></i> ${addr}
                                </div>
                                ${notes ? `<div style="font-size:11px; color:var(--text-muted); margin-top:4px; font-style:italic;">Ciri/Keterangan: ${notes}</div>` : ''}
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }
        } catch (e) {
            showToast(e.message, 'error');
        }
    }

    function getCustomerFormHTML(c = {}) {
        let optionsListHtml = '';
        let activeTypeName = 'Pilih Level...';
        customerTypes.forEach(t => {
            if (c.type_id == t.id) activeTypeName = `${t.name} (Tier: ${t.price_tier})`;
            optionsListHtml += `<li><a class="dropdown-item ${c.type_id == t.id ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='${t.id}'; dp.querySelector('button span').textContent='${t.name} (Tier: ${t.price_tier})'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">${t.name} (Tier: ${t.price_tier})</a></li>`;
        });

        const isAnon = c.name ? c.name.toLowerCase().includes('tanpa nama') : false;

        return `
            <div class="modal-form-group" style="margin-bottom:12px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; background:var(--surface-2); padding:10px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                    <input type="checkbox" id="modalCustAnon" value="1" ${isAnon ? 'checked' : ''} onchange="toggleAnonCheckbox(this.checked)" style="width:18px; height:18px; accent-color:var(--primary);">
                    <span style="font-size:var(--font-size-sm); font-weight:600; color:var(--text-primary);">Nama Pelanggan Tidak Diketahui</span>
                </label>
                <div style="font-size:10px; color:var(--text-muted); margin-top:4px; margin-left:26px;">Gunakan opsi ini jika pelanggan tidak tahu namanya, dan kasir hanya mencatat ciri fisik.</div>
            </div>
            
            <div class="modal-form-group" id="groupCustName">
                <label>Nama Pelanggan *</label>
                <input type="text" id="modalCustName" class="form-control-dark" value="${c.name || ''}" placeholder="Cth: Budi Santoso" required>
            </div>
            
            <div class="modal-form-group">
                <label>Nomor HP / WA</label>
                <input type="text" id="modalCustPhone" class="form-control-dark" value="${c.phone || ''}" placeholder="Cth: 0812...">
            </div>

            <div class="modal-form-group">
                <label>Alamat</label>
                <input type="text" id="modalCustAddr" class="form-control-dark" value="${c.address || ''}" placeholder="Alamat lengkap...">
            </div>

            <div class="modal-form-group">
                <label>Level Kategori Pelanggan</label>
                <div class="dropdown" style="width:100%;">
                    <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:10px; font-size:12px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                        <span>${activeTypeName}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:12px; min-width:100%;">
                        ${optionsListHtml}
                    </ul>
                    <input type="hidden" id="modalCustType" value="${c.type_id || ''}">
                </div>
            </div>

            <div class="modal-form-group">
                <label id="labelNotes">Catatan / Ciri-Ciri Fisik ${isAnon ? '*' : ''}</label>
                <textarea id="modalCustNotes" class="form-control-dark" placeholder="Cth: Ibu-ibu kerudung merah, sering bawa anak kecil, pakai motor Beat" rows="3" required>${c.notes || ''}</textarea>
            </div>
        `;
    }

    // Toggle Checkbox Name Helper
    window.toggleAnonCheckbox = function(checked) {
        const nameGroup = document.getElementById('groupCustName');
        const nameInput = document.getElementById('modalCustName');
        const notesLabel = document.getElementById('labelNotes');
        
        if (checked) {
            nameGroup.style.opacity = '0.5';
            nameInput.disabled = true;
            nameInput.value = 'Pelanggan Tanpa Nama';
            notesLabel.innerHTML = 'Catatan / Ciri-Ciri Fisik *';
            document.getElementById('modalCustNotes').focus();
        } else {
            nameGroup.style.opacity = '1';
            nameInput.disabled = false;
            nameInput.value = '';
            notesLabel.innerHTML = 'Catatan / Ciri-Ciri Fisik';
        }
    };

    window.showAddCustomerModal = async function() {
        await AppModal.show({
            title: 'Tambah Pelanggan',
            subtitle: 'Tambahkan data pelanggan baru',
            icon: 'bi-person-plus-fill',
            iconColor: 'var(--success-bg)',
            iconAccent: 'var(--success)',
            bodyHTML: getCustomerFormHTML(),
            submitText: 'Simpan',
            onSubmit: async () => {
                const isAnon = document.getElementById('modalCustAnon').checked;
                let name = document.getElementById('modalCustName').value.trim();
                const phone = document.getElementById('modalCustPhone').value.trim();
                const address = document.getElementById('modalCustAddr').value.trim();
                const notes = document.getElementById('modalCustNotes').value.trim();
                const typeId = document.getElementById('modalCustType').value;

                if (isAnon) {
                    if (!notes) {
                        showToast('Ciri-ciri fisik wajib diisi jika nama tidak diketahui', 'warning');
                        return false;
                    }
                    // Generate anonymous customer name from brief description of physical traits
                    const shortTrait = notes.split(',')[0].substring(0, 30);
                    name = `Tanpa Nama - ${shortTrait}`;
                } else if (!name) {
                    showToast('Nama pelanggan wajib diisi', 'warning');
                    return false;
                }

                try {
                    const res = await api(`${BASE_URL}api/customers`, 'POST', {
                        csrf_token: csrfVal,
                        name: name,
                        phone: phone,
                        address: address,
                        notes: notes,
                        type_id: typeId
                    });

                    if (res.success) {
                        showToast('Pelanggan berhasil ditambahkan', 'success');
                        loadCustomers();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });

        // Initialize state
        toggleAnonCheckbox(false);
    };

    window.showEditCustomerModal = async function(c) {
        await AppModal.show({
            title: 'Edit Pelanggan',
            subtitle: 'Ubah data pelanggan',
            icon: 'bi-pencil-square',
            iconColor: 'var(--success-bg)',
            iconAccent: 'var(--success)',
            bodyHTML: getCustomerFormHTML(c),
            submitText: 'Update',
            onSubmit: async () => {
                const isAnon = document.getElementById('modalCustAnon').checked;
                let name = document.getElementById('modalCustName').value.trim();
                const phone = document.getElementById('modalCustPhone').value.trim();
                const address = document.getElementById('modalCustAddr').value.trim();
                const notes = document.getElementById('modalCustNotes').value.trim();
                const typeId = document.getElementById('modalCustType').value;

                if (isAnon) {
                    if (!notes) {
                        showToast('Ciri-ciri fisik wajib diisi jika nama tidak diketahui', 'warning');
                        return false;
                    }
                    const shortTrait = notes.split(',')[0].substring(0, 30);
                    name = `Tanpa Nama - ${shortTrait}`;
                } else if (!name) {
                    showToast('Nama pelanggan wajib diisi', 'warning');
                    return false;
                }

                try {
                    const res = await api(`${BASE_URL}api/customers/${c.id}`, 'POST', {
                        csrf_token: csrfVal,
                        name: name,
                        phone: phone,
                        address: address,
                        notes: notes,
                        type_id: typeId
                    });

                    if (res.success) {
                        showToast('Pelanggan berhasil diupdate', 'success');
                        loadCustomers();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });

        // Initialize checkbox UI
        const isAnon = c.name.toLowerCase().includes('tanpa nama');
        toggleAnonCheckbox(isAnon);
    };

    window.deleteCustomer = async function(id, name) {
        await AppModal.show({
            title: 'Hapus Pelanggan',
            subtitle: 'Konfirmasi Penghapusan',
            icon: 'bi-exclamation-triangle',
            iconColor: 'var(--danger-bg)',
            iconAccent: 'var(--danger)',
            bodyHTML: `<p style="text-align:center; font-size:var(--font-size-sm); margin:0;">Apakah Anda yakin ingin menghapus pelanggan <strong>${name}</strong>? Tindakan ini tidak dapat dibatalkan.</p>`,
            submitText: 'Hapus',
            onSubmit: async () => {
                try {
                    const res = await api(`${BASE_URL}api/customers/${id}/delete`, 'POST', {
                        csrf_token: csrfVal
                    });
                    if (res.success) {
                        showToast('Pelanggan berhasil dihapus', 'success');
                        loadCustomers();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });
    };
});
</script>
