<!-- Purchase Detail View -->
<input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">
<div class="page-section" style="padding-bottom: 80px;">
    <!-- Back button -->
    <a href="<?= BASE_URL ?>purchases" style="color:var(--text-muted);text-decoration:none;font-size:var(--font-size-sm);display:inline-flex;align-items:center;gap:4px;margin-bottom:16px;">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>

    <!-- Purchase Info -->
    <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:20px;margin-bottom:16px;border:1px solid var(--border-color);">
        <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border-color);padding-bottom:12px;margin-bottom:12px;">
            <div style="font-weight:800;font-size:1.1rem;color:var(--primary);"><?= htmlspecialchars($purchase['purchase_code']) ?></div>
            <div style="font-size:var(--font-size-xs);color:var(--text-muted);"><?= Helper::formatDate($purchase['purchase_date']) ?></div>
        </div>
        
        <div style="display:grid;grid-template-columns:1fr;gap:12px;font-size:var(--font-size-sm);">
            <div>
                <div style="color:var(--text-muted);font-size:var(--font-size-xs);margin-bottom:2px;">Supplier</div>
                <div style="font-weight:600;"><i class="bi bi-building"></i> <?= htmlspecialchars($purchase['supplier_name'] ?? 'Tanpa Supplier') ?></div>
            </div>
            <?php if ($purchase['sales_rep_name']): ?>
            <div>
                <div style="color:var(--text-muted);font-size:var(--font-size-xs);margin-bottom:2px;">Sales Rep</div>
                <div style="font-weight:600;"><i class="bi bi-person"></i> <?= htmlspecialchars($purchase['sales_rep_name']) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Items -->
    <div class="section-title" style="margin-bottom:12px;">Daftar Barang (<?= $purchase['total_items'] ?>)</div>
    
    <?php foreach ($purchase['items'] as $item): ?>
    <div style="background:var(--surface-1);border-radius:var(--radius-md);padding:12px;margin-bottom:8px;border-left:3px solid var(--info);display:flex;align-items:center;gap:12px;">
        <div style="flex:1;">
            <div style="font-weight:600;font-size:0.9rem;margin-bottom:4px;line-height:1.3;"><?= htmlspecialchars($item['product_name']) ?></div>
            <div style="color:var(--text-muted);font-size:0.8rem;display:flex;gap:12px;">
                <span><?= $item['quantity'] ?> <?= htmlspecialchars($item['unit_name']) ?></span>
                <span>@ <?= Helper::rupiah($item['buy_price']) ?></span>
            </div>
        </div>
        <div style="font-weight:700;font-size:0.95rem;">
            <?= Helper::rupiah($item['total_price']) ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Summary -->
    <div style="background:var(--surface-1);border-radius:var(--radius-lg);padding:16px;margin-top:16px;border:1px solid var(--border-color);">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:var(--font-size-sm);">
            <span style="color:var(--text-muted);">Subtotal</span>
            <span><?= Helper::rupiah($purchase['total_amount']) ?></span>
        </div>
        <?php if ($purchase['discount_amount'] > 0): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:var(--font-size-sm);color:var(--danger);">
            <span>Diskon</span>
            <span>- <?= Helper::rupiah($purchase['discount_amount']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($purchase['ppn_amount'] > 0): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:var(--font-size-sm);">
            <span style="color:var(--text-muted);">PPN</span>
            <span>+ <?= Helper::rupiah($purchase['ppn_amount']) ?></span>
        </div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:1px dashed var(--border-color);font-size:var(--font-size-lg);font-weight:800;">
            <span>TOTAL</span>
            <span style="color:var(--primary);"><?= Helper::rupiah($purchase['grand_total']) ?></span>
        </div>
    </div>

    <!-- Actions -->
    <div style="margin-top:24px;">
        <button class="btn-outline-custom" style="width:100%;color:var(--danger);border-color:var(--danger);" onclick="deletePurchase(<?= $purchase['id'] ?>, '<?= htmlspecialchars($purchase['purchase_code']) ?>')">
            <i class="bi bi-trash"></i> Hapus Pembelian & Kembalikan Stok
        </button>
    </div>
</div>

<script>
async function deletePurchase(id, code) {
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
                    setTimeout(() => window.location.href = '<?= BASE_URL ?>purchases', 1500);
                    return true;
                }
            } catch(e) {
                // error is handled in utils.js api()
            }
            return false;
        }
    });
}
</script>
