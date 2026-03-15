<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use App\Traits\GetTableName;
use App\Traits\HandlesStringBooleans;
use App\Traits\HasUniqueIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ConferenceRoomUser extends Pivot
{
	use CreatedUpdatedBy, GetTableName, HandlesStringBooleans, HasApiTokens, HasFactory, HasUniqueIdentifier, Notifiable;

	protected $table = 'v_conference_room_users';
	protected $primaryKey = 'conference_room_user_uuid';
	public $incrementing = false;
	protected $keyType = 'string';	// TODO, check if UUID is valid
	const CREATED_AT = 'insert_date';
	const UPDATED_AT = 'update_date';

	public function domain(): BelongsTo {
		return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
	}

	public function user(): BelongsTo {
		return $this->belongsTo(User::class, 'user_uuid', 'user_uuid');
	}
}
