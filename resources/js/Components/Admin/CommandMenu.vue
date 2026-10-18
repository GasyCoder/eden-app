<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { CalendarDays, CalendarPlus, ExternalLink, FilePlus2, FolderKanban, GalleryHorizontal, LayoutDashboard, LibraryBig, Mail, Megaphone, MegaphoneOff, Newspaper, Search, Settings, Trash2, UserPlus, UserRound } from '@lucide/vue';
import { DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, VisuallyHidden } from 'reka-ui';
import { computed, nextTick, ref, watch } from 'vue';

// Menu de commandes (⌘K / Ctrl K) : navigation et actions rapides au clavier.
const page = usePage();
const open = defineModel('open', { type: Boolean, default: false });
const query = ref('');
const activeIndex = ref(0);
const input = ref(null);

const commands = [
    { group: 'Créer', label: 'Nouvelle actualité', href: '/admin/posts/create', icon: FilePlus2, keywords: 'article post blog' },
    { group: 'Créer', label: 'Nouvel événement', href: '/admin/events/create', icon: CalendarPlus, keywords: 'séminaire soutenance agenda' },
    { group: 'Créer', label: 'Nouvelle revue', href: '/admin/journals/create', icon: LibraryBig, keywords: 'journal publication' },
    { group: 'Créer', label: 'Nouvelle diapositive du héro', href: '/admin/hero/slides/create', icon: GalleryHorizontal, keywords: 'accueil diaporama slide image bannière' },
    { group: 'Créer', label: 'Nouveau projet', href: '/admin/projects/create', icon: FolderKanban, keywords: 'recherche ravenala coopération' },
    { group: 'Créer', label: 'Ajouter des abonnés', href: '/admin/newsletter?ajouter=1', icon: UserPlus, keywords: 'newsletter email import' },
    { group: 'Créer', label: 'Nouvelle annonce', href: '/admin/announcements/create', icon: Megaphone, keywords: 'popup bandeau alerte' },
    { group: 'Aller à', label: 'Tableau de bord', href: '/admin', icon: LayoutDashboard },
    { group: 'Aller à', label: 'Actualités', href: '/admin/posts', icon: Newspaper },
    { group: 'Aller à', label: 'Corbeille', href: '/admin/trash', icon: Trash2, keywords: 'supprimés restaurer récupérer' },
    { group: 'Aller à', label: 'Brouillons d’actualités', href: '/admin/posts?status=draft', icon: Newspaper, keywords: 'draft' },
    { group: 'Aller à', label: 'Événements', href: '/admin/events', icon: CalendarDays },
    { group: 'Aller à', label: 'Revues', href: '/admin/journals', icon: LibraryBig, keywords: 'matoy numéros' },
    { group: 'Aller à', label: 'Héro de l’accueil', href: '/admin/hero', icon: GalleryHorizontal, keywords: 'diaporama slides bannière titre boutons' },
    { group: 'Aller à', label: 'Projets', href: '/admin/projects', icon: FolderKanban, keywords: 'ravenala' },
    { group: 'Aller à', label: 'Newsletter : abonnés', href: '/admin/newsletter', icon: Mail, keywords: 'email inscrits' },
    { group: 'Aller à', label: 'Newsletter : envois', href: '/admin/newsletter/campaigns', icon: Mail, keywords: 'campagnes file attente' },
    { group: 'Aller à', label: 'Paramètres', href: '/admin/settings', icon: Settings, superadmin: true, keywords: 'logo favicon seo référencement réseaux sociaux couleurs contact éditeurs comptes utilisateurs' },
    { group: 'Aller à', label: 'Annonces', href: '/admin/announcements', icon: Megaphone },
    { group: 'Aller à', label: 'Annonces archivées', href: '/admin/announcements?status=archived', icon: MegaphoneOff },
    { group: 'Aller à', label: 'Mon profil', href: '/me/profile', icon: UserRound, keywords: 'mot de passe photo sécurité compte' },
    { group: 'Site', label: 'Voir le site public', href: '/', icon: ExternalLink, external: true, keywords: 'accueil pages édition' },
];

const normalize = (value) => value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

const results = computed(() => {
    const term = normalize(query.value.trim());

    const available = commands.filter((command) => !command.superadmin || page.props.auth?.user?.role === 'superadmin');

    return term ? available.filter((command) => normalize(`${command.label} ${command.keywords ?? ''}`).includes(term)) : available;
});

const groups = computed(() => [...new Set(results.value.map((command) => command.group))]
    .map((group) => ({ group, items: results.value.filter((command) => command.group === group) })));

watch(query, () => {
    activeIndex.value = 0;
});

watch(open, (value) => {
    if (value) {
        query.value = '';
        activeIndex.value = 0;
        nextTick(() => input.value?.focus());
    }
});

const run = (command) => {
    open.value = false;

    if (command.external) {
        window.location.href = command.href;
        return;
    }

    router.visit(command.href);
};

const onKeydown = (event) => {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        activeIndex.value = (activeIndex.value + 1) % Math.max(results.value.length, 1);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        activeIndex.value = (activeIndex.value - 1 + results.value.length) % Math.max(results.value.length, 1);
    } else if (event.key === 'Enter' && results.value[activeIndex.value]) {
        event.preventDefault();
        run(results.value[activeIndex.value]);
    }
};

const indexOf = (command) => results.value.indexOf(command);
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-[100] bg-black/50 data-[state=open]:animate-in data-[state=open]:fade-in-0" />
            <DialogContent class="fixed top-[16%] left-1/2 z-[110] w-[calc(100vw-2rem)] max-w-xl -translate-x-1/2 overflow-hidden rounded-xl border bg-popover text-popover-foreground shadow-2xl outline-none data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95" @keydown="onKeydown">
                <VisuallyHidden>
                    <DialogTitle>Menu de commandes</DialogTitle>
                    <DialogDescription>Recherchez une page ou une action, puis validez avec Entrée.</DialogDescription>
                </VisuallyHidden>
                <div class="flex items-center gap-2 border-b px-4">
                    <Search class="size-4 text-muted-foreground" aria-hidden="true" />
                    <input
                        ref="input"
                        v-model="query"
                        type="text"
                        class="h-12 flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                        placeholder="Rechercher une page ou une action…"
                        role="combobox"
                        aria-expanded="true"
                        aria-controls="command-list"
                        :aria-activedescendant="results[activeIndex] ? `command-${activeIndex}` : undefined"
                        autocomplete="off"
                    >
                    <kbd class="rounded border bg-muted px-1.5 py-0.5 text-[11px] font-medium text-muted-foreground">Échap</kbd>
                </div>
                <div id="command-list" class="max-h-96 overflow-y-auto p-2" role="listbox" aria-label="Résultats">
                    <p v-if="!results.length" class="px-4 py-8 text-center text-sm text-muted-foreground">Aucun résultat pour « {{ query }} ».</p>
                    <div v-for="group in groups" :key="group.group" class="mb-2 last:mb-0" role="group" :aria-label="group.group">
                        <p class="px-2 py-1.5 text-xs font-medium text-muted-foreground">{{ group.group }}</p>
                        <div
                            v-for="command in group.items"
                            :id="`command-${indexOf(command)}`"
                            :key="command.label"
                            :class="['flex h-10 cursor-pointer items-center gap-3 rounded-md px-2 text-sm', indexOf(command) === activeIndex ? 'bg-accent' : '']"
                            role="option"
                            :aria-selected="indexOf(command) === activeIndex ? 'true' : 'false'"
                            @mouseenter="activeIndex = indexOf(command)"
                            @click="run(command)"
                        >
                            <component :is="command.icon" class="size-4 text-muted-foreground" aria-hidden="true" />
                            <span>{{ command.label }}</span>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
