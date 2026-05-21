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
let bulkItems = []; // module-level so global handlers (onBulkMainChange, onBulkLevelChange) can access it
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



// ═══════════════════════════════════════════════════════════
// UNIFIED PRICE PROPAGATION ENGINE
// ═══════════════════════════════════════════════════════════

/**
 * Propagate buy price/PPN/diskon from the selected packaging level
 * to all other levels, respecting custom flags.
 * @param {object} item - purchase item object
 */
function propagateFromMainInputs(item) {
    const selPkg = item.packagings.find(p => p.level == item.level);
    if (!selPkg) return;

    const selBaseQty = parseFloat(selPkg.base_qty) || 1;
    const buyPrice   = parseFloat(item.buy_price) || 0;
    const buyPerPcs  = buyPrice / selBaseQty;

    item.packagings.forEach(pkg => {
        const bq = parseFloat(pkg.base_qty) || 1;

        // --- Buy Price ---
        if (!pkg.buy_custom) {
            pkg.buy_price = Math.round(buyPerPcs * bq);
        }

        // --- PPN (uniform) ---
        pkg.ppn_pct = item.ppn_pct || 0;

        // --- Diskon ---
        if (item.diskon_mode === 'pct') {
            pkg.diskon_mode  = 'pct';
            pkg.diskon_value = item.diskon_value || 0;
        } else {
            // Rupiah: scale proportionally by base_qty ratio
            pkg.diskon_mode  = 'rp';
            pkg.diskon_value = Math.round((item.diskon_value || 0) * (bq / selBaseQty));
        }

        // --- Nett ---
        pkg.harga_nett = calcItemNett(pkg.buy_price, pkg.ppn_pct, pkg.diskon_mode, pkg.diskon_value);

        // --- Sell prices (if not custom, scale from selected level) ---
        if (!pkg.sell_custom) {
            const selRetPerPcs = (parseFloat(selPkg.sell_price_retail) || 0) / selBaseQty;
            const selWhoPerPcs = (parseFloat(selPkg.sell_price_wholesale) || 0) / selBaseQty;
            pkg.sell_price_retail    = Math.round(selRetPerPcs * bq);
            pkg.sell_price_wholesale = Math.round(selWhoPerPcs * bq);
        }
    });
}

/**
 * Build the unified mini pricing table HTML (all packaging levels)
 * @param {object} item - purchase item object
 */
function buildMiniPricingTableHtml(item) {
    if (!item.packagings || item.packagings.length === 0) return '';

    const rows = item.packagings.map(pkg => {
        const buy  = parseFloat(pkg.buy_price) || 0;
        const ppn  = parseFloat(pkg.ppn_pct) || 0;
        const dm   = pkg.diskon_mode || 'rp';
        const dv   = parseFloat(pkg.diskon_value) || 0;
        const nett = calcItemNett(buy, ppn, dm, dv);
        const ret  = parseFloat(pkg.sell_price_retail) || 0;
        const who  = parseFloat(pkg.sell_price_wholesale) || 0;

        const mR = (nett > 0 && ret > 0) ? ((ret - nett) / ret * 100) : null;
        const mW = (nett > 0 && who > 0) ? ((who - nett) / who * 100) : null;
        const cR = mR !== null ? (mR >= 10 ? 'var(--success)' : mR >= 0 ? 'var(--warning)' : 'var(--danger)') : 'var(--text-muted)';
        const cW = mW !== null ? (mW >= 5  ? 'var(--success)' : mW >= 0 ? 'var(--warning)' : 'var(--danger)') : 'var(--text-muted)';
        const isSelected = (pkg.level == item.level);

        return `<tr style="${isSelected ? 'background:rgba(230,57,70,0.08);' : ''}">
            <td style="padding:5px 6px;font-size:10px;font-weight:600;color:${isSelected ? 'var(--primary)' : 'var(--text-muted)'}">
                ${isSelected ? '<i class="bi bi-arrow-right-short"></i>' : ''} ${pkg.unit_name}
                <span style="font-size:9px;font-weight:400;color:var(--text-muted);">×${pkg.base_qty}</span>
            </td>
            <td style="padding:5px 6px;font-size:10px;text-align:right;">
                <span style="font-weight:700;">${nett > 0 ? 'Rp' + Math.round(nett).toLocaleString('id-ID') : '—'}</span>
                ${ppn > 0 || dv > 0 ? `<div style="font-size:8px;color:var(--text-muted);">(+${ppn}%PPN${dv > 0 ? ' −Disc' : ''})</div>` : ''}
            </td>
            <td style="padding:5px 6px;font-size:10px;text-align:right;">
                <span style="color:var(--success);font-weight:600;">${ret > 0 ? 'Rp' + ret.toLocaleString('id-ID') : '—'}</span>
                ${mR !== null ? `<div style="color:${cR};font-size:8px;">${mR.toFixed(1)}%</div>` : ''}
            </td>
            <td style="padding:5px 6px;font-size:10px;text-align:right;">
                <span style="color:var(--warning);font-weight:600;">${who > 0 ? 'Rp' + who.toLocaleString('id-ID') : '—'}</span>
                ${mW !== null ? `<div style="color:${cW};font-size:8px;">${mW.toFixed(1)}%</div>` : ''}
            </td>
        </tr>`;
    }).join('');

    return `<div style="margin-top:10px;border-radius:var(--radius-sm);overflow:hidden;border:1px solid rgba(255,255,255,0.06);">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:rgba(255,255,255,0.04);">
                    <th style="padding:5px 6px;font-size:9px;font-weight:600;color:var(--text-muted);text-align:left;">Kemasan</th>
                    <th style="padding:5px 6px;font-size:9px;font-weight:600;color:var(--text-muted);text-align:right;">Modal Nett</th>
                    <th style="padding:5px 6px;font-size:9px;font-weight:600;color:var(--success);text-align:right;">Jual Ecer</th>
                    <th style="padding:5px 6px;font-size:9px;font-weight:600;color:var(--warning);text-align:right;">Jual Grosir</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    </div>`;
}

/**
 * Build the price trend banner comparing new buy price with last buy price
 * @param {object} item - purchase item object
 */
function buildTrendBannerHtml(item) {
    const selPkg      = item.packagings.find(p => p.level == item.level);
    const selBaseQty  = parseFloat(selPkg?.base_qty) || 1;
    const buyPerPcs   = (parseFloat(item.buy_price) || 0) / selBaseQty;
    const lastBuy     = parseFloat(item.last_buy_price) || 0;

    if (lastBuy <= 0) {
        if (buyPerPcs > 0) {
            return `<div style="margin-top:8px;padding:7px 10px;border-radius:var(--radius-sm);background:rgba(255,255,255,0.03);border:1px dashed rgba(255,255,255,0.08);font-size:10px;color:var(--text-muted);">
                <i class="bi bi-info-circle"></i> Produk baru — belum ada histori harga sebelumnya
            </div>`;
        }
        return '';
    }

    if (buyPerPcs <= 0) return '';

    const diff = Math.round(buyPerPcs - lastBuy);
    const diffAbs = Math.abs(diff);
    let icon, color, bg, label;

    if (diff === 0) {
        icon  = 'bi-check-circle-fill'; color = 'var(--success)'; bg = 'rgba(40,167,69,0.1)';
        label = `<strong>Stabil</strong> — Harga modal /pcs sama dengan harga terakhir (Rp${Math.round(lastBuy).toLocaleString('id-ID')})`;
    } else if (diff > 0) {
        icon  = 'bi-graph-up-arrow'; color = 'var(--warning)'; bg = 'rgba(255,193,7,0.1)';
        label = `<strong>Naik Rp${diffAbs.toLocaleString('id-ID')}</strong> dari harga terakhir <span style="opacity:0.7">(Rp${Math.round(lastBuy).toLocaleString('id-ID')} → Rp${Math.round(buyPerPcs).toLocaleString('id-ID')} /pcs)</span>`;
    } else {
        icon  = 'bi-graph-down-arrow'; color = 'var(--info)'; bg = 'rgba(76,201,240,0.1)';
        label = `<strong>Turun Rp${diffAbs.toLocaleString('id-ID')}</strong> dari harga terakhir <span style="opacity:0.7">(Rp${Math.round(lastBuy).toLocaleString('id-ID')} → Rp${Math.round(buyPerPcs).toLocaleString('id-ID')} /pcs)</span>`;
    }

    return `<div style="margin-top:8px;padding:7px 10px;border-radius:var(--radius-sm);background:${bg};border:1px solid ${color}30;font-size:10px;color:${color};display:flex;align-items:flex-start;gap:6px;">
        <i class="bi ${icon}" style="margin-top:1px;flex-shrink:0;"></i>
        <span>${label}</span>
    </div>`;
}

/**
 * Build the collapsible drawer rows (per-packaging level detail editor)
 * @param {object} item - purchase item object  
 * @param {string} prefix - unique prefix ('item' or 'bulk')
 */
function buildDrawerRowHtml(item, prefix) {
    const uid = item.id;
    let html  = '';

    item.packagings.forEach(pkg => {
        const isLevel1  = (pkg.level == 1);
        const bq        = parseFloat(pkg.base_qty) || 1;
        const buy       = parseFloat(pkg.buy_price) || 0;
        const ppn       = parseFloat(pkg.ppn_pct) || 0;
        const dm        = pkg.diskon_mode || 'rp';
        const dv        = parseFloat(pkg.diskon_value) || 0;
        const nett      = calcItemNett(buy, ppn, dm, dv);
        const ret       = parseFloat(pkg.sell_price_retail) || 0;
        const who       = parseFloat(pkg.sell_price_wholesale) || 0;
        const origBuy   = parseFloat(pkg._orig_buy) || 0;
        const origRet   = parseFloat(pkg._orig_ret) || 0;
        const isSelected = (pkg.level == item.level);

        // Price change badge vs original DB price
        let changeBadge = '';
        if (origBuy > 0 && buy !== origBuy) {
            const d = Math.abs(Math.round(buy - origBuy));
            changeBadge = buy > origBuy
                ? `<span style="font-size:9px;background:var(--warning-bg);color:var(--warning);padding:1px 5px;border-radius:8px;"><i class="bi bi-arrow-up-right"></i> Naik Rp${d.toLocaleString('id-ID')}</span>`
                : `<span style="font-size:9px;background:var(--info-bg);color:var(--info);padding:1px 5px;border-radius:8px;"><i class="bi bi-arrow-down-right"></i> Turun Rp${d.toLocaleString('id-ID')}</span>`;
        } else if (origBuy > 0) {
            changeBadge = `<span style="font-size:9px;background:var(--success-bg);color:var(--success);padding:1px 5px;border-radius:8px;"><i class="bi bi-check"></i> Sama</span>`;
        }

        // Saran harga jual berdasarkan margin lama
        let suggestHtml = '';
        if (origBuy > 0 && origRet > 0 && origBuy < origRet) {
            const prevMgn = (origRet - origBuy) / origRet;
            if (prevMgn > 0 && prevMgn < 1) {
                const sug = Math.round(buy / (1 - prevMgn));
                suggestHtml = `<div style="font-size:9px;color:var(--info);margin-bottom:6px;"><i class="bi bi-lightbulb"></i> Saran ecer (margin ${(prevMgn*100).toFixed(1)}%): <strong>Rp${sug.toLocaleString('id-ID')}</strong></div>`;
            }
        }

        // Tier rows
        const tiers = pkg.qty_prices || [];
        const tierRowsHtml = tiers.map(t => {
            const th = Math.round((parseFloat(t.min_qty)||0) * (parseFloat(t.unit_price)||0));
            return `<div class="drawer-tier-row" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:4px;margin-bottom:4px;align-items:center;">
                <input type="number" class="form-control-dark drawer-tier-min-qty" style="font-size:10px;padding:4px;" placeholder="Min Qty" value="${t.min_qty}" min="1">
                <input type="number" class="form-control-dark drawer-tier-total" style="font-size:10px;padding:4px;color:var(--success);" placeholder="Total Harga" value="${th}" min="0" oninput="recalcTierHint(this)">
                <select class="form-select-dark drawer-tier-mode" style="font-size:10px;padding:4px;">
                    <option value="both" ${t.sale_mode==='both'?'selected':''}>Semua</option>
                    <option value="retail" ${t.sale_mode==='retail'?'selected':''}>Ecer</option>
                    <option value="wholesale" ${t.sale_mode==='wholesale'?'selected':''}>Grosir</option>
                </select>
                <button type="button" onclick="this.closest('.drawer-tier-row').remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:4px 8px;cursor:pointer;font-size:12px;"><i class="bi bi-x"></i></button>
            </div>`;
        }).join('');

        html += `
        <div class="drawer-pkg-row" data-level="${pkg.level}" data-base-qty="${bq}" data-pkg-id="${pkg.id || ''}" style="border:1px solid ${isSelected ? 'var(--primary)' : 'var(--border-color)'};border-radius:var(--radius-md);padding:12px;margin-bottom:10px;background:${isSelected ? 'rgba(230,57,70,0.05)' : 'var(--surface-2)'};">
            <div style="font-weight:700;font-size:12px;margin-bottom:10px;color:${isSelected ? 'var(--primary)' : 'var(--text-primary)'};display:flex;align-items:center;flex-wrap:wrap;gap:6px;">
                ${isSelected ? '<i class="bi bi-arrow-right-short"></i>' : '<i class="bi bi-box-seam" style="opacity:0.5"></i>'}
                ${pkg.unit_name} <span style="font-weight:400;font-size:10px;color:var(--text-muted);">× ${bq} pcs</span>
                ${changeBadge}
            </div>

            <!-- Harga Modal / Beli -->
            <div style="margin-bottom:8px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                    <label style="font-size:10px;color:var(--text-muted);">Harga Modal / Beli</label>
                    ${origBuy > 0 ? `<span style="font-size:9px;color:var(--text-muted);">Sebelumnya: Rp${Math.round(origBuy).toLocaleString('id-ID')}</span>` : ''}
                </div>
                ${!isLevel1 ? `<label class="price-custom-toggle buy-custom-toggle ${pkg.buy_custom ? 'active' : ''}" style="margin-bottom:4px;" title="Custom harga modal">
                    <input type="checkbox" class="chk-buy-custom" ${pkg.buy_custom ? 'checked' : ''} onchange="onDrawerCustomToggle('${prefix}', ${uid}, ${pkg.level}, 'buy', this.checked)">
                    <i class="bi bi-pencil-square" style="font-size:10px;"></i> Harga Modal Custom
                </label>` : ''}
                <input type="number" class="form-control-dark drawer-pkg-buy" step="0.01" style="width:100%;font-size:12px;padding:6px;" value="${buy}" placeholder="0"
                       oninput="onDrawerBuyInput('${prefix}', ${uid}, ${pkg.level}, this.value)">
                ${!isLevel1 ? `<div class="price-locked-note buy-locked-note ${pkg.buy_custom ? '' : 'visible'}" style="font-size:9px;color:var(--info);margin-top:3px;"><i class="bi bi-link-45deg"></i> Otomatis dari pcs × isi</div>` : ''}
            </div>

            <!-- PPN & Diskon info (read-only display, from main inputs) -->
            <div class="pkg-ppn-diskon-badge" style="background:rgba(76,201,240,0.06);border:1px dashed rgba(76,201,240,0.25);border-radius:4px;padding:6px 8px;margin-bottom:8px;font-size:9px;">
                <span style="color:var(--info);font-weight:600;"><i class="bi bi-receipt"></i> PPN &amp; Diskon</span>
                &nbsp;|&nbsp;
                PPN: <strong>${ppn}%</strong>
                &nbsp;|&nbsp;
                Diskon: <strong>${dm === 'pct' ? dv + '%' : 'Rp' + Math.round(dv).toLocaleString('id-ID')}</strong>
                &nbsp;|&nbsp;
                Nett: <strong style="color:var(--info);">Rp${Math.round(nett).toLocaleString('id-ID')}</strong>
            </div>

            ${suggestHtml}

            <!-- Harga Jual -->
            ${!isLevel1 ? `<label class="price-custom-toggle sell-custom-toggle ${pkg.sell_custom ? 'active' : ''}" style="margin-bottom:4px;" title="Custom harga jual">
                <input type="checkbox" class="chk-sell-custom" ${pkg.sell_custom ? 'checked' : ''} onchange="onDrawerCustomToggle('${prefix}', ${uid}, ${pkg.level}, 'sell', this.checked)">
                <i class="bi bi-tag" style="font-size:10px;"></i> Harga Jual Custom
            </label>` : ''}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:6px;">
                <div>
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">Jual Ecer</label>
                    <input type="number" class="form-control-dark drawer-pkg-ret" style="width:100%;font-size:12px;padding:6px;color:var(--success);" value="${ret}"
                           oninput="onDrawerSellInput('${prefix}', ${uid}, ${pkg.level}, 'retail', this.value)">
                </div>
                <div>
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">Jual Grosir</label>
                    <input type="number" class="form-control-dark drawer-pkg-who" style="width:100%;font-size:12px;padding:6px;color:var(--warning);" value="${who}"
                           oninput="onDrawerSellInput('${prefix}', ${uid}, ${pkg.level}, 'wholesale', this.value)">
                </div>
            </div>
            ${!isLevel1 ? `<div class="price-locked-note sell-locked-note ${pkg.sell_custom ? '' : 'visible'}" style="font-size:9px;color:var(--info);margin-top:-2px;margin-bottom:6px;"><i class="bi bi-link-45deg"></i> Otomatis dari pcs × isi</div>` : ''}

            <!-- Margin display -->
            <div class="drawer-margin-info" style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-muted);margin-bottom:8px;">
                <span class="drawer-margin-retail">${formatMarginWithProfit('Ecer', nett, ret)}</span>
                <span class="drawer-margin-wholesale">${formatMarginWithProfit('Grosir', nett, who)}</span>
            </div>

            <!-- Tier Pricing -->
            <div style="border-top:1px dashed var(--border-color);padding-top:8px;margin-top:4px;">
                <div style="font-size:10px;font-weight:600;color:var(--info);margin-bottom:4px;display:flex;justify-content:space-between;align-items:center;">
                    <span><i class="bi bi-layers"></i> Harga Tier / Kuantitas</span>
                    <button type="button" onclick="addDrawerTierRow(this)" style="background:var(--info-bg);color:var(--info);border:none;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;"><i class="bi bi-plus"></i> Tambah</button>
                </div>
                <div style="font-size:9px;color:var(--text-muted);margin-bottom:3px;display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:4px;"><span>Min Qty</span><span>Total Harga</span><span>Mode</span><span></span></div>
                <div class="drawer-tier-rows-container">${tierRowsHtml}</div>
                ${tiers.length === 0 ? `<div class="drawer-tier-empty" style="font-size:9px;color:var(--text-muted);text-align:center;padding:4px;"><i class="bi bi-info-circle"></i> Belum ada harga tier. Klik Tambah.</div>` : ''}
            </div>
        </div>`;
    });

    return html;
}

/** Toggle collapse/expand of item drawer */
function toggleItemDrawer(uid) {
    const drawer  = document.getElementById(`drawer_${uid}`);
    const btn     = document.getElementById(`drawer_btn_${uid}`);
    if (!drawer || !btn) return;
    const isOpen = drawer.style.display !== 'none';
    drawer.style.display = isOpen ? 'none' : 'block';
    btn.innerHTML = isOpen
        ? '<i class="bi bi-tags"></i> Atur Harga Kemasan Lainnya'
        : '<i class="bi bi-chevron-up"></i> Tutup Panel Kemasan';
    btn.style.borderStyle = isOpen ? 'dashed' : 'solid';
    if (!isOpen) {
        // Refresh mini table and trend banner inside drawer
        const item = purchaseItems.find(i => i.id == uid);
        if (item) {
            const miniTbl = drawer.querySelector('.item-mini-table');
            if (miniTbl) miniTbl.innerHTML = buildMiniPricingTableHtml(item);
            const trendEl = drawer.querySelector('.item-trend-banner');
            if (trendEl) trendEl.innerHTML = buildTrendBannerHtml(item);
        }
        // When opening, trigger margin recalc on all drawer rows
        drawer.querySelectorAll('.drawer-pkg-row').forEach(row => {
            refreshDrawerRowMargin(row);
        });
        // Initialize custom toggle states
        drawer.querySelectorAll('.drawer-pkg-row').forEach(row => {
            const buyNote  = row.querySelector('.buy-locked-note');
            const sellNote = row.querySelector('.sell-locked-note');
            const buyToggle  = row.querySelector('.buy-custom-toggle');
            const sellToggle = row.querySelector('.sell-custom-toggle');
            if (buyToggle) {
                const chk = buyToggle.querySelector('input');
                buyToggle.classList.toggle('active', chk?.checked || false);
            }
            if (sellToggle) {
                const chk = sellToggle.querySelector('input');
                sellToggle.classList.toggle('active', chk?.checked || false);
            }
        });
    }
}

/** Add new tier row to drawer */
function addDrawerTierRow(btn) {
    const container = btn.closest('.drawer-pkg-row').querySelector('.drawer-tier-rows-container');
    const emptyHint = btn.closest('.drawer-pkg-row').querySelector('.drawer-tier-empty');
    if (emptyHint) emptyHint.remove();
    const row = document.createElement('div');
    row.className = 'drawer-tier-row';
    row.style.cssText = 'display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:4px;margin-bottom:4px;align-items:center;';
    row.innerHTML = `
        <input type="number" class="form-control-dark drawer-tier-min-qty" style="font-size:10px;padding:4px;" placeholder="Min Qty" min="1">
        <input type="number" class="form-control-dark drawer-tier-total" style="font-size:10px;padding:4px;color:var(--success);" placeholder="Total Harga" min="0" oninput="recalcTierHint(this)">
        <select class="form-select-dark drawer-tier-mode" style="font-size:10px;padding:4px;">
            <option value="both">Semua</option><option value="retail">Ecer</option><option value="wholesale">Grosir</option>
        </select>
        <button type="button" onclick="this.closest('.drawer-tier-row').remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:4px 8px;cursor:pointer;font-size:12px;"><i class="bi bi-x"></i></button>`;
    container.appendChild(row);
}

/** Recalc margin display for a single drawer-pkg-row element */
function refreshDrawerRowMargin(rowEl) {
    const buy   = parseFloat(rowEl.querySelector('.drawer-pkg-buy')?.value) || 0;
    const ppn   = parseFloat(rowEl.closest('[data-item-ppn]')?.dataset.itemPpn || rowEl.closest('.item-card')?.dataset.ppn || 0);
    const nett  = calcItemNett(buy, ppn, 'rp', 0); // simplified; the row shows nett from badges
    const ret   = parseFloat(rowEl.querySelector('.drawer-pkg-ret')?.value) || 0;
    const who   = parseFloat(rowEl.querySelector('.drawer-pkg-who')?.value) || 0;
    const rEl   = rowEl.querySelector('.drawer-margin-retail');
    const wEl   = rowEl.querySelector('.drawer-margin-wholesale');
    if (rEl) rEl.innerHTML = formatMarginWithProfit('Ecer', buy, ret);
    if (wEl) wEl.innerHTML = formatMarginWithProfit('Grosir', buy, who);
}

/**
 * Called when user types in drawer buy input.
 * Only updates the item's pkg data; does NOT re-propagate to other levels
 * (user is editing this level manually, buy_custom must be set).
 * Works for both regular purchaseItems and bulk modal bulkItems.
 */
function onDrawerBuyInput(prefix, uid, level, newVal) {
    let item = purchaseItems.find(i => i.id == uid);
    if (!item) item = bulkItems.find(b => b.id == uid);
    if (!item) return;
    const pkg = item.packagings.find(p => p.level == level);
    if (!pkg) return;
    pkg.buy_price  = parseFloat(newVal) || 0;
    pkg.harga_nett = calcItemNett(pkg.buy_price, pkg.ppn_pct, pkg.diskon_mode, pkg.diskon_value);
    // Update mini table live (handles both regular and bulk)
    refreshMiniTableForItem(uid);
    // Update margin in drawer row
    const isBulk = (prefix === 'bulk');
    const rowEl = isBulk
        ? document.querySelector(`.bulk-item[data-bulk-id="${uid}"] .drawer-pkg-row[data-level="${level}"]`)
        : document.querySelector(`#drawer_${uid} .drawer-pkg-row[data-level="${level}"]`);
    if (rowEl) refreshDrawerRowMargin(rowEl);
}

function onDrawerSellInput(prefix, uid, level, type, newVal) {
    let item = purchaseItems.find(i => i.id == uid);
    if (!item) item = bulkItems.find(b => b.id == uid);
    if (!item) return;
    const pkg = item.packagings.find(p => p.level == level);
    if (!pkg) return;
    if (type === 'retail')    pkg.sell_price_retail    = parseFloat(newVal) || 0;
    if (type === 'wholesale') pkg.sell_price_wholesale = parseFloat(newVal) || 0;
    refreshMiniTableForItem(uid);
    const isBulk = (prefix === 'bulk');
    const rowEl = isBulk
        ? document.querySelector(`.bulk-item[data-bulk-id="${uid}"] .drawer-pkg-row[data-level="${level}"]`)
        : document.querySelector(`#drawer_${uid} .drawer-pkg-row[data-level="${level}"]`);
    if (rowEl) refreshDrawerRowMargin(rowEl);
}

function onDrawerCustomToggle(prefix, uid, level, priceType, isCustom) {
    let item = purchaseItems.find(i => i.id == uid);
    if (!item) item = bulkItems.find(b => b.id == uid);
    if (!item) return;
    const pkg = item.packagings.find(p => p.level == level);
    if (!pkg) return;
    if (priceType === 'buy')  pkg.buy_custom  = isCustom;
    if (priceType === 'sell') pkg.sell_custom = isCustom;
    const isBulk = (prefix === 'bulk');
    const rowEl = isBulk
        ? document.querySelector(`.bulk-item[data-bulk-id="${uid}"] .drawer-pkg-row[data-level="${level}"]`)
        : document.querySelector(`#drawer_${uid} .drawer-pkg-row[data-level="${level}"]`);
    if (!rowEl) return;
    if (priceType === 'buy') {
        const toggle = rowEl.querySelector('.buy-custom-toggle');
        const note   = rowEl.querySelector('.buy-locked-note');
        if (toggle) toggle.classList.toggle('active', isCustom);
        if (note)   note.style.display = isCustom ? 'none' : 'block';
        if (!isCustom) {
            // Re-sync buy from main
            const selPkg = item.packagings.find(p => p.level == item.level);
            const buyPcs = (parseFloat(item.buy_price) || 0) / (parseFloat(selPkg?.base_qty) || 1);
            pkg.buy_price = Math.round(buyPcs * (parseFloat(pkg.base_qty) || 1));
            const inp = rowEl.querySelector('.drawer-pkg-buy');
            if (inp) inp.value = pkg.buy_price;
        }
    } else {
        const toggle = rowEl.querySelector('.sell-custom-toggle');
        const note   = rowEl.querySelector('.sell-locked-note');
        if (toggle) toggle.classList.toggle('active', isCustom);
        if (note)   note.style.display = isCustom ? 'none' : 'block';
        if (!isCustom) {
            const selPkg = item.packagings.find(p => p.level == item.level);
            const bqSel  = parseFloat(selPkg?.base_qty) || 1;
            const bqThis = parseFloat(pkg.base_qty) || 1;
            pkg.sell_price_retail    = Math.round((parseFloat(selPkg.sell_price_retail)||0) / bqSel * bqThis);
            pkg.sell_price_wholesale = Math.round((parseFloat(selPkg.sell_price_wholesale)||0) / bqSel * bqThis);
            const retInp = rowEl.querySelector('.drawer-pkg-ret');
            const whoInp = rowEl.querySelector('.drawer-pkg-who');
            if (retInp) retInp.value = pkg.sell_price_retail;
            if (whoInp) whoInp.value = pkg.sell_price_wholesale;
        }
    }
    refreshMiniTableForItem(uid);
}

/**
 * Refreshes the mini pricing table without re-rendering the full card.
 * Works for both regular purchaseItems and bulk modal bulkItems.
 */
function refreshMiniTableForItem(uid) {
    let item = purchaseItems.find(i => i.id == uid);
    const isBulk = !item;
    if (isBulk) item = bulkItems.find(b => b.id == uid);
    if (!item) return;
    if (isBulk) {
        // Bulk item: find by data-bulk-id, update .bulk-mini-table and .bulk-trend-banner
        const bulkEl = document.querySelector(`.bulk-item[data-bulk-id="${uid}"]`);
        if (!bulkEl) return;
        const miniTbl = bulkEl.querySelector('.bulk-mini-table');
        if (miniTbl) miniTbl.innerHTML = buildMiniPricingTableHtml(item);
        const trendEl = bulkEl.querySelector('.bulk-trend-banner');
        if (trendEl) trendEl.innerHTML = buildTrendBannerHtml(item);
    } else {
        // Regular cart item: update by ID
        const itemEl = document.getElementById(`drawer_${uid}`);
        if (itemEl) {
            const tblEl = itemEl.querySelector('.item-mini-table');
            if (tblEl) tblEl.innerHTML = buildMiniPricingTableHtml(item);
            const trendEl = itemEl.querySelector('.item-trend-banner');
            if (trendEl) trendEl.innerHTML = buildTrendBannerHtml(item);
        }
    }
}

/** Collect drawer data back into item.packagings before submit */
function collectDrawerDataForItem(item) {
    const drawerEl = document.getElementById(`drawer_${item.id}`);
    if (!drawerEl) return;
    drawerEl.querySelectorAll('.drawer-pkg-row').forEach(async row => {
        const level  = parseInt(row.dataset.level);
        const pkgId  = row.dataset.pkgId;
        const pkg    = item.packagings.find(p => p.level == level);
        if (!pkg) return;
        pkg.buy_price            = parseFloat(row.querySelector('.drawer-pkg-buy')?.value) || pkg.buy_price;
        pkg.sell_price_retail    = parseFloat(row.querySelector('.drawer-pkg-ret')?.value) || pkg.sell_price_retail;
        pkg.sell_price_wholesale = parseFloat(row.querySelector('.drawer-pkg-who')?.value) || pkg.sell_price_wholesale;
        pkg.buy_custom  = row.querySelector('.chk-buy-custom')?.checked  || false;
        pkg.sell_custom = row.querySelector('.chk-sell-custom')?.checked || false;
        pkg.harga_nett  = calcItemNett(pkg.buy_price, pkg.ppn_pct, pkg.diskon_mode, pkg.diskon_value);

        // Collect tier prices
        const tiers = [];
        row.querySelectorAll('.drawer-tier-row').forEach(tr => {
            const minQty = parseFloat(tr.querySelector('.drawer-tier-min-qty')?.value) || 0;
            const totalH = parseFloat(tr.querySelector('.drawer-tier-total')?.value) || 0;
            const mode   = tr.querySelector('.drawer-tier-mode')?.value || 'both';
            if (minQty > 0 && totalH > 0) tiers.push({ min_qty: minQty, unit_price: totalH / minQty, sale_mode: mode });
        });
        pkg.qty_prices = tiers;

        // Save tier prices to DB if pkg has an ID
        if (pkgId) {
            try {
                await fetch(`${BASE_URL}api/packagings/${pkgId}/qty-prices`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfVal },
                    body: JSON.stringify({ tiers, csrf_token: csrfVal })
                });
            } catch(e) { console.warn('Tier save error:', e); }
        }
    });
}

// ═══════════════════════════════════════════════════════════
// RENDER CART — Unified Simplified Layout
// ═══════════════════════════════════════════════════════════

function renderCart() {
    emptyState.style.display = purchaseItems.length === 0 ? 'flex' : 'none';
    countBadge.textContent = `${purchaseItems.length} Item`;

    let html = '';
    purchaseItems.forEach(item => {
        // Ensure orig prices are stored for comparison (first time only)
        item.packagings.forEach(pkg => {
            if (pkg._orig_buy === undefined) pkg._orig_buy = parseFloat(pkg.buy_price) || 0;
            if (pkg._orig_ret === undefined) pkg._orig_ret = parseFloat(pkg.sell_price_retail) || 0;
            if (!pkg.qty_prices) pkg.qty_prices = [];
        });

        const levelOptions = item.packagings.map(p =>
            `<option value="${p.level}" ${p.level == item.level ? 'selected' : ''}>${p.unit_name} (Isi ${p.base_qty})</option>`
        ).join('');

        const selPkg    = item.packagings.find(p => p.level == item.level) || item.packagings[0];
        const selBaseQty = parseFloat(selPkg?.base_qty) || 1;
        const totalVal  = Math.round((item.quantity || 1) * (item.buy_price || 0));
        const hasPkgs   = item.packagings.length > 1;
        const drawerHtml  = hasPkgs ? buildDrawerRowHtml(item, 'item') : '';

        // Simple per-unit price summary
        const buyPrice = parseFloat(selPkg?.buy_price) || 0;
        const lastBuy = parseFloat(item.last_buy_price) || 0;
        let priceSummary = '';
        if (buyPrice > 0) {
            priceSummary = `<span style="font-size:10px;color:var(--text-muted);">Harga terakhir: <strong style="color:var(--info);">Rp${Math.round(buyPrice).toLocaleString('id-ID')}</strong>/${selPkg?.unit_name || 'pcs'}</span>`;
        }
        if (lastBuy > 0 && buyPrice > 0 && lastBuy !== buyPrice) {
            const diff = buyPrice - lastBuy;
            const diffIcon = diff > 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short';
            const diffColor = diff > 0 ? 'var(--warning)' : 'var(--info)';
            priceSummary += ` <span style="font-size:9px;color:${diffColor};font-weight:600;"><i class="bi ${diffIcon}"></i>${diff > 0 ? '+' : ''}Rp${Math.round(Math.abs(diff)).toLocaleString('id-ID')}</span>`;
        }

        html += `
        <div class="item-card" id="item_card_${item.id}" data-ppn="${item.ppn_pct || 0}" style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--border-color);position:relative;">
            <!-- Remove button -->
            <button onclick="removeItem(${item.id})" style="position:absolute;top:12px;right:16px;background:none;border:none;color:var(--danger);font-size:1.2rem;cursor:pointer;"><i class="bi bi-x-circle-fill"></i></button>

            <!-- Product Name -->
            <div style="font-weight:700;font-size:var(--font-size-sm);margin-bottom:12px;padding-right:28px;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                ${item.name}
                ${hasPkgs ? `<span style="font-size:9px;background:var(--info-bg);color:var(--info);padding:2px 6px;border-radius:8px;white-space:nowrap;">${item.packagings.length} kemasan</span>` : ''}
            </div>
            ${priceSummary ? `<div style="margin-bottom:10px;">${priceSummary}</div>` : ''}

            <!-- ── ROW 1: Kemasan + Qty ── -->
            <div style="display:flex;gap:8px;margin-bottom:10px;">
                <div style="flex:2;">
                    <label style="font-size:10px;color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span>Kemasan Beli</span>
                        <a href="<?= BASE_URL ?>settings/master-data" target="_blank" style="color:var(--info);text-decoration:none;font-size:9px;"><i class="bi bi-box-arrow-up-right"></i> Master</a>
                    </label>
                    <select class="form-select-dark" style="width:100%;padding:8px;font-size:12px;" onchange="changeLevel(${item.id}, this.value)">
                        ${levelOptions}
                    </select>
                </div>
                <div style="flex:1;">
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Qty Beli</label>
                    <input type="number" class="form-control-dark" style="width:100%;padding:8px;font-size:12px;text-align:center;" value="${item.quantity}" min="0.01" step="0.01"
                           oninput="onMainInputChange(${item.id}, 'qty', this.value)">
                </div>
            </div>

            <!-- ── ROW 2: Total Harga ── -->
            <div style="margin-bottom:10px;">
                <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Total Harga Pembelian</label>
                <input type="number" id="main_total_${item.id}" class="form-control-dark" style="width:100%;padding:8px;font-size:13px;font-weight:600;color:var(--info);"
                       value="${totalVal > 0 ? totalVal : ''}" placeholder="Masukkan total harga..."
                       oninput="onMainInputChange(${item.id}, 'total', this.value)">
            </div>

            <!-- ── ROW 3: PPN + Diskon ── -->
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:8px;margin-bottom:4px;">
                <div>
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">PPN (%)</label>
                    <input type="number" class="form-control-dark item-ppn" style="width:100%;padding:8px;font-size:12px;" value="${item.ppn_pct || 0}" min="0" max="100" placeholder="0"
                           oninput="onMainInputChange(${item.id}, 'ppn', this.value)">
                </div>
                <div>
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Diskon</label>
                    <div style="display:flex;gap:4px;">
                        <select class="form-select-dark item-diskon-mode" style="width:65px;padding:8px;font-size:11px;" onchange="onMainInputChange(${item.id}, 'diskon_mode', this.value)">
                            <option value="rp" ${(item.diskon_mode||'rp')==='rp'?'selected':''}>Rp</option>
                            <option value="pct" ${(item.diskon_mode||'rp')==='pct'?'selected':''}}>%</option>
                        </select>
                        <input type="number" class="form-control-dark item-diskon-value" style="flex:1;padding:8px;font-size:12px;" value="${item.diskon_value || 0}" min="0" placeholder="0"
                               oninput="onMainInputChange(${item.id}, 'diskon_value', this.value)">
                    </div>
                </div>
            </div>

            <!-- ── Harga per unit (auto calculated) ── -->
            <div class="item-unit-price-info" style="margin-top:6px;font-size:10px;color:var(--text-muted);text-align:right;"></div>

            ${hasPkgs ? `
            <!-- ── Drawer Toggle Button ── -->
            <button id="drawer_btn_${item.id}" type="button" onclick="toggleItemDrawer(${item.id})"
                    style="width:100%;margin-top:10px;background:var(--surface-2);color:var(--primary);border:1px dashed var(--border-color);padding:9px;border-radius:var(--radius-sm);font-size:11px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                <i class="bi bi-tags"></i> Atur Harga Kemasan Lainnya
            </button>

            <!-- ── Collapsible Drawer ── -->
            <div id="drawer_${item.id}" style="display:none;margin-top:10px;">
                <div style="font-size:10px;color:var(--text-muted);margin-bottom:10px;padding:8px;background:rgba(0,0,0,0.1);border-radius:var(--radius-sm);">
                    <i class="bi bi-info-circle"></i> Harga modal dihitung otomatis. PPN & Diskon sama untuk semua kemasan. Centang "Custom" untuk mengunci harga individual.
                </div>
                <!-- ── Mini Pricing Table ── -->
                <div class="item-mini-table">${buildMiniPricingTableHtml(item)}</div>
                <!-- ── Trend Banner ── -->
                <div class="item-trend-banner">${buildTrendBannerHtml(item)}</div>
                ${drawerHtml}
            </div>` : ''}
        </div>`;
    });

    if (purchaseItems.length > 0) {
        itemsContainer.innerHTML = html;
        itemsContainer.appendChild(emptyState);
    } else {
        itemsContainer.innerHTML = '';
        itemsContainer.appendChild(emptyState);
    }

    calculateTotal();
}

/** Unified main input change handler for the regular cart */
function onMainInputChange(uid, field, val) {
    const item = purchaseItems.find(i => i.id == uid);
    if (!item) return;

    if (field === 'ppn')         item.ppn_pct      = parseFloat(val) || 0;
    if (field === 'diskon_mode') item.diskon_mode  = val || 'rp';
    if (field === 'diskon_value') item.diskon_value = parseFloat(val) || 0;

    if (field === 'qty') {
        item.quantity = parseFloat(val) || 0;
        // Recalculate total from qty × buy_price
        item.total = item.quantity * (item.buy_price || 0);
        const totalInp = document.getElementById(`main_total_${uid}`);
        if (totalInp && item.buy_price > 0) totalInp.value = Math.round(item.total);
    }

    if (field === 'total') {
        const total = parseFloat(val) || 0;
        const qty   = item.quantity || 1;
        const selPkg = item.packagings.find(p => p.level == item.level);
        const bq    = parseFloat(selPkg?.base_qty) || 1;
        if (total > 0 && qty > 0) {
            // Total Harga = qty × buy_price_per_pkg → buy_price_per_pkg = total / qty
            item.buy_price = Math.round(total / qty);
            item.total     = total;
            // Update the current level packaging too
            if (selPkg) selPkg.buy_price = item.buy_price;
        }
    }

    // Propagate to all levels
    propagateFromMainInputs(item);
    // Sync item-level fields from the selected packaging
    const selPkg2 = item.packagings.find(p => p.level == item.level);
    if (selPkg2) {
        item.harga_nett          = selPkg2.harga_nett;
        item.sell_price_retail   = selPkg2.sell_price_retail;
        item.sell_price_wholesale = selPkg2.sell_price_wholesale;
    }

    calculateTotal();
    // Refresh mini table & trend banner
    refreshMiniTableForItem(uid);
    // If drawer is open, refresh drawer rows
    refreshOpenDrawer(uid);
}

/** Refresh drawer content if it is currently open */
function refreshOpenDrawer(uid) {
    const drawer = document.getElementById(`drawer_${uid}`);
    if (!drawer || drawer.style.display === 'none') return;
    const item = purchaseItems.find(i => i.id == uid);
    if (!item) return;
    // Re-render only the PPN/Diskon info badges inside each drawer row
    drawer.querySelectorAll('.drawer-pkg-row').forEach(rowEl => {
        const level = parseInt(rowEl.dataset.level);
        const pkg   = item.packagings.find(p => p.level == level);
        if (!pkg) return;
        const ppn   = pkg.ppn_pct || 0;
        const dm    = pkg.diskon_mode || 'rp';
        const dv    = pkg.diskon_value || 0;
        const nett  = pkg.harga_nett || pkg.buy_price || 0;
        // Update PPN/Diskon info label
        const badgesEl = rowEl.querySelector('.pkg-ppn-diskon-badge');
        if (badgesEl) {
            badgesEl.innerHTML = `PPN: <strong>${ppn}%</strong> &nbsp;|&nbsp; Diskon: <strong>${dm === 'pct' ? dv + '%' : 'Rp' + Math.round(dv).toLocaleString('id-ID')}</strong> &nbsp;|&nbsp; Nett: <strong style="color:var(--info);">Rp${Math.round(nett).toLocaleString('id-ID')}</strong>`;
        }
        // Update buy input if not custom
        if (!pkg.buy_custom) {
            const buyInp = rowEl.querySelector('.drawer-pkg-buy');
            if (buyInp) buyInp.value = Math.round(pkg.buy_price);
        }
        // Update sell inputs if not custom
        if (!pkg.sell_custom) {
            const retInp = rowEl.querySelector('.drawer-pkg-ret');
            const whoInp = rowEl.querySelector('.drawer-pkg-who');
            if (retInp) retInp.value = Math.round(pkg.sell_price_retail);
            if (whoInp) whoInp.value = Math.round(pkg.sell_price_wholesale);
        }
        refreshDrawerRowMargin(rowEl);
    });
}

async function submitPurchase() {
    // Collect drawer data for all items before submitting
    purchaseItems.forEach(item => collectDrawerDataForItem(item));
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

    // Build temporary item-like objects for bulk items so we can reuse the same helpers
    bulkItems = products.map(p => {
        const pkgs = (p.packagings || []).map(pkg => ({
            ...pkg,
            ppn_pct:      parseFloat(pkg.ppn_pct) || 0,
            diskon_mode:  pkg.diskon_mode || 'rp',
            diskon_value: parseFloat(pkg.diskon_value) || 0,
            harga_nett:   parseFloat(pkg.buy_price) || 0,
            buy_custom:   false,
            sell_custom:  false,
            qty_prices:   pkg.qty_prices || [],
            _orig_buy:    parseFloat(pkg.buy_price) || 0,
            _orig_ret:    parseFloat(pkg.sell_price_retail) || 0
        }));
        const defPkg = pkgs[0];
        return {
            id:                    'bulk_' + p.id,
            product_id:            p.id,
            name:                  p.full_name || p.short_label,
            packagings:            pkgs,
            level:                 defPkg?.level || 1,
            unit_name:             defPkg?.unit_name || 'pcs',
            quantity:              0,
            buy_price:             parseFloat(defPkg?.buy_price) || 0,
            sell_price_retail:     parseFloat(defPkg?.sell_price_retail) || 0,
            sell_price_wholesale:  parseFloat(defPkg?.sell_price_wholesale) || 0,
            last_buy_price:        parseFloat(p.last_buy_price) || parseFloat(defPkg?.buy_price) || 0,
            total:                 0,
            ppn_pct:               0,
            diskon_mode:           'rp',
            diskon_value:          0,
            harga_nett:            parseFloat(defPkg?.buy_price) || 0
        };
    });

    // Render each bulk item card using the same helpers
    const listHTML = bulkItems.map(item => {
        const levelOptions = item.packagings.map(p =>
            `<option value="${p.level}" ${p.level == item.level ? 'selected' : ''}>${p.unit_name} (Isi ${p.base_qty})</option>`
        ).join('');
        const hasPkgs    = item.packagings.length > 1;
        const drawerHtml  = hasPkgs ? buildDrawerRowHtml(item, 'bulk') : '';

        // Simple per-unit price summary (instead of full table)
        const selPkg = item.packagings.find(p => p.level == item.level) || item.packagings[0];
        const buyPrice = parseFloat(selPkg?.buy_price) || 0;
        const lastBuy = parseFloat(item.last_buy_price) || 0;
        let priceSummary = '';
        if (buyPrice > 0) {
            priceSummary = `<span style="font-size:10px;color:var(--text-muted);">Harga terakhir: <strong style="color:var(--info);">Rp${Math.round(buyPrice).toLocaleString('id-ID')}</strong>/${selPkg?.unit_name || 'pcs'}</span>`;
        }
        if (lastBuy > 0 && buyPrice > 0 && lastBuy !== buyPrice) {
            const diff = buyPrice - lastBuy;
            const diffIcon = diff > 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short';
            const diffColor = diff > 0 ? 'var(--warning)' : 'var(--info)';
            priceSummary += ` <span style="font-size:9px;color:${diffColor};font-weight:600;"><i class="bi ${diffIcon}"></i>${diff > 0 ? '+' : ''}Rp${Math.round(Math.abs(diff)).toLocaleString('id-ID')}</span>`;
        }

        return `
        <div class="bulk-item" data-bulk-id="${item.id}" data-last-buy="${item.last_buy_price}" style="background:var(--surface-2);padding:12px;border-radius:var(--radius-md);margin-bottom:10px;border:1px solid var(--border-color);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                <div style="font-weight:700;font-size:12px;color:var(--text-primary);flex:1;">${item.name}</div>
                ${hasPkgs ? `<span style="font-size:9px;background:var(--info-bg);color:var(--info);padding:2px 6px;border-radius:8px;white-space:nowrap;margin-left:6px;">${item.packagings.length} kemasan</span>` : ''}
            </div>
            ${priceSummary ? `<div style="margin-bottom:8px;">${priceSummary}</div>` : ''}

            <!-- ── ROW 1: Kemasan + Qty ── -->
            <div style="display:flex;gap:8px;margin-bottom:8px;">
                <div style="flex:2;">
                    <label style="font-size:10px;color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:3px;">
                        <span>Kemasan Beli</span>
                        <a href="<?= BASE_URL ?>settings/master-data" target="_blank" style="color:var(--info);text-decoration:none;font-size:9px;"><i class="bi bi-box-arrow-up-right"></i></a>
                    </label>
                    <select class="form-select-dark bulk-pkg-select" style="width:100%;padding:6px;font-size:11px;" onchange="onBulkLevelChange('${item.id}', this.value)">
                        ${levelOptions}
                    </select>
                </div>
                <div style="flex:1;">
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">Qty Beli</label>
                    <input type="number" class="form-control-dark bulk-qty" style="width:100%;padding:6px;font-size:11px;text-align:center;" value="" min="0" step="0.01" placeholder="0"
                           oninput="onBulkMainChange('${item.id}', 'qty', this.value)">
                </div>
            </div>

            <!-- ── ROW 2: Total Harga ── -->
            <div style="margin-bottom:8px;">
                <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">Total Harga Pembelian</label>
                <input type="number" class="form-control-dark bulk-total" style="width:100%;padding:7px;font-size:12px;font-weight:600;color:var(--info);" value="" placeholder="Kosongkan jika tidak dibeli..."
                       oninput="onBulkMainChange('${item.id}', 'total', this.value)">
            </div>

            <!-- ── ROW 3: PPN + Diskon ── -->
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:6px;margin-bottom:4px;">
                <div>
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">PPN (%)</label>
                    <input type="number" class="form-control-dark bulk-ppn" style="width:100%;padding:6px;font-size:11px;" value="0" min="0" max="100" placeholder="0"
                           oninput="onBulkMainChange('${item.id}', 'ppn', this.value)">
                </div>
                <div>
                    <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:3px;">Diskon</label>
                    <div style="display:flex;gap:3px;">
                        <select class="form-select-dark bulk-diskon-mode" style="width:58px;padding:6px;font-size:10px;" onchange="onBulkMainChange('${item.id}', 'diskon_mode', this.value)">
                            <option value="rp">Rp</option>
                            <option value="pct">%</option>
                        </select>
                        <input type="number" class="form-control-dark bulk-diskon-value" style="flex:1;padding:6px;font-size:11px;" value="0" min="0" placeholder="0"
                               oninput="onBulkMainChange('${item.id}', 'diskon_value', this.value)">
                    </div>
                </div>
            </div>

            <!-- ── Harga per unit (auto calculated) ── -->
            <div class="bulk-unit-price-info" style="margin-top:6px;font-size:10px;color:var(--text-muted);text-align:right;"></div>

            ${hasPkgs ? `
            <!-- ── Drawer Toggle ── -->
            <button class="bulk-drawer-btn" type="button" onclick="toggleBulkDrawer('${item.id}', this)"
                    style="width:100%;margin-top:8px;background:var(--surface-1);color:var(--primary);border:1px dashed var(--border-color);padding:7px;border-radius:var(--radius-sm);font-size:11px;font-weight:600;cursor:pointer;">
                <i class="bi bi-tags"></i> Atur Harga Kemasan Lainnya
            </button>
            <!-- ── Collapsible Drawer (hidden by default) ── -->
            <div class="bulk-drawer" style="display:none;margin-top:8px;">
                <div style="font-size:9px;color:var(--text-muted);margin-bottom:8px;padding:6px;background:rgba(0,0,0,0.1);border-radius:var(--radius-sm);">
                    <i class="bi bi-info-circle"></i> PPN & Diskon sama untuk semua kemasan. Centang "Custom" untuk mengunci harga individual.
                </div>
                <!-- Mini Pricing Table (inside drawer) -->
                <div class="bulk-mini-table">${buildMiniPricingTableHtml(item)}</div>
                <!-- Trend Banner (inside drawer) -->
                <div class="bulk-trend-banner">${buildTrendBannerHtml(item)}</div>
                <!-- Per-packaging detail editors -->
                ${drawerHtml}
            </div>` : ''}
        </div>`;
    }).join('');

    AppModal.show({
        title: 'Input Barang Massal',
        subtitle: 'Isi Total Harga > 0 untuk produk yang dibeli',
        bodyHTML: `
            <div style="margin-bottom:12px;">
                <div style="background:var(--bg-input);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:0 12px;display:flex;align-items:center;">
                    <i class="bi bi-search" style="color:var(--text-muted);font-size:12px;"></i>
                    <input type="text" placeholder="Cari nama produk..." style="flex:1;border:none;background:transparent;padding:10px;color:var(--text-primary);font-size:12px;outline:none;" oninput="filterBulkModal(this.value)" autocomplete="off">
                </div>
            </div>
            <div style="max-height:58vh;overflow-y:auto;overflow-x:hidden;padding-right:2px;">${listHTML}</div>
        `,
        submitText: 'Tambahkan Terpilih',
        onSubmit: () => {
            let addedCount = 0;
            // Get all bulk items and their state from bulkItems array
            document.querySelectorAll('.bulk-item').forEach(el => {
                const bulkId   = el.dataset.bulkId;
                const bulkItem = bulkItems.find(b => b.id == bulkId);
                if (!bulkItem) return;

                // Read current values from DOM
                const qty   = parseFloat(el.querySelector('.bulk-qty')?.value) || 0;
                if (qty <= 0) return;

                const total = parseFloat(el.querySelector('.bulk-total')?.value) || 0;
                if (total <= 0 && bulkItem.buy_price <= 0) return;

                // Collect drawer data first
                const drawerEl = el.querySelector('.bulk-drawer');
                if (drawerEl) {
                    drawerEl.querySelectorAll('.drawer-pkg-row').forEach(row => {
                        const level = parseInt(row.dataset.level);
                        const pkg   = bulkItem.packagings.find(p => p.level == level);
                        if (!pkg) return;
                        pkg.buy_price            = parseFloat(row.querySelector('.drawer-pkg-buy')?.value) || pkg.buy_price;
                        pkg.sell_price_retail    = parseFloat(row.querySelector('.drawer-pkg-ret')?.value) || pkg.sell_price_retail;
                        pkg.sell_price_wholesale = parseFloat(row.querySelector('.drawer-pkg-who')?.value) || pkg.sell_price_wholesale;
                        pkg.buy_custom  = row.querySelector('.chk-buy-custom')?.checked  || false;
                        pkg.sell_custom = row.querySelector('.chk-sell-custom')?.checked || false;
                        pkg.harga_nett  = calcItemNett(pkg.buy_price, pkg.ppn_pct, pkg.diskon_mode, pkg.diskon_value);
                        const tiers = [];
                        row.querySelectorAll('.drawer-tier-row').forEach(tr => {
                            const minQty = parseFloat(tr.querySelector('.drawer-tier-min-qty')?.value) || 0;
                            const totalH = parseFloat(tr.querySelector('.drawer-tier-total')?.value) || 0;
                            const mode   = tr.querySelector('.drawer-tier-mode')?.value || 'both';
                            if (minQty > 0 && totalH > 0) tiers.push({ min_qty: minQty, unit_price: totalH / minQty, sale_mode: mode });
                        });
                        pkg.qty_prices = tiers;
                    });
                }

                // Sync selected level with main inputs
                const selPkgEl = el.querySelector('.bulk-pkg-select');
                const selLevel = parseInt(selPkgEl?.value) || bulkItem.level;
                const ppn      = parseFloat(el.querySelector('.bulk-ppn')?.value) || 0;
                const dm       = el.querySelector('.bulk-diskon-mode')?.value || 'rp';
                const dv       = parseFloat(el.querySelector('.bulk-diskon-value')?.value) || 0;

                bulkItem.ppn_pct      = ppn;
                bulkItem.diskon_mode  = dm;
                bulkItem.diskon_value = dv;

                const selPkg = bulkItem.packagings.find(p => p.level == selLevel) || bulkItem.packagings[0];
                const bq     = parseFloat(selPkg?.base_qty) || 1;
                if (total > 0 && qty > 0) {
                    bulkItem.buy_price = Math.round(total / qty);
                    if (selPkg) selPkg.buy_price = bulkItem.buy_price;
                }
                bulkItem.level     = selLevel;
                bulkItem.quantity  = qty;
                bulkItem.total     = qty * bulkItem.buy_price;

                // Propagate
                propagateFromMainInputs(bulkItem);

                const selPkgFinal = bulkItem.packagings.find(p => p.level == selLevel);
                const existingItem = purchaseItems.find(i => i.product_id === bulkItem.product_id && i.level == selLevel);
                if (existingItem) {
                    existingItem.quantity              += qty;
                    existingItem.buy_price              = bulkItem.buy_price;
                    existingItem.sell_price_retail      = selPkgFinal?.sell_price_retail || 0;
                    existingItem.sell_price_wholesale   = selPkgFinal?.sell_price_wholesale || 0;
                    existingItem.total                  = existingItem.quantity * existingItem.buy_price;
                    existingItem.packagings             = bulkItem.packagings;
                    existingItem.ppn_pct               = ppn;
                    existingItem.diskon_mode            = dm;
                    existingItem.diskon_value           = dv;
                    existingItem.harga_nett             = calcItemNett(existingItem.buy_price, ppn, dm, dv);
                } else {
                    purchaseItems.unshift({
                        id:                   Date.now() + Math.random(),
                        product_id:           bulkItem.product_id,
                        name:                 bulkItem.name,
                        packagings:           bulkItem.packagings,
                        level:                selLevel,
                        unit_name:            selPkgFinal?.unit_name || 'pcs',
                        quantity:             qty,
                        buy_price:            bulkItem.buy_price,
                        sell_price_retail:    selPkgFinal?.sell_price_retail || 0,
                        sell_price_wholesale: selPkgFinal?.sell_price_wholesale || 0,
                        last_buy_price:       bulkItem.last_buy_price,
                        total:                bulkItem.total,
                        ppn_pct:              ppn,
                        diskon_mode:          dm,
                        diskon_value:         dv,
                        harga_nett:           calcItemNett(bulkItem.buy_price, ppn, dm, dv)
                    });
                }
                addedCount++;
            });

            if (addedCount > 0) {
                renderCart();
                showToast(`${addedCount} produk ditambahkan ke daftar`, 'success');
                return true;
            } else {
                showToast('Belum ada barang yang diisi Qty & Total Harga', 'warning');
                return false;
            }
        }
    });
}

/** Handle bulk item packaging level change */
function onBulkLevelChange(bulkId, newLevel) {
    const bulkItem = bulkItems.find(b => b.id == bulkId);
    if (!bulkItem) return;
    const el = document.querySelector(`.bulk-item[data-bulk-id="${bulkId}"]`);
    if (!el) return;
    const pkg = bulkItem.packagings.find(p => p.level == newLevel);
    if (!pkg) return;
    bulkItem.level     = parseInt(newLevel);
    bulkItem.unit_name = pkg.unit_name;
    bulkItem.buy_price = parseFloat(pkg.buy_price) || 0;
    // Refresh mini table & trend banner using the shared helpers
    const miniTbl = el.querySelector('.bulk-mini-table');
    if (miniTbl) miniTbl.innerHTML = buildMiniPricingTableHtml(bulkItem);
    const trendEl = el.querySelector('.bulk-trend-banner');
    if (trendEl) trendEl.innerHTML = buildTrendBannerHtml(bulkItem);
}

/**
 * Unified main input change handler for bulk items.
 * Mirrors onMainInputChange() but operates on the module-level bulkItems array.
 */
function onBulkMainChange(bulkId, field, val) {
    const bulkItem = bulkItems.find(b => b.id == bulkId);
    if (!bulkItem) return;
    const el = document.querySelector(`.bulk-item[data-bulk-id="${bulkId}"]`);
    if (!el) return;

    // Update the field on the bulkItem
    if (field === 'ppn')          bulkItem.ppn_pct      = parseFloat(val) || 0;
    if (field === 'diskon_mode')  bulkItem.diskon_mode  = val || 'rp';
    if (field === 'diskon_value') bulkItem.diskon_value = parseFloat(val) || 0;

    if (field === 'qty') {
        bulkItem.quantity = parseFloat(val) || 0;
        // Update total field to reflect new qty
        const totalInp = el.querySelector('.bulk-total');
        if (totalInp && bulkItem.buy_price > 0) {
            totalInp.value = Math.round(bulkItem.quantity * bulkItem.buy_price);
        }
    }

    if (field === 'total') {
        const total = parseFloat(val) || 0;
        const qty   = parseFloat(el.querySelector('.bulk-qty')?.value) || 1;
        if (total > 0 && qty > 0) {
            bulkItem.quantity  = qty;
            bulkItem.buy_price = Math.round(total / qty);
            // Sync selected level packaging buy_price
            const selPkg = bulkItem.packagings.find(p => p.level == bulkItem.level);
            if (selPkg) selPkg.buy_price = bulkItem.buy_price;
        }
    }

    // Propagate buy price, PPN, discount to all packaging levels
    propagateFromMainInputs(bulkItem);

    // Refresh mini pricing table & trend banner via shared helpers
    const miniTbl = el.querySelector('.bulk-mini-table');
    if (miniTbl) miniTbl.innerHTML = buildMiniPricingTableHtml(bulkItem);
    const trendEl = el.querySelector('.bulk-trend-banner');
    if (trendEl) trendEl.innerHTML = buildTrendBannerHtml(bulkItem);

    // If drawer is open, refresh its rows with updated values
    const drawer = el.querySelector('.bulk-drawer');
    if (drawer && drawer.style.display !== 'none') {
        drawer.querySelectorAll('.drawer-pkg-row').forEach(rowEl => {
            const level = parseInt(rowEl.dataset.level);
            const pkg   = bulkItem.packagings.find(p => p.level == level);
            if (!pkg) return;
            const ppn  = pkg.ppn_pct || 0;
            const dm   = pkg.diskon_mode || 'rp';
            const dv   = pkg.diskon_value || 0;
            const nett = pkg.harga_nett || pkg.buy_price || 0;
            // Update PPN/Diskon info badge
            const badgesEl = rowEl.querySelector('.pkg-ppn-diskon-badge');
            if (badgesEl) {
                badgesEl.innerHTML = `PPN: <strong>${ppn}%</strong>&nbsp;|&nbsp;Diskon: <strong>${dm === 'pct' ? dv + '%' : 'Rp' + Math.round(dv).toLocaleString('id-ID')}</strong>&nbsp;|&nbsp;Nett: <strong style="color:var(--info);">Rp${Math.round(nett).toLocaleString('id-ID')}</strong>`;
            }
            if (!pkg.buy_custom) {
                const buyInp = rowEl.querySelector('.drawer-pkg-buy');
                if (buyInp) buyInp.value = Math.round(pkg.buy_price);
            }
            if (!pkg.sell_custom) {
                const retInp = rowEl.querySelector('.drawer-pkg-ret');
                const whoInp = rowEl.querySelector('.drawer-pkg-who');
                if (retInp) retInp.value = Math.round(pkg.sell_price_retail);
                if (whoInp) whoInp.value = Math.round(pkg.sell_price_wholesale);
            }
            refreshDrawerRowMargin(rowEl);
        });
    }
} 'bi-graph-down-arrow'; color = 'var(--info)'; bg = 'rgba(76,201,240,0.1)';
            label = `<strong>Turun Rp${Math.abs(diff).toLocaleString('id-ID')}</strong> dari Rp${Math.round(lastBuy).toLocaleString('id-ID')} → Rp${Math.round(buyPerPcs).toLocaleString('id-ID')}`;
        }
        trendEl.innerHTML = `<div style="margin-top:6px;padding:6px 10px;border-radius:var(--radius-sm);background:${bg};border:1px solid ${color}30;font-size:9px;color:${color};display:flex;gap:6px;align-items:flex-start;">
            <i class="bi ${icon}" style="flex-shrink:0;margin-top:1px;"></i><span>${label}</span></div>`;
    } else if (trendEl) {
        trendEl.innerHTML = '';
    }
}

/** Toggle bulk drawer open/close */
function toggleBulkDrawer(bulkId, btn) {
    const el     = btn.closest('.bulk-item');
    const drawer = el.querySelector('.bulk-drawer');
    if (!drawer) return;
    const isOpen = drawer.style.display !== 'none';
    drawer.style.display = isOpen ? 'none' : 'block';
    btn.innerHTML = isOpen
        ? '<i class="bi bi-tags"></i> Ubah Harga Kemasan Lainnya'
        : '<i class="bi bi-chevron-up"></i> Tutup Panel Kemasan';
    btn.style.borderStyle = isOpen ? 'dashed' : 'solid';
}

function filterBulkModal(keyword) {
    const term  = keyword.toLowerCase();
    document.querySelectorAll('.bulk-item').forEach(item => {
        const name = item.querySelector('[style*="font-weight:700"]')?.textContent?.toLowerCase() || '';
        item.style.display = name.includes(term) ? 'block' : 'none';
    });
}


</script>
