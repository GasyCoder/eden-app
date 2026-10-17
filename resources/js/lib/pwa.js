import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

/**
 * Application installable : enregistrement du service worker, détection d'une nouvelle
 * version (proposée à l'utilisateur) et navigation hors ligne.
 */
export const updateReady = ref(false);
export const online = ref(typeof navigator === 'undefined' ? true : navigator.onLine);

let waitingWorker = null;

const watchInstalling = (worker) => {
    worker?.addEventListener('statechange', () => {
        // Une version est déjà active : la nouvelle attend l'accord de l'utilisateur
        if (worker.state === 'installed' && navigator.serviceWorker.controller) {
            waitingWorker = worker;
            updateReady.value = true;
        }
    });
};

export function applyUpdate() {
    if (!waitingWorker) {
        window.location.reload();
        return;
    }

    navigator.serviceWorker.addEventListener('controllerchange', () => window.location.reload(), { once: true });
    waitingWorker.postMessage('SKIP_WAITING');
}

export function setupPwa() {
    window.addEventListener('online', () => { online.value = true; });
    window.addEventListener('offline', () => { online.value = false; });

    // Hors ligne, une visite Inertia échoue : on recharge la page pour que le service
    // worker serve la version déjà consultée (ou la page hors ligne).
    let lastVisit = null;
    router.on('start', (event) => {
        lastVisit = event.detail?.visit;
    });
    router.on('networkError', (event) => {
        if (!navigator.onLine && lastVisit?.method === 'get') {
            event.preventDefault();
            window.location.href = lastVisit.url?.toString?.() ?? window.location.href;
        }
    });

    if (!import.meta.env.PROD || !('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then((registration) => {
            if (registration.waiting && navigator.serviceWorker.controller) {
                waitingWorker = registration.waiting;
                updateReady.value = true;
            }

            registration.addEventListener('updatefound', () => watchInstalling(registration.installing));

            // Vérifie les mises à jour quand l'application revient au premier plan
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    registration.update().catch(() => {});
                }
            });
        }).catch(() => {});
    });
}
