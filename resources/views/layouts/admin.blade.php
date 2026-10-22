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
        <x-admin-sidebar />
        <main class="min-w-0 px-5 py-8 sm:px-8 lg:px-10 lg:py-10">{{ $slot }}</main>
    </div>
    @livewireScripts
</body>
</html>
