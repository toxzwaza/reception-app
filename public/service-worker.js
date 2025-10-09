// service-worker.js
const CACHE_NAME = 'reception-cache-v' + new Date().getTime();
const urlsToCache = [
  '/',
  '/manifest.json',
  '/192.png',
  '/512.png',
];

// インストール時にキャッシュ登録
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
  self.skipWaiting(); // 即座に新SWを有効化
});

// 有効化時に古いキャッシュを削除
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.map(key => {
        if (key !== CACHE_NAME) {
          return caches.delete(key);
        }
      }))
    )
  );
  self.clients.claim(); // 全タブに即反映
});

// キャッシュまたはネットワークから取得
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => response || fetch(event.request))
  );
});
