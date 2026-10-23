<x-layouts.public>
    <div class="mx-auto max-w-[1120px] px-5 py-12 sm:px-8 lg:py-20">
        <x-page-header eyebrow="Minha conta" title="Olá, {{ auth()->user()->name }}">Acesse seus pedidos e acompanhe cada encomenda.</x-page-header>
        <div class="mt-8 grid gap-4 sm:grid-cols-2"><a href="{{ route('orders.index') }}" class="group border border-ink/10 bg-paper p-6 transition hover:border-bark"><span class="font-serif text-2xl font-semibold">Meus pedidos</span><span class="mt-8 block text-sm font-semibold text-clay">Acompanhar encomendas →</span></a><a href="{{ route('store') }}" class="group border border-ink/10 bg-paper p-6 transition hover:border-bark"><span class="font-serif text-2xl font-semibold">Continuar comprando</span><span class="mt-8 block text-sm font-semibold text-clay">Ver coleção →</span></a></div>
    </div>
</x-layouts.public>
