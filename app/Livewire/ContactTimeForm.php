<?php

namespace App\Livewire;

use App\Http\Requests\ContactTimeRequest;
use App\Models\Contact;
use App\Models\ContactTime;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class ContactTimeForm extends Component
{
    public $contactUuid;
    public $times = [];
    public $removedTimes = [];

    protected $listeners = [
        'timesSaved' => 'save'
    ];

    public function mount($contactUuid): void
    {
        $this->contactUuid = $contactUuid;
        $this->loadTimes();
    }

    public function rules(): array
    {
        $request = new ContactTimeRequest();
        return $request->rules();
    }

    public function loadTimes(): void
    {
        $contact = Contact::where('contact_uuid', $this->contactUuid)
            ->with(['times' => function($query) {
                $query->orderBy('time_start', 'desc');
            }])
            ->first();

        if ($contact && $contact->times->count() > 0) {
            $this->times = $contact->times->map(function ($time) {
                return [
                    'contact_time_uuid' => $time->contact_time_uuid,
                    'time_start' => $time->time_start ? $time->time_start->format('Y-m-d\TH:i') : null,
                    'time_stop' => $time->time_stop ? $time->time_stop->format('Y-m-d\TH:i') : null,
                    'time_description' => $time->time_description,
                    'user_uuid' => $time->user_uuid,
                    'insert_date' => $time->insert_date,
                    'insert_user' => $time->insert_user ?? 'Unknown',
                ];
            })->toArray();
        } else {
            $this->addTime();
        }
    }

    public function addTime()
    {
        if (!auth()->user()->hasPermission('contact_time_add')) {
            session()->flash('message', 'You do not have permission to add time entries.');
            return;
        }

        $this->times[] = [
            'contact_time_uuid' => null,
            'time_start' => now()->format('Y-m-d\TH:i'),
            'time_stop' => null,
            'time_description' => '',
            'user_uuid' => auth()->user()->user_uuid,
            'insert_date' => null,
            'insert_user' => null,
        ];
    }

    public function removeTime($index)
    {
        if (!auth()->user()->hasPermission('contact_time_delete')) {
            session()->flash('message', 'You do not have permission to delete time entries.');
            return;
        }

        if (isset($this->times[$index]['contact_time_uuid']) && !empty($this->times[$index]['contact_time_uuid'])) {
            $this->removedTimes[] = $this->times[$index];
        }

        unset($this->times[$index]);
        $this->times = array_values($this->times);
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            foreach ($this->removedTimes as $removedTime) {
                if (isset($removedTime['contact_time_uuid'])) {
                    ContactTime::where('contact_time_uuid', $removedTime['contact_time_uuid'])->delete();
                }
            }

            foreach ($this->times as $time) {
                if (!empty($time['time_start'])) {
                    $data = [
                        'contact_uuid' => $this->contactUuid,
                        'domain_uuid' => Session::get('domain_uuid'),
                        'user_uuid' => $time['user_uuid'] ?? auth()->user()->user_uuid,
                        'time_start' => Carbon::parse($time['time_start']),
                        'time_stop' => !empty($time['time_stop']) ? Carbon::parse($time['time_stop']) : null,
                        'time_description' => $time['time_description'],
                    ];

                    if (isset($time['contact_time_uuid']) && !empty($time['contact_time_uuid'])) {
                        $contactTime = ContactTime::where('contact_time_uuid', $time['contact_time_uuid'])->first();
                        if ($contactTime) {
                            $contactTime->update($data);
                        }
                    } else {
                        ContactTime::create($data);
                    }
                }
            }

            DB::commit();

            $this->loadTimes();

            $this->removedTimes = [];

            session()->flash('message', 'Contact saved successfully.');
            redirect()->route('contacts.index');

        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('message', 'Error: ' . $e->getMessage());
            throw $e;
        }
    }


    public function render()
    {
        return view('livewire.contact-time-form');
    }
}