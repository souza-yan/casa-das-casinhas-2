<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f8f4ec;color:#211d19;font-family:Arial,sans-serif;line-height:1.5">
    <main style="max-width:560px;margin:32px auto;padding:32px;background:#fffdf9">
        <h1 style="font-family:Georgia,serif;font-weight:500">Recebemos seu pedido #{{ $order->id }}</h1>
        <p>Olá, {{ $order->customer_name }}. Seu pedido foi criado e está aguardando o pagamento.</p>
        <p><strong>Total: R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></p>
        <p><a href="{{ route('guest.orders.show', ['order' => $order, 'token' => $accessToken]) }}" style="display:inline-block;padding:12px 18px;border:1px solid #211d19;color:#211d19;text-decoration:none">Acompanhar pedido</a></p>
        <p>Quer criar uma conta para acompanhar este pedido com mais facilidade? Confirme seu e-mail primeiro:</p>
        <p><a href="{{ route('guest.orders.verify', ['order' => $order, 'token' => $emailVerificationToken]) }}" style="display:inline-block;padding:12px 18px;border:1px solid #211d19;color:#211d19;text-decoration:none">Confirmar e-mail e criar conta</a></p>
        <p>Obrigado,<br>{{ config('app.name') }}</p>
    </main>
</body>
</html>
