<!-- Catalog Builder View -->
<div class="page-section" id="catalogBuilderSection">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:1.4rem; font-weight:800; margin:0; background:var(--gradient-primary); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Buat Katalog</h2>
        <button class="btn-primary-custom" onclick="generatePrintPreview()" id="btnGeneratePreview">
            <i class="bi bi-printer"></i> Generate & Cetak
        </button>
    </div>

    <div style="display:grid; grid-template-columns: 1fr; gap:20px; margin-bottom:24px;" class="desktop-grid-2">
        
        <!-- Left Panel: Product Selection -->
        <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:20px; border:1px solid var(--border-color); box-shadow:var(--shadow-sm);">
            <div class="section-title"><i class="bi bi-search" style="color:var(--primary);"></i> Cari Produk</div>
            
            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-size:12px; font-weight:600; color:var(--text-muted); margin-bottom:6px; display:block;">Pencarian Multi Keyword</label>
                <div style="display:flex; align-items:center; background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:0 12px; transition:all 0.2s;">
                    <i class="bi bi-search" style="color:var(--text-muted); font-size:1.1rem;"></i>
                    <input type="text" id="catalogSearch" placeholder="Ketik nama produk, merk, barcode..." 
                           style="flex:1; border:none; background:transparent; padding:12px 10px; color:var(--text-primary); font-size:var(--font-size-base); outline:none; font-family:var(--font-family);" autocomplete="off">
                </div>
                <div id="catalogSuggestions" style="margin-top:8px; max-height:360px; overflow-y:auto; border-radius:var(--radius-md); scroll-behavior:smooth;"></div>
            </div>

            <hr style="border-color:var(--border-color); margin:20px 0;">

            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-size:12px; font-weight:600; color:var(--text-muted); margin-bottom:6px; display:block;">Pilih Berdasarkan Kategori (Bulk)</label>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <div id="categorySearchBox" style="flex:1; min-width:200px;"></div>
                    <button onclick="addByCategory()" id="btnAddCategory" style="padding:10px 18px; white-space:nowrap; flex-shrink:0; border:none; border-radius:var(--radius-md); background:linear-gradient(135deg,#2dd36f,#1a9e4e); color:#fff; font-weight:700; font-size:13px; font-family:var(--font-family); cursor:pointer; display:flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(45,211,111,0.35); transition:all 0.2s; height:44px;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 18px rgba(45,211,111,0.45)'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 12px rgba(45,211,111,0.35)'" onmousedown="this.style.transform='scale(0.97)'" onmouseup="this.style.transform=''">
                        <i class="bi bi-collection-fill" style="font-size:14px;"></i> Tambah Semua
                    </button>
                </div>
            </div>
            
            <div style="font-size:11px; color:var(--text-muted); background:var(--info-bg); padding:10px; border-radius:var(--radius-sm); border-left:3px solid var(--info);">
                <i class="bi bi-info-circle"></i> <strong>Tips:</strong> Produk yang dipilih akan otomatis masuk ke daftar draft katalog di bawah.
            </div>
        </div>

    </div>

    <!-- Draft List -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div class="section-title" style="margin:0;"><i class="bi bi-list-check" style="color:var(--success);"></i> Draft Katalog</div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button class="btn-outline-custom" style="padding:4px 10px; font-size:11px; color:var(--danger); border-color:var(--danger);" onclick="clearCatalogDraft()">
                <i class="bi bi-trash"></i> Kosongkan
            </button>
            <span id="catalogCount" class="badge-custom badge-primary" style="font-size:12px;">0 Item</span>
        </div>
    </div>

    <div id="catalogDraftList" style="display:flex; flex-direction:column; gap:10px; min-height:200px; background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px;">
        <div class="empty-state" id="emptyCatalogState">
            <i class="bi bi-journal-x" style="font-size:2.5rem; color:var(--text-muted); opacity:0.5;"></i>
            <p style="margin-top:12px; font-size:13px;">Belum ada produk yang ditambahkan ke katalog.<br>Gunakan pencarian di atas untuk menambahkan produk.</p>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- PRINT PREVIEW CONTAINER (Hidden from Normal UI) -->
<!-- ============================================== -->
<div id="printContainer" style="display:none; background:#fff; min-height:100vh;">
    <!-- Rendered content goes here -->
</div>

<!-- STYLES -->
<style>
    /* CSS Grid untuk tampilan Desktop */
    @media (min-width: 768px) {
        .desktop-grid-2 {
            grid-template-columns: 1fr !important; /* Sengaja dibuat 1 kolom agar lebar */
        }
    }

    /* Print Styles */
    @media print {
        @page {
            size: A4;
            margin: 10mm 15mm;
        }
        body {
            background: #fff !important;
            color: #000 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        /* Hide all normal UI elements */
        body > *:not(#printContainer) {
            display: none !important;
        }
        #catalogBuilderSection, .navbar-custom, .bottom-nav {
            display: none !important;
        }
        #printContainer {
            display: block !important;
            width: 100% !important;
            background: #fff !important;
        }
        
        /* Katalog Print Elements */
        .print-header {
            text-align: center;
            margin-bottom: 20mm;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 5mm;
        }
        .print-title {
            font-size: 24pt;
            font-weight: 800;
            margin: 0 0 2mm 0;
            color: #1a1a2e;
        }
        .print-subtitle {
            font-size: 11pt;
            color: #666;
            margin: 0;
        }
        
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 2 kolom di A4 */
            gap: 5mm;
        }
        
        .catalog-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 4mm;
            page-break-inside: avoid;
            display: flex;
            gap: 4mm;
            align-items: flex-start;
        }
        
        .catalog-card-img {
            width: 35mm;
            height: 35mm;
            object-fit: contain;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        .catalog-card-icon {
            width: 35mm;
            height: 35mm;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #eee;
            font-size: 16pt;
            color: #adb5bd;
        }
        
        .catalog-card-content {
            flex: 1;
            min-width: 0;
        }
        
        .catalog-card-title {
            font-size: 11pt;
            font-weight: 700;
            margin: 0 0 2mm 0;
            line-height: 1.2;
            color: #111;
        }
        
        .catalog-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2mm;
        }
        .catalog-table th, .catalog-table td {
            font-size: 8pt;
            padding: 2px 4px;
            border: 1px solid #eee;
        }
        .catalog-table th {
            background: #f8f9fa;
            font-weight: 600;
            text-align: left;
            color: #444;
        }
        .catalog-table td.num {
            text-align: right;
            font-weight: 600;
        }
        
        .catalog-tier {
            font-size: 8pt;
            color: #e63946;
            font-weight: 700;
            background: #fff0f1;
            padding: 1px 4px;
            border-radius: 2px;
            display: inline-block;
            margin-top: 1mm;
        }
        
        .catalog-barcode {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8pt;
            color: #666;
            margin-top: 2mm;
        }
    }
</style>

<!-- SCRIPTS -->
<script>
let catalogDraft = [];
const searchInput = document.getElementById('catalogSearch');
const suggestionsDiv = document.getElementById('catalogSuggestions');
const draftListDiv = document.getElementById('catalogDraftList');
const emptyState = document.getElementById('emptyCatalogState');
const countBadge = document.getElementById('catalogCount');

const categoriesData = [
    <?php foreach ($categories ?? [] as $cat): ?>
        { value: '<?= $cat['id'] ?>', label: <?= json_encode($cat['name']) ?> },
    <?php endforeach; ?>
];
let categorySB;

document.addEventListener('DOMContentLoaded', () => {
    // Initialize SearchBox
    categorySB = new SearchBox(document.getElementById('categorySearchBox'), {
        options: categoriesData,
        value: '',
        placeholder: '-- Pilih Kategori --',
        searchable: true
    });

    // Load draft from localstorage if any
    const saved = localStorage.getItem('alfarezmart_catalog_draft');
    if (saved) {
        try {
            catalogDraft = JSON.parse(saved);
            renderDraft();
        } catch (e) {
            console.error('Failed to parse catalog draft', e);
        }
    }

    // Search input listener
    const runSearch = typeof debounce === 'function' ? debounce(performSearch, 300) : performSearch;
    searchInput.addEventListener('input', () => runSearch());
});

async function performSearch() {
    const q = searchInput.value.trim();
    if (q.length === 0) {
        suggestionsDiv.innerHTML = '';
        suggestionsDiv.style.display = 'none';
        return;
    }
    if (q.length < 2) {
        return;
    }
    suggestionsDiv.style.display = 'block';

    try {
        const res = await api(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`);
        
        if (!res || res.length === 0) {
            suggestionsDiv.style.display = 'block';
            suggestionsDiv.innerHTML = `<div style="padding:16px; text-align:center; font-size:12px; color:var(--text-muted);"><i class="bi bi-search" style="font-size:1.5rem; opacity:0.3; display:block; margin-bottom:6px;"></i>Produk tidak ditemukan.</div>`;
            return;
        }
        suggestionsDiv.style.display = 'block';

        suggestionsDiv.innerHTML = res.map(p => {
            const thumbHtml = p.photo 
                ? `<img src="${BASE_URL}${p.photo}" style="width:36px;height:36px;object-fit:contain;border-radius:4px;">`
                : `<div style="width:36px;height:36px;background:var(--primary-bg);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--primary);"><i class="bi bi-box-seam"></i></div>`;
            
            // packagings html
            let packagingsHtml = '';
            if (p.packagings && p.packagings.length > 0) {
                packagingsHtml = `<div style="font-size:10px; color:var(--text-muted); margin-top:4px; padding-top:4px; border-top:1px dashed var(--border-color);">` + 
                    p.packagings.map(pkg => `
                        <div style="display:flex;justify-content:space-between; margin-bottom:2px;">
                            <span>${pkg.unit_name} (x${pkg.base_qty})</span>
                            <span style="font-weight:600; color:var(--success);">Rp${Number(pkg.sell_price || 0).toLocaleString('id-ID')}</span>
                        </div>
                    `).join('') + `</div>`;
            } else {
                packagingsHtml = `<div style="font-size:10px; color:var(--text-muted); margin-top:4px; padding-top:4px; border-top:1px dashed var(--border-color);">Harga tidak tersedia</div>`;
            }

            // stringify for onclick
            const pStr = JSON.stringify(p).replace(/'/g, "&#39;");

            return `
            <div data-id="${p.id}" style="padding:10px; background:var(--surface-1); margin-bottom:4px; cursor:pointer; border:1px solid var(--border-color); border-radius:var(--radius-md); display:flex; gap:10px; transition:all 0.2s;" onclick='addProductToCatalog(${pStr})' onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--surface-1)'">
                ${thumbHtml}
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:600; font-size:13px; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.short_label || p.full_name}</div>
                    <div style="font-size:11px; color:var(--text-muted);">${p.brand_name || 'Tanpa Merek'}</div>
                    ${packagingsHtml}
                </div>
                <div style="color:var(--success); display:flex; align-items:center;"><i class="bi bi-plus-circle"></i></div>
            </div>`;
        }).join('');
    } catch (e) {
        suggestionsDiv.innerHTML = `<div style="padding:10px; text-align:center; color:var(--danger); font-size:12px;">Error: ${e.message}</div>`;
    }
}

async function addByCategory() {
    const catId = categorySB.getValue();
    if (!catId) {
        showToast('Pilih kategori terlebih dahulu!', 'warning');
        return;
    }

    const btn = document.getElementById('btnAddCategory');
    const oriText = btn.innerHTML;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Memuat...';
    btn.disabled = true;

    try {
        const res = await api(`${BASE_URL}api/products?per_page=9999&category=${catId}`);
        if (res && res.data && res.data.length > 0) {
            let addedCount = 0;
            res.data.forEach(p => {
                if (!catalogDraft.some(item => item.id == p.id)) {
                    catalogDraft.push(p);
                    addedCount++;
                }
            });
            saveDraft();
            renderDraft();
            showToast(`${addedCount} produk berhasil ditambahkan ke draft katalog.`, 'success');
        } else {
            showToast('Tidak ada produk dalam kategori ini.', 'info');
        }
    } catch (e) {
        showToast('Gagal memuat produk kategori: ' + e.message, 'error');
    } finally {
        btn.innerHTML = oriText;
        btn.disabled = false;
    }
}

function addProductToCatalog(product) {
    searchInput.value = '';
    suggestionsDiv.innerHTML = '';
    suggestionsDiv.style.display = 'none';
    
    if (catalogDraft.some(item => item.id == product.id)) {
        showToast('Produk sudah ada di keranjang katalog.', 'info');
        return;
    }
    
    catalogDraft.push(product);
    saveDraft();
    renderDraft();
    showToast('Produk ditambahkan ke katalog.', 'success');
}

function removeProductFromCatalog(id) {
    catalogDraft = catalogDraft.filter(item => item.id != id);
    saveDraft();
    renderDraft();
}

function clearCatalogDraft() {
    if (!confirm('Yakin ingin mengosongkan keranjang katalog?')) return;
    catalogDraft = [];
    saveDraft();
    renderDraft();
}

function saveDraft() {
    localStorage.setItem('alfarezmart_catalog_draft', JSON.stringify(catalogDraft));
}

function renderDraft() {
    countBadge.textContent = `${catalogDraft.length} Item`;
    
    if (catalogDraft.length === 0) {
        draftListDiv.innerHTML = '';
        draftListDiv.appendChild(emptyState);
        return;
    }
    
    draftListDiv.innerHTML = catalogDraft.map(p => {
        const thumbHtml = p.photo 
            ? `<img src="${BASE_URL}${p.photo}" style="width:40px;height:40px;object-fit:contain;border-radius:6px;border:1px solid var(--border-color);">`
            : `<div style="width:40px;height:40px;background:var(--primary-bg);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--primary);border:1px solid var(--border-color);"><i class="bi bi-box-seam"></i></div>`;
            
        return `
        <div style="display:flex; align-items:center; gap:12px; padding:12px; background:var(--bg-primary); border:1px solid var(--border-color); border-radius:var(--radius-md);">
            ${thumbHtml}
            <div style="flex:1; min-width:0;">
                <div style="font-weight:600; font-size:13px; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.short_label || p.full_name}</div>
                <div style="font-size:11px; color:var(--text-muted);">Barcode: ${p.code || '-'} | Kategori: ${p.category_name || '-'}</div>
            </div>
            <button class="btn-outline-custom" style="padding:6px; border-color:var(--danger); color:var(--danger); font-size:12px;" onclick="removeProductFromCatalog(${p.id})">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>`;
    }).join('');
}

// =====================================
// PRINT GENERATOR
// =====================================
function formatRupiah(number) {
    if (isNaN(number)) return 'Rp0';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(number);
}

function generatePrintPreview() {
    if (catalogDraft.length === 0) {
        showToast('Draft katalog masih kosong!', 'warning');
        return;
    }

    const container = document.getElementById('printContainer');
    
    // Header
    let html = `
        <div class="print-header">
            <h1 class="print-title">Katalog Produk</h1>
            <p class="print-subtitle">AlfarezMart &middot; Dicetak pada ${new Date().toLocaleDateString('id-ID')}</p>
        </div>
        <div class="catalog-grid">
    `;

    // Items
    catalogDraft.forEach(p => {
        const thumbHtml = p.photo 
            ? `<img src="${BASE_URL}${p.photo}" class="catalog-card-img">`
            : `<div class="catalog-card-icon"><i class="bi bi-box-seam"></i></div>`;

        // Susun tabel kemasan
        let tableRows = '';
        let tierHtml = '';

        if (p.packagings && p.packagings.length > 0) {
            p.packagings.forEach(pkg => {
                tableRows += `
                    <tr>
                        <td>${pkg.unit_name || 'Satuan'} (x${pkg.base_qty})</td>
                        <td class="num">${formatRupiah(pkg.buy_price)}</td>
                    </tr>
                `;
                
                // Tier prices
                if (pkg.qty_prices && pkg.qty_prices.length > 0) {
                    pkg.qty_prices.forEach(tier => {
                        tierHtml += `<div class="catalog-tier">Beli ${tier.min_qty} ${pkg.unit_name} = ${formatRupiah(tier.unit_price)}/satuan</div> `;
                    });
                }
            });
        } else {
            tableRows += `<tr><td colspan="2" style="text-align:center;">Harga tidak tersedia</td></tr>`;
        }

        html += `
            <div class="catalog-card">
                ${thumbHtml}
                <div class="catalog-card-content">
                    <h3 class="catalog-card-title">${p.short_label || p.full_name}</h3>
                    <table class="catalog-table">
                        <thead>
                            <tr>
                                <th>Kemasan</th>
                                <th style="text-align:right;">Harga Modal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                    </table>
                    ${tierHtml ? `<div style="margin-top:2mm;">${tierHtml}</div>` : ''}
                    <div class="catalog-barcode">Barcode: ${p.code || '-'}</div>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    container.innerHTML = html;

    // Tampilkan container, sembunyikan UI normal (handled by @media print, but we also can do it programmatically if we want an in-browser preview modal. For now, window.print() will handle it using CSS).
    // The CSS @media print already hides everything else and shows printContainer!
    
    // Sedikit delay agar gambar termuat sebelum print dialog muncul
    showToast('Menyiapkan dokumen untuk dicetak...', 'info');
    setTimeout(() => {
        window.print();
    }, 800);
}
</script>
