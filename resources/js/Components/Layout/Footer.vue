<script setup>
defineProps({
    site: {
        type: Object,
        default: () => ({}),
    },
    blocks: {
        type: Object,
        default: () => ({}),
    },
    navigationLinks: {
        type: Array,
        default: () => [],
    },
    researchLinks: {
        type: Array,
        default: () => [],
    },
    informationLinks: {
        type: Array,
        default: () => [],
    },
});

const fallbackNavigationLinks = [
    { title: 'À propos', link_url: '#about' },
    { title: 'Mission & Vision', link_url: '#mission' },
    { title: 'Organisation', link_url: '#organisation' },
    { title: 'Formation', link_url: '#formation' },
];

const fallbackResearchLinks = [
    { title: 'Thématiques EAD 1', link_url: '#recherche' },
    { title: 'Thématiques EAD 2', link_url: '#recherche' },
    { title: 'Projets en cours', link_url: '#projets' },
    { title: 'Partenariats', link_url: '#partenaires' },
];

const fallbackInformationLinks = [
    { title: 'Nous contacter', link_url: '#contact' },
    { title: 'Actualités', link_url: '#' },
    { title: 'Bibliothèque numérique', link_url: '#' },
    { title: 'Espace doctorant', link_url: '#' },
];
</script>

<template>
    <footer>
        <div class="container">
            <div class="ft-grid">
                <div class="ft-brand">
                    <a href="#" class="logo">
                        <div class="logo-mark">
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
                                <path d="M11 2C7 4.5 3 10 6 17C8 21 11.5 22 15 19.5C20 16 20 6 11 2Z" fill="rgba(255,255,255,0.9)" />
                                <path d="M11 2C12.5 7.5 13 14 10 20" stroke="#3f6212" stroke-width="1.2" fill="none" stroke-linecap="round" />
                            </svg>
                        </div>
                        <div class="logo-text">
                            <span class="logo-name">{{ site?.name ?? 'EDEN-UMG' }}</span>
                            <span class="logo-caption">{{ site?.caption ?? 'Université de Mahajanga' }}</span>
                        </div>
                    </a>
                    <p>{{ blocks?.description ?? 'École Doctorale Écosystèmes Naturels — Former les chercheurs de demain pour la préservation des écosystèmes malgaches et le développement durable.' }}</p>
                </div>
                <div class="ft-col">
                    <h5>Navigation</h5>
                    <a v-for="link in (navigationLinks?.length ? navigationLinks : fallbackNavigationLinks)" :key="link.title" :href="link.link_url ?? '#'">{{ link.title }}</a>
                </div>
                <div class="ft-col">
                    <h5>Recherche</h5>
                    <a v-for="link in (researchLinks?.length ? researchLinks : fallbackResearchLinks)" :key="link.title" :href="link.link_url ?? '#'">{{ link.title }}</a>
                </div>
                <div class="ft-col">
                    <h5>Informations</h5>
                    <a v-for="link in (informationLinks?.length ? informationLinks : fallbackInformationLinks)" :key="link.title" :href="link.link_url ?? '#'">{{ link.title }}</a>
                </div>
            </div>
            <div class="ft-bottom">
                <p>{{ blocks?.copyright ?? '© 2025 EDEN-UMG — École Doctorale Écosystèmes Naturels, Université de Mahajanga. Tous droits réservés.' }}</p>
                <p>{{ blocks?.credit ?? "Conçu pour l'excellence académique" }}</p>
            </div>
        </div>
    </footer>
</template>
