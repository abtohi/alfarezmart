/**
 * AlfarezMart PWA - Service Worker v44.0
 * Cache Strategy:
 * - CSS/JS versioned assets: Cache First with EXACT URL match & safe offline fallback
 * - Navigation / HTML: Fast Network Race (350ms Timeout) with Stale-While-Revalidate
 * - API GET: Network First with Fast Fallback to Cache
 *
 * IMPORTANT: Versioned JS/CSS files (e.g. app.js?v=X.Y) are NOT in STATIC_ASSETS.
 * They are cached on first request via the Cache-First fetch handler.
 * This prevents the old unversioned cache entry from being served for new versioned URLs.
 */
const CACHE_NAME = 'alfarezmart-cache-v45.0';
const DYNAMIC_CACHE = 'alfarezmart-dynamic-v45.0';
const BASE_URL = self.location.pathname.replace('/sw.js', '/');
const STATIC_ASSETS = [
    // Static app shell assets only — dynamic PHP pages are cached at runtime upon navigation
    BASE_URL + 'manifest.json',
    BASE_URL + 'public/images/mobile_icon.png',
    BASE_URL + 'public/images/mobile_icon_192.png',
    BASE_URL + 'public/images/mobile_icon_512.png',
    BASE_URL + 'public/images/Icon.png',
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
            return Promise.all(
                STATIC_ASSETS.map(url => {
                    return fetch(url, { cache: 'no-cache', credentials: 'same-origin' })
                        .then(response => {
                            if (!response.ok && response.type !== 'opaque') {
                                throw new Error('Request failed for ' + url);
                            }
                            return cache.put(url, response).catch(e => console.warn('Cache.put failed for', url, e));
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
    const keepCaches = [CACHE_NAME, DYNAMIC_CACHE];
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys.filter(key => !keepCaches.includes(key)).map(key => caches.delete(key)));
        })
    );
    self.clients.claim();
});

// Fetch - strategy based on request type
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Bypass API products sync and CSRF from SW interception
    if (url.pathname.includes('/api/products/sync') || url.pathname.includes('/api/csrf-token')) {
        event.respondWith(
            fetch(event.request, { cache: 'no-cache', credentials: 'same-origin' }).catch(() => {
                return new Response(
                    JSON.stringify({ offline: true, error: 'Offline' }),
                    { status: 200, headers: { 'Content-Type': 'application/json' } }
                );
            })
        );
        return;
    }

    // ── 1. API Requests: Network First with Fast Cache Fallback ──
    if (url.pathname.includes('/api/')) {
        event.respondWith(
            fetch(event.request, { cache: 'no-cache', credentials: 'same-origin' })
                .then(response => {
                    if (response && response.ok && event.request.method === 'GET') {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone)).catch(() => {});
                    }
                    return response;
                })
                .catch(async () => {
                    const cached = await caches.match(event.request);
                    if (cached) return cached;
                    return new Response(
                        JSON.stringify({ success: false, offline: true, error: 'Offline', data: [] }),
                        { status: 200, headers: { 'Content-Type': 'application/json' } }
                    );
                })
        );
        return;
    }

    // ── 2. Auth & Critical Live Pages: Always Network First for CSRF Freshness ──
    const liveFreshPages = ['/login', '/logout', '/register', '/sales/pos', '/purchases/create', '/products/create'];
    const isLiveFreshPage = liveFreshPages.some(p => url.pathname === BASE_URL.replace(/\/$/, '') + p || url.pathname.endsWith(p));
    if (isLiveFreshPage) {
        event.respondWith(
            fetch(event.request, { cache: 'no-cache', credentials: 'same-origin' })
                .then(response => {
                    if (response && response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone)).catch(() => {});
                    }
                    return response;
                })
                .catch(async () => {
                    const cached = await caches.match(event.request, { ignoreSearch: true });
                    if (cached) return cached;
                    return new Response('Offline: Halaman belum tersedia di cache', { status: 503, headers: { 'Content-Type': 'text/plain' } });
                })
        );
        return;
    }

    // ── 3. Images: Cache First with Dynamic Fallback ──
    const isImage = event.request.destination === 'image' || 
                    url.pathname.includes('/uploads/') || 
                    url.pathname.match(/\.(jpg|jpeg|png|webp|gif|svg|ico)($|\?)/i);

    if (isImage) {
        event.respondWith(
            caches.match(event.request, { ignoreSearch: true }).then(cached => {
                if (cached) return cached;

                return fetch(event.request, { cache: 'no-cache' }).then(response => {
                    if (!response || (response.status !== 200 && response.type !== 'opaque') || !event.request.url.startsWith('http')) {
                        return response;
                    }
                    const clone = response.clone();
                    caches.open(DYNAMIC_CACHE).then(cache => cache.put(event.request, clone).catch(() => {}));
                    return response;
                }).catch(() => {
                    return new Response('', { status: 404, statusText: 'Image Offline' });
                });
            }).catch(() => {
                return new Response('', { status: 404, statusText: 'Image Offline' });
            })
        );
        return;
    }

    // ── 4. Styles, Scripts, Fonts: Cache First with Safe Catch Fallback ──
    if (event.request.destination === 'style' || event.request.destination === 'script' || event.request.destination === 'font') {
        event.respondWith(
            caches.match(event.request).then(cached => {
                if (cached) return cached;
                return fetch(event.request, { cache: 'no-cache' }).then(response => {
                    if (!response || response.status !== 200 || response.type === 'opaque' || !event.request.url.startsWith('http')) {
                        return response;
                    }
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone).catch(() => {}));
                    return response;
                }).catch(async () => {
                    // Safe fallback: search cache ignoring query string (?v=...)
                    const fallback = await caches.match(event.request, { ignoreSearch: true });
                    if (fallback) return fallback;
                    const mime = event.request.destination === 'style' ? 'text/css' :
                                 event.request.destination === 'script' ? 'application/javascript' : 'font/woff2';
                    return new Response('', { status: 200, headers: { 'Content-Type': mime } });
                });
            }).catch(async () => {
                const fallback = await caches.match(event.request, { ignoreSearch: true });
                if (fallback) return fallback;
                return new Response('', { status: 200, headers: { 'Content-Type': 'text/plain' } });
            })
        );
        return;
    }

    // ── 5. HTML/Navigation Requests: Ultra-Fast Race Strategy (350ms) ──
    event.respondWith(
        caches.match(event.request, { ignoreSearch: true }).then(cachedResponse => {
            // Background fetcher helper
            const fetchPromise = fetch(event.request, { cache: 'no-cache' })
                .then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200 && networkResponse.type !== 'opaque' && event.request.url.startsWith('http')) {
                        const clone = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone)).catch(() => {});
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    if (cachedResponse) return cachedResponse;
                    const baseCached = await caches.match(BASE_URL);
                    if (baseCached) return baseCached;
                    return new Response('<html><head><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Offline - AlfarezMart</title></head><body style="background:#1a1a2e;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;"><div style="text-align:center;padding:24px;max-width:400px;"><h2>Mode Offline</h2><p style="color:#94a3b8;font-size:14px;">Halaman ini belum tersedia offline. Mohon periksa koneksi internet Anda.</p><button onclick="window.location.reload()" style="background:#e63946;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:bold;margin-top:12px;">Coba Lagi</button></div></body></html>', {
                        status: 200,
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    });
                });

            // If we have a cached version, give network only 350ms to respond, otherwise serve cache INSTANTLY
            if (cachedResponse) {
                return Promise.race([
                    fetchPromise,
                    new Promise(resolve => setTimeout(() => resolve(cachedResponse), 350))
                ]);
            }

            // Not in cache yet: await network fetch directly
            return fetchPromise;
        }).catch(async () => {
            const baseCached = await caches.match(BASE_URL);
            if (baseCached) return baseCached;
            return new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
        })
    );
});
