import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

export function useContentItemEditor(collectionKey, blankForm, toForm, toPayload) {
    const editingId = ref(null);
    const creating = ref(false);
    const form = ref(blankForm());

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
        router.post(`/admin/content-items/${collectionKey}`, toPayload(form.value), {
            preserveScroll: true,
            onSuccess: cancelEdit,
        });
    }

    function saveItem(item) {
        router.patch(`/admin/content-items/${item.id}`, toPayload(form.value), {
            preserveScroll: true,
            onSuccess: cancelEdit,
        });
    }

    function deleteItem(item) {
        if (!window.confirm('Supprimer cet élément ?')) {
            return;
        }

        router.delete(`/admin/content-items/${item.id}`, {
            preserveScroll: true,
        });
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

        router.patch(`/admin/content-items/${collectionKey}/reorder`, {
            items: reordered.map((item, position) => ({ id: item.id, position })),
        }, {
            preserveScroll: true,
        });
    }

    return {
        editingId,
        creating,
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
