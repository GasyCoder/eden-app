<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ExternalLink, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import NotifySubscribersField from '@/Components/Admin/Ui/NotifySubscribersField.vue';
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
import { buttonVariants, inputClass, textareaClass } from '@/lib/variants';
import { cn } from '@/lib/utils';

const props = defineProps({
    event: { type: Object, required: true },
    publicUrl: { type: String, default: null },
    newsletter: { type: Object, default: () => ({ subscribers: 0, campaign: null }) },
});

const isNew = computed(() => !props.event.id);
const locale = ref('fr');
const form = useForm({
    title: { ...props.event.title },
    summary: { ...props.event.summary },
    body: { ...props.event.body },
    slug: props.event.slug ?? '',
    cover_image: props.event.cover_image,
    starts_at: props.event.starts_at,
    ends_at: props.event.ends_at,
    is_all_day: props.event.is_all_day,
    location: props.event.location,
    format: props.event.format,
    registration_url: props.event.registration_url,
    organizer: props.event.organizer,
    status: props.event.status,
    is_featured: props.event.is_featured,
    notify_subscribers: false,
});

useUnsavedGuard(form);

const statusOptions = [{ value: 'draft', label: 'Brouillon' }, { value: 'published', label: 'Publié' }, { value: 'cancelled', label: 'Annulé (reste visible)' }];
const formatOptions = [{ value: 'in_person', label: 'Présentiel' }, { value: 'online', label: 'En ligne' }, { value: 'hybrid', label: 'Hybride' }];
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
        form.post('/admin/events', options);
    } else {
        form.put(`/admin/events/${props.event.id}`, options);
    }
};

const destroy = () => router.delete(`/admin/events/${props.event.id}`, {
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; },
});
</script>

<template>
    <AdminLayout :title="isNew ? 'Nouvel événement' : 'Modifier l’événement'">
        <form class="grid gap-4" novalidate @submit.prevent="submit()">
            <AdminPageHeader :title="isNew ? 'Nouvel événement' : (event.title.fr || 'Événement')" back-href="/admin/events" back-label="Événements">
                <template #badges><StatusBadge v-if="!isNew" :status="event.status" /></template>
                <template #actions>
                    <a v-if="publicUrl" :href="publicUrl" target="_blank" rel="noopener noreferrer" :class="buttonVariants({ variant: 'ghost' })"><ExternalLink aria-hidden="true" />{{ event.status === 'draft' ? 'Aperçu' : 'Voir' }}</a>
                    <button v-if="form.status === 'draft'" type="button" :class="buttonVariants({ variant: 'outline' })" :disabled="form.processing" @click="submit('draft')">Enregistrer le brouillon</button>
                    <button type="button" :class="buttonVariants()" :disabled="form.processing" @click="submit(form.status === 'draft' ? 'published' : null)">
                        <span v-if="form.processing" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        {{ form.status === 'draft' ? 'Publier' : 'Enregistrer' }}
                    </button>
                </template>
            </AdminPageHeader>

            <div v-if="form.hasErrors" class="flex items-start gap-3 rounded-lg border p-4 text-sm border-laterite-200 bg-laterite-50 text-laterite-700 dark:border-laterite-900 dark:bg-laterite-950 dark:text-laterite-300" role="alert">Certains champs sont à corriger : ils sont signalés ci-dessous.</div>

            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="grid min-w-0 gap-4">
                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="mb-4 grid gap-1">
                            <h2 class="text-base font-semibold">Description</h2>
                            <p class="text-sm text-muted-foreground">Le français est obligatoire ; l’anglais est facultatif.</p>
                        </div>
                        <LocaleTabs v-model="locale" :complete="complete" />
                        <div class="grid gap-4">
                            <Field v-slot="{ id, invalid, describedBy }" :label="locale === 'fr' ? 'Titre' : 'Title'" :error="err('title')">
                                <input :id="id" v-model="form.title[locale]" :class="cn(inputClass, 'h-11 text-base font-medium')" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Description courte" optional hint="Affichée sur les cartes et en introduction." :error="err('summary')">
                                <textarea :id="id" v-model="form.summary[locale]" :class="textareaClass" rows="3" :aria-describedby="describedBy"></textarea>
                            </Field>
                            <Field v-slot="{ id }" label="Description détaillée" optional :error="err('body')">
                                <RichTextEditor :id="id" :key="locale" v-model="form.body[locale]" :label="`Description détaillée (${locale.toUpperCase()})`" />
                            </Field>
                        </div>
                    </section>

                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="mb-4 grid gap-1">
                            <h2 class="text-base font-semibold">Date et lieu</h2>
                            <p class="text-sm text-muted-foreground">Heures de Madagascar (UTC+3).</p>
                        </div>
                        <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                            <Field v-slot="{ id, invalid, describedBy }" label="Début" :error="form.errors.starts_at">
                                <input :id="id" v-model="form.starts_at" type="datetime-local" :class="inputClass" required :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Fin" optional :error="form.errors.ends_at">
                                <input :id="id" v-model="form.ends_at" type="datetime-local" :class="inputClass" :min="form.starts_at || undefined" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <div class="sm:col-span-2"><Switch v-model="form.is_all_day" label="Toute la journée" description="Les horaires ne sont pas affichés." /></div>
                            <Field v-slot="{ id }" label="Format">
                                <Select :id="id" v-model="form.format" :options="formatOptions" />
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Lieu" optional :hint="form.format === 'online' ? 'Facultatif pour un événement en ligne.' : ''" :error="form.errors.location">
                                <input :id="id" v-model="form.location" :class="inputClass" placeholder="Amphithéâtre A, Université de Mahajanga" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Lien d’inscription ou de connexion" optional :error="form.errors.registration_url" wide>
                                <input :id="id" v-model="form.registration_url" type="url" :class="inputClass" placeholder="https://…" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Organisateur" optional :error="form.errors.organizer" wide>
                                <input :id="id" v-model="form.organizer" :class="inputClass" placeholder="École Doctorale EDEN" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-4 [&_.card-title]:mb-4">
                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <h2 class="mb-4 text-base font-semibold">Publication</h2>
                        <div class="grid gap-4">
                            <Field v-slot="{ id }" label="Statut" hint="« Annulé » garde l’événement visible avec la mention Annulé.">
                                <Select :id="id" v-model="form.status" :options="statusOptions" />
                            </Field>
                            <Switch v-model="form.is_featured" label="Mettre en avant" />
                            <Field v-slot="{ id, invalid, describedBy }" label="Adresse (slug)" optional :error="form.errors.slug">
                                <input :id="id" v-model="form.slug" :class="inputClass" placeholder="genere-automatiquement" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>

                    <NotifySubscribersField v-model="form.notify_subscribers" :newsletter="newsletter" :published="form.status === 'published'" noun="l’événement" />
                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <ImageField v-model="form.cover_image" label="Image" :error="form.errors.cover_image" />
                    </section>
                    <section v-if="!isNew" class="rounded-xl border bg-card p-5 text-card-foreground border-laterite-200 dark:border-laterite-900">
                        <h2 class="text-base font-semibold">Supprimer</h2>
                        <p class="text-sm text-muted-foreground">Préférez « Annulé » pour informer les participants.</p>
                        <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'sm' })" @click="confirmDelete = true"><Trash2 aria-hidden="true" />Supprimer l’événement</button>
                    </section>
                </aside>
            </div>
        </form>

        <ConfirmDialog v-model:open="confirmDelete" title="Supprimer cet événement ?" description="Il sera définitivement retiré du site." :loading="deleting" @confirm="destroy" />
    </AdminLayout>
</template>
