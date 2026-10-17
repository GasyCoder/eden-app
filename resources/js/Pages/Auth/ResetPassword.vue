<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, KeyRound } from '@lucide/vue';
import BrandStripe from '@/Components/Layout/BrandStripe.vue';
import Button from '@/Components/ui/Button.vue';
import { inputClass } from '@/lib/variants';

const props = defineProps({ token: { type: String, required: true }, email: { type: String, default: '' } });
const form = useForm({ token: props.token, email: props.email, password: '', password_confirmation: '' });
const submit = () => form.post('/password/reset', { onFinish: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <Head title="Réinitialiser le mot de passe"><meta name="robots" content="noindex, nofollow, noarchive"></Head>
    <main class="flex min-h-dvh flex-col bg-muted">
        <BrandStripe />
        <div class="flex flex-1 items-center justify-center px-4 py-10">
            <section class="w-full max-w-md rounded-xl border bg-card p-6 shadow-sm sm:p-8">
                <span class="grid size-11 place-items-center rounded-lg bg-primary/10 text-primary"><KeyRound class="size-5" aria-hidden="true" /></span>
                <h1 class="mt-5 text-2xl font-semibold tracking-tight">Nouveau mot de passe</h1>
                <p class="mt-1.5 text-sm text-muted-foreground">Définissez un nouveau mot de passe pour votre compte EDEN.</p>
                <form class="mt-6 grid gap-4" @submit.prevent="submit">
                    <label class="grid gap-2 text-sm font-medium">Adresse e-mail
                        <input v-model="form.email" :class="inputClass" type="email" autocomplete="email" required>
                        <span v-if="form.errors.email" class="text-xs font-medium text-destructive" role="alert">{{ form.errors.email }}</span>
                    </label>
                    <label class="grid gap-2 text-sm font-medium">Nouveau mot de passe (12 caractères minimum)
                        <input v-model="form.password" :class="inputClass" type="password" autocomplete="new-password" minlength="12" required>
                        <span v-if="form.errors.password" class="text-xs font-medium text-destructive" role="alert">{{ form.errors.password }}</span>
                    </label>
                    <label class="grid gap-2 text-sm font-medium">Confirmer le mot de passe
                        <input v-model="form.password_confirmation" :class="inputClass" type="password" autocomplete="new-password" minlength="12" required>
                    </label>
                    <Button type="submit" :loading="form.processing" class="w-full">Réinitialiser le mot de passe</Button>
                </form>
                <Link href="/me/login" class="mt-5 inline-flex min-h-10 items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" aria-hidden="true" />Retour à la connexion</Link>
            </section>
        </div>
    </main>
</template>
