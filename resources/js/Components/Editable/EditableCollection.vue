<script setup>
defineProps({
    canEdit: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="editable-collection">
        <slot />
    </div>
</template>
