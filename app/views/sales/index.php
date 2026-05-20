<!-- Sales Index View -->
<div class="page-section">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="font-size: var(--font-size-lg); font-weight:700;">Riwayat Penjualan</h2>
        <div style="display:flex;gap:8px;align-items:center;">
            <button type="button" id="btnSalesPrinter" onclick="toggleSalesPrinter()" title="Hubungkan Printer Thermal"
                    style="background:var(--surface-1);color:var(--text-muted);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:8px 12px;cursor:pointer;font-size:1rem;display:flex;align-items:center;gap:6px;font-size:var(--font-size-xs);transition:all 0.3s;">
                <i class="bi bi-printer" id="salesPrinterIcon"></i>
                <span id="salesPrinterLabel" style="display:none;"></span>
            </button>
            <a href="<?= BASE_URL ?>sales/pos" class="btn-primary-custom" style="padding:8px 16px;font-size:var(--font-size-xs);text-decoration:none;color:white;">
                <i class="bi bi-cart"></i> Kasir POS
            </a>
        </div>
    </div>

    <?php if (empty($sales['data'])): ?>
        <div class="empty-state">
            <i class="bi bi-receipt"></i>
            <h3>Belum Ada Transaksi</h3>
            <p>Mulai penjualan melalui kasir</p>
            <a href="<?= BASE_URL ?>sales/pos" class="btn-primary-custom" style="margin-top:16px;text-decoration:none;color:white;">Buka Kasir</a>
        </div>
    <?php else: ?>
        <div style="font-size:var(--font-size-sm); color:var(--text-muted); margin-bottom:12px;">
            Total <?= $sales['total'] ?> transaksi
        </div>
        
        <?php foreach ($sales['data'] as $s): ?>
            <div class="product-card" style="align-items:flex-start; cursor:pointer;" onclick="window.location.href='/sales/<?= $s['id'] ?>'">
                <div class="product-icon" style="background:var(--primary-bg);color:var(--primary);">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="product-info">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="font-weight:700; font-size:var(--font-size-sm);"><?= htmlspecialchars($s['invoice_number']) ?></span>
                        <span style="font-size:var(--font-size-xs); color:var(--text-muted);"><?= Helper::formatDate($s['created_at']) ?></span>
                    </div>
                    <div style="font-size:var(--font-size-xs); color:var(--text-secondary); margin-bottom:6px; display:flex; justify-content:space-between;">
                        <span><i class="bi bi-person"></i> <?= htmlspecialchars($s['customer_name'] ?? 'Pelanggan Umum') ?></span>
                        <span class="badge-custom badge-<?= $s['sale_mode'] == 'retail' ? 'info' : 'warning' ?>"><?= ucfirst($s['sale_mode']) ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                        <span style="font-size:var(--font-size-xs); color:var(--text-muted);"><?= $s['total_items'] ?? 0 ?> item</span>
                        <span style="font-weight:700; color:var(--primary); font-size:var(--font-size-base);"><?= Helper::rupiah($s['total_amount']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Pagination -->
        <?php if ($sales['total_pages'] > 1): ?>
            <?php
                $totalPages = $sales['total_pages'];
                $currentPage = $sales['page'] ?? 1;
                $range = 2;
                $start = max(1, $currentPage - $range);
                $end = min($totalPages, $currentPage + $range);
            ?>
            <div style="display:flex;justify-content:center;align-items:center;gap:4px;margin-top:20px;flex-wrap:wrap;">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= BASE_URL ?>sales?page=<?= $currentPage - 1 ?>" class="chip" style="padding:6px 10px;"><i class="bi bi-chevron-left"></i></a>
                <?php else: ?>
                    <span class="chip" style="padding:6px 10px;opacity:0.35;pointer-events:none;"><i class="bi bi-chevron-left"></i></span>
                <?php endif; ?>
                <?php if ($start > 1): ?>
                    <a href="<?= BASE_URL ?>sales?page=1" class="chip">1</a>
                    <?php if ($start > 2): ?><span style="color:var(--text-muted);font-size:12px;padding:0 2px;">…</span><?php endif; ?>
                <?php endif; ?>
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a href="<?= BASE_URL ?>sales?page=<?= $i ?>" class="chip <?= $currentPage == $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?><span style="color:var(--text-muted);font-size:12px;padding:0 2px;">…</span><?php endif; ?>
                    <a href="<?= BASE_URL ?>sales?page=<?= $totalPages ?>" class="chip"><?= $totalPages ?></a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= BASE_URL ?>sales?page=<?= $currentPage + 1 ?>" class="chip" style="padding:6px 10px;"><i class="bi bi-chevron-right"></i></a>
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
// Point 9: Thermal Printer Management
function updatePrinterUI() {
    const btn = document.getElementById('btnSalesPrinter');
    const icon = document.getElementById('salesPrinterIcon');
    const label = document.getElementById('salesPrinterLabel');
    const tp = (typeof thermalPrinter !== 'undefined') ? thermalPrinter : null;
    if (!btn || !tp) return;

    if (tp.isConnected()) {
        btn.style.background = 'var(--success-bg)';
        btn.style.color = 'var(--success)';
        btn.style.borderColor = 'var(--success)';
        icon.className = 'bi bi-printer-fill';
        label.textContent = tp.device?.name || 'Terhubung';
        label.style.display = 'inline';
        btn.title = 'Printer terhubung - Klik untuk putuskan';
    } else if (tp.hasSavedDevice()) {
        btn.style.background = 'var(--warning-bg)';
        btn.style.color = 'var(--warning)';
        btn.style.borderColor = 'var(--warning)';
        icon.className = 'bi bi-printer';
        label.textContent = 'Tersimpan';
        label.style.display = 'inline';
        btn.title = 'Printer tersimpan - Klik untuk hubungkan';
    } else {
        btn.style.background = 'var(--surface-1)';
        btn.style.color = 'var(--text-muted)';
        btn.style.borderColor = 'var(--border-color)';
        icon.className = 'bi bi-printer';
        label.style.display = 'none';
        btn.title = 'Hubungkan Printer Thermal Bluetooth';
    }
}

async function toggleSalesPrinter() {
    const tp = (typeof thermalPrinter !== 'undefined') ? thermalPrinter : null;
    if (!tp) {
        showToast('Module printer tidak tersedia', 'error');
        return;
    }

    if (tp.isIOS || !tp.hasBluetoothAPI) {
        showToast('Perangkat ini menggunakan cetak via Browser/AirPrint. Hubungkan printer saat checkout di POS.', 'info');
        return;
    }

    if (tp.isConnected()) {
        // Disconnect
        tp.disconnect();
        tp.clearLastDevice();
        showToast('Printer diputuskan', 'info');
        updatePrinterUI();
        return;
    }

    // Try auto-reconnect first
    const btn = document.getElementById('btnSalesPrinter');
    const prevHTML = btn.innerHTML;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i>';
    btn.disabled = true;

    try {
        if (tp.device || tp.hasSavedDevice()) {
            const ok = await tp.tryAutoReconnect();
            if (ok) {
                showToast(`Printer terhubung: ${tp.device?.name || 'Bluetooth'}`, 'success');
                updatePrinterUI();
                btn.disabled = false;
                return;
            }
        }
        // Manual connect with picker
        await tp.connect();
        showToast(`Printer terhubung: ${tp.device?.name || 'Bluetooth'}`, 'success');
    } catch (e) {
        showToast(e.message || 'Gagal menghubungkan printer', 'error');
    }

    btn.innerHTML = prevHTML;
    btn.disabled = false;
    updatePrinterUI();
}

// Auto-check printer status on page load
document.addEventListener('DOMContentLoaded', () => {
    updatePrinterUI();
    const tp = (typeof thermalPrinter !== 'undefined') ? thermalPrinter : null;
    if (tp && !tp.isConnected() && tp.hasSavedDevice() && tp.hasBluetoothAPI && !tp.isIOS) {
        tp.tryAutoReconnect().then(ok => {
            if (ok) showToast(`Printer auto-connected: ${tp.device?.name}`, 'success');
            updatePrinterUI();
        }).catch(() => updatePrinterUI());
    }
});
</script>
