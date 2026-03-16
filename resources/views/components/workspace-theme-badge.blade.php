@props([
    'workspace',
])

@php
    $theme = $workspace->theme();
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-medium', $theme['badge']]) }}>
    {{ $theme['label'] }}
</span>
