<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, CalendarDays, ChevronDown, FlaskConical, FolderKanban, GraduationCap, Handshake, Info, Mail, Menu, Newspaper, Phone } from '@lucide/vue';
import { DropdownMenuContent, DropdownMenuItem, DropdownMenuPortal, DropdownMenuRoot, DropdownMenuTrigger } from 'reka-ui';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import EditableImage from '@/Components/Editable/EditableImage.vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import BrandStripe from '@/Components/Layout/BrandStripe.vue';
import LanguageSwitch from '@/Components/Layout/LanguageSwitch.vue';
import ThemeToggle from '@/Components/Layout/ThemeToggle.vue';
import Sheet from '@/Components/ui/Sheet.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants, containerClass } from '@/lib/variants';

const props = defineProps({
    blocks: { type: Object, default: () => ({}) },
    nav: { type: Object, default: () => ({}) },
    canEdit: { type: Boolean, default: false },
    logoVariants: { type: Object, default: () => ({}) },
    locale: { type: String, default: 'fr' },
    languageUrls: { type: Object, default: () => ({ fr: '/', en: '/en' }) },
    contact: { type: Object, default: () => ({}) },
});

const page = usePage();
const { t, path } = useT();
// Menu mobile, aussi ouvert depuis la barre d'onglets (PublicLayout)
const mobileOpen = defineModel('menuOpen', { type: Boolean, default: false });
const researchOpen = ref(true);
const scrolled = ref(false);

const links = computed(() => [
    { id: 'about', href: path('about'), key: 'about_label', label: t('about'), icon: Info },
    { id: 'training', href: path('training'), key: 'formation_label', label: t('training'), icon: GraduationCap },
    { id: 'research', key: 'recherche_label', label: t('research'), icon: FlaskConical, children: [
        { id: 'research', href: path('research'), label: t('researchThemes'), description: t('researchThemesDesc'), icon: FlaskConical },
        { id: 'projects', href: path('projects'), label: t('projects'), description: t('projectsDesc'), icon: FolderKanban },
        { id: 'journals', href: path('journals'), label: t('journals'), description: t('journalsDesc'), icon: BookOpen },
        { id: 'ravenala', href: path('ravenala'), label: t('ravenala'), description: t('ravenalaDesc'), icon: Handshake },
    ] },
    { id: 'news', href: path('news'), label: t('news'), icon: Newspaper },
    { id: 'events', href: path('events'), label: t('events'), icon: CalendarDays },
    { id: 'contact', href: path('contact'), key: 'contact_label', label: t('contact'), icon: Mail },
]);

const sectionOf = (url) => {
    const clean = url.replace(/^\/en(?=\/|$)/, '').split(/[?#]/)[0];
    const prefixes = {
        about: ['/a-propos', '/about'],
        training: ['/formation', '/training'],
        research: ['/recherche', '/research', '/revues', '/journals', '/projets', '/projects'],
        news: ['/actualites', '/news'],
        events: ['/evenements', '/events'],
    };

    return Object.keys(prefixes).find((key) => prefixes[key].some((prefix) => clean === prefix || clean.startsWith(`${prefix}/`))) ?? null;
};

const active = computed(() => sectionOf(page.url));

const navLink = (isActive) => cn(
    'relative inline-flex h-9 items-center gap-1 rounded-md px-3 text-sm font-medium whitespace-nowrap transition-colors outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50',
    isActive ? 'text-foreground after:absolute after:inset-x-3 after:-bottom-[15px] after:h-0.5 after:rounded-full after:bg-eden-600' : 'text-muted-foreground hover:bg-accent hover:text-foreground data-[state=open]:bg-accent data-[state=open]:text-foreground',
);

const onScroll = () => {
    scrolled.value = window.scrollY > 4;
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});

onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <a href="#main" class="fixed top-3 left-3 z-[200] -translate-y-24 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground focus:translate-y-0">{{ t('skip') }}</a>

    <header :class="cn('sticky top-0 z-50 border-b bg-background/90 backdrop-blur-md transition-colors supports-[backdrop-filter]:bg-background/80', scrolled ? 'border-border' : 'border-transparent')">
        <BrandStripe />
        <div :class="cn(containerClass, 'flex h-16 items-center gap-6')">
            <Link :href="path('home')" class="flex min-w-0 shrink-0 items-center gap-3 rounded-md outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50">
                <span :class="cn('relative grid size-11 shrink-0 place-items-center rounded-lg', blocks?.logo ? 'bg-white ring-1 ring-border' : 'bg-eden-600 text-white')">
                    <picture v-if="blocks?.logo" class="size-full p-0.5">
                        <source v-for="source in logoVariants?.sources ?? []" :key="source.type" :type="source.type" :srcset="source.srcset" :sizes="logoVariants?.sizes">
                        <img :src="blocks.logo" alt="Logo EDEN" :width="logoVariants?.width ?? 70" :height="logoVariants?.height ?? 70" class="size-full object-contain" loading="eager" decoding="async">
                    </picture>
                    <svg v-else class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 21V9" /><path d="M12 12.5C12 8.5 9 6.2 4.5 6.2c0 4 3 6.3 7.5 6.3Z" /><path d="M12 10c0-3.6 3-5.8 7.5-5.8 0 3.6-3 5.8-7.5 5.8Z" />
                    </svg>
                    <EditableImage content-key="site.logo" :can-edit="canEdit" compact />
                </span>
                <span class="grid min-w-0 leading-tight">
                    <span class="text-[15px] font-bold tracking-tight text-foreground">
                        <EditableText :value="blocks?.name" fallback="EDEN" content-key="site.name" :can-edit="canEdit" />
                    </span>
                    <span class="truncate text-xs text-muted-foreground max-[400px]:hidden">
                        <EditableText :value="blocks?.caption" fallback="Université de Mahajanga" content-key="site.caption" :can-edit="canEdit" />
                    </span>
                </span>
            </Link>

            <nav class="ml-auto hidden items-center gap-0.5 xl:flex" :aria-label="t('mainNav')">
                <template v-for="link in links" :key="link.id">
                    <DropdownMenuRoot v-if="link.children" :modal="false">
                        <DropdownMenuTrigger :class="navLink(active === link.id)">
                            <EditableText :value="nav?.[link.key]" :fallback="link.label" :content-key="`nav.${link.key}`" :can-edit="canEdit" />
                            <ChevronDown class="size-3.5 transition-transform in-data-[state=open]:rotate-180" aria-hidden="true" />
                        </DropdownMenuTrigger>
                        <DropdownMenuPortal>
                            <DropdownMenuContent align="start" :side-offset="10" class="z-[120] w-80 rounded-lg border bg-popover p-1.5 text-popover-foreground shadow-lg data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95">
                                <DropdownMenuItem v-for="child in link.children" :key="child.id" as-child>
                                    <Link :href="child.href" class="flex cursor-default items-start gap-3 rounded-md p-2.5 outline-none data-[highlighted]:bg-accent">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-md bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300">
                                            <component :is="child.icon" class="size-4" aria-hidden="true" />
                                        </span>
                                        <span class="grid gap-0.5">
                                            <span class="text-sm font-medium">{{ child.label }}</span>
                                            <span class="text-xs leading-snug text-muted-foreground">{{ child.description }}</span>
                                        </span>
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenuPortal>
                    </DropdownMenuRoot>
                    <component
                        :is="link.id === 'contact' ? 'a' : Link"
                        v-else
                        :href="link.href"
                        :class="navLink(active === link.id)"
                        :aria-current="active === link.id ? 'page' : undefined"
                    >
                        <EditableText v-if="link.key" :value="nav?.[link.key]" :fallback="link.label" :content-key="`nav.${link.key}`" :can-edit="canEdit" />
                        <template v-else>{{ link.label }}</template>
                    </component>
                </template>
            </nav>

            <div class="ml-auto flex shrink-0 items-center gap-2 xl:ml-0">
                <LanguageSwitch class="hidden xl:inline-flex" :locale="locale" :urls="languageUrls" short />
                <ThemeToggle />
                <a :href="nav?.cta_url ?? '#'" :class="cn(buttonVariants({ size: 'sm' }), 'hidden xl:inline-flex')">
                    <EditableText :value="nav?.cta_label" :fallback="t('thesisBank')" content-key="nav.cta_label" :can-edit="canEdit" />
                </a>
                <span v-if="canEdit" class="hidden xl:inline-flex">
                    <EditableText :value="nav?.cta_url" fallback="#" content-key="nav.cta_url" :can-edit="canEdit" type="link" />
                </span>
                <button
                    type="button"
                    :class="cn(buttonVariants({ variant: 'ghost', size: 'icon' }), 'xl:hidden')"
                    :aria-label="t('openMenu')"
                    aria-haspopup="dialog"
                    :aria-expanded="mobileOpen ? 'true' : 'false'"
                    @click="mobileOpen = true"
                >
                    <Menu class="size-5" aria-hidden="true" />
                </button>
            </div>
        </div>
    </header>

    <Sheet v-model:open="mobileOpen" :title="blocks?.name || 'EDEN'" :description="blocks?.caption || 'Université de Mahajanga'" :close-label="t('closeMenu')">
        <nav class="-mx-2 grid gap-0.5" :aria-label="t('mainNav')">
            <template v-for="link in links" :key="link.id">
                <div v-if="link.children" class="grid">
                    <button
                        type="button"
                        :class="cn('flex min-h-12 items-center gap-3 rounded-lg px-3 text-base font-medium hover:bg-accent', active === link.id && 'text-eden-700 dark:text-eden-300')"
                        :aria-expanded="researchOpen ? 'true' : 'false'"
                        @click="researchOpen = !researchOpen"
                    >
                        <component :is="link.icon" class="size-5 text-muted-foreground" aria-hidden="true" />
                        <span class="flex-1 text-left">{{ nav?.[link.key] || link.label }}</span>
                        <ChevronDown :class="cn('size-4 text-muted-foreground transition-transform', researchOpen && 'rotate-180')" aria-hidden="true" />
                    </button>
                    <div v-show="researchOpen" class="ml-5 grid gap-0.5 border-l pl-3">
                        <Link v-for="child in link.children" :key="child.id" :href="child.href" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground" @click="mobileOpen = false">
                            <component :is="child.icon" class="size-4 text-eden-600 dark:text-eden-400" aria-hidden="true" />{{ child.label }}
                        </Link>
                    </div>
                </div>
                <component
                    :is="link.id === 'contact' ? 'a' : Link"
                    v-else
                    :href="link.href"
                    :class="cn('flex min-h-12 items-center gap-3 rounded-lg px-3 text-base font-medium hover:bg-accent', active === link.id && 'bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300')"
                    :aria-current="active === link.id ? 'page' : undefined"
                    @click="mobileOpen = false"
                >
                    <component :is="link.icon" :class="cn('size-5', active === link.id ? 'text-eden-600 dark:text-eden-400' : 'text-muted-foreground')" aria-hidden="true" />
                    {{ (link.key && nav?.[link.key]) || link.label }}
                </component>
            </template>
        </nav>

        <div v-if="contact?.email || contact?.phone" class="mt-6 grid gap-1 border-t pt-4 text-sm">
            <a v-if="contact?.email" :href="`mailto:${contact.email}`" class="flex min-h-10 items-center gap-3 rounded-lg px-1 text-muted-foreground hover:text-foreground"><Mail class="size-4 text-gold-600" aria-hidden="true" />{{ contact.email }}</a>
            <a v-if="contact?.phone && !/x/i.test(contact.phone)" :href="`tel:${contact.phone.replace(/[^\d+]/g, '')}`" class="flex min-h-10 items-center gap-3 rounded-lg px-1 text-muted-foreground hover:text-foreground"><Phone class="size-4 text-gold-600" aria-hidden="true" />{{ contact.phone }}</a>
        </div>

        <template #footer>
            <div class="grid gap-3 pb-[env(safe-area-inset-bottom)]">
                <a :href="nav?.cta_url ?? '#'" :class="cn(buttonVariants({ size: 'lg' }), 'w-full')">{{ nav?.cta_label || t('thesisBank') }}</a>
                <div class="flex items-center justify-between gap-3">
                    <LanguageSwitch :locale="locale" :urls="languageUrls" />
                    <ThemeToggle labeled />
                </div>
            </div>
        </template>
    </Sheet>
</template>
