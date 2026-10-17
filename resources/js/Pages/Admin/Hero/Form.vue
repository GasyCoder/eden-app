<script setup>
import { useForm } from '@inertiajs/vue3';
import { ArrowRight, BookOpen, CalendarDays, FolderKanban, Link2, Link2Off, MapPin, Megaphone, Newspaper, Search } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import ImageField from '@/Components/Admin/Ui/ImageField.vue';
import LocaleTabs from '@/Components/Admin/Ui/LocaleTabs.vue';
import Field from '@/Components/ui/Field.vue';
import Select from '@/Components/ui/Select.vue';
import Switch from '@/Components/ui/Switch.vue';
import { useUnsavedGuard } from '@/Composables/useUnsavedGuard';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass } from '@/lib/variants';

const props = defineProps({
    slide: { type: Object, required: true },
    linkables: { type: Object, default: () => ({}) },
});

const isNew = computed(() => !props.slide.id);
const locale = ref('fr');
const form = useForm({
    image: props.slide.image,
    focal_point: props.slide.focal_point,
    title: { ...props.slide.title },
    subtitle: { ...props.slide.subtitle },
    cta_label: { ...props.slide.cta_label },
    link_type: props.slide.link_type,
    linkable_id: props.slide.linkable_id,
    url: props.slide.url,
    is_active: props.slide.is_active,
    starts_at: props.slide.starts_at,
    ends_at: props.slide.ends_at,
});

useUnsavedGuard(form);

const linkTypes = [
    { value: 'none', label: 'Sans lien', icon: Link2Off },
    { value: 'post', label: 'Actualité', icon: Newspaper },
    { value: 'event', label: 'Événement', icon: CalendarDays },
    { value: 'announcement', label: 'Annonce', icon: Megaphone },
    { value: 'project', label: 'Projet', icon: FolderKanban },
    { value: 'journal_issue', label: 'Numéro de revue', icon: BookOpen },
    { value: 'url', label: 'Lien libre', icon: Link2 },
];
const defaultCta = { post: 'Lire l’article', event: 'Voir l’événement', announcement: 'En savoir plus', project: 'Découvrir le projet', journal_issue: 'Lire ce numéro', url: 'En savoir plus' };
const focalOptions = [
    { value: 'center', label: 'Centre' }, { value: 'top', label: 'Haut' }, { value: 'bottom', label: 'Bas' }, { value: 'left', label: 'Gauche' }, { value: 'right', label: 'Droite' },
];
const focalClass = { center: 'object-center', top: 'object-top', bottom: 'object-bottom', left: 'object-left', right: 'object-right' };

const query = ref('');
const options = computed(() => props.linkables[form.link_type] ?? []);
const filtered = computed(() => {
    const term = query.value.trim().toLowerCase();

    return term ? options.value.filter((option) => `${option.label} ${option.meta ?? ''}`.toLowerCase().includes(term)) : options.value;
});
const selected = computed(() => options.value.find((option) => option.id === form.linkable_id) ?? null);

const chooseType = (value) => {
    form.link_type = value;
    form.linkable_id = null;
    query.value = '';
};

// Aperçu : les valeurs de la diapositive, complétées par le contenu relié
const preview = computed(() => ({
    image: form.image || selected.value?.image,
    title: form.title[locale.value] || form.title.fr || selected.value?.label || 'Légende de la diapositive',
    subtitle: form.subtitle[locale.value] || form.subtitle.fr,
    cta: form.link_type === 'none' ? null : (form.cta_label[locale.value] || form.cta_label.fr || defaultCta[form.link_type]),
}));

const submit = () => (isNew.value ? form.post('/admin/hero/slides') : form.put(`/admin/hero/slides/${props.slide.id}`));
const card = 'rounded-xl border bg-card p-5 text-card-foreground';
</script>

<template>
    <AdminLayout :title="isNew ? 'Nouvelle diapositive' : 'Modifier la diapositive'">
        <form class="grid gap-4" novalidate @submit.prevent="submit">
            <AdminPageHeader :title="isNew ? 'Nouvelle diapositive' : 'Modifier la diapositive'" back-href="/admin/hero" back-label="Héro de l’accueil">
                <template #actions>
                    <button type="submit" :class="buttonVariants()" :disabled="form.processing">
                        <span v-if="form.processing" class="inline-block size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        Enregistrer
                    </button>
                </template>
            </AdminPageHeader>

            <div v-if="form.hasErrors" class="rounded-lg border border-laterite-200 bg-laterite-50 p-4 text-sm text-laterite-700 dark:border-laterite-900 dark:bg-laterite-950 dark:text-laterite-300" role="alert">Certains champs sont à corriger : ils sont signalés ci-dessous.</div>

            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <div class="grid min-w-0 gap-4">
                    <section :class="card">
                        <div class="mb-4 grid gap-1"><h2 class="text-base font-semibold">Lien de la diapositive</h2><p class="text-sm text-muted-foreground">La photo et la légende deviennent cliquables, avec un bouton sur la diapositive.</p></div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4" role="radiogroup" aria-label="Type de lien">
                            <button
                                v-for="type in linkTypes"
                                :key="type.value"
                                type="button"
                                role="radio"
                                :aria-checked="form.link_type === type.value ? 'true' : 'false'"
                                :class="cn('flex items-center gap-2 rounded-lg border px-3 py-2.5 text-left text-sm font-medium transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none', form.link_type === type.value ? 'border-eden-600 bg-eden-50 text-eden-800 dark:bg-eden-950 dark:text-eden-200' : 'hover:bg-accent')"
                                @click="chooseType(type.value)"
                            >
                                <component :is="type.icon" class="size-4 shrink-0" aria-hidden="true" />{{ type.label }}
                            </button>
                        </div>

                        <div v-if="linkables[form.link_type]" class="mt-4 grid gap-2">
                            <div class="relative">
                                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                                <input v-model="query" type="search" :class="cn(inputClass, 'pl-9')" placeholder="Rechercher un contenu…" aria-label="Rechercher un contenu à relier">
                            </div>
                            <ul class="grid max-h-80 gap-1 overflow-y-auto rounded-lg border p-1" role="listbox" aria-label="Contenus disponibles">
                                <li v-for="option in filtered" :key="option.id">
                                    <button
                                        type="button"
                                        role="option"
                                        :aria-selected="form.linkable_id === option.id ? 'true' : 'false'"
                                        :class="cn('flex w-full items-center gap-3 rounded-md p-2 text-left text-sm transition-colors', form.linkable_id === option.id ? 'bg-eden-50 ring-1 ring-eden-600 dark:bg-eden-950' : 'hover:bg-accent')"
                                        @click="form.linkable_id = option.id"
                                    >
                                        <img v-if="option.image" :src="option.image" alt="" class="size-10 shrink-0 rounded object-cover">
                                        <span v-else class="size-10 shrink-0 rounded bg-secondary"></span>
                                        <span class="grid min-w-0 flex-1">
                                            <span class="truncate font-medium">{{ option.label }}</span>
                                            <span class="truncate text-xs text-muted-foreground">{{ option.meta }}</span>
                                        </span>
                                        <span v-if="!option.public" class="shrink-0 rounded bg-gold-100 px-1.5 py-0.5 text-[11px] font-medium text-gold-900 dark:bg-gold-950 dark:text-gold-200">Non publié</span>
                                    </button>
                                </li>
                                <li v-if="!filtered.length" class="p-4 text-center text-sm text-muted-foreground">Aucun contenu.</li>
                            </ul>
                            <p v-if="form.errors.linkable_id" class="text-xs font-medium text-destructive">{{ form.errors.linkable_id }}</p>
                            <p class="text-xs text-muted-foreground">Un contenu non publié peut être relié : le lien n’apparaîtra qu’à sa publication. Sans photo propre, la diapositive reprend l’image du contenu.</p>
                        </div>
                        <Field v-if="form.link_type === 'url'" v-slot="{ id, invalid }" class="mt-4" label="Adresse" :error="form.errors.url">
                            <input :id="id" v-model="form.url" :class="inputClass" placeholder="/formation ou https://…" :aria-invalid="invalid">
                        </Field>
                    </section>

                    <section :class="card">
                        <div class="mb-4 grid gap-1"><h2 class="text-base font-semibold">Légende</h2><p class="text-sm text-muted-foreground">Facultative si un contenu est relié : son titre est alors utilisé.</p></div>
                        <LocaleTabs v-model="locale" :complete="{ fr: Boolean(form.title.fr), en: Boolean(form.title.en) }" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <Field v-slot="{ id }" label="Légende" optional :error="form.errors[`title.${locale}`]" wide><input :id="id" v-model="form.title[locale]" :class="inputClass" :placeholder="selected?.label ?? 'Atelier de restauration, Mahajanga'"></Field>
                            <Field v-slot="{ id }" label="Lieu ou précision" optional :error="form.errors[`subtitle.${locale}`]"><input :id="id" v-model="form.subtitle[locale]" :class="inputClass" placeholder="Région Boeny, Madagascar"></Field>
                            <Field v-slot="{ id }" label="Texte du bouton" optional :error="form.errors[`cta_label.${locale}`]"><input :id="id" v-model="form.cta_label[locale]" :class="inputClass" :placeholder="defaultCta[form.link_type] ?? 'Aucun bouton'" :disabled="form.link_type === 'none'"></Field>
                        </div>
                    </section>

                    <section :class="card">
                        <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_12rem]">
                            <ImageField v-model="form.image" label="Photo" aspect="4 / 3" preset="hero.image" hint="Paysage, 1600 px de large conseillés." :error="form.errors.image" />
                            <div class="grid content-start gap-4">
                                <Field v-slot="{ id }" label="Cadrage" hint="Point gardé visible quand l’image est recadrée (mobile)."><Select :id="id" v-model="form.focal_point" :options="focalOptions" /></Field>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 gap-4 lg:sticky lg:top-20">
                    <section :class="card">
                        <h2 class="mb-3 text-base font-semibold">Aperçu</h2>
                        <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-secondary">
                            <img v-if="preview.image" :src="preview.image" alt="" :class="cn('size-full object-cover', focalClass[form.focal_point])">
                            <div class="absolute inset-x-0 bottom-0 h-3/5 bg-gradient-to-t from-black/75 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 grid gap-1 p-3 text-white">
                                <p class="text-sm font-semibold">{{ preview.title }}</p>
                                <p v-if="preview.subtitle" class="flex items-center gap-1 text-xs text-white/80"><MapPin class="size-3 text-gold-300" aria-hidden="true" />{{ preview.subtitle }}</p>
                                <span v-if="preview.cta" class="mt-1 inline-flex w-fit items-center gap-1 rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-eden-800">{{ preview.cta }}<ArrowRight class="size-3" aria-hidden="true" /></span>
                            </div>
                        </div>
                        <p class="mt-3 mb-2 text-xs font-medium text-muted-foreground">Sur mobile</p>
                        <div class="relative mx-auto aspect-[4/5] w-36 overflow-hidden rounded-xl bg-secondary">
                            <img v-if="preview.image" :src="preview.image" alt="" :class="cn('size-full object-cover', focalClass[form.focal_point])">
                        </div>
                    </section>
                    <section :class="card">
                        <h2 class="mb-4 text-base font-semibold">Diffusion</h2>
                        <div class="grid gap-4">
                            <Switch v-model="form.is_active" label="Afficher dans le diaporama" />
                            <Field v-slot="{ id, invalid }" label="À partir du" optional hint="Heure de Madagascar." :error="form.errors.starts_at"><input :id="id" v-model="form.starts_at" type="datetime-local" :class="inputClass" :aria-invalid="invalid"></Field>
                            <Field v-slot="{ id, invalid }" label="Jusqu’au" optional hint="Idéal pour un événement : la diapositive disparaît après." :error="form.errors.ends_at"><input :id="id" v-model="form.ends_at" type="datetime-local" :class="inputClass" :aria-invalid="invalid"></Field>
                        </div>
                    </section>
                </aside>
            </div>
        </form>
    </AdminLayout>
</template>
