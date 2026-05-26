/**
 * AlfarezMart PWA - Main App Logic
 */
let deferredInstallPrompt = null;

// Auto-login from localStorage if available
function checkAutoLogin() {
    const loggedIn = localStorage.getItem('alfarezmart_logged_in');
    const currentPage = window.location.pathname.includes('/login');
    
    if (loggedIn === 'true' && !currentPage) {
        // Already logged in, proceed
        return;
    }
    
    // If on login page but localStorage says logged in, redirect to dashboard
    if (loggedIn === 'true' && currentPage) {
        // Give server time to validate session, then redirect
        setTimeout(() => {
            fetch(`${BASE_URL}`, { credentials: 'same-origin' })
                .then(r => {
                    if (r.ok) window.location.href = BASE_URL;
                })
                .catch(() => {
                    // If fetch fails (offline), trust localStorage and load app
                    window.location.href = BASE_URL;
                });
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
    
    // Initialize Offline DB
    try {
        if (typeof OfflineDB !== 'undefined') {
            await OfflineDB.init();
            
            // Background sync if online
            if (navigator.onLine) {
                setTimeout(() => {
                    if (typeof OfflineDB.syncAllDataFromServer === 'function') {
                        OfflineDB.syncAllDataFromServer().catch(e => console.error('Background sync failed:', e));
                    } else if (typeof OfflineDB.syncProductsFromServer === 'function') {
                        OfflineDB.syncProductsFromServer().catch(e => console.error('Background sync failed:', e));
                    }
                }, 5000);
            }
        }
    } catch (e) {
        console.error('Offline DB init failed:', e);
    }
});

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
                showToast('Sedang sinkronisasi data master...', 'info');
                if (typeof OfflineDB.syncAllDataFromServer === 'function') {
                    await OfflineDB.syncAllDataFromServer();
                    showToast(`Berhasil sinkronisasi data master ke perangkat`, 'success');
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
                try {
                    const data = await api(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`);
                    renderProductSearch(data, results);
                } catch (apiErr) {
                    // Fallback to offline DB
                    if (typeof OfflineDB !== 'undefined') {
                        const data = await OfflineDB.searchProducts(q);
                        renderProductSearch(data, results);
                    } else {
                        throw apiErr;
                    }
                }
            }
        } catch (e) {
            results.innerHTML = '<div class="empty-state" style="padding:24px"><p>Gagal mencari (Offline)</p></div>';
        }
    }, 300));
}

function renderProductSearch(data, results) {
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
                <div style="font-size:0.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${p.full_name || p.short_label}</div>
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
    if (e.target.closest('form') && !e.target.classList.contains('no-track')) {
        window.hasUnsavedChanges = true;
    }
});

// Reset flag on any form submit to prevent blocking the normal save flow
document.addEventListener('submit', function(e) {
    window.hasUnsavedChanges = false;
});

// Intercept clicks on links
document.addEventListener('click', function(e) {
    const a = e.target.closest('a');
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
        if (changes.length === 0) return;

        showToast('Menyinkronkan data offline...', 'info');
        
        // Show spinning icon
        const syncIcon = document.getElementById('syncIcon');
        if (syncIcon) {
            syncIcon.classList.add('bi-arrow-repeat'); // Ensure icon type
            syncIcon.style.animation = 'spin 1s linear infinite';
        }

        let successCount = 0;
        let failCount = 0;

        for (const change of changes) {
            try {
                // Use standard fetch to bypass api() offline interception
                const config = {
                    method: change.method,
                    headers: {}
                };
                
                const csrfToken = document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '';
                if (csrfToken) config.headers['X-CSRF-Token'] = csrfToken;
                
                if (change.payload) {
                    config.headers['Content-Type'] = 'application/json';
                    config.body = JSON.stringify(change.payload);
                }

                const response = await fetch(change.endpoint, config);
                if (response.ok) {
                    await window.OfflineDB.removePendingChange(change.id);
                    successCount++;
                } else {
                    failCount++;
                    console.error("Gagal sinkron data ID:", change.id);
                }
            } catch (e) {
                console.error("Error saat sinkron data ID:", change.id, e);
                failCount++;
            }
        }

        updateSyncBadge();
        if (syncIcon) syncIcon.style.animation = '';

        if (failCount === 0) {
            showToast(`Sinkronisasi selesai (${successCount} data)`, 'success');
            // Jika ada fungsi syncProductsFromServer (master data produk), panggil juga
            if (typeof OfflineDB.syncAllDataFromServer === 'function') {
                OfflineDB.syncAllDataFromServer().catch(e => console.log('Gagal update cache master', e));
            } else if (typeof OfflineDB.syncProductsFromServer === 'function') {
                OfflineDB.syncProductsFromServer().catch(e => console.log('Gagal update cache produk', e));
            }
            // Refresh data on current page if applicable
            setTimeout(() => { window.location.reload(); }, 1500);
        } else {
            showToast(`Sinkronisasi selesai dengan ${failCount} gagal`, 'warning');
        }
    } catch (e) {
        console.error("Sync process failed", e);
    }
}

// Listen for connection changes
window.addEventListener('online', function() {
    document.getElementById('offlineBanner').style.display = 'none';
    if (localStorage.getItem('alfarezmart_sync_mode') !== 'manual') {
        showToast('Koneksi internet kembali. Menyinkronkan data...', 'success');
        syncPendingChanges();
    } else {
        showToast('Koneksi internet kembali. Sinkronisasi manual aktif.', 'info');
    }
});

window.addEventListener('offline', function() {
    document.getElementById('offlineBanner').style.display = 'block';
    showToast('Koneksi terputus. Beralih ke mode offline.', 'warning');
});

// Initial badge check
document.addEventListener('DOMContentLoaded', () => {
    // Inject spin keyframes if not exists
    if (!document.getElementById('spinKeyframes')) {
        const style = document.createElement('style');
        style.id = 'spinKeyframes';
        style.innerHTML = `@keyframes spin { 100% { transform: rotate(360deg); } }`;
        document.head.appendChild(style);
    }

    if (!navigator.onLine) {
        const banner = document.getElementById('offlineBanner');
        if (banner) banner.style.display = 'block';
    }

    if (window.OfflineDB) {
        window.OfflineDB.init().then(() => {
            updateSyncBadge();
            // Sync on load if online
            if (navigator.onLine && localStorage.getItem('alfarezmart_sync_mode') !== 'manual') {
                syncPendingChanges();
            }
        }).catch(e => console.error("DB Init failed on load", e));
    }
});

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
