<!-- Barcode Scanner View - Rich Product Detail with E-Commerce Split Showcase & Supplier History (v1.2.0) -->
<?php
$scannerUserLevel = $_SESSION['user_level'] ?? '';
$scannerIsSuperadmin = $scannerUserLevel === 'superadmin';
?>
<div class="page-section" style="padding-bottom:100px;">
    <!-- Modern Header -->
    <div style="background: linear-gradient(135deg, var(--surface-1), var(--surface-2)); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 22px 20px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <div style="margin-bottom: 14px;">
            <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 4px; color: var(--text-primary); letter-spacing: -0.3px; display:flex; align-items:center; gap:8px;">
                <i class="bi bi-upc-scan text-primary"></i> Cek Harga &amp; Scan Barcode
            </h2>
            <p style="color: var(--text-muted); font-size: 12px; margin: 0;">Scan barcode produk atau ketik nama/kode barcode untuk cek harga &amp; stok real-time</p>
        </div>

        <!-- Manual Input Bar with Camera Trigger Icon -->
        <div style="position: relative;">
            <div style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 4px 6px 4px 14px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <i class="bi bi-upc-scan" onclick="openGlobalScanner()" style="color: var(--primary); font-size: 1.3rem; cursor: pointer;" title="Klik ikon barcode ini untuk Buka Kamera Scanner"></i>
                <input type="text" id="barcodeInput" placeholder="Ketik nama produk atau scan kode barcode..." 
                       style="flex:1; border:none; background:transparent; padding:10px 0; color:var(--text-primary); font-size:13px; outline:none; font-family: var(--font-family);" 
                       autocomplete="off" autofocus>
                <button onclick="lookupBarcode()" style="border:none; background:var(--primary); color:white; padding:9px 22px; border-radius: 8px; font-weight:700; font-size: 12px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
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
    
    try {
        const res = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : '/' }api/products/barcode/${encodeURIComponent(code)}`);
        if (!res.ok) throw new Error('Not found');
        const data = await res.json();
        if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
        showProductResult(data);
    } catch (e) {
        if (!navigator.onLine && typeof OfflineDB !== 'undefined') {
            try {
                const p = await OfflineDB.findByBarcode(code);
                if (p) {
                    if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
                    showProductResultOffline(p);
                    return;
                }
                const searchData = await OfflineDB.searchProducts(code);
                if (searchData && searchData.length > 0) {
                    if (searchData.length === 1) {
                        showProductResultOffline(searchData[0]);
                    } else {
                        renderMultipleSearchResults(searchData, true);
                    }
                } else {
                    resultDiv.innerHTML = '<div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:40px;text-align:center;"><i class="bi bi-search fs-1 text-muted"></i><h4 style="font-size:15px;font-weight:700;margin-top:10px;">Produk Tidak Ditemukan</h4><p style="font-size:12px;color:var(--text-muted);">Produk dengan barcode/nama tersebut tidak ada di database offline.</p></div>';
                }
            } catch (offErr) {
                resultDiv.innerHTML = '<div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:40px;text-align:center;"><i class="bi bi-exclamation-triangle fs-1 text-danger"></i><h4 style="font-size:15px;font-weight:700;margin-top:10px;">Error Database</h4><p style="font-size:12px;color:var(--text-muted);">Gagal mencari produk di database lokal.</p></div>';
            }
            return;
        }

        // Try search by name online
        try {
            const res = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : '/' }api/products/search?q=${encodeURIComponent(code)}`);
            if (!res.ok) throw new Error('Search failed');
            const searchData = await res.json();
            if (searchData.length === 1) {
                if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
                fetchProductDetail(searchData[0].id);
            } else if (searchData.length > 0) {
                renderMultipleSearchResults(searchData, false);
            } else {
                resultDiv.innerHTML = '<div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:40px;text-align:center;"><i class="bi bi-search fs-1 text-muted"></i><h4 style="font-size:15px;font-weight:700;margin-top:10px;">Produk Tidak Ditemukan</h4><p style="font-size:12px;color:var(--text-muted);">Produk dengan kata kunci "'+code+'" tidak ditemukan.</p></div>';
            }
        } catch (e2) {
            resultDiv.innerHTML = '<div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:40px;text-align:center;"><i class="bi bi-exclamation-triangle fs-1 text-danger"></i><h4 style="font-size:15px;font-weight:700;margin-top:10px;">Gagal Memuat Produk</h4><p style="font-size:12px;color:var(--text-muted);">Terjadi kesalahan saat mengambil data dari server.</p></div>';
        }
    }
}

function renderMultipleSearchResults(products, isOffline) {
    lastSearchResultsData = { products, isOffline };
    const resultDiv = document.getElementById('scanResult');
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';

    let cardsHtml = products.map(p => {
        const prodName = p.name || p.full_name || 'Tanpa Nama';
        const brand = p.brand_name || '';
        const category = p.category_name || '';
        
        return `
            <div class="scan-result-card" onclick="${isOffline ? `showProductResultOffline(${JSON.stringify(p).replace(/'/g, "&#39;")})` : `fetchProductDetail(${p.id})`}">
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

async function fetchProductDetail(id) {
    const resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML = '<div style="text-align:center;padding:40px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    try {
        const res = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : '/' }api/products/${id}`);
        if (!res.ok) throw new Error('Not found');
        const data = await res.json();
        showProductResult(data);
    } catch (e) {
        resultDiv.innerHTML = '<div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:40px;text-align:center;"><i class="bi bi-exclamation-triangle fs-1 text-danger"></i><h4 style="font-size:15px;font-weight:700;margin-top:10px;">Error</h4><p style="font-size:12px;color:var(--text-muted);">Gagal mengambil detail produk.</p></div>';
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

function renderSupplierGroupedSection(suppliers) {
    if (!suppliers || suppliers.length === 0) {
        return `<div class="text-center py-4 text-muted" style="font-size:12px;"><i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i> Belum ada riwayat supplier terdaftar untuk produk ini.</div>`;
    }

    return suppliers.map((sup, idx) => {
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
                        <td style="padding:8px 10px; color:var(--text-muted); white-space:nowrap;">${pDate}</td>
                        <td style="padding:8px 10px; font-weight:700; color:var(--primary); font-family:monospace;">${p.purchase_code || p.purchase_number || '-'}</td>
                        <td style="padding:8px 10px;">${qty} ${p.unit_name || 'Pcs'} <span style="font-size:9px; color:var(--text-muted);">(Isi ${p.base_qty || 1})</span></td>
                        <td style="padding:8px 10px; text-align:right; font-weight:700;">${formatRupiah(itemBuy)}</td>
                        <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--success);">${formatRupiah(subtotal)}</td>
                        <td style="padding:8px 10px; text-align:center;">
                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size:9px;">${p.status || 'Selesai'}</span>
                        </td>
                    </tr>
                `;
            }).join('');

            purchasesTableHtml = `
                <div style="max-height: 260px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-sm); margin-top:10px;">
                    <table class="w-100" style="font-size: 11px; border-collapse: collapse;">
                        <thead style="background: var(--surface-2); position: sticky; top: 0; z-index: 2;">
                            <tr style="border-bottom: 1px solid var(--border-color); text-transform: uppercase; font-size: 9px; color: var(--text-muted);">
                                <th style="padding:8px 10px;">Tanggal Pembelian</th>
                                <th style="padding:8px 10px;">No. Invoice</th>
                                <th style="padding:8px 10px;">Kemasan &amp; Qty</th>
                                <th style="padding:8px 10px; text-align:right;">Harga Beli Satuan</th>
                                <th style="padding:8px 10px; text-align:right;">Total Nilai</th>
                                <th style="padding:8px 10px; text-align:center;">Status</th>
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
            <div style="background:var(--surface-2); border:1px solid var(--border-color); border-radius:var(--radius-md); margin-bottom:12px; overflow:hidden;">
                <div onclick="toggleProductSupplierHistory(${idx})" style="padding:14px 16px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;" onmouseover="this.style.background='var(--surface-1)'" onmouseout="this.style.background='transparent'">
                    <div class="d-flex align-items-center gap-2">
                        <i id="prod-sup-chevron-${idx}" class="bi bi-chevron-right text-muted" style="font-size:12px; transition:transform 0.2s;"></i>
                        <div>
                            <div class="fw-bold" style="font-size:13px; color:var(--text-primary);">${sup.supplier_name}</div>
                            <div style="font-size:11px; color:var(--text-muted);">
                                ${sup.address ? `<i class="bi bi-geo-alt me-1"></i>${sup.address} &middot; ` : ''}
                                Total ${sup.purchase_count || purchases.length} Pembelian
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <div style="font-size:11px; color:var(--text-muted);">Harga Beli Terakhir:</div>
                        <div style="font-weight:700; color:var(--success); font-size:13px;">${lastBuyPrice > 0 ? formatRupiah(lastBuyPrice) : '-'}</div>
                        <div style="font-size:9px; color:var(--text-muted);">${lastDate !== '-' ? 'Per ' + lastDate : ''}</div>
                    </div>
                </div>

                <!-- History Sub-panel -->
                <div id="prod-sup-history-${idx}" style="display:none; padding:0 16px 16px; border-top:1px solid var(--border-color); background:var(--surface-1);">
                    <div class="d-flex align-items-center justify-content-between pt-3 mb-2">
                        <span class="fw-bold" style="font-size:11px; color:var(--primary);"><i class="bi bi-journal-text me-1"></i> Riwayat Pembelian Faktur (${purchases.length} Transaksi)</span>
                        <button type="button" onclick="event.stopPropagation(); toggleProductSupplierHistory(${idx})" class="btn btn-sm btn-outline-secondary" style="font-size:9px; padding:2px 8px;">
                            <i class="bi bi-chevron-up me-1"></i> Tutup
                        </button>
                    </div>
                    ${purchasesTableHtml}
                </div>
            </div>
        `;
    }).join('');
}

function renderProductScanResult(data, isOffline) {
    const resultDiv = document.getElementById('scanResult');
    const packagings = data.packagings || [];
    const prodName = data.name || data.full_name || 'Tanpa Nama';
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';

    // 1. Packagings & Markup % Breakdown
    let packagingsPricingTableHtml = packagings.map(p => {
        const modal = parseFloat(p.buy_price) || 0;
        const ecer = parseFloat(p.sell_price_retail) || 0;
        const grosir = parseFloat(p.sell_price_wholesale) || 0;
        const baseQty = parseFloat(p.base_qty) || 1;

        // Markup % = ((Sell - Modal) / Modal) * 100
        const markupRetailPct = (p.markup_retail_percent !== undefined) ? p.markup_retail_percent : (modal > 0 ? (((ecer - modal) / modal) * 100).toFixed(1) : 0);
        const profitRetailNominal = (p.profit_retail_nominal !== undefined) ? p.profit_retail_nominal : (ecer - modal);

        const markupWholesalePct = (p.markup_wholesale_percent !== undefined) ? p.markup_wholesale_percent : ((modal > 0 && grosir > 0) ? (((grosir - modal) / modal) * 100).toFixed(1) : 0);
        const profitWholesaleNominal = (p.profit_wholesale_nominal !== undefined) ? p.profit_wholesale_nominal : (grosir > 0 ? (grosir - modal) : 0);

        // Tier pricing
        let tierHtml = '';
        if (p.qty_prices && p.qty_prices.length > 0) {
            tierHtml = `
                <div style="margin-top:8px; padding-top:8px; border-top:1px dashed var(--border-color);">
                    <div style="font-size:10px; font-weight:700; color:var(--text-secondary); margin-bottom:4px;"><i class="bi bi-layers me-1 text-info"></i>Harga Grosir Bertingkat / Tier:</div>
                    ${p.qty_prices.map(t => {
                        const tPrice = parseFloat(t.unit_price) || 0;
                        const tMarkup = modal > 0 ? (((tPrice - modal) / modal) * 100).toFixed(1) : 0;
                        return `
                            <div style="display:flex; justify-content:space-between; font-size:10px; margin-bottom:2px;">
                                <span>Min. ${t.min_qty} ${p.unit_name}</span>
                                <span style="font-weight:700;">${formatRupiah(tPrice)} <span style="color:var(--success); font-size:9px;">(Markup +${tMarkup}%)</span></span>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        return `
            <div style="padding:14px 0; border-bottom:1px solid var(--border-color);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; flex-wrap:wrap; gap:10px;">
                    <div>
                        <div style="font-weight:800; font-size:14px; color:var(--text-primary);">
                            ${p.unit_name || 'Level '+p.level}
                            <span style="font-size:10px; font-weight:600; color:var(--text-muted); background:var(--surface-1); padding:2px 8px; border-radius:12px; margin-left:6px; border:1px solid var(--border-color);">Isi ${baseQty} pcs</span>
                            ${p.barcode ? `<span style="font-family:monospace; font-size:10px; color:var(--text-muted); margin-left:6px;"><i class="bi bi-upc me-1"></i>${p.barcode}</span>` : ''}
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-2" style="font-size:11px;">
                    <!-- Harga Modal -->
                    <div class="col-md-4 col-12">
                        <div style="background:var(--surface-1); padding:8px 10px; border-radius:6px; border:1px solid var(--border-color);">
                            <div style="font-size:10px; color:var(--text-muted);">Harga Modal (Beli):</div>
                            <div style="font-weight:800; color:var(--text-primary); font-size:13px;">${modal > 0 ? formatRupiah(modal) : '-'}</div>
                        </div>
                    </div>

                    <!-- Harga Ecer & Markup -->
                    <div class="col-md-4 col-6">
                        <div style="background:rgba(16,185,129,0.08); padding:8px 10px; border-radius:6px; border:1px solid rgba(16,185,129,0.2);">
                            <div style="font-size:10px; color:var(--success); font-weight:600;">Harga Ecer:</div>
                            <div style="font-weight:800; color:var(--success); font-size:13px;">${ecer > 0 ? formatRupiah(ecer) : '-'}</div>
                            ${modal > 0 && ecer > 0 ? `
                                <div style="font-size:9px; color:var(--success); font-weight:700; margin-top:2px;">
                                    Markup: +${markupRetailPct}% (+${formatRupiah(profitRetailNominal)})
                                </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Harga Grosir & Markup -->
                    <div class="col-md-4 col-6">
                        <div style="background:rgba(245,158,11,0.08); padding:8px 10px; border-radius:6px; border:1px solid rgba(245,158,11,0.2);">
                            <div style="font-size:10px; color:var(--warning); font-weight:600;">Harga Grosir:</div>
                            <div style="font-weight:800; color:var(--warning); font-size:13px;">${grosir > 0 ? formatRupiah(grosir) : '-'}</div>
                            ${modal > 0 && grosir > 0 ? `
                                <div style="font-size:9px; color:var(--warning); font-weight:700; margin-top:2px;">
                                    Markup: +${markupWholesalePct}% (+${formatRupiah(profitWholesaleNominal)})
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>

                ${tierHtml}
            </div>
        `;
    }).join('');

    // 2. Last Purchase Insight Card
    let lastPurchaseInfoHtml = '';
    if (data.last_purchase) {
        const lp = data.last_purchase;
        const lpDate = new Date(lp.purchase_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        lastPurchaseInfoHtml = `
            <div style="background:linear-gradient(135deg, rgba(59,130,246,0.1), rgba(59,130,246,0.02)); border:1px solid rgba(59,130,246,0.2); border-radius:var(--radius-md); padding:12px 14px; margin-bottom:14px;">
                <div style="font-size:10px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-clock-history"></i> Pembelian Terakhir
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; font-size:12px;">
                    <div>
                        <strong style="color:var(--text-primary);">${lp.supplier_name || 'Supplier Tanpa Nama'}</strong>
                        <span class="text-muted" style="font-size:11px;">&middot; ${lpDate} (${lp.purchase_code || lp.purchase_number || '-'})</span>
                    </div>
                    <div style="font-weight:700; color:var(--success);">
                        ${formatRupiah(lp.buy_price)} ${lp.unit_name ? '/ ' + lp.unit_name : ''}
                    </div>
                </div>
            </div>
        `;
    }

    // 3. Back Button Bar
    let backButtonHtml = '';
    if (lastSearchResultsData && lastSearchResultsData.products && lastSearchResultsData.products.length > 1) {
        backButtonHtml = `
            <div style="margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <button type="button" onclick="goBackToSearchResults()" class="btn btn-outline-primary" style="font-size:12px; font-weight:700; padding:8px 16px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Hasil Pencarian
                </button>
                ${lastSearchKeyword ? `<span style="font-size:12px; color:var(--text-muted);">Pencarian: <strong style="color:var(--text-primary);">"${lastSearchKeyword}"</strong></span>` : ''}
            </div>
        `;
    } else if (lastSearchKeyword) {
        backButtonHtml = `
            <div style="margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <button type="button" onclick="goBackToSearchResults()" class="btn btn-outline-primary" style="font-size:12px; font-weight:700; padding:8px 16px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Pencarian
                </button>
            </div>
        `;
    }
    
    resultDiv.innerHTML = `
        ${backButtonHtml}
        
        <!-- 2 SPLIT COLUMNS (Desktop) / 1 COLUMN (Mobile) -->
        <div class="row g-4 mb-4">
            <!-- LEFT COLUMN: Large Photo Showcase Stage + Download Button -->
            <div class="col-lg-5 col-md-12">
                <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; text-align:center; height:100%; display:flex; flex-direction:column; justify-content:space-between; box-shadow:0 4px 16px rgba(0,0,0,0.06);">
                    <div>
                        <div style="position:relative; width:100%; height:450px; background:var(--surface-2); border-radius:var(--radius-md); border:1px solid var(--border-color); display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:16px; padding:16px;">
                            ${data.photo 
                                ? `<img id="mainProductImg" src="${baseUrl}${data.photo}" style="width:100%; height:100%; max-height:420px; object-fit:contain; cursor:pointer;" onclick="viewFullPhoto(this.src)">`
                                : `<div style="font-size:6rem; color:var(--primary); opacity:0.65;"><i class="bi bi-box-seam"></i></div>`
                            }
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        ${data.photo ? `
                            <a href="${baseUrl}${data.photo}" download="${prodName.replace(/[^a-zA-Z0-9]/g, '_')}.jpg" target="_blank" class="btn btn-outline-primary flex-fill py-2" style="font-size:12px; font-weight:700; border-radius:8px;">
                                <i class="bi bi-download me-1"></i> Download Foto Produk
                            </a>
                            <button type="button" onclick="viewFullPhoto('${baseUrl}${data.photo}')" class="btn btn-secondary py-2" style="font-size:12px; font-weight:700; border-radius:8px;" title="Perbesar Foto">
                                <i class="bi bi-arrows-angle-expand"></i>
                            </button>
                        ` : '<div class="text-muted w-100" style="font-size:11px;">Belum ada foto produk ter-upload</div>'}
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Product Info & Packaging Pricing Breakdown -->
            <div class="col-lg-7 col-md-12">
                <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:24px; box-shadow:0 4px 16px rgba(0,0,0,0.06);">
                    <!-- Header Badges & Title -->
                    <h2 style="font-size:1.4rem; font-weight:800; color:var(--text-primary); margin-bottom:8px; line-height:1.3;">
                        ${prodName}
                        ${isOffline ? `<span class="badge bg-warning text-dark" style="font-size:10px; vertical-align:middle; margin-left:6px;">OFFLINE</span>` : ''}
                    </h2>

                    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-bottom:16px;">
                        <span class="scan-pill-badge brand" style="font-size:11px; padding:3px 10px;"><i class="bi bi-award me-1"></i>${data.brand_name || 'Tanpa Brand'}</span>
                        <span class="scan-pill-badge category" style="font-size:11px; padding:3px 10px;"><i class="bi bi-tag me-1"></i>${data.category_name || 'Tanpa Kategori'}</span>
                        ${data.short_label ? `<span class="scan-short-label" style="font-size:11px; padding:3px 10px;">Label Struk: ${data.short_label}</span>` : ''}
                        <span class="badge bg-info bg-opacity-10 text-info fw-bold" style="font-size:11px; padding:4px 8px;">Stok: ${data.current_qty_base ?? 0} Base Pcs</span>
                    </div>

                    <!-- Pembelian Terakhir (Last Purchase Insight Card) -->
                    ${lastPurchaseInfoHtml}

                    <!-- Detail Kemasan & Harga (Harga Modal, Harga Jual Ecer & Grosir, Markup %, Selisih Nominal) -->
                    <div style="background:var(--surface-2); border-radius:var(--radius-md); padding:16px; border:1px solid var(--border-color); margin-top:16px;">
                        <div style="font-weight:700; font-size:12px; margin-bottom:12px; color:var(--text-primary); text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:6px;">
                            <i class="bi bi-layers-half text-primary"></i> Detail Kemasan, Harga &amp; Persentase Markup
                        </div>
                        ${packagingsPricingTableHtml || '<div style="color:var(--text-muted);font-size:12px;text-align:center;padding:12px 0;">Belum ada kemasan terdaftar</div>'}
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM SECTION: Supplier Details & Purchase History Grouped by Supplier -->
        <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:24px; box-shadow:0 4px 16px rgba(0,0,0,0.06);">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1" style="font-size:15px; color:var(--text-primary);"><i class="bi bi-truck me-2 text-primary"></i>Pemasok / Detail Supplier (${data.suppliers ? data.suppliers.length : 0} Supplier)</h5>
                    <span class="text-muted" style="font-size:11px;">Daftar supplier yang menyuplai produk ini beserta riwayat pembelian lengkap (diurutkan dari tanggal terbaru)</span>
                </div>
            </div>

            ${renderSupplierGroupedSection(data.suppliers)}
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
