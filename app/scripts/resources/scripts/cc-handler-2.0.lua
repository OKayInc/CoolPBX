-- CallCenter Agent Hander 2.0
-- lua.conf.xml
-- <hook event="CHANNEL_CALLSTATE" script="/usr/share/freeswitch/scripts/cc-handler-2.0.lua"/>


require "resources.functions.config";
require "resources.functions.split";
require "resources.functions.trim";

local s = event:serialize("xml")

local name = event:getHeader("Event-Name");
local channel_state = event:getHeader("Channel-State") or '[nil]';
local original_channel_state = event:getHeader("Original-Channel-Call-State") or '[nil]';
local channel_name = event:getHeader("Channel-Name") or '[nil]';
local channel_call_uuid = event:getHeader("Channel-Call-UUID") or '[nil]';


--channel_name sofia/external/18007692512  sofia/internal/daniel.lucio@pbx.to-call.me
local v  = split(channel_name,'/',true);
--freeswitch.consoleLog("NOTICE", "[handler] type: " .. v[1]);
--freeswitch.consoleLog("NOTICE", "[handler] profile: " .. v[2]);
freeswitch.consoleLog("NOTICE", "[handler] endpoint: " .. v[3]);
local endpoint = v[3];

--freeswitch.consoleLog("NOTICE", "[handler] event-name "  .. name);
freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] original-channel-state " .. original_channel_state);
freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] channel-state " .. channel_state);
--freeswitch.consoleLog("NOTICE", "[handler] channel-name " .. channel_name);
freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] channel-call-uuid " .. channel_call_uuid);
--freeswitch.consoleLog("NOTICE", "Serial!\n" .. s);

-- Check if it is an agent related call
-- name|instance_id|uuid|type|contact|status|state|max_no_answer|wrap_up_time|reject_delay_time|busy_delay_time|no_answer_delay_time|last_bridge_start|last_bridge_end|last_offered_call|last_status_change|no_answer_count|calls_answered|talk_time|ready_time|external_calls_count
-- 5134d4f3-867a-40a7-b616-354fd53c785e|single_box||callback|{call_timeout=15}user/daniel.lucio@pbx.to-call.me|Available|Waiting|0|10|90|90|30|1602610417|1602610426|1602610409|1576073492|0|4|34|1576091223|0


api = freeswitch.API();

local cmd = "callcenter_config agent list";
--freeswitch.consoleLog("notice", "[handler] "..cmd.."\n");
agent_list = trim(api:executeString(cmd));
--freeswitch.consoleLog("NOTICE", "[handler] agent_list: " .. agent_list);
local lines = split (agent_list, "\n", true);
if lines == nil then return end;
local n = #lines or 0;
if n == 0 then return end;
--freeswitch.consoleLog("NOTICE", "[handler] agent_list n: " .. n);
local i = 1;
local is_agent_leg = false;
local agent_contact;
for i,v in ipairs(lines) do 
	if i ~= 1 and i ~= n then
--		freeswitch.consoleLog("NOTICE", "[handler] " .. i .. ": " .. v);
		local agent = split(v, '|', true);
		agent_contact = agent[5];
		agent_status = agent[6];
		agent_state = agent[7];
		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] contact: " .. agent[5]);

		--Try first direct match before trying sofia_contact
		local cmd_regex = "m:~"..agent_contact.."~/(user/[\\d\\w\\.\\-_]+@[\\d\\w\\.\\-_]+)/~$1";
		local agent_contact_only = trim(api:execute("regex", cmd_regex));
		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] " .. cmd_regex .. " = " .. agent_contact_only);
		local w  = split(agent_contact_only,'/',true);
		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] extension: " .. w[2]);
		local extension = w[2];
		if string.match(endpoint, extension) then
                	freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] Found directly!");
			is_agent_leg = true;
		end

		if is_agent_leg == false then
			local cmd_contact = "sofia_contact " .. agent[5];
			local contact_list = trim(api:executeString(cmd_contact));
			local lines2 = split (contact_list, ",", true);
			local n2 = #lines2;
			freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] contact_list n: " .. n2);
			for i2,v2 in ipairs(lines2) do
				freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] " .. i2 .. ": " .. v2);
				if string.match(v2, endpoint) then
					freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] Found!");
					is_agent_leg = true;
					break;
				else
					freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] NOT Found!");
				end
			end
		end
	end
end


if is_agent_leg then
	freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] This belongs to " .. endpoint .. " whish is the " .. agent_contact .. " agent");
	freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] Current status agent is " .. agent_status .. " while doing: " .. agent_state);

	if agent_state == "Receiving" or agent_state == "In a queue call" then
		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] This is a CC call, nothing to do");
	else
		local cmd_regex = "m:~"..agent_contact.."~/(user/[\\d\\w\\.\\-_]+@[\\d\\w\\.\\-_]+)/~$1";
		local agent_contact_only = trim(api:execute("regex", cmd_regex));
		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] " .. cmd_regex .. " = " .. agent_contact_only);

		local json;
		json = require "resources.functions.lunajson";
--		session = freeswitch.Session(channel_call_uuid);
--		domain_uuid = session:getVariable("domain_uuid");       
--		local params = {domain_uuid = domain_uuid, agent_contact_only = agent_contact_only}
--		local sql = "SELECT * FROM v_call_center_agents WHERE domain_uuid = :domain_uuid AND agent_contact = :agent_contact_only ";
		local params = {agent_contact_only = agent_contact_only}
		local sql = "SELECT * FROM v_call_center_agents WHERE agent_contact = :agent_contact_only ";
		local Database = require "resources.functions.database";
		local dbh = Database.new('system');
		--freeswitch.consoleLog("notice", "[handler] SQL: " .. sql .. "; params:" .. json.encode(params) .. "\n");
		dbh:query(sql, params, function(row)
			agent_uuid = row.call_center_agent_uuid;
			agent_name = row.agent_name;
			agent_id = row.agent_id;
		end);

		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] agent_uuid: " .. agent_uuid);
		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] This is NOT a CC call");

		local memcache_ttl = 604800;
		local key = "cc:handler:agent:" .. agent_uuid .. ":endpoint:" .. endpoint;
		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] original-channel-state " .. original_channel_state);
		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] channel-state " .. channel_state);
		freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] key " .. key);


		if (original_channel_state == "RINGING" or original_channel_state == "EARLY") and (channel_state == "CS_CONSUME_MEDIA" or channel_state == "CS_EXECUTE")  and (agent_status == "Available" or agent_status == "Available (On Demand)") then
			result = trim(api:execute("memcache", "set " .. key .. " '" .. agent_status .. "' " .. memcache_ttl));
			freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] Logging out agent " .. agent_name .. "(" .. agent_id .. ")");
			freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] " .. agent_status .. " => On Break status pushed into Memcached");
			local cmd = "callcenter_config agent set status "..agent_uuid.." 'On Break'";
			result = api:executeString(cmd);
		end
		if original_channel_state == "ACTIVE" and channel_state == "CS_HANGUP" and agent_status == "On Break" then
			local last_status = trim(api:execute("memcache", "get " .. key)) or "Available";
			if (last_status == "-ERR NOT FOUND") or (last_status  == "-ERR CONNECTION FAILURE") then
				freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] Falling back to Available as last status pulled from Memcached (NOT FOUND or CONNECTION FAILURE)");
				last_status = "Available";
			else
				freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] " .. last_status .. " as last status pulled from Memcached");				
			end

			freeswitch.consoleLog("NOTICE", "[handler "..endpoint.."] Logging in agent " .. agent_name .. "(" .. agent_id .. ")");
			local cmd = "callcenter_config agent set status " .. agent_uuid .. " '" .. last_status .. "'";
			result = api:executeString(cmd);
		end
		
	end
end
