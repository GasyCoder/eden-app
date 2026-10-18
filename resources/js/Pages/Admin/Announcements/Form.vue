<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { CalendarDays, Info, Megaphone, OctagonAlert, PanelTop, SquareStack, Trash2, Wrench, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import ImageField from '@/Components/Admin/Ui/ImageField.vue';
import LocaleTabs from '@/Components/Admin/Ui/LocaleTabs.vue';
import StatusBadge from '@/Components/Admin/Ui/StatusBadge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Field from '@/Components/ui/Field.vue';
import Select from '@/Components/ui/Select.vue';
import { useUnsavedGuard } from '@/Composables/useUnsavedGuard';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useT } from '@/lib/i18n';
import { buttonVariants, inputClass, textareaClass } from '@/lib/variants';

const props = defineProps({
    announcement: { type: Object, required: true },
    displayByType: { type: Object, required: true },
    state: { type: String, default: null },
});

const isNew = computed(() => !props.announcement.id);
const { t } = useT();
const locale = ref('fr');
const form = useForm({
    title: { ...props.announcement.title },
    message: { ...props.announcement.message },
    cta_label: { ...props.announcement.cta_label },
    cta_url: props.announcement.cta_url,
    image: props.announcement.image,
    type: props.announcement.type,
    status: props.announcement.status,
    priority: props.announcement.priority,
    starts_at: props.announcement.starts_at,
    ends_at: props.announcement.ends_at,
});

useUnsavedGuard(form);

const types = [
    { value: 'info', label: 'Information', icon: Info, help: 'Information générale, sans urgence.' },
    { value: 'event', label: 'Événement', icon: CalendarDays, help: 'Mise en avant d’un événement ou d’une inscription.' },
    { value: 'maintenance', label: 'Maintenance', icon: Wrench, help: 'Interruption de service prévue.' },
    { value: 'important', label: 'Important', icon: Megaphone, help: 'Information à ne pas manquer (fermeture, échéance…).' },
    { value: 'urgent', label: 'Urgence', icon: OctagonAlert, help: 'Situation exceptionnelle : réaffichée à chaque nouvelle visite.' },
];
const statusOptions = [{ value: 'draft', label: 'Brouillon' }, { value: 'published', label: 'Publiée' }, { value: 'archived', label: 'Archivée' }];
const priorityOptions = [{ value: 0, label: 'Basse' }, { value: 1, label: 'Normale' }, { value: 2, label: 'Haute' }];
const display = computed(() => props.displayByType[form.type] ?? 'banner');
const currentType = computed(() => types.find((type) => type.value === form.type));
const complete = computed(() => ({ fr: Boolean(form.title.fr), en: Boolean(form.title.en) }));
const err = (field) => form.errors[`${field}.${locale.value}`] || (locale.value === 'en' ? '' : form.errors[`${field}.fr`]);
const preview = computed(() => ({
    title: form.title[locale.value] || form.title.fr || 'Titre de l’annonce',
    message: form.message[locale.value] || form.message.fr,
    cta: form.cta_url ? (form.cta_label[locale.value] || form.cta_label.fr || (locale.value === 'en' ? 'Learn more' : 'En savoir plus')) : null,
}));
const confirmDelete = ref(false);
const deleting = ref(false);

const submit = () => {
    const options = { preserveScroll: true, onSuccess: () => form.defaults(), onError: (errors) => { if (errors['title.fr']) locale.value = 'fr'; } };

    if (isNew.value) {
        form.post('/admin/announcements', options);
    } else {
        form.put(`/admin/announcements/${props.announcement.id}`, options);
    }
};

const destroy = () => router.delete(`/admin/announcements/${props.announcement.id}`, {
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; },
});
</script>

<template>
    <AdminLayout :title="isNew ? 'Nouvelle annonce' : 'Modifier l’annonce'">
        <form class="grid gap-4" novalidate @submit.prevent="submit">
            <AdminPageHeader :title="isNew ? 'Nouvelle annonce' : (announcement.title.fr || 'Annonce')" back-href="/admin/announcements" back-label="Annonces">
                <template #badges><StatusBadge v-if="state" :status="state" /></template>
                <template #actions>
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
                        <div class="mb-4 grid gap-1">
                            <h2 class="text-base font-semibold">Type d’annonce</h2>
                            <p class="text-sm text-muted-foreground">La présentation est choisie automatiquement : les messages critiques ouvrent une fenêtre, les autres un bandeau discret.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3" role="radiogroup" aria-label="Type d’annonce">
                            <label v-for="type in types" :key="type.value" class="flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-muted has-[:checked]:border-eden-600 has-[:checked]:bg-eden-50 has-[:focus-visible]:ring-[3px] has-[:focus-visible]:ring-ring/50 dark:has-[:checked]:bg-eden-950 [&>svg]:size-[18px] [&>svg]:shrink-0 [&>svg]:text-muted-foreground has-[:checked]:[&>svg]:text-eden-600">
                                <input v-model="form.type" type="radio" name="type" :value="type.value" class="sr-only">
                                <component :is="type.icon" aria-hidden="true" />
                                <span class="grid leading-tight [&_small]:text-xs [&_small]:text-muted-foreground [&_strong]:text-sm [&_strong]:font-medium">
                                    <strong>{{ type.label }}</strong>
                                    <small>{{ displayByType[type.value] === 'dialog' ? 'Fenêtre' : 'Bandeau' }}</small>
                                </span>
                            </label>
                        </div>
                        <p class="text-xs text-muted-foreground mt-3">{{ currentType?.help }}</p>
                    </section>

                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="mb-4 grid gap-1"><h2 class="text-base font-semibold">Message</h2></div>
                        <LocaleTabs v-model="locale" :complete="complete" />
                        <div class="grid gap-4">
                            <Field v-slot="{ id, invalid, describedBy }" :label="locale === 'fr' ? 'Titre' : 'Title'" :hint="`${(form.title[locale] || '').length} / 160`" :error="err('title')">
                                <input :id="id" v-model="form.title[locale]" :class="inputClass" maxlength="160" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Message" optional :hint="display === 'banner' ? 'Restez bref : le bandeau tient sur une ligne.' : 'Reprenez les informations essentielles de l’affiche (date, lieu, objet) pour qu’elles soient lisibles sans l’image.'" :error="err('message')">
                                <textarea :id="id" v-model="form.message[locale]" :class="textareaClass" rows="3" maxlength="1000" :aria-describedby="describedBy"></textarea>
                            </Field>
                            <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                                <Field v-slot="{ id, describedBy }" label="Texte du bouton" optional :error="err('cta_label')">
                                    <input :id="id" v-model="form.cta_label[locale]" :class="inputClass" maxlength="60" :placeholder="locale === 'fr' ? 'En savoir plus' : 'Learn more'" :aria-describedby="describedBy">
                                </Field>
                                <Field v-slot="{ id, invalid, describedBy }" label="Lien du bouton" optional hint="https://…, /page ou #section. Sans lien, pas de bouton." :error="form.errors.cta_url">
                                    <input :id="id" v-model="form.cta_url" :class="inputClass" inputmode="url" :aria-invalid="invalid" :aria-describedby="describedBy">
                                </Field>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="mb-4 grid gap-1">
                            <h2 class="text-base font-semibold">Aperçu</h2>
                            <p class="text-sm text-muted-foreground">{{ display === 'dialog' ? 'Fenêtre ouverte une seule fois, quelques secondes après l’arrivée du visiteur.' : 'Bandeau affiché en haut de toutes les pages jusqu’à sa fermeture.' }}</p>
                        </div>
                        <div class="rounded-lg border border-dashed border-input bg-muted p-4" aria-hidden="true">
                            <div v-if="display === 'banner'" class="rounded-md border bg-background text-sm" :class="`is-${form.type}`">
                                <div class="flex min-h-11 items-center gap-3 px-3 py-2 [&_p]:flex-1 [&_span]:ml-1.5 [&_span]:text-muted-foreground [&_strong]:font-semibold">
                                    <component :is="currentType?.icon" class="size-4 shrink-0 text-muted-foreground" />
                                    <p><strong>{{ preview.title }}</strong><span v-if="preview.message">{{ preview.message }}</span><a v-if="preview.cta" class="ml-2 font-semibold underline underline-offset-4">{{ preview.cta }}</a></p>
                                    <X class="size-4 text-muted-foreground" />
                                </div>
                            </div>
                            <div v-else class="mx-auto overflow-hidden rounded-xl border bg-popover shadow-lg" :class="(preview.message?.length ?? 0) > 240 && form.image ? '' : 'max-w-xl'">
                                <div class="flex items-start gap-3 px-5 pt-5">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300 [&_svg]:size-5"><component :is="currentType?.icon" /></span>
                                    <p class="min-w-0 flex-1 py-1.5 text-lg leading-snug font-semibold break-words">{{ preview.title }}</p>
                                    <X class="mt-2 size-5 shrink-0 text-muted-foreground" />
                                </div>
                                <div class="grid min-w-0 gap-5 px-5 py-4" :class="form.image && (preview.message?.length ?? 0) > 240 ? 'xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]' : ''">
                                    <figure v-if="form.image" class="grid min-w-0 gap-2">
                                        <div class="rounded-lg border bg-muted/60 p-2"><img :src="form.image" alt="" class="block max-h-96 w-full object-contain"></div>
                                        <span class="text-sm font-medium text-eden-700 dark:text-eden-300">{{ t('viewAnnouncementPoster') }} ↗</span>
                                    </figure>
                                    <p v-if="preview.message" class="min-w-0 text-sm leading-7 break-words whitespace-pre-line text-muted-foreground">{{ preview.message }}</p>
                                </div>
                                <div class="flex flex-wrap justify-end gap-2 border-t bg-muted/50 px-6 py-4"><span :class="buttonVariants({ variant: 'outline', size: 'sm' })">Fermer</span><span v-if="preview.cta" :class="buttonVariants({ size: 'sm' })">{{ preview.cta }}</span></div>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-4 [&_.card-title]:mb-4">
                    <section class="rounded-xl border bg-card p-5 text-card-foreground">
                        <h2 class="mb-4 text-base font-semibold">Diffusion</h2>
                        <div class="grid gap-4">
                            <Field v-slot="{ id }" label="Statut"><Select :id="id" v-model="form.status" :options="statusOptions" /></Field>
                            <Field v-slot="{ id }" label="Priorité" hint="Si plusieurs annonces sont en ligne, la plus prioritaire s’affiche en premier.">
                                <Select :id="id" v-model="form.priority" :options="priorityOptions" />
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Début" optional hint="Vide = immédiatement. Heure de Madagascar." :error="form.errors.starts_at">
                                <input :id="id" v-model="form.starts_at" type="datetime-local" :class="inputClass" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, invalid, describedBy }" label="Fin" optional hint="Vide = sans limite." :error="form.errors.ends_at">
                                <input :id="id" v-model="form.ends_at" type="datetime-local" :class="inputClass" :min="form.starts_at || undefined" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>
                    <section v-if="display === 'dialog'" class="rounded-xl border bg-card p-5 text-card-foreground">
                        <ImageField v-model="form.image" label="Affiche ou image (facultative)" compact hint="L’image est affichée entière, sans recadrage. Une affiche verticale est acceptée et peut être ouverte en grand." :error="form.errors.image" />
                    </section>
                    <section class="rounded-xl border p-5 text-card-foreground grid gap-3 bg-muted [&_p]:flex [&_p]:gap-2 [&_p]:text-xs [&_p]:leading-relaxed [&_svg]:mt-0.5 [&_svg]:size-4 [&_svg]:shrink-0">
                        <p><PanelTop aria-hidden="true" /> Un seul bandeau et une seule fenêtre à la fois : les plus prioritaires.</p>
                        <p><SquareStack aria-hidden="true" /> Une annonce fermée par un visiteur ne lui est plus réaffichée, sauf si vous la modifiez.</p>
                    </section>
                    <section v-if="!isNew" class="rounded-xl border bg-card p-5 text-card-foreground border-laterite-200 dark:border-laterite-900">
                        <h2 class="text-base font-semibold">Supprimer</h2>
                        <p class="text-sm text-muted-foreground">Pour arrêter la diffusion en gardant l’annonce, choisissez « Archivée ».</p>
                        <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'sm' })" @click="confirmDelete = true"><Trash2 aria-hidden="true" />Supprimer l’annonce</button>
                    </section>
                </aside>
            </div>
        </form>

        <ConfirmDialog v-model:open="confirmDelete" title="Supprimer cette annonce ?" description="Elle sera placée dans la corbeille." :loading="deleting" @confirm="destroy" />
    </AdminLayout>
</template>
