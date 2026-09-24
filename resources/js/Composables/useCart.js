import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { reactive } from 'vue'

/**
 * Session cart, front end side.
 *
 * Nothing is kept in JS state — the session is the single source of truth.
 * Every mutation is an Inertia visit with `preserveScroll`, so the shared
 * `cart` prop comes back updated and the header re-renders on its own.
 *
 * `pending` only tracks which variant currently has a request in flight,
 * so a button can show a spinner without the page feeling frozen.
 */
const pending = reactive(new Set())

export function useCart() {
    const page = usePage()

    const cart = computed(() => page.props.cart ?? { count: 0, total: 0, items: {} })
    const count = computed(() => Number(cart.value.count ?? 0))
    const total = computed(() => Number(cart.value.total ?? 0))

    /** Quantity of a given variant currently in the cart (0 when absent). */
    const quantityOf = (variantId) => Number(cart.value.items?.[variantId] ?? 0)
    const isInCart = (variantId) => quantityOf(variantId) > 0
    const isPending = (variantId) => pending.has(variantId)

    const visit = (method, url, data = {}, options = {}) => {
        const key = options.key
        if (key) pending.add(key)

        const inertiaOptions = {
            preserveScroll: true,
            preserveState: true,
            // Removed the hardcoded `only` — let Inertia refresh whatever
            // props the current page actually has. If you want to keep it
            // lean later, pass `only` explicitly per-call via options.only
            // instead of forcing 'cart'/'items'/'total' on every visit.
            ...(options.only ? { only: options.only } : {}),
            ...options.inertia,
            onFinish: () => {
                if (key) pending.delete(key)
                options.onFinish?.()
            },
        }

        if (method === 'delete') {
            router.delete(url, { ...inertiaOptions, data })
        } else {
            router[method](url, data, inertiaOptions)
        }
    }

    const add = (variantId, quantity = 1) =>
        visit('post', route('cart.add'), { variant_id: variantId, quantity }, { key: variantId })

    /** Absolute quantity, not a delta — matches CartController@update. */
    const setQuantity = (variantId, quantity, options = {}) =>
        visit('patch', route('cart.update', variantId), { quantity }, { key: variantId, ...options })

    const remove = (variantId) =>
        visit('delete', route('cart.destroy', variantId), {}, { key: variantId })

    const clear = () => visit('delete', route('cart.clear'))

    return { cart, count, total, quantityOf, isInCart, isPending, add, setQuantity, remove, clear }
}
