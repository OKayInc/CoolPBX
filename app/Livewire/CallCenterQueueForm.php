<?php

namespace App\Livewire;

use App\Facades\FreeSwitch;
use Livewire\Component;
use App\Repositories\CallCenterQueueRepository;
use App\Models\CallCenterQueue;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CallCenterQueueForm extends Component
{
    // Form Properties
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
    public $availableAgents = [];
    public $tiers = [];
    public $tierRows = 3;

    // Available Options
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

    protected $rules = [
        'queue_name' => 'required|string|max:255',
        'queue_extension' => 'required|string|max:255',
        'queue_strategy' => 'required|string',
        'queue_description' => 'nullable|string|max:500',
        'queue_moh_sound' => 'nullable|string|max:255',
        'queue_record_template' => 'required|in:true,false',
        'queue_time_base_score' => 'required|string',
        'queue_timeout_action' => 'required|string',
        'queue_discard_abandoned_after' => 'nullable|integer|min:0',
        'queue_abandoned_resume_allowed' => 'required|in:true,false',
        'queue_tier_rules_apply' => 'required|in:true,false',
        'queue_tier_rule_wait_second' => 'nullable|integer|min:0',
        'queue_tier_rule_wait_multiply_level' => 'required|in:true,false',
        'queue_tier_rule_no_agent_no_wait' => 'required|in:true,false',
        'queue_max_wait_time' => 'nullable|integer|min:0',
        'queue_max_wait_time_with_no_agent' => 'nullable|integer|min:0',
        'queue_max_wait_time_with_no_agent_time_reached' => 'nullable|integer|min:0',
        'queue_enabled' => 'required|in:true,false',
        'queue_announce_sound' => 'nullable|string|max:500',
        'queue_announce_frequency' => 'nullable|integer|min:0',
        'queue_cc_exit_keys' => 'nullable|string|max:50',
    ];

    protected $messages = [
        'queue_name.required' => 'Queue name is required.',
        'queue_name.max' => 'Queue name cannot exceed 255 characters.',
        'queue_extension.required' => 'Queue extension is required.',
        'queue_extension.max' => 'Queue extension cannot exceed 255 characters.',
        'queue_strategy.required' => 'Queue strategy is required.',
        'queue_description.max' => 'Description cannot exceed 500 characters.',
        'queue_discard_abandoned_after.integer' => 'Discard abandoned after must be a number.',
        'queue_discard_abandoned_after.min' => 'Discard abandoned after must be 0 or greater.',
        'queue_tier_rule_wait_second.integer' => 'Tier rule wait second must be a number.',
        'queue_tier_rule_wait_second.min' => 'Tier rule wait second must be 0 or greater.',
        'queue_max_wait_time.integer' => 'Max wait time must be a number.',
        'queue_max_wait_time.min' => 'Max wait time must be 0 or greater.',
        'queue_max_wait_time_with_no_agent.integer' => 'Max wait time with no agent must be a number.',
        'queue_max_wait_time_with_no_agent.min' => 'Max wait time with no agent must be 0 or greater.',
        'queue_max_wait_time_with_no_agent_time_reached.integer' => 'Max wait time with no agent time reached must be a number.',
        'queue_max_wait_time_with_no_agent_time_reached.min' => 'Max wait time with no agent time reached must be 0 or greater.',
        'queue_cc_exit_keys.max' => 'Exit keys cannot exceed 50 characters.',
    ];

    public function boot(CallCenterQueueRepository $repository)
    {
        $this->repository = $repository;
    }

    public function mount($queueUuid = null)
    {
        $this->domain_uuid = session('domain_uuid') ?? auth()->user()->domain_uuid;
        $this->loadAvailableAgents();

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

    public function loadAvailableAgents()
    {
        $this->availableAgents = $this->repository->getAvailableAgents($this->domain_uuid);
    }

    public function loadTiers()
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
                'tier_level' => '',
                'tier_position' => '',
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
            $this->queue_cc_exit_keys = $queue->queue_cc_exit_keys ?? '';   

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

        // You could add additional checks for queue_extension if needed
        // Similar logic for checking extension duplicates
    }

    public function save()
    {
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
                'queue_record_template' => $this->queue_record_template,
                'queue_time_base_score' => $this->queue_time_base_score,
                'queue_timeout_action' => $this->queue_timeout_action,
                'queue_discard_abandoned_after' => $this->queue_discard_abandoned_after,
                'queue_abandoned_resume_allowed' => $this->queue_abandoned_resume_allowed,
                'queue_tier_rules_apply' => $this->queue_tier_rules_apply,
                'queue_tier_rule_wait_second' => $this->queue_tier_rule_wait_second,
                'queue_tier_rule_wait_multiply_level' => $this->queue_tier_rule_wait_multiply_level,
                'queue_tier_rule_no_agent_no_wait' => $this->queue_tier_rule_no_agent_no_wait,
                'queue_max_wait_time' => $this->queue_max_wait_time,
                'queue_max_wait_time_with_no_agent' => $this->queue_max_wait_time_with_no_agent,
                'queue_max_wait_time_with_no_agent_time_reached' => $this->queue_max_wait_time_with_no_agent_time_reached,
                'queue_enabled' => $this->queue_enabled,
                'queue_announce_sound' => $this->queue_announce_sound,
                'queue_announce_frequency' => $this->queue_announce_frequency,
                'queue_cc_exit_keys' => $this->queue_cc_exit_keys,
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
        
        $this->showDuplicateError = false;
        $this->duplicateErrorMessage = '';
    }

    public function getTitle()
    {
        return $this->isEditing ? 'Edit Call Center Queue' : 'Add Call Center Queue';
    }

    public function startCalLCenterQueue():void
    {
        try {
            $command = 'callcenter_config queue load '.$this->queue_extension.'@'.Session::get('domain_name');
            $response = FreeSwitch::execute($command);
            if(strpos($response,"+OK") !== false) {
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
            $command = 'callcenter_config queue unload '.$this->queue_extension.'@'.Session::get('domain_name');
            $response = FreeSwitch::execute($command);
            if(strpos($response,'+OK') !== false) {
                session()->flash('message','Queue unloaded successfully.');
            } else {
                session()->flash('error','Error unloading queue: ' . $response);
            }
        } catch (\Throwable $th) {
            Log::error('Error unloading queue:'. $th->getMessage());
            session()->flash('error', 'Error unloading queue'. $th->getMessage());
        }
    }

    public function reloadCalLCenterQueue(): void
    {
        try {
            $command = "callcenter_config queue reload ".$this->queue_extension."@".Session::get('domain_name');
            $response = FreeSwitch::execute($command);
            if(strpos($response,'+OK') !== false) {
                session()->flash('mesagge','Queue reloaded successfully.');
            } else {
                session()->flash('error','Error reloading queue: ' . $response);
            }
        } catch (\Throwable $th) {
            Log::error('Error loading queue'. $th->getMessage());
            session()->flash('error', ''. $th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.call-center-queue-form');
    }
}