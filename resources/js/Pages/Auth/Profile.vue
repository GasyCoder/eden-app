<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { badgeVariants, buttonVariants, inputClass } from '@/lib/variants';
import { cn } from '@/lib/utils';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});
const isSuperAdmin = computed(() => user.value.role === 'superadmin');
const roleLabel = computed(() => (isSuperAdmin.value ? 'Admin' : 'Éditeur'));
const roleDescription = computed(() => (
    isSuperAdmin.value
        ? 'Accès complet : contenus, paramètres du site et comptes éditeurs.'
        : 'Accès aux contenus éditables du site, sauf les réglages réservés au superadmin.'
));
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

const sections = [
    { id: 'compte', label: 'Mon compte' },
    { id: 'securite', label: 'Sécurité' },
];

onBeforeUnmount(() => {
    if (profilePhotoObjectUrl) {
        URL.revokeObjectURL(profilePhotoObjectUrl);
    }
});
</script>

<template>
    <AdminLayout title="Mon profil">
        <div class="grid items-start gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
            <aside class="grid gap-5 lg:sticky lg:top-20">
                <div class="grid justify-items-start gap-3 [&_p]:text-sm [&_p]:text-muted-foreground [&_strong]:block [&_strong]:text-base [&_strong]:font-semibold [&>div>span]:block [&>div>span]:text-sm [&>div>span]:break-all [&>div>span]:text-muted-foreground">
                    <span class="grid shrink-0 place-items-center overflow-hidden rounded-full bg-secondary font-semibold [&_img]:size-full [&_img]:object-cover size-18 text-2xl">
                        <img v-if="user.profile_photo_url" :src="user.profile_photo_url" :alt="`Photo de ${user.name}`" width="88" height="88" loading="lazy" decoding="async">
                        <template v-else>{{ userInitial }}</template>
                    </span>
                    <div>
                        <strong>{{ user.name }}</strong>
                        <span>{{ user.email }}</span>
                    </div>
                    <span :class="badgeVariants()">{{ roleLabel }}</span>
                    <p>{{ roleDescription }}</p>
                </div>
                <nav class="flex gap-1 overflow-x-auto border-t pt-4 lg:grid [&_a]:flex [&_a]:h-9 [&_a]:items-center [&_a]:rounded-md [&_a]:px-3 [&_a]:text-sm [&_a]:font-medium [&_a]:whitespace-nowrap [&_a]:text-muted-foreground [&_a:hover]:bg-accent [&_a:hover]:text-foreground" aria-label="Rubriques du profil">
                    <a v-for="section in sections" :key="section.id" :href="`#${section.id}`">{{ section.label }}</a>
                </nav>
            </aside>

            <div class="grid min-w-0 gap-5">
                <div class="[&_h1]:text-2xl [&_h1]:font-semibold [&_h1]:tracking-tight [&_p]:mt-1 [&_p]:text-sm [&_p]:text-muted-foreground">
                    <h1>Mon profil</h1>
                    <p>Vos informations, votre photo et la sécurité de votre compte.<template v-if="isSuperAdmin"> Les réglages du site et les comptes éditeurs sont dans <a href="/admin/settings" class="font-medium text-eden-700 underline-offset-4 hover:underline dark:text-eden-300">Paramètres</a>.</template></p>
                </div>

                <section id="compte" class="grid scroll-mt-20 gap-5 rounded-xl border bg-card p-5 sm:p-6" aria-labelledby="compte-title">
                    <header class="[&_h2]:text-lg [&_h2]:font-semibold [&_p]:mt-1 [&_p]:max-w-prose [&_p]:text-sm [&_p]:text-muted-foreground">
                        <h2 id="compte-title">Mon compte</h2>
                        <p>Votre nom apparaît dans l’historique des modifications ; l’e-mail sert à vous connecter.</p>
                    </header>

                    <form class="flex flex-col items-start gap-5 border-b pb-5 sm:flex-row sm:items-center" @submit.prevent="submitProfilePhoto">
                        <span class="grid shrink-0 place-items-center overflow-hidden rounded-full bg-secondary font-semibold [&_img]:size-full [&_img]:object-cover size-18 text-2xl relative">
                            <img v-if="profilePhotoPreview" :src="profilePhotoPreview" :alt="`Photo de ${user.name}`" width="88" height="88" loading="lazy" decoding="async">
                            <template v-else>{{ userInitial }}</template>
                            <span v-if="profilePhotoForm.processing" class="absolute inset-0 grid place-items-center bg-background/80 text-sm font-medium backdrop-blur-[2px]" role="status"><span class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span></span>
                        </span>
                        <div class="grid gap-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <label :class="cn(buttonVariants({ variant: 'outline', size: 'sm' }), 'relative cursor-pointer [&_input]:absolute [&_input]:inset-0 [&_input]:cursor-pointer [&_input]:opacity-0', profilePhotoForm.processing && 'pointer-events-none opacity-50')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4" /><path d="m7 9 5-5 5 5" /><path d="M20 16v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3" /></svg>
                                    Choisir une photo
                                    <input type="file" accept="image/png,image/jpeg,image/webp" :disabled="profilePhotoForm.processing" @change="onProfilePhoto">
                                </label>
                                <button v-if="profilePhotoForm.photo" :class="buttonVariants({ size: 'sm' })" type="submit" :disabled="profilePhotoForm.processing">
                                    {{ profilePhotoForm.processing ? 'Téléversement…' : 'Enregistrer la photo' }}
                                </button>
                            </div>
                            <span class="text-xs text-muted-foreground">PNG, JPG ou WebP, 2 Mo maximum. Elle s’affiche dans la barre d’administration.</span>
                            <span v-if="profilePhotoForm.errors.photo" class="text-xs font-medium text-destructive" role="alert">{{ profilePhotoForm.errors.photo }}</span>
                        </div>
                    </form>

                    <form class="grid gap-4" @submit.prevent="submitProfile">
                        <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                            <div class="grid min-w-0 content-start gap-2">
                                <label class="text-sm font-medium" for="profile-name">Nom</label>
                                <input id="profile-name" v-model="profileForm.name" type="text" autocomplete="name" required :class="inputClass" :aria-invalid="profileForm.errors.name ? 'true' : null">
                                <span v-if="profileForm.errors.name" class="text-xs font-medium text-destructive" role="alert">{{ profileForm.errors.name }}</span>
                            </div>
                            <div class="grid min-w-0 content-start gap-2">
                                <label class="text-sm font-medium" for="profile-email">Adresse e-mail</label>
                                <input id="profile-email" v-model="profileForm.email" type="email" autocomplete="email" required :class="inputClass" :aria-invalid="profileForm.errors.email ? 'true' : null">
                                <span v-if="profileForm.errors.email" class="text-xs font-medium text-destructive" role="alert">{{ profileForm.errors.email }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <button :class="buttonVariants()" type="submit" :disabled="profileForm.processing || !profileForm.isDirty">
                                <span v-if="profileForm.processing" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                                {{ profileForm.processing ? 'Enregistrement…' : 'Enregistrer' }}
                            </button>
                        </div>
                    </form>
                </section>

                <section id="securite" class="grid scroll-mt-20 gap-5 rounded-xl border bg-card p-5 sm:p-6" aria-labelledby="securite-title">
                    <header class="[&_h2]:text-lg [&_h2]:font-semibold [&_p]:mt-1 [&_p]:max-w-prose [&_p]:text-sm [&_p]:text-muted-foreground">
                        <h2 id="securite-title">Mot de passe</h2>
                        <p>Utilisez au moins 8 caractères, idéalement une phrase facile à retenir pour vous seul.</p>
                    </header>
                    <form class="grid gap-4" @submit.prevent="submitPassword">
                        <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                            <div class="grid min-w-0 content-start gap-2 sm:col-span-2">
                                <label class="text-sm font-medium" for="current-password">Mot de passe actuel</label>
                                <input id="current-password" v-model="passwordForm.current_password" type="password" autocomplete="current-password" required :class="inputClass" :aria-invalid="passwordForm.errors.current_password ? 'true' : null">
                                <span v-if="passwordForm.errors.current_password" class="text-xs font-medium text-destructive" role="alert">{{ passwordForm.errors.current_password }}</span>
                            </div>
                            <div class="grid min-w-0 content-start gap-2">
                                <label class="text-sm font-medium" for="new-password">Nouveau mot de passe</label>
                                <input id="new-password" v-model="passwordForm.password" type="password" autocomplete="new-password" required :class="inputClass" :aria-invalid="passwordForm.errors.password ? 'true' : null">
                                <span v-if="passwordForm.errors.password" class="text-xs font-medium text-destructive" role="alert">{{ passwordForm.errors.password }}</span>
                            </div>
                            <div class="grid min-w-0 content-start gap-2">
                                <label class="text-sm font-medium" for="confirm-password">Confirmer le nouveau mot de passe</label>
                                <input id="confirm-password" v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" required :class="inputClass">
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <button :class="buttonVariants()" type="submit" :disabled="passwordForm.processing">
                                <span v-if="passwordForm.processing" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                                {{ passwordForm.processing ? 'Mise à jour…' : 'Changer le mot de passe' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>

    </AdminLayout>
</template>
