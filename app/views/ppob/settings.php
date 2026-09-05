<div class="page-section" style="max-width:860px;margin:0 auto;">

    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="<?= BASE_URL ?>ppob" class="btn btn-icon me-3" style="background:var(--surface-2);color:var(--text-primary);border:1px solid var(--border-color);">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="m-0 fw-bold" style="font-family:var(--font-family);font-size:var(--font-size-lg);">Pengaturan PPOB</h4>
            <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:2px;">Konfigurasi integrasi API Digiflazz untuk layanan PPOB</div>
        </div>
        <div class="ms-auto">
            <span id="mode-badge" style="font-size:11px;padding:5px 12px;border-radius:20px;font-weight:700;font-family:var(--font-family);
                <?= ($settings['mode'] ?? 'development') === 'production' ? 'background:var(--success-bg);color:var(--success);border:1px solid rgba(46,196,182,0.3);' : 'background:var(--warning-bg);color:var(--warning);border:1px solid rgba(255,183,3,0.3);' ?>">
                <?= ($settings['mode'] ?? 'development') === 'production' ? '🟢 Production' : '🟡 Development' ?>
            </span>
        </div>
    </div>

    <!-- Status Banner -->
    <div id="conn-banner" style="display:none;margin-bottom:16px;"></div>

    <!-- ===== SECTION 1: API CREDENTIALS ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
        <div style="background:linear-gradient(135deg,var(--primary-bg),transparent);border-bottom:1px solid var(--border-color);padding:14px 20px;display:flex;align-items:center;gap:12px;">
            <div class="stat-icon red" style="width:34px;height:34px;font-size:0.95rem;margin-bottom:0;flex-shrink:0;"><i class="bi bi-key-fill"></i></div>
            <div>
                <div style="font-weight:700;font-size:var(--font-size-sm);font-family:var(--font-family);">Kredensial API Digiflazz</div>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);">Username dan API Key dari dashboard member Digiflazz</div>
            </div>
        </div>
        <div style="padding:24px;">
            <form id="form-api-settings" onsubmit="saveSettings(event)">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <!-- Username -->
                    <div>
                        <label class="ppob-label"><i class="bi bi-person-fill me-1" style="color:var(--primary);"></i>Username Digiflazz</label>
                        <input type="text" class="ppob-field" id="cfg-username"
                            value="<?= htmlspecialchars($settings['username'] ?? '') ?>"
                            placeholder="contoh: toko_alfarez" autocomplete="off" required>
                        <div class="ppob-hint">Username yang terdaftar di akun Digiflazz Anda.</div>
                    </div>
                    <!-- Mode -->
                    <div>
                        <label class="ppob-label"><i class="bi bi-toggles2 me-1" style="color:var(--warning);"></i>Mode Lingkungan</label>
                        <div class="ppob-select-wrap">
                            <select class="ppob-field" id="cfg-mode" onchange="updateModeBadge(this.value)">
                                <option value="development" <?= ($settings['mode'] ?? '') === 'development' ? 'selected' : '' ?>>🟡 Development (Sandbox)</option>
                                <option value="production" <?= ($settings['mode'] ?? '') === 'production' ? 'selected' : '' ?>>🟢 Production (Live)</option>
                            </select>
                            <i class="bi bi-chevron-down ppob-select-icon"></i>
                        </div>
                        <div class="ppob-hint">Dev: API Key <code>dev-xxxx</code> | Prod: API Key <code>prod-xxxx</code></div>
                    </div>
                </div>

                <!-- API Keys -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label class="ppob-label"><i class="bi bi-shield-lock-fill me-1" style="color:var(--danger);"></i>API Key (Development)</label>
                        <div style="position:relative;">
                            <input type="password" class="ppob-field" id="cfg-apikey-dev"
                                value="<?= htmlspecialchars($settings['api_key_dev'] ?? '') ?>"
                                placeholder="dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" style="padding-right:44px;">
                            <button type="button" onclick="togglePwd('cfg-apikey-dev',this)" class="ppob-eye-btn"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div>
                        <label class="ppob-label"><i class="bi bi-shield-lock-fill me-1" style="color:var(--success);"></i>API Key (Production)</label>
                        <div style="position:relative;">
                            <input type="password" class="ppob-field" id="cfg-apikey-prod"
                                value="<?= htmlspecialchars($settings['api_key_prod'] ?? '') ?>"
                                placeholder="prod-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" style="padding-right:44px;">
                            <button type="button" onclick="togglePwd('cfg-apikey-prod',this)" class="ppob-eye-btn"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                </div>
                <div class="ppob-hint mb-3" style="margin-top:-10px;">Dapatkan di: <strong>Dashboard Digiflazz &rarr; Profil &rarr; API Key</strong></div>

                <!-- Webhook Secret -->
                <div style="margin-bottom:20px;">
                    <label class="ppob-label"><i class="bi bi-broadcast me-1" style="color:var(--info);"></i>Webhook Secret <span style="font-size:10px;color:var(--text-muted);font-weight:500;">(Opsional)</span></label>
                    <div style="position:relative;">
                        <input type="password" class="ppob-field" id="cfg-webhook"
                            value="<?= htmlspecialchars($settings['webhook_secret'] ?? '') ?>"
                            placeholder="Isi dengan secret dari Dashboard Digiflazz" style="padding-right:44px;">
                        <button type="button" onclick="togglePwd('cfg-webhook',this)" class="ppob-eye-btn"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="ppob-hint">Digunakan untuk memvalidasi callback webhook dari Digiflazz. Atur di <strong>Digiflazz &rarr; Atur Koneksi &rarr; API &rarr; Webhook Secret</strong>.</div>
                </div>

                <!-- Transaction PIN -->
                <div style="margin-bottom:20px;">
                    <label class="ppob-label"><i class="bi bi-shield-lock me-1" style="color:var(--warning);"></i>PIN Transaksi PPOB <span style="font-size:10px;color:var(--text-muted);font-weight:500;">(Opsional)</span></label>
                    <div style="position:relative;">
                        <input type="password" class="ppob-field" id="cfg-pin"
                            value="<?= htmlspecialchars($settings['digiflazz_pin'] ?? '') ?>"
                            placeholder="Kosongkan jika tidak butuh PIN" style="padding-right:44px;">
                        <button type="button" onclick="togglePwd('cfg-pin',this)" class="ppob-eye-btn"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="ppob-hint">Jika diisi, setiap kali kasir melakukan transaksi PPOB akan dimintai PIN ini.</div>
                </div>

                <!-- Multifinance / Leasing Installment Display Toggle -->
                <div style="margin-bottom:24px;background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:14px 16px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div style="font-weight:700;font-size:var(--font-size-sm);color:var(--text-primary);"><i class="bi bi-credit-card-2-front me-2" style="color:var(--primary);"></i>Tampilkan Nomor Cicilan / Angsuran Multifinance</div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                Menampilkan nomor urutan cicilan (misal: "Ke-2" atau "Ke-19") pada struk, nota, dan pratinjau tagihan.
                            </div>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="cfg-show-installment" style="width:2.5em;height:1.3em;cursor:pointer;" <?= ($settings['show_installment_no'] ?? '0') === '1' ? 'checked' : '' ?>>
                        </div>
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);margin-top:8px;padding-top:8px;border-top:1px dashed var(--border-color);">
                        <i class="bi bi-info-circle me-1" style="color:var(--info);"></i>
                        <strong>Rekomendasi Dinonaktifkan:</strong> Beberapa biller/switching leasing (seperti OTO) mengembalikan urutan tagihan sistem (misal <code>002</code>) bukan urutan kredit sebenarnya. Jika nonaktif, struk tetap menampilkan <strong>Unit Kendaraan, No. Polisi, No. Rangka, Jatuh Tempo, dan Tenor</strong> tanpa label angsuran agar tidak ambigu.
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" id="btn-save-cfg" class="ppob-btn-primary" style="min-width:160px;">
                        <i class="bi bi-save me-1"></i> Simpan Pengaturan
                    </button>
                    <button type="button" id="btn-test-conn" onclick="testConnection()" class="ppob-btn-secondary">
                        <i class="bi bi-plug me-1"></i> Test Koneksi
                    </button>
                </div>
                <div id="cfg-status" style="display:none;margin-top:14px;"></div>
            </form>
        </div>
    </div>

    <!-- ===== SECTION 2: BALANCE & STATUS ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
        <div style="background:linear-gradient(135deg,var(--success-bg),transparent);border-bottom:1px solid var(--border-color);padding:14px 20px;display:flex;align-items:center;gap:12px;">
            <div class="stat-icon green" style="width:34px;height:34px;font-size:0.95rem;margin-bottom:0;flex-shrink:0;"><i class="bi bi-wallet2"></i></div>
            <div>
                <div style="font-weight:700;font-size:var(--font-size-sm);font-family:var(--font-family);">Saldo & Status Akun</div>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);">Cek saldo deposit dan status koneksi API secara real-time</div>
            </div>
        </div>
        <div style="padding:24px;">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px;">
                <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:14px;text-align:center;">
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:6px;">Saldo Deposit</div>
                    <div id="info-saldo" style="font-weight:800;font-size:var(--font-size-md);color:var(--primary);font-family:var(--font-family);">-</div>
                </div>
                <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:14px;text-align:center;">
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:6px;">Status API</div>
                    <div id="info-status" style="font-weight:700;font-size:var(--font-size-sm);">-</div>
                </div>
                <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:14px;text-align:center;">
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:6px;">Mode Aktif</div>
                    <div id="info-mode" style="font-weight:700;font-size:var(--font-size-xs);">-</div>
                </div>
                <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:14px;text-align:center;">
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:6px;">Terakhir Cek</div>
                    <div id="info-time" style="font-weight:600;font-size:var(--font-size-xs);color:var(--text-muted);">-</div>
                </div>
            </div>
            <button class="ppob-btn-secondary" id="btn-cek-saldo" onclick="cekSaldo()">
                <i class="bi bi-arrow-clockwise me-1"></i> Cek Saldo Sekarang
            </button>
        </div>
    </div>

    <!-- ===== SECTION 3: SYNC PRODUK ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
        <div style="background:linear-gradient(135deg,var(--info-bg),transparent);border-bottom:1px solid var(--border-color);padding:14px 20px;display:flex;align-items:center;gap:12px;">
            <div class="stat-icon blue" style="width:34px;height:34px;font-size:0.95rem;margin-bottom:0;flex-shrink:0;background:var(--info-bg);color:var(--info);"><i class="bi bi-cloud-arrow-down"></i></div>
            <div>
                <div style="font-weight:700;font-size:var(--font-size-sm);font-family:var(--font-family);">Sinkronisasi Daftar Produk & Harga</div>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);">Tarik daftar produk terbaru dari Digiflazz ke database lokal</div>
            </div>
        </div>
        <div style="padding:24px;">
            <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:12px 14px;margin-bottom:16px;font-size:var(--font-size-xs);color:var(--text-muted);line-height:1.6;">
                <i class="bi bi-info-circle-fill me-2" style="color:var(--info);"></i>
                Sinkronisasi mengunduh seluruh daftar produk dari <code style="background:var(--surface-3);padding:1px 5px;border-radius:4px;">api.digiflazz.com/v1/price-list</code> dan menyimpannya ke database lokal. Disarankan dilakukan setelah mengganti API Key atau secara berkala.
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="ppob-btn-primary" id="btn-sync-prepaid" onclick="syncProducts('prepaid')" style="background:var(--info);border-color:var(--info);">
                    <i class="bi bi-arrow-repeat me-1"></i> Sync Prabayar
                </button>
                <button class="ppob-btn-secondary" id="btn-sync-postpaid" onclick="syncProducts('postpaid')">
                    <i class="bi bi-arrow-repeat me-1"></i> Sync Pascabayar
                </button>
            </div>
            <div id="sync-status" style="display:none;margin-top:14px;"></div>
        </div>
    </div>

    <!-- ===== SECTION 4: WEBHOOK ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
        <div style="background:linear-gradient(135deg,rgba(139,92,246,0.12),transparent);border-bottom:1px solid var(--border-color);padding:14px 20px;display:flex;align-items:center;gap:12px;">
            <div class="stat-icon purple" style="width:34px;height:34px;font-size:0.95rem;margin-bottom:0;flex-shrink:0;"><i class="bi bi-broadcast"></i></div>
            <div>
                <div style="font-weight:700;font-size:var(--font-size-sm);font-family:var(--font-family);">Konfigurasi Webhook</div>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);">Notifikasi otomatis status transaksi dari Digiflazz ke sistem Anda</div>
            </div>
        </div>
        <div style="padding:24px;">
            <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:12px;">Daftarkan URL berikut ke panel Digiflazz (<strong>Atur Koneksi &rarr; API &rarr; Webhook URL</strong>) agar status transaksi diperbarui otomatis tanpa perlu refresh manual.</p>

            <!-- Webhook URL copy -->
            <div style="position:relative;margin-bottom:16px;">
                <input type="text" id="webhook-url"
                    value="<?= rtrim(BASE_URL, '/') ?>/api/ppob/webhook"
                    readonly
                    style="width:100%;padding:11px 44px 11px 14px;border:1.5px solid var(--border-color);border-radius:var(--radius-md);font-size:var(--font-size-xs);background:var(--surface-2);color:var(--text-primary);font-family:monospace;cursor:pointer;"
                    onclick="copyWebhook()" title="Klik untuk menyalin">
                <button type="button" onclick="copyWebhook()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:15px;padding:4px;transition:color var(--transition-fast);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                    <i class="bi bi-clipboard" id="copy-icon"></i>
                </button>
            </div>

            <!-- Webhook Headers Info -->
            <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:14px;font-size:var(--font-size-xs);">
                <div style="font-weight:700;margin-bottom:10px;font-family:var(--font-family);font-size:var(--font-size-sm);">
                    <i class="bi bi-shield-check me-1" style="color:var(--primary);"></i>Header Keamanan dari Digiflazz
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:10px;">
                        <div style="font-weight:700;color:var(--text-muted);font-size:9px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">X-Hub-Signature</div>
                        <div style="color:var(--text-secondary);">HMAC SHA1 dari request body. Divalidasi jika Webhook Secret diisi.</div>
                    </div>
                    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:10px;">
                        <div style="font-weight:700;color:var(--text-muted);font-size:9px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">X-Digiflazz-Event</div>
                        <div style="color:var(--text-secondary);"><code style="font-size:9px;">create</code> &middot; <code style="font-size:9px;">update</code> &middot; <code style="font-size:9px;">resend</code></div>
                    </div>
                    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:10px;">
                        <div style="font-weight:700;color:var(--text-muted);font-size:9px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">User-Agent (Prepaid)</div>
                        <div style="color:var(--text-secondary);"><code style="font-size:9px;">Digiflazz-Hookshot</code></div>
                    </div>
                    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:10px;">
                        <div style="font-weight:700;color:var(--text-muted);font-size:9px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">User-Agent (Postpaid)</div>
                        <div style="color:var(--text-secondary);"><code style="font-size:9px;">Digiflazz-Pasca-Hookshot</code></div>
                    </div>
                </div>
            </div>

            <!-- Server IP -->
            <div style="margin-top:14px;background:var(--warning-bg);border:1px solid rgba(255,183,3,0.25);border-radius:var(--radius-md);padding:12px 14px;font-size:var(--font-size-xs);">
                <i class="bi bi-exclamation-triangle-fill me-2" style="color:var(--warning);"></i>
                <strong>Whitelist IP Digiflazz:</strong> Daftarkan IP server Anda di <strong>Dashboard Digiflazz &rarr; Atur Koneksi &rarr; IP Development/Production</strong>.
                IP Server Anda: <strong id="server-ip" style="color:var(--primary);">Memuat...</strong>
            </div>
        </div>
    </div>

    <!-- ===== SECTION 5: API REFERENCE ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
        <div style="background:linear-gradient(135deg,rgba(var(--primary-rgb),0.08),transparent);border-bottom:1px solid var(--border-color);padding:14px 20px;display:flex;align-items:center;gap:12px;">
            <div class="stat-icon blue" style="width:34px;height:34px;font-size:0.95rem;margin-bottom:0;flex-shrink:0;"><i class="bi bi-code-slash"></i></div>
            <div>
                <div style="font-weight:700;font-size:var(--font-size-sm);font-family:var(--font-family);">Referensi API Digiflazz</div>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);">Endpoint dan format signature untuk integrasi API</div>
            </div>
        </div>
        <div style="padding:24px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:12px;">
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:8px;">Endpoint Utama</div>
                    <code style="font-size:var(--font-size-xs);color:var(--primary);">POST https://api.digiflazz.com/v1/</code>
                    <ul style="margin:8px 0 0;padding-left:16px;font-size:var(--font-size-xs);color:var(--text-secondary);line-height:2;">
                        <li><code>cek-saldo</code> &mdash; Cek saldo deposit</li>
                        <li><code>price-list</code> &mdash; Daftar produk & harga</li>
                        <li><code>transaction</code> &mdash; Transaksi prabayar</li>
                    </ul>
                </div>
                <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:12px;">
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:8px;">Format Signature (Sign)</div>
                    <code style="font-size:var(--font-size-xs);color:var(--success);">MD5(username + apiKey + "deposit")</code>
                    <div style="margin-top:8px;font-size:var(--font-size-xs);color:var(--text-muted);line-height:1.6;">
                        Gunakan API Key sesuai mode:<br>
                        Dev: <code>dev-xxxx</code> | Prod: <code>prod-xxxx</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== DANGER ZONE ===== -->
    <div class="card mb-4" style="border:1px solid rgba(230,57,70,0.25);border-radius:var(--radius-lg);overflow:hidden;">
        <div style="background:var(--danger-bg);border-bottom:1px solid rgba(230,57,70,0.2);padding:12px 20px;">
            <div style="font-weight:700;color:var(--danger);font-size:var(--font-size-sm);font-family:var(--font-family);">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Zona Berbahaya
            </div>
        </div>
        <div style="padding:16px 20px;background:var(--bg-card);">
            <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:12px;">Tindakan berikut tidak dapat dibatalkan. Pastikan Anda memahami konsekuensinya.</div>
            <button onclick="confirmClearSettings()" class="ppob-btn-danger">
                <i class="bi bi-trash me-1"></i> Hapus Semua Kredensial API
            </button>
        </div>
    </div>
</div>

<style>
    .card-custom { background: var(--bg-card); }
    .ppob-label {
        display: block;
        font-size: var(--font-size-xs);
        font-weight: 700;
        color: var(--text-secondary);
        font-family: var(--font-family);
        margin-bottom: 6px;
    }
    .ppob-field {
        width: 100%;
        padding: 11px 14px;
        background: var(--bg-input);
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: var(--font-size-sm);
        font-family: var(--font-family);
        transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        box-sizing: border-box;
    }
    .ppob-field:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-bg);
    }
    .ppob-field::placeholder { color: var(--text-muted); }
    .ppob-field option { background: var(--bg-card); color: var(--text-primary); }
    .ppob-hint {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 5px;
        font-family: var(--font-family);
    }
    .ppob-select-wrap { position: relative; }
    .ppob-select-icon {
        position: absolute;
        right: 12px; top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: var(--text-muted);
        font-size: 12px;
    }
    .ppob-eye-btn {
        position: absolute;
        right: 10px; top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 16px;
        padding: 4px;
        transition: color var(--transition-fast);
        line-height: 1;
    }
    .ppob-eye-btn:hover { color: var(--primary); }
    .ppob-btn-primary {
        display: inline-flex;
        align-items: center;
        background: var(--gradient-primary);
        color: #fff;
        border: none;
        border-radius: var(--radius-md);
        padding: 11px 20px;
        font-weight: 700;
        font-size: var(--font-size-sm);
        font-family: var(--font-family);
        cursor: pointer;
        transition: opacity var(--transition-fast), transform var(--transition-fast);
    }
    .ppob-btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
    .ppob-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .ppob-btn-secondary {
        display: inline-flex;
        align-items: center;
        background: var(--surface-2);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 10px 20px;
        font-weight: 600;
        font-size: var(--font-size-sm);
        font-family: var(--font-family);
        cursor: pointer;
        transition: background var(--transition-fast);
    }
    .ppob-btn-secondary:hover { background: var(--surface-3); }
    .ppob-btn-danger {
        display: inline-flex;
        align-items: center;
        background: var(--danger-bg);
        color: var(--danger);
        border: 1px solid rgba(230,57,70,0.3);
        border-radius: var(--radius-md);
        padding: 9px 18px;
        font-weight: 700;
        font-size: var(--font-size-sm);
        font-family: var(--font-family);
        cursor: pointer;
        transition: background var(--transition-fast);
    }
    .ppob-btn-danger:hover { background: rgba(230,57,70,0.2); }
    .ppob-status-box {
        border-radius: var(--radius-md);
        padding: 12px 14px;
        font-size: var(--font-size-sm);
        font-weight: 600;
        font-family: var(--font-family);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    @media (max-width: 600px) {
        div[style*="grid-template-columns:1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        div[style*="grid-template-columns:repeat(4"] {
            grid-template-columns: repeat(2,1fr) !important;
        }
    }
</style>

<script>
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const ico = btn ? btn.querySelector('i') : null;
    if (!inp) return;
    if (inp.type === 'password') {
        inp.type = 'text';
        if (ico) ico.classList.replace('bi-eye','bi-eye-slash');
    } else {
        inp.type = 'password';
        if (ico) ico.classList.replace('bi-eye-slash','bi-eye');
    }
}

function updateModeBadge(val) {
    const badge = document.getElementById('mode-badge');
    if (!badge) return;
    if (val === 'production') {
        badge.style.cssText = 'font-size:11px;padding:5px 12px;border-radius:20px;font-weight:700;font-family:var(--font-family);background:var(--success-bg);color:var(--success);border:1px solid rgba(46,196,182,0.3);';
        badge.textContent = '🟢 Production';
    } else {
        badge.style.cssText = 'font-size:11px;padding:5px 12px;border-radius:20px;font-weight:700;font-family:var(--font-family);background:var(--warning-bg);color:var(--warning);border:1px solid rgba(255,183,3,0.3);';
        badge.textContent = '🟡 Development';
    }
}

function showStatus(id, type, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    const configs = {
        success: { bg: 'var(--success-bg)', color: 'var(--success)', border: 'rgba(46,196,182,0.3)', icon: 'bi-check-circle-fill' },
        danger:  { bg: 'var(--danger-bg)',  color: 'var(--danger)',  border: 'rgba(230,57,70,0.3)',  icon: 'bi-x-circle-fill' },
        info:    { bg: 'var(--info-bg)',    color: 'var(--info)',    border: 'rgba(76,201,240,0.3)',  icon: 'bi-info-circle-fill' },
    };
    const c = configs[type] || configs.info;
    el.style.display = 'block';
    el.innerHTML = `<div class="ppob-status-box" style="background:${c.bg};color:${c.color};border:1px solid ${c.border};"><i class="bi ${c.icon}"></i>${msg}</div>`;
}

function copyWebhook() {
    const el = document.getElementById('webhook-url');
    if (!el) return;
    navigator.clipboard.writeText(el.value).then(() => {
        const ico = document.getElementById('copy-icon');
        if (ico) {
            ico.classList.replace('bi-clipboard','bi-check-lg');
            setTimeout(() => ico.classList.replace('bi-check-lg','bi-clipboard'), 2000);
        }
    });
}

function saveSettings(e) {
    e.preventDefault();
    sendSettings();
}

async function sendSettings() {
    const btn = document.getElementById('btn-save-cfg');
    if (btn) {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        btn.disabled = true;
    }
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/settings', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                username: document.getElementById('cfg-username').value,
                api_key_dev: document.getElementById('cfg-apikey-dev').value,
                api_key_prod: document.getElementById('cfg-apikey-prod').value,
                webhook_secret: document.getElementById('cfg-webhook').value,
                mode: document.getElementById('cfg-mode').value,
                pin: document.getElementById('cfg-pin').value,
                show_installment_no: document.getElementById('cfg-show-installment') && document.getElementById('cfg-show-installment').checked ? '1' : '0'
            })
        });
        const data = await res.json();
        if (data.success) {
            showStatus('cfg-status','success','Pengaturan berhasil disimpan!');
            setTimeout(() => { location.reload(); }, 1200);
        } else {
            showStatus('cfg-status','danger', data.message || 'Gagal menyimpan.');
        }
    } catch (err) {
        showStatus('cfg-status','danger','Koneksi error: ' + err.message);
    } finally {
        if (btn) {
            btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Pengaturan';
            btn.disabled = false;
        }
    }
}

// Test Connection
async function testConnection() {
    const btn = document.getElementById('btn-test-conn');
    if (btn) {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';
        btn.disabled = true;
    }
    showStatus('cfg-status','info','Menghubungkan ke API Digiflazz...');
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/balance');
        const data = await res.json();
        if (data.success) {
            const saldo = parseInt(data.data?.deposit || data.deposit || 0).toLocaleString('id-ID');
            showStatus('cfg-status','success',`Koneksi berhasil! Saldo deposit: <strong>Rp ${saldo}</strong>`);
        } else {
            showStatus('cfg-status','danger', data.message || 'Koneksi gagal. Periksa Username & API Key.');
        }
    } catch (err) {
        showStatus('cfg-status','danger','Error: ' + err.message);
    } finally {
        if (btn) {
            btn.innerHTML = '<i class="bi bi-plug me-1"></i> Test Koneksi';
            btn.disabled = false;
        }
    }
}

// Cek Saldo
async function cekSaldo() {
    const btn = document.getElementById('btn-cek-saldo');
    if (btn) {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memeriksa...';
        btn.disabled = true;
    }
    const infoSaldo = document.getElementById('info-saldo');
    const infoStatus = document.getElementById('info-status');
    if (infoSaldo) infoSaldo.textContent = '...';
    if (infoStatus) infoStatus.textContent = '...';
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/balance');
        const data = await res.json();
        if (data.success) {
            const saldo = parseInt(data.data?.deposit || data.deposit || 0).toLocaleString('id-ID');
            if (infoSaldo) infoSaldo.innerHTML = `<span style="color:var(--success);">Rp ${saldo}</span>`;
            if (infoStatus) infoStatus.innerHTML = `<span style="color:var(--success);"><i class="bi bi-circle-fill" style="font-size:7px;"></i> Terhubung</span>`;
        } else {
            if (infoSaldo) infoSaldo.innerHTML = `<span style="color:var(--danger);">Error</span>`;
            if (infoStatus) infoStatus.innerHTML = `<span style="color:var(--danger);"><i class="bi bi-circle-fill" style="font-size:7px;"></i> Gagal</span>`;
        }
        const mode = document.getElementById('cfg-mode').value;
        const infoMode = document.getElementById('info-mode');
        const infoTime = document.getElementById('info-time');
        if (infoMode) infoMode.textContent = mode === 'production' ? '🟢 Production' : '🟡 Development';
        if (infoTime) infoTime.textContent = new Date().toLocaleTimeString('id-ID');
    } catch (err) {
        if (infoSaldo) infoSaldo.innerHTML = `<span style="color:var(--danger);">Offline</span>`;
        if (infoStatus) infoStatus.innerHTML = `<span style="color:var(--text-muted);">-</span>`;
    } finally {
        if (btn) {
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Cek Saldo Sekarang';
            btn.disabled = false;
        }
    }
}

// Sync Products
async function syncProducts(type) {
    const btnId = type === 'prepaid' ? 'btn-sync-prepaid' : 'btn-sync-postpaid';
    const btn = document.getElementById(btnId);
    const label = type === 'prepaid' ? 'Prabayar' : 'Pascabayar';
    if (btn) {
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Sinkronisasi ${label}...`;
        btn.disabled = true;
    }
    showStatus('sync-status','info',`Mengunduh daftar produk ${label.toLowerCase()} dari Digiflazz. Harap tunggu...`);
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/sync-prices', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({type: type})
        });
        const data = await res.json();
        if (data.success) {
            showStatus('sync-status','success',`Sinkronisasi ${label} berhasil! Daftar produk sudah diperbarui.`);
        } else {
            showStatus('sync-status','danger', data.message || 'Sinkronisasi gagal.');
        }
    } catch (err) {
        showStatus('sync-status','danger','Error: ' + err.message);
    } finally {
        if (btn) {
            btn.innerHTML = `<i class="bi bi-arrow-repeat me-1"></i> Sync ${label}`;
            btn.disabled = false;
        }
    }
}

// Clear Credentials
async function confirmClearSettings() {
    const ok = await AppModal.confirm(
        '⚠️ Hapus Semua Kredensial PPOB',
        'Semua kredensial API Digiflazz akan dihapus. Layanan PPOB akan berhenti berfungsi hingga Anda mengisi ulang. Lanjutkan?',
        'Ya, Hapus Semua', 'var(--danger)'
    );
    if (!ok) return;
    fetch('<?= BASE_URL ?>api/ppob/settings', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({username:'',api_key_dev:'',api_key_prod:'',webhook_secret:'',mode:'development'})
    }).then(() => {
        document.getElementById('cfg-username').value = '';
        document.getElementById('cfg-apikey-dev').value = '';
        document.getElementById('cfg-apikey-prod').value = '';
        document.getElementById('cfg-webhook').value = '';
        document.getElementById('cfg-mode').value = 'development';
        updateModeBadge('development');
        showStatus('cfg-status','info','Semua kredensial API telah dihapus.');
    });
}

// Load server IP
fetch('https://api.ipify.org?format=json')
    .then(r => r.json())
    .then(d => {
        const el = document.getElementById('server-ip');
        if (el) el.textContent = d.ip;
    })
    .catch(() => {
        const el = document.getElementById('server-ip');
        if (el) el.textContent = 'Tidak dapat dideteksi';
    });

// Auto cek saldo on load
document.addEventListener('DOMContentLoaded', () => {
    const apiKeyDev = document.getElementById('cfg-apikey-dev');
    const apiKeyProd = document.getElementById('cfg-apikey-prod');
    if ((apiKeyDev && apiKeyDev.value.length > 5) || (apiKeyProd && apiKeyProd.value.length > 5)) {
        cekSaldo();
    }
});
</script>
