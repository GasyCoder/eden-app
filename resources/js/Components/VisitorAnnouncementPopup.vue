<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { adminDelete, adminPatch } from '@/Composables/useAdminRequest';

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    announcements: {
        type: Array,
        default: () => [],
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

const delayOptions = [1, 2, 3, 5, 10, 15, 30];
const managerOpen = ref(false);
const visitorOpen = ref(false);
const selectedAnnouncementId = ref(null);
const creatingAnnouncement = ref(false);
const pendingDelete = ref(null);
const localImagePosition = ref(null);
const repositioning = ref(false);
const savingKey = ref('');
const uploadingImage = ref(false);
const error = ref('');
const imageInput = ref(null);
const drag = reactive({
    active: false,
    x: 0,
    y: 0,
    positionX: 50,
    positionY: 50,
});
const draft = reactive({
    id: null,
    title: '',
    message: '',
    image: '',
    button_label: '',
    button_url: '',
    enabled: false,
    delay_seconds: 3,
    meta: {},
});
let timer = null;

const clamp = (value, min = 0, max = 100) => Math.min(Math.max(value, min), max);

const normalizePosition = (value) => {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? clamp(parsed) : 50;
};

const normalizeZoom = (value) => {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? clamp(parsed, 1, 2) : 1;
};

const normalizeDelay = (value) => {
    const parsed = Number(value);

    return delayOptions.includes(parsed) ? parsed : 3;
};

const truthy = (value) => ['1', 'true', 'oui', 'yes', 'on'].includes(String(value).toLowerCase());

const fallbackPopup = computed(() => ({
    id: props.blocks?.id ?? null,
    enabled: props.blocks?.enabled ?? '0',
    delay_seconds: props.blocks?.delay_seconds ?? '3',
    title: props.blocks?.title || 'Annonce importante',
    message: props.blocks?.message || "Une nouvelle information est disponible pour les visiteurs de l'École Doctorale EDEN.",
    image: props.blocks?.image || '',
    button_label: props.blocks?.button_label || 'En savoir plus',
    button_url: props.blocks?.button_url || '#',
    meta: props.blocks?.meta ?? {},
    is_active: truthy(props.blocks?.enabled),
    is_archived: false,
    updated_at: props.blocks?.updated_at ?? null,
}));

const announcementList = computed(() => {
    if (props.announcements?.length) {
        return props.announcements;
    }

    return fallbackPopup.value.id ? [fallbackPopup.value] : [];
});

const selectedAnnouncement = computed(() => announcementList.value.find((item) => item.id === selectedAnnouncementId.value) ?? null);
const visitorPopup = computed(() => fallbackPopup.value);
const visiblePopup = computed(() => (managerOpen.value ? draft : visitorPopup.value));
const enabled = computed(() => truthy(visitorPopup.value.enabled));
const delaySeconds = computed(() => normalizeDelay(visitorPopup.value.delay_seconds));
const title = computed(() => visiblePopup.value.title || 'Annonce importante');
const message = computed(() => visiblePopup.value.message || "Une nouvelle information est disponible pour les visiteurs de l'École Doctorale EDEN.");
const image = computed(() => visiblePopup.value.image || '');
const buttonLabel = computed(() => visiblePopup.value.button_label || 'En savoir plus');
const buttonUrl = computed(() => visiblePopup.value.button_url || '#');
const hasButton = computed(() => buttonLabel.value && buttonUrl.value && buttonUrl.value !== '#');
const activeMeta = computed(() => (managerOpen.value ? draft.meta : (visitorPopup.value.meta ?? {})));
const storedImagePosition = computed(() => ({
    x: normalizePosition(activeMeta.value?.object_position_x),
    y: normalizePosition(activeMeta.value?.object_position_y),
    zoom: normalizeZoom(activeMeta.value?.object_zoom),
}));
const imagePosition = computed(() => localImagePosition.value ?? storedImagePosition.value);
const imageStyle = computed(() => ({
    transform: `translate3d(${(imagePosition.value.x - 50) * 0.35}%, ${(imagePosition.value.y - 50) * 0.35}%, 0) scale(${imagePosition.value.zoom})`,
}));
const popupSignature = computed(() => {
    const source = `${visitorPopup.value.id}|${visitorPopup.value.title}|${visitorPopup.value.message}|${visitorPopup.value.button_url}`;
    let hash = 0;

    for (let index = 0; index < source.length; index += 1) {
        hash = ((hash << 5) - hash) + source.charCodeAt(index);
        hash |= 0;
    }

    return `eden_visitor_popup_${Math.abs(hash)}`;
});
const popupVisible = computed(() => visitorOpen.value || managerOpen.value);

const statusLabel = (item) => {
    if (item.is_active) {
        return 'Active';
    }

    return item.is_archived ? 'Archivée' : 'Inactive';
};

const resetDraft = (popup = fallbackPopup.value) => {
    draft.id = popup?.id ?? null;
    draft.title = popup?.title || 'Annonce importante';
    draft.message = popup?.message || "Une nouvelle information est disponible pour les visiteurs de l'École Doctorale EDEN.";
    draft.image = popup?.image || '';
    draft.button_label = popup?.button_label || 'En savoir plus';
    draft.button_url = popup?.button_url || '#';
    draft.enabled = Boolean(popup?.is_active ?? truthy(popup?.enabled));
    draft.delay_seconds = normalizeDelay(popup?.delay_seconds ?? popup?.meta?.delay_seconds);
    draft.meta = {
        ...(popup?.meta ?? {}),
        delay_seconds: normalizeDelay(popup?.delay_seconds ?? popup?.meta?.delay_seconds),
    };
    localImagePosition.value = null;
    repositioning.value = false;
    drag.active = false;
    error.value = '';
};

const openManager = () => {
    const initial = props.blocks?.id ?? announcementList.value[0]?.id ?? null;

    managerOpen.value = true;
    creatingAnnouncement.value = !initial;
    selectedAnnouncementId.value = initial;
    resetDraft(initial ? (announcementList.value.find((item) => item.id === initial) ?? fallbackPopup.value) : fallbackPopup.value);
};

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
    creatingAnnouncement.value = false;
    pendingDelete.value = null;
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

const selectAnnouncement = (item) => {
    creatingAnnouncement.value = false;
    selectedAnnouncementId.value = item.id;
    resetDraft(item);
};

const newAnnouncement = () => {
    creatingAnnouncement.value = true;
    selectedAnnouncementId.value = null;
    resetDraft({
        id: null,
        title: 'Nouvelle annonce',
        message: "Rédigez ici le message destiné aux visiteurs de l'École Doctorale EDEN.",
        image: '',
        button_label: 'En savoir plus',
        button_url: '#',
        enabled: '1',
        is_active: true,
        delay_seconds: 3,
        meta: { delay_seconds: 3 },
    });
};

const emptyAnnouncement = () => {
    creatingAnnouncement.value = true;
    selectedAnnouncementId.value = null;
    resetDraft({
        id: null,
        title: '',
        message: '',
        image: '',
        button_label: 'En savoir plus',
        button_url: '#',
        enabled: '0',
        is_active: false,
        delay_seconds: 3,
        meta: { delay_seconds: 3 },
    });
};

const payloadFromDraft = () => ({
    title: draft.title,
    description: draft.message,
    image: draft.image || '',
    link_label: draft.button_label,
    link_url: draft.button_url || '#',
    is_active: draft.enabled,
    meta: {
        ...(draft.meta ?? {}),
        delay_seconds: normalizeDelay(draft.delay_seconds),
    },
});

const saveDraft = (key = 'popup.save') => {
    savingKey.value = key;
    error.value = '';

    const payload = payloadFromDraft();
    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            creatingAnnouncement.value = false;
        },
        onError: () => {
            error.value = 'Enregistrement impossible.';
        },
        onFinish: () => {
            savingKey.value = '';
        },
    };

    if (creatingAnnouncement.value || !draft.id) {
        router.post('/admin/visitor-popups', payload, options);
        return;
    }

    adminPatch(`/admin/visitor-popups/${draft.id}`, payload, options);
};

const updateStatus = (value) => {
    draft.enabled = value === '1';
    saveDraft('popup.status');
};

const updateDelay = (value) => {
    draft.delay_seconds = normalizeDelay(value);
    draft.meta = {
        ...(draft.meta ?? {}),
        delay_seconds: draft.delay_seconds,
    };
    saveDraft('popup.delay');
};

const activateAnnouncement = (item) => {
    savingKey.value = `activate-${item.id}`;
    error.value = '';

    adminPatch(`/admin/visitor-popups/${item.id}/activate`, {}, {
        preserveScroll: true,
        onError: () => {
            error.value = 'Activation impossible.';
        },
        onFinish: () => {
            savingKey.value = '';
        },
    });
};

const archiveAnnouncement = (item) => {
    savingKey.value = `archive-${item.id}`;
    error.value = '';

    adminPatch(`/admin/visitor-popups/${item.id}/archive`, {}, {
        preserveScroll: true,
        onError: () => {
            error.value = 'Archivage impossible.';
        },
        onFinish: () => {
            savingKey.value = '';
        },
    });
};

const requestDeleteAnnouncement = (item) => {
    pendingDelete.value = item;
};

const cancelDeleteAnnouncement = () => {
    if (!savingKey.value) {
        pendingDelete.value = null;
    }
};

const confirmDeleteAnnouncement = () => {
    if (!pendingDelete.value) {
        return;
    }

    const item = pendingDelete.value;
    savingKey.value = `delete-${item.id}`;
    error.value = '';

    adminDelete(`/admin/visitor-popups/${item.id}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            if (selectedAnnouncementId.value === item.id) {
                selectedAnnouncementId.value = null;
                creatingAnnouncement.value = false;
            }

            if (announcementList.value.length <= 1) {
                emptyAnnouncement();
            }

            pendingDelete.value = null;
        },
        onError: () => {
            error.value = 'Suppression impossible.';
        },
        onFinish: () => {
            savingKey.value = '';
        },
    });
};

const chooseImage = () => {
    if (uploadingImage.value || savingKey.value) {
        return;
    }

    imageInput.value?.click();
};

const onImageChange = async (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    uploadingImage.value = true;
    error.value = '';

    try {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('content_key', 'visitor_popup.image');

        const response = await fetch('/admin/uploads/images', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                Accept: 'application/json',
            },
        });
        const data = await response.json();

        if (!response.ok || !data.path) {
            error.value = data.message ?? 'Image refusée.';
            return;
        }

        draft.image = data.path;
        saveDraft('popup.image');
    } catch {
        error.value = 'Upload impossible.';
    } finally {
        uploadingImage.value = false;

        if (event.target) {
            event.target.value = '';
        }
    }
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

    draft.meta = {
        ...(draft.meta ?? {}),
        object_position_x: Math.round(nextPosition.x),
        object_position_y: Math.round(nextPosition.y),
        object_zoom: Number(nextPosition.zoom.toFixed(2)),
    };
    repositioning.value = false;
    saveDraft('popup.image.position');
};

watch(() => props.announcements, () => {
    if (!managerOpen.value) {
        return;
    }

    const selected = selectedAnnouncementId.value
        ? announcementList.value.find((item) => item.id === selectedAnnouncementId.value)
        : null;
    const next = selected ?? announcementList.value.find((item) => item.id === props.blocks?.id) ?? announcementList.value[0] ?? null;

    if (next) {
        selectedAnnouncementId.value = next.id;
        creatingAnnouncement.value = false;
        resetDraft(next);
        return;
    }

    emptyAnnouncement();
}, { deep: true });

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
        @click="openManager"
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
                    <strong>{{ draft.enabled ? 'Activée' : 'Désactivée' }}</strong>
                </div>
                <label>
                    <span>Statut</span>
                    <select
                        :value="draft.enabled ? '1' : '0'"
                        :disabled="Boolean(savingKey)"
                        @change="updateStatus($event.target.value)"
                    >
                        <option value="1">Activer</option>
                        <option value="0">Désactiver</option>
                    </select>
                </label>
                <label>
                    <span>Délai d'affichage</span>
                    <select
                        :value="draft.delay_seconds"
                        :disabled="Boolean(savingKey)"
                        @change="updateDelay($event.target.value)"
                    >
                        <option v-for="seconds in delayOptions" :key="seconds" :value="seconds">
                            Après {{ seconds }}s
                        </option>
                    </select>
                </label>
                <button type="button" class="eden-popup-new-button" :disabled="Boolean(savingKey)" @click="newAnnouncement">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
                    Nouvelle
                </button>
                <span v-if="error" class="eden-popup-admin-error">{{ error }}</span>
            </div>

            <div v-if="managerOpen" class="eden-popup-library">
                <header>
                    <div>
                        <span>Liste des annonces</span>
                        <strong>{{ announcementList.length }} annonce{{ announcementList.length > 1 ? 's' : '' }}</strong>
                    </div>
                </header>

                <div v-if="announcementList.length" class="eden-popup-list">
                    <div
                        v-for="item in announcementList"
                        :key="item.id"
                        class="eden-popup-list-item"
                        :class="{ active: item.id === selectedAnnouncementId }"
                    >
                        <button type="button" class="eden-popup-list-main" @click="selectAnnouncement(item)">
                            <strong>{{ item.title || 'Annonce sans titre' }}</strong>
                            <small>{{ item.updated_at || 'Non datée' }}</small>
                        </button>
                        <em :class="{ active: item.is_active, archived: item.is_archived }">{{ statusLabel(item) }}</em>
                        <span class="eden-popup-list-actions">
                            <button
                                v-if="!item.is_active"
                                type="button"
                                title="Activer"
                                :disabled="Boolean(savingKey)"
                                @click.stop="activateAnnouncement(item)"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5" /></svg>
                            </button>
                            <button
                                type="button"
                                title="Archiver"
                                :disabled="Boolean(savingKey) || item.is_archived"
                                @click.stop="archiveAnnouncement(item)"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8v13H3V8" /><path d="M1 3h22v5H1z" /><path d="M10 12h4" /></svg>
                            </button>
                            <button
                                type="button"
                                title="Supprimer"
                                :disabled="Boolean(savingKey)"
                                @click.stop="requestDeleteAnnouncement(item)"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v5" /><path d="M14 11v5" /></svg>
                            </button>
                        </span>
                    </div>
                </div>
                <p v-else>Aucune annonce enregistrée. Enregistrez l’annonce affichée pour l’ajouter à la liste.</p>
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

                <input ref="imageInput" hidden type="file" accept="image/*" @change="onImageChange">
                <button
                    v-if="managerOpen"
                    type="button"
                    class="eden-edit-image"
                    :disabled="uploadingImage || Boolean(savingKey)"
                    :title="uploadingImage ? 'Upload...' : 'Changer l’image'"
                    :aria-label="uploadingImage ? 'Upload...' : 'Changer l’image'"
                    @click.stop="chooseImage"
                >
                    <span class="admin-edit-button">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14" /><path d="M5 12h14" /></svg>
                    </span>
                </button>

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
                        <button type="button" :disabled="savingKey === 'popup.image.position' || imagePosition.zoom <= 1" aria-label="Réduire le zoom" title="Réduire le zoom" @click.stop="zoomImage(-0.05)">
                            −
                        </button>
                        <strong>{{ Math.round(imagePosition.zoom * 100) }}%</strong>
                        <button type="button" :disabled="savingKey === 'popup.image.position' || imagePosition.zoom >= 2" aria-label="Augmenter le zoom" title="Augmenter le zoom" @click.stop="zoomImage(0.05)">
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
            <template v-if="managerOpen">
                <input v-model="draft.title" class="eden-popup-title-input" type="text" :disabled="Boolean(savingKey)" placeholder="Titre de l'annonce">
                <textarea v-model="draft.message" class="eden-popup-message-input" rows="3" :disabled="Boolean(savingKey)" placeholder="Message de l'annonce"></textarea>
                <div class="eden-popup-admin-link">
                    <label>
                        <span>Libellé</span>
                        <input v-model="draft.button_label" type="text" :disabled="Boolean(savingKey)">
                    </label>
                    <label>
                        <span>Lien</span>
                        <input v-model="draft.button_url" type="text" :disabled="Boolean(savingKey)">
                    </label>
                </div>
                <div class="eden-popup-manager-actions">
                    <button type="button" class="btn btn-primary" :disabled="Boolean(savingKey)" @click="saveDraft()">
                        {{ savingKey === 'popup.save' ? 'Enregistrement...' : 'Enregistrer' }}
                    </button>
                    <button
                        v-if="draft.id && !selectedAnnouncement?.is_archived"
                        type="button"
                        class="btn btn-secondary"
                        :disabled="Boolean(savingKey)"
                        @click="archiveAnnouncement({ id: draft.id })"
                    >
                        Archiver
                    </button>
                    <button
                        v-if="draft.id"
                        type="button"
                        class="btn btn-secondary eden-popup-danger-button"
                        :disabled="Boolean(savingKey)"
                        @click="requestDeleteAnnouncement({ id: draft.id, title: draft.title, updated_at: selectedAnnouncement?.updated_at })"
                    >
                        Supprimer
                    </button>
                    <button type="button" class="btn btn-secondary" :disabled="Boolean(savingKey)" @click="closeManager">
                        Fermer
                    </button>
                </div>
            </template>
            <template v-else>
                <h2>{{ title }}</h2>
                <p>{{ message }}</p>
            </template>

            <div v-if="!managerOpen" class="eden-visitor-popup-actions">
                <a v-if="hasButton" class="btn btn-primary" :href="buttonUrl" target="_blank" rel="noopener noreferrer">
                    {{ buttonLabel }}
                </a>
                <button type="button" class="btn btn-secondary" @click="close">
                    Fermer
                </button>
            </div>

            <div v-if="pendingDelete" class="eden-popup-delete-confirm" role="dialog" aria-modal="true" aria-label="Confirmer la suppression">
                <div>
                    <span class="profile-kicker">Suppression</span>
                    <h3>Supprimer cette annonce ?</h3>
                    <p>
                        L’annonce <strong>{{ pendingDelete.title || 'sans titre' }}</strong> sera supprimée définitivement de la liste.
                    </p>
                    <div class="eden-popup-delete-actions">
                        <button type="button" class="btn btn-secondary" :disabled="Boolean(savingKey)" @click="cancelDeleteAnnouncement">Annuler</button>
                        <button type="button" class="btn eden-popup-danger-submit" :disabled="Boolean(savingKey)" @click="confirmDeleteAnnouncement">
                            {{ savingKey === `delete-${pendingDelete.id}` ? 'Suppression...' : 'Supprimer' }}
                        </button>
                    </div>
                </div>
            </div>
        </article>
    </div>
</template>
