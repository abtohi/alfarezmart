<!-- Multivariant Pricing View -->

<style>
    .mv-container { max-width: 1200px; margin: 0 auto; padding-bottom: 80px; }
    .mv-card { background: var(--surface); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px; border: 1px solid var(--border-color); }
    .mv-header { display: flex; align-items: center; margin-bottom: 24px; gap: 16px; }
    .mv-title { font-size: 20px; font-weight: 700; color: var(--text-primary); margin: 0; flex: 1; }
    
    /* Search Dropdown Custom */
    .mv-search-wrapper { position: relative; }
    .mv-search-input { width: 100%; padding: 12px 16px; padding-left: 40px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--surface-1); color: var(--text-primary); font-size: 14px; transition: all 0.2s; }
    .mv-search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); outline: none; background: var(--surface); }
    .mv-search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    
    .mv-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: var(--surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); margin-top: 4px; box-shadow: var(--shadow-md); z-index: 50; max-height: 300px; overflow-y: auto; display: none; }
    .mv-dropdown.active { display: block; }
    .mv-dropdown-item { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid var(--border-color); transition: background 0.2s; }
    .mv-dropdown-item:last-child { border-bottom: none; }
    .mv-dropdown-item:hover { background: var(--surface-1); }
    .mv-dropdown-item-title { font-weight: 600; color: var(--text-primary); margin-bottom: 4px; }
    .mv-dropdown-item-meta { font-size: 12px; color: var(--text-muted); }
    
    /* Target List */
    .target-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; margin-top: 16px; }
    .target-item { background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; display: flex; gap: 12px; align-items: flex-start; position: relative; transition: all 0.2s; }
    .target-item:hover { border-color: var(--primary); box-shadow: var(--shadow-sm); }
    .target-remove { position: absolute; top: 12px; right: 12px; background: rgba(239, 68, 68, 0.1); color: var(--danger); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; transition: all 0.2s; }
    .target-remove:hover { background: var(--danger); color: white; }
    
    /* Price Preview */
    .price-preview-table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
    .price-preview-table th, .price-preview-table td { padding: 10px 12px; border-bottom: 1px solid var(--border-color); text-align: left; }
    .price-preview-table th { background: var(--surface-1); font-weight: 600; color: var(--text-secondary); }
    
    .tier-badge { display: inline-block; padding: 2px 6px; background: var(--info-bg); color: var(--info); border-radius: 4px; font-size: 11px; margin-right: 4px; margin-bottom: 4px; }
    
    /* Floating Action */
    .mv-floating-action { position: fixed; bottom: 0; left: 0; right: 0; background: var(--surface); padding: 16px 24px; border-top: 1px solid var(--border-color); box-shadow: 0 -4px 12px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; z-index: 40; }
    @media (min-width: 992px) { .mv-floating-action { left: 260px; } }
    .mv-stats { display: flex; gap: 24px; }
    .mv-stat { display: flex; flex-direction: column; }
    .mv-stat-label { font-size: 12px; color: var(--text-muted); }
    .mv-stat-value { font-size: 16px; font-weight: 700; color: var(--text-primary); }
    
    .loading-overlay { position: absolute; inset: 0; background: rgba(255,255,255,0.7); display: flex; align-items: center; justify-content: center; z-index: 10; border-radius: var(--radius-lg); opacity: 0; pointer-events: none; transition: opacity 0.2s; }
    .loading-overlay.active { opacity: 1; pointer-events: auto; }
    
    .dark-mode .loading-overlay { background: rgba(15, 23, 42, 0.7); }
</style>

<div class="mv-container">
    <div class="mv-header">
        <a href="<?= BASE_URL ?>dashboard" class="btn btn-icon btn-light"><i class="bi bi-arrow-left"></i></a>
        <h1 class="mv-title">Harga Produk Multivarian</h1>
    </div>

    <!-- Step 1: Reference Product -->
    <div class="mv-card" style="position: relative;">
        <div class="loading-overlay" id="refLoader">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;"><span class="badge bg-primary rounded-circle me-2">1</span> Pilih Produk Referensi</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Cari dan pilih produk yang harganya sudah benar (termasuk harga modal, ecer, grosir, dan harga tier) untuk dijadikan acuan.</p>
        
        <div class="mv-search-wrapper">
            <i class="bi bi-search mv-search-icon"></i>
            <input type="text" class="mv-search-input" id="refSearchInput" placeholder="Ketik nama produk, barcode, atau kode barang..." autocomplete="off">
            <div class="mv-dropdown" id="refDropdown"></div>
        </div>
        
        <div id="refSelectedContainer" style="display: none; margin-top: 16px; padding: 16px; background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: var(--radius-md);">
            <div style="display: flex; gap: 16px; align-items: center;">
                <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); background: var(--surface); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--primary);">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 700; font-size: 16px; color: var(--primary);" id="refName">-</div>
                    <div style="font-size: 13px; color: var(--text-muted);" id="refMeta">-</div>
                </div>
                <button class="btn btn-sm btn-outline-danger" onclick="clearReference()">Ganti Referensi</button>
            </div>
            
            <div style="margin-top: 20px; overflow-x: auto;">
                <div style="font-size: 13px; font-weight: 600; margin-bottom: 8px;">Detail Harga Referensi (Akan disalin ke target)</div>
                <table class="price-preview-table" id="refPriceTable">
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Kemasan</th>
                            <th>Isi (Pcs)</th>
                            <th>Modal</th>
                            <th>Ecer</th>
                            <th>Grosir</th>
                            <th>Harga Tier (Grosir/Qty)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Step 2: Target Products -->
    <div class="mv-card" style="position: relative;" id="targetSection">
        <div class="loading-overlay" id="targetLoader">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;"><span class="badge bg-primary rounded-circle me-2">2</span> Varian Target</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">Produk-produk di bawah ini akan <strong>ditimpa konfigurasi kemasan dan harganya</strong> agar persis sama dengan Produk Referensi di atas. Varian terdeteksi otomatis, namun Anda bisa menambah/menghapus manual.</p>
        
        <div class="mv-search-wrapper mb-3">
            <i class="bi bi-plus-circle mv-search-icon"></i>
            <input type="text" class="mv-search-input" id="targetSearchInput" placeholder="Tambah produk target lainnya secara manual..." autocomplete="off" disabled>
            <div class="mv-dropdown" id="targetDropdown"></div>
        </div>

        <div class="target-list" id="targetList">
            <!-- Target items will be inserted here -->
            <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--text-muted); background: var(--surface-1); border-radius: var(--radius-md); border: 1px dashed var(--border-color);">
                Pilih Produk Referensi terlebih dahulu untuk mendeteksi varian secara otomatis.
            </div>
        </div>
    </div>
</div>

<div class="mv-floating-action" style="display: none;" id="floatingAction">
    <div class="mv-stats">
        <div class="mv-stat">
            <span class="mv-stat-label">Produk Referensi</span>
            <span class="mv-stat-value text-primary" id="fabRefName">-</span>
        </div>
        <div class="mv-stat">
            <span class="mv-stat-label">Total Target</span>
            <span class="mv-stat-value" id="fabTargetCount">0 Varian</span>
        </div>
    </div>
    <button class="btn btn-primary" id="btnApply" onclick="applyPricing()" style="padding: 12px 24px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i class="bi bi-check2-all" style="font-size: 18px;"></i> Aplikasikan Harga
    </button>
</div>

<script>
let referenceProduct = null;
let targetProducts = []; // Array of {id, name, meta}
let searchTimeout = null;

// DOM Elements
const refSearchInput = document.getElementById('refSearchInput');
const refDropdown = document.getElementById('refDropdown');
const targetSearchInput = document.getElementById('targetSearchInput');
const targetDropdown = document.getElementById('targetDropdown');

// Format Currency
const formatRp = (num) => 'Rp ' + parseFloat(num).toLocaleString('id-ID');

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Setup Reference Search
    refSearchInput.addEventListener('input', (e) => handleSearch(e.target.value, refDropdown, onSelectReference));
    
    // Setup Target Search
    targetSearchInput.addEventListener('input', (e) => handleSearch(e.target.value, targetDropdown, onSelectTargetManual));
    
    // Close dropdowns on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.mv-search-wrapper')) {
            refDropdown.classList.remove('active');
            targetDropdown.classList.remove('active');
        }
    });
});

async function handleSearch(keyword, dropdownEl, onSelectCb) {
    clearTimeout(searchTimeout);
    if (!keyword || keyword.length < 2) {
        dropdownEl.classList.remove('active');
        return;
    }
    
    searchTimeout = setTimeout(async () => {
        try {
            const res = await fetch(`<?= BASE_URL ?>api/products/search?q=${encodeURIComponent(keyword)}`);
            const data = await res.json();
            
            if (data && data.length > 0) {
                dropdownEl.innerHTML = '';
                data.forEach(prod => {
                    const el = document.createElement('div');
                    el.className = 'mv-dropdown-item';
                    let meta = `${prod.code} | ${prod.category_name || '-'} | ${prod.brand_name || '-'}`;
                    el.innerHTML = `
                        <div class="mv-dropdown-item-title">${prod.full_name || prod.short_label}</div>
                        <div class="mv-dropdown-item-meta">${meta}</div>
                    `;
                    el.addEventListener('click', () => {
                        dropdownEl.classList.remove('active');
                        onSelectCb(prod);
                    });
                    dropdownEl.appendChild(el);
                });
                dropdownEl.classList.add('active');
            } else {
                dropdownEl.innerHTML = '<div class="mv-dropdown-item"><div class="mv-dropdown-item-meta text-center">Produk tidak ditemukan</div></div>';
                dropdownEl.classList.add('active');
            }
        } catch (e) {
            console.error('Search error', e);
        }
    }, 300);
}

async function onSelectReference(prod) {
    document.getElementById('refLoader').classList.add('active');
    try {
        // Fetch full product details including packagings and tiers
        const res = await fetch(`<?= BASE_URL ?>api/products/${prod.id}`);
        const data = await res.json();
        
        if (data.success && data.product) {
            referenceProduct = data.product;
            
            // Update UI
            document.getElementById('refName').textContent = referenceProduct.full_name || referenceProduct.short_label;
            document.getElementById('fabRefName').textContent = referenceProduct.short_label || referenceProduct.full_name;
            document.getElementById('refMeta').textContent = `${referenceProduct.code} | Kategori: ${referenceProduct.category_name || '-'} | Brand: ${referenceProduct.brand_name || '-'}`;
            
            refSearchInput.style.display = 'none';
            document.getElementById('refSelectedContainer').style.display = 'block';
            
            renderReferenceTable();
            
            // Auto detect variants
            await loadVariants();
            
            // Enable target search
            targetSearchInput.disabled = false;
            document.getElementById('floatingAction').style.display = 'flex';
        } else {
            showToast('Gagal memuat data produk', 'error');
        }
    } catch (e) {
        console.error(e);
        showToast('Terjadi kesalahan jaringan', 'error');
    } finally {
        document.getElementById('refLoader').classList.remove('active');
    }
}

function renderReferenceTable() {
    const tbody = document.querySelector('#refPriceTable tbody');
    tbody.innerHTML = '';
    
    if (!referenceProduct.packagings || referenceProduct.packagings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Tidak ada data kemasan.</td></tr>';
        return;
    }
    
    referenceProduct.packagings.forEach(pkg => {
        let tierHtml = '';
        if (pkg.qty_prices && pkg.qty_prices.length > 0) {
            pkg.qty_prices.forEach(t => {
                tierHtml += `<span class="tier-badge">≥ ${t.min_qty} : ${formatRp(t.unit_price)} (${t.sale_mode})</span>`;
            });
        } else {
            tierHtml = '<span class="text-muted" style="font-size:11px;">-</span>';
        }
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="badge bg-secondary">${pkg.level}</span></td>
            <td><strong>${pkg.unit_name || 'Pcs'}</strong> <br><small class="text-muted">${pkg.barcode || '-'}</small></td>
            <td>${parseFloat(pkg.base_qty)}</td>
            <td>${formatRp(pkg.buy_price)}</td>
            <td>${formatRp(pkg.sell_price_retail)}</td>
            <td>${formatRp(pkg.sell_price_wholesale)}</td>
            <td>${tierHtml}</td>
        `;
        tbody.appendChild(tr);
    });
}

function clearReference() {
    referenceProduct = null;
    targetProducts = [];
    
    refSearchInput.value = '';
    refSearchInput.style.display = 'block';
    document.getElementById('refSelectedContainer').style.display = 'none';
    
    targetSearchInput.value = '';
    targetSearchInput.disabled = true;
    document.getElementById('floatingAction').style.display = 'none';
    
    renderTargetList();
    setTimeout(() => refSearchInput.focus(), 100);
}

async function loadVariants() {
    document.getElementById('targetLoader').classList.add('active');
    try {
        const res = await fetch(`<?= BASE_URL ?>api/products/${referenceProduct.id}/variants`);
        const data = await res.json();
        
        if (data.success && data.variants) {
            targetProducts = data.variants.map(v => ({
                id: v.id,
                name: v.full_name || v.short_label,
                meta: `${v.code} | Kategori: ${v.category_name || '-'} | Brand: ${v.brand_name || '-'}`
            }));
            renderTargetList();
        }
    } catch (e) {
        console.error(e);
        showToast('Gagal memuat varian otomatis', 'error');
    } finally {
        document.getElementById('targetLoader').classList.remove('active');
    }
}

function onSelectTargetManual(prod) {
    targetSearchInput.value = '';
    
    if (prod.id == referenceProduct.id) {
        showToast('Tidak bisa memilih produk referensi sebagai target', 'warning');
        return;
    }
    if (targetProducts.find(t => t.id == prod.id)) {
        showToast('Produk sudah ada di daftar target', 'warning');
        return;
    }
    
    targetProducts.unshift({
        id: prod.id,
        name: prod.full_name || prod.short_label,
        meta: `${prod.code} | Kategori: ${prod.category_name || '-'} | Brand: ${prod.brand_name || '-'}`
    });
    
    renderTargetList();
    showToast('Produk ditambahkan ke target', 'success');
}

function removeTarget(id) {
    targetProducts = targetProducts.filter(t => t.id != id);
    renderTargetList();
}

function renderTargetList() {
    const container = document.getElementById('targetList');
    const fabCount = document.getElementById('fabTargetCount');
    
    container.innerHTML = '';
    fabCount.textContent = `${targetProducts.length} Varian`;
    
    if (!referenceProduct) {
        container.innerHTML = '<div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--text-muted); background: var(--surface-1); border-radius: var(--radius-md); border: 1px dashed var(--border-color);">Pilih Produk Referensi terlebih dahulu untuk mendeteksi varian secara otomatis.</div>';
        return;
    }
    
    if (targetProducts.length === 0) {
        container.innerHTML = '<div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--text-muted); background: var(--surface-1); border-radius: var(--radius-md); border: 1px dashed var(--border-color);">Belum ada produk target. Silakan cari dan tambah manual.</div>';
        return;
    }
    
    targetProducts.forEach(t => {
        const div = document.createElement('div');
        div.className = 'target-item';
        div.innerHTML = `
            <div style="width: 40px; height: 40px; border-radius: var(--radius-sm); background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="bi bi-box"></i>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600; font-size: 14px; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${t.name}">${t.name}</div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">${t.meta}</div>
            </div>
            <button class="target-remove" onclick="removeTarget(${t.id})" title="Hapus dari target"><i class="bi bi-x-lg" style="font-size: 14px;"></i></button>
        `;
        container.appendChild(div);
    });
}

async function applyPricing() {
    if (!referenceProduct || targetProducts.length === 0) return;
    
    const confirm = await AppModal.show({
        title: 'Konfirmasi Multivariant',
        bodyHTML: `Anda akan menerapkan seluruh harga dan konfigurasi kemasan dari <strong>${referenceProduct.short_label}</strong> ke <strong>${targetProducts.length}</strong> produk target.<br><br><span class="text-danger">Peringatan: Data kemasan dan harga tier produk target akan ditimpa (di-replace) sepenuhnya. Tindakan ini tidak bisa dibatalkan.</span>`,
        submitText: 'Ya, Aplikasikan Sekarang',
        icon: 'bi-exclamation-triangle-fill',
        iconColor: 'var(--warning-bg)',
        iconAccent: 'var(--warning)'
    });
    
    if (!confirm) return;
    
    const btn = document.getElementById('btnApply');
    const oriHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
    btn.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('reference_id', referenceProduct.id);
        targetProducts.forEach(t => formData.append('target_ids[]', t.id));
        
        const res = await fetch('<?= BASE_URL ?>api/products/multivariant-apply', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showToast('Harga berhasil diaplikasikan ke seluruh target!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Gagal mengaplikasikan harga', 'error');
            btn.innerHTML = oriHtml;
            btn.disabled = false;
        }
    } catch (e) {
        console.error(e);
        showToast('Terjadi kesalahan jaringan', 'error');
        btn.innerHTML = oriHtml;
        btn.disabled = false;
    }
}
</script>


