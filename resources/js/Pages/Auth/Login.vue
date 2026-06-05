<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion" />

    <main class="min-h-screen bg-[var(--beige)]" style="padding: 5rem 1.25rem;">
        <form class="card" style="max-width: 420px; margin: 0 auto;" @submit.prevent="submit">
            <span class="eyebrow">Administration</span>
            <h1 style="font-size:1.65rem;margin-bottom:1.5rem;">Connexion EDEN</h1>

            <div style="display:flex;flex-direction:column;gap:1rem;">
                <label>
                    <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Email</span>
                    <input v-model="form.email" type="email" autocomplete="email" required style="width:100%;border:1px solid var(--border);border-radius:var(--r);padding:.7rem .85rem;background:var(--white);">
                    <span v-if="form.errors.email" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ form.errors.email }}</span>
                </label>

                <label>
                    <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Mot de passe</span>
                    <input v-model="form.password" type="password" autocomplete="current-password" required style="width:100%;border:1px solid var(--border);border-radius:var(--r);padding:.7rem .85rem;background:var(--white);">
                    <span v-if="form.errors.password" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ form.errors.password }}</span>
                </label>

                <label style="display:flex;align-items:center;gap:.5rem;font-size:.88rem;color:var(--g700);">
                    <input v-model="form.remember" type="checkbox">
                    <span>Rester connecté</span>
                </label>

                <button class="btn btn-primary" type="submit" :disabled="form.processing" style="justify-content:center;">
                    Se connecter
                </button>
            </div>
        </form>
    </main>
</template>
