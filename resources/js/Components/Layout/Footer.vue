<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { useContentItemEditor } from '@/Composables/useContentItemEditor';

// Seul un superadmin peut éditer les mentions légales du footer (copyright + crédit)
const page = usePage();
const canEditProtected = computed(() => Boolean(page.props.canEditProtected));

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
});

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
    <footer>
        <div class="container">
            <div class="ft-grid">
                <div class="ft-brand">
                    <a href="#" class="logo">
                        <div class="logo-mark" :class="{ 'has-logo': site?.logo }">
                            <img v-if="site?.logo" :src="site.logo" alt="Logo EDEN-UMG">
                            <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                                 stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 21V9" />
                                <path d="M12 12.5C12 8.5 9 6.2 4.5 6.2c0 4 3 6.3 7.5 6.3Z" />
                                <path d="M12 10c0-3.6 3-5.8 7.5-5.8 0 3.6-3 5.8-7.5 5.8Z" />
                            </svg>
                        </div>
                        <div class="logo-text">
                            <span class="logo-name"><EditableText :value="site?.name" fallback="EDEN-UMG" content-key="site.name" :can-edit="canEdit" /></span>
                            <span class="logo-caption"><EditableText :value="site?.caption" fallback="Université de Mahajanga" content-key="site.caption" :can-edit="canEdit" /></span>
                        </div>
                    </a>
                    <p>
                        <EditableTextarea
                            :value="blocks?.description"
                            fallback="École Doctorale Écosystèmes Naturels — Former les chercheurs de demain pour la préservation des écosystèmes malgaches et le développement durable."
                            content-key="footer.description"
                            :can-edit="canEdit"
                        />
                    </p>

                    <div class="ft-socials">
                        <template v-for="(social, index) in socialsForDisplay()" :key="social.id ?? social.title">
                            <span v-if="socialEditor.editingId.value === social.id" class="eden-project-edit">
                                <input v-model="socialEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Nom (ex. Facebook)">
                                <input v-model="socialEditor.form.value.link_url" class="eden-collection-input" type="text" placeholder="Lien (https://…)">
                                <span class="eden-collection-actions">
                                    <button type="button" @click="socialEditor.saveItem(social)">Enregistrer</button>
                                    <button type="button" @click="socialEditor.cancelEdit">Annuler</button>
                                    <button type="button" @click="socialEditor.deleteItem(social)">Supprimer</button>
                                </span>
                            </span>
                            <span v-else class="ft-social-item">
                                <a :href="social.link_url ?? '#'" class="ft-social" target="_blank" rel="noopener noreferrer" :title="social.title" :aria-label="social.title">
                                    <svg v-if="iconKey(social.title) !== 'link'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path :d="brandPaths[iconKey(social.title)]" /></svg>
                                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M3 12h18" /><path d="M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18Z" /></svg>
                                </a>
                                <span v-if="canEdit && social.id" class="eden-collection-actions">
                                    <AdminEditButton @click="socialEditor.editItem(social)" />
                                    <button type="button" @click="socialEditor.reorderItems(socialsForDisplay(), index, -1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Monter</title><path d="m6 15 6-6 6 6" /></svg></button>
                                    <button type="button" @click="socialEditor.reorderItems(socialsForDisplay(), index, 1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Descendre</title><path d="m6 9 6 6 6-6" /></svg></button>
                                </span>
                            </span>
                        </template>

                        <span v-if="socialEditor.creating.value" class="eden-project-edit">
                            <input v-model="socialEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Nom (ex. Facebook)">
                            <input v-model="socialEditor.form.value.link_url" class="eden-collection-input" type="text" placeholder="Lien (https://…)">
                            <span class="eden-collection-actions">
                                <button type="button" @click="socialEditor.createItem">Enregistrer</button>
                                <button type="button" @click="socialEditor.cancelEdit">Annuler</button>
                            </span>
                        </span>
                        <button v-if="canEdit && !socialEditor.creating.value" type="button" class="ft-social-add" title="Ajouter un réseau social" aria-label="Ajouter un réseau social" @click="socialEditor.startCreate">+</button>
                    </div>
                </div>
                <div class="ft-col">
                    <h5><EditableText :value="blocks?.navigation_title" fallback="Navigation" content-key="footer.navigation_title" :can-edit="canEdit" /></h5>
                    <button v-if="canEdit" class="admin-edit-button" type="button" title="Ajouter" @click="navigationEditor.startCreate">+</button>
                    <span v-if="navigationEditor.creating.value" class="eden-project-edit">
                        <input v-model="navigationEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Label">
                        <input v-model="navigationEditor.form.value.link_url" class="eden-collection-input" type="text" placeholder="Lien">
                        <span class="eden-collection-actions">
                            <button type="button" @click="navigationEditor.createItem">Enregistrer</button>
                            <button type="button" @click="navigationEditor.cancelEdit">Annuler</button>
                        </span>
                    </span>
                    <template v-for="(link, index) in navigationForDisplay()" :key="link.id ?? link.title">
                        <span v-if="navigationEditor.editingId.value === link.id" class="eden-project-edit">
                            <input v-model="navigationEditor.form.value.title" class="eden-collection-input" type="text">
                            <input v-model="navigationEditor.form.value.link_url" class="eden-collection-input" type="text">
                            <span class="eden-collection-actions">
                                <button type="button" @click="navigationEditor.saveItem(link)">Enregistrer</button>
                                <button type="button" @click="navigationEditor.cancelEdit">Annuler</button>
                                <button type="button" @click="navigationEditor.deleteItem(link)">Supprimer</button>
                            </span>
                        </span>
                        <a v-else :href="link.link_url ?? '#'">
                            {{ link.title }}
                            <AdminEditButton v-if="canEdit && link.id" @click.prevent="navigationEditor.editItem(link)" />
                            <span v-if="canEdit && link.id" class="eden-collection-actions">
                                <button type="button" @click.prevent="navigationEditor.reorderItems(navigationForDisplay(), index, -1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Monter</title><path d="m6 15 6-6 6 6"/></svg></button>
                                <button type="button" @click.prevent="navigationEditor.reorderItems(navigationForDisplay(), index, 1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Descendre</title><path d="m6 9 6 6 6-6"/></svg></button>
                            </span>
                        </a>
                    </template>
                </div>
                <div class="ft-col">
                    <h5><EditableText :value="blocks?.research_title" fallback="Recherche" content-key="footer.research_title" :can-edit="canEdit" /></h5>
                    <button v-if="canEdit" class="admin-edit-button" type="button" title="Ajouter" @click="researchEditor.startCreate">+</button>
                    <span v-if="researchEditor.creating.value" class="eden-project-edit">
                        <input v-model="researchEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Label">
                        <input v-model="researchEditor.form.value.link_url" class="eden-collection-input" type="text" placeholder="Lien">
                        <span class="eden-collection-actions">
                            <button type="button" @click="researchEditor.createItem">Enregistrer</button>
                            <button type="button" @click="researchEditor.cancelEdit">Annuler</button>
                        </span>
                    </span>
                    <template v-for="(link, index) in researchForDisplay()" :key="link.id ?? link.title">
                        <span v-if="researchEditor.editingId.value === link.id" class="eden-project-edit">
                            <input v-model="researchEditor.form.value.title" class="eden-collection-input" type="text">
                            <input v-model="researchEditor.form.value.link_url" class="eden-collection-input" type="text">
                            <span class="eden-collection-actions">
                                <button type="button" @click="researchEditor.saveItem(link)">Enregistrer</button>
                                <button type="button" @click="researchEditor.cancelEdit">Annuler</button>
                                <button type="button" @click="researchEditor.deleteItem(link)">Supprimer</button>
                            </span>
                        </span>
                        <a v-else :href="link.link_url ?? '#'">
                            {{ link.title }}
                            <AdminEditButton v-if="canEdit && link.id" @click.prevent="researchEditor.editItem(link)" />
                            <span v-if="canEdit && link.id" class="eden-collection-actions">
                                <button type="button" @click.prevent="researchEditor.reorderItems(researchForDisplay(), index, -1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Monter</title><path d="m6 15 6-6 6 6"/></svg></button>
                                <button type="button" @click.prevent="researchEditor.reorderItems(researchForDisplay(), index, 1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Descendre</title><path d="m6 9 6 6 6-6"/></svg></button>
                            </span>
                        </a>
                    </template>
                </div>
                <div class="ft-col">
                    <h5><EditableText :value="blocks?.information_title" fallback="Informations" content-key="footer.information_title" :can-edit="canEdit" /></h5>
                    <button v-if="canEdit" class="admin-edit-button" type="button" title="Ajouter" @click="informationEditor.startCreate">+</button>
                    <span v-if="informationEditor.creating.value" class="eden-project-edit">
                        <input v-model="informationEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Label">
                        <input v-model="informationEditor.form.value.link_url" class="eden-collection-input" type="text" placeholder="Lien">
                        <span class="eden-collection-actions">
                            <button type="button" @click="informationEditor.createItem">Enregistrer</button>
                            <button type="button" @click="informationEditor.cancelEdit">Annuler</button>
                        </span>
                    </span>
                    <template v-for="(link, index) in informationForDisplay()" :key="link.id ?? link.title">
                        <span v-if="informationEditor.editingId.value === link.id" class="eden-project-edit">
                            <input v-model="informationEditor.form.value.title" class="eden-collection-input" type="text">
                            <input v-model="informationEditor.form.value.link_url" class="eden-collection-input" type="text">
                            <span class="eden-collection-actions">
                                <button type="button" @click="informationEditor.saveItem(link)">Enregistrer</button>
                                <button type="button" @click="informationEditor.cancelEdit">Annuler</button>
                                <button type="button" @click="informationEditor.deleteItem(link)">Supprimer</button>
                            </span>
                        </span>
                        <a v-else :href="link.link_url ?? '#'">
                            {{ link.title }}
                            <AdminEditButton v-if="canEdit && link.id" @click.prevent="informationEditor.editItem(link)" />
                            <span v-if="canEdit && link.id" class="eden-collection-actions">
                                <button type="button" @click.prevent="informationEditor.reorderItems(informationForDisplay(), index, -1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Monter</title><path d="m6 15 6-6 6 6"/></svg></button>
                                <button type="button" @click.prevent="informationEditor.reorderItems(informationForDisplay(), index, 1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Descendre</title><path d="m6 9 6 6 6-6"/></svg></button>
                            </span>
                        </a>
                    </template>
                </div>
            </div>
            <div class="ft-bottom">
                <p><EditableText :value="blocks?.copyright" fallback="© 2025 EDEN-UMG — École Doctorale Écosystèmes Naturels, Université de Mahajanga. Tous droits réservés." content-key="footer.copyright" :can-edit="canEdit && canEditProtected" /></p>
                <p><EditableText :value="blocks?.credit" fallback="Conçu pour l'excellence académique" content-key="footer.credit" :can-edit="canEdit && canEditProtected" /></p>
            </div>
        </div>
    </footer>
</template>
