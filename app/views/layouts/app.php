<!DOCTYPE html>
<?php
// Ambil setting lokasi toko untuk keperluan geofencing
$userLevel = AuthController::currentUser()['level'] ?? '';
$geoLat = '';
$geoLng = '';
$geoRadius = '';
if ($userLevel === 'staff') {
    $settingModel = new SettingModel();
    $geoLat = $settingModel->get('store_latitude', '');
    $geoLng = $settingModel->get('store_longitude', '');
    $geoRadius = $settingModel->get('store_radius_meters', '25');
}

/**
 * Generate a secure URL to serve an invoice photo from storage.
 * Photos are stored outside public_html, served via PHP endpoint.
 * @param string $storedPath  e.g. "storage/uploads/invoice_photos/inv_PUR-XXXXXX.jpg"
 * @return string  Full URL to the serving endpoint
 */

?>
<html lang="id">
<head>
    <!-- ⚡ Apply theme IMMEDIATELY before CSS renders to prevent dark flash -->
    <script>!function(){var t=localStorage.getItem('alfarezmart_theme')||'dark';document.documentElement.setAttribute('data-theme',t);}();</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AlfarezMart - Sistem Manajemen Stok Toko">
    <meta name="theme-color" content="#1a1a2e">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="msapplication-TileColor" content="#1a1a2e">
    
    <title><?= isset($title) ? htmlspecialchars($title) . ' - AlfarezMart' : 'AlfarezMart' ?></title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>public/images/mobile_icon.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>public/images/mobile_icon.png">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- App CSS & JS cache versioning -->
    <?php $v = '?v=15.93'; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/variables.css<?= $v ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/app.css<?= $v ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/components.css<?= $v ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/desktop.css<?= $v ?>">

    <!-- Desktop/Mobile Sidebar Responsive Protection (Bypasses Stale CSS Caches) -->
    <style>
        @media (max-width: 1023px) {
            .desktop-sidebar, .sidebar-collapse-btn, .desktop-scanner-hint { display: none !important; }
        }
        @media (min-width: 1024px) {
            .desktop-sidebar { display: flex !important; }
            .sidebar-brand {
                height: 64px !important;
                min-height: 64px !important;
                max-height: 64px !important;
                padding: 0 16px !important;
                margin: 0 !important;
                box-sizing: border-box !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                border-bottom: 1px solid var(--border-color) !important;
            }
            .app-header {
                height: 64px !important;
                min-height: 64px !important;
                max-height: 64px !important;
                box-sizing: border-box !important;
            }
            .sidebar-collapse-btn {
                display: flex !important;
                position: relative !important;
                top: auto !important;
                right: auto !important;
                width: 32px !important;
                height: 32px !important;
                border-radius: 6px !important;
                background: var(--surface-1) !important;
                border: 1px solid var(--border-color) !important;
                color: var(--text-muted) !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 0.85rem !important;
                cursor: pointer !important;
                flex-shrink: 0 !important;
                margin: 0 !important;
            }
            .desktop-scanner-hint { display: flex !important; }
        }
    </style>

    <link rel="manifest" href="<?= BASE_URL ?>manifest.json<?= $v ?>">

    <!-- Global Product & Packaging Sync & Repair Engine -->
    <script>
    window.fixAndSyncProducts = async function() {
        const btn = document.getElementById('btnFixProducts');
        const icon = btn ? btn.querySelector('i') : null;
        if (icon) icon.className = 'spinner-border spinner-border-sm';
        if (typeof showToast === 'function') showToast('Menghubungi server untuk sinkronisasi produk...', 'info');

        try {
            // Fetch latest fresh products catalog with all packaging levels from server
            const res = await fetch('<?= BASE_URL ?>api/products/sync?pos=1&_t=' + Date.now(), { cache: 'no-store' });
            if (res.ok) {
                const data = await res.json();
                if (data && data.products && Array.isArray(data.products) && data.products.length > 0) {
                    // Update in-memory catalog
                    window._posProductsCatalog = data.products;
                    
                    // Update IndexedDB (Dexie / OfflineDB) aman
                    if (typeof db !== 'undefined' && db.products) {
                        try {
                            await db.products.clear();
                            await db.products.bulkPut(data.products);
                        } catch(dbErr) {}
                    }
                    
                    // Update localStorage
                    try {
                        const serialized = JSON.stringify(data.products);
                        if (serialized.length < 1500000) {
                            localStorage.setItem('pos_catalog_cache', serialized);
                        }
                    } catch(e) {}
                    
                    // If on POS page, re-calculate and re-render cart
                    if (typeof renderCart === 'function' && typeof cart !== 'undefined' && Array.isArray(cart)) {
                        cart.forEach(item => {
                            const fresh = data.products.find(p => p.id == item.product_id);
                            if (fresh && fresh.packagings) {
                                item.packagings = fresh.packagings;
                            }
                            if (typeof recalcItemPrice === 'function') recalcItemPrice(item);
                        });
                        renderCart();
                    }

                    if (typeof showToast === 'function') {
                        showToast(`✅ Sukses! ${data.products.length} produk & seluruh kemasan berhasil disinkronkan.`, 'success', 4000);
                    }
                } else {
                    // Server mengembalikan 0 produk (kemungkinan MySQL limit / offline fallback)
                    // JANGAN HAPUS data lokal!
                    const localCount = (window._posProductsCatalog && window._posProductsCatalog.length) || 0;
                    if (typeof showToast === 'function') {
                        showToast(`⚠️ Server mengembalikan 0 produk. Menggunakan data lokal (${localCount} produk aktif).`, 'warning', 5000);
                    }
                }
            } else {
                if (typeof showToast === 'function') showToast('Server belum dapat dijangkau. Tetap menggunakan data lokal.', 'warning');
            }
        } catch(err) {
            if (typeof showToast === 'function') showToast('Koneksi lambat. Menggunakan data offline lokal.', 'warning');
        } finally {
            if (icon) icon.className = 'bi bi-tools';
        }
    };

    </script>
</head>
<body>
<?php $__globalCsrf = (new Security())->getCSRFToken(); ?>
<input type="hidden" id="csrfToken" value="<?= $__globalCsrf ?>">


    <!-- ⚡ Error Log Catcher — dimuat paling awal agar menangkap semua error -->
    <script src="<?= BASE_URL ?>public/js/error-logger.js<?= $v ?>"></script>

    <!-- Ultra-Sleek Animated Typography Splash Loader -->
    <style>
        @keyframes pulseGlowText {
            0%, 100% { opacity: 0.85; transform: scale(0.98); }
            50% { opacity: 1; transform: scale(1.03); filter: drop-shadow(0 0 16px rgba(239, 68, 68, 0.5)); }
        }
        @keyframes lineProgressAnim {
            0% { left: -40%; width: 35%; }
            50% { left: 35%; width: 45%; }
            100% { left: 100%; width: 35%; }
        }
        .splash-brand-text {
            font-size: 2.4rem;
            font-weight: 900;
            letter-spacing: -0.5px;
            font-family: 'Inter', -apple-system, sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 2px;
            animation: pulseGlowText 2.2s infinite ease-in-out;
            user-select: none;
        }
        .splash-brand-alfarez {
            background: linear-gradient(135deg, #6366f1 0%, #818cf8 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .splash-brand-mart {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-style: italic;
            display: inline-block;
            padding-right: 0.15em;
        }
        .splash-progress-track {
            width: 150px;
            height: 4px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            overflow: hidden;
            position: relative;
            margin-top: 18px;
        }
        .splash-progress-bar {
            position: absolute;
            top: 0;
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #ef4444);
            border-radius: 4px;
            animation: lineProgressAnim 1.4s infinite ease-in-out;
        }
    </style>
    <div id="appInitLoader" style="position:fixed;top:0;left:0;right:0;bottom:0;background:#0d0e15;z-index:99999;display:flex;flex-direction:column;justify-content:center;align-items:center;transition:opacity 0.4s ease;">
        <div class="splash-brand-text">
            <span class="splash-brand-alfarez">Alfarez</span><span class="splash-brand-mart">Mart</span>
        </div>
        <div class="splash-progress-track">
            <div class="splash-progress-bar"></div>
        </div>
        <div style="font-size:11px; color:#64748b; margin-top:10px; font-weight:500; letter-spacing:0.5px;">Memuat Aplikasi...</div>
    </div>
    <script>
    function hideAppLoader() {
        const loader = document.getElementById('appInitLoader');
        if (!loader || loader.dataset.hidden === '1') return;
        loader.dataset.hidden = '1';
        loader.style.pointerEvents = 'none';
        loader.style.opacity = '0';
        setTimeout(() => { loader.style.display = 'none'; }, 300);
    }
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        hideAppLoader();
    } else {
        window.addEventListener('load', hideAppLoader);
        document.addEventListener('DOMContentLoaded', hideAppLoader);
    }
    setTimeout(hideAppLoader, 600);
    </script>

    <!-- Desktop Sidebar Navigation (hidden on mobile via CSS) -->
    <aside class="desktop-sidebar" id="desktopSidebar">
        <div class="sidebar-brand" onclick="if(document.getElementById('desktopSidebar').classList.contains('collapsed')) toggleSidebar();">
            <div class="sidebar-brand-left">
                <img src="<?= BASE_URL ?>public/images/Icon.png" alt="AlfarezMart" width="34" height="34">
                <span>Alfarez<span style="color:var(--primary)">Mart</span></span>
            </div>
            <button class="sidebar-collapse-btn" onclick="event.stopPropagation();toggleSidebar();" title="Toggle Sidebar">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= BASE_URL ?>" class="sidebar-item <?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>">
                <i class="bi <?= ($activeNav ?? '') === 'home' ? 'bi-house-door-fill' : 'bi-house-door' ?>"></i>
                <span>Beranda</span>
            </a>
            <a href="<?= BASE_URL ?>products" class="sidebar-item <?= ($activeNav ?? '') === 'products' ? 'active' : '' ?>">
                <i class="bi <?= ($activeNav ?? '') === 'products' ? 'bi-box-seam-fill' : 'bi-box-seam' ?>"></i>
                <span>Produk</span>
            </a>
            <a href="<?= BASE_URL ?>products/barcode-editor" class="sidebar-item <?= ($activeNav ?? '') === 'barcode_editor' ? 'active' : '' ?>">
                <i class="bi bi-upc-scan"></i>
                <span>Edit Barcode Kemasan</span>
            </a>
            <a href="<?= BASE_URL ?>scanner" class="sidebar-item <?= ($activeNav ?? '') === 'scan' ? 'active' : '' ?>">
                <i class="bi bi-search-heart"></i>
                <span>Cek Harga / Scan</span>
            </a>
            <a href="<?= BASE_URL ?>purchases/create" class="sidebar-item <?= ($activeNav ?? '') === 'purchase' ? 'active' : '' ?>">
                <i class="bi <?= ($activeNav ?? '') === 'purchase' ? 'bi-cart-plus-fill' : 'bi-cart-plus' ?>"></i>
                <span>Barang Masuk</span>
            </a>
            <a href="<?= BASE_URL ?>sales/pos" class="sidebar-item <?= ($activeNav ?? '') === 'pos' ? 'active' : '' ?>">
                <i class="bi <?= ($activeNav ?? '') === 'pos' ? 'bi-receipt-cutoff' : 'bi-receipt' ?>"></i>
                <span>Kasir POS</span>
            </a>
            <div class="sidebar-separator"></div>
            <?php if ($this->hasServiceAccess('ppob')): ?>
            <a href="<?= BASE_URL ?>ppob" class="sidebar-item">
                <i class="bi bi-phone"></i>
                <span>Produk Digital (PPOB)</span>
            </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>sales" class="sidebar-item">
                <i class="bi bi-clock-history"></i>
                <span>Riwayat Penjualan</span>
            </a>
            <a href="<?= BASE_URL ?>purchases" class="sidebar-item">
                <i class="bi bi-cart-check"></i>
                <span>Riwayat Pembelian</span>
            </a>
            <a href="<?= BASE_URL ?>customers" class="sidebar-item">
                <i class="bi bi-people"></i>
                <span>Pelanggan</span>
            </a>
            <div class="sidebar-separator"></div>
            <?php if ($this->hasServiceAccess('debts')): ?>
            <a href="<?= BASE_URL ?>debts" class="sidebar-item">
                <i class="bi bi-journal-text"></i>
                <span>Hutang &amp; Piutang</span>
            </a>
            <?php endif; ?>
            <?php if ($this->hasServiceAccess('finance')): ?>
            <a href="<?= BASE_URL ?>finance" class="sidebar-item">
                <i class="bi bi-wallet2"></i>
                <span>Keuangan</span>
            </a>
            <?php endif; ?>
            <?php if ($this->hasServiceAccess('reports')): ?>
            <a href="<?= BASE_URL ?>reports" class="sidebar-item">
                <i class="bi bi-graph-up"></i>
                <span>Laporan</span>
            </a>
            <?php endif; ?>
            <div class="sidebar-separator"></div>
            <a href="<?= BASE_URL ?>settings" class="sidebar-item">
                <i class="bi bi-gear"></i>
                <span>Pengaturan</span>
            </a>
            <a href="<?= BASE_URL ?>chat" class="sidebar-item">
                <i class="bi bi-stars"></i>
                <span>Tanya AI</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <?php if (isset($currentUser)): ?>
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
                    <div class="sidebar-user-role"><?= ucfirst($currentUser['level'] ?? 'user') ?></div>
                </div>
                <a href="<?= BASE_URL ?>logout" onclick="localStorage.removeItem('alfarezmart_logged_in'); localStorage.removeItem('alfarezmart_user');" class="sidebar-logout" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Status Bar Overlay (Android-like) -->
    <div class="status-bar-overlay"></div>

    <!-- Signal Strength Banner (replaces old offlineBanner) -->
    <div id="signalBanner" style="display:none;text-align:center;padding:5px 12px;font-size:11px;font-weight:700;transition:all 0.3s ease;border-bottom:1px solid transparent;">
        <i id="signalBannerIcon" class="bi bi-wifi-off" style="margin-right:5px;"></i>
        <span id="signalBannerText">Mode Offline</span>
    </div>
    <script>
    (function(){
        const banner  = document.getElementById('signalBanner');
        const icon    = document.getElementById('signalBannerIcon');
        const txt     = document.getElementById('signalBannerText');

        // States: 'online' | 'weak' | 'offline'
        let _signalState = 'online';
        // Expose for other scripts to read
        window.getSignalState = () => _signalState;

        function setState(state) {
            if (_signalState === state) return;
            _signalState = state;
            if (state === 'online') {
                banner.style.display = 'none';
            } else if (state === 'weak') {
                banner.style.display = 'block';
                banner.style.background = 'rgba(234,179,8,0.15)';
                banner.style.color = '#ca8a04';
                banner.style.borderBottomColor = 'rgba(234,179,8,0.3)';
                icon.className = 'bi bi-wifi-1';
                txt.textContent = 'Sinyal Lemah — Scan menggunakan data lokal (instan)';
            } else { // offline
                banner.style.display = 'block';
                banner.style.background = 'rgba(239,68,68,0.12)';
                banner.style.color = '#ef4444';
                banner.style.borderBottomColor = 'rgba(239,68,68,0.25)';
                icon.className = 'bi bi-wifi-off';
                txt.textContent = 'Mode Offline — Data lokal digunakan';
            }
            // Broadcast so pages can react
            document.dispatchEvent(new CustomEvent('signal-state-changed', { detail: { state } }));
        }

        function checkSignal() {
            if (!navigator.onLine) { setState('offline'); return; }

            // Use Network Information API if available (0 network requests)
            const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            if (conn) {
                const slow = conn.saveData || (conn.rtt && conn.rtt > 1500) || ['slow-2g'].includes(conn.effectiveType);
                setState(slow ? 'weak' : 'online');
                return;
            }

            setState('online');
        }

        window.addEventListener('online',  () => checkSignal());
        window.addEventListener('offline', () => setState('offline'));

        // Network Information API change event (passive, no polling)
        const conn2 = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (conn2) conn2.addEventListener('change', checkSignal);

        // Initial check once on DOM ready without looping probe
        document.addEventListener('DOMContentLoaded', () => {
            checkSignal();
        });
    })();
    </script>

    <!-- App Header -->
    <header class="app-header" id="appHeader">
        <div class="header-content">
            <div class="header-left">
                <img src="<?= BASE_URL ?>public/images/Icon.png" alt="AlfarezMart" class="header-logo" width="48" height="48">
                <h1 class="header-title"><?= htmlspecialchars($title ?? 'AlfarezMart') ?></h1>
            </div>
            <div class="header-right">
                <a href="<?= BASE_URL ?>chat" class="header-ai-btn" title="AlfarezMart AI Assistant">
                    <i class="bi bi-stars"></i>
                    <span class="header-ai-text">Tanya AI</span>
                </a>
                <button class="header-btn" id="btnTheme" aria-label="Ubah tema" onclick="toggleTheme()" title="Ubah tema Dark/Light">
                    <i class="bi bi-sun" id="themeIcon" data-icon="sun"></i>
                </button>
                <button class="header-btn" id="btnReload" aria-label="Muat Ulang" onclick="window.location.reload(true)" title="Muat Ulang Halaman (Hard Refresh)">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <button class="header-btn" id="btnSync" aria-label="Sinkronisasi" onclick="triggerSync()" oncontextmenu="openSyncSettings(event)" ontouchstart="startSyncSettingsTimer(event)" ontouchend="clearSyncSettingsTimer(event)" title="Tahan untuk Pengaturan Sync">
                    <i class="bi bi-arrow-repeat" id="syncIcon"></i>
                    <span class="notif-badge" id="offlineSyncBadge" style="display:none">0</span>
                </button>
                <button class="header-btn" id="btnTurboClean" aria-label="Bersihkan Cache &amp; Turbo" onclick="window.AppCleaner && window.AppCleaner.cleanAll()" title="Bersihkan Cache &amp; Percepat Aplikasi" style="color:#eab308; background:rgba(234,179,8,0.12); border-radius:8px;">
                    <i class="bi bi-lightning-charge-fill"></i>
                </button>
                <button class="header-btn" id="btnFixProducts" aria-label="Perbaiki &amp; Sinkronkan Produk" onclick="fixAndSyncProducts()" title="Perbaiki &amp; Sinkronkan Data Produk, Barcode, &amp; Kemasan Bertingkat" style="color:#38bdf8; background:rgba(56,189,248,0.12); border-radius:8px;">
                    <i class="bi bi-tools"></i>
                </button>
                <?php if (isset($currentUser)): ?>
                <div class="header-user" style="display:flex;align-items:center;gap:6px;margin-left:4px;">
                    <div style="width:28px;height:28px;border-radius:50%;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--primary);border:1px solid var(--primary);">
                        <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </header>


    <!-- Search Overlay -->
    <div class="search-overlay" id="searchOverlay">
        <div class="search-container">
            <div class="search-input-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" id="globalSearch" placeholder="Cari produk, brand, barcode..." autocomplete="off">
                <button class="search-close" id="btnCloseSearch"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="search-results" id="searchResults"></div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="app-content" id="appContent">
        <!-- Pull to Refresh Indicator -->
        <div id="ptr-indicator" style="position: absolute; top: -50px; left: 0; width: 100%; text-align: center; z-index: 999; display: flex; justify-content: center; align-items: center; transition: transform 0.2s ease, opacity 0.2s ease; opacity: 0; pointer-events: none;">
            <div style="background: var(--surface-1); box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 50%; width: 40px; height: 40px; display: flex; justify-content: center; align-items: center; border: 1px solid var(--border-color);">
                <i class="bi bi-arrow-down-short text-primary fs-3" id="ptr-icon" style="transition: transform 0.3s ease;"></i>
                <div class="spinner-border text-primary spinner-border-sm" id="ptr-spinner" style="display: none;" role="status"></div>
            </div>
        </div>

        <?= $content ?? '' ?>
    </main>

    <!-- Floating AI Chat Button -->
    <?php if (!isset($active_menu) || $active_menu !== 'chat'): ?>
        <a href="<?= BASE_URL ?>chat" class="floating-chat-btn" title="AlfarezMart AI Assistant">
            <div class="chat-icon-wrapper">
                <i class="bi bi-stars"></i>
            </div>
        </a>
    <?php endif; ?>

    <!-- Bottom Navigation Bar (Android-style) -->
    <nav class="bottom-nav" id="bottomNav">
        <a href="<?= BASE_URL ?>" class="nav-item <?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>" id="navHome">
            <i class="bi <?= ($activeNav ?? '') === 'home' ? 'bi-house-door-fill' : 'bi-house-door' ?>"></i>
            <span>Beranda</span>
        </a>
        <a href="<?= BASE_URL ?>products" class="nav-item <?= ($activeNav ?? '') === 'products' ? 'active' : '' ?>" id="navProducts">
            <i class="bi <?= ($activeNav ?? '') === 'products' ? 'bi-box-seam-fill' : 'bi-box-seam' ?>"></i>
            <span>Produk</span>
        </a>
        <a href="<?= BASE_URL ?>scanner" class="nav-item nav-scan <?= ($activeNav ?? '') === 'scan' ? 'active' : '' ?>" id="navScan">
            <div class="scan-btn-wrapper">
                <i class="bi bi-upc-scan"></i>
            </div>
            <span>Scan</span>
        </a>
        <a href="<?= BASE_URL ?>purchases/create" class="nav-item <?= ($activeNav ?? '') === 'purchase' ? 'active' : '' ?>" id="navPurchase">
            <i class="bi <?= ($activeNav ?? '') === 'purchase' ? 'bi-cart-plus-fill' : 'bi-cart-plus' ?>"></i>
            <span>Masuk</span>
        </a>
        <a href="<?= BASE_URL ?>sales/pos" class="nav-item <?= ($activeNav ?? '') === 'pos' ? 'active' : '' ?>" id="navPos">
            <i class="bi <?= ($activeNav ?? '') === 'pos' ? 'bi-receipt-cutoff' : 'bi-receipt' ?>"></i>
            <span>Kasir POS</span>
        </a>
    </nav>

    <!-- Toast Notification -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Sync Settings Modal -->
    <div class="modal fade" id="syncSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:var(--bg-secondary);border:1px solid var(--border-color);">
                <div class="modal-header" style="border-bottom:1px solid var(--border-color);">
                    <h5 class="modal-title" style="font-size:1.1rem;font-weight:700;"><i class="bi bi-cloud-sync" style="color:var(--primary);margin-right:8px;"></i>Pengaturan Sinkronisasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-bottom:16px;border:1px solid var(--border-color);">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <span style="font-weight:600;font-size:0.95rem;">Sinkronisasi Otomatis</span>
                            <div class="form-check form-switch" style="margin:0;">
                                <input class="form-check-input" type="checkbox" id="autoSyncToggle" style="width:2.5em;height:1.2em;cursor:pointer;background-color:var(--surface-2);border-color:var(--border-color);" onchange="toggleAutoSync(this.checked)">
                            </div>
                        </div>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin:0;line-height:1.4;">Jika aktif, sistem akan otomatis mengunduh pembaruan produk dan mengirim data offline saat aplikasi online. Jika mati, gunakan tombol sinkronisasi secara manual.</p>
                    </div>

                    <div style="display:flex;gap:12px;margin-bottom:16px;">
                        <div style="flex:1;background:var(--surface-1);border-radius:var(--radius-lg);padding:12px;border:1px solid var(--border-color);text-align:center;">
                            <div style="font-size:1.5rem;font-weight:700;color:var(--primary);" id="syncPendingCount">0</div>
                            <div style="font-size:0.75rem;color:var(--text-muted);">Menunggu Dikirim</div>
                        </div>
                        <div style="flex:1;background:var(--surface-1);border-radius:var(--radius-lg);padding:12px;border:1px solid var(--border-color);text-align:center;">
                            <div style="font-size:1.5rem;font-weight:700;color:var(--success);" id="syncCachedCount">0</div>
                            <div style="font-size:0.75rem;color:var(--text-muted);">Produk Offline</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border-color);display:flex;flex-direction:column;gap:8px;">
                    <button type="button" class="btn-primary-custom w-100" onclick="forceManualSync()" style="padding:10px;font-weight:600;">
                        <i class="bi bi-arrow-repeat"></i> Mulai Sinkronisasi Sekarang
                    </button>
                    <button type="button" class="btn-outline-custom w-100" onclick="clearPendingQueue()" style="padding:8px;font-size:0.85rem;color:var(--danger);border-color:rgba(239,68,68,0.3);background:var(--danger-bg);">
                        <i class="bi bi-trash"></i> Bersihkan Antrian Offline (Reset Badge)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- App JS -->
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const version = '15.00';
        window.IS_DB_OFFLINE = <?= (class_exists('Database') && Database::getInstance()->isOffline()) ? 'true' : 'false' ?>;
    </script>
    <script src="<?= BASE_URL ?>public/js/utils.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/dexie.min.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/db.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/printer_v3.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/barcode.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/packaging-prices.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/qty-pricing.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/components.js<?= $v ?>"></script>
    <script>
        // Injeksi konfigurasi geofencing untuk staff
        window.GEO_CONFIG = {
            enabled: true,
            role: '<?= $userLevel ?>',
            lat: '<?= $geoLat ?>',
            lng: '<?= $geoLng ?>',
            radius: '<?= $geoRadius ?>',
            logoutUrl: '<?= BASE_URL ?>logout'
        };
    </script>

    <script>
        // Theme toggle (dark/light)
        (function(){
            const THEME_KEY = 'alfarezmart_theme';
            function applyTheme(theme){
                const root = document.documentElement;
                root.setAttribute('data-theme', theme);
            }
            window.toggleTheme = function(){
                const current = localStorage.getItem(THEME_KEY) || 'dark';
                const next = current === 'dark' ? 'light' : 'dark';
                localStorage.setItem(THEME_KEY, next);
                applyTheme(next);
                try { navigator.vibrate && navigator.vibrate(10); } catch(e) {}
            };
            function syncThemeIcon(theme){
                const iconEl = document.getElementById('themeIcon');
                if(!iconEl) return;
                const next = theme === 'light' ? 'moon' : 'sun';
                iconEl.dataset.icon = next;
                iconEl.className = `bi bi-${next}`;
                iconEl.id = 'themeIcon';
            }

            document.addEventListener('DOMContentLoaded', function(){
                const saved = localStorage.getItem(THEME_KEY) || 'dark';
                applyTheme(saved);
                syncThemeIcon(saved);
            });

            const _origApplyTheme = applyTheme;
            applyTheme = function(theme){
                _origApplyTheme(theme);
                syncThemeIcon(theme);
            };
        })();
    </script>

    <script src="<?= BASE_URL ?>public/js/geofencing.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/ppob_contacts.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/desktop.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/app.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/instant-nav.js<?= $v ?>"></script>

    <!-- Service Worker Registration & Cache Buster -->
    <script>
    const APP_VERSION = '15.96'; // Update this to force client reloads


    
    // Self-healing cache buster
    if (localStorage.getItem('app_version') !== APP_VERSION) {
        console.log('New version detected! Clearing caches...');
        if ('caches' in window) {
            caches.keys().then(names => {
                for (let name of names) caches.delete(name);
            });
        }
        localStorage.setItem('app_version', APP_VERSION);
        if (navigator.serviceWorker) {
            navigator.serviceWorker.getRegistrations().then(registrations => {
                for (let registration of registrations) registration.unregister();
            });
        }
        // Force reload from server
        setTimeout(() => window.location.reload(true), 500);
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('<?= BASE_URL ?>sw.js?v=' + APP_VERSION, { updateViaCache: 'none' })
            .then(reg => {
                console.log('SW registered:', reg.scope);
                // Force the new SW to activate immediately & catch silent failures
                if (reg && typeof reg.update === 'function') {
                    reg.update().catch(() => {});
                }
            })
            .catch(err => console.log('SW registration failed:', err));
    }
    </script>

    <!-- ===== Global Photo Lightbox ===== -->
    <div id="fullPhotoModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:99998; align-items:center; justify-content:center;" onclick="closeFullPhoto()">
        <!-- Backdrop blur overlay -->
        <div id="fullPhotoBackdrop" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.92);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);"></div>
        <!-- Close button -->
        <button type="button" onclick="closeFullPhoto()" style="position:absolute;top:16px;right:16px;z-index:2;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);color:white;border-radius:50%;width:44px;height:44px;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;backdrop-filter:blur(4px);" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">
            <i class="bi bi-x-lg"></i>
        </button>
        <!-- Zoom hint -->
        <div id="fullPhotoHint" style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.5);font-size:11px;z-index:2;white-space:nowrap;pointer-events:none;transition:opacity 0.5s;">Tap di luar untuk menutup · Cubit untuk zoom</div>
        <!-- Image -->
        <img id="fullPhotoImg" src="" alt="Preview Foto"
             style="position:relative;z-index:1;max-width:92vw;max-height:88vh;object-fit:contain;border-radius:12px;box-shadow:0 24px 80px rgba(0,0,0,0.8);touch-action:pinch-zoom;user-select:none;"
             onclick="event.stopPropagation()">
    </div>
    <style>
    @keyframes _lightboxFadeIn { from { opacity:0; transform:scale(0.94); } to { opacity:1; transform:scale(1); } }
    #fullPhotoModal.is-open { display:flex !important; }
    #fullPhotoModal.is-open #fullPhotoImg { animation: _lightboxFadeIn 0.22s cubic-bezier(.25,.46,.45,.94) forwards; }
    </style>
    <script>
    function viewFullPhoto(src) {
        if (!src || src === window.location.href) return;
        const modal = document.getElementById('fullPhotoModal');
        const img = document.getElementById('fullPhotoImg');
        if (!modal || !img) return;
        img.src = src;
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        // Auto-hide hint after 2.5s
        const hint = document.getElementById('fullPhotoHint');
        if (hint) setTimeout(() => { hint.style.opacity = '0'; }, 2500);
    }
    function closeFullPhoto() {
        const modal = document.getElementById('fullPhotoModal');
        if (!modal) return;
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
        setTimeout(() => {
            const img = document.getElementById('fullPhotoImg');
            if (img) img.src = '';
            const hint = document.getElementById('fullPhotoHint');
            if (hint) hint.style.opacity = '1';
        }, 200);
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeFullPhoto();
    });
    </script>

    <!-- Global Barcode Beep Sound (Web Audio API - realistic supermarket scanner) -->
    <script>
    window.playBarcodeBeep = (function() {
        let _ctx = null;
        function getCtx() {
            if (!_ctx) _ctx = new (window.AudioContext || window.webkitAudioContext)();
            if (_ctx.state === 'suspended') _ctx.resume();
            return _ctx;
        }
        return function playBarcodeBeep() {
            try {
                const ctx = getCtx();
                const now = ctx.currentTime;

                // Supermarket scanner: one sharp clean beep
                // Uses square wave clipped through gain for that classic digital "bip" sound
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                const filter = ctx.createBiquadFilter();

                osc.connect(filter);
                filter.connect(gain);
                gain.connect(ctx.destination);

                osc.type = 'square';          // Square wave = harsh digital
                osc.frequency.setValueAtTime(1760, now); // A6 – typical scanner pitch

                filter.type = 'bandpass';
                filter.frequency.value = 1760;
                filter.Q.value = 6;            // Narrow band = clean tone

                // Attack: almost instant, hold, then fast decay
                gain.gain.setValueAtTime(0, now);
                gain.gain.linearRampToValueAtTime(0.4, now + 0.008);  // 8ms attack
                gain.gain.setValueAtTime(0.4, now + 0.10);             // hold 92ms
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.18); // 80ms release

                osc.start(now);
                osc.stop(now + 0.20);
            } catch(e) { /* Silent fail if audio API not available */ }
        };
    })();
    </script>

    <!-- Activity Logger: fire-and-forget, silent, no impact on UX -->
    <script>
    (function() {
        'use strict';
        <?php $actUserId = (int)($_SESSION['user_id'] ?? 0); ?>
        if (<?= $actUserId ?> === 0) return;
        const _logUrl = BASE_URL + 'api/activity/log';
        const _pageUrl = window.location.href;
        const _pageTitle = document.title;
        function _sendLog(lat, lng) {
            const body = { page_url: _pageUrl, page_title: _pageTitle };
            if (lat != null) { body.lat = lat; body.lng = lng; }
            fetch(_logUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
                keepalive: true
            }).catch(function() {});
        }
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) { _sendLog(pos.coords.latitude, pos.coords.longitude); },
                function()    { _sendLog(null, null); },
                { timeout: 4000, maximumAge: 60000, enableHighAccuracy: false }
            );
        } else {
            _sendLog(null, null);
        }
    })();
    </script>

    <!-- Global CSRF token for inline barcode editing -->
    <?php $__globalCsrf = (new Security())->getCSRFToken(); ?>
    <input type="hidden" id="globalCsrfToken" value="<?= $__globalCsrf ?>">

    <!-- ===== Global Barcode Scan Result Modal ===== -->
    <div id="globalBarcodeScanModal" style="display:none; position:fixed; inset:0; z-index:99990; background:rgba(0,0,0,0.65); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:16px;" onclick="closeGlobalBarcodeModal()">
        <div id="globalBarcodeScanSheet" style="background:var(--surface-1); border:1px solid var(--border-color); border-radius:24px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; padding:24px; box-shadow:0 24px 80px rgba(0,0,0,0.5); animation:popInGlobalModal 0.25s cubic-bezier(0.34,1.56,0.64,1) both;" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid var(--border-color);">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:38px; height:38px; border-radius:12px; background:linear-gradient(135deg, var(--primary), #ef4444); color:white; display:flex; align-items:center; justify-content:center; font-size:1.1rem; box-shadow:0 4px 14px rgba(239,68,68,0.35);">
                        <i class="bi bi-upc-scan"></i>
                    </div>
                    <div>
                        <div style="font-weight:800; font-size:15px; color:var(--text-primary); letter-spacing:-0.2px;">Hasil Pemindaian Barcode</div>
                        <div id="globalBarcodeModalCode" style="font-size:11px; color:var(--text-muted); font-family:monospace; font-weight:600; margin-top:1px;"></div>
                    </div>
                </div>
                <button type="button" onclick="closeGlobalBarcodeModal()" style="background:var(--surface-2); border:1px solid var(--border-color); color:var(--text-muted); border-radius:50%; width:34px; height:34px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; transition:all 0.2s;" onmouseover="this.style.background='var(--danger-bg)';this.style.color='var(--danger)';" onmouseout="this.style.background='var(--surface-2)';this.style.color='var(--text-muted)';">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div id="globalBarcodeModalContent" style="min-height:100px;"></div>
        </div>
    </div>

    <style>
    @keyframes popInGlobalModal {
        from { opacity:0; transform:scale(0.92) translateY(20px); }
        to { opacity:1; transform:scale(1) translateY(0); }
    }
    </style>

    <script>
    // Global escapeHtml utility (used in barcode modal on any page)
    window._safeHtml = function(str) {
        const d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    };

    window.closeGlobalBarcodeModal = function() {
        const modal = document.getElementById('globalBarcodeScanModal');
        if (modal) modal.style.display = 'none';
    };

    window.addScannedProductToPOS = function(productId, productData) {
        try {
            localStorage.setItem('pos_pending_add_product', JSON.stringify({
                id: productId,
                product: productData || null,
                timestamp: Date.now()
            }));
        } catch(e) {}
        
        if (typeof showToast === 'function') {
            showToast('Menambahkan ke Kasir POS...', 'info');
        }
        window.location.href = BASE_URL + 'sales/pos';
    };

    function renderGlobalProductCard(p, isLocal = false) {
        const nameLabel = (p.short_label || p.invoice_name || p.full_name || '-').replace(/</g,'&lt;');
        const fullName  = (p.full_name || '').replace(/</g,'&lt;');
        const brand     = (p.brand_name || '').replace(/</g,'&lt;');
        const category  = (p.category_name || '').replace(/</g,'&lt;');
        const stockQty  = p.current_qty_base !== undefined ? parseInt(p.current_qty_base) : 0;
        
        const photoContent = p.photo 
            ? `<img id="gmodal_photo_img_${p.id}" src="${BASE_URL}${p.photo}" style="width:100%; height:100%; object-fit:contain; border-radius:12px; background:var(--surface-2); padding:4px;">`
            : `<div id="gmodal_photo_placeholder_${p.id}" style="width:100%; height:100%; border-radius:12px; background:var(--primary-bg); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:2rem;"><i class="bi bi-box-seam"></i></div>`;

        const photoHtml = `
            <div style="position:relative; width:76px; height:76px; flex-shrink:0;">
                <div id="gmodal_photo_wrap_${p.id}" onclick="triggerGlobalPhotoUpload(${p.id})" 
                     style="width:76px; height:76px; border-radius:14px; border:1.5px dashed var(--border-color); display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden; position:relative; transition:all 0.2s ease;" 
                     onmouseover="this.style.borderColor='var(--primary)';this.style.transform='scale(1.03)'" 
                     onmouseout="this.style.borderColor='var(--border-color)';this.style.transform='scale(1)'" 
                     title="Klik untuk mengganti foto produk">
                    ${photoContent}
                    <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(15,23,42,0.75); color:white; font-size:9.5px; font-weight:700; text-align:center; padding:3px 0; backdrop-filter:blur(2px); display:flex; align-items:center; justify-content:center; gap:3px;">
                        <i class="bi bi-camera-fill" style="font-size:10px;"></i> Ganti
                    </div>
                </div>
                <input type="file" id="gmodal_photo_input_${p.id}" accept="image/*" style="display:none;" onchange="handleGlobalProductPhotoSelect(event, ${p.id})">
            </div>`;

        const localBadge = isLocal ? `<span style="font-size:10px; background:rgba(234,179,8,0.15); color:#ca8a04; border:1px solid rgba(234,179,8,0.3); border-radius:6px; padding:2px 7px; font-weight:700;">Lokal</span>` : '';

        // Packaging & Prices breakdown with expand/collapse + barcode edit
        let packagingsHtml = '';
        if (p.packagings && Array.isArray(p.packagings) && p.packagings.length > 0) {
            packagingsHtml = p.packagings.map((pkg, idx) => {
                const priceRetail = parseFloat(pkg.sell_price_retail) || 0;
                const priceWholesale = parseFloat(pkg.sell_price_wholesale) || 0;
                const unitName = pkg.unit_name || pkg.unit_abbr || 'Unit';
                const barcode = pkg.barcode || '';
                const hasBarcode = barcode.length > 0;
                const qty = pkg.base_qty || pkg.contained_qty || 1;
                const uid = `gmodal_pkg_${p.id}_${pkg.id}`;
                
                const barcodeIndicator = hasBarcode
                    ? `<span style="font-size:10px; font-family:'JetBrains Mono','Fira Code',monospace; color:var(--text-muted); background:var(--surface-1); border:1px solid var(--border-color); padding:2px 8px; border-radius:5px; max-width:130px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; vertical-align:middle;" title="${window._safeHtml(barcode)}"><i class="bi bi-upc" style="margin-right:3px;"></i>${window._safeHtml(barcode)}</span>`
                    : `<span style="font-size:10px; color:var(--warning); font-weight:700; display:inline-flex; align-items:center; gap:3px;"><i class="bi bi-exclamation-triangle-fill"></i> Belum ada barcode</span>`;
                
                return `
                    <div style="background:var(--surface-2); border:1px solid var(--border-color); border-radius:12px; margin-bottom:8px; overflow:hidden; transition:border-color 0.2s;" id="${uid}_wrap">
                        <!-- Clickable Header (Collapsed View) -->
                        <div onclick="toggleGlobalPkgRow('${uid}')" style="padding:10px 14px; display:flex; justify-content:space-between; align-items:center; gap:8px; cursor:pointer; transition:background 0.15s; user-select:none;" onmouseover="this.style.background='var(--surface-1)'" onmouseout="this.style.background='transparent'">
                            <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                                <div style="width:26px; height:26px; border-radius:7px; background:var(--primary-bg); color:var(--primary); font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;">L${pkg.level || (idx+1)}</div>
                                <div style="min-width:0;">
                                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                        <span style="font-weight:700; font-size:13px; color:var(--text-primary);">${unitName}</span>
                                        <span style="font-size:10px; color:var(--text-muted); background:var(--surface-1); border:1px solid var(--border-color); padding:1px 6px; border-radius:4px;">Isi ${qty} pcs</span>
                                    </div>
                                    <div style="margin-top:3px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">${barcodeIndicator}</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                                <div style="text-align:right;">
                                    <div style="font-weight:800; font-size:13px; color:var(--success);">Rp${priceRetail.toLocaleString('id-ID')}</div>
                                    ${priceWholesale > 0 ? `<div style="font-size:10px; color:var(--warning); font-weight:600;">Grosir: Rp${priceWholesale.toLocaleString('id-ID')}</div>` : ''}
                                </div>
                                <i class="bi bi-chevron-down" id="${uid}_chevron" style="font-size:14px; color:var(--text-muted); transition:transform 0.25s;"></i>
                            </div>
                        </div>
                        <!-- Expandable Barcode Edit Section (Hidden by default) -->
                        <div id="${uid}_body" style="display:none; padding:0 14px 12px; border-top:1px solid var(--border-color);">
                            <div style="margin-top:10px;">
                                <label style="font-size:10.5px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.4px; margin-bottom:5px; display:block;">Barcode ${unitName}</label>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <div style="flex:1; position:relative;">
                                        <input type="text" id="${uid}_input" value="${window._safeHtml(barcode)}" placeholder="Scan atau ketik barcode..." autocomplete="off"
                                            style="width:100%; height:40px; background:var(--bg-input); border:1.5px solid var(--border-color); border-radius:8px; padding:0 36px 0 12px; font-family:'JetBrains Mono','Fira Code',monospace; font-size:13px; color:var(--text-primary); outline:none; transition:border-color 0.2s, box-shadow 0.2s;"
                                            onfocus="this.style.borderColor='var(--primary)';this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.12)'"
                                            onblur="this.style.borderColor='var(--border-color)';this.style.boxShadow='none'">
                                        <i class="bi bi-camera-fill" 
                                           onclick="if (typeof BarcodeUtil !== 'undefined' && BarcodeUtil.scanBarcode) { BarcodeUtil.scanBarcode(document.getElementById('${uid}_input')); } else if (typeof showToast === 'function') { showToast('Kamera tidak tersedia', 'error'); }" 
                                           style="position:absolute; right:10px; top:50%; transform:translateY(-50%); color:var(--primary); font-size:1.15rem; cursor:pointer; padding:4px; opacity:0.9; transition:all 0.15s;" 
                                           onmouseover="this.style.opacity='1';this.style.transform='translateY(-50%) scale(1.18)';" 
                                           onmouseout="this.style.opacity='0.9';this.style.transform='translateY(-50%) scale(1)';" 
                                           title="Klik ikon ini untuk pindai barcode dengan kamera HP"></i>
                                    </div>
                                    <button type="button" onclick="document.getElementById('${uid}_input').value='';document.getElementById('${uid}_input').focus();" style="width:40px; height:40px; min-width:40px; display:flex; align-items:center; justify-content:center; background:transparent; border:1.5px solid var(--border-color); border-radius:8px; color:var(--text-muted); cursor:pointer; transition:all 0.15s; font-size:14px;" onmouseover="this.style.borderColor='var(--danger)';this.style.color='var(--danger)';this.style.background='var(--danger-bg)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-muted)';this.style.background='transparent'">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }).join('');
        } else if (p.price_small_retail) {
            const price = parseInt(p.price_small_retail);
            packagingsHtml = `
                <div style="background:var(--surface-2); border:1px solid var(--border-color); border-radius:12px; padding:10px 14px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:700; font-size:13px; color:var(--text-primary);">Harga Eceran</span>
                    <span style="font-weight:800; font-size:15px; color:var(--success);">Rp${price.toLocaleString('id-ID')}</span>
                </div>`;
        }

        // Save button (only if product has packagings)
        const saveBarcodeBtn = (p.packagings && p.packagings.length > 0) ? `
            <button type="button" id="gmodal_save_bc_${p.id}" onclick="saveGlobalModalBarcodes(${p.id})" style="width:100%; padding:10px; font-size:12px; font-weight:700; border:none; border-radius:10px; background:var(--primary); color:white; display:none; align-items:center; justify-content:center; gap:6px; cursor:pointer; margin-top:4px; transition:opacity 0.15s, transform 0.15s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                <i class="bi bi-check-circle-fill"></i> Simpan Barcode
            </button>` : '';

        return `
            <div style="display:flex; gap:16px; align-items:flex-start; margin-bottom:16px;">
                ${photoHtml}
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:4px;">
                        <span style="font-weight:800; font-size:16px; color:var(--text-primary); line-height:1.3;">${nameLabel}</span>
                        ${localBadge}
                    </div>
                    ${fullName && fullName !== nameLabel ? `<div style="font-size:12px; color:var(--text-muted); margin-bottom:6px; line-height:1.3;">${fullName}</div>` : ''}
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:6px;">
                        ${brand ? `<span style="font-size:10px; background:var(--surface-2); border:1px solid var(--border-color); padding:3px 8px; border-radius:6px; color:var(--text-secondary); font-weight:600;"><i class="bi bi-tag-fill text-muted"></i> ${brand}</span>` : ''}
                        ${category ? `<span style="font-size:10px; background:var(--surface-2); border:1px solid var(--border-color); padding:3px 8px; border-radius:6px; color:var(--text-secondary); font-weight:600;"><i class="bi bi-folder-fill text-muted"></i> ${category}</span>` : ''}
                        <span style="font-size:10px; background:${stockQty > 0 ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)'}; color:${stockQty > 0 ? 'var(--success)' : 'var(--danger)'}; border:1px solid ${stockQty > 0 ? 'rgba(34,197,94,0.25)' : 'rgba(239,68,68,0.25)'}; padding:3px 8px; border-radius:6px; font-weight:700;">
                            <i class="bi bi-boxes"></i> Stok: ${stockQty} pcs
                        </span>
                    </div>
                </div>
            </div>

            <!-- Price Breakdown Section -->
            <div style="margin-bottom:20px;">
                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-box-seam" style="font-size:12px;"></i> Daftar Harga & Level Kemasan
                    <span style="font-size:9px; font-weight:600; color:var(--primary); background:var(--primary-bg); padding:2px 6px; border-radius:4px;">Klik untuk edit barcode</span>
                </div>
                ${packagingsHtml || '<div style="font-size:12px; color:var(--text-muted); padding:10px; text-align:center;">Belum ada tingkat harga</div>'}
                ${saveBarcodeBtn}
            </div>

            <!-- Action Buttons Footer -->
            <div style="display:flex; gap:10px; margin-top:18px;">
                <button type="button" onclick="addScannedProductToPOS(${p.id})" class="btn-primary-custom" style="flex:1.3; padding:12px; font-size:13px; font-weight:700; border:none; border-radius:12px; background:linear-gradient(135deg, #10b981, #059669); color:white; display:flex; align-items:center; justify-content:center; gap:6px; cursor:pointer; box-shadow:0 4px 14px rgba(16,185,129,0.3); transition:transform 0.15s;" onactive="this.style.transform='scale(0.97)'">
                    <i class="bi bi-cart-plus-fill" style="font-size:1.1rem;"></i> Tambah ke Keranjang POS
                </button>
                <a href="${BASE_URL}products/${p.id}/edit" class="btn-outline-custom" style="flex:1; padding:12px; font-size:13px; font-weight:700; border:1px solid var(--border-color); border-radius:12px; background:var(--surface-2); color:var(--text-primary); display:flex; align-items:center; justify-content:center; gap:6px; text-decoration:none; transition:transform 0.15s;" onactive="this.style.transform='scale(0.97)'">
                    <i class="bi bi-pencil-square" style="font-size:1.05rem; color:var(--primary);"></i> Edit Produk
                </a>
            </div>`;
    }

    // Track current product data shown in the modal for barcode save
    window._currentGlobalModalProduct = null;

    // Trigger photo input file picker
    window.triggerGlobalPhotoUpload = function(productId) {
        const input = document.getElementById('gmodal_photo_input_' + productId);
        if (input) input.click();
    };

    // Handle photo file selection, compression, and saving
    window.handleGlobalProductPhotoSelect = async function(event, productId) {
        const file = event.target.files[0];
        if (!file) return;

        const product = window._currentGlobalModalProduct;
        const wrap = document.getElementById('gmodal_photo_wrap_' + productId);
        if (!wrap) return;

        const origContent = wrap.innerHTML;
        wrap.innerHTML = `<div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; font-size:10px; color:var(--primary); font-weight:700;"><span class="spinner-border spinner-border-sm"></span>Simpan...</div>`;

        try {
            // Compress Image helper using Canvas (max 800px)
            const compressedDataUrl = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;
                        const maxDim = 800;
                        if (width > maxDim || height > maxDim) {
                            if (width > height) {
                                height = Math.round((height * maxDim) / width);
                                width = maxDim;
                            } else {
                                width = Math.round((width * maxDim) / height);
                                height = maxDim;
                            }
                        }
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, width, height);
                        ctx.drawImage(img, 0, 0, width, height);

                        // Preserve alpha transparency for PNG / WebP / GIF / SVG images
                        const isTransparent = file.type === 'image/png' || file.type === 'image/webp' || file.type === 'image/gif' || file.type === 'image/svg+xml' || (file.name && /\.png$/i.test(file.name));
                        const outputType = isTransparent ? 'image/png' : 'image/jpeg';
                        resolve(canvas.toDataURL(outputType, 0.9));
                    };
                    img.onerror = reject;
                    img.src = e.target.result;
                };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });

            // Get CSRF token
            let csrf = '';
            const csrfEl = document.getElementById('csrfToken') || document.getElementById('globalCsrfToken') || document.querySelector('input[name="csrf_token"]');
            if (csrfEl) csrf = csrfEl.value;

            const resp = await fetch(`${BASE_URL}api/products/${productId}/photo`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                credentials: 'same-origin',
                body: JSON.stringify({ photo_base64: compressedDataUrl, csrf_token: csrf })
            });

            const res = await resp.json();
            if (!resp.ok || !res.success) throw new Error(res.message || 'Gagal menyimpan foto');

            const photoPath = res.photo;

            // Update in-memory product photo
            if (product) product.photo = photoPath;

            // Update wrap HTML with new photo
            wrap.innerHTML = `
                <img id="gmodal_photo_img_${productId}" src="${BASE_URL}${photoPath}?t=${Date.now()}" style="width:100%; height:100%; object-fit:contain; border-radius:12px; background:var(--surface-2); padding:4px;">
                <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(15,23,42,0.75); color:white; font-size:9.5px; font-weight:700; text-align:center; padding:3px 0; backdrop-filter:blur(2px); display:flex; align-items:center; justify-content:center; gap:3px;">
                    <i class="bi bi-camera-fill" style="font-size:10px;"></i> Ganti
                </div>
            `;

            if (typeof showToast === 'function') showToast('Foto produk berhasil diperbarui!', 'success');

            // Save to IndexedDB offline cache
            if (typeof OfflineDB !== 'undefined' && OfflineDB.saveProduct && product) {
                OfflineDB.saveProduct(product).catch(() => {});
            }

        } catch (err) {
            wrap.innerHTML = origContent;
            if (typeof showToast === 'function') showToast(err.message || 'Gagal mengganti foto produk', 'error');
        }
    };

    // Toggle expand/collapse for a packaging row in the scan result modal
    window.toggleGlobalPkgRow = function(uid) {
        const body = document.getElementById(uid + '_body');
        const chevron = document.getElementById(uid + '_chevron');
        const wrap = document.getElementById(uid + '_wrap');
        if (!body) return;

        const isOpen = body.style.display !== 'none';
        body.style.display = isOpen ? 'none' : 'block';
        if (chevron) chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        if (wrap) wrap.style.borderColor = isOpen ? 'var(--border-color)' : 'rgba(99,102,241,0.4)';

        // Show save button when any row is expanded
        if (!isOpen) {
            const product = window._currentGlobalModalProduct;
            if (product) {
                const saveBtn = document.getElementById('gmodal_save_bc_' + product.id);
                if (saveBtn) saveBtn.style.display = 'flex';
            }
            // Auto focus the barcode input
            const input = document.getElementById(uid + '_input');
            if (input) setTimeout(() => input.focus(), 80);
        }
    };

    // Save barcodes inline from global scan result modal
    window.saveGlobalModalBarcodes = async function(productId) {
        const product = window._currentGlobalModalProduct;
        if (!product || !product.packagings) return;

        const btn = document.getElementById('gmodal_save_bc_' + productId);
        if (!btn) return;
        const origHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

        try {
            const barcodes = {};
            product.packagings.forEach(pkg => {
                const input = document.getElementById(`gmodal_pkg_${productId}_${pkg.id}_input`);
                if (input) barcodes[pkg.id] = input.value.trim();
            });

            // Get CSRF token from page
            let csrf = '';
            const csrfEl = document.getElementById('csrfToken') || document.getElementById('globalCsrfToken');
            if (csrfEl) csrf = csrfEl.value;

            const resp = await fetch(`${BASE_URL}api/products/${productId}/update-barcodes`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                credentials: 'same-origin',
                body: JSON.stringify({ barcodes, csrf_token: csrf })
            });
            const res = await resp.json();

            if (resp.status === 409 || res.error === 'barcode_conflict') {
                if (typeof showToast === 'function') showToast(res.message || 'Barcode sudah digunakan produk lain!', 'error');
                return;
            }
            if (!resp.ok || res.error) throw new Error(res.error || res.message || 'Gagal');

            if (typeof showToast === 'function') showToast('Barcode berhasil disimpan!', 'success');

            // Update local product data
            product.packagings.forEach(pkg => {
                const input = document.getElementById(`gmodal_pkg_${productId}_${pkg.id}_input`);
                if (input) pkg.barcode = input.value.trim();
            });

            // Refresh OfflineDB cache
            if (typeof OfflineDB !== 'undefined' && OfflineDB.saveProduct) {
                OfflineDB.saveProduct(product).catch(() => {});
            }

            // Briefly flash success
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Tersimpan!';
            btn.style.background = 'var(--success)';
            setTimeout(() => {
                btn.innerHTML = origHTML;
                btn.style.background = 'var(--primary)';
            }, 2000);

        } catch(err) {
            if (typeof showToast === 'function') showToast(err.message || 'Gagal menyimpan barcode', 'error');
            btn.innerHTML = origHTML;
        } finally {
            btn.disabled = false;
        }
    };

    window.showGlobalBarcodeModal = async function(code) {
        const modal = document.getElementById('globalBarcodeScanModal');
        const content = document.getElementById('globalBarcodeModalContent');
        const codeEl = document.getElementById('globalBarcodeModalCode');

        if (!modal || !content || !codeEl) return;

        codeEl.textContent = code;
        modal.style.display = 'flex';

        if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();

        // 1. Check local IndexedDB first for INSTANT 0ms display
        let localProduct = null;
        if (typeof OfflineDB !== 'undefined') {
            try { localProduct = await OfflineDB.findByBarcode(code); } catch(e) {}
        }

        if (localProduct && localProduct.id) {
            window._currentGlobalModalProduct = localProduct;
            content.innerHTML = renderGlobalProductCard(localProduct, true);

            // Background server refresh
            try {
                const ctrl = new AbortController();
                setTimeout(() => ctrl.abort(), 4000);
                const res = await fetch(`${BASE_URL}api/products/barcode/${encodeURIComponent(code)}`, {
                    credentials: 'same-origin', signal: ctrl.signal
                });
                if (res.ok) {
                    const fresh = await res.json();
                    if (fresh && fresh.id) {
                        window._currentGlobalModalProduct = fresh;
                        content.innerHTML = renderGlobalProductCard(fresh, false);
                        if (typeof OfflineDB !== 'undefined' && OfflineDB.saveProduct) {
                            OfflineDB.saveProduct(fresh).catch(() => {});
                        }
                    }
                }
            } catch(e) {}
            return;
        }

        // 2. Not in local DB -> Show spinner and fetch from server
        content.innerHTML = `<div style="text-align:center; padding:36px 12px;"><div class="spinner-border text-primary" role="status"></div><div style="font-size:13px; color:var(--text-muted); margin-top:12px; font-weight:600;">Mencari produk di server...</div></div>`;

        try {
            const ctrl = new AbortController();
            setTimeout(() => ctrl.abort(), 4500);
            const res = await fetch(`${BASE_URL}api/products/barcode/${encodeURIComponent(code)}`, {
                credentials: 'same-origin', signal: ctrl.signal
            });
            if (res.ok) {
                const data = await res.json();
                if (data && data.id) {
                    window._currentGlobalModalProduct = data;
                    content.innerHTML = renderGlobalProductCard(data, false);
                    if (typeof OfflineDB !== 'undefined' && OfflineDB.saveProduct) {
                        OfflineDB.saveProduct(data).catch(() => {});
                    }
                    return;
                }
            }
        } catch(e) {}

        // 3. Not found state
        content.innerHTML = `
            <div style="text-align:center; padding:32px 12px;">
                <div style="width:56px; height:56px; border-radius:50%; background:var(--surface-2); color:var(--text-muted); display:inline-flex; align-items:center; justify-content:center; font-size:1.8rem; margin-bottom:12px;">
                    <i class="bi bi-search"></i>
                </div>
                <div style="font-weight:800; font-size:15px; color:var(--text-primary);">Produk Tidak Ditemukan</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Kode barcode: <span style="font-family:monospace; font-weight:600;">${window._safeHtml ? window._safeHtml(code) : code}</span></div>
            </div>`;
    };
    </script>

</body>
</html>
