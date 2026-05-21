<!-- Purchases Create View — Sales → Supplier (auto) → Product -->
<?php /** @var string $csrfToken */ ?>
<div class="page-section">
    <div style="margin-bottom:20px;">
        <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">Input Barang Masuk</h2>
        <p style="font-size:var(--font-size-sm); color:var(--text-muted);">Pilih sales, supplier terisi otomatis, lalu scan/cari produk</p>
    </div>
    
    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- Step 1: Supplier Selection -->
    <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:16px; margin-bottom:12px; border:1px solid var(--border-color);">
        <div class="section-title" style="margin-bottom:8px;">
            <i class="bi bi-1-circle" style="color:var(--primary);"></i> Sales & Supplier
        </div>
        <div style="margin-bottom:12px;">
            <label style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px; display:block;">Sales *</label>
            <div id="salesRepSearchBox"></div>
            <div id="salesRepInfo" style="margin-top:6px; font-size:11px; color:var(--text-muted);"></div>
        </div>

        <div id="supplierDisplaySection" style="margin-bottom:12px; display:none;">
            <label style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px; display:block;">Supplier (otomatis)</label>
            <div id="supplierDisplay" style="padding:10px 12px; background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); font-size:var(--font-size-sm); font-weight:600; color:var(--text-primary);">—</div>
        </div>

        <div style="display:flex; gap:8px;">
            <div style="flex:1;">
                <label style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px; display:block;">Tanggal *</label>
                <input type="date" id="purchaseDate" value="<?= date('Y-m-d') ?>" class="form-control-dark" style="width:100%;">
            </div>
            <div style="flex:1;">
                <label style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px; display:block;">Foto Invoice</label>
                <input type="file" id="invoicePhotoCam" accept="image/*" capture="environment" style="display:none;" onchange="handlePhotoSelect(event, true)">
                <input type="file" id="invoicePhotoGal" accept="image/*" style="display:none;" onchange="handlePhotoSelect(event, false)">
                <div style="display:flex; gap:4px;">
                    <button type="button" class="btn-outline-custom" id="btnPhotoCam" style="flex:1; padding:8px 4px; font-size:11px;" onclick="document.getElementById('invoicePhotoCam').click()">
                        <i class="bi bi-camera"></i> Kamera
                    </button>
                    <button type="button" class="btn-outline-custom" id="btnPhotoGal" style="flex:1; padding:8px 4px; font-size:11px;" onclick="document.getElementById('invoicePhotoGal').click()">
                        <i class="bi bi-image"></i> Galeri
                    </button>
                </div>
                <div style="display:flex; gap:4px; margin-top:4px;">
                    <button type="button" class="btn-primary-custom" id="btnScanAI" style="flex:1; padding:8px 4px; font-size:11px; display:none;" onclick="scanInvoiceWithAI()">
                        <i class="bi bi-robot"></i> Scan dengan AI (Otomatis)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Preview Modal -->
    <div id="photoPreviewModal" class="modal-backdrop" style="display:none; z-index:2000;">
        <div class="modal-content" style="max-width:400px; padding:0; overflow:hidden; display:flex; flex-direction:column; height:90vh;">
            <div style="padding:16px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:var(--font-size-md); margin:0;">Pratinjau Foto</h3>
                <button class="btn-close-custom" onclick="closePhotoPreview()"><i class="bi bi-x-lg"></i></button>
            </div>
            <div style="flex:1; overflow:hidden; background:#111; position:relative; display:flex; align-items:center; justify-content:center;">
                <canvas id="photoPreviewCanvas" style="max-width:100%; max-height:100%; object-fit:contain;"></canvas>
            </div>
            <div style="padding:16px; background:var(--surface-1);">
                <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px; cursor:pointer;">
                    <input type="checkbox" id="chkEnhancePhoto" checked onchange="applyPhotoFilter()" style="width:18px;height:18px;accent-color:var(--primary);">
                    <span style="font-size:13px; font-weight:600; color:var(--text-primary);">Mode Dokumen (Perjelas Teks)</span>
                </label>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="btn-outline-custom" style="flex:1;" onclick="closePhotoPreview()">Batal</button>
                    <button type="button" class="btn-primary-custom" style="flex:1;" onclick="savePhotoPreview()">Gunakan Foto</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Product Search (shown after supplier selected) -->
    <div id="productSearchSection" style="display:none;">
        <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:16px; margin-bottom:12px; border:1px solid var(--border-color);">
            <div class="section-title" style="margin-bottom:8px;">
                <i class="bi bi-2-circle" style="color:var(--primary);"></i> Cari Produk
            </div>
            <div id="supplierBadge" style="display:none; margin-bottom:8px;">
                <span class="badge-custom badge-info" style="font-size:11px;"></span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:var(--font-size-xs);">
                    <input type="checkbox" id="filterBySupplierSales" checked style="width:16px;height:16px;accent-color:var(--primary);">
                    <span>Filter produk berdasarkan supplier & sales</span>
                </label>
                <button type="button" class="btn-outline-custom" style="padding:4px 8px; font-size:10px;" onclick="openBulkInputModal()">
                    <i class="bi bi-list-check"></i> Input Bulk (Massal)
                </button>
            </div>
            <p id="filterHint" style="font-size:10px;color:var(--text-muted);margin:-6px 0 10px 24px;">Hanya tampilkan barang terkait supplier/sales terpilih</p>
            <div class="search-input-wrapper" style="position:relative;background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:0 12px; display:flex; align-items:center;">
                <i class="bi bi-upc-scan" style="color:var(--text-muted);cursor:pointer;" onclick="scanProductBarcode()" title="Scan Barcode"></i>
                <input type="text" id="productSearch" placeholder="Scan barcode atau ketik nama produk..." 
                       style="flex:1;border:none;background:transparent;padding:12px 10px;color:var(--text-primary);font-size:var(--font-size-base);outline:none;font-family:var(--font-family);" autocomplete="off">
            </div>
            <div id="productSuggestions" style="margin-top:8px;"></div>
        </div>
    </div>

    <!-- Items List -->
    <div class="section-title" style="display:flex; justify-content:space-between;">
        <span><i class="bi bi-3-circle" style="color:var(--primary);"></i> Daftar Barang</span>
        <span id="itemCountBadge" class="badge-custom badge-info">0 Item</span>
    </div>
    <div id="purchaseItems">
        <div class="empty-state" id="emptyPurchaseState" style="padding:24px;">
            <i class="bi bi-cart-plus" style="font-size:2rem;"></i>
            <p style="margin-top:8px;">Pilih sales, lalu cari produk untuk menambahkan ke daftar</p>
        </div>
    </div>

    <!-- Total -->
    <!-- Invoice Adjustments & Total -->
    <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:16px; margin-top:16px; border:1px solid var(--border-color);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid var(--border-color);">
            <span style="font-weight:600; color:var(--text-muted);">Subtotal Barang</span>
            <span id="purchaseSubtotal" style="font-weight:600;">Rp0</span>
        </div>
        
        <div style="display:flex; gap:12px; margin-bottom:12px;">
            <div style="flex:1;">
                <label style="font-size:10px; color:var(--text-muted); display:block; margin-bottom:4px;">Diskon Nota (Rp)</label>
                <input type="number" id="invoiceDiscount" class="form-control-dark" style="width:100%; font-size:13px;" value="0" min="0" oninput="calculateGrandTotal()">
            </div>
            <div style="flex:1;">
                <label style="font-size:10px; color:var(--text-muted); display:block; margin-bottom:4px;">PPN (%)</label>
                <input type="number" id="invoiceTax" class="form-control-dark" style="width:100%; font-size:13px;" value="0" min="0" max="100" oninput="calculateGrandTotal()">
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <span style="font-weight:700;">Grand Total</span>
            <span id="purchaseGrandTotal" style="font-size:var(--font-size-xl); font-weight:800; color:var(--success);">Rp0</span>
        </div>

        <button class="btn-outline-custom" style="width:100%; font-size:12px; padding:8px;" onclick="distributeAdjustments()">
            <i class="bi bi-calculator"></i> Distribusikan ke Harga Modal Barang
        </button>
        <div style="font-size:10px; color:var(--text-muted); text-align:center; margin-top:4px;">Menghitung ulang harga modal satuan berdasarkan Diskon & PPN</div>
    </div>

    <button id="btnSavePurchase" class="btn-primary-custom" style="width:100%; margin-top:16px; padding:14px; cursor:pointer;" onclick="submitPurchase()">
        <i class="bi bi-check-circle"></i> Simpan Pembelian
    </button>
</div>

<script>
// ===== Data from PHP =====
const suppliersData = [
    <?php foreach ($suppliers ?? [] as $s): ?>
        { value: '<?= $s['id'] ?>', label: '<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>' },
    <?php endforeach; ?>
];

const salesRepsLookup = {
    <?php foreach ($salesReps ?? [] as $sr): ?>
    '<?= (int)$sr['id'] ?>': {
        supplier_id: '<?= (int)($sr['supplier_id'] ?? 0) ?>',
        supplier_name: <?= json_encode($sr['supplier_name'] ?? '') ?>,
        name: <?= json_encode($sr['name'] ?? '') ?>,
        phone: <?= json_encode($sr['phone'] ?? '') ?>,
        visit_day: <?= json_encode($sr['visit_day'] ?? '') ?>
    },
    <?php endforeach; ?>
};

const salesRepsOptions = [
    { value: 'other', label: '📦 Other — belum tahu supplier/sales' },
    <?php foreach ($salesReps ?? [] as $sr): ?>
    {
        value: '<?= (int)$sr['id'] ?>',
        label: <?= json_encode(($sr['name'] ?? '') . (!empty($sr['supplier_name']) ? ' · ' . $sr['supplier_name'] : '')) ?>
    },
    <?php endforeach; ?>
];

const csrfVal = document.getElementById('csrfToken').value;
let salesRepSB;
let isOtherMode = false;
let currentSupplierId = null;
let currentSupplierName = '';
let currentSalesRepId = null;
let currentSalesRepName = '';
let currentSubtotal = 0;
let currentGrandTotal = 0;
let filterBySupplierSales = true;
let invoicePhotoBase64 = null;

let originalPhotoImg = null;

function handlePhotoSelect(e, isCamera) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Reset file inputs so same file can be selected again
    document.getElementById('invoicePhotoCam').value = '';
    document.getElementById('invoicePhotoGal').value = '';

    const reader = new FileReader();
    reader.onload = function(event) {
        originalPhotoImg = new Image();
        originalPhotoImg.onload = function() {
            document.getElementById('photoPreviewModal').style.display = 'flex';
            document.getElementById('chkEnhancePhoto').checked = true; // Default to document mode
            applyPhotoFilter();
        };
        originalPhotoImg.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

function closePhotoPreview() {
    document.getElementById('photoPreviewModal').style.display = 'none';
}

function applyPhotoFilter() {
    if (!originalPhotoImg) return;
    const canvas = document.getElementById('photoPreviewCanvas');
    const isEnhanced = document.getElementById('chkEnhancePhoto').checked;
    
    let width = originalPhotoImg.width;
    let height = originalPhotoImg.height;
    const max_size = 1200;
    
    if (width > height) {
        if (width > max_size) { height *= max_size / width; width = max_size; }
    } else {
        if (height > max_size) { width *= max_size / height; height = max_size; }
    }
    
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    
    if (isEnhanced) {
        // Document mode: Grayscale, high contrast, slightly increased brightness
        ctx.filter = 'grayscale(100%) contrast(150%) brightness(110%)';
    } else {
        ctx.filter = 'none';
    }
    
    ctx.drawImage(originalPhotoImg, 0, 0, width, height);
}

function savePhotoPreview() {
    const canvas = document.getElementById('photoPreviewCanvas');
    invoicePhotoBase64 = canvas.toDataURL('image/jpeg', 0.7);
    
    const btnCam = document.getElementById('btnPhotoCam');
    const btnGal = document.getElementById('btnPhotoGal');
    btnCam.className = 'btn-success-custom';
    btnGal.className = 'btn-success-custom';
    btnCam.style.flex = '1'; btnGal.style.flex = '1';
    btnCam.style.padding = '8px 4px'; btnGal.style.padding = '8px 4px';
    btnCam.style.fontSize = '11px'; btnGal.style.fontSize = '11px';
    btnCam.innerHTML = '<i class="bi bi-check2-circle"></i> OK';
    btnGal.innerHTML = '<i class="bi bi-check2-circle"></i> OK';
    
    document.getElementById('btnScanAI').style.display = 'block';
    
    closePhotoPreview();
    showToast('Foto berhasil disiapkan', 'success');
}

async function scanInvoiceWithAI() {
    if (!invoicePhotoBase64) {
        showToast('Pilih atau ambil foto invoice terlebih dahulu', 'error');
        return;
    }
    
    const btn = document.getElementById('btnScanAI');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Memproses AI...';
        
        const data = {
            csrf_token: csrfVal,
            image_base64: invoicePhotoBase64
        };
        
        const result = await api(BASE_URL + 'api/ai/scan-invoice', 'POST', data);
        
        if (result.success && result.data && result.data.length > 0) {
            showToast('AI berhasil memparsing ' + result.data.length + ' item', 'success');
            
            // Loop through results and add to bulk items
            for (const item of result.data) {
                if (item.is_matched && item.product_id) {
                    try {
                        const productData = await api(`${BASE_URL}api/products/${item.product_id}`);
                        // Set quantity and price based on AI output
                        if (productData) {
                            // Find base packaging
                            const basePkg = productData.packagings.find(p => p.level == 1) || productData.packagings[0];
                            if (basePkg) {
                                basePkg.buy_price = item.price;
                            }
                            addProductToCart(productData);
                            
                            // Immediately update the added item's quantity
                            const addedItem = purchaseItems[0]; // addProductToCart unshifts to the front
                            if (addedItem && addedItem.product_id == item.product_id) {
                                addedItem.quantity = item.qty;
                                addedItem.total = addedItem.quantity * addedItem.buy_price;
                            }
                        }
                    } catch(e) {
                        console.error('Failed to add AI mapped item', e);
                    }
                } else {
                    showToast('Item "' + item.original_name + '" tidak dikenali di database, silakan input manual.', 'warning');
                }
            }
            renderCart();
            calculateTotal();
        } else {
            showToast('AI tidak menemukan item yang valid', 'warning');
        }
    } catch (err) {
        console.error('Error scanning invoice:', err);
        showToast(err.message || 'Gagal memindai invoice dengan AI', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    salesRepSB = new SearchBox(document.getElementById('salesRepSearchBox'), {
        options: salesRepsOptions,
        placeholder: 'Cari atau pilih sales...',
        icon: 'bi-person-badge',
        name: 'sales_rep_id',
        required: true,
        clearable: true,
        addLabel: 'Tambah Sales Baru',
        onAdd: () => addSalesRepModal(),
        onChange: (val, label) => onSalesRepPicked(val, label),
        onClear: () => clearSalesRepSelection()
    });

    document.getElementById('filterBySupplierSales')?.addEventListener('change', (e) => {
        if (isOtherMode) {
            e.target.checked = false;
            filterBySupplierSales = false;
            updateFilterHint();
            return;
        }
        filterBySupplierSales = e.target.checked;
        updateFilterHint();
        const q = searchInput?.value?.trim() || '';
        if (q.length >= 2) searchInput.dispatchEvent(new Event('input'));
    });

    initPurchaseProductSearch();
});

function updateFilterHint() {
    const hint = document.getElementById('filterHint');
    if (!hint) return;
    if (isOtherMode) {
        hint.textContent = 'Mode Other: semua produk dapat dicari';
    } else if (filterBySupplierSales) {
        hint.textContent = 'Hanya tampilkan barang terkait supplier/sales terpilih';
    } else {
        hint.textContent = 'Semua produk akan muncul saat diketik namanya';
    }
}

function clearSalesRepSelection() {
    isOtherMode = false;
    currentSalesRepId = null;
    currentSupplierId = null;
    currentSalesRepName = '';
    currentSupplierName = '';
    filterBySupplierSales = true;

    document.getElementById('supplierDisplaySection').style.display = 'none';
    document.getElementById('productSearchSection').style.display = 'none';
    document.getElementById('supplierDisplay').textContent = '—';
    document.getElementById('salesRepInfo').textContent = '';

    const badge = document.getElementById('supplierBadge');
    if (badge) badge.style.display = 'none';

    const filterCb = document.getElementById('filterBySupplierSales');
    if (filterCb) {
        filterCb.checked = true;
        filterCb.disabled = false;
    }

    const productSearch = document.getElementById('productSearch');
    if (productSearch) productSearch.value = '';
    const suggestions = document.getElementById('productSuggestions');
    if (suggestions) suggestions.innerHTML = '';

    updateFilterHint();
}

function onSalesRepPicked(val, label) {
    document.getElementById('supplierDisplaySection').style.display = 'block';
    document.getElementById('productSearchSection').style.display = 'block';

    const badge = document.getElementById('supplierBadge');
    const filterCb = document.getElementById('filterBySupplierSales');

    if (val === 'other') {
        isOtherMode = true;
        currentSalesRepId = null;
        currentSupplierId = null;
        currentSalesRepName = 'Other';
        currentSupplierName = '';
        document.getElementById('supplierDisplay').textContent = 'Other — supplier/sales belum diketahui';
        document.getElementById('salesRepInfo').textContent = 'Barang tanpa supplier/sales terdaftar';
        if (badge) {
            badge.style.display = 'block';
            badge.querySelector('.badge-custom').textContent = '📦 Other';
        }
        if (filterCb) {
            filterCb.checked = false;
            filterBySupplierSales = false;
        }
    } else {
        isOtherMode = false;
        const sr = salesRepsLookup[val];
        if (!sr) return;
        currentSalesRepId = val;
        currentSalesRepName = sr.name || label;
        currentSupplierId = sr.supplier_id || null;
        currentSupplierName = sr.supplier_name || '—';
        document.getElementById('supplierDisplay').textContent = currentSupplierName;
        const infoParts = [];
        if (sr.visit_day) infoParts.push('Kunjungan: ' + sr.visit_day);
        if (sr.phone) infoParts.push(sr.phone);
        document.getElementById('salesRepInfo').textContent = infoParts.join(' · ') || '';
        if (badge) {
            badge.style.display = 'block';
            badge.querySelector('.badge-custom').textContent = `👤 ${sr.name} · 🏪 ${currentSupplierName}`;
        }
        if (filterCb) {
            filterCb.checked = true;
            filterBySupplierSales = true;
        }
    }

    updateFilterHint();
    document.getElementById('productSearch').value = '';
    document.getElementById('productSuggestions').innerHTML = '';
}

// ===== Add Supplier Modal =====
async function addSupplierModal() {
    await AppModal.show({
        title: 'Tambah Supplier Baru',
        subtitle: 'Masukkan data supplier',
        icon: 'bi-truck',
        iconColor: 'var(--success-bg)',
        iconAccent: 'var(--success)',
        bodyHTML: `
            <div class="modal-form-group">
                <label>Nama Supplier *</label>
                <input type="text" class="form-control-dark" id="modalSupplierName" placeholder="Cth: PT Indofood, Agen ABC..." autocomplete="off">
            </div>
            <div class="modal-form-group">
                <label>Catatan</label>
                <input type="text" class="form-control-dark" id="modalSupplierNotes" placeholder="Catatan tambahan..." autocomplete="off">
            </div>
        `,
        submitText: 'Simpan',
        onSubmit: async () => {
            const name = document.getElementById('modalSupplierName').value.trim();
            if (!name) { showToast('Nama supplier wajib diisi', 'warning'); return false; }
            const res = await fetch(`${BASE_URL}api/suppliers`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfVal },
                body: JSON.stringify({ name, notes: document.getElementById('modalSupplierNotes').value.trim(), csrf_token: csrfVal })
            });
            const data = await res.json();
            if (data.error) { showToast(data.error, 'error'); return false; }
            if (data.success) {
                suppliersData.push({ value: String(data.id), label: data.name });
                showToast(`Supplier "${data.name}" berhasil ditambahkan!`, 'success');
                return data;
            }
            return false;
        }
    });
}

// ===== Add Sales Rep Modal =====
async function addSalesRepModal() {
    const supplierOptions = suppliersData.map(s =>
        `<option value="${s.value}">${s.label}</option>`
    ).join('');

    await AppModal.show({
        title: 'Tambah Sales Baru',
        subtitle: 'Pilih supplier untuk sales ini',
        icon: 'bi-person-badge',
        iconColor: 'var(--info-bg)',
        iconAccent: 'var(--info)',
        bodyHTML: `
            <div class="modal-form-group">
                <label>Supplier *</label>
                <select class="form-control-dark" id="modalSalesSupplier" style="width:100%;">
                    <option value="">— Pilih supplier —</option>
                    ${supplierOptions}
                </select>
            </div>
            <div class="modal-form-group">
                <label>Nama Sales *</label>
                <input type="text" class="form-control-dark" id="modalSalesName" placeholder="Nama lengkap sales..." autocomplete="off">
            </div>
            <div class="modal-form-group">
                <label>No. HP / Kontak</label>
                <input type="text" class="form-control-dark" id="modalSalesPhone" placeholder="08xxxxxxxxxx" autocomplete="off">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <div class="modal-form-group">
                    <label>Hari Kunjungan</label>
                    <input type="text" class="form-control-dark" id="modalSalesVisit" placeholder="Cth: Senin" autocomplete="off">
                </div>
                <div class="modal-form-group">
                    <label>Hari Kirim</label>
                    <input type="text" class="form-control-dark" id="modalSalesDelivery" placeholder="Cth: Rabu" autocomplete="off">
                </div>
            </div>
        `,
        submitText: 'Simpan',
        onSubmit: async () => {
            const name = document.getElementById('modalSalesName').value.trim();
            const supplierId = document.getElementById('modalSalesSupplier').value;
            if (!supplierId) { showToast('Pilih supplier untuk sales ini', 'warning'); return false; }
            if (!name) { showToast('Nama sales wajib diisi', 'warning'); return false; }
            const res = await fetch(`${BASE_URL}api/sales-reps`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfVal },
                body: JSON.stringify({
                    name,
                    supplier_id: supplierId,
                    phone: document.getElementById('modalSalesPhone').value.trim(),
                    visit_day: document.getElementById('modalSalesVisit').value.trim(),
                    delivery_day: document.getElementById('modalSalesDelivery').value.trim(),
                    csrf_token: csrfVal
                })
            });
            const data = await res.json();
            if (data.error) { showToast(data.error, 'error'); return false; }
            if (data.success) {
                const sr = data.sales_rep || {};
                const supName = sr.supplier_name || '';
                const label = data.name + (supName ? ' · ' + supName : '');
                salesRepsLookup[String(data.id)] = {
                    supplier_id: String(supplierId),
                    supplier_name: supName,
                    name: data.name,
                    phone: sr.phone || '',
                    visit_day: sr.visit_day || ''
                };
                salesRepSB.addOption(data.id, label, true);
                showToast(`Sales "${data.name}" berhasil ditambahkan!`, 'success');
                return data;
            }
            return false;
        }
    });
}

// ===== Product Search with Supplier Filter =====
let purchaseItems = [];
const searchInput = document.getElementById('productSearch');
const suggestionsDiv = document.getElementById('productSuggestions');
const itemsContainer = document.getElementById('purchaseItems');
const totalEl = document.getElementById('purchaseTotal');
const emptyState = document.getElementById('emptyPurchaseState');
const countBadge = document.getElementById('itemCountBadge');

function initPurchaseProductSearch() {
    if (!searchInput || !suggestionsDiv) return;
    const runSearch = typeof debounce === 'function'
        ? debounce(performProductSearch, 300)
        : performProductSearch;
    searchInput.addEventListener('input', () => runSearch());
}

function scanProductBarcode() {
    if (typeof BarcodeUtil !== 'undefined' && BarcodeUtil.scanBarcode) {
        const fakeInput = document.createElement('input');
        fakeInput.type = 'text';
        document.body.appendChild(fakeInput);
        BarcodeUtil.scanBarcode(fakeInput, (code) => {
            document.body.removeChild(fakeInput);
            searchInput.value = code;
            searchInput.dispatchEvent(new Event('input'));
        });
    } else {
        const code = prompt('Masukkan kode barcode:');
        if (code) {
            searchInput.value = code;
            searchInput.dispatchEvent(new Event('input'));
        }
    }
}

async function performProductSearch() {
    const q = searchInput.value.trim();
    if (q.length === 0) { suggestionsDiv.innerHTML = ''; return; }
    
    // Barcode check
    if (/^\d{8,14}$/.test(q)) {
        try {
            const data = await api(`${BASE_URL}api/products/barcode/${q}`);
            if (data && data.id) {
                addProductToCart(data);
                searchInput.value = '';
                suggestionsDiv.innerHTML = '';
                return;
            }
        } catch (e) { /* fallback to text search */ }
    }

    if (q.length < 2) {
        suggestionsDiv.innerHTML = '';
        return;
    }

    try {
        let url;
        if (filterBySupplierSales && !isOtherMode && currentSupplierId) {
            url = `${BASE_URL}api/purchases/search-products?q=${encodeURIComponent(q)}`;
            url += `&supplier_id=${currentSupplierId}`;
            if (currentSalesRepId) url += `&sales_rep_id=${currentSalesRepId}`;
        } else {
            url = `${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`;
        }

        const data = await api(url);
        if (!Array.isArray(data) || data.length === 0) {
            suggestionsDiv.innerHTML = `
                <div style="padding:12px;text-align:center;">
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:8px;">Produk tidak ditemukan</div>
                    <a href="${BASE_URL}products/create" class="btn-outline-custom" style="padding:6px 16px;font-size:12px;text-decoration:none;">
                        <i class="bi bi-plus"></i> Tambah Produk Baru
                    </a>
                </div>`;
            return;
        }
        
        suggestionsDiv.innerHTML = data.map(p => {
            const isSupplierProduct = filterBySupplierSales && p.is_supplier_product ? 1 : 0;
            const badge = !filterBySupplierSales
                ? ''
                : (isSupplierProduct 
                    ? '<span style="font-size:9px;background:var(--success-bg);color:var(--success);padding:2px 6px;border-radius:10px;margin-left:4px;">Supplier</span>'
                    : '<span style="font-size:9px;background:var(--warning-bg);color:var(--warning);padding:2px 6px;border-radius:10px;margin-left:4px;">Lainnya</span>');
            
            return `
                <div class="search-result-item" onclick='selectProduct(${JSON.stringify(p).replace(/'/g, "&#39;")})' 
                     style="padding:10px;background:var(--surface-2);border-radius:var(--radius-sm);margin-bottom:4px;cursor:pointer;${isSupplierProduct ? 'border-left:3px solid var(--success);' : ''}">
                    <div style="font-size:0.85rem;font-weight:600;">${p.full_name || p.short_label}${badge}</div>
                    <div style="font-size:0.7rem;color:var(--text-muted);">${p.brand_name || ''} · ${p.category_name || ''}${p.last_buy_price ? ' · Beli: ' + formatRupiah(p.last_buy_price) : ''}</div>
                </div>
            `;
        }).join('');
    } catch (e) {
        suggestionsDiv.innerHTML = '';
    }
}

async function selectProduct(productSummary) {
    searchInput.value = '';
    suggestionsDiv.innerHTML = '';
    try {
        const data = await api(`${BASE_URL}api/products/${productSummary.id}`);
        addProductToCart(data);
    } catch (e) {
        showToast('Gagal mengambil data produk', 'error');
    }
}

function addProductToCart(product) {
    let defaultLevel = 1;
    let selectedPkg = product.packagings.find(p => p.level == defaultLevel) || product.packagings[0];
    
    // Ensure all packagings have PPN/diskon initialized
    product.packagings.forEach(p => {
        if (p.ppn_pct === undefined) p.ppn_pct = 0;
        if (p.diskon_mode === undefined) p.diskon_mode = 'rp';
        if (p.diskon_value === undefined) p.diskon_value = 0;
        p.harga_nett = parseFloat(p.buy_price) || 0;
    });
    
    const existingIndex = purchaseItems.findIndex(i => i.product_id == product.id && i.level == selectedPkg.level);
    if (existingIndex > -1) {
        purchaseItems[existingIndex].quantity += 1;
        purchaseItems[existingIndex].total = purchaseItems[existingIndex].quantity * purchaseItems[existingIndex].buy_price;
    } else {
        purchaseItems.unshift({
            id: Date.now(),
            product_id: product.id,
            name: product.full_name || product.short_label,
            packagings: product.packagings,
            level: selectedPkg.level,
            unit_name: selectedPkg.unit_name,
            quantity: 1,
            buy_price: parseFloat(selectedPkg.buy_price) || 0,
            sell_price_retail: parseFloat(selectedPkg.sell_price_retail) || 0,
            sell_price_wholesale: parseFloat(selectedPkg.sell_price_wholesale) || 0,
            last_buy_price: product.last_buy_price ? parseFloat(product.last_buy_price) : (parseFloat(product.packagings.find(p => p.level == 1)?.buy_price) || 0),
            total: parseFloat(selectedPkg.buy_price) || 0,
            ppn_pct: 0,
            diskon_mode: 'rp',
            diskon_value: 0,
            harga_nett: parseFloat(selectedPkg.buy_price) || 0
        });
    }
    
    renderCart();
    showToast(`${product.short_label || product.full_name} ditambahkan`);
}

function changeLevel(tempId, newLevel) {
    const item = purchaseItems.find(i => i.id == tempId);
    if (!item) return;
    const pkg = item.packagings.find(p => p.level == newLevel);
    if (pkg) {
        item.level = newLevel;
        item.unit_name = pkg.unit_name;
        item.buy_price = parseFloat(pkg.buy_price) || 0;
        item.sell_price_retail = parseFloat(pkg.sell_price_retail) || 0;
        item.sell_price_wholesale = parseFloat(pkg.sell_price_wholesale) || 0;
        item.ppn_pct = pkg.ppn_pct || 0;
        item.diskon_mode = pkg.diskon_mode || 'rp';
        item.diskon_value = pkg.diskon_value || 0;
        item.harga_nett = calcItemNett(item.buy_price, item.ppn_pct, item.diskon_mode, item.diskon_value);
        item.total = item.quantity * item.buy_price;
        renderCart();
        
        // Initialize custom price checkboxes and locked notes after render
        setTimeout(() => {
            const row = document.querySelector(`[oninput="updateItem(${tempId}, 'buy_price', this.value)"]`)?.closest('[style*="rgba(0,0,0,0.15)"]');
            if (row) {
                const buyToggle = row.querySelector('.buy-custom-toggle');
                const sellToggle = row.querySelector('.sell-custom-toggle');
                const buyNote = row.querySelector('.buy-locked-note');
                const sellNote = row.querySelector('.sell-locked-note');
                
                // Set checkbox states based on pkg flags
                if (buyToggle) {
                    buyToggle.querySelector('input').checked = pkg.buy_custom || false;
                    buyToggle.classList.toggle('active', pkg.buy_custom || false);
                }
                if (sellToggle) {
                    sellToggle.querySelector('input').checked = pkg.sell_custom || false;
                    sellToggle.classList.toggle('active', pkg.sell_custom || false);
                }
                
                // Show locked notes by default if not custom
                if (buyNote) buyNote.style.display = pkg.buy_custom ? 'none' : 'block';
                if (sellNote) sellNote.style.display = pkg.sell_custom ? 'none' : 'block';
            }
        }, 0);
    }
}

function updateItem(tempId, field, value) {
    const item = purchaseItems.find(i => i.id == tempId);
    if (!item) return;
    const numValue = parseFloat(value) || 0;
    item[field] = numValue;
    
    // Sync with the packagings array so we don't lose the edit if user changes level
    const pkg = item.packagings.find(p => p.level == item.level);
    if (pkg) {
        pkg[field] = numValue;
    }

    if (field === 'quantity' || field === 'buy_price') {
        item.total = item.quantity * item.buy_price;
    }
    
    // Bidirectional sync: auto-update Total Belanja field when buy_price or quantity changes
    if (field === 'buy_price' || field === 'quantity') {
        const totalInput = document.getElementById(`total_input_${tempId}`);
        const totalWrap  = document.getElementById(`total_wrap_${tempId}`);
        if (field === 'buy_price') {
            if (numValue > 0) {
                if (totalWrap) totalWrap.style.display = '';
                if (totalInput && document.activeElement !== totalInput) {
                    totalInput.value = Math.round(item.total);
                }
            } else {
                if (totalWrap) totalWrap.style.display = 'none';
                if (totalInput) totalInput.value = '';
            }
        } else if (field === 'quantity' && item.buy_price > 0) {
            if (totalInput && document.activeElement !== totalInput) {
                totalInput.value = Math.round(item.total);
            }
        }
    }
    
    if (field === 'buy_price') {
        item.harga_nett = calcItemNett(item.buy_price, item.ppn_pct || 0, item.diskon_mode || 'rp', item.diskon_value || 0);
        const nettEl = document.getElementById(`nett_info_${tempId}`);
        if (nettEl) nettEl.innerHTML = buildNettInfo(item);
    }
    
    // Auto update margin displays
    if (field === 'buy_price' || field === 'sell_price_retail' || field === 'sell_price_wholesale') {
        updateMarginDisplay(tempId, item.harga_nett || item.buy_price, item.sell_price_retail, item.sell_price_wholesale);
        
        // Sync prices to other packaging levels if not custom
        syncPricesToPackagings(item, field);
    }

    calculateTotal();
}

function updateItemTotal(tempId, totalValue) {
    const item = purchaseItems.find(i => i.id == tempId);
    if (!item) return;
    const pkg = item.packagings.find(p => p.level == item.level);
    if (!pkg) return;
    
    const total = parseFloat(totalValue);
    const infoEl = document.getElementById(`total_info_${tempId}`);
    
    if (!total || total <= 0) {
        if (infoEl) infoEl.innerHTML = '';
        return;
    }

    // Calculate per pcs price
    const qty = parseFloat(item.quantity) || 1;
    const baseQty = parseFloat(pkg.base_qty) || 1;
    const totalPcs = qty * baseQty;
    const newBaseBuyPrice = total / totalPcs;
    
    // Set the new buy_price on the current level (already multiplied by base_qty for this level)
    const newLevelBuyPrice = Math.round(newBaseBuyPrice * baseQty);
    updateItem(tempId, 'buy_price', newLevelBuyPrice);

    // Update input field visually
    const buyInput = document.querySelector(`[oninput="updateItem(${tempId}, 'buy_price', this.value)"]`);
    if (buyInput) buyInput.value = newLevelBuyPrice;

    // Show status info
    if (infoEl && item.last_buy_price) {
        const oldPrice = parseFloat(item.last_buy_price);
        if (oldPrice > 0) {
            if (newBaseBuyPrice === oldPrice) {
                infoEl.innerHTML = `<span style="color:var(--success);"><i class="bi bi-check-circle"></i> Harga modal masih sama (Rp${Math.round(newBaseBuyPrice).toLocaleString('id-ID')}/pcs)</span>`;
            } else if (newBaseBuyPrice < oldPrice) {
                const diff = oldPrice - newBaseBuyPrice;
                infoEl.innerHTML = `<span style="color:var(--info);"><i class="bi bi-graph-down-arrow"></i> Harga turun / Diskon Rp${Math.round(diff).toLocaleString('id-ID')} dari harga terakhir (Rp${Math.round(oldPrice).toLocaleString('id-ID')}/pcs)</span>`;
            } else {
                const diff = newBaseBuyPrice - oldPrice;
                // Calculate suggested sell prices based on previous margin
                const prevRetail = parseFloat(pkg.sell_price_retail) || 0;
                let suggested = '';
                if (prevRetail > 0 && oldPrice * baseQty > 0) {
                    const prevMarginPct = (prevRetail - (oldPrice * baseQty)) / prevRetail;
                    const suggestedRetail = Math.round((newBaseBuyPrice * baseQty) / (1 - prevMarginPct));
                    suggested = `<br>Saran jual ecer baru: <strong>Rp${suggestedRetail.toLocaleString('id-ID')}</strong> (Margin ${(prevMarginPct*100).toFixed(1)}%)`;
                }
                infoEl.innerHTML = `<span style="color:var(--warning);"><i class="bi bi-graph-up-arrow"></i> Harga naik Rp${Math.round(diff).toLocaleString('id-ID')} dari harga terakhir (Rp${Math.round(oldPrice).toLocaleString('id-ID')}/pcs)${suggested}</span>`;
            }
        }
    } else if (infoEl) {
        infoEl.innerHTML = `<span style="color:var(--text-muted);">Harga modal per pcs: Rp${Math.round(newBaseBuyPrice).toLocaleString('id-ID')}</span>`;
    }
}

function toggleCustomPrice(tempId, priceType, isCustom) {
    const item = purchaseItems.find(i => i.id == tempId);
    if (!item) return;
    
    const pkg = item.packagings.find(p => p.level == item.level);
    if (!pkg) return;
    
    // Store custom flag in the packaging
    if (priceType === 'buy') {
        pkg.buy_custom = isCustom;
    } else if (priceType === 'sell') {
        pkg.sell_custom = isCustom;
    }
    
    // Toggle the locked note visibility
    const row = document.querySelector(`[oninput="updateItem(${tempId}, 'buy_price', this.value)"]`).closest('[style*="rgba(0,0,0,0.15)"]');
    if (row) {
        if (priceType === 'buy') {
            const note = row.querySelector('.buy-locked-note');
            const toggle = row.querySelector('.buy-custom-toggle');
            if (note) note.style.display = isCustom ? 'none' : 'block';
            if (toggle) toggle.classList.toggle('active', isCustom);
        } else if (priceType === 'sell') {
            const note = row.querySelector('.sell-locked-note');
            const toggle = row.querySelector('.sell-custom-toggle');
            if (note) note.style.display = isCustom ? 'none' : 'block';
            if (toggle) toggle.classList.toggle('active', isCustom);
        }
    }
    
    // If disabling custom, re-sync from level 1
    if (!isCustom) {
        syncPricesFromLevel1(item);
    }
}

function syncPricesToPackagings(item, field) {
    // Only sync if this is level 1 (pcs) - the base unit
    if (item.level != 1) return;
    
    const level1Pkg = item.packagings.find(p => p.level == 1);
    if (!level1Pkg) return;
    
    const basePrice = level1Pkg[field];
    if (!basePrice || basePrice <= 0) return;
    
    item.packagings.forEach(pkg => {
        if (pkg.level == 1) return;
        
        // Check if this level has custom price for this field
        const isBuy = field === 'buy_price';
        const isSell = field === 'sell_price_retail' || field === 'sell_price_wholesale';
        
        if (isBuy && pkg.buy_custom) return;
        if (isSell && pkg.sell_custom) return;
        
        // Calculate price based on base_qty
        const ratio = pkg.base_qty / level1Pkg.base_qty;
        if (field === 'buy_price') {
            pkg.buy_price = Math.round(basePrice * ratio);
        } else if (field === 'sell_price_retail') {
            pkg.sell_price_retail = Math.round(basePrice * ratio);
        } else if (field === 'sell_price_wholesale') {
            pkg.sell_price_wholesale = Math.round(basePrice * ratio);
        }
    });
}

function syncPricesFromLevel1(item) {
    const level1Pkg = item.packagings.find(p => p.level == 1);
    if (!level1Pkg) return;
    
    ['buy_price', 'sell_price_retail', 'sell_price_wholesale'].forEach(field => {
        const basePrice = level1Pkg[field];
        if (!basePrice || basePrice <= 0) return;
        
        item.packagings.forEach(pkg => {
            if (pkg.level == 1) return;
            
            const isBuy = field === 'buy_price';
            const isSell = field === 'sell_price_retail' || field === 'sell_price_wholesale';
            
            if (isBuy && pkg.buy_custom) return;
            if (isSell && pkg.sell_custom) return;
            
            const ratio = pkg.base_qty / level1Pkg.base_qty;
            if (field === 'buy_price') {
                pkg.buy_price = Math.round(basePrice * ratio);
            } else if (field === 'sell_price_retail') {
                pkg.sell_price_retail = Math.round(basePrice * ratio);
            } else if (field === 'sell_price_wholesale') {
                pkg.sell_price_wholesale = Math.round(basePrice * ratio);
            }
        });
    });
}

/** Calculate nett buy price after PPN and discount */
function calcItemNett(buy, ppn_pct, diskon_mode, diskon_value) {
    buy = parseFloat(buy) || 0;
    const ppn_amt = buy * ((parseFloat(ppn_pct) || 0) / 100);
    const diskon_amt = diskon_mode === 'pct'
        ? buy * ((parseFloat(diskon_value) || 0) / 100)
        : (parseFloat(diskon_value) || 0);
    return Math.max(0, buy + ppn_amt - diskon_amt);
}

/** Build HTML for nett price breakdown display */
function buildNettInfo(item) {
    const buy = item.buy_price || 0;
    const ppn = item.ppn_pct || 0;
    const diskon = item.diskon_value || 0;
    const diskonMode = item.diskon_mode || 'rp';
    const nett = item.harga_nett || buy;
    if (ppn === 0 && diskon === 0) return '<span style="font-size:9px;color:var(--text-muted);"><i class="bi bi-info-circle"></i> Isi PPN atau Diskon untuk melihat Harga Nett</span>';
    const ppn_amt = buy * (ppn / 100);
    const diskon_amt = diskonMode === 'pct' ? buy * (diskon / 100) : diskon;
    let html = `<div style="background:rgba(0,0,0,0.25);border-radius:4px;padding:5px 7px;font-size:10px;">`;
    html += `<span style="color:var(--text-muted);">Modal: Rp${Math.round(buy).toLocaleString('id-ID')}</span>`;
    if (ppn > 0) html += ` <span style="color:var(--warning);">+PPN(${ppn}%): Rp${Math.round(ppn_amt).toLocaleString('id-ID')}</span>`;
    if (diskon > 0) html += ` <span style="color:var(--success);">−Diskon: Rp${Math.round(diskon_amt).toLocaleString('id-ID')}</span>`;
    html += ` → <strong style="color:var(--info);">Nett: Rp${Math.round(nett).toLocaleString('id-ID')}</strong>`;
    html += `</div>`;
    return html;
}

/** Event listener to update item PPN or Diskon */
function updateItemPpnDiskon(tempId, type, val) {
    const item = purchaseItems.find(i => i.id == tempId);
    if (!item) return;
    
    if (type === 'ppn') {
        item.ppn_pct = parseFloat(val) || 0;
    } else if (type === 'mode') {
        item.diskon_mode = val || 'rp';
    } else if (type === 'diskon') {
        item.diskon_value = parseFloat(val) || 0;
    }
    
    item.harga_nett = calcItemNett(item.buy_price, item.ppn_pct, item.diskon_mode, item.diskon_value);
    
    const nettEl = document.getElementById(`nett_info_${tempId}`);
    if (nettEl) nettEl.innerHTML = buildNettInfo(item);
    
    updateMarginDisplay(tempId, item.harga_nett, item.sell_price_retail, item.sell_price_wholesale);
}

/** Override function for PackagingPriceSync to calculate margins using nett price */
function calcMarginForLevel(lvEl) {
    const buy = parseFloat(lvEl?.querySelector('.pkg-buy,.buy-price')?.value) || 0;
    const ret = parseFloat(lvEl?.querySelector('.pkg-ret,.retail-price')?.value) || 0;
    const who = parseFloat(lvEl?.querySelector('.pkg-wholesale,.wholesale-price')?.value) || 0;
    
    const ppn = parseFloat(lvEl?.querySelector('.pkg-ppn')?.value) || 0;
    const diskonMode = lvEl?.querySelector('.pkg-diskon-mode')?.value || 'rp';
    const diskonVal = parseFloat(lvEl?.querySelector('.pkg-diskon-value')?.value) || 0;
    const nett = calcItemNett(buy, ppn, diskonMode, diskonVal);

    const nettInfoEl = lvEl?.querySelector('.pkg-nett-info');
    if (nettInfoEl) {
        if (ppn > 0 || diskonVal > 0) {
            const ppnAmt = buy * ppn / 100;
            const diskonAmt = diskonMode === 'pct' ? buy * diskonVal / 100 : diskonVal;
            nettInfoEl.innerHTML = '<span style="color:var(--text-muted);">Modal: Rp' + Math.round(buy).toLocaleString('id-ID') + '</span>'
                + (ppn > 0 ? ' <span style="color:var(--warning);">+PPN: Rp' + Math.round(ppnAmt).toLocaleString('id-ID') + '</span>' : '')
                + (diskonVal > 0 ? ' <span style="color:var(--success);">\u2212Diskon: Rp' + Math.round(diskonAmt).toLocaleString('id-ID') + '</span>' : '')
                + ' \u2192 <strong style="color:var(--info);">Nett: Rp' + Math.round(nett).toLocaleString('id-ID') + '</strong>';
        } else { nettInfoEl.innerHTML = ''; }
    }

    const marginEl = lvEl?.querySelector('.pkg-margin-info, .margin-calc');
    if (marginEl) {
        const rText = marginEl.querySelector('.margin-retail-text');
        const wText = marginEl.querySelector('.margin-wholesale-text');
        if (rText) rText.innerHTML = formatMarginWithProfit('Ecer', nett, ret);
        if (wText) wText.innerHTML = formatMarginWithProfit('Grosir', nett, who);
    }
}

function openAllPackagingsModal(tempId) {
    const item = purchaseItems.find(i => i.id == tempId);
    if (!item) return;

    // --- STEP 1: Recalculate buy prices per level from current item.buy_price ---
    const currentPkg = item.packagings.find(p => p.level == item.level);
    const currentBaseQty = parseFloat(currentPkg?.base_qty) || 1;
    const buyPricePerPcs = item.buy_price / currentBaseQty;

    item.packagings.forEach(pkg => {
        // Store original prices once for comparison
        if (pkg._orig_buy === undefined) pkg._orig_buy = pkg.buy_price;
        if (pkg._orig_ret === undefined) pkg._orig_ret = pkg.sell_price_retail;
        if (!pkg.qty_prices) pkg.qty_prices = [];
        if (pkg.ppn_pct === undefined) pkg.ppn_pct = 0;
        if (pkg.diskon_mode === undefined) pkg.diskon_mode = 'rp';
        if (pkg.diskon_value === undefined) pkg.diskon_value = 0;
        // Recalculate non-custom buy prices
        if (!pkg.buy_custom) {
            pkg.buy_price = Math.round(buyPricePerPcs * (parseFloat(pkg.base_qty) || 1));
        }
    });

    let html = `
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">
            Produk: <strong style="color:var(--text-primary);">${item.name}</strong><br>
            Harga modal dihitung otomatis per kemasan. Margin &amp; selisih berubah realtime.
        </div>
    `;

    item.packagings.forEach(pkg => {
        const isLevel1 = pkg.level == 1;
        const baseQty = parseFloat(pkg.base_qty) || 1;
        const origBuy = parseFloat(pkg._orig_buy) || 0;
        const origRet = parseFloat(pkg._orig_ret) || 0;
        const nett = calcItemNett(pkg.buy_price, pkg.ppn_pct, pkg.diskon_mode, pkg.diskon_value);

        // Price change badge
        let changeBadge = '';
        if (origBuy > 0 && pkg.buy_price !== origBuy) {
            if (pkg.buy_price > origBuy) {
                const diff = Math.round(pkg.buy_price - origBuy);
                changeBadge = `<span style="font-size:9px;background:var(--warning-bg);color:var(--warning);padding:2px 6px;border-radius:10px;margin-left:6px;"><i class="bi bi-graph-up-arrow"></i> Naik Rp${diff.toLocaleString('id-ID')}</span>`;
            } else {
                const diff = Math.round(origBuy - pkg.buy_price);
                changeBadge = `<span style="font-size:9px;background:var(--info-bg);color:var(--info);padding:2px 6px;border-radius:10px;margin-left:6px;"><i class="bi bi-graph-down-arrow"></i> Turun Rp${diff.toLocaleString('id-ID')}</span>`;
            }
        } else if (origBuy > 0) {
            changeBadge = `<span style="font-size:9px;background:var(--success-bg);color:var(--success);padding:2px 6px;border-radius:10px;margin-left:6px;"><i class="bi bi-check-circle"></i> Sama</span>`;
        }

        // Suggested sell price based on prev margin
        let suggestedHtml = '';
        if (origBuy > 0 && origRet > 0) {
            const prevMargin = (origRet - origBuy) / origRet;
            if (prevMargin > 0 && prevMargin < 1) {
                const sugRet = Math.round(pkg.buy_price / (1 - prevMargin));
                suggestedHtml = `<div style="font-size:10px;color:var(--info);margin-top:3px;margin-bottom:6px;"><i class="bi bi-lightbulb"></i> Saran jual ecer (margin ${(prevMargin*100).toFixed(1)}%): <strong>Rp${sugRet.toLocaleString('id-ID')}</strong></div>`;
            }
        }

        // Tier rows
        const tiers = pkg.qty_prices || [];
        let tierRowsHtml = tiers.map(t => {
            const totalH = Math.round((parseFloat(t.min_qty)||0) * (parseFloat(t.unit_price)||0));
            return `
            <div class="tier-row" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:4px;margin-bottom:4px;align-items:center;">
                <input type="number" class="form-control-dark tier-min-qty" style="font-size:10px;padding:4px;" placeholder="Min Qty" value="${t.min_qty}" min="1">
                <input type="number" class="form-control-dark tier-total-harga" style="font-size:10px;padding:4px;color:var(--success);" placeholder="Total Harga" value="${totalH}" min="0" oninput="recalcTierHint(this)">
                <select class="form-select-dark tier-mode" style="font-size:10px;padding:4px;">
                    <option value="both" ${t.sale_mode==='both'?'selected':''}>Semua</option>
                    <option value="retail" ${t.sale_mode==='retail'?'selected':''}>Ecer</option>
                    <option value="wholesale" ${t.sale_mode==='wholesale'?'selected':''}>Grosir</option>
                </select>
                <button type="button" onclick="this.closest('.tier-row').remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:4px 8px;cursor:pointer;font-size:12px;"><i class="bi bi-x"></i></button>
            </div>`;
        }).join('');

        html += `
        <div class="packaging-level-edit" data-level="${pkg.level}" data-base-qty="${baseQty}" data-pkg-id="${pkg.id || ''}" style="border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:12px;margin-bottom:12px;background:var(--surface-2);">
            <div style="font-weight:600;font-size:13px;margin-bottom:10px;color:var(--primary);display:flex;align-items:center;flex-wrap:wrap;gap:4px;">
                Level ${pkg.level} — ${pkg.unit_name} (Isi ${baseQty} pcs) ${changeBadge}
            </div>
            <div style="background:rgba(0,0,0,0.15);padding:12px;border-radius:var(--radius-sm);border:1px solid rgba(255,255,255,0.05);">
                ${!isLevel1 ? `
                <label class="price-custom-toggle buy-custom-toggle ${pkg.buy_custom?'active':''}" title="Centang untuk harga modal manual">
                    <input type="checkbox" class="chk-buy-custom" ${pkg.buy_custom?'checked':''}>
                    <i class="bi bi-pencil-square" style="font-size:10px;"></i> Harga Modal Custom
                </label>` : ''}
                <div style="margin-bottom:8px;">
                    <label style="font-size:10px;color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span>Harga Modal / Beli *</span>
                        ${origBuy>0 ? `<span style="font-size:9px;">Sebelumnya: Rp${Math.round(origBuy).toLocaleString('id-ID')}</span>` : ''}
                    </label>
                    <input type="number" id="mod_buy_${pkg.level}" class="form-control-dark buy-price pkg-buy" step="0.01" style="width:100%;font-size:12px;padding:6px;" value="${pkg.buy_price}" oninput="onPkgModalInput(this, ${pkg.level})">
                    ${!isLevel1 ? `<div class="price-locked-note buy-locked-note ${pkg.buy_custom?'':'visible'}"><i class="bi bi-link-45deg"></i> Otomatis dari pcs × isi</div>` : ''}
                </div>

                <div style="background:rgba(76,201,240,0.06);border:1px dashed rgba(76,201,240,0.3);border-radius:4px;padding:8px;margin-bottom:8px;">
                    <div style="font-size:10px;color:var(--info);font-weight:600;margin-bottom:6px;"><i class="bi bi-receipt"></i> PPN &amp; Diskon</div>
                    <div style="display:grid;grid-template-columns:1fr 2fr;gap:6px;margin-bottom:6px;">
                        <div>
                            <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">PPN (%)</label>
                            <input type="number" id="mod_ppn_${pkg.level}" class="form-control-dark pkg-ppn" step="0.01" style="width:100%;padding:4px;font-size:11px;" value="${pkg.ppn_pct || 0}" min="0" max="100" placeholder="0" oninput="onPkgModalInput(this, ${pkg.level})">
                        </div>
                        <div>
                            <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">Diskon</label>
                            <div style="display:flex;gap:4px;">
                                <select id="mod_diskon_mode_${pkg.level}" class="form-select-dark pkg-diskon-mode" style="width:50px;padding:4px;font-size:10px;" onchange="onPkgModalInput(this, ${pkg.level})">
                                    <option value="rp" ${(pkg.diskon_mode||'rp')==='rp'?'selected':''}>Rp</option>
                                    <option value="pct" ${(pkg.diskon_mode||'rp')==='pct'?'selected':''}>%</option>
                                </select>
                                <input type="number" id="mod_diskon_value_${pkg.level}" class="form-control-dark pkg-diskon-value" step="0.01" style="flex:1;padding:4px;font-size:11px;" value="${pkg.diskon_value || 0}" min="0" placeholder="0" oninput="onPkgModalInput(this, ${pkg.level})">
                            </div>
                        </div>
                    </div>
                    <div class="pkg-nett-info" style="min-height:14px;font-size:10px;"></div>
                </div>

                ${suggestedHtml}
                ${!isLevel1 ? `
                <label class="price-custom-toggle sell-custom-toggle ${pkg.sell_custom?'active':''}" title="Centang untuk harga jual manual">
                    <input type="checkbox" class="chk-sell-custom" ${pkg.sell_custom?'checked':''}>
                    <i class="bi bi-tag" style="font-size:10px;"></i> Harga Jual Custom
                </label>` : ''}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:6px;">
                    <div>
                        <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Jual Ecer *</label>
                        <input type="number" id="mod_ret_${pkg.level}" class="form-control-dark retail-price pkg-ret" style="width:100%;font-size:12px;padding:6px;color:var(--success);" value="${pkg.sell_price_retail}" oninput="onPkgModalInput(this, ${pkg.level})">
                    </div>
                    <div>
                        <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Jual Grosir</label>
                        <input type="number" id="mod_who_${pkg.level}" class="form-control-dark wholesale-price pkg-wholesale" style="width:100%;font-size:12px;padding:6px;color:var(--warning);" value="${pkg.sell_price_wholesale}" oninput="onPkgModalInput(this, ${pkg.level})">
                    </div>
                </div>
                ${!isLevel1 ? `<div class="price-locked-note sell-locked-note ${pkg.sell_custom?'':'visible'}"><i class="bi bi-link-45deg"></i> Otomatis dari pcs × isi</div>` : ''}
                <div class="margin-calc pkg-margin-info" id="mod_margin_${pkg.level}" style="margin-top:6px;font-size:11px;color:var(--text-muted);display:flex;justify-content:space-between;">
                    <span class="margin-retail-text">${formatMarginWithProfit('Ecer', nett, pkg.sell_price_retail)}</span>
                    <span class="margin-wholesale-text">${formatMarginWithProfit('Grosir', nett, pkg.sell_price_wholesale)}</span>
                </div>
            </div>
            <!-- Tier / Harga Spesial per Qty -->
            <div style="margin-top:10px;border-top:1px dashed var(--border-color);padding-top:10px;">
                <div style="font-size:11px;font-weight:600;color:var(--info);margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;">
                    <span><i class="bi bi-layers"></i> Harga Spesial / Tier per Kuantitas</span>
                    <button type="button" onclick="addTierRow(this)" style="background:var(--info-bg);color:var(--info);border:none;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;"><i class="bi bi-plus"></i> Tambah</button>
                </div>
                <div style="font-size:9px;color:var(--text-muted);margin-bottom:4px;display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:4px;"><span>Min Qty</span><span>Total Harga</span><span>Mode</span><span></span></div>
                <div class="tier-rows-container">${tierRowsHtml}</div>
                ${tiers.length===0 ? `<div class="tier-empty-hint" style="font-size:10px;color:var(--text-muted);text-align:center;padding:4px;"><i class="bi bi-info-circle"></i> Belum ada harga tier. Klik Tambah untuk menambahkan.</div>` : ''}
            </div>
        </div>`;
    });

    AppModal.show({
        title: 'Atur Semua Harga Kemasan',
        icon: 'bi-tags',
        iconColor: 'var(--primary-bg)',
        iconAccent: 'var(--primary)',
        bodyHTML: html,
        submitText: 'Simpan Harga',
        onSubmit: async () => {
            for (const pkg of item.packagings) {
                const buyEl = document.getElementById(`mod_buy_${pkg.level}`);
                const retEl = document.getElementById(`mod_ret_${pkg.level}`);
                const whoEl = document.getElementById(`mod_who_${pkg.level}`);
                
                const ppnEl = document.getElementById(`mod_ppn_${pkg.level}`);
                const dModeEl = document.getElementById(`mod_diskon_mode_${pkg.level}`);
                const dValEl = document.getElementById(`mod_diskon_value_${pkg.level}`);
                
                if (buyEl) pkg.buy_price = parseFloat(buyEl.value) || 0;
                if (retEl) pkg.sell_price_retail = parseFloat(retEl.value) || 0;
                if (whoEl) pkg.sell_price_wholesale = parseFloat(whoEl.value) || 0;
                
                if (ppnEl) pkg.ppn_pct = parseFloat(ppnEl.value) || 0;
                if (dModeEl) pkg.diskon_mode = dModeEl.value || 'rp';
                if (dValEl) pkg.diskon_value = parseFloat(dValEl.value) || 0;
                pkg.harga_nett = calcItemNett(pkg.buy_price, pkg.ppn_pct, pkg.diskon_mode, pkg.diskon_value);

                const lvEl = document.querySelector(`.packaging-level-edit[data-level="${pkg.level}"]`);
                if (lvEl) {
                    pkg.buy_custom = lvEl.querySelector('.chk-buy-custom')?.checked || false;
                    pkg.sell_custom = lvEl.querySelector('.chk-sell-custom')?.checked || false;

                    // Collect tier data
                    const tiers = [];
                    lvEl.querySelectorAll('.tier-row').forEach(row => {
                        const minQty = parseFloat(row.querySelector('.tier-min-qty')?.value) || 0;
                        const totalH = parseFloat(row.querySelector('.tier-total-harga')?.value) || 0;
                        const mode = row.querySelector('.tier-mode')?.value || 'both';
                        if (minQty > 0 && totalH > 0) {
                            tiers.push({ min_qty: minQty, unit_price: totalH / minQty, sale_mode: mode });
                        }
                    });
                    pkg.qty_prices = tiers;

                    // Save tier prices to DB if pkg has an ID
                    const pkgId = lvEl.dataset.pkgId;
                    if (pkgId) {
                        try {
                            await fetch(`${BASE_URL}api/packagings/${pkgId}/qty-prices`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfVal },
                                body: JSON.stringify({ tiers, csrf_token: csrfVal })
                            });
                        } catch(e) { console.warn('Tier save error:', e); }
                    }
                }

                if (pkg.level == item.level) {
                    item.buy_price = pkg.buy_price;
                    item.sell_price_retail = pkg.sell_price_retail;
                    item.sell_price_wholesale = pkg.sell_price_wholesale;
                    
                    item.ppn_pct = pkg.ppn_pct;
                    item.diskon_mode = pkg.diskon_mode;
                    item.diskon_value = pkg.diskon_value;
                    item.harga_nett = pkg.harga_nett;
                    
                    item.total = item.quantity * item.buy_price;
                }
            }
            renderCart();
            showToast('Harga semua kemasan berhasil diupdate', 'success');
            return true;
        },
        onShown: () => {
            if (typeof PackagingPriceSync !== 'undefined') {
                const levels = document.querySelectorAll('.packaging-level-edit');
                levels.forEach(lv => { PackagingPriceSync.bindLevel(lv); PackagingPriceSync.updateMargins(lv); });
                PackagingPriceSync.propagateAllFromLevel1();
            }
            // Trigger margin calculation initially
            document.querySelectorAll('.packaging-level-edit').forEach(lv => {
                const buyInput = lv.querySelector('.pkg-buy');
                if(buyInput) onPkgModalInput(buyInput, lv.dataset.level);
            });
        }
    });
}

/** Reactive margin update when price changed in packaging modal */
function onPkgModalInput(inputEl, level) {
    const lvEl = inputEl.closest('.packaging-level-edit');
    if (!lvEl) return;
    
    if (typeof PackagingPriceSync !== 'undefined') {
        const field = inputEl.classList.contains('pkg-buy')||inputEl.classList.contains('buy-price') ? 'buy'
                    : inputEl.classList.contains('pkg-ret')||inputEl.classList.contains('retail-price') ? 'retail' : 'wholesale';
        PackagingPriceSync.syncFromInput(inputEl, field);
    } else {
        calcMarginForLevel(lvEl);
    }
}

/** Add new tier row to the tier container */
function addTierRow(btn) {
    const lvEl = btn.closest('.packaging-level-edit');
    const container = lvEl.querySelector('.tier-rows-container');
    const emptyHint = lvEl.querySelector('.tier-empty-hint');
    if (emptyHint) emptyHint.remove();
    const row = document.createElement('div');
    row.className = 'tier-row';
    row.style.cssText = 'display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:4px;margin-bottom:4px;align-items:center;';
    row.innerHTML = `
        <input type="number" class="form-control-dark tier-min-qty" style="font-size:10px;padding:4px;" placeholder="Min Qty" min="1">
        <input type="number" class="form-control-dark tier-total-harga" style="font-size:10px;padding:4px;color:var(--success);" placeholder="Total Harga" min="0" oninput="recalcTierHint(this)">
        <select class="form-select-dark tier-mode" style="font-size:10px;padding:4px;">
            <option value="both">Semua</option><option value="retail">Ecer</option><option value="wholesale">Grosir</option>
        </select>
        <button type="button" onclick="this.closest('.tier-row').remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:4px 8px;cursor:pointer;font-size:12px;"><i class="bi bi-x"></i></button>`;
    container.appendChild(row);
}

/** Show per-unit hint for tier total harga input */
function recalcTierHint(el) {
    const row = el.closest('.tier-row');
    if (!row) return;
    const minQty = parseFloat(row.querySelector('.tier-min-qty')?.value) || 0;
    const totalH = parseFloat(el.value) || 0;
    let hint = row.querySelector('.tier-hint');
    if (!hint) {
        hint = document.createElement('div');
        hint.className = 'tier-hint';
        hint.style.cssText = 'font-size:9px;color:var(--text-muted);grid-column:1/-1;padding-left:2px;';
        row.after(hint);
    }
    hint.textContent = (minQty > 0 && totalH > 0)
        ? `≈ Rp${Math.round(totalH/minQty).toLocaleString('id-ID')} / pcs`
        : '';
}



function removeItem(tempId) {
    purchaseItems = purchaseItems.filter(i => i.id != tempId);
    renderCart();
}

function calculateTotal() {
    let sum = 0;
    purchaseItems.forEach(i => sum += i.total);
    currentSubtotal = sum;
    document.getElementById('purchaseSubtotal').textContent = formatRupiah(sum);
    calculateGrandTotal();
    return sum;
}

function calculateGrandTotal() {
    const discount = parseFloat(document.getElementById('invoiceDiscount').value) || 0;
    const taxPercent = parseFloat(document.getElementById('invoiceTax').value) || 0;
    
    let totalAfterDiscount = currentSubtotal - discount;
    if (totalAfterDiscount < 0) totalAfterDiscount = 0;
    
    const taxAmount = totalAfterDiscount * (taxPercent / 100);
    currentGrandTotal = totalAfterDiscount + taxAmount;
    
    document.getElementById('purchaseGrandTotal').textContent = formatRupiah(currentGrandTotal);
}

function distributeAdjustments() {
    if (purchaseItems.length === 0 || currentSubtotal === 0) return;
    
    // The ratio of grand_total to subtotal
    const ratio = currentGrandTotal / currentSubtotal;
    
    purchaseItems.forEach(item => {
        // Adjust the buy_price proportionally
        item.buy_price = Math.round(item.buy_price * ratio);
        item.total = item.quantity * item.buy_price;
    });
    
    // Reset discount and tax since they are now baked into the item prices
    document.getElementById('invoiceDiscount').value = 0;
    document.getElementById('invoiceTax').value = 0;
    
    renderCart(); // this calls calculateTotal() and calculateGrandTotal()
    showToast('Harga modal berhasil didistribusikan', 'success');
}

function formatMarginWithProfit(label, buy, sell) {
    buy = parseFloat(buy) || 0;
    sell = parseFloat(sell) || 0;
    if (buy <= 0 || sell <= 0) return `${label}: 0%`;
    const m = ((sell - buy) / sell * 100).toFixed(1);
    const profit = sell - buy;
    const color = label === 'Ecer' ? (m >= 10 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)'))
                                   : (m >= 5 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)'));
    return `${label}: <strong style="color:${color}">${m}%</strong> <span style="font-size:10px;color:var(--text-muted);">(Rp${Math.round(profit).toLocaleString('id-ID')})</span>`;
}

function updateMarginDisplay(tempId, buy, retail, wholesale) {
    const mRetail = document.getElementById(`margin_retail_${tempId}`);
    if (mRetail) mRetail.innerHTML = formatMarginWithProfit('Ecer', buy, retail);
    const mWholesale = document.getElementById(`margin_wholesale_${tempId}`);
    if (mWholesale) mWholesale.innerHTML = formatMarginWithProfit('Grosir', buy, wholesale);
}



/** Build mini packaging summary for Daftar Barang view */
function buildPkgMiniSummaryHtml(item) {
    if (!item.packagings || item.packagings.length <= 1) return '';
    const otherPkgs = item.packagings.filter(p => p.level != item.level);
    if (otherPkgs.length === 0) return '';
    const curPkg = item.packagings.find(p => p.level == item.level);
    const curBaseQty = parseFloat(curPkg?.base_qty) || 1;
    const nett = item.harga_nett || item.buy_price || 0;
    const nettPerPcs = nett / curBaseQty;
    const rows = otherPkgs.map(pkg => {
        const bq = parseFloat(pkg.base_qty) || 1;
        const nb = Math.round(nettPerPcs * bq);
        const ret = parseFloat(pkg.sell_price_retail) || 0;
        const who = parseFloat(pkg.sell_price_wholesale) || 0;
        const mR = (nb > 0 && ret > 0) ? ((ret - nb) / ret * 100).toFixed(1) : null;
        const mW = (nb > 0 && who > 0) ? ((who - nb) / who * 100).toFixed(1) : null;
        const cR = mR !== null ? (parseFloat(mR) >= 10 ? 'var(--success)' : parseFloat(mR) >= 0 ? 'var(--warning)' : 'var(--danger)') : 'var(--text-muted)';
        const cW = mW !== null ? (parseFloat(mW) >= 5 ? 'var(--success)' : parseFloat(mW) >= 0 ? 'var(--warning)' : 'var(--danger)') : 'var(--text-muted)';
        return '<div style="background:rgba(0,0,0,0.12);border-radius:6px;padding:6px 8px;margin-bottom:4px;border:1px solid rgba(255,255,255,0.04);">'
            + '<div style="font-size:9px;font-weight:700;color:var(--primary);margin-bottom:4px;"><i class="bi bi-box-seam"></i> ' + pkg.unit_name + ' <span style="font-weight:400;color:var(--text-muted);">(Isi ' + bq + ' pcs)</span></div>'
            + '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;font-size:9px;">'
            + '<div style="background:rgba(0,0,0,0.2);padding:4px 6px;border-radius:4px;"><div style="color:var(--text-muted);font-size:8px;">Modal</div><div style="font-weight:600;font-size:10px;">' + (nb > 0 ? 'Rp'+nb.toLocaleString('id-ID') : '—') + '</div></div>'
            + '<div style="background:rgba(0,0,0,0.2);padding:4px 6px;border-radius:4px;"><div style="color:var(--text-muted);font-size:8px;">Ecer</div><div style="font-weight:600;color:var(--success);font-size:10px;">' + (ret > 0 ? 'Rp'+ret.toLocaleString('id-ID') : '—') + '</div>' + (mR !== null ? '<div style="color:'+cR+';font-size:8px;">'+mR+'%</div>' : '') + '</div>'
            + '<div style="background:rgba(0,0,0,0.2);padding:4px 6px;border-radius:4px;"><div style="color:var(--text-muted);font-size:8px;">Grosir</div><div style="font-weight:600;color:var(--warning);font-size:10px;">' + (who > 0 ? 'Rp'+who.toLocaleString('id-ID') : '—') + '</div>' + (mW !== null ? '<div style="color:'+cW+';font-size:8px;">'+mW+'%</div>' : '') + '</div>'
            + '</div></div>';
    }).join('');
    return '<div style="margin-top:8px;border-top:1px dashed rgba(255,255,255,0.08);padding-top:8px;"><div style="font-size:9px;color:var(--info);font-weight:600;margin-bottom:6px;"><i class="bi bi-info-circle-fill"></i> Info harga kemasan lain</div>' + rows + '</div>';
}

function renderCart() {
    emptyState.style.display = purchaseItems.length === 0 ? 'flex' : 'none';
    countBadge.textContent = `${purchaseItems.length} Item`;
    
    let html = '';
    purchaseItems.forEach(item => {
        const levelOptions = item.packagings.map(p => 
            `<option value="${p.level}" ${p.level == item.level ? 'selected' : ''}>${p.unit_name} (Isi ${p.base_qty})</option>`
        ).join('');

        const p_unit = item.packagings.find(p => p.level == item.level)?.unit_name || 'pcs';

        html += `
            <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--border-color);position:relative;">
                <button onclick="removeItem(${item.id})" style="position:absolute;top:12px;right:16px;background:none;border:none;color:var(--danger);font-size:1.2rem;cursor:pointer;"><i class="bi bi-x-circle-fill"></i></button>
                <div style="font-weight:600;font-size:var(--font-size-sm);margin-bottom:12px;padding-right:24px;">${item.name}</div>
                
                <div style="display:flex;gap:8px;margin-bottom:12px;">
                    <div style="flex:1;">
                        <label style="font-size:10px;color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:4px;">
                            <span>Kemasan</span>
                            <a href="<?= BASE_URL ?>settings/master-data" target="_blank" style="color:var(--info);text-decoration:none;"><i class="bi bi-box-arrow-up-right"></i> Master Data</a>
                        </label>
                        <select class="form-select-dark" style="width:100%;padding:8px;font-size:12px;" onchange="changeLevel(${item.id}, this.value)">
                            ${levelOptions}
                        </select>
                    </div>
                    <div style="width:70px;">
                        <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Qty</label>
                        <input type="number" class="form-control-dark" style="width:100%;padding:8px;font-size:12px;text-align:center;" value="${item.quantity}" min="1" oninput="updateItem(${item.id}, 'quantity', this.value)">
                    </div>
                </div>

                <div style="background:rgba(0,0,0,0.15);padding:10px;border-radius:var(--radius-sm);border:1px solid rgba(255,255,255,0.05);">
                    ${item.level > 1 ? `
                    <label class="price-custom-toggle buy-custom-toggle" style="margin-bottom:4px;" title="Centang untuk mengatur harga modal secara manual pada level ini">
                        <input type="checkbox" class="chk-buy-custom" onchange="toggleCustomPrice(${item.id}, 'buy', this.checked)">
                        <i class="bi bi-pencil-square" style="font-size:10px;"></i> Harga Modal Custom
                    </label>` : ''}
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        <div id="total_wrap_${item.id}" style="flex:1;${item.buy_price > 0 ? '' : 'display:none;'}">
                            <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Total Belanja (Otomatis hitung /pcs)</label>
                            <input type="number" id="total_input_${item.id}" class="form-control-dark" style="width:100%;padding:8px;font-size:12px;color:var(--info);background:rgba(0,0,0,0.2);" placeholder="Total Harga" value="${item.buy_price > 0 ? Math.round(item.total) : ''}" oninput="updateItemTotal(${item.id}, this.value)">
                            <div id="total_info_${item.id}" style="font-size:10px;margin-top:4px;min-height:14px;"></div>
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Harga Modal/Beli (per ${p_unit})</label>
                            <input type="number" class="form-control-dark buy-price pkg-buy" step="0.01" style="width:100%;padding:8px;font-size:12px;" value="${item.buy_price}" oninput="updateItem(${item.id}, 'buy_price', this.value)">
                        </div>
                    </div>
                    ${item.level > 1 ? `<div class="price-locked-note buy-locked-note" style="font-size:10px;color:var(--info);margin-top:-4px;margin-bottom:8px;"><i class="bi bi-link-45deg"></i> Otomatis dihitung dari harga pcs × isi kemasan</div>` : ''}

                    <div style="background:rgba(76,201,240,0.06);border:1px dashed rgba(76,201,240,0.3);border-radius:var(--radius-sm);padding:8px;margin-bottom:8px;">
                        <div style="font-size:10px;color:var(--info);font-weight:600;margin-bottom:6px;"><i class="bi bi-receipt"></i> PPN &amp; Diskon per Barang</div>
                        <div style="display:grid;grid-template-columns:1fr 2fr;gap:6px;margin-bottom:6px;">
                            <div>
                                <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">PPN (%)</label>
                                <input type="number" class="form-control-dark item-ppn" style="width:100%;padding:6px;font-size:11px;" value="${item.ppn_pct || 0}" min="0" max="100" placeholder="0" oninput="updateItemPpnDiskon(${item.id}, 'ppn', this.value)">
                            </div>
                            <div>
                                <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">Diskon</label>
                                <div style="display:flex;gap:4px;">
                                    <select class="form-select-dark item-diskon-mode" style="width:60px;padding:6px;font-size:10px;" onchange="updateItemPpnDiskon(${item.id}, 'mode', this.value)">
                                        <option value="rp" ${(item.diskon_mode||'rp')==='rp'?'selected':''}>Rp</option>
                                        <option value="pct" ${(item.diskon_mode||'rp')==='pct'?'selected':''}>%</option>
                                    </select>
                                    <input type="number" class="form-control-dark item-diskon-value" style="flex:1;padding:6px;font-size:11px;" value="${item.diskon_value || 0}" min="0" placeholder="0" oninput="updateItemPpnDiskon(${item.id}, 'diskon', this.value)">
                                </div>
                            </div>
                        </div>
                        <div id="nett_info_${item.id}" style="min-height:14px;">${buildNettInfo(item)}</div>
                    </div>

                    ${item.level > 1 ? `
                    <label class="price-custom-toggle sell-custom-toggle" style="margin-bottom:4px;" title="Centang untuk mengatur harga jual secara manual">
                        <input type="checkbox" class="chk-sell-custom" onchange="toggleCustomPrice(${item.id}, 'sell', this.checked)">
                        <i class="bi bi-tag" style="font-size:10px;"></i> Harga Jual Custom
                    </label>` : ''}
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        <div style="flex:1;">
                            <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Jual Ecer</label>
                            <input type="number" class="form-control-dark retail-price pkg-ret" style="width:100%;padding:8px;font-size:12px;color:var(--success);" value="${item.sell_price_retail}" oninput="updateItem(${item.id}, 'sell_price_retail', this.value)">
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Jual Grosir</label>
                            <input type="number" class="form-control-dark wholesale-price pkg-wholesale" style="width:100%;padding:8px;font-size:12px;color:var(--warning);" value="${item.sell_price_wholesale}" oninput="updateItem(${item.id}, 'sell_price_wholesale', this.value)">
                        </div>
                    </div>
                    <div id="margin_info_${item.id}" style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-bottom:8px;">
                        <span id="margin_retail_${item.id}">${formatMarginWithProfit('Ecer', item.harga_nett || item.buy_price, item.sell_price_retail)}</span>
                        <span id="margin_wholesale_${item.id}">${formatMarginWithProfit('Grosir', item.harga_nett || item.buy_price, item.sell_price_wholesale)}</span>
                    </div>
                    ${item.level > 1 ? `<div class="price-locked-note sell-locked-note" style="font-size:10px;color:var(--info);margin-top:-4px;margin-bottom:8px;"><i class="bi bi-link-45deg"></i> Otomatis dihitung dari harga pcs × isi kemasan</div>` : ''}
                    ${item.packagings.length > 1 ? `
                    <button type="button" onclick="openAllPackagingsModal(${item.id})" style="width:100%;background:var(--surface-2);color:var(--primary);border:1px dashed var(--border-color);padding:8px;border-radius:var(--radius-sm);font-size:11px;font-weight:600;cursor:pointer;">
                        <i class="bi bi-tags"></i> Atur Harga Kemasan Lainnya
                    </button>
                    ${buildPkgMiniSummaryHtml(item)}
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    if (purchaseItems.length > 0) {
        itemsContainer.innerHTML = html;
        itemsContainer.appendChild(emptyState);
    } else {
        itemsContainer.innerHTML = '';
        itemsContainer.appendChild(emptyState);
    }
    
    // Initialize locked notes as visible by default for non-custom prices
    purchaseItems.forEach(item => {
        if (item.level > 1) {
            const row = document.querySelector(`[oninput="updateItem(${item.id}, 'buy_price', this.value)"]`)?.closest('[style*="rgba(0,0,0,0.15)"]');
            if (row) {
                const pkg = item.packagings.find(p => p.level == item.level);
                const buyNote = row.querySelector('.buy-locked-note');
                const sellNote = row.querySelector('.sell-locked-note');
                const buyToggle = row.querySelector('.buy-custom-toggle');
                const sellToggle = row.querySelector('.sell-custom-toggle');
                
                // Set checkbox states based on pkg flags
                if (buyToggle) {
                    buyToggle.querySelector('input').checked = pkg.buy_custom || false;
                    buyToggle.classList.toggle('active', pkg.buy_custom || false);
                }
                if (sellToggle) {
                    sellToggle.querySelector('input').checked = pkg.sell_custom || false;
                    sellToggle.classList.toggle('active', pkg.sell_custom || false);
                }
                
                // Show locked notes by default if not custom
                if (buyNote) buyNote.style.display = pkg.buy_custom ? 'none' : 'block';
                if (sellNote) sellNote.style.display = pkg.sell_custom ? 'none' : 'block';
            }
        }
    });
    
    calculateTotal();
}

async function submitPurchase() {
    if (purchaseItems.length === 0) {
        showToast('❌ Daftar belanja masih kosong! Tambahkan minimal 1 produk', 'warning');
        return;
    }
    const salesVal = salesRepSB ? salesRepSB.getValue() : '';
    if (!salesVal) {
        showToast('❌ Pilih sales/supplier terlebih dahulu!', 'warning');
        return;
    }
    if (salesVal === 'other') isOtherMode = true;
    if (!isOtherMode && !currentSalesRepId) {
        showToast('❌ Pilih sales terlebih dahulu!', 'warning');
        return;
    }
    
    // Validate each purchase item
    for (let i = 0; i < purchaseItems.length; i++) {
        const item = purchaseItems[i];
        if (!item.product_id) {
            showToast(`❌ Item ${i + 1}: Produk tidak valid`, 'error');
            return;
        }
        if (!item.quantity || item.quantity <= 0) {
            showToast(`❌ Item ${i + 1}: Jumlah harus lebih dari 0`, 'error');
            return;
        }
        if (!item.buy_price || item.buy_price <= 0) {
            showToast(`❌ Item ${i + 1}: Harga modal harus diisi dan lebih dari 0`, 'error');
            return;
        }
    }
    
    const date = document.getElementById('purchaseDate').value;

    const btn = document.getElementById('btnSavePurchase');
    const prevText = btn.innerHTML;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
    btn.disabled = true;

    try {
        const payload = {
            supplier_id: isOtherMode ? null : (currentSupplierId || null),
            sales_rep_id: isOtherMode ? null : (currentSalesRepId || null),
            notes: isOtherMode ? 'Other — supplier/sales belum diketahui' : '',
            purchase_date: date,
            total_amount: currentSubtotal,
            grand_total: currentGrandTotal,
            invoice_photo_base64: invoicePhotoBase64,
            items: purchaseItems.map(i => ({
                product_id: i.product_id,
                level: i.level,
                quantity: i.quantity,
                buy_price: parseFloat(i.buy_price) || 0,
                sell_price_retail: parseFloat(i.sell_price_retail) || 0,
                sell_price_wholesale: parseFloat(i.sell_price_wholesale) || 0,
                ppn_pct: parseFloat(i.ppn_pct) || 0,
                diskon_mode: i.diskon_mode || 'rp',
                diskon_value: parseFloat(i.diskon_value) || 0,
                harga_nett: parseFloat(i.harga_nett) || parseFloat(i.buy_price) || 0,
                packagings: i.packagings.map(p => ({
                    level: p.level,
                    buy_price: parseFloat(p.buy_price) || 0,
                    sell_price_retail: parseFloat(p.sell_price_retail) || 0,
                    sell_price_wholesale: parseFloat(p.sell_price_wholesale) || 0,
                    ppn_pct: parseFloat(p.ppn_pct) || 0,
                    diskon_mode: p.diskon_mode || 'rp',
                    diskon_value: parseFloat(p.diskon_value) || 0
                }))
            }))
        };

        const res = await fetch(`${BASE_URL}api/purchases`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfVal
            },
            body: JSON.stringify(payload)
        });
        const result = await res.json();

        if (result.success) {
            showToast('✅ Pembelian berhasil disimpan!', 'success');
            setTimeout(() => window.location.href = `${BASE_URL}purchases`, 1500);
        } else {
            showToast('❌ ' + (result.error || 'Gagal menyimpan pembelian'), 'error');
            btn.innerHTML = prevText;
            btn.disabled = false;
        }
    } catch (err) {
        showToast('❌ Error: ' + (err.message || 'Terjadi kesalahan saat menyimpan'), 'error');
        console.error('Purchase error:', err);
        btn.innerHTML = prevText;
        btn.disabled = false;
    }
}

async function openBulkInputModal() {
    if (isOtherMode || !currentSupplierId) {
        showToast('Input bulk hanya untuk sales dengan supplier terpilih', 'warning');
        return;
    }
    
    // Fetch bulk products
    let products = [];
    try {
        let url = `${BASE_URL}api/suppliers/${currentSupplierId}/bulk-products`;
        if (currentSalesRepId) url += `?sales_rep_id=${currentSalesRepId}`;
        products = await api(url);
    } catch (e) {
        showToast('Gagal memuat daftar barang massal', 'error');
        return;
    }
    
    if (!products || products.length === 0) {
        showToast('Belum ada histori produk untuk supplier ini', 'warning');
        return;
    }
    
    // Build HTML table for bulk list
    let listHTML = products.map(p => {
        let pkgs = p.packagings || [];
        if (pkgs.length === 0) return '';
        
        let selectHtml = `<select class="bulk-pkg form-select-dark" style="width:100%; font-size:11px; padding:6px; margin-bottom:4px;" onchange="updateBulkPrices(this)">`;
        pkgs.forEach(pkg => {
            selectHtml += `<option value="${pkg.level}" data-pkg='${JSON.stringify(pkg).replace(/'/g, "&#39;")}'>${pkg.unit_name} (Isi ${pkg.base_qty})</option>`;
        });
        selectHtml += `</select>`;
        
        let defPkg = pkgs[0];
        
        let pkgEditHtml = '';
        if (pkgs.length > 1) {
            pkgEditHtml += `<div class="bulk-pkg-panel" style="display:none; margin-top:12px; border-top:1px dashed var(--border-color); padding-top:12px;">`;
            pkgEditHtml += `<div style="font-size:10px; color:var(--info); margin-bottom:8px;">Atur Harga Semua Kemasan:</div>`;
            pkgs.forEach((pkg, i) => {
                const isLevel1 = pkg.level == 1;
                pkgEditHtml += `
                    <div class="pkg-edit-row packaging-level-edit" data-level="${pkg.level}" data-base-qty="${pkg.base_qty}" style="background:rgba(0,0,0,0.1); padding:8px; border-radius:4px; margin-bottom:4px;">
                        <div style="font-size:11px; font-weight:600; margin-bottom:4px;">Level ${pkg.level}: ${pkg.unit_name} (Isi ${pkg.contained_qty || 1})</div>
                        <div style="background:rgba(0,0,0,0.15);padding:8px;border-radius:4px;">
                            ${!isLevel1 ? `
                            <label class="price-custom-toggle buy-custom-toggle" style="font-size:9px;" title="Centang untuk mengatur harga modal secara manual">
                                <input type="checkbox" class="chk-buy-custom" ${pkg.buy_custom ? 'checked' : ''}>
                                <i class="bi bi-pencil-square" style="font-size:9px;"></i> Custom
                            </label>` : ''}
                            <div style="display:flex; gap:6px; margin-bottom:4px;">
                                <div style="flex:1;"><label style="font-size:9px;color:var(--text-muted);">Modal</label><input type="number" class="pkg-buy buy-price form-control-dark" step="0.01" style="width:100%;font-size:10px;padding:4px;" value="${pkg.buy_price || 0}" oninput="onPkgModalInput(this, ${pkg.level})"></div>
                            </div>
                            ${!isLevel1 ? `<div class="price-locked-note buy-locked-note" style="font-size:8px;color:var(--info);margin-top:-2px;margin-bottom:4px;"><i class="bi bi-link-45deg"></i> Otomatis dari pcs × isi</div>` : ''}

                            <div style="background:rgba(76,201,240,0.06);border:1px dashed rgba(76,201,240,0.3);border-radius:4px;padding:6px;margin-bottom:6px;">
                                <div style="font-size:9px;color:var(--info);font-weight:600;margin-bottom:4px;"><i class="bi bi-receipt"></i> PPN &amp; Diskon</div>
                                <div style="display:grid;grid-template-columns:1fr 2fr;gap:4px;margin-bottom:4px;">
                                    <div>
                                        <label style="font-size:8px;color:var(--text-muted);display:block;margin-bottom:2px;">PPN (%)</label>
                                        <input type="number" class="pkg-ppn form-control-dark" style="width:100%;padding:3px;font-size:9px;" value="${pkg.ppn_pct || 0}" min="0" max="100" placeholder="0" oninput="onPkgModalInput(this, ${pkg.level})">
                                    </div>
                                    <div>
                                        <label style="font-size:8px;color:var(--text-muted);display:block;margin-bottom:2px;">Diskon</label>
                                        <div style="display:flex;gap:3px;">
                                            <select class="pkg-diskon-mode form-select-dark" style="width:40px;padding:3px;font-size:8px;" onchange="onPkgModalInput(this, ${pkg.level})">
                                                <option value="rp" ${(pkg.diskon_mode||'rp')==='rp'?'selected':''}>Rp</option>
                                                <option value="pct" ${(pkg.diskon_mode||'rp')==='pct'?'selected':''}>%</option>
                                            </select>
                                            <input type="number" class="pkg-diskon-value form-control-dark" style="flex:1;padding:3px;font-size:9px;" value="${pkg.diskon_value || 0}" min="0" placeholder="0" oninput="onPkgModalInput(this, ${pkg.level})">
                                        </div>
                                    </div>
                                </div>
                                <div class="pkg-nett-info" style="min-height:12px;font-size:8px;"></div>
                            </div>

                            ${!isLevel1 ? `
                            <label class="price-custom-toggle sell-custom-toggle" style="font-size:9px;" title="Centang untuk mengatur harga jual secara manual">
                                <input type="checkbox" class="chk-sell-custom" ${pkg.sell_custom ? 'checked' : ''}>
                                <i class="bi bi-tag" style="font-size:9px;"></i> Custom
                            </label>` : ''}
                            <div style="display:flex; gap:6px;">
                                <div style="flex:1;"><label style="font-size:9px;color:var(--text-muted);">Ecer</label><input type="number" class="pkg-ret retail-price pkg-ret form-control-dark" style="width:100%;font-size:10px;padding:4px;color:var(--success);" value="${pkg.sell_price_retail || 0}"></div>
                                <div style="flex:1;"><label style="font-size:9px;color:var(--text-muted);">Grosir</label><input type="number" class="pkg-whole wholesale-price pkg-wholesale form-control-dark" style="width:100%;font-size:10px;padding:4px;color:var(--warning);" value="${pkg.sell_price_wholesale || 0}"></div>
                            </div>
                            ${!isLevel1 ? `<div class="price-locked-note sell-locked-note" style="font-size:8px;color:var(--info);margin-top:-2px;"><i class="bi bi-link-45deg"></i> Otomatis dari pcs × isi</div>` : ''}
                            <div class="margin-calc pkg-margin-info" style="margin-top:4px;font-size:9px;color:var(--text-muted);display:flex;justify-content:space-between;">
                                <span class="margin-retail-text">Margin: 0%</span>
                                <span class="margin-wholesale-text">Margin: 0%</span>
                            </div>
                        </div>
                            
                            <!-- Tier Pricing for pkg-edit-row -->
                            <div style="margin-top:8px;border-top:1px dashed var(--border-color);padding-top:6px;">
                                <div style="font-size:10px;font-weight:600;color:var(--info);margin-bottom:4px;display:flex;justify-content:space-between;align-items:center;">
                                    <span><i class="bi bi-layers"></i> Harga Tier / Kuantitas</span>
                                    <button type="button" onclick="addBulkTierRow(this)" style="background:var(--info-bg);color:var(--info);border:none;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">+ Tambah</button>
                                </div>
                                <div style="font-size:9px;color:var(--text-muted);margin-bottom:3px;display:grid;grid-template-columns:1fr 1fr auto;gap:4px;"><span>Min Qty</span><span>Total Harga</span><span></span></div>
                                <div class="bulk-tier-rows"></div>
                            </div>
                        </div>
                    </div>
                `;
            });
            pkgEditHtml += `<button type="button" class="btn-outline-custom" style="width:100%; padding:4px; font-size:10px; margin-top:8px;" onclick="syncBulkPkgPanel(this)">Terapkan Harga Kemasan</button>`;
            pkgEditHtml += `</div>`;
            
            const otherPkgs = pkgs.filter(pkg => pkg.level != defPkg.level);
            let miniSummaryHtml = '';
            if (otherPkgs.length > 0) {
                const miniRows = otherPkgs.map(pkg => {
                    const buy  = parseFloat(pkg.buy_price) || 0;
                    const ret  = parseFloat(pkg.sell_price_retail) || 0;
                    const who  = parseFloat(pkg.sell_price_wholesale) || 0;
                    const mRet = (buy > 0 && ret > 0)  ? ((ret - buy) / ret * 100).toFixed(1)  : null;
                    const mWho = (buy > 0 && who > 0)  ? ((who - buy) / who * 100).toFixed(1)  : null;
                    const pRet = (ret > 0 && buy > 0)  ? Math.round(ret - buy)  : null;
                    const pWho = (who > 0 && buy > 0)  ? Math.round(who - buy)  : null;

                    const retColor = mRet !== null ? (parseFloat(mRet) >= 10 ? 'var(--success)' : parseFloat(mRet) >= 0 ? 'var(--warning)' : 'var(--danger)') : 'var(--text-muted)';
                    const whoColor = mWho !== null ? (parseFloat(mWho) >= 5  ? 'var(--success)' : parseFloat(mWho) >= 0 ? 'var(--warning)' : 'var(--danger)') : 'var(--text-muted)';

                    return `
                    <div class="bulk-pkg-mini-row" data-level="${pkg.level}" style="background:rgba(0,0,0,0.12); border-radius:6px; padding:6px 8px; margin-bottom:4px; border:1px solid rgba(255,255,255,0.04);">
                        <div style="font-size:9px; font-weight:700; color:var(--primary); margin-bottom:4px; letter-spacing:0.3px;">
                            <i class="bi bi-box-seam" style="font-size:8px;"></i> ${pkg.unit_name}
                            <span style="font-weight:400; color:var(--text-muted);">(Isi ${pkg.base_qty} pcs)</span>
                            ${pkg.buy_custom ? '<span style="font-size:8px;background:var(--warning-bg);color:var(--warning);padding:1px 4px;border-radius:4px;margin-left:4px;">Custom</span>' : '<span style="font-size:8px;background:var(--info-bg);color:var(--info);padding:1px 4px;border-radius:4px;margin-left:4px;"><i class="bi bi-link-45deg" style="font-size:7px;"></i> Otomatis</span>'}
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:4px; font-size:9px;">
                            <div style="background:rgba(0,0,0,0.2); padding:4px 6px; border-radius:4px; border:1px solid rgba(255,255,255,0.05);">
                                <div style="color:var(--text-muted); font-size:8px; margin-bottom:2px;">Modal</div>
                                <div style="font-weight:600; color:var(--text-primary); font-size:10px;">${buy > 0 ? 'Rp' + buy.toLocaleString('id-ID') : '<span style="color:var(--text-muted);">—</span>'}</div>
                            </div>
                            <div style="background:rgba(0,0,0,0.2); padding:4px 6px; border-radius:4px; border:1px solid rgba(255,255,255,0.05);">
                                <div style="color:var(--text-muted); font-size:8px; margin-bottom:2px;">Ecer</div>
                                <div style="font-weight:600; color:var(--success); font-size:10px;">${ret > 0 ? 'Rp' + ret.toLocaleString('id-ID') : '<span style="color:var(--text-muted);">—</span>'}</div>
                                ${mRet !== null ? `<div style="color:${retColor}; font-size:8px;">${mRet}% &nbsp;+Rp${pRet.toLocaleString('id-ID')}</div>` : ''}
                            </div>
                            <div style="background:rgba(0,0,0,0.2); padding:4px 6px; border-radius:4px; border:1px solid rgba(255,255,255,0.05);">
                                <div style="color:var(--text-muted); font-size:8px; margin-bottom:2px;">Grosir</div>
                                <div style="font-weight:600; color:var(--warning); font-size:10px;">${who > 0 ? 'Rp' + who.toLocaleString('id-ID') : '<span style="color:var(--text-muted);">—</span>'}</div>
                                ${mWho !== null ? `<div style="color:${whoColor}; font-size:8px;">${mWho}% &nbsp;+Rp${pWho.toLocaleString('id-ID')}</div>` : ''}
                            </div>
                        </div>
                    </div>`;
                }).join('');

                miniSummaryHtml = `
                <div class="bulk-pkg-mini-summary" style="margin-top:10px; border-top:1px dashed rgba(255,255,255,0.08); padding-top:8px;">
                    <div style="font-size:9px; color:var(--text-muted); margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                        <i class="bi bi-info-circle-fill" style="color:var(--info); font-size:9px;"></i>
                        <span style="font-weight:600; color:var(--info);">Info harga kemasan lain</span>
                        <span style="color:var(--text-muted);">— ubah jika perlu</span>
                    </div>
                    ${miniRows}
                </div>`;
            }

            pkgEditHtml += miniSummaryHtml;

            pkgEditHtml += `<button type="button" class="btn-outline-custom" style="width:100%; padding:6px; font-size:10px; margin-top:6px; border-style:dashed; border-color:var(--primary); color:var(--primary);" onclick="openBulkPkgPanel(this)">
                <i class="bi bi-tags"></i> Ubah Harga Kemasan Lainnya
            </button>`;
        }
        
        return `
        <div class="bulk-item" data-product='${JSON.stringify(p).replace(/'/g, "&#39;")}' style="background:var(--surface-2); padding:10px; border-radius:var(--radius-sm); margin-bottom:8px;">
            <div style="font-weight:600; font-size:12px; margin-bottom:8px; color:var(--text-primary);">${p.full_name || p.short_label}</div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                <div>
                    <label style="font-size:10px;color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:2px;">
                        <span>Kemasan Beli</span>
                        <a href="<?= BASE_URL ?>settings/master-data" target="_blank" style="color:var(--info);text-decoration:none;"><i class="bi bi-box-arrow-up-right"></i> Master</a>
                    </label>
                    ${selectHtml}
                </div>
                <div>
                    <label style="font-size:10px;color:var(--text-muted);">Qty Beli</label>
                    <input type="number" class="bulk-qty form-control-dark" style="width:100%; font-size:11px;" placeholder="0" min="0" oninput="calcBulkFromTotal(this)">
                </div>
                <div>
                    <label style="font-size:10px;color:var(--text-muted);">Total Harga</label>
                    <input type="number" class="bulk-total form-control-dark" style="width:100%; font-size:11px; color:var(--primary);" placeholder="Total" min="0" oninput="calcBulkFromTotal(this)">
                </div>
            </div>
            
            <div style="background:rgba(0,0,0,0.15); padding:8px; border-radius:var(--radius-sm); margin-top:8px;">
                <div style="margin-bottom:8px;">
                    <label style="font-size:10px;color:var(--text-muted);">Harga Modal / Beli</label>
                    <div style="position:relative;">
                        <input type="number" class="bulk-buy form-control-dark" step="0.01" style="width:100%; font-size:11px; padding-right:24px;" value="${defPkg.buy_price || p.last_buy_price || 0}" data-last-buy="${defPkg.buy_price || p.last_buy_price || 0}" oninput="calcBulkMargin(this); syncTotalFromBuy(this)">
                        <span class="buy-indicator" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:12px;"></span>
                    </div>
                </div>

                <div style="background:rgba(76,201,240,0.06);border:1px dashed rgba(76,201,240,0.3);border-radius:4px;padding:7px;margin-bottom:8px;">
                    <div style="font-size:10px;color:var(--info);font-weight:600;margin-bottom:5px;"><i class="bi bi-receipt"></i> PPN &amp; Diskon</div>
                    <div style="display:grid;grid-template-columns:1fr 2fr;gap:5px;margin-bottom:5px;">
                        <div>
                            <label style="font-size:9px;color:var(--text-muted);display:block;margin-bottom:2px;">PPN (%)</label>
                            <input type="number" class="bulk-ppn form-control-dark" style="width:100%;font-size:10px;padding:4px;" value="${defPkg.ppn_pct || 0}" min="0" max="100" placeholder="0" oninput="calcBulkMargin(this)">
                        </div>
                        <div>
                            <label style="font-size:9px;color:var(--text-muted);display:block;margin-bottom:2px;">Diskon</label>
                            <div style="display:flex;gap:3px;">
                                <select class="bulk-diskon-mode form-select-dark" style="width:52px;font-size:9px;padding:4px;" onchange="calcBulkMargin(this)">
                                    <option value="rp" ${(defPkg.diskon_mode||'rp')==='rp'?'selected':''}>Rp</option>
                                    <option value="pct" ${(defPkg.diskon_mode||'rp')==='pct'?'selected':''}>%</option>
                                </select>
                                <input type="number" class="bulk-diskon-value form-control-dark" style="flex:1;font-size:10px;padding:4px;" value="${defPkg.diskon_value || 0}" min="0" placeholder="0" oninput="calcBulkMargin(this)">
                            </div>
                        </div>
                    </div>
                    <div class="bulk-nett-info" style="min-height:12px;font-size:9px;"></div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div>
                        <label style="font-size:10px;color:var(--text-muted);">Jual Ecer</label>
                        <div style="position:relative;">
                            <input type="number" class="bulk-ret form-control-dark" style="width:100%; font-size:11px; color:var(--success); padding-right:24px;" value="${defPkg.sell_price_retail || 0}" data-last-ret="${defPkg.sell_price_retail || 0}" oninput="calcBulkMargin(this)">
                            <span class="ret-indicator" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:12px;"></span>
                        </div>
                        <div class="bulk-margin-ret" style="font-size:9px; color:var(--text-muted); margin-top:2px;">Margin: 0%</div>
                    </div>
                    <div>
                        <label style="font-size:10px;color:var(--text-muted);">Jual Grosir</label>
                        <div style="position:relative;">
                            <input type="number" class="bulk-whole form-control-dark" style="width:100%; font-size:11px; color:var(--warning); padding-right:24px;" value="${defPkg.sell_price_wholesale || 0}" data-last-whole="${defPkg.sell_price_wholesale || 0}" oninput="calcBulkMargin(this)">
                            <span class="whole-indicator" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:12px;"></span>
                        </div>
                        <div class="bulk-margin-whole" style="font-size:9px; color:var(--text-muted); margin-top:2px;">Margin: 0%</div>
                    </div>
                </div>
                <!-- Tier Pricing for main level -->
                <div style="margin-top:8px;border-top:1px dashed var(--border-color);padding-top:6px;">
                    <div style="font-size:10px;font-weight:600;color:var(--info);margin-bottom:4px;display:flex;justify-content:space-between;align-items:center;">
                        <span><i class="bi bi-layers"></i> Harga Tier / Kuantitas</span>
                        <button type="button" onclick="addBulkTierRow(this)" style="background:var(--info-bg);color:var(--info);border:none;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">+ Tambah</button>
                    </div>
                    <div style="font-size:9px;color:var(--text-muted);margin-bottom:3px;display:grid;grid-template-columns:1fr 1fr auto;gap:4px;"><span>Min Qty</span><span>Total Harga</span><span></span></div>
                    <div class="bulk-tier-rows"></div>
                </div>
            </div>
            ${pkgEditHtml}
        </div>
        `;
    }).join('');
    
    AppModal.show({
        title: 'Input Barang Massal',
        subtitle: `Isi Qty > 0 untuk produk yang dibeli`,
        bodyHTML: `
            <div style="margin-bottom:12px;">
                <div style="background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:0 12px; display:flex; align-items:center;">
                    <i class="bi bi-search" style="color:var(--text-muted);font-size:12px;"></i>
                    <input type="text" placeholder="Cari nama produk..." style="flex:1;border:none;background:transparent;padding:10px;color:var(--text-primary);font-size:12px;outline:none;" oninput="filterBulkModal(this.value)" autocomplete="off">
                </div>
            </div>
            <div style="max-height:55vh; overflow-y:auto; overflow-x:hidden; padding-right:4px;">${listHTML}</div>
        `,
        submitText: 'Tambahkan Terpilih',
        onShown: () => {
            // Initialize PackagingPriceSync for bulk modal panels
            if (typeof PackagingPriceSync !== 'undefined') {
                document.querySelectorAll('.bulk-pkg-panel').forEach(panel => {
                    const levels = panel.querySelectorAll('.packaging-level-edit');
                    levels.forEach(lv => PackagingPriceSync.bindLevel(lv));
                });
            }
            // Initialize locked notes as visible by default
            document.querySelectorAll('.bulk-pkg-panel .price-locked-note').forEach(note => {
                note.style.display = 'block';
            });
        },
        onSubmit: () => {
            const items = document.querySelectorAll('.bulk-item');
            let addedCount = 0;
            items.forEach(item => {
                const qtyInput = item.querySelector('.bulk-qty');
                const qty = parseInt(qtyInput.value) || 0;
                if (qty > 0) {
                    const productStr = item.getAttribute('data-product');
                    const product = JSON.parse(productStr);
                    const pkgSelect = item.querySelector('.bulk-pkg');
                    const selectedPkgStr = pkgSelect.options[pkgSelect.selectedIndex].getAttribute('data-pkg');
                    const selectedPkg = JSON.parse(selectedPkgStr);
                    
                    const buyPrice = parseFloat(item.querySelector('.bulk-buy').value) || 0;
                    const retPrice = parseFloat(item.querySelector('.bulk-ret').value) || 0;
                    const wholePrice = parseFloat(item.querySelector('.bulk-whole').value) || 0;
                    
                    // Construct updated packagings if the user edited them inline
                    let updatedPkgs = [...product.packagings];
                    const editRows = item.querySelectorAll('.pkg-edit-row');
                    if (editRows.length > 0) {
                        editRows.forEach(row => {
                            const lvl = parseInt(row.getAttribute('data-level'));
                            const pb = parseFloat(row.querySelector('.pkg-buy').value) || 0;
                            const pr = parseFloat(row.querySelector('.pkg-ret').value) || 0;
                            const pw = parseFloat(row.querySelector('.pkg-whole').value) || 0;
                            
                            const pp = parseFloat(row.querySelector('.pkg-ppn').value) || 0;
                            const dm = row.querySelector('.pkg-diskon-mode').value || 'rp';
                            const dv = parseFloat(row.querySelector('.pkg-diskon-value').value) || 0;
                            const hn = calcItemNett(pb, pp, dm, dv);

                            const pIndex = updatedPkgs.findIndex(p => p.level == lvl);
                            if (pIndex > -1) {
                                updatedPkgs[pIndex].buy_price = pb;
                                updatedPkgs[pIndex].sell_price_retail = pr;
                                updatedPkgs[pIndex].sell_price_wholesale = pw;
                                updatedPkgs[pIndex].ppn_pct = pp;
                                updatedPkgs[pIndex].diskon_mode = dm;
                                updatedPkgs[pIndex].diskon_value = dv;
                                updatedPkgs[pIndex].harga_nett = hn;
                            }
                        });
                        // also sync the selected level with the main inputs
                        const curIndex = updatedPkgs.findIndex(p => p.level == selectedPkg.level);
                        if (curIndex > -1) {
                            updatedPkgs[curIndex].buy_price = buyPrice;
                            updatedPkgs[curIndex].sell_price_retail = retPrice;
                            updatedPkgs[curIndex].sell_price_wholesale = wholePrice;
                            
                            updatedPkgs[curIndex].ppn_pct = parseFloat(item.querySelector('.bulk-ppn').value) || 0;
                            updatedPkgs[curIndex].diskon_mode = item.querySelector('.bulk-diskon-mode').value || 'rp';
                            updatedPkgs[curIndex].diskon_value = parseFloat(item.querySelector('.bulk-diskon-value').value) || 0;
                            updatedPkgs[curIndex].harga_nett = calcItemNett(buyPrice, updatedPkgs[curIndex].ppn_pct, updatedPkgs[curIndex].diskon_mode, updatedPkgs[curIndex].diskon_value);
                        }
                    } else {
                        // Just update the selected packaging
                        const curIndex = updatedPkgs.findIndex(p => p.level == selectedPkg.level);
                        if (curIndex > -1) {
                            updatedPkgs[curIndex].buy_price = buyPrice;
                            updatedPkgs[curIndex].sell_price_retail = retPrice;
                            updatedPkgs[curIndex].sell_price_wholesale = wholePrice;
                            
                            updatedPkgs[curIndex].ppn_pct = parseFloat(item.querySelector('.bulk-ppn').value) || 0;
                            updatedPkgs[curIndex].diskon_mode = item.querySelector('.bulk-diskon-mode').value || 'rp';
                            updatedPkgs[curIndex].diskon_value = parseFloat(item.querySelector('.bulk-diskon-value').value) || 0;
                            updatedPkgs[curIndex].harga_nett = calcItemNett(buyPrice, updatedPkgs[curIndex].ppn_pct, updatedPkgs[curIndex].diskon_mode, updatedPkgs[curIndex].diskon_value);
                        }
                    }
                    
                    const existingItem = purchaseItems.find(i => i.product_id === product.id && i.level == selectedPkg.level);
                    if (existingItem) {
                        existingItem.quantity += qty;
                        existingItem.buy_price = buyPrice;
                        existingItem.sell_price_retail = retPrice;
                        existingItem.sell_price_wholesale = wholePrice;
                        existingItem.total = existingItem.quantity * buyPrice;
                        existingItem.packagings = updatedPkgs;
                        
                        existingItem.ppn_pct = parseFloat(item.querySelector('.bulk-ppn').value) || 0;
                        existingItem.diskon_mode = item.querySelector('.bulk-diskon-mode').value || 'rp';
                        existingItem.diskon_value = parseFloat(item.querySelector('.bulk-diskon-value').value) || 0;
                        existingItem.harga_nett = calcItemNett(buyPrice, existingItem.ppn_pct, existingItem.diskon_mode, existingItem.diskon_value);
                    } else {
                        purchaseItems.unshift({
                            id: Date.now() + Math.random(),
                            product_id: product.id,
                            name: product.full_name || product.short_label,
                            packagings: updatedPkgs,
                            level: selectedPkg.level,
                            unit_name: selectedPkg.unit_name,
                            quantity: qty,
                            buy_price: buyPrice,
                            sell_price_retail: retPrice,
                            sell_price_wholesale: wholePrice,
                            total: qty * buyPrice,
                            ppn_pct: parseFloat(item.querySelector('.bulk-ppn').value) || 0,
                            diskon_mode: item.querySelector('.bulk-diskon-mode').value || 'rp',
                            diskon_value: parseFloat(item.querySelector('.bulk-diskon-value').value) || 0,
                            harga_nett: calcItemNett(buyPrice, parseFloat(item.querySelector('.bulk-ppn').value) || 0, item.querySelector('.bulk-diskon-mode').value || 'rp', parseFloat(item.querySelector('.bulk-diskon-value').value) || 0)
                        });
                    }
                    addedCount++;
                }
            });
            if (addedCount > 0) {
                renderCart();
                showToast(`${addedCount} produk ditambahkan`, 'success');
                return true;
            } else {
                showToast('Belum ada barang yang diisi Qty', 'warning');
                return false;
            }
        }
    });
    // Calculate margins immediately on load
    setTimeout(() => {
        document.querySelectorAll('.bulk-item').forEach(item => {
            calcBulkMargin(item.querySelector('.bulk-buy'));
        });
    }, 100);
}

function calcBulkMargin(inputEl) {
    const item = inputEl.closest('.bulk-item');
    const buyInput = item.querySelector('.bulk-buy');
    const retInput = item.querySelector('.bulk-ret');
    const wholeInput = item.querySelector('.bulk-whole');
    const buy = parseFloat(buyInput.value) || 0;
    const ret = parseFloat(retInput.value) || 0;
    const whole = parseFloat(wholeInput.value) || 0;

    // Calculate nett price with PPN & Diskon
    const ppn = parseFloat(item.querySelector('.bulk-ppn')?.value) || 0;
    const diskonMode = item.querySelector('.bulk-diskon-mode')?.value || 'rp';
    const diskonVal = parseFloat(item.querySelector('.bulk-diskon-value')?.value) || 0;
    const nett = calcItemNett(buy, ppn, diskonMode, diskonVal);

    // Update nett display
    const nettEl = item.querySelector('.bulk-nett-info');
    if (nettEl) {
        if (ppn > 0 || diskonVal > 0) {
            const ppnAmt = buy * ppn / 100;
            const diskonAmt = diskonMode === 'pct' ? buy * diskonVal / 100 : diskonVal;
            nettEl.innerHTML = '<span style="color:var(--text-muted);">Modal: Rp' + Math.round(buy).toLocaleString('id-ID') + '</span>'
                + (ppn > 0 ? ' <span style="color:var(--warning);">+PPN: Rp' + Math.round(ppnAmt).toLocaleString('id-ID') + '</span>' : '')
                + (diskonVal > 0 ? ' <span style="color:var(--success);">\u2212Diskon: Rp' + Math.round(diskonAmt).toLocaleString('id-ID') + '</span>' : '')
                + ' \u2192 <strong style="color:var(--info);">Nett: Rp' + Math.round(nett).toLocaleString('id-ID') + '</strong>';
        } else { nettEl.innerHTML = ''; }
    }

    // Update indicators
    const setInd = (input, selector) => {
        const last = parseFloat(input.getAttribute('data-last-' + selector.split('-')[1])) || 0;
        const ind = item.querySelector('.' + selector.split('-')[1] + '-indicator');
        if (!ind) return;
        const val = parseFloat(input.value) || 0;
        if (last === 0) ind.innerHTML = '';
        else if (val > last) ind.innerHTML = '<i class="bi bi-arrow-up-right text-danger" title="Naik"></i>';
        else if (val < last) ind.innerHTML = '<i class="bi bi-arrow-down-right text-success" title="Turun"></i>';
        else ind.innerHTML = '<i class="bi bi-dash text-muted" title="Sama"></i>';
    };
    if (inputEl.classList.contains('bulk-buy')) setInd(buyInput, 'bulk-buy');
    if (inputEl.classList.contains('bulk-ret')) setInd(retInput, 'bulk-ret');
    if (inputEl.classList.contains('bulk-whole')) setInd(wholeInput, 'bulk-whole');
    
    const retMarginEl = item.querySelector('.bulk-margin-ret');
    if (retMarginEl) {
        if (buy > 0 && ret > 0) {
            const m = ((ret - nett) / ret * 100).toFixed(1);
            const profit = Math.round(ret - nett);
            const color = m >= 10 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)');
            retMarginEl.innerHTML = `Margin(Nett): <span style="color:${color};font-weight:600;">${m}%</span> <span style="font-size:8px;color:var(--text-muted);">(+Rp${profit.toLocaleString('id-ID')})</span>`;
        } else { retMarginEl.innerHTML = 'Margin: 0%'; }
    }
    
    const wholeMarginEl = item.querySelector('.bulk-margin-whole');
    if (wholeMarginEl) {
        if (buy > 0 && whole > 0) {
            const m = ((whole - nett) / whole * 100).toFixed(1);
            const profit = Math.round(whole - nett);
            const color = m >= 5 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)');
            wholeMarginEl.innerHTML = `Margin(Nett): <span style="color:${color};font-weight:600;">${m}%</span> <span style="font-size:8px;color:var(--text-muted);">(+Rp${profit.toLocaleString('id-ID')})</span>`;
        } else { wholeMarginEl.innerHTML = 'Margin: 0%'; }
    }
}

function calcBulkFromTotal(inputEl) {
    const item = inputEl.closest('.bulk-item');
    const qty = parseFloat(item.querySelector('.bulk-qty').value) || 0;
    const total = parseFloat(item.querySelector('.bulk-total').value) || 0;
    const buyInput = item.querySelector('.bulk-buy');
    
    if (inputEl.classList.contains('bulk-total') && qty > 0) {
        buyInput.value = Math.round(total / qty);
        calcBulkMargin(buyInput);
    } else if (inputEl.classList.contains('bulk-qty') && total > 0) {
        buyInput.value = qty > 0 ? Math.round(total / qty) : 0;
        calcBulkMargin(buyInput);
    } else if (inputEl.classList.contains('bulk-qty') && parseFloat(buyInput.value) > 0) {
        item.querySelector('.bulk-total').value = Math.round(qty * parseFloat(buyInput.value));
    }
}

/** Open pkg panel and sync current prices into it */
function openBulkPkgPanel(btn) {
    const bulkItem = btn.closest('.bulk-item');
    const panel = bulkItem.querySelector('.bulk-pkg-panel');
    const isHidden = panel.style.display === 'none' || !panel.style.display;
    panel.style.display = isHidden ? 'block' : 'none';
    if (!isHidden) return;
    // Sync main prices into panel rows
    const pkgSelect = bulkItem.querySelector('.bulk-pkg');
    const selectedPkg = JSON.parse(pkgSelect.options[pkgSelect.selectedIndex].getAttribute('data-pkg') || '{}');
    const selectedBaseQty = parseFloat(selectedPkg.base_qty) || 1;
    const buy = parseFloat(bulkItem.querySelector('.bulk-buy').value) || 0;
    const ret = parseFloat(bulkItem.querySelector('.bulk-ret').value) || 0;
    const whole = parseFloat(bulkItem.querySelector('.bulk-whole').value) || 0;
    const buyPerPcs = buy / selectedBaseQty;
    const retPerPcs = ret / selectedBaseQty;
    const wholePerPcs = whole / selectedBaseQty;
    bulkItem.querySelectorAll('.pkg-edit-row').forEach(row => {
        const baseQty = parseFloat(row.getAttribute('data-base-qty')) || 1;
        const isCustomBuy = row.querySelector('.chk-buy-custom')?.checked;
        const isCustomSell = row.querySelector('.chk-sell-custom')?.checked;
        if (!isCustomBuy) { const el = row.querySelector('.pkg-buy'); if (el) el.value = Math.round(buyPerPcs * baseQty); }
        if (!isCustomSell) {
            const r = row.querySelector('.pkg-ret'); if (r) r.value = Math.round(retPerPcs * baseQty);
            const w = row.querySelector('.pkg-whole'); if (w) w.value = Math.round(wholePerPcs * baseQty);
        }
        
        // Also sync PPN and diskon to the packaging panel row
        const mainPpn = parseFloat(bulkItem.querySelector('.bulk-ppn').value) || 0;
        const mainDiskonMode = bulkItem.querySelector('.bulk-diskon-mode').value || 'rp';
        const mainDiskonVal = parseFloat(bulkItem.querySelector('.bulk-diskon-value').value) || 0;
        
        const rowPpnInput = row.querySelector('.pkg-ppn');
        const rowDiskonModeSelect = row.querySelector('.pkg-diskon-mode');
        const rowDiskonValInput = row.querySelector('.pkg-diskon-value');
        
        if (rowPpnInput) rowPpnInput.value = mainPpn;
        if (rowDiskonModeSelect) rowDiskonModeSelect.value = mainDiskonMode;
        if (rowDiskonValInput) rowDiskonValInput.value = mainDiskonVal;

        // Update margin display
        const buyInput = row.querySelector('.pkg-buy');
        if (buyInput) onPkgModalInput(buyInput, row.getAttribute('data-level'));
    });
    if (typeof PackagingPriceSync !== 'undefined') {
        const levels = panel.querySelectorAll('.packaging-level-edit');
        levels.forEach(lv => PackagingPriceSync.bindLevel(lv));
    }
}

/** Add a tier row to a bulk item's tier container */
function addBulkTierRow(btn) {
    const container = btn.parentElement.parentElement.querySelector('.bulk-tier-rows');
    if (!container) return;
    const row = document.createElement('div');
    row.className = 'bulk-tier-row';
    row.style.cssText = 'display:grid;grid-template-columns:1fr 1fr auto;gap:4px;margin-bottom:3px;align-items:center;';
    row.innerHTML = `
        <input type="number" class="form-control-dark bulk-tier-min-qty" style="font-size:10px;padding:4px;" placeholder="Min Qty" min="1">
        <input type="number" class="form-control-dark bulk-tier-total" style="font-size:10px;padding:4px;color:var(--success);" placeholder="Total Harga" min="0">
        <button type="button" onclick="this.closest('.bulk-tier-row').remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:3px 7px;cursor:pointer;font-size:11px;"><i class="bi bi-x"></i></button>`;
    container.appendChild(row);
}

function syncTotalFromBuy(buyInput) {
    const item = buyInput.closest('.bulk-item');
    const qty = parseFloat(item.querySelector('.bulk-qty').value) || 0;
    const buy = parseFloat(buyInput.value) || 0;
    const totalInput = item.querySelector('.bulk-total');
    if (qty > 0 && buy > 0) {
        totalInput.value = Math.round(qty * buy);
    }
}

function filterBulkModal(keyword) {
    const term = keyword.toLowerCase();
    const items = document.querySelectorAll('.bulk-item');
    items.forEach(item => {
        const productData = JSON.parse(item.getAttribute('data-product'));
        const searchStr = `${productData.full_name || ''} ${productData.short_label || ''}`.toLowerCase();
        if (searchStr.includes(term)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function syncBulkPkgPanel(btn) {
    // If the user clicks "Terapkan", we can sync the matching level to the main inputs
    const item = btn.closest('.bulk-item');
    const pkgSelect = item.querySelector('.bulk-pkg');
    const selectedLevel = pkgSelect.value;
    
    const editRow = item.querySelector(`.pkg-edit-row[data-level="${selectedLevel}"]`);
    if (editRow) {
        item.querySelector('.bulk-buy').value = editRow.querySelector('.pkg-buy').value;
        item.querySelector('.bulk-ret').value = editRow.querySelector('.pkg-ret').value;
        item.querySelector('.bulk-whole').value = editRow.querySelector('.pkg-whole').value;
        
        item.querySelector('.bulk-ppn').value = editRow.querySelector('.pkg-ppn').value;
        item.querySelector('.bulk-diskon-mode').value = editRow.querySelector('.pkg-diskon-mode').value;
        item.querySelector('.bulk-diskon-value').value = editRow.querySelector('.pkg-diskon-value').value;
        
        calcBulkMargin(item.querySelector('.bulk-buy'));
    }
    btn.closest('.bulk-pkg-panel').style.display = 'none';
    showToast('Harga kemasan diterapkan', 'success');
}

function updateBulkPrices(selectEl) {
    const pkg = JSON.parse(selectEl.options[selectEl.selectedIndex].getAttribute('data-pkg'));
    const item = selectEl.closest('.bulk-item');
    
    // Attempt to read from the inline edit panel first if it exists, so we don't overwrite user edits
    const editRow = item.querySelector(`.pkg-edit-row[data-level="${pkg.level}"]`);
    if (editRow) {
        item.querySelector('.bulk-buy').value = editRow.querySelector('.pkg-buy').value;
        item.querySelector('.bulk-ret').value = editRow.querySelector('.pkg-ret').value;
        item.querySelector('.bulk-whole').value = editRow.querySelector('.pkg-whole').value;
        
        item.querySelector('.bulk-ppn').value = editRow.querySelector('.pkg-ppn').value;
        item.querySelector('.bulk-diskon-mode').value = editRow.querySelector('.pkg-diskon-mode').value;
        item.querySelector('.bulk-diskon-value').value = editRow.querySelector('.pkg-diskon-value').value;
    } else {
        item.querySelector('.bulk-buy').value = pkg.buy_price || 0;
        item.querySelector('.bulk-ret').value = pkg.sell_price_retail || 0;
        item.querySelector('.bulk-whole').value = pkg.sell_price_wholesale || 0;
        
        item.querySelector('.bulk-ppn').value = pkg.ppn_pct || 0;
        item.querySelector('.bulk-diskon-mode').value = pkg.diskon_mode || 'rp';
        item.querySelector('.bulk-diskon-value').value = pkg.diskon_value || 0;
    }
    calcBulkMargin(selectEl);
}

</script>
