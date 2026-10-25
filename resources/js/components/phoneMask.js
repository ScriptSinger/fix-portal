// Маска телефона +7 (999) 999-99-99; библиотека подгружается только там, где есть поле.
export default () => ({
    async init() {
        const { default: IMask } = await import('imask');
        IMask(this.$el, { mask: '+{7} (000) 000-00-00' });
    },
});
