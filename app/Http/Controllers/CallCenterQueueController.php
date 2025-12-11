<?php

namespace App\Http\Controllers;

use App\Facades\FreeSwitch;
use App\Models\CallCenterAgent;
use App\Models\CallCenterQueue;
use App\Repositories\CallCenterQueueRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CallCenterQueueController extends Controller
{
    protected CallCenterQueueRepository $callCenterQueueRepository;

    public function __construct(CallCenterQueueRepository $callCenterQueueRepository)
    {
        $this->callCenterQueueRepository = $callCenterQueueRepository;
    }

    public function index()
    {
        return view("pages.callCenterQueue.index");
    }

    public function create()
    {
        return view("pages.callCenterQueue.form");
    }

    public function edit($queueUuid)
    {
        $callCenterQueue = $this->callCenterQueueRepository->findByUuid($queueUuid);
        $queueUuid = $callCenterQueue->call_center_queue_uuid;

        return view("pages.callCenterQueue.form", compact("queueUuid"));
    }

    public function showAgents(CallCenterQueue $callCenterQueue)
    {
        return view("pages.callCenterQueue.agents");
    }

    //convert the string to a named array
	private function str_to_named_array($tmp_str, $tmp_delimiter)
    {
		$tmp_array = explode("\n", $tmp_str);

        $result = [];

    	if(trim(strtoupper($tmp_array[0])) != "+OK")
        {
			$tmp_field_name_array = explode($tmp_delimiter, $tmp_array[0]);

        	$x = 0;

        	if(!empty($tmp_array))
            {
                foreach($tmp_array as $row)
                {
                    if($x > 0)
                    {
                        $tmp_field_value_array = explode($tmp_delimiter, $tmp_array[$x]);

                        $y = 0;

                        if(!empty($tmp_field_value_array))
                        {
                            foreach($tmp_field_value_array as $tmp_value)
                            {
                                $tmp_name = $tmp_field_name_array[$y];

                                if(trim(strtoupper($tmp_value)) != "+OK")
                                {
                                    $result[$x][$tmp_name] = $tmp_value;
                                }

                                $y++;
                            }
                        }
                    }

                    $x++;
                }
            }

            unset($row);
		}

		return $result;
	}

    public function getAgentsStatus(CallCenterQueue $callCenterQueue)
    {
        //send the event socket command and get the response
        $command = "callcenter_config queue list tiers " . $callCenterQueue->queue_extension . "@" . Session::get("domain_name");
        $event_socket_str = FreeSwitch::execute($command);
        $result = $this->str_to_named_array($event_socket_str, '|');

        if(App::hasDebugModeEnabled())
        {
            Log::debug('CallCenterQueueController > list tiers: ', [$event_socket_str]);
        }

        //prepare the result for array_multisort
        $tier_result = [];
        $x = 0;

        if(is_array($result))
        {
            if(App::hasDebugModeEnabled())
            {
                Log::debug('CallCenterQueueController > result loop: ', $result);
            }

            foreach($result as $row)
            {
                $tier_result[$x]['level'] = $row['level'];
                $tier_result[$x]['position'] = $row['position'];
                $tier_result[$x]['agent'] = $row['agent'];
                $tier_result[$x]['state'] = trim($row['state']);
                $tier_result[$x]['queue'] = $row['queue'];

                $x++;
            }
        }

        //sort the array //SORT_ASC, SORT_DESC, SORT_REGULAR, SORT_NUMERIC, SORT_STRING
        if(!empty($tier_result))
        {
            array_multisort($tier_result, SORT_ASC);
        }

        //send the event socket command and get the response
        $command = 'callcenter_config queue list agents ' . $callCenterQueue->queue_extension . "@" . Session::get("domain_name");
        $event_socket_str = FreeSwitch::execute($command);
        $agent_result = $this->str_to_named_array($event_socket_str, '|');

        if(App::hasDebugModeEnabled())
        {
            Log::debug('CallCenterQueueController > list agents: ', $event_socket_str);
        }

        //get the agents from the database
        if(empty(Session::get("agents")) || !is_array(Session::get("agents")))
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

                            if(is_array(Session::get('agents')))
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

        dd($data);
    }
}
