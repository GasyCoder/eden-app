<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CalendarDays, ChevronsUpDown, ExternalLink, FolderKanban, GalleryHorizontal, LayoutDashboard, LibraryBig, LogOut, Mail, Megaphone, Menu, Newspaper, PanelsTopLeft, Search, Settings, Trash2, UserRound, UsersRound } from '@lucide/vue';
import { DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuPortal, DropdownMenuRoot, DropdownMenuSeparator, DropdownMenuTrigger } from 'reka-ui';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import CommandMenu from '@/Components/Admin/CommandMenu.vue';
import ThemeToggle from '@/Components/Layout/ThemeToggle.vue';
import Toast from '@/Components/Toast.vue';
import BrandStripe from '@/Components/Layout/BrandStripe.vue';
import Sheet from '@/Components/ui/Sheet.vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

// Gabarit de l'espace d'administration : barre latérale, en-tête, menu de commandes.
defineProps({
    title: { type: String, required: true },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});
const roleLabel = computed(() => (user.value.role === 'superadmin' ? 'Admin' : 'Éditeur'));
const initial = computed(() => (user.value.name || user.value.email || 'A').charAt(0).toUpperCase());
const mobileOpen = ref(false);
const commandOpen = ref(false);
const isMac = ref(false);

const isSuperAdmin = computed(() => user.value.role === 'superadmin');
const sections = computed(() => [
    { label: 'Général', items: [{ label: 'Tableau de bord', href: '/admin', icon: LayoutDashboard, match: /^\/admin\/?$/ }] },
    { label: 'Contenus', items: [
        { label: 'Héro de l’accueil', href: '/admin/hero', icon: GalleryHorizontal, match: /^\/admin\/hero/ },
        { label: 'Actualités', href: '/admin/posts', icon: Newspaper, match: /^\/admin\/posts/ },
        { label: 'Événements', href: '/admin/events', icon: CalendarDays, match: /^\/admin\/events/ },
        { label: 'Projets', href: '/admin/projects', icon: FolderKanban, match: /^\/admin\/projects/ },
        { label: 'Revues', href: '/admin/journals', icon: LibraryBig, match: /^\/admin\/journals/ },
        { label: 'Annonces', href: '/admin/announcements', icon: Megaphone, match: /^\/admin\/announcements/ },
        { label: 'Corbeille', href: '/admin/trash', icon: Trash2, match: /^\/admin\/trash/ },
    ] },
    { label: 'Communication', items: [
        { label: 'Newsletter', href: '/admin/newsletter', icon: Mail, match: /^\/admin\/newsletter/ },
    ] },
    ...(isSuperAdmin.value ? [{ label: 'Administration', items: [
        { label: 'Utilisateurs', href: '/admin/users', icon: UsersRound, match: /^\/admin\/users/ },
    ] }] : []),
    { label: 'Site', items: [
        ...(isSuperAdmin.value ? [{ label: 'Paramètres', href: '/admin/settings', icon: Settings, match: /^\/admin\/settings/ }] : []),
        { label: 'Pages et textes du site', href: '/', icon: PanelsTopLeft, hint: 'Édition directe sur les pages' },
        { label: 'Mon profil', href: '/me/profile', icon: UserRound, match: /^\/me\/profile/ },
    ] },
]);

const currentPath = computed(() => page.url.split('?')[0]);
const isActive = (item) => item.match?.test(currentPath.value);
const navLink = (item) => cn(
    'flex h-9 items-center gap-2.5 rounded-md px-2.5 text-sm font-medium transition-colors outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 [&_svg]:size-4 [&_svg]:shrink-0',
    isActive(item) ? 'bg-background text-foreground shadow-xs ring-1 ring-border [&_svg]:text-eden-600 dark:[&_svg]:text-eden-400' : 'text-muted-foreground hover:bg-accent hover:text-foreground',
);

const logout = () => router.post('/logout');

const onKeydown = (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        commandOpen.value = !commandOpen.value;
    }
};

onMounted(() => {
    isMac.value = /mac|iphone|ipad/i.test(navigator.platform || navigator.userAgent);
    document.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Head :title="title" />

    <div class="grid min-h-dvh bg-background lg:grid-cols-[16rem_minmax(0,1fr)]">
        <a href="#admin-main" class="fixed top-3 left-3 z-[200] -translate-y-24 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground focus:translate-y-0">Aller au contenu</a>

        <aside class="sticky top-0 hidden h-dvh flex-col gap-4 overflow-y-auto border-r bg-muted px-3 pb-3 lg:flex" aria-label="Navigation de l’administration">
            <BrandStripe class="-mx-3" />
            <Link href="/admin" class="flex items-center gap-2.5 rounded-md px-1.5 py-1 outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50">
                <span class="grid size-9 place-items-center rounded-lg bg-eden-600 text-white" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21V9" /><path d="M12 12.5C12 8.5 9 6.2 4.5 6.2c0 4 3 6.3 7.5 6.3Z" /><path d="M12 10c0-3.6 3-5.8 7.5-5.8 0 3.6-3 5.8-7.5 5.8Z" /></svg>
                </span>
                <span class="grid leading-tight">
                    <span class="text-sm font-bold">EDEN</span>
                    <span class="text-xs text-muted-foreground">Administration</span>
                </span>
            </Link>

            <button type="button" class="flex h-9 items-center gap-2 rounded-md border bg-background px-2.5 text-sm text-muted-foreground shadow-xs outline-none hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50" @click="commandOpen = true">
                <Search class="size-4" aria-hidden="true" />
                <span class="flex-1 text-left">Rechercher…</span>
                <kbd class="rounded border bg-muted px-1.5 text-[11px] font-medium">{{ isMac ? '⌘' : 'Ctrl' }} K</kbd>
            </button>

            <nav class="grid flex-1 content-start gap-4">
                <div v-for="section in sections" :key="section.label" class="grid gap-0.5">
                    <p class="px-2.5 pb-1 text-xs font-medium text-muted-foreground">{{ section.label }}</p>
                    <Link v-for="item in section.items" :key="item.href" :href="item.href" :class="navLink(item)" :aria-current="isActive(item) ? 'page' : undefined">
                        <component :is="item.icon" aria-hidden="true" /><span>{{ item.label }}</span>
                    </Link>
                </div>
            </nav>

            <DropdownMenuRoot>
                <DropdownMenuTrigger class="flex w-full items-center gap-2.5 rounded-md p-1.5 text-left outline-none hover:bg-accent focus-visible:ring-[3px] focus-visible:ring-ring/50 data-[state=open]:bg-accent">
                    <span class="grid size-8 shrink-0 place-items-center overflow-hidden rounded-md border bg-background text-xs font-semibold">
                        <img v-if="user.profile_photo_url" :src="user.profile_photo_url" alt="" class="size-full object-cover">
                        <template v-else>{{ initial }}</template>
                    </span>
                    <span class="grid min-w-0 flex-1 leading-tight">
                        <strong class="truncate text-sm font-semibold">{{ user.name }}</strong>
                        <small class="text-xs text-muted-foreground">{{ roleLabel }}</small>
                    </span>
                    <ChevronsUpDown class="size-4 text-muted-foreground" aria-hidden="true" />
                </DropdownMenuTrigger>
                <DropdownMenuPortal>
                    <DropdownMenuContent side="top" align="start" :side-offset="6" class="z-[120] min-w-56 rounded-md border bg-popover p-1 shadow-md data-[state=open]:animate-in data-[state=open]:fade-in-0">
                        <DropdownMenuLabel class="px-2 py-1.5 text-xs text-muted-foreground">{{ user.email }}</DropdownMenuLabel>
                        <DropdownMenuItem as-child><Link href="/me/profile" class="flex items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none data-[highlighted]:bg-accent"><UserRound class="size-4 text-muted-foreground" aria-hidden="true" />Mon profil</Link></DropdownMenuItem>
                        <DropdownMenuItem as-child><a href="/" class="flex items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none data-[highlighted]:bg-accent"><ExternalLink class="size-4 text-muted-foreground" aria-hidden="true" />Voir le site</a></DropdownMenuItem>
                        <DropdownMenuSeparator class="-mx-1 my-1 h-px bg-border" />
                        <DropdownMenuItem class="flex items-center gap-2 rounded-sm px-2 py-1.5 text-sm text-destructive outline-none data-[highlighted]:bg-accent" @select="logout"><LogOut class="size-4" aria-hidden="true" />Déconnexion</DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenuPortal>
            </DropdownMenuRoot>
        </aside>

        <div class="flex min-w-0 flex-col">
            <header class="sticky top-0 z-50 flex h-14 items-center gap-3 border-b bg-background/90 px-3 backdrop-blur-md sm:px-6">
                <button type="button" :class="cn(buttonVariants({ variant: 'ghost', size: 'icon-sm' }), 'lg:hidden')" aria-label="Ouvrir la navigation" @click="mobileOpen = true">
                    <Menu aria-hidden="true" />
                </button>
                <div class="min-w-0 flex-1 truncate text-sm font-medium text-muted-foreground"><slot name="breadcrumb">{{ title }}</slot></div>
                <div class="flex items-center gap-1">
                    <button type="button" :class="cn(buttonVariants({ variant: 'ghost', size: 'icon-sm' }), 'lg:hidden')" aria-label="Rechercher (Ctrl K)" @click="commandOpen = true"><Search aria-hidden="true" /></button>
                    <a href="/" :class="cn(buttonVariants({ variant: 'ghost', size: 'sm' }), 'hidden sm:inline-flex')"><ExternalLink aria-hidden="true" />Voir le site</a>
                    <ThemeToggle />
                </div>
            </header>

            <main id="admin-main" class="mx-auto w-full max-w-7xl px-4 py-6 pb-16 sm:px-6">
                <slot />
            </main>
        </div>
    </div>

    <Sheet v-model:open="mobileOpen" title="Administration" side="left">
        <nav class="grid gap-4">
            <div v-for="section in sections" :key="section.label" class="grid gap-0.5">
                <p class="px-2.5 pb-1 text-xs font-medium text-muted-foreground">{{ section.label }}</p>
                <Link v-for="item in section.items" :key="item.href" :href="item.href" :class="navLink(item)" @click="mobileOpen = false">
                    <component :is="item.icon" aria-hidden="true" /><span>{{ item.label }}</span>
                </Link>
            </div>
        </nav>
        <template #footer>
            <button type="button" :class="cn(buttonVariants({ variant: 'outline' }), 'w-full')" @click="logout"><LogOut aria-hidden="true" />Déconnexion</button>
        </template>
    </Sheet>

    <CommandMenu v-model:open="commandOpen" />
    <Toast />
</template>
