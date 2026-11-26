const CACHE_NAME = 'farm-sjb-static-v1';
const STATIC_ASSETS = [
    '/css/bootstrap.css',
    '/css/custom.css',
    '/js/bootstrap.bundle.min.js',
    '/js/app.js',
    '/images/pwa-192.png',
    '/images/pwa-512.png',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys
                    .filter(key => key.startsWith('farm-sjb-static-') && key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const requestUrl = new URL(event.request.url);

    if (
        event.request.method !== 'GET'
        || requestUrl.origin !== self.location.origin
        || !STATIC_ASSETS.includes(requestUrl.pathname)
    ) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(cachedResponse => cachedResponse || fetch(event.request))
    );
});
