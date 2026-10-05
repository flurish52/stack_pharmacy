// Stack Pharmacy service worker: shows push notifications for staff.

self.addEventListener('install', () => self.skipWaiting())

self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()))

self.addEventListener('push', (event) => {
    let data = {}

    try {
        data = event.data ? event.data.json() : {}
    } catch (e) {
        data = { body: event.data ? event.data.text() : '' }
    }

    event.waitUntil(
        self.registration.showNotification(data.title || 'Stack Pharmacy', {
            body: data.body || '',
            data: { url: data.url || '/admin/dashboard' },
        }),
    )
})

self.addEventListener('notificationclick', (event) => {
    event.notification.close()

    const target = new URL(event.notification.data?.url || '/admin/dashboard', self.location.origin).href

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
            for (const client of windows) {
                if (client.url === target && 'focus' in client) return client.focus()
            }
            return self.clients.openWindow(target)
        }),
    )
})
