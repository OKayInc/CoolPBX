<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use App\Traits\GetTableName;
use App\Traits\HandlesStringBooleans;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventGuardLog extends Model
{
    use CreatedUpdatedBy, GetTableName, HandlesStringBooleans, HasApiTokens, HasFactory, HasUniqueIdentifier, Notifiable;

    protected $table = "v_event_guard_logs";
    protected $primaryKey = "event_guard_log_uuid";
    public $incrementing = false;
    protected $keyType = "string";

    public $timestamps = false;

    protected $fillable = [
        'hostname',
        'log_date',
        'filter',
        'ip_address',
        'extension',
        'user_agent',
        'log_status',
    ];

    protected $casts = [
        'log_date' => 'datetime',
    ];
}
