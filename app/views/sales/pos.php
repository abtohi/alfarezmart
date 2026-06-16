<!-- POS View -->
<?php
/**
 * @var string $csrfToken
 */
?>
<div class="page-section" style="padding-bottom:200px;">
    <div class="pos-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <h2 style="font-size:var(--font-size-lg); font-weight:700; margin:0;">Kasir (POS)</h2>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <button class="btn-outline-custom" onclick="window.location.href=`${BASE_URL}sales`" style="padding:8px 14px; border-radius:var(--radius-md); font-size:var(--font-size-xs); background:var(--surface-1); border:1px solid var(--border-color); display:flex; align-items:center; gap:6px;" title="Lihat Riwayat Penjualan"><i class="bi bi-clock-history"></i> <span>Riwayat</span></button>
            <button class="btn-outline-custom" onclick="openDrafts()" style="padding:8px 14px; border-radius:var(--radius-md); font-size:var(--font-size-xs); background:var(--surface-1); border:1px solid var(--border-color); display:flex; align-items:center; gap:6px;"><i class="bi bi-journal-bookmark"></i> <span>Draft</span></button>
            <div style="display:flex; background:var(--surface-1); border-radius:var(--radius-md); padding:4px; border:1px solid var(--border-color);">
                <button id="btnRetail" class="btn-primary-custom" style="padding:8px 16px; border-radius:var(--radius-sm); font-size:var(--font-size-xs);" onclick="setSaleMode('retail')">Ecer</button>
                <button id="btnWholesale" class="btn-outline-custom" style="padding:8px 16px; border-radius:var(--radius-sm); font-size:var(--font-size-xs); border:none;" onclick="setSaleMode('wholesale')">Grosir</button>
            </div>
        </div>
    </div>
    
    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- Customer Selector -->
    <div style="margin-bottom:12px; position:relative;">
        <div id="customerSelectorBox"
             onclick="toggleCustomerDropdown()"
             style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:10px 14px; display:flex; align-items:center; gap:10px; cursor:pointer; transition:all 0.2s; user-select:none;"
             onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=document.getElementById('customerDropdown').style.display==='none'?'var(--border-color)':'var(--primary)'">
            <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-person" id="customerSelectorIcon" style="color:var(--primary);font-size:1rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:10px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.4px;margin-bottom:1px;">Pelanggan</div>
                <div id="customerSelectorLabel" style="font-size:var(--font-size-sm);font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Pelanggan Umum</div>
            </div>
            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                <button type="button" id="btnClearCustomer" onclick="event.stopPropagation();clearCustomer()" title="Hapus pilihan" style="display:none;background:var(--surface-2);border:1px solid var(--border-color);border-radius:50%;width:24px;height:24px;padding:0;cursor:pointer;color:var(--text-muted);font-size:0.75rem;line-height:1;">
                    <i class="bi bi-x"></i>
                </button>
                <i class="bi bi-chevron-down" id="customerChevron" style="color:var(--text-muted);font-size:0.75rem;transition:transform 0.2s;"></i>
            </div>
        </div>
        <!-- Customer Dropdown Panel -->
        <div id="customerDropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:200;background:var(--surface-1);border:1px solid var(--primary);border-radius:var(--radius-md);box-shadow:0 8px 32px rgba(0,0,0,0.25);overflow:hidden;">
            <div style="padding:10px 12px;border-bottom:1px solid var(--border-color);">
                <div style="display:flex;align-items:center;gap:8px;background:var(--bg-input);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:0 10px;">
                    <i class="bi bi-search" style="color:var(--text-muted);font-size:0.9rem;"></i>
                    <input type="text" id="customerSearchInput"
                           placeholder="Ketik nama atau nomor HP..."
                           autocomplete="off" autocorrect="off" spellcheck="false"
                           style="flex:1;border:none;background:transparent;padding:10px 4px;color:var(--text-primary);font-size:var(--font-size-sm);outline:none;font-family:var(--font-family);"
                           oninput="onCustomerSearch(this.value)">
                    <i class="bi bi-x-circle" style="color:var(--text-muted);font-size:0.9rem;cursor:pointer;" onclick="document.getElementById('customerSearchInput').value='';onCustomerSearch('');"></i>
                </div>
            </div>
            <div id="customerResults" style="max-height:220px;overflow-y:auto;padding:6px 0;">
                <!-- populated by JS -->
            </div>
        </div>
    </div>

    <!-- Search: barcode atau nama -->
    <div style="background:var(--surface-1); border-radius:var(--radius-md); padding:12px; margin-bottom:16px; border:1px solid var(--border-color);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
            <label style="font-size:var(--font-size-xs); font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin:0;">Scan/Cari Produk</label>
            <button type="button" class="btn-outline-custom" onclick="openCustomProductModal()" style="padding:4px 10px; border-radius:6px; font-size:11px; display:inline-flex; align-items:center; gap:4px; border:1px solid var(--border-color); background:var(--surface-2);">
                <i class="bi bi-plus-circle"></i> + Barang Custom
            </button>
        </div>
        <div class="search-input-wrapper" style="background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:0 12px; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-upc-scan" style="color:var(--primary); font-size:1.2rem; cursor:pointer;" onclick="BarcodeUtil.scanBarcode(document.getElementById('posSearch'))" title="Scan Barcode Kamera"></i>
            <input type="text" id="posSearch" placeholder="Scan barcode atau ketik nama produk..." 
                   style="flex:1;border:none;background:transparent;padding:12px 8px;color:var(--text-primary);font-size:var(--font-size-base);outline:none;font-family:var(--font-family);" autocomplete="off" autofocus>
            <i class="bi bi-search" style="color:var(--text-muted); font-size:1rem;"></i>
        </div>
        <div id="posSuggestions" style="margin-top:8px;"></div>
    </div>

    <!-- Cart -->
    <div id="cartItems">
        <div class="empty-state" id="emptyCartState" style="padding:48px 24px;">
            <i class="bi bi-basket2" style="font-size:3rem; opacity:0.4; color:var(--text-muted);"></i>
            <h3 style="margin-top:16px; font-size:var(--font-size-md); color:var(--text-primary);">Keranjang Kosong</h3>
            <p style="font-size:var(--font-size-sm); color:var(--text-muted); margin-top:8px;">Scan atau ketik nama produk untuk memulai</p>
        </div>
    </div>

    <!-- Floating Checkout Bar (lebar = aplikasi, max 480px) -->
    <div class="pos-checkout-bar" id="posCheckoutBar">
        <div class="pos-checkout-bar__inner">
            <div class="pos-checkout-bar__summary">
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <span class="pos-checkout-bar__summary-label">Total Belanja</span>
                    <span id="cartTotal" class="pos-checkout-bar__total">Rp0</span>
                    <span id="cartProfit" style="font-size:0.75rem; color:var(--text-muted); font-weight:600;"></span>
                </div>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);">
                    <span id="cartCount">0</span> item
                </div>
            </div>
            <div class="pos-checkout-bar__actions">
                <button id="btnSaveDraft" type="button" class="btn-outline-custom pos-checkout-bar__btn-draft" onclick="saveDraft()" disabled>
                    <i class="bi bi-save"></i> Draft
                </button>
                <button id="btnCheckout" type="button" class="btn-primary-custom pos-checkout-bar__btn-pay" onclick="checkout()" disabled>
                    <i class="bi bi-check-circle"></i> Bayar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const STORE_SETTINGS = <?= json_encode($storeSettings ?? [], JSON_UNESCAPED_UNICODE) ?>;

let cart = [];
let saleMode = 'retail';
let currentDraftId = null;
let editSaleId = null;
let searchInput, suggestionsDiv, cartContainer, emptyState, cartTotalEl, cartCountEl, btnCheckout, btnSaveDraft;
let selectedCustomer = null; // { id, name, phone } or null = Pelanggan Umum

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

function getPkgForItem(item) {
    return item.packagings?.find(p => p.level == item.level) || null;
}

function recalcItemPrice(item) {
    if (item.use_custom_price) {
        const lineTotal = parseFloat(item.custom_line_total);
        if (Number.isFinite(lineTotal) && lineTotal >= 0) {
            item.total = lineTotal;
            const qty = parseFloat(item.quantity) || 1;
            item.unit_price = qty > 0 ? lineTotal / qty : 0;
        } else {
            item.total = 0;
            item.unit_price = 0;
        }
        item.price_note = 'Harga custom (total)';
        return;
    }
    const pkg = getPkgForItem(item);
    if (!pkg) return;

    const qty = parseFloat(item.quantity) || 1;

    // Robust check: verify QtyPricing AND its methods exist
    let basePricePerUnit = 0;
    let rawTotal = 0;
    if (typeof QtyPricing !== 'undefined' && typeof QtyPricing.calculateTotalPrice === 'function') {
        rawTotal = QtyPricing.calculateTotalPrice(pkg, saleMode, qty, false, null, item.packagings);
        basePricePerUnit = qty > 0 ? rawTotal / qty : 0;
        item.price_note = typeof QtyPricing.getPriceNote === 'function'
            ? QtyPricing.getPriceNote(pkg, saleMode, qty, false, item.packagings) : '';
    } else {
        // Fallback: direct unit price (no tier pricing)
        basePricePerUnit = saleMode === 'wholesale'
            ? (parseFloat(pkg.sell_price_wholesale) || parseFloat(pkg.sell_price_retail) || 0)
            : (parseFloat(pkg.sell_price_retail) || 0);
        rawTotal = basePricePerUnit * qty;
        item.price_note = '';
    }

    // Apply PPN and Discount
    const ppnPct = parseFloat(pkg.ppn_pct) || 0;
    const dMode = pkg.discount_mode || 'rp';
    const dVal = parseFloat(pkg.discount_value) || 0;

    let ppnAmount = 0;
    if (ppnPct > 0) {
        ppnAmount = basePricePerUnit * (ppnPct / 100);
    }

    let discountAmount = 0;
    if (dVal > 0) {
        if (dMode === 'pct') {
            // Usually discount percent is applied to base + ppn or just base. 
            // In typical POS, if both exist: (Base + PPN) - Diskon or (Base - Diskon) + PPN
            // We'll apply % discount to (basePricePerUnit + ppnAmount)
            discountAmount = (basePricePerUnit + ppnAmount) * (dVal / 100);
        } else {
            discountAmount = dVal;
        }
    }

    const finalUnitPrice = basePricePerUnit + ppnAmount - discountAmount;
    item.total = Math.round(finalUnitPrice * qty);
    item.unit_price = qty > 0 ? item.total / qty : 0;

    // Add Diskon info to price_note (Hide PPN per task rules)
    if (discountAmount > 0) {
        let extraNote = [];
        extraNote.push(`-Diskon ${dMode === 'pct' ? dVal + '%' : 'Rp' + dVal}`);
        item.price_note = (item.price_note ? item.price_note + ' | ' : '') + extraNote.join(' ');
    }
}

function getThermalPrinterSafe() {
    return window.thermalPrinter || ((typeof thermalPrinter !== 'undefined' && thermalPrinter) ? thermalPrinter : null);
}

function updateCartItemDom(item) {
    const row = cartContainer?.querySelector(`[data-cart-id="${item.id}"]`);
    if (!row) return;
    const unitPriceEl = row.querySelector('.cart-item-unit-price');
    const totalEl = row.querySelector('.cart-item-total');
    const noteEl = row.querySelector('.cart-item-note');
    if (unitPriceEl) {
        if (item.use_custom_price) {
            unitPriceEl.textContent = `${formatRupiah(item.unit_price)} / ${item.unit_name} (Total ${formatRupiah(item.total)})`;
        } else {
            unitPriceEl.textContent = `${formatRupiah(item.unit_price)} / ${item.unit_name}`;
        }
    }
    if (totalEl) totalEl.textContent = formatRupiah(item.total);
    if (noteEl) {
        if (item.price_note) {
            noteEl.textContent = item.price_note;
            noteEl.style.display = '';
        } else {
            noteEl.style.display = 'none';
        }
    }
    
    // Update custom markup info
    const markupEl = row.querySelector('.cart-custom-markup');
    if (markupEl) {
        if (item.use_custom_price) {
            const curPkg = item.packagings?.find(p => p.level == item.level);
            const buyPrice = parseFloat(curPkg?.buy_price) || 0;
            if (buyPrice > 0 && item.custom_line_total !== null && item.custom_line_total !== '') {
                const customUnitPrice = parseFloat(item.custom_line_total) / item.quantity;
                const profitPerUnit = customUnitPrice - buyPrice;
                const totalProfit = parseFloat(item.custom_line_total) - (buyPrice * item.quantity);
                const markupPct = (profitPerUnit / buyPrice * 100).toFixed(1);
                
                let color = profitPerUnit >= 0 ? 'var(--success)' : 'var(--danger)';
                let icon = profitPerUnit >= 0 ? '<i class="bi bi-arrow-up-right"></i>' : '<i class="bi bi-arrow-down-right"></i>';
                
                markupEl.innerHTML = `<div style="color:${color};"><span style="color:var(--text-muted);font-weight:400;">M:</span> ${icon} ${markupPct}% &nbsp;|&nbsp; <span style="color:var(--text-muted);font-weight:400;">S:</span> ${formatRupiah(profitPerUnit)} &nbsp;|&nbsp; <span style="color:var(--text-muted);font-weight:400;">P:</span> ${formatRupiah(totalProfit)}</div>`;
                markupEl.style.display = 'block';
            } else {
                markupEl.style.display = 'none';
            }
        } else {
            markupEl.style.display = 'none';
        }
    }
}

window.togglePosCustomPrice = function(itemId, checked) {
    const item = cart.find(i => i.id == itemId);
    if (!item) return;
    item.use_custom_price = !!checked;
    if (checked) {
        if (item.custom_line_total == null || item.custom_line_total === '') {
            item.custom_line_total = item.total || (item.unit_price * item.quantity);
        }
        item.custom_price_draft = String(item.custom_line_total || '');
    } else {
        delete item.custom_price_draft;
        item.custom_line_total = null;
    }
    recalcItemPrice(item);
    renderCart();
    if (checked) {
        const input = cartContainer?.querySelector(`[data-cart-id="${itemId}"] .cart-custom-price-input`);
        input?.focus();
    }
};

window.onPosCustomPriceInput = function(itemId, inputEl) {
    const item = cart.find(i => i.id == itemId);
    if (!item || !item.use_custom_price) return;
    const raw = inputEl.value;
    item.custom_price_draft = raw;
    if (raw === '' || raw === '.') {
        item.custom_line_total = 0;
        item.unit_price = 0;
        item.total = 0;
        item.price_note = 'Harga custom (total)';
    } else {
        const num = parseFloat(raw);
        if (!Number.isFinite(num) || num < 0) return;
        item.custom_line_total = num;
        recalcItemPrice(item);
    }
    updateCartItemDom(item);
    calculateTotal();
};

function setSaleMode(mode) {
    saleMode = mode;
    if (mode === 'retail') {
        document.getElementById('btnRetail').className = 'btn-primary-custom';
        document.getElementById('btnRetail').style.border = '';
        document.getElementById('btnWholesale').className = 'btn-outline-custom';
        document.getElementById('btnWholesale').style.border = 'none';
    } else {
        document.getElementById('btnWholesale').className = 'btn-primary-custom';
        document.getElementById('btnWholesale').style.border = '';
        document.getElementById('btnRetail').className = 'btn-outline-custom';
        document.getElementById('btnRetail').style.border = 'none';
    }

    cart.forEach(item => {
        recalcItemPrice(item);
    });
    renderCart();
}

function initPosSearch() {
    const inp = document.getElementById('posSearch');
    const sug = document.getElementById('posSuggestions');
    
    if (!inp || !sug) return; // Elements don't exist yet, exit gracefully

    // Input event for text search
    inp.addEventListener('input', function() {
        const q = this.value.trim();
        if (q.length < 2) {
            sug.innerHTML = '';
            if (window.posSearchAbortController) window.posSearchAbortController.abort();
            return;
        }
        performSearch(q);
    });

    // Click outside to hide suggestions
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-input-wrapper') && !e.target.closest('#posSuggestions')) {
            sug.innerHTML = '';
        }
    });

    // Enter key for barcode inside the input
    inp.addEventListener('keypress', async function(e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const q = this.value.trim();
        if (!q) return;
        
        await processBarcodeScan(q, this, sug);
    });

    // Global document listener for barcode scanners
    let barcodeBuffer = '';
    let barcodeTimeout = null;

    document.addEventListener('keypress', function(e) {
        // Ignore if user is already typing in an input/textarea/select
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
        
        if (e.key === 'Enter') {
            if (barcodeBuffer.length >= 3) {
                e.preventDefault();
                processBarcodeScan(barcodeBuffer, inp, sug);
            }
            barcodeBuffer = '';
            return;
        }
        
        if (e.key.length === 1) { // Printable char
            barcodeBuffer += e.key;
            if (barcodeTimeout) clearTimeout(barcodeTimeout);
            // Barcode scanners type very fast (< 30ms between strokes). Reset if slower.
            barcodeTimeout = setTimeout(() => { barcodeBuffer = ''; }, 50);
        }
    });
}

async function processBarcodeScan(q, inpEl, sugEl) {
    try {
        let result = null;
        try {
            if (typeof OfflineDB !== 'undefined') {
                result = await OfflineDB.findByBarcode(q, true); // true for isPos
            }
            if (!result && navigator.onLine) {
                const resp = await fetch(`${BASE_URL}api/products/barcode/${encodeURIComponent(q)}?pos=1`);
                if (!resp.ok && resp.status === 503) throw new Error("Offline");
                result = await resp.json();
            }
        } catch (e) {
            console.error('Barcode error', e);
        }

        if (result && result.id) {
            addProductToCart(result);
            if (inpEl) inpEl.value = '';
            if (sugEl) sugEl.innerHTML = '';
        } else {
            // Fall back to text search if not found
            if (q.length >= 2 && inpEl) {
                inpEl.value = q;
                performSearch(q);
            } else {
                showToast('Barcode tidak ditemukan', 'warning');
            }
        }
    } catch (err) {
        if (q.length >= 2 && inpEl) {
            inpEl.value = q;
            performSearch(q);
        } else {
            showToast('Gagal memproses barcode', 'error');
        }
    }
}

async function performSearch(q) {
    const sug = document.getElementById('posSuggestions');
    if (!sug) return;
    
    sug.innerHTML = '<div style="padding:12px;text-align:center;color:#999;">Mencari...</div>';
    
    if (window.posSearchAbortController) {
        window.posSearchAbortController.abort();
    }
    window.posSearchAbortController = new AbortController();
    
    try {
        let items = [];
        try {
            if (typeof OfflineDB !== 'undefined') {
                items = await OfflineDB.searchProducts(q, true); // true for isPos
            }
            
            if ((!items || items.length === 0) && navigator.onLine) {
                const resp = await fetch(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}&pos=1`, { 
                    credentials: 'same-origin',
                    signal: window.posSearchAbortController.signal
                });
                if (!resp.ok) {
                    if (resp.status !== 503) {
                        sug.innerHTML = '<div style="padding:12px;text-align:center;color:#f59e0b;">Gagal memuat. Refresh halaman atau login ulang.</div>';
                        return;
                    }
                } else {
                    items = await resp.json();
                }
            }
        } catch (e) {
            if (e.name === 'AbortError') return;
            console.error('POS search fetch error', e);
        }
        
        const currentInput = document.getElementById('posSearch');
        if (!currentInput || currentInput.value.trim().length < 2) {
            sug.innerHTML = '';
            return;
        }

        if (!Array.isArray(items) || items.length === 0) {
            sug.innerHTML = '<div style="padding:12px;text-align:center;color:#999;">Tidak ada</div>';
            return;
        }
        
        sug.innerHTML = items.map(p => {
            const name = escapeHtml(p.short_label || p.full_name);
            const brand = escapeHtml(p.brand_name || '');
            
            // Photo or icon
            const thumbHtml = p.photo 
                ? `<div style="width:44px;height:44px;border-radius:var(--radius-sm);overflow:hidden;display:flex;align-items:center;justify-content:center;background:transparent;flex-shrink:0;">
                       <img src="${BASE_URL}${escapeHtml(p.photo)}" style="width:100%;height:100%;object-fit:contain;" loading="lazy">
                   </div>`
                : `<div style="width:44px;height:44px;background:var(--primary-bg);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--primary);flex-shrink:0;">
                       <i class="bi bi-box-seam" style="font-size:1.2rem;"></i>
                   </div>`;
            
            let priceText = '';
            if (p.packagings && p.packagings.length > 0) {
                const pkgsHtml = p.packagings.map(pkg => {
                    const price = saleMode === 'wholesale' 
                        ? (parseFloat(pkg.sell_price_wholesale) || parseFloat(pkg.sell_price_retail) || 0)
                        : (parseFloat(pkg.sell_price_retail) || 0);
                    return price > 0 ? `<div style="font-size:0.75rem; margin-top:2px;">${formatRupiah(price)} <span style="font-size:0.65rem; color:var(--text-muted);">/ ${escapeHtml(pkg.unit_name)}</span></div>` : '';
                }).join('');
                if (pkgsHtml) {
                    priceText = `<div style="font-weight:600; color:var(--primary); text-align:right;">${pkgsHtml}</div>`;
                }
            }

            return `
            <div data-id="${p.id}" style="padding:10px 12px; background:var(--surface-1); margin-bottom:6px; cursor:pointer; border:1px solid var(--border-color); border-radius:var(--radius-md); display:flex; align-items:flex-start; gap:10px; transition:all 0.2s; box-shadow:var(--shadow-sm);" onclick="selectProduct(${p.id})" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--surface-1)'">
                ${thumbHtml}
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:600; font-size:0.8rem; color:var(--text-primary); line-height:1.3; word-break:break-word; white-space:normal;">${name}</div>
                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:2px;">${brand ? brand : 'Tanpa Merek'}</div>
                </div>
                <div style="text-align:right;">
                    ${priceText}
                </div>
            </div>`;
        }).join('');
    } catch (e) {
        console.error('POS search error:', e);
        sug.innerHTML = '<div style="padding:12px;color:var(--danger);">Error pencarian. Coba lagi.</div>';
    }
}

async function selectProduct(id) {
    try {
        let data = null;
        try {
            if (typeof OfflineDB !== 'undefined') {
                data = await OfflineDB.getProductById(id);
            }
            if (!data && navigator.onLine) {
                const resp = await fetch(`${BASE_URL}api/products/${id}`, { credentials: 'same-origin' });
                if (!resp.ok) {
                    if (resp.status === 503) throw new Error("Offline");
                    let errMsg = 'Gagal memuat produk';
                    try { const errData = await resp.json(); errMsg = errData.error || errMsg; } catch(_){}
                    showToast(errMsg, 'error');
                    return;
                }
                data = await resp.json();
            }
        } catch (e) {
            console.error(e);
        }
        
        if (!data || !data.id) {
            showToast('Data produk tidak valid', 'error');
            return;
        }
        addProductToCart(data);
        document.getElementById('posSearch').value = '';
        document.getElementById('posSuggestions').innerHTML = '';
    } catch (e) {
        console.error('POS select product error:', e);
        showToast('Gagal memuat detail produk', 'error');
    }
}

function addProductToCart(product) {
    if (!product.packagings || product.packagings.length === 0) {
        showToast('Produk belum punya data kemasan/harga', 'warning');
        return;
    }

    const defaultLevel = 1;
    const selectedPkg = product.packagings.find(p => p.level == defaultLevel) || product.packagings[0];
    const printName = product.short_label || product.invoice_name || product.full_name;

    const existingIndex = cart.findIndex(i => i.product_id == product.id && i.level == selectedPkg.level && !i.use_custom_price);
    if (existingIndex > -1) {
        cart[existingIndex].quantity += 1;
        recalcItemPrice(cart[existingIndex]);
    } else {
        const newItem = {
            id: Date.now(),
            product_id: product.id,
            name: printName,
            print_name: printName,
            product_name: product.full_name,
            packagings: product.packagings,
            level: selectedPkg.level,
            unit_name: selectedPkg.unit_name,
            quantity: 1,
            use_custom_price: false,
            custom_line_total: null,
            custom_unit_price: null,
            unit_price: 0,
            total: 0,
            price_note: '',
        };
        recalcItemPrice(newItem);
        cart.unshift(newItem);
    }

    renderCart();
    searchInput?.focus();
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id == id);
    if (!item) return;
    item.quantity += delta;
    if (item.quantity <= 0) {
        cart = cart.filter(i => i.id != id);
    } else {
        if (item.use_custom_price && item.unit_price > 0) {
            item.custom_line_total = item.unit_price * item.quantity;
            item.custom_price_draft = String(item.custom_line_total);
        }
        recalcItemPrice(item);
    }
    renderCart();
}

function changeLevel(id, newLevel) {
    const item = cart.find(i => i.id == id);
    if (!item) return;
    const pkg = item.packagings.find(p => p.level == newLevel);
    if (pkg) {
        item.level = parseInt(newLevel, 10);
        item.unit_name = pkg.unit_name;
        item.use_custom_price = false;
        item.custom_line_total = null;
        item.custom_unit_price = null;
        recalcItemPrice(item);
        renderCart();
    }
}

function calculateTotal() {
    let sum = 0;
    let profit = 0;
    cart.forEach(i => {
        sum += i.total;
        const curPkg = i.packagings?.find(p => p.level == i.level);
        const buyPrice = parseFloat(curPkg?.buy_price) || 0;
        if (buyPrice > 0) {
            profit += i.total - (buyPrice * i.quantity);
        }
    });
    cartTotalEl.textContent = formatRupiah(sum);
    const cartProfitEl = document.getElementById('cartProfit');
    if (cartProfitEl) {
        cartProfitEl.textContent = profit > 0 ? `Estimasi Profit: ${formatRupiah(profit)}` : '';
    }
    cartCountEl.textContent = cart.length;
    btnCheckout.disabled = cart.length === 0;
    if (btnSaveDraft) btnSaveDraft.disabled = cart.length === 0;
    return sum;
}

function renderCart() {
    emptyState.style.display = cart.length === 0 ? 'block' : 'none';

    let html = '';
    cart.forEach(item => {
        let activeLabel = item.unit_name;
        let levelOptionsHTML = '';
        item.packagings.forEach(p => {
            if (p.level == item.level) activeLabel = p.unit_name;
            const isActive = (p.level == item.level);
            const activeStyle = isActive ? 'background:var(--primary); color:#fff; font-weight:600;' : 'color:var(--text-primary);';
            levelOptionsHTML += `<li><a class="dropdown-item" href="javascript:void(0)" onclick="changeLevel(${item.id}, ${p.level})" style="padding:8px 16px; font-size:0.85rem; transition:0.2s; ${activeStyle}" onmouseover="if(!${isActive}){this.style.background='var(--surface-3)'}" onmouseout="if(!${isActive}){this.style.background='transparent'}">${escapeHtml(p.unit_name)}</a></li>`;
        });

        const customChecked = item.use_custom_price ? 'checked' : '';
        const customWrapStyle = item.use_custom_price ? '' : 'opacity:0.55;pointer-events:none;';
        const customPriceVal = item.use_custom_price
            ? (item.custom_price_draft !== undefined ? item.custom_price_draft : (item.custom_line_total ?? item.total ?? ''))
            : '';
        const noteBlock = item.price_note
            ? `<div class="cart-item-note" style="font-size:var(--font-size-xs);color:var(--info);margin-top:3px;">${escapeHtml(item.price_note)}</div>`
            : `<div class="cart-item-note" style="display:none;"></div>`;

        // Harga modal samar & profit item
        const curPkg = item.packagings?.find(p => p.level == item.level);
        const buyPrice = parseFloat(curPkg?.buy_price) || 0;
        const profitItem = buyPrice > 0 ? item.total - (buyPrice * item.quantity) : 0;
        const buyPriceBlock = buyPrice > 0
            ? `<div style="font-size:9px;color:rgba(255,255,255,0.18);margin-top:2px;letter-spacing:0.3px;user-select:none;">M: ${formatRupiah(buyPrice)}/${escapeHtml(item.unit_name)} &middot; P: ${formatRupiah(profitItem)}</div>`
            : '';

        html += `
            <div data-cart-id="${item.id}" style="background:var(--surface-1);border-radius:var(--radius-md);padding:14px;margin-bottom:10px;border:1px solid var(--border-color);">
                <div style="display:grid;grid-template-columns:1fr auto;gap:12px;margin-bottom:10px;">
                    <div style="min-width:0;">
                        <div style="font-weight:600;font-size:0.95rem;margin-bottom:3px;line-height:1.3;color:var(--text-primary);">${escapeHtml(item.name)}</div>
                        <div class="cart-item-unit-price" style="color:var(--text-muted);font-size:0.85rem;">${item.use_custom_price ? `${formatRupiah(item.unit_price)} / ${escapeHtml(item.unit_name)} (Total ${formatRupiah(item.total)})` : `${formatRupiah(item.unit_price)} / ${escapeHtml(item.unit_name)}`}</div>
                        ${noteBlock}
                        ${buyPriceBlock}
                    </div>
                    <div class="cart-item-total" style="font-weight:700;font-size:1rem;text-align:right;color:var(--primary);">${formatRupiah(item.total)}</div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;background:var(--surface-2);padding:8px 10px;border-radius:var(--radius-md);border:1px solid var(--border-color);">
                    <div class="dropdown" style="flex:1;min-width:110px;">
                        <button type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%;font-weight:600;color:var(--primary);background-color:var(--primary-bg);border:1px solid rgba(230,57,70,0.2);border-radius:var(--radius-sm);padding:8px 28px 8px 12px;text-align:left;font-size:11px;background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23e63946' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E&quot;);background-repeat:no-repeat;background-position:right 10px center;background-size:10px;cursor:pointer;">
                            ${escapeHtml(activeLabel)}
                        </button>
                        <ul class="dropdown-menu shadow-lg" style="background:var(--surface-2); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:4px 0; min-width:100%; margin-top:4px;">
                            ${levelOptionsHTML}
                        </ul>
                    </div>
                    <div style="display:flex;align-items:center;background:var(--surface-1);border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border-color);box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                        <button type="button" onclick="updateQty(${item.id}, -1)" style="border:none;background:none;color:var(--text-secondary);padding:6px 12px;cursor:pointer;transition:all 150ms;"><i class="bi bi-dash"></i></button>
                        <span style="font-weight:700;width:32px;text-align:center;font-size:0.95rem;color:var(--text-primary);">${item.quantity}</span>
                        <button type="button" onclick="updateQty(${item.id}, 1)" style="border:none;background:none;color:var(--primary);padding:6px 12px;cursor:pointer;transition:all 150ms;"><i class="bi bi-plus"></i></button>
                    </div>
                    <button type="button" onclick="cart = cart.filter(i => i.id != ${item.id}); renderCart();" style="border:1px solid rgba(230,57,70,0.2);background:var(--danger-bg);color:var(--danger);cursor:pointer;padding:6px 12px;border-radius:var(--radius-sm);transition:all 150ms;"><i class="bi bi-trash3"></i></button>
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:11px;color:var(--text-secondary);cursor:pointer;user-select:none;font-weight:500;">
                    <input type="checkbox" ${customChecked} onchange="togglePosCustomPrice(${item.id}, this.checked)" style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;margin:0;">
                    <span><i class="bi bi-pencil-square" style="margin-right:2px;"></i> Terapkan Harga Custom</span>
                </label>
                <div style="margin-top:8px;${customWrapStyle}">
                    <input type="number" min="0" step="1" value="${customPriceVal}" placeholder="Total harga untuk ${item.quantity} ${escapeHtml(item.unit_name)}"
                        oninput="onPosCustomPriceInput(${item.id}, this)"
                        class="form-control-dark cart-custom-price-input" style="width:100%;font-size:0.85rem;padding:8px 10px;border:1px solid var(--border-color);border-radius:var(--radius-sm);background:var(--bg-input);">
                    <div class="cart-custom-markup" style="font-size:0.75rem; margin-top:6px; font-weight:600; background:var(--surface-2); padding:6px 8px; border-radius:4px; border:1px solid var(--border-color); display:none;"></div>
                </div>
            </div>
        `;
    });

    if (cart.length > 0) {
        cartContainer.innerHTML = html;
        cartContainer.appendChild(emptyState);
    } else {
        cartContainer.innerHTML = '';
        cartContainer.appendChild(emptyState);
    }
    
    // Apply dynamic updates (markup etc)
    cart.forEach(item => updateCartItemDom(item));

    calculateTotal();
    autoSaveCart();
}

// Auto-save cart to prevent data loss
function autoSaveCart() {
    try {
        localStorage.setItem('pos_autosave', JSON.stringify({ cart, saleMode, ts: Date.now() }));
    } catch(e) {}
}
function autoRestoreCart() {
    try {
        const saved = JSON.parse(localStorage.getItem('pos_autosave') || 'null');
        if (saved && saved.cart && saved.cart.length > 0) {
            // Only restore if saved within last 12 hours
            if (Date.now() - saved.ts < 12 * 60 * 60 * 1000) {
                cart = saved.cart;
                if (saved.saleMode) setSaleMode(saved.saleMode);
                cart.forEach(it => recalcItemPrice(it));
                showToast(`${cart.length} item direstorasi dari sesi sebelumnya`, 'info');
            }
        }
    } catch(e) {}
}
function clearAutoSave() {
    try { localStorage.removeItem('pos_autosave'); } catch(e) {}
}

function saveDraft() {
    if (cart.length === 0) return;
    const drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
    const defaultName = currentDraftId ? (drafts.find(d => d.id === currentDraftId)?.name || '') : '';

    AppModal.show({
        title: 'Simpan Draft',
        subtitle: `${cart.length} item · ${formatRupiah(calculateTotal())}`,
        icon: 'bi-journal-bookmark',
        iconColor: 'var(--primary-bg)',
        iconAccent: 'var(--primary)',
        bodyHTML: `
            <div class="modal-form-group">
                <label>Nama / Keterangan Draft</label>
                <input type="text" class="form-control-dark" id="draftNameInput" value="${escapeHtml(defaultName)}" 
                       placeholder="Cth: Pesanan Bu Ani, Grosir Warung Pak RT..." autocomplete="off">
            </div>
            <div style="background:var(--surface-2);border-radius:var(--radius-sm);padding:10px 12px;font-size:var(--font-size-xs);color:var(--text-muted);margin-top:8px;">
                <i class="bi bi-info-circle"></i> Draft akan tersimpan di perangkat ini. Keranjang saat ini akan dikosongkan setelah disimpan.
            </div>
        `,
        submitText: 'Simpan Draft',
        onSubmit: async () => {
            const draftName = document.getElementById('draftNameInput')?.value?.trim() || '';
            const draft = {
                id: currentDraftId || Date.now(),
                name: draftName || `Draft ${new Date().toLocaleTimeString('id-ID')}`,
                date: new Date().toISOString(),
                saleMode,
                total: calculateTotal(),
                cart: [...cart]
            };
            if (currentDraftId) {
                const idx = drafts.findIndex(d => d.id === currentDraftId);
                if (idx > -1) drafts[idx] = draft; else drafts.push(draft);
            } else { drafts.push(draft); }
            localStorage.setItem('pos_drafts', JSON.stringify(drafts));
            showToast('Draft berhasil disimpan', 'success');
            cart = [];
            currentDraftId = null;
            clearAutoSave();
            renderCart();
            return true;
        }
    });

    setTimeout(() => {
        const submitBtn = document.getElementById('appModalSubmitBtn');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="bi bi-save"></i> Simpan Draft';
        }
    }, 50);
}

function openDrafts() {
    const drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
    if (drafts.length === 0) {
        showToast('Tidak ada draft tersimpan', 'info');
        return;
    }

    const listHtml = drafts.map(d => {
        const total = d.total || d.cart.reduce((s, i) => s + (i.total || 0), 0);
        return `
        <div style="background:var(--surface-2);border-radius:var(--radius-lg);padding:14px;margin-bottom:10px;border:1px solid var(--border-color);transition:all 0.2s;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:var(--font-size-sm);color:var(--text-primary);margin-bottom:4px;">${escapeHtml(d.name)}</div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <span style="font-size:var(--font-size-xs);color:var(--text-muted);"><i class="bi bi-clock"></i> ${new Date(d.date).toLocaleString('id-ID', {day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'})}</span>
                        <span style="display:inline-flex;align-items:center;gap:3px;background:${d.saleMode === 'retail' ? 'var(--info-bg)' : 'var(--warning-bg)'};color:${d.saleMode === 'retail' ? 'var(--info)' : 'var(--warning)'};padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;">${d.saleMode === 'retail' ? 'Ecer' : 'Grosir'}</span>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:800;font-size:var(--font-size-base);color:var(--primary);">${formatRupiah(total)}</div>
                    <div style="font-size:10px;color:var(--text-muted);">${d.cart.length} item</div>
                </div>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" onclick="loadDraft(${d.id})" class="btn-primary-custom" style="flex:1;padding:10px;font-size:var(--font-size-xs);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;gap:6px;font-weight:600;border:none;cursor:pointer;">
                    <i class="bi bi-box-arrow-in-right"></i> Muat Draft
                </button>
                <button type="button" onclick="deleteDraft(${d.id})" style="padding:10px 14px;background:var(--danger-bg);color:var(--danger);border:none;border-radius:var(--radius-md);cursor:pointer;font-size:var(--font-size-xs);display:flex;align-items:center;gap:4px;">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>`;
    }).join('');

    AppModal.show({
        title: 'Draft Tersimpan',
        subtitle: `${drafts.length} draft tersedia`,
        icon: 'bi-journal-bookmark',
        iconColor: 'var(--primary-bg)',
        iconAccent: 'var(--primary)',
        bodyHTML: `<div id="draftListContainer" style="max-height:55vh;overflow-y:auto;">${listHtml}</div>`,
        hideFooter: true,
        cancelText: 'Tutup'
    });
}

window.loadDraft = function(id) {
    if (cart.length > 0 && !confirm('Keranjang saat ini tidak kosong. Timpa dengan draft?')) return;
    
    const drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
    const d = drafts.find(x => x.id === id);
    if (!d) return;

    cart = d.cart.map(it => ({
        ...it,
        use_custom_price: !!it.use_custom_price,
        custom_line_total: it.custom_line_total ?? null,
        custom_unit_price: it.custom_unit_price ?? null,
        price_note: it.price_note || '',
    }));
    setSaleMode(d.saleMode);
    currentDraftId = d.id;
    cart.forEach(it => recalcItemPrice(it));
    renderCart();
    AppModal.close();
    showToast('Draft dimuat', 'success');
};

window.deleteDraft = function(id) {
    if (!confirm('Hapus draft ini?')) return;
    let drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
    drafts = drafts.filter(x => x.id !== id);
    localStorage.setItem('pos_drafts', JSON.stringify(drafts));
    
    if (currentDraftId === id) currentDraftId = null;
    AppModal.close();
    setTimeout(() => { if(drafts.length > 0) openDrafts(); }, 300);
    showToast('Draft dihapus', 'success');
};

function checkout() {
    if (cart.length === 0) return;

    let hasZeroPrice = false;
    let hasLowPrice = false;

    for (let i = 0; i < cart.length; i++) {
        if (cart[i].total === 0) {
            hasZeroPrice = true;
        } else if (cart[i].total < 500) {
            hasLowPrice = true;
        }
    }

    if (hasZeroPrice) {
        AppModal.show({
            title: 'Harga Tidak Valid',
            subtitle: 'Pengecekan Harga Keranjang',
            icon: 'bi-exclamation-triangle',
            iconColor: 'var(--danger-bg)',
            iconAccent: 'var(--danger)',
            bodyHTML: '<div style="text-align:center; padding:10px 0;"><i class="bi bi-exclamation-circle" style="font-size:3rem; color:var(--danger); display:block; margin-bottom:12px;"></i><p style="font-size:14px; margin-bottom:8px;">Terdapat produk dengan total harga <strong style="color:var(--danger)">Rp0</strong> di keranjang.</p><p style="font-size:13px; color:var(--text-muted);">Mohon perbaiki harga produk tersebut sebelum melanjutkan pembayaran.</p></div>',
            hideFooter: true,
            cancelText: 'Tutup & Perbaiki'
        });
        return;
    }

    if (hasLowPrice) {
        AppModal.show({
            title: 'Konfirmasi Harga',
            subtitle: 'Peringatan Harga Terlalu Rendah',
            icon: 'bi-info-circle',
            iconColor: 'var(--warning-bg)',
            iconAccent: 'var(--warning)',
            bodyHTML: '<div style="text-align:center; padding:10px 0;"><i class="bi bi-question-circle" style="font-size:3rem; color:var(--warning); display:block; margin-bottom:12px;"></i><p style="font-size:14px; margin-bottom:8px;">Terdapat produk dengan total harga <strong>kurang dari Rp500</strong>.</p><p style="font-size:13px; color:var(--text-muted);">Apakah Anda yakin nominal harga tersebut sudah benar?</p></div>',
            submitText: 'Ya, Lanjutkan',
            cancelText: 'Batal',
            onSubmit: async () => {
                proceedCheckout();
                return true;
            }
        });
        return;
    }

    proceedCheckout();
}

async function proceedCheckout() {
    btnCheckout.innerHTML = '<i class="spinner-border spinner-border-sm"></i> MEMPROSES...';
    btnCheckout.disabled = true;

    const payload = {
        csrf_token: document.getElementById('csrfToken')?.value || '',
        sale_mode: saleMode,
        total_amount: calculateTotal(),
        payment_method: 'Cash',
        payment_status: 'Lunas',
        customer_id: selectedCustomer ? selectedCustomer.id : null,
        items: cart.map(i => ({
            product_id: i.product_id,
            level: i.level,
            quantity: i.quantity,
            unit_price: i.unit_price,
            is_custom: !!i.is_custom,
            custom_name: i.is_custom ? i.name : null,
            custom_unit: i.is_custom ? i.unit_name : null,
        })),
    };

    try {
        const endpoint = editSaleId ? `${BASE_URL}api/sales/update/${editSaleId}` : `${BASE_URL}api/sales`;
        const result = await api(endpoint, 'POST', payload);

        if (result.success) {
            showToast('Transaksi Berhasil!', 'success');

            const printCart = cart.map(i => ({ ...i }));
            const printTotal = calculateTotal();
            const invoiceNo = result.invoice || result.id || ('OFF-' + Date.now());

            // Clear current draft if checkout success
            if (currentDraftId) {
                let drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
                drafts = drafts.filter(x => x.id !== currentDraftId);
                localStorage.setItem('pos_drafts', JSON.stringify(drafts));
            }

            cart = [];
            currentDraftId = null;
            if (editSaleId) editSaleId = null;
            clearAutoSave();
            renderCart();
            btnCheckout.innerHTML = 'BAYAR SEKARANG';
            btnCheckout.disabled = false;

            // Remove edit param from URL silently
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('edit');
                window.history.replaceState(null, '', url);
            }

            const tp = getThermalPrinterSafe();
            if (STORE_SETTINGS && tp?.setStoreSettings) {
                tp.setStoreSettings(STORE_SETTINGS);
            }

            // Build invoice items HTML
            const invoiceItemsHTML = printCart.map(item => `
                <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:10px 0;border-bottom:1px solid var(--border-color);">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:var(--font-size-sm);color:var(--text-primary);margin-bottom:2px;">${escapeHtml(item.print_name || item.name)}</div>
                        <div style="font-size:var(--font-size-xs);color:var(--text-muted);">${item.quantity} ${escapeHtml(item.unit_name)} × ${formatRupiah(item.unit_price)}</div>
                    </div>
                    <div style="font-weight:700;font-size:var(--font-size-sm);color:var(--text-primary);white-space:nowrap;margin-left:12px;">${formatRupiah(item.total)}</div>
                </div>
            `).join('');

            const printerConnected = tp?.isConnected?.() ?? false;

            let modalPromise;
            try {
            modalPromise = AppModal.show({
                title: 'Transaksi Berhasil',
                subtitle: `No: ${invoiceNo}`,
                icon: 'bi-check-circle',
                iconColor: 'var(--success-bg)',
                iconAccent: 'var(--success)',
                bodyHTML: `
                    <div style="text-align:center;margin-bottom:16px;">
                        <div style="width:56px;height:56px;background:var(--success-bg);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="bi bi-check-lg" style="font-size:1.8rem;color:var(--success);"></i>
                        </div>
                        <h2 style="font-size:1.75rem;font-weight:800;color:var(--text-primary);margin:0 0 4px;">${formatRupiah(printTotal)}</h2>
                        <div style="display:inline-flex;align-items:center;gap:6px;background:var(--surface-2);padding:4px 12px;border-radius:20px;font-size:var(--font-size-xs);color:var(--text-muted);">
                            <i class="bi bi-credit-card"></i> Tunai · ${saleMode === 'retail' ? 'Ecer' : 'Grosir'}
                        </div>
                    </div>

                    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:14px;margin-bottom:16px;max-height:220px;overflow-y:auto;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid var(--border-color);">
                            <span style="font-size:var(--font-size-xs);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Invoice</span>
                            <span style="font-size:var(--font-size-xs);color:var(--text-muted);font-family:monospace;">${invoiceNo}</span>
                        </div>
                        ${invoiceItemsHTML}
                        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:10px;margin-top:4px;">
                            <span style="font-weight:700;font-size:var(--font-size-sm);">Total</span>
                            <span style="font-weight:800;font-size:var(--font-size-md);color:var(--primary);">${formatRupiah(printTotal)}</span>
                        </div>
                    </div>

                    <div id="printerSection" style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:16px;">
                        <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:14px;">
                            <div style="width:40px;height:40px;background:var(--info-bg);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-printer" style="color:var(--info);font-size:1.2rem;"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:var(--font-size-sm);color:var(--text-primary);">Cetak Struk</div>
                                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:2px;">Printer Thermal Bluetooth 58mm</div>
                            </div>
                        </div>

                        <div id="printerStatusBar" style="display:none;padding:10px 12px;border-radius:var(--radius-sm);font-size:var(--font-size-xs);margin-bottom:12px;align-items:center;gap:6px;font-weight:600;">
                            <i class="bi bi-check-circle-fill"></i> <span id="printerStatusText">Printer terhubung</span>
                        </div>

                        <button type="button" id="btnConnectPrinter" class="btn-outline-custom" style="width:100%;padding:14px;font-weight:600;display:${printerConnected ? 'none' : 'flex'};align-items:center;justify-content:center;gap:8px;font-size:var(--font-size-sm);border-radius:var(--radius-md);border:1px solid var(--border-color);cursor:pointer;transition:all 0.2s;">
                            <i class="bi bi-bluetooth"></i> Hubungkan Printer Bluetooth
                        </button>
                        <button type="button" id="btnPrintReceipt" class="btn-primary-custom" style="width:100%;padding:14px;font-weight:600;display:${printerConnected ? 'flex' : 'none'};align-items:center;justify-content:center;gap:8px;font-size:var(--font-size-sm);border-radius:var(--radius-md);border:none;cursor:pointer;transition:all 0.2s;">
                            <i class="bi bi-printer"></i> Cetak Struk (Bluetooth)
                        </button>
                        <button type="button" id="btnPrintBrowser" class="btn-outline-custom" style="width:100%;padding:14px;margin-top:8px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;font-size:var(--font-size-sm);border-radius:var(--radius-md);border:1px dashed var(--border-color);cursor:pointer;transition:all 0.2s;">
                            <i class="bi bi-window"></i> Cetak Web / AirPrint
                        </button>
                        <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:10px;padding-top:10px;border-top:1px solid var(--border-color);">
                            <i class="bi bi-info-circle"></i> Gunakan Cetak Web jika di iOS atau Bluetooth gagal
                        </div>
                    </div>
                `,
                submitText: 'Transaksi Baru',
                cancelText: 'Tutup',
                onSubmit: async () => {
                    searchInput?.focus();
                    return true;
                },
            });

            setTimeout(() => setupPrinterButtons(printCart, printTotal, invoiceNo), 250);
            await modalPromise;
            } catch (modalErr) {
                console.error('Checkout success UI error:', modalErr);
                showToast(`Transaksi berhasil (${invoiceNo})`, 'success');
            }
        } else {
            showToast(result.error || 'Gagal menyimpan', 'error');
            btnCheckout.innerHTML = 'BAYAR SEKARANG';
            btnCheckout.disabled = false;
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
        btnCheckout.innerHTML = 'BAYAR SEKARANG';
        btnCheckout.disabled = false;
    }
}

function setupPrinterButtons(printCart, printTotal, invoiceNo) {
    const tp = getThermalPrinterSafe();
    const btnConnect = document.getElementById('btnConnectPrinter');
    const btnPrint = document.getElementById('btnPrintReceipt');
    const btnBrowser = document.getElementById('btnPrintBrowser');
    const statusBar = document.getElementById('printerStatusBar');

    if (!btnConnect || !btnPrint || !btnBrowser) {
        console.error('[POS] Printer buttons not found in DOM');
        return;
    }

    if (!tp) {
        console.error('[POS] ThermalPrinter instance not found');
        btnConnect.onclick = () => showToast('Komponen printer gagal dimuat. Muat ulang halaman.', 'warning');
        btnPrint.onclick = () => showToast('Komponen printer gagal dimuat. Muat ulang halaman.', 'warning');
        btnBrowser.onclick = () => showToast('Komponen printer gagal dimuat. Muat ulang halaman.', 'warning');
        return;
    }
    
    btnBrowser.onclick = async () => {
        try {
            btnBrowser.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyiapkan...';
            btnBrowser.disabled = true;
            await tp.printBrowser(printCart, printTotal, invoiceNo, {
                storeSettings: STORE_SETTINGS,
                paymentMethod: 'Tunai',
            });
            btnBrowser.innerHTML = '<i class="bi bi-window"></i> Cetak Web / AirPrint';
            btnBrowser.disabled = false;
        } catch (e) {
            btnBrowser.innerHTML = '<i class="bi bi-window"></i> Cetak Web / AirPrint';
            btnBrowser.disabled = false;
            console.error('[POS] Print Browser error:', e);
            showToast(e.message || 'Gagal mencetak struk via web', 'error');
        }
    };

    function showConnected(deviceName) {
        btnConnect.style.display = 'none';
        btnPrint.style.display = 'flex';
        
        const btnNew = document.getElementById('btnConnectNewPrinter');
        if (btnNew) btnNew.style.display = 'none';

        if (statusBar) {
            statusBar.style.display = 'flex';
            statusBar.style.justifyContent = 'space-between';
            statusBar.style.alignItems = 'center';
            statusBar.style.background = 'var(--success-bg)';
            statusBar.style.color = 'var(--success)';
            statusBar.innerHTML = `
                <div><i class="bi bi-check-circle-fill"></i> <span>Printer terhubung${deviceName ? ': ' + deviceName : ''}</span></div>
                <button type="button" class="btn-outline-custom" style="padding:4px 8px;font-size:10px;border-color:var(--danger);color:var(--danger);" onclick="disconnectPrinter()">Putuskan</button>
            `;
        }
    }

    window.disconnectPrinter = function() {
        if (tp) {
            tp.disconnect();
            tp.clearLastDevice();
            showDisconnected(false);
            setupConnectButton();
            showToast('Printer telah diputuskan', 'info');
        }
    };

    function showDisconnected(hasSaved = false) {
        btnConnect.style.display = 'flex';
        btnConnect.innerHTML = hasSaved ? '<i class="bi bi-arrow-clockwise"></i> Hubungkan ke Printer Tersimpan' : '<i class="bi bi-bluetooth"></i> Hubungkan Printer Bluetooth';
        btnConnect.disabled = false;
        btnPrint.style.display = 'none';
        if (statusBar) statusBar.style.display = 'none';

        const btnNew = document.getElementById('btnConnectNewPrinter');
        if (btnNew) btnNew.style.display = 'none';
    }

    function setupConnectButton() {
        btnConnect.onclick = async () => {
            try {
                btnConnect.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menghubungkan...';
                btnConnect.disabled = true;
                let connected = false;
                await tp.connect();
                connected = tp.isConnected();
                if (!connected) {
                    showDisconnected(tp.hasSavedDevice());
                    setupConnectButton();
                    return;
                }
                showConnected(tp.device?.name);
                showToast('Printer Bluetooth terhubung dengan baik', 'success');
                setupPrintButton();
            } catch (e) {
                showDisconnected(tp.hasSavedDevice());
                console.error('[POS] Printer connection error:', e);
                const errMsg = e.message || 'Gagal menghubungkan printer';
                showToast(errMsg, 'error');
                setupConnectButton();
            }
        };
    }

    function setupPrintButton() {
        btnPrint.onclick = async () => {
            try {
                btnPrint.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Mencetak...';
                btnPrint.disabled = true;

                // If soft-disconnected, reconnect silently before printing
                if (!tp.isConnected()) {
                    btnPrint.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menghubungkan ulang...';
                    const ok = await tp.tryAutoReconnect();
                    if (!ok) {
                        showDisconnected(tp.hasSavedDevice());
                        setupConnectButton();
                        showToast('Printer terputus. Silakan hubungkan ulang.', 'error');
                        return;
                    }
                    showConnected(tp.device?.name);
                }

                await tp.print(printCart, printTotal, invoiceNo, {
                    storeSettings: STORE_SETTINGS,
                    paymentMethod: 'Tunai',
                });
                btnPrint.innerHTML = '<i class="bi bi-printer"></i> Cetak Ulang';
                btnPrint.disabled = false;
                showToast('Struk berhasil dicetak ke printer thermal', 'success');
                showHistorySaveConfirmation(invoiceNo, printTotal, printCart);
            } catch (e) {
                btnPrint.innerHTML = '<i class="bi bi-printer"></i> Cetak Struk';
                btnPrint.disabled = false;
                console.error('[POS] Print error:', e);
                showToast(e.message || 'Gagal mencetak struk', 'error');
                if (!tp.isConnected()) {
                    showDisconnected(tp.hasSavedDevice());
                    setupConnectButton();
                }
            }
        };
    }

    if (tp.isConnected()) {
        showConnected(tp.device?.name);
        setupPrintButton();
        return;
    }

    if (tp.device || tp.hasSavedDevice()) {
        btnConnect.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menghubungkan kembali...';
        btnConnect.disabled = true;
        
        tp.tryAutoReconnect()
            .then(success => {
                if (success) {
                    showConnected(tp.device?.name);
                    showToast('Printer terhubung kembali', 'success');
                    setupPrintButton();
                } else {
                    showDisconnected(tp.hasSavedDevice());
                    setupConnectButton();
                }
            })
            .catch((err) => {
                console.error('[POS] Auto-reconnect error:', err);
                showDisconnected(tp.hasSavedDevice());
                setupConnectButton();
            });
    } else {
        showDisconnected(false);
        setupConnectButton();
    }
}

function showHistorySaveConfirmation(invoiceNo, total, cartItems) {
    // Close the checkout modal first, then show history confirmation
    AppModal.close();
    setTimeout(() => {
        AppModal.show({
            title: 'Simpan Riwayat Transaksi',
            subtitle: `Invoice: ${invoiceNo}`,
            icon: 'bi-clock-history',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            bodyHTML: `
                <div style="text-align:center;margin-bottom:16px;">
                    <div style="width:56px;height:56px;background:var(--success-bg);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <i class="bi bi-check-lg" style="font-size:1.8rem;color:var(--success);"></i>
                    </div>
                    <h3 style="font-size:var(--font-size-md);font-weight:700;color:var(--text-primary);margin:0 0 4px;">Struk Berhasil Dicetak!</h3>
                    <p style="font-size:var(--font-size-sm);color:var(--text-muted);margin:0;">Total: <strong style="color:var(--primary);">${formatRupiah(total)}</strong></p>
                </div>
                <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:14px;margin-bottom:12px;">
                    <div style="display:flex;align-items:flex-start;gap:10px;">
                        <div style="width:36px;height:36px;background:var(--info-bg);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-database-check" style="color:var(--info);font-size:1rem;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;font-size:var(--font-size-sm);color:var(--text-primary);margin-bottom:4px;">Riwayat Otomatis Tersimpan</div>
                            <div style="font-size:var(--font-size-xs);color:var(--text-muted);line-height:1.5;">
                                Transaksi <strong style="font-family:monospace;color:var(--info);">${invoiceNo}</strong> telah tersimpan di database.
                                Anda bisa mencetak ulang struk ini kapan saja melalui menu <strong>Penjualan</strong>.
                            </div>
                        </div>
                    </div>
                </div>
                <div style="background:var(--surface-2);border-radius:var(--radius-sm);padding:10px 12px;font-size:var(--font-size-xs);color:var(--text-muted);">
                    <i class="bi bi-info-circle"></i> ${cartItems.length} item · ${new Date().toLocaleString('id-ID', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})}
                </div>
            `,
            submitText: 'Lihat Riwayat Penjualan',
            cancelText: 'Transaksi Baru',
            onSubmit: async () => {
                window.location.href = `${BASE_URL}sales`;
                return true;
            },
        });
    }, 400);
}

window.openCustomProductModal = function() {
    AppModal.show({
        title: 'Input Barang Custom',
        subtitle: 'Untuk barang tidak terdaftar di database',
        icon: 'bi-plus-circle',
        bodyHTML: `
            <div style="display:flex;flex-direction:column;gap:14px;">
                <div class="modal-form-group">
                    <label style="font-weight:600;font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:6px;">Nama Barang *</label>
                    <input type="text" id="customItemName" class="form-control-dark" placeholder="Cth: Fotokopi Kertas, Jasa Service..." required style="width:100%;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                    <div class="modal-form-group">
                        <label style="font-weight:600;font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:6px;">Qty *</label>
                        <input type="number" id="customItemQty" min="0.01" step="any" value="1" class="form-control-dark" required style="width:100%;">
                    </div>
                    <div class="modal-form-group">
                        <label style="font-weight:600;font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:6px;">Satuan *</label>
                        <input type="hidden" id="customItemUnit">
                        <div style="position:relative;">
                            <input type="text" id="customItemUnitSearch" class="form-control-dark"
                                   placeholder="Pilih satuan..." autocomplete="off" readonly
                                   style="width:100%;cursor:pointer;">
                            <div id="customItemUnitDropdown"
                                 style="position:absolute;top:100%;left:0;right:0;margin-top:2px;
                                        background:var(--surface-2);border:1px solid var(--border-color);
                                        border-radius:var(--radius-sm);max-height:160px;overflow-y:auto;
                                        z-index:9999;display:none;box-shadow:0 4px 16px rgba(0,0,0,0.35);">
                            </div>
                        </div>
                    </div>
                    <div class="modal-form-group">
                        <label style="font-weight:600;font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:6px;">Total Harga (Rp) *</label>
                        <input type="number" id="customItemTotalPrice" min="1" step="any" class="form-control-dark" placeholder="Cth: 5000" required style="width:100%;">
                    </div>
                </div>
                <div style="background:var(--info-bg);border-left:3px solid var(--info);padding:10px 12px;border-radius:4px;font-size:12px;color:var(--text-primary);">
                    <strong style="color:var(--info);">💡 Tips:</strong> Barang custom tidak mengurangi stok produk manapun dan tercatat sebagai item terpisah di laporan.
                </div>
            </div>
        `,
        submitText: 'Tambah ke Keranjang',
        onSubmit: async () => {
            const name = document.getElementById('customItemName')?.value?.trim();
            const qty = parseFloat(document.getElementById('customItemQty')?.value) || 1;
            const unit = document.getElementById('customItemUnit')?.value?.trim();
            const totalPrice = parseFloat(document.getElementById('customItemTotalPrice')?.value);

            if (!name) { showToast('Nama barang wajib diisi', 'warning'); return false; }
            if (isNaN(qty) || qty <= 0) { showToast('Quantity harus minimal 0.01', 'warning'); return false; }
            if (!unit) { showToast('Satuan wajib dipilih dari daftar', 'warning'); return false; }
            if (!Number.isFinite(totalPrice) || totalPrice <= 0) { showToast('Total harga harus lebih dari 0', 'warning'); return false; }

            addCustomProductToCart(name, qty, unit, totalPrice);
            return true;
        }
    });

    // Inisialisasi unit searchbox setelah DOM modal siap
    setTimeout(async () => {
        // Manually set submit button icon to bypass potential service worker caching of components.js
        const submitBtn = document.getElementById('appModalSubmitBtn');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Tambah ke Keranjang';
        }

        const searchEl  = document.getElementById('customItemUnitSearch');
        const hiddenEl  = document.getElementById('customItemUnit');
        const dropEl    = document.getElementById('customItemUnitDropdown');
        if (!searchEl || !hiddenEl || !dropEl) return;

        // Fetch master data satuan
        let units = [];
        try {
            const resp = await fetch(`${BASE_URL}api/units`, { credentials: 'same-origin' });
            const data = await resp.json();
            units = Array.isArray(data) ? data : [];
        } catch(e) { units = []; }

        function renderUnitDropdown(filtered) {
            if (filtered.length === 0) {
                dropEl.innerHTML = '<div style="padding:9px 12px;color:var(--text-muted);font-size:12px;text-align:center;">Tidak ada satuan ditemukan</div>';
            } else {
                dropEl.innerHTML = filtered.map(u => `
                    <div data-unit-name="${escapeHtml(u.name)}"
                         style="padding:9px 12px;cursor:pointer;font-size:13px;
                                color:var(--text-primary);display:flex;align-items:center;
                                gap:8px;border-bottom:1px solid var(--border-color);transition:background 0.12s;">
                        <i class="bi bi-rulers" style="color:var(--success);font-size:11px;flex-shrink:0;"></i>
                        <span>${escapeHtml(u.name)}</span>
                        ${u.abbreviation ? `<span style="color:var(--text-muted);font-size:11px;margin-left:auto;">${escapeHtml(u.abbreviation)}</span>` : ''}
                    </div>
                `).join('');

                dropEl.querySelectorAll('[data-unit-name]').forEach(el => {
                    el.addEventListener('mouseover', () => el.style.background = 'var(--surface-1)');
                    el.addEventListener('mouseout',  () => el.style.background = '');
                    el.addEventListener('mousedown', (e) => {
                        e.preventDefault(); // Jangan trigger blur dulu
                        const name = el.getAttribute('data-unit-name');
                        searchEl.value = name;
                        hiddenEl.value = name;
                        dropEl.style.display = 'none';
                        document.getElementById('customItemTotalPrice')?.focus();
                    });
                });
            }
            dropEl.style.display = 'block';
        }

        // Default ke "Pcs" atau unit pertama
        const defaultUnit = units.find(u => u.name.toLowerCase() === 'pcs') || units[0];
        if (defaultUnit) {
            searchEl.value = defaultUnit.name;
            hiddenEl.value = defaultUnit.name;
        }

        // Aktifkan typing untuk filter
        searchEl.removeAttribute('readonly');
        searchEl.style.cursor = '';

        searchEl.addEventListener('focus', () => {
            const q = searchEl.value.trim().toLowerCase();
            renderUnitDropdown(units.filter(u => u.name.toLowerCase().includes(q)));
        });

        searchEl.addEventListener('input', () => {
            hiddenEl.value = ''; // reset sampai user pilih
            const q = searchEl.value.trim().toLowerCase();
            renderUnitDropdown(units.filter(u => u.name.toLowerCase().includes(q)));
        });

        searchEl.addEventListener('blur', () => {
            setTimeout(() => { dropEl.style.display = 'none'; }, 180);
            // Jika value persis cocok dengan salah satu unit, konfirmasi pilihan
            const q = searchEl.value.trim().toLowerCase();
            const match = units.find(u => u.name.toLowerCase() === q);
            if (match) {
                hiddenEl.value = match.name;
                searchEl.value = match.name;
            } else if (!hiddenEl.value) {
                searchEl.value = ''; // Kosongkan jika tidak valid dan belum ada pilihan
            }
        });
    }, 150);
};

window.addCustomProductToCart = function(name, qty, unit, totalPrice) {
    const unitPrice = totalPrice / qty;
    const newItem = {
        id: Date.now(),
        product_id: 'CUSTOM',
        is_custom: true,
        name: name,
        print_name: name,
        product_name: name,
        packagings: [{
            level: 1,
            unit_name: unit,
            sell_price_retail: unitPrice,
            sell_price_wholesale: unitPrice,
            qty_prices: []
        }],
        level: 1,
        unit_name: unit,
        quantity: qty,
        use_custom_price: true,
        custom_line_total: totalPrice,
        custom_price_draft: String(totalPrice),
        unit_price: unitPrice,
        total: totalPrice,
        price_note: 'Barang Custom',
    };
    cart.unshift(newItem);
    renderCart();
    showToast(`"${name}" ditambahkan ke keranjang`, 'success');
};

document.addEventListener('DOMContentLoaded', function() {
    try {
        searchInput = document.getElementById('posSearch');
        suggestionsDiv = document.getElementById('posSuggestions');
        cartContainer = document.getElementById('cartItems');
        emptyState = document.getElementById('emptyCartState');
        cartTotalEl = document.getElementById('cartTotal');
        cartCountEl = document.getElementById('cartCount');
        btnCheckout = document.getElementById('btnCheckout');
        btnSaveDraft = document.getElementById('btnSaveDraft');

        if (STORE_SETTINGS && typeof thermalPrinter !== 'undefined' && thermalPrinter.setStoreSettings) {
            thermalPrinter.setStoreSettings(STORE_SETTINGS);
        }

        // Auto-reconnect printer thermal saat halaman POS dibuka — supaya kasir tidak perlu pairing ulang
        // setiap kali. Berjalan diam-diam, hanya beri toast saat berhasil.
        try {
            const tpInit = getThermalPrinterSafe();
            if (tpInit && !tpInit.isConnected() && (tpInit.device || tpInit.hasSavedDevice())) {
                tpInit.tryAutoReconnect().then(ok => {
                    if (ok) {
                        showToast('Printer thermal terhubung otomatis: ' + (tpInit.device?.name || ''), 'success');
                    }
                }).catch(() => { /* silent */ });
            }
        } catch (e) { console.warn('[POS] auto-reconnect skip:', e); }

        initPosSearch();
        initCustomerSearch();

        const urlParams = new URLSearchParams(window.location.search);
        const editId = urlParams.get('edit');

        if (editId) {
            editSaleId = editId;
            loadSaleForEdit(editId);
        } else {
            autoRestoreCart();
        }
        
        renderCart();
    } catch (err) {
        console.error('POS init error:', err);
        showToast('Gagal memuat halaman kasir. Muat ulang halaman.', 'error');
    }
});

async function loadSaleForEdit(id) {
    try {
        showToast('Memuat data transaksi...', 'info');
        const res = await fetch(`${BASE_URL}api/sales/invoice/${id}`);
        const data = await res.json();

        if (data.success && data.transaction) {
            const sale = data.transaction;
            setSaleMode(sale.sale_mode);
            
            // Map items
            cart = sale.items.map(item => {
                const isCustom = item.custom_name !== null;
                const printName = item.invoice_name || item.full_name || item.custom_name;
                
                let pkg = null;
                if (!isCustom) {
                    pkg = {
                        level: item.packaging_level || 1,
                        unit_name: item.unit_name,
                        sell_price_retail: item.unit_price,
                        sell_price_wholesale: item.unit_price,
                        ppn_pct: 0,
                        discount_value: 0
                    };
                }

                return {
                    id: Date.now() + Math.random(),
                    product_id: isCustom ? 'CUSTOM' : item.product_id,
                    is_custom: isCustom,
                    name: printName,
                    print_name: printName,
                    product_name: printName,
                    packagings: isCustom ? [{ level: 1, unit_name: item.unit_name, qty_prices: [] }] : [pkg],
                    level: isCustom ? 1 : (item.packaging_level || 1),
                    unit_name: item.unit_name,
                    quantity: parseFloat(item.quantity),
                    use_custom_price: isCustom,
                    custom_line_total: isCustom ? parseFloat(item.total_price) : null,
                    custom_price_draft: isCustom ? String(item.total_price) : undefined,
                    unit_price: parseFloat(item.unit_price),
                    total: parseFloat(item.total_price),
                    price_note: isCustom ? 'Barang Custom' : ''
                };
            });

            // Insert edit banner
            const banner = document.createElement('div');
            banner.innerHTML = `
                <div style="background:var(--warning-bg); border-left:4px solid var(--warning); padding:12px; margin-bottom:16px; border-radius:4px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-weight:700; color:var(--warning); font-size:14px;"><i class="bi bi-pencil-square"></i> Mode Edit Transaksi</div>
                        <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">Mengedit Invoice: <strong style="color:var(--text-primary);">${sale.invoice_number}</strong></div>
                    </div>
                    <a href="${BASE_URL}sales/pos" class="btn-outline-custom" style="padding:4px 10px; font-size:11px; text-decoration:none;">Batal Edit</a>
                </div>
            `;
            document.querySelector('.page-section').insertBefore(banner, document.querySelector('.pos-header').nextSibling);

            cart.forEach(it => recalcItemPrice(it));
            renderCart();
            showToast('Transaksi dimuat untuk diedit', 'success');
        } else {
            showToast('Gagal memuat transaksi', 'error');
            editSaleId = null;
        }
    } catch (e) {
        console.error(e);
        showToast('Error memuat transaksi', 'error');
        editSaleId = null;
    }
}
</script>

<script>
// ===== Customer Selector =====

let _customerSearchTimeout = null;
let _allCustomers = []; // cached from first load

function initCustomerSearch() {
    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#customerSelectorBox') && !e.target.closest('#customerDropdown')) {
            closeCustomerDropdown();
        }
    });
    // Pre-load customers in background
    fetchCustomers('').catch(() => {});
}

function toggleCustomerDropdown() {
    const dd = document.getElementById('customerDropdown');
    if (dd.style.display === 'none' || !dd.style.display) {
        openCustomerDropdown();
    } else {
        closeCustomerDropdown();
    }
}

function openCustomerDropdown() {
    const dd = document.getElementById('customerDropdown');
    const chevron = document.getElementById('customerChevron');
    const box = document.getElementById('customerSelectorBox');
    dd.style.display = 'block';
    box.style.borderColor = 'var(--primary)';
    if (chevron) chevron.style.transform = 'rotate(180deg)';
    // Focus search input
    setTimeout(() => {
        const inp = document.getElementById('customerSearchInput');
        if (inp) { inp.value = ''; inp.focus(); onCustomerSearch(''); }
    }, 60);
}

function closeCustomerDropdown() {
    const dd = document.getElementById('customerDropdown');
    const chevron = document.getElementById('customerChevron');
    const box = document.getElementById('customerSelectorBox');
    dd.style.display = 'none';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (box) box.style.borderColor = 'var(--border-color)';
}

function onCustomerSearch(q) {
    clearTimeout(_customerSearchTimeout);
    _customerSearchTimeout = setTimeout(() => {
        renderCustomerResults(q.trim());
    }, 200);
}

/**
 * Multi-keyword fuzzy search:
 * Splits query into words, each word must match at least one field
 * (name or phone). Case-insensitive, accent-insensitive.
 */
function multiKeywordFilter(customers, q) {
    if (!q) return customers;
    const normalize = s => (s || '').toLowerCase()
        .replace(/[àáâãäå]/g, 'a').replace(/[èéêë]/g, 'e')
        .replace(/[ìíîï]/g, 'i').replace(/[òóôõö]/g, 'o')
        .replace(/[ùúûü]/g, 'u').replace(/[ñ]/g, 'n');
    const keywords = normalize(q).split(/\s+/).filter(k => k.length > 0);
    return customers.filter(c => {
        const name = normalize(c.name || '');
        const phone = normalize(c.phone || '');
        return keywords.every(kw => name.includes(kw) || phone.includes(kw));
    });
}

async function fetchCustomers(q) {
    try {
        const url = q
            ? `${BASE_URL}api/customers?q=${encodeURIComponent(q)}`
            : `${BASE_URL}api/customers`;
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) return [];
        const data = await res.json();
        const list = data.data || data || [];
        if (!q) _allCustomers = list; // cache full list
        return list;
    } catch (e) {
        return [];
    }
}

async function renderCustomerResults(q) {
    const container = document.getElementById('customerResults');
    if (!container) return;

    // Use cached list + client-side filter for instant feel; also fetch from server
    let customers = multiKeywordFilter(_allCustomers, q);

    // Show immediately from cache
    renderCustomerList(container, customers, q);

    // Also fetch from server if query changed (server-side search for new data)
    if (q.length > 0) {
        const serverList = await fetchCustomers(q);
        // Merge & dedupe by id
        const merged = [...serverList];
        customers.forEach(c => {
            if (!merged.find(m => m.id === c.id)) merged.push(c);
        });
        const filtered = multiKeywordFilter(merged, q);
        renderCustomerList(container, filtered, q);
    }
}

function highlightKeywords(text, q) {
    if (!q || !text) return escapeHtml(text || '');
    const keywords = q.trim().split(/\s+/).filter(k => k.length > 0);
    let result = escapeHtml(text);
    keywords.forEach(kw => {
        const regex = new RegExp('(' + kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        result = result.replace(regex, '<mark style="background:var(--primary-bg);color:var(--primary);border-radius:2px;padding:0 2px;">$1</mark>');
    });
    return result;
}

function renderCustomerList(container, customers, q) {
    // Always show "Pelanggan Umum" option at top
    let html = `
        <div onclick="selectCustomer(null)" style="padding:10px 14px;display:flex;align-items:center;gap:10px;cursor:pointer;transition:background 0.15s;${!selectedCustomer ? 'background:var(--primary-bg);' : ''}"
             onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='${!selectedCustomer ? 'var(--primary-bg)' : 'transparent'}'">
            <div style="width:30px;height:30px;border-radius:50%;background:var(--surface-2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-person" style="color:var(--text-muted);font-size:0.9rem;"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:var(--font-size-sm);font-weight:600;color:var(--text-primary);">Pelanggan Umum</div>
                <div style="font-size:10px;color:var(--text-muted);">Tanpa pencatatan pelanggan</div>
            </div>
            ${!selectedCustomer ? '<i class="bi bi-check-circle-fill" style="color:var(--primary);"></i>' : ''}
        </div>`;

    if (customers.length === 0 && q) {
        html += `<div style="padding:14px;text-align:center;color:var(--text-muted);font-size:var(--font-size-sm);">
            <i class="bi bi-search" style="display:block;font-size:1.5rem;margin-bottom:6px;opacity:0.4;"></i>
            Tidak ada pelanggan ditemukan
        </div>`;
    } else {
        customers.slice(0, 20).forEach(c => {
            const isActive = selectedCustomer && selectedCustomer.id === c.id;
            html += `
                <div onclick="selectCustomer(${JSON.stringify({id: c.id, name: c.name, phone: c.phone || ''}).replace(/"/g,'&quot;')})"
                     style="padding:10px 14px;display:flex;align-items:center;gap:10px;cursor:pointer;transition:background 0.15s;${isActive ? 'background:var(--primary-bg);' : ''}border-top:1px solid var(--border-color);"
                     onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='${isActive ? 'var(--primary-bg)' : 'transparent'}'">
                    <div style="width:30px;height:30px;border-radius:50%;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;font-weight:700;color:var(--primary);">
                        ${escapeHtml((c.name || '?').charAt(0).toUpperCase())}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:var(--font-size-sm);font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${highlightKeywords(c.name, q)}</div>
                        ${c.phone ? `<div style="font-size:10px;color:var(--text-muted);">${highlightKeywords(c.phone, q)}</div>` : ''}
                    </div>
                    ${isActive ? '<i class="bi bi-check-circle-fill" style="color:var(--primary);flex-shrink:0;"></i>' : ''}
                </div>`;
        });
    }

    container.innerHTML = html;
}

function selectCustomer(customer) {
    if (!customer || customer === 'null') {
        selectedCustomer = null;
    } else {
        // customer may be object passed via onclick JSON or already an object
        selectedCustomer = typeof customer === 'string' ? JSON.parse(customer) : customer;
    }
    updateCustomerUI();
    closeCustomerDropdown();
}

function clearCustomer() {
    selectedCustomer = null;
    updateCustomerUI();
}

function updateCustomerUI() {
    const label = document.getElementById('customerSelectorLabel');
    const icon = document.getElementById('customerSelectorIcon');
    const clearBtn = document.getElementById('btnClearCustomer');

    if (selectedCustomer) {
        if (label) label.textContent = selectedCustomer.name + (selectedCustomer.phone ? ` · ${selectedCustomer.phone}` : '');
        if (icon) { icon.className = 'bi bi-person-check-fill'; icon.style.color = 'var(--success)'; }
        if (clearBtn) clearBtn.style.display = 'flex';
    } else {
        if (label) label.textContent = 'Pelanggan Umum';
        if (icon) { icon.className = 'bi bi-person'; icon.style.color = 'var(--primary)'; }
        if (clearBtn) clearBtn.style.display = 'none';
    }
}
</script>
