<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f8f4ec;color:#211d19;font-family:Arial,sans-serif;line-height:1.5">
    <main style="max-width:560px;margin:32px auto;padding:32px;background:#fffdf9">
        <h1 style="font-family:Georgia,serif;font-weight:500">Recebemos seu pedido #{{ $order->id }}</h1>
        <p>Olá, {{ $order->customer_name }}. Recebemos seu pedido.</p>
        <h2 style="margin-top:24px;font-family:Georgia,serif;font-weight:500">Resumo do pedido</h2>
        @foreach ($order->items as $item)
            <p style="margin:8px 0">{{ $item->quantity }}× {{ $item->product_name }} <strong style="float:right">R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }}</strong></p>
        @endforeach
        <hr style="margin:20px 0;border:0;border-top:1px solid #d8d0c5">
        <p>Subtotal: <strong style="float:right">R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</strong></p>
        <p>Frete: <strong style="float:right">R$ {{ number_format((float) $order->shipping_total, 2, ',', '.') }}</strong></p>
        <p>Total: <strong style="float:right">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></p>
        <p><a href="{{ route('guest.orders.show', ['order' => $order, 'token' => $accessToken, 'details' => 1]) }}" style="display:inline-block;padding:12px 18px;border:1px solid #211d19;color:#211d19;text-decoration:none">Acompanhar pedido</a></p>
        <p>Guarde este e-mail. O botão acima permite consultar durante 180 dias o status atualizado, os detalhes da compra e o código de rastreio assim que a transportadora o disponibilizar, inclusive em outro dispositivo.</p>
        <p>Obrigado,<br>{{ config('app.name') }}</p>
    </main>
</body>
</html>
