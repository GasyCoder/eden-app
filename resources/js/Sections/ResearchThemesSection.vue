<script setup>
import { ref } from 'vue';

const activeTab = ref('t1');
</script>

<template>
    <section id="recherche">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow">Recherche</span>
                <h2>Thématiques de Recherche</h2>
                <p>Les activités de l'EDEN couvrent un large spectre, des forêts tropicales aux récifs coralliens, en passant par les zones humides et les agroécosystèmes malgaches.</p>
            </div>
            <div class="reveal">
                <div class="res-tabs">
                    <button class="tab-btn" :class="{ active: activeTab === 't1' }" type="button" @click="activeTab = 't1'">EAD 1 — Écosystèmes Terrestres</button>
                    <button class="tab-btn" :class="{ active: activeTab === 't2' }" type="button" @click="activeTab = 't2'">EAD 2 — Aquatique &amp; Marin</button>
                </div>
                <div id="t1" class="tab-pane" :class="{ active: activeTab === 't1' }">
                    <div class="res-card"><h4>Conservation de la Biodiversité Végétale</h4><p>Inventaires floristiques, cartographie et évaluation du statut de conservation des plantes endémiques malgaches de la région Boeny.</p></div>
                    <div class="res-card"><h4>Écologie des Lémuriens et Faune Endémique</h4><p>Comportement, écologie alimentaire et conservation des lémuriens et autres espèces endémiques de la zone d'influence de l'UMG.</p></div>
                    <div class="res-card"><h4>Restauration des Écosystèmes Dégradés</h4><p>Techniques de restauration écologique, suivi de la régénération naturelle et programmes de reboisement communautaire.</p></div>
                    <div class="res-card"><h4>Agroforesterie et Systèmes Durables</h4><p>Développement de modèles agroforestiers adaptés aux conditions écologiques et socio-économiques du nord-ouest de Madagascar.</p></div>
                </div>
                <div id="t2" class="tab-pane" :class="{ active: activeTab === 't2' }">
                    <div class="res-card"><h4>Suivi des Récifs Coralliens</h4><p>Monitoring de l'état de santé des récifs du Canal de Mozambique : couverture corallienne, biodiversité associée, blanchissement thermique.</p></div>
                    <div class="res-card"><h4>Mangroves et Zones Humides</h4><p>Cartographie, dynamique temporelle et services écosystémiques des mangroves de la côte nord-ouest de Madagascar.</p></div>
                    <div class="res-card"><h4>Ressources Halieutiques Durables</h4><p>Évaluation des stocks, pratiques de pêche artisanale et gestion intégrée des ressources marines côtières.</p></div>
                    <div class="res-card"><h4>Limnologie et Eaux Douces</h4><p>Qualité physicochimique et biologique des eaux, biodiversité aquatique des lacs et cours d'eau de la région Boeny.</p></div>
                </div>
            </div>
        </div>
    </section>
</template>
