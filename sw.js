const CACHE_NAME = "lexstudy-mobile-v3";

const SHELL_ASSETS = [
  "./",
  "index.php",
  "comecar.php",
  "plano.php",
  "disciplinas.php",
  "materia.php",
  "sala.php",
  "assistant.php",
  "revisao.php",
  "foco.php",
  "ferramentas.php",
  "telemovel.php",
  "assets/styles.css?v=2",
  "assets/mobile.js",
  "assets/assistant.js",
  "assets/foco.js",
  "assets/parliament-logo.png",
  "assets/lexstudy-mark.svg",
  "assets/icons/parliament.svg",
  "assets/icons/book.svg",
  "assets/icons/lawyer.svg",
  "assets/icons/document.svg",
  "assets/icons/compass.svg"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(SHELL_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (event) => {
  const { request } = event;

  if (request.method !== "GET") {
    return;
  }

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) {
    return;
  }

  if (request.mode === "navigate") {
    event.respondWith(
      fetch(request)
        .then((response) => {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
          return response;
        })
        .catch(() => caches.match(request).then((cached) => cached || caches.match("index.php")))
    );
    return;
  }

  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached) {
        return cached;
      }

      return fetch(request).then((response) => {
        if (response.ok) {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
        }
        return response;
      });
    })
  );
});
