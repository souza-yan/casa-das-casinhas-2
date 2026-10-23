<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $title ?? 'Casa das Casinhas' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600|marcellus:400" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>
<body class="min-h-screen bg-paper font-sans text-ink antialiased">
    <x-toast />
    <x-navbar />
    <main id="inicio">{{ $slot }}</main>
    <x-footer />
    @livewireScripts
</body>
</html>
