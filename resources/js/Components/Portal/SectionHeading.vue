<script setup>
import { cn } from '@/lib/utils';

// En-tête de section : titre et chapô, sans ornement.
const props = defineProps({
    align: { type: String, default: 'left' },
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <div :class="cn('max-w-2xl', align === 'center' && 'mx-auto text-center', props.class)">
        <h2 class="text-2xl font-semibold tracking-tight text-balance text-foreground sm:text-3xl">
            <slot />
        </h2>
        <p v-if="$slots.description" class="mt-3 leading-relaxed text-pretty text-muted-foreground">
            <slot name="description" />
        </p>
    </div>
</template>
