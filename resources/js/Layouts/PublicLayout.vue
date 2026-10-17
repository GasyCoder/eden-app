<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { ArrowUp } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, provide, ref, watch } from 'vue';
import AdminOnboardingGuide from '@/Components/Admin/AdminOnboardingGuide.vue';
import AdminToolbar from '@/Components/Admin/AdminToolbar.vue';
import AnnouncementCenter from '@/Components/AnnouncementCenter.vue';
import CookieBanner from '@/Components/CookieBanner.vue';
import InstallPrompt from '@/Components/InstallPrompt.vue';
import AnnouncementBar from '@/Components/Layout/AnnouncementBar.vue';
import Footer from '@/Components/Layout/Footer.vue';
import MobileTabBar from '@/Components/Layout/MobileTabBar.vue';
import Navbar from '@/Components/Layout/Navbar.vue';
import OfflineNotice from '@/Components/OfflineNotice.vue';
import Toast from '@/Components/Toast.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';

// Gabarit commun à toutes les pages publiques.
const props = defineProps({
    editable: { type: Boolean, default: true },
});

const page = usePage();
const { t, path } = useT();
const canEdit = computed(() => Boolean(page.props.canEdit));
const editMode = ref(canEdit.value);
const showScrollTop = ref(false);
const menuOpen = ref(false);
const blocks = computed(() => page.props.blocks ?? {});
const collections = computed(() => page.props.collections ?? {});
const logo = computed(() => page.props.performance?.siteLogo ?? {});

provide('blockMeta', computed(() => page.props.blockMeta ?? {}));

watch(canEdit, (value) => {
    editMode.value = value;
});

// Les ancres absentes de la page courante renvoient vers la page qui les contient
// (anciens liens « #organisation », « #projets »… du menu et du pied de page).
const sectionPages = {
    about: 'about',
    mission: 'about',
    direction: 'about',
    organisation: 'about',
    partenaires: 'about',
    formation: 'training',
    recherche: 'research',
    projets: 'research',
    chiffres: 'home',
    contact: 'home',
};

const redirectHash = (hash) => {
    const id = hash.replace(/^#/, '');

    if (!id || document.getElementById(id) || !sectionPages[id]) {
        return false;
    }

    router.visit(`${path(sectionPages[id])}#${id}`);

    return true;
};

const onDocumentClick = (event) => {
    const link = event.target.closest?.('a[href^="#"], a[href^="/#"], a[href^="/en#"]');

    if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey) {
        return;
    }

    if (redirectHash(link.getAttribute('href').replace(/^\/(en)?/, ''))) {
        event.preventDefault();
    }
};

const onScroll = () => {
    showScrollTop.value = window.scrollY > 640;
};

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (window.location.hash) {
        redirectHash(window.location.hash);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    window.removeEventListener('scroll', onScroll);
});

const canEditContent = computed(() => props.editable && editMode.value);
const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });
</script>

<template>
    <AdminToolbar :can-edit="canEdit" v-model:edit-mode="editMode" />
    <AdminOnboardingGuide />
    <Toast />
    <CookieBanner />
    <InstallPrompt />

    <AnnouncementCenter :announcements="page.props.announcements ?? []" :suppress-dialogs="canEdit" />
    <AnnouncementBar :announcements="page.props.announcements ?? []" :contact="blocks.contact" />

    <Navbar
        v-model:menu-open="menuOpen"
        :contact="blocks.contact"
        :blocks="blocks.site"
        :nav="blocks.nav"
        :logo-variants="logo"
        :locale="page.props.locale"
        :language-urls="page.props.languageUrls"
        :can-edit="canEditContent"
    />

    <main id="main">
        <slot :edit-mode="canEditContent" />
    </main>

    <Footer
        :site="blocks.site"
        :blocks="blocks.footer"
        :navigation-links="collections.footer_navigation"
        :research-links="collections.footer_research"
        :information-links="collections.footer_information"
        :social-links="collections.footer_socials"
        :logo-variants="logo"
        :locale="page.props.locale"
        :home-href="path('home')"
        :can-edit="canEditContent"
    />

    <MobileTabBar :menu-open="menuOpen" @menu="menuOpen = true" />
    <OfflineNotice />
    <!-- Espace réservé à la barre d'onglets mobile sous le pied de page -->
    <div class="h-[var(--tabbar-h)] lg:hidden" aria-hidden="true"></div>

    <button
        type="button"
        :class="cn(
            'fixed right-5 z-40 grid size-10 place-items-center rounded-full border bg-background text-foreground shadow-md transition-all hover:bg-accent focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none',
            canEdit ? 'bottom-[calc(var(--tabbar-h)+5.5rem)]' : 'bottom-[calc(var(--tabbar-h)+1.25rem)]',
            showScrollTop ? 'visible translate-y-0 opacity-100' : 'invisible translate-y-2 opacity-0',
        )"
        :aria-label="t('backToTop')"
        :title="t('backToTop')"
        :tabindex="showScrollTop ? 0 : -1"
        @click="scrollToTop"
    >
        <ArrowUp class="size-4" aria-hidden="true" />
    </button>
</template>
