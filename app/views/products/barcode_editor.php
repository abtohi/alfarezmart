<!-- View: Edit Barcode Kemasan — Elegant Modern UI -->
<?php /** @var string $csrfToken */ ?>

<style>
/* ===== BARCODE EDITOR STYLES ===== */
.bce-container {
    max-width: 860px;
    margin: 0 auto;
    padding-bottom: 100px;
}
.bce-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}
.bce-header-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    line-height: 1.3;
}
.bce-header-title i {
    background: linear-gradient(135deg, rgba(255,152,0,0.18), rgba(255,87,34,0.12));
    color: #ff9800;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}

/* Search Card */
.bce-search-card {
    background: var(--surface-1);
    border-radius: var(--radius-lg);
    padding: 22px;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 24px;
    position: relative;
}
.bce-search-bar {
    position: relative;
    background: var(--bg-input);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 14px;
    transition: border-color 0.25s, box-shadow 0.25s;
}
.bce-search-bar:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}
.bce-search-bar i.search-icon {
    color: var(--text-muted);
    font-size: 1.15rem;
    flex-shrink: 0;
}
.bce-search-bar input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 14px 0;
    color: var(--text-primary);
    font-size: var(--font-size-base);
    outline: none;
    font-family: var(--font-family);
}
.bce-search-bar input::placeholder {
    color: var(--text-muted);
    opacity: 0.7;
}
.bce-search-hint {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Suggestion items */
.bce-suggestion {
    padding: 12px 14px;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    margin-bottom: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s;
}
.bce-suggestion:hover {
    background: var(--surface-2);
    border-color: var(--primary);
    transform: translateX(2px);
}

/* Product Editor Card */
.bce-editor-card {
    background: var(--surface-1);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    overflow: hidden;
    box-shadow: 0 6px 28px rgba(0,0,0,0.12);
    animation: bceSlideIn 0.35s ease-out;
}
@keyframes bceSlideIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.bce-editor-header {
    padding: 20px 22px;
    background: linear-gradient(135deg, var(--surface-2), var(--surface-1));
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.bce-editor-thumb {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-md);
    background: var(--bg-input);
    border: 1px solid var(--border-color);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.bce-editor-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.bce-editor-info {
    flex: 1;
    min-width: 180px;
}
.bce-editor-name {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.3;
}
.bce-editor-meta {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 4px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}
.bce-editor-body {
    padding: 22px;
}
.bce-section-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.bce-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-color);
}

/* Packaging Row */
.bce-pkg-row {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 16px;
    margin-bottom: 12px;
    transition: border-color 0.2s;
}
.bce-pkg-row:hover {
    border-color: rgba(99,102,241,0.3);
}
.bce-pkg-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}
.bce-pkg-label {
    display: flex;
    align-items: center;
    gap: 8px;
}
.bce-pkg-level-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-bg);
    color: var(--primary);
    font-weight: 800;
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 6px;
    letter-spacing: 0.3px;
}
.bce-pkg-unit-name {
    font-weight: 700;
    font-size: 14px;
    color: var(--text-primary);
}
.bce-pkg-qty-badge {
    font-size: 10.5px;
    color: var(--text-muted);
    background: var(--surface-1);
    padding: 3px 10px;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    font-weight: 600;
}
.bce-pkg-prices {
    font-size: 10.5px;
    color: var(--text-muted);
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}
.bce-pkg-prices strong {
    font-weight: 700;
}

/* Barcode Input */
.bce-barcode-input-wrap {
    display: flex;
    gap: 8px;
    align-items: center;
}
.bce-barcode-field {
    flex: 1;
    position: relative;
}
.bce-barcode-field input {
    width: 100%;
    height: 44px;
    background: var(--bg-input);
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 0 40px 0 14px;
    font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
    font-size: 13.5px;
    color: var(--text-primary);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.bce-barcode-field input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}
.bce-barcode-field input::placeholder {
    color: var(--text-muted);
    opacity: 0.5;
    font-family: var(--font-family);
    font-size: 12px;
}
.bce-barcode-field .barcode-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 1.1rem;
    pointer-events: none;
    opacity: 0.5;
}
.bce-clear-btn {
    height: 44px;
    width: 44px;
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1rem;
}
.bce-clear-btn:hover {
    background: var(--danger-bg);
    border-color: var(--danger);
    color: var(--danger);
}

/* Footer */
.bce-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px solid var(--border-color);
}

/* Success indicator */
.bce-saved-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--success);
    font-weight: 700;
    font-size: 13px;
    opacity: 0;
    transition: opacity 0.3s;
}
.bce-saved-indicator.show {
    opacity: 1;
}
</style>

<div class="page-section bce-container">
    <!-- Header -->
    <div class="bce-header">
        <div>
            <h1 class="bce-header-title">
                <i class="bi bi-upc-scan"></i>
                Edit Barcode Kemasan
            </h1>
            <p style="font-size:var(--font-size-xs); color:var(--text-muted); margin:6px 0 0 0;">
                Scan barcode atau cari produk, lalu edit barcode setiap level kemasan.
            </p>
        </div>
        <a href="<?= BASE_URL ?>products" class="btn-outline-custom" style="font-size:var(--font-size-xs); padding:8px 14px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
            <i class="bi bi-box-seam"></i> Kelola Produk
        </a>
    </div>

    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- Search Card -->
    <div class="bce-search-card">
        <div class="bce-search-bar">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="barcodeEditorSearch" 
                   placeholder="Scan barcode atau ketik nama produk..." 
                   autocomplete="off" autofocus>
        </div>
        <div class="bce-search-hint">
            <i class="bi bi-info-circle"></i>
            Mendukung multi-keyword, scanner barcode USB/Bluetooth, &amp; pencarian nama/merek.
        </div>
        <div id="barcodeEditorSuggestions" style="margin-top:12px;"></div>
    </div>

    <!-- Product Editor (hidden until a product is selected) -->
    <div id="productEditorContainer" style="display:none;">
        <div class="bce-editor-card">
            <!-- Header -->
            <div class="bce-editor-header">
                <div class="bce-editor-thumb" id="editorProductThumb">
                    <i class="bi bi-box-seam" style="font-size:1.6rem; color:var(--primary);"></i>
                </div>
                <div class="bce-editor-info">
                    <h2 class="bce-editor-name" id="editorProductName">Nama Produk</h2>
                    <div class="bce-editor-meta">
                        <span id="editorProductCodeBadge" class="badge-custom badge-info" style="font-size:10px;">—</span>
                        <span>Merek: <strong id="editorProductBrand">—</strong></span>
                        <span>&middot;</span>
                        <span>Kategori: <strong id="editorProductCategory">—</strong></span>
                    </div>
                </div>
                <button type="button" class="btn-outline-custom" style="padding:8px 14px; font-size:12px;" onclick="closeProductEditor()">
                    <i class="bi bi-x-lg"></i> Tutup
                </button>
            </div>

            <!-- Body -->
            <form id="barcodeEditorForm" onsubmit="saveBarcodeEditorForm(event)">
                <div class="bce-editor-body">
                    <div class="bce-section-title">
                        <i class="bi bi-box-seam" style="font-size:14px; color:var(--primary);"></i>
                        Barcode Per Kemasan
                    </div>
                    <div id="packagingListContainer"></div>
                </div>

                <!-- Footer -->
                <div style="padding:0 22px 22px;">
                    <div class="bce-footer">
                        <span class="bce-saved-indicator" id="savedIndicator">
                            <i class="bi bi-check-circle-fill"></i> Tersimpan
                        </span>
                        <button type="button" class="btn-outline-custom" onclick="closeProductEditor()" style="padding:10px 20px;">
                            Batal
                        </button>
                        <button type="submit" id="btnSaveBarcodes" class="btn-primary-custom" style="padding:10px 26px; font-size:14px; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                            <i class="bi bi-check-circle-fill"></i> Simpan Barcode
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentEditorProduct = null;
const searchInput = document.getElementById('barcodeEditorSearch');
const suggestionsDiv = document.getElementById('barcodeEditorSuggestions');

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ===== Global Hardware Barcode Scanner Listener =====
let _barEditorBuffer = '';
let _barEditorLastKeyTime = 0;
let _barEditorTimer = null;

document.addEventListener('keydown', function(e) {
    const activeEl = document.activeElement;
    const isBarInput = activeEl && activeEl.dataset && activeEl.dataset.barcodeInput === '1';

    // *** KEY FIX: If user is focused on a barcode edit field, DO NOTHING. ***
    // Let the scanner type directly into that field naturally.
    if (isBarInput) {
        // Only reset the buffer so it doesn't accumulate stale chars
        _barEditorBuffer = '';
        return;
    }

    const isOtherInput = activeEl && activeEl !== searchInput && (
        activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT'
    );

    const now = Date.now();
    const timeDiff = now - _barEditorLastKeyTime;
    _barEditorLastKeyTime = now;
    const isFast = timeDiff < 50;

    if (isOtherInput && !isFast && _barEditorBuffer.length === 0) return;

    if (e.key === 'Enter') {
        if (_barEditorBuffer.length >= 6) {
            e.preventDefault();
            const code = _barEditorBuffer.trim();
            _barEditorBuffer = '';
            if (searchInput) searchInput.value = '';
            processProductSearchOrBarcode(code);
        }
        _barEditorBuffer = '';
        return;
    }

    if (e.key && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
        if (timeDiff > 60) _barEditorBuffer = e.key;
        else _barEditorBuffer += e.key;

        clearTimeout(_barEditorTimer);
        _barEditorTimer = setTimeout(() => {
            if (_barEditorBuffer.length >= 8 && /^\d{8,16}$/.test(_barEditorBuffer)) {
                const code = _barEditorBuffer.trim();
                _barEditorBuffer = '';
                if (searchInput) searchInput.value = '';
                processProductSearchOrBarcode(code);
            } else if (!isOtherInput && activeEl !== searchInput && _barEditorBuffer.length > 0) {
                if (searchInput) {
                    searchInput.focus();
                    searchInput.value = _barEditorBuffer;
                    searchInput.dispatchEvent(new Event('input'));
                }
            }
        }, 120);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    if (!searchInput) return;
    const runSearch = typeof debounce === 'function' ? debounce(performProductSearch, 300) : performProductSearch;
    searchInput.addEventListener('input', () => runSearch());
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = this.value.trim();
            this.value = '';
            suggestionsDiv.innerHTML = '';
            if (q) processProductSearchOrBarcode(q);
        }
    });
});

async function processProductSearchOrBarcode(rawQuery) {
    const q = String(rawQuery || '').trim();
    if (!q) return;
    if (searchInput) searchInput.value = '';
    if (suggestionsDiv) suggestionsDiv.innerHTML = '';

    if (/^\d{6,16}$/.test(q)) {
        try {
            const data = await api(`${BASE_URL}api/products/barcode/${encodeURIComponent(q)}`);
            if (data && data.id) {
                loadProductToEditor(data.id);
                showToast(`Produk "${data.short_label || data.full_name}" ditemukan`, 'success');
                return;
            }
        } catch (e) { /* fall through */ }
    }
    if (searchInput) searchInput.value = q;
    performProductSearch();
}

async function performProductSearch() {
    const q = searchInput ? searchInput.value.trim() : '';
    if (q.length < 2) { if (suggestionsDiv) suggestionsDiv.innerHTML = ''; return; }

    try {
        const data = await api(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`);
        if (!Array.isArray(data) || data.length === 0) {
            suggestionsDiv.innerHTML = `
                <div style="padding:20px; text-align:center; background:var(--surface-2); border-radius:var(--radius-md); border:1px solid var(--border-color);">
                    <i class="bi bi-search" style="font-size:1.6rem; color:var(--text-muted); opacity:0.4;"></i>
                    <div style="font-size:13px; color:var(--text-muted); margin-top:8px;">Tidak ditemukan hasil untuk "<strong>${escapeHtml(q)}</strong>"</div>
                </div>`;
            return;
        }

        if (data.length === 1 && /^\d{6,16}$/.test(q)) {
            suggestionsDiv.innerHTML = '';
            searchInput.value = '';
            loadProductToEditor(data[0].id);
            return;
        }

        suggestionsDiv.innerHTML = data.map(p => {
            const thumb = p.photo 
                ? `<div style="width:38px;height:38px;border-radius:6px;overflow:hidden;flex-shrink:0;"><img src="${BASE_URL}${p.photo.replace(/"/g,'&quot;')}" style="width:100%;height:100%;object-fit:contain;" loading="lazy"></div>`
                : `<div style="width:38px;height:38px;background:var(--primary-bg);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--primary);flex-shrink:0;font-size:1rem;"><i class="bi bi-box-seam"></i></div>`;
            return `
            <div class="bce-suggestion" onclick="selectProductFromSearch(${p.id})">
                ${thumb}
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:13px;color:var(--text-primary);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(p.short_label || p.full_name)}</div>
                    <div style="font-size:10.5px;color:var(--text-muted);margin-top:2px;">${escapeHtml(p.brand_name || '')} ${p.category_name ? '&middot; ' + escapeHtml(p.category_name) : ''}</div>
                </div>
                <i class="bi bi-chevron-right" style="color:var(--text-muted);opacity:0.4;"></i>
            </div>`;
        }).join('');
    } catch (e) {
        suggestionsDiv.innerHTML = `<div style="padding:14px;text-align:center;color:var(--danger);font-size:12px;">Pencarian gagal: ${e.message}</div>`;
    }
}

function selectProductFromSearch(id) {
    if (searchInput) searchInput.value = '';
    if (suggestionsDiv) suggestionsDiv.innerHTML = '';
    loadProductToEditor(id);
}

async function loadProductToEditor(productId) {
    try {
        const product = await api(`${BASE_URL}api/products/${productId}`);
        if (!product || !product.id) { showToast('Gagal memuat detail produk', 'error'); return; }
        currentEditorProduct = product;

        document.getElementById('editorProductName').textContent = product.short_label || product.full_name;
        document.getElementById('editorProductCodeBadge').textContent = product.code || '—';
        document.getElementById('editorProductBrand').textContent = product.brand_name || 'Tanpa Merek';
        document.getElementById('editorProductCategory').textContent = product.category_name || '—';

        const thumbDiv = document.getElementById('editorProductThumb');
        thumbDiv.innerHTML = product.photo
            ? `<img src="${BASE_URL}${product.photo.replace(/"/g,'&quot;')}">`
            : `<i class="bi bi-box-seam" style="font-size:1.6rem;color:var(--primary);"></i>`;

        const pkgContainer = document.getElementById('packagingListContainer');
        if (product.packagings && product.packagings.length > 0) {
            pkgContainer.innerHTML = product.packagings.map(pkg => {
                const buyP  = parseFloat(pkg.buy_price) || 0;
                const retP  = parseFloat(pkg.sell_price_retail) || 0;
                const whoP  = parseFloat(pkg.sell_price_wholesale) || 0;
                const qty   = pkg.base_qty || pkg.contained_qty || 1;
                return `
                <div class="bce-pkg-row">
                    <div class="bce-pkg-top">
                        <div class="bce-pkg-label">
                            <span class="bce-pkg-level-badge">Lv.${pkg.level}</span>
                            <span class="bce-pkg-unit-name">${escapeHtml(pkg.unit_name || 'Satuan')}</span>
                            <span class="bce-pkg-qty-badge">Isi ${qty} pcs</span>
                        </div>
                        <div class="bce-pkg-prices">
                            <span>Modal <strong style="color:var(--text-primary);">${formatRupiah(buyP)}</strong></span>
                            <span>&middot;</span>
                            <span>Ecer <strong style="color:var(--success);">${formatRupiah(retP)}</strong></span>
                            ${whoP > 0 ? `<span>&middot; Grosir <strong style="color:var(--info);">${formatRupiah(whoP)}</strong></span>` : ''}
                        </div>
                    </div>
                    <div class="bce-barcode-input-wrap">
                        <div class="bce-barcode-field">
                            <input type="text" id="pkg_barcode_${pkg.id}" data-barcode-input="1"
                                   value="${escapeHtml(pkg.barcode || '')}" 
                                   placeholder="Scan atau ketik barcode ${escapeHtml(pkg.unit_name)}..." 
                                   autocomplete="off">
                            <i class="bi bi-upc barcode-icon"></i>
                        </div>
                        <button type="button" class="bce-clear-btn" onclick="clearPkgBarcode(${pkg.id})" title="Kosongkan">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>`;
            }).join('');
        } else {
            pkgContainer.innerHTML = `<div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">Produk ini belum memiliki data kemasan.</div>`;
        }

        document.getElementById('savedIndicator').classList.remove('show');
        document.getElementById('productEditorContainer').style.display = 'block';
        document.getElementById('productEditorContainer').scrollIntoView({ behavior:'smooth', block:'start' });
    } catch (err) {
        console.error("Load product error:", err);
        showToast('Gagal memuat data produk', 'error');
    }
}

function clearPkgBarcode(pkgId) {
    const input = document.getElementById(`pkg_barcode_${pkgId}`);
    if (input) { input.value = ''; input.focus(); }
}

function closeProductEditor() {
    document.getElementById('productEditorContainer').style.display = 'none';
    currentEditorProduct = null;
    if (searchInput) searchInput.focus();
}

async function saveBarcodeEditorForm(e) {
    e.preventDefault();
    if (!currentEditorProduct) return;

    const btn = document.getElementById('btnSaveBarcodes');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Menyimpan...`;

    try {
        const barcodes = {};
        currentEditorProduct.packagings.forEach(pkg => {
            const input = document.getElementById(`pkg_barcode_${pkg.id}`);
            if (input) barcodes[pkg.id] = input.value.trim();
        });

        const csrf = document.getElementById('csrfToken').value;
        const resp = await fetch(`${BASE_URL}api/products/${currentEditorProduct.id}/update-barcodes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ barcodes, csrf_token: csrf })
        });
        const res = await resp.json();

        if (resp.status === 409 || res.error === 'barcode_conflict') {
            showToast(res.message || 'Barcode sudah digunakan produk lain!', 'error');
            return;
        }
        if (!resp.ok || res.error) throw new Error(res.error || res.message || 'Gagal menyimpan');

        showToast('Barcode kemasan berhasil disimpan!', 'success');
        const indicator = document.getElementById('savedIndicator');
        indicator.classList.add('show');
        setTimeout(() => indicator.classList.remove('show'), 3000);

    } catch (err) {
        showToast(err.message || 'Gagal menyimpan barcode', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
}
</script>
