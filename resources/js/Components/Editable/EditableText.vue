<script setup>
import { computed, inject, nextTick, ref, unref, watch } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { buttonVariants } from '@/lib/variants';
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
const fieldLabel = computed(() => ({ link: 'Lien', number: 'Nombre' }[props.type] ?? 'Texte'));
const editTitle = computed(() => (props.type === 'link' ? 'Modifier le lien' : 'Modifier le texte'));

const editing = ref(false);
const anchor = ref(null);
const popInput = ref(null);
const popStyle = ref({});
const draft = ref(String(resolve(props.value)));

// Le popover est téléporté dans <body> : il n'est ni coupé par une carte
// (overflow: hidden) ni imbriqué dans un lien ou un bouton.
const placePopover = () => {
    const rect = anchor.value?.getBoundingClientRect();

    if (!rect) {
        return;
    }

    const width = Math.min(380, window.innerWidth - 24);
    const left = Math.min(Math.max(12, rect.left), window.innerWidth - width - 12);

    popStyle.value = {
        top: `${rect.bottom + window.scrollY + 8}px`,
        left: `${left + window.scrollX}px`,
        width: `${width}px`,
    };
};
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
    placePopover();
    editing.value = true;
    nextTick(() => popInput.value?.focus());
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
    <span ref="anchor" class="relative">
        <span :style="textStyle">{{ display }}</span>
        <AdminEditButton v-if="canEdit && !editing" :title="editTitle" @click="start" />

        <Teleport to="body">
            <span
                v-if="editing"
                class="absolute z-[140] grid gap-3 rounded-xl border bg-popover p-4 text-left text-sm font-normal tracking-normal text-popover-foreground normal-case not-italic shadow-lg animate-in fade-in-0 zoom-in-95"
                role="dialog"
                :aria-label="editTitle"
                :style="popStyle"
                @click.stop
            >
                <label class="grid gap-1.5">
                    <span class="text-xs font-medium">{{ fieldLabel }}</span>
                    <input
                        ref="popInput"
                        v-model="draft"
                        class="flex h-9 w-full min-w-0 rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:opacity-50 dark:bg-input/20"
                        type="text"
                        :inputmode="type === 'number' ? 'numeric' : (type === 'link' ? 'url' : 'text')"
                        :disabled="saving"
                        @keydown.enter.prevent="save"
                        @keydown.esc.prevent="cancel"
                    >
                    <span v-if="type === 'link'" class="text-xs text-muted-foreground">Ancre de la page (ex. #contact) ou adresse complète (https://…).</span>
                </label>
                <span v-if="canEditTextColor" class="flex flex-wrap items-center gap-3">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-xs">
                        <input v-model="useCustomColor" type="checkbox" class="size-4 accent-eden-600" :disabled="saving">
                        Couleur personnalisée
                    </label>
                    <input v-if="useCustomColor" v-model="draftTextColor" class="h-7 w-10 cursor-pointer rounded border bg-background p-0.5" type="color" :disabled="saving" aria-label="Choisir la couleur du texte">
                </span>
                <span v-if="error" class="text-xs font-medium text-destructive" role="alert">{{ error }}</span>
                <span class="flex flex-wrap items-center justify-end gap-2">
                    <span class="mr-auto text-xs text-muted-foreground">Entrée pour enregistrer, Échap pour annuler</span>
                    <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" :disabled="saving" @click.stop.prevent="cancel">Annuler</button>
                    <button type="button" :class="buttonVariants({ size: 'sm' })" :disabled="saving" @click.stop.prevent="save">{{ saving ? 'Enregistrement…' : 'Enregistrer' }}</button>
                </span>
            </span>
        </Teleport>
    </span>
</template>
