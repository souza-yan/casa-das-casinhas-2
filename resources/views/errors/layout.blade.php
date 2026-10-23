<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-ink antialiased">
    <main class="grid min-h-screen place-items-center px-6 py-16">
        <section class="w-full max-w-xl border border-ink/10 bg-paper p-8 text-center shadow-sm sm:p-12">
            <p class="text-sm font-bold uppercase tracking-[0.24em] text-bark">Erro @yield('code')</p>
            <h1 class="mt-4 font-serif text-4xl sm:text-6xl">@yield('message')</h1>
            <p class="mx-auto mt-4 max-w-md text-base leading-6 text-stone-600">@yield('description')</p>
            <a href="{{ route('home') }}" class="mt-8 inline-flex rounded-full bg-bark px-6 py-3 text-sm font-semibold text-paper transition hover:bg-bark/90">
                Voltar para a página inicial
            </a>
        </section>
    </main>
</body>
</html>
