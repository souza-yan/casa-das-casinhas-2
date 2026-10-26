@props(['href' => null, 'type' => 'button'])

@php
    $classes = 'inline-flex min-h-12 items-center justify-center rounded-full border border-ink bg-transparent px-5 py-3 text-sm font-semibold text-ink transition-colors hover:bg-ink hover:text-paper disabled:cursor-not-allowed disabled:opacity-50';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
