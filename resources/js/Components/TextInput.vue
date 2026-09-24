<script setup>
import { onMounted, ref } from 'vue'

defineProps({ modelValue: { type: String, required: true } })
defineEmits(['update:modelValue'])

const input = ref(null)
onMounted(() => {
    if (input.value.hasAttribute('autofocus')) input.value.focus()
})

defineExpose({ focus: () => input.value.focus() })
</script>

<template>
    <input
        ref="input"
        class="w-full rounded-md border border-neutral-text/15 bg-white px-3.5 py-2.5 text-sm text-neutral-text placeholder:text-neutral-text/40 focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
