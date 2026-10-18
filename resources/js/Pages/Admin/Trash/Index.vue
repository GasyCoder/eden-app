<script setup>
import { router } from '@inertiajs/vue3';
import { RotateCcw, Search, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import Select from '@/Components/ui/Select.vue';
import { useListFilters } from '@/Composables/useListFilters';
import { useToasts } from '@/Composables/useToasts';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate } from '@/lib/i18n';
import { inputClass } from '@/lib/variants';

const props = defineProps({ items: { type: Object, required: true }, types: { type: Array, default: () => [] }, filters: { type: Object, default: () => ({}) } });
const { filters, loading, reset, isFiltered } = useListFilters('/admin/trash', { q: props.filters.q ?? '', type: props.filters.type || null });
const typeOptions = computed(() => [{ value: null, label: 'Tous les modules' }, ...props.types]);
const pending = ref(null);
const working = ref(null);
const confirmOpen = computed({ get: () => Boolean(pending.value), set: (open) => { if (!open) pending.value = null; } });
const { push } = useToasts();
const key = (item) => `${item.type}:${item.id}`;
const options = (item) => ({
    preserveScroll: true,
    onStart: () => { working.value = key(item); },
    onFinish: () => { working.value = null; },
    onError: (errors) => push(Object.values(errors).flat()[0] || 'L’action n’a pas pu être effectuée.', 'error'),
    onHttpException: () => { push('L’action n’a pas pu être effectuée. Rechargez la page et réessayez.', 'error'); return false; },
    onNetworkError: () => { push('Impossible de joindre le serveur. Réessayez.', 'error'); return false; },
});
const restore = (item) => router.post(`/admin/trash/${item.type}/${item.id}/restore`, {}, options(item));
const destroy = () => {
    const item = pending.value;
    if (!item || working.value) return;
    router.post(`/admin/trash/${item.type}/${item.id}`, { _method: 'DELETE' }, options(item));
};
</script>

<template>
    <AdminLayout title="Corbeille">
        <AdminPageHeader title="Corbeille" description="Retrouvez les éléments supprimés pour les restaurer ou les supprimer définitivement.">
            <template #actions><span class="rounded-md border px-3 py-2 text-sm text-muted-foreground">{{ items.meta.total }} élément(s)</span></template>
        </AdminPageHeader>

        <div class="mb-5 flex flex-col gap-3 sm:flex-row">
            <div class="relative sm:max-w-sm sm:flex-1">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                <input v-model="filters.q" type="search" :class="[inputClass, 'pl-9']" placeholder="Rechercher dans la corbeille…" aria-label="Rechercher dans la corbeille">
            </div>
            <Select v-model="filters.type" :options="typeOptions" class="sm:w-56" aria-label="Filtrer la corbeille par module" />
        </div>

        <section v-if="items.data.length" class="overflow-x-auto rounded-xl border bg-card" aria-label="Éléments supprimés" :aria-busy="loading">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-muted text-muted-foreground"><tr><th scope="col" class="px-5 py-3 font-medium">Élément</th><th scope="col" class="px-5 py-3 font-medium">Module</th><th scope="col" class="px-5 py-3 font-medium whitespace-nowrap">Supprimé le</th><th scope="col" class="px-5 py-3 text-right font-medium">Actions</th></tr></thead>
                <tbody class="divide-y">
                    <tr v-for="item in items.data" :key="key(item)" class="hover:bg-muted/30">
                        <td class="min-w-48 px-5 py-4"><p class="font-medium">{{ item.title }}</p><p v-if="item.restore_hint" class="mt-1 text-xs text-muted-foreground">{{ item.restore_hint }}</p></td>
                        <td class="px-5 py-4 whitespace-nowrap text-muted-foreground">{{ item.type_label }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-muted-foreground">{{ formatDate(item.deleted_at, 'fr', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</td>
                        <td class="px-5 py-4"><div class="flex justify-end gap-2 whitespace-nowrap">
                            <Button variant="outline" size="sm" :disabled="Boolean(working) || !item.can_restore" :loading="working === key(item)" :title="item.restore_hint || 'Restaurer cet élément'" @click="restore(item)"><RotateCcw aria-hidden="true" />Restaurer</Button>
                            <Button variant="destructive-ghost" size="sm" :disabled="Boolean(working)" @click="pending = item"><Trash2 aria-hidden="true" />Supprimer définitivement</Button>
                        </div></td>
                    </tr>
                </tbody>
            </table>
        </section>
        <EmptyState v-else :icon="Trash2" :title="isFiltered() ? 'Aucun résultat' : 'La corbeille est vide'" :description="isFiltered() ? 'Essayez un autre filtre ou un autre mot-clé.' : 'Les prochaines suppressions apparaîtront ici. Les éléments sont conservés jusqu’à leur restauration ou leur suppression définitive.'">
            <Button v-if="isFiltered()" variant="outline" @click="reset">Effacer les filtres</Button>
        </EmptyState>

        <Pagination v-if="items.meta.last_page > 1" class="mt-5" :meta="items.meta" :links="items.links" preserve-scroll :labels="{ previous: 'Précédent', next: 'Suivant', info: `${items.meta.from} à ${items.meta.to} sur ${items.meta.total}` }" />
        <ConfirmDialog v-model:open="confirmOpen" title="Supprimer définitivement cet élément ?" :description="pending ? `« ${pending.title} » ne pourra plus être restauré.${pending.type === 'journals' ? ' Tous les numéros de cette revue seront également supprimés définitivement.' : ''}` : ''" confirm-label="Supprimer définitivement" :loading="Boolean(working)" @confirm="destroy" />
    </AdminLayout>
</template>
