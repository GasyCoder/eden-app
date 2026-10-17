<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Newspaper, SearchX } from '@lucide/vue';
import { computed } from 'vue';
import PageHeader from '@/Components/Portal/PageHeader.vue';
import PostCard from '@/Components/Portal/PostCard.vue';
import SearchField from '@/Components/Portal/SearchField.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants, containerClass } from '@/lib/variants';

const props = defineProps({
    featured: { type: Object, default: null },
    posts: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const { t, path } = useT();
const isFiltered = computed(() => Boolean(props.filters.category || props.filters.q));
const isEmpty = computed(() => !props.featured && !props.posts.data.length);

const visit = (params) => {
    const query = { categorie: props.filters.category || undefined, q: props.filters.q || undefined, ...params };
    Object.keys(query).forEach((key) => (query[key] === undefined || query[key] === '') && delete query[key]);
    router.get(path('news'), query, { preserveScroll: true, preserveState: true });
};

const categoryHref = (slug) => {
    const params = new URLSearchParams();

    if (slug) params.set('categorie', slug);
    if (props.filters.q) params.set('q', props.filters.q);

    return `${path('news')}${params.toString() ? `?${params}` : ''}`;
};
</script>

<template>
    <Head :title="t('news')" />

    <PublicLayout :editable="false">
        <PageHeader :title="t('news')" :lead="t('newsLead')" :crumbs="[{ label: t('news') }]" />

        <section class="py-12 lg:py-16">
            <div :class="containerClass">
                <div class="mb-10 flex flex-wrap items-center justify-between gap-4">
                    <nav v-if="categories.length" class="flex flex-wrap gap-2" :aria-label="t('allCategories')">
                        <Link :href="categoryHref(null)" class="inline-flex h-8 items-center gap-1.5 rounded-full border px-3.5 text-sm font-medium transition-colors aria-[current=true]:border-eden-600 aria-[current=true]:bg-eden-600 aria-[current=true]:text-white hover:bg-accent aria-[current=true]:hover:bg-eden-700" :aria-current="!filters.category ? 'true' : undefined" preserve-scroll>{{ t('allCategories') }}</Link>
                        <Link
                            v-for="category in categories"
                            :key="category.id"
                            :href="categoryHref(category.slug)"
                            class="inline-flex h-8 items-center gap-1.5 rounded-full border px-3.5 text-sm font-medium transition-colors aria-[current=true]:border-eden-600 aria-[current=true]:bg-eden-600 aria-[current=true]:text-white hover:bg-accent aria-[current=true]:hover:bg-eden-700"
                            :aria-current="filters.category === category.slug ? 'true' : undefined"
                            preserve-scroll
                        >
                            {{ category.name }} <span class="text-xs tabular-nums opacity-70">{{ category.count }}</span>
                        </Link>
                    </nav>
                    <SearchField class="sm:ml-auto" :model-value="filters.q" :placeholder="t('searchPlaceholder')" :label="t('search')" @search="(q) => visit({ q, page: undefined })" />
                </div>

                <template v-if="!isEmpty">
                    <PostCard v-if="featured" :post="featured" featured class="mb-12" />
                    <div v-if="posts.data.length" class="grid gap-x-6 gap-y-12 md:grid-cols-2 lg:grid-cols-3">
                        <PostCard v-for="post in posts.data" :key="post.id" :post="post" />
                    </div>
                    <Pagination
                        :meta="posts.meta"
                        :links="posts.links"
                        :labels="{ previous: t('previous'), next: t('next'), info: posts.meta.total ? t('pageOf', posts.meta.from, posts.meta.to, posts.meta.total) : null }"
                    />
                </template>
                <EmptyState v-else-if="isFiltered" :icon="SearchX" :title="t('noResults')" :description="t('noResultsHint')">
                    <Link :href="path('news')" :class="buttonVariants({ variant: 'outline', size: 'sm' })">{{ t('clearFilters') }}</Link>
                </EmptyState>
                <EmptyState v-else :icon="Newspaper" :title="t('noNews')" :description="t('noNewsHint')" />
            </div>
        </section>
    </PublicLayout>
</template>
