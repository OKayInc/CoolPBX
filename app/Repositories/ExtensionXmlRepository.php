<?php

namespace App\Repositories;

use App\Models\Extension;
use App\Models\Voicemail;
use App\Facades\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;

class ExtensionXmlRepository
{
    protected string $extensionsDir;
    
    public function __construct()
    {
        $this->extensionsDir = config('freeswitch.extensions_dir', '/usr/local/freeswitch/conf/directory');
    }
    
    public function synchronizeAll(string $domainUuid, string $domainName): bool
    {
        try {
            if (!$this->isDirectoryWritable()) {
                Log::warning('Extensions directory is not writable: ' . $this->extensionsDir);
                return false;
            }
            
            $this->cleanOldXmlFiles($domainName);
            
            $extensions = $this->getExtensionsWithVoicemail($domainUuid);
            
            $callGroups = $this->generateExtensionXmlFiles($extensions, $domainName);
            
            $this->generateDomainGroupXml($domainName, $callGroups);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error synchronizing extension XML: ' . $e->getMessage());
            return false;
        }
    }

    protected function cleanOldXmlFiles(string $domainName): void
    {
        $userContext = $this->sanitizeUserContext($domainName);
        $directory = $this->extensionsDir . '/' . $userContext;
        
        if (File::exists($directory)) {
            $files = glob($directory . '/v_*.xml');
            foreach ($files as $file) {
                File::delete($file);
            }
        }
    }
    

    protected function getExtensionsWithVoicemail(string $domainUuid): Collection
    {
        return Extension::where('extensions.domain_uuid', $domainUuid)
            ->where('extensions.enabled', '!=', 'false')
            ->leftJoin('v_voicemails', function($join) {
                $join->on('extensions.domain_uuid', '=', 'v_voicemails.domain_uuid')
                     ->on(DB::raw("COALESCE(NULLIF(extensions.number_alias,''), extensions.extension)"), 
                          '=', DB::raw("CAST(v_voicemails.voicemail_id AS VARCHAR)"));
            })
            ->select('extensions.*', 'v_voicemails.*', 'extensions.extension_uuid as extension_uuid')
            ->orderBy('extensions.call_group', 'asc')
            ->get();
    }
    

    protected function generateExtensionXmlFiles($extensions, string $domainName): array
    {
        $callGroups = [];
        
        foreach ($extensions as $extension) {
            if (!empty($extension->call_group)) {
                $groups = $this->parseCallGroups($extension->call_group);
                foreach ($groups as $group) {
                    if (!isset($callGroups[$group])) {
                        $callGroups[$group] = [];
                    }
                    $callGroups[$group][] = $extension->extension;
                }
            }
            
            $xml = $this->buildExtensionXml($extension, $domainName);
            
            $this->writeExtensionXmlFile($extension, $xml);
        }
        
        return $callGroups;
    }

    protected function buildExtensionXml($extension, string $domainName): string
    {
        $userContext = $extension->user_context ?? $domainName;
        $callTimeout = $extension->call_timeout ?? 30;
        
        $dialString = $extension->dial_string;
        if (empty($dialString)) {
            $dialString = Setting::getSetting('domain', 'dial_string', 'text') ?? 
                "{sip_invite_domain=\${domain_name},leg_timeout={$callTimeout},presence_id=\${dialed_user}@\${dialed_domain}}\${sofia_contact(\${dialed_user}@\${dialed_domain})}";
        }
        
        $xml = "<include>\n";
        
        $xml .= "  <user id=\"{$extension->extension}\"";
        if (!empty($extension->cidr)) {
            $xml .= " cidr=\"{$extension->cidr}\"";
        }
        if (!empty($extension->number_alias)) {
            $xml .= " number-alias=\"{$extension->number_alias}\"";
        }
        $xml .= ">\n";
        
        $xml .= "    <params>\n";
        $xml .= "      <param name=\"password\" value=\"{$extension->password}\"/>\n";
        $xml .= "      <param name=\"reverse-auth-user\" value=\"{$extension->extension}\"/>\n";
        $xml .= "      <param name=\"reverse-auth-pass\" value=\"{$extension->password}\"/>\n";
        
        if (!empty($extension->voicemail_password)) {
            $xml .= "      <param name=\"vm-password\" value=\"{$extension->voicemail_password}\"/>\n";
        }
        $vmEnabled = $extension->voicemail_enabled ?? 'true';
        $xml .= "      <param name=\"vm-enabled\" value=\"{$vmEnabled}\"/>\n";
        
        if (!empty($extension->voicemail_mail_to)) {
            $xml .= "      <param name=\"vm-email-all-messages\" value=\"true\"/>\n";
            $vmAttach = ($extension->voicemail_file == 'attach') ? 'true' : 'false';
            $xml .= "      <param name=\"vm-attach-file\" value=\"{$vmAttach}\"/>\n";
            $vmKeepLocal = $extension->voicemail_local_after_email ?? 'true';
            $xml .= "      <param name=\"vm-keep-local-after-email\" value=\"{$vmKeepLocal}\"/>\n";
            $xml .= "      <param name=\"vm-mailto\" value=\"{$extension->voicemail_mail_to}\"/>\n";
        }
        
        if (!empty($extension->mwi_account)) {
            $xml .= "      <param name=\"MWI-Account\" value=\"{$extension->mwi_account}\"/>\n";
        }
        if (!empty($extension->auth_acl)) {
            $xml .= "      <param name=\"auth-acl\" value=\"{$extension->auth_acl}\"/>\n";
        }
        
        $xml .= "      <param name=\"dial-string\" value=\"{$dialString}\"/>\n";
        $xml .= "    </params>\n";
        
        $xml .= "    <variables>\n";
        $xml .= "      <variable name=\"domain_name\" value=\"{$domainName}\"/>\n";
        $xml .= "      <variable name=\"domain_uuid\" value=\"{$extension->domain_uuid}\"/>\n";
        $xml .= "      <variable name=\"extension_uuid\" value=\"{$extension->extension_uuid}\"/>\n";
        
        $this->addExtensionVariables($xml, $extension);
        
        $xml .= "    </variables>\n";
        $xml .= "  </user>\n";
        $xml .= "</include>\n";
        
        return $xml;
    }

    protected function addExtensionVariables(&$xml, $extension): void
    {
        $variables = [
            'call_group', 'user_record', 'hold_music', 'toll_allow', 'call_timeout',
            'accountcode', 'user_context', 'effective_caller_id_name', 'effective_caller_id_number',
            'outbound_caller_id_name', 'outbound_caller_id_number', 'emergency_caller_id_name',
            'emergency_caller_id_number', 'directory_full_name', 'directory_visible',
            'limit_destination', 'sip_force_contact', 'sip_force_expires', 'nibble_account',
            'absolute_codec_string', 'forward_all_enabled', 'forward_all_destination',
            'forward_busy_enabled', 'forward_busy_destination', 'forward_no_answer_enabled',
            'forward_no_answer_destination', 'forward_user_not_registered_enabled',
            'forward_user_not_registered_destination', 'do_not_disturb'
        ];
        
        foreach ($variables as $var) {
            if (!empty($extension->$var)) {
                $xml .= "      <variable name=\"{$var}\" value=\"{$extension->$var}\"/>\n";
            }
        }
        
        if (empty($extension->limit_max)) {
            $xml .= "      <variable name=\"limit_max\" value=\"5\"/>\n";
        } else {
            $xml .= "      <variable name=\"limit_max\" value=\"{$extension->limit_max}\"/>\n";
        }
        
        switch ($extension->sip_bypass_media) {
            case 'bypass-media':
                $xml .= "      <variable name=\"bypass_media\" value=\"true\"/>\n";
                break;
            case 'bypass-media-after-bridge':
                $xml .= "      <variable name=\"bypass_media_after_bridge\" value=\"true\"/>\n";
                break;
            case 'proxy-media':
                $xml .= "      <variable name=\"proxy_media\" value=\"true\"/>\n";
                break;
        }
    }
    

    protected function writeExtensionXmlFile($extension, string $xml): void
    {
        $userContext = $this->sanitizeUserContext($extension->user_context ?? $extension->domain->domain_name);
        $extensionName = $this->sanitizeFileName($extension->extension);
        
        $directory = $this->extensionsDir . '/' . $userContext;
        
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0770, true);
        }
        
        $filepath = $directory . '/v_' . $extensionName . '.xml';
        File::put($filepath, $xml);
    }
    

    protected function generateDomainGroupXml(string $domainName, array $callGroups): void
    {
        $userContext = $this->sanitizeUserContext($domainName);
        
        $xml = $this->buildDomainGroupXmlHeader();
        $xml .= "<include>\n";
        
        if ($userContext == "default") {
            $xml .= "	<domain name=\"\$\${domain}\">\n";
        } else {
            $xml .= "	<domain name=\"{$userContext}\">\n";
        }
        
        $xml .= "		<params>\n";
        $xml .= "		</params>\n\n";
        $xml .= "		<variables>\n";
        $xml .= "			<variable name=\"record_stereo\" value=\"true\"/>\n";
        $xml .= "			<variable name=\"default_gateway\" value=\"\$\${default_provider}\"/>\n";
        $xml .= "			<variable name=\"default_areacode\" value=\"\$\${default_areacode}\"/>\n";
        $xml .= "			<variable name=\"transfer_fallback_extension\" value=\"operator\"/>\n";
        $xml .= "			<variable name=\"export_vars\" value=\"domain_name\"/>\n";
        $xml .= "		</variables>\n\n";
        
        $xml .= "		<groups>\n";
        $xml .= "			<group name=\"{$userContext}\">\n";
        $xml .= "			<users>\n";
        $xml .= "				<X-PRE-PROCESS cmd=\"include\" data=\"{$userContext}/*.xml\"/>\n";
        $xml .= "			</users>\n";
        $xml .= "			</group>\n\n";
        
        foreach ($callGroups as $groupName => $extensions) {
            $xml .= "			<group name=\"{$groupName}\">\n";
            $xml .= "				<users>\n";
            foreach ($extensions as $ext) {
                $xml .= "					<user id=\"{$ext}\" type=\"pointer\"/>\n";
            }
            $xml .= "				</users>\n";
            $xml .= "			</group>\n\n";
        }
        
        $xml .= "		</groups>\n\n";
        $xml .= "	</domain>\n";
        $xml .= "</include>";
        
        $filepath = $this->extensionsDir . '/' . $userContext . '.xml';
        File::put($filepath, $xml);
    }

    protected function buildDomainGroupXmlHeader(): string
    {
        return "<!--\n"
            . "	NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE\n\n"
            . "	FreeSWITCH works off the concept of users and domains just like email.\n"
            . "	You have users that are in domains for example 1000@domain.com.\n\n"
            . "	When freeswitch gets a register packet it looks for the user in the directory\n"
            . "	based on the from or to domain in the packet depending on how your sofia profile\n"
            . "	is configured.  Out of the box the default domain will be the IP address of the\n"
            . "	machine running FreeSWITCH.  This IP can be found by typing \"sofia status\" at the\n"
            . "	CLI.  You will register your phones to the IP and not the hostname by default.\n"
            . "	If you wish to register using the domain please open vars.xml in the root conf\n"
            . "	directory and set the default domain to the hostname you desire.  Then you would\n"
            . "	use the domain name in the client instead of the IP address to register\n"
            . "	with FreeSWITCH.\n\n"
            . "	NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE NOTICE\n"
            . "-->\n\n";
    }
    
    protected function parseCallGroups(string $callGroup): array
    {
        $callGroup = str_replace(';', ',', $callGroup);
        $groups = explode(',', $callGroup);
        return array_map('trim', array_filter($groups));
    }
    
    protected function sanitizeUserContext(string $context): string
    {
        $context = str_replace(' ', '_', $context);
        return preg_replace('/[\*\:\\/\<\>\|\'\"\?]/', '', $context);
    }
    
    protected function sanitizeFileName(string $filename): string
    {
        $filename = str_replace(' ', '_', $filename);
        return preg_replace('/[\*\:\\/\<\>\|\'\"\?]/', '', $filename);
    }
    
    protected function isDirectoryWritable(): bool
    {
        return !empty($this->extensionsDir) && is_writable($this->extensionsDir);
    }
}