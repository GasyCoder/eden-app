<script setup>
import { Plus } from '@lucide/vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    label: { type: String, default: 'Ajouter' },
    compact: { type: Boolean, default: false },
    onDark: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});

defineEmits(['click']);
</script>

<template>
    <button
        type="button"
        :class="cn(
            'inline-flex shrink-0 items-center justify-center gap-2 rounded-full border border-dashed text-sm font-medium tracking-normal normal-case transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none',
            onDark ? 'border-white/40 text-white hover:bg-white/10' : 'border-eden-400 bg-eden-50/60 text-eden-700 hover:border-solid hover:bg-eden-50 dark:bg-eden-950/50 dark:text-eden-300',
            compact ? 'size-8' : 'h-9 px-4',
            props.class,
        )"
        :title="label"
        @click.stop.prevent="$emit('click')"
    >
        <Plus class="size-4" aria-hidden="true" />
        <span :class="{ 'sr-only': compact }">{{ label }}</span>
    </button>
</template>
