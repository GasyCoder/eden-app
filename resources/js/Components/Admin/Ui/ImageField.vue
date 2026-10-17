<script setup>
import { ImagePlus, Link2, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass } from '@/lib/variants';

// Image de couverture : téléversement (même API que l'édition en place), aperçu, retrait.
const props = defineProps({
    label: { type: String, default: 'Image' },
    hint: { type: String, default: 'JPG, PNG ou WebP, 4 Mo maximum.' },
    aspect: { type: String, default: '16 / 9' },
    error: { type: String, default: '' },
    // Préréglage des variantes responsives (ex. « hero.image » pour les grandes images)
    preset: { type: String, default: '' },
    // Vignette carrée à côté des actions (logos, icônes) : l'image est affichée en entier
    compact: { type: Boolean, default: false },
});

const model = defineModel({ type: String, default: '' });
const uploading = ref(false);
const uploadError = ref('');
const showUrl = ref(false);
const dragging = ref(false);

const upload = async (file) => {
    if (!file) {
        return;
    }

    uploading.value = true;
    uploadError.value = '';

    try {
        const data = new FormData();
        data.append('image', file);

        if (props.preset) {
            data.append('content_key', props.preset);
        }

        const response = await fetch('/admin/uploads/images', {
            method: 'POST',
            body: data,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                Accept: 'application/json',
            },
        });
        const payload = await response.json().catch(() => ({}));

        if (!response.ok || !payload.path) {
            uploadError.value = payload.message ?? 'Image refusée. Vérifiez le format et la taille.';
            return;
        }

        model.value = payload.path;
    } catch {
        uploadError.value = 'Téléversement impossible. Vérifiez votre connexion.';
    } finally {
        uploading.value = false;
    }
};

const onChange = (event) => {
    upload(event.target.files?.[0]);
    event.target.value = '';
};

const onDrop = (event) => {
    dragging.value = false;
    upload(event.dataTransfer?.files?.[0]);
};
</script>

<template>
    <div class="grid min-w-0 grid-cols-1 gap-2">
        <span class="text-sm font-semibold">{{ label }}</span>
        <div :class="compact ? 'flex items-start gap-4' : 'contents'">
        <div
            :class="['relative grid place-items-center overflow-hidden rounded-lg border bg-muted transition-colors', compact ? 'size-24 shrink-0' : 'w-full', model ? 'border-border' : 'border-dashed border-input', model && compact && 'bg-white', dragging && 'border-ring bg-eden-50 dark:bg-eden-950']"
            :style="compact ? undefined : { aspectRatio: aspect }"
            @dragover.prevent="dragging = true"
            @dragleave="dragging = false"
            @drop.prevent="onDrop"
        >
            <img v-if="model" :src="model" alt="Aperçu de l’image" :class="compact ? 'size-full object-contain p-2' : 'size-full object-cover'">
            <label v-else :class="['grid size-full cursor-pointer content-center justify-items-center gap-2 text-center text-sm text-muted-foreground focus-within:ring-[3px] focus-within:ring-ring/50', compact ? 'p-2 text-xs' : 'p-4']">
                <ImagePlus :class="compact ? 'size-5' : 'size-6'" aria-hidden="true" />
                <span v-if="compact" class="font-medium text-foreground">Choisir</span>
                <span v-else><strong class="font-medium text-foreground">Choisir une image</strong> ou la déposer ici</span>
                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" :disabled="uploading" @change="onChange">
            </label>
            <div v-if="uploading" class="absolute inset-0 grid place-items-center bg-background/80 text-sm font-medium backdrop-blur-[2px]" role="status">
                <span class="inline-flex items-center gap-2"><span class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span> Téléversement…</span>
            </div>
        </div>
        <div :class="compact ? 'grid min-w-0 flex-1 content-start gap-2' : 'contents'">
        <div v-if="model" class="flex flex-wrap gap-2">
            <label :class="buttonVariants({ variant: 'outline', size: 'sm' })" class="relative cursor-pointer focus-within:ring-[3px] focus-within:ring-ring/50">
                <ImagePlus aria-hidden="true" />Remplacer
                <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" :disabled="uploading" @change="onChange">
            </label>
            <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'sm' })" @click="model = ''"><Trash2 aria-hidden="true" />Retirer</button>
        </div>
        <p v-if="hint && !uploadError && !error" class="text-xs text-muted-foreground">{{ hint }}</p>
        <p v-if="uploadError || error" class="text-xs font-medium text-destructive" role="alert">{{ uploadError || error }}</p>
        <button v-if="!showUrl" type="button" :class="cn(buttonVariants({ variant: 'link', size: 'sm' }), 'h-auto justify-self-start px-0 text-left text-xs whitespace-normal')" @click="showUrl = true"><Link2 aria-hidden="true" />Utiliser une adresse d’image</button>
        <input v-else v-model="model" type="text" inputmode="url" :class="inputClass" class="h-9" placeholder="https://… ou /storage/…" aria-label="Adresse de l’image">
        </div>
        </div>
    </div>
</template>
