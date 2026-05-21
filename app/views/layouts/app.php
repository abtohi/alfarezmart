<!DOCTYPE html>
<html lang="id">
<head>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- App CSS -->
    <?php $v = '?v=4.0'; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/variables.css<?= $v ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/app.css<?= $v ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/components.css<?= $v ?>">
</head>
<body>

    <!-- Elegant App Loader -->
    <div id="appInitLoader" style="position:fixed;top:0;left:0;right:0;bottom:0;background:var(--bg-default);z-index:99999;display:flex;flex-direction:column;justify-content:center;align-items:center;transition:opacity 0.5s ease;">
        <div class="elegant-loader">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <div style="margin-top:20px;font-weight:700;font-size:1.2rem;letter-spacing:1px;color:var(--text-primary);">Alfarez<span style="color:var(--primary);">Mart</span></div>
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

    <!-- App Header -->
    <header class="app-header" id="appHeader">
        <div class="header-content">
            <div class="header-left">
                <img src="<?= BASE_URL ?>public/images/Icon.png" alt="AlfarezMart" class="header-logo" width="32" height="32">
                <h1 class="header-title"><?= htmlspecialchars($title ?? 'AlfarezMart') ?></h1>
            </div>
            <div class="header-right">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- App JS -->
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="<?= BASE_URL ?>public/js/utils.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/printer.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/barcode.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/packaging-prices.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/qty-pricing.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/components.js<?= $v ?>"></script>
    <script src="<?= BASE_URL ?>public/js/app.js<?= $v ?>"></script>
    
    <!-- Service Worker Registration -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('<?= BASE_URL ?>sw.js')
            .then(reg => console.log('SW registered:', reg.scope))
            .catch(err => console.log('SW registration failed:', err));
    }
    </script>
</body>
</html>
