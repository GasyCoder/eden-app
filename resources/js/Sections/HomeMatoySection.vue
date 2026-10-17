<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Download } from '@lucide/vue';
import IssueCover from '@/Components/Portal/IssueCover.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants, containerClass } from '@/lib/variants';

// Dernier numéro du journal trimestriel (MaToY) : couverture, éditorial, rubriques.
const props = defineProps({
    issue: { type: Object, default: null },
});

const { t, path } = useT();
const markers = ['bg-eden-600', 'bg-laterite-600', 'bg-gold-500'];
const issueHref = () => path('journals', `${props.issue.journal.slug}/${props.issue.slug}`);
</script>

<template>
    <section v-if="issue" id="matoy" class="bg-gold-50/70 py-16 lg:py-20 dark:bg-muted">
        <div :class="cn(containerClass, 'grid items-center gap-10 md:grid-cols-[16rem_minmax(0,1fr)] lg:grid-cols-[20rem_minmax(0,1fr)] lg:gap-16')">
            <Link :href="issueHref()" class="group mx-auto block w-52 outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 md:w-full" :aria-label="`${issue.journal.title} ${issue.label}`">
                <IssueCover :issue="issue" :journal-title="issue.journal.title" class="shadow-xl transition duration-500 group-hover:-translate-y-1 group-hover:rotate-[-1deg]" />
            </Link>

            <div>
                <p class="text-sm font-medium text-laterite-700 dark:text-laterite-300">{{ issue.journal.title }} · {{ t('matoyTagline') }}</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-balance sm:text-3xl">
                    {{ issue.label }}<span v-if="issue.period" class="text-muted-foreground"> · {{ issue.period }}</span>
                </h2>
                <p class="mt-4 max-w-2xl leading-relaxed text-pretty text-muted-foreground">{{ issue.excerpt || t('matoyLead') }}</p>

                <div v-if="issue.rubrics.length" class="mt-7">
                    <h3 class="text-sm font-semibold">{{ t('tableOfContents') }}</h3>
                    <ul class="mt-3 grid gap-x-8 gap-y-2.5 sm:grid-cols-2">
                        <li v-for="(rubric, index) in issue.rubrics" :key="rubric" class="flex items-center gap-3 text-sm">
                            <span :class="['size-1.5 shrink-0 rounded-full', markers[index % 3]]" aria-hidden="true"></span>{{ rubric }}
                        </li>
                    </ul>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <Link :href="issueHref()" :class="buttonVariants()">{{ t('readIssue') }}<ArrowRight aria-hidden="true" /></Link>
                    <a v-if="issue.pdf_url" :href="issue.pdf_url" :class="buttonVariants({ variant: 'outline' })" target="_blank" rel="noopener"><Download aria-hidden="true" />{{ t('downloadPdf') }}</a>
                    <Link :href="path('journals', `${issue.journal.slug}`) + '#numeros'" :class="buttonVariants({ variant: 'ghost' })">{{ t('allIssues') }}</Link>
                </div>
            </div>
        </div>
    </section>
</template>
