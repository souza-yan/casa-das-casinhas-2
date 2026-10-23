<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#4b2b18">
    <title>{{ $title ?? config('app.name', 'Casa das Casinhas') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|instrument-sans:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])<tallstackui:script />@livewireStyles
</head>
<body class="min-h-screen bg-cream font-sans text-ink antialiased">
    <x-toast />
    <main class="grid min-h-screen lg:grid-cols-[.92fr_1.08fr]">
        <section class="relative hidden min-h-screen overflow-hidden bg-bark text-white lg:block">
            <img src="{{ asset('images/products/casas-da-vila.png') }}" alt="Painel artesanal Casas da Vila" class="absolute inset-0 size-full object-cover opacity-70">
            <div class="absolute inset-0 bg-bark/55"></div>
            <div class="relative flex h-full min-h-screen flex-col justify-between p-12 xl:p-16"><a href="{{ route('home') }}" class="w-fit font-serif text-3xl font-semibold italic tracking-[-.05em]">Casa das Casinhas</a><div class="max-w-lg"><p class="text-[10px] font-semibold uppercase tracking-[.25em] text-sand">Arte em madeira</p><h1 class="mt-4 font-serif text-6xl font-semibold leading-[.92] tracking-[-.04em]">Cada peça guarda uma história.</h1><p class="mt-5 max-w-md text-sm leading-7 text-white/70">Entre para acompanhar de perto o caminho da sua encomenda.</p></div><p class="text-xs text-white/55">Produção artesanal sob demanda</p></div>
        </section>
        <section class="flex items-center justify-center px-5 py-12 sm:px-8"><div class="w-full max-w-md"><a href="{{ route('home') }}" class="mb-10 block font-serif text-3xl font-semibold italic tracking-[-.05em] text-bark lg:hidden">Casa das Casinhas</a>@yield('content')</div></section>
    </main>
    @livewireScripts
</body>
</html>
