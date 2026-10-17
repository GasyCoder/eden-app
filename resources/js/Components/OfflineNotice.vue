<script setup>
import { RefreshCw, WifiOff } from '@lucide/vue';
import { useT } from '@/lib/i18n';
import { applyUpdate, online, updateReady } from '@/lib/pwa';
import { buttonVariants } from '@/lib/variants';

// État de l'application installable : hors ligne, nouvelle version disponible.
const { t } = useT();
</script>

<template>
    <Transition enter-active-class="transition duration-300" enter-from-class="-translate-y-2 opacity-0" leave-active-class="transition duration-200" leave-to-class="-translate-y-2 opacity-0">
        <div v-if="!online" class="fixed inset-x-0 top-[env(safe-area-inset-top)] z-[90] flex justify-center px-3 pt-2" role="status">
            <p class="flex items-center gap-2 rounded-full bg-foreground px-4 py-2 text-xs font-medium text-background shadow-lg">
                <WifiOff class="size-3.5" aria-hidden="true" />{{ t('offline') }}
            </p>
        </div>
    </Transition>
    <Transition enter-active-class="transition duration-300" enter-from-class="translate-y-2 opacity-0" leave-active-class="transition duration-200" leave-to-class="translate-y-2 opacity-0">
        <div v-if="updateReady" class="fixed inset-x-3 bottom-[calc(var(--tabbar-h)+0.75rem)] z-[95] mx-auto flex max-w-md items-center gap-3 rounded-xl border bg-popover p-3 pl-4 text-sm shadow-lg" role="status">
            <RefreshCw class="size-4 shrink-0 text-eden-600" aria-hidden="true" />
            <span class="flex-1">{{ t('updateAvailable') }}</span>
            <button type="button" :class="buttonVariants({ size: 'sm' })" @click="applyUpdate">{{ t('update') }}</button>
        </div>
    </Transition>
</template>
