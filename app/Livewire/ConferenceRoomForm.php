<?php

namespace App\Livewire;

use App\Http\Requests\ConferenceRoomUserRequest;
use App\Http\Requests\ConferenceRoomRequest;
use App\Repositories\ConferenceRoomRepository;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Repositories\ConferenceRoomUserRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ConferenceRoomForm extends Component
{
    public $conferenceRoom;

	public ?string $conference_room_uuid = null;
	public ?string $conference_center_uuid = null;
	public string $conference_room_name = '';
	public bool $record = true;
	public ?int $moderator_pin = 0;
	public ?int $participant_pin = 0;
	public ?string $profile = '';
	public ?int $max_members = 0;
	public string $start_datetime = '';
	public string $stop_datetime = '';
	public bool $wait_mod = true;
	public bool $moderator_endconf = true;
	public bool $announce_name = true;
	public bool $announce_count = true;
	public bool $announce_recording = true;
	public bool $sounds = true;
	public bool $mute = true;
	public string $email_address = '';
	public string $account_code = '';
	public bool $enabled = true;
	public string $description = '';

    public $conferenceCenters = [];
    public $conferenceProfiles = [];

    public ?array $conferenceRoomUsers = [];

    public array $conferenceRoomUsersToDelete = [];

    public $users = [];

    protected $conferenceRoomRepository;
    protected $conferenceRoomUserRepository;

    public function boot(ConferenceRoomRepository $conferenceRoomRepository, ConferenceRoomUserRepository $conferenceRoomUserRepository)
    {
        $this->conferenceRoomRepository = $conferenceRoomRepository;
        $this->conferenceRoomUserRepository = $conferenceRoomUserRepository;
    }

    public function rules()
    {
        $request = new ConferenceRoomRequest();

        return $request->rules($this->conference_room_uuid);
    }

    public function mount($conferenceRoom = null, $conferenceCenters = [], $conferenceProfiles = [], $users = []): void
    {
        $this->conferenceCenters = $conferenceCenters;
        $this->conferenceProfiles = $conferenceProfiles;
        $this->users = $users;

        if($conferenceRoom)
        {
            $this->conferenceRoom = $conferenceRoom;
            $this->conference_center_uuid = $conferenceRoom->conference_center_uuid;
            $this->conference_room_name = $conferenceRoom->conference_room_name;
            $this->record = $conferenceRoom->record ?? false;
            $this->moderator_pin = $conferenceRoom->moderator_pin ?? '';
            $this->participant_pin = $conferenceRoom->participant_pin ?? '';
            $this->profile = $conferenceRoom->profile ?? '';
            $this->max_members = $conferenceRoom->max_members ?? 0;
            $this->start_datetime = $conferenceRoom->start_datetime ?? '';
            $this->stop_datetime = $conferenceRoom->stop_datetime ?? '';
            $this->wait_mod = $conferenceRoom->wait_mod ?? false;
            $this->moderator_endconf = $conferenceRoom->moderator_endconf ?? false;
            $this->announce_name = $conferenceRoom->announce_name ?? false;
            $this->announce_count = $conferenceRoom->announce_count ?? false;
            $this->announce_recording = $conferenceRoom->announce_recording ?? false;
            $this->sounds = $conferenceRoom->sounds ?? false;
            $this->mute = $conferenceRoom->mute ?? false;
            $this->email_address = $conferenceRoom->email_address ?? '';
            $this->account_code = $conferenceRoom->account_code ?? '';
            $this->enabled = $conferenceRoom->enabled ?? false;
            $this->description = $conferenceRoom->description ?? '';

            foreach($conferenceRoom->users as $conferenceRoomUser)
            {
                $this->conferenceRoomUsers[] = [
                    'conference_room_user_uuid' => $conferenceRoomUser->conference_room_user_uuid,
                    'user_uuid' => $conferenceRoomUser->user_uuid,
                ];
            }
        }

        if($conferenceRoom && empty($this->conferenceRoomUsers))
        {
            $this->addConferenceRoomUser();
        }
    }

    public function addConferenceRoomUser(): void
    {
        $this->conferenceRoomUsers[] = [
            'conference_room_user_uuid' => '',
            'user_uuid' => '',
        ];
    }

    public function removeConferenceRoomUser($index): void
    {
        if(isset($this->conferenceRoomUsers[$index]['conference_room_user_uuid']) && !empty($this->conferenceRoomUsers[$index]['conference_room_user_uuid']))
        {
            $this->conferenceRoomUsersToDelete[] = $this->conferenceRoomUsers[$index]['conference_room_user_uuid'];
        }

        unset($this->conferenceRoomUsers[$index]);

        $this->conferenceRoomUsers = array_values($this->conferenceRoomUsers);
    }

    public function save(): void
    {
        $this->validate();

        $userRules = ConferenceRoomUserRequest::rules();

        foreach($this->conferenceRoomUsers as $index => $conferenceRoomUser)
        {
            $validator = Validator::make($conferenceRoomUser, $userRules);

            try
            {
                $validator->validate();
            }
            catch (ValidationException $e)
            {
                $errors = [];

                foreach ($e->errors() as $field => $messages)
                {
                    $errors["conferenceRoomUsers.{$index}.{$field}"] = $messages;
                }

                throw ValidationException::withMessages($errors);
            }
        }

        $filteredConferenceRoomUsers = collect($this->conferenceRoomUsers)->filter(function ($conferenceRoomUser)
        {
            return !empty($conferenceRoomUser['user_uuid']);
        })->toArray();

        $conferenceRoomData = [
            'conference_center_uuid' => $this->conference_center_uuid,
            'conference_room_name' => $this->conference_room_name,
            'record' => $this->record,
            'moderator_pin' => $this->moderator_pin,
            'participant_pin' => $this->participant_pin,
            'profile' => $this->profile,
            'max_members' => $this->max_members,
            'start_datetime' => $this->start_datetime,
            'stop_datetime' => $this->stop_datetime,
            'wait_mod' => $this->wait_mod,
            'moderator_endconf' => $this->moderator_endconf,
            'announce_name' => $this->announce_name,
            'announce_count' => $this->announce_count,
            'announce_recording' => $this->announce_recording,
            'sounds' => $this->sounds,
            'mute' => $this->mute,
            'email_address' => $this->email_address,
            'account_code' => $this->account_code,
            'enabled' => $this->enabled,
            'description' => $this->description,
        ];

        if($this->conferenceRoom)
        {
            $updated = $this->conferenceRoomRepository->update($this->conferenceRoom, $conferenceRoomData);

            if(!$updated)
            {
                session()->flash('error', 'Failed to update conference room.');

                return;
            }

            $this->conferenceRoomUserRepository->update($this->conferenceRoom, $filteredConferenceRoomUsers);

            if(!empty($this->conferenceRoomUsersToDelete))
            {
                $this->conferenceRoomUserRepository->delete($this->conferenceRoomUsersToDelete);
            }

            session()->flash('message', 'Conference Room updated successfully.');
        }
        else
        {
            $conferenceRoomData['conference_room_uuid'] = Str::uuid();

            $this->conferenceRoom = $this->conferenceRoomRepository->create($conferenceRoomData);

            $this->conferenceRoomUserRepository->create($this->conferenceRoom, $filteredConferenceRoomUsers);

            session()->flash('message', 'ConferenceRoom created successfully.');
        }

        redirect()->route('conference_rooms.edit', $this->conferenceRoom->conference_room_uuid);
    }

    public function render(): View
    {
        return view('livewire.conference-room-form');
    }
}
