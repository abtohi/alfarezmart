/**
 * AlfarezMart PWA - Service Worker (public/sw.js)
 */
const CACHE_NAME = 'alfarezmart-cache-v18.2';
const DYNAMIC_CACHE = 'alfarezmart-dynamic-v18.1';
const BASE_URL = self.location.pathname.replace('/public/sw.js', '/');
const STATIC_ASSETS = [
    BASE_URL,
    BASE_URL + 'sales',
    BASE_URL + 'sales/pos',
    BASE_URL + 'scanner',
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
    BASE_URL + 'public/css/desktop.css',
    BASE_URL + 'public/js/utils.js',
    BASE_URL + 'public/js/dexie.min.js',
    BASE_URL + 'public/js/db.js',
    BASE_URL + 'public/js/app.js',
    BASE_URL + 'public/js/desktop.js',
    BASE_URL + 'public/js/chat.js',
    BASE_URL + 'chat',
    BASE_URL + 'manifest.json',
    BASE_URL + 'public/images/mobile_icon.png',
    BASE_URL + 'public/images/mobile_icon_192.png',
    BASE_URL + 'public/images/mobile_icon_512.png',
    BASE_URL + 'public/images/splash_logo.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
];

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
                            return cache.put(url, response).catch(() => {});
                        })
                        .catch(err => console.log('Failed to cache:', url, err));
                })
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            const keepCaches = [CACHE_NAME, DYNAMIC_CACHE];
            return Promise.all(keys.filter(key => !keepCaches.includes(key)).map(key => caches.delete(key)));
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;
    const url = new URL(event.request.url);

    if (url.pathname.includes('/api/products/sync')) {
        event.respondWith(fetch(event.request, { cache: 'no-cache' }));
        return;
    }

    if (url.pathname.includes('/api/')) {
        event.respondWith(
            new Promise((resolve) => {
                let isResolved = false;
                const timeoutId = setTimeout(() => {
                    if (!isResolved) {
                        caches.match(event.request, { ignoreSearch: true }).then(cached => {
                            if (cached && !isResolved) {
                                isResolved = true;
                                resolve(cached);
                            }
                        });
                    }
                }, 600);

                fetch(event.request, { cache: 'no-cache' })
                    .then(response => {
                        clearTimeout(timeoutId);
                        if (response.ok) {
                            const clone = response.clone();
                            caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                        }
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
                                    resolve(new Response(
                                        JSON.stringify({ offline: true, error: 'Offline', data: [] }),
                                        { status: 503, headers: { 'Content-Type': 'application/json' } }
                                    ));
                                }
                            });
                        }
                    });
            })
        );
        return;
    }

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
                }).catch(() => new Response('', { status: 404, statusText: 'Image Offline' }));
            })
        );
        return;
    }

    if (event.request.destination === 'style' || event.request.destination === 'script' || event.request.destination === 'font') {
        event.respondWith(
            caches.match(event.request, { ignoreSearch: true }).then(cached => {
                return cached || fetch(event.request, { cache: 'no-cache' }).then(response => {
                    if (!response || response.status !== 200 || response.type === 'opaque' || !event.request.url.startsWith('http')) {
                        return response;
                    }
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone).catch(() => {}));
                    return response;
                });
            })
        );
        return;
    }

    event.respondWith(
        new Promise((resolve) => {
            let isResolved = false;
            const timeoutId = setTimeout(() => {
                if (!isResolved) {
                    caches.match(event.request, { ignoreSearch: true }).then(cached => {
                        if (cached && !isResolved) {
                            isResolved = true;
                            resolve(cached);
                        }
                    });
                }
            }, 600);

            fetch(event.request, { cache: 'no-cache' })
                .then(response => {
                    clearTimeout(timeoutId);
                    if (response && response.status === 200 && response.type !== 'opaque' && event.request.url.startsWith('http')) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone).catch(() => {}));
                    }
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
                            resolve(cached || caches.match(BASE_URL));
                        });
                    }
                });
        })
    );
});
