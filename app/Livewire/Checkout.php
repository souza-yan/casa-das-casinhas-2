<?php

namespace App\Livewire;

use App\Actions\CreateOrder;
use App\Actions\CreatePayment;
use App\Actions\SelectShipping;
use App\Mail\GuestOrderConfirmation;
use App\Services\CartService;
use App\Services\GuestOrderAccessService;
use App\Services\Shipping\MelhorEnvioService;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use TallStackUi\Traits\Interactions;
use Throwable;

#[Layout('layouts.public')]
class Checkout extends Component
{
    use Interactions;

    public string $name = '';

    public string $email = '';

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

    public string $checkoutToken = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = $user?->name ?? '';
        $this->email = $user?->email ?? '';
        $this->phone = $user?->phone ?? '';
        $this->zip_code = $user?->zip_code ?? '';
        $this->street = $user?->street ?? '';
        $this->number = $user?->number ?? '';
        $this->complement = $user?->complement;
        $this->district = $user?->district ?? '';
        $this->city = $user?->city ?? '';
        $this->state = $user?->state ?? '';
        $this->checkoutToken = session()->remember('checkout.token', fn () => bin2hex(random_bytes(32)));
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'zip_code' => ['required', 'string', 'max:9'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
        ];
    }

    public function quote(CartService $cart, MelhorEnvioService $shipping): void
    {
        $this->validateOnly('zip_code');

        if (blank(config('services.melhor_envio.token')) || blank(config('services.melhor_envio.origin_zip_code'))) {
            $this->error = 'O Melhor Envio Sandbox ainda não está configurado. Configure as credenciais para calcular o frete.';
            $this->toast()->warning('Frete indisponível', $this->error)->send();

            return;
        }

        try {
            $this->shippingOptions = $shipping->quote($this->zip_code, $cart->items());
            $this->error = null;
            $this->toast()->success('Frete calculado', 'Escolha uma das opções de entrega disponíveis.')->send();
        } catch (Throwable $e) {
            report($e);
            $this->error = 'Não foi possível consultar o Melhor Envio Sandbox agora.';
            $this->toast()->error('Erro ao calcular o frete', $this->error)->send();
        }
    }

    public function place(CartService $cart, MelhorEnvioService $shipping, CreateOrder $createOrder, SelectShipping $selectShipping, CreatePayment $createPayment, GuestOrderAccessService $guestAccess)
    {
        $data = $this->validate();

        if (blank(config('services.melhor_envio.token')) || blank(config('services.melhor_envio.origin_zip_code'))) {
            $this->error = 'O Melhor Envio Sandbox ainda não está configurado. Configure as credenciais para calcular o frete.';
            $this->toast()->warning('Frete indisponível', $this->error)->send();

            return;
        }

        try {
            $this->shippingOptions = $shipping->quote($this->zip_code, $cart->items());
            $this->error = null;
        } catch (Throwable $exception) {
            report($exception);
            $this->error = 'Não foi possível validar o frete selecionado. Calcule as opções novamente.';
            $this->toast()->error('Frete indisponível', $this->error)->send();

            return;
        }

        $option = collect($this->shippingOptions)->firstWhere('id', $this->shippingId);
        if (! $option) {
            $this->addError('shippingId', 'Selecione um frete válido.');
            $this->toast()->warning('Escolha uma entrega', 'Selecione uma opção de frete antes de finalizar.')->send();

            return;
        }
        $address = collect($data)->only(['zip_code', 'street', 'number', 'complement', 'district', 'city', 'state'])->all();
        $user = auth()->user();
        $guestTokens = null;
        if (! $user) {
            $guestTokens = session()->remember('checkout.guest-tokens.'.$this->checkoutToken, fn () => [
                'access' => $guestAccess->makeToken(),
                'verification' => $guestAccess->makeToken(),
            ]);
        }

        $order = $createOrder->create(
            $user,
            $cart->raw(),
            collect($data)->only(['name', 'email', 'phone'])->all(),
            $address,
            (float) $option['price'],
            $this->checkoutToken,
            $guestTokens['access'] ?? null,
            $guestTokens['verification'] ?? null,
        );
        $selectShipping->execute($order, $option);
        $cart->clear();

        $paymentCreated = false;

        try {
            $createPayment->execute($order);
            $paymentCreated = true;
        } catch (Throwable $exception) {
            report($exception);
        }

        if (! $user) {
            $guestAccess->grantSessionAccess($order);

            if ($order->wasRecentlyCreated) {
                try {
                    Mail::to($order->customer_email)->send(new GuestOrderConfirmation($order, $guestTokens['access'], $guestTokens['verification']));
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            $message = $paymentCreated
                ? 'Pedido criado. Enviamos um link seguro de acompanhamento para seu e-mail.'
                : 'Pedido criado. Você poderá retomar o pagamento nesta página quando o Asaas estiver disponível.';
            $this->toast()->success('Pedido criado', $message)->flash()->send();

            return redirect()->route('guest.orders.show', ['order' => $order, 'token' => $guestTokens['access']]);
        }

        if ($paymentCreated) {
            $this->toast()->success('Pedido criado', 'Continue no ambiente seguro do Asaas para pagar.')->flash()->send();

            return redirect()->route('orders.payment', $order);
        }

        $warning = blank(config('services.asaas.token'))
            ? 'Pedido criado, mas o Asaas Sandbox ainda não está configurado. Você poderá gerar a cobrança nesta página depois da configuração.'
            : 'Pedido criado, mas não foi possível gerar a cobrança no Asaas Sandbox. Tente novamente na página do pedido.';

        $this->toast()->warning('Pedido criado sem cobrança', $warning)->flash()->send();

        return redirect()->route('orders.payment', $order);
    }

    public function render(CartService $cart)
    {
        $items = $cart->items();
        abort_if($items->isEmpty(), 404);

        return view('livewire.checkout', [
            'items' => $items,
            'subtotal' => $cart->subtotal(),
            'productionDays' => (int) $items->max(fn (array $item) => $item['product']->production_days),
        ]);
    }
}
