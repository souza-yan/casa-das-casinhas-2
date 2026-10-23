<div class="mx-auto max-w-[920px] px-5 py-12 sm:px-8 lg:py-20">
    <header class="border-b border-ink/10 pb-8">
        <p class="text-[10px] font-semibold uppercase tracking-[.22em] text-clay">Minha conta</p>
        <div class="mt-4 flex items-center gap-4">
            <div class="grid size-14 shrink-0 place-items-center rounded-full bg-bark font-serif text-2xl font-semibold text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
            <div><h1 class="font-serif text-4xl font-semibold tracking-[-.04em] sm:text-5xl">Olá, {{ auth()->user()->name }}</h1><p class="mt-1 text-sm text-ink/50">Gerencie suas informações e acompanhe sua conta.</p></div>
        </div>
    </header>

    <div class="mt-8 grid gap-5 md:grid-cols-[1.15fr_.85fr]">
        <section class="border border-ink/10 bg-paper p-6 sm:p-8">
            <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-clay">Dados pessoais</p>
            <dl class="mt-6 grid gap-5 text-sm sm:grid-cols-2"><div><dt class="text-xs text-ink/40">Nome</dt><dd class="mt-1 font-medium">{{ auth()->user()->name }}</dd></div><div><dt class="text-xs text-ink/40">E-mail</dt><dd class="mt-1 break-all font-medium">{{ auth()->user()->email }}</dd></div><div><dt class="text-xs text-ink/40">Cliente desde</dt><dd class="mt-1 font-medium">{{ auth()->user()->created_at->format('d/m/Y') }}</dd></div><div><dt class="text-xs text-ink/40">Pedidos</dt><dd class="mt-1 font-medium">{{ $ordersCount }}</dd></div></dl>
        </section>

        <section class="flex flex-col justify-between border border-ink/10 bg-paper p-6 sm:p-8">
            @if ($ordersCount === 0)
                <div><p class="font-serif text-2xl font-semibold">Nenhum pedido por aqui ainda.</p><p class="mt-2 text-xs leading-5 text-ink/45">Quando você fizer uma compra, poderá acompanhar tudo pela sua conta.</p></div>
            @else
                <div><p class="font-serif text-2xl font-semibold">{{ $ordersCount === 1 ? 'Você possui 1 pedido.' : "Você possui {$ordersCount} pedidos." }}</p><a href="{{ route('orders.index') }}" class="mt-3 inline-block text-xs font-semibold text-clay">Acompanhar pedidos →</a></div>
            @endif
        </section>
    </div>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
        <button type="button" wire:click="openEditor" class="rounded-full bg-bark px-6 py-3 text-sm font-semibold text-white transition hover:bg-clay">Editar perfil</button>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-full border border-ink/20 px-6 py-3 text-sm font-semibold transition hover:border-bark sm:w-auto">Sair da conta</button></form>
    </div>

    <x-modal wire="profileModal" size="2xl" center scrollable title="Editar perfil">
        <form wire:submit="updateProfile" class="grid gap-5">
            <div class="grid gap-4 sm:grid-cols-2"><x-form-field label="Nome" name="name" wire:model="name" required /><x-form-field label="E-mail" name="email" type="email" wire:model="email" required /></div>
            <div class="border-t border-ink/10 pt-5"><h2 class="font-serif text-xl font-semibold">Alterar senha</h2><p class="mt-1 text-xs text-ink/45">Deixe estes campos vazios para manter a senha atual.</p></div>
            <x-form-field label="Senha atual" name="current_password" type="password" wire:model="current_password" autocomplete="current-password" />
            <div class="grid gap-4 sm:grid-cols-2"><x-form-field label="Nova senha" name="password" type="password" wire:model="password" autocomplete="new-password" /><x-form-field label="Confirmar nova senha" name="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" /></div>
            <div class="flex flex-col-reverse gap-2 border-t border-ink/10 pt-5 sm:flex-row sm:justify-end"><button type="button" wire:click="cancelEdit" class="rounded-full border border-ink/20 px-5 py-2.5 text-xs font-semibold">Cancelar</button><button type="submit" wire:loading.attr="disabled" class="rounded-full bg-bark px-6 py-2.5 text-xs font-semibold text-white disabled:opacity-50"><span wire:loading.remove wire:target="updateProfile">Salvar alterações</span><span wire:loading wire:target="updateProfile">Salvando...</span></button></div>
        </form>
    </x-modal>
</div>
