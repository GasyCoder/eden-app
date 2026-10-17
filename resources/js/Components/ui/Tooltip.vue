<script setup>
import { TooltipContent, TooltipPortal, TooltipProvider, TooltipRoot, TooltipTrigger } from 'reka-ui';

defineProps({
    content: { type: String, required: true },
    side: { type: String, default: 'top' },
});
</script>

<template>
    <TooltipProvider :delay-duration="300">
        <TooltipRoot>
            <TooltipTrigger as-child><slot /></TooltipTrigger>
            <TooltipPortal>
                <TooltipContent :side="side" :side-offset="6" class="z-[130] max-w-64 rounded-md bg-foreground px-2.5 py-1.5 text-xs font-medium text-background shadow-md data-[state=delayed-open]:animate-in data-[state=delayed-open]:fade-in-0">
                    {{ content }}
                </TooltipContent>
            </TooltipPortal>
        </TooltipRoot>
    </TooltipProvider>
</template>
