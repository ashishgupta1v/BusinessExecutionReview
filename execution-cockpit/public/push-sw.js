/*
 * Push handlers, imported into the generated service worker via
 * VitePWA workbox.importScripts (see vite.config.js). Kept as a plain static
 * file so the push/notificationclick logic is stable across builds.
 */

self.addEventListener('push', (event) => {
  let payload = {};
  try { payload = event.data ? event.data.json() : {}; } catch (e) { payload = { title: 'Business Execution Toolkit', body: event.data && event.data.text() }; }

  const title = payload.title || 'Business Execution Toolkit';
  const options = {
    body:  payload.body || '',
    icon:  payload.icon || '/icons/icon-192.png',
    badge: payload.badge || '/icons/badge.png',
    tag:   payload.tag || 'ec-reminder',
    renotify: true,
    data:  { url: (payload.data && payload.data.url) || '/dcr' },
    actions: (payload.actions || []).map((a) => (typeof a === 'string' ? { action: a, title: a } : a)),
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = (event.notification.data && event.notification.data.url) || '/dcr';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
      for (const client of clients) {
        if (client.url.includes(url) && 'focus' in client) return client.focus();
      }
      if (self.clients.openWindow) return self.clients.openWindow(url);
    })
  );
});
