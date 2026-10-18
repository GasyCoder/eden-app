<script setup>
import { ref } from 'vue';
import Button from '@/Components/ui/Button.vue';
import Dialog from '@/Components/ui/Dialog.vue';
import { useToasts } from '@/Composables/useToasts';
import { inputClass } from '@/lib/variants';

const open = defineModel('open', { type: Boolean, default: false });
const emit = defineEmits(['created']);
const frenchName = ref('');
const englishName = ref('');
const errors = ref({});
const errorMessage = ref('');
const busy = ref(false);
const { push } = useToasts();

const create = async () => {
    if (busy.value) return;
    busy.value = true;
    errors.value = {};
    errorMessage.value = '';

    try {
        const xsrf = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
        const csrfHeaders = xsrf
            ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrf[1]) }
            : { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' };
        const response = await fetch('/admin/post-categories', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...csrfHeaders },
            body: JSON.stringify({ name: { fr: frenchName.value, en: englishName.value } }),
        });
        const data = await response.json().catch(() => ({}));

        if (!response.ok || !data.category?.id) {
            errors.value = data.errors ?? {};
            errorMessage.value = response.status === 419
                ? 'Votre session a expiré. Rechargez la page avant de réessayer.'
                : data.message || 'La catégorie n’a pas pu être créée. Réessayez.';
            return;
        }

        emit('created', data.category);
        frenchName.value = '';
        englishName.value = '';
        open.value = false;
        push('Catégorie créée et sélectionnée.');
    } catch {
        errorMessage.value = 'Impossible de joindre le serveur. Votre actualité est conservée dans le formulaire.';
    } finally {
        busy.value = false;
    }
};
</script>

<template>
    <Dialog v-model:open="open" title="Nouvelle catégorie" description="Ajoutez une catégorie sans quitter votre actualité. Elle sera automatiquement sélectionnée.">
        <form id="inline-category-form" class="grid gap-4" @submit.prevent="create">
            <div class="grid gap-2">
                <label for="inline-category-fr" class="text-sm font-medium">Nom en français</label>
                <input id="inline-category-fr" v-model="frenchName" :class="inputClass" required maxlength="80" autocomplete="off" :disabled="busy" :aria-invalid="Boolean(errors['name.fr'])" aria-describedby="inline-category-error">
                <p v-if="errors['name.fr']" id="inline-category-error" class="text-xs text-destructive" role="alert">{{ errors['name.fr'][0] }}</p>
            </div>
            <div class="grid gap-2">
                <label for="inline-category-en" class="text-sm font-medium">Nom en anglais <span class="font-normal text-muted-foreground">(facultatif)</span></label>
                <input id="inline-category-en" v-model="englishName" :class="inputClass" maxlength="80" autocomplete="off" :disabled="busy">
                <p v-if="errors['name.en']" class="text-xs text-destructive" role="alert">{{ errors['name.en'][0] }}</p>
            </div>
            <p v-if="errorMessage" class="text-sm text-destructive" role="alert">{{ errorMessage }}</p>
        </form>
        <template #footer>
            <Button variant="outline" :disabled="busy" @click="open = false">Annuler</Button>
            <Button type="submit" form="inline-category-form" :loading="busy">Créer et sélectionner</Button>
        </template>
    </Dialog>
</template>
