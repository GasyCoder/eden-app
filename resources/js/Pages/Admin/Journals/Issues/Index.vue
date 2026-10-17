<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ExternalLink, FilePlus2, FileText, Layers, Pencil, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import StatusBadge from '@/Components/Admin/Ui/StatusBadge.vue';
import IssueCover from '@/Components/Portal/IssueCover.vue';
import ActionMenu from '@/Components/ui/ActionMenu.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate } from '@/lib/i18n';
import { buttonVariants } from '@/lib/variants';

const props = defineProps({
    journal: { type: Object, required: true },
    issues: { type: Array, default: () => [] },
});

const base = `/admin/journals/${props.journal.id}/issues`;
const pendingDelete = ref(null);
const deleting = ref(false);

const actions = (issue) => [
    { label: 'Modifier', icon: Pencil, href: `${base}/${issue.id}/edit` },
    { label: 'Voir sur le site', icon: ExternalLink, href: `/revues/${props.journal.slug}/${issue.slug}`, external: true },
    { label: 'Supprimer', icon: Trash2, danger: true, separator: true, onSelect: () => { pendingDelete.value = issue; } },
];

const confirmDelete = () => router.delete(`${base}/${pendingDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; pendingDelete.value = null; },
});
</script>

<template>
    <AdminLayout :title="`Numéros · ${journal.title}`">
        <AdminPageHeader :title="`Numéros de ${journal.title}`" :description="journal.frequency ? `${journal.frequency}. Chaque numéro : couverture, PDF, éditorial, sommaire et chiffres.` : 'Chaque numéro : couverture, PDF, éditorial, sommaire et chiffres.'" :back-href="`/admin/journals/${journal.id}/edit`" back-label="Revue">
            <template #actions>
                <Link :href="`${base}/create`" :class="buttonVariants()"><FilePlus2 aria-hidden="true" />Nouveau numéro</Link>
            </template>
        </AdminPageHeader>

        <ul v-if="issues.length" class="grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            <li v-for="issue in issues" :key="issue.id" class="group relative grid content-start gap-3">
                <IssueCover :issue="{ ...issue, journal: { frequency: journal.frequency } }" :journal-title="journal.title" />
                <div class="flex items-start justify-between gap-2">
                    <div class="grid min-w-0 gap-1">
                        <Link :href="`${base}/${issue.id}/edit`" class="font-semibold hover:underline hover:underline-offset-4">{{ issue.label }}</Link>
                        <span class="text-xs text-muted-foreground">{{ issue.period || 'Période non renseignée' }}</span>
                        <span class="flex flex-wrap items-center gap-1.5">
                            <StatusBadge :status="issue.status" />
                            <span v-if="issue.has_pdf" class="inline-flex items-center gap-1 text-xs text-muted-foreground"><FileText class="size-3.5" aria-hidden="true" />PDF</span>
                        </span>
                        <span v-if="issue.published_at" class="text-xs text-muted-foreground">{{ formatDate(issue.published_at) }}</span>
                    </div>
                    <ActionMenu :items="actions(issue)" :label="`Actions pour ${issue.label}`" />
                </div>
            </li>
        </ul>
        <EmptyState v-else :icon="Layers" title="Aucun numéro" :description="`Ajoutez le premier numéro de ${journal.title} : PDF, couverture, éditorial et sommaire.`">
            <Link :href="`${base}/create`" :class="buttonVariants({ size: 'sm' })"><FilePlus2 aria-hidden="true" />Ajouter un numéro</Link>
        </EmptyState>

        <ConfirmDialog
            :open="Boolean(pendingDelete)"
            title="Supprimer ce numéro ?"
            :description="pendingDelete ? `${pendingDelete.label} sera retiré du site. Pour le masquer sans le perdre, repassez-le en brouillon.` : ''"
            :loading="deleting"
            @update:open="(value) => { if (!value) pendingDelete = null; }"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
