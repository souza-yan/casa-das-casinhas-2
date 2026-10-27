<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCpfCnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid((string) $value)) {
            $fail('Informe um CPF ou CNPJ válido.');
        }
    }

    public static function digits(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    public static function isValid(string $value): bool
    {
        $digits = self::digits($value);

        if (! in_array(strlen($digits), [11, 14], true) || preg_match('/^(\d)\1+$/', $digits)) {
            return false;
        }

        $weights = strlen($digits) === 11
            ? [[10, 9, 8, 7, 6, 5, 4, 3, 2], [11, 10, 9, 8, 7, 6, 5, 4, 3, 2]]
            : [[5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2], [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]];

        foreach ($weights as $weightSet) {
            $sum = 0;
            foreach ($weightSet as $position => $weight) {
                $sum += (int) $digits[$position] * $weight;
            }
            $digit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

            if ((int) $digits[count($weightSet)] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
