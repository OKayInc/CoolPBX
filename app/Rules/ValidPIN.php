<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Facades\Setting;

class ValidPIN implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $complexityEnabled = Setting::getSetting('voicemail', 'password_complexity', 'boolean');

        if (!$complexityEnabled) {
            return; 
        }

        if (preg_match('/(\d)\1{2,}/', $value)) {
            $fail("The :attribute cannot contain 3 or more repeating digits (e.g., 111, 2222).");
            return;
        }

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
                $fail("The :attribute cannot contain sequential digits (e.g., 123, 456, 987).");
                return;
            }
        }
    }
}
