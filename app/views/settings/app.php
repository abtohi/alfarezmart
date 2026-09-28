<?php
/**
 * @var string $csrfToken
 * @var string $aiProvider
 * @var string $aiModel
 * @var string $aiApiKey
 * @var string $aiGeminiApiKey
 * @var string $aiPrompt
 * @var string $storeRadius
 * @var string $storeLat
 * @var string $storeLng
 * @var string $aiChatEnabled
 * @var string $aiChatProvider
 * @var string $aiChatModel
 * @var string $aiChatApiKey
 * @var string $aiChatGeminiApiKey
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

                <!-- Provider Selector for Scanner -->
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;">
                        <i class="bi bi-hdd-network-fill" style="color:var(--primary); margin-right:4px;"></i> Provider AI Scanner
                    </label>
                    <input type="hidden" id="ai_provider" name="ai_provider" value="<?= htmlspecialchars($aiProvider ?? 'openrouter') ?>">

                    <div class="provider-switch-grid" id="scanner-provider-grid">
                        <div class="provider-card <?= ($aiProvider ?? 'openrouter') === 'gemini' ? 'selected' : '' ?>" id="provider-card-scanner-gemini" onclick="switchScannerProvider('gemini')">
                            <div class="provider-card-icon" style="background:linear-gradient(135deg, #4285F4, #34A853);">✨</div>
                            <div class="provider-card-info">
                                <div class="provider-card-title-row">
                                    <span class="provider-card-name">Google AI Studio (Gemini)</span>
                                    <span class="model-badge model-badge-free">100% Free Reset</span>
                                </div>
                                <div class="provider-card-desc">Bebas biaya token, kuota di-reset otomatis setiap hari & menit (~1.500 req/hari)</div>
                            </div>
                        </div>

                        <div class="provider-card <?= ($aiProvider ?? 'openrouter') !== 'gemini' ? 'selected' : '' ?>" id="provider-card-scanner-openrouter" onclick="switchScannerProvider('openrouter')">
                            <div class="provider-card-icon" style="background:linear-gradient(135deg, #6366f1, #a855f7);">🌐</div>
                            <div class="provider-card-info">
                                <div class="provider-card-title-row">
                                    <span class="provider-card-name">OpenRouter</span>
                                    <span class="model-badge model-badge-pro">Multi-Model</span>
                                </div>
                                <div class="provider-card-desc">Katalog model beragam (Llama 3.2 Vision, Nemotron, Auto Vision, dll)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                // --- GEMINI SCANNER MODELS ---
                $geminiScannerModelsList = [
                    [
                        'id' => 'gemini-2.0-flash',
                        'name' => 'Google: Gemini 2.0 Flash Vision',
                        'icon' => '⚡',
                        'icon_bg' => 'linear-gradient(135deg, #4285F4, #34A853)',
                        'badge' => 'REKOMENDASI (FREE)',
                        'badge_class' => 'model-badge-free',
                        'desc' => 'Multimodal OCR super cepat, akurasi tinggi, kuota harian reset otomatis'
                    ],
                    [
                        'id' => 'gemini-2.5-flash',
                        'name' => 'Google: Gemini 2.5 Flash Vision',
                        'icon' => '🌟',
                        'icon_bg' => 'linear-gradient(135deg, #1a73e8, #00acc1)',
                        'badge' => 'TERBARU',
                        'badge_class' => 'model-badge-free',
                        'desc' => 'Generasi multimodal terkini dengan reasoning tinggi untuk nota/faktur rumit'
                    ],
                    [
                        'id' => 'gemini-1.5-flash',
                        'name' => 'Google: Gemini 1.5 Flash Vision',
                        'icon' => '💎',
                        'icon_bg' => 'linear-gradient(135deg, #0ea5e9, #2563eb)',
                        'badge' => 'STABIL',
                        'badge_class' => 'model-badge-free',
                        'desc' => 'Model stabil dan teruji untuk membaca faktur dan struk belanja panjang'
                    ],
                    [
                        'id' => 'gemini-2.0-flash-lite',
                        'name' => 'Google: Gemini 2.0 Flash Lite',
                        'icon' => '🚀',
                        'icon_bg' => 'linear-gradient(135deg, #10b981, #059669)',
                        'badge' => 'KILAT',
                        'badge_class' => 'model-badge-free',
                        'desc' => 'Versi ringan berlatensi rendah untuk scan dokumen kasir cepat'
                    ],
                    [
                        'id' => 'gemini-1.5-pro',
                        'name' => 'Google: Gemini 1.5 Pro Vision',
                        'icon' => '🧠',
                        'icon_bg' => 'linear-gradient(135deg, #8b5cf6, #6d28d9)',
                        'badge' => 'PRO DETAIL',
                        'badge_class' => 'model-badge-pro',
                        'desc' => 'Pemahaman konteks mendalam untuk nota usang, terlipat, atau buram'
                    ],
                    [
                        'id' => 'custom',
                        'name' => 'Model Kustom Google AI Studio',
                        'icon' => '⚙️',
                        'icon_bg' => 'linear-gradient(135deg, #64748b, #475569)',
                        'badge' => 'KUSTOM',
                        'badge_class' => 'model-badge-pro',
                        'desc' => 'Ketik manual identifier model Google Gemini yang Anda inginkan'
                    ]
                ];

                // --- OPENROUTER SCANNER MODELS ---
                $scannerModelsList = [
                        [
                            'id' => 'openrouter/auto',
                            'name' => 'Auto Vision (OpenRouter) - Rekomendasi',
                            'icon' => '⚡',
                            'icon_bg' => 'linear-gradient(135deg, #f59e0b, #d97706)',
                            'badge' => 'TERBAIK',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Routing otomatis ke model vision multimodal tercerdas & tercepat'
                        ],
                        [
                            'id' => 'google/gemini-2.0-flash-001',
                            'name' => 'Google: Gemini 2.0 Flash Vision',
                            'icon' => '🌟',
                            'icon_bg' => 'linear-gradient(135deg, #4285F4, #34A853)',
                            'badge' => 'ULTRA CEPAT',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Multimodal vision cerdas tingkat tinggi dari Google, sangat detail'
                        ],
                        [
                            'id' => 'google/gemini-flash-1.5',
                            'name' => 'Google: Gemini 1.5 Flash Vision',
                            'icon' => '💎',
                            'icon_bg' => 'linear-gradient(135deg, #0ea5e9, #2563eb)',
                            'badge' => 'STABIL',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model stabil berkecepatan tinggi untuk scan nota panjang'
                        ],
                        [
                            'id' => 'meta-llama/llama-3.2-11b-vision-instruct:free',
                            'name' => 'Meta: Llama 3.2 11B Vision',
                            'icon' => '🦙',
                            'icon_bg' => 'linear-gradient(135deg, #0668E1, #0084FF)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Vision multimodal open-source dari Meta'
                        ],
                        [
                            'id' => 'google/gemma-4-31b-it:free',
                            'name' => 'Google: Gemma 4 31B Vision',
                            'icon' => '🟣',
                            'icon_bg' => 'linear-gradient(135deg, #8b5cf6, #6d28d9)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model kapasitas tinggi untuk teks faktur padat'
                        ],
                        [
                            'id' => 'nvidia/nemotron-nano-12b-v2-vl:free',
                            'name' => 'NVIDIA: Nemotron Nano 12B VL',
                            'icon' => '🟢',
                            'icon_bg' => 'linear-gradient(135deg, #10b981, #059669)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Vision multimodal berkecepatan tinggi dari NVIDIA'
                        ],
                        [
                            'id' => 'nvidia/nemotron-3-ultra:free',
                            'name' => 'NVIDIA: Nemotron 3 Ultra (free)',
                            'icon' => '🟢',
                            'icon_bg' => 'linear-gradient(135deg, #10b981, #047857)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) NVIDIA Nemotron 3 Ultra'
                        ],
                        [
                            'id' => 'poolside/laguna-s-2.1:free',
                            'name' => 'Poolside: Laguna S 2.1 (free)',
                            'icon' => '🌊',
                            'icon_bg' => 'linear-gradient(135deg, #0284c7, #0369a1)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) Poolside Laguna S 2.1'
                        ],
                        [
                            'id' => 'nvidia/nemotron-3.5-lightning:free',
                            'name' => 'NVIDIA: Nemotron 3.5 Lightning (free)',
                            'icon' => '⚡',
                            'icon_bg' => 'linear-gradient(135deg, #10b981, #059669)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) NVIDIA Nemotron 3.5 Lightning'
                        ],
                        [
                            'id' => 'inclusionai/ling-3.0-flash-fin:free',
                            'name' => 'inclusionAI: Ling 3.0 Flash Fin (free)',
                            'icon' => '🏮',
                            'icon_bg' => 'linear-gradient(135deg, #f97316, #c2410c)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) inclusionAI Ling 3.0 Flash Fin'
                        ],
                        [
                            'id' => 'dots-studio/dots3-note-preview:free',
                            'name' => 'Dots Studio: Dots3-Note Preview (free)',
                            'icon' => '📝',
                            'icon_bg' => 'linear-gradient(135deg, #6366f1, #4338ca)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) Dots Studio Dots3-Note Preview'
                        ],
                        [
                            'id' => 'nvidia/nemotron-3-super:free',
                            'name' => 'NVIDIA: Nemotron 3 Super (free)',
                            'icon' => '🟢',
                            'icon_bg' => 'linear-gradient(135deg, #059669, #065f46)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) NVIDIA Nemotron 3 Super'
                        ],
                        [
                            'id' => 'thinkingmachines/inkling:free',
                            'name' => 'Thinking Machines: Inkling (free)',
                            'icon' => '💡',
                            'icon_bg' => 'linear-gradient(135deg, #8b5cf6, #7c3aed)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) Thinking Machines Inkling'
                        ],
                        [
                            'id' => 'inclusionai/ling-3.0-flash-sante:free',
                            'name' => 'inclusionAI: Ling 3.0 Flash Sante (free)',
                            'icon' => '🏮',
                            'icon_bg' => 'linear-gradient(135deg, #ea580c, #9a3412)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) inclusionAI Ling 3.0 Flash Sante'
                        ],
                        [
                            'id' => 'thinkingmachines/inkling-small:free',
                            'name' => 'Thinking Machines: Inkling Small (free)',
                            'icon' => '💡',
                            'icon_bg' => 'linear-gradient(135deg, #a855f7, #6b21a8)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) Thinking Machines Inkling Small'
                        ],
                        [
                            'id' => 'nex-agi/nex-n2.5-pro:free',
                            'name' => 'Nex AGI: Nex-N2.5-Pro (free)',
                            'icon' => '🔮',
                            'icon_bg' => 'linear-gradient(135deg, #ec4899, #be185d)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) multimodal vision dari Nex AGI Pro'
                        ],
                        [
                            'id' => 'cohere/north-mini-code:free',
                            'name' => 'Cohere: North Mini Code (free)',
                            'icon' => '🔷',
                            'icon_bg' => 'linear-gradient(135deg, #3b82f6, #1d4ed8)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) Cohere North Mini Code'
                        ],
                        [
                            'id' => 'poolside/laguna-xs-2.1:free',
                            'name' => 'Poolside: Laguna XS 2.1 (free)',
                            'icon' => '🌊',
                            'icon_bg' => 'linear-gradient(135deg, #0ea5e9, #0284c7)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) Poolside Laguna XS 2.1'
                        ],
                        [
                            'id' => 'nex-agi/nex-n2.5-mini:free',
                            'name' => 'Nex AGI: Nex-N2.5-Mini (free)',
                            'icon' => '🔮',
                            'icon_bg' => 'linear-gradient(135deg, #f43f5e, #e11d48)',
                            'badge' => 'GRATIS',
                            'badge_class' => 'model-badge-free',
                            'desc' => 'Model AI gratis (free tier) multimodal vision dari Nex AGI Mini'
                        ],
                        [
                            'id' => 'custom',
                            'name' => 'Model Kustom OpenRouter',
                            'icon' => '⚙️',
                            'icon_bg' => 'linear-gradient(135deg, #64748b, #475569)',
                            'badge' => 'KUSTOM',
                            'badge_class' => 'model-badge-pro',
                            'desc' => 'Ketik manual identifier model OpenRouter spesifik Anda'
                        ],
                    ];
                    $currentScannerModel = $aiModel ?? 'openrouter/auto';
                    $isCurrentProviderGemini = ($aiProvider ?? 'openrouter') === 'gemini';

                    // Determine active item for Gemini
                    $geminiPresetIds = array_column($geminiScannerModelsList, 'id');
                    $isGeminiCustom = !empty($currentScannerModel) && !in_array($currentScannerModel, $geminiPresetIds);
                    $activeGeminiItem = null;
                    if ($isCurrentProviderGemini && $isGeminiCustom) {
                        $activeGeminiItem = [
                            'id' => 'custom',
                            'name' => 'Model Kustom: ' . $currentScannerModel,
                            'icon' => '⚙️',
                            'icon_bg' => 'linear-gradient(135deg, #64748b, #475569)',
                            'badge' => 'KUSTOM',
                            'badge_class' => 'model-badge-pro',
                            'desc' => 'Model kustom Google AI Studio'
                        ];
                    } else {
                        foreach ($geminiScannerModelsList as $m) {
                            if ($m['id'] === $currentScannerModel) {
                                $activeGeminiItem = $m;
                                break;
                            }
                        }
                        if (!$activeGeminiItem) $activeGeminiItem = $geminiScannerModelsList[0];
                    }

                    // Determine active item for OpenRouter
                    $openrouterPresetIds = array_column($scannerModelsList, 'id');
                    $isOpenrouterCustom = !empty($currentScannerModel) && !in_array($currentScannerModel, $openrouterPresetIds);
                    $activeOpenrouterItem = null;
                    if (!$isCurrentProviderGemini && $isOpenrouterCustom) {
                        $activeOpenrouterItem = [
                            'id' => 'custom',
                            'name' => 'Model Kustom: ' . $currentScannerModel,
                            'icon' => '⚙️',
                            'icon_bg' => 'linear-gradient(135deg, #64748b, #475569)',
                            'badge' => 'KUSTOM',
                            'badge_class' => 'model-badge-pro',
                            'desc' => 'Model kustom OpenRouter'
                        ];
                    } else {
                        foreach ($scannerModelsList as $m) {
                            if ($m['id'] === $currentScannerModel) {
                                $activeOpenrouterItem = $m;
                                break;
                            }
                        }
                        if (!$activeOpenrouterItem) $activeOpenrouterItem = $scannerModelsList[0];
                    }
                    ?>

                    <!-- Main hidden input for saved model -->
                    <input type="hidden" id="ai_model" name="ai_model" value="<?= htmlspecialchars($currentScannerModel) ?>">
                    <input type="hidden" id="scanner-selected-gemini-val" value="<?= htmlspecialchars($activeGeminiItem['id'] === 'custom' ? $currentScannerModel : $activeGeminiItem['id']) ?>">
                    <input type="hidden" id="scanner-selected-or-val" value="<?= htmlspecialchars($activeOpenrouterItem['id'] === 'custom' ? $currentScannerModel : $activeOpenrouterItem['id']) ?>">

                    <!-- GEMINI SCANNER MODEL DROPDOWN WRAPPER -->
                    <div id="scanner-model-container-gemini" style="display:<?= $isCurrentProviderGemini ? 'block' : 'none' ?>; margin-bottom:16px;">
                        <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;">
                            <i class="bi bi-cpu-fill" style="color:var(--primary); margin-right:4px;"></i> Pilihan Model Google Gemini
                        </label>

                        <div class="custom-dropdown-container" id="scanner-dropdown-container-gemini">
                            <div class="custom-dropdown-trigger" id="scanner-dropdown-trigger-gemini" onclick="toggleScannerDropdown(event, 'gemini')">
                                <div class="custom-dropdown-icon" id="scanner-gemini-icon" style="background:<?= $activeGeminiItem['icon_bg'] ?>;">
                                    <?= $activeGeminiItem['icon'] ?>
                                </div>
                                <div class="custom-dropdown-info">
                                    <div class="custom-dropdown-title-row">
                                        <span class="custom-dropdown-name" id="scanner-gemini-name"><?= htmlspecialchars($activeGeminiItem['name']) ?></span>
                                        <span class="model-badge <?= $activeGeminiItem['badge_class'] ?>" id="scanner-gemini-badge"><?= $activeGeminiItem['badge'] ?></span>
                                    </div>
                                    <div class="custom-dropdown-desc" id="scanner-gemini-desc"><?= htmlspecialchars($activeGeminiItem['desc']) ?></div>
                                </div>
                                <div class="custom-dropdown-chevron">
                                    <i class="bi bi-chevron-down" id="scanner-gemini-chevron"></i>
                                </div>
                            </div>

                            <!-- Dropdown Menu List Gemini -->
                            <div class="custom-dropdown-menu" id="scanner-dropdown-menu-gemini">
                                <?php foreach ($geminiScannerModelsList as $item): 
                                    $isSelected = ($activeGeminiItem['id'] === $item['id']);
                                ?>
                                <div class="custom-dropdown-option <?= $isSelected ? 'selected' : '' ?>" 
                                     onclick="selectScannerModel('<?= $item['id'] ?>', '<?= htmlspecialchars(addslashes($item['name'])) ?>', '<?= $item['icon'] ?>', '<?= $item['icon_bg'] ?>', '<?= $item['badge'] ?>', '<?= $item['badge_class'] ?>', '<?= htmlspecialchars(addslashes($item['desc'])) ?>', 'gemini', event)">
                                    <div class="custom-dropdown-icon" style="background:<?= $item['icon_bg'] ?>;">
                                        <?= $item['icon'] ?>
                                    </div>
                                    <div class="custom-dropdown-info">
                                        <div class="custom-dropdown-title-row">
                                            <span class="custom-dropdown-name"><?= htmlspecialchars($item['name']) ?></span>
                                            <span class="model-badge <?= $item['badge_class'] ?>"><?= $item['badge'] ?></span>
                                        </div>
                                        <div class="custom-dropdown-desc"><?= htmlspecialchars($item['desc']) ?></div>
                                    </div>
                                    <div class="custom-dropdown-check">
                                        <i class="bi bi-check2"></i>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div id="ai_model_custom_wrap_gemini" style="margin-top:12px; display:<?= ($isCurrentProviderGemini && $isGeminiCustom) ? 'block' : 'none' ?>;">
                            <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:6px;">
                                <i class="bi bi-terminal" style="color:var(--primary); margin-right:4px;"></i> Masukkan Model Identifier Google Gemini:
                            </label>
                            <input id="ai_model_custom_gemini" type="text" value="<?= ($isCurrentProviderGemini && $isGeminiCustom) ? htmlspecialchars($currentScannerModel) : '' ?>" style="width:100%; padding:10px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm); font-family:monospace;" placeholder="contoh: gemini-2.0-flash" oninput="onCustomModelInput(this.value, 'gemini')" />
                        </div>
                    </div>

                    <!-- OPENROUTER SCANNER MODEL DROPDOWN WRAPPER -->
                    <div id="scanner-model-container-openrouter" style="display:<?= !$isCurrentProviderGemini ? 'block' : 'none' ?>; margin-bottom:16px;">
                        <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;">
                            <i class="bi bi-cpu-fill" style="color:var(--primary); margin-right:4px;"></i> Pilihan Model OpenRouter
                        </label>

                        <div class="custom-dropdown-container" id="scanner-dropdown-container-openrouter">
                            <div class="custom-dropdown-trigger" id="scanner-dropdown-trigger-openrouter" onclick="toggleScannerDropdown(event, 'openrouter')">
                                <div class="custom-dropdown-icon" id="scanner-or-icon" style="background:<?= $activeOpenrouterItem['icon_bg'] ?>;">
                                    <?= $activeOpenrouterItem['icon'] ?>
                                </div>
                                <div class="custom-dropdown-info">
                                    <div class="custom-dropdown-title-row">
                                        <span class="custom-dropdown-name" id="scanner-or-name"><?= htmlspecialchars($activeOpenrouterItem['name']) ?></span>
                                        <span class="model-badge <?= $activeOpenrouterItem['badge_class'] ?>" id="scanner-or-badge"><?= $activeOpenrouterItem['badge'] ?></span>
                                    </div>
                                    <div class="custom-dropdown-desc" id="scanner-or-desc"><?= htmlspecialchars($activeOpenrouterItem['desc']) ?></div>
                                </div>
                                <div class="custom-dropdown-chevron">
                                    <i class="bi bi-chevron-down" id="scanner-or-chevron"></i>
                                </div>
                            </div>

                            <!-- Dropdown Menu List OpenRouter -->
                            <div class="custom-dropdown-menu" id="scanner-dropdown-menu-openrouter">
                                <?php foreach ($scannerModelsList as $item): 
                                    $isSelected = ($activeOpenrouterItem['id'] === $item['id']);
                                ?>
                                <div class="custom-dropdown-option <?= $isSelected ? 'selected' : '' ?>" 
                                     onclick="selectScannerModel('<?= $item['id'] ?>', '<?= htmlspecialchars(addslashes($item['name'])) ?>', '<?= $item['icon'] ?>', '<?= $item['icon_bg'] ?>', '<?= $item['badge'] ?>', '<?= $item['badge_class'] ?>', '<?= htmlspecialchars(addslashes($item['desc'])) ?>', 'openrouter', event)">
                                    <div class="custom-dropdown-icon" style="background:<?= $item['icon_bg'] ?>;">
                                        <?= $item['icon'] ?>
                                    </div>
                                    <div class="custom-dropdown-info">
                                        <div class="custom-dropdown-title-row">
                                            <span class="custom-dropdown-name"><?= htmlspecialchars($item['name']) ?></span>
                                            <span class="model-badge <?= $item['badge_class'] ?>"><?= $item['badge'] ?></span>
                                        </div>
                                        <div class="custom-dropdown-desc"><?= htmlspecialchars($item['desc']) ?></div>
                                    </div>
                                    <div class="custom-dropdown-check">
                                        <i class="bi bi-check2"></i>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div id="ai_model_custom_wrap_or" style="margin-top:12px; display:<?= (!$isCurrentProviderGemini && $isOpenrouterCustom) ? 'block' : 'none' ?>;">
                            <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:6px;">
                                <i class="bi bi-terminal" style="color:var(--primary); margin-right:4px;"></i> Masukkan Model Identifier OpenRouter:
                            </label>
                            <input id="ai_model_custom_or" type="text" value="<?= (!$isCurrentProviderGemini && $isOpenrouterCustom) ? htmlspecialchars($currentScannerModel) : '' ?>" style="width:100%; padding:10px 14px; border:1px solid var(--border-color); border-radius:var(--radius-md); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm); font-family:monospace;" placeholder="contoh: google/gemma-4-26b-a4b-it:free" oninput="onCustomModelInput(this.value, 'openrouter')" />
                        </div>
                    </div>
                </div>

                <!-- API KEYS FOR SCANNER -->
                <div id="scanner-api-key-gemini-wrap" style="margin-bottom:12px; display:<?= $isCurrentProviderGemini ? 'block' : 'none' ?>;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">
                        API Key Scanner (Google AI Studio)
                    </label>
                    <input id="ai_gemini_api_key" name="ai_gemini_api_key" type="password" value="<?= htmlspecialchars($aiGeminiApiKey ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="AIzaSy..." />
                    <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">
                        Dapatkan API Key gratis (kuota harian reset otomatis) di <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer" style="color:var(--primary); text-decoration:underline;">Google AI Studio</a>.
                    </small>
                </div>

                <div id="scanner-api-key-openrouter-wrap" style="margin-bottom:12px; display:<?= !$isCurrentProviderGemini ? 'block' : 'none' ?>;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">
                        API Key Scanner (OpenRouter)
                    </label>
                    <input id="ai_api_key" name="ai_api_key" type="password" value="<?= htmlspecialchars($aiApiKey ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="sk-or-v1-..." />
                    <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">
                        Dapatkan API Key di <a href="https://openrouter.ai/keys" target="_blank" rel="noopener noreferrer" style="color:var(--primary); text-decoration:underline;">OpenRouter.ai</a>.
                    </small>
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
            $aiChatProvider = $settingModel->get('ai_chat_provider', 'openrouter');
            $aiChatModel = $settingModel->get('ai_chat_model', 'cohere/north-mini-code:free');
            $aiChatApiKey = $settingModel->get('ai_chat_api_key', '');
            $aiChatGeminiApiKey = $settingModel->get('ai_chat_gemini_api_key', '');
            $isCurrentChatProviderGemini = ($aiChatProvider === 'gemini');
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

                <!-- Provider Selector for Chat -->
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;">
                        <i class="bi bi-hdd-network-fill" style="color:var(--primary); margin-right:4px;"></i> Provider AI Chat
                    </label>
                    <input type="hidden" id="ai_chat_provider" name="ai_chat_provider" value="<?= htmlspecialchars($aiChatProvider) ?>">

                    <div class="provider-switch-grid" id="chat-provider-grid">
                        <div class="provider-card <?= $isCurrentChatProviderGemini ? 'selected' : '' ?>" id="provider-card-chat-gemini" onclick="switchChatProvider('gemini')">
                            <div class="provider-card-icon" style="background:linear-gradient(135deg, #4285F4, #34A853);">✨</div>
                            <div class="provider-card-info">
                                <div class="provider-card-title-row">
                                    <span class="provider-card-name">Google AI Studio (Gemini)</span>
                                    <span class="model-badge model-badge-free">100% Free Reset</span>
                                </div>
                                <div class="provider-card-desc">Tanya jawab toko cepat & cerdas, reset kuota harian otomatis</div>
                            </div>
                        </div>

                        <div class="provider-card <?= !$isCurrentChatProviderGemini ? 'selected' : '' ?>" id="provider-card-chat-openrouter" onclick="switchChatProvider('openrouter')">
                            <div class="provider-card-icon" style="background:linear-gradient(135deg, #6366f1, #a855f7);">🌐</div>
                            <div class="provider-card-info">
                                <div class="provider-card-title-row">
                                    <span class="provider-card-name">OpenRouter</span>
                                    <span class="model-badge model-badge-pro">Multi-Model</span>
                                </div>
                                <div class="provider-card-desc">Pilihan model Cohere, DeepSeek V3, Claude Sonnet, dll</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.05em;">
                        <i class="bi bi-cpu-fill" style="color:var(--primary); margin-right:4px;"></i> Model AI Chat
                    </label>

                    <input type="hidden" id="ai_chat_model" name="ai_chat_model" value="<?= htmlspecialchars($aiChatModel) ?>">

                    <!-- GEMINI CHAT MODELS GRID -->
                    <div id="chat-models-gemini-wrap" style="display:<?= $isCurrentChatProviderGemini ? 'block' : 'none' ?>; margin-bottom:10px;">
                        <div class="model-cards-grid">
                            <div class="model-card <?= in_array($aiChatModel, ['gemini-2.0-flash', 'google/gemini-2.0-flash', 'google/gemini-2.0-flash-001']) || ($isCurrentChatProviderGemini && !in_array($aiChatModel, ['gemini-2.5-flash', 'gemini-2.0-flash-lite', 'gemini-1.5-flash', 'gemini-1.5-pro'])) ? 'selected' : '' ?>" data-model="gemini-2.0-flash" onclick="selectChatModel('gemini-2.0-flash', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#4285F4,#34A853);">G</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Gemini 2.0 Flash</div>
                                    <div class="model-card-meta">Rekomendasi (Kilat & Cerdas)</div>
                                </div>
                            </div>

                            <div class="model-card <?= in_array($aiChatModel, ['gemini-2.5-flash', 'google/gemini-2.5-flash']) ? 'selected' : '' ?>" data-model="gemini-2.5-flash" onclick="selectChatModel('gemini-2.5-flash', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#1a73e8,#00acc1);">🌟</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Gemini 2.5 Flash</div>
                                    <div class="model-card-meta">Akurasi & Reasoning Tinggi</div>
                                </div>
                            </div>

                            <div class="model-card <?= in_array($aiChatModel, ['gemini-2.0-flash-lite', 'google/gemini-2.0-flash-lite']) ? 'selected' : '' ?>" data-model="gemini-2.0-flash-lite" onclick="selectChatModel('gemini-2.0-flash-lite', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#10b981,#059669);">⚡</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Gemini 2.0 Flash Lite</div>
                                    <div class="model-card-meta">Respon Instan (Super Ringan)</div>
                                </div>
                            </div>

                            <div class="model-card <?= in_array($aiChatModel, ['gemini-1.5-flash', 'google/gemini-flash-1.5']) ? 'selected' : '' ?>" data-model="gemini-1.5-flash" onclick="selectChatModel('gemini-1.5-flash', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#0ea5e9,#2563eb);">💎</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Gemini 1.5 Flash</div>
                                    <div class="model-card-meta">Stabil & Teruji</div>
                                </div>
                            </div>

                            <div class="model-card <?= in_array($aiChatModel, ['gemini-1.5-pro', 'google/gemini-pro-1.5']) ? 'selected' : '' ?>" data-model="gemini-1.5-pro" onclick="selectChatModel('gemini-1.5-pro', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">🧠</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Gemini 1.5 Pro</div>
                                    <div class="model-card-meta">Analisis Mendalam</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OPENROUTER CHAT MODELS GRID -->
                    <div id="chat-models-openrouter-wrap" style="display:<?= !$isCurrentChatProviderGemini ? 'block' : 'none' ?>; margin-bottom:10px;">
                        <div class="model-cards-grid">
                            <div class="model-card <?= in_array($aiChatModel, ['openrouter/auto', 'openrouter/free', 'cohere/north-mini-code:free']) || (!$isCurrentChatProviderGemini && !in_array($aiChatModel, ['deepseek/deepseek-chat', 'google/gemini-2.5-flash', 'anthropic/claude-3.5-sonnet'])) ? 'selected' : '' ?>" data-model="cohere/north-mini-code:free" onclick="selectChatModel('cohere/north-mini-code:free', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#00b4d8,#0077b6);">⚡</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Ultra-Fast Free</div>
                                    <div class="model-card-meta">Respon Kilat (Gratis)</div>
                                </div>
                            </div>
                            
                            <div class="model-card <?= in_array($aiChatModel, ['deepseek/deepseek-chat', 'deepseek/deepseek-chat:free']) ? 'selected' : '' ?>" data-model="deepseek/deepseek-chat" onclick="selectChatModel('deepseek/deepseek-chat', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#0052CC,#003d99);">D</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">DeepSeek V3</div>
                                    <div class="model-card-meta">Super Pintar</div>
                                </div>
                            </div>

                            <div class="model-card <?= in_array($aiChatModel, ['google/gemini-2.5-flash', 'google/gemini-2.0-flash-001']) ? 'selected' : '' ?>" data-model="google/gemini-2.5-flash" onclick="selectChatModel('google/gemini-2.5-flash', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#4285F4,#34A853);">G</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Gemini Flash</div>
                                    <div class="model-card-meta">Google AI via OpenRouter</div>
                                </div>
                            </div>

                            <div class="model-card <?= $aiChatModel === 'anthropic/claude-3.5-sonnet' ? 'selected' : '' ?>" data-model="anthropic/claude-3.5-sonnet" onclick="selectChatModel('anthropic/claude-3.5-sonnet', this)">
                                <div class="model-card-icon" style="background:linear-gradient(135deg,#d97757,#b35f42);">C</div>
                                <div class="model-card-info">
                                    <div class="model-card-name">Claude 3.5 Sonnet</div>
                                    <div class="model-card-meta">Pro Premium</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAT API KEYS -->
                <div id="chat-api-key-gemini-wrap" style="margin-bottom:12px; display:<?= $isCurrentChatProviderGemini ? 'block' : 'none' ?>;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">
                        API Key Chat (Google AI Studio)
                    </label>
                    <input id="ai_chat_gemini_api_key" name="ai_chat_gemini_api_key" type="password" value="<?= htmlspecialchars($aiChatGeminiApiKey ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="AIzaSy..." />
                    <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">
                        Kosongkan jika ingin menggunakan API Key Google yang sama dengan Scanner.
                    </small>
                </div>

                <div id="chat-api-key-openrouter-wrap" style="margin-bottom:12px; display:<?= !$isCurrentChatProviderGemini ? 'block' : 'none' ?>;">
                    <label style="display:block; font-size:var(--font-size-xs); font-weight:600; color:var(--text-secondary); margin-bottom:4px;">
                        API Key Chat (OpenRouter)
                    </label>
                    <input id="ai_chat_api_key" name="ai_chat_api_key" type="password" value="<?= htmlspecialchars($aiChatApiKey ?? '') ?>" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:var(--radius-sm); background:var(--bg-primary); color:var(--text-primary); font-size:var(--font-size-sm);" placeholder="sk-or-v1-..." />
                    <small style="font-size:var(--font-size-xs); color:var(--text-muted); display:block; margin-top:4px;">
                        Kosongkan jika ingin menggunakan API Key OpenRouter yang sama dengan Scanner.
                    </small>
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
/* ── Provider Switch Cards ─────────────────────────────── */
.provider-switch-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 16px;
}
@media (max-width: 480px) {
    .provider-switch-grid { grid-template-columns: 1fr; }
}
.provider-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: var(--bg-primary);
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    user-select: none;
}
.provider-card:hover {
    border-color: var(--primary);
    background: var(--surface-2);
}
.provider-card.selected {
    border-color: var(--primary);
    background: rgba(230,57,70,0.08);
    box-shadow: 0 0 0 2px rgba(230,57,70,0.18);
}
.provider-card-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.provider-card-info {
    flex: 1;
    min-width: 0;
}
.provider-card-title-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 2px;
}
.provider-card-name {
    font-size: var(--font-size-sm);
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.provider-badge {
    font-size: 9px;
    font-weight: 700;
    padding: 2px 5px;
    border-radius: 4px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.provider-badge-gemini {
    background: rgba(66, 133, 244, 0.15);
    color: #4285F4;
}
.provider-badge-openrouter {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}
.provider-card-desc {
    font-size: 11px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.provider-card-radio {
    font-size: 18px;
    color: var(--border-color);
    transition: color 0.2s ease;
    flex-shrink: 0;
}
.provider-card.selected .provider-card-radio {
    color: var(--primary);
}

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

/* Custom Elegant Dropdown for AI Scanner */
.custom-dropdown-container {
    position: relative;
    width: 100%;
}
.custom-dropdown-trigger {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: var(--bg-primary);
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.custom-dropdown-trigger:hover {
    border-color: var(--primary);
    background: var(--surface-2);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.custom-dropdown-trigger.open {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(230,57,70,0.18);
    border-bottom-left-radius: 4px;
    border-bottom-right-radius: 4px;
}
.custom-dropdown-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}
.custom-dropdown-info {
    flex: 1;
    min-width: 0;
}
.custom-dropdown-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 2px;
}
.custom-dropdown-name {
    font-size: var(--font-size-sm);
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.custom-dropdown-desc {
    font-size: 11px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.custom-dropdown-chevron {
    color: var(--text-muted);
    font-size: 14px;
    transition: transform 0.25s ease;
    flex-shrink: 0;
}
.custom-dropdown-trigger.open .custom-dropdown-chevron {
    transform: rotate(180deg);
    color: var(--primary);
}
.custom-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2), 0 1px 3px rgba(0,0,0,0.1);
    z-index: 1000;
    overflow: hidden;
    padding: 6px;
    animation: dropDownFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    backdrop-filter: blur(12px);
}
.custom-dropdown-menu.show {
    display: block;
}
@keyframes dropDownFadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.custom-dropdown-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid transparent;
}
.custom-dropdown-option:hover {
    background: var(--surface-2);
    border-color: rgba(230,57,70,0.15);
}
.custom-dropdown-option.selected {
    background: rgba(230,57,70,0.08);
    border-color: rgba(230,57,70,0.3);
}
.custom-dropdown-check {
    color: var(--primary);
    font-size: 18px;
    font-weight: bold;
    opacity: 0;
    transition: opacity 0.15s ease;
}
.custom-dropdown-option.selected .custom-dropdown-check {
    opacity: 1;
}
</style>

<script>
    const csrfToken = document.getElementById('csrfToken').value;

    function switchScannerProvider(provider) {
        const isGemini = (provider === 'gemini');
        const hiddenProvider = document.getElementById('ai_provider');
        if (hiddenProvider) hiddenProvider.value = provider;
        
        // Toggle provider cards UI (IDs match HTML: provider-card-scanner-*)
        const cardGemini = document.getElementById('provider-card-scanner-gemini');
        const cardOr = document.getElementById('provider-card-scanner-openrouter');
        if (cardGemini) cardGemini.classList.toggle('selected', isGemini);
        if (cardOr) cardOr.classList.toggle('selected', !isGemini);

        // Toggle dropdown wrappers
        const geminiWrap = document.getElementById('scanner-model-container-gemini');
        const orWrap = document.getElementById('scanner-model-container-openrouter');
        if (geminiWrap) geminiWrap.style.display = isGemini ? 'block' : 'none';
        if (orWrap) orWrap.style.display = !isGemini ? 'block' : 'none';

        // Toggle API key inputs
        const apiKeyGeminiWrap = document.getElementById('scanner-api-key-gemini-wrap');
        const apiKeyOrWrap = document.getElementById('scanner-api-key-openrouter-wrap');
        if (apiKeyGeminiWrap) apiKeyGeminiWrap.style.display = isGemini ? 'block' : 'none';
        if (apiKeyOrWrap) apiKeyOrWrap.style.display = !isGemini ? 'block' : 'none';

        // Update active model value to active choice of selected provider
        const hiddenModel = document.getElementById('ai_model');
        if (hiddenModel) {
            if (isGemini) {
                const val = document.getElementById('scanner-selected-gemini-val')?.value || 'gemini-2.0-flash';
                hiddenModel.value = val;
            } else {
                const val = document.getElementById('scanner-selected-or-val')?.value || 'openrouter/auto';
                hiddenModel.value = val;
            }
        }
    }

    function toggleScannerDropdown(e, provider) {
        if (e) e.stopPropagation();
        const triggerId = provider === 'gemini' ? 'scanner-dropdown-trigger-gemini' : 'scanner-dropdown-trigger-openrouter';
        const menuId = provider === 'gemini' ? 'scanner-dropdown-menu-gemini' : 'scanner-dropdown-menu-openrouter';
        const trigger = document.getElementById(triggerId);
        const menu = document.getElementById(menuId);
        if (!trigger || !menu) return;
        const isOpen = menu.classList.contains('show');
        if (isOpen) {
            menu.classList.remove('show');
            trigger.classList.remove('open');
        } else {
            menu.classList.add('show');
            trigger.classList.add('open');
        }
    }

    function selectScannerModel(modelId, modelName, icon, iconBg, badge, badgeClass, desc, provider, e) {
        if (e) e.stopPropagation();
        const isGemini = (provider === 'gemini');
        
        // Update trigger UI
        const prefix = isGemini ? 'scanner-gemini-' : 'scanner-or-';
        const nameEl = document.getElementById(prefix + 'name');
        const badgeEl = document.getElementById(prefix + 'badge');
        const descEl = document.getElementById(prefix + 'desc');
        const iconEl = document.getElementById(prefix + 'icon');
        
        const hiddenInput = document.getElementById('ai_model');
        const customWrap = isGemini ? document.getElementById('ai_model_custom_wrap_gemini') : document.getElementById('ai_model_custom_wrap_or');
        const customInput = isGemini ? document.getElementById('ai_model_custom_gemini') : document.getElementById('ai_model_custom_or');
        const storedValInput = isGemini ? document.getElementById('scanner-selected-gemini-val') : document.getElementById('scanner-selected-or-val');

        if (nameEl) nameEl.textContent = modelName;
        if (badgeEl) {
            badgeEl.textContent = badge;
            badgeEl.className = 'model-badge ' + badgeClass;
        }
        if (descEl) descEl.textContent = desc;
        if (iconEl) {
            iconEl.innerHTML = icon;
            iconEl.style.background = iconBg;
        }

        // Update selected option class in menu
        const menuSelector = isGemini ? '#scanner-dropdown-menu-gemini' : '#scanner-dropdown-menu-openrouter';
        document.querySelectorAll(menuSelector + ' .custom-dropdown-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        if (e && e.currentTarget) {
            e.currentTarget.classList.add('selected');
        }

        // Close menu
        const triggerId = isGemini ? 'scanner-dropdown-trigger-gemini' : 'scanner-dropdown-trigger-openrouter';
        const menuId = isGemini ? 'scanner-dropdown-menu-gemini' : 'scanner-dropdown-menu-openrouter';
        const trigger = document.getElementById(triggerId);
        const menu = document.getElementById(menuId);
        if (menu) menu.classList.remove('show');
        if (trigger) trigger.classList.remove('open');

        // Handle custom input visibility & values
        if (modelId === 'custom') {
            if (customWrap) customWrap.style.display = 'block';
            if (customInput) {
                customInput.focus();
                const fallback = isGemini ? 'gemini-2.0-flash' : 'openrouter/auto';
                const val = customInput.value.trim() || fallback;
                if (hiddenInput) hiddenInput.value = val;
                if (storedValInput) storedValInput.value = val;
            }
        } else {
            if (customWrap) customWrap.style.display = 'none';
            if (hiddenInput) hiddenInput.value = modelId;
            if (storedValInput) storedValInput.value = modelId;
        }
    }

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        const containerGemini = document.getElementById('scanner-dropdown-container-gemini');
        if (containerGemini && !containerGemini.contains(e.target)) {
            const trigger = document.getElementById('scanner-dropdown-trigger-gemini');
            const menu = document.getElementById('scanner-dropdown-menu-gemini');
            if (menu) menu.classList.remove('show');
            if (trigger) trigger.classList.remove('open');
        }
        const containerOr = document.getElementById('scanner-dropdown-container-openrouter');
        if (containerOr && !containerOr.contains(e.target)) {
            const trigger = document.getElementById('scanner-dropdown-trigger-openrouter');
            const menu = document.getElementById('scanner-dropdown-menu-openrouter');
            if (menu) menu.classList.remove('show');
            if (trigger) trigger.classList.remove('open');
        }
    });

    function onCustomModelInput(val, provider) {
        const isGemini = (provider === 'gemini');
        const fallback = isGemini ? 'gemini-2.0-flash' : 'openrouter/auto';
        const finalVal = val.trim() || fallback;
        const hiddenInput = document.getElementById('ai_model');
        if (hiddenInput) hiddenInput.value = finalVal;
        const storedValInput = isGemini ? document.getElementById('scanner-selected-gemini-val') : document.getElementById('scanner-selected-or-val');
        if (storedValInput) storedValInput.value = finalVal;
    }

    function switchChatProvider(provider) {
        const isGemini = (provider === 'gemini');
        const hiddenProvider = document.getElementById('ai_chat_provider');
        if (hiddenProvider) hiddenProvider.value = provider;

        // Toggle card selected classes (IDs match HTML: provider-card-chat-*)
        const cardGemini = document.getElementById('provider-card-chat-gemini');
        const cardOr = document.getElementById('provider-card-chat-openrouter');
        if (cardGemini) cardGemini.classList.toggle('selected', isGemini);
        if (cardOr) cardOr.classList.toggle('selected', !isGemini);

        // Toggle chat model grids
        const geminiWrap = document.getElementById('chat-models-gemini-wrap');
        const orWrap = document.getElementById('chat-models-openrouter-wrap');
        if (geminiWrap) geminiWrap.style.display = isGemini ? 'block' : 'none';
        if (orWrap) orWrap.style.display = !isGemini ? 'block' : 'none';

        // Toggle API keys
        const apiKeyGeminiWrap = document.getElementById('chat-api-key-gemini-wrap');
        const apiKeyOrWrap = document.getElementById('chat-api-key-openrouter-wrap');
        if (apiKeyGeminiWrap) apiKeyGeminiWrap.style.display = isGemini ? 'block' : 'none';
        if (apiKeyOrWrap) apiKeyOrWrap.style.display = !isGemini ? 'block' : 'none';

        // Update active model value
        const hiddenModel = document.getElementById('ai_chat_model');
        if (hiddenModel) {
            if (isGemini) {
                const activeCard = document.querySelector('#chat-models-gemini-wrap .model-card.selected');
                hiddenModel.value = activeCard?.getAttribute('data-model') || 'gemini-2.0-flash';
            } else {
                const activeCard = document.querySelector('#chat-models-openrouter-wrap .model-card.selected');
                hiddenModel.value = activeCard?.getAttribute('data-model') || 'cohere/north-mini-code:free';
            }
        }
    }

    function selectChatModel(modelId, cardEl) {
        if (cardEl) {
            const parentGrid = cardEl.closest('.model-cards-grid');
            if (parentGrid) {
                parentGrid.querySelectorAll('.model-card').forEach(c => c.classList.remove('selected'));
            }
            cardEl.classList.add('selected');
        }
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
            if (!btn) return;
            const originalText = btn.textContent;
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
                const data = {
                    csrf_token: csrfToken,
                    ai_provider: document.getElementById('ai_provider')?.value || 'openrouter',
                    ai_model: document.getElementById('ai_model')?.value || '',
                    ai_api_key: document.getElementById('ai_api_key')?.value || '',
                    ai_gemini_api_key: document.getElementById('ai_gemini_api_key')?.value || '',
                    ai_invoice_prompt: document.getElementById('ai_invoice_prompt')?.value || ''
                };
                const result = await api('<?= BASE_URL ?>api/settings/app', 'POST', data);
                showToast(result.message || 'Pengaturan AI berhasil disimpan', 'success');
                if (data.ai_gemini_api_key) {
                    const el = document.getElementById('ai_gemini_api_key');
                    if (el) { el.value = ''; el.placeholder = '(Tersimpan - Diubah untuk mengganti)'; }
                }
                if (data.ai_api_key) {
                    const el = document.getElementById('ai_api_key');
                    if (el) { el.value = ''; el.placeholder = '(Tersimpan - Diubah untuk mengganti)'; }
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
            if (!btn) return;
            const originalText = btn.textContent;
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
                const data = {
                    csrf_token: csrfToken,
                    ai_chat_enabled: document.getElementById('ai_chat_enabled')?.checked ? '1' : '0',
                    ai_chat_provider: document.getElementById('ai_chat_provider')?.value || 'openrouter',
                    ai_chat_model: document.getElementById('ai_chat_model')?.value || '',
                    ai_chat_api_key: document.getElementById('ai_chat_api_key')?.value || '',
                    ai_chat_gemini_api_key: document.getElementById('ai_chat_gemini_api_key')?.value || ''
                };
                const result = await api('<?= BASE_URL ?>api/settings/chat', 'POST', data);
                showToast(result.message || 'Pengaturan Chat berhasil disimpan', 'success');
                if (data.ai_chat_gemini_api_key) {
                    const el = document.getElementById('ai_chat_gemini_api_key');
                    if (el) { el.value = ''; el.placeholder = '(Tersimpan - Diubah untuk mengganti)'; }
                }
                if (data.ai_chat_api_key) {
                    const el = document.getElementById('ai_chat_api_key');
                    if (el) { el.value = ''; el.placeholder = '(Tersimpan - Diubah untuk mengganti)'; }
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
            if (!btn) return;
            const originalText = btn.textContent;
            try {
                btn.disabled = true;
                btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyimpan...';
                const data = {
                    csrf_token: csrfToken,
                    store_latitude: document.getElementById('store_latitude')?.value || '',
                    store_longitude: document.getElementById('store_longitude')?.value || '',
                    store_radius_meters: document.getElementById('store_radius_meters')?.value || '0'
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
