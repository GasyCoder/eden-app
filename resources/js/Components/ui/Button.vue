<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

// Équivalent du <Button> shadcn-vue : rendu en <button>, <a> ou <Link> Inertia selon href.
const props = defineProps({
    variant: { type: String, default: 'default' },
    size: { type: String, default: 'default' },
    href: { type: String, default: null },
    external: { type: Boolean, default: false },
    type: { type: String, default: 'button' },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});
const emit = defineEmits(['click']);

const tag = computed(() => {
    if (!props.href) {
        return 'button';
    }

    return props.external || /^(https?:|mailto:|tel:|#|data:)/.test(props.href) ? 'a' : Link;
});
</script>

<template>
    <component
        :is="tag"
        :class="cn(buttonVariants({ variant, size }), props.class)"
        :href="href || undefined"
        :type="href ? undefined : type"
        :disabled="!href && (disabled || loading) ? true : undefined"
        :aria-busy="loading ? 'true' : undefined"
        :target="external ? '_blank' : undefined"
        :rel="external ? 'noopener noreferrer' : undefined"
        @click="emit('click', $event)"
    >
        <span v-if="loading" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
        <slot />
    </component>
</template>
