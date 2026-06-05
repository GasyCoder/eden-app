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

const fallbackItems = [
    {
        subtitle: 'Mission',
        title: 'Former pour la Science',
        description: 'Former des chercheurs de haut niveau capables de contribuer au développement durable et à la conservation des écosystèmes naturels malgaches, dans une démarche rigoureuse et éthique.',
    },
    {
        subtitle: 'Vision',
        title: "Pôle d'Excellence Régional",
        description: "Devenir un centre d'excellence régional et international en sciences des écosystèmes, reconnu pour la qualité de ses doctorants et l'impact durable de ses recherches sur l'environnement malgache.",
    },
    {
        subtitle: 'Objectifs',
        title: 'Science & Conservation',
        description: 'Promouvoir la recherche scientifique, renforcer les partenariats nationaux et internationaux, et contribuer activement aux politiques de conservation de la biodiversité à Madagascar.',
    },
];
</script>

<template>
    <section id="mission">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow">{{ blocks?.eyebrow ?? 'Nos fondements' }}</span>
                <h2>{{ blocks?.title ?? 'Mission, Vision & Objectifs' }}</h2>
            </div>
            <div class="mvision-grid">
                <div v-for="item in (items?.length ? items : fallbackItems)" :key="item.title" class="mv-card reveal">
                    <p class="mv-lbl">{{ item.subtitle }}</p>
                    <h3>{{ item.title }}</h3>
                    <p>{{ item.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
