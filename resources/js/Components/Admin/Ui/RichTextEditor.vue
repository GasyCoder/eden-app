<script setup>
import { Bold, Heading2, Heading3, ImagePlus, Italic, Link2, List, ListOrdered, Minus, Pilcrow, Quote, Redo2, Undo2 } from '@lucide/vue';
import Image from '@tiptap/extension-image';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, ref, watch } from 'vue';

// Éditeur riche (Tiptap). Le HTML est nettoyé côté serveur (RichTextSanitizer).
const props = defineProps({
    label: { type: String, default: '' },
    id: { type: String, default: undefined },
    invalid: { type: Boolean, default: false },
    placeholder: { type: String, default: 'Rédigez le contenu…' },
});

const model = defineModel({ type: String, default: '' });
const uploading = ref(false);
const fileInput = ref(null);

const editor = useEditor({
    content: model.value || '',
    extensions: [
        StarterKit.configure({
            heading: { levels: [2, 3] },
            link: { openOnClick: false, autolink: true, HTMLAttributes: { rel: 'noopener noreferrer' } },
        }),
        Image,
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-eden max-w-none min-h-64 max-h-[70vh] overflow-y-auto px-4 py-3 text-base outline-none',
            role: 'textbox',
            'aria-multiline': 'true',
            ...(props.label ? { 'aria-label': props.label } : {}),
            ...(props.id ? { id: props.id } : {}),
        },
    },
    onUpdate: ({ editor: instance }) => {
        model.value = instance.isEmpty ? '' : instance.getHTML();
    },
});

// Synchronise l'éditeur quand la valeur change de l'extérieur (bascule FR/EN)
watch(model, (value) => {
    if (editor.value && value !== editor.value.getHTML() && !(value === '' && editor.value.isEmpty)) {
        editor.value.commands.setContent(value || '', { emitUpdate: false });
    }
});

onBeforeUnmount(() => editor.value?.destroy());

const setLink = () => {
    const previous = editor.value.getAttributes('link').href ?? '';
    const url = window.prompt('Adresse du lien (laisser vide pour retirer le lien)', previous);

    if (url === null) {
        return;
    }

    if (url.trim() === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url.trim() }).run();
};

const uploadImage = async (event) => {
    const file = event.target.files?.[0];
    event.target.value = '';

    if (!file) {
        return;
    }

    uploading.value = true;

    try {
        const data = new FormData();
        data.append('image', file);
        const response = await fetch('/admin/uploads/images', {
            method: 'POST',
            body: data,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '', Accept: 'application/json' },
        });
        const payload = await response.json().catch(() => ({}));

        if (response.ok && payload.path) {
            const alt = window.prompt('Texte alternatif de l’image (pour les lecteurs d’écran)', '') ?? '';
            editor.value.chain().focus().setImage({ src: payload.path, alt }).run();
        } else {
            window.alert(payload.message ?? 'Image refusée.');
        }
    } finally {
        uploading.value = false;
    }
};

const tools = [
    { label: 'Paragraphe', icon: Pilcrow, run: (e) => e.chain().focus().setParagraph().run(), active: (e) => e.isActive('paragraph') },
    { label: 'Titre de section', icon: Heading2, run: (e) => e.chain().focus().toggleHeading({ level: 2 }).run(), active: (e) => e.isActive('heading', { level: 2 }) },
    { label: 'Sous-titre', icon: Heading3, run: (e) => e.chain().focus().toggleHeading({ level: 3 }).run(), active: (e) => e.isActive('heading', { level: 3 }) },
    { divider: true },
    { label: 'Gras', icon: Bold, run: (e) => e.chain().focus().toggleBold().run(), active: (e) => e.isActive('bold') },
    { label: 'Italique', icon: Italic, run: (e) => e.chain().focus().toggleItalic().run(), active: (e) => e.isActive('italic') },
    { label: 'Lien', icon: Link2, run: setLink, active: (e) => e.isActive('link') },
    { divider: true },
    { label: 'Liste à puces', icon: List, run: (e) => e.chain().focus().toggleBulletList().run(), active: (e) => e.isActive('bulletList') },
    { label: 'Liste numérotée', icon: ListOrdered, run: (e) => e.chain().focus().toggleOrderedList().run(), active: (e) => e.isActive('orderedList') },
    { label: 'Citation', icon: Quote, run: (e) => e.chain().focus().toggleBlockquote().run(), active: (e) => e.isActive('blockquote') },
    { label: 'Séparateur', icon: Minus, run: (e) => e.chain().focus().setHorizontalRule().run() },
];
</script>

<template>
    <div :class="['overflow-hidden rounded-md border bg-background shadow-xs focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50 dark:bg-input/20', invalid ? 'border-destructive' : 'border-input']">
        <div v-if="editor" class="flex flex-wrap items-center gap-0.5 border-b bg-muted p-1" role="toolbar" aria-label="Mise en forme">
            <template v-for="(tool, index) in tools" :key="index">
                <span v-if="tool.divider" class="mx-1 h-5 w-px bg-border" aria-hidden="true"></span>
                <button
                    v-else
                    type="button"
                    class="inline-grid size-8 place-items-center rounded-md text-muted-foreground transition-colors hover:bg-background hover:text-foreground disabled:opacity-40 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none aria-pressed:bg-background aria-pressed:text-foreground aria-pressed:shadow-xs"
                    :aria-pressed="tool.active ? (tool.active(editor) ? 'true' : 'false') : undefined"
                    :title="tool.label"
                    :aria-label="tool.label"
                    @click="tool.run(editor)"
                >
                    <component :is="tool.icon" class="size-4" aria-hidden="true" />
                </button>
            </template>
            <span class="mx-1 h-5 w-px bg-border" aria-hidden="true"></span>
            <button type="button" class="inline-grid size-8 place-items-center rounded-md text-muted-foreground transition-colors hover:bg-background hover:text-foreground disabled:opacity-40 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none aria-pressed:bg-background aria-pressed:text-foreground aria-pressed:shadow-xs" title="Insérer une image" aria-label="Insérer une image" :disabled="uploading" @click="fileInput?.click()">
                <span v-if="uploading" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" aria-hidden="true"></span>
                <ImagePlus v-else class="size-4" aria-hidden="true" />
            </button>
            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" tabindex="-1" @change="uploadImage">
            <span class="ml-auto flex gap-0.5">
                <button type="button" class="inline-grid size-8 place-items-center rounded-md text-muted-foreground transition-colors hover:bg-background hover:text-foreground disabled:opacity-40 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none aria-pressed:bg-background aria-pressed:text-foreground aria-pressed:shadow-xs" title="Annuler" aria-label="Annuler" :disabled="!editor.can().undo()" @click="editor.chain().focus().undo().run()"><Undo2 class="size-4" aria-hidden="true" /></button>
                <button type="button" class="inline-grid size-8 place-items-center rounded-md text-muted-foreground transition-colors hover:bg-background hover:text-foreground disabled:opacity-40 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none aria-pressed:bg-background aria-pressed:text-foreground aria-pressed:shadow-xs" title="Rétablir" aria-label="Rétablir" :disabled="!editor.can().redo()" @click="editor.chain().focus().redo().run()"><Redo2 class="size-4" aria-hidden="true" /></button>
            </span>
        </div>
        <EditorContent :editor="editor" />
    </div>
</template>
