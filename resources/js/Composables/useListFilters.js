import { router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

/**
 * Filtres de liste synchronisés avec l'URL (recherche différée, sélecteurs immédiats).
 */
export function useListFilters(url, initial = {}) {
    const filters = reactive({ ...initial });
    const loading = ref(false);
    let timer = null;

    const reload = () => {
        const query = Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== null && value !== undefined && value !== ''));

        router.get(url, query, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => { loading.value = true; },
            onFinish: () => { loading.value = false; },
        });
    };

    Object.keys(initial).forEach((key) => {
        watch(() => filters[key], () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(reload, key === 'q' ? 300 : 0);
        });
    });

    const reset = () => {
        Object.keys(filters).forEach((key) => {
            filters[key] = key === 'q' ? '' : null;
        });
    };

    const isFiltered = () => Object.values(filters).some((value) => value !== null && value !== '');

    return { filters, loading, reset, isFiltered };
}
