/**
 * AlfarezMart PWA - Utility Functions
 */

// Format currency
function formatRupiah(num, prefix = 'Rp') {
    if (!num && num !== 0) return prefix + '0';
    return prefix + Math.round(num).toLocaleString('id-ID');
}

// Format date
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Show toast notification
function showToast(message, type = 'info', duration = 3000, onClick = null) {
    const container = document.getElementById('toastContainer');
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    const toast = document.createElement('div');
    toast.className = `app-toast ${type}`;
    toast.innerHTML = `<i class="bi ${icons[type] || icons.info}"></i><span>${message}</span>`;
    if (onClick) {
        toast.style.cursor = 'pointer';
        toast.addEventListener('click', onClick);
    }
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(-20px)'; setTimeout(() => toast.remove(), 300); }, duration);
}

// Debounce function
function debounce(func, wait = 300) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// API call helper
async function api(endpoint, methodOrOptions = {}, data = null) {
    let options = {};
    if (typeof methodOrOptions === 'string') {
        options.method = methodOrOptions;
        if (data) options.body = JSON.stringify(data);
    } else {
        options = methodOrOptions || {};
    }
    
    const method = (options.method || 'GET').toUpperCase();
    
    // OFFLINE INTERCEPTION FOR MUTATIONS
    if (!navigator.onLine && ['POST', 'PUT', 'DELETE'].includes(method)) {
        if (window.OfflineDB) {
            try {
                let payloadData = null;
                if (options.body) {
                    if (options.body instanceof FormData) {
                        payloadData = {};
                        for (let [key, value] of options.body.entries()) {
                            if (value instanceof File) continue; // skip file for offline sync
                            if (key.endsWith('[]')) {
                                const baseKey = key.slice(0, -2);
                                if (!payloadData[baseKey]) payloadData[baseKey] = [];
                                payloadData[baseKey].push(value);
                            } else {
                                payloadData[key] = value;
                            }
                        }
                    } else if (typeof options.body === 'string') {
                        payloadData = JSON.parse(options.body);
                    } else {
                        payloadData = options.body;
                    }
                }
                await window.OfflineDB.addPendingChange(endpoint, method, payloadData);
                
                // Update badge if app.js functions are available
                if (typeof updateSyncBadge === 'function') {
                    updateSyncBadge();
                }

                showToast('Offline: Perubahan disimpan ke antrian lokal', 'warning');
                
                // Return dummy success so UI doesn't break
                return { success: true, message: 'Disimpan offline (menunggu sinkronisasi)', id: 'offline_' + Date.now() };
            } catch (e) {
                console.error("Gagal menyimpan ke antrian offline", e);
                throw new Error('Offline: Gagal menyimpan data sementara');
            }
        } else {
            throw new Error('Koneksi terputus dan sistem offline tidak siap.');
        }
    }

    // Helper to queue mutation offline
    async function fallbackOfflineQueue() {
        if (window.OfflineDB && ['POST', 'PUT', 'DELETE'].includes(method)) {
            try {
                let payloadData = null;
                if (options.body) {
                    if (options.body instanceof FormData) {
                        payloadData = {};
                        for (let [key, value] of options.body.entries()) {
                            if (value instanceof File) continue;
                            if (key.endsWith('[]')) {
                                const baseKey = key.slice(0, -2);
                                if (!payloadData[baseKey]) payloadData[baseKey] = [];
                                payloadData[baseKey].push(value);
                            } else {
                                payloadData[key] = value;
                            }
                        }
                    } else if (typeof options.body === 'string') {
                        payloadData = JSON.parse(options.body);
                    } else {
                        payloadData = options.body;
                    }
                }
                await window.OfflineDB.addPendingChange(endpoint, method, payloadData);
                if (typeof updateSyncBadge === 'function') updateSyncBadge();
                showToast('Sinyal lemah: Data disimpan ke antrian offline', 'warning');
                return { success: true, message: 'Disimpan offline (Sinyal Lemah)', invoice: 'OFF-' + Date.now(), id: 'off_' + Date.now() };
            } catch (e) {
                console.error("Gagal simpan antrian offline", e);
            }
        }
        return null;
    }

    const config = { ...options };
    if (!config.headers) config.headers = {};
    if (!(options.body instanceof FormData) && !config.headers['Content-Type']) {
        config.headers['Content-Type'] = 'application/json';
    }
    
    // Add CSRF token header if exists
    const csrfToken = document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '';
    if (csrfToken && (!config.headers['X-CSRF-Token'])) {
        config.headers['X-CSRF-Token'] = csrfToken;
    }

    // Set appropriate timeout: 120s for heavy AI/Sync endpoints, 8s for weak signal, 15s default
    const isHeavyEndpoint = endpoint.includes('/sync') || endpoint.includes('/scan-invoice') || endpoint.includes('/ai/') || endpoint.includes('/scan-ai') || endpoint.includes('/export') || endpoint.includes('/import');
    let defaultTimeout = isHeavyEndpoint ? 120000 : 15000;
    if (typeof window.getSignalState === 'function' && window.getSignalState() === 'weak' && !isHeavyEndpoint) {
        defaultTimeout = 8000;
    }
    const timeoutMs = Math.max(config.timeout || defaultTimeout, 5000);
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
    if (!config.signal) config.signal = controller.signal;

    try {
        const response = await fetch(endpoint, config);
        clearTimeout(timeoutId);
        
        // Read response as text first to avoid json parse crash on empty/invalid body
        const text = await response.text();
        
        if (!text || text.trim().length === 0) {
            throw new Error('Server mengembalikan respons kosong.');
        }
        
        let jsonData;
        try {
            jsonData = JSON.parse(text);
        } catch (parseErr) {
            console.error('Response bukan JSON valid:', text.substring(0, 500));
            if (text.includes('<br') || text.includes('<html') || text.includes('Fatal error')) {
                throw new Error('Server error (kemungkinan timeout).');
            }
            throw new Error('Respons server tidak valid (bukan JSON)');
        }
        
        if (!response.ok) throw new Error(jsonData.error || 'Request failed');
        return jsonData;
    } catch (error) {
        clearTimeout(timeoutId);
        
        // Fallback for network error / weak signal timeout
        if (['POST', 'PUT', 'DELETE'].includes(method)) {
            const offlineRes = await fallbackOfflineQueue();
            if (offlineRes) return offlineRes;
        }

        if (error.name === 'AbortError') {
            error.message = 'Permintaan waktu habis (timeout server).';
        }

        if (!options.silent && !config.silent) {
            console.error('API Error:', error);
            showToast(error.message || 'Koneksi terputus', 'error');
        }
        throw error;
    }
}

// Truncate text
function truncate(text, len = 35) {
    if (!text) return '';
    return text.length > len ? text.substring(0, len) + '...' : text;
}

// Calculate markup (berbasis harga modal)
function calcMarkup(buy, sell) {
    if (!buy || buy <= 0 || !sell || sell <= 0) return 0;
    return ((sell - buy) / buy * 100).toFixed(1);
}

// Alias for backward compatibility (in case it's used elsewhere)
function calcMargin(buy, sell) {
    return calcMarkup(buy, sell);
}
