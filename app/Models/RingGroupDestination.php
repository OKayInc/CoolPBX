<?php

namespace App\Models;

use App\Traits\GetTableName;
use App\Traits\HandlesStringBooleans;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RingGroupDestination extends Model
{
	use HasFactory, HasUniqueIdentifier, GetTableName, HandlesStringBooleans;
	protected $table = 'v_ring_group_destinations';
	protected $primaryKey = 'ring_group_destination_uuid';
	public $incrementing = false;
	protected $keyType = 'string';	// TODO, check if UUID is valid
	const CREATED_AT = 'insert_date';
	const UPDATED_AT = 'update_date';
    
    protected $stringBooleanFields = [
        'destination_enabled'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $fillable = [
        'ring_group_destination_uuid',
        'domain_uuid',
        'ring_group_uuid',
        'destination_number',
        'destination_delay',
        'destination_timeout',
        'destination_enabled',
        'destination_prompt',
	];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
	protected $hidden = [
	];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
	protected $casts = [
	];

	public function domain(): BelongsTo {
		return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
	}

	public function ringroup(): BelongsTo {
		return $this->belongsTo(RingGroup::class, 'ring_group_uuid', 'ring_group_uuid');
	}
}
