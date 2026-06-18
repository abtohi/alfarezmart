<!-- Customers View -->
<div class="page-section" style="padding-bottom: 80px;">
    <!-- Header Summary Card -->
    <div style="background:var(--gradient-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <a href="<?= BASE_URL ?>dashboard" style="display:inline-flex; align-items:center; gap:6px; color:var(--text-muted); text-decoration:none; font-size:var(--font-size-xs); font-weight:600; margin-bottom:12px; transition:color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
                <h4 style="font-weight:800; font-size:var(--font-size-lg); margin:0; color:var(--text-primary); letter-spacing:-0.5px;">Database Pelanggan</h4>
                <p style="font-size:var(--font-size-sm); color:var(--text-muted); margin:4px 0 0 0; opacity:0.8;">Kelola data pelanggan dan kategori tier harga</p>
            </div>
            <div style="width:48px; height:48px; background:rgba(var(--bs-primary-rgb, 13,110,253), 0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--primary); flex-shrink:0;">
                <i class="bi bi-people-fill" style="font-size:1.5rem;"></i>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div style="display:flex; gap:8px; margin-bottom:20px;">
        <div class="search-input-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" id="searchCustomers" placeholder="Cari nama atau no HP..." oninput="debounceCustomerSearch()">
        </div>
        <button class="btn-primary-custom" style="padding:10px 16px; display:flex; align-items:center; gap:8px; font-weight:600; cursor:pointer;" onclick="showAddCustomerModal()">
            <i class="bi bi-person-plus-fill" style="font-size:1.1rem;"></i> <span style="white-space:nowrap;">Pelanggan Baru</span>
        </button>
    </div>

    <!-- Customers List Container -->
    <div id="customersList">
        <div class="elegant-loader" style="margin:20px auto;"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
    </div>
</div>

<script>
    const customerTypes = <?= json_encode($customerTypes ?? []) ?>;
    const csrfVal = '<?= $csrfToken ?? '' ?>';
    
    let _customerSearchTimeout = null;

    function debounceCustomerSearch() {
        clearTimeout(_customerSearchTimeout);
        _customerSearchTimeout = setTimeout(() => {
            loadCustomers();
        }, 300);
    }

    async function loadCustomers() {
        const container = document.getElementById('customersList');
        container.innerHTML = `<div class="elegant-loader" style="margin:20px auto;"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>`;
        
        try {
            const q = document.getElementById('searchCustomers').value;
            const res = await api(`${BASE_URL}api/customers?q=${encodeURIComponent(q)}`);
            if (res.success) {
                if (res.data.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="bi bi-people-fill"></i>
                            <h3>Belum Ada Pelanggan</h3>
                            <p>Tambahkan pelanggan toko Anda</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                res.data.forEach(c => {
                    const phone = c.phone || 'Tidak ada HP';
                    const addr = c.address || 'Tidak ada alamat';
                    const notes = c.notes || ''; 
                    const isAnon = c.name.toLowerCase().includes('tanpa nama');

                    // Find type name if available
                    let typeBadge = '';
                    if (c.type_id) {
                        const t = customerTypes.find(x => x.id == c.type_id);
                        if (t) {
                            typeBadge = `<span class="badge-custom badge-info" style="font-size:9px; margin-left:6px;"><i class="bi bi-tag-fill"></i> ${t.name}</span>`;
                        }
                    }

                    html += `
                        <div class="product-card" style="margin-bottom:12px; cursor:default; border-left: 3px solid var(--primary);">
                            <div class="product-icon" style="background:var(--primary-bg); color:var(--primary); flex-shrink:0;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div style="flex:1; min-width:0; margin-left:12px;">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div style="font-weight:700; font-size:var(--font-size-sm); color:var(--text-primary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap; display:flex; align-items:center;">
                                        ${c.name}
                                        ${isAnon ? '<span class="badge-custom badge-danger" style="font-size:9px; margin-left:4px;">Tanpa Nama</span>' : ''}
                                        ${typeBadge}
                                    </div>
                                    <div style="display:flex; gap:6px;">
                                        ${c.phone ? `<a href="https://wa.me/${c.phone.replace(/^0/, '62').replace(/\D/g, '')}" target="_blank" class="btn-icon" style="color:#25D366; text-decoration:none; display:flex; align-items:center; justify-content:center; width:32px; height:32px;" title="Hubungi via WhatsApp"><i class="bi bi-whatsapp"></i></a>` : ''}
                                        <button onclick="showEditCustomerModal(${JSON.stringify(c).replace(/"/g, '&quot;')})" class="btn-icon" style="color:var(--text-primary); width:32px; height:32px;" title="Edit Pelanggan"><i class="bi bi-pencil-square"></i></button>
                                        <button onclick="deleteCustomer(${c.id}, '${c.name.replace(/'/g, "\\'")}')" class="btn-icon" style="color:var(--danger); width:32px; height:32px;" title="Hapus Pelanggan"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:4px;">
                                    <i class="bi bi-telephone"></i> ${phone} <span style="margin:0 4px;">&bull;</span> <i class="bi bi-geo-alt"></i> ${addr}
                                </div>
                                ${notes ? `<div style="font-size:11px; color:var(--text-muted); margin-top:4px; font-style:italic; background:var(--surface-2); padding:6px 8px; border-radius:4px; display:inline-block; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Ciri/Keterangan: ${notes}</div>` : ''}
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }
        } catch (e) {
            showToast(e.message, 'error');
        }
    }

    function getCustomerFormHTML(c = {}) {
        let optionsListHtml = '';
        let activeTypeName = 'Pilih Level...';
        customerTypes.forEach(t => {
            if (c.type_id == t.id) activeTypeName = `${t.name} (Tier: ${t.price_tier})`;
            optionsListHtml += `<li><a class="dropdown-item ${c.type_id == t.id ? 'active' : ''}" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='${t.id}'; dp.querySelector('button span').textContent='${t.name} (Tier: ${t.price_tier})'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active'); dp.querySelector('input').dispatchEvent(new Event('change'));">${t.name} (Tier: ${t.price_tier})</a></li>`;
        });

        const isAnon = c.name ? c.name.toLowerCase().includes('tanpa nama') : false;

        return `
            <div class="modal-form-group" style="margin-bottom:12px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; background:var(--surface-2); padding:10px; border-radius:var(--radius-sm); border:1px solid var(--border-color);">
                    <input type="checkbox" id="modalCustAnon" value="1" ${isAnon ? 'checked' : ''} onchange="toggleAnonCheckbox(this.checked)" style="width:18px; height:18px; accent-color:var(--primary);">
                    <span style="font-size:var(--font-size-sm); font-weight:600; color:var(--text-primary);">Nama Pelanggan Tidak Diketahui</span>
                </label>
                <div style="font-size:10px; color:var(--text-muted); margin-top:4px; margin-left:26px;">Gunakan opsi ini jika pelanggan tidak tahu namanya, dan kasir hanya mencatat ciri fisik.</div>
            </div>
            
            <div class="modal-form-group" id="groupCustName">
                <label>Nama Pelanggan *</label>
                <input type="text" id="modalCustName" class="form-control-dark" value="${c.name || ''}" placeholder="Cth: Budi Santoso" required>
            </div>
            
            <div class="modal-form-group">
                <label>Nomor HP / WA</label>
                <input type="text" id="modalCustPhone" class="form-control-dark" value="${c.phone || ''}" placeholder="Cth: 0812...">
            </div>

            <div class="modal-form-group">
                <label>Alamat</label>
                <input type="text" id="modalCustAddr" class="form-control-dark" value="${c.address || ''}" placeholder="Alamat lengkap...">
            </div>

            <div class="modal-form-group">
                <label>Level Kategori Pelanggan</label>
                <div class="dropdown" style="width:100%;">
                    <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:10px; font-size:12px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                        <span>${activeTypeName}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:12px; min-width:100%;">
                        ${optionsListHtml}
                    </ul>
                    <input type="hidden" id="modalCustType" value="${c.type_id || ''}">
                </div>
            </div>

            <div class="modal-form-group">
                <label id="labelNotes">Catatan / Ciri-Ciri Fisik ${isAnon ? '*' : ''}</label>
                <textarea id="modalCustNotes" class="form-control-dark" placeholder="Cth: Ibu-ibu kerudung merah, sering bawa anak kecil, pakai motor Beat" rows="3" required>${c.notes || ''}</textarea>
            </div>
        `;
    }

    window.toggleAnonCheckbox = function(checked) {
        const nameGroup = document.getElementById('groupCustName');
        const nameInput = document.getElementById('modalCustName');
        const notesLabel = document.getElementById('labelNotes');
        
        if (checked) {
            nameGroup.style.opacity = '0.5';
            nameInput.disabled = true;
            nameInput.value = 'Pelanggan Tanpa Nama';
            notesLabel.innerHTML = 'Catatan / Ciri-Ciri Fisik *';
            document.getElementById('modalCustNotes').focus();
        } else {
            nameGroup.style.opacity = '1';
            nameInput.disabled = false;
            nameInput.value = '';
            notesLabel.innerHTML = 'Catatan / Ciri-Ciri Fisik';
        }
    };

    window.showAddCustomerModal = async function() {
        await AppModal.show({
            title: 'Tambah Pelanggan',
            subtitle: 'Tambahkan data pelanggan baru',
            icon: 'bi-person-plus-fill',
            iconColor: 'var(--success-bg)',
            iconAccent: 'var(--success)',
            bodyHTML: getCustomerFormHTML(),
            submitText: 'Simpan',
            onSubmit: async () => {
                const isAnon = document.getElementById('modalCustAnon').checked;
                let name = document.getElementById('modalCustName').value.trim();
                const phone = document.getElementById('modalCustPhone').value.trim();
                const address = document.getElementById('modalCustAddr').value.trim();
                const notes = document.getElementById('modalCustNotes').value.trim();
                const typeId = document.getElementById('modalCustType').value;

                if (isAnon) {
                    if (!notes) {
                        showToast('Ciri-ciri fisik wajib diisi jika nama tidak diketahui', 'warning');
                        return false;
                    }
                    const shortTrait = notes.split(',')[0].substring(0, 30);
                    name = `Tanpa Nama - ${shortTrait}`;
                } else if (!name) {
                    showToast('Nama pelanggan wajib diisi', 'warning');
                    return false;
                }

                try {
                    const res = await api(`${BASE_URL}api/customers`, 'POST', {
                        csrf_token: csrfVal,
                        name: name,
                        phone: phone,
                        address: address,
                        notes: notes,
                        type_id: typeId
                    });

                    if (res.success) {
                        showToast('Pelanggan berhasil ditambahkan', 'success');
                        loadCustomers();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });

        // Initialize state
        toggleAnonCheckbox(false);
    };

    window.showEditCustomerModal = async function(c) {
        await AppModal.show({
            title: 'Edit Pelanggan',
            subtitle: 'Ubah data pelanggan',
            icon: 'bi-pencil-square',
            iconColor: 'var(--success-bg)',
            iconAccent: 'var(--success)',
            bodyHTML: getCustomerFormHTML(c),
            submitText: 'Update',
            onSubmit: async () => {
                const isAnon = document.getElementById('modalCustAnon').checked;
                let name = document.getElementById('modalCustName').value.trim();
                const phone = document.getElementById('modalCustPhone').value.trim();
                const address = document.getElementById('modalCustAddr').value.trim();
                const notes = document.getElementById('modalCustNotes').value.trim();
                const typeId = document.getElementById('modalCustType').value;

                if (isAnon) {
                    if (!notes) {
                        showToast('Ciri-ciri fisik wajib diisi jika nama tidak diketahui', 'warning');
                        return false;
                    }
                    const shortTrait = notes.split(',')[0].substring(0, 30);
                    name = `Tanpa Nama - ${shortTrait}`;
                } else if (!name) {
                    showToast('Nama pelanggan wajib diisi', 'warning');
                    return false;
                }

                try {
                    const res = await api(`${BASE_URL}api/customers/${c.id}`, 'POST', {
                        csrf_token: csrfVal,
                        name: name,
                        phone: phone,
                        address: address,
                        notes: notes,
                        type_id: typeId
                    });

                    if (res.success) {
                        showToast('Pelanggan berhasil diupdate', 'success');
                        loadCustomers();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });

        const isAnon = c.name.toLowerCase().includes('tanpa nama');
        toggleAnonCheckbox(isAnon);
    };

    window.deleteCustomer = async function(id, name) {
        await AppModal.show({
            title: 'Hapus Pelanggan',
            subtitle: 'Konfirmasi Penghapusan',
            icon: 'bi-exclamation-triangle',
            iconColor: 'var(--danger-bg)',
            iconAccent: 'var(--danger)',
            bodyHTML: `<p style="text-align:center; font-size:var(--font-size-sm); margin:0;">Apakah Anda yakin ingin menghapus pelanggan <strong>${name}</strong>? Tindakan ini tidak dapat dibatalkan.</p>`,
            submitText: 'Hapus',
            onSubmit: async () => {
                try {
                    const res = await api(`${BASE_URL}api/customers/${id}/delete`, 'POST', {
                        csrf_token: csrfVal
                    });
                    if (res.success) {
                        showToast('Pelanggan berhasil dihapus', 'success');
                        loadCustomers();
                        return true;
                    }
                } catch (e) {
                    showToast(e.message, 'error');
                }
                return false;
            }
        });
    };

    // Load data initially
    document.addEventListener('DOMContentLoaded', () => {
        loadCustomers();
    });
</script>
