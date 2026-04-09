/**
 * Service Worker - QLTB PWA
 *
 * Chiến lược cache:
 * 1. Cài đặt (install): cache các file tĩnh quan trọng (App Shell)
 * 2. Kích hoạt (activate): xoá cache cũ
 * 3. Fetch: trả cache nếu có, không thì fetch từ mạng; nếu offline → trang offline
 */

const CACHE_NAME = 'qltb-v1';

// Các file được cache khi cài đặt (App Shell)
const STATIC_ASSETS = [
    '/QLTB/public/',
    '/QLTB/public/offline.html',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
];

// ===== 1. INSTALL: Cache App Shell =====
self.addEventListener('install', (event) => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[SW] Caching static assets');
                // Dùng addAll nếu tất cả thành công, hoặc cache từng cái
                return Promise.allSettled(
                    STATIC_ASSETS.map(url => cache.add(url).catch(err => {
                        console.warn('[SW] Failed to cache:', url, err);
                    }))
                );
            })
            .then(() => self.skipWaiting()) // Kích hoạt ngay
    );
});

// ===== 2. ACTIVATE: Xoá cache cũ =====
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME) // Cache không phải hiện tại
                    .map(name => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => self.clients.claim()) // Kiểm soát tất cả tab ngay
    );
});

// ===== 3. FETCH: Xử lý request =====
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Bỏ qua các request không phải GET
    if (request.method !== 'GET') return;

    // Bỏ qua các request đến API bên ngoài không cache được
    if (url.origin !== location.origin &&
        !url.href.includes('cdn.jsdelivr.net') &&
        !url.href.includes('fonts.googleapis.com')) {
        return;
    }

    event.respondWith(
        caches.match(request).then(cachedResponse => {
            // Nếu có trong cache → trả về ngay
            if (cachedResponse) {
                return cachedResponse;
            }

            // Không có cache → fetch từ mạng
            return fetch(request)
                .then(networkResponse => {
                    // Chỉ cache response tốt (status 200)
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            // Cache các file tĩnh
                            if (request.url.includes('cdn.jsdelivr.net') ||
                                request.url.includes('fonts.googleapis.com') ||
                                request.url.includes('.css') ||
                                request.url.includes('.js')) {
                                cache.put(request, responseClone);
                            }
                        });
                    }
                    return networkResponse;
                })
                .catch(() => {
                    // Mạng lỗi: hiển thị trang offline cho navigation request
                    if (request.mode === 'navigate') {
                        return caches.match('/QLTB/public/offline.html');
                    }
                    // Với các request khác, trả về 503
                    return new Response('Offline', {
                        status: 503,
                        statusText: 'Service Unavailable',
                    });
                });
        })
    );
});
