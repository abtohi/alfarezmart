/**
 * AlfarezMart PWA - Service Worker
 * Cache Strategy: Cache First for assets, Network First for API
 */
const CACHE_NAME = 'alfarezmart-v13.8';
const BASE_URL = self.location.pathname.replace('/sw.js', '/');
const STATIC_ASSETS = [
    BASE_URL,
    BASE_URL + 'sales',
    BASE_URL + 'sales/pos',
    BASE_URL + 'products',
    BASE_URL + 'products/create',
    BASE_URL + 'suppliers',
    BASE_URL + 'purchases',
    BASE_URL + 'purchases/create',
    BASE_URL + 'debts',
    BASE_URL + 'finance',
    BASE_URL + 'reports',
    BASE_URL + 'settings',
    BASE_URL + 'public/css/variables.css',
    BASE_URL + 'public/css/app.css',
    BASE_URL + 'public/css/components.css',
    BASE_URL + 'public/js/utils.js',
    BASE_URL + 'public/js/dexie.min.js',
    BASE_URL + 'public/js/db.js',
    BASE_URL + 'public/js/app.js',
    BASE_URL + 'public/js/chat.js',
    BASE_URL + 'chat',
    BASE_URL + 'manifest.json',
    BASE_URL + 'public/images/mobile_icon.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2?856008caa5eb66df68595e734e59580d',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff?856008caa5eb66df68595e734e59580d',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
];

// Install - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // Cache each asset individually so one failure doesn't stop the rest
            return Promise.all(
                STATIC_ASSETS.map(url => {
                    return fetch(url, { cache: 'no-cache', credentials: 'same-origin' })
                        .then(response => {
                            if (!response.ok && response.type !== 'opaque') {
                                throw new Error('Request failed for ' + url);
                            }
                            return cache.put(url, response);
                        })
                        .catch(err => console.log('Failed to cache:', url, err));
                })
            );
        })
    );
    self.skipWaiting();
});

// Activate - clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key)));
        })
    );
    self.clients.claim();
});

// Fetch - strategy based on request type
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    if (url.pathname.includes('/api/products/sync')) {
        event.respondWith(fetch(event.request, { cache: 'no-cache' }));
        return;
    }

    if (url.pathname.includes('/api/')) {
        event.respondWith(
            fetch(event.request, { cache: 'no-cache' }).then(response => {
                // Only cache successful responses (2xx) — never cache auth errors (401/403)
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            }).catch(() => {
                return caches.match(event.request).then(cached => {
                    if (cached) return cached;
                    // Return a proper JSON offline response — never null
                    return new Response(
                        JSON.stringify({ offline: true, error: 'Offline', data: [] }),
                        { status: 503, headers: { 'Content-Type': 'application/json' } }
                    );
                });
            })
        );
        return;
    }

    // PENTING: Halaman auth (login/logout) TIDAK BOLEH di-cache karena mengandung CSRF token
    // Selalu ambil dari network agar token selalu fresh dan tidak expired
    const authPaths = ['/login', '/logout', '/register'];
    const isAuthPage = authPaths.some(p => url.pathname === BASE_URL.replace(/\/$/, '') + p || url.pathname.endsWith(p));
    if (isAuthPage) {
        event.respondWith(fetch(event.request, { cache: 'no-cache', credentials: 'same-origin' }));
        return;
    }

    if (event.request.destination === 'style' || event.request.destination === 'script' || event.request.destination === 'image' || event.request.destination === 'font') {
        event.respondWith(
            caches.match(event.request, { ignoreSearch: true }).then(cached => {
                return cached || fetch(event.request, { cache: 'no-cache' }).then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                });
            })
        );
        return;
    }

    // Default for HTML/Navigation requests: Network First with Timeout (800ms)
    event.respondWith(
        new Promise((resolve) => {
            let isResolved = false;
            const timeoutId = setTimeout(() => {
                if (!isResolved) {
                    caches.match(event.request, { ignoreSearch: true }).then(cached => {
                        if (cached) {
                            isResolved = true;
                            resolve(cached);
                        }
                    });
                }
            }, 800); // 800ms timeout for weak signal fast fallback

            fetch(event.request, { cache: 'no-cache' })
                .then(response => {
                    clearTimeout(timeoutId);
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    if (!isResolved) {
                        isResolved = true;
                        resolve(response);
                    }
                })
                .catch(() => {
                    clearTimeout(timeoutId);
                    if (!isResolved) {
                        isResolved = true;
                        
                            caches.match(event.request, { ignoreSearch: true }).then(cached => {
                                if (cached) {
                                    resolve(cached);
                                } else {
                                    const urlObj = new URL(event.request.url);
                                    
                                    // Helper function to resolve with base if everything fails
                                    const fallbackToBase = () => {
                                        caches.match(BASE_URL).then(baseCached => {
                                            resolve(baseCached || new Response('<html><body><h1>Offline</h1><p>Mohon periksa koneksi internet Anda.</p></body></html>', { 
                                                status: 200, 
                                                headers: {'Content-Type': 'text/html'} 
                                            }));
                                        });
                                    };

                                    if (urlObj.pathname.endsWith('/') && urlObj.pathname.length > BASE_URL.length) {
                                        // Try without trailing slash
                                        urlObj.pathname = urlObj.pathname.slice(0, -1);
                                        caches.match(urlObj.href, { ignoreSearch: true }).then(cachedNoSlash => {
                                            if (cachedNoSlash) resolve(cachedNoSlash);
                                            else fallbackToBase();
                                        });
                                    } else {
                                        // Try with trailing slash
                                        const origPathname = urlObj.pathname;
                                        urlObj.pathname = origPathname + '/';
                                        caches.match(urlObj.href, { ignoreSearch: true }).then(cachedWithSlash => {
                                            if (cachedWithSlash) resolve(cachedWithSlash);
                                            else fallbackToBase();
                                        });
                                    }
                                }
                            });
                    }
                });
        })
    );
});
