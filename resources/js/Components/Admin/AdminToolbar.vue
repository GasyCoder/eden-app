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

const toggleEditMode = () => {
    emit('update:editMode', !props.editMode);
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
            <div>
                <strong>Mode édition</strong>
                <span>{{ user?.email ?? 'Administrateur' }}</span>
            </div>
            <button class="eden-admin-toggle" type="button" @click="toggleEditMode">
                {{ editMode ? 'Masquer les outils' : 'Afficher les outils' }}
            </button>
            <button class="eden-admin-logout" type="button" @click="historyOpen = true">Historique</button>
            <button class="eden-admin-logout eden-admin-danger" type="button" @click="logout">Déconnexion</button>
            <button class="eden-admin-pin" type="button" title="Réduire la barre" aria-label="Réduire la barre" @click="pinned = false">⤓</button>
        </aside>

        <button
            v-else
            class="eden-admin-mini"
            type="button"
            title="Afficher la barre d’édition"
            aria-label="Afficher la barre d’édition"
            @click="pinned = true"
        >✎</button>

        <AdminHistoryPanel :open="historyOpen" @close="historyOpen = false" />
    </template>
</template>
