// resources/js/Composables/useAdminNav.js
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { NAV_SECTIONS } from '@/Composables/adminNav.js'

export function useAdminNav() {
    const page = usePage()

    const auth = computed(() => page.props.auth ?? {})
    const permissions = computed(() => auth.value.permissions ?? [])
    const roles = computed(() => auth.value.roles ?? [])
    const user = computed(() => auth.value.user ?? null)

    const can = (permission) => !permission || permissions.value.includes(permission)
    const hasRole = (role) => !role || roles.value.includes(role)

    // Only sections with at least one item the current user may see.
    const sections = computed(() =>
        NAV_SECTIONS.map((section) => ({
            ...section,
            items: section.items.filter((item) => item.ready && can(item.permission) && hasRole(item.role)),
        })).filter((section) => section.items.length > 0),
    )

    // '/admin/orders' is active for '/admin/orders/12' and '/admin/orders?status=paid',
    // but not for a sibling like '/admin/orders-archive'.
    const isActive = (item) => {
        const url = page.url
        return url === item.match || url.startsWith(`${item.match}/`) || url.startsWith(`${item.match}?`)
    }

    const roleLabel = computed(() => (roles.value[0] ?? '').replaceAll('_', ' '))

    const initials = computed(
        () =>
            (user.value?.name ?? '')
                .split(' ')
                .filter(Boolean)
                .slice(0, 2)
                .map((part) => part[0])
                .join('')
                .toUpperCase() || 'U',
    )

    return { sections, isActive, user, roleLabel, initials }
}
