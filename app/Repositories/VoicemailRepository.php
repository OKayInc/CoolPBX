<?php

namespace App\Repositories;

use App\Facades\Setting;
use App\Models\Voicemail;
use App\Models\VoicemailOption;
use App\Models\VoicemailDestination;
use App\Models\VoicemailGreeting;
use App\Models\VoicemailMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class VoicemailRepository
{

    /**
     * Find a voicemail by UUID
     *
     * @param string $uuid
     * @return Voicemail|null
     */
    public function find(string $uuid): ?Voicemail
    {
        return Voicemail::where('voicemail_uuid', $uuid)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->with(['voicemailoptionss', 'voicemailDestinations', 'voicemailmessages', 'voicemailGreetings'])
            ->first();
    }

    /**
     * Find a voicemail by ID
     *
     * @param string $voicemailId
     * @return Voicemail|null
     */
    public function findByVoicemailId(string $voicemailId): ?Voicemail
    {
        return Voicemail::where('voicemail_id', $voicemailId)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->first();
    }

    /**
     * Create a new voicemail
     *
     * @param array $data
     * @return Voicemail
     */
    public function create(array $data): Voicemail
    {
        DB::beginTransaction();

        try {
            $voicemailUuid = $data['voicemail_uuid'] ?? Str::uuid()->toString();

            if (
                !isset($data['voicemail_transcription_enabled'])
                && !Auth::user()->hasPermission('voicemail_transcription_enabled')
            ) {
                $data['voicemail_transcription_enabled'] = config('voicemail.transcription_enabled_default', 'false');
            }

            $voicemail = Voicemail::create([
                'voicemail_uuid' => $voicemailUuid,
                'domain_uuid' => Auth::user()->domain_uuid,
                'voicemail_id' => $data['voicemail_id'],
                'voicemail_password' => $data['voicemail_password'],
                'greeting_id' => $data['greeting_id'] ?? null,
                'voicemail_alternate_greet_id' => $data['voicemail_alternate_greet_id'] ?? null,
                'voicemail_mail_to' => str_replace(' ', '', $data['voicemail_mail_to'] ?? ''),
                'voicemail_sms_to' => $data['voicemail_sms_to'] ?? null,
                'voicemail_transcription_enabled' => $data['voicemail_transcription_enabled'] ?? 'false',
                'voicemail_tutorial' => $data['voicemail_tutorial'] ?? 'false',
                'voicemail_file' => $data['voicemail_file'] ?? null,
                'voicemail_local_after_email' => $data['voicemail_local_after_email'] ?? 'true',
                'voicemail_enabled' => $data['voicemail_enabled'] ?? 'true',
                'voicemail_description' => $data['voicemail_description'] ?? null,
            ]);

            $this->createVoicemailDirectory($data['voicemail_id']);

            if (!empty($data['voicemail_options']) && Auth::user()->hasPermission('voicemail_option_add')) {
                $this->saveOptions($voicemail, $data['voicemail_options']);
            }

            if (!empty($data['voicemail_destinations']) && Auth::user()->hasPermission('voicemail_forward')) {
                $this->saveDestinations($voicemail, $data['voicemail_destinations']);
            }

            DB::commit();

            return $voicemail->fresh(['voicemailoptionss', 'voicemailDestinations']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing voicemail
     *
     * @param string $uuid
     * @param array $data
     * @return Voicemail
     */
    public function update(string $uuid, array $data): Voicemail
    {
        DB::beginTransaction();

        try {
            $voicemail = $this->find($uuid);

            if (!$voicemail) {
                throw new \Exception('Voicemail not found');
            }

            $voicemail->update([
                'voicemail_id' => $data['voicemail_id'],
                'voicemail_password' => $data['voicemail_password'],
                'greeting_id' => $data['greeting_id'] ?? null,
                'voicemail_alternate_greet_id' => $data['voicemail_alternate_greet_id'] ?? null,
                'voicemail_mail_to' => str_replace(' ', '', $data['voicemail_mail_to'] ?? ''),
                'voicemail_sms_to' => $data['voicemail_sms_to'] ?? null,
                'voicemail_transcription_enabled' => $data['voicemail_transcription_enabled'] ?? 'false',
                'voicemail_tutorial' => $data['voicemail_tutorial'] ?? 'false',
                'voicemail_file' => $data['voicemail_file'] ?? null,
                'voicemail_local_after_email' => $data['voicemail_local_after_email'] ?? 'true',
                'voicemail_enabled' => $data['voicemail_enabled'] ?? 'true',
                'voicemail_description' => $data['voicemail_description'] ?? null,
            ]);

            if (isset($data['voicemail_options']) && Auth::user()->hasPermission('voicemail_option_add')) {
                $this->saveOptions($voicemail, $data['voicemail_options']);
            }

            if (!empty($data['voicemail_options_delete']) && Auth::user()->hasPermission('voicemail_option_delete')) {
                $this->deleteOptions($data['voicemail_options_delete']);
            }

            if (isset($data['voicemail_destinations']) && Auth::user()->hasPermission('voicemail_forward')) {
                $this->saveDestinations($voicemail, $data['voicemail_destinations']);
            }

            if (!empty($data['voicemail_destinations_delete']) && Auth::user()->hasPermission('voicemail_forward')) {
                $this->deleteDestinations($data['voicemail_destinations_delete']);
            }

            DB::commit();

            return $voicemail->fresh(['voicemailoptionss', 'voicemailDestinations']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a voicemail
     *
     * @param string|array $uuids
     * @return bool
     */
    public function delete($uuids): bool
    {
        DB::beginTransaction();

        try {
            $uuids = is_array($uuids) ? $uuids : [$uuids];

            foreach ($uuids as $uuid) {
                $voicemail = $this->find($uuid);

                if ($voicemail) {
                    $voicemail->voicemailoptionss()->delete();

                    $voicemail->voicemailmessages()->delete();

                    $voicemail->voicemailDestinations()->delete();

                    $voicemail->voicemailGreetings()->delete();

                    $voicemail->delete();
                }
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Toggle voicemail enabled status
     *
     * @param array $uuids
     * @return bool
     */
    public function toggle(array $uuids): bool
    {
        DB::beginTransaction();

        try {
            foreach ($uuids as $uuid) {
                $voicemail = $this->find($uuid);

                if ($voicemail) {
                    $voicemail->update([
                        'voicemail_enabled' => $voicemail->voicemail_enabled === 'true' ? 'false' : 'true'
                    ]);
                }
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get voicemail greetings
     *
     * @param string $voicemailId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getGreetings(string $voicemailId)
    {
        return VoicemailGreeting::where('domain_uuid', Auth::user()->domain_uuid)
            ->where('voicemail_id', $voicemailId)
            ->orderBy('greeting_name', 'asc')
            ->get();
    }

    /**
     * Get assigned voicemail destinations
     *
     * @param string $voicemailUuid
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssignedDestinations(string $voicemailUuid)
    {
        return VoicemailDestination::select(
            'v_voicemails.voicemail_id',
            'v_voicemail_destinations.voicemail_destination_uuid',
            'v_voicemail_destinations.voicemail_uuid_copy'
        )
            ->join('v_voicemails', 'v_voicemail_destinations.voicemail_uuid_copy', '=', 'v_voicemails.voicemail_uuid')
            ->where('v_voicemails.domain_uuid', Auth::user()->domain_uuid)
            ->where('v_voicemails.voicemail_enabled', 'true')
            ->where('v_voicemail_destinations.voicemail_uuid', $voicemailUuid)
            ->orderBy('v_voicemails.voicemail_id', 'asc')
            ->get();
    }

    /**
     * Get available voicemail destinations
     *
     * @param string|null $voicemailUuid
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableDestinations(?string $voicemailUuid = null)
    {

        $query = Voicemail::select('voicemail_id', 'voicemail_uuid')
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->where('voicemail_enabled', 'true');

        if ($voicemailUuid) {
            $query->where('voicemail_uuid', '!=', $voicemailUuid);

            $assignedUuids = $this->getAssignedDestinations($voicemailUuid)
                ->pluck('voicemail_uuid_copy')
                ->toArray();

            if (!empty($assignedUuids)) {
                $query->whereNotIn('voicemail_uuid', $assignedUuids);
            }
        }

        return $query->orderBy('voicemail_id', 'asc')->get();
    }

    /**
     * Get voicemail messages count
     *
     * @param string $voicemailUuid
     * @return int
     */
    public function getMessagesCount(string $voicemailUuid): int
    {
        return VoicemailMessage::where('voicemail_uuid', $voicemailUuid)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->count();
    }
    
    /**
     * Create or update voicemail options
     *
     * @param Voicemail $voicemail
     * @param array $options
     * @return void
     */
    protected function saveOptions(Voicemail $voicemail, array $options): void
    {
        foreach ($options as $option) {
            if (empty($option['voicemail_option_digits']) || empty($option['voicemail_option_param'])) {
                continue;
            }

            if (is_numeric($option['voicemail_option_param'])) {
                $action = 'menu-exec-app';
                $param = "transfer {$option['voicemail_option_param']} XML " . Auth::user()->domain_name;
            } else {
                $parts = explode(':', $option['voicemail_option_param'], 2);
                $action = array_shift($parts);
                $param = implode(':', $parts);
            }

            $data = [
                'voicemail_option_digits' => $option['voicemail_option_digits'],
                'voicemail_option_action' => $action,
                'voicemail_option_param' => $param,
                'voicemail_option_order' => $option['voicemail_option_order'] ?? 0,
                'voicemail_option_description' => $option['voicemail_option_description'] ?? null,
            ];

            if (!empty($option['voicemail_option_uuid'])) {
                VoicemailOption::where('voicemail_option_uuid', $option['voicemail_option_uuid'])
                    ->where('domain_uuid', Auth::user()->domain_uuid)
                    ->update($data);
            } else {
                VoicemailOption::create(array_merge($data, [
                    'voicemail_option_uuid' => Str::uuid()->toString(),
                    'voicemail_uuid' => $voicemail->voicemail_uuid,
                    'domain_uuid' => Auth::user()->domain_uuid,
                ]));
            }
        }
    }

    /**
     * Delete voicemail options
     *
     * @param array $optionsToDelete
     * @return void
     */
    protected function deleteOptions(array $optionsToDelete): void
    {
        foreach ($optionsToDelete as $option) {
            if (!empty($option['checked']) && !empty($option['uuid'])) {
                VoicemailOption::where('voicemail_option_uuid', $option['uuid'])
                    ->where('domain_uuid', Auth::user()->domain_uuid)
                    ->delete();
            }
        }
    }

    /**
     * Create or update voicemail destinations
     *
     * @param Voicemail $voicemail
     * @param array $destinations
     * @return void
     */
    protected function saveDestinations(Voicemail $voicemail, array $destinations): void
    {
        foreach ($destinations as $destination) {
            if (empty($destination['voicemail_uuid_copy'])) {
                continue;
            }

            if (!empty($destination['voicemail_destination_uuid'])) {
                VoicemailDestination::where('voicemail_destination_uuid', $destination['voicemail_destination_uuid'])
                    ->where('domain_uuid', Auth::user()->domain_uuid)
                    ->update([
                        'voicemail_uuid_copy' => $destination['voicemail_uuid_copy'],
                    ]);
            } else {
                VoicemailDestination::create([
                    'voicemail_destination_uuid' => Str::uuid()->toString(),
                    'voicemail_uuid' => $voicemail->voicemail_uuid,
                    'voicemail_uuid_copy' => $destination['voicemail_uuid_copy'],
                    'domain_uuid' => Auth::user()->domain_uuid,
                ]);
            }
        }
    }



    /**
     * Delete voicemail destinations
     *
     * @param array $destinationsToDelete
     * @return void
     */
    protected function deleteDestinations(array $destinationsToDelete): void
    {
        foreach ($destinationsToDelete as $destination) {
            if (!empty($destination['checked']) && !empty($destination['uuid'])) {
                VoicemailDestination::where('voicemail_destination_uuid', $destination['uuid'])
                    ->where('domain_uuid', Auth::user()->domain_uuid)
                    ->delete();
            }
        }
    }

    /**
     * Create voicemail directory on filesystem
     *
     * @param string $voicemailId
     * @return void
     */
    protected function createVoicemailDirectory(string $voicemailId): void
    {

        $path = Session::get('voicemail_directory', '/var/lib/freeswitch/storage/voicemail')
            . '/default/'
            . Session::get('domain_name')
            . '/'
            . $voicemailId;

        if (!file_exists($path)) {
            mkdir($path, 0770, true);
        }
    }
}
