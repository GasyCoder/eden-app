<script setup>
import { Moon, Sun } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

defineProps({
    labeled: {
        type: Boolean,
        default: false,
    },
});

const STORAGE_KEY = 'eden_theme';
const theme = ref('light');

const systemTheme = () => (window.matchMedia?.('(prefers-color-scheme: dark)')?.matches ? 'dark' : 'light');

const applyTheme = (value) => {
    theme.value = value === 'dark' ? 'dark' : 'light';

    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.dataset.theme = theme.value;
    document.documentElement.style.colorScheme = theme.value;

    const themeColor = document.querySelector('meta[name="theme-color"]');
    themeColor?.setAttribute('content', theme.value === 'dark' ? '#0B0D0C' : '#FFFFFF');
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

const { t } = useT();
const label = computed(() => (theme.value === 'dark' ? t('toLight') : t('toDark')));

onMounted(() => {
    try {
        applyTheme(window.localStorage.getItem(STORAGE_KEY) ?? systemTheme());
    } catch {
        applyTheme(systemTheme());
    }
});
</script>

<template>
    <button
        :class="labeled ? buttonVariants({ variant: 'outline', size: 'sm' }) : cn(buttonVariants({ variant: 'ghost', size: 'icon' }), 'text-muted-foreground')"
        type="button"
        :title="label"
        :aria-label="label"
        @click="toggleTheme"
    >
        <Sun v-if="theme === 'dark'" class="size-[18px]" aria-hidden="true" />
        <Moon v-else class="size-[18px]" aria-hidden="true" />
        <span v-if="labeled">{{ theme === 'dark' ? t('lightMode') : t('darkMode') }}</span>
    </button>
</template>
