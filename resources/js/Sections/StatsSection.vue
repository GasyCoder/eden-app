<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
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
                <span class="eyebrow">{{ blocks?.eyebrow ?? 'EDEN en chiffres' }}</span>
                <h2>{{ blocks?.title ?? "L'École Doctorale en Données" }}</h2>
            </div>
            <div class="chiffres-grid">
                <div class="chiffre-i reveal"><span class="n" :data-target="blocks?.doctorants ?? fallbackTargets.doctorants">{{ counts.doctorants }}</span><p class="lbl">{{ blocks?.doctorants_label ?? 'Doctorants actifs' }}</p></div>
                <div class="chiffre-i reveal"><span class="n" :data-target="blocks?.theses ?? fallbackTargets.theses">{{ counts.theses }}</span><p class="lbl">{{ blocks?.theses_label ?? 'Thèses soutenues' }}</p></div>
                <div class="chiffre-i reveal"><span class="n" :data-target="blocks?.partnerships ?? fallbackTargets.partnerships">{{ counts.partnerships }}</span><p class="lbl">{{ blocks?.partnerships_label ?? 'Partenariats actifs' }}</p></div>
                <div class="chiffre-i reveal"><span class="n" :data-target="blocks?.publications ?? fallbackTargets.publications">{{ counts.publications }}</span><p class="lbl">{{ blocks?.publications_label ?? 'Publications scientifiques' }}</p></div>
            </div>
        </div>
    </section>
</template>
