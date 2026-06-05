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

const fallbackTeams = [
    {
        title: 'Biodiversité et Écosystèmes Terrestres',
        subtitle: 'Responsable : Dr. [Nom du Responsable EAD 1]',
        meta: {
            number: 'EAD 1',
            themes: [
                'Diversité floristique et fonctionnelle des forêts malgaches',
                'Écologie des espèces endémiques et faune sauvage',
                'Conservation des ressources génétiques végétales',
                'Agroécologie et systèmes agricoles durables',
                'Restauration des écosystèmes forestiers dégradés',
            ],
        },
    },
    {
        title: 'Écosystèmes Aquatiques et Marins',
        subtitle: 'Responsable : Dr. [Nom du Responsable EAD 2]',
        meta: {
            number: 'EAD 2',
            themes: [
                'Biodiversité et santé des récifs coralliens',
                'Dynamique et conservation des mangroves',
                'Ressources halieutiques et pêche durable',
                'Qualité des eaux et écosystèmes lacustres',
                'Ichtyologie et biologie marine côtière',
            ],
        },
    },
];
</script>

<template>
    <section id="organisation">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow">{{ blocks?.eyebrow ?? 'Organisation' }}</span>
                <h2>{{ blocks?.title ?? "Équipes d'Accueil Doctoral" }}</h2>
                <p>{{ blocks?.description ?? "L'EDEN est structurée autour de deux Équipes d'Accueil Doctoral (EAD) regroupant chercheurs et doctorants autour de thématiques scientifiques complémentaires." }}</p>
            </div>
            <div class="ead-grid">
                <div v-for="team in (items?.length ? items : fallbackTeams)" :key="team.title" class="ead-card reveal">
                    <div class="ead-head">
                        <p class="ead-num">{{ team.meta?.number ?? 'EAD 1' }}</p>
                        <h3>{{ team.title }}</h3>
                        <p class="resp">{{ team.subtitle }}</p>
                    </div>
                    <div class="ead-body">
                        <div class="ead-themes">
                            <div v-for="theme in (team.meta?.themes ?? [])" :key="theme" class="ead-t">{{ theme }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
