<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpen, SearchX } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import JournalCard from '@/Components/Portal/JournalCard.vue';
import PageHeader from '@/Components/Portal/PageHeader.vue';
import SearchField from '@/Components/Portal/SearchField.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import Select from '@/Components/ui/Select.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useT } from '@/lib/i18n';
import { buttonVariants, containerClass } from '@/lib/variants';

const props = defineProps({
    journals: { type: Object, required: true },
    domains: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const { t, path } = useT();
const domain = ref(props.filters.domain || null);
const isFiltered = computed(() => Boolean(props.filters.domain || props.filters.q));
const domainOptions = computed(() => [{ value: null, label: t('allDomains') }, ...props.domains.map((item) => ({ value: item, label: item }))]);

const visit = (params) => {
    const query = { domaine: props.filters.domain || undefined, q: props.filters.q || undefined, ...params };
    Object.keys(query).forEach((key) => (query[key] === undefined || query[key] === null || query[key] === '') && delete query[key]);
    router.get(path('journals'), query, { preserveScroll: true, preserveState: true });
};

watch(domain, (value) => {
    if ((value || '') !== (props.filters.domain || '')) {
        visit({ domaine: value || undefined });
    }
});
</script>

<template>
    <Head :title="t('journals')" />

    <PublicLayout :editable="false">
        <PageHeader :title="t('journals')" :lead="t('journalsLead')" :crumbs="[{ label: t('journals') }]" />

        <section class="py-12 lg:py-16">
            <div :class="containerClass">
                <div v-if="domains.length || isFiltered || journals.data.length" class="mb-10 flex flex-wrap items-center justify-between gap-3">
                    <div v-if="domains.length" class="w-full sm:w-64">
                        <Select v-model="domain" :options="domainOptions" :placeholder="t('allDomains')" :aria-label="t('domain')" />
                    </div>
                    <SearchField class="sm:ml-auto" :model-value="filters.q" :placeholder="t('searchPlaceholder')" :label="t('search')" @search="(q) => visit({ q })" />
                </div>

                <template v-if="journals.data.length">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <JournalCard v-for="journal in journals.data" :key="journal.id" :journal="journal" />
                    </div>
                    <Pagination :meta="journals.meta" :links="journals.links" :labels="{ previous: t('previous'), next: t('next') }" />
                </template>
                <EmptyState v-else-if="isFiltered" :icon="SearchX" :title="t('noResults')" :description="t('noResultsHint')">
                    <Link :href="path('journals')" :class="buttonVariants({ variant: 'outline', size: 'sm' })">{{ t('clearFilters') }}</Link>
                </EmptyState>
                <EmptyState v-else :icon="BookOpen" :title="t('noJournals')" :description="t('noJournalsHint')" />
            </div>
        </section>
    </PublicLayout>
</template>
