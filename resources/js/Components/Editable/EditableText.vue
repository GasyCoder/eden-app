<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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

const editing = ref(false);
const draft = ref(String(props.value ?? props.fallback ?? ''));

watch(() => props.value, (value) => {
    if (!editing.value) {
        draft.value = String(value ?? props.fallback ?? '');
    }
});

const start = () => {
    draft.value = String(props.value ?? props.fallback ?? '');
    editing.value = true;
};

const cancel = () => {
    draft.value = String(props.value ?? props.fallback ?? '');
    editing.value = false;
};

const save = () => {
    router.patch(`/admin/content-blocks/${props.contentKey}`, {
        value: draft.value,
        type: props.type,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = false;
        },
    });
};
</script>

<template>
    <span class="eden-edit-wrap">
        <template v-if="!editing">
            <span>{{ value ?? fallback }}</span>
            <AdminEditButton v-if="canEdit" @click="start" />
        </template>
        <template v-else>
            <input v-model="draft" class="eden-edit-input" type="text" @keydown.enter.prevent="save" @keydown.esc.prevent="cancel">
            <span class="eden-edit-actions">
                <button type="button" @click="save">Enregistrer</button>
                <button type="button" @click="cancel">Annuler</button>
            </span>
        </template>
    </span>
</template>
