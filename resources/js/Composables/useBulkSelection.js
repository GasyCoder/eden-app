import { computed, reactive, ref, watch } from 'vue';

/**
 * Sélection multiple des lignes d'un tableau d'administration.
 * La sélection est réduite aux lignes encore affichées après un filtre ou un changement de page.
 */
export function useBulkSelection(rows) {
    const selected = ref([]);
    const ids = computed(() => rows().map((row) => row.id));

    watch(ids, (visible) => {
        selected.value = selected.value.filter((id) => visible.includes(id));
    });

    const isSelected = (id) => selected.value.includes(id);
    const allSelected = computed(() => ids.value.length > 0 && ids.value.every(isSelected));

    return reactive({
        selected,
        count: computed(() => selected.value.length),
        allSelected,
        someSelected: computed(() => selected.value.length > 0 && !allSelected.value),
        isSelected,
        toggle: (id) => {
            selected.value = isSelected(id) ? selected.value.filter((value) => value !== id) : [...selected.value, id];
        },
        toggleAll: () => {
            selected.value = allSelected.value ? [] : [...ids.value];
        },
        clear: () => {
            selected.value = [];
        },
    });
}
