<!-- Suppliers View -->
<?php
/**
 * @var array $suppliers
 * @var string $csrfToken
 */
$avatarColors = [
    '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', 
    '#F06292', '#BA68C8', '#4DB6AC', '#7986CB', '#81C784',
    '#A1887F', '#E06666', '#93C47D', '#8E7CC3', '#F6B26B'
];
function getAvatarColor($name, $colors) {
    $hash = abs(crc32($name));
    return $colors[$hash % count($colors)];
}
function getInitial($name) {
    $clean = preg_replace('/[^a-zA-Z0-9]/', '', $name);
    return strtoupper(substr($clean, 0, 1) ?: '?');
}
?>
<style>
/* Modern Supplier Card */
.supplier-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}
.supplier-card-modern {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    position: relative;
    overflow: hidden;
}
.supplier-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: var(--primary);
}
.supplier-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
}
.supplier-avatar {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.supplier-info {
    flex: 1;
    min-width: 0;
    padding-right: 40px; /* Space for absolute actions */
}
.supplier-name {
    font-weight: 800;
    font-size: var(--font-size-md);
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 4px;
    letter-spacing: 0.3px;
}
.supplier-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.supplier-actions-top {
    position: absolute;
    top: 16px;
    right: 16px;
    display: flex;
    gap: 4px;
    opacity: 0.2;
    transition: opacity 0.2s;
}
.supplier-card-modern:hover .supplier-actions-top {
    opacity: 1;
}
.supplier-actions-top button {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}
.supplier-actions-top button:hover {
    background: var(--surface-2);
    transform: scale(1.1);
}
.supplier-actions-bottom {
    display: flex;
    gap: 8px;
    margin-top: auto;
}
.btn-modern-action {
    flex: 1;
    border: 1px solid var(--border-color);
    background: transparent;
    color: var(--text-secondary);
    padding: 10px 0;
    border-radius: var(--radius-sm);
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s;
}
.btn-modern-action.sales {
    color: var(--info);
    border-color: rgba(0, 200, 255, 0.2);
    background: rgba(0, 200, 255, 0.05);
}
.btn-modern-action.sales:hover {
    background: var(--info-bg);
}
.btn-modern-action.products {
    color: var(--success);
    border-color: rgba(40, 167, 69, 0.2);
    background: rgba(40, 167, 69, 0.05);
}
.btn-modern-action.products:hover {
    background: var(--success-bg);
}
#searchBoxWrapper:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-bg);
}
</style>

<div class="page-section">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="font-size:var(--font-size-lg);font-weight:800;letter-spacing:-0.5px;">Supplier & Sales</h2>
        <button class="btn-primary-custom" style="padding:10px 18px;font-size:var(--font-size-sm);border-radius:30px;box-shadow:0 4px 15px var(--primary-bg);" onclick="showAddSupplier()">
            <i class="bi bi-plus-lg" style="margin-right:4px;"></i> Tambah
        </button>
    </div>

    <!-- PENCARIAN KHUSUS SUPPLIER & SALES -->
    <div style="margin-bottom: 24px; position: relative;" id="supplierSearchContainer">
        <div style="background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:4px 16px; display:flex; align-items:center; transition:all 0.3s;" id="searchBoxWrapper">
            <i class="bi bi-search" style="color:var(--text-muted);font-size:16px;"></i>
            <input type="text" id="localSupplierSearch" placeholder="Cari Nama Supplier atau Sales..." style="flex:1;border:none;background:transparent;padding:12px 12px;color:var(--text-primary);font-size:14px;outline:none;" autocomplete="off">
            <button type="button" id="btnClearLocalSearch" style="display:none;background:var(--surface-2);border:none;color:var(--text-secondary);padding:6px;border-radius:50%;cursor:pointer;width:28px;height:28px;align-items:center;justify-content:center;transition:all 0.2s;"><i class="bi bi-x-lg" style="font-size:12px;"></i></button>
        </div>
        <!-- Dropdown not needed for new UI, kept for fallback -->
        <div id="localSearchResults" style="display:none;"></div>
    </div>
    
    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- Supplier List -->
    <div id="supplierList" class="supplier-grid">
    <?php if (empty($suppliers)): ?>
        <div class="empty-state" id="emptyState" style="grid-column: 1 / -1;">
            <i class="bi bi-building"></i>
            <h3>Belum Ada Supplier</h3>
            <p>Tambahkan supplier pertama Anda</p>
        </div>
    <?php else: ?>
        <?php foreach ($suppliers as $s): 
            $initial = getInitial($s['name']);
            $bgColor = getAvatarColor($s['name'], $avatarColors);
        ?>
            <div class="supplier-card-modern" id="supplier-card-<?= $s['id'] ?>">
                <div class="supplier-actions-top">
                    <button onclick="showEditSupplier(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)" style="color:var(--text-secondary);" title="Edit"><i class="bi bi-pencil-square"></i></button>
                    <button onclick="deleteSupplier(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name']), ENT_QUOTES, 'UTF-8') ?>')" style="color:var(--danger);" title="Hapus"><i class="bi bi-trash"></i></button>
                </div>
                
                <div class="supplier-card-header">
                    <div class="supplier-avatar" style="background:<?= $bgColor ?>;">
                        <?= $initial ?>
                    </div>
                    <div class="supplier-info">
                        <div class="supplier-name" title="<?= htmlspecialchars($s['name']) ?>">
                            <?= htmlspecialchars($s['name']) ?>
                        </div>
                        <div class="supplier-meta">
                            <span class="badge-custom badge-info" style="font-size:9px; padding:3px 6px; text-transform:uppercase; letter-spacing:0.5px; background:var(--surface-2); color:var(--text-secondary); border:1px solid var(--border-color);">
                                <?= htmlspecialchars($s['type_name'] ?? 'Supplier') ?>
                            </span>
                            <?= $s['is_consignment'] ? '<span class="badge-custom badge-warning" style="font-size:9px; padding:3px 6px; background:rgba(255,193,7,0.15); color:#ffc107; border:1px solid rgba(255,193,7,0.3);">🏷️ Konsinyasi</span>' : '' ?>
                        </div>
                    </div>
                </div>

                <div class="supplier-actions-bottom">
                    <button class="btn-modern-action sales" onclick="showSalesReps(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>')">
                        <i class="bi bi-people-fill"></i> Kelola Sales
                    </button>
                    <button class="btn-modern-action products" onclick="showSupplierProducts(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>')">
                        <i class="bi bi-box-seam-fill"></i> Kelola Produk
                    </button>
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
    let activeTypeName = 'Pilih Jenis...';
    let typeOptionsHtml = `<li><a class="dropdown-item ${!s.type_id ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value=''; dp.querySelector('button span').textContent='Pilih Jenis...'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');">Pilih Jenis...</a></li>`;
    supplierTypes.forEach(st => {
        if (s.type_id == st.value) activeTypeName = st.label;
        typeOptionsHtml += `<li><a class="dropdown-item ${s.type_id == st.value ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='${st.value}'; dp.querySelector('button span').textContent='${st.label.replace(/'/g, "\\'")}'  ; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');">${st.label}</a></li>`;
    });

    return `
        <div class="modal-form-group">
            <label>Nama Supplier *</label>
            <input type="text" id="modalSupName" class="form-control-dark" value="${s.name || ''}" placeholder="Cth: PT Everbright" required>
        </div>
        <div class="modal-form-group">
            <label>Jenis Supplier</label>
            <div class="dropdown" style="width:100%;">
                <button class="btn-dropdown-modern dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span><i class="bi bi-tag-fill me-2 text-primary"></i>${activeTypeName}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:12px; min-width:100%;">
                    ${typeOptionsHtml}
                </ul>
                <input type="hidden" id="modalSupType" value="${s.type_id || ''}">
            </div>
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

function formatWAPhone(phone) {
    if (!phone) return '';
    let num = phone.replace(/[^0-9]/g, '');
    if (num.startsWith('0')) {
        num = '62' + num.substring(1);
    }
    return num;
}

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
                        <div style="font-weight:600;font-size:13px;">${sr.name} ${sr.status === 'Non-Aktif' ? '<span class="badge-custom badge-warning" style="font-size:9px;padding:2px 4px;margin-left:4px;">Non-Aktif</span>' : ''}</div>
                        <div style="font-size:11px;color:var(--text-muted);">${sr.phone || '-'} | Kunjungan: ${sr.visit_day || '-'}</div>
                    </div>
                    <div style="display:flex;gap:4px;">
                        ${sr.phone ? `<a href="https://wa.me/${formatWAPhone(sr.phone)}" target="_blank" class="btn-icon" style="color:#25D366;text-decoration:none;display:flex;align-items:center;justify-content:center;" title="Chat WhatsApp"><i class="bi bi-whatsapp"></i></a>` : ''}
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
        <div class="modal-form-group">
            <label>Status</label>
            <div class="dropdown" style="width:100%;">
                <button class="btn-dropdown-modern dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span><i class="bi bi-circle-fill me-2 ${sr.status === 'Non-Aktif' ? 'text-danger' : 'text-success'}" style="font-size:8px;"></i>${sr.status === 'Non-Aktif' ? 'Non-Aktif' : 'Aktif'}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:12px; min-width:100%;">
                    <li><a class="dropdown-item ${sr.status !== 'Non-Aktif' ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='Aktif'; dp.querySelector('button span').innerHTML='<i class=\'bi bi-circle-fill me-2 text-success\' style=\'font-size:8px;\'></i>Aktif'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');"><i class="bi bi-check-circle me-2 text-success"></i>Aktif</a></li>
                    <li><a class="dropdown-item ${sr.status === 'Non-Aktif' ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='Non-Aktif'; dp.querySelector('button span').innerHTML='<i class=\'bi bi-circle-fill me-2 text-danger\' style=\'font-size:8px;\'></i>Non-Aktif'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');"><i class="bi bi-x-circle me-2 text-danger"></i>Non-Aktif</a></li>
                </ul>
                <input type="hidden" id="modalSalesStatus" value="${sr.status === 'Non-Aktif' ? 'Non-Aktif' : 'Aktif'}">
            </div>
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
        status: document.getElementById('modalSalesStatus').value
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

    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        const q = searchInput.value.trim();
        if (q.length < 1) {
            resultsDiv.style.display = 'none';
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
    const ok = await AppModal.confirm('Hapus Produk Supplier', 'Hapus produk ini dari daftar supplier? Tindakan tidak bisa dibatalkan.', 'Ya, Hapus', 'var(--danger)');
    if (!ok) return;
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
    const btnClearLocalSearch = document.getElementById('btnClearLocalSearch');
    const supplierCards = document.querySelectorAll('.supplier-card-modern');
    
    if (!locSearchInput) return;
    
    let locSearchTimeout;

    function resetSearch() {
        btnClearLocalSearch.style.display = 'none';
        supplierCards.forEach(card => {
            card.style.display = 'flex';
        });
    }
    
    locSearchInput.addEventListener('input', () => {
        clearTimeout(locSearchTimeout);
        const q = locSearchInput.value.trim();
        
        if (q.length > 0) {
            btnClearLocalSearch.style.display = 'flex';
        } else {
            resetSearch();
            return;
        }
        
        locSearchTimeout = setTimeout(async () => {
            try {
                const data = await api(`${BASE_URL}api/suppliers/search?q=${encodeURIComponent(q)}`);
                const matchedIds = data ? data.map(s => String(s.id)) : [];
                
                supplierCards.forEach(card => {
                    const id = card.id.replace('supplier-card-', '');
                    if (matchedIds.includes(id)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            } catch (e) {
                supplierCards.forEach(card => card.style.display = 'none');
            }
        }, 300);
    });
    
    btnClearLocalSearch.addEventListener('click', () => {
        locSearchInput.value = '';
        resetSearch();
        locSearchInput.focus();
    });
});
</script>
