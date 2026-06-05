<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';

const props = defineProps({
    value: {
        type: String,
        default: '',
    },
    fallback: {
        type: String,
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
        default: 'textarea',
    },
});

const editing = ref(false);
const draft = ref(props.value ?? props.fallback ?? '');
const saving = ref(false);
const error = ref('');

watch(() => props.value, (value) => {
    if (!editing.value) {
        draft.value = value ?? props.fallback ?? '';
    }
});

const start = () => {
    draft.value = props.value ?? props.fallback ?? '';
    error.value = '';
    editing.value = true;
};

const cancel = () => {
    draft.value = props.value ?? props.fallback ?? '';
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
    <span class="eden-edit-block">
        <template v-if="!editing">
            <span>{{ value ?? fallback }}</span>
            <AdminEditButton v-if="canEdit" @click="start" />
        </template>
        <template v-else>
            <textarea v-model="draft" class="eden-edit-textarea" rows="4" :disabled="saving" @keydown.esc.prevent="cancel"></textarea>
            <span class="eden-edit-actions">
                <button type="button" :disabled="saving" @click="save">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
                <button type="button" :disabled="saving" @click="cancel">Annuler</button>
            </span>
            <span v-if="error" class="eden-edit-error">{{ error }}</span>
        </template>
    </span>
</template>
