<?php

namespace App\Repositories;

use App\Models\MusicOnHold;
use Illuminate\Support\Facades\Session;

class MusicOnHoldRepository
{
    public function getOptions(): array
    {
        $mohs = MusicOnHold::where(function($query) {
                $query->where("domain_uuid", Session::get("domain_uuid"))
                      ->orWhereNull("domain_uuid");
            })->get();

        if ($mohs->isEmpty()) {
            return [];
        }

        $values = [];
        $previous_name = "";

        foreach ($mohs as $moh) {
            if ($previous_name != $moh->music_on_hold_name) {
                $name = $moh->domain_uuid ? $moh->domain->domain_name . '/' : '';
                $name .= $moh->music_on_hold_name;

                $values[] = [
                    "id" => "local_stream://" . $name,
                    "name" => $moh->music_on_hold_name
                ];
            }
            $previous_name = $moh->music_on_hold_name;
        }

        return [
            "label" => __("Music on Hold"),
            "values" => $values
        ];
    }
}
