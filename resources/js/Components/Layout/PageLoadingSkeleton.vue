<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    progress: {
        type: Number,
        default: null,
    },
});
</script>

<template>
    <Transition enter-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150" leave-to-class="opacity-0">
        <div v-if="show" class="fixed inset-0 z-[300] overflow-hidden bg-background" role="status" aria-live="polite" aria-label="Chargement de la page">
            <div class="absolute top-0 left-0 h-0.5 bg-eden-600 transition-[width] duration-200" :style="{ width: `${Math.max(progress ?? 38, 12)}%` }"></div>
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center gap-4 border-b">
                    <span class="h-9 w-36 animate-pulse rounded-lg bg-secondary"></span>
                    <span class="ml-auto hidden h-3 w-1/3 animate-pulse rounded bg-secondary md:block"></span>
                    <span class="h-9 w-28 animate-pulse rounded-md bg-secondary"></span>
                </div>
                <div class="grid items-center gap-10 py-16 md:grid-cols-2">
                    <div class="grid gap-4">
                        <span class="h-10 w-full animate-pulse rounded-md bg-secondary"></span>
                        <span class="h-10 w-3/4 animate-pulse rounded-md bg-secondary"></span>
                        <span class="h-3 w-11/12 animate-pulse rounded bg-secondary"></span>
                        <span class="h-3 w-2/3 animate-pulse rounded bg-secondary"></span>
                    </div>
                    <div class="aspect-[4/3] animate-pulse rounded-2xl bg-secondary"></div>
                </div>
            </div>
        </div>
    </Transition>
</template>
