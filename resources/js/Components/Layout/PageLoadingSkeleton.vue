<script setup>
defineProps({
    variant: {
        type: String,
        default: 'public',
        validator: (value) => ['public', 'admin'].includes(value),
    },
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
        <div v-if="show" class="fixed inset-0 z-[300] overflow-hidden bg-background" role="status" aria-live="polite" :aria-label="variant === 'admin' ? 'Chargement de l’administration' : 'Chargement de la page'">
            <div class="absolute top-0 left-0 h-0.5 bg-eden-600 transition-[width] duration-200" :style="{ width: `${Math.max(progress ?? 38, 12)}%` }"></div>
            <template v-if="variant === 'admin'">
                <div class="grid min-h-dvh lg:grid-cols-[16rem_minmax(0,1fr)]">
                    <aside class="fixed inset-y-0 left-0 hidden w-64 flex-col gap-5 border-r bg-muted px-3 pb-4 lg:flex" aria-hidden="true">
                        <div class="-mx-3 h-1 bg-eden-600"></div>
                        <div class="flex items-center gap-2.5 px-1.5">
                            <span class="size-9 animate-pulse rounded-lg bg-secondary"></span>
                            <span class="grid gap-2"><span class="h-3 w-12 animate-pulse rounded bg-secondary"></span><span class="h-2.5 w-24 animate-pulse rounded bg-secondary"></span></span>
                        </div>
                        <span class="h-9 animate-pulse rounded-md bg-secondary"></span>
                        <div class="grid gap-5">
                            <div v-for="group in 4" :key="group" class="grid gap-2">
                                <span class="ml-2 h-2 w-16 animate-pulse rounded bg-secondary"></span>
                                <span v-for="item in (group === 2 ? 5 : 2)" :key="item" class="flex h-9 items-center gap-2.5 rounded-md px-2.5">
                                    <span class="size-4 animate-pulse rounded bg-secondary"></span><span class="h-3 w-28 animate-pulse rounded bg-secondary"></span>
                                </span>
                            </div>
                        </div>
                        <span class="mt-auto h-10 animate-pulse rounded-md bg-secondary"></span>
                    </aside>

                    <div class="min-w-0 lg:col-start-2">
                        <header class="flex h-14 items-center gap-3 border-b px-4 sm:px-6" aria-hidden="true">
                            <span class="size-8 animate-pulse rounded-md bg-secondary lg:hidden"></span>
                            <span class="h-3 w-40 animate-pulse rounded bg-secondary"></span>
                            <span class="ml-auto h-8 w-24 animate-pulse rounded-md bg-secondary"></span>
                            <span class="size-8 animate-pulse rounded-md bg-secondary"></span>
                        </header>
                        <main class="mx-auto w-full max-w-7xl px-4 py-6 pb-16 sm:px-6" aria-hidden="true">
                            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                                <div class="grid gap-2.5"><span class="h-7 w-48 animate-pulse rounded bg-secondary"></span><span class="h-3 w-64 max-w-full animate-pulse rounded bg-secondary"></span></div>
                                <span class="h-9 w-36 animate-pulse rounded-md bg-secondary"></span>
                            </div>
                            <div class="mb-4 flex flex-wrap gap-2"><span class="h-9 w-full animate-pulse rounded-md bg-secondary sm:max-w-sm"></span><span class="h-9 w-40 animate-pulse rounded-md bg-secondary"></span><span class="h-9 w-40 animate-pulse rounded-md bg-secondary"></span></div>
                            <div class="overflow-hidden rounded-xl border bg-card">
                                <div class="flex h-10 items-center gap-4 border-b bg-muted px-4"><span v-for="item in 5" :key="item" class="h-3 flex-1 animate-pulse rounded bg-secondary"></span></div>
                                <div v-for="row in 6" :key="row" class="flex h-14 items-center gap-4 border-b px-4 last:border-0"><span class="size-4 animate-pulse rounded bg-secondary"></span><span class="h-3 flex-[2] animate-pulse rounded bg-secondary"></span><span class="h-3 flex-1 animate-pulse rounded bg-secondary"></span><span class="h-5 w-20 animate-pulse rounded-full bg-secondary"></span><span class="h-3 w-24 animate-pulse rounded bg-secondary"></span></div>
                            </div>
                        </main>
                    </div>
                </div>
            </template>
            <template v-else>
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
            </template>
        </div>
    </Transition>
</template>
