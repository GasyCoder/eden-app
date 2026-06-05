<script setup>
import { ref } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import { adminPatch } from '@/Composables/useAdminRequest';

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
    if (uploading.value) {
        return;
    }

    input.value?.click();
};

const upload = async (event) => {
    const file = event.target.files?.[0];
    let waitsForContentSave = false;

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

        waitsForContentSave = true;
        adminPatch(`/admin/content-blocks/${props.contentKey}`, {
            value: data.path,
            type: 'image',
        }, {
            preserveScroll: true,
            onError: () => {
                error.value = 'Image non enregistrée.';
            },
            onFinish: () => {
                uploading.value = false;
                event.target.value = '';
            },
        });
    } catch {
        error.value = 'Téléversement impossible.';
    } finally {
        if (!waitsForContentSave) {
            uploading.value = false;
            event.target.value = '';
        }
    }
};
</script>

<template>
    <span v-if="canEdit" class="eden-edit-image">
        <AdminEditButton :title="uploading ? 'Téléversement...' : 'Remplacer l’image'" :disabled="uploading" @click="choose" />
        <input ref="input" type="file" accept="image/*" hidden @change="upload">
        <span v-if="error" class="eden-edit-error">{{ error }}</span>
    </span>
    <span v-if="canEdit && uploading" class="eden-upload-skeleton" role="status" aria-live="polite">
        <span class="eden-upload-content">
            <span class="eden-upload-spinner"></span>
            <span>Téléversement...</span>
        </span>
    </span>
</template>
