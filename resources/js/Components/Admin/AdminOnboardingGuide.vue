<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

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
        text: 'Vous pouvez modifier les contenus directement depuis la page publique, sans passer par un tableau de bord.',
    },
    {
        title: 'Activer ou masquer les outils',
        text: 'La barre verticale à gauche permet d’afficher les crayons, d’ouvrir l’historique et d’accéder à votre profil.',
    },
    {
        title: 'Modifier textes et chiffres',
        text: 'Cliquez sur un crayon près d’un titre, paragraphe, bouton ou compteur, puis enregistrez ou annulez.',
    },
    {
        title: 'Remplacer les images',
        text: 'Les crayons sur les images permettent de remplacer le visuel. Pour le hero et le directeur, vous pouvez rogner avant d’enregistrer.',
    },
    {
        title: 'Gérer les cartes',
        text: 'Sur les projets, partenaires, équipes et parcours, utilisez les actions ajouter, modifier, supprimer et réordonner.',
    },
    {
        title: 'Historique et rôles',
        text: 'L’historique garde les dernières modifications. Un éditeur modifie les contenus, le superadmin gère aussi les zones protégées.',
    },
];

const visible = computed(() => canEdit.value && user.value && !completed.value && !dismissed.value);
const isFirstStep = computed(() => currentStep.value === 0);
const isLastStep = computed(() => currentStep.value === steps.length - 1);
const progressLabel = computed(() => `${currentStep.value + 1} / ${steps.length}`);

const setPlacement = (value) => {
    placement.value = value;
};

const syncPlacementWithStep = () => {
    placement.value = currentStep.value % 2 === 0 ? 'right' : 'left';
};

const previous = () => {
    if (!isFirstStep.value) {
        currentStep.value -= 1;
        syncPlacementWithStep();
    }
};

const next = () => {
    if (!isLastStep.value) {
        currentStep.value += 1;
        syncPlacementWithStep();
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
    <div
        v-if="visible"
        class="eden-onboarding"
        :class="`is-${placement}`"
        role="dialog"
        aria-live="polite"
        aria-label="Guide d'utilisation du mode édition"
    >
        <div class="eden-onboarding-top">
            <span>Guide édition</span>
            <div class="eden-onboarding-position" aria-label="Déplacer le guide">
                <button
                    type="button"
                    :class="{ active: placement === 'left' }"
                    title="Placer à gauche"
                    aria-label="Placer le guide à gauche"
                    @click="setPlacement('left')"
                >
                    ←
                </button>
                <button
                    type="button"
                    :class="{ active: placement === 'right' }"
                    title="Placer à droite"
                    aria-label="Placer le guide à droite"
                    @click="setPlacement('right')"
                >
                    →
                </button>
            </div>
        </div>

        <div class="eden-onboarding-progress">
            <span :style="{ width: `${((currentStep + 1) / steps.length) * 100}%` }"></span>
        </div>

        <p class="eden-onboarding-count">{{ progressLabel }}</p>
        <h3>{{ steps[currentStep].title }}</h3>
        <p>{{ steps[currentStep].text }}</p>

        <div class="eden-onboarding-actions">
            <button type="button" class="btn-mini ghost" :disabled="isFirstStep || processing" @click="previous">
                Précédent
            </button>
            <button v-if="!isLastStep" type="button" class="btn-mini primary" @click="next">
                Suivant
            </button>
            <button v-else type="button" class="btn-mini primary" :disabled="processing" @click="finish">
                {{ processing ? '...' : 'Terminer' }}
            </button>
        </div>
    </div>
</template>
