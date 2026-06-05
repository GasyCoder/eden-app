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

    <Navbar />
    <HeroSection />
    <AboutSection />
    <MissionVisionSection />
    <DirectorMessageSection />
    <TeamsSection />
    <DoctoralPathSection />
    <ResearchThemesSection />
    <StatsSection />
    <PartnersSection />
    <ProjectsSection />
    <ContactSection />
    <Footer />

    <button id="scrollTop" title="Retour en haut" type="button" :class="{ visible: showScrollTop }" @click="scrollToTop">↑</button>
</template>
