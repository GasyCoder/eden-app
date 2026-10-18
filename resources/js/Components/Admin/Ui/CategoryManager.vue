<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { Check, Pencil, Plus, Trash2, X } from '@lucide/vue';
import { ref } from 'vue';
import Sheet from '@/Components/ui/Sheet.vue';
import { buttonVariants, inputClass } from '@/lib/variants';

// Gestion des catégories d'actualités dans un panneau latéral.
defineProps({
    categories: { type: Array, default: () => [] },
});

const open = defineModel('open', { type: Boolean, default: false });
const createForm = useForm({ name: { fr: '', en: '' } });
const editForm = useForm({ name: { fr: '', en: '' } });
const editingId = ref(null);

const create = () => createForm.post('/admin/post-categories', {
    preserveScroll: true,
    onSuccess: () => createForm.reset(),
});

const startEdit = (category) => {
    editingId.value = category.id;
    editForm.name = { fr: category.name_translations.fr, en: category.name_translations.en };
    editForm.clearErrors();
};

const save = (category) => editForm.transform((data) => ({ ...data, _method: 'PATCH' })).post(`/admin/post-categories/${category.id}`, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null; },
});

const remove = (category) => {
    const warning = category.posts_count
        ? `Supprimer « ${category.name} » ? Ses ${category.posts_count} actualité(s) resteront publiées, sans catégorie.`
        : `Supprimer la catégorie « ${category.name} » ?`;

    if (window.confirm(warning)) {
        router.post(`/admin/post-categories/${category.id}`, { _method: 'DELETE' }, { preserveScroll: true });
    }
};
</script>

<template>
    <Sheet v-model:open="open" title="Catégories" description="Elles servent de filtres sur la page Actualités.">
        <form class="mb-4 grid gap-3 border-b pb-5" @submit.prevent="create">
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="grid gap-1.5">
                    <label class="text-xs font-medium" for="category-fr">Nom (FR)</label>
                    <input id="category-fr" v-model="createForm.name.fr" :class="inputClass" class="h-9" required :aria-invalid="createForm.errors['name.fr'] ? 'true' : undefined">
                </div>
                <div class="grid gap-1.5">
                    <label class="text-xs font-medium" for="category-en">Nom (EN)</label>
                    <input id="category-en" v-model="createForm.name.en" :class="inputClass" class="h-9" placeholder="Facultatif">
                </div>
            </div>
            <p v-if="createForm.errors['name.fr']" class="text-xs font-medium text-destructive" role="alert">{{ createForm.errors['name.fr'] }}</p>
            <button type="submit" :class="buttonVariants({ variant: 'outline', size: 'sm' })" class="justify-self-start" :disabled="createForm.processing"><Plus aria-hidden="true" />Ajouter</button>
        </form>

        <ul v-if="categories.length" class="divide-y">
            <li v-for="category in categories" :key="category.id" class="flex items-center gap-2 py-3">
                <form v-if="editingId === category.id" class="flex w-full items-center gap-2" @submit.prevent="save(category)">
                    <input v-model="editForm.name.fr" :class="inputClass" class="h-8 flex-1" aria-label="Nom en français" required>
                    <input v-model="editForm.name.en" :class="inputClass" class="h-8 flex-1" aria-label="Nom en anglais" placeholder="EN">
                    <button type="submit" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" aria-label="Enregistrer" :disabled="editForm.processing"><Check aria-hidden="true" /></button>
                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" aria-label="Annuler" @click="editingId = null"><X aria-hidden="true" /></button>
                </form>
                <template v-else>
                    <span class="grid min-w-0 flex-1">
                        <strong class="text-sm font-medium">{{ category.name }}</strong>
                        <small class="text-xs text-muted-foreground">{{ category.posts_count }} actualité{{ category.posts_count > 1 ? 's' : '' }}<template v-if="category.name_translations.en"> · {{ category.name_translations.en }}</template></small>
                    </span>
                    <button type="button" :class="buttonVariants({ variant: 'ghost', size: 'icon-sm' })" :aria-label="`Renommer ${category.name}`" @click="startEdit(category)"><Pencil aria-hidden="true" /></button>
                    <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'icon-sm' })" :aria-label="`Supprimer ${category.name}`" @click="remove(category)"><Trash2 aria-hidden="true" /></button>
                </template>
            </li>
        </ul>
        <p v-else class="text-sm text-muted-foreground">Aucune catégorie pour le moment.</p>
    </Sheet>
</template>
