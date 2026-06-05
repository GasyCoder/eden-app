<script setup>
import { ref } from 'vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { useContentItemEditor } from '@/Composables/useContentItemEditor';

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    eadOneThemes: {
        type: Array,
        default: () => [],
    },
    eadTwoThemes: {
        type: Array,
        default: () => [],
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});

const activeTab = ref('t1');

const fallbackEadOneThemes = [
    { title: 'Conservation de la Biodiversité Végétale', description: 'Inventaires floristiques, cartographie et évaluation du statut de conservation des plantes endémiques malgaches de la région Boeny.' },
    { title: 'Écologie des Lémuriens et Faune Endémique', description: "Comportement, écologie alimentaire et conservation des lémuriens et autres espèces endémiques de la zone d'influence de l'UMG." },
    { title: 'Restauration des Écosystèmes Dégradés', description: 'Techniques de restauration écologique, suivi de la régénération naturelle et programmes de reboisement communautaire.' },
    { title: 'Agroforesterie et Systèmes Durables', description: 'Développement de modèles agroforestiers adaptés aux conditions écologiques et socio-économiques du nord-ouest de Madagascar.' },
];

const fallbackEadTwoThemes = [
    { title: 'Suivi des Récifs Coralliens', description: "Monitoring de l'état de santé des récifs du Canal de Mozambique : couverture corallienne, biodiversité associée, blanchissement thermique." },
    { title: 'Mangroves et Zones Humides', description: 'Cartographie, dynamique temporelle et services écosystémiques des mangroves de la côte nord-ouest de Madagascar.' },
    { title: 'Ressources Halieutiques Durables', description: 'Évaluation des stocks, pratiques de pêche artisanale et gestion intégrée des ressources marines côtières.' },
    { title: 'Limnologie et Eaux Douces', description: "Qualité physicochimique et biologique des eaux, biodiversité aquatique des lacs et cours d'eau de la région Boeny." },
];

const eadOneForDisplay = () => props.eadOneThemes?.length ? props.eadOneThemes : fallbackEadOneThemes;
const eadTwoForDisplay = () => props.eadTwoThemes?.length ? props.eadTwoThemes : fallbackEadTwoThemes;

const themePayload = (form) => ({
    title: form.title,
    description: form.description,
});

const eadOneEditor = useContentItemEditor(
    'research_themes_ead_1',
    () => ({ title: '', description: '' }),
    (item) => ({ title: item.title ?? '', description: item.description ?? '' }),
    themePayload,
);

const eadTwoEditor = useContentItemEditor(
    'research_themes_ead_2',
    () => ({ title: '', description: '' }),
    (item) => ({ title: item.title ?? '', description: item.description ?? '' }),
    themePayload,
);
</script>

<template>
    <section id="recherche">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow"><EditableText :value="blocks?.eyebrow" fallback="Recherche" content-key="recherche.eyebrow" :can-edit="canEdit" /></span>
                <h2><EditableText :value="blocks?.title" fallback="Thématiques de Recherche" content-key="recherche.title" :can-edit="canEdit" /></h2>
                <p>
                    <EditableTextarea
                        :value="blocks?.description"
                        fallback="Les activités de l'EDEN couvrent un large spectre, des forêts tropicales aux récifs coralliens, en passant par les zones humides et les agroécosystèmes malgaches."
                        content-key="recherche.description"
                        :can-edit="canEdit"
                    />
                </p>
            </div>
            <div class="reveal">
                <div class="res-tabs">
                    <button class="tab-btn" :class="{ active: activeTab === 't1' }" type="button" @click="activeTab = 't1'"><EditableText :value="blocks?.tab_ead_1" fallback="EAD 1 — Écosystèmes Terrestres" content-key="recherche.tab_ead_1" :can-edit="canEdit" /></button>
                    <button class="tab-btn" :class="{ active: activeTab === 't2' }" type="button" @click="activeTab = 't2'"><EditableText :value="blocks?.tab_ead_2" fallback="EAD 2 — Aquatique & Marin" content-key="recherche.tab_ead_2" :can-edit="canEdit" /></button>
                </div>
                <div id="t1" class="tab-pane" :class="{ active: activeTab === 't1' }">
                    <div v-if="canEdit" class="res-card">
                        <button type="button" class="admin-edit-button" title="Ajouter" @click="eadOneEditor.startCreate">+</button>
                        <div v-if="eadOneEditor.creating.value" class="eden-project-edit">
                            <input v-model="eadOneEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Titre">
                            <textarea v-model="eadOneEditor.form.value.description" class="eden-collection-textarea" placeholder="Description"></textarea>
                            <div class="eden-collection-actions">
                                <button type="button" @click="eadOneEditor.createItem">Enregistrer</button>
                                <button type="button" @click="eadOneEditor.cancelEdit">Annuler</button>
                            </div>
                        </div>
                    </div>
                    <div v-for="(theme, index) in eadOneForDisplay()" :key="theme.id ?? theme.title" class="res-card">
                        <template v-if="eadOneEditor.editingId.value === theme.id">
                            <div class="eden-project-edit">
                                <input v-model="eadOneEditor.form.value.title" class="eden-collection-input" type="text">
                                <textarea v-model="eadOneEditor.form.value.description" class="eden-collection-textarea"></textarea>
                                <div class="eden-collection-actions">
                                    <button type="button" @click="eadOneEditor.saveItem(theme)">Enregistrer</button>
                                    <button type="button" @click="eadOneEditor.cancelEdit">Annuler</button>
                                    <button type="button" @click="eadOneEditor.deleteItem(theme)">Supprimer</button>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <h4>{{ theme.title }} <AdminEditButton v-if="canEdit && theme.id" @click="eadOneEditor.editItem(theme)" /></h4>
                            <p>{{ theme.description }}</p>
                            <div v-if="canEdit && theme.id" class="eden-collection-actions">
                                <button type="button" @click="eadOneEditor.reorderItems(eadOneForDisplay(), index, -1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Monter</title><path d="m6 15 6-6 6 6"/></svg></button>
                                <button type="button" @click="eadOneEditor.reorderItems(eadOneForDisplay(), index, 1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Descendre</title><path d="m6 9 6 6 6-6"/></svg></button>
                            </div>
                        </template>
                    </div>
                </div>
                <div id="t2" class="tab-pane" :class="{ active: activeTab === 't2' }">
                    <div v-if="canEdit" class="res-card">
                        <button type="button" class="admin-edit-button" title="Ajouter" @click="eadTwoEditor.startCreate">+</button>
                        <div v-if="eadTwoEditor.creating.value" class="eden-project-edit">
                            <input v-model="eadTwoEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Titre">
                            <textarea v-model="eadTwoEditor.form.value.description" class="eden-collection-textarea" placeholder="Description"></textarea>
                            <div class="eden-collection-actions">
                                <button type="button" @click="eadTwoEditor.createItem">Enregistrer</button>
                                <button type="button" @click="eadTwoEditor.cancelEdit">Annuler</button>
                            </div>
                        </div>
                    </div>
                    <div v-for="(theme, index) in eadTwoForDisplay()" :key="theme.id ?? theme.title" class="res-card">
                        <template v-if="eadTwoEditor.editingId.value === theme.id">
                            <div class="eden-project-edit">
                                <input v-model="eadTwoEditor.form.value.title" class="eden-collection-input" type="text">
                                <textarea v-model="eadTwoEditor.form.value.description" class="eden-collection-textarea"></textarea>
                                <div class="eden-collection-actions">
                                    <button type="button" @click="eadTwoEditor.saveItem(theme)">Enregistrer</button>
                                    <button type="button" @click="eadTwoEditor.cancelEdit">Annuler</button>
                                    <button type="button" @click="eadTwoEditor.deleteItem(theme)">Supprimer</button>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <h4>{{ theme.title }} <AdminEditButton v-if="canEdit && theme.id" @click="eadTwoEditor.editItem(theme)" /></h4>
                            <p>{{ theme.description }}</p>
                            <div v-if="canEdit && theme.id" class="eden-collection-actions">
                                <button type="button" @click="eadTwoEditor.reorderItems(eadTwoForDisplay(), index, -1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Monter</title><path d="m6 15 6-6 6 6"/></svg></button>
                                <button type="button" @click="eadTwoEditor.reorderItems(eadTwoForDisplay(), index, 1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Descendre</title><path d="m6 9 6 6 6-6"/></svg></button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
