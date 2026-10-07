const CACHE_NAME = 'nacoc-ims-cache-v2';
const ASSETS_TO_CACHE = [
    'css/css2.css',
    'css/dashboard_theme.css',
    'js/jquery-3.7.1.min.js',
    'js/lucide.min.js',
    'js/apexcharts.js',
    'js/sweetalert2@11.js',
    'img/NACOC1.png',
    'img/cropped_circle_image.png',
    'offline.html'
];

// Install Event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[Service Worker] Pre-caching static assets');
                return Promise.allSettled(
                    ASSETS_TO_CACHE.map(asset => {
                        return cache.add(new URL(asset, self.location.origin).href).catch(err => {
                            console.warn('[Service Worker] Pre-cache warning for asset:', asset, err);
                        });
                    })
                );
            })
            .then(() => self.skipWaiting())
    );
});

// Activate Event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log('[Service Worker] Clearing old cache:', cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event
self.addEventListener('fetch', event => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    // Ignore non-http/https schemes (e.g. chrome-extension://)
    if (!event.request.url.startsWith('http://') && !event.request.url.startsWith('https://')) {
        return;
    }

    // If it's a page navigation request
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    // Return the cached offline page on network failure
                    return caches.match(new URL('offline.html', self.location.origin).href)
                        .then(offlineResponse => {
                            return offlineResponse || new Response('Offline', { status: 503, statusText: 'Offline' });
                        });
                })
        );
        return;
    }

    // Otherwise, check if the request matches cached assets
    event.respondWith(
        caches.match(event.request)
            .then(cachedResponse => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                
                // Fallback to network with error handling for network failures / aborted requests
                return fetch(event.request)
                    .then(networkResponse => {
                        return networkResponse;
                    })
                    .catch(error => {
                        // Return empty response on network failure instead of throwing unhandled promise rejection
                        return new Response('', { status: 503, statusText: 'Service Unavailable' });
                    });
            })
            .catch(() => {
                return new Response('', { status: 503, statusText: 'Service Unavailable' });
            })
    );
});
