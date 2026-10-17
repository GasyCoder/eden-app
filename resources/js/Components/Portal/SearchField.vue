<script setup>
import { Search, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { cn } from '@/lib/utils';
import { inputClass } from '@/lib/variants';

// Champ de recherche : validation par Entrée, bouton d'effacement.
const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Rechercher…' },
    label: { type: String, default: 'Rechercher' },
    class: { type: [String, Array, Object], default: '' },
});

const emit = defineEmits(['update:modelValue', 'search']);
const value = ref(props.modelValue);

watch(() => props.modelValue, (next) => {
    value.value = next;
});

const submit = () => {
    emit('update:modelValue', value.value);
    emit('search', value.value.trim());
};

const clear = () => {
    value.value = '';
    submit();
};
</script>

<template>
    <form :class="cn('relative w-full sm:w-80', props.class)" role="search" @submit.prevent="submit">
        <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
        <input v-model="value" :class="inputClass" class="pr-9 pl-9 [&::-webkit-search-cancel-button]:hidden" type="search" :placeholder="placeholder" :aria-label="label" enterkeyhint="search">
        <button v-if="value" type="button" class="absolute top-1/2 right-1.5 inline-grid size-7 -translate-y-1/2 place-items-center rounded-md text-muted-foreground hover:bg-accent" aria-label="Effacer la recherche" @click="clear">
            <X class="size-4" aria-hidden="true" />
        </button>
    </form>
</template>
