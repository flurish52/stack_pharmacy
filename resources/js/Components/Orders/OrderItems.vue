<script setup>
defineProps({
    items: { type: Array, required: true },
})

function formatNaira(value) {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(value)
}
</script>

<template>
    <div class="divide-y divide-neutral-text/10 rounded-xl border border-neutral-text/10">
        <div
            v-for="item in items"
            :key="item.id"
            class="flex items-center justify-between gap-4 p-4"
        >
            <div>
                <p class="font-medium text-neutral-text">
                    {{ item.variant?.product?.name || 'Product' }}
                </p>
                <p v-if="item.variant?.variant_name" class="mt-0.5 text-sm text-neutral-text/60">{{ item.variant.variant_name }}</p>
                <p class="mt-0.5 text-sm text-neutral-text/60">Qty {{ item.quantity }} × {{ formatNaira(item.price) }}</p>
            </div>
            <p class="font-medium text-neutral-text">{{ formatNaira(item.price * item.quantity) }}</p>
        </div>
    </div>
</template>
