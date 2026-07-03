<div class="page-section" style="max-width: 900px; margin: 0 auto;">

    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="<?= BASE_URL ?>ppob" class="btn btn-icon me-3" style="background:var(--surface-3);color:var(--text-color); border:1px solid var(--border-color);">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="m-0 fw-bold" style="font-size:1.3rem;">Pengaturan PPOB</h4>
            <div class="text-muted" style="font-size:0.82rem; margin-top:2px;">Konfigurasi integrasi API Digiflazz untuk layanan PPOB</div>
        </div>
    </div>

    <!-- Connection Status Banner -->
    <div id="conn-banner" class="mb-4" style="display:none;"></div>

    <!-- ===== SECTION 1: CREDENTIALS ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
        <div class="card-header p-0" style="background:linear-gradient(135deg, rgba(var(--primary-rgb,79,140,255),0.12), rgba(var(--primary-rgb,79,140,255),0.04)); border-bottom:1px solid var(--border-color);">
            <div class="d-flex align-items-center px-4 py-3 gap-3">
                <div class="stat-icon blue" style="width:36px;height:36px;font-size:1rem;margin-bottom:0;flex-shrink:0;"><i class="bi bi-key-fill"></i></div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem;">Kredensial API Digiflazz</div>
                    <div class="text-muted" style="font-size:0.78rem;">Username dan API Key dari dashboard member Digiflazz Anda</div>
                </div>
                <div class="ms-auto">
                    <span id="mode-badge" class="badge <?= ($settings['mode'] ?? 'development') === 'production' ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-size:11px;">
                        <?= ($settings['mode'] ?? 'development') === 'production' ? '🟢 Production' : '🟡 Development' ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <form id="form-api-settings" onsubmit="saveSettings(event)">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            <i class="bi bi-person-fill me-1 text-primary"></i> Username Digiflazz
                        </label>
                        <input type="text" class="form-control" id="cfg-username"
                               value="<?= htmlspecialchars($settings['username'] ?? '') ?>"
                               placeholder="contoh: toko_alfarez" autocomplete="off" required>
                        <div class="form-text">Username yang terdaftar di akun Digiflazz Anda.</div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            <i class="bi bi-shield-lock-fill me-1 text-warning"></i> Mode Lingkungan
                        </label>
                        <select class="form-select" id="cfg-mode" onchange="updateModeBadge(this.value)">
                            <option value="development" <?= ($settings['mode'] ?? '') === 'development' ? 'selected' : '' ?>>🟡 Development (Sandbox/Testing)</option>
                            <option value="production" <?= ($settings['mode'] ?? '') === 'production' ? 'selected' : '' ?>>🟢 Production (Live/Transaksi Nyata)</option>
                        </select>
                        <div class="form-text">Development: gunakan API Key dev- | Production: gunakan API Key prod-</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            <i class="bi bi-key-fill me-1 text-danger"></i> API Key
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="cfg-apikey"
                                   value="<?= htmlspecialchars($settings['api_key'] ?? '') ?>"
                                   placeholder="dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('cfg-apikey', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            Dapatkan di: <strong>Dashboard Digiflazz → Profil → API Key</strong>.
                            Format dev: <code>dev-xxxx</code> | Format prod: <code>prod-xxxx</code>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            <i class="bi bi-shield-check me-1 text-info"></i> Webhook Secret
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="cfg-webhook"
                                   value="<?= htmlspecialchars($settings['webhook_secret'] ?? '') ?>"
                                   placeholder="Isi jika menggunakan webhook callback">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('cfg-webhook', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            Opsional. Digunakan untuk memverifikasi callback webhook dari Digiflazz.
                            Atur URL webhook ke: <code><?= rtrim(BASE_URL, '/') ?>/api/ppob/webhook</code>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <button type="submit" class="btn btn-primary" id="btn-save-cfg">
                        <i class="bi bi-save me-1"></i> Simpan Pengaturan
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btn-test-conn" onclick="testConnection()">
                        <i class="bi bi-plug me-1"></i> Test Koneksi
                    </button>
                </div>
                <div id="cfg-status" class="mt-3" style="display:none;"></div>
            </form>
        </div>
    </div>

    <!-- ===== SECTION 2: SALDO & STATUS ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
        <div class="card-header p-0" style="background:linear-gradient(135deg, rgba(25,135,84,0.12), rgba(25,135,84,0.04)); border-bottom:1px solid var(--border-color);">
            <div class="d-flex align-items-center px-4 py-3 gap-3">
                <div class="stat-icon green" style="width:36px;height:36px;font-size:1rem;margin-bottom:0;flex-shrink:0;"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem;">Saldo & Status Akun</div>
                    <div class="text-muted" style="font-size:0.78rem;">Cek saldo deposit dan status koneksi API secara real-time</div>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div style="background:var(--surface-3); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; text-align:center;">
                        <div class="text-muted" style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Saldo Deposit</div>
                        <div id="info-saldo" class="fw-bold mt-1" style="font-size:1.2rem; color:var(--primary);">-</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="background:var(--surface-3); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; text-align:center;">
                        <div class="text-muted" style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Status API</div>
                        <div id="info-status" class="fw-bold mt-1" style="font-size:1rem;">-</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="background:var(--surface-3); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; text-align:center;">
                        <div class="text-muted" style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Mode Aktif</div>
                        <div id="info-mode" class="fw-bold mt-1" style="font-size:0.9rem;">-</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="background:var(--surface-3); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; text-align:center;">
                        <div class="text-muted" style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Terakhir Cek</div>
                        <div id="info-time" class="fw-bold mt-1" style="font-size:0.85rem; color:var(--text-muted);">-</div>
                    </div>
                </div>
            </div>
            <button class="btn btn-outline-success" id="btn-cek-saldo" onclick="cekSaldo()">
                <i class="bi bi-arrow-clockwise me-1"></i> Cek Saldo Sekarang
            </button>
        </div>
    </div>

    <!-- ===== SECTION 3: SINKRONISASI PRODUK ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
        <div class="card-header p-0" style="background:linear-gradient(135deg, rgba(13,202,240,0.12), rgba(13,202,240,0.04)); border-bottom:1px solid var(--border-color);">
            <div class="d-flex align-items-center px-4 py-3 gap-3">
                <div class="stat-icon blue" style="width:36px;height:36px;font-size:1rem;margin-bottom:0;flex-shrink:0; background:rgba(13,202,240,0.15);color:#0dcaf0;"><i class="bi bi-cloud-arrow-down"></i></div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem;">Sinkronisasi Daftar Produk & Harga</div>
                    <div class="text-muted" style="font-size:0.78rem;">Tarik daftar produk terbaru dari Digiflazz ke database lokal</div>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div style="background:var(--surface-3); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px; margin-bottom:16px; font-size:13px; color:var(--text-muted);">
                <i class="bi bi-info-circle-fill me-2" style="color:#0dcaf0;"></i>
                Sinkronisasi akan mengunduh seluruh daftar produk dari API Digiflazz (<code>https://api.digiflazz.com/v1/price-list</code>) dan menyimpannya ke database lokal. Proses ini diperlukan agar produk tampil di halaman PPOB. Disarankan dilakukan secara berkala atau setelah mengganti API Key.
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-info" style="color:white;" id="btn-sync-prepaid" onclick="syncProducts('prepaid')">
                    <i class="bi bi-arrow-repeat me-1"></i> Sync Prabayar (Prepaid)
                </button>
                <button class="btn btn-outline-info" id="btn-sync-postpaid" onclick="syncProducts('postpaid')">
                    <i class="bi bi-arrow-repeat me-1"></i> Sync Pascabayar (Postpaid)
                </button>
            </div>
            <div id="sync-status" class="mt-3" style="display:none;"></div>
        </div>
    </div>

    <!-- ===== SECTION 4: WEBHOOK INFO ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color); border-radius:var(--radius-lg); overflow:hidden;">
        <div class="card-header p-0" style="background:linear-gradient(135deg, rgba(139,92,246,0.12), rgba(139,92,246,0.04)); border-bottom:1px solid var(--border-color);">
            <div class="d-flex align-items-center px-4 py-3 gap-3">
                <div class="stat-icon purple" style="width:36px;height:36px;font-size:1rem;margin-bottom:0;flex-shrink:0;"><i class="bi bi-broadcast"></i></div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem;">Konfigurasi Webhook</div>
                    <div class="text-muted" style="font-size:0.78rem;">Konfigurasi callback otomatis dari Digiflazz ke sistem Anda</div>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <p class="text-muted" style="font-size:13px;">Daftarkan URL berikut ke panel Digiflazz (<strong>Pengaturan → Webhook</strong>) agar status transaksi diperbarui otomatis:</p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="webhook-url" value="<?= rtrim(BASE_URL, '/') ?>/api/ppob/webhook" readonly style="font-family:monospace; font-size:13px; background:var(--surface-3);">
                <button class="btn btn-outline-secondary" type="button" onclick="copyWebhook()">
                    <i class="bi bi-clipboard" id="copy-icon"></i>
                </button>
            </div>

            <!-- API Reference Card -->
            <div style="background:var(--surface-3); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; font-size:12px;">
                <div class="fw-bold mb-2" style="font-size:13px;"><i class="bi bi-file-code me-2 text-primary"></i>Referensi API Digiflazz</div>
                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <div style="background:var(--surface-2); border-radius:var(--radius-sm); padding:10px; border:1px solid var(--border-color);">
                            <div class="fw-bold text-muted mb-1" style="font-size:10px; text-transform:uppercase;">Endpoint Utama</div>
                            <code style="font-size:11px; color:var(--primary);">POST https://api.digiflazz.com/v1/</code>
                            <ul class="mt-2 mb-0 ps-3" style="font-size:11px; color:var(--text-muted); line-height:2;">
                                <li><code>cek-saldo</code> — Cek saldo deposit</li>
                                <li><code>price-list</code> — Daftar produk & harga</li>
                                <li><code>transaction</code> — Transaksi prabayar</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div style="background:var(--surface-2); border-radius:var(--radius-sm); padding:10px; border:1px solid var(--border-color);">
                            <div class="fw-bold text-muted mb-1" style="font-size:10px; text-transform:uppercase;">Format Signature (Sign)</div>
                            <code style="font-size:11px; color:var(--success);">MD5(username + apiKey + "deposit")</code>
                            <div class="text-muted mt-2" style="font-size:11px; line-height:1.5;">
                                Gunakan API Key yang sesuai dengan mode:<br>
                                Dev: <code>dev-xxxx</code> | Prod: <code>prod-xxxx</code>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:var(--surface-2); border-radius:var(--radius-sm); padding:10px; border:1px solid var(--border-color);">
                            <div class="fw-bold text-muted mb-1" style="font-size:10px; text-transform:uppercase;">Whitelist IP Server</div>
                            <div class="text-muted" style="font-size:11px;">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                Wajib daftarkan IP server di <strong>Dashboard Digiflazz → Pengaturan → Whitelist IP</strong> sebelum API bisa digunakan.
                                IP Publik Server Anda: <span id="server-ip" class="fw-bold text-primary">Memuat...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SECTION 5: DANGER ZONE ===== -->
    <div class="card mb-4" style="border:1px solid rgba(220,53,69,0.3); border-radius:var(--radius-lg); overflow:hidden;">
        <div class="card-header" style="background:rgba(220,53,69,0.08); border-bottom:1px solid rgba(220,53,69,0.2);">
            <div class="fw-bold text-danger" style="font-size:0.95rem;"><i class="bi bi-exclamation-triangle-fill me-2"></i>Zona Berbahaya</div>
        </div>
        <div class="card-body p-4">
            <div class="text-muted" style="font-size:13px; margin-bottom:12px;">
                Tindakan berikut tidak dapat dibatalkan. Pastikan Anda mengerti konsekuensinya sebelum melanjutkan.
            </div>
            <button class="btn btn-outline-danger btn-sm" onclick="confirmClearSettings()">
                <i class="bi bi-trash me-1"></i> Hapus Semua Kredensial API
            </button>
        </div>
    </div>

</div>

<script>
// ===================== UTILS =====================
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const ico = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        inp.type = 'password';
        ico.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

function updateModeBadge(val) {
    const badge = document.getElementById('mode-badge');
    if (val === 'production') {
        badge.className = 'badge bg-success';
        badge.textContent = '🟢 Production';
    } else {
        badge.className = 'badge bg-warning text-dark';
        badge.textContent = '🟡 Development';
    }
}

function copyWebhook() {
    const val = document.getElementById('webhook-url').value;
    navigator.clipboard.writeText(val).then(() => {
        const ico = document.getElementById('copy-icon');
        ico.classList.replace('bi-clipboard', 'bi-check-lg');
        setTimeout(() => ico.classList.replace('bi-check-lg', 'bi-clipboard'), 2000);
    });
}

function showStatus(id, type, msg) {
    const el = document.getElementById(id);
    const icon = type === 'success' ? 'bi-check-circle-fill' : type === 'danger' ? 'bi-x-circle-fill' : 'bi-info-circle-fill';
    const color = type === 'success' ? '#198754' : type === 'danger' ? '#dc3545' : '#0dcaf0';
    el.style.display = 'block';
    el.innerHTML = `<div style="background:rgba(${type==='success'?'25,135,84':type==='danger'?'220,53,69':'13,202,240'},0.1); border:1px solid rgba(${type==='success'?'25,135,84':type==='danger'?'220,53,69':'13,202,240'},0.25); color:${color}; border-radius:var(--radius-md); padding:12px; font-size:13px; font-weight:600;"><i class="bi ${icon} me-2"></i>${msg}</div>`;
}

// ===================== SAVE SETTINGS =====================
async function saveSettings(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-cfg');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    btn.disabled = true;

    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: document.getElementById('cfg-username').value,
                api_key: document.getElementById('cfg-apikey').value,
                webhook_secret: document.getElementById('cfg-webhook').value,
                mode: document.getElementById('cfg-mode').value
            })
        });
        const data = await res.json();
        if (data.success) {
            showStatus('cfg-status', 'success', '✅ ' + data.message);
            setTimeout(() => { document.getElementById('cfg-status').style.display='none'; }, 4000);
        } else {
            showStatus('cfg-status', 'danger', data.message || 'Gagal menyimpan.');
        }
    } catch (err) {
        showStatus('cfg-status', 'danger', 'Koneksi error: ' + err.message);
    } finally {
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Pengaturan';
        btn.disabled = false;
    }
}

// ===================== TEST CONNECTION =====================
async function testConnection() {
    const btn = document.getElementById('btn-test-conn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';
    btn.disabled = true;
    showStatus('cfg-status', 'info', 'Menghubungkan ke API Digiflazz dan memeriksa saldo...');

    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/balance');
        const data = await res.json();
        if (data.success) {
            showStatus('cfg-status', 'success', `✅ Koneksi berhasil! Saldo deposit: <strong>Rp ${parseInt(data.deposit).toLocaleString('id-ID')}</strong>`);
        } else {
            showStatus('cfg-status', 'danger', '❌ ' + (data.message || 'Koneksi gagal. Periksa Username dan API Key.'));
        }
    } catch (err) {
        showStatus('cfg-status', 'danger', 'Error: ' + err.message);
    } finally {
        btn.innerHTML = '<i class="bi bi-plug me-1"></i> Test Koneksi';
        btn.disabled = false;
    }
}

// ===================== CEK SALDO =====================
async function cekSaldo() {
    const btn = document.getElementById('btn-cek-saldo');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memeriksa...';
    btn.disabled = true;
    document.getElementById('info-saldo').textContent = '...';
    document.getElementById('info-status').textContent = '...';

    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/balance');
        const data = await res.json();
        if (data.success) {
            document.getElementById('info-saldo').innerHTML = `<span style="color:var(--success);">Rp ${parseInt(data.deposit).toLocaleString('id-ID')}</span>`;
            document.getElementById('info-status').innerHTML = `<span style="color:#198754;"><i class="bi bi-circle-fill" style="font-size:8px;"></i> Terhubung</span>`;
        } else {
            document.getElementById('info-saldo').innerHTML = `<span style="color:var(--danger);">Error</span>`;
            document.getElementById('info-status').innerHTML = `<span style="color:#dc3545;"><i class="bi bi-circle-fill" style="font-size:8px;"></i> Gagal</span>`;
        }
        const mode = document.getElementById('cfg-mode').value;
        document.getElementById('info-mode').textContent = mode === 'production' ? '🟢 Production' : '🟡 Development';
        document.getElementById('info-time').textContent = new Date().toLocaleTimeString('id-ID');
    } catch (err) {
        document.getElementById('info-saldo').textContent = 'Error';
        document.getElementById('info-status').innerHTML = `<span style="color:#dc3545;">Offline</span>`;
    } finally {
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Cek Saldo Sekarang';
        btn.disabled = false;
    }
}

// ===================== SYNC PRODUCTS =====================
async function syncProducts(type) {
    const btnId = type === 'prepaid' ? 'btn-sync-prepaid' : 'btn-sync-postpaid';
    const btn = document.getElementById(btnId);
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyinkronkan...';
    btn.disabled = true;
    showStatus('sync-status', 'info', `Mengunduh daftar produk ${type === 'prepaid' ? 'prabayar' : 'pascabayar'} dari Digiflazz. Harap tunggu beberapa detik...`);

    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/sync-prices', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({type: type})
        });
        const data = await res.json();
        if (data.success) {
            showStatus('sync-status', 'success', `✅ Sinkronisasi ${type} berhasil! Daftar produk sudah diperbarui.`);
        } else {
            showStatus('sync-status', 'danger', '❌ ' + (data.message || 'Sinkronisasi gagal.'));
        }
    } catch (err) {
        showStatus('sync-status', 'danger', 'Error: ' + err.message);
    } finally {
        btn.innerHTML = type === 'prepaid'
            ? '<i class="bi bi-arrow-repeat me-1"></i> Sync Prabayar (Prepaid)'
            : '<i class="bi bi-arrow-repeat me-1"></i> Sync Pascabayar (Postpaid)';
        btn.disabled = false;
    }
}

// ===================== CLEAR SETTINGS =====================
function confirmClearSettings() {
    if (!confirm('⚠️ Yakin ingin menghapus semua kredensial API Digiflazz? Layanan PPOB akan berhenti berfungsi sampai Anda mengisi ulang.')) return;
    fetch('<?= BASE_URL ?>api/ppob/settings', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ username: '', api_key: '', webhook_secret: '', mode: 'development' })
    }).then(() => {
        document.getElementById('cfg-username').value = '';
        document.getElementById('cfg-apikey').value = '';
        document.getElementById('cfg-webhook').value = '';
        document.getElementById('cfg-mode').value = 'development';
        updateModeBadge('development');
        alert('Kredensial API berhasil dihapus.');
    });
}

// Load server IP
fetch('https://api.ipify.org?format=json').then(r => r.json()).then(d => {
    document.getElementById('server-ip').textContent = d.ip;
}).catch(() => {
    document.getElementById('server-ip').textContent = 'Tidak dapat dideteksi';
});

// Auto cek saldo saat halaman dimuat jika sudah ada API key
document.addEventListener('DOMContentLoaded', () => {
    const apiKey = document.getElementById('cfg-apikey').value;
    if (apiKey.length > 5) cekSaldo();
});
</script>
