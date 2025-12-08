<?php

namespace App\Repositories;

use App\Models\Destination;
use App\Models\Dialplan;
use App\Services\DialplanService;
use Illuminate\Database\Eloquent\Collection;

class DestinationRepository
{
    protected $model;
    protected $dialplanService;
    protected $dialplanRepository;

    public function __construct(Destination $destination, DialplanService $dialplanService, DialplanRepository $dialplanRepository)
    {
        $this->model = $destination;
        $this->dialplanService = $dialplanService;
        $this->dialplanRepository = $dialplanRepository;
    }

    public function getAll(string $domainUuid): Collection
    {
        return $this->model->where('domain_uuid', $domainUuid)->get();
    }

    public function findByUuid(string $uuid): ?Destination
    {
        return $this->model->where('destination_uuid', $uuid)->first();
    }

    public function create(array $data): Destination
    {
        $destination = $this->model->create($data);

        $dialplan = $this->buildDialplan($destination);

        if($dialplan)
        {
            $this->setDialplan($destination, $dialplan);
        }

        return $destination;
    }

    public function update(Destination $destination, array $data): bool
    {
        $response = $destination->update($data);

        if($response)
        {
            $destination = $this->findByUuid($destination->destination_uuid);

            $dialplan = $this->buildDialplan($destination);

            if($dialplan)
            {
                $this->setDialplan($destination, $dialplan);
            }
        }

        return $response;
    }

    public function delete(Destination $destination): ?bool
    {
        $this->dialplanRepository->delete($destination->dialplan_uuid);

        return $destination->delete();
    }

    private function buildDialplan(Destination $destination)
	{
		$dialplan = null;

		if($destination->destination_type == "inbound")
		{
			$data["dialplan_name"] = $destination->destination_area_code ?? "" . $destination->destination_number;
			$data["dialplan_number"] = $destination->destination_area_code ?? "" . $destination->destination_number;
			$data["dialplan_order"] = $destination->destination_order;
			$data["dialplan_enabled"] = $destination->destination_enabled ?? "false";
			$data["dialplan_description"] = $destination->datadestination_description;
			$data["condition_field_1"] = $destination->destination_conditions;
			$data["condition_expression_1"] = $destination->condition_expressions;
			$data["action_1"] = $destination->destination_actions;

            $dialplan = Dialplan::find($destination->dialplan_uuid);

			$dialplan = $this->dialplanService->setInbound($data, $destination, $dialplan);
		}

		return $dialplan;
	}

    public function setDialplan(Destination $destination, Dialplan $dialplan)
    {
        Destination::where('destination_uuid', $destination->destination_uuid_uuid)->update([
            "dialplan_uuid" => $dialplan->dialplan_uuid
        ]);
    }
}
