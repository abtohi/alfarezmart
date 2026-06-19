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
                <button onclick="openEditUserModal(<?= htmlspecialchars(json_encode([
                    'id' => $u['id'],
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'phone' => $u['phone'],
                    'user_level' => $u['user_level'],
                    'work_days' => $u['work_days'],
                    'work_start' => $u['work_start'],
                    'work_end' => $u['work_end']
                ])) ?>)"
                        title="Edit User"
                        style="background:var(--primary-bg);color:var(--primary);border:none;border-radius:var(--radius-sm);padding:6px 10px;cursor:pointer;font-size:11px;">
                    <i class="bi bi-pencil-square"></i> Edit
                </button>
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
    const modalPromise = AppModal.show({
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
                <select class="form-control-dark" id="mu_level" onchange="toggleScheduleUI(this.value)">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
            </div>
            <div id="schedule_section" class="schedule-card">
                <div class="schedule-header">
                    <div class="schedule-header-icon"><i class="bi bi-clock-fill"></i></div>
                    <div>
                        <div class="schedule-header-text">Jadwal Login Staff</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">Atur kapan staff boleh masuk ke sistem</div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                    <div>
                        <div class="schedule-time-label"><i class="bi bi-sun"></i> Mulai</div>
                        <input type="time" class="time-input-modern" id="mu_start" value="08:00">
                    </div>
                    <div>
                        <div class="schedule-time-label"><i class="bi bi-moon"></i> Selesai</div>
                        <input type="time" class="time-input-modern" id="mu_end" value="17:00">
                    </div>
                </div>
                <div class="schedule-time-label" style="margin-bottom:10px;"><i class="bi bi-calendar3"></i> Hari Kerja</div>
                <div style="display:flex;justify-content:space-between;gap:4px;" id="mu_days">
                    <span class="day-chip active" data-val="Senin">Sen</span>
                    <span class="day-chip active" data-val="Selasa">Sel</span>
                    <span class="day-chip active" data-val="Rabu">Rab</span>
                    <span class="day-chip active" data-val="Kamis">Kam</span>
                    <span class="day-chip active" data-val="Jumat">Jum</span>
                    <span class="day-chip" data-val="Sabtu">Sab</span>
                    <span class="day-chip" data-val="Minggu">Min</span>
                </div>
                <div id="mu_days_hidden"></div>
            </div>
        `,
        submitText: 'Buat User',
        onSubmit: async () => {
            const name = document.getElementById('mu_name').value.trim();
            const email = document.getElementById('mu_email').value.trim();
            const phone = document.getElementById('mu_phone').value.trim();
            const password = document.getElementById('mu_password').value;
            const level = document.getElementById('mu_level').value;
            
            const start = document.getElementById('mu_start').value;
            const end = document.getElementById('mu_end').value;
            const days = Array.from(document.querySelectorAll('#mu_days .day-chip.active')).map(el => el.dataset.val);

            if (!name) { showToast('Nama wajib diisi', 'warning'); return false; }
            if (!email && !phone) { showToast('Email atau No HP wajib diisi', 'warning'); return false; }
            if (password.length < 6) { showToast('Password min 6 karakter', 'warning'); return false; }

            const payload = { csrf_token: csrf, name, email, phone, password, user_level: level };
            if (level === 'staff') {
                payload.work_days = JSON.stringify(days);
                payload.work_start = start;
                payload.work_end = end;
            }

            const res = await api(`${BASE_URL}api/users`, 'POST', payload);
            if (res.error) { showToast(res.error, 'error'); return false; }
            showToast('User berhasil dibuat!', 'success');
            setTimeout(() => location.reload(), 800);
            return true;
        }
    });
    
    setTimeout(() => {
        toggleScheduleUI('staff');
        // Wire up day chip clicks for Add modal
        document.querySelectorAll('#mu_days .day-chip').forEach(chip => {
            chip.onclick = () => chip.classList.toggle('active');
        });
    }, 50);
    await modalPromise;
}

function toggleScheduleUI(level) {
    const sec = document.getElementById('schedule_section');
    if (!sec) return;
    if (level === 'staff') {
        sec.style.display = 'block';
        sec.style.animation = 'scheduleSlideIn 0.3s ease';
    } else {
        sec.style.display = 'none';
    }
}

// Inject keyframe animation once
if (!document.getElementById('schedule-anim-style')) {
    const st = document.createElement('style');
    st.id = 'schedule-anim-style';
    st.textContent = `
    @keyframes scheduleSlideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .day-chip {
        display: inline-flex; align-items: center; justify-content: center;
        width: 38px; height: 38px; border-radius: 50%;
        font-size: 11px; font-weight: 700;
        cursor: pointer; border: 2px solid transparent;
        background: rgba(255,255,255,0.05);
        color: var(--text-muted);
        transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1);
        user-select: none;
        letter-spacing: -0.3px;
    }
    .day-chip:hover { transform: scale(1.1); border-color: rgba(230,57,70,0.4); color: var(--text-primary); }
    .day-chip.active {
        background: linear-gradient(135deg,#e63946,#b8202e);
        border-color: transparent; color: #fff;
        box-shadow: 0 4px 14px rgba(230,57,70,0.45);
        transform: scale(1.05);
    }
    .time-input-modern {
        background: var(--bg-input);
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        color: var(--text-primary);
        font-size: 1.15rem;
        font-weight: 700;
        padding: 10px 12px;
        width: 100%; outline: none; text-align: center;
        letter-spacing: 1px;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: var(--font-family);
        -webkit-appearance: none;
        appearance: none;
    }
    .time-input-modern:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(230,57,70,0.18);
    }
    /* Chrome clock icon color override */
    .time-input-modern::-webkit-calendar-picker-indicator { filter: invert(1) opacity(0.5); cursor: pointer; }
    .schedule-card {
        background: linear-gradient(135deg, rgba(230,57,70,0.07) 0%, rgba(184,32,46,0.04) 100%);
        border: 1.5px solid rgba(230,57,70,0.22);
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 12px;
    }
    .schedule-header {
        display: flex; align-items: center; gap: 8px;
        margin-bottom: 16px;
    }
    .schedule-header-icon {
        width: 28px; height: 28px; border-radius: 8px;
        background: rgba(230,57,70,0.18);
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; color: #e63946; flex-shrink: 0;
    }
    .schedule-header-text { font-size: 12px; font-weight: 700; color: #e63946; }
    .schedule-time-label { font-size: 10px; font-weight: 600; color: var(--text-muted); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 4px; }
    `;
    document.head.appendChild(st);
}

async function openEditUserModal(u) {
    let days = [];
    if (u.work_days) {
        try { days = JSON.parse(u.work_days); } catch(e){}
    }
    const modalPromise = AppModal.show({
        title: 'Edit User',
        subtitle: 'Update data pengguna',
        icon: 'bi-pencil-square',
        iconColor: 'var(--primary-bg)',
        iconAccent: 'var(--primary)',
        bodyHTML: `
            <div class="modal-form-group"><label>Nama Lengkap *</label><input type="text" class="form-control-dark" id="eu_name" value="${u.name}" required></div>
            <div class="modal-form-group"><label>Email</label><input type="email" class="form-control-dark" id="eu_email" value="${u.email||''}"></div>
            <div class="modal-form-group"><label>No HP</label><input type="text" class="form-control-dark" id="eu_phone" value="${u.phone||''}"></div>
            <div class="modal-form-group"><label>Level Akses *</label>
                <select class="form-control-dark" id="eu_level" onchange="toggleScheduleUI(this.value)">
                    <option value="staff" ${u.user_level==='staff'?'selected':''}>Staff</option>
                    <option value="admin" ${u.user_level==='admin'?'selected':''}>Admin</option>
                    <option value="superadmin" ${u.user_level==='superadmin'?'selected':''}>Superadmin</option>
                </select>
            </div>
            <div id="schedule_section" class="schedule-card" style="display:${u.user_level==='staff'?'block':'none'}">
                <div class="schedule-header">
                    <div class="schedule-header-icon"><i class="bi bi-clock-fill"></i></div>
                    <div>
                        <div class="schedule-header-text">Jadwal Login Staff</div>
                        <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">Atur kapan staff boleh masuk ke sistem</div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                    <div>
                        <div class="schedule-time-label"><i class="bi bi-sun"></i> Mulai</div>
                        <input type="time" class="time-input-modern" id="eu_start" value="${u.work_start?u.work_start.substring(0,5):'08:00'}">
                    </div>
                    <div>
                        <div class="schedule-time-label"><i class="bi bi-moon"></i> Selesai</div>
                        <input type="time" class="time-input-modern" id="eu_end" value="${u.work_end?u.work_end.substring(0,5):'17:00'}">
                    </div>
                </div>
                <div class="schedule-time-label" style="margin-bottom:10px;"><i class="bi bi-calendar3"></i> Hari Kerja</div>
                <div style="display:flex;justify-content:space-between;gap:4px;" id="eu_days">
                    <span class="day-chip ${days.includes('Senin')?'active':''}" data-val="Senin">Sen</span>
                    <span class="day-chip ${days.includes('Selasa')?'active':''}" data-val="Selasa">Sel</span>
                    <span class="day-chip ${days.includes('Rabu')?'active':''}" data-val="Rabu">Rab</span>
                    <span class="day-chip ${days.includes('Kamis')?'active':''}" data-val="Kamis">Kam</span>
                    <span class="day-chip ${days.includes('Jumat')?'active':''}" data-val="Jumat">Jum</span>
                    <span class="day-chip ${days.includes('Sabtu')?'active':''}" data-val="Sabtu">Sab</span>
                    <span class="day-chip ${days.includes('Minggu')?'active':''}" data-val="Minggu">Min</span>
                </div>
            </div>
        `,
        submitText: 'Simpan',
        onSubmit: async () => {
            const name = document.getElementById('eu_name').value.trim();
            const email = document.getElementById('eu_email').value.trim();
            const phone = document.getElementById('eu_phone').value.trim();
            const level = document.getElementById('eu_level').value;
            
            const start = document.getElementById('eu_start').value;
            const end = document.getElementById('eu_end').value;
            const daysArr = Array.from(document.querySelectorAll('#eu_days .day-chip.active')).map(el => el.dataset.val);

            if (!name) { showToast('Nama wajib diisi', 'warning'); return false; }
            if (!email && !phone) { showToast('Email atau No HP wajib diisi', 'warning'); return false; }

            const payload = { csrf_token: csrf, name, email, phone, user_level: level };
            if (level === 'staff') {
                payload.work_days = JSON.stringify(daysArr);
                payload.work_start = start;
                payload.work_end = end;
            }

            const res = await api(`${BASE_URL}api/users/${u.id}/update`, 'POST', payload);
            if (res.error) { showToast(res.error, 'error'); return false; }
            showToast('User berhasil diupdate!', 'success');
            setTimeout(() => location.reload(), 800);
            return true;
        }
    });
    // Call after modal HTML is injected into DOM
    setTimeout(() => {
        toggleScheduleUI(u.user_level ? u.user_level.toLowerCase() : 'staff');
        // Wire up day chip clicks for Edit modal
        document.querySelectorAll('#eu_days .day-chip').forEach(chip => {
            chip.onclick = () => chip.classList.toggle('active');
        });
    }, 50);
    await modalPromise;
}

async function toggleUser(id, activate) {
    const actionText = activate ? 'Mengaktifkan' : 'Menonaktifkan';
    const confirmed = await AppModal.confirm(
        `${actionText} User`, 
        `Apakah Anda yakin ingin ${actionText.toLowerCase()} user ini?`, 
        'Ya, Lanjutkan'
    );
    if (!confirmed) return;

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
    const confirmed = await AppModal.confirm(
        'Hapus User', 
        `Apakah Anda yakin ingin menghapus user <strong>${name}</strong>? Tindakan ini tidak bisa dibatalkan!`, 
        'Ya, Hapus'
    );
    if (!confirmed) return;

    const res = await api(`${BASE_URL}api/users/${id}/delete`, 'POST', { csrf_token: csrf });
    if (res.success) {
        showToast('User dihapus', 'success');
        const row = document.getElementById(`user-row-${id}`);
        if (row) row.remove();
    } else showToast(res.error || 'Gagal', 'error');
}
</script>
