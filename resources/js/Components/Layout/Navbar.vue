<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableImage from '@/Components/Editable/EditableImage.vue';
import ThemeToggle from '@/Components/Layout/ThemeToggle.vue';

const isOpen = ref(false);

defineProps({
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
});

const toggleNav = () => {
    isOpen.value = !isOpen.value;
};

const closeNav = () => {
    isOpen.value = false;
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
    }
});
</script>

<template>
    <header id="header">
        <div class="container">
            <a href="#" class="logo" @click="closeNav">
                <div class="logo-mark" :class="{ 'has-logo': blocks?.logo }">
                    <img v-if="blocks?.logo" :src="blocks.logo" alt="Logo EDEN-UMG">
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
                <a href="#about" @click="closeNav"><EditableText :value="nav?.about_label" fallback="À propos" content-key="nav.about_label" :can-edit="canEdit" /></a>
                <a href="#organisation" @click="closeNav"><EditableText :value="nav?.organisation_label" fallback="Organisation" content-key="nav.organisation_label" :can-edit="canEdit" /></a>
                <a href="#formation" @click="closeNav"><EditableText :value="nav?.formation_label" fallback="Formation" content-key="nav.formation_label" :can-edit="canEdit" /></a>
                <a href="#recherche" @click="closeNav"><EditableText :value="nav?.recherche_label" fallback="Recherche" content-key="nav.recherche_label" :can-edit="canEdit" /></a>
                <a href="#projets" @click="closeNav"><EditableText :value="nav?.projets_label" fallback="Projets" content-key="nav.projets_label" :can-edit="canEdit" /></a>
                <a href="#contact" @click="closeNav"><EditableText :value="nav?.contact_label" fallback="Contact" content-key="nav.contact_label" :can-edit="canEdit" /></a>

                <a :href="nav?.cta_url ?? '#'" class="nav-cta" @click="closeNav">
                    <EditableText :value="nav?.cta_label" fallback="Banque de thèse" content-key="nav.cta_label" :can-edit="canEdit" />
                </a>
                <span v-if="canEdit" class="eden-admin-link-edit">
                    <EditableText :value="nav?.cta_url" fallback="#" content-key="nav.cta_url" :can-edit="canEdit" type="link" />
                </span>
            </nav>

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
