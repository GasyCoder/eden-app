<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Trash2, UserPlus, Users } from '@lucide/vue';
import { ref } from 'vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Field from '@/Components/ui/Field.vue';
import { cn } from '@/lib/utils';
import { buttonVariants, inputClass } from '@/lib/variants';

// Comptes éditeurs (superadmin) : création, modification, suppression.
defineProps({
    editors: { type: Array, default: () => [] },
});

const blank = () => ({ name: '', email: '', password: '', password_confirmation: '' });
const creating = ref(false);
const editingId = ref(null);
const pendingDelete = ref(null);
const deleting = ref(false);
const createForm = useForm(blank());
const updateForm = useForm(blank());

const submitCreate = () => createForm.post('/me/users', {
    preserveScroll: true,
    onSuccess: () => { createForm.reset(); creating.value = false; },
});

const startEdit = (editor) => {
    updateForm.clearErrors();
    Object.assign(updateForm, { name: editor.name ?? '', email: editor.email ?? '', password: '', password_confirmation: '' });
    editingId.value = editor.id;
};

const submitUpdate = (editor) => updateForm.patch(`/me/users/${editor.id}`, {
    preserveScroll: true,
    onSuccess: () => { editingId.value = null; updateForm.reset(); },
});

const confirmDelete = () => router.delete(`/me/users/${pendingDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; pendingDelete.value = null; },
});
</script>

<template>
    <section id="utilisateurs" class="grid scroll-mt-20 gap-5 rounded-xl border bg-card p-5 text-card-foreground">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="grid gap-1">
                <h2 class="text-base font-semibold">Comptes éditeurs</h2>
                <p class="text-sm text-muted-foreground">Les éditeurs gèrent les contenus (actualités, événements, projets, newsletter…), sans accès à ces paramètres ni aux comptes.</p>
            </div>
            <button v-if="!creating" type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="creating = true"><UserPlus aria-hidden="true" />Ajouter un éditeur</button>
        </div>

        <form v-if="creating" class="grid gap-4 rounded-lg bg-muted p-4" @submit.prevent="submitCreate">
            <h3 class="text-sm font-semibold">Nouvel éditeur</h3>
            <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                <Field v-slot="{ id, invalid }" label="Nom" :error="createForm.errors.name"><input :id="id" v-model="createForm.name" autocomplete="off" required :class="inputClass" :aria-invalid="invalid"></Field>
                <Field v-slot="{ id, invalid }" label="Adresse email" :error="createForm.errors.email"><input :id="id" v-model="createForm.email" type="email" autocomplete="off" required :class="inputClass" :aria-invalid="invalid"></Field>
                <Field v-slot="{ id, invalid }" label="Mot de passe provisoire" :error="createForm.errors.password"><input :id="id" v-model="createForm.password" type="password" autocomplete="new-password" required :class="inputClass" :aria-invalid="invalid"></Field>
                <Field v-slot="{ id }" label="Confirmation"><input :id="id" v-model="createForm.password_confirmation" type="password" autocomplete="new-password" required :class="inputClass"></Field>
            </div>
            <p class="text-xs text-muted-foreground">Transmettez ce mot de passe à la personne : elle pourra le changer depuis son profil.</p>
            <div class="flex flex-wrap justify-end gap-2">
                <button type="button" :class="buttonVariants({ variant: 'outline' })" :disabled="createForm.processing" @click="creating = false; createForm.reset(); createForm.clearErrors()">Annuler</button>
                <button type="submit" :class="buttonVariants()" :disabled="createForm.processing">{{ createForm.processing ? 'Création…' : 'Créer le compte' }}</button>
            </div>
        </form>

        <ul v-if="editors.length" class="divide-y border-y">
            <li v-for="editor in editors" :key="editor.id" class="py-3">
                <form v-if="editingId === editor.id" class="grid gap-4 rounded-lg bg-muted p-4" @submit.prevent="submitUpdate(editor)">
                    <h3 class="text-sm font-semibold">Modifier {{ editor.name }}</h3>
                    <div class="grid items-start gap-4 sm:grid-cols-2 sm:gap-x-5">
                        <Field v-slot="{ id, invalid }" label="Nom" :error="updateForm.errors.name"><input :id="id" v-model="updateForm.name" required :class="inputClass" :aria-invalid="invalid"></Field>
                        <Field v-slot="{ id, invalid }" label="Adresse email" :error="updateForm.errors.email"><input :id="id" v-model="updateForm.email" type="email" required :class="inputClass" :aria-invalid="invalid"></Field>
                        <Field v-slot="{ id, invalid }" label="Nouveau mot de passe" optional :error="updateForm.errors.password"><input :id="id" v-model="updateForm.password" type="password" autocomplete="new-password" :class="inputClass" :aria-invalid="invalid"></Field>
                        <Field v-slot="{ id }" label="Confirmation"><input :id="id" v-model="updateForm.password_confirmation" type="password" autocomplete="new-password" :class="inputClass"></Field>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button type="button" :class="buttonVariants({ variant: 'outline' })" :disabled="updateForm.processing" @click="editingId = null">Annuler</button>
                        <button type="submit" :class="buttonVariants()" :disabled="updateForm.processing">{{ updateForm.processing ? 'Enregistrement…' : 'Enregistrer' }}</button>
                    </div>
                </form>
                <div v-else class="flex items-center gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-secondary text-sm font-semibold">{{ (editor.name ?? editor.email ?? '?').charAt(0).toUpperCase() }}</span>
                    <span class="grid min-w-0 flex-1">
                        <strong class="text-sm font-medium">{{ editor.name }}</strong>
                        <span class="text-sm break-all text-muted-foreground">{{ editor.email }}</span>
                    </span>
                    <small class="text-xs whitespace-nowrap text-muted-foreground max-sm:hidden">Créé le {{ editor.created_at }}</small>
                    <button type="button" :class="cn(buttonVariants({ variant: 'ghost', size: 'icon-sm' }), 'text-muted-foreground')" :aria-label="`Modifier ${editor.name}`" @click="startEdit(editor)"><Pencil aria-hidden="true" /></button>
                    <button type="button" :class="buttonVariants({ variant: 'destructive-ghost', size: 'icon-sm' })" :aria-label="`Supprimer ${editor.name}`" @click="pendingDelete = editor"><Trash2 aria-hidden="true" /></button>
                </div>
            </li>
        </ul>
        <EmptyState v-else-if="!creating" :icon="Users" title="Aucun éditeur pour le moment" description="Ajoutez un compte pour permettre à un collègue de mettre à jour le site." />

        <ConfirmDialog
            :open="Boolean(pendingDelete)"
            title="Supprimer ce compte éditeur ?"
            :description="pendingDelete ? `${pendingDelete.name} (${pendingDelete.email}) ne pourra plus se connecter ni modifier les contenus du site.` : ''"
            confirm-label="Supprimer le compte"
            :loading="deleting"
            @update:open="(value) => { if (!value) pendingDelete = null; }"
            @confirm="confirmDelete"
        />
    </section>
</template>
