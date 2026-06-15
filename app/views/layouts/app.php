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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
    
    <!-- App CSS -->
    <?php $v = '?v=11.5'; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/variables.css<?= $v ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/app.css<?= $v ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/components.css<?= $v ?>">

    <link rel="manifest" href="<?= BASE_URL ?>manifest.json<?= $v ?>">
</head>
<body>

    <!-- Elegant App Loader -->
    <style>
        @keyframes pulse-glow {
            0% { opacity: 0.8; transform: scale(0.98); text-shadow: 0 0 10px rgba(var(--primary-rgb), 0.2); }
            50% { opacity: 1; transform: scale(1.02); text-shadow: 0 0 25px rgba(var(--primary-rgb), 0.6); }
            100% { opacity: 0.8; transform: scale(0.98); text-shadow: 0 0 10px rgba(var(--primary-rgb), 0.2); }
        }
        .splash-text {
            animation: pulse-glow 2s infinite ease-in-out;
        }
    </style>
    <div id="appInitLoader" style="position:fixed;top:0;left:0;right:0;bottom:0;background:var(--bg-default);z-index:99999;display:flex;flex-direction:column;justify-content:center;align-items:center;transition:opacity 0.5s ease;">
        <div class="elegant-loader">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <div class="splash-text" style="margin-top:20px;font-weight:800;font-size:1.8rem;letter-spacing:1px;color:var(--text-primary);">Alfarez<span style="color:var(--primary);">Mart</span></div>
    </div>
    <script>
    function hideAppLoader() {
        const loader = document.getElementById('appInitLoader');
        if (!loader || loader.dataset.hidden === '1') return;
        loader.dataset.hidden = '1';
        loader.style.pointerEvents = 'none';
        loader.style.opacity = '0';
        setTimeout(() => { loader.style.display = 'none'; }, 500);
    }
    window.addEventListener('load', hideAppLoader);
    document.addEventListener('DOMContentLoaded', hideAppLoader);
    setTimeout(hideAppLoader, 2500);
    </script>

    <!-- Status Bar Overlay (Android-like) -->
    <div class="status-bar-overlay"></div>

    <!-- Offline Banner -->
    <div id="offlineBanner" style="display:none; text-align:center; padding:4px; font-size:10px; color:var(--warning); font-weight:700;">
        <i class="bi bi-wifi-off" style="margin-right:4px;"></i> Mode Offline
    </div>

    <!-- App Header -->
    <header class="app-header" id="appHeader">
        <div class="header-content">
            <div class="header-left">
                <img src="<?= BASE_URL ?>public/images/Icon.png" alt="AlfarezMart" class="header-logo" width="32" height="32">
                <h1 class="header-title"><?= htmlspecialchars($title ?? 'AlfarezMart') ?></h1>
            </div>
            <div class="header-right">
                <button class="header-btn" id="btnTheme" aria-label="Ubah tema" onclick="toggleTheme()" title="Ubah tema Dark/Light">
                    <i class="bi bi-sun" id="themeIcon" data-icon="sun"></i>
                </button>
                <button class="header-btn" id="btnSync" aria-label="Sinkronisasi" onclick="triggerSync()" oncontextmenu="openSyncSettings(event)" ontouchstart="startSyncSettingsTimer(event)" ontouchend="clearSyncSettingsTimer(event)" title="Tahan untuk Pengaturan Sync">
                    <i class="bi bi-arrow-repeat" id="syncIcon"></i>
                    <span class="notif-badge" id="offlineSyncBadge" style="display:none">0</span>
                </button>
                <button class="header-btn" id="btnSearch" aria-label="Cari">
                    <i class="bi bi-search"></i>
                </button>
                <button class="header-btn" id="btnNotif" aria-label="Notifikasi">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge" id="notifBadge" style="display:none">0</span>
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
        <?= $content ?? '' ?>
    </main>

    <!-- Floating AI Chat Button -->
    <?php if (!isset($active_menu) || $active_menu !== 'chat'): ?>
        <?php 
            // Check if feature is enabled (avoid DB call if possible, maybe rely on session or just default true)
            // It's a view, so we will just show it and handle the check in Controller
        ?>
        <a href="<?= BASE_URL ?>chat" class="floating-chat-btn" title="Tanya AI">
            <div class="chat-icon-wrapper">
                🤖
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
                <div class="modal-footer" style="border-top:1px solid var(--border-color);">
                    <button type="button" class="btn-primary-custom w-100" onclick="forceManualSync()" style="padding:10px;font-weight:600;">
                        <i class="bi bi-arrow-repeat"></i> Mulai Sinkronisasi Sekarang
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
        const version = '11.20';
    </script>
    <script src="<?= BASE_URL ?>public/js/utils.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/dexie.min.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/db.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/printer.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/barcode.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/packaging-prices.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/qty-pricing.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/components.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/xlsx.full.min.js"></script>
    <script src="<?= BASE_URL ?>public/js/exceljs.min.js"></script>
    <script>
        // Injeksi konfigurasi geofencing untuk staff
        window.GEO_CONFIG = {
            enabled: true,
            role: '<?= $userLevel ?>',
            lat: '<?= $geoLat ?>',
            lng: '<?= $geoLng ?>',
            radius: '<?= $geoRadius ?>',
            logoutUrl: '<?= BASE_URL ?>auth/logout'
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
    <script src="<?= BASE_URL ?>public/js/app.js<?= $v ?>"></script>

    
    <!-- Service Worker Registration & Cache Buster -->
    <script>
    const APP_VERSION = '11.20'; // Update this to force client reloads
    
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
        navigator.serviceWorker.register('<?= BASE_URL ?>sw.js')
            .then(reg => console.log('SW registered:', reg.scope))
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

</body>
</html>
