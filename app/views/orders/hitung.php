<?php
/**
 * Hitung Orderan — Order Estimate Builder
 *
 * @var array  $suppliers
 * @var string $csrfToken
 */
?>
<div class="page-section" style="padding-bottom: 120px;">

    <!-- Modern Header Card -->
    <div class="order-header-card">
        <div class="order-header-top">
            <div class="order-header-info">
                <div class="order-badge-pill">
                    <i class="bi bi-calculator"></i> Kalkulator Restok
                </div>
                <h4 class="order-title">Hitung Orderan</h4>
                <p class="order-subtitle">Susun estimasi daftar belanja ke supplier &amp; copy ke WhatsApp</p>
            </div>
            <button type="button" class="btn-drafts-pill" onclick="openOrderDrafts()" title="Buka riwayat draft tersimpan">
                <i class="bi bi-journal-bookmark-fill text-warning"></i>
                <span>Draft</span>
            </button>
        </div>

        <!-- Summary KPI Grid -->
        <div class="order-summary-grid">
            <div class="summary-stat-box">
                <div class="stat-icon-wrap info-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-meta">
                    <span class="stat-label">Jumlah Item</span>
                    <span id="orderItemCount" class="stat-value text-info">0</span>
                </div>
            </div>
            <div class="summary-stat-box">
                <div class="stat-icon-wrap success-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-meta">
                    <span class="stat-label">Estimasi Total Belanja</span>
                    <span id="orderEstimateTotal" class="stat-value text-success">Rp 0</span>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

    <!-- Supplier Picker Card -->
    <div class="order-search-card mb-3">
        <label class="section-input-label">
            <i class="bi bi-building"></i> Supplier Tujuan <span class="text-muted fw-normal">(Opsional)</span>
        </label>
        <div style="position:relative;">
            <input type="hidden" id="supplierSelect" value="">
            <input type="hidden" id="supplierName" value="">
            <div class="modern-input-wrap">
                <i class="bi bi-shop search-lead-icon text-muted"></i>
                <input type="text" id="supplierSearchInput" autocomplete="off" placeholder="Ketik nama supplier atau sales..." class="modern-text-input">
                <button type="button" id="btnClearSupplier" class="btn-input-clear" style="display:none;" title="Hapus supplier">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div id="supplierSearchResults" class="custom-dropdown-results" style="display:none;"></div>
        </div>
    </div>

    <!-- Product Search Card -->
    <div class="order-search-card mb-3" style="position:relative;">
        <label class="section-input-label">
            <i class="bi bi-search"></i> Tambah Produk ke Orderan
        </label>
        <div class="modern-input-wrap">
            <button type="button" id="btnOrderScan" class="btn-scan-action" title="Scan Barcode Kamera">
                <i class="bi bi-upc-scan"></i>
            </button>
            <input type="text" id="orderSearchInput" autocomplete="off" placeholder="Ketik nama produk, merek, atau scan barcode..." class="modern-text-input">
            <button type="button" id="btnClearProductSearch" class="btn-input-clear" style="display:none;" title="Bersihkan pencarian">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div id="orderSearchResults" class="custom-dropdown-results" style="display:none;"></div>
    </div>

    <!-- Order Items Section Header -->
    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
        <div class="items-list-title">
            <i class="bi bi-list-check text-primary me-1"></i> Daftar Orderan
        </div>
        <button id="btnClearOrder" type="button" class="btn-clear-list" title="Hapus semua item">
            <i class="bi bi-trash3 me-1"></i> Kosongkan
        </button>
    </div>

    <!-- Order Items List Container -->
    <div id="orderItemsList" class="order-items-wrapper">
        <div id="orderEmptyState" class="order-empty-state">
            <div class="empty-icon-circle">
                <i class="bi bi-cart3"></i>
            </div>
            <div class="empty-title">Belum ada item dalam daftar orderan</div>
            <div class="empty-subtitle">Ketik nama produk atau scan barcode pada kolom pencarian di atas untuk menambahkan barang belanjaan.</div>
        </div>
    </div>

    <!-- Modern Floating Bottom Action Bar -->
    <div class="order-floating-bar">
        <button id="btnSaveOrder" type="button" class="btn-floating-save" onclick="saveOrderDraft()" title="Simpan sebagai Draft">
            <i class="bi bi-bookmark-check"></i>
            <span>Simpan</span>
        </button>
        <button id="btnCopyOrder" type="button" class="btn-floating-wa" title="Salin daftar orderan murni untuk WhatsApp">
            <i class="bi bi-whatsapp"></i>
            <span>Copy WA</span>
        </button>
    </div>
</div>

<style>
/* =====================================================================
   MODERN HITUNG ORDERAN STYLES
   ===================================================================== */

.order-header-card {
    background: linear-gradient(135deg, rgba(22, 33, 62, 0.95) 0%, rgba(15, 15, 26, 0.98) 100%);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    margin-bottom: 14px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    position: relative;
    overflow: hidden;
}

.order-header-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, #e63946, #2ec4b6, #ffb703);
}

.order-header-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
}

.order-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    background: rgba(230, 57, 70, 0.12);
    border: 1px solid rgba(230, 57, 70, 0.25);
    color: var(--primary);
    border-radius: 20px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.order-title {
    font-weight: 800;
    font-size: 1.15rem;
    margin: 0;
    color: var(--text-primary);
    letter-spacing: -0.2px;
}

.order-subtitle {
    font-size: 0.72rem;
    color: var(--text-muted);
    margin: 3px 0 0 0;
}

.btn-drafts-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.btn-drafts-pill:hover {
    background: var(--surface-3);
    border-color: rgba(255, 183, 3, 0.4);
    transform: translateY(-1px);
}

.order-summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.summary-stat-box {
    background: rgba(15, 22, 41, 0.65);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.stat-icon-wrap.info-icon {
    background: rgba(76, 201, 240, 0.12);
    color: var(--info);
    border: 1px solid rgba(76, 201, 240, 0.2);
}

.stat-icon-wrap.success-icon {
    background: rgba(46, 196, 182, 0.12);
    color: var(--success);
    border: 1px solid rgba(46, 196, 182, 0.2);
}

.stat-meta {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.stat-label {
    font-size: 0.65rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}

.stat-value {
    font-weight: 800;
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.order-search-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.section-input-label {
    font-size: 0.72rem;
    color: var(--text-secondary);
    display: block;
    margin-bottom: 8px;
    font-weight: 700;
    letter-spacing: 0.2px;
}

.modern-input-wrap {
    display: flex;
    align-items: center;
    background: var(--bg-input);
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 2px 10px;
    transition: all 0.2s ease;
}

.modern-input-wrap:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.15);
    background: var(--bg-primary);
}

.search-lead-icon {
    font-size: 1.05rem;
    margin-right: 8px;
}

.btn-scan-action {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 1.25rem;
    cursor: pointer;
    padding: 4px 8px 4px 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
}

.btn-scan-action:active {
    transform: scale(0.9);
}

.modern-text-input {
    flex: 1;
    background: transparent;
    color: var(--text-primary);
    border: none;
    padding: 9px 0;
    font-size: 0.82rem;
    font-weight: 500;
    outline: none;
    min-width: 0;
}

.modern-text-input::placeholder {
    color: var(--text-muted);
    font-size: 0.8rem;
}

.btn-input-clear {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
    display: flex;
    align-items: center;
}

.btn-input-clear:hover {
    color: var(--text-primary);
}

.custom-dropdown-results {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    max-height: 320px;
    overflow-y: auto;
    background: var(--surface-1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: var(--radius-md);
    z-index: 999;
    box-shadow: 0 12px 32px rgba(0,0,0,0.55);
}

.search-result-row {
    padding: 10px 14px;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    display: flex;
    gap: 12px;
    align-items: center;
    transition: background 0.15s ease;
}

.search-result-row:last-child {
    border-bottom: none;
}

.search-result-row:hover, .search-result-row:active {
    background: var(--surface-2);
}

.res-thumb-wrap {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    background: var(--surface-2);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border-color);
    flex-shrink: 0;
    overflow: hidden;
}

.res-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.items-list-title {
    font-size: 0.82rem;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: 0.2px;
}

.btn-clear-list {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 0.72rem;
    font-weight: 600;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.2s;
}

.btn-clear-list:hover {
    color: var(--danger);
    background: var(--danger-bg);
}

.order-items-wrapper {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.order-item-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
    transition: border-color 0.2s ease, transform 0.2s ease;
    animation: fadeIn 0.2s ease-out;
}

.order-item-card:hover {
    border-color: rgba(255,255,255,0.15);
}

.item-top-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
}

.item-name-heading {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.35;
    word-break: break-word;
}

.item-subtotal-badge {
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--success);
    white-space: nowrap;
    text-align: right;
}

.item-bottom-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding-top: 8px;
    border-top: 1px dashed var(--border-color);
}

.item-pkg-selector-wrap {
    flex: 1;
    min-width: 0;
}

.stepper-wrap {
    display: inline-flex;
    align-items: center;
    background: var(--bg-input);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 2px 4px;
}

.stepper-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: var(--surface-2);
    color: var(--text-primary);
    font-weight: 800;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
}

.stepper-btn:hover {
    background: var(--primary);
    color: #fff;
}

.stepper-btn:active {
    transform: scale(0.9);
}

.stepper-input {
    width: 46px;
    text-align: center;
    background: transparent;
    color: var(--text-primary);
    border: none;
    font-weight: 800;
    font-size: 0.85rem;
    padding: 4px 0;
    outline: none;
}

.btn-item-del {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: var(--danger-bg);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.2s;
    flex-shrink: 0;
}

.btn-item-del:hover {
    background: var(--primary);
    color: #fff;
    transform: scale(1.05);
}

.order-empty-state {
    text-align: center;
    padding: 36px 20px;
    background: var(--surface-1);
    border: 1.5px dashed var(--border-color);
    border-radius: var(--radius-lg);
}

.empty-icon-circle {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
}

.empty-title {
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.empty-subtitle {
    font-size: 0.72rem;
    color: var(--text-muted);
    max-width: 320px;
    margin: 0 auto;
    line-height: 1.4;
}

.order-floating-bar {
    position: fixed;
    bottom: 68px;
    left: 0;
    right: 0;
    padding: 10px 16px;
    background: rgba(15, 15, 26, 0.92);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 10px;
    max-width: 520px;
    margin: 0 auto;
    z-index: 50;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.35);
}

.btn-floating-save {
    flex: 0.35;
    padding: 12px 14px;
    font-weight: 700;
    font-size: 0.82rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: var(--surface-2);
    color: var(--text-primary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-floating-save:hover {
    background: var(--surface-3);
    border-color: rgba(255,255,255,0.2);
}

.btn-floating-wa {
    flex: 0.65;
    padding: 12px 16px;
    font-weight: 800;
    font-size: 0.85rem;
    border: none;
    border-radius: var(--radius-md);
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35);
    transition: all 0.2s;
}

.btn-floating-wa:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(37, 211, 102, 0.45);
}

.btn-floating-wa:active {
    transform: scale(0.98);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
(function() {
    'use strict';

    let orderItems = []; // { product_id, packaging_id, name, short_label, unit_name, buy_price, qty, base_qty }
    let currentDraftId = null;
    let draftTitle = '';
    let searchTimer = null;
    let searchSeq = 0; // Prevent race conditions
    let supplierSearchTimer = null;

    const elInput = document.getElementById('orderSearchInput');
    const elResults = document.getElementById('orderSearchResults');
    const elBtnClearProduct = document.getElementById('btnClearProductSearch');
    const elList = document.getElementById('orderItemsList');
    const elEmpty = document.getElementById('orderEmptyState');
    const elCount = document.getElementById('orderItemCount');
    const elTotal = document.getElementById('orderEstimateTotal');
    const elSupplier = document.getElementById('supplierSelect');
    const elSupplierName = document.getElementById('supplierName');
    const elSupplierInput = document.getElementById('supplierSearchInput');
    const elSupplierResults = document.getElementById('supplierSearchResults');
    const elBtnClearSupplier = document.getElementById('btnClearSupplier');

    function fmtRp(n) { return 'Rp ' + (Math.round(n) || 0).toLocaleString('id-ID'); }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

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

            const itemSubtotal = (parseFloat(it.buy_price) || 0) * (parseFloat(it.qty) || 0);

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
                               padding:5px 8px; font-size:11px; background:var(--bg-input); border:1px solid var(--border-color);
                               color:var(--text-primary); border-radius:var(--radius-sm); white-space:nowrap; overflow:hidden;">
                        <span style="overflow:hidden;text-overflow:ellipsis;">${activeLabel}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:11px; min-width:100%;">
                        ${optionsHtml}
                    </ul>
                    <input type="hidden" class="pkg-select" data-idx="${idx}"
                        value="${firstPk.id}"
                        data-price="${firstPk.buy_price}"
                        data-unit="${escapeHtml(firstPk.unit_name || 'pcs')}">
                </div>`;
            } else {
                packHtml = `<div style="font-size:11px; color:var(--text-muted); font-weight:600;">Satuan: <span style="color:var(--text-primary);">${escapeHtml(it.unit_name)}</span> &bull; @${fmtRp(it.buy_price)}</div>`;
            }

            card.innerHTML = `
                <div class="item-top-row">
                    <div class="item-name-heading">${escapeHtml(it.name)}</div>
                    <div class="item-subtotal-badge">${fmtRp(itemSubtotal)}</div>
                </div>
                <div class="item-bottom-controls">
                    <div class="item-pkg-selector-wrap">
                        ${packHtml}
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                        <div class="stepper-wrap">
                            <button type="button" class="stepper-btn" data-act="dec" data-idx="${idx}">−</button>
                            <input type="number" class="stepper-input qty-input" min="0" step="1" value="${it.qty}" data-idx="${idx}">
                            <button type="button" class="stepper-btn" data-act="inc" data-idx="${idx}">+</button>
                        </div>
                        <button type="button" class="btn-item-del" data-act="del" data-idx="${idx}" title="Hapus item">
                            <i class="bi bi-trash"></i>
                        </button>
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
                renderList();
            }
        }
    });

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
            AppModal.confirm('Kosongkan Daftar?', 'Semua item orderan akan dihapus dari daftar saat ini.', 'Ya, Kosongkan', 'var(--danger)').then(ok => {
                if (ok) { orderItems.length = 0; renderList(); }
            });
        } else {
            if (confirm('Kosongkan daftar orderan?')) { orderItems.length = 0; renderList(); }
        }
    });

    // ── Search & Dropdown Management with Race Condition Guard ──
    elInput.addEventListener('input', () => {
        const q = elInput.value.trim();
        elBtnClearProduct.style.display = q.length > 0 ? 'flex' : 'none';
        clearTimeout(searchTimer);
        if (q.length < 1) {
            searchSeq++;
            elResults.style.display = 'none';
            elResults.innerHTML = '';
            return;
        }
        searchSeq++;
        const currentSeq = searchSeq;
        searchTimer = setTimeout(() => doSearch(q, currentSeq), 200);
    });

    elBtnClearProduct.addEventListener('click', () => {
        searchSeq++;
        elInput.value = '';
        elBtnClearProduct.style.display = 'none';
        elResults.style.display = 'none';
        elResults.innerHTML = '';
        elInput.focus();
    });
    
    elInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = elInput.value.trim();
            if (q) {
                if (/^\d{6,18}$/.test(q)) {
                    doBarcodeSearch(q);
                } else {
                    searchSeq++;
                    doSearch(q, searchSeq);
                }
            }
        }
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
                if (typeof showToast === 'function') showToast('Scanner belum dimuat.', 'error');
            }
        });
    }

    async function doBarcodeSearch(code) {
        searchSeq++;
        try {
            const url = `<?= BASE_URL ?>api/products/barcode/${encodeURIComponent(code)}`;
            const res = await fetch(url, { credentials: 'same-origin' });
            if (res.ok) {
                const data = await res.json();
                if (data && data.id) {
                    if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
                    addItem(data);
                    closeProductSearchResults();
                    return;
                }
            }
        } catch (err) {}
        // Fallback to regular search
        searchSeq++;
        doSearch(code, searchSeq);
    }

    document.addEventListener('click', (e) => {
        if (!elResults.contains(e.target) && e.target !== elInput && e.target !== btnScan) {
            closeProductSearchResults();
        }
    });

    function closeProductSearchResults() {
        searchSeq++;
        elInput.value = '';
        elBtnClearProduct.style.display = 'none';
        elResults.style.display = 'none';
        elResults.innerHTML = '';
    }

    async function doSearch(q, currentSeq) {
        try {
            // Check offlineDB first
            if (typeof OfflineDB !== 'undefined' && typeof OfflineDB.searchProducts === 'function') {
                const local = await OfflineDB.searchProducts(q);
                if (currentSeq === searchSeq && elInput.value.trim().length > 0 && Array.isArray(local) && local.length > 0) {
                    renderResults(local, currentSeq);
                }
            }
            
            const url = `<?= BASE_URL ?>api/products/search?q=${encodeURIComponent(q)}`;
            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            
            // Only render if this search is still active and input is not cleared
            if (currentSeq === searchSeq && elInput.value.trim().length > 0) {
                if (Array.isArray(data) && data.length > 0) {
                    renderResults(data, currentSeq);
                } else {
                    renderResults([], currentSeq);
                }
            }
        } catch (err) {
            console.error('Search failed:', err);
            if (currentSeq === searchSeq && elInput.value.trim().length > 0) {
                elResults.innerHTML = '<div style="padding:16px; color:var(--text-muted); text-align:center; font-size:12px;"><i class="bi bi-exclamation-triangle me-1"></i> Gagal mencari. Coba lagi.</div>';
                elResults.style.display = 'block';
            }
        }
    }

    function renderResults(results, currentSeq) {
        if (currentSeq !== searchSeq || !elInput.value.trim()) {
            elResults.style.display = 'none';
            return;
        }

        if (!results || results.length === 0) {
            elResults.innerHTML = '<div style="padding:16px; color:var(--text-muted); text-align:center; font-size:12px;"><i class="bi bi-info-circle me-1"></i> Tidak ada produk yang cocok.</div>';
            elResults.style.display = 'block';
            return;
        }
        
        const html = results.map((p, pi) => {
            const thumbUrl = p.photo ? (p.photo.startsWith('http') ? p.photo : `<?= BASE_URL ?>${p.photo}`) : '';
            const thumbHtml = thumbUrl
                ? `<img src="${thumbUrl}" alt="Thumb" class="res-thumb-img">`
                : `<i class="bi bi-box-seam" style="font-size:1.2rem; color:var(--text-muted);"></i>`;
                   
            // Packaging info compact horizontal badges
            let pkgHtml = '';
            if (p.packagings && p.packagings.length > 0) {
                const pkgItems = p.packagings.map((pkg, pki) => {
                    const ret = parseFloat(pkg.buy_price) || (parseFloat(p.last_buy_price || 0) * (parseFloat(pkg.base_qty) || 1));
                    return `
                    <div class="pkg-pill-item" data-pi="${pi}" data-pki="${pki}" style="display:inline-flex; align-items:center; background:var(--surface-2); border:1px solid var(--border-color); border-radius:6px; padding:3px 8px; font-size:0.68rem; white-space:nowrap; flex-shrink:0;">
                        <span style="color:var(--text-primary); font-weight:700; margin-right:4px;">${escapeHtml(pkg.unit_name || '')}</span>
                        <span style="color:var(--text-muted); margin-right:5px; font-size:0.6rem;">(x${pkg.base_qty})</span>
                        <span style="color:var(--success); font-weight:800;">${fmtRp(ret)}</span>
                    </div>`;
                }).join('');
                pkgHtml = `
                <div style="display:flex; overflow-x:auto; gap:6px; margin-top:6px; padding-bottom:2px; scrollbar-width:none; width:100%;">
                    ${pkgItems}
                </div>`;
            }

            return `
            <div data-pi="${pi}" class="search-result-row">
                <div class="res-thumb-wrap">${thumbHtml}</div>
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:6px;">
                        <div style="font-weight:700; font-size:0.82rem; color:var(--text-primary); line-height:1.3; word-break:break-word;">
                            ${escapeHtml(p.short_label || p.full_name)}
                        </div>
                        <div style="width:24px; height:24px; border-radius:50%; background:var(--primary-bg); color:var(--primary); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="bi bi-plus-lg" style="font-size:0.8rem; font-weight:bold;"></i>
                        </div>
                    </div>
                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:2px; display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                        <span style="background:var(--surface-2); padding:1px 6px; border-radius:4px;">${escapeHtml(p.brand_name ? p.brand_name : 'Tanpa Merek')}</span>
                        ${p.last_buy_price ? `<span>Beli: <strong style="color:var(--text-primary);">${fmtRp(p.last_buy_price)}</strong></span>` : ''}
                    </div>
                    ${pkgHtml}
                </div>
            </div>`;
        }).join('');
        
        elResults.innerHTML = html;
        elResults.style.display = 'block';

        // Attach click listener: clicking row or packaging pill adds item and CLOSES dropdown immediately
        Array.from(elResults.querySelectorAll('.search-result-row')).forEach(row => {
            row.addEventListener('click', (e) => {
                const pi = parseInt(row.dataset.pi, 10);
                const p = results[pi];
                if (!p) return;

                // Check if specific packaging pill was clicked
                const pkgPill = e.target.closest('.pkg-pill-item');
                let selectedPkgId = null;
                if (pkgPill && p.packagings) {
                    const pki = parseInt(pkgPill.dataset.pki, 10);
                    if (p.packagings[pki]) {
                        selectedPkgId = p.packagings[pki].id;
                    }
                }

                addItem(p, selectedPkgId);
                closeProductSearchResults();
            });
        });
    }

    function addItem(product, specificPkgId = null) {
        const prodId = parseInt(product.id, 10);
        const packs = Array.isArray(product.packagings) && product.packagings.length > 0 ? product.packagings : [];
        const fallbackBasePrice = parseFloat(product.last_buy_price) || 0;
        
        packs.forEach(pk => {
            if (!pk.buy_price || parseFloat(pk.buy_price) === 0) {
                pk.buy_price = fallbackBasePrice * (parseFloat(pk.base_qty) || 1);
            }
        });

        let targetPkg = null;
        if (specificPkgId && packs.length > 0) {
            targetPkg = packs.find(pk => pk.id == specificPkgId);
        }
        if (!targetPkg && packs.length > 0) {
            targetPkg = packs[0];
        }

        const pkgId = targetPkg ? parseInt(targetPkg.id, 10) : 0;
        const displayName = product.full_name || product.short_label || product.invoice_name || 'Produk';
        const labelName = product.short_label || product.invoice_name || displayName;

        // Dedup: increment qty if same product AND packaging already in list
        const existing = orderItems.find(it => it.product_id === prodId && it.packaging_id === pkgId);
        if (existing) {
            existing.qty++;
            renderList();
            return;
        }
        
        orderItems.unshift({
            product_id: prodId,
            packagings: packs,
            packaging_id: pkgId,
            name: displayName,
            short_label: labelName,
            unit_name: targetPkg ? (targetPkg.unit_name || 'pcs') : (product.unit_small_name || 'pcs'),
            buy_price: targetPkg ? parseFloat(targetPkg.buy_price || 0) : fallbackBasePrice,
            qty: 1,
            base_qty: targetPkg ? parseInt(targetPkg.base_qty || 1, 10) : 1
        });
        renderList();
    }

    // ── Supplier Search ──
    elSupplierInput.addEventListener('input', () => {
        const q = elSupplierInput.value.trim();
        elBtnClearSupplier.style.display = q.length > 0 ? 'flex' : 'none';
        clearTimeout(supplierSearchTimer);
        if (q.length < 1) {
            elSupplierResults.style.display = 'none';
            elSupplier.value = '';
            elSupplierName.value = '';
            return;
        }
        supplierSearchTimer = setTimeout(() => doSupplierSearch(q), 250);
    });

    elBtnClearSupplier.addEventListener('click', () => {
        elSupplierInput.value = '';
        elSupplier.value = '';
        elSupplierName.value = '';
        elBtnClearSupplier.style.display = 'none';
        elSupplierResults.style.display = 'none';
    });

    document.addEventListener('click', (e) => {
        if (!elSupplierResults.contains(e.target) && e.target !== elSupplierInput) {
            elSupplierResults.style.display = 'none';
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
            elSupplierResults.innerHTML = '<div style="padding:14px; color:var(--text-muted); text-align:center; font-size:11px;">Gagal mencari supplier.</div>';
            elSupplierResults.style.display = 'block';
        }
    }

    function renderSupplierResults(results) {
        if (!results || results.length === 0) {
            elSupplierResults.innerHTML = '<div style="padding:14px; color:var(--text-muted); text-align:center; font-size:11px;">Supplier tidak ditemukan.</div>';
            elSupplierResults.style.display = 'block';
            return;
        }
        const html = [];
        results.forEach((s) => {
            const isRep = typeof s.is_sales_rep !== 'undefined' ? s.is_sales_rep : (s.sales_rep_id ? true : false);
            const supName = s.supplier_name || s.name;
            const subText = isRep ? `Sales: ${s.name} (${s.phone || '-'})` : (s.type_name || 'Distributor');
            html.push(`<div class="search-result-row" data-id="${s.supplier_id || s.id}" data-name="${escapeHtml(supName)}">
                <div style="width:34px; height:34px; border-radius:8px; background:var(--surface-2); display:flex; align-items:center; justify-content:center; color:var(--text-muted);">
                    <i class="bi bi-building"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; font-size:0.82rem; color:var(--text-primary);">${escapeHtml(supName)}</div>
                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:1px;">${escapeHtml(subText)}</div>
                </div>
            </div>`);
        });
        elSupplierResults.innerHTML = html.join('');
        elSupplierResults.style.display = 'block';

        Array.from(elSupplierResults.querySelectorAll('.search-result-row')).forEach(row => {
            row.addEventListener('click', () => {
                elSupplier.value = row.dataset.id;
                elSupplierName.value = row.dataset.name;
                elSupplierInput.value = row.dataset.name;
                elBtnClearSupplier.style.display = 'flex';
                elSupplierResults.style.display = 'none';
            });
        });
    }

    // ── Copy to WhatsApp: Clean format (Only product label + quantity + unit) ──
    document.getElementById('btnCopyOrder').addEventListener('click', async () => {
        if (orderItems.length === 0) {
            if (typeof showToast === 'function') showToast('Daftar orderan masih kosong', 'warning');
            return;
        }

        const lines = [];
        orderItems.forEach(it => {
            const qty = parseFloat(it.qty) || 0;
            if (qty <= 0) return;
            const labelName = it.short_label || it.name;
            const unit = it.unit_name || 'pcs';
            lines.push(`${labelName} ${qty} ${unit}`);
        });

        if (lines.length === 0) {
            if (typeof showToast === 'function') showToast('Jumlah item orderan masih 0', 'warning');
            return;
        }

        const text = lines.join('\n');

        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(text);
                if (typeof showToast === 'function') {
                    showToast('✅ Daftar orderan berhasil disalin ke clipboard!', 'success');
                }
            } else {
                promptCopyFallback(text);
            }
        } catch (err) {
            promptCopyFallback(text);
        }
    });

    function promptCopyFallback(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        if (typeof showToast === 'function') {
            showToast('✅ Daftar orderan berhasil disalin!', 'success');
        }
    }

    // ── Save Draft ──
    window.saveOrderDraft = function() {
        if (orderItems.length === 0) {
            if (typeof showToast === 'function') showToast('Daftar orderan kosong', 'warning');
            return;
        }

        const defaultName = draftTitle || `Draft ${new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'})}`;

        if (typeof AppModal !== 'undefined') {
            AppModal.show({
                title: 'Simpan Draft Order',
                subtitle: `${orderItems.length} item orderan`,
                icon: 'bi-save',
                bodyHTML: `
                    <div class="modal-form-group">
                        <label style="font-size:12px; font-weight:600; color:var(--text-secondary); margin-bottom:6px; display:block;">Judul / Nama Draft</label>
                        <input type="text" class="form-control-dark" id="orderDraftNameInput" value="${escapeHtml(defaultName)}" 
                               placeholder="Cth: Pesanan Grosir A..." autocomplete="off" style="width:100%; padding:10px 12px; font-size:13px;">
                    </div>
                `,
                submitText: 'Simpan Draft',
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
                        
                        showToast(data.message || 'Draft berhasil disimpan', 'success');
                        currentDraftId = data.id;
                        draftTitle = name;
                        return true;
                    } catch(e) {
                        showToast(e.message, 'error');
                        return false;
                    }
                }
            });
        }
    };

    // ── Open Drafts List ──
    window.openOrderDrafts = async function() {
        try {
            const res = await fetch(`<?= BASE_URL ?>api/orders/estimates`);
            const data = await res.json();
            if (!data.success) throw new Error(data.error);
            const drafts = data.data || [];
            
            if (drafts.length === 0) {
                if (typeof showToast === 'function') showToast('Tidak ada draft tersimpan', 'info');
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

            if (typeof AppModal !== 'undefined') {
                AppModal.show({
                    title: 'Draft Orderan',
                    subtitle: `${drafts.length} draft tersedia`,
                    icon: 'bi-journal-bookmark',
                    bodyHTML: `<div id="orderDraftListContainer" style="max-height:55vh;overflow-y:auto;">${listHtml}</div>`,
                    hideFooter: true,
                    cancelText: 'Tutup'
                });
            }
        } catch(e) {
            if (typeof showToast === 'function') showToast('Gagal memuat daftar draft', 'error');
        }
    };

    window.loadOrderDraft = async function(id) {
        if (orderItems.length > 0) {
            const ok = await AppModal.confirm('Muat Draft', 'Daftar orderan saat ini akan ditimpa dengan draft yang dipilih.', 'Ya, Timpa', 'var(--warning)');
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
            if (elSupplierName.value) {
                elBtnClearSupplier.style.display = 'flex';
            }
            
            renderList();
            if (typeof AppModal !== 'undefined') AppModal.close();
            if (typeof showToast === 'function') showToast('Draft berhasil dimuat', 'success');
        } catch(e) {
            if (typeof showToast === 'function') showToast(e.message, 'error');
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
            if (typeof AppModal !== 'undefined') AppModal.close();
            setTimeout(window.openOrderDrafts, 300);
            if (typeof showToast === 'function') showToast('Draft dihapus', 'success');
        } catch(e) {
            if (typeof showToast === 'function') showToast(e.message, 'error');
        }
    };

    recompute();
})();
</script>
