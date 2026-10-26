<footer class="border-t border-paper/15 bg-ink text-paper">
    <div class="mx-auto max-w-[1400px] px-5 py-8 sm:px-8 lg:px-12 lg:py-10">
        <div class="flex flex-col gap-7 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-lg">
                <a href="{{ route('home') }}" class="font-serif text-2xl tracking-[-.04em]">Casa das Casinhas</a>
                <p class="mt-2 text-sm leading-6 text-paper/65">Casas e paisagens de madeira montadas e pintadas à mão, produzidas sob encomenda.</p>
            </div>

            <nav class="flex flex-wrap gap-x-6 gap-y-3 text-sm sm:gap-x-8" aria-label="Links do rodapé">
                <a href="{{ route('home') }}" class="hover:text-clay">Início</a>
                <a href="{{ route('store') }}" class="hover:text-clay">Produtos</a>
                <a href="{{ route('about') }}" class="hover:text-clay">Sobre nós</a>
                <a href="{{ route('production') }}" class="hover:text-clay">Como produzimos</a>

                @if (auth()->user()?->role === \App\Enums\Role::ADMIN)
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-clay">Administração</a>
                @else
                    <a href="{{ route('cart') }}" class="hover:text-clay">Carrinho</a>
                    @auth
                        <a href="{{ route('orders.index') }}" class="hover:text-clay">Pedidos</a>
                        <a href="{{ route('dashboard') }}" class="hover:text-clay">Perfil</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-clay">Entrar</a>
                    @endauth
                @endif
            </nav>
        </div>

        <div class="mt-7 flex flex-col gap-2 border-t border-paper/15 pt-5 text-sm text-paper/50 sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ date('Y') }} Casa das Casinhas</span>
            <span>Produção artesanal em até 15 dias</span>
        </div>
    </div>
</footer>
