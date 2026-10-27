// Окно с увеличенной картинкой (в старой теме это был Bootstrap-модал #imageModal):
// клик по картинке внутри статьи/вопроса/ответа или по превью в списке открывает её в окне.
export default () => {
    const root = document.getElementById('lightbox');
    if (!root) return;
    const img = root.querySelector('img');
    const closeButton = root.querySelector('button');
    const prev = root.querySelector('[data-lightbox-prev]');
    const next = root.querySelector('[data-lightbox-next]');
    const counter = root.querySelector('[data-lightbox-count]');
    const spinner = root.querySelector('[data-lightbox-spinner]');
    let opener = null;
    let group = [];
    let index = 0;
    const urlOf = (el) => el.dataset.full || el.currentSrc || el.src;
    // Пока оригинал грузится, показываем крутилку и приглушаем прежний кадр
    const setLoading = (on) => { spinner.hidden = !on; img.style.opacity = on ? '.35' : ''; };
    img.addEventListener('load', () => setLoading(false));
    img.addEventListener('error', () => setLoading(false));
    const render = (src, alt) => {
        if (img.getAttribute('src') !== src) setLoading(true);
        img.src = src; img.alt = alt || '';
        counter.textContent = group.length > 1 ? (index + 1) + ' из ' + group.length : '';
        // соседние фото грузим заранее, чтобы листание не ждало
        [index - 1, index + 1].forEach((i) => { const el = group[(i + group.length) % group.length]; if (el && group.length > 1) new Image().src = urlOf(el); });
    };
    const show = (i) => {
        index = (i + group.length) % group.length;
        render(urlOf(group[index]), group[index].alt);
    };
    const step = (delta) => { if (group.length > 1) show(index + delta); };
    // Листать можно фото из одной мозаики или из одного текста
    const groupOf = (image) => {
        const box = image.closest('[data-gallery], .article-content');
        return box ? [...box.querySelectorAll('img')].filter((el) => el.matches('[data-lightbox-cover], .article-content img')) : [image];
    };
    const close = () => {
        root.classList.add('hidden'); root.classList.remove('flex'); img.removeAttribute('src'); setLoading(false); document.body.classList.remove('overflow-hidden');
        opener?.focus(); opener = null;
    };
    const open = (src, alt, images = []) => {
        opener = document.activeElement;
        group = images; index = Math.max(0, images.findIndex((el) => (el.dataset.full || el.currentSrc || el.src) === src));
        prev.hidden = next.hidden = images.length < 2;
        render(src, alt); root.classList.remove('hidden'); root.classList.add('flex'); document.body.classList.add('overflow-hidden'); closeButton.focus();
    };

    // Пролистывание пальцем: заметный горизонтальный жест влево (следующее) и вправо (предыдущее); вертикальный прокручивает большое фото
    let touch = null;
    root.addEventListener('touchstart', (event) => { touch = event.touches.length === 1 ? { x: event.touches[0].clientX, y: event.touches[0].clientY } : null; }, { passive: true });
    root.addEventListener('touchend', (event) => {
        if (!touch || group.length < 2) return;
        const dx = event.changedTouches[0].clientX - touch.x;
        const dy = event.changedTouches[0].clientY - touch.y;
        touch = null;
        if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5) step(dx < 0 ? 1 : -1);
    }, { passive: true });

    // role="button" для картинок в тексте, как в старой теме; с клавиатуры открываются Enter и пробелом
    document.querySelectorAll('.article-content img, [data-lightbox-cover]').forEach((el) => {
        el.setAttribute('role', 'button');
        if (!el.closest('a[href]')) el.setAttribute('tabindex', '0');
    });

    document.addEventListener('click', (event) => {
        if (!root.classList.contains('hidden')) {
            if (event.target.closest('[data-lightbox-prev]')) return step(-1);
            if (event.target.closest('[data-lightbox-next]')) return step(1);
            if (event.target === root || event.target.closest('[data-lightbox-close]')) close();
            return;
        }
        const trigger = event.target.closest('[data-lightbox]');
        if (trigger) {
            event.preventDefault();
            open(trigger.dataset.lightbox, trigger.querySelector('img')?.alt);
            return;
        }
        const image = event.target.closest('.article-content img, [data-lightbox-cover]');
        if (!image) return;
        const link = image.closest('a');
        if (link && link.getAttribute('href') && !link.getAttribute('href').startsWith('#')) return; // реальная ссылка
        event.preventDefault();
        open(image.dataset.full || image.currentSrc || image.src, image.alt, groupOf(image));
    });
    document.addEventListener('keydown', (event) => {
        if (root.classList.contains('hidden')) {
            const image = event.target.closest?.('.article-content img[tabindex], [data-lightbox-cover][tabindex]');
            if (image && (event.key === 'Enter' || event.key === ' ')) { event.preventDefault(); open(image.dataset.full || image.currentSrc || image.src, image.alt, groupOf(image)); }
            return;
        }
        if (event.key === 'Escape') close();
        if (event.key === 'ArrowLeft') step(-1);
        if (event.key === 'ArrowRight') step(1);
        if (event.key === 'Tab') { event.preventDefault(); closeButton.focus(); } // в окне один элемент управления: фокус не уходит под затемнение
    });
};
