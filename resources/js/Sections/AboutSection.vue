<script setup>
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';

defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <section id="about">
        <div class="container">
            <div class="about-text reveal">
                <span class="eyebrow"><EditableText :value="blocks?.eyebrow" fallback="À propos" content-key="about.eyebrow" :can-edit="canEdit" /></span>
                <h2>
                    <EditableText :value="blocks?.title" fallback="L'École Doctorale" content-key="about.title" :can-edit="canEdit" /><br>
                    <EditableText :value="blocks?.title_second_line" fallback="Écosystèmes Naturels" content-key="about.title_second_line" :can-edit="canEdit" />
                </h2>
                <p>
                    <EditableTextarea
                        :value="blocks?.description_1"
                        fallback="Fondée en 2018, l'EDEN est une composante scientifique de l'Université de Mahajanga dédiée à la formation avancée en sciences de l'environnement et à la recherche sur les écosystèmes de Madagascar."
                        content-key="about.description_1"
                        :can-edit="canEdit"
                    />
                </p>
                <p>
                    <EditableTextarea
                        :value="blocks?.description_2"
                        fallback="Implantée dans la région Boeny, au cœur d'une biodiversité exceptionnelle, EDEN bénéficie d'un cadre privilégié pour l'étude des écosystèmes terrestres, aquatiques et marins du nord-ouest malgache."
                        content-key="about.description_2"
                        :can-edit="canEdit"
                    />
                </p>
                <a :href="blocks?.button_url ?? '#formation'" class="btn btn-primary">
                    <EditableText :value="blocks?.button_label" fallback="Accéder à la formation" content-key="about.button_label" :can-edit="canEdit" />
                </a>
                <span v-if="canEdit" class="eden-admin-link-edit">
                    <EditableText :value="blocks?.button_url" fallback="#formation" content-key="about.button_url" :can-edit="canEdit" type="link" />
                </span>
            </div>
            <div class="about-stats reveal">
                <div class="stat-c"><div class="val"><EditableText :value="stats?.doctorants" fallback="52" content-key="stats.doctorants" :can-edit="canEdit" type="number" /><sup><EditableText :value="stats?.doctorants_suffix" fallback="+" content-key="stats.doctorants_suffix" :can-edit="canEdit" /></sup></div><div class="lbl"><EditableText :value="stats?.doctorants_label" fallback="Doctorants actifs" content-key="stats.doctorants_label" :can-edit="canEdit" /></div></div>
                <div class="stat-c"><div class="val"><EditableText :value="stats?.theses" fallback="31" content-key="stats.theses" :can-edit="canEdit" type="number" /></div><div class="lbl"><EditableText :value="stats?.theses_label" fallback="Thèses soutenues" content-key="stats.theses_label" :can-edit="canEdit" /></div></div>
                <div class="stat-c"><div class="val"><EditableText :value="stats?.partnerships" fallback="14" content-key="stats.partnerships" :can-edit="canEdit" type="number" /></div><div class="lbl"><EditableText :value="stats?.partnerships_label" fallback="Partenariats actifs" content-key="stats.partnerships_label" :can-edit="canEdit" /></div></div>
                <div class="stat-c"><div class="val"><EditableText :value="stats?.publications" fallback="78" content-key="stats.publications" :can-edit="canEdit" type="number" /></div><div class="lbl"><EditableText :value="stats?.publications_label" fallback="Publications scientifiques" content-key="stats.publications_label" :can-edit="canEdit" /></div></div>
            </div>
        </div>
    </section>
</template>
