<script setup>
defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <section id="about">
        <div class="container">
            <div class="about-text reveal">
                <span class="eyebrow">{{ blocks?.eyebrow ?? 'À propos' }}</span>
                <h2>{{ blocks?.title ?? "L'École Doctorale" }}<br>{{ blocks?.title_second_line ?? 'Écosystèmes Naturels' }}</h2>
                <p>{{ blocks?.description_1 ?? "Fondée en 2018, l'EDEN est une composante scientifique de l'Université de Mahajanga dédiée à la formation avancée en sciences de l'environnement et à la recherche sur les écosystèmes de Madagascar." }}</p>
                <p>{{ blocks?.description_2 ?? "Implantée dans la région Boeny, au cœur d'une biodiversité exceptionnelle, EDEN bénéficie d'un cadre privilégié pour l'étude des écosystèmes terrestres, aquatiques et marins du nord-ouest malgache." }}</p>
                <a :href="blocks?.button_url ?? '#formation'" class="btn btn-primary">{{ blocks?.button_label ?? 'Accéder à la formation' }}</a>
            </div>
            <div class="about-stats reveal">
                <div class="stat-c"><div class="val">{{ stats?.doctorants ?? '52' }}<sup>{{ stats?.doctorants_suffix ?? '+' }}</sup></div><div class="lbl">{{ stats?.doctorants_label ?? 'Doctorants actifs' }}</div></div>
                <div class="stat-c"><div class="val">{{ stats?.theses ?? '31' }}</div><div class="lbl">{{ stats?.theses_label ?? 'Thèses soutenues' }}</div></div>
                <div class="stat-c"><div class="val">{{ stats?.partnerships ?? '14' }}</div><div class="lbl">{{ stats?.partnerships_label ?? 'Partenariats actifs' }}</div></div>
                <div class="stat-c"><div class="val">{{ stats?.publications ?? '78' }}</div><div class="lbl">{{ stats?.publications_label ?? 'Publications scientifiques' }}</div></div>
            </div>
        </div>
    </section>
</template>
