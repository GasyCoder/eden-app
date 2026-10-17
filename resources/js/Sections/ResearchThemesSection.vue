<script setup>
import { ref } from 'vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import AddItemButton from '@/Components/Editable/AddItemButton.vue';
import EditField from '@/Components/Editable/EditField.vue';
import ItemEditor from '@/Components/Editable/ItemEditor.vue';
import ItemTools from '@/Components/Editable/ItemTools.vue';
import { TabsContent, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import { useContentItemEditor } from '@/Composables/useContentItemEditor';
import { containerClass } from '@/lib/variants';

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

const tabs = [
    { id: 't1', tabKey: 'tab_ead_1', tabFallback: 'EAD 1 : Écosystèmes Terrestres', items: eadOneForDisplay, editor: eadOneEditor },
    { id: 't2', tabKey: 'tab_ead_2', tabFallback: 'EAD 2 : Aquatique & Marin', items: eadTwoForDisplay, editor: eadTwoEditor },
];

</script>

<template>
    <section id="recherche" class="bg-[var(--bg-recherche,var(--background))] py-16 lg:py-20">
        <div :class="containerClass">
            <SectionHeading>
                <EditableText :value="blocks?.title" fallback="Thématiques de Recherche" content-key="recherche.title" :can-edit="canEdit" />
                <template #description>
                    <EditableTextarea
                        :value="blocks?.description"
                        fallback="Les activités de l'EDEN couvrent un large spectre, des forêts tropicales aux récifs coralliens, en passant par les zones humides et les agroécosystèmes malgaches."
                        content-key="recherche.description"
                        :can-edit="canEdit"
                    />
                </template>
            </SectionHeading>

            <TabsRoot v-model="activeTab" class="mt-12">
                <TabsList class="inline-flex max-w-full items-center gap-1 overflow-x-auto rounded-lg bg-secondary p-1" aria-label="Équipes d’accueil doctoral">
                    <TabsTrigger
                        v-for="tab in tabs"
                        :key="tab.id"
                        :value="tab.id"
                        class="inline-flex h-9 items-center rounded-md px-4 text-sm font-medium whitespace-nowrap text-muted-foreground transition-colors outline-none hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-xs dark:data-[state=active]:bg-accent"
                    >
                        <EditableText :value="blocks?.[tab.tabKey]" :fallback="tab.tabFallback" :content-key="`recherche.${tab.tabKey}`" :can-edit="canEdit" />
                    </TabsTrigger>
                </TabsList>

                <TabsContent v-for="tab in tabs" :key="tab.id" :value="tab.id" class="mt-8 outline-none">
                    <div v-if="canEdit && !tab.editor.creating.value" class="mb-6"><AddItemButton label="Ajouter une thématique" @click="tab.editor.startCreate" /></div>
                    <ItemEditor v-if="tab.editor.creating.value" class="mb-6" title="Nouvelle thématique" :busy="tab.editor.saving.value" @save="tab.editor.createItem" @cancel="tab.editor.cancelEdit">
                        <EditField v-model="tab.editor.form.value.title" label="Titre" wide />
                        <EditField v-model="tab.editor.form.value.description" label="Description" type="textarea" wide />
                    </ItemEditor>
                    <div class="grid gap-5 md:grid-cols-2">
                        <article v-for="(theme, index) in tab.items()" :key="theme.id ?? theme.title" class="relative flex gap-5 rounded-xl border bg-card p-6 shadow-xs transition-shadow hover:shadow-md">
                            <ItemEditor
                                v-if="tab.editor.editingId.value === theme.id"
                                title="Modifier la thématique"
                                compact
                                deletable
                                :busy="tab.editor.saving.value"
                                @save="tab.editor.saveItem(theme)"
                                @cancel="tab.editor.cancelEdit"
                                @delete="tab.editor.deleteItem(theme)"
                            >
                                <EditField v-model="tab.editor.form.value.title" label="Titre" />
                                <EditField v-model="tab.editor.form.value.description" label="Description" type="textarea" />
                            </ItemEditor>
                            <template v-else>
                                <span class="text-2xl leading-none font-bold tabular-nums text-gold-500">{{ String(index + 1).padStart(2, '0') }}</span>
                                <div class="grid gap-2">
                                    <h3 class="pr-16 text-lg font-semibold tracking-tight">{{ theme.title }}</h3>
                                    <p class="text-sm leading-relaxed text-muted-foreground">{{ theme.description }}</p>
                                </div>
                                <ItemTools
                                    v-if="canEdit && theme.id"
                                    floating
                                    :first="index === 0"
                                    :last="index === tab.items().length - 1"
                                    @edit="tab.editor.editItem(theme)"
                                    @up="tab.editor.reorderItems(tab.items(), index, -1)"
                                    @down="tab.editor.reorderItems(tab.items(), index, 1)"
                                />
                            </template>
                        </article>
                    </div>
                </TabsContent>
            </TabsRoot>
        </div>
    </section>
</template>
