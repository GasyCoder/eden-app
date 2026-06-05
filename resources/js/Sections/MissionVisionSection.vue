<script setup>
import EditableText from '@/Components/Editable/EditableText.vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { useContentItemEditor } from '@/Composables/useContentItemEditor';

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    items: {
        type: Array,
        default: () => [],
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});

const fallbackItems = [
    {
        subtitle: 'Mission',
        title: 'Former pour la Science',
        description: 'Former des chercheurs de haut niveau capables de contribuer au développement durable et à la conservation des écosystèmes naturels malgaches, dans une démarche rigoureuse et éthique.',
    },
    {
        subtitle: 'Vision',
        title: "Pôle d'Excellence Régional",
        description: "Devenir un centre d'excellence régional et international en sciences des écosystèmes, reconnu pour la qualité de ses doctorants et l'impact durable de ses recherches sur l'environnement malgache.",
    },
    {
        subtitle: 'Objectifs',
        title: 'Science & Conservation',
        description: 'Promouvoir la recherche scientifique, renforcer les partenariats nationaux et internationaux, et contribuer activement aux politiques de conservation de la biodiversité à Madagascar.',
    },
];

const itemsForDisplay = () => props.items?.length ? props.items : fallbackItems;

const {
    editingId,
    creating,
    form,
    startCreate,
    editItem,
    cancelEdit,
    createItem,
    saveItem,
    deleteItem,
    reorderItems,
} = useContentItemEditor(
    'mission_cards',
    () => ({ subtitle: '', title: '', description: '' }),
    (item) => ({
        subtitle: item.subtitle ?? '',
        title: item.title ?? '',
        description: item.description ?? '',
    }),
    (form) => ({
        subtitle: form.subtitle,
        title: form.title,
        description: form.description,
    }),
);
</script>

<template>
    <section id="mission">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow"><EditableText :value="blocks?.eyebrow" fallback="Nos fondements" content-key="mission.eyebrow" :can-edit="canEdit" /></span>
                <h2><EditableText :value="blocks?.title" fallback="Mission, Vision & Objectifs" content-key="mission.title" :can-edit="canEdit" /></h2>
            </div>
            <div v-if="canEdit" class="eden-collection-toolbar">
                <button type="button" @click="startCreate">Ajouter une carte</button>
            </div>
            <div class="mvision-grid">
                <div v-if="creating" class="mv-card reveal visible">
                    <div class="eden-project-edit">
                        <input v-model="form.subtitle" class="eden-collection-input" type="text" placeholder="Libellé">
                        <input v-model="form.title" class="eden-collection-input" type="text" placeholder="Titre">
                        <textarea v-model="form.description" class="eden-collection-textarea" placeholder="Description"></textarea>
                        <div class="eden-collection-actions">
                            <button type="button" @click="createItem">Enregistrer</button>
                            <button type="button" @click="cancelEdit">Annuler</button>
                        </div>
                    </div>
                </div>
                <div v-for="(item, index) in itemsForDisplay()" :key="item.id ?? item.title" class="mv-card reveal">
                    <template v-if="editingId === item.id">
                        <div class="eden-project-edit">
                            <input v-model="form.subtitle" class="eden-collection-input" type="text">
                            <input v-model="form.title" class="eden-collection-input" type="text">
                            <textarea v-model="form.description" class="eden-collection-textarea"></textarea>
                            <div class="eden-collection-actions">
                                <button type="button" @click="saveItem(item)">Enregistrer</button>
                                <button type="button" @click="cancelEdit">Annuler</button>
                                <button type="button" @click="deleteItem(item)">Supprimer</button>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <p class="mv-lbl">{{ item.subtitle }} <AdminEditButton v-if="canEdit && item.id" @click="editItem(item)" /></p>
                        <h3>{{ item.title }}</h3>
                        <p>{{ item.description }}</p>
                        <div v-if="canEdit && item.id" class="eden-collection-actions">
                            <button type="button" @click="reorderItems(itemsForDisplay(), index, -1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Monter</title><path d="m6 15 6-6 6 6"/></svg></button>
                            <button type="button" @click="reorderItems(itemsForDisplay(), index, 1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Descendre</title><path d="m6 9 6 6 6-6"/></svg></button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>
</template>
