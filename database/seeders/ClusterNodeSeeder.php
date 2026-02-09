<?php

namespace Database\Seeders;

use App\Models\ClusterNode;
use Illuminate\Database\Seeder;

class ClusterNodeSeeder extends Seeder
{
    public function run(): void
    {
        ClusterNode::create([
            'node_name' => 'local-node',
            'node_hostname' => '127.0.0.1',
            'xml_rpc_port' => 8080,
            'node_role' => 'pbx',
            'node_enabled' => 'true',
            'node_priority' => 1,
            'node_description' => 'Local FreeSWITCH node',
        ]);
    }
}