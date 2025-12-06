<?php

namespace App\Services;

use App\Models\Dialplan;
use App\Models\DialplanDetail;
use Illuminate\Support\Collection;

class DialplanMapParser
{
    private array $nodes = [];
    private array $edges = [];
    private Collection $dialplanLookup;

    public function parseAllDialplans(string $domainUuid): array
    {
        $dialplans = Dialplan::with([
            'dialplanDetails',
            'callflow',
            'callcenterqueue',
            'conferencecenter',
            'ringgroup',
            'ivr_menu'
        ])
            ->where('domain_uuid', $domainUuid)
            ->where('dialplan_enabled', 'true')
            ->orderBy('dialplan_order')
            ->get();

        $this->dialplanLookup = $dialplans->mapWithKeys(function ($dp) {
            $cleanNumber = str_replace(['^', '$'], '', $dp->dialplan_number);
            return ["{$dp->dialplan_context}_{$cleanNumber}" => $dp];
        });

        $this->createNodes($dialplans);
        $this->analyzeTransfers($dialplans);

        return [
            'nodes' => $this->nodes,
            'edges' => $this->edges
        ];
    }

    private function createNodes(Collection $dialplans): void
    {
        foreach ($dialplans as $dialplan) {
            $nodeType = $this->determineNodeType($dialplan);

            $this->nodes[] = [
                'id' => $dialplan->dialplan_uuid,
                'type' => $nodeType,
                'position' => ['x' => 0, 'y' => 0], 
                'data' => [
                    'type' => $nodeType,
                    'label' => $this->getNodeLabel($dialplan, $nodeType),
                    'number' => $dialplan->dialplan_number,
                    'context' => $dialplan->dialplan_context,
                    'isCallFlow' => $dialplan->callflow !== null,
                ]
            ];
        }
    }
    private function determineNodeType(Dialplan $dialplan): string
    {
        if ($dialplan->ivr_menu) return 'ivr';
        if ($dialplan->callcenterqueue) return 'queue';
        if ($dialplan->ringgroup) return 'ringgroup';
        if ($dialplan->conferencecenter) return 'conference';

        if ($dialplan->callflow) return 'callflow';

        return 'default';
    }

    private function getNodeLabel(Dialplan $dialplan, string $type): string
    {
        return match ($type) {
            'callflow' => "Flow: " . ($dialplan->callflow->call_flow_name ?? 'Control'),
            'queue' => "Cola: " . ($dialplan->callcenterqueue->queue_name ?? $dialplan->dialplan_name),
            default => $dialplan->dialplan_name
        };
    }

    private function analyzeTransfers(Collection $dialplans): void
    {
        foreach ($dialplans as $dialplan) {
            foreach ($dialplan->dialplanDetails as $detail) {
                if ($this->isTransferAction($detail)) {
                    $this->createEdgeFromTransfer($dialplan, $detail);
                }
            }
        }
    }

    private function isTransferAction(DialplanDetail $detail): bool
    {
        return in_array($detail->dialplan_detail_tag, ['action', 'anti-action'])
            && in_array($detail->dialplan_detail_type, ['transfer', 'bridge', 'lua', 'socket']);
    }
    private function createEdgeFromTransfer(Dialplan $source, DialplanDetail $detail): void
    {
        $destination = $this->parseTransferDestination($detail->dialplan_detail_data);
        if (!$destination) return;

        $target = $this->findTargetInCache($destination['number'], $destination['context']);
        if (!$target || $source->dialplan_uuid === $target->dialplan_uuid) return;


        $label = $this->generateEdgeLabel($source, $detail);

        $isAntiAction = $detail->dialplan_detail_tag === 'anti-action';

        $this->edges[] = [
            'id' => "e_{$detail->dialplan_detail_uuid}",
            'source' => $source->dialplan_uuid,
            'target' => $target->dialplan_uuid,
            'label' => $label,
            'animated' => !$isAntiAction, 
            'style' => $isAntiAction ? ['stroke' => '#ff9999', 'strokeDasharray' => '5,5'] : [],
            'data' => [
                'type' => $detail->dialplan_detail_type,
                'condition_group' => $detail->dialplan_detail_group
            ]
        ];
    }

    private function generateEdgeLabel(Dialplan $dialplan, DialplanDetail $transferDetail): string
    {
        if ($dialplan->callflow) {
            if ($transferDetail->dialplan_detail_tag === 'action') return "Activo (ON)";
            if ($transferDetail->dialplan_detail_tag === 'anti-action') return "Inactivo (OFF)";
        }

        if ($transferDetail->dialplan_detail_tag === 'anti-action') {
            return "Else / False";
        }

        $conditions = $dialplan->dialplanDetails
            ->where('dialplan_detail_group', $transferDetail->dialplan_detail_group)
            ->where('dialplan_detail_tag', 'condition');

        $labels = [];
        foreach ($conditions as $cond) {
            $formatted = $this->formatConditionText($cond->dialplan_detail_type, $cond->dialplan_detail_data);
            if ($formatted) $labels[] = $formatted;
        }

        return implode("\n", $labels);
    }


    private function formatConditionText(string $type, string $data): ?string
    {
        $cleanData = str_replace(['^', '$'], '', $data);

        return match ($type) {
            'destination_number' => null, 
            'caller_id_number' => "CID: {$cleanData}",
            'context' => null, 
            'wday' => "Days: {$cleanData}", 
            'mday' => "Day Month: {$cleanData}",
            'mon' => "Month: {$cleanData}",
            'hour' => "Hour: {$cleanData}",
            'minute' => "Minute: {$cleanData}",
            default => "{$type}: {$cleanData}"
        };
    }

    private function parseTransferDestination(string $data): ?array
    {
        if (preg_match('/^([^\s]+)\s+XML\s+([^\s]+)/', $data, $matches)) {
            return ['number' => $matches[1], 'context' => $matches[2]];
        }
        if (preg_match('/^\d+$/', $data)) {
            return ['number' => $data, 'context' => 'default'];
        }
        return null;
    }

    private function findTargetInCache(string $number, string $context): ?Dialplan
    {
        $key = "{$context}_{$number}";
        if ($this->dialplanLookup->has($key)) return $this->dialplanLookup->get($key);
        return null;
    }

    private function findTargetDialplan(string $number, string $context, string $domainUuid): ?Dialplan
    {
        return Dialplan::where('domain_uuid', $domainUuid)
            ->where('dialplan_context', $context)
            ->where(function ($query) use ($number) {
                $query->where('dialplan_number', $number)
                    ->orWhere('dialplan_destination', 'like', "%{$number}%");
            })
            ->first();
    }

    private function extractConditions(Dialplan $dialplan, $detail): array
    {
        $conditions = [];

        $groupConditions = $dialplan->dialplanDetails()
            ->where('dialplan_detail_group', $detail->dialplan_detail_group)
            ->where('dialplan_detail_tag', 'condition')
            ->get();

        foreach ($groupConditions as $condition) {
            $label = $this->formatCondition($condition);
            if ($label) {
                $conditions[] = $label;
            }
        }

        if ($dialplan->callflow) {
            $conditions[] = "CallFlow: {$dialplan->callflow->call_flow_name}";
        }

        return $conditions;
    }

    private function formatCondition($condition): ?string
    {
        $type = $condition->dialplan_detail_type;
        $data = $condition->dialplan_detail_data;

        return match ($type) {
            'destination_number' => "Destino: {$data}",
            'caller_id_number' => "Caller ID: {$data}",
            'network_addr' => "Red: {$data}",
            'context' => "Context: {$data}",
            default => "{$type}: {$data}"
        };
    }

    private function formatConditionLabel(array $conditions): string
    {
        if (empty($conditions)) {
            return '';
        }

        return implode("\n", array_slice($conditions, 0, 2)); // Max 2 líneas
    }
}
