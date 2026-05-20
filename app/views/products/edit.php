<!-- Product Edit View -->
<?php
/**
 * @var array $units
 * @var array $brands
 * @var array $categories
 * @var array $packagings
 * @var array $product
 * @var string $csrfToken
 */
$unitsJson = json_encode(array_map(fn($u) => ['value'=>(string)$u['id'],'label'=>$u['name']], $units), JSON_UNESCAPED_UNICODE);
$brandsJson = json_encode(array_map(fn($b) => ['value'=>(string)$b['id'],'label'=>$b['name']], $brands), JSON_UNESCAPED_UNICODE);
$catsJson = json_encode(array_map(fn($c) => ['value'=>(string)$c['id'],'label'=>$c['name']], $categories), JSON_UNESCAPED_UNICODE);
$pkgsJson = json_encode($packagings, JSON_UNESCAPED_UNICODE);
?>
<div class="page-section">
    <a href="<?= BASE_URL ?>products/<?= $product['id'] ?>" style="color:var(--text-muted);text-decoration:none;font-size:var(--font-size-sm);display:inline-flex;align-items:center;gap:4px;margin-bottom:16px;">
        <i class="bi bi-arrow-left"></i> Kembali ke Detail
    </a>
    <h2 style="font-size:var(--font-size-lg);font-weight:700;margin-bottom:20px;">Edit Produk</h2>

    <!-- Mode Varian dari Referensi (Point 4) -->
    <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--border-color);">
        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
            <input type="checkbox" id="editUseReferenceMode" style="margin-top:3px;width:18px;height:18px;accent-color:var(--primary);">
            <span>
                <span style="font-weight:600;font-size:var(--font-size-sm);display:block;">Tambah varian dari produk referensi</span>
                <span style="font-size:var(--font-size-xs);color:var(--text-muted);">Salin spesifikasi & kemasan dari produk lain, hanya varian & barcode yang berbeda</span>
            </span>
        </label>
        <div id="editReferencePanel" style="display:none;margin-top:14px;padding-top:14px;border-top:1px solid var(--border-color);">
            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:6px;">Cari produk referensi</label>
            <div style="position:relative;">
                <input type="text" id="editReferenceSearch" class="form-control-dark" placeholder="Ketik nama produk..." autocomplete="off" style="width:100%;">
                <div id="editReferenceResults" style="display:none;position:absolute;left:0;right:0;top:100%;z-index:50;background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);max-height:220px;overflow-y:auto;margin-top:4px;box-shadow:0 8px 24px rgba(0,0,0,0.3);"></div>
            </div>
            <div id="editReferenceSelected" style="display:none;margin-top:10px;padding:10px 12px;background:var(--info-bg);border-radius:var(--radius-sm);font-size:var(--font-size-xs);color:var(--info);">
                <i class="bi bi-link-45deg"></i> Referensi: <strong id="editReferenceSelectedName"></strong>
                <button type="button" onclick="clearEditReference()" style="float:right;background:none;border:none;color:var(--danger);font-size:11px;cursor:pointer;">Hapus</button>
            </div>
        </div>
    </div>

    <form id="formEditProduct" onsubmit="submitEditProduct(event)">
        <input type="hidden" name="csrf_token" id="csrfToken" value="<?= $csrfToken ?>">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <input type="hidden" name="reference_product_id" id="editReferenceProductId" value="">

        <!-- Identitas Produk (Point 5: Multivarian toggle) -->
        <div id="editIdentitySection" style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--border-color);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <div class="section-title" style="margin-bottom:0;">Identitas Produk</div>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:var(--font-size-xs);">
                    <input type="checkbox" id="editIsMultivariant" <?= (!empty($product['brand_id']) || !empty($product['product_type'])) ? 'checked' : '' ?> style="width:16px;height:16px;accent-color:var(--primary);">
                    <span>Produk Multivarian</span>
                </label>
            </div>

            <!-- Single variant panel -->
            <div id="editSingleVariantPanel" style="display:none;margin-bottom:12px;">
                <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Nama Produk *</label>
                <input type="text" name="single_name" id="editSingleNameInput" placeholder="Cth: Sapu Lidi Pendek" class="form-control-dark" style="width:100%;" value="<?= htmlspecialchars($product['full_name'] ?? '') ?>">
            </div>

            <!-- Multi variant panel -->
            <div id="editMultiVariantPanel">
                <div style="margin-bottom:12px;"><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Brand / Merek *</label><div id="editBrandSB"></div></div>
                <div style="margin-bottom:12px;"><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Jenis Produk</label><input type="text" name="product_type" id="editProductTypeInput" value="<?= htmlspecialchars($product['product_type'] ?? '') ?>" placeholder="Cth: UHT, Goreng..." class="form-control-dark" style="width:100%;"></div>
                <div style="margin-bottom:12px;" id="editVariantFieldWrap"><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Varian <span id="editVariantRequiredMark" style="display:none;color:var(--primary);">*</span></label><input type="text" name="variant" id="editVariantInput" value="<?= htmlspecialchars($product['variant'] ?? '') ?>" placeholder="Cth: Choco Malt, Original..." class="form-control-dark" style="width:100%;"><small id="editVariantHint" style="display:none;font-size:10px;color:var(--info);margin-top:4px;">Isi varian baru (wajib saat mode referensi)</small></div>
            </div>

            <div style="margin-bottom:12px;"><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Kategori *</label><div id="editCatSB"></div></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Berat/Volume</label><input type="number" name="weight_value" step="0.01" value="<?= htmlspecialchars($product['weight_value'] ?? '') ?>" placeholder="250" class="form-control-dark" style="width:100%;"></div>
                <div><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Satuan Ukuran</label><div id="editWeightUnitSB"></div></div>
            </div>
        </div>

        <!-- Packaging Levels -->
        <div class="section-title" style="margin-top:20px;margin-bottom:8px;"><i class="bi bi-layers" style="color:var(--info);"></i> Level Kemasan &amp; Harga</div>
        <p style="font-size:var(--font-size-xs);color:var(--info);margin-bottom:8px;"><i class="bi bi-calculator"></i> Harga otomatis dikalikan isi kemasan.</p>
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
            <button type="button" class="btn-outline-custom" style="flex:1;min-width:140px;font-size:var(--font-size-xs);padding:8px;" onclick="BarcodeUtil.generateAllEmpty('input.barcode-field')"><i class="bi bi-magic"></i> Generate Semua Barcode</button>
            <button type="button" class="btn-outline-custom" style="flex:1;min-width:140px;font-size:var(--font-size-xs);padding:8px;" onclick="printAllBarcodesEdit()"><i class="bi bi-printer"></i> Cetak Semua Barcode</button>
        </div>
        <div id="editPkgContainer"></div>
        <button type="button" id="btnAddEditLevel" onclick="addEditLevel()" class="btn-outline-custom" style="width:100%;margin-bottom:16px;border-style:dashed;"><i class="bi bi-plus-circle"></i> Tambah Level Kemasan</button>

        <!-- Preview (Point 6: auto-label) -->
        <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:16px;border:1px solid var(--border-color);">
            <div class="section-title" style="margin-bottom:8px;">Preview Nama Produk</div>
            <div id="editNamePreview" style="font-size:var(--font-size-sm);color:var(--text-secondary);font-weight:600;margin-bottom:12px;"><?= htmlspecialchars($product['full_name']) ?></div>
            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Label Struk & Rak (Bisa diubah manual)</label>
            <input type="text" id="editShortLabel" name="short_label" class="form-control-dark" style="width:100%;font-size:var(--font-size-sm);font-weight:600;color:var(--info);" maxlength="35" placeholder="Maks 35 Karakter" value="<?= htmlspecialchars($product['short_label'] ?? '') ?>">
        </div>

        <button type="submit" id="btnEditSubmit" class="btn-primary-custom" style="width:100%;padding:14px;cursor:pointer;"><i class="bi bi-check-circle"></i> Simpan Perubahan</button>
    </form>
</div>

<script>
const editUnitsData   = <?= $unitsJson ?>;
const editBrandsData  = <?= $brandsJson ?>;
const editCatsData    = <?= $catsJson ?>;
const editPkgsData    = <?= $pkgsJson ?>;
const editProductId   = <?= $product['id'] ?>;
const editCsrfToken   = document.getElementById('csrfToken').value;
const editCurBrand    = '<?= $product['brand_id'] ?? '' ?>';
const editCurCat      = '<?= $product['category_id'] ?? '' ?>';
const editCurWUnit    = '<?= $product['weight_unit'] ?? '' ?>';
const weightUnitOpts = [
    <?php foreach ($units as $u): ?>
        <?php $wLabel = htmlspecialchars($u['name'], ENT_QUOTES) . (!empty($u['abbreviation']) ? ' (' . htmlspecialchars($u['abbreviation'], ENT_QUOTES) . ')' : ''); ?>
        { value: '<?= htmlspecialchars($u['abbreviation'] ?: $u['name'], ENT_QUOTES) ?>', label: '<?= $wLabel ?>' },
    <?php endforeach; ?>
];

let editBrandSB, editCatSB, editWeightUnitSB;
let editLevels = [];
let editNextTempId = 1;
let deletedPkgIds = [];
let editIsMultivariant = true;
let editIsLabelEdited = false;
let editReferenceMode = false;
let editReferenceProductData = null;
let editRefSearchTimer = null;

document.addEventListener('DOMContentLoaded', () => {
    // Point 1: Brand SearchBox with instant add
    editBrandSB = new SearchBox(document.getElementById('editBrandSB'), {
        options: editBrandsData, placeholder:'Pilih brand...', icon:'bi-tag', name:'brand_id',
        value: editCurBrand, required:true,
        addLabel:'Tambah Brand Baru', onAdd: () => openEditMasterModal('brand'),
        onChange: updateEditPreview,
        linkUrl: BASE_URL + 'settings/master-data',
        linkLabel: 'Buka Master Data'
    });
    editCatSB = new SearchBox(document.getElementById('editCatSB'), {
        options: editCatsData, placeholder:'Pilih kategori...', icon:'bi-grid', name:'category_id',
        value: editCurCat, required:true,
        addLabel:'Tambah Kategori Baru', onAdd: () => openEditMasterModal('category'),
        onChange: updateEditPreview,
        linkUrl: BASE_URL + 'settings/master-data',
        linkLabel: 'Buka Master Data'
    });
    editWeightUnitSB = new SearchBox(document.getElementById('editWeightUnitSB'), {
        options: weightUnitOpts, placeholder:'Pilih...', name:'weight_unit',
        value: editCurWUnit, onChange: updateEditPreview,
        addLabel: 'Tambah Satuan Baru', onAdd: () => openEditUnitModal(),
        linkUrl: BASE_URL + 'settings/master-data', linkLabel: 'Buka Master Data'
    });

    // Render existing packagings
    const sorted = [...editPkgsData].sort((a,b) => a.level - b.level);
    sorted.forEach(pk => renderEditLevel({
        pkgId: pk.id, unitId: String(pk.unit_id), unitName: pk.unit_name,
        containedQty: pk.contained_qty, baseQty: pk.base_qty, barcode: pk.barcode || '',
        buyPrice: pk.buy_price, retail: pk.sell_price_retail, wholesale: pk.sell_price_wholesale,
        qtyPrices: pk.qty_prices || [],
    }));

    if (typeof PackagingPriceSync !== 'undefined') PackagingPriceSync.init();

    document.querySelectorAll('[name="product_type"],[name="variant"],[name="weight_value"],[name="single_name"]').forEach(el => {
        el.addEventListener('input', updateEditPreview);
    });

    // Point 6: Track manual label edits
    document.getElementById('editShortLabel')?.addEventListener('input', () => { editIsLabelEdited = true; });

    // Point 5: Multivarian toggle
    const mvCheck = document.getElementById('editIsMultivariant');
    editIsMultivariant = mvCheck?.checked ?? true;
    if (!editIsMultivariant) toggleEditMultivariant(false);
    mvCheck?.addEventListener('change', (e) => { toggleEditMultivariant(e.target.checked); });

    // Point 4: Reference mode toggle
    document.getElementById('editUseReferenceMode')?.addEventListener('change', (e) => {
        editReferenceMode = e.target.checked;
        document.getElementById('editReferencePanel').style.display = editReferenceMode ? 'block' : 'none';
        document.getElementById('editVariantHint').style.display = editReferenceMode ? 'block' : 'none';
        document.getElementById('editVariantRequiredMark').style.display = editReferenceMode ? 'inline' : 'none';
        if (!editReferenceMode) clearEditReference();
    });
    document.getElementById('editReferenceSearch')?.addEventListener('input', (e) => {
        clearTimeout(editRefSearchTimer);
        const q = e.target.value.trim();
        if (q.length < 2) { document.getElementById('editReferenceResults').style.display = 'none'; return; }
        editRefSearchTimer = setTimeout(() => searchEditReference(q), 300);
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#editReferenceSearch') && !e.target.closest('#editReferenceResults'))
            document.getElementById('editReferenceResults').style.display = 'none';
    });
});

function toggleEditMultivariant(isMulti) {
    editIsMultivariant = isMulti;
    document.getElementById('editMultiVariantPanel').style.display = isMulti ? 'block' : 'none';
    document.getElementById('editSingleVariantPanel').style.display = isMulti ? 'none' : 'block';
    if (isMulti) {
        document.getElementById('editSingleNameInput').required = false;
        if (editBrandSB) editBrandSB.setRequired(true);
    } else {
        document.getElementById('editSingleNameInput').required = true;
        if (editBrandSB) editBrandSB.setRequired(false);
    }
    updateEditPreview();
}

// Point 1: Add brand/category instantly
async function openEditMasterModal(type) {
    const cfg = type === 'brand' ? {
        title:'Tambah Brand Baru', icon:'bi-tag', endpoint:`${BASE_URL}api/brands`,
        label:'Nama Brand', placeholder:'Cth: Indomie, Aqua...'
    } : {
        title:'Tambah Kategori Baru', icon:'bi-grid', endpoint:`${BASE_URL}api/categories`,
        label:'Nama Kategori', placeholder:'Cth: Makanan Ringan, Minuman...'
    };
    await AppModal.show({
        title: cfg.title, icon: cfg.icon,
        bodyHTML: `<div class="modal-form-group"><label>${cfg.label} *</label><input type="text" class="form-control-dark" id="editModalMasterName" placeholder="${cfg.placeholder}" required></div>`,
        submitText:'Simpan',
        onSubmit: async () => {
            const name = document.getElementById('editModalMasterName').value.trim();
            if (!name) { showToast(`${cfg.label} wajib diisi`,'warning'); return false; }
            const res = await fetch(cfg.endpoint, {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':editCsrfToken},body:JSON.stringify({csrf_token:editCsrfToken,name})});
            const data = await res.json();
            if (data.error) { showToast(data.error,'error'); return false; }
            if (data.success) {
                if (type === 'brand') { editBrandsData.push({value:String(data.id),label:data.name}); editBrandSB.addOption(data.id, data.name, true); }
                else { editCatsData.push({value:String(data.id),label:data.name}); editCatSB.addOption(data.id, data.name, true); }
                showToast(`"${data.name}" berhasil ditambahkan!`,'success');
                return data;
            }
            return false;
        }
    });
}

// Point 4: Reference search
async function searchEditReference(q) {
    const box = document.getElementById('editReferenceResults');
    try {
        const res = await fetch(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`);
        const items = await res.json();
        if (!items.length) { box.innerHTML = '<div style="padding:12px;font-size:12px;color:var(--text-muted);">Tidak ditemukan</div>'; box.style.display = 'block'; return; }
        box.innerHTML = items.map(p => `<button type="button" onclick="selectEditReference(${p.id})" style="display:block;width:100%;text-align:left;padding:10px 12px;border:none;background:transparent;color:var(--text-primary);font-size:12px;cursor:pointer;border-bottom:1px solid var(--border-color);"><strong>${escHtml(p.short_label || p.full_name)}</strong></button>`).join('');
        box.style.display = 'block';
    } catch(e) { showToast('Gagal mencari produk','error'); }
}
async function selectEditReference(id) {
    document.getElementById('editReferenceResults').style.display = 'none';
    document.getElementById('editReferenceSearch').value = '';
    try {
        const product = await api(`${BASE_URL}api/products/${id}`);
        editReferenceProductData = product;
        document.getElementById('editReferenceProductId').value = product.id;
        document.getElementById('editReferenceSelectedName').textContent = product.short_label || product.full_name;
        document.getElementById('editReferenceSelected').style.display = 'block';
        // Fill identity fields from reference
        if (product.brand_id) editBrandSB.select(String(product.brand_id), product.brand_name || '');
        if (product.category_id) editCatSB.select(String(product.category_id), product.category_name || '');
        document.getElementById('editProductTypeInput').value = product.product_type || '';
        document.querySelector('[name="weight_value"]').value = product.weight_value || '';
        if (product.weight_unit) editWeightUnitSB.select(product.weight_unit, product.weight_unit);
        // Clear variant and barcode - user must fill these
        document.getElementById('editVariantInput').value = '';
        // Rebuild packagings from reference (same structure, clear barcodes)
        rebuildEditPackagingsFromRef(product.packagings || []);
        applyEditReferenceLock();
        document.getElementById('editVariantInput').focus();
        updateEditPreview();
        showToast('Data referensi dimuat. Isi varian & barcode baru.','success');
    } catch(e) { showToast('Gagal memuat produk referensi','error'); }
}
function clearEditReference() {
    editReferenceProductData = null;
    document.getElementById('editReferenceProductId').value = '';
    document.getElementById('editReferenceSelected').style.display = 'none';
    applyEditReferenceLock();
}
function rebuildEditPackagingsFromRef(packagings) {
    const container = document.getElementById('editPkgContainer');
    container.innerHTML = '';
    editLevels = [];
    editNextTempId = 1;
    const sorted = [...packagings].sort((a,b) => a.level - b.level);
    sorted.forEach(pk => renderEditLevel({
        pkgId: null, unitId: String(pk.unit_id), unitName: pk.unit_name,
        containedQty: pk.contained_qty, barcode: '',
        buyPrice: pk.buy_price, retail: pk.sell_price_retail, wholesale: pk.sell_price_wholesale,
        qtyPrices: [],
    }));
    if (sorted.length === 0) renderEditLevel();
}
function applyEditReferenceLock() {
    const locked = editReferenceMode && editReferenceProductData;
    // Lock identity fields except variant
    const identity = document.getElementById('editIdentitySection');
    if (identity) {
        identity.querySelectorAll('input, .searchbox-wrapper').forEach(el => {
            if (el.name === 'variant' || el.id === 'editVariantInput') return;
            el.style.pointerEvents = locked ? 'none' : '';
            el.style.opacity = locked ? '0.65' : '';
        });
        const variantInput = document.getElementById('editVariantInput');
        if (variantInput) { variantInput.style.pointerEvents = ''; variantInput.style.opacity = '1'; }
    }
    // Lock packaging fields except barcode
    document.querySelectorAll('#editPkgContainer .packaging-level').forEach(lv => {
        lv.querySelectorAll('input, .searchbox-wrapper').forEach(el => {
            const isBarcode = el.classList.contains('barcode-field');
            el.style.pointerEvents = locked && !isBarcode ? 'none' : '';
            el.style.opacity = locked && !isBarcode ? '0.65' : '';
        });
        const removeBtn = lv.querySelector('button[onclick*="removeEditLevel"]');
        if (removeBtn) removeBtn.style.display = locked ? 'none' : '';
    });
    const addBtn = document.getElementById('btnAddEditLevel');
    if (addBtn) addBtn.style.display = locked ? 'none' : '';
}

function renderEditLevel(prefill = null) {
    const container = document.getElementById('editPkgContainer');
    const isLevel1 = (editLevels.length === 0);
    const levelNum = editLevels.length + 1;
    const tempId = editNextTempId++;

    const div = document.createElement('div');
    div.className = 'packaging-level';
    div.setAttribute('data-temp-id', tempId);
    div.setAttribute('data-level', levelNum);
    if (prefill?.pkgId) div.setAttribute('data-pkg-id', prefill.pkgId);
    div.style.cssText = 'background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--border-color);position:relative;';

    const levelLabels = {
        1: 'Level 1 — Satuan Terkecil (Eceran)',
        2: 'Level 2 — Kemasan Tambahan (Bebas/Opsional)',
        3: 'Level 3 — Kemasan Tambahan (Bebas/Opsional)'
    };
    const title = levelLabels[levelNum] || `Level ${levelNum} — Kemasan Tambahan (Opsional)`;

    const removeBtn = !isLevel1
        ? `<button type="button" onclick="removeEditLevel(this)" style="position:absolute;top:12px;right:16px;background:none;border:none;color:var(--danger);font-size:1.2rem;cursor:pointer;"><i class="bi bi-x-circle-fill"></i></button>`
        : '';

    let isBuyCustom = false;
    let isSellCustom = false;

    if (!isLevel1 && prefill) {
        try {
            const l1 = editLevels[0].domEl;
            const l1Buy = parseFloat(l1.querySelector('.pkg-buy').value) || 0;
            const l1Retail = parseFloat(l1.querySelector('.pkg-retail').value) || 0;
            const l1Wholesale = parseFloat(l1.querySelector('.pkg-wholesale').value) || 0;
            const bQty = parseInt(prefill.baseQty) || 1;
            
            const expBuy = l1Buy * bQty;
            const expRetail = l1Retail * bQty;
            const expWholesale = l1Wholesale * bQty;
            
            if (prefill.buyPrice > 0 && Math.abs(prefill.buyPrice - expBuy) > 0.01) isBuyCustom = true;
            if ((prefill.retail > 0 && Math.abs(prefill.retail - expRetail) > 0.01) || 
                (prefill.wholesale > 0 && Math.abs(prefill.wholesale - expWholesale) > 0.01)) isSellCustom = true;
        } catch (e) {}
    }

    const containedHtml = isLevel1
        ? `<div style="flex:1;"><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Isi</label><input type="number" value="1" class="form-control-dark" style="width:100%;background:var(--surface-2);color:var(--text-muted);" readonly></div>`
        : `<div style="flex:1;"><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Isi per kemasan *</label><input type="number" class="form-control-dark contained-qty" placeholder="Cth: 10,12,24" style="width:100%;" min="2" required value="${prefill?.containedQty||''}"></div>`;

    div.innerHTML = `
        ${removeBtn}
        <div class="section-title" style="margin-bottom:12px;color:var(--primary);">${title}</div>
        <div style="display:flex;gap:8px;margin-bottom:12px;">
            <div style="flex:2;"><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:4px;align-items:center;"><span>Satuan *</span><a href="${BASE_URL}settings/master-data" target="_blank" style="font-size:10px;color:var(--primary);text-decoration:none;"><i class="bi bi-box-arrow-up-right"></i> Master Data</a></label><div class="unit-searchbox-instance" data-level="${levelNum}"></div></div>
            ${containedHtml}
        </div>
        <div style="margin-bottom:12px;" class="pkg-barcode-row">
            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Barcode</label>
            <div style="background:var(--bg-input);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:0 8px 0 12px;display:flex;align-items:center;gap:4px;">
                <i class="bi bi-upc-scan" style="color:var(--primary);flex-shrink:0;cursor:pointer;padding:8px 4px;font-size:1.1rem;" onclick="BarcodeUtil.scanBarcode(this.nextElementSibling)"></i>
                <input type="text" class="barcode-field" value="${escHtml(prefill?.barcode||'')}" placeholder="Scan, ketik, atau generate..." style="flex:1;border:none;background:transparent;padding:12px 6px;color:var(--text-primary);font-size:var(--font-size-base);outline:none;font-family:var(--font-family);min-width:0;">
                <button type="button" class="btn-scan-bc" style="border:none;background:var(--info-bg);color:var(--info);padding:6px 10px;border-radius:6px;font-size:11px;cursor:pointer;"><i class="bi bi-camera"></i></button>
                <button type="button" class="btn-gen-bc" style="border:none;background:var(--primary-bg);color:var(--primary);padding:6px 10px;border-radius:6px;font-size:11px;cursor:pointer;"><i class="bi bi-magic"></i></button>
                <button type="button" class="btn-print-bc" style="border:none;background:var(--surface-2);color:var(--text-secondary);padding:6px 10px;border-radius:6px;font-size:11px;cursor:pointer;"><i class="bi bi-printer"></i></button>
            </div>
        </div>
        <div style="background:rgba(0,0,0,0.15);padding:12px;border-radius:var(--radius-sm);border:1px solid rgba(255,255,255,0.05);">
            ${!isLevel1 ? `<label class="price-custom-toggle buy-custom-toggle ${isBuyCustom ? 'active' : ''}"><input type="checkbox" class="chk-buy-custom" ${isBuyCustom ? 'checked' : ''}><i class="bi bi-pencil-square" style="font-size:10px;"></i> Harga Modal Custom</label>` : ''}
            <div style="margin-bottom:8px;position:relative;">
                <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:4px;align-items:center;">
                    <span>Harga Modal / Beli</span>
                    <span style="color:var(--primary);cursor:pointer;background:var(--surface-2);padding:2px 6px;border-radius:4px;font-size:10px;" onclick="const b=this.nextElementSibling;b.style.display=b.style.display==='none'?'flex':'none'"><i class="bi bi-calculator"></i> Kalkulator</span>
                    <div style="display:none;position:absolute;top:24px;right:0;background:var(--surface-2);padding:8px;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.8);z-index:10;gap:4px;align-items:center;border:1px solid var(--border-color);">
                        <input type="number" placeholder="Total Rp" class="form-control-dark calc-total" style="width:90px;font-size:12px;padding:4px;">
                        <span style="color:var(--text-muted);">/</span>
                        <input type="number" placeholder="Qty" value="1" class="form-control-dark calc-qty" style="width:50px;font-size:12px;padding:4px;">
                        <button type="button" class="btn-primary-custom" style="padding:4px 8px;font-size:12px;border-radius:4px;" onclick="const p=this.parentElement;const t=p.querySelector('.calc-total').value;const q=p.querySelector('.calc-qty').value;if(t&&q>0){const inp=p.closest('div[style*=&quot;margin-bottom:8px&quot;]').querySelector('.pkg-buy,.buy-price');inp.value=Math.round(t/q);inp.dispatchEvent(new Event('input'));p.style.display='none';}"><i class="bi bi-check2"></i> Hitung</button>
                    </div>
                </label>
                <input type="number" value="${prefill?.buyPrice||0}" placeholder="0" class="form-control-dark pkg-buy" style="width:100%;">
                ${!isLevel1 ? `<div class="price-locked-note buy-locked-note ${isBuyCustom ? '' : 'visible'}"><i class="bi bi-link-45deg"></i> Otomatis dihitung dari harga pcs × isi</div>` : ''}
            </div>
            ${!isLevel1 ? `<label class="price-custom-toggle sell-custom-toggle ${isSellCustom ? 'active' : ''}"><input type="checkbox" class="chk-sell-custom" ${isSellCustom ? 'checked' : ''}><i class="bi bi-tag" style="font-size:10px;"></i> Harga Jual Custom</label>` : ''}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Jual Ecer/Retail</label><input type="number" value="${prefill?.retail||0}" placeholder="0" class="form-control-dark pkg-retail" style="width:100%;"></div>
                <div><label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Jual Grosir</label><input type="number" value="${prefill?.wholesale||0}" placeholder="0" class="form-control-dark pkg-wholesale" style="width:100%;"></div>
            </div>
            ${!isLevel1 ? `<div class="price-locked-note sell-locked-note ${isSellCustom ? '' : 'visible'}"><i class="bi bi-link-45deg"></i> Otomatis dihitung dari harga pcs × isi</div>` : ''}
            <div class="pkg-margin-info" style="margin-top:8px;font-size:11px;color:var(--text-muted);display:flex;justify-content:space-between;">
                <span class="margin-retail-text">Margin Retail: 0%</span>
                <span class="margin-wholesale-text">Margin Grosir: 0%</span>
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

    const unitContainer = div.querySelector('.unit-searchbox-instance');
    const unitSB = new SearchBox(unitContainer, {
        options: [...editUnitsData], placeholder:'Pilih satuan...', name:'unit_id[]', required:true,
        addLabel:'Tambah Satuan Baru', onAdd:() => openEditUnitModal(),
        onChange: updateEditPreview,
        linkUrl: BASE_URL + 'settings/master-data',
        linkLabel: 'Buka Master Data'
    });
    unitContainer._searchbox = unitSB;
    if (prefill?.unitId) unitSB.select(prefill.unitId, prefill.unitName || '');

    const record = { tempId, pkgId: prefill?.pkgId || null, sbRef: unitSB, domEl: div };
    editLevels.push(record);

    // Barcode buttons
    const bcInput = div.querySelector('.barcode-field');
    div.querySelector('.btn-scan-bc')?.addEventListener('click', () => BarcodeUtil.scanBarcode(bcInput));
    div.querySelector('.btn-gen-bc')?.addEventListener('click', () => BarcodeUtil.fillInput(bcInput, div.querySelector('.btn-gen-bc')));
    div.querySelector('.btn-print-bc')?.addEventListener('click', () => {
        const unit = unitSB.getLabel() || '';
        BarcodeUtil.printFromInput(bcInput, document.getElementById('editNamePreview').textContent, unit ? `1 ${unit}` : '');
    });

    if (typeof PackagingPriceSync !== 'undefined') PackagingPriceSync.bindNewLevel(div);
    initQtyTiers(div, prefill?.qtyPrices || []);

    // Listen for contained qty changes to update naming
    const cqtyInput = div.querySelector('.contained-qty');
    if (cqtyInput) {
        cqtyInput.addEventListener('input', () => updateEditPreview());
    }

    updateEditPreview();
}

const QTY_MODE_OPTS = [
    { v: 'both', l: 'Ecer & Grosir' },
    { v: 'retail', l: 'Ecer saja' },
    { v: 'wholesale', l: 'Grosir saja' },
];

function addQtyTierRow(listEl, data = {}) {
    const row = document.createElement('div');
    row.className = 'qty-tier-row';
    row.style.cssText = 'display:grid;grid-template-columns:minmax(56px,0.8fr) minmax(80px,1fr) minmax(90px,1fr) auto;gap:6px;align-items:end;margin-bottom:6px;';
    const mode = data.sale_mode || 'both';
    const modeOpts = QTY_MODE_OPTS.map(o => `<option value="${o.v}" ${mode === o.v ? 'selected' : ''}>${o.l}</option>`).join('');
    
    const minQty = data.min_qty || '';
    const unitPrice = data.unit_price || '';
    const totalPrice = (minQty && unitPrice) ? Math.round(minQty * unitPrice) : '';

    row.innerHTML = `
        <div><label style="font-size:9px;color:var(--text-muted);">Untuk Qty</label>
            <input type="number" class="form-control-dark tier-min-qty" min="1" step="1" value="${minQty}" placeholder="1" style="width:100%;padding:6px;font-size:12px;" oninput="this.closest('.qty-tier-row').querySelector('.tier-total-price').dispatchEvent(new Event('input'))"></div>
        <div><label style="font-size:9px;color:var(--text-muted);">Total Harga</label>
            <input type="number" class="form-control-dark tier-total-price" min="0" step="any" value="${totalPrice}" placeholder="10000" style="width:100%;padding:6px;font-size:12px;" oninput="const r=this.closest('.qty-tier-row'); const q=r.querySelector('.tier-min-qty').value; if(q>0) r.querySelector('.tier-unit-price').value=(this.value/q).toFixed(2);">
            <input type="hidden" class="tier-unit-price" value="${unitPrice}">
        </div>
        <div><label style="font-size:9px;color:var(--text-muted);">Mode</label>
            <select class="form-control-dark tier-sale-mode" style="width:100%;padding:6px;font-size:11px;">${modeOpts}</select></div>
        <button type="button" title="Hapus tier" style="border:none;background:var(--danger-bg);color:var(--danger);padding:8px;border-radius:6px;cursor:pointer;margin-bottom:2px;" onclick="this.closest('.qty-tier-row').remove()"><i class="bi bi-trash"></i></button>
        <div style="grid-column:1/-1;"><label style="font-size:9px;color:var(--text-muted);">Label (opsional)</label>
            <input type="text" class="form-control-dark tier-label" value="${escHtml(data.label || '')}" placeholder="Cth: 3 renceng = Rp 10.000" style="width:100%;padding:6px;font-size:11px;"></div>
    `;
    listEl.appendChild(row);
}

function initQtyTiers(levelDiv, tiers = []) {
    const list = levelDiv.querySelector('.qty-tiers-list');
    const btn = levelDiv.querySelector('.btn-add-qty-tier');
    if (!list || !btn) return;
    list.innerHTML = '';
    tiers.forEach(t => addQtyTierRow(list, t));
    btn.onclick = () => addQtyTierRow(list);
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

async function saveQtyTiersForPackaging(pkgId, levelDiv) {
    const tiers = collectQtyTiers(levelDiv);
    const res = await api(`${BASE_URL}api/products/packaging/${pkgId}/qty-prices`, 'POST', {
        csrf_token: editCsrfToken,
        tiers,
    });
    if (res.error) throw new Error(res.error);
    return res;
}

function addEditLevel() { renderEditLevel(); }

function removeEditLevel(btn) {
    const div = btn.closest('.packaging-level');
    const pkgId = div.getAttribute('data-pkg-id');
    if (pkgId) deletedPkgIds.push(pkgId);
    const tempId = parseInt(div.getAttribute('data-temp-id'));
    editLevels = editLevels.filter(l => l.tempId !== tempId);
    div.remove();
    // Renumber
    document.querySelectorAll('#editPkgContainer .packaging-level').forEach((el, i) => el.setAttribute('data-level', i + 1));
    updateEditPreview();
}

async function openEditUnitModal() {
    const result = await AppModal.show({
        title:'Tambah Satuan Baru', icon:'bi-rulers',
        bodyHTML:`<div class="modal-form-group"><label>Nama Satuan *</label><input type="text" class="form-control-dark" id="editModalUnitName" placeholder="Cth: Karton, Renteng..." required></div>`,
        submitText:'Simpan',
        onSubmit: async () => {
            const name = document.getElementById('editModalUnitName').value.trim();
            if (!name) { showToast('Nama satuan wajib diisi','warning'); return false; }
            const res = await fetch(`${BASE_URL}api/units`, {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':editCsrfToken},body:JSON.stringify({csrf_token:editCsrfToken,name})});
            const data = await res.json();
            if (data.error) { showToast(data.error,'error'); return false; }
            if (data.success) {
                editUnitsData.push({value:String(data.id),label:data.name});
                document.querySelectorAll('.unit-searchbox-instance').forEach(el => {
                    if (el._searchbox) el._searchbox.addOption(data.id, data.name, false);
                });
                
                // Add to weight unit searchbox
                const abbr = data.abbreviation || data.name;
                const wLabel = data.abbreviation ? `${data.name} (${data.abbreviation})` : data.name;
                weightUnitOpts.push({ value: abbr, label: wLabel });
                if (editWeightUnitSB) editWeightUnitSB.addOption(abbr, wLabel, false);
                
                showToast(`Satuan "${data.name}" ditambahkan!`,'success');
                return data;
            }
            return false;
        }
    });
}

function escHtml(str) {
    const d = document.createElement('div'); d.textContent = str||''; return d.innerHTML;
}

function printAllBarcodesEdit() {
    BarcodeUtil.printAllFilled('input.barcode-field', (input) => ({
        title: document.getElementById('editNamePreview')?.textContent?.trim() || 'Produk',
        subtitle: input.dataset.unit ? `1 ${input.dataset.unit}` : '',
    }));
}

function updateEditPreview() {
    const isMulti = document.getElementById('editIsMultivariant')?.checked;
    let baseName = '';
    let shortBaseName = '';

    if (isMulti) {
        const brand  = editBrandSB ? editBrandSB.getLabel() : '';
        const type   = document.querySelector('[name="product_type"]')?.value?.trim() || '';
        const variant= document.querySelector('[name="variant"]')?.value?.trim() || '';
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
    if (rawWeight !== '') formattedWeight = parseFloat(rawWeight).toString();
    const wUnit = editWeightUnitSB ? editWeightUnitSB.getValue() : '';

    let fullName = baseName;
    let shortLabel = shortBaseName;

    // Point 7: Build packaging multiplier chain for all levels
    const levels = document.querySelectorAll('#editPkgContainer .packaging-level');
    const qtyChain = [];
    if (levels.length > 1) {
        for (let i = levels.length - 1; i >= 1; i--) {
            const qty = parseInt(levels[i].querySelector('.contained-qty')?.value) || 0;
            if (qty > 0) qtyChain.push(qty);
        }
    }

    if (qtyChain.length > 0) {
        const chainStr = qtyChain.join(' x ');
        if (formattedWeight && wUnit) {
            fullName += ` (${chainStr} x ${formattedWeight}${wUnit})`;
            shortLabel += ` ${formattedWeight}${wUnit}`;
            formattedWeight = '';
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

    document.getElementById('editNamePreview').textContent = fullName || '-';

    // Point 6: Auto-update label unless manually edited
    if (!editIsLabelEdited) {
        document.getElementById('editShortLabel').value = shortLabel || '';
    }
}

async function updatePackagingWithConflict(pkgId, payload) {
    const resp = await fetch(`${BASE_URL}api/products/packaging/${pkgId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': editCsrfToken },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
    });
    const data = await resp.json();

    if (resp.status === 409 && data.error === 'barcode_conflict' && data.conflict) {
        const c = data.conflict;
        const userChoice = await new Promise(resolve => {
            AppModal.show({
                title: 'Barcode Duplikat Terdeteksi',
                subtitle: `Barcode: ${escHtml(payload.barcode)}`,
                icon: 'bi-exclamation-triangle',
                iconColor: 'var(--warning-bg)',
                iconAccent: 'var(--warning)',
                bodyHTML: `
                    <div style="background:var(--warning-bg);border:1px solid var(--warning);border-radius:var(--radius-md);padding:14px;margin-bottom:14px;">
                        <div style="display:flex;align-items:flex-start;gap:10px;">
                            <i class="bi bi-exclamation-triangle-fill" style="color:var(--warning);font-size:1.3rem;flex-shrink:0;margin-top:2px;"></i>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:700;font-size:var(--font-size-sm);color:var(--text-primary);margin-bottom:4px;">Barcode sudah digunakan!</div>
                                <div style="font-size:var(--font-size-xs);color:var(--text-secondary);line-height:1.5;">
                                    Barcode <strong style="font-family:monospace;">${escHtml(payload.barcode)}</strong> saat ini digunakan oleh:
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:14px;margin-bottom:14px;">
                        <div style="font-weight:700;font-size:var(--font-size-sm);color:var(--text-primary);margin-bottom:4px;">
                            <i class="bi bi-box-seam" style="color:var(--info);"></i> ${escHtml(c.product_name)}
                        </div>
                        <div style="font-size:var(--font-size-xs);color:var(--text-muted);">
                            Level ${c.level} · ${escHtml(c.unit_name || 'pcs')}
                        </div>
                    </div>
                    <div style="font-size:var(--font-size-xs);color:var(--text-muted);background:var(--surface-2);padding:10px 12px;border-radius:var(--radius-sm);">
                        <i class="bi bi-info-circle"></i> Jika di-replace, barcode di produk lama akan dihapus dan dipindahkan ke produk ini.
                    </div>
                `,
                submitText: '<i class="bi bi-arrow-repeat"></i> Replace Barcode',
                cancelText: 'Batal',
                onSubmit: async () => { resolve(true); return true; },
            }).then(() => {}).catch(() => resolve(false));
        });

        if (userChoice) {
            // Retry with force_replace
            const resp2 = await fetch(`${BASE_URL}api/products/packaging/${pkgId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': editCsrfToken },
                body: JSON.stringify({ ...payload, force_replace: true }),
                credentials: 'same-origin'
            });
            return await resp2.json();
        }
        return data; // Return original conflict (caller checks for barcode_conflict)
    }

    if (!resp.ok && data.error) {
        throw new Error(data.error);
    }
    return data;
}

async function submitEditProduct(e) {
    e.preventDefault();
    const btn = document.getElementById('btnEditSubmit');
    const prevText = btn.innerHTML;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
    btn.disabled = true;

    try {
        // 1. Update product basic info
        const isMulti = document.getElementById('editIsMultivariant')?.checked;
        const productData = {
            csrf_token: editCsrfToken,
            full_name: document.getElementById('editNamePreview').textContent,
            short_label: document.getElementById('editShortLabel')?.value?.trim() || '',
            invoice_name: document.getElementById('editShortLabel')?.value?.trim() || '',
            product_type: isMulti ? (document.querySelector('[name="product_type"]')?.value?.trim() || '') : '',
            variant: isMulti ? (document.querySelector('[name="variant"]')?.value?.trim() || '') : '',
            brand_id: isMulti ? editBrandSB.getValue() : '',
            category_id: editCatSB.getValue(),
            weight_value: document.querySelector('[name="weight_value"]').value,
            weight_unit: editWeightUnitSB.getValue(),
        };
        const res = await api(`${BASE_URL}api/products/update/${editProductId}`, 'POST', productData);
        if (!res.success) throw new Error(res.error || 'Gagal update produk');

        // 2. Delete removed packagings
        for (const pkgId of deletedPkgIds) {
            await api(`${BASE_URL}api/products/packaging/${pkgId}/delete`, 'POST', { csrf_token: editCsrfToken });
        }

        // 3. Update / create packagings
        const pkgDivs = document.querySelectorAll('#editPkgContainer .packaging-level');
        for (const div of pkgDivs) {
            let pkgId = div.getAttribute('data-pkg-id');
            const unitSB = div.querySelector('.unit-searchbox-instance')?._searchbox;
            const unitId = unitSB ? unitSB.getValue() : '';
            const containedQty = div.querySelector('.contained-qty')?.value || 1;
            const buyPrice = div.querySelector('.pkg-buy')?.value || 0;
            const retail   = div.querySelector('.pkg-retail')?.value || 0;
            const wholesale= div.querySelector('.pkg-wholesale')?.value || 0;
            const barcode  = div.querySelector('.barcode-field')?.value || '';

            if (pkgId) {
                const up = await updatePackagingWithConflict(pkgId, {
                    csrf_token: editCsrfToken, unit_id: unitId, buy_price: buyPrice,
                    sell_price_retail: retail, sell_price_wholesale: wholesale, barcode
                });
                if (up.error && up.error !== 'barcode_conflict') throw new Error(up.error || up.message);
            } else {
                const addRes = await api(`${BASE_URL}api/products/${editProductId}/packaging/add`, 'POST', {
                    csrf_token: editCsrfToken, unit_id: unitId, contained_qty: containedQty,
                    buy_price: buyPrice, sell_price_retail: retail, sell_price_wholesale: wholesale, barcode
                });
                if (!addRes.success) throw new Error(addRes.error || 'Gagal tambah kemasan');
                pkgId = String(addRes.id);
                div.setAttribute('data-pkg-id', pkgId);
            }
            await saveQtyTiersForPackaging(pkgId, div);
        }

        showToast('Produk berhasil diupdate!', 'success');
        setTimeout(() => window.location.href = `${BASE_URL}products/${editProductId}`, 1000);
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
        btn.innerHTML = prevText;
        btn.disabled = false;
    }
}
</script>
