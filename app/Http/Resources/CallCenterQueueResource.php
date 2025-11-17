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
            'call_center_queue_uuid' => $this->call_center_agent_uuid,
            'queue_name' => $this->agent_name,
            'queue_extension' => $this->agent_type,
            'queue_description' => intval($this->agent_call_timeout),
        ];
    }
}
