const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export default ({ url, counts, mine, order }) => ({
    counts,
    mine,
    order,
    open: false,
    busy: false,

    // чипы в порядке панели; пустые не показываем
    get chips() {
        return order.filter((e) => this.counts[e] > 0).map((e) => ({ emoji: e, n: this.counts[e] }));
    },

    async react(emoji) {
        if (this.busy) return;
        this.busy = true; this.open = false;
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ emoji }),
                credentials: 'same-origin',
            });
            if (response.ok) {
                const data = await response.json();
                this.counts = data.counts;
                this.mine = data.mine;
            }
        } finally {
            this.busy = false;
        }
    },
});
