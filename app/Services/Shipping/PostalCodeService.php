<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class PostalCodeService
{
    public function lookup(string $zipCode): array
    {
        $digits = preg_replace('/\D/', '', $zipCode);
        if (strlen($digits) !== 8) {
            throw new InvalidArgumentException('Informe um CEP com 8 dígitos.');
        }

        $data = Http::acceptJson()->connectTimeout(3)->timeout(6)
            ->get("https://viacep.com.br/ws/{$digits}/json/")->throw()->json();

        if (data_get($data, 'erro')) {
            throw new InvalidArgumentException('CEP não encontrado. Confira os números informados.');
        }

        if (! is_array($data) || blank($data['localidade'] ?? null) || ! preg_match('/^[A-Z]{2}$/', $data['uf'] ?? '')) {
            throw new RuntimeException('Resposta inválida da consulta de CEP.');
        }

        return [
            'street' => trim((string) ($data['logradouro'] ?? '')),
            'district' => trim((string) ($data['bairro'] ?? '')),
            'city' => trim($data['localidade']),
            'state' => $data['uf'],
        ];
    }
}
