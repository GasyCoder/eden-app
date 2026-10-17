<script setup>
import { Quote, UserRound } from '@lucide/vue';
import EditableImage from '@/Components/Editable/EditableImage.vue';
import { cn } from '@/lib/utils';
import { containerClass } from '@/lib/variants';
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
    <section id="direction" class="bg-[var(--bg-direction,var(--background))] border-t py-16 lg:py-20">
        <div :class="cn(containerClass, 'grid items-center gap-10 md:grid-cols-[minmax(0,4fr)_minmax(0,8fr)] lg:gap-16')">
            <div class="mx-auto w-full max-w-xs md:max-w-none">
                <div class="relative aspect-[300/340] overflow-hidden rounded-xl border bg-secondary">
                    <img v-if="blocks?.photo" :src="blocks.photo" :alt="blocks?.name ? `Portrait de ${blocks.name}` : 'Portrait du Directeur'" class="size-full object-cover" width="680" height="770" loading="lazy" decoding="async">
                    <div v-else class="flex size-full flex-col items-center justify-center gap-2 text-sm text-muted-foreground">
                        <UserRound class="size-8 opacity-60" aria-hidden="true" />
                        <span><EditableText :value="blocks?.photo_label" fallback="Portrait du Directeur" content-key="direction.photo_label" :can-edit="canEdit" /></span>
                        <small><EditableText :value="blocks?.photo_caption" fallback="Photo officielle" content-key="direction.photo_caption" :can-edit="canEdit" /></small>
                    </div>
                    <EditableImage content-key="direction.photo" :can-edit="canEdit" />
                </div>
            </div>
            <figure>
                <Quote class="size-7 text-gold-500" aria-hidden="true" />
                <blockquote class="mt-4 text-lg leading-relaxed text-pretty text-foreground sm:text-xl">
                    <EditableTextarea
                        :value="blocks?.quote"
                        fallback="L'École Doctorale EDEN incarne notre engagement envers l'excellence scientifique et la préservation du patrimoine naturel de Madagascar. Nos docteurs sont les gardiens de demain de notre biodiversité unique, une responsabilité que nous assumons avec fierté et conviction."
                        content-key="direction.quote"
                        :can-edit="canEdit"
                    />
                </blockquote>
                <figcaption class="mt-8 flex items-center gap-4">
                    <span class="h-px w-10 bg-eden-600" aria-hidden="true"></span>
                    <span class="grid">
                        <strong class="font-semibold"><EditableText :value="blocks?.name" fallback="Prof. [Nom du Directeur]" content-key="direction.name" :can-edit="canEdit" /></strong>
                        <span class="text-sm text-muted-foreground"><EditableText :value="blocks?.title" fallback="Directeur de l'École Doctorale EDEN-UMG" content-key="direction.title" :can-edit="canEdit" /></span>
                    </span>
                </figcaption>
            </figure>
        </div>
    </section>
</template>
