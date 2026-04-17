--
--	FusionPBX
--	Version: MPL 1.1
--
--	The contents of this file are subject to the Mozilla Public License Version
--	1.1 (the "License"); you may not use this file except in compliance with
--	the License. You may obtain a copy of the License at
--	http://www.mozilla.org/MPL/
--
--	Software distributed under the License is distributed on an "AS IS" basis,
--	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
--	for the specific language governing rights and limitations under the
--	License.
--
--	The Original Code is FusionPBX
--
--	The Initial Developer of the Original Code is
--	Mark J Crane <markjcrane@fusionpbx.com>
--	Copyright (C) 2015 - 2018
--	the Initial Developer. All Rights Reserved.

--set the debug options
	debug["params"] = false;
	debug["info"] = false;
	debug["sql"] = false;

--general functions
	require "resources.functions.config";
	require "resources.functions.explode";
	require "resources.functions.trim";
	require "resources.functions.base64";
	require "resources.functions.file_exists";

--load libraries
	require 'resources.functions.send_mail'

--create the api object
	api = freeswitch.API();

-- show all channel variables
	serialized = env:serialize()
	freeswitch.consoleLog("INFO","[security]\n" .. serialized .. "\n")

-- set channel variables to lua variables
	originate_disposition = env:getHeader("originate_disposition");
	originate_causes = env:getHeader("originate_causes");
	uuid = env:getHeader("uuid");
	domain_uuid = env:getHeader("domain_uuid");
	domain_name = env:getHeader("domain_name");
	sip_to_user = env:getHeader("sip_to_user");
	dialed_user = env:getHeader("dialed_user");
	missed_call_app = env:getHeader("missed_call_app");
	missed_call_data = env:getHeader("missed_call_data");
	call_direction = env:getHeader("call_direction");

-- get the Caller ID
	caller_id_name = env:getHeader("caller_id_name");
	caller_id_number = env:getHeader("caller_id_number");
	if (caller_id_name == nil) then
		caller_id_name = env:getHeader("Caller-Caller-ID-Name");
	end
	if (caller_id_number == nil) then
		caller_id_number = env:getHeader("Caller-Caller-ID-Number");
	end
	if (call_direction == "local") then
		caller_id_name = env:getHeader("effective_caller_id_name");
	end
