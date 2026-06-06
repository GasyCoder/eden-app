<script setup>
import { onMounted, ref } from 'vue';

const KEY = 'eden_cookie_consent';
const visible = ref(false);

const hasDecided = () => {
    try {
        return Boolean(window.localStorage.getItem(KEY));
    } catch {
        return false;
    }
};

const choose = (value) => {
    try {
        window.localStorage.setItem(KEY, JSON.stringify({ value, at: Date.now() }));
    } catch {
        // ignore
    }

    try {
        document.cookie = `eden_cookie_consent=${value}; max-age=${60 * 60 * 24 * 365}; path=/; samesite=lax`;
    } catch {
        // ignore
    }

    visible.value = false;

    // Permet à l'invite d'installation PWA de s'afficher ensuite
    window.dispatchEvent(new CustomEvent('eden:cookie-consent', { detail: value }));
};

onMounted(() => {
    if (!hasDecided()) {
        window.setTimeout(() => {
            visible.value = true;
        }, 700);
    }
});
</script>

<template>
    <transition name="eden-cookie">
        <div v-if="visible" class="eden-cookie" role="dialog" aria-label="Gestion des cookies">
            <div class="eden-cookie-body">
                <strong>Cookies</strong>
                <span>
                    Ce site utilise uniquement des cookies <b>nécessaires à son fonctionnement</b>
                    (session sécurisée, préférence de thème). Aucun cookie de traçage publicitaire.
                </span>
            </div>
            <div class="eden-cookie-actions">
                <button type="button" class="eden-cookie-refuse" @click="choose('declined')">Refuser</button>
                <button type="button" class="eden-cookie-accept" @click="choose('accepted')">Accepter</button>
            </div>
        </div>
    </transition>
</template>
