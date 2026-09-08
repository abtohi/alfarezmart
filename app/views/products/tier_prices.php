<!-- View: Manajemen Harga Tier (Grosir Bertingkat) - Elegant Split & Compact UI -->
<?php /** @var string $csrfToken */ ?>

<style>
/* ==========================================================================
   TIER PRICES MANAGEMENT - MODERN COMPACT & DESKTOP SPLIT DESIGN
   ========================================================================== */

/* Outer Page Wrapper - Breathing Room from Screen Edges */
.tp-page-wrapper {
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 16px 20px 100px 20px;
    box-sizing: border-box;
}

/* Header Area */
.tp-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.tp-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.tp-header-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(230, 57, 70, 0.18));
    color: #f59e0b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
    flex-shrink: 0;
}
.tp-header-title {
    font-size: 1.18rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.3px;
}
.tp-header-subtitle {
    font-size: 0.76rem;
    color: var(--text-muted);
    margin: 2px 0 0 0;
}
.tp-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Compact Stats Ribbon */
.tp-stats-ribbon {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.tp-stat-chip {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 4px 12px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 0.78rem;
    color: var(--text-secondary);
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.tp-stat-chip strong {
    color: var(--text-primary);
    font-weight: 800;
}
.tp-stat-chip i {
    font-size: 0.85rem;
}

/* Toolbar: Search, Filters & Expand/Collapse */
.tp-toolbar {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 10px 14px;
    margin-bottom: 16px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}
.tp-toolbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 240px;
}
.tp-search-wrap {
    position: relative;
    flex: 1;
    max-width: 320px;
    min-width: 180px;
}
.tp-search-wrap i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.85rem;
    pointer-events: none;
}
.tp-search-input {
    width: 100%;
    padding: 6px 10px 6px 30px;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.82rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.tp-search-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(230, 57, 70, 0.18);
}
.tp-cat-select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    font-size: 0.82rem;
    outline: none;
    cursor: pointer;
    max-width: 180px;
}

/* Expand / Collapse Controls */
.tp-toolbar-right {
    display: flex;
    align-items: center;
    gap: 6px;
}
.tp-btn-tool {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    background: var(--surface-2);
    color: var(--text-secondary);
    font-size: 0.76rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.tp-btn-tool:hover {
    background: var(--surface-3);
    color: var(--text-primary);
    border-color: var(--text-muted);
}

/* Buttons */
.tp-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 7px;
    font-size: 0.8rem;
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
    background: var(--primary-dark);
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
}
.tp-btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
}
.tp-btn-success:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}
.tp-btn-sm {
    padding: 4px 8px;
    font-size: 0.75rem;
}
.tp-btn-icon {
    width: 28px;
    height: 28px;
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

/* ==========================================================================
   DESKTOP SPLIT LAYOUT (Master - Detail)
   ========================================================================== */
.tp-split-layout {
    display: grid;
    grid-template-columns: 360px minmax(0, 1fr);
    gap: 16px;
    align-items: start;
}

/* Master Column (Left) */
.tp-master-col {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    position: sticky;
    top: 14px;
    max-height: calc(100vh - 28px);
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 16px rgba(0,0,0,0.05);
}
.tp-master-header {
    padding: 10px 14px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-secondary);
}
.tp-master-list {
    overflow-y: auto;
    flex: 1;
    padding: 6px 8px;
}

/* Category Tree Node in Master */
.tp-cat-node {
    margin-bottom: 6px;
    border-radius: 6px;
    border: 1px solid transparent;
}
.tp-cat-node-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 10px;
    border-radius: 6px;
    background: var(--surface-2);
    cursor: pointer;
    user-select: none;
    transition: background-color 0.15s;
}
.tp-cat-node-header:hover {
    background: var(--surface-3);
}
.tp-cat-node-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-primary);
}
.tp-cat-node-badge {
    font-size: 0.68rem;
    padding: 1px 6px;
    border-radius: 12px;
    background: rgba(230, 57, 70, 0.12);
    color: var(--primary-light);
    font-weight: 700;
}
.tp-cat-node-chevron {
    font-size: 0.75rem;
    color: var(--text-muted);
    transition: transform 0.2s;
}
/* Default is Collapsed */
.tp-cat-node.collapsed .tp-cat-node-chevron {
    transform: rotate(-90deg);
}
.tp-cat-node.collapsed .tp-cat-node-items {
    display: none;
}
.tp-cat-node-items {
    padding: 4px 0 4px 6px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

/* Product Item in Master List */
.tp-prod-item {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 6px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
    border: 1px solid transparent;
}
.tp-prod-item:hover {
    background: var(--surface-2);
}
.tp-prod-item.active {
    background: rgba(230, 57, 70, 0.12);
    border-color: rgba(230, 57, 70, 0.35);
}
.tp-prod-item-thumb {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: radial-gradient(circle at center, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.02) 100%), var(--surface-2);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 0.85rem;
    flex-shrink: 0;
    overflow: hidden;
    padding: 2px;
    box-sizing: border-box;
}
.tp-prod-item-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 4px;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    background: transparent;
}
.tp-prod-item-info {
    flex: 1;
    min-width: 0;
}
.tp-prod-item-name {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.25;
}
.tp-prod-item-meta {
    font-size: 0.7rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 1px;
}
.tp-prod-item-badge {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    flex-shrink: 0;
}

/* Detail Column (Right) */
.tp-detail-col {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

/* Product Detail Card */
.tp-product-detail-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
}
.tp-detail-banner {
    padding: 12px 16px;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.tp-detail-main-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.tp-detail-avatar {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: radial-gradient(circle at center, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.02) 100%), var(--surface-2);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: var(--text-muted);
    flex-shrink: 0;
    overflow: hidden;
    padding: 3px;
    box-sizing: border-box;
}
.tp-detail-avatar img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 6px;
    filter: drop-shadow(0 3px 6px rgba(0,0,0,0.35));
    background: transparent;
}
.tp-detail-title {
    font-size: 0.98rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.25;
}
.tp-detail-tags {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 3px;
    flex-wrap: wrap;
}
.tp-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 1px 7px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    background: rgba(148, 163, 184, 0.12);
    color: var(--text-secondary);
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
.tp-pkg-box {
    padding: 14px 16px;
    border-bottom: 1px dashed var(--border-color);
}
.tp-pkg-box:last-child {
    border-bottom: none;
}
.tp-pkg-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
}
.tp-pkg-badge-group {
    display: flex;
    align-items: center;
    gap: 6px;
}
.tp-pkg-lvl {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 5px;
    background: rgba(230, 57, 70, 0.15);
    color: var(--primary-light);
    border: 1px solid rgba(230, 57, 70, 0.25);
}
.tp-pkg-unit {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--text-primary);
}
.tp-pkg-code {
    font-size: 0.74rem;
    color: var(--text-muted);
    font-family: monospace;
}

/* Base Financial Grid (Compact) */
.tp-finance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 8px;
    background: var(--surface-2);
    padding: 10px 12px;
    border-radius: 6px;
    margin-bottom: 12px;
    border: 1px solid var(--border-color);
}
.tp-finance-item {
    display: flex;
    flex-direction: column;
}
.tp-finance-label {
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 2px;
}
.tp-finance-val {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--text-primary);
}
.tp-finance-val.cost {
    color: #ef4444;
}
.tp-finance-val.profit {
    color: #10b981;
}
.tp-finance-inp-wrap {
    display: flex;
    align-items: center;
    position: relative;
    max-width: 140px;
}
.tp-finance-prefix {
    position: absolute;
    left: 7px;
    font-size: 0.74rem;
    color: var(--text-muted);
    font-weight: 600;
    pointer-events: none;
}
.tp-finance-inp {
    width: 100%;
    padding: 3px 6px 3px 26px;
    border: 1px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    border-radius: 5px;
    font-size: 0.84rem;
    font-weight: 700;
    outline: none;
}
.tp-finance-inp:focus {
    border-color: var(--primary);
}

/* Tier Table (Compact & Elegant) */
.tp-table-wrap {
    overflow-x: auto;
    margin-bottom: 10px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
}
.tp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
    text-align: left;
    min-width: 620px;
}
.tp-table th {
    background: var(--surface-2);
    padding: 8px 10px;
    font-weight: 700;
    color: var(--text-muted);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border-bottom: 1px solid var(--border-color);
    white-space: nowrap;
}
.tp-table td {
    padding: 8px 10px;
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
    width: 70px;
    padding: 4px 6px;
    border: 1px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    border-radius: 5px;
    font-weight: 700;
    text-align: center;
    font-size: 0.8rem;
    outline: none;
}
.tp-inp-qty:focus {
    border-color: var(--primary);
}
.tp-inp-price-wrap {
    display: inline-flex;
    align-items: center;
    position: relative;
    width: 110px;
}
.tp-inp-price-prefix {
    position: absolute;
    left: 7px;
    font-size: 0.72rem;
    color: var(--text-muted);
    font-weight: 600;
    pointer-events: none;
}
.tp-inp-price {
    width: 100%;
    padding: 4px 6px 4px 26px;
    border: 1px solid var(--border-color);
    background: var(--bg-input);
    color: var(--text-primary);
    border-radius: 5px;
    font-weight: 700;
    font-size: 0.8rem;
    outline: none;
}
.tp-inp-price:focus {
    border-color: var(--primary);
}

/* Profit & Badge formatting */
.tp-profit-val {
    font-weight: 800;
    font-size: 0.84rem;
    color: #10b981;
}
.tp-profit-val.loss {
    color: #ef4444;
}
.tp-markup-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 5px;
    font-size: 0.7rem;
    font-weight: 700;
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}
.tp-markup-badge.loss {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

/* Packaging Bottom Bar */
.tp-pkg-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

/* Detail Placeholder */
.tp-detail-empty-prompt {
    background: var(--surface-1);
    border: 1px dashed var(--border-color);
    border-radius: var(--radius-md);
    padding: 60px 20px;
    text-align: center;
}
.tp-detail-empty-prompt i {
    font-size: 2.5rem;
    color: var(--text-muted);
    margin-bottom: 10px;
    display: block;
}
.tp-detail-empty-prompt h4 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 6px 0;
}
.tp-detail-empty-prompt p {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin: 0;
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
    padding: 16px;
}
.tp-modal-backdrop.show {
    display: flex;
}
.tp-modal-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    width: 100%;
    max-width: 540px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 16px 40px rgba(0,0,0,0.3);
    animation: tpModalSlide 0.2s ease-out;
}
@keyframes tpModalSlide {
    from { opacity: 0; transform: translateY(15px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.tp-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
}
.tp-modal-title {
    font-size: 0.98rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tp-modal-body {
    padding: 16px;
    overflow-y: auto;
    flex: 1;
}
.tp-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1px solid var(--border-color);
    background: var(--surface-2);
}
.tp-modal-res-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    margin-top: 8px;
    background: var(--surface-2);
}
.tp-modal-res-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-bottom: 1px solid var(--border-color);
    cursor: pointer;
    transition: background-color 0.15s;
}
.tp-modal-res-item:last-child {
    border-bottom: none;
}
.tp-modal-res-item:hover {
    background: rgba(230, 57, 70, 0.1);
}

/* Save status indicator */
.tp-save-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.74rem;
    color: #10b981;
    font-weight: 700;
    opacity: 0;
    transition: opacity 0.25s;
}
.tp-save-indicator.show {
    opacity: 1;
}

/* Spin animation */
.spin-animation {
    animation: tpSpin 0.7s linear infinite;
}
@keyframes tpSpin {
    100% { transform: rotate(360deg); }
}

/* ==========================================================================
   MOBILE RESPONSIVENESS (< 992px)
   ========================================================================== */
@media (max-width: 991px) {
    .tp-page-wrapper {
        padding: 12px 14px 90px 14px;
    }
    .tp-split-layout {
        display: block;
    }
    .tp-master-col {
        position: static;
        max-height: none;
        margin-bottom: 16px;
    }
    .tp-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .tp-header-actions {
        width: 100%;
    }
    .tp-header-actions .tp-btn {
        flex: 1;
        justify-content: center;
    }
    .tp-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    .tp-toolbar-left {
        flex-direction: column;
        align-items: stretch;
    }
    .tp-search-wrap, .tp-cat-select {
        max-width: none;
        width: 100%;
    }
    .tp-toolbar-right {
        justify-content: space-between;
    }
    .tp-toolbar-right .tp-btn-tool {
        flex: 1;
        justify-content: center;
    }
    .tp-finance-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<div class="tp-page-wrapper">
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
                <p class="tp-header-subtitle">Kelola harga grosir bertingkat, pantau modal, selisih margin, dan keuntungan.</p>
            </div>
        </div>
        <div class="tp-header-actions">
            <button type="button" class="tp-btn tp-btn-primary" onclick="openAddProductModal()">
                <i class="bi bi-plus-circle-fill"></i> Tambah Produk
            </button>
            <button type="button" class="tp-btn tp-btn-outline" onclick="loadTierProducts()" title="Muat Ulang Data">
                <i class="bi bi-arrow-clockwise" id="btnRefreshIcon"></i> Segarkan
            </button>
        </div>
    </div>

    <!-- Compact Stats Ribbon -->
    <div class="tp-stats-ribbon">
        <div class="tp-stat-chip">
            <i class="bi bi-box-seam-fill" style="color: #818cf8;"></i>
            <span><strong id="statProductCount">0</strong> Produk Ber-Tier</span>
        </div>
        <div class="tp-stat-chip">
            <i class="bi bi-layers-fill" style="color: #10b981;"></i>
            <span><strong id="statTierCount">0</strong> Aturan Tier</span>
        </div>
        <div class="tp-stat-chip">
            <i class="bi bi-folder-fill" style="color: #f59e0b;"></i>
            <span><strong id="statCategoryCount">0</strong> Kategori</span>
        </div>
    </div>

    <!-- Toolbar: Search, Category Filter, Expand All, Collapse All -->
    <div class="tp-toolbar">
        <div class="tp-toolbar-left">
            <div class="tp-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="tpSearchInput" class="tp-search-input" placeholder="Cari nama, barcode, SKU..." oninput="onFilterChange()">
            </div>
            <select id="tpCategorySelect" class="tp-cat-select" onchange="onFilterChange()">
                <option value="">Semua Kategori</option>
            </select>
        </div>
        <div class="tp-toolbar-right">
            <button type="button" class="tp-btn-tool" onclick="expandAllCategories()" title="Buka Semua Kategori">
                <i class="bi bi-arrows-expand"></i> Expand All
            </button>
            <button type="button" class="tp-btn-tool" onclick="collapseAllCategories()" title="Tutup Semua Kategori">
                <i class="bi bi-arrows-collapse"></i> Collapse All
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="tpLoadingState" style="text-align: center; padding: 40px 0;">
        <div class="spinner-border text-primary spinner-border-sm" role="status" style="width: 2rem; height: 2rem;"></div>
        <div style="margin-top: 10px; color: var(--text-muted); font-size: 0.85rem;">Memuat daftar harga tier...</div>
    </div>

    <!-- Split Mode Layout Container -->
    <div class="tp-split-layout" id="tpSplitContainer" style="display: none;">
        <!-- Left / Master: Category & Product Tree -->
        <div class="tp-master-col">
            <div class="tp-master-header">
                <span><i class="bi bi-list-ul"></i> Daftar Produk Ber-Tier</span>
                <span id="tpFilteredCount" style="font-size: 0.72rem; color: var(--text-muted);">0 Produk</span>
            </div>
            <div class="tp-master-list" id="tpMasterList">
                <!-- Category nodes rendered dynamically -->
            </div>
        </div>

        <!-- Right / Detail: Active Product Tier Editor -->
        <div class="tp-detail-col" id="tpDetailPanel">
            <div class="tp-detail-empty-prompt">
                <i class="bi bi-hand-index-thumb"></i>
                <h4>Pilih Produk di Sebelah Kiri</h4>
                <p>Klik salah satu produk untuk mengedit harga jual, melihat rincian margin, dan mengatur tier grosir.</p>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="tpEmptyState" class="tp-detail-empty-prompt" style="display: none; margin-top: 20px;">
        <i class="bi bi-tags"></i>
        <h4>Belum Ada Produk Dengan Harga Tier</h4>
        <p style="margin-bottom: 14px;">Belum ada produk yang diset harga grosir bertingkat.</p>
        <button type="button" class="tp-btn tp-btn-primary" onclick="openAddProductModal()">
            <i class="bi bi-plus-circle-fill"></i> Tambah Produk Sekarang
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
            <button type="button" class="btn-close" onclick="closeAddProductModal()" style="background: none; border: none; font-size: 1.1rem; color: var(--text-muted); cursor: pointer;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="tp-modal-body">
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                    Cari Produk (Nama / Barcode / Kode)
                </label>
                <div class="tp-search-wrap" style="width: 100%; max-width: none;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="modalProductSearch" class="tp-search-input" placeholder="Ketik nama atau scan barcode..." oninput="debounceModalSearch()">
                </div>
                <div id="modalSearchSpinner" style="display: none; font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                    <span class="spinner-border spinner-border-sm"></span> Mencari...
                </div>
                <div id="modalSearchResults" class="tp-modal-res-list" style="display: none;"></div>
            </div>

            <!-- Detail produk terpilih -->
            <div id="modalSelectedProductBox" style="display: none; background: var(--surface-2); border: 1px solid var(--border-color); border-radius: 6px; padding: 12px; margin-top: 10px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div id="modalSelectedThumb" class="tp-detail-avatar" style="width:34px;height:34px;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div id="modalSelectedName" style="font-weight: 700; font-size: 0.88rem; color: var(--text-primary);"></div>
                        <div id="modalSelectedMeta" style="font-size: 0.72rem; color: var(--text-muted);"></div>
                    </div>
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="font-size: 0.76rem; font-weight: 700; color: var(--text-muted); margin-bottom: 3px; display: block;">
                        Pilih Kemasan
                    </label>
                    <select id="modalPackagingSelect" class="tp-cat-select" style="width: 100%; max-width: none;" onchange="onModalPackagingSelect()">
                    </select>
                </div>

                <!-- Info Modal Kemasan Terpilih -->
                <div id="modalPkgDetailsBox" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; margin-bottom: 10px; font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                        <span style="color: var(--text-muted);">Modal Kemasan:</span>
                        <span id="modalPkgBuyPrice" style="font-weight: 700; color: #ef4444;">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Harga Jual Normal:</span>
                        <span id="modalPkgSellPrice" style="font-weight: 700; color: var(--text-primary);">Rp 0</span>
                    </div>
                </div>

                <!-- Form Input Tier Baru -->
                <div style="border-top: 1px dashed var(--border-color); padding-top: 10px;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">
                        Set Harga Tier Awal:
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                        <div>
                            <label style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 2px;">Min. Beli (Qty)</label>
                            <input type="number" id="modalTierMinQty" class="tp-inp-qty" style="width: 100%;" value="5" min="2" oninput="recalcModalTier()">
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 2px;">Harga Tier / Satuan (Rp)</label>
                            <input type="number" id="modalTierUnitPrice" class="tp-inp-qty" style="width: 100%; text-align: left;" placeholder="Contoh: 1800" oninput="recalcModalTier()">
                        </div>
                    </div>

                    <!-- Live Calculation Preview in Modal -->
                    <div id="modalTierCalcPreview" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); border-radius: 6px; padding: 8px 10px; font-size: 0.76rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                            <span style="color: var(--text-muted);">Total Bayar:</span>
                            <span id="modalCalcSubtotal" style="font-weight: 700; color: var(--text-primary);">Rp 0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
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
            <button type="button" class="tp-btn tp-btn-outline tp-btn-sm" onclick="closeAddProductModal()">Batal</button>
            <button type="button" id="modalBtnSubmit" class="tp-btn tp-btn-primary tp-btn-sm" onclick="saveNewProductTier()" disabled>
                <i class="bi bi-check-lg"></i> Simpan ke Tier
            </button>
        </div>
    </div>
</div>

<script>
/**
 * State Management
 */
let allProducts = [];
let allCategories = [];
let activeProductId = null;
let selectedModalProduct = null;
let modalSearchTimeout = null;

// Track collapse state per category: default = true (all collapsed)
let categoryCollapseState = {};

function formatRupiah(num) {
    if (isNaN(num) || num === null || num === undefined) return 'Rp 0';
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function formatNum(num) {
    if (isNaN(num) || num === null || num === undefined) return '0';
    return Math.round(num).toLocaleString('id-ID');
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

/**
 * Resolve product photo URL properly (supports transparent images & various path formats)
 */
function getProductPhotoUrl(photo) {
    if (!photo || typeof photo !== 'string') return '';
    const trimmed = photo.trim();
    if (!trimmed) return '';
    if (trimmed.startsWith('http://') || trimmed.startsWith('https://') || trimmed.startsWith('data:')) {
        return trimmed;
    }
    const cleanPath = trimmed.replace(/^\/+/, '');
    return `${BASE_URL}${cleanPath}`;
}

document.addEventListener('DOMContentLoaded', () => {
    loadTierProducts();
});

/**
 * Load all products that have tier prices
 */
async function loadTierProducts() {
    const loadingState = document.getElementById('tpLoadingState');
    const splitContainer = document.getElementById('tpSplitContainer');
    const emptyState = document.getElementById('tpEmptyState');
    const refreshIcon = document.getElementById('btnRefreshIcon');

    if (refreshIcon) refreshIcon.classList.add('spin-animation');
    loadingState.style.display = 'block';
    splitContainer.style.display = 'none';
    emptyState.style.display = 'none';

    try {
        const resp = await fetch(`${BASE_URL}api/products/with-tier-prices`);
        const data = await resp.json();

        if (!data.success) {
            throw new Error(data.message || 'Gagal memuat produk tier');
        }

        allProducts = data.products || [];
        allCategories = data.categories || [];

        // Set default category collapse to true (COLLAPSED BY DEFAULT)
        allCategories.forEach(c => {
            if (categoryCollapseState[c.name] === undefined) {
                categoryCollapseState[c.name] = true; // default collapsed!
            }
        });

        updateStats();
        populateCategoryFilter();
        renderMasterList();

        // If on desktop and there are products, auto select first product
        if (allProducts.length > 0 && !activeProductId) {
            selectProduct(allProducts[0].id);
        } else if (activeProductId) {
            renderDetailPanel(activeProductId);
        }

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

function onFilterChange() {
    renderMasterList();
}

/**
 * Expand All Categories
 */
function expandAllCategories() {
    allCategories.forEach(c => {
        categoryCollapseState[c.name] = false;
    });
    renderMasterList();
}

/**
 * Collapse All Categories
 */
function collapseAllCategories() {
    allCategories.forEach(c => {
        categoryCollapseState[c.name] = true;
    });
    renderMasterList();
}

/**
 * Toggle single category
 */
function toggleCategoryNode(catName) {
    categoryCollapseState[catName] = !categoryCollapseState[catName];
    renderMasterList();
}

/**
 * Render Master Column (Left Panel Tree)
 */
function renderMasterList() {
    const splitContainer = document.getElementById('tpSplitContainer');
    const emptyState = document.getElementById('tpEmptyState');
    const masterList = document.getElementById('tpMasterList');
    const filteredCount = document.getElementById('tpFilteredCount');

    const searchQuery = (document.getElementById('tpSearchInput').value || '').trim().toLowerCase();
    const selectedCategory = document.getElementById('tpCategorySelect').value;

    const filtered = allProducts.filter(p => {
        if (selectedCategory && (p.category_name || 'Tanpa Kategori') !== selectedCategory) {
            return false;
        }
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

    filteredCount.innerText = `${filtered.length} Produk`;

    if (filtered.length === 0) {
        if (allProducts.length === 0) {
            splitContainer.style.display = 'none';
            emptyState.style.display = 'block';
        } else {
            splitContainer.style.display = 'grid';
            masterList.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 0.8rem;">Tidak ada produk yang cocok dengan pencarian.</div>`;
        }
        return;
    }

    emptyState.style.display = 'none';
    splitContainer.style.display = window.innerWidth >= 992 ? 'grid' : 'block';

    // Group by category
    const grouped = {};
    filtered.forEach(p => {
        const cat = p.category_name || 'Tanpa Kategori';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(p);
    });

    let html = '';

    for (const [catName, prods] of Object.entries(grouped)) {
        // If searching, auto-expand matching categories
        const isCollapsed = searchQuery ? false : (categoryCollapseState[catName] ?? true);

        html += `
            <div class="tp-cat-node ${isCollapsed ? 'collapsed' : ''}" id="catNode_${escapeHtml(catName).replace(/[^a-zA-Z0-9]/g, '_')}">
                <div class="tp-cat-node-header" onclick="toggleCategoryNode('${escapeHtml(catName)}')">
                    <div class="tp-cat-node-title">
                        <i class="bi bi-folder2-open" style="color: #f59e0b;"></i>
                        <span>${escapeHtml(catName)}</span>
                        <span class="tp-cat-node-badge">${prods.length}</span>
                    </div>
                    <i class="bi bi-chevron-down tp-cat-node-chevron"></i>
                </div>
                <div class="tp-cat-node-items">
                    ${prods.map(p => {
                        let tierCount = 0;
                        (p.packagings || []).forEach(pkg => { tierCount += (pkg.qty_prices || []).length; });

                        const photoUrl = getProductPhotoUrl(p.photo);
                        const thumbHtml = photoUrl 
                            ? `<img src="${photoUrl}" alt="" loading="lazy" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\\'bi bi-box-seam\\'></i>';">`
                            : `<i class="bi bi-box-seam"></i>`;

                        const isActive = p.id === activeProductId;

                        return `
                            <div class="tp-prod-item ${isActive ? 'active' : ''}" 
                                 id="prodItem_${p.id}" 
                                 onclick="selectProduct(${p.id})">
                                <div class="tp-prod-item-thumb">${thumbHtml}</div>
                                <div class="tp-prod-item-info">
                                    <div class="tp-prod-item-name">${escapeHtml(p.short_label || p.full_name)}</div>
                                    <div class="tp-prod-item-meta">
                                        <span>${p.code ? escapeHtml(p.code) : ''}</span>
                                        ${p.brand_name ? `<span>· ${escapeHtml(p.brand_name)}</span>` : ''}
                                    </div>
                                </div>
                                <span class="tp-prod-item-badge">${tierCount} Tier</span>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    masterList.innerHTML = html;
}

/**
 * Select a product to view and edit on the Right Detail Panel
 */
function selectProduct(productId) {
    activeProductId = productId;

    // Highlight active in list
    document.querySelectorAll('.tp-prod-item').forEach(el => el.classList.remove('active'));
    const activeEl = document.getElementById(`prodItem_${productId}`);
    if (activeEl) activeEl.classList.add('active');

    renderDetailPanel(productId);

    // On mobile, scroll smoothly to the detail panel
    if (window.innerWidth < 992) {
        const detailPanel = document.getElementById('tpDetailPanel');
        if (detailPanel) {
            detailPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

/**
 * Render Detail Panel for a selected Product
 */
function renderDetailPanel(productId) {
    const detailPanel = document.getElementById('tpDetailPanel');
    const p = allProducts.find(item => item.id === productId);

    if (!p) {
        detailPanel.innerHTML = `
            <div class="tp-detail-empty-prompt">
                <i class="bi bi-hand-index-thumb"></i>
                <h4>Pilih Produk di Sebelah Kiri</h4>
                <p>Klik salah satu produk untuk mengedit harga tier grosir.</p>
            </div>
        `;
        return;
    }

    const photoUrl = getProductPhotoUrl(p.photo);
    const photoHtml = photoUrl 
        ? `<img src="${photoUrl}" alt="${escapeHtml(p.full_name)}" loading="lazy" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\\'bi bi-box-seam\\'></i>';">`
        : `<i class="bi bi-box-seam"></i>`;

    const brandHtml = p.brand_name ? `<span class="tp-tag tp-tag-brand"><i class="bi bi-award"></i> ${escapeHtml(p.brand_name)}</span>` : '';
    const stockHtml = `<span class="tp-tag tp-tag-stock"><i class="bi bi-stack"></i> Stok: ${formatNum(p.current_qty_base || 0)}</span>`;
    const codeHtml = p.code ? `<span class="tp-tag"><i class="bi bi-upc"></i> ${escapeHtml(p.code)}</span>` : '';
    const catHtml = `<span class="tp-tag"><i class="bi bi-folder"></i> ${escapeHtml(p.category_name || 'Tanpa Kategori')}</span>`;

    let html = `
        <div class="tp-product-detail-card" id="detailCard_${p.id}">
            <div class="tp-detail-banner">
                <div class="tp-detail-main-info">
                    <div class="tp-detail-avatar">${photoHtml}</div>
                    <div>
                        <h2 class="tp-detail-title">${escapeHtml(p.short_label || p.full_name)}</h2>
                        <div class="tp-detail-tags">
                            ${catHtml}
                            ${codeHtml}
                            ${brandHtml}
                            ${stockHtml}
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 6px;">
                    <a href="${BASE_URL}products/${p.id}/edit" class="tp-btn tp-btn-outline tp-btn-sm" target="_blank" title="Edit Lengkap">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="${BASE_URL}products/${p.id}" class="tp-btn tp-btn-outline tp-btn-sm" target="_blank" title="Lihat Detail">
                        <i class="bi bi-box-arrow-up-right"></i> Detail
                    </a>
                </div>
            </div>
            <div class="tp-detail-body">
                ${(p.packagings || []).map(pkg => renderPackagingBox(p, pkg)).join('')}
            </div>
        </div>
    `;

    detailPanel.innerHTML = html;
}

/**
 * Render packaging block
 */
function renderPackagingBox(product, pkg) {
    const buyPrice = parseFloat(pkg.buy_price) || 0;
    const sellPrice = parseFloat(pkg.sell_price_retail) || 0;
    const baseQty = parseFloat(pkg.base_qty) || 1;
    const normalDiff = sellPrice - buyPrice;
    const normalMarkup = buyPrice > 0 ? ((normalDiff / buyPrice) * 100) : 0;
    const tiers = pkg.qty_prices || [];

    const barcodeDisplay = pkg.barcode 
        ? `<span class="tp-pkg-code"><i class="bi bi-upc-scan"></i> ${escapeHtml(pkg.barcode)}</span>`
        : `<span class="tp-pkg-code" style="opacity:0.4;">(No Barcode)</span>`;

    const unitInfo = pkg.level > 1 && pkg.contained_qty > 1 
        ? `${escapeHtml(pkg.unit_name)} (Isi ${formatNum(pkg.contained_qty)})` 
        : `${escapeHtml(pkg.unit_name)}`;

    return `
        <div class="tp-pkg-box" id="pkgBox_${pkg.id}">
            <div class="tp-pkg-top-bar">
                <div class="tp-pkg-badge-group">
                    <span class="tp-pkg-lvl">Lvl ${pkg.level}</span>
                    <span class="tp-pkg-unit">${unitInfo}</span>
                    ${barcodeDisplay}
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="tp-save-indicator" id="saveInd_${pkg.id}">
                        <i class="bi bi-check-circle-fill"></i> Tersimpan
                    </span>
                    <button type="button" class="tp-btn tp-btn-success tp-btn-sm" onclick="savePackagingChanges(${product.id}, ${pkg.id})">
                        <i class="bi bi-floppy"></i> Simpan
                    </button>
                </div>
            </div>

            <!-- Financial Strip -->
            <div class="tp-finance-grid">
                <div class="tp-finance-item">
                    <span class="tp-finance-label">Modal Kemasan</span>
                    <span class="tp-finance-val cost" id="txtBuyPrice_${pkg.id}">${formatRupiah(buyPrice)}</span>
                </div>
                <div class="tp-finance-item">
                    <span class="tp-finance-label">Harga Jual Normal</span>
                    <div class="tp-finance-inp-wrap">
                        <span class="tp-finance-prefix">Rp</span>
                        <input type="number" 
                               class="tp-finance-inp" 
                               id="inpSellPrice_${pkg.id}" 
                               value="${Math.round(sellPrice)}" 
                               step="100" 
                               oninput="onPkgPriceChange(${pkg.id}, ${buyPrice}, ${baseQty})">
                    </div>
                </div>
                <div class="tp-finance-item">
                    <span class="tp-finance-label">Selisih Normal</span>
                    <span class="tp-finance-val ${normalDiff >= 0 ? 'profit' : 'cost'}" id="txtDiff_${pkg.id}">
                        ${normalDiff >= 0 ? '+' : ''}${formatRupiah(normalDiff)}
                    </span>
                </div>
                <div class="tp-finance-item">
                    <span class="tp-finance-label">Markup Normal</span>
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
                            <th style="width: 90px;">Min. Beli</th>
                            <th style="width: 130px;">Harga Tier</th>
                            <th>Total</th>
                            <th>Untung / Satuan</th>
                            <th>Untung Total</th>
                            <th>Markup Tier</th>
                            <th>Hemat Pembeli</th>
                            <th style="width: 40px; text-align: center;">Aksi</th>
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
                <div style="font-size: 0.72rem; color: var(--text-muted);">
                    <i class="bi bi-lightning-charge-fill" style="color: #f59e0b;"></i> Keuntungan &amp; hemat dihitung instan saat diketik.
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
                <span class="tier-txt-saving" style="font-size: 0.76rem; color: ${customerSaving > 0 ? '#3b82f6' : 'var(--text-muted)'}; font-weight: 600;">
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
        diffEl.className = `tp-finance-val ${diff >= 0 ? 'profit' : 'cost'}`;
        diffEl.innerText = `${diff >= 0 ? '+' : ''}${formatRupiah(diff)}`;
    }

    const markupEl = document.getElementById(`txtMarkup_${pkgId}`);
    if (markupEl) {
        markupEl.className = `tp-markup-badge ${markup >= 0 ? '' : 'loss'}`;
        markupEl.innerText = `${markup >= 0 ? '+' : ''}${markup.toFixed(1)}%`;
    }

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
 * Add a new tier row
 */
function addTierRow(pkgId, buyPrice) {
    const tbody = document.getElementById(`tierTbody_${pkgId}`);
    if (!tbody) return;

    const index = Date.now();
    const sellInp = document.getElementById(`inpSellPrice_${pkgId}`);
    const normalSell = parseFloat(sellInp ? sellInp.value : 0) || 0;

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

        updateStats();
        renderMasterList();

        const ind = document.getElementById(`saveInd_${pkgId}`);
        if (ind) {
            ind.classList.add('show');
            setTimeout(() => ind.classList.remove('show'), 2500);
        }

        showToast('Harga jual & tier berhasil disimpan!', 'success');

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
    setTimeout(() => document.getElementById('modalProductSearch').focus(), 120);
}

function closeAddProductModal() {
    const modal = document.getElementById('addProductModal');
    modal.classList.remove('show');
}

function debounceModalSearch() {
    clearTimeout(modalSearchTimeout);
    modalSearchTimeout = setTimeout(searchModalProducts, 260);
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
            resBox.innerHTML = `<div style="padding: 10px; color: var(--text-muted); font-size: 0.8rem; text-align: center;">Tidak ada produk ditemukan.</div>`;
            resBox.style.display = 'block';
            return;
        }

        let html = '';
        results.forEach(p => {
            const photoUrl = getProductPhotoUrl(p.photo);
            const thumb = photoUrl 
                ? `<div class="tp-prod-item-thumb"><img src="${photoUrl}" alt="" loading="lazy" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\\'bi bi-box\\'></i>';"></div>`
                : `<div class="tp-prod-item-thumb"><i class="bi bi-box"></i></div>`;

            const name = escapeHtml(p.short_label || p.full_name);
            const brand = p.brand_name ? ` · ${escapeHtml(p.brand_name)}` : '';
            const code = p.code ? `[${escapeHtml(p.code)}] ` : '';

            html += `
                <div class="tp-modal-res-item" onclick="selectModalProduct(${p.id})">
                    ${thumb}
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 700; font-size: 0.82rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${code}${name}
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">
                            ${escapeHtml(p.category_name || 'Tanpa Kategori')}${brand}
                        </div>
                    </div>
                    <i class="bi bi-chevron-right" style="color: var(--text-muted); font-size: 0.75rem;"></i>
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

        const box = document.getElementById('modalSelectedProductBox');
        box.style.display = 'block';

        const thumbBox = document.getElementById('modalSelectedThumb');
        const photoUrl = getProductPhotoUrl(p.photo);
        thumbBox.innerHTML = photoUrl 
            ? `<img src="${photoUrl}" alt="" loading="lazy" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\\'bi bi-box-seam\\'></i>';">`
            : `<i class="bi bi-box-seam"></i>`;

        document.getElementById('modalSelectedName').innerText = p.short_label || p.full_name;
        document.getElementById('modalSelectedMeta').innerText = `${p.category_name || 'Tanpa Kategori'} · ${p.brand_name || 'Tanpa Brand'}`;

        const pkgSel = document.getElementById('modalPackagingSelect');
        pkgSel.innerHTML = '';
        p.packagings.forEach(pkg => {
            const opt = document.createElement('option');
            opt.value = pkg.id;
            opt.textContent = `Lvl ${pkg.level}: ${pkg.unit_name} (Modal: ${formatRupiah(pkg.buy_price)} | Jual: ${formatRupiah(pkg.sell_price_retail)})`;
            pkgSel.appendChild(opt);
        });

        onModalPackagingSelect();
        document.getElementById('modalBtnSubmit').disabled = false;

    } catch (err) {
        console.error('Error selecting product:', err);
        showToast('Gagal memuat kemasan produk', 'error');
    }
}

function onModalPackagingSelect() {
    if (!selectedModalProduct) return;
    const pkgId = parseInt(document.getElementById('modalPackagingSelect').value);
    const pkg = selectedModalProduct.packagings.find(pk => pk.id === pkgId);
    if (!pkg) return;

    const buyPrice = parseFloat(pkg.buy_price) || 0;
    const sellPrice = parseFloat(pkg.sell_price_retail) || 0;

    document.getElementById('modalPkgBuyPrice').innerText = formatRupiah(buyPrice);
    document.getElementById('modalPkgSellPrice').innerText = formatRupiah(sellPrice);

    const defUnitPrice = sellPrice > 0 ? Math.round((sellPrice * 0.9) / 100) * 100 : Math.round(buyPrice * 1.1);
    document.getElementById('modalTierUnitPrice').value = defUnitPrice;

    recalcModalTier();
}

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

        // Make sure newly added product is active
        activeProductId = selectedModalProduct.id;
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
