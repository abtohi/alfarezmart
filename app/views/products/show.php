<?php
/**
 * @var string $csrfToken
 * @var array $product
 * @var array $packagings
 * @var array $suppliers
 * @var array $salesReps
 */
?>
<!-- Product Detail View -->
<input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">
<div class="page-section">
    <!-- Back button -->
    <a href="<?= BASE_URL ?>products" style="color:var(--text-muted);text-decoration:none;font-size:var(--font-size-sm);display:inline-flex;align-items:center;gap:4px;margin-bottom:16px;">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>

    <!-- Product Header -->
    <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:20px;margin-bottom:16px;border:1px solid var(--border-color);">
        <div style="display:flex;gap:16px;align-items:flex-start;">
            <div style="width:64px;height:64px;background:var(--primary-bg);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;flex-shrink:0;position:relative;overflow:hidden;">
                <?php if (!empty($product['photo'])): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($product['photo']) ?>"
                         style="width:100%;height:100%;object-fit:contain;cursor:zoom-in;"
                         onclick="viewFullPhoto(this.src)"
                         title="Klik untuk lihat foto penuh">
                <?php else: ?>
                    <i class="bi bi-camera-fill" style="font-size:1.8rem;color:var(--primary);cursor:pointer;" onclick="choosePhotoMethod()"></i>
                <?php endif; ?>
                <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.6);color:white;font-size:9px;text-align:center;padding:2px;font-weight:600;cursor:pointer;" onclick="choosePhotoMethod()">FOTO</div>
            </div>
            <input type="file" id="productPhotoInputCamera" accept="image/*" capture="environment" style="display:none;" onchange="handleProductPhoto(event)">
            <input type="file" id="productPhotoInputGallery" accept="image/*" style="display:none;" onchange="handleProductPhoto(event)">
            <div style="flex:1;min-width:0;">
                <h2 style="font-size:var(--font-size-md);font-weight:700;margin-bottom:4px;"><?= htmlspecialchars($product['full_name']) ?></h2>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:4px;">
                    <?= htmlspecialchars($product['brand_name'] ?? '') ?> · <?= htmlspecialchars($product['category_name'] ?? '') ?>
                </div>
                <div style="font-size:var(--font-size-xs);color:var(--info);margin-top:4px;">
                    <i class="bi bi-tag"></i> Label cetak: <span id="displayShortLabel"><?= htmlspecialchars($product['short_label'] ?: '-') ?></span>
                </div>
                <?php if ($product['weight_value']): ?>
                    <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:2px;">
                        <i class="bi bi-aspect-ratio"></i> <?= htmlspecialchars($product['weight_value'] . $product['weight_unit']) ?>
                    </div>
                <?php endif; ?>
                <div style="font-size:var(--font-size-xs);color:var(--success);margin-top:6px;font-weight:600;background:var(--success-bg);padding:4px 8px;border-radius:4px;display:inline-block;">
                    <i class="bi bi-box2"></i> Stok saat ini: <span id="currentStockDisplay"><?= (int)$product['current_qty_base'] ?></span> <?= htmlspecialchars($packagings[0]['unit_name'] ?? 'Pcs') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier & Sales Info -->
    <?php if (!empty($suppliers)): ?>
    <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:20px;margin-bottom:20px;border:1px solid var(--border-color);">
        <h3 style="font-size:var(--font-size-sm);font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:8px;color:var(--text-primary);">
            <i class="bi bi-truck" style="color:var(--primary);"></i> Informasi Supplier
        </h3>
        
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div>
                <label style="font-size:12px;color:var(--text-muted);margin-bottom:4px;display:block;">Pilih Supplier</label>
                <select id="supplierSelect" class="form-control" style="border-radius:var(--radius-md);background:var(--surface-2);border-color:var(--border-color);" onchange="updateSalesRepOptions()">
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="salesRepContainer" style="display:none;background:var(--surface-2);padding:12px;border-radius:var(--radius-md);border:1px dashed var(--border-color);">
                <label style="font-size:12px;color:var(--text-muted);margin-bottom:8px;display:block;">Sales Representative</label>
                
                <!-- If multiple sales reps -->
                <div id="multipleSalesReps" style="display:none;gap:8px;flex-direction:column;">
                    <select id="salesRepSelect" class="form-control" style="border-radius:var(--radius-md);border-color:var(--border-color);" onchange="updateWhatsAppLink()">
                        <!-- options injected via JS -->
                    </select>
                </div>
                
                <!-- Single sales rep -->
                <div id="singleSalesRep" style="display:none;align-items:center;justify-content:space-between;background:var(--surface-1);padding:10px 12px;border-radius:var(--radius-sm);box-shadow:var(--shadow-sm);">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;background:var(--primary-bg);color:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <div id="singleSalesName" style="font-weight:600;font-size:var(--font-size-sm);color:var(--text-primary);">Nama Sales</div>
                            <div id="singleSalesPhone" style="font-size:11px;color:var(--text-muted);">08xx</div>
                        </div>
                    </div>
                </div>

                <a id="waContactBtn" href="#" target="_blank" style="display:flex;align-items:center;justify-content:center;gap:8px;background:#25D366;color:white;padding:10px;border-radius:var(--radius-md);text-decoration:none;font-weight:600;font-size:14px;margin-top:12px;transition:all 0.2s;box-shadow:0 4px 6px rgba(37,211,102,0.2);">
                    <i class="bi bi-whatsapp" style="font-size:1.1rem;"></i> Hubungi Sales (WhatsApp)
                </a>
                
                <div id="noSalesMsg" style="display:none;font-size:12px;color:var(--text-muted);text-align:center;padding:10px 0;">
                    <i class="bi bi-info-circle"></i> Tidak ada sales aktif untuk supplier ini.
                </div>
            </div>
        </div>
    </div>

    <script>
        const allSalesReps = <?= json_encode($salesReps) ?>;
        
        function updateSalesRepOptions() {
            const supplierId = document.getElementById('supplierSelect').value;
            const container = document.getElementById('salesRepContainer');
            const multiContainer = document.getElementById('multipleSalesReps');
            const singleContainer = document.getElementById('singleSalesRep');
            const waBtn = document.getElementById('waContactBtn');
            const noMsg = document.getElementById('noSalesMsg');
            const select = document.getElementById('salesRepSelect');
            
            if (!supplierId) {
                container.style.display = 'none';
                return;
            }
            
            container.style.display = 'block';
            
            const reps = allSalesReps.filter(sr => sr.supplier_id == supplierId);
            
            if (reps.length === 0) {
                multiContainer.style.display = 'none';
                singleContainer.style.display = 'none';
                waBtn.style.display = 'none';
                noMsg.style.display = 'block';
            } else if (reps.length === 1) {
                multiContainer.style.display = 'none';
                noMsg.style.display = 'none';
                waBtn.style.display = 'flex';
                
                singleContainer.style.display = 'flex';
                document.getElementById('singleSalesName').textContent = reps[0].name;
                document.getElementById('singleSalesPhone').textContent = reps[0].phone || '-';
                
                updateWhatsAppUrl(reps[0]);
            } else {
                singleContainer.style.display = 'none';
                noMsg.style.display = 'none';
                waBtn.style.display = 'flex';
                multiContainer.style.display = 'flex';
                
                select.innerHTML = '';
                reps.forEach((rep, index) => {
                    const opt = document.createElement('option');
                    opt.value = index; // use array index for easy access
                    opt.textContent = rep.name + (rep.phone ? ' (' + rep.phone + ')' : '');
                    select.appendChild(opt);
                });
                
                // Select first by default
                updateWhatsAppUrl(reps[0]);
            }
        }
        
        function updateWhatsAppLink() {
            const supplierId = document.getElementById('supplierSelect').value;
            const reps = allSalesReps.filter(sr => sr.supplier_id == supplierId);
            const select = document.getElementById('salesRepSelect');
            if (select.value !== "" && reps[select.value]) {
                updateWhatsAppUrl(reps[select.value]);
            }
        }
        
        function updateWhatsAppUrl(rep) {
            const waBtn = document.getElementById('waContactBtn');
            if (rep && rep.phone) {
                // Clean phone number (replace starting 0 with 62)
                let phone = rep.phone.replace(/\D/g, '');
                if (phone.startsWith('0')) {
                    phone = '62' + phone.substring(1);
                }
                const productName = <?= json_encode($product['full_name']) ?>;
                const text = encodeURIComponent(`Halo, saya mau order ${productName}`);
                waBtn.href = `https://wa.me/${phone}?text=${text}`;
                waBtn.style.opacity = '1';
                waBtn.style.pointerEvents = 'auto';
                waBtn.innerHTML = '<i class="bi bi-whatsapp" style="font-size:1.1rem;"></i> Hubungi ' + rep.name;
            } else {
                waBtn.href = '#';
                waBtn.style.opacity = '0.5';
                waBtn.style.pointerEvents = 'none';
                waBtn.innerHTML = '<i class="bi bi-whatsapp" style="font-size:1.1rem;"></i> Nomor WA tidak tersedia';
            }
        }
    </script>
    <?php endif; ?>

    <!-- Packaging & Prices -->
    <div class="section-title" style="margin-bottom:12px;">Level Kemasan & Harga</div>
    
    <?php if (empty($packagings)): ?>
        <div class="empty-state" style="padding:20px;">
            <p>Belum ada data kemasan/harga</p>
        </div>
    <?php else: ?>
        <?php foreach ($packagings as $i => $p): ?>
        <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--border-color);position:relative;overflow:hidden;">
            <!-- Badge Level -->
            <div style="position:absolute;top:0;right:0;background:<?= $i === 0 ? 'var(--primary)' : 'var(--surface-2)' ?>;color:<?= $i === 0 ? 'white' : 'var(--text-muted)' ?>;font-size:10px;padding:4px 10px;border-bottom-left-radius:var(--radius-md);font-weight:600;">
                Level <?= $p['level'] ?>
            </div>

            <!-- Header: Satuan & Isi -->
            <div style="margin-bottom:12px;border-bottom:1px solid var(--border-color);padding-bottom:12px;">
                <div style="font-weight:700;font-size:var(--font-size-md);color:var(--text-primary);">
                    1 <?= htmlspecialchars($p['unit_name']) ?>
                </div>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:2px;">
                    <?php if ($p['level'] == 1): ?>
                        Satuan Dasar (Terkecil)
                    <?php else: ?>
                        Isi: <?= $p['contained_qty'] ?> <?= htmlspecialchars($packagings[$i-1]['unit_name']) ?> <span style="opacity:0.5;">(Total: <?= $p['base_qty'] ?> <?= htmlspecialchars($packagings[0]['unit_name']) ?>)</span>
                    <?php endif; ?>
                </div>
                <?php if ($p['barcode']): ?>
                    <div style="margin-top:8px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <div style="font-size:var(--font-size-xs);color:var(--info);font-family:monospace;background:rgba(255,255,255,0.05);padding:4px 8px;border-radius:4px;">
                            <i class="bi bi-upc"></i> <?= htmlspecialchars($p['barcode']) ?>
                        </div>
                        <button type="button" class="btn-outline-custom" style="font-size:10px;padding:4px 10px;" onclick="printBarcodeShow('<?= htmlspecialchars(addslashes($p['barcode'])) ?>', '<?= htmlspecialchars(addslashes($product['short_label'] ?? $product['full_name'])) ?>', '<?= htmlspecialchars(addslashes($p['unit_name'])) ?>')">
                            <i class="bi bi-printer"></i> Cetak
                        </button>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn-outline-custom" style="font-size:10px;padding:4px 10px;margin-top:8px;" onclick="generateAndPrintBarcodeShow(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(addslashes($product['short_label'] ?? $product['full_name'])) ?>', '<?= htmlspecialchars(addslashes($p['unit_name'])) ?>', <?= (float)$p['buy_price'] ?>, <?= (float)$p['sell_price_retail'] ?>, <?= (float)$p['sell_price_wholesale'] ?>)">
                        <i class="bi bi-magic"></i> Generate & Cetak Barcode
                    </button>
                <?php endif; ?>
            </div>

            <!-- Harga Modal -->
            <div style="font-size:var(--font-size-sm);margin-bottom:8px;display:flex;justify-content:space-between;">
                <span style="color:var(--text-muted);">Harga Modal</span>
                <span style="font-weight:600;"><?= Helper::rupiah($p['buy_price']) ?></span>
            </div>

            <!-- Harga Jual -->
            <div style="display:flex;gap:12px;margin-top:12px;">
                <div style="flex:1;padding:10px;background:var(--success-bg);border-radius:var(--radius-sm);text-align:center;">
                    <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:2px;">Ecer/Retail</div>
                    <div style="font-weight:700;color:var(--success);font-size:var(--font-size-sm);"><?= Helper::rupiah($p['sell_price_retail']) ?></div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:2px;text-shadow:0 1px 1px rgba(0,0,0,0.1);">
                        Modal: <?= Helper::rupiah($p['buy_price']) ?>
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;text-shadow:0 1px 1px rgba(0,0,0,0.1);">
                        Selisih: <?= Helper::rupiah($p['sell_price_retail'] - $p['buy_price']) ?> (<?= round($p['margin_retail'] * 100, 1) ?>%)
                    </div>
                </div>
                <?php if ($p['sell_price_wholesale'] > 0): ?>
                <div style="flex:1;padding:10px;background:var(--surface-2);border-radius:var(--radius-sm);text-align:center;">
                    <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:2px;">Grosir</div>
                    <div style="font-weight:700;color:var(--text-primary);font-size:var(--font-size-md);"><?= Helper::rupiah($p['sell_price_wholesale']) ?></div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:2px;text-shadow:0 1px 1px rgba(0,0,0,0.1);">
                        Modal: <?= Helper::rupiah($p['buy_price']) ?>
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:1px;text-shadow:0 1px 1px rgba(0,0,0,0.1);">
                        Selisih: <?= Helper::rupiah($p['sell_price_wholesale'] - $p['buy_price']) ?> (<?= round($p['margin_wholesale'] * 100, 1) ?>%)
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($p['qty_prices'])): ?>
            <div style="margin-top:10px;padding:10px;background:var(--info-bg);border-radius:var(--radius-sm);border:1px dashed rgba(59,130,246,0.3);">
                <div style="font-size:10px;font-weight:600;color:var(--info);margin-bottom:6px;"><i class="bi bi-tags"></i> Harga spesial per kuantitas</div>
                <?php foreach ($p['qty_prices'] as $tier): ?>
                <?php
                    $modeLabel = match ($tier['sale_mode'] ?? 'both') {
                        'retail' => 'Ecer',
                        'wholesale' => 'Grosir',
                        default => 'Ecer & Grosir',
                    };
                ?>
                <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;">
                    ≥ <?= (float)$tier['min_qty'] ?> <?= htmlspecialchars($p['unit_name']) ?>
                    → <strong><?= Helper::rupiah($tier['unit_price']) ?></strong>/<?= htmlspecialchars($p['unit_name']) ?>
                    <span style="color:var(--text-muted);">(<?= $modeLabel ?>)</span>
                    <?php if (!empty($tier['label'])): ?>
                    <span style="display:block;font-size:10px;color:var(--text-muted);"><?= htmlspecialchars($tier['label']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Label Cetak (Thermal) -->
    <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-top:16px;border:1px solid var(--border-color);">
        <div class="section-title" style="margin-bottom:12px;">
            <i class="bi bi-receipt" style="color:var(--warning);"></i> Label Cetak Struk
        </div>
        <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:12px;">
            Label singkat untuk printer thermal (max 35 karakter). Dipakai di struk kasir & invoice.
        </p>
        <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">Label cetak produk ini</label>
        <input type="text" id="inputShortLabel" class="form-control-dark" maxlength="35" value="<?= htmlspecialchars($product['short_label'] ?? '') ?>" placeholder="Cth: Indomie Goreng 250ml" style="width:100%;margin-bottom:4px;">
        <small style="font-size:10px;color:var(--text-muted);"><span id="labelCharCount">0</span>/35 karakter</small>
        <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
            <button type="button" class="btn-primary-custom" style="flex:1;min-width:120px;padding:10px;" onclick="saveProductLabel(<?= (int)$product['id'] ?>)">
                <i class="bi bi-check"></i> Simpan Label
            </button>
            <button type="button" class="btn-outline-custom" style="flex:1;min-width:120px;padding:10px;" onclick="openDistributeLabelModal(<?= (int)$product['id'] ?>)">
                <i class="bi bi-share"></i> Terapkan ke Varian
            </button>
        </div>
    </div>


    <!-- Informasi Supplier (Inline Edit) -->
    <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-top:16px;border:1px solid var(--border-color);">
        <div class="section-title" style="margin-bottom:4px;">
            <i class="bi bi-building" style="color:var(--info);"></i> Informasi Supplier
        </div>
        <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:14px;">
            Kode &amp; nama di invoice supplier — membantu AI Scan Invoice mengenali produk lebih akurat.
        </p>

        <!-- Kode Barang Supplier -->
        <div style="margin-bottom:14px;">
            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:4px;">
                <i class="bi bi-hash"></i> Kode Barang Supplier
            </label>
            <input type="text"
                   id="inputSupplierCode"
                   class="form-control-dark"
                   placeholder="Cth: CMY-125, INM-001"
                   value="<?= htmlspecialchars($product['supplier_product_code'] ?? '') ?>"
                   style="width:100%;">
        </div>

        <!-- Nama Barang di Invoice Supplier (Multi-nama) -->
        <div>
            <label style="font-size:var(--font-size-xs);color:var(--text-muted);display:block;margin-bottom:6px;">
                <i class="bi bi-card-text"></i> Nama Barang di Invoice Supplier
                <span style="font-size:10px;color:var(--info);margin-left:4px;">(bisa multi-nama)</span>
            </label>
            <div id="showInvoiceNameList" style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px;"></div>
            <button type="button"
                    onclick="showAddInvoiceName()"
                    style="width:100%;border:1px dashed var(--border-color);background:transparent;color:var(--info);padding:6px;border-radius:var(--radius-sm);font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;margin-bottom:4px;">
                <i class="bi bi-plus-circle"></i> Tambah Nama Invoice
            </button>
            <div style="font-size:10px;color:var(--text-muted);margin-bottom:12px;">
                <i class="bi bi-info-circle"></i> Tambahkan semua variasi nama produk di invoice supplier.
            </div>
        </div>

        <button type="button"
                id="btnSaveSupplierInfo"
                class="btn-primary-custom"
                style="width:100%;padding:10px;background:var(--info);"
                onclick="saveSupplierInfo(<?= (int)$product['id'] ?>)">
            <i class="bi bi-check2-circle"></i> Simpan Info Supplier
        </button>
    </div>

    <!-- Actions -->
    <?php $isStaffShow = (($_SESSION['user_level'] ?? '') === 'staff'); ?>
    <div style="display:flex;gap:8px;margin-top:24px;flex-direction:column;">
        <button class="btn-primary-custom" style="width:100%;padding:12px;background:var(--success);color:white;border:none;" onclick="openUpdateStockModal()">
            <i class="bi bi-box-seam"></i> Update Stok Fisik (Opname)
        </button>
        <?php if (!$isStaffShow): ?>
        <div style="display:flex;gap:8px;">
            <a href="<?= BASE_URL ?>products/<?= $product['id'] ?>/edit" class="btn-outline-custom" style="flex:1;text-align:center;text-decoration:none;padding:12px;">
                <i class="bi bi-pencil"></i> Edit Produk
            </a>
        <button class="btn-outline-custom" style="flex:1;color:var(--danger);border-color:var(--danger);" onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['short_label'] ?? $product['full_name'])) ?>')">
            <i class="bi bi-trash"></i> Hapus
        </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function printBarcodeShow(code, title, unit) {
    BarcodeUtil.print({ code, title, subtitle: unit ? `1 ${unit}` : '' });
}

// ===== PHOTO UPLOAD & AI BG REMOVAL =====
let imglyLoaded = false;
async function loadImgly() {
    if (imglyLoaded || window.imglyRemoveBackground) return;
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = "https://cdn.jsdelivr.net/npm/@imgly/background-removal@1.4.3/dist/imgly-background-removal.browser.min.js";
        script.onload = () => { imglyLoaded = true; resolve(); };
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function compressImage(file, maxSize) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                if (width > height) {
                    if (width > maxSize) { height *= maxSize / width; width = maxSize; }
                } else {
                    if (height > maxSize) { width *= maxSize / height; height = maxSize; }
                }
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                resolve(canvas.toDataURL('image/webp', 0.8));
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function dataURLtoBlob(dataurl) {
    let arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
    while(n--){ u8arr[n] = bstr.charCodeAt(n); }
    return new Blob([u8arr], {type:mime});
}

async function handleProductPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    showToast('Memproses foto... (Kompresi & AI)', 'info');

    try {
        // 1. Compress Image
        const compressedDataUrl = await compressImage(file, 800);
        let finalBlob = dataURLtoBlob(compressedDataUrl);

        // 2. Background Removal using AI
        try {
            await loadImgly();
            showToast('AI: Sedang menghapus latar belakang... (Mungkin memakan waktu)', 'info', 5000);
            const bgRemovedBlob = await imglyRemoveBackground(finalBlob);
            finalBlob = bgRemovedBlob;
        } catch (e) {
            console.error("AI BG Removal failed:", e);
            showToast('AI Gagal/Dilewati. Menyimpan foto asli...', 'warning');
        }

        // 3. Upload to server
        const reader = new FileReader();
        reader.onload = async (e) => {
            const base64 = e.target.result;
            try {
                const csrfToken = document.getElementById('csrfToken').value;
                const res = await api(`${BASE_URL}api/products/${productIdForLabel}/photo`, 'POST', {
                    csrf_token: csrfToken,
                    photo_base64: base64
                });
                if (res.success) {
                    showToast('Foto berhasil diperbarui', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                }
            } catch (err) {
                showToast(err.message || 'Gagal upload', 'error');
            }
        };
        reader.readAsDataURL(finalBlob);
    } catch (err) {
        showToast('Terjadi kesalahan saat memproses gambar', 'error');
    }
}

function choosePhotoMethod() {
    AppModal.show({
        title: 'Ambil Foto Produk',
        hideFooter: true,
        bodyHTML: `
            <div style="display:flex;flex-direction:column;gap:12px;">
                <button type="button" class="btn-primary-custom" style="padding:16px;font-size:16px;background:var(--primary);color:white;border:none;border-radius:var(--radius-md);cursor:pointer;" onclick="AppModal.close(); document.getElementById('productPhotoInputCamera').click()">
                    <i class="bi bi-camera"></i> Gunakan Kamera
                </button>
                <button type="button" class="btn-outline-custom" style="padding:16px;font-size:16px;border-radius:var(--radius-md);cursor:pointer;" onclick="AppModal.close(); document.getElementById('productPhotoInputGallery').click()">
                    <i class="bi bi-image"></i> Pilih dari Galeri
                </button>
            </div>
        `
    });
}

async function generateAndPrintBarcodeShow(packagingId, title, unit, buyPrice, retailPrice, wholesalePrice) {
    try {
        const code = await BarcodeUtil.generate();
        const csrfToken = document.getElementById('csrfToken')?.value || '';
        const res = await api(`${BASE_URL}api/products/packaging/${packagingId}`, 'POST', {
            csrf_token: csrfToken,
            barcode: code,
            buy_price: buyPrice,
            sell_price_retail: retailPrice,
            sell_price_wholesale: wholesalePrice,
        });
        if (res.success) {
            BarcodeUtil.print({ code, title, subtitle: unit ? `1 ${unit}` : '' });
            showToast('Barcode digenerate. Refresh halaman untuk melihat perubahan.', 'success');
        }
    } catch (e) {
        showToast(e.message || 'Gagal generate barcode', 'error');
    }
}

const productIdForLabel = <?= (int)$product['id'] ?>;

function updateLabelCharCount() {
    const inp = document.getElementById('inputShortLabel');
    const el = document.getElementById('labelCharCount');
    if (inp && el) el.textContent = inp.value.length;
}

document.getElementById('inputShortLabel')?.addEventListener('input', updateLabelCharCount);
updateLabelCharCount();

async function saveProductLabel(id) {
    const shortLabel = document.getElementById('inputShortLabel')?.value?.trim();
    if (!shortLabel) {
        showToast('Label tidak boleh kosong', 'warning');
        return;
    }
    try {
        const csrfToken = document.getElementById('csrfToken')?.value || '';
        const res = await api(`${BASE_URL}api/products/${id}/label`, 'POST', {
            csrf_token: csrfToken,
            short_label: shortLabel,
            invoice_name: shortLabel,
        });
        if (res.success) {
            document.getElementById('displayShortLabel').textContent = res.short_label;
            showToast(res.message || 'Label disimpan', 'success');
        }
    } catch (e) { /* toast from api */ }
}

async function openDistributeLabelModal(id) {
    const labelBase = document.getElementById('inputShortLabel')?.value?.trim();
    if (!labelBase) {
        showToast('Isi label dasar terlebih dahulu', 'warning');
        return;
    }

    let siblingsHtml = '<p style="color:var(--text-muted);font-size:12px;">Memuat daftar varian...</p>';

    const loadVariants = async () => {
        try {
            const data = await api(`${BASE_URL}api/products/${id}/label-variants`);
            const list = document.getElementById('variantLabelList');
            if (!list) return;
            if (!data.siblings || data.siblings.length <= 1) {
                list.innerHTML = '<p style="font-size:12px;color:var(--warning);">Tidak ada produk varian lain dengan jenis yang sama.</p>';
                return;
            }
            list.innerHTML = data.siblings.map(s => {
                let preview = labelBase;
                if (s.variant) preview += ' ' + s.variant;
                if (s.weight_value && s.weight_unit) preview += ' ' + s.weight_value + s.weight_unit;
                if (preview.length > 35) preview = preview.substring(0, 32) + '...';
                const isCurrent = s.id == id;
                return `<div style="padding:8px 10px;background:var(--surface-2);border-radius:6px;margin-bottom:6px;font-size:12px;${isCurrent ? 'border-left:3px solid var(--primary);' : ''}">
                    <strong>${(s.variant || s.full_name).replace(/</g, '&lt;')}</strong>
                    <div style="color:var(--info);font-size:11px;margin-top:2px;">→ ${preview.replace(/</g, '&lt;')}</div>
                </div>`;
            }).join('');
        } catch (e) {
            const list = document.getElementById('variantLabelList');
            if (list) list.innerHTML = '<p style="color:var(--danger);font-size:12px;">Gagal memuat daftar varian</p>';
        }
    };

    AppModal.show({
        title: 'Terapkan Label ke Varian',
        subtitle: 'Produk dengan brand & jenis produk yang sama',
        icon: 'bi-share',
        iconColor: 'var(--info-bg)',
        iconAccent: 'var(--info)',
        bodyHTML: `
            <p style="font-size:var(--font-size-sm);color:var(--text-secondary);margin-bottom:12px;">
                Label dasar: <strong>${labelBase.replace(/</g, '&lt;')}</strong><br>
                <span style="font-size:11px;color:var(--text-muted);">Setiap varian akan mendapat: [dasar] + nama varian + berat</span>
            </p>
            <div id="variantLabelList">${siblingsHtml}</div>
        `,
        submitText: 'Terapkan ke Semua Varian',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const csrfToken = document.getElementById('csrfToken')?.value || '';
                const res = await api(`${BASE_URL}api/products/${id}/label/distribute`, 'POST', {
                    csrf_token: csrfToken,
                    label_base: labelBase,
                });
                if (res.success) {
                    showToast(res.message, 'success');
                    return true;
                }
            } catch (e) { /* handled */ }
            return false;
        },
    });
    setTimeout(loadVariants, 100);
}

async function deleteProduct(id, name) {
    const confirmed = await AppModal.show({
        title: 'Hapus Produk',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `<p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.6;">Yakin ingin menghapus produk <strong>${name}</strong>?<br>Data stok dan kemasan terkait juga akan dihapus.</p>`,
        submitText: 'Ya, Hapus',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const csrfToken = document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '';
                const res = await api(`<?= BASE_URL ?>api/products/${id}/delete`, 'POST', { csrf_token: csrfToken });
                if (res.success) {
                    showToast(res.message || 'Produk berhasil dihapus', 'success');
                    setTimeout(() => window.location.href = '<?= BASE_URL ?>products', 1000);
                    return true;
                }
            } catch(e) {
                // Error already shown by api()
            }
            return false;
        }
    });
}

function openUpdateStockModal() {
    const packagings = <?= json_encode($packagings) ?>;
    if (!packagings || packagings.length === 0) {
        showToast('Data kemasan tidak tersedia', 'warning');
        return;
    }

    let inputsHtml = packagings.reverse().map(p => `
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
            <div style="flex:1;">
                <label style="font-size:12px;font-weight:600;color:var(--text-primary);">${p.unit_name}</label>
                <div style="font-size:10px;color:var(--text-muted);">Isi: ${p.base_qty} ${packagings[packagings.length-1].unit_name}</div>
            </div>
            <input type="number" id="stock_qty_${p.level}" class="form-control-dark" style="width:100px;text-align:center;font-size:14px;" value="0" min="0" oninput="calculateTotalStockPreview()">
        </div>
    `).join('');

    AppModal.show({
        title: 'Update Stok Fisik',
        subtitle: 'Masukkan jumlah barang sesuai kemasan',
        icon: 'bi-box-seam',
        iconColor: 'var(--success-bg)',
        iconAccent: 'var(--success)',
        bodyHTML: `
            <div style="margin-bottom:16px;">
                ${inputsHtml}
            </div>
            <div style="background:var(--surface-2);padding:12px;border-radius:var(--radius-md);display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:12px;color:var(--text-muted);">Total Satuan Terkecil:</span>
                <span id="previewTotalStock" style="font-size:16px;font-weight:700;color:var(--success);">0</span>
            </div>
            <div style="font-size:10px;color:var(--warning);margin-top:8px;">* Stok lama akan ditimpa dengan total ini (Mode Opname).</div>
        `,
        submitText: 'Simpan Stok',
        onSubmit: async () => {
            let totalBaseQty = 0;
            packagings.forEach(p => {
                const el = document.getElementById(`stock_qty_${p.level}`);
                if (el) {
                    const qty = parseFloat(el.value) || 0;
                    totalBaseQty += qty * parseFloat(p.base_qty);
                }
            });

            try {
                const csrfToken = document.getElementById('csrfToken').value;
                const res = await api('<?= BASE_URL ?>api/products/<?= $product['id'] ?>/stock', 'POST', {
                    csrf_token: csrfToken,
                    total_qty: totalBaseQty,
                    notes: 'Stock Opname (Manual Update)'
                });
                
                if (res.success) {
                    document.getElementById('currentStockDisplay').textContent = totalBaseQty;
                    showToast('Stok berhasil diupdate', 'success');
                    return true;
                }
            } catch (e) {
                // error handled by api()
            }
            return false;
        }
    });
}

function calculateTotalStockPreview() {
    const packagings = <?= json_encode($packagings) ?>;
    let total = 0;
    packagings.forEach(p => {
        const el = document.getElementById(`stock_qty_${p.level}`);
        if (el) {
            total += (parseFloat(el.value) || 0) * parseFloat(p.base_qty);
        }
    });
    document.getElementById('previewTotalStock').textContent = total;
}

// ===== SUPPLIER INFO (Inline on Show Page) =====

function showInitInvoiceNameList(namesStr) {
    const list = document.getElementById('showInvoiceNameList');
    if (!list) return;
    list.innerHTML = '';
    const names = (namesStr || '').split(/[;\n]/).map(n => n.trim()).filter(n => n);
    names.forEach(n => showAddInvoiceNameItem(n));
}

function showAddInvoiceName(val) {
    showAddInvoiceNameItem(val || '');
    // Focus the newly added input
    const list = document.getElementById('showInvoiceNameList');
    if (list) {
        const inputs = list.querySelectorAll('.show-invoice-name-item');
        if (inputs.length > 0) inputs[inputs.length - 1].focus();
    }
}

function showAddInvoiceNameItem(val) {
    const list = document.getElementById('showInvoiceNameList');
    if (!list) return;
    const div = document.createElement('div');
    div.style.display = 'flex';
    div.style.gap = '6px';
    div.innerHTML = `
        <input type="text"
               class="form-control-dark show-invoice-name-item"
               placeholder="Cth: CIMORY UHT PORORO"
               style="flex:1;"
               value="${val ? val.replace(/"/g, '&quot;') : ''}">
        <button type="button"
                onclick="this.parentElement.remove()"
                style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:0 12px;cursor:pointer;">
            <i class="bi bi-x-lg"></i>
        </button>
    `;
    list.appendChild(div);
}

function showCollectInvoiceNames() {
    const inputs = document.querySelectorAll('.show-invoice-name-item');
    const names = Array.from(inputs).map(inp => inp.value.trim()).filter(v => v);
    return names.join(';');
}

async function saveSupplierInfo(id) {
    const btn = document.getElementById('btnSaveSupplierInfo');
    const prevText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    btn.disabled = true;

    const supplierCode    = document.getElementById('inputSupplierCode')?.value?.trim() || '';
    const supplierInvName = showCollectInvoiceNames();

    // We still need short_label for the label endpoint (it's required by updatePrintLabel)
    const shortLabel = document.getElementById('inputShortLabel')?.value?.trim()
                     || <?= json_encode($product['short_label'] ?? $product['full_name']) ?>;

    try {
        const csrfToken = document.getElementById('csrfToken')?.value || '';
        const res = await api(`${BASE_URL}api/products/${id}/label`, 'POST', {
            csrf_token:             csrfToken,
            short_label:            shortLabel,
            invoice_name:           shortLabel,
            supplier_product_code:  supplierCode,
            supplier_invoice_name:  supplierInvName,
        });
        if (res.success) {
            showToast(res.message || 'Info supplier disimpan', 'success');
        }
    } catch (e) {
        // api() already shows toast on error
    } finally {
        btn.innerHTML = prevText;
        btn.disabled = false;
    }
}

// Initialize supplier invoice name list on page load
(function() {
    const initialNames = <?= json_encode($product['supplier_invoice_name'] ?? '') ?>;
    showInitInvoiceNameList(initialNames);
})();
</script>

