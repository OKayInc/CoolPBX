<?php

namespace App\Services\FreeSwitch;

use App\Facades\FreeSwitch;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class FeatureEventNotifyService
{
    protected FreeSwitchService $freeSwitchService;
    
    public function __construct(FreeSwitchService $freeSwitchService)
    {
        $this->freeSwitchService = $freeSwitchService;
    }
    
    /**
     * Send feature event notification to phone devices
     *
     * @param array $params Array containing notification parameters
     * @return array Response with success status and details
     */
    public function sendNotification(array $params): array
    {
        $required = [
            'domain_name',
            'extension',
            'forward_all_enabled',
            'forward_busy_enabled', 
            'forward_no_answer_enabled',
            'do_not_disturb'
        ];
        
        foreach ($required as $field) {
            if (!isset($params[$field])) {
                throw new \InvalidArgumentException("Missing required parameter: {$field}");
            }
        }
        
        $params = array_merge([
            'forward_all_destination' => '',
            'forward_busy_destination' => '',
            'forward_no_answer_destination' => '',
            'ring_count' => 5
        ], $params);
        
        try {
            $profiles = $this->getSipProfiles(
                $params['extension'],
                $params['domain_name']
            );
            
            if (App::hasDebugModeEnabled()) {
                Log::debug('[' . __CLASS__ . '][' . __METHOD__ . '] SIP Profiles found: ' . json_encode($profiles));
            }
            
            $responses = [];
            
            foreach ($profiles as $profile) {
                $event = $this->buildFeatureEvent($profile, $params);
                $response = FreeSwitch::execute('sendevent', $event);
                
                $responses[$profile] = [
                    'success' => !empty($response) && $response !== '-ERR',
                    'response' => $response
                ];
                
                if (App::hasDebugModeEnabled()) {
                    Log::debug('[' . __CLASS__ . '][' . __METHOD__ . '] Event sent to profile ' . $profile . ': ' . $response);
                }
            }
            
            return [
                'success' => true,
                'profiles' => $profiles,
                'responses' => $responses
            ];
            
        } catch (\Exception $e) {
            if (App::hasDebugModeEnabled()) {
                Log::error('[' . __CLASS__ . '][' . __METHOD__ . '] Error sending notification: ' . $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get SIP profiles for an extension
     *
     * @param string $extension
     * @param string $domain
     * @return array
     */
    protected function getSipProfiles(string $extension, string $domain): array
    {
        $command = "sofia_contact */{$extension}@{$domain}";
        $contactString = FreeSwitch::execute('api', $command);
        
        if (empty($contactString) || $contactString === 'error/user_not_registered') {
            return ['internal'];
        }
        
        preg_match_all('/sofia\/([^,\/]+)\//', $contactString, $matches);
        
        if (empty($matches[1])) {
            return ['internal'];
        }
        
        return array_unique($matches[1]);
    }
    
    /**
     * Build the feature event string
     *
     * @param string $profile
     * @param array $params
     * @return string
     */
    protected function buildFeatureEvent(string $profile, array $params): string
    {
        $event = "SWITCH_EVENT_PHONE_FEATURE\n";
        $event .= "profile: {$profile}\n";
        $event .= "user: {$params['extension']}\n";
        $event .= "host: {$params['domain_name']}\n";
        $event .= "device: \n";
        $event .= "Feature-Event: init\n";
        $event .= "forward_immediate_enabled: " . $this->formatBoolean($params['forward_all_enabled']) . "\n";
        $event .= "forward_immediate: {$params['forward_all_destination']}\n";
        $event .= "forward_busy_enabled: " . $this->formatBoolean($params['forward_busy_enabled']) . "\n";
        $event .= "forward_busy: {$params['forward_busy_destination']}\n";
        $event .= "forward_no_answer_enabled: " . $this->formatBoolean($params['forward_no_answer_enabled']) . "\n";
        $event .= "forward_no_answer: {$params['forward_no_answer_destination']}\n";
        $event .= "doNotDisturbOn: " . $this->formatBoolean($params['do_not_disturb']) . "\n";
        $event .= "ringCount: {$params['ring_count']}\n";
        
        return $event;
    }
    
    /**
     * Format boolean value for FreeSWITCH
     *
     * @param mixed $value
     * @return string
     */
    protected function formatBoolean($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        return in_array(strtolower($value), ['true', '1', 'yes', 'on']) ? 'true' : 'false';
    }
    
    /**
     * Send simple DND (Do Not Disturb) notification
     *
     * @param string $extension
     * @param string $domain
     * @param bool $enabled
     * @return array
     */
    public function sendDndNotification(string $extension, string $domain, bool $enabled): array
    {
        return $this->sendNotification([
            'domain_name' => $domain,
            'extension' => $extension,
            'forward_all_enabled' => false,
            'forward_busy_enabled' => false,
            'forward_no_answer_enabled' => false,
            'do_not_disturb' => $enabled,
            'ring_count' => 5
        ]);
    }
    
    /**
     * Send call forwarding notification
     *
     * @param string $extension
     * @param string $domain
     * @param array $forwardSettings
     * @return array
     */
    public function sendForwardingNotification(string $extension, string $domain, array $forwardSettings): array
    {
        return $this->sendNotification(array_merge([
            'domain_name' => $domain,
            'extension' => $extension,
            'do_not_disturb' => false,
            'ring_count' => 5
        ], $forwardSettings));
    }
}