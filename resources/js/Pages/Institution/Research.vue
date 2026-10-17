<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/Components/Portal/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useT } from '@/lib/i18n';
import ProjectsSection from '@/Sections/ProjectsSection.vue';
import ResearchThemesSection from '@/Sections/ResearchThemesSection.vue';

defineProps({
    blocks: { type: Object, default: () => ({}) },
    collections: { type: Object, default: () => ({}) },
    locale: { type: String, default: 'fr' },
    projects: { type: Array, default: () => [] },
});

const { t } = useT();
</script>

<template>
    <Head :title="t('research')" />

    <PublicLayout v-slot="{ editMode }">
        <PageHeader :title="t('research')" :lead="t('researchLead')" :crumbs="[{ label: t('research') }]" />
        <ResearchThemesSection
            :blocks="blocks.recherche"
            :ead-one-themes="collections.research_themes_ead_1"
            :ead-two-themes="collections.research_themes_ead_2"
            :can-edit="editMode"
        />
        <ProjectsSection :blocks="blocks.projets" :projects="projects" :can-edit="editMode" />
    </PublicLayout>
</template>
