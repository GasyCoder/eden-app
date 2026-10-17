<script setup>
import { Link2 } from '@lucide/vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import { cn } from '@/lib/utils';

// Pastille d'édition de l'URL d'un bouton (visible en mode édition uniquement).
const props = defineProps({
    value: { type: String, default: '' },
    fallback: { type: String, default: '#' },
    contentKey: { type: String, required: true },
    canEdit: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <span v-if="canEdit" :class="cn('inline-flex w-fit max-w-full items-center gap-1 rounded-full border border-dashed border-eden-400 bg-background py-0.5 pr-1 pl-2.5 text-xs font-medium text-muted-foreground [&>span>span:first-child]:inline-block [&>span>span:first-child]:max-w-52 [&>span>span:first-child]:truncate [&>span>span:first-child]:align-middle', props.class)">
        <Link2 class="size-3 shrink-0" aria-hidden="true" />
        <EditableText :value="value" :fallback="fallback" :content-key="contentKey" :can-edit="canEdit" type="link" />
    </span>
</template>
