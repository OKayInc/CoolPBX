<?php

namespace App\Services;

use App\Models\Dialplan;
use Illuminate\Support\Facades\DB;

class DialplanXmlGenerator
{
    public function generateXml(string $dialplanUuid): string
    {
        $dialplan = Dialplan::with(['dialplanDetails' => function ($query) {
            $query->where('dialplan_detail_enabled', 'true')
                ->orWhereNull('dialplan_detail_enabled')
                ->orderBy('dialplan_detail_group')
                ->orderByRaw("
                    CASE dialplan_detail_tag
                        WHEN 'condition' THEN 1
                        WHEN 'action' THEN 2
                        WHEN 'anti-action' THEN 3
                        ELSE 100
                    END
                ")
                ->orderBy('dialplan_detail_order');
        }])->where('dialplan_uuid', $dialplanUuid)->first();

        if (!$dialplan) {
            return '';
        }

        $xml = '';
        $previousGroup = '';
        $conditionTagOpen = false;
        $conditionAttribute = '';
        $condition = '';
        $conditionBreak = '';
        $firstAction = true;

        $xml .= "<extension name=\"{$dialplan->dialplan_name}\" ";
        $xml .= "continue=\"{$dialplan->dialplan_continue}\" ";
        $xml .= "uuid=\"{$dialplan->dialplan_uuid}\">\n";

        foreach ($dialplan->dialplanDetails as $detail) {
            $group = $detail->dialplan_detail_group;
            $tag = $detail->dialplan_detail_tag;
            $type = $detail->dialplan_detail_type;
            $data = str_replace('$$', '$', $detail->dialplan_detail_data ?? '');
            $break = $detail->dialplan_detail_break;
            $inline = $detail->dialplan_detail_inline;

            $inlineAttr = $inline ? " inline=\"{$inline}\"" : '';

            if ($previousGroup !== '' && $previousGroup != $group) {
                if ($conditionTagOpen) {
                    if ($conditionAttribute) {
                        $xml .= "\t<condition {$conditionAttribute}{$conditionBreak}/>\n";
                        $conditionAttribute = '';
                    } elseif ($condition) {
                        $xml .= "{$condition}/>\n";
                        $condition = '';
                    } else {
                        $xml .= "\t</condition>\n";
                    }
                    $conditionTagOpen = false;
                }
                $firstAction = true;
            }

            if ($tag === 'condition') {
                $timeConditions = ['hour', 'minute', 'minute-of-day', 'mon', 'mday', 'year', 'wday', 'week', 'date-time'];
                $isTimeCondition = in_array($type, $timeConditions);

                if ($conditionTagOpen) {
                    if ($condition && !$isTimeCondition) {
                        $xml .= "{$condition}/>\n";
                        $condition = '';
                        $conditionTagOpen = false;
                    } elseif ($conditionAttribute && !$isTimeCondition) {
                        $xml .= "\t<condition {$conditionAttribute}{$conditionBreak}/>\n";
                        $conditionAttribute = '';
                        $conditionTagOpen = false;
                    }
                }

                $conditionBreak = $break ? " break=\"{$break}\"" : '';

                if ($isTimeCondition) {
                    if ($conditionAttribute) {
                        $conditionAttribute .= "{$type}=\"{$data}\" ";
                    } else {
                        $conditionAttribute = "{$type}=\"{$data}\" ";
                    }
                    $condition = '';
                } else {
                    $condition = "\t<condition field=\"{$type}\" expression=\"{$data}\"{$conditionBreak}";
                }

                $conditionTagOpen = true;
            }

            if ($tag === 'action' || $tag === 'anti-action') {
                if ($conditionTagOpen) {
                    if ($conditionAttribute) {
                        $xml .= "\t<condition {$conditionAttribute}{$conditionBreak}>\n";
                        $conditionAttribute = '';
                    } elseif ($condition) {
                        $xml .= "{$condition}>\n";
                        $condition = '';
                    }
                }

                if ($firstAction && ($dialplan->dialplan_context === 'public' || 
                    strpos($dialplan->dialplan_context, 'public@') === 0 || 
                    substr($dialplan->dialplan_context, -7) === '.public')) {
                    
                    $xml .= "\t\t<action application=\"export\" data=\"call_direction=inbound\" inline=\"true\"/>\n";
                    
                    if ($dialplan->domain_uuid) {
                        $domain = DB::table('v_domains')
                            ->where('domain_uuid', $dialplan->domain_uuid)
                            ->first();
                        
                        if ($domain) {
                            $xml .= "\t\t<action application=\"set\" data=\"domain_uuid={$dialplan->domain_uuid}\" inline=\"true\"/>\n";
                            $xml .= "\t\t<action application=\"set\" data=\"domain_name={$domain->domain_name}\" inline=\"true\"/>\n";
                        }
                    }
                    
                    $firstAction = false;
                }

                if ($tag === 'action') {
                    $xml .= "\t\t<action application=\"{$type}\" data=\"{$data}\"{$inlineAttr}/>\n";
                } else {
                    $xml .= "\t\t<anti-action application=\"{$type}\" data=\"{$data}\"{$inlineAttr}/>\n";
                }
            }

            $previousGroup = $group;
        }

        if ($conditionTagOpen) {
            if ($conditionAttribute) {
                $xml .= "\t<condition {$conditionAttribute}{$conditionBreak}/>\n";
            } elseif ($condition) {
                $xml .= "{$condition}/>\n";
            } else {
                $xml .= "\t</condition>\n";
            }
        }

        $xml .= "</extension>\n";

        return $xml;
    }

    public function regenerateAndSave(string $dialplanUuid): bool
    {
        $xml = $this->generateXml($dialplanUuid);
        
        if (empty($xml)) {
            return false;
        }

        return DB::table(Dialplan::getTableName())
            ->where('dialplan_uuid', $dialplanUuid)
            ->update(['dialplan_xml' => $xml]) > 0;
    }

    public function regenerateMultiple(array $dialplanUuids): int
    {
        $updated = 0;
        
        foreach ($dialplanUuids as $uuid) {
            if ($this->regenerateAndSave($uuid)) {
                $updated++;
            }
        }
        
        return $updated;
    }
}