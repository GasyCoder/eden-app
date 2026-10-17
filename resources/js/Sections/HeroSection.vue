<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight, ChevronLeft, ChevronRight, ImageIcon, MapPin, Pause, Play, Settings2 } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import EditableImage from '@/Components/Editable/EditableImage.vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import LinkEdit from '@/Components/Editable/LinkEdit.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants, containerClass } from '@/lib/variants';

const props = defineProps({
    blocks: { type: Object, default: () => ({}) },
    slides: { type: Array, default: () => [] },
    canEdit: { type: Boolean, default: false },
    imageVariants: { type: Object, default: () => ({}) },
});

const INTERVAL = 7000;
const { t } = useT();

// Diaporama géré dans Administration > Héro de l'accueil ; à défaut, la photo principale (hero.image).
const gallery = computed(() => (props.slides.length
    ? props.slides
    : (props.blocks?.image ? [{ key: 'main', image: props.blocks.image, title: props.blocks?.image_meta, subtitle: props.blocks?.image_caption, main: true, sources: props.imageVariants }] : [])));
const focalClass = { center: 'object-center', top: 'object-top', bottom: 'object-bottom', left: 'object-left', right: 'object-right' };

const active = ref(0);
const paused = ref(false);
const hovering = ref(false);
const reducedMotion = ref(false);
let timer = null;

const current = computed(() => gallery.value[active.value] ?? null);
const playing = computed(() => gallery.value.length > 1 && !paused.value && !hovering.value && !reducedMotion.value && !props.canEdit);
// Chaque photo dérive lentement vers un point différent (zoom continu, sans à-coup)
const drift = ['-2%, -2%', '2%, -1%', '-1%, 2%', '2%, 2%'];

const go = (index) => {
    const total = gallery.value.length;
    active.value = ((index % total) + total) % total;
    restart();
};

const restart = () => {
    window.clearTimeout(timer);

    if (playing.value) {
        timer = window.setTimeout(() => go(active.value + 1), INTERVAL);
    }
};

watch(playing, restart);
watch(() => gallery.value.length, (length) => {
    if (active.value >= length) {
        active.value = 0;
    }
});

const onVisibility = () => {
    paused.value = document.visibilityState !== 'visible' ? true : paused.value;
};

onMounted(() => {
    reducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.addEventListener('visibilitychange', onVisibility);
    restart();
});

onBeforeUnmount(() => {
    window.clearTimeout(timer);
    document.removeEventListener('visibilitychange', onVisibility);
});

</script>

<template>
    <section id="hero" class="relative overflow-hidden bg-[var(--bg-hero,var(--background))]">
        <div :class="cn(containerClass, 'grid items-center gap-12 py-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.08fr)] lg:gap-16 lg:py-20')">
            <div class="order-2 lg:order-1">
                <h1 class="text-3xl leading-tight font-semibold tracking-tight text-balance text-foreground animate-in fade-in slide-in-from-bottom-3 duration-700 fill-mode-both sm:text-4xl">
                    <EditableText :value="blocks?.title" fallback="Former les Chercheurs de Demain au Cœur des" content-key="hero.title" :can-edit="canEdit" />{{ ' ' }}
                    <span class="text-eden-600 dark:text-eden-400">
                        <EditableText :value="blocks?.title_highlight" fallback="Écosystèmes Naturels" content-key="hero.title_highlight" :can-edit="canEdit" />
                    </span>
                </h1>
                <p class="mt-5 max-w-xl leading-relaxed text-pretty text-muted-foreground animate-in fade-in slide-in-from-bottom-3 delay-150 duration-700 fill-mode-both">
                    <EditableTextarea
                        :value="blocks?.subtitle"
                        fallback="L'EDEN prépare des docteurs de haut niveau engagés dans la recherche scientifique, la conservation de la biodiversité unique de Madagascar et le développement durable de la région."
                        content-key="hero.subtitle"
                        :can-edit="canEdit"
                    />
                </p>
                <div class="mt-8 flex flex-col gap-3 animate-in fade-in slide-in-from-bottom-3 delay-300 duration-700 fill-mode-both sm:flex-row sm:flex-wrap">
                    <a :href="blocks?.button_primary_url ?? '#about'" :class="buttonVariants({ size: 'lg' })">
                        <EditableText :value="blocks?.button_primary_label" fallback="Découvrir l'EDEN" content-key="hero.button_primary_label" :can-edit="canEdit" />
                    </a>
                    <a :href="blocks?.button_secondary_url ?? '#formation'" :class="cn(buttonVariants({ variant: 'outline', size: 'lg' }), 'group')">
                        <EditableText :value="blocks?.button_secondary_label" fallback="Candidater" content-key="hero.button_secondary_label" :can-edit="canEdit" />
                        <ArrowRight class="transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                    </a>
                </div>
                <div v-if="canEdit" class="mt-3 flex flex-wrap gap-2">
                    <LinkEdit :value="blocks?.button_primary_url" fallback="#about" content-key="hero.button_primary_url" :can-edit="canEdit" />
                    <LinkEdit :value="blocks?.button_secondary_url" fallback="#formation" content-key="hero.button_secondary_url" :can-edit="canEdit" />
                </div>

                <!-- Vignettes : navigation directe dans le diaporama -->
                <div v-if="gallery.length > 1" class="mt-10 hidden items-center gap-3 animate-in fade-in delay-500 duration-700 fill-mode-both lg:flex">
                    <button
                        v-for="(slide, index) in gallery"
                        :key="slide.key"
                        type="button"
                        :class="cn('relative size-14 overflow-hidden rounded-lg ring-offset-2 ring-offset-background transition duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring', index === active ? 'ring-2 ring-eden-600' : 'opacity-60 grayscale hover:opacity-100 hover:grayscale-0')"
                        :aria-label="slide.title || t('slide', index + 1, gallery.length)"
                        :aria-current="index === active ? 'true' : undefined"
                        @click="go(index)"
                    >
                        <img :src="slide.image" alt="" class="size-full object-cover" loading="lazy" decoding="async">
                    </button>
                </div>
            </div>

            <figure class="relative order-1 animate-in fade-in zoom-in-95 duration-1000 fill-mode-both lg:order-2" :aria-roledescription="gallery.length > 1 ? 'carrousel' : undefined">
                <!-- Cadre décalé et filet tricolore du logo -->
                <span class="absolute inset-0 translate-x-3 translate-y-3 rounded-2xl bg-eden-100 sm:translate-x-4 sm:translate-y-4 dark:bg-eden-950" aria-hidden="true"></span>
                <span class="absolute -top-2 right-10 z-20 flex h-1.5 w-24 overflow-hidden rounded-full" aria-hidden="true">
                    <span class="flex-[3] bg-eden-600"></span><span class="flex-1 bg-gold-500"></span><span class="flex-1 bg-laterite-600"></span>
                </span>

                <div
                    class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-secondary shadow-xl"
                    @mouseenter="hovering = true"
                    @mouseleave="hovering = false"
                    @focusin="hovering = true"
                    @focusout="hovering = false"
                >
                    <template v-if="gallery.length">
                        <div
                            v-for="(slide, index) in gallery"
                            :key="slide.key"
                            :class="cn('absolute inset-0 transition-opacity duration-[1200ms] ease-out', index === active ? 'z-10 opacity-100' : 'z-0 opacity-0')"
                            :aria-hidden="index === active ? undefined : 'true'"
                            :aria-label="gallery.length > 1 ? t('slide', index + 1, gallery.length) : undefined"
                        >
                            <picture>
                                <source v-for="source in slide.sources?.sources ?? []" :key="source.type" :type="source.type" :srcset="source.srcset" :sizes="slide.sources?.sizes">
                                <img
                                    :src="slide.image"
                                    :alt="slide.title || 'Terrain de recherche EDEN-UMG'"
                                    :width="slide.sources?.width ?? 1536"
                                    :height="slide.sources?.height ?? 1024"
                                    :class="cn('size-full object-cover', focalClass[slide.focal] ?? 'object-center', !reducedMotion && 'animate-hero-zoom')"
                                    :style="{ '--zoom-x': drift[index % 4].split(', ')[0], '--zoom-y': drift[index % 4].split(', ')[1] }"
                                    :loading="index === 0 ? 'eager' : 'lazy'"
                                    :fetchpriority="index === 0 ? 'high' : undefined"
                                    decoding="async"
                                >
                            </picture>
                        </div>

                        <!-- Toute la photo mène au contenu relié -->
                        <component
                            :is="current?.link?.external ? 'a' : Link"
                            v-if="current?.link && !canEdit"
                            :href="current.link.url"
                            :target="current.link.external ? '_blank' : undefined"
                            :rel="current.link.external ? 'noopener noreferrer' : undefined"
                            class="absolute inset-0 z-20 focus-visible:ring-[3px] focus-visible:ring-ring/60 focus-visible:outline-none focus-visible:ring-inset"
                            :aria-label="`${current.link.label} : ${current.title ?? ''}`"
                            tabindex="-1"
                        ></component>

                        <!-- Légende de la photo active -->
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-20 h-3/5 bg-gradient-to-t from-black/75 via-black/30 to-transparent" aria-hidden="true"></div>
                        <figcaption class="pointer-events-none absolute inset-x-0 bottom-0 z-30 flex items-end justify-between gap-3 p-4 text-white sm:gap-4 sm:p-6 [&_a]:pointer-events-auto [&_button]:pointer-events-auto">
                            <Transition mode="out-in" enter-active-class="transition duration-500 ease-out" enter-from-class="translate-y-2 opacity-0" leave-active-class="transition duration-200 ease-in" leave-to-class="opacity-0">
                                <div :key="current?.key" class="grid min-w-0 gap-1">
                                    <p class="text-sm font-semibold text-pretty sm:text-base">
                                        <EditableText v-if="current?.main" :value="blocks?.image_meta" fallback="Terrain de recherche, Université de Mahajanga" content-key="hero.image_meta" :can-edit="canEdit" />
                                        <template v-else>{{ current?.title }}</template>
                                    </p>
                                    <p v-if="current?.subtitle || current?.main" class="flex items-center gap-1.5 text-xs text-white/80 sm:text-sm">
                                        <MapPin class="size-3.5 shrink-0 text-gold-300" aria-hidden="true" />
                                        <EditableText v-if="current?.main" :value="blocks?.image_caption" fallback="Région Boeny, Madagascar" content-key="hero.image_caption" :can-edit="canEdit" />
                                        <template v-else>{{ current?.subtitle }}</template>
                                    </p>
                                    <component
                                        :is="current.link.external ? 'a' : Link"
                                        v-if="current?.link"
                                        :href="current.link.url"
                                        :target="current.link.external ? '_blank' : undefined"
                                        :rel="current.link.external ? 'noopener noreferrer' : undefined"
                                        class="mt-2 inline-flex h-8 w-fit items-center gap-1.5 rounded-md bg-white px-3 text-xs font-semibold text-eden-800 shadow-sm transition-colors hover:bg-eden-50 focus-visible:ring-2 focus-visible:ring-gold-300 focus-visible:outline-none sm:h-9 sm:text-sm"
                                    >
                                        {{ current.link.label }}
                                        <ArrowUpRight v-if="current.link.external" class="size-3.5" aria-hidden="true" />
                                        <ArrowRight v-else class="size-3.5" aria-hidden="true" />
                                    </component>
                                </div>
                            </Transition>
                            <div v-if="gallery.length > 1" class="flex shrink-0 items-center gap-1 rounded-full bg-black/35 p-1 backdrop-blur-sm">
                                <button type="button" class="grid size-8 place-items-center rounded-full hover:bg-white/15 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none" :aria-label="t('previous')" @click="go(active - 1)"><ChevronLeft class="size-4" aria-hidden="true" /></button>
                                <button type="button" class="grid size-8 place-items-center rounded-full hover:bg-white/15 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none" :aria-label="paused ? t('play') : t('pause')" @click="paused = !paused">
                                    <Play v-if="paused" class="size-3.5" aria-hidden="true" /><Pause v-else class="size-3.5" aria-hidden="true" />
                                </button>
                                <button type="button" class="grid size-8 place-items-center rounded-full hover:bg-white/15 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none" :aria-label="t('next')" @click="go(active + 1)"><ChevronRight class="size-4" aria-hidden="true" /></button>
                            </div>
                        </figcaption>

                        <!-- Progression du diaporama -->
                        <div v-if="gallery.length > 1" class="absolute inset-x-5 top-5 z-30 flex gap-1.5 sm:inset-x-6" aria-hidden="true">
                            <span v-for="(slide, index) in gallery" :key="slide.key" class="h-1 flex-1 overflow-hidden rounded-full bg-white/35">
                                <span
                                    v-if="index <= active"
                                    :key="`${slide.key}-${active}`"
                                    :class="cn('block h-full origin-left rounded-full bg-white', index === active && playing && 'animate-hero-progress')"
                                ></span>
                            </span>
                        </div>
                    </template>
                    <div v-else class="flex size-full flex-col items-center justify-center gap-2 p-6 text-center text-sm text-muted-foreground">
                        <ImageIcon class="size-8 opacity-60" aria-hidden="true" />
                        <span><EditableText :value="blocks?.image_label" fallback="Photo de terrain ou de recherche" content-key="hero.image_label" :can-edit="canEdit" /></span>
                        <small><EditableText :value="blocks?.image_caption" fallback="Région Boeny, Madagascar" content-key="hero.image_caption" :can-edit="canEdit" /></small>
                    </div>
                    <EditableImage v-if="!current || current.main" content-key="hero.image" :can-edit="canEdit" />
                </div>

            </figure>
        </div>

        <!-- Mode édition : le diaporama se gère dans l'administration -->
        <div v-if="canEdit" :class="cn(containerClass, 'pb-10')">
            <div class="flex flex-wrap items-center gap-3 rounded-lg border border-dashed border-eden-300 p-3 text-sm dark:border-eden-800">
                <Settings2 class="size-4 text-eden-600" aria-hidden="true" />
                <span class="flex-1 text-muted-foreground">Photos du diaporama, liens vers les actualités, événements ou annonces, et dates de diffusion : dans l’administration.</span>
                <a href="/admin/hero" :class="buttonVariants({ variant: 'outline', size: 'sm' })">Gérer le diaporama</a>
            </div>
        </div>
    </section>
</template>
