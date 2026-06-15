<!-- User Management View (Superadmin Only) -->
<div class="page-section">
    <div style="margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h2 style="font-size:var(--font-size-lg);font-weight:700;margin-bottom:4px;">Manajemen User</h2>
            <p style="font-size:var(--font-size-sm);color:var(--text-muted);">Kelola akun pengguna & level akses</p>
        </div>
        <button class="btn-primary-custom" onclick="openAddUserModal()" style="padding:8px 14px;font-size:var(--font-size-xs);">
            <i class="bi bi-person-plus"></i> Tambah User
        </button>
    </div>

    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

    <!-- Level Legend -->
    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:16px;margin-bottom:16px;">
        <div style="font-weight:700;font-size:var(--font-size-xs);color:var(--text-muted);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-shield-lock" style="color:var(--primary);"></i> Level Akses
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:8px;">
            <div style="background:linear-gradient(135deg,rgba(230,57,70,0.12),rgba(230,57,70,0.04));border:1px solid rgba(230,57,70,0.25);border-radius:var(--radius-md);padding:10px 12px;display:flex;align-items:flex-start;gap:8px;">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(230,57,70,0.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-shield-fill-check" style="color:#e63946;font-size:14px;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:12px;color:#e63946;margin-bottom:2px;">Superadmin</div>
                    <div style="font-size:10px;color:var(--text-muted);line-height:1.4;">Akses penuh semua fitur &amp; pengaturan sistem</div>
                </div>
            </div>
            <div style="background:linear-gradient(135deg,rgba(46,213,115,0.10),rgba(46,213,115,0.03));border:1px solid rgba(46,213,115,0.22);border-radius:var(--radius-md);padding:10px 12px;display:flex;align-items:flex-start;gap:8px;">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(46,213,115,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-person-fill-gear" style="color:#2ed573;font-size:14px;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:12px;color:#2ed573;margin-bottom:2px;">Admin</div>
                    <div style="font-size:10px;color:var(--text-muted);line-height:1.4;">Tambah &amp; edit produk, input barang, tanpa hapus</div>
                </div>
            </div>
            <div style="background:linear-gradient(135deg,rgba(76,201,240,0.10),rgba(76,201,240,0.03));border:1px solid rgba(76,201,240,0.22);border-radius:var(--radius-md);padding:10px 12px;display:flex;align-items:flex-start;gap:8px;">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(76,201,240,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-person-fill-check" style="color:#4cc9f0;font-size:14px;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:12px;color:#4cc9f0;margin-bottom:2px;">Staff</div>
                    <div style="font-size:10px;color:var(--text-muted);line-height:1.4;">Scan barcode, POS kasir, cetak struk</div>
                </div>
            </div>
            <div style="background:linear-gradient(135deg,rgba(255,165,2,0.08),rgba(255,165,2,0.02));border:1px solid rgba(255,165,2,0.2);border-radius:var(--radius-md);padding:10px 12px;display:flex;align-items:flex-start;gap:8px;">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(255,165,2,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-person-fill" style="color:#ffa502;font-size:14px;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:12px;color:#ffa502;margin-bottom:2px;">Customer</div>
                    <div style="font-size:10px;color:var(--text-muted);line-height:1.4;">Fitur pelanggan <em style="opacity:0.7;">(Coming Soon)</em></div>
                </div>
            </div>
        </div>
    </div>

    <!-- User List -->
    <div id="userListContainer">
        <?php foreach (($users['data'] ?? []) as $u): ?>
        <?php
            $levelClass = match($u['user_level']) {
                'superadmin' => 'badge-danger',
                'admin'      => 'badge-success',
                'staff'      => 'badge-info',
                default      => 'badge-warning',
            };
        ?>
        <div class="menu-item" id="user-row-<?= $u['id'] ?>" style="flex-wrap:wrap;gap:8px;margin-bottom:8px;border-radius:var(--radius-lg);background:var(--surface-1);<?= !$u['is_active'] ? 'opacity:0.55;' : '' ?>">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;color:var(--primary);flex-shrink:0;">
                <?= strtoupper(substr($u['name'], 0, 1)) ?>
            </div>
            <div style="flex:1;min-width:120px;">
                <div style="font-weight:600;font-size:var(--font-size-sm);"><?= htmlspecialchars($u['name']) ?></div>
                <div style="font-size:11px;color:var(--text-muted);"><?= htmlspecialchars($u['email'] ?? $u['phone'] ?? '-') ?></div>
                <div style="margin-top:4px;">
                    <span class="badge-custom <?= $levelClass ?>"><?= ucfirst($u['user_level']) ?></span>
                    <?php if (!$u['is_active']): ?><span class="badge-custom badge-danger" style="margin-left:4px;">Nonaktif</span><?php endif; ?>
                </div>
            </div>
            <div style="display:flex;gap:6px;align-items:center;">
                <button onclick="toggleUser(<?= $u['id'] ?>, <?= $u['is_active'] ? 'false':'true' ?>)"
                        title="<?= $u['is_active'] ? 'Nonaktifkan':'Aktifkan' ?>"
                        style="background:<?= $u['is_active'] ? 'var(--warning-bg)':'var(--success-bg)' ?>;color:<?= $u['is_active'] ? 'var(--warning)':'var(--success)' ?>;border:none;border-radius:var(--radius-sm);padding:6px 10px;cursor:pointer;font-size:11px;">
                    <i class="bi bi-<?= $u['is_active'] ? 'pause-circle':'play-circle' ?>"></i>
                    <?= $u['is_active'] ? 'Nonaktif':'Aktifkan' ?>
                </button>
                <button onclick="resetPassword(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>')"
                        title="Reset Password"
                        style="background:var(--info-bg);color:var(--info);border:none;border-radius:var(--radius-sm);padding:6px 10px;cursor:pointer;font-size:11px;">
                    <i class="bi bi-key"></i>
                </button>
                <?php if ($u['id'] !== ($_SESSION['user_id'] ?? 0)): ?>
                <button onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>')"
                        title="Hapus User"
                        style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:var(--radius-sm);padding:6px 10px;cursor:pointer;font-size:11px;">
                    <i class="bi bi-trash"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
const csrf = document.getElementById('csrfToken').value;

async function openAddUserModal() {
    await AppModal.show({
        title: 'Tambah User Baru',
        subtitle: 'Isi data user dan pilih level akses',
        icon: 'bi-person-plus',
        iconColor: 'var(--primary-bg)',
        iconAccent: 'var(--primary)',
        bodyHTML: `
            <div class="modal-form-group"><label>Nama Lengkap *</label><input type="text" class="form-control-dark" id="mu_name" placeholder="Nama lengkap" required></div>
            <div class="modal-form-group"><label>Email</label><input type="email" class="form-control-dark" id="mu_email" placeholder="email@example.com"></div>
            <div class="modal-form-group"><label>No HP</label><input type="text" class="form-control-dark" id="mu_phone" placeholder="08xx..."></div>
            <div class="modal-form-group"><label>Password *</label><input type="password" class="form-control-dark" id="mu_password" placeholder="Min 6 karakter" required></div>
            <div class="modal-form-group"><label>Level Akses *</label>
                <div class="dropdown" style="width:100%;">
                    <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:100%; text-align:left; display:flex; justify-content:space-between; align-items:center; padding:10px; font-size:12px; background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary); border-radius:var(--radius-md);">
                        <span>Staff</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark shadow" style="font-size:12px; min-width:100%;">
                        <li><a class="dropdown-item active" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='staff'; dp.querySelector('button span').textContent='Staff'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');">Staff</a></li>
                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='admin'; dp.querySelector('button span').textContent='Admin'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');">Admin</a></li>
                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='superadmin'; dp.querySelector('button span').textContent='Superadmin'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');">Superadmin</a></li>
                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); const dp=this.closest('.dropdown'); dp.querySelector('input').value='customer'; dp.querySelector('button span').textContent='Customer (Coming Soon)'; dp.querySelectorAll('.dropdown-item').forEach(el=>el.classList.remove('active')); this.classList.add('active');">Customer (Coming Soon)</a></li>
                    </ul>
                    <input type="hidden" id="mu_level" value="staff">
                </div>
            </div>
        `,
        submitText: 'Buat User',
        onSubmit: async () => {
            const name = document.getElementById('mu_name').value.trim();
            const email = document.getElementById('mu_email').value.trim();
            const phone = document.getElementById('mu_phone').value.trim();
            const password = document.getElementById('mu_password').value;
            const level = document.getElementById('mu_level').value;

            if (!name) { showToast('Nama wajib diisi', 'warning'); return false; }
            if (!email && !phone) { showToast('Email atau No HP wajib diisi', 'warning'); return false; }
            if (password.length < 6) { showToast('Password min 6 karakter', 'warning'); return false; }

            const res = await api(`${BASE_URL}api/users`, 'POST', { csrf_token: csrf, name, email, phone, password, user_level: level });
            if (res.error) { showToast(res.error, 'error'); return false; }
            showToast('User berhasil dibuat!', 'success');
            setTimeout(() => location.reload(), 800);
            return true;
        }
    });
}

async function toggleUser(id, activate) {
    const label = activate ? 'mengaktifkan' : 'menonaktifkan';
    if (!confirm(`${activate ? 'Aktifkan' : 'Nonaktifkan'} user ini?`)) return;
    const res = await api(`${BASE_URL}api/users/${id}/toggle-active`, 'POST', { csrf_token: csrf });
    if (res.success) { showToast(res.message, 'success'); setTimeout(() => location.reload(), 600); }
    else showToast(res.error || 'Gagal', 'error');
}

async function resetPassword(id, name) {
    await AppModal.show({
        title: `Reset Password`,
        subtitle: name,
        icon: 'bi-key',
        iconColor: 'var(--info-bg)',
        iconAccent: 'var(--info)',
        bodyHTML: `<div class="modal-form-group"><label>Password Baru *</label><input type="password" class="form-control-dark" id="rp_pass" placeholder="Min 6 karakter" required></div>`,
        submitText: 'Reset Password',
        onSubmit: async () => {
            const pass = document.getElementById('rp_pass').value;
            if (pass.length < 6) { showToast('Password min 6 karakter', 'warning'); return false; }
            const res = await api(`${BASE_URL}api/users/${id}/reset-password`, 'POST', { csrf_token: csrf, password: pass });
            if (res.error) { showToast(res.error, 'error'); return false; }
            showToast('Password berhasil direset!', 'success');
            return true;
        }
    });
}

async function deleteUser(id, name) {
    if (!confirm(`Hapus user "${name}"? Tindakan ini tidak bisa dibatalkan!`)) return;
    const res = await api(`${BASE_URL}api/users/${id}/delete`, 'POST', { csrf_token: csrf });
    if (res.success) {
        showToast('User dihapus', 'success');
        const row = document.getElementById(`user-row-${id}`);
        if (row) row.remove();
    } else showToast(res.error || 'Gagal', 'error');
}
</script>
