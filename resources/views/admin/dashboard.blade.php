<x-layouts.admin title="Dashboard — Administração">
    <x-page-header eyebrow="Administração" title="Visão geral">Indicadores reais para validar a operação da loja.</x-page-header>
    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('admin.products.index') }}" class="border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Produtos ativos</p><strong class="mt-2 block text-3xl text-blue-800">{{ $indicators['products'] }}</strong></a>
        <a href="{{ route('admin.orders.index') }}" class="border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Pedidos totais</p><strong class="mt-2 block text-3xl text-blue-800">{{ $indicators['orders'] }}</strong></a>
        <a href="{{ route('admin.orders.index') }}" class="border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Aguardando pagamento</p><strong class="mt-2 block text-3xl text-blue-800">{{ $indicators['pending'] }}</strong></a>
    </div>
    <div class="mt-8 border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900"><strong>Ambiente de desenvolvimento.</strong> As integrações externas devem permanecer configuradas nos respectivos Sandboxes.</div>
</x-layouts.admin>
