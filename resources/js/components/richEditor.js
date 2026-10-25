// Замена Summernote: Tiptap подгружается только на страницах, где есть редактор.
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export default ({ uploadUrl, destroyUrl }) => {
    let editor = null; // вне реактивного состояния Alpine: прокси ломают ProseMirror
    let knownIds = new Set();

    const idsIn = (html) => new Set([...html.matchAll(/data-id="(\d+)"/g)].map((m) => m[1]));

    return {
        ready: false,
        fullscreen: false,
        busy: false,
        error: '',
        active: { bold: false, underline: false, link: false },

        async init() {
            const [{ Editor }, { default: StarterKit }, { default: Image }] = await Promise.all([
                import('@tiptap/core'),
                import('@tiptap/starter-kit'),
                import('@tiptap/extension-image'),
            ]);

            const UploadedImage = Image.extend({
                addAttributes() {
                    return { ...this.parent?.(), 'data-id': { default: null }, class: { default: 'img-fluid' } };
                },
            });

            const field = this.$refs.field;
            knownIds = idsIn(field.value);

            editor = new Editor({
                element: this.$refs.surface,
                extensions: [
                    StarterKit.configure({
                        heading: false, blockquote: false, bulletList: false, orderedList: false, listItem: false,
                        code: false, codeBlock: false, horizontalRule: false, strike: false, italic: false,
                        link: { openOnClick: false },
                    }),
                    UploadedImage,
                ],
                content: field.value,
                editorProps: {
                    attributes: { class: 'article-content min-h-[150px] px-3 py-2 outline-none' },
                    handlePaste: (_view, event) => this.takeFiles(event.clipboardData?.files),
                    handleDrop: (_view, event) => this.takeFiles(event.dataTransfer?.files),
                },
                onUpdate: ({ editor: e }) => {
                    field.value = e.isEmpty ? '' : e.getHTML();
                    this.dropRemovedImages(field.value);
                },
                onTransaction: ({ editor: e }) => {
                    this.active = { bold: e.isActive('bold'), underline: e.isActive('underline'), link: e.isActive('link') };
                },
            });
            this.ready = true;
        },

        destroy() { editor?.destroy(); },

        toggle(mark) { editor.chain().focus()[mark === 'bold' ? 'toggleBold' : 'toggleUnderline']().run(); },

        link() {
            const previous = editor.getAttributes('link').href ?? '';
            const href = window.prompt('Адрес ссылки', previous);
            if (href === null) return;
            const chain = editor.chain().focus().extendMarkRange('link');
            href === '' ? chain.unsetLink().run() : chain.setLink({ href }).run();
        },

        pick() { this.$refs.file.click(); },

        takeFiles(files) {
            const image = [...(files ?? [])].find((f) => f.type.startsWith('image/'));
            if (!image) return false;
            this.upload(image);
            return true;
        },

        async upload(file) {
            this.busy = true; this.error = '';
            try {
                const body = new FormData();
                body.append('file', file);
                const response = await fetch(uploadUrl, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' }, credentials: 'same-origin' });
                const data = await response.json();
                if (!response.ok) {
                    this.error = Object.values(data.errors ?? {}).flat().join(' ') || 'Не удалось загрузить изображение';
                    return;
                }
                editor.chain().focus().insertContent({ type: 'image', attrs: { src: data.url, 'data-id': String(data.id) } }).run();
            } catch (e) {
                this.error = 'Не удалось загрузить изображение';
            } finally {
                this.busy = false;
                this.$refs.file.value = '';
            }
        },

        dropRemovedImages(html) {
            const now = idsIn(html);
            for (const id of knownIds) {
                if (!now.has(id)) {
                    fetch(destroyUrl.replace(':id', id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' }, credentials: 'same-origin' });
                }
            }
            knownIds = now;
        },
    };
};
