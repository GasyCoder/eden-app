<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { containerClass } from '@/lib/variants';

// En-tête des pages secondaires : fil d'Ariane, titre, chapô.
defineProps({
    title: { type: String, required: true },
    lead: { type: String, default: '' },
    crumbs: { type: Array, default: () => [] },
});

const { t, path } = useT();
</script>

<template>
    <header class="border-b bg-muted">
        <div :class="cn(containerClass, 'py-10 lg:py-12')">
            <nav :aria-label="t('home')">
                <ol class="flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
                    <li><Link :href="path('home')" class="hover:text-foreground">{{ t('home') }}</Link></li>
                    <li v-for="crumb in crumbs" :key="crumb.label" class="flex items-center gap-1.5">
                        <ChevronRight class="size-3.5 opacity-60" aria-hidden="true" />
                        <Link v-if="crumb.href" :href="crumb.href" class="hover:text-foreground">{{ crumb.label }}</Link>
                        <span v-else class="font-medium text-foreground" aria-current="page">{{ crumb.label }}</span>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-4 max-w-3xl text-3xl font-semibold tracking-tight text-balance sm:text-4xl">{{ title }}</h1>
            <p v-if="lead" class="mt-3 max-w-2xl leading-relaxed text-muted-foreground">{{ lead }}</p>
            <slot />
        </div>
    </header>
</template>
