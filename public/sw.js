/* Service worker EDEN : application installable, lecture hors ligne des pages consultées. */
const VERSION = 'v5';
const STATIC_CACHE = `eden-static-${VERSION}`;
const PAGES_CACHE = `eden-pages-${VERSION}`;
const IMAGES_CACHE = `eden-images-${VERSION}`;
const OFFLINE_URL = '/offline.html';
const PRECACHE = [OFFLINE_URL, '/pwa/icon-192.png', '/icons/favicon-32.png'];
const MAX_PAGES = 40;
const MAX_IMAGES = 120;

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE)).catch(() => {}));
});

self.addEventListener('activate', (event) => {
  const keep = [STATIC_CACHE, PAGES_CACHE, IMAGES_CACHE];
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((key) => !keep.includes(key)).map((key) => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

// La page propose « Mettre à jour » : la nouvelle version prend la main
self.addEventListener('message', (event) => {
  if (event.data === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

const trim = async (cacheName, max) => {
  const cache = await caches.open(cacheName);
  const keys = await cache.keys();
  await Promise.all(keys.slice(0, Math.max(0, keys.length - max)).map((key) => cache.delete(key)));
};

const isImage = (url) => /\.(png|jpe?g|webp|avif|gif|svg)$/i.test(url.pathname);

self.addEventListener('fetch', (event) => {
  const request = event.request;

  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  if (url.origin !== self.location.origin) {
    return;
  }

  // Jamais : administration, compte, documents PDF, requêtes Inertia (données JSON)
  if (/^\/(admin|me|logout|newsletter|up)(\/|$)/.test(url.pathname) || url.pathname.startsWith('/storage/documents') || request.headers.get('X-Inertia')) {
    return;
  }

  // Pages : réseau d'abord ; hors ligne, la dernière version consultée ou la page hors ligne
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response.ok && response.type === 'basic' && !response.headers.get('Cache-Control')?.includes('no-store')) {
            const copy = response.clone();
            caches.open(PAGES_CACHE).then((cache) => cache.put(request, copy)).then(() => trim(PAGES_CACHE, MAX_PAGES));
          }
          return response;
        })
        .catch(async () => (await caches.match(request, { ignoreSearch: true })) || caches.match(OFFLINE_URL))
    );
    return;
  }

  // Fichiers versionnés du build, icônes, polices : cache d'abord
  if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') || url.pathname.startsWith('/pwa/') || /\.(woff2?|ttf)$/i.test(url.pathname)) {
    event.respondWith(
      caches.match(request).then((cached) => cached || fetch(request).then((response) => {
        if (response.ok) {
          const copy = response.clone();
          caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy));
        }
        return response;
      }))
    );
    return;
  }

  // Photos (contenus, projets) : affichage immédiat depuis le cache, mise à jour en arrière-plan
  if (isImage(url)) {
    event.respondWith(
      caches.open(IMAGES_CACHE).then(async (cache) => {
        const cached = await cache.match(request);
        const network = fetch(request).then((response) => {
          if (response.ok) {
            cache.put(request, response.clone()).then(() => trim(IMAGES_CACHE, MAX_IMAGES));
          }
          return response;
        }).catch(() => cached);
        return cached || network;
      })
    );
  }
});
