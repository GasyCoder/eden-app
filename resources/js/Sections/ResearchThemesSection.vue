<script setup>
import { ref } from 'vue';

defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    eadOneThemes: {
        type: Array,
        default: () => [],
    },
    eadTwoThemes: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('t1');

const fallbackEadOneThemes = [
    { title: 'Conservation de la Biodiversité Végétale', description: 'Inventaires floristiques, cartographie et évaluation du statut de conservation des plantes endémiques malgaches de la région Boeny.' },
    { title: 'Écologie des Lémuriens et Faune Endémique', description: "Comportement, écologie alimentaire et conservation des lémuriens et autres espèces endémiques de la zone d'influence de l'UMG." },
    { title: 'Restauration des Écosystèmes Dégradés', description: 'Techniques de restauration écologique, suivi de la régénération naturelle et programmes de reboisement communautaire.' },
    { title: 'Agroforesterie et Systèmes Durables', description: 'Développement de modèles agroforestiers adaptés aux conditions écologiques et socio-économiques du nord-ouest de Madagascar.' },
];

const fallbackEadTwoThemes = [
    { title: 'Suivi des Récifs Coralliens', description: "Monitoring de l'état de santé des récifs du Canal de Mozambique : couverture corallienne, biodiversité associée, blanchissement thermique." },
    { title: 'Mangroves et Zones Humides', description: 'Cartographie, dynamique temporelle et services écosystémiques des mangroves de la côte nord-ouest de Madagascar.' },
    { title: 'Ressources Halieutiques Durables', description: 'Évaluation des stocks, pratiques de pêche artisanale et gestion intégrée des ressources marines côtières.' },
    { title: 'Limnologie et Eaux Douces', description: "Qualité physicochimique et biologique des eaux, biodiversité aquatique des lacs et cours d'eau de la région Boeny." },
];
</script>

<template>
    <section id="recherche">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow">{{ blocks?.eyebrow ?? 'Recherche' }}</span>
                <h2>{{ blocks?.title ?? 'Thématiques de Recherche' }}</h2>
                <p>{{ blocks?.description ?? "Les activités de l'EDEN couvrent un large spectre, des forêts tropicales aux récifs coralliens, en passant par les zones humides et les agroécosystèmes malgaches." }}</p>
            </div>
            <div class="reveal">
                <div class="res-tabs">
                    <button class="tab-btn" :class="{ active: activeTab === 't1' }" type="button" @click="activeTab = 't1'">{{ blocks?.tab_ead_1 ?? 'EAD 1 — Écosystèmes Terrestres' }}</button>
                    <button class="tab-btn" :class="{ active: activeTab === 't2' }" type="button" @click="activeTab = 't2'">{{ blocks?.tab_ead_2 ?? 'EAD 2 — Aquatique & Marin' }}</button>
                </div>
                <div id="t1" class="tab-pane" :class="{ active: activeTab === 't1' }">
                    <div v-for="theme in (eadOneThemes?.length ? eadOneThemes : fallbackEadOneThemes)" :key="theme.title" class="res-card"><h4>{{ theme.title }}</h4><p>{{ theme.description }}</p></div>
                </div>
                <div id="t2" class="tab-pane" :class="{ active: activeTab === 't2' }">
                    <div v-for="theme in (eadTwoThemes?.length ? eadTwoThemes : fallbackEadTwoThemes)" :key="theme.title" class="res-card"><h4>{{ theme.title }}</h4><p>{{ theme.description }}</p></div>
                </div>
            </div>
        </div>
    </section>
</template>
