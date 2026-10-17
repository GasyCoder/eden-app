import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted } from 'vue';

/**
 * Prévient la perte de modifications non enregistrées d'un formulaire Inertia
 * (fermeture de l'onglet ou navigation interne).
 */
export function useUnsavedGuard(form) {
    const message = 'Des modifications ne sont pas enregistrées. Quitter quand même ?';

    const onBeforeUnload = (event) => {
        if (form.isDirty && !form.processing) {
            event.preventDefault();
            event.returnValue = '';
        }
    };

    let removeListener = null;

    onMounted(() => {
        window.addEventListener('beforeunload', onBeforeUnload);
        removeListener = router.on('before', (event) => {
            const method = event.detail.visit.method;

            if (method === 'get' && form.isDirty && !form.processing && !window.confirm(message)) {
                return false;
            }

            return undefined;
        });
    });

    onBeforeUnmount(() => {
        window.removeEventListener('beforeunload', onBeforeUnload);
        removeListener?.();
    });
}
