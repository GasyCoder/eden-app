<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { CalendarPlus, Clock, ExternalLink, Eye, Globe, MapPin, UserRound } from '@lucide/vue';
import { computed } from 'vue';
import EventCard from '@/Components/Portal/EventCard.vue';
import ShareButtons from '@/Components/Portal/ShareButtons.vue';
import PageHeader from '@/Components/Portal/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatEventRange, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { badgeVariants, buttonVariants, containerClass } from '@/lib/variants';

const props = defineProps({
    event: { type: Object, required: true },
    otherEvents: { type: Array, default: () => [] },
});

const { t, path, locale } = useT();
const formatLabel = computed(() => ({ in_person: t('inPerson'), online: t('online'), hybrid: t('hybrid') }[props.event.format]));

// Fichier .ics généré dans le navigateur (dates en UTC)
const icsHref = computed(() => {
    const stamp = (iso) => new Date(iso).toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');
    const start = props.event.starts_at;
    const end = props.event.ends_at ?? new Date(new Date(start).getTime() + 2 * 3600 * 1000).toISOString();
    const escape = (value = '') => String(value).replace(/([,;\\])/g, '\\$1').replace(/\n/g, '\\n');
    const lines = [
        'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//EDEN-UMG//Evenements//FR', 'BEGIN:VEVENT',
        `UID:eden-event-${props.event.id}@eden`,
        `DTSTAMP:${stamp(new Date().toISOString())}`,
        `DTSTART:${stamp(start)}`, `DTEND:${stamp(end)}`,
        `SUMMARY:${escape(props.event.title)}`,
        props.event.location ? `LOCATION:${escape(props.event.location)}` : null,
        props.event.summary ? `DESCRIPTION:${escape(props.event.summary)}` : null,
        'END:VEVENT', 'END:VCALENDAR',
    ].filter(Boolean);

    return `data:text/calendar;charset=utf-8,${encodeURIComponent(lines.join('\r\n'))}`;
});
</script>

<template>
    <Head :title="event.title">
        <meta v-if="event.summary" name="description" :content="event.summary">
    </Head>

    <PublicLayout :editable="false">
        <PageHeader :title="event.title" :lead="event.summary" :crumbs="[{ label: t('events'), href: path('events') }]">
            <div class="mt-6 flex flex-wrap gap-2">
                <span v-if="event.status === 'cancelled'" :class="badgeVariants({ variant: 'destructive' })">{{ t('cancelled') }}</span>
                <span v-else-if="event.status === 'completed'" :class="badgeVariants({ variant: 'success' })">{{ t('completed') }}</span>
                <span v-else-if="event.status === 'archived'" :class="badgeVariants({ variant: 'outline' })">{{ t('archived') }}</span>
                <span v-else-if="event.is_past" class="inline-flex rounded-md bg-white/10 px-2 py-0.5 text-xs font-medium">{{ t('past') }}</span>
                <span class="inline-flex rounded-md bg-gold-400 px-2 py-0.5 text-xs font-semibold text-eden-950">{{ formatLabel }}</span>
            </div>
        </PageHeader>

        <section class="py-12 lg:py-16">
            <div :class="cn(containerClass, 'grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-14')">
                <div class="grid min-w-0 gap-8">
                    <div v-if="!event.is_public" class="flex items-start gap-3 rounded-lg border border-gold-300 bg-gold-50 p-4 text-sm text-gold-900 dark:border-gold-800 dark:bg-gold-950 dark:text-gold-200" role="status">
                        <Eye class="mt-0.5 size-4 shrink-0" aria-hidden="true" /><span>{{ t('draftPreview') }}</span>
                    </div>
                    <figure v-if="event.cover_image" class="aspect-video overflow-hidden rounded-xl border bg-secondary">
                        <img :src="event.cover_image" :alt="event.title" class="size-full object-cover" decoding="async">
                    </figure>
                    <!-- Contenu nettoyé côté serveur (App\Services\RichTextSanitizer) -->
                    <div v-if="event.body" class="prose prose-eden max-w-none" v-html="event.body"></div>
                </div>
                <aside class="order-first grid gap-4 lg:sticky lg:top-24 lg:order-none">
                    <div class="grid gap-6 rounded-xl border bg-card p-6 shadow-xs">
                        <dl class="grid gap-5">
                            <div v-for="fact in [
                                { icon: Clock, label: t('when'), value: formatEventRange(event, locale) + (event.is_all_day ? ` · ${t('allDay')}` : '') },
                                { icon: MapPin, label: t('where'), value: event.location },
                                { icon: Globe, label: t('format'), value: formatLabel },
                                { icon: UserRound, label: t('organizer'), value: event.organizer },
                            ].filter((item) => item.value)" :key="fact.label" class="flex gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300"><component :is="fact.icon" class="size-4" aria-hidden="true" /></span>
                                <div>
                                    <dt class="text-xs text-muted-foreground">{{ fact.label }}</dt>
                                    <dd class="mt-0.5 text-sm font-medium">{{ fact.value }}</dd>
                                </div>
                            </div>
                        </dl>
                        <div v-if="!event.is_past && !['cancelled', 'completed', 'archived'].includes(event.status)" class="grid gap-2">
                            <a v-if="event.registration_url" :href="event.registration_url" :class="buttonVariants({ size: 'lg' })" target="_blank" rel="noopener noreferrer">
                                {{ t('register') }} <ExternalLink aria-hidden="true" /><span class="sr-only">{{ t('newTab') }}</span>
                            </a>
                            <a :href="icsHref" :download="`${event.slug}.ics`" :class="buttonVariants({ variant: 'outline' })">
                                <CalendarPlus aria-hidden="true" /> {{ t('addToCalendar') }}
                            </a>
                        </div>
                    </div>
                    <ShareButtons :title="event.title" />
                </aside>
            </div>
        </section>

        <section v-if="otherEvents.length" class="bg-muted py-16">
            <div class="mx-auto w-full max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-semibold tracking-tight">{{ t('moreEvents') }}</h2>
                    <Link :href="path('events')" :class="buttonVariants({ variant: 'outline' })">{{ t('seeAll') }}</Link>
                </div>
                <div class="grid gap-4"><EventCard v-for="item in otherEvents" :key="item.id" :event="item" /></div>
            </div>
        </section>
    </PublicLayout>
</template>
