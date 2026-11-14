// Service worker for admin browser push notifications.
self.addEventListener('push', (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: 'New notification', body: event.data ? event.data.text() : '' };
    }

    event.waitUntil(self.registration.showNotification(data.title || 'New notification', {
        body: data.body || '',
        tag: data.tag || undefined,
        // The site's favicon or logo from Settings, Site identity.
        icon: data.icon || '/favicon.ico',
        data: { url: data.url || '/admin' },
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/admin';

    event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
        for (const client of windows) {
            if (client.url === url && 'focus' in client) {
                return client.focus();
            }
        }

        return clients.openWindow(url);
    }));
});
