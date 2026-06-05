<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';

const props = defineProps({
    contentKey: {
        type: String,
        required: true,
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});

const input = ref(null);
const uploading = ref(false);
const error = ref('');

const choose = () => {
    input.value?.click();
};

const upload = async (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    uploading.value = true;
    error.value = '';

    try {
        const formData = new FormData();
        formData.append('image', file);

        const response = await fetch('/admin/uploads/images', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                Accept: 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok || !data.path) {
            error.value = data.message ?? 'Image refusée.';
            return;
        }

        router.patch(`/admin/content-blocks/${props.contentKey}`, {
            value: data.path,
            type: 'image',
        }, {
            preserveScroll: true,
            onError: () => {
                error.value = 'Image non enregistrée.';
            },
        });
    } catch {
        error.value = 'Téléversement impossible.';
    } finally {
        uploading.value = false;
        event.target.value = '';
    }
};
</script>

<template>
    <span v-if="canEdit" class="eden-edit-image">
        <AdminEditButton :title="uploading ? 'Téléversement...' : 'Remplacer l’image'" @click="choose" />
        <input ref="input" type="file" accept="image/*" hidden @change="upload">
        <span v-if="error" class="eden-edit-error">{{ error }}</span>
    </span>
</template>
