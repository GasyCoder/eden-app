<script setup>
import { computed } from 'vue';

// Histogramme horizontal simple (chiffres d'un numéro), sans bibliothèque.
const props = defineProps({
    stats: { type: Array, default: () => [] },
});

const max = computed(() => Math.max(1, ...props.stats.map((stat) => Number(stat.value) || 0)));
const tones = ['bg-eden-600', 'bg-gold-500', 'bg-eden-400', 'bg-laterite-600', 'bg-eden-800', 'bg-gold-300'];
</script>

<template>
    <ul class="grid gap-4">
        <li v-for="(stat, index) in stats" :key="stat.label" class="grid gap-1.5">
            <div class="flex items-baseline justify-between gap-4 text-sm">
                <span>{{ stat.label }}</span>
                <span class="font-semibold tabular-nums">{{ stat.value }}</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-secondary" aria-hidden="true">
                <div :class="['h-full rounded-full transition-[width] duration-700', tones[index % tones.length]]" :style="{ width: `${(Number(stat.value) / max) * 100}%` }"></div>
            </div>
        </li>
    </ul>
</template>
