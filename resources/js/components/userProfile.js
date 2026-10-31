const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export default function userProfile() {
    return {
        open: false,
        loading: false,
        error: '',
        mobile: false,
        data: null,
        trigger: null,
        requestId: 0,

        init() {
            this.avatarClick = (event) => {
                const trigger = event.target.closest('[data-user-profile-url]');
                if (!trigger) return;
                event.preventDefault();
                event.stopPropagation();
                this.openProfile({
                    id: Number(trigger.dataset.userProfileId),
                    url: trigger.dataset.userProfileUrl,
                    trigger,
                });
            };
            document.addEventListener('click', this.avatarClick, true);
        },

        async openProfile(detail) {
            if (this.open && this.data?.id === detail.id) {
                this.close();
                return;
            }

            this.requestId += 1;
            const requestId = this.requestId;
            this.open = true;
            document.body.classList.add('overflow-hidden');
            this.loading = true;
            this.error = '';
            this.data = null;
            this.trigger = detail.trigger ?? null;
            this.mobile = window.innerWidth < 640;

            try {
                const response = await fetch(detail.url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf() },
                    credentials: 'same-origin',
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(payload.message || 'Профиль недоступен.');
                if (requestId !== this.requestId) return;
                this.data = payload.data ?? payload;
            } catch (error) {
                if (requestId !== this.requestId) return;
                this.error = error.message || 'Не удалось загрузить профиль.';
            } finally {
                if (requestId === this.requestId) {
                    this.loading = false;
                }
            }
        },

        position() {
            this.mobile = window.innerWidth < 640;
            if (this.mobile || !this.$refs.card || !this.trigger) return;

            const trigger = this.trigger.getBoundingClientRect();
            const card = this.$refs.card.getBoundingClientRect();
            const gap = 10;
            const margin = 12;
            let left = Math.min(Math.max(margin, trigger.left), window.innerWidth - card.width - margin);
            let top = trigger.bottom + gap;
            if (top + card.height > window.innerHeight - margin) top = trigger.top - card.height - gap;
            top = Math.max(margin, top);
            this.$refs.card.style.left = `${left}px`;
            this.$refs.card.style.top = `${top}px`;
        },

        close() {
            this.requestId += 1;
            this.open = false;
            document.body.classList.remove('overflow-hidden');
            this.loading = false;
            this.error = '';
            this.data = null;
            const trigger = this.trigger;
            this.trigger = null;
            this.$nextTick(() => trigger?.focus?.({ preventScroll: true }));
        },

        initials() {
            return (this.data?.name || '?').trim().split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase();
        },

        formatDate(value) {
            if (!value) return '';
            return new Intl.DateTimeFormat('ru-RU', { month: 'long', year: 'numeric' }).format(new Date(`${value}T00:00:00`));
        },
    };
}
