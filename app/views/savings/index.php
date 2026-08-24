<?php
/**
 * Savings & Financial Goals View
 *
 * @var array $currentUser
 * @var array $summary
 * @var array $goals
 * @var string $csrfToken
 */
$csrfToken = $csrfToken ?? ($this->security ? $this->security->getCSRFToken() : '');
?>

<style>
/* ===== SAVINGS & GOALS STYLES ===== */
.savings-container {
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 90px;
}

.savings-header-card {
    background: var(--gradient-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.savings-kpi-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

@media (min-width: 992px) {
    .savings-kpi-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
}

.savings-kpi-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    transition: transform 0.2s, border-color 0.2s;
}

.savings-kpi-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
}

.savings-dist-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    margin-bottom: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
}

.dist-bar-wrapper {
    height: 12px;
    border-radius: 6px;
    background: var(--surface-2);
    overflow: hidden;
    display: flex;
    margin: 12px 0;
}

.dist-bar-segment {
    height: 100%;
    transition: width 0.4s ease;
}

.dist-pills-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 12px;
}

@media (min-width: 768px) {
    .dist-pills-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.dist-pill-item {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.savings-goals-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-top: 16px;
}

@media (min-width: 768px) {
    .savings-goals-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1200px) {
    .savings-goals-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.goal-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
    position: relative;
    overflow: hidden;
}

.goal-card:hover {
    border-color: var(--primary);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.goal-card-top-accent {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.goal-icon-badge {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.goal-progress-bar {
    height: 9px;
    background: var(--surface-2);
    border-radius: 6px;
    overflow: hidden;
    margin: 12px 0 8px 0;
}

.goal-progress-fill {
    height: 100%;
    border-radius: 6px;
    transition: width 0.5s ease;
}

.alloc-pill {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.alloc-pill-tag {
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.alloc-card-item {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 14px 16px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    transition: border-color 0.2s;
}

.alloc-card-item:hover {
    border-color: var(--primary);
}

.timeline-item {
    position: relative;
    padding-left: 24px;
    padding-bottom: 16px;
    border-left: 2px solid var(--border-color);
}

.timeline-item:last-child {
    border-left-color: transparent;
    padding-bottom: 0;
}

.timeline-dot {
    position: absolute;
    left: -7px;
    top: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--primary);
}

.filter-btn-group .btn-filter {
    padding: 6px 14px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    background: var(--surface-1);
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-btn-group .btn-filter.active {
    background: var(--primary);
    color: #ffffff;
    border-color: var(--primary);
}
</style>

<div class="page-section savings-container">
    <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">

    <!-- Header Card -->
    <div class="savings-header-card">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(99,102,241,0.2)); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(16,185,129,0.25);">
                <i class="bi bi-piggy-bank-fill"></i>
            </div>
            <div>
                <h4 style="font-weight: 800; font-size: 1.15rem; margin: 0; color: var(--text-primary);">Savings &amp; Target Keuangan</h4>
                <p style="font-size: var(--font-size-xs); color: var(--text-muted); margin: 3px 0 0 0;">
                    Pantau tabungan, investasi, uang toko berputar &amp; alokasi dana per target
                </p>
            </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>" class="btn-primary-custom" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: var(--radius-md); font-size: var(--font-size-xs); text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <button class="btn-primary-custom" onclick="openAddGoalModal()" style="padding: 8px 16px; border-radius: var(--radius-md); font-size: var(--font-size-xs); display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 10px rgba(230,57,70,0.3);">
                <i class="bi bi-plus-circle-fill"></i> Tambah Goal Baru
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="savings-kpi-grid" id="kpiGridContainer">
        <!-- 1. Total Terkumpul -->
        <div class="savings-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Terkumpul</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16,185,129,0.12); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div id="statTotalCollected" style="font-size: 1.25rem; font-weight: 800; color: var(--success); line-height: 1.2;">
                Rp <?= number_format($summary['total_collected'] ?? 0, 0, ',', '.') ?>
            </div>
            <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">
                Akumulasi seluruh alokasi
            </div>
        </div>

        <!-- 2. Total Target -->
        <div class="savings-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Target</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(99,102,241,0.12); color: #818cf8; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                    <i class="bi bi-bullseye"></i>
                </div>
            </div>
            <div id="statTotalTarget" style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); line-height: 1.2;">
                Rp <?= number_format($summary['total_target'] ?? 0, 0, ',', '.') ?>
            </div>
            <div id="statTotalRemaining" style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">
                Sisa: <strong style="color: var(--primary);">Rp <?= number_format($summary['total_remaining'] ?? 0, 0, ',', '.') ?></strong>
            </div>
        </div>

        <!-- 3. Overall Progress -->
        <div class="savings-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Pencapaian (Progress)</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245,158,11,0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <div id="statOverallProgress" style="font-size: 1.25rem; font-weight: 800; color: #f59e0b; line-height: 1.2;">
                <?= $summary['overall_progress'] ?? 0 ?>%
            </div>
            <div class="goal-progress-bar" style="margin: 6px 0 0 0; height: 6px;">
                <div id="statProgressBar" class="goal-progress-fill" style="width: <?= min(100, $summary['overall_progress'] ?? 0) ?>%; background: linear-gradient(90deg, #f59e0b, #10b981);"></div>
            </div>
        </div>

        <!-- 4. Goals Count -->
        <div class="savings-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Goals / Target</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59,130,246,0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                    <i class="bi bi-flag-fill"></i>
                </div>
            </div>
            <div id="statGoalsCount" style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); line-height: 1.2;">
                <?= $summary['total_goals'] ?? 0 ?> Target
            </div>
            <div id="statGoalsSub" style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">
                <span style="color: var(--success); font-weight: 700;"><?= $summary['achieved_goals'] ?? 0 ?> Tercapai</span> &middot; <?= $summary['in_progress_goals'] ?? 0 ?> Berjalan
            </div>
        </div>
    </div>

    <!-- Distribusi Klasifikasi Penempatan Uang (Toko, Investasi, Bank, Piutang) -->
    <div class="savings-dist-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div>
                <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-pie-chart-fill" style="color: #6366f1;"></i>
                    <span>Klasifikasi Penempatan Dana (Grouping Uang)</span>
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                    Sebaran uang tabungan di Toko (uang berputar), Bibit (investasi), SeaBank/Bank, dll
                </div>
            </div>
            <span id="totalAllocationsBadge" class="badge-custom badge-primary" style="font-size: 11px;">
                <?= count($summary['type_breakdown'] ?? []) ?> Kategori Akun
            </span>
        </div>

        <!-- Distribution Multi-Segment Bar -->
        <div class="dist-bar-wrapper" id="distBarContainer">
            <?php 
            $palette = ['#10b981', '#6366f1', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6'];
            $typeBreakdown = $summary['type_breakdown'] ?? [];
            if (empty($typeBreakdown)): ?>
                <div class="dist-bar-segment" style="width: 100%; background: var(--surface-3);"></div>
            <?php else: 
                foreach ($typeBreakdown as $idx => $item):
                    $bg = $palette[$idx % count($palette)];
            ?>
                <div class="dist-bar-segment" style="width: <?= max(2, $item['percentage']) ?>%; background: <?= $bg ?>;" title="<?= htmlspecialchars($item['account_type']) ?>: Rp <?= number_format($item['total_amount'], 0, ',', '.') ?> (<?= $item['percentage'] ?>%)"></div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Distribution Pills Grid -->
        <div class="dist-pills-grid" id="distPillsContainer">
            <?php if (empty($typeBreakdown)): ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); font-size: 11px; padding: 10px 0;">
                    Belum ada pos penempatan dana. Buat goal dan tambahkan alokasi uang (Toko, Bibit, Bank).
                </div>
            <?php else:
                foreach ($typeBreakdown as $idx => $item):
                    $bg = $palette[$idx % count($palette)];
                    $iconClass = 'bi-wallet2';
                    if (stripos($item['account_type'], 'toko') !== false || stripos($item['account_type'], 'kas') !== false) $iconClass = 'bi-shop';
                    elseif (stripos($item['account_type'], 'investasi') !== false) $iconClass = 'bi-graph-up-arrow';
                    elseif (stripos($item['account_type'], 'bank') !== false) $iconClass = 'bi-bank';
                    elseif (stripos($item['account_type'], 'piutang') !== false) $iconClass = 'bi-person-lines-fill';
            ?>
                <div class="dist-pill-item">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: <?= $bg ?>22; color: <?= $bg ?>; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                        <i class="bi <?= $iconClass ?>"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 10px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 700; text-transform: uppercase;">
                            <?= htmlspecialchars($item['account_type']) ?>
                        </div>
                        <div style="font-size: 12px; font-weight: 800; color: var(--text-primary);">
                            Rp <?= number_format($item['total_amount'], 0, ',', '.') ?>
                        </div>
                        <div style="font-size: 9px; color: <?= $bg ?>; font-weight: 700;">
                            <?= $item['percentage'] ?>% &middot; <?= $item['item_count'] ?> pos
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Section Title & Filter -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <div class="section-title" style="margin-bottom: 0;">Daftar Target &amp; Goals Tabungan</div>
        <div class="filter-btn-group" style="display: flex; gap: 6px;">
            <button class="btn-filter active" onclick="filterGoals('all', this)">Semua</button>
            <button class="btn-filter" onclick="filterGoals('in_progress', this)">Sedang Berjalan</button>
            <button class="btn-filter" onclick="filterGoals('achieved', this)">Tercapai</button>
        </div>
    </div>

    <!-- Goals Grid Container -->
    <div id="goalsGridContainer" class="savings-goals-grid">
        <!-- Rendered by JS / PHP initial load -->
        <div class="elegant-loader" style="grid-column: 1 / -1; margin: 30px auto;">
            <div class="dot"></div><div class="dot"></div><div class="dot"></div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 1: DETAIL GOAL & KELOLA POS ALOKASI (GROUPING)    -->
<!-- ======================================================== -->
<div class="modal fade" id="modalGoalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden;">
            
            <!-- Modal Header with Goal Banner -->
            <div id="detailGoalBanner" style="padding: 20px 24px; background: var(--gradient-card); border-bottom: 1px solid var(--border-color); position: relative;">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="position: absolute; right: 20px; top: 20px;"></button>
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div id="detailGoalIconBadge" class="goal-icon-badge" style="background: rgba(99,102,241,0.15); color: #818cf8;">
                        <i id="detailGoalIcon" class="bi bi-piggy-bank-fill"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <h4 id="detailGoalName" style="font-weight: 800; font-size: 1.2rem; margin: 0; color: var(--text-primary);">Tabungan Mobil</h4>
                            <span id="detailGoalCategory" class="badge-custom badge-primary" style="font-size: 10px;">Kendaraan</span>
                            <span id="detailGoalStatusBadge" class="badge-custom" style="font-size: 10px;">Sedang Berjalan</span>
                        </div>
                        <p id="detailGoalDesc" style="font-size: var(--font-size-xs); color: var(--text-muted); margin: 4px 0 0 0;">Catatan target</p>
                    </div>
                </div>

                <!-- Goal Stats Strip -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                    <div>
                        <span style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Terkumpul</span>
                        <div id="detailCollectedAmount" style="font-size: 1.15rem; font-weight: 800; color: var(--success);">Rp 0</div>
                    </div>
                    <div>
                        <span style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Target Nominal</span>
                        <div id="detailTargetAmount" style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary);">Rp 0</div>
                    </div>
                    <div>
                        <span style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Sisa Kekurangan</span>
                        <div id="detailRemainingAmount" style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">Rp 0</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="goal-progress-bar" style="margin-top: 12px; height: 8px;">
                    <div id="detailProgressFill" class="goal-progress-fill" style="width: 0%; background: var(--success);"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 10px; color: var(--text-muted); margin-top: 4px;">
                    <span id="detailProgressText">Progress: 0%</span>
                    <span id="detailDeadlineText"><i class="bi bi-calendar-event"></i> -</span>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding: 20px 24px; max-height: 65vh; overflow-y: auto;">
                
                <!-- Nav Tabs (Pos Penempatan vs Riwayat Mutasi) -->
                <ul class="nav nav-pills mb-3" style="gap: 8px;">
                    <li class="nav-item">
                        <button class="btn-filter active" id="tabAllocationsBtn" onclick="switchDetailTab('allocations', this)" style="padding: 7px 16px;">
                            <i class="bi bi-grid-fill me-1"></i> Pos Penempatan Dana
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="btn-filter" id="tabLogsBtn" onclick="switchDetailTab('logs', this)" style="padding: 7px 16px;">
                            <i class="bi bi-clock-history me-1"></i> Riwayat Mutasi
                        </button>
                    </li>
                </ul>

                <!-- TAB 1: POS ALOKASI (GROUPING UANG) -->
                <div id="detailTabAllocations">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="font-size: 12px; font-weight: 700; color: var(--text-primary);">
                            Rincian Pembagian Uang Goal Ini:
                        </div>
                        <button class="btn-primary-custom" onclick="openAddAllocationModal()" style="padding: 6px 12px; border-radius: var(--radius-sm); font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="bi bi-plus-lg"></i> Tambah Pos Uang
                        </button>
                    </div>

                    <div id="allocationsListContainer">
                        <!-- Allocations list injected by JS -->
                    </div>
                </div>

                <!-- TAB 2: RIWAYAT MUTASI -->
                <div id="detailTabLogs" style="display: none;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-bottom: 12px;">
                        Log Penambahan &amp; Penarikan Dana:
                    </div>
                    <div id="logsTimelineContainer" style="padding-left: 6px;">
                        <!-- Timeline injected by JS -->
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="padding: 12px 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between;">
                <button type="button" class="btn-primary-custom" onclick="openEditGoalModalFromDetail()" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); font-size: 11px; padding: 6px 12px;">
                    <i class="bi bi-pencil-square"></i> Edit Goal
                </button>
                <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-3); color: var(--text-primary); font-size: 11px; padding: 6px 16px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: TAMBAH / EDIT GOAL                              -->
<!-- ======================================================== -->
<div class="modal fade" id="modalGoalForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
                <h5 class="modal-title" id="goalFormTitle" style="font-weight: 700; font-size: 1rem; color: var(--text-primary);">
                    Tambah Target / Goal Tabungan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="goalForm" onsubmit="submitGoalForm(event)">
                <input type="hidden" id="formGoalId" value="">
                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
                    
                    <!-- Goal Name -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nama Target / Goal <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formGoalName" class="form-control-custom" placeholder="Misal: Tabungan Mobil, Dana Darurat, Umroh" required style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 13px;">
                    </div>

                    <!-- Target Amount -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Target Nominal (Rp) <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formGoalTargetAmount" class="form-control-custom" placeholder="Misal: 50.000.000" required oninput="formatCurrencyInput(this)" style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 13px; font-weight: 700;">
                    </div>

                    <!-- Target Date & Category (2 cols) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Target Tanggal (Opsional)
                            </label>
                            <input type="date" id="formGoalTargetDate" class="form-control-custom" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px; color-scheme: dark;">
                        </div>
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Kategori
                            </label>
                            <select id="formGoalCategory" class="form-control-custom" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px;">
                                <option value="Kendaraan">Kendaraan (Mobil/Motor)</option>
                                <option value="Properti">Properti &amp; Rumah</option>
                                <option value="Dana Darurat">Dana Darurat</option>
                                <option value="Investasi">Investasi &amp; Saham</option>
                                <option value="Modal Usaha">Modal Usaha / Toko</option>
                                <option value="Liburan">Liburan &amp; Healing</option>
                                <option value="Pendidikan">Pendidikan</option>
                                <option value="Gadget">Gadget &amp; Elektronik</option>
                                <option value="Lainnya" selected>Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <!-- Icon & Color Picker (2 cols) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Pilihan Icon
                            </label>
                            <select id="formGoalIcon" class="form-control-custom" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px;">
                                <option value="bi-piggy-bank-fill">🐷 Celengan / Piggy Bank</option>
                                <option value="bi-car-front-fill">🚗 Mobil / Kendaraan</option>
                                <option value="bi-house-heart-fill">🏠 Rumah / Properti</option>
                                <option value="bi-shield-check">🛡️ Dana Darurat</option>
                                <option value="bi-graph-up-arrow">📈 Investasi / Cuan</option>
                                <option value="bi-shop">🏪 Toko / Bisnis</option>
                                <option value="bi-airplane-fill">✈️ Liburan / Tiket</option>
                                <option value="bi-laptop">💻 Gadget / Laptop</option>
                                <option value="bi-gem">💎 Perhiasan / Emas</option>
                                <option value="bi-trophy-fill">🏆 Impian / Goals</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Warna Aksen
                            </label>
                            <select id="formGoalColor" class="form-control-custom" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px;">
                                <option value="#6366f1">Indigo (Modern Blue)</option>
                                <option value="#10b981">Emerald Green (Fresh)</option>
                                <option value="#3b82f6">Ocean Blue</option>
                                <option value="#f59e0b">Amber Gold</option>
                                <option value="#e63946">Coral Red</option>
                                <option value="#a855f7">Purple Vibe</option>
                                <option value="#ec4899">Pink Rose</option>
                                <option value="#14b8a6">Teal Cyan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status (only in edit) -->
                    <div id="formGoalStatusWrapper" style="display: none;">
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Status Target
                        </label>
                        <select id="formGoalStatus" class="form-control-custom" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px;">
                            <option value="in_progress">Sedang Berjalan (In Progress)</option>
                            <option value="achieved">Tercapai (Achieved 🎉)</option>
                            <option value="paused">Dijeda (Paused)</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Catatan / Keterangan
                        </label>
                        <textarea id="formGoalDesc" rows="2" class="form-control-custom" placeholder="Catatan target atau motivasi..." style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px;"></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 14px; font-size: 12px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitGoal" class="btn-primary-custom" style="padding: 7px 18px; font-size: 12px;">
                        Simpan Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: TAMBAH / EDIT POS PENEMPATAN DANA              -->
<!-- ======================================================== -->
<div class="modal fade" id="modalAllocationForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
                <h5 class="modal-title" id="allocFormTitle" style="font-weight: 700; font-size: 1rem; color: var(--text-primary);">
                    Tambah Pos Penempatan Uang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="allocationForm" onsubmit="submitAllocationForm(event)">
                <input type="hidden" id="formAllocId" value="">
                <input type="hidden" id="formAllocGoalId" value="">
                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
                    
                    <!-- Allocation Name -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nama Pos / Akun <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formAllocName" class="form-control-custom" placeholder="Misal: Uang Toko (Berputar), Bibit Reksadana, SeaBank" required style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 13px;">
                    </div>

                    <!-- Account Type & Institution (2 cols) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Jenis Klasifikasi <span style="color: var(--primary);">*</span>
                            </label>
                            <select id="formAllocType" class="form-control-custom" required style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px;">
                                <option value="Toko / Kas">🏪 Toko / Kas (Uang Berputar)</option>
                                <option value="Investasi">📈 Investasi (Bibit, Saham, Reksadana)</option>
                                <option value="Bank / Rekening" selected>💳 Bank / Rekening (SeaBank, BCA, dll)</option>
                                <option value="Piutang / Hutang">📑 Piutang / Pinjaman</option>
                                <option value="Lainnya">📦 Lainnya (Emas Fisik, Dompet)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Institusi / Platform
                            </label>
                            <input type="text" id="formAllocInstitution" class="form-control-custom" placeholder="Toko, Bibit, SeaBank, BCA, dll" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px;">
                        </div>
                    </div>

                    <!-- Nominal Saldo -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nominal Saldo (Rp) <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formAllocAmount" class="form-control-custom" placeholder="Misal: 3.000.000" required oninput="formatCurrencyInput(this)" style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 13px; font-weight: 700;">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Catatan Pos (Opsional)
                        </label>
                        <input type="text" id="formAllocNotes" class="form-control-custom" placeholder="Misal: No Rek / Portfolio Reksadana Obligasi" style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px;">
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 14px; font-size: 12px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitAlloc" class="btn-primary-custom" style="padding: 7px 18px; font-size: 12px;">
                        Simpan Pos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 4: CATAT MUTASI (SETOR / TARIK SALDO POS)         -->
<!-- ======================================================== -->
<div class="modal fade" id="modalMutationForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
                <h5 class="modal-title" style="font-weight: 700; font-size: 1rem; color: var(--text-primary);">
                    Catat Mutasi / Perubahan Saldo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="mutationForm" onsubmit="submitMutationForm(event)">
                <input type="hidden" id="formMutGoalId" value="">
                <input type="hidden" id="formMutAllocId" value="">
                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
                    
                    <!-- Pos Info Pill -->
                    <div style="background: var(--surface-2); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px 14px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Pos Penempatan Target:</div>
                        <div id="formMutAllocName" style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-top: 2px;">-</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            Saldo Saat Ini: <strong id="formMutCurrentBalance" style="color: var(--success);">Rp 0</strong>
                        </div>
                    </div>

                    <!-- Type Selector (Setor vs Tarik) -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; display: block;">
                            Jenis Mutasi <span style="color: var(--primary);">*</span>
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <label style="display: flex; align-items: center; gap: 8px; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: var(--radius-md); padding: 10px 14px; cursor: pointer;">
                                <input type="radio" name="mutationType" value="deposit" checked style="accent-color: #10b981;">
                                <div>
                                    <div style="font-size: 12px; font-weight: 800; color: #10b981;"><i class="bi bi-plus-circle-fill"></i> Tambah Setor</div>
                                    <div style="font-size: 9px; color: var(--text-muted);">Menambah saldo pos</div>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: var(--radius-md); padding: 10px 14px; cursor: pointer;">
                                <input type="radio" name="mutationType" value="withdraw" style="accent-color: #ef4444;">
                                <div>
                                    <div style="font-size: 12px; font-weight: 800; color: #ef4444;"><i class="bi bi-dash-circle-fill"></i> Tarik Dana</div>
                                    <div style="font-size: 9px; color: var(--text-muted);">Mengurangi saldo pos</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Nominal Mutasi -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nominal Mutasi (Rp) <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formMutAmount" class="form-control-custom" placeholder="Misal: 500.000" required oninput="formatCurrencyInput(this)" style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 13px; font-weight: 700;">
                    </div>

                    <!-- Date & Notes -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Tanggal Mutasi
                            </label>
                            <input type="date" id="formMutDate" value="<?= date('Y-m-d') ?>" class="form-control-custom" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px; color-scheme: dark;">
                        </div>
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Catatan / Keterangan
                            </label>
                            <input type="text" id="formMutNotes" class="form-control-custom" placeholder="Misal: Hasil untung toko bulan Mei" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 14px; font-size: 12px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitMut" class="btn-primary-custom" style="padding: 7px 18px; font-size: 12px;">
                        Simpan Mutasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- JAVASCRIPT LOGIC                                         -->
<!-- ======================================================== -->
<script>
let allGoals = [];
let activeGoal = null;
let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', () => {
    loadGoals();
    loadSummary();
});

// Format Rupiah helpers
function formatRupiah(number) {
    const num = Number(number) || 0;
    return 'Rp ' + num.toLocaleString('id-ID');
}

function parseRupiahInput(val) {
    if (!val) return 0;
    const clean = String(val).replace(/[^0-9]/g, '');
    return parseFloat(clean) || 0;
}

function formatCurrencyInput(el) {
    const val = parseRupiahInput(el.value);
    el.value = val > 0 ? val.toLocaleString('id-ID') : '';
}

function getCsrfToken() {
    return document.getElementById('csrfToken')?.value || '';
}

// ----------------------------------------------------
// LOAD & RENDER GOALS
// ----------------------------------------------------
async function loadGoals() {
    const container = document.getElementById('goalsGridContainer');
    try {
        const res = await fetch('<?= BASE_URL ?>api/savings/goals');
        const json = await res.json();
        if (json.success && Array.isArray(json.data)) {
            allGoals = json.data;
            renderGoalsGrid();
        } else {
            container.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:30px;">Gagal memuat target tabungan.</div>';
        }
    } catch (e) {
        console.error(e);
        container.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:30px;">Koneksi terputus. Silakan coba lagi.</div>';
    }
}

async function loadSummary() {
    try {
        const res = await fetch('<?= BASE_URL ?>api/savings/summary');
        const json = await res.json();
        if (json.success && json.data) {
            updateSummaryUI(json.data);
        }
    } catch (e) {
        console.error(e);
    }
}

function updateSummaryUI(summary) {
    document.getElementById('statTotalCollected').textContent = formatRupiah(summary.total_collected);
    document.getElementById('statTotalTarget').textContent = formatRupiah(summary.total_target);
    document.getElementById('statTotalRemaining').innerHTML = `Sisa: <strong style="color:var(--primary);">${formatRupiah(summary.total_remaining)}</strong>`;
    document.getElementById('statOverallProgress').textContent = `${summary.overall_progress}%`;
    document.getElementById('statProgressBar').style.width = `${Math.min(100, summary.overall_progress)}%`;
    document.getElementById('statGoalsCount').textContent = `${summary.total_goals} Target`;
    document.getElementById('statGoalsSub').innerHTML = `<span style="color:var(--success);font-weight:700;">${summary.achieved_goals} Tercapai</span> &middot; ${summary.in_progress_goals} Berjalan`;

    // Render distribution
    const barCont = document.getElementById('distBarContainer');
    const pillsCont = document.getElementById('distPillsContainer');
    const typeBreakdown = summary.type_breakdown || [];
    const palette = ['#10b981', '#6366f1', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6'];

    if (typeBreakdown.length === 0) {
        barCont.innerHTML = '<div class="dist-bar-segment" style="width:100%;background:var(--surface-3);"></div>';
        pillsCont.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--text-muted);font-size:11px;padding:10px 0;">Belum ada pos penempatan dana. Buat goal dan tambahkan alokasi uang (Toko, Bibit, Bank).</div>';
    } else {
        barCont.innerHTML = typeBreakdown.map((item, idx) => {
            const bg = palette[idx % palette.length];
            return `<div class="dist-bar-segment" style="width:${Math.max(2, item.percentage)}%;background:${bg};" title="${item.account_type}: ${formatRupiah(item.total_amount)} (${item.percentage}%)"></div>`;
        }).join('');

        pillsCont.innerHTML = typeBreakdown.map((item, idx) => {
            const bg = palette[idx % palette.length];
            let icon = 'bi-wallet2';
            const atLower = item.account_type.toLowerCase();
            if (atLower.includes('toko') || atLower.includes('kas')) icon = 'bi-shop';
            else if (atLower.includes('investasi')) icon = 'bi-graph-up-arrow';
            else if (atLower.includes('bank')) icon = 'bi-bank';
            else if (atLower.includes('piutang')) icon = 'bi-person-lines-fill';

            return `
                <div class="dist-pill-item">
                    <div style="width:32px;height:32px;border-radius:8px;background:${bg}22;color:${bg};display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                        <i class="bi ${icon}"></i>
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:10px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:700;text-transform:uppercase;">
                            ${item.account_type}
                        </div>
                        <div style="font-size:12px;font-weight:800;color:var(--text-primary);">
                            ${formatRupiah(item.total_amount)}
                        </div>
                        <div style="font-size:9px;color:${bg};font-weight:700;">
                            ${item.percentage}% &middot; ${item.item_count} pos
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
}

function filterGoals(status, btnEl) {
    currentFilter = status;
    document.querySelectorAll('.filter-btn-group .btn-filter').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    renderGoalsGrid();
}

function renderGoalsGrid() {
    const container = document.getElementById('goalsGridContainer');
    let filtered = allGoals;
    if (currentFilter !== 'all') {
        filtered = allGoals.filter(g => g.status === currentFilter);
    }

    if (filtered.length === 0) {
        container.innerHTML = `
            <div style="grid-column:1/-1;text-align:center;padding:48px 20px;background:var(--surface-1);border:1px dashed var(--border-color);border-radius:var(--radius-lg);">
                <div style="width:56px;height:56px;border-radius:50%;background:rgba(99,102,241,0.12);color:#818cf8;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 14px;">
                    <i class="bi bi-piggy-bank"></i>
                </div>
                <h5 style="font-weight:800;margin:0 0 6px 0;color:var(--text-primary);">Belum Ada Target Tabungan</h5>
                <p style="font-size:12px;color:var(--text-muted);max-width:420px;margin:0 auto 18px;">
                    Mulai rancang impian Anda seperti Tabungan Mobil, Dana Darurat, atau Modal Usaha dengan mengelompokkan uang di Toko, Bibit, Bank, dll.
                </p>
                <button class="btn-primary-custom" onclick="openAddGoalModal()" style="padding:9px 18px;border-radius:var(--radius-md);font-size:12px;display:inline-flex;align-items:center;gap:6px;">
                    <i class="bi bi-plus-circle-fill"></i> Buat Target Pertama Sekarang
                </button>
            </div>
        `;
        return;
    }

    container.innerHTML = filtered.map(goal => {
        const color = goal.color || '#6366f1';
        const icon = goal.icon || 'bi-piggy-bank-fill';
        const progress = Math.min(100, goal.progress_percent || 0);
        const isAchieved = goal.status === 'achieved' || progress >= 100;
        const allocations = goal.allocations || [];

        // Preview allocations pills
        let allocPreviewHtml = '';
        if (allocations.length === 0) {
            allocPreviewHtml = `<div style="font-size:11px;color:var(--text-muted);font-style:italic;">Belum ada pos penempatan uang</div>`;
        } else {
            allocPreviewHtml = allocations.slice(0, 3).map(a => {
                let iconClass = 'bi-wallet2';
                const atLower = (a.account_type || '').toLowerCase();
                if (atLower.includes('toko') || atLower.includes('kas')) iconClass = 'bi-shop';
                else if (atLower.includes('investasi')) iconClass = 'bi-graph-up-arrow';
                else if (atLower.includes('bank')) iconClass = 'bi-bank';
                return `
                    <div class="alloc-pill">
                        <span class="alloc-pill-tag"><i class="bi ${iconClass}"></i> ${escapeHtml(a.name)}</span>
                        <strong style="color:var(--text-primary);">${formatRupiah(a.amount)}</strong>
                    </div>
                `;
            }).join('');
            if (allocations.length > 3) {
                allocPreviewHtml += `<div style="font-size:10px;color:var(--text-muted);text-align:right;font-weight:700;">+ ${allocations.length - 3} pos lainnya</div>`;
            }
        }

        return `
            <div class="goal-card">
                <div class="goal-card-top-accent" style="background:${color};"></div>
                
                <div>
                    <!-- Header -->
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:14px;">
                        <div style="display:flex;align-items:center;gap:12px;min-width:0;flex:1;">
                            <div class="goal-icon-badge" style="background:${color}22;color:${color};">
                                <i class="bi ${icon}"></i>
                            </div>
                            <div style="min-width:0;flex:1;">
                                <div style="font-size:14px;font-weight:800;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    ${escapeHtml(goal.name)}
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;margin-top:2px;">
                                    <span class="badge-custom badge-primary" style="font-size:9px;">${escapeHtml(goal.category || 'Lainnya')}</span>
                                    ${isAchieved ? '<span class="badge-custom badge-success" style="font-size:9px;">Tercapai 🎉</span>' : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Menu -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown" style="box-shadow:none;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow" style="font-size:12px;">
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="openGoalDetail(${goal.id})"><i class="bi bi-eye me-2"></i> Buka Detail</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="openEditGoalModal(${goal.id})"><i class="bi bi-pencil me-2"></i> Edit Goal</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDeleteGoal(${goal.id}, '${escapeHtml(goal.name)}')"><i class="bi bi-trash me-2"></i> Hapus Goal</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Amounts Info -->
                    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:6px;">
                        <div>
                            <span style="font-size:10px;color:var(--text-muted);text-transform:uppercase;font-weight:700;display:block;">Terkumpul</span>
                            <span style="font-size:1.15rem;font-weight:800;color:var(--success);">${formatRupiah(goal.collected_amount)}</span>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:10px;color:var(--text-muted);text-transform:uppercase;font-weight:700;display:block;">Target</span>
                            <span style="font-size:0.95rem;font-weight:800;color:var(--text-primary);">${formatRupiah(goal.target_amount)}</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="goal-progress-bar">
                        <div class="goal-progress-fill" style="width:${progress}%;background:${isAchieved ? '#10b981' : color};"></div>
                    </div>

                    <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-muted);margin-bottom:14px;">
                        <span style="font-weight:700;color:${isAchieved ? '#10b981' : 'var(--text-primary)'};">${progress}% Tercapai</span>
                        <span>Sisa: <strong style="color:var(--primary);">${formatRupiah(goal.remaining_amount)}</strong></span>
                    </div>

                    <!-- Allocations Grouping Preview -->
                    <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:16px;">
                        ${allocPreviewHtml}
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div style="display:grid;grid-template-columns:1fr auto;gap:8px;padding-top:12px;border-top:1px solid var(--border-color);">
                    <button class="btn-primary-custom" onclick="openGoalDetail(${goal.id})" style="padding:7px 12px;border-radius:var(--radius-sm);font-size:11px;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <i class="bi bi-folder2-open"></i> Detail &amp; Kelola Alokasi
                    </button>
                    <button class="btn-primary-custom" onclick="openAddAllocationModalDirect(${goal.id})" title="Tambah Pos Alokasi Uang" style="background:var(--surface-2);color:var(--text-primary);border:1px solid var(--border-color);padding:7px 10px;border-radius:var(--radius-sm);font-size:11px;">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// ----------------------------------------------------
// DETAIL GOAL VIEW & TABS
// ----------------------------------------------------
async function openGoalDetail(goalId) {
    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/goals/${goalId}`);
        const json = await res.json();
        if (!json.success || !json.data) {
            alert(json.error || 'Gagal memuat detail goal');
            return;
        }

        activeGoal = json.data;
        renderGoalDetailModal();
        const modal = new bootstrap.Modal(document.getElementById('modalGoalDetail'));
        modal.show();
    } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan saat memuat detail.');
    }
}

function renderGoalDetailModal() {
    if (!activeGoal) return;

    const color = activeGoal.color || '#6366f1';
    const icon = activeGoal.icon || 'bi-piggy-bank-fill';
    const progress = Math.min(100, activeGoal.progress_percent || 0);
    const isAchieved = activeGoal.status === 'achieved' || progress >= 100;

    document.getElementById('detailGoalIcon').className = `bi ${icon}`;
    document.getElementById('detailGoalIconBadge').style.background = `${color}22`;
    document.getElementById('detailGoalIconBadge').style.color = color;
    document.getElementById('detailGoalName').textContent = activeGoal.name;
    document.getElementById('detailGoalCategory').textContent = activeGoal.category || 'Lainnya';
    document.getElementById('detailGoalDesc').textContent = activeGoal.description || 'Tidak ada catatan tambahan.';

    const statusBadge = document.getElementById('detailGoalStatusBadge');
    if (isAchieved) {
        statusBadge.className = 'badge-custom badge-success';
        statusBadge.textContent = 'Tercapai 🎉';
    } else {
        statusBadge.className = 'badge-custom badge-primary';
        statusBadge.textContent = 'Sedang Berjalan';
    }

    document.getElementById('detailCollectedAmount').textContent = formatRupiah(activeGoal.collected_amount);
    document.getElementById('detailTargetAmount').textContent = formatRupiah(activeGoal.target_amount);
    document.getElementById('detailRemainingAmount').textContent = formatRupiah(activeGoal.remaining_amount);

    const progressFill = document.getElementById('detailProgressFill');
    progressFill.style.width = `${progress}%`;
    progressFill.style.background = isAchieved ? '#10b981' : color;
    document.getElementById('detailProgressText').textContent = `Progress: ${progress}%`;

    const deadlineEl = document.getElementById('detailDeadlineText');
    if (activeGoal.target_date) {
        const days = activeGoal.days_left;
        let dayText = '';
        if (days !== null) {
            dayText = days >= 0 ? ` (${days} hari lagi)` : ' (Terlewat)';
        }
        deadlineEl.innerHTML = `<i class="bi bi-calendar-event"></i> Target: ${activeGoal.target_date}${dayText}`;
    } else {
        deadlineEl.innerHTML = `<i class="bi bi-calendar-event"></i> Tanpa batas tanggal`;
    }

    // Render Allocations List
    renderAllocationsList();

    // Render Logs Timeline
    renderLogsTimeline();
}

function switchDetailTab(tab, btnEl) {
    document.querySelectorAll('#modalGoalDetail .btn-filter').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    document.getElementById('detailTabAllocations').style.display = tab === 'allocations' ? 'block' : 'none';
    document.getElementById('detailTabLogs').style.display = tab === 'logs' ? 'block' : 'none';
}

function renderAllocationsList() {
    const container = document.getElementById('allocationsListContainer');
    const allocations = activeGoal.allocations || [];

    if (allocations.length === 0) {
        container.innerHTML = `
            <div style="text-align:center;padding:24px 10px;background:var(--surface-2);border-radius:var(--radius-md);border:1px dashed var(--border-color);">
                <i class="bi bi-wallet2" style="font-size:1.5rem;color:var(--text-muted);display:block;margin-bottom:6px;"></i>
                <div style="font-size:12px;font-weight:700;color:var(--text-primary);">Belum ada pos penempatan uang</div>
                <div style="font-size:10px;color:var(--text-muted);margin:2px 0 10px 0;">Contoh: Uang di Toko (3jt), di Bibit (4jt), di SeaBank (350rb)</div>
                <button class="btn-primary-custom" onclick="openAddAllocationModal()" style="padding:6px 14px;border-radius:var(--radius-sm);font-size:11px;">
                    <i class="bi bi-plus-lg"></i> Tambah Pos Uang Sekarang
                </button>
            </div>
        `;
        return;
    }

    const totalCollected = activeGoal.collected_amount || 1;

    container.innerHTML = allocations.map(alloc => {
        const pct = totalCollected > 0 ? Math.round((alloc.amount / totalCollected) * 100) : 0;
        let iconClass = 'bi-wallet2';
        const atLower = (alloc.account_type || '').toLowerCase();
        if (atLower.includes('toko') || atLower.includes('kas')) iconClass = 'bi-shop';
        else if (atLower.includes('investasi')) iconClass = 'bi-graph-up-arrow';
        else if (atLower.includes('bank')) iconClass = 'bi-bank';
        else if (atLower.includes('piutang')) iconClass = 'bi-person-lines-fill';

        return `
            <div class="alloc-card-item">
                <div style="display:flex;align-items:center;gap:12px;min-width:0;flex:1;">
                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(99,102,241,0.12);color:#818cf8;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;">
                        <i class="bi ${iconClass}"></i>
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="font-size:13px;font-weight:800;color:var(--text-primary);">${escapeHtml(alloc.name)}</span>
                            <span class="badge-custom badge-primary" style="font-size:9px;">${escapeHtml(alloc.account_type)}</span>
                            ${alloc.institution ? `<span style="font-size:10px;color:var(--text-muted);">(${escapeHtml(alloc.institution)})</span>` : ''}
                        </div>
                        ${alloc.notes ? `<div style="font-size:10px;color:var(--text-muted);margin-top:2px;">${escapeHtml(alloc.notes)}</div>` : ''}
                    </div>
                </div>

                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:13px;font-weight:800;color:var(--success);">${formatRupiah(alloc.amount)}</div>
                    <div style="font-size:9px;color:var(--text-muted);font-weight:700;">${pct}% dari target terkumpul</div>
                </div>

                <!-- Action buttons -->
                <div style="display:flex;gap:4px;flex-shrink:0;">
                    <button class="btn-primary-custom" onclick="openMutationModal(${alloc.id}, '${escapeHtml(alloc.name)}', ${alloc.amount})" title="Tambah / Tarik Saldo" style="padding:6px 9px;border-radius:var(--radius-sm);font-size:11px;">
                        <i class="bi bi-arrow-left-right"></i> Mutasi
                    </button>
                    <button class="btn-primary-custom" onclick="openEditAllocationModal(${alloc.id})" title="Edit Pos" style="background:var(--surface-3);color:var(--text-primary);padding:6px 8px;border-radius:var(--radius-sm);font-size:11px;">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn-primary-custom" onclick="confirmDeleteAllocation(${alloc.id}, '${escapeHtml(alloc.name)}')" title="Hapus Pos" style="background:var(--danger-bg);color:var(--danger);padding:6px 8px;border-radius:var(--radius-sm);font-size:11px;">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function renderLogsTimeline() {
    const container = document.getElementById('logsTimelineContainer');
    const logs = activeGoal.logs || [];

    if (logs.length === 0) {
        container.innerHTML = `<div style="text-align:center;color:var(--text-muted);font-size:11px;padding:20px;">Belum ada riwayat mutasi dana.</div>`;
        return;
    }

    container.innerHTML = logs.map(log => {
        const isDeposit = log.type === 'deposit';
        const color = isDeposit ? 'var(--success)' : 'var(--danger)';
        const sign = isDeposit ? '+' : '-';
        const icon = isDeposit ? 'bi-arrow-down-left-circle-fill' : 'bi-arrow-up-right-circle-fill';

        return `
            <div class="timeline-item">
                <div class="timeline-dot" style="background:${color};"></div>
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                    <div>
                        <div style="font-size:12px;font-weight:700;color:var(--text-primary);">
                            <i class="bi ${icon}" style="color:${color};"></i> ${isDeposit ? 'Setoran' : 'Penarikan'} (${escapeHtml(log.allocation_name || 'Pos')})
                        </div>
                        <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">
                            ${log.log_date} &middot; Saldo akhir: ${formatRupiah(log.balance_after)}
                        </div>
                        ${log.notes ? `<div style="font-size:10px;color:var(--text-primary);margin-top:2px;font-style:italic;">"${escapeHtml(log.notes)}"</div>` : ''}
                    </div>
                    <div style="font-size:12px;font-weight:800;color:${color};white-space:nowrap;">
                        ${sign} ${formatRupiah(log.amount)}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// ----------------------------------------------------
// GOAL CREATE & EDIT
// ----------------------------------------------------
function openAddGoalModal() {
    document.getElementById('formGoalId').value = '';
    document.getElementById('goalFormTitle').textContent = 'Tambah Target / Goal Tabungan';
    document.getElementById('formGoalName').value = '';
    document.getElementById('formGoalTargetAmount').value = '';
    document.getElementById('formGoalTargetDate').value = '';
    document.getElementById('formGoalCategory').value = 'Kendaraan';
    document.getElementById('formGoalIcon').value = 'bi-piggy-bank-fill';
    document.getElementById('formGoalColor').value = '#6366f1';
    document.getElementById('formGoalDesc').value = '';
    document.getElementById('formGoalStatusWrapper').style.display = 'none';
    document.getElementById('btnSubmitGoal').textContent = 'Simpan Goal';

    const modal = new bootstrap.Modal(document.getElementById('modalGoalForm'));
    modal.show();
}

function openEditGoalModal(goalId) {
    const goal = allGoals.find(g => g.id == goalId);
    if (!goal) return;

    document.getElementById('formGoalId').value = goal.id;
    document.getElementById('goalFormTitle').textContent = 'Edit Target / Goal Tabungan';
    document.getElementById('formGoalName').value = goal.name;
    document.getElementById('formGoalTargetAmount').value = Number(goal.target_amount).toLocaleString('id-ID');
    document.getElementById('formGoalTargetDate').value = goal.target_date || '';
    document.getElementById('formGoalCategory').value = goal.category || 'Lainnya';
    document.getElementById('formGoalIcon').value = goal.icon || 'bi-piggy-bank-fill';
    document.getElementById('formGoalColor').value = goal.color || '#6366f1';
    document.getElementById('formGoalDesc').value = goal.description || '';
    document.getElementById('formGoalStatus').value = goal.status || 'in_progress';
    document.getElementById('formGoalStatusWrapper').style.display = 'block';
    document.getElementById('btnSubmitGoal').textContent = 'Perbarui Goal';

    const modal = new bootstrap.Modal(document.getElementById('modalGoalForm'));
    modal.show();
}

function openEditGoalModalFromDetail() {
    if (!activeGoal) return;
    const detailModal = bootstrap.Modal.getInstance(document.getElementById('modalGoalDetail'));
    if (detailModal) detailModal.hide();
    openEditGoalModal(activeGoal.id);
}

async function submitGoalForm(e) {
    e.preventDefault();
    const goalId = document.getElementById('formGoalId').value;
    const isEdit = Boolean(goalId);

    const payload = {
        name: document.getElementById('formGoalName').value.trim(),
        target_amount: parseRupiahInput(document.getElementById('formGoalTargetAmount').value),
        target_date: document.getElementById('formGoalTargetDate').value || null,
        category: document.getElementById('formGoalCategory').value,
        icon: document.getElementById('formGoalIcon').value,
        color: document.getElementById('formGoalColor').value,
        description: document.getElementById('formGoalDesc').value.trim(),
        status: document.getElementById('formGoalStatus').value || 'in_progress',
        csrf_token: getCsrfToken(),
    };

    if (payload.target_amount <= 0) {
        alert('Nominal target harus lebih dari 0');
        return;
    }

    const btn = document.getElementById('btnSubmitGoal');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    try {
        const url = isEdit ? `<?= BASE_URL ?>api/savings/goals/${goalId}/update` : `<?= BASE_URL ?>api/savings/goals`;
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify(payload)
        });
        const json = await res.json();

        if (json.success) {
            const formModal = bootstrap.Modal.getInstance(document.getElementById('modalGoalForm'));
            if (formModal) formModal.hide();
            await loadGoals();
            await loadSummary();

            if (isEdit && activeGoal && activeGoal.id == goalId) {
                await openGoalDetail(goalId);
            }
        } else {
            alert(json.error || 'Gagal menyimpan goal.');
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
        btn.textContent = isEdit ? 'Perbarui Goal' : 'Simpan Goal';
    }
}

async function confirmDeleteGoal(goalId, name) {
    if (!confirm(`Yakin ingin menghapus target "${name}"?\nSeluruh pos alokasi uang dan riwayat mutasi target ini akan ikut terhapus.`)) {
        return;
    }

    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/goals/${goalId}/delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ csrf_token: getCsrfToken() })
        });
        const json = await res.json();
        if (json.success) {
            const detailModal = bootstrap.Modal.getInstance(document.getElementById('modalGoalDetail'));
            if (detailModal) detailModal.hide();
            await loadGoals();
            await loadSummary();
        } else {
            alert(json.error || 'Gagal menghapus goal');
        }
    } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan.');
    }
}

// ----------------------------------------------------
// ALLOCATIONS CREATE & EDIT
// ----------------------------------------------------
function openAddAllocationModal() {
    if (!activeGoal) return;
    openAddAllocationModalDirect(activeGoal.id);
}

function openAddAllocationModalDirect(goalId) {
    document.getElementById('formAllocId').value = '';
    document.getElementById('formAllocGoalId').value = goalId;
    document.getElementById('allocFormTitle').textContent = 'Tambah Pos Penempatan Uang';
    document.getElementById('formAllocName').value = '';
    document.getElementById('formAllocType').value = 'Bank / Rekening';
    document.getElementById('formAllocInstitution').value = '';
    document.getElementById('formAllocAmount').value = '';
    document.getElementById('formAllocNotes').value = '';
    document.getElementById('btnSubmitAlloc').textContent = 'Simpan Pos';

    const modal = new bootstrap.Modal(document.getElementById('modalAllocationForm'));
    modal.show();
}

function openEditAllocationModal(allocId) {
    if (!activeGoal) return;
    const alloc = (activeGoal.allocations || []).find(a => a.id == allocId);
    if (!alloc) return;

    document.getElementById('formAllocId').value = alloc.id;
    document.getElementById('formAllocGoalId').value = activeGoal.id;
    document.getElementById('allocFormTitle').textContent = 'Edit Pos Penempatan Uang';
    document.getElementById('formAllocName').value = alloc.name;
    document.getElementById('formAllocType').value = alloc.account_type || 'Bank / Rekening';
    document.getElementById('formAllocInstitution').value = alloc.institution || '';
    document.getElementById('formAllocAmount').value = Number(alloc.amount).toLocaleString('id-ID');
    document.getElementById('formAllocNotes').value = alloc.notes || '';
    document.getElementById('btnSubmitAlloc').textContent = 'Perbarui Pos';

    const modal = new bootstrap.Modal(document.getElementById('modalAllocationForm'));
    modal.show();
}

async function submitAllocationForm(e) {
    e.preventDefault();
    const allocId = document.getElementById('formAllocId').value;
    const goalId = document.getElementById('formAllocGoalId').value;

    const payload = {
        id: allocId || null,
        goal_id: goalId,
        name: document.getElementById('formAllocName').value.trim(),
        account_type: document.getElementById('formAllocType').value,
        institution: document.getElementById('formAllocInstitution').value.trim(),
        amount: parseRupiahInput(document.getElementById('formAllocAmount').value),
        notes: document.getElementById('formAllocNotes').value.trim(),
        csrf_token: getCsrfToken(),
    };

    const btn = document.getElementById('btnSubmitAlloc');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/allocations`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify(payload)
        });
        const json = await res.json();

        if (json.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAllocationForm'));
            if (modal) modal.hide();
            await loadGoals();
            await loadSummary();

            if (json.data) {
                activeGoal = json.data;
                renderGoalDetailModal();
            }
        } else {
            alert(json.error || 'Gagal menyimpan pos alokasi.');
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
        btn.textContent = allocId ? 'Perbarui Pos' : 'Simpan Pos';
    }
}

async function confirmDeleteAllocation(allocId, name) {
    if (!confirm(`Hapus pos alokasi "${name}"? Saldo di pos ini tidak lagi terhitung dalam goal.`)) {
        return;
    }

    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/allocations/${allocId}/delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ csrf_token: getCsrfToken() })
        });
        const json = await res.json();
        if (json.success) {
            await loadGoals();
            await loadSummary();
            if (json.data) {
                activeGoal = json.data;
                renderGoalDetailModal();
            }
        } else {
            alert(json.error || 'Gagal menghapus pos');
        }
    } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan.');
    }
}

// ----------------------------------------------------
// MUTATION (SETOR / TARIK SALDO)
// ----------------------------------------------------
function openMutationModal(allocId, allocName, currentAmount) {
    if (!activeGoal) return;
    document.getElementById('formMutGoalId').value = activeGoal.id;
    document.getElementById('formMutAllocId').value = allocId;
    document.getElementById('formMutAllocName').textContent = allocName;
    document.getElementById('formMutCurrentBalance').textContent = formatRupiah(currentAmount);
    document.getElementById('formMutAmount').value = '';
    document.getElementById('formMutNotes').value = '';

    const modal = new bootstrap.Modal(document.getElementById('modalMutationForm'));
    modal.show();
}

async function submitMutationForm(e) {
    e.preventDefault();
    const typeEl = document.querySelector('input[name="mutationType"]:checked');
    const type = typeEl ? typeEl.value : 'deposit';

    const payload = {
        goal_id: document.getElementById('formMutGoalId').value,
        allocation_id: document.getElementById('formMutAllocId').value,
        type: type,
        amount: parseRupiahInput(document.getElementById('formMutAmount').value),
        log_date: document.getElementById('formMutDate').value || null,
        notes: document.getElementById('formMutNotes').value.trim(),
        csrf_token: getCsrfToken(),
    };

    if (payload.amount <= 0) {
        alert('Nominal mutasi harus lebih besar dari 0');
        return;
    }

    const btn = document.getElementById('btnSubmitMut');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mencatat...';

    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/mutations`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify(payload)
        });
        const json = await res.json();

        if (json.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalMutationForm'));
            if (modal) modal.hide();
            await loadGoals();
            await loadSummary();

            if (json.data) {
                activeGoal = json.data;
                renderGoalDetailModal();
            }
        } else {
            alert(json.error || 'Gagal mencatat mutasi.');
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Mutasi';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
