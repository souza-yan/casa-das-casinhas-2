<?php

namespace App\Livewire;

use App\Actions\CreateOrder;
use App\Actions\SelectShipping;
use App\Mail\GuestOrderConfirmation;
use App\Rules\ValidCpfCnpj;
use App\Services\CartService;
use App\Services\GuestOrderAccessService;
use App\Services\Payments\AsaasPaymentRedirect;
use App\Services\Payments\AsaasService;
use App\Services\Shipping\PostalCodeService;
use App\Services\Shipping\SuperFreteService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
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

    public string $document = '';

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
            'document' => ['required', 'string', new ValidCpfCnpj],
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
        $this->validateWithScroll($this->personalRules());
        $this->step = 2;
        $this->dispatch('checkout-scroll-top');
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

    public function lookupAddress(PostalCodeService $addresses, CartService $cart, SuperFreteService $shipping): void
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
            $this->postalCodeError = 'CEP não encontrado.';
            $this->showAddressFields = false;

            return;
        }

        $this->quote($cart, $shipping);
    }

    public function continueToShipping(CartService $cart, SuperFreteService $shipping): void
    {
        $this->validateWithScroll($this->deliveryRules());

        $this->quote($cart, $shipping);

        if ($this->shippingOptions === []) {
            $this->dispatch('checkout-scroll-top');

            return;
        }

        $this->step = 3;
        $this->dispatch('checkout-scroll-top');
    }

    public function continueToPayment(): void
    {
        abort_unless($this->step === 3, 404);

        if (! $this->shippingId) {
            $this->addError('shippingId', 'Selecione uma opção de entrega para continuar.');
            $this->dispatch('checkout-scroll-top');

            return;
        }

        $this->step = 4;
        $this->dispatch('checkout-scroll-top');
    }

    public function goToStep(int $step): void
    {
        abort_unless(in_array($step, [1, 2, 3], true), 404);
        $this->step = $step;
        $this->dispatch('checkout-scroll-top');
    }

    public function quote(CartService $cart, SuperFreteService $shipping): void
    {
        $this->validateOnly('zip_code');
        $this->reset('shippingOptions', 'shippingId', 'error');

        if (! $shipping->isConfiguredForQuote()) {
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

    public function place(CartService $cart, SuperFreteService $shipping, CreateOrder $createOrder, SelectShipping $selectShipping, GuestOrderAccessService $guestAccess, AsaasPaymentRedirect $paymentRedirect)
    {
        $data = $this->validateWithScroll($this->rules());

        if (! $shipping->isConfiguredForQuote()) {
            $this->error = 'O SuperFrete Sandbox ainda não está configurado. Configure as credenciais para calcular o frete.';
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
        $address['document'] = ValidCpfCnpj::digits($data['document']);
        $user = auth()->user();
        $guestTokens = null;
        if (! $user) {
            $guestTokens = session()->remember('checkout.guest-tokens.'.$this->checkoutToken, fn () => [
                'access' => $guestAccess->makeToken(),
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
        );
        $selectShipping->execute($order, $option);
        $cart->clear();

        if (! $user) {
            $guestAccess->grantSessionAccess($order);

            if ($order->wasRecentlyCreated) {
                try {
                    Mail::to($order->customer_email)->send(new GuestOrderConfirmation($order, $guestTokens['access']));
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            try {
                return $paymentRedirect->for($order, $this->guestPaymentReturnUrl($order, $guestTokens['access']));
            } catch (Throwable $exception) {
                Log::warning('Não foi possível iniciar pagamento de convidado no checkout.', ['order_id' => $order->id, 'exception' => $exception::class]);
                $this->toast()->warning('Pedido criado sem cobrança', AsaasService::clientMessage($exception))->flash()->send();

                return redirect()->route('guest.orders.show', ['order' => $order, 'token' => $guestTokens['access']]);
            }
        }

        try {
            return $paymentRedirect->for($order, $this->customerPaymentReturnUrl($order));
        } catch (Throwable $exception) {
            Log::warning('Não foi possível iniciar pagamento no checkout.', ['order_id' => $order->id, 'exception' => $exception::class]);
            $this->toast()->warning('Pedido criado sem cobrança', AsaasService::clientMessage($exception))->flash()->send();

            return redirect()->route('orders.show', $order);
        }
    }

    private function validateWithScroll(array $rules): array
    {
        try {
            return $this->validate($rules);
        } catch (ValidationException $exception) {
            $this->dispatch('checkout-scroll-top');

            throw $exception;
        }
    }

    private function customerPaymentReturnUrl($order): ?string
    {
        $baseUrl = rtrim((string) config('services.asaas.success_url'), '/');

        return filled($baseUrl) ? $baseUrl.'/pedidos/'.$order->id.'?payment_returned=1' : null;
    }

    private function guestPaymentReturnUrl($order, string $token): ?string
    {
        $baseUrl = rtrim((string) config('services.asaas.success_url'), '/');

        return filled($baseUrl)
            ? $baseUrl.'/acompanhar-pedido/'.$order->id.'/'.$token.'?payment_returned=1'
            : null;
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
