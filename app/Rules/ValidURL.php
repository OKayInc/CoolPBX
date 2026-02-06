<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidURL implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $response = Http::timeout(5)->get($value);

            if ($response->failed()) {
                $fail('The URL on :attribute does not work.');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $fail('The URL on :attribute is not reachable.');
        }
    }
}
