{{-- Рекламный блок показывается только в проде и только когда админ включил рекламу (Настройки → Реклама); место резервируется, чтобы страница не прыгала --}}
@production
@if (\App\Models\SiteWidget::enabled('ads'))
    @php($blockId = \App\Models\SiteWidget::value('ads', 'block_id', 'R-A-1966455-2'))
    <section class="mb-6 min-h-[250px] rounded-card bg-white p-5" aria-label="Реклама">
        <div id="yandex_rtb_{{ $blockId }}"></div>
    </section>
    @push('scripts')
        <script>window.yaContextCb = window.yaContextCb || []</script>
        <script src="https://yandex.ru/ads/system/context.js" async></script>
        <script>
            window.yaContextCb.push(() => Ya.Context.AdvManager.render({
                blockId: @json($blockId), renderTo: @json('yandex_rtb_' . $blockId)
            }))
        </script>
    @endpush
@endif
@endproduction
