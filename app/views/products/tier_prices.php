<!-- View: Manajemen Harga Tier (Grosir Bertingkat) -->
<?php /** @var string $csrfToken */ ?>

<style>
/* ===== TIER PRICES MANAGEMENT STYLES ===== */
.tp-container {
    max-width: 1200px;
    margin: 0 auto;
    padding-bottom: 120px;
}

/* Header */
.tp-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}
.tp-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.tp-header-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(239, 68, 68, 0.15));
    color: #f59e0b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.18);
    flex-shrink: 0;
}
.tp-header-title {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.25;
}
.tp-header-subtitle {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin: 4px 0 0 0;
}
.tp-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Stats Summary Cards */
.tp-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.tp-stat-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}
.tp-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.tp-stat-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.1;
}
.tp-stat-label {
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-top: 3px;
    font-weight: 600;
}

/* Filter Card */
.tp-filter-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px 20px;
    margin-bottom: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
}
.tp-filter-controls {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    align-items: center;
}
.tp-search-wrapper {
    flex: 1;
    min-width: 260px;
    position: relative;
}
.tp-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 1.05rem;
    pointer-events: none;
}
.tp-search-input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.92rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.tp-search-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}
.tp-cat-filter {
    min-width: 190px;
    padding: 10px 14px;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.92rem;
    outline: none;
    cursor: pointer;
}

/* Category Accordion / Group */
.tp-cat-group {
    margin-bottom: 28px;
}
.tp-cat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    user-select: none;
    margin-bottom: 14px;
    transition: background-color 0.2s;
}
.tp-cat-header:hover {
    background: var(--surface-3, rgba(255,255,255,0.06));
}
.tp-cat-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-primary);
}
.tp-cat-badge {
    font-size: 0.75rem;
    padding: 2px 10px;
    border-radius: 20px;
    background: rgba(99,102,241,0.12);
    color: var(--primary);
    font-weight: 700;
}
.tp-cat-chevron {
    color: var(--text-muted);
    font-size: 1.1rem;
    transition: transform 0.25s;
}
.tp-cat-group.collapsed .tp-cat-chevron {
    transform: rotate(-90deg);
}
.tp-cat-group.collapsed .tp-cat-content {
    display: none;
}

/* Product Card */
.tp-product-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    margin-bottom: 18px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    transition: border-color 0.2s;
}
.tp-product-card:hover {
    border-color: rgba(99,102,241,0.4);
}
.tp-product-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border-color);
    gap: 12px;
    flex-wrap: wrap;
}
.tp-product-info {
    display: flex;
    align-items: center;
    gap: 14px;
    flex: 1;
    min-width: 250px;
}
.tp-product-thumb {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    object-fit: cover;
    background: var(--surface-3, #f1f5f9);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 1.2rem;
    flex-shrink: 0;
}
.tp-product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 9px;
}
.tp-product-name {
    font-size: 1.02rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.3;
    margin: 0;
}
.tp-product-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-top: 3px;
    flex-wrap: wrap;
}
.tp-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 1px 8px;
    border-radius: 4px;
    font-size: 0.72rem;
    font-weight: 600;
    background: rgba(148, 163, 184, 0.15);
    color: var(--text-secondary, #94a3b8);
}
.tp-tag-brand {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}
.tp-tag-stock {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}

/* Packaging Block */
.tp-packaging-box {
    padding: 16px 18px;
    border-bottom: 1px dashed var(--border-color);
}
.tp-packaging-box:last-child {
    border-bottom: none;
}
.tp-packaging-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 14px;
}
.tp-packaging-title {
    display: flex;
    align-items: center;
    gap: 8px;
}
.tp-pkg-level-badge {
    font-size: 0.74rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 6px;
    background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(168,85,247,0.15));
    color: var(--primary);
    border: 1px solid rgba(99,102,241,0.25);
}
.tp-pkg-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
}
.tp-pkg-barcode {
    font-size: 0.78rem;
    color: var(--text-muted);
    font-family: monospace;
}

/* Base Price Strip */
.tp-base-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 12px;
    background: var(--surface-2);
    padding: 12px 14px;
    border-radius: var(--radius-md);
    margin-bottom: 14px;
    border: 1px solid var(--border-color);
}
.tp-base-item {
    display: flex;
    flex-direction: column;
}
.tp-base-label {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 3px;
}
.tp-base-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
}
.tp-base-value.cost {
    color: #ef4444;
}
.tp-base-value.profit {
    color: #10b981;
}
.tp-base-input-wrap {
    display: flex;
    align-items: center;
    position: relative;
}
.tp-base-input-prefix {
    position: absolute;
    left: 8px;
    font-size: 0.78rem;
    color: var(--text-muted);
    font-weight: 600;
    pointer-events: none;
}
.tp-base-input {
    width: 100%;
    padding: 4px 8px 4px 30px;
    border: 1.5px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 700;
    outline: none;
    transition: border-color 0.2s;
}
.tp-base-input:focus {
    border-color: var(--primary);
}

/* Tier Table */
.tp-table-wrap {
    overflow-x: auto;
    margin-bottom: 14px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
}
.tp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.86rem;
    text-align: left;
    min-width: 680px;
}
.tp-table th {
    background: var(--surface-2);
    padding: 10px 12px;
    font-weight: 700;
    color: var(--text-muted);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border-color);
    white-space: nowrap;
}
.tp-table td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}
.tp-table tbody tr:last-child td {
    border-bottom: none;
}
.tp-table tbody tr:hover {
    background: rgba(255,255,255,0.02);
}

/* Tier Table Inputs */
.tp-inp-qty {
    width: 85px;
    padding: 6px 8px;
    border: 1.5px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    border-radius: 6px;
    font-weight: 700;
    text-align: center;
    outline: none;
}
.tp-inp-qty:focus {
    border-color: var(--primary);
}
.tp-inp-price-wrap {
    display: inline-flex;
    align-items: center;
    position: relative;
    width: 130px;
}
.tp-inp-price-prefix {
    position: absolute;
    left: 8px;
    font-size: 0.78rem;
    color: var(--text-muted);
    font-weight: 600;
    pointer-events: none;
}
.tp-inp-price {
    width: 100%;
    padding: 6px 8px 6px 30px;
    border: 1.5px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    border-radius: 6px;
    font-weight: 700;
    outline: none;
}
.tp-inp-price:focus {
    border-color: var(--primary);
}

/* Profit & Info Pills */
.tp-profit-pill {
    display: inline-flex;
    flex-direction: column;
    gap: 2px;
}
.tp-profit-val {
    font-weight: 800;
    font-size: 0.9rem;
    color: #10b981;
}
.tp-profit-val.loss {
    color: #ef4444;
}
.tp-profit-sub {
    font-size: 0.74rem;
    color: var(--text-muted);
}
.tp-markup-badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 0.74rem;
    font-weight: 700;
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}
.tp-markup-badge.loss {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

/* Tier Actions Bottom Bar */
.tp-pkg-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

/* Buttons */
.tp-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: var(--radius-md);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
}
.tp-btn-primary {
    background: var(--primary);
    color: #fff;
}
.tp-btn-primary:hover {
    background: var(--primary-hover, #4f46e5);
    transform: translateY(-1px);
}
.tp-btn-outline {
    background: transparent;
    border-color: var(--border-color);
    color: var(--text-primary);
}
.tp-btn-outline:hover {
    background: var(--surface-2);
    border-color: var(--primary);
    color: var(--primary);
}
.tp-btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
}
.tp-btn-success:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}
.tp-btn-sm {
    padding: 4px 10px;
    font-size: 0.78rem;
}
.tp-btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}
.tp-btn-del {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
}
.tp-btn-del:hover {
    background: #ef4444;
    color: #fff;
}

/* Modal Styles */
.tp-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.65);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 20px;
}
.tp-modal-backdrop.show {
    display: flex;
}
.tp-modal-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 620px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 16px 40px rgba(0,0,0,0.3);
    animation: tpModalSlide 0.25s ease-out;
}
@keyframes tpModalSlide {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.tp-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
}
.tp-modal-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tp-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}
.tp-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid var(--border-color);
    background: var(--surface-2);
}

/* Search results in modal */
.tp-modal-res-list {
    max-height: 240px;
    overflow-y: auto;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    margin-top: 10px;
    background: var(--surface-2);
}
.tp-modal-res-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: background-color 0.15s;
}
.tp-modal-res-item:last-child {
    border-bottom: none;
}
.tp-modal-res-item:hover {
    background: rgba(99,102,241,0.08);
}
.tp-modal-res-item.selected {
    background: rgba(99,102,241,0.15);
    border-left: 3px solid var(--primary);
}

/* Empty State */
.tp-empty-box {
    text-align: center;
    padding: 60px 20px;
    background: var(--surface-1);
    border: 1.5px dashed var(--border-color);
    border-radius: var(--radius-lg);
    margin: 20px 0;
}
.tp-empty-icon {
    font-size: 3rem;
    color: var(--text-muted);
    margin-bottom: 12px;
}
.tp-empty-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}
.tp-empty-desc {
    font-size: 0.85rem;
    color: var(--text-muted);
    max-width: 420px;
    margin: 0 auto 18px auto;
}

/* Toast/indicator helper */
.tp-save-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    color: #10b981;
    font-weight: 700;
    opacity: 0;
    transition: opacity 0.3s;
}
.tp-save-indicator.show {
    opacity: 1;
}

@media (max-width: 768px) {
    .tp-header {
        flex-direction: column;
        align-items: stretch;
    }
    .tp-header-actions {
        justify-content: stretch;
    }
    .tp-header-actions .tp-btn {
        flex: 1;
        justify-content: center;
    }
    .tp-filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    .tp-search-wrapper, .tp-cat-filter {
        width: 100%;
        min-width: 0;
    }
    .tp-base-strip {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<div class="tp-container">
    <!-- Hidden CSRF Token -->
    <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

    <!-- Header -->
    <div class="tp-header">
        <div class="tp-header-left">
            <div class="tp-header-icon">
                <i class="bi bi-tags-fill"></i>
            </div>
            <div>
                <h1 class="tp-header-title">Manajemen Harga Tier</h1>
                <p class="tp-header-subtitle">Kelola harga grosir bertingkat, pantau modal, selisih margin, dan keuntungan per kemasan.</p>
            </div>
        </div>
        <div class="tp-header-actions">
            <button type="button" class="tp-btn tp-btn-primary" onclick="openAddProductModal()">
                <i class="bi bi-plus-circle-fill"></i> Tambah Produk ke Tier
            </button>
            <button type="button" class="tp-btn tp-btn-outline" onclick="loadTierProducts()" title="Muat Ulang Data">
                <i class="bi bi-arrow-clockwise" id="btnRefreshIcon"></i> Segarkan
            </button>
        </div>
    </div>

    <!-- Stats Summary Row -->
    <div class="tp-stats-row">
        <div class="tp-stat-card">
            <div class="tp-stat-icon" style="background: rgba(99,102,241,0.12); color: #818cf8;">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div>
                <div class="tp-stat-value" id="statProductCount">0</div>
                <div class="tp-stat-label">Produk Ber-Tier</div>
            </div>
        </div>
        <div class="tp-stat-card">
            <div class="tp-stat-icon" style="background: rgba(16,185,129,0.12); color: #10b981;">
                <i class="bi bi-layers-fill"></i>
            </div>
            <div>
                <div class="tp-stat-value" id="statTierCount">0</div>
                <div class="tp-stat-label">Total Aturan Tier</div>
            </div>
        </div>
        <div class="tp-stat-card">
            <div class="tp-stat-icon" style="background: rgba(245,158,11,0.12); color: #f59e0b;">
                <i class="bi bi-grid-fill"></i>
            </div>
            <div>
                <div class="tp-stat-value" id="statCategoryCount">0</div>
                <div class="tp-stat-label">Kategori</div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="tp-filter-card">
        <div class="tp-filter-controls">
            <div class="tp-search-wrapper">
                <i class="bi bi-search tp-search-icon"></i>
                <input type="text" id="tpSearchInput" class="tp-search-input" placeholder="Cari nama produk, barcode, SKU..." oninput="onFilterChange()">
            </div>
            <select id="tpCategorySelect" class="tp-cat-filter" onchange="onFilterChange()">
                <option value="">Semua Kategori</option>
            </select>
        </div>
    </div>

    <!-- Loading State -->
    <div id="tpLoadingState" style="text-align: center; padding: 50px 0;">
        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
        <div style="margin-top: 12px; color: var(--text-muted); font-size: 0.9rem;">Memuat daftar harga tier produk...</div>
    </div>

    <!-- Container where category groups and product cards render -->
    <div id="tpProductsContainer" style="display: none;"></div>

    <!-- Empty State -->
    <div id="tpEmptyState" class="tp-empty-box" style="display: none;">
        <div class="tp-empty-icon"><i class="bi bi-tags"></i></div>
        <div class="tp-empty-title">Belum Ada Produk Dengan Harga Tier</div>
        <p class="tp-empty-desc">Belum ada produk yang diset memiliki harga grosir bertingkat (tier price). Klik tombol di bawah untuk menambahkan produk pertama ke tier pricing.</p>
        <button type="button" class="tp-btn tp-btn-primary" onclick="openAddProductModal()">
            <i class="bi bi-plus-circle-fill"></i> Tambah Produk ke Tier Sekarang
        </button>
    </div>
</div>

<!-- MODAL: Tambah Produk ke Tier -->
<div class="tp-modal-backdrop" id="addProductModal">
    <div class="tp-modal-card">
        <div class="tp-modal-header">
            <h3 class="tp-modal-title">
                <i class="bi bi-tags-fill" style="color: #f59e0b;"></i> Tambah Produk ke Tier Pricing
            </h3>
            <button type="button" class="btn-close" onclick="closeAddProductModal()" style="background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="tp-modal-body">
            <div style="margin-bottom: 14px;">
                <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; display: block;">
                    Cari Produk (Nama / Barcode / Kode)
                </label>
                <div class="tp-search-wrapper" style="width: 100%;">
                    <i class="bi bi-search tp-search-icon"></i>
                    <input type="text" id="modalProductSearch" class="tp-search-input" placeholder="Ketik minimal 1 karakter..." oninput="debounceModalSearch()">
                </div>
                <div id="modalSearchSpinner" style="display: none; font-size: 0.8rem; color: var(--text-muted); margin-top: 6px;">
                    <span class="spinner-border spinner-border-sm"></span> Mencari produk...
                </div>
                <div id="modalSearchResults" class="tp-modal-res-list" style="display: none;"></div>
            </div>

            <!-- Detail produk terpilih -->
            <div id="modalSelectedProductBox" style="display: none; background: var(--surface-2); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-top: 14px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <div id="modalSelectedThumb" class="tp-product-thumb">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div id="modalSelectedName" style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);"></div>
                        <div id="modalSelectedMeta" style="font-size: 0.75rem; color: var(--text-muted);"></div>
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                        Pilih Kemasan yang Mau Ditambahkan Tier
                    </label>
                    <select id="modalPackagingSelect" class="tp-cat-filter" style="width: 100%;" onchange="onModalPackagingSelect()">
                    </select>
                </div>

                <!-- Info Modal Kemasan Terpilih -->
                <div id="modalPkgDetailsBox" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 4px;">
                        <span style="color: var(--text-muted);">Modal Kemasan:</span>
                        <span id="modalPkgBuyPrice" style="font-weight: 700; color: #ef4444;">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 4px;">
                        <span style="color: var(--text-muted);">Harga Jual Normal:</span>
                        <span id="modalPkgSellPrice" style="font-weight: 700; color: var(--text-primary);">Rp 0</span>
                    </div>
                </div>

                <!-- Form Input Tier Baru -->
                <div style="border-top: 1px dashed var(--border-color); padding-top: 12px;">
                    <div style="font-size: 0.84rem; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
                        Set Harga Tier Pertama:
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 3px;">Min. Beli (Qty)</label>
                            <input type="number" id="modalTierMinQty" class="tp-inp-qty" style="width: 100%;" value="5" min="2" oninput="recalcModalTier()">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 3px;">Harga Tier / Satuan (Rp)</label>
                            <input type="number" id="modalTierUnitPrice" class="tp-inp-qty" style="width: 100%; text-align: left;" placeholder="Contoh: 1800" oninput="recalcModalTier()">
                        </div>
                    </div>

                    <!-- Live Calculation Preview in Modal -->
                    <div id="modalTierCalcPreview" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); border-radius: var(--radius-md); padding: 10px 12px; font-size: 0.82rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="color: var(--text-muted);">Total Bayar:</span>
                            <span id="modalCalcSubtotal" style="font-weight: 700; color: var(--text-primary);">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="color: var(--text-muted);">Keuntungan / Satuan:</span>
                            <span id="modalCalcProfitPerUnit" style="font-weight: 800; color: #10b981;">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Keuntungan Total:</span>
                            <span id="modalCalcProfitTotal" style="font-weight: 800; color: #10b981;">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tp-modal-footer">
            <button type="button" class="tp-btn tp-btn-outline" onclick="closeAddProductModal()">Batal</button>
            <button type="button" id="modalBtnSubmit" class="tp-btn tp-btn-primary" onclick="saveNewProductTier()" disabled>
                <i class="bi bi-check-lg"></i> Simpan ke Tier
            </button>
        </div>
    </div>
</div>

<script>
/**
 * State Management for Tier Prices Page
 */
let allProducts = [];
let allCategories = [];
let selectedModalProduct = null;
let modalSearchTimeout = null;

// Format Currency
function formatRupiah(number) {
    if (isNaN(number) || number === null || number === undefined) return 'Rp 0';
    return 'Rp ' + Math.round(number).toLocaleString('id-ID');
}

// Format number without Rp
function formatNum(number) {
    if (isNaN(number) || number === null || number === undefined) return '0';
    return Math.round(number).toLocaleString('id-ID');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', () => {
    loadTierProducts();
});

/**
 * Load all products that have tier prices
 */
async function loadTierProducts() {
    const loadingState = document.getElementById('tpLoadingState');
    const container = document.getElementById('tpProductsContainer');
    const emptyState = document.getElementById('tpEmptyState');
    const refreshIcon = document.getElementById('btnRefreshIcon');

    if (refreshIcon) refreshIcon.classList.add('spin-animation');
    loadingState.style.display = 'block';
    container.style.display = 'none';
    emptyState.style.display = 'none';

    try {
        const resp = await fetch(`${BASE_URL}api/products/with-tier-prices`);
        const data = await resp.json();

        if (!data.success) {
            throw new Error(data.message || 'Gagal memuat produk tier');
        }

        allProducts = data.products || [];
        allCategories = data.categories || [];

        updateStats();
        populateCategoryFilter();
        renderProducts();

    } catch (err) {
        console.error('Error loading tier products:', err);
        showToast(err.message || 'Gagal memuat data tier', 'error');
    } finally {
        loadingState.style.display = 'none';
        if (refreshIcon) refreshIcon.classList.remove('spin-animation');
    }
}

/**
 * Update Header Stats
 */
function updateStats() {
    let totalTiers = 0;
    allProducts.forEach(p => {
        (p.packagings || []).forEach(pkg => {
            totalTiers += (pkg.qty_prices || []).length;
        });
    });

    document.getElementById('statProductCount').innerText = allProducts.length;
    document.getElementById('statTierCount').innerText = totalTiers;
    document.getElementById('statCategoryCount').innerText = allCategories.length;
}

/**
 * Populate Category Dropdown
 */
function populateCategoryFilter() {
    const sel = document.getElementById('tpCategorySelect');
    const currentVal = sel.value;
    sel.innerHTML = '<option value="">Semua Kategori</option>';

    allCategories.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.name;
        opt.textContent = `${c.name} (${c.count})`;
        sel.appendChild(opt);
    });

    if (currentVal) sel.value = currentVal;
}

/**
 * Filter change event handler
 */
function onFilterChange() {
    renderProducts();
}

/**
 * Render all products grouped by category
 */
function renderProducts() {
    const container = document.getElementById('tpProductsContainer');
    const emptyState = document.getElementById('tpEmptyState');
    const searchQuery = (document.getElementById('tpSearchInput').value || '').trim().toLowerCase();
    const selectedCategory = document.getElementById('tpCategorySelect').value;

    // Filter products
    const filtered = allProducts.filter(p => {
        // Category filter
        if (selectedCategory && (p.category_name || 'Tanpa Kategori') !== selectedCategory) {
            return false;
        }
        // Search filter
        if (searchQuery) {
            const matchName = (p.full_name || '').toLowerCase().includes(searchQuery);
            const matchShort = (p.short_label || '').toLowerCase().includes(searchQuery);
            const matchCode = (p.code || '').toLowerCase().includes(searchQuery);
            const matchBrand = (p.brand_name || '').toLowerCase().includes(searchQuery);
            const matchBarcode = (p.packagings || []).some(pkg => (pkg.barcode || '').toLowerCase().includes(searchQuery));
            if (!matchName && !matchShort && !matchCode && !matchBrand && !matchBarcode) {
                return false;
            }
        }
        return true;
    });

    if (filtered.length === 0) {
        container.style.display = 'none';
        container.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';
    container.style.display = 'block';

    // Group filtered products by category
    const grouped = {};
    filtered.forEach(p => {
        const cat = p.category_name || 'Tanpa Kategori';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(p);
    });

    let html = '';

    for (const [catName, products] of Object.entries(grouped)) {
        html += `
            <div class="tp-cat-group" id="catGroup_${escapeHtml(catName).replace(/[^a-zA-Z0-9]/g, '_')}">
                <div class="tp-cat-header" onclick="toggleCategory('${escapeHtml(catName).replace(/[^a-zA-Z0-9]/g, '_')}')">
                    <div class="tp-cat-title">
                        <i class="bi bi-folder-fill" style="color: var(--primary);"></i>
                        <span>${escapeHtml(catName)}</span>
                        <span class="tp-cat-badge">${products.length} Produk</span>
                    </div>
                    <i class="bi bi-chevron-down tp-cat-chevron"></i>
                </div>
                <div class="tp-cat-content">
                    ${products.map(p => renderProductCard(p)).join('')}
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
}

/**
 * Toggle category collapse
 */
function toggleCategory(cleanCatId) {
    const el = document.getElementById(`catGroup_${cleanCatId}`);
    if (el) {
        el.classList.toggle('collapsed');
    }
}

/**
 * Render single product card with its packagings & tier rows
 */
function renderProductCard(p) {
    const photoHtml = p.photo 
        ? `<img src="${BASE_URL}storage/products/${escapeHtml(p.photo)}" alt="${escapeHtml(p.full_name)}">`
        : `<i class="bi bi-box-seam"></i>`;

    const brandHtml = p.brand_name ? `<span class="tp-tag tp-tag-brand"><i class="bi bi-award"></i> ${escapeHtml(p.brand_name)}</span>` : '';
    const stockHtml = `<span class="tp-tag tp-tag-stock"><i class="bi bi-stack"></i> Stok: ${formatNum(p.current_qty_base || 0)}</span>`;
    const codeHtml = p.code ? `<span class="tp-tag"><i class="bi bi-upc"></i> ${escapeHtml(p.code)}</span>` : '';

    return `
        <div class="tp-product-card" id="prodCard_${p.id}">
            <div class="tp-product-header">
                <div class="tp-product-info">
                    <div class="tp-product-thumb">${photoHtml}</div>
                    <div>
                        <h3 class="tp-product-name">${escapeHtml(p.short_label || p.full_name)}</h3>
                        <div class="tp-product-meta">
                            ${codeHtml}
                            ${brandHtml}
                            ${stockHtml}
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="${BASE_URL}products/${p.id}/edit" class="tp-btn tp-btn-outline tp-btn-sm" target="_blank" title="Edit Lengkap">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="${BASE_URL}products/${p.id}" class="tp-btn tp-btn-outline tp-btn-sm" target="_blank" title="Lihat Detail">
                        <i class="bi bi-box-arrow-up-right"></i> Detail
                    </a>
                </div>
            </div>
            <div class="tp-product-body">
                ${(p.packagings || []).map(pkg => renderPackagingBox(p, pkg)).join('')}
            </div>
        </div>
    `;
}

/**
 * Render a packaging box inside product card
 */
function renderPackagingBox(product, pkg) {
    const buyPrice = parseFloat(pkg.buy_price) || 0;
    const sellPrice = parseFloat(pkg.sell_price_retail) || 0;
    const baseQty = parseFloat(pkg.base_qty) || 1;
    const buyPricePerBase = buyPrice / baseQty;

    const normalDiff = sellPrice - buyPrice;
    const normalMarkup = buyPrice > 0 ? ((normalDiff / buyPrice) * 100) : 0;
    const tiers = pkg.qty_prices || [];

    const barcodeDisplay = pkg.barcode 
        ? `<span class="tp-pkg-barcode"><i class="bi bi-upc-scan"></i> ${escapeHtml(pkg.barcode)}</span>`
        : `<span class="tp-pkg-barcode" style="opacity:0.5;">(Tanpa Barcode)</span>`;

    const unitInfo = pkg.level > 1 && pkg.contained_qty > 1 
        ? `${escapeHtml(pkg.unit_name)} (Isi ${formatNum(pkg.contained_qty)})` 
        : `${escapeHtml(pkg.unit_name)}`;

    return `
        <div class="tp-packaging-box" id="pkgBox_${pkg.id}">
            <div class="tp-packaging-top">
                <div class="tp-packaging-title">
                    <span class="tp-pkg-level-badge">Lvl ${pkg.level}</span>
                    <span class="tp-pkg-name">${unitInfo}</span>
                    ${barcodeDisplay}
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="tp-save-indicator" id="saveInd_${pkg.id}">
                        <i class="bi bi-check-circle-fill"></i> Tersimpan
                    </span>
                    <button type="button" class="tp-btn tp-btn-success tp-btn-sm" onclick="savePackagingChanges(${product.id}, ${pkg.id})">
                        <i class="bi bi-floppy"></i> Simpan
                    </button>
                </div>
            </div>

            <!-- Base Price Strip -->
            <div class="tp-base-strip">
                <div class="tp-base-item">
                    <span class="tp-base-label">Harga Modal Kemasan</span>
                    <span class="tp-base-value cost" id="txtBuyPrice_${pkg.id}">${formatRupiah(buyPrice)}</span>
                </div>
                <div class="tp-base-item">
                    <span class="tp-base-label">Harga Jual Normal</span>
                    <div class="tp-base-input-wrap">
                        <span class="tp-base-input-prefix">Rp</span>
                        <input type="number" 
                               class="tp-base-input" 
                               id="inpSellPrice_${pkg.id}" 
                               value="${Math.round(sellPrice)}" 
                               step="100" 
                               oninput="onPkgPriceChange(${pkg.id}, ${buyPrice}, ${baseQty})">
                    </div>
                </div>
                <div class="tp-base-item">
                    <span class="tp-base-label">Selisih Jual - Beli</span>
                    <span class="tp-base-value ${normalDiff >= 0 ? 'profit' : 'cost'}" id="txtDiff_${pkg.id}">
                        ${normalDiff >= 0 ? '+' : ''}${formatRupiah(normalDiff)}
                    </span>
                </div>
                <div class="tp-base-item">
                    <span class="tp-base-label">Markup Normal</span>
                    <div>
                        <span class="tp-markup-badge ${normalMarkup >= 0 ? '' : 'loss'}" id="txtMarkup_${pkg.id}">
                            ${normalMarkup >= 0 ? '+' : ''}${normalMarkup.toFixed(1)}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tier Table -->
            <div class="tp-table-wrap">
                <table class="tp-table" id="tierTable_${pkg.id}">
                    <thead>
                        <tr>
                            <th style="width: 110px;">Min. Beli</th>
                            <th style="width: 150px;">Harga Tier / Kemasan</th>
                            <th>Total Belanja</th>
                            <th>Keuntungan / Kemasan</th>
                            <th>Keuntungan Total</th>
                            <th>Markup Tier %</th>
                            <th>Hemat Pembeli</th>
                            <th style="width: 50px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tierTbody_${pkg.id}">
                        ${tiers.map((t, idx) => renderTierRow(pkg.id, t, buyPrice, sellPrice, idx)).join('')}
                    </tbody>
                </table>
            </div>

            <!-- Packaging Actions -->
            <div class="tp-pkg-actions">
                <button type="button" class="tp-btn tp-btn-outline tp-btn-sm" onclick="addTierRow(${pkg.id}, ${buyPrice})">
                    <i class="bi bi-plus-lg"></i> Tambah Tier
                </button>
                <div style="font-size: 0.78rem; color: var(--text-muted);">
                    <i class="bi bi-info-circle"></i> Nilai keuntungan & markup otomatis dikalkulasi secara real-time.
                </div>
            </div>
        </div>
    `;
}

/**
 * Render single Tier row inside table
 */
function renderTierRow(pkgId, tier, buyPrice, normalSellPrice, index) {
    const minQty = parseFloat(tier.min_qty) || 1;
    const unitPrice = parseFloat(tier.unit_price) || 0;

    const subtotal = minQty * unitPrice;
    const profitPerUnit = unitPrice - buyPrice;
    const profitTotal = profitPerUnit * minQty;
    const markupPct = buyPrice > 0 ? ((profitPerUnit / buyPrice) * 100) : 0;
    const customerSaving = (normalSellPrice - unitPrice) * minQty;

    const profitClass = profitPerUnit >= 0 ? '' : 'loss';
    const profitSign = profitPerUnit >= 0 ? '+' : '';

    return `
        <tr id="tierRow_${pkgId}_${index}">
            <td>
                <input type="number" 
                       class="tp-inp-qty tier-min-qty" 
                       value="${minQty}" 
                       min="2" 
                       step="1" 
                       oninput="recalcTierRow(${pkgId}, ${index})">
            </td>
            <td>
                <div class="tp-inp-price-wrap">
                    <span class="tp-inp-price-prefix">Rp</span>
                    <input type="number" 
                           class="tp-inp-price tier-unit-price" 
                           value="${Math.round(unitPrice)}" 
                           step="100" 
                           oninput="recalcTierRow(${pkgId}, ${index})">
                </div>
            </td>
            <td>
                <span class="tier-txt-subtotal" style="font-weight: 700; color: var(--text-primary);">
                    ${formatRupiah(subtotal)}
                </span>
            </td>
            <td>
                <span class="tp-profit-val ${profitClass} tier-txt-profit-unit">
                    ${profitSign}${formatRupiah(profitPerUnit)}
                </span>
            </td>
            <td>
                <span class="tp-profit-val ${profitClass} tier-txt-profit-total">
                    ${profitSign}${formatRupiah(profitTotal)}
                </span>
            </td>
            <td>
                <span class="tp-markup-badge ${profitClass} tier-txt-markup">
                    ${profitSign}${markupPct.toFixed(1)}%
                </span>
            </td>
            <td>
                <span class="tier-txt-saving" style="font-size: 0.8rem; color: ${customerSaving > 0 ? '#3b82f6' : 'var(--text-muted)'}; font-weight: 600;">
                    ${customerSaving > 0 ? `Hemat ${formatRupiah(customerSaving)}` : '-'}
                </span>
            </td>
            <td style="text-align: center;">
                <button type="button" class="tp-btn-icon tp-btn-del" onclick="deleteTierRow(${pkgId}, ${index})" title="Hapus Tier">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
}

/**
 * Handle change of packaging retail sell price
 */
function onPkgPriceChange(pkgId, buyPrice, baseQty) {
    const sellInp = document.getElementById(`inpSellPrice_${pkgId}`);
    const newSell = parseFloat(sellInp.value) || 0;

    const diff = newSell - buyPrice;
    const markup = buyPrice > 0 ? ((diff / buyPrice) * 100) : 0;

    const diffEl = document.getElementById(`txtDiff_${pkgId}`);
    if (diffEl) {
        diffEl.className = `tp-base-value ${diff >= 0 ? 'profit' : 'cost'}`;
        diffEl.innerText = `${diff >= 0 ? '+' : ''}${formatRupiah(diff)}`;
    }

    const markupEl = document.getElementById(`txtMarkup_${pkgId}`);
    if (markupEl) {
        markupEl.className = `tp-markup-badge ${markup >= 0 ? '' : 'loss'}`;
        markupEl.innerText = `${markup >= 0 ? '+' : ''}${markup.toFixed(1)}%`;
    }

    // Recalculate customer savings on all tier rows for this packaging
    const tbody = document.getElementById(`tierTbody_${pkgId}`);
    if (tbody) {
        const rows = tbody.querySelectorAll('tr');
        rows.forEach((r, idx) => recalcTierRow(pkgId, idx));
    }
}

/**
 * Recalculate single tier row
 */
function recalcTierRow(pkgId, index) {
    const row = document.getElementById(`tierRow_${pkgId}_${index}`);
    if (!row) return;

    const buyPriceText = document.getElementById(`txtBuyPrice_${pkgId}`)?.innerText || '0';
    const buyPrice = parseFloat(buyPriceText.replace(/[^0-9]/g, '')) || 0;

    const sellInp = document.getElementById(`inpSellPrice_${pkgId}`);
    const normalSell = parseFloat(sellInp ? sellInp.value : 0) || 0;

    const qtyInp = row.querySelector('.tier-min-qty');
    const priceInp = row.querySelector('.tier-unit-price');

    const minQty = parseFloat(qtyInp.value) || 1;
    const unitPrice = parseFloat(priceInp.value) || 0;

    const subtotal = minQty * unitPrice;
    const profitPerUnit = unitPrice - buyPrice;
    const profitTotal = profitPerUnit * minQty;
    const markupPct = buyPrice > 0 ? ((profitPerUnit / buyPrice) * 100) : 0;
    const customerSaving = (normalSell - unitPrice) * minQty;

    const profitClass = profitPerUnit >= 0 ? '' : 'loss';
    const profitSign = profitPerUnit >= 0 ? '+' : '';

    const subtotalEl = row.querySelector('.tier-txt-subtotal');
    if (subtotalEl) subtotalEl.innerText = formatRupiah(subtotal);

    const profitUnitEl = row.querySelector('.tier-txt-profit-unit');
    if (profitUnitEl) {
        profitUnitEl.className = `tp-profit-val ${profitClass} tier-txt-profit-unit`;
        profitUnitEl.innerText = `${profitSign}${formatRupiah(profitPerUnit)}`;
    }

    const profitTotalEl = row.querySelector('.tier-txt-profit-total');
    if (profitTotalEl) {
        profitTotalEl.className = `tp-profit-val ${profitClass} tier-txt-profit-total`;
        profitTotalEl.innerText = `${profitSign}${formatRupiah(profitTotal)}`;
    }

    const markupEl = row.querySelector('.tier-txt-markup');
    if (markupEl) {
        markupEl.className = `tp-markup-badge ${profitClass} tier-txt-markup`;
        markupEl.innerText = `${profitSign}${markupPct.toFixed(1)}%`;
    }

    const savingEl = row.querySelector('.tier-txt-saving');
    if (savingEl) {
        savingEl.innerText = customerSaving > 0 ? `Hemat ${formatRupiah(customerSaving)}` : '-';
        savingEl.style.color = customerSaving > 0 ? '#3b82f6' : 'var(--text-muted)';
    }
}

/**
 * Add a new tier row to packaging
 */
function addTierRow(pkgId, buyPrice) {
    const tbody = document.getElementById(`tierTbody_${pkgId}`);
    if (!tbody) return;

    const index = Date.now();
    const sellInp = document.getElementById(`inpSellPrice_${pkgId}`);
    const normalSell = parseFloat(sellInp ? sellInp.value : 0) || 0;

    // Suggest reasonable default tier: min_qty 5, unit_price 90% of normalSell
    const defMinQty = 5;
    const defUnitPrice = normalSell > 0 ? Math.round((normalSell * 0.9) / 100) * 100 : buyPrice * 1.1;

    const rowHtml = renderTierRow(pkgId, { min_qty: defMinQty, unit_price: defUnitPrice }, buyPrice, normalSell, index);
    tbody.insertAdjacentHTML('beforeend', rowHtml);
    recalcTierRow(pkgId, index);
}

/**
 * Delete a tier row
 */
function deleteTierRow(pkgId, index) {
    const row = document.getElementById(`tierRow_${pkgId}_${index}`);
    if (row) {
        row.remove();
    }
}

/**
 * Save packaging changes (Sell price and Qty prices)
 */
async function savePackagingChanges(productId, pkgId) {
    const csrf = document.getElementById('csrfToken').value;
    const sellInp = document.getElementById(`inpSellPrice_${pkgId}`);
    const newSellPrice = parseFloat(sellInp.value) || 0;

    // Collect all tiers from tbody
    const tbody = document.getElementById(`tierTbody_${pkgId}`);
    const rows = tbody ? tbody.querySelectorAll('tr') : [];
    const tiers = [];

    rows.forEach(r => {
        const minQty = parseFloat(r.querySelector('.tier-min-qty')?.value) || 0;
        const unitPrice = parseFloat(r.querySelector('.tier-unit-price')?.value) || 0;
        if (minQty >= 1 && unitPrice > 0) {
            tiers.push({
                min_qty: minQty,
                unit_price: unitPrice,
                label: `Beli >= ${minQty}`
            });
        }
    });

    // Find in-memory packaging
    let targetPkg = null;
    for (const p of allProducts) {
        if (p.id === productId) {
            targetPkg = (p.packagings || []).find(pkg => pkg.id === pkgId);
            break;
        }
    }

    const buyPrice = targetPkg ? targetPkg.buy_price : 0;
    const wholesalePrice = targetPkg ? targetPkg.sell_price_wholesale : 0;
    const barcode = targetPkg ? targetPkg.barcode : '';

    try {
        // 1. Update Sell Price
        const respPkg = await fetch(`${BASE_URL}api/products/packaging/${pkgId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                csrf_token: csrf,
                buy_price: buyPrice,
                sell_price_retail: newSellPrice,
                sell_price_wholesale: wholesalePrice,
                barcode: barcode
            })
        });
        const resPkg = await respPkg.json();
        if (!respPkg.ok || resPkg.error) {
            throw new Error(resPkg.error || resPkg.message || 'Gagal update harga jual');
        }

        // 2. Update Qty Tiers
        const respTiers = await fetch(`${BASE_URL}api/products/packaging/${pkgId}/qty-prices`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                csrf_token: csrf,
                tiers: tiers
            })
        });
        const resTiers = await respTiers.json();
        if (!respTiers.ok || resTiers.error) {
            throw new Error(resTiers.error || resTiers.message || 'Gagal simpan tier harga');
        }

        // Update in-memory
        if (targetPkg) {
            targetPkg.sell_price_retail = newSellPrice;
            targetPkg.qty_prices = tiers;
        }

        // If product has no tiers left on any packaging, update stats
        updateStats();

        // Show indicator
        const ind = document.getElementById(`saveInd_${pkgId}`);
        if (ind) {
            ind.classList.add('show');
            setTimeout(() => ind.classList.remove('show'), 2500);
        }

        showToast('Harga jual & tier harga berhasil disimpan!', 'success');

    } catch (err) {
        console.error('Save packaging error:', err);
        showToast(err.message || 'Gagal menyimpan perubahan', 'error');
    }
}

/* ==========================================================================
   MODAL: TAMBAH PRODUK KE TIER
   ========================================================================== */

function openAddProductModal() {
    const modal = document.getElementById('addProductModal');
    modal.classList.add('show');
    document.getElementById('modalProductSearch').value = '';
    document.getElementById('modalSearchResults').style.display = 'none';
    document.getElementById('modalSearchResults').innerHTML = '';
    document.getElementById('modalSelectedProductBox').style.display = 'none';
    document.getElementById('modalBtnSubmit').disabled = true;
    selectedModalProduct = null;
    setTimeout(() => document.getElementById('modalProductSearch').focus(), 150);
}

function closeAddProductModal() {
    const modal = document.getElementById('addProductModal');
    modal.classList.remove('show');
}

function debounceModalSearch() {
    clearTimeout(modalSearchTimeout);
    modalSearchTimeout = setTimeout(searchModalProducts, 280);
}

async function searchModalProducts() {
    const query = document.getElementById('modalProductSearch').value.trim();
    const resBox = document.getElementById('modalSearchResults');
    const spinner = document.getElementById('modalSearchSpinner');

    if (query.length < 1) {
        resBox.style.display = 'none';
        resBox.innerHTML = '';
        return;
    }

    spinner.style.display = 'block';

    try {
        const resp = await fetch(`${BASE_URL}api/products/search?q=${encodeURIComponent(query)}`);
        const results = await resp.json();

        spinner.style.display = 'none';
        if (!Array.isArray(results) || results.length === 0) {
            resBox.innerHTML = `<div style="padding: 12px; color: var(--text-muted); font-size: 0.85rem; text-align: center;">Tidak ada produk ditemukan.</div>`;
            resBox.style.display = 'block';
            return;
        }

        let html = '';
        results.forEach(p => {
            const thumb = p.photo 
                ? `<img src="${BASE_URL}storage/products/${escapeHtml(p.photo)}" style="width:36px;height:36px;border-radius:6px;object-fit:cover;">`
                : `<div style="width:36px;height:36px;border-radius:6px;background:var(--surface-3);display:flex;align-items:center;justify-content:center;color:var(--text-muted);"><i class="bi bi-box"></i></div>`;

            const name = escapeHtml(p.short_label || p.full_name);
            const brand = p.brand_name ? ` · ${escapeHtml(p.brand_name)}` : '';
            const code = p.code ? `[${escapeHtml(p.code)}] ` : '';

            html += `
                <div class="tp-modal-res-item" onclick="selectModalProduct(${p.id})">
                    ${thumb}
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 700; font-size: 0.88rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${code}${name}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            ${escapeHtml(p.category_name || 'Tanpa Kategori')}${brand}
                        </div>
                    </div>
                    <i class="bi bi-chevron-right" style="color: var(--text-muted);"></i>
                </div>
            `;
        });

        resBox.innerHTML = html;
        resBox.style.display = 'block';

    } catch (err) {
        spinner.style.display = 'none';
        console.error('Modal search error:', err);
    }
}

/**
 * Select a product from search results
 */
async function selectModalProduct(productId) {
    const resBox = document.getElementById('modalSearchResults');
    resBox.style.display = 'none';

    try {
        const resp = await fetch(`${BASE_URL}api/products/${productId}/variants`);
        const data = await resp.json();
        
        let p = null;
        if (data.product) {
            p = data.product;
        } else {
            const r = await fetch(`${BASE_URL}api/products/search?q=${productId}`);
            const list = await r.json();
            p = (list || []).find(item => item.id == productId);
        }

        if (!p || !p.packagings || p.packagings.length === 0) {
            showToast('Produk tidak memiliki data kemasan', 'warning');
            return;
        }

        selectedModalProduct = p;

        // Render selected box
        const box = document.getElementById('modalSelectedProductBox');
        box.style.display = 'block';

        const thumbBox = document.getElementById('modalSelectedThumb');
        thumbBox.innerHTML = p.photo 
            ? `<img src="${BASE_URL}storage/products/${escapeHtml(p.photo)}" style="width:100%;height:100%;border-radius:9px;object-fit:cover;">`
            : `<i class="bi bi-box-seam"></i>`;

        document.getElementById('modalSelectedName').innerText = p.short_label || p.full_name;
        document.getElementById('modalSelectedMeta').innerText = `${p.category_name || 'Tanpa Kategori'} · ${p.brand_name || 'Tanpa Brand'}`;

        // Populate packagings select
        const pkgSel = document.getElementById('modalPackagingSelect');
        pkgSel.innerHTML = '';
        p.packagings.forEach(pkg => {
            const opt = document.createElement('option');
            opt.value = pkg.id;
            opt.textContent = `Level ${pkg.level}: ${pkg.unit_name} (Modal: ${formatRupiah(pkg.buy_price)} | Jual: ${formatRupiah(pkg.sell_price_retail)})`;
            pkgSel.appendChild(opt);
        });

        onModalPackagingSelect();
        document.getElementById('modalBtnSubmit').disabled = false;

    } catch (err) {
        console.error('Error selecting product:', err);
        showToast('Gagal memuat kemasan produk', 'error');
    }
}

/**
 * Packaging selected in modal
 */
function onModalPackagingSelect() {
    if (!selectedModalProduct) return;
    const pkgId = parseInt(document.getElementById('modalPackagingSelect').value);
    const pkg = selectedModalProduct.packagings.find(pk => pk.id === pkgId);
    if (!pkg) return;

    const buyPrice = parseFloat(pkg.buy_price) || 0;
    const sellPrice = parseFloat(pkg.sell_price_retail) || 0;

    document.getElementById('modalPkgBuyPrice').innerText = formatRupiah(buyPrice);
    document.getElementById('modalPkgSellPrice').innerText = formatRupiah(sellPrice);

    // Default tier unit price = 90% of sell price or buy price + 10%
    const defUnitPrice = sellPrice > 0 ? Math.round((sellPrice * 0.9) / 100) * 100 : Math.round(buyPrice * 1.1);
    document.getElementById('modalTierUnitPrice').value = defUnitPrice;

    recalcModalTier();
}

/**
 * Recalculate preview in modal
 */
function recalcModalTier() {
    if (!selectedModalProduct) return;
    const pkgId = parseInt(document.getElementById('modalPackagingSelect').value);
    const pkg = selectedModalProduct.packagings.find(pk => pk.id === pkgId);
    if (!pkg) return;

    const buyPrice = parseFloat(pkg.buy_price) || 0;
    const minQty = parseFloat(document.getElementById('modalTierMinQty').value) || 0;
    const unitPrice = parseFloat(document.getElementById('modalTierUnitPrice').value) || 0;

    const subtotal = minQty * unitPrice;
    const profitPerUnit = unitPrice - buyPrice;
    const profitTotal = profitPerUnit * minQty;

    document.getElementById('modalCalcSubtotal').innerText = formatRupiah(subtotal);

    const profitUnitEl = document.getElementById('modalCalcProfitPerUnit');
    profitUnitEl.innerText = `${profitPerUnit >= 0 ? '+' : ''}${formatRupiah(profitPerUnit)}`;
    profitUnitEl.style.color = profitPerUnit >= 0 ? '#10b981' : '#ef4444';

    const profitTotalEl = document.getElementById('modalCalcProfitTotal');
    profitTotalEl.innerText = `${profitTotal >= 0 ? '+' : ''}${formatRupiah(profitTotal)}`;
    profitTotalEl.style.color = profitTotal >= 0 ? '#10b981' : '#ef4444';
}

/**
 * Save new product tier from modal
 */
async function saveNewProductTier() {
    if (!selectedModalProduct) return;
    const btn = document.getElementById('modalBtnSubmit');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Menyimpan...`;

    const pkgId = parseInt(document.getElementById('modalPackagingSelect').value);
    const minQty = parseFloat(document.getElementById('modalTierMinQty').value) || 0;
    const unitPrice = parseFloat(document.getElementById('modalTierUnitPrice').value) || 0;
    const csrf = document.getElementById('csrfToken').value;

    if (minQty < 1 || unitPrice <= 0) {
        showToast('Min. qty dan harga tier harus valid', 'warning');
        btn.disabled = false;
        btn.innerHTML = origHtml;
        return;
    }

    try {
        const payload = {
            csrf_token: csrf,
            tiers: [
                { min_qty: minQty, unit_price: unitPrice, label: `Beli >= ${minQty}` }
            ]
        };

        const resp = await fetch(`${BASE_URL}api/products/packaging/${pkgId}/qty-prices`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload)
        });
        const res = await resp.json();

        if (!resp.ok || res.error) {
            throw new Error(res.error || res.message || 'Gagal menyimpan tier');
        }

        showToast('Produk berhasil ditambahkan ke tier pricing!', 'success');
        closeAddProductModal();

        // Reload data to reflect changes
        await loadTierProducts();

    } catch (err) {
        console.error('Save new tier error:', err);
        showToast(err.message || 'Gagal menyimpan tier', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}
</script>
