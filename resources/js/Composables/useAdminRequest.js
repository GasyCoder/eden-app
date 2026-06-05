import { router } from '@inertiajs/vue3';

export function adminPatch(url, data = {}, options = {}) {
    router.post(url, {
        _method: 'PATCH',
        ...data,
    }, {
        forceFormData: true,
        ...options,
    });
}

export function adminDelete(url, data = {}, options = {}) {
    router.post(url, {
        _method: 'DELETE',
        ...data,
    }, {
        forceFormData: true,
        ...options,
    });
}
