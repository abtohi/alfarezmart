<?php
/**
 * Dashboard Summary & Statistik (Superadmin)
 *
 * @var string $month         e.g. '2026-05'
 * @var array  $salesAgg
 * @var array  $purchaseAgg
 * @var float  $avgMarkup
 * @var array  $dailySeries
 * @var array  $topProducts
 * @var array  $debtOut
 */
$revenue   = (float)($salesAgg['revenue'] ?? 0);
$profit    = (float)($salesAgg['gross_profit'] ?? 0);
$txCount   = (int)  ($salesAgg['tx_count'] ?? 0);
$pTotal    = (float)($purchaseAgg['p_total'] ?? 0);
$pCount    = (int)  ($purchaseAgg['p_count'] ?? 0);
$netProfit = $revenue - $pTotal;

$monthLabel = (function($m) {
    $names = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    [$y, $mm] = array_pad(explode('-', $m), 2, '');
    $i = (int)$mm; if ($i < 1 || $i > 12) return $m;
    return $names[$i] . ' ' . $y;
})($month);

// Find max revenue for chart scaling
$maxRev = 0;
foreach ($dailySeries as $d) { $maxRev = max($maxRev, (float)$d['rev']); }
if ($maxRev <= 0) $maxRev = 1;
?>
<div class="page-section" style="padding-bottom: 80px;">

    <!-- Header & Month Picker -->
    <div style="background:var(--gradient-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:20px; margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
                <h4 style="font-weight:700; font-size:var(--font-size-md); margin:0;">Summary &amp; Statistik</h4>
                <p style="font-size:var(--font-size-xs); color:var(--text-muted); margin:4px 0 0 0;">Periode: <?= htmlspecialchars($monthLabel) ?></p>
            </div>
            <div style="width:40px; height:40px; background:var(--info-bg); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--info);">
                <i class="bi bi-graph-up-arrow" style="font-size:1.2rem;"></i>
            </div>
        </div>
        <form method="GET" action="<?= BASE_URL ?>dashboard/summary" style="display:flex; gap:8px; align-items:center;">
            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>"
                   style="flex:1; background:var(--bg-primary); color:var(--text-primary); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:8px; font-size:var(--font-size-xs);">
            <button type="submit" class="btn-primary-custom" style="padding:8px 14px; border:none; border-radius:var(--radius-sm); cursor:pointer;">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </form>
    </div>

    <!-- KPI Grid -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
        <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px;">
            <div style="font-size:10px; color:var(--text-muted); margin-bottom:6px;"><i class="bi bi-cash-coin"></i> OMZET BULAN INI</div>
            <div style="font-weight:800; color:var(--success); font-size:var(--font-size-md);">Rp <?= number_format($revenue, 0, ',', '.') ?></div>
            <div style="font-size:10px; color:var(--text-muted); margin-top:4px;"><?= $txCount ?> transaksi</div>
        </div>
        <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px;">
            <div style="font-size:10px; color:var(--text-muted); margin-bottom:6px;"><i class="bi bi-cart-dash"></i> TOTAL BELANJA</div>
            <div style="font-weight:800; color:var(--warning); font-size:var(--font-size-md);">Rp <?= number_format($pTotal, 0, ',', '.') ?></div>
            <div style="font-size:10px; color:var(--text-muted); margin-top:4px;"><?= $pCount ?> faktur</div>
        </div>
        <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px;">
            <div style="font-size:10px; color:var(--text-muted); margin-bottom:6px;"><i class="bi bi-graph-up"></i> GROSS PROFIT</div>
            <div style="font-weight:800; color:var(--info); font-size:var(--font-size-md);">Rp <?= number_format($profit, 0, ',', '.') ?></div>
            <div style="font-size:10px; color:var(--text-muted); margin-top:4px;">Markup rata-rata: <?= number_format($avgMarkup, 1, ',', '.') ?>%</div>
        </div>
        <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px;">
            <div style="font-size:10px; color:var(--text-muted); margin-bottom:6px;"><i class="bi bi-wallet2"></i> NET (Omzet − Belanja)</div>
            <div style="font-weight:800; color:<?= $netProfit >= 0 ? 'var(--success)' : 'var(--primary)' ?>; font-size:var(--font-size-md);">Rp <?= number_format($netProfit, 0, ',', '.') ?></div>
            <div style="font-size:10px; color:var(--text-muted); margin-top:4px;">arus kas kotor</div>
        </div>
    </div>

    <!-- Outstanding Debt -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
        <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:12px;">
            <div style="font-size:10px; color:var(--text-muted); margin-bottom:4px;"><i class="bi bi-person-fill-exclamation"></i> PIUTANG PELANGGAN</div>
            <div style="font-weight:800; color:var(--info); font-size:var(--font-size-sm);">Rp <?= number_format($debtOut['customer'], 0, ',', '.') ?></div>
        </div>
        <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:12px;">
            <div style="font-size:10px; color:var(--text-muted); margin-bottom:4px;"><i class="bi bi-shop-window"></i> HUTANG TOKO</div>
            <div style="font-weight:800; color:var(--warning); font-size:var(--font-size-sm);">Rp <?= number_format($debtOut['shop'], 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- Daily Revenue Chart (CSS bars) -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px; margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div style="font-size:var(--font-size-sm); font-weight:700;"><i class="bi bi-bar-chart-line"></i> Omzet Harian</div>
            <div style="font-size:10px; color:var(--text-muted);">Max: Rp <?= number_format($maxRev, 0, ',', '.') ?></div>
        </div>
        <?php if (empty($dailySeries)): ?>
            <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:var(--font-size-xs);">
                Belum ada penjualan di bulan ini.
            </div>
        <?php else: ?>
            <div style="display:flex; align-items:flex-end; gap:3px; height:120px; padding:4px 0; border-bottom:1px solid var(--border-color); overflow-x:auto;">
                <?php foreach ($dailySeries as $d):
                    $h = max(2, (int)((((float)$d['rev']) / $maxRev) * 100));
                    $dayNum = (int)substr($d['d'], 8, 2);
                ?>
                    <div title="<?= $d['d'] ?>: Rp <?= number_format($d['rev'], 0, ',', '.') ?>"
                         style="flex:1; min-width:8px; height:<?= $h ?>%; background:linear-gradient(180deg, var(--info), var(--primary)); border-radius:2px 2px 0 0;"></div>
                <?php endforeach; ?>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:9px; color:var(--text-muted);">
                <span><?= (int)substr($dailySeries[0]['d'], 8, 2) ?></span>
                <span><?= (int)substr(end($dailySeries)['d'], 8, 2) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Top 10 Products -->
    <div style="background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px;">
        <div style="font-size:var(--font-size-sm); font-weight:700; margin-bottom:12px;">
            <i class="bi bi-trophy"></i> Top 10 Produk Laris
        </div>
        <?php if (empty($topProducts)): ?>
            <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:var(--font-size-xs);">
                Belum ada produk terjual di bulan ini.
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <?php foreach ($topProducts as $i => $tp): ?>
                    <div style="display:flex; align-items:center; gap:10px; padding:8px; background:var(--bg-primary); border-radius:var(--radius-sm);">
                        <div style="width:24px; height:24px; border-radius:50%; background:var(--danger-bg); color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:10px;">
                            <?= $i + 1 ?>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:var(--font-size-xs); font-weight:700; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?= htmlspecialchars($tp['name']) ?>
                            </div>
                            <div style="font-size:10px; color:var(--text-muted);">
                                <?= number_format((float)$tp['qty_sold'], 0, ',', '.') ?> terjual
                                · Profit Rp <?= number_format((float)$tp['profit'], 0, ',', '.') ?>
                            </div>
                        </div>
                        <div style="font-size:11px; font-weight:700; color:var(--success); white-space:nowrap;">
                            Rp <?= number_format((float)$tp['revenue'], 0, ',', '.') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
