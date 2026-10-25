const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Фото к тексту: скрепка, миниатюры с крестиком. Новые фото уходят на сервер сразу, уже сохранённые (saved) при удалении только убираются из формы.
export default ({ uploadUrl, destroyUrl, initial = [], max = 10 }) => ({
    photos: initial.map((url) => ({ id: url, url, saved: true })),
    uploading: false,
    sending: false,
    error: '',

    async attach(files) {
        this.error = '';
        for (const file of [...files].filter((f) => f.type.startsWith('image/')).slice(0, max - this.photos.length)) {
            this.uploading = true;
            try {
                const body = new FormData();
                body.append('file', file);
                const response = await fetch(uploadUrl, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' }, credentials: 'same-origin' });
                const data = await response.json();
                if (!response.ok) { this.error = Object.values(data.errors ?? {}).flat().join(' ') || 'Не удалось загрузить фото'; continue; }
                this.photos.push({ id: data.id, url: data.url, saved: false });
            } catch (e) {
                this.error = 'Не удалось загрузить фото';
            }
        }
        this.uploading = false;
        this.$refs.file.value = '';
    },

    drop(i) {
        const [photo] = this.photos.splice(i, 1);
        if (!photo.saved) {
            fetch(destroyUrl.replace(':id', photo.id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' }, credentials: 'same-origin' });
        }
    },

    grow(el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 480) + 'px'; },
});
