<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaxSendRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
            'fax_header' => "bail|nullable|string|max:255",
            'fax_sender' => "bail|nullable|string|max:255",
            'fax_recipient' => "bail|nullable|string|max:255",
            'fax_numbers' => "bail|required|array|min:1",
			'fax_numbers.*' => 'required|string',
            'fax_files' => "bail|required|array|min:1",
			'fax_files.*' => 'required|file|mimes:pdf,tiff|max:2048',
            'fax_resolution' => "bail|nullable|in:normal,fine,superfine",
            'fax_page_size' => "bail|nullable|in:letter,legal,a4",
            'fax_subject' => "bail|nullable|string|max:255",
            'fax_message' => "bail|nullable|string|max:255",
            'fax_footer' => "bail|nullable|string",
		];
	}
}
