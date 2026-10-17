<script setup>
import { SwitchRoot, SwitchThumb } from 'reka-ui';
import { useId } from 'vue';

defineProps({
    label: { type: String, default: '' },
    description: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
});

const model = defineModel({ type: Boolean, default: false });
const id = useId();
</script>

<template>
    <div class="flex items-start gap-3">
        <SwitchRoot
            :id="id"
            v-model="model"
            :disabled="disabled"
            :aria-describedby="description ? `${id}-desc` : undefined"
            class="peer mt-0.5 inline-flex h-5 w-9 shrink-0 items-center rounded-full border border-transparent bg-input shadow-xs transition-colors outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:opacity-50 data-[state=checked]:bg-primary"
        >
            <SwitchThumb class="pointer-events-none block size-4 translate-x-0.5 rounded-full bg-white shadow-sm ring-0 transition-transform data-[state=checked]:translate-x-[18px]" />
        </SwitchRoot>
        <div v-if="label" class="grid gap-0.5">
            <label :for="id" class="cursor-pointer text-sm font-medium">{{ label }}</label>
            <p v-if="description" :id="`${id}-desc`" class="text-xs text-muted-foreground">{{ description }}</p>
        </div>
    </div>
</template>
