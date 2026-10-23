<footer class="border-t border-paper/15 bg-ink text-paper">
    <div class="mx-auto max-w-[1400px] px-5 py-16 sm:px-8 lg:px-12 lg:py-20">
        <div class="grid gap-14 lg:grid-cols-[1.4fr_1fr] lg:gap-24">
            <div>
                <a href="{{ route('home') }}" class="font-serif text-4xl tracking-[-.04em]">Casa das Casinhas</a>
                <p class="mt-6 max-w-xl text-base leading-8 text-paper/65">Casas e paisagens de madeira montadas e pintadas à mão, produzidas sob encomenda para levar cor e memória aos espaços.</p>
                <a href="{{ route('store') }}" class="mt-8 inline-flex min-h-12 items-center justify-center rounded-sm border border-paper px-6 text-sm font-semibold uppercase tracking-[.1em] transition hover:bg-paper hover:text-ink">Ver produtos</a>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h2 class="text-sm font-semibold text-paper/50">Navegação</h2>
                    <nav class="mt-5 grid gap-3 text-base" aria-label="Navegação do rodapé">
                        <a href="{{ route('home') }}#inicio" class="w-fit hover:text-clay">Início</a>
                        <a href="{{ route('store') }}" class="w-fit hover:text-clay">Produtos</a>
                        <a href="{{ route('home') }}#sobre" class="w-fit hover:text-clay">Sobre nós</a>
                        <a href="{{ route('home') }}#como-produzimos" class="w-fit hover:text-clay">Como produzimos</a>
                    </nav>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-paper/50">Sua conta</h2>
                    <div class="mt-5 grid gap-3 text-base">
                        @if (auth()->user()?->role === \App\Enums\Role::ADMIN)
                            <a href="{{ route('admin.dashboard') }}" class="w-fit hover:text-clay">Administração</a>
                            <a href="{{ route('store') }}" class="w-fit hover:text-clay">Visualizar produtos</a>
                        @else
                            <a href="{{ route('cart') }}" class="w-fit hover:text-clay">Carrinho</a>
                            @auth
                                <a href="{{ route('orders.index') }}" class="w-fit hover:text-clay">Pedidos</a>
                                <a href="{{ route('dashboard') }}" class="w-fit hover:text-clay">Perfil</a>
                            @else
                                <a href="{{ route('login') }}" class="w-fit hover:text-clay">Entrar</a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-16 flex flex-col gap-3 border-t border-paper/15 pt-6 text-sm text-paper/50 sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ date('Y') }} Casa das Casinhas</span>
            <span>Produção artesanal em até 15 dias · Feito à mão no Brasil</span>
        </div>
    </div>
</footer>
