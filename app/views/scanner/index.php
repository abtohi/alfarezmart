<!-- Barcode Scanner View -->
<?php
$scannerUserLevel = $_SESSION['user_level'] ?? '';
$scannerIsSuperadmin = $scannerUserLevel === 'superadmin';
?>
<div class="page-section">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 80px; height: 80px; background: var(--primary-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <i class="bi bi-upc-scan" style="font-size: 2rem; color: var(--primary);"></i>
        </div>
        <h2 style="font-size: var(--font-size-xl); margin-bottom: 4px;">Cek Harga</h2>
        <p style="color: var(--text-muted); font-size: var(--font-size-sm);">Scan barcode atau ketik manual untuk cek harga</p>
    </div>

    <!-- Manual Input -->
    <div style="margin-bottom: 20px;">
        <div class="search-input-wrapper" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 0 16px;">
            <i class="bi bi-upc" style="color: var(--text-muted);"></i>
            <input type="text" id="barcodeInput" placeholder="Ketik barcode atau nama produk..." 
                   style="flex:1; border:none; background:transparent; padding:14px 0; color:var(--text-primary); font-size:var(--font-size-base); outline:none; font-family: var(--font-family);" 
                   autocomplete="off">
            <button onclick="lookupBarcode()" style="border:none; background:var(--primary); color:white; padding:8px 16px; border-radius: 8px; font-weight:600; font-size: var(--font-size-sm); cursor:pointer;">
                Cari
            </button>
        </div>
    </div>

    <!-- Camera Scanner Button -->
    <button id="btnStartScan" onclick="openGlobalScanner()" class="btn-outline-custom" style="width: 100%; margin-bottom: 24px;">
        <i class="bi bi-camera"></i> Buka Kamera Scanner
    </button>

    <!-- Result Area -->
    <div id="scanResult"></div>
</div>

<script>
const SCANNER_IS_SUPERADMIN = <?= $scannerIsSuperadmin ? 'true' : 'false' ?>;
async function lookupBarcode() {
    const input = document.getElementById('barcodeInput');
    const code = input.value.trim();
    if (!code) return;
    
    const resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML = '<div style="text-align:center;padding:20px;"><div class="skeleton" style="width:100%;height:120px;"></div></div>';
    
    try {
        const res = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : '/' }api/products/barcode/${encodeURIComponent(code)}`);
        if (!res.ok) throw new Error('Not found');
        const data = await res.json();
        showProductResult(data);
    } catch (e) {
        if (!navigator.onLine && typeof OfflineDB !== 'undefined') {
            try {
                const p = await OfflineDB.findByBarcode(code);
                if (p) {
                    showProductResultOffline(p);
                    return;
                }
                const searchData = await OfflineDB.searchProducts(code);
                if (searchData && searchData.length > 0) {
                    if (searchData.length === 1) {
                        showProductResultOffline(searchData[0]);
                    } else {
                        resultDiv.innerHTML = searchData.map(prod => `
                            <div class="product-card" onclick='showProductResultOffline(${JSON.stringify(prod).replace(/'/g, "&#39;")})' style="cursor:pointer; flex-direction:column; align-items:stretch;">
                                <div style="display:flex; align-items:center;">
                                    ${prod.photo 
                                        ? `<div class="product-icon" style="width:60px; height:60px; border-radius:var(--radius-md); overflow:hidden; display:flex; align-items:center; justify-content:center; background:transparent; flex-shrink:0; margin-right:16px;">
                                               <img src="${typeof BASE_URL !== 'undefined' ? BASE_URL : '/'}${prod.photo}" style="width:100%; height:100%; object-fit:contain;">
                                           </div>`
                                        : `<div class="product-icon" style="width:60px; height:60px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; background:var(--primary-bg); color:var(--primary); font-size:1.5rem; flex-shrink:0; margin-right:16px;"><i class="bi bi-box-seam"></i></div>`
                                    }
                                    <div class="product-info" style="flex:1;">
                                        <div class="product-name">${prod.full_name}</div>
                                        <div class="product-category">${prod.brand_name || ''} · ${prod.category_name || ''}</div>
                                    </div>
                                </div>
                                ${renderPackagingsForList(prod.packagings)}
                            </div>
                        `).join('');
                    }
                } else {
                    resultDiv.innerHTML = '<div class="empty-state"><i class="bi bi-search"></i><h3>Tidak Ditemukan</h3><p>Produk dengan barcode/nama tersebut tidak ada di database lokal (Offline)</p></div>';
                }
            } catch (offErr) {
                resultDiv.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i><h3>Error</h3><p>Gagal mencari produk di database lokal</p></div>';
            }
            return;
        }

        // Try search by name online
        try {
            const res = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : '/' }api/products/search?q=${encodeURIComponent(code)}`);
            if (!res.ok) throw new Error('Search failed');
            const searchData = await res.json();
            if (searchData.length === 1) {
                fetchProductDetail(searchData[0].id);
            } else if (searchData.length > 0) {
                resultDiv.innerHTML = searchData.map(p => `
                    <div class="product-card" onclick="fetchProductDetail(${p.id})" style="cursor:pointer; flex-direction:column; align-items:stretch;">
                        <div style="display:flex; align-items:center;">
                            ${p.photo 
                                ? `<div class="product-icon" style="width:60px; height:60px; border-radius:var(--radius-md); overflow:hidden; display:flex; align-items:center; justify-content:center; background:transparent; flex-shrink:0; margin-right:16px;">
                                       <img src="${typeof BASE_URL !== 'undefined' ? BASE_URL : '/'}${p.photo}" style="width:100%; height:100%; object-fit:contain;">
                                   </div>`
                                : `<div class="product-icon" style="width:60px; height:60px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; background:var(--primary-bg); color:var(--primary); font-size:1.5rem; flex-shrink:0; margin-right:16px;"><i class="bi bi-box-seam"></i></div>`
                            }
                            <div class="product-info" style="flex:1;">
                                <div class="product-name">${p.full_name}</div>
                                <div class="product-category">${p.brand_name || ''} · ${p.category_name || ''}</div>
                            </div>
                        </div>
                        ${renderPackagingsForList(p.packagings)}
                    </div>
                `).join('');
            } else {
                resultDiv.innerHTML = '<div class="empty-state"><i class="bi bi-search"></i><h3>Tidak Ditemukan</h3><p>Produk dengan barcode/nama tersebut tidak ada di database</p></div>';
            }
        } catch (e2) {
            resultDiv.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i><h3>Error</h3><p>Gagal mencari produk</p></div>';
        }
    }
}

function renderPackagingsForList(packagings) {
    if (!packagings || packagings.length === 0) return '';
    return '<div style="margin-top:10px; border-top:1px dashed var(--border-color); padding-top:10px;">' +
        packagings.map(pkg => {
            const jenis = pkg.unit_name || ('Level ' + pkg.level);
            const qty = pkg.base_qty || 1;
            const ecer = parseFloat(pkg.sell_price_retail) || 0;
            const grosir = parseFloat(pkg.sell_price_wholesale) || 0;
            const modal = parseFloat(pkg.buy_price) || 0;
            const marginAmt = ecer - modal;
            const marginPct = modal > 0 ? ((marginAmt / modal) * 100).toFixed(1) : 0;
            
            return `
                <div style="margin-bottom:8px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
                        <div style="font-weight:700; font-size:11px; color:var(--text-primary);">${jenis} <span style="font-weight:400; color:var(--text-muted); font-size:9px;">(Isi ${qty})</span></div>
                        <div style="font-size:9px; color:rgba(150, 150, 150, 0.4); text-shadow:0 1px 1px rgba(0,0,0,0.1); text-align:right;">
                            ${SCANNER_IS_SUPERADMIN && modal > 0 ? `M: ${formatRupiah(modal)} | P: ${formatRupiah(marginAmt)} (${marginPct}%)` : ''}
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:12px;">
                        <div>${ecer > 0 ? `<span style="color:var(--text-muted); font-size:9px;">Ecer:</span> <span style="color:var(--success); font-weight:700;">${formatRupiah(ecer)}</span>` : ''}</div>
                        <div>${grosir > 0 ? `<span style="color:var(--text-muted); font-size:9px;">Grosir:</span> <span style="color:var(--warning); font-weight:600;">${formatRupiah(grosir)}</span>` : ''}</div>
                    </div>
                </div>
            `;
        }).join('') +
    '</div>';
}

async function fetchProductDetail(id) {
    const resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML = '<div style="text-align:center;padding:20px;"><div class="skeleton" style="width:100%;height:120px;"></div></div>';
    
    try {
        const res = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : '/' }api/products/${id}`);
        if (!res.ok) throw new Error('Not found');
        const data = await res.json();
        showProductResult(data);
    } catch (e) {
        resultDiv.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i><h3>Error</h3><p>Gagal mengambil detail produk</p></div>';
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
                <div style="font-size:10px; font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Harga Tier / Kuantitas:</div>
                ${p.qty_prices.map(t => {
                    const tPrice = parseFloat(t.unit_price) || 0;
                    const mode = t.sale_mode || 'both';
                    const modeLabel = mode === 'retail' ? '<span style="color:var(--success);">Ecer</span>' : (mode === 'wholesale' ? '<span style="color:var(--warning);">Grosir</span>' : '<span style="color:var(--info);">Ecer/Grosir</span>');
                    return `<div style="display:flex; justify-content:space-between; font-size:10px; margin-bottom:2px;">
                        <span><i class="bi bi-layers" style="margin-right:4px;"></i>Min. ${t.min_qty} ${p.unit_name}</span>
                        <span style="text-align:right; font-weight:600;">
                            ${modeLabel}: ${formatRupiah(tPrice)}
                        </span>
                    </div>`;
                }).join('')}
            </div>`;
        }

        const baseQty = parseFloat(p.base_qty) || 1;
        
        return `
            <div style="padding:12px 0; border-bottom:1px solid var(--border-color);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
                    <div>
                        <div style="font-weight:700; font-size:var(--font-size-sm); color:var(--text-primary);">
                            ${p.unit_name || 'Level '+p.level} 
                            <span style="font-size:10px; font-weight:400; color:var(--text-muted); background:var(--surface-2); padding:2px 6px; border-radius:4px; margin-left:4px;">Isi ${baseQty} pcs</span>
                        </div>
                        <div style="font-size:9px; color:rgba(150, 150, 150, 0.4); margin-top:2px; text-shadow:0 1px 1px rgba(0,0,0,0.1);">
                            ${SCANNER_IS_SUPERADMIN && modal > 0 ? `M: ${formatRupiah(modal)} | P: ${formatRupiah(marginAmt)} (${marginPct}%)` : ''}
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="color:var(--success); font-weight:700; font-size:var(--font-size-base);">${formatRupiah(ecer)} <span style="font-size:9px;font-weight:400;color:var(--text-muted);">Ecer</span></div>
                        ${grosir > 0 ? `<div style="color:var(--warning); font-weight:600; font-size:var(--font-size-sm); margin-top:2px;">${formatRupiah(grosir)} <span style="font-size:9px;font-weight:400;color:var(--text-muted);">Grosir</span></div>` : ''}
                    </div>
                </div>
                ${tierHtml}
            </div>
        `;
    }).join('');
    
    resultDiv.innerHTML = `
        <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:16px; border:1px solid var(--border-color); box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <div style="display:flex; gap:14px; margin-bottom:16px;">
                ${data.photo 
                    ? `<div style="width:60px;height:60px;border-radius:var(--radius-md);overflow:hidden;display:flex;align-items:center;justify-content:center;background:transparent;flex-shrink:0;">
                           <img src="${(typeof BASE_URL !== 'undefined' ? BASE_URL : '/')}${data.photo}" style="width:100%;height:100%;object-fit:contain;cursor:pointer;" onclick="viewFullPhoto(this.src)">
                       </div>`
                    : `<div style="width:50px;height:50px;background:var(--primary-bg);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                           <i class="bi bi-upc-scan" style="font-size:1.5rem;color:var(--primary);"></i>
                       </div>`
                }
                <div style="flex:1; min-width:0;">
                    <h3 style="font-size:var(--font-size-md); font-weight:700; margin-bottom:4px; line-height:1.2;">
                        ${data.full_name} 
                        ${isOffline ? `<span class="badge" style="background:var(--warning);color:black;font-size:9px;vertical-align:middle;margin-left:4px;">OFFLINE</span>` : ''}
                    </h3>
                    <div style="font-size:11px; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                        <i class="bi bi-tag-fill" style="color:var(--info);"></i>
                        ${data.brand_name || 'Tanpa Brand'} · ${data.category_name || 'Tanpa Kategori'}
                    </div>
                    ${data.short_label ? `<div style="font-size:10px; color:var(--text-secondary); margin-top:4px; background:var(--surface-2); display:inline-block; padding:2px 8px; border-radius:10px;">Label: ${data.short_label}</div>` : ''}
                </div>
            </div>
            
            <div style="background:var(--bg-default); border-radius:var(--radius-md); padding:12px; border:1px solid var(--border-color);">
                <div style="font-weight:700; font-size:11px; margin-bottom:8px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px;">
                    <i class="bi bi-list-ul" style="margin-right:4px;"></i> Harga per Kemasan
                </div>
                ${priceHtml || '<div style="color:var(--text-muted);font-size:var(--font-size-sm);text-align:center;padding:12px 0;">Belum ada harga</div>'}
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
        showToast('Scanner module not loaded', 'error');
    }
}

let scannerTimer = null;
document.getElementById('barcodeInput')?.addEventListener('input', (e) => {
    clearTimeout(scannerTimer);
    scannerTimer = setTimeout(() => {
        lookupBarcode();
    }, 300);
});

// Also support Enter key for immediate search
document.getElementById('barcodeInput')?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        clearTimeout(scannerTimer);
        lookupBarcode();
    }
});

// Auto-focus
document.getElementById('barcodeInput')?.focus();
</script>


