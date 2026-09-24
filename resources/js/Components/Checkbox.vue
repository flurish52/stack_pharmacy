<script setup>
defineProps({ checked: { type: [Array, Boolean], default: false } })
defineEmits(['update:checked'])
</script>

<template>
    <input
        type="checkbox"
        :checked="checked"
        class="h-4 w-4 rounded border-neutral-text/25 text-primary-dark focus:ring-1 focus:ring-primary-dark/30 focus:ring-offset-0"
        @change="$emit('update:checked', $event.target.checked)"
    />
</template>
