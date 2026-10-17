<script setup>
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import AddItemButton from '@/Components/Editable/AddItemButton.vue';
import EditField from '@/Components/Editable/EditField.vue';
import ItemEditor from '@/Components/Editable/ItemEditor.vue';
import ItemTools from '@/Components/Editable/ItemTools.vue';
import { BookOpenCheck, ClipboardCheck } from '@lucide/vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import { useContentItemEditor } from '@/Composables/useContentItemEditor';
import { containerClass } from '@/lib/variants';

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

// Deux listes simples (conditions, unités) partagent le même gabarit
const simpleLists = [
    { id: 'conditions', titleKey: 'conditions_title', titleFallback: "Conditions d'Admission", items: conditionsForDisplay, editor: conditionEditor, itemLabel: 'Condition', addLabel: 'Ajouter une condition', icon: ClipboardCheck },
    { id: 'units', titleKey: 'units_title', titleFallback: 'Unités de Formation', items: unitsForDisplay, editor: unitEditor, itemLabel: 'Unité de formation', addLabel: 'Ajouter une unité', icon: BookOpenCheck },
];
</script>

<template>
    <section id="formation" class="bg-[var(--bg-formation,var(--background))] py-16 lg:py-20">
        <div :class="containerClass">
            <SectionHeading>
                <EditableText :value="blocks?.title" fallback="Parcours Doctoral" content-key="formation.title" :can-edit="canEdit" />
                <template #description>
                    <EditableTextarea
                        :value="blocks?.description"
                        fallback="L'EDEN offre une formation de 3 ans alliant excellence scientifique, encadrement personnalisé et développement des compétences du chercheur."
                        content-key="formation.description"
                        :can-edit="canEdit"
                    />
                </template>
            </SectionHeading>

            <div class="mt-14 mb-6 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-semibold"><EditableText :value="blocks?.path_title" fallback="Parcours en 3 Ans" content-key="formation.path_title" :can-edit="canEdit" /></h3>
                <AddItemButton v-if="canEdit && !stepEditor.creating.value" label="Ajouter une étape" @click="stepEditor.startCreate" />
            </div>
            <ItemEditor v-if="stepEditor.creating.value" class="mb-6" title="Nouvelle étape" :busy="stepEditor.saving.value" @save="stepEditor.createItem" @cancel="stepEditor.cancelEdit">
                <EditField v-model="stepEditor.form.value.number" label="Numéro" placeholder="4" />
                <EditField v-model="stepEditor.form.value.subtitle" label="Période" placeholder="Année 4" />
                <EditField v-model="stepEditor.form.value.title" label="Titre" wide />
                <EditField v-model="stepEditor.form.value.description" label="Description" type="textarea" wide />
            </ItemEditor>
            <ol class="grid gap-6 md:grid-cols-3">
                <li v-for="(step, index) in stepsForDisplay()" :key="step.id ?? step.title" class="relative">
                    <ItemEditor
                        v-if="stepEditor.editingId.value === step.id"
                        title="Modifier l’étape"
                        compact
                        deletable
                        :busy="stepEditor.saving.value"
                        @save="stepEditor.saveItem(step)"
                        @cancel="stepEditor.cancelEdit"
                        @delete="stepEditor.deleteItem(step)"
                    >
                        <EditField v-model="stepEditor.form.value.number" label="Numéro" />
                        <EditField v-model="stepEditor.form.value.subtitle" label="Période" />
                        <EditField v-model="stepEditor.form.value.title" label="Titre" />
                        <EditField v-model="stepEditor.form.value.description" label="Description" type="textarea" />
                    </ItemEditor>
                    <div v-else class="h-full rounded-xl border bg-card p-6 shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="grid size-11 shrink-0 place-items-center rounded-full bg-eden-600 text-base font-bold text-white ring-4 ring-eden-50 dark:ring-eden-950">{{ step.meta?.number ?? index + 1 }}</span>
                            <span v-if="index < stepsForDisplay().length - 1" class="hidden h-px flex-1 border-t-2 border-dashed border-eden-200 md:block dark:border-eden-800" aria-hidden="true"></span>
                        </div>
                        <p class="mt-5 text-sm font-semibold text-gold-700 dark:text-gold-400">{{ step.subtitle }}</p>
                        <h4 class="mt-1 text-xl font-semibold tracking-tight">{{ step.title }}</h4>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ step.description }}</p>
                        <div v-if="canEdit && step.id" class="mt-4">
                            <ItemTools
                                :first="index === 0"
                                :last="index === stepsForDisplay().length - 1"
                                @edit="stepEditor.editItem(step)"
                                @up="stepEditor.reorderItems(stepsForDisplay(), index, -1)"
                                @down="stepEditor.reorderItems(stepsForDisplay(), index, 1)"
                            />
                        </div>
                    </div>
                </li>
            </ol>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <div v-for="list in simpleLists" :key="list.id" class="rounded-xl border bg-muted/60 p-7">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-lg bg-background text-eden-700 shadow-xs ring-1 ring-border dark:text-eden-300"><component :is="list.icon" class="size-5" aria-hidden="true" /></span>
                        <h3 class="text-lg font-semibold"><EditableText :value="blocks?.[list.titleKey]" :fallback="list.titleFallback" :content-key="`formation.${list.titleKey}`" :can-edit="canEdit" /></h3>
                        <AddItemButton v-if="canEdit && !list.editor.creating.value" class="ml-auto" compact :label="list.addLabel" @click="list.editor.startCreate" />
                    </div>
                    <ItemEditor v-if="list.editor.creating.value" class="mb-4" :title="list.addLabel" compact :busy="list.editor.saving.value" @save="list.editor.createItem" @cancel="list.editor.cancelEdit">
                        <EditField v-model="list.editor.form.value.title" :label="list.itemLabel" />
                    </ItemEditor>
                    <ul class="grid gap-3">
                        <li v-for="(entry, index) in list.items()" :key="entry.id ?? entry.title" class="flex items-start gap-3 text-sm leading-relaxed">
                            <ItemEditor
                                v-if="list.editor.editingId.value === entry.id"
                                :title="`Modifier : ${list.itemLabel.toLowerCase()}`"
                                compact
                                deletable
                                :busy="list.editor.saving.value"
                                @save="list.editor.saveItem(entry)"
                                @cancel="list.editor.cancelEdit"
                                @delete="list.editor.deleteItem(entry)"
                            >
                                <EditField v-model="list.editor.form.value.title" :label="list.itemLabel" />
                            </ItemEditor>
                            <template v-else>
                                <span class="mt-1 grid size-5 shrink-0 place-items-center rounded-full bg-eden-600 text-white" aria-hidden="true">
                                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5" /></svg>
                                </span>
                                <span class="flex-1">{{ entry.title }}</span>
                                <ItemTools
                                    v-if="canEdit && entry.id"
                                    :first="index === 0"
                                    :last="index === list.items().length - 1"
                                    @edit="list.editor.editItem(entry)"
                                    @up="list.editor.reorderItems(list.items(), index, -1)"
                                    @down="list.editor.reorderItems(list.items(), index, 1)"
                                />
                            </template>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
