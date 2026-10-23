<aside class="border-b border-white/10 bg-ink px-5 py-5 text-white lg:min-h-screen lg:border-b-0 lg:px-7 lg:py-8">
    <div class="flex items-center justify-between lg:block"><a href="{{ route('admin.dashboard') }}" class="font-serif text-2xl font-semibold italic tracking-[-.04em]">Casa das Casinhas</a><span class="rounded-full bg-white/10 px-3 py-1 text-[9px] font-semibold uppercase tracking-[.18em] lg:mt-2 lg:inline-block">Administração</span></div>
    <nav class="mt-6 flex gap-2 overflow-x-auto lg:mt-10 lg:grid" aria-label="Administração">
        @foreach ([['admin.dashboard', 'admin.dashboard', 'Visão geral'], ['admin.products.index', 'admin.products.*', 'Produtos'], ['admin.orders.index', 'admin.orders.*', 'Pedidos']] as [$route, $pattern, $label])
            <a href="{{ route($route) }}" @class(['rounded-sm px-4 py-3 text-sm font-medium transition', 'bg-white text-ink' => request()->routeIs($pattern), 'text-white/65 hover:bg-white/10 hover:text-white' => ! request()->routeIs($pattern)])>{{ $label }}</a>
        @endforeach
        <a href="{{ route('store') }}" class="flex items-center gap-2 rounded-sm px-4 py-3 text-sm font-medium text-white/65 transition hover:bg-white/10 hover:text-white"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>Visualizar loja</a>
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="mt-8 hidden border-t border-white/10 px-4 pt-6 lg:block">@csrf<button class="text-sm text-white/60 transition hover:text-white">Sair da conta</button></form>
</aside>
