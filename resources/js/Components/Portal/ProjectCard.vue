<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight, CalendarRange, FolderKanban, Landmark, Pencil, UserRound } from '@lucide/vue';
import { computed } from 'vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { badgeVariants } from '@/lib/variants';

// Carte projet compacte : seules les informations connues sont affichées.
const props = defineProps({
    project: { type: Object, required: true },
    list: { type: Boolean, default: false },
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
    <article :class="cn('group relative flex min-w-0 overflow-hidden rounded-xl border bg-card transition-all duration-300 hover:-translate-y-0.5 hover:border-eden-200 hover:shadow-md dark:hover:border-eden-800', list ? 'flex-row' : 'flex-row sm:flex-col')">
        <div :class="cn('relative w-28 shrink-0 self-stretch overflow-hidden bg-secondary', list ? 'sm:w-56' : 'sm:aspect-[16/9] sm:w-auto sm:self-auto')">
            <img v-if="project.cover_image" :src="project.cover_image" :alt="project.title" class="size-full object-cover transition duration-500 group-hover:scale-[1.04]" width="480" height="270" loading="lazy" decoding="async">
            <div v-else class="flex size-full items-center justify-center bg-eden-50 dark:bg-eden-950">
                <img v-if="project.logo" :src="project.logo" alt="" class="max-h-14 max-w-[45%] object-contain" loading="lazy">
                <FolderKanban v-else class="size-8 text-eden-600/60 dark:text-eden-400/60" aria-hidden="true" />
            </div>
            <span :class="cn(badgeVariants({ variant: progressTone[project.progress] ?? 'success', dot: true }), 'absolute top-3 left-3 bg-white/95 shadow-xs max-sm:hidden dark:bg-background/90')">{{ t(`progress_${project.progress}`) }}</span>
            <span v-if="project.logo && project.cover_image" class="absolute right-3 bottom-3 max-sm:hidden grid size-9 place-items-center rounded-md bg-white p-1 shadow-sm">
                <img :src="project.logo" alt="" class="max-h-full max-w-full object-contain" loading="lazy">
            </span>
        </div>

        <div class="flex min-w-0 flex-1 flex-col gap-1.5 p-3 sm:gap-2 sm:p-4">
            <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-eden-700 dark:text-eden-400">
                <span :class="cn(badgeVariants({ variant: progressTone[project.progress] ?? 'success', dot: true }), 'sm:hidden')">{{ t(`progress_${project.progress}`) }}</span>
                <span v-if="project.team">{{ project.team }}</span>
            </p>
            <h3 class="line-clamp-2 text-base leading-snug font-semibold tracking-tight">
                <component :is="linkComponent" v-if="project.url" v-bind="linkAttrs" class="outline-none after:absolute after:inset-0 group-hover:text-eden-700 focus-visible:after:rounded-xl focus-visible:after:ring-[3px] focus-visible:after:ring-ring/50 dark:group-hover:text-eden-300">{{ project.title }}</component>
                <template v-else>{{ project.title }}</template>
            </h3>
            <p v-if="project.summary" class="line-clamp-2 text-sm leading-relaxed text-muted-foreground max-sm:hidden">{{ project.summary }}</p>

            <div class="mt-auto flex items-end justify-between gap-3 pt-2">
                <ul v-if="meta.length" class="grid min-w-0 gap-1 text-xs text-muted-foreground">
                    <li v-for="item in meta" :key="item.label" class="flex min-w-0 items-center gap-1.5" :title="`${item.label} : ${item.value}`">
                        <component :is="item.icon" class="size-3.5 shrink-0 text-gold-600" aria-hidden="true" />
                        <span class="sr-only">{{ item.label }} :</span>
                        <span class="truncate">{{ item.value }}</span>
                    </li>
                </ul>
                <span v-if="project.url" class="ml-auto grid size-8 shrink-0 max-sm:size-7 place-items-center rounded-full border text-eden-700 transition-colors group-hover:border-eden-600 group-hover:bg-eden-600 group-hover:text-white dark:text-eden-300" aria-hidden="true">
                    <ArrowUpRight v-if="project.is_external" class="size-4" />
                    <ArrowRight v-else class="size-4" />
                </span>
            </div>
        </div>

        <a v-if="canEdit" :href="`/admin/projects/${project.id}/edit`" class="absolute top-3 right-3 z-10 grid size-8 place-items-center rounded-full bg-white/95 text-eden-700 shadow-xs hover:bg-eden-600 hover:text-white" :aria-label="`Modifier ${project.title}`">
            <Pencil class="size-3.5" aria-hidden="true" />
        </a>
    </article>
</template>
