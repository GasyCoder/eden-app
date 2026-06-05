<script setup>
import { ref } from 'vue';

const isOpen = ref(false);

defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
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
                    <span class="logo-name">{{ blocks?.name ?? 'EDEN-UMG' }}</span>
                    <span class="logo-caption">{{ blocks?.caption ?? 'Université de Mahajanga' }}</span>
                </div>
            </a>

            <nav id="mainNav" :class="{ open: isOpen }">
                <a href="#about" @click="closeNav">À propos</a>
                <a href="#organisation" @click="closeNav">Organisation</a>
                <a href="#formation" @click="closeNav">Formation</a>
                <a href="#recherche" @click="closeNav">Recherche</a>
                <a href="#projets" @click="closeNav">Projets</a>
                <a href="#contact" @click="closeNav">Contact</a>
            </nav>

            <a href="#contact" class="nav-cta">Nous contacter</a>
            <button class="menu-btn" id="menuBtn" aria-label="Menu" type="button" @click="toggleNav">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>
</template>
