@props(['eyebrow' => null, 'title'])
<header {{ $attributes->class(['border-b border-[#261d17]/15 pb-8']) }}>@if($eyebrow)<p class="text-sm font-semibold uppercase tracking-[.2em] text-[#8b5e3c]">{{ $eyebrow }}</p>@endif<h1 class="mt-3 font-serif text-4xl sm:text-5xl">{{ $title }}</h1>@if($slot->isNotEmpty())<div class="mt-4 text-[#261d17]/60">{{ $slot }}</div>@endif</header>
