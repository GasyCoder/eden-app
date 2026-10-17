<script setup>
import { FileText, FileUp, Link2, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import { buttonVariants, inputClass } from '@/lib/variants';

// Fichier PDF d'un numéro : téléversement avec progression, ou lien externe.
const props = defineProps({
    label: { type: String, default: 'Fichier PDF' },
    error: { type: String, default: '' },
});

const emit = defineEmits(['uploaded']);
const model = defineModel({ type: String, default: '' });
const uploading = ref(false);
const progress = ref(0);
const uploadError = ref('');
const showUrl = ref(false);
const dragging = ref(false);
const fileName = computed(() => decodeURIComponent((model.value ?? '').split('/').pop() ?? ''));

const upload = (file) => {
    if (!file) {
        return;
    }

    if (file.type && file.type !== 'application/pdf') {
        uploadError.value = 'Seuls les fichiers PDF sont acceptés.';
        return;
    }

    uploading.value = true;
    progress.value = 0;
    uploadError.value = '';

    const data = new FormData();
    data.append('document', file);
    const request = new XMLHttpRequest();
    request.open('POST', '/admin/uploads/documents');
    request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content ?? '');
    request.setRequestHeader('Accept', 'application/json');
    request.upload.onprogress = (event) => {
        if (event.lengthComputable) {
            progress.value = Math.round((event.loaded / event.total) * 100);
        }
    };
    request.onload = () => {
        uploading.value = false;
        let payload = {};

        try {
            payload = JSON.parse(request.responseText);
        } catch {
            payload = {};
        }

        if (request.status >= 200 && request.status < 300 && payload.path) {
            model.value = payload.path;
            emit('uploaded', payload);
            return;
        }

        uploadError.value = request.status === 413
            ? 'Fichier trop lourd pour le serveur. Augmentez upload_max_filesize et post_max_size, ou utilisez un lien externe.'
            : (payload.errors?.document?.[0] ?? payload.message ?? 'PDF refusé.');
    };
    request.onerror = () => {
        uploading.value = false;
        uploadError.value = 'Téléversement impossible. Vérifiez votre connexion.';
    };
    request.send(data);
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
    <div class="grid gap-2">
        <span class="text-sm font-semibold">{{ label }}</span>
        <div
            v-if="!model || uploading"
            :class="['relative grid min-h-32 place-items-center rounded-lg border border-dashed border-input bg-muted transition-colors', dragging && 'border-ring bg-eden-50 dark:bg-eden-950']"
            @dragover.prevent="dragging = true"
            @dragleave="dragging = false"
            @drop.prevent="onDrop"
        >
            <div v-if="uploading" class="grid w-full gap-2 p-5" role="status">
                <span class="text-sm font-medium">Téléversement… {{ progress }} %</span>
                <span class="h-1.5 overflow-hidden rounded-full bg-secondary"><span class="block h-full rounded-full bg-eden-600 transition-[width]" :style="{ width: `${progress}%` }"></span></span>
            </div>
            <label v-else class="grid size-full cursor-pointer content-center justify-items-center gap-2 p-5 text-center text-sm text-muted-foreground focus-within:ring-[3px] focus-within:ring-ring/50">
                <FileUp class="size-6" aria-hidden="true" />
                <span><strong class="font-medium text-foreground">Choisir le PDF</strong> ou le déposer ici</span>
                <input type="file" accept="application/pdf" class="sr-only" @change="onChange">
            </label>
        </div>
        <div v-else class="flex items-center gap-3 rounded-lg bg-muted p-3">
            <span class="grid size-10 shrink-0 place-items-center rounded-md bg-laterite-600 text-white"><FileText class="size-5" aria-hidden="true" /></span>
            <a :href="model" target="_blank" rel="noopener" class="min-w-0 flex-1 truncate text-sm font-medium hover:underline hover:underline-offset-4">{{ fileName || model }}</a>
            <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'icon-sm' })" aria-label="Retirer le PDF" @click="model = ''"><Trash2 aria-hidden="true" /></button>
        </div>
        <p v-if="!uploadError && !error" class="text-xs text-muted-foreground">PDF, 50 Mo maximum (selon la configuration du serveur).</p>
        <p v-if="uploadError || error" class="text-xs font-medium text-destructive" role="alert">{{ uploadError || error }}</p>
        <button v-if="!showUrl" type="button" :class="buttonVariants({ variant: 'link', size: 'sm' })" class="justify-self-start text-xs" @click="showUrl = true"><Link2 aria-hidden="true" />Utiliser un lien externe (Drive, site…)</button>
        <input v-else v-model="model" type="text" inputmode="url" :class="inputClass" class="h-9" placeholder="https://… ou /storage/…" aria-label="Adresse du PDF">
    </div>
</template>
