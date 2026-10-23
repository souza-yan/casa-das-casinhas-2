<footer class="border-t border-ink/10 bg-paper">
    <div class="bg-bark px-5 py-8 text-white sm:px-8"><div class="mx-auto flex max-w-[1320px] flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-serif text-2xl font-semibold">Peças feitas devagar, para ficar por muito tempo.</p><p class="mt-1 text-sm text-white/65">Conheça a coleção de arte em madeira.</p></div><a href="{{ route('store') }}" class="w-fit rounded-full bg-paper px-6 py-3 text-sm font-semibold text-bark transition hover:bg-sand">Ver coleção →</a></div></div>
    <div class="mx-auto grid max-w-[1320px] gap-10 px-5 py-12 sm:grid-cols-2 sm:px-8 lg:grid-cols-[1.5fr_1fr_1fr]">
        <div><p class="font-serif text-3xl font-semibold italic tracking-[-.04em] text-bark">Casa das Casinhas</p><p class="mt-3 max-w-sm text-sm leading-6 text-ink/55">Painéis artesanais em madeira, produzidos sob demanda e pintados à mão.</p></div>
        <div><h2 class="text-[11px] font-semibold uppercase tracking-[.2em] text-clay">Explore</h2><div class="mt-4 grid gap-2 text-sm"><a href="{{ route('home') }}">Início</a><a href="{{ route('store') }}">Loja</a><a href="{{ route('home') }}#processo">Nosso fazer</a></div></div>
        <div><h2 class="text-[11px] font-semibold uppercase tracking-[.2em] text-clay">Sua compra</h2><div class="mt-4 grid gap-2 text-sm"><a href="{{ route('cart') }}">Carrinho</a>@auth<a href="{{ route('orders.index') }}">Acompanhar pedidos</a>@else<a href="{{ route('login') }}">Entrar na conta</a>@endauth</div></div>
    </div>
    <div class="mx-auto flex max-w-[1320px] flex-col gap-2 border-t border-ink/10 px-5 py-5 text-xs text-ink/45 sm:flex-row sm:justify-between sm:px-8"><span>© {{ date('Y') }} Casa das Casinhas</span><span>Feito à mão no Brasil</span></div>
</footer>
