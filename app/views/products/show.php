<?php
/**
 * @var string $csrfToken
 * @var array $product
 * @var bool $productFound
 * @var array $packagings
 * @var array $suppliers
 * @var array $salesReps
 */
$productFound = $productFound ?? true;
$isStaffShow = (($_SESSION['user_level'] ?? '') === 'staff');
$isSuperadmin = (($_SESSION['user_level'] ?? '') === 'superadmin');

// Calculate base financial insights
$basePkg = !empty($packagings) ? $packagings[0] : null;
$baseBuyPrice = $basePkg ? (float)$basePkg['buy_price'] : 0;
$baseSellRetail = $basePkg ? (float)$basePkg['sell_price_retail'] : 0;
$baseSellWholesale = $basePkg ? (float)$basePkg['sell_price_wholesale'] : 0;
$currentStock = (float)($product['current_qty_base'] ?? 0);
$baseUnitName = $basePkg['unit_name'] ?? 'Pcs';

$totalAssetValue = $currentStock * $baseBuyPrice;
$retailMarginAmt = $baseSellRetail - $baseBuyPrice;
$retailMarginPct = $baseBuyPrice > 0 ? round(($retailMarginAmt / $baseBuyPrice) * 100, 1) : 0;
$wholesaleMarginAmt = $baseSellWholesale > 0 ? ($baseSellWholesale - $baseBuyPrice) : 0;
$wholesaleMarginPct = ($baseBuyPrice > 0 && $baseSellWholesale > 0) ? round(($wholesaleMarginAmt / $baseBuyPrice) * 100, 1) : 0;

$stockStatusClass = 'stock-safe';
$stockStatusLabel = 'Stok Aman';
if ($currentStock <= 0) {
    $stockStatusClass = 'stock-empty';
    $stockStatusLabel = 'Habis';
} elseif ($currentStock <= 5) {
    $stockStatusClass = 'stock-low';
    $stockStatusLabel = 'Menipis';
}
?>

<input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">
<input type="hidden" id="serverProductFound" value="<?= $productFound ? '1' : '0' ?>">
<input type="hidden" id="productIdHidden" value="<?= (int)$product['id'] ?>">

<div class="page-section product-detail-container" id="productDetailWrapper">
    <!-- Top Action Bar / Header -->
    <div class="product-top-bar">
        <a href="<?= BASE_URL ?>products" class="back-link">
            <i class="bi bi-arrow-left"></i> <span>Daftar Produk</span>
        </a>
        <div class="top-actions">
            <?php if (!$isStaffShow): ?>
                <a href="<?= BASE_URL ?>products/<?= (int)$product['id'] ?>/edit" class="btn-action-edit" id="btnTopEditProduct">
                    <i class="bi bi-pencil-square"></i> <span>Edit Produk</span>
                </a>
            <?php endif; ?>
            <button type="button" class="btn-action-opname" onclick="openUpdateStockModal()" title="Update Stok Fisik">
                <i class="bi bi-box-seam"></i> <span>Opname Stok</span>
            </button>
            <?php if (!$isStaffShow): ?>
                <button type="button" class="btn-action-more" onclick="toggleProductMenu(event)" title="Opsi Lainnya">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="dropdown-menu-product" id="productDropdownMenu" style="display:none;">
                    <button type="button" onclick="choosePhotoMethod(); hideProductMenu();">
                        <i class="bi bi-camera"></i> Ubah Foto Produk
                    </button>
                    <button type="button" onclick="quickToggleAvailabilityDetail(<?= (int)$product['id'] ?>); hideProductMenu();">
                        <i class="bi bi-toggle-on"></i> <?= (!isset($product['is_available']) || $product['is_available'] == 1) ? 'Nonaktifkan Produk' : 'Aktifkan Produk' ?>
                    </button>
                    <button type="button" class="text-danger" onclick="deleteProduct(<?= (int)$product['id'] ?>, '<?= htmlspecialchars(addslashes($product['short_label'] ?? $product['full_name'])) ?>'); hideProductMenu();">
                        <i class="bi bi-trash"></i> Hapus Produk
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- NOT FOUND EMPTY STATE (Visible if server didn't find product and client is checking) -->
    <div id="productNotFoundState" style="<?= $productFound ? 'display:none;' : 'display:block;' ?>">
        <div class="empty-state-card">
            <div class="empty-icon-wrap"><i class="bi bi-search"></i></div>
            <h3>Memeriksa Detail Produk...</h3>
            <p id="notFoundSubtext">Mencari data produk di database lokal offline...</p>
            <div class="empty-actions" style="margin-top:16px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <a href="<?= BASE_URL ?>products" class="btn-outline-custom">
                    <i class="bi bi-arrow-left"></i> Kembali ke Katalog
                </a>
                <button type="button" onclick="window.location.reload()" class="btn-primary-custom">
                    <i class="bi bi-arrow-clockwise"></i> Muat Ulang
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN PRODUCT CONTENT -->
    <div id="productMainContent" style="<?= $productFound ? 'display:block;' : 'display:none;' ?>">
        <!-- Hero Product Card -->
        <div class="product-hero-card">
            <div class="hero-photo-col">
                <div class="photo-wrapper" onclick="choosePhotoMethod()" title="Klik untuk ubah foto">
                    <?php if (!empty($product['photo'])): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($product['photo']) ?>"
                             id="productHeroImg"
                             alt="<?= htmlspecialchars($product['full_name']) ?>"
                             onclick="event.stopPropagation(); viewFullPhoto(this.src)"
                             title="Klik untuk zoom foto">
                    <?php else: ?>
                        <div class="photo-placeholder" id="productPhotoPlaceholder">
                            <i class="bi bi-camera-fill"></i>
                            <span>FOTO</span>
                        </div>
                    <?php endif; ?>
                    <div class="photo-overlay-badge">
                        <i class="bi bi-camera"></i> Ubah
                    </div>
                </div>
                <input type="file" id="productPhotoInputCamera" accept="image/*" capture="environment" style="display:none;" onchange="handleProductPhoto(event)">
                <input type="file" id="productPhotoInputGallery" accept="image/*" style="display:none;" onchange="handleProductPhoto(event)">
            </div>

            <div class="hero-details-col">
                <div class="hero-badges-row">
                    <?php $pIsAvail = !isset($product['is_available']) || $product['is_available'] == 1; ?>
                    <span class="status-pill <?= $pIsAvail ? 'status-active' : 'status-inactive' ?>" id="displayStatusPill">
                        <span class="status-dot"></span> <?= $pIsAvail ? 'Tersedia' : 'Nonaktif' ?>
                    </span>
                    <?php if (!empty($product['brand_name'])): ?>
                        <span class="badge-tag brand-tag" id="displayBrandTag">
                            <i class="bi bi-bookmark-star"></i> <?= htmlspecialchars($product['brand_name']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($product['category_name'])): ?>
                        <span class="badge-tag category-tag" id="displayCategoryTag">
                            <i class="bi bi-grid"></i> <?= htmlspecialchars($product['category_name']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="product-title" id="displayFullName">
                    <?= htmlspecialchars($product['full_name']) ?>
                </h1>

                <div class="product-meta-grid">
                    <div class="meta-item">
                        <span class="meta-label"><i class="bi bi-receipt"></i> Label Thermal:</span>
                        <span class="meta-value label-highlight" id="displayShortLabel">
                            <?= htmlspecialchars($product['short_label'] ?: '-') ?>
                        </span>
                    </div>

                    <?php if (!empty($product['code'])): ?>
                    <div class="meta-item" id="displayCodeWrap">
                        <span class="meta-label"><i class="bi bi-upc-scan"></i> SKU / Kode:</span>
                        <span class="meta-value code-font" id="displayCode"><?= htmlspecialchars($product['code']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($product['weight_value'])): ?>
                    <div class="meta-item" id="displayWeightWrap">
                        <span class="meta-label"><i class="bi bi-speedometer2"></i> Bobot / Isi:</span>
                        <span class="meta-value" id="displayWeight"><?= htmlspecialchars($product['weight_value'] . ' ' . $product['weight_unit']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($product['supplier_product_code'])): ?>
                    <div class="meta-item" id="displaySupplierCodeWrap">
                        <span class="meta-label"><i class="bi bi-hash"></i> Kode Supplier:</span>
                        <span class="meta-value code-font" id="displaySupplierCode"><?= htmlspecialchars($product['supplier_product_code']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Stock Overview in Hero -->
                <div class="hero-stock-banner <?= $stockStatusClass ?>" id="heroStockBanner">
                    <div class="stock-info-left">
                        <div class="stock-title">
                            <i class="bi bi-box-seam-fill"></i> Total Stok Fisik
                        </div>
                        <div class="stock-number-wrap">
                            <span class="stock-number" id="currentStockDisplay"><?= (float)$currentStock ?></span>
                            <span class="stock-unit" id="baseUnitDisplay"><?= htmlspecialchars($baseUnitName) ?></span>
                            <span class="stock-badge <?= $stockStatusClass ?>" id="stockStatusBadge"><?= $stockStatusLabel ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-quick-opname" onclick="openUpdateStockModal()">
                        <i class="bi bi-pencil"></i> Sesuaikan Stok
                    </button>
                </div>
            </div>
        </div>

        <!-- 4-Grid Financial & Margin Insights -->
        <div class="insight-grid-container">
            <div class="insight-card">
                <div class="insight-icon bg-info-subtle"><i class="bi bi-boxes"></i></div>
                <div class="insight-content">
                    <span class="insight-label">Stok Fisik</span>
                    <strong class="insight-value" id="statStockVal"><?= (float)$currentStock ?> <?= htmlspecialchars($baseUnitName) ?></strong>
                    <span class="insight-sub"><?= count($packagings) ?> Tingkat Kemasan</span>
                </div>
            </div>
            <div class="insight-card">
                <div class="insight-icon bg-success-subtle"><i class="bi bi-cash-stack"></i></div>
                <div class="insight-content">
                    <span class="insight-label">Estimasi Nilai Stok</span>
                    <strong class="insight-value" id="statAssetVal"><?= Helper::rupiah($totalAssetValue) ?></strong>
                    <span class="insight-sub">Modal x Total Unit</span>
                </div>
            </div>
            <div class="insight-card">
                <div class="insight-icon bg-primary-subtle"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="insight-content">
                    <span class="insight-label">Margin Ecer (<?= htmlspecialchars($baseUnitName) ?>)</span>
                    <strong class="insight-value text-success" id="statMarginRetail">
                        <?= Helper::rupiah($retailMarginAmt) ?>
                    </strong>
                    <span class="insight-sub"><?= $retailMarginPct ?>% Profit Margin</span>
                </div>
            </div>
            <div class="insight-card">
                <div class="insight-icon bg-warning-subtle"><i class="bi bi-shop"></i></div>
                <div class="insight-content">
                    <span class="insight-label">Margin Grosir (<?= htmlspecialchars($baseUnitName) ?>)</span>
                    <strong class="insight-value text-warning" id="statMarginWholesale">
                        <?= $baseSellWholesale > 0 ? Helper::rupiah($wholesaleMarginAmt) : '-' ?>
                    </strong>
                    <span class="insight-sub"><?= $baseSellWholesale > 0 ? $wholesaleMarginPct . '% Profit Margin' : 'Belum diatur' ?></span>
                </div>
            </div>
        </div>

        <!-- Packaging & Pricing Levels -->
        <div class="card-section">
            <div class="section-header">
                <div class="section-title-wrap">
                    <h2 class="section-title"><i class="bi bi-layers-fill text-primary"></i> Level Kemasan &amp; Harga Jual</h2>
                    <span class="section-badge" id="pkgCountBadge"><?= count($packagings) ?> Level Terdaftar</span>
                </div>
                <?php if (!$isStaffShow): ?>
                <a href="<?= BASE_URL ?>products/<?= (int)$product['id'] ?>/edit#packagingSection" class="btn-section-link">
                    <i class="bi bi-gear"></i> Atur Kemasan
                </a>
                <?php endif; ?>
            </div>

            <div id="packagingListContainer" class="packaging-cards-grid">
                <?php if (empty($packagings)): ?>
                    <div class="empty-packaging-card" id="emptyPackagingNotice">
                        <i class="bi bi-box"></i>
                        <p>Belum ada data kemasan &amp; harga untuk produk ini.</p>
                        <?php if (!$isStaffShow): ?>
                        <a href="<?= BASE_URL ?>products/<?= (int)$product['id'] ?>/edit" class="btn-primary-custom" style="padding:8px 16px;font-size:12px;margin-top:10px;">
                            <i class="bi bi-plus-circle"></i> Tambah Kemasan Sekarang
                        </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($packagings as $i => $p): ?>
                        <?php
                            $buyP = (float)($p['buy_price'] ?? 0);
                            $sellR = (float)($p['sell_price_retail'] ?? 0);
                            $sellW = (float)($p['sell_price_wholesale'] ?? 0);
                            $mRetail = $sellR - $buyP;
                            $mRetailPct = $buyP > 0 ? round(($mRetail / $buyP) * 100, 1) : 0;
                            $mWhole = $sellW > 0 ? ($sellW - $buyP) : 0;
                            $mWholePct = ($buyP > 0 && $sellW > 0) ? round(($mWhole / $buyP) * 100, 1) : 0;
                        ?>
                        <div class="packaging-card <?= $i === 0 ? 'base-packaging' : '' ?>">
                            <div class="pkg-card-header">
                                <div class="pkg-level-badge">
                                    Level <?= (int)$p['level'] ?>
                                    <?php if ($p['level'] == 1): ?>
                                        <span class="pkg-base-pill">Satuan Dasar</span>
                                    <?php endif; ?>
                                </div>
                                <div class="pkg-unit-name">
                                    1 <?= htmlspecialchars($p['unit_name']) ?>
                                </div>
                                <div class="pkg-ratio-text">
                                    <?php if ($p['level'] == 1): ?>
                                        Unit terkecil (Base)
                                    <?php else: ?>
                                        Isi: <strong><?= $p['contained_qty'] ?> <?= htmlspecialchars($packagings[$i-1]['unit_name'] ?? '') ?></strong>
                                        <span class="text-muted">(Total: <?= $p['base_qty'] ?> <?= htmlspecialchars($packagings[0]['unit_name'] ?? 'Pcs') ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Barcode Section -->
                            <div class="pkg-barcode-row">
                                <?php if (!empty($p['barcode'])): ?>
                                    <div class="barcode-badge">
                                        <i class="bi bi-upc"></i>
                                        <span class="barcode-num"><?= htmlspecialchars($p['barcode']) ?></span>
                                    </div>
                                    <button type="button" class="btn-barcode-print" onclick="printBarcodeShow('<?= htmlspecialchars(addslashes($p['barcode'])) ?>', '<?= htmlspecialchars(addslashes($product['short_label'] ?: $product['full_name'])) ?>', '<?= htmlspecialchars(addslashes($p['unit_name'])) ?>')">
                                        <i class="bi bi-printer"></i> Cetak
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn-barcode-gen" onclick="generateAndPrintBarcodeShow(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(addslashes($product['short_label'] ?: $product['full_name'])) ?>', '<?= htmlspecialchars(addslashes($p['unit_name'])) ?>', <?= $buyP ?>, <?= $sellR ?>, <?= $sellW ?>)">
                                        <i class="bi bi-magic"></i> Generate &amp; Cetak Barcode
                                    </button>
                                <?php endif; ?>
                            </div>

                            <!-- Prices Grid -->
                            <div class="pkg-price-matrix">
                                <div class="price-box price-buy">
                                    <span class="price-box-label">Harga Modal (Beli)</span>
                                    <strong class="price-box-val"><?= Helper::rupiah($buyP) ?></strong>
                                </div>

                                <div class="price-box price-retail">
                                    <div class="price-box-top">
                                        <span class="price-box-label">Harga Ecer</span>
                                        <span class="margin-pill margin-green">+<?= Helper::rupiah($mRetail) ?> (<?= $mRetailPct ?>%)</span>
                                    </div>
                                    <strong class="price-box-val text-success"><?= Helper::rupiah($sellR) ?></strong>
                                </div>

                                <div class="price-box price-wholesale">
                                    <div class="price-box-top">
                                        <span class="price-box-label">Harga Grosir</span>
                                        <?php if ($sellW > 0): ?>
                                            <span class="margin-pill margin-amber">+<?= Helper::rupiah($mWhole) ?> (<?= $mWholePct ?>%)</span>
                                        <?php endif; ?>
                                    </div>
                                    <strong class="price-box-val <?= $sellW > 0 ? 'text-warning' : 'text-muted' ?>">
                                        <?= $sellW > 0 ? Helper::rupiah($sellW) : 'Belum diatur' ?>
                                    </strong>
                                </div>
                            </div>

                            <!-- Tiered Pricing if any -->
                            <?php if (!empty($p['qty_prices'])): ?>
                            <div class="pkg-tier-box">
                                <div class="tier-title"><i class="bi bi-tags-fill"></i> Harga Grosir Bertingkat:</div>
                                <div class="tier-list">
                                    <?php foreach ($p['qty_prices'] as $tier): ?>
                                        <?php
                                            $tMode = match ($tier['sale_mode'] ?? 'both') {
                                                'retail' => 'Ecer',
                                                'wholesale' => 'Grosir',
                                                default => 'Ecer & Grosir',
                                            };
                                        ?>
                                        <div class="tier-item">
                                            <span>&ge; <?= (float)$tier['min_qty'] ?> <?= htmlspecialchars($p['unit_name']) ?></span>
                                            <strong><?= Helper::rupiah($tier['unit_price']) ?></strong>
                                            <span class="tier-mode">(<?= $tMode ?>)</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2-Column Section: Thermal Label & Supplier Contact -->
        <div class="info-split-grid">
            <!-- Thermal Label Quick Box -->
            <div class="card-section">
                <div class="section-header">
                    <div class="section-title-wrap">
                        <h2 class="section-title"><i class="bi bi-printer-fill text-warning"></i> Label Cetak Struk</h2>
                    </div>
                </div>
                <p class="section-desc">
                    Nama ringkas untuk printer thermal kasir (maksimal 35 karakter).
                </p>
                <div class="input-with-counter">
                    <input type="text" id="inputShortLabel" class="form-control-dark" maxlength="35" value="<?= htmlspecialchars($product['short_label'] ?? '') ?>" placeholder="Cth: Indomie Goreng 85g">
                    <span class="char-counter"><span id="labelCharCount">0</span>/35</span>
                </div>
                <div class="label-actions-row">
                    <button type="button" class="btn-primary-custom" onclick="saveProductLabel(<?= (int)$product['id'] ?>)">
                        <i class="bi bi-check-lg"></i> Simpan Label
                    </button>
                    <button type="button" class="btn-outline-custom" onclick="openDistributeLabelModal(<?= (int)$product['id'] ?>)">
                        <i class="bi bi-share"></i> Terapkan ke Varian
                    </button>
                </div>
            </div>

            <!-- Supplier & Sales Rep Information -->
            <div class="card-section">
                <div class="section-header">
                    <div class="section-title-wrap">
                        <h2 class="section-title"><i class="bi bi-truck text-info"></i> Informasi Supplier &amp; Sales</h2>
                    </div>
                </div>
                
                <div class="supplier-selection-box">
                    <label class="form-sublabel">Pilih Supplier Pembelian</label>
                    <div id="supplierSearchBox"></div>
                </div>

                <div id="salesRepContainer" style="display:none;margin-top:12px;">
                    <div id="multipleSalesReps" style="display:none;flex-direction:column;gap:8px;">
                        <label class="form-sublabel">Pilih Kontak Sales Representative</label>
                        <div id="salesRepSearchBox"></div>
                    </div>

                    <div id="singleSalesRep" class="sales-rep-card" style="display:none;">
                        <div class="sales-avatar"><i class="bi bi-person-fill"></i></div>
                        <div class="sales-meta">
                            <strong id="singleSalesName" class="sales-name">-</strong>
                            <span id="singleSalesPhone" class="sales-phone">-</span>
                        </div>
                    </div>

                    <a id="waContactBtn" href="#" target="_blank" class="btn-whatsapp">
                        <i class="bi bi-whatsapp"></i> <span id="waBtnText">Hubungi Sales (WhatsApp)</span>
                    </a>

                    <div id="noSalesMsg" class="no-sales-notice" style="display:none;">
                        <i class="bi bi-info-circle"></i> Tidak ada sales aktif terdaftar untuk supplier ini.
                    </div>
                </div>

                <!-- Invoice Multi-Name Aliases -->
                <div class="invoice-aliases-box">
                    <label class="form-sublabel">Nama di Invoice Supplier (AI Scan Matcher)</label>
                    <div id="showInvoiceNameList" class="invoice-name-list"></div>
                    <button type="button" class="btn-add-alias" onclick="showAddInvoiceName()">
                        <i class="bi bi-plus-circle"></i> Tambah Variasi Nama Invoice
                    </button>
                    <button type="button" id="btnSaveSupplierInfo" class="btn-save-supplier-info" onclick="saveSupplierInfo(<?= (int)$product['id'] ?>)">
                        <i class="bi bi-check2-circle"></i> Simpan Alias Invoice
                    </button>
                </div>
            </div>
        </div>

        <!-- Desktop Action Footer (Optional extra shortcut) -->
        <div class="product-bottom-desktop-actions">
            <a href="<?= BASE_URL ?>products" class="btn-outline-custom">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Produk
            </a>
            <?php if (!$isStaffShow): ?>
            <div style="display:flex;gap:8px;">
                <button type="button" class="btn-outline-custom text-danger" onclick="deleteProduct(<?= (int)$product['id'] ?>, '<?= htmlspecialchars(addslashes($product['short_label'] ?? $product['full_name'])) ?>')">
                    <i class="bi bi-trash"></i> Hapus Produk
                </button>
                <a href="<?= BASE_URL ?>products/<?= (int)$product['id'] ?>/edit" class="btn-primary-custom">
                    <i class="bi bi-pencil-square"></i> Edit Produk
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- STYLES -->
<style>
/* CSS Reset & Variables for Product Detail */
.product-detail-container {
    max-width: 1100px;
    margin: 0 auto;
    padding-bottom: 24px;
}

/* Top Bar */
.product-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    gap: 12px;
}
.back-link {
    color: var(--text-muted);
    text-decoration: none;
    font-size: var(--font-size-sm);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
}
.back-link:hover {
    color: var(--text-primary);
    background: var(--surface-2);
}
.top-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
}
.btn-action-edit {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, var(--primary) 0%, #3b82f6 100%);
    color: #ffffff !important;
    text-decoration: none;
    font-size: var(--font-size-sm);
    font-weight: 600;
    padding: 8px 16px;
    border-radius: var(--radius-md);
    box-shadow: 0 2px 8px rgba(59,130,246,0.3);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.btn-action-edit:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59,130,246,0.4);
}
.btn-action-opname {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--surface-1);
    color: var(--success);
    border: 1px solid var(--success);
    font-size: var(--font-size-sm);
    font-weight: 600;
    padding: 8px 14px;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-action-opname:hover {
    background: var(--success-bg);
}
.btn-action-more {
    width: 36px;
    height: 36px;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.dropdown-menu-product {
    position: absolute;
    top: 42px;
    right: 0;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    z-index: 100;
    min-width: 180px;
    overflow: hidden;
}
.dropdown-menu-product button {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 10px 14px;
    color: var(--text-primary);
    font-size: var(--font-size-xs);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.15s ease;
}
.dropdown-menu-product button:hover {
    background: var(--surface-2);
}

/* Empty State Card */
.empty-state-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 48px 24px;
    text-align: center;
}
.empty-icon-wrap {
    width: 64px;
    height: 64px;
    background: var(--primary-bg);
    color: var(--primary);
    border-radius: 50%;
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

/* Hero Product Card */
.product-hero-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 20px;
    margin-bottom: 16px;
    display: flex;
    gap: 20px;
    position: relative;
}
.hero-photo-col {
    flex-shrink: 0;
}
.photo-wrapper {
    width: 96px;
    height: 96px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    position: relative;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.photo-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.photo-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 28px;
}
.photo-placeholder span {
    font-size: 9px;
    font-weight: 700;
    margin-top: 2px;
    color: var(--text-muted);
}
.photo-overlay-badge {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.65);
    color: #fff;
    font-size: 9px;
    font-weight: 600;
    text-align: center;
    padding: 2px 0;
}
.hero-details-col {
    flex: 1;
    min-width: 0;
}
.hero-badges-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    margin-bottom: 8px;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.status-active {
    background: var(--success-bg);
    color: var(--success);
}
.status-inactive {
    background: var(--surface-2);
    color: var(--text-muted);
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}
.badge-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
}
.brand-tag {
    background: rgba(59,130,246,0.12);
    color: var(--info);
}
.category-tag {
    background: rgba(168,85,247,0.12);
    color: #a855f7;
}
.product-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 10px 0;
    line-height: 1.35;
    word-break: break-word;
}
.product-meta-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 20px;
    margin-bottom: 14px;
    font-size: var(--font-size-xs);
}
.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.meta-label {
    color: var(--text-muted);
}
.meta-value {
    color: var(--text-primary);
    font-weight: 600;
}
.label-highlight {
    color: var(--info);
    background: rgba(59,130,246,0.08);
    padding: 2px 6px;
    border-radius: 4px;
}
.code-font {
    font-family: monospace;
}

/* Hero Stock Banner */
.hero-stock-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-radius: var(--radius-md);
    background: var(--surface-2);
    border: 1px solid var(--border-color);
}
.hero-stock-banner.stock-safe {
    background: rgba(34, 197, 94, 0.08);
    border-color: rgba(34, 197, 94, 0.25);
}
.hero-stock-banner.stock-low {
    background: rgba(245, 158, 11, 0.08);
    border-color: rgba(245, 158, 11, 0.25);
}
.hero-stock-banner.stock-empty {
    background: rgba(239, 68, 68, 0.08);
    border-color: rgba(239, 68, 68, 0.25);
}
.stock-title {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}
.stock-number-wrap {
    display: flex;
    align-items: baseline;
    gap: 6px;
    margin-top: 2px;
}
.stock-number {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--text-primary);
}
.stock-unit {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    font-weight: 600;
}
.stock-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 4px;
}
.stock-badge.stock-safe { background: var(--success-bg); color: var(--success); }
.stock-badge.stock-low { background: var(--warning-bg); color: var(--warning); }
.stock-badge.stock-empty { background: var(--danger-bg); color: var(--danger); }
.btn-quick-opname {
    background: var(--surface-1);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    padding: 6px 12px;
    border-radius: var(--radius-sm);
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: background 0.15s ease;
}
.btn-quick-opname:hover {
    background: var(--surface-2);
}

/* 4-Grid Financial Insights */
.insight-grid-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}
.insight-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.insight-icon {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.bg-info-subtle { background: rgba(59,130,246,0.15); color: var(--info); }
.bg-success-subtle { background: rgba(34,197,94,0.15); color: var(--success); }
.bg-primary-subtle { background: rgba(99,102,241,0.15); color: #6366f1; }
.bg-warning-subtle { background: rgba(245,158,11,0.15); color: var(--warning); }
.insight-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.insight-label {
    font-size: 11px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.insight-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.insight-sub {
    font-size: 10px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* Card Sections */
.card-section {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    margin-bottom: 16px;
}
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 8px;
}
.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-title {
    font-size: var(--font-size-sm);
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.section-badge {
    font-size: 10px;
    font-weight: 600;
    background: var(--surface-2);
    color: var(--text-muted);
    padding: 2px 8px;
    border-radius: 12px;
}
.section-desc {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    margin: -6px 0 12px 0;
}
.btn-section-link {
    font-size: var(--font-size-xs);
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Packaging Cards Grid */
.packaging-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 14px;
}
.empty-packaging-card {
    grid-column: 1 / -1;
    text-align: center;
    padding: 32px 16px;
    background: var(--surface-2);
    border: 1px dashed var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-muted);
}
.empty-packaging-card i {
    font-size: 2rem;
    opacity: 0.5;
    margin-bottom: 8px;
    display: block;
}
.packaging-card {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    transition: transform 0.15s ease, border-color 0.15s ease;
}
.packaging-card.base-packaging {
    border-color: rgba(59, 130, 246, 0.4);
    background: linear-gradient(180deg, var(--surface-2) 0%, rgba(59,130,246,0.03) 100%);
}
.pkg-card-header {
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 10px;
}
.pkg-level-badge {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
}
.pkg-base-pill {
    background: var(--primary-bg);
    color: var(--primary);
    padding: 2px 6px;
    border-radius: 4px;
}
.pkg-unit-name {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text-primary);
}
.pkg-ratio-text {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* Barcode Row */
.pkg-barcode-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--surface-1);
    padding: 8px 10px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-color);
    gap: 8px;
}
.barcode-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: monospace;
    font-size: 11px;
    color: var(--info);
}
.btn-barcode-print, .btn-barcode-gen {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    padding: 4px 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}
.btn-barcode-gen {
    width: 100%;
    justify-content: center;
    color: var(--primary);
}

/* Price Matrix */
.pkg-price-matrix {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.price-box {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 8px 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.price-box-top {
    display: flex;
    flex-direction: column;
}
.price-box-label {
    font-size: 10px;
    color: var(--text-muted);
    text-transform: uppercase;
    font-weight: 600;
}
.price-box-val {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-primary);
}
.margin-pill {
    font-size: 9px;
    font-weight: 600;
    border-radius: 4px;
    padding: 1px 4px;
    margin-top: 2px;
    width: fit-content;
}
.margin-green { background: var(--success-bg); color: var(--success); }
.margin-amber { background: var(--warning-bg); color: var(--warning); }

/* Tier list */
.pkg-tier-box {
    background: rgba(59,130,246,0.06);
    border: 1px dashed rgba(59,130,246,0.25);
    border-radius: var(--radius-sm);
    padding: 8px 10px;
}
.tier-title {
    font-size: 10px;
    font-weight: 700;
    color: var(--info);
    margin-bottom: 4px;
}
.tier-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.tier-item {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: var(--text-secondary);
}
.tier-mode {
    font-size: 10px;
    color: var(--text-muted);
}

/* 2-Column Info Grid */
.info-split-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.input-with-counter {
    position: relative;
    margin-bottom: 12px;
}
.input-with-counter input {
    width: 100%;
    padding-right: 50px;
}
.char-counter {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 10px;
    color: var(--text-muted);
    pointer-events: none;
}
.label-actions-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.label-actions-row button {
    flex: 1;
    min-width: 130px;
    padding: 9px 12px;
    font-size: var(--font-size-xs);
}

.form-sublabel {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    display: block;
    margin-bottom: 6px;
}
.sales-rep-card {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    padding: 10px 12px;
    border-radius: var(--radius-md);
    margin-top: 10px;
}
.sales-avatar {
    width: 36px;
    height: 36px;
    background: var(--primary-bg);
    color: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.sales-meta {
    display: flex;
    flex-direction: column;
}
.sales-name {
    font-size: var(--font-size-xs);
    color: var(--text-primary);
}
.sales-phone {
    font-size: 11px;
    color: var(--text-muted);
}
.btn-whatsapp {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #25D366;
    color: #ffffff !important;
    padding: 10px 14px;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: 600;
    font-size: var(--font-size-xs);
    margin-top: 10px;
    box-shadow: 0 4px 10px rgba(37,211,102,0.25);
    transition: transform 0.15s ease;
}
.btn-whatsapp:hover {
    transform: translateY(-1px);
}
.no-sales-notice {
    font-size: 11px;
    color: var(--text-muted);
    text-align: center;
    padding: 8px 0;
}

.invoice-aliases-box {
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid var(--border-color);
}
.invoice-name-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 8px;
}
.btn-add-alias {
    width: 100%;
    border: 1px dashed var(--border-color);
    background: transparent;
    color: var(--info);
    padding: 7px;
    border-radius: var(--radius-sm);
    font-size: 11px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 8px;
}
.btn-save-supplier-info {
    width: 100%;
    padding: 8px 12px;
    background: var(--info);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: 600;
    cursor: pointer;
}

/* Bottom Desktop Actions */
.product-bottom-desktop-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid var(--border-color);
}

/* Responsive Breakpoints */
@media (max-width: 900px) {
    .insight-grid-container {
        grid-template-columns: repeat(2, 1fr);
    }
    .info-split-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .product-hero-card {
        flex-direction: column;
        padding: 16px;
    }
    .hero-photo-col {
        display: flex;
        justify-content: center;
    }
    .photo-wrapper {
        width: 80px;
        height: 80px;
    }
    .product-title {
        font-size: 1.1rem;
        text-align: center;
    }
    .hero-badges-row {
        justify-content: center;
    }
    .product-meta-grid {
        justify-content: center;
    }
    .insight-grid-container {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .insight-card {
        padding: 10px;
        gap: 8px;
    }
    .insight-icon {
        width: 34px;
        height: 34px;
        font-size: 15px;
    }
    .insight-value {
        font-size: 12px;
    }
    .product-bottom-desktop-actions {
        display: none;
    }
    .btn-action-edit {
        padding: 6px 10px;
        font-size: 12px;
    }
    .btn-action-opname {
        padding: 6px 10px;
        font-size: 12px;
    }
}
</style>

<!-- SCRIPTS -->
<script>
const currentProductId = <?= (int)$product['id'] ?>;
const allSalesReps = <?= json_encode($salesReps) ?>;
const suppliersData = [
    <?php foreach ($suppliers as $sup): ?>
        { value: "<?= $sup['id'] ?>", label: <?= json_encode($sup['name']) ?> },
    <?php endforeach; ?>
];

let supplierSB;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', async () => {
    // 1. Check if server found product, or if we need offline DB fallback
    const serverFound = document.getElementById('serverProductFound').value === '1';
    if (!serverFound) {
        await attemptOfflineProductHydration(currentProductId);
    }

    // 2. Initialize Supplier SearchBox
    if (document.getElementById('supplierSearchBox')) {
        supplierSB = new SearchBox(document.getElementById('supplierSearchBox'), {
            options: suppliersData,
            placeholder: '-- Pilih Supplier --',
            icon: 'bi-truck',
            name: 'supplier_id',
            onChange: (val) => {
                updateSalesRepOptions(val);
            }
        });
        if (suppliersData.length > 0) {
            supplierSB.select(suppliersData[0].value, suppliersData[0].label);
            updateSalesRepOptions(suppliersData[0].value);
        }
    }

    // 3. Initialize character counter for label
    const labelInp = document.getElementById('inputShortLabel');
    if (labelInp) {
        labelInp.addEventListener('input', () => {
            document.getElementById('labelCharCount').textContent = labelInp.value.length;
        });
        document.getElementById('labelCharCount').textContent = labelInp.value.length;
    }

    // 4. Initialize supplier invoice alias list
    const initialNames = <?= json_encode($product['supplier_invoice_name'] ?? '') ?>;
    showInitInvoiceNameList(initialNames);
});

// CLIENT-SIDE OFFLINE HYDRATION
async function attemptOfflineProductHydration(productId) {
    const notFoundSubtext = document.getElementById('notFoundSubtext');
    let localProduct = null;

    try {
        if (typeof OfflineDB !== 'undefined') {
            localProduct = await OfflineDB.getProductById(productId);
            if (!localProduct) {
                // Try finding by barcode or code
                const all = await OfflineDB.getAllProducts();
                localProduct = all.find(p => String(p.id) === String(productId) || String(p.code) === String(productId) || (p.packagings && p.packagings.some(pkg => String(pkg.barcode) === String(productId))));
            }
        }
    } catch(e) {
        console.warn('OfflineDB lookup error:', e);
    }

    if (localProduct) {
        // Hydrate DOM with local product details!
        document.getElementById('productNotFoundState').style.display = 'none';
        document.getElementById('productMainContent').style.display = 'block';

        // Set titles
        document.getElementById('displayFullName').textContent = localProduct.full_name || ('Produk #' + localProduct.id);
        document.title = (localProduct.short_label || localProduct.full_name) + ' - AlfarezMart';

        if (localProduct.brand_name) {
            let bTag = document.getElementById('displayBrandTag');
            if (!bTag) {
                bTag = document.createElement('span');
                bTag.className = 'badge-tag brand-tag';
                bTag.id = 'displayBrandTag';
                document.querySelector('.hero-badges-row').appendChild(bTag);
            }
            bTag.innerHTML = `<i class="bi bi-bookmark-star"></i> ${localProduct.brand_name}`;
        }
        if (localProduct.category_name) {
            let cTag = document.getElementById('displayCategoryTag');
            if (!cTag) {
                cTag = document.createElement('span');
                cTag.className = 'badge-tag category-tag';
                cTag.id = 'displayCategoryTag';
                document.querySelector('.hero-badges-row').appendChild(cTag);
            }
            cTag.innerHTML = `<i class="bi bi-grid"></i> ${localProduct.category_name}`;
        }

        if (localProduct.short_label) {
            document.getElementById('displayShortLabel').textContent = localProduct.short_label;
            const inputLabel = document.getElementById('inputShortLabel');
            if (inputLabel) {
                inputLabel.value = localProduct.short_label;
                document.getElementById('labelCharCount').textContent = localProduct.short_label.length;
            }
        }

        const stock = parseFloat(localProduct.current_qty_base) || 0;
        document.getElementById('currentStockDisplay').textContent = stock;
        document.getElementById('statStockVal').textContent = stock + ' ' + (localProduct.packagings?.[0]?.unit_name || 'Pcs');

        // Render packagings if present
        if (localProduct.packagings && localProduct.packagings.length > 0) {
            const emptyNotice = document.getElementById('emptyPackagingNotice');
            if (emptyNotice) emptyNotice.remove();
            
            const pkgContainer = document.getElementById('packagingListContainer');
            pkgContainer.innerHTML = '';
            
            const basePkg = localProduct.packagings[0];
            const baseBuy = parseFloat(basePkg.buy_price) || 0;
            const baseRetail = parseFloat(basePkg.sell_price_retail) || 0;
            const baseWholesale = parseFloat(basePkg.sell_price_wholesale) || 0;
            
            document.getElementById('statAssetVal').textContent = 'Rp ' + (stock * baseBuy).toLocaleString('id-ID');
            document.getElementById('statMarginRetail').textContent = '+Rp ' + (baseRetail - baseBuy).toLocaleString('id-ID');
            document.getElementById('statMarginWholesale').textContent = baseWholesale > 0 ? '+Rp ' + (baseWholesale - baseBuy).toLocaleString('id-ID') : '-';

            localProduct.packagings.forEach((p, idx) => {
                const buyP = parseFloat(p.buy_price) || 0;
                const sellR = parseFloat(p.sell_price_retail) || 0;
                const sellW = parseFloat(p.sell_price_wholesale) || 0;
                const mR = sellR - buyP;
                const mRPct = buyP > 0 ? ((mR / buyP) * 100).toFixed(1) : 0;
                const mW = sellW > 0 ? (sellW - buyP) : 0;
                const mWPct = (buyP > 0 && sellW > 0) ? ((mW / buyP) * 100).toFixed(1) : 0;

                const cardHtml = `
                    <div class="packaging-card ${idx === 0 ? 'base-packaging' : ''}">
                        <div class="pkg-card-header">
                            <div class="pkg-level-badge">
                                Level ${p.level}
                                ${idx === 0 ? '<span class="pkg-base-pill">Satuan Dasar</span>' : ''}
                            </div>
                            <div class="pkg-unit-name">1 ${p.unit_name}</div>
                            <div class="pkg-ratio-text">
                                ${idx === 0 ? 'Unit terkecil (Base)' : `Isi: <strong>${p.contained_qty} ${localProduct.packagings[idx-1]?.unit_name || ''}</strong> (Total: ${p.base_qty} ${basePkg.unit_name})`}
                            </div>
                        </div>
                        <div class="pkg-barcode-row">
                            ${p.barcode ? `
                                <div class="barcode-badge"><i class="bi bi-upc"></i> <span class="barcode-num">${p.barcode}</span></div>
                                <button type="button" class="btn-barcode-print" onclick="printBarcodeShow('${p.barcode}', '${localProduct.short_label || localProduct.full_name}', '${p.unit_name}')"><i class="bi bi-printer"></i> Cetak</button>
                            ` : `
                                <button type="button" class="btn-barcode-gen" onclick="generateAndPrintBarcodeShow(${p.id}, '${localProduct.short_label || localProduct.full_name}', '${p.unit_name}', ${buyP}, ${sellR}, ${sellW})"><i class="bi bi-magic"></i> Generate & Cetak Barcode</button>
                            `}
                        </div>
                        <div class="pkg-price-matrix">
                            <div class="price-box price-buy">
                                <span class="price-box-label">Harga Modal (Beli)</span>
                                <strong class="price-box-val">Rp ${buyP.toLocaleString('id-ID')}</strong>
                            </div>
                            <div class="price-box price-retail">
                                <div class="price-box-top">
                                    <span class="price-box-label">Harga Ecer</span>
                                    <span class="margin-pill margin-green">+Rp ${mR.toLocaleString('id-ID')} (${mRPct}%)</span>
                                </div>
                                <strong class="price-box-val text-success">Rp ${sellR.toLocaleString('id-ID')}</strong>
                            </div>
                            <div class="price-box price-wholesale">
                                <div class="price-box-top">
                                    <span class="price-box-label">Harga Grosir</span>
                                    ${sellW > 0 ? `<span class="margin-pill margin-amber">+Rp ${mW.toLocaleString('id-ID')} (${mWPct}%)</span>` : ''}
                                </div>
                                <strong class="price-box-val ${sellW > 0 ? 'text-warning' : 'text-muted'}">
                                    ${sellW > 0 ? 'Rp ' + sellW.toLocaleString('id-ID') : 'Belum diatur'}
                                </strong>
                            </div>
                        </div>
                    </div>
                `;
                pkgContainer.insertAdjacentHTML('beforeend', cardHtml);
            });
            document.getElementById('pkgCountBadge').textContent = localProduct.packagings.length + ' Level Terdaftar';
        }
    } else {
        if (notFoundSubtext) {
            notFoundSubtext.innerHTML = `Produk dengan ID #${productId} tidak ditemukan di database server maupun offline.`;
        }
    }
}

// Supplier & Sales Representative Handlers
function updateSalesRepOptions(supplierId) {
    const container = document.getElementById('salesRepContainer');
    const multiContainer = document.getElementById('multipleSalesReps');
    const singleContainer = document.getElementById('singleSalesRep');
    const waBtn = document.getElementById('waContactBtn');
    const noMsg = document.getElementById('noSalesMsg');
    
    if (!supplierId) {
        if (container) container.style.display = 'none';
        return;
    }
    
    if (container) container.style.display = 'block';
    const reps = allSalesReps.filter(sr => sr.supplier_id == supplierId);
    
    if (reps.length === 0) {
        if (multiContainer) multiContainer.style.display = 'none';
        if (singleContainer) singleContainer.style.display = 'none';
        if (waBtn) waBtn.style.display = 'none';
        if (noMsg) noMsg.style.display = 'block';
    } else if (reps.length === 1) {
        if (multiContainer) multiContainer.style.display = 'none';
        if (noMsg) noMsg.style.display = 'none';
        if (waBtn) waBtn.style.display = 'flex';
        
        if (singleContainer) {
            singleContainer.style.display = 'flex';
            document.getElementById('singleSalesName').textContent = reps[0].name;
            document.getElementById('singleSalesPhone').textContent = reps[0].phone || '-';
        }
        updateWhatsAppUrl(reps[0]);
    } else {
        if (singleContainer) singleContainer.style.display = 'none';
        if (noMsg) noMsg.style.display = 'none';
        if (waBtn) waBtn.style.display = 'flex';
        if (multiContainer) multiContainer.style.display = 'flex';
        
        const sbOptions = reps.map((rep, index) => ({
            value: String(index),
            label: rep.name + (rep.phone ? ' (' + rep.phone + ')' : '')
        }));

        if (typeof salesRepSB === 'undefined' || !salesRepSB) {
            window.salesRepSB = new SearchBox(document.getElementById('salesRepSearchBox'), {
                options: sbOptions,
                placeholder: '-- Pilih Sales Rep --',
                icon: 'bi-person',
                name: 'sales_rep_index',
                onChange: (val) => {
                    if (val !== "" && reps[val]) {
                        updateWhatsAppUrl(reps[val]);
                    }
                }
            });
        } else {
            window.salesRepSB.setOptions(sbOptions);
        }
        
        window.salesRepSB.select("0", sbOptions[0].label);
        updateWhatsAppUrl(reps[0]);
    }
}

function updateWhatsAppUrl(rep) {
    const waBtn = document.getElementById('waContactBtn');
    if (!waBtn) return;
    if (rep && rep.phone) {
        let phone = rep.phone.replace(/\D/g, '');
        if (phone.startsWith('0')) {
            phone = '62' + phone.substring(1);
        }
        waBtn.href = `https://wa.me/${phone}`;
        waBtn.style.opacity = '1';
        waBtn.style.pointerEvents = 'auto';
        document.getElementById('waBtnText').textContent = 'Hubungi ' + rep.name + ' (WhatsApp)';
    } else {
        waBtn.href = '#';
        waBtn.style.opacity = '0.5';
        waBtn.style.pointerEvents = 'none';
        document.getElementById('waBtnText').textContent = 'Nomor WhatsApp Tidak Tersedia';
    }
}

// Thermal Print Label Functions
async function saveProductLabel(id) {
    const shortLabel = document.getElementById('inputShortLabel')?.value?.trim();
    if (!shortLabel) {
        showToast('Label tidak boleh kosong', 'warning');
        return;
    }
    try {
        const csrfToken = document.getElementById('csrfToken')?.value || '';
        const res = await api(`${BASE_URL}api/products/${id}/label`, 'POST', {
            csrf_token: csrfToken,
            short_label: shortLabel,
            invoice_name: shortLabel,
        });
        if (res.success) {
            document.getElementById('displayShortLabel').textContent = res.short_label;
            showToast(res.message || 'Label berhasil disimpan', 'success');
        }
    } catch (e) {}
}

async function openDistributeLabelModal(id) {
    const labelBase = document.getElementById('inputShortLabel')?.value?.trim();
    if (!labelBase) {
        showToast('Isi label dasar terlebih dahulu', 'warning');
        return;
    }

    let siblingsHtml = '<p style="color:var(--text-muted);font-size:12px;">Memuat daftar varian...</p>';

    const loadVariants = async () => {
        try {
            const data = await api(`${BASE_URL}api/products/${id}/label-variants`);
            const list = document.getElementById('variantLabelList');
            if (!list) return;
            if (!data.siblings || data.siblings.length <= 1) {
                list.innerHTML = '<p style="font-size:12px;color:var(--warning);">Tidak ada produk varian lain dengan jenis yang sama.</p>';
                return;
            }
            list.innerHTML = data.siblings.map(s => {
                let preview = labelBase;
                if (s.variant) preview += ' ' + s.variant;
                if (s.weight_value && s.weight_unit) preview += ' ' + s.weight_value + s.weight_unit;
                if (preview.length > 35) preview = preview.substring(0, 32) + '...';
                const isCurrent = s.id == id;
                return `<div style="padding:8px 10px;background:var(--surface-2);border-radius:6px;margin-bottom:6px;font-size:12px;${isCurrent ? 'border-left:3px solid var(--primary);' : ''}">
                    <strong>${(s.variant || s.full_name).replace(/</g, '&lt;')}</strong>
                    <div style="color:var(--info);font-size:11px;margin-top:2px;">→ ${preview.replace(/</g, '&lt;')}</div>
                </div>`;
            }).join('');
        } catch (e) {
            const list = document.getElementById('variantLabelList');
            if (list) list.innerHTML = '<p style="color:var(--danger);font-size:12px;">Gagal memuat daftar varian</p>';
        }
    };

    AppModal.show({
        title: 'Terapkan Label ke Varian',
        subtitle: 'Produk dengan brand & jenis produk yang sama',
        icon: 'bi-share',
        iconColor: 'var(--info-bg)',
        iconAccent: 'var(--info)',
        bodyHTML: `
            <p style="font-size:var(--font-size-sm);color:var(--text-secondary);margin-bottom:12px;">
                Label dasar: <strong>${labelBase.replace(/</g, '&lt;')}</strong><br>
                <span style="font-size:11px;color:var(--text-muted);">Setiap varian akan otomatis disesuaikan (dasar + varian + berat).</span>
            </p>
            <div id="variantLabelList">${siblingsHtml}</div>
        `,
        submitText: 'Terapkan ke Semua Varian',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const csrfToken = document.getElementById('csrfToken')?.value || '';
                const res = await api(`${BASE_URL}api/products/${id}/label/distribute`, 'POST', {
                    csrf_token: csrfToken,
                    label_base: labelBase,
                });
                if (res.success) {
                    showToast(res.message, 'success');
                    return true;
                }
            } catch (e) {}
            return false;
        },
    });
    setTimeout(loadVariants, 100);
}

// Barcode Print & Generate
function printBarcodeShow(code, title, unit) {
    BarcodeUtil.print({ code, title, subtitle: unit ? `1 ${unit}` : '' });
}

async function generateAndPrintBarcodeShow(packagingId, title, unit, buyPrice, retailPrice, wholesalePrice) {
    try {
        const code = await BarcodeUtil.generate();
        const csrfToken = document.getElementById('csrfToken')?.value || '';
        const res = await api(`${BASE_URL}api/products/packaging/${packagingId}`, 'POST', {
            csrf_token: csrfToken,
            barcode: code,
            buy_price: buyPrice,
            sell_price_retail: retailPrice,
            sell_price_wholesale: wholesalePrice,
        });
        if (res.success) {
            BarcodeUtil.print({ code, title, subtitle: unit ? `1 ${unit}` : '' });
            showToast('Barcode berhasil digenerate & dicetak!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (e) {
        showToast(e.message || 'Gagal generate barcode', 'error');
    }
}

// Photo Handlers
function choosePhotoMethod() {
    const hasPhoto = !!document.getElementById('productHeroImg');
    const deleteBtnHtml = hasPhoto ? `
        <button type="button" class="btn-outline-custom text-danger" style="padding:12px;font-size:14px;display:flex;align-items:center;justify-content:center;gap:8px;border-color:var(--danger);" onclick="deleteProductPhoto()">
            <i class="bi bi-trash"></i> Hapus Foto
        </button>
    ` : '';

    AppModal.show({
        title: 'Pilih Foto Produk',
        hideFooter: true,
        bodyHTML: `
            <div style="display:flex;flex-direction:column;gap:10px;padding:6px 0;">
                <button type="button" class="btn-primary-custom" style="padding:12px;font-size:14px;display:flex;align-items:center;justify-content:center;gap:8px;" onclick="AppModal.close(); document.getElementById('productPhotoInputCamera').click()">
                    <i class="bi bi-camera"></i> Buka Kamera
                </button>
                <button type="button" class="btn-outline-custom" style="padding:12px;font-size:14px;display:flex;align-items:center;justify-content:center;gap:8px;" onclick="AppModal.close(); document.getElementById('productPhotoInputGallery').click()">
                    <i class="bi bi-image"></i> Pilih dari Galeri
                </button>
                ${deleteBtnHtml}
            </div>
        `
    });
}

function compressImage(file, maxSize = 1000) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onerror = () => reject(new Error('Gagal membaca file gambar'));
        reader.onload = function(e) {
            const img = new Image();
            img.onerror = () => reject(new Error('Gagal memuat format gambar'));
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                if (width > height) {
                    if (width > maxSize) { height = Math.round(height * (maxSize / width)); width = maxSize; }
                } else {
                    if (height > maxSize) { width = Math.round(width * (maxSize / height)); height = maxSize; }
                }
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                // Ensure transparent background is preserved
                ctx.clearRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);

                // Preserve alpha transparency for PNG, WebP, GIF, SVG
                const isTransparent = file.type === 'image/png' || 
                                      file.type === 'image/webp' || 
                                      file.type === 'image/gif' || 
                                      file.type === 'image/svg+xml' || 
                                      /\.(png|webp|gif|svg)$/i.test(file.name || '');

                const outputType = isTransparent ? 'image/png' : 'image/jpeg';
                resolve(canvas.toDataURL(outputType, 0.92));
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

async function handleProductPhoto(event) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;

    showToast('Memproses & mengunggah foto...', 'info');

    try {
        const base64DataUrl = await compressImage(file, 1000);
        const csrfToken = document.getElementById('csrfToken')?.value || '';
        const res = await api(`${BASE_URL}api/products/${currentProductId}/photo`, 'POST', {
            csrf_token: csrfToken,
            photo_base64: base64DataUrl
        });
        if (res && res.success) {
            showToast('Foto berhasil diperbarui', 'success');
            setTimeout(() => window.location.reload(), 600);
        } else {
            showToast(res?.error || 'Gagal mengunggah foto', 'error');
        }
    } catch (err) {
        console.error('Upload photo error:', err);
        showToast(err.message || 'Terjadi kesalahan saat memproses gambar', 'error');
    } finally {
        event.target.value = '';
    }
}

async function deleteProductPhoto() {
    AppModal.close();
    const confirmed = await AppModal.show({
        title: 'Hapus Foto Produk',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: '<p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.5;">Yakin ingin menghapus foto produk ini?</p>',
        submitText: 'Ya, Hapus Foto',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const csrfToken = document.getElementById('csrfToken')?.value || '';
                const res = await api(`${BASE_URL}api/products/${currentProductId}/photo`, 'POST', {
                    csrf_token: csrfToken,
                    delete_photo: 1
                });
                if (res && res.success) {
                    showToast('Foto berhasil dihapus', 'success');
                    setTimeout(() => window.location.reload(), 600);
                    return true;
                }
            } catch (err) {
                showToast(err.message || 'Gagal menghapus foto', 'error');
            }
            return false;
        }
    });
}

// Opname Modal (Stock Physical Update)
function openUpdateStockModal() {
    const packagings = <?= json_encode($packagings) ?>;
    if (!packagings || packagings.length === 0) {
        showToast('Data kemasan tidak tersedia untuk opname', 'warning');
        return;
    }

    const reversedPackagings = [...packagings].reverse();
    let inputsHtml = reversedPackagings.map(p => `
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;background:var(--surface-2);padding:10px 12px;border-radius:var(--radius-md);border:1px solid var(--border-color);">
            <div style="flex:1;">
                <label style="font-size:13px;font-weight:700;color:var(--text-primary);display:block;">1 ${p.unit_name}</label>
                <div style="font-size:11px;color:var(--text-muted);">Isi: ${p.base_qty} ${packagings[0].unit_name}</div>
            </div>
            <input type="number" id="stock_qty_${p.level}" class="form-control-dark" style="width:100px;text-align:center;font-size:15px;font-weight:700;" value="0" min="0" oninput="calculateTotalStockPreview()">
        </div>
    `).join('');

    AppModal.show({
        title: 'Sesuaikan Stok Fisik (Opname)',
        subtitle: 'Masukkan jumlah barang per tingkat kemasan',
        icon: 'bi-box-seam',
        iconColor: 'var(--success-bg)',
        iconAccent: 'var(--success)',
        bodyHTML: `
            <div style="margin-bottom:14px;">
                ${inputsHtml}
            </div>
            <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);padding:12px;border-radius:var(--radius-md);display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:12px;color:var(--text-muted);font-weight:600;">Total Satuan Terkecil:</span>
                <span id="previewTotalStock" style="font-size:18px;font-weight:800;color:var(--success);">0</span>
            </div>
            <div style="font-size:11px;color:var(--warning);margin-top:10px;">
                <i class="bi bi-exclamation-triangle"></i> Stok saat ini akan digantikan dengan nilai total opname ini.
            </div>
        `,
        submitText: 'Simpan Penyesuaian Stok',
        onSubmit: async () => {
            let totalBaseQty = 0;
            packagings.forEach(p => {
                const el = document.getElementById(`stock_qty_${p.level}`);
                if (el) {
                    const qty = parseFloat(el.value) || 0;
                    totalBaseQty += qty * parseFloat(p.base_qty);
                }
            });

            try {
                const csrfToken = document.getElementById('csrfToken').value;
                const res = await api(`<?= BASE_URL ?>api/products/${currentProductId}/stock`, 'POST', {
                    csrf_token: csrfToken,
                    total_qty: totalBaseQty,
                    notes: 'Stock Opname (Penyesuaian Manual)'
                });
                
                if (res.success) {
                    document.getElementById('currentStockDisplay').textContent = totalBaseQty;
                    document.getElementById('statStockVal').textContent = totalBaseQty + ' <?= htmlspecialchars($baseUnitName) ?>';
                    showToast('Stok berhasil disesuaikan', 'success');
                    return true;
                }
            } catch (e) {}
            return false;
        }
    });
}

function calculateTotalStockPreview() {
    const packagings = <?= json_encode($packagings) ?>;
    let total = 0;
    packagings.forEach(p => {
        const el = document.getElementById(`stock_qty_${p.level}`);
        if (el) {
            total += (parseFloat(el.value) || 0) * parseFloat(p.base_qty);
        }
    });
    const preview = document.getElementById('previewTotalStock');
    if (preview) preview.textContent = total;
}

// Supplier Info Aliases
function showInitInvoiceNameList(namesStr) {
    const list = document.getElementById('showInvoiceNameList');
    if (!list) return;
    list.innerHTML = '';
    const names = (namesStr || '').split(/[;\n]/).map(n => n.trim()).filter(n => n);
    names.forEach(n => showAddInvoiceNameItem(n));
}

function showAddInvoiceName(val) {
    showAddInvoiceNameItem(val || '');
    const list = document.getElementById('showInvoiceNameList');
    if (list) {
        const inputs = list.querySelectorAll('.show-invoice-name-item');
        if (inputs.length > 0) inputs[inputs.length - 1].focus();
    }
}

function showAddInvoiceNameItem(val) {
    const list = document.getElementById('showInvoiceNameList');
    if (!list) return;
    const div = document.createElement('div');
    div.style.display = 'flex';
    div.style.gap = '6px';
    div.innerHTML = `
        <input type="text"
               class="form-control-dark show-invoice-name-item"
               placeholder="Cth: CIMORY UHT 250ML"
               style="flex:1;"
               value="${val ? val.replace(/"/g, '&quot;') : ''}">
        <button type="button"
                onclick="this.parentElement.remove()"
                style="background:var(--danger-bg);color:var(--danger);border:none;border-radius:4px;padding:0 12px;cursor:pointer;">
            <i class="bi bi-x-lg"></i>
        </button>
    `;
    list.appendChild(div);
}

function showCollectInvoiceNames() {
    const inputs = document.querySelectorAll('.show-invoice-name-item');
    const names = Array.from(inputs).map(inp => inp.value.trim()).filter(v => v);
    return names.join(';');
}

async function saveSupplierInfo(id) {
    const btn = document.getElementById('btnSaveSupplierInfo');
    const prevText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
    btn.disabled = true;

    const supplierInvName = showCollectInvoiceNames();
    const shortLabel = document.getElementById('inputShortLabel')?.value?.trim()
                     || <?= json_encode($product['short_label'] ?? $product['full_name']) ?>;

    try {
        const csrfToken = document.getElementById('csrfToken')?.value || '';
        const res = await api(`${BASE_URL}api/products/${id}/label`, 'POST', {
            csrf_token:             csrfToken,
            short_label:            shortLabel,
            invoice_name:           shortLabel,
            supplier_product_code:  document.getElementById('displaySupplierCode')?.textContent || '',
            supplier_invoice_name:  supplierInvName,
        });
        if (res.success) {
            showToast(res.message || 'Alias invoice supplier disimpan', 'success');
        }
    } catch (e) {
    } finally {
        btn.innerHTML = prevText;
        btn.disabled = false;
    }
}

// Delete Product
async function deleteProduct(id, name) {
    const confirmed = await AppModal.show({
        title: 'Hapus Produk',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `<p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.6;">Yakin ingin menghapus produk <strong>${name}</strong>?<br>Data stok dan kemasan terkait juga akan dihapus.</p>`,
        submitText: 'Ya, Hapus',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const csrfToken = document.getElementById('csrfToken')?.value || '';
                const res = await api(`<?= BASE_URL ?>api/products/${id}/delete`, 'POST', { csrf_token: csrfToken });
                if (res.success) {
                    showToast(res.message || 'Produk berhasil dihapus', 'success');
                    setTimeout(() => window.location.href = '<?= BASE_URL ?>products', 1000);
                    return true;
                }
            } catch(e) {}
            return false;
        }
    });
}

// Quick availability toggle
async function quickToggleAvailabilityDetail(id) {
    try {
        const res = await api(`${BASE_URL}api/products/${id}/toggle-active`, 'POST', {
            csrf_token: document.getElementById('csrfToken')?.value || ''
        });
        if (res.success) {
            showToast(res.message || 'Status produk diperbarui', 'success');
            setTimeout(() => window.location.reload(), 600);
        }
    } catch(e) {}
}

// Menu Dropdown Handlers
function toggleProductMenu(e) {
    e.stopPropagation();
    const menu = document.getElementById('productDropdownMenu');
    if (!menu) return;
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
function hideProductMenu() {
    const menu = document.getElementById('productDropdownMenu');
    if (menu) menu.style.display = 'none';
}
document.addEventListener('click', (e) => {
    if (!e.target.closest('#productDropdownMenu') && !e.target.closest('.btn-action-more')) {
        hideProductMenu();
    }
});
</script>
