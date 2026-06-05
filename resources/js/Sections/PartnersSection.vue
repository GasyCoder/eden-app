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
    { title: 'GEF — Fonds Mondial Environnement' },
    { title: 'IUCN' },
    { title: 'Union Européenne' },
];

const nationalForDisplay = () => props.nationalPartners?.length ? props.nationalPartners : fallbackNationalPartners;
const internationalForDisplay = () => props.internationalPartners?.length ? props.internationalPartners : fallbackInternationalPartners;
const partnerPayload = (form) => ({ title: form.title });

const nationalEditor = useContentItemEditor(
    'partners_national',
    () => ({ title: '' }),
    (item) => ({ title: item.title ?? '' }),
    partnerPayload,
);

const internationalEditor = useContentItemEditor(
    'partners_international',
    () => ({ title: '' }),
    (item) => ({ title: item.title ?? '' }),
    partnerPayload,
);
</script>

<template>
    <section id="partenaires">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow"><EditableText :value="blocks?.eyebrow" fallback="Partenariats" content-key="partenaires.eyebrow" :can-edit="canEdit" /></span>
                <h2><EditableText :value="blocks?.title" fallback="Nos Partenaires" content-key="partenaires.title" :can-edit="canEdit" /></h2>
                <p>
                    <EditableTextarea
                        :value="blocks?.description"
                        fallback="L'EDEN développe des collaborations avec des organismes nationaux et internationaux de premier plan en matière de recherche environnementale et de conservation."
                        content-key="partenaires.description"
                        :can-edit="canEdit"
                    />
                </p>
            </div>
            <div class="part-section reveal">
                <p class="part-type"><EditableText :value="blocks?.national_label" fallback="Partenaires nationaux" content-key="partenaires.national_label" :can-edit="canEdit" /></p>
                <div v-if="canEdit" class="eden-collection-toolbar">
                    <button type="button" @click="nationalEditor.startCreate">Ajouter</button>
                </div>
                <div class="part-row">
                    <span v-if="nationalEditor.creating.value" class="part-chip">
                        <span class="eden-project-edit">
                            <input v-model="nationalEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Partenaire">
                            <span class="eden-collection-actions">
                                <button type="button" @click="nationalEditor.createItem">Enregistrer</button>
                                <button type="button" @click="nationalEditor.cancelEdit">Annuler</button>
                            </span>
                        </span>
                    </span>
                    <span v-for="(partner, index) in nationalForDisplay()" :key="partner.id ?? partner.title" class="part-chip">
                        <template v-if="nationalEditor.editingId.value === partner.id">
                            <span class="eden-project-edit">
                                <input v-model="nationalEditor.form.value.title" class="eden-collection-input" type="text">
                                <span class="eden-collection-actions">
                                    <button type="button" @click="nationalEditor.saveItem(partner)">Enregistrer</button>
                                    <button type="button" @click="nationalEditor.cancelEdit">Annuler</button>
                                    <button type="button" @click="nationalEditor.deleteItem(partner)">Supprimer</button>
                                </span>
                            </span>
                        </template>
                        <template v-else>
                            {{ partner.title }}
                            <AdminEditButton v-if="canEdit && partner.id" @click="nationalEditor.editItem(partner)" />
                            <span v-if="canEdit && partner.id" class="eden-collection-actions">
                                <button type="button" @click="nationalEditor.reorderItems(nationalForDisplay(), index, -1)">Monter</button>
                                <button type="button" @click="nationalEditor.reorderItems(nationalForDisplay(), index, 1)">Descendre</button>
                            </span>
                        </template>
                    </span>
                </div>
            </div>
            <div class="part-section reveal">
                <p class="part-type"><EditableText :value="blocks?.international_label" fallback="Partenaires internationaux" content-key="partenaires.international_label" :can-edit="canEdit" /></p>
                <div v-if="canEdit" class="eden-collection-toolbar">
                    <button type="button" @click="internationalEditor.startCreate">Ajouter</button>
                </div>
                <div class="part-row">
                    <span v-if="internationalEditor.creating.value" class="part-chip">
                        <span class="eden-project-edit">
                            <input v-model="internationalEditor.form.value.title" class="eden-collection-input" type="text" placeholder="Partenaire">
                            <span class="eden-collection-actions">
                                <button type="button" @click="internationalEditor.createItem">Enregistrer</button>
                                <button type="button" @click="internationalEditor.cancelEdit">Annuler</button>
                            </span>
                        </span>
                    </span>
                    <span v-for="(partner, index) in internationalForDisplay()" :key="partner.id ?? partner.title" class="part-chip">
                        <template v-if="internationalEditor.editingId.value === partner.id">
                            <span class="eden-project-edit">
                                <input v-model="internationalEditor.form.value.title" class="eden-collection-input" type="text">
                                <span class="eden-collection-actions">
                                    <button type="button" @click="internationalEditor.saveItem(partner)">Enregistrer</button>
                                    <button type="button" @click="internationalEditor.cancelEdit">Annuler</button>
                                    <button type="button" @click="internationalEditor.deleteItem(partner)">Supprimer</button>
                                </span>
                            </span>
                        </template>
                        <template v-else>
                            {{ partner.title }}
                            <AdminEditButton v-if="canEdit && partner.id" @click="internationalEditor.editItem(partner)" />
                            <span v-if="canEdit && partner.id" class="eden-collection-actions">
                                <button type="button" @click="internationalEditor.reorderItems(internationalForDisplay(), index, -1)">Monter</button>
                                <button type="button" @click="internationalEditor.reorderItems(internationalForDisplay(), index, 1)">Descendre</button>
                            </span>
                        </template>
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
