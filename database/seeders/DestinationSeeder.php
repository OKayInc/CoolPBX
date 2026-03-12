<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\DialplanDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DestinationSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dialplansInfo = DialplanDetail::select('dialplan_uuid', 'dialplan_detail_type', 'dialplan_detail_data')
                            ->where('dialplan_detail_tag','=', 'action')
                            ->whereIn('dialplan_uuid', function($query)
                                {
                                    $query->select('dialplan_uuid')
                                        ->from(Destination::getTableName())
                                        ->where('destination_type', '=', 'inbound')
                                        ->whereNull('destination_app')
                                        ->whereNull('destination_data');
                                })
                            ->where(function (Builder $query) {
                                $query->where('dialplan_detail_type', '=', 'transfer')
                                ->orWhere('dialplan_detail_type', '=', 'bridge');
                            })
                            ->orderBy('dialplan_detail_order', 'ASC')
                            ->get();
        foreach ($dialplansInfo as $dialplanInfo)
        {
            $sql = 'UPDATE '.Destination::getTableName().' SET destination_app = "'.$dialplanInfo->dialplan_detail_type.'", destination_data = "'.$dialplanInfo->dialplan_detail_data.'" WHERE dialplan_uuid = "'.$dialplanInfo->dialplan_uuid.'"';
            DB:statement($sql);
            unset($sql);
        }

        $destinations = Destination::whereNull('destination_actions')->get();
        foreach($destinations as $destination)
        {
            unset($actions);
            if (isset($destination->destination_app) && !empty($destination->destination_data))
            {
                $actions[0]['destination_app'] = $destination->destination_app;
                $actions[0]['destination_data'] = $destination->destination_data;
            }

            if (isset($destination->destination_app) && !empty($destination->destination_data))
            {
                $actions[1]['destination_app'] = $destination->destination_alternate_app;
                $actions[1]['destination_data'] = $destination->destination_alternate_data;
            }

            if (!empty($actions))
            {
                $destination->destination_actions = json_encode($actions);
                $destination->save();
            }
        }
    }
}
