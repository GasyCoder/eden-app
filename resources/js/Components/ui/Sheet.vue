<script setup>
import { X } from '@lucide/vue';
import { DialogClose, DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle } from 'reka-ui';
import { cn } from '@/lib/utils';

// Panneau latéral (shadcn <Sheet>) construit sur Dialog.
const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    side: { type: String, default: 'right' },
    wide: { type: Boolean, default: false },
    closeLabel: { type: String, default: 'Fermer' },
});

const open = defineModel('open', { type: Boolean, default: false });
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-[100] bg-black/50 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=closed]:animate-out data-[state=closed]:fade-out-0" />
            <DialogContent
                :class="cn(
                    'fixed inset-y-0 z-[110] flex w-[88vw] flex-col bg-background shadow-lg outline-none data-[state=open]:animate-in data-[state=open]:duration-300 data-[state=closed]:animate-out',
                    props.wide ? 'max-w-lg' : 'max-w-sm',
                    props.side === 'left'
                        ? 'left-0 border-r data-[state=open]:slide-in-from-left data-[state=closed]:slide-out-to-left'
                        : 'right-0 border-l data-[state=open]:slide-in-from-right data-[state=closed]:slide-out-to-right',
                )"
            >
                <header class="flex items-start justify-between gap-4 border-b px-5 py-4">
                    <div class="grid gap-1">
                        <DialogTitle class="text-base font-semibold">{{ title }}</DialogTitle>
                        <DialogDescription v-if="description" class="text-sm text-muted-foreground">{{ description }}</DialogDescription>
                    </div>
                    <DialogClose class="-mr-2 inline-grid size-8 place-items-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none" :aria-label="closeLabel">
                        <X class="size-4" aria-hidden="true" />
                    </DialogClose>
                </header>
                <div class="flex-1 overflow-y-auto p-5"><slot /></div>
                <footer v-if="$slots.footer" class="border-t px-5 py-4"><slot name="footer" /></footer>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
