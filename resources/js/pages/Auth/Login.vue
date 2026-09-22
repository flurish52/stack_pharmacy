<script setup>
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div
        v-if="status"
        class="mb-5 flex items-center gap-2 rounded-md bg-primary-light px-3.5 py-2.5 text-sm font-medium text-primary-dark"
    >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M20 6 9 17l-5-5" />
        </svg>
        {{ status }}
    </div>

    <div class="mb-7">
        <h1 class="font-heading text-xl font-semibold tracking-tight text-neutral-text sm:text-2xl">Welcome back!</h1>
        <p  class="mt-1.5 text-sm text-neutral-text/60">Sign in to manage your orders and prescriptions.</p>
    </div>

    <form class="space-y-4" @submit.prevent="submit">
        <div>
            <InputLabel for="email" value="Email" />
            <TextInput
                id="email"
                type="email"
                v-model="form.email"
                required
                autofocus
                autocomplete="username"
            />
            <InputError class="mt-1.5" :message="form.errors.email" />
        </div>

        <div>
            <InputLabel for="password" value="Password" />
            <div class="relative">
                <TextInput
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    class="pr-10"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-neutral-text/40 transition-colors hover:text-primary-dark focus-visible:outline-none focus-visible:text-primary-dark"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    @click="showPassword = !showPassword"
                >
                    <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.3 20.3 0 0 1 5.06-6.06M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a20.3 20.3 0 0 1-3.22 4.48M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                </button>
            </div>
            <InputError class="mt-1.5" :message="form.errors.password" />
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="text-sm text-neutral-text/70">Remember me</span>
            </label>

            <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-sm font-medium text-primary-dark hover:underline"
            >
                Forgot password?
            </Link>
        </div>

        <PrimaryButton class="w-full" :class="{ 'opacity-60': form.processing }" :disabled="form.processing">
            {{ form.processing ? 'Signing in…' : 'Sign in' }}
        </PrimaryButton>
    </form>

    <p class="mt-6 text-center text-sm text-neutral-text/60">
        New to Stack Pharmacy?
        <Link :href="route('register')" class="font-medium text-primary-dark hover:underline">Create an account</Link>
    </p>
</template>
