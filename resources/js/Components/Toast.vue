<script setup>
import { usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, X } from '@lucide/vue';
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
    <div class="pointer-events-none fixed inset-x-3 bottom-[calc(var(--tabbar-h)+0.75rem)] sm:bottom-[calc(var(--tabbar-h)+1.25rem)] z-[150] grid gap-2 sm:inset-x-auto sm:right-5 sm:w-96" role="status" aria-live="polite" aria-atomic="false">
        <transition-group
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex items-start gap-3 rounded-lg border bg-popover py-3 pr-2 pl-4 text-sm shadow-lg"
                :role="toast.type === 'error' ? 'alert' : null"
            >
                <CircleCheck v-if="toast.type === 'success'" class="mt-0.5 size-[18px] shrink-0 text-eden-600 dark:text-eden-400" aria-hidden="true" />
                <CircleAlert v-else class="mt-0.5 size-[18px] shrink-0 text-destructive" aria-hidden="true" />
                <span class="flex-1 pt-px font-medium">{{ toast.message }}</span>
                <button type="button" class="-my-1 inline-grid size-7 place-items-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="Fermer la notification" @click="dismiss(toast.id)">
                    <X class="size-4" aria-hidden="true" />
                </button>
            </div>
        </transition-group>
    </div>
</template>
