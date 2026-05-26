<!-- App Settings View -->
<div class="page-section">
    <div style="margin-bottom:20px;">
        <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">Pengaturan Aplikasi</h2>
        <p style="font-size:var(--font-size-sm); color:var(--text-muted);">Atur Konfigurasi AI Agent dan Ubah Password Akun Anda</p>
    </div>

    <!-- Tabs -->
    <div style="display:flex; border-bottom:1px solid var(--border-color); margin-bottom:20px; overflow-x:auto; white-space:nowrap;">
        <?php if (($currentUser['level'] ?? '') !== 'staff'): ?>
        <button id="tabBtn-ai" class="tab-btn active" style="padding:12px 16px; background:none; border:none; border-bottom:2px solid var(--primary); color:var(--primary); font-weight:700; font-size:var(--font-size-sm);" onclick="switchTab('ai')">
            <i class="bi bi-robot"></i> AI Agent
        </button>
        <button id="tabBtn-geo" class="tab-btn" style="padding:12px 16px; background:none; border:none; border-bottom:2px solid transparent; color:var(--text-muted); font-weight:600; font-size:var(--font-size-sm);" onclick="switchTab('geo')">
            <i class="bi bi-geo-alt"></i> Geofencing
        </button>
        <button id="tabBtn-pwd" class="tab-btn" style="padding:12px 16px; background:none; border:none; border-bottom:2px solid transparent; color:var(--text-muted); font-weight:600; font-size:var(--font-size-sm);" onclick="switchTab('pwd')">
        <?php else: ?>
        <button id="tabBtn-pwd" class="tab-btn active" style="padding:12px 16px; background:none; border:none; border-bottom:2px solid var(--primary); color:var(--primary); font-weight:700; font-size:var(--font-size-sm);" onclick="switchTab('pwd')">
        <?php endif; ?>
            <i class="bi bi-key"></i> Ganti Password
        </button>
    </div>

    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>" />

    <?php if (($currentUser['level'] ?? '') !== 'staff'): ?>
    <!-- AI Agent Tab -->
    <div id="tabContent-ai" style="display:block;">
        <form id="ai-settings-form">
            <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
                <div style="font-weight:600; margin-bottom:12px; color:var(--text-primary);">
                    <i class="bi bi-cpu" style="color:var(--info); margin-right:8px;"></i> OpenRouter API Config
                </div>
                
                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Model AI</label>
                    <input id="ai_model" name="ai_model" type="text" value="<?= htmlspecialchars($aiModel ?? 'google/gemini-2.5-flash') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="contoh: google/gemini-2.5-flash" required />
                    <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">Model dari OpenRouter yang digunakan.</small>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">API Key (OpenRouter)</label>
                    <input id="ai_api_key" name="ai_api_key" type="password" value="<?= htmlspecialchars($aiApiKey ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="sk-or-v1-..." />
                    <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">Kunci API dari OpenRouter. Biarkan kosong jika tidak diubah (disensor demi keamanan).</small>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">System Prompt (Invoice Scanner)</label>
                    <textarea id="ai_invoice_prompt" name="ai_invoice_prompt" rows="5" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm); resize:none;" placeholder="Prompt untuk menganalisa nota" required><?= htmlspecialchars($aiPrompt ?? '') ?></textarea>
                    <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">Prompt instruksi AI agar menghasilkan JSON berisi produk (name, qty, price).</small>
                </div>
            </div>
            <button type="submit" class="btn-primary-custom" style="width:100%; padding:12px; font-weight:600; margin-bottom:8px;">💾 Simpan Pengaturan AI</button>
        </form>
    </div>

    <!-- Geofencing Tab -->
    <div id="tabContent-geo" style="display:none;">
        <form id="geo-settings-form">
            <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
                <div style="font-weight:600; margin-bottom:12px; color:var(--text-primary);">
                    <i class="bi bi-geo-alt" style="color:var(--success); margin-right:8px;"></i> Pembatasan Lokasi Staff
                </div>
                
                <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:16px; background:rgba(255,255,255,0.03); padding:8px; border-radius:4px;">
                    Staff hanya bisa mengakses aplikasi jika berada dalam radius (meter) yang ditentukan dari koordinat toko ini. Pastikan Anda mengisinya dengan akurat.
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Latitude Toko</label>
                    <input id="store_latitude" name="store_latitude" type="text" value="<?= htmlspecialchars($storeLat ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="contoh: -6.200000" />
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Longitude Toko</label>
                    <input id="store_longitude" name="store_longitude" type="text" value="<?= htmlspecialchars($storeLng ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="contoh: 106.816666" />
                </div>
                
                <button type="button" class="btn-outline-custom" onclick="getLocation()" style="width:100%; padding:8px; margin-bottom:12px; font-size:12px; display:flex; align-items:center; justify-content:center; gap:6px;">
                    <i class="bi bi-crosshair"></i> Dapatkan Lokasi Saat Ini
                </button>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Radius Akses (Meter)</label>
                    <input id="store_radius_meters" name="store_radius_meters" type="number" value="<?= htmlspecialchars($storeRadius ?? '25') ?>" min="0" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="25" />
                    <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">Saran: 20-30 meter. Set 0 untuk menonaktifkan fitur Geofencing.</small>
                </div>
            </div>
            <button type="submit" class="btn-primary-custom" style="width:100%; padding:12px; font-weight:600; margin-bottom:8px;">💾 Simpan Pengaturan Lokasi</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Password Tab -->
    <div id="tabContent-pwd" style="display:<?= (($currentUser['level'] ?? '') === 'staff') ? 'block' : 'none' ?>;">
        <form id="password-form">
            <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
                <div style="font-weight:600; margin-bottom:12px; color:var(--text-primary);">
                    <i class="bi bi-shield-lock" style="color:var(--danger); margin-right:8px;"></i> Keamanan Akun
                </div>
                
                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Password Lama</label>
                    <input id="old_password" name="old_password" type="password" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="Masukkan Password Lama" required />
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Password Baru</label>
                    <input id="new_password" name="new_password" type="password" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="Password Baru" required />
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">Konfirmasi Password Baru</label>
                    <input id="confirm_password" name="confirm_password" type="password" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="Ulangi Password Baru" required />
                </div>
            </div>
            <button type="submit" class="btn-primary-custom" style="width:100%; padding:12px; font-weight:600; margin-bottom:8px; background:var(--danger); border-color:var(--danger);">Ubah Password</button>
        </form>
    </div>

    <a href="<?= BASE_URL ?>settings" class="btn-outline-custom" style="width:100%; padding:12px; text-align:center; display:block; font-weight:600;">Kembali ke Pengaturan</a>
</div>

<script>
    const csrfToken = document.getElementById('csrfToken').value;

    function switchTab(tab) {
        // Toggle Buttons
        document.getElementById('tabBtn-ai').style.borderBottomColor = 'transparent';
        document.getElementById('tabBtn-ai').style.color = 'var(--text-muted)';
        document.getElementById('tabBtn-ai').style.fontWeight = '600';
        
        document.getElementById('tabBtn-geo').style.borderBottomColor = 'transparent';
        document.getElementById('tabBtn-geo').style.color = 'var(--text-muted)';
        document.getElementById('tabBtn-geo').style.fontWeight = '600';
        
        document.getElementById('tabBtn-pwd').style.borderBottomColor = 'transparent';
        document.getElementById('tabBtn-pwd').style.color = 'var(--text-muted)';
        document.getElementById('tabBtn-pwd').style.fontWeight = '600';
        
        const activeBtn = document.getElementById('tabBtn-' + tab);
        activeBtn.style.borderBottomColor = 'var(--primary)';
        activeBtn.style.color = 'var(--primary)';
        activeBtn.style.fontWeight = '700';

        // Toggle Content
        document.getElementById('tabContent-ai').style.display = 'none';
        document.getElementById('tabContent-geo').style.display = 'none';
        document.getElementById('tabContent-pwd').style.display = 'none';
        
        document.getElementById('tabContent-' + tab).style.display = 'block';
    }

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('store_latitude').value = position.coords.latitude;
                document.getElementById('store_longitude').value = position.coords.longitude;
                showToast('Lokasi berhasil didapatkan', 'success');
            }, function(error) {
                showToast('Gagal mendapatkan lokasi. Pastikan izin lokasi diberikan.', 'error');
            });
        } else {
            showToast('Geolocation tidak didukung di browser ini.', 'error');
        }
    }

    // ── Save AI Settings ──────────────────────────────────────────────
    document.getElementById('ai-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        
        try {
            btn.disabled = true;
            btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
            
            const data = {
                csrf_token: csrfToken,
                ai_model: document.getElementById('ai_model').value,
                ai_api_key: document.getElementById('ai_api_key').value, // can be empty to skip updating
                ai_invoice_prompt: document.getElementById('ai_invoice_prompt').value
            };
            
            const result = await api('<?= BASE_URL ?>api/settings/app', 'POST', data);
            showToast(result.message || 'Pengaturan AI berhasil disimpan', 'success');
            
            // clear api key field so user knows it's saved but hidden
            if (data.ai_api_key) {
                document.getElementById('ai_api_key').value = '';
                document.getElementById('ai_api_key').placeholder = '(Tersimpan - Diubah untuk mengganti)';
            }
        } catch (err) {
            console.error('Error saving AI settings:', err);
            showToast(err.message || 'Gagal menyimpan pengaturan AI', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // ── Save Geo Settings ──────────────────────────────────────────────
    document.getElementById('geo-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        
        try {
            btn.disabled = true;
            btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
            
            const data = {
                csrf_token: csrfToken,
                store_latitude: document.getElementById('store_latitude').value,
                store_longitude: document.getElementById('store_longitude').value,
                store_radius_meters: document.getElementById('store_radius_meters').value
            };
            
            const result = await api('<?= BASE_URL ?>api/settings/app', 'POST', data);
            showToast(result.message || 'Pengaturan Lokasi berhasil disimpan', 'success');
        } catch (err) {
            console.error('Error saving Geo settings:', err);
            showToast(err.message || 'Gagal menyimpan pengaturan Lokasi', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    // ── Change Password ─────────────────────────────────────────────
    document.getElementById('password-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        
        const oldPwd = document.getElementById('old_password').value;
        const newPwd = document.getElementById('new_password').value;
        const confPwd = document.getElementById('confirm_password').value;
        
        if (newPwd !== confPwd) {
            showToast('Konfirmasi password baru tidak cocok', 'error');
            return;
        }
        if (newPwd.length < 6) {
            showToast('Password baru minimal 6 karakter', 'error');
            return;
        }
        
        try {
            btn.disabled = true;
            btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Memproses...';
            
            const data = {
                csrf_token: csrfToken,
                old_password: oldPwd,
                new_password: newPwd
            };
            
            const result = await api('<?= BASE_URL ?>api/users/change-password', 'POST', data);
            showToast(result.message || 'Password berhasil diubah', 'success');
            
            // Clear form
            this.reset();
        } catch (err) {
            console.error('Error changing password:', err);
            showToast(err.message || 'Gagal mengubah password', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });
</script>
