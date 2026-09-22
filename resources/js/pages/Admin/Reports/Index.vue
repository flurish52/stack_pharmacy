<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { naira } from '@/Composables/OrderStatus.js'

const props = defineProps({
    period: { type: String, required: true },
    from: { type: String, required: true },
    to: { type: String, required: true },
    summary: { type: Object, required: true },
    series: { type: Array, default: () => [] },
    topProducts: { type: Array, default: () => [] },
    fulfillment: { type: Array, default: () => [] },
    statusBreakdown: { type: Array, default: () => [] },
})

/* ------------------------------------------------------------------ */
/* Period                                                              */
/* ------------------------------------------------------------------ */

const presets = [
    { value: 'today', label: 'Today' },
    { value: '7d', label: 'Last 7 days' },
    { value: '30d', label: 'Last 30 days' },
    { value: 'this_month', label: 'This month' },
    { value: 'last_month', label: 'Last month' },
]

const custom = reactive({ from: props.from, to: props.to })
const loading = ref(false)

const go = (params) =>
    router.get('/admin/reports', params, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    })

const canApply = computed(() => !!custom.from && !!custom.to && custom.from <= custom.to)

const applyCustom = () => {
    if (canApply.value) go({ period: 'custom', from: custom.from, to: custom.to })
}

const shortDate = (iso) =>
    new Date(`${iso}T00:00:00`).toLocaleDateString('en-NG', { day: 'numeric', month: 'short' })

const rangeLabel = computed(() =>
    props.from === props.to ? shortDate(props.from) : `${shortDate(props.from)} to ${shortDate(props.to)}`,
)

/* ------------------------------------------------------------------ */
/* Motion helpers                                                      */
/* ------------------------------------------------------------------ */

// Bars and rings grow from zero once the page has mounted.
const ready = ref(false)
onMounted(() => requestAnimationFrame(() => (ready.value = true)))

const prefersReducedMotion = () =>
    typeof window !== 'undefined' && !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

// Summary numbers count up to their value, and re-count when the period changes.
const shown = reactive({ revenue: 0, orders: 0, average: 0, cancelled: 0 })
const tokens = {}

const tween = (key, to) => {
    const token = (tokens[key] = (tokens[key] ?? 0) + 1)

    if (typeof window === 'undefined' || prefersReducedMotion()) {
        shown[key] = to
        return
    }

    const from = shown[key]
    const start = performance.now()
    const duration = 700

    const tick = (now) => {
        if (token !== tokens[key]) return // a newer tween took over
        const t = Math.min(1, (now - start) / duration)
        shown[key] = from + (to - from) * (1 - Math.pow(1 - t, 3))
        if (t < 1) requestAnimationFrame(tick)
    }
    requestAnimationFrame(tick)
}

const targets = computed(() => ({
    revenue: Number(props.summary.revenue) || 0,
    orders: Number(props.summary.orders) || 0,
    average: Number(props.summary.average) || 0,
    cancelled: Number(props.summary.cancelled) || 0,
}))

watch(targets, (next) => Object.entries(next).forEach(([key, value]) => tween(key, value)), { immediate: true })

/* ------------------------------------------------------------------ */
/* Revenue chart (plain HTML, no chart library)                        */
/* ------------------------------------------------------------------ */

const maxRevenue = computed(() => Math.max(0, ...props.series.map((d) => Number(d.revenue))))
const hasRevenue = computed(() => maxRevenue.value > 0)

const averageRevenue = computed(() =>
    props.series.length ? props.series.reduce((sum, d) => sum + Number(d.revenue), 0) / props.series.length : 0,
)
const averagePercent = computed(() => (hasRevenue.value ? (averageRevenue.value / maxRevenue.value) * 100 : 0))

const barPercent = (day) => {
    if (!hasRevenue.value || Number(day.revenue) <= 0) return 0
    return Math.max(2, (Number(day.revenue) / maxRevenue.value) * 100)
}

const isPeak = (day) => hasRevenue.value && Number(day.revenue) === maxRevenue.value

const compact = new Intl.NumberFormat('en-NG', { notation: 'compact', maximumFractionDigits: 1 })
const axisLabel = (value) => `₦${compact.format(value)}`

const gapClass = computed(() => (props.series.length > 60 ? 'gap-0' : props.series.length > 31 ? 'gap-px' : 'gap-1'))

// Keep tooltips inside the card at both ends of the chart.
const tipPosition = (index) => {
    if (index < 3) return 'left-0'
    if (index > props.series.length - 4) return 'right-0'
    return 'left-1/2 -translate-x-1/2'
}

const xLabels = computed(() => {
    const first = props.series[0]?.date ?? props.from
    const last = props.series[props.series.length - 1]?.date ?? props.to
    const mid = props.series.length > 4 ? props.series[Math.floor(props.series.length / 2)].date : null
    return [first, mid, last].filter(Boolean)
})

// Sparkline behind the revenue card
const spark = computed(() => {
    const n = props.series.length
    if (n < 2 || !hasRevenue.value) return null

    const w = 120
    const h = 32
    const points = props.series.map((d, i) => [(i / (n - 1)) * w, h - 2 - (Number(d.revenue) / maxRevenue.value) * (h - 6)])
    const line = 'M' + points.map(([x, y]) => `${x.toFixed(1)} ${y.toFixed(1)}`).join(' L')

    return { line, area: `${line} L${w} ${h} L0 ${h} Z`, viewBox: `0 0 ${w} ${h}` }
})

/* ------------------------------------------------------------------ */
/* Summary cards                                                       */
/* ------------------------------------------------------------------ */

const change = (value) => {
    if (value === null || value === undefined) return null
    return { text: `${value > 0 ? '+' : ''}${value}%`, up: value >= 0 }
}

const summaryCards = computed(() => [
    {
        key: 'revenue',
        hero: true,
        label: 'Revenue',
        value: naira.format(shown.revenue),
        change: change(props.summary.revenue_change),
        icon: 'M4 6.5h12v8H4v-8Zm6 1.75a1.75 1.75 0 1 0 0 3.5 1.75 1.75 0 0 0 0-3.5ZM6.5 6.5v0M13.5 14.5v0',
        chip: 'bg-white/15 text-white',
    },
    {
        key: 'orders',
        label: 'Orders',
        value: Math.round(shown.orders),
        change: change(props.summary.orders_change),
        icon: 'M4.5 6.5h11l-1 9h-9l-1-9ZM7.5 8.5V6a2.5 2.5 0 0 1 5 0v2.5',
        chip: 'bg-primary/10 text-primary-dark',
    },
    {
        key: 'average',
        label: 'Average order',
        value: naira.format(shown.average),
        change: null,
        icon: 'M5 15V9M10 15V5M15 15v-4',
        chip: 'bg-secondary text-primary-dark',
    },
    {
        key: 'cancelled',
        label: 'Cancelled orders',
        value: Math.round(shown.cancelled),
        change: null,
        icon: 'M10 2.5a7.5 7.5 0 1 0 0 15 7.5 7.5 0 0 0 0-15ZM7.5 7.5l5 5M12.5 7.5l-5 5',
        chip: 'bg-accent/10 text-accent',
    },
])

/* ------------------------------------------------------------------ */
/* Best sellers                                                        */
/* ------------------------------------------------------------------ */

const totalUnits = computed(() => props.topProducts.reduce((sum, p) => sum + Number(p.units), 0))
const topRevenue = computed(() => Math.max(1, ...props.topProducts.map((p) => Number(p.revenue))))
const revenueShare = (product) => (ready.value ? Math.max(3, (Number(product.revenue) / topRevenue.value) * 100) : 0)

/* ------------------------------------------------------------------ */
/* Pickup vs delivery (donut)                                          */
/* ------------------------------------------------------------------ */

const fulfillmentTotal = computed(() => props.fulfillment.reduce((sum, r) => sum + Number(r.orders), 0))
const fulfillmentDots = ['bg-primary-dark', 'bg-primary', 'bg-accent-light']
const fulfillmentStrokes = ['stroke-primary-dark', 'stroke-primary', 'stroke-accent-light']
const fulfillmentShare = (row) => (fulfillmentTotal.value ? (Number(row.orders) / fulfillmentTotal.value) * 100 : 0)

const RADIUS = 40
const CIRCUMFERENCE = 2 * Math.PI * RADIUS

const donut = computed(() => {
    const gap = props.fulfillment.length > 1 ? 3 : 0
    let used = 0

    return props.fulfillment.map((row, i) => {
        const length = (fulfillmentShare(row) / 100) * CIRCUMFERENCE
        const visible = ready.value ? Math.max(0, length - gap) : 0
        const segment = {
            key: row.fulfillment_type,
            dash: `${visible} ${CIRCUMFERENCE}`,
            offset: -used,
            stroke: fulfillmentStrokes[i % fulfillmentStrokes.length],
        }
        used += length
        return segment
    })
})

/* ------------------------------------------------------------------ */
/* Status breakdown                                                    */
/* ------------------------------------------------------------------ */

const statusTotal = computed(() => props.statusBreakdown.reduce((sum, r) => sum + Number(r.orders), 0))
const statusShare = (row) => (statusTotal.value ? (Number(row.orders) / statusTotal.value) * 100 : 0)

/* ------------------------------------------------------------------ */
/* Shared classes                                                      */
/* ------------------------------------------------------------------ */

const card = 'rounded-xl border border-neutral-text/10 bg-white'
const cardHead = 'flex items-center justify-between gap-3 border-b border-neutral-text/10 bg-neutral-bg px-5 py-3.5'
const cardTitle = 'font-heading text-sm font-semibold text-neutral-text'
const dateInput =
    'rounded-lg border border-neutral-text/15 bg-white px-3 py-1.5 text-sm text-neutral-text transition hover:border-neutral-text/30 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20'
</script>

<template>
    <Head title="Reports" />

    <AdminLayout title="Reports">
        <div class="page-in">
            <!-- Period -->
            <div :class="[card, 'mb-6 flex flex-col gap-4 p-3 lg:flex-row lg:items-end lg:justify-between']">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex flex-wrap gap-1 rounded-xl bg-neutral-bg p-1" role="group" aria-label="Report period">
                        <button
                            v-for="preset in presets"
                            :key="preset.value"
                            type="button"
                            class="rounded-lg px-3.5 py-1.5 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                            :class="
                                period === preset.value
                                    ? 'bg-primary text-white shadow-sm'
                                    : 'text-neutral-text/65 hover:bg-white hover:text-primary-dark'
                            "
                            :aria-pressed="period === preset.value"
                            @click="go({ period: preset.value })"
                        >
                            {{ preset.label }}
                        </button>
                    </div>

                    <p class="inline-flex items-center gap-1.5 text-xs font-medium tabular-nums text-neutral-text/60">
                        <svg class="h-3.5 w-3.5 text-primary-dark" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4.5 6.5h11v9h-11v-9ZM4.5 9.5h11M7.5 4.5v3M12.5 4.5v3" />
                        </svg>
                        {{ rangeLabel }}
                    </p>
                </div>

                <div class="flex flex-wrap items-end gap-2">
                    <div>
                        <label for="rep-from" class="mb-1 block text-xs font-medium text-neutral-text/55">From</label>
                        <input id="rep-from" v-model="custom.from" type="date" :class="dateInput" />
                    </div>
                    <div>
                        <label for="rep-to" class="mb-1 block text-xs font-medium text-neutral-text/55">To</label>
                        <input id="rep-to" v-model="custom.to" type="date" :class="dateInput" />
                    </div>
                    <button
                        type="button"
                        :disabled="!canApply"
                        class="rounded-lg border px-4 py-1.5 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                        :class="
                            period === 'custom'
                                ? 'border-primary bg-primary text-white hover:bg-primary-dark'
                                : 'border-primary/40 bg-white text-primary-dark hover:bg-primary-light'
                        "
                        @click="applyCustom"
                    >
                        Apply
                    </button>
                </div>
            </div>

            <div class="transition-opacity duration-200" :class="loading ? 'opacity-60' : 'opacity-100'" :aria-busy="loading">
                <!-- Summary -->
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="item in summaryCards"
                        :key="item.key"
                        class="relative flex min-h-[8.5rem] flex-col overflow-hidden rounded-xl border p-5"
                        :class="item.hero ? 'border-primary-dark bg-primary-dark text-white' : 'border-neutral-text/10 bg-white'"
                    >
                        <!-- Sparkline sits behind the revenue card's content -->
                        <svg
                            v-if="item.hero && spark"
                            class="spark-in pointer-events-none absolute inset-x-0 bottom-0 h-14 w-full"
                            :viewBox="spark.viewBox"
                            preserveAspectRatio="none"
                            aria-hidden="true"
                        >
                            <path :d="spark.area" class="fill-white/10" />
                            <path
                                :d="spark.line"
                                fill="none"
                                class="stroke-white/40"
                                stroke-width="1.5"
                                stroke-linejoin="round"
                                stroke-linecap="round"
                                vector-effect="non-scaling-stroke"
                            />
                        </svg>

                        <div class="relative flex items-start justify-between gap-3">
                            <p
                                class="pt-1.5 text-sm font-medium leading-none"
                                :class="item.hero ? 'text-white/75' : 'text-neutral-text/65'"
                            >
                                {{ item.label }}
                            </p>
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="item.chip">
                                <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path :d="item.icon" />
                                </svg>
                            </span>
                        </div>

                        <p
                            class="relative mt-4 font-heading text-[1.75rem] font-semibold leading-none tracking-tight tabular-nums"
                            :class="item.hero ? 'text-white' : 'text-neutral-text'"
                        >
                            {{ item.value }}
                        </p>

                        <div class="relative mt-auto pt-3">
                            <p
                                v-if="item.change"
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    item.hero
                                        ? 'bg-white/15 text-white'
                                        : item.change.up
                                          ? 'bg-primary/10 text-primary-dark'
                                          : 'bg-accent/10 text-accent'
                                "
                            >
                                <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path v-if="item.change.up" d="M6 10V2M2.5 5.5 6 2l3.5 3.5" />
                                    <path v-else d="M6 2v8M2.5 6.5 6 10l3.5-3.5" />
                                </svg>
                                {{ item.change.text }}
                                <span class="font-normal" :class="item.hero ? 'text-white/70' : 'text-neutral-text/55'">
                                    vs previous period
                                </span>
                            </p>
                            <p v-else class="h-5" />
                        </div>
                    </div>
                </div>

                <p class="mt-3 flex items-center gap-2 text-xs text-neutral-text/55">
                    <svg class="h-3.5 w-3.5 shrink-0 text-primary-dark" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="10" cy="10" r="7.5" /><path d="M10 9.25v4M10 6.5h.01" />
                    </svg>
                    Revenue and orders count paid orders only. Unpaid and cancelled orders are left out.
                </p>

                <!-- Chart -->
                <section :class="[card, 'mt-6 overflow-hidden']">
                    <header :class="cardHead">
                        <h2 :class="cardTitle">Revenue by day</h2>
                        <p v-if="hasRevenue" class="text-xs text-neutral-text/55">
                            Highest day
                            <span class="ml-1 rounded-full bg-secondary px-2 py-0.5 font-medium tabular-nums text-primary-dark">
                                {{ naira.format(maxRevenue) }}
                            </span>
                        </p>
                    </header>

                    <div class="p-5">
                        <div class="flex gap-3">
                            <!-- Y axis -->
                            <div class="relative h-44 w-12 shrink-0 text-right text-[11px] tabular-nums text-neutral-text/45" aria-hidden="true">
                                <span class="absolute right-0 top-0 -translate-y-1/2">{{ axisLabel(maxRevenue) }}</span>
                                <span class="absolute right-0 top-1/2 -translate-y-1/2">{{ axisLabel(maxRevenue / 2) }}</span>
                                <span class="absolute bottom-0 right-0 translate-y-1/2">{{ axisLabel(0) }}</span>
                            </div>

                            <div class="relative h-44 min-w-0 flex-1" role="img" aria-label="Revenue by day">
                                <!-- gridlines -->
                                <div class="absolute inset-x-0 top-0 border-t border-dashed border-neutral-text/10" />
                                <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-neutral-text/10" />
                                <div class="absolute inset-x-0 bottom-0 border-t border-neutral-text/15" />

                                <!-- bars -->
                                <div class="relative flex h-full items-end" :class="gapClass">
                                    <div
                                        v-for="(day, index) in series"
                                        :key="day.date"
                                        class="group relative flex h-full flex-1 items-end justify-center"
                                    >
                                        <div
                                            class="bar-grow w-full max-w-10 rounded-t-md transition-colors duration-150 group-hover:bg-primary-dark"
                                            :class="isPeak(day) ? 'bg-primary-dark' : 'bg-primary'"
                                            :style="{ height: barPercent(day) + '%', '--i': Math.min(index, 30) }"
                                        />
                                        <div
                                            class="pointer-events-none absolute z-10 mb-2 whitespace-nowrap rounded-lg bg-neutral-text px-2.5 py-1.5 text-xs text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100"
                                            :class="tipPosition(index)"
                                            :style="{ bottom: barPercent(day) + '%' }"
                                        >
                                            <p class="font-medium">{{ shortDate(day.date) }}</p>
                                            <p class="tabular-nums text-white/80">
                                                {{ naira.format(day.revenue) }} from {{ day.orders }} {{ Number(day.orders) === 1 ? 'order' : 'orders' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- daily average -->
                                <div
                                    v-if="hasRevenue && series.length > 1"
                                    class="pointer-events-none absolute inset-x-0 z-[5] border-t border-dashed border-accent/70"
                                    :style="{ bottom: averagePercent + '%' }"
                                >
                                    <span class="absolute bottom-full right-0 mb-0.5 rounded bg-white/90 px-1.5 text-[11px] font-medium tabular-nums text-accent">
                                        Avg {{ axisLabel(averageRevenue) }}
                                    </span>
                                </div>

                                <p
                                    v-if="!hasRevenue"
                                    class="absolute inset-0 flex items-center justify-center text-sm text-neutral-text/50"
                                >
                                    No paid orders in this period.
                                </p>
                            </div>
                        </div>

                        <!-- X axis -->
                        <div class="ml-[3.75rem] mt-2 flex justify-between text-xs text-neutral-text/50">
                            <span v-for="date in xLabels" :key="date">{{ shortDate(date) }}</span>
                        </div>
                    </div>
                </section>

                <div class="mt-6 grid gap-6 lg:grid-cols-3">
                    <!-- Best sellers -->
                    <section :class="[card, 'overflow-hidden lg:col-span-2']">
                        <header :class="cardHead">
                            <h2 :class="cardTitle">Best sellers</h2>
                            <span v-if="topProducts.length" class="text-xs text-neutral-text/55">By revenue</span>
                        </header>

                        <div v-if="topProducts.length === 0" class="px-5 py-12 text-center">
                            <p class="font-heading font-medium text-neutral-text">No sales in this period</p>
                            <p class="mt-1 text-sm text-neutral-text/55">Try a wider date range.</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-neutral-text/10 text-xs text-neutral-text/55">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Product</th>
                                    <th class="px-5 py-3 text-right font-medium">Units sold</th>
                                    <th class="px-5 py-3 text-right font-medium">Revenue</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-text/[0.07]">
                                <tr
                                    v-for="(product, index) in topProducts"
                                    :key="product.id"
                                    class="transition-colors hover:bg-primary-light/60"
                                >
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                                <span
                                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold tabular-nums"
                                                    :class="index === 0 ? 'bg-primary-dark text-white' : 'bg-neutral-text/[0.07] text-neutral-text/60'"
                                                >
                                                    {{ index + 1 }}
                                                </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate font-medium text-neutral-text">{{ product.name }}</p>
                                                <div class="mt-1.5 h-1.5 w-full max-w-[12rem] overflow-hidden rounded-full bg-neutral-text/[0.07]">
                                                    <div
                                                        class="h-full rounded-full transition-[width] duration-700 ease-out motion-reduce:transition-none"
                                                        :class="index === 0 ? 'bg-primary-dark' : 'bg-primary'"
                                                        :style="{ width: revenueShare(product) + '%' }"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-right tabular-nums text-neutral-text/70">{{ product.units }}</td>
                                    <td class="px-5 py-3.5 text-right font-medium tabular-nums text-neutral-text">
                                        {{ naira.format(product.revenue) }}
                                    </td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="border-t border-neutral-text/10 bg-secondary/40 text-xs font-medium text-neutral-text/70">
                                    <td class="px-5 py-3">Top {{ topProducts.length }} total</td>
                                    <td class="px-5 py-3 text-right tabular-nums">{{ totalUnits }}</td>
                                    <td />
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </section>

                    <div class="space-y-6">
                        <!-- Pickup vs delivery -->
                        <section :class="[card, 'overflow-hidden text-sm']">
                            <header :class="cardHead"><h2 :class="cardTitle">Pickup vs delivery</h2></header>
                            <div class="p-5">
                                <p v-if="fulfillment.length === 0" class="py-4 text-center text-neutral-text/55">
                                    No orders in this period.
                                </p>
                                <div v-else class="flex items-center gap-5">
                                    <div class="relative h-24 w-24 shrink-0">
                                        <svg class="h-full w-full" viewBox="0 0 100 100" role="img" aria-label="Pickup vs delivery split">
                                            <g transform="rotate(-90 50 50)" fill="none" stroke-width="12">
                                                <circle cx="50" cy="50" :r="RADIUS" class="stroke-neutral-text/[0.07]" />
                                                <circle
                                                    v-for="segment in donut"
                                                    :key="segment.key"
                                                    cx="50"
                                                    cy="50"
                                                    :r="RADIUS"
                                                    :class="segment.stroke"
                                                    :stroke-dasharray="segment.dash"
                                                    :stroke-dashoffset="segment.offset"
                                                    style="transition: stroke-dasharray 0.8s ease-out"
                                                />
                                            </g>
                                        </svg>
                                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                                            <span class="font-heading text-lg font-semibold leading-none tabular-nums text-neutral-text">
                                                {{ fulfillmentTotal }}
                                            </span>
                                            <span class="mt-0.5 text-[11px] text-neutral-text/55">
                                                {{ fulfillmentTotal === 1 ? 'order' : 'orders' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1 space-y-3">
                                        <div v-for="(row, i) in fulfillment" :key="row.fulfillment_type">
                                            <p class="flex items-center gap-2 capitalize text-neutral-text">
                                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" :class="fulfillmentDots[i % fulfillmentDots.length]" />
                                                {{ row.fulfillment_type }}
                                            </p>
                                            <p class="mt-0.5 pl-[1.125rem] text-sm font-medium tabular-nums text-neutral-text">
                                                {{ naira.format(row.revenue) }}
                                            </p>
                                            <p class="pl-[1.125rem] text-xs tabular-nums text-neutral-text/55">
                                                {{ row.orders }} {{ Number(row.orders) === 1 ? 'order' : 'orders' }}, {{ Math.round(fulfillmentShare(row)) }}%
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Status breakdown -->
                        <section :class="[card, 'overflow-hidden text-sm']">
                            <header :class="cardHead"><h2 :class="cardTitle">Orders by status</h2></header>
                            <div class="p-5">
                                <p v-if="statusBreakdown.length === 0" class="py-4 text-center text-neutral-text/55">
                                    No orders in this period.
                                </p>
                                <ul v-else class="space-y-4">
                                    <li v-for="row in statusBreakdown" :key="row.status" class="space-y-2">
                                        <div class="flex items-center justify-between gap-3">
                                            <StatusBadge :status="row.status" />
                                            <span class="tabular-nums">
                                                <span class="font-medium text-neutral-text">{{ row.orders }}</span>
                                                <span class="ml-1.5 text-xs text-neutral-text/55">{{ Math.round(statusShare(row)) }}%</span>
                                            </span>
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-neutral-text/[0.07]">
                                            <div
                                                class="h-full rounded-full bg-primary transition-[width] duration-700 ease-out motion-reduce:transition-none"
                                                :style="{ width: (ready ? statusShare(row) : 0) + '%' }"
                                            />
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* One orchestrated entrance: the page settles in, bars grow up, the sparkline draws across. */
.page-in {
    animation: page-in 0.3s ease-out both;
}

.bar-grow {
    transform-origin: bottom;
    animation: bar-grow 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: calc(var(--i, 0) * 14ms + 150ms);
}

.spark-in {
    animation: spark-in 0.9s ease-out 0.25s both;
}

@keyframes page-in {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes bar-grow {
    from {
        transform: scaleY(0);
    }
    to {
        transform: scaleY(1);
    }
}

@keyframes spark-in {
    from {
        clip-path: inset(0 100% 0 0);
    }
    to {
        clip-path: inset(0 0 0 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .page-in,
    .bar-grow,
    .spark-in {
        animation: none;
    }
}
</style>
