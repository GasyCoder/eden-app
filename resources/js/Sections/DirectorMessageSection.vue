<script setup>
import EditableImage from '@/Components/Editable/EditableImage.vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';

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
    <section id="direction">
        <div class="container">
            <div class="dir-quote reveal">
                <blockquote>
                    <EditableTextarea
                        :value="blocks?.quote"
                        fallback="« L'École Doctorale EDEN incarne notre engagement envers l'excellence scientifique et la préservation du patrimoine naturel de Madagascar. Nos docteurs sont les gardiens de demain de notre biodiversité unique — une responsabilité que nous assumons avec fierté et conviction. »"
                        content-key="direction.quote"
                        :can-edit="canEdit"
                    />
                </blockquote>
                <div class="dir-sig">
                    <div class="line"></div>
                    <div>
                        <div class="name"><EditableText :value="blocks?.name" fallback="Prof. [Nom du Directeur]" content-key="direction.name" :can-edit="canEdit" /></div>
                        <div class="title"><EditableText :value="blocks?.title" fallback="Directeur de l'École Doctorale EDEN-UMG" content-key="direction.title" :can-edit="canEdit" /></div>
                    </div>
                </div>
            </div>
            <div class="dir-photo img-ph reveal" :class="{ 'has-image': blocks?.photo }">
                <img v-if="blocks?.photo" :src="blocks.photo" alt="Portrait du Directeur">
                <template v-else>
                    <div class="ic" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8.5" r="3.8"/><path d="M5 20c0-3.6 3.1-6 7-6s7 2.4 7 6"/></svg>
                    </div>
                    <span><EditableText :value="blocks?.photo_label" fallback="Portrait du Directeur" content-key="direction.photo_label" :can-edit="canEdit" /></span>
                    <span style="font-size:0.7rem;opacity:.55;"><EditableText :value="blocks?.photo_caption" fallback="Photo officielle" content-key="direction.photo_caption" :can-edit="canEdit" /></span>
                </template>
                <EditableImage content-key="direction.photo" :can-edit="canEdit" />
            </div>
        </div>
    </section>
</template>
