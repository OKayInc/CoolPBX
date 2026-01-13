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
        if(App::hasDebugModeEnabled())
        {
            Log::notice('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] attribute:'.$attribute);
            Log::notice('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] value:'.$value);
        }
//dd($this->data);
        $matches = [];
        if (preg_match("/\w+\.(\d+)\.\w+\.(\d+)\.(\w+)/i", $value, $matches)) {
            $fail("The :attribute is not a valid Time Condition field.");
            return;
        }

        // customConditions.0.conditions.0.value_start
        $groupIndex = $matches[0];
        $conditionIndex= $matches[1];
        $fieldName = $matches[2];
        unset($matches);

        if ($fieldName = 'value_stop'){
            $now = Carbon::now();
            switch ($data['customConditions'][$groupIndex]['conditions'][$conditionIndex][$fieldName])
            {
                case: 'year':
                    if (intval($value) < $now->year)
                    {
                        $fail("The :attribute cannot be earlier than the current year.");
                        return;
                    }
                    break;
                case: 'mday':
                    if (intval($value) < 1)
                    {
                        $fail("The :attribute cannot be less than one.");
                        return;
                    }
                    if (intval($value) > 31)
                    {
                        $fail("The :attribute cannot be more than 31.");
                        return;
                    }
                    break;
                case 'wday':
                    if (intval($value) < 1)
                    {
                        $fail("The :attribute cannot be less than one.");
                        return;
                    }
                    if (intval($value) > 7)
                    {
                        $fail("The :attribute cannot be more than seven.");
                        return;
                    }
                    break;
                case 'week':
                    if (intval($value) < 1)
                    {
                        $fail("The :attribute cannot be less than one.");
                        return;
                    }
                    if (intval($value) > 53)
                    {
                        $fail("The :attribute cannot be more than 53.");
                        return;
                    }
                    break;
                case 'mweek':
                    if (intval($value) < 1)
                    {
                        $fail("The :attribute cannot be less than one.");
                        return;
                    }
                    if (intval($value) > 5)
                    {
                        $fail("The :attribute cannot be more than five.");
                        return;
                    }
                    break;
                case 'hour':
                    if (intval($value) < 0)
                    {
                        $fail("The :attribute cannot be less than zero.");
                        return;
                    }
                    if (intval($value) > 23)
                    {
                        $fail("The :attribute cannot be more than 23.");
                        return;
                    }
                    break;
                case 'time-of-day':
                    list ($h, $m) = explode(':', $value, 2);
                    if (intval($h) < 0)
                    {
                        $fail("The :attribute cannot be less than zero.");
                        return;
                    }
                    if (intval($h) > 23)
                    {
                        $fail("The :attribute cannot be more than 23.");
                        return;
                    }
                    if (intval($m) < 0)
                    {
                        $fail("The :attribute cannot be less than zero.");
                        return;
                    }
                    if (intval($h) > 59)
                    {
                        $fail("The :attribute cannot be more than 59.");
                        return;
                    }
                    break;
                case 'date-time':
                    break;
                default;
                    $fail("The condition type of :attribute is not valid.");
                    return;
            }
        }
        elseif ($fieldName = 'value_stop'){

        }
    }
}
