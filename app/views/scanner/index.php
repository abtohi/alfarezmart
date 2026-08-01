<!-- Barcode Scanner View - Redesigned (v1.1.6) -->
<?php
$scannerUserLevel = $_SESSION['user_level'] ?? '';
$scannerIsSuperadmin = $scannerUserLevel === 'superadmin';
?>
<div class="page-section" style="padding-bottom:100px;">
    <!-- Modern Header -->
    <div style="background: linear-gradient(135deg, var(--surface-1), var(--surface-2)); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 22px 20px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 4px; color: var(--text-primary); letter-spacing: -0.3px; display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-upc-scan text-primary"></i> Cek Harga &amp; Scan Barcode
                </h2>
                <p style="color: var(--text-muted); font-size: 12px; margin: 0;">Scan barcode produk atau ketik nama/kode barcode untuk cek harga &amp; stok real-time</p>
            </div>
            
            <button onclick="openGlobalScanner()" class="btn-primary-custom" style="padding:10px 18px; font-size:12px; font-weight:700; border-radius:20px; display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg, #e63946, #d62828);">
                <i class="bi bi-camera-fill"></i> Buka Kamera Scanner
            </button>
        </div>

        <!-- Manual Input Bar -->
        <div style="margin-top: 18px; position: relative;">
            <div style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 4px 6px 4px 14px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <i class="bi bi-upc-scan" onclick="openGlobalScanner()" style="color: var(--primary); font-size: 1.3rem; cursor: pointer;" title="Klik untuk Buka Kamera Scanner"></i>
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

    <!-- Result Area (3 Split Columns Grid on Desktop) -->
    <div id="scanResult"></div>
</div>

<style>
.scan-results-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
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
    transition: all 0.22s ease;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    cursor: pointer;
    box-sizing: border-box;
}
.scan-result-card:hover {
    transform: translateY(-4px);
    border-color: var(--primary) !important;
    box-shadow: 0 10px 28px rgba(0,0,0,0.18) !important;
}
.scan-card-thumb {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-md);
    overflow: hidden;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.scan-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.scan-card-thumb-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-md);
    background: linear-gradient(135deg, rgba(230,57,70,0.15), rgba(230,57,70,0.05));
    border: 1px solid rgba(230,57,70,0.2);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.scan-card-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.35;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.scan-pill-badge {
    font-size: 9px;
    font-weight: 600;
    padding: 2px 7px;
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
    margin-top: 4px;
    background: var(--surface-2);
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
}
</style>

<script>
const SCANNER_IS_SUPERADMIN = <?= $scannerIsSuperadmin ? 'true' : 'false' ?>;

async function lookupBarcode() {
    const input = document.getElementById('barcodeInput');
    const code = input.value.trim();
    if (!code) return;
    
    const resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML = '<div style="text-align:center;padding:30px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><div style="font-size:12px;color:var(--text-muted);margin-top:8px;">Mencari produk...</div></div>';
    
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
    const resultDiv = document.getElementById('scanResult');
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';

    let cardsHtml = products.map(p => {
        const prodName = p.name || p.full_name || 'Tanpa Nama';
        const brand = p.brand_name || '';
        const category = p.category_name || '';
        
        return `
            <div class="scan-result-card" onclick="${isOffline ? `showProductResultOffline(${JSON.stringify(p).replace(/'/g, "&#39;")})` : `fetchProductDetail(${p.id})`}">
                <div>
                    <!-- Header Product -->
                    <div style="display:flex; gap:12px; align-items:flex-start; margin-bottom:12px;">
                        ${p.photo 
                            ? `<div class="scan-card-thumb">
                                   <img src="${baseUrl}${p.photo}">
                               </div>`
                            : `<div class="scan-card-thumb-icon">
                                   <i class="bi bi-box-seam"></i>
                               </div>`
                        }
                        <div style="flex:1; min-width:0;">
                            <div class="scan-card-title">${prodName}</div>
                            <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:4px;">
                                ${brand ? `<span class="scan-pill-badge brand"><i class="bi bi-award me-1"></i>${brand}</span>` : ''}
                                ${category ? `<span class="scan-pill-badge category"><i class="bi bi-tag me-1"></i>${category}</span>` : ''}
                            </div>
                            ${p.short_label ? `<div class="scan-short-label">Label: ${p.short_label}</div>` : ''}
                        </div>
                    </div>

                    <!-- Packaging List -->
                    ${renderPackagingsForList(p.packagings)}
                </div>

                <!-- Footer Action -->
                <div style="margin-top:12px; padding-top:10px; border-top:1px solid var(--border-color);">
                    <button class="btn-primary-custom w-100" style="padding:6px 12px; font-size:11px; border-radius:6px;">
                        <i class="bi bi-eye-fill me-1"></i> Detail &amp; Cek Lengkap
                    </button>
                </div>
            </div>
        `;
    }).join('');

    resultDiv.innerHTML = `<div class="scan-results-grid">${cardsHtml}</div>`;
}

function renderPackagingsForList(packagings) {
    if (!packagings || packagings.length === 0) return '';
    return '<div style="margin-top:8px; background:var(--surface-2); border-radius:var(--radius-md); padding:10px; border:1px solid var(--border-color);">' +
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
    resultDiv.innerHTML = '<div style="text-align:center;padding:30px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
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

function renderProductScanResult(data, isOffline) {
    const resultDiv = document.getElementById('scanResult');
    const packagings = data.packagings || [];
    const prodName = data.name || data.full_name || 'Tanpa Nama';
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';

    let priceHtml = packagings.map(p => {
        const modal = parseFloat(p.buy_price) || 0;
        const ecer = parseFloat(p.sell_price_retail) || 0;
        const grosir = parseFloat(p.sell_price_wholesale) || 0;
        const marginAmt = ecer - modal;
        const marginPct = modal > 0 ? ((marginAmt / modal) * 100).toFixed(1) : 0;
        
        // Tier pricing html
        let tierHtml = '';
        if (p.qty_prices && p.qty_prices.length > 0) {
            tierHtml = `<div style="margin-top:8px; padding-top:8px; border-top:1px dashed var(--border-color);">
                <div style="font-size:10px; font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Harga Tier / Grosir Bertingkat:</div>
                ${p.qty_prices.map(t => {
                    const tPrice = parseFloat(t.unit_price) || 0;
                    const mode = t.sale_mode || 'both';
                    const modeLabel = mode === 'retail' ? '<span style="color:var(--success);">Ecer</span>' : (mode === 'wholesale' ? '<span style="color:var(--warning);">Grosir</span>' : '<span style="color:var(--info);">Ecer/Grosir</span>');
                    return `<div style="display:flex; justify-content:space-between; font-size:10px; margin-bottom:2px;">
                        <span><i class="bi bi-layers me-1"></i>Min. ${t.min_qty} ${p.unit_name}</span>
                        <span style="text-align:right; font-weight:600;">
                            ${modeLabel}: ${formatRupiah(tPrice)}
                        </span>
                    </div>`;
                }).join('')}
            </div>`;
        }

        const baseQty = parseFloat(p.base_qty) || 1;
        
        return `
            <div style="padding:14px 0; border-bottom:1px solid var(--border-color);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px; flex-wrap:wrap; gap:8px;">
                    <div>
                        <div style="font-weight:700; font-size:13px; color:var(--text-primary);">
                            ${p.unit_name || 'Level '+p.level} 
                            <span style="font-size:10px; font-weight:600; color:var(--text-muted); background:var(--surface-2); padding:2px 8px; border-radius:12px; margin-left:4px;">Isi ${baseQty} pcs</span>
                        </div>
                        ${SCANNER_IS_SUPERADMIN && modal > 0 ? `
                            <div style="font-size:10px; color:var(--text-muted); margin-top:2px;">
                                Modal: <strong>${formatRupiah(modal)}</strong> &middot; Margin: <strong style="color:var(--success);">${formatRupiah(marginAmt)} (${marginPct}%)</strong>
                            </div>
                        ` : ''}
                    </div>
                    <div style="text-align:right;">
                        <div style="color:var(--success); font-weight:700; font-size:14px;">${formatRupiah(ecer)} <span style="font-size:10px;font-weight:600;color:var(--text-muted);">Ecer</span></div>
                        ${grosir > 0 ? `<div style="color:var(--warning); font-weight:600; font-size:12px; margin-top:2px;">${formatRupiah(grosir)} <span style="font-size:10px;font-weight:400;color:var(--text-muted);">Grosir</span></div>` : ''}
                    </div>
                </div>
                ${tierHtml}
            </div>
        `;
    }).join('');
    
    resultDiv.innerHTML = `
        <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:20px; border:1px solid var(--border-color); box-shadow:0 4px 16px rgba(0,0,0,0.06);">
            <div style="display:flex; gap:16px; margin-bottom:18px; flex-wrap:wrap; align-items:flex-start;">
                ${data.photo 
                    ? `<div style="width:72px;height:72px;border-radius:var(--radius-md);overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--surface-2);border:1px solid var(--border-color);flex-shrink:0;">
                           <img src="${baseUrl}${data.photo}" style="width:100%;height:100%;object-fit:contain;cursor:pointer;" onclick="viewFullPhoto(this.src)">
                       </div>`
                    : `<div style="width:72px;height:72px;background:linear-gradient(135deg, rgba(230,57,70,0.15), rgba(230,57,70,0.05));border:1px solid rgba(230,57,70,0.2);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary);">
                           <i class="bi bi-upc-scan" style="font-size:1.8rem;"></i>
                       </div>`
                }
                <div style="flex:1; min-width:220px;">
                    <h3 style="font-size:1.1rem; font-weight:800; margin-bottom:6px; line-height:1.3; color:var(--text-primary);">
                        ${prodName} 
                        ${isOffline ? `<span class="badge bg-warning text-dark" style="font-size:9px;vertical-align:middle;margin-left:4px;">OFFLINE</span>` : ''}
                    </h3>
                    <div style="font-size:11px; color:var(--text-muted); display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span class="scan-pill-badge brand"><i class="bi bi-award me-1"></i>${data.brand_name || 'Tanpa Brand'}</span>
                        <span class="scan-pill-badge category"><i class="bi bi-tag me-1"></i>${data.category_name || 'Tanpa Kategori'}</span>
                        ${data.short_label ? `<span class="scan-short-label">Label Struk: ${data.short_label}</span>` : ''}
                    </div>
                </div>
            </div>
            
            <div style="background:var(--surface-2); border-radius:var(--radius-md); padding:16px; border:1px solid var(--border-color);">
                <div style="font-weight:700; font-size:11px; margin-bottom:10px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-list-ul text-primary"></i> Daftar Harga per Kemasan
                </div>
                ${priceHtml || '<div style="color:var(--text-muted);font-size:12px;text-align:center;padding:12px 0;">Belum ada kemasan terdaftar</div>'}
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
