<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4b2b18">
    <title>{{ $title ?? 'Casa das Casinhas' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|instrument-sans:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <tallstackui:script />
    @livewireStyles
</head>
<body class="min-h-screen bg-cream font-sans text-ink antialiased">
    <x-navbar />
    <main>{{ $slot }}</main>
    <x-footer />
    @livewireScripts
</body>
</html>
