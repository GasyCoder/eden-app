<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    backHref: { type: String, default: null },
    backLabel: { type: String, default: 'Retour' },
});
</script>

<template>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div class="grid min-w-0 gap-1.5">
            <Link v-if="backHref" :href="backHref" class="inline-flex w-fit items-center gap-1.5 text-sm font-medium text-muted-foreground hover:text-foreground"><ArrowLeft class="size-3.5" aria-hidden="true" />{{ backLabel }}</Link>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold tracking-tight break-words">{{ title }}</h1>
                <slot name="badges" />
            </div>
            <p v-if="description" class="max-w-2xl text-sm text-muted-foreground">{{ description }}</p>
        </div>
        <div v-if="$slots.actions" class="flex w-full flex-wrap gap-2 sm:w-auto [&>*]:flex-1 sm:[&>*]:flex-none"><slot name="actions" /></div>
    </div>
</template>
