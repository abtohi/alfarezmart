<!-- Barcode Scanner View - Rich Product Detail with E-Commerce Split Showcase & Supplier History (v1.2.0) -->
<?php
$scannerUserLevel = $_SESSION['user_level'] ?? '';
$scannerIsSuperadmin = $scannerUserLevel === 'superadmin';
?>
<div class="page-section" style="padding-bottom:100px;">
    <!-- Modern Hero Scanner Terminal Card -->
    <div class="scanner-hero-card">
        <div class="scanner-hero-glow"></div>
        <div class="scanner-hero-inner">
            <!-- Top Status Row -->
            <div class="scanner-status-row">
                <div class="scanner-live-badge">
                    <span class="scanner-pulse-dot"></span>
                    <span>SCANNER ENGINE ACTIVE</span>
                </div>
                <div class="scanner-hardware-pill" onclick="document.getElementById('barcodeInput').focus()" title="Tekan F2 untuk fokus scanner">
                    <i class="bi bi-bluetooth text-info"></i>
                    <span>USB / Bluetooth Gun Ready</span>
                    <kbd class="scanner-kbd">F2</kbd>
                </div>
            </div>

            <!-- Title & Subtitle -->
            <div class="scanner-headline-wrap">
                <h1 class="scanner-page-title">
                    <span class="scanner-icon-badge"><i class="bi bi-upc-scan"></i></span>
                    <span class="scanner-title-text">Cek Harga &amp; Scan Barcode</span>
                </h1>
                <p class="scanner-page-subtitle">
                    Pindai barcode fisik atau ketik nama/kode barang untuk informasi harga eceran, grosir bertingkat, stok real-time, dan riwayat modal.
                </p>
            </div>

            <!-- Sleek Input Capsule with Quick Action Buttons -->
            <div class="scanner-search-capsule">
                <div class="scanner-input-lens" onclick="openGlobalScanner()" title="Klik untuk Buka Kamera Scanner">
                    <i class="bi bi-camera-fill"></i>
                </div>
                <input type="text" id="barcodeInput" 
                       placeholder="Scan barcode USB/kamera atau ketik nama produk..." 
                       autocomplete="off" autofocus>
                
                <button type="button" id="scannerClearBtn" class="scanner-clear-btn" onclick="clearScannerSearch()" title="Bersihkan input" style="display:none;">
                    <i class="bi bi-x-circle-fill"></i>
                </button>

                <button type="button" onclick="openGlobalScanner()" class="scanner-cam-btn" title="Buka Kamera Scanner">
                    <i class="bi bi-camera-video-fill"></i>
                    <span class="btn-text">Kamera</span>
                </button>

                <button type="button" onclick="lookupBarcode()" class="scanner-submit-btn">
                    <i class="bi bi-search"></i>
                    <span class="btn-text">Cari</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Result Area (Empty State Hub, Multiple Results, or Single Product Detail) -->
    <div id="scanResult"></div>
</div>

<style>
/* ==========================================================================
   ALFAREZMAKT SCANNER - LUXURY RETAIL UI & HIGH-RESPONSIVE STYLING (v2.0)
   ========================================================================== */

/* Hero Terminal Card */
.scanner-hero-card {
    position: relative;
    background: linear-gradient(135deg, var(--surface-1) 0%, var(--surface-2) 100%);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg, 20px);
    padding: 24px 26px;
    margin-bottom: 22px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}
.scanner-hero-glow {
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
    pointer-events: none;
    border-radius: 50%;
}
.scanner-hero-inner {
    position: relative;
    z-index: 2;
}

/* Status Row */
.scanner-status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}
.scanner-live-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.6px;
    padding: 4px 12px;
    border-radius: 9999px;
    text-transform: uppercase;
}
.scanner-pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: scannerPulse 1.8s infinite;
}
@keyframes scannerPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.scanner-hardware-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 11px;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s ease;
}
.scanner-hardware-pill:hover {
    border-color: var(--primary);
    color: var(--text-primary);
}
.scanner-kbd {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Headline */
.scanner-headline-wrap {
    margin-bottom: 18px;
}
.scanner-page-title {
    font-size: 1.45rem;
    font-weight: 800;
    margin-bottom: 6px;
    color: var(--text-primary);
    letter-spacing: -0.4px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.scanner-icon-badge {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary, #3b82f6), #6366f1);
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    flex-shrink: 0;
}
.scanner-page-subtitle {
    color: var(--text-muted);
    font-size: 12.5px;
    margin: 0;
    line-height: 1.45;
}

/* Modern Input Capsule */
.scanner-search-capsule {
    background: var(--surface-1);
    border: 1.5px solid var(--border-color);
    border-radius: 14px;
    padding: 6px 8px 6px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    transition: all 0.25s ease;
}
.scanner-search-capsule:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2), 0 8px 24px rgba(0, 0, 0, 0.07);
}
.scanner-input-lens {
    color: var(--primary);
    font-size: 1.25rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
}
.scanner-input-lens:hover {
    transform: scale(1.15);
}
.scanner-search-capsule input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 8px 0;
    color: var(--text-primary);
    font-size: 14px;
    font-weight: 500;
    outline: none;
    font-family: inherit;
}
.scanner-search-capsule input::placeholder {
    color: var(--text-muted);
    opacity: 0.75;
}

.scanner-clear-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    font-size: 1.1rem;
    cursor: pointer;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    transition: color 0.15s;
}
.scanner-clear-btn:hover {
    color: var(--danger, #ef4444);
}

.scanner-cam-btn {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(59, 130, 246, 0.08));
    color: var(--primary, #3b82f6);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: 10px;
    padding: 9px 15px;
    font-weight: 700;
    font-size: 12.5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.scanner-cam-btn:hover {
    background: var(--primary, #3b82f6);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.scanner-submit-btn {
    background: linear-gradient(135deg, var(--primary, #3b82f6), #4f46e5);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 9px 20px;
    font-weight: 700;
    font-size: 12.5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
    white-space: nowrap;
}
.scanner-submit-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(99, 102, 241, 0.38);
}

/* ==========================================================================
   INTERACTIVE EMPTY STATE HUB (Cyber Holographic Viewfinder)
   ========================================================================== */
.scanner-hub-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg, 20px);
    padding: 36px 24px;
    text-align: center;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.03);
    margin-bottom: 24px;
}

.scanner-viewfinder-wrapper {
    margin-bottom: 30px;
}
.scanner-viewfinder-reticle {
    position: relative;
    width: 200px;
    height: 130px;
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(180deg, rgba(99, 102, 241, 0.04) 0%, rgba(99, 102, 241, 0.01) 100%);
    border-radius: 16px;
    overflow: hidden;
}
.corner {
    position: absolute;
    width: 18px;
    height: 18px;
    border-color: var(--primary, #6366f1);
    border-style: solid;
}
.corner-tl { top: 8px; left: 8px; border-width: 3px 0 0 3px; border-top-left-radius: 6px; }
.corner-tr { top: 8px; right: 8px; border-width: 3px 3px 0 0; border-top-right-radius: 6px; }
.corner-bl { bottom: 8px; left: 8px; border-width: 0 0 3px 3px; border-bottom-left-radius: 6px; }
.corner-br { bottom: 8px; right: 8px; border-width: 0 3px 3px 0; border-bottom-right-radius: 6px; }

.scanner-laser-beam {
    position: absolute;
    left: 12px;
    right: 12px;
    height: 2.5px;
    background: linear-gradient(90deg, transparent, #ef4444, #f43f5e, #ef4444, transparent);
    box-shadow: 0 0 14px 2px rgba(239, 68, 68, 0.85);
    border-radius: 2px;
    animation: laserScanMotion 2.4s ease-in-out infinite;
    z-index: 3;
}
@keyframes laserScanMotion {
    0%, 100% { top: 18%; opacity: 0.35; }
    50% { top: 80%; opacity: 1; }
}

.scanner-barcode-silhouette {
    color: var(--text-muted);
    opacity: 0.35;
    transition: opacity 0.3s;
}
.scanner-viewfinder-reticle:hover .scanner-barcode-silhouette {
    opacity: 0.6;
}

.scanner-hub-label h3 {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 4px;
    letter-spacing: -0.2px;
}
.scanner-hub-label p {
    font-size: 12.5px;
    color: var(--text-muted);
    margin: 0;
}

/* 3 Features Action Grid */
.scanner-features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    max-width: 900px;
    margin: 0 auto 28px;
    text-align: left;
}
.scanner-feature-card {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 14px);
    padding: 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s ease;
    cursor: pointer;
}
.scanner-feature-card:hover {
    transform: translateY(-4px);
    border-color: var(--primary);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
}
.sfc-icon-wrapper {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 12px;
}
.sfc-icon-wrapper.camera {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(59, 130, 246, 0.1));
    color: #6366f1;
}
.sfc-icon-wrapper.hardware {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.1));
    color: #10b981;
}
.sfc-icon-wrapper.database {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.1));
    color: #f59e0b;
}

.sfc-content h4 {
    font-size: 13.5px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.sfc-content p {
    font-size: 11.5px;
    color: var(--text-muted);
    margin: 0 0 12px;
    line-height: 1.4;
}

.btn-sfc {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: var(--primary);
}
.badge-sfc-ready {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10.5px;
    font-weight: 700;
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
    padding: 3px 9px;
    border-radius: 6px;
}
.badge-sfc-info {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10.5px;
    font-weight: 700;
    color: #f59e0b;
    background: rgba(245, 158, 11, 0.1);
    padding: 3px 9px;
    border-radius: 6px;
}

/* Quick Search Suggestions */
.scanner-quick-search-box {
    border-top: 1px dashed var(--border-color);
    padding-top: 20px;
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
}
.sqs-title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary);
}
.sqs-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.sqs-chip {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    border-radius: 9999px;
    padding: 4px 12px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.sqs-chip:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
    transform: translateY(-1px);
}

/* ==========================================================================
   SEARCH RESULTS GRID (Multiple Results)
   ========================================================================== */
.scan-results-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
}
@media (min-width: 640px) {
    .scan-results-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (min-width: 992px) {
    .scan-results-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}

.scan-result-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg, 16px);
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s ease;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    cursor: pointer;
    box-sizing: border-box;
    height: 100%;
}
.scan-result-card:hover {
    transform: translateY(-4px);
    border-color: var(--primary) !important;
    box-shadow: 0 12px 32px rgba(0,0,0,0.12) !important;
}

.scan-card-image-stage {
    width: 100%;
    height: 180px;
    border-radius: var(--radius-md, 12px);
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 14px;
    position: relative;
}
.scan-card-image-stage img {
    max-height: 160px;
    width: auto;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.scan-result-card:hover .scan-card-image-stage img {
    transform: scale(1.08);
}
.scan-card-no-image {
    font-size: 3.2rem;
    color: var(--primary);
    opacity: 0.5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.scan-card-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.35;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 6px;
}
.scan-pill-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 6px;
    display: inline-block;
}
.scan-pill-badge.brand {
    background: rgba(59,130,246,0.12);
    color: #3b82f6;
    border: 1px solid rgba(59,130,246,0.2);
}
.scan-pill-badge.category {
    background: rgba(16,185,129,0.12);
    color: #10b981;
    border: 1px solid rgba(16,185,129,0.2);
}
.scan-short-label {
    font-size: 10px;
    color: var(--text-muted);
    background: var(--surface-2);
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
}

/* ==========================================================================
   MOBILE RESPONSIVENESS (Max-Width 991.98px)
   ========================================================================== */
@media (max-width: 991.98px) {
    .scanner-hero-card {
        padding: 18px 16px !important;
        margin-bottom: 16px !important;
        border-radius: 16px !important;
    }
    .scanner-page-title {
        font-size: 1.18rem !important;
        gap: 8px !important;
    }
    .scanner-icon-badge {
        width: 30px !important;
        height: 30px !important;
        font-size: 1rem !important;
    }
    .scanner-page-subtitle {
        font-size: 11px !important;
    }
    .scanner-search-capsule {
        padding: 4px 6px 4px 10px !important;
        gap: 6px !important;
    }
    .scanner-search-capsule input {
        font-size: 13px !important;
    }
    .scanner-cam-btn {
        padding: 7px 11px !important;
        font-size: 11px !important;
    }
    .scanner-submit-btn {
        padding: 7px 14px !important;
        font-size: 11px !important;
    }
    .scanner-features-grid {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    .scanner-hub-card {
        padding: 24px 16px !important;
    }

    /* Back Button Bar Mobile */
    .scanner-back-btn-wrapper {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 8px !important;
        margin-bottom: 16px !important;
        width: 100% !important;
        position: relative !important;
        z-index: 10 !important;
    }
    .scanner-back-btn {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        font-size: 11px !important;
        padding: 6px 12px !important;
        border-radius: 8px !important;
    }
    .scanner-back-keyword {
        font-size: 11px !important;
        text-align: right !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 55% !important;
        color: var(--text-muted) !important;
    }

    /* Mobile Flex Wrapper */
    .scan-mobile-flex-wrapper {
        display: flex !important;
        flex-direction: column !important;
        gap: 14px !important;
    }
    .scan-mobile-flex-col {
        display: contents !important;
    }
    .scan-m-order-1 { order: 1 !important; }
    .scan-m-order-2 { order: 2 !important; }
    .scan-m-order-3 { order: 3 !important; }
    .scan-m-order-4 { order: 4 !important; }
    .scan-m-order-5 { order: 5 !important; }
    .scan-m-order-6 { order: 6 !important; }

    /* Photo Stage Mobile */
    .scanner-detail-photo-stage {
        max-height: 240px !important;
        padding: 12px !important;
    }
    .scanner-detail-photo-stage img {
        max-height: 210px !important;
    }

    /* Header & Pricing Mobile */
    .scanner-detail-header-card {
        padding: 16px !important;
    }
    .scanner-detail-prod-title {
        font-size: 1.15rem !important;
    }
    .scanner-detail-pkg-card {
        padding: 16px !important;
    }
    .scanner-pkg-card {
        padding: 12px !important;
        margin-bottom: 10px !important;
    }
    .scanner-price-grid {
        gap: 8px !important;
    }
    .scanner-price-box {
        padding: 8px 10px !important;
    }
    .scanner-price-val {
        font-size: 15px !important;
    }
    .scanner-modal-text {
        font-size: 11px !important;
        font-weight: 700 !important;
    }
    .scanner-detail-info-card {
        padding: 16px !important;
    }
    .scanner-detail-supplier-card {
        padding: 16px !important;
    }
}
</style>


<script>
const SCANNER_IS_SUPERADMIN = <?= $scannerIsSuperadmin ? 'true' : 'false' ?>;

// Search state for Back button navigation
let lastSearchKeyword = '';
let lastSearchResultsData = null;

let _scannerJustSearched = false;
let _scannerAutoLookupTimer = null;

function renderEmptyStateHub() {
    const resultDiv = document.getElementById('scanResult');
    if (!resultDiv) return;
    resultDiv.innerHTML = `
        <div id="scannerEmptyState" class="scanner-hub-card">
            <div class="scanner-viewfinder-wrapper">
                <div class="scanner-viewfinder-reticle">
                    <div class="corner corner-tl"></div>
                    <div class="corner corner-tr"></div>
                    <div class="corner corner-bl"></div>
                    <div class="corner corner-br"></div>
                    <div class="scanner-laser-beam"></div>
                    <div class="scanner-barcode-silhouette">
                        <svg viewBox="0 0 160 80" width="140" height="70" fill="currentColor">
                            <rect x="0" y="5" width="4" height="70" rx="1"/>
                            <rect x="7" y="5" width="8" height="70" rx="1"/>
                            <rect x="18" y="5" width="3" height="70" rx="1"/>
                            <rect x="24" y="5" width="10" height="70" rx="1"/>
                            <rect x="37" y="5" width="5" height="70" rx="1"/>
                            <rect x="45" y="5" width="3" height="70" rx="1"/>
                            <rect x="51" y="5" width="8" height="70" rx="1"/>
                            <rect x="62" y="5" width="4" height="70" rx="1"/>
                            <rect x="69" y="5" width="12" height="70" rx="1"/>
                            <rect x="84" y="5" width="4" height="70" rx="1"/>
                            <rect x="91" y="5" width="6" height="70" rx="1"/>
                            <rect x="100" y="5" width="10" height="70" rx="1"/>
                            <rect x="113" y="5" width="3" height="70" rx="1"/>
                            <rect x="119" y="5" width="8" height="70" rx="1"/>
                            <rect x="130" y="5" width="4" height="70" rx="1"/>
                            <rect x="137" y="5" width="10" height="70" rx="1"/>
                            <rect x="150" y="5" width="4" height="70" rx="1"/>
                        </svg>
                    </div>
                </div>
                <div class="scanner-hub-label">
                    <h3>Siap Memindai Produk</h3>
                    <p>Arahkan barcode ke scanner fisik USB/Bluetooth, gunakan kamera HP, atau ketik nama barang</p>
                </div>
            </div>

            <div class="scanner-features-grid">
                <div class="scanner-feature-card" onclick="openGlobalScanner()">
                    <div class="sfc-icon-wrapper camera">
                        <i class="bi bi-camera-video-fill"></i>
                    </div>
                    <div class="sfc-content">
                        <h4>Kamera Scanner HP</h4>
                        <p>Gunakan kamera smartphone atau webcam laptop untuk membaca barcode fisik seketika</p>
                    </div>
                    <div class="sfc-action">
                        <span class="btn-sfc">Buka Kamera <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>

                <div class="scanner-feature-card" onclick="document.getElementById('barcodeInput').focus()">
                    <div class="sfc-icon-wrapper hardware">
                        <i class="bi bi-upc-scan"></i>
                    </div>
                    <div class="sfc-content">
                        <h4>Scanner Barcode Standby</h4>
                        <p>Scanner barcode USB/Bluetooth otomatis terdeteksi instan. Tekan tombol <kbd class="scanner-kbd">F2</kbd> untuk fokus</p>
                    </div>
                    <div class="sfc-action">
                        <span class="badge-sfc-ready"><i class="bi bi-check-circle-fill"></i> Standby 0ms</span>
                    </div>
                </div>

                <div class="scanner-feature-card" onclick="document.getElementById('barcodeInput').focus()">
                    <div class="sfc-icon-wrapper database">
                        <i class="bi bi-layers-half"></i>
                    </div>
                    <div class="sfc-content">
                        <h4>Multi-Satuan &amp; Grosir</h4>
                        <p>Cek harga eceran, grosir bertingkat, stok real-time, dan kalkulasi profit modal secara presisi</p>
                    </div>
                    <div class="sfc-action">
                        <span class="badge-sfc-info"><i class="bi bi-shield-check"></i> Akurat</span>
                    </div>
                </div>
            </div>

            <div class="scanner-quick-search-box">
                <div class="sqs-title">
                    <i class="bi bi-stars text-warning"></i>
                    <span>Coba Pencarian Populer:</span>
                </div>
                <div class="sqs-chips">
                    <button type="button" class="sqs-chip" onclick="quickSearchProduct('Minyak')">Minyak</button>
                    <button type="button" class="sqs-chip" onclick="quickSearchProduct('Beras')">Beras</button>
                    <button type="button" class="sqs-chip" onclick="quickSearchProduct('Gula')">Gula</button>
                    <button type="button" class="sqs-chip" onclick="quickSearchProduct('Kecap')">Kecap</button>
                    <button type="button" class="sqs-chip" onclick="quickSearchProduct('Susu')">Susu</button>
                    <button type="button" class="sqs-chip" onclick="quickSearchProduct('Mie')">Mie</button>
                    <button type="button" class="sqs-chip" onclick="quickSearchProduct('Sabun')">Sabun</button>
                    <button type="button" class="sqs-chip" onclick="quickSearchProduct('Kopi')">Kopi</button>
                </div>
            </div>
        </div>
    `;
}

function quickSearchProduct(term) {
    const input = document.getElementById('barcodeInput');
    if (input) {
        input.value = term;
        toggleClearButton();
        lookupBarcode();
    }
}

function clearScannerSearch() {
    const input = document.getElementById('barcodeInput');
    if (input) {
        input.value = '';
        input.focus();
        toggleClearButton();
    }
    lastSearchKeyword = '';
    lastSearchResultsData = null;
    renderEmptyStateHub();
}

function toggleClearButton() {
    const input = document.getElementById('barcodeInput');
    const clearBtn = document.getElementById('scannerClearBtn');
    if (clearBtn && input) {
        clearBtn.style.display = input.value.trim().length > 0 ? 'inline-flex' : 'none';
    }
}

async function lookupBarcode() {
    clearTimeout(scannerTimer);
    clearTimeout(_scannerAutoLookupTimer);

    const input = document.getElementById('barcodeInput');
    const code = input?.value?.trim();
    toggleClearButton();
    if (!code) {
        renderEmptyStateHub();
        return;
    }

    lastSearchKeyword = code;
    _scannerJustSearched = true;
    _scannerFastCharCount = 0;

    const selectInp = () => {
        // DO NOT select/focus on mobile touch devices as it selects all text and steals virtual keyboard focus while user is typing
        if ('ontouchstart' in window || navigator.maxTouchPoints > 0) return;
        setTimeout(() => {
            if (input) {
                input.focus();
                input.select();
            }
        }, 50);
    };

    const resultDiv = document.getElementById('scanResult');
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';

    const isNumericBarcode = /^\d{6,16}$/.test(code);

    // 1. Check local IndexedDB for match (instant < 10ms)
    let offlineFound = null;
    if (typeof OfflineDB !== 'undefined') {
        try {
            if (isNumericBarcode) {
                const p = await OfflineDB.findByBarcode(code);
                if (p && p.id) offlineFound = p;
            } else {
                const localResults = await OfflineDB.searchProducts(code);
                if (localResults && localResults.length > 0) {
                    if (localResults.length === 1) {
                        showProductResultOffline(localResults[0]);
                    } else {
                        renderMultipleSearchResults(localResults, true);
                    }
                }
            }
        } catch (e) {}
    }

    // IF EXACT BARCODE MATCH IN LOCAL DB: Render INSTANTLY (0ms delay)
    if (offlineFound) {
        if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
        showProductResultOffline(offlineFound);
        selectInp();
        // Refresh authoritative product data from server in background
        syncProductByCodeBackground(code);
        return;
    }

    // 2. Fetch from server API
    resultDiv.innerHTML = '<div style="text-align:center;padding:40px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><div style="font-size:12px;color:var(--text-muted);margin-top:10px;">Mencari produk di server...</div></div>';

    if (isNumericBarcode) {
        // Try strict barcode lookup first
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);

            const res = await fetch(`${baseUrl}api/products/barcode/${encodeURIComponent(code)}`, { 
                credentials: 'same-origin',
                signal: controller.signal
            });
            clearTimeout(timeoutId);
            if (res.ok) {
                const data = await res.json();
                if (data && data.id) {
                    if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
                    showProductResult(data);
                    if (typeof OfflineDB !== 'undefined' && OfflineDB.saveProduct) {
                        OfflineDB.saveProduct(data).catch(() => {});
                    }
                    return;
                }
            }
        } catch (e) {}
    }

    // 3. Try online text search via API (for product name/brand/type)
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 6000);

        const res = await fetch(`${baseUrl}api/products/search?q=${encodeURIComponent(code)}`, { 
            credentials: 'same-origin',
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        if (res.ok) {
            const searchData = await res.json();
            if (Array.isArray(searchData) && searchData.length === 1) {
                if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
                fetchProductDetail(searchData[0].id);
                return;
            } else if (Array.isArray(searchData) && searchData.length > 0) {
                renderMultipleSearchResults(searchData, false);
                return;
            }
        }
    } catch (searchErr) {}

    // 4. Fallback to local IndexedDB text search if server is unreachable / offline
    if (typeof OfflineDB !== 'undefined') {
        try {
            const searchData = await OfflineDB.searchProducts(code);
            if (searchData && searchData.length > 0) {
                if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
                if (searchData.length === 1) {
                    showProductResultOffline(searchData[0]);
                } else {
                    renderMultipleSearchResults(searchData, true);
                }
                return;
            }
        } catch (e) {}
    }

    // 5. Not found state
    resultDiv.innerHTML = '<div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:40px;text-align:center;"><i class="bi bi-search fs-1 text-muted"></i><h4 style="font-size:15px;font-weight:700;margin-top:10px;">Produk Tidak Ditemukan</h4><p style="font-size:12px;color:var(--text-muted);">Tidak ada hasil untuk "'+escapeHtml(code)+'".</p></div>';
}

async function syncProductByCodeBackground(code) {
    try {
        const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 4000);

        const res = await fetch(`${baseUrl}api/products/barcode/${encodeURIComponent(code)}`, { 
            credentials: 'same-origin',
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        if (res.ok) {
            const data = await res.json();
            if (data && data.id) {
                showProductResult(data);
                if (typeof OfflineDB !== 'undefined' && OfflineDB.saveProduct) {
                    OfflineDB.saveProduct(data).catch(() => {});
                }
            }
        }
    } catch (e) {
        // Silently ignore timeout or network errors as local data is already rendered
    }
}

function selectSearchResultItem(idx) {
    if (!lastSearchResultsData || !lastSearchResultsData.products || !lastSearchResultsData.products[idx]) return;
    const p = lastSearchResultsData.products[idx];
    if (lastSearchResultsData.isOffline) {
        showProductResultOffline(p);
    } else {
        // Render immediately from in-memory product data (0ms instant display)
        showProductResult(p);
        // Enrich with detailed supplier/purchase history from server asynchronously
        fetchProductDetail(p.id, p);
    }
}

function renderMultipleSearchResults(products, isOffline) {
    lastSearchResultsData = { products, isOffline };
    const resultDiv = document.getElementById('scanResult');
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';

    let cardsHtml = products.map((p, idx) => {
        const prodName = p.name || p.full_name || 'Tanpa Nama';
        const brand = p.brand_name || '';
        const category = p.category_name || '';
        
        return `
            <div class="scan-result-card" onclick="selectSearchResultItem(${idx})">
                <!-- Top E-Commerce Showcase Stage (Large Image) -->
                <div class="scan-card-image-stage">
                    ${p.photo 
                        ? `<img src="${baseUrl}${p.photo}" alt="${prodName}">`
                        : `<div class="scan-card-no-image"><i class="bi bi-box-seam"></i></div>`
                    }
                </div>

                <!-- Product Details -->
                <div style="flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div class="scan-card-title">${prodName}</div>
                        <div style="display:flex; flex-wrap:wrap; gap:4px; margin-bottom:6px;">
                            ${brand ? `<span class="scan-pill-badge brand"><i class="bi bi-award me-1"></i>${brand}</span>` : ''}
                            ${category ? `<span class="scan-pill-badge category"><i class="bi bi-tag me-1"></i>${category}</span>` : ''}
                        </div>
                        ${p.short_label ? `<div class="scan-short-label" style="margin-bottom:6px;">Label: ${p.short_label}</div>` : ''}

                        <!-- Packaging List -->
                        ${renderPackagingsForList(p.packagings)}
                    </div>

                    <!-- Footer Action -->
                    <div style="margin-top:14px; padding-top:10px; border-top:1px solid var(--border-color);">
                        <button class="btn-primary-custom w-100" style="padding:8px 14px; font-size:12px; font-weight:700; border-radius:8px;">
                            <i class="bi bi-eye-fill me-1"></i> Detail &amp; Cek Lengkap
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    resultDiv.innerHTML = `<div class="scan-results-grid">${cardsHtml}</div>`;
}

function goBackToSearchResults() {
    const input = document.getElementById('barcodeInput');
    if (lastSearchKeyword) {
        input.value = lastSearchKeyword;
    }
    toggleClearButton();
    
    if (lastSearchResultsData && lastSearchResultsData.products && lastSearchResultsData.products.length > 1) {
        renderMultipleSearchResults(lastSearchResultsData.products, lastSearchResultsData.isOffline);
    } else if (lastSearchKeyword) {
        lookupBarcode();
    } else {
        renderEmptyStateHub();
    }
    input.focus();
}

function renderPackagingsForList(packagings) {
    if (!packagings || packagings.length === 0) return '';
    return '<div style="margin-top:6px; background:var(--surface-2); border-radius:var(--radius-md); padding:10px; border:1px solid var(--border-color);">' +
        packagings.map(pkg => {
            const jenis = pkg.unit_name || ('Level ' + pkg.level);
            const qty = pkg.base_qty || 1;
            const ecer = parseFloat(pkg.sell_price_retail) || 0;
            const grosir = parseFloat(pkg.sell_price_wholesale) || 0;
            const modal = parseFloat(pkg.buy_price) || 0;
            const marginAmt = ecer - modal;
            const marginPct = modal > 0 ? ((marginAmt / modal) * 100).toFixed(1) : 0;
            
            return `
                <div style="margin-bottom:6px; padding-bottom:6px; border-bottom:1px dashed var(--border-color);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
                        <div style="font-weight:700; font-size:11px; color:var(--text-primary);">${jenis} <span style="font-weight:400; color:var(--text-muted); font-size:9px;">(Isi ${qty})</span></div>
                        ${SCANNER_IS_SUPERADMIN && modal > 0 ? `<div style="font-size:9px; color:var(--text-muted); font-weight:600;">M: ${formatRupiah(modal)} | Profit ${marginPct}%</div>` : ''}
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px;">
                        <div>${ecer > 0 ? `<span style="color:var(--text-muted); font-size:9px;">Ecer:</span> <strong style="color:var(--success);">${formatRupiah(ecer)}</strong>` : ''}</div>
                        <div>${grosir > 0 ? `<span style="color:var(--text-muted); font-size:9px;">Grosir:</span> <strong style="color:var(--warning);">${formatRupiah(grosir)}</strong>` : ''}</div>
                    </div>
                </div>
            `;
        }).join('') +
    '</div>';
}

async function fetchProductDetail(id, fallbackObj = null) {
    const resultDiv = document.getElementById('scanResult');
    if (!fallbackObj) {
        resultDiv.innerHTML = '<div style="text-align:center;padding:40px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        if (typeof OfflineDB !== 'undefined') {
            try {
                const localP = await OfflineDB.findByBarcode(id);
                if (localP) showProductResultOffline(localP);
            } catch(e) {}
        }
    }
    
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
    try {
        let data = null;
        if (typeof api === 'function') {
            data = await api(`${baseUrl}api/products/${id}`);
        } else {
            const res = await fetch(`${baseUrl}api/products/${id}`, { credentials: 'same-origin' });
            if (res.ok) data = await res.json();
        }

        if (data && data.id) {
            showProductResult(data);
        }
    } catch (e) {
        console.error("fetchProductDetail error:", e);
        if (!fallbackObj) {
            const spinner = resultDiv.querySelector('.spinner-border');
            if (spinner) {
                resultDiv.innerHTML = '<div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:40px;text-align:center;"><i class="bi bi-exclamation-triangle fs-1 text-danger"></i><h4 style="font-size:15px;font-weight:700;margin-top:10px;">Error</h4><p style="font-size:12px;color:var(--text-muted);">Gagal mengambil detail produk.</p></div>';
            }
        }
    }
}

async function enrichProductSuppliersOffline(product) {
    if (!product || !product.id) return product;
    if (product.suppliers && Array.isArray(product.suppliers) && product.suppliers.length > 0 && product.last_purchase) {
        return product; // Already has full supplier data from server
    }

    try {
        if (typeof db === 'undefined' || !db.purchases) return product;

        const allPurchases = await db.purchases.toArray();
        const allSuppliers = typeof db.suppliers !== 'undefined' ? await db.suppliers.toArray() : [];
        const supplierMap = {};
        allSuppliers.forEach(s => { supplierMap[s.id] = s; });

        const matchingPurchases = [];
        allPurchases.forEach(pur => {
            const items = pur.items || pur.purchase_items || [];
            if (Array.isArray(items)) {
                items.forEach(item => {
                    if (item.product_id == product.id || item.id == product.id) {
                        matchingPurchases.push({ purchase: pur, item: item });
                    }
                });
            }
        });

        if (matchingPurchases.length > 0) {
            matchingPurchases.sort((a, b) => new Date(b.purchase.date || b.purchase.purchase_date || 0) - new Date(a.purchase.date || a.purchase.purchase_date || 0));

            const latest = matchingPurchases[0];
            const supName = latest.purchase.supplier_name || (supplierMap[latest.purchase.supplier_id] ? supplierMap[latest.purchase.supplier_id].name : 'Pemasok');
            product.last_purchase = {
                supplier_name: supName,
                purchase_date: latest.purchase.date || latest.purchase.purchase_date,
                purchase_code: latest.purchase.invoice_number || latest.purchase.purchase_code || '-',
                buy_price: latest.item.buy_price || latest.item.item_buy_price || 0,
                unit_name: latest.item.unit_name || 'Pcs'
            };

            const supGroupMap = {};
            matchingPurchases.forEach(mp => {
                const sId = mp.purchase.supplier_id || 0;
                const sName = mp.purchase.supplier_name || (supplierMap[sId] ? supplierMap[sId].name : 'Pemasok Umum');
                if (!supGroupMap[sId]) {
                    supGroupMap[sId] = {
                        supplier_id: sId,
                        supplier_name: sName,
                        address: supplierMap[sId] ? supplierMap[sId].address || '' : '',
                        last_buy_price: mp.item.buy_price || mp.item.item_buy_price || 0,
                        last_purchase_date: mp.purchase.date || mp.purchase.purchase_date,
                        purchase_count: 0,
                        purchases: []
                    };
                }
                supGroupMap[sId].purchase_count++;
                supGroupMap[sId].purchases.push({
                    purchase_id: mp.purchase.id,
                    purchase_code: mp.purchase.invoice_number || mp.purchase.purchase_code || '-',
                    purchase_date: mp.purchase.date || mp.purchase.purchase_date,
                    quantity: mp.item.quantity || 1,
                    item_buy_price: mp.item.buy_price || mp.item.item_buy_price || 0,
                    item_subtotal: (mp.item.buy_price || mp.item.item_buy_price || 0) * (mp.item.quantity || 1),
                    unit_name: mp.item.unit_name || 'Pcs'
                });
            });

            product.suppliers = Object.values(supGroupMap);
        }
    } catch(e) {
        console.warn("enrichProductSuppliersOffline error:", e);
    }

    return product;
}

async function showProductResultOffline(data) {
    if (typeof enrichProductSuppliersOffline === 'function') {
        data = await enrichProductSuppliersOffline(data);
    }
    renderProductScanResult(data, true);
}

async function showProductResult(data) {
    if ((!data.suppliers || data.suppliers.length === 0) && typeof enrichProductSuppliersOffline === 'function') {
        data = await enrichProductSuppliersOffline(data);
    }
    renderProductScanResult(data, false);
}

function toggleProductSupplierHistory(idx) {
    const row = document.getElementById(`prod-sup-history-${idx}`);
    const icon = document.getElementById(`prod-sup-chevron-${idx}`);
    if (!row) return;

    if (row.style.display === 'none' || !row.style.display) {
        row.style.display = 'block';
        if (icon) {
            icon.classList.remove('bi-chevron-right');
            icon.classList.add('bi-chevron-down');
            icon.classList.add('text-primary');
        }
    } else {
        row.style.display = 'none';
        if (icon) {
            icon.classList.remove('bi-chevron-down');
            icon.classList.remove('text-primary');
            icon.classList.add('bi-chevron-right');
        }
    }
}

let currentSupplierList = [];
let currentSupplierPage = 1;
const SUPPLIER_PAGE_SIZE = 3;

function changeSupplierPage(page) {
    currentSupplierPage = page;
    const container = document.getElementById('scannerSupplierListContainer');
    if (container) {
        container.innerHTML = renderSupplierGroupedSectionHtml(currentSupplierList, currentSupplierPage);
    }
}

function renderSupplierGroupedSectionHtml(suppliers, page = 1) {
    if (!suppliers || suppliers.length === 0) {
        return `<div class="text-center py-4 text-muted" style="font-size:12px;"><i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i> Belum ada riwayat supplier terdaftar untuk produk ini.</div>`;
    }

    const totalSuppliers = suppliers.length;
    const totalPages = Math.ceil(totalSuppliers / SUPPLIER_PAGE_SIZE);
    const startIdx = (page - 1) * SUPPLIER_PAGE_SIZE;
    const paginatedSuppliers = suppliers.slice(startIdx, startIdx + SUPPLIER_PAGE_SIZE);

    let cardsHtml = paginatedSuppliers.map((sup, itemIdx) => {
        const globalIdx = startIdx + itemIdx;
        const purchases = sup.purchases || [];
        const lastDate = sup.last_purchase_date ? new Date(sup.last_purchase_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
        const lastBuyPrice = parseFloat(sup.last_buy_price) || 0;

        let purchasesTableHtml = '';
        if (purchases.length === 0) {
            purchasesTableHtml = `<div class="text-center py-3 text-muted" style="font-size:11px;">Belum ada riwayat faktur pembelian dari supplier ini</div>`;
        } else {
            let rows = purchases.map(p => {
                const pDate = new Date(p.purchase_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const itemBuy = parseFloat(p.item_buy_price) || 0;
                const subtotal = parseFloat(p.item_subtotal) || 0;
                const qty = parseFloat(p.quantity) || 0;

                return `
                    <tr style="border-bottom:1px solid var(--border-color); font-size:11px;">
                        <td style="padding:6px 8px; color:var(--text-muted); white-space:nowrap;">${pDate}</td>
                        <td style="padding:6px 8px; font-weight:700; color:var(--primary); font-family:monospace; white-space:nowrap;">${p.purchase_code || p.purchase_number || '-'}</td>
                        <td style="padding:6px 8px; white-space:nowrap;">${qty} ${p.unit_name || 'Pcs'}</td>
                        <td style="padding:6px 8px; text-align:right; font-weight:700; white-space:nowrap;">${formatRupiah(itemBuy)}</td>
                        <td style="padding:6px 8px; text-align:right; font-weight:700; color:var(--success); white-space:nowrap;">${formatRupiah(subtotal)}</td>
                    </tr>
                `;
            }).join('');

            purchasesTableHtml = `
                <div style="max-height: 220px; overflow-x: auto; overflow-y: auto; max-width: 100%; width: 100%; border: 1px solid var(--border-color); border-radius: var(--radius-sm); margin-top:8px; -webkit-overflow-scrolling: touch;">
                    <table style="width: 100%; min-width: 460px; font-size: 11px; border-collapse: collapse;">
                        <thead style="background: var(--surface-2); position: sticky; top: 0; z-index: 2;">
                            <tr style="border-bottom: 1px solid var(--border-color); text-transform: uppercase; font-size: 9px; color: var(--text-muted);">
                                <th style="padding:6px 8px; white-space:nowrap;">Tanggal</th>
                                <th style="padding:6px 8px; white-space:nowrap;">No. Invoice</th>
                                <th style="padding:6px 8px; white-space:nowrap;">Qty</th>
                                <th style="padding:6px 8px; text-align:right; white-space:nowrap;">Harga Beli</th>
                                <th style="padding:6px 8px; text-align:right; white-space:nowrap;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows}
                        </tbody>
                    </table>
                </div>
            `;
        }

        return `
            <div style="background:var(--surface-2); border:1px solid var(--border-color); border-radius:var(--radius-md); margin-bottom:8px; overflow:hidden;">
                <div onclick="toggleProductSupplierHistory(${globalIdx})" style="padding:10px 14px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;" onmouseover="this.style.background='var(--surface-1)'" onmouseout="this.style.background='transparent'">
                    <div class="d-flex align-items-center gap-2">
                        <i id="prod-sup-chevron-${globalIdx}" class="bi bi-chevron-right text-muted" style="font-size:11px; transition:transform 0.2s;"></i>
                        <div>
                            <div class="fw-bold" style="font-size:12px; color:var(--text-primary);">${sup.supplier_name}</div>
                            <div style="font-size:10px; color:var(--text-muted);">
                                ${sup.address ? `<i class="bi bi-geo-alt me-1"></i>${sup.address} &middot; ` : ''}
                                ${sup.purchase_count || purchases.length} Pembelian
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <div style="font-weight:700; color:var(--success); font-size:12px;">${lastBuyPrice > 0 ? formatRupiah(lastBuyPrice) : '-'}</div>
                        <div style="font-size:9px; color:var(--text-muted);">${lastDate !== '-' ? lastDate : ''}</div>
                    </div>
                </div>

                <!-- History Sub-panel -->
                <div id="prod-sup-history-${globalIdx}" style="display:none; padding:0 12px 12px; border-top:1px solid var(--border-color); background:var(--surface-1);">
                    <div class="d-flex align-items-center justify-content-between pt-2 mb-1">
                        <span class="fw-bold" style="font-size:10px; color:var(--primary);"><i class="bi bi-journal-text me-1"></i> Riwayat Faktur (${purchases.length})</span>
                        <button type="button" onclick="event.stopPropagation(); toggleProductSupplierHistory(${globalIdx})" class="btn btn-sm btn-outline-secondary" style="font-size:9px; padding:1px 6px;">
                            <i class="bi bi-chevron-up me-1"></i> Tutup
                        </button>
                    </div>
                    ${purchasesTableHtml}
                </div>
            </div>
        `;
    }).join('');

    // Pagination Controls
    let paginationHtml = '';
    if (totalPages > 1) {
        paginationHtml = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; padding-top:8px; border-top:1px dashed var(--border-color); font-size:11px;">
                <span style="color:var(--text-muted); font-size:10px;">Hal. <strong>${page}</strong> dari <strong>${totalPages}</strong></span>
                <div style="display:flex; gap:3px;">
                    <button type="button" onclick="changeSupplierPage(${page - 1})" class="btn btn-sm btn-outline-secondary" ${page === 1 ? 'disabled' : ''} style="font-size:10px; padding:1px 6px;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    ${Array.from({length: totalPages}, (_, i) => i + 1).map(p => `
                        <button type="button" onclick="changeSupplierPage(${p})" class="btn btn-sm ${p === page ? 'btn-primary' : 'btn-outline-secondary'}" style="font-size:10px; padding:1px 6px;">${p}</button>
                    `).join('')}
                    <button type="button" onclick="changeSupplierPage(${page + 1})" class="btn btn-sm btn-outline-secondary" ${page === totalPages ? 'disabled' : ''} style="font-size:10px; padding:1px 6px;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        `;
    }

    return cardsHtml + paginationHtml;
}


function renderProductScanResult(data, isOffline) {
    const resultDiv = document.getElementById('scanResult');
    const packagings = data.packagings || [];
    const prodName = data.name || data.full_name || 'Tanpa Nama';
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';

    currentSupplierList = data.suppliers || [];
    currentSupplierPage = 1;

    // Helper: format date nicely
    const fmtDate = (d) => { try { return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); } catch(e) { return d || '-'; } };

    // ── 1. Build Packaging Cards ──────────────────────────────────
    let packagingCardsHtml = packagings.map((p, pIdx) => {
        const modal = parseFloat(p.buy_price) || 0;
        const ecer = parseFloat(p.sell_price_retail) || 0;
        const grosir = parseFloat(p.sell_price_wholesale) || 0;
        const baseQty = parseFloat(p.base_qty) || 1;
        const markupRetailPct = (p.markup_retail_percent !== undefined) ? p.markup_retail_percent : (modal > 0 ? (((ecer - modal) / modal) * 100).toFixed(1) : 0);
        const profitRetailNominal = (p.profit_retail_nominal !== undefined) ? p.profit_retail_nominal : (ecer - modal);
        const markupWholesalePct = (p.markup_wholesale_percent !== undefined) ? p.markup_wholesale_percent : ((modal > 0 && grosir > 0) ? (((grosir - modal) / modal) * 100).toFixed(1) : 0);
        const profitWholesaleNominal = (p.profit_wholesale_nominal !== undefined) ? p.profit_wholesale_nominal : (grosir > 0 ? (grosir - modal) : 0);

        // Tier pricing
        let tierHtml = '';
        if (p.qty_prices && p.qty_prices.length > 0) {
            tierHtml = `
                <div style="margin-top:10px; padding:8px 10px; background:var(--surface-1); border-radius:8px; border:1px solid var(--border-color);">
                    <div style="font-size:10px; font-weight:700; color:var(--info); margin-bottom:4px; display:flex; align-items:center; gap:4px;">
                        <i class="bi bi-layers"></i> Harga Grosir Bertingkat
                    </div>
                    ${p.qty_prices.map(t => {
                        const tPrice = parseFloat(t.unit_price) || 0;
                        const tMarkup = modal > 0 ? (((tPrice - modal) / modal) * 100).toFixed(1) : 0;
                        const mode = t.sale_mode || 'both';
                        const modeIcon = mode === 'retail' ? 'bi-person' : (mode === 'wholesale' ? 'bi-people' : 'bi-arrow-left-right');
                        return `<div style="display:flex; justify-content:space-between; align-items:center; font-size:10px; padding:3px 0; border-bottom:1px dotted var(--border-color);">
                            <span><i class="bi ${modeIcon} me-1 text-muted" style="font-size:9px;"></i>Min. <strong>${t.min_qty}</strong> ${p.unit_name || 'pcs'}</span>
                            <span style="font-weight:700; color:var(--text-primary);">${formatRupiah(tPrice)} <span style="font-weight:600; color:var(--success); font-size:9px;">(+${tMarkup}%)</span></span>
                        </div>`;
                    }).join('')}
                </div>`;
        }

        return `
        <div class="scanner-pkg-card" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:12px; padding:14px; margin-bottom:12px; transition:all 0.2s ease;">
            <!-- Packaging Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:6px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:linear-gradient(135deg, var(--primary), #6366f1); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:13px;">${pIdx + 1}</div>
                    <div>
                        <div style="font-weight:800; font-size:14px; color:var(--text-primary); line-height:1.2;">${p.unit_name || 'Level ' + p.level}</div>
                        <div style="font-size:10px; color:var(--text-muted); display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-top:2px;">
                            <span><i class="bi bi-box me-1"></i>Isi ${baseQty} pcs</span>
                            ${p.barcode ? `<span style="font-family:'Courier New',monospace; background:var(--surface-2); padding:1px 6px; border-radius:4px; border:1px solid var(--border-color);"><i class="bi bi-upc me-1"></i>${p.barcode}</span>` : ''}
                        </div>
                    </div>
                </div>
                ${SCANNER_IS_SUPERADMIN && modal > 0 ? `<div style="text-align:right;"><div style="font-size:9px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.3px;">Modal</div><div class="scanner-modal-text" style="font-weight:700; font-size:13px; color:var(--text-muted); opacity:0.85;">${formatRupiah(modal)}</div></div>` : ''}
            </div>

            <!-- Price Grid: Ecer + Grosir -->
            <div class="scanner-price-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:4px;">
                <!-- Ecer Card -->
                <div class="scanner-price-box" style="background:linear-gradient(135deg, rgba(16,185,129,0.08), rgba(16,185,129,0.02)); padding:10px 12px; border-radius:10px; border:1px solid rgba(16,185,129,0.18);">
                    <div style="font-size:9px; font-weight:700; color:var(--success); text-transform:uppercase; letter-spacing:0.3px; margin-bottom:3px;">
                        <i class="bi bi-person me-1"></i>Harga Ecer
                    </div>
                    <div class="scanner-price-val" style="font-weight:800; color:var(--success); font-size:17px; line-height:1.2;">${ecer > 0 ? formatRupiah(ecer) : '-'}</div>
                    ${SCANNER_IS_SUPERADMIN && modal > 0 && ecer > 0 ? `
                        <div style="margin-top:4px; padding-top:4px; border-top:1px dashed rgba(16,185,129,0.2);">
                            <div style="display:flex; justify-content:space-between; font-size:9px;">
                                <span style="color:var(--text-muted);">Markup</span>
                                <span style="font-weight:700; color:var(--success);">+${markupRetailPct}%</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:9px; margin-top:1px;">
                                <span style="color:var(--text-muted);">Profit/pcs</span>
                                <span style="font-weight:700; color:var(--success);">+${formatRupiah(profitRetailNominal)}</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
                <!-- Grosir Card -->
                <div class="scanner-price-box" style="background:linear-gradient(135deg, rgba(245,158,11,0.08), rgba(245,158,11,0.02)); padding:10px 12px; border-radius:10px; border:1px solid rgba(245,158,11,0.18);">
                    <div style="font-size:9px; font-weight:700; color:var(--warning); text-transform:uppercase; letter-spacing:0.3px; margin-bottom:3px;">
                        <i class="bi bi-people me-1"></i>Harga Grosir
                    </div>
                    <div class="scanner-price-val" style="font-weight:800; color:var(--warning); font-size:17px; line-height:1.2;">${grosir > 0 ? formatRupiah(grosir) : '-'}</div>
                    ${SCANNER_IS_SUPERADMIN && modal > 0 && grosir > 0 ? `
                        <div style="margin-top:4px; padding-top:4px; border-top:1px dashed rgba(245,158,11,0.2);">
                            <div style="display:flex; justify-content:space-between; font-size:9px;">
                                <span style="color:var(--text-muted);">Markup</span>
                                <span style="font-weight:700; color:var(--warning);">+${markupWholesalePct}%</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:9px; margin-top:1px;">
                                <span style="color:var(--text-muted);">Profit/pcs</span>
                                <span style="font-weight:700; color:var(--warning);">+${formatRupiah(profitWholesaleNominal)}</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
            ${tierHtml}
        </div>`;
    }).join('');

    // ── 2. Last Purchase Insight ──────────────────────────────────
    let lastPurchaseInfoHtml = '';
    if (data.last_purchase) {
        const lp = data.last_purchase;
        lastPurchaseInfoHtml = `
            <div style="background:linear-gradient(135deg, rgba(59,130,246,0.1), rgba(59,130,246,0.03)); border:1px solid rgba(59,130,246,0.2); border-radius:10px; padding:12px 14px;">
                <div style="font-size:10px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-clock-history"></i> Pembelian Terakhir
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <div>
                        <div style="font-weight:700; font-size:12px; color:var(--text-primary);">${lp.supplier_name || '-'}</div>
                        <div style="font-size:10px; color:var(--text-muted);">${fmtDate(lp.purchase_date)} &middot; Invoice: ${lp.purchase_code || '-'}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:800; color:var(--success); font-size:14px;">${formatRupiah(lp.buy_price)}</div>
                        ${lp.unit_name ? `<div style="font-size:9px; color:var(--text-muted);">per ${lp.unit_name}</div>` : ''}
                    </div>
                </div>
            </div>`;
    }

    // ── 3. Product Info Metadata Grid ─────────────────────────────
    const productType = data.product_type || '';
    const variant = data.variant || '';
    const weightVal = data.weight_value ? `${parseFloat(data.weight_value)}${data.weight_unit || ''}` : '';
    const invoiceName = data.invoice_name || data.supplier_invoice_name || '';
    const productCode = data.code || data.supplier_product_code || '';
    const createdAt = data.created_at ? fmtDate(data.created_at) : '-';
    const updatedAt = data.updated_at ? fmtDate(data.updated_at) : '-';
    const isAvailable = data.is_available !== undefined ? (data.is_available == 1 ? '<span style="color:var(--success); font-weight:700;">✓ Tersedia</span>' : '<span style="color:var(--danger); font-weight:700;">✗ Tidak Tersedia</span>') : '';
    const suppliersCount = data.suppliers ? data.suppliers.length : 0;

    const rawStock = parseFloat(data.current_qty_base ?? 0);
    const stockDisplay = (rawStock % 1 === 0) ? parseInt(rawStock) : parseFloat(rawStock.toFixed(2));

    let metaRows = [];
    metaRows.push(['Stok Tersedia', `<strong style="color:var(--primary); font-size:13px; font-weight:800;">${stockDisplay}</strong> Base Pcs`]);
    if (productType) metaRows.push(['Jenis Produk', productType]);
    if (variant) metaRows.push(['Varian', variant]);
    if (weightVal) metaRows.push(['Berat / Volume', weightVal]);
    if (productCode) metaRows.push(['Kode Produk', `<code style="background:var(--surface-2); padding:1px 6px; border-radius:4px; font-family:monospace; font-size:11px;">${productCode}</code>`]);
    if (invoiceName) metaRows.push(['Nama Invoice', invoiceName]);
    if (isAvailable) metaRows.push(['Status Ketersediaan', isAvailable]);
    metaRows.push(['Jumlah Supplier', `<strong>${suppliersCount}</strong> Pemasok Terdaftar`]);
    metaRows.push(['Jumlah Kemasan', `<strong>${packagings.length}</strong> Level Kemasan`]);
    metaRows.push(['Terakhir Diperbarui', updatedAt]);
    metaRows.push(['Tanggal Didaftarkan', createdAt]);

    let productMetaHtml = metaRows.map(([label, val]) => `
        <div style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid var(--border-color); font-size:11px;">
            <span style="color:var(--text-muted); font-weight:600;"><i class="bi bi-dot text-primary me-1"></i>${label}</span>
            <span style="color:var(--text-primary); font-weight:600; text-align:right;">${val}</span>
        </div>
    `).join('');

    // ── 4. Back Button ────────────────────────────────────────────
    let backButtonHtml = '';
    if (lastSearchResultsData && lastSearchResultsData.products && lastSearchResultsData.products.length > 1) {
        backButtonHtml = `
            <div class="scanner-back-btn-wrapper" style="margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:10px; position:relative; z-index:10; width:100%;">
                <button type="button" onclick="goBackToSearchResults()" class="btn btn-outline-primary scanner-back-btn" style="font-size:12px; font-weight:700; padding:7px 14px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; flex-shrink:0;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Hasil
                </button>
                ${lastSearchKeyword ? `<span class="scanner-back-keyword" style="font-size:12px; color:var(--text-muted); text-align:right; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:55%;">Pencarian: <strong style="color:var(--text-primary);">"${lastSearchKeyword}"</strong></span>` : ''}
            </div>`;
    } else if (lastSearchKeyword) {
        backButtonHtml = `
            <div class="scanner-back-btn-wrapper" style="margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:10px; position:relative; z-index:10; width:100%;">
                <button type="button" onclick="goBackToSearchResults()" class="btn btn-outline-primary scanner-back-btn" style="font-size:12px; font-weight:700; padding:7px 14px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; flex-shrink:0;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Pencarian
                </button>
                <span class="scanner-back-keyword" style="font-size:12px; color:var(--text-muted); text-align:right; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:55%;">Pencarian: <strong style="color:var(--text-primary);">"${lastSearchKeyword}"</strong></span>
            </div>`;
    }

    // ── 5. Assemble Full Layout (Desktop & Mobile Unified Structure) ──
    resultDiv.innerHTML = `
        ${backButtonHtml}

        <div class="row g-4 mb-4 scan-mobile-flex-wrapper">
            <!-- ── LEFT COLUMN (DESKTOP) ── -->
            <div class="col-lg-5 col-md-12 scan-mobile-flex-col">
                <!-- 1. Photo Showcase (Mobile Order 1) -->
                <div class="scanner-detail-photo-card scan-m-order-1" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:16px; overflow:hidden; box-shadow:0 8px 32px rgba(0,0,0,0.08); margin-bottom:16px;">
                    <!-- Photo Stage -->
                    <div class="scanner-detail-photo-stage" style="width:100%; aspect-ratio:1/1; max-height:460px; background:linear-gradient(180deg, var(--surface-2) 0%, var(--surface-1) 100%); display:flex; align-items:center; justify-content:center; padding:20px; cursor:pointer;" onclick="${data.photo ? `viewFullPhoto('${baseUrl}${data.photo}')` : ''}">
                        ${data.photo
                            ? `<img id="mainProductImg" src="${baseUrl}${data.photo}" style="width:100%; height:100%; object-fit:contain; transition:transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">`
                            : `<div style="text-align:center;"><i class="bi bi-box-seam" style="font-size:4.5rem; color:var(--primary); opacity:0.4;"></i><div style="font-size:11px; color:var(--text-muted); margin-top:6px;">Belum ada foto produk</div></div>`
                        }
                    </div>
                    <!-- Download & Expand Buttons -->
                    <div style="padding:10px 14px; border-top:1px solid var(--border-color); display:flex; gap:8px;">
                        ${data.photo ? `
                            <a href="${baseUrl}${data.photo}" download="${prodName.replace(/[^a-zA-Z0-9]/g, '_')}.jpg" target="_blank" class="btn btn-outline-primary flex-fill" style="font-size:11px; font-weight:700; border-radius:8px; padding:6px 10px;">
                                <i class="bi bi-download me-1"></i> Download Foto
                            </a>
                            <button type="button" onclick="viewFullPhoto('${baseUrl}${data.photo}')" class="btn btn-primary" style="font-size:11px; font-weight:700; border-radius:8px; padding:6px 12px;" title="Perbesar Foto">
                                <i class="bi bi-arrows-fullscreen"></i>
                            </button>
                        ` : `<div class="text-muted w-100 text-center py-1" style="font-size:11px;">Foto belum tersedia</div>`}
                    </div>
                </div>

                <!-- 4. Informasi Produk (Mobile Order 4, Desktop Bottom Left) -->
                <div class="scanner-detail-info-card scan-m-order-4" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:16px; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                    <div style="font-weight:800; font-size:13px; color:var(--text-primary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                        <div style="width:26px; height:26px; border-radius:6px; background:linear-gradient(135deg, var(--primary), #6366f1); display:flex; align-items:center; justify-content:center;"><i class="bi bi-info-circle text-white" style="font-size:13px;"></i></div>
                        Informasi Produk
                    </div>
                    <div style="background:var(--surface-2); border-radius:10px; padding:12px 14px; border:1px solid var(--border-color);">
                        ${productMetaHtml}
                    </div>
                </div>
            </div>

            <!-- ── RIGHT COLUMN (DESKTOP) ── -->
            <div class="col-lg-7 col-md-12 scan-mobile-flex-col">
                <!-- 2. Product Title & Badges Header (Mobile Order 2) -->
                <div class="scanner-detail-header-card scan-m-order-2" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:16px; padding:20px 24px; box-shadow:0 4px 20px rgba(0,0,0,0.06); margin-bottom:16px;">
                    <h2 class="scanner-detail-prod-title" style="font-size:1.4rem; font-weight:800; color:var(--text-primary); margin-bottom:10px; line-height:1.3; letter-spacing:-0.3px;">
                        ${prodName}
                        ${isOffline ? `<span class="badge bg-warning text-dark" style="font-size:10px; vertical-align:middle; margin-left:6px;">OFFLINE</span>` : ''}
                    </h2>
                    <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                        <span class="scan-pill-badge brand" style="font-size:11px; padding:4px 12px;"><i class="bi bi-award me-1"></i>${data.brand_name || 'Tanpa Brand'}</span>
                        <span class="scan-pill-badge category" style="font-size:11px; padding:4px 12px;"><i class="bi bi-tag me-1"></i>${data.category_name || 'Tanpa Kategori'}</span>
                        ${data.short_label ? `<span class="scan-short-label" style="font-size:11px; padding:4px 12px;"><i class="bi bi-receipt me-1"></i>${data.short_label}</span>` : ''}
                        <span class="scan-pill-badge" style="font-size:11px; padding:4px 12px; background:rgba(59,130,246,0.1); color:var(--primary); font-weight:700; border:1px solid rgba(59,130,246,0.2);"><i class="bi bi-box-seam me-1"></i>Stok: ${stockDisplay} Base Pcs</span>
                    </div>
                </div>

                <!-- 3. Kemasan & Harga Cards (Mobile Order 3) -->
                <div class="scanner-detail-pkg-card scan-m-order-3" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:16px; padding:20px 24px; box-shadow:0 4px 20px rgba(0,0,0,0.06); margin-bottom:16px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
                        <div style="font-weight:800; font-size:14px; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <div style="width:28px; height:28px; border-radius:8px; background:linear-gradient(135deg, var(--primary), #6366f1); display:flex; align-items:center; justify-content:center;"><i class="bi bi-layers-half text-white" style="font-size:14px;"></i></div>
                            Kemasan &amp; Harga
                        </div>
                        <span style="font-size:11px; color:var(--text-muted); font-weight:600;">${packagings.length} Level Kemasan</span>
                    </div>
                    ${packagingCardsHtml || '<div style="color:var(--text-muted); font-size:12px; text-align:center; padding:20px 0;"><i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i>Belum ada kemasan terdaftar</div>'}
                </div>

                <!-- 5. Pembelian Terakhir Insight (Mobile Order 5) -->
                ${lastPurchaseInfoHtml ? `
                    <div class="scanner-detail-lastpurchase-card scan-m-order-5" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:16px; padding:16px 20px; box-shadow:0 4px 20px rgba(0,0,0,0.06); margin-bottom:16px;">
                        ${lastPurchaseInfoHtml}
                    </div>
                ` : ''}

                <!-- 6. Pemasok & Riwayat Pembelian (Mobile Order 6) -->
                <div class="scanner-detail-supplier-card scan-m-order-6" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:16px; padding:20px 24px; box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="width:28px; height:28px; border-radius:8px; background:linear-gradient(135deg, #f59e0b, #d97706); display:flex; align-items:center; justify-content:center;"><i class="bi bi-truck text-white" style="font-size:13px;"></i></div>
                            <div style="font-weight:800; font-size:14px; color:var(--text-primary);">Pemasok &amp; Riwayat Pembelian</div>
                        </div>
                        <span style="font-size:11px; color:var(--text-muted); font-weight:600;">${suppliersCount} Pemasok</span>
                    </div>
                    <div id="scannerSupplierListContainer">
                        ${renderSupplierGroupedSectionHtml(data.suppliers, 1)}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function openGlobalScanner() {
    const input = document.getElementById('barcodeInput');
    if (typeof BarcodeUtil !== 'undefined') {
        BarcodeUtil.scanBarcode(input, (code) => {
            if (code && input) {
                input.value = code;
            }
            lookupBarcode();
        });
    } else {
        if (typeof showToast === 'function') showToast('Scanner module tidak tersedia', 'error');
    }
}

let scannerTimer = null;
const barcodeInpEl = document.getElementById('barcodeInput');

if (barcodeInpEl) {
    barcodeInpEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(scannerTimer);
            lookupBarcode();
        }
    });

    barcodeInpEl.addEventListener('input', (e) => {
        const val = barcodeInpEl.value.trim();
        toggleClearButton();
        clearTimeout(scannerTimer);

        if (val.length === 0) {
            renderEmptyStateHub();
            return;
        }

        if (val.length < 2) {
            return;
        }

        // Live responsive search debounce (300ms)
        scannerTimer = setTimeout(() => {
            lookupBarcode();
        }, 300);
    });

    // Auto-focus only on desktop (prevent opening mobile virtual keyboard automatically on page load)
    if (!('ontouchstart' in window || navigator.maxTouchPoints > 0)) {
        barcodeInpEl.focus();
    }
}

// Global F2 shortcut for instant hardware scanner or cashier focus
window.addEventListener('keydown', (e) => {
    if (e.key === 'F2') {
        e.preventDefault();
        const input = document.getElementById('barcodeInput');
        if (input) {
            input.focus();
            input.select();
        }
    }
});

// Initial Page Load: Render Rich Empty State Hub if no initial query
document.addEventListener('DOMContentLoaded', () => {
    if (barcodeInpEl && barcodeInpEl.value.trim().length > 0) {
        toggleClearButton();
        lookupBarcode();
    } else {
        renderEmptyStateHub();
    }
});
// Fallback if DOMContentLoaded has already fired
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    if (barcodeInpEl && barcodeInpEl.value.trim().length === 0) {
        renderEmptyStateHub();
    }
}
</script>
