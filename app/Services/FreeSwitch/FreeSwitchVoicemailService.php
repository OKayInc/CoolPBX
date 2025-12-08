<?php

namespace App\Services\FreeSwitch;

use App\Models\Voicemail;
use App\Models\VoicemailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class FreeSwitchVoicemailService
{
    protected FreeSwitchService $freeSwitchService;

    public function __construct(FreeSwitchService $freeSwitchService)
    {
        $this->freeSwitchService = $freeSwitchService;
    }

    public function updateMWI(string $voicemailId, string $domainName): bool
    {
        try {
            $command = "luarun app.lua voicemail mwi {$voicemailId}@{$domainName}";
            
            if (App::hasDebugModeEnabled()) {
                Log::debug('[' . __CLASS__ . '][' . __METHOD__ . '] Executing MWI update: ' . $command);
            }

            $response = $this->freeSwitchService->execute('api', $command);

            if (App::hasDebugModeEnabled()) {
                Log::debug('[' . __CLASS__ . '][' . __METHOD__ . '] MWI Response: ' . $response);
            }

            $success = !empty($response) && !str_contains($response, '-ERR');

            return $success;

        } catch (\Exception $e) {
            Log::error('[' . __CLASS__ . '][' . __METHOD__ . '] Error updating MWI: ' . $e->getMessage());
            return false;
        }
    }

    public function updateMWIFromModel(Voicemail $voicemail): bool
    {
        return $this->updateMWI(
            $voicemail->voicemail_id,
            $voicemail->domain->domain_name
        );
    }

    public function updateMWIFromMessage(VoicemailMessage $message): bool
    {
        if (!$message->relationLoaded('voicemail')) {
            $message->load('voicemail.domain');
        }

        return $this->updateMWI(
            $message->voicemail->voicemail_id,
            $message->voicemail->domain->domain_name
        );
    }

    public function getMessageCount(string $voicemailId, string $domainName): array
    {
        try {
            $voicemail = Voicemail::with('domain')
                ->whereHas('domain', function($query) use ($domainName) {
                    $query->where('domain_name', $domainName);
                })
                ->where('voicemail_id', $voicemailId)
                ->first();

            if (!$voicemail) {
                return ['new' => 0, 'saved' => 0, 'total' => 0];
            }

            $newCount = VoicemailMessage::where('voicemail_uuid', $voicemail->voicemail_uuid)
                ->where(function($query) {
                    $query->where('message_status', '!=', 'saved')
                          ->orWhereNull('message_status');
                })
                ->count();

            $savedCount = VoicemailMessage::where('voicemail_uuid', $voicemail->voicemail_uuid)
                ->where('message_status', 'saved')
                ->count();

            return [
                'new' => $newCount,
                'saved' => $savedCount,
                'total' => $newCount + $savedCount
            ];

        } catch (\Exception $e) {
            Log::error('[' . __CLASS__ . '][' . __METHOD__ . '] Error getting message count: ' . $e->getMessage());
            return ['new' => 0, 'saved' => 0, 'total' => 0];
        }
    }


    public function bulkUpdateMWI(array $voicemailIds): array
    {
        $results = [];

        foreach ($voicemailIds as $item) {
            $voicemailId = $item['voicemail_id'];
            $domainName = $item['domain_name'];
            $key = "{$voicemailId}@{$domainName}";

            $results[$key] = [
                'voicemail_id' => $voicemailId,
                'domain_name' => $domainName,
                'success' => $this->updateMWI($voicemailId, $domainName)
            ];
        }

        return $results;
    }

    public function clearMWI(string $voicemailId, string $domainName): bool
    {
        return $this->updateMWI($voicemailId, $domainName);
    }
}