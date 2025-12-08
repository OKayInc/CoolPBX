<?php

namespace App\Services\FreeSwitch;

use App\Facades\FreeSwitch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FreeSwitchPresenceService
{    
    public function isActive(string $presenceId): bool
    {
        try {
            $response = FreeSwitch::execute('api', 'show calls as json');
            $calls = json_decode($response, true);
            
            if (isset($calls['rows'])) {
                foreach ($calls['rows'] as $row) {
                    if ($row['presence_id'] == $presenceId || 
                        (isset($row['b_presence_id']) && $row['b_presence_id'] == $presenceId)) {
                        return true;
                    }
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Error checking presence status: ' . $e->getMessage());
            return false;
        }
    }
    

    public function sendPresenceEvent(array $params): bool
    {
        try {
            $event = "PRESENCE_IN\n";
            $event .= "proto: sip\n";
            $event .= "login: {$params['login']}\n";
            $event .= "from: {$params['from']}\n";
            $event .= "status: {$params['status']}\n";
            $event .= "rpid: {$params['rpid']}\n";
            $event .= "event_type: presence\n";
            $event .= "alt_event_type: dialog\n";
            $event .= "event_count: {$params['event_count']}\n";
            $event .= "unique-id: " . Str::uuid() . "\n";
            $event .= "Presence-Call-Direction: {$params['direction']}\n";
            $event .= "answer-state: {$params['answer_state']}\n";
            
            $response = FreeSwitch::execute('sendevent', $event);
            
            return !empty($response) && $response !== '-ERR';
        } catch (\Exception $e) {
            Log::error('Error sending presence event: ' . $e->getMessage());
            return false;
        }
    }
    public function updateDndStatus(string $extension, string $domain, bool $dndEnabled): bool
    {
        $presenceId = "{$extension}@{$domain}";
        
        if ($dndEnabled) {
            return $this->sendPresenceEvent([
                'login' => $presenceId,
                'from' => $presenceId,
                'status' => 'Active (1 waiting)',
                'rpid' => 'unknown',
                'event_count' => 1,
                'direction' => 'outbound',
                'answer_state' => 'confirmed'
            ]);
        } else {
            if (!$this->isActive($presenceId)) {
                return $this->sendPresenceEvent([
                    'login' => $presenceId,
                    'from' => $presenceId,
                    'status' => 'Active (1 waiting)',
                    'rpid' => 'unknown',
                    'event_count' => 1,
                    'direction' => 'outbound',
                    'answer_state' => 'terminated'
                ]);
            }
            
            return true;
        }
    }
}