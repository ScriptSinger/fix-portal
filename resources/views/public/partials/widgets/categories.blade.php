<x-ui.widget title="Популярные категории">
    <ul>
        @foreach ($popular_categories as $category)
            <li class="border-b border-dashed border-line pb-[9.6px] pl-[3.2px] pr-[3.2px] pt-[6.4px] first:pt-0 last:border-0 max-md:py-0">
                <a href="{{ route('categories.show', ['category' => $category->slug]) }}"
                    class="keep -mx-5 flex justify-between gap-3 px-5 capitalize leading-[15px] text-body transition-colors hover:bg-brand-50 hover:shadow-[inset_4px_0_0_var(--color-brand-400)] max-md:py-[13px]">
                    <span>{{ $category->title }}</span><span class="font-normal text-body">{{ $category->posts_count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</x-ui.widget>
