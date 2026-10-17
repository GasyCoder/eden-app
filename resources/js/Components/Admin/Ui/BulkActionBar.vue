<script setup>
import { router } from '@inertiajs/vue3';
import { X } from '@lucide/vue';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

/**
 * Barre d'actions groupées, affichée dès qu'une ligne est cochée.
 * actions : [{ action, label, icon, danger?, confirm?: { title, description }, href?: (ids) => url }]
 */
const props = defineProps({
    selection: { type: Object, required: true },
    url: { type: String, required: true },
    actions: { type: Array, default: () => [] },
    noun: { type: Array, default: () => ['élément', 'éléments'] },
});

const pending = ref(null);
const busy = ref(false);
const label = computed(() => `${props.selection.count} ${props.selection.count > 1 ? props.noun[1] : props.noun[0]} sélectionné${props.selection.count > 1 ? 's' : ''}`);

const run = (item) => {
    if (item.href) {
        window.location.href = item.href(props.selection.selected);
        return;
    }

    if (item.confirm && pending.value !== item) {
        pending.value = item;
        return;
    }

    router.post(props.url, { action: item.action, ids: props.selection.selected }, {
        preserveScroll: true,
        onStart: () => { busy.value = true; },
        onSuccess: () => props.selection.clear(),
        onFinish: () => { busy.value = false; pending.value = null; },
    });
};
</script>

<template>
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="translate-y-4 opacity-0" leave-active-class="transition duration-150 ease-in" leave-to-class="translate-y-4 opacity-0">
        <div
            v-if="selection.count"
            class="fixed inset-x-3 bottom-4 z-50 mx-auto flex max-w-fit flex-wrap items-center gap-2 rounded-xl border bg-popover p-2 pl-4 text-popover-foreground shadow-lg lg:left-64"
            role="toolbar"
            :aria-label="`Actions groupées : ${label}`"
        >
            <span class="mr-1 text-sm font-medium tabular-nums" aria-live="polite">{{ label }}</span>
            <button
                v-for="item in actions"
                :key="item.action"
                type="button"
                :class="cn(buttonVariants({ variant: item.danger ? 'destructive-ghost' : 'outline', size: 'sm' }))"
                :disabled="busy"
                @click="run(item)"
            >
                <component :is="item.icon" v-if="item.icon" aria-hidden="true" />{{ item.label }}
            </button>
            <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" aria-label="Annuler la sélection" title="Annuler la sélection" @click="selection.clear()"><X aria-hidden="true" /></button>
        </div>
    </Transition>

    <ConfirmDialog
        :open="Boolean(pending)"
        :title="pending?.confirm?.title ?? ''"
        :description="pending ? (typeof pending.confirm.description === 'function' ? pending.confirm.description(selection.count) : pending.confirm.description) : ''"
        :confirm-label="pending?.confirm?.label ?? 'Supprimer'"
        :destructive="pending?.danger ?? true"
        :loading="busy"
        @update:open="(value) => { if (!value) pending = null; }"
        @confirm="run(pending)"
    />
</template>
