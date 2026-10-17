<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, CalendarDays, CalendarPlus, FilePlus2, FolderKanban, LibraryBig, Mail, Megaphone, Newspaper } from '@lucide/vue';
import { computed } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import StatusBadge from '@/Components/Admin/Ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate, formatEventRange } from '@/lib/i18n';
import { buttonVariants } from '@/lib/variants';

const props = defineProps({
    stats: { type: Object, required: true },
    recentPosts: { type: Array, default: () => [] },
    upcomingEvents: { type: Array, default: () => [] },
    liveAnnouncements: { type: Array, default: () => [] },
});

const firstName = computed(() => (usePage().props.auth?.user?.name ?? '').split(' ')[0]);

const cards = computed(() => [
    { label: 'Actualités publiées', value: props.stats.posts.published, detail: `${props.stats.posts.drafts} brouillon${props.stats.posts.drafts > 1 ? 's' : ''}`, href: '/admin/posts', icon: Newspaper },
    { label: 'Événements à venir', value: props.stats.events.upcoming, detail: `${props.stats.events.drafts} brouillon${props.stats.events.drafts > 1 ? 's' : ''}`, href: '/admin/events', icon: CalendarDays },
    { label: 'Revues actives', value: props.stats.journals.active, detail: `${props.stats.journals.total} au catalogue`, href: '/admin/journals', icon: LibraryBig },
    { label: 'Annonces en ligne', value: props.stats.announcements.live, detail: `${props.stats.announcements.total} au total`, href: '/admin/announcements', icon: Megaphone },
    { label: 'Projets publiés', value: props.stats.projects?.published ?? 0, detail: `${props.stats.projects?.ongoing ?? 0} en cours`, href: '/admin/projects', icon: FolderKanban },
    { label: 'Abonnés newsletter', value: props.stats.newsletter?.active ?? 0, detail: `${props.stats.newsletter?.scheduled ?? 0} envoi${(props.stats.newsletter?.scheduled ?? 0) > 1 ? 's' : ''} programmé${(props.stats.newsletter?.scheduled ?? 0) > 1 ? 's' : ''}`, href: '/admin/newsletter', icon: Mail },
]);
</script>

<template>
    <AdminLayout title="Tableau de bord">
        <AdminPageHeader :title="firstName ? `Bonjour ${firstName}` : 'Tableau de bord'" description="Vue d’ensemble des contenus du portail et raccourcis vers les tâches courantes.">
            <template #actions>
                <Link href="/admin/events/create" :class="buttonVariants({ variant: 'outline' })"><CalendarPlus aria-hidden="true" />Événement</Link>
                <Link href="/admin/posts/create" :class="buttonVariants()"><FilePlus2 aria-hidden="true" />Nouvelle actualité</Link>
            </template>
        </AdminPageHeader>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Link v-for="card in cards" :key="card.label" :href="card.href" class="grid gap-1 rounded-xl border bg-card p-5 transition-colors outline-none hover:border-eden-300 focus-visible:ring-[3px] focus-visible:ring-ring/50 [&_small]:text-xs [&_small]:text-muted-foreground [&_strong]:text-3xl [&_strong]:font-semibold [&_strong]:tracking-tight">
                <div class="flex items-center justify-between text-sm font-medium text-muted-foreground [&_svg]:size-4 [&_svg]:text-eden-600">
                    <span>{{ card.label }}</span>
                    <component :is="card.icon" aria-hidden="true" />
                </div>
                <strong class="tabular-nums">{{ card.value }}</strong>
                <small>{{ card.detail }}</small>
            </Link>
        </div>

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
            <section class="rounded-xl border bg-card text-card-foreground overflow-hidden p-0">
                <header class="flex justify-between gap-3 m-0 items-center border-b px-5 py-4">
                    <div>
                        <h2 class="text-base font-semibold">Dernières actualités modifiées</h2>
                        <p class="text-sm text-muted-foreground">Reprenez un brouillon ou vérifiez une publication.</p>
                    </div>
                    <Link href="/admin/posts" :class="buttonVariants({ variant: 'ghost', size: 'sm' })">Tout voir<ArrowRight aria-hidden="true" /></Link>
                </header>
                <ul v-if="recentPosts.length" class="divide-y">
                    <li v-for="post in recentPosts" :key="post.id">
                        <Link :href="`/admin/posts/${post.id}/edit`" class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-muted">
                            <span class="grid min-w-0 gap-0.5 [&_small]:text-xs [&_small]:text-muted-foreground [&_strong]:truncate [&_strong]:text-sm [&_strong]:font-medium">
                                <strong>{{ post.title }}</strong>
                                <small>{{ post.category ?? 'Sans catégorie' }} · modifié le {{ formatDate(post.updated_at, 'fr', { day: 'numeric', month: 'short' }) }}</small>
                            </span>
                            <StatusBadge :status="post.is_scheduled ? 'scheduled' : post.status" />
                        </Link>
                    </li>
                </ul>
                <div v-else class="p-4">
                    <EmptyState :icon="Newspaper" title="Aucune actualité" description="Rédigez la première actualité du portail.">
                        <Link href="/admin/posts/create" :class="buttonVariants({ variant: 'outline', size: 'sm' })">Rédiger</Link>
                    </EmptyState>
                </div>
            </section>

            <div class="grid gap-4">
                <section class="rounded-xl border bg-card text-card-foreground overflow-hidden p-0">
                    <header class="flex justify-between gap-3 m-0 items-center border-b px-5 py-4">
                        <h2 class="text-base font-semibold">Prochains événements</h2>
                        <Link href="/admin/events" :class="buttonVariants({ variant: 'ghost', size: 'sm' })">Tout voir<ArrowRight aria-hidden="true" /></Link>
                    </header>
                    <ul v-if="upcomingEvents.length" class="divide-y">
                        <li v-for="event in upcomingEvents" :key="event.id">
                            <Link :href="`/admin/events/${event.id}/edit`" class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-muted">
                                <span class="grid min-w-0 gap-0.5 [&_small]:text-xs [&_small]:text-muted-foreground [&_strong]:truncate [&_strong]:text-sm [&_strong]:font-medium">
                                    <strong>{{ event.title }}</strong>
                                    <small>{{ formatEventRange(event, 'fr') }}</small>
                                </span>
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="px-5 py-4 text-sm text-muted-foreground">Aucun événement à venir.</p>
                </section>

                <section class="rounded-xl border bg-card text-card-foreground overflow-hidden p-0">
                    <header class="flex justify-between gap-3 m-0 items-center border-b px-5 py-4">
                        <h2 class="text-base font-semibold">Annonces en ligne</h2>
                        <Link href="/admin/announcements" :class="buttonVariants({ variant: 'ghost', size: 'sm' })">Gérer<ArrowRight aria-hidden="true" /></Link>
                    </header>
                    <ul v-if="liveAnnouncements.length" class="divide-y">
                        <li v-for="announcement in liveAnnouncements" :key="announcement.id">
                            <Link :href="`/admin/announcements/${announcement.id}/edit`" class="flex items-center justify-between gap-3 px-5 py-3 transition-colors hover:bg-muted">
                                <span class="grid min-w-0 gap-0.5 [&_small]:text-xs [&_small]:text-muted-foreground [&_strong]:truncate [&_strong]:text-sm [&_strong]:font-medium">
                                    <strong>{{ announcement.title }}</strong>
                                    <small>{{ announcement.display === 'dialog' ? 'Fenêtre' : 'Bandeau' }}</small>
                                </span>
                                <StatusBadge status="live" />
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="px-5 py-4 text-sm text-muted-foreground">Aucune annonce diffusée actuellement.</p>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
