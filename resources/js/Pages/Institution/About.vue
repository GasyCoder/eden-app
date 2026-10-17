<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/Components/Portal/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useT } from '@/lib/i18n';
import AboutSection from '@/Sections/AboutSection.vue';
import DirectorMessageSection from '@/Sections/DirectorMessageSection.vue';
import MissionVisionSection from '@/Sections/MissionVisionSection.vue';
import PartnersSection from '@/Sections/PartnersSection.vue';
import TeamsSection from '@/Sections/TeamsSection.vue';

defineProps({
    blocks: { type: Object, default: () => ({}) },
    collections: { type: Object, default: () => ({}) },
});

const { t } = useT();
</script>

<template>
    <Head :title="t('aboutPage')" />

    <PublicLayout v-slot="{ editMode }">
        <PageHeader :title="t('aboutPage')" :lead="t('aboutLead')" :crumbs="[{ label: t('about') }]" />
        <AboutSection :blocks="blocks.about" :stats="blocks.stats" :can-edit="editMode" />
        <MissionVisionSection :blocks="blocks.mission" :items="collections.mission_cards" :can-edit="editMode" />
        <DirectorMessageSection :blocks="blocks.direction" :can-edit="editMode" />
        <TeamsSection :blocks="blocks.organisation" :items="collections.ead_teams" :can-edit="editMode" />
        <PartnersSection
            :blocks="blocks.partenaires"
            :national-partners="collections.partners_national"
            :international-partners="collections.partners_international"
            :can-edit="editMode"
        />
    </PublicLayout>
</template>
