@php
    $card = 'mb-12 rounded-[5px] bg-white px-8 py-12';
@endphp
<section class="{{ $card }}">
    <h2 class="mb-8 text-lg font-bold leading-[18px] text-ink">Реклама</h2>
    <div id="yandex_rtb_R-A-1966455-2"></div>
</section>
@production
    @push('scripts')
        <script>window.yaContextCb = window.yaContextCb || []</script>
        <script src="https://yandex.ru/ads/system/context.js" async></script>
        <script>
            window.yaContextCb.push(() => Ya.Context.AdvManager.render({
                blockId: "R-A-1966455-2", renderTo: "yandex_rtb_R-A-1966455-2"
            }))
        </script>
    @endpush
@endproduction
