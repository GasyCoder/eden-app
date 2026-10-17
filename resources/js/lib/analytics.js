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
const isPrivate = (url) => /^\/(admin|me|logout|newsletter)(\/|$)/.test(new URL(url, window.location.origin).pathname);

const trackPage = (id, url) => {
    const hidden = isPrivate(url);
    window[`ga-disable-${id}`] = hidden;
    if (loaded && !hidden) {
        window.gtag('event', 'page_view', { page_path: url });
    }
};

const load = (id) => {
    if (loaded || !id || !consentGiven() || isPrivate(window.location.href)) {
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
    window.gtag('config', id, { anonymize_ip: true, send_page_view: false });
    trackPage(id, window.location.pathname + window.location.search);
};

export function setupAnalytics() {
    const id = document.querySelector('meta[name="eden-analytics"]')?.content;

    if (!id) {
        return;
    }

    load(id);
    window.addEventListener('eden:cookie-consent', () => load(id));
    router.on('start', (event) => {
        window[`ga-disable-${id}`] = isPrivate(event.detail.visit.url);
    });
    router.on('navigate', (event) => {
        trackPage(id, event.detail.page.url);
    });
}
