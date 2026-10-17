<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, Eye, EyeOff, ImagePlus, Link2, Link2Off, Pencil, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import BulkActionBar from '@/Components/Admin/Ui/BulkActionBar.vue';
import BulkCheckbox from '@/Components/Admin/Ui/BulkCheckbox.vue';
import LocaleTabs from '@/Components/Admin/Ui/LocaleTabs.vue';
import ActionMenu from '@/Components/ui/ActionMenu.vue';
import Badge from '@/Components/ui/Badge.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Field from '@/Components/ui/Field.vue';
import { useBulkSelection } from '@/Composables/useBulkSelection';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass, textareaClass } from '@/lib/variants';

const props = defineProps({
    texts: { type: Object, required: true },
    urls: { type: Object, required: true },
    slides: { type: Array, default: () => [] },
});

const locale = ref('fr');
const form = useForm({
    ...Object.fromEntries(Object.entries(props.texts).map(([key, value]) => [key, { ...value }])),
    ...props.urls,
});
const complete = computed(() => ({ fr: Boolean(form.title.fr), en: Boolean(form.title.en) }));
const err = (field) => form.errors[`${field}.${locale.value}`] || (locale.value === 'en' ? '' : form.errors[`${field}.fr`]);
const saveTexts = () => form.put('/admin/hero/texts', { preserveScroll: true, onSuccess: () => form.defaults() });

const linkLabels = { none: 'Sans lien', post: 'Actualité', event: 'Événement', announcement: 'Annonce', project: 'Projet', journal_issue: 'Numéro de revue', url: 'Lien libre' };
const stateLabels = { live: ['En ligne', 'success'], scheduled: ['Programmée', 'warning'], expired: ['Expirée', 'secondary'], hidden: ['Masquée', 'secondary'] };
const pendingDelete = ref(null);
const deleting = ref(false);
const bulk = useBulkSelection(() => props.slides);
const bulkActions = [
    { action: 'show', label: 'Afficher', icon: Eye },
    { action: 'hide', label: 'Masquer', icon: EyeOff },
    { action: 'delete', label: 'Supprimer', icon: Trash2, danger: true, confirm: { title: 'Supprimer les diapositives sélectionnées ?', description: (n) => `${n} diapositive(s) seront retirées du diaporama. Pour les garder de côté, masquez-les.` } },
];

const move = (index, delta) => {
    const ids = props.slides.map((slide) => slide.id);
    const [id] = ids.splice(index, 1);
    ids.splice(index + delta, 0, id);
    router.patch('/admin/hero/reorder', { ids }, { preserveScroll: true });
};

const toggle = (slide) => router.post('/admin/hero/bulk', { action: slide.is_active ? 'hide' : 'show', ids: [slide.id] }, { preserveScroll: true });
const actions = (slide) => [
    { label: 'Modifier', icon: Pencil, href: `/admin/hero/slides/${slide.id}/edit` },
    { label: slide.is_active ? 'Masquer' : 'Afficher', icon: slide.is_active ? EyeOff : Eye, onSelect: () => toggle(slide) },
    { label: 'Supprimer', icon: Trash2, danger: true, separator: true, onSelect: () => { pendingDelete.value = slide; } },
];
const confirmDelete = () => router.delete(`/admin/hero/slides/${pendingDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; pendingDelete.value = null; },
});
const card = 'rounded-xl border bg-card p-5 text-card-foreground';
</script>

<template>
    <AdminLayout title="Héro de l’accueil">
        <AdminPageHeader title="Héro de l’accueil" description="Le grand bandeau en haut de l’accueil : titre, boutons et diaporama. Chaque diapositive peut renvoyer vers une actualité, un événement, une annonce, un projet ou un numéro de revue.">
            <template #actions>
                <a href="/" target="_blank" rel="noopener" :class="buttonVariants({ variant: 'outline' })">Voir l’accueil</a>
                <Link href="/admin/hero/slides/create" :class="buttonVariants()"><ImagePlus aria-hidden="true" />Ajouter une diapositive</Link>
            </template>
        </AdminPageHeader>

        <div class="grid min-w-0 grid-cols-1 items-start gap-5 2xl:grid-cols-[minmax(0,1fr)_26rem]">
            <section :class="cn(card, 'min-w-0')" aria-labelledby="slides-title">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="grid gap-1">
                        <h2 id="slides-title" class="text-base font-semibold">Diaporama</h2>
                        <p class="text-sm text-muted-foreground">Ordre d’affichage de haut en bas. Les diapositives masquées, programmées ou expirées ne sont pas visibles.</p>
                    </div>
                    <label v-if="slides.length" class="flex items-center gap-2 text-sm text-muted-foreground">
                        <BulkCheckbox :checked="bulk.allSelected" :indeterminate="bulk.someSelected" @change="bulk.toggleAll()" />Tout sélectionner
                    </label>
                </div>

                <ol v-if="slides.length" class="grid min-w-0 grid-cols-1 gap-2">
                    <li v-for="(slide, index) in slides" :key="slide.id" :class="cn('flex min-w-0 items-center gap-3 rounded-lg border p-2 pr-2 transition-colors sm:pr-3', bulk.isSelected(slide.id) && 'border-eden-300 bg-eden-50/60 dark:border-eden-800 dark:bg-eden-950/40', !slide.is_active && 'opacity-60')">
                        <BulkCheckbox class="ml-1" :checked="bulk.isSelected(slide.id)" :aria-label="`Sélectionner ${slide.title || 'la diapositive'}`" @change="bulk.toggle(slide.id)" />
                        <span class="w-5 shrink-0 text-center text-xs font-semibold tabular-nums text-muted-foreground max-sm:hidden">{{ index + 1 }}</span>
                        <img v-if="slide.image" :src="slide.image" alt="" class="aspect-[4/3] w-20 shrink-0 rounded-md object-cover sm:w-28">
                        <span v-else class="grid aspect-[4/3] w-20 shrink-0 place-items-center rounded-md bg-secondary sm:w-28"><ImagePlus class="size-5 text-muted-foreground" aria-hidden="true" /></span>
                        <div class="grid min-w-0 flex-1 gap-1">
                            <Link :href="`/admin/hero/slides/${slide.id}/edit`" class="truncate text-sm font-medium hover:underline hover:underline-offset-4">{{ slide.title || 'Sans légende' }}</Link>
                            <span v-if="slide.subtitle" class="truncate text-xs text-muted-foreground">{{ slide.subtitle }}</span>
                            <span class="flex min-w-0 flex-wrap items-center gap-1.5 text-xs">
                                <Badge :variant="stateLabels[slide.state][1]" dot>{{ stateLabels[slide.state][0] }}</Badge>
                                <span :class="cn('inline-flex max-w-full min-w-0 items-center gap-1', slide.link_ok ? 'text-muted-foreground' : 'text-destructive')" :title="slide.link_target || undefined">
                                    <component :is="slide.link_type === 'none' ? Link2Off : Link2" class="size-3.5 shrink-0" aria-hidden="true" />
                                    <span class="truncate">{{ linkLabels[slide.link_type] }}<template v-if="slide.link_target && slide.link_type !== 'none'"> : {{ slide.link_target }}</template><template v-if="!slide.link_ok"> (non publié, lien masqué)</template></span>
                                </span>
                                <span v-if="slide.starts_at || slide.ends_at" class="text-muted-foreground">· {{ slide.starts_at ? `du ${formatDate(slide.starts_at, 'fr', { day: 'numeric', month: 'short' })}` : '' }} {{ slide.ends_at ? `au ${formatDate(slide.ends_at, 'fr', { day: 'numeric', month: 'short' })}` : '' }}</span>
                            </span>
                        </div>
                        <div class="flex shrink-0 items-center">
                            <button type="button" :class="cn(buttonVariants({ variant: 'ghost', size: 'icon-sm' }), 'max-sm:hidden')" :disabled="index === 0" :aria-label="`Monter la diapositive ${index + 1}`" @click="move(index, -1)"><ChevronUp aria-hidden="true" /></button>
                            <button type="button" :class="cn(buttonVariants({ variant: 'ghost', size: 'icon-sm' }), 'max-sm:hidden')" :disabled="index === slides.length - 1" :aria-label="`Descendre la diapositive ${index + 1}`" @click="move(index, 1)"><ChevronDown aria-hidden="true" /></button>
                            <ActionMenu :items="actions(slide)" :label="`Actions pour la diapositive ${index + 1}`" />
                        </div>
                    </li>
                </ol>
                <EmptyState v-else :icon="ImagePlus" title="Aucune diapositive" description="Ajoutez des photos de terrain, d’événements ou d’actualités pour animer l’accueil.">
                    <Link href="/admin/hero/slides/create" :class="buttonVariants({ size: 'sm' })">Ajouter une diapositive</Link>
                </EmptyState>
            </section>

            <form :class="cn(card, 'min-w-0')" novalidate @submit.prevent="saveTexts">
                <div class="mb-4 grid gap-1"><h2 class="text-base font-semibold">Textes et boutons</h2><p class="text-sm text-muted-foreground">Aussi modifiables directement sur l’accueil.</p></div>
                <LocaleTabs v-model="locale" :complete="complete" />
                <div class="grid items-start gap-4 md:grid-cols-2 2xl:grid-cols-1">
                    <div class="grid gap-4">
                    <Field v-slot="{ id, invalid }" label="Titre" :error="err('title')"><textarea :id="id" v-model="form.title[locale]" rows="2" :class="textareaClass" :aria-invalid="invalid"></textarea></Field>
                    <Field v-slot="{ id }" label="Fin du titre, en vert" optional :error="err('title_highlight')"><input :id="id" v-model="form.title_highlight[locale]" :class="inputClass"></Field>
                    <Field v-slot="{ id }" label="Texte d’introduction" optional :error="err('subtitle')"><textarea :id="id" v-model="form.subtitle[locale]" rows="3" :class="textareaClass"></textarea></Field>
                    </div>
                    <div class="grid gap-4">
                    <div class="grid gap-3 rounded-lg bg-muted p-3">
                        <Field v-slot="{ id }" label="Bouton principal" :error="err('button_primary_label')"><input :id="id" v-model="form.button_primary_label[locale]" :class="inputClass" placeholder="Découvrir l’EDEN"></Field>
                        <Field v-slot="{ id, invalid }" label="Lien" :error="form.errors.button_primary_url"><input :id="id" v-model="form.button_primary_url" :class="inputClass" placeholder="/a-propos ou #about" :aria-invalid="invalid"></Field>
                    </div>
                    <div class="grid gap-3 rounded-lg bg-muted p-3">
                        <Field v-slot="{ id }" label="Bouton secondaire" :error="err('button_secondary_label')"><input :id="id" v-model="form.button_secondary_label[locale]" :class="inputClass" placeholder="Candidater"></Field>
                        <Field v-slot="{ id, invalid }" label="Lien" :error="form.errors.button_secondary_url"><input :id="id" v-model="form.button_secondary_url" :class="inputClass" placeholder="/formation" :aria-invalid="invalid"></Field>
                    </div>
                    </div>
                    <button type="submit" :class="cn(buttonVariants(), 'justify-self-end md:col-span-2 2xl:col-span-1')" :disabled="form.processing || !form.isDirty">Enregistrer les textes</button>
                </div>
            </form>
        </div>

        <BulkActionBar :selection="bulk" url="/admin/hero/bulk" :actions="bulkActions" :noun="['diapositive', 'diapositives']" />
        <ConfirmDialog
            :open="Boolean(pendingDelete)"
            title="Supprimer cette diapositive ?"
            description="Elle sera retirée du diaporama. Pour la garder de côté, masquez-la plutôt."
            :loading="deleting"
            @update:open="(value) => { if (!value) pendingDelete = null; }"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
