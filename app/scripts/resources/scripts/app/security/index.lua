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
--	This Module author
--	Luis Daniel Lucio Quiroz <dlucio@okay.com.mx>

--set the debug options
	debug["params"] = false;
	debug["info"] = false;
	debug["sql"] = false;

--create the api object
	api = freeswitch.API();

-- set channel variables to lua variables
--handle originate_disposition
	if (session ~= nil and session:ready()) then
		uuid = session:getVariable("uuid");
		domain_uuid = session:getVariable("domain_uuid");
		domain_name = session:getVariable("domain_name");
		context = session:getVariable("context");
		sip_network_ip = session:getVariable("sip_network_ip");
		sip_received_ip = session:getVariable("sip_received_ip");
		sip_auth_username = session:getVariable("sip_auth_username");
		sip_auth_realm = session:getVariable("sip_auth_realm");
		sofia_profile_name = session:getVariable("sofia_profile_name");

		if (sip_auth_username ~= nil and sip_auth_realm ~= nil and sofia_profile_name ~= nil) then
			sip_auth = sip_auth_username .. "@" .. sip_auth_realm;
			-- if call is not authenticated we do not verify
			freeswitch.consoleLog("INFO","[security] sip_auth_username:" .. sip_auth_username .. "\n");
			freeswitch.consoleLog("INFO","[security] sip_auth_realm:" .. sip_auth_realm .. "\n");
			sip_ip = sip_received_ip or sip_network_ip;
			if (sip_ip ~= nil) then
				freeswitch.consoleLog("INFO","[security] sip_ip:" .. sip_ip .. "\n");
				contact = api:executeString("sofia_contact " .. sip_auth);
				if contact == "error/user_not_registered" then
					session:setVariable("proto_specific_hangup_cause", "sip:603");
					session:hangup();
				else
					if string.find(contact, sip_ip) then
						freeswitch.consoleLog("info", "[security] IP MATCH: User " .. sip_auth .. " is registered from " .. sip_ip .. "\n");
					else
						freeswitch.consoleLog("info", "[security] IP MATCH: User " .. sip_auth .. " is NOT registered from " .. sip_ip .. ". Hanging up!\n");
						session:setVariable("proto_specific_hangup_cause", "sip:603");
						session:hangup();
					end
				end
			end
		end
	end
