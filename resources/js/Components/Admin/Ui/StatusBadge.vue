<script setup>
import { computed } from 'vue';
import Badge from '@/Components/ui/Badge.vue';

// Statuts éditoriaux harmonisés pour tous les modules.
const props = defineProps({
    status: { type: String, required: true },
});

const map = {
    published: ['Publié', 'success'],
    live: ['En ligne', 'success'],
    active: ['Active', 'success'],
    scheduled: ['Programmé', 'info'],
    draft: ['Brouillon', 'secondary'],
    completed: ['Terminé', 'success'],
    archived: ['Archivé', 'outline'],
    expired: ['Terminée', 'outline'],
    cancelled: ['Annulé', 'destructive'],
};

const config = computed(() => map[props.status] ?? [props.status, 'secondary']);
</script>

<template>
    <Badge :variant="config[1]" dot>{{ config[0] }}</Badge>
</template>
