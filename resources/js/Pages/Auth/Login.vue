<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Eye, EyeOff } from '@lucide/vue';
import { ref } from 'vue';
import BrandStripe from '@/Components/Layout/BrandStripe.vue';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass } from '@/lib/variants';

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(window.location.pathname, {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion"><meta name="robots" content="noindex, nofollow, noarchive"></Head>

    <main id="main" class="flex min-h-dvh flex-col bg-muted">
        <BrandStripe />
        <div class="flex flex-1 flex-col items-center justify-center gap-6 px-4 py-10">
            <div class="w-full max-w-sm rounded-xl border bg-card p-6 shadow-sm sm:p-8">
                <Link href="/" class="flex items-center gap-2.5 rounded-md outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50">
                    <span class="grid size-9 place-items-center rounded-lg bg-eden-600 text-white" aria-hidden="true">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9" /><path d="M12 12.5C12 8.5 9 6.2 4.5 6.2c0 4 3 6.3 7.5 6.3Z" /><path d="M12 10c0-3.6 3-5.8 7.5-5.8 0 3.6-3 5.8-7.5 5.8Z" /></svg>
                    </span>
                    <span class="grid leading-tight">
                        <span class="text-sm font-bold">EDEN</span>
                        <span class="text-xs text-muted-foreground">École Doctorale Écosystèmes Naturels</span>
                    </span>
                </Link>

                <div class="mt-7">
                    <h1 class="text-2xl font-semibold tracking-tight">Connexion</h1>
                    <p class="mt-1.5 text-sm text-muted-foreground">Espace réservé à l’équipe qui gère le site.</p>
                </div>

                <form class="mt-6 grid gap-4" novalidate @submit.prevent="submit">
                    <div class="grid gap-2">
                        <label class="text-sm font-medium" for="email">Adresse e-mail</label>
                        <input id="email" v-model="form.email" :class="inputClass" type="email" autocomplete="email" required autofocus placeholder="vous@exemple.mg" :aria-invalid="form.errors.email ? 'true' : null" aria-describedby="email-error">
                        <p v-if="form.errors.email" id="email-error" class="text-xs font-medium text-destructive" role="alert">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid gap-2">
                        <label class="text-sm font-medium" for="password">Mot de passe</label>
                        <div class="relative">
                            <input id="password" v-model="form.password" :class="cn(inputClass, 'pr-11')" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required :aria-invalid="form.errors.password ? 'true' : null" aria-describedby="password-error">
                            <button
                                type="button"
                                :class="cn(buttonVariants({ variant: 'ghost', size: 'icon-sm' }), 'absolute top-1/2 right-1 -translate-y-1/2 text-muted-foreground')"
                                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                                :aria-pressed="showPassword ? 'true' : 'false'"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" aria-hidden="true" />
                                <Eye v-else aria-hidden="true" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" id="password-error" class="text-xs font-medium text-destructive" role="alert">{{ form.errors.password }}</p>
                    </div>

                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm">
                        <input v-model="form.remember" type="checkbox" class="size-4 accent-eden-600">
                        Rester connecté sur cet appareil
                    </label>

                    <button :class="cn(buttonVariants(), 'mt-2 w-full')" type="submit" :disabled="form.processing">
                        <span v-if="form.processing" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        {{ form.processing ? 'Connexion…' : 'Se connecter' }}
                    </button>
                </form>
            </div>

            <Link href="/" :class="buttonVariants({ variant: 'ghost', size: 'sm' })"><ArrowLeft aria-hidden="true" />Retour au site</Link>
        </div>
    </main>
</template>
