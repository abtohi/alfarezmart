<!-- Backup Data Harian View -->
<div class="page-section">

    <!-- Header -->
    <div style="margin-bottom:20px; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">
                <i class="bi bi-shield-lock-fill" style="color:#6366f1; margin-right:8px;"></i>Backup Data Harian
            </h2>
            <p style="font-size:var(--font-size-xs); color:var(--text-muted);">
                Snapshot data darurat offline &mdash; disimpan di IndexedDB browser (kapasitas besar, unlimited) atau unduh ke HP/PC
            </p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <button id="btn-manual-backup" onclick="doManualBackup()" class="btn-primary-custom" style="padding:8px 14px; font-size:var(--font-size-xs); display:flex; align-items:center; gap:6px;">
                <i class="bi bi-cloud-download-fill"></i> Backup Sekarang
            </button>
            <button id="btn-refresh-backup" onclick="loadBackupPage()" class="btn-outline-custom" style="padding:8px 14px; font-size:var(--font-size-xs); display:flex; align-items:center; gap:6px;">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Export & Import Card -->
    <div style="background:linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(16,185,129,0.03) 100%); border:1px solid rgba(16,185,129,0.25); border-radius:var(--radius-lg); padding:14px 16px; margin-bottom:18px;">
        <div style="font-weight:700; font-size:var(--font-size-xs); color:#10b981; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
            <i class="bi bi-phone-fill"></i> Simpan / Muat File ke HP atau PC
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button id="btn-download-today" onclick="doDownloadToday()" style="flex:1; min-width:140px; background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.35); color:#10b981; padding:10px 12px; border-radius:var(--radius-md); font-size:var(--font-size-xs); font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                <i class="bi bi-download"></i> Unduh Backup (.json)
            </button>
            <label for="input-import-file" style="flex:1; min-width:140px; background:rgba(99,102,241,0.1); border:1px solid rgba(99,102,241,0.3); color:#818cf8; padding:10px 12px; border-radius:var(--radius-md); font-size:var(--font-size-xs); font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; margin:0;">
                <i class="bi bi-upload"></i> Impor dari File (.json)
            </label>
            <input id="input-import-file" type="file" accept=".json,application/json" style="display:none" onchange="doImportFile(this)">
        </div>
        <div style="margin-top:8px; font-size:10px; color:var(--text-muted); line-height:1.6;">
            📥 <strong>Unduh</strong>: Simpan file backup hari ini (.json) langsung ke memori HP/PC — bisa dipindahkan ke perangkat lain.<br>
            📤 <strong>Impor</strong>: Muat file .json backup yang sebelumnya diunduh untuk restore data.
        </div>
    </div>

    <!-- Active Restore Banner -->
    <div id="restore-banner" style="display:none; background:linear-gradient(135deg, rgba(234,179,8,0.12) 0%, rgba(245,158,11,0.05) 100%); border:1.5px solid rgba(234,179,8,0.4); border-radius:var(--radius-lg); padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; gap:12px;">
        <div style="width:36px; height:36px; background:rgba(234,179,8,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="bi bi-exclamation-triangle-fill" style="color:#ca8a04; font-size:1rem;"></i>
        </div>
        <div style="flex:1; min-width:0;">
            <div style="font-weight:700; font-size:var(--font-size-sm); color:#ca8a04;" id="restore-banner-title">Mode Data Backup Aktif</div>
            <div style="font-size:var(--font-size-xs); color:var(--text-muted);" id="restore-banner-sub"></div>
        </div>
        <button onclick="clearRestore()" style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:var(--danger); padding:6px 12px; border-radius:8px; font-size:var(--font-size-xs); font-weight:700; cursor:pointer;">
            <i class="bi bi-x-circle"></i> Kembali ke Data Live
        </button>
    </div>

    <!-- Status Card Today -->
    <div id="status-card" style="background:var(--gradient-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:16px 18px; margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div id="status-icon-wrap" style="width:44px; height:44px; border-radius:12px; background:var(--surface-2); display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">
                <i class="bi bi-hourglass-split" style="color:var(--text-muted);"></i>
            </div>
            <div style="flex:1; min-width:0;">
                <div id="status-title" style="font-weight:700; font-size:var(--font-size-sm);">Memeriksa status backup...</div>
                <div id="status-sub" style="font-size:var(--font-size-xs); color:var(--text-muted); margin-top:2px;"></div>
            </div>
            <div id="status-badge"></div>
        </div>
    </div>

    <!-- Storage Stats -->
    <div id="storage-stats" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;"></div>

    <!-- History List -->
    <div class="section-title" style="margin-bottom:10px;">
        <i class="bi bi-clock-history" style="margin-right:6px; color:var(--primary);"></i>History Backup (14 Hari Terakhir)
    </div>

    <div id="backup-list-container">
        <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
            <i class="bi bi-hourglass-split" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
            <div style="font-size:var(--font-size-sm);">Memuat history backup...</div>
        </div>
    </div>

    <!-- Info Box -->
    <div style="margin-top:20px; background:linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(99,102,241,0.03) 100%); border:1px solid rgba(99,102,241,0.25); border-radius:var(--radius-lg); padding:14px 16px;">
        <div style="font-weight:700; font-size:var(--font-size-xs); color:#818cf8; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
            <i class="bi bi-info-circle-fill"></i> Cara Kerja Backup Darurat
        </div>
        <ul style="margin:0; padding-left:16px; font-size:var(--font-size-xs); color:var(--text-muted); line-height:1.9;">
            <li>Backup otomatis berjalan <strong>sekali sehari</strong> saat aplikasi dibuka</li>
            <li>Data disimpan di <strong>IndexedDB browser</strong> (kapasitas besar, tidak ada limit storage) — 100% offline</li>
            <li>Menyimpan data <strong>produk, supplier, dan kategori</strong> aktif</li>
            <li>Backup <strong>tidak akan terhapus</strong> oleh fitur "Bersihkan Cache &amp; Turbo" ⚡</li>
            <li>History dijaga selama <strong>2 minggu (14 hari)</strong>, lebih lama otomatis dihapus</li>
            <li>Klik <strong>"Unduh"</strong> untuk menyimpan file backup .json ke memori HP atau PC</li>
            <li>Klik <strong>"Restore"</strong> pada backup tertentu untuk menggunakannya saat koneksi database bermasalah</li>
        </ul>
    </div>

    <!-- Delete All -->
    <button onclick="doDeleteAllBackups()" class="btn-outline-custom" style="width:100%; padding:10px; text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; font-weight:600; margin-top:16px; color:var(--danger); border-color:var(--danger); font-size:var(--font-size-xs);">
        <i class="bi bi-trash"></i> Hapus Semua Backup
    </button>

    <a href="<?= BASE_URL ?>settings" class="btn-outline-custom" style="width:100%; padding:12px; text-align:center; display:block; font-weight:600; margin-top:10px;">
        <i class="bi bi-arrow-left" style="margin-right:6px;"></i>Kembali ke Pengaturan
    </a>
</div>

<style>
.backup-entry {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    margin-bottom: 10px;
    overflow: hidden;
    transition: box-shadow 200ms ease, border-color 200ms ease;
}
.backup-entry:hover {
    box-shadow: 0 2px 14px rgba(0,0,0,0.12);
    border-color: rgba(99,102,241,0.35);
}
.backup-entry.is-today {
    border-color: rgba(34,197,94,0.4);
    background: linear-gradient(135deg, rgba(34,197,94,0.06) 0%, var(--surface-1) 100%);
}
.backup-entry.is-active-restore {
    border-color: rgba(234,179,8,0.5);
    background: linear-gradient(135deg, rgba(234,179,8,0.08) 0%, var(--surface-1) 100%);
}
.backup-entry-inner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
}
.backup-date-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.backup-meta {
    flex: 1;
    min-width: 0;
}
.backup-date-label {
    font-weight: 700;
    font-size: var(--font-size-sm);
    color: var(--text-primary);
}
.backup-counts {
    font-size: 10px;
    color: var(--text-muted);
    margin-top: 3px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.backup-count-chip {
    background: var(--surface-2);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 1px 6px;
    font-weight: 600;
}
.backup-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}
</style>

<script>
var _backupList = [];
var _activeRestore = null;

async function doDownloadToday() {
    if (typeof window.DailyBackup === 'undefined') { showToast('Modul backup tidak tersedia', 'error'); return; }
    const btn = document.getElementById('btn-download-today');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Menyiapkan...'; }
    try {
        await DailyBackup.downloadBackup();
        showToast('✅ File backup berhasil diunduh ke HP/PC', 'success', 4000);
        loadBackupPage();
    } catch(e) {
        showToast('Gagal unduh: ' + (e.message || 'Error'), 'error');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-download"></i> Unduh Backup (.json)'; }
    }
}

async function doImportFile(input) {
    if (!input || !input.files || !input.files[0]) return;
    const file = input.files[0];
    if (typeof window.DailyBackup === 'undefined') { showToast('Modul backup tidak tersedia', 'error'); return; }
    const label = document.querySelector('label[for="input-import-file"]');
    if (label) { label.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Mengimpor...'; }
    try {
        const result = await DailyBackup.importFromFile(file);
        showToast(`✅ Impor berhasil! ${result.counts.products} produk dimuat ke mode offline.`, 'success', 5000);
        loadBackupPage();
    } catch(e) {
        showToast('Gagal impor: ' + (e.message || 'Format tidak valid'), 'error');
    } finally {
        input.value = '';
        if (label) { label.innerHTML = '<i class="bi bi-upload"></i> Impor dari File (.json)'; }
    }
}

async function doDownloadEntry(dateStr) {
    if (typeof window.DailyBackup === 'undefined') return;
    try {
        await DailyBackup.downloadBackup(dateStr);
        showToast('✅ File backup berhasil diunduh', 'success', 3000);
    } catch(e) {
        showToast('Gagal unduh: ' + (e.message || 'Error'), 'error');
    }
}

function loadBackupPage() {
    if (typeof window.DailyBackup === 'undefined') {
        document.getElementById('backup-list-container').innerHTML =
            '<div style="text-align:center;padding:40px;color:var(--danger);">' +
            '<i class="bi bi-exclamation-triangle" style="font-size:2rem;display:block;margin-bottom:8px;"></i>' +
            '<div>Modul backup tidak tersedia. Pastikan daily-backup.js sudah dimuat.</div></div>';
        return;
    }

    _activeRestore = DailyBackup.getActiveRestore();
    renderRestoreBanner();

    _backupList = DailyBackup.getBackupList();
    renderStatusCard();
    renderStorageStats();
    renderBackupList();
}

function renderRestoreBanner() {
    const banner = document.getElementById('restore-banner');
    if (!banner) return;
    if (_activeRestore) {
        banner.style.display = 'flex';
        const title = document.getElementById('restore-banner-title');
        const sub = document.getElementById('restore-banner-sub');
        if (title) title.textContent = '⚠ Mode Data Backup Aktif: ' + (_activeRestore.label || _activeRestore.date);
        if (sub) {
            const d = new Date(_activeRestore.restoredAt);
            const pad = n => n < 10 ? '0' + n : String(n);
            const timeStr = pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear() +
                ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
            sub.textContent = 'Restore dilakukan: ' + timeStr + ' · ' +
                (_activeRestore.counts ? `${_activeRestore.counts.products || 0} produk, ${_activeRestore.counts.suppliers || 0} supplier` : '');
        }
    } else {
        banner.style.display = 'none';
    }
}

function renderStatusCard() {
    const today = DailyBackup._todayStr ? DailyBackup._todayStr() : new Date().toISOString().split('T')[0];
    const todayBackup = _backupList.find(e => e.date === today);

    const iconWrap = document.getElementById('status-icon-wrap');
    const titleEl = document.getElementById('status-title');
    const subEl = document.getElementById('status-sub');
    const badgeEl = document.getElementById('status-badge');

    if (todayBackup) {
        if (iconWrap) iconWrap.innerHTML = '<i class="bi bi-shield-check-fill" style="color:var(--success);"></i>';
        if (iconWrap) iconWrap.style.background = 'rgba(34,197,94,0.12)';
        if (titleEl) titleEl.textContent = '✅ Backup hari ini sudah tersimpan';
        if (subEl) subEl.textContent = todayBackup.label + ' · ' + todayBackup.size_label +
            ' · ' + (todayBackup.counts ? todayBackup.counts.products + ' produk' : '');
        if (badgeEl) badgeEl.innerHTML = '<span class="badge-custom badge-success" style="font-size:10px;">Tersimpan</span>';
    } else {
        if (iconWrap) iconWrap.innerHTML = '<i class="bi bi-cloud-slash-fill" style="color:var(--warning);"></i>';
        if (iconWrap) iconWrap.style.background = 'rgba(234,179,8,0.12)';
        if (titleEl) titleEl.textContent = 'Belum ada backup hari ini';
        if (subEl) subEl.textContent = 'Klik "Backup Sekarang" untuk menyimpan snapshot data saat ini';
        if (badgeEl) badgeEl.innerHTML = '<span class="badge-custom badge-warning" style="font-size:10px;">Belum</span>';
    }
}

function renderStorageStats() {
    const stats = DailyBackup.getStorageStats ? DailyBackup.getStorageStats() : null;
    const container = document.getElementById('storage-stats');
    if (!container || !stats) return;

    container.innerHTML =
        '<div style="display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:700;background:var(--surface-1);border:1px solid var(--border-color);color:var(--text-primary);">' +
        '<i class="bi bi-archive" style="color:var(--primary);"></i> ' + stats.count + ' Backup</div>' +

        '<div style="display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:700;background:var(--surface-1);border:1px solid var(--border-color);color:var(--text-muted);">' +
        '<i class="bi bi-hdd"></i> ' + stats.total_size_label + '</div>' +

        (stats.today_backed
            ? '<div style="display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:var(--success);">' +
              '<i class="bi bi-check-circle-fill"></i> Hari ini ter-backup</div>'
            : '<div style="display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(234,179,8,0.1);border:1px solid rgba(234,179,8,0.3);color:#ca8a04;">' +
              '<i class="bi bi-exclamation-circle-fill"></i> Belum backup hari ini</div>');
}

function renderBackupList() {
    const container = document.getElementById('backup-list-container');
    if (!container) return;

    if (!_backupList || _backupList.length === 0) {
        container.innerHTML =
            '<div style="text-align:center;padding:48px 20px;color:var(--text-muted);">' +
            '<div style="width:64px;height:64px;background:var(--surface-1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.8rem;">' +
            '<i class="bi bi-archive" style="color:var(--text-muted);"></i></div>' +
            '<div style="font-weight:700;font-size:var(--font-size-sm);margin-bottom:4px;">Belum Ada Backup</div>' +
            '<div style="font-size:var(--font-size-xs);">Backup pertama akan dibuat otomatis saat kamu membuka app esok hari, atau klik "Backup Sekarang".</div>' +
            '</div>';
        return;
    }

    const today = DailyBackup._todayStr ? DailyBackup._todayStr() : new Date().toISOString().split('T')[0];
    const activeDate = _activeRestore ? _activeRestore.date : null;

    let html = '';
    _backupList.forEach(function(entry, idx) {
        const isToday = entry.date === today;
        const isActive = entry.date === activeDate;

        let entryClass = 'backup-entry';
        if (isToday) entryClass += ' is-today';
        if (isActive) entryClass += ' is-active-restore';

        const iconBg = isToday ? 'rgba(34,197,94,0.12)' : 'rgba(99,102,241,0.1)';
        const iconColor = isToday ? 'var(--success)' : '#818cf8';
        const iconClass = isToday ? 'bi-shield-check-fill' : 'bi-archive-fill';

        const counts = entry.counts || {};
        const countsHtml =
            (counts.products ? '<span class="backup-count-chip"><i class="bi bi-box-seam"></i> ' + counts.products + ' Produk</span>' : '') +
            (counts.suppliers ? '<span class="backup-count-chip"><i class="bi bi-building"></i> ' + counts.suppliers + ' Supplier</span>' : '') +
            (counts.categories ? '<span class="backup-count-chip"><i class="bi bi-folder"></i> ' + counts.categories + ' Kategori</span>' : '');

        const todayBadge = isToday ? '<span class="badge-custom badge-success" style="font-size:9px;margin-left:6px;">Hari Ini</span>' : '';
        const activeBadge = isActive ? '<span class="badge-custom badge-warning" style="font-size:9px;margin-left:6px;">⚡ Aktif</span>' : '';

        const restoreBtn = isActive
            ? '<button onclick="clearRestore()" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:var(--danger);padding:6px 10px;border-radius:8px;font-size:10px;font-weight:700;cursor:pointer;white-space:nowrap;">' +
              '<i class="bi bi-x-circle"></i> Batalkan</button>'
            : '<button onclick="doRestore(\'' + entry.date + '\')" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.3);color:#818cf8;padding:6px 10px;border-radius:8px;font-size:10px;font-weight:700;cursor:pointer;white-space:nowrap;">' +
              '<i class="bi bi-cloud-upload"></i> Restore</button>';

        const downloadBtn = '<button onclick="doDownloadEntry(\'' + entry.date + '\')" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;padding:6px 10px;border-radius:8px;font-size:10px;font-weight:700;cursor:pointer;white-space:nowrap;">' +
            '<i class="bi bi-download"></i> Unduh</button>';

        html +=
            '<div class="' + entryClass + '">' +
            '<div class="backup-entry-inner">' +
            '<div class="backup-date-icon" style="background:' + iconBg + ';">' +
            '<i class="bi ' + iconClass + '" style="color:' + iconColor + ';"></i>' +
            '</div>' +
            '<div class="backup-meta">' +
            '<div class="backup-date-label">' + (entry.label || entry.date) + todayBadge + activeBadge + '</div>' +
            '<div class="backup-counts">' + countsHtml +
            '<span class="backup-count-chip"><i class="bi bi-hdd"></i> ' + (entry.size_label || '-') + '</span>' +
            '</div>' +
            '</div>' +
            '<div class="backup-actions">' + downloadBtn + restoreBtn + '</div>' +
            '</div>' +
            '</div>';
    });

    container.innerHTML = html;
}

async function doManualBackup() {
    const btn = document.getElementById('btn-manual-backup');
    if (typeof window.DailyBackup === 'undefined') {
        showToast('Modul backup tidak tersedia', 'error');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Membackup...';
    }

    try {
        const result = await DailyBackup.runManualBackup();
        if (result && result.success) {
            showToast('✅ Backup berhasil! ' + (result.counts ? result.counts.products + ' produk tersimpan' : ''), 'success', 4000);
            loadBackupPage();
        } else if (result && result.error === 'storage_full') {
            showToast('⚠ Storage browser penuh. Hapus beberapa backup lama terlebih dahulu.', 'warning', 5000);
        } else if (result && result.error === 'no_data_available') {
            showToast('⚠ Tidak ada data untuk dibackup. Sinkronisasi data terlebih dahulu.', 'warning');
        } else {
            showToast('Backup berhasil disimpan', 'success');
            loadBackupPage();
        }
    } catch(e) {
        showToast('Gagal backup: ' + (e.message || 'Error tidak diketahui'), 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-download-fill"></i> Backup Sekarang';
        }
    }
}

async function doRestore(dateStr) {
    if (typeof window.DailyBackup === 'undefined') {
        showToast('Modul backup tidak tersedia', 'error');
        return;
    }

    const entry = _backupList.find(e => e.date === dateStr);
    const label = entry ? (entry.label || dateStr) : dateStr;

    AppModal.show({
        title: '🔄 Restore Backup?',
        subtitle: 'Data akan dimuat ke mode offline',
        icon: 'bi-cloud-upload',
        iconColor: 'rgba(99,102,241,0.15)',
        iconAccent: '#818cf8',
        bodyHTML: `
            <div style="padding:8px 0;">
                <div style="background:var(--surface-2); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:14px; margin-bottom:14px;">
                    <div style="font-size:var(--font-size-xs); color:var(--text-muted); margin-bottom:4px;">Backup yang akan di-restore:</div>
                    <div style="font-weight:700; font-size:var(--font-size-sm); color:var(--text-primary);">${label}</div>
                    ${entry && entry.counts ? `<div style="font-size:11px; color:var(--text-muted); margin-top:4px;">${entry.counts.products || 0} produk · ${entry.counts.suppliers || 0} supplier · ${entry.counts.categories || 0} kategori</div>` : ''}
                </div>
                <div style="font-size:var(--font-size-xs); color:var(--text-muted); line-height:1.7;">
                    Data backup akan dimuat ke IndexedDB lokal browser. Data saat ini di IndexedDB akan <strong>digantikan sementara</strong>. Gunakan fitur ini saat koneksi ke database bermasalah.
                    <br><br>
                    Untuk kembali ke data live, klik <strong>"Batalkan Restore"</strong> atau <strong>"Kembali ke Data Live"</strong>.
                </div>
            </div>
        `,
        submitText: '🔄 Ya, Restore Sekarang',
        submitClass: 'btn-primary-custom',
        onSubmit: async function() {
            try {
                const result = await DailyBackup.restoreFromBackup(dateStr);
                showToast(`✅ Restore berhasil! ${result.counts.products} produk dimuat ke mode offline.`, 'success', 5000);
                loadBackupPage();
                return true;
            } catch(e) {
                showToast('Gagal restore: ' + (e.message || 'Error'), 'error');
                return false;
            }
        }
    });
}

function clearRestore() {
    if (typeof window.DailyBackup === 'undefined') return;
    AppModal.show({
        title: 'Kembali ke Data Live?',
        subtitle: 'Mode restore akan dinonaktifkan',
        icon: 'bi-wifi',
        iconColor: 'rgba(34,197,94,0.12)',
        iconAccent: 'var(--success)',
        bodyHTML: '<div style="text-align:center; padding:12px 0; color:var(--text-muted); font-size:var(--font-size-sm);">Mode backup akan dinonaktifkan. Data akan kembali menggunakan IndexedDB live dari server saat terhubung kembali.</div>',
        submitText: 'Ya, Kembali ke Data Live',
        submitClass: 'btn-primary-custom',
        onSubmit: async function() {
            DailyBackup.clearActiveRestore();
            _activeRestore = null;
            renderRestoreBanner();
            renderBackupList();
            showToast('Mode data live aktif kembali', 'success');
            return true;
        }
    });
}

async function doDeleteAllBackups() {
    if (typeof window.DailyBackup === 'undefined') return;
    AppModal.show({
        title: 'Hapus Semua Backup?',
        subtitle: 'Tindakan ini tidak dapat dibatalkan',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: '<div style="text-align:center;padding:12px 0;color:var(--text-muted);font-size:var(--font-size-sm);">Semua <strong>' + _backupList.length + ' backup</strong> akan dihapus permanen dari browser ini.</div>',
        submitText: 'Ya, Hapus Semua',
        submitClass: 'btn-danger-custom',
        onSubmit: async function() {
            DailyBackup.deleteAllBackups();
            _backupList = [];
            _activeRestore = null;
            renderRestoreBanner();
            renderStatusCard();
            renderStorageStats();
            renderBackupList();
            showToast('Semua backup berhasil dihapus', 'success');
            return true;
        }
    });
}

// Auto-load on page ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() { setTimeout(loadBackupPage, 150); });
} else {
    setTimeout(loadBackupPage, 150);
}
</script>
