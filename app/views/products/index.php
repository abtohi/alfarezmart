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
                <input type="text" name="q" id="productSearchInput" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Cari produk..." autocomplete="off">
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
        <span style="font-size:var(--font-size-xs);color:var(--text-muted);">
            <?= (int)($products['total'] ?? 0) ?> produk
        </span>
        <?php
        $userLevel = $_SESSION['user_level'] ?? '';
        if (in_array($userLevel, ['superadmin', 'admin'], true)):
        ?>
        <a href="<?= BASE_URL ?>products/create" class="btn-primary-custom" style="padding:6px 12px;font-size:var(--font-size-xs);text-decoration:none;color:white;flex-shrink:0;">
            <i class="bi bi-plus"></i> Tambah
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($products['data'])): ?>
        <div class="empty-state">
            <i class="bi bi-box-seam"></i>
            <h3>Belum Ada Produk</h3>
            <p>Tambahkan produk pertama Anda.</p>
        </div>
    <?php else: ?>
        <?php foreach ($products['data'] as $p): ?>
            <a href="<?= BASE_URL ?>products/<?= (int)$p['id'] ?>" class="product-card">
                <div class="product-icon"><i class="bi bi-box-seam"></i></div>
                <div class="product-info">
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
                </div>
            </a>
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

<script>
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
        BarcodeUtil.scanBarcode(fakeInput, (code) => {
            document.body.removeChild(fakeInput);
            window.location.href = '<?= BASE_URL ?>products?q=' + encodeURIComponent(code);
        });
    } else {
        const code = prompt('Masukkan kode barcode:');
        if (code) window.location.href = '<?= BASE_URL ?>products?q=' + encodeURIComponent(code);
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
                const res = await fetch(`<?= BASE_URL ?>api/products/search?q=${encodeURIComponent(q)}`);
                const items = await res.json();
                if (!items || items.length === 0) {
                    resultsDiv.innerHTML = '<div style="padding:12px;text-align:center;color:var(--text-muted);font-size:var(--font-size-xs);">Tidak ditemukan</div>';
                } else {
                    resultsDiv.innerHTML = items.map(p => {
                        const name = (p.short_label || p.full_name || '').replace(/</g,'&lt;');
                        const brand = (p.brand_name || '').replace(/</g,'&lt;');
                        const price = p.price_small_retail ? `Rp${parseInt(p.price_small_retail).toLocaleString('id-ID')}` : '';
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
