<?php

namespace App\Models;

use App\Traits\GetTableName;
use App\Traits\HandlesStringBooleans;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClusterNode extends Model
{
    use HasFactory;
    use HasUniqueIdentifier;
    use HandlesStringBooleans;
    use GetTableName;

    protected $table = 'v_cluster_nodes';
    protected $primaryKey = 'cluster_node_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'insert_date';
    const UPDATED_AT = 'update_date';

    protected $fillable = [
        'node_name',
        'node_hostname',
        'xml_rpc_port',
        'node_role',
        'node_enabled',
        'node_priority',
        'node_description',
    ];

    protected static $stringBooleanFields = [
        'node_enabled',
    ];

    protected $casts = [
        'xml_rpc_port' => 'integer',
        'node_priority' => 'integer',
    ];

 
    public function scopePbxNodes($query)
    {
        return $query->whereIn('node_role', ['pbx', 'both'])
                     ->where('node_enabled', 'true')
                     ->orderBy('node_priority');
    }


    public function scopeActive($query)
    {
        return $query->where('node_enabled', 'true');
    }


    public function isEnabled(): bool
    {
        return $this->node_enabled === 'true';
    }

    public function isPbxNode(): bool
    {
        return in_array($this->node_role, ['pbx', 'both']);
    }
}