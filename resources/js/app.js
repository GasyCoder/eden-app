import '../css/app.css';

// Police auto-hébergée (Inter variable, accents FR via latin-ext inclus)
import '@fontsource-variable/inter/index.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, onBeforeUnmount, ref } from 'vue';
import PageLoadingSkeleton from '@/Components/Layout/PageLoadingSkeleton.vue';
import { setupAnalytics } from '@/lib/analytics';
import { setupPwa } from '@/lib/pwa';

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
                    if (event.detail?.visit?.showProgress === false) {
                        return false;
                    }

                    const visitUrl = event.detail?.visit?.url?.toString?.() ?? '';
                    const path = new URL(visitUrl, window.location.origin).pathname;

                    return !path.startsWith('/admin/content-')
                        && !path.startsWith('/admin/uploads')
                        && !path.startsWith('/me/login')
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

        setupAnalytics();

        // Retire le skeleton de premier chargement (rendu côté serveur) en fondu
        const skeleton = document.getElementById('app-skeleton');
        if (skeleton) {
            requestAnimationFrame(() => {
                skeleton.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => skeleton.remove(), 400);
            });
        }
    },
});

router.on('navigate', (event) => {
    const locale = event.detail?.page?.props?.locale;

    if (['fr', 'en'].includes(locale)) {
        document.documentElement.lang = locale;
    }
});

// PWA : service worker, mise à jour proposée, navigation hors ligne
setupPwa();
