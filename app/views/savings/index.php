<?php
/**
 * Savings & Financial Goals View (Compact & Ultra-Modern Responsive UI)
 *
 * @var array $currentUser
 * @var array $summary
 * @var array $goals
 * @var string $csrfToken
 */
$csrfToken = $csrfToken ?? ($this->security ? $this->security->getCSRFToken() : '');
require_once APP_PATH . '/services/MutualFundService.php';
?>

<style>
/* ===== SAVINGS & GOALS ULTRA-MODERN COMPACT STYLES ===== */
.savings-container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 12px 90px 12px;
    box-sizing: border-box;
    overflow-x: hidden;
}

@media (min-width: 768px) {
    .savings-container {
        padding: 0 20px 90px 20px;
    }
}

/* Header Card */
.savings-header-card {
    background: var(--gradient-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    margin-bottom: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    width: 100%;
    box-sizing: border-box;
}

/* PPOB Live Balance Banner */
.savings-ppob-banner {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(99, 102, 241, 0.08) 100%);
    border: 1px solid rgba(59, 130, 246, 0.22);
    border-radius: var(--radius-lg);
    padding: 12px 18px;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    box-sizing: border-box;
}

.savings-ppob-banner:hover {
    border-color: rgba(59, 130, 246, 0.4);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.08);
}

.savings-ppob-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.savings-ppob-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.savings-ppob-info {
    min-width: 0;
}

.savings-ppob-label {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.savings-ppob-val-wrapper {
    margin-top: 2px;
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.savings-ppob-value {
    font-size: 1.3rem;
    font-weight: 800;
    color: #3b82f6;
    letter-spacing: -0.2px;
    line-height: 1.2;
}

.savings-ppob-actions {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.btn-ppob-action {
    background: var(--surface-1);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    padding: 6px 12px;
    border-radius: var(--radius-md);
    font-size: 11.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}

.btn-ppob-action:hover {
    background: var(--surface-2);
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-1px);
}

.btn-ppob-action.primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #ffffff;
    border: none;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
}

.btn-ppob-action.primary:hover {
    box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
    color: #ffffff;
}

/* KPI Summary Grid */
.savings-kpi-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 16px;
    width: 100%;
    box-sizing: border-box;
}

@media (min-width: 992px) {
    .savings-kpi-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }
}

.savings-kpi-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 3px 12px rgba(0,0,0,0.04);
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
    overflow: hidden;
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.2s ease;
}

.savings-kpi-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
}

.kpi-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
    min-width: 0;
}

.kpi-title-text {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1;
}

.kpi-icon-box {
    width: 26px;
    height: 26px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
}

.kpi-value-text {
    font-size: clamp(0.95rem, 3.8vw, 1.25rem);
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    margin: 2px 0;
}

.kpi-sub-text {
    font-size: 9px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
}

/* Distribution Card */
.savings-dist-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 14px 16px;
    margin-bottom: 16px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.04);
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

.dist-bar-wrapper {
    height: 9px;
    border-radius: 5px;
    background: var(--surface-2);
    overflow: hidden;
    display: flex;
    margin: 10px 0;
    width: 100%;
}

.dist-bar-segment {
    height: 100%;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.dist-pills-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    margin-top: 10px;
    width: 100%;
    box-sizing: border-box;
}

@media (min-width: 768px) {
    .dist-pills-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }
}

.dist-pill-item {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 8px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
    overflow: hidden;
}

/* Control Bar (Search & Filters) */
.savings-control-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    gap: 10px;
    flex-wrap: wrap;
    width: 100%;
    box-sizing: border-box;
}

.savings-search-box {
    flex: 1;
    min-width: 180px;
    max-width: 360px;
}

/* Goals Grid */
.savings-goals-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 14px;
    margin-top: 6px;
    width: 100%;
    box-sizing: border-box;
}

@media (min-width: 768px) {
    .savings-goals-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1200px) {
    .savings-goals-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

.goal-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    box-shadow: 0 3px 14px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.goal-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.goal-card-top-accent {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3.5px;
}

.goal-icon-badge {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.goal-progress-bar {
    height: 7px;
    background: var(--surface-2);
    border-radius: 5px;
    overflow: hidden;
    margin: 8px 0 6px 0;
}

.goal-progress-fill {
    height: 100%;
    border-radius: 5px;
    transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.alloc-pill {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 7px;
    padding: 5px 9px;
    font-size: 10.5px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    min-width: 0;
}

.alloc-pill-tag {
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
}

/* ======================================================== */
/* COMPACT & STRUCTURED ALLOCATION CARDS (NO OVERLAPPING!)  */
/* ======================================================== */
.alloc-card-item {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
    gap: 9px;
    transition: border-color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    min-width: 0;
    overflow: hidden;
}

.alloc-card-item:hover {
    border-color: var(--primary);
}

.alloc-card-top-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    min-width: 0;
}

.alloc-card-left-info {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    flex: 1;
}

.alloc-card-icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}

.alloc-card-name-group {
    min-width: 0;
    flex: 1;
}

.alloc-card-name {
    font-size: 13px;
    font-weight: 800;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.25;
}

.alloc-card-meta {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 2px;
    flex-wrap: wrap;
}

.alloc-card-actions {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
}

.btn-alloc-mutate {
    background: var(--primary);
    color: #ffffff !important;
    border: none;
    padding: 5px 10px;
    border-radius: var(--radius-sm);
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
    box-shadow: 0 2px 6px rgba(230,57,70,0.25);
    white-space: nowrap;
}

.btn-alloc-mutate:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

.btn-alloc-icon-action {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    width: 28px;
    height: 28px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-alloc-icon-action:hover {
    border-color: var(--border-active);
    background: var(--surface-3);
}

.btn-alloc-icon-action.btn-delete:hover {
    border-color: var(--danger);
    background: var(--danger-bg);
    color: var(--danger);
}

/* Allocation Card Body (Amount & Share Bar) */
.alloc-card-bottom-row {
    background: var(--bg-primary);
    border: 1px solid rgba(255,255,255,0.04);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.alloc-amount-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.alloc-amount-val {
    font-size: 13px;
    font-weight: 800;
    color: var(--success);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.alloc-share-tag {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--text-muted);
    background: var(--surface-2);
    padding: 2px 7px;
    border-radius: 4px;
    white-space: nowrap;
    flex-shrink: 0;
}

.alloc-mini-bar {
    height: 4px;
    background: var(--surface-3);
    border-radius: 2px;
    overflow: hidden;
}

.alloc-mini-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.4s ease;
}

.alloc-notes-text {
    font-size: 10px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 1px;
}

/* ======================================================== */
/* MODAL GOAL DETAIL RESPONSIVE BANNER & COMPACT STATS     */
/* ======================================================== */
.modal-goal-banner {
    padding: 16px 18px;
    background: var(--gradient-card);
    border-bottom: 1px solid var(--border-color);
    position: relative;
}

@media (min-width: 768px) {
    .modal-goal-banner {
        padding: 20px 24px;
    }
}

.modal-stats-box {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    margin-top: 12px;
}

.stats-collected-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 8px;
    margin-bottom: 6px;
}

.stats-sub-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--border-color);
}

.stats-sub-item {
    min-width: 0;
}

.stats-sub-label {
    font-size: 9.5px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    display: block;
    margin-bottom: 2px;
}

.stats-sub-val {
    font-size: 0.92rem;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

/* Modern Segmented Navigation Tabs */
.detail-segmented-tabs {
    display: flex;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 3px;
    gap: 4px;
    margin-bottom: 14px;
}

.detail-tab-btn {
    flex: 1;
    text-align: center;
    padding: 7px 12px;
    border-radius: var(--radius-sm);
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
}

.detail-tab-btn.active {
    background: var(--primary);
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(230,57,70,0.3);
}

/* Timeline */
.timeline-item {
    position: relative;
    padding-left: 20px;
    padding-bottom: 14px;
    border-left: 2px solid var(--border-color);
}

.timeline-item:last-child {
    border-left-color: transparent;
    padding-bottom: 0;
}

.timeline-dot {
    position: absolute;
    left: -6px;
    top: 2px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

/* Custom Filter Buttons */
.filter-btn-group .btn-filter {
    padding: 6px 13px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    background: var(--surface-1);
    color: var(--text-muted);
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
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

/* Custom Select Dropdowns */
.custom-select-picker {
    position: relative;
    width: 100%;
}

.custom-select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 12px;
    background: var(--bg-primary);
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
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
    gap: 8px;
    min-width: 0;
    flex: 1;
}

.custom-select-icon {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

.custom-select-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.custom-select-sub {
    font-size: 10px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.custom-select-chevron {
    color: var(--text-muted);
    font-size: 11px;
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
    top: calc(100% + 5px);
    left: 0;
    right: 0;
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: 0 12px 36px rgba(0,0,0,0.3);
    z-index: 1060;
    overflow: hidden;
    padding: 5px;
    max-height: 250px;
    overflow-y: auto;
    animation: customMenuFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    backdrop-filter: blur(14px);
}

.custom-select-menu.show {
    display: block;
}

@keyframes customMenuFade {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-select-search {
    padding: 4px 6px 6px 6px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 4px;
}

.custom-select-search input {
    width: 100%;
    padding: 6px 8px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-size: 11px;
    outline: none;
}

.custom-select-search input:focus {
    border-color: var(--primary);
}

.custom-select-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 7px 10px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all 0.15s ease;
}

.custom-select-option:hover {
    background: var(--surface-2);
}

.custom-select-option.selected {
    background: rgba(230,57,70,0.08);
}

.custom-select-check {
    color: var(--primary);
    font-size: 13px;
    font-weight: bold;
    opacity: 0;
}

.custom-select-option.selected .custom-select-check {
    opacity: 1;
}

/* Icon Grid */
.icon-picker-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 6px;
}

.icon-picker-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    padding: 7px 3px;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--border-color);
    background: var(--bg-primary);
    cursor: pointer;
    transition: all 0.15s ease;
    color: var(--text-muted);
}

.icon-picker-item:hover {
    border-color: var(--primary);
    background: var(--surface-2);
    color: var(--text-primary);
}

.icon-picker-item.selected {
    border-color: var(--primary);
    background: rgba(230,57,70,0.12);
    color: var(--primary);
}

.icon-picker-item i {
    font-size: 1.15rem;
}

.icon-picker-item span {
    font-size: 9px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

/* Color Swatches Grid */
.color-picker-grid {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.color-swatch-item {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 13px;
    transition: transform 0.15s ease;
    border: 2px solid transparent;
}

.color-swatch-item:hover {
    transform: scale(1.15);
}

.color-swatch-item.selected {
    transform: scale(1.15);
    border-color: #ffffff;
    box-shadow: 0 0 0 2.5px rgba(255,255,255,0.4);
}

.color-swatch-item i {
    opacity: 0;
}

.color-swatch-item.selected i {
    opacity: 1;
}

/* Mutation Segmented Cards */
.mutation-type-segment {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.mutation-type-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--border-color);
    background: var(--bg-primary);
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}

.mutation-type-card.selected-deposit {
    border-color: var(--success);
    background: rgba(16,185,129,0.12);
}

.mutation-type-card.selected-withdraw {
    border-color: var(--danger);
    background: rgba(239,68,68,0.12);
}

/* Ultra-Modern Custom Toasts & Notification Center */
.savings-toast-container {
    position: fixed;
    top: 24px;
    right: 20px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
    max-width: 90vw;
    width: 380px;
}

.savings-toast-item {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 14px 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.35);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    pointer-events: auto;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    animation: savingsToastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}

.savings-toast-item.toast-leave {
    opacity: 0;
    transform: translateY(-16px) scale(0.95);
}

.savings-toast-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.savings-toast-content {
    min-width: 0;
    flex: 1;
}

.savings-toast-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 2px;
    line-height: 1.25;
}

.savings-toast-msg {
    font-size: 11px;
    color: var(--text-muted);
    line-height: 1.35;
}

.savings-toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    width: 100%;
    transform-origin: left;
    animation: savingsToastProgress 3s linear forwards;
}

@keyframes savingsToastSlideIn {
    from {
        opacity: 0;
        transform: translateY(-24px) scale(0.92);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes savingsToastProgress {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}

/* Savings Tab Navigation */
.savings-tab-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 10px;
    overflow-x: auto;
    width: 100%;
    box-sizing: border-box;
}

.btn-tab-pill {
    background: var(--surface-1);
    color: var(--text-muted);
    border: 1px solid var(--border-color);
    padding: 8px 16px;
    border-radius: var(--radius-md);
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
}

.btn-tab-pill:hover {
    background: var(--surface-2);
    color: var(--text-primary);
    border-color: var(--primary);
}

.btn-tab-pill.active {
    background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(59,130,246,0.15));
    color: var(--primary);
    border-color: var(--primary);
    box-shadow: 0 2px 10px rgba(99,102,241,0.15);
}

/* Mutual Funds Specific Styles */
.mf-kpi-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 16px;
    width: 100%;
    box-sizing: border-box;
}

@media (min-width: 992px) {
    .mf-kpi-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }
}

.mf-kpi-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 3px 12px rgba(0,0,0,0.04);
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.2s ease;
}

.mf-kpi-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
}

.mf-card-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
    width: 100%;
    box-sizing: border-box;
}

@media (min-width: 768px) {
    .mf-card-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1200px) {
    .mf-card-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

.mf-card {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.mf-card:hover {
    border-color: rgba(99, 102, 241, 0.4);
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.08);
    transform: translateY(-3px);
}

.mf-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
}

.mf-card-title {
    font-size: 13.5px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.3;
    margin-bottom: 3px;
}

.mf-card-house {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
}

.mf-metric-box {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 10px 12px;
    margin-bottom: 12px;
}

.mf-metric-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11.5px;
    padding: 3px 0;
}

.mf-metric-label {
    color: var(--text-muted);
    font-weight: 600;
}

.mf-metric-val {
    color: var(--text-primary);
    font-weight: 700;
}

.mf-val-highlight {
    font-size: 1.15rem;
    font-weight: 800;
    color: #3b82f6;
    letter-spacing: -0.2px;
}

.mf-pnl-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 800;
}

.mf-pnl-pill.profit {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.mf-pnl-pill.loss {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.mf-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 10px;
    border-top: 1px solid var(--border-color);
    font-size: 11px;
    color: var(--text-muted);
}
</style>

<div class="page-section savings-container">
    <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">

    <!-- Header Card -->
    <div class="savings-header-card">
        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(99,102,241,0.2)); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
                <i class="bi bi-piggy-bank-fill"></i>
            </div>
            <div style="min-width: 0;">
                <h4 style="font-weight: 800; font-size: 1.1rem; margin: 0; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    Savings &amp; Target Keuangan
                </h4>
                <p style="font-size: 11px; color: var(--text-muted); margin: 2px 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    Pantau tabungan, investasi, uang toko &amp; alokasi dana per target
                </p>
            </div>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <button class="btn-primary-custom" onclick="captureAllSnapshotsManual()" title="Simpan / Backup Snapshot Hari Ini" style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border: none; padding: 7px 13px; border-radius: var(--radius-md); font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 3px 10px rgba(16,185,129,0.25);">
                <i class="bi bi-camera-fill"></i> Simpan History Hari Ini
            </button>
            <button class="btn-primary-custom" onclick="openGlobalHistoryModal()" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 13px; border-radius: var(--radius-md); font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px;">
                <i class="bi bi-clock-history"></i> Riwayat Tabungan
            </button>
            <button class="btn-primary-custom" onclick="openAddGoalModal()" style="padding: 7px 14px; border-radius: var(--radius-md); font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px;">
                <i class="bi bi-plus-circle-fill"></i> Tambah Goal
            </button>
            <a href="<?= BASE_URL ?>" class="btn-primary-custom" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 12px; border-radius: var(--radius-md); font-size: 11.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Saldo PPOB Live Widget Banner -->
    <div class="savings-ppob-banner" id="savingsPpobBanner">
        <div class="savings-ppob-left">
            <div class="savings-ppob-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="savings-ppob-info">
                <div class="savings-ppob-label">
                    <span>Saldo PPOB Saat Ini (Digiflazz)</span>
                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25" id="ppobStatusLiveBadge" style="font-size: 9.5px; font-weight: 700; padding: 2px 7px;">
                        <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Live
                    </span>
                </div>
                <div class="savings-ppob-val-wrapper">
                    <span class="savings-ppob-value" id="topPpobBalanceVal">
                        <span class="spinner-border spinner-border-sm text-primary" style="width: 1.1rem; height: 1.1rem; vertical-align: middle;"></span> <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Memuat saldo...</span>
                    </span>
                </div>
            </div>
        </div>
        <div class="savings-ppob-actions">
            <button type="button" class="btn-ppob-action" onclick="refreshSavingsPpobBalance()" id="btnRefreshPpobBalance" title="Perbarui Saldo Real-Time">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button type="button" class="btn-ppob-action" onclick="copyPpobBalanceAmount()" id="btnCopyPpobBalance" title="Salin Nominal Saldo">
                <i class="bi bi-clipboard"></i> Salin Nominal
            </button>
            <a href="<?= BASE_URL ?>ppob" class="btn-ppob-action primary" title="Buka Menu Transaksi PPOB">
                <i class="bi bi-box-arrow-up-right"></i> Buka PPOB
            </a>
        </div>
    </div>

    <!-- Savings & Portfolio Navigation Tabs -->
    <div class="savings-tab-nav" style="display: flex; gap: 8px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
        <button type="button" class="btn-tab-pill active" id="tabBtnGoals" onclick="switchSavingsTab('goals')">
            <i class="bi bi-bullseye"></i> Financial Goals &amp; Tabungan
        </button>
        <button type="button" class="btn-tab-pill" id="tabBtnMutualFunds" onclick="switchSavingsTab('reksadana')">
            <i class="bi bi-graph-up-arrow"></i> Portofolio Reksadana Real-time
            <span class="badge bg-primary bg-opacity-20 text-primary ms-1" id="badgeMfCount" style="font-size: 10px; padding: 2px 6px; border-radius: 20px;">0</span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 1: FINANCIAL GOALS & TARGET TABUNGAN VIEW        -->
    <!-- ======================================================== -->
    <div id="sectionGoalsView">
        <!-- KPI Summary Grid -->
        <div class="savings-kpi-grid" id="kpiGridContainer">
            <!-- 1. Total Terkumpul -->
            <div class="savings-kpi-card">
                <div class="kpi-title-row">
                    <span class="kpi-title-text">Total Terkumpul</span>
                    <div class="kpi-icon-box" style="background: rgba(16,185,129,0.12); color: var(--success);">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div id="statTotalCollected" class="kpi-value-text" style="color: var(--success);">
                    Rp <?= number_format($summary['total_collected'] ?? 0, 0, ',', '.') ?>
                </div>
                <div class="kpi-sub-text">Akumulasi seluruh alokasi</div>
            </div>

            <!-- 2. Total Target -->
            <div class="savings-kpi-card">
                <div class="kpi-title-row">
                    <span class="kpi-title-text">Total Target</span>
                    <div class="kpi-icon-box" style="background: rgba(99,102,241,0.12); color: #818cf8;">
                        <i class="bi bi-bullseye"></i>
                    </div>
                </div>
                <div id="statTotalTarget" class="kpi-value-text" style="color: var(--text-primary);">
                    Rp <?= number_format($summary['total_target'] ?? 0, 0, ',', '.') ?>
                </div>
                <div id="statTotalRemaining" class="kpi-sub-text">
                    Sisa: <strong style="color: var(--primary);">Rp <?= number_format($summary['total_remaining'] ?? 0, 0, ',', '.') ?></strong>
                </div>
            </div>

            <!-- 3. Overall Progress -->
            <div class="savings-kpi-card">
                <div class="kpi-title-row">
                    <span class="kpi-title-text">Pencapaian</span>
                    <div class="kpi-icon-box" style="background: rgba(245,158,11,0.12); color: #f59e0b;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <div id="statOverallProgress" class="kpi-value-text" style="color: #f59e0b;">
                    <?= $summary['overall_progress'] ?? 0 ?>%
                </div>
                <div class="goal-progress-bar" style="margin: 4px 0 0 0; height: 5px;">
                    <div id="statProgressBar" class="goal-progress-fill" style="width: <?= min(100, $summary['overall_progress'] ?? 0) ?>%; background: linear-gradient(90deg, #f59e0b, #10b981);"></div>
                </div>
            </div>

            <!-- 4. Goals Count -->
            <div class="savings-kpi-card">
                <div class="kpi-title-row">
                    <span class="kpi-title-text">Goals / Target</span>
                    <div class="kpi-icon-box" style="background: rgba(59,130,246,0.12); color: #3b82f6;">
                        <i class="bi bi-flag-fill"></i>
                    </div>
                </div>
                <div id="statGoalsCount" class="kpi-value-text" style="color: var(--text-primary);">
                    <?= $summary['total_goals'] ?? 0 ?> Target
                </div>
                <div id="statGoalsSub" class="kpi-sub-text">
                    <span style="color: var(--success); font-weight: 700;"><?= $summary['achieved_goals'] ?? 0 ?> Tercapai</span> &middot; <?= $summary['in_progress_goals'] ?? 0 ?> Berjalan
                </div>
            </div>
        </div>

        <!-- Distribusi Klasifikasi Penempatan Uang (Toko, Investasi, Bank, Piutang) -->
        <div class="savings-dist-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px;">
                <div>
                    <div style="font-size: 12.5px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-pie-chart-fill" style="color: #6366f1;"></i>
                        <span>Klasifikasi Penempatan Dana (Grouping Uang)</span>
                    </div>
                    <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 1px;">
                        Sebaran uang tabungan di Toko (uang berputar), Bibit (investasi), SeaBank/Bank, dll
                    </div>
                </div>
                <span id="totalAllocationsBadge" class="badge-custom badge-primary" style="font-size: 10px;">
                    <?= count($summary['type_breakdown'] ?? []) ?> Kategori Akun
                </span>
            </div>

            <!-- Multi-Segment Bar -->
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

            <!-- Pills Grid -->
            <div class="dist-pills-grid" id="distPillsContainer">
                <?php if (empty($typeBreakdown)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); font-size: 11px; padding: 6px 0;">
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
                        <div style="width: 28px; height: 28px; border-radius: 7px; background: <?= $bg ?>22; color: <?= $bg ?>; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                            <i class="bi <?= $iconClass ?>"></i>
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-size: 9.5px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 700; text-transform: uppercase;">
                                <?= htmlspecialchars($item['account_type']) ?>
                            </div>
                            <div style="font-size: 11.5px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
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

        <!-- Search & Filter Control Bar -->
        <div class="savings-control-bar">
            <div class="section-title" style="margin-bottom: 0;">Daftar Target &amp; Goals</div>
            
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; flex: 1; justify-content: flex-end;">
                <!-- Search Box -->
                <div class="search-input-wrapper savings-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchGoalsInput" placeholder="Cari nama target / pos uang..." oninput="onSearchGoals(this.value)">
                    <button type="button" id="btnClearSearch" onclick="clearSearchGoals()" style="display: none; background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 0 4px;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>

                <!-- Filter Status Buttons -->
                <div class="filter-btn-group" style="display: flex; gap: 5px;">
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
    <!-- SECTION 2: MUTUAL FUNDS / REKSADANA PORTFOLIO VIEW       -->
    <!-- ======================================================== -->
    <div id="sectionMutualFundsView" style="display: none;">
        <!-- Reksadana KPI Grid -->
        <div class="mf-kpi-grid" id="mfKpiGridContainer">
            <!-- 1. Total Modal Investasi -->
            <div class="mf-kpi-card">
                <div class="kpi-title-row">
                    <span class="kpi-title-text">Modal Investasi</span>
                    <div class="kpi-icon-box" style="background: rgba(99,102,241,0.12); color: #818cf8;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
                <div id="mfStatTotalInvested" class="kpi-value-text" style="color: var(--text-primary);">
                    Rp 0
                </div>
                <div class="kpi-sub-text" id="mfStatTotalFunds">0 Produk Reksadana</div>
            </div>

            <!-- 2. Nilai Portofolio Saat Ini -->
            <div class="mf-kpi-card">
                <div class="kpi-title-row">
                    <span class="kpi-title-text">Nilai Portofolio Saat Ini</span>
                    <div class="kpi-icon-box" style="background: rgba(59,130,246,0.12); color: #3b82f6;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <div id="mfStatCurrentValue" class="kpi-value-text" style="color: #3b82f6;">
                    Rp 0
                </div>
                <div class="kpi-sub-text">Total Unit &times; NAB Terkini</div>
            </div>

            <!-- 3. Total Keuntungan / Return -->
            <div class="mf-kpi-card">
                <div class="kpi-title-row">
                    <span class="kpi-title-text">Total Keuntungan (Return)</span>
                    <div class="kpi-icon-box" id="mfPnlIconBox" style="background: rgba(16,185,129,0.12); color: var(--success);">
                        <i class="bi bi-cash-stack" id="mfPnlIcon"></i>
                    </div>
                </div>
                <div id="mfStatTotalPnl" class="kpi-value-text" style="color: var(--success);">
                    +Rp 0 (+0.0%)
                </div>
                <div class="kpi-sub-text" id="mfStatReturnSub">Capital Gain / Loss</div>
            </div>

            <!-- 4. Pergerakan Hari Ini & Top Performer -->
            <div class="mf-kpi-card">
                <div class="kpi-title-row">
                    <span class="kpi-title-text">Pergerakan Hari Ini</span>
                    <div class="kpi-icon-box" style="background: rgba(245,158,11,0.12); color: #f59e0b;">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>
                </div>
                <div id="mfStatDailyPnl" class="kpi-value-text" style="color: #f59e0b;">
                    Rp 0
                </div>
                <div class="kpi-sub-text" id="mfStatTopPerformer">Top: -</div>
            </div>
        </div>

        <!-- Diversifikasi Reksadana Section -->
        <div class="savings-dist-card" style="margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                <div>
                    <div style="font-size: 12.5px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-pie-chart-fill" style="color: #3b82f6;"></i>
                        <span>Diversifikasi Portofolio Reksadana</span>
                    </div>
                    <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 1px;" id="mfDistSummaryText">
                        Alokasi sebaran aset (Pasar Uang, Obligasi, Saham, Campuran)
                    </div>
                </div>
                <button type="button" class="btn-primary-custom" onclick="syncMutualFundsToSavings()" style="background: var(--surface-2); color: var(--primary); border: 1px solid rgba(99,102,241,0.3); padding: 5px 12px; font-size: 11px; display: inline-flex; align-items: center; gap: 5px;" title="Gunakan total aset reksadana sebagai saldo pada pos alokasi tabungan">
                    <i class="bi bi-arrow-repeat"></i> Sinkron ke Tabungan
                </button>
            </div>
            <div class="dist-bar-wrapper" id="mfDistBarContainer">
                <div class="dist-bar-segment" style="width: 100%; background: var(--surface-3);"></div>
            </div>
            <div class="dist-pills-grid" id="mfDistPillsContainer">
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); font-size: 11px; padding: 6px 0;">
                    Belum ada data reksadana. Tambahkan produk untuk melihat grafik alokasi.
                </div>
            </div>
        </div>

        <!-- Control Bar: Search, Filter, Buttons -->
        <div class="savings-control-bar">
            <div class="section-title" style="margin-bottom: 0;">Daftar Reksadana</div>

            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; flex: 1; justify-content: flex-end;">
                <!-- Search Box -->
                <div class="search-input-wrapper savings-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchMfInput" placeholder="Cari nama produk / Manajer Investasi..." oninput="onSearchMutualFunds(this.value)">
                    <button type="button" id="btnClearMfSearch" onclick="clearSearchMutualFunds()" style="display: none; background: transparent; border: none; color: var(--text-muted); cursor: pointer; padding: 0 4px;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>

                <!-- Filter Buttons -->
                <div class="filter-btn-group" id="mfFilterGroup" style="display: flex; gap: 4px; flex-wrap: wrap;">
                    <button class="btn-filter active" onclick="filterMutualFunds('all', this)">Semua</button>
                    <button class="btn-filter" onclick="filterMutualFunds('Pasar Uang', this)">Pasar Uang</button>
                    <button class="btn-filter" onclick="filterMutualFunds('Pendapatan Tetap', this)">Pendapatan Tetap</button>
                    <button class="btn-filter" onclick="filterMutualFunds('Saham', this)">Saham</button>
                    <button class="btn-filter" onclick="filterMutualFunds('Campuran', this)">Campuran</button>
                    <button class="btn-filter" onclick="filterMutualFunds('Index / ETF', this)">Index</button>
                </div>

                <!-- Action Buttons -->
                <button type="button" class="btn-primary-custom" onclick="refreshMutualFundsLiveNav()" id="btnRefreshMfNav" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 12px; font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bi bi-arrow-clockwise"></i> Update Live NAB
                </button>
                <button type="button" class="btn-primary-custom" onclick="openAddMutualFundModal()" style="padding: 7px 14px; font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Reksadana
                </button>
            </div>
        </div>

        <!-- Mutual Funds Cards Grid -->
        <div class="mf-card-grid" id="mfGridContainer">
            <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px;">
                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                <div>Memuat portofolio reksadana...</div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 1: DETAIL GOAL & KELOLA POS ALOKASI (RESPONSIVE)   -->
<!-- ======================================================== -->
<div class="modal fade" id="modalGoalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden;">
            
            <!-- Modal Header Banner -->
            <div class="modal-goal-banner" id="detailGoalBanner">
                <!-- Top Row: Icon, Title & Close Button -->
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                        <div id="detailGoalIconBadge" class="goal-icon-badge" style="background: rgba(99,102,241,0.15); color: #818cf8; width: 38px; height: 38px; font-size: 1.15rem;">
                            <i id="detailGoalIcon" class="bi bi-piggy-bank-fill"></i>
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <h4 id="detailGoalName" style="font-weight: 800; font-size: 1.15rem; margin: 0; color: var(--text-primary); line-height: 1.2;">
                                    Tabungan Mobil
                                </h4>
                                <span id="detailGoalCategory" class="badge-custom badge-primary" style="font-size: 9.5px;">Kendaraan</span>
                                <span id="detailGoalStatusBadge" class="badge-custom" style="font-size: 9.5px;">Sedang Berjalan</span>
                            </div>
                            <p id="detailGoalDesc" style="font-size: 11px; color: var(--text-muted); margin: 3px 0 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                Catatan target
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="flex-shrink: 0; margin-top: 2px;"></button>
                </div>

                <!-- Main Stats Highlight Box (Compact & Never Overflows) -->
                <div class="modal-stats-box">
                    <div class="stats-collected-row">
                        <div>
                            <span style="font-size: 9.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Terkumpul</span>
                            <div id="detailCollectedAmount" style="font-size: 1.25rem; font-weight: 800; color: var(--success); line-height: 1.2;">
                                Rp 0
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span id="detailProgressText" class="badge-custom badge-primary" style="font-size: 10px; font-weight: 800;">
                                0% Tercapai
                            </span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="goal-progress-bar" style="margin: 4px 0 6px 0; height: 6px;">
                        <div id="detailProgressFill" class="goal-progress-fill" style="width: 0%; background: var(--success);"></div>
                    </div>

                    <!-- Sub 2-Columns Grid -->
                    <div class="stats-sub-grid">
                        <div class="stats-sub-item">
                            <span class="stats-sub-label">Target Nominal</span>
                            <div id="detailTargetAmount" class="stats-sub-val" style="color: var(--text-primary);">Rp 0</div>
                        </div>
                        <div class="stats-sub-item" style="text-align: right;">
                            <span class="stats-sub-label">Sisa Kekurangan</span>
                            <div id="detailRemainingAmount" class="stats-sub-val" style="color: var(--primary);">Rp 0</div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 10px; color: var(--text-muted); margin-top: 6px; padding-top: 6px; border-top: 1px dashed var(--border-color);">
                        <span id="detailDeadlineText"><i class="bi bi-calendar-event"></i> -</span>
                        <span id="detailAllocCountText">0 Pos Alokasi</span>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding: 16px 18px; max-height: 60vh; overflow-y: auto;">
                
                <!-- Modern Segmented Switch Tabs -->
                <div class="detail-segmented-tabs">
                    <button class="detail-tab-btn active" id="tabAllocationsBtn" onclick="switchDetailTab('allocations', this)">
                        <i class="bi bi-grid-fill"></i> Pos Penempatan
                    </button>
                    <button class="detail-tab-btn" id="tabLogsBtn" onclick="switchDetailTab('logs', this)">
                        <i class="bi bi-clock-history"></i> Riwayat Mutasi
                    </button>
                    <button class="detail-tab-btn" id="tabHistoryBtn" onclick="switchDetailTab('history', this)">
                        <i class="bi bi-graph-up-arrow"></i> Progress Harian
                    </button>
                </div>

                <!-- TAB 1: POS ALOKASI (GROUPING UANG) -->
                <div id="detailTabAllocations">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 8px; flex-wrap: wrap;">
                        <div style="font-size: 12px; font-weight: 700; color: var(--text-primary);">
                            Rincian Pembagian Uang:
                        </div>
                        <button class="btn-primary-custom" onclick="openAddAllocationModal()" style="padding: 5px 12px; border-radius: var(--radius-sm); font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="bi bi-plus-lg"></i> Tambah Pos Uang
                        </button>
                    </div>

                    <div id="allocationsListContainer">
                        <!-- Allocations list injected by JS -->
                    </div>
                </div>

                <!-- TAB 2: RIWAYAT MUTASI -->
                <div id="detailTabLogs" style="display: none;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">
                        Riwayat Mutasi Dana (Setor / Tarik):
                    </div>
                    <div id="logsTimelineContainer" style="padding-left: 4px;">
                        <!-- Timeline injected by JS -->
                    </div>
                </div>

                <!-- TAB 3: PROGRESS HARIAN & HISTORY SNAPSHOT (AUTO 23:00 GMT+7) -->
                <div id="detailTabHistory" style="display: none;">
                    <!-- Analytics Summary Box -->
                    <div id="historyAnalyticsBox" style="background: var(--surface-2); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px 14px; margin-bottom: 12px;">
                        <!-- Injected by JS -->
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 8px; flex-wrap: wrap;">
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                                <span>Riwayat Snapshot Harian</span>
                                <span class="badge" style="font-size: 9px; background: rgba(99,102,241,0.15); color: #818cf8; border: 1px solid rgba(99,102,241,0.3); font-weight: 600; padding: 2px 6px;">
                                    <i class="bi bi-clock-history me-1"></i> Auto 23:00 WIB
                                </span>
                            </div>
                            <div style="font-size: 10px; color: var(--text-muted); margin-top: 1px;">
                                Memantau kenaikan / penurunan saldo &amp; persentase progress per hari
                            </div>
                        </div>
                        <button class="btn-primary-custom" onclick="captureGoalSnapshotManual()" style="padding: 5px 12px; border-radius: var(--radius-sm); font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="bi bi-camera-fill"></i> Snapshot Hari Ini
                        </button>
                    </div>

                    <div id="historyTimelineContainer">
                        <!-- Injected by JS -->
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="padding: 10px 18px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between;">
                <button type="button" class="btn-primary-custom" onclick="openEditGoalModalFromDetail()" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); font-size: 11.5px; padding: 6px 12px;">
                    <i class="bi bi-pencil-square me-1"></i> Edit Goal
                </button>
                <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-3); color: var(--text-primary); font-size: 11.5px; padding: 6px 16px;">
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 14px 18px;">
                <h5 class="modal-title" id="goalFormTitle" style="font-weight: 800; font-size: 1rem; color: var(--text-primary);">
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

                <div class="modal-body" style="padding: 16px 18px; display: flex; flex-direction: column; gap: 13px; max-height: 70vh; overflow-y: auto;">
                    
                    <!-- Goal Name -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nama Target / Goal <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formGoalName" class="form-control-custom" placeholder="Misal: Tabungan Mobil, Dana Darurat, Umroh" required style="width: 100%; padding: 9px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 13px; font-weight: 600;">
                    </div>

                    <!-- Target Amount -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Target Nominal (Rp) <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formGoalTargetAmount" class="form-control-custom" placeholder="Misal: 50.000.000" required oninput="formatCurrencyInput(this)" style="width: 100%; padding: 9px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 13.5px; font-weight: 800;">
                    </div>

                    <!-- Target Date & Category (Custom Dropdown) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Target Tanggal (Opsional)
                            </label>
                            <input type="date" id="formGoalTargetDate" class="form-control-custom" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 11.5px; color-scheme: dark;">
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
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 5px; display: block;">
                            Pilihan Icon Target
                        </label>
                        <div class="icon-picker-grid" id="goalIconGrid">
                            <!-- Rendered by JS -->
                        </div>
                    </div>

                    <!-- Visual Color Swatch Picker -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 5px; display: block;">
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
                        <textarea id="formGoalDesc" rows="2" class="form-control-custom" placeholder="Catatan target atau motivasi menabung..." style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12px;"></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 18px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 14px; font-size: 11.5px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitGoal" class="btn-primary-custom" style="padding: 7px 18px; font-size: 11.5px;">
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 14px 18px;">
                <h5 class="modal-title" id="allocFormTitle" style="font-weight: 800; font-size: 1rem; color: var(--text-primary);">
                    Tambah Pos Penempatan Uang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="allocationForm" onsubmit="submitAllocationForm(event)">
                <input type="hidden" id="formAllocId" value="">
                <input type="hidden" id="formAllocGoalId" value="">
                <input type="hidden" id="formAllocType" value="Bank / Rekening">

                <div class="modal-body" style="padding: 16px 18px; display: flex; flex-direction: column; gap: 13px;">
                    
                    <!-- Allocation Name -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nama Pos / Akun <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formAllocName" class="form-control-custom" placeholder="Misal: Uang Toko (Berputar), Bibit Reksadana, SeaBank" required style="width: 100%; padding: 9px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 13px; font-weight: 600;">
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
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Platform / Lembaga
                            </label>
                            <input type="text" id="formAllocInstitution" class="form-control-custom" placeholder="Toko, Bibit, SeaBank" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12px;">
                        </div>
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Nominal Saldo (Rp) <span style="color: var(--primary);">*</span>
                            </label>
                            <input type="text" id="formAllocAmount" class="form-control-custom" placeholder="3.000.000" required oninput="formatCurrencyInput(this)" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12.5px; font-weight: 800;">
                            <div id="quickPpobAllocHelper" class="mt-1" style="font-size: 10.5px; display: none;">
                                <span style="color: var(--text-muted);">Saldo PPOB:</span>
                                <button type="button" class="btn btn-sm py-0 px-2 rounded-pill ms-1 fw-bold" onclick="applyPpobBalanceToAllocInput()" style="font-size: 10px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.25);" title="Klik untuk otomatis mengisi nominal sesuai Saldo PPOB saat ini">
                                    <i class="bi bi-wallet2 me-1"></i><span id="allocPpobBalanceText">Rp 0</span> (Gunakan)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Catatan Pos (Opsional)
                        </label>
                        <input type="text" id="formAllocNotes" class="form-control-custom" placeholder="Misal: No Rek / Portfolio Reksadana Obligasi" style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12px;">
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 18px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 14px; font-size: 11.5px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitAlloc" class="btn-primary-custom" style="padding: 7px 18px; font-size: 11.5px;">
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 14px 18px;">
                <h5 class="modal-title" style="font-weight: 800; font-size: 1rem; color: var(--text-primary);">
                    Catat Mutasi / Perubahan Saldo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="mutationForm" onsubmit="submitMutationForm(event)">
                <input type="hidden" id="formMutGoalId" value="">
                <input type="hidden" id="formMutAllocId" value="">
                <input type="hidden" id="formMutType" value="deposit">

                <div class="modal-body" style="padding: 16px 18px; display: flex; flex-direction: column; gap: 13px;">
                    
                    <!-- Pos Info Pill -->
                    <div style="background: var(--surface-2); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 10px 12px;">
                        <div style="font-size: 9.5px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Pos Penempatan Target:</div>
                        <div id="formMutAllocName" style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</div>
                        <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">
                            Saldo Saat Ini: <strong id="formMutCurrentBalance" style="color: var(--success);">Rp 0</strong>
                        </div>
                    </div>

                    <!-- Custom Mutation Segmented Card Selection -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 5px; display: block;">
                            Jenis Mutasi <span style="color: var(--primary);">*</span>
                        </label>
                        <div class="mutation-type-segment">
                            <div class="mutation-type-card selected-deposit" id="mutCardDeposit" onclick="selectMutationType('deposit')">
                                <div style="width: 28px; height: 28px; border-radius: 7px; background: rgba(16,185,129,0.18); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                                    <i class="bi bi-plus-circle-fill"></i>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="font-size: 11.5px; font-weight: 800; color: #10b981; line-height: 1.2;">Tambah Setor</div>
                                    <div style="font-size: 9px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Menambah saldo</div>
                                </div>
                            </div>
                            <div class="mutation-type-card" id="mutCardWithdraw" onclick="selectMutationType('withdraw')">
                                <div style="width: 28px; height: 28px; border-radius: 7px; background: rgba(239,68,68,0.18); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                                    <i class="bi bi-dash-circle-fill"></i>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="font-size: 11.5px; font-weight: 800; color: var(--text-muted); line-height: 1.2;">Tarik Dana</div>
                                    <div style="font-size: 9px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Mengurangi saldo</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nominal Mutasi -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Nominal Mutasi (Rp) <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="text" id="formMutAmount" class="form-control-custom" placeholder="Misal: 500.000" required oninput="formatCurrencyInput(this)" style="width: 100%; padding: 9px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 13.5px; font-weight: 800;">
                        <div id="quickPpobMutHelper" class="mt-1" style="font-size: 10.5px; display: none;">
                            <span style="color: var(--text-muted);">Isi sesuai Saldo PPOB:</span>
                            <button type="button" class="btn btn-sm py-0 px-2 rounded-pill ms-1 fw-bold" onclick="applyPpobBalanceToMutInput()" style="font-size: 10px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.25);" title="Klik untuk otomatis mengisi nominal mutasi sesuai Saldo PPOB saat ini">
                                <i class="bi bi-wallet2 me-1"></i><span id="mutPpobBalanceText">Rp 0</span>
                            </button>
                        </div>
                    </div>

                    <!-- Date & Notes -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Tanggal Mutasi
                            </label>
                            <input type="date" id="formMutDate" value="<?= date('Y-m-d') ?>" class="form-control-custom" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 11.5px; color-scheme: dark;">
                        </div>
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Catatan
                            </label>
                            <input type="text" id="formMutNotes" class="form-control-custom" placeholder="Misal: Untung toko" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 11.5px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 18px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 14px; font-size: 11.5px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitMut" class="btn-primary-custom" style="padding: 7px 18px; font-size: 11.5px;">
                        Simpan Mutasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 5: RIWAYAT GLOBAL SELURUH TABUNGAN & SNAPSHOT     -->
<!-- ======================================================== -->
<div class="modal fade" id="modalGlobalHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden;">
            <div class="modal-header modal-header-custom" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 9px; background: rgba(99,102,241,0.15); color: #818cf8; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" style="font-size: 1.05rem; font-weight: 800; margin: 0; color: var(--text-primary);">
                            Riwayat Snapshot &amp; Progress Tabungan
                        </h5>
                        <p style="font-size: 11px; color: var(--text-muted); margin: 2px 0 0 0;">
                            Memantau pergerakan saldo naik / turun setiap hari per target
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding: 16px 20px; max-height: 65vh; overflow-y: auto;">
                <!-- Action & Filter Bar -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 10px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--text-muted); white-space: nowrap;">Filter Target:</span>
                        <select id="globalHistoryGoalFilter" class="form-select-custom" onchange="filterGlobalSnapshots()" style="padding: 6px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 11.5px; font-weight: 600; flex: 1; max-width: 250px;">
                            <option value="all">Semua Target Tabungan</option>
                        </select>
                    </div>
                    <button class="btn-primary-custom" onclick="captureAllSnapshotsManual()" style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border: none; padding: 6px 14px; border-radius: var(--radius-md); font-size: 11px; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="bi bi-camera-fill"></i> Simpan History Hari Ini
                    </button>
                </div>

                <!-- Info Pill -->
                <div style="background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.25); border-radius: var(--radius-md); padding: 10px 14px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 11px; color: var(--text-primary);">
                        <i class="bi bi-robot" style="color: #818cf8; font-size: 1.1rem;"></i>
                        <span>Sistem otomatis mencapture data saldo tabungan setiap pukul <strong>23:00 WIB (GMT+7)</strong>.</span>
                    </div>
                    <span id="globalSnapshotCountBadge" class="badge-custom badge-primary" style="font-size: 10px;">0 Record</span>
                </div>

                <!-- Snapshot List Container -->
                <div id="globalHistoryListContainer">
                    <!-- Injected by JS -->
                </div>
            </div>

            <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
                <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); font-size: 11.5px; padding: 6px 16px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 7: TAMBAH / EDIT REKSADANA                        -->
<!-- ======================================================== -->
<div class="modal fade" id="modalMutualFundForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 14px 18px;">
                <h5 class="modal-title" id="mfFormTitle" style="font-weight: 800; font-size: 1rem; color: var(--text-primary);">
                    Tambah Produk Reksadana
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="mutualFundForm" onsubmit="submitMutualFundForm(event)">
                <input type="hidden" id="formMfId" value="">
                <input type="hidden" id="formMfType" value="Pasar Uang">

                <div class="modal-body" style="padding: 16px 18px; display: flex; flex-direction: column; gap: 13px;">
                    
                    <!-- 1. Manajer Investasi (MI) Custom Searchable Dropdown Picker -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Manajer Investasi (MI) <span style="color: var(--primary);">*</span>
                        </label>
                        <input type="hidden" id="formMfHouse" value="Sucorinvest Asset Management" required>
                        <div class="custom-select-picker" id="mfHousePickerContainer">
                            <div class="custom-select-trigger" id="mfHousePickerTrigger" onclick="toggleCustomDropdown('mfHousePickerMenu', event)">
                                <div class="custom-select-content">
                                    <div class="custom-select-icon" id="mfHouseSelectedIcon" style="background: rgba(99,102,241,0.15); color: #818cf8;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div class="custom-select-label" id="mfHouseSelectedLabel">Sucorinvest Asset Management</div>
                                        <div class="custom-select-sub" id="mfHouseSelectedSub">Manajer Investasi</div>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-down custom-select-chevron"></i>
                            </div>
                            <div class="custom-select-menu" id="mfHousePickerMenu">
                                <div class="custom-select-search">
                                    <input type="text" placeholder="Cari Manajer Investasi..." oninput="filterDropdownOptions('mfHousePickerMenu', this.value)">
                                </div>
                                <div id="mfHouseOptionsList" style="max-height: 200px; overflow-y: auto;">
                                    <!-- Rendered by JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Nama Produk Reksadana Custom Searchable Dropdown Picker -->
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin: 0;">
                                Nama Produk Reksadana <span style="color: var(--primary);">*</span>
                            </label>
                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 9.5px; cursor: pointer;" onclick="toggleCustomMfInput()">
                                <i class="bi bi-pencil-square me-1"></i>Ketik Manual / Custom
                            </span>
                        </div>
                        <input type="hidden" id="formMfName" value="Sucorinvest Sharia Money Market Fund" required>
                        <div class="custom-select-picker" id="mfProductPickerContainer">
                            <div class="custom-select-trigger" id="mfProductPickerTrigger" onclick="toggleCustomDropdown('mfProductPickerMenu', event)">
                                <div class="custom-select-content">
                                    <div class="custom-select-icon" id="mfProductSelectedIcon" style="background: rgba(16,185,129,0.15); color: #10b981;">
                                        <i class="bi bi-graph-up-arrow"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div class="custom-select-label" id="mfProductSelectedLabel">Sucorinvest Sharia Money Market Fund</div>
                                        <div class="custom-select-sub" id="mfProductSelectedSub">Pasar Uang &middot; NAB: Rp 1.528,42</div>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-down custom-select-chevron"></i>
                            </div>
                            <div class="custom-select-menu" id="mfProductPickerMenu" style="max-height: 340px; min-width: 320px;">
                                <div class="custom-select-search" style="padding: 8px 10px 4px 10px;">
                                    <input type="text" id="inputSearchMfProducts" placeholder="Cari nama produk, obligasi, MI..." oninput="onFilterMfProductList(this.value)">
                                </div>
                                <!-- Quick Category Filter Strip -->
                                <div style="display: flex; gap: 4px; padding: 4px 10px 8px 10px; overflow-x: auto; border-bottom: 1px solid var(--border-color);" id="mfProductCatFilterStrip">
                                    <button type="button" class="btn-filter active" style="font-size: 10px; padding: 3px 8px; border-radius: 6px; white-space: nowrap;" onclick="setMfProductCatFilter('all', this)">Semua</button>
                                    <button type="button" class="btn-filter" style="font-size: 10px; padding: 3px 8px; border-radius: 6px; white-space: nowrap;" onclick="setMfProductCatFilter('Pasar Uang', this)">Pasar Uang</button>
                                    <button type="button" class="btn-filter" style="font-size: 10px; padding: 3px 8px; border-radius: 6px; white-space: nowrap;" onclick="setMfProductCatFilter('Pendapatan Tetap', this)">Obligasi / Fixed</button>
                                    <button type="button" class="btn-filter" style="font-size: 10px; padding: 3px 8px; border-radius: 6px; white-space: nowrap;" onclick="setMfProductCatFilter('Saham', this)">Saham</button>
                                    <button type="button" class="btn-filter" style="font-size: 10px; padding: 3px 8px; border-radius: 6px; white-space: nowrap;" onclick="setMfProductCatFilter('Campuran', this)">Campuran</button>
                                    <button type="button" class="btn-filter" style="font-size: 10px; padding: 3px 8px; border-radius: 6px; white-space: nowrap;" onclick="setMfProductCatFilter('Index / ETF', this)">Index</button>
                                </div>
                                <div id="mfProductOptionsList" style="max-height: 220px; overflow-y: auto;">
                                    <!-- Rendered by JS -->
                                </div>
                            </div>
                        </div>
                        <!-- Manual Name Input (if custom toggle active) -->
                        <input type="text" id="formMfNameManual" class="form-control-custom" placeholder="Ketik nama produk reksadana..." style="width: 100%; padding: 9px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12.5px; font-weight: 600; display: none;">
                    </div>

                    <!-- 3. Tipe / Kategori & Tanggal Beli (2 cols) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Tipe / Kategori <span style="color: var(--primary);">*</span>
                            </label>
                            <select id="formMfTypeSelect" class="form-control-custom" onchange="onMfTypeSelectChange(this.value)" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12px; font-weight: 600;">
                                <option value="Pasar Uang">Pasar Uang</option>
                                <option value="Pendapatan Tetap">Pendapatan Tetap (Obligasi)</option>
                                <option value="Saham">Saham (Equity)</option>
                                <option value="Campuran">Campuran (Balanced)</option>
                                <option value="Index / ETF">Index / ETF</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Tanggal Pembelian
                            </label>
                            <input type="date" id="formMfBuyDate" value="<?= date('Y-m-d') ?>" class="form-control-custom" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 11.5px; color-scheme: dark;">
                        </div>
                    </div>

                    <!-- Smart Investment Inputs (Modal, NAB Beli, Unit) with Auto Calculation -->
                    <div style="background: var(--surface-2); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 11px; font-weight: 700; color: var(--text-primary);">
                                <i class="bi bi-calculator me-1" style="color: var(--primary);"></i> Kalkulasi Pembelian &amp; Unit
                            </span>
                            <span style="font-size: 9.5px; color: var(--text-muted);">
                                Otomatis menghitung Unit &amp; Nilai
                            </span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                            <!-- Modal Pembelian (Rp) -->
                            <div>
                                <label class="form-label-custom" style="font-size: 10.5px; font-weight: 700; color: var(--text-muted); margin-bottom: 3px; display: block;">
                                    Modal Beli (Rp) <span style="color: var(--primary);">*</span>
                                </label>
                                <input type="text" id="formMfInvested" class="form-control-custom" placeholder="5.000.000" required oninput="formatCurrencyInput(this); calcMfUnitsOrNav('invested');" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12.5px; font-weight: 800;">
                            </div>

                            <!-- NAB Pembelian per Unit -->
                            <div>
                                <label class="form-label-custom" style="font-size: 10.5px; font-weight: 700; color: var(--text-muted); margin-bottom: 3px; display: block;">
                                    NAB Beli / Unit (Rp) <span style="color: var(--primary);">*</span>
                                </label>
                                <input type="number" step="0.0001" id="formMfBuyNav" class="form-control-custom" placeholder="1528.42" required oninput="calcMfUnitsOrNav('buy_nav')" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12px; font-weight: 700;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <!-- Total Unit Dimiliki -->
                            <div>
                                <label class="form-label-custom" style="font-size: 10.5px; font-weight: 700; color: var(--text-muted); margin-bottom: 3px; display: block;">
                                    Total Unit Dimiliki <span style="color: var(--primary);">*</span>
                                </label>
                                <input type="number" step="0.0001" id="formMfUnits" class="form-control-custom" placeholder="3271.3652" required oninput="calcMfUnitsOrNav('units')" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: #3b82f6; font-size: 12px; font-weight: 800;">
                            </div>

                            <!-- NAB Saat Ini (Real-time Live NAV) -->
                            <div>
                                <label class="form-label-custom" style="font-size: 10.5px; font-weight: 700; color: var(--text-muted); margin-bottom: 3px; display: block;">
                                    NAB Terkini (Rp)
                                </label>
                                <input type="number" step="0.0001" id="formMfCurrentNav" class="form-control-custom" placeholder="1528.42" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--success); font-size: 12px; font-weight: 700;">
                            </div>
                        </div>
                    </div>

                    <!-- Platform & Syariah (2 cols) -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; align-items: center;">
                        <div>
                            <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                                Platform Agen / APERD
                            </label>
                            <input type="text" id="formMfPlatform" class="form-control-custom" placeholder="Bibit / Bareksa / IPOT" value="Bibit" style="width: 100%; padding: 8px 10px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12px;">
                        </div>
                        <div style="padding-top: 18px;">
                            <label class="form-check-label" style="font-size: 11.5px; font-weight: 600; color: var(--text-primary); cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" id="formMfIsSyariah" class="form-check-input" style="cursor: pointer;">
                                <span>Produk Syariah</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="form-label-custom" style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px; display: block;">
                            Catatan Investasi (Opsional)
                        </label>
                        <input type="text" id="formMfNotes" class="form-control-custom" placeholder="Misal: Alokasi untuk dana darurat / DP Rumah" style="width: 100%; padding: 8px 12px; background: var(--bg-primary); border: 1.5px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 12px;">
                    </div>
                </div>

                <div class="modal-footer" style="padding: 12px 18px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 14px; font-size: 11.5px;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitMf" class="btn-primary-custom" style="padding: 7px 18px; font-size: 11.5px;">
                        Simpan Reksadana
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: RIWAYAT NAB & TOTAL ASET REKSADANA               -->
<!-- ======================================================== -->
<div class="modal fade" id="modalMutualFundHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden;">
            <div class="modal-header" style="padding: 12px 18px; border-bottom: 1px solid var(--border-color); background: var(--surface-2);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59,130,246,0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h6 id="mfHistoryModalTitle" style="font-weight: 800; font-size: 13px; margin: 0; color: var(--text-primary);">
                            Riwayat NAB & Perubahan Nilai Aset
                        </h6>
                        <span id="mfHistoryModalSub" style="font-size: 10.5px; color: var(--text-muted);">
                            Log pembaruan otomatis setiap jam
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size: 10px;"></button>
            </div>
            <div class="modal-body" style="padding: 16px; max-height: 420px; overflow-y: auto;">
                <div id="mfHistoryListContainer">
                    <div style="text-align: center; padding: 25px; color: var(--text-muted); font-size: 12px;">
                        <span class="spinner-border spinner-border-sm me-1"></span> Memuat riwayat NAB...
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 10px 18px; border-top: 1px solid var(--border-color); background: var(--surface-2);">
                <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-3); color: var(--text-primary); border: 1px solid var(--border-color); padding: 6px 14px; font-size: 11px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 6: CUSTOM CONFIRMATION MODAL                      -->
<!-- ======================================================== -->
<div class="modal fade" id="modalSavingsConfirm" tabindex="-1" aria-hidden="true" style="z-index: 100000;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content modal-content-custom" style="background: var(--surface-1); border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
            <div class="modal-body" style="padding: 24px 20px 18px 20px; text-align: center;">
                <div id="confirmModalIconBox" style="width: 54px; height: 54px; border-radius: 50%; background: rgba(239,68,68,0.15); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.65rem; margin: 0 auto 14px;">
                    <i id="confirmModalIcon" class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h5 id="confirmModalTitle" style="font-weight: 800; font-size: 1.1rem; margin: 0 0 6px 0; color: var(--text-primary);">
                    Konfirmasi Tindakan
                </h5>
                <p id="confirmModalText" style="font-size: 12px; color: var(--text-muted); margin: 0 0 20px 0; line-height: 1.45;">
                    Apakah Anda yakin ingin melanjutkan tindakan ini?
                </p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <button type="button" class="btn-primary-custom" data-bs-dismiss="modal" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 9px 16px; border-radius: var(--radius-md); font-size: 12px; font-weight: 700;">
                        Batal
                    </button>
                    <button type="button" id="confirmModalActionBtn" class="btn-primary-custom" style="background: var(--danger); color: #ffffff; border: none; padding: 9px 16px; border-radius: var(--radius-md); font-size: 12px; font-weight: 700; box-shadow: 0 4px 12px rgba(239,68,68,0.3);">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Toast Container -->
<div id="savingsToastContainer" class="savings-toast-container"></div>

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
    { value: 'PPOB / Digiflazz', label: 'Saldo PPOB (Digiflazz / Pulsa)', icon: 'bi-wallet2', bg: 'rgba(16,185,129,0.15)', color: '#10b981', sub: 'Saldo deposit Digiflazz / PPOB' },
    { value: 'Toko / Kas', label: 'Toko / Kas (Uang Berputar)', icon: 'bi-shop', bg: 'rgba(245,158,11,0.15)', color: '#f59e0b', sub: 'Uang di kasir, brankas, modal toko' },
    { value: 'Investasi', label: 'Investasi (Bibit, Saham, Reksadana)', icon: 'bi-graph-up-arrow', bg: 'rgba(59,130,246,0.15)', color: '#3b82f6', sub: 'Bibit, Ajaib, Reksadana, Emas' },
    { value: 'Bank / Rekening', label: 'Bank / Rekening (SeaBank, BCA)', icon: 'bi-bank', bg: 'rgba(99,102,241,0.15)', color: '#818cf8', sub: 'SeaBank, BCA, Mandiri, BRI' },
    { value: 'Piutang / Hutang', label: 'Piutang / Pinjaman', icon: 'bi-person-lines-fill', bg: 'rgba(236,72,153,0.15)', color: '#ec4899', sub: 'Piutang pelanggan/rekanan' },
    { value: 'Lainnya', label: 'Lainnya (Fisik / Dompet)', icon: 'bi-wallet2', bg: 'rgba(100,116,139,0.15)', color: '#94a3b8', sub: 'Dompet fisik, amplop, dll' }
];

// Master catalog data from MutualFundService (Bareksa top funds)
const INLINE_MASTER_CATALOG = <?= json_encode(MutualFundService::getDefaultCatalog()) ?>;
const INLINE_FUND_HOUSES = <?= json_encode(MutualFundService::getFundHouses()) ?>;

// State for Mutual Funds
let allMutualFunds = [];
let mutualFundsSummary = null;
let currentMfFilter = 'all';
let searchMfKeyword = '';
let masterCatalogProducts = Array.isArray(INLINE_MASTER_CATALOG) ? INLINE_MASTER_CATALOG : [];
let isCustomMfInput = false;
let currentMfCategoryFilter = 'all';
let searchMfDropdownKeyword = '';
let selectedMfHouseFilter = '';

document.addEventListener('DOMContentLoaded', () => {
    initCustomSelectPickers();
    initMutualFundPickers();
    loadGoals();
    loadSummary();
    loadSavingsPpobBalance();
    loadMutualFunds();
    loadMasterCatalog();
    scheduleNightlySnapshot();

    // Auto-refresh mutual funds live NAV & recalculate assets every 1 hour (3,600,000 ms)
    setInterval(() => {
        refreshMutualFundsLiveNav(true);
    }, 3600000);

    // Close dropdowns on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.custom-select-picker')) {
            document.querySelectorAll('.custom-select-menu.show').forEach(m => m.classList.remove('show'));
            document.querySelectorAll('.custom-select-trigger.open').forEach(t => t.classList.remove('open'));
        }
        if (!e.target.closest('#mfCatalogSearchWrapper')) {
            hideMfCatalogDropdown();
        }
    });
});

// Nightly Snapshot Automation at 23:00 WIB (GMT+7)
function scheduleNightlySnapshot() {
    try {
        const now = new Date();
        const target = new Date();
        target.setHours(23, 0, 0, 0);

        let diff = target.getTime() - now.getTime();
        // If current time is 23:00 or later, trigger once if needed and schedule tomorrow
        if (diff <= 0) {
            target.setDate(target.getDate() + 1);
            diff = target.getTime() - now.getTime();
        }

        setTimeout(async () => {
            try {
                await fetch('<?= BASE_URL ?>api/savings/snapshots/capture-all', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                    body: JSON.stringify({ csrf_token: getCsrfToken() })
                });
            } catch(e) {}
            scheduleNightlySnapshot();
        }, diff);
    } catch(e) {
        console.warn('Auto-snapshot scheduler error:', e);
    }
}

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
// ULTRA-MODERN CUSTOM NOTIFICATIONS & DIALOGS
// ----------------------------------------------------
function showSavingsToast(title, message, type = 'success', duration = 3500) {
    const container = document.getElementById('savingsToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'savings-toast-item';

    let icon = 'bi-check-circle-fill';
    let iconBg = 'rgba(16,185,129,0.15)';
    let iconColor = '#10b981';
    let progressBg = '#10b981';

    if (type === 'error') {
        icon = 'bi-x-circle-fill';
        iconBg = 'rgba(239,68,68,0.15)';
        iconColor = '#ef4444';
        progressBg = '#ef4444';
    } else if (type === 'warning') {
        icon = 'bi-exclamation-triangle-fill';
        iconBg = 'rgba(245,158,11,0.15)';
        iconColor = '#f59e0b';
        progressBg = '#f59e0b';
    } else if (type === 'info') {
        icon = 'bi-info-circle-fill';
        iconBg = 'rgba(99,102,241,0.15)';
        iconColor = '#818cf8';
        progressBg = '#818cf8';
    } else if (type === 'camera') {
        icon = 'bi-camera-fill';
        iconBg = 'rgba(16,185,129,0.2)';
        iconColor = '#10b981';
        progressBg = 'linear-gradient(90deg, #10b981, #6366f1)';
    }

    toast.innerHTML = `
        <div class="savings-toast-icon-box" style="background:${iconBg};color:${iconColor};">
            <i class="bi ${icon}"></i>
        </div>
        <div class="savings-toast-content">
            <div class="savings-toast-title">${escapeHtml(title)}</div>
            <div class="savings-toast-msg">${escapeHtml(message)}</div>
        </div>
        <div class="savings-toast-progress" style="background:${progressBg};animation-duration:${duration}ms;"></div>
    `;

    container.appendChild(toast);

    const timer = setTimeout(() => {
        toast.classList.add('toast-leave');
        setTimeout(() => toast.remove(), 300);
    }, duration);

    toast.addEventListener('click', () => {
        clearTimeout(timer);
        toast.classList.add('toast-leave');
        setTimeout(() => toast.remove(), 300);
    });
}

let pendingSavingsConfirmAction = null;
function showSavingsConfirm({ title, text, icon = 'bi-trash-fill', iconColor = '#ef4444', iconBg = 'rgba(239,68,68,0.15)', confirmText = 'Ya, Hapus', confirmBtnColor = 'var(--danger)', onConfirm }) {
    document.getElementById('confirmModalTitle').textContent = title || 'Konfirmasi Tindakan';
    document.getElementById('confirmModalText').textContent = text || 'Apakah Anda yakin ingin melanjutkan?';
    
    const iconBox = document.getElementById('confirmModalIconBox');
    iconBox.style.background = iconBg;
    iconBox.style.color = iconColor;
    document.getElementById('confirmModalIcon').className = `bi ${icon}`;

    const actBtn = document.getElementById('confirmModalActionBtn');
    actBtn.textContent = confirmText;
    actBtn.style.background = confirmBtnColor;

    pendingSavingsConfirmAction = onConfirm;
    actBtn.onclick = () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalSavingsConfirm'));
        if (modal) modal.hide();
        if (typeof pendingSavingsConfirmAction === 'function') {
            pendingSavingsConfirmAction();
        }
    };

    const modal = new bootstrap.Modal(document.getElementById('modalSavingsConfirm'));
    modal.show();
}

// ----------------------------------------------------
// INITIALIZE CUSTOM SELECTORS & PICKERS
// ----------------------------------------------------
function initCustomSelectPickers() {
    // 1. Category Options
    const catList = document.getElementById('categoryOptionsList');
    if (catList) {
        catList.innerHTML = CATEGORY_PRESETS.map(cat => `
            <div class="custom-select-option ${cat.value === 'Kendaraan' ? 'selected' : ''}" onclick="selectCategoryOption('${cat.value}', '${cat.label}', '${cat.icon}', '${cat.bg}', '${cat.color}')">
                <div class="custom-select-content">
                    <div class="custom-select-icon" style="background:${cat.bg};color:${cat.color};">
                        <i class="bi ${cat.icon}"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="custom-select-label">${cat.label}</div>
                        <div class="custom-select-sub">${cat.sub}</div>
                    </div>
                </div>
                <i class="bi bi-check-lg custom-select-check"></i>
            </div>
        `).join('');
    }

    // 2. Icon Grid
    const iconGrid = document.getElementById('goalIconGrid');
    if (iconGrid) {
        iconGrid.innerHTML = ICON_PRESETS.map(item => `
            <div class="icon-picker-item ${item.icon === 'bi-piggy-bank-fill' ? 'selected' : ''}" onclick="selectGoalIcon('${item.icon}', this)">
                <i class="bi ${item.icon}"></i>
                <span>${item.label}</span>
            </div>
        `).join('');
    }

    // 3. Color Grid
    const colorGrid = document.getElementById('goalColorGrid');
    if (colorGrid) {
        colorGrid.innerHTML = COLOR_PRESETS.map(item => `
            <div class="color-swatch-item ${item.color === '#6366f1' ? 'selected' : ''}" style="background:${item.color};" onclick="selectGoalColor('${item.color}', this)" title="${item.name}">
                <i class="bi bi-check"></i>
            </div>
        `).join('');
    }

    // 4. Allocation Type Options
    const allocTypeList = document.getElementById('allocTypeOptionsList');
    if (allocTypeList) {
        allocTypeList.innerHTML = ALLOC_TYPE_PRESETS.map(type => `
            <div class="custom-select-option ${type.value === 'Bank / Rekening' ? 'selected' : ''}" onclick="selectAllocTypeOption('${type.value}', '${type.label}', '${type.icon}', '${type.bg}', '${type.color}', '${type.sub}')">
                <div class="custom-select-content">
                    <div class="custom-select-icon" style="background:${type.bg};color:${type.color};">
                        <i class="bi ${type.icon}"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="custom-select-label">${type.label}</div>
                        <div class="custom-select-sub">${type.sub}</div>
                    </div>
                </div>
                <i class="bi bi-check-lg custom-select-check"></i>
            </div>
        `).join('');
    }

    // 5. Mutual Fund Pickers (MI & Products)
    initMutualFundPickers();
}

function toggleCustomDropdown(menuId, e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById(menuId);
    if (!menu) return;
    const trigger = menu.previousElementSibling;
    const isOpen = menu.classList.contains('show');

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

// ----------------------------------------------------
// PPOB LIVE BALANCE INTEGRATION
// ----------------------------------------------------
let currentPpobBalanceNum = 0;

async function loadSavingsPpobBalance(isManualRefresh = false) {
    const valEl = document.getElementById('topPpobBalanceVal');
    const badgeEl = document.getElementById('ppobStatusLiveBadge');
    const refreshBtn = document.getElementById('btnRefreshPpobBalance');
    if (refreshBtn) refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:10px;height:10px;"></span> Refreshing...';

    try {
        let res = await fetch('<?= BASE_URL ?>api/savings/ppob-balance');
        if (!res.ok) {
            res = await fetch('<?= BASE_URL ?>api/ppob/balance');
        }
        const json = await res.json();
        let bal = null;
        if (json.success && json.data && json.data.deposit !== undefined) {
            bal = json.data.deposit;
        } else if (json.data && json.data.balance !== undefined) {
            bal = json.data.balance;
        } else if (json.deposit !== undefined) {
            bal = json.deposit;
        }

        if (bal !== null && !isNaN(bal)) {
            currentPpobBalanceNum = parseFloat(bal);
            const formatted = `Rp ${Math.round(currentPpobBalanceNum).toLocaleString('id-ID')}`;
            if (valEl) {
                valEl.innerHTML = formatted;
            }
            if (badgeEl) {
                badgeEl.className = 'badge bg-success bg-opacity-15 text-success border border-success border-opacity-25';
                badgeEl.innerHTML = '<i class="bi bi-circle-fill" style="font-size:6px;"></i> Live';
            }
            
            // Update helpers in modals
            const allocHelper = document.getElementById('allocPpobBalanceText');
            if (allocHelper) allocHelper.innerText = formatted;
            const quickAllocPill = document.getElementById('quickPpobAllocHelper');
            if (quickAllocPill) quickAllocPill.style.display = 'block';

            const mutHelper = document.getElementById('mutPpobBalanceText');
            if (mutHelper) mutHelper.innerText = formatted;
            const quickMutPill = document.getElementById('quickPpobMutHelper');
            if (quickMutPill) quickMutPill.style.display = 'block';

            if (isManualRefresh) {
                showSavingsToast('Saldo PPOB Diperbarui', `Saldo saat ini: ${formatted}`, 'success');
            }
        } else {
            const errMsg = json.error || json.message || 'Gagal memuat';
            if (valEl) valEl.innerHTML = `<span class="text-danger small" style="font-size:12px;"><i class="bi bi-exclamation-circle me-1"></i>${escapeHtml(errMsg)}</span>`;
            if (badgeEl) {
                badgeEl.className = 'badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25';
                badgeEl.innerHTML = '<i class="bi bi-x-circle-fill" style="font-size:6px;"></i> Offline';
            }
        }
    } catch (e) {
        console.error('Error fetching PPOB balance on savings page:', e);
        if (valEl) valEl.innerHTML = '<span class="text-danger small" style="font-size:13px;"><i class="bi bi-exclamation-circle me-1"></i>Offline</span>';
    } finally {
        if (refreshBtn) refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh';
    }
}

function refreshSavingsPpobBalance() {
    loadSavingsPpobBalance(true);
}

function copyPpobBalanceAmount() {
    if (!currentPpobBalanceNum || currentPpobBalanceNum <= 0) {
        showSavingsToast('Info', 'Saldo PPOB adalah Rp 0 atau belum termuat', 'info');
        return;
    }
    const amountStr = Math.round(currentPpobBalanceNum).toString();
    const formatted = `Rp ${Math.round(currentPpobBalanceNum).toLocaleString('id-ID')}`;
    
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(amountStr).then(() => {
            showSavingsToast('Berhasil Disalin!', `Nominal ${formatted} (${amountStr}) telah disalin ke clipboard`, 'success');
        }).catch(() => fallbackCopy(amountStr, formatted));
    } else {
        fallbackCopy(amountStr, formatted);
    }
}

function fallbackCopy(text, formatted) {
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    showSavingsToast('Berhasil Disalin!', `Nominal ${formatted} (${text}) telah disalin`, 'success');
}

function applyPpobBalanceToAllocInput() {
    if (!currentPpobBalanceNum || currentPpobBalanceNum <= 0) return;
    const amountInput = document.getElementById('formAllocAmount');
    if (amountInput) {
        amountInput.value = Math.round(currentPpobBalanceNum).toLocaleString('id-ID');
        amountInput.focus();
    }
    const nameInput = document.getElementById('formAllocName');
    if (nameInput && !nameInput.value.trim()) {
        nameInput.value = 'Saldo PPOB (Digiflazz)';
    }
    const instInput = document.getElementById('formAllocInstitution');
    if (instInput && !instInput.value.trim()) {
        instInput.value = 'Digiflazz / PPOB';
    }
    // Select PPOB / Digiflazz type
    selectAllocTypeOption('PPOB / Digiflazz', 'Saldo PPOB (Digiflazz / Pulsa)', 'bi-wallet2', 'rgba(16,185,129,0.15)', '#10b981', 'Saldo deposit Digiflazz / PPOB');
    showSavingsToast('Nominal Diisi', `Nominal Rp ${Math.round(currentPpobBalanceNum).toLocaleString('id-ID')} berhasil dimasukkan ke form`, 'info');
}

function applyPpobBalanceToMutInput() {
    if (!currentPpobBalanceNum || currentPpobBalanceNum <= 0) return;
    const amountInput = document.getElementById('formMutAmount');
    if (amountInput) {
        amountInput.value = Math.round(currentPpobBalanceNum).toLocaleString('id-ID');
        amountInput.focus();
    }
    const notesInput = document.getElementById('formMutNotes');
    if (notesInput && !notesInput.value.trim()) {
        notesInput.value = 'Penyesuaian Saldo PPOB Digiflazz';
    }
    showSavingsToast('Nominal Diisi', `Nominal Rp ${Math.round(currentPpobBalanceNum).toLocaleString('id-ID')} berhasil dimasukkan ke form mutasi`, 'info');
}

// ----------------------------------------------------
// TAB NAVIGATION (GOALS VS REKSADANA)
// ----------------------------------------------------
function switchSavingsTab(tabName) {
    const goalsView = document.getElementById('sectionGoalsView');
    const mfView = document.getElementById('sectionMutualFundsView');
    const tabGoals = document.getElementById('tabBtnGoals');
    const tabMf = document.getElementById('tabBtnMutualFunds');

    if (tabName === 'reksadana') {
        if (goalsView) goalsView.style.display = 'none';
        if (mfView) mfView.style.display = 'block';
        if (tabGoals) tabGoals.classList.remove('active');
        if (tabMf) tabMf.classList.add('active');
        renderMutualFundsGrid();
    } else {
        if (goalsView) goalsView.style.display = 'block';
        if (mfView) mfView.style.display = 'none';
        if (tabGoals) tabGoals.classList.add('active');
        if (tabMf) tabMf.classList.remove('active');
    }
}

// ----------------------------------------------------
// MUTUAL FUNDS (REKSADANA) PORTFOLIO CONTROLLER
// ----------------------------------------------------

async function loadMutualFunds() {
    try {
        const res = await fetch('<?= BASE_URL ?>api/savings/mutual-funds');
        const json = await res.json();
        if (json.success) {
            allMutualFunds = Array.isArray(json.data) ? json.data : [];
            mutualFundsSummary = json.summary || null;
            
            // Update Tab Count Badge
            const badge = document.getElementById('badgeMfCount');
            if (badge) badge.innerText = allMutualFunds.length;

            renderMutualFundsGrid();
            if (mutualFundsSummary) {
                updateMutualFundsSummaryUI(mutualFundsSummary);
            }
        }
    } catch (e) {
        console.error('Error loading mutual funds:', e);
    }
}

async function loadMasterCatalog() {
    try {
        const res = await fetch('<?= BASE_URL ?>api/savings/mutual-funds/master');
        const json = await res.json();
        if (json.success && json.data && json.data.products) {
            masterCatalogProducts = json.data.products;
            initMutualFundPickers();
        }
    } catch (e) {
        console.error('Error loading master mutual funds catalog:', e);
    }
}

function updateMutualFundsSummaryUI(summary) {
    document.getElementById('mfStatTotalInvested').textContent = formatRupiah(summary.total_invested);
    document.getElementById('mfStatTotalFunds').textContent = `${summary.total_funds} Produk Reksadana`;
    document.getElementById('mfStatCurrentValue').textContent = formatRupiah(summary.total_current_value);

    // Total Return / P&L
    const pnlEl = document.getElementById('mfStatTotalPnl');
    const pnlIconBox = document.getElementById('mfPnlIconBox');
    const returnSub = document.getElementById('mfStatReturnSub');
    const isProfit = summary.is_overall_profit;
    const sign = isProfit ? '+' : '';
    const pnlFormatted = `${sign}${formatRupiah(summary.total_pnl)} (${sign}${summary.total_return_pct}%)`;

    pnlEl.textContent = pnlFormatted;
    pnlEl.style.color = isProfit ? 'var(--success)' : 'var(--danger)';
    if (pnlIconBox) {
        pnlIconBox.style.background = isProfit ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)';
        pnlIconBox.style.color = isProfit ? 'var(--success)' : 'var(--danger)';
    }
    if (returnSub) {
        returnSub.textContent = isProfit ? 'Floating Profit' : 'Floating Loss';
    }

    // Daily Return
    const dailyPnlEl = document.getElementById('mfStatDailyPnl');
    const dailySign = summary.total_daily_pnl >= 0 ? '+' : '';
    dailyPnlEl.textContent = `${dailySign}${formatRupiah(summary.total_daily_pnl)}`;
    dailyPnlEl.style.color = summary.total_daily_pnl >= 0 ? '#10b981' : '#ef4444';

    // Top Performer
    const topPerfEl = document.getElementById('mfStatTopPerformer');
    if (topPerfEl) {
        if (summary.top_performer) {
            topPerfEl.innerHTML = `Top: <strong style="color:var(--text-primary);">${summary.top_performer.fund_name.substring(0, 18)}...</strong> (+${summary.top_performer.unrealized_pnl_pct}%)`;
        } else {
            topPerfEl.textContent = 'Top: Belum ada data';
        }
    }

    // Diversification Bar & Pills
    const barCont = document.getElementById('mfDistBarContainer');
    const pillsCont = document.getElementById('mfDistPillsContainer');
    const typeBreakdown = summary.type_breakdown || [];
    const palette = ['#3b82f6', '#10b981', '#6366f1', '#f59e0b', '#ec4899', '#8b5cf6'];

    if (typeBreakdown.length === 0) {
        barCont.innerHTML = '<div class="dist-bar-segment" style="width:100%;background:var(--surface-3);"></div>';
        pillsCont.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--text-muted);font-size:11px;padding:10px 0;">Belum ada reksadana. Tambahkan produk untuk melihat grafik alokasi aset.</div>';
    } else {
        barCont.innerHTML = typeBreakdown.map((item, idx) => {
            const bg = palette[idx % palette.length];
            return `<div class="dist-bar-segment" style="width:${Math.max(2, item.percentage)}%;background:${bg};" title="${item.type}: ${formatRupiah(item.total_amount)} (${item.percentage}%)"></div>`;
        }).join('');

        pillsCont.innerHTML = typeBreakdown.map((item, idx) => {
            const bg = palette[idx % palette.length];
            let icon = 'bi-wallet2';
            if (item.type.includes('Pasar Uang')) icon = 'bi-cash-coin';
            else if (item.type.includes('Pendapatan Tetap')) icon = 'bi-shield-check';
            else if (item.type.includes('Saham')) icon = 'bi-graph-up-arrow';
            else if (item.type.includes('Campuran')) icon = 'bi-pie-chart-fill';
            else if (item.type.includes('Index')) icon = 'bi-bar-chart-line-fill';

            return `
                <div class="dist-pill-item">
                    <div style="width: 28px; height: 28px; border-radius: 7px; background: ${bg}22; color: ${bg}; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;">
                        <i class="bi ${icon}"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 9.5px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 700; text-transform: uppercase;">
                            ${item.type}
                        </div>
                        <div style="font-size: 11.5px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${formatRupiah(item.total_amount)}
                        </div>
                        <div style="font-size: 9px; color: ${bg}; font-weight: 700;">
                            ${item.percentage}% &middot; ${item.fund_count} produk
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
}

function renderMutualFundsGrid() {
    const container = document.getElementById('mfGridContainer');
    if (!container) return;

    let list = [...allMutualFunds];

    // Filter by type
    if (currentMfFilter !== 'all') {
        list = list.filter(f => f.fund_type === currentMfFilter);
    }

    // Filter by search keyword
    if (searchMfKeyword) {
        list = list.filter(f => {
            const matchName = (f.fund_name || '').toLowerCase().includes(searchMfKeyword);
            const matchHouse = (f.fund_house || '').toLowerCase().includes(searchMfKeyword);
            const matchType = (f.fund_type || '').toLowerCase().includes(searchMfKeyword);
            const matchPlatform = (f.platform || '').toLowerCase().includes(searchMfKeyword);
            return matchName || matchHouse || matchType || matchPlatform;
        });
    }

    if (list.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 45px 20px; background: var(--surface-1); border: 1px dashed var(--border-color); border-radius: var(--radius-lg);">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(59,130,246,0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 12px;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h5 style="font-weight: 800; font-size: 1rem; color: var(--text-primary); margin-bottom: 6px;">
                    ${searchMfKeyword || currentMfFilter !== 'all' ? 'Tidak Ada Reksadana yang Cocok' : 'Belum Ada Portofolio Reksadana'}
                </h5>
                <p style="font-size: 11.5px; color: var(--text-muted); max-width: 400px; margin: 0 auto 16px;">
                    ${searchMfKeyword || currentMfFilter !== 'all' ? 'Coba ubah kata kunci pencarian atau filter kategori reksadana Anda.' : 'Mulai pantau investasi reksadana Anda dengan harga NAB real-time dan hitung total unit & keuntungan secara otomatis.'}
                </p>
                ${!searchMfKeyword && currentMfFilter === 'all' ? `
                    <button class="btn-primary-custom" onclick="openAddMutualFundModal()" style="padding: 8px 18px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Reksadana Sekarang
                    </button>
                ` : `
                    <button class="btn-primary-custom" onclick="clearSearchMutualFunds(); filterMutualFunds('all', document.querySelector('#mfFilterGroup .btn-filter'));" style="background: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color); padding: 7px 14px; font-size: 11.5px;">
                        Reset Filter
                    </button>
                `}
            </div>
        `;
        return;
    }

    container.innerHTML = list.map(f => {
        const isProfit = f.is_profit;
        const pnlSign = isProfit ? '+' : '';
        const pnlFormatted = `${pnlSign}${formatRupiah(f.unrealized_pnl)} (${pnlSign}${f.unrealized_pnl_pct}%)`;
        const pnlClass = isProfit ? 'profit' : 'loss';
        const pnlIcon = isProfit ? 'bi-arrow-up-right' : 'bi-arrow-down-right';

        let typeBadgeBg = 'rgba(59,130,246,0.12)';
        let typeBadgeColor = '#3b82f6';
        if (f.fund_type === 'Pasar Uang') { typeBadgeBg = 'rgba(16,185,129,0.12)'; typeBadgeColor = '#10b981'; }
        else if (f.fund_type === 'Pendapatan Tetap') { typeBadgeBg = 'rgba(99,102,241,0.12)'; typeBadgeColor = '#818cf8'; }
        else if (f.fund_type === 'Saham') { typeBadgeBg = 'rgba(236,72,153,0.12)'; typeBadgeColor = '#ec4899'; }
        else if (f.fund_type === 'Campuran') { typeBadgeBg = 'rgba(245,158,11,0.12)'; typeBadgeColor = '#f59e0b'; }

        return `
            <div class="mf-card" id="mfCard_${f.id}">
                <!-- Header -->
                <div class="mf-card-header">
                    <div style="min-width: 0; flex: 1;">
                        <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap; margin-bottom: 4px;">
                            <span class="badge-custom" style="background:${typeBadgeBg}; color:${typeBadgeColor}; font-size: 9px; font-weight: 800;">
                                ${escapeHtml(f.fund_type)}
                            </span>
                            ${f.is_syariah ? '<span class="badge-custom" style="background:rgba(16,185,129,0.12); color:#10b981; font-size: 9px; font-weight: 800;"><i class="bi bi-moon-stars-fill me-1"></i>Syariah</span>' : ''}
                            <span class="badge-custom" style="background:var(--surface-2); color:var(--text-muted); font-size: 9px;">
                                ${escapeHtml(f.platform || 'Bibit')}
                            </span>
                        </div>
                        <div class="mf-card-title">${escapeHtml(f.fund_name)}</div>
                        <div class="mf-card-house"><i class="bi bi-building me-1"></i>${escapeHtml(f.fund_house)}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <button class="btn btn-sm" type="button" title="Lihat Riwayat NAB & Aset" onclick="openMutualFundHistoryModal(${f.id}, '${escapeHtml(f.fund_name).replace(/'/g, "\\'")}')" style="background: rgba(59,130,246,0.12); color: #3b82f6; border: 1px solid rgba(59,130,246,0.25); border-radius: 6px; padding: 4px 8px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="bi bi-clock-history"></i> Riwayat
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown" style="background: var(--surface-2); color: var(--text-muted); border: 1px solid var(--border-color); border-radius: 6px; width: 28px; height: 28px; padding: 0;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" style="background: var(--surface-1); border: 1px solid var(--border-color); font-size: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                                <li><a class="dropdown-item text-primary" href="javascript:void(0)" onclick="openEditMutualFundModal(${f.id})"><i class="bi bi-pencil-square me-2"></i>Edit Reksadana</a></li>
                                <li><a class="dropdown-item text-info" href="javascript:void(0)" onclick="openMutualFundHistoryModal(${f.id}, '${escapeHtml(f.fund_name).replace(/'/g, "\\'")}')"><i class="bi bi-clock-history me-2"></i>Riwayat Perubahan NAB</a></li>
                                <li><hr class="dropdown-divider" style="border-color: var(--border-color);"></li>
                                <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteMutualFund(${f.id}, '${escapeHtml(f.fund_name).replace(/'/g, "\\'")}')"><i class="bi bi-trash me-2"></i>Hapus dari Portofolio</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Metrics Box -->
                <div class="mf-metric-box">
                    <div class="mf-metric-row">
                        <span class="mf-metric-label">Modal Pembelian:</span>
                        <span class="mf-metric-val">${formatRupiah(f.invested_amount)}</span>
                    </div>
                    <div class="mf-metric-row">
                        <span class="mf-metric-label">Total Unit Dimiliki:</span>
                        <span class="mf-metric-val" style="color: #3b82f6; font-family: monospace; font-weight: 800;">${parseFloat(f.units_owned).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })} Unit</span>
                    </div>
                    <div class="mf-metric-row">
                        <span class="mf-metric-label">Harga Beli (NAB Awal):</span>
                        <span class="mf-metric-val">Rp ${parseFloat(f.buy_nav).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })}</span>
                    </div>
                    <div class="mf-metric-row" style="border-top: 1px dashed var(--border-color); margin-top: 3px; padding-top: 4px;">
                        <span class="mf-metric-label">Harga NAB Terkini (Live):</span>
                        <span class="mf-metric-val" style="color: var(--success); font-weight: 800;">
                            Rp ${parseFloat(f.current_nav).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })}
                            <span style="font-size: 9.5px; font-weight: 800; color: ${f.daily_change_pct >= 0 ? '#10b981' : '#ef4444'}; margin-left: 3px;">
                                ${f.daily_change_pct >= 0 ? '+' : ''}${f.daily_change_pct}%
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Current Asset Value & PnL Highlight -->
                <div style="background: linear-gradient(135deg, rgba(59,130,246,0.06), rgba(99,102,241,0.06)); border: 1px solid rgba(59,130,246,0.18); border-radius: var(--radius-md); padding: 10px 12px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                            Total Nilai Aset Saat Ini
                        </span>
                        <span style="font-size: 9.5px; color: var(--text-muted);">
                            (Unit &times; NAB Saat Ini)
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 6px; margin-top: 2px;">
                        <span class="mf-val-highlight">${formatRupiah(f.current_value)}</span>
                        <span class="mf-pnl-pill ${pnlClass}">
                            <i class="bi ${pnlIcon}"></i> ${pnlFormatted}
                        </span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mf-card-footer">
                    <span><i class="bi bi-calendar-event me-1"></i>Beli: ${formatIndoDate(f.buy_date)}</span>
                    <span style="cursor: pointer; color: var(--primary); font-weight: 700;" onclick="openEditMutualFundModal(${f.id})">
                        Kelola <i class="bi bi-chevron-right"></i>
                    </span>
                </div>
            </div>
        `;
    }).join('');
}

function onSearchMutualFunds(query) {
    searchMfKeyword = (query || '').toLowerCase().trim();
    const btnClear = document.getElementById('btnClearMfSearch');
    if (btnClear) btnClear.style.display = searchMfKeyword ? 'inline-block' : 'none';
    renderMutualFundsGrid();
}

function clearSearchMutualFunds() {
    const input = document.getElementById('searchMfInput');
    if (input) input.value = '';
    onSearchMutualFunds('');
}

function filterMutualFunds(type, el) {
    currentMfFilter = type;
    document.querySelectorAll('#mfFilterGroup .btn-filter').forEach(b => b.classList.remove('active'));
    if (el) el.classList.add('active');
    renderMutualFundsGrid();
}

// ----------------------------------------------------
// SMART CALCULATION & AUTOCOMPLETE HANDLERS
// ----------------------------------------------------

function calcMfUnitsOrNav(changedField) {
    const investedStr = document.getElementById('formMfInvested').value || '0';
    const invested = parseRupiahInput(investedStr);
    const buyNav = parseFloat(document.getElementById('formMfBuyNav').value) || 0;
    const units = parseFloat(document.getElementById('formMfUnits').value) || 0;

    if (changedField === 'invested' || changedField === 'buy_nav') {
        if (invested > 0 && buyNav > 0) {
            const calculatedUnits = round(invested / buyNav, 4);
            document.getElementById('formMfUnits').value = calculatedUnits;
        }
    } else if (changedField === 'units') {
        if (invested > 0 && units > 0) {
            const calculatedNav = round(invested / units, 4);
            document.getElementById('formMfBuyNav').value = calculatedNav;
        }
    }
}

function round(value, decimals) {
    return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
}

function parseRupiahInput(val) {
    return parseFloat(String(val).replace(/[^0-9]/g, '')) || 0;
}

function onMfTypeSelectChange(type) {
    document.getElementById('formMfType').value = type;
}

// Dynamic Fund Houses from catalog
function getDynamicFundHouses() {
    const counts = {};
    (masterCatalogProducts || []).forEach(p => {
        const h = p.fund_house || 'Lainnya';
        counts[h] = (counts[h] || 0) + 1;
    });

    const list = [
        { name: 'Semua Manajer Investasi', code: 'all', count: (masterCatalogProducts || []).length, icon: 'bi-grid-fill' }
    ];

    Object.keys(counts).sort().forEach(h => {
        list.push({
            name: h,
            code: h.toLowerCase().replace(/[^a-z0-9]/g, '_'),
            count: counts[h],
            icon: 'bi-building'
        });
    });

    return list;
}

function initMutualFundPickers() {
    renderMfHouseOptions();
    renderMfProductOptions();
}

async function loadMasterCatalog() {
    try {
        const res = await fetch('<?= BASE_URL ?>api/savings/mutual-funds/master');
        const json = await res.json();
        if (json.success && json.data && Array.isArray(json.data.products) && json.data.products.length > 0) {
            masterCatalogProducts = json.data.products;
            initMutualFundPickers();
        }
    } catch (e) {
        console.warn('Fallback to inline master mutual fund catalog:', e);
    }
}

function renderMfHouseOptions() {
    const listEl = document.getElementById('mfHouseOptionsList');
    if (!listEl) return;

    const currentHouse = selectedMfHouseFilter || document.getElementById('formMfHouse')?.value || 'Semua Manajer Investasi';
    const houses = getDynamicFundHouses();

    listEl.innerHTML = houses.map(h => {
        const isSelected = (h.name === currentHouse || (h.code === 'all' && (!selectedMfHouseFilter || selectedMfHouseFilter === 'Semua Manajer Investasi')));
        return `
            <div class="custom-select-option ${isSelected ? 'selected' : ''}" onclick="selectMfHouseOption('${h.name.replace(/'/g, "\\'")}')">
                <div class="custom-select-content">
                    <div class="custom-select-icon" style="background: rgba(99,102,241,0.12); color: #818cf8;">
                        <i class="bi ${h.icon || 'bi-building'}"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="custom-select-label">${escapeHtml(h.name)}</div>
                        <div class="custom-select-sub">${h.count} Produk Tersedia</div>
                    </div>
                </div>
                <i class="bi bi-check-lg custom-select-check"></i>
            </div>
        `;
    }).join('');
}

function selectMfHouseOption(houseName) {
    if (houseName === 'Semua Manajer Investasi' || houseName === 'all') {
        selectedMfHouseFilter = '';
        const houseLabel = document.getElementById('mfHouseSelectedLabel');
        if (houseLabel) houseLabel.textContent = 'Semua Manajer Investasi';
    } else {
        selectedMfHouseFilter = houseName;
        const houseInput = document.getElementById('formMfHouse');
        if (houseInput) houseInput.value = houseName;
        const houseLabel = document.getElementById('mfHouseSelectedLabel');
        if (houseLabel) houseLabel.textContent = houseName;
    }

    renderMfHouseOptions();
    renderMfProductOptions();

    // Close menu
    const menu = document.getElementById('mfHousePickerMenu');
    if (menu) menu.classList.remove('show');
}

function setMfProductCatFilter(cat, btnEl) {
    currentMfCategoryFilter = cat;
    const strip = document.getElementById('mfProductCatFilterStrip');
    if (strip) {
        strip.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
    }
    if (btnEl) btnEl.classList.add('active');
    renderMfProductOptions();
}

function onFilterMfProductList(query) {
    searchMfDropdownKeyword = (query || '').toLowerCase().trim();
    renderMfProductOptions();
}

function renderMfProductOptions() {
    const listEl = document.getElementById('mfProductOptionsList');
    if (!listEl) return;

    let products = masterCatalogProducts || [];

    // Filter by MI if selected
    if (selectedMfHouseFilter && selectedMfHouseFilter !== 'Semua Manajer Investasi') {
        const houseLower = selectedMfHouseFilter.toLowerCase();
        products = products.filter(p => p.fund_house.toLowerCase().includes(houseLower) || houseLower.includes(p.fund_house.toLowerCase()));
    }

    // Filter by Category if selected
    if (currentMfCategoryFilter && currentMfCategoryFilter !== 'all') {
        const catLower = currentMfCategoryFilter.toLowerCase();
        products = products.filter(p => {
            const pType = (p.type || '').toLowerCase();
            if (catLower === 'pendapatan tetap') {
                return pType.includes('pendapatan tetap') || pType.includes('obligasi');
            }
            return pType.includes(catLower);
        });
    }

    // Filter by Search Keyword if typed
    if (searchMfDropdownKeyword) {
        const kw = searchMfDropdownKeyword;
        products = products.filter(p => {
            const haystack = `${p.name} ${p.fund_house} ${p.type} ${p.code} ${p.is_syariah ? 'syariah' : ''}`.toLowerCase();
            return haystack.includes(kw);
        });
    }

    if (products.length === 0) {
        listEl.innerHTML = `
            <div style="padding: 18px 14px; text-align: center; color: var(--text-muted); font-size: 12px;">
                <i class="bi bi-search" style="font-size: 20px; display: block; margin-bottom: 6px; opacity: 0.5;"></i>
                Tidak ada produk reksadana yang cocok dengan filter / pencarian ini.
                <div style="margin-top: 8px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-sm btn-outline-primary" style="font-size: 11px;" onclick="resetMfProductFilters()">Reset Filter & Tampilkan Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" style="font-size: 11px;" onclick="toggleCustomMfInput()">Ketik Manual</button>
                </div>
            </div>
        `;
        return;
    }

    const currentName = document.getElementById('formMfName')?.value || '';

    listEl.innerHTML = products.map(p => {
        const isSelected = (p.name === currentName);
        let badgeColor = '#3b82f6';
        let badgeBg = 'rgba(59,130,246,0.12)';
        if (p.type.includes('Pasar Uang')) { badgeColor = '#10b981'; badgeBg = 'rgba(16,185,129,0.12)'; }
        else if (p.type.includes('Pendapatan Tetap') || p.type.includes('Obligasi')) { badgeColor = '#818cf8'; badgeBg = 'rgba(99,102,241,0.12)'; }
        else if (p.type.includes('Saham')) { badgeColor = '#ec4899'; badgeBg = 'rgba(236,72,153,0.12)'; }
        else if (p.type.includes('Campuran')) { badgeColor = '#f59e0b'; badgeBg = 'rgba(245,158,11,0.12)'; }

        return `
            <div class="custom-select-option ${isSelected ? 'selected' : ''}" onclick="selectMfProductOption('${p.code}')">
                <div class="custom-select-content">
                    <div class="custom-select-icon" style="background: ${badgeBg}; color: ${badgeColor};">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="custom-select-label" style="font-size: 11.5px; font-weight: 700;">${escapeHtml(p.name)}</div>
                        <div class="custom-select-sub" style="display: flex; gap: 6px; align-items: center; margin-top: 2px; flex-wrap: wrap;">
                            <span style="color: var(--text-muted);">${escapeHtml(p.fund_house)}</span>
                            <span>&middot;</span>
                            <span style="color: ${badgeColor}; font-weight: 700;">${escapeHtml(p.type)}</span>
                            ${p.is_syariah ? '<span class="badge" style="background:rgba(16,185,129,0.12);color:#10b981;font-size:8.5px;padding:1px 4px;">Syariah</span>' : ''}
                            <span>&middot;</span>
                            <span style="color: var(--success); font-weight: 700;">NAB: Rp ${p.current_nav.toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                </div>
                <i class="bi bi-check-lg custom-select-check"></i>
            </div>
        `;
    }).join('');
}

function resetMfProductFilters() {
    selectedMfHouseFilter = '';
    currentMfCategoryFilter = 'all';
    searchMfDropdownKeyword = '';
    const searchInp = document.getElementById('inputSearchMfProducts');
    if (searchInp) searchInp.value = '';
    const strip = document.getElementById('mfProductCatFilterStrip');
    if (strip) {
        strip.querySelectorAll('.btn-filter').forEach((b, i) => {
            if (i === 0) b.classList.add('active');
            else b.classList.remove('active');
        });
    }
    const houseLabel = document.getElementById('mfHouseSelectedLabel');
    if (houseLabel) houseLabel.textContent = 'Semua Manajer Investasi';
    renderMfHouseOptions();
    renderMfProductOptions();
}

function selectMfProductOption(code) {
    const item = masterCatalogProducts.find(p => p.code === code);
    if (!item) return;

    // Set Product Name
    document.getElementById('formMfName').value = item.name;
    document.getElementById('mfProductSelectedLabel').textContent = item.name;
    document.getElementById('mfProductSelectedSub').innerHTML = `${item.type} &middot; NAB: Rp ${item.current_nav.toLocaleString('id-ID')}`;

    // Set House
    document.getElementById('formMfHouse').value = item.fund_house;
    document.getElementById('mfHouseSelectedLabel').textContent = item.fund_house;
    selectedMfHouseFilter = item.fund_house;

    // Set Type
    document.getElementById('formMfTypeSelect').value = item.type;
    document.getElementById('formMfType').value = item.type;

    // Set Syariah
    document.getElementById('formMfIsSyariah').checked = !!item.is_syariah;

    // Prefill Buy NAV if empty, and Live NAV
    const buyNavInput = document.getElementById('formMfBuyNav');
    if (!buyNavInput.value || parseFloat(buyNavInput.value) <= 0) {
        buyNavInput.value = item.current_nav;
    }
    document.getElementById('formMfCurrentNav').value = item.current_nav;

    // Auto calculate units or modal
    calcMfUnitsOrNav('buy_nav');

    // Close menu & refresh options
    renderMfHouseOptions();
    renderMfProductOptions();
    const menu = document.getElementById('mfProductPickerMenu');
    if (menu) menu.classList.remove('show');
}

function toggleCustomMfInput() {
    isCustomMfInput = !isCustomMfInput;
    const picker = document.getElementById('mfProductPickerContainer');
    const manualInput = document.getElementById('formMfNameManual');

    if (isCustomMfInput) {
        if (picker) picker.style.display = 'none';
        if (manualInput) {
            manualInput.style.display = 'block';
            manualInput.value = document.getElementById('formMfName')?.value || '';
            manualInput.focus();
        }
    } else {
        if (picker) picker.style.display = 'block';
        if (manualInput) {
            manualInput.style.display = 'none';
            if (manualInput.value.trim()) {
                document.getElementById('formMfName').value = manualInput.value.trim();
                document.getElementById('mfProductSelectedLabel').textContent = manualInput.value.trim();
            }
        }
    }
}

// ----------------------------------------------------
// MODAL ACTIONS (ADD, EDIT, SUBMIT, DELETE, REFRESH)
// ----------------------------------------------------

function openAddMutualFundModal() {
    document.getElementById('mfFormTitle').textContent = 'Tambah Produk Reksadana';
    document.getElementById('formMfId').value = '';
    selectedMfHouseFilter = '';
    currentMfCategoryFilter = 'all';
    searchMfDropdownKeyword = '';
    const searchInp = document.getElementById('inputSearchMfProducts');
    if (searchInp) searchInp.value = '';
    const strip = document.getElementById('mfProductCatFilterStrip');
    if (strip) {
        strip.querySelectorAll('.btn-filter').forEach((b, i) => {
            if (i === 0) b.classList.add('active');
            else b.classList.remove('active');
        });
    }

    isCustomMfInput = false;
    document.getElementById('mfProductPickerContainer').style.display = 'block';
    document.getElementById('formMfNameManual').style.display = 'none';

    // Pick first product from catalog
    if (masterCatalogProducts && masterCatalogProducts.length > 0) {
        const firstProduct = masterCatalogProducts[0];
        selectMfProductOption(firstProduct.code);
    } else {
        document.getElementById('formMfName').value = '';
        document.getElementById('mfProductSelectedLabel').textContent = 'Pilih Produk Reksadana...';
        document.getElementById('mfProductSelectedSub').textContent = 'Pilih dari katalog';
    }

    document.getElementById('formMfInvested').value = '';
    document.getElementById('formMfBuyNav').value = '';
    document.getElementById('formMfUnits').value = '';
    document.getElementById('formMfPlatform').value = 'Bibit';
    document.getElementById('formMfBuyDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('formMfNotes').value = '';

    renderMfHouseOptions();
    renderMfProductOptions();

    const modal = new bootstrap.Modal(document.getElementById('modalMutualFundForm'));
    modal.show();
}

function openEditMutualFundModal(id) {
    const fund = allMutualFunds.find(f => f.id == id);
    if (!fund) return;

    document.getElementById('mfFormTitle').textContent = 'Edit Data Reksadana';
    document.getElementById('formMfId').value = fund.id;
    document.getElementById('formMfHouse').value = fund.fund_house;
    document.getElementById('mfHouseSelectedLabel').textContent = fund.fund_house;
    selectedMfHouseFilter = fund.fund_house;

    document.getElementById('formMfName').value = fund.fund_name;
    document.getElementById('mfProductSelectedLabel').textContent = fund.fund_name;
    document.getElementById('mfProductSelectedSub').innerHTML = `${fund.fund_type} &middot; NAB: Rp ${parseFloat(fund.current_nav).toLocaleString('id-ID')}`;

    document.getElementById('formMfTypeSelect').value = fund.fund_type;
    document.getElementById('formMfType').value = fund.fund_type;
    document.getElementById('formMfInvested').value = Math.round(fund.invested_amount).toLocaleString('id-ID');
    document.getElementById('formMfBuyNav').value = fund.buy_nav;
    document.getElementById('formMfUnits').value = fund.units_owned;
    document.getElementById('formMfCurrentNav').value = fund.current_nav;
    document.getElementById('formMfPlatform').value = fund.platform || 'Bibit';
    document.getElementById('formMfBuyDate').value = fund.buy_date || new Date().toISOString().split('T')[0];
    document.getElementById('formMfIsSyariah').checked = !!parseInt(fund.is_syariah);
    document.getElementById('formMfNotes').value = fund.notes || '';

    // Check if fund name exists in master catalog
    const matchedMaster = masterCatalogProducts.find(p => p.name.toLowerCase() === fund.fund_name.toLowerCase());
    if (matchedMaster) {
        isCustomMfInput = false;
        document.getElementById('mfProductPickerContainer').style.display = 'block';
        document.getElementById('formMfNameManual').style.display = 'none';
    } else {
        isCustomMfInput = true;
        document.getElementById('mfProductPickerContainer').style.display = 'none';
        document.getElementById('formMfNameManual').style.display = 'block';
        document.getElementById('formMfNameManual').value = fund.fund_name;
    }

    renderMfHouseOptions();
    renderMfProductOptions();

    const modal = new bootstrap.Modal(document.getElementById('modalMutualFundForm'));
    modal.show();
}

async function submitMutualFundForm(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitMf');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    const id = document.getElementById('formMfId').value;
    const name = isCustomMfInput ? document.getElementById('formMfNameManual').value : document.getElementById('formMfName').value;
    const house = document.getElementById('formMfHouse').value;
    const type = document.getElementById('formMfType').value;
    const invested = parseRupiahInput(document.getElementById('formMfInvested').value);
    const buyNav = parseFloat(document.getElementById('formMfBuyNav').value) || 0;
    const units = parseFloat(document.getElementById('formMfUnits').value) || 0;
    const currentNav = parseFloat(document.getElementById('formMfCurrentNav').value) || buyNav;
    const platform = document.getElementById('formMfPlatform').value;
    const buyDate = document.getElementById('formMfBuyDate').value;
    const isSyariah = document.getElementById('formMfIsSyariah').checked ? 1 : 0;
    const notes = document.getElementById('formMfNotes').value;

    if (!name || !name.trim()) {
        showSavingsToast('Peringatan', 'Silakan pilih atau masukkan nama produk reksadana', 'warning');
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
    }

    const payload = {
        fund_name: name.trim(),
        fund_house: house || 'Lainnya',
        fund_type: type,
        invested_amount: invested,
        buy_nav: buyNav,
        units_owned: units,
        current_nav: currentNav,
        platform: platform,
        buy_date: buyDate,
        is_syariah: isSyariah,
        notes: notes
    };

    const url = id ? `<?= BASE_URL ?>api/savings/mutual-funds/${id}/update` : `<?= BASE_URL ?>api/savings/mutual-funds`;

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.getElementById('csrfToken').value
            },
            body: JSON.stringify(payload)
        });
        const json = await res.json();

        if (json.success) {
            const modalEl = document.getElementById('modalMutualFundForm');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            showSavingsToast('Berhasil', json.message || 'Data reksadana berhasil disimpan', 'success');
            loadMutualFunds();
        } else {
            showSavingsToast('Gagal', json.error || 'Gagal menyimpan data reksadana', 'danger');
        }
    } catch (err) {
        console.error(err);
        showSavingsToast('Error', 'Terjadi kesalahan sistem', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function deleteMutualFund(id, name) {
    showSavingsConfirm({
        title: 'Hapus Reksadana',
        text: `Apakah Anda yakin ingin menghapus produk "${name}" dari portofolio investasi?`,
        icon: 'bi-trash-fill',
        iconColor: '#ef4444',
        iconBg: 'rgba(239,68,68,0.15)',
        confirmText: 'Ya, Hapus',
        confirmBtnColor: 'var(--danger)',
        onConfirm: async () => {
            try {
                const res = await fetch(`<?= BASE_URL ?>api/savings/mutual-funds/${id}/delete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.getElementById('csrfToken').value
                    }
                });
                const json = await res.json();
                if (json.success) {
                    showSavingsToast('Terhapus', json.message, 'success');
                    loadMutualFunds();
                } else {
                    showSavingsToast('Gagal', json.error, 'danger');
                }
            } catch (e) {
                console.error(e);
                showSavingsToast('Error', 'Gagal menghubungi server', 'danger');
            }
        }
    });
}

async function refreshMutualFundsLiveNav(isSilent = false) {
    const btn = document.getElementById('btnRefreshMfNav');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn && !isSilent) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengupdate NAB...';
    }

    try {
        const res = await fetch('<?= BASE_URL ?>api/savings/mutual-funds/refresh-nav', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.getElementById('csrfToken').value
            }
        });
        const json = await res.json();
        if (json.success) {
            allMutualFunds = Array.isArray(json.data) ? json.data : allMutualFunds;
            mutualFundsSummary = json.summary || mutualFundsSummary;
            renderMutualFundsGrid();
            if (mutualFundsSummary) updateMutualFundsSummaryUI(mutualFundsSummary);
            if (!isSilent) {
                showSavingsToast('NAB Terkini Berhasil Dimuat', `Pembaruan harga NAB real-time selesai (${json.meta ? json.meta.updated_count : 0} produk)`, 'success');
            }
        } else if (!isSilent) {
            showSavingsToast('Peringatan', json.error || 'Gagal memperbarui NAB', 'warning');
        }
    } catch (e) {
        console.error(e);
        if (!isSilent) {
            showSavingsToast('Error', 'Gagal memuat live NAB', 'danger');
        }
    } finally {
        if (btn && !isSilent) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
}

async function openMutualFundHistoryModal(fundId, fundName) {
    const titleEl = document.getElementById('mfHistoryModalTitle');
    const subEl = document.getElementById('mfHistoryModalSub');
    const listCont = document.getElementById('mfHistoryListContainer');

    if (titleEl) titleEl.textContent = `Riwayat NAB - ${fundName}`;
    if (subEl) subEl.textContent = 'Log update otomatis setiap jam & perhitungan total aset';
    if (listCont) {
        listCont.innerHTML = '<div style="text-align:center;padding:25px;color:var(--text-muted);font-size:12px;"><span class="spinner-border spinner-border-sm me-1"></span> Memuat log riwayat NAB...</div>';
    }

    const modal = new bootstrap.Modal(document.getElementById('modalMutualFundHistory'));
    modal.show();

    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/mutual-funds/${fundId}/history`);
        const json = await res.json();
        if (json.success && Array.isArray(json.data) && json.data.length > 0) {
            listCont.innerHTML = `
                <div class="table-responsive" style="margin: 0;">
                    <table class="table table-sm" style="font-size: 11.5px; margin: 0; color: var(--text-primary);">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 10px; text-transform: uppercase;">
                                <th>Waktu Update</th>
                                <th style="text-align: right;">NAB (Harga)</th>
                                <th style="text-align: center;">Perubahan</th>
                                <th style="text-align: right;">Total Unit</th>
                                <th style="text-align: right;">Total Aset (Unit &times; NAB)</th>
                                <th style="text-align: center;">Sumber</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${json.data.map(h => {
                                const changeSign = (h.daily_change_pct || 0) >= 0 ? '+' : '';
                                const changeColor = (h.daily_change_pct || 0) >= 0 ? '#10b981' : '#ef4444';
                                return `
                                    <tr style="border-bottom: 1px solid var(--border-color); vertical-align: middle;">
                                        <td>
                                            <div style="font-weight: 700;">${formatIndoDate(h.nav_date)}</div>
                                            <div style="font-size: 9.5px; color: var(--text-muted);">${(h.created_at || '').substring(11, 16)} WIB</div>
                                        </td>
                                        <td style="text-align: right; font-weight: 800; color: var(--text-primary);">
                                            Rp ${parseFloat(h.nav).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })}
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge" style="background: ${changeColor}22; color: ${changeColor}; font-size: 9px; font-weight: 700;">
                                                ${changeSign}${parseFloat(h.daily_change_pct || 0)}%
                                            </span>
                                        </td>
                                        <td style="text-align: right; font-family: monospace; color: #3b82f6;">
                                            ${parseFloat(h.total_units).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 })}
                                        </td>
                                        <td style="text-align: right; font-weight: 800; color: var(--success);">
                                            Rp ${Math.round(h.total_value).toLocaleString('id-ID')}
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge bg-secondary bg-opacity-15 text-muted" style="font-size: 9px;">
                                                ${escapeHtml(h.source || 'bareksa')}
                                            </span>
                                        </td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            listCont.innerHTML = `
                <div style="text-align: center; padding: 30px; color: var(--text-muted); font-size: 12px;">
                    <i class="bi bi-info-circle" style="font-size: 24px; display: block; margin-bottom: 6px; opacity: 0.5;"></i>
                    Belum ada riwayat NAB tersimpan untuk produk ini.<br>
                    Data akan otomatis dicatat setiap kali auto-update 1 jam atau tombol refresh NAB dijalankan.
                </div>
            `;
        }
    } catch (e) {
        console.error(e);
        listCont.innerHTML = '<div style="text-align:center;padding:25px;color:var(--danger);font-size:12px;">Gagal memuat riwayat NAB</div>';
    }
}

function syncMutualFundsToSavings() {
    if (!mutualFundsSummary || mutualFundsSummary.total_current_value <= 0) {
        showSavingsToast('Info', 'Belum ada aset reksadana atau total nilai Rp 0', 'info');
        return;
    }

    const totalVal = Math.round(mutualFundsSummary.total_current_value);
    showSavingsToast('Aset Reksadana Siap', `Total nilai aset: Rp ${totalVal.toLocaleString('id-ID')}. Anda dapat memilih Target Keuangan untuk menambahkan alokasi ini.`, 'info');
    
    // Switch to goals tab and prompt
    switchSavingsTab('goals');
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
                    <div style="width:28px;height:28px;border-radius:7px;background:${bg}22;color:${bg};display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">
                        <i class="bi ${icon}"></i>
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:9.5px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:700;text-transform:uppercase;">
                            ${item.account_type}
                        </div>
                        <div style="font-size:11.5px;font-weight:800;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
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
            <div style="grid-column:1/-1;text-align:center;padding:36px 18px;background:var(--surface-1);border:1px dashed var(--border-color);border-radius:var(--radius-lg);">
                <div style="width:50px;height:50px;border-radius:50%;background:rgba(99,102,241,0.12);color:#818cf8;display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 12px;">
                    <i class="bi bi-piggy-bank"></i>
                </div>
                <h5 style="font-weight:800;font-size:1rem;margin:0 0 4px 0;color:var(--text-primary);">
                    ${searchKeyword ? 'Tidak ada target yang cocok' : 'Belum Ada Target Tabungan'}
                </h5>
                <p style="font-size:11.5px;color:var(--text-muted);max-width:380px;margin:0 auto 16px;">
                    ${searchKeyword ? 'Coba cari dengan kata kunci lain atau bersihkan pencarian.' : 'Mulai rancang impian Anda seperti Tabungan Mobil, Dana Darurat, atau Modal Usaha dengan mengelompokkan uang di Toko, Bibit, Bank, dll.'}
                </p>
                <button class="btn-primary-custom" onclick="${searchKeyword ? 'clearSearchGoals()' : 'openAddGoalModal()'}" style="padding:8px 16px;border-radius:var(--radius-md);font-size:11.5px;display:inline-flex;align-items:center;gap:6px;">
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
            allocPreviewHtml = `<div style="font-size:10.5px;color:var(--text-muted);font-style:italic;">Belum ada pos penempatan uang</div>`;
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
                        <strong style="color:var(--text-primary);font-size:10.5px;flex-shrink:0;">${formatRupiah(a.amount)}</strong>
                    </div>
                `;
            }).join('');
            if (allocations.length > 3) {
                allocPreviewHtml += `<div style="font-size:9.5px;color:var(--text-muted);text-align:right;font-weight:700;">+ ${allocations.length - 3} pos lainnya</div>`;
            }
        }

        return `
            <div class="goal-card">
                <div class="goal-card-top-accent" style="background:${color};"></div>
                
                <div>
                    <!-- Header -->
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1;">
                            <div class="goal-icon-badge" style="background:${color}22;color:${color};">
                                <i class="bi ${icon}"></i>
                            </div>
                            <div style="min-width:0;flex:1;">
                                <div style="font-size:13.5px;font-weight:800;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;">
                                    ${escapeHtml(goal.name)}
                                </div>
                                <div style="display:flex;align-items:center;gap:5px;margin-top:2px;flex-wrap:wrap;">
                                    <span class="badge-custom badge-primary" style="font-size:9px;">${escapeHtml(goal.category || 'Lainnya')}</span>
                                    ${isAchieved ? '<span class="badge-custom badge-success" style="font-size:9px;">Tercapai 🎉</span>' : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Menu -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown" style="box-shadow:none;">
                                <i class="bi bi-three-dots-vertical" style="font-size:1.05rem;"></i>
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
                    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:4px;gap:6px;">
                        <div style="min-width:0;">
                            <span style="font-size:9.5px;color:var(--text-muted);text-transform:uppercase;font-weight:700;display:block;">Terkumpul</span>
                            <span style="font-size:1.1rem;font-weight:800;color:var(--success);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;">
                                ${formatRupiah(goal.collected_amount)}
                            </span>
                        </div>
                        <div style="text-align:right;min-width:0;">
                            <span style="font-size:9.5px;color:var(--text-muted);text-transform:uppercase;font-weight:700;display:block;">Target</span>
                            <span style="font-size:0.9rem;font-weight:800;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;">
                                ${formatRupiah(goal.target_amount)}
                            </span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="goal-progress-bar">
                        <div class="goal-progress-fill" style="width:${progress}%;background:${isAchieved ? '#10b981' : color};"></div>
                    </div>

                    <div style="display:flex;justify-content:space-between;font-size:9.5px;color:var(--text-muted);margin-bottom:12px;gap:6px;">
                        <span style="font-weight:700;color:${isAchieved ? '#10b981' : 'var(--text-primary)'};">${progress}% Tercapai</span>
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Sisa: <strong style="color:var(--primary);">${formatRupiah(goal.remaining_amount)}</strong></span>
                    </div>

                    <!-- Allocations Grouping Preview -->
                    <div style="display:flex;flex-direction:column;gap:5px;margin-bottom:14px;">
                        ${allocPreviewHtml}
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:6px;padding-top:10px;border-top:1px solid var(--border-color);">
                    <button class="btn-primary-custom" onclick="openGoalDetail(${goal.id}, 'allocations')" style="padding:7px 8px;border-radius:var(--radius-sm);font-size:10.5px;display:flex;align-items:center;justify-content:center;gap:4px;">
                        <i class="bi bi-folder2-open"></i> Alokasi
                    </button>
                    <button class="btn-primary-custom" onclick="openGoalDetail(${goal.id}, 'history')" style="background:var(--surface-2);color:var(--text-primary);border:1px solid var(--border-color);padding:7px 8px;border-radius:var(--radius-sm);font-size:10.5px;display:flex;align-items:center;justify-content:center;gap:4px;">
                        <i class="bi bi-graph-up-arrow"></i> Riwayat
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
async function openGoalDetail(goalId, initialTab = 'allocations') {
    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/goals/${goalId}`);
        const json = await res.json();
        if (!json.success || !json.data) {
            showSavingsToast('Gagal Memuat Detail', json.error || 'Goal tidak ditemukan', 'error');
            return;
        }

        activeGoal = json.data;
        renderGoalDetailModal();
        
        // Switch to initial tab
        if (initialTab === 'history') {
            switchDetailTab('history', document.getElementById('tabHistoryBtn'));
        } else if (initialTab === 'logs') {
            switchDetailTab('logs', document.getElementById('tabLogsBtn'));
        } else {
            switchDetailTab('allocations', document.getElementById('tabAllocationsBtn'));
        }

        const modal = new bootstrap.Modal(document.getElementById('modalGoalDetail'));
        modal.show();
    } catch (e) {
        console.error(e);
        showSavingsToast('Kesalahan Jaringan', 'Terjadi kesalahan saat memuat detail goal.', 'error');
    }
}

function renderGoalDetailModal() {
    if (!activeGoal) return;

    const color = activeGoal.color || '#6366f1';
    const icon = activeGoal.icon || 'bi-piggy-bank-fill';
    const progress = Math.min(100, activeGoal.progress_percent || 0);
    const isAchieved = activeGoal.status === 'achieved' || progress >= 100;
    const allocations = activeGoal.allocations || [];

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
    document.getElementById('detailProgressText').textContent = `${progress}% Tercapai`;
    document.getElementById('detailAllocCountText').textContent = `${allocations.length} Pos Alokasi`;

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
    document.querySelectorAll('#modalGoalDetail .detail-tab-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    document.getElementById('detailTabAllocations').style.display = tab === 'allocations' ? 'block' : 'none';
    document.getElementById('detailTabLogs').style.display = tab === 'logs' ? 'block' : 'none';
    document.getElementById('detailTabHistory').style.display = tab === 'history' ? 'block' : 'none';

    if (tab === 'history' && activeGoal) {
        loadGoalHistory(activeGoal.id);
    }
}

// ----------------------------------------------------
// DAILY PROGRESS HISTORY & SNAPSHOTS ENGINE
// ----------------------------------------------------
async function loadGoalHistory(goalId) {
    const analyticsBox = document.getElementById('historyAnalyticsBox');
    const timelineBox = document.getElementById('historyTimelineContainer');
    
    analyticsBox.innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:11px;padding:8px;"><i class="bi bi-arrow-repeat spin me-1"></i> Memuat analytics progress...</div>';
    timelineBox.innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:11px;padding:16px;"><i class="bi bi-arrow-repeat spin me-1"></i> Memuat log snapshot harian...</div>';

    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/goals/${goalId}/history`);
        const json = await res.json();
        if (!json.success || !json.data) {
            analyticsBox.innerHTML = '<div style="text-align:center;color:var(--danger);font-size:11px;">Gagal memuat riwayat progress.</div>';
            timelineBox.innerHTML = '';
            showSavingsToast('Gagal Riwayat', json.error || 'Gagal memuat riwayat snapshot.', 'error');
            return;
        }

        renderGoalHistory(json.data);
    } catch (e) {
        console.error(e);
        analyticsBox.innerHTML = '<div style="text-align:center;color:var(--danger);font-size:11px;">Koneksi terputus saat memuat history.</div>';
        timelineBox.innerHTML = '';
        showSavingsToast('Koneksi Terputus', 'Gagal menghubungi server.', 'error');
    }
}

function renderGoalHistory(data) {
    const analyticsBox = document.getElementById('historyAnalyticsBox');
    const timelineBox = document.getElementById('historyTimelineContainer');
    const analytics = data.analytics || {};
    const snapshots = data.snapshots || [];

    // 1. Render Analytics Overview
    const net7 = analytics.net_change_7d || 0;
    const pct7 = analytics.pct_change_7d || 0;
    const net30 = analytics.net_change_30d || 0;
    const pct30 = analytics.pct_change_30d || 0;
    const trend = analytics.trend || 'neutral';
    const totalDays = analytics.total_days_tracked || 0;

    let trendLabel = '⏸️ Stabil';
    let trendColor = 'var(--text-muted)';
    let trendBg = 'var(--surface-3)';
    if (trend === 'up') {
        trendLabel = '📈 Kenaikan Konsisten';
        trendColor = '#10b981';
        trendBg = 'rgba(16,185,129,0.12)';
    } else if (trend === 'down') {
        trendLabel = '📉 Mengalami Penurunan';
        trendColor = '#ef4444';
        trendBg = 'rgba(239,68,68,0.12)';
    }

    analyticsBox.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
            <div style="font-size: 11px; font-weight: 700; color: var(--text-primary);">
                Tren Pertumbuhan Tabungan (${totalDays} Hari Terdata):
            </div>
            <span style="font-size: 10px; font-weight: 800; color: ${trendColor}; background: ${trendBg}; padding: 2px 8px; border-radius: var(--radius-sm); border: 1px solid ${trendColor}33;">
                ${trendLabel}
            </span>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            <div style="background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 8px 10px;">
                <div style="font-size: 9px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Perubahan 7 Hari</div>
                <div style="font-size: 12.5px; font-weight: 800; color: ${net7 >= 0 ? 'var(--success)' : 'var(--danger)'}; margin-top: 1px;">
                    ${net7 >= 0 ? '+' : ''}${formatRupiah(net7)}
                </div>
                <div style="font-size: 9px; color: ${net7 >= 0 ? 'var(--success)' : 'var(--danger)'}; font-weight: 700;">
                    ${pct7 >= 0 ? '+' : ''}${pct7}%
                </div>
            </div>
            <div style="background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 8px 10px;">
                <div style="font-size: 9px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Perubahan 30 Hari</div>
                <div style="font-size: 12.5px; font-weight: 800; color: ${net30 >= 0 ? 'var(--success)' : 'var(--danger)'}; margin-top: 1px;">
                    ${net30 >= 0 ? '+' : ''}${formatRupiah(net30)}
                </div>
                <div style="font-size: 9px; color: ${net30 >= 0 ? 'var(--success)' : 'var(--danger)'}; font-weight: 700;">
                    ${pct30 >= 0 ? '+' : ''}${pct30}%
                </div>
            </div>
        </div>
    `;

    // 2. Render Timeline Snapshots
    if (snapshots.length === 0) {
        timelineBox.innerHTML = `
            <div style="text-align:center;padding:24px 10px;background:var(--surface-2);border-radius:var(--radius-md);border:1px dashed var(--border-color);">
                <i class="bi bi-clock-history" style="font-size:1.5rem;color:var(--text-muted);display:block;margin-bottom:6px;"></i>
                <div style="font-size:12px;font-weight:700;color:var(--text-primary);">Belum ada snapshot harian</div>
                <div style="font-size:10px;color:var(--text-muted);margin:2px 0 10px 0;">Sistem akan otomatis mencapture setiap pukul 23:00 WIB, atau Anda dapat mengambil snapshot manual sekarang.</div>
                <button class="btn-primary-custom" onclick="captureGoalSnapshotManual()" style="padding:6px 14px;border-radius:var(--radius-sm);font-size:11px;">
                    <i class="bi bi-camera-fill me-1"></i> Capture Snapshot Sekarang
                </button>
            </div>
        `;
        return;
    }

    timelineBox.innerHTML = snapshots.map((snap, idx) => {
        const changeAmt = snap.change_amount || 0;
        const changePct = snap.change_percent || 0;
        const isUp = changeAmt > 0;
        const isDown = changeAmt < 0;
        const isNeutral = changeAmt === 0;

        let badgeBg = 'var(--surface-3)';
        let badgeColor = 'var(--text-muted)';
        let badgeBorder = 'var(--border-color)';
        let badgeIcon = 'bi-dash';
        let badgeText = 'Stabil (0%)';

        if (isUp) {
            badgeBg = 'rgba(16,185,129,0.12)';
            badgeColor = '#10b981';
            badgeBorder = 'rgba(16,185,129,0.3)';
            badgeIcon = 'bi-arrow-up-right';
            badgeText = `+${formatRupiah(changeAmt)} (+${changePct}%)`;
        } else if (isDown) {
            badgeBg = 'rgba(239,68,68,0.12)';
            badgeColor = '#ef4444';
            badgeBorder = 'rgba(239,68,68,0.3)';
            badgeIcon = 'bi-arrow-down-right';
            badgeText = `-${formatRupiah(Math.abs(changeAmt))} (${changePct}%)`;
        }

        // Format Date Indonesian
        const dObj = new Date(snap.snapshot_date);
        const dateFormatted = dObj.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' });

        const allocs = snap.allocations || [];
        const allocsHtml = allocs.length > 0 ? `
            <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-top: 6px; padding-top: 6px; border-top: 1px dashed var(--border-color);">
                ${allocs.map(a => `
                    <span style="font-size: 8.5px; background: var(--surface-3); border: 1px solid var(--border-color); border-radius: 4px; padding: 1px 5px; color: var(--text-muted);">
                        ${escapeHtml(a.name)}: <strong>${formatRupiah(a.amount)}</strong>
                    </span>
                `).join('')}
            </div>
        ` : '';

        return `
            <div style="background: var(--surface-2); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 10px 12px; margin-bottom: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 11px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 5px;">
                            <i class="bi bi-calendar-check" style="color: #818cf8;"></i>
                            <span>${dateFormatted}</span>
                            ${idx === 0 ? '<span class="badge badge-primary" style="font-size: 8px; padding: 1px 4px;">Terbaru</span>' : ''}
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 6px; margin-top: 3px;">
                            <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);">
                                ${formatRupiah(snap.total_collected)}
                            </div>
                            <span style="font-size: 9.5px; font-weight: 700; color: var(--primary);">
                                (${snap.progress_percent}% dari target)
                            </span>
                        </div>
                    </div>

                    <!-- Growth Badge -->
                    <div style="text-align: right; flex-shrink: 0;">
                        <div style="display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 800; color: ${badgeColor}; background: ${badgeBg}; border: 1px solid ${badgeBorder}; padding: 3px 8px; border-radius: var(--radius-sm);">
                            <i class="bi ${badgeIcon}"></i> ${badgeText}
                        </div>
                    </div>
                </div>

                ${allocsHtml}
            </div>
        `;
    }).join('');
}

async function captureGoalSnapshotManual() {
    if (!activeGoal) return;
    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/goals/${activeGoal.id}/snapshot`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ csrf_token: getCsrfToken() })
        });
        const json = await res.json();
        if (json.success) {
            showSavingsToast('Snapshot Tersimpan!', `Snapshot progress hari ini untuk "${activeGoal.name}" berhasil dicapture.`, 'camera');
            loadGoalHistory(activeGoal.id);
        } else {
            showSavingsToast('Gagal Snapshot', json.error || 'Gagal mengambil snapshot', 'error');
        }
    } catch (e) {
        console.error(e);
        showSavingsToast('Koneksi Terputus', 'Terjadi kesalahan koneksi server.', 'error');
    }
}

function renderAllocationsList() {
    const container = document.getElementById('allocationsListContainer');
    const allocations = activeGoal.allocations || [];

    if (allocations.length === 0) {
        container.innerHTML = `
            <div style="text-align:center;padding:24px 10px;background:var(--surface-2);border-radius:var(--radius-md);border:1px dashed var(--border-color);">
                <i class="bi bi-wallet2" style="font-size:1.5rem;color:var(--text-muted);display:block;margin-bottom:6px;"></i>
                <div style="font-size:12.5px;font-weight:700;color:var(--text-primary);">Belum ada pos penempatan uang</div>
                <div style="font-size:10.5px;color:var(--text-muted);margin:2px 0 10px 0;">Contoh: Uang di Toko (3jt), di Bibit (4jt), di SeaBank (350rb)</div>
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
        let iconBg = 'rgba(99,102,241,0.15)';
        let iconColor = '#818cf8';

        const atLower = (alloc.account_type || '').toLowerCase();
        if (atLower.includes('toko') || atLower.includes('kas')) {
            iconClass = 'bi-shop';
            iconBg = 'rgba(245,158,11,0.15)';
            iconColor = '#f59e0b';
        } else if (atLower.includes('investasi')) {
            iconClass = 'bi-graph-up-arrow';
            iconBg = 'rgba(59,130,246,0.15)';
            iconColor = '#3b82f6';
        } else if (atLower.includes('bank')) {
            iconClass = 'bi-bank';
            iconBg = 'rgba(16,185,129,0.15)';
            iconColor = '#10b981';
        } else if (atLower.includes('piutang')) {
            iconClass = 'bi-person-lines-fill';
            iconBg = 'rgba(236,72,153,0.15)';
            iconColor = '#ec4899';
        }

        return `
            <div class="alloc-card-item">
                <!-- Top Row: Icon + Name & Type on Left; Action Buttons on Right -->
                <div class="alloc-card-top-row">
                    <div class="alloc-card-left-info">
                        <div class="alloc-card-icon" style="background:${iconBg};color:${iconColor};">
                            <i class="bi ${iconClass}"></i>
                        </div>
                        <div class="alloc-card-name-group">
                            <div class="alloc-card-name">${escapeHtml(alloc.name)}</div>
                            <div class="alloc-card-meta">
                                <span class="badge-custom badge-primary" style="font-size:9px;padding:2px 6px;">${escapeHtml(alloc.account_type)}</span>
                                ${alloc.institution ? `<span style="font-size:9.5px;color:var(--text-muted);font-weight:600;">(${escapeHtml(alloc.institution)})</span>` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="alloc-card-actions">
                        <button class="btn-alloc-mutate" onclick="openMutationModal(${alloc.id}, '${escapeHtml(alloc.name)}', ${alloc.amount})" title="Tambah / Tarik Saldo">
                            <i class="bi bi-arrow-left-right"></i> Mutasi
                        </button>
                        <button class="btn-alloc-icon-action" onclick="openEditAllocationModal(${alloc.id})" title="Edit Pos">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-alloc-icon-action btn-delete" onclick="confirmDeleteAllocation(${alloc.id}, '${escapeHtml(alloc.name)}')" title="Hapus Pos">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- Bottom Row: Clean Amount & Share Info (Never Collides) -->
                <div class="alloc-card-bottom-row">
                    <div class="alloc-amount-line">
                        <div class="alloc-amount-val">${formatRupiah(alloc.amount)}</div>
                        <span class="alloc-share-tag">${pct}% dari goal</span>
                    </div>
                    <div class="alloc-mini-bar">
                        <div class="alloc-mini-fill" style="width:${Math.min(100, pct)}%;background:${iconColor};"></div>
                    </div>
                    ${alloc.notes ? `<div class="alloc-notes-text"><i class="bi bi-info-circle me-1"></i>${escapeHtml(alloc.notes)}</div>` : ''}
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
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:11.5px;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <i class="bi ${icon}" style="color:${color};"></i> ${isDeposit ? 'Setoran' : 'Penarikan'} (${escapeHtml(log.allocation_name || 'Pos')})
                        </div>
                        <div style="font-size:9.5px;color:var(--text-muted);margin-top:1px;">
                            ${log.log_date} &middot; Saldo akhir: ${formatRupiah(log.balance_after)}
                        </div>
                        ${log.notes ? `<div style="font-size:9.5px;color:var(--text-primary);margin-top:2px;font-style:italic;">"${escapeHtml(log.notes)}"</div>` : ''}
                    </div>
                    <div style="font-size:11.5px;font-weight:800;color:${color};white-space:nowrap;flex-shrink:0;">
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
        showSavingsToast('Validasi Nominal', 'Nominal target harus lebih besar dari 0', 'warning');
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
            showSavingsToast('Goal Disimpan!', isEdit ? 'Target tabungan berhasil diperbarui' : 'Target tabungan baru berhasil ditambahkan', 'success');
            await loadGoals();
            await loadSummary();

            if (isEdit && activeGoal && activeGoal.id == goalId) {
                await openGoalDetail(goalId);
            }
        } else {
            showSavingsToast('Gagal Menyimpan Goal', json.error || 'Gagal menyimpan target.', 'error');
        }
    } catch (err) {
        console.error(err);
        showSavingsToast('Koneksi Terputus', 'Terjadi kesalahan jaringan.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = isEdit ? 'Perbarui Goal' : 'Simpan Goal';
    }
}

async function confirmDeleteGoal(goalId, name) {
    showSavingsConfirm({
        title: 'Hapus Target Tabungan?',
        text: `Yakin ingin menghapus target "${name}"? Seluruh pos alokasi uang dan riwayat mutasi target ini akan ikut terhapus secara permanen.`,
        icon: 'bi-trash-fill',
        confirmText: 'Ya, Hapus Target',
        onConfirm: async () => {
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
                    showSavingsToast('Target Dihapus', `Target tabungan "${name}" berhasil dihapus.`, 'success');
                    await loadGoals();
                    await loadSummary();
                } else {
                    showSavingsToast('Gagal Menghapus', json.error || 'Gagal menghapus goal', 'error');
                }
            } catch (e) {
                console.error(e);
                showSavingsToast('Koneksi Terputus', 'Terjadi kesalahan jaringan.', 'error');
            }
        }
    });
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
            showSavingsToast('Pos Disimpan!', allocId ? 'Pos penempatan uang berhasil diperbarui' : 'Pos penempatan uang baru berhasil ditambahkan', 'success');
            await loadGoals();
            await loadSummary();

            if (json.data) {
                activeGoal = json.data;
                renderGoalDetailModal();
            }
        } else {
            showSavingsToast('Gagal Menyimpan Pos', json.error || 'Gagal menyimpan pos alokasi.', 'error');
        }
    } catch (err) {
        console.error(err);
        showSavingsToast('Koneksi Terputus', 'Terjadi kesalahan jaringan.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = allocId ? 'Perbarui Pos' : 'Simpan Pos';
    }
}

async function confirmDeleteAllocation(allocId, name) {
    showSavingsConfirm({
        title: 'Hapus Pos Alokasi?',
        text: `Hapus pos penempatan "${name}"? Saldo di pos ini tidak lagi terhitung dalam target tabungan.`,
        icon: 'bi-trash-fill',
        confirmText: 'Ya, Hapus Pos',
        onConfirm: async () => {
            try {
                const res = await fetch(`<?= BASE_URL ?>api/savings/allocations/${allocId}/delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                    body: JSON.stringify({ csrf_token: getCsrfToken() })
                });
                const json = await res.json();
                if (json.success) {
                    showSavingsToast('Pos Dihapus', `Pos penempatan "${name}" berhasil dihapus.`, 'success');
                    await loadGoals();
                    await loadSummary();
                    if (json.data) {
                        activeGoal = json.data;
                        renderGoalDetailModal();
                    }
                } else {
                    showSavingsToast('Gagal Menghapus Pos', json.error || 'Gagal menghapus pos', 'error');
                }
            } catch (e) {
                console.error(e);
                showSavingsToast('Koneksi Terputus', 'Terjadi kesalahan jaringan.', 'error');
            }
        }
    });
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
        showSavingsToast('Validasi Mutasi', 'Nominal mutasi harus lebih besar dari 0', 'warning');
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
            showSavingsToast('Mutasi Berhasil Dicatat!', type === 'deposit' ? 'Setoran dana berhasil ditambahkan' : 'Penarikan dana berhasil dicatat', 'success');
            await loadGoals();
            await loadSummary();

            if (json.data) {
                activeGoal = json.data;
                renderGoalDetailModal();
            }
        } else {
            showSavingsToast('Gagal Mencatat Mutasi', json.error || 'Gagal mencatat mutasi.', 'error');
        }
    } catch (err) {
        console.error(err);
        showSavingsToast('Koneksi Terputus', 'Terjadi kesalahan jaringan.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Mutasi';
    }
}

// ----------------------------------------------------
// GLOBAL SNAPSHOTS & HISTORY MODAL
// ----------------------------------------------------
let allGlobalSnapshots = [];

async function captureAllSnapshotsManual() {
    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/snapshots/capture-all`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ csrf_token: getCsrfToken() })
        });
        const json = await res.json();
        if (json.success) {
            const count = Array.isArray(json.data) ? json.data.length : 0;
            showSavingsToast(
                'Snapshot Berhasil Dicapture!',
                `History progress hari ini untuk ${count} target tabungan berhasil dicapture & dibackup ke database.`,
                'camera',
                4500
            );

            // Reload current open modal if active
            if (activeGoal) {
                loadGoalHistory(activeGoal.id);
            }
            // Reload global history modal if open
            const globalModal = document.getElementById('modalGlobalHistory');
            if (globalModal && globalModal.classList.contains('show')) {
                loadGlobalSnapshots();
            }
        } else {
            showSavingsToast('Gagal Menyimpan Snapshot', json.error || 'Gagal menyimpan snapshot', 'error');
        }
    } catch (e) {
        console.error(e);
        showSavingsToast('Koneksi Terputus', 'Terjadi kesalahan jaringan.', 'error');
    }
}

async function openGlobalHistoryModal() {
    // Populate Goal Filter dropdown
    const filterSelect = document.getElementById('globalHistoryGoalFilter');
    if (filterSelect) {
        filterSelect.innerHTML = '<option value="all">Semua Target Tabungan</option>' + 
            allGoals.map(g => `<option value="${g.id}">${escapeHtml(g.name)}</option>`).join('');
    }

    loadGlobalSnapshots();
    const modal = new bootstrap.Modal(document.getElementById('modalGlobalHistory'));
    modal.show();
}

async function loadGlobalSnapshots() {
    const container = document.getElementById('globalHistoryListContainer');
    const badge = document.getElementById('globalSnapshotCountBadge');
    container.innerHTML = '<div style="text-align:center;color:var(--text-muted);font-size:11px;padding:30px;"><i class="bi bi-arrow-repeat spin me-1"></i> Memuat riwayat seluruh target...</div>';

    try {
        const res = await fetch(`<?= BASE_URL ?>api/savings/snapshots/all`);
        const json = await res.json();
        if (json.success && Array.isArray(json.data)) {
            allGlobalSnapshots = json.data;
            if (badge) badge.textContent = `${allGlobalSnapshots.length} Record`;
            renderGlobalSnapshots(allGlobalSnapshots);
        } else {
            container.innerHTML = '<div style="text-align:center;color:var(--danger);font-size:11px;padding:20px;">Gagal memuat riwayat snapshot.</div>';
        }
    } catch (e) {
        console.error(e);
        container.innerHTML = '<div style="text-align:center;color:var(--danger);font-size:11px;padding:20px;">Koneksi terputus saat memuat riwayat.</div>';
    }
}

function filterGlobalSnapshots() {
    const filterVal = document.getElementById('globalHistoryGoalFilter')?.value || 'all';
    if (filterVal === 'all') {
        renderGlobalSnapshots(allGlobalSnapshots);
    } else {
        const filtered = allGlobalSnapshots.filter(s => String(s.goal_id) === String(filterVal));
        renderGlobalSnapshots(filtered);
    }
}

function renderGlobalSnapshots(snapshots) {
    const container = document.getElementById('globalHistoryListContainer');
    if (!container) return;

    if (snapshots.length === 0) {
        container.innerHTML = `
            <div style="text-align:center;padding:30px 10px;background:var(--surface-2);border-radius:var(--radius-md);border:1px dashed var(--border-color);">
                <i class="bi bi-clock-history" style="font-size:1.6rem;color:var(--text-muted);display:block;margin-bottom:6px;"></i>
                <div style="font-size:12.5px;font-weight:700;color:var(--text-primary);">Belum Ada Riwayat Snapshot</div>
                <div style="font-size:10.5px;color:var(--text-muted);margin:2px 0 12px 0;">Klik tombol di bawah untuk mencapture dan membackup saldo tabungan hari ini.</div>
                <button class="btn-primary-custom" onclick="captureAllSnapshotsManual()" style="padding:6px 14px;border-radius:var(--radius-sm);font-size:11px;">
                    <i class="bi bi-camera-fill me-1"></i> Simpan History Hari Ini
                </button>
            </div>
        `;
        return;
    }

    container.innerHTML = snapshots.map(snap => {
        const changeAmt = snap.change_amount || 0;
        const changePct = snap.change_percent || 0;
        const isUp = changeAmt > 0;
        const isDown = changeAmt < 0;

        let badgeBg = 'var(--surface-3)';
        let badgeColor = 'var(--text-muted)';
        let badgeBorder = 'var(--border-color)';
        let badgeIcon = 'bi-dash';
        let badgeText = 'Stabil (0%)';

        if (isUp) {
            badgeBg = 'rgba(16,185,129,0.12)';
            badgeColor = '#10b981';
            badgeBorder = 'rgba(16,185,129,0.3)';
            badgeIcon = 'bi-arrow-up-right';
            badgeText = `+${formatRupiah(changeAmt)} (+${changePct}%)`;
        } else if (isDown) {
            badgeBg = 'rgba(239,68,68,0.12)';
            badgeColor = '#ef4444';
            badgeBorder = 'rgba(239,68,68,0.3)';
            badgeIcon = 'bi-arrow-down-right';
            badgeText = `-${formatRupiah(Math.abs(changeAmt))} (${changePct}%)`;
        }

        const dObj = new Date(snap.snapshot_date);
        const dateFormatted = dObj.toLocaleDateString('id-ID', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });

        const allocs = snap.allocations || [];
        const allocsHtml = allocs.length > 0 ? `
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:6px;padding-top:6px;border-top:1px dashed var(--border-color);">
                ${allocs.map(a => `
                    <span style="font-size:8.5px;background:var(--surface-3);border:1px solid var(--border-color);border-radius:4px;padding:1px 5px;color:var(--text-muted);">
                        ${escapeHtml(a.name)}: <strong>${formatRupiah(a.amount)}</strong>
                    </span>
                `).join('')}
            </div>
        ` : '';

        return `
            <div style="background:var(--surface-2);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:10px 14px;margin-bottom:8px;transition:border-color 0.15s ease;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                    <div style="min-width:0;flex:1;">
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            <span class="badge-custom" style="background:${snap.goal_color || '#6366f1'}22;color:${snap.goal_color || '#6366f1'};font-size:9.5px;font-weight:700;">
                                <i class="bi ${snap.goal_icon || 'bi-piggy-bank-fill'} me-1"></i>${escapeHtml(snap.goal_name)}
                            </span>
                            <span style="font-size:10px;color:var(--text-muted);">
                                <i class="bi bi-calendar-event me-1"></i>${dateFormatted}
                            </span>
                        </div>
                        <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px;">
                            <span style="font-size:13px;font-weight:800;color:var(--text-primary);">
                                ${formatRupiah(snap.total_collected)}
                            </span>
                            <span style="font-size:9.5px;font-weight:700;color:var(--primary);">
                                (${snap.progress_percent}% dari target)
                            </span>
                        </div>
                    </div>

                    <div style="text-align:right;flex-shrink:0;">
                        <div style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:800;color:${badgeColor};background:${badgeBg};border:1px solid ${badgeBorder};padding:3px 8px;border-radius:var(--radius-sm);">
                            <i class="bi ${badgeIcon}"></i> ${badgeText}
                        </div>
                    </div>
                </div>

                ${allocsHtml}
            </div>
        `;
    }).join('');
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
