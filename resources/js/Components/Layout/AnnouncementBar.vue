<script setup>
import { CalendarDays, ChevronLeft, ChevronRight, Info, Mail, Megaphone, OctagonAlert, Pause, Phone, Play, Wrench } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { containerClass } from '@/lib/variants';

/**
 * Barre d'annonces au-dessus du menu : fait défiler les annonces en cours
 * (toutes les 6 s, en pause au survol ou au focus) et rappelle les contacts.
 */
const props = defineProps({
    announcements: { type: Array, default: () => [] },
    contact: { type: Object, default: () => ({}) },
});

const { t, locale } = useT();
const index = ref(0);
const paused = ref(false);
const hovering = ref(false);
let timer = null;

const items = computed(() => props.announcements);
const current = computed(() => items.value[index.value % Math.max(items.value.length, 1)] ?? null);
const icons = { info: Info, event: CalendarDays, maintenance: Wrench, important: Megaphone, urgent: OctagonAlert };
const typeLabels = {
    fr: { info: 'Info', event: 'Événement', maintenance: 'Maintenance', important: 'Important', urgent: 'Urgent' },
    en: { info: 'Info', event: 'Event', maintenance: 'Maintenance', important: 'Important', urgent: 'Urgent' },
};
const typeTone = { urgent: 'bg-laterite-600 text-white', important: 'bg-gold-500 text-gold-950', maintenance: 'bg-gold-200 text-gold-950' };
const isExternal = (url) => /^https?:\/\//.test(url ?? '');
const email = computed(() => props.contact?.email || 'contact@eden.univ-mahajanga.mg');
const phone = computed(() => (props.contact?.phone && !/x/i.test(props.contact.phone) ? props.contact.phone : ''));

const go = (delta) => {
    index.value = (index.value + delta + items.value.length) % items.value.length;
};

const start = () => {
    window.clearInterval(timer);

    if (items.value.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        timer = window.setInterval(() => {
            if (!paused.value && !hovering.value && document.visibilityState === 'visible') {
                go(1);
            }
        }, 6000);
    }
};

watch(() => items.value.length, () => {
    index.value = 0;
    start();
});

onMounted(start);
onBeforeUnmount(() => window.clearInterval(timer));
</script>

<template>
    <div
        :class="cn('bg-eden-800 text-[13px] text-eden-50 dark:bg-eden-950', !current && 'max-lg:hidden')"
        role="region"
        :aria-label="t('announcements')"
        @mouseenter="hovering = true"
        @mouseleave="hovering = false"
        @focusin="hovering = true"
        @focusout="hovering = false"
    >
        <div :class="cn(containerClass, 'flex h-10 items-center gap-4')">
            <div v-if="current" class="flex min-w-0 flex-1 items-center gap-3">
                <span :class="cn('inline-flex shrink-0 items-center gap-1.5 rounded px-1.5 py-0.5 text-[11px] font-semibold', typeTone[current.type] ?? 'bg-white/10 text-gold-300')">
                    <component :is="icons[current.type] ?? Info" class="size-3.5" aria-hidden="true" />
                    <span class="max-sm:sr-only">{{ typeLabels[locale]?.[current.type] ?? typeLabels.fr.info }}</span>
                </span>
                <div class="relative h-10 min-w-0 flex-1 overflow-hidden">
                    <Transition
                        enter-active-class="transition duration-500 ease-out"
                        enter-from-class="translate-y-full opacity-0"
                        leave-active-class="absolute inset-x-0 transition duration-300 ease-in"
                        leave-to-class="-translate-y-full opacity-0"
                    >
                        <p :key="current.key" class="flex h-10 items-center gap-2 truncate">
                            <strong class="truncate font-semibold text-white">{{ current.title }}</strong>
                            <span v-if="current.message" class="hidden truncate text-eden-100/80 md:inline">{{ current.message }}</span>
                            <a
                                v-if="current.cta_url"
                                :href="current.cta_url"
                                class="shrink-0 font-semibold text-gold-300 underline-offset-4 hover:underline focus-visible:underline focus-visible:outline-none"
                                :target="isExternal(current.cta_url) ? '_blank' : undefined"
                                :rel="isExternal(current.cta_url) ? 'noopener noreferrer' : undefined"
                            >{{ current.cta_label || t('learnMore') }} →</a>
                        </p>
                    </Transition>
                </div>
                <div v-if="items.length > 1" class="flex shrink-0 items-center gap-0.5">
                    <span class="mr-1 text-[11px] tabular-nums text-eden-100/70 max-sm:hidden">{{ (index % items.length) + 1 }}/{{ items.length }}</span>
                    <button type="button" class="grid size-7 place-items-center rounded hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-gold-300 focus-visible:outline-none" :aria-label="t('previous')" @click="go(-1)"><ChevronLeft class="size-4" aria-hidden="true" /></button>
                    <button type="button" class="grid size-7 place-items-center rounded hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-gold-300 focus-visible:outline-none max-sm:hidden" :aria-label="paused ? t('play') : t('pause')" @click="paused = !paused">
                        <Play v-if="paused" class="size-3.5" aria-hidden="true" /><Pause v-else class="size-3.5" aria-hidden="true" />
                    </button>
                    <button type="button" class="grid size-7 place-items-center rounded hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-gold-300 focus-visible:outline-none" :aria-label="t('next')" @click="go(1)"><ChevronRight class="size-4" aria-hidden="true" /></button>
                </div>
            </div>
            <p v-else class="flex-1 truncate text-eden-100/80">École Doctorale Écosystèmes Naturels, Université de Mahajanga</p>

            <div class="hidden shrink-0 items-center gap-5 border-l border-white/15 pl-5 lg:flex">
                <a :href="`mailto:${email}`" class="inline-flex items-center gap-1.5 text-eden-100/90 hover:text-white"><Mail class="size-3.5 text-gold-300" aria-hidden="true" />{{ email }}</a>
                <span v-if="phone" class="inline-flex items-center gap-1.5 text-eden-100/90"><Phone class="size-3.5 text-gold-300" aria-hidden="true" />{{ phone }}</span>
            </div>
        </div>
    </div>
</template>
