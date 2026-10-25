// Замена Dropzone: выбор файла или перетаскивание, превью, отправка и удаление через API аватаров.
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const headers = () => ({ 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' });

export default ({ showUrl, uploadUrl, destroyUrl }) => ({
    current: null, // {uri, name, size} с сервера
    file: null,
    preview: null,
    over: false,
    busy: false,
    error: '',
    done: false,

    async init() {
        try {
            const response = await fetch(showUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const text = await response.text();
            if (response.ok && text) this.current = JSON.parse(text);
        } catch (e) { /* нет аватара */ }
    },

    pick(files) {
        const file = files?.[0];
        this.error = ''; this.done = false;
        if (!file) return;
        if (!['image/jpeg', 'image/png'].includes(file.type)) { this.error = 'Допустимы файлы JPG и PNG'; return; }
        if (file.size > 2 * 1024 * 1024) { this.error = 'Размер файла не больше 2 МБ'; return; }
        this.file = file;
        this.preview = URL.createObjectURL(file);
    },

    async send() {
        if (!this.file || this.busy) return;
        this.busy = true; this.error = '';
        try {
            const body = new FormData();
            body.append('avatar', this.file);
            const response = await fetch(uploadUrl, { method: 'POST', body, headers: headers(), credentials: 'same-origin' });
            const data = await response.json();
            if (!response.ok) { this.error = Object.values(data.errors ?? {}).flat().join(' ') || 'Не удалось загрузить файл'; return; }
            this.current = { uri: '/storage/' + data.uri, name: this.file.name, size: data.size };
            this.file = null; this.preview = null; this.done = true;
        } catch (e) {
            this.error = 'Не удалось загрузить файл';
        } finally { this.busy = false; }
    },

    async remove() {
        if (this.file) { this.file = null; this.preview = null; return; }
        if (!this.current) return;
        this.busy = true; this.error = '';
        try {
            const response = await fetch(destroyUrl, { method: 'DELETE', headers: headers(), credentials: 'same-origin' });
            if (response.ok) { this.current = null; this.done = false; } else this.error = 'Не удалось удалить файл';
        } finally { this.busy = false; }
    },
});
