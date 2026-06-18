const CACHE_NAME = 'nacoc-ims-cache-v1';
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
                return cache.addAll(ASSETS_TO_CACHE);
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

    const url = new URL(event.request.url);

    // If it's a page navigation request
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    // Return the cached offline page on network failure
                    return caches.match(new URL('offline.html', self.location.href).href);
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
                
                // Fallback to network
                return fetch(event.request).then(networkResponse => {
                    // Don't cache dynamic pages, API calls, or non-static files dynamically
                    return networkResponse;
                });
            })
    );
});
