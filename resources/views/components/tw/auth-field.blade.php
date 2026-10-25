@props(['name', 'placeholder', 'type' => 'text', 'icon' => 'envelope', 'value' => null, 'autocomplete' => null])
<div class="mb-4">
    <div class="flex">
        <input name="{{ $name }}" type="{{ $type }}" placeholder="{{ $placeholder }}" value="{{ $type === 'password' ? '' : old($name, $value) }}"
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @class(['block h-[38px] min-w-0 flex-1 rounded-l border bg-white px-3 text-base text-[#495057] placeholder:text-[#6c757d] focus:border-[#80bdff] focus:outline-none', 'border-[#ced4da]' => ! $errors->has($name), 'border-[#dc3545]' => $errors->has($name)])>
        <span @class(['flex w-[41px] items-center justify-center rounded-r border border-l-0 bg-white text-[#777]', 'border-[#ced4da]' => ! $errors->has($name), 'border-[#dc3545]' => $errors->has($name)])>
            <x-tw.icon :name="$icon" class="size-4" />
        </span>
    </div>
    @error($name)<p class="mt-1 text-[80%] text-[#dc3545]">{{ $message }}</p>@enderror
</div>
