<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, CircleCheck, ExternalLink, FilePen, FolderKanban, FolderPlus, Pencil, SearchX, Star, StarOff, Trash2 } from '@lucide/vue';
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
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass } from '@/lib/variants';

const props = defineProps({
    projects: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { filters, loading, reset, isFiltered } = useListFilters('/admin/projects', {
    q: props.filters.q ?? '',
    status: props.filters.status || null,
    progress: props.filters.progress || null,
});

const statusOptions = [{ value: null, label: 'Tous les statuts' }, { value: 'published', label: 'Publiés' }, { value: 'draft', label: 'Brouillons' }];
const progressOptions = [{ value: null, label: 'Tout avancement' }, { value: 'upcoming', label: 'Démarrage' }, { value: 'ongoing', label: 'En cours' }, { value: 'completed', label: 'Terminés' }];
const progressLabels = { upcoming: ['Démarrage', 'warning'], ongoing: ['En cours', 'success'], completed: ['Terminé', 'secondary'] };
const pendingDelete = ref(null);
const deleting = ref(false);

const actions = (project) => [
    { label: 'Modifier', icon: Pencil, href: `/admin/projects/${project.id}/edit` },
    ...(project.has_page ? [{ label: 'Voir la page', icon: ExternalLink, href: `/projets/${project.slug}`, external: true }] : []),
    { label: 'Supprimer', icon: Trash2, danger: true, separator: true, onSelect: () => { pendingDelete.value = project; } },
];

const move = (index, delta) => {
    const ids = props.projects.data.map((project) => project.id);
    const [id] = ids.splice(index, 1);
    ids.splice(index + delta, 0, id);
    router.patch('/admin/projects/reorder', { ids }, { preserveScroll: true });
};

const confirmDelete = () => router.delete(`/admin/projects/${pendingDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; pendingDelete.value = null; },
});

const tableClass = 'w-full border-collapse text-sm max-md:block max-md:[&_tbody]:grid max-md:[&_tbody]:gap-3 max-md:[&_td]:flex max-md:[&_td]:items-center max-md:[&_td]:justify-between max-md:[&_td]:gap-3 max-md:[&_td]:border-0 max-md:[&_td]:px-0 max-md:[&_td]:py-1.5 max-md:[&_td[data-label]]:before:text-xs max-md:[&_td[data-label]]:before:text-muted-foreground max-md:[&_td[data-label]]:before:content-[attr(data-label)] max-md:[&_thead]:hidden max-md:[&_tr]:block max-md:[&_tr]:rounded-xl max-md:[&_tr]:border max-md:[&_tr]:bg-card max-md:[&_tr]:px-4 max-md:[&_tr]:py-3 [&_tbody_tr:hover]:bg-muted/50 [&_tbody_tr:last-child_td]:border-b-0 [&_td]:border-b [&_td]:px-4 [&_td]:py-3 [&_td]:align-middle [&_th]:h-10 [&_th]:border-b [&_th]:bg-muted [&_th]:px-4 [&_th]:text-left [&_th]:font-medium [&_th]:whitespace-nowrap [&_th]:text-muted-foreground';

// Sélection multiple et actions groupées
const bulk = useBulkSelection(() => props.projects.data);
const bulkActions = [
    { action: 'publish', label: 'Publier', icon: CircleCheck },
    { action: 'draft', label: 'Brouillon', icon: FilePen },
    { action: 'feature', label: 'Mettre en avant', icon: Star },
    { action: 'unfeature', label: 'Retirer la mise en avant', icon: StarOff },
    { action: 'delete', label: 'Supprimer', icon: Trash2, danger: true, confirm: { title: 'Supprimer les projets sélectionnés ?', description: (n) => `${n} projet(s) seront placés dans la corbeille, page détaillée comprise.` } },
];
</script>

<template>
    <AdminLayout title="Projets">
        <AdminPageHeader title="Projets" description="Projets de recherche et de coopération (RAVENALA compris), affichés sur l’accueil, la page Recherche et /projets. Un projet avec contenu détaillé obtient sa propre page.">
            <template #actions>
                <Link href="/admin/projects/create" :class="buttonVariants()"><FolderPlus aria-hidden="true" />Nouveau projet</Link>
            </template>
        </AdminPageHeader>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <input v-model="filters.q" type="search" :class="cn(inputClass, 'basis-full sm:max-w-sm sm:flex-1 sm:basis-64')" placeholder="Titre, acronyme ou financeur…" aria-label="Rechercher un projet">
            <div class="flex-1 sm:w-44 sm:flex-none"><Select v-model="filters.status" :options="statusOptions" aria-label="Filtrer par statut" size="sm" /></div>
            <div class="flex-1 sm:w-44 sm:flex-none"><Select v-model="filters.progress" :options="progressOptions" aria-label="Filtrer par avancement" size="sm" /></div>
            <span v-if="loading" class="inline-block size-4 animate-spin rounded-full border-2 border-current border-t-transparent text-muted-foreground" aria-label="Chargement"></span>
        </div>

        <template v-if="projects.data.length">
            <div class="overflow-hidden rounded-xl border bg-card max-md:overflow-visible max-md:border-0 max-md:bg-transparent">
                <table :class="tableClass">
                    <thead>
                        <tr>
                            <th scope="col" class="w-px !pr-0"><BulkCheckbox :checked="bulk.allSelected" :indeterminate="bulk.someSelected" aria-label="Tout sélectionner sur cette page" @change="bulk.toggleAll()" /></th>
                            <th scope="col">Projet</th>
                            <th scope="col">Avancement</th>
                            <th scope="col">Période</th>
                            <th scope="col">Financement</th>
                            <th scope="col">Statut</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(project, index) in projects.data" :key="project.id" :class="['relative', bulk.isSelected(project.id) && 'bg-eden-50/60 dark:bg-eden-950/40']">
                            <td class="w-px !pr-0 max-md:absolute max-md:top-4 max-md:left-4 max-md:!p-0"><BulkCheckbox :checked="bulk.isSelected(project.id)" :aria-label="`Sélectionner ${project.title}`" @change="bulk.toggle(project.id)" /></td>
                            <td class="max-md:mb-1 max-md:!block max-md:border-b max-md:pb-2 max-md:pl-7">
                                <div class="flex min-w-64 items-center gap-3 max-md:min-w-0">
                                    <span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-md border bg-white">
                                        <img v-if="project.logo || project.cover_image" :src="project.logo || project.cover_image" alt="" :class="project.logo ? 'size-full object-contain p-1' : 'size-full object-cover'">
                                        <FolderKanban v-else class="size-4 text-muted-foreground" aria-hidden="true" />
                                    </span>
                                    <span class="grid gap-0.5">
                                        <Link :href="`/admin/projects/${project.id}/edit`" class="font-medium hover:underline">{{ project.title }}</Link>
                                        <span class="flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground">
                                            <span v-if="project.team">{{ project.team }}</span>
                                            <Badge v-if="project.is_featured" variant="default">Mis en avant</Badge>
                                            <span v-if="project.has_page">Page détaillée</span>
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td data-label="Avancement"><Badge :variant="progressLabels[project.progress]?.[1]">{{ progressLabels[project.progress]?.[0] }}</Badge></td>
                            <td class="whitespace-nowrap text-muted-foreground tabular-nums" data-label="Période">{{ project.period ?? '-' }}</td>
                            <td class="text-muted-foreground" data-label="Financement">{{ project.funder ?? '-' }}</td>
                            <td data-label="Statut"><StatusBadge :status="project.status" /></td>
                            <td class="w-px text-right whitespace-nowrap max-md:!justify-end">
                                <span v-if="!isFiltered()" class="inline-flex">
                                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" :disabled="index === 0" :aria-label="`Monter ${project.title}`" @click="move(index, -1)"><ChevronUp aria-hidden="true" /></button>
                                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" :disabled="index === projects.data.length - 1" :aria-label="`Descendre ${project.title}`" @click="move(index, 1)"><ChevronDown aria-hidden="true" /></button>
                                </span>
                                <ActionMenu :items="actions(project)" :label="`Actions pour ${project.title}`" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :meta="projects.meta" :links="projects.links" preserve-scroll :labels="{ previous: 'Précédent', next: 'Suivant', info: `${projects.meta.from} à ${projects.meta.to} sur ${projects.meta.total}` }" />
        </template>
        <EmptyState v-else-if="isFiltered()" :icon="SearchX" title="Aucun résultat" description="Aucun projet ne correspond à ces filtres.">
            <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="reset">Effacer les filtres</button>
        </EmptyState>
        <EmptyState v-else :icon="FolderKanban" title="Aucun projet" description="Ajoutez les projets de recherche et de coopération de l’EDEN.">
            <Link href="/admin/projects/create" :class="buttonVariants({ size: 'sm' })"><FolderPlus aria-hidden="true" />Ajouter un projet</Link>
        </EmptyState>

        <ConfirmDialog
            :open="Boolean(pendingDelete)"
            title="Supprimer ce projet ?"
            :description="pendingDelete ? `« ${pendingDelete.title} » sera retiré du site. Pour le masquer sans le perdre, repassez-le en brouillon.` : ''"
            :loading="deleting"
            @update:open="(value) => { if (!value) pendingDelete = null; }"
            @confirm="confirmDelete"
        />
        <BulkActionBar :selection="bulk" url="/admin/projects/bulk" :actions="bulkActions" :noun="['projet', 'projets']" />
    </AdminLayout>
</template>
