<?php

namespace App\Services;

use App\Http\Requests\InboundDialplanRequest;
use App\Http\Requests\OutboundDialplanRequest;
use App\Http\Requests\QueueDialplanRequest;
use App\Models\Destination;
use App\Models\Dialplan;
use App\Models\Fax;
use App\Repositories\DialplanDetailRepository;
use App\Repositories\DialplanRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class DialplanService
{
	protected $dialplanRepository;
	protected $dialplanDetailRepository;

	public function __construct(DialplanRepository $dialplanRepository, DialplanDetailRepository $dialplanDetailRepository)
    {
        $this->dialplanRepository = $dialplanRepository;
        $this->dialplanDetailRepository = $dialplanDetailRepository;
    }

	public function saveDialplan($dialplanData, $dialplanDetailData, ?Dialplan $dialplan)
	{
		if($dialplan)
		{
			$this->dialplanRepository->update($dialplan->dialplan_uuid, $dialplanData);

			$this->dialplanDetailRepository->deleteByDialplan($dialplan);
		}
		else
		{
			$dialplan = $this->dialplanRepository->create($dialplanData);
		}

		$this->dialplanDetailRepository->create($dialplan, $dialplanDetailData);

		$this->dialplanRepository->buildXML($dialplan);

		return $dialplan;
	}

	public function setInbound(array $data, ?Destination $destination, ?Dialplan $dialplan)
	{
		$dialplanData = [
            "domain_uuid" => Session::get("domain_uuid"),
            "app_uuid" => config('coolpbx.inbound_route.app_uuid') ?? $data["app_uuid"],
//          "app_uuid" => $data["app_uuid"] ?? 'c03b422e-13a8-bd1b-e42b-b6b9b4d27ce4',
            "dialplan_name" => $data["dialplan_name"],
            "dialplan_number" => isset($destination) ? $destination->destination_number : null,
            "dialplan_order" => $data["dialplan_order"],
            "dialplan_continue" => "false",
            "dialplan_destination" => "false",
            "dialplan_context" => "public",
            "dialplan_enabled" => $data["dialplan_enabled"] ?? "false",
            "dialplan_description" => $data["dialplan_description"],
        ];

		$dialplanDetailData = [];

		$y = 0;

		$condition_field_1 = $data["condition_field_1"];
		$condition_expression_1 = $data["condition_expression_1"];
		// $condition_field_2 = $data["condition_field_2"]; //TODO: remove?
		// $condition_expression_2 = $data["condition_expression_2"]; //TODO: remove?

		$destination_accountcode = '';
		$destination_carrier = '';
        $destination_carrier_uuid = '';
        $destination_cid_name_prefix = '';
        $destination_record = '';
        $destination_hold_music = '';
        $destination_distinctive_ring = '';

		$limit = $data["limit"] ?? 0;
		$caller_id_outbound_prefix = $data["caller_id_outbound_prefix"] ?? '';
		$fax_uuid = null;
		$domain_name = Session::get("domain_name");
		$condition_expression_2 = null;
		$condition_field_2 = null;

		if($destination)
		{
			$condition_expression_2 = $condition_expression_1;
			$condition_field_2 = $condition_field_1;
			//TODO: review how to build condition_expression_1 properly
			$condition_expression_1 = $destination->destination_number;
			$fax_uuid = $destination->fax_uuid;
			$destination_carrier = $destination->carrier ? $destination->carrier->carrier_name : null;
            $destination_carrier_uuid = $destination->carrier ? $destination->carrier->carrier_uuid : null;
			$destination_accountcode = $destination->destination_accountcode;
			$destination_cid_name_prefix = $destination->destination_cid_name_prefix;
			$destination_record = $destination->destination_record;
			$destination_hold_music = $destination->destination_hold_music;
			$destination_distinctive_ring = $destination->destination_distinctive_ring;
		}

		$action_1 = $data["action_1"];
		// $action_2 = $data["action_2"]; //TODO: remove?

		list($action_application_1, $action_data_1) = $this->parseAction($action_1);
		// list($action_application_2, $action_data_2) = $this->parseAction($action_2); //TODO: remove?

		if($condition_field_1 && $condition_expression_1)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "condition", type: $condition_field_1, data: $condition_expression_1, order: $y++ * 10);
		}

		if($condition_field_2 && $condition_expression_2)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "condition", type: $condition_field_2, data: $condition_expression_2, order: $y++ * 10);
		}

		if($destination_accountcode)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "accountcode={$destination_accountcode}", order: $y++ * 10);
		}

		if($destination_carrier)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "carrier={$destination_carrier}", order: $y++ * 10);
            $dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "carrier_uuid={$destination_carrier_uuid}", order: $y++ * 10);
		}

		if($destination_cid_name_prefix)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "effective_caller_id_name={$destination_cid_name_prefix}#\${caller_id_name}", order: $y++ * 10, inline:"false");
		}

		if($destination_record == 'true')
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "record_path=\${recordings_dir}/\${domain_name}/archive/\${strftime(%Y)}/\${strftime(%b)}/\${strftime(%d)}", order: $y++ * 10, inline:"true");
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "record_name=\${uuid}.\${record_ext}", order: $y++ * 10, inline:"true");
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "record_append=true", order: $y++ * 10, inline:"true");
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "record_in_progress=true", order: $y++ * 10, inline:"true");
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "recording_follow_transfer=true", order: $y++ * 10, inline:"true");
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "record_session", data: "\${record_path}/\${record_name}", order: $y++ * 10, inline:"false");
		}

		if($destination_hold_music)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "export", data: "hold_music={$destination_hold_music}", order: $y++ * 10, inline:"true");
		}

		if($destination_distinctive_ring)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "export", data: "sip_h_Alert-Info={$destination_distinctive_ring}", order: $y++ * 10, inline:"true");
		}

		if($limit)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "limit", data: "hash {$domain_name} inbound {$limit} !USER_BUSY", order: $y++ * 10);
		}

		if($caller_id_outbound_prefix)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "effective_caller_id_number={$caller_id_outbound_prefix}\${caller_id_number}", order: $y++ * 10);
		}

		if(Str::isUuid($fax_uuid ?? null))
		{
			$fax = Fax::where("domain_uuid", Session::get("domain_uuid"))->where("fax_uuid", $fax_uuid)->first();

			if($fax)
			{
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "codec_string=PCMU,PCMA", order: $y++ * 10);
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "tone_detect_hits=1", order: $y++ * 10);
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "set", data: "execute_on_tone_detect=transfer {$fax->fax_extension} XML " . Session::get("domain_name"), order: $y++ * 10);
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "tone_detect", data: "fax 1100 r +5000", order: $y++ * 10);
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "sleep", data: "3000", order: $y++ * 10);
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "export", data: "codec_string=\${ep_codec_string}", order: $y++ * 10);
			}
		}

		if(in_array($action_application_1, ["ivr", "conference"]))
		// if(in_array($action_application_1, ["ivr", "conference"]) || in_array($action_application_2, ["ivr", "conference"]))  //TODO: remove?
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: "answer", data: "", order: $y++ * 10);
		}

		// Add final actions
		if($action_application_1 && $action_data_1)
		{
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: "action", type: $action_application_1, data: $action_data_1, order: $y++ * 10);
		}

  		//TODO: remove?
		// if($action_application_2 && $action_data_2)
		// {
		// 	$this->buildDialplanDetail(tag: "action", type:$action_application_2, data: $action_data_2, order: $y++ * 10);
		// }

		return $this->saveDialplan($dialplanData, $dialplanDetailData, $dialplan);
	}

	public function setOutbound(OutboundDialplanRequest $request, ?Dialplan $dialplan)
	{
		$dialplan_name = $request->input("dialplan_name") ?? '';
		$dialplan_order = $request->input("dialplan_order") ?? '';
		$dialplan_expressions = explode("\n", $request->input("dialplan_expression") ?? '');
		$prefix_number = $request->input("prefix_number") ?? '';
		$condition_field_1 = $request->input("condition_field_1") ?? '';
		$condition_expression_1 = $request->input("condition_expression_1") ?? '';
		$condition_field_2 = $request->input("condition_field_2") ?? '';
		$condition_expression_2 = $request->input("condition_expression_2") ?? '';
		$gateway = $request->input("gateway") ?? '';
		$limit = $request->input("limit") ?? '';
		$accountcode = $request->input("accountcode") ?? '';
		$toll_allow = $request->input("toll_allow") ?? '';
		$pin_numbers_enable = $request->input("pin_numbers_enabled") ?? null;

		if(empty($pin_numbers_enable))
		{
			$pin_numbers_enable = "false";
		}

		//set the default type
		$gateway_type = 'gateway';
		$gateway_2_type = 'gateway';
		$gateway_3_type = 'gateway';

		//set the gateway type to bridge
		if (strtolower(substr($gateway, 0, 6)) == "bridge")
		{
			$gateway_type = 'bridge';
		}

		//set the type to enum
		if (strtolower(substr($gateway, 0, 4)) == "enum")
		{
			$gateway_type = 'enum';
		}

		//set the type to freetdm
		if (strtolower(substr($gateway, 0, 7)) == "freetdm")
		{
			$gateway_type = 'freetdm';
		}

		//set the type to transfer
		if (strtolower(substr($gateway, 0, 8)) == "transfer")
		{
			$gateway_type = 'transfer';
		}

		//set the type to dingaling
		if (strtolower(substr($gateway, 0, 4)) == "xmpp")
		{
			$gateway_type = 'xmpp';
		}

		//set the gateway_uuid and gateway_name
		if ($gateway_type == "gateway")
		{
			$gateway_array = explode(":", $gateway);
			$gateway_uuid = $gateway_array[0];
			$gateway_name = $gateway_array[1];
		}
		else
		{
			$gateway_name = '';
			$gateway_uuid = '';
		}

		//set the gateway_2 variable
		$gateway_2 = $_POST["gateway_2"];

		//set the type to bridge
		if (strtolower(substr($gateway_2, 0, 6)) == "bridge")
		{
			$gateway_2_type = 'bridge';
		}

		//set type to enum
		if (strtolower(substr($gateway_2, 0, 4)) == "enum")
		{
			$gateway_2_type = 'enum';
		}

		//set the type to freetdm
		if (strtolower(substr($gateway_2, 0, 7)) == "freetdm")
		{
			$gateway_2_type = 'freetdm';
		}
		//set the type to transfer
		if (strtolower(substr($gateway_2, 0, 8)) == "transfer")
		{
			$gateway_type = 'transfer';
		}

		//set the type to dingaling
		if (strtolower(substr($gateway_2, 0, 4)) == "xmpp")
		{
			$gateway_2_type = 'xmpp';
		}

		//set the gateway_2_id and gateway_2_name
		if ($gateway_2_type == "gateway" && !empty($_POST["gateway_2"]))
		{
			$gateway_2_array = explode(":", $gateway_2);
			$gateway_2_id = $gateway_2_array[0];
			$gateway_2_name = $gateway_2_array[1];
		}
		else
		{
			$gateway_2_id = '';
			$gateway_2_name = '';
		}

		//set the gateway_3 variable
		$gateway_3 = $_POST["gateway_3"];

		//set the type to bridge
		if (strtolower(substr($gateway_3, 0, 6)) == "bridge")
		{
			$gateway_3_type = 'bridge';
		}

		//set the type to enum
		if (strtolower(substr($gateway_3, 0, 4)) == "enum")
		{
			$gateway_3_type = 'enum';
		}

		//set the type to freetdm
		if (strtolower(substr($gateway_3, 0, 7)) == "freetdm")
		{
			$gateway_3_type = 'freetdm';
		}

		//set the type to dingaling
		if (strtolower(substr($gateway_3, 0, 4)) == "xmpp")
		{
			$gateway_3_type = 'xmpp';
		}

		//set the type to transfer
		if (strtolower(substr($gateway_3, 0, 8)) == "transfer")
		{
			$gateway_type = 'transfer';
		}

		//set the gateway_3_id and gateway_3_name
		if ($gateway_3_type == "gateway" && !empty($_POST["gateway_3"]))
		{
			$gateway_3_array = explode(":", $gateway_3);
			$gateway_3_id = $gateway_3_array[0];
			$gateway_3_name = $gateway_3_array[1];
		}
		else
		{
			$gateway_3_id = '';
			$gateway_3_name = '';
		}

        if(App::hasDebugModeEnabled())
        {
            Log::notice('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] request: '.print_r(request()->toArray(), true));
        }
		//set additional variables
		$dialplan_enabled = $request->input("dialplan_enabled") ?? 'false';
		$dialplan_description = $request->input("dialplan_description");

		//set default to enabled
		if (empty($dialplan_enabled))
		{
			$dialplan_enabled = "true";
		}

		foreach($dialplan_expressions as $dialplan_expression)
		{
			$dialplan_expression = trim($dialplan_expression);

			if(!empty($dialplan_expression))
			{
				switch ($dialplan_expression)
				{
					case "^(\d{7})$":
						$abbrv = "7d";
						break;
					case "^(\d{8})$":
						$abbrv = "8d";
						break;
					case "^(\d{9})$":
						$abbrv = "9d";
						break;
					case "^(\d{10})$":
						$abbrv = "10d";
						break;
					case "^\+?(\d{11})$":
						$abbrv = "11d";
						break;
					case "^(?:\+1|1)?([2-9]\d{2}[2-9]\d{2}\d{4})$":
						$abbrv = "10-11-NANP";
						break;
					case "^(011\d{9,17})$":
						$abbrv = "011.9-17d";
						break;
					case "^\+?1?((?:264|268|242|246|441|284|345|767|809|829|849|473|658|876|664|787|939|869|758|784|721|868|649|340|684|671|670|808)\d{7})$":
						$abbrv = "011.9-17d";
						break;
					case "^(\d{12,20})$":
						$abbrv = __('International');
						break;
					case "^(311)$":
						$abbrv = "311";
						break;
					case "^(411)$":
						$abbrv = "411";
						break;
					case "^(711)$":
						$abbrv = "711";
						break;
					case "(^911$|^933$)":
						$abbrv = "911";
						break;
					case "(^988$)":
						$abbrv = "988";
						break;
					case "^9(\d{3})$":
						$abbrv = "9.3d";
						break;
					case "^9(\d{4})$":
						$abbrv = "9.4d";
						break;
					case "^9(\d{7})$":
						$abbrv = "9.7d";
						break;
					case "^9(\d{10})$":
						$abbrv = "9.10d";
						break;
					case "^9(\d{11})$":
						$abbrv = "9.11d";
						break;
					case "^9(\d{12,20})$":
						$abbrv = "9.12-20";
						break;
					case "^1?(8(00|33|44|55|66|77|88)[2-9]\d{6})$":
						$abbrv = "800";
						break;
					case "^0118835100\d{8}$":
						$abbrv = "inum";
						break;
					default:
						$abbrv = $this->filename_safe($dialplan_expression);
				}

				// Use as outbound prefix all digits beetwen ^ and first (
				$tmp_prefix = preg_replace("/^\^(\d{1,})\(.*/", "$1", $dialplan_expression);
				$tmp_prefix == $dialplan_expression ? $outbound_prefix = "" : $outbound_prefix = $tmp_prefix;

				if ($gateway_type == "gateway")
				{
					$dialplan_name = $gateway_name . "." . $abbrv;

					if ($abbrv == "988")
					{
						$bridge_data = "sofia/gateway/" . $gateway_uuid . "/" . $prefix_number . "18002738255";
					}
					else
					{
						$bridge_data = "sofia/gateway/" . $gateway_uuid . "/" . $prefix_number . "\$1";
					}
				}

				if (!empty($gateway_2_name) && $gateway_2_type == "gateway")
				{
					$extension_2_name = $gateway_2_id . "." . $abbrv;

					if ($abbrv == "988")
					{
						$bridge_2_data = "sofia/gateway/" . $gateway_2_id . "/" . $prefix_number . "18002738255";
					}
					else
					{
						$bridge_2_data = "sofia/gateway/" . $gateway_2_id . "/" . $prefix_number . "\$1";
					}
				}

				if (!empty($gateway_3_name) && $gateway_3_type == "gateway")
				{
					$extension_3_name = $gateway_3_id . "." . $abbrv;

					if ($abbrv == "988")
					{
						$bridge_3_data = "sofia/gateway/" . $gateway_3_id . "/" . $prefix_number . "18002738255";
					}
					else
					{
						$bridge_3_data = "sofia/gateway/" . $gateway_3_id . "/" . $prefix_number . "\$1";
					}
				}

				if ($gateway_type == "freetdm")
				{
					$dialplan_name = "freetdm." . $abbrv;
					$bridge_data = $gateway . "/1/a/" . $prefix_number . "\$1";
				}

				if ($gateway_2_type == "freetdm")
				{
					$extension_2_name = "freetdm." . $abbrv;
					$bridge_2_data .= $gateway_2 . "/1/a/" . $prefix_number . "\$1";
				}

				if ($gateway_3_type == "freetdm")
				{
					$extension_3_name = "freetdm." . $abbrv;
					$bridge_3_data .= $gateway_3 . "/1/a/" . $prefix_number . "\$1";
				}

				if ($gateway_type == "xmpp")
				{
					$dialplan_name = "xmpp." . $abbrv;
					$bridge_data = "dingaling/gtalk/+" . $prefix_number . "\$1@voice.google.com";
				}

				if ($gateway_2_type == "xmpp")
				{
					$extension_2_name = "xmpp." . $abbrv;
					$bridge_2_data .= "dingaling/gtalk/+" . $prefix_number . "\$1@voice.google.com";
				}

				if ($gateway_3_type == "xmpp")
				{
					$extension_3_name = "xmpp." . $abbrv;
					$bridge_3_data .= "dingaling/gtalk/+" . $prefix_number . "\$1@voice.google.com";
				}

				if ($gateway_type == "bridge")
				{
					$dialplan_name = "bridge." . $abbrv;
					$gateway_array = explode(":", $gateway);
					$bridge_data = $gateway_array[1];
				}

				if ($gateway_2_type == "bridge")
				{
					$dialplan_name = "bridge." . $abbrv;
					$gateway_array = explode(":", $gateway_2);
					$bridge_2_data = $gateway_array[1];
				}

				if ($gateway_3_type == "bridge")
				{
					$dialplan_name = "bridge." . $abbrv;
					$gateway_array = explode(":", $gateway_3);
					$bridge_3_data = $gateway_array[1];
				}

				if ($gateway_type == "enum")
				{
					if (empty($bridge_2_data))
					{
						$dialplan_name = "enum." . $abbrv;
					}
					else
					{
						$dialplan_name = $extension_2_name;
					}
					$bridge_data = "\${enum_auto_route}";
				}

				if ($gateway_2_type == "enum")
				{
					$bridge_2_data .= "\${enum_auto_route}";
				}

				if ($gateway_3_type == "enum")
				{
					$bridge_3_data .= "\${enum_auto_route}";
				}

				if ($gateway_type == "transfer")
				{
					$dialplan_name = "transfer." . $abbrv;
					$gateway_array = explode(":", $gateway);
					$bridge_data = $gateway_array[1];
				}

				if ($gateway_2_type == "transfer")
				{
					$gateway_array = explode(":", $gateway_2);
					$bridge_2_data = $gateway_array[1];
				}

				if ($gateway_3_type == "transfer")
				{
					$gateway_array = explode(":", $gateway_3);
					$bridge_3_data = $gateway_array[1];
				}

				if (empty($dialplan_order))
				{
					$dialplan_order = '333';
				}

				$dialplan_context = Session::get("domain_name");
				$dialplan_continue = 'false';
				// $app_uuid = '8c914ec3-9fc0-8ab5-4cda-6c9288bdc9a3';

				//call direction
				$dialplanData = [
					"domain_uuid" => Session::get("domain_uuid"),
					"app_uuid" => config('coolpbx.outbound_route.app_uuid'),
//					"app_uuid" => $request->input("app_uuid"),
					"dialplan_name" => "call_direction-outbound",
					"dialplan_order" => "22",
					"dialplan_continue" => "true",
					"dialplan_context" => $dialplan_context,
					"dialplan_enabled" => $dialplan_enabled,
					"dialplan_description" => $dialplan_description,
				];

				$y = 0;

				$dialplanDetailData = [];

				$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'condition', type: '${user_exists}', data: 'false', order: $y++ * 10, group: 0, enabled: 'true');
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'condition', type: '${call_direction}', data: '^$', order: $y++ * 10, group: 0, enabled: 'true');
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'condition', type: 'destination_number', data: $dialplan_expression, order: $y++ * 10, group: 0, enabled: 'true');
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'export', data: 'call_direction=outbound', order: $y++ * 10, inline: 'true', group: 0, enabled: 'true');

				$this->saveDialplan($dialplanData, $dialplanDetailData, $dialplan);

				//outbound route
				$dialplanData = [
					"domain_uuid" => Session::get("domain_uuid"),
					"app_uuid" => $request->input("app_uuid"),
					"dialplan_name" => $dialplan_name,
					"dialplan_order" => $dialplan_order,
					"dialplan_continue" => $dialplan_continue,
					"dialplan_context" => $dialplan_context,
					"dialplan_enabled" => $dialplan_enabled,
					"dialplan_description" => $dialplan_description,
				];

				$y = 1;

				$dialplanDetailData = [];

				$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'condition', type: '${user_exists}', data: 'false', order: $y++ * 10, group: 0, enabled: 'true');

				if(!empty($toll_allow))
				{
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'condition', type: '${toll_allow}', data: $toll_allow, order: $y++ * 10, group: 0, enabled: 'true');
				}

				$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'condition', type: 'destination_number', data: $dialplan_expression, order: $y++ * 10, group: 0, enabled: 'true');

				if ($gateway_type != "transfer")
				{
					if (!empty($accountcode))
					{
						$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'sip_h_accountcode=' . $accountcode, order: $y++ * 10, group: 0, enabled: 'false');
					}
					else
					{
						$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'sip_h_accountcode=${accountcode}', order: $y++ * 10, group: 0, enabled: 'false');
					}
				}

				$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'export', data: 'call_direction=outbound', inline: 'true', order: $y++ * 10, group: 0, enabled: 'true');
				$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'unset', data: 'call_timeout', order: $y++ * 10, group: 0, enabled: 'true');

				if ($gateway_type != "transfer")
				{
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'hangup_after_bridge=true', order: $y++ * 10, group: 0, enabled: 'true');
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: ($dialplan_expression == '(^911$|^933$)') ? 'effective_caller_id_name=${emergency_caller_id_name}' : 'effective_caller_id_name=${outbound_caller_id_name}', order: $y++ * 10, group: 0, enabled: 'true');
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: ($dialplan_expression == '(^911$|^933$)') ? 'effective_caller_id_number=${emergency_caller_id_number}' : 'effective_caller_id_number=${outbound_caller_id_number}', order: $y++ * 10, group: 0, enabled: 'true');

					if ($dialplan_expression == '(^911$|^933$)')
					{
						$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'lua', data: "email.lua \${email_to} \${email_from} '' 'Emergency Call' '\${sip_from_user}@\${domain_name} has called 911 emergency'", order: $y++ * 10, group: 0, enabled: 'false');
					}

					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'inherit_codec=true', order: $y++ * 10, group: 0, enabled: 'true');
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'ignore_display_updates=true', order: $y++ * 10, group: 0, enabled: 'true');
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'callee_id_number=$1', order: $y++ * 10, group: 0, enabled: 'true');
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'continue_on_fail=1,2,3,6,18,21,27,28,31,34,38,41,42,44,58,88,111,403,501,602,607', order: $y++ * 10, group: 0, enabled: 'true');
				}

				if ($gateway_type == "enum" || $gateway_2_type == "enum")
				{
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'enum', data: $prefix_number . "$1 e164.org", order: $y++ * 10, group: 0, enabled: 'true');
				}

				if (!empty($limit))
				{
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'limit', data: "hash \${domain_name} outbound " . $limit . " !USER_BUSY", order: $y++ * 10, group: 0, enabled: 'true');
				}

				if (!empty($outbound_prefix))
				{
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'outbound_prefix=' . $outbound_prefix, order: $y++ * 10, group: 0, enabled: 'true');
				}

				if ($pin_numbers_enable == "true")
				{
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'pin_number=database', order: $y++ * 10, group: 0, enabled: 'true');
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'lua', data: 'pin_number.lua', order: $y++ * 10, group: 0, enabled: 'true');
				}

				if (strlen($prefix_number) > 2)
				{
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'provider_prefix=' . $prefix_number, order: $y++ * 10, group: 0, enabled: 'true');
				}

				if ($gateway_type == "transfer")
				{
					$dialplan_detail_type = 'transfer';
				}
				else
				{
					$dialplan_detail_type = 'bridge';
				}

				$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: $dialplan_detail_type, data: $bridge_data, order: $y++ * 10, group: 0, enabled: 'true');

				if (!empty($bridge_2_data))
				{
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'bridge', data: $bridge_2_data, order: $y++ * 10, group: 0, enabled: 'true');
				}

				if (!empty($bridge_3_data))
				{
					$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'bridge', data: $bridge_3_data, order: $y++ * 10, group: 0, enabled: 'true');
				}

				$this->saveDialplan($dialplanData, $dialplanDetailData, $dialplan);
			}
		}

		return redirect()->to(route("dialplans.index") . "?app_uuid=" . urlencode($request->input("app_uuid")));
	}

	public function setQueue(array $data)
	{
		$dialplanData = [
            "domain_uuid" => Session::get("domain_uuid"),
            "app_uuid" => config('coolpbx.queue.app_uuid'),
//            "app_uuid" => $data["app_uuid"] ?? '16589224-c876-aeb3-f59f-523a1c0801f7',
            "dialplan_name" => $data["extension_name"],
            "dialplan_order" => $data["dialplan_order"],
            "dialplan_continue" => "false",
            "dialplan_destination" => "false",
            "dialplan_context" => $data["dialplan_context"] ?? Session::get("domain_name"),
            "dialplan_enabled" => $data["dialplan_enabled"] ?? "false",
            "dialplan_description" => $data["dialplan_description"],
        ];

		$queue_name = $data["extension_name"] . "@\${domain_name}";

		$dialplanDetailData = [];

		$y = 0;

		//set the destination number
		$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'condition', type: 'destination_number', data: "^{$data["queue_extension_number"]}$", order: $y++ * 10, group: 1, enabled: 'true', break: ((strlen($data["agent_queue_extension_number"]) > 0) || (!empty($data["agent_login_logout_extension_number"]))) ? 'on-true' : '');

		//set the hold music
		$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: "fifo_music=\$\${hold_music}", order: $y++ * 10, group: 1, inline: "true");

		//action answer
		$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'answer', data: "", order: $y++ * 10, group: 1);

		//action fifo
		$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'fifo', data: "{$queue_name} in", order: $y++ * 10, group: 1);

		// Caller Queue / Agent Queue
		if(!empty($data["agent_queue_extension_number"]))
		{
			$y = 0;

			//set the destination number
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'condition', type: 'destination_number', data: "^{$data["agent_queue_extension_number"]}$", order: $y++ * 10, group: 2, break: (!empty($data["agent_login_logout_extension_number"])) ? 'on-true' : '');

			//set the hold music
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: "fifo_music=\$\${hold_music}", order: $y++ * 10, group: 2, inline: "true");

			//action answer
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'answer', data: "", order: $y++ * 10, group: 2);

			//action fifo
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'fifo', data: "{$queue_name} out wait", order: $y++ * 10, group: 2);
		}

		// agent or member login / logout
		if(!empty($data["agent_login_logout_extension_number"]))
		{
			$y = 0;

			//set the destination number
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'condition', type: 'destination_number', data: "^{$data["agent_login_logout_extension_number"]}$", order: $y++ * 10, group: 3, break: 'on-true');

			//set the queue_name
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: "queue_name={$queue_name}", order: $y++ * 10, group: 3, inline: "true");

			//set the user_name
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: 'user_name=${caller_id_number}@${domain_name}', order: $y++ * 10, group: 3, inline: "true");

			//set the fifo_simo
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: "fifo_simo=1", order: $y++ * 10, group: 3, inline: "true");

			//set the fifo_timeout
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: "fifo_timeout=10", order: $y++ * 10, group: 3, inline: "true");

			//set the fifo_lag
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: "fifo_lag=10", order: $y++ * 10, group: 3, inline: "true");

			//set the pin_number
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'set', data: "pin_number=", order: $y++ * 10, group: 3, inline: "true");

			//action lua
			$dialplanDetailData[] = $this->buildDialplanDetail(tag: 'action', type: 'lua', data: "fifo_member.lua", order: $y++ * 10, group: 3);
		}

		return $this->saveDialplan($dialplanData, $dialplanDetailData, null);
	}

	private function parseAction(?string $action): array
	{
		if(!$action)
		{
			return [null, null];
		}

		$parts = explode(":", $action);
		$app = array_shift($parts);
		$data = implode(":", $parts);

		return [$app, $data];
	}

	public function buildDialplanDetail(string $tag, string $type, string $data, string $break = '', string $inline = '', int $order = 0, int $group = 0, string $enabled = 'false')
	{
		return [
			"dialplan_detail_tag" => $tag,
			"dialplan_detail_type" => $type,
			"dialplan_detail_data" => $data,
			"dialplan_detail_break" => $break,
			"dialplan_detail_inline" => $inline,
			"dialplan_detail_group" => $group,
			"dialplan_detail_order" => $order,
			"dialplan_detail_enabled" => $enabled,
		];
	}

	private function filename_safe($filename)
	{
		//lower case
		$filename = strtolower($filename);

		//replace spaces with a '_'
		$filename = str_replace(" ", "_", $filename);

		//loop through string
		$result = '';

		for($i=0; $i<strlen($filename); $i++)
		{
			if(preg_match('([0-9]|[a-z]|_)', $filename[$i]))
			{
				$result .= $filename[$i];
			}
		}

		return $result;
	}
}
