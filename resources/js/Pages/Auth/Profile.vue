<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    seo: {
        type: Object,
        default: () => ({}),
    },
    theme: {
        type: Object,
        default: () => ({ sections: {}, colors: {} }),
    },
    editors: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});
const isSuperAdmin = computed(() => user.value.role === 'superadmin');
const roleLabel = computed(() => (isSuperAdmin.value ? 'Super administrateur' : 'Éditeur'));
const roleDescription = computed(() => (
    isSuperAdmin.value
        ? 'Accès complet aux contenus, réglages SEO, apparence et comptes éditeurs.'
        : 'Accès aux contenus éditables du site, sauf les réglages réservés au superadmin.'
));
const editorsCount = computed(() => props.editors.length);
const userInitial = computed(() => (user.value.name ?? user.value.email ?? 'A').charAt(0).toUpperCase());

const profileForm = useForm({
    name: user.value.name ?? '',
    email: user.value.email ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const profilePhotoForm = useForm({
    photo: null,
});

const profilePhotoPreview = ref(user.value.profile_photo_url ?? null);
let profilePhotoObjectUrl = null;

watch(() => user.value.profile_photo_url, (url) => {
    if (!profilePhotoForm.photo) {
        profilePhotoPreview.value = url ?? null;
    }
});

const editorForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submitProfile = () => profileForm
    .transform((data) => ({ ...data, _method: 'PATCH' }))
    .post('/me/profile', {
        preserveScroll: true,
        onFinish: () => profileForm.transform((data) => data),
    });

const submitPassword = () => passwordForm
    .transform((data) => ({ ...data, _method: 'PUT' }))
    .post('/me/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onFinish: () => passwordForm.transform((data) => data),
    });

const onProfilePhoto = (event) => {
    const file = event.target.files?.[0] ?? null;
    profilePhotoForm.photo = file;

    if (profilePhotoObjectUrl) {
        URL.revokeObjectURL(profilePhotoObjectUrl);
        profilePhotoObjectUrl = null;
    }

    if (file) {
        profilePhotoObjectUrl = URL.createObjectURL(file);
        profilePhotoPreview.value = profilePhotoObjectUrl;
        return;
    }

    profilePhotoPreview.value = user.value.profile_photo_url ?? null;
};

const submitProfilePhoto = () => profilePhotoForm.post('/me/profile/photo', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
        profilePhotoForm.reset('photo');

        if (profilePhotoObjectUrl) {
            URL.revokeObjectURL(profilePhotoObjectUrl);
            profilePhotoObjectUrl = null;
        }

        profilePhotoPreview.value = user.value.profile_photo_url ?? profilePhotoPreview.value;
    },
});

const submitEditor = () => editorForm.post('/me/users', {
    preserveScroll: true,
    onSuccess: () => editorForm.reset(),
});

const editingEditorId = ref(null);
const editorUpdateForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
const editorDeleteForm = useForm({});
const editorPendingDelete = ref(null);

const startEditEditor = (editor) => {
    editingEditorId.value = editor.id;
    editorUpdateForm.clearErrors();
    editorUpdateForm.name = editor.name ?? '';
    editorUpdateForm.email = editor.email ?? '';
    editorUpdateForm.password = '';
    editorUpdateForm.password_confirmation = '';
};

const cancelEditEditor = () => {
    editingEditorId.value = null;
    editorUpdateForm.clearErrors();
    editorUpdateForm.reset();
};

const submitEditorUpdate = (editor) => {
    editorUpdateForm
        .transform((data) => ({ ...data, _method: 'PATCH' }))
        .post(`/me/users/${editor.id}`, {
            preserveScroll: true,
            onSuccess: () => cancelEditEditor(),
            onFinish: () => editorUpdateForm.transform((data) => data),
        });
};

const requestDeleteEditor = (editor) => {
    editorPendingDelete.value = editor;
};

const cancelDeleteEditor = () => {
    if (editorDeleteForm.processing) {
        return;
    }

    editorPendingDelete.value = null;
};

const confirmDeleteEditor = () => {
    if (!editorPendingDelete.value) {
        return;
    }

    const editor = editorPendingDelete.value;

    editorDeleteForm
        .transform(() => ({ _method: 'DELETE' }))
        .post(`/me/users/${editor.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                if (editingEditorId.value === editor.id) {
                    cancelEditEditor();
                }

                editorPendingDelete.value = null;
            },
            onFinish: () => editorDeleteForm.transform((data) => data),
        });
};

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

// Couleurs de fond des sections (superadmin)
const sections = props.theme?.sections ?? {};
const colorState = reactive({});
Object.keys(sections).forEach((key) => {
    const stored = props.theme?.colors?.[key] ?? '';
    colorState[key] = { enabled: stored !== '', value: stored || '#0F5132' };
});

const colorsForm = useForm({ colors: {} });

const submitColors = () => {
    const payload = {};
    Object.keys(sections).forEach((key) => {
        payload[key] = colorState[key].enabled ? colorState[key].value : '';
    });
    colorsForm.colors = payload;
    colorsForm.post('/me/settings', { preserveScroll: true });
};

onBeforeUnmount(() => {
    if (profilePhotoObjectUrl) {
        URL.revokeObjectURL(profilePhotoObjectUrl);
    }
});
</script>

<template>
    <Head title="Mon profil" />

    <main class="profile-page">
        <div class="profile-shell">
            <div class="profile-hero">
                <div class="profile-hero-copy">
                    <span class="eyebrow">Mon compte</span>
                    <h1>Profil administrateur</h1>
                    <p>Gérez votre accès, la sécurité du compte et les réglages réservés au superadmin.</p>
                </div>
                <div class="profile-actions">
                    <Link href="/" class="btn btn-secondary profile-back-link">Retour au site</Link>
                </div>
            </div>

            <Toast />

            <div class="profile-grid">
                <div class="profile-main">
                    <form class="card profile-card" @submit.prevent="submitProfile">
                        <div class="profile-card-header">
                            <span class="profile-kicker">Identité</span>
                            <h2>Informations du compte</h2>
                        </div>

                        <div class="profile-form-grid">
                            <label class="profile-field">
                                <span>Nom</span>
                                <input v-model="profileForm.name" type="text" required class="eden-edit-input">
                                <small v-if="profileForm.errors.name" class="profile-error">{{ profileForm.errors.name }}</small>
                            </label>

                            <label class="profile-field">
                                <span>Email d'accès</span>
                                <input v-model="profileForm.email" type="email" autocomplete="email" required class="eden-edit-input">
                                <small v-if="profileForm.errors.email" class="profile-error">{{ profileForm.errors.email }}</small>
                            </label>
                        </div>

                        <div class="profile-card-actions">
                            <button class="btn btn-primary profile-submit" type="submit" :disabled="profileForm.processing">
                                {{ profileForm.processing ? 'Enregistrement...' : 'Enregistrer le profil' }}
                            </button>
                        </div>
                    </form>

                    <form class="card profile-card" @submit.prevent="submitPassword">
                        <div class="profile-card-header">
                            <span class="profile-kicker">Sécurité</span>
                            <h2>Mot de passe</h2>
                        </div>

                        <div class="profile-form-grid">
                            <label class="profile-field full">
                                <span>Mot de passe actuel</span>
                                <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" required class="eden-edit-input">
                                <small v-if="passwordForm.errors.current_password" class="profile-error">{{ passwordForm.errors.current_password }}</small>
                            </label>

                            <label class="profile-field">
                                <span>Nouveau mot de passe</span>
                                <input v-model="passwordForm.password" type="password" autocomplete="new-password" required class="eden-edit-input">
                                <small v-if="passwordForm.errors.password" class="profile-error">{{ passwordForm.errors.password }}</small>
                            </label>

                            <label class="profile-field">
                                <span>Confirmation</span>
                                <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" required class="eden-edit-input">
                            </label>
                        </div>

                        <div class="profile-card-actions">
                            <button class="btn btn-primary profile-submit" type="submit" :disabled="passwordForm.processing">
                                {{ passwordForm.processing ? 'Mise à jour...' : 'Mettre à jour le mot de passe' }}
                            </button>
                        </div>
                    </form>

                    <form v-if="isSuperAdmin" class="card profile-card" @submit.prevent="submitSettings">
                        <div class="profile-card-header">
                            <span class="profile-kicker">Référencement</span>
                            <h2>SEO, favicon et partage</h2>
                            <p>Ces informations alimentent l'onglet du navigateur, les moteurs de recherche et les aperçus de partage.</p>
                        </div>

                        <div class="profile-form-stack">
                            <label class="profile-field">
                                <span>Titre du site</span>
                                <input v-model="settingsForm.title" type="text" class="eden-edit-input">
                                <small v-if="settingsForm.errors.title" class="profile-error">{{ settingsForm.errors.title }}</small>
                            </label>

                            <label class="profile-field">
                                <span>Description</span>
                                <textarea v-model="settingsForm.description" rows="3" class="eden-edit-textarea"></textarea>
                                <small v-if="settingsForm.errors.description" class="profile-error">{{ settingsForm.errors.description }}</small>
                            </label>

                            <label class="profile-field">
                                <span>Mots-clés</span>
                                <textarea v-model="settingsForm.keywords" rows="2" class="eden-edit-textarea"></textarea>
                                <small v-if="settingsForm.errors.keywords" class="profile-error">{{ settingsForm.errors.keywords }}</small>
                            </label>

                            <div class="profile-upload-grid">
                                <div class="profile-upload-block">
                                    <span class="profile-upload-label">Favicon</span>
                                    <div class="profile-upload-row compact">
                                        <span class="eden-file-preview" :class="{ 'is-uploading': isUploadingFavicon }">
                                            <img :src="seo.favicon" alt="Favicon actuel" width="48" height="48" loading="lazy" decoding="async" class="profile-favicon-preview">
                                            <span v-if="isUploadingFavicon" class="eden-upload-skeleton small" role="status" aria-live="polite">
                                                <span class="eden-upload-content">
                                                    <span class="eden-upload-spinner"></span>
                                                </span>
                                            </span>
                                        </span>
                                        <input type="file" accept="image/png,image/jpeg,image/webp" :disabled="settingsForm.processing" @change="onFavicon">
                                    </div>
                                    <small v-if="settingsForm.errors.favicon" class="profile-error">{{ settingsForm.errors.favicon }}</small>
                                </div>

                                <div class="profile-upload-block">
                                    <span class="profile-upload-label">Image de partage</span>
                                    <span class="eden-file-preview profile-og-preview" :class="{ 'is-uploading': isUploadingOgImage }">
                                        <img :src="seo.ogImage" alt="Image de partage actuelle" width="340" height="179" loading="lazy" decoding="async">
                                        <span v-if="isUploadingOgImage" class="eden-upload-skeleton" role="status" aria-live="polite">
                                            <span class="eden-upload-content">
                                                <span class="eden-upload-spinner"></span>
                                                <span>Téléversement...</span>
                                            </span>
                                        </span>
                                    </span>
                                    <input type="file" accept="image/png,image/jpeg,image/webp" :disabled="settingsForm.processing" @change="onOgImage">
                                    <small v-if="settingsForm.errors.og_image" class="profile-error">{{ settingsForm.errors.og_image }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="profile-card-actions">
                            <button class="btn btn-primary profile-submit" type="submit" :disabled="settingsForm.processing">
                                {{ settingsForm.processing ? 'Enregistrement...' : 'Enregistrer le référencement' }}
                            </button>
                        </div>
                    </form>

                    <form v-if="isSuperAdmin" class="card profile-card" @submit.prevent="submitColors">
                        <div class="profile-card-header">
                            <span class="profile-kicker">Apparence</span>
                            <h2>Couleurs des sections</h2>
                            <p>Décochez une section pour revenir à la couleur par défaut du thème.</p>
                        </div>

                        <div class="profile-color-list">
                            <div
                                v-for="(label, key) in theme.sections"
                                :key="key"
                                class="profile-color-row"
                            >
                                <label class="profile-color-check">
                                    <input type="checkbox" v-model="colorState[key].enabled">
                                    <span>{{ label }}</span>
                                </label>
                                <input
                                    type="color"
                                    v-model="colorState[key].value"
                                    :disabled="!colorState[key].enabled"
                                    class="profile-color-input"
                                >
                                <span class="profile-color-value">
                                    {{ colorState[key].enabled ? colorState[key].value : 'Par défaut' }}
                                </span>
                            </div>
                        </div>

                        <div class="profile-card-actions">
                            <button class="btn btn-primary profile-submit" type="submit" :disabled="colorsForm.processing">
                                {{ colorsForm.processing ? 'Enregistrement...' : 'Enregistrer les couleurs' }}
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="profile-side">
                    <form class="card profile-card profile-photo-card" @submit.prevent="submitProfilePhoto">
                        <div class="profile-card-header">
                            <span class="profile-kicker">Photo</span>
                            <h2>Photo de profil</h2>
                            <p>Elle s'affiche dans la barre verticale gauche lorsque vous êtes connecté.</p>
                        </div>

                        <div class="profile-photo-panel">
                            <div class="profile-avatar-preview">
                                <img
                                    v-if="profilePhotoPreview"
                                    :src="profilePhotoPreview"
                                    :alt="`Photo de ${user.name}`"
                                    width="92"
                                    height="92"
                                    loading="lazy"
                                    decoding="async"
                                >
                                <span v-else>{{ userInitial }}</span>
                                <span v-if="profilePhotoForm.processing" class="eden-upload-skeleton" role="status" aria-live="polite">
                                    <span class="eden-upload-content">
                                        <span class="eden-upload-spinner"></span>
                                    </span>
                                </span>
                            </div>

                            <div class="profile-photo-fields">
                                <input
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp"
                                    :disabled="profilePhotoForm.processing"
                                    @change="onProfilePhoto"
                                >
                                <small>PNG, JPG ou WebP. Taille maximale : 2 Mo.</small>
                                <small v-if="profilePhotoForm.errors.photo" class="profile-error">{{ profilePhotoForm.errors.photo }}</small>
                            </div>
                        </div>

                        <div class="profile-card-actions">
                            <button class="btn btn-primary profile-submit" type="submit" :disabled="profilePhotoForm.processing || !profilePhotoForm.photo">
                                {{ profilePhotoForm.processing ? 'Téléversement...' : 'Enregistrer la photo' }}
                            </button>
                        </div>
                    </form>

                    <section class="card profile-card profile-role-card">
                        <div class="profile-role-top">
                            <span class="profile-role-badge">{{ roleLabel }}</span>
                            <span class="profile-role-dot" aria-hidden="true"></span>
                        </div>
                        <h2>{{ user.name }}</h2>
                        <p>{{ roleDescription }}</p>
                        <dl class="profile-meta-list">
                            <div>
                                <dt>Email</dt>
                                <dd>{{ user.email }}</dd>
                            </div>
                            <div>
                                <dt>Rôle technique</dt>
                                <dd>{{ user.role }}</dd>
                            </div>
                            <div v-if="isSuperAdmin">
                                <dt>Éditeurs</dt>
                                <dd>{{ editorsCount }}</dd>
                            </div>
                        </dl>
                    </section>

                    <form v-if="isSuperAdmin" class="card profile-card profile-editor-card" @submit.prevent="submitEditor">
                        <div class="profile-card-header">
                            <span class="profile-kicker">Utilisateurs</span>
                            <h2>Créer un éditeur</h2>
                            <p>Le compte créé pourra modifier les contenus publics autorisés.</p>
                        </div>

                        <div class="profile-form-stack">
                            <label class="profile-field">
                                <span>Nom</span>
                                <input v-model="editorForm.name" type="text" autocomplete="name" required class="eden-edit-input">
                                <small v-if="editorForm.errors.name" class="profile-error">{{ editorForm.errors.name }}</small>
                            </label>

                            <label class="profile-field">
                                <span>Email</span>
                                <input v-model="editorForm.email" type="email" autocomplete="email" required class="eden-edit-input">
                                <small v-if="editorForm.errors.email" class="profile-error">{{ editorForm.errors.email }}</small>
                            </label>

                            <label class="profile-field">
                                <span>Mot de passe</span>
                                <input v-model="editorForm.password" type="password" autocomplete="new-password" required class="eden-edit-input">
                                <small v-if="editorForm.errors.password" class="profile-error">{{ editorForm.errors.password }}</small>
                            </label>

                            <label class="profile-field">
                                <span>Confirmation</span>
                                <input v-model="editorForm.password_confirmation" type="password" autocomplete="new-password" required class="eden-edit-input">
                            </label>
                        </div>

                        <div class="profile-card-actions">
                            <button class="btn btn-primary profile-submit" type="submit" :disabled="editorForm.processing">
                                {{ editorForm.processing ? 'Création...' : 'Créer l’éditeur' }}
                            </button>
                        </div>
                    </form>

                    <section v-if="isSuperAdmin" class="card profile-card">
                        <div class="profile-card-header">
                            <span class="profile-kicker">Comptes actifs</span>
                            <h2>Éditeurs</h2>
                        </div>

                        <div v-if="editors.length" class="profile-editor-list">
                            <div v-for="editor in editors" :key="editor.id" class="profile-editor-row">
                                <template v-if="editingEditorId === editor.id">
                                    <form class="profile-editor-edit" @submit.prevent="submitEditorUpdate(editor)">
                                        <label class="profile-field">
                                            <span>Nom</span>
                                            <input v-model="editorUpdateForm.name" type="text" required class="eden-edit-input">
                                            <small v-if="editorUpdateForm.errors.name" class="profile-error">{{ editorUpdateForm.errors.name }}</small>
                                        </label>
                                        <label class="profile-field">
                                            <span>Email</span>
                                            <input v-model="editorUpdateForm.email" type="email" required class="eden-edit-input">
                                            <small v-if="editorUpdateForm.errors.email" class="profile-error">{{ editorUpdateForm.errors.email }}</small>
                                        </label>
                                        <label class="profile-field">
                                            <span>Nouveau mot de passe</span>
                                            <input v-model="editorUpdateForm.password" type="password" autocomplete="new-password" class="eden-edit-input" placeholder="Optionnel">
                                            <small v-if="editorUpdateForm.errors.password" class="profile-error">{{ editorUpdateForm.errors.password }}</small>
                                        </label>
                                        <label class="profile-field">
                                            <span>Confirmation</span>
                                            <input v-model="editorUpdateForm.password_confirmation" type="password" autocomplete="new-password" class="eden-edit-input" placeholder="Optionnel">
                                        </label>
                                        <div class="profile-editor-edit-actions">
                                            <button type="submit" class="profile-icon-action is-save" :disabled="editorUpdateForm.processing" :title="editorUpdateForm.processing ? 'Enregistrement...' : 'Enregistrer'" :aria-label="editorUpdateForm.processing ? 'Enregistrement...' : 'Enregistrer'">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5" /></svg>
                                            </button>
                                            <button type="button" class="profile-icon-action" :disabled="editorUpdateForm.processing" title="Annuler" aria-label="Annuler" @click="cancelEditEditor">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                                            </button>
                                        </div>
                                    </form>
                                </template>
                                <template v-else>
                                    <div>
                                        <strong>{{ editor.name }}</strong>
                                        <span>{{ editor.email }}</span>
                                    </div>
                                    <small>{{ editor.created_at }}</small>
                                    <div class="profile-editor-actions">
                                        <button type="button" class="profile-icon-action" title="Modifier l'éditeur" aria-label="Modifier l'éditeur" @click="startEditEditor(editor)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
                                        </button>
                                        <button type="button" class="profile-icon-action is-danger" :disabled="editorDeleteForm.processing" title="Supprimer l'éditeur" aria-label="Supprimer l'éditeur" @click="requestDeleteEditor(editor)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5" /><path d="M14 11v5" /></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <p v-else class="profile-empty">Aucun éditeur créé pour le moment.</p>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <teleport to="body">
        <div
            v-if="editorPendingDelete"
            class="profile-confirm-backdrop"
            role="presentation"
            @click.self="cancelDeleteEditor"
        >
            <section
                class="profile-confirm-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="profile-delete-editor-title"
            >
                <button
                    type="button"
                    class="profile-confirm-close"
                    :disabled="editorDeleteForm.processing"
                    aria-label="Fermer"
                    @click="cancelDeleteEditor"
                >
                    ×
                </button>

                <div class="profile-confirm-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18" />
                        <path d="M8 6V4h8v2" />
                        <path d="M19 6l-1 14H6L5 6" />
                        <path d="M10 11v5" />
                        <path d="M14 11v5" />
                    </svg>
                </div>

                <span class="profile-kicker">Suppression</span>
                <h2 id="profile-delete-editor-title">Supprimer cet éditeur ?</h2>
                <p>
                    Le compte <strong>{{ editorPendingDelete.name }}</strong> ne pourra plus se connecter ni modifier les contenus du site.
                </p>

                <dl class="profile-confirm-summary">
                    <div>
                        <dt>Nom</dt>
                        <dd>{{ editorPendingDelete.name }}</dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd>{{ editorPendingDelete.email }}</dd>
                    </div>
                </dl>

                <div class="profile-confirm-actions">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        :disabled="editorDeleteForm.processing"
                        @click="cancelDeleteEditor"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        class="btn profile-danger-submit"
                        :disabled="editorDeleteForm.processing"
                        @click="confirmDeleteEditor"
                    >
                        {{ editorDeleteForm.processing ? 'Suppression...' : 'Supprimer le compte' }}
                    </button>
                </div>
            </section>
        </div>
    </teleport>
</template>
