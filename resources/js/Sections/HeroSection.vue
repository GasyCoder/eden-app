<script setup>
defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <section id="hero">
        <div class="container">
            <div class="hero-left">
                <p class="hero-eyebrow">{{ blocks?.eyebrow ?? 'École Doctorale — Université de Mahajanga' }}</p>
                <h1>{{ blocks?.title ?? 'Former les Chercheurs de Demain au Cœur des' }} <span>{{ blocks?.title_highlight ?? 'Écosystèmes Naturels' }}</span></h1>
                <p class="hero-desc">{{ blocks?.subtitle ?? "L'EDEN prépare des docteurs de haut niveau engagés dans la recherche scientifique, la conservation de la biodiversité unique de Madagascar et le développement durable de la région." }}</p>
                <div class="hero-actions">
                    <a :href="blocks?.button_primary_url ?? '#about'" class="btn btn-primary">{{ blocks?.button_primary_label ?? "Découvrir l'EDEN" }}</a>
                    <a :href="blocks?.button_secondary_url ?? '#formation'" class="btn btn-secondary">{{ blocks?.button_secondary_label ?? 'Candidater' }}</a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-img img-ph">
                    <div class="ic">{{ blocks?.image_icon ?? '🌿' }}</div>
                    <span>{{ blocks?.image_label ?? 'Photo de terrain ou de recherche' }}</span>
                    <span style="font-size:0.72rem;opacity:.6;">{{ blocks?.image_caption ?? 'Région Boeny — Madagascar' }}</span>
                </div>
                <div class="hero-img-meta">
                    <span>{{ blocks?.image_meta ?? 'Terrain de recherche, Université de Mahajanga' }}</span>
                    <span class="tag">{{ blocks?.image_tag ?? 'Biodiversité · Recherche' }}</span>
                </div>
            </div>
        </div>
    </section>
</template>
