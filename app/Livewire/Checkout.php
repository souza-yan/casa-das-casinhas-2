<?php

namespace App\Livewire;

use App\Actions\CreateOrder;
use App\Actions\CreatePayment;
use App\Actions\SelectShipping;
use App\Services\CartService;
use App\Services\Shipping\MelhorEnvioService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.public')]
class Checkout extends Component
{
    public string $phone = '';

    public string $zip_code = '';

    public string $street = '';

    public string $number = '';

    public ?string $complement = null;

    public string $district = '';

    public string $city = '';

    public string $state = '';

    public array $shippingOptions = [];

    public ?string $shippingId = null;

    public ?string $error = null;

    protected function rules(): array
    {
        return ['phone' => ['required', 'string', 'max:30'], 'zip_code' => ['required', 'string', 'max:9'], 'street' => ['required', 'string', 'max:255'], 'number' => ['required', 'string', 'max:30'], 'complement' => ['nullable', 'string', 'max:255'], 'district' => ['required', 'string', 'max:255'], 'city' => ['required', 'string', 'max:255'], 'state' => ['required', 'string', 'size:2']];
    }

    public function quote(CartService $cart, MelhorEnvioService $shipping): void
    {
        $this->validateOnly('zip_code');

        if (blank(config('services.melhor_envio.token')) || blank(config('services.melhor_envio.origin_zip_code'))) {
            $this->error = 'O Melhor Envio Sandbox ainda não está configurado. Configure as credenciais para calcular o frete.';

            return;
        }

        try {
            $this->shippingOptions = $shipping->quote($this->zip_code, $cart->items());
            $this->error = null;
        } catch (Throwable $e) {
            report($e);
            $this->error = 'Não foi possível consultar o Melhor Envio Sandbox agora.';
        }
    }

    public function place(CartService $cart, CreateOrder $createOrder, SelectShipping $selectShipping, CreatePayment $createPayment)
    {
        $data = $this->validate();
        $option = collect($this->shippingOptions)->firstWhere('id', $this->shippingId);
        if (! $option) {
            $this->addError('shippingId', 'Selecione um frete válido.');

            return;
        }
        $address = collect($data)->only(['zip_code', 'street', 'number', 'complement', 'district', 'city', 'state'])->all();
        $order = $createOrder->execute(auth()->user(), $cart->raw(), $address, $this->phone, (float) $option['price']);
        $selectShipping->execute($order, $option);
        $cart->clear();

        try {
            $createPayment->execute($order);

            return redirect()->route('orders.payment', $order)->with([
                'status' => 'Pedido criado. Continue no ambiente seguro do Asaas Sandbox.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('orders.payment', $order)->with('warning',
                blank(config('services.asaas.token'))
                    ? 'Pedido criado, mas o Asaas Sandbox ainda não está configurado. Você poderá gerar a cobrança nesta página depois da configuração.'
                    : 'Pedido criado, mas não foi possível gerar a cobrança no Asaas Sandbox. Tente novamente na página do pedido.'
            );
        }
    }

    public function render(CartService $cart)
    {
        abort_if($cart->items()->isEmpty(), 404);

        return view('livewire.checkout', ['items' => $cart->items(), 'subtotal' => $cart->subtotal()]);
    }
}
