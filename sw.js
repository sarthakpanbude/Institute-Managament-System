const CACHE_NAME = 'dna-ims-v1';
const ASSETS = [
    '/',
    '/index.php',
    '/assets/css/style.css',
    '/assets/img/logo.png',
    'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS);
        })
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) return cachedResponse;
            return fetch(event.request).then((response) => {
                // Check if we should cache this (e.g. notes PDF)
                if (event.request.url.includes('/uploads/')) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            });
        }).catch(() => {
            if (event.request.headers.get('accept').includes('text/html')) {
                return caches.match('/index.php');
            }
        })
    );
});
