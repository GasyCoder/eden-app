<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { buttonVariants, inputClass } from '@/lib/variants';

const props = defineProps({ token: { type: String, required: true }, email: { type: String, default: null }, valid: { type: Boolean, default: false } });
const form = useForm({ password: '', password_confirmation: '' });
const submit = () => form.post(`/invitation/${props.token}`);
</script>

<template>
    <Head title="Confirmer votre invitation" />
    <main class="grid min-h-dvh place-items-center bg-muted/40 px-4 py-10">
        <section class="w-full max-w-md rounded-xl border bg-card p-6 shadow-sm sm:p-8">
            <template v-if="valid">
                <p class="text-sm font-semibold text-eden-700">EDEN-UMG</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight">Confirmez votre compte</h1>
                <p class="mt-2 text-sm text-muted-foreground">Choisissez un mot de passe pour <strong class="break-all text-foreground">{{ email }}</strong>.</p>
                <form class="mt-6 grid gap-4" @submit.prevent="submit">
                    <label class="grid gap-1.5 text-sm font-medium">Mot de passe (12 caractères minimum)
                        <input v-model="form.password" :class="inputClass" type="password" autocomplete="new-password" required minlength="12">
                        <span v-if="form.errors.password" class="text-xs text-destructive">{{ form.errors.password }}</span>
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium">Confirmer le mot de passe
                        <input v-model="form.password_confirmation" :class="inputClass" type="password" autocomplete="new-password" required minlength="12">
                    </label>
                    <p v-if="form.errors.token" class="text-sm text-destructive" role="alert">{{ form.errors.token }}</p>
                    <button :class="buttonVariants()" :disabled="form.processing">{{ form.processing ? 'Confirmation…' : 'Confirmer et créer mon mot de passe' }}</button>
                </form>
            </template>
            <template v-else>
                <h1 class="text-2xl font-semibold tracking-tight">Lien invalide</h1>
                <p class="mt-2 text-sm text-muted-foreground">Ce lien d’invitation a expiré ou a déjà été utilisé. Contactez un administrateur pour recevoir une nouvelle invitation.</p>
            </template>
        </section>
    </main>
</template>
