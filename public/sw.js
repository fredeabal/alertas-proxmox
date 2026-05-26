const CACHE_NAME = 'proxmox-alert-v1';

// Instalación: No cacheamos nada agresivamente por ahora para evitar problemas de desarrollo
self.addEventListener('install', event => {
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(clients.claim());
});

// Estrategia: Network first, fallback to cache (filtrando peticiones dinámicas y POST)
self.addEventListener('fetch', event => {
  // 1. Ignorar peticiones que no sean GET (como POST de acciones masivas o envíos de formularios)
  if (event.request.method !== 'GET') {
    return;
  }

  // 2. Solo interceptar peticiones de recursos estáticos o de la propia aplicación
  // Omitimos rutas dinámicas de controladores (ej: /companies/view/1, /ai, /alerts-config) para evitar problemas de caché de datos vivos.
  const url = new URL(event.request.url);
  
  // Determinamos si es un recurso estático (CSS, JS, imágenes, fuentes, manifiesto, favicon, etc.)
  const isStaticAsset = url.pathname.includes('/assets/') || 
                        url.pathname.includes('/uploads/') ||
                        url.pathname === '/manifest.json' ||
                        url.pathname === '/favicon.ico' ||
                        url.pathname === '/favicon.png' ||
                        /\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i.test(url.pathname);

  // Si no es un asset estático, dejamos que la petición fluya normalmente por la red sin intervención del Service Worker
  if (!isStaticAsset) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Si obtenemos una respuesta exitosa, la guardamos en la caché para soporte offline futuro
        if (response && response.status === 200) {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }
        return response;
      })
      .catch(async () => {
        // Si falla la red (offline), intentamos servir desde la caché
        const cachedResponse = await caches.match(event.request);
        if (cachedResponse) {
          return cachedResponse;
        }
        
        // Si no está en caché ni hay red, devolvemos una respuesta de error de red válida 
        // para evitar que el navegador lance: "TypeError: Failed to convert value to 'Response'"
        return new Response('Network error and no cache fallback available', {
          status: 503,
          statusText: 'Service Unavailable',
          headers: new Headers({ 'Content-Type': 'text/plain; charset=utf-8' })
        });
      })
  );
});
