<script setup>
import { computed, ref, watch } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { adminPatch } from '@/Composables/useAdminRequest';

const props = defineProps({
    value: {
        type: [String, Number],
        default: '',
    },
    fallback: {
        type: [String, Number],
        default: '',
    },
    contentKey: {
        type: String,
        required: true,
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'text',
    },
});

// Renvoie le fallback si la valeur est absente OU vide
const resolve = (value) => {
    if (value === null || value === undefined || value === '') {
        return props.fallback;
    }

    return value;
};

// Valeur affichée localement (mise à jour optimiste avant le retour serveur)
const localValue = ref(null);

const display = computed(() => (localValue.value !== null ? localValue.value : resolve(props.value)));

const editing = ref(false);
const draft = ref(String(resolve(props.value)));
const saving = ref(false);
const error = ref('');

// Dès que la vraie valeur revient du serveur, on lâche la valeur optimiste
watch(() => props.value, (value) => {
    localValue.value = null;

    if (!editing.value) {
        draft.value = String(resolve(value));
    }
});

const start = () => {
    draft.value = String(localValue.value !== null ? localValue.value : resolve(props.value));
    error.value = '';
    editing.value = true;
};

const cancel = () => {
    draft.value = String(resolve(props.value));
    error.value = '';
    editing.value = false;
};

const save = () => {
    const next = draft.value;
    saving.value = true;
    error.value = '';

    // Mise à jour optimiste : on affiche tout de suite et on ferme le popover
    localValue.value = next;
    editing.value = false;

    adminPatch(`/admin/content-blocks/${props.contentKey}`, {
        value: next,
        type: props.type,
    }, {
        preserveScroll: true,
        onError: () => {
            localValue.value = null;
            error.value = 'Enregistrement impossible.';
            editing.value = true;
        },
        onFinish: () => {
            saving.value = false;
        },
    });
};
</script>

<template>
    <span class="eden-edit-wrap">
        <span>{{ display }}</span>
        <AdminEditButton v-if="canEdit && !editing" @click="start" />

        <span v-if="editing" class="eden-edit-pop" @click.stop>
            <input
                v-model="draft"
                class="eden-edit-input"
                type="text"
                :disabled="saving"
                autofocus
                @keydown.enter.prevent="save"
                @keydown.esc.prevent="cancel"
            >
            <span class="eden-edit-actions">
                <button type="button" :disabled="saving" @click.stop.prevent="save">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
                <button type="button" :disabled="saving" @click.stop.prevent="cancel">Annuler</button>
            </span>
            <span v-if="error" class="eden-edit-error">{{ error }}</span>
        </span>
    </span>
</template>
