<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { buttonVariants } from '@/lib/variants';

// Pagination à partir du format App\Support\Paginated.
defineProps({
    meta: { type: Object, required: true },
    links: { type: Object, required: true },
    labels: { type: Object, default: () => ({ previous: 'Précédent', next: 'Suivant', info: null }) },
    preserveScroll: { type: Boolean, default: false },
});
</script>

<template>
    <nav v-if="meta.last_page > 1 || labels.info" class="mt-8 flex flex-wrap items-center justify-center gap-3 sm:justify-between" aria-label="Pagination">
        <p v-if="labels.info" class="w-full text-center text-sm text-muted-foreground sm:w-auto sm:text-left">{{ labels.info }}</p>
        <div v-if="meta.last_page > 1" class="flex items-center gap-1">
            <Link v-if="links.prev" :href="links.prev" :preserve-scroll="preserveScroll" rel="prev" :class="buttonVariants({ variant: 'ghost', size: 'sm' })">
                <ChevronLeft aria-hidden="true" /><span class="hidden sm:inline">{{ labels.previous }}</span>
            </Link>
            <template v-for="(item, index) in links.pages" :key="index">
                <span v-if="item.type === 'ellipsis'" class="hidden px-2 text-muted-foreground sm:inline" aria-hidden="true">…</span>
                <Link
                    v-else
                    :href="item.url"
                    :preserve-scroll="preserveScroll"
                    :aria-current="item.active ? 'page' : undefined"
                    :class="[buttonVariants({ variant: item.active ? 'outline' : 'ghost', size: 'icon-sm' }), !item.active && 'hidden sm:inline-flex']"
                >{{ item.page }}</Link>
            </template>
            <Link v-if="links.next" :href="links.next" :preserve-scroll="preserveScroll" rel="next" :class="buttonVariants({ variant: 'ghost', size: 'sm' })">
                <span class="hidden sm:inline">{{ labels.next }}</span><ChevronRight aria-hidden="true" />
            </Link>
        </div>
    </nav>
</template>
