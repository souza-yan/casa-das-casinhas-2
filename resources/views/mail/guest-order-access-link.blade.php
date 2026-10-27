<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f8f4ec;color:#211d19;font-family:Arial,sans-serif;line-height:1.5">
    <main style="max-width:560px;margin:32px auto;padding:32px;background:#fffdf9">
        <h1 style="font-family:Georgia,serif;font-weight:500">Acesso ao pedido #{{ $order->id }}</h1>
        <p>Olá, {{ $order->customer_name }}.</p>
        <p>Recebemos uma solicitação para consultar seu pedido. Use o botão abaixo para visualizar o status, os detalhes da compra e o código de rastreio quando estiver disponível.</p>
        <p><a href="{{ route('guest.orders.show', ['order' => $order, 'token' => $accessToken, 'details' => 1]) }}" style="display:inline-block;padding:12px 18px;border:1px solid #211d19;color:#211d19;text-decoration:none">Acompanhar pedido</a></p>
        <p>Este novo link substitui links de acesso solicitados anteriormente e permanece válido por 180 dias.</p>
        <p>Se você não fez esta solicitação, ignore este e-mail. Nenhuma alteração foi realizada no pedido.</p>
        <p>Obrigado,<br>{{ config('app.name') }}</p>
    </main>
</body>
</html>
