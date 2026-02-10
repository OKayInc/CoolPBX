<?php

namespace App\Services;

use App\Models\Conference;
use App\Models\ConferenceRoom;
use App\Services\FreeSwitch\FreeSwitchService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ConferenceCenterActiveService
{
    protected FreeSwitchService $freeSwitchService;

    public function __construct(FreeSwitchService $freeSwitchService)
    {
        $this->freeSwitchService = $freeSwitchService;
    }

	public function getActiveConferenceCenters()
    {
		$data = [];

		try
		{
			$command = "conference xml_list";

			// Execute on all nodes and consolidate conferences from every node
			$responses = $this->freeSwitchService->execute($command);

			foreach ($responses as $item) {
				$xml_string = $item['response'] ?? '';

				if (empty($xml_string) || str_ends_with(trim($xml_string), 'not found')) {
					continue;
				}

				$xml = simplexml_load_string($xml_string, "SimpleXMLElement", LIBXML_NOCDATA);
				if ($xml === false) {
					continue;
				}

				$json = json_encode($xml);
				$conferences = json_decode($json, true);

				foreach($conferences as $conference)
				{
					$memberCount = $conference["@attributes"]["member-count"];

					list($conferenceRoomUuid, $domain) = explode('@', $conference["@attributes"]["name"]);

					if($domain == Session::get('domain_name'))
					{
						if(Str::isUuid($conferenceRoomUuid))
						{
							$conferenceRoom = ConferenceRoom::with('conferenceCenter')->where('conference_room_uuid', $conferenceRoomUuid)->first();

							$conferenceRoomName = $conferenceRoom->conference_room_name;
							$conferenceExtension = $conferenceRoom->conferencecenter->conference_center_extension;
							$participantPIN = $conferenceRoom->participant_pin;
						}
						else if(is_numeric($conferenceRoomUuid))
						{
							$c = Conference::where('domain_uuid', Session::get('domain_name'))->andWhere('conference_extension', $conferenceRoomUuid)->first();

							$conferenceRoomName = $c->conference_room_name;
							$conferenceExtension = $c->conference_center_extension;
							$participantPIN = $c->participant_pin;
						}

						$data[] = [
							"conference_room_uuid" => $conferenceRoomUuid,
							"conference_room_name" => $conferenceRoomName,
							"conference_center_extension" => $conferenceExtension,
							"participant_pin" => $participantPIN,
							"memberCount" => $memberCount,
							"_node_name" => $item['node']->node_name,
							"_node_hostname" => $item['node']->node_hostname,
						];
					}
				}
			}
        }
        catch(\Exception $e)
        {
            throw $e;

            if(App::hasDebugModeEnabled())
            {
                Log::error('[' . __CLASS__ . '][' . __METHOD__ . ']: ' . $e->getMessage());
            }
        }
        finally
        {
            return $data;
        }
    }
}
