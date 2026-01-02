<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPIN implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $sequences = [
            '012',
            '123',
            '234',
            '345',
            '456',
            '567',
            '678',
            '789',
            '987',
            '876',
            '765',
            '654',
            '543',
            '432',
            '321',
            '210'
        ];

        foreach ($sequences as $sequence) {
            if (strpos($value, $sequence) !== false) {
                $fail("Password cannot contain sequential digits");
                break;
            }
        }
    }
}
