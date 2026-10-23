<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Casa das Casinhas') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|lora:500,600" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>
<body class="min-h-screen bg-[#f8f4ed] font-sans text-[#2a1b13] antialiased">
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="hidden bg-[#4a2a16] p-12 text-[#fffdf8] lg:flex lg:flex-col lg:justify-between">
            <a href="{{ route('home') }}" class="font-serif text-2xl tracking-[-0.04em]">Casa das Casinhas</a>
            <div class="max-w-lg">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#e8d3bd]">Feito com afeto</p>
                <h1 class="mt-5 font-serif text-5xl leading-tight">Artesanato que encontra um lugar especial na sua casa.</h1>
            </div>
            <p class="text-sm text-[#fffdf8]/60">Peças produzidas sob demanda.</p>
        </section>
        <section class="flex items-center justify-center px-5 py-12 sm:px-8">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-10 block font-serif text-2xl tracking-[-0.04em] lg:hidden">Casa das Casinhas</a>
                @yield('content')
            </div>
        </section>
    </main>
    @livewireScripts
</body>
</html>
