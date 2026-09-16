<script setup>
import { ref, onMounted } from 'vue'
import { subscribeToPush, unsubscribeFromPush, getSubscriptionStatus } from '@/push'

const isSubscribed = ref(false)
const loading = ref(false)
const error = ref(null)

onMounted(async () => {
    isSubscribed.value = await getSubscriptionStatus()
})

async function toggle() {
    loading.value = true
    error.value = null

    try {
        if (isSubscribed.value) {
            await unsubscribeFromPush()
            isSubscribed.value = false
        } else {
            await subscribeToPush()
            isSubscribed.value = true
        }
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div>
        <button
            @click="toggle"
            :disabled="loading"
            class="rounded px-4 py-2 text-white disabled:opacity-50"
            :class="isSubscribed ? 'bg-neutral-text' : 'bg-primary'"
        >
            {{ loading ? 'Working…' : isSubscribed ? 'Disable stock alerts' : 'Enable stock alerts' }}
        </button>
        <p v-if="error" class="mt-2 text-sm text-accent">{{ error }}</p>
    </div>
</template>
