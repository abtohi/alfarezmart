<!-- Catalog Builder View -->

<!-- ========== CUSTOM CONFIRM MODAL ========== -->
<div id="catalogConfirmModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--surface-1); border-radius:var(--radius-xl,16px); padding:0; width:100%; max-width:360px; border:1px solid var(--border-color); box-shadow:0 20px 60px rgba(0,0,0,0.5); overflow:hidden; animation:slideUp 0.2s ease;">
        <div style="padding:24px; text-align:center;">
            <div style="width:52px;height:52px;border-radius:50%;background:rgba(230,57,70,0.12);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i class="bi bi-trash3-fill" style="font-size:1.4rem;color:var(--danger,#e63946);"></i>
            </div>
            <div style="font-weight:800;font-size:1.1rem;color:var(--text-primary);margin-bottom:8px;" id="confirmModalTitle">Kosongkan Katalog?</div>
            <div style="font-size:13px;color:var(--text-muted);line-height:1.5;" id="confirmModalMessage">Semua produk dalam draft katalog akan dihapus. Tindakan ini tidak bisa dibatalkan.</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;border-top:1px solid var(--border-color);">
            <button onclick="catalogConfirmCancel()" style="padding:16px;background:transparent;border:none;border-right:1px solid var(--border-color);font-size:14px;font-weight:600;color:var(--text-muted);font-family:var(--font-family);cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
                Batal
            </button>
            <button id="confirmModalOkBtn" onclick="catalogConfirmOk()" style="padding:16px;background:transparent;border:none;font-size:14px;font-weight:700;color:var(--danger,#e63946);font-family:var(--font-family);cursor:pointer;transition:background 0.15s;" onmouseover="this.style.background='rgba(230,57,70,0.08)'" onmouseout="this.style.background='transparent'">
                Kosongkan
            </button>
        </div>
    </div>
</div>

<!-- ========== EXPORT FORMAT MODAL ========== -->
<div id="catalogExportModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--surface-1); border-radius:var(--radius-xl,16px); padding:0; width:100%; max-width:380px; border:1px solid var(--border-color); box-shadow:0 20px 60px rgba(0,0,0,0.5); overflow:hidden; animation:slideUp 0.2s ease;">
        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between;">
            <div>
                <div style="font-weight:800;font-size:1rem;color:var(--text-primary);">Generate Katalog</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Pilih format dokumen</div>
            </div>
            <button onclick="document.getElementById('catalogExportModal').style.display='none'" style="width:32px;height:32px;border-radius:50%;background:var(--surface-2);border:none;color:var(--text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div style="padding:20px; display:flex; gap:12px;">
            <!-- PDF option -->
            <button onclick="doExport('pdf')" style="flex:1; padding:20px 10px; background:var(--surface-2); border:2px solid var(--border-color); border-radius:var(--radius-lg,12px); cursor:pointer; font-family:var(--font-family); transition:all 0.2s; text-align:center;" onmouseover="this.style.borderColor='var(--primary)';this.style.background='rgba(230,57,70,0.06)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.background='var(--surface-2)'">
                <i class="bi bi-file-earmark-pdf-fill" style="font-size:2.2rem;color:#e63946;display:block;margin-bottom:10px;"></i>
                <div style="font-weight:700;font-size:14px;color:var(--text-primary);">PDF</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Buka di tab baru & simpan sebagai PDF</div>
            </button>
            <!-- PNG option -->
            <button onclick="doExport('png')" style="flex:1; padding:20px 10px; background:var(--surface-2); border:2px solid var(--border-color); border-radius:var(--radius-lg,12px); cursor:pointer; font-family:var(--font-family); transition:all 0.2s; text-align:center;" onmouseover="this.style.borderColor='#2dd36f';this.style.background='rgba(45,211,111,0.06)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.background='var(--surface-2)'">
                <i class="bi bi-file-earmark-image-fill" style="font-size:2.2rem;color:#2dd36f;display:block;margin-bottom:10px;"></i>
                <div style="font-weight:700;font-size:14px;color:var(--text-primary);">PNG</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Screenshot otomatis & unduh sebagai gambar</div>
            </button>
        </div>
        <div style="padding:0 20px 20px; font-size:11px; color:var(--text-muted); background:var(--info-bg,rgba(13,110,253,0.05)); margin:0 20px; border-radius:8px; padding:10px 12px; margin-bottom:20px; margin-left:20px; margin-right:20px; border-left:3px solid var(--info,#0dcaf0);">
            <i class="bi bi-info-circle"></i> <strong>Tips PDF:</strong> Di dialog print browser, pilih "Save as PDF" atau "Microsoft Print to PDF" sebagai printer.
        </div>
    </div>
</div>

<!-- ========== MAIN CATALOG BUILDER ========== -->
<div class="page-section" id="catalogBuilderSection">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:1.4rem; font-weight:800; margin:0; background:var(--gradient-primary); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">Buat Katalog</h2>
        <button onclick="openExportModal()" id="btnGeneratePreview" style="display:flex; align-items:center; gap:8px; padding:11px 20px; border:none; border-radius:var(--radius-md); background:var(--gradient-primary); color:#fff; font-weight:700; font-size:13px; font-family:var(--font-family); cursor:pointer; box-shadow:0 4px 12px rgba(230,57,70,0.35); transition:all 0.2s;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 18px rgba(230,57,70,0.5)'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 12px rgba(230,57,70,0.35)'">
            <i class="bi bi-file-earmark-arrow-down-fill"></i> Generate & Cetak
        </button>
    </div>

    <div style="display:grid; grid-template-columns: 1fr; gap:20px; margin-bottom:24px;">
        
        <!-- Panel: Product Selection -->
        <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:20px; border:1px solid var(--border-color); box-shadow:var(--shadow-sm);">
            <div class="section-title"><i class="bi bi-search" style="color:var(--primary);"></i> Cari Produk</div>
            
            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-size:12px; font-weight:600; color:var(--text-muted); margin-bottom:6px; display:block;">Judul Katalog</label>
                <input type="text" id="catalogTitleInput" value="Katalog Produk" placeholder="Masukkan judul katalog..." style="width:100%; border:1px solid var(--border-color); background:var(--bg-input); padding:10px 12px; border-radius:var(--radius-md); color:var(--text-primary); font-family:var(--font-family); font-size:13px; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-size:12px; font-weight:600; color:var(--text-muted); margin-bottom:6px; display:block;">Pencarian Multi Keyword</label>
                <div style="display:flex; align-items:center; background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:0 12px; transition:all 0.2s;">
                    <i class="bi bi-search" style="color:var(--text-muted); font-size:1.1rem;"></i>
                    <input type="text" id="catalogSearch" placeholder="Ketik nama produk, merk, barcode..." 
                           style="flex:1; border:none; background:transparent; padding:12px 10px; color:var(--text-primary); font-size:var(--font-size-base); outline:none; font-family:var(--font-family);" autocomplete="off">
                </div>
                <div id="catalogSuggestions" style="margin-top:8px; max-height:360px; overflow-y:auto; border-radius:var(--radius-md); scroll-behavior:smooth; display:none;"></div>
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
            <button onclick="clearCatalogDraft()" style="display:flex; align-items:center; gap:6px; padding:6px 12px; background:transparent; border:1px solid var(--danger,#e63946); color:var(--danger,#e63946); border-radius:var(--radius-md); font-size:11px; font-weight:600; font-family:var(--font-family); cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='rgba(230,57,70,0.08)'" onmouseout="this.style.background='transparent'">
                <i class="bi bi-trash3"></i> Kosongkan
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

<!-- Scripts for PDF & PNG generation -->
<script src="<?= BASE_URL ?>public/js/html2canvas.min.js"></script>
<script src="<?= BASE_URL ?>public/js/html2pdf.bundle.min.js"></script>

<!-- STYLES -->
<style>
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0);   opacity: 1; }
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
        /* catalog print page is opened in a new tab — all body is the print content */
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

// ==============================
// Confirm modal helpers
// ==============================
let _confirmCallback = null;
function showCatalogConfirm(title, message, okLabel, okCallback) {
    document.getElementById('confirmModalTitle').textContent = title;
    document.getElementById('confirmModalMessage').textContent = message;
    document.getElementById('confirmModalOkBtn').textContent = okLabel || 'OK';
    _confirmCallback = okCallback;
    const modal = document.getElementById('catalogConfirmModal');
    modal.style.display = 'flex';
}
function catalogConfirmCancel() {
    document.getElementById('catalogConfirmModal').style.display = 'none';
    _confirmCallback = null;
}
function catalogConfirmOk() {
    document.getElementById('catalogConfirmModal').style.display = 'none';
    if (typeof _confirmCallback === 'function') _confirmCallback();
    _confirmCallback = null;
}

// ==============================
// Export modal helpers
// ==============================
function openExportModal() {
    if (catalogDraft.length === 0) {
        showToast('Draft katalog masih kosong!', 'warning');
        return;
    }
    document.getElementById('catalogExportModal').style.display = 'flex';
}

// Removed old doExport
// ==============================
// Init
// ==============================
document.addEventListener('DOMContentLoaded', () => {
    categorySB = new SearchBox(document.getElementById('categorySearchBox'), {
        options: categoriesData,
        value: '',
        placeholder: '-- Pilih Kategori --',
        searchable: true
    });

    const saved = localStorage.getItem('alfarezmart_catalog_draft');
    if (saved) {
        try {
            catalogDraft = JSON.parse(saved);
            renderDraft();
        } catch (e) { console.error('Failed to parse catalog draft', e); }
    }

    const runSearch = typeof debounce === 'function' ? debounce(performSearch, 300) : performSearch;
    searchInput.addEventListener('input', () => runSearch());
});

// ==============================
// Search
// ==============================
async function performSearch() {
    const q = searchInput.value.trim();
    if (q.length === 0) {
        suggestionsDiv.innerHTML = '';
        suggestionsDiv.style.display = 'none';
        return;
    }
    if (q.length < 2) return;
    suggestionsDiv.style.display = 'block';

    try {
        const res = await api(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`);
        
        if (!res || res.length === 0) {
            suggestionsDiv.innerHTML = `<div style="padding:16px; text-align:center; font-size:12px; color:var(--text-muted);"><i class="bi bi-search" style="font-size:1.5rem; opacity:0.3; display:block; margin-bottom:6px;"></i>Produk tidak ditemukan.</div>`;
            return;
        }
        suggestionsDiv.style.display = 'block';

        // Sort by label name
        const sorted = [...res].sort((a, b) => {
            const la = (a.short_label || a.full_name || '').toLowerCase();
            const lb = (b.short_label || b.full_name || '').toLowerCase();
            return la.localeCompare(lb, 'id');
        });

        suggestionsDiv.innerHTML = sorted.map(p => {
            const thumbHtml = p.photo 
                ? `<img src="${BASE_URL}${p.photo}" style="width:42px;height:42px;object-fit:contain;border-radius:6px;border:1px solid var(--border-color);">`
                : `<div style="width:42px;height:42px;background:var(--primary-bg);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--primary);border:1px solid var(--border-color);flex-shrink:0;"><i class="bi bi-box-seam"></i></div>`;
            
            let packagingsHtml = '';
            if (p.packagings && p.packagings.length > 0) {
                packagingsHtml = `<div style="margin-top:6px; padding-top:6px; border-top:1px dashed var(--border-color);">` + 
                    p.packagings.map(pkg => {
                        const ecer = Number(pkg.sell_price_retail || 0).toLocaleString('id-ID');
                        const grosir = Number(pkg.sell_price_wholesale || 0).toLocaleString('id-ID');
                        const modal = Number(pkg.buy_price || 0).toLocaleString('id-ID');
                        let tierRows = '';
                        if (pkg.qty_prices && pkg.qty_prices.length > 0) {
                            tierRows = pkg.qty_prices.map(t => {
                                let modeText = t.sale_mode === 'wholesale' ? 'Grosir' : (t.sale_mode === 'retail' ? 'Ecer' : 'Grosir & Ecer');
                                return `<span style="display:inline-block; font-size:9px; background:rgba(230,57,70,0.1); color:var(--danger,#e63946); padding:1px 5px; border-radius:4px; margin-top:2px;">Beli ${t.min_qty}+ = Rp${Number(t.unit_price||0).toLocaleString('id-ID')} (${modeText})</span>`;
                            }).join(' ');
                        }
                        const showGrosir = grosir !== ecer && Number(pkg.sell_price_wholesale || 0) > 0;
                        return `
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px;">
                                <span style="font-size:10px;color:var(--text-muted);">${pkg.unit_name} (x${pkg.base_qty})</span>
                                <div style="text-align:right;">
                                    <span style="font-size:10px;font-weight:600;color:var(--success,#2dd36f);">Rp${ecer}</span>
                                    ${showGrosir ? `<span style="font-size:9px;color:var(--info,#0dcaf0);margin-left:4px;">/grosir Rp${grosir}</span>` : ''}
                                </div>
                            </div>
                            ${tierRows ? `<div style="margin-bottom:4px;">${tierRows}</div>` : ''}
                        `;
                    }).join('') + `</div>`;
            }

            const pStr = JSON.stringify(p).replace(/'/g, "&#39;");

            return `
            <div data-id="${p.id}" style="padding:10px 12px; background:var(--surface-1); margin-bottom:4px; cursor:pointer; border:1px solid var(--border-color); border-radius:var(--radius-md); display:flex; gap:10px; transition:all 0.2s;" onclick='addProductToCatalog(${pStr})' onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--surface-1)'">
                <div style="flex-shrink:0;">${thumbHtml}</div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; font-size:13px; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.short_label || p.full_name}</div>
                    <div style="font-size:11px; color:var(--text-muted);">${p.brand_name || 'Tanpa Merek'}</div>
                    ${packagingsHtml}
                </div>
                <div style="color:var(--success,#2dd36f); display:flex; align-items:center; flex-shrink:0;"><i class="bi bi-plus-circle-fill" style="font-size:1.2rem;"></i></div>
            </div>`;
        }).join('');
    } catch (e) {
        suggestionsDiv.innerHTML = `<div style="padding:10px; text-align:center; color:var(--danger); font-size:12px;">Error: ${e.message}</div>`;
    }
}

// ==============================
// Category bulk add
// ==============================
async function addByCategory() {
    const catId = categorySB.getValue();
    if (!catId) {
        showToast('Pilih kategori terlebih dahulu!', 'warning');
        return;
    }

    const btn = document.getElementById('btnAddCategory');
    const oriHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memuat...';
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
        showToast('Gagal memuat produk: ' + e.message, 'error');
    } finally {
        btn.innerHTML = oriHTML;
        btn.disabled = false;
    }
}

// ==============================
// Draft management
// ==============================
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
    showCatalogConfirm(
        'Kosongkan Katalog?',
        'Semua produk dalam draft katalog akan dihapus. Tindakan ini tidak bisa dibatalkan.',
        'Ya, Kosongkan',
        () => {
            catalogDraft = [];
            saveDraft();
            renderDraft();
            showToast('Katalog berhasil dikosongkan.', 'success');
        }
    );
}

function saveDraft() {
    localStorage.setItem('alfarezmart_catalog_draft', JSON.stringify(catalogDraft));
}

function renderDraft() {
    // Selalu pastikan terurut abjad berdasarkan nama label setiap kali di-render
    catalogDraft.sort((a, b) => {
        const la = (a.short_label || a.full_name || '').toLowerCase();
        const lb = (b.short_label || b.full_name || '').toLowerCase();
        return la.localeCompare(lb, 'id');
    });

    countBadge.textContent = `${catalogDraft.length} Item`;
    
    if (catalogDraft.length === 0) {
        draftListDiv.innerHTML = '';
        draftListDiv.appendChild(emptyState);
        return;
    }
    
    draftListDiv.innerHTML = catalogDraft.map((p, idx) => {
        const thumbHtml = p.photo 
            ? `<img src="${BASE_URL}${p.photo}" style="width:44px;height:44px;object-fit:contain;border-radius:6px;border:1px solid var(--border-color);flex-shrink:0;">`
            : `<div style="width:44px;height:44px;background:var(--primary-bg);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--primary);border:1px solid var(--border-color);flex-shrink:0;"><i class="bi bi-box-seam"></i></div>`;
            
        return `
        <div style="display:flex; align-items:center; gap:12px; padding:12px; background:var(--bg-primary); border:1px solid var(--border-color); border-radius:var(--radius-md);">
            ${thumbHtml}
            <div style="flex:1; min-width:0;">
                <div style="font-weight:600; font-size:13px; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.short_label || p.full_name}</div>
                <div style="font-size:11px; color:var(--text-muted);">Barcode: ${p.code || '-'} &middot; ${p.category_name || '-'}</div>
            </div>
            <button style="width:32px;height:32px;border-radius:50%;border:1px solid var(--danger,#e63946);background:transparent;color:var(--danger,#e63946);cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;transition:all 0.15s;" onclick="removeProductFromCatalog(${p.id})" onmouseover="this.style.background='rgba(230,57,70,0.1)'" onmouseout="this.style.background='transparent'">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>`;
    }).join('');
}

// ==============================
// Catalog HTML builder
// ==============================
function formatRupiah(number) {
    if (!number || isNaN(number)) return 'Rp0';
    return 'Rp' + Number(number).toLocaleString('id-ID');
}

function buildCatalogHTML(forPng) {
    const date = new Date().toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });
    let titleText = document.getElementById('catalogTitleInput') ? document.getElementById('catalogTitleInput').value.trim() : 'Katalog Produk';
    if (!titleText) titleText = 'Katalog Produk';

    let cardsHTML = catalogDraft.map(p => {
        const imgSrc = p.photo ? (window.location.origin + '/' + p.photo.replace(/^\//, '')) : '';
        const thumbHtml = imgSrc
            ? `<img src="${BASE_URL}${p.photo}" style="width:80px;height:80px;object-fit:contain;border-radius:6px;border:1px solid #e5e7eb;flex-shrink:0;" crossorigin="anonymous">`
            : `<div style="width:80px;height:80px;background:#f3f4f6;border-radius:6px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><span style="font-size:28px;">📦</span></div>`;

        let priceRowsHTML = '';
        if (p.packagings && p.packagings.length > 0) {
            priceRowsHTML = p.packagings.map(pkg => {
                const ecer = formatRupiah(pkg.sell_price_retail);
                const grosir = formatRupiah(pkg.sell_price_wholesale);
                const showGrosir = Number(pkg.sell_price_wholesale || 0) > 0 && pkg.sell_price_wholesale !== pkg.sell_price_retail;
                let tierHTML = '';
                if (pkg.qty_prices && pkg.qty_prices.length > 0) {
                    tierHTML = pkg.qty_prices.map(t => {
                        let modeText = t.sale_mode === 'wholesale' ? 'Grosir' : (t.sale_mode === 'retail' ? 'Ecer' : 'Grosir & Ecer');
                        return `<div style="font-size:8.5pt;color:#c0392b;background:#fff5f5;padding:2px 6px;border-radius:3px;margin-top:2px;display:inline-block;">Beli ${t.min_qty}+ = ${formatRupiah(t.unit_price)}/${pkg.unit_name} (${modeText})</div>`;
                    }).join(' ');
                }
                return `
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:4px 6px;font-size:9pt;color:#374151;font-weight:600;">${pkg.unit_name}<span style="font-weight:400;color:#6b7280;font-size:8.5pt;"> (isi ${pkg.base_qty})</span></td>
                    <td style="padding:4px 6px;font-size:9pt;color:#059669;font-weight:700;text-align:right;">${ecer}</td>
                    <td style="padding:4px 6px;font-size:9pt;color:#0284c7;font-weight:700;text-align:right;">${showGrosir ? grosir : '-'}</td>
                </tr>
                ${tierHTML ? `<tr><td colspan="3" style="padding:2px 6px 6px;">${tierHTML}</td></tr>` : ''}`;
            }).join('');
        } else {
            priceRowsHTML = `<tr><td colspan="3" style="padding:6px;text-align:center;color:#9ca3af;font-size:9pt;">Harga belum tersedia</td></tr>`;
        }

        return `
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:12px;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,0.06);height:100%;box-sizing:border-box;">
                <div style="display:flex;gap:10px;align-items:flex-start;margin-bottom:8px;">
                    ${thumbHtml}
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:11pt;font-weight:800;color:#111827;line-height:1.3;margin-bottom:2px;">${p.short_label || p.full_name}</div>
                        <div style="font-size:8.5pt;color:#6b7280;">${p.brand_name || ''} ${p.category_name ? '· '+p.category_name : ''}</div>
                        ${p.code ? `<div style="font-size:8pt;color:#9ca3af;margin-top:2px;font-family:monospace;">Barcode: ${p.code}</div>` : ''}
                    </div>
                </div>
                <table style="width:100%;border-collapse:collapse;border:1px solid #f0f0f0;border-radius:6px;overflow:hidden;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="padding:4px 6px;text-align:left;font-size:8.5pt;font-weight:700;color:#374151;">Kemasan</th>
                            <th style="padding:4px 6px;text-align:right;font-size:8.5pt;font-weight:700;color:#059669;">Harga Ecer</th>
                            <th style="padding:4px 6px;text-align:right;font-size:8.5pt;font-weight:700;color:#0284c7;">Harga Grosir</th>
                        </tr>
                    </thead>
                    <tbody>${priceRowsHTML}</tbody>
                </table>
            </div>`;
    });

    // Susun array kartu menjadi baris-baris tabel (2 kolom per baris)
    let tableRows = '';
    for (let i = 0; i < cardsHTML.length; i += 2) {
        const leftCard = cardsHTML[i];
        const rightCard = cardsHTML[i + 1] ? cardsHTML[i + 1] : '';
        tableRows += `
        <tr class="catalog-item" style="page-break-inside:avoid; break-inside:avoid;">
            <td style="width:50%; padding-right:6px; padding-bottom:12px; vertical-align:top;">${leftCard}</td>
            <td style="width:50%; padding-left:6px; padding-bottom:12px; vertical-align:top;">${rightCard}</td>
        </tr>`;
    }

    return `<div id="catalogContent" style="background:#fff; max-width:794px; margin:0 auto; padding:20mm 15mm; color:#111827;">
        <div style="text-align:center; margin-bottom:20px; padding-bottom:16px; border-bottom:2px solid #f0f0f0;">
            <div style="font-size:22pt; font-weight:900; color:#1a1a2e; margin-bottom:4px;">${titleText}</div>
            <div style="font-size:10pt; color:#6b7280;">AlfarezMart &nbsp;&middot;&nbsp; Dicetak pada ${date}</div>
        </div>
        <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
            <tbody>
                ${tableRows}
            </tbody>
        </table>
    </div>`;
}

async function doExport(format) {
    document.getElementById('catalogExportModal').style.display = 'none';
    
    if (catalogDraft.length === 0) {
        showToast('Draft katalog kosong!', 'warning');
        return;
    }
    
    showToast('Sedang memproses dokumen...', 'info');

    // Create a temporary hidden container to render HTML for capture
    const tempContainer = document.createElement('div');
    tempContainer.style.position = 'absolute';
    tempContainer.style.left = '-9999px';
    tempContainer.style.top = '0';
    tempContainer.style.width = '794px'; // Force width for rendering
    tempContainer.innerHTML = buildCatalogHTML(true);
    document.body.appendChild(tempContainer);
    
    const element = tempContainer.querySelector('#catalogContent');
    
    // Wait for DOM to settle and images to load
    await new Promise(resolve => setTimeout(resolve, 500));
    
    const titleInput = document.getElementById('catalogTitleInput');
    let titleText = titleInput ? titleInput.value.trim() : 'Katalog Produk';
    if (!titleText) titleText = 'Katalog Produk';

    try {
        if (format === 'pdf') {
            const opt = {
                margin:       [0.3, 0.3, 0.3, 0.3],
                filename:     `${titleText.replace(/[^a-z0-9]/gi, '_')}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['css', 'legacy'], avoid: '.catalog-item' }
            };
            await html2pdf().set(opt).from(element).save();
            showToast('PDF berhasil diunduh.', 'success');
        } else {
            const canvas = await html2canvas(element, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
            const link = document.createElement('a');
            link.download = 'Katalog_Produk_AlfarezMart.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            showToast('PNG berhasil diunduh.', 'success');
        }
    } catch (e) {
        console.error(e);
        showToast('Gagal men-generate dokumen.', 'error');
    } finally {
        document.body.removeChild(tempContainer);
    }
}
</script>
