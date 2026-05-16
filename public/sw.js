const CACHE_NAME = 'proxmox-alert-v1';

// Instalación: No cacheamos nada agresivamente por ahora para evitar problemas de desarrollo
self.addEventListener('install', event => {
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(clients.claim());
});

// Estrategia: Network first, fallback to cache
self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});
