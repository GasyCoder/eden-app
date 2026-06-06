<script setup>
import { computed, inject, ref, unref, watch } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { adminPatch } from '@/Composables/useAdminRequest';

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

const injectedBlockMeta = inject('blockMeta', {});

const getByPath = (source, path) => path
    .split('.')
    .reduce((current, segment) => (
        current && typeof current === 'object' ? current[segment] : undefined
    ), source);

const normalizeColor = (value) => (
    typeof value === 'string' && /^#[0-9a-fA-F]{6}$/.test(value) ? value : ''
);

// Renvoie le fallback si la valeur est absente OU vide
const resolve = (value) => {
    if (value === null || value === undefined || value === '') {
        return props.fallback;
    }

    return value;
};

const display = computed(() => resolve(props.value));
const blockMeta = computed(() => getByPath(unref(injectedBlockMeta), props.contentKey) ?? {});
const storedTextColor = computed(() => normalizeColor(blockMeta.value?.text_color));
const localTextColor = ref(null);
const activeTextColor = computed(() => (
    localTextColor.value !== null ? normalizeColor(localTextColor.value) : storedTextColor.value
));
const textStyle = computed(() => (activeTextColor.value ? { color: activeTextColor.value } : {}));

const editing = ref(false);
const draft = ref(resolve(props.value));
const useCustomColor = ref(false);
const draftTextColor = ref('#0F5132');
const saving = ref(false);
const error = ref('');

watch(() => props.value, (value) => {
    if (!editing.value) {
        draft.value = resolve(value);
    }
});

watch(storedTextColor, (color) => {
    localTextColor.value = null;

    if (!editing.value) {
        useCustomColor.value = Boolean(color);
        draftTextColor.value = color || '#0F5132';
    }
});

const start = () => {
    draft.value = resolve(props.value);
    useCustomColor.value = Boolean(activeTextColor.value);
    draftTextColor.value = activeTextColor.value || '#0F5132';
    error.value = '';
    editing.value = true;
};

const cancel = () => {
    draft.value = resolve(props.value);
    useCustomColor.value = Boolean(activeTextColor.value);
    draftTextColor.value = activeTextColor.value || '#0F5132';
    error.value = '';
    editing.value = false;
};

const save = () => {
    const nextTextColor = useCustomColor.value ? draftTextColor.value : '';
    saving.value = true;
    error.value = '';
    localTextColor.value = nextTextColor;

    adminPatch(`/admin/content-blocks/${props.contentKey}`, {
        value: draft.value,
        type: props.type,
        meta: {
            ...blockMeta.value,
            text_color: nextTextColor,
        },
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = false;
        },
        onError: () => {
            localTextColor.value = null;
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
            <span :style="textStyle">{{ display }}</span>
            <AdminEditButton v-if="canEdit" @click="start" />
        </template>
        <template v-else>
            <textarea v-model="draft" class="eden-edit-textarea" rows="4" :disabled="saving" @keydown.esc.prevent="cancel"></textarea>
            <label class="eden-color-edit">
                <span>Couleur du texte</span>
                <span class="eden-color-control">
                    <input v-model="useCustomColor" type="checkbox" :disabled="saving">
                    <input v-model="draftTextColor" type="color" :disabled="saving || !useCustomColor">
                    <span class="eden-color-value">{{ useCustomColor ? draftTextColor : 'Par défaut' }}</span>
                </span>
            </label>
            <span class="eden-edit-actions">
                <button type="button" :disabled="saving" @click="save">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
                <button type="button" :disabled="saving" @click="cancel">Annuler</button>
            </span>
            <span v-if="error" class="eden-edit-error">{{ error }}</span>
        </template>
    </span>
</template>
