// Плавная прокрутка к разделам статьи: ссылки из редактора вида <a class="title__link" data-scroll="#id">
// (в старой теме это делал jQuery). Обычные #якоря плавно прокручивает html.scroll-smooth.
export default () => {
    const scrollToDiscussionHash = () => {
        const hash = window.location.hash;
        if (!/^#(?:c|r)-\d+$/.test(hash)) return;

        const target = document.getElementById(hash.slice(1));
        if (!target) return;

        requestAnimationFrame(() => requestAnimationFrame(() => {
            // Межстраничный переход должен завершиться сразу: иначе длинная лента
            // может прокручиваться дольше, чем пользователь ожидает.
            window.scrollTo({ top: Math.max(0, target.getBoundingClientRect().top + window.scrollY - 100), behavior: 'auto' });
        }));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scrollToDiscussionHash, { once: true });
    } else {
        scrollToDiscussionHash();
    }
    window.setTimeout(scrollToDiscussionHash, 150);
    window.addEventListener('hashchange', scrollToDiscussionHash);

    document.addEventListener('click', (event) => {
        const link = event.target.closest('.title__link[data-scroll]');
        if (!link) return;

        // Идентификаторы вида #1, #1-2 не являются корректными CSS-селекторами, поэтому ищем по id (как делал jQuery)
        const raw = link.dataset.scroll.trim();
        let target = null;
        if (raw.startsWith('#') && !/[\s.>+~\[:,]/.test(raw)) {
            target = document.getElementById(raw.slice(1));
        } else {
            try { target = document.querySelector(raw); } catch (e) { /* некорректный селектор */ }
        }
        if (!target) return;

        event.preventDefault();
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        if (target.id) history.replaceState(null, '', '#' + target.id);
    });
};
