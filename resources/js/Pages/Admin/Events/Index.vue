<script setup>
import { Link, router } from '@inertiajs/vue3';
import { Archive, Ban, CalendarDays, CalendarPlus, CircleCheck, ExternalLink, FilePen, Pencil, SearchX, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import BulkActionBar from '@/Components/Admin/Ui/BulkActionBar.vue';
import BulkCheckbox from '@/Components/Admin/Ui/BulkCheckbox.vue';
import StatusBadge from '@/Components/Admin/Ui/StatusBadge.vue';
import ActionMenu from '@/Components/ui/ActionMenu.vue';
import Badge from '@/Components/ui/Badge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import Select from '@/Components/ui/Select.vue';
import { useBulkSelection } from '@/Composables/useBulkSelection';
import { useListFilters } from '@/Composables/useListFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatEventRange } from '@/lib/i18n';
import { buttonVariants, inputClass } from '@/lib/variants';

const props = defineProps({
    events: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { filters, loading, reset, isFiltered } = useListFilters('/admin/events', {
    q: props.filters.q ?? '',
    status: props.filters.status || null,
    period: props.filters.period || null,
});

const statusOptions = [{ value: null, label: 'Tous les statuts' }, { value: 'published', label: 'Publiés' }, { value: 'completed', label: 'Terminés' }, { value: 'archived', label: 'Archivés' }, { value: 'draft', label: 'Brouillons' }, { value: 'cancelled', label: 'Annulés' }];
const periodOptions = [{ value: null, label: 'Toutes les dates' }, { value: 'upcoming', label: 'À venir' }, { value: 'past', label: 'Passés' }];
const formats = { in_person: 'Présentiel', online: 'En ligne', hybrid: 'Hybride' };
const pendingDelete = ref(null);
const deleting = ref(false);

const actions = (event) => [
    { label: 'Modifier', icon: Pencil, href: `/admin/events/${event.id}/edit` },
    { label: 'Voir sur le site', icon: ExternalLink, href: `/evenements/${event.slug}`, external: true },
    { label: 'Supprimer', icon: Trash2, danger: true, separator: true, onSelect: () => { pendingDelete.value = event; } },
];

const confirmDelete = () => router.delete(`/admin/events/${pendingDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; pendingDelete.value = null; },
});

// Sélection multiple et actions groupées
const bulk = useBulkSelection(() => props.events.data);
const bulkActions = [
    { action: 'publish', label: 'Publier', icon: CircleCheck },
    { action: 'draft', label: 'Brouillon', icon: FilePen },
    { action: 'complete', label: 'Marquer terminés', icon: CircleCheck },
    { action: 'archive', label: 'Archiver', icon: Archive },
    { action: 'cancel', label: 'Marquer annulés', icon: Ban, confirm: { title: 'Annuler les événements sélectionnés ?', description: (n) => `${n} événement(s) resteront visibles avec la mention « Annulé ».`, label: 'Marquer annulés' }, danger: false },
    { action: 'delete', label: 'Supprimer', icon: Trash2, danger: true, confirm: { title: 'Supprimer les événements sélectionnés ?', description: (n) => `${n} événement(s) seront placés dans la corbeille. Préférez « Marquer annulés » pour garder une trace.` } },
];
</script>

<template>
    <AdminLayout title="Événements">
        <AdminPageHeader title="Événements" description="Séminaires, soutenances et rencontres affichés sur /evenements et sur l’accueil.">
            <template #actions>
                <Link href="/admin/events/create" :class="buttonVariants()"><CalendarPlus aria-hidden="true" />Nouvel événement</Link>
            </template>
        </AdminPageHeader>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative [&>input]:pl-9 [&>svg]:pointer-events-none [&>svg]:absolute [&>svg]:top-1/2 [&>svg]:left-3 [&>svg]:size-4 [&>svg]:-translate-y-1/2 [&>svg]:text-muted-foreground basis-full sm:max-w-sm sm:flex-1 sm:basis-64">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
                <input v-model="filters.q" type="search" :class="inputClass" placeholder="Titre ou lieu…" aria-label="Rechercher un événement">
            </div>
            <div class="flex-1 sm:w-48 sm:flex-none"><Select v-model="filters.period" :options="periodOptions" aria-label="Filtrer par date" size="sm" /></div>
            <div class="flex-1 sm:w-48 sm:flex-none"><Select v-model="filters.status" :options="statusOptions" aria-label="Filtrer par statut" size="sm" /></div>
            <span v-if="loading" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent text-muted-foreground" aria-label="Chargement"></span>
        </div>

        <template v-if="events.data.length">
            <div class="overflow-hidden rounded-xl border bg-card max-md:overflow-visible max-md:border-0 max-md:bg-transparent">
                <table class="w-full border-collapse text-sm max-md:block max-md:[&_tbody]:grid max-md:[&_tbody]:gap-3 max-md:[&_td]:flex max-md:[&_td]:items-center max-md:[&_td]:justify-between max-md:[&_td]:gap-3 max-md:[&_td]:border-0 max-md:[&_td]:px-0 max-md:[&_td]:py-1.5 max-md:[&_td[data-label]]:before:text-xs max-md:[&_td[data-label]]:before:text-muted-foreground max-md:[&_td[data-label]]:before:content-[attr(data-label)] max-md:[&_thead]:hidden max-md:[&_tr]:block max-md:[&_tr]:rounded-xl max-md:[&_tr]:border max-md:[&_tr]:bg-card max-md:[&_tr]:px-4 max-md:[&_tr]:py-3 [&_tbody_tr:hover]:bg-muted/50 [&_tbody_tr:last-child_td]:border-b-0 [&_td]:border-b [&_td]:px-4 [&_td]:py-3 [&_td]:align-middle [&_th]:h-10 [&_th]:border-b [&_th]:bg-muted [&_th]:px-4 [&_th]:text-left [&_th]:font-medium [&_th]:whitespace-nowrap [&_th]:text-muted-foreground">
                    <thead>
                        <tr>
                            <th scope="col" class="w-px !pr-0"><BulkCheckbox :checked="bulk.allSelected" :indeterminate="bulk.someSelected" aria-label="Tout sélectionner sur cette page" @change="bulk.toggleAll()" /></th>
                            <th scope="col">Événement</th>
                            <th scope="col">Date</th>
                            <th scope="col">Format</th>
                            <th scope="col">Statut</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="event in events.data" :key="event.id" :class="['relative', bulk.isSelected(event.id) && 'bg-eden-50/60 dark:bg-eden-950/40']">
                            <td class="w-px !pr-0 max-md:absolute max-md:top-4 max-md:left-4 max-md:!p-0"><BulkCheckbox :checked="bulk.isSelected(event.id)" :aria-label="`Sélectionner ${event.title}`" @change="bulk.toggle(event.id)" /></td>
                            <td class="max-md:mb-1 max-md:!block max-md:border-b max-md:pb-2 max-md:pl-7">
                                <div class="flex min-w-56 items-center gap-3 max-md:min-w-0 [&_a]:font-medium [&_a:hover]:underline">
                                    <span class="h-8 w-11 shrink-0 overflow-hidden rounded-md border bg-secondary [&_img]:size-full [&_img]:object-cover"><img v-if="event.cover_image" :src="event.cover_image" alt=""></span>
                                    <span>
                                        <Link :href="`/admin/events/${event.id}/edit`">{{ event.title }}</Link>
                                        <span class="mt-0.5 flex flex-wrap items-center gap-1 text-xs text-muted-foreground">
                                            {{ event.location || '-' }}
                                            <Badge v-if="event.is_featured" variant="default">Mis en avant</Badge>
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-muted-foreground" data-label="Date">
                                {{ formatEventRange(event, 'fr') }}
                                <Badge v-if="event.is_past" variant="outline">Passé</Badge>
                            </td>
                            <td class="whitespace-nowrap text-muted-foreground" data-label="Format">{{ formats[event.format] }}</td>
                            <td data-label="Statut"><StatusBadge :status="event.status" /></td>
                            <td class="w-px text-right whitespace-nowrap max-md:!justify-end"><ActionMenu :items="actions(event)" :label="`Actions pour ${event.title}`" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :meta="events.meta" :links="events.links" preserve-scroll :labels="{ previous: 'Précédent', next: 'Suivant', info: `${events.meta.from} à ${events.meta.to} sur ${events.meta.total}` }" />
        </template>
        <EmptyState v-else-if="isFiltered()" :icon="SearchX" title="Aucun résultat" description="Aucun événement ne correspond à ces filtres.">
            <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="reset">Effacer les filtres</button>
        </EmptyState>
        <EmptyState v-else :icon="CalendarDays" title="Aucun événement" description="Les événements publiés apparaissent sur la page Événements et, s’ils sont à venir, sur l’accueil.">
            <Link href="/admin/events/create" :class="buttonVariants({ size: 'sm' })"><CalendarPlus aria-hidden="true" />Créer un événement</Link>
        </EmptyState>

        <ConfirmDialog
            :open="Boolean(pendingDelete)"
            title="Supprimer cet événement ?"
            :description="pendingDelete ? `« ${pendingDelete.title} » sera placé dans la corbeille. Pour le garder visible, passez-le plutôt en « Annulé ».` : ''"
            :loading="deleting"
            @update:open="(value) => { if (!value) pendingDelete = null; }"
            @confirm="confirmDelete"
        />
        <BulkActionBar :selection="bulk" url="/admin/events/bulk" :actions="bulkActions" :noun="['événement', 'événements']" />
    </AdminLayout>
</template>
