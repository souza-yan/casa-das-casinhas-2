<x-layouts.public>
    <div class="mx-auto max-w-5xl px-5 py-14 sm:px-8">
        <x-page-header eyebrow="Minha conta" title="Olá, {{ auth()->user()->name }}">Acesse seus pedidos e acompanhe cada encomenda.</x-page-header>
        <div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('orders.index') }}" class="bg-blue-700 px-5 py-3 font-semibold text-white">Meus pedidos</a><a href="{{ route('store') }}" class="border border-slate-300 bg-white px-5 py-3 font-semibold">Continuar comprando</a>@if(auth()->user()->role === \App\Enums\Role::ADMIN)<a href="{{ route('admin.dashboard') }}" class="border border-blue-300 bg-blue-50 px-5 py-3 font-semibold text-blue-800">Administração</a>@endif</div>
    </div>
</x-layouts.public>
