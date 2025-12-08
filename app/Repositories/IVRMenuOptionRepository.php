<?php

namespace App\Repositories;

use App\Models\IVRMenu;
use App\Models\IVRMenuOption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class IVRMenuOptionRepository
{
	protected $model;

	public function __construct(IVRMenuOption $ivrMenuOption)
	{
		$this->model = $ivrMenuOption;
	}

	public function getAll(): Collection
	{
		return $this->model->all();
	}

    public function findByUuid(string $ivr_menu_option_uuid): ?IVRMenuOption
    {
        return $this->model->where('ivr_menu_option_uuid', $ivr_menu_option_uuid)->first();
    }

	public function create(IVRMenu $ivrMenu, array $ivrMenuOptions): void
	{
		foreach ($ivrMenuOptions as $ivrMenuOption)
		{
			$ivrMenuOption['ivr_menu_option_uuid'] = Str::uuid();
			$ivrMenuOption['ivr_menu_uuid'] = $ivrMenu->ivr_menu_uuid;

			$this->model->create($ivrMenuOption);
		}
	}

	public function update(IVRMenu $ivrMenu, array $ivrMenuOptions): void
	{
		foreach ($ivrMenuOptions as $ivrMenuOption)
		{
			if (empty($ivrMenuOption['ivr_menu_option_uuid']))
			{
				$ivrMenuOption['ivr_menu_option_uuid'] = Str::uuid();
				$ivrMenuOption['ivr_menu_uuid'] = $ivrMenu->ivr_menu_uuid;

				$this->model->create($ivrMenuOption);
			}
			else
			{
				$this->model->where('ivr_menu_option_uuid', $ivrMenuOption['ivr_menu_option_uuid'])->update($ivrMenuOption);
			}
		}
	}

	public function delete(array $ivrMenuOptions): bool
	{
		return $this->model->whereIn('ivr_menu_option_uuid', $ivrMenuOptions)->delete();
	}
}
