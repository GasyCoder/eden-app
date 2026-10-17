<script setup>
import { Pencil } from '@lucide/vue';

defineProps({
    title: { type: String, default: 'Modifier' },
    disabled: { type: Boolean, default: false },
});

defineEmits(['click']);
</script>

<template>
    <button
        class="ml-1.5 inline-grid size-6 shrink-0 place-items-center rounded-full border border-eden-200 bg-background align-middle text-eden-700 shadow-xs transition-colors not-italic hover:border-eden-600 hover:bg-eden-600 hover:text-white focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:opacity-50 dark:border-eden-800 dark:text-eden-300"
        type="button"
        :title="title"
        :aria-label="title"
        :disabled="disabled"
        @click.stop.prevent="$emit('click')"
    >
        <Pencil class="size-3" aria-hidden="true" />
    </button>
</template>
