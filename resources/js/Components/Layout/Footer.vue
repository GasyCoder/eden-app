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
</script>

<template>
    <footer>
        <div class="container">
            <div class="ft-grid">
                <div class="ft-brand">
                    <a href="#" class="logo">
                        <div class="logo-mark">
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
