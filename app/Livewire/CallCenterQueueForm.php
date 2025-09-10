<?php

namespace App\Livewire;

use App\Facades\FreeSwitch;
use App\Http\Requests\CallCenterQueueRequest;
use Livewire\Component;
use App\Repositories\CallCenterQueueRepository;
use App\Models\CallCenterQueue;
use App\Services\SoundsService;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CallCenterQueueForm extends Component
{
    public $call_center_queue_uuid;
    public $queue_name = '';
    public $queue_extension = '';
    public $queue_strategy = 'longest-idle-agent';
    public $queue_description = '';
    public $queue_moh_sound = 'local_stream://moh';
    public $queue_record_template = 'false';
    public $queue_time_base_score = 'system';
    public $queue_timeout_action = 'hangup';
    public $queue_discard_abandoned_after = '900';
    public $queue_abandoned_resume_allowed = 'false';
    public $queue_tier_rules_apply = 'false';
    public $queue_tier_rule_wait_second = '300';
    public $queue_tier_rule_wait_multiply_level = 'true';
    public $queue_tier_rule_no_agent_no_wait = 'false';
    public $queue_max_wait_time = '0';
    public $queue_max_wait_time_with_no_agent = '0';
    public $queue_max_wait_time_with_no_agent_time_reached = '5';
    public $queue_enabled = 'true';
    public $domain_uuid;
    public $queue_announce_sound = '';
    public $queue_announce_frequency = '';
    public $queue_cc_exit_keys = '';
    public $isEditing = false;
    public $showDuplicateError = false;
    public $duplicateErrorMessage = '';
    public $available_sounds = [];
    public $filtered_sounds = [];
    public $sounds_search = '';
    public $queue_greeting = '';
    public $availableAgents = [];
    public $tiers = [];
    public $tierRows = 3;

    public $dialplan_uuid;
    public $queue_cid_prefix = '';
    public $queue_time_base_score_sec = 0;

    public $strategyOptions = [
        'longest-idle-agent' => 'Longest Idle Agent',
        'round-robin' => 'Round Robin',
        'top-down' => 'Top Down',
        'agent-with-least-talk-time' => 'Agent with Least Talk Time',
        'agent-with-fewest-calls' => 'Agent with Fewest Calls',
        'sequentially-by-agent-order' => 'Sequentially by Agent Order',
        'random' => 'Random',
        'ring-all' => 'Ring All'
    ];

    public $timeBaseScoreOptions = [
        'system' => 'System',
        'queue' => 'Queue'
    ];

    public $timeoutActionOptions = [
        'hangup' => 'Hangup',
        'bridge' => 'Bridge',
        'voicemail' => 'Voicemail'
    ];

    protected CallCenterQueueRepository $repository;
    protected SoundsService $soundsService;


    public function boot(CallCenterQueueRepository $repository, SoundsService $soundsService): void
    {
        $this->repository = $repository;
        $this->soundsService = $soundsService;
    }

    public function rules()
    {
        $request = new CallCenterQueueRequest();
        return $request->rules();
    }

    public function mount($queueUuid = null): void
    {
        $this->domain_uuid = Session::get('domain_uuid') ?? auth()->user()->domain_uuid;
        $this->loadAvailableAgents();
        $this->loadAvailableSounds();

        if ($queueUuid) {
            $this->isEditing = true;
            $this->call_center_queue_uuid = $queueUuid;
            $this->loadQueue();
            $this->loadTiers();
        } else {
            $this->call_center_queue_uuid = Str::uuid()->toString();
            $this->initializeEmptyTiers();
        }
    }

    public function loadAvailableSounds(): void
    {
        try {
            $this->available_sounds = $this->soundsService->getAllSounds();
        } catch (\Exception $e) {
            \Log::error('Error loading sounds: ' . $e->getMessage());
            $this->available_sounds = [];
        }
    }

    public function selectGreeting($greetingValue)
    {
        $this->queue_greeting = $greetingValue;
        $this->sounds_search = '';
        $this->filtered_sounds = $this->available_sounds;
    }

    public function clearGreeting(): void
    {
        $this->queue_greeting = '';
    }

    public function loadAvailableAgents(): void
    {
        $this->availableAgents = $this->repository->getAvailableAgents($this->domain_uuid);
    }

    public function loadTiers(): void
    {
        $existingTiers = $this->repository->getTiers($this->call_center_queue_uuid, $this->domain_uuid);

        $this->tiers = $existingTiers->map(function ($tier) {
            return [
                'call_center_tier_uuid' => $tier->call_center_tier_uuid,
                'call_center_agent_uuid' => $tier->call_center_agent_uuid,
                'tier_level' => $tier->tier_level,
                'tier_position' => $tier->tier_position,
                'agent_name' => $tier->agent_name
            ];
        })->toArray();

        $this->initializeEmptyTiers();
    }

    public function initializeEmptyTiers()
    {
        for ($i = 0; $i < $this->tierRows; $i++) {
            $this->tiers[] = [
                'call_center_tier_uuid' => Str::uuid()->toString(),
                'call_center_agent_uuid' => '',
                'tier_level' => 0,
                'tier_position' => 0,
                'agent_name' => ''
            ];
        }
    }

    public function deleteTier($index, $tierUuid = null)
    {
        if ($tierUuid && $this->isEditing && !$this->repository->isNewTier($tierUuid)) {
            try {
                $this->repository->deleteTier($tierUuid);
                session()->flash('success', 'Agent removed from queue successfully.');
            } catch (Exception $e) {
                session()->flash('error', 'Error removing agent: ' . $e->getMessage());
                return;
            }
        }

        unset($this->tiers[$index]);
        $this->tiers = array_values($this->tiers);
    }




    public function loadQueue()
    {
        try {
            $queue = $this->repository->findByUuid($this->call_center_queue_uuid);

            if (!$queue) {
                session()->flash('error', 'Queue not found.');
                return redirect()->route('call_center_queues.index');
            }

            $this->queue_name = $queue->queue_name;
            $this->queue_extension = $queue->queue_extension;
            $this->queue_strategy = $queue->queue_strategy ?? 'longest-idle-agent';
            $this->queue_description = $queue->queue_description;
            $this->queue_moh_sound = $queue->queue_moh_sound ?? 'local_stream://moh';
            $this->queue_record_template = $queue->queue_record_template ?? 'false';
            $this->queue_time_base_score = $queue->queue_time_base_score ?? 'system';
            $this->queue_timeout_action = $queue->queue_timeout_action ?? 'hangup';
            $this->queue_discard_abandoned_after = $queue->queue_discard_abandoned_after ?? '900';
            $this->queue_abandoned_resume_allowed = $queue->queue_abandoned_resume_allowed ?? 'false';
            $this->queue_tier_rules_apply = $queue->queue_tier_rules_apply ?? 'false';
            $this->queue_tier_rule_wait_second = $queue->queue_tier_rule_wait_second ?? '300';
            $this->queue_tier_rule_wait_multiply_level = $queue->queue_tier_rule_wait_multiply_level ?? 'true';
            $this->queue_tier_rule_no_agent_no_wait = $queue->queue_tier_rule_no_agent_no_wait ?? 'false';
            $this->queue_max_wait_time = $queue->queue_max_wait_time ?? '0';
            $this->queue_max_wait_time_with_no_agent = $queue->queue_max_wait_time_with_no_agent ?? '0';
            $this->queue_max_wait_time_with_no_agent_time_reached = $queue->queue_max_wait_time_with_no_agent_time_reached ?? '5';
            $this->queue_enabled = $queue->queue_enabled ?? 'true';
            $this->domain_uuid = $queue->domain_uuid;
            $this->queue_announce_sound = $queue->queue_announce_sound ?? '';
            $this->queue_announce_frequency = $queue->queue_announce_frequency ?? '';
            $this->queue_greeting = $queue->queue_greeting ?? '';
            $this->queue_cc_exit_keys = $queue->queue_cc_exit_keys ?? '';
            $this->dialplan_uuid = $queue->dialplan_uuid ?? '';
            $this->queue_cid_prefix = $queue->queue_cid_prefix ?? '';
            $this->queue_time_base_score_sec = $queue->queue_time_base_score_sec ?? 0;
        } catch (Exception $e) {
            session()->flash('error', 'Error loading queue: ' . $e->getMessage());
        }
    }

    public function updatedQueueName()
    {
        $this->checkForDuplicates();
    }

    public function updatedQueueExtension()
    {
        $this->checkForDuplicates();
    }

    public function checkForDuplicates()
    {
        $this->showDuplicateError = false;
        $this->duplicateErrorMessage = '';

        if (!empty($this->queue_name)) {
            $excludeUuid = $this->isEditing ? $this->call_center_queue_uuid : null;

            if ($this->repository->checkDuplicate($this->queue_name, $this->domain_uuid, $excludeUuid)) {
                $this->showDuplicateError = true;
                $this->duplicateErrorMessage = 'A queue with this name already exists.';
                return;
            }
        }
    }

    public function save()
    {
        if (!empty($this->queue_greeting) && !$this->soundsService->validateSound($this->queue_greeting)) {
            $this->addError('queue_greeting', 'Invalid greeting sound selected.');
            return;
        }
        $this->validate();

        $this->checkForDuplicates();

        if ($this->showDuplicateError) {
            return;
        }

        try {
            $data = [
                'call_center_queue_uuid' => $this->call_center_queue_uuid,
                'domain_uuid' => $this->domain_uuid,
                'queue_name' => $this->queue_name,
                'queue_extension' => $this->queue_extension,
                'queue_strategy' => $this->queue_strategy,
                'queue_description' => $this->queue_description,
                'queue_moh_sound' => $this->queue_moh_sound,
                'queue_record_template' => $this->queue_record_template === true || $this->queue_record_template === 'true' ? 'true' : 'false',
                'queue_time_base_score' => $this->queue_time_base_score,
                'queue_timeout_action' => $this->queue_timeout_action,
                'queue_discard_abandoned_after' => $this->queue_discard_abandoned_after,
                'queue_abandoned_resume_allowed' => $this->queue_abandoned_resume_allowed === true || $this->queue_abandoned_resume_allowed === 'true' ? 'true' : 'false',
                'queue_tier_rules_apply' => $this->queue_tier_rules_apply === true || $this->queue_tier_rules_apply === 'true' ? 'true' : 'false',
                'queue_tier_rule_wait_second' => $this->queue_tier_rule_wait_second,
                'queue_tier_rule_wait_multiply_level' => $this->queue_tier_rule_wait_multiply_level === true || $this->queue_tier_rule_wait_multiply_level === 'true' ? 'true' : 'false',
                'queue_tier_rule_no_agent_no_wait' => $this->queue_tier_rule_no_agent_no_wait === true || $this->queue_tier_rule_no_agent_no_wait === 'true' ? 'true' : 'false',
                'queue_max_wait_time' => $this->queue_max_wait_time,
                'queue_max_wait_time_with_no_agent' => $this->queue_max_wait_time_with_no_agent,
                'queue_max_wait_time_with_no_agent_time_reached' => $this->queue_max_wait_time_with_no_agent_time_reached,
                'queue_enabled' => $this->queue_enabled,
                'queue_announce_sound' => $this->queue_announce_sound,
                'queue_announce_frequency' => $this->queue_announce_frequency,
                'queue_cc_exit_keys' => $this->queue_cc_exit_keys,
                'queue_greeting' => $this->queue_greeting,
                'dialplan_uuid' => $this->dialplan_uuid,
                'queue_cid_prefix' => $this->queue_cid_prefix,
                'queue_time_base_score_sec' => $this->queue_time_base_score_sec,
            ];

            if ($this->isEditing) {
                $queue = $this->repository->update($this->call_center_queue_uuid, $data, $this->tiers);
            } else {
                $queue = $this->repository->create($data, $this->tiers);
            }


            session()->flash('success', $this->isEditing ? 'Queue updated successfully.' : 'Queue created successfully.');
            return redirect()->route('call_center_queues.index');
        } catch (Exception $e) {
            throw $e;
            session()->flash('error', 'Error saving queue: ' . $e->getMessage());
        }
    }



    public function cancel()
    {
        return redirect()->route('call_center_queues.index');
    }

    public function resetForm()
    {
        $this->reset([
            'queue_name',
            'queue_extension',
            'queue_description'
        ]);

        $this->queue_strategy = 'longest-idle-agent';
        $this->queue_moh_sound = 'local_stream://moh';
        $this->queue_record_template = 'false';
        $this->queue_time_base_score = 'system';
        $this->queue_timeout_action = 'hangup';
        $this->queue_discard_abandoned_after = '900';
        $this->queue_abandoned_resume_allowed = 'false';
        $this->queue_tier_rules_apply = 'false';
        $this->queue_tier_rule_wait_second = '300';
        $this->queue_tier_rule_wait_multiply_level = 'true';
        $this->queue_tier_rule_no_agent_no_wait = 'false';
        $this->queue_max_wait_time = '0';
        $this->queue_max_wait_time_with_no_agent = '0';
        $this->queue_max_wait_time_with_no_agent_time_reached = '5';
        $this->queue_enabled = 'true';
        $this->queue_announce_sound = '';
        $this->queue_announce_frequency = '';
        $this->queue_cc_exit_keys = '';
        $this->queue_greeting = '';

        $this->showDuplicateError = false;
        $this->duplicateErrorMessage = '';
    }

    public function getTitle()
    {
        return $this->isEditing ? 'Edit Call Center Queue' : 'Add Call Center Queue';
    }

    public function startCalLCenterQueue(): void
    {
        try {
            $command = 'callcenter_config queue load ' . $this->queue_extension . '@' . Session::get('domain_name');
            $response = FreeSwitch::execute($command);
            if (strpos($response, "+OK") !== false) {
                session()->flash('message', 'Queue loaded successfully.');
            } else {
                session()->flash('error', 'Error loading queue: ' . $response);
            }
        } catch (\Throwable $th) {
            Log::error('Error loading queue: ' . $th->getMessage());
            session()->flash('error', 'Error loading queue: ' . $th->getMessage());
        }
    }

    public function unloadCalLCenterQueue(): void
    {
        try {
            $command = 'callcenter_config queue unload ' . $this->queue_extension . '@' . Session::get('domain_name');
            $response = FreeSwitch::execute($command);
            if (strpos($response, '+OK') !== false) {
                session()->flash('message', 'Queue unloaded successfully.');
            } else {
                session()->flash('error', 'Error unloading queue: ' . $response);
            }
        } catch (\Throwable $th) {
            Log::error('Error unloading queue:' . $th->getMessage());
            session()->flash('error', 'Error unloading queue' . $th->getMessage());
        }
    }

    public function reloadCalLCenterQueue(): void
    {
        try {
            $command = "callcenter_config queue reload " . $this->queue_extension . "@" . Session::get('domain_name');
            $response = FreeSwitch::execute($command);
            if (strpos($response, '+OK') !== false) {
                session()->flash('mesagge', 'Queue reloaded successfully.');
            } else {
                session()->flash('error', 'Error reloading queue: ' . $response);
            }
        } catch (\Throwable $th) {
            Log::error('Error loading queue' . $th->getMessage());
            session()->flash('error', '' . $th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.call-center-queue-form');
    }
}
