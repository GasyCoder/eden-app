<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import BrandStripe from '@/Components/Layout/BrandStripe.vue';
import { cn } from '@/lib/utils';
import { buttonVariants, containerClass } from '@/lib/variants';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import AddItemButton from '@/Components/Editable/AddItemButton.vue';
import EditField from '@/Components/Editable/EditField.vue';
import ItemEditor from '@/Components/Editable/ItemEditor.vue';
import ItemTools from '@/Components/Editable/ItemTools.vue';
import { useContentItemEditor } from '@/Composables/useContentItemEditor';

// Seul un superadmin peut éditer les mentions légales du footer (copyright + crédit)
const page = usePage();
const canEditProtected = computed(() => Boolean(page.props.canEditProtected));
const newsletterForm = useForm({
    email: '',
});

const props = defineProps({
    site: {
        type: Object,
        default: () => ({}),
    },
    blocks: {
        type: Object,
        default: () => ({}),
    },
    navigationLinks: {
        type: Array,
        default: () => [],
    },
    researchLinks: {
        type: Array,
        default: () => [],
    },
    informationLinks: {
        type: Array,
        default: () => [],
    },
    socialLinks: {
        type: Array,
        default: () => [],
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    logoVariants: {
        type: Object,
        default: () => ({}),
    },
    locale: {
        type: String,
        default: 'fr',
    },
    homeHref: {
        type: String,
        default: '#',
    },
});

const t = computed(() => (props.locale === 'en'
    ? {
        newsletterTitle: 'Stay informed',
        newsletterText: 'Receive news from the Doctoral School by email.',
        email: 'Your email address',
        subscribe: 'Subscribe',
        sending: 'Sending…',
        socials: 'Social networks',
    }
    : {
        newsletterTitle: 'Restez informé',
        newsletterText: 'Recevez les actualités de l’École Doctorale par e-mail.',
        email: 'Votre adresse e-mail',
        subscribe: 'S’inscrire',
        sending: 'Envoi…',
        socials: 'Réseaux sociaux',
    }));

const fallbackNavigationLinks = [
    { title: 'À propos', link_url: '#about' },
    { title: 'Mission & Vision', link_url: '#mission' },
    { title: 'Organisation', link_url: '#organisation' },
    { title: 'Formation', link_url: '#formation' },
];

const fallbackResearchLinks = [
    { title: 'Thématiques EAD 1', link_url: '#recherche' },
    { title: 'Thématiques EAD 2', link_url: '#recherche' },
    { title: 'Projets en cours', link_url: '#projets' },
    { title: 'Partenariats', link_url: '#partenaires' },
];

const fallbackInformationLinks = [
    { title: 'Nous contacter', link_url: '#contact' },
    { title: 'Actualités', link_url: '#' },
    { title: 'Bibliothèque numérique', link_url: '#' },
    { title: 'Espace doctorant', link_url: '#' },
];

const navigationForDisplay = () => props.navigationLinks?.length ? props.navigationLinks : fallbackNavigationLinks;
const researchForDisplay = () => props.researchLinks?.length ? props.researchLinks : fallbackResearchLinks;
const informationForDisplay = () => props.informationLinks?.length ? props.informationLinks : fallbackInformationLinks;

const linkPayload = (form) => ({ title: form.title, link_url: form.link_url });

const navigationEditor = useContentItemEditor(
    'footer_navigation',
    () => ({ title: '', link_url: '#' }),
    (item) => ({ title: item.title ?? '', link_url: item.link_url ?? '#' }),
    linkPayload,
);

const researchEditor = useContentItemEditor(
    'footer_research',
    () => ({ title: '', link_url: '#' }),
    (item) => ({ title: item.title ?? '', link_url: item.link_url ?? '#' }),
    linkPayload,
);

const informationEditor = useContentItemEditor(
    'footer_information',
    () => ({ title: '', link_url: '#' }),
    (item) => ({ title: item.title ?? '', link_url: item.link_url ?? '#' }),
    linkPayload,
);

// Réseaux sociaux (collection éditable)
const fallbackSocialLinks = [
    { title: 'Facebook', link_url: '#' },
];

const socialsForDisplay = () => props.socialLinks?.length ? props.socialLinks : fallbackSocialLinks;

const socialEditor = useContentItemEditor(
    'footer_socials',
    () => ({ title: '', link_url: 'https://' }),
    (item) => ({ title: item.title ?? '', link_url: item.link_url ?? 'https://' }),
    linkPayload,
);

const columns = [
    { id: 'navigation', titleKey: 'navigation_title', titleFallback: 'Navigation', items: navigationForDisplay, editor: navigationEditor },
    { id: 'research', titleKey: 'research_title', titleFallback: 'Recherche', items: researchForDisplay, editor: researchEditor },
    { id: 'information', titleKey: 'information_title', titleFallback: 'Informations', items: informationForDisplay, editor: informationEditor },
];

const submitNewsletter = () => {
    newsletterForm.post('/newsletter', {
        preserveScroll: true,
    });
};

// Icônes de marque (simple-icons, chemin unique). Repli : icône lien générique.
const brandPaths = {
    facebook: 'M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.628-5.373-12-12-12s-12 5.372-12 12c0 5.628 3.874 10.35 9.101 11.647Z',
    instagram: 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069ZM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0Zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881Z',
    twitter: 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.451-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z',
    linkedin: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286ZM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065Zm1.782 13.019H3.555V9h3.564v11.452ZM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0Z',
    youtube: 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814ZM9.545 15.568V8.432L15.818 12l-6.273 3.568Z',
    whatsapp: 'M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.449L.057 24Zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.516 5.26l-.999 3.648 3.742-.981Zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.611-.916-2.206-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01a1.093 1.093 0 0 0-.793.372c-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.414Z',
};

const iconKey = (name = '') => {
    const n = name.toLowerCase();
    if (n.includes('facebook')) return 'facebook';
    if (n.includes('insta')) return 'instagram';
    if (n.includes('linkedin')) return 'linkedin';
    if (n.includes('youtube') || n.includes('youtu.be')) return 'youtube';
    if (n.includes('whatsapp')) return 'whatsapp';
    if (n.includes('twitter') || n === 'x' || n.includes(' x') || n.includes('x ')) return 'twitter';
    return 'link';
};
</script>

<template>
    <footer class="bg-[var(--bg-footer,var(--color-eden-950))] text-sm text-eden-100/75">
        <div :class="cn(containerClass, 'pt-16 pb-8')">
            <div class="grid gap-12 lg:grid-cols-[1.1fr_2fr]">
                <div class="grid max-w-sm content-start gap-5">
                    <a :href="homeHref" class="flex items-center gap-3">
                        <span :class="cn('grid size-12 shrink-0 place-items-center rounded-lg', site?.logo ? 'bg-white p-0.5' : 'bg-eden-600 text-white')">
                            <picture v-if="site?.logo" class="size-full">
                                <source v-for="source in logoVariants?.sources ?? []" :key="source.type" :type="source.type" :srcset="source.srcset" :sizes="logoVariants?.sizes">
                                <img :src="site.logo" alt="Logo EDEN" :width="logoVariants?.width ?? 56" :height="logoVariants?.height ?? 56" class="size-full object-contain" loading="lazy" decoding="async">
                            </picture>
                            <svg v-else class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9" /><path d="M12 12.5C12 8.5 9 6.2 4.5 6.2c0 4 3 6.3 7.5 6.3Z" /><path d="M12 10c0-3.6 3-5.8 7.5-5.8 0 3.6-3 5.8-7.5 5.8Z" /></svg>
                        </span>
                        <span class="grid leading-tight">
                            <!-- Nom et légende se modifient depuis l'en-tête -->
                            <span class="text-base font-bold text-white">{{ site?.name || 'EDEN' }}</span>
                            <span class="text-xs text-eden-100/70">{{ site?.caption || 'Université de Mahajanga' }}</span>
                        </span>
                    </a>
                    <p class="leading-relaxed">
                        <EditableTextarea
                            :value="blocks?.description"
                            fallback="École Doctorale Écosystèmes Naturels : former les chercheurs de demain pour la préservation des écosystèmes malgaches et le développement durable."
                            content-key="footer.description"
                            :can-edit="canEdit"
                        />
                    </p>

                    <ul class="flex flex-wrap items-center gap-2" :aria-label="t.socials">
                        <li v-for="(social, index) in socialsForDisplay()" :key="social.id ?? social.title" :class="{ 'basis-full': socialEditor.editingId.value === social.id }">
                            <ItemEditor
                                v-if="socialEditor.editingId.value === social.id"
                                title="Modifier le réseau social"
                                compact
                                deletable
                                :busy="socialEditor.saving.value"
                                @save="socialEditor.saveItem(social)"
                                @cancel="socialEditor.cancelEdit"
                                @delete="socialEditor.deleteItem(social)"
                            >
                                <EditField v-model="socialEditor.form.value.title" label="Nom" placeholder="Facebook, LinkedIn, YouTube…" hint="L’icône est choisie d’après ce nom." />
                                <EditField v-model="socialEditor.form.value.link_url" label="Adresse de la page" placeholder="https://…" />
                            </ItemEditor>
                            <span v-else class="inline-flex items-center gap-1">
                                <a :href="social.link_url ?? '#'" class="grid size-9 place-items-center rounded-md border border-white/15 text-white transition-colors hover:border-gold-400 hover:text-gold-300" target="_blank" rel="noopener noreferrer" :title="social.title" :aria-label="social.title">
                                    <svg v-if="iconKey(social.title) !== 'link'" class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path :d="brandPaths[iconKey(social.title)]" /></svg>
                                    <svg v-else class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M3 12h18" /><path d="M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18Z" /></svg>
                                </a>
                                <ItemTools
                                    v-if="canEdit && social.id"
                                    :first="index === 0"
                                    :last="index === socialsForDisplay().length - 1"
                                    @edit="socialEditor.editItem(social)"
                                    @up="socialEditor.reorderItems(socialsForDisplay(), index, -1)"
                                    @down="socialEditor.reorderItems(socialsForDisplay(), index, 1)"
                                />
                            </span>
                        </li>
                        <li v-if="canEdit && !socialEditor.creating.value">
                            <AddItemButton compact on-dark label="Ajouter un réseau social" @click="socialEditor.startCreate" />
                        </li>
                    </ul>
                    <ItemEditor
                        v-if="socialEditor.creating.value"
                        title="Nouveau réseau social"
                        compact
                        :busy="socialEditor.saving.value"
                        @save="socialEditor.createItem"
                        @cancel="socialEditor.cancelEdit"
                    >
                        <EditField v-model="socialEditor.form.value.title" label="Nom" placeholder="Facebook, LinkedIn, YouTube…" hint="L’icône est choisie d’après ce nom." />
                        <EditField v-model="socialEditor.form.value.link_url" label="Adresse de la page" placeholder="https://…" />
                    </ItemEditor>
                </div>

                <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
                    <nav v-for="col in columns" :key="col.id" class="last:col-span-2 sm:last:col-span-1" :aria-labelledby="`footer-${col.id}`">
                        <h2 :id="`footer-${col.id}`" class="mb-4 flex items-center gap-2 text-sm font-semibold text-white">
                            <EditableText :value="blocks?.[col.titleKey]" :fallback="col.titleFallback" :content-key="`footer.${col.titleKey}`" :can-edit="canEdit" />
                            <AddItemButton v-if="canEdit && !col.editor.creating.value" compact on-dark class="ml-auto" label="Ajouter un lien" @click="col.editor.startCreate" />
                        </h2>
                        <ItemEditor
                            v-if="col.editor.creating.value"
                            class="mb-3"
                            title="Nouveau lien"
                            compact
                            :busy="col.editor.saving.value"
                            @save="col.editor.createItem"
                            @cancel="col.editor.cancelEdit"
                        >
                            <EditField v-model="col.editor.form.value.title" label="Libellé" />
                            <EditField v-model="col.editor.form.value.link_url" label="Lien" placeholder="#contact ou https://…" />
                        </ItemEditor>
                        <ul class="grid gap-2.5">
                            <li v-for="(link, index) in col.items()" :key="link.id ?? link.title" class="flex flex-wrap items-center gap-2">
                                <ItemEditor
                                    v-if="col.editor.editingId.value === link.id"
                                    title="Modifier le lien"
                                    compact
                                    deletable
                                    :busy="col.editor.saving.value"
                                    @save="col.editor.saveItem(link)"
                                    @cancel="col.editor.cancelEdit"
                                    @delete="col.editor.deleteItem(link)"
                                >
                                    <EditField v-model="col.editor.form.value.title" label="Libellé" />
                                    <EditField v-model="col.editor.form.value.link_url" label="Lien" placeholder="#contact ou https://…" />
                                </ItemEditor>
                                <template v-else>
                                    <a :href="link.link_url ?? '#'" class="transition-colors hover:text-white">{{ link.title }}</a>
                                    <ItemTools
                                        v-if="canEdit && link.id"
                                        :first="index === 0"
                                        :last="index === col.items().length - 1"
                                        @edit="col.editor.editItem(link)"
                                        @up="col.editor.reorderItems(col.items(), index, -1)"
                                        @down="col.editor.reorderItems(col.items(), index, 1)"
                                    />
                                </template>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <div class="mt-12 grid items-center gap-5 rounded-xl border border-white/10 bg-white/[0.04] p-6 lg:grid-cols-2 lg:gap-10">
                <div>
                    <h2 class="text-base font-semibold text-white">{{ t.newsletterTitle }}</h2>
                    <p class="mt-1">{{ t.newsletterText }}</p>
                </div>
                <form class="grid gap-2" novalidate @submit.prevent="submitNewsletter">
                    <label for="newsletter-email" class="sr-only">{{ t.email }}</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input
                            id="newsletter-email"
                            v-model="newsletterForm.email"
                            class="h-10 flex-1 rounded-md border border-white/20 bg-white/5 px-3 text-sm text-white outline-none placeholder:text-eden-100/50 focus-visible:border-gold-400 focus-visible:ring-[3px] focus-visible:ring-gold-400/30 aria-invalid:border-laterite-400"
                            type="email"
                            autocomplete="email"
                            :placeholder="t.email"
                            required
                            :aria-invalid="newsletterForm.errors.email ? 'true' : null"
                            aria-describedby="newsletter-error"
                        >
                        <button type="submit" :class="buttonVariants({ variant: 'inverse' })" :disabled="newsletterForm.processing">
                            <span v-if="newsletterForm.processing" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                            {{ newsletterForm.processing ? t.sending : t.subscribe }}
                        </button>
                    </div>
                    <span v-if="newsletterForm.errors.email" id="newsletter-error" class="text-xs font-medium text-laterite-300" role="alert">{{ newsletterForm.errors.email }}</span>
                </form>
            </div>

            <div class="mt-10 flex flex-wrap justify-between gap-x-6 gap-y-2 border-t border-white/10 pt-6 text-xs text-eden-100/60">
                <p><EditableText :value="blocks?.copyright" fallback="© 2025 EDEN-UMG, École Doctorale Écosystèmes Naturels, Université de Mahajanga. Tous droits réservés." content-key="footer.copyright" :can-edit="canEdit && canEditProtected" /></p>
                <p><EditableText :value="blocks?.credit" fallback="Conçu pour l'excellence académique" content-key="footer.credit" :can-edit="canEdit && canEditProtected" /></p>
            </div>
        </div>
        <BrandStripe />
    </footer>
</template>
