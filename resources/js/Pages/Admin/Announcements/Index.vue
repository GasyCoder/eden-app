<script setup>
import { Link, router } from '@inertiajs/vue3';
import { Archive, CircleCheck, FilePen, Megaphone, MessageSquarePlus, Pencil, SearchX, Trash2 } from '@lucide/vue';
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
import { formatDate } from '@/lib/i18n';
import { buttonVariants, inputClass } from '@/lib/variants';

const props = defineProps({
    announcements: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { filters, loading, reset, isFiltered } = useListFilters('/admin/announcements', {
    q: props.filters.q ?? '',
    status: props.filters.status || null,
    type: props.filters.type || null,
});

const types = { info: 'Information', event: 'Événement', important: 'Important', maintenance: 'Maintenance', urgent: 'Urgence' };
const typeOptions = [{ value: null, label: 'Tous les types' }, ...Object.entries(types).map(([value, label]) => ({ value, label }))];
const statusOptions = [{ value: null, label: 'Tous les statuts' }, { value: 'published', label: 'Publiées' }, { value: 'draft', label: 'Brouillons' }, { value: 'archived', label: 'Archivées' }];
const priorities = ['Basse', 'Normale', 'Haute'];
const pendingDelete = ref(null);
const deleting = ref(false);
const period = (item) => {
    const date = (iso) => formatDate(iso, 'fr', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });

    if (!item.starts_at && !item.ends_at) return 'Sans limite';
    if (item.starts_at && item.ends_at) return `${date(item.starts_at)} → ${date(item.ends_at)}`;

    return item.starts_at ? `À partir du ${date(item.starts_at)}` : `Jusqu’au ${date(item.ends_at)}`;
};

const actions = (item) => [
    { label: 'Modifier', icon: Pencil, href: `/admin/announcements/${item.id}/edit` },
    { label: 'Supprimer', icon: Trash2, danger: true, separator: true, onSelect: () => { pendingDelete.value = item; } },
];

const confirmDelete = () => router.delete(`/admin/announcements/${pendingDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; pendingDelete.value = null; },
});

// Sélection multiple et actions groupées
const bulk = useBulkSelection(() => props.announcements.data);
const bulkActions = [
    { action: 'publish', label: 'Publier', icon: CircleCheck },
    { action: 'archive', label: 'Archiver', icon: Archive },
    { action: 'draft', label: 'Brouillon', icon: FilePen },
    { action: 'delete', label: 'Supprimer', icon: Trash2, danger: true, confirm: { title: 'Supprimer les annonces sélectionnées ?', description: (n) => `${n} annonce(s) seront définitivement supprimées. Préférez « Archiver » pour les conserver.` } },
];
</script>

<template>
    <AdminLayout title="Annonces">
        <AdminPageHeader title="Annonces" description="Messages affichés aux visiteurs : bandeau discret ou fenêtre selon l’importance.">
            <template #actions>
                <Link href="/admin/announcements/create" :class="buttonVariants()"><MessageSquarePlus aria-hidden="true" />Nouvelle annonce</Link>
            </template>
        </AdminPageHeader>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative [&>input]:pl-9 [&>svg]:pointer-events-none [&>svg]:absolute [&>svg]:top-1/2 [&>svg]:left-3 [&>svg]:size-4 [&>svg]:-translate-y-1/2 [&>svg]:text-muted-foreground basis-full sm:max-w-sm sm:flex-1 sm:basis-64">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
                <input v-model="filters.q" type="search" :class="inputClass" placeholder="Rechercher un titre…" aria-label="Rechercher une annonce">
            </div>
            <div class="flex-1 sm:w-48 sm:flex-none"><Select v-model="filters.type" :options="typeOptions" aria-label="Filtrer par type" size="sm" /></div>
            <div class="flex-1 sm:w-48 sm:flex-none"><Select v-model="filters.status" :options="statusOptions" aria-label="Filtrer par statut" size="sm" /></div>
            <span v-if="loading" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent text-muted-foreground" aria-label="Chargement"></span>
        </div>

        <template v-if="announcements.data.length">
            <div class="overflow-hidden rounded-xl border bg-card max-md:overflow-visible max-md:border-0 max-md:bg-transparent">
                <table class="w-full border-collapse text-sm max-md:block max-md:[&_tbody]:grid max-md:[&_tbody]:gap-3 max-md:[&_td]:flex max-md:[&_td]:items-center max-md:[&_td]:justify-between max-md:[&_td]:gap-3 max-md:[&_td]:border-0 max-md:[&_td]:px-0 max-md:[&_td]:py-1.5 max-md:[&_td[data-label]]:before:text-xs max-md:[&_td[data-label]]:before:text-muted-foreground max-md:[&_td[data-label]]:before:content-[attr(data-label)] max-md:[&_thead]:hidden max-md:[&_tr]:block max-md:[&_tr]:rounded-xl max-md:[&_tr]:border max-md:[&_tr]:bg-card max-md:[&_tr]:px-4 max-md:[&_tr]:py-3 [&_tbody_tr:hover]:bg-muted/50 [&_tbody_tr:last-child_td]:border-b-0 [&_td]:border-b [&_td]:px-4 [&_td]:py-3 [&_td]:align-middle [&_th]:h-10 [&_th]:border-b [&_th]:bg-muted [&_th]:px-4 [&_th]:text-left [&_th]:font-medium [&_th]:whitespace-nowrap [&_th]:text-muted-foreground">
                    <thead>
                        <tr>
                            <th scope="col" class="w-px !pr-0"><BulkCheckbox :checked="bulk.allSelected" :indeterminate="bulk.someSelected" aria-label="Tout sélectionner sur cette page" @change="bulk.toggleAll()" /></th>
                            <th scope="col">Annonce</th>
                            <th scope="col">Type</th>
                            <th scope="col">Diffusion</th>
                            <th scope="col">Priorité</th>
                            <th scope="col">État</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in announcements.data" :key="item.id" :class="['relative', bulk.isSelected(item.id) && 'bg-eden-50/60 dark:bg-eden-950/40']">
                            <td class="w-px !pr-0 max-md:absolute max-md:top-4 max-md:left-4 max-md:!p-0"><BulkCheckbox :checked="bulk.isSelected(item.id)" :aria-label="`Sélectionner ${item.title}`" @change="bulk.toggle(item.id)" /></td>
                            <td class="max-md:mb-1 max-md:!block max-md:border-b max-md:pb-2 max-md:pl-7">
                                <Link :href="`/admin/announcements/${item.id}/edit`" class="font-medium hover:underline">{{ item.title }}</Link>
                                <span class="mt-0.5 flex flex-wrap items-center gap-1 text-xs text-muted-foreground">{{ item.display === 'dialog' ? 'Fenêtre' : 'Bandeau' }}</span>
                            </td>
                            <td data-label="Type"><Badge :variant="item.type === 'urgent' ? 'danger' : item.type === 'maintenance' ? 'warning' : 'outline'">{{ types[item.type] }}</Badge></td>
                            <td class="whitespace-nowrap text-muted-foreground" data-label="Diffusion">{{ period(item) }}</td>
                            <td class="whitespace-nowrap text-muted-foreground" data-label="Priorité">{{ priorities[item.priority] }}</td>
                            <td data-label="État"><StatusBadge :status="item.state" /></td>
                            <td class="w-px text-right whitespace-nowrap max-md:!justify-end"><ActionMenu :items="actions(item)" :label="`Actions pour ${item.title}`" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :meta="announcements.meta" :links="announcements.links" preserve-scroll :labels="{ previous: 'Précédent', next: 'Suivant', info: `${announcements.meta.from} à ${announcements.meta.to} sur ${announcements.meta.total}` }" />
        </template>
        <EmptyState v-else-if="isFiltered()" :icon="SearchX" title="Aucun résultat" description="Aucune annonce ne correspond à ces filtres.">
            <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="reset">Effacer les filtres</button>
        </EmptyState>
        <EmptyState v-else :icon="Megaphone" title="Aucune annonce" description="Informez les visiteurs d’une maintenance, d’un événement ou d’une information urgente.">
            <Link href="/admin/announcements/create" :class="buttonVariants({ size: 'sm' })"><MessageSquarePlus aria-hidden="true" />Créer une annonce</Link>
        </EmptyState>

        <ConfirmDialog
            :open="Boolean(pendingDelete)"
            title="Supprimer cette annonce ?"
            :description="pendingDelete ? `« ${pendingDelete.title} » sera définitivement supprimée. Pour l’arrêter en la conservant, archivez-la.` : ''"
            :loading="deleting"
            @update:open="(value) => { if (!value) pendingDelete = null; }"
            @confirm="confirmDelete"
        />
        <BulkActionBar :selection="bulk" url="/admin/announcements/bulk" :actions="bulkActions" :noun="['annonce', 'annonces']" />
    </AdminLayout>
</template>
