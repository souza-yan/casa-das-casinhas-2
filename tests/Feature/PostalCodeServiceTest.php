<?php

namespace Tests\Feature;

use App\Services\Shipping\PostalCodeService;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class PostalCodeServiceTest extends TestCase
{
    public function test_lookup_uses_only_postal_code_and_returns_address_without_house_number_or_complement(): void
    {
        Http::preventStrayRequests();
        Http::fake(['viacep.com.br/ws/01001000/json/' => Http::response([
            'logradouro' => 'Praça da Sé', 'bairro' => 'Sé', 'localidade' => 'São Paulo', 'uf' => 'SP', 'complemento' => 'lado ímpar',
        ])]);
        $this->assertSame([
            'street' => 'Praça da Sé', 'district' => 'Sé', 'city' => 'São Paulo', 'state' => 'SP',
        ], app(PostalCodeService::class)->lookup('01001-000'));
        Http::assertSent(fn ($request) => $request->url() === 'https://viacep.com.br/ws/01001000/json/');
    }

    public function test_invalid_code_does_not_call_the_service(): void
    {
        Http::fake();
        try {
            app(PostalCodeService::class)->lookup('123');
            $this->fail('Expected an invalid postal code.');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    public function test_unknown_postal_code_is_rejected(): void
    {
        Http::fake(['viacep.com.br/ws/*/json/' => Http::response(['erro' => true])]);
        $this->expectException(InvalidArgumentException::class);
        app(PostalCodeService::class)->lookup('99999-999');
    }
}
