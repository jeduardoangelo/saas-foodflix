<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Cpf implements Rule
{
    public function passes($attribute, $value)
    {
        if (! is_string($value) || ! preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $value)) {
            return false;
        }

        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits)) {
            return false;
        }

        for ($position = 9; $position < 11; $position++) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $digits[$index] * (($position + 1) - $index);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $digits[$position] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'Informe um CPF válido no formato 000.000.000-00.';
    }
}
