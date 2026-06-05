<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import EditableText from '@/Components/Editable/EditableText.vue';

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});
const blocks = props.blocks;

const hasAnimated = ref(false);
const counts = ref({
    doctorants: 0,
    theses: 0,
    partnerships: 0,
    publications: 0,
});

let observer = null;
let animationFrame = null;

const numberBlock = (key, fallback) => {
    const value = Number.parseInt(props.blocks?.[key] ?? fallback, 10);

    return Number.isNaN(value) ? fallback : value;
};

const resolveTargets = () => ({
    doctorants: numberBlock('doctorants', 52),
    theses: numberBlock('theses', 31),
    partnerships: numberBlock('partnerships', 14),
    publications: numberBlock('publications', 78),
});

const fallbackTargets = {
    doctorants: '52',
    theses: '31',
    partnerships: '14',
    publications: '78',
};

const animateCounts = () => {
    if (hasAnimated.value) {
        return;
    }

    hasAnimated.value = true;
    const duration = 1800;
    const start = performance.now();

    const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);

        const targets = resolveTargets();

        counts.value = Object.fromEntries(
            Object.entries(targets).map(([key, target]) => [key, Math.round(ease * target)]),
        );

        if (progress < 1) {
            animationFrame = requestAnimationFrame(tick);
            return;
        }

        counts.value = { ...resolveTargets() };
    };

    animationFrame = requestAnimationFrame(tick);
};

onMounted(() => {
    const section = document.getElementById('chiffres');

    if (!section) {
        return;
    }

    observer = new IntersectionObserver((entries) => {
        if (entries[0]?.isIntersecting) {
            animateCounts();
            observer.disconnect();
        }
    }, { threshold: 0.3 });

    observer.observe(section);
});

onBeforeUnmount(() => {
    if (observer) {
        observer.disconnect();
    }

    if (animationFrame) {
        cancelAnimationFrame(animationFrame);
    }
});
</script>

<template>
    <section id="chiffres">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow"><EditableText :value="blocks?.eyebrow" fallback="EDEN en chiffres" content-key="stats.eyebrow" :can-edit="canEdit" /></span>
                <h2><EditableText :value="blocks?.title" fallback="L'École Doctorale en Données" content-key="stats.title" :can-edit="canEdit" /></h2>
            </div>
            <div class="chiffres-grid">
                <div class="chiffre-i reveal">
                    <span class="n" :data-target="blocks?.doctorants ?? fallbackTargets.doctorants">
                        <EditableText v-if="canEdit" :value="blocks?.doctorants" fallback="52" content-key="stats.doctorants" :can-edit="canEdit" type="number" />
                        <template v-else>{{ counts.doctorants }}</template>
                    </span>
                    <p class="lbl"><EditableText :value="blocks?.doctorants_label" fallback="Doctorants actifs" content-key="stats.doctorants_label" :can-edit="canEdit" /></p>
                </div>
                <div class="chiffre-i reveal">
                    <span class="n" :data-target="blocks?.theses ?? fallbackTargets.theses">
                        <EditableText v-if="canEdit" :value="blocks?.theses" fallback="31" content-key="stats.theses" :can-edit="canEdit" type="number" />
                        <template v-else>{{ counts.theses }}</template>
                    </span>
                    <p class="lbl"><EditableText :value="blocks?.theses_label" fallback="Thèses soutenues" content-key="stats.theses_label" :can-edit="canEdit" /></p>
                </div>
                <div class="chiffre-i reveal">
                    <span class="n" :data-target="blocks?.partnerships ?? fallbackTargets.partnerships">
                        <EditableText v-if="canEdit" :value="blocks?.partnerships" fallback="14" content-key="stats.partnerships" :can-edit="canEdit" type="number" />
                        <template v-else>{{ counts.partnerships }}</template>
                    </span>
                    <p class="lbl"><EditableText :value="blocks?.partnerships_label" fallback="Partenariats actifs" content-key="stats.partnerships_label" :can-edit="canEdit" /></p>
                </div>
                <div class="chiffre-i reveal">
                    <span class="n" :data-target="blocks?.publications ?? fallbackTargets.publications">
                        <EditableText v-if="canEdit" :value="blocks?.publications" fallback="78" content-key="stats.publications" :can-edit="canEdit" type="number" />
                        <template v-else>{{ counts.publications }}</template>
                    </span>
                    <p class="lbl"><EditableText :value="blocks?.publications_label" fallback="Publications scientifiques" content-key="stats.publications_label" :can-edit="canEdit" /></p>
                </div>
            </div>
        </div>
    </section>
</template>
