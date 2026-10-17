<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import AboutSection from '@/Sections/AboutSection.vue';
import ContactSection from '@/Sections/ContactSection.vue';
import DirectorMessageSection from '@/Sections/DirectorMessageSection.vue';
import HeroSection from '@/Sections/HeroSection.vue';
import HomeEventsSection from '@/Sections/HomeEventsSection.vue';
import HomeJournalsSection from '@/Sections/HomeJournalsSection.vue';
import HomeMatoySection from '@/Sections/HomeMatoySection.vue';
import HomeNewsSection from '@/Sections/HomeNewsSection.vue';
import ProjectsSection from '@/Sections/ProjectsSection.vue';
import TrainingPreviewSection from '@/Sections/TrainingPreviewSection.vue';

// Accueil : présentation, mot du directeur, formation, actualités, projets, événements,
// journal MaToY et contact. Le détail (mission, équipes, partenaires…) vit sur les pages
// À propos, Formation et Recherche.
defineProps({
    blocks: { type: Object, default: () => ({}) },
    collections: { type: Object, default: () => ({}) },
    performance: { type: Object, default: () => ({}) },
    locale: { type: String, default: 'fr' },
    latestPosts: { type: Array, default: () => [] },
    upcomingEvents: { type: Array, default: () => [] },
    featuredJournals: { type: Array, default: () => [] },
    latestIssue: { type: Object, default: null },
    projects: { type: Array, default: () => [] },
    heroSlides: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="École Doctorale Écosystèmes Naturels" />

    <PublicLayout v-slot="{ editMode }">
        <HeroSection :blocks="blocks.hero" :slides="heroSlides" :image-variants="performance.heroImage" :can-edit="editMode" />
        <AboutSection :blocks="blocks.about" :stats="blocks.stats" :can-edit="editMode" />
        <DirectorMessageSection :blocks="blocks.direction" :can-edit="editMode" />
        <TrainingPreviewSection :blocks="blocks.formation" :steps="collections.formation_steps" :can-edit="editMode" />
        <HomeNewsSection :posts="latestPosts" :can-edit="editMode" />
        <ProjectsSection :blocks="blocks.projets" :projects="projects" :can-edit="editMode" show-all-link />
        <HomeEventsSection :events="upcomingEvents" :can-edit="editMode" />
        <HomeMatoySection :issue="latestIssue" />
        <HomeJournalsSection :journals="featuredJournals" />
        <ContactSection :blocks="blocks.contact" :image-variants="performance.contactMapImage" :can-edit="editMode" />
    </PublicLayout>
</template>
