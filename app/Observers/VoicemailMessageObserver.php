<?php

namespace App\Observers;

use App\Models\VoicemailMessage;
use App\Services\FreeSwitch\FreeSwitchVoicemailService;
use Illuminate\Support\Facades\Log;

class VoicemailMessageObserver
{
    protected FreeSwitchVoicemailService $voicemailService;

    public function __construct(FreeSwitchVoicemailService $voicemailService)
    {
        $this->voicemailService = $voicemailService;
    }


    public function created(VoicemailMessage $message): void
    {
        $this->updateMWI($message, 'created');
    }

    public function updated(VoicemailMessage $message): void
    {
        if ($message->wasChanged('message_status')) {
            $this->updateMWI($message, 'updated');
        }
    }

    public function deleted(VoicemailMessage $message): void
    {
        $this->updateMWI($message, 'deleted');
    }

    protected function updateMWI(VoicemailMessage $message, string $event): void
    {
        try {
            if (config('voicemail.mwi_async', true)) {
                dispatch(function () use ($message) {
                    $this->voicemailService->updateMWIFromMessage($message);
                })->afterResponse();
            } else {
                $this->voicemailService->updateMWIFromMessage($message);
            }

            Log::info("MWI updated for voicemail {$message->voicemail_uuid} after {$event} event");

        } catch (\Exception $e) {
            Log::error("Failed to update MWI after {$event}: " . $e->getMessage());
        }
    }
}