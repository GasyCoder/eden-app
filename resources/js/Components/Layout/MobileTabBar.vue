<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { CalendarDays, FolderKanban, House, Menu, Newspaper } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted } from 'vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';

// Barre d'onglets mobile (comme une application) : accès direct aux rubriques principales.
defineProps({
    menuOpen: { type: Boolean, default: false },
});

const emit = defineEmits(['menu']);
const page = usePage();
const { t, path } = useT();

const tabs = computed(() => [
    { id: 'home', href: path('home'), label: t('home'), icon: House },
    { id: 'news', href: path('news'), label: t('news'), icon: Newspaper },
    { id: 'events', href: path('events'), label: t('events'), icon: CalendarDays },
    { id: 'projects', href: path('projects'), label: t('projects'), icon: FolderKanban },
]);

const current = computed(() => {
    const url = page.url.replace(/^\/en(?=\/|$)/, '').split(/[?#]/)[0] || '/';
    const map = { news: ['/actualites', '/news'], events: ['/evenements', '/events'], projects: ['/projets', '/projects'] };

    if (url === '/' || url === '') {
        return 'home';
    }

    return Object.keys(map).find((key) => map[key].some((prefix) => url === prefix || url.startsWith(`${prefix}/`))) ?? null;
});

onMounted(() => {
    document.documentElement.dataset.tabbar = '';
});

onBeforeUnmount(() => {
    delete document.documentElement.dataset.tabbar;
});
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-50 border-t bg-background/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-md supports-[backdrop-filter]:bg-background/85 lg:hidden"
        :aria-label="t('mainNav')"
    >
        <ul class="mx-auto grid h-16 max-w-lg grid-cols-5">
            <li v-for="tab in tabs" :key="tab.id">
                <Link
                    :href="tab.href"
                    :class="cn('relative flex h-full flex-col items-center justify-center gap-1 text-[11px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-inset', current === tab.id ? 'text-eden-700 dark:text-eden-300' : 'text-muted-foreground hover:text-foreground')"
                    :aria-current="current === tab.id ? 'page' : undefined"
                >
                    <span v-if="current === tab.id" class="absolute top-0 h-0.5 w-8 rounded-full bg-eden-600" aria-hidden="true"></span>
                    <component :is="tab.icon" class="size-5" :stroke-width="current === tab.id ? 2.25 : 1.75" aria-hidden="true" />
                    <span class="max-w-full truncate px-1">{{ tab.label }}</span>
                </Link>
            </li>
            <li>
                <button
                    type="button"
                    :class="cn('flex h-full w-full flex-col items-center justify-center gap-1 text-[11px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-inset', menuOpen ? 'text-eden-700 dark:text-eden-300' : 'text-muted-foreground')"
                    aria-haspopup="dialog"
                    :aria-expanded="menuOpen ? 'true' : 'false'"
                    @click="emit('menu')"
                >
                    <Menu class="size-5" aria-hidden="true" />
                    <span>{{ t('menu') }}</span>
                </button>
            </li>
        </ul>
    </nav>
</template>
