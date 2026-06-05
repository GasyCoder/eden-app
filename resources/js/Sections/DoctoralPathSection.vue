<script setup>
defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    conditions: {
        type: Array,
        default: () => [],
    },
    steps: {
        type: Array,
        default: () => [],
    },
    units: {
        type: Array,
        default: () => [],
    },
});

const fallbackConditions = [
    { title: "Être titulaire d'un Master 2 ou diplôme équivalent" },
    { title: "Disposer d'un projet de recherche rédigé et validé" },
    { title: 'Avoir un directeur de thèse habilité (HDR)' },
    { title: "Réussir l'entretien devant le Conseil de l'École Doctorale" },
    { title: "Obtenir l'inscription administrative à l'Université de Mahajanga" },
];

const fallbackSteps = [
    { subtitle: 'Année 1', title: 'Initiation à la Recherche', description: 'Séminaires, revue bibliographique, protocole expérimental', meta: { number: '1' } },
    { subtitle: 'Année 2', title: 'Recherche Active', description: 'Terrains, analyses, publications, conférences', meta: { number: '2' } },
    { subtitle: 'Année 3', title: 'Rédaction & Soutenance', description: 'Rédaction de thèse, pré-soutenance, soutenance publique', meta: { number: '3' } },
];

const fallbackUnits = [
    { title: 'Méthodologie de la recherche scientifique' },
    { title: 'Biostatistiques et analyse de données' },
    { title: 'Rédaction et publication scientifique' },
    { title: 'Éthique et déontologie de la recherche' },
    { title: 'Valorisation et transfert de connaissances' },
];
</script>

<template>
    <section id="formation">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow">{{ blocks?.eyebrow ?? 'Formation' }}</span>
                <h2>{{ blocks?.title ?? 'Parcours Doctoral' }}</h2>
                <p>{{ blocks?.description ?? "L'EDEN offre une formation de 3 ans alliant excellence scientifique, encadrement personnalisé et développement des compétences du chercheur." }}</p>
            </div>
            <div class="formation-grid">
                <div class="card reveal">
                    <h3 style="font-size:1.1rem;margin-bottom:1.25rem;">{{ blocks?.conditions_title ?? "Conditions d'Admission" }}</h3>
                    <div class="check-list">
                        <div v-for="condition in (conditions?.length ? conditions : fallbackConditions)" :key="condition.title" class="check-i">{{ condition.title }}</div>
                    </div>
                </div>
                <div class="card reveal">
                    <h3 style="font-size:1.1rem;margin-bottom:1.25rem;">{{ blocks?.path_title ?? 'Parcours en 3 Ans' }}</h3>
                    <div class="parcours">
                        <div v-for="step in (steps?.length ? steps : fallbackSteps)" :key="step.title" class="step">
                            <div class="step-l"><div class="step-dot">{{ step.meta?.number ?? '1' }}</div><div class="step-line"></div></div>
                            <div class="step-r">
                                <p class="step-yr">{{ step.subtitle }}</p>
                                <p class="step-t">{{ step.title }}</p>
                                <p class="step-d">{{ step.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card reveal">
                    <h3 style="font-size:1.1rem;margin-bottom:1.25rem;">{{ blocks?.units_title ?? 'Unités de Formation' }}</h3>
                    <div class="check-list">
                        <div v-for="unit in (units?.length ? units : fallbackUnits)" :key="unit.title" class="check-i">{{ unit.title }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
