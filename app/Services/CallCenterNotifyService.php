<?php

namespace App\Services;

use App\Facades\FreeSwitch;
use Illuminate\Support\Facades\Log;
use Exception;

class CallCenterNotifyService
{
    public function sendNotification(array $params): void
    {
        try {
            $this->sendCallCenterNotify($params);
        } catch (Exception $e) {
            Log::error('Failed to send call center notification: ' . $e->getMessage(), $params);
        }
    }

    protected function sendCallCenterNotify(array $params): void
    {
        $domainName = $params['domain_name'];
        $agentName = $params['agent_name'];
        $answerState = $params['answer_state'];
        $agentUuid = $params['agent_uuid'];

        $event = [
            'Event-Name' => 'PRESENCE_IN',
            'proto' => 'agent',
            'event_type' => 'presence',
            'alt_event_type' => 'dialog',
            'Presence-Call-Direction' => 'outbound',
            'state' => 'Active (1 waiting)',
            'from' => "agent+{$agentName}@{$domainName}",
            'login' => "agent+{$agentName}@{$domainName}",
            'unique-id' => $agentUuid,
            'answer-state' => $answerState,
        ];

        try {
            $this->sendFreeSwitchEvent($event);
        } catch (Exception $e) {
            Log::error('Failed to send FreeSWITCH event: ' . $e->getMessage(), $event);
            throw $e;
        }
    }

    protected function sendFreeSwitchEvent(array $event): void
    {
        $eventType = $event['Event-Name'];
        unset($event['Event-Name']);
        
        $eventString = '';
        foreach ($event as $key => $value) {
            $eventString .= "{$key}: {$value}\n";
        }
        $eventString .= "\n"; 
        try {
            $responses = FreeSwitch::execute('sendevent', "{$eventType}\n{$eventString}");

            foreach ($responses as $item) {
                $response = trim($item['response'] ?? '');
                if (!str_starts_with($response, '+OK') && !str_starts_with($response, 'OK')) {
                    Log::warning('Node failed to send event', [
                        'node' => $item['node']->node_name,
                        'hostname' => $item['node']->node_hostname,
                        'response' => $response,
                    ]);
                }
            }
        } catch (Exception $e) {
            throw new Exception("Failed to send event to FreeSWITCH: " . $e->getMessage());
        }
    }

    public function sendAgentPresence(
        string $domainName,
        string $agentName,
        string $agentUuid,
        string $answerState = 'answered',
        string $state = 'Active (1 waiting)'
    ): void {
        $params = [
            'domain_name' => $domainName,
            'agent_name' => $agentName,
            'agent_uuid' => $agentUuid,
            'answer_state' => $answerState,
            'state' => $state
        ];

        $this->sendNotification($params);
    }


    public function sendAgentAvailable(string $domainName, string $agentName, string $agentUuid): void
    {
        $this->sendAgentPresence($domainName, $agentName, $agentUuid, 'available', 'Available');
    }

    public function sendAgentBusy(string $domainName, string $agentName, string $agentUuid): void
    {
        $this->sendAgentPresence($domainName, $agentName, $agentUuid, 'busy', 'Busy');
    }

    public function sendAgentOnCall(string $domainName, string $agentName, string $agentUuid): void
    {
        $this->sendAgentPresence($domainName, $agentName, $agentUuid, 'answered', 'Active (1 waiting)');
    }
}