<!-- Purchases Index View — grouped by date → supplier -->
<div class="page-section purchases-index">
    <div class="purchases-index__header">
        <h2 class="purchases-index__title">Riwayat Barang Masuk</h2>
        <a href="<?= BASE_URL ?>purchases/create" class="btn-primary-custom purchases-index__add">
            <i class="bi bi-plus-lg"></i> Input Baru
        </a>
    </div>

    <?php if (empty($purchases['data'])): ?>
        <div class="empty-state">
            <i class="bi bi-cart-plus"></i>
            <h3>Belum Ada Data</h3>
            <p>Mulai input barang masuk dari supplier</p>
            <a href="<?= BASE_URL ?>purchases/create" class="btn-primary-custom" style="margin-top:16px;text-decoration:none;color:white;">Input Barang Masuk</a>
        </div>
    <?php else: ?>
        <div class="purchases-index__summary">
            <span><?= (int)($purchases['total'] ?? 0) ?> transaksi</span>
            <span class="purchases-index__summary-dot">·</span>
            <span>Terbaru di atas</span>
        </div>

        <div class="purchase-groups">
        <?php foreach ($groupedPurchases ?? [] as $dayIdx => $day): ?>
            <div class="purchase-date-group">
                <button type="button" class="purchase-group-toggle purchase-date-toggle" onclick="togglePurchaseGroup(this)" aria-expanded="<?= $dayIdx === 0 ? 'true' : 'false' ?>">
                    <span class="purchase-group-toggle__main">
                        <i class="bi bi-calendar3 purchase-group-toggle__icon"></i>
                        <span class="purchase-group-toggle__label"><?= htmlspecialchars($day['date_label']) ?></span>
                    </span>
                    <span class="purchase-group-toggle__meta">
                        <span class="purchase-group-toggle__count"><?= (int)$day['total'] ?> trx</span>
                        <span class="purchase-group-toggle__amount"><?= Helper::rupiah($day['grand_total']) ?></span>
                        <i class="bi bi-chevron-down purchase-group-toggle__chev"></i>
                    </span>
                </button>
                <div class="purchase-group-body" style="display:<?= $dayIdx === 0 ? 'block' : 'none' ?>;">
                    <?php foreach ($day['suppliers'] as $supIdx => $sup): ?>
                    <div class="purchase-supplier-group">
                        <button type="button" class="purchase-group-toggle purchase-supplier-toggle" onclick="togglePurchaseGroup(this)" aria-expanded="<?= $dayIdx === 0 && $supIdx === 0 ? 'true' : 'false' ?>">
                            <span class="purchase-group-toggle__main">
                                <i class="bi bi-building purchase-group-toggle__icon purchase-group-toggle__icon--supplier"></i>
                                <span class="purchase-group-toggle__label"><?= htmlspecialchars($sup['supplier_name']) ?></span>
                            </span>
                            <span class="purchase-group-toggle__meta">
                                <span class="purchase-group-toggle__count"><?= (int)$sup['total'] ?></span>
                                <span class="purchase-group-toggle__amount"><?= Helper::rupiah($sup['grand_total']) ?></span>
                                <i class="bi bi-chevron-down purchase-group-toggle__chev"></i>
                            </span>
                        </button>
                        <div class="purchase-group-body purchase-supplier-body" style="display:<?= $dayIdx === 0 && $supIdx === 0 ? 'block' : 'none' ?>;">
                            <?php foreach ($sup['purchases'] as $p): ?>
                            <a href="<?= BASE_URL ?>purchases/<?= (int)$p['id'] ?>" class="purchase-item-card">
                                <div class="purchase-item-card__icon">
                                    <i class="bi bi-box-arrow-in-down"></i>
                                </div>
                                <div class="purchase-item-card__body">
                                    <div class="purchase-item-card__row">
                                        <span class="purchase-item-card__code"><?= htmlspecialchars($p['purchase_code']) ?></span>
                                        <span class="purchase-item-card__amount"><?= Helper::rupiah($p['grand_total']) ?></span>
                                    </div>
                                    <div class="purchase-item-card__row purchase-item-card__row--sub">
                                        <span><i class="bi bi-box-seam"></i> <?= (int)($p['total_items'] ?? 0) ?> item</span>
                                        <?php if (!empty($p['notes'])): ?>
                                        <span class="purchase-item-card__note"><?= htmlspecialchars(mb_strimwidth($p['notes'], 0, 40, '…')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right purchase-item-card__chev"></i>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <?php if (($purchases['total_pages'] ?? 1) > 1): ?>
            <?php
            $curPage = (int)($purchases['page'] ?? 1);
            $totalPages = (int)$purchases['total_pages'];
            $range = 2;
            $pStart = max(1, $curPage - $range);
            $pEnd = min($totalPages, $curPage + $range);
            ?>
            <div style="display:flex;justify-content:center;align-items:center;gap:4px;margin-top:20px;flex-wrap:wrap;">
                <?php if ($curPage > 1): ?>
                    <a href="<?= BASE_URL ?>purchases?page=<?= $curPage - 1 ?>" class="chip" style="padding:6px 10px;"><i class="bi bi-chevron-left"></i></a>
                <?php else: ?>
                    <span class="chip" style="padding:6px 10px;opacity:0.35;pointer-events:none;"><i class="bi bi-chevron-left"></i></span>
                <?php endif; ?>
                <?php if ($pStart > 1): ?>
                    <a href="<?= BASE_URL ?>purchases?page=1" class="chip">1</a>
                    <?php if ($pStart > 2): ?><span style="color:var(--text-muted);font-size:12px;padding:0 2px;">…</span><?php endif; ?>
                <?php endif; ?>
                <?php for ($i = $pStart; $i <= $pEnd; $i++): ?>
                    <a href="<?= BASE_URL ?>purchases?page=<?= $i ?>" class="chip <?= $curPage === $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($pEnd < $totalPages): ?>
                    <?php if ($pEnd < $totalPages - 1): ?><span style="color:var(--text-muted);font-size:12px;padding:0 2px;">…</span><?php endif; ?>
                    <a href="<?= BASE_URL ?>purchases?page=<?= $totalPages ?>" class="chip"><?= $totalPages ?></a>
                <?php endif; ?>
                <?php if ($curPage < $totalPages): ?>
                    <a href="<?= BASE_URL ?>purchases?page=<?= $curPage + 1 ?>" class="chip" style="padding:6px 10px;"><i class="bi bi-chevron-right"></i></a>
                <?php else: ?>
                    <span class="chip" style="padding:6px 10px;opacity:0.35;pointer-events:none;"><i class="bi bi-chevron-right"></i></span>
                <?php endif; ?>
            </div>
            <div style="text-align:center;margin-top:6px;font-size:11px;color:var(--text-muted);">
                Halaman <?= $curPage ?> dari <?= $totalPages ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function togglePurchaseGroup(btn) {
    const body = btn.nextElementSibling;
    if (!body || !body.classList.contains('purchase-group-body')) return;
    const isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : 'block';
    btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    const chev = btn.querySelector('.purchase-group-toggle__chev');
    if (chev) chev.className = isOpen ? 'bi bi-chevron-down purchase-group-toggle__chev' : 'bi bi-chevron-up purchase-group-toggle__chev';
}
</script>
