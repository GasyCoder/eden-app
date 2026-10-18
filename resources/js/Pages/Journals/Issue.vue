<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { BookOpenText, Download, Eye, FileText } from '@lucide/vue';
import { computed } from 'vue';
import IssueCard from '@/Components/Portal/IssueCard.vue';
import IssueCover from '@/Components/Portal/IssueCover.vue';
import PdfReader from '@/Components/Portal/PdfReader.vue';
import ShareButtons from '@/Components/Portal/ShareButtons.vue';
import StatBars from '@/Components/Portal/StatBars.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDate, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants, containerClass } from '@/lib/variants';

const props = defineProps({
    issue: { type: Object, required: true },
    journal: { type: Object, required: true },
    otherIssues: { type: Array, default: () => [] },
});

const { t, path, locale } = useT();
const megabytes = computed(() => (props.issue.pdf_size ? `${(props.issue.pdf_size / 1048576).toFixed(1).replace('.', locale.value === 'fr' ? ',' : '.')} Mo` : ''));
const pdfMeta = computed(() => ['PDF', megabytes.value, props.issue.page_count ? t('pages', props.issue.page_count) : ''].filter(Boolean).join(' · '));
const isLocalPdf = computed(() => props.issue.pdf_url?.startsWith('/'));
const pageTitle = computed(() => `${props.journal.title} ${props.issue.label}`);
</script>

<template>
    <Head :title="pageTitle">
        <meta v-if="issue.excerpt" name="description" :content="issue.excerpt">
    </Head>

    <PublicLayout :editable="false">
        <section class="border-b bg-muted">
            <div :class="cn(containerClass, 'py-10 lg:py-14')">
                <div v-if="!issue.is_public" class="mb-6 flex items-start gap-3 rounded-lg bg-gold-100 p-4 text-sm text-gold-900 dark:bg-gold-950 dark:text-gold-200" role="status">
                    <Eye class="mt-0.5 size-4 shrink-0" aria-hidden="true" /><span>{{ t('draftPreview') }}</span>
                </div>
                <nav :aria-label="t('home')">
                    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
                        <li><Link :href="path('home')" class="hover:text-foreground">{{ t('home') }}</Link></li>
                        <li aria-hidden="true">/</li>
                        <li><Link :href="path('journals')" class="hover:text-foreground">{{ t('journals') }}</Link></li>
                        <li aria-hidden="true">/</li>
                        <li><Link :href="path('journals', journal.slug)" class="hover:text-foreground">{{ journal.title }}</Link></li>
                    </ol>
                </nav>

                <div class="mt-8 grid items-center gap-10 md:grid-cols-[15rem_minmax(0,1fr)] lg:grid-cols-[18rem_minmax(0,1fr)] lg:gap-14">
                    <IssueCover :issue="issue" :journal-title="journal.title" eager class="mx-auto w-48 shadow-xl md:w-full animate-in fade-in-0 slide-in-from-bottom-4 duration-700" />
                    <div>
                        <p class="text-sm font-medium text-eden-700 dark:text-eden-300">{{ journal.title }}<template v-if="journal.frequency"> · {{ journal.frequency }}</template></p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
                            {{ issue.label }}<span v-if="issue.period" class="text-muted-foreground"> · {{ issue.period }}</span>
                        </h1>
                        <p v-if="issue.title" class="mt-3 text-lg text-muted-foreground">{{ issue.title }}</p>
                        <p v-if="issue.excerpt" class="mt-5 max-w-2xl leading-relaxed text-pretty">{{ issue.excerpt }}</p>
                        <div class="mt-7 flex flex-wrap gap-3">
                            <a v-if="issue.pdf_url" href="#lecture" :class="buttonVariants({ size: 'lg' })"><BookOpenText aria-hidden="true" />{{ t('readOnline') }}</a>
                            <a v-if="issue.pdf_url" :href="issue.pdf_url" :class="buttonVariants({ variant: 'outline', size: 'lg' })" :download="isLocalPdf ? `${journal.slug}-${issue.slug}.pdf` : undefined">
                                <Download aria-hidden="true" />{{ t('downloadPdf') }}
                            </a>
                        </div>
                        <p v-if="issue.pdf_url" class="mt-3 text-xs text-muted-foreground">{{ pdfMeta }}</p>
                        <p v-if="issue.published_at" class="mt-6 text-sm text-muted-foreground">
                            <time :datetime="issue.published_at">{{ formatDate(issue.published_at, locale) }}</time>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div :class="cn(containerClass, 'grid gap-14 py-14 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-16')">
            <div class="grid min-w-0 content-start gap-14">
                <section v-if="issue.editorial" aria-labelledby="editorial-title">
                    <h2 id="editorial-title" class="text-2xl font-semibold tracking-tight">{{ t('editorial') }}</h2>
                    <!-- Contenu nettoyé côté serveur (App\Services\RichTextSanitizer) -->
                    <div class="prose prose-eden mt-5 max-w-none" v-html="issue.editorial"></div>
                </section>

                <section v-if="issue.contents.length" aria-labelledby="contents-title">
                    <h2 id="contents-title" class="text-2xl font-semibold tracking-tight">{{ t('tableOfContents') }}</h2>
                    <div class="mt-6 grid gap-10">
                        <div v-for="(group, groupIndex) in issue.contents" :key="group.rubric">
                            <h3 class="flex items-center gap-3 text-sm font-semibold text-eden-700 dark:text-eden-300">
                                <span :class="['h-4 w-1 rounded-full', ['bg-eden-600', 'bg-laterite-600', 'bg-gold-500'][groupIndex % 3]]" aria-hidden="true"></span>
                                {{ group.rubric }}
                            </h3>
                            <ul class="mt-4 grid divide-y">
                                <li v-for="item in group.items" :key="item.title" class="flex gap-5 py-5 first:pt-0">
                                    <img v-if="item.image" :src="item.image" alt="" class="aspect-[4/3] w-28 shrink-0 rounded-md object-cover sm:w-36" loading="lazy" decoding="async">
                                    <div class="min-w-0">
                                        <p class="font-semibold leading-snug text-pretty">{{ item.title }}</p>
                                        <p v-if="item.summary" class="mt-1.5 text-sm leading-relaxed text-pretty whitespace-pre-line text-muted-foreground">{{ item.summary }}</p>
                                        <p v-if="item.page" class="mt-2 text-xs text-muted-foreground">p. {{ item.page }}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="grid content-start gap-10 lg:sticky lg:top-24 lg:self-start">
                <section v-if="issue.stats.length" aria-labelledby="figures-title">
                    <h2 id="figures-title" class="text-base font-semibold">{{ t('inFigures') }}</h2>
                    <StatBars class="mt-5" :stats="issue.stats" />
                </section>
                <section v-if="issue.pdf_url" class="grid gap-3">
                    <h2 class="text-base font-semibold">{{ t('readIssue') }}</h2>
                    <a :href="issue.pdf_url" class="group flex items-center gap-3 rounded-lg bg-muted p-3 text-sm transition-colors hover:bg-accent">
                        <span class="grid size-10 shrink-0 place-items-center rounded-md bg-laterite-600 text-white"><FileText class="size-5" aria-hidden="true" /></span>
                        <span class="grid min-w-0">
                            <span class="font-medium group-hover:text-eden-700 dark:group-hover:text-eden-300">{{ t('downloadPdf') }}</span>
                            <span class="text-xs text-muted-foreground">{{ pdfMeta }}</span>
                        </span>
                    </a>
                </section>
                <ShareButtons :title="pageTitle" />
            </aside>
        </div>

        <section v-if="issue.pdf_url" id="lecture" class="scroll-mt-24 bg-muted py-14" aria-labelledby="reader-title">
            <div :class="containerClass">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 id="reader-title" class="text-2xl font-semibold tracking-tight">{{ t('readOnline') }}</h2>
                    <a :href="issue.pdf_url" :class="buttonVariants({ variant: 'outline' })" :download="isLocalPdf ? `${journal.slug}-${issue.slug}.pdf` : undefined"><Download aria-hidden="true" />{{ t('downloadPdf') }}</a>
                </div>
                <div class="mt-6">
                    <PdfReader
                        :src="issue.pdf_url"
                        :title="t('pdfViewer')"
                        :download-label="t('downloadPdf')"
                        :open-label="t('openPdf')"
                        :page-label="t('pdfPage', ':current', ':total')"
                        :previous-label="t('previous')"
                        :next-label="t('next')"
                        :loading-label="t('pdfLoading')"
                        :error-label="t('pdfLoadError')"
                    />
                </div>
            </div>
        </section>

        <section v-if="otherIssues.length" class="py-16">
            <div :class="containerClass">
                <div class="mb-8 flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-semibold tracking-tight">{{ t('otherIssues') }}</h2>
                    <Link :href="path('journals', journal.slug)" :class="buttonVariants({ variant: 'outline' })">{{ t('allIssues') }}</Link>
                </div>
                <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
                    <IssueCard v-for="item in otherIssues" :key="item.id" :issue="item" :journal-slug="journal.slug" :journal-title="journal.title" />
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
