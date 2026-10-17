<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import { computed } from 'vue';
import { useT } from '@/lib/i18n';

// Carte revue compacte : petite couverture (ou couverture typographique), titre, repères.
const props = defineProps({
    journal: { type: Object, required: true },
});

const { t, path } = useT();
const initials = computed(() => props.journal.title.split(/[\s’']+/).filter((word) => word.length > 3).map((word) => word[0].toUpperCase()).slice(0, 3).join(''));
const meta = computed(() => [
    props.journal.frequency,
    props.journal.latest_issue ? `${t('latestIssue')} ${props.journal.latest_issue}` : null,
    props.journal.issn ? `ISSN ${props.journal.issn}` : null,
].filter(Boolean));
</script>

<template>
    <article class="group relative flex min-w-0 gap-4 rounded-xl border bg-card p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-eden-200 hover:shadow-md dark:hover:border-eden-800">
        <div class="aspect-[3/4] w-16 shrink-0 overflow-hidden rounded-md bg-secondary shadow-sm ring-1 ring-black/5 sm:w-[4.5rem]">
            <img v-if="journal.cover_image" :src="journal.cover_image" alt="" class="size-full object-cover object-top" loading="lazy" decoding="async">
            <div v-else class="flex size-full flex-col justify-between bg-eden-50 p-1.5 dark:bg-eden-950">
                <span class="h-0.5 w-6 rounded-full bg-gold-500" aria-hidden="true"></span>
                <span class="text-sm leading-none font-bold text-eden-700 dark:text-eden-300" aria-hidden="true">{{ initials }}</span>
                <span class="flex h-0.5 overflow-hidden rounded-full" aria-hidden="true"><span class="flex-[3] bg-eden-600"></span><span class="flex-1 bg-gold-500"></span><span class="flex-1 bg-laterite-600"></span></span>
            </div>
        </div>
        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <p v-if="journal.domain || journal.status === 'archived'" class="truncate text-xs font-medium text-eden-700 dark:text-eden-400">
                {{ journal.domain }}<template v-if="journal.status === 'archived'"><template v-if="journal.domain"> · </template>{{ t('archived') }}</template>
            </p>
            <h3 class="line-clamp-2 text-sm leading-snug font-semibold sm:text-base">
                <Link :href="path('journals', journal.slug)" class="outline-none after:absolute after:inset-0 after:rounded-xl group-hover:text-eden-700 focus-visible:after:ring-[3px] focus-visible:after:ring-ring/50 dark:group-hover:text-eden-300">{{ journal.title }}</Link>
            </h3>
            <p v-if="journal.summary" class="line-clamp-2 text-xs leading-relaxed text-muted-foreground">{{ journal.summary }}</p>
            <div class="mt-auto flex items-end justify-between gap-2 pt-1">
                <p class="min-w-0 truncate text-xs text-muted-foreground tabular-nums">{{ meta.join(' · ') }}</p>
                <ArrowRight class="size-4 shrink-0 text-muted-foreground transition-all group-hover:translate-x-0.5 group-hover:text-eden-600" aria-hidden="true" />
            </div>
        </div>
    </article>
</template>
