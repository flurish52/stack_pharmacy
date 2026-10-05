<script setup>
defineProps({ value: { type: String, default: '' } })
</script>

<template>
    <label class="mb-1.5 block text-xs font-medium text-neutral-text/70">
        <slot v-if="$slots.default" />
        <template v-else>{{ value }}</template>
    </label>
</template>
