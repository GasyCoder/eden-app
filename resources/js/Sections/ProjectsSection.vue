<script setup>
defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    items: {
        type: Array,
        default: () => [],
    },
});

const fallbackProjects = [
    {
        title: 'BIODIVERS-BOENY',
        subtitle: 'En cours',
        description: "Conservation et valorisation des espèces endémiques de la Région Boeny : inventaires, suivis fauniques et réseau d'aires protégées communautaires.",
        meta: { icon: '🌿', image_label: 'Terrain — forêt endémique Boeny', ead: 'EAD 1', funding: 'Financement : AFD', period: '2023 – 2026', badge_class: 'badge-active' },
    },
    {
        title: 'CORAL-MOZAMBIQUE',
        subtitle: 'En cours',
        description: "Monitoring continu et restauration des récifs coralliens du Canal de Mozambique face au blanchissement thermique et à l'acidification des océans.",
        meta: { icon: '🐠', image_label: 'Terrain — récif corallien', ead: 'EAD 2', funding: 'Financement : Union Européenne', period: '2022 – 2025', badge_class: 'badge-active' },
    },
    {
        title: 'CLIMATE-COAST',
        subtitle: 'Démarrage',
        description: "Évaluation et stratégies d'adaptation des écosystèmes côtiers et terrestres de la région Boeny face aux impacts du changement climatique.",
        meta: { icon: '🌊', image_label: 'Terrain — mangroves et zone côtière', ead: 'EAD 1 & 2', funding: 'Financement : GEF', period: '2025 – 2028', badge_class: 'badge-new' },
    },
];
</script>

<template>
    <section id="projets">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow">{{ blocks?.eyebrow ?? 'Projets' }}</span>
                <h2>{{ blocks?.title ?? 'Projets de Recherche en Cours' }}</h2>
                <p>{{ blocks?.description ?? "L'EDEN pilote et co-pilote plusieurs projets d'envergure financés par des institutions nationales et internationales." }}</p>
            </div>
            <div class="grid-3">
                <div v-for="project in (items?.length ? items : fallbackProjects)" :key="project.title" class="proj-card reveal">
                    <div class="proj-img img-ph">
                        <div class="ic">{{ project.meta?.icon ?? '🌿' }}</div>
                        <span>{{ project.meta?.image_label ?? 'Terrain — forêt endémique Boeny' }}</span>
                    </div>
                    <div class="proj-body">
                        <div class="proj-meta">
                            <span class="badge" :class="project.meta?.badge_class ?? 'badge-active'">{{ project.subtitle ?? 'En cours' }}</span>
                            <span class="proj-ead">{{ project.meta?.ead ?? 'EAD 1' }}</span>
                        </div>
                        <h4>{{ project.title ?? 'BIODIVERS-BOENY' }}</h4>
                        <p>{{ project.description ?? "Conservation et valorisation des espèces endémiques de la Région Boeny : inventaires, suivis fauniques et réseau d'aires protégées communautaires." }}</p>
                        <div class="proj-foot">
                            <span>{{ project.meta?.funding ?? 'Financement : AFD' }}</span>
                            <span>{{ project.meta?.period ?? '2023 – 2026' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
