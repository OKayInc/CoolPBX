<?php
namespace App\Http\Controllers;

use App\Facades\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            "service_level" => $this->getServiceLevel(),
            "average_abandon_time" => $this->getAverageAbandonTime(),
            "average_wait_time" => $this->getAverageWaitTime(),
            "longest_wait_time" => $this->getLongestWaitTime(),
            "active_agents" => $this->getActiveAgents(),
            "inbound_contacts" => $this->getInboundContacts(),
        ];

        return view("dashboard", compact("stats"));
    }

    private function getServiceLevel()
    {
        // ToDo: define settings
        // $threshold = Setting::getSetting("talkdesk", "threshold") ?? 20;
        // $daysRange = Setting::getSetting("talkdesk", "days_range") ?? 30;
        // $businessStart = Setting::getSetting("talkdesk", "business_hour_start") ?? 9;
        // $businessEnd = Setting::getSetting("talkdesk", "business_hour_end") ?? 18;

        $threshold = 20;
        $daysRange = 30;
        $businessStart = 9;
        $businessEnd = 18;

        $result = DB::table('v_xml_cdr')
            ->selectRaw("
                SUM(CASE
                        WHEN cc_queue_answered_epoch IS NOT NULL
                            AND (cc_queue_joined_epoch - answer_epoch) <= ?
                        THEN 1 ELSE 0
                    END) AS callsAnsweredWithinThreshold,
                SUM(CASE
                        WHEN cc_queue_answered_epoch IS NULL
                            AND (cc_queue_joined_epoch - answer_epoch) <= ?
                        THEN 1 ELSE 0
                    END) AS callsMissedWithinThreshold,
                SUM(CASE
                        WHEN cc_queue_answered_epoch IS NOT NULL
                        THEN 1 ELSE 0
                    END) AS totalAnsweredCalls,
                SUM(CASE
                        WHEN cc_queue_answered_epoch IS NULL
                        THEN 1 ELSE 0
                    END) AS totalMissedCalls
            ", [$threshold, $threshold])
            ->where('direction', 'inbound')
            ->whereRaw('start_epoch >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ? DAY))', [$daysRange])
            ->whereRaw("HOUR(FROM_UNIXTIME(start_epoch)) BETWEEN ? AND ?", [$businessStart, $businessEnd - 1])
            ->first();

        $serviceLevel = 0;

        $totalCalls = $result->totalAnsweredCalls + $result->totalMissedCalls;

        if($totalCalls > 0)
        {
            $serviceLevel = (($result->callsAnsweredWithinThreshold + $result->callsMissedWithinThreshold) / $totalCalls) * 100;
        }

        return [
            "title" => "Service Level",
            "subtitle" => "",
            "value" => round($serviceLevel, 2) . "%",
        ];
    }

    private function getAverageAbandonTime()
    {
        return [
            "title" => "Average Abandon Time",
            "subtitle" => "",
            "value" => 0,
        ];
    }

    private function getAverageWaitTime()
    {
        return [
            "title" => "Average Wait Time",
            "subtitle" => "",
            "value" => "00:13",
        ];
    }

    private function getLongestWaitTime()
    {
        return [
            "title" => "Longest Wait Time",
            "subtitle" => "",
            "value" => 0,
        ];
    }

    private function getActiveAgents()
    {
        return [
            "title" => "Active Agents",
            "subtitle" => "",
            "count" => 7,
            "metrics" => [
                "online" => [
                    "value" => 4,
                    "color" => "#00A65A",
                ],
                "offline" => [
                    "value" => 1,
                    "color" => "#DD4B39",
                ],
                "other" => [
                    "value" => 2,
                    "color" => "#F39C12",
                ],
            ],
        ];
    }

    private function getInboundContacts()
    {
        return [
            "title" => "Inbound Contacts",
            "subtitle" => "",
            "count" => 0,
            "metrics" => [
            ],
        ];
    }
}
