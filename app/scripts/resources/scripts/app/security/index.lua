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
	--serialized = env:serialize()
	--freeswitch.consoleLog("INFO","[security]\n" .. serialized .. "\n")

-- set channel variables to lua variables
--handle originate_disposition
    if (session ~= nil and session:ready()) then
        uuid = session:getVariable("uuid");
        domain_uuid = session:getVariable("domain_uuid");
        domain_name = session:getVariable("domain_name");
        context = session:getVariable("context");
        sip_network_ip = session:getVariable("sip_network_ip");
        sip_received_ip = session:getVariable("sip_received_ip");

        sip_ip = sip_received_ip or sip_network_ip;
        if (sip_ip ~= nil) then
            freeswitch.consoleLog("INFO","[security] ip:" .. ip .. "\n");
        end
    end
