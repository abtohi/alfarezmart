<!-- Barcode Scanner View -->
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
                    resultDiv.innerHTML = searchData.map(prod => `
                        <a href="${BASE_URL}products/${prod.id}" class="product-card">
                            <div class="product-icon"><i class="bi bi-box-seam"></i></div>
                            <div class="product-info">
                                <div class="product-name">${prod.full_name}</div>
                                <div class="product-category">${prod.brand_name || ''} · ${prod.category_name || ''}</div>
                            </div>
                        </a>
                    `).join('');
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
            if (searchData.length > 0) {
                resultDiv.innerHTML = searchData.map(p => `
                    <a href="<?= BASE_URL ?>products/${p.id}" class="product-card">
                        <div class="product-icon"><i class="bi bi-box-seam"></i></div>
                        <div class="product-info">
                            <div class="product-name">${p.full_name}</div>
                            <div class="product-category">${p.brand_name || ''} · ${p.category_name || ''}</div>
                        </div>
                    </a>
                `).join('');
            } else {
                resultDiv.innerHTML = '<div class="empty-state"><i class="bi bi-search"></i><h3>Tidak Ditemukan</h3><p>Produk dengan barcode/nama tersebut tidak ada di database</p></div>';
            }
        } catch (e2) {
            resultDiv.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i><h3>Error</h3><p>Gagal mencari produk</p></div>';
        }
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
        
        // Tier pricing html
        let tierHtml = '';
        if (p.qty_prices && p.qty_prices.length > 0) {
            tierHtml = `<div style="margin-top:8px; padding-top:8px; border-top:1px dashed var(--border-color);">
                <div style="font-size:10px; font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Harga Tier / Kuantitas:</div>
                ${p.qty_prices.map(t => {
                    const tRet = parseFloat(t.price_retail) || 0;
                    const tWho = parseFloat(t.price_wholesale) || 0;
                    return `<div style="display:flex; justify-content:space-between; font-size:10px; margin-bottom:2px;">
                        <span><i class="bi bi-layers" style="margin-right:4px;"></i>Min. ${t.min_qty} ${p.unit_name}</span>
                        <span style="text-align:right;">
                            ${tRet > 0 ? `<span style="color:var(--success);">Ecer: ${formatRupiah(tRet)}</span>` : ''}
                            ${tRet > 0 && tWho > 0 ? '<span style="color:var(--text-muted);margin:0 4px;">|</span>' : ''}
                            ${tWho > 0 ? `<span style="color:var(--warning);">Grosir: ${formatRupiah(tWho)}</span>` : ''}
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
                        <div style="font-size:9px; color:rgba(150, 150, 150, 0.4); margin-top:2px;">
                            ${modal > 0 ? `Modal: ${formatRupiah(modal)}` : ''}
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
                <div style="width:50px;height:50px;background:var(--primary-bg);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-upc-scan" style="font-size:1.5rem;color:var(--primary);"></i>
                </div>
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
