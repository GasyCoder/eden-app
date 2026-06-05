<script setup>
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
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

const fallbackTeams = [
    {
        title: 'Biodiversité et Écosystèmes Terrestres',
        subtitle: 'Responsable : Dr. [Nom du Responsable EAD 1]',
        meta: {
            number: 'EAD 1',
            themes: [
                'Diversité floristique et fonctionnelle des forêts malgaches',
                'Écologie des espèces endémiques et faune sauvage',
                'Conservation des ressources génétiques végétales',
                'Agroécologie et systèmes agricoles durables',
                'Restauration des écosystèmes forestiers dégradés',
            ],
        },
    },
    {
        title: 'Écosystèmes Aquatiques et Marins',
        subtitle: 'Responsable : Dr. [Nom du Responsable EAD 2]',
        meta: {
            number: 'EAD 2',
            themes: [
                'Biodiversité et santé des récifs coralliens',
                'Dynamique et conservation des mangroves',
                'Ressources halieutiques et pêche durable',
                'Qualité des eaux et écosystèmes lacustres',
                'Ichtyologie et biologie marine côtière',
            ],
        },
    },
];

const teamsForDisplay = () => props.items?.length ? props.items : fallbackTeams;

const themesToText = (themes = []) => themes.join('\n');
const textToThemes = (value = '') => value.split('\n').map((line) => line.trim()).filter(Boolean);

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
    'ead_teams',
    () => ({ number: 'EAD', title: '', subtitle: '', themes: '' }),
    (item) => ({
        number: item.meta?.number ?? '',
        title: item.title ?? '',
        subtitle: item.subtitle ?? '',
        themes: themesToText(item.meta?.themes ?? []),
    }),
    (form) => ({
        title: form.title,
        subtitle: form.subtitle,
        meta: {
            number: form.number,
            themes: textToThemes(form.themes),
        },
    }),
);
</script>

<template>
    <section id="organisation">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow"><EditableText :value="blocks?.eyebrow" fallback="Organisation" content-key="organisation.eyebrow" :can-edit="canEdit" /></span>
                <h2><EditableText :value="blocks?.title" fallback="Équipes d'Accueil Doctoral" content-key="organisation.title" :can-edit="canEdit" /></h2>
                <p>
                    <EditableTextarea
                        :value="blocks?.description"
                        fallback="L'EDEN est structurée autour de deux Équipes d'Accueil Doctoral (EAD) regroupant chercheurs et doctorants autour de thématiques scientifiques complémentaires."
                        content-key="organisation.description"
                        :can-edit="canEdit"
                    />
                </p>
            </div>
            <div v-if="canEdit" class="eden-collection-toolbar">
                <button type="button" @click="startCreate">Ajouter une équipe</button>
            </div>
            <div class="ead-grid">
                <div v-if="creating" class="ead-card reveal visible">
                    <div class="ead-head">
                        <div class="eden-project-edit">
                            <input v-model="form.number" class="eden-collection-input" type="text" placeholder="Numéro">
                            <input v-model="form.title" class="eden-collection-input" type="text" placeholder="Titre">
                            <input v-model="form.subtitle" class="eden-collection-input" type="text" placeholder="Responsable">
                        </div>
                    </div>
                    <div class="ead-body">
                        <textarea v-model="form.themes" class="eden-collection-textarea" placeholder="Une thématique par ligne"></textarea>
                        <div class="eden-collection-actions">
                            <button type="button" @click="createItem">Enregistrer</button>
                            <button type="button" @click="cancelEdit">Annuler</button>
                        </div>
                    </div>
                </div>
                <div v-for="(team, index) in teamsForDisplay()" :key="team.id ?? team.title" class="ead-card reveal">
                    <div class="ead-head">
                        <template v-if="editingId === team.id">
                            <div class="eden-project-edit">
                                <input v-model="form.number" class="eden-collection-input" type="text">
                                <input v-model="form.title" class="eden-collection-input" type="text">
                                <input v-model="form.subtitle" class="eden-collection-input" type="text">
                            </div>
                        </template>
                        <template v-else>
                            <p class="ead-num">{{ team.meta?.number ?? 'EAD 1' }} <AdminEditButton v-if="canEdit && team.id" @click="editItem(team)" /></p>
                            <h3>{{ team.title }}</h3>
                            <p class="resp">{{ team.subtitle }}</p>
                        </template>
                    </div>
                    <div class="ead-body">
                        <template v-if="editingId === team.id">
                            <textarea v-model="form.themes" class="eden-collection-textarea"></textarea>
                            <div class="eden-collection-actions">
                                <button type="button" @click="saveItem(team)">Enregistrer</button>
                                <button type="button" @click="cancelEdit">Annuler</button>
                                <button type="button" @click="deleteItem(team)">Supprimer</button>
                            </div>
                        </template>
                        <template v-else>
                            <div class="ead-themes">
                                <div v-for="theme in (team.meta?.themes ?? [])" :key="theme" class="ead-t">{{ theme }}</div>
                            </div>
                            <div v-if="canEdit && team.id" class="eden-collection-actions">
                                <button type="button" @click="reorderItems(teamsForDisplay(), index, -1)">Monter</button>
                                <button type="button" @click="reorderItems(teamsForDisplay(), index, 1)">Descendre</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
