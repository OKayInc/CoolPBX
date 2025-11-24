<?php

namespace App\Services;

use App\Models\Dialplan;

class DialplanFlowParser
{
    private int $yPosition = 100;
    private int $xOffset = 250;
    private int $ySpacing = 120;

    public function parse(Dialplan $dialplan): array
    {
        $details = $dialplan->dialplanDetails;

        $nodes = [];
        $edges = [];

        $nodes[] = [
            'id' => 'start',
            'type' => 'input',
            'position' => ['x' => $this->xOffset, 'y' => $this->yPosition],
            'data' => [
                'label' => "📞 {$dialplan->dialplan_name}",
                'description' => $dialplan->dialplan_description
            ]
        ];

        $lastNodeId = 'start';
        $currentGroup = null;
        $groupNodes = [];

        foreach ($details as $index => $detail) {
            $nodeId = "node_{$index}";

            if ($currentGroup !== $detail->dialplan_detail_group) {
                $currentGroup = $detail->dialplan_detail_group;
                $this->yPosition += $this->ySpacing;
            }

            $node = $this->createNodeFromDetail($nodeId, $detail);
            $nodes[] = $node;

            // Crear edge desde el último nodo
            if ($lastNodeId) {
                $edges[] = [
                    'id' => "edge_{$lastNodeId}_{$nodeId}",
                    'source' => $lastNodeId,
                    'target' => $nodeId,
                    'type' => $this->getEdgeType($detail),
                    'animated' => $detail->dialplan_detail_tag === 'action',
                    'label' => $this->getEdgeLabel($detail)
                ];
            }

            $lastNodeId = $nodeId;

            // Incrementar posición Y
            $this->yPosition += $this->ySpacing;
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges
        ];
    }

    private function createNodeFromDetail(string $id, $detail): array
    {
        $tag = $detail->dialplan_detail_tag;

        return [
            'id' => $id,
            'type' => $this->getNodeType($tag),
            'position' => ['x' => $this->xOffset, 'y' => $this->yPosition],
            'data' => [
                'label' => $this->getNodeLabel($detail),
                'tag' => $tag,
                'type' => $detail->dialplan_detail_type,
                'data' => $detail->dialplan_detail_data,
                'group' => $detail->dialplan_detail_group,
                'order' => $detail->dialplan_detail_order,
                'break' => $detail->dialplan_detail_break,
                'inline' => $detail->dialplan_detail_inline,
            ]
        ];
    }

    private function getNodeType(string $tag): string
    {
        return match ($tag) {
            'condition' => 'condition',
            'action' => 'action',
            'anti-action' => 'antiAction',
            'regex' => 'regex',
            default => 'default'
        };
    }

    private function getNodeLabel($detail): string
    {
        $tag = $detail->dialplan_detail_tag;
        $type = $detail->dialplan_detail_type;
        $data = $detail->dialplan_detail_data;

        return match ($tag) {
            'condition' => "🔍 {$type}: {$data}",
            'action' => "▶️ {$type}: {$data}",
            'anti-action' => "⛔ {$type}: {$data}",
            'regex' => "📝 {$type}: {$data}",
            default => "{$type}: {$data}"
        };
    }

    private function getEdgeType($detail): string
    {
        return match ($detail->dialplan_detail_tag) {
            'action' => 'smoothstep',
            'anti-action' => 'step',
            default => 'default'
        };
    }

    private function getEdgeLabel($detail): ?string
    {
        if ($detail->dialplan_detail_tag === 'action') {
            return 'true';
        }
        if ($detail->dialplan_detail_tag === 'anti-action') {
            return 'false';
        }
        return null;
    }
}
