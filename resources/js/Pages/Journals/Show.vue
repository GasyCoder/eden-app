<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, ExternalLink, Eye } from '@lucide/vue';
import IssueCard from '@/Components/Portal/IssueCard.vue';
import JournalCard from '@/Components/Portal/JournalCard.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { badgeVariants, buttonVariants, containerClass } from '@/lib/variants';

defineProps({
    journal: { type: Object, required: true },
    related: { type: Array, default: () => [] },
    issues: { type: Array, default: () => [] },
});

const { t, path } = useT();
</script>

<template>
    <Head :title="journal.title">
        <meta v-if="journal.summary" name="description" :content="journal.summary">
    </Head>

    <PublicLayout :editable="false">
        <section class="border-b bg-muted py-12 lg:py-16">
            <div :class="containerClass">
                <div v-if="!journal.is_public" class="mb-6 flex items-start gap-3 rounded-lg border border-gold-300 bg-gold-50 p-4 text-sm text-gold-900 dark:border-gold-800 dark:bg-gold-950 dark:text-gold-200" role="status">
                    <Eye class="mt-0.5 size-4 shrink-0" aria-hidden="true" /><span>{{ t('draftPreview') }}</span>
                </div>
                <nav :aria-label="t('home')">
                    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
                        <li><Link :href="path('home')" class="hover:text-foreground">{{ t('home') }}</Link></li>
                        <li aria-hidden="true">/</li>
                        <li><Link :href="path('journals')" class="hover:text-foreground">{{ t('journals') }}</Link></li>
                    </ol>
                </nav>
                <div class="mt-8 grid items-start gap-10 md:grid-cols-[12rem_minmax(0,1fr)]">
                    <div class="aspect-[3/4] w-36 overflow-hidden rounded-xl border bg-secondary shadow-lg md:w-full">
                        <img v-if="journal.cover_image" :src="journal.cover_image" :alt="journal.title" class="size-full object-cover" decoding="async">
                        <div v-else class="flex size-full items-center justify-center bg-eden-700 text-gold-300"><BookOpen class="size-8" aria-hidden="true" /></div>
                    </div>
                    <div class="grid justify-items-start gap-5">
                        <div class="flex flex-wrap gap-2">
                            <span v-if="journal.domain" :class="badgeVariants({ variant: 'outline' })">{{ journal.domain }}</span>
                            <span v-if="journal.status === 'archived'" :class="badgeVariants({ variant: 'secondary' })">{{ t('archived') }}</span>
                        </div>
                        <h1 class="text-3xl font-bold tracking-tight text-balance sm:text-4xl">{{ journal.title }}</h1>
                        <dl class="grid w-full grid-cols-2 gap-x-8 gap-y-4 border-y py-5 sm:grid-cols-4">
                            <div v-if="journal.issn"><dt class="text-xs text-muted-foreground">{{ t('issn') }}</dt><dd class="mt-0.5 text-sm font-medium tabular-nums">{{ journal.issn }}</dd></div>
                            <div v-if="journal.frequency"><dt class="text-xs text-muted-foreground">{{ t('frequency') }}</dt><dd class="mt-0.5 text-sm font-medium">{{ journal.frequency }}</dd></div>
                            <div v-if="journal.language"><dt class="text-xs text-muted-foreground">{{ t('journalLanguage') }}</dt><dd class="mt-0.5 text-sm font-medium">{{ journal.language }}</dd></div>
                            <div v-if="journal.editor"><dt class="text-xs text-muted-foreground">{{ t('editor') }}</dt><dd class="mt-0.5 text-sm font-medium">{{ journal.editor }}</dd></div>
                        </dl>
                        <div v-if="issues.length" class="flex flex-wrap gap-3">
                            <Link :href="path('journals', `${journal.slug}/${issues[0].slug}`)" :class="buttonVariants({ size: 'lg' })">{{ t('latestIssue') }} · {{ issues[0].label }}</Link>
                            <a href="#numeros" :class="buttonVariants({ variant: 'outline', size: 'lg' })">{{ t('allIssues') }}</a>
                        </div>
                        <a v-if="journal.external_url" :href="journal.external_url" :class="buttonVariants({ size: 'lg' })" target="_blank" rel="noopener noreferrer">
                            {{ t('visitJournal') }} <ExternalLink aria-hidden="true" /><span class="sr-only">{{ t('newTab') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="journal.description" class="py-14">
            <!-- Contenu nettoyé côté serveur (App\Services\RichTextSanitizer) -->
            <div class="prose prose-eden mx-auto max-w-3xl px-4 sm:px-6" v-html="journal.description"></div>
        </section>

        <section v-if="issues.length" id="numeros" class="scroll-mt-24 border-t py-14 lg:py-16" aria-labelledby="issues-title">
            <div :class="containerClass">
                <h2 id="issues-title" class="text-2xl font-semibold tracking-tight">{{ t('issues') }}</h2>
                <p class="mt-2 text-muted-foreground">{{ t('issuesLead') }}</p>
                <div class="mt-8 grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-5">
                    <IssueCard v-for="issue in issues" :key="issue.id" :issue="issue" :journal-slug="journal.slug" :journal-title="journal.title" />
                </div>
            </div>
        </section>

        <section v-if="related.length" class="bg-muted py-16">
            <div :class="containerClass">
                <div class="mb-8 flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-semibold tracking-tight">{{ t('relatedJournals') }}</h2>
                    <Link :href="path('journals')" :class="buttonVariants({ variant: 'outline' })">{{ t('seeAll') }}</Link>
                </div>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"><JournalCard v-for="item in related" :key="item.id" :journal="item" /></div>
            </div>
        </section>
    </PublicLayout>
</template>
