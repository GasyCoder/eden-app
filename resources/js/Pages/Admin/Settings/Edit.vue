<script setup>
import { useForm } from '@inertiajs/vue3';
import { Image, Palette, Phone, Search, Share2, Users } from '@lucide/vue';
import { computed } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import EditorAccounts from '@/Components/Admin/Ui/EditorAccounts.vue';
import ImageField from '@/Components/Admin/Ui/ImageField.vue';
import RepeatableList from '@/Components/Admin/Ui/RepeatableList.vue';
import Field from '@/Components/ui/Field.vue';
import Select from '@/Components/ui/Select.vue';
import { useUnsavedGuard } from '@/Composables/useUnsavedGuard';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass, textareaClass } from '@/lib/variants';

const props = defineProps({
    settings: { type: Object, required: true },
    socials: { type: Array, default: () => [] },
    networks: { type: Array, default: () => [] },
    theme: { type: Object, default: () => ({ sections: {}, colors: {} }) },
    defaults: { type: Object, default: () => ({}) },
    editors: { type: Array, default: () => [] },
});

const form = useForm({
    ...props.settings,
    socials: props.socials.map((social) => ({ ...social })),
    colors: Object.fromEntries(Object.keys(props.theme.sections).map((key) => [key, props.theme.colors?.[key] ?? ''])),
});

useUnsavedGuard(form);

const nav = [
    { id: 'identite', label: 'Identité', icon: Image },
    { id: 'referencement', label: 'Référencement', icon: Search },
    { id: 'contact', label: 'Contact', icon: Phone },
    { id: 'reseaux', label: 'Réseaux sociaux', icon: Share2 },
    { id: 'apparence', label: 'Apparence', icon: Palette },
    { id: 'utilisateurs', label: 'Comptes éditeurs', icon: Users },
];
const robotsOptions = [
    { value: 'index', label: 'Visible par les moteurs de recherche' },
    { value: 'noindex', label: 'Masqué (préproduction, maintenance)' },
];
const networkOptions = computed(() => [...new Set([...props.networks, ...form.socials.map((social) => social.title).filter(Boolean)])].map((name) => ({ value: name, label: name })));
const titlePreview = computed(() => form.seo_title || props.defaults.title);
const descriptionPreview = computed(() => form.seo_description || props.defaults.description);
const host = typeof window === 'undefined' ? 'eden.mahajanga-univ.mg' : window.location.host;
const listError = (prefix) => Object.entries(form.errors).find(([key]) => key.startsWith(`${prefix}.`))?.[1];
const submit = () => form.put('/admin/settings', { preserveScroll: true, onSuccess: () => form.defaults() });
const card = 'scroll-mt-20 rounded-xl border bg-card p-5 text-card-foreground';
</script>

<template>
    <AdminLayout title="Paramètres du site">
        <div class="grid gap-4">
            <AdminPageHeader title="Paramètres du site" description="Identité, référencement, coordonnées, réseaux sociaux, couleurs et comptes éditeurs. Votre mot de passe se change dans Mon profil.">
                <template #actions>
                    <button type="submit" form="site-settings" :class="buttonVariants()" :disabled="form.processing || !form.isDirty">
                        <span v-if="form.processing" class="inline-block size-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        Enregistrer
                    </button>
                </template>
            </AdminPageHeader>

            <div v-if="form.hasErrors" class="rounded-lg border border-laterite-200 bg-laterite-50 p-4 text-sm text-laterite-700 dark:border-laterite-900 dark:bg-laterite-950 dark:text-laterite-300" role="alert">Certains champs sont à corriger : ils sont signalés ci-dessous.</div>

            <div class="grid items-start gap-6 lg:grid-cols-[12rem_minmax(0,1fr)]">
                <nav class="top-20 hidden gap-0.5 lg:sticky lg:grid" aria-label="Sections des paramètres">
                    <a v-for="item in nav" :key="item.id" :href="`#${item.id}`" class="flex h-9 items-center gap-2.5 rounded-md px-2.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground">
                        <component :is="item.icon" class="size-4" aria-hidden="true" />{{ item.label }}
                    </a>
                </nav>

                <div class="grid min-w-0 gap-4">
                    <form id="site-settings" class="grid gap-4" novalidate @submit.prevent="submit">
                    <section id="identite" :class="card">
                        <div class="mb-5 grid gap-1"><h2 class="text-base font-semibold">Identité</h2><p class="text-sm text-muted-foreground">Nom et logo affichés dans l’en-tête, le pied de page et les emails.</p></div>
                        <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                            <Field v-slot="{ id, describedBy }" label="Nom du site" optional hint="Par défaut : EDEN." :error="form.errors.site_name">
                                <input :id="id" v-model="form.site_name" :class="inputClass" placeholder="EDEN" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Sous-titre" optional hint="Sous le nom, dans l’en-tête." :error="form.errors.site_caption">
                                <input :id="id" v-model="form.site_caption" :class="inputClass" placeholder="École Doctorale Écosystèmes Naturels" :aria-describedby="describedBy">
                            </Field>
                            <ImageField v-model="form.logo" compact label="Logo" aspect="1 / 1" hint="PNG carré à fond transparent, 512 px conseillés." :error="form.errors.logo" />
                            <ImageField v-model="form.favicon" compact label="Favicon et icône d’application" aspect="1 / 1" hint="Carré, 512 px conseillés : sert aussi d’icône quand le site est installé sur un téléphone." :error="form.errors.favicon" />
                        </div>
                    </section>

                    <section id="referencement" :class="card">
                        <div class="mb-5 grid gap-1"><h2 class="text-base font-semibold">Référencement (SEO)</h2><p class="text-sm text-muted-foreground">Titre et description par défaut dans Google et lors des partages ; les actualités et événements ont les leurs.</p></div>
                        <div class="mb-5 rounded-lg bg-muted p-4" aria-label="Aperçu dans un moteur de recherche">
                            <p class="truncate text-xs text-muted-foreground">{{ host }}</p>
                            <p class="mt-1 truncate text-lg text-[#1a0dab] dark:text-sky-300">{{ titlePreview }}</p>
                            <p class="mt-1 line-clamp-2 text-sm text-muted-foreground">{{ descriptionPreview }}</p>
                        </div>
                        <div class="grid gap-4">
                            <Field v-slot="{ id, describedBy }" label="Titre du site" optional :hint="`${form.seo_title?.length ?? 0} caractères, 60 conseillés.`" :error="form.errors.seo_title">
                                <input :id="id" v-model="form.seo_title" :class="inputClass" :placeholder="defaults.title" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Description" optional :hint="`${form.seo_description?.length ?? 0} caractères, 160 conseillés.`" :error="form.errors.seo_description">
                                <textarea :id="id" v-model="form.seo_description" rows="3" :class="textareaClass" :placeholder="defaults.description" :aria-describedby="describedBy"></textarea>
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Mots-clés" optional hint="Séparés par des virgules." :error="form.errors.seo_keywords">
                                <input :id="id" v-model="form.seo_keywords" :class="inputClass" :placeholder="defaults.keywords" :aria-describedby="describedBy">
                            </Field>
                            <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                                <ImageField v-model="form.og_image" label="Image de partage" aspect="1200 / 630" hint="1200 × 630 px : affichée sur Facebook, WhatsApp, LinkedIn…" :error="form.errors.og_image" />
                                <div class="grid content-start gap-4">
                                    <Field v-slot="{ id }" label="Indexation"><Select :id="id" v-model="form.robots" :options="robotsOptions" /></Field>
                                    <Field v-slot="{ id, invalid, describedBy }" label="Google Search Console" optional hint="Valeur « content » de la balise de vérification." :error="form.errors.google_verification">
                                        <input :id="id" v-model="form.google_verification" :class="inputClass" placeholder="abc123XYZ…" :aria-invalid="invalid" :aria-describedby="describedBy">
                                    </Field>
                                    <Field v-slot="{ id, invalid, describedBy }" label="Google Analytics 4" optional hint="Chargé seulement si le visiteur accepte les cookies." :error="form.errors.analytics_id">
                                        <input :id="id" v-model="form.analytics_id" :class="inputClass" placeholder="G-XXXXXXXXXX" :aria-invalid="invalid" :aria-describedby="describedBy">
                                    </Field>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="contact" :class="card">
                        <div class="mb-5 grid gap-1"><h2 class="text-base font-semibold">Coordonnées</h2><p class="text-sm text-muted-foreground">Section Contact, barre d’annonces et pied de page.</p></div>
                        <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                            <Field v-slot="{ id, invalid, describedBy }" label="Email" optional :error="form.errors.contact_email">
                                <input :id="id" v-model="form.contact_email" type="email" :class="inputClass" placeholder="contact@eden.univ-mahajanga.mg" :aria-invalid="invalid" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Téléphone" optional :error="form.errors.contact_phone">
                                <input :id="id" v-model="form.contact_phone" type="tel" :class="inputClass" placeholder="+261 …" :aria-describedby="describedBy">
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Adresse" optional :error="form.errors.contact_address">
                                <textarea :id="id" v-model="form.contact_address" rows="3" :class="textareaClass" :aria-describedby="describedBy"></textarea>
                            </Field>
                            <Field v-slot="{ id, describedBy }" label="Site de l’université" optional :error="form.errors.contact_website">
                                <input :id="id" v-model="form.contact_website" :class="inputClass" placeholder="www.univ-mahajanga.mg" :aria-describedby="describedBy">
                            </Field>
                        </div>
                    </section>

                    <section id="reseaux" :class="card">
                        <div class="mb-5 grid gap-1"><h2 class="text-base font-semibold">Réseaux sociaux</h2><p class="text-sm text-muted-foreground">Icônes du pied de page, identiques en français et en anglais.</p></div>
                        <RepeatableList v-model="form.socials" :blank="() => ({ title: networks.find((name) => !form.socials.some((social) => social.title === name)) ?? 'Facebook', link_url: '' })" add-label="Ajouter un réseau" empty-text="Aucun réseau social." item-label="le réseau" inline>
                            <template #default="{ item }">
                                <div class="grid gap-2 sm:grid-cols-[11rem_minmax(0,1fr)]">
                                    <Select v-model="item.title" :options="networkOptions" aria-label="Réseau" />
                                    <input v-model="item.link_url" type="url" :class="inputClass" placeholder="https://www.facebook.com/…" aria-label="Adresse de la page">
                                </div>
                            </template>
                        </RepeatableList>
                        <p v-if="listError('socials')" class="mt-2 text-xs font-medium text-destructive">{{ listError('socials') }}</p>
                    </section>

                    <section id="apparence" :class="card">
                        <div class="mb-5 grid gap-1"><h2 class="text-base font-semibold">Couleurs de fond des sections</h2><p class="text-sm text-muted-foreground">Laissez vide pour la couleur par défaut, aux couleurs du logo.</p></div>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <div v-for="(label, key) in theme.sections" :key="key" class="flex items-center gap-3 rounded-lg border p-2.5">
                                <label :class="cn('relative size-9 shrink-0 cursor-pointer overflow-hidden rounded-md border focus-within:ring-[3px] focus-within:ring-ring/50', !form.colors[key] && 'border-dashed bg-[repeating-linear-gradient(45deg,var(--muted),var(--muted)_4px,var(--background)_4px,var(--background)_8px)]')" :style="form.colors[key] ? { backgroundColor: form.colors[key] } : undefined">
                                    <input v-model="form.colors[key]" type="color" class="absolute inset-0 size-full cursor-pointer opacity-0" :aria-label="`Couleur : ${label}`">
                                </label>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium">{{ label }}</span>
                                    <span class="text-xs text-muted-foreground tabular-nums">{{ form.colors[key] || 'Par défaut' }}</span>
                                </span>
                                <button v-if="form.colors[key]" type="button" :class="cn(buttonVariants({ variant: 'ghost', size: 'sm' }), 'text-xs')" @click="form.colors[key] = ''">Réinitialiser</button>
                            </div>
                        </div>
                    </section>
                    <div class="flex justify-end">
                        <button type="submit" :class="buttonVariants()" :disabled="form.processing || !form.isDirty">Enregistrer les paramètres</button>
                    </div>
                    </form>

                    <EditorAccounts :editors="editors" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
