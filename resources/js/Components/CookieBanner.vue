<script setup>
import { onMounted, ref } from 'vue';
import { buttonVariants } from '@/lib/variants';

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
    <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-3 opacity-0"
        leave-active-class="transition duration-200 ease-in"
        leave-to-class="translate-y-3 opacity-0"
    >
        <section v-if="visible" class="fixed inset-x-3 bottom-[calc(var(--tabbar-h)+0.75rem)] sm:bottom-[calc(var(--tabbar-h)+1.25rem)] z-40 grid gap-4 rounded-xl border bg-popover p-5 shadow-lg sm:inset-x-auto sm:left-5 sm:w-[26rem]" role="dialog" aria-labelledby="cookie-title">
            <div class="grid gap-1">
                <p id="cookie-title" class="text-sm font-semibold">Cookies</p>
                <p class="text-sm leading-relaxed text-muted-foreground">
                    Ce site n’utilise que des cookies <strong class="font-medium text-foreground">nécessaires à son fonctionnement</strong>
                    (session sécurisée, préférence d’affichage). Aucun suivi publicitaire.
                </p>
            </div>
            <div class="flex gap-2 sm:justify-end">
                <button type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" class="flex-1 sm:flex-none" @click="choose('declined')">Refuser</button>
                <button type="button" :class="buttonVariants({ size: 'sm' })" class="flex-1 sm:flex-none" @click="choose('accepted')">Accepter</button>
            </div>
        </section>
    </transition>
</template>
