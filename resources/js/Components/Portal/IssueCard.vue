<script setup>
import { Link } from '@inertiajs/vue3';
import { formatDate, useT } from '@/lib/i18n';
import IssueCover from '@/Components/Portal/IssueCover.vue';

defineProps({
    issue: { type: Object, required: true },
    journalSlug: { type: String, required: true },
    journalTitle: { type: String, default: '' },
});

const { t, path, locale } = useT();
</script>

<template>
    <article class="group relative grid content-start gap-4">
        <IssueCover :issue="issue" :journal-title="journalTitle" class="transition duration-300 group-hover:-translate-y-1 group-hover:shadow-lg" />
        <div class="grid gap-1">
            <h3 class="font-semibold">
                <Link :href="path('journals', `${journalSlug}/${issue.slug}`)" class="outline-none after:absolute after:inset-0 after:rounded-lg group-hover:text-eden-700 focus-visible:after:ring-[3px] focus-visible:after:ring-ring/50 dark:group-hover:text-eden-300">
                    {{ issue.label }}
                </Link>
            </h3>
            <p class="text-sm text-muted-foreground">
                <span v-if="issue.period">{{ issue.period }}</span>
                <span v-if="issue.period && issue.published_at"> · </span>
                <time v-if="issue.published_at" :datetime="issue.published_at">{{ formatDate(issue.published_at, locale, { month: 'long', year: 'numeric' }) }}</time>
            </p>
            <p v-if="issue.is_public === false" class="text-xs font-medium text-gold-700 dark:text-gold-300">{{ t('draftShort') }}</p>
        </div>
    </article>
</template>
