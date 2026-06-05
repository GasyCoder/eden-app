<script setup>
defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <section id="direction">
        <div class="container">
            <div class="dir-quote reveal">
                <blockquote>{{ blocks?.quote ?? "« L'École Doctorale EDEN incarne notre engagement envers l'excellence scientifique et la préservation du patrimoine naturel de Madagascar. Nos docteurs sont les gardiens de demain de notre biodiversité unique — une responsabilité que nous assumons avec fierté et conviction. »" }}</blockquote>
                <div class="dir-sig">
                    <div class="line"></div>
                    <div>
                        <div class="name">{{ blocks?.name ?? 'Prof. [Nom du Directeur]' }}</div>
                        <div class="title">{{ blocks?.title ?? "Directeur de l'École Doctorale EDEN-UMG" }}</div>
                    </div>
                </div>
            </div>
            <div class="dir-photo img-ph reveal">
                <div class="ic">{{ blocks?.photo_icon ?? '👤' }}</div>
                <span>{{ blocks?.photo_label ?? 'Portrait du Directeur' }}</span>
                <span style="font-size:0.7rem;opacity:.55;">{{ blocks?.photo_caption ?? 'Photo officielle' }}</span>
            </div>
        </div>
    </section>
</template>
