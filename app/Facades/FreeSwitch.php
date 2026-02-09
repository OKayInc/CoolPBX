<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array execute(string $command, ?string $param = null) Execute a command on ALL cluster nodes
 * @method static string|null executeFirst(string $command, ?string $param = null) Execute and return only first response (deprecated)
 * @method static array executeAndMerge(string $command, ?string $param = null) Execute and get merged response
 * @method static void clearNodesCache() Clear cluster nodes cache
 * @method static bool isConnected() Check if connected to FreeSWITCH
 * @method static bool reconnect() Force reconnection
 * @method static string getConnectionType() Get connection type (EVENT_SOCKET or XML_RPC)
 * @method static array getGatewayStatus(string $gateway_uuid, string $result_type = 'xml') Get gateway status from all nodes
 * @method static array getServerStatus() Get server status from all nodes
 * @method static void closeConnection() Close connection
 *
 * @see \App\Services\FreeSwitch\FreeSwitchService
 */
class FreeSwitch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'freeswitch';
    }
}