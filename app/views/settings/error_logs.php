<!-- Error Log Catcher View -->
<div class="page-section">
    <div style="margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="font-size:var(--font-size-lg); font-weight:700; margin-bottom:4px;">
                <i class="bi bi-bug" style="color:var(--danger); margin-right:6px;"></i>Error Log Catcher
            </h2>
            <p style="font-size:var(--font-size-xs); color:var(--text-muted);">
                Semua error JavaScript, Network, dan Console — 7 hari terakhir
            </p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <button id="btn-turbo-clean" onclick="window.AppCleaner && window.AppCleaner.cleanAll()" class="btn-outline-custom" style="padding:8px 14px; font-size:var(--font-size-xs); display:flex; align-items:center; gap:6px; color:#ca8a04; border-color:#eab308; background:rgba(234,179,8,0.1); font-weight:700;">
                <i class="bi bi-lightning-charge-fill"></i> Bersihkan Cache &amp; Turbo
            </button>
            <button id="btn-refresh-logs" onclick="loadErrorLogs()" class="btn-outline-custom" style="padding:8px 14px; font-size:var(--font-size-xs); display:flex; align-items:center; gap:6px;">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button id="btn-copy-logs" onclick="copyLogsToClipboard()" class="btn-outline-custom" style="padding:8px 14px; font-size:var(--font-size-xs); display:flex; align-items:center; gap:6px; color:var(--info); border-color:var(--info);">
                <i class="bi bi-clipboard"></i> Copy Semua
            </button>
            <button id="btn-clear-logs" onclick="clearErrorLogs()" class="btn-outline-custom" style="padding:8px 14px; font-size:var(--font-size-xs); display:flex; align-items:center; gap:6px; color:var(--danger); border-color:var(--danger);">
                <i class="bi bi-trash"></i> Hapus Semua
            </button>
        </div>
    </div>

    <!-- Stats Bar -->
    <div id="log-stats-bar" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;"></div>

    <!-- Filter Tabs -->
    <div style="display:flex; gap:4px; border-bottom:1px solid var(--border-color); margin-bottom:16px; overflow-x:auto; white-space:nowrap;">
        <button class="log-filter-btn active" data-filter="all" onclick="filterLogs('all', this)"
            style="padding:8px 14px; background:none; border:none; border-bottom:2px solid var(--primary); color:var(--primary); font-weight:700; font-size:var(--font-size-xs); cursor:pointer;">
            Semua
        </button>
        <button class="log-filter-btn" data-filter="error" onclick="filterLogs('error', this)"
            style="padding:8px 14px; background:none; border:none; border-bottom:2px solid transparent; color:var(--text-muted); font-weight:600; font-size:var(--font-size-xs); cursor:pointer;">
            <i class="bi bi-exclamation-triangle"></i> JS Error
        </button>
        <button class="log-filter-btn" data-filter="promise" onclick="filterLogs('promise', this)"
            style="padding:8px 14px; background:none; border:none; border-bottom:2px solid transparent; color:var(--text-muted); font-weight:600; font-size:var(--font-size-xs); cursor:pointer;">
            <i class="bi bi-arrow-repeat"></i> Promise
        </button>
        <button class="log-filter-btn" data-filter="console_error" onclick="filterLogs('console_error', this)"
            style="padding:8px 14px; background:none; border:none; border-bottom:2px solid transparent; color:var(--text-muted); font-weight:600; font-size:var(--font-size-xs); cursor:pointer;">
            <i class="bi bi-terminal"></i> Console
        </button>
        <button class="log-filter-btn" data-filter="network" onclick="filterLogs('network', this)"
            style="padding:8px 14px; background:none; border:none; border-bottom:2px solid transparent; color:var(--text-muted); font-weight:600; font-size:var(--font-size-xs); cursor:pointer;">
            <i class="bi bi-wifi-off"></i> Network
        </button>
    </div>

    <!-- Log Container -->
    <div id="log-container">
        <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
            <i class="bi bi-hourglass-split" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
            <div style="font-size:var(--font-size-sm);">Memuat log...</div>
        </div>
    </div>

    <!-- Info -->
    <div style="margin-top:16px; background:var(--surface-1); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:12px 16px; font-size:var(--font-size-xs); color:var(--text-muted);">
        <i class="bi bi-info-circle" style="color:var(--info); margin-right:6px;"></i>
        Log disimpan di <code style="font-family:monospace; background:var(--surface-2); padding:1px 4px; border-radius:3px;">localStorage</code> browser ini.
        Data <strong>otomatis dihapus</strong> setelah 7 hari.
        Maksimal <strong>500 entri</strong> tersimpan.
    </div>

    <a href="<?= BASE_URL ?>settings" class="btn-outline-custom" style="width:100%; padding:12px; text-align:center; display:block; font-weight:600; margin-top:16px;">
        Kembali ke Pengaturan
    </a>
</div>

<style>
.log-entry {
    background: var(--surface-1);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    margin-bottom: 8px;
    overflow: hidden;
    transition: box-shadow 200ms ease;
}
.log-entry:hover {
    box-shadow: 0 2px 12px rgba(0,0,0,0.15);
}
.log-entry-header {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    cursor: pointer;
    user-select: none;
}
.log-type-badge {
    flex-shrink: 0;
    font-size: 9px;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-top: 1px;
}
.log-type-error     { background: rgba(239,68,68,0.15);   color: #ef4444; }
.log-type-promise   { background: rgba(245,158,11,0.15);  color: #f59e0b; }
.log-type-console_error { background: rgba(245,158,11,0.15); color: #f59e0b; }
.log-type-console_warn  { background: rgba(99,102,241,0.15); color: #818cf8; }
.log-type-network   { background: rgba(59,130,246,0.15);  color: #3b82f6; }
.log-type-ai-error  { background: rgba(236,72,153,0.15);  color: #ec4899; }
.log-type-ai-empty  { background: rgba(168,85,247,0.15);  color: #a855f7; }
.log-type-ai-partial{ background: rgba(249,115,22,0.15);  color: #f97316; }
.log-entry-msg {
    flex: 1;
    font-size: var(--font-size-xs);
    font-weight: 600;
    color: var(--text-primary);
    word-break: break-all;
    line-height: 1.4;
}
.log-entry-meta {
    font-size: 10px;
    color: var(--text-muted);
    margin-top: 3px;
    font-weight: 400;
}
.log-entry-details {
    display: none;
    border-top: 1px solid var(--border-color);
    padding: 12px 14px;
    background: var(--bg-primary);
}
.log-entry-details.expanded {
    display: block;
}
.log-entry-details pre {
    font-size: 11px;
    font-family: 'Courier New', monospace;
    color: var(--text-secondary);
    white-space: pre-wrap;
    word-break: break-all;
    margin: 0;
    line-height: 1.5;
    background: var(--surface-2);
    padding: 10px;
    border-radius: var(--radius-sm);
    max-height: 200px;
    overflow-y: auto;
}
.log-stat-chip {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    border: 1px solid transparent;
}
</style>

<script>
var _allLogs = [];
var _currentFilter = 'all';

var TYPE_CONFIG = {
    'error':          { label: 'JS Error',      cls: 'log-type-error',         icon: 'bi-exclamation-octagon-fill', color: '#ef4444' },
    'promise':        { label: 'Promise',        cls: 'log-type-promise',       icon: 'bi-arrow-repeat',             color: '#f59e0b' },
    'console_error':  { label: 'Console ❌',     cls: 'log-type-console_error', icon: 'bi-terminal-fill',            color: '#f59e0b' },
    'console_warn':   { label: 'Console ⚠',      cls: 'log-type-console_warn',  icon: 'bi-terminal',                 color: '#818cf8' },
    'network':        { label: 'Network',         cls: 'log-type-network',       icon: 'bi-wifi-off',                 color: '#3b82f6' },
    'ai_scan':        { label: 'AI Scan ❌',      cls: 'log-type-ai-error',      icon: 'bi-camera-fill',              color: '#ec4899' },
    'ai_scan_error':  { label: 'AI Scan Error',   cls: 'log-type-ai-error',      icon: 'bi-camera-fill',              color: '#ec4899' },
    'ai_scan_empty':  { label: 'AI Scan 0 Item',  cls: 'log-type-ai-empty',      icon: 'bi-camera',                   color: '#a855f7' },
    'ai_scan_result': { label: 'AI Scan Parsial', cls: 'log-type-ai-partial',    icon: 'bi-camera2',                  color: '#f97316' },
};

function formatDate(isoStr, fallbackStr) {
    if (fallbackStr && String(fallbackStr).includes(':')) return fallbackStr;
    if (!isoStr) return '-';
    try {
        var d = new Date(isoStr);
        if (isNaN(d.getTime())) return String(isoStr);
        var pad = function(n) { return n < 10 ? '0' + n : String(n); };
        var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        var datePart = pad(d.getDate()) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        var timePart = pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
        return datePart + ', ' + timePart + ' WIB';
    } catch(e) { return String(isoStr); }
}

function safeHtml(str) {
    return String(str || '')
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}

function loadErrorLogs() {
    if (typeof window.ErrorLogger === 'undefined') {
        document.getElementById('log-container').innerHTML =
            '<div style="text-align:center;padding:40px;color:var(--danger);">' +
            '<i class="bi bi-exclamation-triangle" style="font-size:2rem;display:block;margin-bottom:8px;"></i>' +
            '<div>ErrorLogger tidak tersedia. Pastikan error-logger.js sudah dimuat.</div></div>';
        return;
    }
    _allLogs = window.ErrorLogger.getLogs().reverse(); // terbaru di atas
    renderStats();
    renderLogs(_currentFilter);
}

function renderStats() {
    var counts = {};
    _allLogs.forEach(function(e) {
        counts[e.type] = (counts[e.type] || 0) + 1;
    });
    var total = _allLogs.length;
    var html = '<div class="log-stat-chip" style="background:var(--surface-1);border-color:var(--border-color);color:var(--text-primary);">' +
        '<i class="bi bi-list-ul" style="color:var(--text-muted);"></i> Total: ' + total + '</div>';
    Object.keys(TYPE_CONFIG).forEach(function(type) {
        if (counts[type]) {
            var cfg = TYPE_CONFIG[type];
            html += '<div class="log-stat-chip" style="background:rgba(0,0,0,0.15);border-color:' + cfg.color + '33;color:' + cfg.color + ';">' +
                '<i class="bi ' + cfg.icon + '"></i> ' + cfg.label + ': ' + counts[type] + '</div>';
        }
    });
    document.getElementById('log-stats-bar').innerHTML = html;
}

function renderLogs(filter) {
    var logs = filter === 'all' ? _allLogs : _allLogs.filter(function(e) {
        if (filter === 'console_error') return e.type === 'console_error' || e.type === 'console_warn';
        return e.type === filter;
    });
    var container = document.getElementById('log-container');

    if (logs.length === 0) {
        container.innerHTML =
            '<div style="text-align:center;padding:48px 20px;color:var(--text-muted);">' +
            '<div style="width:64px;height:64px;background:var(--surface-1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.8rem;">' +
            '<i class="bi bi-shield-check" style="color:var(--success);"></i></div>' +
            '<div style="font-weight:700;font-size:var(--font-size-sm);margin-bottom:4px;">Tidak Ada Error</div>' +
            '<div style="font-size:var(--font-size-xs);">' + (filter === 'all' ? 'Tidak ada error dalam 7 hari terakhir.' : 'Tidak ada error tipe ini.') + '</div>' +
            '</div>';
        return;
    }

    var html = '';
    logs.forEach(function(entry, idx) {
        var cfg = TYPE_CONFIG[entry.type] || { label: entry.type, cls: 'log-type-error', icon: 'bi-bug', color: '#ef4444' };
        var hasDetails = entry.details && Object.keys(entry.details).length > 0;
        var detailText = hasDetails ? JSON.stringify(entry.details, null, 2) : '';

        // Ambil info source jika ada
        var sourceInfo = '';
        if (entry.details && entry.details.source) {
            sourceInfo = entry.details.source;
            if (entry.details.line) sourceInfo += ':' + entry.details.line;
        } else if (entry.details && entry.details.status) {
            sourceInfo = 'HTTP ' + entry.details.status;
        }

        var timeFormatted = entry.time_str || formatDate(entry.ts);

        html += '<div class="log-entry" data-type="' + safeHtml(entry.type) + '">';
        html += '<div class="log-entry-header" onclick="toggleDetails(\'details-' + idx + '\')">';
        html += '<div>';
        html += '<span class="log-type-badge ' + cfg.cls + '">' + cfg.label + '</span>';
        html += '</div>';
        html += '<div class="log-entry-msg">';
        html += '<div style="font-weight:600;">' + safeHtml(entry.message) + '</div>';
        html += '<div class="log-entry-meta" style="margin-top:4px; display:flex; align-items:center; flex-wrap:wrap; gap:6px;">';
        html += '<span style="background:var(--surface-2); padding:2px 7px; border-radius:4px; font-weight:600; color:var(--text-primary); border:1px solid var(--border-color);"><i class="bi bi-clock-fill" style="margin-right:4px;color:var(--primary);"></i>' + safeHtml(timeFormatted) + '</span>';
        if (sourceInfo) html += '<code style="font-size:10px;font-family:monospace; background:var(--surface-2); padding:2px 6px; border-radius:4px;">' + safeHtml(sourceInfo) + '</code>';
        html += '</div>';
        html += '</div>';
        if (hasDetails || entry.url || entry.stack) {
            html += '<i class="bi bi-chevron-down" style="color:var(--text-muted);font-size:12px;flex-shrink:0;margin-top:2px;"></i>';
        }
        html += '</div>'; // end header

        if (hasDetails || entry.url) {
            html += '<div class="log-entry-details" id="details-' + idx + '">';
            if (entry.url) {
                html += '<div style="font-size:10px;color:var(--text-muted);margin-bottom:8px;word-break:break-all;">';
                html += '<strong>URL:</strong> ' + safeHtml(entry.url) + '</div>';
            }
            if (detailText) {
                html += '<pre>' + safeHtml(detailText) + '</pre>';
            }
            html += '</div>';
        }
        html += '</div>'; // end entry
    });

    container.innerHTML = html;
}

function toggleDetails(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('expanded');
    // Rotasi chevron
    var header = el.previousElementSibling;
    if (header) {
        var chevron = header.querySelector('.bi-chevron-down, .bi-chevron-up');
        if (chevron) {
            chevron.classList.toggle('bi-chevron-down');
            chevron.classList.toggle('bi-chevron-up');
        }
    }
}

function filterLogs(filter, btn) {
    _currentFilter = filter;
    document.querySelectorAll('.log-filter-btn').forEach(function(b) {
        b.style.borderBottomColor = 'transparent';
        b.style.color = 'var(--text-muted)';
        b.style.fontWeight = '600';
    });
    if (btn) {
        btn.style.borderBottomColor = 'var(--primary)';
        btn.style.color = 'var(--primary)';
        btn.style.fontWeight = '700';
    }
    renderLogs(filter);
}

function clearErrorLogs() {
    AppModal.show({
        title: 'Hapus Semua Log?',
        subtitle: 'Tindakan ini tidak dapat dibatalkan',
        icon: 'bi-trash',
        iconColor: 'var(--danger-bg)',
        iconAccent: 'var(--danger)',
        bodyHTML: '<div style="text-align:center;padding:12px 0;color:var(--text-muted);font-size:var(--font-size-sm);">Semua log error akan dihapus permanen dari browser ini.</div>',
        submitText: 'Ya, Hapus Semua',
        submitClass: 'btn-danger-custom',
        onSubmit: async function() {
            if (typeof window.ErrorLogger !== 'undefined') {
                window.ErrorLogger.clearLogs();
            }
            _allLogs = [];
            renderStats();
            renderLogs(_currentFilter);
            showToast('Semua log berhasil dihapus', 'success');
            return true;
        }
    });
}

function copyLogsToClipboard() {
    if (_allLogs.length === 0) {
        showToast('Tidak ada log untuk disalin', 'warning');
        return;
    }
    var nowExport = new Date();
    var pad = function(n) { return n < 10 ? '0' + n : String(n); };
    var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    var exportTimeStr = pad(nowExport.getDate()) + ' ' + months[nowExport.getMonth()] + ' ' + nowExport.getFullYear() + ', ' + pad(nowExport.getHours()) + ':' + pad(nowExport.getMinutes()) + ':' + pad(nowExport.getSeconds()) + ' WIB';

    var text = '=== AlfarezMart Error Log Export ===\n';
    text += 'Waktu Export : ' + exportTimeStr + '\n';
    text += 'Total Entri  : ' + _allLogs.length + '\n\n';

    _allLogs.forEach(function(e, i) {
        var timeDisplay = e.time_str || formatDate(e.ts);
        text += '--- [' + (i + 1) + '] ---\n';
        text += 'Waktu  : ' + timeDisplay + '\n';
        text += 'Tipe   : ' + e.type + '\n';
        text += 'Pesan  : ' + e.message + '\n';
        text += 'URL    : ' + e.url + '\n';
        if (e.details) {
            text += 'Detail : ' + JSON.stringify(e.details, null, 2) + '\n';
        }
        text += '\n';
    });

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Log berhasil disalin ke clipboard! (' + _allLogs.length + ' entri)', 'success');
        }).catch(function() {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        showToast('Log berhasil disalin!', 'success');
    } catch(e) {
        showToast('Gagal menyalin. Silakan screenshot halaman ini.', 'error');
    }
    document.body.removeChild(ta);
}

// Muat log saat halaman siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadErrorLogs);
} else {
    // Tunggu sebentar agar ErrorLogger sudah terinisialisasi
    setTimeout(loadErrorLogs, 100);
}
</script>
