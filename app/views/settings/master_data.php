<!-- Master Data View -->
<div class="page-section">
    <div style="margin-bottom:20px;">
        <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">Master Data</h2>
        <p style="font-size:var(--font-size-sm); color:var(--text-muted);">Kelola Brand, Kategori, dan Satuan Produk</p>
    </div>
    
    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- Tabs Navigation (Simple Segmented Control) -->
    <div style="display:flex; background:var(--surface-2); border-radius:var(--radius-md); padding:4px; margin-bottom:16px;">
        <button class="tab-btn active" onclick="switchTab('brands')" id="tab-btn-brands" style="flex:1; padding:8px; border:none; background:var(--bg-secondary); border-radius:var(--radius-sm); color:var(--text-primary); font-size:13px; font-weight:600; cursor:pointer;">Brands</button>
        <button class="tab-btn" onclick="switchTab('categories')" id="tab-btn-categories" style="flex:1; padding:8px; border:none; background:transparent; border-radius:var(--radius-sm); color:var(--text-muted); font-size:13px; font-weight:600; cursor:pointer;">Kategori</button>
        <button class="tab-btn" onclick="switchTab('units')" id="tab-btn-units" style="flex:1; padding:8px; border:none; background:transparent; border-radius:var(--radius-sm); color:var(--text-muted); font-size:13px; font-weight:600; cursor:pointer;">Satuan</button>
    </div>

    <!-- BRANDS TAB -->
    <div id="tab-brands" class="tab-content" style="display:block;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="font-weight:600;"><i class="bi bi-tag" style="color:var(--primary);"></i> Daftar Brand</div>
            <button class="btn-outline-custom" style="padding:4px 12px; font-size:12px;" onclick="showAddBrand()">+ Tambah</button>
        </div>
        <div style="background:var(--surface-1); border-radius:var(--radius-md); border:1px solid var(--border-color); max-height:400px; overflow-y:auto;">
            <?php if(empty($brands)): ?>
                <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:13px;">Belum ada data</div>
            <?php else: ?>
                <?php foreach($brands as $b): ?>
                    <div style="padding:12px 16px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;" id="brand-<?= $b['id'] ?>">
                        <div>
                            <div style="font-weight:600; font-size:14px;"><?= htmlspecialchars($b['name']) ?></div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button class="btn-icon" style="color:var(--warning);" onclick="showEditBrand(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)"><i class="bi bi-pencil-square"></i></button>
                            <button class="btn-icon" style="color:var(--danger);" onclick="deleteItem('brands', <?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['name']), ENT_QUOTES, 'UTF-8') ?>')"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- CATEGORIES TAB -->
    <div id="tab-categories" class="tab-content" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="font-weight:600;"><i class="bi bi-grid" style="color:var(--info);"></i> Daftar Kategori</div>
            <button class="btn-outline-custom" style="padding:4px 12px; font-size:12px;" onclick="showAddCategory()">+ Tambah</button>
        </div>
        <div style="background:var(--surface-1); border-radius:var(--radius-md); border:1px solid var(--border-color); max-height:400px; overflow-y:auto;">
            <?php if(empty($categories)): ?>
                <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:13px;">Belum ada data</div>
            <?php else: ?>
                <?php foreach($categories as $c): ?>
                    <div style="padding:12px 16px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;" id="category-<?= $c['id'] ?>">
                        <div>
                            <div style="font-weight:600; font-size:14px;"><?= htmlspecialchars($c['name']) ?></div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button class="btn-icon" style="color:var(--warning);" onclick="showEditCategory(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>)"><i class="bi bi-pencil-square"></i></button>
                            <button class="btn-icon" style="color:var(--danger);" onclick="deleteItem('categories', <?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['name']), ENT_QUOTES, 'UTF-8') ?>')"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- UNITS TAB -->
    <div id="tab-units" class="tab-content" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="font-weight:600;"><i class="bi bi-box" style="color:var(--success);"></i> Daftar Satuan</div>
            <button class="btn-outline-custom" style="padding:4px 12px; font-size:12px;" onclick="showAddUnit()">+ Tambah</button>
        </div>
        <div style="background:var(--surface-1); border-radius:var(--radius-md); border:1px solid var(--border-color); max-height:400px; overflow-y:auto;">
            <?php if(empty($units)): ?>
                <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:13px;">Belum ada data</div>
            <?php else: ?>
                <?php foreach($units as $u): ?>
                    <div style="padding:12px 16px; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;" id="unit-<?= $u['id'] ?>">
                        <div>
                            <div style="font-weight:600; font-size:14px;"><?= htmlspecialchars($u['name']) ?></div>
                            <div style="font-size:12px; color:var(--text-muted);">Singkatan: <?= htmlspecialchars($u['abbreviation']) ?></div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button class="btn-icon" style="color:var(--warning);" onclick="showEditUnit(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)"><i class="bi bi-pencil-square"></i></button>
                            <button class="btn-icon" style="color:var(--danger);" onclick="deleteItem('units', <?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name']), ENT_QUOTES, 'UTF-8') ?>')"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const csrfVal = document.getElementById('csrfToken').value;

function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.background = 'transparent';
        btn.style.color = 'var(--text-muted)';
    });
    
    document.getElementById('tab-' + tabId).style.display = 'block';
    const activeBtn = document.getElementById('tab-btn-' + tabId);
    activeBtn.style.background = 'var(--bg-secondary)';
    activeBtn.style.color = 'var(--text-primary)';
}

async function saveItem(endpoint, data, isEdit = false) {
    try {
        const res = await api(`${BASE_URL}api/${endpoint}`, 'POST', data);
        if (res.success) {
            showToast(res.message || 'Berhasil disimpan', 'success');
            setTimeout(() => window.location.reload(), 1000);
            return true;
        }
    } catch (e) {
        showToast(e.message, 'error');
    }
    return false;
}

async function deleteItem(type, id, name) {
    await AppModal.show({
        title: 'Konfirmasi Hapus',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `<p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.7;">Yakin ingin menghapus <strong>"${name}"</strong>?<br><span style="color:var(--warning);font-size:11px;"><i class="bi bi-exclamation-triangle"></i> Operasi ini bisa gagal jika data sedang digunakan oleh produk.</span></p>`,
        submitText: 'Ya, Hapus',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const res = await api(`${BASE_URL}api/${type}/${id}/delete`, 'POST', { csrf_token: csrfVal });
                if (res.success) {
                    showToast('Berhasil dihapus', 'success');
                    // Remove DOM element - normalize type name to singular for element id
                    const singular = type.replace(/ies$/, 'y').replace(/s$/, '');
                    const el = document.getElementById(`${singular}-${id}`);
                    if (el) el.remove();
                    return true;
                }
            } catch (e) {
                showToast(e.message || 'Gagal menghapus data', 'error');
            }
            return false;
        }
    });
}

// ===== BRANDS =====
async function showAddBrand() {
    await AppModal.show({
        title: 'Tambah Brand',
        icon: 'bi-tag',
        iconColor: 'var(--primary-bg)',
        iconAccent: 'var(--primary)',
        bodyHTML: `<div class="modal-form-group"><label>Nama Brand</label><input type="text" id="mName" class="form-control-dark" placeholder="Cth: Indomie, Indofood..." autocomplete="off"></div>`,
        submitText: 'Simpan',
        onSubmit: async () => {
            const name = document.getElementById('mName').value.trim();
            if (!name) { showToast('Nama brand wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/brands`, 'POST', { name, csrf_token: csrfVal });
                if (res.success) {
                    showToast('Brand berhasil ditambahkan', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                    return true;
                }
            } catch (e) {
                showToast(e.message || 'Gagal menyimpan brand', 'error');
            }
            return false;
        }
    });
}

async function showEditBrand(b) {
    await AppModal.show({
        title: 'Edit Brand',
        icon: 'bi-pencil-square',
        iconColor: 'var(--warning-bg)',
        iconAccent: 'var(--warning)',
        bodyHTML: `<div class="modal-form-group"><label>Nama Brand</label><input type="text" id="mName" class="form-control-dark" value="${b.name}" autocomplete="off"></div>`,
        submitText: 'Update',
        onSubmit: async () => {
            const name = document.getElementById('mName').value.trim();
            if (!name) { showToast('Nama brand wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/brands/${b.id}`, 'POST', { name, csrf_token: csrfVal });
                if (res.success) {
                    showToast('Brand berhasil diupdate', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                    return true;
                }
            } catch (e) {
                showToast(e.message || 'Gagal mengupdate brand', 'error');
            }
            return false;
        }
    });
}

// ===== CATEGORIES =====
async function showAddCategory() {
    await AppModal.show({
        title: 'Tambah Kategori',
        icon: 'bi-grid',
        iconColor: 'var(--info-bg)',
        iconAccent: 'var(--info)',
        bodyHTML: `<div class="modal-form-group"><label>Nama Kategori</label><input type="text" id="mName" class="form-control-dark" placeholder="Cth: Minuman, Makanan..." autocomplete="off"></div>`,
        submitText: 'Simpan',
        onSubmit: async () => {
            const name = document.getElementById('mName').value.trim();
            if (!name) { showToast('Nama kategori wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/categories`, 'POST', { name, csrf_token: csrfVal });
                if (res.success) {
                    showToast('Kategori berhasil ditambahkan', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                    return true;
                }
            } catch (e) {
                showToast(e.message || 'Gagal menyimpan kategori', 'error');
            }
            return false;
        }
    });
}

async function showEditCategory(c) {
    await AppModal.show({
        title: 'Edit Kategori',
        icon: 'bi-pencil-square',
        iconColor: 'var(--warning-bg)',
        iconAccent: 'var(--warning)',
        bodyHTML: `<div class="modal-form-group"><label>Nama Kategori</label><input type="text" id="mName" class="form-control-dark" value="${c.name}" autocomplete="off"></div>`,
        submitText: 'Update',
        onSubmit: async () => {
            const name = document.getElementById('mName').value.trim();
            if (!name) { showToast('Nama kategori wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/categories/${c.id}`, 'POST', { name, csrf_token: csrfVal });
                if (res.success) {
                    showToast('Kategori berhasil diupdate', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                    return true;
                }
            } catch (e) {
                showToast(e.message || 'Gagal mengupdate kategori', 'error');
            }
            return false;
        }
    });
}

// ===== UNITS =====
async function showAddUnit() {
    await AppModal.show({
        title: 'Tambah Satuan',
        icon: 'bi-box',
        iconColor: 'var(--success-bg)',
        iconAccent: 'var(--success)',
        bodyHTML: `
            <div class="modal-form-group"><label>Nama Satuan</label><input type="text" id="mName" class="form-control-dark" placeholder="Cth: Karton, Renteng, Lusin" autocomplete="off"></div>
            <div class="modal-form-group"><label>Singkatan</label><input type="text" id="mAbbr" class="form-control-dark" placeholder="Cth: krt, rtg, lsn" autocomplete="off"></div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:-4px;padding:6px 10px;background:var(--info-bg);border-radius:var(--radius-sm);"><i class="bi bi-info-circle"></i> Singkatan dipakai sebagai format pendek di dropdown (contoh: Karton/krt).</div>
        `,
        submitText: 'Simpan',
        onSubmit: async () => {
            const name = document.getElementById('mName').value.trim();
            if (!name) { showToast('Nama satuan wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/units`, 'POST', { 
                    name, 
                    abbreviation: document.getElementById('mAbbr').value.trim(), 
                    csrf_token: csrfVal 
                });
                if (res.success) {
                    showToast(res.message || 'Satuan berhasil ditambahkan', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                    return true;
                }
            } catch (e) {
                // Check if it's a duplicate error
                if (e.message && e.message.includes('sudah ada')) {
                    await AppModal.show({
                        title: 'Satuan Sudah Ada',
                        icon: 'bi-exclamation-triangle',
                        iconColor: 'var(--warning-bg)',
                        iconAccent: 'var(--warning)',
                        bodyHTML: `<p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.7;margin:0;">${e.message}</p>`,
                        submitText: 'OK',
                        cancelText: null,
                        onSubmit: () => {
                            showAddUnit();
                            return true;
                        }
                    });
                } else {
                    showToast(e.message || 'Gagal menyimpan satuan', 'error');
                }
            }
            return false;
        }
    });
}

async function showEditUnit(u) {
    await AppModal.show({
        title: 'Edit Satuan',
        icon: 'bi-pencil-square',
        iconColor: 'var(--warning-bg)',
        iconAccent: 'var(--warning)',
        bodyHTML: `
            <div class="modal-form-group"><label>Nama Satuan</label><input type="text" id="mName" class="form-control-dark" value="${u.name}" autocomplete="off"></div>
            <div class="modal-form-group"><label>Singkatan</label><input type="text" id="mAbbr" class="form-control-dark" value="${u.abbreviation}" autocomplete="off"></div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:-4px;padding:6px 10px;background:var(--info-bg);border-radius:var(--radius-sm);"><i class="bi bi-info-circle"></i> Singkatan dipakai sebagai format pendek di dropdown (contoh: Karton/krt).</div>
        `,
        submitText: 'Update',
        onSubmit: async () => {
            const name = document.getElementById('mName').value.trim();
            if (!name) { showToast('Nama satuan wajib diisi', 'warning'); return false; }
            
            try {
                const res = await api(`${BASE_URL}api/units/${u.id}`, 'POST', { 
                    name, 
                    abbreviation: document.getElementById('mAbbr').value.trim(), 
                    csrf_token: csrfVal 
                });
                if (res.success) {
                    showToast(res.message || 'Satuan berhasil diupdate', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                    return true;
                }
            } catch (e) {
                showToast(e.message || 'Gagal mengupdate satuan', 'error');
            }
            return false;
        }
    });
}

async function deleteUnitWithConfirm(id, name) {
    await AppModal.show({
        title: 'Konfirmasi Hapus Satuan',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `<p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.7;">Yakin ingin menghapus satuan <strong>"${name}"</strong>?<br><span style="color:var(--warning);font-size:11px;"><i class="bi bi-exclamation-triangle"></i> Produk yang masih menggunakan satuan ini akan kehilangan data satuan (set NULL).</span></p>`,
        submitText: 'Ya, Hapus',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const res = await api(`${BASE_URL}api/units/${id}/delete`, 'POST', { csrf_token: csrfVal });
                if (res.success) {
                    showToast('Satuan berhasil dihapus', 'success');
                    const el = document.getElementById(`unit-${id}`);
                    if (el) el.remove();
                    return true;
                }
            } catch (e) {
                showToast(e.message || 'Gagal menghapus satuan', 'error');
            }
            return false;
        }
    });
}
</script>
