<?php
namespace App\Http\Controllers;

use App\Facades\Setting;
use App\Models\CallCenterAgent;
use App\Models\XmlCDR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    private $threshold;
    private $daysRange;
    private $businessStart;
    private $businessEnd;
    private $inboundTimeRange;

    public function __construct()
    {
        // ToDo: define settings
        // $this->threshold = Setting::getSetting("talkdesk", "threshold");
        // $this->daysRange = Setting::getSetting("talkdesk", "days_range");
        // $this->businessStart = Setting::getSetting("talkdesk", "business_hour_start");
        // $this->businessEnd = Setting::getSetting("talkdesk", "business_hour_end");

        $this->threshold = 20;      // seconds
        $this->daysRange = 60;
        $this->businessStart = 9;   // local time
        $this->businessEnd = 18;    // localtime
        $this->inboundTimeRange = 'today'; // today | 15m | 30m | hour
    }

    function formatSeconds($seconds)
    {
        return gmdate("i:s", (int)$seconds);
    }

    private function baseXMLCDRQuery()
    {
        return XmlCDR::where('direction', 'inbound')
            ->whereRaw('start_epoch >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ? DAY))', [$this->daysRange])
            ->whereRaw('HOUR(FROM_UNIXTIME(start_epoch)) BETWEEN ? AND ?', [$this->businessStart, $this->businessEnd - 1]);
    }

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
        $resultQuery = $this->baseXMLCDRQuery()
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
            ", [$this->threshold, $this->threshold]);
        if(App::hasDebugModeEnabled()){
            Log::debug('['.__CLASS__.']['.__METHOD__.'] Dasboard Query: ' . $resultQuery->toRawSql());
        }
        $result = $resultQuery->first();

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
        $result = $this->baseXMLCDRQuery()
            ->selectRaw('
                COALESCE(
                    AVG(CASE
                        WHEN cc_queue_answered_epoch IS NULL
                        AND cc_queue_canceled_epoch IS NOT NULL
                        AND cc_queue_joined_epoch IS NOT NULL
                        THEN cc_queue_canceled_epoch - cc_queue_joined_epoch
                        END), 0
                ) AS AverageAbandonTime
            ')
            ->first();

        return [
            "title" => "Average Abandon Time",
            "subtitle" => "",
            "value" => $this->formatSeconds($result->AverageAbandonTime),
        ];
    }

    private function getAverageWaitTime()
    {
        $result = $this->baseXMLCDRQuery()
            ->selectRaw('
                COALESCE(
                    AVG(CASE
                        WHEN cc_queue_answered_epoch IS NOT NULL
                        AND cc_queue_joined_epoch IS NOT NULL
                        THEN cc_queue_answered_epoch - cc_queue_joined_epoch
                        END), 0
                ) AS AverageWaitTime
            ')
            ->first();

        return [
            "title" => "Average Wait Time",
            "subtitle" => "",
            "value" => $this->formatSeconds($result->AverageWaitTime),
        ];
    }

    private function getLongestWaitTime()
    {
        $result = $this->baseXMLCDRQuery()
            ->selectRaw('
                COALESCE(
                    MAX(CASE
                        WHEN cc_queue_answered_epoch IS NOT NULL
                        AND cc_queue_joined_epoch IS NOT NULL
                        THEN cc_queue_answered_epoch - cc_queue_joined_epoch
                        END), 0
                ) AS LongestWaitTime
            ')
            ->first();

        return [
            "title" => "Longest Wait Time",
            "subtitle" => "",
            "value" => $this->formatSeconds($result->LongestWaitTime),
        ];
    }

    private function getActiveAgents()
    {
        $agents = CallCenterAgent::query()
            ->select("agent_status", DB::raw("COUNT(*) as total"))
            ->groupBy("agent_status")
            ->pluck("total", "agent_status");

        $online = ($agents["Available"] ?? 0) + ($agents["Available (On Demand)"] ?? 0);
        $offline = $agents["Logged Out"] ?? 0;
        $away = $agents["On Break"] ?? 0;

        return [
            "title" => "Active Agents",
            "subtitle" => "",
            "count" => $online + $offline + $away,
            "metrics" => [
                "Available" => [
                    "value" => $online,
                    "color" => "#00A65A",
                ],
                "Logged Out" => [
                    "value" => $offline,
                    "color" => "#DD4B39",
                ],
                "On Break" => [
                    "value" => $away,
                    "color" => "#F39C12",
                ],
            ],
        ];
    }

    private function getInboundRange()
    {
        $now = time();

        switch($this->inboundTimeRange)
        {
            case '15m':
                $from = $now - 900;
                break;
            case '30m':
                $from = $now - 1800;
                break;
            case 'hour':
                $from = $now - 3600;
                break;
            default: // today
                $from = strtotime(date('Y-m-d 00:00:00'));
                break;
        }

        return [$from, $now];
    }

    private function getInboundContacts()
    {
        [$start, $end] = $this->getInboundRange();

        $result = XmlCDR::where('direction', 'inbound')
            ->whereBetween('start_epoch', [$start, $end])
            ->selectRaw("
                COALESCE(SUM(CASE
                    WHEN cc_queue_answered_epoch IS NOT NULL
                    THEN 1 ELSE 0 END), 0) AS answered,

                COALESCE(SUM(CASE
                    WHEN cc_queue_answered_epoch IS NULL
                    AND cc_queue_canceled_epoch IS NOT NULL
                    AND (cc_queue_canceled_epoch - cc_queue_joined_epoch) >= ?
                    THEN 1 ELSE 0 END), 0) AS abandoned,

                COALESCE(SUM(CASE
                    WHEN cc_queue_answered_epoch IS NULL
                    AND cc_queue_canceled_epoch IS NOT NULL
                    AND (cc_queue_canceled_epoch - cc_queue_joined_epoch) < ?
                    THEN 1 ELSE 0 END), 0) AS short_abandoned,

                COALESCE(SUM(CASE
                    WHEN cc_queue_answered_epoch IS NULL
                    AND cc_queue_canceled_epoch IS NULL
                    THEN 1 ELSE 0 END), 0) AS missed,

                COALESCE(SUM(CASE
                    WHEN voicemail_message = true
                    THEN 1 ELSE 0 END), 0) AS voicemail
            ", [$this->threshold, $this->threshold])
            ->first();

        $total =
            $result->answered +
            $result->abandoned +
            $result->short_abandoned +
            $result->missed +
            $result->voicemail;

        return [
            "title" => "Inbound Contacts",
            "subtitle" => ucfirst($this->inboundTimeRange),
            "count" => $total,
            "metrics" => [
                "Answered" => [
                    "value" => (int) $result->answered,
                    "color" => "#00A65A",
                ],
                "Abandoned" => [
                    "value" => (int) $result->abandoned,
                    "color" => "#DD4B39",
                ],
                "Short Abandoned" => [
                    "value" => (int) $result->short_abandoned,
                    "color" => "#F39C12",
                ],
                "Missed" => [
                    "value" => (int) $result->missed,
                    "color" => "#605CA8",
                ],
                "Voicemail" => [
                    "value" => (int) $result->voicemail,
                    "color" => "#3C8DBC",
                ],
            ],
        ];
    }
}
