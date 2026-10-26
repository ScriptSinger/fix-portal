// Карусели из статей (разметка Bootstrap 4: .carousel > .carousel-inner > .carousel-item, кнопки data-slide, точки data-slide-to).
// Раньше их оживлял Bootstrap JS, теперь это небольшой скрипт без библиотек.
const INTERVAL = 5000;

function setup(root) {
    const items = [...root.querySelectorAll('.carousel-item')];
    if (items.length < 2) return;
    const dots = [...root.querySelectorAll('[data-slide-to]')];
    let index = Math.max(0, items.findIndex((i) => i.classList.contains('active')));
    let timer = null;

    items.forEach((i) => i.classList.remove('carousel-item-left', 'carousel-item-right', 'carousel-item-next', 'carousel-item-prev'));

    const show = (next) => {
        index = (next + items.length) % items.length;
        items.forEach((item, n) => item.classList.toggle('active', n === index));
        dots.forEach((dot, n) => dot.classList.toggle('active', n === index));
    };
    show(index);

    root.querySelectorAll('[data-slide]').forEach((button) =>
        button.addEventListener('click', (event) => {
            event.preventDefault();
            show(index + (button.dataset.slide === 'prev' ? -1 : 1));
        }));
    dots.forEach((dot) => dot.addEventListener('click', () => show(Number(dot.dataset.slideTo))));

    // стрелки влево/вправо переключают слайды, когда карусель в фокусе
    root.setAttribute('tabindex', '0');
    root.setAttribute('role', 'region');
    root.setAttribute('aria-roledescription', 'карусель');
    root.setAttribute('aria-label', 'Галерея изображений');
    root.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') { event.preventDefault(); show(index - 1); }
        if (event.key === 'ArrowRight') { event.preventDefault(); show(index + 1); }
    });

    if (root.dataset.ride === 'carousel' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const start = () => { stop(); timer = setInterval(() => show(index + 1), INTERVAL); };
        const stop = () => clearInterval(timer);
        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);
        root.addEventListener('focusin', stop);
        root.addEventListener('focusout', start);
        start();
    }
}

export default () => document.querySelectorAll('.article-content .carousel').forEach(setup);
