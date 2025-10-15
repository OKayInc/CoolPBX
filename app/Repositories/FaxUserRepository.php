<?php

namespace App\Repositories;

use App\Models\Fax;
use App\Models\FaxUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class FaxUserRepository
{
	protected $model;

	public function __construct(FaxUser $faxUser)
	{
		$this->model = $faxUser;
	}

	public function getAll(): Collection
	{
		return $this->model->all();
	}

    public function findByUuid(string $fax_user_uuid): ?FaxUser
    {
        return $this->model->where('fax_user_uuid', $fax_user_uuid)->first();
    }

	public function create(Fax $fax, array $faxUsers): void
	{
		foreach($faxUsers as $faxUser)
		{
			$data = [
				'fax_user_uuid' => Str::uuid(),
				'fax_uuid' => $fax->fax_uuid,
				'user_uuid' => $faxUser['user_uuid'],
			];

			$this->model->create($data);
		}
	}

	public function update(Fax $fax, array $faxUsers): void
	{
		foreach($faxUsers as $faxUser)
		{
			if(empty($faxUser['fax_user_uuid']))
			{
				$data = [
					'fax_user_uuid' => Str::uuid(),
					'fax_uuid' => $fax->fax_uuid,
					'user_uuid' => $faxUser['user_uuid'],
				];

				$this->model->create($data);
			}
			else
			{
				$this->model->where('fax_user_uuid', $faxUser['fax_user_uuid'])->update($faxUser);
			}
		}
	}

	public function delete(array $faxUsers): bool
	{
		return $this->model->whereIn('fax_user_uuid', $faxUsers)->delete();
	}

	public function deleteAll(Fax $fax): bool
	{
		return $this->model->where('fax_uuid', $fax->fax_uuid)->delete();
	}
}
