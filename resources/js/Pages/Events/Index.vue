<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { CalendarX } from '@lucide/vue';
import EventCard from '@/Components/Portal/EventCard.vue';
import PageHeader from '@/Components/Portal/PageHeader.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useT } from '@/lib/i18n';
import { buttonVariants } from '@/lib/variants';

defineProps({
    period: { type: String, default: 'upcoming' },
    events: { type: Object, required: true },
    counts: { type: Object, default: () => ({ upcoming: 0, past: 0 }) },
});

const { t, path } = useT();
</script>

<template>
    <Head :title="t('events')" />

    <PublicLayout :editable="false">
        <PageHeader :title="t('events')" :lead="t('eventsLead')" :crumbs="[{ label: t('events') }]" />

        <section class="py-12 lg:py-16">
            <div class="mx-auto w-full max-w-4xl px-4 sm:px-6 lg:px-8">
                <nav class="mb-8 inline-flex items-center gap-1 rounded-lg bg-secondary p-1" :aria-label="t('events')">
                    <Link :href="path('events')" class="inline-flex h-9 items-center gap-2 rounded-md px-4 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground aria-[current=page]:bg-background aria-[current=page]:text-foreground aria-[current=page]:shadow-xs dark:aria-[current=page]:bg-accent" :aria-current="period === 'upcoming' ? 'page' : undefined" preserve-scroll>
                        {{ t('upcoming') }} <span class="text-xs tabular-nums text-muted-foreground">{{ counts.upcoming }}</span>
                    </Link>
                    <Link :href="`${path('events')}?periode=passes`" class="inline-flex h-9 items-center gap-2 rounded-md px-4 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground aria-[current=page]:bg-background aria-[current=page]:text-foreground aria-[current=page]:shadow-xs dark:aria-[current=page]:bg-accent" :aria-current="period === 'past' ? 'page' : undefined" preserve-scroll>
                        {{ t('past') }} <span class="text-xs tabular-nums text-muted-foreground">{{ counts.past }}</span>
                    </Link>
                </nav>

                <template v-if="events.data.length">
                    <div class="grid gap-4">
                        <EventCard v-for="event in events.data" :key="event.id" :event="event" />
                    </div>
                    <Pagination :meta="events.meta" :links="events.links" :labels="{ previous: t('previous'), next: t('next') }" />
                </template>
                <EmptyState v-else-if="period === 'upcoming'" :icon="CalendarX" :title="t('noUpcoming')" :description="t('noUpcomingHint')">
                    <Link v-if="counts.past" :href="`${path('events')}?periode=passes`" :class="buttonVariants({ variant: 'outline', size: 'sm' })">{{ t('past') }}</Link>
                </EmptyState>
                <EmptyState v-else :icon="CalendarX" :title="t('noPast')" />
            </div>
        </section>
    </PublicLayout>
</template>
