<script setup>
// Bascule FR / EN pour les champs traduisibles ; signale la traduction manquante.
defineProps({
    complete: { type: Object, default: () => ({ fr: true, en: false }) },
});

const locale = defineModel({ type: String, default: 'fr' });
const locales = [
    { code: 'fr', label: 'Français', required: true },
    { code: 'en', label: 'English', required: false },
];
</script>

<template>
    <div class="mb-5 inline-flex items-center gap-0.5 rounded-lg bg-secondary p-1" role="tablist" aria-label="Langue du contenu">
        <button
            v-for="item in locales"
            :key="item.code"
            type="button"
            role="tab"
            :aria-selected="locale === item.code ? 'true' : 'false'"
            :class="['inline-flex h-8 items-center gap-2 rounded-md px-3 text-sm font-medium transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none', locale === item.code ? 'bg-background text-foreground shadow-xs dark:bg-accent' : 'text-muted-foreground hover:text-foreground']"
            @click="locale = item.code"
        >
            {{ item.label }}
            <span v-if="!complete[item.code]" class="size-1.5 rounded-full bg-gold-500" :title="item.required ? 'Obligatoire' : 'Traduction facultative manquante : le français sera affiché'" aria-hidden="true"></span>
            <span v-if="!complete[item.code]" class="sr-only">{{ item.required ? '(à compléter)' : '(traduction manquante)' }}</span>
        </button>
    </div>
</template>
