<x-layouts.admin title="Dashboard — Administração">
    <x-page-header eyebrow="Administração" title="Visão geral">Indicadores da operação da loja.</x-page-header>
    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('admin.products.index') }}" class="group border border-ink/10 bg-paper p-6 transition hover:border-bark"><p class="text-xs font-semibold uppercase tracking-[.14em] text-ink/45">Produtos ativos</p><strong class="mt-5 block font-serif text-5xl text-bark">{{ $indicators['products'] }}</strong><span class="mt-5 block text-xs text-clay">Gerenciar catálogo →</span></a>
        <a href="{{ route('admin.orders.index') }}" class="group border border-ink/10 bg-paper p-6 transition hover:border-bark"><p class="text-xs font-semibold uppercase tracking-[.14em] text-ink/45">Pedidos totais</p><strong class="mt-5 block font-serif text-5xl text-bark">{{ $indicators['orders'] }}</strong><span class="mt-5 block text-xs text-clay">Ver pedidos →</span></a>
        <a href="{{ route('admin.orders.index') }}" class="group border border-ink/10 bg-paper p-6 transition hover:border-bark"><p class="text-xs font-semibold uppercase tracking-[.14em] text-ink/45">Aguardando pagamento</p><strong class="mt-5 block font-serif text-5xl text-bark">{{ $indicators['pending'] }}</strong><span class="mt-5 block text-xs text-clay">Acompanhar →</span></a>
    </div>
    <div class="mt-6 border border-amber-800/15 bg-amber-50 p-4 text-sm text-amber-950"><strong>Ambiente de desenvolvimento.</strong> As integrações externas devem permanecer nos respectivos Sandboxes.</div>
</x-layouts.admin>
