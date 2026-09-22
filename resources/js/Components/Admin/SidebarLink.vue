<script setup>
// resources/js/Components/Admin/SidebarLink.vue
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    href: { type: String, required: true },
    label: { type: String, required: true },
    icon: { type: String, required: true }, // inner markup of a 24x24 stroke SVG
    active: { type: Boolean, default: false },
    collapsed: { type: Boolean, default: false },
    danger: { type: Boolean, default: false },
    method: { type: String, default: 'get' },
    as: { type: String, default: 'a' },
})

// The label tooltip is teleported to <body>, so the sidebar's scroll container can never clip it.
const tip = ref(null)

const showTip = (event) => {
    if (!props.collapsed || typeof window === 'undefined') return
    if (!window.matchMedia('(min-width: 1024px)').matches) return // drawer on mobile shows labels

    const rect = event.currentTarget.getBoundingClientRect()
    tip.value = { top: rect.top + rect.height / 2, left: rect.right + 12 }
}

const hideTip = () => (tip.value = null)
</script>

<template>
    <Link
        :href="href"
        :method="method"
        :as="as"
        :type="as === 'button' ? 'button' : undefined"
        class="group relative flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-left text-sm transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
        :class="[
            collapsed ? 'lg:justify-center lg:px-0' : '',
            active
                ? 'bg-primary/10 font-medium text-primary-dark'
                : danger
                  ? 'text-neutral-text/65 hover:bg-accent/10 hover:text-accent'
                  : 'text-neutral-text/65 hover:bg-neutral-bg hover:text-neutral-text',
        ]"
        :aria-current="active ? 'page' : undefined"
        @mouseenter="showTip"
        @mouseleave="hideTip"
        @focus="showTip"
        @blur="hideTip"
        @click="hideTip"
    >
        <!-- Active marker, hugging the sidebar's left edge -->
        <span
            class="absolute -left-3 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-primary transition-transform duration-200 motion-reduce:transition-none"
            :class="active ? 'scale-y-100' : 'scale-y-0'"
            aria-hidden="true"
        />

        <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.75"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="shrink-0 transition-colors duration-150"
            :class="
                active
                    ? 'text-primary-dark'
                    : danger
                      ? 'text-neutral-text/45 group-hover:text-accent'
                      : 'text-neutral-text/45 group-hover:text-primary-dark'
            "
            aria-hidden="true"
            v-html="icon"
        />

        <span class="truncate" :class="collapsed ? 'lg:hidden' : ''">{{ label }}</span>
    </Link>

    <Teleport to="body">
        <Transition name="tip">
            <span
                v-if="tip"
                role="tooltip"
                class="pointer-events-none fixed z-50 -translate-y-1/2 whitespace-nowrap rounded-lg bg-neutral-text px-2.5 py-1.5 text-xs font-medium text-white shadow-lg"
                :style="{ top: tip.top + 'px', left: tip.left + 'px' }"
            >
                {{ label }}
            </span>
        </Transition>
    </Teleport>
</template>

<style scoped>
.tip-enter-active,
.tip-leave-active {
    transition: opacity 0.15s ease;
}

.tip-enter-from,
.tip-leave-to {
    opacity: 0;
}
</style>
