<script setup>
import { computed, useId } from 'vue';

// Libellé, aide et erreur reliés au contrôle (slot) pour l'accessibilité.
const props = defineProps({
    label: { type: String, default: '' },
    hint: { type: String, default: '' },
    error: { type: String, default: '' },
    optional: { type: Boolean, default: false },
    id: { type: String, default: null },
    wide: { type: Boolean, default: false },
});

const autoId = useId();
const controlId = computed(() => props.id ?? autoId);
const describedBy = computed(() => [props.hint && `${controlId.value}-hint`, props.error && `${controlId.value}-error`].filter(Boolean).join(' ') || undefined);
</script>

<template>
    <div class="grid min-w-0 content-start gap-2" :class="{ 'sm:col-span-2': wide }">
        <div v-if="label || $slots.aside" class="flex items-center justify-between gap-3">
            <label v-if="label" class="text-sm leading-none font-medium" :for="controlId">
                {{ label }}<span v-if="optional" class="font-normal text-muted-foreground"> (facultatif)</span>
            </label>
            <slot name="aside" />
        </div>
        <slot :id="controlId" :invalid="error ? 'true' : undefined" :described-by="describedBy" />
        <p v-if="hint" :id="`${controlId}-hint`" class="text-xs leading-relaxed text-muted-foreground">{{ hint }}</p>
        <p v-if="error" :id="`${controlId}-error`" class="text-xs font-medium text-destructive" role="alert">{{ error }}</p>
    </div>
</template>
