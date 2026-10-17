<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import { useT } from '@/lib/i18n';
import { buttonVariants, containerClass } from '@/lib/variants';

// Aperçu du parcours doctoral sur l'accueil (le détail est sur /formation).
const props = defineProps({
    blocks: { type: Object, default: () => ({}) },
    steps: { type: Array, default: () => [] },
    canEdit: { type: Boolean, default: false },
});

const { t, path } = useT();

const fallbackSteps = [
    { subtitle: 'Année 1', title: 'Initiation à la Recherche', description: 'Séminaires, revue bibliographique, protocole expérimental', meta: { number: '1' } },
    { subtitle: 'Année 2', title: 'Recherche Active', description: 'Terrains, analyses, publications, conférences', meta: { number: '2' } },
    { subtitle: 'Année 3', title: 'Rédaction & Soutenance', description: 'Rédaction de thèse, pré-soutenance, soutenance publique', meta: { number: '3' } },
];

const stepsForDisplay = () => (props.steps?.length ? props.steps : fallbackSteps);
</script>

<template>
    <section id="formation" class="border-t bg-muted py-16 lg:py-20">
        <div :class="containerClass">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading>
                    <EditableText :value="blocks?.title" fallback="Parcours Doctoral" content-key="formation.title" :can-edit="canEdit" />
                    <template #description>
                        <EditableTextarea
                            :value="blocks?.description"
                            fallback="L'EDEN offre une formation de 3 ans alliant excellence scientifique, encadrement personnalisé et développement des compétences du chercheur."
                            content-key="formation.description"
                            :can-edit="canEdit"
                        />
                    </template>
                </SectionHeading>
                <Link :href="path('training')" :class="buttonVariants({ variant: 'outline' })">
                    {{ t('exploreTraining') }}
                    <ArrowRight aria-hidden="true" />
                </Link>
            </div>
            <ol class="mt-10 grid gap-4 md:grid-cols-3">
                <li v-for="(step, index) in stepsForDisplay()" :key="step.id ?? step.title" class="rounded-xl border bg-card p-6">
                    <span class="grid size-9 place-items-center rounded-full bg-eden-600 text-sm font-semibold text-white">{{ step.meta?.number ?? index + 1 }}</span>
                    <p class="mt-4 text-sm font-medium text-gold-700 dark:text-gold-400">{{ step.subtitle }}</p>
                    <h3 class="mt-1 text-base font-semibold">{{ step.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ step.description }}</p>
                </li>
            </ol>
        </div>
    </section>
</template>
