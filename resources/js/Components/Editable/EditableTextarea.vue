<script setup>
import { computed, inject, ref, unref, watch } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { buttonVariants, textareaClass } from '@/lib/variants';
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
    <span :class="editing ? 'block' : 'inline'">
        <template v-if="!editing">
            <span :style="textStyle">{{ display }}</span>
            <AdminEditButton v-if="canEdit" title="Modifier le paragraphe" @click="start" />
        </template>
        <span v-else class="my-2 grid w-full gap-3 rounded-xl border border-eden-600 bg-card p-4 text-left text-sm font-normal tracking-normal text-foreground normal-case not-italic shadow-[0_0_0_4px] shadow-eden-600/10" @click.stop>
            <textarea v-model="draft" :class="textareaClass" class="text-base" rows="4" :disabled="saving" aria-label="Texte du paragraphe" @keydown.esc.prevent="cancel"></textarea>
            <span class="flex flex-wrap items-center gap-3">
                <label class="inline-flex cursor-pointer items-center gap-2 text-xs">
                    <input v-model="useCustomColor" type="checkbox" class="size-4 accent-eden-600" :disabled="saving">
                    Couleur personnalisée
                </label>
                <input v-if="useCustomColor" v-model="draftTextColor" class="h-7 w-10 cursor-pointer rounded border bg-background p-0.5" type="color" :disabled="saving" aria-label="Choisir la couleur du texte">
            </span>
            <span v-if="error" class="text-xs font-medium text-destructive" role="alert">{{ error }}</span>
            <span class="flex justify-end gap-2">
                <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" :disabled="saving" @click.stop.prevent="cancel">Annuler</button>
                <button type="button" :class="buttonVariants({ size: 'sm' })" :disabled="saving" @click.stop.prevent="save">{{ saving ? 'Enregistrement…' : 'Enregistrer' }}</button>
            </span>
        </span>
    </span>
</template>
