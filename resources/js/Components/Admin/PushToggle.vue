<script setup>
/**
 * Push notification toggle for order and low-stock alerts.
 *
 * Styled to sit on a dark-teal surface (the AdminSidebar footer tile), since
 * that's where it's used. If you need it on a white background elsewhere,
 * say so and I'll add a `variant="light"` prop instead of guessing here.
 */
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
            message.value = 'Blocked. Allow notifications for this site in your browser settings.'
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

const toggle = () => (subscribed.value ? disable() : enable())
</script>

<template>
    <div v-if="supported" class="w-full px-2 py-2">
        <div class="flex items-center gap-2.5">
            <span
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors duration-150"
                :class="subscribed ? 'bg-white/15 text-white' : 'bg-white/10 text-white/50'"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 17.5v.3a3 3 0 0 0 6 0v-.3" />
                    <path d="M6 17.5V11a6 6 0 1 1 12 0v6.5" />
                    <path v-if="subscribed" d="M4.5 17.5h15" />
                    <path v-else d="m4 4 16 16" />
                </svg>
            </span>

            <span class="min-w-0 flex-1">
                <span class="block text-sm font-medium leading-tight text-white">Order alerts</span>
                <span class="mt-0.5 block text-xs leading-tight text-white/55">
                    {{ subscribed ? 'On for this device' : 'Low stock and new orders' }}
                </span>
            </span>

            <!-- Switch -->
            <button
                v-if="permission !== 'denied'"
                type="button"
                role="switch"
                :aria-checked="subscribed"
                aria-label="Order and stock alerts"
                :disabled="busy"
                class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 focus-visible:ring-offset-2 focus-visible:ring-offset-primary-dark disabled:opacity-50"
                :class="subscribed ? 'bg-primary' : 'bg-white/20'"
                @click="toggle"
            >
                <span
                    class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform duration-150 motion-reduce:transition-none"
                    :class="subscribed ? 'translate-x-[1.125rem]' : 'translate-x-1'"
                >
                    <svg
                        v-if="busy"
                        class="h-3.5 w-3.5 animate-spin text-primary-dark/70 motion-reduce:animate-none"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                    >
                        <circle cx="12" cy="12" r="9" stroke-opacity="0.25" />
                        <path d="M21 12a9 9 0 0 0-9-9" stroke-linecap="round" />
                    </svg>
                </span>
            </button>
        </div>

        <p v-if="permission === 'denied'" class="mt-2 flex items-start gap-1.5 pl-[2.625rem] text-xs leading-snug text-accent-light">
            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="10" cy="10" r="7.5" /><path d="M10 9.25v4M10 6.5h.01" />
            </svg>
            Blocked in your browser settings for this site.
        </p>
        <p v-else-if="message" class="mt-2 flex items-start gap-1.5 pl-[2.625rem] text-xs leading-snug text-accent-light">
            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="10" cy="10" r="7.5" /><path d="M10 9.25v4M10 6.5h.01" />
            </svg>
            {{ message }}
        </p>
    </div>
</template>
