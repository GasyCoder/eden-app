<script setup>
import { Eye, EyeOff } from '@lucide/vue';
import { computed, ref, useId, watch } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass } from '@/lib/variants';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    id: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});
const model = defineModel({ type: String, default: '' });
const visible = ref(false);
const autoId = useId();
const controlId = computed(() => props.id ?? autoId);
const toggleLabel = computed(() => visible.value ? 'Masquer le mot de passe' : 'Afficher le mot de passe');

watch(model, (value) => {
    if (!value) visible.value = false;
});
</script>

<template>
    <div class="relative min-w-0 w-full">
        <input
            v-bind="$attrs"
            :id="controlId"
            v-model="model"
            :type="visible ? 'text' : 'password'"
            :disabled="disabled"
            :class="cn(inputClass, 'pr-12', props.class)"
        >
        <button
            type="button"
            :class="cn(buttonVariants({ variant: 'ghost', size: 'icon-sm' }), 'absolute top-1/2 right-0.5 size-9 -translate-y-1/2 text-muted-foreground')"
            :disabled="disabled"
            :aria-label="toggleLabel"
            :title="toggleLabel"
            :aria-controls="controlId"
            :aria-pressed="visible"
            @click="visible = !visible"
        >
            <EyeOff v-if="visible" aria-hidden="true" />
            <Eye v-else aria-hidden="true" />
        </button>
    </div>
</template>
