<script setup>
import { X } from '@lucide/vue';
import { DialogClose, DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle } from 'reka-ui';
import { cn } from '@/lib/utils';

// Dialog accessible (focus piégé, Échap, retour du focus) : équivalent shadcn <Dialog>.
const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    size: { type: String, default: 'md' },
    closeLabel: { type: String, default: 'Fermer' },
    hideClose: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});

const open = defineModel('open', { type: Boolean, default: false });
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-[100] bg-black/50 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=closed]:animate-out data-[state=closed]:fade-out-0" />
            <DialogContent
                v-bind="description ? {} : { 'aria-describedby': undefined }"
                :class="cn(
                    'fixed top-1/2 left-1/2 z-[110] flex max-h-[calc(100dvh-2rem)] w-[calc(100vw-2rem)] -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-xl border bg-popover text-popover-foreground shadow-lg outline-none data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95',
                    size === 'lg' ? 'max-w-2xl' : 'max-w-lg',
                    props.class,
                )"
            >
                <slot name="media" />
                <div class="flex shrink-0 items-start justify-between gap-3 px-5 pt-5 sm:px-6 sm:pt-6">
                    <div class="flex min-w-0 items-start gap-3">
                        <slot name="icon" />
                        <div class="grid min-w-0 gap-1.5 py-1.5">
                            <DialogTitle class="text-lg leading-snug font-semibold break-words">{{ title }}</DialogTitle>
                            <DialogDescription v-if="description" class="text-sm text-muted-foreground">{{ description }}</DialogDescription>
                        </div>
                    </div>
                    <DialogClose v-if="!hideClose" class="-mt-1 -mr-2 inline-grid size-11 shrink-0 place-items-center rounded-md text-muted-foreground transition-colors outline-none hover:bg-accent hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50" :aria-label="closeLabel">
                        <X class="size-5" aria-hidden="true" />
                    </DialogClose>
                </div>
                <div class="min-h-0 overflow-y-auto overscroll-contain px-5 py-4 sm:px-6"><slot /></div>
                <footer v-if="$slots.footer" class="flex shrink-0 flex-col-reverse gap-2 border-t bg-muted/50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                    <slot name="footer" />
                </footer>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
