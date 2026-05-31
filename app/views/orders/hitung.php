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
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h4 style="font-weight:700; font-size:var(--font-size-md); margin:0;">Hitung Orderan</h4>
                <p style="font-size:var(--font-size-xs); color:var(--text-muted); margin:4px 0 0 0;">Susun daftar belanja ke supplier &amp; copy ke WhatsApp</p>
            </div>
            <div style="width:40px; height:40px; background:var(--success-bg); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--success);">
                <i class="bi bi-clipboard-check" style="font-size:1.2rem;"></i>
            </div>
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
        <input type="text" id="orderSearchInput" autocomplete="off" placeholder="Ketik nama produk, merk, atau berat..."
               style="background:var(--bg-primary); color:var(--text-primary); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:10px; width:100%; font-size:var(--font-size-sm);">
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
        <button id="btnCopyOrder" type="button" class="btn-primary-custom" style="flex:1; padding:12px; font-weight:700; border:none; border-radius:var(--radius-md); cursor:pointer; background:linear-gradient(135deg,#25d366,#128c7e); color:white;">
            <i class="bi bi-whatsapp"></i> Copy Pesan WhatsApp
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

    const orderItems = []; // { product_id, packaging_id, name, short_label, unit_name, buy_price, qty, base_qty }
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
            card.innerHTML = `
                <div style="flex:1; min-width:0;">
                    <div class="item-name">${escapeHtml(it.name)}</div>
                    <div class="item-meta">${escapeHtml(it.unit_name)} · ${fmtRp(it.buy_price)} / unit</div>
                </div>
                <button type="button" class="qty-btn" data-act="dec" data-idx="${idx}">−</button>
                <input type="number" class="qty-input" min="0" step="1" value="${it.qty}" data-idx="${idx}">
                <button type="button" class="qty-btn" data-act="inc" data-idx="${idx}">+</button>
                <button type="button" class="del-btn" data-act="del" data-idx="${idx}"><i class="bi bi-x"></i></button>
            `;
            elList.appendChild(card);
        });
        recompute();
    }

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
        if (typeof AppModal !== 'undefined' && AppModal.confirm) {
            AppModal.confirm({
                title: 'Kosongkan Daftar?',
                message: 'Semua item orderan akan dihapus.',
                confirmText: 'Ya, Kosongkan',
                onConfirm: () => { orderItems.length = 0; renderList(); }
            });
        } else if (confirm('Kosongkan daftar orderan?')) {
            orderItems.length = 0; renderList();
        }
    });

    // ── Search with debounce; uses /api/products/search (already has multi-word algorithm) ──
    elInput.addEventListener('input', () => {
        const q = elInput.value.trim();
        clearTimeout(searchTimer);
        if (q.length < 1) { elResults.style.display = 'none'; return; }
        searchTimer = setTimeout(() => doSearch(q), 250);
    });
    elInput.addEventListener('focus', () => {
        if (lastResults.length > 0) elResults.style.display = '';
    });
    document.addEventListener('click', (e) => {
        if (!elResults.contains(e.target) && e.target !== elInput) {
            elResults.style.display = 'none';
        }
    });

    async function doSearch(q) {
        try {
            const url = `<?= BASE_URL ?>api/products/search?q=${encodeURIComponent(q)}`;
            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            lastResults = Array.isArray(data) ? data : [];
            renderResults(lastResults);
        } catch (err) {
            console.error('Search failed:', err);
            elResults.innerHTML = '<div style="padding:12px; color:var(--text-muted); text-align:center; font-size:11px;">Gagal mencari. Coba lagi.</div>';
            elResults.style.display = '';
        }
    }

    function renderResults(results) {
        if (!results || results.length === 0) {
            elResults.innerHTML = '<div style="padding:12px; color:var(--text-muted); text-align:center; font-size:11px;">Tidak ada produk cocok.</div>';
            elResults.style.display = '';
            return;
        }
        const html = [];
        results.forEach((p, pi) => {
            const packs = Array.isArray(p.packagings) && p.packagings.length > 0 ? p.packagings : [];
            const displayName = p.full_name || p.short_label || p.invoice_name || 'Produk';
            if (packs.length === 0) {
                html.push(`<div class="search-result-row" data-pi="${pi}" data-ki="-1">
                    <div style="flex:1; min-width:0;">
                        <div class="res-name">${escapeHtml(displayName)}</div>
                        <div class="res-meta">— belum ada kemasan —</div>
                    </div>
                </div>`);
            } else {
                packs.forEach((pk, ki) => {
                    html.push(`<div class="search-result-row" data-pi="${pi}" data-ki="${ki}">
                        <div style="flex:1; min-width:0;">
                            <div class="res-name">${escapeHtml(displayName)}</div>
                            <div class="res-meta">Lv.${pk.level} · ${escapeHtml(pk.unit_name || 'pcs')} (isi ${pk.base_qty})</div>
                        </div>
                        <div class="res-price">${fmtRp(pk.buy_price)}</div>
                    </div>`);
                });
            }
        });
        elResults.innerHTML = html.join('');
        elResults.style.display = '';
        Array.from(elResults.querySelectorAll('.search-result-row')).forEach(row => {
            row.addEventListener('click', () => {
                const pi = parseInt(row.dataset.pi, 10);
                const ki = parseInt(row.dataset.ki, 10);
                const p = results[pi]; if (!p) return;
                const pk = (ki >= 0 && p.packagings) ? p.packagings[ki] : null;
                addItem(p, pk);
                elInput.value = '';
                elResults.style.display = 'none';
                lastResults = [];
            });
        });
    }

    function addItem(product, packaging) {
        const pkgId = packaging ? parseInt(packaging.id, 10) : 0;
        // Dedup: increment qty if same packaging already in list
        const existing = orderItems.find(it => it.product_id === parseInt(product.id, 10) && it.packaging_id === pkgId);
        if (existing) { existing.qty++; renderList(); return; }
        const displayName = product.full_name || product.short_label || product.invoice_name || 'Produk';
        orderItems.push({
            product_id: parseInt(product.id, 10),
            packaging_id: pkgId,
            name: displayName,
            short_label: product.short_label || product.invoice_name || displayName,
            unit_name: packaging ? (packaging.unit_name || 'pcs') : (product.unit_small_name || 'pcs'),
            buy_price: packaging ? parseFloat(packaging.buy_price || 0) : parseFloat(product.buy_price_small || 0),
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

    recompute();
})();
</script>
