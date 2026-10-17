import { router } from '@inertiajs/vue3';

/**
 * Google Analytics 4, configuré dans Paramètres du site. Le script n'est chargé
 * qu'après acceptation des cookies ; chaque navigation Inertia compte une page vue.
 */
const consentGiven = () => {
    try {
        return JSON.parse(window.localStorage.getItem('eden_cookie_consent') ?? '{}').value === 'accepted';
    } catch {
        return false;
    }
};

let loaded = false;

const load = (id) => {
    if (loaded || !id || !consentGiven()) {
        return;
    }

    loaded = true;
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`;
    document.head.appendChild(script);

    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
        window.dataLayer.push(arguments);
    };
    window.gtag('js', new Date());
    window.gtag('config', id, { anonymize_ip: true });
};

export function setupAnalytics() {
    const id = document.querySelector('meta[name="eden-analytics"]')?.content;

    if (!id) {
        return;
    }

    load(id);
    window.addEventListener('eden:cookie-consent', () => load(id));
    router.on('navigate', (event) => {
        if (loaded && !event.detail.page.url.startsWith('/admin')) {
            window.gtag('event', 'page_view', { page_path: event.detail.page.url });
        }
    });
}
