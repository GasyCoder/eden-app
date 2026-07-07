<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableImage from '@/Components/Editable/EditableImage.vue';
import ThemeToggle from '@/Components/Layout/ThemeToggle.vue';
import LangFlag from '@/Components/Layout/LangFlag.vue';

const isOpen = ref(false);
const languageOpen = ref(false);

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    nav: {
        type: Object,
        default: () => ({}),
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    logoVariants: {
        type: Object,
        default: () => ({}),
    },
    locale: {
        type: String,
        default: 'fr',
    },
    languageUrls: {
        type: Object,
        default: () => ({ fr: '/', en: '/en' }),
    },
    anchorBase: {
        type: String,
        default: '',
    },
    activeSection: {
        type: String,
        default: '',
    },
});

const currentLanguageName = computed(() => (props.locale === 'en' ? 'English' : 'Français'));
const currentLanguageCode = computed(() => (props.locale === 'en' ? 'EN' : 'FR'));
const anchorHref = (id) => `${props.anchorBase}#${id}`;

const languages = computed(() => [
    { code: 'fr', name: 'Français', url: props.languageUrls?.fr ?? '/' },
    { code: 'en', name: 'English', url: props.languageUrls?.en ?? '/en' },
]);

const toggleNav = () => {
    isOpen.value = !isOpen.value;
    languageOpen.value = false;
};

const closeNav = () => {
    isOpen.value = false;
    languageOpen.value = false;
};

const toggleLanguageDropdown = () => {
    languageOpen.value = !languageOpen.value;
};

const closeLanguageDropdown = (event) => {
    if (!event.target.closest?.('.language-switcher')) {
        languageOpen.value = false;
    }
};

// Verrouille le défilement de la page quand le menu mobile est ouvert
watch(isOpen, (open) => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = open ? 'hidden' : '';
    }
});

onBeforeUnmount(() => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
        document.removeEventListener('click', closeLanguageDropdown);
    }
});

onMounted(() => {
    if (typeof document !== 'undefined') {
        document.addEventListener('click', closeLanguageDropdown);
    }
});
</script>

<template>
    <header id="header">
        <div class="container">
            <a href="#" class="logo" :class="{ 'logo--editable': canEdit }" @click="closeNav">
                <div class="logo-mark" :class="{ 'has-logo': blocks?.logo }">
                    <picture v-if="blocks?.logo">
                        <source
                            v-for="source in logoVariants?.sources ?? []"
                            :key="source.type"
                            :type="source.type"
                            :srcset="source.srcset"
                            :sizes="logoVariants?.sizes"
                        >
                        <img
                            :src="blocks.logo"
                            alt="Logo EDEN-UMG"
                            :width="logoVariants?.width ?? 70"
                            :height="logoVariants?.height ?? 70"
                            loading="eager"
                            decoding="async"
                        >
                    </picture>
                    <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                         stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 21V9" />
                        <path d="M12 12.5C12 8.5 9 6.2 4.5 6.2c0 4 3 6.3 7.5 6.3Z" />
                        <path d="M12 10c0-3.6 3-5.8 7.5-5.8 0 3.6-3 5.8-7.5 5.8Z" />
                    </svg>
                    <EditableImage content-key="site.logo" :can-edit="canEdit" />
                </div>
                <div class="logo-text">
                    <span class="logo-name">
                        <EditableText :value="blocks?.name" fallback="EDEN-UMG" content-key="site.name" :can-edit="canEdit" />
                    </span>
                    <span class="logo-caption">
                        <EditableText :value="blocks?.caption" fallback="Université de Mahajanga" content-key="site.caption" :can-edit="canEdit" />
                    </span>
                </div>
            </a>

            <nav id="mainNav" :class="{ open: isOpen }">
                <a :href="anchorHref('about')" :class="{ 'is-active': activeSection === 'about' }" @click="closeNav"><EditableText :value="nav?.about_label" fallback="À propos" content-key="nav.about_label" :can-edit="canEdit" /></a>
                <a :href="anchorHref('organisation')" :class="{ 'is-active': activeSection === 'organisation' }" @click="closeNav"><EditableText :value="nav?.organisation_label" fallback="Organisation" content-key="nav.organisation_label" :can-edit="canEdit" /></a>
                <a :href="anchorHref('formation')" :class="{ 'is-active': activeSection === 'formation' }" @click="closeNav"><EditableText :value="nav?.formation_label" fallback="Formation" content-key="nav.formation_label" :can-edit="canEdit" /></a>
                <a :href="anchorHref('recherche')" :class="{ 'is-active': activeSection === 'recherche' }" @click="closeNav"><EditableText :value="nav?.recherche_label" fallback="Recherche" content-key="nav.recherche_label" :can-edit="canEdit" /></a>
                <a :href="anchorHref('projets')" :class="{ 'is-active': activeSection === 'projets' }" @click="closeNav"><EditableText :value="nav?.projets_label" fallback="Projets" content-key="nav.projets_label" :can-edit="canEdit" /></a>
                <a :href="anchorHref('contact')" :class="{ 'is-active': activeSection === 'contact' }" @click="closeNav"><EditableText :value="nav?.contact_label" fallback="Contact" content-key="nav.contact_label" :can-edit="canEdit" /></a>

                <a :href="nav?.cta_url ?? '#'" class="nav-cta" @click="closeNav">
                    <EditableText :value="nav?.cta_label" fallback="Banque de thèse" content-key="nav.cta_label" :can-edit="canEdit" />
                </a>
                <span v-if="canEdit" class="eden-admin-link-edit">
                    <EditableText :value="nav?.cta_url" fallback="#" content-key="nav.cta_url" :can-edit="canEdit" type="link" />
                </span>
            </nav>

            <div class="language-switcher" :class="{ 'is-open': languageOpen }" aria-label="Choix de langue">
                <button
                    class="language-trigger"
                    type="button"
                    :title="`Langue : ${currentLanguageName}`"
                    :aria-label="`Langue actuelle : ${currentLanguageName}. Ouvrir le choix de langue`"
                    :aria-expanded="languageOpen ? 'true' : 'false'"
                    aria-haspopup="true"
                    @click.stop="toggleLanguageDropdown"
                >
                    <LangFlag :code="locale" />
                    <span class="language-code">{{ currentLanguageCode }}</span>
                    <svg class="language-chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <div v-if="languageOpen" class="language-menu" role="menu">
                    <a
                        v-for="lang in languages"
                        :key="lang.code"
                        class="language-option"
                        :class="{ 'is-active': lang.code === locale }"
                        :href="lang.url"
                        :hreflang="lang.code"
                        :aria-current="lang.code === locale ? 'true' : null"
                        :title="`Afficher en ${lang.name}`"
                        role="menuitem"
                        @click="closeNav"
                    >
                        <LangFlag :code="lang.code" />
                        <span class="language-name">{{ lang.name }}</span>
                        <svg v-if="lang.code === locale" class="language-check" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="m5 10.5 3.5 3.5 6.5-7.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </div>
            </div>

            <ThemeToggle />

            <button
                class="menu-btn"
                :class="{ open: isOpen }"
                id="menuBtn"
                aria-label="Menu"
                aria-controls="mainNav"
                :aria-expanded="isOpen ? 'true' : 'false'"
                type="button"
                @click="toggleNav"
            >
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>
</template>
