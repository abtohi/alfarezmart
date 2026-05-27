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
        const data = await api(`/api/products/barcode/${encodeURIComponent(code)}`);
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
            const searchData = await api(`/api/products/search?q=${encodeURIComponent(code)}`);
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
    const resultDiv = document.getElementById('scanResult');
    const packagings = data.packagings || [];
    
    let priceHtml = packagings.map(p => {
        const modal = parseFloat(p.buy_price) || 0;
        const ecer = parseFloat(p.sell_price_retail) || 0;
        const grosir = parseFloat(p.sell_price_wholesale) || 0;
        const margin = ecer > 0 ? calcMargin(modal, ecer) : 0;
        
        return `
            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--border-color);">
                <div>
                    <div style="font-weight:600; font-size:var(--font-size-sm);">Per ${p.unit_name || 'Level '+p.level} <span class="badge" style="background:var(--surface-2);color:var(--text-muted);font-size:10px;margin-left:4px;">Offline</span></div>
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Modal: ${formatRupiah(modal)}</div>
                </div>
                <div style="text-align:right;">
                    <div style="color:var(--success); font-weight:700;">${formatRupiah(ecer)}</div>
                    ${grosir > 0 ? `<div style="font-size:var(--font-size-xs); color:var(--warning);">Grosir: ${formatRupiah(grosir)}</div>` : ''}
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Margin: ${margin}%</div>
                </div>
            </div>
        `;
    }).join('');
    
    resultDiv.innerHTML = `
        <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:20px; border:1px solid var(--border-color);">
            <div style="display:flex; gap:14px; margin-bottom:16px;">
                <div style="width:56px;height:56px;background:var(--primary-bg);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-box-seam-fill" style="font-size:1.5rem;color:var(--primary);"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <h3 style="font-size:var(--font-size-md); font-weight:700; margin-bottom:4px;">${data.full_name} <span class="badge" style="background:var(--warning);color:black;font-size:9px;">OFFLINE</span></h3>
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted);">${data.brand_name || ''} · ${data.category_name || ''}</div>
                    ${data.short_label ? `<div style="font-size:var(--font-size-xs); color:var(--info); margin-top:2px;">Label: ${data.short_label}</div>` : ''}
                </div>
            </div>
            <div class="divider"></div>
            <div style="font-weight:600; font-size:var(--font-size-sm); margin-bottom:8px; color:var(--text-secondary);">Daftar Harga</div>
            ${priceHtml || '<p style="color:var(--text-muted);font-size:var(--font-size-sm);">Belum ada harga</p>'}
        </div>
    `;
}

function showProductResult(data) {
    const resultDiv = document.getElementById('scanResult');
    const packagings = data.packagings || [];
    
    let priceHtml = packagings.map(p => {
        const modal = parseFloat(p.buy_price) || 0;
        const ecer = parseFloat(p.sell_price_retail) || 0;
        const grosir = parseFloat(p.sell_price_wholesale) || 0;
        const margin = ecer > 0 ? calcMargin(modal, ecer) : 0;
        
        return `
            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--border-color);">
                <div>
                    <div style="font-weight:600; font-size:var(--font-size-sm);">Per ${p.unit_name || 'Level '+p.level}</div>
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Modal: ${formatRupiah(modal)}</div>
                </div>
                <div style="text-align:right;">
                    <div style="color:var(--success); font-weight:700;">${formatRupiah(ecer)}</div>
                    ${grosir > 0 ? `<div style="font-size:var(--font-size-xs); color:var(--warning);">Grosir: ${formatRupiah(grosir)}</div>` : ''}
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Margin: ${margin}%</div>
                </div>
            </div>
        `;
    }).join('');
    
    resultDiv.innerHTML = `
        <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:20px; border:1px solid var(--border-color);">
            <div style="display:flex; gap:14px; margin-bottom:16px;">
                <div style="width:56px;height:56px;background:var(--primary-bg);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-box-seam-fill" style="font-size:1.5rem;color:var(--primary);"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <h3 style="font-size:var(--font-size-md); font-weight:700; margin-bottom:4px;">${data.full_name}</h3>
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted);">${data.brand_name || ''} · ${data.category_name || ''}</div>
                    ${data.short_label ? `<div style="font-size:var(--font-size-xs); color:var(--info); margin-top:2px;">Label: ${data.short_label}</div>` : ''}
                </div>
            </div>
            <div class="divider"></div>
            <div style="font-weight:600; font-size:var(--font-size-sm); margin-bottom:8px; color:var(--text-secondary);">Daftar Harga</div>
            ${priceHtml || '<p style="color:var(--text-muted);font-size:var(--font-size-sm);">Belum ada harga</p>'}
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

// Enter key to search
document.getElementById('barcodeInput')?.addEventListener('keyup', (e) => {
    if (e.key === 'Enter') lookupBarcode();
});

// Auto-focus
document.getElementById('barcodeInput')?.focus();
</script>
