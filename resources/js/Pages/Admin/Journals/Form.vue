<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { ExternalLink, Layers, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import ImageField from '@/Components/Admin/Ui/ImageField.vue';
import LocaleTabs from '@/Components/Admin/Ui/LocaleTabs.vue';
import RichTextEditor from '@/Components/Admin/Ui/RichTextEditor.vue';
import StatusBadge from '@/Components/Admin/Ui/StatusBadge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Field from '@/Components/ui/Field.vue';
import Select from '@/Components/ui/Select.vue';
import Switch from '@/Components/ui/Switch.vue';
import { useUnsavedGuard } from '@/Composables/useUnsavedGuard';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { buttonVariants, inputClass } from '@/lib/variants';
import { cn } from '@/lib/utils';

const props = defineProps({
    journal: { type: Object, required: true },
    domains: { type: Array, default: () => [] },
    publicUrl: { type: String, default: null },
    issuesCount: { type: Number, default: 0 },
});

const isNew = computed(() => !props.journal.id);
const locale = ref('fr');
const form = useForm({
    title: { ...props.journal.title },
    description: { ...props.journal.description },
    frequency: { ...props.journal.frequency },
    slug: props.journal.slug ?? '',
    domain: props.journal.domain,
    cover_image: props.journal.cover_image,
    issn: props.journal.issn,
    external_url: props.journal.external_url,
    editor: props.journal.editor,
    language: props.journal.language,
    status: props.journal.status,
    is_featured: props.journal.is_featured,
    position: props.journal.position,
});

useUnsavedGuard(form);

const statusOptions = [
    { value: 'draft', label: 'Brouillon (non visible)' },
    { value: 'active', label: 'Active' },
    { value: 'archived', label: 'Archivée (visible, plus éditée)' },
];
const complete = computed(() => ({ fr: Boolean(form.title.fr), en: Boolean(form.title.en) }));
const err = (field) => form.errors[`${field}.${locale.value}`] || (locale.value === 'en' ? '' : form.errors[`${field}.fr`]);
const confirmDelete = ref(false);
const deleting = ref(false);

const submit = () => {
    const options = { preserveScroll: true, onSuccess: () => form.defaults(), onError: (errors) => { if (errors['title.fr']) locale.value = 'fr'; } };

    if (isNew.value) {
        form.post('/admin/journals', options);
    } else {
        form.put(`/admin/journals/${props.journal.id}`, options);
    }
};

const destroy = () => router.delete(`/admin/journals/${props.journal.id}`, {
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; },
});
</script>

<template>
    <AdminLayout :title="isNew ? 'Nouvelle revue' : 'Modifier la revue'">
        <form class="grid gap-4" novalidate @submit.prevent="submit">
            <AdminPageHeader :title="isNew ? 'Nouvelle revue' : (journal.title.fr || 'Revue')" back-href="/admin/journals" back-label="Revues">
                <template #badges><StatusBadge v-if="!isNew" :status="journal.status" /></template>
                <template #actions>
                    <a v-if="publicUrl" :href="publicUrl" target="_blank" rel="noopener noreferrer" :class="buttonVariants({ variant: 'ghost' })"><ExternalLink aria-hidden="true" />Voir</a>
                    <button type="submit" :class="buttonVariants()" :disabled="form.processing">
                        <span v-if="form.processing" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        Enregistrer
                    </button>
                </template>
            </AdminPageHeader>

            <div v-if="form.hasErrors" class="flex items-start gap-3 rounded-lg border p-4 text-sm border-laterite-200 bg-laterite-50 text-laterite-700 dark:border-laterite-900 dark:bg-laterite-950 dark:text-laterite-300" role="alert">Certains champs sont à corriger : ils sont signalés ci-dessous.</div>

            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="grid min-w-0 gap-4">
                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="mb-4 grid gap-1"><h2 class="text-base font-semibold">Présentation</h2></div>
                        <LocaleTabs v-model="locale" :complete="complete" />
                        <div class="grid gap-4">
                            <Field v-slot="{ id, invalid, describedBy }" :label="locale === 'fr' ? 'Titre' : 'Title'" :error="err('title')">
                                <input :id="id" v-model="form.title[locale]" :class="cn(inputClass, 'h-11 text-base font-medium')" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id }" label="Description" optional hint="Ligne éditoriale, public visé, modalités de soumission…" :error="err('description')">
                                <RichTextEditor :id="id" :key="locale" v-model="form.description[locale]" :label="`Description (${locale.toUpperCase()})`" />
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Périodicité" optional :error="err('frequency')">
                                <input :id="id" v-model="form.frequency[locale]" :class="inputClass" :placeholder="locale === 'fr' ? 'Semestrielle' : 'Biannual'" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>

                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="mb-4 grid gap-1">
                            <h2 class="text-base font-semibold">Informations éditoriales</h2>
                        </div>
                        <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                            <Field v-slot="{ id, invalid, describedBy }" label="Domaine / thématique" optional hint="Sert de filtre sur la page publique." :error="form.errors.domain">
                                <input :id="id" v-model="form.domain" :class="inputClass" list="journal-domains" :aria-invalid="invalid" :aria-describedby="describedBy">
                                <datalist id="journal-domains"><option v-for="item in domains" :key="item" :value="item" /></datalist>
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="ISSN" optional hint="Format 1234-5678." :error="form.errors.issn">
                                <input :id="id" v-model="form.issn" :class="cn(inputClass, 'tabular-nums')" placeholder="1234-5678" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Responsable / éditeur" optional :error="form.errors.editor">
                                <input :id="id" v-model="form.editor" :class="inputClass" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Langue(s) de publication" optional :error="form.errors.language">
                                <input :id="id" v-model="form.language" :class="inputClass" placeholder="Français, anglais" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Site de la revue" optional :error="form.errors.external_url" wide>
                                <input :id="id" v-model="form.external_url" type="url" :class="inputClass" placeholder="https://…" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-4 [&_.card-title]:mb-4">
                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <h2 class="mb-4 text-base font-semibold">Visibilité</h2>
                        <div class="grid gap-4">
                            <Field v-slot="{ id }" label="Statut"><Select :id="id" v-model="form.status" :options="statusOptions" /></Field>
                            <Switch v-model="form.is_featured" label="Mettre en avant" description="Affichée sur l’accueil (3 revues maximum)." />
                            <Field v-slot="{ id, describedBy }" label="Ordre d’affichage" optional hint="Plus petit = plus haut." :error="form.errors.position">
                                <input :id="id" v-model.number="form.position" type="number" min="0" :class="inputClass" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Adresse (slug)" optional :error="form.errors.slug">
                                <input :id="id" v-model="form.slug" :class="inputClass" placeholder="genere-automatiquement" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>
                    <section v-if="!isNew" class="grid gap-3 rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="grid gap-1">
                            <h2 class="text-base font-semibold">Numéros</h2>
                            <p class="text-sm text-muted-foreground">{{ issuesCount ? `${issuesCount} ${issuesCount > 1 ? 'numéros' : 'numéro'} : PDF, couverture, éditorial et sommaire.` : 'Ajoutez les numéros parus (PDF, couverture, sommaire).' }}</p>
                        </div>
                        <Link :href="`/admin/journals/${journal.id}/issues`" :class="buttonVariants({ variant: 'outline', size: 'sm' })"><Layers aria-hidden="true" />Gérer les numéros</Link>
                    </section>
                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <ImageField v-model="form.cover_image" label="Couverture" aspect="3 / 4" :error="form.errors.cover_image" />
                    </section>
                    <section v-if="!isNew" class="rounded-xl border bg-card p-5 text-card-foreground border-laterite-200 dark:border-laterite-900">
                        <h2 class="text-base font-semibold">Supprimer</h2>
                        <p class="text-sm text-muted-foreground">Préférez « Archivée » pour conserver la fiche.</p>
                        <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'sm' })" @click="confirmDelete = true"><Trash2 aria-hidden="true" />Supprimer la revue</button>
                    </section>
                </aside>
            </div>
        </form>

        <ConfirmDialog v-model:open="confirmDelete" title="Supprimer cette revue ?" description="La revue et ses numéros seront placés dans la corbeille." :loading="deleting" @confirm="destroy" />
    </AdminLayout>
</template>
