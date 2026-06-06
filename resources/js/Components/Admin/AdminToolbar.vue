<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import AdminHistoryPanel from '@/Components/Admin/AdminHistoryPanel.vue';

const props = defineProps({
    canEdit: {
        type: Boolean,
        default: false,
    },
    editMode: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:editMode']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const historyOpen = ref(false);

// Barre épinglée (ouverte) ou réduite en pastille — préférence mémorisée
const PIN_KEY = 'eden_admin_pinned';
const pinned = ref(true);

onMounted(() => {
    try {
        const stored = window.localStorage.getItem(PIN_KEY);
        if (stored !== null) {
            pinned.value = stored === '1';
        }
    } catch {
        // localStorage indisponible : on garde la valeur par défaut
    }
});

watch(pinned, (value) => {
    try {
        window.localStorage.setItem(PIN_KEY, value ? '1' : '0');
    } catch {
        // ignore
    }
});

const roleLabel = computed(() => (user.value?.role === 'superadmin' ? 'Super administrateur' : 'Éditeur'));

const toggleEditMode = () => {
    emit('update:editMode', !props.editMode);
};

const goProfile = () => {
    router.visit('/me/profile');
};

const logout = () => {
    router.post('/logout', {}, {
        preserveScroll: false,
    });
};
</script>

<template>
    <template v-if="canEdit">
        <aside v-if="pinned" class="eden-admin-toolbar" aria-label="Mode édition administrateur">
            <button
                class="eden-admin-status"
                :class="{ 'has-photo': user?.profile_photo_url }"
                type="button"
                :title="`${user?.email ?? 'Administrateur'} — ${roleLabel}`"
                :aria-label="`Profil de ${user?.name ?? 'l’administrateur'}`"
                @click="goProfile"
            >
                <img
                    v-if="user?.profile_photo_url"
                    :src="user.profile_photo_url"
                    :alt="`Photo de ${user?.name ?? 'administrateur'}`"
                    width="38"
                    height="38"
                    loading="lazy"
                    decoding="async"
                >
                <span v-else>{{ (user?.email ?? 'A').charAt(0).toUpperCase() }}</span>
            </button>
            <span class="eden-admin-sep"></span>

            <button
                class="eden-admin-btn"
                :class="{ 'is-active': editMode }"
                type="button"
                :title="editMode ? 'Masquer les outils d’édition' : 'Afficher les outils d’édition'"
                :aria-label="editMode ? 'Masquer les outils' : 'Afficher les outils'"
                @click="toggleEditMode"
            >
                <svg v-if="editMode" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 4.2A11 11 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19M6.6 6.6A18.6 18.6 0 0 0 2 11s3.5 7 10 7a11 11 0 0 0 3.6-.6" /><path d="m3 3 18 18" /></svg>
            </button>

            <button class="eden-admin-btn" type="button" title="Paramètres" aria-label="Paramètres" @click="goProfile">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" /></svg>
            </button>

            <button class="eden-admin-btn" type="button" title="Historique des modifications" aria-label="Historique" @click="historyOpen = true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5" /><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8" /><path d="M12 7v5l4 2" /></svg>
            </button>

            <button class="eden-admin-btn eden-admin-btn-danger" type="button" title="Déconnexion" aria-label="Déconnexion" @click="logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="m16 17 5-5-5-5" /><path d="M21 12H9" /></svg>
            </button>

            <span class="eden-admin-sep"></span>

            <button class="eden-admin-btn" type="button" title="Réduire la barre" aria-label="Réduire la barre" @click="pinned = false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6" /></svg>
            </button>
        </aside>

        <button
            v-else
            class="eden-admin-mini"
            type="button"
            title="Outils d’édition"
            aria-label="Afficher la barre d’édition"
            @click="pinned = true"
        >
            <img
                v-if="user?.profile_photo_url"
                :src="user.profile_photo_url"
                :alt="`Photo de ${user?.name ?? 'administrateur'}`"
                width="46"
                height="46"
                loading="lazy"
                decoding="async"
            >
            <span v-else class="eden-admin-mini-initial">{{ (user?.email ?? 'A').charAt(0).toUpperCase() }}</span>
            <span class="eden-admin-mini-edit" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9" /><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
            </span>
        </button>

        <AdminHistoryPanel :open="historyOpen" @close="historyOpen = false" />
    </template>
</template>
