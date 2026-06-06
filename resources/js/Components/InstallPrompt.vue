<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

// URL publique (ne pas laisser Vite la transformer en import de module)
const iconUrl = '/icons/icon-192.png';

const DISMISS_KEY = 'eden_pwa_dismissed';
const deferredPrompt = ref(null);
const visible = ref(false); // bannière « Installer » (Android / Chrome / Edge)
const iosHint = ref(false); // instructions iOS (pas d'événement natif)

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

    if (!isStandalone() && !dismissedRecently()) {
        visible.value = true;
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

    // iOS Safari : pas de beforeinstallprompt → on affiche des instructions
    const ua = window.navigator.userAgent || '';
    const isIos = /iphone|ipad|ipod/i.test(ua);
    const isSafari = /safari/i.test(ua) && !/crios|fxios|edgios/i.test(ua);

    if (isIos && isSafari && !dismissedRecently()) {
        window.setTimeout(() => {
            iosHint.value = true;
        }, 2500);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('beforeinstallprompt', onBeforeInstall);
    window.removeEventListener('appinstalled', onInstalled);
});
</script>

<template>
    <transition name="eden-install">
        <div v-if="visible || iosHint" class="eden-install" role="dialog" aria-label="Installer l'application">
            <img :src="iconUrl" alt="" class="eden-install-ic" width="46" height="46">
            <div class="eden-install-body">
                <strong>Installer l'application EDEN</strong>
                <span v-if="!iosHint">Ajoutez le site à votre appareil pour un accès rapide, même hors connexion.</span>
                <span v-else>Sur Safari : touchez <b>Partager</b> puis <b>« Sur l'écran d'accueil »</b>.</span>
            </div>
            <div class="eden-install-actions">
                <button v-if="visible" type="button" class="eden-install-btn" @click="install">Installer</button>
                <button type="button" class="eden-install-close" aria-label="Fermer" @click="dismiss">✕</button>
            </div>
        </div>
    </transition>
</template>
