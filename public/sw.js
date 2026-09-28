/* MBG Kitchen PWA shell cache (static CDN only; never cache authenticated HTML). */
const CACHE = 'mbg-shell-v1';
const SHELL = [
  'https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/css/tabler.min.css',
  'https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js',
];
self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(SHELL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', (e) => {
  e.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', (e) => {
  const url = new URL(e.request.url);
  if (url.origin !== location.origin) {
    e.respondWith(caches.match(e.request).then((hit) => hit || fetch(e.request).then((res) => {
      const copy = res.clone();
      caches.open(CACHE).then((c) => c.put(e.request, copy));
      return res;
    }).catch(() => caches.match(e.request))));
  }
});
