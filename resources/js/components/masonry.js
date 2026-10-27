// Кирпичная раскладка карточек в две колонки с порядком чтения слева направо.
// CSS-колонки укладывали карточки плотно, но порядок шёл сверху вниз по колонкам. Здесь сетка с маленькими строками:
// каждая карточка занимает столько строк, сколько нужно по высоте, а браузер сам ставит следующую в самое низкое свободное место.
// Без скрипта (и на телефоне, где колонка одна) остаётся обычная сетка.
const UNIT = 4; // высота служебной строки сетки, px

export default () => {
    document.querySelectorAll('[data-masonry]').forEach((grid) => {
        const wide = window.matchMedia('(min-width: 36rem)');
        const gap = parseFloat(getComputedStyle(grid).getPropertyValue('--masonry-gap')) || 24;

        const items = () => [...grid.children];
        const layout = () => {
            grid.classList.toggle('masonry-on', wide.matches);
            items().forEach((item) => {
                item.style.gridRowEnd = wide.matches ? 'span ' + Math.ceil((item.firstElementChild.getBoundingClientRect().height + gap) / UNIT) : '';
            });
        };

        const observer = new ResizeObserver(layout);
        items().forEach((item) => observer.observe(item.firstElementChild));
        wide.addEventListener('change', layout);
        layout();
    });
};
