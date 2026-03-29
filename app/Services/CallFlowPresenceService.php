<?php

namespace App\Services;

use App\Facades\FreeSwitch;
use App\Services\FreeSwitch\FreeSwitchService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CallFlowPresenceService
{
    protected FreeSwitchService $freeSwitchService;

    public function __construct(FreeSwitchService $freeSwitchService)
    {
        $this->freeSwitchService = $freeSwitchService;
    }

    /**
     * Send call flow presence notification
     *
     * @param string $featureCode 
     * @param string $domainName
     * @param string $callFlowUuid 
     * @param bool $isActive 
     * @return array 
     */
    public function sendCallFlowPresence(
        string $featureCode,
        string $domainName,
        string $callFlowUuid,
        bool $isActive
    ): array {
        try {
            $event = $this->buildPresenceEvent(
                $featureCode,
                $domainName,
                $callFlowUuid,
                $isActive
            );

            if (App::hasDebugModeEnabled()) {
                Log::debug('[' . __CLASS__ . '][' . __METHOD__ . '] Sending call flow presence event: ' . $event);
            }

            $responses = FreeSwitch::execute('sendevent', $event);

            $success = true;
            foreach ($responses as $item) {
                $nodeResponse = trim($item['response'] ?? '');

                if (App::hasDebugModeEnabled()) {
                    Log::debug('[' . __CLASS__ . '][' . __METHOD__ . '] Node ' . $item['node']->node_name . ' response: ' . $nodeResponse);
                }

                if (!str_starts_with($nodeResponse, '+OK') && !str_starts_with($nodeResponse, 'OK')) {
                    $success = false;
                    Log::warning('Node failed to send event', [
                        'node' => $item['node']->node_name,
                        'hostname' => $item['node']->node_hostname,
                        'response' => $nodeResponse,
                    ]);
                }
            }

            return [
                'success' => $success,
                'responses' => $responses,
            ];
        } catch (\Exception $e) {
            if (App::hasDebugModeEnabled()) {
                Log::error('[' . __CLASS__ . '][' . __METHOD__ . '] Error: ' . $e->getMessage());
            }

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build the presence event string for call flow
     *
     * @param string $featureCode
     * @param string $domainName
     * @param string $callFlowUuid
     * @param bool $isActive
     * @return string
     */
    protected function buildPresenceEvent(
        string $featureCode,
        string $domainName,
        string $callFlowUuid,
        bool $isActive
    ): string {
        $event = "PRESENCE_IN\n";
        $event .= "proto: flow\n";
        $event .= "event_type: presence\n";
        $event .= "alt_event_type: dialog\n";
        $event .= "Presence-Call-Direction: outbound\n";
        $event .= "state: Active (1 waiting)\n";
        $event .= "from: flow+{$featureCode}@{$domainName}\n";
        $event .= "login: flow+{$featureCode}@{$domainName}\n";
        $event .= "unique-id: {$callFlowUuid}\n";
        $event .= "answer-state: " . ($isActive ? "confirmed" : "terminated") . "\n";

        return $event;
    }
}
