<?php

namespace App\Services;

use App\Models\Fax;
use App\Models\FaxTask;
use App\Models\Gateway;

class FaxActiveService
{
    public function getActiveFaxes(Fax $fax): array
    {
		$gateways = Gateway::pluck('gateway', 'gateway_uuid')->toArray();

        return FaxTask::where('fax_uuid', $fax->fax_uuid)
            ->orderBy('task_next_time')
            ->get()
            ->map(function ($t) use ($gateways)
			{
                $status = 'Wait';

                if ($t->task_status > 0)
				{
					if( $t->task_status <= 3)
					{
						$status = 'Execute';
					}
					elseif ($t->task_status == 10)
					{
						$status = 'Success';
					}
					else
					{
						$status = 'Fail';
					}
				}

                $enabled = ($t->task_interrupted === 'true') ? false : true;

                $server = $t->fax?->fax_name ?? '-';

                $files = [];

                if($t->task_fax_file)
				{
                    $files[] = basename($t->task_fax_file);
				}

                if($t->task_wav_file)
				{
                    $files[] = basename($t->task_wav_file);
				}
                elseif ($t->fax?->fax_send_greeting)
				{
                    $files[] = basename($t->fax->fax_send_greeting);
				}

                $uri = $t->task_uri;

                if(!empty($gateways) && $uri)
				{
                    foreach ($gateways as $uuid => $name)
					{
                        $uri = str_replace($uuid, $name, $uri);
                    }
                }

                return [
                    'uuid'       => $t->fax_task_uuid,
                    'server'     => $server,
                    'enabled'    => $enabled,
                    'status'     => $status,
                    'next_time'  => $t->task_next_time,
                    'files'      => $files,
                    'uri'        => $uri,
                ];
            })
            ->toArray();
    }

    public function deleteTask(FaxTask $faxTask)
    {
        FaxTask::where('fax_task_uuid', $faxTask->fax_task_uuid)->delete();
    }
}
