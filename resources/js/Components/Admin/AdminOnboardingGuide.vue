<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { ArrowLeftRight } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { buttonVariants } from '@/lib/variants';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const canEdit = computed(() => Boolean(page.props.canEdit));
const completed = computed(() => Boolean(user.value?.onboarding_completed_at));
const currentStep = ref(0);
const dismissed = ref(false);
const processing = ref(false);
const placement = ref('right');

const steps = [
    {
        title: 'Bienvenue dans l’édition',
        text: 'Vous modifiez les contenus directement sur la page publique : ce que vous voyez est ce que verront les visiteurs.',
    },
    {
        title: 'La barre d’administration',
        text: 'En bas de l’écran : l’interrupteur « Outils d’édition » affiche ou masque les crayons. Vous y trouvez aussi l’historique, votre profil et la déconnexion.',
    },
    {
        title: 'Modifier un texte',
        text: 'Cliquez sur le crayon à côté d’un titre, d’un paragraphe, d’un bouton ou d’un chiffre. Entrée enregistre, Échap annule.',
    },
    {
        title: 'Remplacer une image',
        text: 'Le bouton « Remplacer » apparaît en haut à droite des images. Pour la bannière et le portrait, vous choisissez ensuite le cadrage.',
    },
    {
        title: 'Gérer les cartes',
        text: 'Projets, équipes, partenaires, étapes… Utilisez « Ajouter », le crayon pour modifier ou supprimer, et les flèches pour changer l’ordre.',
    },
    {
        title: 'Historique et rôles',
        text: 'Chaque modification est enregistrée et peut être restaurée. Les éditeurs gèrent les contenus ; le superadmin gère aussi le référencement, l’apparence et les comptes.',
    },
];

const visible = computed(() => canEdit.value && user.value && !completed.value && !dismissed.value);
const isFirstStep = computed(() => currentStep.value === 0);
const isLastStep = computed(() => currentStep.value === steps.length - 1);
const progressLabel = computed(() => `${currentStep.value + 1} / ${steps.length}`);

const togglePlacement = () => {
    placement.value = placement.value === 'right' ? 'left' : 'right';
};

const previous = () => {
    if (!isFirstStep.value) {
        currentStep.value -= 1;
    }
};

const next = () => {
    if (!isLastStep.value) {
        currentStep.value += 1;
    }
};

const finish = () => {
    processing.value = true;

    router.post('/me/onboarding/complete', {}, {
        preserveScroll: true,
        onSuccess: () => {
            dismissed.value = true;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

watch(() => user.value?.id, () => {
    currentStep.value = 0;
    dismissed.value = false;
    placement.value = 'right';
});
</script>

<template>
    <section
        v-if="visible"
        :class="['fixed inset-x-3 bottom-20 z-[61] grid gap-3 rounded-xl border bg-popover p-5 shadow-xl animate-in fade-in-0 slide-in-from-bottom-2 md:inset-x-auto md:bottom-24 md:w-96', placement === 'left' ? 'md:left-4' : 'md:right-4']"
        role="dialog"
        aria-live="polite"
        aria-labelledby="onboarding-title"
    >
        <div class="-mt-1 -mr-2 flex items-center justify-between gap-3">
            <span class="text-xs font-medium text-eden-700 dark:text-eden-300">Prise en main · {{ progressLabel }}</span>
            <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" :title="placement === 'right' ? 'Déplacer à gauche' : 'Déplacer à droite'" aria-label="Déplacer le guide" @click="togglePlacement">
                <ArrowLeftRight aria-hidden="true" />
            </button>
        </div>
        <ol class="flex gap-1.5" aria-hidden="true">
            <li v-for="(step, index) in steps" :key="step.title" :class="['h-1 flex-1 rounded-full', index < currentStep ? 'bg-eden-600' : index === currentStep ? 'bg-gold-500' : 'bg-secondary']"></li>
        </ol>
        <h3 id="onboarding-title" class="text-base font-semibold">{{ steps[currentStep].title }}</h3>
        <p class="text-sm leading-relaxed text-muted-foreground">{{ steps[currentStep].text }}</p>
        <div class="mt-1 flex flex-wrap items-center justify-end gap-2">
            <button v-if="!isLastStep" type="button" :class="buttonVariants({ variant: 'ghost', size: 'sm' })" class="mr-auto" :disabled="processing" @click="finish">Passer le guide</button>
            <button v-if="!isFirstStep" type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" :disabled="processing" @click="previous">Précédent</button>
            <button v-if="!isLastStep" type="button" :class="buttonVariants({ size: 'sm' })" @click="next">Suivant</button>
            <button v-else type="button" :class="buttonVariants({ size: 'sm' })" :disabled="processing" @click="finish">C’est parti</button>
        </div>
    </section>
</template>
