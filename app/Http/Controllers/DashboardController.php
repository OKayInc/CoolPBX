<?php
namespace App\Http\Controllers;

use App\Facades\Setting;
use App\Models\CallCenterAgent;
use App\Models\Domain;
use App\Models\Voicemail;
use App\Models\VoicemailMessage;
use App\Models\XmlCDR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    private $threshold;
    private $daysRange;
    private $businessStart;
    private $businessEnd;
    private $inboundTimeRange;

    private $colorRed;
    private $colorGreen;
    private $colorBlue;
    private $colorYellow;
    private $colorOrange;
    private $colorViolet;

    private $scope;

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

        $this->colorRed = "#DD4B39FF";
        $this->colorGreen = "#00A65AFF";
        $this->colorBlue = "#1D78DFFF";
        $this->colorYellow = "#F3DD12FF";
        $this->colorOrange = "#E97313FF";
        $this->colorViolet = "#605CA8FF";
    }

    function formatSeconds($seconds)
    {
        return gmdate("i:s", (int)$seconds);
    }

    private function baseXMLCDRQuery()
    {
        return XmlCDR::where('domain_uuid', Session::get("domain_uuid"))
            ->where('direction', 'inbound')
            ->where('cc_side', 'member')
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
            "new_messages" => $this->getNewMessages(),
            "missed_calls" => $this->getMissedCalls(),
            "recent_calls" => $this->getRecentCalls(),
            "disk_usage" => $this->getDiskUsage(),
            "cpu_usage" => $this->getCpuUsage(),
            "system_counts" => $this->getSystemCounts(),
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
            Log::debug('['.__CLASS__.']['.__METHOD__.'] Dasboard - Service Level: ' . $resultQuery->toRawSql());
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
            ->select("agent_status", "agent_name")
            ->where('domain_uuid', Session::get("domain_uuid"))
            ->get()
            ->groupBy("agent_status")
            ->map(function($items)
            {
                return [
                    "total"  => $items->count(),
                    "names"  => $items->pluck("agent_name")->values()
                ];
            });

        $available = $agents["Available"]["total"] ?? 0;
        $availableOnDemand = $agents["Available (On Demand)"]["total"] ?? 0;
        $loggedOut = $agents["Logged Out"]["total"] ?? 0;
        $onBreak = $agents["On Break"]["total"] ?? 0;

        return [
            "title" => "Active Agents",
            "subtitle" => "",
            "count" => $available + $availableOnDemand + $loggedOut + $onBreak,
            "metrics" => [
                "Available" => [
                    "value" => $available,
                    "color" => $this->colorGreen,
                    "extra" => $agents["Available"]["names"] ?? [],
                ],
                "Available (On Demand)" => [
                    "value" => $availableOnDemand,
                    "color" => $this->colorBlue,
                    "extra" => $agents["Available (On Demand)"]["names"] ?? [],
                ],
                "Logged Out" => [
                    "value" => $loggedOut,
                    "color" => $this->colorRed,
                    "extra" => $agents["Logged Out"]["names"] ?? [],
                ],
                "On Break" => [
                    "value" => $onBreak,
                    "color" => $this->colorOrange,
                    "extra" => $agents["On Break"]["names"] ?? [],
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
            case '60m':
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

        $resultQuery = XmlCDR::where('domain_uuid', Session::get("domain_uuid"))
            ->where('direction', 'inbound')
            ->where('cc_side', 'member')
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
                    WHEN destination_number LIKE '*99%'
                    THEN 1 ELSE 0 END), 0) AS voicemail
            ", [$this->threshold, $this->threshold]);

        if(App::hasDebugModeEnabled()){
            Log::debug('['.__CLASS__.']['.__METHOD__.'] Dasboard - Inbound contacts: ' . $resultQuery->toRawSql());
        }

        $result = $resultQuery->first();

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
                    "color" => $this->colorGreen,
                    "link" => route("xmlcdr.filter_status", ["status" => "answered"]),
                ],
                "Abandoned" => [
                    "value" => (int) $result->abandoned,
                    "color" => $this->colorRed,
                    "link" => route("xmlcdr.filter_status", ["status" => "cancelled"]),
                ],
                "Short Abandoned" => [
                    "value" => (int) $result->short_abandoned,
                    "color" => $this->colorOrange,
                    "link" => route("xmlcdr.filter_status", ["status" => "cancelled"]),
                ],
                "Missed" => [
                    "value" => (int) $result->missed,
                    "color" => $this->colorViolet,
                    "link" => route("xmlcdr.filter_status", ["status" => "missed"]),
                ],
                "Voicemail" => [
                    "value" => (int) $result->voicemail,
                    "color" => $this->colorYellow,
                    "link" => route("xmlcdr.filter_status", ["status" => "voicemail"]),
                ],
            ],
        ];
    }

    public function ajaxInboundContacts(Request $request)
    {
        $valid = ["15m", "30m", "60m", "today"];

        $range = $request->query("range", "today");

        if(!in_array($range, $valid))
        {
            $range = "today";
        }

        $this->inboundTimeRange = $range;

        $data = $this->getInboundContacts();

        return response()->json($data);
    }

    public function getNewMessages()
    {
        $voicemails = Voicemail::query()
            ->where('domain_uuid', Session::get('domain_uuid'))
            ->withCount([
                'voicemailmessages as total_messages',
                'voicemailmessages as new_messages' => function ($q) {
                    $q->where('message_status', '')
                    ->orWhereNull('message_status');
                },
            ])
            ->get();

        $totalMessages = $voicemails->sum('total_messages');
        $newMessages = $voicemails->sum('new_messages');

        return [
            'title' => 'New Messages',
            'subtitle' => '',
            'count' => $totalMessages,
            'metrics' => [
                'New Messages' => [
                    'value' => $newMessages,
                    'color' => $this->colorGreen,
                ],
            ],
        ];
    }

    private function getAssignedExtensions()
    {
        $assignedExtensions = [];

        $userExtensions = Setting::getSetting('user', 'extension');

        if(is_array($userExtensions))
        {
            foreach($userExtensions  as $userExtension)
            {
                $assignedExtensions[] = [
                    'extension_uuid' => $userExtension['extension_uuid'],
                    'destination_number' => $userExtension['user'],
                ];
            }
        }

        return $assignedExtensions;
    }

    public function getMissedCalls()
    {
        $assignedExtensions = $this->getAssignedExtensions();

        $query = XmlCDR::query()
            ->where('domain_uuid', Session::get('domain_uuid'))
            ->whereIn('direction', ['inbound', 'local'])
            ->where(function ($q) {
                $q->where('missed_call', true)
                ->orWhereNull('bridge_uuid');
            })
            ->where('hangup_cause', '<>', 'LOSE_RACE')
            ->where('start_epoch', '>', (time() - 86400));

        if(!empty($assignedExtensions))
        {
            $query->where(function ($q) use ($assignedExtensions)
            {
                foreach($assignedExtensions as $assignedExtension)
                {
                    $q->orWhere(function ($or) use ($assignedExtension)
                    {
                        $or->where('extension_uuid', $assignedExtension['extension_uuid'])
                        ->orWhere('destination_number', $assignedExtension['destination_number']);
                    });
                }
            });
        }

        $missedCalls = $query->count();

        return [
            'title' => 'Missed Calls',
            'subtitle' => 'Last 24 hours',
            'count' => $missedCalls,
            'metrics' => [
                'Missed Calls' => [
                    'value' => $missedCalls,
                    'color' => $this->colorRed,
                ],
            ],
        ];
    }

    public function getRecentCalls()
    {
        $assignedExtensions = $this->getAssignedExtensions();

        $query = XmlCdr::query()
            ->where('domain_uuid', Session::get('domain_uuid'))
            ->where('start_epoch', '>', (now()->subDay()->timestamp));

        if(!empty($assignedExtensions))
        {
            $query->where(function ($q) use ($assignedExtensions)
            {
                foreach($assignedExtensions as $extensionUuid => $extension)
                {
                    $q->orWhere(function ($q2) use ($extensionUuid, $extension)
                    {
                        $q2->where('extension_uuid', $extensionUuid)
                        ->orWhere('caller_id_number', $extension)
                        ->orWhere('destination_number', $extension)
                        ->orWhere('destination_number', '*99' . $extension);
                    });
                }
            });
        }

        $count = $query->count();

        return [
            'title' => 'Recent Calls',
            'subtitle' => 'Last 24 hours',
            'count' => $count,
            'metrics' => [
                'Missed Calls' => [
                    'value' => $count,
                    'color' => $this->colorBlue,
                ],
            ],
        ];
    }

    public function getDiskUsage()
    {
        $path = '/home';

        $total = @disk_total_space($path);
        $free  = @disk_free_space($path);

        if(!$total || !$free)
        {
            $percent = null;
        }
        else
        {
            $used = $total - $free;
            $percent = round(($used / $total) * 100);
        }

        return [
            'title' => 'Disk Usage',
            'subtitle' => $path,
            'count' => $percent,
            'metrics' => [
                'Used' => [
                    'value' => $percent,
                    'color' => $percent >= 85 ? $this->colorRed : $this->colorBlue,
                ],
            ],
            "system_info" => $this->getSystemInfo(),
        ];
    }

    public function getSystemInfo()
    {
        return [
            'App' => Setting::getSetting('theme', 'title', 'text') ?? config("app.name"),
            'OS Uptime' => $this->getOsUptime(),
            'Memory Usage' => $this->getMemoryUsage(),
            'Avail. Memory' => $this->getAvailableMemory(),
            'DB Connections' => $this->getDbConnections(),
        ];
    }

    private function getOsUptime()
    {
        if(!is_readable('/proc/uptime'))
        {
            return null;
        }

        $contents = trim(file_get_contents('/proc/uptime'));

        [$seconds] = explode(' ', $contents);

        return $this->formatUptime((int)$seconds);
    }

    private function formatUptime(int $seconds): string
    {
        $weeks = intdiv($seconds, 604800);
        $seconds %= 604800;

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;

        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;

        $minutes = intdiv($seconds, 60);

        $parts = [];

        if($weeks)
        {
            $parts[] = "$weeks weeks";
        }

        if($days)
        {
            $parts[] = "$days days";
        }

        if($hours)
        {
            $parts[] = "$hours hours";
        }

        if($minutes)
        {
            $parts[] = "$minutes minutes";
        }

        return implode(', ', $parts);
    }

    private function getMemoryUsage()
    {
        if(!stristr(PHP_OS, 'Linux'))
        {
            return 'N/A';
        }

        $meminfo = file_get_contents('/proc/meminfo');

        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);

        if(empty($total[1]) || empty($available[1]))
        {
            return 'N/A';
        }

        $used = $total[1] - $available[1];
        $percent = ($used / $total[1]) * 100;

        return round($percent) . '%';
    }

    private function getAvailableMemory()
    {
        if(!stristr(PHP_OS, 'Linux'))
        {
            return 'N/A';
        }

        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);

        if(empty($available[1]))
        {
            return 'N/A';
        }

        $mb = $available[1] / 1024;

        return round($mb / 1024, 1) . ' GiB';
    }

    private function getDbConnections()
    {
        try
        {
            $result = DB::selectOne('SHOW STATUS WHERE Variable_name = "Threads_connected"');

            return $result->Value ?? '0';
        }
        catch(\Throwable $e)
        {
            return 'N/A';
        }
    }

    public function getCpuUsage()
    {
        if(!stristr(PHP_OS, 'Linux'))
        {
            return 'N/A';
        }

        $stat1 = $this->readCpuStat();
        usleep(200000); // 0.2s
        $stat2 = $this->readCpuStat();

        if(!$stat1 || !$stat2)
        {
            return 'N/A';
        }

        $totalDiff = $stat2['total'] - $stat1['total'];
        $idleDiff  = $stat2['idle']  - $stat1['idle'];

        if($totalDiff === 0)
        {
            return 'N/A';
        }

        $usage = (1 - ($idleDiff / $totalDiff)) * 100;

        $percent = (int) round($usage);

        return [
            'title' => 'CPU Usage',
            'subtitle' => '',
            'count' => $percent,
            'metrics' => [
                'Used' => [
                    'value' => $percent,
                    'color' => $this->getCpuColor($percent),
                ],
            ],
            "cpu_info" => $this->getCpuInfo(),
        ];
    }

    private function readCpuStat()
    {
        $stat = file('/proc/stat');

        if(!$stat)
        {
            return null;
        }

        $parts = preg_split('/\s+/', trim($stat[0]));

        array_shift($parts); // remove "cpu"

        $total = array_sum($parts);

        $idle  = $parts[3] + ($parts[4] ?? 0); // idle + iowait

        return [
            'total' => $total,
            'idle'  => $idle,
        ];
    }

    public function getCpuInfo()
    {
        $avgLoad = $this->getLoadAverage();

        return [
            'CPU cores' => $this->getCpuCores(),
            'Load Average (1)' => $avgLoad[0],
            'Load Average (5)' => $avgLoad[1],
            'Load Average (15)' => $avgLoad[2],
        ];
    }

    protected function getCpuCores()
    {
        if(!stristr(PHP_OS, 'Linux'))
        {
            return 'N/A';
        }

        $cpuinfo = file('/proc/cpuinfo');

        return count(array_filter($cpuinfo, fn($line) => str_starts_with($line, 'processor')));
    }

    protected function getLoadAverage()
    {
        if(!function_exists('sys_getloadavg'))
        {
            return 'N/A';
        }

        $load = sys_getloadavg();

        return array_map(fn($v) => number_format($v, 2), $load);
    }

    protected function getCpuColor(int $percent)
    {
        if($percent >= 85)
        {
            return $this->colorRed;
        }

        if($percent >= 60)
        {
            return $this->colorYellow;
        }

        return $this->colorGreen;
    }

    public function getSystemCounts()
    {
        $this->scope = (auth()->user()->hasPermission("dialplan_add")) ? "system" : "domain";

        $modules = $this->getSystemCountInfo();

        return [
            'title' => 'System Counts',
            'subtitle' => '',
            'count' => $modules["Domains"]["total"],
            'metrics' => [
                'Used' => [
                    'value' => $modules["Domains"]["total"],
                    'color' => $this->colorBlue,
                ],
            ],
            "modules" => $modules,
            "messages" => [
                'Messages' => $this->getVoicemailCount(),
            ],
        ];
    }

    private function getSystemCountInfo()
    {
        return [
            'Domains' => $this->getDomainsCount(),
            'Devices' => $this->getModelCount(\App\Models\Device::class, "device_view", "device_enabled"),
            'Extensions' => $this->getModelCount(\App\Models\Extension::class, "extension_view", "enabled"),
            'Gateways' => $this->getModelCount(\App\Models\Gateway::class, "gateway_view", "enabled"),
            'Users' => $this->getModelCount(\App\Models\User::class, "user_view", "user_enabled"),
            'Destinations' => $this->getModelCount(\App\Models\Destination::class, "destination_view", "destination_enabled"),
            'CC Queues' => $this->getModelCount(\App\Models\CallCenterQueue::class, "call_center_active_view"),
            'IVR Menus' => $this->getModelCount(\App\Models\IVRMenu::class, "ivr_menu_view", "ivr_menu_enabled"),
            'Ring Groups' => $this->getModelCount(\App\Models\RingGroup::class, "ring_group_view", "ring_group_enabled"),
            'Voicemail' => $this->getModelCount(\App\Models\Voicemail::class, "voicemail_view", "voicemail_enabled"),
        ];
    }

    private function getDomainsCount()
    {
        $stats = [
            'total' => 0,
            'disabled' => 0,
        ];

        if(auth()->user()->hasPermission("domain_view"))
        {
            $domains = Domain::all();

            $stats['total'] = $domains->count();

            foreach($domains as $domain)
            {
                $stats['disabled'] += ($domain->domain_enabled != 'true') ? 1 : 0;
            }
        }

        return $stats;
    }

    private function getModelCount($model, $permission, $enabledField = null)
    {
        $stats = [
            'system' => [
                'total' => 0,
                'disabled' => 0,
            ],
            'domain' => [
                'total' => 0,
                'disabled' => 0,
            ],
        ];

        if(auth()->user()->hasPermission($permission))
        {
            $items = $model::all();

            $stats['system']['total'] = $items->count();

            foreach($items as $item)
            {
                if(!is_null($enabledField))
                {
                    $stats['system']['disabled'] += ($item->{$enabledField} == "true") ? 0 : 1;
                }

                if($item->domain_uuid == Session::get('domain_uuid'))
                {
                    $stats['domain']['total']++;

                    if(!is_null($enabledField))
                    {
                        $stats['domain']['disabled'] += ($item->{$enabledField} == "true") ? 0 : 1;
                    }
                }
            }
        }

        return $stats[$this->scope];
    }

    private function getVoicemailCount()
    {
        $stats = [
            'system' => [
                'total' => 0,
                'new' => 0,
            ],
            'domain' => [
                'total' => 0,
                'new' => 0,
            ],
        ];

        if(auth()->user()->hasPermission("voicemail_message_view"))
        {
            $items = VoicemailMessage::all();

            $stats['system']['total'] = $items->count();

            foreach($items as $item)
            {
                $stats['system']['new'] += ($item->message_status == "saved") ? 0 : 1;

                if($item->domain_uuid == Session::get('domain_uuid'))
                {
                    $stats['domain']['total']++;

                    $stats['domain']['new'] += ($item->message_status == "saved") ? 0 : 1;
                }
            }
        }

        return $stats[$this->scope];
    }
}
