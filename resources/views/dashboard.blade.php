<x-layouts.public>
    <div class="mx-auto max-w-[1120px] px-5 py-12 sm:px-8 lg:py-20">
        <header class="border-b border-ink/10 pb-8">
            <p class="text-[10px] font-semibold uppercase tracking-[.22em] text-clay">Minha conta</p>
            <div class="mt-4 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="grid size-14 shrink-0 place-items-center rounded-full bg-bark font-serif text-2xl font-semibold text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                    <div><h1 class="font-serif text-4xl font-semibold tracking-[-.04em] sm:text-5xl">Olá, {{ auth()->user()->name }}</h1><p class="mt-1 text-sm text-ink/50">Acompanhe suas encomendas e dados da conta.</p></div>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-full border border-ink/15 px-5 py-2.5 text-xs font-semibold transition hover:border-bark">Sair da conta</button></form>
            </div>
        </header>

        <div class="mt-8 grid gap-5 lg:grid-cols-[.72fr_1.28fr]">
            <aside class="space-y-5">
                <section class="border border-ink/10 bg-paper p-6">
                    <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-clay">Dados pessoais</p>
                    <dl class="mt-5 grid gap-4 text-sm"><div><dt class="text-xs text-ink/40">Nome</dt><dd class="mt-1 font-medium">{{ auth()->user()->name }}</dd></div><div><dt class="text-xs text-ink/40">E-mail</dt><dd class="mt-1 break-all font-medium">{{ auth()->user()->email }}</dd></div><div><dt class="text-xs text-ink/40">Cliente desde</dt><dd class="mt-1 font-medium">{{ auth()->user()->created_at->format('d/m/Y') }}</dd></div></dl>
                </section>

                <section class="border border-ink/10 bg-paper p-6">
                    <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-clay">Resumo</p>
                    <div class="mt-4 flex items-end justify-between"><div><strong class="font-serif text-5xl font-semibold text-bark">{{ $ordersCount }}</strong><span class="ml-2 text-sm text-ink/50">{{ $ordersCount === 1 ? 'pedido' : 'pedidos' }}</span></div><a href="{{ route('orders.index') }}" class="text-xs font-semibold text-clay">Ver todos →</a></div>
                </section>
            </aside>

            <section class="border border-ink/10 bg-paper p-6 sm:p-8">
                <div class="flex items-center justify-between gap-4"><div><p class="text-[10px] font-semibold uppercase tracking-[.18em] text-clay">Histórico</p><h2 class="mt-1 font-serif text-3xl font-semibold">Pedidos recentes</h2></div><a href="{{ route('store') }}" class="hidden rounded-full bg-bark px-5 py-2.5 text-xs font-semibold text-white sm:block">Ver coleção</a></div>

                <div class="mt-6 divide-y divide-ink/10 border-y border-ink/10">
                    @forelse ($latestOrders as $order)
                        <a href="{{ route('orders.show', $order) }}" class="grid grid-cols-[1fr_auto] gap-3 py-4 transition hover:bg-cream/60 sm:grid-cols-[.8fr_1fr_auto] sm:items-center sm:px-2"><div><span class="text-[9px] uppercase tracking-[.12em] text-ink/40">Pedido</span><strong class="mt-1 block text-sm">#{{ $order->id }}</strong></div><div class="hidden sm:block"><span class="text-[9px] uppercase tracking-[.12em] text-ink/40">Status</span><div class="mt-1"><x-status-badge :status="$order->status" /></div></div><div class="text-right"><strong class="block text-sm">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong><span class="mt-1 block text-[10px] text-ink/40">{{ $order->created_at->format('d/m/Y') }}</span></div></a>
                    @empty
                        <div class="py-10 text-center"><p class="font-serif text-2xl">Nenhum pedido por aqui ainda.</p><p class="mt-2 text-xs text-ink/45">Sua primeira peça artesanal pode estar na coleção.</p><a href="{{ route('store') }}" class="mt-5 inline-block rounded-full bg-bark px-5 py-2.5 text-xs font-semibold text-white">Conhecer a loja</a></div>
                    @endforelse
                </div>
                @if ($latestOrders->isNotEmpty())<a href="{{ route('orders.index') }}" class="mt-5 block text-right text-xs font-semibold text-clay">Acompanhar todos os pedidos →</a>@endif
            </section>
        </div>
    </div>
</x-layouts.public>
