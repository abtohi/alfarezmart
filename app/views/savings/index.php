<?php
/**
 * Savings & Financial Goals View (Modern Custom UI)
 *
 * @var array $currentUser
 * @var array $summary
 * @var array $goals
 * @var string $csrfToken
 */
$csrfToken = $csrfToken ?? ($this->security ? $this->security->getCSRFToken() : '');
?>

<style>
/* ===== SAVINGS & GOALS ULTRA-MODERN STYLES ===== */
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
    margin-bottom: 22px;
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
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.2s ease, box-shadow 0.2s ease;
}

.savings-kpi-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

.savings-dist-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    margin-bottom: 22px;
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
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
    transition: border-color 0.2s;
}

.dist-pill-item:hover {
    border-color: var(--primary);
}

/* Control Bar (Search & Filters) */
.savings-control-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    gap: 12px;
    flex-wrap: wrap;
}

.savings-search-box {
    flex: 1;
    min-width: 240px;
    max-width: 380px;
}

.savings-goals-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-top: 6px;
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
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
}

.goal-card:hover {
    border-color: var(--primary);
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.12);
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
    transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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
    transition: border-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), transform 0.15s ease;
}

.alloc-card-item:hover {
    border-color: var(--primary);
    transform: translateY(-1px);
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

/* Custom Filter Buttons */
.filter-btn-group .btn-filter {
    padding: 7px 15px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    background: var(--surface-1);
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.filter-btn-group .btn-filter:hover {
    border-color: var(--primary);
    color: var(--text-primary);
    background: var(--surface-2);
}

.filter-btn-group .btn-filter.active {
    background: var(--primary);
    color: #ffffff !important;
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(230,57,70,0.3);
}

/* ======================================================== */
/* CUSTOM MODERN DROPDOWN PICKERS (NO NATIVE SELECTS)     */
/* ======================================================== */
.custom-select-picker {
    position: relative;
    width: 100%;
}

.custom-select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 14px;
    background: var(--bg-primary);
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.custom-select-trigger:hover {
    border-color: var(--primary);
    background: var(--surface-2);
}

.custom-select-trigger.open {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(230,57,70,0.18);
}

.custom-select-content {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    flex: 1;
}

.custom-select-icon {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.custom-select-label {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.custom-select-sub {
    font-size: 10.5px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.custom-select-chevron {
    color: var(--text-muted);
    font-size: 12px;
    transition: transform 0.25s ease;
    flex-shrink: 0;
}

.custom-select-trigger.open .custom-select-chevron {
    transform: rotate(180deg);
    color: var(--primary);
}

.custom-select-menu {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: 0 12px 36px rgba(0,0,0,0.3), 0 2px 6px rgba(0,0,0,0.1);
    z-index: 1050;
    overflow: hidden;
    padding: 6px;
    max-height: 270px;
    overflow-y: auto;
    animation: customMenuFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    backdrop-filter: blur(14px);
}

.custom-select-menu.show {
    display: block;
}

@keyframes customMenuFade {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-select-search {
    padding: 6px 8px 8px 8px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 4px;
}

.custom-select-search input {
    width: 100%;
    padding: 6px 10px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-size: 11.5px;
    outline: none;
}

.custom-select-search input:focus {
    border-color: var(--primary);
}

.custom-select-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid transparent;
}

.custom-select-option:hover {
    background: var(--surface-2);
    border-color: rgba(230,57,70,0.15);
}

.custom-select-option.selected {
    background: rgba(230,57,70,0.08);
    border-color: rgba(230,57,70,0.3);
}

.custom-select-check {
    color: var(--primary);
    font-size: 14px;
    font-weight: bold;
    opacity: 0;
    transition: opacity 0.15s ease;
}

.custom-select-option.selected .custom-select-check {
    opacity: 1;
}

/* Visual Icon Picker Grid */
.icon-picker-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
}

.icon-picker-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 8px 4px;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--border-color);
    background: var(--bg-primary);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    color: var(--text-muted);
}

.icon-picker-item:hover {
    border-color: var(--primary);
    background: var(--surface-2);
    color: var(--text-primary);
    transform: translateY(-2px);
}

.icon-picker-item.selected {
    border-color: var(--primary);
    background: rgba(230,57,70,0.12);
    color: var(--primary);
    box-shadow: 0 0 0 2px rgba(230,57,70,0.25);
}

.icon-picker-item i {
    font-size: 1.25rem;
}

.icon-picker-item span {
    font-size: 9.5px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

/* Color Swatches Grid */
.color-picker-grid {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.color-swatch-item {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 14px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    border: 2px solid transparent;
}

.color-swatch-item:hover {
    transform: scale(1.15);
}

.color-swatch-item.selected {
    transform: scale(1.15);
    border-color: #ffffff;
    box-shadow: 0 0 0 3px rgba(255,255,255,0.4);
}

.color-swatch-item i {
    opacity: 0;
    transition: opacity 0.15s ease;
}

.color-swatch-item.selected i {
    opacity: 1;
}

/* Mutation Segmented Radio Cards */
.mutation-type-segment {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.mutation-type-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--border-color);
    background: var(--bg-primary);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
}

.mutation-type-card:hover {
    border-color: var(--border-active);
    background: var(--surface-2);
}

.mutation-type-card.selected-deposit {
    border-color: var(--success);
    background: rgba(16,185,129,0.12);
    box-shadow: 0 0 0 2px rgba(16,185,129,0.25);
}

.mutation-type-card.selected-withdraw {
    border-color: var(--danger);
    background: rgba(239,68,68,0.12);
    box-shadow: 0 0 0 2px rgba(239,68,68,0.25);
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

    <!-- Modern Search & Filter Control Bar -->
    <div class="savings-control-bar">
        <div class="section-title" style="margin-bottom: 0;">Daftar Target &amp; Goals Tabungan</div>
        
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; flex: 1; justify-content: flex-end;">
            <!-- Search Box -->
            <div class="search-input-wrapper savings-search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchGoalsInput" placeholder="Cari nama target atau pos alokasi..." oninput="onSearchGoals(this.value)">
                <button type="button" id="btnClearSearch" onclick="clearSearchGoals()" style="display: none; background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 0 4px;">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>

            <!-- Filter Status Buttons -->
            <div class="filter-btn-group" style="display: flex; gap: 6px;">
                <button class="btn-filter active" onclick="filterGoals('all', this)"><i class="bi bi-grid"></i> Semua</button>
                <button class="btn-filter" onclick="filterGoals('in_progress', this)"><i class="bi bi-hourglass-split"></i> Berjalan</button>
                <button class="btn-filter" onclick="filterGoals('achieved', this)"><i class="bi bi-check2-circle"></i> Tercapai</button>
            </div>
        </div>
    </div>

    <!-- Goals Grid Container -->
    <div id="goalsGridContainer" class="savings-goals-grid">
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
                
                <!-- Nav Tabs -->
                <ul class="nav nav-pills mb-3 filter-btn-group" style="gap: 8px;">
                    <li class="nav-item">
                        <button class="btn-filter active" id="tabAllocationsBtn" onclick="switchDetailTab('allocations', this)" style="padding: 7px 16px;">
                            <i class="bi bi-grid-fill"></i> Pos Penempatan Dana
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="btn-filter" id="tabLogsBtn" onclick="switchDetailTab('logs', this)" style="padding: 7px 16px;">
                            <i class="bi bi-clock-history"></i> Riwayat Mutasi
                        </button>
                    </li>
                </ul>

                <!-- TAB 1: POS ALOKASI (GROUPING UANG) -->
                <div id="detailTabAllocations">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="font-size: 12px; font-weight: 700; color: var(--text-primary);">
                            Rincian Pembagian Uang Goal Ini:
                        </div>
                        <button class="btn-primary-custom" onclick="openAddAllocationModal()" style="padding: 6px 14px; border-radius: var(--radius-sm); font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
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
                <button type="button" class="btn-primary-custom" onclick="openEditGoalModalFromDetail()" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); font-size: 11.5px; padding: 7px 14px;">
                    <i class="bi bi-pencil-square me-1"></i> Edit Goal
                </button>
                <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-3); color: var(--text-primary); font-size: 11.5px; padding: 7px 18px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: TAMBAH / EDIT GOAL (MODERN CUSTOM PICKERS)     -->
<!-- ======================================================== -->
<div class="modal fade" id="modalGoalForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
                <h5 class="modal-title" id="goalFormTitle" style="font-weight: 800; font-size: 1.05rem; color: var(--text-primary);">
                    Tambah Target / Goal Tabungan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="goalForm" onsubmit="submitGoalForm(event)">
                <input type="hidden" id="formGoalId" value="">
                <input type="hidden" id="formGoalCategory" value="Kendaraan">
                <input type="hidden" id="formGoalIcon" value="bi-piggy-bank-fill">
                <input type="hidden" id="formGoalColor" value="#6366f1">
                <input type="hidden" id="formGoalStatus" value="in_progress">

                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 16px; max-height: 75vh; overflow-y: auto;">
                    
                    <!-- Goal Name -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nama Target / Goal <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formGoalName" class="form-control-custom" placeholder="Misal: Tabungan Mobil, Dana Darurat, Umroh" required style="width: 100%; padding: 10px 14px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 13px; font-weight: 600;">
                    </div>

                    <!-- Target Amount -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Target Nominal (Rp) <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formGoalTargetAmount" class="form-control-custom" placeholder="Misal: 50.000.000" required oninput="formatCurrencyInput(this)" style="width: 100%; padding: 10px 14px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 14px; font-weight: 800;">
                    </div>

                    <!-- Target Date & Category (Custom Dropdown) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Target Tanggal (Opsional)
                            </label>
                            <input type="date" id="formGoalTargetDate" class="form-control-custom" style="width: 100%; padding: 9px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12.5px; color-scheme: dark;">
                        </div>

                        <!-- Custom Category Dropdown Picker -->
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Kategori
                            </label>
                            <div class="custom-select-picker" id="categoryPickerContainer">
                                <div class="custom-select-trigger" id="categoryPickerTrigger" onclick="toggleCustomDropdown('categoryPickerMenu', event)">
                                    <div class="custom-select-content">
                                        <div class="custom-select-icon" id="categorySelectedIcon" style="background: rgba(99,102,241,0.15); color: #818cf8;">
                                            <i class="bi bi-car-front-fill"></i>
                                        </div>
                                        <div class="custom-select-label" id="categorySelectedLabel">Kendaraan</div>
                                    </div>
                                    <i class="bi bi-chevron-down custom-select-chevron"></i>
                                </div>
                                <div class="custom-select-menu" id="categoryPickerMenu">
                                    <div class="custom-select-search">
                                        <input type="text" placeholder="Cari kategori..." oninput="filterDropdownOptions('categoryPickerMenu', this.value)">
                                    </div>
                                    <div id="categoryOptionsList">
                                        <!-- Rendered by JS -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Icon Grid Picker -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; display: block;">
                            Pilihan Icon Target
                        </label>
                        <div class="icon-picker-grid" id="goalIconGrid">
                            <!-- Rendered by JS -->
                        </div>
                    </div>

                    <!-- Visual Color Swatch Picker -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; display: block;">
                            Warna Aksen Kartu
                        </label>
                        <div class="color-picker-grid" id="goalColorGrid">
                            <!-- Rendered by JS -->
                        </div>
                    </div>

                    <!-- Status Picker (only in edit) -->
                    <div id="formGoalStatusWrapper" style="display: none;">
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Status Target
                        </label>
                        <div class="custom-select-picker" id="statusPickerContainer">
                            <div class="custom-select-trigger" id="statusPickerTrigger" onclick="toggleCustomDropdown('statusPickerMenu', event)">
                                <div class="custom-select-content">
                                    <div class="custom-select-icon" id="statusSelectedIcon" style="background: rgba(59,130,246,0.15); color: #3b82f6;">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                    <div class="custom-select-label" id="statusSelectedLabel">Sedang Berjalan</div>
                                </div>
                                <i class="bi bi-chevron-down custom-select-chevron"></i>
                            </div>
                            <div class="custom-select-menu" id="statusPickerMenu">
                                <div class="custom-select-option selected" onclick="selectStatusOption('in_progress', 'Sedang Berjalan', 'bi-hourglass-split', '#3b82f6')">
                                    <div class="custom-select-content">
                                        <div class="custom-select-icon" style="background: rgba(59,130,246,0.15); color: #3b82f6;"><i class="bi bi-hourglass-split"></i></div>
                                        <div>
                                            <div class="custom-select-label">Sedang Berjalan</div>
                                            <div class="custom-select-sub">Target aktif &amp; terus menabung</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-check-lg custom-select-check"></i>
                                </div>
                                <div class="custom-select-option" onclick="selectStatusOption('achieved', 'Tercapai 🎉', 'bi-check-circle-fill', '#10b981')">
                                    <div class="custom-select-content">
                                        <div class="custom-select-icon" style="background: rgba(16,185,129,0.15); color: #10b981;"><i class="bi bi-check-circle-fill"></i></div>
                                        <div>
                                            <div class="custom-select-label">Tercapai 🎉</div>
                                            <div class="custom-select-sub">Target dana sudah terkumpul</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-check-lg custom-select-check"></i>
                                </div>
                                <div class="custom-select-option" onclick="selectStatusOption('paused', 'Dijeda (Paused)', 'bi-pause-circle-fill', '#f59e0b')">
                                    <div class="custom-select-content">
                                        <div class="custom-select-icon" style="background: rgba(245,158,11,0.15); color: #f59e0b;"><i class="bi bi-pause-circle-fill"></i></div>
                                        <div>
                                            <div class="custom-select-label">Dijeda (Paused)</div>
                                            <div class="custom-select-sub">Tabungan sementara tidak aktif</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-check-lg custom-select-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Catatan / Keterangan
                        </label>
                        <textarea id="formGoalDesc" rows="2" class="form-control-custom" placeholder="Catatan target atau motivasi menabung..." style="width: 100%; padding: 10px 14px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12.5px;"></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 14px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 8px 16px; font-size: 12px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitGoal" class="btn-primary-custom" style="padding: 8px 20px; font-size: 12px; box-shadow: 0 2px 10px rgba(230,57,70,0.3);">
                        Simpan Goal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: TAMBAH / EDIT POS PENEMPATAN (CUSTOM SELECT)   -->
<!-- ======================================================== -->
<div class="modal fade" id="modalAllocationForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
                <h5 class="modal-title" id="allocFormTitle" style="font-weight: 800; font-size: 1.05rem; color: var(--text-primary);">
                    Tambah Pos Penempatan Uang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="allocationForm" onsubmit="submitAllocationForm(event)">
                <input type="hidden" id="formAllocId" value="">
                <input type="hidden" id="formAllocGoalId" value="">
                <input type="hidden" id="formAllocType" value="Bank / Rekening">

                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                    
                    <!-- Allocation Name -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nama Pos / Akun <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formAllocName" class="form-control-custom" placeholder="Misal: Uang Toko (Berputar), Bibit Reksadana, SeaBank" required style="width: 100%; padding: 10px 14px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 13px; font-weight: 600;">
                    </div>

                    <!-- Custom Account Type Dropdown -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Jenis Klasifikasi Akun <span style="color: var(--primary);">*</span>
                        </label>
                        <div class="custom-select-picker" id="allocTypePickerContainer">
                            <div class="custom-select-trigger" id="allocTypePickerTrigger" onclick="toggleCustomDropdown('allocTypePickerMenu', event)">
                                <div class="custom-select-content">
                                    <div class="custom-select-icon" id="allocTypeSelectedIcon" style="background: rgba(99,102,241,0.15); color: #818cf8;">
                                        <i class="bi bi-bank"></i>
                                    </div>
                                    <div>
                                        <div class="custom-select-label" id="allocTypeSelectedLabel">Bank / Rekening</div>
                                        <div class="custom-select-sub" id="allocTypeSelectedSub">SeaBank, BCA, Mandiri, dll</div>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-down custom-select-chevron"></i>
                            </div>
                            <div class="custom-select-menu" id="allocTypePickerMenu">
                                <div id="allocTypeOptionsList">
                                    <!-- Rendered by JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Institution & Amount (2 cols) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Platform / Lembaga
                            </label>
                            <input type="text" id="formAllocInstitution" class="form-control-custom" placeholder="Toko, Bibit, SeaBank, BCA" style="width: 100%; padding: 10px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12.5px;">
                        </div>
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Nominal Saldo (Rp) <span style="color: var(--primary);">*</span>
                            </label>
                            <input type="text" id="formAllocAmount" class="form-control-custom" placeholder="3.000.000" required oninput="formatCurrencyInput(this)" style="width: 100%; padding: 10px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 13px; font-weight: 800;">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Catatan Pos (Opsional)
                        </label>
                        <input type="text" id="formAllocNotes" class="form-control-custom" placeholder="Misal: No Rek / Portfolio Reksadana Obligasi" style="width: 100%; padding: 10px 14px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12.5px;">
                    </div>
                </div>

                <div class="modal-footer" style="padding: 14px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 8px 16px; font-size: 12px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitAlloc" class="btn-primary-custom" style="padding: 8px 20px; font-size: 12px; box-shadow: 0 2px 10px rgba(230,57,70,0.3);">
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
                <h5 class="modal-title" style="font-weight: 800; font-size: 1.05rem; color: var(--text-primary);">
                    Catat Mutasi / Perubahan Saldo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="mutationForm" onsubmit="submitMutationForm(event)">
                <input type="hidden" id="formMutGoalId" value="">
                <input type="hidden" id="formMutAllocId" value="">
                <input type="hidden" id="formMutType" value="deposit">

                <div class="modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                    
                    <!-- Pos Info Pill -->
                    <div style="background: var(--surface-2); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px 14px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Pos Penempatan Target:</div>
                        <div id="formMutAllocName" style="font-size: 13.5px; font-weight: 800; color: var(--text-primary); margin-top: 2px;">-</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            Saldo Saat Ini: <strong id="formMutCurrentBalance" style="color: var(--success);">Rp 0</strong>
                        </div>
                    </div>

                    <!-- Custom Mutation Segmented Card Selection -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; display: block;">
                            Jenis Mutasi <span style="color: var(--primary);">*</span>
                        </label>
                        <div class="mutation-type-segment">
                            <div class="mutation-type-card selected-deposit" id="mutCardDeposit" onclick="selectMutationType('deposit')">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16,185,129,0.18); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                                    <i class="bi bi-plus-circle-fill"></i>
                                </div>
                                <div>
                                    <div style="font-size: 12px; font-weight: 800; color: #10b981;">Tambah Setoran</div>
                                    <div style="font-size: 9.5px; color: var(--text-muted);">Menambah saldo pos</div>
                                </div>
                            </div>
                            <div class="mutation-type-card" id="mutCardWithdraw" onclick="selectMutationType('withdraw')">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(239,68,68,0.18); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                                    <i class="bi bi-dash-circle-fill"></i>
                                </div>
                                <div>
                                    <div style="font-size: 12px; font-weight: 800; color: var(--text-muted);">Tarik Dana</div>
                                    <div style="font-size: 9.5px; color: var(--text-muted);">Mengurangi saldo pos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nominal Mutasi -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nominal Mutasi (Rp) <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formMutAmount" class="form-control-custom" placeholder="Misal: 500.000" required oninput="formatCurrencyInput(this)" style="width: 100%; padding: 10px 14px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 14px; font-weight: 800;">
                    </div>

                    <!-- Date & Notes -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Tanggal Mutasi
                            </label>
                            <input type="date" id="formMutDate" value="<?= date('Y-m-d') ?>" class="form-control-custom" style="width: 100%; padding: 9px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12px; color-scheme: dark;">
                        </div>
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Catatan / Keterangan
                            </label>
                            <input type="text" id="formMutNotes" class="form-control-custom" placeholder="Misal: Untung toko bulan Mei" style="width: 100%; padding: 9px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 14px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 8px 16px; font-size: 12px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitMut" class="btn-primary-custom" style="padding: 8px 20px; font-size: 12px; box-shadow: 0 2px 10px rgba(230,57,70,0.3);">
                        Simpan Mutasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- JAVASCRIPT LOGIC & CUSTOM COMPONENT CONTROLLERS         -->
<!-- ======================================================== -->
<script>
let allGoals = [];
let activeGoal = null;
let currentFilter = 'all';
let searchKeyword = '';

// Preset master data for custom selectors
const CATEGORY_PRESETS = [
    { value: 'Kendaraan', label: 'Kendaraan (Mobil/Motor)', icon: 'bi-car-front-fill', bg: 'rgba(99,102,241,0.15)', color: '#818cf8', sub: 'Mobil, motor, service' },
    { value: 'Properti', label: 'Properti & Rumah', icon: 'bi-house-heart-fill', bg: 'rgba(16,185,129,0.15)', color: '#10b981', sub: 'Beli rumah, DP, renovasi' },
    { value: 'Dana Darurat', label: 'Dana Darurat', icon: 'bi-shield-check', bg: 'rgba(239,68,68,0.15)', color: '#ef4444', sub: 'Dana siaga 3-6 bulan' },
    { value: 'Investasi', label: 'Investasi & Saham', icon: 'bi-graph-up-arrow', bg: 'rgba(59,130,246,0.15)', color: '#3b82f6', sub: 'Bibit, saham, reksadana' },
    { value: 'Modal Usaha', label: 'Modal Usaha / Toko', icon: 'bi-shop', bg: 'rgba(245,158,11,0.15)', color: '#f59e0b', sub: 'Stok toko, ekspansi bisnis' },
    { value: 'Liburan', label: 'Liburan & Healing', icon: 'bi-airplane-fill', bg: 'rgba(236,72,153,0.15)', color: '#ec4899', sub: 'Wisata, tiket, hotel' },
    { value: 'Pendidikan', label: 'Pendidikan', icon: 'bi-mortarboard-fill', bg: 'rgba(168,85,247,0.15)', color: '#a855f7', sub: 'Sekolah, kursus, kuliah' },
    { value: 'Gadget', label: 'Gadget & Elektronik', icon: 'bi-laptop', bg: 'rgba(20,184,166,0.15)', color: '#14b8a6', sub: 'Laptop, smartphone, kamera' },
    { value: 'Lainnya', label: 'Lainnya', icon: 'bi-piggy-bank-fill', bg: 'rgba(100,116,139,0.15)', color: '#94a3b8', sub: 'Tujuan tabungan umum' }
];

const ICON_PRESETS = [
    { icon: 'bi-piggy-bank-fill', label: 'Celengan' },
    { icon: 'bi-car-front-fill', label: 'Mobil' },
    { icon: 'bi-house-heart-fill', label: 'Rumah' },
    { icon: 'bi-shield-check', label: 'Darurat' },
    { icon: 'bi-graph-up-arrow', label: 'Investasi' },
    { icon: 'bi-shop', label: 'Toko' },
    { icon: 'bi-airplane-fill', label: 'Liburan' },
    { icon: 'bi-laptop', label: 'Gadget' },
    { icon: 'bi-gem', label: 'Emas/Cuan' },
    { icon: 'bi-trophy-fill', label: 'Impian' }
];

const COLOR_PRESETS = [
    { color: '#6366f1', name: 'Indigo' },
    { color: '#10b981', name: 'Emerald' },
    { color: '#3b82f6', name: 'Ocean' },
    { color: '#f59e0b', name: 'Amber' },
    { color: '#e63946', name: 'Coral Red' },
    { color: '#a855f7', name: 'Purple' },
    { color: '#ec4899', name: 'Rose Pink' },
    { color: '#14b8a6', name: 'Teal' }
];

const ALLOC_TYPE_PRESETS = [
    { value: 'Toko / Kas', label: 'Toko / Kas (Uang Berputar)', icon: 'bi-shop', bg: 'rgba(245,158,11,0.15)', color: '#f59e0b', sub: 'Uang di kasir, brankas, modal toko' },
    { value: 'Investasi', label: 'Investasi (Bibit, Saham, Reksadana)', icon: 'bi-graph-up-arrow', bg: 'rgba(59,130,246,0.15)', color: '#3b82f6', sub: 'Bibit, Ajaib, Reksadana, Emas' },
    { value: 'Bank / Rekening', label: 'Bank / Rekening (SeaBank, BCA)', icon: 'bi-bank', bg: 'rgba(99,102,241,0.15)', color: '#818cf8', sub: 'SeaBank, BCA, Mandiri, BRI' },
    { value: 'Piutang / Hutang', label: 'Piutang / Pinjaman', icon: 'bi-person-lines-fill', bg: 'rgba(236,72,153,0.15)', color: '#ec4899', sub: 'Piutang pelanggan/rekanan' },
    { value: 'Lainnya', label: 'Lainnya (Fisik / Dompet)', icon: 'bi-wallet2', bg: 'rgba(100,116,139,0.15)', color: '#94a3b8', sub: 'Dompet fisik, amplop, dll' }
];

document.addEventListener('DOMContentLoaded', () => {
    initCustomSelectPickers();
    loadGoals();
    loadSummary();

    // Close any open custom dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.custom-select-picker')) {
            document.querySelectorAll('.custom-select-menu.show').forEach(m => m.classList.remove('show'));
            document.querySelectorAll('.custom-select-trigger.open').forEach(t => t.classList.remove('open'));
        }
    });
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
// INITIALIZE CUSTOM SELECTORS & PICKERS
// ----------------------------------------------------
function initCustomSelectPickers() {
    // 1. Render Category Options
    const catList = document.getElementById('categoryOptionsList');
    if (catList) {
        catList.innerHTML = CATEGORY_PRESETS.map(cat => `
            <div class="custom-select-option ${cat.value === 'Kendaraan' ? 'selected' : ''}" onclick="selectCategoryOption('${cat.value}', '${cat.label}', '${cat.icon}', '${cat.bg}', '${cat.color}')">
                <div class="custom-select-content">
                    <div class="custom-select-icon" style="background:${cat.bg};color:${cat.color};">
                        <i class="bi ${cat.icon}"></i>
                    </div>
                    <div>
                        <div class="custom-select-label">${cat.label}</div>
                        <div class="custom-select-sub">${cat.sub}</div>
                    </div>
                </div>
                <i class="bi bi-check-lg custom-select-check"></i>
            </div>
        `).join('');
    }

    // 2. Render Icon Grid
    const iconGrid = document.getElementById('goalIconGrid');
    if (iconGrid) {
        iconGrid.innerHTML = ICON_PRESETS.map(item => `
            <div class="icon-picker-item ${item.icon === 'bi-piggy-bank-fill' ? 'selected' : ''}" onclick="selectGoalIcon('${item.icon}', this)">
                <i class="bi ${item.icon}"></i>
                <span>${item.label}</span>
            </div>
        `).join('');
    }

    // 3. Render Color Grid
    const colorGrid = document.getElementById('goalColorGrid');
    if (colorGrid) {
        colorGrid.innerHTML = COLOR_PRESETS.map(item => `
            <div class="color-swatch-item ${item.color === '#6366f1' ? 'selected' : ''}" style="background:${item.color};" onclick="selectGoalColor('${item.color}', this)" title="${item.name}">
                <i class="bi bi-check"></i>
            </div>
        `).join('');
    }

    // 4. Render Allocation Type Options
    const allocTypeList = document.getElementById('allocTypeOptionsList');
    if (allocTypeList) {
        allocTypeList.innerHTML = ALLOC_TYPE_PRESETS.map(type => `
            <div class="custom-select-option ${type.value === 'Bank / Rekening' ? 'selected' : ''}" onclick="selectAllocTypeOption('${type.value}', '${type.label}', '${type.icon}', '${type.bg}', '${type.color}', '${type.sub}')">
                <div class="custom-select-content">
                    <div class="custom-select-icon" style="background:${type.bg};color:${type.color};">
                        <i class="bi ${type.icon}"></i>
                    </div>
                    <div>
                        <div class="custom-select-label">${type.label}</div>
                        <div class="custom-select-sub">${type.sub}</div>
                    </div>
                </div>
                <i class="bi bi-check-lg custom-select-check"></i>
            </div>
        `).join('');
    }
}

function toggleCustomDropdown(menuId, e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById(menuId);
    if (!menu) return;
    const trigger = menu.previousElementSibling;
    const isOpen = menu.classList.contains('show');

    // Close all other custom dropdowns
    document.querySelectorAll('.custom-select-menu.show').forEach(m => m.classList.remove('show'));
    document.querySelectorAll('.custom-select-trigger.open').forEach(t => t.classList.remove('open'));

    if (!isOpen) {
        menu.classList.add('show');
        if (trigger) trigger.classList.add('open');
        const searchInput = menu.querySelector('.custom-select-search input');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
            filterDropdownOptions(menuId, '');
        }
    }
}

function filterDropdownOptions(menuId, query) {
    const menu = document.getElementById(menuId);
    if (!menu) return;
    const q = (query || '').toLowerCase().trim();
    menu.querySelectorAll('.custom-select-option').forEach(opt => {
        const text = opt.textContent.toLowerCase();
        opt.style.display = text.includes(q) ? 'flex' : 'none';
    });
}

function selectCategoryOption(value, label, icon, bg, color) {
    document.getElementById('formGoalCategory').value = value;
    document.getElementById('categorySelectedLabel').textContent = label;
    const iconEl = document.getElementById('categorySelectedIcon');
    iconEl.style.background = bg;
    iconEl.style.color = color;
    iconEl.innerHTML = `<i class="bi ${icon}"></i>`;

    const menu = document.getElementById('categoryPickerMenu');
    menu.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('selected'));
    if (event && event.currentTarget) event.currentTarget.classList.add('selected');
    menu.classList.remove('show');
    document.getElementById('categoryPickerTrigger').classList.remove('open');
}

function selectGoalIcon(iconClass, el) {
    document.getElementById('formGoalIcon').value = iconClass;
    document.querySelectorAll('#goalIconGrid .icon-picker-item').forEach(i => i.classList.remove('selected'));
    if (el) el.classList.add('selected');
}

function selectGoalColor(colorHex, el) {
    document.getElementById('formGoalColor').value = colorHex;
    document.querySelectorAll('#goalColorGrid .color-swatch-item').forEach(c => c.classList.remove('selected'));
    if (el) el.classList.add('selected');
}

function selectStatusOption(value, label, icon, color) {
    document.getElementById('formGoalStatus').value = value;
    document.getElementById('statusSelectedLabel').textContent = label;
    const iconEl = document.getElementById('statusSelectedIcon');
    iconEl.style.background = `${color}22`;
    iconEl.style.color = color;
    iconEl.innerHTML = `<i class="bi ${icon}"></i>`;

    const menu = document.getElementById('statusPickerMenu');
    menu.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('selected'));
    if (event && event.currentTarget) event.currentTarget.classList.add('selected');
    menu.classList.remove('show');
    document.getElementById('statusPickerTrigger').classList.remove('open');
}

function selectAllocTypeOption(value, label, icon, bg, color, sub) {
    document.getElementById('formAllocType').value = value;
    document.getElementById('allocTypeSelectedLabel').textContent = label;
    document.getElementById('allocTypeSelectedSub').textContent = sub;
    const iconEl = document.getElementById('allocTypeSelectedIcon');
    iconEl.style.background = bg;
    iconEl.style.color = color;
    iconEl.innerHTML = `<i class="bi ${icon}"></i>`;

    const menu = document.getElementById('allocTypePickerMenu');
    menu.querySelectorAll('.custom-select-option').forEach(opt => opt.classList.remove('selected'));
    if (event && event.currentTarget) event.currentTarget.classList.add('selected');
    menu.classList.remove('show');
    document.getElementById('allocTypePickerTrigger').classList.remove('open');
}

function selectMutationType(type) {
    document.getElementById('formMutType').value = type;
    const depCard = document.getElementById('mutCardDeposit');
    const withCard = document.getElementById('mutCardWithdraw');

    if (type === 'deposit') {
        depCard.className = 'mutation-type-card selected-deposit';
        depCard.querySelector('div:last-child div:first-child').style.color = '#10b981';
        withCard.className = 'mutation-type-card';
        withCard.querySelector('div:last-child div:first-child').style.color = 'var(--text-muted)';
    } else {
        withCard.className = 'mutation-type-card selected-withdraw';
        withCard.querySelector('div:last-child div:first-child').style.color = '#ef4444';
        depCard.className = 'mutation-type-card';
        depCard.querySelector('div:last-child div:first-child').style.color = 'var(--text-muted)';
    }
}

// ----------------------------------------------------
// SEARCH & FILTER GOALS
// ----------------------------------------------------
function onSearchGoals(keyword) {
    searchKeyword = (keyword || '').toLowerCase().trim();
    const btnClear = document.getElementById('btnClearSearch');
    if (btnClear) btnClear.style.display = searchKeyword ? 'inline-block' : 'none';
    renderGoalsGrid();
}

function clearSearchGoals() {
    const input = document.getElementById('searchGoalsInput');
    if (input) input.value = '';
    onSearchGoals('');
}

function filterGoals(status, btnEl) {
    currentFilter = status;
    document.querySelectorAll('.filter-btn-group .btn-filter').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    renderGoalsGrid();
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

function renderGoalsGrid() {
    const container = document.getElementById('goalsGridContainer');
    let filtered = allGoals;

    // Filter by status
    if (currentFilter !== 'all') {
        filtered = filtered.filter(g => g.status === currentFilter);
    }

    // Filter by search keyword
    if (searchKeyword) {
        filtered = filtered.filter(g => {
            const nameMatch = (g.name || '').toLowerCase().includes(searchKeyword);
            const catMatch = (g.category || '').toLowerCase().includes(searchKeyword);
            const allocMatch = (g.allocations || []).some(a => (a.name || '').toLowerCase().includes(searchKeyword) || (a.institution || '').toLowerCase().includes(searchKeyword));
            return nameMatch || catMatch || allocMatch;
        });
    }

    if (filtered.length === 0) {
        container.innerHTML = `
            <div style="grid-column:1/-1;text-align:center;padding:48px 20px;background:var(--surface-1);border:1px dashed var(--border-color);border-radius:var(--radius-lg);">
                <div style="width:56px;height:56px;border-radius:50%;background:rgba(99,102,241,0.12);color:#818cf8;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 14px;">
                    <i class="bi bi-piggy-bank"></i>
                </div>
                <h5 style="font-weight:800;margin:0 0 6px 0;color:var(--text-primary);">
                    ${searchKeyword ? 'Tidak ada target yang cocok' : 'Belum Ada Target Tabungan'}
                </h5>
                <p style="font-size:12px;color:var(--text-muted);max-width:420px;margin:0 auto 18px;">
                    ${searchKeyword ? 'Coba cari dengan kata kunci lain atau bersihkan pencarian.' : 'Mulai rancang impian Anda seperti Tabungan Mobil, Dana Darurat, atau Modal Usaha dengan mengelompokkan uang di Toko, Bibit, Bank, dll.'}
                </p>
                <button class="btn-primary-custom" onclick="${searchKeyword ? 'clearSearchGoals()' : 'openAddGoalModal()'}" style="padding:9px 18px;border-radius:var(--radius-md);font-size:12px;display:inline-flex;align-items:center;gap:6px;">
                    <i class="bi ${searchKeyword ? 'bi-arrow-counterclockwise' : 'bi-plus-circle-fill'}"></i>
                    ${searchKeyword ? 'Reset Pencarian' : 'Buat Target Pertama Sekarang'}
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
                                    <span class="badge-custom badge-primary" style="font-size:9.5px;">${escapeHtml(goal.category || 'Lainnya')}</span>
                                    ${isAchieved ? '<span class="badge-custom badge-success" style="font-size:9.5px;">Tercapai 🎉</span>' : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Menu -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown" style="box-shadow:none;">
                                <i class="bi bi-three-dots-vertical" style="font-size:1.1rem;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow" style="font-size:12px;border:1px solid var(--border-color);border-radius:var(--radius-md);">
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="openGoalDetail(${goal.id})"><i class="bi bi-eye me-2 text-info"></i> Buka Detail</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="openEditGoalModal(${goal.id})"><i class="bi bi-pencil me-2 text-warning"></i> Edit Goal</a></li>
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
                    <button class="btn-primary-custom" onclick="openGoalDetail(${goal.id})" style="padding:8px 12px;border-radius:var(--radius-sm);font-size:11.5px;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <i class="bi bi-folder2-open"></i> Detail &amp; Kelola Alokasi
                    </button>
                    <button class="btn-primary-custom" onclick="openAddAllocationModalDirect(${goal.id})" title="Tambah Pos Alokasi Uang" style="background:var(--surface-2);color:var(--text-primary);border:1px solid var(--border-color);padding:8px 12px;border-radius:var(--radius-sm);font-size:11.5px;">
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
            <div style="text-align:center;padding:28px 10px;background:var(--surface-2);border-radius:var(--radius-md);border:1px dashed var(--border-color);">
                <i class="bi bi-wallet2" style="font-size:1.6rem;color:var(--text-muted);display:block;margin-bottom:6px;"></i>
                <div style="font-size:13px;font-weight:700;color:var(--text-primary);">Belum ada pos penempatan uang</div>
                <div style="font-size:11px;color:var(--text-muted);margin:2px 0 12px 0;">Contoh: Uang di Toko (3jt), di Bibit (4jt), di SeaBank (350rb)</div>
                <button class="btn-primary-custom" onclick="openAddAllocationModal()" style="padding:7px 16px;border-radius:var(--radius-sm);font-size:11.5px;">
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
                            <span style="font-size:13.5px;font-weight:800;color:var(--text-primary);">${escapeHtml(alloc.name)}</span>
                            <span class="badge-custom badge-primary" style="font-size:9.5px;">${escapeHtml(alloc.account_type)}</span>
                            ${alloc.institution ? `<span style="font-size:10.5px;color:var(--text-muted);">(${escapeHtml(alloc.institution)})</span>` : ''}
                        </div>
                        ${alloc.notes ? `<div style="font-size:10.5px;color:var(--text-muted);margin-top:2px;">${escapeHtml(alloc.notes)}</div>` : ''}
                    </div>
                </div>

                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:13.5px;font-weight:800;color:var(--success);">${formatRupiah(alloc.amount)}</div>
                    <div style="font-size:9.5px;color:var(--text-muted);font-weight:700;">${pct}% dari target terkumpul</div>
                </div>

                <!-- Action buttons -->
                <div style="display:flex;gap:5px;flex-shrink:0;">
                    <button class="btn-primary-custom" onclick="openMutationModal(${alloc.id}, '${escapeHtml(alloc.name)}', ${alloc.amount})" title="Tambah / Tarik Saldo" style="padding:6px 10px;border-radius:var(--radius-sm);font-size:11px;">
                        <i class="bi bi-arrow-left-right"></i> Mutasi
                    </button>
                    <button class="btn-primary-custom" onclick="openEditAllocationModal(${alloc.id})" title="Edit Pos" style="background:var(--surface-3);color:var(--text-primary);padding:6px 9px;border-radius:var(--radius-sm);font-size:11px;">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn-primary-custom" onclick="confirmDeleteAllocation(${alloc.id}, '${escapeHtml(alloc.name)}')" title="Hapus Pos" style="background:var(--danger-bg);color:var(--danger);padding:6px 9px;border-radius:var(--radius-sm);font-size:11px;">
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
        container.innerHTML = `<div style="text-align:center;color:var(--text-muted);font-size:11px;padding:24px;">Belum ada riwayat mutasi dana.</div>`;
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
    document.getElementById('formGoalDesc').value = '';
    document.getElementById('formGoalStatusWrapper').style.display = 'none';
    document.getElementById('btnSubmitGoal').textContent = 'Simpan Goal';

    // Reset Category
    const defCat = CATEGORY_PRESETS[0];
    selectCategoryOption(defCat.value, defCat.label, defCat.icon, defCat.bg, defCat.color);

    // Reset Icon
    selectGoalIcon('bi-piggy-bank-fill', document.querySelector('#goalIconGrid .icon-picker-item'));

    // Reset Color
    selectGoalColor('#6366f1', document.querySelector('#goalColorGrid .color-swatch-item'));

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
    document.getElementById('formGoalDesc').value = goal.description || '';
    document.getElementById('formGoalStatusWrapper').style.display = 'block';
    document.getElementById('btnSubmitGoal').textContent = 'Perbarui Goal';

    // Set Category
    const cat = CATEGORY_PRESETS.find(c => c.value === goal.category) || CATEGORY_PRESETS[CATEGORY_PRESETS.length - 1];
    selectCategoryOption(cat.value, cat.label, cat.icon, cat.bg, cat.color);

    // Set Icon
    const iconEl = Array.from(document.querySelectorAll('#goalIconGrid .icon-picker-item')).find(el => el.querySelector(`.${goal.icon}`));
    selectGoalIcon(goal.icon || 'bi-piggy-bank-fill', iconEl);

    // Set Color
    const colorEl = Array.from(document.querySelectorAll('#goalColorGrid .color-swatch-item')).find(el => el.getAttribute('style')?.includes(goal.color));
    selectGoalColor(goal.color || '#6366f1', colorEl);

    // Set Status
    const statusVal = goal.status || 'in_progress';
    const statusMap = {
        'in_progress': { label: 'Sedang Berjalan', icon: 'bi-hourglass-split', color: '#3b82f6' },
        'achieved': { label: 'Tercapai 🎉', icon: 'bi-check-circle-fill', color: '#10b981' },
        'paused': { label: 'Dijeda (Paused)', icon: 'bi-pause-circle-fill', color: '#f59e0b' }
    };
    const curStatus = statusMap[statusVal] || statusMap['in_progress'];
    selectStatusOption(statusVal, curStatus.label, curStatus.icon, curStatus.color);

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
    document.getElementById('formAllocInstitution').value = '';
    document.getElementById('formAllocAmount').value = '';
    document.getElementById('formAllocNotes').value = '';
    document.getElementById('btnSubmitAlloc').textContent = 'Simpan Pos';

    // Reset Type to Bank / Rekening
    const defType = ALLOC_TYPE_PRESETS[2];
    selectAllocTypeOption(defType.value, defType.label, defType.icon, defType.bg, defType.color, defType.sub);

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
    document.getElementById('formAllocInstitution').value = alloc.institution || '';
    document.getElementById('formAllocAmount').value = Number(alloc.amount).toLocaleString('id-ID');
    document.getElementById('formAllocNotes').value = alloc.notes || '';
    document.getElementById('btnSubmitAlloc').textContent = 'Perbarui Pos';

    // Set Type
    const type = ALLOC_TYPE_PRESETS.find(t => t.value === alloc.account_type) || ALLOC_TYPE_PRESETS[2];
    selectAllocTypeOption(type.value, type.label, type.icon, type.bg, type.color, type.sub);

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

    selectMutationType('deposit');

    const modal = new bootstrap.Modal(document.getElementById('modalMutationForm'));
    modal.show();
}

async function submitMutationForm(e) {
    e.preventDefault();
    const type = document.getElementById('formMutType').value || 'deposit';

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
