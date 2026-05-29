<!-- Purchases Index View — grouped by date → supplier -->
<div class="page-section purchases-index">
    <div class="purchases-index__header">
        <h2 class="purchases-index__title">Riwayat Barang Masuk</h2>
        <a href="<?= BASE_URL ?>purchases/create" class="btn-primary-custom purchases-index__add">
            <i class="bi bi-plus-lg"></i> Input Baru
        </a>
    </div>
    
    <input type="hidden" id="csrfToken" value="<?= $csrfToken ?? '' ?>">

    <!-- Mass Action Toolbar -->
    <div id="massActionToolbar" style="display:none; background:rgba(230,57,70,0.1); border-radius:var(--radius-md); padding:12px; margin-bottom:16px; align-items:center; justify-content:space-between; border:1px solid rgba(230,57,70,0.2);">
        <label style="display:flex; align-items:center; gap:8px; font-size:var(--font-size-sm); font-weight:600; color:var(--danger); cursor:pointer;">
            <input type="checkbox" id="chkSelectAll" style="width:16px; height:16px; accent-color:var(--danger);" onchange="toggleSelectAllPurchases(this)">
            <span id="massSelectCount">0 Terpilih</span>
        </label>
        <button type="button" class="btn-primary-custom" style="background:var(--danger-bg); color:var(--danger); font-size:11px; padding:6px 12px; border-color:var(--danger);" onclick="deleteSelectedPurchases()">
            <i class="bi bi-trash"></i> Hapus Terpilih
        </button>
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
                            <div class="purchase-item-card" style="position:relative; padding-right:120px; cursor:pointer;" onclick="if(!event.target.closest('.purchase-actions')) window.location.href='<?= BASE_URL ?>purchases/<?= (int)$p['id'] ?>'">
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
                                <!-- Actions Container -->
                                <div class="purchase-actions" style="position:absolute; top:50%; right:12px; transform:translateY(-50%); display:flex; gap:12px; align-items:center; background:var(--bg-card); padding-left:8px;">
                                    <?php if (!empty($p['invoice_photo'])): ?>
                                        <a href="<?= invoicePhotoUrl($p['invoice_photo']) ?>" target="_blank" style="color:var(--info); font-size:1.2rem; cursor:pointer; text-decoration:none;" title="Lihat Foto Invoice">
                                            <i class="bi bi-image"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>purchases/<?= (int)$p['id'] ?>/edit" style="color:var(--primary); font-size:1.2rem; cursor:pointer; text-decoration:none;" title="Edit Pembelian">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" onclick="deleteSinglePurchase(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['purchase_code']) ?>')" style="background:none;border:none;color:var(--danger);font-size:1.2rem;cursor:pointer;padding:0;" title="Hapus Pembelian"><i class="bi bi-trash"></i></button>
                                    <input type="checkbox" class="purchase-chk" value="<?= (int)$p['id'] ?>" style="width:18px;height:18px;accent-color:var(--danger);cursor:pointer;margin-left:4px;" onchange="updatePurchaseSelection()">
                                </div>
                            </div>
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

function updatePurchaseSelection() {
    const checkboxes = document.querySelectorAll('.purchase-chk');
    const checkedBoxes = Array.from(checkboxes).filter(c => c.checked);
    const count = checkedBoxes.length;
    
    const toolbar = document.getElementById('massActionToolbar');
    const countSpan = document.getElementById('massSelectCount');
    const chkSelectAll = document.getElementById('chkSelectAll');
    
    if (toolbar) {
        toolbar.style.display = count > 0 ? 'flex' : 'none';
    }
    if (countSpan) {
        countSpan.textContent = `${count} Terpilih`;
    }
    if (chkSelectAll) {
        chkSelectAll.checked = (count === checkboxes.length && checkboxes.length > 0);
        chkSelectAll.indeterminate = (count > 0 && count < checkboxes.length);
    }
}

function toggleSelectAllPurchases(masterChk) {
    const checkboxes = document.querySelectorAll('.purchase-chk');
    checkboxes.forEach(chk => {
        // Only toggle if the group is visible? For now just toggle all available on page
        chk.checked = masterChk.checked;
    });
    updatePurchaseSelection();
}

async function deleteSinglePurchase(id, code) {
    await AppModal.show({
        title: 'Hapus Pembelian',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `
            <p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.7;">Yakin ingin menghapus nota <strong>${code}</strong>?</p>
            <div style="background:var(--warning-bg);border:1px solid var(--warning);border-radius:var(--radius-sm);padding:10px 14px;margin-top:12px;font-size:11px;color:var(--warning);">
                <i class="bi bi-exclamation-triangle-fill"></i> <strong>PERINGATAN:</strong> Semua stok barang dari nota ini akan DIBATALKAN. Pastikan barang belum terjual!
            </div>
        `,
        submitText: 'Ya, Hapus & Kembalikan Stok',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const csrfToken = document.getElementById('csrfToken').value;
                const res = await api(`<?= BASE_URL ?>api/purchases/${id}/delete`, 'POST', { csrf_token: csrfToken });
                if (res.success) {
                    showToast(res.message || 'Pembelian berhasil dihapus', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                    return true;
                }
            } catch(e) { }
            return false;
        }
    });
}

async function deleteSelectedPurchases() {
    const checkboxes = document.querySelectorAll('.purchase-chk:checked');
    if (checkboxes.length === 0) return;
    
    const count = checkboxes.length;
    await AppModal.show({
        title: `Hapus ${count} Pembelian`,
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `
            <p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.7;">Yakin ingin menghapus <strong>${count}</strong> nota pembelian sekaligus?</p>
            <div style="background:var(--warning-bg);border:1px solid var(--warning);border-radius:var(--radius-sm);padding:10px 14px;margin-top:12px;font-size:11px;color:var(--warning);">
                <i class="bi bi-exclamation-triangle-fill"></i> <strong>PERINGATAN:</strong> Stok dari semua nota yang dihapus akan DIBATALKAN.
            </div>
        `,
        submitText: 'Ya, Hapus Semua',
        cancelText: 'Batal',
        onSubmit: async () => {
            try {
                const csrfToken = document.getElementById('csrfToken').value;
                // Delete sequentially or via bulk API. We'll do it sequentially for simplicity and safety,
                // or if there's a bulk delete API we can use that. Since we only have single delete, we loop.
                let successCount = 0;
                for (let chk of checkboxes) {
                    const res = await api(`<?= BASE_URL ?>api/purchases/${chk.value}/delete`, 'POST', { csrf_token: csrfToken });
                    if (res.success) successCount++;
                }
                showToast(`Berhasil menghapus ${successCount} pembelian`, 'success');
                setTimeout(() => window.location.reload(), 1000);
                return true;
            } catch(e) { }
            return false;
        }
    });
}
</script>
