<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { CircleCheck } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

defineProps({
    email: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Inscription newsletter" />

    <main id="main" class="grid min-h-dvh place-items-center bg-muted px-4 py-10">
        <section class="grid w-full max-w-sm justify-items-center gap-4 rounded-xl border bg-card p-8 text-center shadow-sm">
            <span class="grid size-12 place-items-center rounded-full bg-eden-50 text-eden-600 dark:bg-eden-950" aria-hidden="true"><CircleCheck class="size-6" /></span>
            <h1 class="text-2xl font-semibold tracking-tight">Inscription confirmée</h1>
            <p class="text-sm leading-relaxed text-muted-foreground">
                L’adresse <strong class="font-medium break-all text-foreground">{{ email }}</strong> recevra désormais les actualités de l’École Doctorale EDEN.
            </p>
            <Link href="/" :class="cn(buttonVariants(), 'mt-2 w-full')">Retour au site</Link>
        </section>
    </main>
</template>
