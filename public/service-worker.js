self.addEventListener("install", event => {
    event.waitUntil(
      caches.open("reception-cache-v2").then(cache => {
        return cache.addAll([
          "/",
          "/manifest.json",
          "/192.png",
          "/512.png",
        ]);
      })
    );
  });
  
  self.addEventListener("fetch", event => {
    event.respondWith(
      caches.match(event.request).then(response => {
        return response || fetch(event.request);
      })
    );
  });
  