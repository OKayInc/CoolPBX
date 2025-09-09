<?php

namespace App\Repositories;

use App\Models\Variable;

class VariableRepository
{
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
