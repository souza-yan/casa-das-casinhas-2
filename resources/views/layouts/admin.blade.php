<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Administração — Casa das Casinhas' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>
<body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[240px_1fr]">
        <aside class="border-b border-stone-200 bg-white px-5 py-5 lg:min-h-screen lg:border-b-0 lg:border-r lg:px-6 lg:py-8">
            <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl text-[#4b2d1b]">Casa das Casinhas</a>
            <nav class="mt-8 flex gap-2 lg:grid" aria-label="Administração">
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 text-sm font-medium hover:bg-stone-100">Visão geral</a>
                <a href="{{ route('admin.products.index') }}" class="bg-[#4b2d1b] px-3 py-2 text-sm font-medium text-white">Produtos</a>
                <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 text-sm font-medium hover:bg-stone-100">Pedidos</a>
            </nav>
        </aside>
        <main class="min-w-0 px-5 py-8 sm:px-8 lg:px-10 lg:py-10">{{ $slot }}</main>
    </div>
    @livewireScripts
</body>
</html>
