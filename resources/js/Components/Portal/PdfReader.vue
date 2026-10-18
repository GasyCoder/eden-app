<script setup>
import { ChevronLeft, ChevronRight, Download, ExternalLink, FileText, LoaderCircle } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    src: { type: String, required: true },
    title: { type: String, required: true },
    downloadLabel: { type: String, required: true },
    openLabel: { type: String, required: true },
    pageLabel: { type: String, required: true },
    previousLabel: { type: String, required: true },
    nextLabel: { type: String, required: true },
    loadingLabel: { type: String, required: true },
    errorLabel: { type: String, required: true },
});

const host = ref(null);
const canvas = ref(null);
const documentRef = ref(null);
const currentPage = ref(1);
const pageCount = ref(0);
const pageWidth = ref(0);
const loading = ref(true);
const error = ref(false);
const pageText = computed(() => props.pageLabel.replace(':current', currentPage.value).replace(':total', pageCount.value));
let pdfjs;
let renderTask;
let resizeObserver;
let alive = true;

async function renderPage() {
    const pdf = documentRef.value;
    if (!pdf || !canvas.value || !pageWidth.value) return;

    renderTask?.cancel();
    const page = await pdf.getPage(currentPage.value);
    const baseViewport = page.getViewport({ scale: 1 });
    const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
    const viewport = page.getViewport({ scale: (pageWidth.value / baseViewport.width) * pixelRatio });
    const context = canvas.value.getContext('2d', { alpha: false });
    canvas.value.width = Math.ceil(viewport.width);
    canvas.value.height = Math.ceil(viewport.height);
    canvas.value.style.width = `${Math.ceil(viewport.width / pixelRatio)}px`;
    canvas.value.style.height = `${Math.ceil(viewport.height / pixelRatio)}px`;

    try {
        renderTask = page.render({ canvasContext: context, viewport, canvas: canvas.value });
        await renderTask.promise;
    } catch (exception) {
        if (exception?.name !== 'RenderingCancelledException') throw exception;
    }
}

async function loadPdf() {
    loading.value = true;
    error.value = false;

    try {
        const [library, worker] = await Promise.all([
            import('pdfjs-dist'),
            import('pdfjs-dist/build/pdf.worker.min.mjs?url'),
        ]);
        if (!alive) return;
        pdfjs = library;
        pdfjs.GlobalWorkerOptions.workerSrc = worker.default;
        const task = pdfjs.getDocument({ url: props.src, rangeChunkSize: 65536, disableAutoFetch: true, disableStream: true });
        documentRef.value = await task.promise;
        if (!alive) return;
        pageCount.value = documentRef.value.numPages;
        currentPage.value = 1;
        loading.value = false;
        await renderPage();
    } catch (exception) {
        if (exception?.name === 'RenderingCancelledException') return;
        console.error('Impossible de charger le PDF dans le lecteur intégré.', exception);
        error.value = true;
        loading.value = false;
    }
}

function setPageWidth() {
    if (host.value) pageWidth.value = Math.max(240, Math.floor(host.value.clientWidth - 32));
}

function changePage(amount) {
    currentPage.value = Math.min(pageCount.value, Math.max(1, currentPage.value + amount));
}

onMounted(() => {
    setPageWidth();
    resizeObserver = new ResizeObserver(setPageWidth);
    if (host.value) resizeObserver.observe(host.value);
    loadPdf();
});

watch([currentPage, pageWidth], () => renderPage().catch((exception) => {
    if (exception?.name !== 'RenderingCancelledException') {
        console.error('Impossible de dessiner la page PDF.', exception);
        error.value = true;
    }
}));

watch(() => props.src, loadPdf);

onBeforeUnmount(() => {
    alive = false;
    renderTask?.cancel();
    resizeObserver?.disconnect();
    documentRef.value?.destroy();
});
</script>

<template>
    <div class="overflow-hidden rounded-xl border bg-background shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-3 py-2 sm:px-4">
            <div class="flex min-w-0 items-center gap-2 text-sm font-medium">
                <FileText class="size-4 shrink-0 text-eden-700 dark:text-eden-300" aria-hidden="true" />
                <span class="truncate">{{ title }}</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="inline-flex size-10 items-center justify-center rounded-md border hover:bg-muted disabled:opacity-40" :aria-label="previousLabel" :disabled="loading || error || currentPage <= 1" @click="changePage(-1)">
                    <ChevronLeft class="size-5" aria-hidden="true" />
                </button>
                <span class="min-w-16 text-center text-sm tabular-nums" aria-live="polite">{{ pageCount ? pageText : '—' }}</span>
                <button type="button" class="inline-flex size-10 items-center justify-center rounded-md border hover:bg-muted disabled:opacity-40" :aria-label="nextLabel" :disabled="loading || error || currentPage >= pageCount" @click="changePage(1)">
                    <ChevronRight class="size-5" aria-hidden="true" />
                </button>
                <a :href="src" class="ml-1 inline-flex min-h-10 items-center gap-2 rounded-md bg-eden-700 px-3 text-sm font-medium text-white hover:bg-eden-800" :download="src.startsWith('/') ? undefined : null">
                    <Download class="size-4" aria-hidden="true" /><span class="hidden sm:inline">{{ downloadLabel }}</span>
                </a>
            </div>
        </div>

        <div ref="host" class="relative grid min-h-80 content-start justify-items-center overflow-auto bg-muted p-4 sm:min-h-[36rem]">
            <div v-if="loading" class="absolute inset-0 grid place-items-center text-sm text-muted-foreground" role="status">
                <span class="flex items-center gap-2"><LoaderCircle class="size-4 animate-spin" aria-hidden="true" />{{ loadingLabel }}</span>
            </div>
            <div v-else-if="error" class="mx-auto grid max-w-md place-items-center gap-3 py-16 text-center" role="status">
                <FileText class="size-10 text-muted-foreground" aria-hidden="true" />
                <p class="text-sm text-muted-foreground">{{ errorLabel }}</p>
                <a :href="src" class="inline-flex min-h-11 items-center gap-2 rounded-md border bg-background px-4 text-sm font-medium hover:bg-accent">
                    <ExternalLink class="size-4" aria-hidden="true" />{{ openLabel }}
                </a>
            </div>
            <canvas v-show="!loading && !error" ref="canvas" class="mx-auto max-w-full bg-white shadow-md" :aria-label="`${title} — ${pageText}`"></canvas>
        </div>
    </div>
</template>
