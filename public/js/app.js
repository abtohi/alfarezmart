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

document.addEventListener('DOMContentLoaded', () => {
    initSearch();
    initHeaderScroll();
});

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
                const data = await api(`${BASE_URL}api/products/search?q=${encodeURIComponent(q)}`);
                if (data.length === 0) {
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
        } catch (e) {
            results.innerHTML = '<div class="empty-state" style="padding:24px"><p>Gagal mencari</p></div>';
        }
    }, 300));
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
