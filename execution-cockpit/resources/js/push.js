/*
 * Web Push enrolment (call enablePush() from a user tap — required on iOS).
 *
 *   import { enablePush, isPushSupported, needsHomeScreenInstall } from './push'
 *
 * Requires <meta name="vapid-key" content="{{ config('webpush.vapid.public_key') }}">
 * in the app layout head, and vite-plugin-pwa registering the service worker.
 */

function urlBase64ToUint8Array(base64) {
  const padding = '='.repeat((4 - (base64.length % 4)) % 4);
  const b64 = (base64 + padding).replace(/-/g, '+').replace(/_/g, '/');
  const raw = atob(b64);
  return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
}

export function isPushSupported() {
  return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
}

/** iOS only allows push once the PWA is added to the Home Screen. */
export function needsHomeScreenInstall() {
  const iOS = /iP(hone|ad|od)/.test(navigator.userAgent);
  const standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  return iOS && !standalone;
}

export async function enablePush() {
  if (!isPushSupported()) throw new Error('Push not supported on this browser.');
  if (needsHomeScreenInstall()) throw new Error('On iPhone/iPad, add this app to your Home Screen first, then enable reminders.');

  const permission = await Notification.requestPermission();
  if (permission !== 'granted') throw new Error('Notification permission was declined.');

  const registration = await navigator.serviceWorker.ready;
  const vapidKey = document.querySelector('meta[name="vapid-key"]')?.content;
  if (!vapidKey) throw new Error('Missing VAPID public key meta tag.');

  const existing = await registration.pushManager.getSubscription();
  const subscription = existing || await registration.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array(vapidKey),
  });

  await fetch('/push/subscribe', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify(subscription.toJSON()),
  });

  return true;
}

export async function disablePush() {
  const registration = await navigator.serviceWorker.ready;
  const subscription = await registration.pushManager.getSubscription();
  if (!subscription) return;
  await fetch('/push/subscribe', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
    },
    body: JSON.stringify({ endpoint: subscription.endpoint }),
  });
  await subscription.unsubscribe();
}
