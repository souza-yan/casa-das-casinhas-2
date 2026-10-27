<div class="mx-auto max-w-[1040px] px-5 py-12 sm:px-8 lg:py-20">
    @if (request()->boolean('payment_returned'))
        <section class="mx-auto max-w-2xl border border-ink/20 bg-paper px-6 py-12 text-center sm:px-12 sm:py-16">
            <span aria-hidden="true" class="mx-auto grid size-14 place-items-center rounded-full bg-moss text-2xl font-semibold text-paper">✓</span>
            <p class="mt-6 text-sm font-semibold uppercase tracking-[.18em] text-ink/75">Pedido #{{ $order->id }}</p>
            <h1 class="mt-4 font-serif text-4xl font-semibold tracking-[-.03em] sm:text-5xl">Pedido concluído</h1>
            <p class="mx-auto mt-5 max-w-xl text-base leading-7 text-ink/80">Tudo certo! Recebemos seu pedido com sucesso. Você pode consultar o status, os itens e o rastreio quando estiver disponível na área de pedidos.</p>
            <x-action-button :href="route('orders.show', $order)" class="mt-8">Acompanhar pedido</x-action-button>
        </section>
    @else
    <x-page-header eyebrow="Acompanhamento" title="Pedido #{{ $order->id }}">Veja o status e os detalhes da sua encomenda.</x-page-header>
    @if (session('status'))<div class="mt-6 border border-emerald-800/15 bg-emerald-50 p-4 text-base text-emerald-900">{{ session('status') }}</div>@endif
    @if (session('payment_error'))<div class="mt-6 border border-red-800/15 bg-red-50 p-4 text-base text-red-900">{{ session('payment_error') }}</div>@endif
    @if (request()->boolean('payment_returned') && $order->status === \App\Enums\OrderStatus::PENDING_PAYMENT)<div class="mt-6 border border-clay/30 bg-clay/10 p-4 text-base text-ink">Pagamento em análise. A confirmação pode levar alguns instantes.</div>@endif
    <div class="mt-8 grid items-stretch gap-8 lg:grid-cols-[minmax(300px,.9fr)_minmax(0,1.4fr)]">
        <aside class="flex h-full flex-col border border-ink/20 bg-paper p-7"><p class="text-sm font-semibold uppercase tracking-[.18em] text-ink/75">Status do pedido</p><div class="mt-4"><x-status-badge :status="$order->status" size="large" /></div>@if ($order->status === \App\Enums\OrderStatus::PENDING_PAYMENT)<div class="mt-auto pt-8"><a href="{{ route('orders.payment', $order) }}" class="block rounded-sm bg-bark px-5 py-3 text-center text-base font-semibold text-paper">Pagar agora →</a></div>@endif</aside>
        <x-order-details :order="$order" :tracking-message="$trackingMessage" />
    </div>
    @endif
</div>
