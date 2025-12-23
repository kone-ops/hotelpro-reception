// Service Worker pour Hotel Pro
const CACHE_NAME = 'hotelpro-v1.0.0';
const RUNTIME_CACHE = 'hotelpro-runtime-v1.0.0';

// Assets à mettre en cache au démarrage (seulement les fichiers essentiels qui existent)
// Les autres ressources seront mises en cache à la demande via le cache runtime
const PRECACHE_ASSETS = [
  '/' // Seulement la page d'accueil, les autres ressources seront mises en cache à la demande
];

// Installation du Service Worker
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installation...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[Service Worker] Mise en cache des assets');
        // Utiliser Promise.allSettled pour gérer les fichiers manquants gracieusement
        return Promise.allSettled(
          PRECACHE_ASSETS.map(url => 
            cache.add(url).catch(err => {
              console.warn(`[Service Worker] Impossible de mettre en cache ${url}:`, err.message);
              return null; // Ignorer les erreurs pour continuer avec les autres fichiers
            })
          )
        );
      })
      .then(() => {
        console.log('[Service Worker] Installation terminée');
        return self.skipWaiting();
      })
      .catch(err => {
        console.error('[Service Worker] Erreur lors de l\'installation:', err);
        return self.skipWaiting(); // Continuer quand même
      })
  );
});

// Activation du Service Worker
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activation...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => {
            return cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE;
          })
          .map((cacheName) => {
            console.log('[Service Worker] Suppression du cache:', cacheName);
            return caches.delete(cacheName);
          })
      );
    })
    .then(() => self.clients.claim())
  );
});

// Stratégie de cache : Network First, puis Cache
self.addEventListener('fetch', (event) => {
  // Ignorer les requêtes non-GET
  if (event.request.method !== 'GET') {
    return;
  }

  // Ignorer les requêtes API et les formulaires
  if (event.request.url.includes('/api/') || 
      event.request.url.includes('/login') ||
      event.request.url.includes('/logout')) {
    return;
  }

  event.respondWith(
    caches.open(RUNTIME_CACHE).then((cache) => {
      return fetch(event.request)
        .then((response) => {
          // Mettre en cache la réponse si valide
          if (response && response.status === 200) {
            cache.put(event.request, response.clone());
          }
          return response;
        })
        .catch(() => {
          // Si le réseau échoue, utiliser le cache
          return cache.match(event.request);
        });
    })
  );
});

