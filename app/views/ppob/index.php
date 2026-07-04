
<!-- Custom CSS for PPOB — fully aligned with AlfarezMart design system -->
<style>
    /* ===== PPOB-specific overrides using design tokens ===== */
    .ppob-hero {
        background: var(--gradient-primary);
        border-radius: var(--radius-lg);
        padding: 20px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }
    .ppob-hero::before {
        content: '';
        position: absolute;
        top: -30px; right: -30px;
        width: 120px; height: 120px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .ppob-hero::after {
        content: '';
        position: absolute;
        bottom: -20px; right: 40px;
        width: 80px; height: 80px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .ppob-balance-pill {
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.25);
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 700;
        font-size: var(--font-size-sm);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        backdrop-filter: blur(4px);
    }
    .category-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }
    .category-card {
        background: var(--surface-1);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 14px 8px;
        text-align: center;
        cursor: pointer;
        transition: all var(--transition-base);
    }
    .category-card:hover, .category-card:active {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
        border-color: var(--primary);
        background: var(--primary-bg);
    }
    .category-icon {
        font-size: 22px;
        margin-bottom: 6px;
        line-height: 1;
    }
    .category-label {
        font-size: var(--font-size-xs);
        font-weight: 700;
        color: var(--text-secondary);
        font-family: var(--font-family);
        letter-spacing: 0.2px;
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(136px, 1fr));
        gap: 10px;
        margin-top: 14px;
    }
    .product-card {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 12px;
        cursor: pointer;
        transition: all var(--transition-base);
        background: var(--surface-1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 90px;
    }
    .product-card:hover {
        border-color: var(--primary);
        background: var(--primary-bg);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    .product-name {
        font-size: var(--font-size-xs);
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
        line-height: 1.4;
        font-family: var(--font-family);
        flex: 1;
    }
    .product-desc {
        font-size: 10px;
        color: var(--text-muted);
        margin-bottom: 6px;
        line-height: 1.3;
    }
    .product-price {
        font-size: var(--font-size-sm);
        font-weight: 800;
        color: var(--primary);
        font-family: var(--font-family);
    }
    .product-modal-price {
        font-size: 9px;
        color: var(--text-muted);
        font-weight: 400;
    }
    .badge-cheapest {
        display: inline-block;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        font-size: 8px;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 6px;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .badge-cutoff {
        display: inline-block;
        background: var(--warning-bg);
        color: var(--warning);
        font-size: 9px;
        font-weight: 600;
        padding: 1px 5px;
        border-radius: 4px;
        margin-top: 3px;
    }
    .ppob-input {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: var(--font-size-base);
        background: var(--bg-input);
        color: var(--text-primary);
        font-family: var(--font-family);
        transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
        outline: none;
    }
    .ppob-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-bg);
    }
    .ppob-input::placeholder {
        color: var(--text-muted);
    }
    .ppob-select {
        width: 100%;
        padding: 11px 16px;
        border: 1.5px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: var(--font-size-base);
        background: var(--bg-input);
        color: var(--text-primary);
        font-family: var(--font-family);
        transition: border-color var(--transition-fast);
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
    }
    .ppob-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-bg);
    }
    .ppob-select option {
        background: var(--bg-card);
        color: var(--text-primary);
    }
    .select-wrapper {
        position: relative;
    }
    .select-wrapper::after {
        content: '\F282';
        font-family: 'bootstrap-icons';
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
        font-size: 14px;
    }
    .input-wrapper {
        position: relative;
    }
    .input-suffix-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        cursor: pointer;
        font-size: 16px;
        padding: 4px;
        transition: color var(--transition-fast);
    }
    .input-suffix-icon:hover { color: var(--primary); }
    .input-wrapper input { padding-right: 42px; }
    .inquiry-box {
        border-radius: var(--radius-md);
        padding: 12px 14px;
        font-size: var(--font-size-sm);
        margin-top: 10px;
        display: none;
    }
    .list-group-item {
        background: var(--surface-1);
        border-color: var(--border-color);
        color: var(--text-primary);
        font-family: var(--font-family);
    }
    .list-group-item:hover { background: var(--surface-2); }
    .modal-content {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    .modal-header-ppob {
        background: var(--gradient-primary);
        padding: 16px 20px;
    }
    .modal-detail-box {
        background: var(--surface-2);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 14px 16px;
    }
    .modal-detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        font-size: var(--font-size-sm);
    }
    .modal-detail-row .label { color: var(--text-muted); }
    .modal-detail-row .value { font-weight: 700; color: var(--text-primary); text-align: right; max-width: 60%; }
    .btn-ppob-primary {
        background: var(--gradient-primary);
        color: #fff;
        border: none;
        border-radius: var(--radius-md);
        padding: 13px 20px;
        font-weight: 700;
        font-size: var(--font-size-base);
        font-family: var(--font-family);
        width: 100%;
        cursor: pointer;
        transition: opacity var(--transition-fast), transform var(--transition-fast);
    }
    .btn-ppob-primary:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn-ppob-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .btn-ppob-secondary {
        background: var(--surface-2);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 11px 20px;
        font-weight: 600;
        font-size: var(--font-size-sm);
        font-family: var(--font-family);
        cursor: pointer;
        transition: background var(--transition-fast);
    }
    .btn-ppob-secondary:hover { background: var(--surface-3); }
    #ppob-view { display: block; }
    #category-view { display: none; }
</style>

<div class="page-section">
    <!-- Main PPOB View -->
    <div id="ppob-view">
        <!-- Hero Header -->
        <div class="ppob-hero">
            <div style="position:relative;z-index:1;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:var(--font-size-lg);font-weight:800;color:#fff;font-family:var(--font-family);">Produk Digital</div>
                    <div style="font-size:var(--font-size-xs);color:rgba(255,255,255,0.8);margin-top:2px;font-family:var(--font-family);">Layanan PPOB Digiflazz</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div class="ppob-balance-pill">
                        <i class="bi bi-wallet2"></i>
                        <span id="digi-balance">Loading...</span>
                    </div>
                    <?php if (in_array($currentUser['level'] ?? '', ['superadmin', 'admin'])): ?>
                    <a href="<?= BASE_URL ?>ppob/settings" title="Pengaturan API PPOB" style="color:rgba(255,255,255,0.85);font-size:1.1rem;padding:6px;line-height:1;background:rgba(255,255,255,0.15);border-radius:var(--radius-sm);border:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;transition:all var(--transition-fast);" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Category Grid -->
        <div class="section-title">Pilih Layanan</div>
        <div class="category-grid">
            <div class="category-card" onclick="openCategory('pulsa','Pulsa')">
                <div class="category-icon" style="color:#ef4444;"><i class="bi bi-phone"></i></div>
                <div class="category-label">Pulsa</div>
            </div>
            <div class="category-card" onclick="openCategory('data','Paket Data')">
                <div class="category-icon" style="color:#3b82f6;"><i class="bi bi-wifi"></i></div>
                <div class="category-label">Data</div>
            </div>
            <div class="category-card" onclick="openCategory('pln','Token PLN')">
                <div class="category-icon" style="color:#eab308;"><i class="bi bi-lightning-charge"></i></div>
                <div class="category-label">PLN</div>
            </div>
            <div class="category-card" onclick="openCategory('ewallet','E-Wallet')">
                <div class="category-icon" style="color:#06b6d4;"><i class="bi bi-wallet"></i></div>
                <div class="category-label">E-Wallet</div>
            </div>
            <div class="category-card" onclick="openCategory('bpjs','BPJS')">
                <div class="category-icon" style="color:#10b981;"><i class="bi bi-hospital"></i></div>
                <div class="category-label">BPJS</div>
            </div>
            <div class="category-card" onclick="openCategory('game','Voucher Game')">
                <div class="category-icon" style="color:#8b5cf6;"><i class="bi bi-controller"></i></div>
                <div class="category-label">Game</div>
            </div>
            <div class="category-card" onclick="openCategory('multifinance','Angsuran')">
                <div class="category-icon" style="color:#f97316;"><i class="bi bi-building"></i></div>
                <div class="category-label">Angsuran</div>
            </div>
            <div class="category-card" onclick="openCategory('bank','Transfer Bank')">
                <div class="category-icon" style="color:var(--text-muted);"><i class="bi bi-bank"></i></div>
                <div class="category-label">Transfer</div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="section-title mb-0">Transaksi Terakhir</div>
            <div style="display:flex;align-items:center;gap:12px;">
                <?php if (in_array($currentUser['level'] ?? '', ['superadmin', 'admin'])): ?>
                <a href="<?= BASE_URL ?>ppob/settings" style="color:var(--text-muted);text-decoration:none;font-size:var(--font-size-xs);font-weight:600;display:flex;align-items:center;gap:4px;" title="Pengaturan API">
                    <i class="bi bi-gear"></i> Pengaturan
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>ppob/history" style="color:var(--primary);text-decoration:none;font-size:var(--font-size-xs);font-weight:700;display:flex;align-items:center;gap:3px;">
                    Lihat Semua <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        <div class="card card-custom p-0 overflow-hidden">
            <ul class="list-group list-group-flush" id="recent-transactions">
                <li class="list-group-item text-center py-4" style="font-size:var(--font-size-sm);color:var(--text-muted);">
                    <span class="spinner-border spinner-border-sm me-2" style="color:var(--primary);"></span>Memuat...
                </li>
            </ul>
        </div>
    </div>

    <!-- Category Detail View -->
    <div id="category-view">
        <div class="d-flex align-items-center mb-4">
            <button class="btn btn-icon me-3" onclick="closeCategory()" style="background:var(--surface-2);color:var(--text-primary);border:1px solid var(--border-color);">
                <i class="bi bi-arrow-left"></i>
            </button>
            <div>
                <h5 class="m-0 fw-bold" id="category-title" style="font-family:var(--font-family);font-size:var(--font-size-md);">Nama Kategori</h5>
            </div>
        </div>

        <div class="card card-custom mb-3">
            <div class="input-wrapper mb-0">
                <input type="text" id="customer-no" class="ppob-input" placeholder="Masukkan Nomor Tujuan" oninput="handleCustomerNoInput()">
                <span class="input-suffix-icon" onclick="openContacts()" title="Pilih dari kontak">
                    <i class="bi bi-person-lines-fill"></i>
                </span>
            </div>
            <!-- Inquiry Result -->
            <div id="inquiry-result" class="inquiry-box" style="margin-top:10px;"></div>
            <!-- Brand Selection -->
            <div id="brand-selection" style="display:none;margin-top:14px;">
                <label style="font-size:var(--font-size-xs);font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px;">Provider / Layanan</label>
                <div class="select-wrapper">
                    <select class="ppob-select" id="brand-select" onchange="loadProducts()">
                        <option value="">Pilih...</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading state -->
        <div id="product-loading" class="text-center py-4" style="display:none;">
            <div class="spinner-border spinner-border-sm" style="color:var(--primary);" role="status"></div>
            <div class="mt-2" style="font-size:var(--font-size-sm);color:var(--text-muted);">Memuat produk...</div>
        </div>

        <!-- Product Grid -->
        <div class="product-grid" id="product-list"></div>
    </div>
</div>

<!-- Modal Konfirmasi Transaksi -->
<div class="modal fade" id="trxModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-ppob">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <h5 style="margin:0;font-weight:700;color:#fff;font-size:var(--font-size-md);font-family:var(--font-family);">Konfirmasi Transaksi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size:0.8rem;"></button>
                </div>
            </div>
            <div style="padding:20px;">
                <div class="text-center mb-3">
                    <div class="fw-bold" id="confirm-product-name" style="font-size:var(--font-size-lg);color:var(--text-primary);font-family:var(--font-family);">Product Name</div>
                    <div style="font-size:var(--font-size-sm);color:var(--text-muted);margin-top:4px;" id="confirm-brand">Brand</div>
                </div>
                <div class="modal-detail-box mb-4">
                    <div class="modal-detail-row">
                        <span class="label">No. Tujuan</span>
                        <span class="value" id="confirm-target">-</span>
                    </div>
                    <div class="modal-detail-row" id="confirm-name-row" style="display:none;">
                        <span class="label">Nama Pelanggan</span>
                        <span class="value" id="confirm-name">-</span>
                    </div>
                    <hr style="border-color:var(--border-color);margin:10px 0;">
                    <div class="modal-detail-row">
                        <span class="label">Total Bayar</span>
                        <span class="value" id="confirm-price" style="color:var(--primary);font-size:var(--font-size-lg);">Rp0</span>
                    </div>
                </div>
                <button class="btn-ppob-primary" id="btn-process-trx" onclick="processTransaction()">
                    Proses Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hasil Transaksi -->
<div class="modal fade" id="resultModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div style="padding:24px 20px;text-align:center;">
                <div id="result-icon" style="font-size:52px;margin-bottom:12px;"></div>
                <h4 class="fw-bold mb-1" id="result-title" style="font-family:var(--font-family);color:var(--text-primary);">Status</h4>
                <p id="result-desc" style="font-size:var(--font-size-sm);color:var(--text-muted);margin-bottom:16px;">Message</p>
                
                <div class="modal-detail-box text-start mb-4">
                    <div style="font-size:var(--font-size-xs);color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;font-weight:700;">Serial Number / Token</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="fw-bold" id="result-sn" style="font-size:var(--font-size-base);word-break:break-all;color:var(--text-primary);">-</div>
                        <button onclick="copySN()" style="background:var(--surface-3);border:1px solid var(--border-color);color:var(--text-secondary);border-radius:var(--radius-sm);padding:6px 10px;cursor:pointer;flex-shrink:0;font-size:13px;transition:all var(--transition-fast);" title="Copy SN">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn-ppob-secondary flex-fill" data-bs-dismiss="modal" onclick="closeCategory()">Tutup</button>
                    <button class="btn-ppob-primary flex-fill" id="btn-print-receipt" onclick="promptPrintReceipt()" style="padding:11px 20px;">
                        <i class="bi bi-printer me-1"></i> Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Harga Jual (Cetak Struk) -->
<div class="modal fade" id="printReceiptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid var(--border-color);background:var(--surface-1);">
                <h6 class="modal-title fw-bold" style="font-family:var(--font-family);">Harga Cetak Struk</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:12px;"></button>
            </div>
            <div class="modal-body text-center" style="padding:20px;">
                <p style="font-size:11px;color:var(--text-muted);margin-bottom:12px;">Sesuaikan harga jual (Total Bayar) yang akan dicetak di struk fisik/PDF pelanggan.</p>
                <div class="input-group mb-3">
                    <span class="input-group-text" style="background:var(--surface-2);border-color:var(--border-color);font-size:14px;font-weight:600;">Rp</span>
                    <input type="number" id="input-print-price" class="form-control" style="font-size:16px;font-weight:700;text-align:right;" autofocus>
                </div>
                <button class="btn-ppob-primary w-100" onclick="executePrintReceipt()">
                    <i class="bi bi-printer me-1"></i> Cetak Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentCategory = '';
    let currentProducts = [];
    let selectedProduct = null;
    let customerName = null;
    let debounceTimer;
    let isProcessing = false;
    let lastTransaction = null;

    const OPERATOR_PREFIX = {
        'TELKOMSEL': ['0811','0812','0813','0821','0822','0823','0851','0852','0853'],
        'INDOSAT':   ['0814','0815','0816','0855','0856','0857','0858'],
        'XL':        ['0817','0818','0819','0859','0877','0878'],
        'AXIS':      ['0838','0831','0832','0833'],
        'TRI':       ['0895','0896','0897','0898','0899'],
        'SMARTFREN': ['0881','0882','0883','0884','0885','0886','0887','0888','0889']
    };

    document.addEventListener('DOMContentLoaded', () => {
        loadBalance();
        loadRecentTransactions();
    });

    async function loadBalance() {
        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/balance');
            const data = await res.json();
            if (data.success && data.data && data.data.deposit !== undefined) {
                document.getElementById('digi-balance').textContent = 'Rp ' + parseInt(data.data.deposit).toLocaleString('id-ID');
            } else {
                document.getElementById('digi-balance').textContent = 'Error';
            }
        } catch (e) {
            document.getElementById('digi-balance').textContent = 'Offline';
        }
    }

    async function loadRecentTransactions() {
        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/transactions?limit=5');
            const data = await res.json();
            const list = document.getElementById('recent-transactions');
            list.innerHTML = '';
            if (data.success && data.data.length > 0) {
                data.data.forEach(trx => {
                    const isSuccess = trx.status === 'success';
                    const isPending = trx.status === 'pending' || trx.status === 'processing';
                    const iconColor = isSuccess ? 'var(--success)' : isPending ? 'var(--warning)' : 'var(--danger)';
                    const icon = isSuccess ? 'check-circle-fill' : isPending ? 'clock-fill' : 'x-circle-fill';
                    const label = isSuccess ? 'SUKSES' : isPending ? 'DIPROSES' : 'GAGAL';
                    list.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-3">
                            <div style="min-width:0;flex:1;">
                                <div class="fw-bold text-truncate" style="font-size:var(--font-size-xs);font-family:var(--font-family);color:var(--text-primary);">${trx.product_name}</div>
                                <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">${trx.customer_no} · ${trx.created_at}</div>
                            </div>
                            <div class="text-end ms-3 flex-shrink-0">
                                <div style="font-size:10px;font-weight:700;color:${iconColor};display:flex;align-items:center;gap:3px;justify-content:flex-end;">
                                    <i class="bi bi-${icon}"></i> ${label}
                                </div>
                                <div class="fw-bold" style="font-size:var(--font-size-sm);color:var(--text-primary);">Rp${parseInt(trx.sell_price).toLocaleString('id-ID')}</div>
                            </div>
                        </li>`;
                });
            } else {
                list.innerHTML = '<li class="list-group-item text-center py-4" style="font-size:var(--font-size-sm);color:var(--text-muted);">Belum ada transaksi</li>';
            }
        } catch (e) { console.error(e); }
    }

    function openCategory(cat, title) {
        currentCategory = cat;
        document.getElementById('category-title').textContent = title;
        document.getElementById('ppob-view').style.display = 'none';
        document.getElementById('category-view').style.display = 'block';
        document.getElementById('customer-no').value = '';
        document.getElementById('product-list').innerHTML = '';
        document.getElementById('inquiry-result').style.display = 'none';
        document.getElementById('brand-selection').style.display = 'none';
        customerName = null;
        if (cat === 'ewallet' || cat === 'game' || cat === 'multifinance') {
            loadBrands(cat);
        } else if (cat === 'pulsa' || cat === 'data') {
            document.getElementById('customer-no').placeholder = 'Masukkan Nomor HP';
        } else if (cat === 'pln') {
            document.getElementById('customer-no').placeholder = 'Masukkan No. Meter / ID Pelanggan';
        } else {
            loadProducts();
        }
    }

    function closeCategory() {
        document.getElementById('category-view').style.display = 'none';
        document.getElementById('ppob-view').style.display = 'block';
        loadBalance();
        loadRecentTransactions();
    }

    async function loadBrands(cat) {
        try {
            const res = await fetch(`<?= BASE_URL ?>api/ppob/brands/${cat}`);
            const data = await res.json();
            if (data.success) {
                const select = document.getElementById('brand-select');
                select.innerHTML = '<option value="">Pilih Provider...</option>';
                data.data.forEach(brand => {
                    select.innerHTML += `<option value="${brand}">${brand}</option>`;
                });
                document.getElementById('brand-selection').style.display = 'block';
            }
        } catch (e) { console.error(e); }
    }

    function handleCustomerNoInput() {
        clearTimeout(debounceTimer);
        const no = document.getElementById('customer-no').value.replace(/\D/g, '');
        if (currentCategory === 'pulsa' || currentCategory === 'data') {
            if (no.length >= 4) {
                const prefix = no.substring(0, 4);
                let detectedBrand = null;
                for (const [brand, prefixes] of Object.entries(OPERATOR_PREFIX)) {
                    if (prefixes.includes(prefix)) { detectedBrand = brand; break; }
                }
                const inqBox = document.getElementById('inquiry-result');
                if (detectedBrand) {
                    inqBox.style.display = 'block';
                    inqBox.style.background = 'var(--info-bg)';
                    inqBox.style.color = 'var(--info)';
                    inqBox.style.border = '1px solid rgba(76,201,240,0.25)';
                    inqBox.innerHTML = `<div class="d-flex align-items-center gap-2"><i class="bi bi-broadcast-pin"></i><div><div class="fw-bold">${detectedBrand}</div><div style="font-size:10px;opacity:0.8;">Operator terdeteksi</div></div></div>`;
                    loadProducts(detectedBrand);
                } else {
                    inqBox.style.display = 'none';
                    document.getElementById('product-list').innerHTML = '';
                }
            } else {
                document.getElementById('inquiry-result').style.display = 'none';
                document.getElementById('product-list').innerHTML = '';
            }
        } else if (currentCategory === 'pln') {
            if (no.length >= 11) {
                debounceTimer = setTimeout(() => inquiryPLN(no), 1000);
            }
        }
    }

    async function inquiryPLN(no) {
        const inqBox = document.getElementById('inquiry-result');
        inqBox.style.display = 'block';
        inqBox.style.background = 'var(--info-bg)';
        inqBox.style.color = 'var(--info)';
        inqBox.style.border = '1px solid rgba(76,201,240,0.25)';
        inqBox.innerHTML = `<div class="d-flex align-items-center gap-2"><span class="spinner-border spinner-border-sm"></span> Mengecek ID Pelanggan...</div>`;
        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/inquiry-pln', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({customer_no: no})
            });
            const data = await res.json();
            if (data.success && data.data && data.data.name) {
                customerName = data.data.name;
                inqBox.style.background = 'var(--success-bg)';
                inqBox.style.color = 'var(--success)';
                inqBox.style.border = '1px solid rgba(46,196,182,0.25)';
                inqBox.innerHTML = `<div class="fw-bold"><i class="bi bi-person-check-fill me-1"></i>${customerName}</div><div style="font-size:10px;opacity:0.8;margin-top:2px;">Daya: ${data.data.segment_power||'-'} · ID: ${data.data.subscriber_id||no}</div>`;
                loadProducts('PLN');
            } else {
                customerName = null;
                inqBox.style.background = 'var(--danger-bg)';
                inqBox.style.color = 'var(--danger)';
                inqBox.style.border = '1px solid rgba(230,57,70,0.25)';
                inqBox.innerHTML = `<div class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>ID Tidak Ditemukan</div><div style="font-size:10px;opacity:0.8;margin-top:2px;">Periksa kembali nomor pelanggan.</div>`;
                document.getElementById('product-list').innerHTML = '';
            }
        } catch (e) {
            inqBox.style.background = 'var(--warning-bg)';
            inqBox.style.color = 'var(--warning)';
            inqBox.style.border = '1px solid rgba(255,183,3,0.25)';
            inqBox.innerHTML = `<div class="fw-bold"><i class="bi bi-wifi-off me-1"></i>Gagal cek otomatis</div><div style="font-size:10px;opacity:0.8;margin-top:2px;">Pilih nominal token di bawah.</div>`;
            loadProducts('PLN');
        }
    }

    async function loadProducts(brandOverride = null) {
        let brand = brandOverride;
        if (!brand && document.getElementById('brand-selection').style.display !== 'none') {
            brand = document.getElementById('brand-select').value;
            if (!brand) { document.getElementById('product-list').innerHTML = ''; return; }
        }
        document.getElementById('product-loading').style.display = 'block';
        document.getElementById('product-list').innerHTML = '';
        try {
            let url = `<?= BASE_URL ?>api/ppob/products/${currentCategory}`;
            if (brand) url += `?brand=${encodeURIComponent(brand)}`;
            const res = await fetch(url);
            const data = await res.json();
            document.getElementById('product-loading').style.display = 'none';
            if (data.success && data.data.length > 0) {
                currentProducts = data.data;
                const list = document.getElementById('product-list');
                const minPrice = Math.min(...data.data.map(p => parseInt(p.seller_price)));
                data.data.forEach(p => {
                    const price = parseInt(p.seller_price).toLocaleString('id-ID'); // Hanya tampilkan Harga Modal
                    const isCheapest = parseInt(p.seller_price) === minPrice;
                    const desc = p.description ? `<div class="product-desc">${p.description.substring(0, 40)}${p.description.length > 40 ? '...' : ''}</div>` : '';
                    const cutoff = (p.start_cut_off && p.end_cut_off) ? `<div class="badge-cutoff"><i class="bi bi-clock"></i> ${p.start_cut_off} - ${p.end_cut_off}</div>` : '';
                    const cheapestBadge = isCheapest ? '<div class="badge-cheapest">⚡ Termurah</div>' : '';
                    list.innerHTML += `<div class="product-card" onclick='confirmTransaction(${JSON.stringify(p).replace(/'/g, "\\'")})'>
                        ${cheapestBadge}
                        <div class="product-name">${p.product_name}</div>
                        ${desc}
                        <div>
                            <div class="product-modal-price" style="margin-bottom:2px;">Modal</div>
                            <div class="product-price">Rp${price}</div>
                            ${cutoff}
                        </div>
                    </div>`;
                });
            } else {
                document.getElementById('product-list').innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:24px;font-size:var(--font-size-sm);color:var(--text-muted);"><i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:8px;"></i>Produk tidak tersedia</div>`;
            }
        } catch (e) {
            console.error(e);
            document.getElementById('product-loading').style.display = 'none';
        }
    }

    async function confirmTransaction(product) {
        const no = document.getElementById('customer-no').value;
        if (!no) { alert('Silakan isi nomor tujuan terlebih dahulu'); document.getElementById('customer-no').focus(); return; }
        
        selectedProduct = product;
        
        if (product.type === 'postpaid' || product.type === 'pascabayar') {
            const inqBox = document.getElementById('inquiry-result');
            inqBox.style.display = 'block';
            inqBox.style.background = 'var(--info-bg)';
            inqBox.style.color = 'var(--info)';
            inqBox.style.border = '1px solid rgba(76,201,240,0.25)';
            inqBox.innerHTML = `<div class="d-flex align-items-center gap-2"><span class="spinner-border spinner-border-sm"></span> Mengecek Tagihan...</div>`;
            
            try {
                const res = await fetch('<?= BASE_URL ?>api/ppob/inquiry-pasca', {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({sku: product.buyer_sku_code, customer_no: no})
                });
                const data = await res.json();
                if (data.success && data.data && data.data.customer_name) {
                    customerName = data.data.customer_name;
                    selectedProduct.sell_price = data.data.selling_price; 
                    selectedProduct.ref_id = data.data.ref_id;
                    
                    inqBox.style.background = 'var(--success-bg)';
                    inqBox.style.color = 'var(--success)';
                    inqBox.style.border = '1px solid rgba(46,196,182,0.25)';
                    inqBox.innerHTML = `<div class="fw-bold"><i class="bi bi-person-check-fill me-1"></i>${customerName}</div><div style="font-size:10px;opacity:0.8;margin-top:2px;">Tagihan ditemukan.</div>`;
                    
                    showConfirmModal(selectedProduct, no);
                } else {
                    customerName = null;
                    inqBox.style.background = 'var(--danger-bg)';
                    inqBox.style.color = 'var(--danger)';
                    inqBox.style.border = '1px solid rgba(230,57,70,0.25)';
                    inqBox.innerHTML = `<div class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Gagal Cek Tagihan</div><div style="font-size:10px;opacity:0.8;margin-top:2px;">${data.message || 'Tagihan tidak ditemukan atau sudah dibayar.'}</div>`;
                }
            } catch (e) {
                inqBox.style.background = 'var(--danger-bg)';
                inqBox.style.color = 'var(--danger)';
                inqBox.style.border = '1px solid rgba(230,57,70,0.25)';
                inqBox.innerHTML = `<div class="fw-bold"><i class="bi bi-wifi-off me-1"></i>Gagal koneksi</div>`;
            }
        } else {
            showConfirmModal(selectedProduct, no);
        }
    }

    function showConfirmModal(product, no) {
        document.getElementById('confirm-product-name').textContent = product.product_name;
        document.getElementById('confirm-brand').textContent = product.brand;
        document.getElementById('confirm-target').textContent = no;
        document.getElementById('confirm-price').textContent = 'Rp ' + parseInt(product.sell_price).toLocaleString('id-ID');
        const nameRow = document.getElementById('confirm-name-row');
        if (customerName) { document.getElementById('confirm-name').textContent = customerName; nameRow.style.display = 'flex'; }
        else { nameRow.style.display = 'none'; }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('trxModal')).show();
    }

    async function processTransaction() {
        if (isProcessing) return;
        const no = document.getElementById('customer-no').value;
        const btn = document.getElementById('btn-process-trx');
        isProcessing = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
        btn.disabled = true;
        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/transaction', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    sku: selectedProduct.buyer_sku_code, 
                    customer_no: no, 
                    customer_name: customerName,
                    ref_id: selectedProduct.ref_id,
                    sell_price: selectedProduct.sell_price
                })
            });
            const data = await res.json();
            const trxModal = bootstrap.Modal.getInstance(document.getElementById('trxModal'));
            if (trxModal) trxModal.hide();
            const rIcon = document.getElementById('result-icon');
            const rTitle = document.getElementById('result-title');
            if (data.success && data.data) {
                lastTransaction = {...data.data, product_name: selectedProduct.product_name, customer_no: no, customer_name: customerName, sell_price: data.data.sell_price || selectedProduct.sell_price};
                const status = (data.data.status||'').toLowerCase();
                if (status === 'sukses') { 
                    rIcon.innerHTML = '<i class="bi bi-check-circle-fill" style="color:var(--success);"></i>'; 
                    rTitle.textContent = 'Transaksi Sukses'; 
                } else if (status === 'pending') { 
                    rIcon.innerHTML = '<i class="bi bi-clock-fill" style="color:var(--warning);"></i>'; 
                    rTitle.textContent = 'Sedang Diproses'; 
                    // Start polling
                    if (data.data.ref_id) pollTransactionStatus(data.data.ref_id);
                } else { 
                    rIcon.innerHTML = '<i class="bi bi-x-circle-fill" style="color:var(--danger);"></i>'; 
                    rTitle.textContent = 'Transaksi Gagal'; 
                }
                document.getElementById('result-desc').textContent = data.data.message || '';
                document.getElementById('result-sn').textContent = data.data.sn || '-';
            } else {
                rIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill" style="color:var(--danger);"></i>';
                rTitle.textContent = 'Gagal';
                document.getElementById('result-desc').textContent = data.message || 'Terjadi kesalahan sistem';
                document.getElementById('result-sn').textContent = '-';
                lastTransaction = null;
            }
            bootstrap.Modal.getOrCreateInstance(document.getElementById('resultModal')).show();
        } catch (e) { console.error(e); alert('Gagal memproses transaksi. Cek koneksi internet.'); }
        finally { isProcessing = false; btn.innerHTML = 'Proses Sekarang'; btn.disabled = false; }
    }

    let pollCount = 0;
    let pollTimer = null;
    async function pollTransactionStatus(refId) {
        pollCount = 0;
        clearInterval(pollTimer);
        pollTimer = setInterval(async () => {
            pollCount++;
            if (pollCount > 24) { clearInterval(pollTimer); return; } // max 2 minutes
            try {
                const res = await fetch(`<?= BASE_URL ?>api/ppob/transaction/${refId}`);
                const data = await res.json();
                if (data.success && data.data) {
                    const status = data.data.status;
                    if (status === 'success') {
                        clearInterval(pollTimer);
                        document.getElementById('result-icon').innerHTML = '<i class="bi bi-check-circle-fill" style="color:var(--success);"></i>';
                        document.getElementById('result-title').textContent = 'Transaksi Sukses!';
                        document.getElementById('result-desc').textContent = data.data.message || 'Pembayaran berhasil diproses.';
                        document.getElementById('result-sn').textContent = data.data.sn || '-';
                        if (lastTransaction) lastTransaction.sn = data.data.sn || lastTransaction.sn;
                        loadRecentTransactions();
                    } else if (status === 'failed') {
                        clearInterval(pollTimer);
                        document.getElementById('result-icon').innerHTML = '<i class="bi bi-x-circle-fill" style="color:var(--danger);"></i>';
                        document.getElementById('result-title').textContent = 'Transaksi Gagal';
                        document.getElementById('result-desc').textContent = data.data.message || 'Transaksi ditolak.';
                    }
                }
            } catch(e) { /* ignore polling error */ }
        }, 5000); // poll every 5 seconds
    }

    function copySN() {
        const sn = document.getElementById('result-sn').textContent;
        if (sn && sn !== '-') { navigator.clipboard.writeText(sn); alert('SN berhasil disalin!'); }
    }
    function openContacts() {
        if (window.AndroidInterface && window.AndroidInterface.pickContact) window.AndroidInterface.pickContact();
        else alert('Fitur ini hanya tersedia di aplikasi Android.');
    }
    function onContactPicked(number) {
        let no = number.replace(/\D/g, '');
        if (no.startsWith('62')) no = '0' + no.substring(2);
        document.getElementById('customer-no').value = no;
        handleCustomerNoInput();
    }
    
    function promptPrintReceipt() {
        if (!lastTransaction) return;
        const defaultPrice = lastTransaction.sell_price || selectedProduct?.sell_price || 0;
        document.getElementById('input-print-price').value = parseInt(defaultPrice);
        const modal = new bootstrap.Modal(document.getElementById('printReceiptModal'));
        modal.show();
    }

    function executePrintReceipt() {
        if (!lastTransaction) return;
        const printPrice = document.getElementById('input-print-price').value;
        lastTransaction.custom_print_price = printPrice;
        
        bootstrap.Modal.getInstance(document.getElementById('printReceiptModal')).hide();
        
        const date = new Date().toLocaleString('id-ID');
        const priceStr = parseInt(printPrice).toLocaleString('id-ID');
        const win = window.open('', '_blank');
        win.document.write(`
            <html>
            <head><title>Struk Transaksi</title>
            <style>
                body { font-family: monospace; padding: 20px; font-size:12px; width: 300px; margin: 0 auto; color:#000; }
                .center { text-align: center; }
                .line { border-bottom: 1px dashed #000; margin: 10px 0; }
                .row { display: flex; justify-content: space-between; margin-bottom: 4px; }
            </style>
            </head>
            <body>
                <div class="center">
                    <h3 style="margin:0 0 5px;">ALFAREZ MART</h3>
                    <div>Struk Pembayaran PPOB</div>
                    <div style="font-size:10px;">${date}</div>
                </div>
                <div class="line"></div>
                <div class="row"><span>Produk:</span><span>${lastTransaction.product_name}</span></div>
                <div class="row"><span>No Tujuan:</span><span>${lastTransaction.customer_no}</span></div>
                ${lastTransaction.customer_name ? `<div class="row"><span>Nama:</span><span>${lastTransaction.customer_name}</span></div>` : ''}
                <div class="row"><span>SN/Token:</span><span>${lastTransaction.sn || '-'}</span></div>
                <div class="row"><span>Status:</span><span>SUKSES</span></div>
                <div class="line"></div>
                <div class="row" style="font-weight:bold;font-size:14px;"><span>Total Bayar:</span><span>Rp ${priceStr}</span></div>
                <div class="line"></div>
                <div class="center" style="margin-top:20px;">Terima Kasih</div>
                <scr` + `ipt>window.print(); window.onafterprint = () => window.close();</scr` + `ipt>
            </body>
            </html>
        `);
    }

    function printReceipt() {
        promptPrintReceipt();
    }
</script>
