<script setup>
import { router } from '@inertiajs/vue3';
import { History, X } from '@lucide/vue';
import { onBeforeUnmount, ref, watch } from 'vue';
import { buttonVariants } from '@/lib/variants';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const revisions = ref([]);
const loading = ref(false);
const error = ref('');
const restoringId = ref(null);
const expandedId = ref(null);

async function loadHistory() {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/admin/content-revisions', {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json();

        if (!response.ok) {
            error.value = payload.message ?? 'Historique indisponible.';
            return;
        }

        revisions.value = payload.revisions ?? [];
        expandedId.value = revisions.value[0]?.id ?? null;
    } catch {
        error.value = 'Historique indisponible.';
    } finally {
        loading.value = false;
    }
}

function restoreRevision(revision) {
    if (!revision.can_restore || !window.confirm('Restaurer cette version ?')) {
        return;
    }

    restoringId.value = revision.id;

    router.post(`/admin/content-revisions/${revision.id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
        onFinish: () => {
            restoringId.value = null;
        },
    });
}

function toggleDetails(revision) {
    expandedId.value = expandedId.value === revision.id ? null : revision.id;
}

const onKeydown = (event) => {
    if (event.key === 'Escape') {
        emit('close');
    }
};

watch(() => props.open, (open) => {
    if (open) {
        loadHistory();
        document.addEventListener('keydown', onKeydown);
        return;
    }

    document.removeEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-[100] bg-black/50 animate-in fade-in-0" role="presentation" @click.self="$emit('close')">
            <aside class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col border-l bg-background shadow-lg animate-in slide-in-from-right" role="dialog" aria-modal="true" aria-labelledby="history-title">
                <header class="flex items-start justify-between gap-4 border-b px-5 py-4">
                    <div>
                        <h2 id="history-title" class="text-base font-semibold">Historique</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Dernières modifications de contenu. Vous pouvez revenir à une version précédente.</p>
                    </div>
                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" aria-label="Fermer l’historique" @click="$emit('close')"><X aria-hidden="true" /></button>
                </header>

                <div class="flex-1 overflow-y-auto p-5">
                    <div v-if="loading" class="grid gap-3" role="status" aria-label="Chargement de l’historique">
                        <span v-for="n in 4" :key="n" class="h-16 animate-pulse rounded-lg bg-secondary"></span>
                    </div>

                    <div v-else-if="error" class="rounded-lg border border-laterite-200 bg-laterite-50 p-4 text-sm text-laterite-700 dark:border-laterite-900 dark:bg-laterite-950 dark:text-laterite-300" role="alert">
                        {{ error }} <button type="button" class="font-semibold underline underline-offset-4" @click="loadHistory">Réessayer</button>
                    </div>

                    <div v-else-if="!revisions.length" class="flex flex-col items-center gap-2 rounded-xl border border-dashed px-6 py-12 text-center">
                        <History class="mb-1 size-5 text-muted-foreground" aria-hidden="true" />
                        <p class="font-semibold">Aucune modification pour l’instant</p>
                        <p class="text-sm text-muted-foreground">Les changements apportés aux textes, images et cartes apparaîtront ici.</p>
                    </div>

                    <ol v-else class="grid">
                        <li v-for="(revision, index) in revisions" :key="revision.id" class="relative ml-1.5 border-l-2 pb-6 pl-5 last:border-transparent">
                            <span :class="['absolute top-1 -left-[7px] size-3 rounded-full border-2 border-eden-600', index === 0 ? 'bg-eden-600' : 'bg-background']" aria-hidden="true"></span>
                            <div class="grid gap-0.5">
                                <strong class="text-sm font-semibold">{{ revision.label }}</strong>
                                <span class="text-sm text-muted-foreground">{{ revision.detail }}, {{ revision.user }}</span>
                                <time class="text-xs text-muted-foreground">{{ revision.created_at }}</time>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'sm' })" :aria-expanded="expandedId === revision.id ? 'true' : 'false'" @click="toggleDetails(revision)">
                                    {{ expandedId === revision.id ? 'Masquer les détails' : 'Voir les détails' }}
                                </button>
                                <button
                                    type="button"
                                    :class="buttonVariants({ variant: 'outline', size: 'sm' })"
                                    :disabled="!revision.can_restore || restoringId === revision.id"
                                    :title="revision.can_restore ? 'Revenir à cette version' : 'Cette version ne peut pas être restaurée'"
                                    @click="restoreRevision(revision)"
                                >
                                    <span v-if="restoringId === revision.id" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                                    {{ restoringId === revision.id ? 'Restauration…' : 'Restaurer' }}
                                </button>
                            </div>
                            <div v-if="expandedId === revision.id" class="mt-3 grid gap-3">
                                <p v-if="!revision.changes?.length" class="text-sm text-muted-foreground">Aucun détail disponible pour cette modification.</p>
                                <div v-for="change in revision.changes ?? []" :key="change.field">
                                    <p class="mb-1.5 text-xs font-semibold text-muted-foreground">{{ change.field }}</p>
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <div class="rounded-md bg-laterite-50 p-3 text-xs dark:bg-laterite-950">
                                            <span class="mb-1 block font-semibold text-laterite-700 dark:text-laterite-300">Avant</span>
                                            <p class="leading-relaxed break-words whitespace-pre-wrap">{{ change.before || 'Vide' }}</p>
                                        </div>
                                        <div class="rounded-md bg-eden-50 p-3 text-xs dark:bg-eden-950">
                                            <span class="mb-1 block font-semibold text-eden-700 dark:text-eden-300">Après</span>
                                            <p class="leading-relaxed break-words whitespace-pre-wrap">{{ change.after || 'Vide' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ol>
                </div>
            </aside>
        </div>
    </Teleport>
</template>
