@props(['status'])
<span {{ $attributes->class(['inline-flex rounded-full bg-stone-100 px-3 py-1 text-sm font-semibold text-stone-700']) }}>{{ method_exists($status, 'label') ? $status->label() : $status }}</span>
