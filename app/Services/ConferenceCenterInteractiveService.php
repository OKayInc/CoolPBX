<?php

namespace App\Services;

use App\Models\ConferenceCenter;
use App\Models\ConferenceRoom;
use App\Services\FreeSwitch\FreeSwitchService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ConferenceCenterInteractiveService
{
    protected FreeSwitchService $freeSwitchService;

    public function __construct(FreeSwitchService $freeSwitchService)
    {
        $this->freeSwitchService = $freeSwitchService;
    }

	public function getInteractiveConferenceCenters(ConferenceRoom $conferenceRoom)
    {
		$data = [
			"head" => [],
			"body" => [],
		];

		try
		{
			$conferenceRoomName = $conferenceRoom->conference_room_name . "@" . Session::get("domain_name");
			$command = "conference {$conferenceRoomName} xml_list";
			$xml_string = $this->freeSwitchService->execute($command);

			if(substr($xml_string, -9) == "not found")
			{
				$valid_xml = false;
			}
			else
			{
				$valid_xml = true;
			}

			if($valid_xml)
			{
				$xml = simplexml_load_string($xml_string, "SimpleXMLElement", LIBXML_NOCDATA);
				$json = json_encode($xml);
				$result = json_decode($json, true);

				$mute_all = true;

				foreach($result["conference"]["members"]["member"] as $member)
				{
					$command = "uuid_getvar " . $member["uuid"] . " hand_raised";

					if($member["flags"]["is_moderator"] == "false" && $member["flags"]["can_speak"] == "true")
					{
						$mute_all = false;
					}

					$data["body"][] = [
						"id" => $member["id"],
						"join_time" => $member["join_time"],
						"flag_can_hear" => (bool)$member["flags"]["can_hear"],
						"flag_can_speak" => (bool)$member["flags"]["can_speak"],
						"flag_talking" => (bool)$member["flags"]["talking"],
						"last_talking" => (bool)$member["last_talking"],
						"flag_has_video" => (bool)$member["flags"]["has_video"],
						"flag_has_floor" => (bool)$member["flags"]["has_floor"],
						"is_moderator" => $member["flags"]["is_moderator"],
						"uuid" => $member["uuid"],
						"caller_id_name" => urldecode($member["caller_id_name"]),
						"caller_id_number" => $member["caller_id_number"],
						"hand_raised" => ($this->freeSwitchService->execute($command) == "true") ? true : false,
						"join_time_formatted" => sprintf('%02d:%02d:%02d', floor($member["join_time"] / 3600), floor(floor($member["join_time"] / 60) % 60), $member["join_time"] % 60),
						"last_talking_formatted" => sprintf('%02d:%02d:%02d', floor($member["last_talking"] / 3600), floor(floor($member["last_talking"] / 60) % 60), $member["last_talking"] % 60),
						"mute_all" => ($member["flags"]["is_moderator"] == "false" && $member["flags"]["can_speak"] == "true") ? false : true,
					];
				}

				$data["head"] = [
					"session_uuid" => $result["conference"]["@attributes"]["uuid"],
					"member_count" => $result["conference"]["@attributes"]["member-count"] ?? 0,
					"locked" => $result["conference"]["locked"] ?? false,
					"recording" => $result["conference"]["@attributes"]["recording"] ?? false,
					"mute_all" => $mute_all,
				];
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
