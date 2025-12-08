<?php

namespace App\Http\Resources;

use App\Http\Requests\CallCenterAgentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallCenterAgentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'call_center_agent_uuid' => $this->call_center_agent_uuid,
            'agent_name' => $this->agent_name,
            'agent_type' => $this->agent_type,
            'agent_call_timeout' => intval($this->agent_call_timeout),
            'agent_id' => $this->agent_id,
            'agent_contact' => $this->agent_contact,
            'agent_status' => $this->agent_status,
            'agent_logout' => $this->agent_logout,
            'agent_max_no_answer' => intval($this->agent_max_no_answer),
            'agent_wrap_up_time' => intval($this->agent_wrap_up_time),
            'agent_reject_delay_time' => intval($this->agent_reject_delay_time),
            'agent_busy_delay_time' => intval($this->agent_busy_delay_time),
            'agent_no_answer_delay_time' => intval($this->agent_no_answer_delay_time),
            'agent_record' => boolval($this->agent_record),
        ];
    }
}
