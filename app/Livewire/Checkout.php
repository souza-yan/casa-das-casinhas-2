<?php

namespace App\Livewire;

use App\Actions\CreateOrder;
use App\Actions\CreatePayment;
use App\Actions\SelectShipping;
use App\Mail\GuestOrderConfirmation;
use App\Services\CartService;
use App\Services\GuestOrderAccessService;
use App\Services\Shipping\MelhorEnvioService;
use App\Services\Shipping\PostalCodeService;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
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

    public int $step = 1;

    #[Locked]
    public bool $showAddressFields = false;

    #[Locked]
    public array $resolvedAddress = [];

    #[Locked]
    public ?string $resolvedZipCode = null;

    public ?string $postalCodeError = null;

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
        return [...$this->personalRules(), ...$this->deliveryRules()];
    }

    private function personalRules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255', "regex:/^[\\p{L}\\p{M}][\\p{L}\\p{M}\\s'’-]*$/u"],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(?:\d{10,11}|\(\d{2}\) \d{4,5}-\d{4})$/'],
        ];
    }

    private function deliveryRules(): array
    {
        return [
            'zip_code' => ['required', 'string', 'regex:/^\d{5}-\d{3}$/'],
            'street' => ['required', 'string', 'min:3', 'max:255', "regex:/^[\\p{L}\\p{M}\\d\\s.,'ºª°-]+$/u"],
            'number' => ['required', 'string', 'max:30', 'regex:~^[\\p{L}\\p{M}\\d\\s/-]+$~u'],
            'complement' => ['nullable', 'string', 'max:255', "regex:~^[\\p{L}\\p{M}\\d\\s.,'ºª°#/-]*$~u"],
            'district' => ['required', 'string', 'min:2', 'max:255', "regex:/^[\\p{L}\\p{M}\\d\\s.'’-]+$/u"],
            'city' => ['required', 'string', 'min:2', 'max:255', "regex:/^[\\p{L}\\p{M}\\s.'’-]+$/u"],
            'state' => ['required', 'string', 'regex:/^[A-Z]{2}$/'],
        ];
    }

    public function continueToDelivery(): void
    {
        $this->validate($this->personalRules());
        $this->step = 2;
        if (strlen(preg_replace('/\D/', '', $this->zip_code)) === 8 && ! $this->showAddressFields) {
            app()->call([$this, 'lookupAddress']);
        }
    }

    public function updatedZipCode(): void
    {
        $digits = preg_replace('/\D/', '', $this->zip_code);
        if ($this->resolvedZipCode === $digits) {
            $this->zip_code = substr($digits, 0, 5).'-'.substr($digits, 5);

            return;
        }

        $this->reset('street', 'district', 'city', 'state', 'number', 'complement', 'resolvedAddress', 'resolvedZipCode', 'showAddressFields', 'postalCodeError', 'shippingOptions', 'shippingId', 'error');
        $this->resetValidation(['zip_code', 'street', 'district', 'city', 'state', 'number', 'complement', 'shippingId']);

        if (strlen($digits) === 8) {
            app()->call([$this, 'lookupAddress']);
        }
    }

    public function lookupAddress(PostalCodeService $addresses, CartService $cart, MelhorEnvioService $shipping): void
    {
        $digits = preg_replace('/\D/', '', $this->zip_code);
        if (strlen($digits) !== 8) {
            return;
        }
        $this->zip_code = substr($digits, 0, 5).'-'.substr($digits, 5);
        $this->reset('postalCodeError', 'shippingOptions', 'shippingId', 'error', 'resolvedAddress');

        try {
            $this->resolvedAddress = $addresses->lookup($digits);
            foreach ($this->resolvedAddress as $field => $value) {
                $this->{$field} = $value;
            }
            $this->resolvedZipCode = $digits;
            $this->showAddressFields = true;
        } catch (InvalidArgumentException $exception) {
            $this->postalCodeError = $exception->getMessage();
            $this->showAddressFields = false;

            return;
        } catch (Throwable $exception) {
            report($exception);
            $this->postalCodeError = 'Não foi possível buscar o endereço. Você pode preenchê-lo abaixo ou tentar novamente.';
            $this->showAddressFields = true;
        }

        $this->quote($cart, $shipping);
    }

    public function continueToPayment(): void
    {
        $this->validate($this->deliveryRules());

        if (! $this->shippingId) {
            $this->addError('shippingId', 'Selecione uma opção de entrega para continuar.');

            return;
        }

        $this->step = 3;
    }

    public function goToStep(int $step): void
    {
        abort_unless(in_array($step, [1, 2], true), 404);
        $this->step = $step;
    }

    public function quote(CartService $cart, MelhorEnvioService $shipping): void
    {
        $this->validateOnly('zip_code');
        $this->reset('shippingOptions', 'shippingId', 'error');

        if (blank(config('services.melhor_envio.token')) || blank(config('services.melhor_envio.origin_zip_code'))) {
            $this->error = 'O frete está indisponível no momento. Tente novamente mais tarde.';

            return;
        }

        try {
            $this->shippingOptions = $shipping->quote($this->zip_code, $cart->items());
            if ($this->shippingOptions === []) {
                $this->error = 'Nenhuma opção de entrega foi encontrada para este CEP.';
            }
        } catch (Throwable $e) {
            report($e);
            $this->error = 'Não foi possível calcular o frete agora. Tente novamente em instantes.';
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
            'selectedShipping' => collect($this->shippingOptions)->firstWhere('id', $this->shippingId),
        ]);
    }
}
