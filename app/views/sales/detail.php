<?php
/**
 * @var array $sale
 * @var array $storeSettings
 */

$rawItems = $sale['items'] ?? [];
$categoryGroups = [];
$totalItemsCount = count($rawItems);
$totalQuantitySum = 0;
$totalProfitSum = 0;
$totalModalSum = 0;
$saleTotalAmount = (float)($sale['total_amount'] ?? 0);

// Helper function to pick a category icon
$getCategoryIcon = function($catName) {
    $cn = strtolower($catName);
    if (strpos($cn, 'rokok') !== false) return 'bi-fire';
    if (strpos($cn, 'makanan') !== false || strpos($cn, 'snack') !== false) return 'bi-egg-fried';
    if (strpos($cn, 'mie') !== false) return 'bi-bowl';
    if (strpos($cn, 'minuman') !== false) return 'bi-cup-hot';
    if (strpos($cn, 'sembako') !== false || strpos($cn, 'beras') !== false) return 'bi-basket2';
    if (strpos($cn, 'bersih') !== false || strpos($cn, 'sabun') !== false || strpos($cn, 'deterjen') !== false) return 'bi-stars';
    if (strpos($cn, 'alat') !== false || strpos($cn, 'serbaguna') !== false) return 'bi-tools';
    if (strpos($cn, 'kesehatan') !== false || strpos($cn, 'obat') !== false) return 'bi-heart-pulse';
    if (strpos($cn, 'bayi') !== false) return 'bi-emoji-smile';
    if (strpos($cn, 'dingin') !== false || strpos($cn, 'es') !== false) return 'bi-snow';
    if (strpos($cn, 'atk') !== false || strpos($cn, 'kantor') !== false) return 'bi-pen';
    if (strpos($cn, 'listrik') !== false || strpos($cn, 'elektronik') !== false) return 'bi-lightning-charge';
    if (strpos($cn, 'bumbu') !== false || strpos($cn, 'dapur') !== false) return 'bi-flower1';
    return 'bi-tag-fill';
};

foreach ($rawItems as $idx => $item) {
    $isCustom = !empty($item['custom_name']);
    $catName = $isCustom ? 'Lainnya / Custom' : (!empty($item['category_name']) ? $item['category_name'] : 'Tanpa Kategori');
    $qty = (float)($item['quantity'] ?? 1);
    $buyPrice = (float)($item['buy_price'] ?? 0);
    $unitPrice = (float)($item['unit_price'] ?? 0);
    $totalPrice = (float)($item['total_price'] ?? 0);
    $itemProfit = (float)($item['profit'] ?? ($isCustom || $unitPrice <= 0 ? 0 : ($unitPrice - $buyPrice) * $qty));
    $totalModal = $isCustom ? 0 : ($buyPrice * $qty);

    $totalQuantitySum += $qty;
    $totalProfitSum += $itemProfit;
    $totalModalSum += $totalModal;

    $catSlug = preg_replace('/[^a-zA-Z0-9_-]/', '-', strtolower($catName));

    if (!isset($categoryGroups[$catName])) {
        $categoryGroups[$catName] = [
            'name' => $catName,
            'slug' => $catSlug,
            'icon' => $getCategoryIcon($catName),
            'count' => 0,
            'total_qty' => 0,
            'total_amount' => 0,
            'total_modal' => 0,
            'total_profit' => 0,
            'items' => []
        ];
    }
    $categoryGroups[$catName]['count']++;
    $categoryGroups[$catName]['total_qty'] += $qty;
    $categoryGroups[$catName]['total_amount'] += $totalPrice;
    $categoryGroups[$catName]['total_modal'] += $totalModal;
    $categoryGroups[$catName]['total_profit'] += $itemProfit;
    
    $selisih = ($isCustom || $unitPrice <= 0) ? 0 : ($unitPrice - $buyPrice);
    $markup = (!$isCustom && $buyPrice > 0 && $unitPrice > 0) ? (($selisih / $buyPrice) * 100) : 0;

    $categoryGroups[$catName]['items'][] = array_merge($item, [
        'is_custom' => $isCustom,
        'cat_name' => $catName,
        'calc_qty' => $qty,
        'calc_buy_price' => $buyPrice,
        'calc_unit_price' => $unitPrice,
        'calc_total_price' => $totalPrice,
        'calc_profit' => $itemProfit,
        'calc_modal' => $totalModal,
        'calc_selisih' => $selisih,
        'calc_markup' => $markup,
        'orig_index' => $idx + 1
    ]);
}

// Sort category groups by total amount descending (highest spending category at the top)
uasort($categoryGroups, function($a, $b) {
    return $b['total_amount'] <=> $a['total_amount'];
});

$netProfitMargin = $saleTotalAmount > 0 ? round(($totalProfitSum / $saleTotalAmount) * 100, 1) : 0;
?>
<script>
window.SALE_DATA = <?= json_encode($sale, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
</script>

<style>
/* ==========================================================================
   Sales Detail - Modern Desktop & Mobile Layout
   ========================================================================== */
.sale-detail-page {
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 60px;
}

/* Header & Breadcrumb */
.sale-header-bar {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.sale-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sale-btn-back {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    background: var(--surface-1);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    text-decoration: none;
    transition: all 0.2s ease;
}

.sale-btn-back:hover {
    background: var(--surface-2);
    border-color: var(--primary);
    color: var(--primary);
}

.sale-page-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.02em;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.sale-inv-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: var(--radius-sm);
    background: var(--primary-bg);
    color: var(--primary);
    border: 1px solid rgba(var(--primary-rgb), 0.25);
    font-weight: 700;
    letter-spacing: 0.5px;
}

.sale-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-sale-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: var(--radius-md);
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.btn-sale-action.primary {
    background: var(--gradient-primary);
    color: #fff;
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.28);
}

.btn-sale-action.primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(var(--primary-rgb), 0.38);
}

.btn-sale-action.outline {
    background: var(--surface-1);
    color: var(--text-primary);
    border-color: var(--border-color);
}

.btn-sale-action.outline:hover {
    background: var(--surface-2);
    border-color: var(--text-secondary);
}

/* 2-Column Responsive Grid */
.sale-detail-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

@media (min-width: 1024px) {
    .sale-detail-grid {
        grid-template-columns: minmax(0, 1.62fr) minmax(350px, 0.98fr);
        gap: 24px;
        align-items: start;
    }
    .sale-detail-sidebar {
        position: sticky;
        top: 80px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
}

/* Items Column */
.sale-items-panel {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Toolbar Control Bar */
.items-toolbar-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 14px 18px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.items-title-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.items-main-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.items-count-chip {
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 20px;
    background: var(--surface-2);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    font-weight: 600;
}

.items-ctrl-buttons {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.ctrl-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--surface-2);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}

.ctrl-btn:hover {
    background: var(--surface-3);
    color: var(--text-primary);
    border-color: rgba(255,255,255,0.18);
}

.ctrl-btn.active {
    background: var(--primary-bg);
    color: var(--primary);
    border-color: rgba(var(--primary-rgb), 0.35);
}

/* Category Quick Filter Chips */
.cat-chips-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: thin;
}

.cat-chips-scroll::-webkit-scrollbar {
    height: 4px;
}

.cat-chips-scroll::-webkit-scrollbar-thumb {
    background: var(--surface-3);
    border-radius: 4px;
}

.cat-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    font-size: 0.73rem;
    color: var(--text-secondary);
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.cat-chip:hover {
    background: var(--surface-2);
    border-color: var(--text-muted);
    color: var(--text-primary);
}

.cat-chip .chip-total {
    color: var(--primary);
    font-weight: 700;
}

/* Category Group Cards */
.category-groups-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.category-group-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.category-group-card:hover {
    border-color: rgba(255, 255, 255, 0.15);
}

.category-group-card.is-open {
    border-color: rgba(var(--primary-rgb), 0.35);
}

/* Accordion Header */
.category-group-header {
    width: 100%;
    padding: 14px 16px;
    background: transparent;
    border: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    cursor: pointer;
    text-align: left;
    color: var(--text-primary);
    transition: background 0.15s ease;
    user-select: none;
}

.category-group-header:hover {
    background: var(--surface-2);
}

.category-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    flex: 1;
}

.category-icon-box {
    width: 34px;
    height: 34px;
    border-radius: var(--radius-md);
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 0.95rem;
    flex-shrink: 0;
}

.category-title-info {
    min-width: 0;
}

.category-name-text {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.25;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.category-meta-text {
    font-size: 0.72rem;
    color: var(--text-muted);
}

.category-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.category-spending-pill {
    text-align: right;
}

.category-spending-label {
    font-size: 0.65rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.category-spending-val {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--primary);
    letter-spacing: -0.01em;
}

.category-share-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    background: var(--surface-3);
    color: var(--text-secondary);
}

.category-chevron {
    color: var(--text-muted);
    font-size: 0.85rem;
    transition: transform 0.25s ease;
}

.category-group-card.is-open .category-chevron {
    transform: rotate(180deg);
    color: var(--primary);
}

/* Accordion Body */
.category-group-body {
    display: none;
    border-top: 1px solid var(--border-color);
    background: var(--bg-primary);
}

.category-group-card.is-open .category-group-body {
    display: block;
}

/* Item Rows */
.sale-item-row {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.15s ease;
}

.sale-item-row:last-child {
    border-bottom: none;
}

.sale-item-row:hover {
    background: rgba(255, 255, 255, 0.015);
}

.item-main-flex {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 6px;
}

.item-name-wrap {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.35;
    margin-bottom: 3px;
}

.item-unit-badge {
    display: inline-block;
    font-size: 0.7rem;
    padding: 1px 6px;
    border-radius: 4px;
    background: var(--surface-2);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
}

.item-price-formula {
    font-size: 0.76rem;
    color: var(--text-secondary);
    margin-top: 3px;
}

.item-total-price {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    text-align: right;
    white-space: nowrap;
}

/* Cost & Profit Pill */
.item-profit-card {
    background: var(--surface-1);
    border-radius: var(--radius-sm);
    padding: 8px 10px;
    margin-top: 6px;
    border: 1px solid var(--border-color);
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 6px 12px;
}

@media (max-width: 640px) {
    .item-profit-card {
        grid-template-columns: 1fr 1fr;
    }
}

.profit-col-label {
    font-size: 0.65rem;
    color: var(--text-muted);
    margin-bottom: 2px;
}

.profit-col-val {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-secondary);
}

.profit-total-bar {
    grid-column: 1 / -1;
    border-top: 1px dashed var(--border-color);
    padding-top: 6px;
    margin-top: 2px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Flat List View Container */
.flat-items-list {
    display: none;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

/* Sidebar Cards */
.sale-sidebar-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 18px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.sidebar-card-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.sidebar-card-title i {
    color: var(--primary);
    font-size: 1rem;
}

/* Meta Data Grid in Sidebar */
.meta-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 14px;
}

.meta-field {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.meta-label {
    font-size: 0.7rem;
    color: var(--text-muted);
}

.meta-val {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--text-primary);
    word-break: break-word;
}

/* Category Breakdown in Sidebar */
.cat-breakdown-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.cat-breakdown-row {
    padding: 8px 10px;
    border-radius: var(--radius-md);
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    cursor: pointer;
    transition: all 0.2s ease;
}

.cat-breakdown-row:hover {
    border-color: rgba(var(--primary-rgb), 0.4);
    background: var(--surface-3);
    transform: translateX(2px);
}

.cat-row-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.cat-row-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 6px;
}

.cat-row-amount {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--primary);
}

.cat-row-bar-wrap {
    width: 100%;
    height: 5px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 4px;
}

.cat-row-bar-fill {
    height: 100%;
    background: var(--gradient-primary);
    border-radius: 4px;
    transition: width 0.4s ease;
}

.cat-row-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.68rem;
    color: var(--text-muted);
}

/* Financial Summary Table */
.summary-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.82rem;
    margin-bottom: 8px;
    color: var(--text-secondary);
}

.summary-line.grand-total {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px dashed var(--border-color);
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text-primary);
}

.summary-line.grand-total .val {
    color: var(--primary);
    font-size: 1.25rem;
    letter-spacing: -0.02em;
}

/* Copy Invoice Feedback */
.btn-copy-inv {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 0.78rem;
    padding: 2px 6px;
    border-radius: 4px;
    transition: color 0.15s ease;
}

.btn-copy-inv:hover {
    color: var(--text-primary);
}

/* Search Box */
.search-items-box {
    position: relative;
    width: 100%;
}

.search-items-input {
    width: 100%;
    padding: 7px 12px 7px 32px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-size: 0.78rem;
    outline: none;
    transition: border-color 0.2s ease;
}

.search-items-input:focus {
    border-color: var(--primary);
}

.search-items-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.8rem;
    pointer-events: none;
}
</style>

<div class="sale-detail-page">
    <!-- Header Bar -->
    <div class="sale-header-bar">
        <div class="sale-title-wrap">
            <a href="<?= BASE_URL ?>sales" class="sale-btn-back" title="Kembali ke Riwayat Penjualan">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="sale-page-title">
                    Detail Transaksi
                    <span class="sale-inv-badge"><?= htmlspecialchars($sale['invoice_number']) ?></span>
                </h1>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                    <i class="bi bi-calendar3"></i> <?= Helper::formatDateTime($sale['created_at']) ?>
                </div>
            </div>
        </div>

        <div class="sale-header-actions">
            <a href="<?= BASE_URL ?>sales/pos?edit=<?= $sale['id'] ?>" 
               onclick="try{localStorage.setItem('pos_edit_sale_payload', JSON.stringify(SALE_DATA));}catch(e){}" 
               class="btn-sale-action outline" title="Edit transaksi di kasir">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
            <button type="button" id="btnConnectPrinter" onclick="connectPrinter()" class="btn-sale-action outline" title="Hubungkan printer thermal">
                <i class="bi bi-bluetooth"></i> Printer
            </button>
            <button type="button" onclick="printReceipt()" class="btn-sale-action primary" title="Cetak struk belanja">
                <i class="bi bi-printer"></i> Cetak Struk
            </button>
        </div>
    </div>

    <!-- 2-Column Responsive Layout -->
    <div class="sale-detail-grid">
        <!-- LEFT COLUMN: Product Items & Category Grouping -->
        <div class="sale-items-panel">
            
            <!-- Toolbar Card -->
            <div class="items-toolbar-card">
                <div class="items-title-group">
                    <h2 class="items-main-title">Item Belanja</h2>
                    <span class="items-count-chip"><?= $totalItemsCount ?> item · <?= $totalQuantitySum ?> unit</span>
                </div>

                <div class="items-ctrl-buttons">
                    <!-- Toggle Grouping ON / OFF -->
                    <button type="button" class="ctrl-btn active" id="btnToggleGrouping" onclick="toggleGroupingMode()" title="Nyalakan atau matikan pengelompokan per kategori">
                        <i class="bi bi-folder2-open" id="iconGrouping"></i>
                        <span id="labelGrouping">Grouping: Aktif</span>
                    </button>

                    <!-- Expand All Button (active only in grouping mode) -->
                    <button type="button" class="ctrl-btn" id="btnExpandAll" onclick="expandAllCategories()" title="Buka seluruh kategori">
                        <i class="bi bi-arrows-expand"></i> Buka Semua
                    </button>

                    <!-- Collapse All Button (active only in grouping mode) -->
                    <button type="button" class="ctrl-btn" id="btnCollapseAll" onclick="collapseAllCategories()" title="Tutup seluruh kategori">
                        <i class="bi bi-arrows-collapse"></i> Tutup Semua
                    </button>
                </div>

                <!-- Instant Filter Input -->
                <div class="search-items-box">
                    <i class="bi bi-search search-items-icon"></i>
                    <input type="text" id="itemSearchFilter" class="search-items-input" placeholder="Cari item dalam transaksi ini..." oninput="filterItems(this.value)">
                </div>
            </div>

            <!-- Quick Category Chips Navigation -->
            <div class="cat-chips-scroll" id="catChipsNav">
                <?php foreach ($categoryGroups as $grp): 
                    $pct = $saleTotalAmount > 0 ? round(($grp['total_amount'] / $saleTotalAmount) * 100, 1) : 0;
                ?>
                <button type="button" class="cat-chip" onclick="scrollToCategory('cat-card-<?= $grp['slug'] ?>')">
                    <i class="bi <?= $grp['icon'] ?>"></i>
                    <span><?= htmlspecialchars($grp['name']) ?></span>
                    <span class="chip-total"><?= Helper::rupiah($grp['total_amount']) ?></span>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- GROUPED CATEGORIES CONTAINER (Default Mode: Grouped & Collapsed) -->
            <div class="category-groups-list" id="categoryGroupsContainer">
                <?php foreach ($categoryGroups as $grp): 
                    $sharePct = $saleTotalAmount > 0 ? round(($grp['total_amount'] / $saleTotalAmount) * 100, 1) : 0;
                ?>
                <div class="category-group-card" id="cat-card-<?= $grp['slug'] ?>" data-category="<?= htmlspecialchars(strtolower($grp['name'])) ?>">
                    <!-- Accordion Header: Click to expand/collapse -->
                    <button type="button" class="category-group-header" onclick="toggleCategory('cat-card-<?= $grp['slug'] ?>')">
                        <div class="category-header-left">
                            <div class="category-icon-box">
                                <i class="bi <?= $grp['icon'] ?>"></i>
                            </div>
                            <div class="category-title-info">
                                <div class="category-name-text"><?= htmlspecialchars($grp['name']) ?></div>
                                <div class="category-meta-text">
                                    <?= $grp['count'] ?> item · <?= $grp['total_qty'] ?> unit
                                </div>
                            </div>
                        </div>

                        <div class="category-header-right">
                            <div class="category-spending-pill">
                                <div class="category-spending-label">Total Belanja</div>
                                <div class="category-spending-val"><?= Helper::rupiah($grp['total_amount']) ?></div>
                            </div>
                            <span class="category-share-badge"><?= $sharePct ?>%</span>
                            <i class="bi bi-chevron-down category-chevron"></i>
                        </div>
                    </button>

                    <!-- Accordion Body: Item Rows (Default COLLAPSED) -->
                    <div class="category-group-body">
                        <?php foreach ($grp['items'] as $item): ?>
                        <div class="sale-item-row" data-item-name="<?= htmlspecialchars(strtolower($item['invoice_name'] ?? $item['full_name'] ?? '')) ?>">
                            <div class="item-main-flex">
                                <div class="item-name-wrap">
                                    <div class="item-name">
                                        <?= htmlspecialchars($item['invoice_name'] ?? $item['full_name'] ?? 'Item') ?>
                                        <?php if ($item['is_custom']): ?>
                                            <span class="item-unit-badge" style="color:var(--warning); border-color:rgba(255,183,3,0.3);">Custom</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-price-formula">
                                        <?= $item['calc_qty'] ?> <?= htmlspecialchars($item['unit_name'] ?? 'Pcs') ?> &times; <?= Helper::rupiah($item['calc_unit_price']) ?>
                                    </div>
                                </div>
                                <div class="item-total-price">
                                    <?= Helper::rupiah($item['calc_total_price']) ?>
                                </div>
                            </div>

                            <!-- Cost & Profit details -->
                            <?php if (!$item['is_custom']): ?>
                            <div class="item-profit-card">
                                <div>
                                    <div class="profit-col-label">Modal/unit</div>
                                    <div class="profit-col-val"><?= Helper::rupiah($item['calc_buy_price']) ?></div>
                                </div>
                                <div>
                                    <div class="profit-col-label">Total modal</div>
                                    <div class="profit-col-val"><?= Helper::rupiah($item['calc_modal']) ?></div>
                                </div>
                                <div>
                                    <div class="profit-col-label">Selisih</div>
                                    <div class="profit-col-val" style="color:<?= $item['calc_selisih'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                        <?= ($item['calc_selisih'] >= 0 ? '+' : '') . Helper::rupiah($item['calc_selisih']) ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="profit-col-label">Markup</div>
                                    <div class="profit-col-val" style="color:<?= $item['calc_markup'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                        <?= ($item['calc_markup'] >= 0 ? '+' : '') . str_replace('.', ',', round($item['calc_markup'], 1)) ?>%
                                    </div>
                                </div>
                                <div class="profit-total-bar">
                                    <span style="font-size:0.7rem; color:var(--text-muted);">
                                        Profit (<?= $item['calc_qty'] ?> <?= htmlspecialchars($item['unit_name'] ?? 'Pcs') ?>)
                                    </span>
                                    <span style="font-size:0.8rem; font-weight:700; color:<?= $item['calc_profit'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                        <?= ($item['calc_profit'] >= 0 ? '+' : '') . Helper::rupiah($item['calc_profit']) ?>
                                    </span>
                                </div>
                            </div>
                            <?php else: ?>
                            <div style="background:var(--surface-2); border-radius:var(--radius-sm); padding:5px 8px; margin-top:4px;">
                                <span style="font-size:0.7rem; color:var(--text-muted); font-style:italic;">Item custom — modal tidak tercatat</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- FLAT ITEMS CONTAINER (Shown only when grouping is turned OFF) -->
            <div class="flat-items-list" id="flatItemsContainer">
                <?php 
                $seq = 0;
                foreach ($categoryGroups as $grp):
                    foreach ($grp['items'] as $item):
                        $seq++;
                ?>
                <div class="sale-item-row" data-item-name="<?= htmlspecialchars(strtolower($item['invoice_name'] ?? $item['full_name'] ?? '')) ?>">
                    <div class="item-main-flex">
                        <div class="item-name-wrap">
                            <div class="item-name">
                                <span style="color:var(--text-muted); font-size:0.75rem; margin-right:4px;">#<?= $seq ?></span>
                                <?= htmlspecialchars($item['invoice_name'] ?? $item['full_name'] ?? 'Item') ?>
                                <span class="item-unit-badge" style="margin-left:4px;">
                                    <i class="bi <?= $grp['icon'] ?>" style="font-size:0.65rem;"></i> <?= htmlspecialchars($grp['name']) ?>
                                </span>
                            </div>
                            <div class="item-price-formula">
                                <?= $item['calc_qty'] ?> <?= htmlspecialchars($item['unit_name'] ?? 'Pcs') ?> &times; <?= Helper::rupiah($item['calc_unit_price']) ?>
                            </div>
                        </div>
                        <div class="item-total-price">
                            <?= Helper::rupiah($item['calc_total_price']) ?>
                        </div>
                    </div>

                    <!-- Cost & Profit details -->
                    <?php if (!$item['is_custom']): ?>
                    <div class="item-profit-card">
                        <div>
                            <div class="profit-col-label">Modal/unit</div>
                            <div class="profit-col-val"><?= Helper::rupiah($item['calc_buy_price']) ?></div>
                        </div>
                        <div>
                            <div class="profit-col-label">Total modal</div>
                            <div class="profit-col-val"><?= Helper::rupiah($item['calc_modal']) ?></div>
                        </div>
                        <div>
                            <div class="profit-col-label">Selisih</div>
                            <div class="profit-col-val" style="color:<?= $item['calc_selisih'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                <?= ($item['calc_selisih'] >= 0 ? '+' : '') . Helper::rupiah($item['calc_selisih']) ?>
                            </div>
                        </div>
                        <div>
                            <div class="profit-col-label">Markup</div>
                            <div class="profit-col-val" style="color:<?= $item['calc_markup'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                <?= ($item['calc_markup'] >= 0 ? '+' : '') . str_replace('.', ',', round($item['calc_markup'], 1)) ?>%
                            </div>
                        </div>
                        <div class="profit-total-bar">
                            <span style="font-size:0.7rem; color:var(--text-muted);">
                                Profit (<?= $item['calc_qty'] ?> <?= htmlspecialchars($item['unit_name'] ?? 'Pcs') ?>)
                            </span>
                            <span style="font-size:0.8rem; font-weight:700; color:<?= $item['calc_profit'] >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                <?= ($item['calc_profit'] >= 0 ? '+' : '') . Helper::rupiah($item['calc_profit']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php 
                    endforeach;
                endforeach; 
                ?>
            </div>

        </div>

        <!-- RIGHT COLUMN: Transaction Info & Category Spending Breakdown Sidebar -->
        <div class="sale-detail-sidebar">
            
            <!-- Metadata Card -->
            <div class="sale-sidebar-card">
                <div class="sidebar-card-title">
                    <span><i class="bi bi-receipt"></i> Informasi Transaksi</span>
                    <button type="button" class="btn-copy-inv" onclick="copyInvoice('<?= htmlspecialchars(addslashes($sale['invoice_number'])) ?>')" title="Salin nomor invoice">
                        <i class="bi bi-copy"></i> Salin
                    </button>
                </div>

                <div class="meta-info-grid">
                    <div class="meta-field">
                        <span class="meta-label">No. Invoice</span>
                        <span class="meta-val" style="color:var(--primary);"><?= htmlspecialchars($sale['invoice_number']) ?></span>
                    </div>
                    <div class="meta-field">
                        <span class="meta-label">Waktu</span>
                        <span class="meta-val"><?= Helper::formatDate($sale['created_at'], 'H:i') ?> WIB</span>
                    </div>
                    <div class="meta-field">
                        <span class="meta-label">Pelanggan</span>
                        <span class="meta-val"><?= htmlspecialchars($sale['customer_name'] ?? 'Pelanggan Umum') ?></span>
                    </div>
                    <div class="meta-field">
                        <span class="meta-label">Mode Transaksi</span>
                        <span class="meta-val">
                            <span class="badge-custom badge-<?= $sale['sale_mode'] == 'retail' ? 'info' : 'warning' ?>">
                                <?= ucfirst($sale['sale_mode']) ?>
                            </span>
                        </span>
                    </div>
                    <div class="meta-field">
                        <span class="meta-label">Metode Pembayaran</span>
                        <span class="meta-val"><?= htmlspecialchars($sale['payment_method']) ?></span>
                    </div>
                    <div class="meta-field">
                        <span class="meta-label">Status Bayar</span>
                        <span class="meta-val">
                            <span class="badge-custom badge-success"><?= htmlspecialchars($sale['payment_status']) ?></span>
                        </span>
                    </div>
                </div>

                <?php if (!empty($sale['notes'])): ?>
                <div style="background:var(--surface-2); border-radius:var(--radius-sm); padding:10px; border:1px solid var(--border-color);">
                    <div style="font-size:0.7rem; color:var(--text-muted); margin-bottom:2px;">Catatan:</div>
                    <div style="font-size:0.8rem; color:var(--text-secondary);"><?= nl2br(htmlspecialchars($sale['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- EXECUTIVE WIDGET: Total Belanja Per Kategori -->
            <div class="sale-sidebar-card">
                <div class="sidebar-card-title">
                    <span><i class="bi bi-pie-chart-fill"></i> Total Belanja Per Kategori</span>
                    <span style="font-size:0.72rem; color:var(--text-muted); font-weight:normal;"><?= count($categoryGroups) ?> Kategori</span>
                </div>

                <div class="cat-breakdown-list">
                    <?php foreach ($categoryGroups as $grp): 
                        $pct = $saleTotalAmount > 0 ? round(($grp['total_amount'] / $saleTotalAmount) * 100, 1) : 0;
                    ?>
                    <div class="cat-breakdown-row" onclick="scrollToCategory('cat-card-<?= $grp['slug'] ?>')" title="Klik untuk lihat rincian barang kategori ini">
                        <div class="cat-row-top">
                            <span class="cat-row-title">
                                <i class="bi <?= $grp['icon'] ?>" style="color:var(--primary);"></i>
                                <?= htmlspecialchars($grp['name']) ?>
                            </span>
                            <span class="cat-row-amount"><?= Helper::rupiah($grp['total_amount']) ?></span>
                        </div>
                        <div class="cat-row-bar-wrap">
                            <div class="cat-row-bar-fill" style="width: <?= $pct ?>%;"></div>
                        </div>
                        <div class="cat-row-bottom">
                            <span><?= $grp['count'] ?> item (<?= $grp['total_qty'] ?> unit)</span>
                            <span><?= $pct ?>% dari total</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Financial Summary & Profit Card -->
            <div class="sale-sidebar-card">
                <div class="sidebar-card-title">
                    <span><i class="bi bi-cash-stack"></i> Ringkasan Finansial</span>
                </div>

                <div class="summary-line">
                    <span>Total Produk</span>
                    <span style="font-weight:600; color:var(--text-primary);"><?= $totalItemsCount ?> macam (<?= $totalQuantitySum ?> pcs)</span>
                </div>
                <div class="summary-line">
                    <span>Total Modal Belanja</span>
                    <span style="font-weight:600; color:var(--text-secondary);"><?= Helper::rupiah($totalModalSum) ?></span>
                </div>
                <div class="summary-line">
                    <span>Estimasi Profit Transaksi</span>
                    <span style="font-weight:700; color:<?= $totalProfitSum >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                        <?= Helper::rupiah($totalProfitSum) ?> <small>(<?= $netProfitMargin ?>%)</small>
                    </span>
                </div>
                <div class="summary-line grand-total">
                    <span>TOTAL TRANSAKSI</span>
                    <span class="val"><?= Helper::rupiah($sale['total_amount']) ?></span>
                </div>
            </div>

            <!-- Fast Action Footer Card in Sidebar -->
            <div class="sale-sidebar-card" style="display:flex; flex-direction:column; gap:10px;">
                <button type="button" onclick="printReceipt()" class="btn-sale-action primary" style="justify-content:center; padding:10px;">
                    <i class="bi bi-printer"></i> Cetak Struk Belanja
                </button>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <button type="button" id="btnConnectPrinterSidebar" onclick="connectPrinter()" class="btn-sale-action outline" style="justify-content:center; font-size:0.75rem;">
                        <i class="bi bi-bluetooth"></i> Printer
                    </button>
                    <a href="<?= BASE_URL ?>sales/pos?edit=<?= $sale['id'] ?>" 
                       onclick="try{localStorage.setItem('pos_edit_sale_payload', JSON.stringify(SALE_DATA));}catch(e){}" 
                       class="btn-sale-action outline" style="justify-content:center; font-size:0.75rem;">
                        <i class="bi bi-pencil-square"></i> Edit POS
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const STORE_SETTINGS = <?= json_encode($storeSettings) ?>;
    const SALE_DATA = <?= json_encode($sale) ?>;

    // ── Grouping & Accordion Controls ──────────────────────────────────────────
    let isGroupingActive = true;

    function toggleGroupingMode() {
        isGroupingActive = !isGroupingActive;
        const btn = document.getElementById('btnToggleGrouping');
        const icon = document.getElementById('iconGrouping');
        const label = document.getElementById('labelGrouping');
        const groupContainer = document.getElementById('categoryGroupsContainer');
        const flatContainer = document.getElementById('flatItemsContainer');
        const btnExpand = document.getElementById('btnExpandAll');
        const btnCollapse = document.getElementById('btnCollapseAll');
        const chipsNav = document.getElementById('catChipsNav');

        if (isGroupingActive) {
            btn.classList.add('active');
            icon.className = 'bi bi-folder2-open';
            label.textContent = 'Grouping: Aktif';
            groupContainer.style.display = 'flex';
            flatContainer.style.display = 'none';
            btnExpand.style.display = 'inline-flex';
            btnCollapse.style.display = 'inline-flex';
            if (chipsNav) chipsNav.style.display = 'flex';
            showToast('Tampilan dikelompokkan per kategori', 'info');
        } else {
            btn.classList.remove('active');
            icon.className = 'bi bi-list-ul';
            label.textContent = 'Grouping: Nonaktif';
            groupContainer.style.display = 'none';
            flatContainer.style.display = 'block';
            btnExpand.style.display = 'none';
            btnCollapse.style.display = 'none';
            if (chipsNav) chipsNav.style.display = 'none';
            showToast('Tampilan daftar penuh tanpa grouping', 'info');
        }
    }

    function toggleCategory(cardId) {
        const card = document.getElementById(cardId);
        if (card) {
            card.classList.toggle('is-open');
        }
    }

    function expandAllCategories() {
        document.querySelectorAll('.category-group-card').forEach(c => c.classList.add('is-open'));
        showToast('Semua kategori dibuka', 'info');
    }

    function collapseAllCategories() {
        document.querySelectorAll('.category-group-card').forEach(c => c.classList.remove('is-open'));
        showToast('Semua kategori ditutup', 'info');
    }

    function scrollToCategory(cardId) {
        // If grouping is turned off, turn it back on so user can see it
        if (!isGroupingActive) {
            toggleGroupingMode();
        }
        const card = document.getElementById(cardId);
        if (card) {
            // Expand this category
            card.classList.add('is-open');
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Highlight card briefly
            card.style.borderColor = 'var(--primary)';
            card.style.boxShadow = '0 0 16px rgba(var(--primary-rgb), 0.35)';
            setTimeout(() => {
                card.style.borderColor = '';
                card.style.boxShadow = '';
            }, 1200);
        }
    }

    function filterItems(query) {
        const q = (query || '').trim().toLowerCase();
        
        if (!isGroupingActive) {
            // Filter flat items list
            document.querySelectorAll('#flatItemsContainer .sale-item-row').forEach(row => {
                const name = row.getAttribute('data-item-name') || '';
                row.style.display = (name.includes(q)) ? 'block' : 'none';
            });
            return;
        }

        // In grouping mode: filter items within category cards
        document.querySelectorAll('.category-group-card').forEach(card => {
            const catName = card.getAttribute('data-category') || '';
            let hasMatch = false;

            card.querySelectorAll('.sale-item-row').forEach(row => {
                const itemName = row.getAttribute('data-item-name') || '';
                const match = itemName.includes(q) || catName.includes(q);
                row.style.display = match ? 'block' : 'none';
                if (match) hasMatch = true;
            });

            if (q === '') {
                card.style.display = 'block';
                // Reset to collapsed if search cleared
            } else if (hasMatch) {
                card.style.display = 'block';
                card.classList.add('is-open'); // Auto open matching categories!
            } else {
                card.style.display = 'none';
            }
        });
    }

    function copyInvoice(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Nomor invoice berhasil disalin', 'success');
            }).catch(() => {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            showToast('Nomor invoice berhasil disalin', 'success');
        } catch (e) {
            showToast('Gagal menyalin invoice', 'error');
        }
        document.body.removeChild(ta);
    }

    // ── Safe helper to obtain ThermalPrinter instance ──────────────────────────
    function getPrinter() {
        if (window.thermalPrinter) return window.thermalPrinter;
        if (typeof ThermalPrinter !== 'undefined') {
            window.thermalPrinter = new ThermalPrinter();
            window.thermalPrinter.setStoreSettings(STORE_SETTINGS);
            return window.thermalPrinter;
        }
        return null;
    }

    // ── UI helpers ──────────────────────────────────────────────────────────
    function updatePrinterBtn() {
        const btnHeader = document.getElementById('btnConnectPrinter');
        const btnSidebar = document.getElementById('btnConnectPrinterSidebar');
        const tp = getPrinter();
        if (!tp) return;

        let html = '<i class="bi bi-bluetooth"></i> Hubungkan';
        let bg = '';
        let color = '';
        let borderColor = '';
        let title = 'Hubungkan printer Bluetooth thermal';

        if (tp.isConnected && tp.isConnected()) {
            html = `<i class="bi bi-bluetooth-fill"></i> ${tp.device?.name || 'Terhubung'}`;
            bg = 'var(--success-bg)';
            color = 'var(--success)';
            borderColor = 'var(--success)';
            title = 'Klik untuk putuskan koneksi';
        } else if (tp.hasSavedDevice && tp.hasSavedDevice()) {
            html = `<i class="bi bi-bluetooth"></i> ${tp.lastConnectedDevice?.name || 'Tersimpan'}`;
            bg = 'var(--warning-bg)';
            color = 'var(--warning)';
            borderColor = 'var(--warning)';
            title = 'Klik untuk hubungkan ke printer tersimpan';
        }

        [btnHeader, btnSidebar].forEach(btn => {
            if (!btn) return;
            btn.innerHTML = html;
            btn.style.background = bg;
            btn.style.color = color;
            btn.style.borderColor = borderColor;
            btn.title = title;
        });
    }

    // ── Auto-reconnect on page load ─────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        updatePrinterBtn();

        const tp = getPrinter();
        if (tp && !tp.isIOS && tp.hasBluetoothAPI && (tp.device || (tp.hasSavedDevice && tp.hasSavedDevice())) && !tp.isConnected()) {
            const btn = document.getElementById('btnConnectPrinter');
            if (btn) {
                btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i>';
                btn.disabled = true;
            }
            tp.tryAutoReconnect().then(ok => {
                if (ok) showToast(`Printer terhubung: ${tp.device?.name || 'Bluetooth'}`, 'success');
            }).catch(() => {}).finally(() => {
                updatePrinterBtn();
                if (btn) btn.disabled = false;
            });
        }
    });

    // ── Connect / disconnect toggle ─────────────────────────────────────────
    async function connectPrinter() {
        const tp = getPrinter();
        if (!tp) {
            showToast('Modul printer belum siap. Silakan refresh halaman.', 'warning');
            return;
        }

        if (tp.isIOS || !tp.hasBluetoothAPI) {
            showToast('Perangkat ini menggunakan cetak via Browser/AirPrint.', 'info');
            return;
        }

        const btn = document.getElementById('btnConnectPrinter');
        const prevHTML = btn ? btn.innerHTML : '';
        if (btn) { btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i>'; btn.disabled = true; }

        try {
            if (tp.isConnected()) {
                tp.disconnect();
                tp.clearLastDevice();
                showToast('Printer diputuskan', 'info');
            } else {
                let ok = false;
                if (typeof window.openPrinterChooser === 'function') {
                    ok = await window.openPrinterChooser(tp);
                } else {
                    ok = (tp.device || (tp.hasSavedDevice && tp.hasSavedDevice())) ? await tp.tryAutoReconnect() : false;
                    if (!ok) { await tp.connect(); ok = tp.isConnected(); }
                }
                if (ok) showToast(`Printer terhubung: ${tp.device?.name || 'Bluetooth'}`, 'success');
            }
        } catch (e) {
            showToast(e.message || 'Gagal menghubungkan printer', 'error');
            if (btn) { btn.innerHTML = prevHTML; btn.disabled = false; }
        }

        updatePrinterBtn();
        if (btn) btn.disabled = false;
    }

    // ── Print receipt ───────────────────────────────────────────────────────
    async function printReceipt() {
        const tp = getPrinter();
        if (!tp) {
            window.print();
            return;
        }

        const cartData = (SALE_DATA.items || []).map(i => ({
            name: i.invoice_name || i.full_name || 'Item',
            print_name: i.invoice_name || i.full_name || 'Item',
            quantity: parseFloat(i.quantity) || 1,
            unit_price: parseFloat(i.unit_price) || 0,
            total: parseFloat(i.total_price) || 0,
            unit_name: i.unit_name || 'pcs'
        }));

        const btns = document.querySelectorAll('[onclick="printReceipt()"]');
        btns.forEach(b => { b.disabled = true; b.dataset.prevHtml = b.innerHTML; b.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Mencetak...'; });

        try {
            if (tp.isIOS || !tp.hasBluetoothAPI) {
                await tp.printBrowser(cartData, parseFloat(SALE_DATA.total_amount), SALE_DATA.invoice_number, {
                    paymentMethod: SALE_DATA.payment_method
                });
            } else {
                if (!tp.isConnected()) {
                    const ok = (tp.device || (tp.hasSavedDevice && tp.hasSavedDevice())) ? await tp.tryAutoReconnect() : false;
                    if (!ok) {
                        await tp.connect();
                    }
                    updatePrinterBtn();
                }

                await tp.print(cartData, parseFloat(SALE_DATA.total_amount), SALE_DATA.invoice_number, {
                    paymentMethod: SALE_DATA.payment_method,
                    storeSettings: STORE_SETTINGS,
                });
                showToast('Struk berhasil dicetak ke printer thermal', 'success');
            }
        } catch (err) {
            console.error('[Detail] printReceipt error:', err);
            showToast('Bluetooth gagal, mencetak via browser...', 'warning');
            try {
                await tp.printBrowser(cartData, parseFloat(SALE_DATA.total_amount), SALE_DATA.invoice_number, {
                    paymentMethod: SALE_DATA.payment_method
                });
            } catch (e2) {
                showToast('Gagal mencetak: ' + (e2.message || err.message), 'error');
            }
        } finally {
            updatePrinterBtn();
            btns.forEach(b => { b.disabled = false; if (b.dataset.prevHtml) b.innerHTML = b.dataset.prevHtml; });
        }
    }
</script>
