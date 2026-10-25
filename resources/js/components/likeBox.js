const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export default ({ like, dislike, likeUrl, dislikeUrl }) => ({
    like,
    dislike,
    busy: false,

    async send(kind) {
        if (this.busy) return;
        this.busy = true;

        try {
            const response = await fetch(kind === 'like' ? likeUrl : dislikeUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (response.ok) {
                const data = await response.json();
                this.like = data.likeCount;
                this.dislike = data.dislikeCount;
            }
        } finally {
            this.busy = false;
        }
    },
});
