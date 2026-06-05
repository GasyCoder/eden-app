import { reactive } from 'vue';

// File d'attente partagée des toasts (singleton)
const state = reactive({ items: [] });
let counter = 0;

export function useToasts() {
    function dismiss(id) {
        const index = state.items.findIndex((toast) => toast.id === id);

        if (index !== -1) {
            state.items.splice(index, 1);
        }
    }

    function push(message, type = 'success', timeout = 3400) {
        if (!message) {
            return null;
        }

        const id = ++counter;
        state.items.push({ id, message, type });

        if (timeout > 0) {
            setTimeout(() => dismiss(id), timeout);
        }

        return id;
    }

    return {
        toasts: state.items,
        push,
        dismiss,
    };
}
