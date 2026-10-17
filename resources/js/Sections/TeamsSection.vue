<script setup>
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import AddItemButton from '@/Components/Editable/AddItemButton.vue';
import EditField from '@/Components/Editable/EditField.vue';
import ItemEditor from '@/Components/Editable/ItemEditor.vue';
import ItemTools from '@/Components/Editable/ItemTools.vue';
import { CircleCheck, UserRound } from '@lucide/vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import { useContentItemEditor } from '@/Composables/useContentItemEditor';
import { containerClass } from '@/lib/variants';

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
    <section id="organisation" class="bg-[var(--bg-organisation,var(--muted))] py-16 lg:py-20">
        <div :class="containerClass">
            <SectionHeading>
                <EditableText :value="blocks?.title" fallback="Équipes d'Accueil Doctoral" content-key="organisation.title" :can-edit="canEdit" />
                <template #description>
                    <EditableTextarea
                        :value="blocks?.description"
                        fallback="L'EDEN est structurée autour de deux Équipes d'Accueil Doctoral (EAD) regroupant chercheurs et doctorants autour de thématiques scientifiques complémentaires."
                        content-key="organisation.description"
                        :can-edit="canEdit"
                    />
                </template>
            </SectionHeading>
            <div v-if="canEdit && !creating" class="mt-8"><AddItemButton label="Ajouter une équipe" @click="startCreate" /></div>
            <ItemEditor v-if="creating" class="mt-8" title="Nouvelle équipe" :busy="saving" @save="createItem" @cancel="cancelEdit">
                <EditField v-model="form.number" label="Code" placeholder="EAD 3" />
                <EditField v-model="form.title" label="Nom de l’équipe" />
                <EditField v-model="form.subtitle" label="Responsable" placeholder="Responsable : Dr …" wide />
                <EditField v-model="form.themes" label="Thématiques" type="textarea" :rows="5" hint="Une thématique par ligne." wide />
            </ItemEditor>
            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <article v-for="(team, index) in teamsForDisplay()" :key="team.id ?? team.title" class="relative flex flex-col overflow-hidden rounded-xl border bg-card shadow-xs">
                    <div v-if="editingId === team.id" class="p-5">
                        <ItemEditor title="Modifier l’équipe" deletable :busy="saving" @save="saveItem(team)" @cancel="cancelEdit" @delete="deleteItem(team)">
                            <EditField v-model="form.number" label="Code" />
                            <EditField v-model="form.title" label="Nom de l’équipe" />
                            <EditField v-model="form.subtitle" label="Responsable" wide />
                            <EditField v-model="form.themes" label="Thématiques" type="textarea" :rows="6" hint="Une thématique par ligne." wide />
                        </ItemEditor>
                    </div>
                    <template v-else>
                        <header class="border-b p-6">
                            <span class="inline-flex rounded-md bg-eden-50 px-2 py-0.5 text-xs font-semibold text-eden-700 dark:bg-eden-950 dark:text-eden-300">{{ team.meta?.number ?? 'EAD 1' }}</span>
                            <h3 class="mt-3 text-lg font-semibold tracking-tight">{{ team.title }}</h3>
                            <p class="mt-1.5 flex items-center gap-2 text-sm text-muted-foreground">
                                <UserRound class="size-4 shrink-0" aria-hidden="true" />{{ team.subtitle }}
                            </p>
                        </header>
                        <ul class="grid gap-3 p-6">
                            <li v-for="theme in (team.meta?.themes ?? [])" :key="theme" class="flex items-start gap-3 text-sm leading-relaxed">
                                <CircleCheck class="mt-0.5 size-4 shrink-0 text-eden-600 dark:text-eden-400" aria-hidden="true" />{{ theme }}
                            </li>
                        </ul>
                        <ItemTools
                            v-if="canEdit && team.id"
                            floating
                            :first="index === 0"
                            :last="index === teamsForDisplay().length - 1"
                            @edit="editItem(team)"
                            @up="reorderItems(teamsForDisplay(), index, -1)"
                            @down="reorderItems(teamsForDisplay(), index, 1)"
                        />
                    </template>
                </article>
            </div>
        </div>
    </section>
</template>
