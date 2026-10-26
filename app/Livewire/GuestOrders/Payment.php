<?php

namespace App\Livewire\GuestOrders;

use App\Actions\CreatePayment;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\GuestOrderAccessService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

#[Layout('layouts.public')]
class Payment extends Component
{
    use Interactions;

    public Order $order;

    public ?string $message = null;

    public function mount(Order $order, GuestOrderAccessService $access): void
    {
        abort_if($order->user_id !== null || ! $access->hasSessionAccess($order), 403);
        $this->order = $order->load('payments');
    }

    public function openSandbox(CreatePayment $createPayment, GuestOrderAccessService $access)
    {
        abort_unless($access->hasSessionAccess($this->order), 403);
        abort_unless($this->order->status === OrderStatus::PENDING_PAYMENT, 422);

        if (blank(config('services.asaas.token'))) {
            $this->message = 'Configure ASAAS_TOKEN para gerar a cobrança no Sandbox.';
            $this->toast()->warning('Pagamento não configurado', $this->message)->send();

            return;
        }

        try {
            $payment = $createPayment->execute($this->order);
            $this->order->refresh()->load('payments');

            if (blank($payment->payment_url)) {
                $this->message = 'O Asaas Sandbox criou a cobrança, mas não retornou uma URL de pagamento.';
                $this->toast()->warning('Cobrança sem link', $this->message)->send();

                return;
            }

            $this->toast()->info('Abrindo pagamento', 'Você será direcionado ao ambiente seguro do Asaas.')->send();

            return redirect()->away($payment->payment_url);
        } catch (Throwable $exception) {
            report($exception);
            $this->message = 'Não foi possível acessar o Asaas Sandbox. Consulte os logs e tente novamente.';
            $this->toast()->error('Falha no pagamento', $this->message)->send();
        }
    }

    public function render()
    {
        return view('livewire.guest-orders.payment');
    }
}
