<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DomainSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'domain_setting_category' => 'required|max:255',
            'domain_setting_subcategory' => 'required|max:255',
            'domain_setting_name' => 'required|max:255',
            'domain_setting_value' => 'nullable',
            'domain_setting_order' => 'required|integer',
            'domain_setting_enabled' => 'required|in:true,false',
            'domain_setting_description' => 'nullable|max:1024',
        ];
    }
}
