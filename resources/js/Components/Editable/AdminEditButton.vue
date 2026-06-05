<script setup>
defineProps({
    title: {
        type: String,
        default: 'Modifier',
    },
});

defineEmits(['click']);
</script>

<template>
    <button class="admin-edit-button" type="button" :title="title" :aria-label="title" @click.stop.prevent="$emit('click')">
        ✎
    </button>
</template>
