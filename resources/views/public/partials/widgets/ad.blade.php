{{-- Рекламный блок показывается только там, где реклама реально грузится; место резервируется, чтобы страница не прыгала --}}
@production
    <section class="mb-6 min-h-[250px] rounded-[5px] bg-white p-5" aria-label="Реклама">
        <div id="yandex_rtb_R-A-1966455-2"></div>
    </section>
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
