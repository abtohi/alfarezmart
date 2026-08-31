<?php
/**
 * Hitung Orderan — Order Estimate Builder
 *
 * @var array  $suppliers
 * @var string $csrfToken
 */
?>
<div class="page-section" style="padding-bottom: 100px;">

    <!-- Header Card -->
    <div style="background:var(--gradient-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div>
                <h4 style="font-weight:700; font-size:var(--font-size-md); margin:0;">Hitung Orderan</h4>
                <p style="font-size:var(--font-size-xs); color:var(--text-muted); margin:4px 0 0 0;">Susun daftar belanja ke supplier &amp; copy ke WhatsApp</p>
            </div>
            <button type="button" class="btn-outline-custom" onclick="openOrderDrafts()" style="padding:8px 14px; border-radius:var(--radius-md); font-size:var(--font-size-xs); background:var(--surface-1); border:1px solid var(--border-color); display:flex; align-items:center; gap:6px; flex-shrink:0;">
                <i class="bi bi-journal-bookmark"></i> <span>Draft</span>
            </button>
        </div>

        <!-- Summary -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:16px;">
            <div style="background:var(--bg-primary); padding:12px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px;">Jumlah Item</div>
                <div id="orderItemCount" style="font-weight:800; font-size:var(--font-size-sm); color:var(--info);">0</div>
            </div>
            <div style="background:var(--bg-primary); padding:12px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
                <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px;">Estimasi Total Belanja</div>
                <div id="orderEstimateTotal" style="font-weight:800; font-size:var(--font-size-sm); color:var(--success);">Rp 0</div>
            </div>
        </div>
    </div>

    <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">

    <!-- Supplier Picker -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:12px; margin-bottom:12px;">
        <label style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-bottom:6px; font-weight:600;">
            <i class="bi bi-building"></i> Supplier Tujuan
        </label>
        <div style="position:relative;">
            <input type="hidden" id="supplierSelect" value="">
            <input type="hidden" id="supplierName" value="">
            <div class="search-input-wrapper" style="width:100%;">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="supplierSearchInput" autocomplete="off" placeholder="Ketik nama supplier atau sales..." class="form-control" style="background:var(--bg-primary); color:var(--text-primary); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:10px 10px 10px 36px; width:100%; font-size:var(--font-size-sm);">
            </div>
            <div id="supplierSearchResults" style="position:absolute; top:100%; left:0; right:0; max-height:200px; overflow-y:auto; background:var(--bg-primary); border:1px solid var(--border-color); border-radius:var(--radius-md); z-index:60; display:none; box-shadow:0 8px 24px rgba(0,0,0,0.4); margin-top:4px;"></div>
        </div>
    </div>

    <!-- Product Search -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:12px; margin-bottom:12px; position:relative;">
        <label style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-bottom:6px; font-weight:600;">
            <i class="bi bi-search"></i> Cari Produk (cth: "Chocolatos 24g")
        </label>
        <div style="display:flex; align-items:center; background:var(--bg-primary); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:0 10px;">
            <i class="bi bi-upc-scan" id="btnOrderScan" style="color:var(--primary); font-size:1.2rem; cursor:pointer; margin-right:8px;" title="Scan Barcode Kamera"></i>
            <input type="text" id="orderSearchInput" autocomplete="off" placeholder="Ketik nama produk, merk, atau berat..."
                   style="flex:1; background:transparent; color:var(--text-primary); border:none; padding:10px 0; font-size:var(--font-size-sm); outline:none;">
        </div>
        <div id="orderSearchResults" style="position:absolute; top:100%; left:12px; right:12px; max-height:340px; overflow-y:auto; background:var(--bg-primary); border:1px solid var(--border-color); border-radius:var(--radius-md); z-index:50; display:none; box-shadow:0 8px 24px rgba(0,0,0,0.4); margin-top:4px;"></div>
    </div>

    <!-- Order Items List -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding:0 4px;">
        <div style="font-size:var(--font-size-sm); font-weight:700; color:var(--text-primary);">
            <i class="bi bi-list-check"></i> Daftar Orderan
        </div>
        <button id="btnClearOrder" type="button" style="background:transparent; border:none; color:var(--text-muted); font-size:var(--font-size-xs); cursor:pointer;">
            <i class="bi bi-trash"></i> Kosongkan
        </button>
    </div>

    <div id="orderItemsList" style="display:flex; flex-direction:column; gap:8px; margin-bottom:16px;">
        <div id="orderEmptyState" style="text-align:center; padding:30px 16px; background:var(--bg-secondary); border:1px dashed var(--border-color); border-radius:var(--radius-md); color:var(--text-muted); font-size:var(--font-size-xs);">
            <i class="bi bi-cart3" style="font-size:2rem; display:block; margin-bottom:8px; opacity:0.5;"></i>
            Belum ada item. Cari produk di atas untuk menambahkan.
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="position:fixed; bottom:70px; left:0; right:0; padding:12px; background:var(--bg-primary); border-top:1px solid var(--border-color); display:flex; gap:8px; max-width:480px; margin:0 auto; z-index:40;">
        <button id="btnSaveOrder" type="button" class="btn-outline-custom" onclick="saveOrderDraft()" style="padding:12px; font-weight:700; border:1px solid var(--border-color); border-radius:var(--radius-md); cursor:pointer; background:var(--surface-2); color:var(--text-primary); flex:0.4;">
            <i class="bi bi-save"></i> Simpan
        </button>
        <button id="btnCopyOrder" type="button" class="btn-primary-custom" style="flex:1; padding:12px; font-weight:700; border:none; border-radius:var(--radius-md); cursor:pointer; background:linear-gradient(135deg,#25d366,#128c7e); color:white;">
            <i class="bi bi-whatsapp"></i> Copy WA
        </button>
    </div>
</div>

<style>
.order-item-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.order-item-card .item-name { font-size: var(--font-size-xs); font-weight:700; color:var(--text-primary); line-height:1.3; }
.order-item-card .item-meta { font-size: 10px; color:var(--text-muted); margin-top:2px; }
.order-item-card .qty-input {
    width: 60px; text-align:center; background:var(--bg-primary); color:var(--text-primary);
    border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:6px;
    font-weight:700; font-size:var(--font-size-xs);
}
.order-item-card .qty-btn {
    width:28px; height:28px; border-radius:50%; border:none; background:var(--bg-primary);
    color:var(--text-primary); font-weight:700; cursor:pointer;
}
.order-item-card .del-btn {
    width:30px; height:30px; border-radius:50%; border:none; background:var(--danger-bg);
    color:var(--primary); cursor:pointer;
}
.search-result-row {
    padding:10px 12px; border-bottom:1px solid var(--border-color); cursor:pointer;
    display:flex; justify-content:space-between; align-items:center; gap:10px;
}
.search-result-row:last-child { border-bottom:none; }
.search-result-row:hover { background:var(--bg-secondary); }
.search-result-row .res-name { font-size:var(--font-size-xs); font-weight:700; color:var(--text-primary); line-height:1.3; }
.search-result-row .res-meta { font-size:10px; color:var(--text-muted); margin-top:2px; }
.search-result-row .res-price { font-size:11px; font-weight:700; color:var(--success); white-space:nowrap; }
</style>

<script>
(function() {
    'use strict';

    let orderItems = []; // { product_id, packaging_id, name, short_label, unit_name, buy_price, qty, base_qty }
    let currentDraftId = null;
    let draftTitle = '';
    let searchTimer = null;
    let lastResults = [];

    const elInput = document.getElementById('orderSearchInput');
    const elResults = document.getElementById('orderSearchResults');
    const elList = document.getElementById('orderItemsList');
    const elEmpty = document.getElementById('orderEmptyState');
    const elCount = document.getElementById('orderItemCount');
    const elTotal = document.getElementById('orderEstimateTotal');
    const elSupplier = document.getElementById('supplierSelect');
    const elSupplierName = document.getElementById('supplierName');
    const elSupplierInput = document.getElementById('supplierSearchInput');
    const elSupplierResults = document.getElementById('supplierSearchResults');
    let supplierSearchTimer = null;

    function fmtRp(n) { return 'Rp ' + (Math.round(n) || 0).toLocaleString('id-ID'); }

    function recompute() {
        let total = 0;
        orderItems.forEach(it => { total += (parseFloat(it.buy_price) || 0) * (parseFloat(it.qty) || 0); });
        elCount.textContent = orderItems.length;
        elTotal.textContent = fmtRp(total);
        elEmpty.style.display = orderItems.length === 0 ? '' : 'none';
    }

    function renderList() {
        // Clear except empty state
        Array.from(elList.querySelectorAll('.order-item-card')).forEach(n => n.remove());
        orderItems.forEach((it, idx) => {
            const card = document.createElement('div');
            card.className = 'order-item-card';

            let packHtml = '';
            if (it.packagings && it.packagings.length > 0) {
                const firstPk = it.packagings.find(pk => pk.id == it.packaging_id) || it.packagings[0];
                let optionsHtml = '';
                it.packagings.forEach(pk => {
                    const isActive = (pk.id == it.packaging_id);
                    optionsHtml += `<li><a class="dropdown-item ${isActive ? 'active' : ''}" href="#"
                        data-pkg-id="${pk.id}" data-price="${pk.buy_price}" data-unit="${escapeHtml(pk.unit_name || 'pcs')}"
                        onclick="event.preventDefault(); event.stopPropagation();
                            const dp=this.closest('.pkg-dropdown-wrapper');
                            const inp=dp.querySelector('input.pkg-select');
                            inp.value='${pk.id}';
                            inp.dataset.price='${pk.buy_price}';
                            inp.dataset.unit='${escapeHtml(pk.unit_name || 'pcs')}';
                            dp.querySelector('button span').textContent='${escapeHtml((pk.unit_name||'pcs') + ' (Isi ' + pk.base_qty + ') - ' + fmtRp(pk.buy_price))}';
                            dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');
                            inp.dispatchEvent(new Event('change', {bubbles:true}));
                        ">${escapeHtml(pk.unit_name || 'pcs')} (Isi ${pk.base_qty}) - ${fmtRp(pk.buy_price)}</a></li>`;
                });
                const activeLabel = `${escapeHtml((firstPk.unit_name||'pcs'))} (Isi ${firstPk.base_qty}) - ${fmtRp(firstPk.buy_price)}`;
                packHtml = `<div class="pkg-dropdown-wrapper dropdown" style="width:100%;">
                    <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center;
                               padding:6px 8px; font-size:10px; background:var(--bg-input); border:1px solid var(--border-color);
                               color:var(--text-primary); border-radius:var(--radius-sm); white-space:nowrap; overflow:hidden;">
                        <span style="overflow:hidden;text-overflow:ellipsis;">${activeLabel}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:10px; min-width:100%;">
                        ${optionsHtml}
                    </ul>
                    <input type="hidden" class="pkg-select" data-idx="${idx}"
                        value="${firstPk.id}"
                        data-price="${firstPk.buy_price}"
                        data-unit="${escapeHtml(firstPk.unit_name || 'pcs')}">
                </div>`;
            } else {
                packHtml = `<div style="font-size:10px; color:var(--text-muted); padding:6px 0;">${escapeHtml(it.unit_name)} · ${fmtRp(it.buy_price)}</div>`;
            }

            card.innerHTML = `
                <div style="display:flex; flex-direction:column; gap:8px; width:100%;">
                    <div class="item-name" style="width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(it.name)}</div>
                    <div style="display:flex; align-items:center; gap:10px; width:100%;">
                        <div style="flex:1; min-width:0;">
                            ${packHtml}
                        </div>
                        <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                            <button type="button" class="qty-btn" data-act="dec" data-idx="${idx}">−</button>
                            <input type="number" class="qty-input" min="0" step="1" value="${it.qty}" data-idx="${idx}">
                            <button type="button" class="qty-btn" data-act="inc" data-idx="${idx}">+</button>
                            <button type="button" class="del-btn" style="margin-left:4px;" data-act="del" data-idx="${idx}"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>
            `;
            elList.appendChild(card);
        });
        recompute();
    }

    elList.addEventListener('change', (e) => {
        if (e.target.classList.contains('pkg-select')) {
            const idx = parseInt(e.target.dataset.idx, 10);
            const val = e.target.value;
            const price = e.target.dataset.price;
            const unit = e.target.dataset.unit;
            if (orderItems[idx]) {
                orderItems[idx].packaging_id = parseInt(val, 10);
                orderItems[idx].buy_price = parseFloat(price) || 0;
                orderItems[idx].unit_name = unit || '';
                recompute();
            }
        }
    });

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    elList.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-act]');
        if (!btn) return;
        const idx = parseInt(btn.dataset.idx, 10);
        if (isNaN(idx) || !orderItems[idx]) return;
        if (btn.dataset.act === 'inc') orderItems[idx].qty++;
        else if (btn.dataset.act === 'dec') orderItems[idx].qty = Math.max(0, orderItems[idx].qty - 1);
        else if (btn.dataset.act === 'del') orderItems.splice(idx, 1);
        renderList();
    });
    elList.addEventListener('input', (e) => {
        const inp = e.target.closest('.qty-input');
        if (!inp) return;
        const idx = parseInt(inp.dataset.idx, 10);
        if (orderItems[idx]) {
            orderItems[idx].qty = Math.max(0, parseFloat(inp.value) || 0);
            recompute();
        }
    });

    document.getElementById('btnClearOrder').addEventListener('click', () => {
        if (orderItems.length === 0) return;
        if (typeof AppModal !== 'undefined') {
            AppModal.confirm('Kosongkan Daftar?', 'Semua item orderan akan dihapus.', 'Ya, Kosongkan', 'var(--danger)').then(ok => {
                if (ok) { orderItems.length = 0; renderList(); }
            });
        } else {
            if (confirm('Kosongkan daftar orderan?')) { orderItems.length = 0; renderList(); }
        }
    });

    // ── Search with debounce; uses /api/products/search (already has multi-word algorithm) ──
    elInput.addEventListener('input', () => {
        const q = elInput.value.trim();
        clearTimeout(searchTimer);
        if (q.length < 1) { elResults.style.display = 'none'; return; }
        searchTimer = setTimeout(() => doSearch(q), 200);
    });
    
    elInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = elInput.value.trim();
            if (q) {
                if (/^\d{6,18}$/.test(q)) {
                    doBarcodeSearch(q);
                } else {
                    doSearch(q);
                }
            }
        }
    });
    
    elInput.addEventListener('focus', () => {
        if (lastResults.length > 0) elResults.style.display = '';
    });

    const btnScan = document.getElementById('btnOrderScan');
    if (btnScan) {
        btnScan.addEventListener('click', () => {
            if (typeof BarcodeUtil !== 'undefined' && BarcodeUtil.scanBarcode) {
                const fakeInput = document.createElement('input');
                BarcodeUtil.scanBarcode(fakeInput, (code) => {
                    if (code) {
                        elInput.value = code;
                        doBarcodeSearch(code);
                    }
                });
            } else {
                showToast('Scanner belum dimuat.', 'error');
            }
        });
    }

    async function doBarcodeSearch(code) {
        try {
            const url = `<?= BASE_URL ?>api/products/barcode/${encodeURIComponent(code)}`;
            const res = await fetch(url, { credentials: 'same-origin' });
            if (res.ok) {
                const data = await res.json();
                if (data && data.id) {
                    if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
                    addItem(data);
                    elInput.value = '';
                    elResults.style.display = 'none';
                    return;
                }
            }
        } catch (err) {}
        // Fallback to regular search if barcode API fails or not found
        doSearch(code);
    }
    document.addEventListener('click', (e) => {
        if (!elResults.contains(e.target) && e.target !== elInput) {
            elResults.style.display = 'none';
        }
    });

    async function doSearch(q) {
        try {
            if (typeof OfflineDB !== 'undefined' && typeof OfflineDB.searchProducts === 'function') {
                const local = await OfflineDB.searchProducts(q);
                if (Array.isArray(local) && local.length > 0) {
                    lastResults = local;
                    renderResults(lastResults);
                }
            }
            const url = `<?= BASE_URL ?>api/products/search?q=${encodeURIComponent(q)}`;
            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            if (Array.isArray(data) && data.length > 0) {
                lastResults = data;
                renderResults(lastResults);
            } else if (!lastResults || lastResults.length === 0) {
                lastResults = [];
                renderResults([]);
            }
        } catch (err) {
            console.error('Search failed:', err);
            if (!lastResults || lastResults.length === 0) {
                elResults.innerHTML = '<div style="padding:12px; color:var(--text-muted); text-align:center; font-size:11px;">Gagal mencari. Coba lagi.</div>';
                elResults.style.display = '';
            }
        }
    }

    function renderResults(results) {
        if (!results || results.length === 0) {
            elResults.innerHTML = '<div style="padding:12px; color:var(--text-muted); text-align:center; font-size:11px;">Tidak ada produk cocok.</div>';
            elResults.style.display = '';
            return;
        }
        
        const html = results.map((p, pi) => {
            const thumbUrl = p.photo ? (p.photo.startsWith('http') ? p.photo : `<?= BASE_URL ?>${p.photo}`) : '';
            const thumbHtml = thumbUrl
                ? `<img src="${thumbUrl}" alt="Thumb" style="width:40px; height:40px; border-radius:6px; object-fit:cover; border:1px solid var(--border-color); flex-shrink:0;">`
                : `<div style="width:40px; height:40px; border-radius:6px; background:var(--surface-2); display:flex; align-items:center; justify-content:center; border:1px solid var(--border-color); flex-shrink:0; color:var(--text-muted);">
                       <i class="bi bi-box-seam" style="font-size:1.2rem;"></i>
                   </div>`;
                   
            // Packaging info compact horizontal badges
            let pkgHtml = '';
            if (p.packagings && p.packagings.length > 0) {
                const pkgItems = p.packagings.map(pkg => {
                    const ret = parseFloat(pkg.sell_price_retail) || 0;
                    return `
                    <div style="display:inline-flex; align-items:center; background:var(--surface-2); border:1px solid var(--border-color); border-radius:4px; padding:3px 6px; font-size:0.65rem; white-space:nowrap; flex-shrink:0;">
                        <span style="color:var(--text-primary); font-weight:600; margin-right:3px;">${pkg.unit_name || ''}</span>
                        <span style="color:var(--text-muted); margin-right:5px; font-size:0.55rem;">(x${pkg.base_qty})</span>
                        <span style="color:var(--success); font-weight:700;">${fmtRp(ret)}</span>
                    </div>`;
                }).join('');
                pkgHtml = `
                <style>.hide-scroll::-webkit-scrollbar { display: none; }</style>
                <div class="hide-scroll" style="display:flex; overflow-x:auto; gap:4px; margin-top:6px; padding-bottom:2px; scrollbar-width:none; -ms-overflow-style:none; width:100%;">
                    ${pkgItems}
                </div>`;
            }

            return `
            <div data-pi="${pi}" class="search-result-row" style="align-items:flex-start;">
                ${thumbHtml}
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:4px;">
                        <div style="font-weight:600; font-size:0.85rem; color:var(--text-primary); line-height:1.3; word-break:break-word; white-space:normal;">${p.short_label || p.full_name}</div>
                        <i class="bi bi-plus-circle" style="font-size:1.2rem;color:var(--primary);flex-shrink:0;"></i>
                    </div>
                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:2px; display:flex; flex-wrap:wrap; gap:4px; align-items:center;">
                        <span>${p.brand_name ? p.brand_name : 'Tanpa Merek'}</span>
                        ${p.last_buy_price ? `<span>&middot; Beli: <strong style="color:var(--text-primary);">${fmtRp(p.last_buy_price)}</strong></span>` : ''}
                    </div>
                    ${pkgHtml}
                </div>
            </div>`;
        }).join('');
        
        elResults.innerHTML = html;
        elResults.style.display = '';
        Array.from(elResults.querySelectorAll('.search-result-row')).forEach(row => {
            row.addEventListener('click', () => {
                const pi = parseInt(row.dataset.pi, 10);
                const p = results[pi]; if (!p) return;
                addItem(p);
                elInput.value = '';
                elResults.style.display = 'none';
                lastResults = [];
            });
        });
    }

    function addItem(product) {
        // Dedup: increment qty if same product already in list
        const existing = orderItems.find(it => it.product_id === parseInt(product.id, 10));
        if (existing) { existing.qty++; renderList(); return; }
        
        const packs = Array.isArray(product.packagings) && product.packagings.length > 0 ? product.packagings : [];
        const fallbackBasePrice = parseFloat(product.last_buy_price) || 0;
        packs.forEach(pk => {
            if (!pk.buy_price || parseFloat(pk.buy_price) === 0) {
                pk.buy_price = fallbackBasePrice * (parseFloat(pk.base_qty) || 1);
            }
        });
        const packaging = packs.length > 0 ? packs[0] : null;
        const pkgId = packaging ? parseInt(packaging.id, 10) : 0;
        
        const displayName = product.full_name || product.short_label || product.invoice_name || 'Produk';
        orderItems.unshift({
            product_id: parseInt(product.id, 10),
            packagings: packs,
            packaging_id: pkgId,
            name: displayName,
            short_label: product.short_label || product.invoice_name || displayName,
            unit_name: packaging ? (packaging.unit_name || 'pcs') : (product.unit_small_name || 'pcs'),
            buy_price: packaging ? parseFloat(packaging.buy_price || 0) : fallbackBasePrice,
            qty: 1,
            base_qty: packaging ? parseInt(packaging.base_qty || 1, 10) : 1
        });
        renderList();
    }

    // ── Supplier Search ──
    elSupplierInput.addEventListener('input', () => {
        const q = elSupplierInput.value.trim();
        clearTimeout(supplierSearchTimer);
        if (q.length < 1) { elSupplierResults.style.display = 'none'; return; }
        supplierSearchTimer = setTimeout(() => doSupplierSearch(q), 250);
    });
    document.addEventListener('click', (e) => {
        if (!elSupplierResults.contains(e.target) && e.target !== elSupplierInput) {
            elSupplierResults.style.display = 'none';
        }
    });
    elSupplierInput.addEventListener('change', () => {
        if (!elSupplierInput.value.trim()) {
            elSupplier.value = '';
            elSupplierName.value = '';
        }
    });

    async function doSupplierSearch(q) {
        try {
            const url = `<?= BASE_URL ?>api/suppliers/search?q=${encodeURIComponent(q)}`;
            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            const results = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
            renderSupplierResults(results);
        } catch (err) {
            elSupplierResults.innerHTML = '<div style="padding:12px; color:var(--text-muted); text-align:center; font-size:11px;">Gagal mencari.</div>';
            elSupplierResults.style.display = '';
        }
    }

    function renderSupplierResults(results) {
        if (!results || results.length === 0) {
            elSupplierResults.innerHTML = '<div style="padding:12px; color:var(--text-muted); text-align:center; font-size:11px;">Tidak ditemukan.</div>';
            elSupplierResults.style.display = '';
            return;
        }
        const html = [];
        results.forEach((s) => {
            const isRep = typeof s.is_sales_rep !== 'undefined' ? s.is_sales_rep : (s.sales_rep_id ? true : false);
            const supName = s.supplier_name || s.name;
            const subText = isRep ? `Sales: ${s.name} (${s.phone || '-'})` : (s.type_name || 'Distributor');
            html.push(`<div class="search-result-row" data-id="${s.supplier_id || s.id}" data-name="${escapeHtml(supName)}">
                <div style="flex:1; min-width:0;">
                    <div class="res-name">${escapeHtml(supName)}</div>
                    <div class="res-meta">${escapeHtml(subText)}</div>
                </div>
            </div>`);
        });
        elSupplierResults.innerHTML = html.join('');
        elSupplierResults.style.display = '';
        Array.from(elSupplierResults.querySelectorAll('.search-result-row')).forEach(row => {
            row.addEventListener('click', () => {
                elSupplier.value = row.dataset.id;
                elSupplierName.value = row.dataset.name;
                elSupplierInput.value = row.dataset.name;
                elSupplierResults.style.display = 'none';
            });
        });
    }

    // ── Copy to WhatsApp ──
    document.getElementById('btnCopyOrder').addEventListener('click', async () => {
        if (orderItems.length === 0) {
            if (typeof showToast === 'function') showToast('Daftar orderan masih kosong', 'warning');
            return;
        }
        const lines = [];
        const supplierName = elSupplierName.value || elSupplierInput.value.trim();
        const today = new Date().toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
        lines.push('*ORDER BARANG*');
        if (supplierName) lines.push(`Supplier: ${supplierName}`);
        lines.push(`Tanggal: ${today}`);
        lines.push('');
        orderItems.forEach(it => {
            if ((parseFloat(it.qty) || 0) <= 0) return;
            const labelName = it.short_label || it.name;
            lines.push(`* ${labelName} (${it.qty} ${it.unit_name})`);
        });
        lines.push('');
        lines.push('Mohon konfirmasi ketersediaan & jadwal pengiriman. Terima kasih 🙏');
        const text = lines.join('\n');
        try {
            await navigator.clipboard.writeText(text);
            if (typeof showToast === 'function') showToast('Pesan disalin ke clipboard. Tempel di WhatsApp.', 'success');
        } catch (err) {
            // Fallback: open WhatsApp directly
            const url = 'https://wa.me/?text=' + encodeURIComponent(text);
            window.open(url, '_blank');
        }
    });

    window.saveOrderDraft = function() {
        if (orderItems.length === 0) {
            if (typeof showToast === 'function') showToast('Daftar orderan kosong', 'warning');
            return;
        }

        const defaultName = draftTitle || `Draft ${new Date().toLocaleTimeString('id-ID')}`;

        AppModal.show({
            title: 'Simpan Draft Order',
            subtitle: `${orderItems.length} item`,
            icon: 'bi-save',
            bodyHTML: `
                <div class="modal-form-group">
                    <label>Judul / Nama Draft</label>
                    <input type="text" class="form-control-dark" id="orderDraftNameInput" value="${escapeHtml(defaultName)}" 
                           placeholder="Cth: Pesanan Grosir A..." autocomplete="off">
                </div>
            `,
            submitText: 'Simpan',
            onSubmit: async () => {
                const name = document.getElementById('orderDraftNameInput')?.value?.trim() || defaultName;
                const total = orderItems.reduce((acc, it) => acc + (parseFloat(it.buy_price) || 0) * (parseFloat(it.qty) || 0), 0);
                const supplierId = elSupplier.value ? parseInt(elSupplier.value, 10) : null;
                
                const payload = {
                    id: currentDraftId,
                    title: name,
                    supplier_id: supplierId,
                    total_amount: total,
                    items: orderItems
                };
                
                try {
                    const csrf = document.getElementById('csrfToken')?.value || '';
                    const res = await fetch(`<?= BASE_URL ?>api/orders/estimates`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (!data.success) throw new Error(data.error || 'Gagal menyimpan');
                    
                    showToast(data.message, 'success');
                    currentDraftId = data.id;
                    draftTitle = name;
                    return true;
                } catch(e) {
                    showToast(e.message, 'error');
                    return false;
                }
            }
        });
    };

    window.openOrderDrafts = async function() {
        try {
            const res = await fetch(`<?= BASE_URL ?>api/orders/estimates`);
            const data = await res.json();
            if (!data.success) throw new Error(data.error);
            const drafts = data.data || [];
            
            if (drafts.length === 0) {
                showToast('Tidak ada draft tersimpan', 'info');
                return;
            }
            
            const listHtml = drafts.map(d => {
                const total = parseFloat(d.total_amount) || 0;
                return `
                <div style="background:var(--surface-2);border-radius:var(--radius-lg);padding:14px;margin-bottom:10px;border:1px solid var(--border-color);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:var(--font-size-sm);color:var(--text-primary);margin-bottom:4px;">${escapeHtml(d.title)}</div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                <span style="font-size:var(--font-size-xs);color:var(--text-muted);"><i class="bi bi-clock"></i> ${new Date(d.updated_at).toLocaleDateString('id-ID')}</span>
                                ${d.supplier_name ? `<span style="font-size:10px;color:var(--info);"><i class="bi bi-building"></i> ${escapeHtml(d.supplier_name)}</span>` : ''}
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800;font-size:var(--font-size-base);color:var(--primary);">${fmtRp(total)}</div>
                            <div style="font-size:10px;color:var(--text-muted);">${d.total_items || 0} item</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="button" onclick="loadOrderDraft(${d.id})" class="btn-primary-custom" style="flex:1;padding:10px;font-size:var(--font-size-xs);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;gap:6px;font-weight:600;border:none;cursor:pointer;">
                            <i class="bi bi-box-arrow-in-right"></i> Muat Draft
                        </button>
                        <button type="button" onclick="deleteOrderDraft(${d.id})" style="padding:10px 14px;background:var(--danger-bg);color:var(--danger);border:none;border-radius:var(--radius-md);cursor:pointer;font-size:var(--font-size-xs);display:flex;align-items:center;gap:4px;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>`;
            }).join('');

            AppModal.show({
                title: 'Draft Orderan',
                subtitle: `${drafts.length} draft tersedia`,
                icon: 'bi-journal-bookmark',
                bodyHTML: `<div id="orderDraftListContainer" style="max-height:55vh;overflow-y:auto;">${listHtml}</div>`,
                hideFooter: true,
                cancelText: 'Tutup'
            });
        } catch(e) {
            showToast('Gagal memuat daftar draft', 'error');
        }
    };

    window.loadOrderDraft = async function(id) {
        if (orderItems.length > 0) {
            const ok = await AppModal.confirm('Muat Draft', 'Daftar saat ini tidak kosong. Timpa dengan draft ini?', 'Ya, Timpa', 'var(--warning)');
            if (!ok) return;
        }
        try {
            const res = await fetch(`<?= BASE_URL ?>api/orders/estimates/${id}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.error);
            const d = data.data;
            
            orderItems = d.items.map(it => ({
                product_id: parseInt(it.product_id, 10) || 0,
                packaging_id: parseInt(it.packaging_id, 10) || 0,
                name: it.product_name,
                short_label: it.product_name,
                unit_name: it.unit_name,
                buy_price: parseFloat(it.buy_price) || 0,
                qty: parseFloat(it.quantity) || 0,
                base_qty: 1 
            }));
            
            currentDraftId = d.id;
            draftTitle = d.title;
            
            elSupplier.value = d.supplier_id || '';
            elSupplierName.value = d.supplier_name || '';
            elSupplierInput.value = d.supplier_name || '';
            
            renderList();
            AppModal.close();
            showToast('Draft dimuat', 'success');
        } catch(e) {
            showToast(e.message, 'error');
        }
    };

    window.deleteOrderDraft = async function(id) {
        const ok = await AppModal.confirm('Hapus Draft', 'Draft orderan ini akan dihapus permanen.', 'Ya, Hapus', 'var(--danger)');
        if (!ok) return;
        try {
            const csrf = document.getElementById('csrfToken')?.value || '';
            const res = await fetch(`<?= BASE_URL ?>api/orders/estimates/${id}/delete`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf }
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error);
            
            if (currentDraftId == id) {
                currentDraftId = null;
                draftTitle = '';
            }
            AppModal.close();
            setTimeout(window.openOrderDrafts, 300);
            showToast('Draft dihapus', 'success');
        } catch(e) {
            showToast(e.message, 'error');
        }
    };

    recompute();
})();
</script>
