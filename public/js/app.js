/**
 * AlfarezMart PWA - Main App Logic
 */
let deferredInstallPrompt = null;

// Auto-login from localStorage if available
function checkAutoLogin() {
    const loggedIn = localStorage.getItem('alfarezmart_logged_in');
    const onLoginPage = window.location.pathname.includes('/login');

    // Skenario 1: di halaman normal, sudah login → lanjut
    if (loggedIn === 'true' && !onLoginPage) return;

    // Skenario 2: di halaman login tapi flag bilang sudah login
    if (loggedIn === 'true' && onLoginPage) {
        if (!navigator.onLine) {
            // Offline: SW akan serve cache, langsung redirect ke dashboard
            window.location.href = BASE_URL;
            return;
        }
        // Online: server validasi session dulu
        setTimeout(() => {
            fetch(`${BASE_URL}`, { credentials: 'same-origin' })
                .then(r => { if (r.ok) window.location.href = BASE_URL; })
                .catch(() => { window.location.href = BASE_URL; });
        }, 300);
    }
}

// Call auto-login check early
checkAutoLogin();

window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    deferredInstallPrompt = event;
    showToast('Aplikasi AlfarezMart siap diinstal. Ketuk notifikasi ini untuk pasang.', 'info', 10000, async () => {
        if (!deferredInstallPrompt) return;
        deferredInstallPrompt.prompt();
        const choiceResult = await deferredInstallPrompt.userChoice;
        if (choiceResult.outcome === 'accepted') {
            showToast('Instalasi berhasil. Silakan buka aplikasi dari layar utama.', 'success');
        } else {
            showToast('Instalasi dibatalkan.', 'warning');
        }
        deferredInstallPrompt = null;
    });
});

window.addEventListener('appinstalled', () => {
    showToast('AlfarezMart sudah terpasang di perangkat Anda.', 'success');
});

document.addEventListener('DOMContentLoaded', async () => {
    initSearch();
    initHeaderScroll();
    // initPullToRefresh(); // Disabled pull-to-refresh as requested
    
    // Initialize Offline DB
    try {
        if (typeof OfflineDB !== 'undefined') {
            await OfflineDB.init();

            // Update badge immediately
            await updateSyncBadge();
            
            // Background sync if online (throttled to prevent mobile lag)
            if (navigator.onLine) {
                const LAST_SYNC_KEY = 'alfarezmart_last_bg_sync';
                const lastSync = parseInt(localStorage.getItem(LAST_SYNC_KEY) || '0', 10);
                const now = Date.now();
                // Only run automatic background sync if more than 15 minutes have passed
                if (now - lastSync > 900000) {
                    setTimeout(() => {
                        localStorage.setItem(LAST_SYNC_KEY, String(Date.now()));
                        if (typeof OfflineDB.syncProductsFromServer === 'function') {
                            OfflineDB.syncProductsFromServer().catch(() => {});
                        }
                        syncPendingChanges().catch(() => {});
                    }, 5000);
                } else {
                    // Just flush pending outbox quietly
                    setTimeout(() => {
                        syncPendingChanges().catch(() => {});
                    }, 2000);
                }
            }
        }
    } catch (e) {
        console.error('Offline DB init failed:', e);
    }

    // Manage offline banner visibility
    _updateOfflineBanner();

    // Register online/offline listeners
    window.addEventListener('online', async () => {
        _updateOfflineBanner();
        showToast('Koneksi kembali! Menyinkronkan data...', 'success', 3000);
        try {
            if (typeof OfflineDB !== 'undefined') {
                await syncPendingChanges();
                await updateSyncBadge();
            }
        } catch(e) { console.error('Auto sync on reconnect failed:', e); }
    });

    window.addEventListener('offline', () => {
        _updateOfflineBanner();
        showToast('Koneksi terputus. Aplikasi beralih ke mode offline.', 'warning', 4000);
    });
});

function _updateOfflineBanner() {
    // Inject spin keyframes if not present
    if (!document.getElementById('spinKeyframes')) {
        const style = document.createElement('style');
        style.id = 'spinKeyframes';
        style.innerHTML = `@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }`;
        document.head.appendChild(style);
    }

    const banner = document.getElementById('offlineBanner');
    if (!banner) return;
    if (!navigator.onLine) {
        banner.style.display = 'block';
        // Push content down so banner doesn't cover header
        const header = document.getElementById('appHeader');
        if (header) header.style.top = banner.offsetHeight + 'px';
    } else {
        banner.style.display = 'none';
        const header = document.getElementById('appHeader');
        if (header) header.style.top = '';
    }
}


async function triggerSync() {
    const btn = document.getElementById('btnSync');
    if (!navigator.onLine) {
        showToast('Koneksi terputus. Tidak dapat sinkronisasi.', 'warning');
        return;
    }
    
    if (btn) btn.style.transform = 'rotate(360deg)';
    
    try {
        if (typeof window.OfflineDB !== 'undefined') {
            const countPending = await window.OfflineDB.countPending();
            if (countPending > 0) {
                // syncPendingChanges will also sync products when done
                await syncPendingChanges(); 
            } else {
                showToast('Mengunduh data master & foto produk...', 'info', 4000);
                if (typeof OfflineDB.syncAllDataFromServer === 'function') {
                    await OfflineDB.syncAllDataFromServer();
                    showToast('✅ Data & foto produk berhasil diunduh — siap offline!', 'success', 4000);
                } else {
                    const count = await OfflineDB.syncProductsFromServer();
                    showToast(`Berhasil sinkronisasi ${count} produk ke perangkat`, 'success');
                }
            }
        }
    } catch (e) {
        showToast('Gagal sinkronisasi data', 'error');
    }
    
    if (btn) {
        setTimeout(() => {
            btn.style.transform = 'rotate(0deg)';
        }, 500);
    }
}

// Global Search
function initSearch() {
    const btnSearch = document.getElementById('btnSearch');
    const overlay = document.getElementById('searchOverlay');
    const btnClose = document.getElementById('btnCloseSearch');
    const input = document.getElementById('globalSearch');
    const results = document.getElementById('searchResults');

    if (!btnSearch) return;

    // Set placeholder based on context
    if (window.location.href.includes('/suppliers')) {
        input.placeholder = "Cari Nama Supplier atau Sales...";
    }

    btnSearch.addEventListener('click', () => {
        overlay.classList.add('active');
        setTimeout(() => input.focus(), 200);
    });

    btnClose.addEventListener('click', () => {
        overlay.classList.remove('active');
        input.value = '';
        results.innerHTML = '';
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('active');
            input.value = '';
            results.innerHTML = '';
        }
    });

    input.addEventListener('input', debounce(async () => {
        const q = input.value.trim();
        if (q.length < 2) { results.innerHTML = ''; return; }

        try {
            if (window.location.href.includes('/suppliers')) {
                // Search Suppliers / Sales
                const data = await api(`${BASE_URL}api/suppliers/search?q=${encodeURIComponent(q)}`);
                if (input.value.trim().length < 2) { results.innerHTML = ''; return; }
                if (data.length === 0) {
                    results.innerHTML = '<div class="empty-state" style="padding:24px"><i class="bi bi-search"></i><p>Supplier/Sales tidak ditemukan</p></div>';
                    return;
                }
                results.innerHTML = data.map(s => `
                    <div class="search-result-item" style="cursor:pointer;" onclick="document.getElementById('searchOverlay').classList.remove('active'); window.location.href='${BASE_URL}suppliers#supplier-card-${s.id}'; setTimeout(()=> { const el = document.getElementById('supplier-card-${s.id}'); if(el){ el.scrollIntoView({behavior: 'smooth', block: 'center'}); el.style.background = 'var(--warning-bg)'; setTimeout(()=>el.style.background='', 2000); } }, 500);">
                        <div style="width:40px;height:40px;background:var(--info-bg);border-radius:8px;display:flex;align-items:center;justify-content:center">
                            <i class="bi bi-building" style="color:var(--info)"></i>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:0.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${s.name}</div>
                            <div style="font-size:0.7rem;color:var(--text-muted)">
                                ${s.match_type === 'sales' ? 'Sales: ' + s.match_name : (s.type_name || 'Supplier')}
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                // Default Product Search
                // Strategy: When online → always use server (accurate, includes new products).
                //           When offline → use IndexedDB only.
                //           IndexedDB is shown ONLY as instant preview while server fetches.
                try {
                    const currentQ = q;
                    const isWeak = (typeof window.getSignalState === 'function' && window.getSignalState() === 'weak');

                    // Show instant offline preview immediately (0ms delay)
                    let offlineResultsFound = false;
                    if (typeof OfflineDB !== 'undefined') {
                        const offlineData = await OfflineDB.searchProducts(currentQ);
                        if (input.value.trim() !== currentQ) return; // stale
                        if (offlineData && offlineData.length > 0) {
                            offlineResultsFound = true;
                            renderProductSearch(offlineData, results, true); // true = "preview" badge
                        }
                    }

                    // If offline or weak signal with local results found, stop here to avoid freezing UI
                    if (!navigator.onLine || (isWeak && offlineResultsFound)) {
                        return;
                    }

                    // Fetch server results (handles new items)
                    const serverData = await api(`${BASE_URL}api/products/search?q=${encodeURIComponent(currentQ)}`, { timeout: 6000, silent: true });
                    if (input.value.trim() !== currentQ) return; // stale
                    if (input.value.trim().length < 2) { results.innerHTML = ''; return; }
                    renderProductSearch(serverData, results, currentQ);
                } catch (e) {
                    // Server failed / timed out → show offline results as fallback if not already rendered
                    try {
                        if (typeof OfflineDB !== 'undefined' && input.value.trim() === q) {
                            const fallback = await OfflineDB.searchProducts(q);
                            if (input.value.trim().length >= 2) renderProductSearch(fallback, results, q);
                        }
                    } catch (e2) {
                        if (input.value.trim() === q) results.innerHTML = '<div class="empty-state" style="padding:24px"><p>Produk tidak ditemukan</p></div>';
                    }
                }
            }
        } catch (e) {
            results.innerHTML = '<div class="empty-state" style="padding:24px"><p>Gagal mencari</p></div>';
        }
    }, 200)); // 200ms debounce — gives offline preview time to show before server call
}


function renderProductSearch(data, results, querySent = '') {
    const input = document.getElementById('globalSearch');
    if (querySent && input && input.value.trim().toLowerCase() !== querySent.toLowerCase()) {
        return; // Stale check
    }
    if (!data || data.length === 0) {
        results.innerHTML = '<div class="empty-state" style="padding:24px"><i class="bi bi-search"></i><p>Produk tidak ditemukan</p></div>';
        return;
    }
    results.innerHTML = data.map(p => `
        <a href="${BASE_URL}products/${p.id}" class="search-result-item">
            <div style="width:40px;height:40px;background:var(--primary-bg);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-box-seam" style="color:var(--primary)"></i>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:0.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${p.short_label || p.full_name}</div>
                <div style="font-size:0.7rem;color:var(--text-muted)">${p.brand_name || ''} · ${p.category_name || ''}</div>
            </div>
        </a>
    `).join('');
}

// Header hide/show on scroll
function initHeaderScroll() {
    let lastScroll = 0;
    const header = document.getElementById('appHeader');
    if (!header) return;

    window.addEventListener('scroll', () => {
        const current = window.pageYOffset;
        if (current > lastScroll && current > 100) {
            header.style.transform = 'translateY(-100%)';
        } else {
            header.style.transform = 'translateY(0)';
        }
        lastScroll = current;
    }, { passive: true });
}

// ==========================================
// UNSAVED CHANGES TRACKER
// ==========================================
window.hasUnsavedChanges = false;
window.setUnsavedChanges = function(state) {
    window.hasUnsavedChanges = state;
};

// Listen to input changes in any form to set the flag
document.addEventListener('input', function(e) {
    if (!e || !e.target) return;
    const target = e.target.nodeType === 1 ? e.target : (e.target ? e.target.parentElement : null);
    if (target && typeof target.closest === 'function' && target.closest('form') && !target.classList.contains('no-track')) {
        window.hasUnsavedChanges = true;
    }
});

// Reset flag on any form submit to prevent blocking the normal save flow
document.addEventListener('submit', function(e) {
    window.hasUnsavedChanges = false;
});

// Intercept clicks on links
document.addEventListener('click', function(e) {
    if (!e || !e.target) return;
    const target = e.target.nodeType === 1 ? e.target : (e.target ? e.target.parentElement : null);
    if (!target || typeof target.closest !== 'function') return;
    const a = target.closest('a');
    // Only intercept if we have unsaved changes and it's a real navigation link
    if (a && a.href && !a.target && window.hasUnsavedChanges) {
        const href = a.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
            e.preventDefault();
            
            if (typeof AppModal !== 'undefined') {
                AppModal.show({
                    title: 'Konfirmasi Keluar',
                    subtitle: 'Ada input yang belum disimpan. Jika Anda keluar, semua inputan akan terhapus. Yakin ingin keluar?',
                    icon: 'bi-exclamation-triangle',
                    iconColor: 'var(--warning-bg)',
                    iconAccent: 'var(--warning)',
                    submitText: 'Ya, Keluar',
                    cancelText: 'Tidak, Tetap di Sini',
                    onSubmit: () => {
                        window.hasUnsavedChanges = false;
                        window.location.href = a.href;
                    }
                });
            } else {
                if (confirm('Ada input yang belum disimpan. Semua inputan akan hilang. Yakin ingin keluar?')) {
                    window.hasUnsavedChanges = false;
                    window.location.href = a.href;
                }
            }
        }
    }
});

// For browser back button / refresh
window.addEventListener('beforeunload', function (e) {
    if (window.hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// ==========================================
// OFFLINE SYNC MANAGER
// ==========================================
async function updateSyncBadge() {
    if (typeof window.OfflineDB !== 'undefined') {
        try {
            const count = await window.OfflineDB.countPending();
            const badge = document.getElementById('offlineSyncBadge');
            if (badge) {
                if (count > 0) {
                    badge.style.display = 'flex';
                    badge.textContent = count;
                    const icon = document.getElementById('syncIcon');
                    if(icon) icon.style.color = 'var(--warning)';
                } else {
                    badge.style.display = 'none';
                    const icon = document.getElementById('syncIcon');
                    if(icon) icon.style.color = '';
                }
            }
            const modalCount = document.getElementById('syncPendingCount');
            if (modalCount) modalCount.textContent = count;
        } catch (e) {
            console.error(e);
        }
    }
}

async function syncPendingChanges() {
    if (!navigator.onLine) return;
    if (typeof window.OfflineDB === 'undefined') return;

    try {
        const changes = await window.OfflineDB.getPendingChanges();
        if (changes.length === 0) {
            updateSyncBadge();
            return;
        }

        showToast('Menyinkronkan data offline...', 'info');
        
        // Show spinning icon
        const syncIcon = document.getElementById('syncIcon');
        if (syncIcon) {
            syncIcon.classList.add('bi-arrow-repeat');
            syncIcon.style.animation = 'spin 1s linear infinite';
        }

        let successCount = 0;
        let failCount = 0;

        for (const change of changes) {
            const failKey = `sync_fail_${change.id}`;
            let retries = parseInt(localStorage.getItem(failKey) || '0');

            try {
                // Ensure endpoint is normalized using BASE_URL if relative
                let endpoint = change.endpoint;
                if (!endpoint.startsWith('http://') && !endpoint.startsWith('https://')) {
                    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
                    if (endpoint.startsWith('/')) {
                        endpoint = baseUrl.replace(/\/+$/, '') + endpoint;
                    } else {
                        endpoint = baseUrl + endpoint;
                    }
                }

                const config = {
                    method: change.method || 'POST',
                    headers: {}
                };
                
                // Prefer CSRF from stored payload, fallback to DOM
                const csrfFromPayload = change.payload && (change.payload.csrf_token || change.payload['X-CSRF-Token']);
                const csrfFromDom = document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '';
                const csrfToken = csrfFromPayload || csrfFromDom || '';
                if (csrfToken) config.headers['X-CSRF-Token'] = csrfToken;
                
                if (change.payload) {
                    config.headers['Content-Type'] = 'application/json';
                    config.body = JSON.stringify(change.payload);
                }

                const response = await fetch(endpoint, config);
                const status = response.status;

                let isSuccess = response.ok;
                let errorMsg = '';

                try {
                    const resText = await response.text();
                    if (resText && resText.trim().length > 0) {
                        const json = JSON.parse(resText);
                        if (json && json.success === false) {
                            isSuccess = false;
                            errorMsg = json.error || 'Server error';
                        }
                    }
                } catch(pe) {
                    // Ignored JSON parse error
                }

                if (isSuccess) {
                    await window.OfflineDB.removePendingChange(change.id);
                    localStorage.removeItem(failKey);
                    successCount++;
                } else {
                    retries += 1;
                    localStorage.setItem(failKey, retries);

                    // Client errors (4xx) OR items failed >= 3 times: drop permanently to prevent clog
                    if ((status >= 400 && status < 500) || retries >= 3) {
                        await window.OfflineDB.removePendingChange(change.id);
                        localStorage.removeItem(failKey);
                        console.warn(`Menghapus antrian ID ${change.id} (${status >= 400 && status < 500 ? 'Client Error HTTP ' + status : 'Gagal 3x (' + errorMsg + ')'})`);
                    } else {
                        failCount++;
                        console.error(`Gagal sinkron data ID: ${change.id} (percobaan ${retries}) HTTP ${status} — akan dicoba lagi`);
                    }
                }
            } catch (e) {
                console.error("Error saat sinkron data ID:", change.id, e);
                retries += 1;
                localStorage.setItem(failKey, retries);

                if (retries >= 3) {
                    await window.OfflineDB.removePendingChange(change.id);
                    localStorage.removeItem(failKey);
                    console.warn(`Menghapus antrian ID ${change.id} setelah ${retries}x percobaan exception`);
                } else {
                    failCount++;
                }
            }
            updateSyncBadge(); // Update UI badge live
        }

        updateSyncBadge();
        if (syncIcon) syncIcon.style.animation = '';

        if (failCount === 0) {
            showToast(`Sinkronisasi selesai (${successCount} data)`, 'success');
            if (typeof OfflineDB.syncAllDataFromServer === 'function') {
                OfflineDB.syncAllDataFromServer().catch(e => console.log('Gagal update cache master', e));
            } else if (typeof OfflineDB.syncProductsFromServer === 'function') {
                OfflineDB.syncProductsFromServer().catch(e => console.log('Gagal update cache produk', e));
            }
        } else {
            showToast(`Sinkronisasi selesai dengan ${failCount} gagal`, 'warning');
        }
    } catch (e) {
        console.error("Sync process failed", e);
        updateSyncBadge();
    }
}

// ==========================================
// SYNC SETTINGS MODAL & LONG PRESS LOGIC
// ==========================================
let syncSettingsTimer = null;

function startSyncSettingsTimer(e) {
    syncSettingsTimer = setTimeout(() => {
        openSyncSettings(e);
    }, 600);
}

function clearSyncSettingsTimer() {
    if (syncSettingsTimer) clearTimeout(syncSettingsTimer);
}

async function openSyncSettings(e) {
    if (e) e.preventDefault();
    clearSyncSettingsTimer();
    
    // Set toggle state
    const autoSync = localStorage.getItem('alfarezmart_sync_mode') !== 'manual';
    document.getElementById('autoSyncToggle').checked = autoSync;
    
    // Get counts
    if (typeof OfflineDB !== 'undefined') {
        try {
            const pendingCount = await OfflineDB.countPending();
            document.getElementById('syncPendingCount').textContent = pendingCount;
            
            const allProducts = await OfflineDB.getAllProducts();
            document.getElementById('syncCachedCount').textContent = allProducts ? allProducts.length : 0;
        } catch(e) {
            console.error('Gagal mengambil stat offline', e);
        }
    }
    
    const modal = new bootstrap.Modal(document.getElementById('syncSettingsModal'));
    modal.show();
}

function toggleAutoSync(isAuto) {
    if (isAuto) {
        localStorage.setItem('alfarezmart_sync_mode', 'auto');
        showToast('Sinkronisasi otomatis diaktifkan', 'success');
    } else {
        localStorage.setItem('alfarezmart_sync_mode', 'manual');
        showToast('Sinkronisasi manual diaktifkan', 'info');
    }
}

function forceManualSync() {
    const modalEl = document.getElementById('syncSettingsModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
    
    triggerSync();
}

async function clearPendingQueue() {
    if (typeof OfflineDB === 'undefined') return;
    try {
        await OfflineDB.clearPending();
        Object.keys(localStorage).forEach(key => {
            if (key.startsWith('sync_fail_')) localStorage.removeItem(key);
        });
        await updateSyncBadge();
        const pendingEl = document.getElementById('syncPendingCount');
        if (pendingEl) pendingEl.textContent = '0';
        
        const modalEl = document.getElementById('syncSettingsModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
        showToast('Antrian offline berhasil dibersihkan & badge di-reset', 'success');
    } catch(e) {
        console.error('Gagal membersihkan antrian offline:', e);
        showToast('Gagal membersihkan antrian offline', 'error');
    }
}

// ==========================================
// OFFLINE DETAIL MODALS
// ==========================================
document.addEventListener('click', function(e) {
    if (!e || !e.target) return;
    const target = e.target.nodeType === 1 ? e.target : (e.target ? e.target.parentElement : null);
    if (!target || typeof target.closest !== 'function') return;
    const a = target.closest('a');
    if (a && a.href && !a.target && !navigator.onLine) {
        const href = a.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
            const url = new URL(a.href);
            const path = url.pathname;
            
            const productMatch = path.match(/\/products\/(\d+)(?:\/edit)?$/);
            if (productMatch) {
                e.preventDefault();
                showOfflineProductDetail(productMatch[1]);
                return;
            }

            const saleMatch = path.match(/\/sales\/(\d+)$/);
            if (saleMatch) {
                e.preventDefault();
                showOfflineSaleDetail(saleMatch[1]);
                return;
            }

            const suppMatch = path.match(/\/suppliers\/(\d+)$/);
            if (suppMatch) {
                e.preventDefault();
                showOfflineSupplierDetail(suppMatch[1]);
                return;
            }
        }
    }
});

async function showOfflineProductDetail(id) {
    if (typeof OfflineDB === 'undefined' || typeof AppModal === 'undefined') return;
    try {
        const p = await OfflineDB.getProductById(id);
        if (!p) {
            showToast('Data produk tidak ditemukan di penyimpanan offline.', 'error');
            return;
        }

        let packagingsHtml = '';
        if (p.packagings && p.packagings.length > 0) {
            packagingsHtml = p.packagings.map(pkg => `
                <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:8px 12px;margin-bottom:8px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-weight:600;font-size:var(--font-size-sm);">${pkg.unit_name} (Level ${pkg.level})</span>
                        <span style="color:var(--text-muted);font-size:var(--font-size-xs);">Isi: ${pkg.base_qty || 1}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-end;">
                        <div style="font-size:var(--font-size-xs);">
                            <div style="color:var(--text-muted);margin-bottom:2px;">Beli: ${formatRupiah(pkg.buy_price || 0)}</div>
                            <div style="color:var(--text-primary);">
                                Ecer: <span style="color:var(--primary);font-weight:600;">${formatRupiah(pkg.sell_price_retail || 0)}</span>
                            </div>
                            <div style="color:var(--text-primary);">
                                Grosir: <span style="color:var(--warning);font-weight:600;">${formatRupiah(pkg.sell_price_wholesale || 0)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            packagingsHtml = '<div style="color:var(--text-muted);font-size:var(--font-size-sm);text-align:center;">Belum ada kemasan</div>';
        }

        AppModal.show({
            title: p.full_name || p.short_label,
            subtitle: `${p.code || '-'} • ${p.brand_name || '-'} • ${p.category_name || '-'}`,
            icon: 'bi-box-seam',
            iconColor: 'var(--primary-bg)',
            iconAccent: 'var(--primary)',
            hideFooter: true,
            bodyHTML: `
                <div style="margin-bottom:16px;">
                    <span class="badge-custom badge-warning" style="margin-bottom:12px;"><i class="bi bi-wifi-off"></i> Mode Offline</span>
                </div>
                <h4 style="font-size:var(--font-size-sm);margin-bottom:8px;color:var(--text-primary);">Harga & Kemasan</h4>
                ${packagingsHtml}
                <div style="margin-top:16px;text-align:center;font-size:11px;color:var(--text-muted);">
                    Data diambil dari penyimpanan lokal perangkat Anda.
                </div>
            `
        });
    } catch (e) {
        showToast('Gagal memuat data produk offline.', 'error');
        console.error(e);
    }
}

async function showOfflineSaleDetail(id) {
    if (typeof OfflineDB === 'undefined' || typeof AppModal === 'undefined') return;
    try {
        const sales = await OfflineDB.getAllSales();
        const s = sales.find(x => x.id == id);
        if (!s) {
            showToast('Data penjualan tidak ditemukan di penyimpanan offline.', 'error');
            return;
        }

        AppModal.show({
            title: s.invoice_number,
            subtitle: `${formatDate(s.created_at)} • ${s.sale_mode === 'retail' ? 'Ecer' : 'Grosir'}`,
            icon: 'bi-receipt',
            iconColor: 'var(--success-bg)',
            iconAccent: 'var(--success)',
            hideFooter: true,
            bodyHTML: `
                <div style="margin-bottom:16px;">
                    <span class="badge-custom badge-warning" style="margin-bottom:12px;"><i class="bi bi-wifi-off"></i> Mode Offline</span>
                </div>
                <table class="table-custom" style="width:100%;margin-bottom:16px;font-size:var(--font-size-sm);">
                    <tr><td style="color:var(--text-muted);padding:8px 0;">Pelanggan</td><td style="text-align:right;font-weight:600;">${s.customer_name || 'Pelanggan Umum'}</td></tr>
                    <tr><td style="color:var(--text-muted);padding:8px 0;">Status</td><td style="text-align:right;">${s.payment_status}</td></tr>
                    <tr><td style="color:var(--text-muted);padding:8px 0;">Total Item</td><td style="text-align:right;">${s.total_items || '-'}</td></tr>
                    <tr><td style="color:var(--text-muted);padding:8px 0;font-weight:700;">Total Bayar</td><td style="text-align:right;font-weight:700;color:var(--primary);">${formatRupiah(s.total_amount)}</td></tr>
                </table>
                <div style="margin-top:16px;text-align:center;font-size:11px;color:var(--text-muted);">
                    Untuk melihat rincian item barang, harap aktifkan internet kembali.
                </div>
            `
        });
    } catch (e) {
        showToast('Gagal memuat data penjualan offline.', 'error');
        console.error(e);
    }
}

async function showOfflineSupplierDetail(id) {
    if (typeof OfflineDB === 'undefined' || typeof AppModal === 'undefined') return;
    try {
        const suppliers = await OfflineDB.getAllSuppliers();
        const s = suppliers.find(x => x.id == id);
        if (!s) {
            showToast('Data supplier tidak ditemukan di penyimpanan offline.', 'error');
            return;
        }

        AppModal.show({
            title: s.name,
            subtitle: s.type_name || 'Supplier',
            icon: 'bi-building',
            iconColor: 'var(--info-bg)',
            iconAccent: 'var(--info)',
            hideFooter: true,
            bodyHTML: `
                <div style="margin-bottom:16px;">
                    <span class="badge-custom badge-warning" style="margin-bottom:12px;"><i class="bi bi-wifi-off"></i> Mode Offline</span>
                </div>
                <div style="background:var(--surface-1);border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:12px;margin-bottom:16px;font-size:var(--font-size-sm);">
                    <div style="margin-bottom:8px;"><i class="bi bi-telephone" style="color:var(--text-muted);margin-right:8px;"></i> ${s.phone || '-'}</div>
                    <div style="margin-bottom:8px;"><i class="bi bi-envelope" style="color:var(--text-muted);margin-right:8px;"></i> ${s.email || '-'}</div>
                    <div><i class="bi bi-geo-alt" style="color:var(--text-muted);margin-right:8px;"></i> ${s.address || '-'}</div>
                </div>
                <div style="margin-top:16px;text-align:center;font-size:11px;color:var(--text-muted);">
                    Data diambil dari penyimpanan lokal perangkat Anda.
                </div>
            `
        });
    } catch (e) {
        showToast('Gagal memuat data supplier offline.', 'error');
        console.error(e);
    }
}

// Pull to Refresh Implementation
function initPullToRefresh() {
    let touchStartY = 0;
    let touchMoveY = 0;
    let isPulling = false;
    let isRefreshing = false;
    const ptrIndicator = document.getElementById('ptr-indicator');
    const ptrIcon = document.getElementById('ptr-icon');
    const ptrSpinner = document.getElementById('ptr-spinner');
    if (!ptrIndicator) return;

    const pullThreshold = 70; // Distance needed to trigger refresh
    const maxPull = 120;

    document.addEventListener('touchstart', (e) => {
        if (window.scrollY === 0 && !isRefreshing) {
            touchStartY = e.touches[0].clientY;
            isPulling = true;
            ptrIndicator.style.transition = 'none';
        }
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        if (!isPulling || isRefreshing) return;
        touchMoveY = e.touches[0].clientY;
        const pullDist = touchMoveY - touchStartY;

        // Only handle pull down, not push up
        if (pullDist > 0 && window.scrollY === 0) {
            // Prevent default scroll behavior
            if (e.cancelable) e.preventDefault();
            
            // Add resistance
            const resistanceDist = pullDist < maxPull ? pullDist : maxPull + (pullDist - maxPull) * 0.2;
            
            ptrIndicator.style.opacity = Math.min(resistanceDist / pullThreshold, 1);
            ptrIndicator.style.transform = `translateY(${resistanceDist}px)`;

            if (resistanceDist > pullThreshold) {
                ptrIcon.style.transform = 'rotate(180deg)';
            } else {
                ptrIcon.style.transform = 'rotate(0deg)';
            }
        }
    }, { passive: false });

    document.addEventListener('touchend', (e) => {
        if (!isPulling || isRefreshing) return;
        isPulling = false;
        const pullDist = touchMoveY - touchStartY;

        ptrIndicator.style.transition = 'transform 0.3s ease, opacity 0.3s ease';

        if (pullDist > pullThreshold && window.scrollY === 0) {
            // Trigger refresh
            isRefreshing = true;
            ptrIcon.style.display = 'none';
            ptrSpinner.style.display = 'inline-block';
            ptrIndicator.style.transform = `translateY(${pullThreshold}px)`;
            
            // Reload page or sync
            setTimeout(() => {
                window.location.reload(true);
            }, 600);
        } else {
            // Reset
            ptrIndicator.style.transform = 'translateY(0)';
            ptrIndicator.style.opacity = 0;
            setTimeout(() => {
                ptrIcon.style.transform = 'rotate(0deg)';
            }, 300);
        }
        touchStartY = 0;
        touchMoveY = 0;
    });
}
