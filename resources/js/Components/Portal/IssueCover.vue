<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

// Couverture d'un numéro : image téléversée ou couverture typographique aux couleurs
// du logo (reprend la maquette papier de MaToY : lettres rouges et vertes, filets rouges).
const props = defineProps({
    issue: { type: Object, required: true },
    journalTitle: { type: String, default: '' },
    eager: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});

const title = computed(() => props.journalTitle || props.issue.journal?.title || '');
const isMatoy = computed(() => /^matoy$/i.test(title.value.replace(/\s/g, '')));
const letters = [
    { char: 'M', tone: 'text-laterite-600' },
    { char: 'a', tone: 'text-laterite-600' },
    { char: 'T', tone: 'text-eden-600' },
    { char: 'o', tone: 'text-eden-600' },
    { char: 'Y', tone: 'text-laterite-600' },
];
</script>

<template>
    <div :class="cn('relative aspect-[3/4] overflow-hidden rounded-lg bg-gold-50 shadow-sm ring-1 ring-black/5', props.class)">
        <picture v-if="issue.cover_image">
            <source v-for="source in issue.cover_sources?.sources ?? []" :key="source.type" :type="source.type" :srcset="source.srcset" :sizes="issue.cover_sources?.sizes">
            <img :src="issue.cover_image" :alt="`${title} ${issue.label}`" class="size-full object-cover" :loading="eager ? 'eager' : 'lazy'" decoding="async">
        </picture>
        <div v-else class="flex size-full flex-col justify-between p-[9%] text-left text-neutral-900 @container">
            <div>
                <p v-if="isMatoy" class="flex justify-between text-[clamp(1.25rem,18cqw,3.25rem)] leading-none font-extrabold" aria-hidden="true">
                    <span v-for="letter in letters" :key="letter.char" :class="letter.tone">{{ letter.char }}</span>
                </p>
                <p v-else class="text-[clamp(1rem,12cqw,2.5rem)] leading-tight font-extrabold text-eden-700">{{ title }}</p>
                <span v-if="isMatoy" class="sr-only">MaToY</span>
                <p class="mt-2 text-[clamp(0.55rem,5.5cqw,0.9rem)] italic">{{ issue.journal?.frequency ? `${issue.journal.frequency}` : '' }}</p>
            </div>
            <div class="grid gap-1.5 text-right">
                <span class="ml-auto h-[3px] w-3/4 bg-laterite-600"></span>
                <p class="text-[clamp(0.6rem,6cqw,1rem)] font-bold">{{ issue.label }}</p>
                <p v-if="issue.period" class="text-[clamp(0.55rem,5cqw,0.85rem)] text-neutral-600">{{ issue.period }}</p>
                <span class="ml-auto h-[3px] w-3/4 bg-laterite-600"></span>
            </div>
        </div>
    </div>
</template>
