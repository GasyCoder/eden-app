<script setup>
// Case à cocher des tableaux (gère l'état intermédiaire de « tout sélectionner »).
defineProps({
    checked: { type: Boolean, default: false },
    indeterminate: { type: Boolean, default: false },
});

defineEmits(['change']);
</script>

<template>
    <input
        type="checkbox"
        class="size-4 shrink-0 cursor-pointer rounded border-input align-middle accent-eden-600 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none"
        :checked="checked"
        :indeterminate="indeterminate"
        @change="$emit('change')"
        @click.stop
    >
</template>
