<?php

namespace App\Services\FreeSwitch;

class FreeSwitchResponseMerger
{
    /**
     * Merge responses from multiple nodes
     * 
     * @param array $responses Array of ['node' => ClusterNode, 'response' => string|null]
     * @param string $command The command that was executed
     * @return array Combined responses with metadata
     */
    public function merge(array $responses, string $command): array
    {
        $type = $this->detectResponseType($responses);
        
        return [
            'type' => $type,
            'combined' => $this->combineByType($responses, $type),
            'nodes' => $this->formatNodeResponses($responses),
        ];
    }

    private function detectResponseType(array $responses): string
    {
        foreach ($responses as $item) {
            if (empty($item['response'])) {
                continue;
            }

            $response = trim($item['response']);
            
            if (str_starts_with($response, '<?xml') || str_starts_with($response, '<')) {
                return 'xml';
            }
            
            if (str_starts_with($response, '{') || str_starts_with($response, '[')) {
                json_decode($response);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return 'json';
                }
            }
        }
        
        return 'text';
    }

    private function combineByType(array $responses, string $type): mixed
    {
        return match($type) {
            'xml' => $this->combineXml($responses),
            'json' => $this->combineJson($responses),
            default => $this->combineText($responses),
        };
    }

    private function combineText(array $responses): string
    {
        $combined = [];
        
        foreach ($responses as $item) {
            if (!empty($item['response'])) {
                $nodeName = $item['node']->node_name;
                $combined[] = "=== Node: {$nodeName} ===\n{$item['response']}";
            }
        }
        
        return implode("\n\n", $combined);
    }


    private function combineXml(array $responses): string
    {
        $xml = new \SimpleXMLElement('<cluster_response/>');
        
        foreach ($responses as $item) {
            if (empty($item['response'])) {
                continue;
            }
            
            try {
                $nodeXml = new \SimpleXMLElement($item['response']);
                $nodeElement = $xml->addChild('node');
                $nodeElement->addAttribute('name', $item['node']->node_name);
                $nodeElement->addAttribute('hostname', $item['node']->node_hostname);
                
                foreach ($nodeXml->children() as $child) {
                    $this->xmlAppend($nodeElement, $child);
                }
            } catch (\Exception $e) {
                $nodeElement = $xml->addChild('node');
                $nodeElement->addAttribute('name', $item['node']->node_name);
                $nodeElement->addAttribute('error', 'XML parse error');
                $nodeElement[0] = $item['response'];
            }
        }
        
        return $xml->asXML();
    }

    /**
     * Combine JSON responses (merge arrays/objects)
     */
    private function combineJson(array $responses): string
    {
        $combined = [];
        
        foreach ($responses as $item) {
            if (empty($item['response'])) {
                continue;
            }
            
            $decoded = json_decode($item['response'], true);
            if ($decoded !== null) {
                $combined[$item['node']->node_name] = $decoded;
            }
        }
        
        return json_encode($combined, JSON_PRETTY_PRINT);
    }

    /**
     * Format node responses for debugging/logging
     */
    private function formatNodeResponses(array $responses): array
    {
        return array_map(function($item) {
            return [
                'node_name' => $item['node']->node_name,
                'node_hostname' => $item['node']->node_hostname,
                'response_length' => strlen($item['response'] ?? ''),
                'has_response' => !empty($item['response']),
            ];
        }, $responses);
    }

    /**
     * Helper to append XML elements
     */
    private function xmlAppend(\SimpleXMLElement $parent, \SimpleXMLElement $child): void
    {
        $new = $parent->addChild($child->getName(), (string) $child);
        
        foreach ($child->attributes() as $attr => $value) {
            $new->addAttribute($attr, $value);
        }
        
        foreach ($child->children() as $ch) {
            $this->xmlAppend($new, $ch);
        }
    }
}