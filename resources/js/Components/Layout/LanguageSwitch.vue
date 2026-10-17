<script setup>
import { computed } from 'vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';

const props = defineProps({
    locale: { type: String, default: 'fr' },
    urls: { type: Object, default: () => ({ fr: '/', en: '/en' }) },
    short: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});

const { t } = useT();
const languages = computed(() => [
    { code: 'fr', label: props.short ? 'FR' : 'Français', name: 'Français', url: props.urls?.fr ?? '/' },
    { code: 'en', label: props.short ? 'EN' : 'English', name: 'English', url: props.urls?.en ?? '/en' },
]);
</script>

<template>
    <div :class="cn('inline-flex items-center gap-0.5 rounded-md bg-secondary p-0.5', props.class)" role="group" :aria-label="t('language')">
        <a
            v-for="lang in languages"
            :key="lang.code"
            :href="lang.url"
            :hreflang="lang.code"
            :lang="lang.code"
            :title="lang.name"
            :aria-current="lang.code === locale ? 'true' : undefined"
            :class="cn(
                'inline-flex h-7 min-w-9 items-center justify-center rounded-[5px] px-2 text-xs font-semibold transition-colors outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50',
                lang.code === locale ? 'bg-background text-foreground shadow-xs dark:bg-accent' : 'text-muted-foreground hover:text-foreground',
            )"
        >{{ lang.label }}</a>
    </div>
</template>
