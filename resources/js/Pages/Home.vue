<script setup>
import { Head } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Footer from '@/Components/Layout/Footer.vue';
import Navbar from '@/Components/Layout/Navbar.vue';
import AboutSection from '@/Sections/AboutSection.vue';
import ContactSection from '@/Sections/ContactSection.vue';
import DirectorMessageSection from '@/Sections/DirectorMessageSection.vue';
import DoctoralPathSection from '@/Sections/DoctoralPathSection.vue';
import HeroSection from '@/Sections/HeroSection.vue';
import MissionVisionSection from '@/Sections/MissionVisionSection.vue';
import PartnersSection from '@/Sections/PartnersSection.vue';
import ProjectsSection from '@/Sections/ProjectsSection.vue';
import ResearchThemesSection from '@/Sections/ResearchThemesSection.vue';
import StatsSection from '@/Sections/StatsSection.vue';
import TeamsSection from '@/Sections/TeamsSection.vue';

defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    collections: {
        type: Object,
        default: () => ({}),
    },
});

const showScrollTop = ref(false);
let revealObserver = null;

const handleScroll = () => {
    showScrollTop.value = window.scrollY > 600;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();

    revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), index * 65);
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -28px 0px' });

    document.querySelectorAll('.reveal').forEach((element) => {
        revealObserver.observe(element);
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);

    if (revealObserver) {
        revealObserver.disconnect();
    }
});
</script>

<template>
    <Head title="École Doctorale Écosystèmes Naturels" />

    <Navbar :blocks="blocks.site" />
    <HeroSection :blocks="blocks.hero" />
    <AboutSection :blocks="blocks.about" :stats="blocks.stats" />
    <MissionVisionSection :blocks="blocks.mission" :items="collections.mission_cards" />
    <DirectorMessageSection :blocks="blocks.direction" />
    <TeamsSection :blocks="blocks.organisation" :items="collections.ead_teams" />
    <DoctoralPathSection
        :blocks="blocks.formation"
        :conditions="collections.formation_conditions"
        :steps="collections.formation_steps"
        :units="collections.formation_units"
    />
    <ResearchThemesSection
        :blocks="blocks.recherche"
        :ead-one-themes="collections.research_themes_ead_1"
        :ead-two-themes="collections.research_themes_ead_2"
    />
    <StatsSection :blocks="blocks.stats" />
    <PartnersSection
        :blocks="blocks.partenaires"
        :national-partners="collections.partners_national"
        :international-partners="collections.partners_international"
    />
    <ProjectsSection :blocks="blocks.projets" :items="collections.projects" />
    <ContactSection :blocks="blocks.contact" />
    <Footer
        :site="blocks.site"
        :blocks="blocks.footer"
        :navigation-links="collections.footer_navigation"
        :research-links="collections.footer_research"
        :information-links="collections.footer_information"
    />

    <button id="scrollTop" title="Retour en haut" type="button" :class="{ visible: showScrollTop }" @click="scrollToTop">↑</button>
</template>
