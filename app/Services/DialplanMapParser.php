<?php

namespace App\Services;

use App\Models\Dialplan;
use Illuminate\Support\Collection;

class DialplanMapParser
{
    private array $nodes = [];
    private array $edges = [];
    private int $gridColumns = 4;
    private int $nodeWidth = 300;
    private int $nodeHeight = 150;
    private int $xSpacing = 400;
    private int $ySpacing = 200;

    public function parseAllDialplans(string $domainUuid): array
    {
        // Traer todos los dialplans del dominio con sus detalles
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

        // Crear nodos para cada dialplan
        $this->createNodes($dialplans);

        // Analizar transferencias y crear edges
        $this->analyzeTransfers($dialplans);

        return [
            'nodes' => $this->nodes,
            'edges' => $this->edges
        ];
    }

    private function createNodes(Collection $dialplans): void
    {
        $row = 0;
        $col = 0;

        foreach ($dialplans as $index => $dialplan) {
            // Calcular posición en grid
            $x = $col * $this->xSpacing;
            $y = $row * $this->ySpacing;

            // Determinar tipo de nodo según sus relaciones
            $nodeType = $this->determineNodeType($dialplan);
            
            $this->nodes[] = [
                'id' => $dialplan->dialplan_uuid,
                'type' => $nodeType,
                'position' => ['x' => $x, 'y' => $y],
                'data' => [
                    'label' => $dialplan->dialplan_name,
                    'number' => $dialplan->dialplan_number,
                    'context' => $dialplan->dialplan_context,
                    'description' => $dialplan->dialplan_description,
                    'type' => $nodeType,
                    'hasCallFlow' => $dialplan->callflow !== null,
                    'hasQueue' => $dialplan->callcenterqueue !== null,
                    'hasIVR' => $dialplan->ivr_menu !== null,
                    'hasRingGroup' => $dialplan->ringgroup !== null,
                ]
            ];

            // Avanzar en el grid
            $col++;
            if ($col >= $this->gridColumns) {
                $col = 0;
                $row++;
            }
        }
    }

    private function determineNodeType(Dialplan $dialplan): string
    {
        // Determinar tipo según relaciones
        if ($dialplan->ivr_menu) return 'ivr';
        if ($dialplan->callcenterqueue) return 'queue';
        if ($dialplan->ringgroup) return 'ringgroup';
        if ($dialplan->conferencecenter) return 'conference';
        if ($dialplan->callflow) return 'callflow';
        
        return 'default';
    }

    private function analyzeTransfers(Collection $dialplans): void
    {
        foreach ($dialplans as $dialplan) {
            // Analizar cada detail buscando transferencias
            foreach ($dialplan->dialplanDetails as $detail) {
                if ($this->isTransferAction($detail)) {
                    $this->createEdgeFromTransfer($dialplan, $detail);
                }
            }
        }
    }

    private function isTransferAction($detail): bool
    {
        return in_array($detail->dialplan_detail_tag, ['action', 'anti-action']) 
            && in_array($detail->dialplan_detail_type, ['transfer', 'bridge']);
    }

    private function createEdgeFromTransfer(Dialplan $sourceDialplan, $detail): void
    {
        // Parsear el destino de la transferencia
        // Formato típico: "1001 XML default" o "user/1001@default"
        $destination = $this->parseTransferDestination($detail->dialplan_detail_data);
        
        if (!$destination) return;

        // Buscar el dialplan de destino
        $targetDialplan = $this->findTargetDialplan(
            $destination['number'], 
            $destination['context'],
            $sourceDialplan->domain_uuid
        );

        if (!$targetDialplan) return;

        // Obtener condiciones aplicables
        $conditions = $this->extractConditions($sourceDialplan, $detail);

        // Crear edge
        $edgeId = "{$sourceDialplan->dialplan_uuid}_{$targetDialplan->dialplan_uuid}_{$detail->dialplan_detail_uuid}";
        
        $this->edges[] = [
            'id' => $edgeId,
            'source' => $sourceDialplan->dialplan_uuid,
            'target' => $targetDialplan->dialplan_uuid,
            'type' => 'smoothstep',
            'animated' => $detail->dialplan_detail_tag === 'action',
            'label' => $this->formatConditionLabel($conditions),
            'data' => [
                'conditions' => $conditions,
                'transferType' => $detail->dialplan_detail_type,
                'isAntiAction' => $detail->dialplan_detail_tag === 'anti-action',
            ]
        ];
    }

    private function parseTransferDestination(string $data): ?array
    {
        // Formato: "1001 XML default" o "user/1001@default"
        
        // Patrón 1: "NUMBER XML CONTEXT"
        if (preg_match('/^([^\s]+)\s+XML\s+([^\s]+)/', $data, $matches)) {
            return [
                'number' => $matches[1],
                'context' => $matches[2]
            ];
        }

        // Patrón 2: "user/NUMBER@CONTEXT"
        if (preg_match('/user\/([^@]+)@([^\s]+)/', $data, $matches)) {
            return [
                'number' => $matches[1],
                'context' => $matches[2]
            ];
        }

        // Patrón 3: solo número (usar context default)
        if (preg_match('/^\d+$/', $data)) {
            return [
                'number' => $data,
                'context' => 'default'
            ];
        }

        return null;
    }

    private function findTargetDialplan(string $number, string $context, string $domainUuid): ?Dialplan
    {
        return Dialplan::where('domain_uuid', $domainUuid)
            ->where('dialplan_context', $context)
            ->where(function($query) use ($number) {
                $query->where('dialplan_number', $number)
                    ->orWhere('dialplan_destination', 'like', "%{$number}%");
            })
            ->first();
    }

    private function extractConditions(Dialplan $dialplan, $detail): array
    {
        $conditions = [];

        // Buscar condiciones en el mismo grupo
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

        // Añadir info de CallFlow si existe
        if ($dialplan->callflow) {
            $conditions[] = "CallFlow: {$dialplan->callflow->call_flow_name}";
        }

        return $conditions;
    }

    private function formatCondition($condition): ?string
    {
        $type = $condition->dialplan_detail_type;
        $data = $condition->dialplan_detail_data;

        return match($type) {
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