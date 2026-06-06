<script setup>
import { computed, inject, onBeforeUnmount, reactive, ref, unref, watch } from 'vue';
import EditableImage from '@/Components/Editable/EditableImage.vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import { adminPatch } from '@/Composables/useAdminRequest';

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    canManage: {
        type: Boolean,
        default: false,
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
});

const injectedBlockMeta = inject('blockMeta', {});
const delayOptions = [1, 2, 3, 5, 10, 15, 30];
const managerOpen = ref(false);
const visitorOpen = ref(false);
const localEnabled = ref(null);
const localDelay = ref(null);
const localImagePosition = ref(null);
const repositioning = ref(false);
const savingKey = ref('');
const error = ref('');
const drag = reactive({
    active: false,
    x: 0,
    y: 0,
    positionX: 50,
    positionY: 50,
});
let timer = null;

const getByPath = (source, path) => path
    .split('.')
    .reduce((current, segment) => (
        current && typeof current === 'object' ? current[segment] : undefined
    ), source);

const clamp = (value, min = 0, max = 100) => Math.min(Math.max(value, min), max);

const normalizePosition = (value) => {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? clamp(parsed) : 50;
};

const normalizeZoom = (value) => {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? clamp(parsed, 1, 2) : 1;
};

const rawEnabled = computed(() => localEnabled.value ?? props.blocks?.enabled ?? '0');
const enabled = computed(() => ['1', 'true', 'oui', 'yes', 'on'].includes(String(rawEnabled.value).toLowerCase()));
const delaySeconds = computed(() => {
    const parsed = Number(localDelay.value ?? props.blocks?.delay_seconds ?? 3);

    return delayOptions.includes(parsed) ? parsed : 3;
});
const title = computed(() => props.blocks?.title || 'Annonce importante');
const message = computed(() => props.blocks?.message || "Une nouvelle information est disponible pour les visiteurs de l'École Doctorale EDEN.");
const image = computed(() => props.blocks?.image || '');
const blockMeta = computed(() => getByPath(unref(injectedBlockMeta), 'popup.image') ?? {});
const storedImagePosition = computed(() => ({
    x: normalizePosition(blockMeta.value?.object_position_x),
    y: normalizePosition(blockMeta.value?.object_position_y),
    zoom: normalizeZoom(blockMeta.value?.object_zoom),
}));
const imagePosition = computed(() => localImagePosition.value ?? storedImagePosition.value);
const imageStyle = computed(() => ({
    transform: `translate3d(${(imagePosition.value.x - 50) * 0.35}%, ${(imagePosition.value.y - 50) * 0.35}%, 0) scale(${imagePosition.value.zoom})`,
}));
const buttonLabel = computed(() => props.blocks?.button_label || 'En savoir plus');
const buttonUrl = computed(() => props.blocks?.button_url || '#');
const hasButton = computed(() => buttonLabel.value && buttonUrl.value && buttonUrl.value !== '#');
const popupSignature = computed(() => {
    const source = `${title.value}|${message.value}|${buttonLabel.value}|${buttonUrl.value}`;
    let hash = 0;

    for (let index = 0; index < source.length; index += 1) {
        hash = ((hash << 5) - hash) + source.charCodeAt(index);
        hash |= 0;
    }

    return `eden_visitor_popup_${Math.abs(hash)}`;
});
const popupVisible = computed(() => visitorOpen.value || managerOpen.value);

const closeVisitor = () => {
    visitorOpen.value = false;

    try {
        window.sessionStorage.setItem(popupSignature.value, 'closed');
    } catch {
        // sessionStorage indisponible : fermeture simple.
    }
};

const closeManager = () => {
    managerOpen.value = false;
    repositioning.value = false;
    drag.active = false;
    localImagePosition.value = null;
};

const close = () => {
    if (managerOpen.value) {
        closeManager();
        return;
    }

    closeVisitor();
};

const shouldSkipVisitorPopup = () => {
    if (!enabled.value || props.isAdmin || props.canManage) {
        return true;
    }

    try {
        return window.sessionStorage.getItem(popupSignature.value) === 'closed';
    } catch {
        return false;
    }
};

const scheduleVisitorPopup = () => {
    if (timer) {
        window.clearTimeout(timer);
    }

    visitorOpen.value = false;

    if (shouldSkipVisitorPopup()) {
        return;
    }

    timer = window.setTimeout(() => {
        visitorOpen.value = true;
    }, delaySeconds.value * 1000);
};

const saveBlock = (key, value, type = 'text') => {
    savingKey.value = key;
    error.value = '';

    if (key === 'popup.enabled') {
        localEnabled.value = value;
    }

    if (key === 'popup.delay_seconds') {
        localDelay.value = value;
    }

    adminPatch(`/admin/content-blocks/${key}`, {
        value,
        type,
    }, {
        preserveScroll: true,
        onError: () => {
            error.value = 'Enregistrement impossible.';
            localEnabled.value = null;
            localDelay.value = null;
        },
        onFinish: () => {
            savingKey.value = '';
        },
    });
};

const startReposition = () => {
    localImagePosition.value = {
        ...imagePosition.value,
        zoom: Math.max(imagePosition.value.zoom, 1.18),
    };
    repositioning.value = true;
    error.value = '';
};

const cancelReposition = () => {
    localImagePosition.value = null;
    repositioning.value = false;
    drag.active = false;
};

const resetReposition = () => {
    localImagePosition.value = { x: 50, y: 50, zoom: 1 };
};

const startDrag = (event) => {
    if (!repositioning.value) {
        return;
    }

    event.preventDefault();
    drag.active = true;
    drag.x = event.clientX;
    drag.y = event.clientY;
    drag.positionX = imagePosition.value.x;
    drag.positionY = imagePosition.value.y;
    event.currentTarget.setPointerCapture?.(event.pointerId);
};

const moveDrag = (event) => {
    if (!drag.active || !repositioning.value) {
        return;
    }

    event.preventDefault();
    const rect = event.currentTarget.getBoundingClientRect();
    const deltaX = ((event.clientX - drag.x) / rect.width) * 100;
    const deltaY = ((event.clientY - drag.y) / rect.height) * 100;

    localImagePosition.value = {
        ...imagePosition.value,
        x: clamp(drag.positionX + deltaX),
        y: clamp(drag.positionY + deltaY),
    };
};

const endDrag = () => {
    drag.active = false;
};

const setImageAxis = (axis, value) => {
    localImagePosition.value = {
        ...imagePosition.value,
        [axis]: normalizePosition(value),
    };
};

const zoomImage = (step) => {
    localImagePosition.value = {
        ...imagePosition.value,
        zoom: Number(clamp(imagePosition.value.zoom + step, 1, 2).toFixed(2)),
    };
};

const saveReposition = () => {
    const nextPosition = imagePosition.value;
    savingKey.value = 'popup.image.position';
    error.value = '';

    adminPatch('/admin/content-blocks/popup.image', {
        value: image.value,
        type: 'image',
        meta: {
            ...blockMeta.value,
            object_position_x: Math.round(nextPosition.x),
            object_position_y: Math.round(nextPosition.y),
            object_zoom: Number(nextPosition.zoom.toFixed(2)),
        },
    }, {
        preserveScroll: true,
        onSuccess: () => {
            repositioning.value = false;
        },
        onError: () => {
            error.value = 'Position non enregistrée.';
            localImagePosition.value = null;
        },
        onFinish: () => {
            savingKey.value = '';
        },
    });
};

watch(() => props.blocks?.enabled, () => {
    localEnabled.value = null;
});

watch(() => props.blocks?.delay_seconds, () => {
    localDelay.value = null;
});

watch(storedImagePosition, () => {
    if (!repositioning.value) {
        localImagePosition.value = null;
    }
});

watch([enabled, delaySeconds, popupSignature, () => props.isAdmin, () => props.canManage], scheduleVisitorPopup, {
    immediate: true,
});

onBeforeUnmount(() => {
    if (timer) {
        window.clearTimeout(timer);
    }
});
</script>

<template>
    <button
        v-if="canManage && !managerOpen"
        type="button"
        class="eden-popup-manager-trigger"
        title="Configurer l'annonce visiteurs"
        @click="managerOpen = true"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 14a3 3 0 0 0 3 3h1l3 4v-4h4l5 2V5l-5 2H7a3 3 0 0 0-3 3v4Z" />
            <path d="M8 17v2a2 2 0 0 1-2 2" />
        </svg>
        <span>Annonce</span>
    </button>

    <div
        v-if="popupVisible"
        class="eden-visitor-popup-backdrop"
        :class="{ 'is-manager': managerOpen }"
        role="presentation"
        @click.self="close"
    >
        <article class="eden-visitor-popup" role="dialog" aria-modal="true" aria-label="Annonce visiteurs">
            <button type="button" class="eden-visitor-popup-close" aria-label="Fermer" @click="close">×</button>

            <div v-if="managerOpen" class="eden-popup-admin-settings">
                <div>
                    <span>Annonce visiteurs</span>
                    <strong>{{ enabled ? 'Activée' : 'Désactivée' }}</strong>
                </div>
                <label>
                    <span>Statut</span>
                    <select
                        :value="enabled ? '1' : '0'"
                        :disabled="savingKey === 'popup.enabled'"
                        @change="saveBlock('popup.enabled', $event.target.value, 'number')"
                    >
                        <option value="1">Activer</option>
                        <option value="0">Désactiver</option>
                    </select>
                </label>
                <label>
                    <span>Délai d'affichage</span>
                    <select
                        :value="delaySeconds"
                        :disabled="savingKey === 'popup.delay_seconds'"
                        @change="saveBlock('popup.delay_seconds', $event.target.value, 'number')"
                    >
                        <option v-for="seconds in delayOptions" :key="seconds" :value="seconds">
                            Après {{ seconds }}s
                        </option>
                    </select>
                </label>
                <span v-if="error" class="eden-popup-admin-error">{{ error }}</span>
            </div>

            <div
                v-if="image || managerOpen"
                class="eden-visitor-popup-banner"
                :class="{ 'is-empty': !image, 'is-repositioning': repositioning, 'is-dragging': drag.active }"
                @pointerdown="startDrag"
                @pointermove="moveDrag"
                @pointerup="endDrag"
                @pointercancel="endDrag"
            >
                <img
                    v-if="image"
                    :src="image"
                    alt="Image de l'annonce"
                    width="960"
                    height="420"
                    loading="lazy"
                    decoding="async"
                    draggable="false"
                    :style="imageStyle"
                >
                <span v-else>Image de l'annonce</span>
                <EditableImage content-key="popup.image" :can-edit="canManage" />

                <button
                    v-if="managerOpen && image && !repositioning"
                    type="button"
                    class="eden-popup-reposition-trigger"
                    @click.stop="startReposition"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3v18" />
                        <path d="m8 7 4-4 4 4" />
                        <path d="m8 17 4 4 4-4" />
                        <path d="M3 12h18" />
                        <path d="m7 8-4 4 4 4" />
                        <path d="m17 8 4 4-4 4" />
                    </svg>
                    Repositionner
                </button>

                <div v-if="repositioning" class="eden-popup-reposition-panel" @pointerdown.stop>
                    <span>Glissez l'image dans le cadre</span>
                    <label>
                        <span>Horizontal</span>
                        <input
                            type="range"
                            min="0"
                            max="100"
                            step="1"
                            :value="imagePosition.x"
                            :disabled="savingKey === 'popup.image.position'"
                            @input="setImageAxis('x', $event.target.value)"
                        >
                    </label>
                    <label>
                        <span>Vertical</span>
                        <input
                            type="range"
                            min="0"
                            max="100"
                            step="1"
                            :value="imagePosition.y"
                            :disabled="savingKey === 'popup.image.position'"
                            @input="setImageAxis('y', $event.target.value)"
                        >
                    </label>
                    <div class="eden-popup-zoom-controls">
                        <span>Zoom</span>
                        <button type="button" :disabled="savingKey === 'popup.image.position' || imagePosition.zoom <= 1" aria-label="Réduire le zoom" @click.stop="zoomImage(-0.05)">
                            −
                        </button>
                        <strong>{{ Math.round(imagePosition.zoom * 100) }}%</strong>
                        <button type="button" :disabled="savingKey === 'popup.image.position' || imagePosition.zoom >= 2" aria-label="Augmenter le zoom" @click.stop="zoomImage(0.05)">
                            +
                        </button>
                    </div>
                    <div>
                        <button
                            type="button"
                            :disabled="savingKey === 'popup.image.position'"
                            :title="savingKey === 'popup.image.position' ? 'Enregistrement...' : 'Enregistrer'"
                            :aria-label="savingKey === 'popup.image.position' ? 'Enregistrement...' : 'Enregistrer'"
                            @click.stop="saveReposition"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5" /></svg>
                        </button>
                        <button
                            type="button"
                            :disabled="savingKey === 'popup.image.position'"
                            title="Réinitialiser"
                            aria-label="Réinitialiser"
                            @click.stop="resetReposition"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8" /><path d="M3 3v5h5" /></svg>
                        </button>
                        <button
                            type="button"
                            :disabled="savingKey === 'popup.image.position'"
                            title="Annuler"
                            aria-label="Annuler"
                            @click.stop="cancelReposition"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <span class="eyebrow">Annonce</span>
            <h2>
                <EditableText
                    :value="title"
                    fallback="Annonce importante"
                    content-key="popup.title"
                    :can-edit="canManage"
                />
            </h2>
            <p>
                <EditableTextarea
                    :value="message"
                    fallback="Une nouvelle information est disponible pour les visiteurs de l'École Doctorale EDEN."
                    content-key="popup.message"
                    :can-edit="canManage"
                />
            </p>

            <div v-if="managerOpen" class="eden-popup-admin-link">
                <span>
                    Libellé :
                    <EditableText
                        :value="buttonLabel"
                        fallback="En savoir plus"
                        content-key="popup.button_label"
                        :can-edit="canManage"
                    />
                </span>
                <span>
                    Lien :
                    <EditableText
                        :value="buttonUrl"
                        fallback="#"
                        content-key="popup.button_url"
                        :can-edit="canManage"
                        type="link"
                    />
                </span>
            </div>

            <div class="eden-visitor-popup-actions">
                <a v-if="hasButton" class="btn btn-primary" :href="buttonUrl" target="_blank" rel="noopener noreferrer">
                    {{ buttonLabel }}
                </a>
                <button type="button" class="btn btn-secondary" @click="close">
                    Fermer
                </button>
            </div>
        </article>
    </div>
</template>
