<!-- View: Edit Barcode Kemasan -->
<?php /** @var string $csrfToken */ ?>
<div class="page-section" style="max-width:900px; margin:0 auto; padding-bottom:100px;">
    <!-- Page Header -->
    <div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
        <div>
            <h1 style="font-size:var(--font-size-xl); font-weight:800; color:var(--text-primary); margin:0; display:flex; align-items:center; gap:10px;">
                <i class="bi bi-upc-scan" style="color:var(--primary);"></i> Edit Barcode Kemasan
            </h1>
            <p style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:4px;">
                Cari produk atau scan barcode untuk mengedit barcode setiap level kemasan
            </p>
        </div>
        <a href="<?= BASE_URL ?>products" class="btn-outline-custom" style="font-size:var(--font-size-xs); padding:6px 12px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
            <i class="bi bi-box-seam"></i> Kelola Produk
        </a>
    </div>

    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- Search Section Card -->
    <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:20px; border:1px solid var(--border-color); box-shadow:var(--shadow-sm); margin-bottom:20px;">
        <label style="font-size:var(--font-size-xs); font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px; display:block;">
            Pencarian Produk (Nama / Brand / Barcode)
        </label>
        
        <div class="search-input-wrapper" style="position:relative; background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:4px 14px; display:flex; align-items:center; gap:10px; transition:border-color 0.2s;">
            <i class="bi bi-search" style="color:var(--text-muted); font-size:1.1rem;"></i>
            <input type="text" id="barcodeEditorSearch" placeholder="Scan barcode atau ketik kata kunci..." 
                   style="flex:1; border:none; background:transparent; padding:12px 0; color:var(--text-primary); font-size:var(--font-size-base); outline:none; font-family:var(--font-family);" 
                   autocomplete="off" autofocus>
            <button type="button" onclick="triggerBarcodeCameraScan()" class="btn-outline-custom" style="padding:6px 10px; font-size:12px; display:inline-flex; align-items:center; gap:6px;" title="Scan Barcode Kamera">
                <i class="bi bi-camera"></i> Scan Kamera
            </button>
        </div>
        <div style="font-size:11px; color:var(--text-muted); margin-top:6px; display:flex; justify-content:space-between; align-items:center;">
            <span><i class="bi bi-info-circle"></i> Mendukung alat pemindai scanner barcode USB/Bluetooth &amp; multi-keyword.</span>
            <span id="scanStatusBadge" style="color:var(--success); font-weight:600; display:none;"><i class="bi bi-check-circle-fill"></i> Barcode Terdeteksi</span>
        </div>

        <!-- Search Suggestions Dropdown -->
        <div id="barcodeEditorSuggestions" style="margin-top:10px;"></div>
    </div>

    <!-- Product Editor Card / Dialog -->
    <div id="productEditorContainer" style="display:none;">
        <div style="background:var(--surface-1); border-radius:var(--radius-lg); border:1px solid var(--border-color); overflow:hidden; box-shadow:var(--shadow-md);">
            <!-- Product Header Summary -->
            <div style="padding:20px; background:var(--surface-2); border-bottom:1px solid var(--border-color); display:flex; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                <div id="editorProductThumb" style="width:64px; height:64px; border-radius:var(--radius-md); background:var(--bg-input); border:1px solid var(--border-color); overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="bi bi-box-seam" style="font-size:2rem; color:var(--primary);"></i>
                </div>
                <div style="flex:1; min-width:200px;">
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <h2 id="editorProductName" style="font-size:var(--font-size-lg); font-weight:800; color:var(--text-primary); margin:0;">
                            Nama Produk
                        </h2>
                        <span id="editorProductCodeBadge" class="badge-custom badge-info" style="font-size:11px;">SKU: —</span>
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:4px; display:flex; gap:12px; flex-wrap:wrap;">
                        <span>Merk: <strong id="editorProductBrand" style="color:var(--text-primary);">—</strong></span>
                        <span>&middot;</span>
                        <span>Kategori: <strong id="editorProductCategory" style="color:var(--text-primary);">—</strong></span>
                    </div>
                </div>
                <button type="button" class="btn-outline-custom" style="padding:6px 12px; font-size:12px;" onclick="closeProductEditor()">
                    <i class="bi bi-x-lg"></i> Tutup
                </button>
            </div>

            <!-- Form Content -->
            <form id="barcodeEditorForm" onsubmit="saveBarcodeEditorForm(event)" style="padding:20px;">
                <input type="hidden" id="editorProductId" value="">

                <div style="margin-bottom:16px;">
                    <h3 style="font-size:var(--font-size-sm); font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin:0 0 12px 0;">
                        Daftar Barcode Kemasan
                    </h3>

                    <div id="packagingListContainer" style="display:flex; flex-direction:column; gap:14px;">
                        <!-- Dynamic Packaging Rows Will Be Injected Here -->
                    </div>
                </div>

                <!-- Footer Save Buttons -->
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px; padding-top:16px; border-top:1px solid var(--border-color);">
                    <button type="button" class="btn-outline-custom" onclick="closeProductEditor()" style="padding:10px 20px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSaveBarcodes" class="btn-primary-custom" style="padding:10px 24px; font-size:14px; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                        <i class="bi bi-check-circle-fill"></i> Simpan Barcode Kemasan
                    </button>
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
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ===== Global Hardware Barcode Scanner Listener =====
let _barEditorBuffer = '';
let _barEditorLastKeyTime = 0;
let _barEditorTimer = null;

document.addEventListener('keydown', function(e) {
    const activeEl = document.activeElement;
    
    // Check if user is typing inside one of the barcode edit input fields or modal
    const isOtherInputFocused = activeEl && activeEl !== searchInput && (
        activeEl.tagName === 'INPUT' || 
        activeEl.tagName === 'TEXTAREA' || 
        activeEl.tagName === 'SELECT'
    );

    const now = Date.now();
    const timeDiff = now - _barEditorLastKeyTime;
    _barEditorLastKeyTime = now;

    const isFastScannerSpeed = timeDiff < 50;

    // If typing inside another input, don't hijack unless fast scanner speed
    if (isOtherInputFocused && !isFastScannerSpeed && _barEditorBuffer.length === 0) {
        return;
    }

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
        if (timeDiff > 60) {
            _barEditorBuffer = e.key;
        } else {
            _barEditorBuffer += e.key;
        }

        clearTimeout(_barEditorTimer);
        _barEditorTimer = setTimeout(() => {
            if (_barEditorBuffer.length >= 8 && /^\d{8,16}$/.test(_barEditorBuffer)) {
                const code = _barEditorBuffer.trim();
                _barEditorBuffer = '';
                if (searchInput) searchInput.value = '';
                processProductSearchOrBarcode(code);
            } else if (!isOtherInputFocused && activeEl !== searchInput && _barEditorBuffer.length > 0) {
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

    const runSearch = typeof debounce === 'function' 
        ? debounce(performProductSearch, 300) 
        : performProductSearch;

    searchInput.addEventListener('input', () => runSearch());

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = this.value.trim();
            this.value = ''; // Reset input field immediately to prevent barcode stacking
            suggestionsDiv.innerHTML = '';
            if (!q) return;
            processProductSearchOrBarcode(q);
        }
    });
});

async function processProductSearchOrBarcode(rawQuery) {
    const q = String(rawQuery || '').trim();
    if (!q) return;

    if (searchInput) searchInput.value = '';
    if (suggestionsDiv) suggestionsDiv.innerHTML = '';

    const isBarcode = /^\d{6,16}$/.test(q);

    if (isBarcode) {
        // Try direct barcode API lookup
        try {
            const data = await api(`${BASE_URL}api/products/barcode/${encodeURIComponent(q)}`);
            if (data && data.id) {
                loadProductToEditor(data.id);
                showToast(`Produk "${data.short_label || data.full_name || data.name}" ditemukan`, 'success');
                return true;
            }
        } catch (e) {
            /* fallback to text search */
        }
    }

    // Keyword Search
    if (searchInput) searchInput.value = q;
    performProductSearch();
}

async function performProductSearch() {
    const q = searchInput ? searchInput.value.trim() : '';
    if (q.length < 2) {
        if (suggestionsDiv) suggestionsDiv.innerHTML = '';
        return;
    }

    try {
        const url = `${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`;
        const data = await api(url);

        if (!Array.isArray(data) || data.length === 0) {
            suggestionsDiv.innerHTML = `
                <div style="padding:16px; text-align:center; background:var(--surface-2); border-radius:var(--radius-md); border:1px solid var(--border-color);">
                    <div style="font-size:13px; color:var(--text-muted);">Produk tidak ditemukan untuk "${escapeHtml(q)}"</div>
                </div>`;
            return;
        }

        // Auto select if exact match or single result
        if (data.length === 1 && /^\d{6,16}$/.test(q)) {
            suggestionsDiv.innerHTML = '';
            searchInput.value = '';
            loadProductToEditor(data[0].id);
            return;
        }

        suggestionsDiv.innerHTML = data.map(p => {
            const thumbHtml = p.photo 
                ? `<div style="width:40px; height:40px; border-radius:var(--radius-sm); overflow:hidden; flex-shrink:0; background:transparent;">
                       <img src="${BASE_URL}${p.photo.replace(/"/g, '&quot;')}" style="width:100%; height:100%; object-fit:contain;" loading="lazy">
                   </div>`
                : `<div style="width:40px; height:40px; background:var(--primary-bg); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; color:var(--primary); flex-shrink:0;">
                       <i class="bi bi-box-seam"></i>
                   </div>`;

            const barcodes = (p.packagings || []).map(pkg => pkg.barcode).filter(Boolean).join(', ') || 'Belum ada barcode';

            return `
            <div onclick="selectProductFromSearch(${p.id})" style="padding:10px 14px; background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-md); margin-bottom:6px; cursor:pointer; display:flex; align-items:center; gap:12px; transition:all 0.2s;" onmouseover="this.style.background='var(--surface-2)'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='var(--surface-1)'; this.style.borderColor='var(--border-color)';">
                ${thumbHtml}
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; font-size:13px; color:var(--text-primary); line-height:1.3;">${escapeHtml(p.short_label || p.full_name)}</div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                        <span>${escapeHtml(p.brand_name || 'Tanpa Merek')}</span> &middot; 
                        <span style="font-family:monospace;">${escapeHtml(barcodes)}</span>
                    </div>
                </div>
                <button type="button" class="btn-outline-custom" style="padding:4px 10px; font-size:11px;">Pilih</button>
            </div>`;
        }).join('');
    } catch (e) {
        console.error("Barcode Editor Search Error:", e);
        suggestionsDiv.innerHTML = `<div style="padding:12px; text-align:center; color:var(--danger); font-size:12px;">Pencarian gagal: ${e.message}</div>`;
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
        if (!product || !product.id) {
            showToast('Gagal memuat detail produk', 'error');
            return;
        }

        currentEditorProduct = product;

        document.getElementById('editorProductId').value = product.id;
        document.getElementById('editorProductName').textContent = product.full_name || product.short_label;
        document.getElementById('editorProductCodeBadge').textContent = 'SKU: ' + (product.code || '—');
        document.getElementById('editorProductBrand').textContent = product.brand_name || 'Tanpa Merek';
        document.getElementById('editorProductCategory').textContent = product.category_name || 'Tanpa Kategori';

        // Thumbnail
        const thumbDiv = document.getElementById('editorProductThumb');
        if (product.photo) {
            thumbDiv.innerHTML = `<img src="${BASE_URL}${product.photo.replace(/"/g, '&quot;')}" style="width:100%; height:100%; object-fit:contain;">`;
        } else {
            thumbDiv.innerHTML = `<i class="bi bi-box-seam" style="font-size:2rem; color:var(--primary);"></i>`;
        }

        // Render Packagings List
        const pkgContainer = document.getElementById('packagingListContainer');
        if (product.packagings && Array.isArray(product.packagings) && product.packagings.length > 0) {
            pkgContainer.innerHTML = product.packagings.map(pkg => {
                const buyP = parseFloat(pkg.buy_price) || 0;
                const retP = parseFloat(pkg.sell_price_retail) || 0;
                const whoP = parseFloat(pkg.sell_price_wholesale) || 0;

                return `
                <div style="background:var(--surface-2); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px; position:relative;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:10px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="badge-custom badge-primary" style="font-size:11px; font-weight:700;">Level ${pkg.level}</span>
                            <span style="font-weight:700; font-size:14px; color:var(--text-primary);">${escapeHtml(pkg.unit_name || 'Satuan')}</span>
                            <span style="font-size:11px; color:var(--text-muted); background:var(--surface-1); padding:2px 8px; border-radius:10px; border:1px solid var(--border-color);">
                                Isi: <strong>${pkg.contained_qty || pkg.base_qty || 1}</strong> item
                            </span>
                        </div>
                        
                        <!-- Financial info in small badges -->
                        <div style="font-size:11px; color:var(--text-muted); display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                            <span>Modal: <strong style="color:var(--text-primary);">${formatRupiah(buyP)}</strong></span>
                            <span>&middot;</span>
                            <span>Ecer: <strong style="color:var(--success);">${formatRupiah(retP)}</strong></span>
                            ${whoP > 0 ? `<span>&middot; Grosir: <strong style="color:var(--info);">${formatRupiah(whoP)}</strong></span>` : ''}
                        </div>
                    </div>

                    <div style="display:flex; gap:8px; align-items:center;">
                        <div style="flex:1; position:relative;">
                            <input type="text" id="pkg_barcode_${pkg.id}" name="barcodes[${pkg.id}]" value="${escapeHtml(pkg.barcode || '')}" 
                                   placeholder="Masukkan atau scan barcode kemasan ${escapeHtml(pkg.unit_name)}..." 
                                   class="form-control-dark" style="width:100%; height:42px; font-family:monospace; font-size:13px; padding-right:36px;" autocomplete="off">
                            <i class="bi bi-upc" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:1.1rem; pointer-events:none;"></i>
                        </div>
                        <button type="button" class="btn-outline-custom" style="height:42px; padding:0 12px; font-size:12px;" onclick="clearPkgBarcode(${pkg.id})" title="Kosongkan Barcode">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>`;
            }).join('');
        } else {
            pkgContainer.innerHTML = `<div style="padding:16px; text-align:center; color:var(--text-muted);">Tidak ada data kemasan produk.</div>`;
        }

        document.getElementById('productEditorContainer').style.display = 'block';
        document.getElementById('productEditorContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });

    } catch (err) {
        console.error("Failed to load product for barcode editor:", err);
        showToast('Gagal memuat data produk', 'error');
    }
}

function clearPkgBarcode(pkgId) {
    const input = document.getElementById(`pkg_barcode_${pkgId}`);
    if (input) {
        input.value = '';
        input.focus();
    }
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
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...`;

    try {
        const productId = currentEditorProduct.id;
        const barcodes = {};
        
        currentEditorProduct.packagings.forEach(pkg => {
            const input = document.getElementById(`pkg_barcode_${pkg.id}`);
            if (input) {
                barcodes[pkg.id] = input.value.trim();
            }
        });

        const csrfToken = document.getElementById('csrfToken').value;
        const resp = await fetch(`${BASE_URL}api/products/${productId}/update-barcodes`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                barcodes: barcodes,
                csrf_token: csrfToken
            })
        });

        const resData = await resp.json();

        if (resp.status === 409 || resData.error === 'barcode_conflict') {
            showToast(resData.message || 'Barcode sudah digunakan oleh produk lain!', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
            return;
        }

        if (!resp.ok || resData.error) {
            throw new Error(resData.error || resData.message || 'Gagal menyimpan barcode');
        }

        showToast('Barcode kemasan berhasil disimpan!', 'success');
        closeProductEditor();

    } catch (err) {
        console.error("Save barcodes error:", err);
        showToast(err.message || 'Gagal menyimpan barcode', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function triggerBarcodeCameraScan() {
    if (typeof BarcodeUtil !== 'undefined' && BarcodeUtil.scanBarcode) {
        BarcodeUtil.scanBarcode(searchInput, (code) => {
            if (code) {
                processProductSearchOrBarcode(code);
            }
        });
    } else {
        const code = prompt('Masukkan kode barcode:');
        if (code) {
            processProductSearchOrBarcode(code);
        }
    }
}
</script>
