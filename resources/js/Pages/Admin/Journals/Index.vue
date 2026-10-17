<script setup>
import { Link, router } from '@inertiajs/vue3';
import { Archive, BookPlus, CircleCheck, ExternalLink, FilePen, Layers, LibraryBig, Pencil, SearchX, Star, StarOff, Trash2 } from '@lucide/vue';
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
import { buttonVariants, inputClass } from '@/lib/variants';

const props = defineProps({
    journals: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { filters, loading, reset, isFiltered } = useListFilters('/admin/journals', {
    q: props.filters.q ?? '',
    status: props.filters.status || null,
});

const statusOptions = [{ value: null, label: 'Tous les statuts' }, { value: 'active', label: 'Actives' }, { value: 'archived', label: 'Archivées' }, { value: 'draft', label: 'Brouillons' }];
const pendingDelete = ref(null);
const deleting = ref(false);

const actions = (journal) => [
    { label: 'Modifier', icon: Pencil, href: `/admin/journals/${journal.id}/edit` },
    { label: 'Numéros', icon: Layers, href: `/admin/journals/${journal.id}/issues` },
    { label: 'Voir sur le site', icon: ExternalLink, href: `/revues/${journal.slug}`, external: true },
    { label: 'Supprimer', icon: Trash2, danger: true, separator: true, onSelect: () => { pendingDelete.value = journal; } },
];

const confirmDelete = () => router.delete(`/admin/journals/${pendingDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; pendingDelete.value = null; },
});

// Sélection multiple et actions groupées
const bulk = useBulkSelection(() => props.journals.data);
const bulkActions = [
    { action: 'activate', label: 'Activer', icon: CircleCheck },
    { action: 'archive', label: 'Archiver', icon: Archive },
    { action: 'draft', label: 'Brouillon', icon: FilePen },
    { action: 'feature', label: 'Mettre en avant', icon: Star },
    { action: 'unfeature', label: 'Retirer de l’accueil', icon: StarOff },
    { action: 'delete', label: 'Supprimer', icon: Trash2, danger: true, confirm: { title: 'Supprimer les revues sélectionnées ?', description: (n) => `${n} revue(s) seront supprimées avec tous leurs numéros. Préférez « Archiver » pour les garder visibles.` } },
];
</script>

<template>
    <AdminLayout title="Revues">
        <AdminPageHeader title="Revues scientifiques" description="Catalogue des revues présentées sur /revues, avec leurs numéros (journal trimestriel MaToY, PDF, sommaire). Les revues « mises en avant » apparaissent sur l’accueil.">
            <template #actions>
                <Link href="/admin/journals/create" :class="buttonVariants()"><BookPlus aria-hidden="true" />Nouvelle revue</Link>
            </template>
        </AdminPageHeader>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative [&>input]:pl-9 [&>svg]:pointer-events-none [&>svg]:absolute [&>svg]:top-1/2 [&>svg]:left-3 [&>svg]:size-4 [&>svg]:-translate-y-1/2 [&>svg]:text-muted-foreground basis-full sm:max-w-sm sm:flex-1 sm:basis-64">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
                <input v-model="filters.q" type="search" :class="inputClass" placeholder="Titre, ISSN ou domaine…" aria-label="Rechercher une revue">
            </div>
            <div class="flex-1 sm:w-48 sm:flex-none"><Select v-model="filters.status" :options="statusOptions" aria-label="Filtrer par statut" size="sm" /></div>
            <span v-if="loading" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent text-muted-foreground" aria-label="Chargement"></span>
        </div>

        <template v-if="journals.data.length">
            <div class="overflow-hidden rounded-xl border bg-card max-md:overflow-visible max-md:border-0 max-md:bg-transparent">
                <table class="w-full border-collapse text-sm max-md:block max-md:[&_tbody]:grid max-md:[&_tbody]:gap-3 max-md:[&_td]:flex max-md:[&_td]:items-center max-md:[&_td]:justify-between max-md:[&_td]:gap-3 max-md:[&_td]:border-0 max-md:[&_td]:px-0 max-md:[&_td]:py-1.5 max-md:[&_td[data-label]]:before:text-xs max-md:[&_td[data-label]]:before:text-muted-foreground max-md:[&_td[data-label]]:before:content-[attr(data-label)] max-md:[&_thead]:hidden max-md:[&_tr]:block max-md:[&_tr]:rounded-xl max-md:[&_tr]:border max-md:[&_tr]:bg-card max-md:[&_tr]:px-4 max-md:[&_tr]:py-3 [&_tbody_tr:hover]:bg-muted/50 [&_tbody_tr:last-child_td]:border-b-0 [&_td]:border-b [&_td]:px-4 [&_td]:py-3 [&_td]:align-middle [&_th]:h-10 [&_th]:border-b [&_th]:bg-muted [&_th]:px-4 [&_th]:text-left [&_th]:font-medium [&_th]:whitespace-nowrap [&_th]:text-muted-foreground">
                    <thead>
                        <tr>
                            <th scope="col" class="w-px !pr-0"><BulkCheckbox :checked="bulk.allSelected" :indeterminate="bulk.someSelected" aria-label="Tout sélectionner sur cette page" @change="bulk.toggleAll()" /></th>
                            <th scope="col">Revue</th>
                            <th scope="col">Domaine</th>
                            <th scope="col">ISSN</th>
                            <th scope="col">Numéros</th>
                            <th scope="col">Statut</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="journal in journals.data" :key="journal.id" :class="['relative', bulk.isSelected(journal.id) && 'bg-eden-50/60 dark:bg-eden-950/40']">
                            <td class="w-px !pr-0 max-md:absolute max-md:top-4 max-md:left-4 max-md:!p-0"><BulkCheckbox :checked="bulk.isSelected(journal.id)" :aria-label="`Sélectionner ${journal.title}`" @change="bulk.toggle(journal.id)" /></td>
                            <td class="max-md:mb-1 max-md:!block max-md:border-b max-md:pb-2 max-md:pl-7">
                                <div class="flex min-w-56 items-center gap-3 max-md:min-w-0 [&_a]:font-medium [&_a:hover]:underline">
                                    <span class="shrink-0 overflow-hidden rounded-md border bg-secondary [&_img]:size-full [&_img]:object-cover h-10 w-8"><img v-if="journal.cover_image" :src="journal.cover_image" alt=""></span>
                                    <span>
                                        <Link :href="`/admin/journals/${journal.id}/edit`">{{ journal.title }}</Link>
                                        <span class="mt-0.5 flex flex-wrap items-center gap-1 text-xs text-muted-foreground"><Badge v-if="journal.is_featured" variant="default">Mise en avant</Badge></span>
                                    </span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-muted-foreground" data-label="Domaine">{{ journal.domain ?? '-' }}</td>
                            <td class="whitespace-nowrap text-muted-foreground tabular-nums" data-label="ISSN">{{ journal.issn ?? '-' }}</td>
                            <td class="whitespace-nowrap" data-label="Numéros">
                                <Link :href="`/admin/journals/${journal.id}/issues`" class="font-medium text-eden-700 hover:underline hover:underline-offset-4 dark:text-eden-300">{{ journal.issues_count }} {{ journal.issues_count > 1 ? 'numéros' : 'numéro' }}</Link>
                            </td>
                            <td data-label="Statut"><StatusBadge :status="journal.status" /></td>
                            <td class="w-px text-right whitespace-nowrap max-md:!justify-end"><ActionMenu :items="actions(journal)" :label="`Actions pour ${journal.title}`" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :meta="journals.meta" :links="journals.links" preserve-scroll :labels="{ previous: 'Précédent', next: 'Suivant', info: `${journals.meta.from} à ${journals.meta.to} sur ${journals.meta.total}` }" />
        </template>
        <EmptyState v-else-if="isFiltered()" :icon="SearchX" title="Aucun résultat" description="Aucune revue ne correspond à ces filtres.">
            <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="reset">Effacer les filtres</button>
        </EmptyState>
        <EmptyState v-else :icon="LibraryBig" title="Aucune revue" description="Ajoutez les revues éditées ou soutenues par l’EDEN.">
            <Link href="/admin/journals/create" :class="buttonVariants({ size: 'sm' })"><BookPlus aria-hidden="true" />Ajouter une revue</Link>
        </EmptyState>

        <ConfirmDialog
            :open="Boolean(pendingDelete)"
            title="Supprimer cette revue ?"
            :description="pendingDelete ? `« ${pendingDelete.title} » sera retirée du catalogue. Pour la garder visible sans activité, archivez-la plutôt.` : ''"
            :loading="deleting"
            @update:open="(value) => { if (!value) pendingDelete = null; }"
            @confirm="confirmDelete"
        />
        <BulkActionBar :selection="bulk" url="/admin/journals/bulk" :actions="bulkActions" :noun="['revue', 'revues']" />
    </AdminLayout>
</template>
