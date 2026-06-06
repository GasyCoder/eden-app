import '../css/app.css';

// Polices auto-hébergées (accents FR via latin-ext inclus)
// Titres : Libre Baskerville (serif académique) — Texte : Source Sans 3 (sans-serif humaniste)
import '@fontsource/source-sans-3/400.css';
import '@fontsource/source-sans-3/500.css';
import '@fontsource/source-sans-3/600.css';
import '@fontsource/source-sans-3/700.css';
import '@fontsource/libre-baskerville/400.css';
import '@fontsource/libre-baskerville/400-italic.css';
import '@fontsource/libre-baskerville/700.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, onBeforeUnmount, ref } from 'vue';
import PageLoadingSkeleton from '@/Components/Layout/PageLoadingSkeleton.vue';

createInertiaApp({
    title: (title) => `${title} - EDEN-UMG`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const Root = {
            setup() {
                const loading = ref(false);
                const progress = ref(null);
                let delayTimer = null;
                let finishTimer = null;

                const shouldShowSkeleton = (event) => {
                    const visitUrl = event.detail?.visit?.url?.toString?.() ?? '';
                    const path = new URL(visitUrl, window.location.origin).pathname;

                    return !path.startsWith('/admin/content-')
                        && !path.startsWith('/admin/uploads')
                        && path !== '/me/settings';
                };

                const clearTimers = () => {
                    clearTimeout(delayTimer);
                    clearTimeout(finishTimer);
                };

                const removeStart = router.on('start', (event) => {
                    clearTimers();
                    progress.value = null;

                    if (!shouldShowSkeleton(event)) {
                        loading.value = false;
                        return;
                    }

                    delayTimer = setTimeout(() => {
                        loading.value = true;
                    }, 120);
                });

                const removeProgress = router.on('progress', (event) => {
                    progress.value = event.detail?.progress?.percentage ?? null;
                });

                const removeFinish = router.on('finish', () => {
                    clearTimeout(delayTimer);
                    progress.value = 100;
                    finishTimer = setTimeout(() => {
                        loading.value = false;
                        progress.value = null;
                    }, 180);
                });

                onBeforeUnmount(() => {
                    clearTimers();
                    removeStart();
                    removeProgress();
                    removeFinish();
                });

                return () => [
                    h(App, props),
                    h(PageLoadingSkeleton, {
                        show: loading.value,
                        progress: progress.value,
                    }),
                ];
            },
        };

        createApp(Root)
            .use(plugin)
            .mount(el);

        // Retire le skeleton de premier chargement (rendu côté serveur) en fondu
        const skeleton = document.getElementById('app-skeleton');
        if (skeleton) {
            requestAnimationFrame(() => {
                skeleton.classList.add('is-gone');
                setTimeout(() => skeleton.remove(), 400);
            });
        }
    },
});

// PWA : enregistrement du service worker (production uniquement)
if (import.meta.env.PROD && 'serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
