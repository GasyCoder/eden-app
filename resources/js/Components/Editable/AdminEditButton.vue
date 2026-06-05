<script setup>
defineProps({
    title: {
        type: String,
        default: 'Modifier',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['click']);
</script>

<template>
    <button class="admin-edit-button" type="button" :title="title" :aria-label="title" :disabled="disabled" @click.stop.prevent="$emit('click')">
        ✎
    </button>
</template>
