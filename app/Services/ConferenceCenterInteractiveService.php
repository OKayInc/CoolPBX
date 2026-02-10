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

    /**
     * Execute command and find first successful/meaningful response (for targeted operations)
     */
    private function executeUntilSuccess(string $command, ?string $param = null): array
    {
        $responses = $this->freeSwitchService->execute($command, $param);

        foreach ($responses as $item) {
            $response = trim($item['response'] ?? '');

            $isSuccess = str_starts_with($response, '+OK') ||
                         str_starts_with($response, 'OK');

            if ($isSuccess) {
                return [
                    'success' => true,
                    'response' => $response,
                    'node' => $item['node']->node_name
                ];
            }
        }

        return [
            'success' => false,
            'response' => null,
            'node' => null
        ];
    }

    /**
     * Execute command on all nodes and return first non-error response
     * Used for read operations where the data exists on one specific node
     */
    private function executeAndFindResponse(string $command, ?string $param = null): ?string
    {
        $responses = $this->freeSwitchService->execute($command, $param);

        foreach ($responses as $item) {
            $response = trim($item['response'] ?? '');

            if (!empty($response) && !str_ends_with($response, 'not found') && $response !== '-ERR') {
                return $response;
            }
        }

        return null;
    }

    /**
     * Execute command on all nodes (for operations that must apply everywhere like lock/unlock)
     */
    private function executeOnAllNodes(string $command, ?string $param = null): array
    {
        $responses = $this->freeSwitchService->execute($command, $param);
        $failedNodes = [];

        foreach ($responses as $item) {
            $response = trim($item['response'] ?? '');
            $isSuccess = str_starts_with($response, '+OK') ||
                         str_starts_with($response, 'OK');

            if (!$isSuccess && !empty($response)) {
                $failedNodes[] = [
                    'node' => $item['node']->node_name,
                    'hostname' => $item['node']->node_hostname,
                    'response' => $response
                ];
            }
        }

        return [
            'success' => empty($failedNodes),
            'responses' => $responses,
            'failed_nodes' => $failedNodes
        ];
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

			// Execute on all nodes and find the node that has this conference
			$xml_string = $this->executeAndFindResponse($command);

			if(empty($xml_string) || substr($xml_string, -9) == "not found")
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

					// uuid_getvar is targeted - the UUID exists on one specific node
					$handRaisedResponse = $this->executeAndFindResponse($command);

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
						"hand_raised" => ($handRaisedResponse == "true") ? true : false,
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
                // Targeted: get current energy value from the node that has this participant
                $switch_result = $this->executeAndFindResponse($command);
                $result_array = explode("=", $switch_result ?? '=0');
                $tmp_value = $result_array[1];

                if($direction == "up")
                {
                    $tmp_value = $tmp_value + 100;
                }

                if($direction == "down")
                {
                    $tmp_value = $tmp_value - 100;
                }

                // Targeted: set new energy value on the node that has this participant
                $this->executeUntilSuccess($command . ' ' . $tmp_value);
            }

            if($data == "volume_in")
            {
                // Targeted: get current volume from the node that has this participant
                $switch_result = $this->executeAndFindResponse($command);
                $result_array = explode("=", $switch_result ?? '=0');
                $tmp_value = $result_array[1];

                if($direction == "up")
                {
                    $tmp_value = $tmp_value + 1;
                }

                if($direction == "down")
                {
                    $tmp_value = $tmp_value - 1;
                }

                // Targeted: set new volume on the node that has this participant
                $this->executeUntilSuccess($command . ' ' . $tmp_value);
            }

            if($data == "volume_out")
            {
                // Targeted: get current volume from the node that has this participant
                $switch_result = $this->executeAndFindResponse($command);
                $result_array = explode("=", $switch_result ?? '=0');
                $tmp_value = $result_array[1];

                if($direction == "up")
                {
                    $tmp_value = $tmp_value + 1;
                }

                if($direction == "down")
                {
                    $tmp_value = $tmp_value - 1;
                }

                // Targeted: set new volume on the node that has this participant
                $this->executeUntilSuccess($command . ' ' . $tmp_value);
            }

            if($data == "record")
            {
                $recordingDirectory = Setting::getSetting('switch', 'recordings', 'dir') . '/' .  Session::get('domain_name') . '/archive/' . date("Y") . '/' . date("M") . '/' . date("d");

                $file = $recordingDirectory . "/" . $uuid . ".wav";

                $command .= $file;

                if(!file_exists($file))
                {
                    // Targeted: record on the node that has this conference
                    $this->executeUntilSuccess($command);
                }
            }

            if($data == "norecord")
            {
                $recordingDirectory = Setting::getSetting('switch', 'recordings', 'dir') . '/' .  Session::get('domain_name') . '/archive/' . date("Y") . '/' . date("M") . '/' . date("d");

                $file = $recordingDirectory . "/" . $uuid . ".wav";

                $command .= $file;

                // Targeted: stop recording on the node that has this conference
                $this->executeUntilSuccess($command);
            }

            if($data == "kick")
            {
                // Targeted: uuid_kill on the node that has this call
                $this->executeUntilSuccess('uuid_kill ' . $uuid);
            }

            if($data == "kick all")
            {
                $this->endConference($name);
            }

            if($data == "mute" || $data == "unmute" || $data == "mute non_moderator" || $data == "unmute non_moderator")
            {
                // Targeted: mute/unmute on the node that has this participant
                $this->executeUntilSuccess($command);

                // Targeted: set hand_raised on the node that has this UUID
                $this->executeUntilSuccess("uuid_setvar " . $uuid . " hand_raised false");
            }

            if($data == "deaf" || $data == "undeaf" )
            {
                // Targeted: deaf/undeaf on the node that has this participant
                $this->executeUntilSuccess($command);
            }

            if($data == "lock" || $data == "unlock" )
            {
                // Lock/unlock should apply on all nodes that have this conference
                $this->executeOnAllNodes($command);
            }
        }
    }

    //define an alternative kick all
	private function endConference($name)
	{
		$command = "conference '{$name}' xml_list";

		// Get conference XML from the node that has it
        $xml_str = $this->executeAndFindResponse($command);

        if (empty($xml_str)) {
            Log::warning('Conference not found on any node for endConference', ['name' => $name]);
            return;
        }

		try
		{
			$xml = new \SimpleXMLElement($xml_str);
		}
		catch(\Exception $e)
		{
			Log::error('Failed to parse conference XML in endConference: ' . $e->getMessage());
			return;
		}

		$session_uuid = $xml->conference['uuid'];

        $x = 0;

        foreach($xml->conference->members->member as $row)
        {
			$uuid = (string)$row->uuid;

			if(Str::isUuid($uuid))
            {
                // Targeted: uuid_kill on the node that has this call
                $this->executeUntilSuccess('uuid_kill ' . $uuid);
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
