<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        return [
            "title" => "Service Level",
            "subtitle" => "",
            "value" => "50%",
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
