/**
 * AlfarezMart PWA - Service Worker
 * Cache Strategy: Cache First for assets, Network First for API
 */
const CACHE_NAME = 'alfarezmart-v9.8';
const BASE_URL = self.location.pathname.replace('/public/sw.js', '/');
const STATIC_ASSETS = [
    BASE_URL,
    BASE_URL + 'dashboard',
    BASE_URL + 'products',
    BASE_URL + 'sales',
    BASE_URL + 'sales/pos',
    BASE_URL + 'purchases',
    BASE_URL + 'suppliers',
    BASE_URL + 'finance',
    BASE_URL + 'debts',
    BASE_URL + 'reports',
    BASE_URL + 'public/css/variables.css',
    BASE_URL + 'public/css/app.css',
    BASE_URL + 'public/css/components.css',
    BASE_URL + 'public/js/utils.js',
    BASE_URL + 'public/js/app.js',
    BASE_URL + 'public/js/dexie.min.js',
    BASE_URL + 'public/js/db.js',
    BASE_URL + 'public/js/components.js',
    BASE_URL + 'public/js/xlsx.full.min.js',
    BASE_URL + 'public/manifest.json',
    BASE_URL + 'public/images/mobile_icon.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
];

// Install - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(err => console.log('Cache addAll error:', err));
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
    // Ignore non-GET requests (POST, PUT, DELETE shouldn't be cached)
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    
    // API requests: Network First
    if (url.pathname.includes('/api/')) {
        event.respondWith(
            fetch(event.request).then(response => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                return response;
            }).catch(() => caches.match(event.request))
        );
        return;
    }

    // Static assets: Cache First
    if (event.request.destination === 'style' || event.request.destination === 'script' || event.request.destination === 'image' || event.request.destination === 'font') {
        event.respondWith(
            caches.match(event.request).then(cached => {
                return cached || fetch(event.request).then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                });
            })
        );
        return;
    }

    // HTML pages: Network First with smarter cache fallback
    event.respondWith(
        fetch(event.request).then(response => {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
            return response;
        }).catch(() => {
            // Coba exact match dulu
            return caches.match(event.request).then(cached => {
                if (cached) return cached;
                // Jika tidak ada (misal karena query params: /products?q=xyz), coba match URL tanpa query params
                const urlWithoutSearch = event.request.url.split('?')[0];
                return caches.match(urlWithoutSearch).then(cachedNoSearch => {
                    return cachedNoSearch || caches.match(BASE_URL);
                });
            });
        })
    );
});
