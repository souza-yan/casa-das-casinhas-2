<div class="mx-auto max-w-[1120px] px-5 py-12 sm:px-8 lg:py-20">
    <header class="border-b border-ink/10 pb-8">
        <p class="text-sm font-semibold uppercase tracking-[.22em] text-clay">Minha conta</p>
        <div class="mt-4 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="grid size-14 shrink-0 place-items-center rounded-full bg-bark font-serif text-2xl font-semibold text-paper">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                <div><h1 class="font-serif text-4xl font-semibold tracking-[-.04em] sm:text-6xl">Olá, {{ auth()->user()->name }}</h1><p class="mt-1 text-base text-ink/50">Acompanhe suas encomendas e gerencie os dados da conta.</p></div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <button type="button" wire:click="openEditor" class="editorial-button h-12 w-full min-h-0 px-5 sm:w-44">Editar perfil</button>
                <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-44">@csrf<button class="h-12 w-full border border-ink px-5 text-sm font-semibold uppercase tracking-[.08em] transition hover:bg-ink hover:text-paper">Sair da conta</button></form>
            </div>
        </div>
    </header>

    <div class="mt-8 grid gap-5 lg:grid-cols-[.72fr_1.28fr]">
        <aside>
            <section class="border border-ink/10 bg-paper p-6">
                <p class="text-sm font-semibold uppercase tracking-[.18em] text-clay">Dados pessoais</p>
                <dl class="mt-5 grid gap-4 text-base"><div><dt class="text-sm text-ink/40">Nome</dt><dd class="mt-1 font-medium">{{ auth()->user()->name }}</dd></div><div><dt class="text-sm text-ink/40">E-mail</dt><dd class="mt-1 break-all font-medium">{{ auth()->user()->email }}</dd></div><div><dt class="text-sm text-ink/40">Cliente desde</dt><dd class="mt-1 font-medium">{{ auth()->user()->created_at->format('d/m/Y') }}</dd></div><div class="border-t border-ink/10 pt-4"><dt class="text-sm text-ink/40">Endereço padrão</dt>@if (auth()->user()->zip_code)<dd class="mt-1 font-medium">{{ auth()->user()->street }}, {{ auth()->user()->number }}@if (auth()->user()->complement) · {{ auth()->user()->complement }}@endif<br>{{ auth()->user()->district }} · {{ auth()->user()->city }}/{{ auth()->user()->state }}<br>CEP {{ auth()->user()->zip_code }}</dd>@else<dd class="mt-1 text-sm text-ink/50">Nenhum endereço cadastrado.</dd>@endif</div></dl>
            </section>
        </aside>

        <section class="border border-ink/10 bg-paper p-6 sm:p-8">
            <div><p class="text-sm font-semibold uppercase tracking-[.18em] text-clay">Histórico</p><h2 class="mt-1 font-serif text-3xl font-semibold">Pedidos recentes</h2></div>

            <div class="mt-6 divide-y divide-ink/10 border-y border-ink/10">
                @forelse ($latestOrders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="grid grid-cols-[1fr_auto] gap-3 py-4 transition hover:bg-cream/60 sm:grid-cols-[.8fr_1fr_auto] sm:items-center sm:px-2"><div><span class="text-sm uppercase tracking-[.12em] text-ink/40">Pedido</span><strong class="mt-1 block text-base">#{{ $order->id }}</strong></div><div class="hidden sm:block"><span class="text-sm uppercase tracking-[.12em] text-ink/40">Status</span><div class="mt-1"><x-status-badge :status="$order->status" /></div></div><div class="text-right"><strong class="block text-base">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong><span class="mt-1 block text-sm text-ink/40">{{ $order->created_at->format('d/m/Y') }}</span></div></a>
                @empty
                    <div class="py-12 text-center"><p class="font-serif text-2xl">Nenhum pedido por aqui ainda.</p><p class="mt-2 text-sm text-ink/45">Quando você fizer uma compra, poderá acompanhar tudo pela sua conta.</p></div>
                @endforelse
            </div>
            @if ($latestOrders->isNotEmpty())<a href="{{ route('orders.index') }}" class="mt-5 block text-right text-sm font-semibold text-clay">Acompanhar todos os pedidos →</a>@endif
        </section>
    </div>

    <x-modal wire="profileModal" size="2xl" center scrollable title="Editar perfil">
        <form wire:submit="updateProfile" class="grid gap-5">
            <div class="grid gap-4 sm:grid-cols-2"><x-form-field label="Nome" name="name" wire:model="name" required /><x-form-field label="E-mail" name="email" type="email" wire:model="email" required /></div>
            <div class="border-t border-ink/10 pt-5"><h2 class="font-serif text-xl font-semibold">Endereço padrão</h2><p class="mt-1 text-sm text-ink/45">Será preenchido automaticamente na sua próxima compra.</p></div>
            <div class="grid gap-4 sm:grid-cols-2"><x-form-field label="Telefone" name="phone" wire:model="phone" /><x-form-field label="CEP" name="zip_code" wire:model="zip_code" /></div>
            <div class="grid gap-4 sm:grid-cols-[1fr_9rem]"><x-form-field label="Rua" name="street" wire:model="street" /><x-form-field label="Número" name="number" wire:model="number" /></div>
            <x-form-field label="Complemento" name="complement" wire:model="complement" />
            <div class="grid gap-4 sm:grid-cols-3"><x-form-field label="Bairro" name="district" wire:model="district" /><x-form-field label="Cidade" name="city" wire:model="city" /><x-form-field label="UF" name="state" maxlength="2" wire:model="state" /></div>
            <div class="border-t border-ink/10 pt-5"><h2 class="font-serif text-xl font-semibold">Alterar senha</h2><p class="mt-1 text-sm text-ink/45">Deixe estes campos vazios para manter a senha atual.</p></div>
            <x-form-field label="Senha atual" name="current_password" type="password" wire:model="current_password" autocomplete="current-password" />
            <div class="grid gap-4 sm:grid-cols-2"><x-form-field label="Nova senha" name="password" type="password" wire:model="password" autocomplete="new-password" /><x-form-field label="Confirmar nova senha" name="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" /></div>
            <div class="flex flex-col-reverse gap-2 border-t border-ink/10 pt-5 sm:flex-row sm:justify-end"><button type="button" wire:click="cancelEdit" class="rounded-full border border-ink/20 px-5 py-2.5 text-sm font-semibold">Cancelar</button><button type="submit" wire:loading.attr="disabled" class="rounded-full bg-bark px-6 py-2.5 text-sm font-semibold text-paper disabled:opacity-50"><span wire:loading.remove wire:target="updateProfile">Salvar alterações</span><span wire:loading wire:target="updateProfile">Salvando...</span></button></div>
        </form>
    </x-modal>
</div>
