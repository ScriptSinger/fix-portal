@props(['type' => 'info'])
<div role="{{ $type === 'error' ? 'alert' : 'status' }}" @class(['mb-6 border-l-4 px-4 py-3 text-[14px] leading-6', 'border-brand-400 bg-brand-50 text-black' => $type === 'info', 'border-red-600 bg-red-50 text-black' => $type === 'error', 'border-success bg-[#eaf6ec] text-black' => $type === 'success'])>{{ $slot }}</div>
