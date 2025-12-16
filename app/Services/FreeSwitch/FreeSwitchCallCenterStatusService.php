<?php

namespace App\Services\FreeSwitch;

use App\Models\CallCenterAgent;
use App\Models\CallCenterQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class FreeSwitchCallCenterStatusService
{
    protected FreeSwitchService $freeSwitchService;

    public function __construct(FreeSwitchService $freeSwitchService)
    {
        $this->freeSwitchService = $freeSwitchService;
    }

    //convert the string to a named array
    private function str_to_named_array($tmp_str, $tmp_delimiter)
    {
        $lines = explode("\n", $tmp_str);

        if(empty($lines))
        {
            return [];
        }

        $fieldNames = explode($tmp_delimiter, array_shift($lines));

        $result = [];

        foreach($lines as $line)
        {
            if($line !== '' && strtoupper($line) !== '+OK')
            {
                $values = explode($tmp_delimiter, $line);

                $row = [];

                foreach($fieldNames as $i => $name)
                {
                    $row[$name] = $values[$i] ?? null;
                }

                $result[] = $row;
            }
        }

        return $result;
    }

	public function getAgentsStatus(CallCenterQueue $callCenterQueue)
    {
		$data = [];

		try
		{
			//send the event socket command and get the response
			$command = "callcenter_config queue list tiers " . $callCenterQueue->queue_extension . "@" . Session::get("domain_name");
			$event_socket_str = $this->freeSwitchService->execute($command);
			$result = $this->str_to_named_array($event_socket_str, '|');

			if(App::hasDebugModeEnabled())
			{
				Log::debug('CallCenterQueueController > list tiers: ', [$event_socket_str]);
			}

			//prepare the result for array_multisort
			$tier_result = [];

			if(is_array($result))
			{
				if(App::hasDebugModeEnabled())
				{
					Log::debug('CallCenterQueueController > result loop: ', $result);
				}

				foreach($result as $row)
				{
					$tier_result[] = [
						'level' => $row['level'] ?? "",
						'position' => $row['position'] ?? "",
						'agent' => $row['agent'] ?? "",
						'state' => trim($row['state'] ?? ""),
						'queue' => $row['queue'] ?? "",
					];
				}
			}

			//sort the array //SORT_ASC, SORT_DESC, SORT_REGULAR, SORT_NUMERIC, SORT_STRING
			if(!empty($tier_result))
			{
				array_multisort($tier_result, SORT_ASC);
			}

			//send the event socket command and get the response
			$command = 'callcenter_config queue list agents ' . $callCenterQueue->queue_extension . "@" . Session::get("domain_name");
			$event_socket_str = $this->freeSwitchService->execute($command);
			$agent_result = $this->str_to_named_array($event_socket_str, '|');

			if(App::hasDebugModeEnabled())
			{
				Log::debug('CallCenterQueueController > list agents: ', [$event_socket_str]);
			}

			//get the agents from the database
			if(empty(Session::get("agents")))
			{
				$agents = CallCenterAgent::where("domain_uuid", Session::get("domain_uuid"))->orderBy("agent_name")->get();

				Session::put("agents", $agents);
			}

			//list the agents
			$data = [];

			if(!empty($tier_result))
			{
				foreach($tier_result as $tier_row)
				{
					$agent = $tier_row['agent'];

					$tier_state = $tier_row['state'];
					$tier_level = $tier_row['level'];
					$tier_position = $tier_row['position'];

					if(!empty($agent_result))
					{
						foreach($agent_result as $agent_row)
						{
							if($tier_row['agent'] == $agent_row['name'])
							{
								$agent_uuid = $agent_row['name'];

								$agent_name = '';

								if(!empty(Session::get('agents')))
								{
									foreach(Session::get('agents') as $agent)
									{
										if($agent['call_center_agent_uuid'] == $agent_uuid)
										{
											$agent_name = $agent['agent_name'];
										}
									}
								}

								if(Str::isUuid($agent_row['uuid']))
								{
									$agent_uuid = $agent_row['uuid'];
								}

								$contact = $agent_row['contact'];
								$agent_extension = preg_replace("/user\//", "", $contact);
								$agent_extension = preg_replace("/@.*./", "", $agent_extension);
								$agent_extension = preg_replace("/{.*}/", "", $agent_extension);
								$status = $agent_row['status'];
								$state = $agent_row['state'];
								$last_status_change = $agent_row['last_status_change'];
								$no_answer_count = $agent_row['no_answer_count'];
								$calls_answered = $agent_row['calls_answered'];

								$last_status_change_seconds = time() - $last_status_change;
								$last_status_change_length_hour = floor($last_status_change_seconds / 3600);
								$last_status_change_length_min = floor($last_status_change_seconds / 60 - ($last_status_change_length_hour * 60));
								$last_status_change_length_sec = $last_status_change_seconds - (($last_status_change_length_hour * 3600) + ($last_status_change_length_min * 60));
								$last_status_change_length_min = sprintf("%02d", $last_status_change_length_min);
								$last_status_change_length_sec = sprintf("%02d", $last_status_change_length_sec);
								$last_status_change_length = $last_status_change_length_hour . ':' . $last_status_change_length_min.':'.$last_status_change_length_sec;

								$data[] = [
									"uuid" => $agent_uuid,
									"name" => $agent_name,
									"extension" => $agent_extension,
									"status" => $status,
									"state" => $state,
									"status_change" => $last_status_change_length,
									"missed" => $no_answer_count,
									"answered" => $calls_answered,
									"tier_state" => $tier_state,
									"tier_level" => $tier_level,
									"tier_position" => $tier_position,
								];
							}
						}
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

	public function getMembersStatus(CallCenterQueue $callCenterQueue)
    {
		$data = [
			"status" => [],
			"members" => [],
		];

		try
		{
			//send the event socket command and get the response
			$command = "callcenter_config queue list members " . $callCenterQueue->queue_extension . "@" . Session::get("domain_name");
			$event_socket_str = $this->freeSwitchService->execute($command);
			$result = $this->str_to_named_array($event_socket_str, '|');

			if(App::hasDebugModeEnabled())
			{
				Log::debug('CallCenterQueueController > list members: ', [$event_socket_str]);
			}

			$q_waiting = 0;
			$q_trying = 0;
			$q_answered = 0;

			foreach($result as $row)
			{
				$state = $row["state"];
				$q_trying += ($state == "Trying") ? 1 : 0;
				$q_waiting += ($state == "Waiting") ? 1 : 0;
				$q_answered += ($state == "Answered") ? 1 : 0;
			}

			$data["status"] = [
				"waiting" => $q_waiting,
				"trying" => $q_trying,
				"answered" => $q_answered,
			];

			foreach($result as $row)
			{
				$queue = $row['queue'];
				$system = $row['system'] ?? null;
				$uuid = $row['uuid'];
				$session_uuid = $row['session_uuid'];
				$caller_number = $row['cid_number'];
				$caller_name = $row['cid_name'];
				$system_epoch = $row['system_epoch'];
				$joined_epoch = $row['joined_epoch'];
				$rejoined_epoch = $row['rejoined_epoch'];
				$bridge_epoch = $row['bridge_epoch'];
				$abandoned_epoch = $row['abandoned_epoch'];
				$base_score = $row['base_score'];
				$skill_score = $row['skill_score'];
				$serving_agent = $row['serving_agent'];
				$serving_system = $row['serving_system'];
				$state = $row['state'];
				$joined_seconds = time() - $joined_epoch;
				$joined_length_hour = floor($joined_seconds/3600);
				$joined_length_min = floor($joined_seconds/60 - ($joined_length_hour * 60));
				$joined_length_sec = $joined_seconds - (($joined_length_hour * 3600) + ($joined_length_min * 60));
				$joined_length_min = sprintf("%02d", $joined_length_min);
				$joined_length_sec = sprintf("%02d", $joined_length_sec);
				$joined_length = $joined_length_hour.':'.$joined_length_min.':'.$joined_length_sec;

				//get the serving agent name
				$serving_agent_name = '';

				if(!empty(Session::get('agents')))
				{
					foreach(Session::get('agents') as $agent)
					{
						if($agent['call_center_agent_uuid'] == $serving_agent)
						{
							$serving_agent_name = $agent['agent_name'];
						}
					}
				}

				$data["members"][] = [
					"joined_length" => $joined_length,
					"caller_name" => $caller_name,
					"caller_number" => $caller_number,
					"state" => $state,
					"serving_agent_name" => $serving_agent_name,
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
