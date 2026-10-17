<script setup>
import { computed, nextTick, reactive, ref } from 'vue';
import { ImageUp, Minus, Plus, X } from '@lucide/vue';
import { adminPatch } from '@/Composables/useAdminRequest';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

const props = defineProps({
    contentKey: {
        type: String,
        required: true,
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    // Pastille ronde (logo) plutôt que bouton libellé
    compact: {
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
    <span v-if="canEdit" :class="cn('absolute z-10 grid justify-items-end gap-2', compact ? '-top-2 -right-2' : 'top-3 right-3')">
        <button
            type="button"
            :class="cn(
                'inline-flex items-center gap-1.5 rounded-full border bg-background/95 text-xs font-semibold text-foreground shadow-md transition-colors hover:border-eden-600 hover:bg-eden-600 hover:text-white focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:opacity-60',
                compact ? 'size-6 justify-center p-0' : 'h-8 px-3',
            )"
            :disabled="uploading || processingCrop"
            :aria-label="uploading ? 'Téléversement en cours' : 'Remplacer l’image'"
            @click.stop.prevent="choose"
        >
            <ImageUp :class="compact ? 'size-3' : 'size-4'" aria-hidden="true" />
            <span v-if="!compact">{{ uploading ? 'Envoi…' : 'Remplacer' }}</span>
        </button>
        <input ref="input" type="file" accept="image/*" hidden @change="onFileChange">
        <span v-if="error" class="max-w-56 rounded-md bg-laterite-50 px-2.5 py-1.5 text-xs font-medium text-laterite-700" role="alert">{{ error }}</span>
    </span>

    <Teleport to="body">
        <div v-if="cropOpen" class="fixed inset-0 z-[100] grid place-items-center overflow-y-auto bg-black/50 p-4" role="presentation" @keydown.esc.prevent="closeCropper">
            <section class="flex w-full max-w-2xl flex-col overflow-hidden rounded-xl border bg-popover shadow-lg" role="dialog" aria-modal="true" aria-labelledby="crop-title">
                <header class="flex items-start justify-between gap-4 px-6 pt-6">
                    <div>
                        <h2 id="crop-title" class="text-lg font-semibold">{{ preset?.title }}</h2>
                        <p class="mt-1 text-sm text-muted-foreground">{{ preset?.hint }} Faites glisser l’image pour choisir le cadrage.</p>
                    </div>
                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" :disabled="processingCrop" aria-label="Fermer" @click="closeCropper"><X aria-hidden="true" /></button>
                </header>
                <div class="px-6 py-5">
                    <div
                        ref="cropFrame"
                        :class="cn('relative mx-auto w-full max-w-xl touch-none overflow-hidden rounded-lg bg-neutral-950 select-none', drag.active ? 'cursor-grabbing' : 'cursor-grab')"
                        :style="cropFrameStyle"
                        @pointerdown.prevent="startDrag"
                        @pointermove.prevent="moveDrag"
                        @pointerup.prevent="endDrag"
                        @pointercancel.prevent="endDrag"
                    >
                        <img ref="cropImageElement" :src="objectUrl" alt="" class="pointer-events-none absolute top-1/2 left-1/2 max-w-none" :style="cropImageStyle" draggable="false" @load="onImageLoaded">
                        <span class="pointer-events-none absolute inset-0 rounded-lg border-2 border-white/85 bg-[linear-gradient(to_right,transparent_33%,rgba(255,255,255,.35)_33%,rgba(255,255,255,.35)_calc(33%+1px),transparent_calc(33%+1px),transparent_66%,rgba(255,255,255,.35)_66%,rgba(255,255,255,.35)_calc(66%+1px),transparent_calc(66%+1px)),linear-gradient(to_bottom,transparent_33%,rgba(255,255,255,.35)_33%,rgba(255,255,255,.35)_calc(33%+1px),transparent_calc(33%+1px),transparent_66%,rgba(255,255,255,.35)_66%,rgba(255,255,255,.35)_calc(66%+1px),transparent_calc(66%+1px))]" aria-hidden="true"></span>
                    </div>
                    <div class="mx-auto mt-5 flex w-full max-w-md items-center gap-3">
                        <button type="button" :class="buttonVariants({ variant: 'outline', size: 'icon-sm' })" :disabled="processingCrop" aria-label="Zoom arrière" @click="zoomBy(-0.1)"><Minus aria-hidden="true" /></button>
                        <input v-model="crop.zoom" type="range" min="1" max="3" step="0.01" class="flex-1 accent-eden-600" aria-label="Zoom" :disabled="processingCrop" @input="onZoom">
                        <button type="button" :class="buttonVariants({ variant: 'outline', size: 'icon-sm' })" :disabled="processingCrop" aria-label="Zoom avant" @click="zoomBy(0.1)"><Plus aria-hidden="true" /></button>
                    </div>
                </div>
                <footer class="flex flex-col-reverse gap-2 border-t bg-muted/50 px-6 py-4 sm:flex-row sm:justify-end">
                    <button type="button" :class="buttonVariants({ variant: 'outline' })" :disabled="processingCrop" @click="closeCropper">Annuler</button>
                    <button type="button" :class="buttonVariants()" :disabled="processingCrop" @click="confirmCrop">
                        <span v-if="processingCrop" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        {{ processingCrop ? 'Préparation…' : 'Utiliser cette image' }}
                    </button>
                </footer>
            </section>
        </div>
    </Teleport>

    <span v-if="canEdit && uploading" class="absolute inset-0 z-[5] grid place-items-center bg-background/80 text-sm font-medium backdrop-blur-[2px]" role="status" aria-live="polite">
        <span class="inline-flex items-center gap-2"><span class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span> Téléversement…</span>
    </span>
</template>
