-- Config Dialplan 1  Order 81 (before the outbound routes) Continue true
-- 	<condition field="${db exists/caller_id/${extension_uuid}}" expression="true"/>
--	<condition field="${user_exists}" expression="false">
--		<action application="log" data="Set the Caller ID for ${extension_uuid}"/>
--		<action application="set" data="outbound_caller_id_number=${db select/caller_id/${extension_uuid}}" inline="true"/>
--		<action application="db" data="delete/caller_id/${extension_uuid}"/>
-- 	</condition>

-- Config Dialplan 2 Order 200ish  
--	<condition field="destination_number" expression="^\*22553743$">
--		<action application="set" data="caller_id_sound_current=phrase:3bbba16a-49af-43f6-8925-1b8d2d9115cd"/>
--		<action application="set" data="caller_id_sound_prompt=phrase:f3931718-1835-47b9-b808-4acc8cdf534e"/>
--		<action application="set" data="caller_id_sound_goodbye=phrase:52cf56cc-dfaa-4e8e-aecc-83810a35df57"/>
--		<action application="lua" data="app.lua caller_id"/>
--	</condition>

	 if (session:ready()) then
--get the variables
		extension_uuid = session:getVariable("extension_uuid");
		outbound_caller_id_number = session:getVariable("outbound_caller_id_number");
		caller_id_sound_current = session:getVariable("caller_id_sound_current");
		caller_id_sound_prompt = session:getVariable("caller_id_sound_prompt");
		caller_id_sound_goodbye = session:getVariable("caller_id_sound_goodbye");

--prepare the api object
		api = freeswitch.API();

--define the trim function
		require "resources.functions.trim";

--set the cache key
		realm = 'caller_id';
		key = extension_uuid;
		argument = 'exists/'..realm..'/'..key;

		if (	(api:execute('db', argument) == 'false') and
			(caller_id_sound_current ~= nil) and
			(caller_id_sound_prompt ~= nil)
		) then
			if ((outbound_caller_id_number ~= nil) and (string.len(outbound_caller_id_number) > 0)) then
				freeswitch.consoleLog("notice", "[caller_id] Current caller ID for " .. extension_uuid .. ' is ' .. outbound_caller_id_number .. "\n");
				session:execute('playback',caller_id_sound_current);
				session:say(outbound_caller_id_number, 'en', "name_spelled", "iterated");
			end
			min_digits = 10;
			max_digits = 15;
			max_tries = 1;
			digit_timeout = 5000;	-- in ms
			tmp_caller_id = session:playAndGetDigits(min_digits, max_digits, max_tries, digit_timeout, "#", caller_id_sound_prompt, "", "\\d+");
			freeswitch.consoleLog("notice", "[caller_id] Temporal caller ID for " .. extension_uuid .. ' is ' .. tmp_caller_id .. "\n");
			api:execute('db','insert/'..realm..'/'..key..'/'..tmp_caller_id);
		else
			freeswitch.consoleLog("notice", "[caller_id] A temporal caller ID number already exists or some basic sounds are missing.\n");
		end
		if (caller_id_sound_goodbye ~= nil) then
			session:execute('playback',caller_id_sound_goodbye);
		end
	end
