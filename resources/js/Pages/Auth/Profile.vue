<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});
const flash = computed(() => page.props.flash ?? {});

const roleLabel = computed(() => (user.value.role === 'superadmin' ? 'Super administrateur' : 'Éditeur'));

const profileForm = useForm({
    name: user.value.name ?? '',
    email: user.value.email ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitProfile = () => profileForm.patch('/me/profile', { preserveScroll: true });

const submitPassword = () => passwordForm.put('/me/password', {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
});
</script>

<template>
    <Head title="Mon profil" />

    <main class="min-h-screen" style="background: var(--beige); padding: 4.5rem 1.25rem;">
        <div style="max-width: 560px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                <div>
                    <span class="eyebrow">Mon compte</span>
                    <h1 style="font-size:1.7rem;">Profil administrateur</h1>
                </div>
                <Link href="/" class="btn btn-secondary" style="padding:.55rem 1.1rem;">← Retour au site</Link>
            </div>

            <div v-if="flash.success" class="eden-admin-flash eden-admin-flash-success" style="position:static;max-width:none;">
                {{ flash.success }}
            </div>

            <!-- Rôle (lecture seule) -->
            <div class="card" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                <div>
                    <span style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--g400);">Rôle</span>
                    <strong style="font-family:var(--fh);font-size:1.05rem;color:var(--green);">{{ roleLabel }}</strong>
                </div>
                <span style="font-size:.84rem;color:var(--g500);max-width:260px;text-align:right;">
                    {{ user.role === 'superadmin' ? 'Accès complet à tous les contenus.' : 'Accès à tous les contenus sauf les mentions légales du footer.' }}
                </span>
            </div>

            <!-- Identité / email -->
            <form class="card" @submit.prevent="submitProfile">
                <h2 style="font-size:1.2rem;margin-bottom:1.25rem;">Identité & accès</h2>
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    <label>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Nom</span>
                        <input v-model="profileForm.name" type="text" required class="eden-edit-input" style="min-width:0;">
                        <span v-if="profileForm.errors.name" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ profileForm.errors.name }}</span>
                    </label>
                    <label>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Email d'accès</span>
                        <input v-model="profileForm.email" type="email" autocomplete="email" required class="eden-edit-input" style="min-width:0;">
                        <span v-if="profileForm.errors.email" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ profileForm.errors.email }}</span>
                    </label>
                    <div>
                        <button class="btn btn-primary" type="submit" :disabled="profileForm.processing" style="justify-content:center;">Enregistrer</button>
                    </div>
                </div>
            </form>

            <!-- Mot de passe -->
            <form class="card" @submit.prevent="submitPassword">
                <h2 style="font-size:1.2rem;margin-bottom:1.25rem;">Changer le mot de passe</h2>
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    <label>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Mot de passe actuel</span>
                        <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" required class="eden-edit-input" style="min-width:0;">
                        <span v-if="passwordForm.errors.current_password" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ passwordForm.errors.current_password }}</span>
                    </label>
                    <label>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Nouveau mot de passe</span>
                        <input v-model="passwordForm.password" type="password" autocomplete="new-password" required class="eden-edit-input" style="min-width:0;">
                        <span v-if="passwordForm.errors.password" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ passwordForm.errors.password }}</span>
                    </label>
                    <label>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Confirmer le nouveau mot de passe</span>
                        <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" required class="eden-edit-input" style="min-width:0;">
                    </label>
                    <div>
                        <button class="btn btn-primary" type="submit" :disabled="passwordForm.processing" style="justify-content:center;">Mettre à jour le mot de passe</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</template>
