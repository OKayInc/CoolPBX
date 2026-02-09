<?php

namespace App\Services\FreeSwitch;

use App\Contracts\FreeSwitchConnectionManagerInterface;
use App\Models\ClusterNode;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FreeSwitchService
{
    protected FreeSwitchConnectionManagerInterface $connection;
    protected FreeSwitchResponseMerger $merger;

    public function __construct(
        FreeSwitchConnectionManagerInterface $connection,
        FreeSwitchResponseMerger $merger
    ) {
        $this->connection = $connection;
        $this->merger = $merger;
    }

    /**
     * Execute a command on ALL cluster nodes
     *
     * @param string $command The command to execute
     * @param string|null $param Optional parameters for the command
     * @return array Array of responses from all nodes
     */
    public function execute(string $command, ?string $param = null): array
    {
        if (App::hasDebugModeEnabled()) {
            Log::debug('[' . __CLASS__ . '][' . __METHOD__ . '] Executing command: ' . $command . ' ' . $param);
        }

        $nodes = $this->getClusterNodes();
        $responses = [];

        foreach ($nodes as $node) {
            if (App::hasDebugModeEnabled()) {
                Log::debug('[' . __CLASS__ . '][' . __METHOD__ . '] Executing on node: ' . $node->node_name . ' (' . $node->node_hostname . ')');
            }

            $response = $this->connection->executeCommand(
                $command,
                $param,
                $node->node_hostname
            );

            $responses[] = [
                'node' => $node,
                'response' => $response,
            ];
        }

        return $responses;
    }

    /**
     * Execute a command and return ONLY the first response (for backward compatibility)
     * 
     * @deprecated Use execute() and handle array responses instead
     * @param string $command The command to execute
     * @param string|null $param Optional parameters for the command
     * @return string|null The response from the first node only
     */
    public function executeFirst(string $command, ?string $param = null): ?string
    {
        $responses = $this->execute($command, $param);

        if (empty($responses)) {
            return null;
        }

        return $responses[0]['response'] ?? null;
    }

    /**
     * Execute and get merged response
     * 
     * @param string $command The command to execute
     * @param string|null $param Optional parameters for the command
     * @return array Merged response with metadata
     */
    public function executeAndMerge(string $command, ?string $param = null): array
    {
        $responses = $this->execute($command, $param);
        return $this->merger->merge($responses, $command);
    }

    /**
     * Get active cluster nodes (cached for performance)
     * 
     * @return \Illuminate\Support\Collection
     */
    protected function getClusterNodes()
    {
        return Cache::remember('cluster_nodes_active', 300, function () {
            $nodes = ClusterNode::pbxNodes()->get();

            if ($nodes->isEmpty()) {
                Log::warning('[' . __CLASS__ . '][' . __METHOD__ . '] No cluster nodes found, using localhost');

                return collect([
                    (object) [
                        'node_name' => 'local',
                        'node_hostname' => '127.0.0.1',
                        'xml_rpc_port' => 8080,
                        'node_enabled' => 'true',
                    ]
                ]);
            }

            return $nodes;
        });
    }

    /**
     * Clear cluster nodes cache
     */
    public function clearNodesCache(): void
    {
        Cache::forget('cluster_nodes_active');
    }

    /**
     * Check if the service is connected to the FreeSWITCH server
     *
     * @return bool Connection status
     */
    public function isConnected(): bool
    {
        return $this->connection->isConnected();
    }

    /**
     * Force a reconnection to the FreeSWITCH server
     *
     * @return bool Success status
     */
    public function reconnect(): bool
    {
        $this->connection->close();
        return $this->connection->connect();
    }

    /**
     * Get the connection type (EVENT_SOCKET or XML_RPC)
     *
     * @return string Connection type
     */
    public function getConnectionType(): string
    {
        return $this->connection->getConnectionType();
    }

    /**
     * Get gateway status
     *
     * @param string $gateway_uuid The UUID of the gateway
     * @param string $result_type The type of result (xml, json, etc)
     * @return array Responses from all nodes
     */
    public function getGatewayStatus(string $gateway_uuid, string $result_type = 'xml'): array
    {
        $cmd = 'sofia xmlstatus gateway ' . $gateway_uuid;
        $responses = $this->execute($cmd);

        foreach ($responses as &$item) {
            if ($item['response'] == "Invalid Gateway!") {
                $cmd = 'sofia xmlstatus gateway ' . strtoupper($gateway_uuid);
                $item['response'] = $this->connection->executeCommand(
                    $cmd,
                    null,
                    $item['node']->node_hostname
                );
            }
        }

        return $responses;
    }

    /**
     * Get server status from all nodes
     *
     * @return array Responses from all nodes
     */
    public function getServerStatus(): array
    {
        return $this->execute('status');
    }

    /**
     * Close the connection to the FreeSWITCH server
     */
    public function closeConnection(): void
    {
        $this->connection->close();
    }
}
