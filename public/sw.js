// ALG3PL Service Worker v1.0
const CACHE_NAME = 'alg3pl-v1';
const STATIC_ASSETS = [
    '/manifest.json',
    '/css/alg.css',
];

// Install: cache static assets
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function(cache) {
            return cache.addAll(STATIC_ASSETS.filter(url => {
                // Only cache assets that exist; ignore failures
                return true;
            }));
        }).catch(function(err) {
            console.warn('[SW] Install cache failed (non-fatal):', err);
        })
    );
    self.skipWaiting();
});

// Activate: clean up old caches
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames
                    .filter(function(name) { return name !== CACHE_NAME; })
                    .map(function(name) { return caches.delete(name); })
            );
        })
    );
    self.clients.claim();
});

// Fetch: cache-first for static assets, network-first for admin pages
self.addEventListener('fetch', function(event) {
    var url = new URL(event.request.url);

    // Only handle same-origin GET requests
    if (event.request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Network-first for admin pages and API calls
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api')) {
        event.respondWith(
            fetch(event.request)
                .then(function(response) {
                    return response;
                })
                .catch(function() {
                    return caches.match(event.request);
                })
        );
        return;
    }

    // Cache-first for static assets (css, js, images, fonts)
    if (
        url.pathname.startsWith('/css/') ||
        url.pathname.startsWith('/js/') ||
        url.pathname.startsWith('/images/') ||
        url.pathname.startsWith('/fonts/') ||
        url.pathname === '/manifest.json' ||
        url.pathname === '/favicon.ico'
    ) {
        event.respondWith(
            caches.match(event.request).then(function(cached) {
                if (cached) {
                    return cached;
                }
                return fetch(event.request).then(function(response) {
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }
                    var responseToCache = response.clone();
                    caches.open(CACHE_NAME).then(function(cache) {
                        cache.put(event.request, responseToCache);
                    });
                    return response;
                });
            })
        );
        return;
    }

    // Default: network with cache fallback
    event.respondWith(
        fetch(event.request).catch(function() {
            return caches.match(event.request);
        })
    );
});
