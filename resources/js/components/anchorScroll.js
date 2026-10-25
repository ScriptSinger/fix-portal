// Плавная прокрутка к разделам статьи: ссылки из редактора вида <a class="title__link" data-scroll="#id">
// (в старой теме это делал jQuery). Обычные #якоря плавно прокручивает html.scroll-smooth.
export default () => {
    document.addEventListener('click', (event) => {
        const link = event.target.closest('.title__link[data-scroll]');
        if (!link) return;

        let target = null;
        try { target = document.querySelector(link.dataset.scroll); } catch (e) { /* некорректный селектор */ }
        if (!target) return;

        event.preventDefault();
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        if (target.id) history.replaceState(null, '', '#' + target.id);
    });
};
