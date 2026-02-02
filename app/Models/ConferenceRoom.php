<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use App\Traits\GetTableName;
use App\Traits\HandlesStringBooleans;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ConferenceRoom extends Model
{
	use HasApiTokens, HasFactory, Notifiable, HandlesStringBooleans, HasUniqueIdentifier, GetTableName;
	protected $table = 'v_conference_rooms';
	protected $primaryKey = 'conference_room_uuid';
	public $incrementing = false;
	protected $keyType = 'string';	// TODO, check if UUID is valid
	const CREATED_AT = 'insert_date';
	const UPDATED_AT = 'update_date';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
	protected $fillable = [
        'domain_uuid',
        'conference_center_uuid',
        'conference_room_uuid',
        'conference_room_name',
        'profile',
        'record',
        'moderator_pin',
        'participant_pin',
        'max_members',
        'start_datetime',
        'stop_datetime',
        'wait_mod',
        'moderator_endconf',
        'announce_name',
        'announce_count',
        'announce_recording',
        'sounds',
        'mute',
//        'created',
//        'created_by',
        'email_address',
        'account_code',
        'enabled',
        'description',
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

	protected static $stringBooleanFields = [
		'record',
		'wait_mod',
		'moderator_endconf',
        'announce_name',
        'announce_count',
        'announce_recording',
        'mute',
		'enabled',
		'sounds',
	];

	public function domain(): BelongsTo {
		return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
	}

	public function conferencecenter(): BelongsTo {
		return $this->belongsTo(ConferenceCenter::class, 'conference_center_uuid', 'conference_center_uuid');
	}

	public function users(): HasMany {
		return $this->hasMany(ConferenceRoomUser::class, 'conference_room_uuid', 'conference_room_uuid');
	}
}
