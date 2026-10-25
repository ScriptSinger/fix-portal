// Содержание статьи слева: собирается из заголовков h2 и h3 текста, подсвечивает раздел, который сейчас читают.
// Авторский блок «Содержание» внутри текста при этом скрывается, чтобы оглавление не дублировалось.
export default () => ({
    items: [],
    active: null,
    open: window.matchMedia('(min-width: 62rem)').matches,

    init() {
        const root = document.querySelector('.article-content');
        if (!root) return;

        const isToc = (el) => /^\s*содержание\s*$/i.test(el.textContent);
        const heads = [...root.querySelectorAll('h2, h3')].filter((h) => !isToc(h));
        if (heads.length < 2) return;

        // Скрываем верхнеуровневый блок с заголовком «Содержание», если он небольшой (это список ссылок)
        [...root.children].forEach((child) => {
            const title = child.matches('h2') ? child : child.querySelector('h2');
            if (title && isToc(title) && child.textContent.length < 3000) child.hidden = true;
        });

        heads.forEach((h, i) => { if (!h.id) h.id = `sec-${i + 1}`; });
        this.items = heads.map((h) => ({ id: h.id, title: h.textContent.trim(), sub: h.tagName === 'H3' }));
        this.active = this.items[0].id;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => { if (entry.isIntersecting) this.active = entry.target.id; });
        }, { rootMargin: '-120px 0px -70% 0px' });
        heads.forEach((h) => observer.observe(h));
    },
});
