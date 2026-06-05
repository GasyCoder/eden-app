<script setup>
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import EditableImage from '@/Components/Editable/EditableImage.vue';

defineProps({
    blocks: {
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
    <section id="hero">
        <div class="container">
            <div class="hero-left">
                <p class="hero-eyebrow">
                    <EditableText :value="blocks?.eyebrow" fallback="École Doctorale — Université de Mahajanga" content-key="hero.eyebrow" :can-edit="canEdit" />
                </p>
                <h1>
                    <EditableText :value="blocks?.title" fallback="Former les Chercheurs de Demain au Cœur des" content-key="hero.title" :can-edit="canEdit" />
                    <span class="hero-highlight">
                        <EditableText :value="blocks?.title_highlight" fallback="Écosystèmes Naturels" content-key="hero.title_highlight" :can-edit="canEdit" />
                    </span>
                </h1>
                <p class="hero-desc">
                    <EditableTextarea
                        :value="blocks?.subtitle"
                        fallback="L'EDEN prépare des docteurs de haut niveau engagés dans la recherche scientifique, la conservation de la biodiversité unique de Madagascar et le développement durable de la région."
                        content-key="hero.subtitle"
                        :can-edit="canEdit"
                    />
                </p>
                <div class="hero-actions">
                    <a :href="blocks?.button_primary_url ?? '#about'" class="btn btn-primary">
                        <EditableText :value="blocks?.button_primary_label" fallback="Découvrir l'EDEN" content-key="hero.button_primary_label" :can-edit="canEdit" />
                    </a>
                    <span v-if="canEdit" class="eden-admin-link-edit">
                        <EditableText :value="blocks?.button_primary_url" fallback="#about" content-key="hero.button_primary_url" :can-edit="canEdit" type="link" />
                    </span>
                    <a :href="blocks?.button_secondary_url ?? '#formation'" class="btn btn-secondary">
                        <EditableText :value="blocks?.button_secondary_label" fallback="Candidater" content-key="hero.button_secondary_label" :can-edit="canEdit" />
                    </a>
                    <span v-if="canEdit" class="eden-admin-link-edit">
                        <EditableText :value="blocks?.button_secondary_url" fallback="#formation" content-key="hero.button_secondary_url" :can-edit="canEdit" type="link" />
                    </span>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-img img-ph" :class="{ 'has-image': blocks?.image }">
                    <img v-if="blocks?.image" :src="blocks.image" alt="Terrain de recherche">
                    <template v-else>
                        <div class="ic">
                            <EditableText :value="blocks?.image_icon" fallback="🌿" content-key="hero.image_icon" :can-edit="canEdit" />
                        </div>
                        <span>
                            <EditableText :value="blocks?.image_label" fallback="Photo de terrain ou de recherche" content-key="hero.image_label" :can-edit="canEdit" />
                        </span>
                        <span style="font-size:0.72rem;opacity:.6;">
                            <EditableText :value="blocks?.image_caption" fallback="Région Boeny — Madagascar" content-key="hero.image_caption" :can-edit="canEdit" />
                        </span>
                    </template>
                    <EditableImage content-key="hero.image" :can-edit="canEdit" />
                </div>
                <div class="hero-img-meta">
                    <span>
                        <EditableText :value="blocks?.image_meta" fallback="Terrain de recherche, Université de Mahajanga" content-key="hero.image_meta" :can-edit="canEdit" />
                    </span>
                    <span class="tag">
                        <EditableText :value="blocks?.image_tag" fallback="Biodiversité · Recherche" content-key="hero.image_tag" :can-edit="canEdit" />
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>
