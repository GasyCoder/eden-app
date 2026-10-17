<script setup>
import { Pencil, Trash2 } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

// Cadre d'édition d'une carte : titre, champs (slot) et actions homogènes.
const props = defineProps({
    title: { type: String, default: 'Modifier' },
    deletable: { type: Boolean, default: false },
    busy: { type: Boolean, default: false },
    saveLabel: { type: String, default: 'Enregistrer' },
    compact: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});

defineEmits(['save', 'cancel', 'delete']);
</script>

<template>
    <form
        :class="cn('grid w-full gap-4 rounded-xl border border-eden-600 bg-card p-4 text-left text-sm font-normal tracking-normal text-card-foreground normal-case not-italic shadow-[0_0_0_4px] shadow-eden-600/10', props.class)"
        :aria-label="title"
        @submit.prevent="$emit('save')"
        @keydown.esc.prevent="$emit('cancel')"
        @click.stop
    >
        <p class="flex items-center gap-2 text-xs font-semibold text-eden-700 dark:text-eden-300">
            <Pencil class="size-3.5" aria-hidden="true" />{{ title }}
        </p>
        <div :class="cn('grid gap-3', compact ? '' : 'sm:grid-cols-2 sm:gap-x-4')">
            <slot />
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2 border-t pt-3">
            <button v-if="deletable" type="button" :class="cn(buttonVariants({ variant: 'destructive-ghost', size: 'sm' }), 'mr-auto')" :disabled="busy" @click="$emit('delete')">
                <Trash2 aria-hidden="true" />Supprimer
            </button>
            <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" :disabled="busy" @click="$emit('cancel')">Annuler</button>
            <button type="submit" :class="buttonVariants({ size: 'sm' })" :disabled="busy">
                <span v-if="busy" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                {{ busy ? 'Enregistrement…' : saveLabel }}
            </button>
        </div>
    </form>
</template>
