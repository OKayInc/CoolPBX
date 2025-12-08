<?php

namespace App\Repositories;

use App\Models\Variable;
use Illuminate\Database\Eloquent\Collection;

class VariableRepository
{
    protected $model;

    public function __construct(Variable $variable)
    {
        $this->model = $variable;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findByUuid(string $uuid): ?Variable
    {
        return $this->model->where('variable_uuid', $uuid)->first();
    }

    public function create(array $data): Variable
    {
        return $this->model->create($data);
    }

    public function update(Variable $variable, array $data): bool
    {
        return $variable->update($data);
    }

    public function delete(Variable $variable): ?bool
    {
        return $variable->delete();
    }

    public function getCategories()
    {
        return Variable::distinct()->orderBy('var_category')->pluck('var_category');
    }

    public function getRingtonesOptions(): array
    {
        $ringtones = Variable::where('var_category', 'Ringtones')
                           ->orderBy('var_name', 'asc')
                           ->get();

        if ($ringtones->isEmpty()) {
            return [];
        }

        $values = [];

        foreach ($ringtones as $ringtone) {
            $label = __('label-' . $ringtone->var_name);
            if ($label === 'label-' . $ringtone->var_name) {
                $label = $ringtone->var_name;
            }

            $values[] = [
                "id" => '${' . $ringtone->var_name . '}',
                "name" => $label
            ];
        }

        $values[] = [
            "id" => "silence",
            "name" => __("Silence")
        ];

        return [
            "label" => __("Ringtones"),
            "values" => $values
        ];
    }
}
