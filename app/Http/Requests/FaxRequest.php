<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaxRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		$rules = [
			"fax_name" => "bail|required|string|max:30",
			"fax_extension" => "bail|required|string|max:15",
			"accountcode" => "bail|nullable|string|max:80",
			"fax_destination_number" => "bail|nullable|alpha_num|max:255",
			"fax_prefix" => "bail|nullable|string|max:12",
			"fax_email" => "bail|nullable|string|max:255",
			"fax_caller_id_name" => "bail|nullable|string|max:40",
			"fax_caller_id_number" => "bail|nullable|alpha_num|min:0",
			"fax_forward_number" => "bail|nullable|alpha_num|max:20",
			"fax_toll_allow" => "bail|nullable|integer|min:0",
			"fax_send_greeting" => "bail|nullable|string",
			"fax_send_channels" => "bail|nullable|integer|min:0",
			"fax_description" => "bail|nullable|string|max:255",
		];

		$user = auth()->user();

		if($user->hasPermission('fax_extension_advanced') && config("fax.imap_open_enabled") && config("fax.files_remote_enabled"))
		{
			$extra_rules = [
				"fax_email_connection_type" => "bail|nullable|string|max:255",
				"fax_email_connection_host" => "bail|nullable|string|max:255",
				"fax_email_connection_port" => "bail|nullable|string|max:255",
				"fax_email_connection_security" => "bail|nullable|string|max:255",
				"fax_email_connection_validate" => "bail|nullable|string|max:255",
				"fax_email_connection_username" => "bail|nullable|string|max:255",
				"fax_email_connection_password" => "bail|nullable|string|max:255",
				"fax_email_connection_mailbox" => "bail|nullable|string|max:255",
				"fax_email_inbound_subject_tag" => "bail|nullable|string|max:255",
				"fax_email_outbound_subject_tag" => "bail|nullable|string|max:255",
				"fax_email_outbound_authorized_senders" => "bail|nullable|string|max:255",
			];

			$rules = array_merge($rules, $extra_rules);
		}

		return $rules;
	}
}
