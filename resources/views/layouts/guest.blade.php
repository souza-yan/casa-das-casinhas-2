<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#ffffff">
    <title>{{ $title ?? config('app.name', 'Casa das Casinhas') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600|marcellus:400" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])<tallstackui:script />@livewireStyles
</head>
<body class="min-h-screen bg-paper font-sans text-ink antialiased">
    <x-toast />
    <main class="page-enter grid min-h-screen lg:grid-cols-[.92fr_1.08fr]">
        <section class="relative hidden min-h-screen overflow-hidden bg-ink text-paper lg:block">
            <img src="{{ asset('images/vila-completa-1200x1500.png') }}" alt="" aria-hidden="true" class="absolute inset-0 size-full object-cover object-center">
            <div class="absolute inset-0 bg-ink/60"></div>
            <div class="relative flex min-h-screen flex-col p-12 xl:p-16">
                <a href="{{ route('home') }}" class="w-fit font-serif text-2xl tracking-[-.04em]">Casa das Casinhas</a>
                <div class="flex flex-1 items-center py-12">
                    <div class="max-w-lg">
                        <h1 class="font-serif text-6xl leading-[1.02] tracking-[-.04em]">Cada peça guarda uma história.</h1>
                        <p class="mt-5 max-w-md text-base leading-7 text-paper/90">Entre para acompanhar de perto o caminho da sua encomenda.</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="flex items-center justify-center px-5 py-12 sm:px-8"><div class="w-full max-w-md"><a href="{{ route('home') }}" class="mb-12 block border-b border-ink/10 pb-5 font-serif text-2xl tracking-[-.04em] lg:hidden">Casa das Casinhas</a>@yield('content')</div></section>
    </main>
    @livewireScripts
</body>
</html>
