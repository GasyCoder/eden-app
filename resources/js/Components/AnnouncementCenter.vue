<script setup>
import { CalendarDays, Info, Megaphone, OctagonAlert, Wrench, X } from '@lucide/vue';
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
        @update:open="closeDialog"
    >
        <template #media>
            <div v-if="dialogAnnouncement.image" class="aspect-[16/7] border-b bg-secondary">
                <img :src="dialogAnnouncement.image" alt="" class="size-full object-cover" loading="lazy" decoding="async">
            </div>
            <div :class="cn('mx-6 mt-6 -mb-2 grid size-10 place-items-center rounded-lg', dialogTone[dialogAnnouncement.type] ?? dialogTone.important)">
                <component :is="icons[dialogAnnouncement.type]" class="size-5" aria-hidden="true" />
            </div>
        </template>
        <p v-if="dialogAnnouncement.message" class="text-sm leading-relaxed whitespace-pre-line">{{ dialogAnnouncement.message }}</p>
        <template #footer>
            <button type="button" :class="buttonVariants({ variant: 'outline' })" @click="closeDialog(false)">{{ t('close') }}</button>
            <a
                v-if="dialogAnnouncement.cta_url"
                :href="dialogAnnouncement.cta_url"
                :class="buttonVariants()"
                :target="isExternal(dialogAnnouncement.cta_url) ? '_blank' : undefined"
                :rel="isExternal(dialogAnnouncement.cta_url) ? 'noopener noreferrer' : undefined"
                @click="remember(dialogAnnouncement)"
            >{{ dialogAnnouncement.cta_label || t('learnMore') }}</a>
        </template>
    </Dialog>
</template>
