@props(['status', 'size' => 'default'])

<span {{ $attributes->class([
    'inline-flex rounded-full bg-stone-100 font-semibold text-stone-700',
    'px-5 py-2 text-base' => $size === 'large',
    'px-3 py-1 text-sm' => $size !== 'large',
]) }}>{{ method_exists($status, 'label') ? $status->label() : $status }}</span>
