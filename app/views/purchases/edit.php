<!-- Purchases Create View — Sales → Supplier (auto) → Product -->
<?php /** @var string $csrfToken */ ?>
<div class="page-section">
    <div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
            <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">Edit Barang Masuk</h2>
            <p style="font-size:var(--font-size-sm); color:var(--text-muted);">Pilih sales, supplier terisi otomatis, lalu scan/cari produk</p>
        </div>
        <a href="<?= BASE_URL ?>purchases" class="btn-outline-custom" style="font-size:var(--font-size-xs); padding:6px 10px; text-decoration:none;">
            <i class="bi bi-clock-history"></i> Riwayat
        </a>
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
                <input type="file" id="invoicePhotoCam" accept="image/*" capture="environment" style="position:absolute; width:1px; height:1px; opacity:0; overflow:hidden; z-index:-1;" onchange="handlePhotoSelect(event, true)">
                <input type="file" id="invoicePhotoGal" accept="image/*" style="position:absolute; width:1px; height:1px; opacity:0; overflow:hidden; z-index:-1;" onchange="handlePhotoSelect(event, false)">
                <div style="display:flex; gap:4px; align-items:center;">
                    <label for="invoicePhotoCam" class="btn-outline-custom" id="btnPhotoCam" style="flex:1; padding:8px 4px; font-size:11px; display:inline-flex; align-items:center; justify-content:center; gap:4px; cursor:pointer; margin:0;">
                        <i class="bi bi-camera"></i> Kamera
                    </label>
                    <label for="invoicePhotoGal" class="btn-outline-custom" id="btnPhotoGal" style="flex:1; padding:8px 4px; font-size:11px; display:inline-flex; align-items:center; justify-content:center; gap:4px; cursor:pointer; margin:0;">
                        <i class="bi bi-image"></i> Galeri
                    </label>
                    <?php if (!empty($purchase['invoice_photo'])): ?>
                        <a href="<?= invoicePhotoUrl($purchase['invoice_photo']) ?>" target="_blank" class="btn-outline-custom" style="padding:8px; font-size:11px; text-decoration:none;" title="Lihat Foto Lama">
                            <i class="bi bi-eye"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div style="display:flex; gap:4px; margin-top:4px;">
                    <?php
                    $currentAiModel = $aiModel ?? 'openrouter/auto';
                    if ($currentAiModel === 'openrouter/auto' || $currentAiModel === 'openrouter/free' || empty($currentAiModel)) {
                        $aiButtonLabel = 'Scan dengan AI (Otomatis)';
                    } else {
                        $parts = explode('/', $currentAiModel);
                        $modelShort = end($parts);
                        $modelShort = explode(':', $modelShort)[0];
                        $modelShort = ucwords(str_replace(['-', '_'], ' ', $modelShort));
                        $aiButtonLabel = 'Scan dengan AI (' . htmlspecialchars($modelShort) . ')';
                    }
                    ?>
                    <button type="button" class="btn-primary-custom" id="btnScanAI" style="flex:1; padding:8px 4px; font-size:11px; display:none;" onclick="scanInvoiceWithAI()">
                        <i class="bi bi-robot"></i> <?= $aiButtonLabel ?>
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

    <!-- Step 3: Product Search -->
    <div id="productSearchSection" style="display:block;">
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
            
            <div style="margin-top:12px; display:flex; align-items:center; gap:8px;">
                <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:11px;font-weight:600;color:var(--text-primary);">
                    <input type="checkbox" id="chkGlobalPpn" style="width:13px;height:13px;accent-color:var(--primary);" onchange="toggleGlobalPpn()">
                    PPN (%)
                </label>
                <input type="number" id="globalPpnInput" placeholder="Misal: 11" class="form-control-dark" style="width:80px; height:26px; font-size:11px; padding:4px 8px;" disabled oninput="applyGlobalPpn()">
                <div style="font-size:10px; color:var(--text-muted);">Terapkan PPN ke semua barang di keranjang</div>
            </div>

            <div id="productSuggestions" style="margin-top:8px;"></div>
        </div>
    </div>

    <!-- Items List Header -->
    <div class="purchase-items-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
        <div style="display:flex; align-items:center; gap:8px; min-width:0;">
            <span class="section-title" style="margin:0; white-space:nowrap; display:inline-flex; align-items:center; gap:6px;">
                <i class="bi bi-3-circle" style="color:var(--primary);"></i> Daftar Barang
            </span>
            <span id="itemCountBadge" class="badge-custom badge-info" style="font-size:11px; padding:3px 8px; border-radius:12px; font-weight:700;">0 Item</span>
        </div>
        <div style="display:flex; align-items:center; gap:6px; flex-wrap:nowrap; overflow-x:auto; -webkit-overflow-scrolling:touch; max-width:100%; padding-bottom:2px;">
            <button type="button" class="btn-outline-custom" style="padding:5px 9px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:4px; border-radius:6px; white-space:nowrap;" onclick="expandAllItems()" title="Tampilkan Semua Detail Produk">
                <i class="bi bi-arrows-angle-expand"></i> Expand All
            </button>
            <button type="button" class="btn-outline-custom" style="padding:5px 9px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:4px; border-radius:6px; white-space:nowrap;" onclick="collapseAllItems()" title="Ringkaskan Tampilan Produk">
                <i class="bi bi-arrows-angle-contract"></i> Collapse All
            </button>
            <button type="button" class="btn-outline-custom" style="padding:5px 9px; font-size:11px; font-weight:600; color:var(--danger); border-color:rgba(239,68,68,0.4); background:rgba(239,68,68,0.06); display:inline-flex; align-items:center; gap:4px; border-radius:6px; white-space:nowrap;" onclick="clearAllDrafts()" title="Kosongkan Semua Inputan">
                <i class="bi bi-trash"></i> Kosongkan
            </button>
        </div>
    </div>
    
    <div id="massActionToolbar" style="display:none; background:rgba(230,57,70,0.1); border-radius:var(--radius-md); padding:8px 12px; margin-bottom:12px; align-items:center; justify-content:space-between;">
        <label style="display:flex; align-items:center; gap:8px; font-size:var(--font-size-sm); font-weight:600; color:var(--danger); cursor:pointer;">
            <input type="checkbox" id="chkSelectAllItems" style="width:16px; height:16px; accent-color:var(--danger);" onchange="toggleSelectAllItems(this)">
            <span id="massSelectCount">0 Terpilih</span>
        </label>
        <button type="button" class="btn-primary-custom" style="background:var(--danger-bg); color:var(--danger); font-size:10px; padding:4px 8px;" onclick="deleteSelectedItems()">
            <i class="bi bi-trash"></i> Hapus Terpilih
        </button>
    </div>

    <div id="purchaseItems">
        <div class="empty-state" id="emptyPurchaseState" style="padding:24px;">
            <i class="bi bi-cart-plus" style="font-size:2rem;"></i>
            <p style="margin-top:8px;">Pilih sales, lalu cari produk untuk menambahkan ke daftar</p>
        </div>
    </div>

    <!-- Invoice Adjustments & Total -->
    <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:16px; margin-top:16px; border:1px solid var(--border-color);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span style="font-weight:500; font-size:11px; color:var(--text-muted);">Subtotal Barang</span>
            <span id="purchaseSubtotal" style="font-weight:600; font-size:12px;">Rp0</span>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span style="font-weight:500; font-size:11px; color:var(--text-muted);">Diskon Nota (Rp)</span>
            <input type="number" id="invoiceDiscount" class="form-control-dark" style="width:100px; font-size:11px; padding:4px 8px; text-align:right;" value="0" min="0" oninput="calculateGrandTotal()">
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span style="font-weight:500; font-size:11px; color:var(--text-muted);">Total Sebelum PPN</span>
            <span id="purchaseTotalBeforePPN" style="font-weight:600; font-size:12px;">Rp0</span>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid var(--border-color);">
            <span style="font-weight:500; font-size:11px; color:var(--text-muted);">Total PPN</span>
            <span id="purchaseTotalPPN" style="font-weight:600; font-size:12px;">Rp0</span>
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
        <i class="bi bi-check-circle"></i> Simpan Perubahan
    </button>
</div>

<script>
// ===== Data from PHP =====
const suppliersData = [
    <?php foreach ($suppliers ?? [] as $s): ?>
        { value: '<?= $s['id'] ?>', label: <?= json_encode($s['name']) ?> },
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
let currentSubtotal = <?= (float)($purchase['total_amount'] ?? 0) ?>;
let currentGrandTotal = <?= (float)($purchase['grand_total'] ?? 0) ?>;
let filterBySupplierSales = true;
let invoicePhotoBase64 = null;

let originalPhotoImg = null;
let purchaseId = <?= (int)($purchase['id'] ?? 0) ?>;

// Inject Existing Data
const existingItems = <?= json_encode($purchase['items'] ?? []) ?>;

function handlePhotoSelect(e, isCamera) {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(event) {
        originalPhotoImg = new Image();
        originalPhotoImg.onload = function() {
            document.getElementById('photoPreviewModal').style.display = 'flex';
            document.getElementById('chkEnhancePhoto').checked = true; // Default to document mode
            applyPhotoFilter();

            const cam = document.getElementById('invoicePhotoCam');
            const gal = document.getElementById('invoicePhotoGal');
            if (cam) cam.value = '';
            if (gal) gal.value = '';
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
    invoicePhotoBase64 = canvas.toDataURL('image/webp', 0.7);
    
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

// Helper to safely optimize any high-res image down to perfect OCR dimensions (< 400KB)
async function compressImageForAI(dataUrl, maxDimension = 1500, quality = 0.82) {
    if (!dataUrl || !dataUrl.startsWith('data:image')) return dataUrl;
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            let w = img.width;
            let h = img.height;
            if (w > maxDimension || h > maxDimension) {
                if (w > h) {
                    h = Math.round((h * maxDimension) / w);
                    w = maxDimension;
                } else {
                    w = Math.round((w * maxDimension) / h);
                    h = maxDimension;
                }
            }
            const canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, w, h);
            try {
                resolve(canvas.toDataURL('image/webp', quality));
            } catch(e) {
                resolve(canvas.toDataURL('image/jpeg', quality));
            }
        };
        img.onerror = () => resolve(dataUrl);
        img.src = dataUrl;
    });
}

async function scanInvoiceWithAI() {
    if (!invoicePhotoBase64) {
        showToast('Pilih atau ambil foto invoice terlebih dahulu', 'error');
        return;
    }
    
    const btn = document.getElementById('btnScanAI');
    const originalText = btn.innerHTML;
    const _scanStart = Date.now();
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Memproses AI...';
        
        // Auto-optimize image resolution safely
        let imageToSend = invoicePhotoBase64;
        let imageSizeKb = 0;
        try {
            imageToSend = await compressImageForAI(invoicePhotoBase64, 1800, 0.85);
            imageSizeKb = Math.round((imageToSend.length * 3 / 4) / 1024);
        } catch(ce) {
            console.warn('Image pre-compression bypassed:', ce);
        }

        const payload = {
            csrf_token: csrfVal,
            image_base64: imageToSend,
            supplier_id: currentSupplierId || null
        };
        
        // Use silent + noOfflineQueue to prevent failed AI scan from entering offline sync queue
        let result;
        try {
            result = await api(`${BASE_URL}api/ai/scan-invoice`, {
                method: 'POST',
                timeout: 120000,
                silent: true,
                noOfflineQueue: true,
                body: JSON.stringify(payload)
            });
        } catch (fetchErr) {
            const elapsedMs = Date.now() - _scanStart;
            if (window.ErrorLogger) {
                window.ErrorLogger.log('ai_scan', '[AI Scan] Fetch Gagal: ' + fetchErr.message, {
                    errorType: fetchErr.name || 'FetchError',
                    elapsedMs: elapsedMs,
                    imageSizeKb: imageSizeKb,
                    supplierId: currentSupplierId || null,
                    hint: elapsedMs >= 119000 ? 'TIMEOUT (>119s) — server tidak merespons dalam batas waktu' : 'Network error — server tidak dapat dijangkau'
                });
            }
            throw fetchErr;
        }

        if (!result) throw new Error('Tidak ada respons dari server.');
        if (result.error) throw new Error(result.error);
        
        const elapsedSec = ((Date.now() - _scanStart) / 1000).toFixed(1);
        const meta = result.metadata || {};
        
        if (result.success && result.data && result.data.length > 0) {
            const matched   = result.data.filter(i => i.is_matched && i.product_id);
            const unmatched = result.data.filter(i => !i.is_matched || !i.product_id);

            showToast(`AI: ${matched.length} item cocok, ${unmatched.length} tidak dikenali (${elapsedSec}s)`, matched.length > 0 ? 'success' : 'warning');

            if (window.ErrorLogger && (unmatched.length > 0 || meta.avg_confidence < 70)) {
                window.ErrorLogger.log('ai_scan_result', '[AI Scan] Hasil Parsial/Tidak Cocok', {
                    elapsedSec: parseFloat(elapsedSec),
                    imageSizeKb: imageSizeKb,
                    model_used: meta.skill_used || 'unknown',
                    supplier_detected: meta.supplier_detected || 'unknown',
                    total_extracted: result.data.length,
                    matched_count: matched.length,
                    unmatched_count: unmatched.length,
                    avg_confidence: meta.avg_confidence || 0,
                    unmatched_names: unmatched.slice(0, 10).map(i => i.original_name || i.supplier_code || '-')
                });
            }

            for (const item of result.data) {
                if (item.is_matched && item.product_id) {
                    try {
                        const productData = item.product_data || await api(`${BASE_URL}api/products/${item.product_id}`);
                        if (productData && productData.packagings && productData.packagings.length > 0) {
                            const targetLevel = item.packaging_level || 1;
                            let selectedPkg = productData.packagings.find(p => p.level == targetLevel);
                            if (!selectedPkg) {
                                let targetUnit = (item.unit || '').toLowerCase().trim();
                                if (targetUnit) {
                                    selectedPkg = productData.packagings.find(p => p.unit_name && p.unit_name.toLowerCase().includes(targetUnit));
                                }
                            }
                            if (!selectedPkg) selectedPkg = productData.packagings[0];

                            const scanUnitPrice = parseFloat(item.unit_price) || 0;
                            const scanQty = parseFloat(item.qty) || 1;
                            const scanTotal = parseFloat(item.total_price) || (scanQty * scanUnitPrice);

                            const existingIndex = purchaseItems.findIndex(i => i.product_id == productData.id && i.level == selectedPkg.level);
                            if (existingIndex > -1) {
                                const existing = purchaseItems[existingIndex];
                                existing.quantity = scanQty;
                                if (scanUnitPrice > 0) existing.buy_price = scanUnitPrice;
                                existing.total = scanTotal;
                                propagateFromMainInputs(existing);
                                syncSellPricesWhenBuyPriceChanges(existing);
                            } else {
                                addProductToCart(productData, selectedPkg.level);
                                const addedItem = purchaseItems[0];
                                if (addedItem && addedItem.product_id == productData.id) {
                                    addedItem.quantity  = scanQty;
                                    if (scanUnitPrice > 0) addedItem.buy_price = scanUnitPrice;
                                    addedItem.total     = scanTotal;
                                    propagateFromMainInputs(addedItem);
                                    syncSellPricesWhenBuyPriceChanges(addedItem);
                                }
                            }
                        }
                    } catch(e) {
                        console.error('Failed to add AI mapped item', e);
                    }
                } else {
                    showToast('Item "' + (item.original_name || item.supplier_code || '-') + '" belum cocok, input manual.', 'warning');
                }
            }
            renderCart();
            calculateTotal();
        } else {
            const elapsedSecEnd = ((Date.now() - _scanStart) / 1000).toFixed(1);
            if (window.ErrorLogger) {
                window.ErrorLogger.log('ai_scan_empty', '[AI Scan] Hasil 0 Item — AI tidak mengekstrak apapun', {
                    elapsedSec: parseFloat(elapsedSecEnd),
                    imageSizeKb: imageSizeKb,
                    model_used: meta.skill_used || 'unknown',
                    supplier_detected: meta.supplier_detected || 'unknown',
                    raw_message: result.message || '',
                    hint: 'Periksa kualitas gambar dan konfigurasi API Key / Model di Pengaturan AI'
                });
            }
            showToast('AI tidak mengekstrak item apapun — coba foto lebih jelas', 'warning');
        }
    } catch (err) {
        const elapsedMs = Date.now() - _scanStart;
        if (window.ErrorLogger) {
            window.ErrorLogger.log('ai_scan_error', '[AI Scan] Error: ' + (err.message || 'Unknown'), {
                errorType: err.name || 'Error',
                elapsedMs: elapsedMs,
                supplierId: currentSupplierId || null,
                stack: err.stack ? err.stack.substring(0, 400) : null
            });
        }
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
        onChange: (val, label) => {
            onSalesRepPicked(val, label);
        },
        onClear: () => {
            clearSalesRepSelection();
        }
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
    loadExistingData();
});

function loadExistingData() {
    const srId = <?= json_encode($purchase['sales_rep_id'] ?? null) ?>;
    if (srId && salesRepsLookup[srId]) {
        salesRepSB.setValue(String(srId), salesRepsLookup[srId].name);
        onSalesRepPicked(String(srId), salesRepsLookup[srId].name); // Show product section + set supplier
    } else {
        const supId = <?= json_encode($purchase['supplier_id'] ?? null) ?>;
        if (supId) {
            salesRepSB.setValue('other', '📦 Other — belum tahu supplier/sales');
            onSalesRepPicked('other', '📦 Other — belum tahu supplier/sales');
            currentSupplierId = supId; // Retain supplier if mapped to other
        }
    }
    document.getElementById('purchaseDate').value = <?= json_encode($purchase['purchase_date'] ?? date('Y-m-d')) ?>;
    document.getElementById('invoiceDiscount').value = <?= json_encode($purchase['discount_amount'] ?? 0) ?>;
    
    existingItems.forEach(async (itemInfo) => {
        try {
            let data = null;
            if (typeof OfflineDB !== 'undefined') {
                try {
                    data = await OfflineDB.getProductById(itemInfo.product_id);
                } catch(err) {
                    console.warn('OfflineDB fallback:', err);
                }
            }
            if (!data && navigator.onLine) {
                data = await api(`${BASE_URL}api/products/${itemInfo.product_id}`);
            }
            if (data && !data.error) {
                addProductToCartExisting(data, itemInfo);
            }
        } catch(e) { console.warn('Gagal memuat item', e); }
    });
}

function addProductToCartExisting(product, itemInfo) {
    const realProductId = product.id; // Save actual product ID
    product.id = Date.now() + Math.random(); // Temp ID for cart array (must be unique)
    product.product_id = realProductId;  // Real product ID for backend
    product.name = product.full_name || product.short_label || 'Produk';
    product.is_manual_price = false;
    product.level = parseInt(itemInfo.level) || 1;
    product.quantity = parseFloat(itemInfo.quantity) || 1;
    product.buy_price = parseFloat(itemInfo.buy_price) || 0;
    product.ppn_pct = parseFloat(itemInfo.ppn_percent) || 0;
    product.diskon_value = parseFloat(itemInfo.discount_amount) || 0;
    if(itemInfo.discount_percent > 0) {
        product.diskon_mode = 'pct';
        product.diskon_value = parseFloat(itemInfo.discount_percent);
    } else {
        product.diskon_mode = 'rp';
    }
    
    product.packagings.forEach(p => {
        p.ppn_pct = product.ppn_pct;
        p.diskon_mode = product.diskon_mode;
        p.diskon_value = product.diskon_value;
        if (p.level == product.level) {
            p.buy_price = product.buy_price;
            p.sell_price_retail = itemInfo.sell_price_retail;
            p.sell_price_wholesale = itemInfo.sell_price_wholesale;
            p.harga_nett = itemInfo.nett_price;
        }
    });
    
    product.total = product.quantity * product.buy_price;
    purchaseItems.unshift(product);
    renderCart();
    calculateTotal();
}


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

function toggleGlobalPpn() {
    const isChecked = document.getElementById('chkGlobalPpn').checked;
    const input = document.getElementById('globalPpnInput');
    input.disabled = !isChecked;
    if (isChecked) {
        input.focus();
    } else {
        input.value = '';
        applyGlobalPpn();
    }
}

function applyGlobalPpn() {
    const val = parseFloat(document.getElementById('globalPpnInput').value) || 0;
    purchaseItems.forEach(item => {
        onMainInputChange(item.id, 'ppn', val);
    });
    if (typeof renderCart === 'function') renderCart();
}

function clearSalesRepSelection() {
    isOtherMode = false;
    currentSalesRepId = null;
    currentSupplierId = null;
    currentSalesRepName = '';
    currentSupplierName = '';
    filterBySupplierSales = true;

    document.getElementById('supplierDisplaySection').style.display = 'none';
    document.getElementById('productSearchSection').style.display = 'block';
    document.getElementById('supplierDisplay').textContent = '—';
    document.getElementById('salesRepInfo').textContent = '';

    const badge = document.getElementById('supplierBadge');
    if (badge) badge.style.display = 'none';

    const filterCb = document.getElementById('filterBySupplierSales');
    if (filterCb) {
        filterCb.checked = false;
        filterCb.disabled = false;
        filterBySupplierSales = false;
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
                <div id="modalSalesSupplierContainer"></div>
                <input type="hidden" id="modalSalesSupplier">
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
        onShown: () => {
            new SearchBox(document.getElementById('modalSalesSupplierContainer'), {
                options: suppliersData,
                placeholder: 'Cari atau pilih supplier...',
                icon: 'bi-truck',
                name: 'modalSalesSupplierDummy',
                required: true,
                clearable: true,
                onChange: (val) => { document.getElementById('modalSalesSupplier').value = val; },
                onClear: () => { document.getElementById('modalSalesSupplier').value = ''; }
            });
        },
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

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function ensureSearchSectionVisible() {
    const searchSection = document.getElementById('productSearchSection');
    if (searchSection && (searchSection.style.display === 'none' || getComputedStyle(searchSection).display === 'none')) {
        if (typeof onSalesRepPicked === 'function') {
            if (typeof salesRepSB !== 'undefined' && salesRepSB) {
                try { salesRepSB.setValue('other'); } catch(e){}
            }
            onSalesRepPicked('other', '📦 Other — belum tahu supplier/sales');
        } else {
            searchSection.style.display = 'block';
        }
    }
}

// ===== Global Hardware Barcode Scanner Listener =====
let _purchaseBarcodeBuffer = '';
let _purchaseLastKeyTime = 0;
let _purchaseBarcodeTimer = null;

document.addEventListener('keydown', function(e) {
    const activeEl = document.activeElement;
    
    // Check if another input (e.g. qty, price, discount input in cart or form) is active
    const isOtherInputFocused = activeEl && activeEl !== searchInput && (
        activeEl.tagName === 'INPUT' || 
        activeEl.tagName === 'TEXTAREA' || 
        activeEl.tagName === 'SELECT' || 
        activeEl.isContentEditable
    );

    const now = Date.now();
    const timeDiff = now - _purchaseLastKeyTime;
    _purchaseLastKeyTime = now;

    const isFastScannerSpeed = timeDiff < 50;

    // Normal typing inside another input field -> don't intercept
    if (isOtherInputFocused && !isFastScannerSpeed && _purchaseBarcodeBuffer.length === 0) {
        return;
    }

    if (e.key === 'Enter') {
        if (_purchaseBarcodeBuffer.length >= 6) {
            e.preventDefault();
            const code = _purchaseBarcodeBuffer.trim();
            _purchaseBarcodeBuffer = '';
            if (searchInput) searchInput.value = '';
            processPurchaseBarcodeOrSearch(code);
        } else if (activeEl === searchInput) {
            // Handled by searchInput keydown listener
        }
        _purchaseBarcodeBuffer = '';
        return;
    }

    // Accumulate printable characters
    if (e.key && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
        if (timeDiff > 60) {
            _purchaseBarcodeBuffer = e.key;
        } else {
            _purchaseBarcodeBuffer += e.key;
        }

        clearTimeout(_purchaseBarcodeTimer);
        _purchaseBarcodeTimer = setTimeout(() => {
            if (_purchaseBarcodeBuffer.length >= 8 && /^\d{8,16}$/.test(_purchaseBarcodeBuffer)) {
                const code = _purchaseBarcodeBuffer.trim();
                _purchaseBarcodeBuffer = '';
                if (searchInput) searchInput.value = '';
                processPurchaseBarcodeOrSearch(code);
            } else if (!isOtherInputFocused && activeEl !== searchInput && _purchaseBarcodeBuffer.length > 0) {
                if (searchInput) {
                    searchInput.focus();
                    searchInput.value = _purchaseBarcodeBuffer;
                    searchInput.dispatchEvent(new Event('input'));
                }
            }
        }, 120);
    }
});

function initPurchaseProductSearch() {
    if (!searchInput || !suggestionsDiv) return;
    const runSearch = typeof debounce === 'function'
        ? debounce(performProductSearch, 300)
        : performProductSearch;

    // Live typing for text keyword search
    searchInput.addEventListener('input', function() {
        runSearch();
    });

    // Enter key: IMMEDIATELY clear searchInput so barcodes never stack or concatenate
    searchInput.addEventListener('keydown', async function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = this.value.trim();
            this.value = ''; // IMMEDIATELY reset input
            suggestionsDiv.innerHTML = '';
            if (!q) return;

            await processPurchaseBarcodeOrSearch(q);
        }
    });
}

function scanProductBarcode() {
    ensureSearchSectionVisible();
    const searchInp = document.getElementById('productSearch');
    if (typeof BarcodeUtil !== 'undefined' && BarcodeUtil.scanBarcode) {
        BarcodeUtil.scanBarcode(searchInp, (code) => {
            if (code) {
                processPurchaseBarcodeOrSearch(code);
            }
        });
    } else {
        AppModal.show({
            title: 'Masukkan Barcode',
            icon: 'bi-upc-scan',
            iconColor: 'var(--primary-bg)',
            iconAccent: 'var(--primary)',
            bodyHTML: `<div class="modal-form-group">
                <label>Kode Barcode / SKU</label>
                <input type="text" class="form-control-dark" id="manualBarcodeInput" placeholder="Scan atau ketik kode barcode..." autocomplete="off" inputmode="numeric">
            </div>`,
            submitText: '<i class="bi bi-search"></i> Cari',
            onSubmit: () => {
                const code = document.getElementById('manualBarcodeInput')?.value?.trim();
                if (code) processPurchaseBarcodeOrSearch(code);
                return true;
            }
        });
    }
}

async function processPurchaseBarcodeOrSearch(rawQuery) {
    const q = String(rawQuery || '').trim();
    if (!q) return;

    // Auto-reveal search section & default sales if not selected yet
    ensureSearchSectionVisible();

    // 1. ALWAYS RESET search input field IMMEDIATELY to prevent barcode accumulation
    if (searchInput) searchInput.value = '';
    if (suggestionsDiv) suggestionsDiv.innerHTML = '';

    // Check if query is numeric barcode (6-16 digits)
    const isBarcode = /^\d{6,16}$/.test(q);

    if (isBarcode) {
        // Try direct barcode API lookup
        try {
            const data = await api(`${BASE_URL}api/products/barcode/${encodeURIComponent(q)}`);
            if (data && data.id) {
                let targetLevel = data.level;
                if (data.packagings && Array.isArray(data.packagings)) {
                    const matchedPkg = data.packagings.find(p => p.barcode && String(p.barcode).replace(/\s+/g, '') === q.replace(/\s+/g, ''));
                    if (matchedPkg) targetLevel = matchedPkg.level;
                }
                data.scanned_barcode = q;
                addProductToCart(data, targetLevel);
                const pkgObj = (data.packagings && data.packagings.find(p => p.level == targetLevel)) || {};
                showToast(`Produk "${data.short_label || data.full_name || data.name}" (${pkgObj.unit_name || ''}) ditambahkan`, 'success');
                if (suggestionsDiv) suggestionsDiv.innerHTML = '';
                return true;
            }
        } catch (e) {
            /* fallback to purchase search endpoint */
        }

        // Try purchase product search API by barcode
        try {
            let searchUrl;
            if (filterBySupplierSales && !isOtherMode && currentSupplierId) {
                searchUrl = `${BASE_URL}api/purchases/search-products?q=${encodeURIComponent(q)}&supplier_id=${currentSupplierId}`;
                if (currentSalesRepId) searchUrl += `&sales_rep_id=${currentSalesRepId}`;
            } else {
                searchUrl = `${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`;
            }

            const results = await api(searchUrl);
            if (Array.isArray(results) && results.length > 0) {
                // Find exact barcode match or first match
                const match = results.find(p => p.barcode === q) || results[0];
                if (match && match.id) {
                    let fullProduct = null;
                    if (typeof OfflineDB !== 'undefined') {
                        fullProduct = await OfflineDB.getProductById(match.id);
                    }
                    if (!fullProduct && navigator.onLine) {
                        fullProduct = await api(`${BASE_URL}api/products/${match.id}`);
                    }
                    if (fullProduct) {
                        let targetLevel = match.level;
                        if (fullProduct.packagings && Array.isArray(fullProduct.packagings)) {
                            const matchedPkg = fullProduct.packagings.find(p => p.barcode && String(p.barcode).replace(/\s+/g, '') === q.replace(/\s+/g, ''));
                            if (matchedPkg) targetLevel = matchedPkg.level;
                        }
                        fullProduct.scanned_barcode = q;
                        addProductToCart(fullProduct, targetLevel);
                        const pkgObj = (fullProduct.packagings && fullProduct.packagings.find(p => p.level == targetLevel)) || {};
                        showToast(`Produk "${fullProduct.short_label || fullProduct.full_name}" (${pkgObj.unit_name || ''}) ditambahkan`, 'success');
                        if (suggestionsDiv) suggestionsDiv.innerHTML = '';
                        return true;
                    }
                }
            }
        } catch (err) {
            console.error("Barcode lookup error:", err);
        }

        // Barcode NOT found: Show clear feedback, BUT searchInput remains 100% empty!
        if (suggestionsDiv) {
            suggestionsDiv.innerHTML = `
                <div style="padding:14px 16px; text-align:center; background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-md); margin-top:8px; box-shadow:var(--shadow-sm);">
                    <div style="font-size:13px; font-weight:600; color:var(--danger); margin-bottom:4px;">
                        <i class="bi bi-exclamation-triangle-fill" style="margin-right:6px;"></i> Produk Tidak Ditemukan
                    </div>
                    <div style="font-size:11px; color:var(--text-muted); margin-bottom:10px;">
                        Barcode: <strong style="color:var(--text-primary); font-family:monospace; font-size:12px;">${escapeHtml(q)}</strong>
                    </div>
                    <a href="${BASE_URL}products/create" class="btn-outline-custom" style="padding:6px 16px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                        <i class="bi bi-plus-circle"></i> Tambah Produk Baru
                    </a>
                </div>`;
        }
        showToast(`Barcode ${q} tidak ditemukan`, 'warning');
        return false;
    }

    // Text Keyword Search
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
        let data = [];
        if (!filterBySupplierSales && typeof OfflineDB !== 'undefined') {
            data = await OfflineDB.searchProducts(q);
        }

        if ((!data || data.length === 0) || filterBySupplierSales) {
            let url;
            if (filterBySupplierSales && !isOtherMode && currentSupplierId) {
                url = `${BASE_URL}api/purchases/search-products?q=${encodeURIComponent(q)}`;
                url += `&supplier_id=${currentSupplierId}`;
                if (currentSalesRepId) url += `&sales_rep_id=${currentSalesRepId}`;
            } else {
                url = `${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`;
            }
            try {
                data = await api(url);
            } catch (e) {
                if (typeof OfflineDB !== 'undefined') data = await OfflineDB.searchProducts(q);
            }
        }

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
            
            const pStr = JSON.stringify(p).replace(/'/g, "&#39;");

            return `
                <div class="search-result-item" onclick='selectProduct(${pStr})' 
                     style="padding:10px;background:var(--surface-2);border-radius:var(--radius-sm);margin-bottom:4px;cursor:pointer;${isSupplierProduct ? 'border-left:3px solid var(--success);' : ''}">
                    <div style="font-size:0.85rem;font-weight:600;">${p.full_name || p.short_label}${badge}</div>
                    <div style="font-size:0.7rem;color:var(--text-muted);">${p.brand_name || ''} · ${p.category_name || ''}${p.last_buy_price ? ' · Beli: ' + formatRupiah(p.last_buy_price) : ''}</div>
                </div>
            `;
        }).join('');
    } catch (e) {
        console.error("Product Search Error:", e);
        suggestionsDiv.innerHTML = `<div style="padding:12px;text-align:center;color:var(--danger);font-size:12px;">Pencarian gagal: ${e.message}</div>`;
    }
}

async function selectProduct(productSummary, defaultLevel = null) {
    searchInput.value = '';
    suggestionsDiv.innerHTML = '';
    try {
        let data = null;
        if (typeof OfflineDB !== 'undefined') {
            data = await OfflineDB.getProductById(productSummary.id);
        }
        if (!data && navigator.onLine) {
            data = await api(`${BASE_URL}api/products/${productSummary.id}`);
        }
        if (data) {
            const targetLevel = defaultLevel || productSummary.level || null;
            addProductToCart(data, targetLevel);
        } else {
            showToast('Produk tidak ditemukan', 'warning');
        }
    } catch (e) {
        showToast('Gagal mengambil data produk', 'error');
    }
}

function addProductToCart(product, defaultLevel = null) {
    if (!product || !Array.isArray(product.packagings) || product.packagings.length === 0) {
        showToast('Data kemasan produk tidak lengkap', 'error');
        return;
    }

    let targetLevel = defaultLevel;
    if (!targetLevel && product.scanned_barcode) {
        const cleanScanned = String(product.scanned_barcode).replace(/\s+/g, '');
        const matched = product.packagings.find(p => p.barcode && String(p.barcode).replace(/\s+/g, '') === cleanScanned);
        if (matched) targetLevel = matched.level;
    }
    if (!targetLevel && product.level) {
        targetLevel = product.level;
    }
    if (!targetLevel) {
        targetLevel = 1;
    }

    let selectedPkg = product.packagings.find(p => p.level == targetLevel) 
        || product.packagings.find(p => p.level == 1) 
        || product.packagings[0];
    
    // Ensure all packagings have PPN/diskon initialized and auto-detect custom pricing
    const lv1 = product.packagings.find(p => p.level == 1) || product.packagings[0];
    const lv1BaseQty = parseFloat(lv1?.base_qty) || 1;
    const lv1Buy = parseFloat(lv1?.buy_price) || 0;
    const lv1Ret = parseFloat(lv1?.sell_price_retail) || 0;
    const lv1Who = parseFloat(lv1?.sell_price_wholesale) || 0;

    product.packagings.forEach(p => {
        if (p.ppn_pct === undefined) p.ppn_pct = 0;
        if (p.diskon_mode === undefined) p.diskon_mode = 'rp';
        if (p.diskon_value === undefined) p.diskon_value = 0;
        p.harga_nett = parseFloat(p.buy_price) || 0;

        // Auto-detect custom flags by checking against proportional values
        if (p.level == 1) {
            p.buy_custom = false;
            p.sell_custom = false;
        } else {
            const ratio = (parseFloat(p.base_qty) || 1) / lv1BaseQty;
            const expectedRet = Math.round(lv1Ret * ratio);
            const expectedWho = Math.round(lv1Who * ratio);

            p.buy_custom = false;
            
            const diffRet = Math.abs((parseFloat(p.sell_price_retail) || 0) - expectedRet);
            const diffWho = Math.abs((parseFloat(p.sell_price_wholesale) || 0) - expectedWho);
            p.sell_custom = diffRet > 5 || diffWho > 5;
        }
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
            is_collapsed: false,
            is_manual_price: false,
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
    if (field === 'buy_price') item.is_manual_price = true;
    
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
        
        // If buy_price changed, also sync sell prices to other levels based on margin
        if (field === 'buy_price') {
            syncSellPricesWhenBuyPriceChanges(item);
        }
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
    
    // STEP 1: Update Level 1 packaging dengan harga per pcs baru
    const level1Pkg = item.packagings.find(p => p.level == 1);
    if (level1Pkg) {
        level1Pkg.buy_price = newBaseBuyPrice;
    }
    
    // STEP 2: Sync semua packaging levels dari level 1
    syncPricesFromLevel1(item);
    
    // STEP 2b: Sync harga jual dengan margin maintained
    syncSellPricesWhenBuyPriceChanges(item);
    
    // STEP 3: Update item.buy_price untuk display
    const newLevelBuyPrice = newBaseBuyPrice * baseQty;
    item.buy_price = newLevelBuyPrice;
    item.total = total;
    item.is_manual_price = true;
    
    // Update packaging di current level juga
    if (pkg) {
        pkg.buy_price = newLevelBuyPrice;
    }
    
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
    
    // Re-render tabel harga kemasan dengan harga modal yang sudah ter-update
    renderCart();
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

/**
 * Handle sell price custom toggle in modal - lock/unlock sell price per packaging
 * When unchecked: sync sell prices from level 1 with margin maintained
 * When checked: lock sell prices to be independent per packaging
 */
function onModalSellCustomToggle(chkEl, level) {
    const lvEl = chkEl.closest('.packaging-level-edit');
    if (!lvEl) return;
    
    const isCustom = chkEl.checked;
    const toggle = lvEl.querySelector('.sell-custom-toggle');
    const note = lvEl.querySelector('.sell-locked-note');
    
    // Update toggle styling
    if (toggle) toggle.classList.toggle('active', isCustom);
    if (note) note.style.display = isCustom ? 'none' : 'block';
    
    // If unchecking (disabling custom), sync sell prices from level 1 based on margin
    if (!isCustom) {
        const retEl = lvEl.querySelector('.pkg-ret');
        const whoEl = lvEl.querySelector('.pkg-wholesale');
        const buyEl = lvEl.querySelector('.pkg-buy');
        
        const level1LvEl = document.querySelector(`.packaging-level-edit[data-level="1"]`);
        if (!level1LvEl) return;
        
        const level1RetEl = level1LvEl.querySelector('.pkg-ret');
        const level1WhoEl = level1LvEl.querySelector('.pkg-wholesale');
        const level1BuyEl = level1LvEl.querySelector('.pkg-buy');
        
        if (level1RetEl && retEl && level1BuyEl && buyEl) {
            const level1Retail = parseFloat(level1RetEl.value) || 0;
            const level1Buy = parseFloat(level1BuyEl.value) || 0;
            const currentBuy = parseFloat(buyEl.value) || 0;
            
            // Calculate margin percentage from level 1
            if (level1Buy > 0 && level1Retail > 0) {
                const marginPct = (level1Retail - level1Buy) / level1Retail;
                const newRetail = Math.round(currentBuy / (1 - marginPct));
                retEl.value = newRetail;
            }
        }
        
        if (level1WhoEl && whoEl && level1BuyEl && buyEl) {
            const level1Wholesale = parseFloat(level1WhoEl.value) || 0;
            const level1Buy = parseFloat(level1BuyEl.value) || 0;
            const currentBuy = parseFloat(buyEl.value) || 0;
            
            // Calculate margin percentage from level 1
            if (level1Buy > 0 && level1Wholesale > 0) {
                const marginPct = (level1Wholesale - level1Buy) / level1Wholesale;
                const newWholesale = Math.round(currentBuy / (1 - marginPct));
                whoEl.value = newWholesale;
            }
        }
        
        // Trigger margin update
        if (retEl) onPkgModalInput(retEl, level);
    }
}

/**
 * Handle buy price custom toggle in modal - lock/unlock buy price per packaging
 * When unchecked: sync buy prices from level 1 (recalculate from pcs × isi)
 * When checked: lock buy prices to be independent per packaging
 */
function onModalBuyCustomToggle(chkEl, level) {
    const lvEl = chkEl.closest('.packaging-level-edit');
    if (!lvEl) return;
    
    const isCustom = chkEl.checked;
    const toggle = lvEl.querySelector('.buy-custom-toggle');
    const note = lvEl.querySelector('.buy-locked-note');
    
    // Update toggle styling
    if (toggle) toggle.classList.toggle('active', isCustom);
    if (note) note.style.display = isCustom ? 'none' : 'block';
    
    // If unchecking (disabling custom), sync buy price from level 1
    if (!isCustom) {
        const buyEl = lvEl.querySelector('.pkg-buy');
        const level1LvEl = document.querySelector(`.packaging-level-edit[data-level="1"]`);
        
        if (buyEl && level1LvEl) {
            const level1BuyEl = level1LvEl.querySelector('.pkg-buy');
            const baseQty = parseFloat(lvEl.dataset.baseQty) || 1;
            const level1BaseQty = parseFloat(level1LvEl.dataset.baseQty) || 1;
            
            if (level1BuyEl) {
                const level1Buy = parseFloat(level1BuyEl.value) || 0;
                const buyPerPcs = level1Buy / level1BaseQty;
                const newBuy = Math.round(buyPerPcs * baseQty);
                buyEl.value = newBuy;
                
                // Trigger margin update
                onPkgModalInput(buyEl, level);
            }
        }
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

/**
 * When buy price changes, sync sell prices to other levels based on margin from level 1
 * This ensures that margin percentage is maintained across all packaging levels
 */
function syncSellPricesWhenBuyPriceChanges(item) {
    const level1Pkg = item.packagings.find(p => p.level == 1);
    if (!level1Pkg) return;
    
    const level1Buy = parseFloat(level1Pkg.buy_price) || 0;
    const level1Retail = parseFloat(level1Pkg.sell_price_retail) || 0;
    const level1Wholesale = parseFloat(level1Pkg.sell_price_wholesale) || 0;
    
    if (level1Buy <= 0) return;
    
    // Calculate markup percentages from level 1
    const retailMarginPct = level1Buy > 0 ? (level1Retail - level1Buy) / level1Buy : 0;
    const wholesaleMarginPct = level1Buy > 0 ? (level1Wholesale - level1Buy) / level1Buy : 0;
    
    // Apply margins (markup) to other levels if sell_price is not custom
    item.packagings.forEach(pkg => {
        if (pkg.level == 1 || pkg.sell_custom) return;
        
        const pkgBuy = parseFloat(pkg.buy_price) || 0;
        if (pkgBuy <= 0) return;
        
        if (retailMarginPct > 0) {
            pkg.sell_price_retail = Math.round(pkgBuy * (1 + retailMarginPct));
        }
        if (wholesaleMarginPct > 0) {
            pkg.sell_price_wholesale = Math.round(pkgBuy * (1 + wholesaleMarginPct));
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
function calcItemNett(buy, ppn_pct, diskon_mode, diskon_value, qty = 1) {
    buy = parseFloat(buy) || 0;
    const ppn_amt = buy * ((parseFloat(ppn_pct) || 0) / 100);
    const validQty = parseFloat(qty) > 0 ? parseFloat(qty) : 1;
    
    // For Rp mode: diskon_value is total discount for the quantity purchased
    // For pct mode: percentage is applied to buy price directly
    const diskon_amt = diskon_mode === 'pct'
        ? buy * ((parseFloat(diskon_value) || 0) / 100)
        : ((parseFloat(diskon_value) || 0) / validQty);
        
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
    const diskon_amt = diskonMode === 'pct' ? buy * (diskon / 100) : (diskon / (parseFloat(item.quantity) || 1));
    let html = `<div style="background:rgba(0,0,0,0.25);border-radius:4px;padding:5px 7px;font-size:10px;">`;
    html += `<span style="color:var(--text-muted);">Modal: Rp${Math.round(buy).toLocaleString('id-ID')}</span>`;
    if (ppn > 0) html += ` <span style="color:var(--warning);">+PPN(${ppn}%): Rp${Math.round(ppn_amt).toLocaleString('id-ID')}</span>`;
    if (diskon > 0) html += ` <span style="color:var(--success);">−Diskon: Rp${Math.round(diskon_amt).toLocaleString('id-ID')}${diskonMode === 'rp' ? '/unit' : ''}</span>`;
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
    
    item.harga_nett = calcItemNett(item.buy_price, item.ppn_pct, item.diskon_mode, item.diskon_value, item.quantity);
    
    const nettEl = document.getElementById(`nett_info_${tempId}`);
    if (nettEl) nettEl.innerHTML = buildNettInfo(item);
    
    updateMarginDisplay(tempId, item.harga_nett, item.sell_price_retail, item.sell_price_wholesale);
}

/** Override function for PackagingPriceSync to calculate margins using nett price */
function calcMarginForLevel(lvEl) {
    const buy = parseFloat(lvEl?.querySelector('.pkg-buy,.buy-price')?.value) || 0;
    const ret = parseFloat(lvEl?.querySelector('.pkg-ret,.retail-price')?.value) || 0;
    const who = parseFloat(lvEl?.querySelector('.pkg-wholesale,.wholesale-price')?.value) || 0;
    const marginInfo = lvEl?.querySelector('.pkg-margin-info,.margin-calc');
    if (!marginInfo) return;
    
    // Find the item quantity (use 1 as fallback for modal edit)
    let itemQty = 1;
    const row = lvEl.closest('[data-id]');
    if (row && row.dataset.id) {
        const item = purchaseItems.find(i => i.id == row.dataset.id);
        if (item) itemQty = item.quantity || 1;
    }
    
    const ppn = parseFloat(lvEl?.querySelector('.pkg-ppn-pct')?.value) || 0;
    const diskonMode = lvEl?.querySelector('.pkg-diskon-mode')?.value || 'rp';
    const diskonVal = parseFloat(lvEl?.querySelector('.pkg-diskon-value')?.value) || 0;
    
    const nett = calcItemNett(buy, ppn, diskonMode, diskonVal, itemQty);

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

    // Recalculate buy prices per level from CURRENT item.buy_price (after Total Harga Pembelian updated)
    const currentPkg = item.packagings.find(p => p.level == item.level);
    const currentBaseQty = parseFloat(currentPkg?.base_qty) || 1;
    const buyPricePerPcs = item.buy_price / currentBaseQty;

    item.packagings.forEach(pkg => {
        // Store original prices for THIS modal opening (updated setiap kali modal dibuka)
        // This ensures comparison dengan harga terbaru, bukan harga lama yang di-cache
        pkg._orig_buy = pkg.buy_price;
        pkg._orig_ret = pkg.sell_price_retail;
        if (!pkg.qty_prices) pkg.qty_prices = [];
        if (pkg.ppn_pct === undefined) pkg.ppn_pct = 0;
        if (pkg.diskon_mode === undefined) pkg.diskon_mode = 'rp';
        if (pkg.diskon_value === undefined) pkg.diskon_value = 0;
        // Initialize custom flags if not set
        if (pkg.buy_custom === undefined) pkg.buy_custom = false;
        if (pkg.sell_custom === undefined) pkg.sell_custom = false;
        
        // Recalculate non-custom buy prices berdasarkan current selected level
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

        // Suggested sell price based on prev markup
        let suggestedHtml = '';
        if (origBuy > 0 && origRet > 0) {
            const prevMarkup = (origRet - origBuy) / origBuy;
            if (prevMarkup > 0) {
                const sugRet = Math.round(pkg.buy_price * (1 + prevMarkup));
                suggestedHtml = `<div style="font-size:10px;color:var(--info);margin-top:3px;margin-bottom:6px;"><i class="bi bi-lightbulb"></i> Saran jual ecer (markup ${(prevMarkup*100).toFixed(1)}%): <strong>Rp${sugRet.toLocaleString('id-ID')}</strong></div>`;
            }
        }

        // Tier rows
        const tiers = pkg.qty_prices || [];
        let tierRowsHtml = tiers.map(t => {
            const totalH = Math.round((parseFloat(t.min_qty)||0) * (parseFloat(t.unit_price)||0));
            return `
            <div class="tier-row" style="margin-bottom:6px;">
                <div style="display:grid;grid-template-columns:minmax(0,0.8fr) minmax(0,1fr) minmax(0,1fr) 30px;gap:4px;margin-bottom:4px;align-items:center;">
                    <input type="number" class="form-control-dark tier-min-qty" style="font-size:10px;padding:4px;min-width:0;box-sizing:border-box;width:100%;" placeholder="Qty" value="${t.min_qty}" min="1">
                    <input type="number" class="form-control-dark tier-total-harga" style="font-size:10px;padding:4px;color:var(--success);min-width:0;box-sizing:border-box;width:100%;" placeholder="Total" value="${totalH}" min="0" oninput="recalcTierHint(this)">
                    <div class="dropdown" style="width:100%;">
                        <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:4px; font-size:10px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                            <span>${t.sale_mode==='retail'?'Ecer':t.sale_mode==='wholesale'?'Grosir':'E+G'}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:10px; min-width:100%;">
                            <li><a class="dropdown-item ${t.sale_mode==='both'?'active':''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='both'; dp.querySelector('button span').textContent='E+G'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">E+G</a></li>
                            <li><a class="dropdown-item ${t.sale_mode==='retail'?'active':''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='retail'; dp.querySelector('button span').textContent='Ecer'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">Ecer</a></li>
                            <li><a class="dropdown-item ${t.sale_mode==='wholesale'?'active':''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='wholesale'; dp.querySelector('button span').textContent='Grosir'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">Grosir</a></li>
                        </ul>
                        <input type="hidden" class="tier-mode" value="${t.sale_mode||'both'}">
                    </div>
                    <button type="button" onclick="this.closest('.tier-row').remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:4px 6px;cursor:pointer;font-size:11px;min-width:0;"><i class="bi bi-x"></i></button>
                </div>
                <input type="text" class="form-control-dark tier-label" value="${t.label||''}" placeholder="Label (opsional)" style="font-size:10px;padding:4px;width:100%;box-sizing:border-box;">
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
                    <input type="checkbox" class="chk-buy-custom" ${pkg.buy_custom?'checked':''} onchange="onModalBuyCustomToggle(this, ${pkg.level})">
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
                                <div class="discount-toggle-group" style="display:flex; border-radius:var(--radius-md) 0 0 var(--radius-md); overflow:hidden; border:1px solid var(--border-color); border-right:none; width:50px;">
                                    <button type="button" class="btn-discount-mode rp-mode ${(pkg.diskon_mode||'rp')==='rp'?'active':''}" style="flex:1; padding:4px 0; background:${(pkg.diskon_mode||'rp')==='rp'?'var(--primary)':'var(--bg-input)'}; color:${(pkg.diskon_mode||'rp')==='rp'?'#fff':'var(--text-muted)'}; border:none; font-size:10px; font-weight:bold; cursor:pointer;" onclick="event.preventDefault(); const p=this.closest('.discount-toggle-group'); p.querySelector('.pct-mode').style.background='var(--bg-input)'; p.querySelector('.pct-mode').style.color='var(--text-muted)'; this.style.background='var(--primary)'; this.style.color='#fff'; const hidden=p.nextElementSibling; hidden.value='rp'; hidden.dispatchEvent(new Event('change'));">Rp</button>
                                    <button type="button" class="btn-discount-mode pct-mode ${pkg.diskon_mode==='pct'?'active':''}" style="flex:1; padding:4px 0; background:${pkg.diskon_mode==='pct'?'var(--primary)':'var(--bg-input)'}; color:${pkg.diskon_mode==='pct'?'#fff':'var(--text-muted)'}; border:none; font-size:10px; font-weight:bold; cursor:pointer;" onclick="event.preventDefault(); const p=this.closest('.discount-toggle-group'); p.querySelector('.rp-mode').style.background='var(--bg-input)'; p.querySelector('.rp-mode').style.color='var(--text-muted)'; this.style.background='var(--primary)'; this.style.color='#fff'; const hidden=p.nextElementSibling; hidden.value='pct'; hidden.dispatchEvent(new Event('change'));">%</button>
                                </div>
                                <input type="hidden" id="mod_diskon_mode_${pkg.level}" class="pkg-diskon-mode" value="${pkg.diskon_mode||'rp'}" onchange="onPkgModalInput(this, ${pkg.level})">
                                <input type="number" id="mod_diskon_value_${pkg.level}" class="form-control-dark pkg-diskon-value" step="0.01" style="flex:1;padding:4px;font-size:11px;" value="${pkg.diskon_value || 0}" min="0" placeholder="0" oninput="onPkgModalInput(this, ${pkg.level})">
                            </div>
                        </div>
                    </div>
                    <div class="pkg-nett-info" style="min-height:14px;font-size:10px;"></div>
                </div>

                ${suggestedHtml}
                ${!isLevel1 ? `
                <label class="price-custom-toggle sell-custom-toggle ${pkg.sell_custom?'active':''}" title="Centang untuk harga jual manual">
                    <input type="checkbox" class="chk-sell-custom" ${pkg.sell_custom?'checked':''} onchange="onModalSellCustomToggle(this, ${pkg.level})">
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
                        const label = row.querySelector('.tier-label')?.value?.trim() || '';
                        if (minQty > 0 && totalH > 0) {
                            tiers.push({ min_qty: minQty, unit_price: totalH / minQty, sale_mode: mode, label: label });
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
    row.style.cssText = 'margin-bottom:6px;';
    row.innerHTML = `
        <div style="display:grid;grid-template-columns:minmax(0,0.8fr) minmax(0,1fr) minmax(0,1fr) 30px;gap:4px;margin-bottom:4px;align-items:center;">
            <input type="number" class="form-control-dark tier-min-qty" style="font-size:10px;padding:4px;min-width:0;box-sizing:border-box;width:100%;" placeholder="Qty" min="1">
            <input type="number" class="form-control-dark tier-total-harga" style="font-size:10px;padding:4px;color:var(--success);min-width:0;box-sizing:border-box;width:100%;" placeholder="Total" min="0" oninput="recalcTierHint(this)">
            <div class="dropdown" style="width:100%;">
                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:4px; font-size:10px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                    <span>E+G</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:10px; min-width:100%;">
                    <li><a class="dropdown-item active" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='both'; dp.querySelector('button span').textContent='E+G'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">E+G</a></li>
                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='retail'; dp.querySelector('button span').textContent='Ecer'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">Ecer</a></li>
                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='wholesale'; dp.querySelector('button span').textContent='Grosir'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">Grosir</a></li>
                </ul>
                <input type="hidden" class="tier-mode" value="both">
            </div>
            <button type="button" onclick="this.closest('.tier-row').remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:4px 6px;cursor:pointer;font-size:11px;min-width:0;"><i class="bi bi-x"></i></button>
        </div>
        <input type="text" class="form-control-dark tier-label" placeholder="Label (opsional)" style="font-size:10px;padding:4px;width:100%;box-sizing:border-box;">`;
    container.appendChild(row);
}

/** Show per-unit hint for tier total harga input */
function recalcTierHint(el) {
    const row = el.closest('.tier-row') || el.closest('.drawer-tier-row');
    if (!row) return;
    const minQty = parseFloat(row.querySelector('.tier-min-qty, .drawer-tier-min-qty')?.value) || 0;
    const totalH = parseFloat(row.querySelector('.tier-total-harga, .drawer-tier-total')?.value) || 0;
    let hint = row.querySelector('.tier-hint');
    if (!hint) {
        hint = document.createElement('div');
        hint.className = 'tier-hint';
        hint.style.cssText = 'font-size:9px;color:var(--text-muted);padding-left:2px;';
        row.querySelector('.tier-mode, .drawer-tier-mode').parentElement.after(hint);
    }
    hint.textContent = (minQty > 0 && totalH > 0)
        ? `≈ Rp${Math.round(totalH/minQty).toLocaleString('id-ID')} / pcs`
        : '';
}



function toggleItemCollapse(uid) {
    const item = purchaseItems.find(i => i.id == uid);
    if (!item) return;
    item.is_collapsed = !item.is_collapsed;
    renderCart();
}

function expandAllItems() {
    purchaseItems.forEach(item => { item.is_collapsed = false; });
    renderCart();
}

function collapseAllItems() {
    purchaseItems.forEach(item => { item.is_collapsed = true; });
    renderCart();
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
    
    let totalAfterDiscount = currentSubtotal - discount;
    if (totalAfterDiscount < 0) totalAfterDiscount = 0;
    
    let totalPpn = 0;
    for (const item of purchaseItems) {
        const itemPpnAmt = item.buy_price * ((item.ppn_pct || 0) / 100) * (item.quantity || 1);
        totalPpn += itemPpnAmt;
    }
    
    currentGrandTotal = totalAfterDiscount + totalPpn;
    
    document.getElementById('purchaseSubtotal').textContent = formatRupiah(currentSubtotal);
    const beforePpnEl = document.getElementById('purchaseTotalBeforePPN');
    if(beforePpnEl) beforePpnEl.textContent = formatRupiah(totalAfterDiscount);
    const ppnEl = document.getElementById('purchaseTotalPPN');
    if(ppnEl) ppnEl.textContent = formatRupiah(totalPpn);
    document.getElementById('purchaseGrandTotal').textContent = formatRupiah(currentGrandTotal);
}

// ===== Draft and Mass Actions =====
const EDIT_DRAFT_KEY = 'alfarezmart_purchase_edit_draft_' + (typeof purchaseId !== 'undefined' ? purchaseId : 'edit');

function saveDraft() {
    if (typeof purchaseItems !== 'undefined' && Array.isArray(purchaseItems)) {
        purchaseItems.forEach(item => collectDrawerDataForItem(item.id));
    }
    const draft = {
        purchaseDate: document.getElementById('purchaseDate')?.value || '',
        invoiceDiscount: document.getElementById('invoiceDiscount')?.value || '',
        invoiceTax: document.getElementById('invoiceTax')?.value || '',
        items: purchaseItems
    };
    try {
        localStorage.setItem(EDIT_DRAFT_KEY, JSON.stringify(draft));
    } catch (e) {
        console.warn('Gagal menyimpan draft edit ke localStorage', e);
    }
}

function loadDraft() {
    try {
        const draftJson = localStorage.getItem(EDIT_DRAFT_KEY);
        if (!draftJson) return;
        const draft = JSON.parse(draftJson);
        
        if (draft.purchaseDate && document.getElementById('purchaseDate')) document.getElementById('purchaseDate').value = draft.purchaseDate;
        if (draft.invoiceDiscount && document.getElementById('invoiceDiscount')) document.getElementById('invoiceDiscount').value = draft.invoiceDiscount;
        if (draft.invoiceTax && document.getElementById('invoiceTax')) document.getElementById('invoiceTax').value = draft.invoiceTax;

        if (Array.isArray(draft.items) && draft.items.length > 0) {
            purchaseItems = draft.items;
            renderCart();
            calculateTotal();
            showToast('Draft perubahan terakhir berhasil dimuat', 'info');
        }
    } catch (e) {
        console.warn('Gagal memuat draft edit', e);
    }
}

async function clearAllDrafts() {
    if (purchaseItems.length === 0) return;
    const confirm = await AppModal.show({
        title: 'Kosongkan Inputan?',
        bodyHTML: 'Semua barang dalam daftar ini akan dihapus.',
        submitText: 'Ya, Kosongkan',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)'
    });
    if (!confirm) return;
    
    purchaseItems = [];
    localStorage.removeItem('alfarezmart_purchase_draft');
    
    document.getElementById('invoiceDiscount').value = '0';
    document.getElementById('invoiceTax').value = '0';
    
    renderCart();
    calculateTotal();
    showToast('Semua inputan berhasil dibersihkan');
}

function toggleSelectAllItems(chk) {
    const checkboxes = document.querySelectorAll('.item-select-chk');
    checkboxes.forEach(c => c.checked = chk.checked);
    updateMassSelect();
}

function updateMassSelect() {
    const checkboxes = document.querySelectorAll('.item-select-chk');
    const checkedCount = Array.from(checkboxes).filter(c => c.checked).length;
    const masterChk = document.getElementById('chkSelectAllItems');
    if (masterChk) {
        masterChk.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
        masterChk.indeterminate = (checkedCount > 0 && checkedCount < checkboxes.length);
    }
    const countEl = document.getElementById('massSelectCount');
    if (countEl) countEl.textContent = `${checkedCount} Terpilih`;
}

async function deleteSelectedItems() {
    const checkboxes = document.querySelectorAll('.item-select-chk:checked');
    if (checkboxes.length === 0) return;
    
    const confirm = await AppModal.show({
        title: `Hapus ${checkboxes.length} Barang?`,
        bodyHTML: `Anda akan menghapus ${checkboxes.length} barang terpilih dari daftar.`,
        submitText: 'Hapus Terpilih',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)'
    });
    if (!confirm) return;
    
    const idsToDelete = Array.from(checkboxes).map(c => parseInt(c.value));
    purchaseItems = purchaseItems.filter(item => !idsToDelete.includes(item.id));
    
    renderCart();
    calculateTotal();
    showToast(`${idsToDelete.length} barang dihapus`);
}

function distributeAdjustments() {
    purchaseItems.forEach(item => collectDrawerDataForItem(item.id));
    if (purchaseItems.length === 0 || currentSubtotal === 0) return;
    
    // The ratio of grand_total to subtotal
    const ratio = currentGrandTotal / currentSubtotal;
    
    purchaseItems.forEach(item => {
        if (item.is_manual_price) return;
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
    const m = ((sell - buy) / buy * 100).toFixed(1);
    const profit = sell - buy;
    const color = label === 'Ecer' ? (m >= 10 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)'))
                                   : (m >= 5 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)'));
    return `Markup ${label}: <strong style="color:${color}">${m}%</strong> <span style="font-size:10px;color:var(--text-muted);">(Rp${Math.round(profit).toLocaleString('id-ID')})</span>`;
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
    const qty        = parseFloat(item.quantity) || 1;

    item.packagings.forEach(pkg => {
        const bq = parseFloat(pkg.base_qty) || 1;

        // --- Buy Price ---
        if (!pkg.buy_custom) {
            pkg.buy_price = buyPerPcs * bq;
        }

        // --- PPN (uniform) ---
        pkg.ppn_pct = item.ppn_pct || 0;

        // --- Diskon & Nett ---
        if (item.diskon_mode === 'pct') {
            pkg.diskon_mode  = 'pct';
            pkg.diskon_value = item.diskon_value || 0;
            pkg.harga_nett   = calcItemNett(pkg.buy_price, pkg.ppn_pct, 'pct', pkg.diskon_value, 1);
        } else {
            pkg.diskon_mode  = 'rp';
            pkg.diskon_value = item.diskon_value || 0;
            const totalPcs = qty * selBaseQty;
            const discPerPcs = totalPcs > 0 ? ((parseFloat(item.diskon_value) || 0) / totalPcs) : 0;
            const discForPkg = discPerPcs * bq;
            const ppnAmt = pkg.buy_price * ((parseFloat(pkg.ppn_pct) || 0) / 100);
            pkg.harga_nett = Math.max(0, pkg.buy_price + ppnAmt - discForPkg);
        }

        // --- Do not modify sell prices ---
        // (Harga jual tetap seperti di database, jangan diubah)
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
        const profitR = ret > 0 ? (ret - nett) : null;
        const profitW = who > 0 ? (who - nett) : null;
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
                ${mR !== null ? `<div style="color:${cR};font-size:8px;">${mR.toFixed(1)}% <span style="opacity:0.7">(Rp${Math.round(profitR).toLocaleString('id-ID')})</span></div>` : ''}
            </td>
            <td style="padding:5px 6px;font-size:10px;text-align:right;">
                <span style="color:var(--warning);font-weight:600;">${who > 0 ? 'Rp' + who.toLocaleString('id-ID') : '—'}</span>
                ${mW !== null ? `<div style="color:${cW};font-size:8px;">${mW.toFixed(1)}% <span style="opacity:0.7">(Rp${Math.round(profitW).toLocaleString('id-ID')})</span></div>` : ''}
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
        const nett      = pkg.harga_nett || calcItemNett(buy, ppn, dm, dv, item.quantity);
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
            return `<div class="drawer-tier-row" style="margin-bottom:6px;">
                <div style="display:grid;grid-template-columns:minmax(0,0.8fr) minmax(0,1fr) minmax(0,1fr) 30px;gap:4px;margin-bottom:4px;align-items:center;">
                    <input type="number" class="form-control-dark drawer-tier-min-qty" style="font-size:10px;padding:4px;min-width:0;box-sizing:border-box;width:100%;" placeholder="Qty" value="${t.min_qty}" min="1">
                    <input type="number" class="form-control-dark drawer-tier-total" style="font-size:10px;padding:4px;color:var(--success);min-width:0;box-sizing:border-box;width:100%;" placeholder="Total" value="${th}" min="0" oninput="recalcTierHint(this)">
                    <div class="dropdown" style="width:100%;">
                        <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:4px; font-size:10px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                            <span>${t.sale_mode==='retail'?'Ecer':t.sale_mode==='wholesale'?'Grosir':'E+G'}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:10px; min-width:100%;">
                            <li><a class="dropdown-item ${t.sale_mode==='both'?'active':''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='both'; dp.querySelector('button span').textContent='E+G'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">E+G</a></li>
                            <li><a class="dropdown-item ${t.sale_mode==='retail'?'active':''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='retail'; dp.querySelector('button span').textContent='Ecer'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">Ecer</a></li>
                            <li><a class="dropdown-item ${t.sale_mode==='wholesale'?'active':''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='wholesale'; dp.querySelector('button span').textContent='Grosir'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">Grosir</a></li>
                        </ul>
                        <input type="hidden" class="drawer-tier-mode" value="${t.sale_mode||'both'}">
                    </div>
                    <button type="button" onclick="this.closest('.drawer-tier-row').remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:4px 6px;cursor:pointer;font-size:11px;min-width:0;"><i class="bi bi-x"></i></button>
                </div>
                <input type="text" class="form-control-dark drawer-tier-label" value="${t.label||''}" placeholder="Label (opsional)" style="font-size:10px;padding:4px;width:100%;box-sizing:border-box;">
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
        // Refresh mini table and trend banner (now outside drawer, in main card)
        refreshMiniTableForItem(uid);
        
        // Refresh drawer inputs to ensure they match item.packagings
        refreshOpenDrawer(uid);
        
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
    row.style.cssText = 'margin-bottom:6px;';
    row.innerHTML = `
        <div style="display:grid;grid-template-columns:minmax(0,0.8fr) minmax(0,1fr) minmax(0,1fr) 30px;gap:4px;margin-bottom:4px;align-items:center;">
            <input type="number" class="form-control-dark drawer-tier-min-qty" style="font-size:10px;padding:4px;min-width:0;box-sizing:border-box;width:100%;" placeholder="Qty" min="1">
            <input type="number" class="form-control-dark drawer-tier-total" style="font-size:10px;padding:4px;color:var(--success);min-width:0;box-sizing:border-box;width:100%;" placeholder="Total" value="" min="0" oninput="recalcTierHint(this)">
            <div class="dropdown" style="width:100%;">
                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:4px; font-size:10px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                    <span>E+G</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:10px; min-width:100%;">
                    <li><a class="dropdown-item active" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='both'; dp.querySelector('button span').textContent='E+G'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">E+G</a></li>
                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='retail'; dp.querySelector('button span').textContent='Ecer'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">Ecer</a></li>
                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='wholesale'; dp.querySelector('button span').textContent='Grosir'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">Grosir</a></li>
                </ul>
                <input type="hidden" class="drawer-tier-mode" value="both">
            </div>
            <button type="button" onclick="this.closest('.drawer-tier-row').remove()" style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:4px 6px;cursor:pointer;font-size:11px;min-width:0;"><i class="bi bi-x"></i></button>
        </div>
        <input type="text" class="form-control-dark drawer-tier-label" placeholder="Label (opsional)" style="font-size:10px;padding:4px;width:100%;box-sizing:border-box;">`;
    container.appendChild(row);
}

/** Recalc margin display for a single drawer-pkg-row element */
function refreshDrawerRowMargin(rowEl) {
    const buy   = parseFloat(rowEl.querySelector('.drawer-pkg-buy')?.value) || 0;
    const ppn   = parseFloat(rowEl.closest('[data-item-ppn]')?.dataset.itemPpn || rowEl.closest('.item-card')?.dataset.ppn || 0);
    // Include discount if available, but simplified here; get exact nett from the item state if possible
    let nett = calcItemNett(buy, ppn, 'rp', 0);
    
    // Attempt to get exact nett from the UI badge or dataset if available to include discounts correctly
    const uid = rowEl.closest('[id^="drawer_"]')?.id.split('_')[1];
    if (uid) {
        let item = typeof purchaseItems !== 'undefined' ? purchaseItems.find(i => i.id == uid) : null;
        if (!item && typeof bulkItems !== 'undefined') item = bulkItems.find(b => b.id == uid);
        if (item) {
            const level = parseInt(rowEl.dataset.level || 1, 10);
            const pkg = item.packagings.find(p => p.level == level);
            if (pkg) nett = pkg.harga_nett || calcItemNett(buy, pkg.ppn_pct, pkg.diskon_mode, pkg.diskon_value);
        }
    }

    const ret   = parseFloat(rowEl.querySelector('.drawer-pkg-ret')?.value) || 0;
    const who   = parseFloat(rowEl.querySelector('.drawer-pkg-who')?.value) || 0;
    const rEl   = rowEl.querySelector('.drawer-margin-retail');
    const wEl   = rowEl.querySelector('.drawer-margin-wholesale');
    if (rEl) rEl.innerHTML = formatMarginWithProfit('Ecer', nett, ret);
    if (wEl) wEl.innerHTML = formatMarginWithProfit('Grosir', nett, who);
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
    pkg.buy_custom = true;
    pkg.harga_nett = calcItemNett(pkg.buy_price, pkg.ppn_pct, pkg.diskon_mode, pkg.diskon_value);
    
    // Propagate if level 1
    if (level === 1) {
        item.packagings.forEach(p => {
            if (p.level === 1 || p.buy_custom) return;
            const ratio = (parseFloat(p.base_qty) || 1) / (parseFloat(pkg.base_qty) || 1);
            p.buy_price = pkg.buy_price * ratio;
            p.harga_nett = calcItemNett(p.buy_price, p.ppn_pct, p.diskon_mode, p.diskon_value);
        });
        
        if (item.level === 1) {
            item.buy_price = pkg.buy_price;
            item.total = item.quantity * item.buy_price;
            const totalInp = document.getElementById(prefix === 'bulk' ? '' : `main_total_${uid}`);
            if (totalInp && prefix !== 'bulk') totalInp.value = item.total;
            else if (prefix === 'bulk') {
                const rowEl = document.querySelector(`.bulk-item[data-bulk-id="${uid}"]`);
                if (rowEl) {
                    const blkTot = rowEl.querySelector('.bulk-total');
                    if (blkTot) blkTot.value = item.total;
                }
            }
        }
        
        refreshOpenDrawer(uid);
    }

    // Update mini table live (handles both regular and bulk)
    refreshMiniTableForItem(uid);
    // Update margin in drawer row
    const isBulk = (prefix === 'bulk');
    const rowEl = isBulk
        ? document.querySelector(`.bulk-item[data-bulk-id="${uid}"] .drawer-pkg-row[data-level="${level}"]`)
        : document.querySelector(`#drawer_${uid} .drawer-pkg-row[data-level="${level}"]`);
    if (rowEl) refreshDrawerRowMargin(rowEl);
    saveDraft();
}

function onDrawerSellInput(prefix, uid, level, type, newVal) {
    let item = purchaseItems.find(i => i.id == uid);
    if (!item) item = bulkItems.find(b => b.id == uid);
    if (!item) return;
    const pkg = item.packagings.find(p => p.level == level);
    if (!pkg) return;
    if (type === 'retail')    pkg.sell_price_retail    = parseFloat(newVal) || 0;
    if (type === 'wholesale') pkg.sell_price_wholesale = parseFloat(newVal) || 0;
    pkg.sell_custom = true; // Lock custom sale price
    
    // Propagate if level 1
    if (level === 1) {
        const baseVal = parseFloat(newVal) || 0;
        item.packagings.forEach(p => {
            if (p.level === 1 || p.sell_custom) return;
            const ratio = (parseFloat(p.base_qty) || 1) / (parseFloat(pkg.base_qty) || 1);
            if (type === 'retail') p.sell_price_retail = baseVal * ratio;
            if (type === 'wholesale') p.sell_price_wholesale = baseVal * ratio;
        });
        refreshOpenDrawer(uid);
    }

    refreshMiniTableForItem(uid);
    const isBulk = (prefix === 'bulk');
    const rowEl = isBulk
        ? document.querySelector(`.bulk-item[data-bulk-id="${uid}"] .drawer-pkg-row[data-level="${level}"]`)
        : document.querySelector(`#drawer_${uid} .drawer-pkg-row[data-level="${level}"]`);
    if (rowEl) refreshDrawerRowMargin(rowEl);
    saveDraft();
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
            pkg.buy_price = buyPcs * (parseFloat(pkg.base_qty) || 1);
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
            pkg.sell_price_retail    = (parseFloat(selPkg.sell_price_retail)||0) / bqSel * bqThis;
            pkg.sell_price_wholesale = (parseFloat(selPkg.sell_price_wholesale)||0) / bqSel * bqThis;
            const retInp = rowEl.querySelector('.drawer-pkg-ret');
            const whoInp = rowEl.querySelector('.drawer-pkg-who');
            if (retInp) retInp.value = pkg.sell_price_retail;
            if (whoInp) whoInp.value = pkg.sell_price_wholesale;
        }
    }
    refreshMiniTableForItem(uid);
    // Refresh drawer row margin using the rowEl already obtained above
    if (rowEl) refreshDrawerRowMargin(rowEl);
    saveDraft();
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
        // Bulk item: find by data-bulk-id, update .bulk-mini-table and .bulk-trend-banner (outside drawer)
        const bulkEl = document.querySelector(`.bulk-item[data-bulk-id="${uid}"]`);
        if (!bulkEl) return;
        const miniTbl = bulkEl.querySelector('.bulk-mini-table');
        if (miniTbl) miniTbl.innerHTML = buildMiniPricingTableHtml(item);
        const trendEl = bulkEl.querySelector('.bulk-trend-banner');
        if (trendEl) trendEl.innerHTML = buildTrendBannerHtml(item);
    } else {
        // Regular cart item: update by ID (outside drawer)
        const itemEl = document.getElementById(`item_card_${uid}`);
        if (itemEl) {
            const tblEl = itemEl.querySelector('.item-mini-table');
            if (tblEl) tblEl.innerHTML = buildMiniPricingTableHtml(item);
            const trendEl = itemEl.querySelector('.item-trend-banner');
            if (trendEl) trendEl.innerHTML = buildTrendBannerHtml(item);
        }
    }
}

/** Collect drawer data back into item.packagings before submit */
function collectDrawerDataForItem(uid) {
    let item = purchaseItems.find(i => i.id == uid);
    if (!item) item = bulkItems.find(b => b.id == uid);
    if (!item) return;
    const drawerEl = document.getElementById(`drawer_${uid}`) || document.querySelector(`.bulk-item[data-bulk-id="${uid}"] .bulk-drawer`);
    if (!drawerEl) return;
    drawerEl.querySelectorAll('.drawer-pkg-row').forEach(row => {
        const level  = parseInt(row.dataset.level);
        const pkg    = item.packagings.find(p => p.level == level);
        if (!pkg) return;
        // The following values are already handled by oninput handlers (onDrawerPkgInput & onDrawerCustomToggle)
        // Overwriting them here can cause bugs if the drawer DOM is stale (e.g. when main input changes but drawer isn't re-rendered)
        /*
        pkg.buy_price            = parseFloat(row.querySelector('.drawer-pkg-buy')?.value) || pkg.buy_price;
        pkg.sell_price_retail    = parseFloat(row.querySelector('.drawer-pkg-ret')?.value) || pkg.sell_price_retail;
        pkg.sell_price_wholesale = parseFloat(row.querySelector('.drawer-pkg-who')?.value) || pkg.sell_price_wholesale;
        pkg.buy_custom  = row.querySelector('.chk-buy-custom')?.checked  || false;
        pkg.sell_custom = row.querySelector('.chk-sell-custom')?.checked || false;
        pkg.harga_nett  = calcItemNett(pkg.buy_price, pkg.ppn_pct, pkg.diskon_mode, pkg.diskon_value);
        */

        // Collect tier prices
        const tiers = [];
        row.querySelectorAll('.drawer-tier-row').forEach(tr => {
            const minQty = parseFloat(tr.querySelector('.drawer-tier-min-qty')?.value) || 0;
            const totalH = parseFloat(tr.querySelector('.drawer-tier-total')?.value) || 0;
            const mode   = tr.querySelector('.drawer-tier-mode')?.value || 'both';
            const label  = tr.querySelector('.drawer-tier-label')?.value?.trim() || '';
            if (minQty > 0 && totalH > 0) tiers.push({ min_qty: minQty, unit_price: totalH / minQty, sale_mode: mode, label: label });
        });
        pkg.qty_prices = tiers;
    });
}

function renderCart() {
    emptyState.style.display = purchaseItems.length === 0 ? 'flex' : 'none';
    countBadge.textContent = `${purchaseItems.length} Item`;

    const massToolbar = document.getElementById('massActionToolbar');
    if (massToolbar) {
        massToolbar.style.display = purchaseItems.length > 0 ? 'flex' : 'none';
        updateMassSelect(); // refresh count & state
    }

    let html = '';
    purchaseItems.forEach(item => {
        // Ensure orig prices are stored for comparison (first time only)
        item.packagings.forEach(pkg => {
            if (pkg._orig_buy === undefined) pkg._orig_buy = parseFloat(pkg.buy_price) || 0;
            if (pkg._orig_ret === undefined) pkg._orig_ret = parseFloat(pkg.sell_price_retail) || 0;
            if (!pkg.qty_prices) pkg.qty_prices = [];
        });

        if (item.is_collapsed === undefined) item.is_collapsed = false;

        const levelOptions = item.packagings.map(p => 
            `<li><a class="dropdown-item ${p.level == item.level ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='${p.level}'; dp.querySelector('button span').textContent='${p.unit_name} (Isi ${p.base_qty})'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">${p.unit_name} (Isi ${p.base_qty})</a></li>`
        ).join('');
        const activePkgStr = (item.packagings.find(p => p.level == item.level) || item.packagings[0]) ? `${(item.packagings.find(p => p.level == item.level) || item.packagings[0]).unit_name} (Isi ${(item.packagings.find(p => p.level == item.level) || item.packagings[0]).base_qty})` : 'Pilih Kemasan';

        const selPkg    = item.packagings.find(p => p.level == item.level) || item.packagings[0];
        const selBaseQty = parseFloat(selPkg?.base_qty) || 1;
        const totalVal  = (item.total !== undefined && item.total !== null && item.total > 0) ? item.total : ((item.quantity || 1) * (item.buy_price || 0));
        const hasPkgs   = item.packagings.length > 1;
        const drawerHtml  = buildDrawerRowHtml(item, 'item');

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

        const isCollapsed = !!item.is_collapsed;

        const collapseToggleBtn = isCollapsed
            ? `<button type="button" class="btn-outline-custom" style="padding:3px 8px; font-size:10px; display:inline-flex; align-items:center; gap:4px; border-color:var(--primary); color:var(--primary);" onclick="toggleItemCollapse(${item.id})" title="Tampilkan Detail Lengkap"><i class="bi bi-chevron-down"></i> Expand</button>`
            : `<button type="button" class="btn-outline-custom" style="padding:3px 8px; font-size:10px; display:inline-flex; align-items:center; gap:4px;" onclick="toggleItemCollapse(${item.id})" title="Ringkaskan Tampilan"><i class="bi bi-chevron-up"></i> Collapse</button>`;

        let collapsedSummaryHtml = '';
        if (isCollapsed) {
            collapsedSummaryHtml = `
            <div class="item-collapsed-summary" style="margin-top:6px; padding:6px 10px; background:var(--surface-2); border-radius:var(--radius-md); border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; font-size:11px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:nowrap;">
                    <span style="color:var(--text-muted); font-size:10px;">Beli: <strong style="color:var(--text-primary); font-size:11px;">${item.quantity} ${selPkg.unit_name}</strong></span>
                    <span style="color:var(--border-color);">|</span>
                    <span style="color:var(--text-muted); font-size:10px;">Total: <strong style="color:var(--info); font-size:11px;">${formatRupiah(totalVal)}</strong></span>
                </div>
                
                <div style="display:flex; align-items:center; gap:4px; flex-wrap:nowrap; overflow-x:auto;">
                    ${item.packagings.map(pkg => {
                        const isSel = (pkg.level == item.level);
                        const buy = parseFloat(pkg.buy_price) || 0;
                        const ppn = parseFloat(pkg.ppn_pct || item.ppn_pct || 0);
                        const dm  = pkg.diskon_mode || item.diskon_mode || 'rp';
                        const dv  = parseFloat(pkg.diskon_value || item.diskon_value || 0);
                        const nett = calcItemNett(buy, ppn, dm, dv, item.quantity);
                        return `<div style="display:inline-flex; align-items:center; gap:3px; padding:2px 6px; background:${isSel ? 'rgba(230,57,70,0.12)' : 'var(--bg-input)'}; border:1px solid ${isSel ? 'var(--primary)' : 'var(--border-color)'}; border-radius:var(--radius-sm); font-size:9.5px; white-space:nowrap;">
                            <span style="font-weight:600; color:${isSel ? 'var(--primary)' : 'var(--text-primary)'};">${pkg.unit_name}</span>
                            <span style="color:var(--text-muted); font-size:8.5px;">(x${pkg.base_qty})</span>
                            <span style="color:var(--text-primary); font-weight:700;">${formatRupiah(nett)}</span>
                        </div>`;
                    }).join('')}
                </div>
            </div>`;
        }

        html += `
        <div class="item-card" id="item_card_${item.id}" data-ppn="${item.ppn_pct || 0}" style="background:var(--surface-1);border-radius:var(--radius-lg);padding:${isCollapsed ? '10px 14px' : '16px'};margin-bottom:12px;border:1px solid var(--border-color);position:relative;">
            <!-- Top Bar Actions -->
            <div style="position:absolute;top:10px;right:14px;display:flex;align-items:center;gap:8px;">
                ${collapseToggleBtn}
                <button type="button" onclick="removeItem(${item.id})" style="background:transparent; border:none; color:var(--danger); font-size:14px; cursor:pointer; padding:2px 4px;" title="Hapus Produk"><i class="bi bi-trash"></i></button>
                <input type="checkbox" class="item-select-chk" value="${item.id}" style="width:18px;height:18px;accent-color:var(--danger);cursor:pointer;margin-left:4px;" onchange="updateMassSelect()">
            </div>

            <!-- Product Name -->
            <div style="font-weight:700;font-size:var(--font-size-sm);margin-bottom:${isCollapsed ? '4px' : '12px'};padding-right:160px;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                ${item.name}
                ${hasPkgs ? `<span style="font-size:9px;background:var(--info-bg);color:var(--info);padding:2px 6px;border-radius:8px;white-space:nowrap;">${item.packagings.length} kemasan</span>` : ''}
            </div>

            ${isCollapsed ? collapsedSummaryHtml : `
                ${priceSummary ? `<div style="margin-bottom:10px;">${priceSummary}</div>` : ''}

                <!-- ── ROW 1: Kemasan + Qty ── -->
                <div style="display:flex;gap:8px;margin-bottom:10px;">
                    <div style="flex:2;">
                        <label style="font-size:10px;color:var(--text-muted);display:flex;justify-content:space-between;margin-bottom:4px;">
                            <span>Kemasan Beli</span>
                        </label>
                        <div class="dropdown" style="width:100%;">
                            <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:8px; font-size:12px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                                <span>${activePkgStr}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:12px; min-width:100%;">
                                ${levelOptions}
                            </ul>
                            <input type="hidden" value="${item.level}" onchange="changeLevel(${item.id}, this.value)">
                        </div>
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
                    <input type="number" id="main_total_${item.id}" step="any" class="form-control-dark" style="width:100%;padding:8px;font-size:13px;font-weight:600;color:var(--info);"
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
                        <label style="font-size:10px;color:var(--text-muted);display:block;margin-bottom:4px;">Diskon (Rp=Total)</label>
                        <div style="display:flex;gap:4px;">
                            <div class="discount-toggle-group" style="display:flex; border-radius:var(--radius-md) 0 0 var(--radius-md); overflow:hidden; border:1px solid var(--border-color); border-right:none; width:65px;">
                                <button type="button" class="btn-discount-mode rp-mode ${(item.diskon_mode||'rp')==='rp'?'active':''}" style="flex:1; padding:8px 0; background:${(item.diskon_mode||'rp')==='rp'?'var(--primary)':'var(--bg-input)'}; color:${(item.diskon_mode||'rp')==='rp'?'#fff':'var(--text-muted)'}; border:none; font-size:11px; font-weight:bold; cursor:pointer;" onclick="event.preventDefault(); const p=this.closest('.discount-toggle-group'); p.querySelector('.pct-mode').style.background='var(--bg-input)'; p.querySelector('.pct-mode').style.color='var(--text-muted)'; this.style.background='var(--primary)'; this.style.color='#fff'; const hidden=p.nextElementSibling; hidden.value='rp'; hidden.dispatchEvent(new Event('change'));">Rp</button>
                                <button type="button" class="btn-discount-mode pct-mode ${item.diskon_mode==='pct'?'active':''}" style="flex:1; padding:8px 0; background:${item.diskon_mode==='pct'?'var(--primary)':'var(--bg-input)'}; color:${item.diskon_mode==='pct'?'#fff':'var(--text-muted)'}; border:none; font-size:11px; font-weight:bold; cursor:pointer;" onclick="event.preventDefault(); const p=this.closest('.discount-toggle-group'); p.querySelector('.rp-mode').style.background='var(--bg-input)'; p.querySelector('.rp-mode').style.color='var(--text-muted)'; this.style.background='var(--primary)'; this.style.color='#fff'; const hidden=p.nextElementSibling; hidden.value='pct'; hidden.dispatchEvent(new Event('change'));">%</button>
                            </div>
                            <input type="hidden" class="item-diskon-mode" value="${item.diskon_mode||'rp'}" onchange="onMainInputChange(${item.id}, 'diskon_mode', this.value)">
                            <input type="number" class="form-control-dark item-diskon-value" style="flex:1;padding:8px;font-size:12px;" value="${item.diskon_value || 0}" min="0" placeholder="0"
                                   oninput="onMainInputChange(${item.id}, 'diskon_value', this.value)">
                        </div>
                    </div>
                </div>

                <!-- ── Mini Pricing Table (OUTSIDE drawer) ── -->
                <div class="item-mini-table">${buildMiniPricingTableHtml(item)}</div>
                <!-- ── Trend Banner (OUTSIDE drawer) ── -->
                <div class="item-trend-banner">${buildTrendBannerHtml(item)}</div>

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
                    <!-- Per-packaging detail editors -->
                    ${drawerHtml}
                </div>
            `}
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
        if (totalInp && item.buy_price > 0) totalInp.value = item.total;
    }

    if (field === 'total') {
        const total = parseFloat(val) || 0;
        const qty   = item.quantity || 1;
        const selPkg = item.packagings.find(p => p.level == item.level);
        if (total > 0 && qty > 0) {
            // Total Harga = qty × buy_price_per_pkg → buy_price_per_pkg = total / qty
            item.buy_price = total / qty;
            item.total     = total;
            item.is_manual_price = true;
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
    saveDraft();
}

/** Refresh drawer content if it is currently open */
function refreshOpenDrawer(uid) {
    let isBulk = false;
    let item = purchaseItems.find(i => i.id == uid);
    if (!item) {
        item = bulkItems.find(b => b.id == uid);
        isBulk = true;
    }
    if (!item) return;

    let drawer;
    if (isBulk) {
        const bulkEl = document.querySelector(`.bulk-item[data-bulk-id="${uid}"]`);
        if (bulkEl) drawer = bulkEl.querySelector('.bulk-drawer');
    } else {
        drawer = document.getElementById(`drawer_${uid}`);
    }

    if (!drawer || drawer.style.display === 'none') return;
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
    if (!currentSalesRepId && !isOtherMode) {
        showToast('Pilih Sales / Supplier terlebih dahulu');
        return;
    }
    if (purchaseItems.length === 0) {
        showToast('Daftar barang masih kosong');
        return;
    }

    // Force collect data from all open drawers before saving
    purchaseItems.forEach(item => {
        if (item.packagings && item.packagings.length > 1) {
            collectDrawerDataForItem(item.id);
        }
    });

    const btn = document.getElementById('btnSavePurchase');
    const prevText = btn.innerHTML;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
    btn.disabled = true;

    try {
        const payload = {
            supplier_id: isOtherMode ? (currentSupplierId || null) : (currentSupplierId || null),
            sales_rep_id: isOtherMode ? null : (currentSalesRepId || null),
            notes: isOtherMode ? 'Other — supplier/sales belum diketahui' : '',
            purchase_date: document.getElementById('purchaseDate').value,
            total_amount: currentSubtotal,
            grand_total: currentGrandTotal,
            invoice_photo_base64: invoicePhotoBase64,
            items: purchaseItems.map(i => {
                const selPkg = (i.packagings || []).find(p => p.level == i.level) || {};
                const itemNett = parseFloat(selPkg.harga_nett) || parseFloat(i.harga_nett) || parseFloat(i.buy_price) || 0;
                const buyGross = parseFloat(i.buy_price) || 0;
                return {
                    product_id: i.product_id,
                    level: i.level,
                    quantity: i.quantity,
                    buy_price: buyGross, 
                    sell_price_retail: parseFloat(i.sell_price_retail) || 0,
                    sell_price_wholesale: parseFloat(i.sell_price_wholesale) || 0,
                    ppn_pct: parseFloat(i.ppn_pct) || 0,
                    diskon_mode: i.diskon_mode || 'rp',
                    diskon_value: parseFloat(i.diskon_value) || 0,
                    harga_nett: itemNett,
                    packagings: (i.packagings || []).map(p => {
                        const pkgNett = (parseFloat(p.harga_nett) > 0)
                            ? parseFloat(p.harga_nett)
                            : calcItemNett(parseFloat(p.buy_price) || 0, parseFloat(p.ppn_pct) || 0, p.diskon_mode || 'rp', parseFloat(p.diskon_value) || 0, i.quantity);
                        return {
                            level: p.level,
                            buy_price: pkgNett,
                            sell_price_retail: parseFloat(p.sell_price_retail) || 0,
                            sell_price_wholesale: parseFloat(p.sell_price_wholesale) || 0,
                            ppn_pct: parseFloat(p.ppn_pct) || 0,
                            diskon_mode: p.diskon_mode || 'rp',
                            diskon_value: parseFloat(p.diskon_value) || 0,
                            harga_nett: pkgNett,
                            qty_prices: p.qty_prices || []
                        };
                    })
                };
            })
        };

        const res = await api(`${BASE_URL}api/purchases/${purchaseId}/update`, 'POST', payload);
        if (res.success) {
            showToast('Pembelian berhasil diperbarui!', 'success');
            setTimeout(() => window.location.href = BASE_URL + 'purchases', 1500);
        } else {
            showToast('❌ ' + (res.error || 'Gagal menyimpan pembelian'), 'error');
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
        const lv1 = (p.packagings || []).find(pkg => pkg.level == 1) || (p.packagings || [])[0];
        const lv1BaseQty = parseFloat(lv1?.base_qty) || 1;
        const lv1Buy = parseFloat(lv1?.buy_price) || 0;
        const lv1Ret = parseFloat(lv1?.sell_price_retail) || 0;
        const lv1Who = parseFloat(lv1?.sell_price_wholesale) || 0;

        const pkgs = (p.packagings || []).map(pkg => {
            let buyCustom = false, sellCustom = false;
            if (pkg.level != 1) {
                const ratio = (parseFloat(pkg.base_qty) || 1) / lv1BaseQty;
                const expectedRet = Math.round(lv1Ret * ratio);
                const expectedWho = Math.round(lv1Who * ratio);
                buyCustom = false;
                const diffRet = Math.abs((parseFloat(pkg.sell_price_retail) || 0) - expectedRet);
                const diffWho = Math.abs((parseFloat(pkg.sell_price_wholesale) || 0) - expectedWho);
                sellCustom = diffRet > 5 || diffWho > 5;
            }
            return {
                ...pkg,
                ppn_pct:      parseFloat(pkg.ppn_pct) || 0,
                diskon_mode:  pkg.diskon_mode || 'rp',
                diskon_value: parseFloat(pkg.diskon_value) || 0,
                harga_nett:   parseFloat(pkg.buy_price) || 0,
                buy_custom:   buyCustom,
                sell_custom:  sellCustom,
                qty_prices:   pkg.qty_prices || [],
                _orig_buy:    parseFloat(pkg.buy_price) || 0,
                _orig_ret:    parseFloat(pkg.sell_price_retail) || 0
            };
        });
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
            `<li><a class="dropdown-item ${p.level == item.level ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='${p.level}'; dp.querySelector('button span').textContent='${p.unit_name} (Isi ${p.base_qty})'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">${p.unit_name} (Isi ${p.base_qty})</a></li>`
        ).join('');
        const selPkg = item.packagings.find(p => p.level == item.level) || item.packagings[0];
        const activePkgStr = selPkg ? `${selPkg.unit_name} (Isi ${selPkg.base_qty})` : 'Pilih Kemasan';
        const hasPkgs    = item.packagings.length > 1;
        const drawerHtml  = hasPkgs ? buildDrawerRowHtml(item, 'bulk') : '';

        // Simple per-unit price summary (instead of full table)
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
                    <div class="dropdown" style="width:100%;">
                        <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:6px; font-size:11px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                            <span>${activePkgStr}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:11px; min-width:100%;">
                            ${levelOptions}
                        </ul>
                        <input type="hidden" class="bulk-pkg-select" value="${item.level}" onchange="onBulkLevelChange('${item.id}', this.value)">
                    </div>
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
                        <div class="discount-toggle-group" style="display:flex; border-radius:var(--radius-md) 0 0 var(--radius-md); overflow:hidden; border:1px solid var(--border-color); border-right:none; width:58px;">
                            <button type="button" class="btn-discount-mode rp-mode active" style="flex:1; padding:6px 0; background:var(--primary); color:#fff; border:none; font-size:10px; font-weight:bold; cursor:pointer;" onclick="event.preventDefault(); const p=this.closest('.discount-toggle-group'); p.querySelector('.pct-mode').style.background='var(--bg-input)'; p.querySelector('.pct-mode').style.color='var(--text-muted)'; this.style.background='var(--primary)'; this.style.color='#fff'; const hidden=p.nextElementSibling; hidden.value='rp'; hidden.dispatchEvent(new Event('change'));">Rp</button>
                            <button type="button" class="btn-discount-mode pct-mode" style="flex:1; padding:6px 0; background:var(--bg-input); color:var(--text-muted); border:none; font-size:10px; font-weight:bold; cursor:pointer;" onclick="event.preventDefault(); const p=this.closest('.discount-toggle-group'); p.querySelector('.rp-mode').style.background='var(--bg-input)'; p.querySelector('.rp-mode').style.color='var(--text-muted)'; this.style.background='var(--primary)'; this.style.color='#fff'; const hidden=p.nextElementSibling; hidden.value='pct'; hidden.dispatchEvent(new Event('change'));">%</button>
                        </div>
                        <input type="hidden" class="bulk-diskon-mode" value="rp" onchange="onBulkMainChange('${item.id}', 'diskon_mode', this.value)">
                        <input type="number" class="form-control-dark bulk-diskon-value" style="flex:1;padding:6px;font-size:11px;" value="0" min="0" placeholder="0"
                               oninput="onBulkMainChange('${item.id}', 'diskon_value', this.value)">
                    </div>
                </div>
            </div>

            <!-- ── Harga per unit (auto calculated) ── -->
            <div class="bulk-unit-price-info" style="margin-top:6px;font-size:10px;color:var(--text-muted);text-align:right;"></div>

            <!-- ── Mini Pricing Table (OUTSIDE drawer) ── -->
            <div class="bulk-mini-table">${buildMiniPricingTableHtml(item)}</div>
            <!-- ── Trend Banner (OUTSIDE drawer) ── -->
            <div class="bulk-trend-banner">${buildTrendBannerHtml(item)}</div>

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
                            const label  = tr.querySelector('.drawer-tier-label')?.value?.trim() || '';
                            if (minQty > 0 && totalH > 0) tiers.push({ min_qty: minQty, unit_price: totalH / minQty, sale_mode: mode, label: label });
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
                const finalNett = selPkgFinal?.harga_nett || calcItemNett(bulkItem.buy_price, ppn, dm, dv, qty);
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
                    existingItem.harga_nett             = finalNett;
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
                        harga_nett:           finalNett
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
    refreshOpenDrawer(bulkId);
}

/** Toggle bulk drawer open/close */
function toggleBulkDrawer(bulkId, btn) {
    const el     = btn.closest('.bulk-item');
    const drawer = el.querySelector('.bulk-drawer');
    if (!drawer) return;
    const isOpen = drawer.style.display !== 'none';
    drawer.style.display = isOpen ? 'none' : 'block';
    btn.innerHTML = isOpen
        ? '<i class="bi bi-tags"></i> Atur Harga Kemasan Lainnya'
        : '<i class="bi bi-chevron-up"></i> Tutup Panel Kemasan';
    btn.style.borderStyle = isOpen ? 'dashed' : 'solid';
    
    if (!isOpen) {
        refreshMiniTableForItem(bulkId);
        refreshOpenDrawer(bulkId);
    }
}

function filterBulkModal(keyword) {
    const term  = keyword.toLowerCase();
    document.querySelectorAll('.bulk-item').forEach(item => {
        const name = item.querySelector('[style*="font-weight:700"]')?.textContent?.toLowerCase() || '';
        item.style.display = name.includes(term) ? 'block' : 'none';
    });
}


</script>
