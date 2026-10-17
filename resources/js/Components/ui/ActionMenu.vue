<script setup>
import { Link } from '@inertiajs/vue3';
import { Ellipsis } from '@lucide/vue';
import { DropdownMenuContent, DropdownMenuItem, DropdownMenuPortal, DropdownMenuRoot, DropdownMenuSeparator, DropdownMenuTrigger } from 'reka-ui';

// Menu d'actions contextuelles d'une ligne (« … »).
defineProps({
    items: { type: Array, required: true },
    label: { type: String, default: 'Actions' },
});

const itemClass = (danger) => [
    'flex w-full cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none select-none data-[highlighted]:bg-accent [&_svg]:size-4',
    danger ? 'text-destructive [&_svg]:text-destructive' : '[&_svg]:text-muted-foreground',
];
</script>

<template>
    <DropdownMenuRoot :modal="false">
        <DropdownMenuTrigger class="inline-grid size-8 place-items-center rounded-md text-muted-foreground transition-colors outline-none hover:bg-accent hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 data-[state=open]:bg-accent" :aria-label="label">
            <Ellipsis class="size-4" aria-hidden="true" />
        </DropdownMenuTrigger>
        <DropdownMenuPortal>
            <DropdownMenuContent align="end" :side-offset="4" class="z-[120] min-w-44 rounded-md border bg-popover p-1 text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95">
                <template v-for="(item, index) in items" :key="item.label">
                    <DropdownMenuSeparator v-if="item.separator && index > 0" class="-mx-1 my-1 h-px bg-border" />
                    <DropdownMenuItem v-if="item.href" as-child>
                        <a v-if="item.external" :href="item.href" target="_blank" rel="noopener noreferrer" :class="itemClass(item.danger)">
                            <component :is="item.icon" v-if="item.icon" aria-hidden="true" />{{ item.label }}
                        </a>
                        <Link v-else :href="item.href" :class="itemClass(item.danger)">
                            <component :is="item.icon" v-if="item.icon" aria-hidden="true" />{{ item.label }}
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem v-else :class="itemClass(item.danger)" @select="item.onSelect?.()">
                        <component :is="item.icon" v-if="item.icon" aria-hidden="true" />{{ item.label }}
                    </DropdownMenuItem>
                </template>
            </DropdownMenuContent>
        </DropdownMenuPortal>
    </DropdownMenuRoot>
</template>
