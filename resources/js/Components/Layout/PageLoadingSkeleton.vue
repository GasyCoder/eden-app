<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    progress: {
        type: Number,
        default: null,
    },
});
</script>

<template>
    <Transition name="eden-page-loading">
        <div v-if="show" class="eden-page-loading" role="status" aria-live="polite" aria-label="Chargement">
            <div class="eden-page-loading-bar" :style="{ width: `${Math.max(progress ?? 38, 12)}%` }"></div>
            <div class="eden-page-loading-shell">
                <div class="eden-page-loading-header">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="eden-page-loading-hero">
                    <div>
                        <span class="eden-page-loading-line short"></span>
                        <span class="eden-page-loading-title"></span>
                        <span class="eden-page-loading-title narrow"></span>
                        <span class="eden-page-loading-line"></span>
                        <span class="eden-page-loading-line medium"></span>
                    </div>
                    <div class="eden-page-loading-media"></div>
                </div>
                <div class="eden-page-loading-grid">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </Transition>
</template>
