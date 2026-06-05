<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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

watch(() => props.open, (open) => {
    if (open) {
        loadHistory();
    }
});
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="eden-history-overlay" role="presentation" @click.self="$emit('close')">
            <aside class="eden-history-panel" aria-label="Historique des modifications">
                <header>
                    <div>
                        <strong>Historique</strong>
                        <span>Dernières modifications de contenu</span>
                    </div>
                    <button type="button" aria-label="Fermer" @click="$emit('close')">×</button>
                </header>

                <div v-if="loading" class="eden-history-empty">Chargement...</div>
                <div v-else-if="error" class="eden-history-empty">{{ error }}</div>
                <div v-else-if="!revisions.length" class="eden-history-empty">Aucune modification enregistrée.</div>

                <ol v-else class="eden-history-list">
                    <li v-for="revision in revisions" :key="revision.id">
                        <div class="eden-history-row">
                            <div>
                                <strong>{{ revision.label }}</strong>
                                <span>{{ revision.detail }} par {{ revision.user }} · {{ revision.created_at }}</span>
                            </div>
                            <div class="eden-history-actions">
                                <button type="button" class="eden-history-secondary" @click="toggleDetails(revision)">
                                    {{ expandedId === revision.id ? 'Masquer' : 'Détails' }}
                                </button>
                                <button
                                    type="button"
                                    :disabled="!revision.can_restore || restoringId === revision.id"
                                    @click="restoreRevision(revision)"
                                >
                                    {{ restoringId === revision.id ? 'Restauration...' : 'Restaurer' }}
                                </button>
                            </div>
                        </div>
                        <div v-if="expandedId === revision.id" class="eden-history-details">
                            <div v-if="!revision.changes?.length" class="eden-history-empty">Aucun détail disponible.</div>
                            <dl v-else>
                                <template v-for="change in revision.changes" :key="change.field">
                                    <dt>{{ change.field }}</dt>
                                    <dd>
                                        <span>Avant</span>
                                        <p>{{ change.before }}</p>
                                    </dd>
                                    <dd>
                                        <span>Après</span>
                                        <p>{{ change.after }}</p>
                                    </dd>
                                </template>
                            </dl>
                        </div>
                    </li>
                </ol>
            </aside>
        </div>
    </Teleport>
</template>
