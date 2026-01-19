<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class ValidTimeCondition implements DataAwareRule, ValidationRule
{
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (App::hasDebugModeEnabled()) {
            Log::notice('[' . __FILE__ . ':' . __LINE__ . '][' . __CLASS__ . '][' . __METHOD__ . '] attribute:' . $attribute);
            Log::notice('[' . __FILE__ . ':' . __LINE__ . '][' . __CLASS__ . '][' . __METHOD__ . '] value:' . $value);
        }

        $matches = [];
        if (!preg_match("/customConditions\.(\d+)\.conditions\.(\d+)\.(value_start|value_stop)/i", $attribute, $matches)) {
            return;
        }

        $groupIndex = (int)$matches[1];
        $conditionIndex = (int)$matches[2];
        $fieldName = $matches[3];

        if (!isset($this->data['customConditions'][$groupIndex]['conditions'][$conditionIndex]['variable'])) {
            return;
        }

        
        $variableType = $this->data['customConditions'][$groupIndex]['conditions'][$conditionIndex]['variable'];

        if (empty($variableType) || empty($value)) {
            return;
        }

        $now = Carbon::now();
        
        switch ($variableType) {
            case 'year':
                if (intval($value) < $now->year) {
                    $fail("The :attribute cannot be earlier than the current year.");
                    return;
                }
                break;

            case 'yday':
                if (intval($value) < 1) {
                    $fail("The :attribute cannot be less than one.");
                    return;
                }
                if (intval($value) > 366) {
                    $fail("The :attribute cannot be more than 366.");
                    return;
                }
                break;

            case 'mon':
                if (intval($value) < 1) {
                    $fail("The :attribute cannot be less than one.");
                    return;
                }
                if (intval($value) > 12) {
                    $fail("The :attribute cannot be more than 12.");
                    return;
                }
                break;

            case 'mday':
                if (intval($value) < 1) {
                    $fail("The :attribute cannot be less than one.");
                    return;
                }
                if (intval($value) > 31) {
                    $fail("The :attribute cannot be more than 31.");
                    return;
                }
                break;

            case 'wday':
                if (intval($value) < 1) {
                    $fail("The :attribute cannot be less than one.");
                    return;
                }
                if (intval($value) > 7) {
                    $fail("The :attribute cannot be more than seven.");
                    return;
                }
                break;

            case 'week':
                if (intval($value) < 1) {
                    $fail("The :attribute cannot be less than one.");
                    return;
                }
                if (intval($value) > 53) {
                    $fail("The :attribute cannot be more than 53.");
                    return;
                }
                break;

            case 'mweek':
                if (intval($value) < 1) {
                    $fail("The :attribute cannot be less than one.");
                    return;
                }
                if (intval($value) > 6) { 
                    $fail("The :attribute cannot be more than six.");
                    return;
                }
                break;

            case 'hour':
                if (intval($value) < 0) {
                    $fail("The :attribute cannot be less than zero.");
                    return;
                }
                if (intval($value) > 23) {
                    $fail("The :attribute cannot be more than 23.");
                    return;
                }
                break;

            case 'minute-of-day':
                if (intval($value) < 1) {
                    $fail("The :attribute cannot be less than one.");
                    return;
                }
                if (intval($value) > 1440) {
                    $fail("The :attribute cannot be more than 1440.");
                    return;
                }
                break;

            case 'time-of-day':
                if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                    $fail("The :attribute must be in HH:MM or HH:MM:SS format.");
                    return;
                }

                $parts = explode(':', $value);
                $h = intval($parts[0]);
                $m = intval($parts[1]);

                if ($h < 0 || $h > 23) {
                    $fail("The hour in :attribute must be between 0 and 23.");
                    return;
                }
                if ($m < 0 || $m > 59) {
                    $fail("The minute in :attribute must be between 0 and 59.");
                    return;
                }
                break;

            case 'date-time':
                if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/', $value)) {
                    $fail("The :attribute must be a valid datetime.");
                    return;
                }
                break;

            default:
                break;
        }
    }
}