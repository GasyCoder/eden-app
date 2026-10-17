<script setup>
import { X } from '@lucide/vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { buttonVariants } from '@/lib/variants';

// URL publique (ne pas laisser Vite la transformer en import de module)
const iconUrl = '/icons/icon-192.png';

const DISMISS_KEY = 'eden_pwa_dismissed';
const deferredPrompt = ref(null);
const visible = ref(false); // bannière « Installer » (Android / Chrome / Edge)
const iosHint = ref(false); // instructions iOS (pas d'événement natif)
const iosPending = ref(false); // iOS détecté, en attente du choix cookies

const isStandalone = () =>
    window.matchMedia?.('(display-mode: standalone)')?.matches ||
    window.navigator.standalone === true;

const dismissedRecently = () => {
    try {
        const ts = Number(window.localStorage.getItem(DISMISS_KEY) || 0);
        return ts > 0 && Date.now() - ts < 1000 * 60 * 60 * 24 * 14; // 14 jours
    } catch {
        return false;
    }
};

// On n'affiche l'invite qu'une fois le choix cookies fait (évite le chevauchement des bannières)
const cookieDecided = () => {
    try {
        return Boolean(window.localStorage.getItem('eden_cookie_consent'));
    } catch {
        return true;
    }
};

const canShow = () => !isStandalone() && !dismissedRecently() && cookieDecided();

const remember = () => {
    try {
        window.localStorage.setItem(DISMISS_KEY, String(Date.now()));
    } catch {
        // ignore
    }
};

const onBeforeInstall = (event) => {
    event.preventDefault();
    deferredPrompt.value = event;

    if (canShow()) {
        visible.value = true;
    }
};

// Affiche l'invite (ou les instructions iOS) une fois le consentement cookies donné
const onCookieConsent = () => {
    if (deferredPrompt.value && canShow()) {
        visible.value = true;
    }

    if (iosPending.value && !isStandalone() && !dismissedRecently()) {
        iosHint.value = true;
    }
};

const onInstalled = () => {
    visible.value = false;
    iosHint.value = false;
    remember();
};

const install = async () => {
    if (!deferredPrompt.value) {
        return;
    }

    deferredPrompt.value.prompt();
    const choice = await deferredPrompt.value.userChoice;
    deferredPrompt.value = null;
    visible.value = false;

    if (choice?.outcome !== 'accepted') {
        remember();
    }
};

const dismiss = () => {
    visible.value = false;
    iosHint.value = false;
    remember();
};

onMounted(() => {
    if (isStandalone()) {
        return;
    }

    window.addEventListener('beforeinstallprompt', onBeforeInstall);
    window.addEventListener('appinstalled', onInstalled);
    window.addEventListener('eden:cookie-consent', onCookieConsent);

    // iOS Safari : pas de beforeinstallprompt → on affiche des instructions
    const ua = window.navigator.userAgent || '';
    const isIos = /iphone|ipad|ipod/i.test(ua);
    const isSafari = /safari/i.test(ua) && !/crios|fxios|edgios/i.test(ua);

    if (isIos && isSafari && !dismissedRecently()) {
        iosPending.value = true;
        window.setTimeout(() => {
            if (canShow()) {
                iosHint.value = true;
            }
        }, 2500);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('beforeinstallprompt', onBeforeInstall);
    window.removeEventListener('appinstalled', onInstalled);
    window.removeEventListener('eden:cookie-consent', onCookieConsent);
});
</script>

<template>
    <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-3 opacity-0"
        leave-active-class="transition duration-200 ease-in"
        leave-to-class="translate-y-3 opacity-0"
    >
        <section v-if="visible || iosHint" class="fixed inset-x-3 bottom-[calc(var(--tabbar-h)+0.75rem)] sm:bottom-[calc(var(--tabbar-h)+1.25rem)] z-40 grid gap-4 rounded-xl border bg-popover p-5 shadow-lg sm:inset-x-auto sm:left-5 sm:w-[26rem]" role="dialog" aria-labelledby="install-title">
            <div class="flex items-start gap-3">
                <img :src="iconUrl" alt="" width="44" height="44" class="size-11 shrink-0 rounded-lg">
                <div class="grid flex-1 gap-1">
                    <p id="install-title" class="text-sm font-semibold">Installer l’application EDEN</p>
                    <p v-if="!iosHint" class="text-sm text-muted-foreground">Ajoutez le site à votre appareil pour y accéder en un geste, même hors connexion.</p>
                    <p v-else class="text-sm text-muted-foreground">Dans Safari, touchez <strong class="font-medium text-foreground">Partager</strong> puis <strong class="font-medium text-foreground">Sur l’écran d’accueil</strong>.</p>
                </div>
                <button type="button" class="-mt-1 -mr-2 inline-grid size-8 place-items-center rounded-md text-muted-foreground hover:bg-accent" aria-label="Fermer" @click="dismiss"><X class="size-4" aria-hidden="true" /></button>
            </div>
            <div v-if="visible" class="flex gap-2 sm:justify-end">
                <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" class="flex-1 sm:flex-none" @click="dismiss">Plus tard</button>
                <button type="button" :class="buttonVariants({ size: 'sm' })" class="flex-1 sm:flex-none" @click="install">Installer</button>
            </div>
        </section>
    </transition>
</template>
