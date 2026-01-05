<?php

namespace App\Services;

use App\Facades\Setting;
use App\Models\Conference;
use App\Models\ConferenceRoom;
use App\Services\FreeSwitch\FreeSwitchService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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
						"uuid" => $member["uuid"],
						"join_time" => $member["join_time"],
						"record_path" => $member["record_path"] ?? "",
						"flag_can_hear" => filter_var($member["flags"]["can_hear"], FILTER_VALIDATE_BOOLEAN),
						"flag_can_speak" => filter_var($member["flags"]["can_speak"], FILTER_VALIDATE_BOOLEAN),
						"flag_talking" => filter_var($member["flags"]["talking"], FILTER_VALIDATE_BOOLEAN),
						"last_talking" => filter_var($member["last_talking"], FILTER_VALIDATE_BOOLEAN),
						"flag_has_video" => filter_var($member["flags"]["has_video"], FILTER_VALIDATE_BOOLEAN),
						"flag_has_floor" => filter_var($member["flags"]["has_floor"], FILTER_VALIDATE_BOOLEAN),
						"is_moderator" => $member["flags"]["is_moderator"],
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
					"locked" => filter_var($result["conference"]["locked"] ?? false, FILTER_VALIDATE_BOOLEAN),
					"recording" => filter_var($result["conference"]["@attributes"]["recording"] ?? false, FILTER_VALIDATE_BOOLEAN),
					"mute_all" => $mute_all,
					"conference_name" => $conferenceRoomName,
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

    public function runCommand($cmd, $name, $data, $id = null, $uuid = null, $direction = null)
    {
        if($cmd != "conference")
        {
            return;
        }

        list($conferenceRoomName, $domain) = explode("@", $name);

        if(Str::isUuid($conferenceRoomName))
        {
            $x = Conference::where("domain_uuid", $domain)->where("conference_extension", $conferenceRoomName)->get();
        }

        //validate the uuid
        if(!Str::isUuid($uuid))
        {
            $uuid = null;
        }

        //validate direction
        switch($direction)
        {
            case "up":
                break;
            case "down":
                break;
            default:
                $direction = null;
        }

        //validate the data
        switch($data)
        {
            case "energy":
                break;
            case "volume_in":
                break;
            case "volume_out":
                break;
            case "record":
                break;
            case "norecord":
                break;
            case "kick":
                break;
            case "kick all":
                break;
            case "mute":
                break;
            case "unmute":
                break;
            case "mute non_moderator":
                break;
            case "unmute non_moderator":
                break;
            case "deaf":
                break;
            case "undeaf":
                break;
            case "lock":
                break;
            case "unlock":
                break;
            default:
                $data = null;
        }

        //validate the numeric id
        if(!is_numeric($id))
        {
            $direction = null;
        }

        if(!empty($cmd))
        {
            //prepare the switch cmd
            $command = $cmd . " ";
            $command .= $name . " ";
            $command .= $data . " ";

            if($id && !empty($id))
            {
                $command .= " " . $id;
            }

            if($data == "energy")
            {
                $switch_result = $this->freeSwitchService->execute($command);
                $result_array = explode("=", $switch_result);
                $tmp_value = $result_array[1];

                if($direction == "up")
                {
                    $tmp_value = $tmp_value + 100;
                }

                if($direction == "down")
                {
                    $tmp_value = $tmp_value - 100;
                }

                $switch_result = $this->freeSwitchService->execute($command . ' ' . $tmp_value);
            }

            if($data == "volume_in")
            {
                $switch_result = $this->freeSwitchService->execute($command);
                $result_array = explode("=", $switch_result);
                $tmp_value = $result_array[1];

                if($direction == "up")
                {
                    $tmp_value = $tmp_value + 1;
                }

                if($direction == "down")
                {
                    $tmp_value = $tmp_value - 1;
                }

                $switch_result = $this->freeSwitchService->execute($command . ' ' . $tmp_value);
            }

            if($data == "volume_out")
            {
                $switch_result = $this->freeSwitchService->execute($command);
                $result_array = explode("=", $switch_result);
                $tmp_value = $result_array[1];

                if($direction == "up")
                {
                    $tmp_value = $tmp_value + 1;
                }

                if($direction == "down")
                {
                    $tmp_value = $tmp_value - 1;
                }

                $switch_result = $this->freeSwitchService->execute($command . ' ' . $tmp_value);
            }

            if($data == "record")
            {
                $recordingDirectory = Setting::getSetting('switch', 'recordings', 'dir') . '/' .  Session::get('domain_name') . '/archive/' . date("Y") . '/' . date("M") . '/' . date("d");

                $file = $recordingDirectory . "/" . $uuid . ".wav";

                $command .= $file;

                if(!file_exists($file))
                {
                    $switch_result = $this->freeSwitchService->execute($command);
                }
            }

            if($data == "norecord")
            {
                $recordingDirectory = Setting::getSetting('switch', 'recordings', 'dir') . '/' .  Session::get('domain_name') . '/archive/' . date("Y") . '/' . date("M") . '/' . date("d");

                $file = $recordingDirectory . "/" . $uuid . ".wav";

                $command .= $file;

                $switch_result = $this->freeSwitchService->execute($command);
            }

            if($data == "kick")
            {
                $switch_result = $this->freeSwitchService->execute('uuid_kill ' . $uuid);
            }

            if($data == "kick all")
            {
                $this->endConference($name);
            }

            if($data == "mute" || $data == "unmute" || $data == "mute non_moderator" || $data == "unmute non_moderator")
            {
                $switch_result = $this->freeSwitchService->execute($command);

                $command = "uuid_setvar " . $uuid . " hand_raised false";

                $this->freeSwitchService->execute($command);
            }

            if($data == "deaf" || $data == "undeaf" )
            {
                $switch_result = $this->freeSwitchService->execute($command);
            }

            if($data == "lock" || $data == "unlock" )
            {
                $switch_result = $this->freeSwitchService->execute($command);
            }
        }
    }

    //define an alternative kick all
	private function endConference($name)
	{
		$command = "conference '{$name}' xml_list";

        $xml_str = $this->freeSwitchService->execute($command);

		try
		{
			$xml = new \SimpleXMLElement($xml_str);
		}
		catch(\Exception $e)
		{
			//echo $e->getMessage();
		}

		$session_uuid = $xml->conference['uuid'];

        $x = 0;

        foreach($xml->conference->members->member as $row)
        {
			$uuid = (string)$row->uuid;

			if(Str::isUuid($uuid))
            {
                $switch_result = $this->freeSwitchService->execute('uuid_kill ' . $uuid);
			}

			if($x < 1)
            {
				usleep(500000); //500000 = 0.5 seconds
			}
			else
            {
				usleep(10000);  //1000000 = 0.01 seconds
			}

            $x++;
		}
	}
}
