<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ExternalLink, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import ImageField from '@/Components/Admin/Ui/ImageField.vue';
import LocaleTabs from '@/Components/Admin/Ui/LocaleTabs.vue';
import RepeatableList from '@/Components/Admin/Ui/RepeatableList.vue';
import RichTextEditor from '@/Components/Admin/Ui/RichTextEditor.vue';
import StatusBadge from '@/Components/Admin/Ui/StatusBadge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Field from '@/Components/ui/Field.vue';
import Select from '@/Components/ui/Select.vue';
import Switch from '@/Components/ui/Switch.vue';
import { useUnsavedGuard } from '@/Composables/useUnsavedGuard';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass, textareaClass } from '@/lib/variants';

const props = defineProps({
    project: { type: Object, required: true },
    teams: { type: Array, default: () => [] },
    publicUrl: { type: String, default: null },
});

const isNew = computed(() => !props.project.id);
const locale = ref('fr');
const clone = (value) => JSON.parse(JSON.stringify(value ?? []));
const form = useForm({
    title: { ...props.project.title },
    subtitle: { ...props.project.subtitle },
    summary: { ...props.project.summary },
    body: { ...props.project.body },
    slug: props.project.slug,
    acronym: props.project.acronym,
    cover_image: props.project.cover_image,
    logo: props.project.logo,
    stage: props.project.stage,
    team: props.project.team,
    funder: props.project.funder,
    leader: props.project.leader,
    start_year: props.project.start_year,
    end_year: props.project.end_year,
    external_url: props.project.external_url,
    objectives: clone(props.project.objectives),
    beneficiaries: clone(props.project.beneficiaries),
    partners: clone(props.project.partners),
    work_packages: clone(props.project.work_packages),
    facts: clone(props.project.facts),
    funding_notice: props.project.funding_notice,
    funding_logo: props.project.funding_logo,
    gallery_image: props.project.gallery_image,
    status: props.project.status,
    is_featured: props.project.is_featured,
    position: props.project.position,
});

useUnsavedGuard(form);

const statusOptions = [{ value: 'draft', label: 'Brouillon (non visible)' }, { value: 'published', label: 'Publié' }];
const progressOptions = [{ value: 'upcoming', label: 'Démarrage' }, { value: 'ongoing', label: 'En cours' }, { value: 'completed', label: 'Terminé' }];
const complete = computed(() => ({ fr: Boolean(form.title.fr), en: Boolean(form.title.en) }));
const err = (field) => form.errors[`${field}.${locale.value}`] || (locale.value === 'en' ? '' : form.errors[`${field}.fr`]);
const listError = (prefix) => Object.entries(form.errors).find(([key]) => key.startsWith(`${prefix}.`))?.[1];
const confirmDelete = ref(false);
const deleting = ref(false);

const submit = () => {
    const options = { preserveScroll: true, onSuccess: () => form.defaults(), onError: (errors) => { if (errors['title.fr']) locale.value = 'fr'; } };

    if (isNew.value) {
        form.post('/admin/projects', options);
    } else {
        form.put(`/admin/projects/${props.project.id}`, options);
    }
};

const destroy = () => router.delete(`/admin/projects/${props.project.id}`, {
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; },
});

const card = 'rounded-xl border bg-card p-5 text-card-foreground';
</script>

<template>
    <AdminLayout :title="isNew ? 'Nouveau projet' : 'Modifier le projet'">
        <form class="grid gap-4" novalidate @submit.prevent="submit">
            <AdminPageHeader :title="isNew ? 'Nouveau projet' : (project.title.fr || 'Projet')" back-href="/admin/projects" back-label="Projets">
                <template #badges><StatusBadge v-if="!isNew" :status="project.status" /></template>
                <template #actions>
                    <a v-if="publicUrl" :href="publicUrl" target="_blank" rel="noopener noreferrer" :class="buttonVariants({ variant: 'ghost' })"><ExternalLink aria-hidden="true" />Voir la page</a>
                    <button type="submit" :class="buttonVariants()" :disabled="form.processing">
                        <span v-if="form.processing" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        Enregistrer
                    </button>
                </template>
            </AdminPageHeader>

            <div v-if="form.hasErrors" class="rounded-lg border border-laterite-200 bg-laterite-50 p-4 text-sm text-laterite-700 dark:border-laterite-900 dark:bg-laterite-950 dark:text-laterite-300" role="alert">Certains champs sont à corriger : ils sont signalés ci-dessous.</div>

            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="grid min-w-0 gap-4">
                    <section :class="card">
                        <div class="mb-4 grid gap-1"><h2 class="text-base font-semibold">Présentation</h2><p class="text-sm text-muted-foreground">Le résumé apparaît sur les cartes ; la description détaillée sur la page du projet.</p></div>
                        <LocaleTabs v-model="locale" :complete="complete" />
                        <div class="grid gap-4">
                            <Field v-slot="{ id, invalid, describedBy }" :label="locale === 'fr' ? 'Titre' : 'Title'" :error="err('title')">
                                <input :id="id" v-model="form.title[locale]" :class="cn(inputClass, 'h-11 text-base font-medium')" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" :label="locale === 'fr' ? 'Nom complet' : 'Full name'" optional hint="Ex. « Réseau Académique pour la Valorisation des Enseignants… »" :error="err('subtitle')">
                                <input :id="id" v-model="form.subtitle[locale]" :class="inputClass" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" :label="locale === 'fr' ? 'Résumé' : 'Summary'" optional :error="err('summary')">
                                <textarea :id="id" v-model="form.summary[locale]" rows="3" :class="textareaClass" :aria-describedby="describedBy"></textarea>
                            </Field>
                            <Field v-slot="{ id }" :label="locale === 'fr' ? 'Description détaillée' : 'Detailed description'" optional :error="err('body')">
                                <RichTextEditor :id="id" :key="locale" v-model="form.body[locale]" :label="`Description (${locale.toUpperCase()})`" />
                            </Field>
                        </div>
                    </section>

                    <section :class="card">
                        <h2 class="mb-4 text-base font-semibold">Informations</h2>
                        <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                            <Field v-slot="{ id, describedBy }" label="Acronyme" optional :error="form.errors.acronym">
                                <input :id="id" v-model="form.acronym" :class="inputClass" placeholder="RAVENALA" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id }" label="Avancement" :error="form.errors.stage"><Select :id="id" v-model="form.stage" :options="progressOptions" /></Field>
                            <Field v-slot="{ id, describedBy }" label="Équipe d’accueil" optional :error="form.errors.team">
                                <input :id="id" v-model="form.team" :class="inputClass" list="project-teams" placeholder="EAD 1" :aria-describedby="describedBy">
                                <datalist id="project-teams"><option v-for="team in teams" :key="team" :value="team" /></datalist>
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Financement" optional :error="form.errors.funder">
                                <input :id="id" v-model="form.funder" :class="inputClass" placeholder="Union européenne (Erasmus+)" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Chef de projet" optional :error="form.errors.leader" wide>
                                <input :id="id" v-model="form.leader" :class="inputClass" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Année de début" optional :error="form.errors.start_year">
                                <input :id="id" v-model.number="form.start_year" type="number" min="1990" max="2100" :class="cn(inputClass, 'tabular-nums')" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Année de fin" optional :error="form.errors.end_year">
                                <input :id="id" v-model.number="form.end_year" type="number" min="1990" max="2100" :class="cn(inputClass, 'tabular-nums')" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Site externe du projet" optional hint="Utilisé comme lien de la carte si le projet n’a pas de page détaillée." :error="form.errors.external_url" wide>
                                <input :id="id" v-model="form.external_url" type="url" :class="inputClass" placeholder="https://…" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>

                    <section :class="card">
                        <div class="mb-5 grid gap-1">
                            <h2 class="text-base font-semibold">Page détaillée</h2>
                            <p class="text-sm text-muted-foreground">Facultatif. Dès qu’une description ou une liste est renseignée, le projet obtient sa page (/projets/{{ form.slug || 'adresse' }}).</p>
                        </div>
                        <div class="grid gap-8">
                            <div class="grid gap-2">
                                <h3 class="text-sm font-semibold">Chiffres clés</h3>
                                <RepeatableList v-model="form.facts" :blank="() => ({ label: '', value: '' })" add-label="Ajouter un chiffre" empty-text="Ex. 5 partenaires, 3 universités bénéficiaires." item-label="le chiffre" inline>
                                    <template #default="{ item }">
                                        <div class="grid grid-cols-[6rem_minmax(0,1fr)] gap-2">
                                            <input v-model="item.value" :class="inputClass" placeholder="5" aria-label="Valeur">
                                            <input v-model="item.label" :class="inputClass" placeholder="Partenaires" aria-label="Libellé">
                                        </div>
                                    </template>
                                </RepeatableList>
                                <p v-if="listError('facts')" class="text-xs font-medium text-destructive">{{ listError('facts') }}</p>
                            </div>

                            <div class="grid gap-2">
                                <h3 class="text-sm font-semibold">Objectifs</h3>
                                <RepeatableList v-model="form.objectives" :blank="() => ''" add-label="Ajouter un objectif" empty-text="Aucun objectif." item-label="l’objectif" inline>
                                    <template #default="{ index }">
                                        <input v-model="form.objectives[index]" :class="inputClass" :aria-label="`Objectif ${index + 1}`">
                                    </template>
                                </RepeatableList>
                            </div>

                            <div class="grid gap-2">
                                <h3 class="text-sm font-semibold">Bénéficiaires</h3>
                                <RepeatableList v-model="form.beneficiaries" :blank="() => ''" add-label="Ajouter un bénéficiaire" empty-text="Aucun bénéficiaire." item-label="le bénéficiaire" inline>
                                    <template #default="{ index }">
                                        <input v-model="form.beneficiaries[index]" :class="inputClass" :aria-label="`Bénéficiaire ${index + 1}`">
                                    </template>
                                </RepeatableList>
                            </div>

                            <div class="grid gap-2">
                                <h3 class="text-sm font-semibold">Partenaires</h3>
                                <RepeatableList v-model="form.partners" :blank="() => ({ name: '', country: '', role: '', logo: '', url: '' })" add-label="Ajouter un partenaire" empty-text="Aucun partenaire." item-label="le partenaire">
                                    <template #default="{ item }">
                                        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_10rem]">
                                            <div class="grid content-start gap-3 sm:grid-cols-2">
                                                <input v-model="item.name" :class="cn(inputClass, 'sm:col-span-2')" placeholder="Nom de l’établissement" aria-label="Nom du partenaire">
                                                <input v-model="item.country" :class="inputClass" placeholder="Pays" aria-label="Pays">
                                                <input v-model="item.role" :class="inputClass" placeholder="Rôle (chef de file…)" aria-label="Rôle">
                                                <input v-model="item.url" type="url" :class="cn(inputClass, 'sm:col-span-2')" placeholder="https://site-du-partenaire" aria-label="Site du partenaire">
                                            </div>
                                            <div class="min-w-0"><ImageField v-model="item.logo" label="Logo" aspect="3 / 2" hint="" /></div>
                                        </div>
                                    </template>
                                </RepeatableList>
                                <p v-if="listError('partners')" class="text-xs font-medium text-destructive">{{ listError('partners') }}</p>
                            </div>

                            <div class="grid gap-2">
                                <h3 class="text-sm font-semibold">Lots de travail</h3>
                                <RepeatableList v-model="form.work_packages" :blank="() => ({ code: `WP${form.work_packages.length + 1}`, title: '', description: '', lead: '' })" add-label="Ajouter un lot" empty-text="Aucun lot de travail." item-label="le lot">
                                    <template #default="{ item }">
                                        <div class="grid gap-3 sm:grid-cols-[6rem_minmax(0,1fr)]">
                                            <input v-model="item.code" :class="inputClass" placeholder="WP1" aria-label="Code">
                                            <input v-model="item.title" :class="inputClass" placeholder="Titre du lot" aria-label="Titre du lot">
                                            <textarea v-model="item.description" rows="2" :class="cn(textareaClass, 'sm:col-span-2')" placeholder="Description" aria-label="Description du lot"></textarea>
                                            <input v-model="item.lead" :class="cn(inputClass, 'sm:col-span-2')" placeholder="Pilote : …, co-pilote : …" aria-label="Pilotage">
                                        </div>
                                    </template>
                                </RepeatableList>
                                <p v-if="listError('work_packages')" class="text-xs font-medium text-destructive">{{ listError('work_packages') }}</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_12rem]">
                                <Field v-slot="{ id }" label="Mention de financement" optional hint="Ex. avertissement obligatoire des projets européens." :error="form.errors.funding_notice">
                                    <textarea :id="id" v-model="form.funding_notice" rows="4" :class="textareaClass"></textarea>
                                </Field>
                                <div class="min-w-0"><ImageField v-model="form.funding_logo" label="Logo du financeur" aspect="4 / 1" hint="" /></div>
                            </div>
                            <ImageField v-model="form.gallery_image" label="Image complémentaire" aspect="16 / 9" hint="Affichée à côté de la description (schéma, affiche…)." />
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-4">
                    <section :class="card">
                        <h2 class="mb-4 text-base font-semibold">Visibilité</h2>
                        <div class="grid gap-4">
                            <Field v-slot="{ id }" label="Statut"><Select :id="id" v-model="form.status" :options="statusOptions" /></Field>
                            <Switch v-model="form.is_featured" label="Mettre en avant" description="Signalé sur les cartes et prioritaire sur l’accueil." />
                            <Field v-slot="{ id, describedBy }" label="Ordre d’affichage" optional hint="Plus petit = plus haut." :error="form.errors.position">
                                <input :id="id" v-model.number="form.position" type="number" min="0" :class="inputClass" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Adresse (slug)" optional :error="form.errors.slug">
                                <input :id="id" v-model="form.slug" :class="inputClass" placeholder="genere-automatiquement" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>
                    <section :class="card"><ImageField v-model="form.cover_image" label="Image de couverture" aspect="16 / 10" :error="form.errors.cover_image" /></section>
                    <section :class="card"><ImageField v-model="form.logo" compact label="Logo du projet" aspect="1 / 1" hint="PNG à fond transparent de préférence." :error="form.errors.logo" /></section>
                    <section v-if="!isNew" :class="cn(card, 'grid gap-2 border-laterite-200 dark:border-laterite-900')">
                        <h2 class="text-base font-semibold">Supprimer</h2>
                        <p class="text-sm text-muted-foreground">Repassez en brouillon pour masquer le projet sans le perdre.</p>
                        <button type="button" :class="cn(buttonVariants({ variant: 'destructive-ghost', size: 'sm' }), 'justify-self-start')" @click="confirmDelete = true"><Trash2 aria-hidden="true" />Supprimer le projet</button>
                    </section>
                </aside>
            </div>
        </form>

        <ConfirmDialog v-model:open="confirmDelete" title="Supprimer ce projet ?" description="Il sera définitivement retiré du site." :loading="deleting" @confirm="destroy" />
    </AdminLayout>
</template>
