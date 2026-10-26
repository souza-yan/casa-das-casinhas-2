<x-mail::message>
# Recebemos seu pedido #{{ $order->id }}

Olá, {{ $order->customer_name }}. Seu pedido foi criado e está aguardando o pagamento.

Total: **R$ {{ number_format((float) $order->total, 2, ',', '.') }}**

<x-mail::button :url="route('guest.orders.show', ['order' => $order, 'token' => $accessToken])">
Acompanhar pedido
</x-mail::button>

Quer criar uma conta para acompanhar este pedido com mais facilidade? Confirme seu e-mail primeiro:

<x-mail::button :url="route('guest.orders.verify', ['order' => $order, 'token' => $emailVerificationToken])">
Confirmar e-mail e criar conta
</x-mail::button>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
