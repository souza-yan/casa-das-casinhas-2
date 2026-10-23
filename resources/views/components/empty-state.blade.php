@props(['title', 'action' => null, 'href' => null])
<div {{ $attributes->class(['border border-dashed border-clay px-6 py-12 text-center']) }}><h2 class="font-serif text-2xl">{{ $title }}</h2><div class="mx-auto mt-3 max-w-md text-base text-ink/60">{{ $slot }}</div>@if($action && $href)<a href="{{ $href }}" class="mt-5 inline-block bg-terracotta px-5 py-3 text-paper">{{ $action }}</a>@endif</div>
