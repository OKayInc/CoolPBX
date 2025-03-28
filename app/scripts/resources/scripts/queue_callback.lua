--      Contributor(s):
--      Luis Daniel Lucio Quiroz <dlucio@okay.com.mx>
--
--      First CC:  <action application="bind_digit_action" data="queue-callback,*,exec:execute_extension,callback-${caller_id_number}-${destination_number}1 XML ${domain_name}"/>
--               <action application="digit_action_set_realm" data="inqueue"/>
--               <action application="set" data="bridge_pre_execute_aleg_app=clear_digit_action"/>
--               <action application="set" data="bridge_pre_execute_aleg_data=all"/>

--      Second CC: <action application="set" data="cc_base_score=1000"/>

--      Auxiliar Dialplan:
--      <extension name="callcentre_call_back" continue="false" uuid="dfb023b2-8b42-4254-a11a-f2be44fc4ad2">
--      <condition field="destination_number" expression="^callback-(\d+)-(\d+)">
--              <action application="set" data="queue_caller_id_number=$1"/>
--              <action application="set" data="queue_original_id_number=$2"/>
--              <action application="lua" data="queue_callback.lua"/>
--      </condition>
--      </extension>


-- queue-callback,*,exec:execute_extension,callback-${caller_id_number}-${destination_number}1 XML ${domain_name}
	require "resources.functions.config";

	debug.sql = true;
	skip_cdr_analysis = true;

--define the trim function
	require "resources.functions.trim";

--add is_numeric
	function is_numeric(text)
		if type(text)~="string" and type(text)~="number" then return false end
		return tonumber(text) and true or false
	end


if ( session:ready() ) then
	ask_queue_caller_id_number = session:getVariable("ask_queue_caller_id_number") or 'false';
        queue_caller_id_number = session:getVariable("queue_caller_id_number");
        queue_original_id_number = session:getVariable("queue_original_id_number");
	queue_name = session:getVariable("cc_queue");
	queue_default_delay = session:getVariable("queue_default_delay") or 300;

	origination_caller_id_name = session:getVariable("origination_caller_id_name") or '15149991234';
	origination_caller_id_number = session:getVariable("origination_caller_id_number") or '15149991234';
	domain_name = session:getVariable("domain_name");
	domain_uuid = session:getVariable("domain_uuid") or '';
	accountcode = session:getVariable("accountcode") or '';

        default_language = session:getVariable("default_language");
        default_dialect = session:getVariable("default_dialect");
        default_voice = session:getVariable("default_voice");
        if (not default_language) then default_language = 'en'; end
        if (not default_dialect) then default_dialect = 'us'; end
        if (not default_voice) then default_voice = 'callie'; end
        sound_dir = session:getVariable("sounds_dir") or '/usr/share/freeswitch/sounds/';

	--detect time, by default 5 minutes
	require "resources.functions.database_handle";
	dbh = database_handle('system');

	if (skip_cdr_analysis) then
		delay = 300;
	else
		if (database["type"] == "mysql") then
		else
-- select json -> 'variables' ->>  'cc_queue' as cc_queue, AVG(CAST(json -> 'variables' ->>  'cc_queue_joined_epoch' AS INTEGER)) as cc_queue_joined_epoch, AVG(CAST(json -> 'variables' ->>  'cc_queue_answered_epoch' AS INTEGER)) as cc_queue_answered_epoch from v_xml_cdr where CAST(json -> 'variables' ->>  'cc_queue' AS TEXT) LIKE 'Savecom_Support%40savecom.savetel.net' AND cc_queue_joined_epoch::integer > 0 GROUP BY json -> 'variables' ->>  'cc_queue';
			sql = [[SELECT json -> 'variables' ->>  'cc_queue' as cc_queue, AVG(CAST(json -> 'variables' ->>  'cc_queue_joined_epoch' AS INTEGER)) as cc_queue_joined_epoch, AVG(CAST(json -> 'variables' ->>  'cc_queue_answered_epoch' AS INTEGER)) as cc_queue_answered_epoch from v_xml_cdr where CAST(json -> 'variables' ->>  'cc_queue' AS TEXT) LIKE 'Savecom_Support%40savecom.savetel.net' AND cc_queue_joined_epoch::integer > 0 GROUP BY json -> 'variables' ->>  'cc_queue']];
		end
	 
		if (debug["sql"]) then
			freeswitch.consoleLog("notice", "[queue_callback] "..sql.."\n");
		end

		status = dbh:query(sql, function(row)
			cc_queue_joined_epoch = row.cc_queue_joined_epoch;
			cc_queue_answered_epoch = row.cc_queue_answered_epoch;
		end);

		if (cc_queue_joined_epoch == nil) then
			freeswitch.consoleLog("notice", "[queue_callback] default delay set to 300\n");
			delay = queue_default_delay;
		else
			delay = cc_queue_answered_epoch - cc_queue_joined_epoch;
			freeswitch.consoleLog("notice", "[queue_callback] delay set to "..delay.."\n");
		end
	end


	if (ask_queue_caller_id_number == 'true') then
                min_digits = 8;
                max_digits = 20;
                max_tries = 3;
                digit_timeout = 5000;
                session:sleep(1000);
                                
		temp_number = session:playAndGetDigits(min_digits, max_digits, max_tries, digit_timeout, "#", sounds_dir.."/"..default_language.."/"..default_dialect.."/"..default_voice.."/ivr/ivr-please_enter_the_number_where_we_can_reach_you.wav", "", "\\d+");
		if (temp_number ~= nil and temp_number ~= '') then
			queue_caller_id_number = temp_number;
			freeswitch.consoleLog("NOTICE", "[queue_callback]: assigned new call back number "..queue_caller_id_number.."\n");
			session:streamFile(sounds_dir.."/"..default_language.."/"..default_dialect.."/"..default_voice.."/ivr/ivr-we_will_return_your_call_at_this_number.wav");
		end

--./ivr/48000/ivr-it_appears_that_your_phone_number_is.wav
--./ivr/48000/ivr-please.wav
--./ivr/48000/ivr-enter_destination_telephone_number.wav
--./ivr/48000/ivr-please_enter_the_number_where_we_can_reach_you.wav
--./ivr/48000/ivr-we_will_return_your_call_at_this_number.wav
--./ivr/48000/ivr-would_you_like_to_receive_a_call_at_this_number.wav
--digits = session:playAndGetDigits(min_digits, max_digits, max_tries, digit_timeout, "#", "phrase:voicemail_enter_pass:#", "", "\\d+");
        end


	api = freeswitch.API();
	-- originate {origination_uuid=6f9e24ee-0576-4580-9590-676aaa010ec3,click_to_call=true,origination_caller_id_name='OKayTest',origination_caller_id_number=16138007370,instant_ringback=true,ringback=\'%(2000,4000,440.0,480.0)\',presence_id=00991@pbx.to-call.me,call_direction=outbound}loopback/00991/savecom.savetel.net &transfer('16138535080 XML savecom.savetel.net')

	cmd_string = "sched_api +"..delay.." queue-callback-"..queue_name.." originate {origination_uuid=6f9e24ee-0576-4580-9590-676aaa010ec3,click_to_call=true,origination_caller_id_name='"..origination_caller_id_name.."',origination_caller_id_number="..origination_caller_id_number..",instant_ringback=true,ringback=\'%(2000,4000,440.0,480.0)\',presence_id="..queue_caller_id_number.."@"..domain_name..",domain_uuid="..domain_uuid..",call_direction=outbound,accountcode="..accountcode..",domain_name="..domain_name.."}loopback/"..queue_original_id_number.."/"..domain_name.." &transfer('"..queue_caller_id_number.." XML "..domain_name.."')";
	freeswitch.consoleLog("NOTICE", "[queue_callback]: "..cmd_string.."\n");
	reply = api:executeString(cmd_string);
	session:hangup();	
end
