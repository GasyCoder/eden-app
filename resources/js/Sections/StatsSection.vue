<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const hasAnimated = ref(false);
const counts = ref({
    doctorants: 0,
    theses: 0,
    partnerships: 0,
    publications: 0,
});

let observer = null;
let animationFrame = null;

const targets = {
    doctorants: 52,
    theses: 31,
    partnerships: 14,
    publications: 78,
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

        counts.value = Object.fromEntries(
            Object.entries(targets).map(([key, target]) => [key, Math.round(ease * target)]),
        );

        if (progress < 1) {
            animationFrame = requestAnimationFrame(tick);
            return;
        }

        counts.value = { ...targets };
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
                <span class="eyebrow">EDEN en chiffres</span>
                <h2>L'École Doctorale en Données</h2>
            </div>
            <div class="chiffres-grid">
                <div class="chiffre-i reveal"><span class="n" data-target="52">{{ counts.doctorants }}</span><p class="lbl">Doctorants actifs</p></div>
                <div class="chiffre-i reveal"><span class="n" data-target="31">{{ counts.theses }}</span><p class="lbl">Thèses soutenues</p></div>
                <div class="chiffre-i reveal"><span class="n" data-target="14">{{ counts.partnerships }}</span><p class="lbl">Partenariats actifs</p></div>
                <div class="chiffre-i reveal"><span class="n" data-target="78">{{ counts.publications }}</span><p class="lbl">Publications scientifiques</p></div>
            </div>
        </div>
    </section>
</template>
