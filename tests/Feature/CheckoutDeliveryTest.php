<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.melhor_envio.token' => 'test-token', 'services.melhor_envio.origin_zip_code' => '01310-100']);
        $product = Product::factory()->create();
        $this->withSession(['cart.items' => [$product->id => 1]]);
    }

    private function deliveryForm()
    {
        return Livewire::test(Checkout::class)->set('name', 'Maria Silva')
            ->set('email', 'maria@example.com')->set('phone', '12988961502')
            ->call('continueToDelivery');
    }

    private function fakeServices(array $address = [], array $shipping = []): void
    {
        Http::fake([
            'viacep.com.br/ws/*/json/' => Http::response($address ?: ['logradouro' => 'Praça da Sé', 'bairro' => 'Sé', 'localidade' => 'São Paulo', 'uf' => 'SP']),
            '*/api/v2/me/shipment/calculate' => Http::response($shipping ?: [['id' => 1, 'name' => 'PAC', 'price' => '20.00', 'delivery_time' => 5, 'company' => ['name' => 'Correios']]]),
        ]);
    }

    public function test_delivery_starts_with_only_postal_code_and_does_not_request_incomplete_codes(): void
    {
        $this->deliveryForm()->assertSee('name="zip_code"', false)
            ->assertDontSee('name="street"', false)->assertDontSee('name="number"', false)
            ->assertDontSee('Calcular opções de frete')->assertDontSee('O prazo estimado total combina')
            ->set('zip_code', '01001')->assertSet('showAddressFields', false);
        Http::assertNothingSent();
    }

    public function test_cart_summary_does_not_show_removed_heading_and_shipping_text(): void
    {
        $this->get(route('cart'))->assertOk()->assertDontSee('Resumo do pedido')
            ->assertDontSee('O frete será calculado na próxima etapa.')->assertSee('Subtotal');
    }

    public function test_completed_code_fills_address_and_quotes_shipping_automatically(): void
    {
        $this->fakeServices();
        $component = $this->deliveryForm()->set('zip_code', '01001000')
            ->assertSet('zip_code', '01001-000')->assertSet('street', 'Praça da Sé')
            ->assertSet('district', 'Sé')->assertSet('city', 'São Paulo')->assertSet('state', 'SP')
            ->assertSet('number', '')->assertSet('complement', null)
            ->assertSet('showAddressFields', true)->assertSet('shippingOptions.0.price', 20.0)
            ->assertSee('name="number"', false)->assertSee('name="complement"', false);

        $component->set('shippingId', '1')->assertSee('Total')->assertSee('R$ 20,00');
        $component->set('number', '10')->call('continueToPayment')->assertHasNoErrors()->assertSet('step', 3);
        $component->set('zip_code', '01001000')->assertSet('zip_code', '01001-000');
        Http::assertSentCount(2);
    }

    public function test_changing_postal_code_clears_old_address_number_and_shipping_selection(): void
    {
        $this->fakeServices();
        $this->deliveryForm()->set('zip_code', '01001-000')->set('shippingId', '1')->set('number', '12')->set('complement', 'Apto 1')
            ->set('zip_code', '0100')->assertSet('street', '')->assertSet('number', '')
            ->assertSet('complement', null)->assertSet('shippingOptions', [])->assertSet('shippingId', null)
            ->assertSet('showAddressFields', false);
    }

    public function test_unknown_postal_code_has_feedback_and_does_not_quote_shipping(): void
    {
        $this->fakeServices(['erro' => true]);
        $this->deliveryForm()->set('zip_code', '99999-999')->assertSet('showAddressFields', false)
            ->assertSet('shippingOptions', [])->assertSee('CEP não encontrado');
        Http::assertSentCount(1);
    }

    public function test_address_service_failure_allows_manual_address_and_shipping_still_runs(): void
    {
        Http::fake([
            'viacep.com.br/ws/*/json/' => Http::response([], 503),
            '*/api/v2/me/shipment/calculate' => Http::response([['id' => 1, 'name' => 'PAC', 'price' => 20, 'delivery_time' => 5]]),
        ]);
        $this->deliveryForm()->set('zip_code', '01001-000')->assertSet('showAddressFields', true)
            ->assertSet('resolvedAddress', [])->assertSet('shippingOptions.0.id', '1')
            ->assertSee('Você pode preenchê-lo abaixo');
    }

    public function test_generic_postal_code_leaves_missing_street_and_district_editable(): void
    {
        $this->fakeServices(['logradouro' => '', 'bairro' => '', 'localidade' => 'São Paulo', 'uf' => 'SP']);
        $this->deliveryForm()->set('zip_code', '01001-000')->assertSet('showAddressFields', true)
            ->assertSet('street', '')->assertSet('district', '')->assertSet('city', 'São Paulo')
            ->set('street', 'Rua A')->set('district', 'Centro')->set('number', '1')->set('shippingId', '1')
            ->call('continueToPayment')->assertHasNoErrors()->assertSet('step', 3);
    }

    public function test_missing_shipping_credentials_does_not_prevent_address_autofill(): void
    {
        $this->fakeServices();
        config(['services.melhor_envio.token' => null]);
        $this->deliveryForm()->set('zip_code', '01001-000')->assertSet('street', 'Praça da Sé')
            ->assertSet('showAddressFields', true)->assertSet('shippingOptions', [])
            ->assertSee('O frete está indisponível no momento');
        Http::assertSentCount(1);
    }

    public function test_shipping_failure_preserves_address_and_allows_retry(): void
    {
        Http::fake([
            'viacep.com.br/ws/*/json/' => Http::response(['logradouro' => 'Rua A', 'bairro' => 'Centro', 'localidade' => 'São Paulo', 'uf' => 'SP']),
            '*/api/v2/me/shipment/calculate' => Http::sequence()->pushStatus(503)->pushStatus(503)
                ->push([['id' => 1, 'name' => 'PAC', 'price' => 20, 'delivery_time' => 5]]),
        ]);
        $component = $this->deliveryForm()->set('zip_code', '01001-000')
            ->assertSet('street', 'Rua A')->assertSet('shippingOptions', [])
            ->assertSee('Não foi possível calcular o frete agora');
        $component->call('lookupAddress')->assertSet('error', null)->assertSet('shippingOptions.0.id', '1');
    }
}
