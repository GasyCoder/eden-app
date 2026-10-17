<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight, CalendarRange, Landmark, Pencil, Star, UserRound } from '@lucide/vue';
import { computed } from 'vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { badgeVariants, buttonVariants } from '@/lib/variants';

// Projet « à la une » (mis en avant dans l'administration), en tête de la section Projets.
const props = defineProps({
    project: { type: Object, required: true },
    canEdit: { type: Boolean, default: false },
});

const { t } = useT();
const progressTone = { upcoming: 'warning', ongoing: 'success', completed: 'secondary' };
const linkComponent = computed(() => (props.project.is_external ? 'a' : Link));
const linkAttrs = computed(() => (props.project.is_external
    ? { href: props.project.url, target: '_blank', rel: 'noopener noreferrer' }
    : { href: props.project.url }));
const meta = computed(() => [
    { icon: Landmark, label: t('funding'), value: props.project.funder },
    { icon: CalendarRange, label: t('period'), value: props.project.period },
    { icon: UserRound, label: t('projectLeader'), value: props.project.leader },
].filter((item) => item.value));
</script>

<template>
    <article class="group relative grid min-w-0 overflow-hidden rounded-xl border bg-card transition-shadow hover:shadow-md sm:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
        <div class="relative aspect-[16/9] overflow-hidden bg-secondary sm:aspect-auto sm:min-h-60">
            <img v-if="project.cover_image" :src="project.cover_image" :alt="project.title" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-[1.03]" loading="lazy" decoding="async">
            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-gold-500 px-2.5 py-1 text-xs font-semibold text-gold-950 shadow-sm">
                <Star class="size-3.5 fill-current" aria-hidden="true" />{{ t('featuredProject') }}
            </span>
        </div>

        <div class="flex min-w-0 flex-col gap-3 p-5 sm:p-6">
            <div class="flex flex-wrap items-center gap-2">
                <span :class="badgeVariants({ variant: progressTone[project.progress] ?? 'success', dot: true })">{{ t(`progress_${project.progress}`) }}</span>
                <span v-if="project.team" class="text-xs font-medium text-eden-700 dark:text-eden-400">{{ project.team }}</span>
            </div>
            <div class="flex items-start gap-3">
                <img v-if="project.logo" :src="project.logo" alt="" class="size-11 shrink-0 rounded-lg bg-white object-contain p-1 ring-1 ring-border" loading="lazy">
                <div class="min-w-0">
                    <h3 class="text-xl leading-snug font-semibold tracking-tight">{{ project.title }}</h3>
                    <p v-if="project.subtitle" class="mt-0.5 line-clamp-2 text-sm text-eden-700 dark:text-eden-400">{{ project.subtitle }}</p>
                </div>
            </div>
            <p v-if="project.summary" class="line-clamp-3 text-sm leading-relaxed text-muted-foreground">{{ project.summary }}</p>
            <ul v-if="meta.length" class="flex flex-wrap gap-x-5 gap-y-1.5 text-xs text-muted-foreground">
                <li v-for="item in meta" :key="item.label" class="flex items-center gap-1.5">
                    <component :is="item.icon" class="size-3.5 text-gold-600" aria-hidden="true" /><span class="sr-only">{{ item.label }} :</span>{{ item.value }}
                </li>
            </ul>
            <div v-if="project.url" class="mt-auto pt-1">
                <component :is="linkComponent" v-bind="linkAttrs" :class="cn(buttonVariants({ size: 'sm' }), 'after:absolute after:inset-0')">
                    {{ project.is_external ? t('projectWebsite') : t('discoverProject') }}
                    <ArrowUpRight v-if="project.is_external" aria-hidden="true" />
                    <ArrowRight v-else aria-hidden="true" />
                </component>
            </div>
        </div>

        <a v-if="canEdit" :href="`/admin/projects/${project.id}/edit`" class="absolute top-3 right-3 z-10 grid size-8 place-items-center rounded-full bg-white/95 text-eden-700 shadow-xs hover:bg-eden-600 hover:text-white" :aria-label="`Modifier ${project.title}`">
            <Pencil class="size-3.5" aria-hidden="true" />
        </a>
    </article>
</template>
