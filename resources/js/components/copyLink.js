export default (url) => ({
    copied: false,

    async copy() {
        try {
            await navigator.clipboard.writeText(url);
            this.copied = true;
            setTimeout(() => (this.copied = false), 2000);
        } catch (e) {
            window.prompt('Скопируйте ссылку:', url);
        }
    },
});
