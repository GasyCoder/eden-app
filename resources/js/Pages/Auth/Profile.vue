<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    seo: {
        type: Object,
        default: () => ({}),
    },
});

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

// Référencement & favicon (superadmin uniquement)
const settingsForm = useForm({
    title: props.seo.title ?? '',
    description: props.seo.description ?? '',
    keywords: props.seo.keywords ?? '',
    favicon: null,
    og_image: null,
});

const isUploadingFavicon = computed(() => settingsForm.processing && !!settingsForm.favicon);
const isUploadingOgImage = computed(() => settingsForm.processing && !!settingsForm.og_image);

const onFavicon = (event) => {
    settingsForm.favicon = event.target.files?.[0] ?? null;
};

const onOgImage = (event) => {
    settingsForm.og_image = event.target.files?.[0] ?? null;
};

const submitSettings = () => settingsForm.post('/me/settings', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
        settingsForm.favicon = null;
        settingsForm.og_image = null;
    },
});
</script>

<template>
    <Head title="Mon profil" />

    <main class="min-h-screen" style="background: var(--beige); padding: 4.5rem 1.25rem;">
        <div style="max-width: 880px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                <div>
                    <span class="eyebrow">Mon compte</span>
                    <h1 style="font-size:1.7rem;">Profil administrateur</h1>
                </div>
                <Link href="/" class="btn btn-secondary" style="padding:.55rem 1.1rem;">← Retour au site</Link>
            </div>

            <Toast />

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
                        <button class="btn btn-primary" type="submit" :disabled="profileForm.processing" style="justify-content:center;">
                            {{ profileForm.processing ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
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
                        <button class="btn btn-primary" type="submit" :disabled="passwordForm.processing" style="justify-content:center;">
                            {{ passwordForm.processing ? 'Mise à jour...' : 'Mettre à jour le mot de passe' }}
                        </button>
                    </div>
                </div>
            </form>

            <!-- Référencement & favicon — superadmin uniquement -->
            <form v-if="user.role === 'superadmin'" class="card" @submit.prevent="submitSettings">
                <h2 style="font-size:1.2rem;margin-bottom:.35rem;">Référencement &amp; favicon</h2>
                <p style="font-size:.84rem;color:var(--g500);margin-bottom:1.5rem;">
                    Réservé au super administrateur. Ces informations apparaissent dans les moteurs de recherche
                    et lors du partage du lien (Facebook, WhatsApp…).
                </p>
                <div style="display:flex;flex-direction:column;gap:1.1rem;">
                    <label>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Titre du site (onglet &amp; partage)</span>
                        <input v-model="settingsForm.title" type="text" class="eden-edit-input" style="min-width:0;">
                        <span v-if="settingsForm.errors.title" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ settingsForm.errors.title }}</span>
                    </label>
                    <label>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Description (meta description)</span>
                        <textarea v-model="settingsForm.description" rows="3" class="eden-edit-textarea" style="min-height:auto;"></textarea>
                        <span v-if="settingsForm.errors.description" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ settingsForm.errors.description }}</span>
                    </label>
                    <label>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.35rem;">Mots-clés (séparés par des virgules)</span>
                        <textarea v-model="settingsForm.keywords" rows="2" class="eden-edit-textarea" style="min-height:auto;"></textarea>
                        <span v-if="settingsForm.errors.keywords" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ settingsForm.errors.keywords }}</span>
                    </label>

                    <div>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.5rem;">Favicon (PNG carré recommandé)</span>
                        <div style="display:flex;align-items:center;gap:1rem;">
                            <span class="eden-file-preview" :class="{ 'is-uploading': isUploadingFavicon }" style="width:40px;height:40px;">
                                <img :src="seo.favicon" alt="Favicon actuel" width="40" height="40" loading="lazy" decoding="async" style="width:40px;height:40px;border:1px solid var(--border);border-radius:8px;object-fit:contain;background:#fff;">
                                <span v-if="isUploadingFavicon" class="eden-upload-skeleton small" role="status" aria-live="polite">
                                    <span class="eden-upload-content">
                                        <span class="eden-upload-spinner"></span>
                                    </span>
                                </span>
                            </span>
                            <input type="file" accept="image/png,image/jpeg,image/webp" :disabled="settingsForm.processing" @change="onFavicon">
                        </div>
                        <span v-if="settingsForm.errors.favicon" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ settingsForm.errors.favicon }}</span>
                    </div>

                    <div>
                        <span style="display:block;font-size:.82rem;color:var(--g500);margin-bottom:.5rem;">Image de partage (Open Graph — 1200×630 conseillé)</span>
                        <span class="eden-file-preview" :class="{ 'is-uploading': isUploadingOgImage }" style="width:100%;max-width:340px;">
                            <img :src="seo.ogImage" alt="Image de partage actuelle" width="340" height="179" loading="lazy" decoding="async" style="width:100%;max-width:340px;border:1px solid var(--border);border-radius:8px;display:block;">
                            <span v-if="isUploadingOgImage" class="eden-upload-skeleton" role="status" aria-live="polite">
                                <span class="eden-upload-content">
                                    <span class="eden-upload-spinner"></span>
                                    <span>Téléversement...</span>
                                </span>
                            </span>
                        </span>
                        <input type="file" accept="image/png,image/jpeg,image/webp" style="margin-top:.6rem;" :disabled="settingsForm.processing" @change="onOgImage">
                        <span v-if="settingsForm.errors.og_image" style="display:block;color:#b91c1c;font-size:.8rem;margin-top:.35rem;">{{ settingsForm.errors.og_image }}</span>
                    </div>

                    <div>
                        <button class="btn btn-primary" type="submit" :disabled="settingsForm.processing" style="justify-content:center;">
                            {{ settingsForm.processing ? 'Enregistrement...' : 'Enregistrer le référencement' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</template>
