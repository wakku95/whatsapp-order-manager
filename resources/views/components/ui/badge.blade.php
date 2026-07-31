@props([
    'active'       => true,
    'activeText'   => 'Active',
    'inactiveText' => 'Inactive',
    'size'         => 'sm', // sm | xs
])

@php
    $baseClasses = 'inline-flex items-center rounded-full font-semibold';
    $sizeClasses = $size === 'xs'
        ? 'px-2 py-0.5 text-[10px]'
        : 'px-2.5 py-0.5 text-xs';

    $colorClasses = $active
        ? 'bg-green-100 text-green-700'
        : 'bg-[#F1F5F9] text-[#64748B]';
@endphp

<span {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $colorClasses"]) }}>
    {{ $active ? $activeText : $inactiveText }}
</span>
