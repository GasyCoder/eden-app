<script setup>
import { TriangleAlert } from '@lucide/vue';
import { AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogOverlay, AlertDialogPortal, AlertDialogRoot, AlertDialogTitle } from 'reka-ui';
import { buttonVariants } from '@/lib/variants';

// Confirmation d'une action irréversible (AlertDialog : pas de fermeture au clic extérieur).
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Supprimer' },
    cancelLabel: { type: String, default: 'Annuler' },
    destructive: { type: Boolean, default: true },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm']);
const open = defineModel('open', { type: Boolean, default: false });

const confirm = () => {
    emit('confirm');
    open.value = false;
};
</script>

<template>
    <AlertDialogRoot v-model:open="open">
        <AlertDialogPortal>
            <AlertDialogOverlay class="fixed inset-0 z-[100] bg-black/50 data-[state=open]:animate-in data-[state=open]:fade-in-0" />
            <AlertDialogContent class="fixed top-1/2 left-1/2 z-[110] grid w-[calc(100vw-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 gap-4 rounded-xl border bg-popover p-6 shadow-lg outline-none data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95">
                <div class="flex gap-4">
                    <span v-if="destructive" class="grid size-10 shrink-0 place-items-center rounded-full bg-laterite-50 text-laterite-600 dark:bg-laterite-950 dark:text-laterite-300" aria-hidden="true"><TriangleAlert class="size-5" /></span>
                    <div class="grid gap-1.5">
                        <AlertDialogTitle class="text-lg font-semibold">{{ title }}</AlertDialogTitle>
                        <AlertDialogDescription v-if="description" class="text-sm text-muted-foreground">{{ description }}</AlertDialogDescription>
                        <slot />
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <AlertDialogCancel :class="buttonVariants({ variant: 'outline' })" :disabled="loading">{{ cancelLabel }}</AlertDialogCancel>
                    <button type="button" :class="buttonVariants({ variant: destructive ? 'destructive' : 'default' })" :disabled="loading" @click="confirm">
                        <span v-if="loading" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                        {{ confirmLabel }}
                    </button>
                </div>
            </AlertDialogContent>
        </AlertDialogPortal>
    </AlertDialogRoot>
</template>
