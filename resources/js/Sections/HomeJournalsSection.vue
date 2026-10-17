<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import JournalCard from '@/Components/Portal/JournalCard.vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import { useT } from '@/lib/i18n';
import { buttonVariants, containerClass } from '@/lib/variants';

defineProps({
    journals: { type: Array, default: () => [] },
});

const { t, path } = useT();
</script>

<template>
    <section v-if="journals.length" id="revues" class="bg-muted py-16 lg:py-20">
        <div :class="containerClass">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading>
                    {{ t('featuredJournals') }}
                    <template #description>{{ t('journalsLead') }}</template>
                </SectionHeading>
                <Link :href="path('journals')" :class="buttonVariants({ variant: 'outline' })">{{ t('seeAll') }}<ArrowRight aria-hidden="true" /></Link>
            </div>
            <div class="mt-8 grid gap-3 sm:mt-10 sm:gap-4 md:grid-cols-2 lg:grid-cols-3">
                <JournalCard v-for="journal in journals" :key="journal.id" :journal="journal" />
            </div>
        </div>
    </section>
</template>
