<script setup>
import { computed, onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const publicKey = computed(() => page.props.vapidPublicKey)

const supported = ref(false)
const permission = ref('default')
const subscribed = ref(false)
const busy = ref(false)
const message = ref('')

const urlBase64ToUint8Array = (base64) => {
    const padding = '='.repeat((4 - (base64.length % 4)) % 4)
    const raw = atob((base64 + padding).replace(/-/g, '+').replace(/_/g, '/'))
    return Uint8Array.from([...raw].map((char) => char.charCodeAt(0)))
}

const xsrf = () =>
    decodeURIComponent(
        document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1] ?? '',
    )

const sendToServer = async (method, body) => {
    const response = await fetch('/push/subscribe', {
        method,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-XSRF-TOKEN': xsrf(),
        },
        body: JSON.stringify(body),
    })

    if (!response.ok) throw new Error('Server rejected the request')
}

onMounted(async () => {
    supported.value =
        !!publicKey.value && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window

    if (!supported.value) return

    permission.value = Notification.permission

    try {
        const registration = await navigator.serviceWorker.register('/sw.js')
        const existing = await registration.pushManager.getSubscription()

        subscribed.value = !!existing && permission.value === 'granted'

        // Quietly re-save it so the server copy can never drift out of date.
        if (subscribed.value) await sendToServer('POST', existing.toJSON())
    } catch (error) {
        console.error(error)
    }
})

const enable = async () => {
    busy.value = true
    message.value = ''

    try {
        permission.value = await Notification.requestPermission()

        if (permission.value !== 'granted') {
            message.value = 'Notifications are blocked. Allow them in your browser settings for this site.'
            return
        }

        const registration = await navigator.serviceWorker.ready
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(publicKey.value),
        })

        await sendToServer('POST', subscription.toJSON())
        subscribed.value = true
    } catch (error) {
        console.error(error)
        message.value = 'Could not turn on alerts. Please try again.'
    } finally {
        busy.value = false
    }
}

const disable = async () => {
    busy.value = true
    message.value = ''

    try {
        const registration = await navigator.serviceWorker.ready
        const subscription = await registration.pushManager.getSubscription()

        if (subscription) {
            await sendToServer('DELETE', { endpoint: subscription.endpoint })
            await subscription.unsubscribe()
        }

        subscribed.value = false
    } catch (error) {
        console.error(error)
        message.value = 'Could not turn off alerts. Please try again.'
    } finally {
        busy.value = false
    }
}
</script>

<template>
    <div v-if="supported" class="mb-4 rounded-md bg-gray-50 p-3 text-xs">
        <p class="font-medium text-gray-700">Order and stock alerts</p>
        <p class="mt-0.5 text-gray-500">
            {{ subscribed ? 'On for this device.' : 'Get a notification on this device for low stock.' }}
        </p>

        <button
            v-if="permission !== 'denied'"
            type="button"
            :disabled="busy"
            class="mt-2 rounded-md border px-2.5 py-1 font-medium disabled:opacity-50"
            :class="subscribed ? 'border-gray-300 text-gray-600 hover:bg-white' : 'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700'"
            @click="subscribed ? disable() : enable()"
        >
            {{ busy ? 'Please wait...' : subscribed ? 'Turn off' : 'Turn on' }}
        </button>

        <p v-else class="mt-2 text-amber-700">Blocked in your browser settings for this site.</p>
        <p v-if="message" class="mt-2 text-red-600">{{ message }}</p>
    </div>
</template>
