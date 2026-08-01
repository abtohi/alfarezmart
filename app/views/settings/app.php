<?php
/**
 * @var string $csrfToken
 * @var string $aiModel
 * @var string $aiApiKey
 * @var string $aiPrompt
 * @var string $storeRadius
 * @var string $storeLat
 * @var string $storeLng
 * @var string $aiChatEnabled
 * @var string $aiChatModel
 * @var string $aiChatApiKey
 * @var array $currentUser
 */
?>
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
        
        <!-- SECTION 1: AI INVOICE SCANNER -->
        <form id="ai-settings-form">
            <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
                <div style="font-weight:600; margin-bottom:16px; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-receipt-cutoff" style="color:var(--info);"></i> 1. AI Invoice Scanner
                </div>

                <!-- Model Selector (Card-based) -->
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.05em;">
                        <i class="bi bi-cpu-fill" style="color:var(--primary); margin-right:4px;"></i> Model AI Scanner
                    </label>

                    <input type="hidden" id="ai_model" name="ai_model" value="<?= htmlspecialchars($aiModel ?? 'openrouter/auto') ?>">

                    <div class="custom-dropdown" id="aiModelDropdown" style="position:relative; margin-bottom:16px;">
                        <div class="dropdown-selected" onclick="toggleModelDropdown()" style="display:flex; align-items:center; justify-content:space-between; padding:12px; background:var(--bg-primary); border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div id="selectedModelIcon" class="model-card-icon" style="background:linear-gradient(135deg,#5c5c5c,#2c2c2c);">A</div>
                                <div>
                                    <div id="selectedModelName" style="font-weight:600; color:var(--text-primary); font-size:13px;">Auto (Default)</div>
                                    <div id="selectedModelId" style="font-size:11px; color:var(--text-muted);">openrouter/auto</div>
                                </div>
                            </div>
                            <i class="bi bi-chevron-down" style="color:var(--text-muted);"></i>
                        </div>
                        
                        <div class="dropdown-list" id="aiModelList" style="display:none; position:absolute; top:100%; left:0; right:0; background:var(--bg-primary); border:1px solid var(--border-color); border-radius:var(--radius-sm); margin-top:4px; z-index:100; box-shadow:0 4px 12px rgba(0,0,0,0.15); max-height:300px; display:flex; flex-direction:column; overflow:hidden; opacity:0; visibility:hidden; transition:opacity 0.2s, visibility 0.2s;">
                            <div style="padding:10px; border-bottom:1px solid var(--border-color); background:var(--surface-1);">
                                <input type="text" id="aiModelSearch" placeholder="Cari model AI..." onkeyup="filterModels()" style="width:100%; padding:8px 12px; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-primary); color:var(--text-primary); font-size:13px;">
                            </div>
                            <div id="aiModelItems" style="overflow-y:auto; flex:1;">
                                <!-- Model items injected via JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">API Key Scanner (Google Gemini / OpenRouter)</label>
                    <input id="ai_api_key" name="ai_api_key" type="password" value="<?= htmlspecialchars($aiApiKey ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="AIzaSy... (Google Gemini) atau sk-or-v1-... (OpenRouter)" />
                </div>

                <!-- Google Gemini Free Key Banner -->
                <div style="padding:12px 14px; border-radius:8px; margin-bottom:16px; background:rgba(66,133,244,0.1); border:1px solid rgba(66,133,244,0.3); font-size:12px; color:var(--text-primary); line-height:1.5;">
                    <div style="font-weight:700; color:#4285F4; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                        <i class="bi bi-stars"></i> Rekomendasi 100% Gratis Selamanya (Unlimited 21.600 scan/hari):
                    </div>
                    Agar <strong>100% GRATIS tanpa biaya / deposit OpenRouter</strong>, gunakan <strong>Google Gemini API Key</strong> resmi gratis dari Google AI Studio.<br>
                    👉 <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color:#4285F4; text-decoration:underline; font-weight:bold;">Klik di sini untuk mengambil API Key Gemini Gratis (Google AI Studio)</a>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">System Prompt Scanner</label>
                    <textarea id="ai_invoice_prompt" name="ai_invoice_prompt" rows="3" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm); resize:none;" placeholder="Prompt untuk menganalisa nota" required><?= htmlspecialchars($aiPrompt ?? '') ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn-primary-custom" style="width:100%; padding:12px; font-weight:600; margin-bottom:24px;">💾 Simpan Pengaturan Scanner</button>
        </form>

        <!-- SECTION 2: AI CHAT ASSISTANT -->
        <?php 
            $settingModel = new SettingModel();
            $aiChatEnabled = $settingModel->get('ai_chat_enabled', '1');
            $aiChatModel = $settingModel->get('ai_chat_model', 'openrouter/free');
            if (empty($aiChatModel) || in_array($aiChatModel, ['openrouter/auto', 'deepseek/deepseek-chat:free', 'meta-llama/llama-3.3-70b-instruct:free'])) {
                $aiChatModel = 'openrouter/free';
            }
            $aiChatApiKey = $settingModel->get('ai_chat_api_key', '');
        ?>
        <form id="chat-settings-form">
            <div style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px;">
                <div style="font-weight:600; margin-bottom:16px; color:var(--text-primary); display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-chat-dots" style="color:var(--primary);"></i> 2. AI Chat Assistant
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="ai_chat_enabled" name="ai_chat_enabled" value="1" <?= $aiChatEnabled === '1' ? 'checked' : '' ?> style="cursor:pointer;">
                        <label class="form-check-label" for="ai_chat_enabled" style="font-size:var(--font-size-xs); cursor:pointer;">Aktif</label>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.05em;">
                        <i class="bi bi-cpu-fill" style="color:var(--primary); margin-right:4px;"></i> Model AI Chat
                    </label>

                    <input type="hidden" id="ai_chat_model" name="ai_chat_model" value="<?= htmlspecialchars($aiChatModel) ?>">

                    <div style="margin-bottom:10px;">
                        <div class="model-cards-grid">
                            <div class="model-card <?= $aiChatModel === 'openrouter/free' ? 'selected' : '' ?>" onclick="selectChatModel('openrouter/free', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#2ec4b6,#0f9f90);">F</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Auto Model Gratis</div>
                                    <div class="model-card-meta">Otomatis (100% Free)</div>
                                </div>
                            </div>
                            
                            <div class="model-card <?= $aiChatModel === 'google/gemma-4-26b-a4b-it:free' ? 'selected' : '' ?>" onclick="selectChatModel('google/gemma-4-26b-a4b-it:free', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#4285F4,#FBBC04);">G</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Gemma 4 26B</div>
                                    <div class="model-card-meta">Google (100% Free)</div>
                                </div>
                            </div>

                            <div class="model-card <?= $aiChatModel === 'openai/gpt-oss-20b:free' ? 'selected' : '' ?>" onclick="selectChatModel('openai/gpt-oss-20b:free', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#10a37f,#0b7057);">O</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">GPT-OSS 20B</div>
                                    <div class="model-card-meta">OpenAI (100% Free)</div>
                                </div>
                            </div>

                            <div class="model-card <?= $aiChatModel === 'nvidia/nemotron-nano-12b-v2-vl:free' ? 'selected' : '' ?>" onclick="selectChatModel('nvidia/nemotron-nano-12b-v2-vl:free', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#76b900,#3a5b00);">N</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Nemotron VL</div>
                                    <div class="model-card-meta">NVIDIA (100% Free)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">API Key Chat (OpenRouter)</label>
                    <input id="ai_chat_api_key" name="ai_chat_api_key" type="password" value="" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="<?= !empty($aiChatApiKey) ? '(Tersimpan - Diubah untuk mengganti)' : 'sk-or-v1-...' ?>" />
                    <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">Isi API Key OpenRouter Anda di sini. Kosongkan jika sudah tersimpan atau ingin memakai API Key Scanner.</small>
                </div>
            </div>
            <button type="submit" class="btn-primary-custom" style="width:100%; padding:12px; font-weight:600; margin-bottom:8px;">💾 Simpan Pengaturan Chat</button>
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
    <div id="tabContent-pwd" style="display:<?= (($currentUser['user_level'] ?? '') === 'staff') ? 'block' : 'none' ?>;">
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

<style>
/* ── Model Card Selector ─────────────────────────────── */
.model-cards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
@media (max-width: 340px) {
    .model-cards-grid { grid-template-columns: 1fr; }
}
.model-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: var(--bg-primary);
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: border-color 200ms ease, background 200ms ease, box-shadow 200ms ease;
    position: relative;
    overflow: hidden;
}
.model-card:hover {
    border-color: rgba(230,57,70,0.4);
    background: rgba(230,57,70,0.04);
}
.model-card.selected {
    border-color: var(--primary);
    background: rgba(230,57,70,0.08);
    box-shadow: 0 0 0 2px rgba(230,57,70,0.15);
}
.model-card.selected::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 3px; height: 100%;
    background: var(--primary);
    border-radius: 3px 0 0 3px;
}
.model-card-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 14px; color: #fff;
    flex-shrink: 0;
    font-family: 'Inter', sans-serif;
}
.model-card-info { flex: 1; min-width: 0; }
.model-card-name {
    font-size: 12px; font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.model-card-meta {
    font-size: 10px; color: var(--text-muted);
    margin-top: 1px;
}
.model-badge {
    font-size: 9px; font-weight: 700;
    padding: 2px 5px; border-radius: 4px;
    text-transform: uppercase; letter-spacing: 0.04em;
    flex-shrink: 0; align-self: flex-start;
}
.model-badge-free { background: rgba(46,196,182,0.15); color: var(--success); }
.model-badge-pro  { background: rgba(76,201,240,0.15); color: var(--info); }
/* ── Dropdown Styles ─────────────────────────────── */
.custom-dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    cursor: pointer;
    transition: background 0.2s;
}
.custom-dropdown-item:hover {
    background: rgba(230,57,70,0.04);
}
.custom-dropdown-item.active {
    background: rgba(230,57,70,0.08);
}
</style>

<script>
    const csrfToken = document.getElementById('csrfToken').value;

    function selectModel(modelId, cardEl) {
        document.querySelectorAll('#ai-settings-form .model-card').forEach(c => c.classList.remove('selected'));
        if (cardEl) cardEl.classList.add('selected');
        document.getElementById('ai_model').value = modelId;
    }

    const scannerModels = [
        { id: 'google/gemini-2.0-flash', name: 'Google Gemini 2.0 Flash (Gratis 100%)', meta: 'Direct Google AI Studio - Super Cepat (1-2s)', icon: 'G', bg: 'linear-gradient(135deg,#4285F4,#0F9D58)', badge: 'Free Unlimited', badgeClass: 'free' },
        { id: 'openrouter/auto', name: 'Auto (OpenRouter)', meta: 'Pilih model terbaik otomatis OpenRouter', icon: 'A', bg: 'linear-gradient(135deg,#5c5c5c,#2c2c2c)', badge: 'Auto', badgeClass: 'pro' },
        { id: 'google/gemma-4-27b-it:free', name: 'Gemma 4 27B Vision (Free)', meta: 'Google - Paling Cerdas Gratis', icon: 'G', bg: 'linear-gradient(135deg,#4285F4,#0F9D58)', badge: 'Free', badgeClass: 'free' },
        { id: 'google/gemma-4-26b-a4b-it:free', name: 'Gemma 4 26B MoE (Free)', meta: 'Google - Cepat & Akurat (Gratis)', icon: 'G', bg: 'linear-gradient(135deg,#4285F4,#FBBC04)', badge: 'Free', badgeClass: 'free' },
        { id: 'google/gemma-4-31b-it:free', name: 'Gemma 4 31B (Free)', meta: 'Google - Akurat (Gratis)', icon: 'G', bg: 'linear-gradient(135deg,#34A853,#0F9D58)', badge: 'Free', badgeClass: 'free' },
        { id: 'nvidia/nemotron-nano-12b-v2-vl:free', name: 'Nemotron 12B Vision (Free)', meta: 'NVIDIA - OCR Vision (Gratis)', icon: 'N', bg: 'linear-gradient(135deg,#76b900,#4a7400)', badge: 'Free', badgeClass: 'free' },
        { id: 'openai/gpt-4o-mini', name: 'GPT-4o Mini', meta: 'OpenAI - Cepat (Paid)', icon: 'O', bg: 'linear-gradient(135deg,#10a37f,#0b7057)', badge: 'Pro', badgeClass: 'pro' },
        { id: 'openai/gpt-4o', name: 'GPT-4o', meta: 'OpenAI - Cerdas (Paid)', icon: 'O', bg: 'linear-gradient(135deg,#10a37f,#000000)', badge: 'Pro', badgeClass: 'pro' }
    ];


    function renderModels(filterText = '') {
        const container = document.getElementById('aiModelItems');
        if (!container) return;
        container.innerHTML = '';
        
        const currentModel = document.getElementById('ai_model').value || 'openrouter/auto';
        const filtered = scannerModels.filter(m => 
            m.name.toLowerCase().includes(filterText.toLowerCase()) || 
            m.id.toLowerCase().includes(filterText.toLowerCase())
        );
        
        if (filtered.length === 0) {
            container.innerHTML = '<div style="padding:12px; text-align:center; color:var(--text-muted); font-size:12px;">Tidak ditemukan</div>';
            return;
        }

        filtered.forEach(m => {
            const isActive = m.id === currentModel;
            const item = document.createElement('div');
            item.className = `custom-dropdown-item ${isActive ? 'active' : ''}`;
            item.onclick = () => selectDropdownModel(m);
            
            item.innerHTML = `
                <div class="model-card-icon" style="background:${m.bg}; width:28px; height:28px; font-size:12px;">${m.icon}</div>
                <div style="flex:1;">
                    <div style="font-size:12px; font-weight:600; color:var(--text-primary);">${m.name}</div>
                    <div style="font-size:10px; color:var(--text-muted);">${m.id}</div>
                </div>
                <span class="model-badge model-badge-${m.badgeClass}" style="font-size:8px;">${m.badge}</span>
            `;
            container.appendChild(item);
        });
        
        // Update selected display
        const activeModel = scannerModels.find(m => m.id === currentModel) || scannerModels[0];
        document.getElementById('selectedModelIcon').textContent = activeModel.icon;
        document.getElementById('selectedModelIcon').style.background = activeModel.bg;
        document.getElementById('selectedModelName').textContent = activeModel.name;
        document.getElementById('selectedModelId').textContent = activeModel.id;
    }

    function toggleModelDropdown() {
        const list = document.getElementById('aiModelList');
        if (list.style.opacity === '1') {
            list.style.opacity = '0';
            list.style.visibility = 'hidden';
        } else {
            list.style.display = 'flex';
            // slight delay to allow display:flex to apply before transition
            setTimeout(() => {
                list.style.opacity = '1';
                list.style.visibility = 'visible';
                document.getElementById('aiModelSearch').focus();
            }, 10);
        }
    }

    function selectDropdownModel(modelObj) {
        document.getElementById('ai_model').value = modelObj.id;
        renderModels(document.getElementById('aiModelSearch').value);
        toggleModelDropdown();
    }

    function filterModels() {
        renderModels(document.getElementById('aiModelSearch').value);
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('aiModelDropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            const list = document.getElementById('aiModelList');
            if (list) {
                list.style.opacity = '0';
                list.style.visibility = 'hidden';
            }
        }
    });

    // Initialize on load
    document.addEventListener('DOMContentLoaded', () => {
        renderModels();
    });

    function selectChatModel(modelId, cardEl) {
        document.querySelectorAll('#chat-settings-form .model-card').forEach(c => c.classList.remove('selected'));
        if (cardEl) cardEl.classList.add('selected');
        document.getElementById('ai_chat_model').value = modelId;
    }

    function switchTab(tab) {
        ['ai','geo','pwd'].forEach(t => {
            const btn = document.getElementById('tabBtn-' + t);
            if (btn) {
                btn.style.borderBottomColor = 'transparent';
                btn.style.color = 'var(--text-muted)';
                btn.style.fontWeight = '600';
            }
        });
        
        const activeBtn = document.getElementById('tabBtn-' + tab);
        if (activeBtn) {
            activeBtn.style.borderBottomColor = 'var(--primary)';
            activeBtn.style.color = 'var(--primary)';
            activeBtn.style.fontWeight = '700';
        }

        ['ai','geo','pwd'].forEach(t => {
            const el = document.getElementById('tabContent-' + t);
            if (el) el.style.display = 'none';
        });
        const target = document.getElementById('tabContent-' + tab);
        if (target) target.style.display = 'block';
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

    // Save AI Settings
    const aiForm = document.getElementById('ai-settings-form');
    if (aiForm) {
        aiForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
                const data = {
                    csrf_token: csrfToken,
                    ai_model: document.getElementById('ai_model').value,
                    ai_api_key: document.getElementById('ai_api_key').value,
                    ai_invoice_prompt: document.getElementById('ai_invoice_prompt').value
                };
                const result = await api('<?= BASE_URL ?>api/settings/app', 'POST', data);
                showToast(result.message || 'Pengaturan AI berhasil disimpan', 'success');
                if (data.ai_api_key) {
                    document.getElementById('ai_api_key').value = '';
                    document.getElementById('ai_api_key').placeholder = '(Tersimpan - Diubah untuk mengganti)';
                }
            } catch (err) {
                showToast(err.message || 'Gagal menyimpan pengaturan AI', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }

    // Save Chat Settings
    const chatForm = document.getElementById('chat-settings-form');
    if (chatForm) {
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
                const data = {
                    csrf_token: csrfToken,
                    ai_chat_enabled: document.getElementById('ai_chat_enabled').checked ? '1' : '0',
                    ai_chat_model: document.getElementById('ai_chat_model').value,
                    ai_chat_api_key: document.getElementById('ai_chat_api_key').value
                };
                const result = await api('<?= BASE_URL ?>api/settings/chat', 'POST', data);
                showToast(result.message || 'Pengaturan Chat berhasil disimpan', 'success');
                if (data.ai_chat_api_key) {
                    document.getElementById('ai_chat_api_key').value = '';
                    document.getElementById('ai_chat_api_key').placeholder = '(Tersimpan - Diubah untuk mengganti)';
                }
            } catch (err) {
                showToast(err.message || 'Gagal menyimpan pengaturan chat', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }

    // Save Geo Settings
    const geoForm = document.getElementById('geo-settings-form');
    if (geoForm) {
        geoForm.addEventListener('submit', async function(e) {
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
                showToast(err.message || 'Gagal menyimpan pengaturan Lokasi', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }

    // Change Password
    const pwdForm = document.getElementById('password-form');
    if (pwdForm) {
        pwdForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            const oldPwd = document.getElementById('old_password').value;
            const newPwd = document.getElementById('new_password').value;
            const confPwd = document.getElementById('confirm_password').value;
            
            if (newPwd !== confPwd) return showToast('Konfirmasi password baru tidak cocok', 'error');
            if (newPwd.length < 6) return showToast('Password baru minimal 6 karakter', 'error');
            
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Memproses...';
                const data = { csrf_token: csrfToken, old_password: oldPwd, new_password: newPwd };
                const result = await api('<?= BASE_URL ?>api/users/change-password', 'POST', data);
                if(result.success) {
                    showToast(result.message || 'Password berhasil diubah', 'success');
                    this.reset();
                } else {
                    showToast(result.error || 'Gagal mengubah password', 'error');
                }
            } catch (err) {
                showToast(err.message || 'Gagal mengubah password', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }
</script>
