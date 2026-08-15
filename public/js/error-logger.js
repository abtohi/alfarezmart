/**
 * AlfarezMart - Global Error Log Catcher
 * Menangkap semua jenis error (JS, Promise, Console, Network) dan menyimpannya
 * di localStorage selama maksimal 7 hari. Auto-purge saat aplikasi dibuka.
 */
(function () {
    'use strict';

    const STORAGE_KEY = 'alfarezmart_error_logs';
    const MAX_DAYS = 7;
    const MAX_LOGS = 500; // batas maksimum entri log agar tidak terlalu besar

    // ─── Helper: timestamp ISO ───────────────────────────────────────────────
    function now() {
        return new Date().toISOString();
    }

    // ─── Load logs dari localStorage ────────────────────────────────────────
    function loadLogs() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    // ─── Purge log lebih dari 7 hari ────────────────────────────────────────
    function purgeLogs(logs) {
        const cutoff = new Date();
        cutoff.setDate(cutoff.getDate() - MAX_DAYS);
        return logs.filter(function (entry) {
            try {
                return new Date(entry.ts) >= cutoff;
            } catch (e) {
                return false;
            }
        });
    }

    // ─── Simpan log ke localStorage ─────────────────────────────────────────
    function saveLogs(logs) {
        // Batasi jumlah entri agar tidak memenuhi storage
        if (logs.length > MAX_LOGS) {
            logs = logs.slice(logs.length - MAX_LOGS);
        }
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(logs));
        } catch (e) {
            // Jika storage penuh, hapus setengah entri lama dan coba lagi
            try {
                logs = logs.slice(Math.floor(logs.length / 2));
                localStorage.setItem(STORAGE_KEY, JSON.stringify(logs));
            } catch (e2) { /* ignore */ }
        }
    }

    // ─── Tambah satu entri log baru ─────────────────────────────────────────
    function addLog(type, message, details) {
        var logs = purgeLogs(loadLogs());
        var entry = {
            id: Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            ts: now(),
            type: type,        // 'error' | 'promise' | 'console_error' | 'console_warn' | 'network'
            message: String(message || '').substring(0, 500),
            details: details || null,
            url: window.location.href,
            ua: navigator.userAgent.substring(0, 200)
        };
        logs.push(entry);
        saveLogs(logs);
    }

    // ─── Jalankan auto-purge saat app pertama kali dibuka ───────────────────
    (function autoPurgeOnLoad() {
        var logs = purgeLogs(loadLogs());
        saveLogs(logs);
    })();

    // ─── 1. Tangkap window.onerror (JavaScript runtime error) ───────────────
    var _origOnError = window.onerror;
    window.onerror = function (message, source, lineno, colno, error) {
        // Abaikan error dari extension browser
        if (source && (source.includes('extension://') || source.includes('moz-extension://'))) {
            if (_origOnError) return _origOnError.apply(this, arguments);
            return false;
        }
        addLog('error', message, {
            source: source || '',
            line: lineno || 0,
            col: colno || 0,
            stack: error && error.stack ? String(error.stack).substring(0, 1000) : null
        });
        if (_origOnError) return _origOnError.apply(this, arguments);
        return false;
    };

    // ─── 2. Tangkap unhandledrejection (Promise error) ──────────────────────
    window.addEventListener('unhandledrejection', function (event) {
        var reason = event.reason;
        var message = reason instanceof Error
            ? reason.message
            : (typeof reason === 'string' ? reason : JSON.stringify(reason));
        
        // Abaikan rejection internal browser saat update ServiceWorker background
        if (message && (
            message.indexOf('ServiceWorker') !== -1 ||
            message.indexOf('serviceworker') !== -1 ||
            message.indexOf('AbortError') !== -1
        )) {
            return;
        }

        addLog('promise', 'Unhandled Promise Rejection: ' + message, {
            stack: reason instanceof Error && reason.stack
                ? String(reason.stack).substring(0, 1000)
                : null
        });
    });

    // ─── 3. Intercept console.error dan console.warn ────────────────────────
    var _origConsoleError = console.error;
    var _origConsoleWarn = console.warn;

    console.error = function () {
        var args = Array.prototype.slice.call(arguments);
        var msg = args.map(function (a) {
            return (a instanceof Error) ? (a.message + ' | ' + (a.stack || '')) : String(a);
        }).join(' | ');
        addLog('console_error', msg, null);
        _origConsoleError.apply(console, arguments);
    };

    console.warn = function () {
        var args = Array.prototype.slice.call(arguments);
        var msg = args.map(function (a) {
            return (a instanceof Error) ? a.message : String(a);
        }).join(' | ');
        addLog('console_warn', msg, null);
        _origConsoleWarn.apply(console, arguments);
    };

    // ─── 4. Intercept fetch untuk menangkap network error ───────────────────
    // URL-URL berikut diabaikan dari pencatatan error (fire-and-forget / expected)
    var IGNORED_URL_PATTERNS = [
        '/api/activity/log',      // fire-and-forget activity tracker
        '/api/sync/',             // background sync
        '/api/products/sync',     // background Dexie IndexedDB sync
        '/setup',                 // 403 intentional di production
        '/sw.js',                 // service worker check
    ];

    function isIgnoredUrl(url) {
        if (!url) return false;
        var u = String(url).toLowerCase();
        return IGNORED_URL_PATTERNS.some(function(p) { return u.indexOf(p.toLowerCase()) !== -1; });
    }

    if (window.fetch) {
        var _origFetch = window.fetch;
        window.fetch = function (input, init) {
            var url = typeof input === 'string' ? input : (input && input.url ? input.url : String(input));
            
            // Abaikan URL yang memang fire-and-forget atau expected
            var skipLog = isIgnoredUrl(url);

            // Deteksi prefetch (X-Prefetch header atau priority: low)
            if (!skipLog && init) {
                if (init.priority === 'low') skipLog = true;
                if (init.headers) {
                    if (typeof init.headers.get === 'function' && init.headers.get('X-Prefetch')) {
                        skipLog = true;
                    } else if (init.headers['X-Prefetch'] || init.headers['x-prefetch']) {
                        skipLog = true;
                    }
                }
            }

            return _origFetch.apply(this, arguments).then(function (response) {
                if (!response.ok && !skipLog) {
                    // Abaikan 403 dan 404 untuk halaman navigasi biasa (bukan API)
                    var isApi = String(url).indexOf('/api/') !== -1;
                    var skip4xx = (response.status === 403 || response.status === 404) && !isApi;
                    if (!skip4xx) {
                        var cloned = response.clone();
                        cloned.text().then(function (bodyText) {
                            addLog('network', 'HTTP ' + response.status + ' ' + response.statusText + ' - ' + url, {
                                status: response.status,
                                statusText: response.statusText,
                                responsePreview: bodyText.substring(0, 500)
                            });
                        }).catch(function () {
                            addLog('network', 'HTTP ' + response.status + ' ' + response.statusText + ' - ' + url, {
                                status: response.status,
                                statusText: response.statusText
                            });
                        });
                    }
                }
                return response;
            }).catch(function (err) {
                // Abaikan AbortError (misal request dibatalkan saat navigasi berpindah)
                var errName = err && err.name ? err.name : '';
                if (errName === 'AbortError') {
                    throw err;
                }

                // Network failure (offline, CORS, timeout) — log hanya jika bukan prefetch/fire-and-forget
                if (!skipLog) {
                    addLog('network', 'Fetch Failed: ' + (err && err.message ? err.message : String(err)) + ' - ' + url, {
                        errorType: errName || 'NetworkError'
                    });
                }
                throw err;
            });
        };
    }

    // ─── 5. Expose API global untuk digunakan view ─────────────────────────
    window.ErrorLogger = {
        getLogs: function () {
            return purgeLogs(loadLogs());
        },
        clearLogs: function () {
            localStorage.removeItem(STORAGE_KEY);
        },
        addLog: addLog
    };

})();
