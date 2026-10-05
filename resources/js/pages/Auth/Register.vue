<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />
    <div class="mb-7">
        <h1 class="font-heading text-xl font-semibold tracking-tight text-neutral-text sm:text-2xl">Create your account</h1>
        <p class="mt-1.5 text-sm text-neutral-text/60">Faster checkout, order tracking, and easy reorders.</p>
    </div>

    <form class="space-y-4" @submit.prevent="submit">
        <div>
            <InputLabel for="name" value="Full name" />
            <TextInput
                id="name"
                type="text"
                v-model="form.name"
                required
                autofocus
                autocomplete="name"
            />
            <InputError class="mt-1.5" :message="form.errors.name" />
        </div>

        <div>
            <InputLabel for="email" value="Email" />
            <TextInput
                id="email"
                type="email"
                v-model="form.email"
                required
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
                    autocomplete="new-password"
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

        <div>
            <InputLabel for="password_confirmation" value="Confirm password" />
            <div class="relative">
                <TextInput
                    id="password_confirmation"
                    :type="showPasswordConfirmation ? 'text' : 'password'"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    class="pr-10"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-neutral-text/40 transition-colors hover:text-primary-dark focus-visible:outline-none focus-visible:text-primary-dark"
                    :aria-label="showPasswordConfirmation ? 'Hide password' : 'Show password'"
                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                >
                    <svg v-if="!showPasswordConfirmation" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.3 20.3 0 0 1 5.06-6.06M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a20.3 20.3 0 0 1-3.22 4.48M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                </button>
            </div>
            <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
        </div>

        <PrimaryButton class="w-full" :class="{ 'opacity-60': form.processing }" :disabled="form.processing">
            {{ form.processing ? 'Creating account…' : 'Create account' }}
        </PrimaryButton>
    </form>

    <p class="mt-6 text-center text-sm text-neutral-text/60">
        Already have an account?
        <Link :href="route('login')" class="font-medium text-primary-dark hover:underline">Sign in</Link>
    </p>
</template>
