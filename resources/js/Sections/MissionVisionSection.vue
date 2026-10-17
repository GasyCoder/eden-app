<script setup>
import EditableText from '@/Components/Editable/EditableText.vue';
import AddItemButton from '@/Components/Editable/AddItemButton.vue';
import EditField from '@/Components/Editable/EditField.vue';
import ItemEditor from '@/Components/Editable/ItemEditor.vue';
import ItemTools from '@/Components/Editable/ItemTools.vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import { useContentItemEditor } from '@/Composables/useContentItemEditor';
import { cn } from '@/lib/utils';
import { containerClass } from '@/lib/variants';

// Une couleur du logo par carte : vert, or, rouge.
const accents = [
    { bar: 'bg-eden-600', badge: 'bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300' },
    { bar: 'bg-gold-500', badge: 'bg-gold-50 text-gold-800 dark:bg-gold-950 dark:text-gold-300' },
    { bar: 'bg-laterite-600', badge: 'bg-laterite-50 text-laterite-700 dark:bg-laterite-950 dark:text-laterite-300' },
];

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
    saving,
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
    <section id="mission" class="bg-[var(--bg-mission,var(--muted))] py-16 lg:py-20">
        <div :class="containerClass">
            <SectionHeading>
                <EditableText :value="blocks?.title" fallback="Mission, Vision & Objectifs" content-key="mission.title" :can-edit="canEdit" />
            </SectionHeading>
            <div v-if="canEdit && !creating" class="mt-8"><AddItemButton label="Ajouter une carte" @click="startCreate" /></div>
            <ItemEditor v-if="creating" class="mt-8" title="Nouvelle carte" :busy="saving" @save="createItem" @cancel="cancelEdit">
                <EditField v-model="form.subtitle" label="Libellé" placeholder="Mission, Vision…" />
                <EditField v-model="form.title" label="Titre" />
                <EditField v-model="form.description" label="Description" type="textarea" wide />
            </ItemEditor>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                <article
                    v-for="(item, index) in itemsForDisplay()"
                    :key="item.id ?? item.title"
                    class="relative flex flex-col overflow-hidden rounded-xl border bg-card p-6"
                >
                    <span :class="cn('absolute inset-x-0 top-0 h-1', accents[index % 3].bar)" aria-hidden="true"></span>
                    <ItemEditor
                        v-if="editingId === item.id"
                        title="Modifier la carte"
                        compact
                        deletable
                        :busy="saving"
                        @save="saveItem(item)"
                        @cancel="cancelEdit"
                        @delete="deleteItem(item)"
                    >
                        <EditField v-model="form.subtitle" label="Libellé" />
                        <EditField v-model="form.title" label="Titre" />
                        <EditField v-model="form.description" label="Description" type="textarea" :rows="5" />
                    </ItemEditor>
                    <template v-else>
                        <div class="flex items-center gap-3">
                            <span :class="cn('grid size-9 place-items-center rounded-lg text-sm font-bold tabular-nums', accents[index % 3].badge)">{{ String(index + 1).padStart(2, '0') }}</span>
                            <p class="text-sm font-semibold text-muted-foreground">{{ item.subtitle }}</p>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold tracking-tight">{{ item.title }}</h3>
                        <p class="mt-3 leading-relaxed text-muted-foreground">{{ item.description }}</p>
                        <ItemTools
                            v-if="canEdit && item.id"
                            floating
                            :first="index === 0"
                            :last="index === itemsForDisplay().length - 1"
                            @edit="editItem(item)"
                            @up="reorderItems(itemsForDisplay(), index, -1)"
                            @down="reorderItems(itemsForDisplay(), index, 1)"
                        />
                    </template>
                </article>
            </div>
        </div>
    </section>
</template>
