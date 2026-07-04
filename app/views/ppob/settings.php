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
        <div style="padding:20px;">
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

                <!-- API Key -->
                <div style="margin-bottom:16px;">
                    <label class="ppob-label"><i class="bi bi-shield-lock-fill me-1" style="color:var(--danger);"></i>API Key</label>
                    <div style="position:relative;">
                        <input type="password" class="ppob-field" id="cfg-apikey"
                            value="<?= htmlspecialchars($settings['api_key'] ?? '') ?>"
                            placeholder="dev-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required style="padding-right:44px;">
                        <button type="button" onclick="togglePwd('cfg-apikey',this)" class="ppob-eye-btn"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="ppob-hint">Dapatkan di: <strong>Dashboard Digiflazz → Profil → API Key</strong></div>
                </div>

                <!-- Webhook Secret -->
                <div style="margin-bottom:20px;">
                    <label class="ppob-label"><i class="bi bi-broadcast me-1" style="color:var(--info);"></i>Webhook Secret <span style="font-size:10px;color:var(--text-muted);font-weight:500;">(Opsional)</span></label>
                    <div style="position:relative;">
                        <input type="password" class="ppob-field" id="cfg-webhook"
                            value="<?= htmlspecialchars($settings['webhook_secret'] ?? '') ?>"
                            placeholder="Isi dengan secret dari Dashboard Digiflazz" style="padding-right:44px;">
                        <button type="button" onclick="togglePwd('cfg-webhook',this)" class="ppob-eye-btn"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="ppob-hint">Digunakan untuk memvalidasi callback webhook dari Digiflazz. Atur di <strong>Digiflazz → Atur Koneksi → API → Webhook Secret</strong>.</div>
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
        <div style="padding:20px;">
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
        <div style="padding:20px;">
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

    <!-- ===== SECTION 3.5: MARKUP HARGA ===== -->
    <div class="card card-custom mb-4" style="border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
        <div style="background:linear-gradient(135deg,rgba(249,115,22,0.1),transparent);border-bottom:1px solid var(--border-color);padding:14px 20px;display:flex;align-items:center;gap:12px;">
            <div class="stat-icon" style="width:34px;height:34px;font-size:0.95rem;margin-bottom:0;flex-shrink:0;background:rgba(249,115,22,0.12);color:#f97316;"><i class="bi bi-percent"></i></div>
            <div>
                <div style="font-weight:700;font-size:var(--font-size-sm);font-family:var(--font-family);">Pengaturan Markup Harga</div>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);">Atur keuntungan otomatis per kategori produk. Diterapkan ke semua produk setelah disimpan.</div>
            </div>
        </div>
        <div style="padding:20px;">
            <div style="background:var(--info-bg);border:1px solid rgba(76,201,240,0.25);border-radius:var(--radius-md);padding:10px 14px;margin-bottom:16px;font-size:var(--font-size-xs);color:var(--info);">
                <i class="bi bi-info-circle-fill me-1"></i>
                Harga jual = Harga modal Digiflazz + Markup. Markup <strong>fixed</strong> = Rp nominal tetap. Markup <strong>persentase</strong> = % dari harga modal.
            </div>

            <div id="markup-rules-table">
                <?php
                $categoryLabels = [
                    'pulsa'        => ['label' => 'Pulsa', 'icon' => 'bi-phone', 'color' => '#ef4444'],
                    'data'         => ['label' => 'Paket Data', 'icon' => 'bi-wifi', 'color' => '#3b82f6'],
                    'pln'          => ['label' => 'Token PLN', 'icon' => 'bi-lightning-charge', 'color' => '#eab308'],
                    'ewallet'      => ['label' => 'E-Wallet', 'icon' => 'bi-wallet', 'color' => '#06b6d4'],
                    'game'         => ['label' => 'Voucher Game', 'icon' => 'bi-controller', 'color' => '#8b5cf6'],
                    'bpjs'         => ['label' => 'BPJS', 'icon' => 'bi-hospital', 'color' => '#10b981'],
                    'multifinance' => ['label' => 'Angsuran/Kredit', 'icon' => 'bi-building', 'color' => '#f97316'],
                    'bank'         => ['label' => 'Transfer Bank', 'icon' => 'bi-bank', 'color' => '#64748b'],
                ];
                $rulesByCategory = [];
                foreach (($markupRules ?? []) as $rule) {
                    $rulesByCategory[$rule['category']] = $rule;
                }
                ?>
                <div style="display:grid;gap:10px;">
                    <?php foreach ($categoryLabels as $catKey => $catInfo): ?>
                    <?php $rule = $rulesByCategory[$catKey] ?? ['markup_type' => 'fixed', 'markup_value' => 2000]; ?>
                    <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:12px 14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;gap:8px;min-width:150px;">
                            <i class="<?= $catInfo['icon'] ?>" style="color:<?= $catInfo['color'] ?>;font-size:1rem;"></i>
                            <span style="font-weight:700;font-size:var(--font-size-xs);color:var(--text-primary);font-family:var(--font-family);"><?= $catInfo['label'] ?></span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:200px;">
                            <div class="ppob-select-wrap" style="width:130px;">
                                <select class="ppob-field" id="markup-type-<?= $catKey ?>" style="padding:8px 10px;font-size:12px;">
                                    <option value="fixed" <?= $rule['markup_type'] === 'fixed' ? 'selected' : '' ?>>Rp (Fixed)</option>
                                    <option value="percentage" <?= $rule['markup_type'] === 'percentage' ? 'selected' : '' ?>>% (Persentase)</option>
                                </select>
                                <i class="bi bi-chevron-down ppob-select-icon"></i>
                            </div>
                            <div style="position:relative;flex:1;">
                                <input type="number" class="ppob-field" id="markup-val-<?= $catKey ?>"
                                    value="<?= number_format((float)$rule['markup_value'], 0, '.', '') ?>"
                                    min="0" step="100"
                                    style="padding:8px 10px;font-size:12px;text-align:right;"
                                    placeholder="0">
                            </div>
                            <div style="font-size:10px;color:var(--text-muted);white-space:nowrap;" id="markup-preview-<?= $catKey ?>">
                                +Rp <?= number_format((float)$rule['markup_value'], 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="margin-top:16px;display:flex;gap:10px;align-items:center;">
                <button class="ppob-btn-primary" id="btn-save-markup" onclick="saveMarkupRules()" style="min-width:180px;">
                    <i class="bi bi-check2-circle me-1"></i> Simpan & Terapkan Semua
                </button>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);">Markup akan langsung diperbarui ke semua produk.</div>
            </div>
            <div id="markup-status" style="display:none;margin-top:12px;"></div>
        </div>
    </div>

            <div class="mt-4" style="border-top:1px dashed var(--border-color);padding-top:16px;">
                <h6 style="font-size:var(--font-size-sm);font-weight:700;margin-bottom:10px;">Manajemen Harga Jual Spesifik (Per Produk)</h6>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:12px;">Cari produk untuk mengatur harga jualnya secara manual (mengabaikan markup otomatis).</div>
                
                <div class="d-flex gap-2 mb-3">
                    <input type="text" id="search-product-sku" class="ppob-field" placeholder="Cari nama produk atau SKU..." style="flex:1;">
                    <button class="ppob-btn-secondary" onclick="searchCustomPriceProduct()">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
                
                <div id="custom-price-results" style="display:none;background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-md);max-height:300px;overflow-y:auto;">
                    <!-- Results populated via JS -->
                </div>
            </div>
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
        <div style="padding:20px;">
            <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:12px;">Daftarkan URL berikut ke panel Digiflazz (<strong>Atur Koneksi → API → Webhook URL</strong>) agar status transaksi diperbarui otomatis tanpa perlu refresh manual.</p>

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
                        <div style="color:var(--text-secondary);"><code style="font-size:9px;">create</code> · <code style="font-size:9px;">update</code> · <code style="font-size:9px;">resend</code></div>
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
                <strong>Whitelist IP Digiflazz:</strong> Daftarkan IP server Anda di <strong>Dashboard Digiflazz → Atur Koneksi → IP Development/Production</strong>.
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
        <div style="padding:20px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:12px;">
                    <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);margin-bottom:8px;">Endpoint Utama</div>
                    <code style="font-size:var(--font-size-xs);color:var(--primary);">POST https://api.digiflazz.com/v1/</code>
                    <ul style="margin:8px 0 0;padding-left:16px;font-size:var(--font-size-xs);color:var(--text-secondary);line-height:2;">
                        <li><code>cek-saldo</code> — Cek saldo deposit</li>
                        <li><code>price-list</code> — Daftar produk & harga</li>
                        <li><code>transaction</code> — Transaksi prabayar</li>
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
    const ico = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.classList.replace('bi-eye','bi-eye-slash');
    } else {
        inp.type = 'password';
        ico.classList.replace('bi-eye-slash','bi-eye');
    }
}

function updateModeBadge(val) {
    const badge = document.getElementById('mode-badge');
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
    const val = document.getElementById('webhook-url').value;
    navigator.clipboard.writeText(val).then(() => {
        const ico = document.getElementById('copy-icon');
        ico.classList.replace('bi-clipboard','bi-check-lg');
        setTimeout(() => ico.classList.replace('bi-check-lg','bi-clipboard'), 2000);
    });
}

// Save Settings
async function saveSettings(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-cfg');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    btn.disabled = true;
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/settings', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                username: document.getElementById('cfg-username').value,
                api_key: document.getElementById('cfg-apikey').value,
                webhook_secret: document.getElementById('cfg-webhook').value,
                mode: document.getElementById('cfg-mode').value
            })
        });
        const data = await res.json();
        if (data.success) {
            showStatus('cfg-status','success','Pengaturan berhasil disimpan!');
            setTimeout(() => document.getElementById('cfg-status').style.display='none', 4000);
        } else {
            showStatus('cfg-status','danger', data.message || 'Gagal menyimpan.');
        }
    } catch (err) {
        showStatus('cfg-status','danger','Koneksi error: ' + err.message);
    } finally {
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Pengaturan';
        btn.disabled = false;
    }
}

// Test Connection
async function testConnection() {
    const btn = document.getElementById('btn-test-conn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';
    btn.disabled = true;
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
        btn.innerHTML = '<i class="bi bi-plug me-1"></i> Test Koneksi';
        btn.disabled = false;
    }
}

// Cek Saldo
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
            const saldo = parseInt(data.data?.deposit || data.deposit || 0).toLocaleString('id-ID');
            document.getElementById('info-saldo').innerHTML = `<span style="color:var(--success);">Rp ${saldo}</span>`;
            document.getElementById('info-status').innerHTML = `<span style="color:var(--success);"><i class="bi bi-circle-fill" style="font-size:7px;"></i> Terhubung</span>`;
        } else {
            document.getElementById('info-saldo').innerHTML = `<span style="color:var(--danger);">Error</span>`;
            document.getElementById('info-status').innerHTML = `<span style="color:var(--danger);"><i class="bi bi-circle-fill" style="font-size:7px;"></i> Gagal</span>`;
        }
        const mode = document.getElementById('cfg-mode').value;
        document.getElementById('info-mode').textContent = mode === 'production' ? '🟢 Production' : '🟡 Development';
        document.getElementById('info-time').textContent = new Date().toLocaleTimeString('id-ID');
    } catch (err) {
        document.getElementById('info-saldo').innerHTML = `<span style="color:var(--danger);">Offline</span>`;
        document.getElementById('info-status').innerHTML = `<span style="color:var(--text-muted);">-</span>`;
    } finally {
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Cek Saldo Sekarang';
        btn.disabled = false;
    }
}

// Sync Products
async function syncProducts(type) {
    const btnId = type === 'prepaid' ? 'btn-sync-prepaid' : 'btn-sync-postpaid';
    const btn = document.getElementById(btnId);
    const label = type === 'prepaid' ? 'Prabayar' : 'Pascabayar';
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Sinkronisasi ${label}...`;
    btn.disabled = true;
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
        btn.innerHTML = `<i class="bi bi-arrow-repeat me-1"></i> Sync ${label}`;
        btn.disabled = false;
    }
}

// Clear Credentials
function confirmClearSettings() {
    if (!confirm('⚠️ Yakin ingin menghapus semua kredensial API Digiflazz?\nLayanan PPOB akan berhenti berfungsi hingga Anda mengisi ulang.')) return;
    fetch('<?= BASE_URL ?>api/ppob/settings', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({username:'',api_key:'',webhook_secret:'',mode:'development'})
    }).then(() => {
        document.getElementById('cfg-username').value = '';
        document.getElementById('cfg-apikey').value = '';
        document.getElementById('cfg-webhook').value = '';
        document.getElementById('cfg-mode').value = 'development';
        updateModeBadge('development');
        showStatus('cfg-status','info','Semua kredensial API telah dihapus.');
    });
}

// Load server IP
fetch('https://api.ipify.org?format=json')
    .then(r => r.json())
    .then(d => document.getElementById('server-ip').textContent = d.ip)
    .catch(() => document.getElementById('server-ip').textContent = 'Tidak dapat dideteksi');

// Auto cek saldo on load
document.addEventListener('DOMContentLoaded', () => {
    const apiKey = document.getElementById('cfg-apikey').value;
    if (apiKey.length > 5) cekSaldo();
});

// Save Markup Rules
async function saveMarkupRules() {
    const btn = document.getElementById('btn-save-markup');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    btn.disabled = true;
    const categories = ['pulsa','data','pln','ewallet','game','bpjs','multifinance','bank'];
    const rules = categories.map(cat => ({
        category: cat,
        markup_type: document.getElementById(`markup-type-${cat}`)?.value || 'fixed',
        markup_value: parseFloat(document.getElementById(`markup-val-${cat}`)?.value || 0)
    }));
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/markup-rules', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({rules})
        });
        const data = await res.json();
        if (data.success) {
            showStatus('markup-status','success','Markup berhasil disimpan dan diterapkan ke semua produk!');
            setTimeout(() => document.getElementById('markup-status').style.display='none', 5000);
        } else {
            showStatus('markup-status','danger', data.message || 'Gagal menyimpan markup.');
        }
    } catch(err) {
        showStatus('markup-status','danger','Error: ' + err.message);
    } finally {
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan & Terapkan Semua';
        btn.disabled = false;
    }
}

// Custom Price Search
async function searchCustomPriceProduct() {
    const q = document.getElementById('search-product-sku').value;
    if (!q) return;
    const resDiv = document.getElementById('custom-price-results');
    resDiv.style.display = 'block';
    resDiv.innerHTML = '<div style="padding:16px;text-align:center;font-size:12px;"><span class="spinner-border spinner-border-sm"></span> Mencari...</div>';
    
    try {
        const res = await fetch(`<?= BASE_URL ?>api/ppob/products/search?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        if (data.success && data.data.length > 0) {
            let html = '<div style="display:grid;gap:1px;background:var(--border-color);">';
            data.data.forEach(p => {
                const isCustom = parseInt(p.is_custom_price) === 1;
                const modal = parseInt(p.seller_price).toLocaleString('id-ID');
                html += `
                <div style="background:var(--surface-2);padding:12px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:200px;">
                        <div style="font-weight:700;font-size:12px;">${p.product_name}</div>
                        <div style="font-size:10px;color:var(--text-muted);">SKU: ${p.buyer_sku_code} | Modal: Rp ${modal}</div>
                        ${isCustom ? '<span class="badge bg-warning text-dark" style="font-size:8px;">Harga Manual</span>' : '<span class="badge bg-info" style="font-size:8px;">Auto Markup</span>'}
                    </div>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <span style="font-size:12px;font-weight:600;">Rp</span>
                        <input type="number" id="cp-${p.buyer_sku_code}" class="ppob-field" value="${p.sell_price}" style="width:100px;padding:6px;font-size:12px;" ${!isCustom ? 'placeholder="Auto"' : ''}>
                        <button onclick="saveCustomPrice('${p.buyer_sku_code}')" class="btn btn-sm btn-primary" style="font-size:11px;padding:6px 10px;"><i class="bi bi-save"></i></button>
                        ${isCustom ? `<button onclick="resetCustomPrice('${p.buyer_sku_code}')" class="btn btn-sm btn-outline-danger" style="font-size:11px;padding:6px 10px;" title="Reset ke Auto Markup"><i class="bi bi-arrow-counterclockwise"></i></button>` : ''}
                    </div>
                </div>`;
            });
            html += '</div>';
            resDiv.innerHTML = html;
        } else {
            resDiv.innerHTML = '<div style="padding:16px;text-align:center;font-size:12px;color:var(--text-muted);">Produk tidak ditemukan</div>';
        }
    } catch (e) {
        resDiv.innerHTML = '<div style="padding:16px;text-align:center;font-size:12px;color:var(--danger);">Error koneksi pencarian</div>';
    }
}

async function saveCustomPrice(sku) {
    const val = document.getElementById(`cp-${sku}`).value;
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/custom-price', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({sku: sku, sell_price: val})
        });
        const data = await res.json();
        if (data.success) {
            alert('Harga jual custom berhasil disimpan!');
            searchCustomPriceProduct(); // reload
        } else {
            alert('Gagal: ' + data.message);
        }
    } catch(e) { alert('Error: ' + e.message); }
}

async function resetCustomPrice(sku) {
    if (!confirm('Kembalikan ke Auto Markup?')) return;
    try {
        const res = await fetch('<?= BASE_URL ?>api/ppob/custom-price/reset', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({sku: sku})
        });
        const data = await res.json();
        if (data.success) {
            searchCustomPriceProduct(); // reload
        }
    } catch(e) { alert('Error: ' + e.message); }
}
</script>
