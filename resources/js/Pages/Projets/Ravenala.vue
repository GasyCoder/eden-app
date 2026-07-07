<script setup>
import { Head } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import CookieBanner from '@/Components/CookieBanner.vue';
import InstallPrompt from '@/Components/InstallPrompt.vue';
import VisitorAnnouncementPopup from '@/Components/VisitorAnnouncementPopup.vue';
import Navbar from '@/Components/Layout/Navbar.vue';
import Footer from '@/Components/Layout/Footer.vue';

defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    collections: {
        type: Object,
        default: () => ({}),
    },
    locale: {
        type: String,
        default: 'fr',
    },
    languageUrls: {
        type: Object,
        default: () => ({ fr: '/projets/ravenala', en: '/en' }),
    },
    performance: {
        type: Object,
        default: () => ({}),
    },
    visitorPopups: {
        type: Array,
        default: () => [],
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});

const objectives = [
    'Renforcer les compétences pédagogiques des enseignants universitaires.',
    'Soutenir la transformation positive des pratiques pédagogiques.',
    'Favoriser l’émergence de référents pédagogiques qualifiés.',
    'Promouvoir l’éducation inclusive et l’égalité femmes-hommes.',
    'Optimiser l’usage des pratiques pédagogiques et numériques.',
    'Diffuser les résultats du projet et sensibiliser la communauté universitaire.',
];

const partners = [
    {
        initials: 'UA',
        name: 'Université d’Angers',
        country: 'France',
        role: 'Chef de file du projet',
        logo: '/images-projet/ua-nb.png',
    },
    {
        initials: 'UCL',
        name: 'Université Catholique de Louvain',
        country: 'Belgique',
        role: 'Partenaire académique',
        logo: '/images-projet/ucl.jpeg',
    },
    {
        initials: 'UNA',
        name: 'Université d’Antsiranana',
        country: 'Madagascar',
        role: 'Université bénéficiaire',
        logo: '/images-projet/logo-una-NB.png',
    },
    {
        initials: 'UMG',
        name: 'Université de Mahajanga',
        country: 'Madagascar',
        role: 'Université bénéficiaire',
        logo: '/images-projet/umgs.jpeg',
    },
    {
        initials: 'UDC',
        name: 'Université des Comores',
        country: 'Comores',
        role: 'Université bénéficiaire',
        logo: '/images-projet/unive-comores-NB.jpg',
    },
];

const projectImages = {
    hero: '/images-projet/1771294651732.jpeg',
    banner: '/images-projet/images.jpeg',
    logo: '/images-projet/ID-R-C.png',
    eu: '/images-projet/EN_Co-fundedbytheEU_RGB_BLACK.jpg',
};

const beneficiaries = [
    'Enseignants et personnels de l’Université d’Antsiranana',
    'Enseignants et personnels de l’Université de Mahajanga',
    'Enseignants et personnels de l’Université des Comores',
];

const workPackages = [
    {
        code: 'WP1',
        title: 'Gestion, coordination et suivi du projet',
        description: 'Coordination opérationnelle, suivi méthodologique et reporting régulier pour garantir le bon déroulement du projet.',
        lead: 'Pilote : UA — Co-pilote : UNA',
    },
    {
        code: 'WP2',
        title: 'Partage et co-construction des ressources pédagogiques',
        description: 'Mise en commun de ressources, guides et outils pour renforcer les pratiques pédagogiques universitaires.',
        lead: 'Pilote : UA — Co-pilote : UCLouvain',
    },
    {
        code: 'WP3',
        title: 'Formation de futurs référents pédagogiques',
        description: 'Formation et accompagnement d’enseignants capables de soutenir leurs pairs dans les établissements partenaires.',
        lead: 'Pilote : UCLouvain — Co-pilote : UMG',
    },
    {
        code: 'WP4',
        title: 'Sensibilisations et formations pour les enseignants',
        description: 'Formations en présentiel, accompagnements à distance et sensibilisations à l’égalité dans l’enseignement supérieur.',
        lead: 'Pilote : UNA — Co-pilote : UDC',
    },
    {
        code: 'WP5',
        title: 'Communication et pérennisation',
        description: 'Diffusion des résultats, valorisation des acquis et pérennisation des pratiques après le projet.',
        lead: 'Pilote : UDC — Co-pilote : UMG',
    },
];
</script>

<template>
    <Head title="Projet RAVENALA">
        <meta
            name="description"
            content="Projet RAVENALA : coopération universitaire pour renforcer la pédagogie universitaire à Madagascar et aux Comores, avec l’Université de Mahajanga et l’École Doctorale EDEN."
        >
    </Head>

    <VisitorAnnouncementPopup
        :blocks="blocks.popup"
        :announcements="visitorPopups"
        :current-locale="locale"
        :is-admin="canEdit"
        :can-manage="false"
    />
    <Toast />
    <CookieBanner />
    <InstallPrompt />

    <Navbar
        :blocks="blocks.site"
        :nav="blocks.nav"
        :logo-variants="performance.siteLogo"
        :locale="locale"
        :language-urls="languageUrls"
        anchor-base="/"
        active-section="projets"
    />

    <main class="ravenala-page">
        <section class="ravenala-hero">
            <div class="ravenala-shell ravenala-hero-grid">
                <div class="ravenala-hero-copy">
                    <a class="ravenala-back-link" href="/#projets">← Retour aux projets EDEN</a>
                    <p class="ravenala-eyebrow"><span></span>Projet institutionnel</p>
                    <h1>Projet RAVENALA</h1>
                    <p class="ravenala-subtitle">
                        Réseau Académique pour la Valorisation des Enseignants, des Nouvelles Approches et de l’Apprentissage actif
                    </p>
                    <p class="ravenala-lead">
                        Le projet renforce les capacités des établissements d’enseignement supérieur en matière de pédagogie universitaire à Madagascar et aux Comores, au service d’un apprentissage actif, inclusif et durable.
                    </p>
                    <div class="ravenala-actions">
                        <a href="#objectifs" class="ravenala-btn ravenala-btn-primary">Découvrir les objectifs</a>
                        <a href="#partenaires-ravenala" class="ravenala-btn ravenala-btn-secondary">Voir les partenaires</a>
                    </div>
                </div>

                <aside class="ravenala-hero-card" aria-label="Résumé du projet RAVENALA">
                    <figure class="ravenala-hero-media">
                        <img
                            :src="projectImages.hero"
                            alt="Rencontre institutionnelle du projet RAVENALA avec les partenaires"
                            width="800"
                            height="533"
                            loading="eager"
                            fetchpriority="high"
                            decoding="async"
                        >
                    </figure>
                    <div class="ravenala-brand-row">
                        <img
                            :src="projectImages.logo"
                            alt="Logo RAVENALA"
                            width="588"
                            height="606"
                            loading="eager"
                            decoding="async"
                        >
                        <div>
                            <strong>RAVENALA</strong>
                            <span>Apprentissage actif et pédagogie universitaire</span>
                        </div>
                    </div>
                    <div class="ravenala-stats">
                        <div>
                            <strong>5</strong>
                            <span>Partenaires</span>
                        </div>
                        <div>
                            <strong>3</strong>
                            <span>Universités bénéficiaires</span>
                        </div>
                        <div>
                            <strong>5</strong>
                            <span>Work packages</span>
                        </div>
                    </div>
                    <div class="ravenala-note">
                        <strong>Coopération universitaire</strong>
                        <p>Une dynamique entre l’Europe, Madagascar et les Comores pour accompagner les enseignants, structurer des référents pédagogiques et diffuser des pratiques actives.</p>
                        <img
                            :src="projectImages.eu"
                            alt="Co-funded by the European Union"
                            width="800"
                            height="178"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                </aside>
            </div>
        </section>

        <section id="about" class="ravenala-section">
            <div class="ravenala-shell ravenala-two-columns">
                <div>
                    <p class="ravenala-kicker">À propos</p>
                    <h2>Un projet de renforcement des capacités pédagogiques</h2>
                    <figure class="ravenala-about-visual">
                        <img
                            :src="projectImages.banner"
                            alt="Présentation synthétique du projet RAVENALA et de ses partenaires"
                            width="599"
                            height="333"
                            loading="lazy"
                            decoding="async"
                        >
                    </figure>
                </div>
                <div class="ravenala-rich-text">
                    <p>
                        RAVENALA rassemble des universités partenaires autour d’un objectif commun : améliorer durablement les pratiques pédagogiques dans l’enseignement supérieur, en favorisant l’innovation, l’inclusion et l’usage raisonné du numérique.
                    </p>
                    <p>
                        Pour l’Université de Mahajanga et l’École Doctorale EDEN, le projet représente une opportunité de renforcer les compétences des enseignants, d’accompagner la transformation des pratiques et de contribuer à la diffusion de ressources pédagogiques adaptées aux réalités locales.
                    </p>
                    <div class="ravenala-beneficiaries">
                        <article v-for="beneficiary in beneficiaries" :key="beneficiary">
                            {{ beneficiary }}
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section id="objectifs" class="ravenala-section ravenala-section-plain">
            <div class="ravenala-shell">
                <div class="ravenala-section-head">
                    <p class="ravenala-kicker">Objectifs</p>
                    <h2>Des priorités claires pour transformer les pratiques</h2>
                    <p>Six axes structurent l’accompagnement des établissements partenaires et la diffusion des pratiques pédagogiques actives.</p>
                </div>
                <div class="ravenala-objectives">
                    <article v-for="(objective, index) in objectives" :key="objective">
                        <span>{{ String(index + 1).padStart(2, '0') }}</span>
                        <p>{{ objective }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="partenaires-ravenala" class="ravenala-section">
            <div class="ravenala-shell">
                <div class="ravenala-section-head">
                    <p class="ravenala-kicker">Partenaires</p>
                    <h2>Un réseau académique international</h2>
                    <p>Le projet réunit cinq partenaires en Europe, à Madagascar et aux Comores, avec l’Université d’Angers comme chef de file.</p>
                </div>
                <div class="ravenala-partners">
                    <article v-for="partner in partners" :key="partner.initials">
                        <div class="ravenala-partner-mark" :class="{ 'has-logo': partner.logo }">
                            <img
                                v-if="partner.logo"
                                :src="partner.logo"
                                :alt="`Logo ${partner.name}`"
                                width="180"
                                height="120"
                                loading="lazy"
                                decoding="async"
                            >
                            <span v-else>{{ partner.initials }}</span>
                        </div>
                        <h3>{{ partner.name }}</h3>
                        <p class="ravenala-country">{{ partner.country }}</p>
                        <p>{{ partner.role }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="work-packages" class="ravenala-section ravenala-section-plain">
            <div class="ravenala-shell">
                <div class="ravenala-section-head">
                    <p class="ravenala-kicker">Work Packages</p>
                    <h2>Cinq lots de travail complémentaires</h2>
                </div>
                <div class="ravenala-work-list">
                    <article v-for="workPackage in workPackages" :key="workPackage.code">
                        <div class="ravenala-wp-code">{{ workPackage.code }}</div>
                        <div>
                            <h3>{{ workPackage.title }}</h3>
                            <p>{{ workPackage.description }}</p>
                            <strong>{{ workPackage.lead }}</strong>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="ravenala-section">
            <div class="ravenala-shell ravenala-role">
                <div class="ravenala-role-title">
                    <p class="ravenala-kicker">Université de Mahajanga</p>
                    <h2>Rôle de l’UMG et de l’EDEN</h2>
                </div>
                <div class="ravenala-role-text">
                    <p>
                        L’Université de Mahajanga participe à la formation, à la pédagogie universitaire, à la transformation numérique pédagogique et à la dissémination des résultats. L’EDEN apporte son ancrage académique et scientifique pour valoriser les ressources produites, accompagner les enseignants et renforcer les pratiques d’apprentissage actif au sein de la communauté universitaire.
                    </p>
                </div>
            </div>
        </section>

        <section class="ravenala-section ravenala-section-plain">
            <div class="ravenala-shell ravenala-final-grid">
                <article class="ravenala-info-card">
                    <p class="ravenala-kicker">Actualités du projet</p>
                    <h2>Suivi et publications</h2>
                    <p>Les actualités du projet RAVENALA seront publiées prochainement.</p>
                </article>

                <article id="contact" class="ravenala-info-card">
                    <p class="ravenala-kicker">Contact</p>
                    <h2>EDEN / Université de Mahajanga</h2>
                    <p>
                        Pour toute information relative au projet RAVENALA et à son articulation avec les activités de l’EDEN, contactez l’École Doctorale Écosystèmes Naturels.
                    </p>
                    <a href="/#contact" class="ravenala-btn ravenala-btn-primary">Aller à la page contact</a>
                </article>
            </div>
        </section>
    </main>

    <Footer
        :site="blocks.site"
        :blocks="blocks.footer"
        :navigation-links="collections.footer_navigation"
        :research-links="collections.footer_research"
        :information-links="collections.footer_information"
        :social-links="collections.footer_socials"
        :logo-variants="performance.siteLogo"
    />
</template>
