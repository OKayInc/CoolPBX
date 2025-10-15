<?php

namespace App\Livewire;

use App\Http\Requests\FaxRequest;
use App\Repositories\FaxRepository;
use App\Repositories\FaxUserRepository;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FaxForm extends Component
{
    public $fax;
    public ?string $fax_uuid = null;
    public string $fax_name = '';
    public string $fax_extension = '';
    public ?string $accountcode = '';
    public ?string $fax_destination_number = '';
    public ?string $fax_prefix = '';
    public ?string $fax_email = '';
    public ?string $fax_caller_id_name = '';
    public ?string $fax_caller_id_number = '';
    public ?string $fax_forward_number = '';
    public ?int $fax_toll_allow = 0;
    public ?int $fax_send_channels = 0;
    public ?string $fax_description = '';

	//advanced settings
	public ?string $fax_email_connection_type = '';
	public ?string $fax_email_connection_host = '';
	public ?string $fax_email_connection_port = '';
	public ?string $fax_email_connection_security = '';
	public ?string $fax_email_connection_validate = '';
	public ?string $fax_email_connection_username = '';
	public ?string $fax_email_connection_password = '';
	public ?string $fax_email_connection_mailbox = '';
	public ?string $fax_email_inbound_subject_tag = '';
	public ?string $fax_email_outbound_subject_tag = '';
	public ?string $fax_email_outbound_authorized_senders = '';

	public ?array $faxEmails = [];
	public ?array $faxEmailOutboundSenders = [];

    protected $faxRepository;
    protected $faxUserRepository;

    public $faxUsers = [];
    public $availableUsers = [];
    public $availableUser = '';

    public function boot(FaxRepository $faxRepository, FaxUserRepository $faxUserRepository)
    {
        $this->faxRepository = $faxRepository;
        $this->faxUserRepository = $faxUserRepository;
    }

    public function rules()
    {
        $request = new FaxRequest();

        return $request->rules();
    }

    public function mount($fax = null, $fax_users = [], $available_users = []): void
    {
        if($fax)
        {
            $this->fax = $fax;
            $this->fax_uuid = $fax->fax_uuid;
            $this->fax_name = $fax->fax_name;
            $this->fax_extension = $fax->fax_extension;
            $this->accountcode = $fax->accountcode;
            $this->fax_destination_number = $fax->fax_destination_number;
            $this->fax_prefix = $fax->fax_prefix;
            $this->fax_email = $fax->fax_email;
            $this->fax_caller_id_name = $fax->fax_caller_id_name;
            $this->fax_caller_id_number = $fax->fax_caller_id_number;
            $this->fax_forward_number = $fax->fax_forward_number;
            $this->fax_toll_allow = $fax->fax_toll_allow;
            $this->fax_send_channels = $fax->fax_send_channels;
            $this->fax_description = $fax->fax_description;

			//advanced settings
			$this->fax_email_connection_type = $fax->fax_email_connection_type;
			$this->fax_email_connection_host = $fax->fax_email_connection_host;
			$this->fax_email_connection_port = $fax->fax_email_connection_port;
			$this->fax_email_connection_security = $fax->fax_email_connection_security;
			$this->fax_email_connection_validate = $fax->fax_email_connection_validate;
			$this->fax_email_connection_username = $fax->fax_email_connection_username;
			$this->fax_email_connection_password = $fax->fax_email_connection_password;
			$this->fax_email_connection_mailbox = $fax->fax_email_connection_mailbox;
			$this->fax_email_inbound_subject_tag = $fax->fax_email_inbound_subject_tag;
			$this->fax_email_outbound_subject_tag = $fax->fax_email_outbound_subject_tag;
			$this->fax_email_outbound_authorized_senders = '';

			$this->faxEmails = $fax->fax_email ? explode(',', $fax->fax_email) : [];
			$this->faxEmailOutboundSenders = $fax->fax_email_outbound_authorized_senders ? explode(',', $fax->fax_email_outbound_authorized_senders) : [];

            $this->faxUsers = [];

            foreach($fax_users as $fax_user)
            {
                $this->faxUsers[] = [
                    'user_uuid' => $fax_user->user_uuid,
                    'username' => $fax_user->username,
                ];
            }

        }
		else
		{
			$this->accountcode = getAccountCode();
		}
    }

	public function addFaxEmail(): void
	{
		$this->faxEmails[] = '';
	}

	public function removeFaxEmail(int $index): void
	{
		unset($this->faxEmails[$index]);

		$this->faxEmails = array_values($this->faxEmails);
	}

	public function addFaxEmailOutboundSender(): void
	{
        $this->faxEmailOutboundSenders[] = '';
	}

	public function removeFaxEmailOutboundSender(int $index): void
	{
		unset($this->faxEmailOutboundSenders[$index]);

		$this->faxEmailOutboundSenders = array_values($this->faxEmailOutboundSenders);
	}

    public function addFaxUser(): void
	{
        $availableUser = collect($this->availableUsers)->firstWhere('user_uuid', $this->availableUser);

        if(collect($this->faxUsers)->contains('user_uuid', $availableUser->user_uuid))
        {
            return;
        }

        if($availableUser)
        {
            $this->faxUsers[] = [
                'user_uuid' => $availableUser->user_uuid,
                'username' => $availableUser->username,
            ];
        }
	}

	public function removeFaxUser(int $index): void
	{
		unset($this->faxUsers[$index]);

		$this->faxUsers = array_values($this->faxUsers);
	}

    public function save(): void
    {
        $this->validate();

        $faxData = [
            'fax_name' => $this->fax_name,
            'fax_extension' => $this->fax_extension,
            'accountcode' => $this->accountcode,
            'fax_destination_number' => $this->fax_destination_number,
            'fax_prefix' => $this->fax_prefix,
            'fax_email' => implode(",", $this->faxEmails),
            'fax_caller_id_name' => $this->fax_caller_id_name,
            'fax_caller_id_number' => $this->fax_caller_id_number,
            'fax_forward_number' => $this->fax_forward_number,
            'fax_toll_allow' => $this->fax_toll_allow,
            'fax_send_channels' => $this->fax_send_channels,
            'fax_description' => $this->fax_description,

			//advanced settings
			'fax_email_connection_type' => $this->fax_email_connection_type,
			'fax_email_connection_host' => $this->fax_email_connection_host,
			'fax_email_connection_port' => $this->fax_email_connection_port,
			'fax_email_connection_security' => $this->fax_email_connection_security,
			'fax_email_connection_validate' => $this->fax_email_connection_validate,
			'fax_email_connection_username' => $this->fax_email_connection_username,
			'fax_email_connection_password' => $this->fax_email_connection_password,
			'fax_email_connection_mailbox' => $this->fax_email_connection_mailbox,
			'fax_email_inbound_subject_tag' => $this->fax_email_inbound_subject_tag,
			'fax_email_outbound_subject_tag' => $this->fax_email_outbound_subject_tag,
			'fax_email_outbound_authorized_senders' => implode(',', $this->faxEmailOutboundSenders),
        ];

        if($this->fax)
        {
            $updated = $this->faxRepository->update($this->fax, $faxData);

            if(!$updated)
            {
                session()->flash('error', 'Failed to update fax.');

			    return;
            }
        }
        else
        {
            $this->fax = $this->faxRepository->create($faxData);

            session()->flash('message', 'Fax created successfully.');
        }

        $this->faxUserRepository->deleteAll($this->fax);

        $this->faxUserRepository->create($this->fax, $this->faxUsers);

        redirect()->route('faxes.edit', $this->fax->fax_uuid);
    }

    public function render(): View
    {
        return view('livewire.fax-form');
    }
}
