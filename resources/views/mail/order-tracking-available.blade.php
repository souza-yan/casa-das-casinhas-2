<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f8f4ec;color:#211d19;font-family:Arial,sans-serif;line-height:1.5">
    <main style="max-width:560px;margin:32px auto;padding:32px;background:#fffdf9">
        <h1 style="font-family:Georgia,serif;font-weight:500">Seu pedido foi preparado para envio</h1>
        <p>Olá, {{ $order->customer_name }}. O código de rastreio do pedido #{{ $order->id }} já está disponível.</p>
        <p style="padding:16px;border:1px solid #d8d0c5"><small>Código de rastreio</small><br><strong style="font-size:20px">{{ $order->shipment->tracking_code }}</strong></p>
        <p><a href="{{ $order->shipment->tracking_url ?: config('services.superfrete.tracking_url') }}" style="display:inline-block;padding:12px 18px;border:1px solid #211d19;color:#211d19;text-decoration:none">Acompanhar entrega</a></p>
        @if ($order->user_id)
            <p><a href="{{ route('orders.show', $order) }}">Consultar detalhes do pedido</a></p>
        @else
            <p>Para consultar todos os detalhes, use o link privado recebido na confirmação da compra ou solicite um novo acesso na página <a href="{{ route('guest.orders.lookup') }}">Acompanhar pedido</a>.</p>
        @endif
        <p>As movimentações são atualizadas pela transportadora após a postagem.</p>
        <p>Obrigado,<br>{{ config('app.name') }}</p>
    </main>
</body>
</html>
