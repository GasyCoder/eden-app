<script setup>
import { ChevronDown, ChevronUp } from '@lucide/vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { cn } from '@/lib/utils';

// Outils d'une carte en mode édition : modifier, monter, descendre.
defineProps({
    first: { type: Boolean, default: false },
    last: { type: Boolean, default: false },
    editLabel: { type: String, default: 'Modifier' },
    floating: { type: Boolean, default: false },
});

defineEmits(['edit', 'up', 'down']);

const toolClass = 'inline-grid size-6 place-items-center rounded-full text-muted-foreground transition-colors hover:bg-accent hover:text-foreground disabled:opacity-30 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none';
</script>

<template>
    <span :class="cn('inline-flex shrink-0 items-center gap-0.5 rounded-full border bg-background p-0.5 align-middle shadow-xs [&_button:first-child]:ml-0', floating && 'absolute top-3 right-3 z-10')" @click.stop.prevent>
        <AdminEditButton :title="editLabel" @click="$emit('edit')" />
        <button type="button" :class="toolClass" title="Monter" aria-label="Monter" :disabled="first" @click.stop.prevent="$emit('up')"><ChevronUp class="size-3.5" aria-hidden="true" /></button>
        <button type="button" :class="toolClass" title="Descendre" aria-label="Descendre" :disabled="last" @click.stop.prevent="$emit('down')"><ChevronDown class="size-3.5" aria-hidden="true" /></button>
    </span>
</template>
