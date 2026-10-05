<script setup>
// The client's motto is "We Treat, God Heals" + "Service to Humanity".
// Shown as two parts with a divider, so no dash character is needed.
defineProps({
    first: { type: String, default: 'We Treat, God Heals' },
    second: { type: String, default: 'Service to Humanity' },
})
</script>

<template>
    <section class="bg-primary-dark absolute w-full bottom-[-100px] h-24 flex items-center justify-center">
        <p class="mx-auto flex max-w-6xl flex-col items-center justify-center gap-1 px-6 py-5 text-center font-heading text-lg font-semibold tracking-tight text-white sm:flex-row sm:gap-5 sm:text-xl">
            <span>{{ first }}</span>
            <span class="hidden h-5 w-px bg-white/30 sm:block" aria-hidden="true" />
            <span>{{ second }}</span>
        </p>
    </section>
</template>
