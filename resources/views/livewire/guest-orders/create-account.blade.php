<div class="mx-auto max-w-xl px-5 py-12 sm:px-8 lg:py-20">
    <x-page-header eyebrow="Pedido #{{ $order->id }}" title="Crie sua conta">Use os dados já informados no pedido e defina apenas uma senha.</x-page-header>
    <section class="mt-8 border border-ink/10 bg-paper p-6 sm:p-8">
        @if ($existingAccount)
            <h2 class="font-serif text-2xl">Já existe uma conta com este e-mail</h2>
            <p class="mt-3 text-base leading-7 text-ink/65">Entre na sua conta ou recupere o acesso. Nenhuma conta duplicada será criada.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row"><a href="{{ route('login') }}" class="editorial-button">Entrar</a><a href="{{ route('password.request') }}" class="inline-flex min-h-12 items-center justify-center border border-ink px-5 text-sm font-semibold">Recuperar acesso</a></div>
        @else
            <form wire:submit="createAccount" class="grid gap-5">
                <div><p class="text-sm text-ink/45">E-mail</p><p class="mt-1 font-medium">{{ $order->customer_email }}</p></div>
                <x-form-field label="Crie uma senha" name="password" type="password" wire:model="password" autocomplete="new-password" required />
                <x-form-field label="Confirmar senha" name="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required />
                <button type="submit" wire:loading.attr="disabled" class="editorial-button w-full disabled:opacity-50"><span wire:loading.remove wire:target="createAccount">Criar minha conta</span><span wire:loading wire:target="createAccount">Criando conta...</span></button>
            </form>
        @endif
    </section>
</div>
