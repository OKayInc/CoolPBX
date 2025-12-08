<?php

namespace App\Livewire;

use App\Facades\Setting;
use App\Http\Requests\FaxSendRequest;
use App\Services\FaxService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FaxSend extends Component
{
	use WithFileUploads;

    public $fax;
    public ?string $fax_uuid = null;
    public string $fax_header = '';
    public string $fax_sender = '';
    public ?string $fax_recipient = '';
    public ?array $fax_numbers = [];
    public ?array $fax_files = [];
    public ?string $fax_resolution = '';
    public ?string $fax_page_size = '';
    public ?string $fax_subject = '';
    public ?string $fax_message = '';
    public ?string $fax_footer = '';

    protected $faxService;

	public $contacts = [];

    public function boot(FaxService $faxService)
    {
        $this->faxService = $faxService;
    }

    public function rules()
    {
        $request = new FaxSendRequest();

        return $request->rules();
    }

    public function mount($fax = null, $contacts = []): void
    {
		$this->fax = $fax;
		$this->contacts = $contacts;

		$this->fax_resolution = Setting::getSetting('fax', 'resolution', 'text');
		$this->fax_page_size = Setting::getSetting('fax', 'page_size', 'text');
		$this->fax_footer = Setting::getSetting('fax', 'cover_footer', 'text');

		$this->addFaxNumber();
		$this->addFaxFile();
    }

	public function addFaxNumber(): void
	{
		$this->fax_numbers[] = '';
	}

	public function removeFaxNumber(int $index): void
	{
		unset($this->fax_numbers[$index]);

		$this->fax_numbers = array_values($this->fax_numbers);

		if(empty($this->fax_numbers))
		{
			$this->addFaxNumber();
		}
	}

	public function addFaxFile(): void
	{
		$this->fax_files[] = '';
	}

	public function removeFaxFile(int $index): void
	{
		unset($this->fax_files[$index]);

		$this->fax_files = array_values($this->fax_files);

		if(empty($this->fax_files))
		{
			$this->addFaxFile();
		}
	}

    public function save()
    {
        $this->validate();

        $faxData = [
            'fax_header' => $this->fax_header,
            'fax_sender' => $this->fax_sender,
            'fax_recipient' => $this->fax_recipient,
            'fax_numbers' => $this->fax_numbers,
            'fax_files' => $this->fax_files,
            'fax_resolution' => $this->fax_resolution,
            'fax_page_size' => $this->fax_page_size,
            'fax_subject' => $this->fax_subject,
            'fax_message' => $this->fax_message,
            'fax_footer' => $this->fax_footer,
        ];

		$sent = $this->faxService->sendFax($this->fax, $faxData);

		if($sent)
		{
            session()->flash('success', 'Fax sent');
		}
        else
        {
            session()->flash('error', 'Fax not sent');
        }

        return redirect()->route('faxes.send', $this->fax);
    }

    public function render(): View
    {
        return view('livewire.fax-send');
    }
}
