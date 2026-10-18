<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ExternalLink, Plus, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import NotifySubscribersField from '@/Components/Admin/Ui/NotifySubscribersField.vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import CreatePostCategoryDialog from '@/Components/Admin/Ui/CreatePostCategoryDialog.vue';
import ImageField from '@/Components/Admin/Ui/ImageField.vue';
import LocaleTabs from '@/Components/Admin/Ui/LocaleTabs.vue';
import RichTextEditor from '@/Components/Admin/Ui/RichTextEditor.vue';
import StatusBadge from '@/Components/Admin/Ui/StatusBadge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Button from '@/Components/ui/Button.vue';
import Field from '@/Components/ui/Field.vue';
import Select from '@/Components/ui/Select.vue';
import Switch from '@/Components/ui/Switch.vue';
import { useUnsavedGuard } from '@/Composables/useUnsavedGuard';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { buttonVariants, inputClass, textareaClass } from '@/lib/variants';
import { cn } from '@/lib/utils';

const props = defineProps({
    post: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    publicUrl: { type: String, default: null },
    newsletter: { type: Object, default: () => ({ subscribers: 0, campaign: null }) },
});

const isNew = computed(() => !props.post.id);
const locale = ref('fr');
const form = useForm({
    title: { ...props.post.title },
    excerpt: { ...props.post.excerpt },
    body: { ...props.post.body },
    seo_title: { ...props.post.seo_title },
    meta_description: { ...props.post.meta_description },
    slug: props.post.slug ?? '',
    cover_image: props.post.cover_image,
    post_category_id: props.post.post_category_id,
    author_name: props.post.author_name,
    status: props.post.status,
    published_at: props.post.published_at,
    is_featured: props.post.is_featured,
    notify_subscribers: false,
});

useUnsavedGuard(form);

const categoryDialogOpen = ref(false);
const addedCategories = ref([]);
const categoryOptions = computed(() => [{ value: null, label: 'Sans catégorie' }, ...[...props.categories, ...addedCategories.value.filter((category) => !props.categories.some((existing) => existing.id === category.id))].map((c) => ({ value: c.id, label: c.name }))]);
const categoryCreated = (category) => {
    addedCategories.value.push(category);
    form.post_category_id = category.id;
};
const statusOptions = [{ value: 'draft', label: 'Brouillon' }, { value: 'published', label: 'Publié' }, { value: 'archived', label: 'Archivé' }];
const saveLabel = computed(() => {
    if (form.status === 'archived') return props.post.status === 'archived' ? 'Enregistrer' : 'Archiver';
    return form.status === 'published' && !isNew.value ? 'Mettre à jour' : 'Publier';
});
const complete = computed(() => ({ fr: Boolean(form.title.fr), en: Boolean(form.title.en) }));
const err = (field) => form.errors[`${field}.${locale.value}`] || (locale.value === 'en' ? '' : form.errors[`${field}.fr`]);
const confirmDelete = ref(false);
const deleting = ref(false);

const submit = (status = null) => {
    if (status) {
        form.status = status;
    }

    const options = { preserveScroll: true, onSuccess: () => form.defaults(), onError: (errors) => { if (errors['title.fr']) locale.value = 'fr'; } };

    if (isNew.value) {
        form.post('/admin/posts', options);
    } else {
        form.put(`/admin/posts/${props.post.id}`, options);
    }
};

const destroy = () => router.delete(`/admin/posts/${props.post.id}`, {
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; },
});
</script>

<template>
    <AdminLayout :title="isNew ? 'Nouvelle actualité' : 'Modifier l’actualité'">
        <form class="grid gap-4" novalidate @submit.prevent="submit()">
            <AdminPageHeader :title="isNew ? 'Nouvelle actualité' : (post.title.fr || 'Actualité')" back-href="/admin/posts" back-label="Actualités">
                <template #badges><StatusBadge v-if="!isNew" :status="post.status" /></template>
                <template #actions>
                    <a v-if="publicUrl" :href="publicUrl" target="_blank" rel="noopener noreferrer" :class="buttonVariants({ variant: 'ghost' })"><ExternalLink aria-hidden="true" />{{ post.status === 'draft' ? 'Aperçu' : 'Voir' }}</a>
                    <button v-if="form.status === 'draft'" type="button" :class="buttonVariants({ variant: 'outline' })" :disabled="form.processing" @click="submit('draft')">Enregistrer le brouillon</button>
                    <button type="button" :class="buttonVariants()" :disabled="form.processing" @click="submit(form.status === 'draft' ? 'published' : null)">
                        <span v-if="form.processing" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        {{ saveLabel }}
                    </button>
                </template>
            </AdminPageHeader>

            <div v-if="form.hasErrors" class="flex items-start gap-3 rounded-lg border p-4 text-sm border-laterite-200 bg-laterite-50 text-laterite-700 dark:border-laterite-900 dark:bg-laterite-950 dark:text-laterite-300" role="alert">Certains champs sont à corriger : ils sont signalés ci-dessous.</div>

            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="grid min-w-0 gap-4">
                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div class="mb-4 grid gap-1">
                                <h2 class="text-base font-semibold">Contenu</h2>
                                <p class="text-sm text-muted-foreground">Le français est obligatoire ; sans traduction, la version anglaise du site affiche le français.</p>
                            </div>
                        </div>
                        <LocaleTabs v-model="locale" :complete="complete" />
                        <div class="grid gap-4">
                            <Field v-slot="{ id, invalid, describedBy }" :label="locale === 'fr' ? 'Titre' : 'Title'" :error="err('title')">
                                <input :id="id" v-model="form.title[locale]" :class="cn(inputClass, 'h-11 text-base font-medium')" :aria-invalid="invalid" :aria-describedby="describedBy" :required="locale === 'fr'">
                            </Field>
                            <Field v-slot="{ id, describedBy }" :label="locale === 'fr' ? 'Chapô' : 'Summary'" optional hint="Une ou deux phrases affichées sous le titre et sur les cartes." :error="err('excerpt')">
                                <textarea :id="id" v-model="form.excerpt[locale]" :class="textareaClass" rows="3" :aria-describedby="describedBy"></textarea>
                            </Field>
                            <Field v-slot="{ id }" :label="locale === 'fr' ? 'Article' : 'Article'" :error="err('body')">
                                <RichTextEditor :id="id" :key="locale" v-model="form.body[locale]" :label="`Article (${locale.toUpperCase()})`" />
                            </Field>
                        </div>
                    </section>

                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="mb-4 grid gap-1">
                            <h2 class="text-base font-semibold">Référencement</h2>
                            <p class="text-sm text-muted-foreground">Facultatif : par défaut, le titre et le chapô sont utilisés.</p>
                        </div>
                        <div class="grid gap-4">
                            <Field v-slot="{ id, describedBy }" :label="`Titre SEO (${locale.toUpperCase()})`" optional :hint="`${(form.seo_title[locale] || '').length} / 70 caractères`" :error="err('seo_title')">
                                <input :id="id" v-model="form.seo_title[locale]" :class="inputClass" maxlength="70" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" :label="`Meta description (${locale.toUpperCase()})`" optional :hint="`${(form.meta_description[locale] || '').length} / 170 caractères`" :error="err('meta_description')">
                                <textarea :id="id" v-model="form.meta_description[locale]" :class="textareaClass" rows="2" maxlength="170" :aria-describedby="describedBy"></textarea>
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Adresse (slug)" optional hint="Généré à partir du titre français si vide. Évitez de le modifier après publication." :error="form.errors.slug">
                                <div class="flex [&>input]:rounded-l-none [&>span]:flex [&>span]:items-center [&>span]:rounded-l-md [&>span]:border [&>span]:border-r-0 [&>span]:border-input [&>span]:bg-muted [&>span]:px-3 [&>span]:text-sm [&>span]:whitespace-nowrap [&>span]:text-muted-foreground"><span>/actualites/</span><input :id="id" v-model="form.slug" :class="inputClass" :aria-invalid="invalid" :aria-describedby="describedBy" placeholder="genere-automatiquement"></div>
                            </Field>
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-4 [&_.card-title]:mb-4">
                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <h2 class="mb-4 text-base font-semibold">Publication</h2>
                        <div class="grid gap-4">
                            <Field v-slot="{ id }" label="Statut" hint="Une actualité archivée reste consultable sur le site avec le badge « Archivé ». Elle n’est plus mise en avant sur l’accueil.">
                                <Select :id="id" v-model="form.status" :options="statusOptions" />
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Date de publication" optional hint="Heure de Madagascar. Une date future programme la publication." :error="form.errors.published_at">
                                <input :id="id" v-model="form.published_at" type="datetime-local" :class="inputClass" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Switch v-model="form.is_featured" :disabled="form.status === 'archived'" label="Mettre à la une" :description="form.status === 'archived' ? 'Les actualités archivées ne sont plus mises en avant.' : 'Affichée en grand sur la page Actualités et en premier sur l’accueil.'" />
                        </div>
                    </section>

                    <NotifySubscribersField v-if="form.status !== 'archived'" v-model="form.notify_subscribers" :newsletter="newsletter" :published="form.status === 'published'" noun="l’actualité" />

                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <h2 class="mb-4 text-base font-semibold">Classement</h2>
                        <div class="grid gap-4">
                            <Field label="Catégorie" :error="form.errors.post_category_id">
                                <template #aside><Button variant="outline" size="icon-sm" title="Ajouter une catégorie" aria-label="Ajouter une catégorie sans quitter le formulaire" @click="categoryDialogOpen = true"><Plus aria-hidden="true" /></Button></template>
                                <template #default="{ id }"><Select :id="id" v-model="form.post_category_id" :options="categoryOptions" /></template>
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Auteur affiché" optional hint="Par défaut : votre nom." :error="form.errors.author_name">
                                <input :id="id" v-model="form.author_name" :class="inputClass" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>

                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <ImageField v-model="form.cover_image" label="Image de couverture" :error="form.errors.cover_image" />
                    </section>

                    <section v-if="!isNew" class="rounded-xl border bg-card p-5 text-card-foreground border-laterite-200 dark:border-laterite-900">
                        <h2 class="text-base font-semibold">Supprimer</h2>
                        <p class="text-sm text-muted-foreground">L’actualité disparaît du site. Vous pourrez la restaurer depuis la corbeille.</p>
                        <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'sm' })" @click="confirmDelete = true"><Trash2 aria-hidden="true" />Supprimer l’actualité</button>
                    </section>
                </aside>
            </div>
        </form>

        <CreatePostCategoryDialog v-model:open="categoryDialogOpen" @created="categoryCreated" />
        <ConfirmDialog v-model:open="confirmDelete" title="Supprimer cette actualité ?" description="Elle sera retirée du site et placée dans la corbeille." :loading="deleting" @confirm="destroy" />
    </AdminLayout>
</template>
