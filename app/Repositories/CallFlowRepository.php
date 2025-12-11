<?php

namespace App\Repositories;

use App\Facades\FreeSwitch;
use App\Models\CallFlow;
use App\Models\Dialplan;
use App\Models\Domain;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CallFlowRepository
{
    protected CallFlow $callFlow;
    protected Dialplan $dialplan;
    protected User $user;

    public function __construct(
        CallFlow $callFlow,
        Dialplan $dialplan,
        User $user
    ) {
        $this->callFlow = $callFlow;
        $this->dialplan = $dialplan;
        $this->user = $user;
    }

    public function mine()
    {
        return auth()->user()->callFlows->toResourceCollection();
    }

    public function all()
    {
        return $this->callFlow->all();
    }

    public function findByUuid(string $callFlowUuid, bool $withRelations = false): ?CallFlow
    {
        if (App::hasDebugModeEnabled()) {
            Log::debug("public function findByUuid(string $callFlowUuid, bool $withRelations = false)");
        }

        $query = $this->callFlow->where('call_flow_uuid', $callFlowUuid);

        if ($withRelations) {
            $query->with(['domain', 'dialplan']);
        }

        return $query->first();
    }

    public function findByExtension(string $extension, string $domainUuid, ?string $excludeCallFlowUuid = null): ?CallFlow
    {
        $query = $this->callFlow
            ->where('call_flow_extension', $extension)
            ->where('domain_uuid', $domainUuid);

        if ($excludeCallFlowUuid) {
            $query->where('call_flow_uuid', '!=', $excludeCallFlowUuid);
        }

        return $query->first();
    }

    public function findByFeatureCode(string $featureCode, string $domainUuid, ?string $excludeCallFlowUuid = null): ?CallFlow
    {
        $query = $this->callFlow
            ->where('call_flow_feature_code', $featureCode)
            ->where('domain_uuid', $domainUuid);

        if ($excludeCallFlowUuid) {
            $query->where('call_flow_uuid', '!=', $excludeCallFlowUuid);
        }

        return $query->first();
    }

    public function checkDuplicateExtension(string $extension, string $domainUuid, ?string $excludeCallFlowUuid = null): ?string
    {
        $callFlow = $this->findByExtension($extension, $domainUuid, $excludeCallFlowUuid);
        return $callFlow ? $callFlow->call_flow_name : null;
    }

    public function checkDuplicateFeatureCode(string $featureCode, string $domainUuid, ?string $excludeCallFlowUuid = null): ?string
    {
        $callFlow = $this->findByFeatureCode($featureCode, $domainUuid, $excludeCallFlowUuid);
        return $callFlow ? $callFlow->call_flow_name : null;
    }

    public function getTotalCallFlowsCount(string $domainUuid): int
    {
        return $this->callFlow->where('domain_uuid', $domainUuid)->count();
    }

    public function create(array $callFlowData): CallFlow
    {
        $callFlowData['call_flow_uuid'] = $callFlowData['call_flow_uuid'] ?? Str::uuid();
        $callFlowData['dialplan_uuid'] = $callFlowData['dialplan_uuid'] ?? Str::uuid();

        try {
            DB::beginTransaction();

            // Validate duplicates
            if ($this->findByExtension($callFlowData['call_flow_extension'], $callFlowData['domain_uuid'])) {
                throw new Exception("Extension already exists");
            }

            if ($this->findByFeatureCode($callFlowData['call_flow_feature_code'], $callFlowData['domain_uuid'])) {
                throw new Exception("Feature code already exists");
            }

            // Set default context if not provided
            if (empty($callFlowData['call_flow_context'])) {
                $domain = Domain::where('domain_uuid', $callFlowData['domain_uuid'])->first();
                $callFlowData['call_flow_context'] = $domain->domain_name ?? auth()->user()->domain->domain_name;
            }

            // Parse destination data
            $callFlowData = $this->parseDestinations($callFlowData);

            // Apply permissions
            $filteredData = $this->applyCallFlowPermissions($callFlowData);

            // Create call flow
            $callFlow = $this->callFlow->create($filteredData);

            // Create associated dialplan
            $this->createDialplan($callFlow);

            DB::commit();

            // Update FreeSWITCH
            $this->updateSwitch($callFlow);
            $this->sendPresenceEvent($callFlow);
            $this->clearCache($callFlow->call_flow_context);

            return $callFlow;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $callFlowUuid, array $callFlowData): CallFlow
    {
        try {
            DB::beginTransaction();

            $callFlow = $this->findByUuid($callFlowUuid);
            if (!$callFlow) {
                throw new Exception("Call flow not found");
            }

            // Validate duplicates (excluding current)
            if (isset($callFlowData['call_flow_extension'])) {
                if ($this->findByExtension($callFlowData['call_flow_extension'], $callFlow->domain_uuid, $callFlowUuid)) {
                    throw new Exception("Extension already exists");
                }
            }

            if (isset($callFlowData['call_flow_feature_code'])) {
                if ($this->findByFeatureCode($callFlowData['call_flow_feature_code'], $callFlow->domain_uuid, $callFlowUuid)) {
                    throw new Exception("Feature code already exists");
                }
            }

            // Parse destination data
            $callFlowData = $this->parseDestinations($callFlowData);

            // Apply permissions
            $filteredData = $this->applyCallFlowPermissions($callFlowData, $callFlow);

            // Update call flow
            $callFlow->update($filteredData);

            // Update associated dialplan
            $this->updateDialplan($callFlow);

            DB::commit();

            // Update FreeSWITCH
            $this->updateSwitch($callFlow);
            $this->sendPresenceEvent($callFlow);
            $this->clearCache($callFlow->call_flow_context);

            return $callFlow->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $callFlowUuid): void
    {
        try {
            DB::beginTransaction();

            $callFlow = $this->findByUuid($callFlowUuid, true);
            if (!$callFlow) {
                throw new Exception("Call flow not found");
            }

            $context = $callFlow->call_flow_context;

            // Delete associated dialplan
            if ($callFlow->dialplan) {
                $callFlow->dialplan->delete();
            }

            // Delete call flow
            $callFlow->delete();

            DB::commit();

            // Clear cache
            $this->clearCache($context);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getDomains(): Collection
    {
        return Domain::select('domain_uuid', 'domain_name')->get();
    }

    public function getAvailableStatuses(): array
    {
        return [
            'true' => 'Active',
            'false' => 'Inactive'
        ];
    }

    public function setDefaultValues(array &$callFlowData): void
    {
        $defaults = [
            'call_flow_enabled' => 'true',
            'call_flow_status' => 'false',
        ];

        foreach ($defaults as $key => $value) {
            if (empty($callFlowData[$key])) {
                $callFlowData[$key] = $value;
            }
        }
    }

    public function toggleStatus(string $callFlowUuid): CallFlow
    {
        try {
            DB::beginTransaction();

            $callFlow = $this->findByUuid($callFlowUuid);
            if (!$callFlow) {
                throw new Exception("Call flow not found");
            }

            $newStatus = $callFlow->call_flow_status === 'true' ? 'false' : 'true';
            $callFlow->update(['call_flow_status' => $newStatus]);

            DB::commit();

            // Send presence update
            $this->sendPresenceEvent($callFlow);

            return $callFlow->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function parseDestinations(array $callFlowData): array
    {
        // Parse main destination
        if (isset($callFlowData['call_flow_destination'])) {
            $destination = explode(':', $callFlowData['call_flow_destination'], 2);
            $callFlowData['call_flow_app'] = $destination[0] ?? '';
            $callFlowData['call_flow_data'] = $destination[1] ?? '';
            unset($callFlowData['call_flow_destination']);
        }

        // Parse alternate destination
        if (isset($callFlowData['call_flow_alternate_destination'])) {
            $alternateDestination = explode(':', $callFlowData['call_flow_alternate_destination'], 2);
            $callFlowData['call_flow_alternate_app'] = $alternateDestination[0] ?? '';
            $callFlowData['call_flow_alternate_data'] = $alternateDestination[1] ?? '';
            unset($callFlowData['call_flow_alternate_destination']);
        }

        return $callFlowData;
    }

    private function applyCallFlowPermissions(array $callFlowData, ?CallFlow $existingCallFlow = null): array
    {
        $filteredData = [];
        $user = auth()->user();

        $filteredData['domain_uuid'] = $callFlowData['domain_uuid'] ?? ($existingCallFlow->domain_uuid ?? $user->domain_uuid);

        if (is_null($existingCallFlow)) {
            $filteredData['call_flow_uuid'] = $callFlowData['call_flow_uuid'] ?? Str::uuid();
            $filteredData['dialplan_uuid'] = $callFlowData['dialplan_uuid'] ?? Str::uuid();
        }

        if ($user->hasPermission('call_flow_add') || $user->hasPermission('call_flow_edit')) {
            $filteredData['call_flow_name'] = $callFlowData['call_flow_name'] ?? ($existingCallFlow->call_flow_name ?? null);
            $filteredData['call_flow_extension'] = $callFlowData['call_flow_extension'] ?? ($existingCallFlow->call_flow_extension ?? null);
            $filteredData['call_flow_feature_code'] = $callFlowData['call_flow_feature_code'] ?? ($existingCallFlow->call_flow_feature_code ?? null);
            $filteredData['call_flow_status'] = $callFlowData['call_flow_status'] ?? ($existingCallFlow->call_flow_status ?? 'false');
            $filteredData['call_flow_pin_number'] = $callFlowData['call_flow_pin_number'] ?? ($existingCallFlow->call_flow_pin_number ?? null);
            $filteredData['call_flow_label'] = $callFlowData['call_flow_label'] ?? ($existingCallFlow->call_flow_label ?? null);
            $filteredData['call_flow_sound'] = $callFlowData['call_flow_sound'] ?? ($existingCallFlow->call_flow_sound ?? null);
            $filteredData['call_flow_app'] = $callFlowData['call_flow_app'] ?? ($existingCallFlow->call_flow_app ?? null);
            $filteredData['call_flow_data'] = $callFlowData['call_flow_data'] ?? ($existingCallFlow->call_flow_data ?? null);
            $filteredData['call_flow_alternate_label'] = $callFlowData['call_flow_alternate_label'] ?? ($existingCallFlow->call_flow_alternate_label ?? null);
            $filteredData['call_flow_alternate_sound'] = $callFlowData['call_flow_alternate_sound'] ?? ($existingCallFlow->call_flow_alternate_sound ?? null);
            $filteredData['call_flow_alternate_app'] = $callFlowData['call_flow_alternate_app'] ?? ($existingCallFlow->call_flow_alternate_app ?? null);
            $filteredData['call_flow_alternate_data'] = $callFlowData['call_flow_alternate_data'] ?? ($existingCallFlow->call_flow_alternate_data ?? null);
            $filteredData['call_flow_enabled'] = $callFlowData['call_flow_enabled'] ?? ($existingCallFlow->call_flow_enabled ?? 'true');
            $filteredData['call_flow_description'] = $callFlowData['call_flow_description'] ?? ($existingCallFlow->call_flow_description ?? null);
        }

        if ($user->hasPermission('call_flow_context')) {
            $filteredData['call_flow_context'] = $callFlowData['call_flow_context'] ?? ($existingCallFlow->call_flow_context ?? null);
        } else {
            $filteredData['call_flow_context'] = $existingCallFlow->call_flow_context ?? $user->domain->domain_name;
        }

        return array_filter($filteredData, function ($value) {
            return !is_null($value);
        });
    }

    private function createDialplan(CallFlow $callFlow): void
    {
        $dialplanXml = $this->buildDialplanXml($callFlow);

        $this->dialplan->create([
            'dialplan_uuid' => $callFlow->dialplan_uuid,
            'domain_uuid' => $callFlow->domain_uuid,
            'dialplan_name' => $callFlow->call_flow_name,
            'dialplan_number' => $callFlow->call_flow_extension,
            'dialplan_context' => $callFlow->call_flow_context,
            'dialplan_continue' => 'false',
            'dialplan_xml' => $dialplanXml,
            'dialplan_order' => '333',
            'dialplan_enabled' => $callFlow->call_flow_enabled,
            'dialplan_description' => $callFlow->call_flow_description,
            'app_uuid' => 'b1b70f85-6b42-429b-8c5a-60c8b02b7d14',
        ]);
    }

    private function updateDialplan(CallFlow $callFlow): void
    {
        if (!$callFlow->dialplan) {
            $this->createDialplan($callFlow);
            return;
        }

        $dialplanXml = $this->buildDialplanXml($callFlow);

        $callFlow->dialplan->update([
            'dialplan_name' => $callFlow->call_flow_name,
            'dialplan_number' => $callFlow->call_flow_extension,
            'dialplan_context' => $callFlow->call_flow_context,
            'dialplan_xml' => $dialplanXml,
            'dialplan_enabled' => $callFlow->call_flow_enabled,
            'dialplan_description' => $callFlow->call_flow_description,
        ]);
    }

    private function buildDialplanXml(CallFlow $callFlow): string
    {
        // Escape special characters
        $destinationExtension = str_replace(['*', '+'], ['\*', '\+'], $callFlow->call_flow_extension);
        
        $destinationFeature = $callFlow->call_flow_feature_code;
        if (substr($destinationFeature, 0, 5) != 'flow+') {
            $destinationFeature = '(?:flow+)?' . $destinationFeature;
        }
        $destinationFeature = str_replace(['*', '+'], ['\*', '\+'], $destinationFeature);

        $xml = "<extension name=\"" . htmlspecialchars($callFlow->call_flow_name) . "\" continue=\"\" uuid=\"" . $callFlow->dialplan_uuid . "\">\n";
        $xml .= "	<condition field=\"destination_number\" expression=\"^" . $destinationFeature . "$\" break=\"on-true\">\n";
        $xml .= "		<action application=\"answer\" data=\"\"/>\n";
        $xml .= "		<action application=\"sleep\" data=\"200\"/>\n";
        $xml .= "		<action application=\"set\" data=\"feature_code=true\"/>\n";
        $xml .= "		<action application=\"set\" data=\"call_flow_uuid=" . $callFlow->call_flow_uuid . "\"/>\n";
        $xml .= "		<action application=\"lua\" data=\"call_flow.lua\"/>\n";
        $xml .= "	</condition>\n";
        $xml .= "	<condition field=\"destination_number\" expression=\"^" . $destinationExtension . "$\">\n";
        $xml .= "		<action application=\"set\" data=\"call_flow_uuid=" . $callFlow->call_flow_uuid . "\"/>\n";
        $xml .= "		<action application=\"lua\" data=\"call_flow.lua\"/>\n";
        $xml .= "	</condition>\n";
        $xml .= "</extension>\n";

        return $xml;
    }

    private function updateSwitch(CallFlow $callFlow): void
    {
        // Reload XML for dialplan changes
        FreeSwitch::execute('reloadxml', '');
    }

    private function sendPresenceEvent(CallFlow $callFlow): void
    {
        $domain = $callFlow->domain->domain_name ?? auth()->user()->domain->domain_name;
        
        $event = "sendevent PRESENCE_IN\n";
        $event .= "proto: flow\n";
        $event .= "event_type: presence\n";
        $event .= "alt_event_type: dialog\n";
        $event .= "Presence-Call-Direction: outbound\n";
        $event .= "state: Active (1 waiting)\n";
        $event .= "from: flow+" . $callFlow->call_flow_feature_code . "@" . $domain . "\n";
        $event .= "login: flow+" . $callFlow->call_flow_feature_code . "@" . $domain . "\n";
        $event .= "unique-id: " . $callFlow->call_flow_uuid . "\n";
        
        if ($callFlow->call_flow_status == "true") {
            $event .= "answer-state: confirmed\n";
        } else {
            $event .= "answer-state: terminated\n";
        }

        FreeSwitch::api($event);
    }

    private function clearCache(string $context): void
    {
        Cache::forget("dialplan:{$context}");
    }
}