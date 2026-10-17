<script setup>
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import AddItemButton from '@/Components/Editable/AddItemButton.vue';
import EditField from '@/Components/Editable/EditField.vue';
import ItemEditor from '@/Components/Editable/ItemEditor.vue';
import ItemTools from '@/Components/Editable/ItemTools.vue';
import { ArrowUpRight, Globe2, Landmark } from '@lucide/vue';
import ImageField from '@/Components/Admin/Ui/ImageField.vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import { useContentItemEditor } from '@/Composables/useContentItemEditor';
import { cn } from '@/lib/utils';
import { containerClass } from '@/lib/variants';

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    nationalPartners: {
        type: Array,
        default: () => [],
    },
    internationalPartners: {
        type: Array,
        default: () => [],
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});

const fallbackNationalPartners = [
    { title: "Université d'Antananarivo" },
    { title: 'Université de Toliara' },
    { title: 'CNRE Madagascar' },
    { title: 'IRD Madagascar' },
    { title: 'WWF Madagascar' },
    { title: 'Madagascar National Parks' },
    { title: 'WCS Madagascar' },
];

const fallbackInternationalPartners = [
    { title: 'Université de La Réunion' },
    { title: 'MNHN Paris' },
    { title: 'Université de Bordeaux' },
    { title: 'CIRAD' },
    { title: 'Agence Française de Développement' },
    { title: 'GEF, Fonds Mondial Environnement' },
    { title: 'IUCN' },
    { title: 'Union Européenne' },
];

const nationalForDisplay = () => props.nationalPartners?.length ? props.nationalPartners : fallbackNationalPartners;
const internationalForDisplay = () => props.internationalPartners?.length ? props.internationalPartners : fallbackInternationalPartners;
const partnerPayload = (form) => ({ title: form.title, image: form.image, link_url: form.link_url });

const nationalEditor = useContentItemEditor(
    'partners_national',
    () => ({ title: '', image: '', link_url: '' }),
    (item) => ({ title: item.title ?? '', image: item.image ?? '', link_url: item.link_url ?? '' }),
    partnerPayload,
);

const internationalEditor = useContentItemEditor(
    'partners_international',
    () => ({ title: '', image: '', link_url: '' }),
    (item) => ({ title: item.title ?? '', image: item.image ?? '', link_url: item.link_url ?? '' }),
    partnerPayload,
);

const groups = [
    { id: 'national', labelKey: 'national_label', labelFallback: 'Partenaires nationaux', icon: Landmark, items: nationalForDisplay, editor: nationalEditor },
    { id: 'international', labelKey: 'international_label', labelFallback: 'Partenaires internationaux', icon: Globe2, items: internationalForDisplay, editor: internationalEditor },
];

// Monogramme affiché tant qu'aucun logo n'est fourni : sigle (CNRE, IRD…) ou initiales.
const stopWords = ['de', 'des', 'du', 'la', 'le', 'les', 'et', 'pour', 'd', 'l'];
const monogram = (name = '') => {
    const words = name.replace(/[’']/g, ' ').split(/[\s,]+/).filter(Boolean);
    const acronym = words.find((word) => word.length >= 2 && word === word.toUpperCase() && /[A-Z]/.test(word));

    if (acronym) {
        return acronym.slice(0, 4);
    }

    return words.filter((word) => !stopWords.includes(word.toLowerCase())).slice(0, 2).map((word) => word.charAt(0).toUpperCase()).join('');
};

const tones = [
    'bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300',
    'bg-gold-50 text-gold-800 dark:bg-gold-950 dark:text-gold-300',
    'bg-laterite-50 text-laterite-700 dark:bg-laterite-950 dark:text-laterite-300',
];
const hasLink = (partner) => partner.link_url && partner.link_url !== '#';
</script>

<template>
    <section id="partenaires" class="bg-[var(--bg-partenaires,var(--background))] py-16 lg:py-20">
        <div :class="containerClass">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <SectionHeading>
                    <EditableText :value="blocks?.title" fallback="Nos Partenaires" content-key="partenaires.title" :can-edit="canEdit" />
                    <template #description>
                        <EditableTextarea
                            :value="blocks?.description"
                            fallback="L'EDEN développe des collaborations avec des organismes nationaux et internationaux de premier plan en matière de recherche environnementale et de conservation."
                            content-key="partenaires.description"
                            :can-edit="canEdit"
                        />
                    </template>
                </SectionHeading>
                <dl class="flex gap-8 lg:pb-1">
                    <div v-for="group in groups" :key="group.id" class="flex flex-col-reverse">
                        <dt class="text-sm text-muted-foreground">{{ blocks?.[group.labelKey] || group.labelFallback }}</dt>
                        <dd class="text-3xl font-semibold tracking-tight tabular-nums text-eden-700 dark:text-eden-400">{{ group.items().length }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-10 grid gap-10">
                <div v-for="group in groups" :key="group.id">
                    <div class="mb-6 flex flex-wrap items-center gap-3">
                        <component :is="group.icon" class="size-5 text-eden-600 dark:text-eden-400" aria-hidden="true" />
                        <h3 class="text-base font-semibold">
                            <EditableText :value="blocks?.[group.labelKey]" :fallback="group.labelFallback" :content-key="`partenaires.${group.labelKey}`" :can-edit="canEdit" />
                        </h3>
                        <span class="h-px flex-1 bg-border" aria-hidden="true"></span>
                        <AddItemButton v-if="canEdit && !group.editor.creating.value" label="Ajouter un partenaire" @click="group.editor.startCreate" />
                    </div>

                    <ItemEditor
                        v-if="group.editor.creating.value"
                        class="mb-6"
                        title="Nouveau partenaire"
                        :busy="group.editor.saving.value"
                        @save="group.editor.createItem"
                        @cancel="group.editor.cancelEdit"
                    >
                        <EditField v-model="group.editor.form.value.title" label="Nom du partenaire" />
                        <EditField v-model="group.editor.form.value.link_url" label="Site web" placeholder="https://…" />
                        <div class="sm:col-span-2"><ImageField v-model="group.editor.form.value.image" label="Logo" aspect="3 / 1" hint="PNG à fond transparent de préférence." /></div>
                    </ItemEditor>

                    <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <li
                            v-for="(partner, index) in group.items()"
                            :key="partner.id ?? partner.title"
                            :class="cn('relative', group.editor.editingId.value === partner.id && 'col-span-full')"
                        >
                            <ItemEditor
                                v-if="group.editor.editingId.value === partner.id"
                                title="Modifier le partenaire"
                                deletable
                                :busy="group.editor.saving.value"
                                @save="group.editor.saveItem(partner)"
                                @cancel="group.editor.cancelEdit"
                                @delete="group.editor.deleteItem(partner)"
                            >
                                <EditField v-model="group.editor.form.value.title" label="Nom du partenaire" />
                                <EditField v-model="group.editor.form.value.link_url" label="Site web" placeholder="https://…" />
                                <div class="sm:col-span-2"><ImageField v-model="group.editor.form.value.image" label="Logo" aspect="3 / 1" hint="PNG à fond transparent de préférence." /></div>
                            </ItemEditor>
                            <template v-else>
                                <component
                                    :is="hasLink(partner) ? 'a' : 'div'"
                                    :href="hasLink(partner) ? partner.link_url : undefined"
                                    :target="hasLink(partner) ? '_blank' : undefined"
                                    :rel="hasLink(partner) ? 'noopener noreferrer' : undefined"
                                    class="group flex h-full min-h-36 flex-col items-center justify-center gap-3 rounded-xl border bg-card p-5 text-center transition-colors hover:border-eden-300 hover:shadow-md focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none dark:hover:border-eden-700"
                                >
                                    <span v-if="partner.image" class="flex h-14 w-full items-center justify-center">
                                        <img :src="partner.image" :alt="`Logo ${partner.title}`" class="max-h-14 max-w-[85%] object-contain grayscale transition duration-300 group-hover:grayscale-0 dark:rounded dark:bg-white dark:p-1" loading="lazy" decoding="async">
                                    </span>
                                    <span v-else :class="cn('grid size-14 place-items-center rounded-full text-sm font-bold tracking-wide', tones[index % 3])" aria-hidden="true">{{ monogram(partner.title) }}</span>
                                    <span class="text-sm leading-snug font-medium text-pretty text-foreground">{{ partner.title }}</span>
                                    <ArrowUpRight v-if="hasLink(partner)" class="absolute top-4 right-4 size-4 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100" aria-hidden="true" />
                                    <span v-if="hasLink(partner)" class="sr-only">(site du partenaire, nouvel onglet)</span>
                                </component>
                                <ItemTools
                                    v-if="canEdit && partner.id"
                                    floating
                                    :first="index === 0"
                                    :last="index === group.items().length - 1"
                                    @edit="group.editor.editItem(partner)"
                                    @up="group.editor.reorderItems(group.items(), index, -1)"
                                    @down="group.editor.reorderItems(group.items(), index, 1)"
                                />
                            </template>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
