
<style>
/* =========================================================
   PPOB Premium Design System
========================================================= */
.ppob-wrapper {
    max-width: 1000px;
    margin: 0 auto;
    font-family: var(--font-family);
}

/* Hero Section */
.ppob-hero {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 20px;
    padding: 30px;
    position: relative;
    overflow: hidden;
    color: white;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
.ppob-hero::before {
    content: '';
    position: absolute;
    top: -50%; left: -10%;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, transparent 70%);
}
.ppob-hero::after {
    content: '';
    position: absolute;
    bottom: -30%; right: -5%;
    width: 250px; height: 250px;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 70%);
}
.hero-content {
    position: relative;
    z-index: 2;
}
.hero-title {
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #94a3b8;
    margin-bottom: 5px;
}
.hero-balance {
    font-size: 32px;
    font-weight: 800;
    margin: 0;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.hero-actions {
    position: relative;
    z-index: 2;
}
.btn-deposit {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 600;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}
.btn-deposit:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
    color: white;
}

/* Category Grid */
.section-title {
    font-weight: 700;
    font-size: 18px;
    margin-bottom: 15px;
    color: var(--text-primary);
}
.cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}
.cat-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 20px 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}
.cat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1);
    border-color: var(--primary);
}
.cat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.cat-icon.blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.cat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.cat-icon.orange { background: rgba(249, 115, 22, 0.1); color: #f97316; }
.cat-icon.green { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.cat-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
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
    border-radius: 12px;
    padding: 15px 20px;
    font-size: 16px;
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
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 15px;
    margin-top: 20px;
}
.prod-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 125px;
    position: relative;
    overflow: hidden;
}
.prod-card:hover {
    border-color: var(--primary);
    background: var(--surface-2);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}
.prod-name { font-size: 13.5px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; line-height: 1.35; letter-spacing: -0.2px; }
.prod-desc { font-size: 11px; color: var(--text-muted); margin-bottom: 12px; line-height: 1.45; white-space: pre-wrap; word-break: break-word; }
.prod-price { font-size: 15.5px; font-weight: 800; color: var(--primary); margin-top: auto; }

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
</style>

<div class="container-fluid py-4 ppob-wrapper">
    
    <!-- Hero / Balance Section -->
    <div class="ppob-hero">
        <div class="hero-content">
            <div class="hero-title"><i class="bi bi-wallet2 me-2"></i>Saldo Digiflazz</div>
            <div class="hero-balance" id="live-balance">
                <span class="spinner-border spinner-border-sm"></span> Loading...
            </div>
        </div>
        <div class="hero-actions">
            <button class="btn btn-deposit" onclick="openDepositModal()"><i class="bi bi-plus-lg me-2"></i>Topup Saldo</button>
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
    </div><!-- end .cat-grid Pascabayar -->

    <!-- Admin Tools Section -->
    <h4 class="section-title mt-4">PPOB Admin Tools</h4>
    <div class="row g-3 mb-5">
        <div class="col-6 col-md-3">
            <a href="<?= BASE_URL ?>ppob/prices" class="btn btn-outline-primary w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-tags d-block fs-4 mb-1"></i> Daftar Harga
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?= BASE_URL ?>ppob/history" class="btn btn-outline-success w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-clock-history d-block fs-4 mb-1"></i> Histori Transaksi
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?= BASE_URL ?>ppob/settings" class="btn btn-outline-warning w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-gear d-block fs-4 mb-1"></i> Pengaturan
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?= BASE_URL ?>ppob/docs" class="btn btn-outline-info w-100 py-3 rounded-4" style="background: var(--surface-1);">
                <i class="bi bi-book d-block fs-4 mb-1"></i> Dokumentasi & API
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
                    <label class="form-label fw-bold small text-muted text-uppercase" style="letter-spacing: 0.5px;">Nomor Tujuan / Pelanggan</label>
                    <div class="d-flex position-relative align-items-center">
                        <input type="text" class="form-control glass-input w-100" id="customer-no" placeholder="Masukkan nomor..." style="padding-right:110px;">
                        <div id="provider-badge" class="position-absolute end-0 me-3 fw-bold" style="font-size:10px;display:none;padding:5px 10px;border-radius:20px;background:var(--primary);color:#fff;text-transform:uppercase;letter-spacing:0.5px;pointer-events:none; margin-right: 60px !important;"></div>
                        <button class="btn btn-primary position-absolute end-0 h-100 px-4" id="btn-inquiry" onclick="performInquiry()" style="display:none; border-radius: 0 12px 12px 0;">Cek</button>
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
                    <div id="brand-filter-container" class="mb-3 d-flex gap-2 overflow-auto pb-2" style="display:none; white-space:nowrap; scrollbar-width: none;"></div>

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

<!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-0" style="background: var(--surface-2);">
                <h5 class="modal-title fw-bold"><i class="bi bi-wallet2 me-2"></i>Topup Saldo Digiflazz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: var(--btn-close-filter, invert(1) grayscale(100%) brightness(200%));"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info rounded-3" style="font-size: 13px;">
                    <i class="bi bi-info-circle-fill me-2"></i> Deposit akan langsung masuk ke akun Digiflazz Anda secara otomatis jika transfer sesuai nominal hingga 3 digit terakhir.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nominal Deposit (Min Rp 50.000)</label>
                    <input type="number" class="form-control glass-input" id="depo-amount" placeholder="50000">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Pilih Bank Tujuan</label>
                    <select class="form-select glass-input" id="depo-bank">
                        <option value="BCA">BCA</option>
                        <option value="MANDIRI">MANDIRI</option>
                        <option value="BRI">BRI</option>
                        <option value="BNI">BNI</option>
                        <option value="FLIP">FLIP</option>
                        <option value="SHOPEEPAY">SHOPEEPAY</option>
                        <option value="GOPAY">GOPAY</option>
                        <option value="JAGO">BANK JAGO</option>
                    </select>
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

<!-- Deposit Result Modal -->
<div class="modal fade" id="depoResultModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4" style="border-radius: 20px; border: none;">
            <div class="mb-3"><i class="bi bi-check-circle-fill text-success" style="font-size: 50px;"></i></div>
            <h4 class="fw-bold mb-1">Tiket Deposit Berhasil</h4>
            <p class="text-muted small mb-4">Silakan transfer sesuai instruksi di bawah ini.</p>
            
            <div class="rounded-3 p-3 mb-3 text-start" style="background: var(--surface-2);">
                <div class="small text-muted mb-1">Transfer Tepat Sesuai Nominal (Termasuk 3 Digit Terakhir):</div>
                <h2 class="text-primary fw-bold mb-3" id="dr-amount" style="letter-spacing: 1px;">Rp 0</h2>
                
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

<!-- PIN Verification Modal -->
<div class="modal fade" id="pinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:20px;border:none;background:var(--surface-1);">
            <div class="modal-body p-4 text-center">
                <div class="mb-3"><i class="bi bi-shield-lock text-warning" style="font-size:50px;"></i></div>
                <h5 class="fw-bold mb-3" style="color:var(--text-primary);">Masukkan PIN</h5>
                <p class="small mb-3" style="color:var(--text-secondary);">Transaksi PPOB ini membutuhkan PIN otorisasi.</p>
                <div class="mb-4 position-relative">
                    <input type="password" class="form-control glass-input text-center fs-4" id="trx-pin-input" placeholder="••••" style="letter-spacing: 5px;">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary flex-grow-1 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-warning flex-grow-1 rounded-pill fw-bold" id="pinBtnVerify">Verifikasi</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal (Blocking UI during purchase) -->
<div class="modal" id="loadingModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0" style="background: transparent;">
            <div class="spinner-border text-primary" style="width: 2rem; height: 2rem; border-width: 0.2rem;" role="status"></div>
            <div class="mt-3 text-white fw-semibold shadow-sm" style="font-size: 0.95rem; letter-spacing: 0.5px;">Memproses...</div>
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
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Ref ID</span>
                            <span class="text-muted" id="result-refid" style="font-size: 11px;">-</span>
                        </div>
                    </div>

                    <button class="btn btn-warning w-100 rounded-pill fw-bold py-2 mb-2" id="result-recheck-btn" style="display:none;">
                        🔄 Cek Status Terbaru
                    </button>
                    <button class="btn btn-secondary w-100 rounded-pill py-2" data-bs-dismiss="modal">Tutup</button>
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
function getLoadingModal() { return bootstrap.Modal.getOrCreateInstance(document.getElementById('loadingModal')); }

let currentCategory = '';
let currentType = '';
let currentProducts = [];
let selectedInqData = null; // Storing postpaid / PLN inquiry data

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

// 2. Open Deposit
function openDepositModal() {
    getDepositModal().show();
}

// 3. Request Deposit
async function requestDeposit() {
    const amount = document.getElementById('depo-amount').value;
    const bank = document.getElementById('depo-bank').value;
    const owner = document.getElementById('depo-owner').value;
    const btn = document.getElementById('btn-depo');

    if(!amount || !bank || !owner) { showAlert('⚠️ Harap isi semua kolom deposit.', 'warning'); return; }
    
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/deposit', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({amount, bank, owner_name: owner})
        });
        const data = await res.json();
        if(data.success && data.data) {
            // Digiflazz returns notes as a string with transfer instruction
            const notes = data.data.notes || 'Silakan cek mutasi atau panduan transfer dari Digiflazz.';
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
            document.getElementById('dr-notes').innerText = notes;
            
            // Try to parse the notes to get Bank, Acc No, and Name
            // e.g. "Silahkan transfer ... ke BCA 123456 a/n PT Digiflazz"
            // or "Transfer nominal Rp... ke rekening BCA 123456 a.n PT Digiflazz"
            const match = notes.match(/ke\s+(?:rekening\s+)?([a-zA-Z\s]+?)\s+(\d{8,})\s*a\.?\/?n\.?\s+(.*)/i) 
                          || notes.match(/(BCA|MANDIRI|BRI|BNI|FLIP|SHOPEEPAY|GOPAY)\s+(\d{8,})\s*a\.?\/?n\.?\s+(.*)/i);
            const parsedContainer = document.getElementById('dr-parsed-dest');
            if (match) {
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

// 4. Open Transaction Modal
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
    document.getElementById('brand-filter-container').style.display = 'none';
    document.getElementById('product-search-container').style.display = 'none';
    document.getElementById('search-product').value = '';
    document.getElementById('customer-no').style.paddingRight = '20px';
    document.getElementById('btn-pay-postpaid').style.display = 'none';
    document.getElementById('product-list-container').style.display = 'block';
    document.getElementById('product-grid').innerHTML = '';
    
    if (type === 'postpaid') {
        // Tagihan Pasca: Harus Cek Dulu
        document.getElementById('customer-no').placeholder = 'Masukkan ID Pelanggan';
        document.getElementById('btn-inquiry').style.display = 'block';
        document.getElementById('customer-no').style.paddingRight = '100px';
        document.getElementById('product-list-container').style.display = 'none'; // Sembunyikan list produk
        // Auto load products in background just to get SKU for inquiry
        loadProducts(category, type); 
    } else if (category === 'pln') {
        // PLN Prabayar: Opsional Cek Nama (Inquiry PLN)
        document.getElementById('customer-no').placeholder = 'Masukkan Nomor Meter/IDPEL';
        document.getElementById('btn-inquiry').style.display = 'block';
        document.getElementById('customer-no').style.paddingRight = '120px';
        document.getElementById('btn-inquiry').innerText = 'Cek Nama';
        // Auto load products to buy
        loadProducts(category, type);
    } else {
        // Pulsa / Data biasa: Auto search on input
        document.getElementById('customer-no').placeholder = 'Masukkan Nomor HP (0812...)';
        document.getElementById('customer-no').style.paddingRight = '100px';
        // Auto load products to buy based on prefix logic
        loadProducts(category, type);
    }

    getTrxModal().show();
}

// 5. Load Products
async function loadProducts(category, type) {
    document.getElementById('product-loading').style.display = 'block';
    document.getElementById('product-grid').innerHTML = '';
    try {
        const res = await fetch(`<?= BASE_URL ?>api/ppob/products/${category}?type=${type}`);
        const data = await res.json();
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
        '0811': 'TELKOMSEL', '0812': 'TELKOMSEL', '0813': 'TELKOMSEL', '0821': 'TELKOMSEL', '0822': 'TELKOMSEL', '0852': 'TELKOMSEL', '0853': 'TELKOMSEL', '0851': 'TELKOMSEL',
        '0814': 'INDOSAT', '0815': 'INDOSAT', '0816': 'INDOSAT', '0855': 'INDOSAT', '0856': 'INDOSAT', '0857': 'INDOSAT', '0858': 'INDOSAT',
        '0817': 'XL', '0818': 'XL', '0819': 'XL', '0859': 'XL', '0877': 'XL', '0878': 'XL', 
        '0838': 'AXIS', '0831': 'AXIS', '0832': 'AXIS', '0833': 'AXIS',
        '0895': 'THREE', '0896': 'THREE', '0897': 'THREE', '0898': 'THREE', '0899': 'THREE',
        '0881': 'SMARTFREN', '0882': 'SMARTFREN', '0883': 'SMARTFREN', '0884': 'SMARTFREN', '0885': 'SMARTFREN', '0886': 'SMARTFREN', '0887': 'SMARTFREN', '0888': 'SMARTFREN', '0889': 'SMARTFREN'
    };
    return prefixes[prefix] || null;
}

function filterProductsByPrefix(phone) {
    if (currentCategory !== 'pulsa' && currentCategory !== 'data') return;
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
    if (currentCategory === 'pulsa' || currentCategory === 'data') {
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

function renderProducts(products) {
    const grid = document.getElementById('product-grid');
    grid.innerHTML = '';
    
    if (currentCategory !== 'pulsa' && currentCategory !== 'data') {
        document.getElementById('product-search-container').style.display = products.length > 5 ? 'block' : 'none';
    }
    products.forEach(p => {
        const card = document.createElement('div');
        card.className = 'prod-card';
        card.onclick = () => confirmPurchase(p);
        card.innerHTML = `
            <div>
                <div class="prod-name">${p.product_name}</div>
                <div class="prod-desc">${p.description || ''}</div>
            </div>
            <div class="prod-price">${formatRp(p.seller_price)}</div>
        `;
        grid.appendChild(card);
    });
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
                inqBox.style.display = 'block';
                document.getElementById('inq-name').innerText = data.data.name;
                document.getElementById('inq-price').innerText = data.data.segment_power || '-';
                document.getElementById('inq-detail-label').style.display = 'none';
                document.getElementById('inq-detail').style.display = 'none';
                document.getElementById('btn-pay-postpaid').style.display = 'none'; // Prabayar pilih produk di bawah
            } else { showAlert('❌ ' + (data.message || 'ID pelanggan PLN tidak ditemukan'), 'danger'); }
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

// 7. Confirm Purchase (Prepaid)
async function confirmPurchase(product) {
    const no = document.getElementById('customer-no').value;
    if(!no) { showAlert('⚠️ Masukkan nomor HP/Tujuan terlebih dahulu!', 'warning'); return; }
    
    showConfirm('Konfirmasi Transaksi', `Produk: <b>${product.product_name}</b><br>Nomor: <b>${no}</b><br>Harga: <b>${formatRp(product.seller_price)}</b>`, () => {
        processTransaction({
            sku: product.buyer_sku_code,
            customer_no: no,
            sell_price: product.seller_price,
            product_name: product.product_name
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
        executeTransactionAPI(payload);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('pinBtnVerify')?.addEventListener('click', () => {
        const pin = document.getElementById('trx-pin-input').value;
        if(!pin) { showAlert('⚠️ Masukkan PIN!', 'warning'); return; }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('pinModal')).hide();
        
        if(pendingTrxPayload) {
            pendingTrxPayload.pin = pin;
            executeTransactionAPI(pendingTrxPayload);
        }
    });
});

// 10. Execute Transaction API
async function executeTransactionAPI(payload) {
    getTrxModal().hide();
    getLoadingModal().show();
    
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/transaction', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        getLoadingModal().hide();
        
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

            document.getElementById('result-icon').innerText = iconEl;
            document.getElementById('result-title').innerText = statusText;
            document.getElementById('result-title').className = 'fw-bold fs-4 ' + colorClass;
            document.getElementById('result-customer').innerText = payload.customer_no || '-';
            document.getElementById('result-product').innerText = payload.product_name || '-';
            document.getElementById('result-price').innerText = formatRp(payload.sell_price || 0);
            document.getElementById('result-sn').innerText = sn;
            document.getElementById('result-msg').innerText = msg;
            document.getElementById('result-refid').innerText = refId;

            // Show re-check button only for pending
            const reCheckBtn = document.getElementById('result-recheck-btn');
            if(isPending && refId) {
                reCheckBtn.style.display = 'block';
                reCheckBtn.onclick = () => checkTransactionStatus(payload.sku || '', payload.customer_no, refId);
            } else {
                reCheckBtn.style.display = 'none';
            }

            const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            resultModal.show();
            fetchBalance(); // Refresh balance
        } else {
            showAlert('❌ Gagal: ' + (data.message || 'Terjadi kesalahan'), 'danger');
        }
    } catch(e) {
        getLoadingModal().hide();
        showAlert('❌ Terjadi kesalahan jaringan saat transaksi.', 'danger');
    }
}

// 10. Check transaction status (polling)
async function checkTransactionStatus(sku, customerNo, refId) {
    const reCheckBtn = document.getElementById('result-recheck-btn');
    reCheckBtn.disabled = true;
    reCheckBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengecek...';
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
                reCheckBtn.style.display = 'none';
            } else {
                reCheckBtn.disabled = false;
                reCheckBtn.innerText = '🔄 Cek Status Lagi';
            }
        }
    } catch(e) { reCheckBtn.disabled = false; reCheckBtn.innerText = '🔄 Cek Status Lagi'; }
}

// Helper: show non-blocking alert
function showAlert(msg, type='info') {
    const container = document.getElementById('toast-container-ppob');
    const id = 'toast-' + Date.now();
    const colors = {info:'#0dcaf0',success:'#22c55e',danger:'#ef4444',warning:'#f59e0b'};
    container.innerHTML += `
        <div id="${id}" style="background:${colors[type] || '#0dcaf0'};color:white;padding:14px 20px;border-radius:12px;font-weight:600;box-shadow:0 5px 15px rgba(0,0,0,0.3);animation:slideInRight 0.3s ease;">${msg}</div>
    `;
    setTimeout(() => document.getElementById(id)?.remove(), 4000);
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
</script>
