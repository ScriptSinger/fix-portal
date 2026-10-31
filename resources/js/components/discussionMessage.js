import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export default function discussionMessage(options) {
    return {
        menu: false,
        editing: false,
        saving: false,
        deleting: false,
        error: '',
        editor: null,
        originalText: options.text ?? '',
        options,

        init() {
            this.$watch('menu', (open) => {
                if (open) this.$nextTick(() => this.$refs.menu?.querySelector('button')?.focus());
            });
        },

        startEdit() {
            this.menu = false;
            this.$dispatch('discussion-edit', {
                id: this.options.id,
                anchor: `#${this.options.isReply ? 'r' : 'c'}-${this.options.id}`,
                name: this.options.name,
                text: this.originalText,
                hasImages: this.options.hasImages,
                updateUrl: this.options.updateUrl,
            });
        },

        async remove() {
            this.menu = false;
            if (this.deleting || !window.confirm('Подтвердите удаление')) return;
            this.deleting = true;
            try {
                const response = await fetch(this.options.deleteUrl, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Не удалось удалить сообщение.');
                (this.$root.closest('[data-discussion-row]') ?? this.$root).remove();
                this.$dispatch('discussion-toast', { type: 'success', message: 'Сообщение удалено' });
            } catch (error) {
                this.deleting = false;
                this.$dispatch('discussion-toast', { type: 'error', message: error.message || 'Не удалось удалить сообщение.' });
            }
        },

        reply() {
            this.menu = false;
            this.$dispatch('discussion-reply', {
                root: this.options.root,
                reply: this.options.isReply ? this.options.id : null,
                name: this.options.name,
                text: this.options.preview,
            });
        },

    };
}

export function discussionEditor(options = {}) {
    // Editor нельзя хранить в Alpine state: Alpine Proxy ломает identity ProseMirror transactions.
    let editorInstance = null;

    return {
        saving: false,
        error: '',
        photos: [],
        busy: false,
        emojiOpen: false,
        emojis: options.emojis ?? [],
        target: null,

        openEdit(detail) {
            editorInstance?.destroy();
            editorInstance = null;
            this.target = detail;
            this.photos = [];
            this.error = '';
            this.$nextTick(() => {
                editorInstance = new Editor({
                    element: this.$refs.editor,
                    extensions: [StarterKit, Image],
                    content: detail.text,
                    editorProps: {
                        attributes: { class: 'discussion-editor' },
                        handleKeyDown: (view, event) => {
                            if (event.key === 'Escape') {
                                event.preventDefault();
                                this.cancel();
                                return true;
                            }
                            if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                                event.preventDefault();
                                this.save();
                                return true;
                            }
                            return false;
                        },
                    },
                });
                // Не используем Tiptap focus('end') сразу после инициализации: Alpine ещё
                // меняет x-show и ProseMirror может получить устаревший transaction state.
                requestAnimationFrame(() => {
                    if (editorInstance && !editorInstance.isDestroyed) editorInstance.view.focus();
                });
            });
        },

        cancel() {
            editorInstance?.destroy();
            editorInstance = null;
            this.target = null;
            this.error = '';
            this.$dispatch('discussion-edit-cancel');
        },

        async attach(files) {
            for (const file of [...files].filter((file) => file.type.startsWith('image/')).slice(0, 6 - this.photos.length)) {
                this.busy = true;
                try {
                    const body = new FormData();
                    body.append('file', file);
                    const response = await fetch('/api/users-images', { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' }, credentials: 'same-origin' });
                    const data = await response.json();
                    if (!response.ok) throw new Error(Object.values(data.errors ?? {}).flat().join(' ') || 'Не удалось загрузить фото.');
                    this.photos.push({ id: data.id, url: data.url });
                    if (editorInstance && !editorInstance.isDestroyed) {
                        editorInstance.view.focus();
                        editorInstance.commands.setImage({ src: data.url });
                    }
                } catch (error) {
                    this.error = error.message || 'Не удалось загрузить фото.';
                }
            }
            this.busy = false;
            if (this.$refs.file) this.$refs.file.value = '';
        },

        async save() {
            if (!editorInstance || this.saving) return;
            const text = editorInstance.getText().trim();
            if (!text && !this.target.hasImages) {
                this.error = 'Введите текст сообщения или оставьте изображение.';
                return;
            }

            this.saving = true;
            this.error = '';
            const content = document.querySelector(`${this.target.anchor} [data-message-content]`);
            const previousContent = content?.innerHTML ?? '';
            if (content) content.textContent = text;

            try {
                const response = await fetch(this.target.updateUrl, {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json', 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ text, images: this.photos.map((photo) => photo.url) }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(Object.values(data.errors ?? {}).flat().join(' ') || data.message || 'Не удалось сохранить сообщение.');
                if (content && data.content) content.outerHTML = data.content;
                this.$dispatch('discussion-toast', { type: 'success', message: 'Сообщение изменено' });
                this.cancel();
            } catch (error) {
                if (content) content.innerHTML = previousContent;
                this.error = error.message || 'Не удалось сохранить сообщение.';
            } finally {
                this.saving = false;
            }
        },

        insertEmoji(emoji) {
            if (editorInstance && !editorInstance.isDestroyed) {
                editorInstance.view.focus();
                editorInstance.commands.insertContent(emoji);
            }
            this.emojiOpen = false;
        },
    };
}
