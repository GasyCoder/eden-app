<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, LayoutGrid, List, Settings2 } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import FeaturedProjectCard from '@/Components/Portal/FeaturedProjectCard.vue';
import ProjectCard from '@/Components/Portal/ProjectCard.vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants, containerClass } from '@/lib/variants';

// Projets publiés (gérés dans Administration > Projets) ; titre et chapô éditables en place.
const props = defineProps({
    blocks: { type: Object, default: () => ({}) },
    projects: { type: Array, default: () => [] },
    canEdit: { type: Boolean, default: false },
    showAllLink: { type: Boolean, default: false },
});

const { t, path } = useT();
const view = ref('grid');
// Le premier projet « mis en avant » (ex. RAVENALA) s'affiche en vedette, les autres en grille
const featured = computed(() => props.projects.find((project) => project.is_featured) ?? null);
const others = computed(() => props.projects.filter((project) => project !== featured.value));

onMounted(() => {
    try {
        const saved = window.localStorage.getItem('eden-project-view');
        view.value = ['grid', 'list'].includes(saved) ? saved : 'grid';
    } catch {
        view.value = 'grid';
    }
});

const setView = (value) => {
    view.value = value;

    try {
        window.localStorage.setItem('eden-project-view', value);
    } catch {
        // Stockage indisponible : le choix vaut pour la page en cours.
    }
};
</script>

<template>
    <section v-if="projects.length || canEdit" id="projets" class="bg-[var(--bg-projets,var(--muted))] py-16 lg:py-20">
        <div :class="containerClass">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading>
                    <EditableText :value="blocks?.title" fallback="Projets de Recherche en Cours" content-key="projets.title" :can-edit="canEdit" />
                    <template #description>
                        <EditableTextarea
                            :value="blocks?.description"
                            fallback="L'EDEN pilote et co-pilote plusieurs projets d'envergure financés par des institutions nationales et internationales."
                            content-key="projets.description"
                            :can-edit="canEdit"
                        />
                    </template>
                </SectionHeading>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-center gap-0.5 rounded-lg bg-secondary p-1" role="group" :aria-label="t('display')">
                        <button
                            v-for="option in [{ value: 'grid', label: t('grid'), icon: LayoutGrid }, { value: 'list', label: t('list'), icon: List }]"
                            :key="option.value"
                            type="button"
                            :aria-pressed="view === option.value ? 'true' : 'false'"
                            :title="option.label"
                            :class="cn('inline-flex h-8 items-center gap-1.5 rounded-md px-3 text-sm font-medium transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none', view === option.value ? 'bg-background text-foreground shadow-xs dark:bg-accent' : 'text-muted-foreground hover:text-foreground')"
                            @click="setView(option.value)"
                        >
                            <component :is="option.icon" class="size-4" aria-hidden="true" /><span class="hidden sm:inline">{{ option.label }}</span>
                        </button>
                    </div>
                    <Link v-if="showAllLink" :href="path('projects')" :class="buttonVariants({ variant: 'outline' })">{{ t('allProjects') }}<ArrowRight aria-hidden="true" /></Link>
                </div>
            </div>

            <div v-if="canEdit" class="mt-6 flex flex-wrap items-center gap-3 rounded-lg border border-dashed border-eden-300 p-3 text-sm dark:border-eden-800">
                <Settings2 class="size-4 text-eden-600" aria-hidden="true" />
                <span class="flex-1 text-muted-foreground">Les projets (ajout, ordre, page détaillée, RAVENALA…) se gèrent dans l’administration.</span>
                <a href="/admin/projects" :class="buttonVariants({ variant: 'outline', size: 'sm' })">Gérer les projets</a>
            </div>

            <FeaturedProjectCard v-if="featured" class="mt-8 sm:mt-10" :project="featured" :can-edit="canEdit" />

            <div v-if="others.length" :class="cn(featured ? 'mt-4 sm:mt-5' : 'mt-8 sm:mt-10', 'grid gap-3 sm:gap-5', view === 'list' ? 'grid-cols-1 lg:grid-cols-2' : 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4')">
                <ProjectCard v-for="project in others" :key="project.id" :project="project" :list="view === 'list'" :can-edit="canEdit" />
            </div>
        </div>
    </section>
</template>
