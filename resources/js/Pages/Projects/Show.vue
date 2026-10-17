<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight, CircleCheck, Eye, Pencil } from '@lucide/vue';
import { computed } from 'vue';
import ProjectCard from '@/Components/Portal/ProjectCard.vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { badgeVariants, buttonVariants, containerClass } from '@/lib/variants';

// Page de détail d'un projet (RAVENALA compris) : chaque bloc n'apparaît que s'il est renseigné.
const props = defineProps({
    project: { type: Object, required: true },
    otherProjects: { type: Array, default: () => [] },
    canEdit: { type: Boolean, default: false },
});

const { t, path } = useT();
const progressTone = { upcoming: 'warning', ongoing: 'success', completed: 'secondary' };
const facts = computed(() => [
    ...(props.project.funder ? [{ label: t('funding'), value: props.project.funder }] : []),
    ...(props.project.leader ? [{ label: t('projectLeader'), value: props.project.leader }] : []),
    ...(props.project.period ? [{ label: t('period'), value: props.project.period }] : []),
    ...(props.project.team ? [{ label: 'EAD', value: props.project.team }] : []),
]);
const initials = (name = '') => name.split(/[\s’']+/).filter((word) => word.length > 2 && word[0] === word[0].toUpperCase()).map((word) => word[0]).join('').slice(0, 4);
</script>

<template>
    <Head :title="project.title">
        <meta v-if="project.summary" name="description" :content="project.summary">
    </Head>

    <PublicLayout :editable="false">
        <section class="border-b bg-muted">
            <div :class="cn(containerClass, 'grid items-center gap-10 py-12 lg:grid-cols-2 lg:py-16')">
                <div>
                    <div v-if="!project.is_public" class="mb-6 flex items-start gap-3 rounded-lg bg-gold-100 p-4 text-sm text-gold-900 dark:bg-gold-950 dark:text-gold-200" role="status">
                        <Eye class="mt-0.5 size-4 shrink-0" aria-hidden="true" /><span>{{ t('draftPreview') }}</span>
                    </div>
                    <nav :aria-label="t('home')">
                        <ol class="flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
                            <li><Link :href="path('home')" class="hover:text-foreground">{{ t('home') }}</Link></li>
                            <li aria-hidden="true">/</li>
                            <li><Link :href="path('projects')" class="hover:text-foreground">{{ t('projects') }}</Link></li>
                            <li aria-hidden="true">/</li>
                            <li class="font-medium text-foreground" aria-current="page">{{ project.acronym || project.title }}</li>
                        </ol>
                    </nav>
                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <span :class="badgeVariants({ variant: progressTone[project.progress] ?? 'success', dot: true })">{{ t(`progress_${project.progress}`) }}</span>
                    </div>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-balance sm:text-4xl">{{ project.title }}</h1>
                    <p v-if="project.subtitle" class="mt-3 font-medium text-eden-700 dark:text-eden-400">{{ project.subtitle }}</p>
                    <p v-if="project.summary" class="mt-4 max-w-xl leading-relaxed text-muted-foreground">{{ project.summary }}</p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a v-if="project.objectives.length" href="#objectifs" :class="buttonVariants()">{{ t('objectives') }}</a>
                        <a v-if="project.partners.length" href="#partenaires-projet" :class="buttonVariants({ variant: 'outline' })">{{ t('partnersTitle') }}</a>
                        <a v-if="project.external_url" :href="project.external_url" target="_blank" rel="noopener noreferrer" :class="buttonVariants({ variant: 'outline' })">{{ t('projectWebsite') }}<ArrowUpRight aria-hidden="true" /></a>
                        <a v-if="canEdit" :href="`/admin/projects/${project.id}/edit`" :class="buttonVariants({ variant: 'ghost' })"><Pencil aria-hidden="true" />Modifier</a>
                    </div>
                </div>

                <aside class="overflow-hidden rounded-xl bg-card text-card-foreground shadow-sm" :aria-label="t('projectAtGlance')">
                    <img v-if="project.cover_image" :src="project.cover_image" :alt="project.title" class="aspect-[16/10] w-full object-cover" width="800" height="500" loading="eager" fetchpriority="high" decoding="async">
                    <div class="grid gap-5 p-6">
                        <div v-if="project.logo || project.acronym" class="flex items-center gap-3">
                            <img v-if="project.logo" :src="project.logo" :alt="`Logo ${project.acronym || project.title}`" class="size-12 object-contain" loading="eager" decoding="async">
                            <div>
                                <strong class="block text-lg font-semibold">{{ project.acronym || project.title }}</strong>
                                <span v-if="project.team" class="text-sm text-muted-foreground">{{ project.team }}</span>
                            </div>
                        </div>
                        <dl v-if="project.facts.length" class="grid grid-cols-3 divide-x border-y">
                            <div v-for="fact in project.facts.slice(0, 3)" :key="fact.label" class="flex flex-col-reverse gap-1 px-3 py-4 first:pl-0">
                                <dt class="text-xs leading-snug text-muted-foreground">{{ fact.label }}</dt>
                                <dd class="text-2xl font-bold text-eden-700 dark:text-eden-300">{{ fact.value }}</dd>
                            </div>
                        </dl>
                        <dl v-if="facts.length" class="grid grid-cols-2 gap-4">
                            <div v-for="fact in facts" :key="fact.label">
                                <dt class="text-xs text-muted-foreground">{{ fact.label }}</dt>
                                <dd class="text-sm font-medium">{{ fact.value }}</dd>
                            </div>
                        </dl>
                    </div>
                </aside>
            </div>
        </section>

        <section v-if="project.funding_notice || project.funding_logo" class="border-b">
            <div :class="cn(containerClass, 'flex flex-col items-start gap-4 py-6 sm:flex-row sm:items-center sm:gap-8')">
                <img v-if="project.funding_logo" :src="project.funding_logo" :alt="project.funder || ''" class="h-auto w-48 shrink-0 dark:rounded dark:bg-white dark:p-1.5" loading="lazy" decoding="async">
                <p v-if="project.funding_notice" class="text-xs leading-relaxed text-muted-foreground">{{ project.funding_notice }}</p>
            </div>
        </section>

        <section v-if="project.body || project.gallery_image || project.beneficiaries.length" class="py-16 lg:py-20">
            <div :class="cn(containerClass, 'grid items-start gap-12 lg:grid-cols-2 lg:gap-16')">
                <div>
                    <SectionHeading>{{ t('aboutProject') }}</SectionHeading>
                    <!-- Contenu nettoyé côté serveur (App\Services\RichTextSanitizer) -->
                    <div v-if="project.body" class="prose prose-eden mt-6 max-w-none" v-html="project.body"></div>
                </div>
                <div class="grid gap-8">
                    <img v-if="project.gallery_image" :src="project.gallery_image" alt="" class="w-full rounded-xl" loading="lazy" decoding="async">
                    <div v-if="project.beneficiaries.length">
                        <h3 class="mb-4 text-base font-semibold">{{ t('beneficiaries') }}</h3>
                        <ul class="grid gap-3">
                            <li v-for="beneficiary in project.beneficiaries" :key="beneficiary" class="flex items-start gap-3 text-sm"><CircleCheck class="mt-0.5 size-4 shrink-0 text-eden-600" aria-hidden="true" />{{ beneficiary }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="project.objectives.length" id="objectifs" class="scroll-mt-24 bg-muted py-16 lg:py-20">
            <div :class="containerClass">
                <SectionHeading>{{ t('objectives') }}<template #description>{{ t('objectivesLead') }}</template></SectionHeading>
                <ol class="mt-10 grid gap-x-10 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="(objective, index) in project.objectives" :key="objective" class="grid content-start gap-3 border-t-2 border-eden-600 pt-4">
                        <span class="text-sm font-bold tabular-nums text-gold-600">{{ String(index + 1).padStart(2, '0') }}</span>
                        <p class="font-medium leading-relaxed">{{ objective }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <section v-if="project.partners.length" id="partenaires-projet" class="scroll-mt-24 py-16 lg:py-20">
            <div :class="containerClass">
                <SectionHeading>{{ t('partnersTitle') }}</SectionHeading>
                <ul class="mt-10 grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-5">
                    <li v-for="partner in project.partners" :key="partner.name" class="grid content-start gap-3">
                        <component :is="partner.url ? 'a' : 'div'" :href="partner.url" :target="partner.url ? '_blank' : undefined" :rel="partner.url ? 'noopener noreferrer' : undefined" class="grid h-24 place-items-center rounded-xl bg-white p-3 ring-1 ring-border transition hover:ring-eden-300">
                            <img v-if="partner.logo" :src="partner.logo" :alt="`Logo ${partner.name}`" class="max-h-16 w-auto object-contain" loading="lazy" decoding="async">
                            <span v-else class="text-xl font-bold text-eden-700">{{ initials(partner.name) }}</span>
                        </component>
                        <h3 class="font-semibold leading-snug">{{ partner.name }}</h3>
                        <p class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                            <span v-if="partner.country" :class="badgeVariants({ variant: 'outline' })">{{ partner.country }}</span>{{ partner.role }}
                        </p>
                    </li>
                </ul>
            </div>
        </section>

        <section v-if="project.work_packages.length" class="bg-muted py-16 lg:py-20">
            <div :class="containerClass">
                <SectionHeading>{{ t('workPackages') }}</SectionHeading>
                <ol class="mt-10 grid divide-y border-y">
                    <li v-for="workPackage in project.work_packages" :key="workPackage.code + workPackage.title" class="grid gap-3 py-6 sm:grid-cols-[6rem_minmax(0,1fr)] sm:gap-6">
                        <span class="grid h-10 w-20 place-items-center rounded-md bg-eden-600 font-bold text-white">{{ workPackage.code }}</span>
                        <div>
                            <h3 class="text-lg font-semibold">{{ workPackage.title }}</h3>
                            <p v-if="workPackage.description" class="mt-1 text-sm leading-relaxed text-muted-foreground">{{ workPackage.description }}</p>
                            <p v-if="workPackage.lead" class="mt-2 text-sm font-medium">{{ workPackage.lead }}</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section class="py-16">
            <div :class="cn(containerClass, 'flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center')">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight">{{ t('projectQuestion') }}</h2>
                    <p class="mt-2 text-muted-foreground">{{ t('projectQuestionText') }}</p>
                </div>
                <a :href="path('contact')" :class="buttonVariants()">{{ t('writeUs') }}<ArrowRight aria-hidden="true" /></a>
            </div>
        </section>

        <section v-if="otherProjects.length" class="bg-muted py-16">
            <div :class="containerClass">
                <div class="mb-8 flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-semibold tracking-tight">{{ t('otherProjects') }}</h2>
                    <Link :href="path('projects')" :class="buttonVariants({ variant: 'outline' })">{{ t('allProjects') }}</Link>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"><ProjectCard v-for="item in otherProjects" :key="item.id" :project="item" /></div>
            </div>
        </section>
    </PublicLayout>
</template>
