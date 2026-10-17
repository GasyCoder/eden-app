<script setup>
import { Check, ChevronDown } from '@lucide/vue';
import { SelectContent, SelectIcon, SelectItem, SelectItemIndicator, SelectItemText, SelectPortal, SelectRoot, SelectTrigger, SelectValue, SelectViewport } from 'reka-ui';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

// Select accessible (Reka UI). La valeur vide est représentée par « __empty ».
const EMPTY = '__empty';

const props = defineProps({
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Choisir…' },
    id: { type: String, default: undefined },
    size: { type: String, default: 'default' },
    invalid: { type: [String, Boolean], default: undefined },
    describedBy: { type: String, default: undefined },
    disabled: { type: Boolean, default: false },
    ariaLabel: { type: String, default: undefined },
    class: { type: [String, Array, Object], default: '' },
});

const model = defineModel({ type: [String, Number, null], default: null });
const toInner = (value) => (value === null || value === undefined || value === '' ? EMPTY : String(value));

const inner = computed({
    get: () => toInner(model.value),
    set: (value) => {
        const option = props.options.find((item) => toInner(item.value) === value);
        model.value = option ? option.value : null;
    },
});
</script>

<template>
    <SelectRoot v-model="inner" :disabled="disabled">
        <SelectTrigger
            :id="id"
            :aria-invalid="invalid"
            :aria-describedby="describedBy"
            :aria-label="ariaLabel"
            :class="cn(
                'flex w-full items-center justify-between gap-2 rounded-md border border-input bg-background px-3 text-left text-sm shadow-xs outline-none data-[placeholder]:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:opacity-50 aria-invalid:border-destructive dark:bg-input/20 [&>span]:truncate',
                size === 'sm' ? 'h-8' : 'h-10',
                props.class,
            )"
        >
            <SelectValue :placeholder="placeholder" />
            <SelectIcon as-child><ChevronDown class="size-4 opacity-60" aria-hidden="true" /></SelectIcon>
        </SelectTrigger>
        <SelectPortal>
            <SelectContent
                position="popper"
                :side-offset="4"
                class="z-[120] max-h-(--reka-select-content-available-height) min-w-(--reka-select-trigger-width) overflow-y-auto rounded-md border bg-popover p-1 text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95"
            >
                <SelectViewport>
                    <SelectItem
                        v-for="option in options"
                        :key="toInner(option.value)"
                        :value="toInner(option.value)"
                        class="relative flex min-h-8 cursor-default items-center rounded-sm py-1.5 pr-2 pl-8 text-sm outline-none select-none data-[disabled]:opacity-50 data-[highlighted]:bg-accent"
                    >
                        <SelectItemIndicator class="absolute left-2 inline-flex"><Check class="size-4" aria-hidden="true" /></SelectItemIndicator>
                        <SelectItemText>{{ option.label }}</SelectItemText>
                    </SelectItem>
                </SelectViewport>
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>
