<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash ?? {});
const message = ref('');
const type = ref('success');
const visible = ref(false);
let timer = null;

watch(
    () => [flash.value.success, flash.value.error],
    ([success, error]) => {
        const nextMessage = success ?? error;

        if (!nextMessage) {
            return;
        }

        message.value = nextMessage;
        type.value = success ? 'success' : 'error';
        visible.value = true;

        clearTimeout(timer);
        timer = setTimeout(() => {
            visible.value = false;
        }, 2600);
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    clearTimeout(timer);
});
</script>

<template>
    <div v-if="visible" class="eden-admin-flash" :class="`eden-admin-flash-${type}`" role="status" aria-live="polite">
        {{ message }}
    </div>
</template>
