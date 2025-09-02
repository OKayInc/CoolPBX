<?php

namespace App\Livewire;

use Livewire\Component;
use App\Repositories\CallCenterAgentRepository;
use App\Models\CallCenterAgent;
use App\Http\Requests\CallCenterAgentRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CallCenterAgentForm extends Component
{
    public ?string $agentUuid = null;
    public $agent;
    public bool $isEditing = false;

    public ?string $domain_uuid = null;
    public ?string $agent_name = '';
    public ?string $agent_id = '';
    public ?string $agent_password = '';
    public string $agent_type = 'callback';
    public int $agent_call_timeout = 20;
    public ?string $user_uuid = null;
    public ?string $agent_status = null;
    public ?string $agent_contact = '';
    public int $agent_no_answer_delay_time = 30;
    public int $agent_max_no_answer = 0;
    public int $agent_wrap_up_time = 10;
    public int $agent_reject_delay_time = 90;
    public int $agent_busy_delay_time = 90;
    public string $agent_record = 'true';

    public array $users = [];
    public array $availableDomains = [];
    public array $availableStatuses = [];
    public array $availableTypes = [];
    public $duplicateAgentId = null;

    protected $callCenterAgentRepository;

    public function boot(CallCenterAgentRepository $callCenterAgentRepository)
    {
        $this->callCenterAgentRepository = $callCenterAgentRepository;
    }

    public function rules()
    {
        return [
            'domain_uuid' => 'nullable|string',
            'agent_name' => 'required|string|max:255',
            'agent_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('v_call_center_agents', 'agent_id')
                    ->where('domain_uuid', $this->domain_uuid)
                    ->ignore($this->agentUuid, 'call_center_agent_uuid')
            ],
            'agent_password' => 'nullable|string|max:255',
            'agent_type' => 'nullable|in:callback,uuid-standby',
            'agent_call_timeout' => 'nullable|integer|min:1|max:300',
            'user_uuid' => 'nullable|string',
            'agent_status' => 'nullable|in:Logged Out,Available,Available (On Demand),On Break',
            'agent_contact' => 'nullable|string|max:255',
            'agent_no_answer_delay_time' => 'nullable|integer|min:0|max:300',
            'agent_max_no_answer' => 'nullable|integer|min:0|max:100',
            'agent_wrap_up_time' => 'nullable|integer|min:0|max:300',
            'agent_reject_delay_time' => 'nullable|integer|min:0|max:300',
            'agent_busy_delay_time' => 'nullable|integer|min:0|max:300',
            'agent_record' => 'nullable|in:true,false',
        ];
    }

    public function mount($agentUuid = null)
    {
        $this->agentUuid = $agentUuid;
        $this->isEditing = !is_null($agentUuid);

        $this->loadDropdownData();

        if ($this->isEditing) {
            $this->loadAgent();
        } else {
            $this->initializeDefaults();
        }
    }

    protected function loadAgent()
    {
        $this->agent = $this->callCenterAgentRepository->findByUuid($this->agentUuid, true);

        if (!$this->agent) {
            session()->flash('error', 'Call center agent not found.');
            return redirect()->route('call_center_agent.index');
        }

        $this->domain_uuid = $this->agent->domain_uuid;
        $this->agent_name = $this->agent->agent_name;
        $this->agent_id = $this->agent->agent_id;
        $this->agent_password = $this->agent->agent_password;
        $this->agent_type = $this->agent->agent_type;
        $this->agent_call_timeout = $this->agent->agent_call_timeout;
        $this->user_uuid = $this->agent->user_uuid;
        $this->agent_status = $this->agent->agent_status;
        $this->agent_contact = $this->agent->agent_contact;
        $this->agent_no_answer_delay_time = $this->agent->agent_no_answer_delay_time;
        $this->agent_max_no_answer = $this->agent->agent_max_no_answer;
        $this->agent_wrap_up_time = $this->agent->agent_wrap_up_time;
        $this->agent_reject_delay_time = $this->agent->agent_reject_delay_time;
        $this->agent_busy_delay_time = $this->agent->agent_busy_delay_time;
        $this->agent_record = $this->agent->agent_record;

    }

    protected function initializeDefaults()
    {
        $user = auth()->user();
        $this->domain_uuid = $user->domain_uuid;
        
        $agentData = [];
        $this->callCenterAgentRepository->setDefaultValues($agentData);
        
        $this->agent_type = $agentData['agent_type'];
        $this->agent_call_timeout = $agentData['agent_call_timeout'];
        $this->agent_max_no_answer = $agentData['agent_max_no_answer'];
        $this->agent_wrap_up_time = $agentData['agent_wrap_up_time'];
        $this->agent_no_answer_delay_time = $agentData['agent_no_answer_delay_time'];
        $this->agent_reject_delay_time = $agentData['agent_reject_delay_time'];
        $this->agent_busy_delay_time = $agentData['agent_busy_delay_time'];
        $this->agent_record = $agentData['agent_record'];
    }

    protected function loadDropdownData()
    {
        $user = auth()->user();

        $this->users = $this->callCenterAgentRepository->getUsersForDomain($user->domain_uuid)->toArray();
        $this->availableDomains = $this->callCenterAgentRepository->getDomains()->toArray();
        $this->availableStatuses = $this->callCenterAgentRepository->getAvailableStatuses();
        $this->availableTypes = $this->callCenterAgentRepository->getAvailableTypes();

        if (!$this->isEditing) {
            $this->domain_uuid = Session::get('domain_uuid') ?? $user->domain_uuid;
        }
    }

    public function updatedAgentId()
    {
        if ($this->agent_id && $this->domain_uuid) {
            $this->duplicateAgentId = $this->callCenterAgentRepository->checkDuplicateAgentId(
                $this->agent_id,
                $this->domain_uuid,
                $this->agentUuid
            );
        }
    }

    public function updatedDomainUuid()
    {
        if ($this->domain_uuid) {
            $this->users = $this->callCenterAgentRepository->getUsersForDomain($this->domain_uuid)->toArray();
            
            if (!$this->isEditing) {
                $this->user_uuid = null;
            }
        }
    }

    public function updatedAgentContact()
    {
        if ($this->agent_contact) {
            $this->agent_contact = $this->callCenterAgentRepository->sanitizeContactString($this->agent_contact);
        }
    }

    public function copyAgent()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $newAgentName = $this->agent_name . ' (Copy)';
            $newAgentId = $this->agent_id . '_copy';

            $copiedAgent = $this->callCenterAgentRepository->copy(
                $this->agentUuid,
                $newAgentName,
                $newAgentId
            );

            session()->flash('success', 'Agent copied successfully.');
            return redirect()->route('call-center-agents.edit', $copiedAgent->call_center_agent_uuid);
        } catch (\Exception $e) {
            session()->flash('error', 'Error copying agent: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $agentData = [
                'domain_uuid' => $this->domain_uuid,
                'agent_name' => $this->agent_name,
                'agent_id' => $this->agent_id,
                'agent_password' => $this->agent_password,
                'agent_type' => $this->agent_type,
                'agent_call_timeout' => $this->agent_call_timeout,
                'user_uuid' => $this->user_uuid,
                'agent_status' => $this->agent_status,
                'agent_contact' => $this->agent_contact,
                'agent_no_answer_delay_time' => $this->agent_no_answer_delay_time,
                'agent_max_no_answer' => $this->agent_max_no_answer,
                'agent_wrap_up_time' => $this->agent_wrap_up_time,
                'agent_reject_delay_time' => $this->agent_reject_delay_time,
                'agent_busy_delay_time' => $this->agent_busy_delay_time,
                'agent_record' => $this->agent_record,
            ];

            if ($this->isEditing) {
                $agent = $this->callCenterAgentRepository->update($this->agentUuid, $agentData);
                session()->flash('success', 'Agent updated successfully.');
                return redirect()->route('call_center_agent.edit', $agent->call_center_agent_uuid);
            } else {
                $agent = $this->callCenterAgentRepository->create($agentData);
                session()->flash('success', 'Agent created successfully.');
                return redirect()->route('call_center_agent.edit', $agent->call_center_agent_uuid);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving agent: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->isEditing) {
            return;
        }

        try {
            $this->callCenterAgentRepository->delete($this->agentUuid);
            session()->flash('success', 'Agent deleted successfully.');
            return redirect()->route('call_center_agent.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting agent: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.call-center-agent-form');
    }
}