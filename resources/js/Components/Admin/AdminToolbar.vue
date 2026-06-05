<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
    <aside v-if="canEdit" class="eden-admin-toolbar" aria-label="Mode édition administrateur">
        <div>
            <strong>Mode édition</strong>
            <span>{{ user?.email ?? 'Administrateur' }}</span>
        </div>
        <button class="eden-admin-toggle" type="button" @click="toggleEditMode">
            {{ editMode ? 'Masquer les outils' : 'Afficher les outils' }}
        </button>
        <button class="eden-admin-logout" type="button" @click="historyOpen = true">Historique</button>
        <button class="eden-admin-logout" type="button" @click="logout">Déconnexion</button>
    </aside>

    <template v-if="canEdit">
        <AdminHistoryPanel :open="historyOpen" @close="historyOpen = false" />
    </template>
</template>
