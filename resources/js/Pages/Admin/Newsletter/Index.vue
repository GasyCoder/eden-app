<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { Download, Languages, Mail, SearchX, Send, Trash2, UserCheck, UserMinus, UserPlus } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import BulkActionBar from '@/Components/Admin/Ui/BulkActionBar.vue';
import BulkCheckbox from '@/Components/Admin/Ui/BulkCheckbox.vue';
import ActionMenu from '@/Components/ui/ActionMenu.vue';
import Badge from '@/Components/ui/Badge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Dialog from '@/Components/ui/Dialog.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Field from '@/Components/ui/Field.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import Select from '@/Components/ui/Select.vue';
import { useBulkSelection } from '@/Composables/useBulkSelection';
import { useListFilters } from '@/Composables/useListFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass, textareaClass } from '@/lib/variants';

const props = defineProps({
    subscribers: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    counts: { type: Object, default: () => ({}) },
});

const { filters, loading, reset, isFiltered } = useListFilters('/admin/newsletter', {
    q: props.filters.q ?? '',
    status: props.filters.status || null,
});

const statusOptions = [{ value: null, label: 'Tous' }, { value: 'subscribed', label: 'Abonnés' }, { value: 'unsubscribed', label: 'Désinscrits' }];
const localeOptions = [{ value: 'fr', label: 'Français' }, { value: 'en', label: 'English' }];
const sourceLabels = { site: 'Site', manual: 'Ajout manuel', import: 'Import' };
const adding = ref(false);
const pendingDelete = ref(null);
const deleting = ref(false);
const form = useForm({ emails: '', name: '', locale: 'fr' });
const isMultiple = () => form.emails.split(/[\s,;]+/).filter(Boolean).length > 1;

const exportUrl = () => {
    const query = new URLSearchParams(Object.entries(filters).filter(([, value]) => value)).toString();

    return `/admin/newsletter/export${query ? `?${query}` : ''}`;
};

const submit = () => form.post('/admin/newsletter/subscribers', {
    preserveScroll: true,
    onSuccess: () => { form.reset(); adding.value = false; },
});

const setStatus = (subscriber, status) => router.patch(`/admin/newsletter/subscribers/${subscriber.id}`, { status }, { preserveScroll: true });

const actions = (subscriber) => [
    subscriber.status === 'subscribed'
        ? { label: 'Désinscrire', icon: UserMinus, onSelect: () => setStatus(subscriber, 'unsubscribed') }
        : { label: 'Réabonner', icon: UserCheck, onSelect: () => setStatus(subscriber, 'subscribed') },
    { label: 'Écrire', icon: Mail, href: `mailto:${subscriber.email}`, external: true },
    { label: 'Supprimer', icon: Trash2, danger: true, separator: true, onSelect: () => { pendingDelete.value = subscriber; } },
];

const confirmDelete = () => router.delete(`/admin/newsletter/subscribers/${pendingDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; pendingDelete.value = null; },
});

onMounted(() => {
    if (new URLSearchParams(window.location.search).has('ajouter')) {
        adding.value = true;
    }
});

const tableClass = 'w-full border-collapse text-sm max-md:block max-md:[&_tbody]:grid max-md:[&_tbody]:gap-3 max-md:[&_td]:flex max-md:[&_td]:items-center max-md:[&_td]:justify-between max-md:[&_td]:gap-3 max-md:[&_td]:border-0 max-md:[&_td]:px-0 max-md:[&_td]:py-1.5 max-md:[&_td[data-label]]:before:text-xs max-md:[&_td[data-label]]:before:text-muted-foreground max-md:[&_td[data-label]]:before:content-[attr(data-label)] max-md:[&_thead]:hidden max-md:[&_tr]:block max-md:[&_tr]:rounded-xl max-md:[&_tr]:border max-md:[&_tr]:bg-card max-md:[&_tr]:px-4 max-md:[&_tr]:py-3 [&_tbody_tr:hover]:bg-muted/50 [&_tbody_tr:last-child_td]:border-b-0 [&_td]:border-b [&_td]:px-4 [&_td]:py-3 [&_td]:align-middle [&_th]:h-10 [&_th]:border-b [&_th]:bg-muted [&_th]:px-4 [&_th]:text-left [&_th]:font-medium [&_th]:whitespace-nowrap [&_th]:text-muted-foreground';

// Sélection multiple et actions groupées
const bulk = useBulkSelection(() => props.subscribers.data);
const bulkActions = [
    { action: 'unsubscribe', label: 'Désinscrire', icon: UserMinus },
    { action: 'resubscribe', label: 'Réabonner', icon: UserCheck },
    { action: 'locale_fr', label: 'Emails en français', icon: Languages },
    { action: 'locale_en', label: 'Emails en anglais', icon: Languages },
    { action: 'export', label: 'Exporter', icon: Download, href: (ids) => `/admin/newsletter/export?${ids.map((id) => `ids[]=${id}`).join('&')}` },
    { action: 'delete', label: 'Supprimer', icon: Trash2, danger: true, confirm: { title: 'Supprimer les abonnés sélectionnés ?', description: (n) => `${n} abonné(s) seront effacés de la liste, historique d’envoi compris. Pour arrêter les envois, désinscrivez-les plutôt.` } },
];
</script>

<template>
    <AdminLayout title="Newsletter">
        <AdminPageHeader title="Newsletter" description="Abonnés inscrits depuis le site ou ajoutés à la main. Cochez « Envoyer aux abonnés » dans une actualité, un événement ou un numéro de revue pour les prévenir.">
            <template #actions>
                <Link href="/admin/newsletter/campaigns" :class="buttonVariants({ variant: 'outline' })"><Send aria-hidden="true" />Envois</Link>
                <button type="button" :class="buttonVariants()" @click="adding = true"><UserPlus aria-hidden="true" />Ajouter des abonnés</button>
            </template>
        </AdminPageHeader>

        <dl class="mb-6 grid grid-cols-3 divide-x rounded-xl border bg-card">
            <div class="p-4"><dt class="text-xs text-muted-foreground">Abonnés actifs</dt><dd class="mt-1 text-2xl font-semibold tabular-nums text-eden-700 dark:text-eden-300">{{ counts.active }}</dd></div>
            <div class="p-4"><dt class="text-xs text-muted-foreground">Nouveaux (30 jours)</dt><dd class="mt-1 text-2xl font-semibold tabular-nums">{{ counts.month }}</dd></div>
            <div class="p-4"><dt class="text-xs text-muted-foreground">Désinscrits</dt><dd class="mt-1 text-2xl font-semibold tabular-nums text-muted-foreground">{{ counts.unsubscribed }}</dd></div>
        </dl>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <input v-model="filters.q" type="search" :class="cn(inputClass, 'basis-full sm:max-w-sm sm:flex-1 sm:basis-64')" placeholder="Email ou nom…" aria-label="Rechercher un abonné">
            <div class="flex-1 sm:w-40 sm:flex-none"><Select v-model="filters.status" :options="statusOptions" aria-label="Filtrer par statut" size="sm" /></div>
            <span v-if="loading" class="inline-block size-4 animate-spin rounded-full border-2 border-current border-t-transparent text-muted-foreground" aria-label="Chargement"></span>
            <a :href="exportUrl()" :class="cn(buttonVariants({ variant: 'ghost', size: 'sm' }), 'sm:ml-auto')"><Download aria-hidden="true" />Exporter (CSV)</a>
        </div>

        <template v-if="subscribers.data.length">
            <div class="overflow-hidden rounded-xl border bg-card max-md:overflow-visible max-md:border-0 max-md:bg-transparent">
                <table :class="tableClass">
                    <thead>
                        <tr>
                            <th scope="col" class="w-px !pr-0"><BulkCheckbox :checked="bulk.allSelected" :indeterminate="bulk.someSelected" aria-label="Tout sélectionner sur cette page" @change="bulk.toggleAll()" /></th>
                            <th scope="col">Abonné</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Langue</th>
                            <th scope="col">Origine</th>
                            <th scope="col">Inscrit le</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="subscriber in subscribers.data" :key="subscriber.id" :class="['relative', bulk.isSelected(subscriber.id) && 'bg-eden-50/60 dark:bg-eden-950/40']">
                            <td class="w-px !pr-0 max-md:absolute max-md:top-4 max-md:left-4 max-md:!p-0"><BulkCheckbox :checked="bulk.isSelected(subscriber.id)" :aria-label="`Sélectionner ${subscriber.email}`" @change="bulk.toggle(subscriber.id)" /></td>
                            <td class="max-md:mb-1 max-md:!block max-md:border-b max-md:pb-2 max-md:pl-7">
                                <span class="grid min-w-56 gap-0.5 max-md:min-w-0">
                                    <span class="font-medium break-all">{{ subscriber.email }}</span>
                                    <span v-if="subscriber.name" class="text-xs text-muted-foreground">{{ subscriber.name }}</span>
                                </span>
                            </td>
                            <td data-label="Statut"><Badge :variant="subscriber.status === 'subscribed' ? 'success' : 'secondary'" dot>{{ subscriber.status === 'subscribed' ? 'Abonné' : 'Désinscrit' }}</Badge></td>
                            <td class="text-muted-foreground uppercase" data-label="Langue">{{ subscriber.locale }}</td>
                            <td class="text-muted-foreground" data-label="Origine">{{ sourceLabels[subscriber.source] ?? subscriber.source }}</td>
                            <td class="whitespace-nowrap text-muted-foreground" data-label="Inscrit le">{{ formatDate(subscriber.subscribed_at, 'fr', { day: 'numeric', month: 'short', year: 'numeric' }) }}</td>
                            <td class="w-px text-right max-md:!justify-end"><ActionMenu :items="actions(subscriber)" :label="`Actions pour ${subscriber.email}`" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Pagination :meta="subscribers.meta" :links="subscribers.links" preserve-scroll :labels="{ previous: 'Précédent', next: 'Suivant', info: `${subscribers.meta.from} à ${subscribers.meta.to} sur ${subscribers.meta.total}` }" />
        </template>
        <EmptyState v-else-if="isFiltered()" :icon="SearchX" title="Aucun résultat" description="Aucun abonné ne correspond à ces filtres.">
            <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="reset">Effacer les filtres</button>
        </EmptyState>
        <EmptyState v-else :icon="Mail" title="Aucun abonné" description="Les inscriptions du pied de page apparaîtront ici. Vous pouvez aussi ajouter des adresses à la main.">
            <button type="button" :class="buttonVariants({ size: 'sm' })" @click="adding = true"><UserPlus aria-hidden="true" />Ajouter des abonnés</button>
        </EmptyState>

        <Dialog v-model:open="adding" title="Ajouter des abonnés" description="Une adresse, ou plusieurs (une par ligne, ou séparées par des virgules) pour un import en lot.">
            <form id="add-subscribers" class="grid gap-4" @submit.prevent="submit">
                <Field v-slot="{ id, invalid, describedBy }" label="Adresse(s) email" :error="form.errors.emails">
                    <textarea :id="id" v-model="form.emails" rows="4" :class="textareaClass" placeholder="prenom.nom@exemple.mg" :aria-invalid="invalid" :aria-describedby="describedBy"></textarea>
                </Field>
                <Field v-if="!isMultiple()" v-slot="{ id }" label="Nom" optional :error="form.errors.name">
                    <input :id="id" v-model="form.name" :class="inputClass">
                </Field>
                <Field v-slot="{ id }" label="Langue des emails"><Select :id="id" v-model="form.locale" :options="localeOptions" /></Field>
                <p class="text-xs text-muted-foreground">Ajoutez uniquement des personnes qui ont accepté de recevoir les nouvelles de l’EDEN. Chaque email contient un lien de désinscription.</p>
            </form>
            <template #footer>
                <button type="button" :class="buttonVariants({ variant: 'outline' })" @click="adding = false">Annuler</button>
                <button type="submit" form="add-subscribers" :class="buttonVariants()" :disabled="form.processing || !form.emails.trim()">
                    <span v-if="form.processing" class="inline-block size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                    Ajouter
                </button>
            </template>
        </Dialog>

        <ConfirmDialog
            :open="Boolean(pendingDelete)"
            title="Supprimer cet abonné ?"
            :description="pendingDelete ? `${pendingDelete.email} sera effacé de la liste (historique d’envoi compris). Pour simplement arrêter les envois, désinscrivez-le.` : ''"
            :loading="deleting"
            @update:open="(value) => { if (!value) pendingDelete = null; }"
            @confirm="confirmDelete"
        />
        <BulkActionBar :selection="bulk" url="/admin/newsletter/subscribers/bulk" :actions="bulkActions" :noun="['abonné', 'abonnés']" />
    </AdminLayout>
</template>
