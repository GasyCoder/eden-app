<script setup>
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useToasts } from '@/Composables/useToasts';

const page = usePage();
const { toasts, push, dismiss } = useToasts();

// Affiche un toast à chaque message flash (succès / erreur) renvoyé par le serveur.
// page.props.flash est un nouvel objet à chaque réponse Inertia → le watcher se déclenche
// même si le message est identique au précédent.
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) {
            return;
        }

        if (flash.success) {
            push(flash.success, 'success');
        }

        if (flash.error) {
            push(flash.error, 'error');
        }
    },
    { immediate: true, deep: true },
);
</script>

<template>
    <div class="eden-toasts" aria-live="polite" aria-atomic="true">
        <transition-group name="eden-toast">
            <button
                v-for="toast in toasts"
                :key="toast.id"
                type="button"
                class="eden-toast"
                :class="`eden-toast-${toast.type}`"
                @click="dismiss(toast.id)"
            >
                <span class="eden-toast-ic" aria-hidden="true">
                    <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5" /></svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="M12 8v5" /><path d="M12 16.5h.01" /></svg>
                </span>
                <span class="eden-toast-msg">{{ toast.message }}</span>
            </button>
        </transition-group>
    </div>
</template>
