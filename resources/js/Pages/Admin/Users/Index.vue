<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { Check, KeyRound, Pencil, Plus, Trash2, UserRound, UsersRound } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import ConfirmDialog from '@/Components/ui/ConfirmDialog.vue';
import Dialog from '@/Components/ui/Dialog.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import Select from '@/Components/ui/Select.vue';
import { useToasts } from '@/Composables/useToasts';
import { inputClass } from '@/lib/variants';

const props = defineProps({ users: { type: Object, required: true } });
const inviteForm = useForm({ name: '', email: '', role: 'editor' });
const editForm = useForm({ name: '', email: '', role: 'editor' });
const editingUser = ref(null);
const editOpen = ref(false);
const pendingDelete = ref(null);
const deleteDialogOpen = computed({
    get: () => Boolean(pendingDelete.value),
    set: (open) => { if (!open) pendingDelete.value = null; },
});
const deleting = ref(false);
const sendingResetTo = ref(null);
const { push } = useToasts();
const roleOptions = [{ value: 'admin', label: 'Admin' }, { value: 'editor', label: 'Éditeur' }];
const showRequestError = (errors) => {
    const message = Object.values(errors ?? {}).flat().find((value) => typeof value === 'string');
    push(message || 'L’action n’a pas pu être effectuée. Réessayez.', 'error');
};

const invite = () => inviteForm.post('/admin/users', { preserveScroll: true, onSuccess: () => inviteForm.reset() });
const openEdit = (user) => {
    editingUser.value = user;
    editForm.defaults({ name: user.name, email: user.email, role: user.role });
    editForm.reset();
    editOpen.value = true;
};
const saveEdit = () => editForm.patch(`/admin/users/${editingUser.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { editOpen.value = false; editingUser.value = null; },
    onError: showRequestError,
});
const changeRole = (user, role) => router.patch(`/admin/users/${user.id}`, {
    name: user.name, email: user.email, role,
}, { preserveScroll: true, onError: showRequestError });
const sendReset = (user) => router.post(`/admin/users/${user.id}/password-reset`, {}, {
    preserveScroll: true,
    onStart: () => { sendingResetTo.value = user.id; },
    onFinish: () => { sendingResetTo.value = null; },
});
const removeUser = () => router.delete(`/admin/users/${pendingDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => { deleting.value = true; },
    onFinish: () => { deleting.value = false; pendingDelete.value = null; },
    onError: showRequestError,
});
</script>

<template>
    <AdminLayout title="Utilisateurs">
        <AdminPageHeader title="Utilisateurs" description="Gérez les accès à l’administration et accompagnez les membres de l’équipe.">
            <template #actions>
                <span class="inline-flex h-9 items-center gap-2 rounded-md border bg-background px-3 text-sm text-muted-foreground"><UsersRound class="size-4" aria-hidden="true" />{{ users.meta.total }} compte(s)</span>
            </template>
        </AdminPageHeader>

        <section class="mb-6 rounded-xl border bg-card p-5 shadow-xs sm:p-6" aria-labelledby="invite-title">
            <div class="mb-5 flex items-start gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-primary/10 text-primary"><UserRound class="size-4" aria-hidden="true" /></span>
                <div><h2 id="invite-title" class="font-semibold tracking-tight">Inviter un utilisateur</h2><p class="mt-1 text-sm text-muted-foreground">Un lien sécurisé sera envoyé par e-mail pour confirmer l’adresse et créer son mot de passe. Il expire après 48 heures.</p></div>
            </div>
            <form class="grid items-start gap-4 md:grid-cols-2 xl:grid-cols-[minmax(12rem,1fr)_minmax(15rem,1fr)_12rem_auto]" @submit.prevent="invite">
                <label class="grid gap-2 text-sm font-medium">Nom complet
                    <input v-model="inviteForm.name" :class="inputClass" autocomplete="name" placeholder="Nom de la personne" required :aria-invalid="Boolean(inviteForm.errors.name)">
                    <span v-if="inviteForm.errors.name" class="text-xs font-medium text-destructive">{{ inviteForm.errors.name }}</span>
                </label>
                <label class="grid gap-2 text-sm font-medium">Adresse e-mail
                    <input v-model="inviteForm.email" :class="inputClass" type="email" autocomplete="email" placeholder="nom@exemple.mg" required :aria-invalid="Boolean(inviteForm.errors.email)">
                    <span v-if="inviteForm.errors.email" class="text-xs font-medium text-destructive">{{ inviteForm.errors.email }}</span>
                </label>
                <label class="grid gap-2 text-sm font-medium">Rôle
                    <Select v-model="inviteForm.role" :options="roleOptions" aria-label="Rôle du nouvel utilisateur" />
                    <span v-if="inviteForm.errors.role" class="text-xs font-medium text-destructive">{{ inviteForm.errors.role }}</span>
                </label>
                <div class="flex items-end md:col-span-2 xl:col-span-1 xl:self-end"><Button type="submit" :loading="inviteForm.processing" class="w-full"><Plus aria-hidden="true" />Envoyer l’invitation</Button></div>
                <p v-if="inviteForm.hasErrors" class="text-sm text-destructive md:col-span-2 xl:col-span-4" role="alert">Certains champs doivent être corrigés.</p>
            </form>
        </section>

        <section class="overflow-hidden rounded-xl border bg-card shadow-xs" aria-label="Liste des utilisateurs">
            <div v-if="users.data.length" class="divide-y">
                <article v-for="user in users.data" :key="user.id" class="flex flex-col gap-4 p-4 transition-colors hover:bg-muted/30 sm:flex-row sm:items-center sm:px-5">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full border bg-muted text-sm font-semibold text-muted-foreground">{{ (user.name || user.email).charAt(0).toUpperCase() }}</span>
                        <div class="min-w-0"><p class="truncate text-sm font-medium">{{ user.name }}</p><p class="mt-0.5 break-all text-sm text-muted-foreground">{{ user.email }}</p></div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <span v-if="user.invitation_pending" class="inline-flex h-7 items-center rounded-full border border-amber-600/25 bg-amber-500/10 px-2.5 text-xs font-medium text-amber-800 dark:text-amber-300">Invitation en attente</span>
                        <span v-else-if="user.email_verified" class="inline-flex h-7 items-center gap-1 rounded-full border border-emerald-600/25 bg-emerald-500/10 px-2.5 text-xs font-medium text-emerald-800 dark:text-emerald-300"><Check class="size-3.5" aria-hidden="true" />Adresse confirmée</span>
                        <Select :model-value="user.role" :options="roleOptions" :aria-label="`Rôle de ${user.name}`" size="sm" class="w-28" @update:model-value="changeRole(user, $event)" />
                        <div class="flex items-center gap-1 border-l pl-2">
                            <Button variant="ghost" size="icon-sm" :aria-label="`Modifier ${user.name}`" title="Modifier les informations" @click="openEdit(user)"><Pencil aria-hidden="true" /></Button>
                            <Button variant="ghost" size="icon-sm" :loading="sendingResetTo === user.id" :aria-label="`Envoyer un lien de réinitialisation à ${user.email}`" title="Envoyer un lien de réinitialisation" @click="sendReset(user)"><KeyRound aria-hidden="true" /></Button>
                            <Button v-if="user.id !== $page.props.auth.user.id" variant="destructive-ghost" size="icon-sm" :aria-label="`Supprimer ${user.name}`" title="Supprimer le compte" @click="pendingDelete = user"><Trash2 aria-hidden="true" /></Button>
                        </div>
                    </div>
                </article>
            </div>
            <div v-else class="grid justify-items-center gap-3 px-5 py-12 text-center">
                <span class="grid size-12 place-items-center rounded-full bg-muted text-muted-foreground"><UsersRound class="size-5" aria-hidden="true" /></span>
                <div><p class="font-medium">Aucun utilisateur pour le moment</p><p class="mt-1 text-sm text-muted-foreground">Les comptes invités apparaîtront ici.</p></div>
            </div>
        </section>

        <Pagination v-if="users.meta.last_page > 1" :meta="users.meta" :links="users.links" preserve-scroll :labels="{ previous: 'Précédent', next: 'Suivant', info: `${users.meta.from} à ${users.meta.to} sur ${users.meta.total}` }" />

        <Dialog v-model:open="editOpen" title="Modifier l’utilisateur" :description="editingUser ? `Mettez à jour les informations de ${editingUser.name}.` : ''">
            <form class="grid gap-4" @submit.prevent="saveEdit">
                <label class="grid gap-2 text-sm font-medium">Nom complet
                    <input v-model="editForm.name" :class="inputClass" autocomplete="name" required>
                    <span v-if="editForm.errors.name" class="text-xs text-destructive">{{ editForm.errors.name }}</span>
                </label>
                <label class="grid gap-2 text-sm font-medium">Adresse e-mail
                    <input v-model="editForm.email" :class="inputClass" type="email" autocomplete="email" required>
                    <span v-if="editForm.errors.email" class="text-xs text-destructive">{{ editForm.errors.email }}</span>
                    <span class="text-xs text-muted-foreground">Si l’adresse change, un lien de confirmation et de création du mot de passe lui sera envoyé.</span>
                </label>
                <label class="grid gap-2 text-sm font-medium">Rôle
                    <Select v-model="editForm.role" :options="roleOptions" aria-label="Rôle de l’utilisateur" />
                    <span v-if="editForm.errors.role" class="text-xs text-destructive">{{ editForm.errors.role }}</span>
                </label>
            </form>
            <template #footer>
                <Button variant="outline" :disabled="editForm.processing" @click="editOpen = false">Annuler</Button>
                <Button :loading="editForm.processing" @click="saveEdit">Enregistrer les modifications</Button>
            </template>
        </Dialog>

        <ConfirmDialog v-model:open="deleteDialogOpen" title="Supprimer cet utilisateur ?" :description="pendingDelete ? `Le compte ${pendingDelete.email} sera supprimé.` : ''" :loading="deleting" @confirm="removeUser" />
    </AdminLayout>
</template>
