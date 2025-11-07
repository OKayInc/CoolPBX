<?php

namespace App\Repositories;

use App\Models\VoicemailGreeting;
use App\Models\Voicemail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoicemailGreetingRepository
{
    /**
     * Find a voicemail greeting by UUID
     *
     * @param string $uuid
     * @return VoicemailGreeting|null
     */
    public function find(string $uuid): ?VoicemailGreeting
    {
        return VoicemailGreeting::where('voicemail_greeting_uuid', $uuid)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->with('voicemail')
            ->first();
    }

    /**
     * Find a voicemail greeting by UUID and voicemail ID
     *
     * @param string $uuid
     * @param string $voicemailId
     * @return VoicemailGreeting|null
     */
    public function findByVoicemailId(string $uuid, string $voicemailId): ?VoicemailGreeting
    {
        return VoicemailGreeting::where('voicemail_greeting_uuid', $uuid)
            ->where('voicemail_id', $voicemailId)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->with('voicemail')
            ->first();
    }

    /**
     * Get all greetings for a voicemail
     *
     * @param string $voicemailId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByVoicemailId(string $voicemailId)
    {
        return VoicemailGreeting::where('voicemail_id', $voicemailId)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->orderBy('greeting_name', 'asc')
            ->get();
    }

    /**
     * Create a new voicemail greeting
     *
     * @param array $data
     * @return VoicemailGreeting
     */
    public function create(array $data): VoicemailGreeting
    {
        DB::beginTransaction();

        try {
            $voicemailGreetingUuid = $data['voicemail_greeting_uuid'] ?? Str::uuid()->toString();

            $greetingName = str_replace("'", "", $data['greeting_name']);

            $greeting = VoicemailGreeting::create([
                'voicemail_greeting_uuid' => $voicemailGreetingUuid,
                'domain_uuid' => Auth::user()->domain_uuid,
                'voicemail_id' => $data['voicemail_id'],
                'greeting_name' => $greetingName,
                'greeting_description' => $data['greeting_description'] ?? null,
                'greeting_filename' => $data['greeting_filename'] ?? null,
                'greeting_base64' => $data['greeting_base64'] ?? null,
            ]);

            DB::commit();

            return $greeting->fresh('voicemail');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $uuid, array $data): VoicemailGreeting
    {
        DB::beginTransaction();

        try {
            $greeting = $this->find($uuid);

            if (!$greeting) {
                throw new \Exception('Voicemail greeting not found');
            }

            $greetingName = str_replace("'", "", $data['greeting_name']);

            $updateData = [
                'greeting_name' => $greetingName,
                'greeting_description' => $data['greeting_description'] ?? null,
            ];

            if (isset($data['greeting_filename'])) {
                $updateData['greeting_filename'] = $data['greeting_filename'];
            }

            if (isset($data['greeting_base64'])) {
                $updateData['greeting_base64'] = $data['greeting_base64'];
            }

            $greeting->update($updateData);

            DB::commit();

            return $greeting->fresh('voicemail');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a voicemail greeting
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
                $greeting = $this->find($uuid);

                if ($greeting) {
                    $this->deleteGreetingFile($greeting);

                    $greeting->delete();
                }
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getVoicemail(string $voicemailId): ?Voicemail
    {
        return Voicemail::where('voicemail_id', $voicemailId)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->first();
    }

    protected function deleteGreetingFile(VoicemailGreeting $greeting): bool
    {
        if (!$greeting->greeting_filename) {
            return false;
        }

        $voicemailPath = config('voicemail.directory', '/var/lib/freeswitch/storage/voicemail');
        $filePath = $voicemailPath . '/default/' .
            Auth::user()->domain_name . '/' .
            $greeting->voicemail_id . '/' .
            $greeting->greeting_filename;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    public function greetingNameExists(string $voicemailId, string $greetingName, ?string $excludeUuid = null): bool
    {
        $query = VoicemailGreeting::where('voicemail_id', $voicemailId)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->where('greeting_name', $greetingName);

        if ($excludeUuid) {
            $query->where('voicemail_greeting_uuid', '!=', $excludeUuid);
        }

        return $query->exists();
    }

    public function getGreetingCount(string $voicemailId): int
    {
        return VoicemailGreeting::where('voicemail_id', $voicemailId)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->count();
    }

    public function saveGreetingFile(string $voicemailId, string $greetingName, $file): array
    {
        $voicemail = Voicemail::with('domain')
            ->where('voicemail_id', $voicemailId)
            ->where('domain_uuid', Auth::user()->domain_uuid)
            ->firstOrFail();

        $safeName = Str::slug($greetingName);
        $extension = $file->getClientOriginalExtension();
        $filename = "greeting_{$safeName}.{$extension}";

        $relativePath = "voicemail/default/{$voicemail->domain->domain_name}/{$voicemailId}";

        $fullPath = storage_path("app/public/{$relativePath}");
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0770, true);
        }

        $storedPath = $file->storeAs("public/{$relativePath}", $filename);

        return [
            'filename' => $filename,
            'path' => storage_path("app/{$storedPath}"),
        ];
    }
}
