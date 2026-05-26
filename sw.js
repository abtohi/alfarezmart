/**
 * AlfarezMart PWA - Service Worker
 * Cache Strategy: Cache First for assets, Network First for API
 */
const CACHE_NAME = 'alfarezmart-v5.8';
const BASE_URL = self.location.pathname.replace('/sw.js', '/');
const STATIC_ASSETS = [
    BASE_URL,
    BASE_URL + 'public/css/variables.css',
    BASE_URL + 'public/css/app.css',
    BASE_URL + 'public/css/components.css',
    BASE_URL + 'public/js/utils.js',
    BASE_URL + 'public/js/offline-db.js',
    BASE_URL + 'public/js/app.js',
    BASE_URL + 'manifest.json',
    BASE_URL + 'public/images/mobile_icon.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
];

// Install - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // Bypass HTTP cache when installing to get freshest files
            const requests = STATIC_ASSETS.map(url => new Request(url, { cache: 'no-cache' }));
            return cache.addAll(requests).catch(err => console.log('Cache addAll error:', err));
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
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                return response;
            }).catch(() => caches.match(event.request))
        );
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
                    caches.match(event.request).then(cached => {
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
                        caches.match(event.request).then(cached => resolve(cached || caches.match(BASE_URL)));
                    }
                });
        })
    );
});
