<script setup>
defineProps({
    title: {
        type: String,
        default: 'Modifier',
    },
});
</script>

<template>
    <button class="admin-edit-button" type="button" :title="title" :aria-label="title">
        ✎
    </button>
</template>
