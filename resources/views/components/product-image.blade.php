@props(['product' => null, 'variant' => 'card', 'size' => null, 'preview' => false, 'src' => null, 'alt' => null])

@php
    $imageUrl = $src ?? $product?->image_url;
    $imageAlt = $alt ?? $product?->name ?? 'Imagem do produto';
    $isDetail = $variant === 'detail';
    $isAdminPreview = $variant === 'admin' && $preview;
    $isCartThumb = $variant === 'thumb' && $size === 'cart';
    $containerClass = match ($variant) {
        'detail' => 'aspect-[4/5] w-full overflow-hidden rounded-sm bg-clay',
        'thumb' => $isCartThumb ? 'h-24 w-24 shrink-0 overflow-hidden rounded-sm bg-clay' : 'h-20 w-20 shrink-0 overflow-hidden rounded-sm bg-clay',
        'admin' => $isAdminPreview ? 'aspect-[4/5] w-full overflow-hidden rounded-sm bg-clay' : 'h-14 w-14 shrink-0 overflow-hidden rounded-sm bg-clay',
        default => 'aspect-[4/5] w-full overflow-hidden bg-clay',
    };
    $imageClass = match ($variant) {
        'detail' => 'block h-full w-full object-contain',
        'thumb' => 'block h-full w-full object-cover object-center',
        'admin' => $isAdminPreview ? 'block h-full w-full object-contain' : 'block h-full w-full object-cover object-center',
        default => 'block h-full w-full object-cover object-center',
    };
    $width = match ($variant) {
        'detail', 'card' => 4,
        'thumb' => $isCartThumb ? 96 : 80,
        'admin' => $isAdminPreview ? 4 : 56,
        default => 4,
    };
    $height = match ($variant) {
        'detail', 'card' => 5,
        'thumb' => $isCartThumb ? 96 : 80,
        'admin' => $isAdminPreview ? 5 : 56,
        default => 5,
    };
@endphp

<div {{ $attributes->class($containerClass) }}>
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="{{ $imageClass }}" width="{{ $width }}" height="{{ $height }}" @unless($isDetail) loading="lazy" @endunless>
    @endif
</div>
