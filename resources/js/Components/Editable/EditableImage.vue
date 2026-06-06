<script setup>
import { computed, nextTick, reactive, ref } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { adminPatch } from '@/Composables/useAdminRequest';

const props = defineProps({
    contentKey: {
        type: String,
        required: true,
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});

const cropPresets = {
    'hero.image': {
        title: 'Rogner la bannière',
        hint: 'Cadre horizontal utilisé dans la section d’accueil.',
        aspect: 4 / 3,
        outputWidth: 1600,
        outputHeight: 1200,
    },
    'direction.photo': {
        title: 'Rogner le portrait',
        hint: 'Cadre vertical utilisé pour la photo du directeur.',
        aspect: 300 / 340,
        outputWidth: 900,
        outputHeight: 1020,
    },
};

const input = ref(null);
const cropFrame = ref(null);
const cropImageElement = ref(null);
const uploading = ref(false);
const processingCrop = ref(false);
const error = ref('');
const cropOpen = ref(false);
const selectedFile = ref(null);
const objectUrl = ref('');
const naturalSize = reactive({ width: 0, height: 0 });
const frameSize = reactive({ width: 0, height: 0 });
const crop = reactive({ zoom: 1, offsetX: 0, offsetY: 0 });
const drag = reactive({ active: false, x: 0, y: 0, offsetX: 0, offsetY: 0 });

const preset = computed(() => cropPresets[props.contentKey] ?? null);
const cropFrameStyle = computed(() => ({
    aspectRatio: preset.value ? String(preset.value.aspect) : '4 / 3',
}));
const imageScale = computed(() => {
    if (!naturalSize.width || !naturalSize.height || !frameSize.width || !frameSize.height) {
        return 1;
    }

    return Math.max(frameSize.width / naturalSize.width, frameSize.height / naturalSize.height);
});
const cropImageStyle = computed(() => {
    const scale = imageScale.value * crop.zoom;

    return {
        width: `${naturalSize.width * scale}px`,
        height: `${naturalSize.height * scale}px`,
        transform: `translate(-50%, -50%) translate(${crop.offsetX}px, ${crop.offsetY}px)`,
    };
});

const choose = () => {
    if (uploading.value || processingCrop.value) {
        return;
    }

    input.value?.click();
};

const resetInput = () => {
    if (input.value) {
        input.value.value = '';
    }
};

const clearObjectUrl = () => {
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
        objectUrl.value = '';
    }
};

const updateFrameSize = async () => {
    await nextTick();
    const rect = cropFrame.value?.getBoundingClientRect();

    if (!rect) {
        return;
    }

    frameSize.width = rect.width;
    frameSize.height = rect.height;
};

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const clampCrop = () => {
    const scale = imageScale.value * crop.zoom;
    const scaledWidth = naturalSize.width * scale;
    const scaledHeight = naturalSize.height * scale;
    const maxX = Math.max(0, (scaledWidth - frameSize.width) / 2);
    const maxY = Math.max(0, (scaledHeight - frameSize.height) / 2);

    crop.offsetX = clamp(crop.offsetX, -maxX, maxX);
    crop.offsetY = clamp(crop.offsetY, -maxY, maxY);
};

const resetCrop = () => {
    crop.zoom = 1;
    crop.offsetX = 0;
    crop.offsetY = 0;
};

const openCropper = async (file) => {
    clearObjectUrl();
    selectedFile.value = file;
    objectUrl.value = URL.createObjectURL(file);
    resetCrop();
    cropOpen.value = true;
    error.value = '';

    await updateFrameSize();
};

const onImageLoaded = async () => {
    const image = cropImageElement.value;

    if (!image) {
        return;
    }

    naturalSize.width = image.naturalWidth;
    naturalSize.height = image.naturalHeight;
    await updateFrameSize();
    clampCrop();
};

const closeCropper = () => {
    cropOpen.value = false;
    selectedFile.value = null;
    processingCrop.value = false;
    drag.active = false;
    clearObjectUrl();
    resetInput();
};

const onFileChange = async (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    if (!preset.value) {
        await uploadFile(file);
        resetInput();
        return;
    }

    await openCropper(file);
};

const onZoom = () => {
    crop.zoom = Number(crop.zoom);
    clampCrop();
};

const zoomBy = (step) => {
    crop.zoom = clamp(Number((crop.zoom + step).toFixed(2)), 1, 3);
    clampCrop();
};

const startDrag = (event) => {
    drag.active = true;
    drag.x = event.clientX;
    drag.y = event.clientY;
    drag.offsetX = crop.offsetX;
    drag.offsetY = crop.offsetY;
    event.currentTarget.setPointerCapture?.(event.pointerId);
};

const moveDrag = (event) => {
    if (!drag.active) {
        return;
    }

    crop.offsetX = drag.offsetX + (event.clientX - drag.x);
    crop.offsetY = drag.offsetY + (event.clientY - drag.y);
    clampCrop();
};

const endDrag = () => {
    drag.active = false;
};

const cropToFile = () => new Promise((resolve, reject) => {
    const image = cropImageElement.value;
    const activePreset = preset.value;

    if (!image || !activePreset) {
        reject(new Error('Aucune image à rogner.'));
        return;
    }

    const scale = imageScale.value * crop.zoom;
    const imageLeft = frameSize.width / 2 - (naturalSize.width * scale) / 2 + crop.offsetX;
    const imageTop = frameSize.height / 2 - (naturalSize.height * scale) / 2 + crop.offsetY;
    const sourceX = clamp(-imageLeft / scale, 0, naturalSize.width);
    const sourceY = clamp(-imageTop / scale, 0, naturalSize.height);
    const sourceWidth = Math.min(frameSize.width / scale, naturalSize.width - sourceX);
    const sourceHeight = Math.min(frameSize.height / scale, naturalSize.height - sourceY);
    const canvas = document.createElement('canvas');
    canvas.width = activePreset.outputWidth;
    canvas.height = activePreset.outputHeight;

    const context = canvas.getContext('2d');
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.drawImage(
        image,
        sourceX,
        sourceY,
        sourceWidth,
        sourceHeight,
        0,
        0,
        canvas.width,
        canvas.height,
    );

    canvas.toBlob((blob) => {
        if (!blob) {
            reject(new Error('Image invalide.'));
            return;
        }

        const filename = `${props.contentKey.replaceAll('.', '-')}.jpg`;
        resolve(new File([blob], filename, { type: 'image/jpeg' }));
    }, 'image/jpeg', 0.9);
});

const confirmCrop = async () => {
    processingCrop.value = true;
    error.value = '';

    try {
        const croppedFile = await cropToFile();
        closeCropper();
        await uploadFile(croppedFile);
    } catch {
        error.value = 'Recadrage impossible.';
        processingCrop.value = false;
    }
};

const uploadFile = async (file) => {
    let waitsForContentSave = false;
    uploading.value = true;
    error.value = '';

    try {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('content_key', props.contentKey);

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

        waitsForContentSave = true;
        adminPatch(`/admin/content-blocks/${props.contentKey}`, {
            value: data.path,
            type: 'image',
        }, {
            preserveScroll: true,
            onError: () => {
                error.value = 'Image non enregistrée.';
            },
            onFinish: () => {
                uploading.value = false;
                resetInput();
            },
        });
    } catch {
        error.value = 'Téléversement impossible.';
    } finally {
        if (!waitsForContentSave) {
            uploading.value = false;
        }
    }
};
</script>

<template>
    <span v-if="canEdit" class="eden-edit-image">
        <AdminEditButton :title="uploading ? 'Téléversement...' : 'Remplacer l’image'" :disabled="uploading || processingCrop" @click="choose" />
        <input ref="input" type="file" accept="image/*" hidden @change="onFileChange">
        <span v-if="error" class="eden-edit-error">{{ error }}</span>
    </span>

    <teleport to="body">
        <div v-if="cropOpen" class="eden-crop-backdrop" role="dialog" aria-modal="true" @keydown.esc.prevent="closeCropper">
            <div class="eden-crop-modal">
                <header class="eden-crop-header">
                    <div>
                        <strong>{{ preset?.title }}</strong>
                        <span>{{ preset?.hint }}</span>
                    </div>
                    <button type="button" :disabled="processingCrop" aria-label="Fermer" @click="closeCropper">×</button>
                </header>

                <div class="eden-crop-body">
                    <div
                        ref="cropFrame"
                        class="eden-crop-frame"
                        :class="{ 'is-dragging': drag.active }"
                        :style="cropFrameStyle"
                        @pointerdown.prevent="startDrag"
                        @pointermove.prevent="moveDrag"
                        @pointerup.prevent="endDrag"
                        @pointercancel.prevent="endDrag"
                    >
                        <img
                            ref="cropImageElement"
                            :src="objectUrl"
                            alt=""
                            :style="cropImageStyle"
                            draggable="false"
                            @load="onImageLoaded"
                        >
                        <span class="eden-crop-grid" aria-hidden="true"></span>
                    </div>

                    <div class="eden-crop-controls">
                        <button type="button" :disabled="processingCrop" aria-label="Zoom arrière" @click="zoomBy(-0.1)">−</button>
                        <input v-model="crop.zoom" type="range" min="1" max="3" step="0.01" :disabled="processingCrop" @input="onZoom">
                        <button type="button" :disabled="processingCrop" aria-label="Zoom avant" @click="zoomBy(0.1)">+</button>
                    </div>
                </div>

                <footer class="eden-crop-actions">
                    <button type="button" :disabled="processingCrop" @click="closeCropper">Annuler</button>
                    <button type="button" :disabled="processingCrop" @click="confirmCrop">
                        {{ processingCrop ? 'Préparation...' : 'Utiliser cette image' }}
                    </button>
                </footer>
            </div>
        </div>
    </teleport>

    <span v-if="canEdit && uploading" class="eden-upload-skeleton" role="status" aria-live="polite">
        <span class="eden-upload-content">
            <span class="eden-upload-spinner"></span>
            <span>Téléversement...</span>
        </span>
    </span>
</template>
