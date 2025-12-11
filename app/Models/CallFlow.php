<?php

namespace App\Models;

use App\Traits\GetTableName;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class CallFlow extends Model
{
    use HasApiTokens, HasFactory, Notifiable, HasUniqueIdentifier, GetTableName;
    protected $table = 'v_call_flows';
    protected $primaryKey = 'call_flow_uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    const CREATED_AT = 'insert_date';
    const UPDATED_AT = 'update_date';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'domain_uuid',
        'dialplan_uuid',
        'call_flow_name',
        'call_flow_extension',
        'call_flow_feature_code',
        'call_flow_context',
        'call_flow_status',
        'call_flow_pin_number',
        'call_flow_label',
        'call_flow_sound',
        'call_flow_app',
        'call_flow_data',
        'call_flow_alternate_label',
        'call_flow_alternate_sound',
        'call_flow_alternate_app',
        'call_flow_alternate_data',
        'call_flow_enabled',
        'call_flow_description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'insert_user',
        'update_user',
    ];


    protected $casts = [
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }

    public function dialplan(): BelongsTo
    {
        return $this->belongsTo(Dialplan::class, 'dialplan_uuid', 'dialplan_uuid');
    }
}