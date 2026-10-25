/*
 * Фильтр каталога прошивок.
 *  - Отметка не перезагружает страницу: число прошивок считает сервер (firmwares.count),
 *    кнопка показывает «Показать N прошивок», а фильтр применяется по нажатию.
 *  - На компьютере это панель слева. На телефоне панель выезжает слева по язычку «Фильтр»:
 *    сначала список групп с выбранным значением, нажатие на группу открывает её значения
 *    (как фильтр в проекте start-stop-laravel).
 *  - Серии зависят от выбранной марки.
 */
const MOBILE = '(max-width: 991.98px)';
const FIELDS = ['search', 'brand', 'series', 'platform', 'extension', 'crc32'];

const plural = (n, [one, few, many]) => {
    const m10 = n % 10, m100 = n % 100;
    if (m10 === 1 && m100 !== 11) return one;
    if (m10 >= 2 && m10 <= 4 && (m100 < 12 || m100 > 14)) return few;
    return many;
};

export default ({ url, countUrl, state, byBrand, allSeries }) => ({
    ...state,
    open: false,
    group: null,
    mobile: window.matchMedia(MOBILE).matches,
    changed: false,
    count: null,
    timer: null,
    request: 0,

    init() {
        const media = window.matchMedia(MOBILE);
        media.addEventListener('change', () => { this.mobile = media.matches; if (!this.mobile) this.close(); });
        // Смена марки сбрасывает серию, которой у новой марки нет
        this.$watch('brand', () => { if (!this.seriesList.includes(this.series)) this.series = ''; });
        FIELDS.forEach((key) => this.$watch(key, () => this.touch()));
    },

    get seriesList() {
        return this.brand ? (byBrand[this.brand] ?? []) : allSeries;
    },

    get submitLabel() {
        if (!this.changed) return 'Применить';
        if (this.count === null) return 'Считаем…';
        return `Показать ${this.count.toLocaleString('ru-RU')} ${plural(this.count, ['прошивку', 'прошивки', 'прошивок'])}`;
    },

    summary(key) {
        return this[key] ? String(this[key]) : 'Все';
    },

    params() {
        const params = new URLSearchParams();
        FIELDS.forEach((key) => { const value = String(this[key] ?? '').trim(); if (value) params.set(key, value); });
        return params;
    },

    touch() {
        this.changed = true;
        this.count = null;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.fetchCount(), 250);
    },

    async fetchCount() {
        const id = ++this.request;
        try {
            const response = await fetch(`${countUrl}?${this.params()}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (id === this.request) this.count = data.count;
        } catch (e) {
            if (id === this.request) this.count = null;
        }
    },

    apply() {
        const params = this.params();
        // Сортировку и размер страницы не теряем при смене фильтра
        const current = new URLSearchParams(window.location.search);
        ['sort', 'dir', 'per_page'].forEach((key) => { if (current.has(key)) params.set(key, current.get(key)); });
        window.location.href = params.toString() ? `${url}?${params}` : url;
    },

    show() { this.open = true; document.body.classList.add('overflow-hidden'); },
    close() { this.open = false; this.group = null; document.body.classList.remove('overflow-hidden'); },
});
