<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    form: { type: Object, required: true }, // an Inertia useForm() object
    categories: { type: Array, default: () => [] },
    autoSlug: { type: Boolean, default: false }, // fill slug from name (create page)
    submitLabel: { type: String, default: 'Save product' },
})

const emit = defineEmits(['submit'])

const slugify = (text) =>
    text.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')

const slugEdited = ref(false)

watch(
    () => props.form.name,
    (name) => {
        if (props.autoSlug && !slugEdited.value) props.form.slug = slugify(name ?? '')
    },
)

const toggleCategory = (id) => {
    const ids = props.form.category_ids
    if (ids.includes(id)) {
        props.form.category_ids = ids.filter((i) => i !== id)
        if (props.form.primary_category_id === id) {
            props.form.primary_category_id = props.form.category_ids[0] ?? null
        }
    } else {
        props.form.category_ids = [...ids, id]
        if (!props.form.primary_category_id) props.form.primary_category_id = id
    }
}

const addVariant = () =>
    props.form.variants.push({ id: null, variant_name: '', sku: '', price: '', stock_quantity: 0 })

const removeVariant = (index) => {
    if (props.form.variants.length > 1) props.form.variants.splice(index, 1)
}

const err = (key) => props.form.errors[key]

const input =
    'w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500'
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <!-- Details -->
        <section class="rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="mb-4 font-semibold">Details</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Name</label>
                    <input v-model="form.name" type="text" :class="input" />
                    <p v-if="err('name')" class="mt-1 text-xs text-red-600">{{ err('name') }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Slug</label>
                    <input v-model="form.slug" type="text" :class="input" @input="slugEdited = true" />
                    <p class="mt-1 text-xs text-gray-400">Used in the shop link. Leave blank to build it from the name.</p>
                    <p v-if="err('slug')" class="mt-1 text-xs text-red-600">{{ err('slug') }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Status</label>
                    <select v-model="form.status" :class="input">
                        <option value="active">Active (visible in the shop)</option>
                        <option value="draft">Draft (hidden)</option>
                        <option value="archived">Archived (hidden)</option>
                    </select>
                    <p v-if="err('status')" class="mt-1 text-xs text-red-600">{{ err('status') }}</p>
                </div>
            </div>
            <div class="mt-4">
                <label class="mb-1 block text-sm font-medium">Description</label>
                <textarea v-model="form.description" rows="5" :class="input" />
                <p v-if="err('description')" class="mt-1 text-xs text-red-600">{{ err('description') }}</p>
            </div>
        </section>

        <!-- Categories -->
        <section class="rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="mb-1 font-semibold">Categories</h2>
            <p class="mb-4 text-sm text-gray-500">
                Pick at least one. The primary category is used for breadcrumbs.
            </p>
            <div class="grid gap-2 sm:grid-cols-2">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="flex items-center justify-between rounded-md border px-3 py-2 text-sm"
                    :class="form.category_ids.includes(category.id) ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200'"
                >
                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            type="checkbox"
                            :checked="form.category_ids.includes(category.id)"
                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                            @change="toggleCategory(category.id)"
                        />
                        {{ category.name }}
                    </label>
                    <label v-if="form.category_ids.includes(category.id)" class="flex cursor-pointer items-center gap-1 text-xs text-gray-600">
                        <input
                            v-model="form.primary_category_id"
                            type="radio"
                            :value="category.id"
                            class="text-emerald-600 focus:ring-emerald-500"
                        />
                        Primary
                    </label>
                </div>
            </div>
            <p v-if="err('category_ids')" class="mt-2 text-xs text-red-600">{{ err('category_ids') }}</p>
            <p v-if="err('primary_category_id')" class="mt-2 text-xs text-red-600">{{ err('primary_category_id') }}</p>
        </section>

        <!-- Variants -->
        <section class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="mb-1 flex items-center justify-between">
                <h2 class="font-semibold">Variants</h2>
                <button type="button" class="text-sm font-medium text-emerald-700 hover:underline" @click="addVariant">
                    Add variant
                </button>
            </div>
            <p class="mb-4 text-sm text-gray-500">
                Price and stock live on the variant. A variant at zero stock stays here but is hidden from customers.
                Removing a variant hides it from the shop; past orders keep it.
            </p>
            <p v-if="err('variants')" class="mb-3 text-xs text-red-600">{{ err('variants') }}</p>

            <div class="space-y-3">
                <div
                    v-for="(variant, index) in form.variants"
                    :key="variant.id ?? `new-${index}`"
                    class="rounded-md border border-gray-200 p-4"
                >
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Variant name</label>
                            <input v-model="variant.variant_name" type="text" placeholder="500mg - 30 tablets" :class="input" />
                            <p v-if="err(`variants.${index}.variant_name`)" class="mt-1 text-xs text-red-600">
                                {{ err(`variants.${index}.variant_name`) }}
                            </p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">SKU</label>
                            <input v-model="variant.sku" type="text" :class="input" />
                            <p v-if="err(`variants.${index}.sku`)" class="mt-1 text-xs text-red-600">
                                {{ err(`variants.${index}.sku`) }}
                            </p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Price (NGN)</label>
                            <input v-model="variant.price" type="number" min="0" step="0.01" :class="input" />
                            <p v-if="err(`variants.${index}.price`)" class="mt-1 text-xs text-red-600">
                                {{ err(`variants.${index}.price`) }}
                            </p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Stock</label>
                            <input v-model="variant.stock_quantity" type="number" min="0" step="1" :class="input" />
                            <p v-if="err(`variants.${index}.stock_quantity`)" class="mt-1 text-xs text-red-600">
                                {{ err(`variants.${index}.stock_quantity`) }}
                            </p>
                        </div>
                    </div>
                    <button
                        v-if="form.variants.length > 1"
                        type="button"
                        class="mt-3 text-xs text-red-600 hover:underline"
                        @click="removeVariant(index)"
                    >
                        Remove variant
                    </button>
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <slot name="cancel" />
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
            >
                {{ form.processing ? 'Saving...' : submitLabel }}
            </button>
        </div>
    </form>
</template>
