<script setup>
import { onMounted, ref } from 'vue';

const STORAGE_KEY = 'eden_theme';
const theme = ref('light');

const applyTheme = (value) => {
    theme.value = value === 'dark' ? 'dark' : 'light';

    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.dataset.theme = theme.value;
    document.documentElement.style.colorScheme = theme.value;

    const themeColor = document.querySelector('meta[name="theme-color"]');
    themeColor?.setAttribute('content', theme.value === 'dark' ? '#0F1712' : '#0F5132');
};

const toggleTheme = () => {
    const next = theme.value === 'dark' ? 'light' : 'dark';
    applyTheme(next);

    try {
        window.localStorage.setItem(STORAGE_KEY, next);
    } catch {
        // localStorage peut être indisponible en navigation privée stricte.
    }
};

onMounted(() => {
    try {
        const stored = window.localStorage.getItem(STORAGE_KEY);
        applyTheme(stored ?? 'light');
    } catch {
        applyTheme('light');
    }
});
</script>

<template>
    <button
        class="theme-toggle"
        type="button"
        :title="theme === 'dark' ? 'Passer en mode clair' : 'Passer en mode sombre'"
        :aria-label="theme === 'dark' ? 'Passer en mode clair' : 'Passer en mode sombre'"
        @click="toggleTheme"
    >
        <svg v-if="theme === 'dark'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
        </svg>
        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 12.8A8.5 8.5 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" />
        </svg>
    </button>
</template>
