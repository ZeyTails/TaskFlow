@props([
    'workspace',
    'size' => 'md',
])

@php
    $theme = $workspace->theme();
    $sizes = [
        'sm' => 'h-9 w-9',
        'md' => 'h-10 w-10',
        'lg' => 'h-11 w-11',
    ];
    $iconSizes = [
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-5 w-5',
    ];
@endphp

<div {{ $attributes->class(['inline-flex shrink-0 items-center justify-center rounded-xl border', $theme['icon'], $sizes[$size] ?? $sizes['md']]) }}>
    @svg('icon-' . ($workspace->icon_key ?? 'briefcase'), $iconSizes[$size] ?? $iconSizes['md'])
</div>
