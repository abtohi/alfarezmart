<!-- Products List View -->
<?php
/**
 * @var array $products
 * @var array $categories
 * @var string $search
 * @var int|null $selectedCategory
 */
?>
<div class="page-section">
    <div style="display:flex;gap:8px;margin-bottom:10px;align-items:center;min-width:0;">
        <form method="GET" action="/products" style="flex:1;min-width:0;" id="productSearchForm">
            <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory ?? '') ?>">
            <div class="search-input-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" name="q" id="productSearchInput" class="no-track" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Cari produk..." autocomplete="off">
                <?php if (!empty($search)): ?>
                    <a href="<?= BASE_URL ?>products<?= $selectedCategory ? '?category=' . (int)$selectedCategory : '' ?>" style="color:var(--text-muted);text-decoration:none;flex-shrink:0;"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
        <button type="button" onclick="scanProductBarcode()" title="Scan Barcode"
                style="background:var(--primary);color:white;border:none;border-radius:var(--radius-lg);padding:10px 12px;cursor:pointer;font-size:1rem;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-upc-scan"></i>
        </button>
    </div>
    <div style="margin-bottom:12px;">
        <div id="categoryFilterSearchBox"></div>
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
            <div class="product-card" data-id="<?= (int)$p['id'] ?>" style="position:relative;display:block;">
                <?php if (!$isStaff): ?>
                <input type="checkbox" class="product-checkbox" value="<?= (int)$p['id'] ?>" style="display:none;position:absolute;top:16px;left:16px;width:20px;height:20px;accent-color:var(--primary);z-index:2;">
                <?php endif; ?>
                <a href="<?= BASE_URL ?>products/<?= (int)$p['id'] ?>" class="product-card-link" style="display:flex;text-decoration:none;color:inherit;width:100%;">
                    <div class="product-icon"><i class="bi bi-box-seam"></i></div>
                    <div class="product-info" style="width:100%;">
                        <div class="product-name"><?= htmlspecialchars($p['full_name'] ?? $p['short_label'] ?? '-') ?></div>
                    <div class="product-category">
                        <?= htmlspecialchars($p['brand_name'] ?? '') ?>
                        <?php if (!empty($p['brand_name']) && !empty($p['category_name'])): ?> · <?php endif; ?>
                        <?= htmlspecialchars($p['category_name'] ?? '') ?>
                        <?php if (!empty($p['updated_at'])): ?>
                            <span style="margin-left:4px;opacity:0.6;" title="Terakhir diupdate: <?= htmlspecialchars($p['updated_at']) ?>">· <?= Helper::timeAgo($p['updated_at']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:4px;">
                        <div>
                            <?php if (!empty($p['price_small_retail'])): ?>
                                <span class="product-price"><?= Helper::rupiah($p['price_small_retail']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($p['price_small_wholesale'])): ?>
                                <span class="product-price-wholesale" style="margin-left:6px;"><?= Helper::rupiah($p['price_small_wholesale']) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="product-stock"><?= (int)($p['current_qty_base'] ?? 0) ?> pcs</span>
                    </div>
                    <?php if (!empty($p['packagings']) && count($p['packagings']) > 1): ?>
                    <div style="margin-top:8px;padding-top:8px;border-top:1px dashed var(--border-color);display:flex;flex-direction:column;gap:4px;">
                        <?php foreach($p['packagings'] as $idx => $pkg): if($idx == 0) continue; ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:10px;">
                            <span style="color:var(--text-muted);font-weight:600;"><i class="bi bi-box2"></i> <?= htmlspecialchars($pkg['unit_name']) ?></span>
                            <div style="text-align:right;">
                                <span style="color:var(--success);"><?= Helper::rupiah($pkg['sell_price_retail']) ?></span>
                                <?php if($pkg['sell_price_wholesale'] > 0): ?>
                                <span style="color:var(--warning);margin-left:4px;">(G: <?= Helper::rupiah($pkg['sell_price_wholesale']) ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>

        <?php if (($products['total_pages'] ?? 1) > 1): ?>
            <?php
                $totalPages = $products['total_pages'];
                $currentPage = $products['page'] ?? 1;
                $qStr = urlencode($search ?? '');
                $catStr = $selectedCategory ? '&category=' . (int)$selectedCategory : '';
                $buildUrl = function($p) use ($qStr, $catStr) {
                    return BASE_URL . "products?page={$p}&q={$qStr}{$catStr}";
                };
                // Show max 5 page buttons around current
                $range = 2;
                $start = max(1, $currentPage - $range);
                $end = min($totalPages, $currentPage + $range);
            ?>
            <div style="display:flex;justify-content:center;align-items:center;gap:4px;margin-top:20px;flex-wrap:wrap;">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= $buildUrl($currentPage - 1) ?>" class="chip" title="Sebelumnya" style="padding:6px 10px;"><i class="bi bi-chevron-left"></i></a>
                <?php else: ?>
                    <span class="chip" style="padding:6px 10px;opacity:0.35;pointer-events:none;"><i class="bi bi-chevron-left"></i></span>
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
                    <a href="<?= $buildUrl($currentPage + 1) ?>" class="chip" title="Selanjutnya" style="padding:6px 10px;"><i class="bi bi-chevron-right"></i></a>
                <?php else: ?>
                    <span class="chip" style="padding:6px 10px;opacity:0.35;pointer-events:none;"><i class="bi bi-chevron-right"></i></span>
                <?php endif; ?>
            </div>
            <div style="text-align:center;margin-top:6px;font-size:11px;color:var(--text-muted);">
                Halaman <?= $currentPage ?> dari <?= $totalPages ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.product-card.select-mode .product-checkbox { display: block !important; }
.product-card.select-mode .product-card-link { margin-left: 28px; }
.product-card.select-mode { padding-left: 8px; }
.product-card.selected { border-color: var(--primary); background: rgba(230,57,70,0.05); }
</style>

<script>
let selectMode = false;
let pressTimer;
const ROLE_IS_STAFF = <?= $isStaff ? 'true' : 'false' ?>;

document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.product-card');
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCountSpan = document.getElementById('selectedCount');
    const btnAddProduct = document.getElementById('btnAddProduct');
    const selectAllContainer = document.getElementById('selectAllContainer');
    const totalProductsText = document.getElementById('totalProductsText');

    // Staff tidak punya hak hapus/edit — skip semua handler seleksi & long-press
    if (ROLE_IS_STAFF) return;

    function updateSelectionState() {
        const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
        const count = checkedBoxes.length;
        if (selectedCountSpan) selectedCountSpan.textContent = count;
        
        if (count > 0) {
            btnBulkDelete.style.display = 'flex';
            if (btnAddProduct) btnAddProduct.style.display = 'none';
        } else if (selectMode) {
            btnBulkDelete.style.display = 'none';
            if (btnAddProduct) btnAddProduct.style.display = 'inline-flex';
        }

        selectAllCheckbox.checked = count === cards.length && cards.length > 0;
    }

    function toggleSelectMode(enable) {
        selectMode = enable;
        if (enable) {
            document.querySelectorAll('.product-card').forEach(c => c.classList.add('select-mode'));
            selectAllContainer.style.display = 'flex';
            totalProductsText.style.display = 'none';
            if (btnAddProduct) btnAddProduct.style.display = 'none';
            btnBulkDelete.style.display = document.querySelectorAll('.product-checkbox:checked').length > 0 ? 'flex' : 'none';
        } else {
            document.querySelectorAll('.product-card').forEach(c => {
                c.classList.remove('select-mode', 'selected');
                const cb = c.querySelector('.product-checkbox');
                if (cb) cb.checked = false;
            });
            selectAllContainer.style.display = 'none';
            totalProductsText.style.display = 'inline-block';
            if (btnAddProduct) btnAddProduct.style.display = 'inline-flex';
            btnBulkDelete.style.display = 'none';
            updateSelectionState();
        }
    }

    cards.forEach(card => {
        const link = card.querySelector('.product-card-link');
        const checkbox = card.querySelector('.product-checkbox');

        // Long press logic
        link.addEventListener('touchstart', (e) => {
            pressTimer = window.setTimeout(() => {
                toggleSelectMode(true);
                checkbox.checked = true;
                card.classList.add('selected');
                updateSelectionState();
                if (window.navigator && window.navigator.vibrate) {
                    window.navigator.vibrate(50);
                }
            }, 600); // 600ms long press
        }, {passive: true});

        link.addEventListener('touchend', () => {
            clearTimeout(pressTimer);
        });

        link.addEventListener('touchmove', () => {
            clearTimeout(pressTimer);
        });

        link.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return; // only left click
            pressTimer = window.setTimeout(() => {
                toggleSelectMode(true);
                checkbox.checked = true;
                card.classList.add('selected');
                updateSelectionState();
            }, 600);
        });

        link.addEventListener('mouseup', () => {
            clearTimeout(pressTimer);
        });

        link.addEventListener('mouseleave', () => {
            clearTimeout(pressTimer);
        });

        // Click intercept when in select mode
        link.addEventListener('click', (e) => {
            if (selectMode) {
                e.preventDefault();
                checkbox.checked = !checkbox.checked;
                if (checkbox.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
                updateSelectionState();
                
                // Exit select mode if nothing is checked
                if (document.querySelectorAll('.product-checkbox:checked').length === 0) {
                    toggleSelectMode(false);
                }
            }
        });

        // Direct checkbox click
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
    });

    selectAllCheckbox.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        cards.forEach(card => {
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

    // Also close select mode if clicking outside (optional, omitted for simplicity)
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
    const q = document.getElementById('productSearchInput')?.value?.trim() || '';
    let url = '<?= BASE_URL ?>products?';
    if (val) url += 'category=' + encodeURIComponent(val) + '&';
    if (q) url += 'q=' + encodeURIComponent(q);
    window.location.href = url;
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
                try {
                    const res = await fetch(`<?= BASE_URL ?>api/products/search?q=${encodeURIComponent(q)}`);
                    items = await res.json();
                } catch (apiErr) {
                    if (typeof OfflineDB !== 'undefined') {
                        items = await OfflineDB.searchProducts(q);
                    }
                }

                if (!items || items.length === 0) {
                    resultsDiv.innerHTML = '<div style="padding:12px;text-align:center;color:var(--text-muted);font-size:var(--font-size-xs);">Tidak ditemukan</div>';
                } else {
                    resultsDiv.innerHTML = items.map(p => {
                        const name = (p.short_label || p.full_name || '').replace(/</g,'&lt;');
                        const brand = (p.brand_name || '').replace(/</g,'&lt;');
                        const price = p.price_small_retail ? `Rp${parseInt(p.price_small_retail).toLocaleString('id-ID')}` : (p.packagings && p.packagings.length > 0 ? `Rp${parseInt(p.packagings[0].sell_price_retail).toLocaleString('id-ID')}` : '');
                        return `<a href="<?= BASE_URL ?>products/${p.id}" style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px solid var(--border-color);text-decoration:none;color:var(--text-primary);font-size:var(--font-size-sm);">
                            <div><div style="font-weight:600;">${name}</div>${brand ? `<div style="font-size:var(--font-size-xs);color:var(--text-muted);">${brand}</div>` : ''}</div>
                            ${price ? `<span style="color:var(--primary);font-weight:600;font-size:var(--font-size-xs);white-space:nowrap;">${price}</span>` : ''}
                        </a>`;
                    }).join('');
                }
                resultsDiv.style.display = 'block';
            } catch(e) { resultsDiv.style.display = 'none'; }
        }, 250);
    });

    input.addEventListener('keypress', e => {
        if (e.key === 'Enter') { resultsDiv.style.display = 'none'; document.getElementById('productSearchForm').submit(); }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#productSearchForm') && !e.target.closest('#productLiveResults'))
            resultsDiv.style.display = 'none';
    });
})();
</script>
