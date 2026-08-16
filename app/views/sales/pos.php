<!-- POS View -->
<?php
/**
 * @var string $csrfToken
 */
?>
<div class="page-section" style="padding-bottom:200px;">
    <style>
        .pos-segmented {
            display: inline-flex;
            background: var(--surface-1);
            border-radius: 6px;
            padding: 3px;
            border: 1px solid var(--border-color);
            align-items: stretch;
        }
        .pos-segmented button {
            flex: 1;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 4px;
            transition: all 0.2s ease;
            cursor: pointer;
            white-space: nowrap;
        }
        .pos-segmented button:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.08);
        }
        .pos-segmented button.active {
            background: var(--primary);
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(230, 57, 70, 0.4);
        }
        /* ──── Mobile Layout Ordering (<992px) ──── */
        @media (max-width: 991.98px) {
            .desktop-pos-layout {
                display: flex;
                flex-direction: column;
            }
            .pos-left-panel,
            .pos-right-panel {
                display: contents;
            }
            .pos-header-toolbar { order: 1; }
            #mixDefaultPriceBox { order: 2; }
            #posChainBanner    { order: 3; }
            .pos-customer-block { order: 4; }
            .pos-search-block   { order: 5; }
            #cartItems          { order: 6; }
            .pos-desktop-customer-header { display: none !important; }
        }
    </style>

    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- POS Header: Action Buttons & Sale Mode (Full Width Top Bar) -->
    <div class="pos-header-toolbar" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
        <!-- Left: Actions -->
        <div style="display:flex; gap:6px; flex-shrink:0; flex-wrap:wrap;">
            <button type="button" class="btn-outline-custom" onclick="clearCartConfirm()" style="padding:4px 8px; border-radius:var(--radius-sm); font-size:11px; background:var(--danger-bg); border:1px solid rgba(230,57,70,0.3); color:var(--danger); display:flex; align-items:center; gap:4px;" title="Kosongkan Keranjang"><i class="bi bi-trash3"></i> <span class="d-none d-sm-inline">Batal</span></button>
            <button class="btn-outline-custom" onclick="window.location.href=`${BASE_URL}sales`" style="padding:4px 8px; border-radius:var(--radius-sm); font-size:11px; background:var(--surface-1); border:1px solid var(--border-color); display:flex; align-items:center; gap:4px;" title="Lihat Riwayat Penjualan"><i class="bi bi-clock-history"></i> <span class="d-none d-sm-inline">Riwayat</span></button>
            <button class="btn-outline-custom" onclick="openDrafts()" style="padding:4px 8px; border-radius:var(--radius-sm); font-size:11px; background:var(--surface-1); border:1px solid var(--border-color); display:flex; align-items:center; gap:4px;"><i class="bi bi-journal-bookmark"></i> <span class="d-none d-sm-inline">Draft</span></button>
        </div>
        
        <!-- Right: Sale Mode Tabs -->
        <div class="pos-segmented" style="flex-shrink:0;">
            <button id="btnRetail" class="active" onclick="setSaleMode('retail')">Ecer</button>
            <button id="btnWholesale" onclick="setSaleMode('wholesale')">Grosir</button>
            <button id="btnMix" onclick="setSaleMode('mix')">Mix</button>
        </div>
    </div>

    <!-- ═══ Desktop 2-Column POS Layout / Mobile Stack ═══ -->
    <div class="desktop-pos-layout">

        <!-- ── LEFT PANEL: Customer · Search · Cart ── -->
        <div class="pos-left-panel">

            <!-- Customer Selector (Sectioned Card on Desktop) -->
            <div class="pos-customer-block" style="margin-bottom:12px; position:relative;">
                <div class="pos-desktop-customer-header">
                    <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; display:flex; align-items:center; justify-content:space-between; width:100%;">
                        <span><i class="bi bi-person-vcard" style="color:var(--primary); margin-right:6px;"></i> Pelanggan</span>
                        <span id="customerBadge" class="badge-custom badge-secondary" style="font-size:10px; padding:3px 8px;">Umum</span>
                    </div>
                </div>
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
                        <button type="button" id="btnClearCustomer" onclick="event.stopPropagation();clearCustomer()" title="Hapus pilihan" style="display:none;background:var(--surface-2);border:1px solid var(--border-color);border-radius:50%;width:24px;height:24px;padding:0;cursor:pointer;color:var(--text-muted);font-size:0.75rem;line-height:1;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <!-- Customer Dropdown Panel -->
                <div id="customerDropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:200;background:var(--surface-1);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid var(--border-color);border-radius:var(--radius-lg);box-shadow:0 12px 36px rgba(0,0,0,0.35);overflow:hidden;">
                    <div style="padding:10px 12px;border-bottom:1px solid var(--border-color);background:var(--surface-2);">
                        <div style="display:flex;align-items:center;gap:8px;background:var(--bg-input);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:0 12px;">
                            <i class="bi bi-search" style="color:var(--text-muted);font-size:0.9rem;"></i>
                            <input type="text" id="customerSearchInput"
                                   placeholder="Ketik nama atau nomor HP..."
                                   autocomplete="off" autocorrect="off" spellcheck="false"
                                   style="flex:1;border:none;background:transparent;padding:10px 4px;color:var(--text-primary);font-size:14px;outline:none;font-family:var(--font-family);"
                                   oninput="onCustomerSearch(this.value)">
                            <i class="bi bi-x-circle" style="color:var(--text-muted);font-size:0.9rem;cursor:pointer;" onclick="document.getElementById('customerSearchInput').value='';onCustomerSearch('');"></i>
                        </div>
                    </div>
                    <div id="customerResults" style="max-height:240px;overflow-y:auto;padding:6px 0;scrollbar-width:thin;">
                        <!-- populated by JS -->
                    </div>
                </div>
            </div>

            <!-- Search Product -->
            <div class="pos-search-block" style="background:var(--surface-1); border-radius:var(--radius-md); padding:12px; margin-bottom:16px; border:1px solid var(--border-color);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label style="font-size:var(--font-size-xs); font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin:0;">Scan/Cari Produk</label>
                    <button type="button" class="btn-outline-custom" onclick="openCustomProductModal()" style="padding:4px 10px; border-radius:6px; font-size:11px; display:inline-flex; align-items:center; gap:4px; border:1px solid var(--border-color); background:var(--surface-2);">
                        <i class="bi bi-plus-circle"></i> + Barang Custom
                    </button>
                </div>
                <div class="search-input-wrapper" style="background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:0 12px; display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-upc-scan" style="color:var(--primary); font-size:1.2rem; cursor:pointer;" onclick="openPosScanner()" title="Scan Barcode Kamera"></i>
                    <input type="text" id="posSearch" placeholder="Scan barcode atau ketik nama produk..." 
                           style="flex:1;border:none;background:transparent;padding:12px 8px;color:var(--text-primary);font-size:16px;outline:none;font-family:var(--font-family);" autocomplete="off" autofocus>
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

        </div><!-- /pos-left-panel -->

        <!-- ── RIGHT PANEL: Mix Box · Chain Banner · Checkout Card ── -->
        <div class="pos-right-panel">

            <!-- Mix Default Price Selector (Slim Bar) -->
            <div id="mixDefaultPriceBox" style="display:none; background:var(--surface-1); border-radius:var(--radius-lg); padding:14px 18px; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; border:1px solid var(--border-color); box-shadow:0 8px 24px rgba(0,0,0,0.14);">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-shuffle" style="color:var(--primary); font-size:1.1rem;"></i>
                    <span style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px;">Default Mix:</span>
                </div>
                <div class="pos-segmented">
                    <button id="btnMixDefaultRetail" class="active" onclick="setMixDefault('retail')">Ecer</button>
                    <button id="btnMixDefaultWholesale" onclick="setMixDefault('wholesale')">Grosir</button>
                </div>
            </div>

            <!-- Banner Indicator Struk / Invoice Lanjutan -->
            <div id="posChainBanner" style="display:none; background:linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border-radius:var(--radius-lg); padding:14px 18px; border:1px solid rgba(129,140,248,0.4); color:white; box-shadow:0 8px 24px rgba(0,0,0,0.15);"></div>

            <!-- Checkout Card (Floating on mobile, Sticky Sidebar Panel on desktop) -->
            <div class="pos-checkout-bar" id="posCheckoutBar">
                <div class="pos-checkout-bar__inner">

                    <!-- Desktop Sidebar Title Header (Visible on Desktop >=992px) -->
                    <div class="pos-desktop-sidebar-header">
                        <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; display:flex; align-items:center; justify-content:space-between; width:100%;">
                            <span><i class="bi bi-receipt-cutoff" style="color:var(--primary); margin-right:6px;"></i> Ringkasan Kasir</span>
                            <span id="posModeBadge" class="badge-custom badge-info" style="font-size:10px; padding:3px 8px;">Mode Ecer</span>
                        </div>
                    </div>

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

                    <!-- Additional Transaction Summary Breakdown (Desktop Only) -->
                    <div class="pos-desktop-summary-details" style="border-top:1px dashed var(--border-color); padding-top:12px; margin-top:4px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--text-muted); margin-bottom:4px;">
                            <span>Status Sesi</span>
                            <span style="color:var(--success); font-weight:600;"><i class="bi bi-check-circle-fill" style="margin-right:4px;"></i> Siap Transaksi</span>
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

        </div><!-- /pos-right-panel -->

    </div><!-- /desktop-pos-layout -->
</div><!-- /page-section -->


<script>
const STORE_SETTINGS = <?= json_encode($storeSettings ?? [], JSON_UNESCAPED_UNICODE) ?>;

let cart = [];
let saleMode = 'retail';
let mixDefaultPrice = 'retail'; // Used when saleMode === 'mix'
let currentDraftId = null;
let editSaleId = null;
let searchInput, suggestionsDiv, cartContainer, emptyState, cartTotalEl, cartCountEl, btnCheckout, btnSaveDraft;
let selectedCustomer = null; // { id, name, phone } or null = Pelanggan Umum

// ── Linked / Continuation Invoice State (Struk Lanjutan) ─────────────────────
let chainParentInvoiceNo = null;
let chainedInvoices = []; // Array of { invoiceNo, total }

function getChainInfo() {
    if (!chainedInvoices || chainedInvoices.length === 0) {
        return { isContinuation: false };
    }
    const previousTotal = chainedInvoices.reduce((sum, inv) => sum + (inv.total || 0), 0);
    const currentTotal = (typeof cart !== 'undefined' && Array.isArray(cart))
        ? cart.reduce((sum, i) => sum + (i.total || 0), 0)
        : 0;
    return {
        isContinuation: true,
        parentInvoiceNo: chainParentInvoiceNo,
        chainNumber: chainedInvoices.length,          // 1 = Struk Lanjutan 1, 2 = Struk Lanjutan 2, …
        allPreviousInvoices: [...chainedInvoices],    // [{invoiceNo, total}, …]
        previousInvoices: chainedInvoices.map(i => i.invoiceNo),
        previousTotal: previousTotal,
        currentTotal: currentTotal,
        grandTotal: previousTotal + currentTotal
    };
}

function startNextInvoiceChain(invoiceNo, total) {
    if (!chainParentInvoiceNo) {
        chainParentInvoiceNo = invoiceNo;
    }
    chainedInvoices.push({ invoiceNo, total });
    
    cart = [];
    currentDraftId = null;
    clearAutoSave();
    renderCart();
    renderChainBanner();
    
    AppModal.close();
    showToast(`Mode Struk Lanjutan Aktif (Lanjutan dari ${chainParentInvoiceNo})`, 'info');
    const inp = document.getElementById('posSearch');
    if (inp) inp.focus();
}

function clearChainSession() {
    chainParentInvoiceNo = null;
    chainedInvoices = [];
    renderChainBanner();
    calculateTotal();
}

function renderChainBanner() {
    const banner = document.getElementById('posChainBanner');
    if (!banner) return;
    if (chainedInvoices.length === 0) {
        banner.style.display = 'none';
        banner.innerHTML = '';
        return;
    }
    const prevTotal = chainedInvoices.reduce((sum, inv) => sum + (inv.total || 0), 0);
    banner.style.display = 'flex';
    banner.style.alignItems = 'center';
    banner.style.justifyContent = 'space-between';
    banner.style.gap = '10px';
    banner.style.flexWrap = 'wrap';
    
    banner.innerHTML = `
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:34px; height:34px; background:rgba(129,140,248,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="bi bi-link-45deg" style="font-size:1.3rem; color:#818cf8;"></i>
            </div>
            <div>
                <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#c7d2fe;">
                    🔗 Struk Lanjutan Aktif (${chainedInvoices.length} Invoice Terhubung)
                </div>
                <div style="font-size:12px; font-weight:600; color:#ffffff;">
                    Rujukan: <span style="font-family:monospace;">${escapeHtml(chainParentInvoiceNo)}</span> &nbsp;|&nbsp; Total Sebelumnya: <strong style="color:#a7f3d0;">${formatRupiah(prevTotal)}</strong>
                </div>
            </div>
        </div>
        <button type="button" onclick="clearChainSession()" class="btn-outline-custom" style="padding:4px 10px; font-size:11px; border:1px solid rgba(255,255,255,0.3); color:#ffffff; background:rgba(255,255,255,0.12); border-radius:6px; cursor:pointer;" title="Selesaikan sesi rantai invoice ini">
            <i class="bi bi-x-circle"></i> Selesai Rantai
        </button>
    `;
}

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

    // --- MIX MODE: resolve effective price mode per-item ---
    // For 'mix' mode, each item can override its price mode independently.
    // The 'retail' and 'wholesale' code paths below are completely unchanged.
    let effectiveMode = saleMode;
    if (saleMode === 'mix') {
        effectiveMode = item.mix_override_mode ?? mixDefaultPrice;
        // Store retail price for discount computation (default=Ecer, override=Grosir)
        if (mixDefaultPrice === 'retail' && effectiveMode === 'wholesale') {
            const retailTotal = typeof QtyPricing !== 'undefined' && typeof QtyPricing.calculateTotalPrice === 'function'
                ? QtyPricing.calculateTotalPrice(pkg, 'retail', qty, false, null, item.packagings)
                : (parseFloat(pkg.sell_price_retail) || 0) * qty;
            item._retail_total = Math.round(retailTotal);
            item._retail_unit_price = qty > 0 ? item._retail_total / qty : 0;
        } else {
            item._retail_total = null;
            item._retail_unit_price = null;
        }
    } else {
        item._retail_total = null;
        item._retail_unit_price = null;
    }


    let basePricePerUnit = 0;
    let rawTotal = 0;
    if (typeof QtyPricing !== 'undefined' && typeof QtyPricing.calculateTotalPrice === 'function') {
        rawTotal = QtyPricing.calculateTotalPrice(pkg, effectiveMode, qty, false, null, item.packagings);
        basePricePerUnit = qty > 0 ? rawTotal / qty : 0;
        item.price_note = typeof QtyPricing.getPriceNote === 'function'
            ? QtyPricing.getPriceNote(pkg, effectiveMode, qty, false, item.packagings) : '';
    } else {
        // Fallback: direct unit price (no tier pricing)
        basePricePerUnit = effectiveMode === 'wholesale'
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

window.toggleItemMixMode = function(itemId, mode) {
    const item = cart.find(i => i.id == itemId);
    if (!item) return;
    item.mix_override_mode = mode;
    recalcItemPrice(item);
    renderCart();
};

window.togglePosCustomPrice = async function(itemId, checked, checkboxEl) {
    const item = cart.find(i => i.id == itemId);
    if (!item) return;

    if (!checked) {
        const confirmed = await AppModal.show({
            title: 'Kembalikan ke Harga Normal?',
            subtitle: 'Pembatalan Harga Custom',
            bodyHTML: '<div style="text-align:center; padding:10px 0;"><i class="bi bi-exclamation-circle" style="font-size:3rem; color:var(--warning); display:block; margin-bottom:12px;"></i><p style="font-size:14px; margin-bottom:8px;">Anda yakin ingin membatalkan harga custom?</p><p style="font-size:13px; color:var(--text-muted);">Harga barang ini akan kembali mengikuti harga normal sesuai sistem.</p></div>',
            icon: 'bi-question-circle',
            iconColor: 'var(--warning-bg)',
            iconAccent: 'var(--warning)',
            submitText: 'Ya, Kembalikan',
            cancelText: 'Batal'
        });
        
        if (!confirmed) {
            if (checkboxEl) checkboxEl.checked = true;
            return;
        }
    }

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
    const btnRetail = document.getElementById('btnRetail');
    const btnWholesale = document.getElementById('btnWholesale');
    const btnMix = document.getElementById('btnMix');
    const mixBox = document.getElementById('mixDefaultPriceBox');

    // Reset all tabs
    [btnRetail, btnWholesale, btnMix].forEach(btn => {
        if (btn) { btn.className = ''; }
    });

    if (mode === 'retail') {
        if (btnRetail) { btnRetail.className = 'active'; }
        if (mixBox) mixBox.style.display = 'none';
    } else if (mode === 'wholesale') {
        if (btnWholesale) { btnWholesale.className = 'active'; }
        if (mixBox) mixBox.style.display = 'none';
    } else if (mode === 'mix') {
        if (btnMix) { btnMix.className = 'active'; }
        if (mixBox) mixBox.style.display = '';
        // Only set default for items that don't already have an override (preserves edit-loaded overrides)
        cart.forEach(item => { if (item.mix_override_mode === undefined) item.mix_override_mode = mixDefaultPrice; });
    }

    cart.forEach(item => { recalcItemPrice(item); });
    const modeBadge = document.getElementById('posModeBadge');
    if (modeBadge) {
        modeBadge.textContent = mode === 'retail' ? 'Mode Ecer' : (mode === 'wholesale' ? 'Mode Grosir' : 'Mode Mix');
    }
    renderCart();
}

function setMixDefault(mode) {
    mixDefaultPrice = mode;
    const btnR = document.getElementById('btnMixDefaultRetail');
    const btnW = document.getElementById('btnMixDefaultWholesale');
    if (btnR) { btnR.className = mode === 'retail' ? 'active' : ''; }
    if (btnW) { btnW.className = mode === 'wholesale' ? 'active' : ''; }
    // Reset all items to new default
    cart.forEach(item => {
        item.mix_override_mode = mode;
        recalcItemPrice(item);
    });
    renderCart();
}

// Global In-Memory POS Products Catalog Cache for 0ms instant search
window._posProductsCatalog = window._posProductsCatalog || [];

async function preloadPosCatalog() {
    // 1. Load from LocalStorage for 0ms immediate availability
    try {
        const cached = localStorage.getItem('pos_catalog_cache');
        if (cached) {
            window._posProductsCatalog = JSON.parse(cached);
        }
    } catch(e) {}

    // 2. Load from Dexie IndexedDB if localStorage is still empty
    if (window._posProductsCatalog.length === 0 && typeof db !== 'undefined' && db.products) {
        try {
            const dbItems = await db.products.filter(p => p.is_available != 0 && p.is_available !== '0' && p.is_available !== false).toArray();
            if (dbItems && dbItems.length > 0) {
                window._posProductsCatalog = dbItems;
            }
        } catch(e) {}
    }

    // 3. Always fetch fresh catalog from server (non-blocking, 5s timeout)
    // This ensures new products are ALWAYS available, even if not yet synced locally.
    if (navigator.onLine) {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);
            const res = await fetch(`${BASE_URL}api/products/sync?pos=1&_t=` + Date.now(), { signal: controller.signal });
            clearTimeout(timeoutId);
            if (res.ok) {
                const data = await res.json();
                if (data && data.products && Array.isArray(data.products)) {
                    // Update in-memory catalog immediately
                    window._posProductsCatalog = data.products;
                    // Update Dexie IndexedDB in background (non-blocking)
                    if (typeof db !== 'undefined' && db.products) {
                        db.products.clear().then(() => db.products.bulkPut(data.products)).catch(() => {});
                    }
                    // Update localStorage cache (cap at 1.5MB to avoid quota errors)
                    try {
                        const serialized = JSON.stringify(data.products);
                        if (serialized.length < 1500000) {
                            localStorage.setItem('pos_catalog_cache', serialized);
                        }
                    } catch(e) {}
                }
            }
        } catch(e) {}
    }
}

let _posSearchDebounceTimer = null;

// ── Barcode Scanner Detection ──────────────────────────────────────────────────
// Physical barcode scanners send characters in < 30ms bursts.
// We track the gap between keystrokes: if consistently < 50ms → scanner mode.
// In scanner mode: skip text-search debounce, and after Enter clear input
// BEFORE the async lookup to prevent barcodes stacking on next scan.
let _posLastCharTime = 0;
let _posFastCharCount = 0;
let _posFromScanner = false; // true if current Enter came from scanner
const _POS_SCANNER_GAP_MS = 50; // chars < 50ms apart = scanner
const _POS_SCANNER_MIN_FAST = 2; // need ≥2 fast chars to flag as scanner
let _posScannerAutoScanTimer = null;

function initPosSearch() {
    const inp = document.getElementById('posSearch');
    const sug = document.getElementById('posSuggestions');
    
    if (!inp || !sug) return;

    // Track typing speed & run text search (debounced) for human typing
    inp.addEventListener('input', function() {
        const now = Date.now();
        const gap = now - _posLastCharTime;
        _posLastCharTime = now;

        const val = this.value;
        const q = val.trim();

        // Reset fast-char counter when input is cleared / very first char
        if (q.length <= 1) {
            _posFastCharCount = q.length === 1 ? 1 : 0;
        } else if (gap < _POS_SCANNER_GAP_MS) {
            _posFastCharCount++;
        } else {
            // Slow gap → human typing; reset scanner counter
            _posFastCharCount = 0;
        }

        // Auto-reset input when a new fast scanner stream starts while old text was present
        if (_posFastCharCount === 2 && val.length > 2) {
            const firstTwo = val.slice(-2);
            this.value = firstTwo;
        }

        if (this.value.trim().length < 2) {
            sug.innerHTML = '';
            clearTimeout(_posSearchDebounceTimer);
            clearTimeout(_posScannerAutoScanTimer);
            if (window.posSearchAbortController) window.posSearchAbortController.abort();
            return;
        }

        // If scanner is typing fast, skip text-search debounce and hide suggestions immediately
        const isScannerTyping = _posFastCharCount >= _POS_SCANNER_MIN_FAST;
        clearTimeout(_posSearchDebounceTimer);
        clearTimeout(_posScannerAutoScanTimer);

        if (isScannerTyping) {
            sug.innerHTML = '';
            if (window.posSearchAbortController) window.posSearchAbortController.abort();
            
            // Auto-trigger scan 120ms after last digit for scanners without Enter key
            _posScannerAutoScanTimer = setTimeout(() => {
                const scanVal = this.value.trim();
                if (scanVal.length >= 2) {
                    this.value = '';
                    sug.innerHTML = '';
                    processBarcodeScan(scanVal, this, sug, true);
                }
            }, 120);
            return;
        }

        // Human typing → normal 80ms debounce text search (supports multi-keyword)
        _posSearchDebounceTimer = setTimeout(() => {
            performSearch(this.value.trim());
        }, 80);
    });

    // Click outside to hide suggestions
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-input-wrapper') && !e.target.closest('#posSuggestions')) {
            sug.innerHTML = '';
        }
    });

    // Enter key: clear input IMMEDIATELY before async processing to prevent
    // barcode stacking when the scanner starts typing the next code right away.
    inp.addEventListener('keydown', async function(e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        clearTimeout(_posSearchDebounceTimer);
        clearTimeout(_posScannerAutoScanTimer);

        const q = this.value.trim();
        if (!q) return;

        // Determine if Enter came from scanner (fast chars accumulated)
        const fromScanner = _posFastCharCount >= _POS_SCANNER_MIN_FAST;
        _posFromScanner = fromScanner;

        // ← KEY FIX: wipe input BEFORE the async call so next scan starts clean
        this.value = '';
        sug.innerHTML = '';
        _posFastCharCount = 0;

        await processBarcodeScan(q, this, sug, fromScanner);
    });

    // Preload catalog on search focus or init
    preloadPosCatalog();
}

function openPosScanner() {
    const inp = document.getElementById('posSearch');
    const sug = document.getElementById('posSuggestions');
    if (typeof BarcodeUtil !== 'undefined') {
        BarcodeUtil.scanBarcode(inp, (code) => {
            if (code) {
                processBarcodeScan(code, inp, sug);
            }
        });
    }
}

let _lastPosScanCode = '';
let _lastPosScanTime = 0;

function findMatchedLevelForBarcode(product, q) {
    if (!product || !product.packagings || !Array.isArray(product.packagings) || product.packagings.length === 0) {
        return 1;
    }
    if (!q) {
        return product.packagings[0].level || 1;
    }
    const cleanQ = String(q).replace(/\s+/g, '').toLowerCase();
    if (!cleanQ) return 1;

    // 1. Direct packaging barcode match
    const matchedPkg = product.packagings.find(pkg => {
        if (!pkg.barcode) return false;
        const b = String(pkg.barcode).replace(/\s+/g, '').toLowerCase();
        return b === cleanQ || b === '0' + cleanQ || '0' + b === cleanQ || b === '00' + cleanQ || '00' + b === cleanQ;
    });

    if (matchedPkg && matchedPkg.level != null) {
        return parseInt(matchedPkg.level, 10);
    }

    // 2. Fallback if findByBarcode attached level to root product object
    if (product.level != null) {
        return parseInt(product.level, 10);
    }

    // 3. Fallback to level 1 or first packaging level
    const lvl1 = product.packagings.find(p => p.level == 1);
    return lvl1 ? 1 : (parseInt(product.packagings[0].level, 10) || 1);
}

async function processBarcodeScan(q, inpEl, sugEl, fromScanner) {
    if (!q) return;
    q = q.trim();
    if (fromScanner === undefined) fromScanner = _posFromScanner;
    
    // Prevent duplicate scan of the same code within 350ms
    const now = Date.now();
    if (_lastPosScanCode === q && (now - _lastPosScanTime < 350)) {
        if (inpEl) inpEl.value = '';
        if (sugEl) sugEl.innerHTML = '';
        return;
    }
    _lastPosScanCode = q;
    _lastPosScanTime = now;
    
    // 1. Instant 0ms barcode lookup in memory
    let result = null;
    if (window._posProductsCatalog && window._posProductsCatalog.length > 0) {
        const cleanB = q.replace(/\s+/g, '').toLowerCase();
        result = window._posProductsCatalog.find(p => {
            if (p.is_available == 0 || p.is_available === '0' || p.is_available === false) return false;
            if (p.code && p.code.replace(/\s+/g, '').toLowerCase() === cleanB) return true;
            if (p.packagings && Array.isArray(p.packagings)) {
                return p.packagings.some(pkg => pkg.barcode && pkg.barcode.replace(/\s+/g, '').toLowerCase() === cleanB);
            }
            return false;
        });
    }

    if (!result && typeof OfflineDB !== 'undefined') {
        try { result = await OfflineDB.findByBarcode(q, true); } catch(e){}
    }

    if (result && result.id) {
        if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
        const matchedLevel = findMatchedLevelForBarcode(result, q);
        addProductToCart(result, matchedLevel);
        // Input was already cleared before this async call; ensure clean state
        if (inpEl) inpEl.value = '';
        if (sugEl) sugEl.innerHTML = '';
        if (inpEl) inpEl.focus();
        return;
    }

    // 2. Fallback network search with 2s timeout
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 2000);
        const resp = await fetch(`${BASE_URL}api/products/barcode/${encodeURIComponent(q)}?pos=1`, { signal: controller.signal });
        clearTimeout(timeoutId);
        if (resp.ok) {
            result = await resp.json();
            if (result && result.id) {
                if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
                const matchedLevel = findMatchedLevelForBarcode(result, q);
                addProductToCart(result, matchedLevel);
                // Input was already cleared before this async call; ensure clean state
                if (inpEl) inpEl.value = '';
                if (sugEl) sugEl.innerHTML = '';
                if (inpEl) inpEl.focus();
                return;
            }
        }
    } catch(e) {}

    // ── Barcode not found ─────────────────────────────────────────────────────
    // fromScanner = Enter came from physical scanner (fast keystrokes)
    //   → Do NOT restore value (would cause stacking on next scan).
    //     Show toast so user knows, input stays clear for next scan.
    // !fromScanner = user typed manually and pressed Enter
    //   → Restore text and show text-search suggestions as fallback.
    if (fromScanner) {
        showToast('Barcode tidak ditemukan', 'warning');
        if (inpEl) { inpEl.value = ''; inpEl.focus(); }
        if (sugEl) sugEl.innerHTML = '';
    } else if (q.length >= 2 && inpEl) {
        // Manual typing fallback: put text back and run multi-keyword search
        inpEl.value = q;
        inpEl.focus();
        performSearch(q);
    } else {
        showToast('Barcode tidak ditemukan', 'warning');
    }
}

async function performSearch(q) {
    const sug = document.getElementById('posSuggestions');
    if (!sug) return;
    
    q = (q || '').trim().toLowerCase();
    if (q.length < 2) {
        sug.innerHTML = '';
        return;
    }

    const words = q.split(/\s+/).filter(w => w.length > 0);

    // ── STEP 1: Search In-Memory / IndexedDB Catalog INSTANTLY (0 Milidetik) ─────────
    let items = [];
    if (window._posProductsCatalog && window._posProductsCatalog.length > 0) {
        items = window._posProductsCatalog.filter(p => {
            if (p.is_available == 0 || p.is_available === '0' || p.is_available === false) return false;
            return words.every(word => {
                const nameMatch = (p.full_name && p.full_name.toLowerCase().includes(word)) ||
                                  (p.short_label && p.short_label.toLowerCase().includes(word)) ||
                                  (p.invoice_name && p.invoice_name.toLowerCase().includes(word)) ||
                                  (p.supplier_invoice_name && p.supplier_invoice_name.toLowerCase().includes(word));
                const brandMatch = p.brand_name && p.brand_name.toLowerCase().includes(word);
                const codeMatch = p.code && p.code.toLowerCase().includes(word);
                let barcodeMatch = false;
                if (p.packagings && Array.isArray(p.packagings)) {
                    barcodeMatch = p.packagings.some(pkg => pkg.barcode && pkg.barcode.toLowerCase().includes(word));
                }
                return nameMatch || brandMatch || codeMatch || barcodeMatch;
            });
        }).slice(0, 50);
    }

    // Render local results IMMEDIATELY (0ms delay!)
    if (items.length > 0) {
        renderPosSearchSuggestions(sug, items, q);
    } else {
        sug.innerHTML = '<div style="padding:12px;text-align:center;color:#999;">Mencari...</div>';
    }

    // ── STEP 2: Background Server Fetch (Non-blocking with Signal-Aware Abort Timeout) ──
    if (window.posSearchAbortController) {
        window.posSearchAbortController.abort();
    }
    window.posSearchAbortController = new AbortController();

    const isWeak = (typeof window.getSignalState === 'function' && window.getSignalState() === 'weak');
    if (isWeak && items.length > 0) {
        return; // Weak signal + local results present → stop here to keep typing ultra smooth
    }
    
    try {
        const fetchTimeout = isWeak ? 400 : 900;
        const timeoutId = setTimeout(() => {
            if (window.posSearchAbortController) window.posSearchAbortController.abort();
        }, fetchTimeout);

        const resp = await fetch(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}&pos=1`, { 
            credentials: 'same-origin',
            signal: window.posSearchAbortController.signal
        });
        clearTimeout(timeoutId);
        if (resp.ok) {
            const currentInput = document.getElementById('posSearch');
            if (currentInput && currentInput.value.trim().toLowerCase() === q.toLowerCase()) {
                const serverItems = await resp.json();
                if (currentInput.value.trim().toLowerCase() === q.toLowerCase()) {
                    if (Array.isArray(serverItems) && serverItems.length > 0) {
                        renderPosSearchSuggestions(sug, serverItems, q);
                    } else if (items.length === 0) {
                        sug.innerHTML = '<div style="padding:12px;text-align:center;color:#999;">Tidak ada produk ditemukan</div>';
                    }
                }
            }
        }
    } catch (e) {
        if (e.name === 'AbortError') return;
        const currentInput = document.getElementById('posSearch');
        if (items.length === 0 && currentInput && currentInput.value.trim().toLowerCase() === q.toLowerCase()) {
            sug.innerHTML = '<div style="padding:12px;text-align:center;color:#999;">Tidak ada produk ditemukan (Offline)</div>';
        }
    }
}

function renderPosSearchSuggestions(sug, items, q) {
    const currentInput = document.getElementById('posSearch');
    if (!currentInput || currentInput.value.trim().length < 2) {
        sug.innerHTML = '';
        return;
    }
    if (q && currentInput.value.trim().toLowerCase() !== q.toLowerCase()) {
        return; // Stale check
    }

    if (!Array.isArray(items) || items.length === 0) {
        sug.innerHTML = '<div style="padding:12px;text-align:center;color:#999;">Tidak ada</div>';
        return;
    }
    
    // Sort by display label (short_label if not empty, else full_name) ascending
    items.sort((a, b) => {
        const getLabel = (p) => (p.short_label && p.short_label.trim() !== '') ? p.short_label : (p.full_name || '');
        return getLabel(a).localeCompare(getLabel(b), 'id', { sensitivity: 'base' });
    });
    
    sug.innerHTML = items.map(p => {
        const name = escapeHtml(p.short_label || p.full_name);
        const brand = escapeHtml(p.brand_name || '');
        
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
}

async function selectProduct(id) {
    try {
        let data = null;

        // 1. Instant 0ms lookup in memory catalog
        if (window._posProductsCatalog && window._posProductsCatalog.length > 0) {
            data = window._posProductsCatalog.find(p => p.id == id);
        }

        // 2. Dexie IndexedDB lookup fallback
        if (!data && typeof OfflineDB !== 'undefined') {
            try { data = await OfflineDB.getProductById(id); } catch(e){}
        }

        // 3. Network fetch fallback if still not found
        if (!data && navigator.onLine) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 2000);
                const resp = await fetch(`${BASE_URL}api/products/${id}`, { credentials: 'same-origin', signal: controller.signal });
                clearTimeout(timeoutId);
                if (resp.ok) data = await resp.json();
            } catch(e){}
        }
        
        if (!data || !data.id) {
            showToast('Data produk tidak ditemukan', 'error');
            return;
        }
        const inp = document.getElementById('posSearch');
        const qVal = inp ? inp.value.trim() : '';
        const matchedLevel = findMatchedLevelForBarcode(data, qVal);
        addProductToCart(data, matchedLevel);
        const sug = document.getElementById('posSuggestions');
        if (inp) inp.value = '';
        if (sug) sug.innerHTML = '';
        if (inp) inp.focus();
    } catch(e) {
        console.error('selectProduct error', e);
    }
}

function addProductToCart(product, preferredLevel = null) {
    if (!product.packagings || product.packagings.length === 0) {
        showToast('Produk belum punya data kemasan/harga', 'warning');
        return;
    }

    let selectedPkg = null;
    if (preferredLevel != null) {
        selectedPkg = product.packagings.find(p => p.level == preferredLevel);
    }
    if (!selectedPkg) {
        const defaultLevel = 1;
        selectedPkg = product.packagings.find(p => p.level == defaultLevel) || product.packagings[0];
    }
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
            photo: product.photo || null,
            packagings: product.packagings,
            level: selectedPkg.level,
            unit_name: selectedPkg.unit_name,
            unit_abbr: selectedPkg.unit_abbr,
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
    if (!item || !item.packagings) return;
    const targetLevel = parseInt(newLevel, 10);
    const pkg = item.packagings.find(p => parseInt(p.level, 10) === targetLevel);
    if (pkg) {
        item.level = targetLevel;
        item.unit_name = pkg.unit_name;
        item.unit_abbr = pkg.unit_abbr;
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
    let mixTotalDiscount = 0;
    cart.forEach(i => {
        sum += i.total;
        const curPkg = i.packagings?.find(p => p.level == i.level);
        const buyPrice = parseFloat(curPkg?.buy_price) || 0;
        if (buyPrice > 0) {
            profit += i.total - (buyPrice * i.quantity);
        }
        // Sum up mix discounts (retail_total - actual_total when defaultEcer & override=Grosir)
        if (saleMode === 'mix' && i._retail_total != null && i._retail_total > i.total) {
            mixTotalDiscount += i._retail_total - i.total;
        }
    });

    if (chainedInvoices && chainedInvoices.length > 0) {
        const prevTotal = chainedInvoices.reduce((s, inv) => s + (inv.total || 0), 0);
        const grandTotal = sum + prevTotal;
        cartTotalEl.innerHTML = `${formatRupiah(sum)} <span style="font-size:0.75rem; font-weight:700; color:var(--primary); display:block; margin-top:2px;">(Grand Total: ${formatRupiah(grandTotal)})</span>`;
    } else {
        cartTotalEl.textContent = formatRupiah(sum);
    }

    const cartProfitEl = document.getElementById('cartProfit');
    if (cartProfitEl) {
        if (saleMode === 'mix' && mixTotalDiscount > 0) {
            cartProfitEl.innerHTML = `<span style="color:var(--success); font-size:0.7rem;">✂ Hemat <b>${formatRupiah(mixTotalDiscount)}</b></span>`;
        } else {
            cartProfitEl.textContent = profit > 0 ? `Estimasi Profit: ${formatRupiah(profit)}` : '';
        }
    }
    cartCountEl.textContent = cart.length;
    btnCheckout.disabled = cart.length === 0;
    if (btnSaveDraft) btnSaveDraft.disabled = cart.length === 0;
    return sum;
}

function getMixDiscountInfo() {
    let totalDiscount = 0;
    const items = [];
    cart.forEach(i => {
        if (saleMode === 'mix' && i._retail_total != null && i._retail_total > i.total) {
            const discAmt = i._retail_total - i.total;
            totalDiscount += discAmt;
            items.push({ name: i.name, discount: discAmt, retailTotal: i._retail_total, actualTotal: i.total });
        }
    });
    return { totalDiscount, items };
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
        const profitPerUnit = buyPrice > 0 ? (item.total / item.quantity) - buyPrice : 0;
        const buyPriceBlock = buyPrice > 0
            ? `<div style="font-size:10px;color:var(--text-muted);margin-top:4px;letter-spacing:0.3px;display:flex;flex-wrap:wrap;gap:6px;"><span>M: ${formatRupiah(buyPrice)}</span> &middot; <span style="color:${profitPerUnit >= 0 ? 'var(--success)' : 'var(--danger)'}">P: ${formatRupiah(profitPerUnit)}/satuan</span> &middot; <span style="color:${profitItem >= 0 ? 'var(--success)' : 'var(--danger)'}">Total P: ${formatRupiah(profitItem)}</span></div>`
            : '';

        // Gambar produk (thumbnail)
        const photoUrl = item.photo
            ? (item.photo.startsWith('http') ? item.photo : `${BASE_URL}${item.photo}`)
            : null;
        const thumbHtml = photoUrl
            ? `<div style="width:46px;height:46px;flex-shrink:0;border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border-color);background:var(--surface-2);">
                   <img src="${photoUrl}" alt="" loading="lazy" style="width:100%;height:100%;object-fit:contain;display:block;">
               </div>`
            : `<div style="width:46px;height:46px;flex-shrink:0;border-radius:var(--radius-sm);background:var(--primary-bg);border:1px solid rgba(230,57,70,0.15);display:flex;align-items:center;justify-content:center;color:var(--primary);">
                   <i class="bi bi-box-seam" style="font-size:1.2rem;"></i>
               </div>`;

        // Gunakan short_label (nama label) sebagai tampilan nama, fallback ke name
        const displayName = item.name;

        html += `
            <div data-cart-id="${item.id}" style="background:var(--surface-1);border-radius:var(--radius-md);padding:12px;margin-bottom:10px;border:1px solid var(--border-color);transition:box-shadow 0.15s;">
                <!-- Baris 1: Foto + Info Produk + Total -->
                <div style="display:flex;gap:10px;align-items:flex-start;margin-bottom:10px;">
                    ${thumbHtml}
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;font-size:0.8rem;margin-bottom:2px;line-height:1.35;color:var(--text-primary);white-space:normal;word-break:break-word;">${escapeHtml(displayName)}</div>
                        <div class="cart-item-unit-price" style="color:var(--text-muted);font-size:0.78rem;">${item.use_custom_price ? `${formatRupiah(item.unit_price)} / ${escapeHtml(item.unit_name)} (Total ${formatRupiah(item.total)})` : `${formatRupiah(item.unit_price)} / ${escapeHtml(item.unit_name)}`}</div>
                        ${noteBlock}
                        ${buyPriceBlock}
                    </div>
                    <div class="cart-item-total" style="font-weight:700;font-size:0.95rem;text-align:right;color:var(--primary);flex-shrink:0;white-space:nowrap;">${formatRupiah(item.total)}</div>
                </div>
                <!-- Baris 2: Kontrol kemasan + qty + hapus -->
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;background:var(--surface-2);padding:7px 10px;border-radius:var(--radius-md);border:1px solid var(--border-color);">
                    <div class="dropdown" style="flex:1;min-width:100px;">
                        <button type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%;font-weight:600;color:var(--primary);background-color:var(--primary-bg);border:1px solid rgba(230,57,70,0.2);border-radius:var(--radius-sm);padding:7px 26px 7px 10px;text-align:left;font-size:11px;background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23e63946' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E&quot;);background-repeat:no-repeat;background-position:right 8px center;background-size:10px;cursor:pointer;">
                            ${escapeHtml(activeLabel)}
                        </button>
                        <ul class="dropdown-menu shadow-lg" style="background:var(--surface-2); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:4px 0; min-width:100%; margin-top:4px;">
                            ${levelOptionsHTML}
                        </ul>
                    </div>
                    <div style="display:flex;align-items:center;background:var(--surface-1);border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border-color);box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                        <button type="button" onclick="updateQty(${item.id}, -1)" style="border:none;background:none;color:var(--text-secondary);padding:5px 11px;cursor:pointer;transition:all 150ms;"><i class="bi bi-dash"></i></button>
                        <span style="font-weight:700;width:30px;text-align:center;font-size:0.9rem;color:var(--text-primary);">${item.quantity}</span>
                        <button type="button" onclick="updateQty(${item.id}, 1)" style="border:none;background:none;color:var(--primary);padding:5px 11px;cursor:pointer;transition:all 150ms;"><i class="bi bi-plus"></i></button>
                    </div>
                    <button type="button" onclick="cart = cart.filter(i => i.id != ${item.id}); renderCart();" style="border:1px solid rgba(230,57,70,0.2);background:var(--danger-bg);color:var(--danger);cursor:pointer;padding:5px 11px;border-radius:var(--radius-sm);transition:all 150ms;"><i class="bi bi-trash3"></i></button>
                </div>
                <!-- Baris 3: Harga Custom -->
                <label style="display:flex;align-items:center;gap:8px;margin-top:10px;font-size:11px;color:var(--text-secondary);cursor:pointer;user-select:none;font-weight:500;">
                    <input type="checkbox" ${customChecked} onchange="togglePosCustomPrice(${item.id}, this.checked, this)" style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;margin:0;">
                    <span><i class="bi bi-pencil-square" style="margin-right:2px;"></i> Terapkan Harga Custom</span>
                </label>
                <div style="margin-top:8px;${customWrapStyle}">
                    <input type="number" min="0" step="1" value="${customPriceVal}" placeholder="Total harga untuk ${item.quantity} ${escapeHtml(item.unit_name)}"
                        oninput="onPosCustomPriceInput(${item.id}, this)"
                        class="form-control-dark cart-custom-price-input" style="width:100%;font-size:0.85rem;padding:8px 10px;border:1px solid var(--border-color);border-radius:var(--radius-sm);background:var(--bg-input);">
                    <div class="cart-custom-markup" style="font-size:0.75rem; margin-top:6px; font-weight:600; background:var(--surface-2); padding:6px 8px; border-radius:4px; border:1px solid var(--border-color); display:none;"></div>
                </div>`;
        
        if (saleMode === 'mix') {
            const currentOverride = item.mix_override_mode ?? mixDefaultPrice;
            const isRetail = currentOverride === 'retail';
            const isWholesale = currentOverride === 'wholesale';
            html += `
                <!-- Baris 4: Mix Mode Toggle -->
                <div style="display:flex; align-items:center; gap:8px; margin-top:10px; font-size:11px; padding:8px 12px; background:var(--surface-2); border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                    <i class="bi bi-shuffle" style="color:var(--primary); font-size:1rem;"></i>
                    <span style="font-weight:600; color:var(--text-primary); margin-right:auto;">Harga:</span>
                    <div class="pos-segmented">
                        <button type="button" onclick="toggleItemMixMode(${item.id}, 'retail')" class="${isRetail ? 'active' : ''}">Ecer</button>
                        <button type="button" onclick="toggleItemMixMode(${item.id}, 'wholesale')" class="${isWholesale ? 'active' : ''}">Grosir</button>
                    </div>
                </div>
            `;
        }
        
        html += `
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
        localStorage.setItem('pos_autosave', JSON.stringify({ cart, saleMode, mixDefaultPrice, ts: Date.now() }));
    } catch(e) {}
}
function autoRestoreCart() {
    try {
        const saved = JSON.parse(localStorage.getItem('pos_autosave') || 'null');
        if (saved && saved.cart && saved.cart.length > 0) {
            // Only restore if saved within last 12 hours
            if (Date.now() - saved.ts < 12 * 60 * 60 * 1000) {
                cart = saved.cart;
                if (saved.mixDefaultPrice) mixDefaultPrice = saved.mixDefaultPrice;
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

window.clearCartConfirm = async function() {
    if (cart.length === 0) {
        showToast('Keranjang sudah kosong', 'info');
        return;
    }
    const confirmed = await AppModal.show({
        title: 'Kosongkan Keranjang?',
        subtitle: 'Hapus Semua Produk',
        bodyHTML: '<div style="text-align:center; padding:10px 0;"><i class="bi bi-trash3" style="font-size:3rem; color:var(--danger); display:block; margin-bottom:12px;"></i><p style="font-size:14px; margin-bottom:8px;">Anda yakin ingin menghapus semua produk dari keranjang?</p><p style="font-size:13px; color:var(--text-muted);">Tindakan ini tidak dapat dibatalkan.</p></div>',
        icon: 'bi-trash3',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        submitText: 'Ya, Kosongkan',
        cancelText: 'Batal'
    });
    
    if (confirmed) {
        cart = [];
        currentDraftId = null;
        if (editSaleId) editSaleId = null;
        clearAutoSave();
        renderCart();
        
        // Remove edit param from URL silently if exists
        if (window.history.replaceState) {
            const url = new URL(window.location);
            if (url.searchParams.has('edit')) {
                url.searchParams.delete('edit');
                window.history.replaceState(null, '', url);
            }
        }
        
        showToast('Keranjang berhasil dikosongkan', 'success');
        searchInput?.focus();
    }
};

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

window.loadDraft = async function(id) {
    if (cart.length > 0) {
        const ok = await AppModal.confirm('Muat Draft', 'Keranjang saat ini tidak kosong. Timpa dengan draft ini?', 'Ya, Timpa', 'var(--warning)');
        if (!ok) return;
    }
    
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

window.deleteDraft = async function(id) {
    const ok = await AppModal.confirm('Hapus Draft', 'Draft ini akan dihapus permanen dan tidak bisa dikembalikan.', 'Ya, Hapus', 'var(--danger)');
    if (!ok) return;
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

            const isEditMode = !!editSaleId;

            // Clear current draft if checkout success
            if (currentDraftId) {
                let drafts = JSON.parse(localStorage.getItem('pos_drafts') || '[]');
                drafts = drafts.filter(x => x.id !== currentDraftId);
                localStorage.setItem('pos_drafts', JSON.stringify(drafts));
            }

            if (isEditMode) {
                const banner = document.getElementById('posEditBanner');
                if (banner) banner.remove();
                editSaleId = null;
                showToast(`Perubahan transaksi berhasil disimpan!`, 'success');
            }

            const printCart = cart.map(i => ({ ...i }));
            const printTotal = calculateTotal();
            const invoiceNo = result.invoice || result.id || ('OFF-' + Date.now());
            const currentSaleMode = saleMode;
            const currentCustomer = selectedCustomer ? { ...selectedCustomer } : null;
            const mixInfo = getMixDiscountInfo();
            const currentChainInfo = getChainInfo();

            cart = [];
            currentDraftId = null;
            clearAutoSave();
            renderCart();
            btnCheckout.innerHTML = 'BAYAR SEKARANG';
            btnCheckout.disabled = false;

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

            const hasSavedPrinter = tp?.hasSavedDevice?.() ?? false;
            const printerConnected = tp?.isConnected?.() ?? false;
            const shouldShowPrintBtn = printerConnected || hasSavedPrinter || (tp?.getDriver?.() === 'rawbt');

            let modalPromise;
            try {
            modalPromise = AppModal.show({
                title: isEditMode ? 'Transaksi Diperbarui' : (currentChainInfo.isContinuation ? 'Struk Lanjutan Berhasil' : 'Transaksi Berhasil'),
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
                            <i class="bi bi-credit-card"></i> Tunai · ${currentSaleMode === 'retail' ? 'Ecer' : (currentSaleMode === 'wholesale' ? 'Grosir' : 'Mix')}
                        </div>
                        ${mixInfo.totalDiscount > 0 ? `<div style="margin-top:8px; display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700; color:var(--success); background:var(--success-bg); padding:4px 10px; border-radius:12px;">🎉 Hemat ${formatRupiah(mixInfo.totalDiscount)}</div>` : ''}
                    </div>

                    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:14px;margin-bottom:16px;max-height:260px;overflow-y:auto;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid var(--border-color);">
                            <span style="font-size:var(--font-size-xs);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Invoice</span>
                            <span style="font-size:var(--font-size-xs);color:var(--text-muted);font-family:monospace;">${invoiceNo}</span>
                        </div>
                        ${invoiceItemsHTML}
                        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:10px;margin-top:4px;">
                            <span style="font-weight:700;font-size:var(--font-size-sm);">Total Struk Ini</span>
                            <span style="font-weight:800;font-size:var(--font-size-md);color:var(--primary);">${formatRupiah(printTotal)}</span>
                        </div>
                        ${currentChainInfo.isContinuation ? `
                        <div style="margin-top:10px; padding-top:10px; border-top:1px dashed var(--border-color);">
                            <div style="font-size:10px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
                                Rekap Semua Struk
                            </div>
                            ${currentChainInfo.allPreviousInvoices.map((inv, idx) => `
                            <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:var(--text-muted);margin-bottom:4px;">
                                <span>Struk ${idx+1} <span style="font-size:10px;font-family:monospace;">(${escapeHtml((inv.invoiceNo||'').slice(-6))})</span></span>
                                <strong>${formatRupiah(inv.total||0)}</strong>
                            </div>`).join('')}
                            <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:var(--text-primary);margin-bottom:4px;font-weight:700;">
                                <span>Struk ${currentChainInfo.chainNumber+1} (Ini) <span style="font-size:10px;font-family:monospace;">(${invoiceNo.slice(-6)})</span></span>
                                <strong>${formatRupiah(printTotal)}</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:rgba(230,57,70,0.08);border:1px solid rgba(230,57,70,0.2);border-radius:6px;margin-top:6px;">
                                <span style="font-size:12px;font-weight:800;color:var(--primary);">GRAND TOTAL GABUNGAN</span>
                                <span style="font-size:14px;font-weight:800;color:var(--primary);">${formatRupiah(currentChainInfo.grandTotal)}</span>
                            </div>
                        </div>
                        ` : ''}
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

                        <button type="button" id="btnConnectPrinter" class="btn-outline-custom" style="width:100%;padding:14px;font-weight:600;display:${shouldShowPrintBtn ? 'none' : 'flex'};align-items:center;justify-content:center;gap:8px;font-size:var(--font-size-sm);border-radius:var(--radius-md);border:1px solid var(--border-color);cursor:pointer;transition:all 0.2s;">
                            <i class="bi bi-bluetooth"></i> Hubungkan Printer Bluetooth
                        </button>
                        <button type="button" id="btnPrintReceipt" class="btn-primary-custom" style="width:100%;padding:14px;font-weight:600;display:${shouldShowPrintBtn ? 'flex' : 'none'};align-items:center;justify-content:center;gap:8px;font-size:var(--font-size-sm);border-radius:var(--radius-md);border:none;cursor:pointer;transition:all 0.2s;">
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
                extraBtnText: '<i class="bi bi-pencil-square"></i> Edit',
                cancelText: 'Tutup',
                onExtra: async () => {
                    handleEditCheckoutTransaction(result.id || result.invoice, invoiceNo, printCart, currentCustomer, currentSaleMode);
                    return true;
                },
                onSubmit: async () => {
                    editSaleId = null;
                    clearChainSession();
                    const banner = document.getElementById('posEditBanner');
                    if (banner) banner.remove();
                    btnCheckout.innerHTML = 'BAYAR SEKARANG';
                    searchInput?.focus();
                    return true;
                },
            });

            setTimeout(() => setupPrinterButtons(printCart, printTotal, invoiceNo, currentSaleMode, mixInfo, currentChainInfo), 150);
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
        console.error('Checkout error:', err);
        showToast('Gagal memproses transaksi: ' + err.message, 'error');
        btnCheckout.innerHTML = 'BAYAR SEKARANG';
        btnCheckout.disabled = false;
    }
}

function setupPrinterButtons(printCart, printTotal, invoiceNo, printSaleMode, mixInfo, chainInfo) {
    const btnConnect = document.getElementById('btnConnectPrinter');
    const btnPrint = document.getElementById('btnPrintReceipt');
    const btnBrowser = document.getElementById('btnPrintBrowser');
    const statusBar = document.getElementById('printerStatusBar');

    if (!btnConnect || !btnPrint) return;

    const tp = getThermalPrinterSafe();
    if (!tp) return;

    if (btnBrowser) {
        btnBrowser.onclick = () => {
            tp.printBrowserFallback(printCart, printTotal, invoiceNo, {
                storeSettings: STORE_SETTINGS,
                paymentMethod: 'Tunai',
                saleMode: printSaleMode,
                mixInfo: mixInfo,
                chainInfo: chainInfo,
            });
        };
    }

    function showConnected(deviceName) {
        btnConnect.style.display = 'none';
        btnPrint.style.display = 'flex';
        btnPrint.innerHTML = '<i class="bi bi-printer"></i> Cetak Struk (Bluetooth)';
        
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
        if (hasSaved) {
            btnConnect.style.display = 'none';
            btnPrint.style.display = 'flex';
            btnPrint.innerHTML = '<i class="bi bi-printer"></i> Cetak Struk (Bluetooth)';
        } else {
            btnConnect.style.display = 'flex';
            btnConnect.innerHTML = '<i class="bi bi-bluetooth"></i> Hubungkan Printer Bluetooth';
            btnConnect.disabled = false;
            btnPrint.style.display = 'none';
        }
        if (statusBar) statusBar.style.display = 'none';

        const btnNew = document.getElementById('btnConnectNewPrinter');
        if (btnNew) btnNew.style.display = 'none';
    }

    function setupConnectButton() {
        btnConnect.onclick = async () => {
            try {
                btnConnect.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menghubungkan...';
                btnConnect.disabled = true;
                const connected = await tp.connect(true, false);
                if (!connected || !tp.isConnected()) {
                    showDisconnected(tp.hasSavedDevice());
                    setupConnectButton();
                    return;
                }
                showConnected(tp.device?.name);
                showToast('Printer Bluetooth terhubung dengan baik', 'success');
                setupPrintButton();
                if (!STORE_SETTINGS || STORE_SETTINGS.auto_print_checkout !== '0') {
                    btnPrint.click();
                }
            } catch (e) {
                showDisconnected(tp.hasSavedDevice());
                console.error('[POS] Printer connection error:', e);
                showToast(e.message || 'Gagal menghubungkan printer', 'error');
                setupConnectButton();
            }
        };
    }

    function setupPrintButton() {
        btnPrint.onclick = async () => {
            try {
                btnPrint.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Mencetak Struk...';
                btnPrint.disabled = true;

                const driverMode = tp.getDriver();
                if (driverMode === 'web_bluetooth' && !tp.isConnected()) {
                    btnPrint.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menghubungkan ulang...';
                    const ok = await tp.tryAutoReconnect();
                    if (!ok) {
                        showDisconnected(tp.hasSavedDevice());
                        setupConnectButton();
                        showToast('Printer Bluetooth terputus. Klik "Hubungkan Printer Bluetooth".', 'warning');
                        btnPrint.innerHTML = '<i class="bi bi-printer"></i> Hubungkan & Cetak Struk';
                        btnPrint.disabled = false;
                        btnPrint.onclick = async () => {
                            const conn = await tp.connect(true, false);
                            if (conn) {
                                setupPrintButton();
                                btnPrint.click();
                            }
                        };
                        return;
                    }
                    showConnected(tp.device?.name);
                }

                await tp.print(printCart, printTotal, invoiceNo, {
                    storeSettings: STORE_SETTINGS,
                    paymentMethod: 'Tunai',
                    saleMode: printSaleMode,
                    mixInfo: mixInfo,
                    chainInfo: chainInfo,
                });
                btnPrint.innerHTML = '<i class="bi bi-printer"></i> Cetak Ulang Struk';
                btnPrint.disabled = false;
                showToast('Struk berhasil dicetak ke printer thermal', 'success');
                showHistorySaveConfirmation(invoiceNo, printTotal, printCart, chainInfo);
            } catch (e) {
                btnPrint.innerHTML = '<i class="bi bi-printer"></i> Cetak Struk';
                btnPrint.disabled = false;
                console.error('[POS] Print error:', e);
                showToast(e.message || 'Gagal mencetak struk', 'error');
            }
        };
    }

    setupPrintButton();
    setupConnectButton();

    if (tp.isConnected()) {
        showConnected(tp.device?.name);
    } else if (tp.hasSavedDevice()) {
        showConnected(tp.device?.name || 'Printer Tersimpan');
    }

    // Auto-trigger print on checkout
    if (!STORE_SETTINGS || STORE_SETTINGS.auto_print_checkout !== '0') {
        setTimeout(() => {
            if (btnPrint && typeof btnPrint.click === 'function') {
                btnPrint.click();
            }
        }, 200);
    }
} // end setupPrinterButtons

function handleEditCheckoutTransaction(saleId, invoiceNo, savedCart, savedCustomer, savedSaleMode) {
    editSaleId = saleId;

    // 1. Restore cart items with exact details
    cart = savedCart.map(item => ({
        ...item,
        quantity: item.quantity,
        unit_price: item.unit_price,
        total: item.total
    }));

    // 2. Restore selected customer if any
    if (savedCustomer && savedCustomer.id) {
        selectedCustomer = { ...savedCustomer };
        // Update customer selector UI
        const labelEl = document.getElementById('customerSelectorLabel');
        const iconEl = document.getElementById('customerSelectorIcon');
        const clearBtn = document.getElementById('btnClearCustomer');
        if (labelEl) labelEl.textContent = savedCustomer.name || savedCustomer.full_name || 'Pelanggan';
        if (iconEl) { iconEl.className = 'bi bi-person-check'; iconEl.style.color = 'var(--success)'; }
        if (clearBtn) clearBtn.style.display = 'flex';
    } else {
        selectedCustomer = null;
        const labelEl = document.getElementById('customerSelectorLabel');
        const iconEl = document.getElementById('customerSelectorIcon');
        const clearBtn = document.getElementById('btnClearCustomer');
        if (labelEl) labelEl.textContent = 'Umum';
        if (iconEl) { iconEl.className = 'bi bi-person'; iconEl.style.color = 'var(--primary)'; }
        if (clearBtn) clearBtn.style.display = 'none';
    }

    // 3. Restore sale mode using the existing setSaleMode function
    if (savedSaleMode && typeof setSaleMode === 'function') {
        setSaleMode(savedSaleMode);
    }

    renderCart();

    // 4. Create or update notice banner (insert before #cartItems)
    let banner = document.getElementById('posEditBanner');
    if (!banner) {
        banner = document.createElement('div');
        banner.id = 'posEditBanner';
        const cartItems = document.getElementById('cartItems');
        if (cartItems && cartItems.parentNode) {
            cartItems.parentNode.insertBefore(banner, cartItems);
        }
    }
    if (banner) {
        banner.style.cssText = 'background:var(--warning-bg); color:var(--warning); border:1px solid var(--warning); border-radius:var(--radius-md); padding:10px 14px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; font-size:var(--font-size-xs); font-weight:700;';
        banner.innerHTML = `
            <div style="display:flex;align-items:center;gap:6px;"><i class="bi bi-pencil-square"></i> <span>MENGEDIT TRANSAKSI: <strong>${escapeHtml(invoiceNo)}</strong></span></div>
            <button type="button" onclick="cancelEditCheckoutMode()" style="padding:4px 8px; font-size:11px; background:transparent; border:1px solid var(--danger); color:var(--danger); border-radius:4px; cursor:pointer; font-weight:600;">Batal Edit</button>
        `;
    }

    // 5. Update checkout button text
    if (btnCheckout) {
        btnCheckout.innerHTML = `<i class="bi bi-check2-circle"></i> SIMPAN PERUBAHAN (${escapeHtml(invoiceNo)})`;
        btnCheckout.disabled = false;
    }

    showToast(`Data transaksi ${invoiceNo} dimuat kembali ke keranjang`, 'info');
}

window.cancelEditCheckoutMode = function() {
    editSaleId = null;
    cart = [];
    if (typeof clearCustomer === 'function') {
        clearCustomer();
    } else {
        selectedCustomer = null;
        if (typeof updateCustomerUI === 'function') updateCustomerUI();
    }
    const banner = document.getElementById('posEditBanner');
    if (banner) banner.remove();
    if (btnCheckout) btnCheckout.innerHTML = 'BAYAR SEKARANG';
    renderCart();
    showToast('Mode edit dibatalkan', 'info');
};

function showHistorySaveConfirmation(invoiceNo, total, cartItems, chainInfo) {
    // Close the checkout modal first, then show history confirmation
    AppModal.close();

    const savedChainInfo = chainInfo ? { ...chainInfo } : null;
    const savedInvoiceNo = invoiceNo;
    const savedTotal = total;

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
                    ${(savedChainInfo && savedChainInfo.isContinuation) ? `
                    <div style="margin-top:10px;background:rgba(99,102,241,0.07);border:1px solid rgba(99,102,241,0.2);border-radius:8px;padding:10px;font-size:11px;text-align:left;">
                        <div style="font-weight:700;color:#818cf8;margin-bottom:6px;text-transform:uppercase;font-size:10px;letter-spacing:0.5px;">🔗 Rekap Semua Struk</div>
                        ${savedChainInfo.allPreviousInvoices.map((inv, idx) => `
                        <div style="display:flex;justify-content:space-between;color:var(--text-muted);margin-bottom:3px;">
                            <span>Struk ${idx+1} <span style="font-family:monospace;font-size:9px;">(${(inv.invoiceNo||'').slice(-6)})</span></span>
                            <strong>${formatRupiah(inv.total||0)}</strong>
                        </div>`).join('')}
                        <div style="display:flex;justify-content:space-between;color:var(--text-primary);font-weight:700;margin-bottom:3px;">
                            <span>Struk ${savedChainInfo.chainNumber+1} (Ini) <span style="font-family:monospace;font-size:9px;">(${invoiceNo.slice(-6)})</span></span>
                            <strong>${formatRupiah(total)}</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;border-top:1px dashed rgba(99,102,241,0.3);padding-top:6px;margin-top:4px;color:#a5b4fc;font-weight:800;font-size:12px;">
                            <span>GRAND TOTAL GABUNGAN</span>
                            <span>${formatRupiah(savedChainInfo.grandTotal)}</span>
                        </div>
                    </div>` : ''}
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
                <div style="background:var(--surface-2);border-radius:var(--radius-sm);padding:10px 12px;font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:12px;">
                    <i class="bi bi-info-circle"></i> ${cartItems.length} item · ${new Date().toLocaleString('id-ID', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})}
                </div>

                <!-- Tombol Lanjut ke Invoice Berikutnya -->
                <button type="button" onclick="startNextInvoiceChain('${invoiceNo}', ${total})" style="width:100%; padding:12px 14px; font-weight:700; background:linear-gradient(135deg, rgba(79,70,229,0.1) 0%, rgba(99,102,241,0.15) 100%); border:1.5px dashed var(--primary); color:var(--primary); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; gap:8px; font-size:var(--font-size-sm); cursor:pointer; transition:all 0.2s; margin-bottom:4px;" onmouseover="this.style.background='var(--primary-bg)'" onmouseout="this.style.background='linear-gradient(135deg, rgba(79,70,229,0.1) 0%, rgba(99,102,241,0.15) 100%)'">
                    <i class="bi bi-link-45deg" style="font-size:1.3rem;"></i>
                    <span>🧾 Lanjut ke Invoice Berikutnya</span>
                </button>
                <div style="font-size:10.5px; color:var(--text-muted); text-align:center; margin-bottom:4px;">
                    Gunakan ini jika pembeli menambah barang belanjaan setelah cetak struk.
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
            unit_abbr: unit.substring(0, 5),
            sell_price_retail: unitPrice,
            sell_price_wholesale: unitPrice,
            qty_prices: []
        }],
        level: 1,
        unit_name: unit,
        unit_abbr: unit.substring(0, 5),
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
        const isMerged = urlParams.get('merged') || localStorage.getItem('pos_merged_invoice_draft');

        if (editId) {
            editSaleId = editId;
            loadSaleForEdit(editId);
        } else if (isMerged) {
            loadMergedInvoiceDraft();
        } else {
            autoRestoreCart();
        }
        
        renderCart();

        // Check if there is a pending product to add from a scan on another page
        (function checkPendingAddProduct() {
            try {
                const pendingRaw = localStorage.getItem('pos_pending_add_product');
                if (!pendingRaw) return;
                localStorage.removeItem('pos_pending_add_product');
                
                const pending = JSON.parse(pendingRaw);
                if (!pending || !pending.id) return;
                
                setTimeout(async () => {
                    let product = null;
                    if (window._posProductsCatalog && window._posProductsCatalog.length > 0) {
                        product = window._posProductsCatalog.find(p => p.id == pending.id);
                    }
                    if (!product && typeof OfflineDB !== 'undefined') {
                        try { product = await OfflineDB.getProductById(pending.id); } catch(e){}
                    }
                    if (!product && pending.product) {
                        product = pending.product;
                    }
                    if (!product) {
                        try {
                            const res = await fetch(`${BASE_URL}api/products/${pending.id}?pos=1`);
                            if (res.ok) product = await res.json();
                        } catch(e) {}
                    }
                    
                    if (product && typeof addProductToCart === 'function') {
                        addProductToCart(product);
                        if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
                        if (typeof showToast === 'function') {
                            showToast(`✅ ${product.short_label || product.full_name || 'Produk'} ditambahkan ke keranjang`, 'success');
                        }
                    }
                }, 400);
            } catch(e) {}
        })();
    } catch (err) {
        console.error('POS init error:', err);
        showToast('Gagal memuat halaman kasir. Muat ulang halaman.', 'error');
    }
});

async function loadSaleForEdit(id) {
    try {
        let sale = null;

        // 1. Instant 0ms check from localStorage payload (cached when clicking Edit from detail page)
        try {
            const cachedRaw = localStorage.getItem('pos_edit_sale_payload');
            if (cachedRaw) {
                const parsed = JSON.parse(cachedRaw);
                if (parsed && (parsed.id == id || parsed.invoice_number == id)) {
                    sale = parsed;
                }
            }
        } catch(e) {}

        // 2. Fetch from server if not cached locally
        if (!sale) {
            showToast('Memuat data transaksi...', 'info');
            try {
                const res = await fetch(`${BASE_URL}api/sales/invoice/${id}`);
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.transaction) {
                        sale = data.transaction;
                    }
                }
            } catch(e) {}
        }

        // 3. Fallback endpoint /sales/{id}?format=json if still null
        if (!sale) {
            try {
                const res2 = await fetch(`${BASE_URL}sales/${id}?format=json`);
                if (res2.ok) {
                    const data2 = await res2.json();
                    sale = data2.sale || data2;
                }
            } catch(e) {}
        }

        if (sale && (sale.items || sale.details)) {
            const items = sale.items || sale.details || [];
            const targetMode = sale.sale_mode || 'retail'; // 'retail', 'wholesale', or 'mix'

            // 1. Restore Customer selection
            if (sale.customer_id && sale.customer_id != 0) {
                const custName = sale.customer_name || 'Pelanggan #' + sale.customer_id;
                selectCustomer({ id: sale.customer_id, name: custName, phone: sale.customer_phone || '' });
            } else {
                selectCustomer(null);
            }
            
            // 2. Build cart items with 100% precision
            cart = await Promise.all(items.map(async item => {
                const isCustom = item.custom_name !== null && item.custom_name !== undefined || item.product_id === 'CUSTOM' || String(item.product_id).toUpperCase() === 'CUSTOM';
                const printName = item.invoice_name || item.full_name || item.custom_name || item.name || 'Produk';
                const savedLevel = parseInt(item.packaging_level || item.level || 1, 10);
                const savedQuantity = parseFloat(item.quantity) || 1;
                const savedTotalPrice = parseFloat(item.total_price != null ? item.total_price : item.total) || 0;
                const savedUnitPrice = parseFloat(item.unit_price) || (savedQuantity > 0 ? savedTotalPrice / savedQuantity : 0);

                let packagings = [];
                let isItemValid = true;

                if (!isCustom && item.product_id) {
                    // Check in-memory catalog first (0ms)
                    if (window._posProductsCatalog && window._posProductsCatalog.length > 0) {
                        const localProd = window._posProductsCatalog.find(p => p.id == item.product_id);
                        if (localProd && localProd.packagings && localProd.packagings.length > 0) {
                            packagings = localProd.packagings;
                        }
                    }

                    // Check IndexedDB fallback (0ms)
                    if (packagings.length === 0 && typeof OfflineDB !== 'undefined') {
                        try {
                            const dbProd = await OfflineDB.getProductById(item.product_id);
                            if (dbProd && dbProd.packagings && dbProd.packagings.length > 0) {
                                packagings = dbProd.packagings;
                            }
                        } catch(e){}
                    }

                    // Network fetch only as last resort if not found locally
                    if (packagings.length === 0 && navigator.onLine) {
                        try {
                            const pRes = await fetch(`${BASE_URL}api/products/${item.product_id}?pos=1`);
                            if (pRes.ok) {
                                const pData = await pRes.json();
                                if (pData && pData.packagings && pData.packagings.length > 0) {
                                    packagings = pData.packagings;
                                }
                            }
                        } catch(e) {}
                    }
                }
                
                // Fallback packagings if fetch fails or is custom
                if (isCustom || packagings.length === 0) {
                    packagings = [{
                        level: savedLevel,
                        unit_name: item.unit_name || 'Pcs',
                        unit_abbr: item.unit_abbr || (item.unit_name ? item.unit_name.substring(0, 5) : 'Pcs'),
                        sell_price_retail: savedUnitPrice,
                        sell_price_wholesale: savedUnitPrice,
                        buy_price: parseFloat(item.buy_price) || 0,
                        ppn_pct: 0,
                        discount_value: 0
                    }];
                }

                // Precision check: verify if price matches current catalog packaging price or is custom
                let isCustomPrice = isCustom;
                let detectedOverrideMode = item.mix_override_mode || undefined;

                if (!isCustom) {
                    const curPkg = packagings.find(p => parseInt(p.level, 10) === savedLevel) || packagings[0];
                    if (curPkg) {
                        const expectedRetail = typeof QtyPricing !== 'undefined'
                            ? QtyPricing.calculateTotalPrice(curPkg, 'retail', 1, false, null, packagings)
                            : (parseFloat(curPkg.sell_price_retail) || 0);
                        const expectedWholesale = typeof QtyPricing !== 'undefined'
                            ? QtyPricing.calculateTotalPrice(curPkg, 'wholesale', 1, false, null, packagings)
                            : (parseFloat(curPkg.sell_price_wholesale) || expectedRetail);

                        const eps = 1;
                        if (targetMode === 'mix') {
                            if (Math.abs(savedUnitPrice - expectedRetail) <= eps) {
                                isCustomPrice = false;
                                detectedOverrideMode = 'retail';
                            } else if (Math.abs(savedUnitPrice - expectedWholesale) <= eps) {
                                isCustomPrice = false;
                                detectedOverrideMode = 'wholesale';
                            } else {
                                isCustomPrice = true;
                            }
                        } else if (targetMode === 'wholesale') {
                            isCustomPrice = Math.abs(savedUnitPrice - expectedWholesale) > eps;
                        } else {
                            isCustomPrice = Math.abs(savedUnitPrice - expectedRetail) > eps;
                        }
                    }
                }

                return {
                    id: Date.now() + Math.random(),
                    product_id: isCustom ? 'CUSTOM' : item.product_id,
                    is_custom: isCustom,
                    name: printName,
                    print_name: printName,
                    product_name: printName,
                    photo: item.photo || null,
                    packagings: packagings,
                    level: savedLevel,
                    unit_name: item.unit_name || (packagings.find(p => parseInt(p.level, 10) === savedLevel)?.unit_name) || 'Pcs',
                    unit_abbr: item.unit_abbr || (item.unit_name ? item.unit_name.substring(0, 5) : 'Pcs'),
                    quantity: savedQuantity,
                    use_custom_price: isCustomPrice,
                    custom_line_total: isCustomPrice ? savedTotalPrice : null,
                    custom_unit_price: isCustomPrice ? savedUnitPrice : null,
                    custom_price_draft: isCustomPrice ? String(savedTotalPrice) : undefined,
                    unit_price: savedUnitPrice,
                    total: savedTotalPrice,
                    price_note: isCustom ? 'Barang Custom' : (isCustomPrice ? 'Harga Custom (Presisi Transaksi)' : ''),
                    mix_override_mode: detectedOverrideMode
                };
            }));

            // Restore sale mode
            setSaleMode(targetMode);

            // Insert edit banner
            let banner = document.getElementById('posEditBanner');
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'posEditBanner';
                const pageSection = document.querySelector('.page-section');
                const posHeader = pageSection?.children[0];
                if (pageSection) {
                    if (posHeader && posHeader.nextSibling) {
                        pageSection.insertBefore(banner, posHeader.nextSibling);
                    } else {
                        pageSection.prepend(banner);
                    }
                }
            }
            if (banner) {
                banner.style.cssText = 'background:var(--warning-bg); color:var(--warning); border:1px solid var(--warning); border-radius:var(--radius-md); padding:10px 14px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; font-size:var(--font-size-xs); font-weight:700;';
                banner.innerHTML = `
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-pencil-square" style="font-size:1.1rem;"></i>
                        <span>MODE EDIT TRANSAKSI: <strong style="color:var(--text-primary); font-family:monospace;">${escapeHtml(sale.invoice_number)}</strong></span>
                    </div>
                    <a href="${BASE_URL}sales" class="btn-outline-custom" style="padding:4px 10px; font-size:11px; text-decoration:none; background:transparent; border:1px solid var(--danger); color:var(--danger); border-radius:4px; font-weight:600;">Batal Edit</a>
                `;
            }

            // Update checkout button text
            const btnCheckoutEl = document.getElementById('btnCheckout');
            if (btnCheckoutEl) {
                btnCheckoutEl.innerHTML = `<i class="bi bi-save"></i> SIMPAN PERUBAHAN (${escapeHtml(sale.invoice_number)})`;
                btnCheckoutEl.disabled = false;
            }

            cart.forEach(it => recalcItemPrice(it));
            renderCart();
            showToast(`✅ Data transaksi ${sale.invoice_number} berhasil dimuat ke keranjang kasir`, 'success');
        } else {
            showToast('Gagal memuat transaksi', 'error');
            editSaleId = null;
        }
    } catch (e) {
        console.error('loadSaleForEdit error:', e);
        showToast('Error memuat transaksi: ' + e.message, 'error');
        editSaleId = null;
    }
}


/**
 * Load merged invoices draft payload into POS cart
 */
async function loadMergedInvoiceDraft() {
    try {
        const jsonStr = localStorage.getItem('pos_merged_invoice_draft');
        if (!jsonStr) return;
        localStorage.removeItem('pos_merged_invoice_draft'); // Clear once read

        const draft = JSON.parse(jsonStr);
        if (!draft || !draft.raw_items || draft.raw_items.length === 0) return;

        showToast('Memproses invoice gabungan...', 'info');

        const targetMode = draft.target_mode || 'wholesale';
        const invoiceNumbers = draft.merged_invoices || [];

        // 1. Restore Customer selection if applicable
        if (draft.customer && draft.customer.id) {
            selectCustomer(draft.customer);
        } else {
            selectCustomer(null);
        }

        // 2. Reconstruct items for POS cart with full packagings
        const reconstructedItems = await Promise.all(draft.raw_items.map(async item => {
            const isCustom = item.is_custom || item.product_id === 'CUSTOM' || String(item.product_id).toUpperCase() === 'CUSTOM';
            const printName = item.print_name || item.name || item.product_name || 'Barang Custom';

            let packagings = [];
            let isItemValid = true;

            if (!isCustom) {
                try {
                    let product = null;
                    if (window._posProductsCatalog && window._posProductsCatalog.length > 0) {
                        product = window._posProductsCatalog.find(p => p.id == item.product_id);
                    }
                    if (!product) {
                        const pRes = await fetch(`${BASE_URL}api/products/${item.product_id}?pos=1`);
                        if (pRes.ok) product = await pRes.json();
                    }
                    if (product && product.packagings && product.packagings.length > 0) {
                        packagings = product.packagings;
                    } else {
                        isItemValid = false;
                    }
                } catch(e) {
                    isItemValid = false;
                }
            }

            if (isCustom || !isItemValid || packagings.length === 0) {
                packagings = [{
                    level: item.level || 1,
                    unit_name: item.unit_name || 'Pcs',
                    unit_abbr: item.unit_abbr || (item.unit_name ? item.unit_name.substring(0, 5) : 'Pcs'),
                    sell_price_retail: item.unit_price || 0,
                    sell_price_wholesale: item.unit_price || 0,
                    buy_price: item.buy_price || 0,
                    ppn_pct: 0,
                    discount_value: 0
                }];
            }

            const savedLevel = parseInt(item.level) || 1;
            const savedQuantity = parseFloat(item.quantity) || 1;
            const savedTotalPrice = parseFloat(item.total);
            const savedUnitPrice = parseFloat(item.unit_price) || (savedQuantity > 0 ? savedTotalPrice / savedQuantity : 0);

            let isCustomPrice = isCustom;

            if (!isCustom && isItemValid) {
                const curPkg = packagings.find(p => parseInt(p.level) === savedLevel) || packagings[0];
                if (curPkg) {
                    const expectedPrice = _calcExpectedUnitPrice(curPkg, targetMode, savedQuantity, packagings);
                    const eps = 1;
                    if (Math.abs(savedUnitPrice - expectedPrice) >= eps) {
                        isCustomPrice = true;
                    } else {
                        isCustomPrice = false;
                    }
                }
            }

            return {
                id: Date.now() + Math.random(),
                product_id: isCustom ? 'CUSTOM' : item.product_id,
                is_custom: isCustom,
                name: printName,
                print_name: printName,
                product_name: printName,
                packagings: packagings,
                level: isCustom ? 1 : savedLevel,
                unit_name: item.unit_name || 'Pcs',
                unit_abbr: item.unit_abbr || (item.unit_name ? item.unit_name.substring(0, 5) : 'Pcs'),
                quantity: savedQuantity,
                use_custom_price: isCustomPrice,
                custom_line_total: isCustomPrice ? savedTotalPrice : null,
                custom_price_draft: isCustomPrice ? String(savedTotalPrice) : undefined,
                unit_price: savedUnitPrice,
                total: savedTotalPrice,
                price_note: isCustom ? 'Barang Custom' : (isCustomPrice ? 'Harga Custom (Gabungan)' : '')
            };
        }));

        // 3. Aggregate items (same catalog product + same level + non-custom price)
        const finalCart = [];
        for (const item of reconstructedItems) {
            if (item.is_custom || item.use_custom_price) {
                const existingCustom = finalCart.find(f => 
                    f.is_custom && 
                    f.name === item.name && 
                    f.unit_name === item.unit_name && 
                    Math.abs(f.unit_price - item.unit_price) < 0.5
                );
                if (existingCustom) {
                    existingCustom.quantity += item.quantity;
                    existingCustom.total = existingCustom.quantity * existingCustom.unit_price;
                    existingCustom.custom_line_total = existingCustom.total;
                    existingCustom.custom_price_draft = String(existingCustom.total);
                } else {
                    finalCart.push(item);
                }
            } else {
                const existingCatalog = finalCart.find(f => 
                    !f.is_custom && 
                    !f.use_custom_price && 
                    f.product_id == item.product_id && 
                    f.level == item.level
                );
                if (existingCatalog) {
                    existingCatalog.quantity += item.quantity;
                    recalcItemPrice(existingCatalog);
                } else {
                    finalCart.push(item);
                }
            }
        }

        cart = finalCart;

        // 4. Set Sale Mode (retail / wholesale)
        setSaleMode(targetMode);

        // 5. Display Merged Banner Top Indicator
        const oldBanner = document.getElementById('posMergedBanner');
        if (oldBanner) oldBanner.remove();

        const banner = document.createElement('div');
        banner.id = 'posMergedBanner';
        banner.innerHTML = `
            <div style="background:linear-gradient(135deg, #15803d 0%, #166534 100%); border-radius:var(--radius-lg); padding:12px 16px; margin-bottom:14px; border:1px solid rgba(74,222,128,0.4); color:white; display:flex; justify-content:space-between; align-items:center; box-shadow:0 8px 24px rgba(0,0,0,0.15);">
                <div>
                    <div style="font-weight:700; font-size:14px; display:flex; align-items:center; gap:6px;"><i class="bi bi-layers-fill" style="font-size:1.1rem; color:#86efac;"></i> Invoice Gabungan POS</div>
                    <div style="font-size:12px; color:rgba(255,255,255,0.9); margin-top:3px;">
                        Menggabungkan ${invoiceNumbers.length} Invoice: <strong style="color:#fef08a;">${invoiceNumbers.join(', ')}</strong> &middot; Default Mode: <span class="badge-custom badge-${targetMode === 'retail' ? 'info' : 'warning'}" style="font-size:10px; padding:2px 6px;">${targetMode === 'retail' ? 'Ecer' : 'Grosir'}</span>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:white; cursor:pointer; font-size:1.2rem; padding:4px;" title="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        `;
        const pageSection = document.querySelector('.page-section');
        const posHeader = pageSection?.children[0];
        if (pageSection) {
            if (posHeader && posHeader.nextSibling) {
                pageSection.insertBefore(banner, posHeader.nextSibling);
            } else {
                pageSection.prepend(banner);
            }
        }

        cart.forEach(it => recalcItemPrice(it));
        renderCart();
        showToast(`✅ ${invoiceNumbers.length} Invoice berhasil digabungkan ke Kasir POS!`, 'success');

    } catch (e) {
        console.error('loadMergedInvoiceDraft error:', e);
        showToast('Gagal memuat invoice gabungan', 'error');
    }
}

// Helper: calculate expected unit price for a packaging at a given mode/qty
// Mirrors recalcItemPrice logic but returns the final unit price without modifying any item
function _calcExpectedUnitPrice(pkg, mode, qty, allPackagings) {
    let basePricePerUnit = 0;
    let rawTotal = 0;
    
    if (typeof QtyPricing !== 'undefined' && typeof QtyPricing.calculateTotalPrice === 'function') {
        rawTotal = QtyPricing.calculateTotalPrice(pkg, mode, qty, false, null, allPackagings);
        basePricePerUnit = qty > 0 ? rawTotal / qty : 0;
    } else {
        basePricePerUnit = mode === 'wholesale'
            ? (parseFloat(pkg.sell_price_wholesale) || parseFloat(pkg.sell_price_retail) || 0)
            : (parseFloat(pkg.sell_price_retail) || 0);
        rawTotal = basePricePerUnit * qty;
    }

    // Apply PPN
    const ppnPct = parseFloat(pkg.ppn_pct) || 0;
    let ppnAmount = 0;
    if (ppnPct > 0) {
        ppnAmount = basePricePerUnit * (ppnPct / 100);
    }

    // Apply Discount
    const dMode = pkg.discount_mode || 'rp';
    const dVal = parseFloat(pkg.discount_value) || 0;
    let discountAmount = 0;
    if (dVal > 0) {
        if (dMode === 'pct') {
            discountAmount = (basePricePerUnit + ppnAmount) * (dVal / 100);
        } else {
            discountAmount = dVal;
        }
    }

    const finalUnitPrice = basePricePerUnit + ppnAmount - discountAmount;
    const finalTotal = Math.round(finalUnitPrice * qty);
    return qty > 0 ? finalTotal / qty : 0;
}
</script>

<script>
// ===== Customer Selector =====

const customerTypes = <?= json_encode($customerTypes ?? []) ?>;
let _customerSearchTimeout = null;
let _allCustomers = []; // cached from first load

function getCustomerFormHTML(c = {}) {
    let optionsListHtml = '';
    let activeTypeName = 'Pilih Level...';
    customerTypes.forEach(t => {
        if (c.type_id == t.id) activeTypeName = `${t.name} (Tier: ${t.price_tier})`;
        optionsListHtml += `<li><a class="dropdown-item ${c.type_id == t.id ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='${t.id}'; dp.querySelector('button span').textContent='${t.name} (Tier: ${t.price_tier})'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">${t.name} (Tier: ${t.price_tier})</a></li>`;
    });

    const isAnon = c.name ? c.name.toLowerCase().includes('tanpa nama') : false;

    return `
        <div class="modal-form-group" style="margin-bottom:12px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; background:var(--surface-2); padding:10px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                <input type="checkbox" id="modalCustAnon" value="1" ${isAnon ? 'checked' : ''} onchange="toggleAnonCheckbox(this.checked)" style="width:18px; height:18px; accent-color:var(--primary);">
                <span style="font-size:var(--font-size-sm); font-weight:600; color:var(--text-primary);">Nama Pelanggan Tidak Diketahui</span>
            </label>
            <div style="font-size:10px; color:var(--text-muted); margin-top:4px; margin-left:26px;">Gunakan opsi ini jika pelanggan tidak tahu namanya, dan kasir hanya mencatat ciri fisik.</div>
        </div>
        
        <div class="modal-form-group" id="groupCustName">
            <label>Nama Pelanggan *</label>
            <input type="text" id="modalCustName" class="form-control-dark" value="${c.name || ''}" placeholder="Cth: Budi Santoso" required>
        </div>
        
        <div class="modal-form-group">
            <label>Nomor HP / WA</label>
            <input type="text" id="modalCustPhone" class="form-control-dark" value="${c.phone || ''}" placeholder="Cth: 0812...">
        </div>

        <div class="modal-form-group">
            <label>Alamat</label>
            <input type="text" id="modalCustAddr" class="form-control-dark" value="${c.address || ''}" placeholder="Alamat lengkap...">
        </div>

        <div class="modal-form-group">
            <label>Level Kategori Pelanggan</label>
            <div class="dropdown" style="width:100%;">
                <button class="btn-dropdown-modern dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span><i class="bi bi-tag-fill me-2 text-primary"></i>${activeTypeName}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:12px; min-width:100%;">
                    ${optionsListHtml}
                </ul>
                <input type="hidden" id="modalCustType" value="${c.type_id || ''}">
            </div>
        </div>

        <div class="modal-form-group">
            <label id="labelNotes">Catatan / Ciri-Ciri Fisik ${isAnon ? '*' : ''}</label>
            <textarea id="modalCustNotes" class="form-control-dark" placeholder="Cth: Ibu-ibu kerudung merah, sering bawa anak kecil, pakai motor Beat" rows="3" required>${c.notes || ''}</textarea>
        </div>
    `;
}

window.toggleAnonCheckbox = function(checked) {
    const nameGroup = document.getElementById('groupCustName');
    const nameInput = document.getElementById('modalCustName');
    const notesLabel = document.getElementById('labelNotes');
    
    if (checked) {
        nameGroup.style.opacity = '0.5';
        nameInput.disabled = true;
        nameInput.value = 'Pelanggan Tanpa Nama';
        notesLabel.innerHTML = 'Catatan / Ciri-Ciri Fisik *';
        document.getElementById('modalCustNotes').focus();
    } else {
        nameGroup.style.opacity = '1';
        nameInput.disabled = false;
        nameInput.value = '';
        notesLabel.innerHTML = 'Catatan / Ciri-Ciri Fisik';
    }
};

window.showAddCustomerModal = async function() {
    closeCustomerDropdown();
    await AppModal.show({
        title: 'Tambah Pelanggan',
        subtitle: 'Tambahkan data pelanggan baru',
        icon: 'bi-person-plus-fill',
        iconColor: 'var(--success-bg)',
        iconAccent: 'var(--success)',
        bodyHTML: getCustomerFormHTML(),
        submitText: 'Simpan',
        onSubmit: async () => {
            const isAnon = document.getElementById('modalCustAnon').checked;
            let name = document.getElementById('modalCustName').value.trim();
            const phone = document.getElementById('modalCustPhone').value.trim();
            const address = document.getElementById('modalCustAddr').value.trim();
            const notes = document.getElementById('modalCustNotes').value.trim();
            const typeId = document.getElementById('modalCustType').value;

            if (isAnon) {
                if (!notes) {
                    showToast('Ciri-ciri fisik wajib diisi jika nama tidak diketahui', 'warning');
                    return false;
                }
                const shortTrait = notes.split(',')[0].substring(0, 30);
                name = `Tanpa Nama - ${shortTrait}`;
            } else if (!name) {
                showToast('Nama pelanggan wajib diisi', 'warning');
                return false;
            }

            try {
                const res = await fetch(`${BASE_URL}api/customers`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        csrf_token: document.getElementById('csrfToken')?.value || '',
                        name: name,
                        phone: phone,
                        address: address,
                        notes: notes,
                        type_id: typeId
                    })
                }).then(r => r.json());

                if (res.success) {
                    showToast('Pelanggan berhasil ditambahkan', 'success');
                    // Refresh customers
                    await fetchCustomers('');
                    // Auto select new customer
                    selectCustomer({id: res.customer_id, name: name, phone: phone});
                    return true;
                } else {
                    showToast(res.error || 'Terjadi kesalahan', 'error');
                }
            } catch (e) {
                showToast(e.message, 'error');
            }
            return false;
        }
    });

    // Initialize state
    toggleAnonCheckbox(false);
};

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
        </div>
        <div onclick="showAddCustomerModal()" style="padding:10px 14px;display:flex;align-items:center;gap:10px;cursor:pointer;transition:background 0.15s;border-top:1px solid var(--border-color);"
             onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
            <div style="width:30px;height:30px;border-radius:50%;background:var(--success-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-person-plus-fill" style="color:var(--success);font-size:0.9rem;"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:var(--font-size-sm);font-weight:600;color:var(--success);">+ Pelanggan Baru</div>
                <div style="font-size:10px;color:var(--text-muted);">Tambah data pelanggan baru</div>
            </div>
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
