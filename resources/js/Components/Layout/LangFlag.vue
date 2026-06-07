<script setup>
defineProps({
    code: {
        type: String,
        default: 'fr',
    },
});
</script>

<template>
    <svg v-if="code === 'en'" class="language-flag" viewBox="0 0 36 24" aria-hidden="true">
        <rect width="36" height="24" fill="#012169" rx="2" />
        <path d="M0 0 36 24M36 0 0 24" stroke="#fff" stroke-width="5" />
        <path d="M0 0 36 24M36 0 0 24" stroke="#C8102E" stroke-width="3" />
        <path d="M18 0v24M0 12h36" stroke="#fff" stroke-width="8" />
        <path d="M18 0v24M0 12h36" stroke="#C8102E" stroke-width="5" />
    </svg>
    <svg v-else class="language-flag" viewBox="0 0 36 24" aria-hidden="true">
        <rect width="12" height="24" fill="#0055A4" rx="2" />
        <rect x="12" width="12" height="24" fill="#fff" />
        <rect x="24" width="12" height="24" fill="#EF4135" rx="2" />
    </svg>
</template>
