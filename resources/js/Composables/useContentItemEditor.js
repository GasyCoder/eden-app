import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { adminDelete, adminPatch, withContentLocale } from '@/Composables/useAdminRequest';

export function useContentItemEditor(collectionKey, blankForm, toForm, toPayload) {
    const editingId = ref(null);
    const creating = ref(false);
    const saving = ref(false);
    const form = ref(blankForm());

    const trackSaving = (options) => ({
        ...options,
        onStart: () => {
            saving.value = true;
        },
        onFinish: () => {
            saving.value = false;
        },
    });

    function startCreate() {
        form.value = blankForm();
        editingId.value = null;
        creating.value = true;
    }

    function editItem(item) {
        form.value = toForm(item);
        creating.value = false;
        editingId.value = item.id;
    }

    function cancelEdit() {
        editingId.value = null;
        creating.value = false;
        form.value = blankForm();
    }

    function createItem() {
        router.post(`/admin/content-items/${collectionKey}`, withContentLocale(toPayload(form.value)), trackSaving({
            preserveScroll: true,
            onSuccess: cancelEdit,
        }));
    }

    function saveItem(item) {
        adminPatch(`/admin/content-items/${item.id}`, toPayload(form.value), trackSaving({
            preserveScroll: true,
            onSuccess: cancelEdit,
        }));
    }

    function deleteItem(item) {
        if (!window.confirm('Supprimer cet élément ? Cette action est enregistrée dans l’historique.')) {
            return;
        }

        adminDelete(`/admin/content-items/${item.id}`, {}, trackSaving({
            preserveScroll: true,
            onSuccess: cancelEdit,
        }));
    }

    function reorderItems(items, index, direction) {
        const editableItems = items.filter((item) => item.id);
        const targetIndex = index + direction;

        if (targetIndex < 0 || targetIndex >= editableItems.length) {
            return;
        }

        const reordered = [...editableItems];
        const [item] = reordered.splice(index, 1);
        reordered.splice(targetIndex, 0, item);

        adminPatch(`/admin/content-items/${collectionKey}/reorder`, {
            items: reordered.map((item, position) => ({ id: item.id, position })),
        }, {
            preserveScroll: true,
        });
    }

    return {
        editingId,
        creating,
        saving,
        form,
        startCreate,
        editItem,
        cancelEdit,
        createItem,
        saveItem,
        deleteItem,
        reorderItems,
    };
}
