<script setup>
import { Link } from '@inertiajs/vue3';
import { Globe, MapPin, MonitorSmartphone } from '@lucide/vue';
import { computed } from 'vue';
import { dateParts, formatEventRange, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { badgeVariants } from '@/lib/variants';

const props = defineProps({
    event: { type: Object, required: true },
});

const { t, path, locale } = useT();
const parts = computed(() => dateParts(props.event.starts_at, locale.value));
const formatLabel = computed(() => ({ in_person: t('inPerson'), online: t('online'), hybrid: t('hybrid') }[props.event.format]));
const formatIcon = computed(() => ({ in_person: MapPin, online: Globe, hybrid: MonitorSmartphone }[props.event.format] ?? MapPin));
const cancelled = computed(() => props.event.status === 'cancelled');
const statusBadge = computed(() => {
    if (props.event.status === 'cancelled') return { label: t('cancelled'), variant: 'destructive' };
    if (props.event.status === 'completed') return { label: t('completed'), variant: 'success' };
    if (props.event.status === 'archived') return { label: t('archived'), variant: 'outline' };

    return props.event.is_past ? { label: t('past'), variant: 'outline' } : null;
});
</script>

<template>
    <article class="group relative flex gap-4 rounded-xl border bg-card p-4 shadow-xs transition-all hover:border-eden-300 hover:shadow-md sm:gap-5 sm:p-5 dark:hover:border-eden-700">
        <div :class="cn('flex w-14 shrink-0 flex-col items-center justify-center self-start overflow-hidden rounded-xl border text-center sm:w-16', event.is_past && 'opacity-70')" aria-hidden="true">
            <span class="w-full bg-eden-600 py-1 text-[11px] font-semibold tracking-wide text-white uppercase">{{ parts.month }}</span>
            <span class="py-1.5 text-2xl font-bold tracking-tight tabular-nums">{{ parts.day }}</span>
        </div>
        <div class="grid min-w-0 flex-1 gap-1.5">
            <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                <span v-if="statusBadge" :class="badgeVariants({ variant: statusBadge.variant })">{{ statusBadge.label }}</span>
                <time :datetime="event.starts_at">{{ formatEventRange(event, locale) }}</time>
            </div>
            <h3 :class="cn('text-base leading-snug font-semibold text-foreground', cancelled && 'line-through decoration-muted-foreground/60')">
                <Link :href="path('events', event.slug)" class="outline-none after:absolute after:inset-0 after:rounded-xl group-hover:text-eden-700 focus-visible:after:ring-[3px] focus-visible:after:ring-ring/50 dark:group-hover:text-eden-300">{{ event.title }}</Link>
            </h3>
            <p v-if="event.summary" class="line-clamp-2 text-sm leading-relaxed text-muted-foreground">{{ event.summary }}</p>
            <p class="mt-0.5 flex items-start gap-1.5 text-sm text-muted-foreground">
                <component :is="formatIcon" class="mt-0.5 size-4 shrink-0 text-laterite-600 dark:text-laterite-400" aria-hidden="true" />
                <span class="min-w-0">{{ event.location || formatLabel }}</span>
                <span v-if="event.location && event.format !== 'in_person'" class="shrink-0 rounded-md border px-1.5 text-xs">{{ formatLabel }}</span>
            </p>
        </div>
    </article>
</template>
