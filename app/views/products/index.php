<!-- Products List View -->
<?php
/**
 * @var array $products
 * @var array $categories
 * @var string $search
 * @var int|null $selectedCategory
 * @var float|null $minPrice
 * @var float|null $maxPrice
 */
$hasPriceFilter = ($minPrice !== null || $maxPrice !== null);
// Build URL keeping current filters except search (used for clear-search link)
$clearSearchUrl = BASE_URL . 'products';
$clearSearchParts = [];
if ($selectedCategory) $clearSearchParts[] = 'category=' . (int)$selectedCategory;
if ($minPrice !== null) $clearSearchParts[] = 'min_price=' . urlencode($minPrice);
if ($maxPrice !== null) $clearSearchParts[] = 'max_price=' . urlencode($maxPrice);
if ($clearSearchParts) $clearSearchUrl .= '?' . implode('&', $clearSearchParts);
// Build URL keeping current filters except price (used for clear-price link)
$clearPriceUrl = BASE_URL . 'products';
$clearPriceParts = [];
if ($selectedCategory) $clearPriceParts[] = 'category=' . (int)$selectedCategory;
if (!empty($search)) $clearPriceParts[] = 'q=' . urlencode($search);
if ($clearPriceParts) $clearPriceUrl .= '?' . implode('&', $clearPriceParts);
?>
<div class="page-section">
    <div style="display:flex;gap:8px;margin-bottom:10px;align-items:center;min-width:0;">
        <form method="GET" action="<?= BASE_URL ?>products" style="flex:1;min-width:0;" id="productSearchForm">
            <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory ?? '') ?>">
            <input type="hidden" name="min_price" value="<?= htmlspecialchars($minPrice ?? '') ?>">
            <input type="hidden" name="max_price" value="<?= htmlspecialchars($maxPrice ?? '') ?>">
            <div class="search-input-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" name="q" id="productSearchInput" class="no-track" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Cari produk..." autocomplete="off" oninput="filterProductsClientSide(this.value)">
                <?php if (!empty($search)): ?>
                    <a href="<?= $clearSearchUrl ?>" style="color:var(--text-muted);text-decoration:none;flex-shrink:0;"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
        <button type="button" onclick="scanProductBarcode()" title="Scan Barcode"
                style="background:var(--primary);color:white;border:none;border-radius:var(--radius-lg);padding:10px 12px;cursor:pointer;font-size:1rem;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-upc-scan"></i>
        </button>
    </div>
    <div style="margin-bottom:8px;">
        <div id="categoryFilterSearchBox"></div>
    </div>
    <div style="margin-bottom:12px;">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:nowrap;">
            <span style="font-size:11px;color:var(--text-muted);white-space:nowrap;flex-shrink:0;">💰</span>
            <input type="number" id="filterMinPrice" placeholder="Harga min" min="0" step="1000"
                   value="<?= htmlspecialchars($minPrice !== null ? (int)$minPrice : '') ?>"
                   style="flex:1;min-width:0;background:var(--bg-card);color:var(--text-primary);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:8px 10px;font-size:var(--font-size-xs);outline:none;appearance:textfield;-moz-appearance:textfield;"
                   onkeydown="if(event.key==='Enter') applyPriceFilter()">
            <span style="color:var(--text-muted);flex-shrink:0;font-size:12px;">—</span>
            <input type="number" id="filterMaxPrice" placeholder="Harga max" min="0" step="1000"
                   value="<?= htmlspecialchars($maxPrice !== null ? (int)$maxPrice : '') ?>"
                   style="flex:1;min-width:0;background:var(--bg-card);color:var(--text-primary);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:8px 10px;font-size:var(--font-size-xs);outline:none;appearance:textfield;-moz-appearance:textfield;"
                   onkeydown="if(event.key==='Enter') applyPriceFilter()">
            <button type="button" onclick="applyPriceFilter()"
                    title="Terapkan filter harga"
                    style="background:var(--primary);color:white;border:none;border-radius:var(--radius-md);padding:8px 11px;cursor:pointer;font-size:13px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-funnel-fill"></i>
            </button>
            <?php if ($hasPriceFilter): ?>
            <a href="<?= $clearPriceUrl ?>" title="Reset filter harga"
               style="color:var(--text-muted);text-decoration:none;flex-shrink:0;font-size:16px;display:flex;align-items:center;">
                <i class="bi bi-x-circle"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:8px;min-width:0;">
        <span style="font-size:var(--font-size-xs);color:var(--text-muted);display:flex;align-items:center;gap:8px;">
            <label id="selectAllContainer" style="display:none;align-items:center;gap:4px;cursor:pointer;margin:0;">
                <input type="checkbox" id="selectAllProducts" style="width:16px;height:16px;accent-color:var(--primary);">
                <span>Pilih Semua</span>
            </label>
            <span id="totalProductsText"><?= (int)($products['total'] ?? 0) ?> produk</span>
        </span>
        <div style="display:flex;align-items:center;gap:8px;">
            <?php
            $userLevel = $_SESSION['user_level'] ?? '';
            $isStaff = $userLevel === 'staff';
            $isSuperadmin = $userLevel === 'superadmin';
            if (!$isStaff):
            ?>
            <button type="button" id="btnBulkDelete" onclick="bulkDeleteSelected()" style="display:none;background:var(--danger);color:white;border:none;border-radius:var(--radius-sm);padding:6px 12px;font-size:var(--font-size-xs);cursor:pointer;align-items:center;gap:4px;flex-shrink:0;">
                <i class="bi bi-trash"></i> Hapus (<span id="selectedCount">0</span>)
            </button>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>products/create" id="btnAddProduct" class="btn-primary-custom" style="padding:6px 12px;font-size:var(--font-size-xs);text-decoration:none;color:white;flex-shrink:0;">
                <i class="bi bi-plus"></i> Tambah
            </a>
        </div>
    </div>

    <div id="productListContainer">
    <?php if (empty($products['data'])): ?>
        <div class="empty-state">
            <i class="bi bi-box-seam"></i>
            <?php if (!empty($search)): ?>
                <h3>Produk Tidak Ditemukan</h3>
                <p>Tidak ada produk yang cocok dengan pencarian "<?= htmlspecialchars($search) ?>".</p>
            <?php else: ?>
                <h3>Belum Ada Produk</h3>
                <p>Tambahkan produk pertama Anda.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($products['data'] as $p): ?>
            <?php $pIsAvail = !isset($p['is_available']) || $p['is_available'] == 1; ?>
            <div class="product-card" data-id="<?= (int)$p['id'] ?>" data-available="<?= $pIsAvail ? '1' : '0' ?>" style="position:relative;display:block;<?= !$pIsAvail ? 'opacity:0.7;' : '' ?>">
                <?php if (!$isStaff): ?>
                <input type="checkbox" class="product-checkbox" value="<?= (int)$p['id'] ?>" style="display:none;position:absolute;top:16px;left:16px;width:20px;height:20px;accent-color:var(--primary);z-index:2;">
                <?php endif; ?>
                <a href="<?= BASE_URL ?>products/<?= (int)$p['id'] ?>" class="product-card-link" style="display:flex;text-decoration:none;color:inherit;width:100%;">
                    <?php if (!empty($p['photo'])): ?>
                        <div class="product-icon" style="width:60px; height:60px; border-radius:var(--radius-md); overflow:hidden; display:flex; align-items:center; justify-content:center; background:transparent; flex-shrink:0; margin-right:16px;">
                            <img src="<?= BASE_URL . htmlspecialchars($p['photo']) ?>" style="width:100%; height:100%; object-fit:contain;">
                        </div>
                    <?php else: ?>
                        <div class="product-icon" style="width:60px; height:60px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; background:var(--primary-bg); color:var(--primary); font-size:1.5rem; flex-shrink:0; margin-right:16px;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    <?php endif; ?>
                    <div class="product-info" style="width:calc(100% - 76px);">
                        <div class="product-name" style="padding-right:36px;"><?= htmlspecialchars($p['full_name'] ?? $p['short_label'] ?? '-') ?></div>
                    <div class="product-category">
                        <?= htmlspecialchars($p['brand_name'] ?? '') ?>
                        <?php if (!empty($p['brand_name']) && !empty($p['category_name'])): ?> · <?php endif; ?>
                        <?= htmlspecialchars($p['category_name'] ?? '') ?>
                        <?php if (!empty($p['updated_at'])): ?>
                            <span style="margin-left:4px;opacity:0.6;" title="Terakhir diupdate: <?= htmlspecialchars($p['updated_at']) ?>">· <?= Helper::timeAgo($p['updated_at']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:4px;">
                        <div style="display:flex;flex-direction:column;">
                            <div>
                                <?php if (!empty($p['price_small_retail'])): ?>
                                    <span class="product-price"><?= Helper::rupiah($p['price_small_retail']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($p['price_small_wholesale'])): ?>
                                    <span class="product-price-wholesale" style="margin-left:6px;"><?= Helper::rupiah($p['price_small_wholesale']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($isSuperadmin && !empty($p['packagings']) && isset($p['packagings'][0])): 
                                $basePkg = $p['packagings'][0];
                                $baseMarginAmt = current($p['packagings'])['sell_price_retail'] - current($p['packagings'])['buy_price'];
                                $baseMarginPct = current($p['packagings'])['buy_price'] > 0 ? round(($baseMarginAmt / current($p['packagings'])['buy_price']) * 100, 1) : 0;
                            ?>
                            <div style="font-size:10px; color:var(--text-muted); opacity:0.8; text-shadow:0 1px 1px rgba(0,0,0,0.1); margin-top:2px; text-align:right;">
                                M: <?= Helper::rupiah(current($p['packagings'])['buy_price']) ?> | P: <?= Helper::rupiah($baseMarginAmt) ?> (<?= $baseMarginPct ?>%)
                            </div>
                            <?php endif; ?>
                        </div>
                        <span class="product-stock"><?= (int)($p['current_qty_base'] ?? 0) ?> pcs</span>
                    </div>
                    <?php if (!empty($p['packagings']) && count($p['packagings']) > 1): ?>
                    <div style="margin-top:8px;padding-top:8px;border-top:1px dashed var(--border-color);display:flex;flex-direction:column;gap:4px;">
                        <?php foreach($p['packagings'] as $idx => $pkg): if($idx == 0) continue; 
                            $marginAmt = $pkg['sell_price_retail'] - $pkg['buy_price'];
                            $marginPct = $pkg['buy_price'] > 0 ? round(($marginAmt / $pkg['buy_price']) * 100, 1) : 0;
                        ?>
                        <div style="display:flex;flex-direction:column;font-size:10px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="color:var(--text-muted);font-weight:600;"><i class="bi bi-box2"></i> <?= htmlspecialchars($pkg['unit_name']) ?></span>
                                <div style="text-align:right;">
                                    <span style="color:var(--success);"><?= Helper::rupiah($pkg['sell_price_retail']) ?></span>
                                    <?php if($pkg['sell_price_wholesale'] > 0): ?>
                                    <span style="color:var(--warning);margin-left:4px;">(G: <?= Helper::rupiah($pkg['sell_price_wholesale']) ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($isSuperadmin): ?>
                                <div style="font-size:9px; color:var(--text-muted); opacity:0.8; text-shadow:0 1px 1px rgba(0,0,0,0.1); text-align:right;">
                                    M: <?= Helper::rupiah($pkg['buy_price']) ?> | P: <?= Helper::rupiah($marginAmt) ?> (<?= $marginPct ?>%)
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    </div>
                </a>
                <?php if (!$isStaff): ?>
                <button type="button" class="avail-toggle-btn" title="<?= $pIsAvail ? 'Nonaktifkan produk' : 'Aktifkan produk' ?>" onclick="event.stopPropagation(); quickToggleAvailability(this, <?= (int)$p['id'] ?>, <?= $pIsAvail ? 1 : 0 ?>)" style="position:absolute;top:10px;right:10px;width:34px;height:20px;border-radius:10px;border:none;cursor:pointer;padding:2px;transition:background 0.25s;background:<?= $pIsAvail ? 'var(--success)' : 'var(--surface-2)' ?>;display:flex;align-items:center;z-index:3;">
                    <span style="display:block;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.3);transition:transform 0.25s;transform:translateX(<?= $pIsAvail ? '14px' : '0px' ?>);"></span>
                </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>

    <?php if (!empty($products['data']) && ($products['total_pages'] ?? 1) > 1): ?>
        <?php
            $totalPages = $products['total_pages'];
            $currentPage = $products['page'] ?? 1;
            $qStr = urlencode($search ?? '');
            $catStr = $selectedCategory ? '&category=' . (int)$selectedCategory : '';
            $catStr .= ($minPrice !== null) ? '&min_price=' . urlencode($minPrice) : '';
            $catStr .= ($maxPrice !== null) ? '&max_price=' . urlencode($maxPrice) : '';
            $buildUrl = function($p) use ($qStr, $catStr) {
                return BASE_URL . "products?page={$p}&q={$qStr}{$catStr}";
            };
            // Show max 5 page buttons around current
            $range = 2;
            $start = max(1, $currentPage - $range);
            $end = min($totalPages, $currentPage + $range);
        ?>
        <div class="desktop-pagination-wrapper" style="margin-top:28px;padding-top:16px;border-top:1px solid var(--border-color);display:flex;flex-direction:column;align-items:center;justify-content:center;">
            <div style="display:flex;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= $buildUrl($currentPage - 1) ?>" class="chip" title="Sebelumnya" style="padding:6px 12px;"><i class="bi bi-chevron-left"></i></a>
                <?php else: ?>
                    <span class="chip" style="padding:6px 12px;opacity:0.35;pointer-events:none;"><i class="bi bi-chevron-left"></i></span>
                <?php endif; ?>

                <?php if ($start > 1): ?>
                    <a href="<?= $buildUrl(1) ?>" class="chip">1</a>
                    <?php if ($start > 2): ?><span style="color:var(--text-muted);font-size:12px;padding:0 2px;">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a href="<?= $buildUrl($i) ?>" class="chip <?= $currentPage == $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?><span style="color:var(--text-muted);font-size:12px;padding:0 2px;">…</span><?php endif; ?>
                    <a href="<?= $buildUrl($totalPages) ?>" class="chip"><?= $totalPages ?></a>
                <?php endif; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= $buildUrl($currentPage + 1) ?>" class="chip" title="Selanjutnya" style="padding:6px 12px;"><i class="bi bi-chevron-right"></i></a>
                <?php else: ?>
                    <span class="chip" style="padding:6px 10px;opacity:0.35;pointer-events:none;"><i class="bi bi-chevron-right"></i></span>
                <?php endif; ?>
            </div>
            <div style="text-align:center;margin-top:8px;font-size:0.82rem;color:var(--text-muted);">
                Halaman <?= $currentPage ?> dari <?= $totalPages ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.product-card.select-mode .product-checkbox { display: block !important; }
.product-card.select-mode .product-card-link { margin-left: 28px; }
.product-card.select-mode { padding-left: 8px; }
.product-card.selected { border-color: var(--primary); background: rgba(230,57,70,0.05); }
.product-card.unavailable-product { opacity: 0.65; }
.avail-toggle-btn:active { transform: scale(0.92); }

/* Availability Confirm Modal */
#availConfirmModal {
    position: fixed; inset: 0; z-index: 9999;
    display: none; align-items: flex-end; justify-content: center;
    background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);
    padding: 16px;
}
#availConfirmModal.show { display: flex; }
#availConfirmSheet {
    background: var(--surface-1);
    border-radius: 20px 20px 16px 16px;
    width: 100%; max-width: 480px;
    padding: 28px 24px 24px;
    box-shadow: 0 -8px 40px rgba(0,0,0,0.4);
    animation: slideUpModal 0.28s cubic-bezier(.32,1.1,.5,1) both;
}
@keyframes slideUpModal {
    from { transform: translateY(60px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.avail-modal-icon {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; margin: 0 auto 14px;
}
#availConfirmTitle {
    font-size: 1.05rem; font-weight: 700;
    color: var(--text-primary); text-align: center; margin-bottom: 8px;
}
#availConfirmBody {
    font-size: var(--font-size-sm); color: var(--text-muted);
    text-align: center; line-height: 1.6; margin-bottom: 22px;
}
.avail-modal-btns {
    display: flex; gap: 10px;
}
.avail-modal-btns button {
    flex: 1; padding: 12px; border: none; border-radius: var(--radius-md);
    font-size: var(--font-size-sm); font-weight: 600; cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
}
.avail-modal-btns button:active { transform: scale(0.97); opacity: 0.85; }
#availBtnCancel {
    background: var(--surface-2); color: var(--text-muted);
    border: 1px solid var(--border-color);
}
#availBtnConfirm { color: #fff; }
</style>

<!-- Availability Confirmation Modal -->
<div id="availConfirmModal" onclick="_closeAvailModal()">
    <div id="availConfirmSheet" onclick="event.stopPropagation()">
        <div class="avail-modal-icon" id="availModalIcon"></div>
        <div id="availConfirmTitle">Konfirmasi</div>
        <div id="availConfirmBody"></div>
        <div class="avail-modal-btns">
            <button id="availBtnCancel" onclick="_closeAvailModal()">Batal</button>
            <button id="availBtnConfirm">Konfirmasi</button>
        </div>
    </div>
</div>

<script>
let selectMode = false;
let pressTimer;
const ROLE_IS_STAFF = <?= $isStaff ? 'true' : 'false' ?>;
const ROLE_IS_SUPERADMIN = <?= $isSuperadmin ? 'true' : 'false' ?>;

// Elemen UI global (akan di-set setelah DOM ready)
let _selectAllCheckbox, _btnBulkDelete, _selectedCountSpan, _btnAddProduct, _selectAllContainer, _totalProductsText;

function updateSelectionState() {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const count = checkedBoxes.length;
    const cards = document.querySelectorAll('.product-card');
    if (_selectedCountSpan) _selectedCountSpan.textContent = count;
    
    if (count > 0) {
        if (_btnBulkDelete) _btnBulkDelete.style.display = 'flex';
        if (_btnAddProduct) _btnAddProduct.style.display = 'none';
    } else if (selectMode) {
        if (_btnBulkDelete) _btnBulkDelete.style.display = 'none';
        if (_btnAddProduct) _btnAddProduct.style.display = 'inline-flex';
    }

    if (_selectAllCheckbox) _selectAllCheckbox.checked = count === cards.length && cards.length > 0;
}

function toggleSelectMode(enable) {
    selectMode = enable;
    if (enable) {
        document.querySelectorAll('.product-card').forEach(c => c.classList.add('select-mode'));
        if (_selectAllContainer) _selectAllContainer.style.display = 'flex';
        if (_totalProductsText) _totalProductsText.style.display = 'none';
        if (_btnAddProduct) _btnAddProduct.style.display = 'none';
        if (_btnBulkDelete) _btnBulkDelete.style.display = document.querySelectorAll('.product-checkbox:checked').length > 0 ? 'flex' : 'none';
    } else {
        document.querySelectorAll('.product-card').forEach(c => {
            c.classList.remove('select-mode', 'selected');
            const cb = c.querySelector('.product-checkbox');
            if (cb) cb.checked = false;
        });
        if (_selectAllContainer) _selectAllContainer.style.display = 'none';
        if (_totalProductsText) _totalProductsText.style.display = 'inline-block';
        if (_btnAddProduct) _btnAddProduct.style.display = 'inline-flex';
        if (_btnBulkDelete) _btnBulkDelete.style.display = 'none';
        updateSelectionState();
    }
}

// Fungsi global agar bisa dipanggil ulang setelah doOfflineSearch render card baru
function attachProductCardListeners() {
    if (ROLE_IS_STAFF) return;
    const cards = document.querySelectorAll('.product-card');
    
    cards.forEach(card => {
        const link = card.querySelector('.product-card-link');
        const checkbox = card.querySelector('.product-checkbox');
        if (!link) return; // safety guard

        // Hapus event lama dengan mengganti elemen (clone trick) agar tidak double-bind
        const newLink = link.cloneNode(true);
        link.parentNode.replaceChild(newLink, link);

        // Long press logic (touch)
        newLink.addEventListener('touchstart', (e) => {
            pressTimer = window.setTimeout(() => {
                if (!checkbox) return;
                toggleSelectMode(true);
                checkbox.checked = true;
                card.classList.add('selected');
                updateSelectionState();
                if (window.navigator && window.navigator.vibrate) {
                    window.navigator.vibrate(50);
                }
            }, 600); // 600ms long press
        }, {passive: true});

        newLink.addEventListener('touchend', () => { clearTimeout(pressTimer); });
        newLink.addEventListener('touchmove', () => { clearTimeout(pressTimer); });

        // Long press logic (mouse)
        newLink.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return;
            pressTimer = window.setTimeout(() => {
                if (!checkbox) return;
                toggleSelectMode(true);
                checkbox.checked = true;
                card.classList.add('selected');
                updateSelectionState();
            }, 600);
        });

        newLink.addEventListener('mouseup', () => { clearTimeout(pressTimer); });
        newLink.addEventListener('mouseleave', () => { clearTimeout(pressTimer); });

        // Click intercept when in select mode
        newLink.addEventListener('click', (e) => {
            if (selectMode) {
                e.preventDefault();
                if (!checkbox) return;
                checkbox.checked = !checkbox.checked;
                if (checkbox.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
                updateSelectionState();
                if (document.querySelectorAll('.product-checkbox:checked').length === 0) {
                    toggleSelectMode(false);
                }
            }
        });

        // Direct checkbox click
        if (checkbox) {
            checkbox.addEventListener('change', (e) => {
                if (e.target.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
                updateSelectionState();
                if (document.querySelectorAll('.product-checkbox:checked').length === 0) {
                    toggleSelectMode(false);
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    _selectAllCheckbox = document.getElementById('selectAllProducts');
    _btnBulkDelete = document.getElementById('btnBulkDelete');
    _selectedCountSpan = document.getElementById('selectedCount');
    _btnAddProduct = document.getElementById('btnAddProduct');
    _selectAllContainer = document.getElementById('selectAllContainer');
    _totalProductsText = document.getElementById('totalProductsText');

    // Staff tidak punya hak hapus/edit — skip semua handler seleksi & long-press
    if (ROLE_IS_STAFF) return;

    // Pasang listener ke semua card yang sudah ada (dari server render)
    attachProductCardListeners();

    if (_selectAllCheckbox) {
        _selectAllCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            document.querySelectorAll('.product-card').forEach(card => {
                const cb = card.querySelector('.product-checkbox');
                if (cb) {
                    cb.checked = isChecked;
                    if (isChecked) card.classList.add('selected');
                    else card.classList.remove('selected');
                }
            });
            updateSelectionState();
            if (!isChecked) {
                toggleSelectMode(false);
            }
        });
    }
});


async function bulkDeleteSelected() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (checked.length === 0) return;

    if (!await AppModal.confirm('Hapus Produk Terpilih', `Apakah Anda yakin ingin menghapus ${checked.length} produk yang dipilih? Semua data terkait juga akan terhapus dan tidak bisa dikembalikan.`, 'Ya, Hapus Semua', 'var(--danger)')) {
        return;
    }

    const ids = Array.from(checked).map(cb => cb.value);

    try {
        const res = await fetch(`${BASE_URL}api/products/bulk-delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION["csrf_token"] ?? "" ?>'
            },
            body: JSON.stringify({ ids })
        });
        const data = await res.json();
        
        if (data.error) throw new Error(data.error);
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (e) {
        showToast(e.message || 'Terjadi kesalahan sistem', 'error');
    }
}

function filterByCategory(val) {
    // Clear search input when category changes
    const searchInput = document.getElementById('productSearchInput');
    if (searchInput) searchInput.value = '';
    
    const q = '';  // Always empty search when filtering by category
    const minP = document.getElementById('filterMinPrice')?.value?.trim() || '';
    const maxP = document.getElementById('filterMaxPrice')?.value?.trim() || '';
    const parts = [];
    if (val) parts.push('category=' + encodeURIComponent(val));
    if (q) parts.push('q=' + encodeURIComponent(q));
    if (minP) parts.push('min_price=' + encodeURIComponent(minP));
    if (maxP) parts.push('max_price=' + encodeURIComponent(maxP));
    window.location.href = '<?= BASE_URL ?>products' + (parts.length ? '?' + parts.join('&') : '');
}

// Ensure search input is clear on load if URL has no 'q' (prevents browser autofill/bfcache from repopulating it)
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('q')) {
        const searchInput = document.getElementById('productSearchInput');
        if (searchInput) searchInput.value = '';
    }
});

function applyPriceFilter() {
    const q = document.getElementById('productSearchInput')?.value?.trim() || '';
    const minP = document.getElementById('filterMinPrice')?.value?.trim() || '';
    const maxP = document.getElementById('filterMaxPrice')?.value?.trim() || '';
    const catVal = '<?= htmlspecialchars($selectedCategory ?? '') ?>';
    const parts = [];
    if (catVal) parts.push('category=' + encodeURIComponent(catVal));
    if (q) parts.push('q=' + encodeURIComponent(q));
    if (minP !== '') parts.push('min_price=' + encodeURIComponent(minP));
    if (maxP !== '') parts.push('max_price=' + encodeURIComponent(maxP));
    window.location.href = '<?= BASE_URL ?>products' + (parts.length ? '?' + parts.join('&') : '');
}

function scanProductBarcode() {
    if (typeof BarcodeUtil !== 'undefined' && BarcodeUtil.scanBarcode) {
        const fakeInput = document.createElement('input');
        fakeInput.type = 'text';
        document.body.appendChild(fakeInput);
        BarcodeUtil.scanBarcode(fakeInput, async (code) => {
            document.body.removeChild(fakeInput);
            if (!navigator.onLine && typeof OfflineDB !== 'undefined') {
                const product = await OfflineDB.findByBarcode(code);
                if (product) {
                    window.location.href = `<?= BASE_URL ?>products/${product.id}`;
                    return;
                }
            }
            window.location.href = '<?= BASE_URL ?>products?q=' + encodeURIComponent(code);
        });
    } else {
        const code = prompt('Masukkan kode barcode:');
        if (code) {
            if (!navigator.onLine && typeof OfflineDB !== 'undefined') {
                OfflineDB.findByBarcode(code).then(product => {
                    if (product) window.location.href = `<?= BASE_URL ?>products/${product.id}`;
                    else window.location.href = '<?= BASE_URL ?>products?q=' + encodeURIComponent(code);
                });
            } else {
                window.location.href = '<?= BASE_URL ?>products?q=' + encodeURIComponent(code);
            }
        }
    }
}

// Initialize Category Filter SearchBox
document.addEventListener('DOMContentLoaded', () => {
    const categoriesData = [
        { value: '', label: '📂 Semua Kategori' },
        <?php foreach ($categories as $cat): ?>
            { value: '<?= (int)$cat['id'] ?>', label: <?= json_encode($cat['name']) ?> },
        <?php endforeach; ?>
    ];
    
    new SearchBox(document.getElementById('categoryFilterSearchBox'), {
        options: categoriesData,
        placeholder: '📂 Semua Kategori',
        value: '<?= htmlspecialchars($selectedCategory ?? '') ?>',
        onChange: (val) => filterByCategory(val)
    });
});

// Point 2: Realtime search
(function() {
    const input = document.getElementById('productSearchInput');
    if (!input) return;
    let timer = null;
    let resultsDiv = document.createElement('div');
    resultsDiv.id = 'productLiveResults';
    resultsDiv.style.cssText = 'position:absolute;left:0;right:0;top:100%;z-index:100;display:none;background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-md);max-height:320px;overflow-y:auto;margin-top:4px;box-shadow:0 8px 24px rgba(0,0,0,0.3);';
    const wrapper = input.closest('.search-input-wrapper');
    wrapper.style.position = 'relative';
    wrapper.appendChild(resultsDiv);

    input.addEventListener('input', function() {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 1) { resultsDiv.style.display = 'none'; return; }
        timer = setTimeout(async () => {
            try {
                let items = [];
                if (navigator.onLine) {
                    // Online: prioritaskan data server (akurat & terkini)
                    try {
                        const res = await fetch(`<?= BASE_URL ?>api/products/search?q=${encodeURIComponent(q)}`);
                        items = await res.json();
                    } catch (apiErr) {
                        console.error('API search failed', apiErr);
                    }
                }
                if ((!items || items.length === 0) && typeof OfflineDB !== 'undefined') {
                    // Fallback ke offline jika tidak ada hasil atau tidak online
                    items = await OfflineDB.searchProducts(q);
                }

                if (!items || items.length === 0) {
                    resultsDiv.innerHTML = '<div style="padding:12px;text-align:center;color:var(--text-muted);font-size:var(--font-size-xs);">Tidak ditemukan</div>';
                } else {
                    resultsDiv.innerHTML = items.map(p => {
                        // Use short_label as display name (user preference)
                        const label = (p.short_label || p.full_name || '').replace(/</g,'&lt;');
                        const brand = (p.brand_name || '').replace(/</g,'&lt;');
                        let priceText = '';
                        if (p.packagings && p.packagings.length > 0) {
                            const pkgsHtml = p.packagings.map(pkg => {
                                const price = parseFloat(pkg.sell_price_retail) || 0;
                                const unitDisp = pkg.unit_abbr || pkg.unit_name;
                                return price > 0 ? `<span style="color:var(--primary);font-weight:600;font-size:9px;">Rp${price.toLocaleString('id-ID')}</span><span style="font-size:8px;color:var(--text-muted);margin-left:2px;">/${unitDisp}</span>` : '';
                            }).filter(Boolean).join('<span style="color:var(--border-color);margin:0 4px;font-size:10px;">·</span>');
                            if (pkgsHtml) {
                                priceText = `<div style="margin-top:2px;display:flex;flex-wrap:wrap;gap:2px;align-items:center;">${pkgsHtml}</div>`;
                            }
                        } else if (p.price_small_retail) {
                            const price = parseInt(p.price_small_retail);
                            priceText = `<div style="margin-top:2px;"><span style="color:var(--primary);font-weight:600;font-size:9px;">Rp${price.toLocaleString('id-ID')}</span></div>`;
                        }

                        const imgHtml = (p.photo)
                            ? `<img src="${BASE_URL}${p.photo}" style="width:44px;height:44px;object-fit:contain;border-radius:8px;background:transparent;flex-shrink:0;" loading="lazy">`
                            : `<div style="width:44px;height:44px;border-radius:8px;background:var(--primary-bg);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;"><i class="bi bi-box-seam"></i></div>`;

                        return `<a href="<?= BASE_URL ?>products/${p.id}" style="display:flex;align-items:flex-start;gap:12px;padding:10px 14px;border-bottom:1px solid var(--border-color);text-decoration:none;color:var(--text-primary);font-size:var(--font-size-sm);transition:background 0.15s;" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
                            ${imgHtml}
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;line-height:1.3;">${label}</div>
                                ${brand ? `<div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:1px;">${brand}</div>` : ''}
                                ${priceText}
                            </div>
                        </a>`;
                    }).join('');
                }
                resultsDiv.style.display = 'block';
            } catch(e) { resultsDiv.style.display = 'none'; }
        }, 250);
    });

    input.addEventListener('keypress', e => {
        if (e.key === 'Enter') {
            resultsDiv.style.display = 'none';
            e.preventDefault(); // Prevent default immediately
            if (!navigator.onLine && typeof OfflineDB !== 'undefined') {
                doOfflineSearch(input.value);
            } else {
                document.getElementById('productSearchForm').submit();
            }
        }
    });
    
    // Also intercept the form submit itself (if submitted via other means)
    document.getElementById('productSearchForm').addEventListener('submit', function(e) {
        if (!navigator.onLine && typeof OfflineDB !== 'undefined') {
            e.preventDefault();
            resultsDiv.style.display = 'none';
            doOfflineSearch(input.value);
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#productSearchForm') && !e.target.closest('#productLiveResults'))
            resultsDiv.style.display = 'none';
    });
})();

// OFFLINE SEARCH EXECUTION
async function doOfflineSearch(query) {
    const container = document.getElementById('productListContainer');
    if (!container) return;

    container.innerHTML = '<div style="text-align:center;padding:40px;"><div class="spinner-border text-primary"></div><div style="margin-top:10px;font-size:12px;color:var(--text-muted);">Mencari di data offline...</div></div>';
    
    try {
        let items = [];
        if (!query || query.trim() === '') {
            // Get all products
            items = await OfflineDB.getAllProducts();
        } else {
            items = await OfflineDB.searchProducts(query);
        }

        // Apply Filters from URL params
        const urlParams = new URLSearchParams(window.location.search);
        const catId = urlParams.get('category');
        const minP = parseFloat(urlParams.get('min_price'));
        const maxP = parseFloat(urlParams.get('max_price'));
        
        if (catId || !isNaN(minP) || !isNaN(maxP)) {
            items = items.filter(p => {
                let match = true;
                // Filter by category (compare category_id if available, or fallback to category name)
                if (catId) {
                    const pCatId = String(p.category_id || '');
                    const pCatName = String(p.category_name || '').toLowerCase();
                    const filterCat = String(catId).toLowerCase();
                    if (pCatId !== String(catId) && pCatName !== filterCat) {
                        match = false;
                    }
                }
                // Filter by price range
                const price = p.price_small_retail ? parseFloat(p.price_small_retail) : (p.packagings && p.packagings.length > 0 ? parseFloat(p.packagings[0].sell_price_retail) : 0);
                if (!isNaN(minP) && price < minP) match = false;
                if (!isNaN(maxP) && price > maxP) match = false;
                return match;
            });
        }

        // Sort exactly like online mode: ORDER BY COALESCE(updated_at, created_at) DESC, full_name ASC
        items.sort((a, b) => {
            const dateA = new Date(a.updated_at || a.created_at || 0).getTime();
            const dateB = new Date(b.updated_at || b.created_at || 0).getTime();
            if (dateA !== dateB) return dateB - dateA;
            
            const nameA = (a.full_name || '').toLowerCase();
            const nameB = (b.full_name || '').toLowerCase();
            if (nameA < nameB) return -1;
            if (nameA > nameB) return 1;
            return 0;
        });

        if (items.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-box-seam"></i>
                    <h3>Produk Tidak Ditemukan</h3>
                    <p>Tidak ada produk yang cocok dengan pencarian (Offline Mode).</p>
                </div>
            `;
            return;
        }

        let html = '';
        items.forEach(p => {
            const name = (p.full_name || p.short_label || '-').replace(/</g, '&lt;');
            const brandCat = `${p.brand_name || ''} ${p.brand_name && p.category_name ? '·' : ''} ${p.category_name || ''}`;
            const price = p.price_small_retail ? parseFloat(p.price_small_retail) : (p.packagings && p.packagings.length > 0 ? parseFloat(p.packagings[0].sell_price_retail) : 0);
            const priceWs = p.price_small_wholesale ? parseFloat(p.price_small_wholesale) : (p.packagings && p.packagings.length > 0 ? parseFloat(p.packagings[0].sell_price_wholesale) : 0);
            
            const photoHtml = p.photo 
                ? `<div class="product-icon" style="width:60px; height:60px; border-radius:var(--radius-md); overflow:hidden; display:flex; align-items:center; justify-content:center; background:transparent; flex-shrink:0; margin-right:16px;">
                       <img src="${BASE_URL}${p.photo}" style="width:100%; height:100%; object-fit:contain;">
                   </div>`
                : `<div class="product-icon" style="width:60px; height:60px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; background:var(--primary-bg); color:var(--primary); font-size:1.5rem; flex-shrink:0; margin-right:16px;">
                       <i class="bi bi-box-seam"></i>
                   </div>`;

            const _isUnavail = (p.is_available == 0 || p.is_available === '0' || p.is_available === false);

            html += `
            <div class="product-card" data-id="${p.id}" data-available="${_isUnavail ? 0 : 1}" style="position:relative;display:block;${_isUnavail ? 'opacity:0.65;' : ''}">
                ${!ROLE_IS_STAFF ? `<input type="checkbox" class="product-checkbox" value="${p.id}" style="display:none;position:absolute;top:16px;left:16px;width:20px;height:20px;accent-color:var(--primary);z-index:2;">` : ''}
                <a href="${BASE_URL}products/${p.id}" class="product-card-link" style="display:flex;text-decoration:none;color:inherit;width:100%;">
                    ${photoHtml}
                    <div class="product-info" style="width:calc(100% - 110px);">
                        <div class="product-name" style="padding-right:0;">${name}</div>
                        <div class="product-category">${brandCat}</div>`;

            let baseMarginHtml = '';
            if (ROLE_IS_SUPERADMIN && p.packagings && p.packagings.length > 0) {
                const basePkg = p.packagings[0];
                const baseMarginAmt = parseFloat(basePkg.sell_price_retail) - parseFloat(basePkg.buy_price || 0);
                const baseMarginPct = parseFloat(basePkg.buy_price) > 0 ? ((baseMarginAmt / parseFloat(basePkg.buy_price)) * 100).toFixed(1) : 0;
                baseMarginHtml = `
                                    <div style="font-size:10px; color:var(--text-muted); opacity:0.8; text-shadow:0 1px 1px rgba(0,0,0,0.1); margin-top:2px; text-align:right;">
                                        M: Rp${parseFloat(basePkg.buy_price || 0).toLocaleString('id-ID')} | P: Rp${baseMarginAmt.toLocaleString('id-ID')} (${baseMarginPct}%)
                                    </div>`;
            }

            html += `
                        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:4px;">
                            <div style="display:flex;flex-direction:column;">
                                <div>
                                    ${price ? `<span class="product-price">Rp${price.toLocaleString('id-ID')}</span>` : ''}
                                    ${priceWs ? `<span class="product-price-wholesale" style="margin-left:6px;">Rp${priceWs.toLocaleString('id-ID')}</span>` : ''}
                                </div>
                                ${baseMarginHtml}
                            </div>
                            <span class="product-stock">${p.current_qty_base || 0} pcs</span>
                        </div>`;
            
            if (p.packagings && p.packagings.length > 1) {
                html += `<div style="margin-top:8px;padding-top:8px;border-top:1px dashed var(--border-color);display:flex;flex-direction:column;gap:4px;">`;
                p.packagings.forEach((pkg, idx) => {
                    if (idx === 0) return;
                    const spR = parseFloat(pkg.sell_price_retail);
                    const spW = parseFloat(pkg.sell_price_wholesale);
                    const bp = parseFloat(pkg.buy_price || 0);
                    const marginAmt = spR - bp;
                    const marginPct = bp > 0 ? ((marginAmt / bp) * 100).toFixed(1) : 0;
                    
                    html += `
                        <div style="display:flex;flex-direction:column;font-size:10px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="color:var(--text-muted);font-weight:600;"><i class="bi bi-box2"></i> ${pkg.unit_name}</span>
                                <div style="text-align:right;">
                                    <span style="color:var(--success);">Rp${spR.toLocaleString('id-ID')}</span>
                                    ${spW > 0 ? `<span style="color:var(--warning);margin-left:4px;">(G: Rp${spW.toLocaleString('id-ID')})</span>` : ''}
                                </div>
                            </div>
                            ${ROLE_IS_SUPERADMIN ? `<div style="text-align:right;font-size:9px;color:var(--text-muted);opacity:0.8;text-shadow:0 1px 1px rgba(0,0,0,0.1);">M: Rp${bp.toLocaleString('id-ID')} | P: Rp${marginAmt.toLocaleString('id-ID')} (${marginPct}%)</div>` : ''}
                        </div>`;
                });
                html += `</div>`;
            }

            const availBg = (p.is_available == 0 || p.is_available === '0' || p.is_available === false) ? 'var(--surface-2)' : 'var(--success)';
            const availTx = (p.is_available == 0 || p.is_available === '0' || p.is_available === false) ? '0px' : '14px';
            const availVal = (p.is_available == 0 || p.is_available === '0' || p.is_available === false) ? 0 : 1;
            const availTitle = availVal === 1 ? 'Nonaktifkan produk' : 'Aktifkan produk';
            const cardOpacity = availVal === 0 ? 'opacity:0.65;' : '';

            html += `
                    </div>
                </a>
                ${!ROLE_IS_STAFF ? `
                <button class="btn-offline-edit" onclick="openOfflineEdit(${p.id}, event)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); width:36px; height:36px; border-radius:50%; background:var(--primary-light); color:var(--primary); border:none; display:flex; align-items:center; justify-content:center; z-index:5;">
                    <i class="bi bi-pencil"></i>
                </button>
                ` : ''}
                ${_isUnavail ? `<div style="position:absolute;top:0;right:0;background:var(--danger);color:white;font-size:9px;padding:2px 8px;border-bottom-left-radius:var(--radius-sm);z-index:2;font-weight:600;">UNAVAILABLE</div>` : ''}
                ${!ROLE_IS_STAFF ? `<button type="button" class="avail-toggle-btn" title="${availTitle}" onclick="event.stopPropagation(); quickToggleAvailability(this, ${p.id}, ${availVal})" style="position:absolute;top:10px;right:60px;width:34px;height:20px;border-radius:10px;border:none;cursor:pointer;padding:2px;transition:background 0.25s;background:${availBg};display:flex;align-items:center;z-index:3;"><span style="display:block;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.3);transition:transform 0.25s;transform:translateX(${availTx});"></span></button>` : ''}
            </div>`;
        });
        
        // Update product list and count
        container.innerHTML = html;
        const totalText = document.getElementById('totalProductsText');
        if (totalText) totalText.textContent = `${items.length} produk (Offline)`;
        
        // Intercept link clicks if offline
        const links = container.querySelectorAll('.product-card-link');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!navigator.onLine) {
                    e.preventDefault();
                    showToast('Detail produk penuh tidak tersedia offline. Gunakan tombol Pensil untuk Edit Cepat.', 'info');
                }
            });
        });

        // Re-attach long press listeners for newly rendered items
        if (typeof attachProductCardListeners === 'function') attachProductCardListeners();
        
    } catch (e) {
        console.error(e);
        container.innerHTML = '<div style="text-align:center;padding:20px;color:var(--danger);">Terjadi kesalahan saat memuat data offline.</div>';
    }
}

// Auto-run offline search jika tidak ada koneksi internet, DB offline, atau data PHP kosong
document.addEventListener('DOMContentLoaded', async () => {
    if (typeof OfflineDB !== 'undefined') {
        await OfflineDB.init();
        const hasServerCards = document.querySelectorAll('#productListContainer .product-card, #productListContainer [data-id]').length > 0;
        if (!navigator.onLine || window.IS_DB_OFFLINE || !hasServerCards) {
            const urlParams = new URLSearchParams(window.location.search);
            const q = urlParams.get('q') || '';
            doOfflineSearch(q);
        }
    }
});

// =====================================================
// QUICK TOGGLE AVAILABILITY
// =====================================================
let _availPendingBtn = null;
let _availPendingId = null;
let _availPendingNewVal = null;

function quickToggleAvailability(btn, productId, currentVal) {
    // currentVal: 1 = saat ini Tersedia, 0 = saat ini Tidak Tersedia
    const newVal = currentVal === 1 ? 0 : 1;
    _availPendingBtn = btn;
    _availPendingId = productId;
    _availPendingNewVal = newVal;

    const modal  = document.getElementById('availConfirmModal');
    const icon   = document.getElementById('availModalIcon');
    const title  = document.getElementById('availConfirmTitle');
    const body   = document.getElementById('availConfirmBody');
    const btnOk  = document.getElementById('availBtnConfirm');

    if (newVal === 0) {
        // Disable product
        icon.innerHTML  = '<i class="bi bi-eye-slash-fill" style="color:#f59e0b;"></i>';
        icon.style.background = 'rgba(245,158,11,0.12)';
        title.textContent = 'Nonaktifkan Produk?';
        body.innerHTML  = 'Produk ini akan diubah menjadi <strong style="color:var(--text-primary);">Tidak Tersedia</strong>.<br>Produk tidak akan muncul di hasil pencarian <strong style="color:var(--primary);">Kasir POS</strong>, sehingga kasir tidak bisa menambahkannya ke transaksi.';
        btnOk.style.background = '#f59e0b';
        btnOk.textContent = 'Ya, Nonaktifkan';
    } else {
        // Enable product
        icon.innerHTML  = '<i class="bi bi-check-circle-fill" style="color:var(--success);"></i>';
        icon.style.background = 'rgba(34,197,94,0.12)';
        title.textContent = 'Aktifkan Produk?';
        body.innerHTML  = 'Produk ini akan diubah menjadi <strong style="color:var(--success);">Tersedia</strong>.<br>Produk akan kembali muncul di pencarian <strong style="color:var(--primary);">Kasir POS</strong>.';
        btnOk.style.background = 'var(--success)';
        btnOk.textContent = 'Ya, Aktifkan';
    }

    btnOk.onclick = _confirmAvailToggle;
    modal.classList.add('show');
}

function _closeAvailModal() {
    const modal = document.getElementById('availConfirmModal');
    modal.classList.remove('show');
    _availPendingBtn = null;
    _availPendingId = null;
    _availPendingNewVal = null;
}

async function _confirmAvailToggle() {
    const btn = _availPendingBtn;
    const id  = _availPendingId;
    const newVal = _availPendingNewVal;
    _closeAvailModal();

    if (!btn || id === null) return;

    // Optimistic UI update
    const knob = btn.querySelector('span');
    if (newVal === 1) {
        btn.style.background = 'var(--success)';
        if (knob) knob.style.transform = 'translateX(14px)';
        btn.title = 'Nonaktifkan produk';
    } else {
        btn.style.background = 'var(--surface-2)';
        if (knob) knob.style.transform = 'translateX(0px)';
        btn.title = 'Aktifkan produk';
    }
    const card = btn.closest('.product-card');
    if (card) {
        card.style.opacity = newVal === 1 ? '1' : '0.65';
        card.dataset.available = String(newVal);
    }
    btn.setAttribute('onclick', `event.stopPropagation(); quickToggleAvailability(this, ${id}, ${newVal})`);

    try {
        const csrfToken = '<?= $_SESSION["csrf_token"] ?? "" ?>';
        const res = await fetch(`${BASE_URL}api/products/${id}/availability`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            credentials: 'same-origin',
            body: JSON.stringify({ is_available: newVal, csrf_token: csrfToken })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Gagal update');

        // Also update Dexie local cache so offline search reflects the change immediately
        if (typeof OfflineDB !== 'undefined') {
            try {
                const local = await OfflineDB.getProductById(id);
                if (local) {
                    local.is_available = newVal;
                    await OfflineDB.saveProduct(local);
                }
            } catch(e) { /* non-critical */ }
        }

        if (typeof showToast === 'function') {
            showToast(newVal === 1 ? '✅ Produk diaktifkan' : '🔕 Produk dinonaktifkan', newVal === 1 ? 'success' : 'info');
        }
    } catch(err) {
        // Revert optimistic update on failure
        const revertVal = newVal === 1 ? 0 : 1;
        const knob2 = btn.querySelector('span');
        if (revertVal === 1) {
            btn.style.background = 'var(--success)';
            if (knob2) knob2.style.transform = 'translateX(14px)';
        } else {
            btn.style.background = 'var(--surface-2)';
            if (knob2) knob2.style.transform = 'translateX(0px)';
        }
        if (card) card.style.opacity = revertVal === 1 ? '1' : '0.65';
        btn.setAttribute('onclick', `event.stopPropagation(); quickToggleAvailability(this, ${id}, ${revertVal})`);
        if (typeof showToast === 'function') showToast('Gagal mengubah status: ' + err.message, 'error');
    }
}

function filterProductsClientSide(query) {
    const q = (query || '').toLowerCase().trim();
    const words = q.split(/\s+/).filter(w => w.length > 0);
    const items = document.querySelectorAll('#productListContainer .product-card, #productListContainer [data-id]');
    items.forEach(el => {
        const text = (el.textContent || '').toLowerCase();
        if (words.length === 0 || words.every(w => text.includes(w))) {
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    });
}

    // Listen for hardware scanner events on products page to show global modal
    document.addEventListener('hardware-barcode-scanned-products', (e) => {
        if (typeof window.showGlobalBarcodeModal === 'function') {
            window.showGlobalBarcodeModal(e.detail.code);
        }
    });

    // --- OFFLINE QUICK EDIT ---
    async function openOfflineEdit(productId, e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (typeof OfflineDB === 'undefined') {
            showToast('Offline Database belum siap.', 'error');
            return;
        }
        
        try {
            const product = await OfflineDB.getProductById(productId);
            if (!product) {
                showToast('Produk tidak ditemukan di cache offline.', 'error');
                return;
            }
            
            // Build modal HTML dynamically
            let modalHtml = `
            <div id="offlineEditModal" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;">
                <div style="background:var(--surface);width:100%;max-width:400px;border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow-lg);">
                    <h4 style="margin-top:0;margin-bottom:16px;">Edit Cepat (Offline)</h4>
                    
                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:6px;">Nama Produk</label>
                        <input type="text" id="offlineEditName" value="${(product.full_name || '').replace(/"/g, '&quot;')}" class="form-control" style="width:100%;padding:10px;border-radius:var(--radius-sm);border:1px solid var(--border-color);">
                    </div>
            `;
            
            if (product.packagings && product.packagings.length > 0) {
                const pkg = product.packagings[0];
                modalHtml += `
                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:6px;">Harga Jual Eceran (${pkg.unit_name || 'Satuan'})</label>
                        <input type="number" id="offlineEditPrice" value="${pkg.sell_price_retail || 0}" class="form-control" style="width:100%;padding:10px;border-radius:var(--radius-sm);border:1px solid var(--border-color);">
                    </div>
                `;
            } else {
                modalHtml += `<input type="hidden" id="offlineEditPrice" value="0">`;
            }
            
            modalHtml += `
                    <div style="display:flex;gap:12px;margin-top:24px;">
                        <button type="button" onclick="document.getElementById('offlineEditModal').remove()" style="flex:1;padding:12px;border:none;background:var(--surface-2);border-radius:var(--radius-md);cursor:pointer;">Batal</button>
                        <button type="button" onclick="saveOfflineEdit(${productId})" style="flex:1;padding:12px;border:none;background:var(--primary);color:white;border-radius:var(--radius-md);cursor:pointer;">Simpan Offline</button>
                    </div>
                </div>
            </div>`;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
        } catch (err) {
            console.error('Failed to open offline edit:', err);
            showToast('Gagal memuat produk.', 'error');
        }
    }
    
    async function saveOfflineEdit(productId) {
        const nameVal = document.getElementById('offlineEditName').value.trim();
        const priceVal = parseFloat(document.getElementById('offlineEditPrice').value) || 0;
        
        if (!nameVal) {
            showToast('Nama produk tidak boleh kosong', 'warning');
            return;
        }
        
        try {
            const product = await OfflineDB.getProductById(productId);
            if (product) {
                // Update local object
                product.full_name = nameVal;
                product.is_pending_update = true;
                
                let pkgId = null;
                if (product.packagings && product.packagings.length > 0) {
                    product.packagings[0].sell_price_retail = priceVal;
                    pkgId = product.packagings[0].id;
                }
                
                // Save to local cache
                await OfflineDB.saveProduct(product);
                
                // Queue API calls
                const csrfToken = '<?= $_SESSION["csrf_token"] ?? "" ?>';
                
                // 1. Update main product info
                await OfflineDB.addPendingChange(`${BASE_URL}api/products/update/${productId}`, 'POST', {
                    csrf_token: csrfToken,
                    full_name: nameVal,
                    short_label: nameVal.substring(0, 35),
                    category_id: product.category_id || '',
                    is_available: product.is_available || 1,
                    weight_value: product.weight_value || 0
                });
                
                // 2. Update packaging price if exists
                if (pkgId) {
                    await OfflineDB.addPendingChange(`${BASE_URL}api/products/packaging/${pkgId}`, 'POST', {
                        csrf_token: csrfToken,
                        unit_id: product.packagings[0].unit_id || '',
                        contained_qty: product.packagings[0].contained_qty || 1,
                        sell_price_retail: priceVal,
                        sell_price_wholesale: product.packagings[0].sell_price_wholesale || 0,
                        buy_price: product.packagings[0].buy_price || 0
                    });
                }
                
                document.getElementById('offlineEditModal').remove();
                showToast('✅ Tersimpan offline. Akan otomatis sinkron.', 'success');
                if (typeof updateSyncBadge === 'function') updateSyncBadge();
                
                // Refresh list
                const searchInput = document.getElementById('productSearch');
                doOfflineSearch(searchInput ? searchInput.value : '');
            }
        } catch (e) {
            console.error('Failed to save offline edit:', e);
            showToast('Gagal menyimpan perubahan offline', 'error');
        }
    }
</script>

