<script setup>
import { computed, inject, ref, unref, watch } from 'vue';
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

// Valeur affichée localement (mise à jour optimiste avant le retour serveur)
const localValue = ref(null);
const localTextColor = ref(null);

const display = computed(() => (localValue.value !== null ? localValue.value : resolve(props.value)));
const blockMeta = computed(() => getByPath(unref(injectedBlockMeta), props.contentKey) ?? {});
const storedTextColor = computed(() => normalizeColor(blockMeta.value?.text_color));
const activeTextColor = computed(() => (
    localTextColor.value !== null ? normalizeColor(localTextColor.value) : storedTextColor.value
));
const textStyle = computed(() => (activeTextColor.value ? { color: activeTextColor.value } : {}));
const canEditTextColor = computed(() => props.canEdit && !['link', 'image'].includes(props.type));

const editing = ref(false);
const draft = ref(String(resolve(props.value)));
const useCustomColor = ref(false);
const draftTextColor = ref('#0F5132');
const saving = ref(false);
const error = ref('');

// Dès que la vraie valeur revient du serveur, on lâche la valeur optimiste
watch(() => props.value, (value) => {
    localValue.value = null;

    if (!editing.value) {
        draft.value = String(resolve(value));
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
    draft.value = String(localValue.value !== null ? localValue.value : resolve(props.value));
    useCustomColor.value = Boolean(activeTextColor.value);
    draftTextColor.value = activeTextColor.value || '#0F5132';
    error.value = '';
    editing.value = true;
};

const cancel = () => {
    draft.value = String(resolve(props.value));
    useCustomColor.value = Boolean(activeTextColor.value);
    draftTextColor.value = activeTextColor.value || '#0F5132';
    error.value = '';
    editing.value = false;
};

const save = () => {
    const next = draft.value;
    const nextTextColor = canEditTextColor.value && useCustomColor.value ? draftTextColor.value : '';
    saving.value = true;
    error.value = '';

    // Mise à jour optimiste : on affiche tout de suite et on ferme le popover
    localValue.value = next;
    localTextColor.value = nextTextColor;
    editing.value = false;

    adminPatch(`/admin/content-blocks/${props.contentKey}`, {
        value: next,
        type: props.type,
        meta: {
            ...blockMeta.value,
            text_color: nextTextColor,
        },
    }, {
        preserveScroll: true,
        onError: () => {
            localValue.value = null;
            localTextColor.value = null;
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
        <span :style="textStyle">{{ display }}</span>
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
            <label v-if="canEditTextColor" class="eden-color-edit">
                <span>Couleur du texte</span>
                <span class="eden-color-control">
                    <input v-model="useCustomColor" type="checkbox" :disabled="saving">
                    <input v-model="draftTextColor" type="color" :disabled="saving || !useCustomColor">
                    <span class="eden-color-value">{{ useCustomColor ? draftTextColor : 'Par défaut' }}</span>
                </span>
            </label>
            <span class="eden-edit-actions">
                <button type="button" :disabled="saving" @click.stop.prevent="save">{{ saving ? 'Enregistrement...' : 'Enregistrer' }}</button>
                <button type="button" :disabled="saving" @click.stop.prevent="cancel">Annuler</button>
            </span>
            <span v-if="error" class="eden-edit-error">{{ error }}</span>
        </span>
    </span>
</template>
