<?php
/**
 * @var array $sale
 * @var array $storeSettings
 */
?>
<!-- Sales Detail View -->
<div class="page-section">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="<?= BASE_URL ?>sales" style="color:var(--text-primary);"><i class="bi bi-arrow-left"></i></a>
            <h2 style="font-size: var(--font-size-lg); font-weight:700;">Detail Transaksi</h2>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="button" id="btnConnectPrinter" onclick="connectPrinter()" class="btn-outline-custom" style="padding:6px 12px; font-size:var(--font-size-xs);">
                <i class="bi bi-bluetooth"></i> Printer
            </button>
            <button type="button" onclick="printReceipt()" class="btn-primary-custom" style="padding:6px 12px; font-size:var(--font-size-xs);">
                <i class="bi bi-printer"></i> Cetak Struk
            </button>
        </div>
    </div>

    <div style="background:var(--surface-1); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px; border:1px solid var(--border-color);">
        <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
            <div style="font-weight:700; color:var(--text-primary);"><?= htmlspecialchars($sale['invoice_number']) ?></div>
            <div style="font-size:var(--font-size-xs); color:var(--text-muted);"><?= Helper::formatDate($sale['created_at']) ?></div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:var(--font-size-sm); color:var(--text-secondary); margin-bottom:16px;">
            <div>
                <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Pelanggan</div>
                <div><?= htmlspecialchars($sale['customer_name'] ?? 'Pelanggan Umum') ?></div>
            </div>
            <div>
                <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Mode</div>
                <div><span class="badge-custom badge-<?= $sale['sale_mode'] == 'retail' ? 'info' : 'warning' ?>"><?= ucfirst($sale['sale_mode']) ?></span></div>
            </div>
            <div>
                <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Metode Bayar</div>
                <div><?= htmlspecialchars($sale['payment_method']) ?></div>
            </div>
            <div>
                <div style="font-size:var(--font-size-xs); color:var(--text-muted);">Status</div>
                <div><span class="badge-custom badge-success"><?= htmlspecialchars($sale['payment_status']) ?></span></div>
            </div>
        </div>
        
        <?php if (!empty($sale['notes'])): ?>
        <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px;">Catatan</div>
        <div style="font-size:var(--font-size-sm); color:var(--text-secondary); background:var(--surface-2); padding:8px; border-radius:var(--radius-sm);">
            <?= htmlspecialchars($sale['notes']) ?>
        </div>
        <?php endif; ?>
    </div>

    <h3 style="font-size:var(--font-size-md); font-weight:600; margin-bottom:12px;">Daftar Item</h3>
    <div style="background:var(--surface-1); border-radius:var(--radius-lg); overflow:hidden; border:1px solid var(--border-color); margin-bottom:16px;">
        <?php foreach ($sale['items'] as $item): ?>
            <div style="padding:12px 16px; border-bottom:1px solid var(--border-color);">
                <div style="font-weight:600; font-size:var(--font-size-sm); color:var(--text-primary); margin-bottom:4px;">
                    <?= htmlspecialchars($item['invoice_name'] ?? $item['full_name'] ?? 'Item') ?>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:var(--font-size-xs); color:var(--text-secondary);">
                        <?= (int)$item['quantity'] ?> <?= htmlspecialchars($item['unit_name'] ?? 'Pcs') ?> x <?= Helper::rupiah($item['unit_price']) ?>
                    </div>
                    <div style="font-weight:700; color:var(--text-primary); font-size:var(--font-size-sm);">
                        <?= Helper::rupiah($item['total_price']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div style="padding:16px; background:var(--surface-2); display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:600; font-size:var(--font-size-sm); color:var(--text-secondary);">Total Harga</div>
            <div style="font-weight:700; font-size:var(--font-size-lg); color:var(--primary);"><?= Helper::rupiah($sale['total_amount']) ?></div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>public/js/printer.js"></script>
<script>
    const STORE_SETTINGS = <?= json_encode($storeSettings) ?>;
    const SALE_DATA = <?= json_encode($sale) ?>;

    // Use shared instance if available (from layout), else create local one
    const tp = window.thermalPrinter || new ThermalPrinter();
    tp.setStoreSettings(STORE_SETTINGS);

    // ── UI helpers ──────────────────────────────────────────────────────────
    function updatePrinterBtn() {
        const btn = document.getElementById('btnConnectPrinter');
        if (!btn) return;

        if (tp.isConnected()) {
            btn.innerHTML = `<i class="bi bi-bluetooth-fill"></i> ${tp.device?.name || 'Terhubung'}`;
            btn.style.background = 'var(--success-bg)';
            btn.style.color = 'var(--success)';
            btn.style.borderColor = 'var(--success)';
            btn.title = 'Klik untuk putuskan koneksi';
        } else if (tp.hasSavedDevice()) {
            btn.innerHTML = `<i class="bi bi-bluetooth"></i> ${tp.lastConnectedDevice?.name || 'Printer Tersimpan'}`;
            btn.style.background = 'var(--warning-bg)';
            btn.style.color = 'var(--warning)';
            btn.style.borderColor = 'var(--warning)';
            btn.title = 'Klik untuk hubungkan ke printer tersimpan';
        } else {
            btn.innerHTML = '<i class="bi bi-bluetooth"></i> Hubungkan Printer';
            btn.style.background = '';
            btn.style.color = '';
            btn.style.borderColor = '';
            btn.title = 'Hubungkan printer Bluetooth thermal';
        }
    }

    // ── Auto-reconnect on page load ─────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        updatePrinterBtn();

        if (!tp.isIOS && tp.hasBluetoothAPI && (tp.device || tp.hasSavedDevice()) && !tp.isConnected()) {
            const btn = document.getElementById('btnConnectPrinter');
            if (btn) {
                btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menghubungkan...';
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
                // Try silent auto-reconnect first (no dialog)
                const ok = (tp.device || tp.hasSavedDevice()) ? await tp.tryAutoReconnect() : false;
                if (!ok) {
                    // Show Bluetooth picker as fallback
                    await tp.connect();
                }
                showToast(`Printer terhubung: ${tp.device?.name || 'Bluetooth'}`, 'success');
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
        const cartData = SALE_DATA.items.map(i => ({
            name: i.invoice_name || i.full_name || 'Item',
            print_name: i.invoice_name || i.full_name || 'Item',
            quantity: parseFloat(i.quantity),
            unit_price: parseFloat(i.unit_price),
            total: parseFloat(i.total_price),
            unit_name: i.unit_name || 'pcs'
        }));

        const btnPrint = document.querySelector('[onclick="printReceipt()"]');
        const prevHTML = btnPrint ? btnPrint.innerHTML : '';
        if (btnPrint) { btnPrint.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Mencetak...'; btnPrint.disabled = true; }

        try {
            if (tp.isIOS || !tp.hasBluetoothAPI) {
                // iOS / no Bluetooth API → fallback ke browser print
                await tp.printBrowser(cartData, parseFloat(SALE_DATA.total_amount), SALE_DATA.invoice_number, {
                    paymentMethod: SALE_DATA.payment_method
                });
            } else {
                // Try to use Bluetooth thermal printer
                if (!tp.isConnected()) {
                    // 1. Try silent auto-reconnect
                    const ok = (tp.device || tp.hasSavedDevice()) ? await tp.tryAutoReconnect() : false;
                    if (!ok) {
                        // 2. Show picker if needed
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
            if (btnPrint) { btnPrint.innerHTML = prevHTML; btnPrint.disabled = false; }
        }
    }
</script>

