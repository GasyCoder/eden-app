import { router } from '@inertiajs/vue3';

export function currentContentLocale() {
    const htmlLocale = document.documentElement.lang;

    if (['fr', 'en'].includes(htmlLocale)) {
        return htmlLocale;
    }

    return window.location.pathname.split('/').filter(Boolean)[0] === 'en' ? 'en' : 'fr';
}

export function withContentLocale(data = {}) {
    return {
        locale: currentContentLocale(),
        ...data,
    };
}

export function adminPatch(url, data = {}, options = {}) {
    router.post(url, {
        _method: 'PATCH',
        ...withContentLocale(data),
    }, {
        forceFormData: true,
        ...options,
    });
}

export function adminDelete(url, data = {}, options = {}) {
    router.post(url, {
        _method: 'DELETE',
        ...withContentLocale(data),
    }, {
        forceFormData: true,
        ...options,
    });
}
