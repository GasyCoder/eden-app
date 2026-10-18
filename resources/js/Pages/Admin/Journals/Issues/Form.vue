<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, ExternalLink, Plus, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import NotifySubscribersField from '@/Components/Admin/Ui/NotifySubscribersField.vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import ImageField from '@/Components/Admin/Ui/ImageField.vue';
import LocaleTabs from '@/Components/Admin/Ui/LocaleTabs.vue';
import PdfField from '@/Components/Admin/Ui/PdfField.vue';
import RichTextEditor from '@/Components/Admin/Ui/RichTextEditor.vue';
import StatusBadge from '@/Components/Admin/Ui/StatusBadge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Field from '@/Components/ui/Field.vue';
import Select from '@/Components/ui/Select.vue';
import { useUnsavedGuard } from '@/Composables/useUnsavedGuard';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass, textareaClass } from '@/lib/variants';

const props = defineProps({
    journal: { type: Object, required: true },
    issue: { type: Object, required: true },
    rubrics: { type: Array, default: () => [] },
    publicUrl: { type: String, default: null },
    newsletter: { type: Object, default: () => ({ subscribers: 0, campaign: null }) },
});

const base = `/admin/journals/${props.journal.id}/issues`;
const isNew = computed(() => !props.issue.id);
const locale = ref('fr');
const form = useForm({
    number: props.issue.number,
    year: props.issue.year,
    period: { ...props.issue.period },
    title: { ...props.issue.title },
    editorial: { ...props.issue.editorial },
    slug: props.issue.slug,
    cover_image: props.issue.cover_image,
    pdf_url: props.issue.pdf_url,
    pdf_size: props.issue.pdf_size,
    page_count: props.issue.page_count,
    sections: props.issue.sections.map((section) => ({ ...section })),
    stats: props.issue.stats.map((stat) => ({ ...stat })),
    status: props.issue.status,
    published_at: props.issue.published_at,
    notify_subscribers: false,
});

useUnsavedGuard(form);

const label = computed(() => `N° ${String(form.number || 0).padStart(2, '0')} · ${form.year || ''}`);
const statusOptions = [
    { value: 'draft', label: 'Brouillon (non visible)' },
    { value: 'published', label: 'Publié' },
];
const complete = computed(() => ({ fr: Boolean(form.period.fr || form.editorial.fr), en: Boolean(form.period.en || form.editorial.en) }));
const err = (field) => form.errors[`${field}.${locale.value}`] || (locale.value === 'en' ? '' : form.errors[`${field}.fr`]);

// Sommaire : un article par ligne, regroupé par rubrique sur le site
const openSection = ref(form.sections.length ? null : 0);
const addSection = () => {
    const previous = form.sections[form.sections.length - 1];
    form.sections.push({ rubric: previous?.rubric ?? props.rubrics[0] ?? '', title: '', summary: '', image: '', page: null });
    openSection.value = form.sections.length - 1;
};
const moveSection = (index, delta) => {
    const target = index + delta;
    const [item] = form.sections.splice(index, 1);
    form.sections.splice(target, 0, item);
    openSection.value = target;
};
const removeSection = (index) => {
    form.sections.splice(index, 1);
    openSection.value = null;
};

const addStat = () => form.stats.push({ label: '', value: null });
const onPdfUploaded = (payload) => {
    form.pdf_size = payload.size ?? null;
    form.page_count = payload.pages ?? form.page_count;
};

const confirmDelete = ref(false);
const deleting = ref(false);

const submit = () => {
    const options = { preserveScroll: true, onSuccess: () => form.defaults() };

    if (isNew.value) {
        form.post(base, options);
    } else {
        form.put(`${base}/${props.issue.id}`, options);
    }
};

const destroy = () => router.delete(`${base}/${props.issue.id}`, {
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; },
});

const cardClass = 'rounded-xl border bg-card p-5 text-card-foreground';
</script>

<template>
    <AdminLayout :title="isNew ? 'Nouveau numéro' : `${journal.title} ${label}`">
        <form class="grid gap-4" novalidate @submit.prevent="submit">
            <AdminPageHeader :title="isNew ? `Nouveau numéro de ${journal.title}` : `${journal.title} ${label}`" :back-href="base" back-label="Numéros">
                <template #badges><StatusBadge v-if="!isNew" :status="issue.status" /></template>
                <template #actions>
                    <a v-if="publicUrl" :href="publicUrl" target="_blank" rel="noopener noreferrer" :class="buttonVariants({ variant: 'ghost' })"><ExternalLink aria-hidden="true" />Voir</a>
                    <button type="submit" :class="buttonVariants()" :disabled="form.processing">
                        <span v-if="form.processing" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        Enregistrer
                    </button>
                </template>
            </AdminPageHeader>

            <div v-if="form.hasErrors" class="rounded-lg border border-laterite-200 bg-laterite-50 p-4 text-sm text-laterite-700 dark:border-laterite-900 dark:bg-laterite-950 dark:text-laterite-300" role="alert">Certains champs sont à corriger : ils sont signalés ci-dessous.</div>

            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="grid min-w-0 gap-4">
                    <section :class="cardClass">
                        <div class="mb-4 grid gap-1">
                            <h2 class="text-base font-semibold">Numéro</h2>
                            <p class="text-sm text-muted-foreground">Affiché « {{ label }} » sur le site.</p>
                        </div>
                        <div class="grid items-start gap-4 sm:grid-cols-3 sm:gap-x-5">
                            <Field v-slot="{ id, invalid, describedBy }" label="Numéro" :error="form.errors.number">
                                <input :id="id" v-model.number="form.number" type="number" min="1" :class="cn(inputClass, 'tabular-nums')" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Année" :error="form.errors.year">
                                <input :id="id" v-model.number="form.year" type="number" min="1990" max="2100" :class="cn(inputClass, 'tabular-nums')" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Nombre de pages" optional :error="form.errors.page_count">
                                <input :id="id" v-model.number="form.page_count" type="number" min="1" :class="cn(inputClass, 'tabular-nums')" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>

                    <section :class="cardClass">
                        <div class="mb-4 grid gap-1"><h2 class="text-base font-semibold">Présentation</h2></div>
                        <LocaleTabs v-model="locale" :complete="complete" />
                        <div class="grid gap-4">
                            <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                                <Field v-slot="{ id, describedBy }" :label="locale === 'fr' ? 'Période couverte' : 'Period'" optional :error="err('period')">
                                    <input :id="id" v-model="form.period[locale]" :class="inputClass" :placeholder="locale === 'fr' ? 'Juin - Août' : 'June - August'" :aria-describedby="describedBy">
                                </Field>
                                <Field v-slot="{ id, describedBy }" :label="locale === 'fr' ? 'Sous-titre' : 'Subtitle'" optional :error="err('title')">
                                    <input :id="id" v-model="form.title[locale]" :class="inputClass" :placeholder="locale === 'fr' ? 'Nouvelle édition' : 'New edition'" :aria-describedby="describedBy">
                                </Field>
                            </div>
                            <Field v-slot="{ id }" label="Éditorial" optional hint="Mot d’ouverture du numéro ; son début sert de résumé sur l’accueil." :error="err('editorial')">
                                <RichTextEditor :id="id" :key="locale" v-model="form.editorial[locale]" :label="`Éditorial (${locale.toUpperCase()})`" />
                            </Field>
                        </div>
                    </section>

                    <section :class="cardClass">
                        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                            <div class="grid gap-1">
                                <h2 class="text-base font-semibold">Sommaire</h2>
                                <p class="text-sm text-muted-foreground">Un article par entrée. Sur le site, les articles sont regroupés par rubrique dans l’ordre ci-dessous.</p>
                            </div>
                            <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="addSection"><Plus aria-hidden="true" />Ajouter un article</button>
                        </div>
                        <datalist id="issue-rubrics"><option v-for="rubric in rubrics" :key="rubric" :value="rubric" /></datalist>
                        <p v-if="!form.sections.length" class="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">Aucun article. Ajoutez par exemple « Doctoriales 2026 : l’EDEN à l’honneur » dans la rubrique « Formation & doctorat ».</p>
                        <ol v-else class="grid gap-2">
                            <li v-for="(section, index) in form.sections" :key="index" :class="cn('rounded-lg border', openSection === index && 'border-eden-300 dark:border-eden-800')">
                                <div class="flex items-center gap-2 p-2 pl-3">
                                    <button type="button" class="grid min-w-0 flex-1 text-left" :aria-expanded="openSection === index ? 'true' : 'false'" @click="openSection = openSection === index ? null : index">
                                        <span class="truncate text-xs text-muted-foreground">{{ section.rubric || 'Sans rubrique' }}</span>
                                        <span :class="cn('truncate text-sm font-medium', !section.title && 'text-muted-foreground italic')">{{ section.title || 'Article sans titre' }}</span>
                                    </button>
                                    <span v-if="form.errors[`sections.${index}.title`]" class="text-xs font-medium text-destructive">Titre manquant</span>
                                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" :disabled="index === 0" aria-label="Monter" @click="moveSection(index, -1)"><ChevronUp aria-hidden="true" /></button>
                                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" :disabled="index === form.sections.length - 1" aria-label="Descendre" @click="moveSection(index, 1)"><ChevronDown aria-hidden="true" /></button>
                                    <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'icon-sm' })" aria-label="Retirer l’article" @click="removeSection(index)"><Trash2 aria-hidden="true" /></button>
                                </div>
                                <div v-if="openSection === index" class="grid gap-4 border-t p-4 sm:grid-cols-[minmax(0,1fr)_12rem]">
                                    <div class="grid content-start gap-4">
                                        <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_6rem]">
                                            <Field v-slot="{ id }" label="Rubrique">
                                                <input :id="id" v-model="section.rubric" list="issue-rubrics" :class="inputClass" placeholder="Actualités">
                                            </Field>
                                            <Field v-slot="{ id }" label="Page" optional>
                                                <input :id="id" v-model.number="section.page" type="number" min="1" :class="inputClass">
                                            </Field>
                                        </div>
                                        <Field v-slot="{ id, invalid }" label="Titre" :error="form.errors[`sections.${index}.title`]">
                                            <input :id="id" v-model="section.title" :class="inputClass" :aria-invalid="invalid">
                                        </Field>
                                        <Field v-slot="{ id }" label="Résumé" optional>
                                            <textarea :id="id" v-model="section.summary" rows="4" :class="textareaClass"></textarea>
                                        </Field>
                                    </div>
                                    <ImageField v-model="section.image" label="Photo" aspect="4 / 3" hint="Facultative." />
                                </div>
                            </li>
                        </ol>
                    </section>

                    <section :class="cardClass">
                        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                            <div class="grid gap-1">
                                <h2 class="text-base font-semibold">La période en chiffres</h2>
                                <p class="text-sm text-muted-foreground">Affiché sous forme d’histogramme (ex. Communication 13, Formation 4).</p>
                            </div>
                            <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="addStat"><Plus aria-hidden="true" />Ajouter un indicateur</button>
                        </div>
                        <p v-if="!form.stats.length" class="text-sm text-muted-foreground">Aucun indicateur.</p>
                        <div v-else class="grid gap-2">
                            <div v-for="(stat, index) in form.stats" :key="index" class="grid grid-cols-[minmax(0,1fr)_6rem_auto] items-start gap-2">
                                <div class="grid gap-1">
                                    <input v-model="stat.label" :class="inputClass" placeholder="Libellé" :aria-label="`Libellé de l’indicateur ${index + 1}`" :aria-invalid="form.errors[`stats.${index}.label`] ? 'true' : undefined">
                                    <span v-if="form.errors[`stats.${index}.label`]" class="text-xs text-destructive">{{ form.errors[`stats.${index}.label`] }}</span>
                                </div>
                                <div class="grid gap-1">
                                    <input v-model.number="stat.value" type="number" min="0" step="any" :class="cn(inputClass, 'tabular-nums')" placeholder="0" :aria-label="`Valeur de l’indicateur ${index + 1}`" :aria-invalid="form.errors[`stats.${index}.value`] ? 'true' : undefined">
                                    <span v-if="form.errors[`stats.${index}.value`]" class="text-xs text-destructive">Valeur requise</span>
                                </div>
                                <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'icon' })" aria-label="Retirer l’indicateur" @click="form.stats.splice(index, 1)"><Trash2 aria-hidden="true" /></button>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-4">
                    <section :class="cardClass">
                        <h2 class="mb-4 text-base font-semibold">Publication</h2>
                        <div class="grid gap-4">
                            <Field v-slot="{ id }" label="Statut"><Select :id="id" v-model="form.status" :options="statusOptions" /></Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Date de parution" optional hint="Vide : date du jour à la publication." :error="form.errors.published_at">
                                <input :id="id" v-model="form.published_at" type="date" :class="inputClass" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Adresse (slug)" optional :hint="`Par défaut : n${String(form.number || 0).padStart(2, '0')}-${form.year}`" :error="form.errors.slug">
                                <input :id="id" v-model="form.slug" :class="inputClass" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>
                    <NotifySubscribersField v-model="form.notify_subscribers" :newsletter="newsletter" :published="form.status === 'published'" noun="le numéro" />
                    <section :class="cardClass">
                        <PdfField v-model="form.pdf_url" :error="form.errors.pdf_url" @uploaded="onPdfUploaded" />
                    </section>
                    <section :class="cardClass">
                        <ImageField v-model="form.cover_image" label="Couverture" aspect="3 / 4" hint="Première page du numéro. Sans image, une couverture typographique est générée." :error="form.errors.cover_image" />
                    </section>
                    <section v-if="!isNew" :class="cn(cardClass, 'grid gap-2 border-laterite-200 dark:border-laterite-900')">
                        <h2 class="text-base font-semibold">Supprimer</h2>
                        <p class="text-sm text-muted-foreground">Repassez en brouillon pour masquer le numéro sans le perdre.</p>
                        <button type="button" :class="cn(buttonVariants({ variant: 'destructive-ghost', size: 'sm' }), 'justify-self-start')" @click="confirmDelete = true"><Trash2 aria-hidden="true" />Supprimer le numéro</button>
                    </section>
                </aside>
            </div>
        </form>

        <ConfirmDialog v-model:open="confirmDelete" title="Supprimer ce numéro ?" description="Il sera retiré du site et placé dans la corbeille." :loading="deleting" @confirm="destroy" />
    </AdminLayout>
</template>
