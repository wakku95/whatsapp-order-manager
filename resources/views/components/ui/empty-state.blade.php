@props([
    'title'       => 'Nothing here yet',
    'description' => null,
    'icon'        => 'default', // 'category' | 'product' | 'default'
])

<div class="text-center py-16 px-4">
    {{-- Icon --}}
    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[#F1F5F9] text-[#94A3B8] mb-4">
        @if($icon === 'category')
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        @elseif($icon === 'product')
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        @else
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>

    {{-- Title --}}
    <h3 class="text-base font-semibold text-[#0F172A] mb-1">{{ $title }}</h3>

    {{-- Description --}}
    @if($description)
        <p class="text-sm text-[#64748B] max-w-xs mx-auto">{{ $description }}</p>
    @endif

    {{-- Optional slot for a CTA button --}}
    @if($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
