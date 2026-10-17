<script setup>
import { ChevronDown, ChevronUp, Plus, Trash2 } from '@lucide/vue';
import { buttonVariants } from '@/lib/variants';

// Liste éditable d'éléments (ajout, ordre, retrait) ; les champs sont fournis par le slot.
const props = defineProps({
    blank: { type: Function, required: true },
    addLabel: { type: String, default: 'Ajouter' },
    emptyText: { type: String, default: 'Aucun élément.' },
    itemLabel: { type: String, default: 'l’élément' },
    inline: { type: Boolean, default: false },
});

const items = defineModel({ type: Array, default: () => [] });

const add = () => items.value.push(props.blank());
const move = (index, delta) => {
    const [item] = items.value.splice(index, 1);
    items.value.splice(index + delta, 0, item);
};
</script>

<template>
    <div class="grid gap-2">
        <p v-if="!items.length" class="rounded-lg border border-dashed p-4 text-center text-sm text-muted-foreground">{{ emptyText }}</p>
        <ol v-else class="grid gap-2">
            <li v-for="(item, index) in items" :key="index" :class="inline ? 'flex items-start gap-2' : 'grid min-w-0 grid-cols-1 gap-3 rounded-lg border p-3'">
                <div :class="inline ? 'min-w-0 flex-1' : 'contents'"><slot :item="item" :index="index" /></div>
                <div :class="inline ? 'flex shrink-0 items-center' : 'flex items-center justify-end gap-1 border-t pt-2'">
                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" :disabled="index === 0" :aria-label="`Monter ${itemLabel}`" @click="move(index, -1)"><ChevronUp aria-hidden="true" /></button>
                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" :disabled="index === items.length - 1" :aria-label="`Descendre ${itemLabel}`" @click="move(index, 1)"><ChevronDown aria-hidden="true" /></button>
                    <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'icon-sm' })" :aria-label="`Retirer ${itemLabel}`" @click="items.splice(index, 1)"><Trash2 aria-hidden="true" /></button>
                </div>
            </li>
        </ol>
        <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" class="justify-self-start" @click="add"><Plus aria-hidden="true" />{{ addLabel }}</button>
    </div>
</template>
