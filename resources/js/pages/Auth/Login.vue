<script setup>
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
                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
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
