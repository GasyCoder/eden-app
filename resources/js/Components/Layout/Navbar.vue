<script setup>
import { ref } from 'vue';
import EditableText from '@/Components/Editable/EditableText.vue';

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
</script>

<template>
    <header id="header">
        <div class="container">
            <a href="#" class="logo" @click="closeNav">
                <div class="logo-mark">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
                        <path d="M11 2C7 4.5 3 10 6 17C8 21 11.5 22 15 19.5C20 16 20 6 11 2Z" fill="rgba(255,255,255,0.9)" />
                        <path d="M11 2C12.5 7.5 13 14 10 20" stroke="#3f6212" stroke-width="1.2" fill="none" stroke-linecap="round" />
                    </svg>
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
            </nav>

            <a :href="nav?.cta_url ?? '#contact'" class="nav-cta">
                <EditableText :value="nav?.cta_label" fallback="Nous contacter" content-key="nav.cta_label" :can-edit="canEdit" />
            </a>
            <span v-if="canEdit" class="eden-admin-link-edit">
                <EditableText :value="nav?.cta_url" fallback="#contact" content-key="nav.cta_url" :can-edit="canEdit" type="link" />
            </span>
            <button class="menu-btn" id="menuBtn" aria-label="Menu" type="button" @click="toggleNav">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>
</template>
