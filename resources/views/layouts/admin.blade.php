<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#1a1315">
    <title>{{ $title ?? 'Administração — Casa das Casinhas' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600|marcellus:400" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])<tallstackui:script />@livewireStyles
</head>
<body class="admin-shell min-h-screen bg-cream font-sans text-ink antialiased">
    <x-toast />
    <div class="page-enter min-h-screen lg:grid lg:grid-cols-[260px_1fr]"><x-admin-sidebar /><main class="min-w-0 px-5 py-8 sm:px-8 lg:px-10 lg:py-10">{{ $slot }}</main></div>
    @livewireScripts
</body>
</html>
