<?php

namespace App\Http\Resources;

use App\Http\Requests\CallCenterAgentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallCenterQueueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'call_center_queue_uuid' => $this->call_center_queue_uuid,
            'queue_name' => $this->queue_name,
            'queue_extension' => $this->queue_extension,
            'queue_description' => $this->queue_description,
//            'tier' => $this->pivot,
	    'tier' => [
			'tier_level' => intval($this->pivot->tier_level),
			'tier_position' => intval($this->pivot->tier_position),
		],
        ];
    }
}
