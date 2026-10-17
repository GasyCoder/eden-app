<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronDown, History, LayoutDashboard, LogOut, Pencil, UserRound } from '@lucide/vue';
import { SwitchRoot, SwitchThumb } from 'reka-ui';
import { computed, onMounted, ref, watch } from 'vue';
import AdminHistoryPanel from '@/Components/Admin/AdminHistoryPanel.vue';

const props = defineProps({
    canEdit: {
        type: Boolean,
        default: false,
    },
    editMode: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:editMode']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const historyOpen = ref(false);

// Barre épinglée (ouverte) ou réduite en pastille, préférence mémorisée
const PIN_KEY = 'eden_admin_pinned';
const pinned = ref(true);

onMounted(() => {
    try {
        const stored = window.localStorage.getItem(PIN_KEY);
        if (stored !== null) {
            pinned.value = stored === '1';
        }
    } catch {
        // localStorage indisponible : on garde la valeur par défaut
    }
});

watch(pinned, (value) => {
    try {
        window.localStorage.setItem(PIN_KEY, value ? '1' : '0');
    } catch {
        // ignore
    }
});

const roleLabel = computed(() => (user.value?.role === 'superadmin' ? 'Super administrateur' : 'Éditeur'));
const userDisplayName = computed(() => user.value?.name || user.value?.email || 'Administrateur');
const userInitial = computed(() => (user.value?.name || user.value?.email || 'A').charAt(0).toUpperCase());

const toggleEditMode = () => {
    emit('update:editMode', !props.editMode);
};

const goProfile = () => {
    router.visit('/me/profile');
};

const logout = () => {
    router.post('/logout', {}, {
        preserveScroll: false,
    });
};
</script>

<template>
    <template v-if="canEdit">
        <div
            v-if="pinned"
            class="admin-bar fixed inset-x-2 bottom-[calc(var(--tabbar-h)+0.5rem)] z-[60] flex items-center justify-between gap-1 rounded-full bg-foreground p-1.5 text-background shadow-2xl md:inset-x-auto md:bottom-[calc(var(--tabbar-h)+1rem)] md:left-1/2 md:w-max md:max-w-[calc(100vw-2rem)] md:-translate-x-1/2 md:justify-start"
            role="toolbar"
            aria-label="Barre d’administration"
        >
            <button class="flex shrink-0 items-center gap-2 rounded-full py-0.5 pr-2.5 pl-0.5 text-left hover:bg-background/10" type="button" :title="`${userDisplayName} (${roleLabel})`" @click="goProfile">
                <span class="grid size-9 place-items-center overflow-hidden rounded-full bg-background/15 text-sm font-semibold">
                    <img v-if="user?.profile_photo_url" :src="user.profile_photo_url" :alt="`Photo de ${userDisplayName}`" class="size-full object-cover" loading="lazy" decoding="async">
                    <template v-else>{{ userInitial }}</template>
                </span>
                <span class="hidden leading-tight lg:grid">
                    <strong class="max-w-36 truncate text-sm font-semibold">{{ userDisplayName }}</strong>
                    <small class="text-xs text-background/65">{{ roleLabel }}</small>
                </span>
            </button>

            <span class="mx-1 hidden h-6 w-px bg-background/20 md:block" aria-hidden="true"></span>

            <label class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full px-3 text-sm font-medium whitespace-nowrap text-background transition-colors hover:bg-background/10 focus-visible:ring-[3px] focus-visible:ring-gold-400/60 focus-visible:outline-none cursor-pointer" :title="editMode ? 'Masquer les crayons d’édition' : 'Afficher les crayons d’édition'">
                <SwitchRoot :model-value="editMode" class="inline-flex h-5 w-9 shrink-0 items-center rounded-full bg-background/25 transition-colors data-[state=checked]:bg-eden-400" @update:model-value="toggleEditMode">
                    <SwitchThumb class="block size-4 translate-x-0.5 rounded-full bg-white shadow transition-transform data-[state=checked]:translate-x-[18px]" />
                </SwitchRoot>
                <span class="hidden md:inline">Outils d’édition</span>
                <span class="sr-only md:hidden">Outils d’édition</span>
            </label>

            <Link href="/admin" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full px-3 text-sm font-medium whitespace-nowrap text-background transition-colors hover:bg-background/10 focus-visible:ring-[3px] focus-visible:ring-gold-400/60 focus-visible:outline-none" title="Tableau de bord : actualités, événements, revues, annonces">
                <LayoutDashboard class="size-[18px]" aria-hidden="true" /><span class="hidden md:inline">Administration</span>
            </Link>
            <button class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full px-3 text-sm font-medium whitespace-nowrap text-background transition-colors hover:bg-background/10 focus-visible:ring-[3px] focus-visible:ring-gold-400/60 focus-visible:outline-none" type="button" title="Historique des modifications" @click="historyOpen = true">
                <History class="size-[18px]" aria-hidden="true" /><span class="hidden md:inline">Historique</span>
            </button>
            <button class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full px-3 text-sm font-medium whitespace-nowrap text-background transition-colors hover:bg-background/10 focus-visible:ring-[3px] focus-visible:ring-gold-400/60 focus-visible:outline-none" type="button" title="Mon profil et les réglages" @click="goProfile">
                <UserRound class="size-[18px]" aria-hidden="true" /><span class="hidden md:inline">Profil</span>
            </button>
            <button class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full px-3 text-sm font-medium whitespace-nowrap text-background transition-colors hover:bg-background/10 focus-visible:ring-[3px] focus-visible:ring-gold-400/60 focus-visible:outline-none hover:bg-laterite-500/25 hover:text-laterite-200" type="button" title="Se déconnecter" @click="logout">
                <LogOut class="size-[18px]" aria-hidden="true" /><span class="hidden md:inline">Déconnexion</span>
            </button>

            <span class="mx-1 hidden h-6 w-px bg-background/20 md:block" aria-hidden="true"></span>

            <button class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full px-3 text-sm font-medium whitespace-nowrap text-background transition-colors hover:bg-background/10 focus-visible:ring-[3px] focus-visible:ring-gold-400/60 focus-visible:outline-none px-2.5" type="button" title="Réduire la barre" aria-label="Réduire la barre" @click="pinned = false">
                <ChevronDown class="size-[18px]" aria-hidden="true" />
            </button>
        </div>

        <button
            v-else
            class="admin-bar fixed bottom-[calc(var(--tabbar-h)+1rem)] left-4 z-[60] rounded-full bg-foreground p-1 shadow-2xl"
            type="button"
            :title="`Afficher la barre d’administration (${userDisplayName})`"
            :aria-label="`Afficher la barre d’administration de ${userDisplayName}`"
            @click="pinned = true"
        >
            <span class="grid size-11 place-items-center overflow-hidden rounded-full bg-background/15 text-sm font-semibold text-background">
                <img v-if="user?.profile_photo_url" :src="user.profile_photo_url" :alt="`Photo de ${userDisplayName}`" class="size-full object-cover" loading="lazy" decoding="async">
                <template v-else>{{ userInitial }}</template>
            </span>
            <span class="absolute -right-0.5 -bottom-0.5 grid size-5 place-items-center rounded-full border-2 border-background bg-gold-500 text-eden-950" aria-hidden="true">
                <Pencil class="size-2.5" />
            </span>
        </button>

        <AdminHistoryPanel :open="historyOpen" @close="historyOpen = false" />
    </template>
</template>
