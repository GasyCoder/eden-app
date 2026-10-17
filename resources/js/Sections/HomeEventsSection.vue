<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, CalendarDays } from '@lucide/vue';
import EventCard from '@/Components/Portal/EventCard.vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useT } from '@/lib/i18n';
import { buttonVariants, containerClass } from '@/lib/variants';

defineProps({
    events: { type: Array, default: () => [] },
    canEdit: { type: Boolean, default: false },
});

const { t, path } = useT();
</script>

<template>
    <section id="evenements" class="bg-background py-16 lg:py-20">
        <div :class="containerClass">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading>{{ t('upcomingEvents') }}</SectionHeading>
                <Link v-if="events.length" :href="path('events')" :class="buttonVariants({ variant: 'outline' })">{{ t('seeAll') }}<ArrowRight aria-hidden="true" /></Link>
            </div>
            <div v-if="events.length" class="mt-10 grid gap-4 lg:grid-cols-2">
                <EventCard v-for="event in events" :key="event.id" :event="event" />
            </div>
            <p v-else-if="!canEdit" class="mt-8 max-w-xl text-muted-foreground">{{ t('noEventsYet') }}</p>
            <EmptyState v-else class="mt-10" :icon="CalendarDays" title="Aucun événement à venir" description="Cette section apparaît sur l’accueil dès qu’un événement à venir est publié (visible uniquement par les administrateurs).">
                <Link href="/admin/events/create" :class="buttonVariants({ variant: 'outline', size: 'sm' })">Créer un événement</Link>
            </EmptyState>
        </div>
    </section>
</template>
