<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    icon: { type: [Object, Function], default: null },
});
</script>

<template>
    <div class="flex flex-col items-center gap-2 rounded-xl border border-dashed px-6 py-12 text-center">
        <span v-if="icon" class="mb-2 grid size-11 place-items-center rounded-lg bg-secondary text-muted-foreground" aria-hidden="true">
            <component :is="icon" class="size-5" />
        </span>
        <p class="text-base font-semibold">{{ title }}</p>
        <p v-if="description" class="max-w-md text-sm text-muted-foreground">{{ description }}</p>
        <div v-if="$slots.default" class="mt-3"><slot /></div>
    </div>
</template>
