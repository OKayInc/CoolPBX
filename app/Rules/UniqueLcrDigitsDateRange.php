<?php
namespace App\Rules;

use App\Models\Lcr;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueLcrDigitsDateRange implements ValidationRule
{
    protected ?string $currentUuid;
    protected string $dateStart;
    protected string $dateEnd;

    public function __construct(?string $currentUuid = null, ?string $dateStart = null, ?string $dateEnd = null)
    {
        $this->currentUuid = $currentUuid;
        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = $value;

        $conflict = Lcr::where('digits', $digits)
            ->when($this->currentUuid, function ($query) {
                $query->where('lcr_uuid', '!=', $this->currentUuid);
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('date_start', '<=', $this->dateEnd)
                      ->where('date_end', '>=', $this->dateStart);
                });
            })
            ->exists();

        if ($conflict) {
            $fail("The digits value '{$digits}' is already used for an overlapping date range.");
        }
    }
}
