<script setup>
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';

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

// Renvoie le fallback si la valeur est absente OU vide (corrige l'affichage quand la BD ne contient pas la clé)
const resolve = (value) => {
    if (value === null || value === undefined || value === '') {
        return props.fallback;
    }

    return value;
};

const display = computed(() => resolve(props.value));

const editing = ref(false);
const draft = ref(String(resolve(props.value)));
const saving = ref(false);
const error = ref('');

watch(() => props.value, (value) => {
    if (!editing.value) {
        draft.value = String(resolve(value));
    }
});

const start = () => {
    draft.value = String(resolve(props.value));
    error.value = '';
    editing.value = true;
};

const cancel = () => {
    draft.value = String(resolve(props.value));
    error.value = '';
    editing.value = false;
};

const save = () => {
    saving.value = true;
    error.value = '';

    router.patch(`/admin/content-blocks/${props.contentKey}`, {
        value: draft.value,
        type: props.type,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = false;
        },
        onError: () => {
            error.value = 'Enregistrement impossible.';
        },
        onFinish: () => {
            saving.value = false;
        },
    });
};
</script>

<template>
    <span class="eden-edit-wrap">
        <template v-if="!editing">
            <span>{{ display }}</span>
            <AdminEditButton v-if="canEdit" @click="start" />
        </template>
        <template v-else>
            <input v-model="draft" class="eden-edit-input" type="text" :disabled="saving" @keydown.enter.prevent="save" @keydown.esc.prevent="cancel">
            <span class="eden-edit-actions">
                <button type="button" :disabled="saving" @click="save">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
                <button type="button" :disabled="saving" @click="cancel">Annuler</button>
            </span>
            <span v-if="error" class="eden-edit-error">{{ error }}</span>
        </template>
    </span>
</template>
