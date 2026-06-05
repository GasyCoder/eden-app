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
    conditions: {
        type: Array,
        default: () => [],
    },
    steps: {
        type: Array,
        default: () => [],
    },
    units: {
        type: Array,
        default: () => [],
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});

const fallbackConditions = [
    { title: "Être titulaire d'un Master 2 ou diplôme équivalent" },
    { title: "Disposer d'un projet de recherche rédigé et validé" },
    { title: 'Avoir un directeur de thèse habilité (HDR)' },
    { title: "Réussir l'entretien devant le Conseil de l'École Doctorale" },
    { title: "Obtenir l'inscription administrative à l'Université de Mahajanga" },
];

const fallbackSteps = [
    { subtitle: 'Année 1', title: 'Initiation à la Recherche', description: 'Séminaires, revue bibliographique, protocole expérimental', meta: { number: '1' } },
    { subtitle: 'Année 2', title: 'Recherche Active', description: 'Terrains, analyses, publications, conférences', meta: { number: '2' } },
    { subtitle: 'Année 3', title: 'Rédaction & Soutenance', description: 'Rédaction de thèse, pré-soutenance, soutenance publique', meta: { number: '3' } },
];

const fallbackUnits = [
    { title: 'Méthodologie de la recherche scientifique' },
    { title: 'Biostatistiques et analyse de données' },
    { title: 'Rédaction et publication scientifique' },
    { title: 'Éthique et déontologie de la recherche' },
    { title: 'Valorisation et transfert de connaissances' },
];

const conditionsForDisplay = () => props.conditions?.length ? props.conditions : fallbackConditions;
const stepsForDisplay = () => props.steps?.length ? props.steps : fallbackSteps;
const unitsForDisplay = () => props.units?.length ? props.units : fallbackUnits;

const conditionEditor = useContentItemEditor(
    'formation_conditions',
    () => ({ title: '' }),
    (item) => ({ title: item.title ?? '' }),
    (form) => ({ title: form.title }),
);

const stepEditor = useContentItemEditor(
    'formation_steps',
    () => ({ number: '1', subtitle: '', title: '', description: '' }),
    (item) => ({
        number: item.meta?.number ?? '',
        subtitle: item.subtitle ?? '',
        title: item.title ?? '',
        description: item.description ?? '',
    }),
    (form) => ({
        subtitle: form.subtitle,
        title: form.title,
        description: form.description,
        meta: { number: form.number },
    }),
);

const unitEditor = useContentItemEditor(
    'formation_units',
    () => ({ title: '' }),
    (item) => ({ title: item.title ?? '' }),
    (form) => ({ title: form.title }),
);
</script>

<template>
    <section id="formation">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow"><EditableText :value="blocks?.eyebrow" fallback="Formation" content-key="formation.eyebrow" :can-edit="canEdit" /></span>
                <h2><EditableText :value="blocks?.title" fallback="Parcours Doctoral" content-key="formation.title" :can-edit="canEdit" /></h2>
                <p>
                    <EditableTextarea
                        :value="blocks?.description"
                        fallback="L'EDEN offre une formation de 3 ans alliant excellence scientifique, encadrement personnalisé et développement des compétences du chercheur."
                        content-key="formation.description"
                        :can-edit="canEdit"
                    />
                </p>
            </div>
            <div class="formation-grid">
                <div class="card reveal">
                    <h3 style="font-size:1.1rem;margin-bottom:1.25rem;"><EditableText :value="blocks?.conditions_title" fallback="Conditions d'Admission" content-key="formation.conditions_title" :can-edit="canEdit" /></h3>
                    <div v-if="canEdit" class="eden-collection-toolbar">
                        <button type="button" @click="conditionEditor.startCreate">Ajouter</button>
                    </div>
                    <div class="check-list">
                        <div v-if="conditionEditor.creating.value" class="check-i">
                            <span class="eden-project-edit">
                                <input v-model="conditionEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Condition">
                                <span class="eden-collection-actions">
                                    <button type="button" @click="conditionEditor.createItem">Enregistrer</button>
                                    <button type="button" @click="conditionEditor.cancelEdit">Annuler</button>
                                </span>
                            </span>
                        </div>
                        <div v-for="(condition, index) in conditionsForDisplay()" :key="condition.id ?? condition.title" class="check-i">
                            <template v-if="conditionEditor.editingId.value === condition.id">
                                <span class="eden-project-edit">
                                    <input v-model="conditionEditor.form.value.title" class="eden-collection-input" type="text">
                                    <span class="eden-collection-actions">
                                        <button type="button" @click="conditionEditor.saveItem(condition)">Enregistrer</button>
                                        <button type="button" @click="conditionEditor.cancelEdit">Annuler</button>
                                        <button type="button" @click="conditionEditor.deleteItem(condition)">Supprimer</button>
                                    </span>
                                </span>
                            </template>
                            <template v-else>
                                {{ condition.title }}
                                <AdminEditButton v-if="canEdit && condition.id" @click="conditionEditor.editItem(condition)" />
                                <span v-if="canEdit && condition.id" class="eden-collection-actions">
                                    <button type="button" @click="conditionEditor.reorderItems(conditionsForDisplay(), index, -1)">Monter</button>
                                    <button type="button" @click="conditionEditor.reorderItems(conditionsForDisplay(), index, 1)">Descendre</button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="card reveal">
                    <h3 style="font-size:1.1rem;margin-bottom:1.25rem;"><EditableText :value="blocks?.path_title" fallback="Parcours en 3 Ans" content-key="formation.path_title" :can-edit="canEdit" /></h3>
                    <div v-if="canEdit" class="eden-collection-toolbar">
                        <button type="button" @click="stepEditor.startCreate">Ajouter</button>
                    </div>
                    <div class="parcours">
                        <div v-if="stepEditor.creating.value" class="step">
                            <div class="step-l"><div class="step-dot">{{ stepEditor.form.value.number }}</div><div class="step-line"></div></div>
                            <div class="step-r eden-project-edit">
                                <input v-model="stepEditor.form.value.number" class="eden-collection-input" type="text" placeholder="Numéro">
                                <input v-model="stepEditor.form.value.subtitle" class="eden-collection-input" type="text" placeholder="Année">
                                <input v-model="stepEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Titre">
                                <textarea v-model="stepEditor.form.value.description" class="eden-collection-textarea" placeholder="Description"></textarea>
                                <div class="eden-collection-actions">
                                    <button type="button" @click="stepEditor.createItem">Enregistrer</button>
                                    <button type="button" @click="stepEditor.cancelEdit">Annuler</button>
                                </div>
                            </div>
                        </div>
                        <div v-for="(step, index) in stepsForDisplay()" :key="step.id ?? step.title" class="step">
                            <div class="step-l"><div class="step-dot">{{ step.meta?.number ?? '1' }}</div><div class="step-line"></div></div>
                            <div class="step-r">
                                <template v-if="stepEditor.editingId.value === step.id">
                                    <div class="eden-project-edit">
                                        <input v-model="stepEditor.form.value.number" class="eden-collection-input" type="text">
                                        <input v-model="stepEditor.form.value.subtitle" class="eden-collection-input" type="text">
                                        <input v-model="stepEditor.form.value.title" class="eden-collection-input" type="text">
                                        <textarea v-model="stepEditor.form.value.description" class="eden-collection-textarea"></textarea>
                                        <div class="eden-collection-actions">
                                            <button type="button" @click="stepEditor.saveItem(step)">Enregistrer</button>
                                            <button type="button" @click="stepEditor.cancelEdit">Annuler</button>
                                            <button type="button" @click="stepEditor.deleteItem(step)">Supprimer</button>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <p class="step-yr">{{ step.subtitle }} <AdminEditButton v-if="canEdit && step.id" @click="stepEditor.editItem(step)" /></p>
                                    <p class="step-t">{{ step.title }}</p>
                                    <p class="step-d">{{ step.description }}</p>
                                    <div v-if="canEdit && step.id" class="eden-collection-actions">
                                        <button type="button" @click="stepEditor.reorderItems(stepsForDisplay(), index, -1)">Monter</button>
                                        <button type="button" @click="stepEditor.reorderItems(stepsForDisplay(), index, 1)">Descendre</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card reveal">
                    <h3 style="font-size:1.1rem;margin-bottom:1.25rem;"><EditableText :value="blocks?.units_title" fallback="Unités de Formation" content-key="formation.units_title" :can-edit="canEdit" /></h3>
                    <div v-if="canEdit" class="eden-collection-toolbar">
                        <button type="button" @click="unitEditor.startCreate">Ajouter</button>
                    </div>
                    <div class="check-list">
                        <div v-if="unitEditor.creating.value" class="check-i">
                            <span class="eden-project-edit">
                                <input v-model="unitEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Unité">
                                <span class="eden-collection-actions">
                                    <button type="button" @click="unitEditor.createItem">Enregistrer</button>
                                    <button type="button" @click="unitEditor.cancelEdit">Annuler</button>
                                </span>
                            </span>
                        </div>
                        <div v-for="(unit, index) in unitsForDisplay()" :key="unit.id ?? unit.title" class="check-i">
                            <template v-if="unitEditor.editingId.value === unit.id">
                                <span class="eden-project-edit">
                                    <input v-model="unitEditor.form.value.title" class="eden-collection-input" type="text">
                                    <span class="eden-collection-actions">
                                        <button type="button" @click="unitEditor.saveItem(unit)">Enregistrer</button>
                                        <button type="button" @click="unitEditor.cancelEdit">Annuler</button>
                                        <button type="button" @click="unitEditor.deleteItem(unit)">Supprimer</button>
                                    </span>
                                </span>
                            </template>
                            <template v-else>
                                {{ unit.title }}
                                <AdminEditButton v-if="canEdit && unit.id" @click="unitEditor.editItem(unit)" />
                                <span v-if="canEdit && unit.id" class="eden-collection-actions">
                                    <button type="button" @click="unitEditor.reorderItems(unitsForDisplay(), index, -1)">Monter</button>
                                    <button type="button" @click="unitEditor.reorderItems(unitsForDisplay(), index, 1)">Descendre</button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
