<!-- App Settings View -->
<div class="page-section">
    <div style="margin-bottom:20px;">
        <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">Pengaturan Aplikasi</h2>
        <p style="font-size:var(--font-size-sm); color:var(--text-muted);">Atur Konfigurasi AI Agent dan Ubah Password Akun Anda</p>
    </div>

    <!-- Tabs -->
    <div style="display:flex; border-bottom:1px solid var(--border-color); margin-bottom:20px;">
        <button id="tabBtn-ai" class="tab-btn active" style="flex:1; padding:12px; background:none; border:none; border-bottom:2px solid var(--primary); color:var(--primary); font-weight:700; font-size:var(--font-size-sm);" onclick="switchTab('ai')">
            <i class="bi bi-robot"></i> AI Agent
        </button>
        <button id="tabBtn-pwd" class="tab-btn" style="flex:1; padding:12px; background:none; border:none; border-bottom:2px solid transparent; color:var(--text-muted); font-weight:600; font-size:var(--font-size-sm);" onclick="switchTab('pwd')">
            <i class="bi bi-key"></i> Ganti Password
        </button>
    </div>

    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?>" />

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

    <!-- Password Tab -->
    <div id="tabContent-pwd" style="display:none;">
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
        
        document.getElementById('tabBtn-pwd').style.borderBottomColor = 'transparent';
        document.getElementById('tabBtn-pwd').style.color = 'var(--text-muted)';
        document.getElementById('tabBtn-pwd').style.fontWeight = '600';
        
        const activeBtn = document.getElementById('tabBtn-' + tab);
        activeBtn.style.borderBottomColor = 'var(--primary)';
        activeBtn.style.color = 'var(--primary)';
        activeBtn.style.fontWeight = '700';

        // Toggle Content
        document.getElementById('tabContent-ai').style.display = 'none';
        document.getElementById('tabContent-pwd').style.display = 'none';
        
        document.getElementById('tabContent-' + tab).style.display = 'block';
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
