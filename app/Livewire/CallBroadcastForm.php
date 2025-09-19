<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Repositories\CallBroadcastRepository;
use App\Models\CallBroadcast;
use App\Facades\Setting;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CallBroadcastForm extends Component
{
    use WithFileUploads;

    public ?string $callBroadcastUuid = null;
    public $callBroadcast;
    public $isEditing = false;

    public ?string $domain_uuid = null;
    public string $broadcast_name = '';
    public ?string $broadcast_description = '';
    public ?string $broadcast_start_time = '';
    public ?string $broadcast_timeout = '';
    public ?string $broadcast_concurrent_limit = '';
    public ?string $recording_uuid = null;
    public ?string $broadcast_caller_id_name = '';
    public ?string $broadcast_caller_id_number = '';
    public ?string $broadcast_destination_type = '';
    public ?string $broadcast_destination_data = '';
    public ?string $broadcast_phone_numbers = '';
    public $broadcast_phone_numbers_file = null;
    public string $broadcast_avmd = 'false';
    public ?string $broadcast_accountcode = '';
    public ?string $broadcast_toll_allow = '';

    public array $recordings = [];
    public array $destinations = [];
    public array $availableDomains = [];

    protected $callBroadcastRepository;

    public function boot(CallBroadcastRepository $callBroadcastRepository)
    {
        $this->callBroadcastRepository = $callBroadcastRepository;
    }

    public function rules()
    {
        return [
            'broadcast_name' => 'required|string|max:255',
            'broadcast_description' => 'nullable|string|max:255',
            'broadcast_start_time' => 'nullable|date_format:Y-m-d H:i',
            'broadcast_timeout' => 'nullable|integer|min:1',
            'broadcast_concurrent_limit' => 'nullable|integer|min:1',
            'recording_uuid' => 'nullable|string',
            'broadcast_caller_id_name' => 'nullable|string|max:255',
            'broadcast_caller_id_number' => 'nullable|string|max:255',
            'broadcast_destination_data' => 'nullable|string|max:255',
            'broadcast_phone_numbers' => 'nullable|string',
            'broadcast_phone_numbers_file' => 'nullable|file|mimes:csv,txt|max:2048',
            'broadcast_avmd' => 'required|in:true,false',
            'broadcast_accountcode' => 'nullable|string|max:255',
            'broadcast_toll_allow' => 'nullable|string|max:255',
        ];
    }

    public function mount($callBroadcastUuid = null)
    {
        $this->callBroadcastUuid = $callBroadcastUuid;
        $this->isEditing = !is_null($callBroadcastUuid);

        $this->loadDropdownData();

        if ($this->isEditing) {
            $this->loadCallBroadcast();
        } else {
            $this->initializeDefaults();
        }
    }

    protected function loadCallBroadcast()
    {
        $this->callBroadcast = $this->callBroadcastRepository->findByUuid($this->callBroadcastUuid, true);

        if (!$this->callBroadcast) {
            session()->flash('error', 'Call broadcast not found.');
            return redirect()->route('call_broadcasts.index');
        }

        $this->domain_uuid = $this->callBroadcast->domain_uuid;
        $this->broadcast_name = $this->callBroadcast->broadcast_name ?? '';
        $this->broadcast_description = $this->callBroadcast->broadcast_description ?? '';
        $this->broadcast_timeout = $this->callBroadcast->broadcast_timeout;
        $this->broadcast_concurrent_limit = $this->callBroadcast->broadcast_concurrent_limit;
        $this->recording_uuid = $this->callBroadcast->recording_uuid;
        $this->broadcast_caller_id_name = $this->callBroadcast->broadcast_caller_id_name ?? '';
        $this->broadcast_caller_id_number = $this->callBroadcast->broadcast_caller_id_number ?? '';
        $this->broadcast_destination_type = $this->callBroadcast->broadcast_destination_type ?? '';
        $this->broadcast_destination_data = $this->callBroadcast->broadcast_destination_data ?? '';
        $this->broadcast_phone_numbers = $this->callBroadcast->broadcast_phone_numbers ?? '';
        $this->broadcast_avmd = $this->callBroadcast->broadcast_avmd ?? 'false';
        $this->broadcast_accountcode = $this->callBroadcast->broadcast_accountcode ?? '';
        $this->broadcast_toll_allow = $this->callBroadcast->broadcast_toll_allow ?? '';

        if ($this->callBroadcast->broadcast_start_time) {
            $this->broadcast_start_time = $this->callBroadcastRepository->formatStartTimeForDisplay($this->callBroadcast);
        }
    }

    protected function initializeDefaults()
    {
        $user = auth()->user();
        $this->domain_uuid = $user->domain_uuid;
        $this->broadcast_avmd = 'false';
        
        // Set default account code based on user permissions
        if ($user->hasGroup('superadmin')) {
            $this->broadcast_accountcode = '';
        } else {
            $this->broadcast_accountcode = $user->domain_name;
        }
    }

    protected function loadDropdownData()
    {
        $user = auth()->user();

        $this->recordings = $this->callBroadcastRepository->getRecordings($user->domain_uuid)->toArray();
        
        $this->destinations = $this->callBroadcastRepository->getDestinations($user->domain_uuid)->toArray();

        if ($user->hasPermission('call_broadcast_domain')) {
            $this->availableDomains = $this->callBroadcastRepository->getAvailableDomains()->toArray();
        }

        if (!$this->isEditing) {
            $this->domain_uuid = Session::get('domain_uuid', $user->domain_uuid);
        }
    }

    public function updatedBroadcastPhoneNumbersFile()
    {
        if ($this->broadcast_phone_numbers_file) {
            $this->validate([
                'broadcast_phone_numbers_file' => 'file|mimes:csv,txt|max:2048'
            ]);
        }
    }

    public function copyCallBroadcast()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $newName = $this->broadcast_name . ' (Copy)';
            $copiedBroadcast = $this->callBroadcastRepository->copy($this->callBroadcastUuid, $newName);

            session()->flash('success', 'Call broadcast copied successfully.');
            return redirect()->route('call-broadcasts.edit', $copiedBroadcast->call_broadcast_uuid);
        } catch (\Exception $e) {
            session()->flash('error', 'Error copying call broadcast: ' . $e->getMessage());
        }
    }

    public function startBroadcast()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            session()->flash('success', 'Call broadcast started successfully.');
            return redirect()->route('call-broadcasts.send', $this->callBroadcastUuid);
        } catch (\Exception $e) {
            session()->flash('error', 'Error starting broadcast: ' . $e->getMessage());
        }
    }

    public function stopBroadcast()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            session()->flash('success', 'Call broadcast stopped successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error stopping broadcast: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $broadcastData = [
                'domain_uuid' => $this->domain_uuid,
                'broadcast_name' => $this->broadcast_name,
                'broadcast_description' => $this->broadcast_description,
                'broadcast_start_time' => $this->broadcast_start_time,
                'broadcast_timeout' => $this->broadcast_timeout,
                'broadcast_concurrent_limit' => $this->broadcast_concurrent_limit,
                'recording_uuid' => $this->recording_uuid,
                'broadcast_caller_id_name' => $this->broadcast_caller_id_name,
                'broadcast_caller_id_number' => $this->broadcast_caller_id_number,
                'broadcast_destination_type' => $this->broadcast_destination_type,
                'broadcast_destination_data' => $this->broadcast_destination_data,
                'broadcast_phone_numbers' => $this->broadcast_phone_numbers,
                'broadcast_avmd' => $this->broadcast_avmd,
                'broadcast_accountcode' => $this->broadcast_accountcode,
                'broadcast_toll_allow' => $this->broadcast_toll_allow,
            ];

            if ($this->broadcast_phone_numbers_file) {
                $broadcastData['broadcast_phone_numbers_file'] = [
                    'tmp_name' => $this->broadcast_phone_numbers_file->getPathname(),
                    'size' => $this->broadcast_phone_numbers_file->getSize(),
                    'type' => $this->broadcast_phone_numbers_file->getMimeType(),
                ];
            }

            if ($this->isEditing) {
                $callBroadcast = $this->callBroadcastRepository->update($this->callBroadcastUuid, $broadcastData);
                session()->flash('success', 'Call broadcast updated successfully.');
                return redirect()->route('call-broadcasts.edit', $callBroadcast->call_broadcast_uuid);
            } else {
                $callBroadcast = $this->callBroadcastRepository->create($broadcastData);
                session()->flash('success', 'Call broadcast created successfully.');
                return redirect()->route('call-broadcasts.edit', $callBroadcast->call_broadcast_uuid);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving call broadcast: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $this->callBroadcastRepository->delete($this->callBroadcastUuid);
            session()->flash('success', 'Call broadcast deleted successfully.');
            return redirect()->route('call_broadcasts.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting call broadcast: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.call-broadcast-form');
    }
}