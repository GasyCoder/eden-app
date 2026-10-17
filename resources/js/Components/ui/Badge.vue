<script setup>
import { cn } from '@/lib/utils';
import { badgeVariants } from '@/lib/variants';

const props = defineProps({
    variant: { type: String, default: 'default' },
    dot: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <span :class="cn(badgeVariants({ variant, dot }), props.class)"><slot /></span>
</template>
