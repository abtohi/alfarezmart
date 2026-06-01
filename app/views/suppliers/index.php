<!-- Suppliers View -->
<?php
/**
 * @var array $suppliers
 * @var string $csrfToken
 */
?>
<div class="page-section">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 style="font-size:var(--font-size-lg);font-weight:700;">Supplier & Sales</h2>
        <button class="btn-primary-custom" style="padding:8px 16px;font-size:var(--font-size-xs);cursor:pointer;" onclick="showAddSupplier()">
            <i class="bi bi-plus"></i> Tambah Supplier
        </button>
    </div>

    <!-- PENCARIAN KHUSUS SUPPLIER & SALES -->
    <div style="margin-bottom: 16px; position: relative;" id="supplierSearchContainer">
        <div style="background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:0 12px; display:flex; align-items:center;">
            <i class="bi bi-search" style="color:var(--text-muted);font-size:14px;"></i>
            <input type="text" id="localSupplierSearch" placeholder="Cari Nama Supplier atau Sales di sini..." style="flex:1;border:none;background:transparent;padding:12px 10px;color:var(--text-primary);font-size:14px;outline:none;" autocomplete="off">
            <button type="button" id="btnClearLocalSearch" style="display:none;background:none;border:none;color:var(--text-muted);padding:4px;cursor:pointer;"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="localSearchResults" style="position:absolute;top:100%;left:0;right:0;background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);max-height:300px;overflow-y:auto;z-index:100;display:none;margin-top:4px;box-shadow:var(--shadow-md);"></div>
    </div>
    
    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- Supplier List -->
    <div id="supplierList">
    <?php if (empty($suppliers)): ?>
        <div class="empty-state" id="emptyState">
            <i class="bi bi-building"></i>
            <h3>Belum Ada Supplier</h3>
            <p>Tambahkan supplier pertama Anda</p>
        </div>
    <?php else: ?>
        <?php foreach ($suppliers as $s): ?>
            <div class="product-card" style="margin-bottom:12px; cursor:default; border:1px solid var(--border-color); background:var(--bg-primary); transition:all 0.3s ease; box-shadow:0 4px 15px rgba(0,0,0,0.1);" id="supplier-card-<?= $s['id'] ?>">
                <div class="product-icon" style="background:linear-gradient(135deg, var(--info-bg), rgba(0,200,255,0.15)); color:var(--info); border-radius:12px; width:48px; height:48px; font-size:1.4rem; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="bi bi-building"></i>
                </div>
                <div class="product-info" style="flex:1; min-width:0; margin-left:12px;">
                    <div class="product-name" style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div style="flex:1; min-width:0; padding-right:8px;">
                            <div style="font-weight:800; font-size:var(--font-size-md); letter-spacing:0.3px; color:var(--text-primary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
                                <?= htmlspecialchars($s['name']) ?>
                            </div>
                            <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:4px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span class="badge-custom badge-info" style="font-size:9px; padding:3px 6px; text-transform:uppercase; letter-spacing:0.5px;">
                                    <?= htmlspecialchars($s['type_name'] ?? 'Supplier') ?>
                                </span>
                                <?= $s['is_consignment'] ? '<span class="badge-custom badge-warning" style="font-size:9px; padding:3px 6px; background:rgba(255,193,7,0.15); color:#ffc107;">🏷️ Konsinyasi</span>' : '' ?>
                            </div>
                        </div>
                        <div style="display:flex; gap:2px; background:var(--surface-2); border-radius:30px; padding:2px; flex-shrink:0;">
                            <button onclick="showEditSupplier(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)" class="btn-icon" style="color:var(--text-primary); padding:6px; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><i class="bi bi-pencil-square" style="font-size:13px;"></i></button>
                            <button onclick="deleteSupplier(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name']), ENT_QUOTES, 'UTF-8') ?>')" class="btn-icon" style="color:var(--danger); padding:6px; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center;"><i class="bi bi-trash" style="font-size:13px;"></i></button>
                        </div>
                    </div>
                    <div style="margin-top:12px; display:flex; gap:8px;">
                        <button onclick="showSalesReps(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>')" style="flex:1; border:none; background:var(--primary-bg); color:var(--primary); padding:8px 0; border-radius:var(--radius-sm); font-size:11px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px; transition:transform 0.1s;"><i class="bi bi-people-fill"></i> Kelola Sales</button>
                        <button onclick="showSupplierProducts(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>')" style="flex:1; border:none; background:var(--success-bg); color:var(--success); padding:8px 0; border-radius:var(--radius-sm); font-size:11px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px; transition:transform 0.1s;"><i class="bi bi-box-seam-fill"></i> Kelola Produk</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
</div>

<script>
const csrfVal = document.getElementById('csrfToken').value;

// Supplier Types Data
const supplierTypes = [
    <?php foreach ($supplierTypes ?? [] as $st): ?>
        { value: '<?= $st['id'] ?>', label: <?= json_encode($st['name']) ?> },
    <?php endforeach; ?>
];

function getSupplierFormHTML(s = {}) {
    let options = '<option value="">Pilih Jenis...</option>';
    supplierTypes.forEach(st => {
        options += `<option value="${st.value}" ${s.type_id == st.value ? 'selected' : ''}>${st.label}</option>`;
    });

    return `
        <div class="modal-form-group">
            <label>Nama Supplier *</label>
            <input type="text" id="modalSupName" class="form-control-dark" value="${s.name || ''}" placeholder="Cth: PT Everbright" required>
        </div>
        <div class="modal-form-group">
            <label>Jenis Supplier</label>
            <select id="modalSupType" class="form-select-dark">${options}</select>
        </div>
        <div class="modal-form-group">
            <label>Alamat</label>
            <input type="text" id="modalSupAddr" class="form-control-dark" value="${s.address || ''}" placeholder="Alamat lengkap...">
        </div>
        <div class="modal-form-group">
            <label>Produk yang Dijual</label>
            <input type="text" id="modalSupProd" class="form-control-dark" value="${s.products_sold || ''}" placeholder="Cth: Indomie, Cimory...">
        </div>
        <div class="modal-form-group">
            <label>Catatan</label>
            <input type="text" id="modalSupNote" class="form-control-dark" value="${s.notes || ''}" placeholder="Catatan tambahan...">
        </div>
        <div class="modal-form-group" style="margin-top:10px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" id="modalSupConsign" value="1" ${s.is_consignment ? 'checked' : ''} style="width:18px;height:18px;accent-color:var(--primary);">
                <span style="font-size:var(--font-size-sm);">Supplier Konsinyasi (Barang Titipan)</span>
            </label>
        </div>
    `;
}

function getSupplierFormData() {
    return {
        name: document.getElementById('modalSupName').value.trim(),
        type_id: document.getElementById('modalSupType').value,
        address: document.getElementById('modalSupAddr').value.trim(),
        products_sold: document.getElementById('modalSupProd').value.trim(),
        notes: document.getElementById('modalSupNote').value.trim(),
        is_consignment: document.getElementById('modalSupConsign').checked ? 1 : 0
    };
}

async function showAddSupplier() {
    await AppModal.show({
        title: 'Tambah Supplier',
        subtitle: 'Masukkan data supplier baru',
        icon: 'bi-building-add',
        iconColor: 'var(--success-bg)',
        iconAccent: 'var(--success)',
        bodyHTML: getSupplierFormHTML(),
        submitText: 'Simpan',
        onSubmit: async () => {
            const data = getSupplierFormData();
            if (!data.name) { showToast('Nama supplier wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/suppliers`, 'POST', data);
                if (res.success) {
                    showToast('Supplier berhasil ditambahkan', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                    return true;
                }
            } catch (e) {
                showToast(e.message, 'error');
            }
            return false;
        }
    });
}

async function showEditSupplier(s) {
    await AppModal.show({
        title: 'Edit Supplier',
        subtitle: `Mengubah data ${s.name}`,
        icon: 'bi-pencil-square',
        iconColor: 'var(--warning-bg)',
        iconAccent: 'var(--warning)',
        bodyHTML: getSupplierFormHTML(s),
        submitText: 'Update',
        onSubmit: async () => {
            const data = getSupplierFormData();
            if (!data.name) { showToast('Nama supplier wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/suppliers/${s.id}`, 'POST', data); // using POST for PUT
                if (res.success) {
                    showToast('Supplier berhasil diupdate', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                    return true;
                }
            } catch (e) {
                showToast(e.message, 'error');
            }
            return false;
        }
    });
}

async function deleteSupplier(id, name) {
    await AppModal.show({
        title: 'Hapus Supplier',
        icon: 'bi-building-x',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `<p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.7;">Yakin ingin menghapus supplier <strong>"${name}"</strong>?<br><span style="color:var(--warning);font-size:11px;"><i class="bi bi-exclamation-triangle"></i> Data sales rep dan relasi produk akan ikut terhapus.</span></p>`,
        submitText: 'Ya, Hapus',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const res = await api(`${BASE_URL}api/suppliers/${id}/delete`, 'POST', { csrf_token: csrfVal });
                if (res.success) {
                    showToast('Supplier berhasil dihapus', 'success');
                    const el = document.getElementById(`supplier-card-${id}`);
                    if (el) el.remove();
                    if (document.querySelectorAll('.product-card').length === 0) {
                        setTimeout(() => window.location.reload(), 800);
                    }
                    return true;
                }
            } catch (e) {
                showToast(e.message || 'Gagal menghapus supplier', 'error');
            }
            return false;
        }
    });
}

// ==========================================
// SALES REPS MANAGEMENT
// ==========================================

async function showSalesReps(supplierId, supplierName) {
    // Load sales reps first
    let salesReps = [];
    try {
        salesReps = await api(`${BASE_URL}api/suppliers/${supplierId}/sales-reps`);
    } catch (e) {
        showToast('Gagal memuat data sales', 'error');
        return;
    }

    let listHTML = '';
    if (salesReps.length === 0) {
        listHTML = `<div style="text-align:center;padding:20px;color:var(--text-muted);font-size:12px;">Belum ada sales terdaftar</div>`;
    } else {
        salesReps.forEach(sr => {
            listHTML += `
                <div style="background:var(--surface-2);border-radius:var(--radius-sm);padding:10px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-weight:600;font-size:13px;">${sr.name}</div>
                        <div style="font-size:11px;color:var(--text-muted);">${sr.phone || '-'} | Kunjungan: ${sr.visit_day || '-'}</div>
                    </div>
                    <div style="display:flex;gap:4px;">
                        <button class="btn-icon" style="color:var(--warning);" onclick="AppModal.close(); setTimeout(() => showEditSalesRep(\'${encodeURIComponent(JSON.stringify(sr))}\', ${supplierId}, \'${supplierName.replace(/'/g, "\\'")}\'), 300)"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn-icon" style="color:var(--danger);" onclick="AppModal.close(); setTimeout(() => deleteSalesRep(${sr.id}, \'${sr.name.replace(/'/g, "\\'")}\', ${supplierId}, \'${supplierName.replace(/'/g, "\\'")}\'), 300)"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
        });
    }

    await AppModal.show({
        title: 'Kelola Sales',
        subtitle: `Supplier: ${supplierName}`,
        icon: 'bi-people',
        iconColor: 'var(--info-bg)',
        iconAccent: 'var(--info)',
        bodyHTML: `
            <div style="margin-bottom:12px;">
                <button type="button" class="btn-outline-custom" style="width:100%;font-size:12px;padding:8px;" onclick='AppModal.close(); setTimeout(() => showAddSalesRep(${supplierId}, "${supplierName}"), 300)'>
                    <i class="bi bi-plus"></i> Tambah Sales Baru
                </button>
            </div>
            <div style="max-height:300px;overflow-y:auto;">
                ${listHTML}
            </div>
        `,
        submitText: 'Tutup',
        onSubmit: async () => { return true; } // Just close
    });
}

function getSalesRepFormHTML(sr = {}) {
    return `
        <div class="modal-form-group">
            <label>Nama Sales *</label>
            <input type="text" class="form-control-dark" id="modalSalesName" value="${sr.name || ''}" placeholder="Nama lengkap..." autocomplete="off">
        </div>
        <div class="modal-form-group">
            <label>No. HP / Kontak</label>
            <input type="text" class="form-control-dark" id="modalSalesPhone" value="${sr.phone || ''}" placeholder="08xxxxxxxxxx" autocomplete="off">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <div class="modal-form-group">
                <label>Hari Kunjungan</label>
                <input type="text" class="form-control-dark" id="modalSalesVisit" value="${sr.visit_day || ''}" placeholder="Cth: Senin" autocomplete="off">
            </div>
            <div class="modal-form-group">
                <label>Hari Kirim</label>
                <input type="text" class="form-control-dark" id="modalSalesDelivery" value="${sr.delivery_day || ''}" placeholder="Cth: Rabu" autocomplete="off">
            </div>
        </div>
        <div class="modal-form-group">
            <label>Catatan</label>
            <input type="text" class="form-control-dark" id="modalSalesNotes" value="${sr.notes || ''}" placeholder="..." autocomplete="off">
        </div>
    `;
}

function getSalesRepFormData() {
    return {
        name: document.getElementById('modalSalesName').value.trim(),
        phone: document.getElementById('modalSalesPhone').value.trim(),
        visit_day: document.getElementById('modalSalesVisit').value.trim(),
        delivery_day: document.getElementById('modalSalesDelivery').value.trim(),
        notes: document.getElementById('modalSalesNotes').value.trim(),
    };
}

async function showAddSalesRep(supplierId, supplierName) {
    await AppModal.show({
        title: 'Tambah Sales',
        subtitle: `Supplier: ${supplierName}`,
        icon: 'bi-person-plus',
        iconColor: 'var(--success-bg)',
        iconAccent: 'var(--success)',
        bodyHTML: getSalesRepFormHTML(),
        submitText: 'Simpan',
        onSubmit: async () => {
            const data = getSalesRepFormData();
            data.supplier_id = supplierId;
            if (!data.name) { showToast('Nama sales wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/sales-reps`, 'POST', data);
                if (res.success) {
                    showToast('Sales berhasil ditambahkan', 'success');
                    setTimeout(() => showSalesReps(supplierId, supplierName), 300);
                    return true;
                }
            } catch (e) {
                showToast(e.message, 'error');
            }
            return false;
        }
    });
}

async function showEditSalesRep(encodedSr, supplierId, supplierName) {
    const sr = JSON.parse(decodeURIComponent(encodedSr));
    await AppModal.show({
        title: 'Edit Sales',
        subtitle: `Mengubah data ${sr.name}`,
        icon: 'bi-pencil',
        iconColor: 'var(--warning-bg)',
        iconAccent: 'var(--warning)',
        bodyHTML: getSalesRepFormHTML(sr),
        submitText: 'Update',
        onSubmit: async () => {
            const data = getSalesRepFormData();
            if (!data.name) { showToast('Nama sales wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/sales-reps/${sr.id}`, 'POST', data);
                if (res.success) {
                    showToast('Sales berhasil diupdate', 'success');
                    setTimeout(() => showSalesReps(supplierId, supplierName), 300);
                    return true;
                }
            } catch (e) {
                showToast(e.message, 'error');
            }
            return false;
        }
    });
}

async function deleteSalesRep(id, name, supplierId, supplierName) {
    await AppModal.show({
        title: 'Hapus Sales',
        icon: 'bi-person-x',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `<p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.6;">Yakin ingin menghapus sales <strong>"${name}"</strong>?</p>`,
        submitText: 'Ya, Hapus',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const res = await api(`${BASE_URL}api/sales-reps/${id}/delete`, 'POST', { csrf_token: csrfVal });
                if (res.success) {
                    showToast('Sales berhasil dihapus', 'success');
                    setTimeout(() => showSalesReps(supplierId, supplierName), 300);
                    return true;
                }
            } catch (e) {
                showToast(e.message || 'Gagal menghapus sales', 'error');
            }
            return false;
        }
    });
}

// ==========================================
// SUPPLIER PRODUCTS MANAGEMENT
// ==========================================

async function showSupplierProducts(supplierId, supplierName) {
    let products = [];
    try {
        products = await api(`${BASE_URL}api/suppliers/${supplierId}/products`);
    } catch (e) {
        showToast('Gagal memuat produk supplier', 'error');
        return;
    }

    let listHTML = '';
    if (products.length === 0) {
        listHTML = `<div style="text-align:center;padding:20px;color:var(--text-muted);font-size:12px;">Belum ada produk yang dikaitkan</div>`;
    } else {
        products.forEach(p => {
            listHTML += `
                <div style="background:var(--surface-2);border-radius:var(--radius-sm);padding:10px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-weight:600;font-size:12px;">${p.full_name || p.short_label}</div>
                        <div style="font-size:10px;color:var(--text-muted);">${p.brand_name || ''} · ${p.category_name || ''}</div>
                    </div>
                    <button class="btn-icon" style="color:var(--danger);" onclick="removeSupplierProduct(${supplierId}, ${p.id}, '${supplierName.replace(/'/g, "\\'")}')"><i class="bi bi-trash"></i></button>
                </div>
            `;
        });
    }

    const modalPromise = AppModal.show({
        title: 'Kelola Produk',
        subtitle: `Supplier: ${supplierName}`,
        icon: 'bi-box-seam',
        iconColor: 'var(--success-bg)',
        iconAccent: 'var(--success)',
        bodyHTML: `
            <div style="margin-bottom:16px;">
                <label style="font-size:11px;color:var(--text-muted);display:block;margin-bottom:4px;">Cari dan Tambah Produk</label>
                <div style="position:relative;">
                    <div style="background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:0 12px; display:flex; align-items:center;">
                        <i class="bi bi-search" style="color:var(--text-muted);font-size:12px;"></i>
                        <input type="text" id="spSearchInput" placeholder="Ketik nama produk..." style="flex:1;border:none;background:transparent;padding:10px;color:var(--text-primary);font-size:12px;outline:none;" autocomplete="off">
                        <button type="button" id="spScanBtn" style="border:none;background:var(--primary-bg);color:var(--primary);padding:6px 10px;border-radius:6px;font-size:12px;cursor:pointer;" title="Scan Barcode"><i class="bi bi-upc-scan"></i></button>
                    </div>
                    <div id="spSearchResults" style="position:absolute;top:100%;left:0;right:0;background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);max-height:200px;overflow-y:auto;z-index:100;display:none;margin-top:4px;box-shadow:var(--shadow-md);"></div>
                </div>
            </div>
            <div style="font-size:12px;font-weight:600;margin-bottom:8px;">Daftar Produk (${products.length})</div>
            <div style="max-height:250px;overflow-y:auto;">
                ${listHTML}
            </div>
        `,
        submitText: 'Tutup',
        onSubmit: async () => { return true; }
    });

    // Setup search autocomplete logic
    const searchInput = document.getElementById('spSearchInput');
    const resultsDiv = document.getElementById('spSearchResults');
    
    // Point 8: Barcode scan button
    document.getElementById('spScanBtn')?.addEventListener('click', () => {
        if (typeof BarcodeUtil !== 'undefined' && BarcodeUtil.scanBarcode) {
            BarcodeUtil.scanBarcode(searchInput, (code) => {
                searchInput.value = code;
                searchInput.dispatchEvent(new Event('input'));
            });
        } else {
            showToast('Scanner barcode tidak tersedia','info');
        }
    });

    // Load all products initially
    try {
        const allData = await api(`${BASE_URL}api/products/search?q=`);
        if (allData && allData.length > 0) {
            resultsDiv.innerHTML = allData.map(p => `
                <div class="sp-search-item" style="padding:10px;border-bottom:1px solid var(--border-color);cursor:pointer;font-size:12px;" 
                     onclick="addSupplierProduct(${supplierId}, ${p.id}, '${supplierName.replace(/'/g, "\\'")}')">
                    <div style="font-weight:600;">${p.full_name || p.short_label}</div>
                    <div style="font-size:10px;color:var(--text-muted);">${p.brand_name || ''}</div>
                </div>
            `).join('');
            resultsDiv.style.display = 'block';
        }
    } catch(e) {}

    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        const q = searchInput.value.trim();
        if (q.length < 1) {
            // Reload all products when empty
            searchTimeout = setTimeout(async () => {
                try {
                    const allData = await api(`${BASE_URL}api/products/search?q=`);
                    if (allData && allData.length > 0) {
                        resultsDiv.innerHTML = allData.map(p => `
                            <div class="sp-search-item" style="padding:10px;border-bottom:1px solid var(--border-color);cursor:pointer;font-size:12px;" 
                                 onclick="addSupplierProduct(${supplierId}, ${p.id}, '${supplierName.replace(/'/g, "\\'")}')">
                                <div style="font-weight:600;">${p.full_name || p.short_label}</div>
                                <div style="font-size:10px;color:var(--text-muted);">${p.brand_name || ''}</div>
                            </div>
                        `).join('');
                    }
                    resultsDiv.style.display = 'block';
                } catch(e) {}
            }, 200);
            return;
        }
        searchTimeout = setTimeout(async () => {
            try {
                const data = await api(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`);
                if (data && data.length > 0) {
                    resultsDiv.innerHTML = data.map(p => `
                        <div class="sp-search-item" style="padding:10px;border-bottom:1px solid var(--border-color);cursor:pointer;font-size:12px;" 
                             onclick="addSupplierProduct(${supplierId}, ${p.id}, '${supplierName.replace(/'/g, "\\'")}')">
                            <div style="font-weight:600;">${p.full_name || p.short_label}</div>
                            <div style="font-size:10px;color:var(--text-muted);">${p.brand_name || ''}</div>
                        </div>
                    `).join('');
                    resultsDiv.style.display = 'block';
                } else {
                    resultsDiv.innerHTML = '<div style="padding:10px;font-size:11px;color:var(--text-muted);text-align:center;">Tidak ditemukan</div>';
                    resultsDiv.style.display = 'block';
                }
            } catch (e) {
                resultsDiv.style.display = 'none';
            }
        }, 300);
    });

    await modalPromise;
}

async function addSupplierProduct(supplierId, productId, supplierName) {
    try {
        const res = await api(`${BASE_URL}api/suppliers/${supplierId}/products`, 'POST', { product_id: productId, csrf_token: csrfVal });
        if (res.success) {
            showToast('Produk ditambahkan ke supplier', 'success');
            AppModal.close();
            setTimeout(() => showSupplierProducts(supplierId, supplierName), 300);
        }
    } catch (e) {
        showToast(e.message, 'error');
    }
}

async function removeSupplierProduct(supplierId, productId, supplierName) {
    if (!confirm('Hapus produk ini dari daftar supplier?')) return;
    try {
        const res = await api(`${BASE_URL}api/suppliers/${supplierId}/products/${productId}/delete`, 'POST', { csrf_token: csrfVal });
        if (res.success) {
            showToast('Produk dihapus', 'success');
            AppModal.close();
            setTimeout(() => showSupplierProducts(supplierId, supplierName), 300);
        }
    } catch (e) {
        showToast(e.message, 'error');
    }
}

// ==========================================
// LOCAL SEARCH SUPPPLIER / SALES LOGIC
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const locSearchInput = document.getElementById('localSupplierSearch');
    const locSearchResults = document.getElementById('localSearchResults');
    const btnClearLocalSearch = document.getElementById('btnClearLocalSearch');
    
    if (!locSearchInput) return;
    
    let locSearchTimeout;
    
    locSearchInput.addEventListener('input', () => {
        clearTimeout(locSearchTimeout);
        const q = locSearchInput.value.trim();
        
        if (q.length > 0) {
            btnClearLocalSearch.style.display = 'block';
        } else {
            btnClearLocalSearch.style.display = 'none';
            locSearchResults.style.display = 'none';
            return;
        }
        
        locSearchTimeout = setTimeout(async () => {
            try {
                const data = await api(`${BASE_URL}api/suppliers/search?q=${encodeURIComponent(q)}`);
                if (data && data.length > 0) {
                    locSearchResults.innerHTML = data.map(s => `
                        <div style="padding:12px;border-bottom:1px solid var(--border-color);cursor:pointer;" 
                             onclick="scrollToSupplier(${s.id})">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:36px;height:36px;background:var(--info-bg);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--info);">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${s.name}</div>
                                    <div style="font-size:11px;color:var(--text-muted);">
                                        ${s.match_type === 'sales' ? 'Sales: ' + s.match_name : (s.type_name || 'Supplier')}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                    locSearchResults.style.display = 'block';
                } else {
                    locSearchResults.innerHTML = '<div style="padding:16px;text-align:center;color:var(--text-muted);font-size:13px;">Supplier/Sales tidak ditemukan</div>';
                    locSearchResults.style.display = 'block';
                }
            } catch (e) {
                locSearchResults.style.display = 'none';
            }
        }, 300);
    });
    
    btnClearLocalSearch.addEventListener('click', () => {
        locSearchInput.value = '';
        btnClearLocalSearch.style.display = 'none';
        locSearchResults.style.display = 'none';
        locSearchInput.focus();
    });
    
    // Hide results when clicking outside
    document.addEventListener('click', (e) => {
        if (!document.getElementById('supplierSearchContainer').contains(e.target)) {
            locSearchResults.style.display = 'none';
        }
    });
});

function scrollToSupplier(id) {
    const locSearchResults = document.getElementById('localSearchResults');
    if (locSearchResults) locSearchResults.style.display = 'none';
    
    const card = document.getElementById(`supplier-card-${id}`);
    if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const oldBg = card.style.background;
        card.style.background = 'var(--warning-bg)';
        card.style.transition = 'background 0.5s';
        setTimeout(() => { card.style.background = oldBg; }, 2000);
    } else {
        showToast('Supplier tidak ada di halaman ini', 'warning');
    }
}
</script>
