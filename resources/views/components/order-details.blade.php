@props(['order', 'trackingMessage' => null])

<section class="min-w-0">
    <h2 class="font-serif text-3xl font-semibold">Itens do pedido</h2>
    <div class="mt-4 divide-y divide-ink/10 border-y border-ink/10">
        @foreach ($order->items as $item)
            <div class="py-4 text-base">
                <div class="flex justify-between gap-4"><span>{{ $item->quantity }}× {{ $item->product_name }}</span><strong class="shrink-0">R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }}</strong></div>
                @if ($item->personalization_description)<p class="mt-3 whitespace-pre-line border-l border-clay pl-3 text-sm text-ink/65"><strong class="block text-ink">Sua personalização</strong>{{ $item->personalization_description }}</p>@endif
            </div>
        @endforeach
    </div>
    <div class="mt-5 ml-auto grid w-full gap-3 text-base sm:max-w-sm">
        <p class="flex justify-between gap-4"><span class="text-ink/70">Documento</span><span class="text-right">{{ $order->address->document_masked ?? '—' }}</span></p>
        <p class="flex justify-between gap-4"><span class="text-ink/70">Produção</span><span class="text-right">Até {{ $order->productionDays() }} dias úteis</span></p>
        @if ($order->shipment?->tracking_code)
            <div class="border border-ink/20 p-3">
                <span class="block text-sm text-ink/70">Código de rastreio</span>
                <strong class="mt-1 block break-all">{{ $order->shipment->tracking_code }}</strong>
                <a href="{{ $order->shipment->tracking_url ?: config('services.superfrete.tracking_url') }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm font-semibold underline underline-offset-4">Acompanhar na SuperFrete ↗</a>
            </div>
        @else
            <p class="flex justify-between gap-4"><span class="text-ink/70">Transporte</span><span class="text-right">Conforme frete selecionado</span></p>
            @if ($order->shipment?->provider_shipment_id)
                <button type="button" wire:click="syncTracking" wire:loading.attr="disabled" wire:target="syncTracking" class="w-full border border-ink/30 px-3 py-2 text-sm font-semibold disabled:opacity-50"><span wire:loading.remove wire:target="syncTracking">Atualizar rastreio</span><span wire:loading wire:target="syncTracking">Consultando...</span></button>
            @endif
        @endif
        @if ($order->shipment?->posted_at)<p class="flex justify-between gap-4"><span class="text-ink/70">Postagem</span><span class="text-right">{{ $order->shipment->posted_at->format('d/m/Y H:i') }}</span></p>@endif
        @if ($order->shipment?->delivered_at)<p class="flex justify-between gap-4"><span class="text-ink/70">Entrega</span><span class="text-right">{{ $order->shipment->delivered_at->format('d/m/Y H:i') }}</span></p>@endif
        @if ($trackingMessage)<p class="text-sm leading-5 text-ink/75">{{ $trackingMessage }}</p>@endif
        <p class="flex justify-between gap-4"><span class="text-ink/70">Subtotal</span><span>R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</span></p>
        <p class="flex justify-between gap-4"><span class="text-ink/70">Frete</span><span>R$ {{ number_format((float) $order->shipping_total, 2, ',', '.') }}</span></p>
        <p class="mt-2 flex justify-between gap-4 border-t border-ink/20 pt-3 font-semibold"><span>Total</span><span>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</span></p>
    </div>
</section>
