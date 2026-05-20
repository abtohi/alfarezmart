<!-- Database Setup Result View -->
<div class="page-section">
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 72px; height: 72px; background: var(--success-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
            <i class="bi bi-database-check" style="font-size: 2rem; color: var(--success);"></i>
        </div>
        <h2 style="font-size: var(--font-size-xl);">Database Setup</h2>
    </div>
    <div style="background: var(--surface-1); border-radius: var(--radius-lg); padding: 16px; border: 1px solid var(--border-color);">
        <?php foreach ($messages as $msg): ?>
            <div style="padding: 6px 0; font-size: var(--font-size-sm); font-family: monospace; color: <?= strpos($msg, '✅') !== false ? 'var(--success)' : (strpos($msg, '❌') !== false ? 'var(--danger)' : 'var(--text-secondary)') ?>;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="<?= BASE_URL ?>" class="btn-primary-custom" style="display: block; text-align: center; margin-top: 20px; text-decoration: none; color: white;">
        <i class="bi bi-house-door"></i> Ke Dashboard
    </a>
</div>
