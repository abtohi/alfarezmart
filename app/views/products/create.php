<!-- Product Create View -->
<?php
/** 
 * @var string $csrfToken 
 * @var array $brands 
 * @var array $categories 
 * @var array $units 
 */
?>
<div class="page-section">
    <a href="<?= BASE_URL ?>products" style="color:var(--text-muted);text-decoration:none;font-size:var(--font-size-sm);display:inline-flex;align-items:center;gap:4px;margin-bottom:16px;">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <h2 style="font-size:var(--font-size-lg);font-weight:700;margin-bottom:20px;">Tambah Produk Baru</h2>

    <!-- Mode Varian dari Referensi -->
    <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--border-color);">
        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
            <input type="checkbox" id="useReferenceMode" style="margin-top:3px;width:18px;height:18px;accent-color:var(--primary);">
            <span>
                <span style="font-weight:600;font-size:var(--font-size-sm);display:block;">Tambah varian dari produk referensi</span>
                <span style="font-size:var(--font-size-xs);color:var(--text-muted);">Spesifikasi & kemasan sama, hanya varian & barcode yang berbeda</span>
            </span>
        </label>
        <div id="referencePanel" style="display:none;margin-top:14px;padding-top:14px;border-top:1px solid var(--border-color);">
            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:6px;">Cari produk referensi</label>
            <div style="position:relative;">
                <input type="text" id="referenceSearch" class="form-control-dark" placeholder="Ketik nama produk..." autocomplete="off" style="width:100%;">
                <div id="referenceResults" style="display:none;position:absolute;left:0;right:0;top:100%;z-index:50;background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);max-height:220px;overflow-y:auto;margin-top:4px;box-shadow:0 8px 24px rgba(0,0,0,0.3);"></div>
            </div>
            <div id="referenceSelected" style="display:none;margin-top:10px;padding:10px 12px;background:var(--info-bg);border-radius:var(--radius-sm);font-size:var(--font-size-xs);color:var(--info);">
                <i class="bi bi-link-45deg"></i> Referensi: <strong id="referenceSelectedName"></strong>
                <button type="button" onclick="clearReference()" style="float:right;background:none;border:none;color:var(--danger);font-size:11px;cursor:pointer;">Hapus</button>
            </div>
        </div>
    </div>

    <form id="formProduct" onsubmit="submitProduct(event)">
        <input type="hidden" name="reference_product_id" id="referenceProductId" value="">
        <input type="hidden" name="csrf_token" id="csrfToken" value="<?= $csrfToken ?>">

        <!-- Identitas Produk -->
        <!-- Mode Varian & Identitas -->
        <div id="identitySection" style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--border-color);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div class="section-title" style="margin-bottom:0;">Identitas Produk</div>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:var(--font-size-xs);">
                    <input type="checkbox" id="isMultivariant" checked style="width:16px;height:16px;accent-color:var(--primary);">
                    <span>Produk Multivarian (Brand + Jenis + Varian)</span>
                </label>
            </div>
            
            <div id="singleVariantPanel" style="display:none; margin-bottom:12px;">
                <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Nama Produk *</label>
                <input type="text" name="single_name" id="singleNameInput" placeholder="Cth: Sapu Lidi Pendek" class="form-control-dark" style="width:100%;">
            </div>

            <div id="multiVariantPanel">
                <!-- Brand (SearchBox) -->
                <div style="margin-bottom:12px;">
                    <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Brand / Merek *</label>
                    <div id="brandSearchBox"></div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Jenis Produk</label>
                    <input type="text" name="product_type" id="productTypeInput" placeholder="Cth: UHT, Goreng, Hair Color, Mild..." class="form-control-dark" style="width:100%;">
                </div>
                
                <div style="margin-bottom:12px;" id="variantFieldWrap">
                    <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Varian <span id="variantRequiredMark" style="display:none;color:var(--primary);">*</span></label>
                    <input type="text" name="variant" id="variantInput" placeholder="Cth: Choco Malt, Original, Violet Red..." class="form-control-dark" style="width:100%;">
                    <small id="variantHint" style="display:none;font-size:10px;color:var(--info);margin-top:4px;">Isi varian baru (wajib saat mode referensi)</small>
                </div>
            </div>
            
            <!-- Kategori (SearchBox) -->
            <div style="margin-bottom:12px;">
                <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Kategori *</label>
                <div id="categorySearchBox"></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                    <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Berat/Volume</label>
                    <input type="number" name="weight_value" step="0.01" placeholder="250" class="form-control-dark" style="width:100%;">
                </div>
                <div>
                    <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Satuan Ukuran</label>
                    <div id="weightUnitSearchBox"></div>
                </div>
            </div>
        </div>

        <!-- Informasi Supplier (Opsional) -->
        <div style="background:var(--surface-1);border-radius:var(--radius-lg);margin-bottom:12px;border:1px solid var(--border-color);overflow:hidden;">
            <button type="button" id="btnToggleSupplierInfo" onclick="toggleSupplierInfo()" style="width:100%;background:none;border:none;padding:14px 16px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;color:var(--text-secondary);font-size:var(--font-size-sm);">
                <span><i class="bi bi-building" style="color:var(--info);margin-right:8px;"></i> Informasi Supplier (Opsional)</span>
                <i class="bi bi-chevron-down" id="iconSupplierChevron" style="transition:transform 0.3s;"></i>
            </button>
            <div id="supplierInfoPanel" style="display:none;padding:0 16px 16px;">
                <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:12px;padding-top:4px;border-top:1px solid var(--border-color);">Data ini membantu AI Scan Invoice mengenali produk ini lebih akurat.</p>
                <div style="margin-bottom:12px;">
                    <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Kode Barang Supplier</label>
                    <input type="text" name="supplier_product_code" id="supplierProductCode" placeholder="Cth: CMY-125, INM-001 (kode di faktur supplier)" class="form-control-dark" style="width:100%;">
                </div>
                <div>
                    <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Nama Barang di Invoice Supplier (Multi-nama)</label>
                    <div id="invoiceNameList" style="display:flex;flex-direction:column;gap:6px;margin-bottom:6px;"></div>
                    <button type="button" onclick="addInvoiceName()" style="width:100%;border:1px dashed var(--border-color);background:transparent;color:var(--info);padding:6px;border-radius:var(--radius-sm);font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;">
                        <i class="bi bi-plus-circle"></i> Tambah Nama Invoice
                    </button>
                    <input type="hidden" name="supplier_invoice_name" id="supplierInvoiceName" value="">
                    <div style="font-size:10px;color:var(--text-muted);margin-top:4px;"><i class="bi bi-info-circle"></i> Tambahkan semua variasi nama di invoice supplier agar AI Scan lebih akurat mengenali produk ini.</div>
                </div>
            </div>
        </div>

        <!-- Dynamic Packaging Levels -->
        <div class="section-title" style="margin-top:20px;margin-bottom:8px;">
            <i class="bi bi-layers" style="color:var(--info);"></i> Level Kemasan & Harga
        </div>
        <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:8px;">
            Contoh: 1 Karton → 2 Pack → 4 Box → 10 Papan → Pcs. Mulai dari satuan terkecil.
        </p>
        <p style="font-size:var(--font-size-xs);color:var(--info);margin-bottom:8px;">
            <i class="bi bi-calculator"></i> Harga modal/jual otomatis dikalikan isi kemasan (ubah di level manapun, level lain menyesuaikan).
        </p>
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
            <button type="button" class="btn-outline-custom" style="flex:1;min-width:140px;font-size:var(--font-size-xs);padding:8px;" onclick="BarcodeUtil.generateAllEmpty('input.barcode-field')">
                <i class="bi bi-magic"></i> Generate Semua Barcode
            </button>
            <button type="button" class="btn-outline-custom" style="flex:1;min-width:140px;font-size:var(--font-size-xs);padding:8px;" onclick="printAllBarcodesCreate()">
                <i class="bi bi-printer"></i> Cetak Semua Barcode
            </button>
        </div>
        <div id="packagingContainer">
            <!-- Levels generated by JS -->
        </div>

        <button type="button" id="btnAddPackagingLevel" onclick="addPackagingLevel()" class="btn-outline-custom" style="width:100%;margin-bottom:16px;border-style:dashed;">
            <i class="bi bi-plus-circle"></i> Tambah Level Kemasan (Grosir/Karton)
        </button>

        <!-- Nama Preview -->
        <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:16px;border:1px solid var(--border-color);">
            <div class="section-title" style="margin-bottom:8px;">Preview Nama Produk</div>
            <div id="namePreview" style="font-size:var(--font-size-sm);color:var(--text-secondary);font-weight:600;margin-bottom:12px;">-</div>
            
            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:6px;">Status Produk</label>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:12px;">
                <input type="hidden" name="is_available" value="0">
                <input type="checkbox" name="is_available" value="1" checked id="toggleIsAvailableCreate" style="display:none;">
                <span id="toggleIsAvailableCreateSwitch" onclick="toggleAvailability('toggleIsAvailableCreate','toggleIsAvailableCreateSwitch','toggleIsAvailableCreateLabel')" style="display:inline-flex;align-items:center;width:44px;height:24px;border-radius:12px;background:var(--primary);border:none;padding:2px;cursor:pointer;transition:background 0.25s;flex-shrink:0;">
                    <span style="display:block;width:20px;height:20px;border-radius:50%;background:#fff;transition:transform 0.25s;transform:translateX(20px);"></span>
                </span>
                <span id="toggleIsAvailableCreateLabel" style="font-size:var(--font-size-sm);font-weight:600;color:var(--success);">Tersedia</span>
            </label>

            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Label Struk & Rak</label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:11px;margin-bottom:8px;">
                <input type="checkbox" id="isCustomLabel" name="is_custom_label" value="1" style="width:14px;height:14px;accent-color:var(--primary);">
                <span>Custom nama label (jangan otomatis ubah)</span>
            </label>
            <input type="text" id="manualLabel" class="form-control-dark" style="width:100%;font-size:var(--font-size-sm);font-weight:600;color:var(--info);" maxlength="35" placeholder="Maks 35 Karakter" disabled>
        </div>

        <button type="submit" id="btnSubmit" class="btn-primary-custom" style="width:100%;padding:14px;box-shadow:0 8px 24px rgba(230,57,70,0.4);cursor:pointer;">
            <i class="bi bi-check-circle"></i> Simpan Produk
        </button>
    </form>
</div>

<!-- Template for units -->
<script>
<?php
$brandsArr = array_map(function($b) { return ['value' => (string)$b['id'], 'label' => $b['name']]; }, $brands);
$catsArr = array_map(function($c) { return ['value' => (string)$c['id'], 'label' => $c['name']]; }, $categories);
$unitsArr = array_map(function($u) { 
    $abbr = !empty($u['abbreviation']) ? $u['abbreviation'] : $u['name'];
    return [
        'id' => (string)$u['id'],
        'name' => $u['name'],
        'abbreviation' => !empty($u['abbreviation']) ? $u['abbreviation'] : ''
    ]; 
}, $units);
?>
let brandsData = <?= json_encode($brandsArr) ?>;
let categoriesData = <?= json_encode($catsArr) ?>;
let unitsData = <?= json_encode(array_map(function($u) { return ['value' => $u['id'], 'label' => $u['name']]; }, $unitsArr)) ?>;
let weightUnitOptions = <?= json_encode(array_map(function($u) {
    return [
        'value' => $u['abbreviation'], 
        'label' => $u['abbreviation'] && $u['abbreviation'] !== $u['name'] ? $u['name'] . ' (' . $u['abbreviation'] . ')' : $u['name']
    ];
}, $unitsArr)) ?>;

const csrfTokenValue = document.getElementById('csrfToken').value;
let levelCount = 0;
let referenceMode = false;
let isMultivariant = true;
let isLabelEdited = false;
let referenceProductData = null;
let referenceSearchTimer = null;

// ===== SearchBox Instances =====
let brandSB, categorySB, weightUnitSB;

document.addEventListener('DOMContentLoaded', async () => {

    brandSB = new SearchBox(document.getElementById('brandSearchBox'), {
        options: brandsData,
        placeholder: 'Cari atau pilih brand...',
        icon: 'bi-tag',
        name: 'brand_id',
        required: true,
        addLabel: 'Tambah Brand Baru',
        onAdd: () => openMasterModal('brand'),
        onChange: () => updateNamePreview(),
        linkUrl: BASE_URL + 'settings/master-data',
        linkLabel: 'Buka Master Data'
    });

    categorySB = new SearchBox(document.getElementById('categorySearchBox'), {
        options: categoriesData,
        placeholder: 'Cari atau pilih kategori...',
        icon: 'bi-grid',
        name: 'category_id',
        required: true,
        addLabel: 'Tambah Kategori Baru',
        onAdd: () => openMasterModal('category'),
        onChange: () => updateNamePreview(),
        linkUrl: BASE_URL + 'settings/master-data',
        linkLabel: 'Buka Master Data'
    });

    // Weight Unit SearchBox
    weightUnitSB = new SearchBox(document.getElementById('weightUnitSearchBox'), {
        options: weightUnitOptions,
        placeholder: 'Pilih...',
        name: 'weight_unit',
        addLabel: 'Tambah Satuan Baru',
        onAdd: () => openMasterModal('unit'),
        onChange: () => updateNamePreview(),
        linkUrl: BASE_URL + 'settings/master-data',
        linkLabel: 'Buka Master Data'
    });

    // Init first packaging level
    addPackagingLevel();
    if (typeof PackagingPriceSync !== 'undefined') PackagingPriceSync.init();
    
    // Init invoice name list (1 empty input)
    initInvoiceNameList('');

    // Reference mode toggle
    document.getElementById('useReferenceMode')?.addEventListener('change', (e) => {
        referenceMode = e.target.checked;
        document.getElementById('referencePanel').style.display = referenceMode ? 'block' : 'none';
        document.getElementById('variantHint').style.display = referenceMode ? 'block' : 'none';
        document.getElementById('variantRequiredMark').style.display = referenceMode ? 'inline' : 'none';
        if (!referenceMode) clearReference();
        applyReferenceLock();
    });

    // Multivariant toggle
    document.getElementById('isMultivariant')?.addEventListener('change', (e) => {
        isMultivariant = e.target.checked;
        if (isMultivariant) {
            // Close all SearchBox dropdowns before changing panel visibility
            if (brandSB) brandSB.close();
            if (categorySB) categorySB.close();
            if (weightUnitSB) weightUnitSB.close();
            
            // Show multivariant panel
            document.getElementById('multiVariantPanel').style.display = 'block';
            document.getElementById('singleVariantPanel').style.display = 'none';
            document.getElementById('singleNameInput').required = false;
            if (brandSB) brandSB.setRequired(true);
        } else {
            // Close all SearchBox dropdowns before changing panel visibility
            if (brandSB) brandSB.close();
            if (categorySB) categorySB.close();
            if (weightUnitSB) weightUnitSB.close();
            
            // Show single variant panel
            document.getElementById('multiVariantPanel').style.display = 'none';
            document.getElementById('singleVariantPanel').style.display = 'block';
            document.getElementById('singleNameInput').required = true;
            if (brandSB) brandSB.setRequired(false);
        }
        updateNamePreview();
    });

    document.getElementById('isCustomLabel')?.addEventListener('change', (e) => {
        isLabelEdited = e.target.checked;
        const manualLabel = document.getElementById('manualLabel');
        manualLabel.disabled = !isLabelEdited;
        if (!isLabelEdited) {
            updateNamePreview();
        } else {
            manualLabel.focus();
        }
    });

    document.getElementById('referenceSearch')?.addEventListener('input', (e) => {
        clearTimeout(referenceSearchTimer);
        const q = e.target.value.trim();
        if (q.length < 2) {
            document.getElementById('referenceResults').style.display = 'none';
            return;
        }
        referenceSearchTimer = setTimeout(() => searchReferenceProducts(q), 300);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#referenceSearch') && !e.target.closest('#referenceResults')) {
            document.getElementById('referenceResults').style.display = 'none';
        }
    });
});

// ===== Add Master Data via Modal =====
async function openMasterModal(type) {
    const configs = {
        brand: {
            title: 'Tambah Brand Baru',
            subtitle: 'Masukkan nama brand/merek produk',
            icon: 'bi-tag',
            iconColor: 'var(--primary-bg)',
            iconAccent: 'var(--primary)',
            fields: [
                { name: 'name', label: 'Nama Brand', placeholder: 'Cth: Indomie, Aqua, Unilever...', required: true }
            ],
            endpoint: `${BASE_URL}api/brands`
        },
        category: {
            title: 'Tambah Kategori Baru',
            subtitle: 'Masukkan nama kategori produk',
            icon: 'bi-grid',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            fields: [
                { name: 'name', label: 'Nama Kategori', placeholder: 'Cth: Makanan Ringan, Minuman, Rokok...', required: true }
            ],
            endpoint: `${BASE_URL}api/categories`
        },
        unit: {
            title: 'Tambah Satuan Baru',
            subtitle: 'Masukkan nama satuan kemasan',
            icon: 'bi-rulers',
            iconColor: 'var(--success-bg)',
            iconAccent: 'var(--success)',
            fields: [
                { name: 'name', label: 'Nama Satuan', placeholder: 'Cth: Karton, Renteng, Lusin...', required: true },
                { name: 'abbreviation', label: 'Singkatan (opsional)', placeholder: 'Cth: krt, rtg, lsn...', required: false }
            ],
            endpoint: `${BASE_URL}api/units`
        }
    };

    const cfg = configs[type];
    if (!cfg) return;

    // Build form HTML
    const fieldsHTML = cfg.fields.map(f => `
        <div class="modal-form-group">
            <label>${f.label} ${f.required ? '*' : ''}</label>
            <input type="text" class="form-control-dark" id="modalField_${f.name}" 
                   placeholder="${f.placeholder}" ${f.required ? 'required' : ''} autocomplete="off">
        </div>
    `).join('');

    const result = await AppModal.show({
        title: cfg.title,
        subtitle: cfg.subtitle,
        icon: cfg.icon,
        iconColor: cfg.iconColor,
        iconAccent: cfg.iconAccent,
        bodyHTML: fieldsHTML,
        submitText: 'Simpan',
        onSubmit: async () => {
            // Gather field values
            const payload = { csrf_token: csrfTokenValue };
            for (const f of cfg.fields) {
                const val = document.getElementById(`modalField_${f.name}`).value.trim();
                if (f.required && !val) {
                    showToast(`${f.label} wajib diisi`, 'warning');
                    return false; // Prevent close
                }
                payload[f.name] = val;
            }

            // API call
            const res = await fetch(cfg.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfTokenValue },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.error) {
                showToast(data.error, 'error');
                return false;
            }

            if (data.success) {
                // Add to searchbox
                if (type === 'brand') {
                    brandSB.addOption(data.id, data.name, true);
                } else if (type === 'category') {
                    categorySB.addOption(data.id, data.name, true);
                } else if (type === 'unit') {
                    // Add to all unit searchboxes
                    unitsData.push({ value: String(data.id), label: data.name });
                    document.querySelectorAll('.unit-searchbox-instance').forEach(el => {
                        if (el._searchbox) el._searchbox.addOption(data.id, data.name, false);
                    });
                    
                    // Add to weight unit searchbox
                    const abbr = data.abbreviation || data.name;
                    const wLabel = data.abbreviation ? `${data.name} (${data.abbreviation})` : data.name;
                    weightUnitOptions.push({ value: abbr, label: wLabel });
                    if (weightUnitSB) weightUnitSB.addOption(abbr, wLabel, false);
                }
                showToast(`${cfg.title.replace('Tambah ', '')} "${data.name}" berhasil ditambahkan!`, 'success');
                return data;
            }
            return false;
        }
    });
}

// ===== Reference Product (Multivarian) =====
async function searchReferenceProducts(q) {
    const box = document.getElementById('referenceResults');
    try {
        let items = [];
        if (typeof OfflineDB !== 'undefined') {
            items = await OfflineDB.searchProducts(q);
        }
        if ((!items || items.length === 0) && navigator.onLine) {
            const res = await fetch(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`);
            items = await res.json();
        }
        if (!items.length) {
            box.innerHTML = '<div style="padding:12px;font-size:12px;color:var(--text-muted);">Tidak ditemukan</div>';
            box.style.display = 'block';
            return;
        }
        box.innerHTML = items.map(p => `
            <button type="button" onclick="selectReferenceProduct(${p.id})" style="display:block;width:100%;text-align:left;padding:10px 12px;border:none;background:transparent;color:var(--text-primary);font-size:12px;cursor:pointer;border-bottom:1px solid var(--border-color);">
                <strong>${escapeHtml(p.short_label || p.full_name)}</strong>
                ${p.variant ? `<span style="color:var(--text-muted);"> · ${escapeHtml(p.variant)}</span>` : ''}
            </button>
        `).join('');
        box.style.display = 'block';
    } catch (e) {
        showToast('Gagal mencari produk', 'error');
    }
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

async function selectReferenceProduct(id) {
    document.getElementById('referenceResults').style.display = 'none';
    document.getElementById('referenceSearch').value = '';
    try {
        const product = await api(`${BASE_URL}api/products/${id}`);
        loadReferenceProduct(product);
    } catch (e) {
        showToast('Gagal memuat produk referensi', 'error');
    }
}

function loadReferenceProduct(product) {
    referenceProductData = product;
    document.getElementById('referenceProductId').value = product.id;
    document.getElementById('referenceSelectedName').textContent = product.short_label || product.full_name;
    document.getElementById('referenceSelected').style.display = 'block';

    if (product.brand_id) brandSB.select(String(product.brand_id), product.brand_name || '');
    if (product.category_id) categorySB.select(String(product.category_id), product.category_name || '');
    document.querySelector('[name="product_type"]').value = product.product_type || '';
    document.querySelector('[name="weight_value"]').value = product.weight_value || '';
    if (product.weight_unit) weightUnitSB.select(product.weight_unit, product.weight_unit);

    document.querySelector('[name="variant"]').value = '';
    document.querySelector('[name="variant"]').focus();

    rebuildPackagingsFromReference(product.packagings || []);
    applyReferenceLock();
    updateNamePreview();

    if (product.is_custom_label == 1) {
        isLabelEdited = true;
        const chk = document.getElementById('isCustomLabel');
        if (chk) chk.checked = true;
        const manualLabel = document.getElementById('manualLabel');
        manualLabel.disabled = false;
        manualLabel.value = product.short_label || '';
    } else {
        isLabelEdited = false;
        const chk = document.getElementById('isCustomLabel');
        if (chk) chk.checked = false;
        document.getElementById('manualLabel').disabled = true;
    }

    showToast('Data referensi dimuat. Isi varian & barcode baru.', 'success');
}

function clearReference() {
    referenceProductData = null;
    document.getElementById('referenceProductId').value = '';
    document.getElementById('referenceSelected').style.display = 'none';
    document.getElementById('referenceSelectedName').textContent = '';
    applyReferenceLock();
}

function rebuildPackagingsFromReference(packagings) {
    const container = document.getElementById('packagingContainer');
    container.innerHTML = '';
    levelCount = 0;
    const sorted = [...packagings].sort((a, b) => a.level - b.level);
    sorted.forEach(pk => addPackagingLevel({
        unit_id: pk.unit_id,
        unit_name: pk.unit_name,
        contained_qty: pk.contained_qty,
        buy_price: pk.buy_price,
        sell_price_retail: pk.sell_price_retail,
        sell_price_wholesale: pk.sell_price_wholesale,
        barcode: '',
        qty_prices: pk.qty_prices ? [...pk.qty_prices] : [] // Copy qty_prices from reference, not empty
    }));
    if (sorted.length === 0) addPackagingLevel();
    updateBaseQtyInfo();
}

function applyReferenceLock() {
    const locked = referenceMode && referenceProductData;
    const identity = document.getElementById('identitySection');
    identity?.querySelectorAll('input, .searchbox-wrapper').forEach(el => {
        if (el.name === 'variant') return;
        el.style.pointerEvents = locked ? 'none' : '';
        el.style.opacity = locked ? '0.65' : '';
    });
    document.querySelector('[name="variant"]').style.pointerEvents = '';
    document.querySelector('[name="variant"]').style.opacity = '1';

    document.querySelectorAll('.packaging-level').forEach(lv => {
        lv.querySelectorAll('input, .searchbox-wrapper').forEach(el => {
            const isBarcode = el.classList.contains('barcode-field');
            el.style.pointerEvents = locked && !isBarcode ? 'none' : '';
            el.style.opacity = locked && !isBarcode ? '0.65' : '';
        });
        const removeBtn = lv.querySelector('button[onclick*="removeLevel"]');
        if (removeBtn) removeBtn.style.display = locked ? 'none' : '';
    });

    const addBtn = document.getElementById('btnAddPackagingLevel');
    if (addBtn) addBtn.style.display = locked ? 'none' : '';
}

function getCreateProductLabel() {
    return document.getElementById('namePreview')?.textContent?.trim() || 'Produk';
}

function printAllBarcodesCreate() {
    BarcodeUtil.printAllFilled('input.barcode-field', (input, row) => {
        const unit = row?.querySelector('.unit-searchbox-instance')?._searchbox?.getLabel() || '';
        return { title: getCreateProductLabel(), subtitle: unit ? `1 ${unit}` : '' };
    });
}

// ===== Tier Pricing JS =====
const QTY_MODE_OPTS = [
    { v: 'both', l: 'Ecer & Grosir' },
    { v: 'retail', l: 'Ecer saja' },
    { v: 'wholesale', l: 'Grosir saja' },
];

function addQtyTierRow(listEl, data = {}) {
    const row = document.createElement('div');
    row.className = 'qty-tier-row';
    row.style.cssText = 'display:grid;grid-template-columns:minmax(56px,0.8fr) minmax(80px,1fr) minmax(90px,1fr) auto;gap:6px;align-items:start;margin-bottom:6px;';
    const mode = data.sale_mode || 'both';
    const selectedModeObj = QTY_MODE_OPTS.find(o => o.v === mode) || QTY_MODE_OPTS[0];
    const modeDropdownItems = QTY_MODE_OPTS.map(o => `
        <li><a class="dropdown-item ${mode === o.v ? 'active' : ''}" href="#" onclick="event.preventDefault(); const p=this.closest('.dropdown'); p.querySelector('.tier-sale-mode').value='${o.v}'; p.querySelector('button span').textContent='${o.l}'; p.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');">${o.l}</a></li>
    `).join('');
    
    const minQty = data.min_qty || '';
    const unitPrice = data.unit_price || '';
    const calculatedTotal = (minQty && unitPrice) ? (minQty * unitPrice) : '';
    const totalPrice = calculatedTotal ? (Number.isInteger(calculatedTotal) ? calculatedTotal : parseFloat(calculatedTotal.toFixed(2))) : '';

    row.innerHTML = `
        <div><label style="font-size:9px;color:var(--text-muted);">Untuk Qty</label>
            <input type="number" class="form-control-dark tier-min-qty" min="1" step="1" value="${minQty}" placeholder="1" style="width:100%;padding:6px;font-size:12px;" oninput="this.closest('.qty-tier-row').querySelector('.tier-total-price').dispatchEvent(new Event('input'))"></div>
        <div><label style="font-size:9px;color:var(--text-muted);">Total Harga</label>
            <input type="number" class="form-control-dark tier-total-price" min="0" step="any" value="${totalPrice}" placeholder="10000" style="width:100%;padding:6px;font-size:12px;" oninput="const r=this.closest('.qty-tier-row'); const q=r.querySelector('.tier-min-qty').value; if(q>0) r.querySelector('.tier-unit-price').value=parseFloat((this.value/q).toFixed(2)); r.querySelector('.tier-unit-price').dispatchEvent(new Event('change'));">
            <input type="hidden" class="tier-unit-price" value="${unitPrice}">
            <div class="tier-margin-info" style="font-size:9px;color:var(--text-muted);margin-top:2px;min-height:14px;"></div>
        </div>
        <div><label style="font-size:9px;color:var(--text-muted);">Mode</label>
            <div class="dropdown" style="width:100%;">
                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:6px; font-size:11px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                    <span>${selectedModeObj.l}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:11px; min-width:100%;">
                    ${modeDropdownItems}
                </ul>
                <input type="hidden" class="tier-sale-mode" value="${mode}">
            </div>
        </div>
        <div><label style="font-size:9px;visibility:hidden;">X</label>
            <button type="button" title="Hapus tier" style="border:none;background:var(--danger-bg);color:var(--danger);padding:8px;border-radius:6px;cursor:pointer;margin-bottom:2px;display:block;width:100%;" onclick="this.closest('.qty-tier-row').remove()"><i class="bi bi-trash"></i></button></div>
        <div style="grid-column:1/-1;"><label style="font-size:9px;color:var(--text-muted);">Label (opsional)</label>
            <input type="text" class="form-control-dark tier-label" value="${data && data.label ? data.label : ''}" placeholder="Cth: 3 renceng = Rp 10.000" style="width:100%;padding:6px;font-size:11px;"></div>
    `;
    listEl.appendChild(row);

    const unitPriceEl = row.querySelector('.tier-unit-price');
    if (unitPriceEl) {
        unitPriceEl.addEventListener('change', () => {
            if (typeof calcMarginForLevel === 'function') calcMarginForLevel(row.closest('.packaging-level'));
        });
    }
    if (unitPrice) {
        setTimeout(() => { if (typeof calcMarginForLevel === 'function') calcMarginForLevel(row.closest('.packaging-level')); }, 50);
    }
}

function initQtyTiers(levelDiv, tiers = []) {
    const list = levelDiv.querySelector('.qty-tiers-list');
    const btn = levelDiv.querySelector('.btn-add-qty-tier');
    if (!list || !btn) return;
    list.innerHTML = '';
    tiers.forEach(t => addQtyTierRow(list, t));
    
    // Gunakan removeEventListener untuk mencegah duplikasi event listener
    const clone = btn.cloneNode(true);
    btn.parentNode.replaceChild(clone, btn);
    
    clone.addEventListener('click', function(e) {
        e.preventDefault();
        try {
            addQtyTierRow(list);
        } catch(err) {
            console.error('Error adding tier row:', err);
            alert('Terjadi kesalahan: ' + err.message);
        }
    });
}

function collectQtyTiers(levelDiv) {
    const tiers = [];
    levelDiv.querySelectorAll('.qty-tier-row').forEach(row => {
        const min_qty = parseFloat(row.querySelector('.tier-min-qty')?.value);
        const unit_price = parseFloat(row.querySelector('.tier-unit-price')?.value);
        const sale_mode = row.querySelector('.tier-sale-mode')?.value || 'both';
        const label = row.querySelector('.tier-label')?.value?.trim() || '';
        if (min_qty > 0 && Number.isFinite(unit_price) && unit_price >= 0) {
            tiers.push({ min_qty, unit_price, sale_mode, label });
        }
    });
    tiers.sort((a, b) => a.min_qty - b.min_qty);
    return tiers;
}

// ===== Packaging Levels =====
function addPackagingLevel(prefill = null) {
    if (referenceMode && referenceProductData && !prefill) {
        showToast('Kemasan mengikuti produk referensi', 'info');
        return;
    }
    levelCount++;
    const container = document.getElementById('packagingContainer');
    
    const div = document.createElement('div');
    div.className = 'packaging-level';
    div.setAttribute('data-level', levelCount);
    div.style.cssText = 'background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--border-color);position:relative;transition:all 0.3s ease;';
    
    const isLevel1 = levelCount === 1;
    const levelLabels = {
        1: 'Level 1 — Satuan Terkecil (Eceran)',
        2: 'Level 2 — Kemasan Tambahan (Bebas/Opsional)',
        3: 'Level 3 — Kemasan Tambahan (Bebas/Opsional)',
    };
    const title = levelLabels[levelCount] || `Level ${levelCount} — Kemasan Tambahan (Opsional)`;
    
    let containedHtml = '';
    if (isLevel1) {
        containedHtml = `
            <div style="flex:1;">
                <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Isi</label>
                <input type="number" name="contained_qty[]" value="1" class="form-control-dark" style="width:100%;background:var(--surface-2);color:var(--text-muted);" readonly>
            </div>
        `;
    } else {
        containedHtml = `
            <div style="flex:1;">
                <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Isi per kemasan *</label>
                <input type="number" name="contained_qty[]" placeholder="Cth: 10, 12, 24" class="form-control-dark contained-qty" style="width:100%;" min="2" required>
                <small class="base-qty-info" style="font-size:10px;color:var(--info);margin-top:2px;display:block;"></small>
            </div>
        `;
    }

    const removeBtn = !isLevel1 ? `<button type="button" onclick="removeLevel(this)" style="position:absolute;top:12px;right:16px;background:none;border:none;color:var(--danger);font-size:1.2rem;cursor:pointer;z-index:2;"><i class="bi bi-x-circle-fill"></i></button>` : '';

    div.innerHTML = `
        ${removeBtn}
        <div class="section-title" style="margin-bottom:12px;color:var(--primary);">${title}</div>
        
        <div style="display:flex;gap:8px;margin-bottom:12px;">
            <div style="flex:2;"><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:4px;align-items:center;"><span>Satuan *</span><a href="${BASE_URL}settings/master-data" target="_blank" style="font-size:10px;color:var(--primary);text-decoration:none;"><i class="bi bi-box-arrow-up-right"></i> Master Data</a></label><div class="unit-searchbox-instance" data-level="${levelCount}"></div></div>
            ${containedHtml}
        </div>

        <div style="margin-bottom:12px;" class="pkg-barcode-row">
            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Barcode</label>
            <div style="background:var(--bg-input);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:0 8px 0 12px;display:flex;align-items:center;gap:4px;">
                <i class="bi bi-upc-scan" style="color:var(--primary);flex-shrink:0;cursor:pointer;padding:8px 4px;font-size:1.1rem;" onclick="BarcodeUtil.scanBarcode(this.nextElementSibling)" title="Scan Barcode"></i>
                <input type="text" name="barcode[]" class="barcode-field" placeholder="Scan, ketik, atau generate..." style="flex:1;border:none;background:transparent;padding:12px 6px;color:var(--text-primary);font-size:var(--font-size-base);outline:none;font-family:var(--font-family);min-width:0;">
                <button type="button" class="btn-scan-bc" title="Scan barcode dengan kamera" style="border:none;background:var(--info-bg);color:var(--info);padding:6px 10px;border-radius:6px;font-size:11px;cursor:pointer;white-space:nowrap;"><i class="bi bi-camera"></i></button>
                <button type="button" class="btn-gen-bc" title="Generate barcode" style="border:none;background:var(--primary-bg);color:var(--primary);padding:6px 10px;border-radius:6px;font-size:11px;cursor:pointer;white-space:nowrap;"><i class="bi bi-magic"></i></button>
                <button type="button" class="btn-print-bc" title="Cetak barcode" style="border:none;background:var(--surface-2);color:var(--text-secondary);padding:6px 10px;border-radius:6px;font-size:11px;cursor:pointer;"><i class="bi bi-printer"></i></button>
            </div>
        </div>

        <div style="background:rgba(0,0,0,0.15);padding:12px;border-radius:var(--radius-sm);border:1px solid rgba(255,255,255,0.05);">
            ${!isLevel1 ? `
            <label class="price-custom-toggle buy-custom-toggle" title="Centang untuk mengatur harga modal secara manual pada level ini">
                <input type="checkbox" class="chk-buy-custom">
                <i class="bi bi-pencil-square" style="font-size:10px;"></i> Harga Modal Custom (tidak ikut otomatis)
            </label>` : ''}
            <div style="margin-bottom:8px;position:relative;">
                <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:4px;align-items:center;">
                    <span>Harga Modal / Beli *</span>
                    <span style="color:var(--primary);cursor:pointer;background:var(--surface-2);padding:2px 6px;border-radius:4px;font-size:10px;" onclick="const b=this.nextElementSibling; b.style.display=b.style.display==='none'?'flex':'none'"><i class="bi bi-calculator"></i> Kalkulator</span>
                    <div style="display:none;position:absolute;top:24px;right:0;background:var(--surface-2);padding:8px;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.8);z-index:10;gap:4px;align-items:center;border:1px solid var(--border-color);">
                        <input type="number" placeholder="Total Rp" step="any" class="form-control-dark calc-total" style="width:90px;font-size:12px;padding:4px;">
                        <span style="color:var(--text-muted);">/</span>
                        <input type="number" placeholder="Qty" value="1" class="form-control-dark calc-qty" style="width:50px;font-size:12px;padding:4px;">
                        <button type="button" class="btn-primary-custom" style="padding:4px 8px;font-size:12px;border-radius:4px;" onclick="const p=this.parentElement; const t=p.querySelector('.calc-total').value; const q=p.querySelector('.calc-qty').value; if(t&&q>0){ const inp=p.closest('label').parentElement.querySelector('.buy-price'); const calculatedVal = parseFloat(t)/parseFloat(q); inp.value=Number.isInteger(calculatedVal) ? calculatedVal : parseFloat(calculatedVal.toFixed(2)); inp.dispatchEvent(new Event('input')); p.style.display='none'; }"><i class="bi bi-check2"></i> Hitung</button>
                    </div>
                </label>
                <input type="number" name="buy_price[]" placeholder="0" step="any" class="form-control-dark price-input buy-price" style="width:100%;" required>
                ${!isLevel1 ? `<div class="price-locked-note buy-locked-note"><i class="bi bi-link-45deg"></i> Otomatis dihitung dari harga pcs × isi kemasan</div>` : ''}
            </div>
            <div style="margin-top:8px;margin-bottom:10px;padding:8px 10px;background:rgba(0,0,0,0.12);border-radius:var(--radius-sm);border:1px dashed rgba(255,255,255,0.08);">
                <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;"><i class="bi bi-percent"></i> PPN & Diskon (Harga Modal)</div>
                <div style="display:flex;gap:8px;">
                    <div style="flex:1;">
                        <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">PPN (%)</label>
                        <input type="number" name="ppn_pct[]" placeholder="0" step="any" min="0" max="100" class="form-control-dark ppn-input" style="width:100%;font-size:12px;">
                    </div>
                    <div style="flex:1.5;">
                        <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Diskon</label>
                        <div style="display:flex;">
                            <div class="discount-toggle-group" style="display:flex; border-radius:var(--radius-md) 0 0 var(--radius-md); overflow:hidden; border:1px solid var(--border-color); border-right:none; width:65px;">
                                <button type="button" class="btn-discount-mode rp-mode active" style="flex:1; padding:6px 0; background:var(--primary); color:#fff; border:none; font-size:11px; font-weight:bold; cursor:pointer;" onclick="event.preventDefault(); const p=this.closest('.discount-toggle-group'); p.querySelector('.pct-mode').style.background='var(--bg-input)'; p.querySelector('.pct-mode').style.color='var(--text-muted)'; this.style.background='var(--primary)'; this.style.color='#fff'; const hidden=p.nextElementSibling; hidden.value='rp'; hidden.dispatchEvent(new Event('change'));">Rp</button>
                                <button type="button" class="btn-discount-mode pct-mode" style="flex:1; padding:6px 0; background:var(--bg-input); color:var(--text-muted); border:none; font-size:11px; font-weight:bold; cursor:pointer;" onclick="event.preventDefault(); const p=this.closest('.discount-toggle-group'); p.querySelector('.rp-mode').style.background='var(--bg-input)'; p.querySelector('.rp-mode').style.color='var(--text-muted)'; this.style.background='var(--primary)'; this.style.color='#fff'; const hidden=p.nextElementSibling; hidden.value='pct'; hidden.dispatchEvent(new Event('change'));">%</button>
                            </div>
                            <input type="hidden" name="discount_mode[]" class="discount-mode" value="rp">
                            <input type="number" name="discount_value[]" placeholder="0" step="any" min="0" class="form-control-dark discount-value" style="width:100%; border-top-left-radius:0; border-bottom-left-radius:0; font-size:12px;">
                        </div>
                    </div>
                </div>
                <div style="font-size:10px;color:var(--warning);margin-top:5px;"><i class="bi bi-exclamation-triangle"></i> Harga Modal Final = Modal + PPN - Diskon — disimpan permanen ke DB</div>
            </div>

            ${!isLevel1 ? `
            <label class="price-custom-toggle sell-custom-toggle" title="Centang untuk mengatur harga jual secara manual (misal harga renceng lebih murah dari harga pcs × qty)">
                <input type="checkbox" class="chk-sell-custom">
                <i class="bi bi-tag" style="font-size:10px;"></i> Harga Jual Custom (harga spesial per kemasan ini)
            </label>` : ''}

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div>
                    <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Jual Ecer/Retail *</label>
                    <input type="number" name="sell_price_retail[]" placeholder="0" step="any" class="form-control-dark price-input retail-price" style="width:100%;" required>
                </div>
                <div>
                    <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Jual Grosir</label>
                    <input type="number" name="sell_price_wholesale[]" placeholder="0" step="any" class="form-control-dark price-input wholesale-price" style="width:100%;">
                </div>
            </div>
            ${!isLevel1 ? `<div class="price-locked-note sell-locked-note"><i class="bi bi-link-45deg"></i> Otomatis dihitung dari harga pcs × isi kemasan</div>` : ''}
            <div class="margin-calc" style="margin-top:8px;font-size:11px;color:var(--text-muted);">
                <div style="display:flex;justify-content:space-between;">
                    <span class="margin-retail-text">Markup Retail: 0%</span>
                    <span class="margin-wholesale-text">Markup Grosir: 0%</span>
                </div>
                <div class="margin-final-note" style="font-size:10px;color:var(--info);margin-top:3px;"></div>
            </div>
        </div>

        <div class="qty-price-tiers-section" style="margin-top:12px;padding-top:12px;border-top:1px dashed var(--border-color);">
            <div style="font-size:11px;font-weight:600;color:var(--info);margin-bottom:6px;"><i class="bi bi-tags"></i> Harga Spesial per Kuantitas</div>
            <p style="font-size:10px;color:var(--text-muted);margin-bottom:8px;">Min. qty = jumlah satuan kemasan ini. Harga = per 1 satuan pada tier tersebut.</p>
            <div class="qty-tiers-list"></div>
            <button type="button" class="btn-outline-custom btn-add-qty-tier" style="width:100%;margin-top:6px;font-size:11px;padding:6px;border-style:dashed;"><i class="bi bi-plus"></i> Tambah Tier Harga</button>
        </div>
    `;
    
    container.appendChild(div);

    // Create SearchBox for unit
    const unitContainer = div.querySelector('.unit-searchbox-instance');
    const unitSB = new SearchBox(unitContainer, {
        options: [...unitsData],
        placeholder: 'Pilih satuan...',
        name: 'unit_id[]',
        required: true,
        addLabel: 'Tambah Satuan Baru',
        onAdd: () => openMasterModal('unit'),
        onChange: () => { updateNamePreview(); updateBaseQtyInfo(); },
        linkUrl: BASE_URL + 'settings/master-data',
        linkLabel: 'Buka Master Data'
    });
    unitContainer._searchbox = unitSB;

    if (prefill) {
        if (prefill.unit_id) unitSB.select(String(prefill.unit_id), prefill.unit_name || '');
        const cqtyEl = div.querySelector('input[name="contained_qty[]"]');
        if (cqtyEl && prefill.contained_qty) cqtyEl.value = prefill.contained_qty;
        const buyEl = div.querySelector('.buy-price');
        const retailEl = div.querySelector('.retail-price');
        const wholesaleEl = div.querySelector('.wholesale-price');
        const bcEl = div.querySelector('.barcode-field');
        if (buyEl) buyEl.value = prefill.buy_price ?? '';
        if (retailEl) retailEl.value = prefill.sell_price_retail ?? '';
        if (wholesaleEl) wholesaleEl.value = prefill.sell_price_wholesale ?? '';
        if (bcEl) bcEl.value = prefill.barcode ?? '';
        
        const ppnEl = div.querySelector('.ppn-input');
        const dModeEl = div.querySelector('.discount-mode');
        const dValEl = div.querySelector('.discount-value');
        if (ppnEl) ppnEl.value = prefill.ppn_pct ?? '';
        if (dModeEl && prefill.discount_mode) {
             dModeEl.value = prefill.discount_mode;
             const dGroup = div.querySelector('.discount-toggle-group');
             if (dGroup) {
                 const rpBtn = dGroup.querySelector('.rp-mode');
                 const pctBtn = dGroup.querySelector('.pct-mode');
                 if (prefill.discount_mode === 'pct') {
                     rpBtn.style.background='var(--bg-input)'; rpBtn.style.color='var(--text-muted)';
                     pctBtn.style.background='var(--primary)'; pctBtn.style.color='#fff';
                 } else {
                     pctBtn.style.background='var(--bg-input)'; pctBtn.style.color='var(--text-muted)';
                     rpBtn.style.background='var(--primary)'; rpBtn.style.color='#fff';
                 }
             }
        }
        if (dValEl) dValEl.value = prefill.discount_value ?? '';
        
        calcMarginForLevel(div);
        
        const chkBuy = div.querySelector('.chk-buy-custom');
        const chkSell = div.querySelector('.chk-sell-custom');
        if (chkBuy) chkBuy.checked = true;
        if (chkSell) chkSell.checked = true;

        const hiddenQty = document.createElement('input');
        hiddenQty.type = 'hidden';
        hiddenQty.name = 'qty_prices_json[]';
        hiddenQty.className = 'qty-prices-json-input';
        hiddenQty.value = JSON.stringify(prefill.qty_prices || []);
        div.appendChild(hiddenQty);
    } else if (isLevel1) {
        const pcsOption = unitsData.find(u => u.label.toLowerCase() === 'pcs');
        if (pcsOption) unitSB.select(pcsOption.value, pcsOption.label);
        const hiddenQty = document.createElement('input');
        hiddenQty.type = 'hidden';
        hiddenQty.name = 'qty_prices_json[]';
        hiddenQty.className = 'qty-prices-json-input';
        hiddenQty.value = '[]';
        div.appendChild(hiddenQty);
    } else {
        const hiddenQty = document.createElement('input');
        hiddenQty.type = 'hidden';
        hiddenQty.name = 'qty_prices_json[]';
        hiddenQty.className = 'qty-prices-json-input';
        hiddenQty.value = '[]';
        div.appendChild(hiddenQty);
    }

    initQtyTiers(div, prefill ? (prefill.qty_prices || []) : []);

    const bcInput = div.querySelector('.barcode-field');
    const genBtn = div.querySelector('.btn-gen-bc');
    const printBtn = div.querySelector('.btn-print-bc');
    const scanBtn = div.querySelector('.btn-scan-bc');
    
    scanBtn?.addEventListener('click', () => BarcodeUtil.scanBarcode(bcInput));
    genBtn?.addEventListener('click', () => BarcodeUtil.fillInput(bcInput, genBtn));
    printBtn?.addEventListener('click', () => {
        const unit = unitSB.getLabel() || '';
        BarcodeUtil.printFromInput(bcInput, getCreateProductLabel(), unit ? `1 ${unit}` : '');
    });

    applyReferenceLock();

    if (typeof PackagingPriceSync !== 'undefined') {
        PackagingPriceSync.bindNewLevel(div);
    } else {
        div.querySelectorAll('.price-input').forEach(input => {
            input.addEventListener('input', () => calcMarginForLevel(div));
        });
    }

    const cqtyInput = div.querySelector('.contained-qty');
    if (cqtyInput) {
        cqtyInput.addEventListener('input', () => { updateNamePreview(); updateBaseQtyInfo(); });
    }

    // PPN & Diskon inputs juga trigger recalc margin
    div.querySelector('.ppn-input')?.addEventListener('input', () => calcMarginForLevel(div));
    div.querySelector('.discount-value')?.addEventListener('input', () => calcMarginForLevel(div));
    div.querySelector('.discount-mode')?.addEventListener('change', () => calcMarginForLevel(div));
}

function removeLevel(btn) {
    if (referenceMode && referenceProductData) return;
    btn.closest('.packaging-level').remove();
    // Renumber levels
    const levels = document.querySelectorAll('.packaging-level');
    levelCount = levels.length;
    levels.forEach((lv, i) => {
        lv.setAttribute('data-level', i + 1);
    });
    updateNamePreview();
    updateBaseQtyInfo();
    if (typeof PackagingPriceSync !== 'undefined') PackagingPriceSync.propagateAllFromLevel1();
}

function updateBaseQtyInfo() {
    const levels = document.querySelectorAll('.packaging-level');
    let runningBase = 1;
    const firstUnitSB = levels[0]?.querySelector('.unit-searchbox-instance')?._searchbox;
    const baseUnitName = firstUnitSB ? firstUnitSB.getLabel() || 'pcs' : 'pcs';
    
    levels.forEach((lv, i) => {
        if (i === 0) {
            runningBase = 1;
            return;
        }
        const cqty = parseInt(lv.querySelector('.contained-qty')?.value) || 0;
        if (cqty > 0) runningBase = runningBase * cqty;
        const info = lv.querySelector('.base-qty-info');
        if (info && cqty > 0) {
            info.textContent = `= ${runningBase} ${baseUnitName}`;
        } else if (info) {
            info.textContent = '';
        }
    });
}

function calcMarginForLevel(div) {
    const buy = parseFloat(div.querySelector('.buy-price')?.value) || 0;
    const retail = parseFloat(div.querySelector('.retail-price')?.value) || 0;
    const wholesale = parseFloat(div.querySelector('.wholesale-price')?.value) || 0;

    // Baca PPN & Diskon
    const ppnPct = parseFloat(div.querySelector('.ppn-input')?.value) || 0;
    const dMode = div.querySelector('.discount-mode')?.value || 'rp';
    const dVal = parseFloat(div.querySelector('.discount-value')?.value) || 0;

    const retailText = div.querySelector('.margin-retail-text');
    const wholesaleText = div.querySelector('.margin-wholesale-text');
    const finalNoteEl = div.querySelector('.margin-final-note');

    const formatRp = (num) => 'Rp ' + Math.round(num).toLocaleString('id-ID');

    // Harga Modal Final = Modal + PPN - Diskon (harga modal yang benar-benar disimpan ke DB)
    function applyPpnDiskon(basePrice) {
        if (basePrice <= 0) return basePrice;
        const buyAfterPpn = basePrice * (1 + ppnPct / 100);
        if (dMode === 'pct') {
            return buyAfterPpn * (1 - dVal / 100);
        } else {
            return buyAfterPpn - dVal;
        }
    }

    const finalBuy = Math.max(0, applyPpnDiskon(buy));
    const hasPpnDiskon = ppnPct > 0 || dVal > 0;

    // Markup = (Jual - Modal) / Modal × 100 (berbasis harga modal, bukan harga jual)
    if (finalBuy > 0 && retail > 0) {
        const m = ((retail - finalBuy) / finalBuy * 100).toFixed(1);
        const profit = retail - finalBuy;
        const color = m >= 10 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)');
        retailText.innerHTML = `Ecer: <strong style="color:${color}">${m}%</strong> <span style="font-size:10px;color:var(--text-muted);">(${formatRp(profit)})</span>`;
    } else {
        retailText.innerHTML = `Markup Retail: 0%`;
    }

    if (finalBuy > 0 && wholesale > 0) {
        const m = ((wholesale - finalBuy) / finalBuy * 100).toFixed(1);
        const profit = wholesale - finalBuy;
        const color = m >= 5 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)');
        wholesaleText.innerHTML = `Grosir: <strong style="color:${color}">${m}%</strong> <span style="font-size:10px;color:var(--text-muted);">(${formatRp(profit)})</span>`;
    } else {
        wholesaleText.innerHTML = `Markup Grosir: 0%`;
    }

    // Tampilkan info harga modal final jika ada PPN/Diskon
    if (finalNoteEl) {
        if (hasPpnDiskon && buy > 0) {
            finalNoteEl.innerHTML = `<i class="bi bi-arrow-right-short"></i> Harga Modal Final (disimpan ke DB): <strong>${formatRp(finalBuy)}</strong>`;
        } else {
            finalNoteEl.innerHTML = '';
        }
    }

    // Kalkulasi markup untuk tier pricing di level ini
    div.querySelectorAll('.qty-tier-row').forEach(row => {
        const infoEl = row.querySelector('.tier-margin-info');
        if (!infoEl) return;
        const tierUnit = parseFloat(row.querySelector('.tier-unit-price')?.value) || 0;
        if (finalBuy > 0 && tierUnit > 0) {
            const m = ((tierUnit - finalBuy) / finalBuy * 100).toFixed(1);
            const profit = tierUnit - finalBuy;
            const color = m >= 5 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)');
            infoEl.innerHTML = `Mkp: <strong style="color:${color}">${m}%</strong> <span style="font-size:9px;color:var(--text-muted);">(${profit > 0 ? '+' : ''}${formatRp(profit)})</span>`;
        } else {
            infoEl.innerHTML = '';
        }
    });
}

// Auto-generate name preview
document.querySelectorAll('[name="product_type"],[name="variant"],[name="weight_value"],[name="single_name"]').forEach(el => {
    el.addEventListener('input', updateNamePreview);
    el.addEventListener('change', updateNamePreview);
});

function updateNamePreview() {
    const isMulti = document.getElementById('isMultivariant')?.checked;
    
    let baseName = '';
    let shortBaseName = '';

    if (isMulti) {
        const brand = brandSB ? brandSB.getLabel() : '';
        const type = document.querySelector('[name="product_type"]')?.value?.trim() || '';
        const variant = document.querySelector('[name="variant"]')?.value?.trim() || '';
        
        if (brand) baseName += brand;
        if (type) baseName += (baseName ? ' ' : '') + type;
        if (variant) baseName += ' ' + variant;
        
        shortBaseName = baseName;
    } else {
        const singleName = document.querySelector('[name="single_name"]')?.value?.trim() || '';
        baseName = singleName;
        shortBaseName = singleName;
    }

    let rawWeight = document.querySelector('[name="weight_value"]')?.value || '';
    let formattedWeight = '';
    if (rawWeight !== '') {
        formattedWeight = parseFloat(rawWeight).toString();
    }
    const wUnit = weightUnitSB ? weightUnitSB.getValue() : '';
    
    let fullName = baseName;
    let shortLabel = shortBaseName;

    // Point 7: Packaging chain for all levels (e.g. 10 x 10 x 16btg)
    const levels = document.querySelectorAll('.packaging-level');
    const qtyChain = [];
    if (levels.length > 1) {
        for (let i = levels.length - 1; i >= 1; i--) {
            const qty = parseInt(levels[i].querySelector('input[name="contained_qty[]"]')?.value) || 0;
            if (qty > 0) qtyChain.push(qty);
        }
    }

    if (qtyChain.length > 0) {
        const chainStr = qtyChain.join(' x ');
        if (formattedWeight && wUnit) {
            fullName += ` (${chainStr} x ${formattedWeight}${wUnit})`;
            shortLabel += ` ${formattedWeight}${wUnit}`;
            formattedWeight = ''; // clear so it doesn't get added twice
        } else {
            const unitSB = levels[0]?.querySelector('.unit-searchbox-instance')?._searchbox;
            const baseUnit = unitSB ? unitSB.getLabel() || 'pcs' : 'pcs';
            fullName += ` (${chainStr} x 1${baseUnit})`;
        }
    }

    if (formattedWeight && wUnit) {
        fullName += ' ' + formattedWeight + wUnit;
        shortLabel += ' ' + formattedWeight + wUnit;
    }

    if (shortLabel.length > 35) shortLabel = shortLabel.substring(0, 32) + '...';

    document.getElementById('namePreview').textContent = fullName || '-';
    
    if (!isLabelEdited) {
        document.getElementById('manualLabel').value = shortLabel || '';
    }
}

// ===== Submit Product =====
async function submitProduct(e) {
    e.preventDefault();

    if (referenceMode && referenceProductData) {
        const variant = document.querySelector('[name="variant"]')?.value?.trim();
        if (!variant) {
            showToast('Varian wajib diisi untuk produk multivariant', 'warning');
            document.getElementById('variantInput')?.focus();
            return;
        }
    }

    const btn = document.getElementById('btnSubmit');
    const prevText = btn.innerHTML;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
    btn.disabled = true;

    const form = e.target;
    const data = new FormData(form);
    
    // Update qty_prices json array
    document.querySelectorAll('.packaging-level').forEach(div => {
        const tiers = collectQtyTiers(div);
        const hiddenInp = div.querySelector('.qty-prices-json-input');
        if (hiddenInp) hiddenInp.value = JSON.stringify(tiers);
    });
    
    // Refresh formData with updated hidden fields
    const updatedData = new FormData(form);
    
    // Compose & validate names. full_name is NOT NULL di DB,
    // fallback aman: namePreview → manual label → single_name → brand+type+variant
    let fullName = (document.getElementById('namePreview').textContent || '').trim();
    if (fullName === '-' || fullName === '') fullName = '';
    let labelText = document.getElementById('manualLabel').value.trim();
    if (!fullName) {
        const single = document.querySelector('[name="single_name"]')?.value?.trim() || '';
        const brand = brandSB ? (brandSB.getLabel() || '').trim() : '';
        const ptype = document.querySelector('[name="product_type"]')?.value?.trim() || '';
        const variant = document.querySelector('[name="variant"]')?.value?.trim() || '';
        fullName = single || labelText || [brand, ptype, variant].filter(Boolean).join(' ').trim();
    }
    if (!fullName) {
        showToast('Nama produk wajib diisi (Brand/Jenis/Varian atau Nama Produk).', 'warning');
        btn.innerHTML = prevText;
        btn.disabled = false;
        return;
    }
    if (!labelText) labelText = fullName.substring(0, 35);

    data.set('full_name', fullName);
    updatedData.set('full_name', fullName);
    data.set('short_label', labelText);
    updatedData.set('short_label', labelText);
    data.set('invoice_name', labelText);
    updatedData.set('invoice_name', labelText);

        // Update supplier invoice name from dynamic list
        const collectedNames = collectInvoiceNames();
        updatedData.set('supplier_invoice_name', collectedNames);
        
        // Ensure is_available is explicitly set from the toggle state
        const isAvailableToggle = document.getElementById('toggleIsAvailableCreate');
        updatedData.set('is_available', isAvailableToggle && isAvailableToggle.checked ? 1 : 0);
        
        if (!navigator.onLine && typeof OfflineDB !== 'undefined') {
        try {
            const payload = {};
            updatedData.forEach((value, key) => {
                if (payload[key]) {
                    if (!Array.isArray(payload[key])) payload[key] = [payload[key]];
                    payload[key].push(value);
                } else {
                    payload[key] = value;
                }
            });
            
            // Optimistic Save to Dexie products table
            const tempId = parseInt('999' + (Date.now() % 100000));
            const newProduct = {
                id: tempId,
                full_name: payload.full_name,
                short_label: payload.short_label || payload.full_name,
                brand_name: brandSB ? brandSB.getLabel() : '',
                category_name: categorySB ? categorySB.getLabel() : '',
                code: payload.code || '',
                packagings: [],
                is_pending: true
            };
            
            if (payload.unit_id) {
                const units = Array.isArray(payload.unit_id) ? payload.unit_id : [payload.unit_id];
                const buys = Array.isArray(payload.buy_price) ? payload.buy_price : [payload.buy_price];
                const retails = Array.isArray(payload.sell_price_retail) ? payload.sell_price_retail : [payload.sell_price_retail];
                const wholesales = Array.isArray(payload.sell_price_wholesale) ? payload.sell_price_wholesale : [payload.sell_price_wholesale];
                const cqtys = Array.isArray(payload.contained_qty) ? payload.contained_qty : [payload.contained_qty];
                
                units.forEach((u, i) => {
                    const unitObj = unitsData.find(x => x.value == u);
                    newProduct.packagings.push({
                        level: i + 1,
                        unit_name: unitObj ? unitObj.label : 'Unit',
                        contained_qty: cqtys[i] || 1,
                        buy_price: buys[i] || 0,
                        sell_price_retail: retails[i] || 0,
                        sell_price_wholesale: wholesales[i] || 0
                    });
                });
            }
            await OfflineDB.saveProduct(newProduct);
            
            await OfflineDB.addPendingChange(`${BASE_URL}api/products`, 'POST', payload);
            showToast('Tersimpan offline. Akan disinkronkan saat online.', 'info');
            if (typeof updateSyncBadge === 'function') updateSyncBadge();
            setTimeout(() => window.location.href = `${BASE_URL}products`, 1500);
        } catch (err) {
            showToast('Gagal menyimpan offline: ' + err.message, 'error');
            btn.innerHTML = prevText;
            btn.disabled = false;
        }
        return;
    }

    try {
        const result = await api(`${BASE_URL}api/products`, {
            method: 'POST',
            body: updatedData
        });
        
        if (result.success) {
            showToast('Produk berhasil ditambahkan!', 'success');
            if (typeof OfflineDB !== 'undefined' && OfflineDB.saveProduct) {
                // Fetch again to save the complete product with all relations to local DB
                api(`${BASE_URL}api/products/${result.id}`).then(res => {
                    if (res && res.id) OfflineDB.saveProduct(res);
                }).catch(e => console.error(e));
            }
            setTimeout(() => window.location.href = `${BASE_URL}products/${result.id}`, 1000);
        } else {
            showToast(result.error || 'Gagal menyimpan produk', 'error');
            btn.innerHTML = prevText;
            btn.disabled = false;
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
        btn.innerHTML = prevText;
        btn.disabled = false;
    }
}

function toggleSupplierInfo() {
    const panel = document.getElementById('supplierInfoPanel');
    const icon = document.getElementById('iconSupplierChevron');
    const isOpen = panel.style.display !== 'none';
    panel.style.display = isOpen ? 'none' : 'block';
    icon.style.transform = isOpen ? '' : 'rotate(180deg)';
}

function initInvoiceNameList(namesStr) {
    const list = document.getElementById('invoiceNameList');
    if (!list) return;
    list.innerHTML = '';
    const names = (namesStr || '').split(/[;\n]/).map(n => n.trim()).filter(n => n);
    if (names.length === 0) {
        addInvoiceName();
    } else {
        names.forEach(n => addInvoiceName(n));
    }
}

function addInvoiceName(val = '') {
    const list = document.getElementById('invoiceNameList');
    if (!list) return;
    const div = document.createElement('div');
    div.style.display = 'flex';
    div.style.gap = '6px';
    div.innerHTML = `
        <input type="text" class="form-control-dark invoice-name-item" placeholder="Cth: CIMORY UHT PORORO" style="flex:1;" value="${escapeHtml(val)}">
        <button type="button" onclick="this.parentElement.remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:0 12px;cursor:pointer;"><i class="bi bi-x-lg"></i></button>
    `;
    list.appendChild(div);
}

function collectInvoiceNames() {
    const inputs = document.querySelectorAll('.invoice-name-item');
    const names = Array.from(inputs).map(inp => inp.value.trim()).filter(v => v);
    return names.join(';');
}

function toggleAvailability(inputId, switchId, labelId) {
    const inp = document.getElementById(inputId);
    const sw = document.getElementById(switchId);
    const lbl = document.getElementById(labelId);
    if (!inp || !sw || !lbl) return;

    inp.checked = !inp.checked;
    
    if (inp.checked) {
        sw.style.background = 'var(--primary)';
        sw.firstElementChild.style.transform = 'translateX(20px)';
        lbl.textContent = 'Tersedia';
        lbl.style.color = 'var(--success)';
    } else {
        sw.style.background = 'var(--surface-2)';
        sw.firstElementChild.style.transform = 'translateX(0)';
        lbl.textContent = 'Tidak Tersedia';
        lbl.style.color = 'var(--text-muted)';
    }
}
</script>
