--	callcentercenter/index.lua
--	Part of FusionPBX
--	Copyright (C) 2013 - 2021 Mark J Crane <markjcrane@fusionpbx.com>
--	All rights reserved.
--
--	Redistribution and use in source and binary forms, with or without
--	modification, are permitted provided that the following conditions are met:
--
--	1. Redistributions of source code must retain the above copyright notice,
--	this list of conditions and the following disclaimer.
--
--	2. Redistributions in binary form must reproduce the above copyright
--	notice, this list of conditions and the following disclaimer in the
--	documentation and/or other materials provided with the distribution.
--
--	THIS SOFTWARE IS PROVIDED AS ''IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
--	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
--	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
--	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
--	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
--	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
--	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
--	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
--	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
--	POSSIBILITY OF SUCH DAMAGE.
--
--	Contributor(s):
--	Mark J Crane <markjcrane@fusionpbx.com>
--	Luis Daniel Lucio Quiroz <dlucio@okay.com.mx>

--set variables
	flags = "";
	max_tries = 3;
	digit_timeout = 5000;

--debug
	debug["sql"] = false;

--includes
	require "resources.functions.config";
	local Database = require "resources.functions.database";
	dbh = Database.new('system');
	
--include json library
	local json
	if (debug["sql"]) then
		json = require "resources.functions.lunajson"
	end

--prepare the api object
	api = freeswitch.API();

--general functions
	require "resources.functions.base64";
	require "resources.functions.trim";
	require "resources.functions.file_exists";
	require "resources.functions.explode";
	require "resources.functions.format_seconds";
	require "resources.functions.mkdir";

--get the session variables
	uuid = session:getVariable("uuid");
  callcenter_queue = argv[2];

--answer the call
	session:answer();

--get record_ext
	record_ext = session:getVariable("record_ext");
	if (not record_ext) then
			record_ext = "wav";
	end



--make sure the session is ready
	if (session:ready()) then

		--answer the call
			session:preAnswer();

		--set the session sleep
			session:sleep(1000);

		--get session variables
			sounds_dir = session:getVariable("sounds_dir");
			hold_music = session:getVariable("hold_music");
			domain_name = session:getVariable("domain_name");
			pin_number = session:getVariable("pin_number");
			domain_uuid = session:getVariable("domain_uuid");
			destination_number = session:getVariable("destination_number");
			caller_id_number = session:getVariable("caller_id_number");
			--freeswitch.consoleLog("notice", "[call center] destination_number: " .. destination_number .. "\n");
			--freeswitch.consoleLog("notice", "[call center] caller_id_number: " .. caller_id_number .. "\n");

		--add the domain name to the recordings directory
			recordings_dir = recordings_dir .. "/"..domain_name;

		--set the sounds path for the language, dialect and voice
			default_language = session:getVariable("default_language");
			default_dialect = session:getVariable("default_dialect");
			default_voice = session:getVariable("default_voice");
			if (not default_language) then default_language = 'en'; end
			if (not default_dialect) then default_dialect = 'us'; end
			if (not default_voice) then default_voice = 'callie'; end

		--get the domain_uuid
			if (domain_name ~= nil and domain_uuid == nil) then
				local sql = "SELECT domain_uuid FROM v_domains ";
				sql = sql .. "WHERE domain_name = :domain_name ";
				local params = {domain_name = domain_name};
				if (debug["sql"]) then
					freeswitch.consoleLog("notice", "[call center] SQL: " .. sql .. "; params:" .. json.encode(params) .. "\n");
				end
				dbh:query(sql, params, function(rows)
					domain_uuid = string.lower(rows["domain_uuid"]);
				end);
			end
      dbh:release();

		--connect to the switch database
			local dbh_switch = Database.new('switch')

		--check if someone has already joined the callcenter
			local_hostname = trim(api:execute("switchname", ""));
			freeswitch.consoleLog("notice", "[call center] local_hostname is " .. local_hostname .. "\n");
			sql = "SELECT hostname FROM channels WHERE application = 'callcenter' "
				.. "AND dest = :destination_number AND cid_num <> :caller_id_number LIMIT 1";
			params = {destination_number = destination_number, caller_id_number = caller_id_number};
			if (debug["sql"]) then
				freeswitch.consoleLog("notice", "[call center] SQL: " .. sql .. "; params:" .. json.encode(params) .. "\n");
			end
			dbh_switch:query(sql, params, function(rows)
				callcenter_hostname = rows["hostname"];
			end);

		--close the database connection
			dbh_switch:release();

		--if callcenter hosntame exist, then we bridge there
			if (callcenter_hostname ~= nil) then
				freeswitch.consoleLog("notice", "[call center] callcenter_hostname is " .. callcenter_hostname .. "\n");
				if (callcenter_hostname ~= local_hostname) then
					session:execute("bridge","sofia/internal/" .. destination_number .. "@" .. domain_name .. ";fs_path=sip:" .. callcenter_hostname);
				end
			end

		--call not bridged, so we answer
			session:answer();

		--send the call to the callcenter
			freeswitch.consoleLog("INFO","[call center] callcenter " .. callcenter_queue .. "\n");
			session:execute("callcenter", callcenter_queue);
	end
