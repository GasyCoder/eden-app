<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { FolderKanban } from '@lucide/vue';
import PageHeader from '@/Components/Portal/PageHeader.vue';
import ProjectCard from '@/Components/Portal/ProjectCard.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { containerClass } from '@/lib/variants';

defineProps({
    projects: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    canEdit: { type: Boolean, default: false },
});

const { t, path } = useT();
const options = ['ongoing', 'upcoming', 'completed'];
</script>

<template>
    <Head :title="t('projects')">
        <meta name="description" :content="t('projectsLead')">
    </Head>

    <PublicLayout :editable="false">
        <PageHeader :title="t('projects')" :lead="t('projectsLead')" :crumbs="[{ label: t('projects') }]">
            <nav class="mt-6 flex flex-wrap gap-2" :aria-label="t('projects')">
                <Link
                    v-for="option in [null, ...options]"
                    :key="option ?? 'all'"
                    :href="option ? `${path('projects')}?etat=${option}` : path('projects')"
                    preserve-scroll
                    :class="cn('inline-flex h-8 items-center rounded-full px-3.5 text-sm font-medium transition-colors', (filters.progress ?? null) === option ? 'bg-eden-600 text-white' : 'bg-background text-muted-foreground ring-1 ring-border hover:text-foreground')"
                    :aria-current="(filters.progress ?? null) === option ? 'page' : undefined"
                >{{ option ? t(`progress_${option}`) : t('allProgress') }}</Link>
            </nav>
        </PageHeader>

        <section class="py-14">
            <div :class="containerClass">
                <div v-if="projects.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <ProjectCard v-for="project in projects" :key="project.id" :project="project" :can-edit="canEdit" />
                </div>
                <EmptyState v-else :icon="FolderKanban" :title="t('noProjects')" />
            </div>
        </section>
    </PublicLayout>
</template>
