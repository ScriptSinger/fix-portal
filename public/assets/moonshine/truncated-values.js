(() => {
    const cellSelector = 'td, th';
    const textSelector = 'a, span, div, p, code';

    const isOverflowing = (element) => (
        element.scrollWidth > element.clientWidth + 1
        || element.scrollHeight > element.clientHeight + 1
    );

    const addTitle = (cell) => {
        const candidates = [cell, ...cell.querySelectorAll(textSelector)]
            .filter((element) => !element.closest('.pretty-limit'))
            .filter((element) => element.textContent.trim() !== '')
            .filter((element) => element.children.length === 0 || element.tagName === 'A');

        const target = candidates.find(isOverflowing);

        if (!target) {
            return;
        }

        const text = target.textContent.trim();
        if (text.length > 0 && target.getAttribute('title') !== text) {
            target.setAttribute('title', text);
        }
    };

    const scan = (root = document) => {
        if (root.matches?.(cellSelector)) {
            addTitle(root);
        }

        root.querySelectorAll?.(cellSelector).forEach(addTitle);
    };

    let scanTimer;
    const scheduleScan = () => {
        clearTimeout(scanTimer);
        scanTimer = setTimeout(() => scan(), 80);
    };

    document.addEventListener('mouseover', (event) => {
        const cell = event.target.closest?.(cellSelector);
        if (cell) {
            addTitle(cell);
        }
    });

    document.addEventListener('focusin', (event) => {
        const cell = event.target.closest?.(cellSelector);
        if (cell) {
            addTitle(cell);
        }
    });

    document.addEventListener('DOMContentLoaded', scheduleScan);
    document.addEventListener('moonshine:init', scheduleScan);

    new MutationObserver(scheduleScan).observe(document.body, {
        childList: true,
        subtree: true,
    });
})();
