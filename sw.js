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
const CACHE_NAME = 'alfarezmart-cache-v45.8';
const DYNAMIC_CACHE = 'alfarezmart-dynamic-v45.8';
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

    // ── 2. Auth & Critical Live Pages: Always Network First for CSRF & Auth Freshness ──
    const liveFreshPages = ['/', '/login', '/logout', '/register', '/sales/pos', '/purchases/create', '/products/create'];
    const isLiveFreshPage = url.pathname === '/' || 
                            url.pathname === BASE_URL || 
                            url.pathname === BASE_URL.replace(/\/$/, '') ||
                            liveFreshPages.some(p => p !== '/' && (url.pathname === p || url.pathname === BASE_URL.replace(/\/$/, '') + p || url.pathname.endsWith(p)));
    if (isLiveFreshPage) {
        event.respondWith(
            fetch(event.request, { cache: 'no-cache', credentials: 'same-origin' })
                .then(response => {
                    if (response && response.ok && !response.redirected) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone)).catch(() => {});
                    }
                    return response;
                })
                .catch(async () => {
                    const cached = await caches.match(event.request, { ignoreSearch: true });
                    if (cached) return cached;
                    const baseCached = await caches.match(BASE_URL);
                    if (baseCached) return baseCached;
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
        caches.match(event.request, { ignoreSearch: true }).then(async cachedResponse => {
            // Background fetcher helper
            const fetchPromise = fetch(event.request, { cache: 'no-cache', credentials: 'same-origin' })
                .then(networkResponse => {
                    if (networkResponse && networkResponse.status === 200 && networkResponse.type !== 'opaque' && !networkResponse.redirected && event.request.url.startsWith('http')) {
                        const clone = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone)).catch(() => {});
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    if (cachedResponse) return cachedResponse;
                    
                    // Fallback to other available cached pages in order of priority
                    const fallbackUrls = [
                        BASE_URL,
                        BASE_URL + 'sales/pos',
                        BASE_URL + 'products',
                        BASE_URL + 'settings/error-logs'
                    ];
                    for (const fUrl of fallbackUrls) {
                        const hit = await caches.match(fUrl, { ignoreSearch: true });
                        if (hit) return hit;
                    }

                    // Interactive Offline Screen with Direct Links
                    const offlineHtml = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mode Offline - AlfarezMart</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f172a; color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card { background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 32px 24px; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; margin-bottom: 16px; }
        h1 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        p { color: #94a3b8; font-size: 13px; line-height: 1.5; margin-bottom: 24px; }
        .btn-group { display: flex; flex-direction: column; gap: 10px; }
        .btn { display: block; width: 100%; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; text-align: center; cursor: pointer; transition: 0.2s ease; border: none; }
        .btn-primary { background: #ef4444; color: #fff; }
        .btn-primary:hover { background: #dc2626; }
        .btn-outline { background: #334155; color: #f8fafc; border: 1px solid rgba(255,255,255,0.1); }
        .btn-outline:hover { background: #475569; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">📡 Sedang Offline</div>
        <h1>Halaman Belum Tersedia Offline</h1>
        <p>Halaman ini belum sempat di-cache ke memori perangkat. Silakan buka menu offline yang siap digunakan di bawah:</p>
        <div class="btn-group">
            <a href="${BASE_URL}sales/pos" class="btn btn-primary">🛒 Buka Kasir POS</a>
            <a href="${BASE_URL}scanner" class="btn btn-outline">🔍 Cek Harga / Scan</a>
            <a href="${BASE_URL}products" class="btn btn-outline">📦 Katalog Produk</a>
            <a href="${BASE_URL}settings/error-logs" class="btn btn-outline">🐞 Error Log Catcher</a>
            <button onclick="window.location.reload()" class="btn btn-outline" style="margin-top:6px; color:#94a3b8;">🔄 Coba Muat Ulang</button>
        </div>
    </div>
</body>
</html>`;
                    return new Response(offlineHtml, {
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
