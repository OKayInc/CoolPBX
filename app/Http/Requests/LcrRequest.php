<?php

namespace App\Http\Requests;

use App\Rules\UniqueLcrDigitsDateRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LcrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lcr = $this->route('lcr');

        $lcrUuid = $lcr instanceof \App\Models\Lcr ? $lcr->lcr_uuid : null;

        $dateStart = $this->input('date_start');
        $dateEnd = $this->input('date_end');

        return [
            'origination_digits' => 'bail|nullable|string|max:255',
            'lcr_direction'      => 'bail|required|in:inbound,outbound,local',
            'rate'               => 'bail|nullable|numeric|min:0',
            'connect_rate'       => 'bail|nullable|numeric|min:0',
            'connect_increment'  => 'bail|nullable|integer|min:0',
            'talk_increment'     => 'bail|nullable|integer|min:0',
            'intrastate_rate'    => 'bail|nullable|numeric|min:0',
            'intralata_rate'     => 'bail|nullable|numeric|min:0',
            'lead_strip'         => 'bail|nullable|integer|min:0',
            'trail_strip'        => 'bail|nullable|integer|min:0',
            'prefix'             => 'bail|nullable|string|max:255',
            'suffix'             => 'bail|nullable|string|max:255',
            'lcr_profile'        => 'bail|nullable|string|max:255',
            'date_start'         => 'bail|required|date',
            'date_end'           => 'bail|required|date|after_or_equal:date_start',
            'quality'            => 'bail|nullable|numeric|min:0',
            'reliability'        => 'bail|nullable|numeric|min:0',
            'cid'                => 'bail|nullable|string|max:255',
            'enabled'            => 'bail|nullable|string|in:true,false',
            'description'        => 'bail|nullable|string|max:500',
            'carrier_uuid'       => 'bail|nullable|uuid|exists:App\Models\Carrier,carrier_uuid',
            'digits' => [
                'bail',
                'required',
                'string',
                'max:255',
                new UniqueLcrDigitsDateRange($lcrUuid, $dateStart, $dateEnd),
            ],
            'currency' => [
                'bail',
                'nullable',
                'string',
                Rule::in(array_merge(config('currencies'), ['%']))
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'date_start' => $this->input('date_start') ?: now()->toDateString(),
            'date_end' => $this->input('date_end') ?: now()->addYears(10)->toDateString(),
        ]);
    }
}
