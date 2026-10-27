<div class="mx-auto w-full max-w-[1400px] px-5 py-12 sm:px-8 sm:py-16 lg:px-12 lg:py-20">
    <div class="mx-auto grid max-w-5xl overflow-hidden border border-ink/15 bg-paper lg:grid-cols-[minmax(0,1fr)_minmax(320px,.72fr)]">
        <section class="min-w-0 p-6 sm:p-10 lg:p-14">
            <p class="text-sm font-semibold uppercase tracking-[.18em] text-ink/70">Consulta segura</p>
            <h1 class="mt-3 font-serif text-4xl leading-tight sm:text-5xl">Acompanhar pedido</h1>
            <p class="mt-4 max-w-xl text-base leading-7 text-ink/75">Informe os mesmos dados utilizados na compra. Enviaremos um novo link de acesso para o e-mail do pedido.</p>

            @if ($requestCompleted)
                <div class="mt-8 border border-emerald-800/25 bg-emerald-50 p-5 text-sm leading-6 text-emerald-950" role="status">
                    Se os dados informados corresponderem a um pedido sem cadastro, o link de acompanhamento será enviado por e-mail. Verifique também as pastas de spam e lixo eletrônico.
                </div>
            @endif

            <form wire:submit="sendLink" class="mt-8 grid gap-6" novalidate>
                <x-form-field label="Número do pedido" name="orderNumber" wire:model="orderNumber" inputmode="numeric" autocomplete="off" placeholder="Ex.: 7" required />
                <x-form-field label="E-mail utilizado na compra" name="email" type="email" wire:model="email" autocomplete="email" placeholder="voce@exemplo.com" required />

                <button type="submit" wire:loading.attr="disabled" wire:target="sendLink" class="w-full rounded-sm bg-ink px-6 py-3.5 text-base font-semibold text-paper transition hover:bg-bark disabled:cursor-wait disabled:opacity-60 sm:w-fit">
                    <span wire:loading.remove wire:target="sendLink">Enviar link de acesso</span>
                    <span wire:loading wire:target="sendLink">Enviando...</span>
                </button>
            </form>
        </section>

        <aside class="border-t border-ink/15 bg-clay/20 p-6 sm:p-10 lg:border-l lg:border-t-0 lg:p-12">
            <h2 class="font-serif text-2xl">Onde encontro o número?</h2>
            <p class="mt-4 text-sm leading-6 text-ink/75">O número aparece na tela de confirmação e no assunto do e-mail enviado após a compra, por exemplo: <strong class="text-ink">Pedido #7</strong>.</p>
            <div class="mt-8 border-t border-ink/15 pt-6">
                <p class="text-sm font-semibold">Possui uma conta?</p>
                <p class="mt-2 text-sm leading-6 text-ink/70">Entre normalmente para consultar todos os pedidos associados ao seu perfil.</p>
                <a href="{{ route('login') }}" class="mt-4 inline-block text-sm font-semibold underline underline-offset-4">Entrar na minha conta</a>
            </div>
        </aside>
    </div>
</div>
