<script>
    const csrfToken = '<?= $csrfToken ?? "" ?>';
</script>
<style>
/* =========================================================
   PPOB Premium Design System
========================================================= */
.ppob-wrapper {
    max-width: 1000px;
    margin: 0 auto;
    font-family: var(--font-family);
}

/* Redesigned Premium Hero Section */
.ppob-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    padding: 18px 26px;
    position: relative;
    overflow: hidden;
    color: white;
    box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.5);
    display: flex;
    flex-direction: column;
    margin-bottom: 25px;
    gap: 14px;
    transition: all 0.3s ease;
}

.ppob-hero::before {
    content: '';
    position: absolute;
    top: -50%; left: -10%;
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.12) 0%, transparent 70%);
    pointer-events: none;
}

.ppob-hero::after {
    content: '';
    position: absolute;
    bottom: -30%; right: -5%;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.12) 0%, transparent 70%);
    pointer-events: none;
}

/* Hero left section: logo + balance */
.hero-left {
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
    z-index: 2;
    flex: 1;
    min-width: 0;
}

.hero-logo-pure {
    width: 88px;
    height: 88px;
    object-fit: contain;
    flex-shrink: 0;
    filter: drop-shadow(0 4px 12px rgba(0,0,0,0.25));
}

.hero-balance-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    text-align: right;
    min-width: 0;
}

.hero-label-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(255,255,255,0.5);
    white-space: nowrap;
}

.hero-balance-row {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: flex-end;
}

.hero-balance-amount {
    font-size: 18px;
    font-weight: 800;
    margin: 0;
    letter-spacing: -0.5px;
    background: linear-gradient(to right, #ffffff, #e2e8f0);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    white-space: nowrap;
}

/* Hero right section: wallet icon */
.hero-right {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    z-index: 2;
    flex-shrink: 0;
}

.hero-icon-container {
    width: 48px;
    height: 48px;
    background: rgba(56, 189, 248, 0.1);
    border: 1px solid rgba(56, 189, 248, 0.25);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #38bdf8;
    box-shadow: 0 8px 20px -6px rgba(56, 189, 248, 0.3);
}

.btn-riwayat-premium {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: var(--text-color, #fff);
    font-weight: 600;
    font-size: 14px;
    padding: 12px 20px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    height: 48px; /* Match Top Up button height */
}

html[data-bs-theme="light"] .btn-riwayat-premium {
    color: #1e293b;
    border-color: #cbd5e1;
    background: #f8fafc;
}

.btn-riwayat-premium:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

html[data-bs-theme="light"] .btn-riwayat-premium:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

.btn-riwayat-premium i {
    font-size: 16px;
}

/* hero-top-row: left+right side by side */
.hero-top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    position: relative;
    z-index: 2;
    gap: 12px;
}

/* Elegant Price Sync Button */
.btn-sync-prices {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-sync-prices:hover {
    background: rgba(56, 189, 248, 0.15);
    border-color: rgba(56, 189, 248, 0.4);
    color: #38bdf8;
    transform: scale(1.08);
}

.btn-sync-prices i {
    font-size: 16px;
    line-height: 1;
    display: inline-block;
}

.btn-sync-prices.spinning i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Premium Top Up Button */
.hero-actions-container {
    position: relative;
    z-index: 2;
}

.btn-topup-premium {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    padding: 11px 22px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 13.5px;
    letter-spacing: 0.5px;
    box-shadow: 0 10px 20px -8px rgba(37, 99, 235, 0.4);
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.btn-topup-premium:hover {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    transform: translateY(-2px);
    box-shadow: 0 12px 24px -6px rgba(37, 99, 235, 0.5);
}

.btn-topup-premium:active {
    transform: translateY(0);
}

/* Bank Selection Cards */
.bank-option {
    display: none;
}
.bank-card {
    border: 1.5px solid var(--border-color, #e2e8f0);
    border-radius: 12px;
    padding: 14px 8px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background: var(--surface-1);
    text-align: center;
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}
.bank-card:hover {
    border-color: var(--primary);
    background: var(--surface-2);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.05);
}
.bank-option:checked + .bank-card {
    border-color: var(--primary);
    background: rgba(37, 99, 235, 0.08);
    box-shadow: inset 0 0 0 1px var(--primary);
}
.bank-option:checked + .bank-card::before {
    content: '\F26A'; /* bi-check-circle-fill */
    font-family: bootstrap-icons;
    position: absolute;
    top: 6px;
    right: 6px;
    color: var(--primary);
    font-size: 13px;
}
.bank-logo {
    font-weight: 800;
    font-size: 13px;
    letter-spacing: 0.5px;
}
.bank-desc {
    font-size: 9px;
    color: var(--text-muted);
    margin-top: 4px;
    font-weight: 500;
}

@media (max-width: 480px) {
    .ppob-hero {
        padding: 18px 20px;
        gap: 14px;
    }

    .hero-logo-pure {
        width: 44px;
        height: 44px;
    }

    .hero-balance-amount {
        font-size: 16px;
    }
}

/* Category Grid */
.section-title {
    font-weight: 700;
    font-size: 15px;
    margin-bottom: 15px;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(85px, 1fr));
    gap: 12px;
    margin-bottom: 25px;
}
.cat-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 16px 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.cat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px -4px rgba(0,0,0,0.1);
    border-color: var(--primary);
}
.cat-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.cat-icon.blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.cat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.cat-icon.orange { background: rgba(249, 115, 22, 0.1); color: #f97316; }
.cat-icon.green { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.cat-name {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.2;
}

/* Testing Mode Alert */
.dev-alert {
    background: rgba(245, 158, 11, 0.1);
    border: 1px dashed #f59e0b;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.dev-alert-text {
    color: #f59e0b;
    font-size: 14px;
}
.dev-alert .btn-sm {
    background: #f59e0b;
    color: white;
    border: none;
}

/* Form Styles */
.glass-input {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    width: 100%;
    color: var(--text-primary);
}
.glass-input::placeholder {
    color: var(--text-muted);
    opacity: 0.8;
}
.glass-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 15px;
}
@media (min-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }
}
.prod-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 90px;
    position: relative;
    overflow: hidden;
}
.prod-card:hover {
    border-color: var(--primary);
    background: var(--surface-2);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}
.prod-name { font-size: 11.5px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; line-height: 1.35; letter-spacing: -0.2px; }
.prod-desc { font-size: 9px; color: var(--text-muted); margin-bottom: 8px; line-height: 1.35; white-space: pre-wrap; word-break: break-word; }
.prod-price { font-size: 13px; font-weight: 800; color: var(--primary); margin-top: auto; }

/* Inquriy Result Box */
.inquiry-box {
    background: var(--surface-2);
    border: 1px dashed var(--border-color);
    border-radius: 12px;
    padding: 20px;
    margin-top: 15px;
    display: none;
}
.inq-label { font-size: 12px; color: var(--text-muted); }
.inq-value { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px; }

/* Custom Modal Animations & Styling */
.modal-content {
    background: var(--bg-modal) !important;
    color: var(--text-primary);
}
.modal.fade .modal-dialog {
    transform: scale(0.95);
    transition: transform 0.3s ease-out;
}
.modal.show .modal-dialog {
    transform: scale(1);
}
.modal-content {
    background: var(--bg-modal) !important;
    color: var(--text-primary) !important;
}
.list-group-item {
    background: var(--surface-1);
    color: var(--text-primary);
    border-color: var(--border-color);
}
.list-group-item:hover {
    background: var(--surface-2);
    color: var(--primary);
}
.text-muted {
    color: var(--text-muted) !important;
}

/* Light theme overrides for PPOB */
:root[data-theme="light"] .btn-close { filter: none; }
:root[data-theme="light"] .glass-input { background: var(--surface-2); }
:root[data-theme="light"] .form-select.glass-input { background: var(--surface-2); }
:root[data-theme="light"] .ppob-hero { 
    background: rgba(255, 255, 255, 0.65) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(200, 210, 230, 0.6) !important;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.07), 0 1px 6px rgba(0, 0, 0, 0.04) !important;
}
:root[data-theme="light"] .hero-label-title { color: #64748b !important; }
:root[data-theme="light"] .hero-balance-amount { 
    background: linear-gradient(to right, #0f172a, #334155) !important;
    -webkit-background-clip: text !important;
    background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
}
:root[data-theme="light"] .btn-sync-prices {
    background: rgba(15, 23, 42, 0.06) !important;
    border: 1px solid rgba(15, 23, 42, 0.1) !important;
    color: #475569 !important;
}
:root[data-theme="light"] .btn-sync-prices:hover {
    background: rgba(37, 99, 235, 0.1) !important;
    border-color: rgba(37, 99, 235, 0.3) !important;
    color: #2563eb !important;
}
:root[data-theme="light"] .hero-icon-container {
    background: rgba(37, 99, 235, 0.08) !important;
    border: 1px solid rgba(37, 99, 235, 0.2) !important;
    color: #2563eb !important;
    box-shadow: 0 8px 20px -6px rgba(37, 99, 235, 0.12) !important;
}
:root[data-theme="light"] .hero-logo-pure {
    filter: none !important;
}
:root[data-theme="light"] .alert-info { background: var(--info-bg); border-color: rgba(37,99,235,0.3); color: var(--info); }
:root[data-theme="light"] .prod-card { background: var(--surface-2); }
:root[data-theme="light"] .prod-card:hover { background: var(--surface-3); }
</style>

<div class="container-fluid py-4 ppob-wrapper">

    <!-- Hero / Balance Section -->
    <div class="ppob-hero">
        <div class="hero-top-row">
            <!-- Left: Pure Logo only -->
            <div class="hero-left">
                <img src="<?= BASE_URL ?>public/images/Icon.png" alt="AlfarezMart" class="hero-logo-pure">
            </div>

            <!-- Right: Balance Info + Wallet Icon -->
            <div class="hero-right">
                <div class="hero-balance-info">
                    <span class="hero-label-title">Saldo Digiflazz</span>
                    <div class="hero-balance-row">
                        <h2 class="hero-balance-amount" id="live-balance">
                            <span class="spinner-border spinner-border-sm"></span>
                        </h2>
                        <?php if (in_array($_SESSION['user_level'] ?? '', ['superadmin', 'admin'])): ?>
                        <button class="btn-sync-prices" onclick="triggerPriceSync(this)" title="Sinkronisasi Harga Digiflazz" id="btn-sync-prices-icon">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hero-icon-container">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>

        <div class="hero-actions-container mt-3 w-100 d-flex gap-2">
            <button class="btn-topup-premium flex-grow-1 justify-content-center" onclick="openDepositModal()">
                <i class="bi bi-plus-circle-fill"></i> Top Up Saldo
            </button>
            <button class="btn-riwayat-premium" onclick="openDepositHistoryModal()">
                <i class="bi bi-clock-history"></i> Riwayat
            </button>
        </div>
    </div>

    <!-- Development Mode Notice -->
    <?php if (isset($mode) && $mode === 'development'): ?>
    <div class="dev-alert">
        <div class="dev-alert-text">
            <strong><i class="bi bi-bug me-1"></i> Sandbox Mode Aktif!</strong> Transaksi tidak akan memotong saldo asli.
        </div>
        <button class="btn btn-sm rounded-pill px-3" onclick="openTestCaseModal()"><i class="bi bi-magic me-1"></i>Bantuan Test</button>
    </div>
    <?php endif; ?>

    <!-- Prabayar Section -->
    <h4 class="section-title">Isi Ulang & Prabayar</h4>
    <div class="cat-grid">
        <div class="cat-card" onclick="openTrxModal('Pulsa', 'pulsa', 'prepaid')">
            <div class="cat-icon blue"><i class="bi bi-phone"></i></div>
            <div class="cat-name">Pulsa</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('Data', 'data', 'prepaid')">
            <div class="cat-icon purple"><i class="bi bi-wifi"></i></div>
            <div class="cat-name">Paket Data</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('SMS & Nelpon', 'sms_nelpon', 'prepaid')">
            <div class="cat-icon blue"><i class="bi bi-telephone"></i></div>
            <div class="cat-name">SMS & Nelpon</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('Token PLN', 'pln', 'prepaid')">
            <div class="cat-icon orange"><i class="bi bi-lightning-charge"></i></div>
            <div class="cat-name">Token PLN</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('E-Wallet', 'ewallet', 'prepaid')">
            <div class="cat-icon green"><i class="bi bi-wallet"></i></div>
            <div class="cat-name">E-Wallet</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('Voucher Game', 'game', 'prepaid')">
            <div class="cat-icon purple"><i class="bi bi-controller"></i></div>
            <div class="cat-name">Games</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('TV Voucher', 'tv', 'prepaid')">
            <div class="cat-icon blue"><i class="bi bi-tv"></i></div>
            <div class="cat-name">TV Voucher</div>
        </div>
    </div>

    <!-- Pascabayar Section -->
    <h4 class="section-title mt-4">Tagihan Pascabayar (Bayar Nanti)</h4>
    <div class="cat-grid">
        <div class="cat-card" onclick="openTrxModal('PLN Pascabayar', 'pln', 'postpaid')">
            <div class="cat-icon orange"><i class="bi bi-lightning"></i></div>
            <div class="cat-name">PLN Pasca</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('BPJS', 'bpjs', 'postpaid')">
            <div class="cat-icon green"><i class="bi bi-heart-pulse"></i></div>
            <div class="cat-name">BPJS</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('PDAM', 'pdam', 'postpaid')">
            <div class="cat-icon blue"><i class="bi bi-droplet"></i></div>
            <div class="cat-name">PDAM</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('Multifinance', 'multifinance', 'postpaid')">
            <div class="cat-icon purple"><i class="bi bi-car-front"></i></div>
            <div class="cat-name">Cicilan</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('HP Pascabayar', 'hp', 'postpaid')">
            <div class="cat-icon blue"><i class="bi bi-phone-vibrate"></i></div>
            <div class="cat-name">HP Pasca</div>
        </div>
        <div class="cat-card" onclick="openTrxModal('TV & Internet', 'tv', 'postpaid')">
            <div class="cat-icon orange"><i class="bi bi-router"></i></div>
            <div class="cat-name">TV & Internet</div>
        </div>
    </div><!-- end .cat-grid Pascabayar -->

    <!-- Admin Tools Section -->
    <h4 class="section-title mt-4">PPOB Admin Tools</h4>
    <div class="row g-3 mb-5">
        <div class="col-6 col-md">
            <a href="<?= BASE_URL ?>ppob/customers" class="btn btn-outline-danger w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-person-lines-fill d-block fs-4 mb-1"></i> Pelanggan
            </a>
        </div>
        <div class="col-6 col-md">
            <a href="<?= BASE_URL ?>ppob/summary" class="btn btn-outline-info w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-bar-chart-line-fill d-block fs-4 mb-1"></i> Analytics
            </a>
        </div>
        <div class="col-6 col-md">
            <a href="<?= BASE_URL ?>ppob/prices" class="btn btn-outline-primary w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-tags d-block fs-4 mb-1"></i> Daftar Harga
            </a>
        </div>
        <div class="col-6 col-md">
            <a href="<?= BASE_URL ?>ppob/history" class="btn btn-outline-success w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-clock-history d-block fs-4 mb-1"></i> Histori Transaksi
            </a>
        </div>
        <div class="col-6 col-md">
            <a href="<?= BASE_URL ?>ppob/settings" class="btn btn-outline-warning w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-gear d-block fs-4 mb-1"></i> Pengaturan
            </a>
        </div>
        <div class="col-6 col-md">
            <a href="<?= BASE_URL ?>ppob/docs" class="btn btn-outline-secondary w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-book d-block fs-4 mb-1"></i> API Docs
            </a>
        </div>
    </div>

</div>

<!-- Universal Transaction Modal -->
<div class="modal fade" id="trxModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
            <div class="modal-header border-0" style="background: var(--surface-2);">
                <h5 class="modal-title fw-bold" id="trxModalTitle">Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="trx-type" value="prepaid">
                <input type="hidden" id="trx-category" value="">
                                <!-- Input Section -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase" style="letter-spacing: 0.5px; font-size: 11px;">Nomor Tujuan / Pelanggan</label>
                    <div class="d-flex align-items-center w-100" style="border: 1px solid var(--border-color); border-radius: 12px; background: var(--surface-2); overflow: hidden; padding-right: 8px;">
                        <input type="text" class="form-control border-0 bg-transparent shadow-none flex-grow-1" id="customer-no" placeholder="Masukkan nomor..." style="font-size: 14px; padding: 14px 16px; min-width: 0; color: var(--text-primary);">
                        
                        <div id="provider-badge" class="fw-bold flex-shrink-0" style="font-size:10px; display:none; padding:5px 10px; border-radius:8px; background:var(--primary); color:#fff; text-transform:uppercase; letter-spacing:0.5px; white-space: nowrap;"></div>
                        
                        <!-- Contact Button -->
                        <button type="button" class="btn text-primary bg-transparent border-0 px-2 m-0 flex-shrink-0" onclick="openContactBook()" title="Buku Kontak" id="btn-contact-book">
                            <i class="bi bi-person-lines-fill fs-5"></i>
                        </button>
                        <button class="btn btn-primary px-4 fw-bold m-0 flex-shrink-0" id="btn-inquiry" onclick="performInquiry()" style="display:none; border-radius: 8px; font-size:13px; align-self: stretch; margin: 4px 0 4px 4px !important;">Cek</button>
                    </div>
                </div>

                <!-- Box Hasil Inquiry (Nama Pelanggan, Detail Tagihan) -->
                <div id="inquiry-box" class="inquiry-box">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="inq-label">Nama Pelanggan</div>
                            <div class="inq-value" id="inq-name">-</div>
                            <div class="inq-label" id="inq-detail-label" style="display:none;">Detail Tagihan</div>
                            <div class="inq-value" id="inq-detail" style="display:none; font-size:13px; font-weight:normal;">-</div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="inq-label">Total Pembayaran</div>
                            <div class="inq-value text-primary fs-3" id="inq-price">Rp 0</div>
                            <button class="btn btn-success rounded-pill px-4 fw-bold mt-2" id="btn-pay-postpaid" style="display: none;" onclick="payPostpaid()">Bayar Tagihan Sekarang</button>
                        </div>
                    </div>
                </div>

                <!-- Product List -->
                <div id="product-list-container" style="display: none;">
                    
                    <!-- Brand Filter Tabs (For E-Wallet, Game, etc) -->
                    <div id="brand-filter-container" class="mb-3 gap-2 overflow-auto pb-2" style="display:none; white-space:nowrap; scrollbar-width: none;"></div>

                    <!-- Search Product Input (Useful for huge lists) -->
                    <div class="mb-3 position-relative" id="product-search-container" style="display:none;">
                        <input type="text" class="form-control glass-input" id="search-product" placeholder="Cari paket / nominal..." style="padding-left:35px; border-radius:12px;">
                        <i class="bi bi-search position-absolute text-muted" style="left:12px; top:50%; transform:translateY(-50%);"></i>
                    </div>

                    <label class="form-label fw-bold text-muted small text-uppercase mt-3 mb-0" id="label-product">Pilih Produk</label>
                    <div id="product-loading" class="text-center py-4" style="display:none;">
                        <span class="spinner-border text-primary"></span>
                    </div>
                    <div class="product-grid" id="product-grid">
                        <!-- Products injected here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Set Harga Jual Modal -->
<div class="modal fade" id="setPriceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; background: var(--surface-1);">
            <div class="modal-header border-0" style="background: var(--surface-2); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" style="color: var(--text-primary);"><i class="bi bi-gear-fill me-2" style="color: var(--primary);"></i>Atur Harga Jual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: var(--btn-close-filter, invert(1) grayscale(100%) brightness(200%));"></button>
            </div>
            <div class="modal-body p-4" style="color: var(--text-primary);">
                <input type="hidden" id="sp-sku">
                <input type="hidden" id="sp-base-price">
                <div class="mb-4 text-center">
                    <h6 class="fw-bold mb-1" id="sp-product-name" style="color: var(--primary);">Nama Produk</h6>
                    <div class="text-muted small">Harga Modal: <span id="sp-base-price-text" class="fw-bold" style="color: var(--text-primary);">Rp0</span></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1 fw-bold">Harga Jual Baru</label>
                    <div class="d-flex align-items-stretch" style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; background: var(--surface-2);">
                        <div class="d-flex align-items-center justify-content-center px-3 fw-bold text-muted" style="background: rgba(0,0,0,0.03); border-right: 1px solid var(--border-color);">Rp</div>
                        <input type="number" class="form-control border-0 bg-transparent ps-2 fw-bold shadow-none" id="sp-sell-price" placeholder="Masukkan harga jual" style="font-size: 16px; color: var(--text-primary); padding: 12px 15px;">
                    </div>
                    <div class="mt-2 text-end">
                        <small id="sp-profit-preview" class="fw-bold" style="color: var(--success); display: none;">Untung: Rp0</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-secondary rounded-pill fw-bold px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary flex-grow-1 rounded-pill fw-bold" id="btn-save-price"><i class="bi bi-check-circle me-2"></i>Simpan Harga</button>
            </div>
        </div>
    </div>
</div>

<!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-0" style="background: var(--surface-2);">
                <h5 class="modal-title fw-bold"><i class="bi bi-wallet2 me-2"></i>Topup Saldo Digiflazz</h5>
                <button type="button" class="btn btn-sm btn-outline-primary ms-auto me-2 rounded-pill" onclick="openDepositHistoryModal()"><i class="bi bi-clock-history me-1"></i>Riwayat</button>
                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" style="filter: var(--btn-close-filter, invert(1) grayscale(100%) brightness(200%));"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info rounded-3" style="font-size: 13px;">
                    <i class="bi bi-info-circle-fill me-2"></i> Deposit akan langsung masuk ke akun Digiflazz Anda secara otomatis jika transfer sesuai nominal hingga 3 digit terakhir.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nominal Deposit (Min Rp 50.000)</label>
                    <input type="number" class="form-control glass-input" id="depo-amount" placeholder="50000">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small mb-2">Pilih Metode Pembayaran</label>
                    <div class="row g-2" id="depo-bank-options">
                        <div class="col-4">
                            <label class="w-100 mb-0 h-100">
                                <input type="radio" name="depo_bank" value="BCA" class="bank-option" checked>
                                <div class="bank-card">
                                    <div class="bank-logo" style="color:#005E6A;">BCA</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-4">
                            <label class="w-100 mb-0 h-100">
                                <input type="radio" name="depo_bank" value="MANDIRI" class="bank-option">
                                <div class="bank-card">
                                    <div class="bank-logo" style="color:#F2A124;">MANDIRI</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-4">
                            <label class="w-100 mb-0 h-100">
                                <input type="radio" name="depo_bank" value="BRI" class="bank-option">
                                <div class="bank-card">
                                    <div class="bank-logo" style="color:#00529C;">BRI</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-4">
                            <label class="w-100 mb-0 h-100">
                                <input type="radio" name="depo_bank" value="BNI" class="bank-option">
                                <div class="bank-card">
                                    <div class="bank-logo" style="color:#F04A23;">BNI</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-4">
                            <label class="w-100 mb-0 h-100">
                                <input type="radio" name="depo_bank" value="FLIP" class="bank-option">
                                <div class="bank-card">
                                    <div class="bank-logo" style="color:#FD6542;">FLIP</div>
                                    <div class="bank-desc">Bebas Admin</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-4">
                            <label class="w-100 mb-0 h-100">
                                <input type="radio" name="depo_bank" value="SHOPEEPAY" class="bank-option">
                                <div class="bank-card">
                                    <div class="bank-logo" style="color:#EE4D2D;">SHOPEE</div>
                                    <div class="bank-desc">Bebas Admin</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-12 mt-2">
                            <label class="w-100 mb-0">
                                <input type="radio" name="depo_bank" value="GOPAY" class="bank-option">
                                <div class="bank-card flex-row justify-content-center align-items-center py-3">
                                    <div class="bank-logo me-2" style="color:#00A5CF;">GOPAY</div>
                                    <div class="bank-desc mt-0">Bebas Admin</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold small">Nama Pemilik Rekening Anda <span class="text-danger">*</span></label>
                    <input type="text" class="form-control glass-input" id="depo-owner" placeholder="Nama sesuai rekening pentransfer">
                    <div class="form-text mt-2 text-warning" style="font-size: 11px;"><i class="bi bi-info-circle"></i> Wajib diisi (Ketentuan Digiflazz) agar deposit masuk otomatis.</div>
                </div>
                <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold" onclick="requestDeposit()" id="btn-depo">
                    Minta Tiket Deposit
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Deposit History Modal -->
<div class="modal fade" id="depositHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 20px; border: none; background: var(--surface-1); box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 pb-0" style="padding: 24px 24px 16px;">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold" style="font-size: 1.25rem;"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Deposit</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" onclick="fetchDepositHistory()" id="btnRefreshDeposit" style="transition: all 0.2s;">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" style="filter: var(--btn-close-filter, invert(1) grayscale(100%) brightness(200%));"></button>
                    </div>
                </div>
            </div>
            <div class="modal-body p-4 pt-3">
                <div id="deposit-history-body" class="d-flex flex-column gap-3">
                    <!-- Data will be loaded here via JS -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted fw-bold">Memuat riwayat deposit...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deposit Result Modal -->
<div class="modal fade" id="depoResultModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4" style="border-radius: 20px; border: none;">
            <div class="mb-3"><i class="bi bi-check-circle-fill text-success" style="font-size: 50px;"></i></div>
            <h4 class="fw-bold mb-1">Tiket Deposit Berhasil</h4>
            <p class="text-muted small mb-4">Silakan transfer sesuai instruksi di bawah ini.</p>
            
            <div class="rounded-3 p-3 mb-3 text-start" style="background: var(--surface-2);">
                <div class="small text-muted mb-1">Transfer Tepat Sesuai Nominal (Termasuk 3 Digit Terakhir):</div>
                <div class="d-flex align-items-center justify-content-between p-3 rounded mb-3" style="background: rgba(255, 255, 255, 0.05); border: 1px dashed var(--border-active);">
                    <h2 class="text-primary fw-bold mb-0" id="dr-amount" style="letter-spacing: 1px;">Rp 0</h2>
                    <button class="btn btn-sm btn-outline-primary rounded-pill fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('dr-amount').dataset.amount); this.innerText='Disalin!'; setTimeout(()=>this.innerHTML='<i class=\'bi bi-clipboard me-1\'></i>Salin', 2000);"><i class="bi bi-clipboard me-1"></i>Salin</button>
                </div>
                
                <div id="dr-parsed-dest" style="display: none;">
                    <div class="small text-muted mb-2">Tujuan Transfer:</div>
                    <div class="p-3 rounded mb-3 d-flex justify-content-between align-items-center" style="background: rgba(255, 255, 255, 0.05); border: 1px dashed var(--border-active);">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase" id="dr-bank-name">BANK</div>
                            <div class="fw-bold text-primary" id="dr-acc-no" style="font-size: 22px; letter-spacing: 1.5px;">0000000000</div>
                            <div class="text-muted small">a.n. <span class="fw-bold text-light" id="dr-acc-name">NAMA REKENING</span></div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary rounded-pill fw-bold" onclick="navigator.clipboard.writeText(document.getElementById('dr-acc-no').innerText); this.innerText='Disalin!'; setTimeout(()=>this.innerText='Salin', 2000);"><i class="bi bi-clipboard me-1"></i>Salin</button>
                    </div>
                </div>
                
                <div class="small text-muted mb-2">Pesan Sistem / Instruksi Asli:</div>
                <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color);">
                    <div class="fw-bold" id="dr-notes" style="font-size: 14px; line-height: 1.6; color: var(--text-primary);">Memuat instruksi...</div>
                </div>
            </div>
            
            <div class="small text-danger mb-4 fw-bold">
                * Pastikan nominal transfer SAMA PERSIS hingga 3 digit terakhir agar diproses otomatis.
            </div>
            
            <button class="btn btn-secondary w-100 rounded-pill" data-bs-dismiss="modal">Tutup</button>
        </div>
    </div>
</div>

<!-- Test Case Sandbox Helpers Modal -->
<div class="modal fade" id="testCaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0" style="background: var(--warning-bg);">
                <h5 class="modal-title fw-bold" style="color: var(--warning);"><i class="bi bi-magic me-2"></i>Bantuan Test Sandbox</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: var(--btn-close-filter, invert(1) grayscale(100%) brightness(200%));"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted">Klik tombol di bawah untuk menyalin nomor khusus Digiflazz ke kolom input saat transaksi.</p>
                <div class="list-group list-group-flush">
                    <button class="list-group-item list-group-item-action fw-bold text-success" onclick="useTestNo('087800001230')">Simulasi TRX Sukses (087800001230)</button>
                    <button class="list-group-item list-group-item-action fw-bold text-warning" onclick="useTestNo('087800001231')">Simulasi TRX Pending (087800001231)</button>
                    <button class="list-group-item list-group-item-action fw-bold text-danger" onclick="useTestNo('087800001232')">Simulasi TRX Gagal (087800001232)</button>
                    
                    <div class="px-3 py-2 small fw-bold text-muted mt-2" style="background:var(--surface-2);">Testing Inquiry PLN / Pascabayar</div>
                    <button class="list-group-item list-group-item-action fw-bold text-success" onclick="useTestNo('530000000001')">PLN Inq Sukses (530000000001)</button>
                    <button class="list-group-item list-group-item-action fw-bold text-danger" onclick="useTestNo('530000000003')">PLN Inq Gagal (530000000003)</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:20px;border:none;background:var(--surface-1);">
            <div class="modal-body p-4 text-center">
                <div class="mb-3"><i class="bi bi-question-circle text-primary" style="font-size:50px;"></i></div>
                <h5 class="fw-bold mb-3" id="confirmTitle" style="color:var(--text-primary);">Konfirmasi</h5>
                <p class="small mb-4" id="confirmMessage" style="color:var(--text-secondary);"></p>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary flex-grow-1 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary flex-grow-1 rounded-pill fw-bold" id="confirmBtnYes">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save PLN Contact Modal -->
<div class="modal fade" id="savePlnContactModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:20px;border:none;background:var(--surface-1);">
            <div class="modal-body p-4 text-center">
                <div class="mb-3"><i class="bi bi-person-plus-fill text-primary" style="font-size:50px;"></i></div>
                <h5 class="fw-bold mb-3" style="color:var(--text-primary);">Simpan Pelanggan Baru</h5>
                <p class="small mb-3" id="savePlnContactMessage" style="color:var(--text-secondary);"></p>
                
                <div class="mb-4 text-start">
                    <label class="form-label fw-bold small" style="color:var(--text-primary);">Nama Alias <span class="text-danger">*</span></label>
                    <input type="text" class="form-control glass-input" id="savePlnContactAlias" placeholder="Contoh: Rumah Budi">
                    <input type="hidden" id="savePlnContactNo">
                    <input type="hidden" id="savePlnContactDefaultName">
                    <input type="hidden" id="savePlnContactPower">
                </div>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary flex-grow-1 rounded-pill" data-bs-dismiss="modal">Lain Kali</button>
                    <button class="btn btn-primary flex-grow-1 rounded-pill fw-bold" id="btnExecuteSavePln" onclick="executeSavePlnContact()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save E-Wallet Contact Modal -->
<div class="modal fade" id="saveEwalletContactModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:20px;border:none;background:var(--surface-1);">
            <div class="modal-body p-4 text-center">
                <div class="mb-3"><i class="bi bi-wallet2 text-primary" style="font-size:50px;"></i></div>
                <h5 class="fw-bold mb-3" style="color:var(--text-primary);">Simpan Pelanggan Baru</h5>
                <p class="small mb-3" id="saveEwalletContactMessage" style="color:var(--text-secondary);"></p>
                
                <div class="mb-4 text-start">
                    <label class="form-label fw-bold small" style="color:var(--text-primary);">Nama Alias <span class="text-danger">*</span></label>
                    <input type="text" class="form-control glass-input" id="saveEwalletContactAlias" placeholder="Contoh: DANA Budi">
                    <input type="hidden" id="saveEwalletContactNo">
                    <input type="hidden" id="saveEwalletContactName">
                    <input type="hidden" id="saveEwalletContactBrand">
                </div>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary flex-grow-1 rounded-pill" data-bs-dismiss="modal">Lain Kali</button>
                    <button class="btn btn-primary flex-grow-1 rounded-pill fw-bold" id="btnExecuteSaveEwallet" onclick="executeSaveEwalletContact()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PIN Verification Modal -->
<div class="modal fade" id="pinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:20px;border:none;background:var(--surface-1);">
            <div class="modal-body p-4 text-center">
                <div class="mb-3"><i class="bi bi-shield-lock text-warning" style="font-size:50px;"></i></div>
                <h5 class="fw-bold mb-3" style="color:var(--text-primary);">Masukkan PIN</h5>
                <p class="small mb-3" style="color:var(--text-secondary);">Transaksi PPOB ini membutuhkan PIN otorisasi.</p>
                <div class="mb-4 position-relative">
                    <input type="tel" inputmode="numeric" pattern="[0-9]*" class="form-control glass-input text-center fs-4" id="trx-pin-input" placeholder="••••" style="letter-spacing: 5px; -webkit-text-security: disc;">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary flex-grow-1 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-warning flex-grow-1 rounded-pill fw-bold" id="pinBtnVerify">Verifikasi</button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Premium Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 24px; overflow: hidden;">
            <div class="modal-body p-0">
                <div class="text-center py-5 px-4" style="background: var(--surface-1);">
                    <div style="font-size: 60px; line-height: 1; margin-bottom: 15px;" id="result-icon">⏳</div>
                    <div id="result-title" class="fw-bold fs-4 text-warning">Sedang Diproses</div>
                    <div class="text-muted small mt-1 mb-4" id="result-msg">Menunggu konfirmasi dari provider</div>
                    
                    <div style="background: var(--surface-2); border-radius: 16px; padding: 20px; text-align: left; margin-bottom: 20px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Nomor Tujuan</span>
                            <span class="fw-bold" id="result-customer">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Produk</span>
                            <span class="fw-bold" id="result-product">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Harga</span>
                            <span class="fw-bold text-primary" id="result-price">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">SN/Token</span>
                            <span class="fw-bold" id="result-sn" style="max-width: 180px; word-break: break-all; text-align: right;">-</span>
                        </div>
                        <div id="result-pln-details" style="display:none; border-top: 1px dashed var(--border-color); padding-top: 8px; margin-top: 8px;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Nama Mtr</span>
                                <span class="fw-bold" id="result-pln-name" style="text-align: right;">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Tarif/Daya</span>
                                <span class="fw-bold" id="result-pln-power" style="text-align: right;">-</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Jml kWh</span>
                                <span class="fw-bold" id="result-pln-kwh" style="text-align: right;">-</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Ref ID</span>
                            <span class="text-muted" id="result-refid" style="font-size: 11px;">-</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted small">Trx ID</span>
                            <span class="text-muted fw-bold" id="result-trxid" style="font-size: 11px;">-</span>
                        </div>
                    </div>

                    <div class="mb-3" id="custom-price-container" style="display:none; text-align: left; padding: 0 10px;">
                        <label class="form-label small text-muted mb-1 fw-bold">Harga Jual (Bisa Diubah Untuk Struk)</label>
                        <div class="d-flex align-items-stretch" style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; background: var(--surface-2);">
                            <div class="d-flex align-items-center justify-content-center px-3 fw-bold text-muted" style="background: rgba(0,0,0,0.03); border-right: 1px solid var(--border-color);">Rp</div>
                            <input type="number" class="form-control border-0 bg-transparent ps-2 fw-bold shadow-none" id="custom-print-price" placeholder="0" style="font-size: 16px; color: var(--text-primary); padding: 12px 15px;">
                        </div>
                    </div>

                    <div class="row g-2 mb-2" id="result-actions" style="display:none;">
                        <div class="col-12 text-center mb-1">
                            <span id="printer-status-badge" class="badge bg-secondary" style="font-size: 10px; font-weight: 500; display: none;"><i class="bi bi-printer me-1"></i>Printer: Belum Terhubung</span>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-primary w-100 rounded-pill fw-bold py-2" id="btn-print-receipt" onclick="printPpobReceipt()">
                                <i class="bi bi-printer me-1"></i>Cetak
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success w-100 rounded-pill fw-bold py-2" id="btn-share-receipt" onclick="sharePpobReceipt()">
                                <i class="bi bi-share me-1"></i>Kirim
                            </button>
                        </div>
                        <div class="col-12 mt-2">
                            <button class="btn btn-outline-primary w-100 rounded-pill py-2" onclick="previewPpobReceipt()">
                                <i class="bi bi-eye me-1"></i>Preview Web
                            </button>
                        </div>
                    </div>
                    
                    <button class="btn btn-secondary w-100 rounded-pill py-2" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Book Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 20px; border: none; background: var(--surface-1);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Buku Kontak PPOB</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: var(--btn-close-filter, invert(1) grayscale(100%) brightness(200%));"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div class="position-relative mb-3 mt-2">
                    <input type="text" class="form-control glass-input" id="search-contact" placeholder="Cari nama / nomor..." style="padding-left: 35px; border-radius: 12px;">
                    <i class="bi bi-search position-absolute text-muted" style="left:12px; top:50%; transform:translateY(-50%);"></i>
                </div>
                <div id="contact-loading" class="text-center py-4" style="display:none;">
                    <span class="spinner-border text-primary"></span>
                </div>
                <div id="contact-list" class="d-flex flex-column gap-2">
                    <!-- Contacts will be injected here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seller History Modal -->
<div class="modal fade" id="sellerHistoryModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 20px; border: none; background: var(--surface-1);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Riwayat & Analisis Seller: <span id="sh-seller-name" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: var(--btn-close-filter, invert(1) grayscale(100%) brightness(200%));"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <div id="sh-loading" class="text-center py-5">
                    <span class="spinner-border text-primary"></span>
                    <div class="text-muted small mt-2">Memuat riwayat & analitik...</div>
                </div>
                
                <div id="sh-content" style="display:none;">
                    <!-- Analytics -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100" style="background:var(--surface-2); border:1px solid var(--border-color);">
                                <div class="fw-bold mb-3 small text-uppercase text-muted">Success Rate</div>
                                <div class="d-flex align-items-center mb-2">
                                    <h3 class="m-0 me-2" id="sh-stat-total" style="color:var(--text-primary);">0</h3> 
                                    <span class="text-muted small">Total Transaksi</span>
                                </div>
                                <div class="progress mb-2" style="height: 12px; background:var(--danger-bg); border-radius: 6px; overflow: hidden;">
                                    <div class="progress-bar bg-success" id="sh-bar-success" role="progressbar" style="width: 0%"></div>
                                    <div class="progress-bar bg-danger" id="sh-bar-failed" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between small fw-bold">
                                    <span class="text-success" id="sh-txt-success">Sukses: 0 (0%)</span>
                                    <span class="text-danger" id="sh-txt-failed">Gagal: 0 (0%)</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100" style="background:var(--surface-2); border:1px solid var(--border-color);">
                                <div class="fw-bold mb-3 small text-uppercase text-muted">Kategori Transaksi</div>
                                <div id="sh-cat-bars" class="d-flex flex-column gap-2">
                                    <!-- Rendered via JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table -->
                    <div class="fw-bold mb-2 small text-uppercase text-muted">Detail Transaksi</div>
                    <div class="table-responsive" style="border:1px solid var(--border-color); border-radius:12px;">
                        <table class="table table-borderless table-hover mb-0" style="font-size:12px; color:var(--text-primary); --bs-table-bg: transparent; --bs-table-color: var(--text-primary);">
                            <thead style="background:var(--surface-3); border-bottom:1px solid var(--border-color);">
                                <tr>
                                    <th class="py-2 text-muted">Waktu</th>
                                    <th class="py-2 text-muted">Produk / Tujuan</th>
                                    <th class="py-2 text-end text-muted">Modal</th>
                                    <th class="py-2 text-end text-muted">Jual</th>
                                    <th class="py-2 text-end text-muted">Selisih</th>
                                    <th class="py-2 text-end text-muted">Markup(%)</th>
                                    <th class="py-2 text-center text-muted">Status</th>
                                </tr>
                            </thead>
                            <tbody id="sh-list">
                                <!-- JS injected -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="small text-muted" id="sh-page-info">Halaman 1 dari 1</div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" id="sh-btn-prev" onclick="changeSellerHistoryPage(-1)">Sebelumnya</button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" id="sh-btn-next" onclick="changeSellerHistoryPage(1)">Berikutnya</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container-ppob" style="position: fixed; top: 80px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 300px;">
</div>

<style>
@keyframes slideInRight {
    from { transform: translateX(120%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

<script>
const REQUIRE_PIN = <?= isset($requirePin) && $requirePin ? 'true' : 'false' ?>;
let pendingTrxPayload = null;

// Format Currency IDR
const formatRp = (num) => 'Rp' + parseInt(num).toLocaleString('id-ID');

// Modals (Lazy Initialization to avoid bootstrap load race conditions)
function getTrxModal() { return bootstrap.Modal.getOrCreateInstance(document.getElementById('trxModal')); }
function getDepositModal() { return bootstrap.Modal.getOrCreateInstance(document.getElementById('depositModal')); }
function getDepoResultModal() { return bootstrap.Modal.getOrCreateInstance(document.getElementById('depoResultModal')); }
function getTestCaseModal() { return bootstrap.Modal.getOrCreateInstance(document.getElementById('testCaseModal')); }
function getContactModal() { return bootstrap.Modal.getOrCreateInstance(document.getElementById('contactModal')); }

let currentCategory = '';
let currentType = '';
let currentProducts = [];
let selectedInqData = null; // Storing postpaid / PLN inquiry data
let lastTrxData = null; // Storing last transaction result for print receipt
let contactsData = []; // Store fetched contacts

// 1. Fetch Live Balance on load
async function fetchBalance() {
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/balance');
        const data = await res.json();
        if(data.success && data.data && data.data.deposit !== undefined) {
            document.getElementById('live-balance').innerText = formatRp(data.data.deposit);
        } else {
            document.getElementById('live-balance').innerText = 'Gagal memuat';
        }
    } catch(e) {
        document.getElementById('live-balance').innerText = 'Error';
    }
}
fetchBalance();

// 1b. Trigger Price Sync (Admin only)
async function triggerPriceSync(btn) {
    if (btn.classList.contains('spinning')) return;
    
    btn.classList.add('spinning');
    btn.disabled = true;
    
    // Use a 6-minute timeout to handle large catalog sync
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 6 * 60 * 1000); // 6 minutes
    
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/sync-prices', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({type: 'all'}),
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        
        // Get raw text first to debug any non-JSON responses
        const rawText = await res.text();
        
        let data;
        try {
            data = JSON.parse(rawText);
        } catch(jsonErr) {
            console.error('Sync response bukan JSON:', rawText.substring(0, 500));
            if (res.ok) {
                showAlert('✅ Sinkronisasi selesai.', 'success');
                fetchBalance();
            } else {
                showAlert('❌ Server error ' + res.status + ': Cek log PHP.', 'danger');
            }
            return;
        }
        
        if (data.success) {
            showAlert('✅ Sinkronisasi harga berhasil!', 'success');
            fetchBalance();
        } else {
            showAlert('⚠️ ' + (data.message || 'Gagal sinkronisasi.'), 'warning');
        }
    } catch(e) {
        clearTimeout(timeoutId);
        if (e.name === 'AbortError') {
            showAlert('⏳ Sinkronisasi melebihi batas waktu 6 menit. Coba lagi nanti.', 'warning');
        } else {
            console.error('Sync error:', e);
            showAlert('❌ Koneksi ke server gagal. Pastikan server menyala dan coba lagi.', 'danger');
        }
    } finally {
        btn.classList.remove('spinning');
        btn.disabled = false;
    }
}


// 2. Open Deposit
function openDepositModal() {
    getDepositModal().show();
}

// 3. Request Deposit
async function requestDeposit() {
    const amount = document.getElementById('depo-amount').value;
    const bankRadio = document.querySelector('input[name="depo_bank"]:checked');
    const bank = bankRadio ? bankRadio.value : '';
    const owner = document.getElementById('depo-owner').value;
    const btn = document.getElementById('btn-depo');

    if(!amount || !bank || !owner) { showAlert('⚠️ Harap isi semua kolom deposit.', 'warning'); return; }

    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/deposit', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({amount, bank: bank, owner_name: owner})
        });
        const data = await res.json();
        if(data.success && data.data) {
            // Digiflazz returns notes as a string with transfer instruction
            const notes = data.data.notes || 'Instruksi transfer otomatis tidak tersedia dari API Digiflazz untuk metode ini. Silakan buka aplikasi resmi Digiflazz untuk melihat instruksi lengkap.';
            const finalAmount = data.data.amount || amount;
            
            // Format nominal and highlight last 3 digits
            const amountStr = parseInt(finalAmount).toString();
            let formattedAmountHTML = formatRp(finalAmount);
            if (amountStr.length > 3) {
                const head = amountStr.slice(0, -3);
                const tail = amountStr.slice(-3);
                formattedAmountHTML = `Rp ${parseInt(head).toLocaleString('id-ID')}.<span class="text-warning fw-bolder fs-1">${tail}</span>`;
            }
            
            document.getElementById('dr-amount').innerHTML = formattedAmountHTML;
            document.getElementById('dr-amount').dataset.amount = finalAmount;
            document.getElementById('dr-notes').innerText = notes;
            
            // Try to parse the notes to get Bank, Acc No, and Name
            const match = notes.match(/ke\s+(?:rekening\s+)?([a-zA-Z\s]+?)\s+(\d{8,})\s*a\.?\/?n\.?\s+(.*)/i) 
                          || notes.match(/(BCA|MANDIRI|BRI|BNI|FLIP|SHOPEEPAY|GOPAY)\s+(\d{8,})\s*a\.?\/?n\.?\s+(.*)/i);
            const parsedContainer = document.getElementById('dr-parsed-dest');
            
            if (bank === 'SHOPEEPAY' || bank === 'FLIP') {
                document.getElementById('dr-bank-name').innerText = 'BCA';
                document.getElementById('dr-acc-no').innerText = '6042888890';
                document.getElementById('dr-acc-name').innerText = 'PT DIGIFLAZZ INTERKONEKSI INDONESIA';
                parsedContainer.style.display = 'block';
            } else if (match) {
                document.getElementById('dr-bank-name').innerText = match[1].trim();
                document.getElementById('dr-acc-no').innerText = match[2];
                document.getElementById('dr-acc-name').innerText = match[3].replace(/[.]$/, '').trim(); // remove trailing dots
                parsedContainer.style.display = 'block';
            } else {
                // Try just finding any long digit sequence as the account number
                const digitsMatch = notes.match(/(\d{10,})/);
                if(digitsMatch) {
                    document.getElementById('dr-bank-name').innerText = bank; // using selected bank
                    document.getElementById('dr-acc-no').innerText = digitsMatch[1];
                    document.getElementById('dr-acc-name').innerText = "Digiflazz";
                    parsedContainer.style.display = 'block';
                } else {
                    parsedContainer.style.display = 'none';
                }
            }
            
            getDepositModal().hide();
            getDepoResultModal().show();
        } else {
            showAlert('❌ ' + (data.message || 'Gagal request deposit'), 'danger');
        }
    } catch(e) { showAlert('❌ Terjadi kesalahan server', 'danger'); }
    btn.disabled = false; btn.innerText = 'Minta Tiket Deposit';
}

// 4. Deposit History Modal
function getDepositHistoryModal() { return bootstrap.Modal.getOrCreateInstance(document.getElementById('depositHistoryModal')); }

async function openDepositHistoryModal() {
    getDepositModal().hide();
    getDepositHistoryModal().show();
    await fetchDepositHistory();
}

async function fetchDepositHistory() {
    const tbody = document.getElementById('deposit-history-body');
    const btnRefresh = document.getElementById('btnRefreshDeposit');
    
    tbody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="text-muted fw-bold">Memuat riwayat deposit...</p>
        </div>
    `;
    
    if(btnRefresh) {
        btnRefresh.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Refreshing...';
        btnRefresh.disabled = true;
    }
    
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/deposit-history');
        const data = await res.json();
        
        if(data.success && data.data && data.data.length > 0) {
            tbody.innerHTML = '';
            data.data.forEach(item => {
                let badgeClass = 'bg-warning text-dark';
                let statusText = 'Pending';
                let iconClass = 'bi-hourglass-split';
                
                const statusStr = (item.status || '').toLowerCase();
                if(statusStr === 'success' || statusStr === 'sukses') {
                    badgeClass = 'bg-success';
                    statusText = 'Sukses';
                    iconClass = 'bi-check-circle-fill';
                } else if(statusStr === 'failed' || statusStr === 'gagal' || statusStr === 'cancelled' || statusStr === 'batal') {
                    badgeClass = 'bg-danger';
                    statusText = 'Gagal';
                    iconClass = 'bi-x-circle-fill';
                }

                // parse notes
                let targetRek = '-';
                let targetName = '-';
                let uniqueTransfer = item.amount;
                
                if (item.raw) {
                    try {
                        const rawObj = JSON.parse(item.raw);
                        if (rawObj.deposit && rawObj.deposit.amount) {
                            uniqueTransfer = rawObj.deposit.amount;
                        } else if (rawObj.amount) {
                            uniqueTransfer = rawObj.amount;
                        }
                    } catch(e) {}
                }
                
                // Ultimate fallback: Parse unique amount from notes text
                if (item.notes) {
                    const rpMatch = item.notes.match(/Rp\s*([0-9.,]+)/i);
                    if (rpMatch && rpMatch[1]) {
                        const parsedRp = parseInt(rpMatch[1].replace(/[.,]/g, ''), 10);
                        if (!isNaN(parsedRp) && parsedRp > 0) {
                            uniqueTransfer = parsedRp;
                        }
                    }
                }
                
                if(item.notes) {
                    const match = item.notes.match(/ke\s+(?:rekening\s+)?([a-zA-Z\s]+?)\s+(\d{8,})\s*a\.?\/?n\.?\s+(.*)/i) 
                                || item.notes.match(/(BCA|MANDIRI|BRI|BNI|BSI|FLIP|SHOPEEPAY|GOPAY)\s+(\d{8,})\s*a\.?\/?n\.?\s+(.*)/i);
                    if(item.bank === 'SHOPEEPAY' || item.bank === 'FLIP') {
                        targetRek = 'BCA - 6042888890';
                        targetName = 'PT DIGIFLAZZ INTERKONEKSI';
                    } else if(match) {
                        targetRek = `${match[1].trim()} - ${match[2]}`;
                        targetName = match[3].replace(/[.]$/, '').trim();
                    } else {
                        let extractNo = item.notes.match(/\b\d{8,}\b/);
                        if(extractNo) targetRek = item.bank + ' - ' + extractNo[0];
                    }
                }
                
                if (targetName === '-' && item.owner_name) {
                    targetName = 'PT. Digiflazz Interkoneksi Indonesia';
                } else if (targetName === '-') {
                    targetName = 'PT. Digiflazz Interkoneksi Indonesia';
                }
                
                // Force PT Digiflazz Interkoneksi Indonesia if it looks like a person's name just in case, but usually Digiflazz notes has the PT name.
                if (item.bank && targetName !== '-' && !targetName.toLowerCase().includes('digiflazz')) {
                     // For e-wallets or banks where Digiflazz notes don't specify PT Digiflazz, enforce it
                     targetName = 'PT. Digiflazz Interkoneksi Indonesia';
                }
                
                const d = new Date(item.created_at);
                const dateStr = d.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
                const timeStr = d.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});

                const cardHtml = `
                    <div class="card border-0 mb-1" style="background: var(--surface-2); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(56, 189, 248, 0.1); color: #38bdf8;">
                                        <i class="bi bi-wallet2 fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold" style="font-size: 15px; color: var(--text-color);">Deposit ${item.bank}</h6>
                                        <small class="text-muted" style="font-size: 12px;">${dateStr} • ${timeStr}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge ${badgeClass} rounded-pill px-3 py-2 fw-bold" style="font-size: 12px;"><i class="bi ${iconClass} me-1"></i>${statusText}</span>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 h-100" style="background: var(--surface-3);">
                                        <div class="text-muted mb-2" style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Tujuan Transfer</div>
                                        <div class="fw-bold text-truncate" style="font-size: 14px; color: var(--text-color);"><i class="bi bi-bank me-2 text-muted"></i>${targetRek}</div>
                                        <div class="text-muted mt-2 text-truncate" style="font-size: 13px;"><i class="bi bi-person me-2"></i>a/n ${targetName}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-4 h-100 d-flex flex-column justify-content-center" style="background: rgba(56, 189, 248, 0.04); border: 1px dashed rgba(56, 189, 248, 0.3);">
                                        <div class="mb-2">
                                            <div class="text-muted" style="font-size: 12px; margin-bottom: 2px;">Nominal Deposit</div>
                                            <div class="fw-bold" style="font-size: 14px; color: var(--text-color);">${formatRp(item.amount)}</div>
                                        </div>
                                        <div>
                                            <div class="text-primary fw-bold" style="font-size: 12px; margin-bottom: 2px;">Nominal Transfer <i class="bi bi-info-circle ms-1 text-muted" title="Sertakan 3 angka unik terakhir agar otomatis masuk"></i></div>
                                            <div class="fw-black text-primary" style="font-size: 1.15rem; letter-spacing: -0.5px;">${formatRp(uniqueTransfer)}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                tbody.innerHTML += cardHtml;
            });
        } else {
            tbody.innerHTML = `
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bi bi-journal-x" style="font-size: 3rem; opacity: 0.3;"></i></div>
                    <h5 class="fw-bold text-muted">Belum ada riwayat deposit</h5>
                    <p class="text-muted small">Riwayat permintaan pengisian saldo akan tampil di sini.</p>
                </div>
            `;
        }
    } catch(e) {
        tbody.innerHTML = `
            <div class="text-center py-5">
                <div class="text-danger mb-3"><i class="bi bi-exclamation-triangle" style="font-size: 3rem; opacity: 0.5;"></i></div>
                <h5 class="fw-bold text-danger">Gagal memuat data</h5>
                <p class="text-muted small">Terjadi kesalahan koneksi saat mengambil riwayat deposit.</p>
            </div>
        `;
    } finally {
        if(btnRefresh) {
            btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Refresh';
            btnRefresh.disabled = false;
        }
    }
}

// 5. Open Transaction Modal
function openTrxModal(title, category, type) {
    document.getElementById('trxModalTitle').innerText = title;
    document.getElementById('trx-type').value = type;
    document.getElementById('trx-category').value = category;
    document.getElementById('customer-no').value = '';
    
    currentCategory = category;
    currentType = type;
    selectedInqData = null;

    // Reset UI
    document.getElementById('inquiry-box').style.display = 'none';
    document.getElementById('btn-inquiry').style.display = 'none';
    document.getElementById('provider-badge').style.display = 'none';
    document.getElementById('brand-filter-container').innerHTML = '';
    document.getElementById('brand-filter-container').style.display = 'none';
    document.getElementById('product-search-container').style.display = 'none';
    document.getElementById('search-product').value = '';
    document.getElementById('btn-pay-postpaid').style.display = 'none';
    document.getElementById('product-list-container').style.display = 'block';
    document.getElementById('product-grid').innerHTML = '';
    
    if (type === 'postpaid') {
        // Tagihan Pasca: Harus Cek Dulu
        document.getElementById('customer-no').placeholder = 'Masukkan ID Pelanggan';
        document.getElementById('btn-inquiry').style.display = 'block';
        document.getElementById('product-list-container').style.display = 'none'; // Sembunyikan list produk
        // Auto load products in background just to get SKU for inquiry
        loadProducts(category, type); 
    } else if (category === 'pln') {
        // PLN Prabayar: Opsional Cek Nama (Inquiry PLN)
        document.getElementById('customer-no').placeholder = 'Masukkan Nomor Meter/IDPEL';
        document.getElementById('btn-inquiry').style.display = 'block';
        document.getElementById('btn-inquiry').innerText = 'Cek Nama';
        // Auto load products to buy
        loadProducts(category, type);
    } else if (category === 'ewallet') {
        document.getElementById('customer-no').placeholder = 'Masukkan Nomor HP/Akun';
        document.getElementById('btn-inquiry').style.display = 'block';
        document.getElementById('btn-inquiry').innerText = 'Cek Nama';
        loadProducts(category, type);
    } else {
        // Pulsa / Data biasa: Auto search on input
        document.getElementById('customer-no').placeholder = 'Masukkan Nomor HP (0812...)';
        // Auto load products to buy based on prefix logic
        loadProducts(category, type);
    }

    getTrxModal().show();
}

function openContactBook() {
    getContactModal().show();
    document.getElementById('contact-loading').style.display = 'block';
    document.getElementById('contact-list').innerHTML = '';
    
    // Determine which classification of contacts to load based on current category
    let typeMap = {
        'pulsa': 'hp',
        'data': 'hp',
        'sms_nelpon': 'hp',
        'ewallet': 'ewallet',
        'pln': 'pln',
        'game': 'game',
        'tv': 'tv'
    };
    let contactType = typeMap[currentCategory] || 'hp'; // fallback to hp

    fetch(`${BASE_URL}api/ppob/customers?type=${contactType}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('contact-loading').style.display = 'none';
            if(data.success) {
                contactsData = data.data;
                renderContacts(contactsData);
            }
        })
        .catch(() => {
            document.getElementById('contact-loading').style.display = 'none';
            showAlert('❌ Gagal memuat daftar kontak', 'danger');
        });
}

function renderContacts(data) {
    const list = document.getElementById('contact-list');
    list.innerHTML = '';
    
    if(data.length === 0) {
        list.innerHTML = '<div class="text-center text-muted small py-4">Belum ada kontak tersimpan untuk layanan ini.</div>';
        return;
    }

    data.forEach(c => {
        let title = c.customer_name || 'Tanpa Nama';
        let detail = c.customer_no;
        if(c.type === 'pln' && c.pln_name) detail += ` • ${c.pln_name}`;
        if(c.type === 'ewallet' && c.pln_name) detail += ` • ${c.pln_name}`;
        
        let html = `
        <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center mb-2" style="background:var(--surface-2); cursor:pointer;" onclick="selectContact('${c.customer_no}')">
            <div>
                <div class="fw-bold mb-1" style="font-size:14px; color:var(--text-primary);">${title}</div>
                <div class="text-muted small">${detail}</div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
        </div>`;
        list.insertAdjacentHTML('beforeend', html);
    });
}

document.getElementById('search-contact').addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase();
    const filtered = contactsData.filter(c => 
        (c.customer_name && c.customer_name.toLowerCase().includes(q)) || 
        (c.customer_no && c.customer_no.toLowerCase().includes(q)) ||
        (c.pln_name && c.pln_name.toLowerCase().includes(q))
    );
    renderContacts(filtered);
});

function selectContact(no) {
    document.getElementById('customer-no').value = no;
    getContactModal().hide();
    
    // Trigger input event to re-evaluate provider badge / search products
    const event = new Event('input', { bubbles: true });
    document.getElementById('customer-no').dispatchEvent(event);
    
    // Auto trigger provider check or validation
    if(currentCategory !== 'pln' && currentCategory !== 'game' && currentCategory !== 'tv') {
        checkProvider();
    }
}

// 5. Load Product Categories based on Tab (Prepaid/Postpaid)

// 5. Load Products
async function loadProducts(category, type) {
    document.getElementById('product-loading').style.display = 'block';
    document.getElementById('product-grid').innerHTML = '';
    try {
        const res = await fetch(`<?= BASE_URL ?>api/ppob/products/${category}?type=${type}`);
        const data = await res.json();
        
        // Race condition check: Only proceed if this is still the active category/type
        if (currentCategory !== category || currentType !== type) {
            return;
        }
        
        document.getElementById('product-loading').style.display = 'none';
        if (data.success && data.data.length > 0) {
            currentProducts = data.data;
            if(type === 'prepaid') {
                if (category === 'pulsa' || category === 'data' || category === 'sms_nelpon') {
                    document.getElementById('brand-filter-container').style.display = 'none';
                    document.getElementById('product-grid').innerHTML = `<div class="text-center text-muted w-100 py-3" style="grid-column: 1 / -1;">Silakan masukkan nomor HP untuk melihat produk.</div>`;
                    filterProductsByPrefix(document.getElementById('customer-no').value);
                } else {
                    renderFilters(data.data, 'brand');
                    renderProducts(data.data);
                }
            }
        } else {
            document.getElementById('product-grid').innerHTML = `<div class="text-center text-muted w-100 py-3" style="grid-column: 1 / -1;">Produk tidak tersedia/belum disync.</div>`;
        }
    } catch(e) { console.error(e); }
}

// Provider Prefix Detection
function detectProvider(phone) {
    if (!phone || phone.length < 4) return null;
    const prefix = phone.substring(0, 4);
    const prefixes = {
        '0811': 'TELKOMSEL', '0812': 'TELKOMSEL', '0813': 'TELKOMSEL', '0821': 'TELKOMSEL', '0822': 'TELKOMSEL', '0823': 'TELKOMSEL', '0852': 'TELKOMSEL', '0853': 'TELKOMSEL', '0851': 'TELKOMSEL',
        '0814': 'INDOSAT', '0815': 'INDOSAT', '0816': 'INDOSAT', '0855': 'INDOSAT', '0856': 'INDOSAT', '0857': 'INDOSAT', '0858': 'INDOSAT',
        '0817': 'XL', '0818': 'XL', '0819': 'XL', '0859': 'XL', '0877': 'XL', '0878': 'XL', 
        '0838': 'AXIS', '0831': 'AXIS', '0832': 'AXIS', '0833': 'AXIS',
        '0895': 'THREE', '0896': 'THREE', '0897': 'THREE', '0898': 'THREE', '0899': 'THREE',
        '0881': 'SMARTFREN', '0882': 'SMARTFREN', '0883': 'SMARTFREN', '0884': 'SMARTFREN', '0885': 'SMARTFREN', '0886': 'SMARTFREN', '0887': 'SMARTFREN', '0888': 'SMARTFREN', '0889': 'SMARTFREN'
    };
    return prefixes[prefix] || null;
}

function filterProductsByPrefix(phone) {
    if (currentCategory !== 'pulsa' && currentCategory !== 'data' && currentCategory !== 'sms_nelpon') return;
    const provider = detectProvider(phone);
    const badge = document.getElementById('provider-badge');
    
    if (provider) {
        badge.innerText = provider;
        badge.style.display = 'block';
        
        // Brand logic map for Digiflazz (which might use slightly different naming)
        let digiBrand = provider;
        if(provider === 'THREE') digiBrand = 'TRI';
        
        // Filter case-insensitive and partial match
        const filtered = currentProducts.filter(p => (p.brand || '').toUpperCase().includes(digiBrand));
        
        if (filtered.length > 0) {
            renderFilters(filtered, 'sub_category');
            renderProducts(filtered);
            document.getElementById('product-search-container').style.display = filtered.length > 5 ? 'block' : 'none';
        } else {
            document.getElementById('brand-filter-container').style.display = 'none';
            document.getElementById('product-search-container').style.display = 'none';
            document.getElementById('product-grid').innerHTML = `<div class="text-center text-muted w-100 py-4" style="grid-column: 1 / -1;"><i class="bi bi-inbox fs-1 mb-2 d-block opacity-50"></i>Produk ${provider} sedang tidak tersedia.</div>`;
        }
    } else {
        badge.style.display = 'none';
        document.getElementById('product-search-container').style.display = 'none';
        document.getElementById('product-grid').innerHTML = `<div class="text-center text-muted w-100 py-4" style="grid-column: 1 / -1;"><i class="bi bi-search fs-1 mb-2 d-block opacity-50"></i>Masukkan awalan nomor HP yang valid untuk melihat produk.</div>`;
    }
}

document.getElementById('customer-no').addEventListener('input', (e) => {
    if (currentCategory === 'pulsa' || currentCategory === 'data' || currentCategory === 'sms_nelpon') {
        filterProductsByPrefix(e.target.value);
    }
});

document.getElementById('search-product').addEventListener('input', (e) => {
    const keyword = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.prod-card');
    cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        card.style.display = text.includes(keyword) ? 'flex' : 'none';
    });
});

function renderFilters(products, filterKey = 'brand') {
    const container = document.getElementById('brand-filter-container');
    container.innerHTML = '';
    
    // Get unique filter values (ignoring null/empty)
    const items = [...new Set(products.map(p => p[filterKey]))].filter(b => b);
    if (items.length <= 1) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'flex';
    
    // "Semua" button
    const allBtn = document.createElement('button');
    allBtn.className = 'btn btn-sm btn-primary rounded-pill fw-bold px-3';
    allBtn.innerText = 'Semua';
    allBtn.onclick = (e) => filterList('', filterKey, e.target, products);
    container.appendChild(allBtn);
    
    items.forEach(val => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 border-2';
        btn.innerText = val;
        btn.onclick = (e) => filterList(val, filterKey, e.target, products);
        container.appendChild(btn);
    });
}

function filterList(val, filterKey, clickedBtn, originalProducts) {
    // Update active button styling
    const container = document.getElementById('brand-filter-container');
    container.querySelectorAll('button').forEach(b => {
        b.className = 'btn btn-sm btn-outline-primary rounded-pill fw-bold px-3 border-2';
    });
    
    clickedBtn.className = 'btn btn-sm btn-primary rounded-pill fw-bold px-3';
    
    if (!val) {
        renderProducts(originalProducts);
    } else {
        const filtered = originalProducts.filter(p => p[filterKey] === val);
        renderProducts(filtered);
    }
}

// PPOB Selling Prices Logic
function getPpobSellPrices() {
    try {
        return JSON.parse(localStorage.getItem('ppob_sell_prices')) || {};
    } catch(e) { return {}; }
}

function getPpobSellPrice(sku) {
    if (typeof currentProducts !== 'undefined') {
        const p = currentProducts.find(x => x.buyer_sku_code === sku);
        if (p && p.is_custom_price == 1 && p.sell_price > 0) {
            return parseInt(p.sell_price, 10);
        }
    }
    // Fallback to local storage
    return getPpobSellPrices()[sku];
}

async function savePpobSellPrice(sku, price) {
    // Save to local storage for instant fallback
    const prices = getPpobSellPrices();
    prices[sku] = parseInt(price, 10);
    localStorage.setItem('ppob_sell_prices', JSON.stringify(prices));

    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/custom-price', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({sku: sku, sell_price: price})
        });
        const data = await res.json();
        if(data.success) {
            if (typeof currentProducts !== 'undefined') {
                const p = currentProducts.find(x => x.buyer_sku_code === sku);
                if (p) {
                    p.sell_price = price;
                    p.is_custom_price = 1;
                }
            }
        }
    } catch(e) { console.error('Failed to save to server'); }
}

async function deletePpobSellPrice(sku) {
    const prices = getPpobSellPrices();
    delete prices[sku];
    localStorage.setItem('ppob_sell_prices', JSON.stringify(prices));

    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/custom-price/reset', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({sku: sku})
        });
        const data = await res.json();
        if(data.success) {
            if (typeof currentProducts !== 'undefined') {
                const p = currentProducts.find(x => x.buyer_sku_code === sku);
                if (p) {
                    p.sell_price = p.seller_price;
                    p.is_custom_price = 0;
                }
            }
        }
    } catch(e) { console.error('Failed to delete from server'); }
}

window.openSetPriceModal = function(e, productStr) {
    e.stopPropagation(); // Prevent confirmPurchase
    const product = JSON.parse(decodeURIComponent(productStr));
    const currentSell = getPpobSellPrice(product.buyer_sku_code) || '';
    
    document.getElementById('sp-sku').value = product.buyer_sku_code;
    document.getElementById('sp-base-price').value = product.seller_price;
    document.getElementById('sp-product-name').innerText = product.product_name;
    document.getElementById('sp-base-price-text').innerText = formatRp(product.seller_price);
    document.getElementById('sp-sell-price').value = currentSell;
    
    updateProfitPreview();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('setPriceModal')).show();
}

document.getElementById('sp-sell-price')?.addEventListener('input', updateProfitPreview);

function updateProfitPreview() {
    const base = parseInt(document.getElementById('sp-base-price').value) || 0;
    const sell = parseInt(document.getElementById('sp-sell-price').value) || 0;
    const preview = document.getElementById('sp-profit-preview');
    
    if (sell > base) {
        const profit = sell - base;
        const pct = ((profit / base) * 100).toFixed(1);
        preview.style.display = 'block';
        preview.style.color = 'var(--success)';
        preview.innerText = `Untung: ${formatRp(profit)} (${pct}%)`;
    } else if (sell > 0 && sell <= base) {
        preview.style.display = 'block';
        preview.style.color = 'var(--danger)';
        preview.innerText = `Rugi / Seri!`;
    } else {
        preview.style.display = 'none';
    }
}

document.getElementById('btn-save-price')?.addEventListener('click', async () => {
    const sku = document.getElementById('sp-sku').value;
    const sell = document.getElementById('sp-sell-price').value;
    
    const btn = document.getElementById('btn-save-price');
    const ogText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
    btn.disabled = true;

    if (sell && parseInt(sell) > 0) {
        await savePpobSellPrice(sku, sell);
        showToast('Harga jual berhasil disimpan di server', 'success');
    } else {
        await deletePpobSellPrice(sku);
        showToast('Harga jual dikembalikan ke default', 'info');
    }
    
    btn.innerHTML = ogText;
    btn.disabled = false;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('setPriceModal')).hide();
    
    if (typeof currentProducts !== 'undefined') {
        if(currentCategory === 'pulsa' || currentCategory === 'data') {
           filterProductsByPrefix(document.getElementById('customer-no').value);
        } else {
           renderProducts(currentProducts);
        }
    }
});

// Auto-sync existing local prices to server on load
document.addEventListener('DOMContentLoaded', async () => {
    const prices = getPpobSellPrices();
    if (Object.keys(prices).length > 0) {
        try {
            const res = await fetch('<?= BASE_URL ?>api/ppob/custom-price/bulk', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({prices: prices})
            });
            const data = await res.json();
            if(data.success) {
                console.log(data.message);
                localStorage.removeItem('ppob_sell_prices');
            }
        } catch(e) {}
    }
});

function renderProducts(products) {
    const grid = document.getElementById('product-grid');
    grid.innerHTML = '';
    
    if (currentCategory !== 'pulsa' && currentCategory !== 'data') {
        document.getElementById('product-search-container').style.display = products.length > 5 ? 'block' : 'none';
    }
    products.forEach(p => {
        const card = document.createElement('div');
        card.className = 'prod-card position-relative';
        card.onclick = () => confirmPurchase(p);
        
        const sellPrice = getPpobSellPrice(p.buyer_sku_code);
        let priceHtml = `<div class="prod-price">${formatRp(p.seller_price)}</div>`;
        
        if (sellPrice && sellPrice > p.seller_price) {
            const profit = sellPrice - p.seller_price;
            const pct = ((profit / p.seller_price) * 100).toFixed(1);
            priceHtml = `
                <div class="prod-price">${formatRp(p.seller_price)}</div>
                <div class="mt-1 d-flex flex-column" style="font-size: 0.75rem;">
                    <span class="text-success fw-bold">Jual: ${formatRp(sellPrice)}</span>
                    <span class="text-muted" style="font-size: 0.6rem; opacity: 0.7; margin-top: -2px;">(+${formatRp(profit)} / ${pct}%)</span>
                </div>
            `;
        } else if (sellPrice) {
            priceHtml = `
                <div class="prod-price">${formatRp(p.seller_price)}</div>
                <div class="mt-1" style="font-size: 0.75rem;">
                    <span class="text-warning fw-bold">Jual: ${formatRp(sellPrice)}</span>
                </div>
            `;
        }

        const encodedProduct = encodeURIComponent(JSON.stringify(p));
        
        let sellerHtml = '';
        if (p.seller_name) {
            let successBadge = '';
            let rawRate = (p.success_rate !== null && p.success_rate !== undefined) ? p.success_rate : null;
            if (rawRate !== null) {
                let badgeColor = rawRate >= 80 ? '#10b981' : (rawRate >= 50 ? '#f59e0b' : '#ef4444');
                successBadge = `<span style="color: ${badgeColor}; font-weight: 700; margin-left: 3px;"><i class="bi bi-lightning-charge-fill" style="font-size: 0.55rem;"></i> ${rawRate}%</span>`;
            } else {
                successBadge = `<span class="text-muted" style="margin-left: 3px; font-weight: 700;"><i class="bi bi-lightning-charge-fill" style="font-size: 0.55rem;"></i> -</span>`;
            }
            
            sellerHtml = `
            <div class="mt-2" style="font-size: 0.6rem; opacity: 0.85;">
                <div onclick="openSellerHistory(event, '${p.seller_name}')" style="display: inline-flex; align-items: center; gap: 3px; padding: 2px 6px; background: var(--surface-1); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); cursor: pointer; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="bi bi-shop text-primary" style="font-size: 0.6rem;"></i>
                    <span style="font-weight: 600; color: var(--text-secondary); max-width: 80px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${p.seller_name}</span>
                    <div style="width: 1px; height: 8px; background: var(--border-color); margin: 0 1px;"></div>
                    ${successBadge}
                </div>
            </div>`;
        }
        
        card.innerHTML = `
            <button class="btn btn-sm position-absolute top-0 end-0 m-2" style="z-index: 2; padding: 4px 8px; border-radius: 8px; background: var(--surface-2); color: var(--text-muted); border: 1px solid var(--border-color);" onclick="openSetPriceModal(event, '${encodedProduct}')">
                <i class="bi bi-gear-fill" style="font-size: 1rem;"></i>
            </button>
            <div class="pe-4">
                <div class="prod-name">${p.product_name}</div>
                <div class="prod-desc">${p.description || ''}</div>
            </div>
            <div class="mt-2 pt-2 border-top border-secondary border-opacity-10">
                ${priceHtml}
                ${sellerHtml}
            </div>
        `;
        grid.appendChild(card);
    });
}

// Open Seller History Modal
let currentSellerPage = 1;
let currentSellerName = '';

async function openSellerHistory(e, sellerName) {
    if(e) e.stopPropagation(); // prevent confirming purchase
    if (!sellerName) return;

    currentSellerName = sellerName;
    currentSellerPage = 1;
    document.getElementById('sh-seller-name').innerText = sellerName;
    
    bootstrap.Modal.getOrCreateInstance(document.getElementById('sellerHistoryModal')).show();
    
    await fetchSellerHistory(currentSellerPage);
}

async function changeSellerHistoryPage(dir) {
    currentSellerPage += dir;
    if (currentSellerPage < 1) currentSellerPage = 1;
    await fetchSellerHistory(currentSellerPage);
}

async function fetchSellerHistory(page) {
    const list = document.getElementById('sh-list');
    const loading = document.getElementById('sh-loading');
    const content = document.getElementById('sh-content');
    const btnPrev = document.getElementById('sh-btn-prev');
    const btnNext = document.getElementById('sh-btn-next');
    
    loading.style.display = 'block';
    content.style.display = 'none';

    try {
        const res = await fetch(`<?= BASE_URL ?>api/ppob/seller-history?seller=${encodeURIComponent(currentSellerName)}&page=${page}`);
        const data = await res.json();
        
        loading.style.display = 'none';
        content.style.display = 'block';
        
        if (data.success && data.data) {
            // Render Analytics
            const analytics = data.data.analytics;
            document.getElementById('sh-stat-total').innerText = formatRp(analytics.total).replace('Rp', '');
            
            const total = analytics.total || 1; // prevent div zero
            const pSuccess = Math.round((analytics.success / total) * 100);
            const pFailed = Math.round((analytics.failed / total) * 100);
            
            document.getElementById('sh-bar-success').style.width = pSuccess + '%';
            document.getElementById('sh-bar-failed').style.width = pFailed + '%';
            
            document.getElementById('sh-txt-success').innerText = `Sukses: ${analytics.success} (${pSuccess}%)`;
            document.getElementById('sh-txt-failed').innerText = `Gagal: ${analytics.failed} (${pFailed}%)`;
            
            // Category breakdown
            let catHtml = '';
            for (const [catName, count] of Object.entries(analytics.categories)) {
                if(count > 0) {
                    const pCat = Math.round((count / total) * 100);
                    catHtml += `
                    <div class="d-flex align-items-center mb-1" style="font-size:11px;">
                        <div style="width:60px;" class="fw-bold">${catName}</div>
                        <div class="flex-grow-1 mx-2">
                            <div class="progress" style="height:6px; background:var(--surface-3); border-radius:3px;">
                                <div class="progress-bar bg-primary" style="width:${pCat}%; border-radius:3px;"></div>
                            </div>
                        </div>
                        <div style="width:30px; text-align:right;" class="text-muted">${count}</div>
                    </div>`;
                }
            }
            if(!catHtml) catHtml = '<div class="text-muted small">Belum ada kategori.</div>';
            document.getElementById('sh-cat-bars').innerHTML = catHtml;
            
            // Render Table
            list.innerHTML = '';
            if (data.data.data && data.data.data.length > 0) {
                data.data.data.forEach(trx => {
                    let badgeClass = trx.status === 'success' || trx.status === 'sukses' ? 'bg-success' : (trx.status === 'pending' || trx.status === 'processing' ? 'bg-warning text-dark' : 'bg-danger');
                    let d = new Date(trx.created_at);
                    let dateStr = d.toLocaleDateString('id-ID', {day: '2-digit', month: 'short'}) + '<br><small class="text-muted">' + d.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) + '</small>';
                    
                    let failMsg = '';
                    if ((trx.status === 'gagal' || trx.status === 'failed') && trx.message) {
                        failMsg = `<div class="text-danger mt-1" style="font-size:10px; font-style:italic;"><i class="bi bi-exclamation-circle"></i> ${trx.message}</div>`;
                    }
                    
                    let modal = parseInt(trx.modal_price || 0);
                    let jual = parseInt(trx.sell_price || 0);
                    let profit = parseInt(trx.profit || 0);
                    let markupPct = modal > 0 ? ((profit / modal) * 100).toFixed(1) : 0;
                    let markupColor = profit > 0 ? 'var(--success)' : (profit < 0 ? 'var(--danger)' : 'var(--text-muted)');

                    list.innerHTML += `
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td class="py-2 align-middle">${dateStr}</td>
                        <td class="py-2 align-middle">
                            <div class="fw-bold" style="color:var(--text-primary);">${trx.product_name}</div>
                            <div class="text-muted" style="font-size:11px;">
                                <span class="badge bg-secondary" style="font-size:9px; padding:3px 5px;">${(trx.category || '').toUpperCase()}</span> ${trx.customer_no}
                            </div>
                            ${failMsg}
                        </td>
                        <td class="py-2 text-end fw-bold align-middle">${formatRp(modal)}</td>
                        <td class="py-2 text-end fw-bold align-middle" style="color:var(--primary);">${formatRp(jual)}</td>
                        <td class="py-2 text-end fw-bold align-middle" style="color:${markupColor};">${formatRp(profit)}</td>
                        <td class="py-2 text-end fw-bold align-middle" style="color:${markupColor};">${markupPct}%</td>
                        <td class="py-2 text-center align-middle">
                            <span class="badge ${badgeClass}" style="font-size:10px; padding:4px 8px;">${trx.status.toUpperCase()}</span>
                        </td>
                    </tr>`;
                });
            } else {
                list.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted small">Belum ada riwayat transaksi.</td></tr>';
            }
            
            // Pagination config
            const p = data.data.pagination;
            document.getElementById('sh-page-info').innerText = `Halaman ${p.current_page} dari ${p.total_pages} (${p.total_records} trx)`;
            btnPrev.disabled = p.current_page <= 1;
            btnNext.disabled = p.current_page >= p.total_pages;
        }
    } catch (err) {
        console.error(err);
        loading.style.display = 'none';
        content.style.display = 'block';
        list.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger small">Gagal memuat riwayat.</td></tr>';
    }
}

// 6. Inquiry (Cek Tagihan / Cek Nama PLN)
async function performInquiry() {
    const no = document.getElementById('customer-no').value;
    if(!no) { showAlert('⚠️ Masukkan nomor tujuan/ID pelanggan!', 'warning'); return; }
    
    const btn = document.getElementById('btn-inquiry');
    btn.disabled = true; btn.innerHTML = 'Loading...';

    const inqBox = document.getElementById('inquiry-box');
    inqBox.style.display = 'none';

    try {
        if(currentCategory === 'pln' && currentType === 'prepaid') {
            // Cek Nama PLN Prabayar
            const res = await fetch('<?= BASE_URL ?>api/ppob/inquiry-pln', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({customer_no: no})
            });
            const data = await res.json();
            if(data.success && data.data && data.data.name) {
                window.currentPlnName = data.data.name;
                window.currentPlnPower = data.data.segment_power;
                inqBox.style.display = 'block';
                document.getElementById('inq-name').innerText = data.data.name;
                document.getElementById('inq-price').innerText = data.data.segment_power || '-';
                document.getElementById('inq-detail-label').style.display = 'none';
                document.getElementById('inq-detail').style.display = 'none';
                document.getElementById('btn-pay-postpaid').style.display = 'none'; // Prabayar pilih produk di bawah
            } else { showAlert('❌ ' + (data.message || 'ID pelanggan PLN tidak ditemukan'), 'danger'); }
        } else if (currentCategory === 'ewallet') {
            // Cek Nama E-Wallet
            const activeBrandBtn = document.querySelector('#brand-filter-container .btn-primary');
            let brand = activeBrandBtn ? activeBrandBtn.innerText : '';
            if (brand === 'Semua' && typeof currentProducts !== 'undefined' && currentProducts.length > 0) {
                 brand = currentProducts[0].brand; 
            }
            if (!brand || brand === 'Semua') {
                showAlert('⚠️ Pilih penyedia E-Wallet terlebih dahulu (misal: DANA, GOPAY)', 'warning');
                btn.disabled = false; btn.innerText = 'Cek Nama';
                return;
            }
            
            // Map brand to account_bank
            const brandMap = {
                'GOPAY': 'gopay', 'GO PAY': 'gopay',
                'DANA': 'dana',
                'OVO': 'ovo',
                'SHOPEEPAY': 'shopeepay', 'SHOPEE PAY': 'shopeepay',
                'LINKAJA': 'linkaja', 'LINK AJA': 'linkaja'
            };
            const accountBank = brandMap[brand.toUpperCase()] || brand.toLowerCase().replace(/\s/g, '');
            
            const res = await fetch('<?= BASE_URL ?>api/ppob/cek-ewallet', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({account_bank: accountBank, account_number: no})
            });
            const data = await res.json();
            
            let accountName = null;
            if (data && typeof data === 'object') {
                if (data.data) {
                    accountName = data.data.account_holder || data.data.account_name || data.data.name || data.data.accountName || data.data.customer_name;
                }
                if (!accountName) {
                    accountName = data.account_holder || data.account_name || data.accountName || data.name || data.customer_name;
                }
            }

            if (res.ok && accountName) {
                inqBox.style.display = 'block';
                document.getElementById('inq-name').innerText = accountName;
                document.getElementById('inq-price').innerText = '-';
                document.getElementById('inq-detail-label').style.display = 'none';
                document.getElementById('inq-detail').style.display = 'none';
                document.getElementById('btn-pay-postpaid').style.display = 'none';
            } else {
                console.error("E-Wallet Check Error Response:", data);
                showAlert('❌ ' + (data.message || data.detail || 'Akun e-wallet tidak ditemukan'), 'danger');
            }
        } else if (currentType === 'postpaid') {
            // Cek Tagihan Pascabayar
            // Ambil SKU pertama dari currentProducts (asumsi 1 kategori 1 SKU utama untuk cek, atau user bisa pilih dulu. Di sini simplifikasi ambil index 0)
            if(currentProducts.length === 0) { showAlert('⚠️ Produk pascabayar belum tersedia/sync.', 'warning'); btn.disabled=false; btn.innerHTML='Cek Detail'; return; }
            
            const sku = currentProducts[0].buyer_sku_code; // Usually generic SKU
            const res = await fetch('<?= BASE_URL ?>api/ppob/inquiry-pasca', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({sku: sku, customer_no: no})
            });
            const data = await res.json();
            if(data.success && data.data && data.data.customer_name) {
                selectedInqData = data.data; // Simpan data untuk dibayar
                selectedInqData.sku = sku;
                
                inqBox.style.display = 'block';
                document.getElementById('inq-name').innerText = data.data.customer_name;
                document.getElementById('inq-detail-label').style.display = 'block';
                document.getElementById('inq-detail').style.display = 'block';
                document.getElementById('inq-detail').innerText = data.data.desc ? data.data.desc.detail[0].periode : '-';
                document.getElementById('inq-price').innerText = formatRp(data.data.selling_price);
                
                document.getElementById('btn-pay-postpaid').style.display = 'inline-block';
            } else { showAlert('❌ ' + (data.message || 'Tagihan tidak ditemukan atau sudah dibayar'), 'danger'); }
        }
    } catch(e) { showAlert('❌ Terjadi kesalahan jaringan', 'danger'); }
    
    btn.disabled = false; btn.innerText = (currentType==='prepaid') ? 'Cek Nama' : 'Cek Tagihan';
}

async function confirmPurchase(product) {
    const no = document.getElementById('customer-no').value;
    if(!no) { showAlert('⚠️ Masukkan nomor HP/Tujuan terlebih dahulu!', 'warning'); return; }
    
    const customSellPrice = typeof getPpobSellPrice === 'function' ? getPpobSellPrice(product.buyer_sku_code) : null;
    const finalPrice = customSellPrice && customSellPrice > 0 ? customSellPrice : (product.sell_price || product.seller_price);
    const modalPrice = product.seller_price || product.price || 0;
    const profit = finalPrice - modalPrice;
    const markupPct = modalPrice > 0 ? ((profit / modalPrice) * 100).toFixed(1) : 0;
    
    const sellerName = product.seller_name || '-';
    const successRate = product.success_rate ? `${product.success_rate}%` : '-';
    
    let extraInfo = '';
    
    if (currentCategory === 'pln' && currentType === 'prepaid') {
        const plnName = window.currentPlnName || document.getElementById('inq-name').innerText || '-';
        const plnPower = window.currentPlnPower || document.getElementById('inq-price').innerText || '-';
        
        let nominal = 0;
        const match = product.product_name.match(/(?:PLN|TOKEN)\s*(\d+)(?:\.000|RB|RIBU)?/i);
        if (match) {
            nominal = parseInt(match[1]);
            if (product.product_name.toLowerCase().includes('000') || product.product_name.toLowerCase().includes('ribu') || product.product_name.toLowerCase().includes('rb')) {
                nominal *= 1000;
            } else if (nominal < 1000) {
                nominal *= 1000;
            }
        } else {
             const matchDigits = product.product_name.match(/\d+/g);
             if (matchDigits) {
                 const num = parseInt(matchDigits.join(''));
                 nominal = num < 1000 ? num * 1000 : num;
             }
        }
        
        let estimasiKwh = '-';
        if (nominal > 0) {
            let tarif = 1444.70; 
            if (plnPower.includes('450')) tarif = 415;
            else if (plnPower.includes('900')) tarif = 1352;
            
            const dpp = nominal - 2000;
            const netto = dpp - (dpp * 0.05);
            const kwh = netto / tarif;
            if (kwh > 0) estimasiKwh = `~${kwh.toFixed(1)} kWh`;
        }
        
        extraInfo = `<br>Nama Meter: <b>${plnName}</b><br>Daya: <b>${plnPower}</b><br>Estimasi kWh: <b>${estimasiKwh}</b>`;
    }
    
    const profitColor = profit >= 0 ? '#198754' : '#dc3545';
    showConfirm('Konfirmasi Transaksi', `Produk: <b>${product.product_name}</b><br>Nomor: <b>${no}</b>${extraInfo}<br><br>Harga Modal: <b>${formatRp(modalPrice)}</b><br>Harga Jual: <b style="color:var(--primary);">${formatRp(finalPrice)}</b><br>Profit/Margin: <b style="color:${profitColor};">${formatRp(profit)} (${markupPct}%)</b><br>Seller: <b>${sellerName}</b> (Success Rate: <b>${successRate}</b>)`, () => {
        processTransaction({
            sku: product.buyer_sku_code,
            customer_no: no,
            sell_price: finalPrice,
            product_name: product.product_name,
            brand: product.brand,
            customer_name: (currentCategory === 'ewallet' || currentCategory === 'pln') ? (document.getElementById('inq-name').innerText || '') : ''
        });
    });
}

// 8. Pay Postpaid
async function payPostpaid() {
    if(!selectedInqData) return;
    
    showConfirm('Bayar Tagihan', `Yakin membayar tagihan sebesar <b>${formatRp(selectedInqData.selling_price)}</b>?`, () => {
        processTransaction({
            sku: selectedInqData.sku,
            customer_no: selectedInqData.customer_no,
            ref_id: selectedInqData.ref_id, // Wajib dari inquiry
            sell_price: selectedInqData.selling_price,
            product_name: selectedInqData.customer_name
        });
    });
}

// 9. Process Transaction (Check PIN if required)
function processTransaction(payload) {
    if (REQUIRE_PIN) {
        pendingTrxPayload = payload;
        document.getElementById('trx-pin-input').value = '';
        // Small delay ensures any closing modal animation has finished
        setTimeout(() => {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('pinModal')).show();
        }, 50);
    } else {
        // REQUIRE_PIN may be stale (cached page). Include empty pin and let server decide.
        // If server rejects with PIN error, showPinAndRetry() will handle it.
        executeTransactionAPI(payload);
    }
}

// Show PIN modal and retry a pending payload
function showPinAndRetry(payload) {
    pendingTrxPayload = payload;
    document.getElementById('trx-pin-input').value = '';
    // Force update REQUIRE_PIN so next transactions also show PIN
    // eslint-disable-next-line no-global-assign
    REQUIRE_PIN_ACTIVE = true;
    setTimeout(() => {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('pinModal')).show();
    }, 100);
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('pinBtnVerify')?.addEventListener('click', () => {
        const pin = document.getElementById('trx-pin-input').value;
        if(!pin) { showAlert('⚠️ Masukkan PIN!', 'warning'); return; }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('pinModal')).hide();
        
        if(pendingTrxPayload) {
            pendingTrxPayload.pin = pin;
            // Wait for pin modal to close, then execute
            document.getElementById('pinModal').addEventListener('hidden.bs.modal', () => {
                executeTransactionAPI(pendingTrxPayload);
            }, { once: true });
        }
    });

    // Auto connect printer in the background on page load
    setTimeout(async () => {
        if (typeof ThermalPrinter !== 'undefined' && navigator.bluetooth) {
            let printer = window._ppobPrinter = new ThermalPrinter();
            if (printer.hasSavedDevice()) {
                console.log("[PPOB] Printer tersimpan ditemukan. Mencoba auto-connect di background...");
                await printer.tryAutoReconnect();
            }
        }
    }, 1500); // Wait 1.5s so it doesn't block UI rendering
});

let REQUIRE_PIN_ACTIVE = REQUIRE_PIN;

// Global variable for polling
let autoPollInterval = null;

async function executeTransactionAPI(payload) {
    getTrxModal().hide();
    
    const resultModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('resultModal'));
    // Set to loading state immediately
    document.getElementById('result-icon').innerText = '⏳';
    document.getElementById('result-title').innerText = 'Memproses...';
    document.getElementById('result-title').className = 'fw-bold fs-4 text-warning';
    document.getElementById('result-customer').innerText = payload.customer_no || '-';
    document.getElementById('result-product').innerText = payload.product_name || '-';
    document.getElementById('result-price').innerText = formatRp(payload.sell_price || 0);
    document.getElementById('result-sn').innerText = '-';
    document.getElementById('result-msg').innerText = 'Harap tunggu...';
    document.getElementById('result-refid').innerText = payload.ref_id || '-';
    document.getElementById('result-actions').style.display = 'none';
    
    // Check if recheck btn exists in DOM, if so hide it
    const existingRecheck = document.getElementById('result-recheck-btn');
    if(existingRecheck) existingRecheck.style.display = 'none';
    
    resultModal.show();
    
    if (autoPollInterval) {
        clearInterval(autoPollInterval);
        autoPollInterval = null;
    }
    
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/transaction', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        // Wait a bit to ensure smooth transition
        setTimeout(() => {
            if(data.success) {
                const status = (data.data.status || 'pending').toLowerCase();
            const isPending = status === 'pending' || status === 'processing';
            const isSuccess = status === 'success' || status === 'sukses';
            
            let iconEl = isPending ? '⏳' : (isSuccess ? '✅' : '❌');
            let statusText = isPending ? 'Sedang Diproses' : (isSuccess ? 'Transaksi Sukses!' : 'Transaksi Gagal');
            let colorClass = isPending ? 'text-warning' : (isSuccess ? 'text-success' : 'text-danger');
            let sn = data.data.sn || '-';
            let msg = data.data.message || '';
            let refId = payload.ref_id || data.data.ref_id || '';
            let trxId = data.data.trx_id || data.data.digiflazz_trx_id || '-';

            document.getElementById('result-icon').innerText = iconEl;
            document.getElementById('result-title').innerText = statusText;
            document.getElementById('result-title').className = 'fw-bold fs-4 ' + colorClass;
            document.getElementById('result-customer').innerText = payload.customer_no || '-';
            document.getElementById('result-product').innerText = payload.product_name || '-';
            document.getElementById('result-price').innerText = formatRp(payload.sell_price || 0);
            document.getElementById('result-sn').innerText = sn;
            document.getElementById('result-msg').innerText = msg;
            document.getElementById('result-refid').innerText = refId;
            document.getElementById('result-trxid').innerText = trxId;

            // Store last transaction for printing
            lastTrxData = {
                ref_id: refId,
                trx_id: trxId,
                product_name: payload.product_name || '-',
                customer_no: payload.customer_no || '-',
                customer_name: payload.customer_name || '',
                brand: payload.brand || '',
                sn: sn,
                sell_price: payload.sell_price || 0,
                created_at: new Date().toLocaleString('id-ID')
            };

            // Remove existing recheck btn if we created one before to avoid duplicates
            let reCheckBtn = document.getElementById('result-recheck-btn');
            if(reCheckBtn) reCheckBtn.remove();

            // Show re-check button only for pending
            if(isPending && refId) {
                // Create a new recheck button
                reCheckBtn = document.createElement('button');
                reCheckBtn.id = 'result-recheck-btn';
                reCheckBtn.className = 'btn btn-warning w-100 rounded-pill fw-bold py-2 mb-2';
                reCheckBtn.disabled = true; // Disabled initially because we auto-poll
                reCheckBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengecek otomatis...';
                reCheckBtn.onclick = () => checkTransactionStatus(payload.sku || '', payload.customer_no, refId);
                
                // Insert it before result-actions
                const actionsDiv = document.getElementById('result-actions');
                actionsDiv.parentNode.insertBefore(reCheckBtn, actionsDiv);
                
                // Auto-poll logic
                let pollCount = 0;
                autoPollInterval = setInterval(async () => {
                    pollCount++;
                    if (pollCount > 15) { // Stop polling after 45 seconds (15 * 3s)
                        clearInterval(autoPollInterval);
                        reCheckBtn.disabled = false;
                        reCheckBtn.innerText = '🔄 Cek Status Lagi';
                        return;
                    }
                    
                    const done = await checkTransactionStatus(payload.sku || '', payload.customer_no, refId, true);
                    if (done) {
                        clearInterval(autoPollInterval);
                    }
                }, 3000); // 3 seconds interval
            }

            // Show action buttons for success or pending (user may want to print receipt early)
            if (isSuccess || isPending) {
                document.getElementById('result-actions').style.display = 'flex';
                document.getElementById('custom-price-container').style.display = 'block';
                document.getElementById('custom-print-price').value = parseInt(payload.sell_price || 0);
                
                // Update printer badge
                let printer = window._ppobPrinter;
                if (!printer && typeof ThermalPrinter !== 'undefined') {
                    printer = window._ppobPrinter = new ThermalPrinter();
                }
                
                if (printer && printer.isConnected()) {
                    const badge = document.getElementById('printer-status-badge');
                    badge.className = 'badge bg-success';
                    badge.innerHTML = '<i class="bi bi-bluetooth me-1"></i>Printer: Terhubung';
                    badge.style.display = 'inline-block';
                } else if (printer && printer.hasSavedDevice()) {
                    const badge = document.getElementById('printer-status-badge');
                    badge.className = 'badge bg-info text-dark';
                    badge.innerHTML = '<i class="bi bi-bluetooth me-1"></i>Printer: Tersimpan (Tidur)';
                    badge.style.display = 'inline-block';
                } else if (navigator.bluetooth) {
                    const badge = document.getElementById('printer-status-badge');
                    badge.className = 'badge bg-secondary';
                    badge.innerHTML = '<i class="bi bi-printer me-1"></i>Printer: Belum Terhubung';
                    badge.style.display = 'inline-block';
                }
                
                // PLN UI Parsing
                const isPln = payload.product_name && payload.product_name.toLowerCase().includes('pln');
                const resultPlnDetails = document.getElementById('result-pln-details');
                if (isPln && sn && sn !== '-' && sn.includes('/')) {
                    const parts = sn.split('/');
                    if (parts.length >= 4) {
                        document.getElementById('result-sn').innerText = parts[0];
                        document.getElementById('result-pln-name').innerText = parts[1] || '-';
                        document.getElementById('result-pln-power').innerText = parts.length > 4 ? `${parts[2]}/${parts[3]}` : parts[2];
                        document.getElementById('result-pln-kwh').innerText = parts.length > 4 ? parts[4] : parts[3];
                        resultPlnDetails.style.display = 'block';
                    } else {
                        resultPlnDetails.style.display = 'none';
                    }
                } else {
                    resultPlnDetails.style.display = 'none';
                }
            } else {
                document.getElementById('result-actions').style.display = 'none';
                document.getElementById('custom-price-container').style.display = 'none';
                document.getElementById('result-pln-details').style.display = 'none';
            }
            
            // Auto prompt save contact if PLN or E-Wallet
            if (isSuccess && currentCategory === 'pln') {
                promptSavePlnContact(payload.customer_no, payload.customer_name || '');
            } else if (isSuccess && currentCategory === 'ewallet' && payload.brand) {
                promptSaveEwalletContact(payload.customer_no, payload.customer_name || '', payload.brand);
            }

            // Listen to modal close to stop polling
            document.getElementById('resultModal').addEventListener('hidden.bs.modal', function () {
                if (autoPollInterval) clearInterval(autoPollInterval);
            }, { once: true });

            fetchBalance(); // Refresh balance
        } else {
            // Check if server is asking for PIN (could happen if page was cached and PIN was set later)
            const msg = data.message || '';
            if (msg.toLowerCase().includes('pin')) {
                // Show PIN modal and retry with same payload
                showPinAndRetry(payload);
            } else {
                showAlert('❌ Gagal: ' + (msg || 'Terjadi kesalahan'), 'danger');
            }
        }
    }, 400); // 400ms delay to wait for loadingModal to hide
    } catch(e) {
        getLoadingModal().hide();
        showAlert('❌ Terjadi kesalahan jaringan saat transaksi.', 'danger');
    }
}

// 10. Check transaction status (polling)
// isAuto parameter prevents button text from flickering during auto-poll
async function checkTransactionStatus(sku, customerNo, refId, isAuto = false) {
    const reCheckBtn = document.getElementById('result-recheck-btn');
    if (!isAuto) {
        reCheckBtn.disabled = true;
        reCheckBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengecek...';
    }
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/check-transaction', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ref_id: refId, sku: sku, customer_no: customerNo})
        });
        const data = await res.json();
        if(data.success && data.data) {
            const status = (data.data.status || 'pending').toLowerCase();
            const isSuccess = status === 'success' || status === 'sukses';
            const isFailed = status === 'failed' || status === 'gagal';
            
            if(isSuccess || isFailed) {
                document.getElementById('result-title').innerText = isSuccess ? 'Transaksi Sukses!' : 'Transaksi Gagal';
                document.getElementById('result-title').className = 'fw-bold fs-4 ' + (isSuccess ? 'text-success' : 'text-danger');
                document.getElementById('result-icon').innerText = isSuccess ? '✅' : '❌';
                document.getElementById('result-sn').innerText = data.data.sn || '-';
                document.getElementById('result-msg').innerText = data.data.message || '';
                reCheckBtn.style.display = 'none';

                // Update last trx data with new SN & Trx ID and show print button
                if (lastTrxData) {
                    lastTrxData.sn = data.data.sn || '-';
                    if (data.data.trx_id || data.data.digiflazz_trx_id) {
                        lastTrxData.trx_id = data.data.trx_id || data.data.digiflazz_trx_id;
                        document.getElementById('result-trxid').innerText = lastTrxData.trx_id;
                    }
                    const configuredPrice = typeof getPpobSellPrice === 'function' ? getPpobSellPrice(lastTrxData.sku) : null;
                    document.getElementById('custom-print-price').value = parseInt(configuredPrice || lastTrxData.sell_price || 0);
                }
                
                if (isSuccess) {
                    document.getElementById('result-actions').style.display = 'flex';
                    document.getElementById('custom-price-container').style.display = 'block';
                    
                    // Update printer badge
                    let printer = window._ppobPrinter;
                    if (!printer && typeof ThermalPrinter !== 'undefined') {
                        printer = window._ppobPrinter = new ThermalPrinter();
                    }
                    
                    if (printer && printer.isConnected()) {
                        const badge = document.getElementById('printer-status-badge');
                        badge.className = 'badge bg-success';
                        badge.innerHTML = '<i class="bi bi-bluetooth me-1"></i>Printer: Terhubung';
                        badge.style.display = 'inline-block';
                    } else if (printer && printer.hasSavedDevice()) {
                        const badge = document.getElementById('printer-status-badge');
                        badge.className = 'badge bg-info text-dark';
                        badge.innerHTML = '<i class="bi bi-bluetooth me-1"></i>Printer: Tersimpan (Tidur)';
                        badge.style.display = 'inline-block';
                    } else if (navigator.bluetooth) {
                        const badge = document.getElementById('printer-status-badge');
                        badge.className = 'badge bg-secondary';
                        badge.innerHTML = '<i class="bi bi-printer me-1"></i>Printer: Belum Terhubung';
                        badge.style.display = 'inline-block';
                    }
                    
                    // PLN UI Parsing for polling success
                    const isPln = lastTrxData && lastTrxData.product_name && lastTrxData.product_name.toLowerCase().includes('pln');
                    const resultPlnDetails = document.getElementById('result-pln-details');
                    if (isPln && lastTrxData.sn && lastTrxData.sn !== '-' && lastTrxData.sn.includes('/')) {
                        const parts = lastTrxData.sn.split('/');
                        if (parts.length >= 4) {
                            document.getElementById('result-sn').innerText = parts[0];
                            document.getElementById('result-pln-name').innerText = parts[1] || '-';
                            document.getElementById('result-pln-power').innerText = parts.length > 4 ? `${parts[2]}/${parts[3]}` : parts[2];
                            document.getElementById('result-pln-kwh').innerText = parts.length > 4 ? parts[4] : parts[3];
                            resultPlnDetails.style.display = 'block';
                        }
                    }
                } else {
                    document.getElementById('result-actions').style.display = 'none';
                    document.getElementById('custom-price-container').style.display = 'none';
                }
                
                if (isSuccess && currentCategory === 'pln') {
                    promptSavePlnContact(customerNo, lastTrxData ? lastTrxData.customer_name : '');
                } else if (isSuccess && currentCategory === 'ewallet' && lastTrxData && lastTrxData.brand) {
                    promptSaveEwalletContact(customerNo, lastTrxData.customer_name || '', lastTrxData.brand);
                }

                return true; // Polling finished
            } else {
                if (!isAuto) {
                    reCheckBtn.disabled = false;
                    reCheckBtn.innerText = '🔄 Cek Status Lagi';
                }
                return false; // Still pending
            }
        }
    } catch(e) { 
        if (!isAuto) {
            reCheckBtn.disabled = false; 
            reCheckBtn.innerText = '🔄 Cek Status Lagi'; 
        }
        return false;
    }
    return false;
}

// 11. Print PPOB Receipt
async function printPpobReceipt() {
    if (!lastTrxData) { showToast('⚠️ Data transaksi tidak ditemukan', 'warning'); return; }
    
    // Override sell price with custom input if valid
    const customPriceInput = document.getElementById('custom-print-price');
    if (customPriceInput && customPriceInput.value) {
        lastTrxData.sell_price = parseInt(customPriceInput.value) || lastTrxData.sell_price;
    }
    
    const btn = document.getElementById('btn-print-receipt');
    const badge = document.getElementById('printer-status-badge');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';
    btn.disabled = true;

    try {
        // Check if ThermalPrinter is available (from printer.js)
        if (typeof ThermalPrinter !== 'undefined') {
            const printer = window._ppobPrinter || (window._ppobPrinter = new ThermalPrinter());
            
            if (navigator.bluetooth && !printer.isConnected()) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghubungkan...';
                const reconnected = await printer.tryAutoReconnect();
                if (!reconnected) {
                    await printer.connect();
                }
                if (badge) {
                    badge.className = 'badge bg-success';
                    badge.innerHTML = '<i class="bi bi-bluetooth me-1"></i>Printer: Terhubung';
                }
            }
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mencetak...';
            await printer.printDigitalReceipt(lastTrxData);
            showToast('✅ Struk berhasil dikirim ke printer', 'success');
        } else {
            // Fallback to browser print if no printer.js
            printPpobReceiptBrowser();
        }
    } catch (e) {
        console.error(e);
        showToast('❌ Gagal mencetak: ' + (e.message || 'Printer tidak ditemukan/dibatalkan'), 'danger');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// Preview Web Receipt
function previewPpobReceipt() {
    if (!lastTrxData) { showToast('⚠️ Data transaksi tidak ditemukan', 'warning'); return; }
    // Override sell price with custom input if valid
    const customPriceInput = document.getElementById('custom-print-price');
    if (customPriceInput && customPriceInput.value) {
        lastTrxData.sell_price = parseInt(customPriceInput.value) || lastTrxData.sell_price;
    }
    printPpobReceiptBrowser();
}

// Share Receipt Image
async function sharePpobReceipt() {
    if (!lastTrxData) { showToast('⚠️ Data transaksi tidak ditemukan', 'warning'); return; }
    
    // Override sell price with custom input if valid
    const customPriceInput = document.getElementById('custom-print-price');
    if (customPriceInput && customPriceInput.value) {
        lastTrxData.sell_price = parseInt(customPriceInput.value) || lastTrxData.sell_price;
    }
    
    const btn = document.getElementById('btn-share-receipt');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
    btn.disabled = true;

    try {
        // Ensure html2canvas is loaded
        if (typeof html2canvas === 'undefined') {
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = '<?= BASE_URL ?>public/js/html2canvas.min.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        const d = lastTrxData;
        const hasSN = d.sn && d.sn !== '-';
        const isPln = d.product_name && d.product_name.toLowerCase().includes('pln');
        
        let snTitle = "SN / TOKEN";
        let snValue = d.sn;
        let plnDetailsHtml = '';
        
        // Parse PLN SN (format: Token/Name/Tarif/Power/Kwh)
        if (isPln && d.sn.includes('/')) {
            const parts = d.sn.split('/');
            if (parts.length >= 4) {
                snTitle = "TOKEN PLN";
                snValue = parts[0];
                const plnName = parts[1] || '';
                const plnTarifPower = parts.length > 4 ? `${parts[2]}/${parts[3]}` : parts[2];
                const plnKwh = parts.length > 4 ? parts[4] : parts[3];
                
                plnDetailsHtml = `
                <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 13px;">
                    <span style="color:#555;">Nama Mtr</span>
                    <span style="font-weight: 600; text-align: right;">${plnName}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 13px;">
                    <span style="color:#555;">Tarif/Daya</span>
                    <span style="font-weight: 600; text-align: right;">${plnTarifPower}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 13px;">
                    <span style="color:#555;">Jml kWh</span>
                    <span style="font-weight: 600; text-align: right;">${plnKwh}</span>
                </div>
                `;
            }
        }
        
        // Create a temporary hidden container for the receipt
        const container = document.createElement('div');
        container.style.position = 'absolute';
        container.style.left = '-9999px';
        container.style.top = '0';
        container.style.background = '#ffffff';
        container.style.width = '320px';
        container.style.padding = '20px';
        container.style.fontFamily = 'sans-serif';
        container.style.color = '#111';
        
        container.innerHTML = `
            <div style="text-align:center; margin-bottom: 20px; border-bottom: 2px dashed #ddd; padding-bottom: 15px;">
                <div style="font-size: 18px; font-weight: 800; color: #000; margin-bottom: 4px;">ALFAREZMART</div>
                <div style="font-size: 11px; color: #666;">Struk Pembayaran Produk Digital</div>
            </div>
            <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 13px;">
                <span style="color:#555;">No. Ref</span>
                <span style="font-weight: 600; text-align: right;">${d.ref_id}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 13px;">
                <span style="color:#555;">Tanggal</span>
                <span style="font-weight: 600; text-align: right;">${d.created_at}</span>
            </div>
            <div style="border-top: 1px dashed #ddd; margin: 12px 0;"></div>
            <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 13px;">
                <span style="color:#555;">Produk</span>
                <span style="font-weight: 600; text-align: right;">${d.product_name}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 13px;">
                <span style="color:#555;">ID / No.</span>
                <span style="font-weight: 600; text-align: right;">${d.customer_no}</span>
            </div>
            ${d.customer_name && !isPln ? `
            <div style="display: flex; justify-content: space-between; margin: 8px 0; font-size: 13px;">
                <span style="color:#555;">Nama</span>
                <span style="font-weight: 600; text-align: right;">${d.customer_name}</span>
            </div>` : ''}
            ${plnDetailsHtml}
            <div style="border-top: 1px dashed #ddd; margin: 12px 0;"></div>
            ${hasSN ? `
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 15px 12px; margin: 15px 0; text-align: center;">
                <div style="font-size: 11px; color: #666; margin-bottom: 8px; font-weight: 800;">${snTitle}</div>
                <div style="font-size: ${snValue.length > 25 ? '16px' : '20px'}; font-weight: 900; color: #000; letter-spacing: 1px; word-break: break-all; font-family: monospace;">${snValue}</div>
            </div>` : ''}
            <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #000; font-size: 16px; font-weight: 800; color: #000;">
                <span>TOTAL BAYAR</span>
                <span>Rp ${parseInt(d.sell_price).toLocaleString('id-ID')}</span>
            </div>
            <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #666; line-height: 1.5;">
                <div style="font-weight: 700; color:#000; margin-bottom:4px;">Terima kasih telah berbelanja</div>
                <div>= Semoga Berkah =</div>
            </div>
        `;
        document.body.appendChild(container);

        // Render to canvas
        const canvas = await html2canvas(container, {
            scale: 2, // Higher quality
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        });
        
        document.body.removeChild(container);

        // Share the image
        canvas.toBlob(async (blob) => {
            if (!blob) {
                showToast('❌ Gagal membuat gambar struk', 'danger');
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
                    showToast('✅ Berhasil membagikan struk!', 'success');
                } catch (err) {
                    console.log('Share canceled or failed', err);
                }
            } else {
                // Fallback: trigger download if Web Share API doesn't support files
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = file.name;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                showToast('✅ Struk berhasil diunduh (perangkat tidak mendukung Web Share)', 'success');
            }
        }, 'image/png');

    } catch (error) {
        console.error('Error sharing receipt:', error);
        showToast('❌ Gagal membagikan struk', 'danger');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// Browser Fallback Print for PPOB Receipt
function printPpobReceiptBrowser() {
    if (!lastTrxData) return;
    const d = lastTrxData;
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
    .sn-value { font-size: ${snValue.length > 25 ? '16px' : '20px'}; font-weight: 900; color: #000; letter-spacing: 1px; word-break: break-all; font-family: monospace; }
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
<div class="row"><div class="label">Trx ID</div><div class="value">${d.digiflazz_trx_id || d.trx_id || '-'}</div></div>
<div class="row"><div class="label">Tanggal</div><div class="value">${d.created_at}</div></div>
<div class="center trx-success">TRANSAKSI BERHASIL</div>
<div class="row"><div class="label">Produk</div><div class="value">${d.product_name}</div></div>
<div class="row"><div class="label">ID / No.</div><div class="value">${d.customer_no}</div></div>
${d.customer_name && !isPln ? `<div class="row"><div class="label">Nama</div><div class="value">${d.customer_name}</div></div>` : ''}
${plnDetailsHtml}
${otherSnHtml}
<div class="line"></div>
${hasSN ? `<div class="sn-box"><div class="sn-title">${snTitle}</div><div class="sn-value">${snValue}</div></div>` : ''}
<div class="total-row"><span>TOTAL BAYAR</span><span>Rp ${parseInt(d.sell_price).toLocaleString('id-ID')}</span></div>
<div class="footer"><div class="bold" style="color:#000;margin-bottom:4px;">Terima kasih telah berbelanja</div><div>= Semoga Berkah =</div></div>
<button class="print-btn no-print" onclick="window.print()">🖨️ Cetak Struk Sekarang</button>
</body></html>`);
    w.document.close();
}

async function promptSavePlnContact(customerNo, defaultName) {
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/customers?type=pln');
        const data = await res.json();
        if (data.success && data.data) {
            const exists = data.data.some(c => c.customer_no === customerNo);
            if (exists) return; // Don't show if already saved
        }
        
        // Grab parsed PLN details from the UI if available
        let plnNameStr = document.getElementById('result-pln-name')?.innerText || defaultName;
        if(plnNameStr === '-') plnNameStr = defaultName;
        const plnPowerStr = document.getElementById('result-pln-power')?.innerText || '';
        
        // Populate modal data and show it
        setTimeout(() => {
            document.getElementById('savePlnContactMessage').innerHTML = `Nomor PLN <b>${customerNo}</b> belum ada di daftar pelanggan. Apakah Anda ingin menyimpannya?`;
            document.getElementById('savePlnContactAlias').value = plnNameStr || '';
            document.getElementById('savePlnContactNo').value = customerNo;
            document.getElementById('savePlnContactDefaultName').value = plnNameStr || '';
            document.getElementById('savePlnContactPower').value = plnPowerStr !== '-' ? plnPowerStr : '';
            
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('savePlnContactModal'));
            modal.show();
        }, 500);
    } catch (e) {
        console.error('Error checking pln contact:', e);
    }
}

async function executeSavePlnContact() {
    const alias = document.getElementById('savePlnContactAlias').value;
    const customerNo = document.getElementById('savePlnContactNo').value;
    const defaultName = document.getElementById('savePlnContactDefaultName').value;
    const power = document.getElementById('savePlnContactPower').value;
    const btn = document.getElementById('btnExecuteSavePln');
    
    if (alias === null || alias.trim() === '') {
        showToast('⚠️ Nama Alias tidak boleh kosong', 'warning');
        return;
    }
    
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    btn.disabled = true;

    try {
        const formData = new URLSearchParams();
        formData.append('type', 'pln');
        formData.append('customer_no', customerNo);
        formData.append('customer_name', alias.trim());
        formData.append('pln_name', defaultName || '');
        formData.append('pln_power', power || '');
        formData.append('csrf_token', csrfToken);
        
        const saveRes = await fetch('<?= BASE_URL ?>api/ppob/customers', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });
        const saveData = await saveRes.json();
        
        if (saveData.success) {
            showToast('✅ Pelanggan PLN berhasil disimpan', 'success');
            bootstrap.Modal.getInstance(document.getElementById('savePlnContactModal')).hide();
        } else {
            showToast('❌ Gagal menyimpan: ' + saveData.message, 'danger');
        }
    } catch (e) {
        console.error('Error saving pln contact:', e);
        showToast('❌ Gagal menyimpan karena gangguan jaringan', 'danger');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// Helper: show non-blocking alert
function showAlert(msg, type='info') {
    const container = document.getElementById('toast-container-ppob');
    const id = 'toast-' + Date.now();
    const colors = {info:'#0dcaf0',success:'#22c55e',danger:'#ef4444',warning:'#f59e0b'};
    
    const toast = document.createElement('div');
    toast.id = id;
    toast.style.cssText = `background:${colors[type] || '#0dcaf0'};color:white;padding:14px 20px;border-radius:12px;font-weight:600;box-shadow:0 5px 15px rgba(0,0,0,0.3);animation:slideInRight 0.3s ease;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;`;
    
    const msgSpan = document.createElement('span');
    msgSpan.innerHTML = msg;
    toast.appendChild(msgSpan);
    
    const copyBtn = document.createElement('button');
    copyBtn.innerHTML = '<i class="bi bi-clipboard"></i>';
    copyBtn.style.cssText = 'background:none;border:none;color:white;cursor:pointer;padding:4px;opacity:0.8;transition:opacity 0.2s;display:flex;align-items:center;justify-content:center;';
    copyBtn.title = "Copy pesan";
    copyBtn.onclick = (e) => {
        e.stopPropagation();
        const temp = document.createElement('div');
        temp.innerHTML = msg;
        const text = temp.textContent || temp.innerText || "";
        navigator.clipboard.writeText(text).then(() => {
            copyBtn.innerHTML = '<i class="bi bi-check2"></i>';
            copyBtn.style.opacity = '1';
            setTimeout(() => {
                copyBtn.innerHTML = '<i class="bi bi-clipboard"></i>';
                copyBtn.style.opacity = '0.8';
            }, 2000);
        }).catch(err => console.error('Failed to copy:', err));
    };
    copyBtn.onmouseover = () => copyBtn.style.opacity = '1';
    copyBtn.onmouseout = () => copyBtn.style.opacity = '0.8';
    
    toast.appendChild(copyBtn);
    container.appendChild(toast);
    
    // Increased timeout to give time to copy
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 6000);
}

// Helper: Custom Confirm Modal
function showConfirm(title, message, onYes) {
    document.getElementById('confirmTitle').innerText = title;
    document.getElementById('confirmMessage').innerHTML = message;
    const modalEl = document.getElementById('confirmModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const btn = document.getElementById('confirmBtnYes');
    
    // Avoid stacking listeners
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    
    newBtn.addEventListener('click', () => {
        modal.hide();
        // Wait for close animation (Bootstrap fade ~300ms) before calling onYes
        modalEl.addEventListener('hidden.bs.modal', () => {
            onYes();
        }, { once: true });
    });
    modal.show();
}

// Test Case Helper
function openTestCaseModal() { getTestCaseModal().show(); }
function useTestNo(no) {
    document.getElementById('customer-no').value = no;
    getTestCaseModal().hide();
    // Jika modal trx belum terbuka, biarkan user buka sendiri,
    // Jika sedang terbuka, input terisi otomatis.
}

async function promptSaveEwalletContact(customerNo, defaultName, brand) {
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/customers?type=ewallet');
        const data = await res.json();
        if (data.success && data.data) {
            const exists = data.data.find(c => c.customer_no === customerNo);
            if (exists) {
                // Jika sudah ada tapi pln_name (Nama DANA) masih kosong, update otomatis di background
                if ((!exists.pln_name || exists.pln_name.trim() === '') && defaultName) {
                    const formData = new URLSearchParams();
                    formData.append('id', exists.id);
                    formData.append('type', exists.type);
                    formData.append('customer_no', exists.customer_no);
                    formData.append('customer_name', exists.customer_name || '');
                    formData.append('pln_name', defaultName);
                    formData.append('pln_power', exists.pln_power || '');
                    formData.append('csrf_token', csrfToken);
                    
                    fetch('<?= BASE_URL ?>api/ppob/customers/update', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData.toString()
                    }).catch(err => console.error('Gagal update nama ewallet otomatis', err));
                }
                return; // Don't show modal if already saved
            }
        }
        
        // Populate modal data and show it
        setTimeout(() => {
            document.getElementById('saveEwalletContactMessage').innerHTML = `Nomor ${brand} <b>${customerNo}</b> belum ada di daftar pelanggan. Apakah Anda ingin menyimpannya?`;
            document.getElementById('saveEwalletContactAlias').value = defaultName || '';
            document.getElementById('saveEwalletContactNo').value = customerNo;
            document.getElementById('saveEwalletContactName').value = defaultName || ''; // Use pln_name for storing ewallet name
            document.getElementById('saveEwalletContactBrand').value = brand;
            
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('saveEwalletContactModal'));
            modal.show();
        }, 500);
    } catch (e) {
        console.error('Error checking ewallet contact:', e);
    }
}

async function executeSaveEwalletContact() {
    const alias = document.getElementById('saveEwalletContactAlias').value;
    const customerNo = document.getElementById('saveEwalletContactNo').value;
    const ewalletName = document.getElementById('saveEwalletContactName').value;
    const btn = document.getElementById('btnExecuteSaveEwallet');
    
    if (alias === null || alias.trim() === '') {
        showToast('⚠️ Nama Alias tidak boleh kosong', 'warning');
        return;
    }
    
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    btn.disabled = true;

    try {
        const formData = new URLSearchParams();
        formData.append('type', 'ewallet');
        formData.append('customer_no', customerNo);
        formData.append('customer_name', alias.trim());
        formData.append('pln_name', ewalletName); // Storing Ewallet Name in pln_name field
        formData.append('csrf_token', csrfToken);
        
        const saveRes = await fetch('<?= BASE_URL ?>api/ppob/customers', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });
        const saveData = await saveRes.json();
        
        if (saveData.success) {
            showToast('✅ Pelanggan E-Wallet berhasil disimpan', 'success');
            bootstrap.Modal.getInstance(document.getElementById('saveEwalletContactModal')).hide();
        } else {
            showToast('❌ Gagal menyimpan: ' + saveData.message, 'danger');
        }
    } catch (e) {
        console.error('Error saving ewallet contact:', e);
        showToast('❌ Gagal menyimpan karena gangguan jaringan', 'danger');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
</script>
