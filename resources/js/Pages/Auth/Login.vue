<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/me/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion" />

    <main class="login-page">
        <div class="login-card">
            <!-- Volet institutionnel -->
            <aside class="login-brand">
                <div class="login-mark" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 21V9" />
                        <path d="M12 12.5C12 8.5 9 6.2 4.5 6.2c0 4 3 6.3 7.5 6.3Z" />
                        <path d="M12 10c0-3.6 3-5.8 7.5-5.8 0 3.6-3 5.8-7.5 5.8Z" />
                    </svg>
                </div>
                <div>
                    <h2>EDEN-UMG</h2>
                    <p class="login-brand-caption">École Doctorale Écosystèmes Naturels — Université de Mahajanga</p>
                </div>
                <p class="login-brand-text">
                    Espace d'administration du site. Connectez-vous pour gérer les contenus,
                    les images et les réglages de l'École Doctorale.
                </p>
                <ul class="login-points">
                    <li class="login-point">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5" /></svg>
                        <span>Édition du contenu en direct</span>
                    </li>
                    <li class="login-point">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5" /></svg>
                        <span>Gestion des images et du référencement</span>
                    </li>
                    <li class="login-point">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5" /></svg>
                        <span>Accès sécurisé et réservé</span>
                    </li>
                </ul>
            </aside>

            <!-- Formulaire -->
            <div class="login-form">
                <span class="eyebrow">Administration</span>
                <h1 class="login-title">Connexion</h1>
                <p class="login-subtitle">Accédez à votre espace pour gérer le site.</p>

                <form @submit.prevent="submit">
                    <div class="login-field">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            class="login-input"
                            type="email"
                            autocomplete="email"
                            required
                            autofocus
                            placeholder="vous@exemple.mg"
                        >
                        <span v-if="form.errors.email" class="login-error">{{ form.errors.email }}</span>
                    </div>

                    <div class="login-field">
                        <label for="password">Mot de passe</label>
                        <div class="login-password">
                            <input
                                id="password"
                                v-model="form.password"
                                class="login-input"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                placeholder="••••••••"
                            >
                            <button
                                type="button"
                                class="login-eye"
                                :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                                :title="showPassword ? 'Masquer' : 'Afficher'"
                                @click="showPassword = !showPassword"
                            >
                                <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 4.2A11 11 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19M6.6 6.6A18.6 18.6 0 0 0 2 11s3.5 7 10 7a11 11 0 0 0 3.6-.6" /><path d="m3 3 18 18" /></svg>
                            </button>
                        </div>
                        <span v-if="form.errors.password" class="login-error">{{ form.errors.password }}</span>
                    </div>

                    <label class="login-remember">
                        <input v-model="form.remember" type="checkbox">
                        <span>Rester connecté</span>
                    </label>

                    <button class="btn btn-primary login-submit" type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Connexion...' : 'Se connecter' }}
                    </button>
                </form>

                <Link href="/" class="login-back">← Retour au site</Link>
            </div>
        </div>
    </main>
</template>
