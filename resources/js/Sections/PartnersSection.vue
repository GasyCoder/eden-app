<script setup>
defineProps({
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
</script>

<template>
    <section id="partenaires">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow">{{ blocks?.eyebrow ?? 'Partenariats' }}</span>
                <h2>{{ blocks?.title ?? 'Nos Partenaires' }}</h2>
                <p>{{ blocks?.description ?? "L'EDEN développe des collaborations avec des organismes nationaux et internationaux de premier plan en matière de recherche environnementale et de conservation." }}</p>
            </div>
            <div class="part-section reveal">
                <p class="part-type">{{ blocks?.national_label ?? 'Partenaires nationaux' }}</p>
                <div class="part-row">
                    <span v-for="partner in (nationalPartners?.length ? nationalPartners : fallbackNationalPartners)" :key="partner.title" class="part-chip">{{ partner.title }}</span>
                </div>
            </div>
            <div class="part-section reveal">
                <p class="part-type">{{ blocks?.international_label ?? 'Partenaires internationaux' }}</p>
                <div class="part-row">
                    <span v-for="partner in (internationalPartners?.length ? internationalPartners : fallbackInternationalPartners)" :key="partner.title" class="part-chip">{{ partner.title }}</span>
                </div>
            </div>
        </div>
    </section>
</template>
