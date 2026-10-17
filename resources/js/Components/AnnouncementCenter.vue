<script setup>
import { ArrowUpRight, CalendarDays, Info, Megaphone, OctagonAlert, Wrench } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import Dialog from '@/Components/ui/Dialog.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

/**
 * Diffusion des annonces côté visiteur.
 * - toutes les annonces défilent dans la barre au-dessus du menu (AnnouncementBar) ;
 * - important / urgence → fenêtre accessible, une seule à la fois, ouverte une fois.
 * Une annonce fermée n'est plus réaffichée (mémorisé localement ; « urgence » pour la session).
 */
const props = defineProps({
    announcements: { type: Array, default: () => [] },
    suppressDialogs: { type: Boolean, default: false },
});

const STORAGE_KEY = 'eden_announcements_dismissed';
const { t } = useT();
const dismissed = ref(new Set());
const ready = ref(false);
const dialogOpen = ref(false);
let timer = null;

const read = (storage) => {
    try {
        return JSON.parse(storage.getItem(STORAGE_KEY) ?? '[]');
    } catch {
        return [];
    }
};

const remember = (announcement) => {
    dismissed.value = new Set([...dismissed.value, announcement.key]);
    const storage = announcement.type === 'urgent' ? window.sessionStorage : window.localStorage;

    try {
        const keys = new Set(read(storage));
        keys.add(announcement.key);
        storage.setItem(STORAGE_KEY, JSON.stringify([...keys].slice(-50)));
    } catch {
        // Stockage indisponible : la fermeture vaut pour la page en cours.
    }
};

const visible = computed(() => (ready.value ? props.announcements.filter((item) => !dismissed.value.has(item.key)) : []));
const dialogAnnouncement = computed(() => (props.suppressDialogs ? null : visible.value.find((item) => item.display === 'dialog') ?? null));
const sideBySide = computed(() => Boolean(dialogAnnouncement.value?.image && (dialogAnnouncement.value.message?.length ?? 0) > 240));

const icons = { info: Info, event: CalendarDays, maintenance: Wrench, important: Megaphone, urgent: OctagonAlert };
const isExternal = (url) => /^https?:\/\//.test(url ?? '');

const dialogTone = {
    important: 'bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300',
    urgent: 'bg-laterite-50 text-laterite-600 dark:bg-laterite-950 dark:text-laterite-300',
};

const closeDialog = (open) => {
    dialogOpen.value = open;

    if (!open && dialogAnnouncement.value) {
        remember(dialogAnnouncement.value);
    }
};

onMounted(() => {
    try {
        dismissed.value = new Set([...read(window.localStorage), ...read(window.sessionStorage)]);
    } catch {
        dismissed.value = new Set();
    }

    ready.value = true;

    // Laisse la page s'afficher avant d'ouvrir une fenêtre
    if (dialogAnnouncement.value) {
        timer = window.setTimeout(() => {
            dialogOpen.value = true;
        }, 1200);
    }
});

onBeforeUnmount(() => window.clearTimeout(timer));
</script>

<template>
    <Dialog
        v-if="dialogAnnouncement"
        :open="dialogOpen"
        :title="dialogAnnouncement.title"
        :close-label="t('close')"
        :class="dialogAnnouncement.image ? (sideBySide ? 'sm:max-w-4xl' : 'sm:max-w-xl') : ''"
        @update:open="closeDialog"
    >
        <template #icon>
            <div :class="cn('grid size-10 shrink-0 place-items-center rounded-lg', dialogTone[dialogAnnouncement.type] ?? dialogTone.important)">
                <component :is="icons[dialogAnnouncement.type]" class="size-5" aria-hidden="true" />
            </div>
        </template>
        <div class="grid min-w-0 gap-5" :class="sideBySide ? 'sm:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] sm:items-start sm:gap-6' : ''">
            <figure v-if="dialogAnnouncement.image" class="grid min-w-0 gap-2">
                <a
                    :href="dialogAnnouncement.image"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex min-w-0 items-center justify-center overflow-hidden rounded-lg border bg-muted/60 p-2 outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    :aria-label="`${t('viewAnnouncementPoster')} ${t('newTab')}`"
                >
                    <img :src="dialogAnnouncement.image" :alt="`${t('announcementPoster')} : ${dialogAnnouncement.title}`" class="block max-h-[48dvh] w-full object-contain" :class="sideBySide ? 'sm:max-h-[62dvh]' : 'sm:max-h-[52dvh]'" decoding="async">
                </a>
                <figcaption>
                    <a :href="dialogAnnouncement.image" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center gap-1.5 rounded-md px-1 text-sm font-medium text-eden-700 underline-offset-4 outline-none hover:underline focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:text-eden-300">
                        {{ t('viewAnnouncementPoster') }}<ArrowUpRight class="size-4" aria-hidden="true" /><span class="sr-only">{{ t('newTab') }}</span>
                    </a>
                </figcaption>
            </figure>
            <p v-if="dialogAnnouncement.message" class="min-w-0 text-sm leading-7 break-words whitespace-pre-line text-muted-foreground">{{ dialogAnnouncement.message }}</p>
        </div>
        <template #footer>
            <button type="button" :class="cn(buttonVariants({ variant: 'outline' }), 'min-h-11')" @click="closeDialog(false)">{{ t('close') }}</button>
            <a
                v-if="dialogAnnouncement.cta_url"
                :href="dialogAnnouncement.cta_url"
                :class="cn(buttonVariants(), 'min-h-11 h-auto whitespace-normal py-2.5 text-center')"
                :target="isExternal(dialogAnnouncement.cta_url) ? '_blank' : undefined"
                :rel="isExternal(dialogAnnouncement.cta_url) ? 'noopener noreferrer' : undefined"
                @click="remember(dialogAnnouncement)"
            >{{ dialogAnnouncement.cta_label || t('learnMore') }}<ArrowUpRight v-if="isExternal(dialogAnnouncement.cta_url)" class="size-4 shrink-0" aria-hidden="true" /><span v-if="isExternal(dialogAnnouncement.cta_url)" class="sr-only">{{ t('newTab') }}</span></a>
        </template>
    </Dialog>
</template>
