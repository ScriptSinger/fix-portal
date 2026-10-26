@props(['items' => []]) {{-- [[title, url], ...] --}}
<div class="rounded-[5px] bg-white p-12 max-sm:px-4">
    <ul class="mb-12 flex flex-wrap max-md:justify-center">
        @foreach ($items as [$title, $url])
            <li class="my-2 mr-2 inline-block h-7 rounded-[3px] bg-brand-400 px-4">
                <a href="{{ $url }}"
                    class="keep mt-[1px] inline-block px-4 text-[11px] uppercase leading-7 tracking-[2px] text-black hover:underline">{{ $title }}</a>
            </li>
        @endforeach
    </ul>
</div>
