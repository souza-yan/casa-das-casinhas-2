<?php

namespace App\Livewire;

use App\Actions\CreateOrder;
use App\Actions\CreatePayment;
use App\Actions\SelectShipping;
use App\Services\CartService;
use App\Services\Shipping\MelhorEnvioService;
use Livewire\Attributes\Layout;
use Livewire\Component;

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
        try {
            $this->shippingOptions = $shipping->quote($this->zip_code, $cart->items());
            $this->error = null;
        } catch (\Throwable $e) {
            report($e);
            $this->error = 'Não foi possível calcular o frete agora.';
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
        $payment = $createPayment->execute($order);
        $cart->clear();

        return redirect()->route('orders.show', $order)->with('payment_url', $payment->payment_url);
    }

    public function render(CartService $cart)
    {
        abort_if($cart->items()->isEmpty(), 404);

        return view('livewire.checkout', ['items' => $cart->items(), 'subtotal' => $cart->subtotal()]);
    }
}
