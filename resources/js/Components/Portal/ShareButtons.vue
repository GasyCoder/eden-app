<script setup>
import { Check, Link2, Share2 } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import Tooltip from '@/Components/ui/Tooltip.vue';
import { useT } from '@/lib/i18n';

const props = defineProps({
    title: { type: String, required: true },
});

const { t } = useT();
const url = ref('');
const copied = ref(false);
const canNativeShare = ref(false);

onMounted(() => {
    url.value = window.location.href.split('#')[0];
    canNativeShare.value = typeof navigator.share === 'function';
});

const encoded = computed(() => encodeURIComponent(url.value));
const networks = computed(() => [
    { name: 'Facebook', href: `https://www.facebook.com/sharer/sharer.php?u=${encoded.value}`, path: 'M9.1 23.7v-8H6.6V12h2.5v-1.6c0-4.1 1.8-6 5.9-6 .8 0 2.1.2 2.6.3v3.3h-1.4c-1.4 0-1.9.5-1.9 1.9V12h3.2l-.6 3.7h-2.6v8.2C19.4 23.2 24 18.2 24 12 24 5.4 18.6 0 12 0S0 5.4 0 12c0 5.6 3.9 10.4 9.1 11.7Z' },
    { name: 'LinkedIn', href: `https://www.linkedin.com/sharing/share-offsite/?url=${encoded.value}`, path: 'M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13ZM7.12 20.45H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z' },
    { name: 'X', href: `https://x.com/intent/post?url=${encoded.value}&text=${encodeURIComponent(props.title)}`, path: 'M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.65l-5.21-6.82-5.97 6.82H1.68l7.73-8.84L1.25 2.25h6.83l4.71 6.23 5.45-6.23Zm-1.16 17.52h1.83L7.08 4.13H5.12l11.96 15.64Z' },
    { name: 'WhatsApp', href: `https://wa.me/?text=${encodeURIComponent(`${props.title} ${url.value}`)}`, path: 'M.06 24l1.69-6.16A11.87 11.87 0 0 1 .16 11.9C.16 5.34 5.5 0 12.05 0a11.82 11.82 0 0 1 8.41 3.49 11.82 11.82 0 0 1 3.48 8.41c0 6.56-5.34 11.9-11.89 11.9a11.9 11.9 0 0 1-5.69-1.45L.06 24Zm6.6-3.8a9.86 9.86 0 0 0 5.39 1.59c5.45 0 9.89-4.43 9.89-9.88A9.88 9.88 0 0 0 12.06 2.02a9.89 9.89 0 0 0-8.37 15.14l-1 3.65 3.97-1Zm11.39-5.47c-.08-.12-.27-.2-.57-.35-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.21 3.07.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41Z' },
]);

const copy = async () => {
    try {
        await navigator.clipboard.writeText(url.value);
        copied.value = true;
        window.setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        window.prompt(t('copyLink'), url.value);
    }
};

const nativeShare = () => navigator.share({ title: props.title, url: url.value }).catch(() => {});
</script>

<template>
    <div class="flex flex-wrap items-center gap-0.5" role="group" :aria-label="t('share')">
        <span class="mr-2 text-xs font-medium text-muted-foreground">{{ t('share') }}</span>
        <button v-if="canNativeShare" type="button" class="inline-grid size-9 place-items-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none sm:hidden" :aria-label="t('share')" @click="nativeShare">
            <Share2 class="size-4" aria-hidden="true" />
        </button>
        <Tooltip v-for="network in networks" :key="network.name" :content="network.name">
            <a :href="network.href" class="inline-grid size-9 place-items-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none" target="_blank" rel="noopener noreferrer" :aria-label="`${t('share')} : ${network.name}`">
                <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path :d="network.path" /></svg>
            </a>
        </Tooltip>
        <Tooltip :content="copied ? t('linkCopied') : t('copyLink')">
            <button type="button" class="inline-grid size-9 place-items-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none" :aria-label="copied ? t('linkCopied') : t('copyLink')" @click="copy">
                <Check v-if="copied" class="size-4 text-eden-600" aria-hidden="true" />
                <Link2 v-else class="size-4" aria-hidden="true" />
            </button>
        </Tooltip>
        <span class="sr-only" aria-live="polite">{{ copied ? t('linkCopied') : '' }}</span>
    </div>
</template>
