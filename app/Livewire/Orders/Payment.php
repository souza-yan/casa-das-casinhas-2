<?php

namespace App\Livewire\Orders;

use App\Actions\CreatePayment;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.public')]
class Payment extends Component
{
    use AuthorizesRequests;

    public Order $order;

    public ?string $message = null;

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);
        $this->order = $order->load('payments');
    }

    public function openSandbox(CreatePayment $createPayment)
    {
        $this->authorize('view', $this->order);
        abort_unless($this->order->status === OrderStatus::PENDING_PAYMENT, 422);

        if (blank(config('services.asaas.token'))) {
            $this->message = 'Configure ASAAS_TOKEN para gerar a cobrança no Sandbox.';

            return;
        }

        try {
            $payment = $createPayment->execute($this->order);
            $this->order->refresh()->load('payments');

            if (blank($payment->payment_url)) {
                $this->message = 'O Asaas Sandbox criou a cobrança, mas não retornou uma URL de pagamento.';

                return;
            }

            return redirect()->away($payment->payment_url);
        } catch (Throwable $exception) {
            report($exception);
            $this->message = 'Não foi possível acessar o Asaas Sandbox. Consulte os logs e tente novamente.';
        }
    }

    public function render()
    {
        return view('livewire.orders.payment');
    }
}
