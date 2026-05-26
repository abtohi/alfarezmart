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
            <div class="product-card sale-card" data-sale-id="<?= $s['id'] ?>" style="align-items:flex-start; cursor:pointer; position:relative; transition: all 0.2s;">
                <!-- Selection checkbox (hidden by default) -->
                <div class="sale-check" style="display:none; position:absolute; left:8px; top:50%; transform:translateY(-50%); z-index:2;">
                    <div style="width:24px;height:24px;border-radius:var(--radius-full);border:2px solid var(--primary);display:flex;align-items:center;justify-content:center;background:var(--surface-1);transition:all 0.2s;">
                        <i class="bi bi-check-lg" style="font-size:14px;color:var(--primary);display:none;"></i>
                    </div>
                </div>
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

<input type="hidden" id="csrfToken" value="<?= $csrfToken ?>">

<!-- Bulk Action Bar (fixed bottom) -->
<div id="bulkActionBar" style="display:none;position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:480px;z-index:90;padding:0 12px 12px;">
    <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 -4px 24px rgba(0,0,0,0.25);backdrop-filter:blur(12px);">
        <div style="display:flex;align-items:center;gap:10px;">
            <button type="button" onclick="exitSelectionMode()" style="background:none;border:none;color:var(--text-primary);cursor:pointer;padding:4px;font-size:1.2rem;"><i class="bi bi-x-lg"></i></button>
            <span id="bulkSelectedCount" style="font-weight:700;font-size:var(--font-size-sm);color:var(--text-primary);">0 dipilih</span>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" onclick="selectAllSales()" style="padding:8px 14px;border-radius:var(--radius-md);border:1px solid var(--border-color);background:var(--surface-2);color:var(--text-primary);cursor:pointer;font-size:var(--font-size-xs);display:flex;align-items:center;gap:4px;">
                <i class="bi bi-check-all"></i> Semua
            </button>
            <button type="button" id="btnBulkDelete" onclick="bulkDeleteSelected()" style="padding:8px 14px;border-radius:var(--radius-md);border:none;background:var(--danger);color:white;cursor:pointer;font-size:var(--font-size-xs);display:flex;align-items:center;gap:4px;font-weight:600;">
                <i class="bi bi-trash"></i> Hapus
            </button>
        </div>
    </div>
</div>

<script>
// ===== Selection Mode =====
let selectionMode = false;
let selectedIds = new Set();
let longPressTimer = null;
const LONG_PRESS_MS = 500;

function initSelectionMode() {
    document.querySelectorAll('.sale-card').forEach(card => {
        const saleId = card.dataset.saleId;

        // Long-press (touch)
        card.addEventListener('touchstart', (e) => {
            longPressTimer = setTimeout(() => {
                e.preventDefault();
                if (!selectionMode) enterSelectionMode();
                toggleSelect(saleId);
                // Haptic feedback
                if (navigator.vibrate) navigator.vibrate(30);
            }, LONG_PRESS_MS);
        }, { passive: false });

        card.addEventListener('touchend', () => clearTimeout(longPressTimer));
        card.addEventListener('touchmove', () => clearTimeout(longPressTimer));

        // Right-click (desktop)
        card.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            if (!selectionMode) enterSelectionMode();
            toggleSelect(saleId);
        });

        // Normal click
        card.addEventListener('click', (e) => {
            if (selectionMode) {
                e.preventDefault();
                e.stopPropagation();
                toggleSelect(saleId);
            } else {
                window.location.href = `${BASE_URL}sales/${saleId}`;
            }
        });
    });
}

function enterSelectionMode() {
    selectionMode = true;
    document.querySelectorAll('.sale-check').forEach(el => el.style.display = 'flex');
    document.querySelectorAll('.sale-card .product-icon').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.sale-card').forEach(el => el.style.paddingLeft = '44px');
    document.getElementById('bulkActionBar').style.display = 'block';
}

function exitSelectionMode() {
    selectionMode = false;
    selectedIds.clear();
    document.querySelectorAll('.sale-check').forEach(el => {
        el.style.display = 'none';
        el.querySelector('div').style.background = 'var(--surface-1)';
        el.querySelector('i').style.display = 'none';
    });
    document.querySelectorAll('.sale-card .product-icon').forEach(el => el.style.display = '');
    document.querySelectorAll('.sale-card').forEach(el => {
        el.style.paddingLeft = '';
        el.style.background = '';
    });
    document.getElementById('bulkActionBar').style.display = 'none';
}

function toggleSelect(id) {
    const card = document.querySelector(`.sale-card[data-sale-id="${id}"]`);
    if (!card) return;
    const check = card.querySelector('.sale-check');
    const checkDiv = check.querySelector('div');
    const checkIcon = check.querySelector('i');

    if (selectedIds.has(id)) {
        selectedIds.delete(id);
        checkDiv.style.background = 'var(--surface-1)';
        checkIcon.style.display = 'none';
        card.style.background = '';
    } else {
        selectedIds.add(id);
        checkDiv.style.background = 'var(--primary)';
        checkIcon.style.display = 'block';
        checkIcon.style.color = 'white';
        card.style.background = 'var(--primary-bg)';
    }

    document.getElementById('bulkSelectedCount').textContent = `${selectedIds.size} dipilih`;

    if (selectedIds.size === 0) exitSelectionMode();
}

function selectAllSales() {
    document.querySelectorAll('.sale-card').forEach(card => {
        const id = card.dataset.saleId;
        if (!selectedIds.has(id)) toggleSelect(id);
    });
}

async function bulkDeleteSelected() {
    if (selectedIds.size === 0) return;

    const count = selectedIds.size;
    
    AppModal.show({
        title: 'Hapus Transaksi',
        subtitle: `${count} transaksi dipilih`,
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: `
            <div style="text-align:center; padding:12px 0;">
                <p style="font-size:var(--font-size-md); font-weight:600; color:var(--text-primary); margin-bottom:8px;">
                    Yakin ingin menghapus ${count} transaksi?
                </p>
                <p style="font-size:var(--font-size-sm); color:var(--text-muted); margin-bottom:16px;">
                    Stok produk yang terjual akan dikembalikan. Tindakan ini tidak bisa dibatalkan.
                </p>
            </div>
        `,
        submitText: 'Ya, Hapus',
        submitClass: 'btn-danger',
        cancelText: 'Batal',
        onSubmit: async () => {
            const btn = document.getElementById('appModalSubmitBtn') || document.getElementById('btnBulkDelete');
            const prevText = btn.innerHTML;
            btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menghapus...';
            btn.disabled = true;

            try {
                const csrf = document.getElementById('csrfToken')?.value || '';
                const res = await fetch(`${BASE_URL}api/sales/bulk-delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                    body: JSON.stringify({ csrf_token: csrf, ids: Array.from(selectedIds).map(Number) })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`✅ ${result.deleted} transaksi berhasil dihapus`, 'success');
                    setTimeout(() => window.location.reload(), 800);
                    return true;
                } else {
                    throw new Error(result.error || 'Gagal menghapus');
                }
            } catch (err) {
                showToast('Error: ' + err.message, 'error');
                btn.innerHTML = prevText;
                btn.disabled = false;
                return false;
            }
        }
    });
}

// ===== Printer (unchanged) =====
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
        tp.disconnect();
        tp.clearLastDevice();
        showToast('Printer diputuskan', 'info');
        updatePrinterUI();
        return;
    }

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
        await tp.connect();
        showToast(`Printer terhubung: ${tp.device?.name || 'Bluetooth'}`, 'success');
    } catch (e) {
        showToast(e.message || 'Gagal menghubungkan printer', 'error');
    }

    btn.innerHTML = prevHTML;
    btn.disabled = false;
    updatePrinterUI();
}

// Init on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    initSelectionMode();
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
