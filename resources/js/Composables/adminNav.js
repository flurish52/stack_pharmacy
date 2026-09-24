// resources/js/Composables/adminNav.js
//
// Pure data for the admin navigation. To add a page, add one item to NAV_SECTIONS.
// Visibility (permission / role / ready) is applied in useAdminNav.js.

// Inline icon set: no external icon dependency, one visual language throughout.
// Values are the inner markup of a 24x24 stroke SVG.
export const ICONS = {
    grid: '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
    list: '<rect x="3.5" y="4.5" width="17" height="3.6" rx="1"/><rect x="3.5" y="10.2" width="17" height="3.6" rx="1"/><rect x="3.5" y="15.9" width="11" height="3.6" rx="1"/>',
    box: '<path d="M3.5 8l8.5-4.6L20.5 8 12 12.6 3.5 8z"/><path d="M3.5 8v8l8.5 4.6 8.5-4.6V8"/><path d="M12 12.6V21"/>',
    tag: '<path d="M12 3.5h5.5a2 2 0 012 2V11L11 20.5 3.5 13 12 3.5z"/><circle cx="15.8" cy="7.2" r="1.3" fill="currentColor" stroke="none"/>',
    pin: '<path d="M12 21s6.5-6 6.5-11.2a6.5 6.5 0 10-13 0C5.5 15 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.2"/>',
    wrench: '<path d="M14.9 6.3a3.8 3.8 0 10-5 5L4.5 16.7l2.8 2.8 5.4-5.4a3.8 3.8 0 005-5l-2.5 2.5-1.9-1.9 2.6-2.4z"/>',
    cap: '<path d="M2.5 9L12 4.2 21.5 9 12 13.8 2.5 9z"/><path d="M6.3 10.9V16c0 1.4 2.6 2.8 5.7 2.8s5.7-1.4 5.7-2.8v-5.1"/>',
    chat: '<path d="M4.5 5.5h15a1 1 0 011 1V15a1 1 0 01-1 1H9.5l-4 3.5V16h-1a1 1 0 01-1-1V6.5a1 1 0 011-1z"/>',
    users: '<circle cx="9" cy="8.2" r="3"/><path d="M3 18.8c.5-3.2 2.8-5 6-5s5.5 1.8 6 5"/><circle cx="17.3" cy="9.2" r="2.4"/><path d="M15.6 13.6c2.4.4 4 2.1 4.4 5"/>',
    bars: '<rect x="4" y="12.5" width="3.4" height="7" rx="0.8"/><rect x="10.3" y="7.5" width="3.4" height="12" rx="0.8"/><rect x="16.6" y="4" width="3.4" height="15.5" rx="0.8"/>',
    history: '<path d="M4 12a8 8 0 118 8"/><path d="M4 12V7"/><path d="M4 12H9"/><path d="M12 8v4l3 2"/>',
    gear: '<circle cx="12" cy="12" r="3"/><path d="M12 3v3.2M12 17.8V21M21 12h-3.2M6.2 12H3M17.9 6.1l-2.3 2.3M8.4 15.6l-2.3 2.3M17.9 17.9l-2.3-2.3M8.4 8.4L6.1 6.1"/>',
    // Footer
    store: '<path d="M4 9.5 5.5 4h13L20 9.5"/><path d="M4 9.5c0 1.4 1.1 2.5 2.5 2.5S9 10.9 9 9.5c0 1.4 1.1 2.5 3 2.5s3-1.1 3-2.5c0 1.4 1.1 2.5 2.5 2.5S20 10.9 20 9.5"/><path d="M5.5 12v8h13v-8"/>',
    logout: '<path d="M9 4.5H6A1.5 1.5 0 0 0 4.5 6v12A1.5 1.5 0 0 0 6 19.5h3"/><path d="M15 8l4 4-4 4M19 12H9.5"/>',
}

export const NAV_SECTIONS = [
    {
        label: 'Overview',
        items: [{ name: 'Dashboard', href: '/admin/dashboard', match: '/admin/dashboard', ready: true, icon: 'grid' }],
    },
    {
        label: 'Sales',
        items: [
            { name: 'Orders', href: '/admin/orders', match: '/admin/orders', permission: 'view-orders', ready: true, icon: 'list' },
        ],
    },
    {
        label: 'Catalog',
        items: [
            { name: 'Products', href: '/admin/products', match: '/admin/products', permission: 'manage-products', ready: true, icon: 'box' },
            { name: 'Categories', href: '/admin/categories', match: '/admin/categories', permission: 'manage-categories', ready: true, icon: 'tag' },
            { name: 'Pickup Points', href: '/admin/pickup-points', match: '/admin/pickup-points', permission: 'manage-pickup-points', ready: true, icon: 'pin' },
        ],
    },
    {
        label: 'Content',
        items: [
            { name: 'Services', href: '/admin/services', match: '/admin/services', permission: 'manage-services', ready: true, icon: 'wrench' },
            { name: 'Training', href: '/admin/training', match: '/admin/training', permission: 'manage-training', ready: true, icon: 'cap' },
            { name: 'Contact Channels', href: '/admin/contact-channels', match: '/admin/contact-channels', permission: 'manage-contact', ready: true, icon: 'chat' },
        ],
    },
    {
        label: 'Administration',
        items: [
            { name: 'Staff', href: '/admin/staff', match: '/admin/staff', permission: 'manage-staff', ready: true, icon: 'users' },
            { name: 'Reports', href: '/admin/reports', match: '/admin/reports', permission: 'view-reports', ready: true, icon: 'bars' },
            { name: 'Activity Log', href: '/admin/activity-log', match: '/admin/activity-log', permission: 'view-activity-log', ready: true, icon: 'history' },
            { name: 'Settings', href: '/admin/settings', match: '/admin/settings', role: 'super_admin', ready: false, icon: 'gear' },
        ],
    },
]
