<script setup>
import { ArrowRight } from '@lucide/vue';
import { computed } from 'vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import LinkEdit from '@/Components/Editable/LinkEdit.vue';
import { cn } from '@/lib/utils';
import { buttonVariants, containerClass } from '@/lib/variants';

// Présentation de l'École et chiffres clés (clés about.* et stats.* existantes).
const props = defineProps({
    blocks: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
    canEdit: { type: Boolean, default: false },
});

const figures = computed(() => [
    { key: 'doctorants', fallback: '52', label: 'Doctorants actifs', suffixKey: 'doctorants_suffix', suffixFallback: '+' },
    { key: 'theses', fallback: '31', label: 'Thèses soutenues' },
    { key: 'partnerships', fallback: '14', label: 'Partenariats actifs' },
    { key: 'publications', fallback: '78', label: 'Publications scientifiques' },
]);
</script>

<template>
    <section id="about" class="border-t bg-[var(--bg-about,var(--background))] py-16 lg:py-20">
        <div :class="cn(containerClass, 'grid gap-12 lg:grid-cols-2 lg:gap-16')">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-balance sm:text-3xl">
                    <EditableText :value="blocks?.title" fallback="L'École Doctorale" content-key="about.title" :can-edit="canEdit" />{{ ' ' }}
                    <span class="text-eden-700 dark:text-eden-400"><EditableText :value="blocks?.title_second_line" fallback="Écosystèmes Naturels" content-key="about.title_second_line" :can-edit="canEdit" /></span>
                </h2>
                <p class="mt-5 leading-relaxed text-pretty text-foreground">
                    <EditableTextarea
                        :value="blocks?.description_1"
                        fallback="Fondée en 2018, l'EDEN est une composante scientifique de l'Université de Mahajanga dédiée à la formation avancée en sciences de l'environnement et à la recherche sur les écosystèmes de Madagascar."
                        content-key="about.description_1"
                        :can-edit="canEdit"
                    />
                </p>
                <p class="mt-4 leading-relaxed text-pretty text-muted-foreground">
                    <EditableTextarea
                        :value="blocks?.description_2"
                        fallback="Implantée dans la région Boeny, au cœur d'une biodiversité exceptionnelle, EDEN bénéficie d'un cadre privilégié pour l'étude des écosystèmes terrestres, aquatiques et marins du nord-ouest malgache."
                        content-key="about.description_2"
                        :can-edit="canEdit"
                    />
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a :href="blocks?.button_url ?? '#formation'" :class="buttonVariants({ variant: 'outline' })">
                        <EditableText :value="blocks?.button_label" fallback="Accéder à la formation" content-key="about.button_label" :can-edit="canEdit" />
                        <ArrowRight aria-hidden="true" />
                    </a>
                    <LinkEdit :value="blocks?.button_url" fallback="#formation" content-key="about.button_url" :can-edit="canEdit" />
                </div>
            </div>

            <dl class="grid grid-cols-2 self-start rounded-xl border">
                <div
                    v-for="(figure, index) in figures"
                    :key="figure.key"
                    :class="cn('flex flex-col-reverse gap-1 p-5 sm:p-6', index % 2 === 1 && 'border-l', index > 1 && 'border-t')"
                >
                    <dt class="text-sm text-muted-foreground"><EditableText :value="stats?.[`${figure.key}_label`]" :fallback="figure.label" :content-key="`stats.${figure.key}_label`" :can-edit="canEdit" /></dt>
                    <dd class="text-3xl font-semibold tracking-tight tabular-nums text-eden-700 dark:text-eden-400">
                        <EditableText :value="stats?.[figure.key]" :fallback="figure.fallback" :content-key="`stats.${figure.key}`" :can-edit="canEdit" type="number" /><span v-if="figure.suffixKey" class="text-gold-600"><EditableText :value="stats?.[figure.suffixKey]" :fallback="figure.suffixFallback" :content-key="`stats.${figure.suffixKey}`" :can-edit="canEdit" /></span>
                    </dd>
                </div>
            </dl>
        </div>
    </section>
</template>
