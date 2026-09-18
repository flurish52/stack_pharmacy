<script setup>
const items = [
    { icon: 'truck', label: 'Fast delivery on every, zero delays' },
    { icon: 'shield-check', label: '100% genuine medicines' },
    { icon: 'lock', label: 'Secure & safe payments' },
    { icon: 'headset', label: '24/7 support' },
    // { icon: 'file', label: 'Order with prescription' },
]

const icons = {
    truck: 'M3 7h11v9H3zM14 10h4l3 3v3h-7zM6.5 20a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM17.5 20a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z',
    'shield-check': 'M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4zM9 12l2 2 4-4',
    lock: 'M6 11V7a6 6 0 1 1 12 0v4M5 11h14v9H5z',
    headset: 'M3 13a9 9 0 1 1 18 0v5a2 2 0 0 1-2 2h-1v-6h3M3 18v-5h3v6H5a2 2 0 0 1-2-2z',
    file: 'M6 3h9l3 3v15H6zM15 3v3h3M9 12h6M9 15.5h6M9 8.5h3',
}
</script>

<template>
    <div class="bg-primary-dark">
        <div class="mx-auto max-w-6xl overflow-x-auto px-4 sm:px-6">
            <div class="flex w-max items-center gap-6 py-2 text-xs text-white/90 sm:w-full sm:justify-between sm:gap-8">
                <div
                    v-for="(item, index) in items"
                    :key="item.label"
                    class="flex shrink-0 items-center gap-1.5"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0 text-white/70">
                        <path :d="icons[item.icon]" />
                    </svg>
                    <span class="whitespace-nowrap">{{ item.label }}</span>
                    <span v-if="index < items.length - 1" class="ml-6 hidden h-3 w-px bg-white/20 sm:block" aria-hidden="true" />
                </div>
            </div>
        </div>
    </div>
</template>
