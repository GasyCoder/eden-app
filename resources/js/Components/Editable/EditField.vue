<script setup>
import { useId } from 'vue';
import { inputClass, textareaClass } from '@/lib/variants';

// Champ libellé utilisé dans les éditeurs de cartes (projets, équipes, liens…)
defineProps({
    label: { type: String, required: true },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    hint: { type: String, default: '' },
    options: { type: Array, default: () => [] },
    rows: { type: Number, default: 3 },
    wide: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const model = defineModel({ type: [String, Number], default: '' });
const id = useId();
</script>

<template>
    <div class="grid min-w-0 content-start gap-1.5" :class="{ 'sm:col-span-2': wide }">
        <label class="text-xs font-medium" :for="id">{{ label }}</label>
        <textarea v-if="type === 'textarea'" :id="id" v-model="model" :class="textareaClass" :rows="rows" :placeholder="placeholder" :disabled="disabled"></textarea>
        <select v-else-if="type === 'select'" :id="id" v-model="model" :class="inputClass" class="h-9" :disabled="disabled">
            <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>
        <input v-else :id="id" v-model="model" :class="inputClass" class="h-9" :type="type" :placeholder="placeholder" :disabled="disabled">
        <span v-if="hint" class="text-xs text-muted-foreground">{{ hint }}</span>
    </div>
</template>
