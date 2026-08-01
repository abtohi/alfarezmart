<!-- Barcode Scanner View - Rich Product Detail with E-Commerce Split Showcase & Supplier History (v1.2.0) -->
<?php
$scannerUserLevel = $_SESSION['user_level'] ?? '';
$scannerIsSuperadmin = $scannerUserLevel === 'superadmin';
?>
<div class="page-section" style="padding-bottom:100px;">
    <!-- Modern Header -->
    <div class="scanner-header-card" style="background: linear-gradient(135deg, var(--surface-1), var(--surface-2)); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 22px 20px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <div style="margin-bottom: 14px;">
            <h2 class="scanner-page-title" style="font-size: 1.4rem; font-weight: 800; margin-bottom: 4px; color: var(--text-primary); letter-spacing: -0.3px; display:flex; align-items:center; gap:8px;">
                <i class="bi bi-upc-scan text-primary"></i> Cek Harga &amp; Scan Barcode
            </h2>
            <p class="scanner-page-subtitle" style="color: var(--text-muted); font-size: 12px; margin: 0;">Scan barcode produk atau ketik nama/kode barcode untuk cek harga &amp; stok real-time</p>
        </div>

        <!-- Manual Input Bar with Camera Trigger Icon -->
        <div style="position: relative;">
            <div class="scanner-search-bar" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 4px 6px 4px 14px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <i class="bi bi-upc-scan" onclick="openGlobalScanner()" style="color: var(--primary); font-size: 1.3rem; cursor: pointer;" title="Klik ikon barcode ini untuk Buka Kamera Scanner"></i>
                <input type="text" id="barcodeInput" placeholder="Ketik nama produk atau scan kode barcode..." 
                       style="flex:1; border:none; background:transparent; padding:10px 0; color:var(--text-primary); font-size:13px; outline:none; font-family: var(--font-family);" 
                       autocomplete="off" autofocus>
                <button onclick="lookupBarcode()" class="scanner-search-btn" style="border:none; background:var(--primary); color:white; padding:9px 22px; border-radius: 8px; font-weight:700; font-size: 12px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </div>
    </div>

    <!-- Desktop Hardware Scanner Hint -->
    <div class="desktop-scanner-hint" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:10px 14px; margin-bottom:20px; font-size:12px; color:var(--text-muted); display:flex; align-items:center; gap:8px;">
        <i class="bi bi-bluetooth text-info" style="font-size:1.1rem;"></i>
        <span>Hubungkan scanner barcode USB/Bluetooth — barcode otomatis terdeteksi. Tekan <kbd style="background:var(--surface-2);padding:2px 6px;border-radius:4px;font-size:0.75rem;border:1px solid var(--border-color);color:var(--text-primary);">F2</kbd> untuk fokus ke pencarian.</span>
    </div>

    <!-- Result Area (3 Split Columns Grid on Desktop or Rich Split Detail View) -->
    <div id="scanResult"></div>
</div>

<style>
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
    border-radius: var(--radius-lg);
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
    box-shadow: 0 12px 32px rgba(0,0,0,0.2) !important;
}

/* Large E-Commerce Showcase Stage */
.scan-card-image-stage {
    width: 100%;
    height: 180px;
    border-radius: var(--radius-md);
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
    opacity: 0.65;
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
    font-size: 9px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
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

/* Mobile Responsive Detail View Styling (Max-Width 991.98px) */
@media (max-width: 991.98px) {
    /* Page Header Card Mobile */
    .scanner-header-card {
        padding: 14px 16px !important;
        margin-bottom: 14px !important;
    }
    .scanner-page-title {
        font-size: 1.05rem !important;
        margin-bottom: 2px !important;
        letter-spacing: -0.2px !important;
    }
    .scanner-page-subtitle {
        font-size: 10px !important;
        line-height: 1.35 !important;
    }
    .scanner-search-bar {
        padding: 3px 4px 3px 10px !important;
        gap: 6px !important;
    }
    .scanner-search-btn {
        padding: 7px 14px !important;
        font-size: 11px !important;
    }

    /* Back Button Bar Mobile & Desktop Side-by-Side */
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
        padding: 6px 10px !important;
        border-radius: 8px !important;
    }
    .scanner-back-keyword {
        font-size: 11px !important;
        text-align: right !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 52% !important;
        color: var(--text-muted) !important;
        display: inline-block !important;
    }

    /* Mobile Flex Wrapper - Align Left & Right Borders 100% with Header Card */
    .scan-mobile-flex-wrapper {
        display: flex !important;
        flex-direction: column !important;
        gap: 14px !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .scan-mobile-flex-col {
        display: contents !important;
    }
    .scan-m-order-1 { order: 1 !important; margin-bottom: 0 !important; margin-top: 0 !important; }
    .scan-m-order-2 { order: 2 !important; margin-bottom: 0 !important; margin-top: 0 !important; }
    .scan-m-order-3 { order: 3 !important; margin-bottom: 0 !important; margin-top: 0 !important; }
    .scan-m-order-4 { order: 4 !important; margin-bottom: 0 !important; margin-top: 0 !important; }
    .scan-m-order-5 { order: 5 !important; margin-bottom: 0 !important; margin-top: 0 !important; }
    .scan-m-order-6 { order: 6 !important; margin-bottom: 0 !important; margin-top: 0 !important; }

    /* Photo Stage Mobile */
    .scanner-detail-photo-stage {
        max-height: 240px !important;
        padding: 12px !important;
    }
    .scanner-detail-photo-stage img {
        max-height: 210px !important;
    }
    .scanner-detail-photo-card {
        margin-bottom: 0 !important;
    }

    /* Product Title & Header Mobile */
    .scanner-detail-header-card {
        padding: 16px !important;
        margin-bottom: 0 !important;
    }
    .scanner-detail-prod-title {
        font-size: 1.15rem !important;
        margin-bottom: 8px !important;
    }

    /* Packaging & Pricing Cards Mobile */
    .scanner-detail-pkg-card {
        padding: 16px !important;
        margin-bottom: 0 !important;
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
        color: var(--text-muted) !important;
        opacity: 0.8 !important;
    }

    /* Info & History Cards Mobile */
    .scanner-detail-info-card {
        padding: 16px !important;
        margin-bottom: 0 !important;
        margin-top: 0 !important;
    }
    .scanner-detail-lastpurchase-card {
        padding: 12px 14px !important;
        margin-bottom: 0 !important;
    }
    .scanner-detail-supplier-card {
        padding: 16px !important;
        margin-bottom: 0 !important;
        overflow: hidden !important;
        max-width: 100% !important;
    }
}
</style>

<script>
const SCANNER_IS_SUPERADMIN = <?= $scannerIsSuperadmin ? 'true' : 'false' ?>;

// Search state for Back button navigation
let lastSearchKeyword = '';
let lastSearchResultsData = null;

async function lookupBarcode() {
    const input = document.getElementById('barcodeInput');
    const code = input.value.trim();
    if (!code) return;

    lastSearchKeyword = code;
    
    const resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML = '<div style="text-align:center;padding:40px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><div style="font-size:12px;color:var(--text-muted);margin-top:10px;">Mencari produk...</div></div>';
    
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';

    // 1. Instant local IndexedDB pre-lookup (< 15ms)
    let offlineFound = null;
    if (typeof OfflineDB !== 'undefined') {
        try {
            const p = await OfflineDB.findByBarcode(code);
            if (p) {
                offlineFound = { type: 'single', data: p };
            } else {
                const searchData = await OfflineDB.searchProducts(code);
                if (searchData && searchData.length > 0) {
                    offlineFound = { type: 'multi', data: searchData };
                }
            }
        } catch (e) {}
    }

    // 2. Try online barcode lookup via API
    try {
        let data = null;
        if (typeof api === 'function') {
            data = await api(`${baseUrl}api/products/barcode/${encodeURIComponent(code)}`);
        } else {
            const res = await fetch(`${baseUrl}api/products/barcode/${encodeURIComponent(code)}`, { credentials: 'same-origin' });
            if (res.ok) data = await res.json();
        }

        if (data && data.id) {
            if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
            showProductResult(data);
            return;
        }
    } catch (e) {}

    // 3. Fallback: try online name search via API
    try {
        let searchData = null;
        if (typeof api === 'function') {
            searchData = await api(`${baseUrl}api/products/search?q=${encodeURIComponent(code)}`);
        } else {
            const res = await fetch(`${baseUrl}api/products/search?q=${encodeURIComponent(code)}`, { credentials: 'same-origin' });
            if (res.ok) searchData = await res.json();
        }

        if (Array.isArray(searchData) && searchData.length === 1) {
            if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
            fetchProductDetail(searchData[0].id);
            return;
        } else if (Array.isArray(searchData) && searchData.length > 0) {
            renderMultipleSearchResults(searchData, false);
            return;
        }
    } catch (searchErr) {}

    // 4. Final offline fallback if server calls failed or returned nothing
    if (offlineFound) {
        if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
        if (offlineFound.type === 'single') {
            showProductResultOffline(offlineFound.data);
        } else if (offlineFound.data.length === 1) {
            showProductResultOffline(offlineFound.data[0]);
        } else {
            renderMultipleSearchResults(offlineFound.data, true);
        }
        return;
    }

    // 5. Not found state
    resultDiv.innerHTML = '<div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:40px;text-align:center;"><i class="bi bi-search fs-1 text-muted"></i><h4 style="font-size:15px;font-weight:700;margin-top:10px;">Produk Tidak Ditemukan</h4><p style="font-size:12px;color:var(--text-muted);">Tidak ada hasil untuk "'+code+'".</p></div>';
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
    
    if (lastSearchResultsData && lastSearchResultsData.products && lastSearchResultsData.products.length > 1) {
        renderMultipleSearchResults(lastSearchResultsData.products, lastSearchResultsData.isOffline);
    } else if (lastSearchKeyword) {
        lookupBarcode();
    } else {
        document.getElementById('scanResult').innerHTML = '';
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

function showProductResultOffline(data) {
    renderProductScanResult(data, true);
}

function showProductResult(data) {
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
            lookupBarcode();
        });
    } else {
        if (typeof showToast === 'function') showToast('Scanner module tidak tersedia', 'error');
    }
}

let scannerTimer = null;
document.getElementById('barcodeInput')?.addEventListener('input', (e) => {
    clearTimeout(scannerTimer);
    scannerTimer = setTimeout(() => {
        lookupBarcode();
    }, 300);
});

// Support Enter key for immediate search
document.getElementById('barcodeInput')?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        clearTimeout(scannerTimer);
        lookupBarcode();
    }
});

// Auto-focus
document.getElementById('barcodeInput')?.focus();
</script>
