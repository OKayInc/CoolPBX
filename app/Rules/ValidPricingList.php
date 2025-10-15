<?php

namespace App\Rules;

use App\Models\Lcr;
use App\Models\Domain;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ValidPricingList implements ValidationRule
{
    private bool $buying = false;
    private bool $selling = false;

    public function __construct(int $flag = 3)
    {
        $this->selling = $flag & config('freeswitch.SELLING_PRICING_LIST');
        $this->buying = $flag & config('freeswitch.BUYING_PRICING_LIST');
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $pl_query = Lcr::select('lcr_profile')->distinct();
        if ($buying && !$selling)
        {
            $pl_query = $pl_query->whereNotNull('carrier_uuid');
        }

        if (!$buying && $selling)
        {
            $pl_query = $pl_query->whereNull('carrier_uuid');
        }

        $pl_count = $pl_query->count();
        if(App::hasDebugModeEnabled()){
            Log::debug('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] $pl_query: '.$pl_query->toRawSql());
        }

        if ($pl_count == 0)
        {
            $fail('The :attribute does not have a valid pricing list value.');
        }
    }
}
